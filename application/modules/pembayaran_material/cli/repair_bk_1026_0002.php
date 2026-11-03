<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
$args=array_slice($argv,1);$apply=false;$reviewPath=null;
foreach($args as $arg){if($arg==='--apply'&&!$apply)$apply=true;elseif(strpos($arg,'--review=')===0&&$reviewPath===null)$reviewPath=substr($arg,9);else{fwrite(STDERR,"Usage: php repair_bk_1026_0002.php [--review=review.json] [--apply]\n");exit(2);}}
if($apply&&!$reviewPath){fwrite(STDERR,"Apply memerlukan berkas keputusan review yang telah dikonfirmasi.\n");exit(2);}
$root=dirname(__DIR__,4);define('BASEPATH',$root.'/system/');define('ENVIRONMENT','development');
require $root.'/application/config/development/database.php';require dirname(__DIR__).'/libraries/Payment_voucher_repair.php';
mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);$conn=null;$exitCode=0;
date_default_timezone_set('Asia/Jakarta');
try{
    $review=$reviewPath?json_decode(file_get_contents($reviewPath),true,512,JSON_THROW_ON_ERROR):[];
    $cfg=$db['default'];if($cfg['database']!=='db_sendigs_ss')throw new RuntimeException('Database source lokal tidak cocok.');
    $conn=new mysqli('127.0.0.1',$cfg['username'],$cfg['password'],$cfg['database'],3307);$conn->set_charset('utf8mb4');
    if(!$apply)$conn->query('START TRANSACTION READ ONLY');
    $identifier=function($value){if(!preg_match('/^[A-Za-z0-9_]+$/D',$value))throw new RuntimeException('Schema tidak valid.');return '`'.$value.'`';};
    $hr=$identifier($db['hris']['database']);$stm=$identifier($db['accounting_stm']['database']);
    $collect=function()use($conn,$hr,$stm){
        $process=proc_open([PHP_BINARY,__DIR__.'/reconcile_bk_1026_0002.php'],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes,null,null,['bypass_shell'=>true]);
        if(!is_resource($process))throw new RuntimeException('Collector tidak tersedia.');fclose($pipes[0]);
        $json=stream_get_contents($pipes[1]);$error=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);$exit=proc_close($process);
        if($exit!==0)throw new RuntimeException('Collector rekonsiliasi gagal; apply diblokir.');
        $report=json_decode($json,true,512,JSON_THROW_ON_ERROR);$e=$report['evidence'];$e['source_contract_matches']=$report['source_contract_matches'];
        $cash=$conn->query("SELECT p.no_non_po no_doc,d.company_id,p.no_pr,h.id_dept FROM tr_pr_non_po p JOIN rutin_non_planning_header h ON h.no_pr=p.no_pr LEFT JOIN $hr.departments d ON d.id=h.id_dept WHERE p.no_non_po IN ('DPM-2026-00081','DPM-2026-00083','DPM-2026-00082') ORDER BY p.no_non_po")->fetch_all(MYSQLI_ASSOC);
        $periodik=$conn->query("SELECT p.no_doc,d.company_id FROM tr_pengajuan_rutin p LEFT JOIN $hr.departments d ON d.id=p.departement WHERE p.no_doc IN ('PERIODIK-2026-00181','PERIODIK-2026-00177') ORDER BY p.no_doc")->fetch_all(MYSQLI_ASSOC);
        $coas=array_column($conn->query("SELECT no_perkiraan FROM $stm.coa_master")->fetch_all(MYSQLI_ASSOC),'no_perkiraan');
        return ['evidence'=>$e,'ownership'=>['cash'=>$cash,'periodik'=>$periodik,'target_coas'=>$coas]];
    };
    $result=Payment_voucher_repair::execute($conn,$collect,$review,$root.'/scratch/bk-1026-0002-repair-before.json',$apply);
    echo json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR),PHP_EOL;
}catch(Throwable $error){
    $message=$error instanceof DomainException?$error->getMessage():'Koreksi tidak terkonfirmasi. Periksa snapshot/backup dan hasil dry-run sebelum mencoba ulang.';
    fwrite(STDERR,$message.PHP_EOL);$exitCode=2;
}finally{if($conn){$conn->rollback();$conn->close();}}
exit($exitCode);
