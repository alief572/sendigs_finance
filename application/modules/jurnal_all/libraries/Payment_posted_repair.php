<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once __DIR__.'/Payment_patch.php';
require_once __DIR__.'/Payment_normalization.php';

/**
 * One-off repair for journals still keyed by payment_approve.id instead of id_payment. Only the whitelisted
 * groups of the chosen batch are touched. The accounting tables (japh, jurnal) are MyISAM, so there is no
 * transaction rollback there: every UPDATE is row-count checked, compensated on mismatch, and the full
 * before-image is stored in a backup file next to the manifest.
 *
 * Batches: payment (applied 2026-11-11), refill_posted, refill_pending (tr_jurnal only, no accounting) and shared
 * (one key used by a Payment AND a Refill journal, which then share the voucher; two japh headers, told apart by jenis_ap).
 * pending_payment: BK--0926-0017 (Cash DPM-2026-00022) is rekeyed to its real voucher BK--0826-0015; its old key equals the
 * voucher of BK--0926-0123,... and blocked that group's posting (Payment_reference::inspect 'collision').
 * Deliberately excluded (need a human decision): BK--0825-0001 (voucher shared by 5 approvals) and
 * BK--0926-0123,... (key shared with pending Payment journals).
 */
class Payment_posted_repair
{
    /** payment batch: old no_transaksi => [new voucher, accounting config group] */
    const WHITELIST = [
        'BK--0426-0045'=>['BK--0426-0008','accounting_vuca'],
        'BK--0626-0019'=>['BK--0926-0001','accounting_stm'],
        'BK--0626-0061'=>['BK--0926-0010','accounting_stm'],
        'BK--0726-0040'=>['BK--0126-0001','accounting_stm'],
        'BK--0826-0075'=>['BK--0926-0014','accounting_stm'],
    ];
    /** Ledger null = accounting is NOT changed (it is keyed by the RPC number, not by the approval id). */
    const REFILL_POSTED = [
        'BK--0926-0104,BK--0926-0105'=>['BK--0926-0018','accounting_stm'],
        'BK--0926-0128'=>['BK--0926-0016',null],
        'BK--1026-0074'=>['BK--1026-0003',null],
    ];
    const REFILL_PENDING = [
        'BK--0826-0046'=>['BK--0826-0014',null],
        'BK--0826-0050'=>['BK--0826-0029',null],
        'BK--0826-0048,BK--0926-0099,BK--0926-0098,BK--0926-0138,BK--0826-0047,BK--0826-0014'=>['BK--0826-0030',null],
        'BK--0826-0043,BK--0826-0044,BK--0826-0045'=>['BK--0826-0031',null],
        'BK--0826-0042,BK--0826-0049,BK--0926-0106'=>['BK--0826-0032',null],
    ];
    /** Pending Payment journal keyed by an approval id whose real voucher collides with another group (tr_jurnal only, no accounting). */
    const PENDING_PAYMENT = [
        'BK--0926-0017'=>['BK--0826-0015',null],
        'BK--0926-0015,BK--0926-0016,BK--0926-0022,BK--0926-0089,BK--0926-0020'=>['BK--0826-0018',null],
    ];
    const SHARED = [
        'BK--1026-0040,BK--1026-0006,BK--1026-0008,BK--1026-0039,BK--1026-0041,BK--1026-0042'=>['BK--1026-0002','accounting_stm'],
    ];
    const BATCHES = [
        'payment'=>['jenis'=>['Payment'],'sts'=>'1','list'=>self::WHITELIST],
        'refill_posted'=>['jenis'=>['Refill Pettycash'],'sts'=>'1','list'=>self::REFILL_POSTED],
        'refill_pending'=>['jenis'=>['Refill Pettycash'],'sts'=>'0','list'=>self::REFILL_PENDING],
        'shared'=>['jenis'=>['Payment','Refill Pettycash'],'sts'=>'1','list'=>self::SHARED],
        'pending_payment'=>['jenis'=>['Payment'],'sts'=>'0','list'=>self::PENDING_PAYMENT],
    ];
    const LEDGERS = ['accounting_vuca','accounting_stm','accounting_sustain'];

