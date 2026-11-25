<?php
require __DIR__.'/Payment_identity_repair_test.php';
function is_php($version){return version_compare(PHP_VERSION,$version,'>=');}
$mysqlBase=$checks;mysqli_report(MYSQLI_REPORT_OFF);
$port=(int)(getenv('JOURNAL_TEST_PORT') ?: 33317);$password=getenv('JOURNAL_TEST_PASSWORD') ?: '';
$admin=@new mysqli('127.0.0.1','root',$password,'',$port);
if($admin->connect_errno){fwrite(STDERR,"Disposable MySQL unavailable; application data not touched.\n");exit(2);}
$prefix='codex_identity_'.bin2hex(random_bytes(5));$schemas=[$prefix.'_source',$prefix.'_stm',$prefix.'_sustain'];$connections=[];$paths=[];
function identityMysqlQuery($conn,$sql){if(!$conn->query($sql))throw new RuntimeException('Disposable fixture SQL failed.');}
try{
    foreach($schemas as $schema)identityMysqlQuery($admin,'CREATE DATABASE `'.$schema.'` CHARACTER SET utf8mb4');
    $seed=identityFixture();$ci=identityFixture();$schemaMap=['source'=>$schemas[0],'accounting_stm'=>$schemas[1],'accounting_sustain'=>$schemas[2]];
    foreach($schemaMap as $name=>$schema){
        $conn=DB(['dbdriver'=>'mysqli','hostname'=>'127.0.0.1','port'=>$port,'username'=>'root','password'=>$password,'database'=>$schema,'db_debug'=>false,'pconnect'=>false,'char_set'=>'utf8mb4','dbcollat'=>'utf8mb4_unicode_ci'],true);$connections[]=$conn;
        $original=$name==='source'?$seed->db:$seed->load->dbs[$name];
        $tables=$name==='source'?['tr_jurnal','payment_approve','tr_payment_paid','tr_jurnal_postings','tr_jurnal_payment_attempts','jurnaltras','tr_jurnal_payment_reference_aliases']:['jurnal','japh','pastibisa_tb_cabang','coa_master'];
        foreach($tables as $table){
            $columns=$original->query('PRAGMA table_info('.$table.')')->result_array();$ddl=[];$pk=[];
            foreach($columns as $column){$type=strtoupper($column['type']);if($type==='TEXT')$type='VARCHAR(255)';$ddl[]='`'.$column['name'].'` '.$type;if($column['pk'])$pk[$column['pk']]='`'.$column['name'].'`';}
            if($pk){ksort($pk);$ddl[]='PRIMARY KEY ('.implode(',',$pk).')';}
            identityMysqlQuery($conn,'CREATE TABLE `'.$table.'` ('.implode(',',$ddl).') ENGINE='.($name==='source'?'InnoDB':'MyISAM'));
            $rows=$original->get($table)->result_array();if($rows&&!$conn->insert_batch($table,$rows))throw new RuntimeException('Fixture seed failed for '.$table.': '.$conn->error()['message']);
        }
        if($name==='source')$ci->db=$conn;else $ci->load->dbs[$name]=$conn;
    }
    $path=identityPath();$paths[]=$path;$repair=new Payment_identity_repair($ci);$before=identitySnapshot($ci);$repair->prepare($path);
    $repair->run($path);check($repair->verify($path)['groups']===22,'real InnoDB/MyISAM apply and exact verification');
    $after=identitySnapshot($ci);$repair->run($path);check(identitySnapshot($ci)===$after,'real MySQL idempotency');
    $repair->run($path,true);$repair->verify($path,true);check(identitySnapshot($ci)===$before,'real MyISAM rollback exact');
    foreach(['accounting_partial','staging_after_accounting'] as $case){
        $path=identityPath();$paths[]=$path;$repair->prepare($path);$a=$ci->load->dbs['accounting_stm'];
        if($case==='accounting_partial')identityMysqlQuery($a,"CREATE TRIGGER fail_detail BEFORE UPDATE ON jurnal FOR EACH ROW BEGIN IF OLD.id=32 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture'; END IF; END");
        else identityMysqlQuery($ci->db,"CREATE TRIGGER fail_staging BEFORE UPDATE ON tr_jurnal FOR EACH ROW BEGIN IF OLD.id=31 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture'; END IF; END");
        $beforeDetails=Payment_normalization::digest($a->get('jurnal')->result_array());$failed=false;try{$repair->run($path);}catch(Throwable $e){$failed=true;}
        check($failed,'real MySQL injected failure '.$case);
        check(Payment_normalization::digest($a->get('jurnal')->result_array())===$beforeDetails,'real MyISAM compensation '.$case);
        check($ci->db->get_where('tr_jurnal',['id'=>31])->row()->keterangan==='Cost - APP-BK--0926-0011','real source rollback '.$case);
        if($case==='accounting_partial')identityMysqlQuery($a,'DROP TRIGGER fail_detail');else identityMysqlQuery($ci->db,'DROP TRIGGER fail_staging');
        $repair->run($path,true);$repair->verify($path,true);
    }
    echo 'PASS Payment identity MySQL: '.($checks-$mysqlBase)." assertions (InnoDB source, MyISAM accounting)\n";
}finally{
    foreach($paths as $path)identityClean($path);
    foreach($connections as $conn)$conn->close();
    foreach($schemas as $schema)identityMysqlQuery($admin,'DROP DATABASE IF EXISTS `'.$schema.'`');$admin->close();
}
