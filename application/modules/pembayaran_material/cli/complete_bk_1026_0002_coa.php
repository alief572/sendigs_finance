<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
if($argc>2||($argc===2&&$argv[1]!=='--apply')){fwrite(STDERR,"Usage: php complete_bk_1026_0002_coa.php [--apply]\n");exit(2);}
$apply=$argc===2;$root=dirname(__DIR__,4);define('BASEPATH',$root.'/system/');define('ENVIRONMENT','development');
require $root.'/application/config/development/database.php';require dirname(__DIR__).'/libraries/Payment_coa_completion.php';
mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);$conn=null;$code=0;
try{
    $cfg=$db['default'];$source=$db['accounting']['database'];$target=$db['accounting_stm']['database'];
    if($cfg['database']!=='db_sendigs_ss'||$source!=='db_sendigs_gl_ss'||$target!=='db_sendigs_ss_stm')throw new RuntimeException('Konfigurasi lokal tidak sesuai.');
    $conn=new mysqli('127.0.0.1',$cfg['username'],$cfg['password'],$cfg['database'],3307);$conn->set_charset('utf8mb4');
    $trigger=$conn->query("SELECT COUNT(*) n FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA='db_sendigs_ss_stm' AND EVENT_OBJECT_TABLE='coa_master'")->fetch_assoc();
    if((int)$trigger['n']!==0)throw new DomainException('Trigger master belum ditinjau.');
    echo json_encode(Payment_coa_completion::execute($conn,$source,$target,$root.'/scratch/bk0002-coa-master-before.json',$apply),JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR),PHP_EOL;
}catch(Throwable $error){fwrite(STDERR,($error instanceof DomainException?$error->getMessage():'Completion belum terkonfirmasi; periksa backup master sebelum mengulang.').PHP_EOL);$code=2;}
finally{if($conn)$conn->close();}
exit($code);