    private $ci;
    private $failpoint;
    private $batch;
    private $cfg;
    private $jenisList;
    public function __construct($ci, $failpoint = null, $batch = 'payment')
    {
        if(!isset(self::BATCHES[$batch]))throw new DomainException('Batch perbaikan tidak dikenal.');
        $this->ci=$ci;$this->failpoint=$failpoint;$this->batch=$batch;$this->cfg=self::BATCHES[$batch];$this->jenisList=$this->cfg['jenis'];
    }

    private function read($q) { if(!$q)throw new RuntimeException('Pembacaan perbaikan Payment posted gagal.');return $q->result_array(); }
    private function environment() { $db=$this->ci->db;return hash('sha256',json_encode([(string)$db->hostname,(string)$db->port,(string)$db->database],JSON_THROW_ON_ERROR)); }
    private function ledger($name) { $acc=$this->ci->load->database($name,true); if(!$acc)throw new RuntimeException('Accounting '.$name.' tidak tersedia.'); $acc->db_debug=false; return $acc; }
    // japh has no id column (a header is identified by nomor), so order by nomor there
    private function rowsByNo($acc,$table,$old) { return $this->read($acc->where('no_reff',$old)->order_by($table==='japh'?'nomor':'id','asc')->get($table)); }
    // accounting no_reff is varchar(25): a long id list is stored cut off
    private function ref($old) { return strlen($old)>25?substr($old,0,25):$old; }
    private function tokens($old) { return array_values(array_filter(array_map('trim',explode(',',$old)),'strlen')); }
    private function sharedBatch() { return count($this->jenisList)>1; }

    private function checkpoint($point) { if($this->failpoint===$point)throw new RuntimeException('Simulated failure at '.$point); }

