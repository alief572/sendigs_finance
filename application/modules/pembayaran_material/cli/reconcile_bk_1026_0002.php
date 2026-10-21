<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
if($argc!==1){fwrite(STDERR,"Usage: php reconcile_bk_1026_0002.php (read-only; no options or apply mode)\n");exit(2);}
$root=dirname(__DIR__,4);
define('BASEPATH',$root.'/system/');define('ENVIRONMENT','development');
require $root.'/application/config/development/database.php';
require dirname(__DIR__).'/libraries/Payment_voucher_reconciliation.php';
mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
$conn=null;$exitCode=0;
try{
    $settings=$db;$cfg=$settings['default'];
    $conn=new mysqli('127.0.0.1',$cfg['username'],$cfg['password'],$cfg['database'],3307);
    $conn->set_charset('utf8mb4');$conn->query('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');$conn->query('START TRANSACTION READ ONLY');
    $read=function($sql,$params=[])use($conn){
        if(!preg_match('/^SELECT\b/i',$sql))throw new RuntimeException('Only SELECT is allowed.');
        $statement=$conn->prepare($sql);
        if($params)$statement->bind_param(str_repeat('s',count($params)),...$params);
        $statement->execute();return $statement->get_result()->fetch_all(MYSQLI_ASSOC);
    };
    $identifier=function($name){if(!preg_match('/^[A-Za-z0-9_]+$/D',$name))throw new RuntimeException('Invalid configured identifier.');return '`'.$name.'`';};
    $schema=$cfg['database'];$qualified=$identifier($schema).'.';
    $voucher=Payment_voucher_reconciliation::VOUCHER;
    $approvals=$read('SELECT id,id_payment,no_doc,tipe,jumlah,status,tgl_bayar,coa_bank,payment_bank,total_payment,selisih,kurs_payment FROM '.$qualified.'payment_approve WHERE id_payment=? ORDER BY id',[$voucher]);
    $paid=$read('SELECT id,bank_charge,admin_charge_bearer FROM '.$qualified.'tr_payment_paid WHERE id=?',[$voucher]);
    $journalSql='SELECT * FROM '.$qualified.'tr_jurnal WHERE id BETWEEN 11079 AND 11089 ORDER BY id';
    $journal=$read($journalSql);$beforeHash=Payment_voucher_reconciliation::fingerprint($journal);
    $keys=array_merge([$voucher],array_keys(Payment_voucher_reconciliation::SOURCES));
    foreach($approvals as $p)$keys[]=$p['no_doc'];
    $numberKeys=[];foreach($journal as $row)if(!empty($row['no_jurnal']))$numberKeys[]=$row['no_jurnal'];
    $errors=[];$ledger=[];$postings=[];$bank=null;
    $schemaColumns=function($name,$table)use($read){return array_column($read('SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? ORDER BY ORDINAL_POSITION',[$name,$table]),'COLUMN_NAME');};
    $scan=function($name,$table,$refs,$keys,$numbers,$bankCoa=null)use($read,$identifier,$schemaColumns){
        $columns=$schemaColumns($name,$table);
        if(!$columns)throw new RuntimeException('Required reconciliation table unavailable.');
        $refs=array_values(array_intersect($refs,$columns));
        if(!$refs)throw new RuntimeException('Reference columns unavailable.');
        $where=[];$params=[];$allKeys=array_values(array_unique(array_filter(array_merge($keys,$numbers))));
        foreach($refs as $field)foreach($allKeys as $key){$where[]='LOCATE(?,'.$identifier($field).')>0';$params[]=$key;}
        $dateField=in_array('tanggal',$columns,true)?'tanggal':(in_array('tgl',$columns,true)?'tgl':null);
        $hasBank=$bankCoa!==null && $dateField && in_array('no_perkiraan',$columns,true) && in_array('kredit',$columns,true);
        if($hasBank){$where[]='('.$identifier($dateField).'=? AND no_perkiraan=? AND kredit IN (8713202.00,8920202.00,207000.00))';$params[]='2026-10-05';$params[]=$bankCoa;}
        $rows=$read('SELECT * FROM '.$identifier($name).'.'.$identifier($table).' WHERE '.implode(' OR ',$where),$params);
        return array_values(array_filter($rows,function($row)use($refs,$allKeys,$hasBank,$dateField,$bankCoa){
            foreach($refs as $field)if(Payment_voucher_reconciliation::matches($row[$field],$allKeys))return true;
            return $hasBank && substr((string)$row[$dateField],0,10)==='2026-10-05' && $row['no_perkiraan']===$bankCoa
                && in_array(Payment_journal_validator::cents($row['kredit']),[871320200,892020200,20700000],true);
        }));
    };
    $bankValues=array_values(array_unique(array_column($approvals,'coa_bank')));
    if(count($bankValues)===1 && $bankValues[0]!==''){
        $banks=$read('SELECT id,nama,coa_bank,deleted FROM '.$qualified.'ms_bank WHERE id=? OR coa_bank=?',[$bankValues[0],$bankValues[0]]);
        $banks=array_values(array_filter($banks,function($b){return (string)$b['deleted']==='0';}));
        if(count($banks)===1){
            $bank=$banks[0];$bank['payment_source_consistent']=count($approvals)===6;
            $coa=$read('SELECT no_perkiraan,nama FROM '.$identifier($settings['accounting']['database']).'.coa_master WHERE no_perkiraan=?',[$bank['coa_bank']]);
            $bank['coa_verified']=count($coa)===1;$bank['coa_name']=$coa[0]['nama'] ?? null;
        }
    }
    try{
        $postings=$scan($schema,'tr_jurnal_postings',['no_transaksi','accounting_numbers'],$keys,$numberKeys);
        foreach($postings as $posting){
            $numbers=json_decode($posting['accounting_numbers'],true);
            if(!is_array($numbers)){$errors[]='Metadata nomor accounting tidak valid.';continue;}
            foreach($numbers as $entry)if(is_array($entry)&&is_string($entry['number'] ?? null))$numberKeys[]=$entry['number'];
        }
    }catch(Throwable $e){$errors[]='Metadata posting tidak dapat diperiksa lengkap.';}
    $targets=[[$schema,'jurnaltras',['no_reff','no_request','nomor','keterangan']]];
    foreach(['accounting','accounting_stm','accounting_vuca','accounting_sustain'] as $group){
        if($settings[$group]['hostname']!==$cfg['hostname']){$errors[]='Host accounting berbeda dan belum diperiksa: '.$group;continue;}
        $name=$settings[$group]['database'];
        $targets[]=[$name,'jurnal',['no_reff','nomor','keterangan']];$targets[]=[$name,'japh',['no_reff','nomor','note']];
    }
    $targets=array_values(array_reduce($targets,function($all,$target){$all[$target[0].'.'.$target[1]]=$target;return $all;},[]));
    $numberKeys=array_values(array_unique($numberKeys));
    for($pass=0;$pass<4;$pass++){
        $previous=$numberKeys;
        foreach($targets as $target){
            $label=$target[0].'.'.$target[1];
            try{
                $ledger[$label]=$scan($target[0],$target[1],$target[2],$keys,$numberKeys,$bank['coa_bank'] ?? null);
                foreach($ledger[$label] as $entry)if(!empty($entry['nomor']))$numberKeys[]=$entry['nomor'];
            }catch(Throwable $e){$errors[]='Target accounting tidak dapat diperiksa lengkap: '.$label;}
        }
        $numberKeys=array_values(array_unique($numberKeys));sort($numberKeys,SORT_STRING);sort($previous,SORT_STRING);
        if($numberKeys===$previous)break;
        if($pass===3)$errors[]='Penelusuran nomor accounting belum mencapai hasil stabil.';
    }
    $relatedJournal=$scan($schema,'tr_jurnal',['no_transaksi','no_jurnal','keterangan'],$keys,[]);
    foreach($relatedJournal as $row)if((int)$row['id']<11079 || (int)$row['id']>11089)$errors[]='Jurnal terkait di luar snapshot awal ditemukan: '.$row['id'];
    $afterHash=Payment_voucher_reconciliation::fingerprint($read($journalSql));
    $afterApprovals=$read('SELECT id,id_payment,no_doc,tipe,jumlah,status,tgl_bayar,coa_bank,payment_bank,total_payment,selisih,kurs_payment FROM '.$qualified.'payment_approve WHERE id_payment=? ORDER BY id',[$voucher]);
    $evidence=['approvals'=>$approvals,'paid'=>$paid,'journal'=>$journal,'related_journal_count'=>count($relatedJournal),
        'bank'=>$bank,'postings'=>$postings,'ledger'=>$ledger,'linked_accounting_numbers'=>$numberKeys,'collection_errors'=>array_values(array_unique($errors)),
        'journal_sha256_before'=>$beforeHash,'journal_sha256_after'=>$afterHash,
        'baseline_matches'=>$beforeHash==='0188d46ad78db0b71a80c438f7e93e5c0879bf452dbb224a382cc591b6816a17',
        'snapshot_unchanged'=>$beforeHash===$afterHash && $approvals===$afterApprovals];
    $report=Payment_voucher_reconciliation::assess($evidence);
    $report['transaction']='READ ONLY; ROLLBACK';
    echo json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR),PHP_EOL;
}catch(Throwable $e){fwrite(STDERR,"Rekonsiliasi tidak lengkap; tidak ada koreksi yang diterapkan. Periksa akses/config/schema lokal.\n");$exitCode=2;}
finally{if($conn){$conn->rollback();$conn->close();}}
exit($exitCode);
