<?php
require __DIR__.'/Coa_completion_test.php';
mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
$prefix='codex_coa_complete_'.bin2hex(random_bytes(5));$schemas=[$prefix.'_source',$prefix.'_target'];
$db=new mysqli('127.0.0.1','root',getenv('JOURNAL_TEST_PASSWORD')?:'','',3307);$db->set_charset('utf8mb4');
$backup=sys_get_temp_dir().'/'.$prefix.'.json';$initial=$checks;
try{
    foreach($schemas as $name)$db->query('CREATE DATABASE `'.$name.'`');
    $source='`'.$schemas[0].'`.coa_master';$target='`'.$schemas[1].'`.coa_master';$codes="'".implode("','",array_keys(Payment_coa_completion::ACCOUNTS))."'";
    $db->query('CREATE TABLE '.$source.' LIKE db_sendigs_gl_ss.coa_master');$db->query('CREATE TABLE '.$target.' LIKE db_sendigs_ss_stm.coa_master');
    $db->query('INSERT INTO '.$source.' SELECT * FROM db_sendigs_gl_ss.coa_master WHERE no_perkiraan IN ('.$codes.')');
    $db->query('UPDATE '.$source.' SET saldoawal=999,debet=55,kredit=44');
    $db->query("INSERT INTO $target SELECT * FROM db_sendigs_ss_stm.coa_master WHERE no_perkiraan='1101-02-01'");
    $original=$db->query('SELECT * FROM '.$target.' ORDER BY id')->fetch_all(MYSQLI_ASSOC);
    $dry=Payment_coa_completion::execute($db,$schemas[0],$schemas[1],$backup);
    coaCheck($dry['missing_count']===4&&!is_file($backup),'dry-run no backup/write');
    $result=Payment_coa_completion::execute($db,$schemas[0],$schemas[1],$backup,true);
    coaCheck($result['inserted_count']===4&&$result['existing_rows_unchanged'],'real MyISAM completion verified');
    coaCheck($db->query('SELECT COUNT(*) n FROM '.$target.' WHERE no_perkiraan IN ('.$codes.') AND saldoawal=0 AND debet=0 AND kredit=0')->fetch_assoc()['n']==4,'no source balances/movements imported');
    coaCheck($db->query('SELECT COUNT(*) n FROM '.$source.' WHERE saldoawal=999')->fetch_assoc()['n']==4,'source untouched');
    coaCheck(json_decode(file_get_contents($backup),true)['target_before']===$original,'exact prior master backup');
    coaCheck(Payment_coa_completion::execute($db,$schemas[0],$schemas[1],$backup,true)['status']==='already_complete','idempotent completion');
    $db->query("UPDATE $target SET level=4 WHERE no_perkiraan='6207-00-00'");
    try{Payment_coa_completion::execute($db,$schemas[0],$schemas[1],$backup,true);coaCheck(false,'existing conflict');}catch(DomainException $e){coaCheck(true,'target conflict blocked without overwriting');}
    $db->query('DELETE FROM '.$target.' WHERE no_perkiraan IN ('.$codes.')');unlink($backup);
    $db->query('ALTER TABLE '.$target.' ADD UNIQUE KEY fixture_level (level)');
    try{Payment_coa_completion::execute($db,$schemas[0],$schemas[1],$backup,true);coaCheck(false,'MyISAM partial failure');}catch(RuntimeException $e){coaCheck(strpos($e->getMessage(),'mungkin terisi sebagian')!==false,'MyISAM partial failure explicit, no false rollback claim');}
    coaCheck($db->query('SELECT COUNT(*) n FROM '.$target.' WHERE no_perkiraan IN ('.$codes.')')->fetch_assoc()['n']==2,'confirmed partial rows preserved for review');
    echo 'PASS COA completion disposable MySQL: '.($checks-$initial)." assertions\n";
}finally{
    foreach($schemas as $name){if(!preg_match('/^codex_coa_complete_[a-f0-9]{10}_(source|target)$/D',$name))throw new RuntimeException('Unsafe fixture cleanup');$db->query('DROP DATABASE `'.$name.'`');}
    $db->close();if(is_file($backup))unlink($backup);
}