    /** Read-only review of one whitelisted group. Throws Payment_reference_issue when it is not safe. */
    private function inspectGroup($old,$new,$expectedLedger)
    {
        $db=$this->ci->db;$sts=$this->cfg['sts'];$jenisList=$this->jenisList;$shared=$this->sharedBatch();
        $refillInvolved=in_array('Refill Pettycash',$jenisList,true);
        $all=$this->read($db->order_by('id','asc')->get_where('tr_jurnal',['no_transaksi'=>$old]));
        $rows=array_values(array_filter($all,function($r)use($jenisList){return in_array($r['jenis_transaksi'],$jenisList,true);}));
        if(!$rows)throw new Payment_reference_issue('invalid','Kelompok '.$old.' tidak ada.');
        if(count($rows)!==count($all))throw new Payment_reference_issue('collision','Kunci '.$old.' dipakai jurnal jenis lain; perlu keputusan terpisah.');
        $byJenis=[];foreach($rows as $r)$byJenis[$r['jenis_transaksi']][]=$r;
        foreach($jenisList as $j)if(empty($byJenis[$j]))throw new Payment_reference_issue('invalid','Kelompok '.$old.' tidak memuat jurnal '.$j.'.');
        foreach($rows as $r)if((string)$r['sts']!==$sts)throw new Payment_reference_issue('invalid','Kelompok '.$old.' tidak berstatus '.($sts==='1'?'posted':'pending').'.');
        // the voucher must hold exactly the approvals listed in the key
        $tokens=$this->tokens($old);
        $members=$this->read($db->get_where('payment_approve',['id_payment'=>$new]));
        $have=array_map('strval',array_column($members,'id'));$want=$tokens;sort($have);sort($want);
        if(!$tokens||$have!==$want)throw new Payment_reference_issue('ambiguous','Voucher '.$new.' tidak persis memuat approval '.$old.'.');
        $refillIds=[];$otherIds=[];
        foreach($members as $m){if((string)$m['tipe']==='refill_pettycash')$refillIds[]=(string)$m['id'];else $otherIds[]=(string)$m['id'];}
        if($shared){
            if(count($refillIds)!==1||!$otherIds)throw new Payment_reference_issue('ambiguous','Voucher '.$new.' harus memuat tepat satu approval Refill dan approval Payment lain.');
            $aliasMembers=['Payment'=>$otherIds,'Refill Pettycash'=>$refillIds];
        }elseif($jenisList===['Refill Pettycash']){
            if($otherIds)throw new Payment_reference_issue('ambiguous','Approval '.$otherIds[0].' bukan refill_pettycash.');
            $aliasMembers=['Refill Pettycash'=>$refillIds];
        }else $aliasMembers=[$jenisList[0]=>$tokens];
        if($refillInvolved){
            // Refill lookups (Jurnal_all_model::resolvePayments) try payment_approve.id first but keep only tipe=refill_pettycash.
            // A voucher equal to the id of an approval of ANOTHER type is therefore harmless; equal to a refill approval id it is not.
            if($this->read($db->get_where('payment_approve',['id'=>$new,'tipe'=>'refill_pettycash'])))throw new Payment_reference_issue('collision','Voucher '.$new.' sama dengan id sebuah approval Refill; pencarian Refill akan memilih approval yang salah.');
        }
        if(count($this->read($db->get_where('tr_payment_paid',['id'=>$new])))!==1)throw new Payment_reference_issue('invalid','Record pembayaran '.$new.' tidak valid.');
        if($this->read($db->get_where('tr_jurnal',['no_transaksi'=>$new])))throw new Payment_reference_issue('collision','Voucher '.$new.' sudah dipakai di tr_jurnal.');
        if($db->table_exists('tr_jurnal_postings')&&$this->read($db->where_in('no_transaksi',[$old,$new])->get('tr_jurnal_postings')))throw new Payment_reference_issue('posted','Metadata posting sudah ada.');
        if($db->table_exists('tr_jurnal_payment_attempts')&&$this->read($db->where_in('voucher',[$new])->get('tr_jurnal_payment_attempts')))throw new Payment_reference_issue('reconciliation_required','Ada percobaan posting untuk '.$new.'.');
        if($db->table_exists('tr_jurnal_payment_reference_aliases')&&$this->read($db->where_in('journal_id',array_map('intval',array_column($rows,'id')))->get('tr_jurnal_payment_reference_aliases')))throw new Payment_reference_issue('collision','Kelompok '.$old.' sudah punya alias.');
        $sum=[];
        foreach($byJenis as $j=>$list){
            $debit=0;$credit=0;foreach($list as $r){$debit+=Payment_reference::cents($r['debit']);$credit+=Payment_reference::cents($r['kredit']);}
            if(!$debit||$debit!==$credit)throw new Payment_reference_issue('unbalanced','Kelompok '.$old.' ('.$j.') tidak balance.');
            $sum[$j]=$debit;
        }
        $japh=[];$jurnal=[];
        if($expectedLedger!==null){
            $ref=$this->ref($old);$found=[];
            foreach(self::LEDGERS as $name){
                $acc=$this->ledger($name);
                $h=$this->rowsByNo($acc,'japh',$ref);
                if($h)$found[$name]=$h;
                if($this->read($acc->where('no_reff',$new)->get('japh'))||$this->read($acc->where('no_reff',$new)->get('jurnal')))throw new Payment_reference_issue('collision','Voucher '.$new.' sudah dipakai di accounting '.$name.'.');
            }
            if(count($found)!==1||!isset($found[$expectedLedger]))throw new Payment_reference_issue('ambiguous','Ledger accounting '.$old.' tidak tunggal/tidak sesuai ('.implode(',',array_keys($found)).').');
            $japh=$found[$expectedLedger];
            if(count($japh)!==count($jenisList))throw new Payment_reference_issue('ambiguous','Jumlah header japh '.$old.' tidak sesuai ('.count($japh).').');
            $acc=$this->ledger($expectedLedger);
            $jurnal=$this->read($acc->where('no_reff',$ref)->order_by('id','asc')->get('jurnal'));
            if(!$jurnal)throw new Payment_reference_issue('invalid','Baris jurnal accounting '.$old.' tidak ada.');
            $nomors=array_map('strval',array_column($japh,'nomor'));
            foreach($jurnal as $j)if(!in_array((string)$j['nomor'],$nomors,true))throw new Payment_reference_issue('ambiguous','Baris jurnal accounting memakai nomor di luar header.');
            foreach($japh as $h){
                $n=(string)$h['nomor'];$lines=array_filter($jurnal,function($j)use($n){return (string)$j['nomor']===$n;});
                if(!$lines)throw new Payment_reference_issue('invalid','Header '.$n.' tidak punya baris jurnal accounting.');
                $kind=$shared?(((string)$h['jenis_ap']==='V')?'Payment':'Refill Pettycash'):$jenisList[0];
                if(!isset($sum[$kind]))throw new Payment_reference_issue('ambiguous','Header '.$n.' tidak cocok dengan jenis jurnal.');
                if(abs(((float)$h['jml'])*100-$sum[$kind])>1)throw new Payment_reference_issue('ambiguous','Jumlah japh '.$n.' tidak sama dengan jurnal '.$kind.' '.$old.'.');
                $lineDebit=0;foreach($lines as $l)$lineDebit+=(float)$l['debet'];
                if(abs($lineDebit*100-$sum[$kind])>1)throw new Payment_reference_issue('ambiguous','Baris jurnal accounting '.$n.' tidak sama dengan jurnal '.$kind.' '.$old.'.');
            }
            if($shared){
                $kinds=[];foreach($japh as $h)$kinds[((string)$h['jenis_ap']==='V')?'Payment':'Refill Pettycash']=1;
                if(count($kinds)!==2)throw new Payment_reference_issue('ambiguous','Header japh harus satu Payment (jenis_ap V) dan satu Refill.');
            }
        }else{
            // accounting is left untouched, so it must not reference the key in any form
            $refs=array_values(array_unique([$old,$this->ref($old),$new]));
            if($sts==='0')$refs=array_merge($refs,array_map('strval',array_column($members,'no_doc')));
            foreach(self::LEDGERS as $name){
                $acc=$this->ledger($name);
                foreach(['japh','jurnal'] as $t)if($this->read($acc->where_in('no_reff',$refs)->get($t)))throw new Payment_reference_issue('reconciliation_required','Ada catatan accounting ('.$name.'.'.$t.') untuk '.$old.'; tentukan ledger di daftar putih.');
            }
        }
        $noteChanges=0;foreach($japh as $h)if((string)$h['note']==='Payment '.$old)$noteChanges++;
        return ['old'=>$old,'voucher'=>$new,'ledger'=>$expectedLedger,'company'=>(string)$rows[0]['id_company'],
            'tr_jurnal'=>$rows,'japh'=>$japh,'jurnal'=>$jurnal,'alias_members'=>$aliasMembers,'note_changes'=>$noteChanges];
    }

