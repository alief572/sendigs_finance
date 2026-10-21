<?php
require_once __DIR__.'/Payment_voucher_reconciliation.php';

class Payment_voucher_repair
{
    const MARKER='CORR-BK--1026-0002-BANK';

    public static function plan($e,$ownership,$review)
    {
        $errors=$e['collection_errors'] ?? [];
        foreach(['source_contract_matches','baseline_matches','snapshot_unchanged'] as $flag)if(empty($e[$flag]))$errors[]='Snapshot/sumber tidak memenuhi kontrak: '.$flag;
        if(!empty($e['postings']))$errors[]='Metadata posting sudah ada.';
        if(($review['bank_company'] ?? '')!=='STM'||($review['bank_id'] ?? '')!=='18'||($review['bank_coa'] ?? '')!=='1101-02-01'||trim($review['reviewed_by'] ?? '')==='')$errors[]='Owner bank STM belum dikonfirmasi oleh reviewer.';
        if(($review['legacy_jurnaltras'] ?? '')!=='retain_existing_trace_no_replay')$errors[]='Klasifikasi jurnaltras legacy belum ditinjau.';
        $bank=$e['bank'] ?? [];
        if((string)($bank['id'] ?? '')!=='18'||($bank['coa_bank'] ?? '')!=='1101-02-01'||empty($bank['coa_verified'])||empty($bank['payment_source_consistent']))$errors[]='Bank/COA sumber berubah atau belum valid.';
        $expected=['cash'=>['DPM-2026-00081','DPM-2026-00083','DPM-2026-00082'],'periodik'=>['PERIODIK-2026-00181','PERIODIK-2026-00177']];
        foreach($expected as $kind=>$docs){
            $seen=[];foreach($ownership[$kind] ?? [] as $row){$seen[]=$row['no_doc'];if(($row['company_id'] ?? '')!=='COM003')$errors[]='Company dokumen '.$row['no_doc'].' bukan STM.';}
            sort($seen);sort($docs);if($seen!==$docs)$errors[]='Relasi dokumen '.$kind.' tidak lengkap/unik.';
        }
        $legacy=[];
        foreach($e['ledger'] ?? [] as $table=>$rows){
            if(substr($table,-strlen('.jurnaltras'))==='.jurnaltras')$legacy=array_merge($legacy,$rows);
            elseif($rows)$errors[]='Accounting terkait sudah ada: '.$table;
        }
        $expectedLegacy=['1183'=>['18',0,892020200],'1184'=>['2101-01-01',892020200,0],'1185'=>['6101-01-24',0,0]];
        if(count($legacy)!==3)$errors[]='Jumlah jurnaltras legacy berubah.';
        foreach($legacy as $row){
            $contract=$expectedLegacy[(string)($row['id'] ?? '')] ?? null;
            if(!$contract||($row['no_request'] ?? '')!==Payment_voucher_reconciliation::VOUCHER||(string)($row['stspos'] ?? '')!=='1'
                ||[(string)$row['no_perkiraan'],Payment_journal_validator::cents($row['debet']),Payment_journal_validator::cents($row['kredit'])]!==$contract)$errors[]='Isi jurnaltras legacy berubah.';
        }
        $rows=$e['journal'] ?? [];$updates=[];$required=['1101-02-01'];$totals=['Payment'=>[0,0,0],'Refill Pettycash'=>[0,0,0]];
        foreach($rows as $row){
            $type=$row['jenis_transaksi'];
            if(!isset($totals[$type])||$row['no_transaksi']!==Payment_voucher_reconciliation::NATIVE_KEY||(string)$row['sts']!=='0'||!empty($row['tgl_posting'])||!empty($row['posted_by'])){$errors[]='Identitas/status jurnal berubah.';continue;}
            $d=Payment_journal_validator::cents($row['debit']);$k=Payment_journal_validator::cents($row['kredit']);
            $totals[$type][0]+=$d;$totals[$type][1]+=$k;$totals[$type][2]++;
            if($d>0||$k>0)$required[]=$row['coa'];
            if(in_array((int)$row['id'],[11085,11086,11087],true)){
                if(trim((string)$row['id_company'])!==''||trim((string)$row['nm_company'])!=='')$errors[]='Company Cash telah berubah.';
                $updates[]=['id'=>$row['id'],'id_company'=>'7','nm_company'=>'STM'];
            }elseif($type==='Payment'&&((string)$row['id_company']!=='7'||$row['nm_company']!=='STM'))$errors[]='Company Periodik tidak konsisten.';
        }
        if(count($updates)!==3||$totals!==['Payment'=>[871320200,0,9],'Refill Pettycash'=>[20700000,20700000,2]])$errors[]='Struktur/nominal jurnal tidak cocok baseline.';
        $missing=array_values(array_diff(array_unique($required),$ownership['target_coas'] ?? []));
        if($missing)$errors[]='COA belum tersedia pada accounting_stm: '.implode(', ',$missing);
        $insert=['no_jurnal'=>self::MARKER,'no_transaksi'=>Payment_voucher_reconciliation::NATIVE_KEY,'jenis_transaksi'=>'Payment',
            'tgl_jurnal'=>'2026-10-05','id_company'=>'7','nm_company'=>'STM','id_divisi'=>'','nm_divisi'=>'',
            'coa'=>'1101-02-01','nm_coa'=>'Bank BCA','debit'=>'0.00','kredit'=>'8713202.00','sts'=>'0',
            'keterangan'=>'Kredit bank pembayaran '.Payment_voucher_reconciliation::VOUCHER.' [koreksi jurnal sumber]'];
        return ['voucher'=>Payment_voucher_reconciliation::VOUCHER,'ready'=>!$errors,'blockers'=>array_values(array_unique($errors)),
            'updates'=>$updates,'insert'=>$insert,'accounting_writes'=>0,'legacy_ledger_writes'=>0,'missing_coas'=>$missing];
    }

