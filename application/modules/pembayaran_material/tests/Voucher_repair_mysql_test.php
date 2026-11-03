<?php
require __DIR__.'/Voucher_repair_test.php';
mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
class RepairFixtureDb extends mysqli
{
    public $commitFault='';
    public function commit($flags=0,$name=null){if($this->commitFault==='before')return false;$ok=parent::commit($flags,$name);return $this->commitFault==='after'?false:$ok;}
}
$schema='codex_voucher_repair_'.bin2hex(random_bytes(5));
$db=new RepairFixtureDb('127.0.0.1','root',getenv('JOURNAL_TEST_PASSWORD')?:'','',3307);$db->set_charset('utf8mb4');
$db->query('CREATE DATABASE `'.$schema.'`');$db->select_db($schema);
$backup=sys_get_temp_dir().'/'.$schema.'.json';$initialChecks=$checks;
try{
    $db->query('CREATE TABLE tr_jurnal LIKE db_sendigs_ss.tr_jurnal');
    $db->query('CREATE TABLE payment_approve (id VARCHAR(32),id_payment VARCHAR(32)) ENGINE=InnoDB');
    $db->query('CREATE TABLE tr_payment_paid (id VARCHAR(32)) ENGINE=InnoDB');
    $reset=function()use($db,$e,$backup){
        $db->commitFault='';$db->query('TRUNCATE tr_jurnal');if(is_file($backup))unlink($backup);
        foreach($e['journal'] as $row){$sql='INSERT INTO tr_jurnal (`'.implode('`,`',array_keys($row)).'`) VALUES ('.implode(',',array_fill(0,count($row),'?')).')';$s=$db->prepare($sql);$values=array_values($row);$s->bind_param(str_repeat('s',count($values)),...$values);$s->execute();}
    };
    $collect=function()use($db,$e,$ownership){$fresh=$e;$fresh['journal']=$db->query('SELECT * FROM tr_jurnal ORDER BY id')->fetch_all(MYSQLI_ASSOC);$fresh['journal_sha256_before']=Payment_voucher_reconciliation::fingerprint($fresh['journal']);return ['evidence'=>$fresh,'ownership'=>$ownership];};
    $snapshot=function()use($db){return Payment_voucher_reconciliation::fingerprint($db->query('SELECT * FROM tr_jurnal ORDER BY id')->fetch_all(MYSQLI_ASSOC));};
    $reset();$before=$snapshot();$dry=Payment_voucher_repair::execute($db,$collect,$review,$backup);
    checkRepair($dry['plan']['ready']&&$before===$snapshot()&&!file_exists($backup),'dry-run has no database/backup writes');
    $applied=Payment_voucher_repair::execute($db,$collect,$review,$backup,true);
    checkRepair($applied['status']==='applied'&&$applied['source_rows']===12,'apply commits twelve source lines');
    $after=$snapshot();$saved=json_decode(file_get_contents($backup),true);
    checkRepair($saved['journal_hash']===$before&&count($saved['journal'])===11,'durable backup includes exact original snapshot');
    checkRepair($db->query("SELECT COUNT(*) n FROM tr_jurnal WHERE id IN (11085,11086,11087) AND id_company='7' AND nm_company='STM'")->fetch_assoc()['n']==3,'only intended Cash company fields corrected');
    checkRepair($db->query("SELECT SUM(debit) d,SUM(kredit) k FROM tr_jurnal WHERE jenis_transaksi='Payment'")->fetch_assoc()===['d'=>'8713202.00','k'=>'8713202.00'],'Payment balanced exact cents');
    checkRepair(Payment_voucher_reconciliation::fingerprint(array_slice($saved['journal'],9))===Payment_voucher_reconciliation::fingerprint($db->query('SELECT * FROM tr_jurnal WHERE id IN (11088,11089) ORDER BY id')->fetch_all(MYSQLI_ASSOC)),'refill unchanged');
    checkRepair(Payment_voucher_repair::execute($db,$collect,$review,$backup,true)['status']==='already_applied'&&$after===$snapshot(),'rerun never inserts second bank row');
    $db->query("UPDATE tr_jurnal SET keterangan='Changed' WHERE id=11085");$tampered=$snapshot();
    try{Payment_voucher_repair::execute($db,$collect,$review,$backup,true);checkRepair(false,'changed result');}catch(RuntimeException $error){checkRepair($tampered===$snapshot(),'changed result rejects rerun without changes');}
    $reset();$before=$snapshot();try{Payment_voucher_repair::execute($db,$collect,[],$backup,true);checkRepair(false,'missing review');}catch(DomainException $error){checkRepair($before===$snapshot()&&!file_exists($backup),'blocked review rolls back before write');}
    $wrong=function()use($collect){$data=$collect();$data['evidence']['journal_sha256_before']='changed';return $data;};
    try{Payment_voucher_repair::execute($db,$wrong,$review,$backup,true);checkRepair(false,'source mismatch');}catch(RuntimeException $error){checkRepair($before===$snapshot(),'source mismatch rejects before mutation');}
    $missing=function()use($collect){$data=$collect();$data['ownership']['target_coas']=[];return $data;};
    try{Payment_voucher_repair::execute($db,$missing,$review,$backup,true);checkRepair(false,'missing COA');}catch(DomainException $error){checkRepair($before===$snapshot(),'missing target COA refuses write');}
    $db->query('ALTER TABLE tr_jurnal DROP COLUMN created_date');$before=$snapshot();
    try{Payment_voucher_repair::execute($db,$collect,$review,$backup,true);checkRepair(false,'insert failure');}catch(mysqli_sql_exception $error){checkRepair($before===$snapshot(),'insert failure rolls back three preceding company updates');}
    $db->query('ALTER TABLE tr_jurnal ADD created_date DATETIME NULL');
    foreach(['before','after'] as $fault){
        $reset();$before=$snapshot();$db->commitFault=$fault;
        try{Payment_voucher_repair::execute($db,$collect,$review,$backup,true);checkRepair(false,'commit uncertainty');}catch(RuntimeException $error){checkRepair(strpos($error->getMessage(),'tidak ada retry otomatis')!==false,'commit uncertainty explicit: '.$fault);}
        $db->commitFault='';
        if($fault==='before')checkRepair($before===$snapshot(),'before-commit failure rolls back');
        else checkRepair(Payment_voucher_repair::execute($db,$collect,$review,$backup,true)['status']==='already_applied','lost commit acknowledgment detected by snapshot and marker');
    }
    $reset();$second=new mysqli('127.0.0.1','root',getenv('JOURNAL_TEST_PASSWORD')?:'',$schema,3307);
    $lock='voucher-repair:'.Payment_voucher_reconciliation::VOUCHER;$second->query("SELECT GET_LOCK('$lock',0)");$before=$snapshot();
    try{Payment_voucher_repair::execute($db,$collect,$review,$backup,true);checkRepair(false,'concurrent lock');}catch(RuntimeException $error){checkRepair($before===$snapshot(),'concurrent correction cannot write');}finally{$second->query("SELECT RELEASE_LOCK('$lock')");$second->close();}
    echo 'PASS voucher repair disposable MySQL: '.($checks-$initialChecks)." assertions\n";
}finally{
    $db->rollback();if(!preg_match('/^codex_voucher_repair_[a-f0-9]{10}$/D',$schema))throw new RuntimeException('Unsafe cleanup');
    $db->query('DROP DATABASE `'.$schema.'`');$db->close();if(is_file($backup))unlink($backup);
}