    /** Dry-run: reads only, writes the manifest + backup file. */
    public function prepare($path)
    {
        if(file_exists($path)||file_exists($path.'.backup.json'))throw new DomainException('Manifest sudah ada; gunakan nama baru.');
        Payment_reference::acquire($this->ci->db);
        try{
            $items=[];$backup=[];
            foreach($this->cfg['list'] as $old=>$cfg){
                $item=['old'=>$old,'voucher'=>$cfg[0],'ledger'=>$cfg[1],'status'=>'skipped'];
                try{
                    $g=$this->inspectGroup($old,$cfg[0],$cfg[1]);
                    $item=['old'=>$old,'voucher'=>$cfg[0],'ledger'=>$cfg[1],'status'=>'eligible','company'=>$g['company'],
                        'tr_jurnal_ids'=>array_map('intval',array_column($g['tr_jurnal'],'id')),'japh_nomor'=>$g['japh']?$g['japh'][0]['nomor']:null,
                        'japh_nomors'=>array_map('strval',array_column($g['japh'],'nomor')),
                        'jurnal_ids'=>array_map('intval',array_column($g['jurnal'],'id')),'note_changes'=>$g['note_changes'],'alias_members'=>$g['alias_members'],
                        'before'=>['tr_jurnal'=>Payment_normalization::digest($g['tr_jurnal']),'japh'=>Payment_normalization::digest($g['japh']),'jurnal'=>Payment_normalization::digest($g['jurnal'])]];
                    $backup[$old]=['tr_jurnal'=>$g['tr_jurnal'],'japh'=>$g['japh'],'jurnal'=>$g['jurnal']];
                }catch(Throwable $e){$item['reason']=$e instanceof DomainException?$e->getMessage():'Prasyarat belum dapat dipastikan.';$item['category']=$e instanceof Payment_reference_issue?$e->category:'unverified';}
                $items[]=$item;
            }
            $manifest=['kind'=>'payment_posted_repair','version'=>1,'batch'=>$this->batch,'run_id'=>bin2hex(random_bytes(16)),'environment'=>$this->environment(),
                'created_at'=>(new DateTimeImmutable('now',new DateTimeZone('Asia/Jakarta')))->format('Y-m-d H:i:s'),'items'=>$items];
            $backup=['run_id'=>$manifest['run_id'],'groups'=>$backup];
            $manifest['backup_digest']=hash('sha256',json_encode($backup,JSON_THROW_ON_ERROR));
            Payment_patch::save($path.'.backup.json',$backup);
            Payment_patch::save($path,$manifest);
            return $manifest;
        }finally{Payment_reference::release($this->ci->db);}
    }