    public static function execute($db,$collect,$review,$backupPath,$apply=false)
    {
        $key=Payment_voucher_reconciliation::NATIVE_KEY;$locks=[];$started=false;$commitAttempted=false;
        $query=function($sql,$args=[])use($db){$s=$db->prepare($sql);if($args)$s->bind_param(str_repeat('s',count($args)),...$args);$s->execute();return $s;};
        try{
            if($apply){
                $names=['voucher-repair:'.Payment_voucher_reconciliation::VOUCHER];
                foreach(['Payment','Refill Pettycash'] as $type)$names[]='jurnal_all:'.substr(hash('sha256',json_encode([$key,$type,null])),0,48);
                sort($names,SORT_STRING);foreach($names as $name){$lock=$query('SELECT GET_LOCK(?,10) acquired',[$name])->get_result()->fetch_assoc();if((int)$lock['acquired']!==1)throw new RuntimeException('Voucher sedang diproses.');$locks[]=$name;}
                if(!$db->begin_transaction())throw new RuntimeException('Transaksi koreksi gagal dimulai.');$started=true;
            }
            $sql='SELECT * FROM tr_jurnal WHERE no_transaksi=? ORDER BY id'.($apply?' FOR UPDATE':'');
            $current=$query($sql,[$key])->get_result()->fetch_all(MYSQLI_ASSOC);
            $markers=array_values(array_filter($current,function($r){return $r['no_jurnal']===self::MARKER;}));
            if($markers){
                if(count($markers)!==1||!is_file($backupPath))throw new RuntimeException('Marker koreksi/backup tidak konsisten.');
                $backup=json_decode(file_get_contents($backupPath),true,512,JSON_THROW_ON_ERROR);$original=[];
                if(Payment_voucher_reconciliation::fingerprint($backup['journal'])!==$backup['journal_hash'])throw new RuntimeException('Backup source berubah.');
                foreach($current as $row){
                    if($row['no_jurnal']===self::MARKER){
                        $expected=$backup['plan']['insert'];foreach($expected as $field=>$value)if((string)$row[$field]!==$value)throw new RuntimeException('Baris koreksi berubah.');
                    }else{
                        $original[]=$row;
                    }
                }
                $expectedOriginal=$backup['journal'];foreach($expectedOriginal as &$row)if(in_array((int)$row['id'],[11085,11086,11087],true)){$row['id_company']='7';$row['nm_company']='STM';}unset($row);
                if(Payment_voucher_reconciliation::fingerprint($original)!==Payment_voucher_reconciliation::fingerprint($expectedOriginal))throw new RuntimeException('Snapshot hasil koreksi berubah.');
                if($started){$db->rollback();$started=false;}
                return ['status'=>'already_applied','inserted_id'=>$markers[0]['id'],'database_writes'=>0];
            }
            if($apply){$query('SELECT id FROM payment_approve WHERE id_payment=? FOR UPDATE',[Payment_voucher_reconciliation::VOUCHER]);$query('SELECT id FROM tr_payment_paid WHERE id=? FOR UPDATE',[Payment_voucher_reconciliation::VOUCHER]);}
            $data=$collect();$e=$data['evidence'];$plan=self::plan($e,$data['ownership'],$review);
            if(Payment_voucher_reconciliation::fingerprint($current)!==$e['journal_sha256_before'])throw new RuntimeException('Snapshot source berubah saat preflight.');
            if(!$apply)return ['status'=>'dry_run','plan'=>$plan];
            if(!$plan['ready'])throw new DomainException(implode(' ',$plan['blockers']));
            $engine=$query("SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tr_jurnal'")->get_result()->fetch_assoc();
            if(strtoupper($engine['ENGINE'] ?? '')!=='INNODB')throw new RuntimeException('Source koreksi memerlukan InnoDB.');
            $triggers=$query("SELECT COUNT(*) n FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA=DATABASE() AND EVENT_OBJECT_TABLE='tr_jurnal'")->get_result()->fetch_assoc();
            if((int)$triggers['n']!==0)throw new RuntimeException('Trigger source belum ditinjau.');
            $backup=['voucher'=>Payment_voucher_reconciliation::VOUCHER,'journal_hash'=>$e['journal_sha256_before'],'journal'=>$current,'evidence'=>$e,'ownership'=>$data['ownership'],'review'=>$review,'plan'=>$plan];
            if(is_file($backupPath)){
                $existing=json_decode(file_get_contents($backupPath),true,512,JSON_THROW_ON_ERROR);
                if(($existing['journal_hash'] ?? '')!==$backup['journal_hash']||($existing['review'] ?? [])!==$review)throw new RuntimeException('Backup existing tidak cocok; jangan ditimpa.');
            }else{
                if(!is_dir(dirname($backupPath)))throw new RuntimeException('Direktori backup tidak tersedia.');
                $handle=fopen($backupPath,'x');if(!$handle)throw new RuntimeException('Backup gagal dibuat.');
                try{$json=json_encode($backup,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR);if(fwrite($handle,$json)!==strlen($json)||!fflush($handle))throw new RuntimeException('Backup tidak lengkap.');}finally{fclose($handle);}
            }
            foreach($plan['updates'] as $row){$s=$query("UPDATE tr_jurnal SET id_company=?,nm_company=? WHERE id=? AND no_transaksi=? AND sts='0'",[$row['id_company'],$row['nm_company'],$row['id'],$key]);if($s->affected_rows!==1)throw new RuntimeException('Company source gagal diperbarui.');}
            $insert=$plan['insert'];$insert['created_by']='case-repair';$insert['created_date']=date('Y-m-d H:i:s');
            $s=$query('INSERT INTO tr_jurnal (`'.implode('`,`',array_keys($insert)).'`) VALUES ('.implode(',',array_fill(0,count($insert),'?')).')',array_values($insert));
            if($s->affected_rows!==1)throw new RuntimeException('Baris bank gagal dibuat.');$id=$db->insert_id;
            $after=$query('SELECT * FROM tr_jurnal WHERE no_transaksi=? ORDER BY id',[$key])->get_result()->fetch_all(MYSQLI_ASSOC);
            Payment_journal_validator::validate($after);
            $commitAttempted=true;if(!$db->commit())throw new RuntimeException('Commit koreksi tidak terkonfirmasi; periksa marker/backup sebelum mengulang.');$started=false;
            return ['status'=>'applied','inserted_id'=>$id,'updated_rows'=>3,'inserted_rows'=>1,'source_rows'=>count($after),'accounting_writes'=>0];
        }catch(Throwable $error){
            if($started)$db->rollback();
            if($commitAttempted)throw new RuntimeException('Hasil commit koreksi perlu diperiksa melalui marker dan backup; tidak ada retry otomatis.',0,$error);
            throw $error;
        }finally{foreach(array_reverse($locks) as $name)try{$query('SELECT RELEASE_LOCK(?)',[$name]);}catch(Throwable $error){}}
    }
}