    private function loadFiles($path)
    {
        $manifest=Payment_patch::load($path);
        if(($manifest['kind']??null)!=='payment_posted_repair'||($manifest['version']??null)!==1||!isset($manifest['items']))throw new DomainException('Format manifest tidak valid.');
        if(($manifest['batch']??'payment')!==$this->batch)throw new DomainException('Manifest milik batch lain.');
        if(($manifest['environment']??null)!==$this->environment())throw new DomainException('Manifest berasal dari environment database yang berbeda.');
        $backup=json_decode(file_get_contents($path.'.backup.json'),true,512,JSON_THROW_ON_ERROR);
        if(hash('sha256',json_encode($backup,JSON_THROW_ON_ERROR))!==($manifest['backup_digest']??null))throw new DomainException('Backup tidak cocok dengan manifest.');
        return [$manifest,$backup];
    }

    private function setRef($acc,$table,$ids,$from,$to,$field='no_reff')
    {
        if(!$ids)return 0;
        $acc->where_in('id',$ids)->where($field,$from)->update($table,[$field=>$to]);
        if($acc->affected_rows()!==count($ids))throw new RuntimeException('Jumlah baris '.$table.' yang berubah tidak sesuai manifest.');
        return count($ids);
    }

    private function applyOne($item,$backup,$run)
    {
        $old=$item['old'];$new=$item['voucher'];$db=$this->ci->db;
        $hasAcc=!empty($item['ledger']);$acc=$hasAcc?$this->ledger($item['ledger']):null;
        $b=$backup['groups'][$old];$ref=$this->ref($old);
        $trIds=$item['tr_jurnal_ids'];
        $jenisById=[];foreach($b['tr_jurnal'] as $r)$jenisById[(int)$r['id']]=$r['jenis_transaksi'];
        $begun=false;
        try{
            // 1) accounting first (MyISAM, compensated by hand); tr_jurnal keeps the old key until accounting is done
            if($hasAcc){
                foreach($b['japh'] as $h){
                    $set=['no_reff'=>$new];if((string)$h['note']==='Payment '.$old)$set['note']='Payment '.$new;
                    $acc->where('nomor',$h['nomor'])->where('no_reff',$ref)->update('japh',$set);
                    if($acc->affected_rows()!==1)throw new RuntimeException('Header japh tidak berubah sesuai manifest.');
                }
                $this->checkpoint('after_japh');
                $this->setRef($acc,'jurnal',$item['jurnal_ids'],$ref,$new);$this->checkpoint('after_jurnal');
            }
            // 2) tr_jurnal + alias in one InnoDB transaction
            if(!$db->trans_begin())throw new RuntimeException('Transaksi tr_jurnal gagal dimulai.');
            $begun=true;
            $db->where_in('id',$trIds)->where('no_transaksi',$old)->update('tr_jurnal',['no_transaksi'=>$new]);
            if($db->affected_rows()!==count($trIds))throw new RuntimeException('Jumlah baris tr_jurnal yang berubah tidak sesuai manifest.');
            $created=(new DateTimeImmutable('now',new DateTimeZone('Asia/Jakarta')))->format('Y-m-d H:i:s');
            foreach($trIds as $id){
                // alias approvals follow the row's own kind, so a Payment row never resolves to the Refill approval of the shared voucher
                $members=$item['alias_members'][$jenisById[(int)$id] ?? ''] ?? $this->tokens($old);
                $db->insert('tr_jurnal_payment_reference_aliases',['run_id'=>$run,'journal_id'=>$id,'original_no_transaksi'=>$old,'voucher'=>$new,'approval_ids'=>json_encode(array_values($members)),'active'=>1,'created_at'=>$created]);
            }
            $this->checkpoint('before_commit');
            if($db->trans_status()===false||!$db->trans_commit())throw new RuntimeException('Commit tr_jurnal gagal.');
            $begun=false;
        }catch(Throwable $error){
            // undo the InnoDB part, then compensate accounting from the backup, then rethrow
            if($begun)$db->trans_rollback();
            try{ $this->restoreAccounting($acc,$b,$new); }catch(Throwable $inner){ throw new RuntimeException('Perbaikan gagal dan pemulihan accounting juga gagal; pulihkan manual dari backup: '.$error->getMessage()); }
            throw $error;
        }
    }

    /** Safe to run unconditionally: the target voucher was verified unused, so only rows we changed match no_reff=$new. */
    private function restoreAccounting($acc,$b,$new)
    {
        if(!$acc||empty($b['japh']))return;
        $orig=$b['japh'][0]['no_reff'];
        $acc->where_in('id',array_map('intval',array_column($b['jurnal'],'id')))->where('no_reff',$new)->update('jurnal',['no_reff'=>$orig]);
        foreach($b['japh'] as $h)$acc->where('nomor',$h['nomor'])->where('no_reff',$new)->update('japh',['no_reff'=>$h['no_reff'],'note'=>$h['note']]);
    }

    /** Apply the reviewed manifest. Idempotent: groups already repaired are skipped. */
    public function run($path)
    {
        Payment_reference::acquire($this->ci->db);
        try{
            list($manifest,$backup)=$this->loadFiles($path);
            Payment_reference::transactional($this->ci->db,['tr_jurnal','tr_jurnal_payment_reference_aliases']);
            $list=$this->cfg['list'];
            // pre-flight: every pending group must still equal what was reviewed, before anything is written
            $todo=[];
            foreach($manifest['items'] as $i=>$item){
                if(!in_array($item['status'],['eligible','applied'],true))continue;
                if(!isset($list[$item['old']])||$list[$item['old']][0]!==$item['voucher'])throw new DomainException('Kelompok di manifest tidak ada pada daftar putih.');
                $stillOld=$this->read($this->ci->db->where_in('jenis_transaksi',$this->jenisList)->get_where('tr_jurnal',['no_transaksi'=>$item['old']]));
                if(!$stillOld){ if($item['status']==='applied')continue; throw new DomainException('Kelompok '.$item['old'].' sudah berubah di luar manifest.'); }
                $g=$this->inspectGroup($item['old'],$item['voucher'],$item['ledger']);
                if(Payment_normalization::digest($g['tr_jurnal'])!==$item['before']['tr_jurnal']||Payment_normalization::digest($g['japh'])!==$item['before']['japh']||Payment_normalization::digest($g['jurnal'])!==$item['before']['jurnal'])throw new DomainException('Data '.$item['old'].' berubah sejak dry-run; buat manifest baru.');
                $todo[]=$i;
            }
            // apply group by group; progress is checkpointed so an interrupted run can be resumed
            foreach($todo as $i){
                $this->applyOne($manifest['items'][$i],$backup,$manifest['run_id']);
                $manifest['items'][$i]['status']='applied';
                Payment_patch::save($path,$manifest);
            }
            return $manifest;
        }finally{Payment_reference::release($this->ci->db);}
    }

    /** Verify that only the reference columns changed, against the backup. */
    public function verify($path)
    {
        list($manifest,$backup)=$this->loadFiles($path);
        $db=$this->ci->db;$result=['groups'=>0,'tr_jurnal'=>0,'japh'=>0,'jurnal'=>0];
        foreach($manifest['items'] as $item){
            if($item['status']!=='applied')continue;
            $old=$item['old'];$new=$item['voucher'];$b=$backup['groups'][$old];
            if($this->read($db->where_in('jenis_transaksi',$this->jenisList)->get_where('tr_jurnal',['no_transaksi'=>$old])))throw new RuntimeException('Masih ada tr_jurnal berkunci '.$old.'.');
            $now=$this->read($db->where_in('id',array_map('intval',array_column($b['tr_jurnal'],'id')))->order_by('id','asc')->get('tr_jurnal'));
            $exp=$b['tr_jurnal'];foreach($exp as &$r)$r['no_transaksi']=$new;unset($r);
            if(Payment_normalization::digest($now)!==Payment_normalization::digest($exp))throw new RuntimeException('tr_jurnal '.$old.' berbeda dari backup selain no_transaksi.');
            $result['groups']++;$result['tr_jurnal']+=count($now);
            if(empty($item['ledger']))continue;
            $acc=$this->ledger($item['ledger']);
            $nowJ=$this->read($acc->where_in('id',array_map('intval',array_column($b['jurnal'],'id')))->order_by('id','asc')->get('jurnal'));
            $expJ=$b['jurnal'];foreach($expJ as &$r)$r['no_reff']=$new;unset($r);
            if(Payment_normalization::digest($nowJ)!==Payment_normalization::digest($expJ))throw new RuntimeException('jurnal accounting '.$old.' berbeda dari backup selain no_reff.');
            $nowH=$this->read($acc->where_in('nomor',array_map('strval',array_column($b['japh'],'nomor')))->order_by('nomor','asc')->get('japh'));
            $expH=$b['japh'];foreach($expH as &$h){$was=(string)$h['note']==='Payment '.$old;$h['no_reff']=$new;if($was)$h['note']='Payment '.$new;}unset($h);
            if(Payment_normalization::digest($nowH)!==Payment_normalization::digest($expH))throw new RuntimeException('japh '.$old.' berbeda dari backup selain no_reff/note.');
            $result['jurnal']+=count($nowJ);$result['japh']+=count($nowH);
        }
        return $result;
    }

    /** Restore the before-image from the backup (newest state is replaced, aliases of this run deactivated). */
    public function rollback($path)
    {
        Payment_reference::acquire($this->ci->db);
        try{
            list($manifest,$backup)=$this->loadFiles($path);
            $db=$this->ci->db;
            foreach($manifest['items'] as &$item){
                if($item['status']!=='applied')continue;
                $old=$item['old'];$new=$item['voucher'];$b=$backup['groups'][$old];
                $trIds=array_map('intval',array_column($b['tr_jurnal'],'id'));
                if(!$db->trans_begin())throw new RuntimeException('Transaksi rollback gagal dimulai.');
                $db->where_in('id',$trIds)->where('no_transaksi',$new)->update('tr_jurnal',['no_transaksi'=>$old]);
                if($db->affected_rows()!==count($trIds)){$db->trans_rollback();throw new RuntimeException('Rollback tr_jurnal '.$old.' tidak sesuai.');}
                $db->where('run_id',$manifest['run_id'])->where_in('journal_id',$trIds)->update('tr_jurnal_payment_reference_aliases',['active'=>0]);
                if($db->trans_status()===false||!$db->trans_commit())throw new RuntimeException('Commit rollback gagal.');
                if(!empty($item['ledger']))$this->restoreAccounting($this->ledger($item['ledger']),$b,$new);
                $item['status']='rolled_back';
            }
            unset($item);
            Payment_patch::save($path,$manifest);
            return $manifest;
        }finally{Payment_reference::release($this->ci->db);}
    }
}
