<?php
define('BASEPATH',dirname(__DIR__,4).'/system/');define('APPPATH',dirname(__DIR__,3).'/');
function log_message($level,$message){} function show_error($message){throw new RuntimeException($message);} function load_class($name,$directory){return null;}
function is_php($version){return version_compare(PHP_VERSION,$version,'>=');} class BF_Model {} class CI_Model {}
require BASEPATH.'database/DB.php';
require dirname(__DIR__).'/libraries/Payment_patch.php';require dirname(__DIR__).'/libraries/Payment_posting.php';
require dirname(__DIR__).'/libraries/Payment_normalization.php';
require dirname(__DIR__,2).'/jurnal_payment/models/Jurnal_payment_nomor_model.php';
mysqli_report(MYSQLI_REPORT_OFF);
$port=(int)(getenv('JOURNAL_TEST_PORT') ?: 33317);$password=getenv('JOURNAL_TEST_PASSWORD') ?: '';
$admin=@new mysqli('127.0.0.1','root',$password,'',$port);
if($admin->connect_errno){fwrite(STDERR,"Disposable MySQL unavailable; application data not touched.\n");exit(2);}
$prefix='codex_payment_ref_'.bin2hex(random_bytes(5));$schemas=[$prefix.'_source',$prefix.'_target',$prefix.'_auto'];
define('DBACC_STM',$schemas[1]);define('DBACC_VUCA',$schemas[1]);define('DBACC_SUST',$schemas[1]);
$checks=0;function check($value,$message){global $checks;$checks++;if(!$value)throw new RuntimeException($message);}
function sql($db,$query){$r=$db->query($query);if($r===false)throw new RuntimeException('Fixture SQL failed.');return $r;}
$path=dirname(__DIR__,4).'/scratch/payment-mysql-'.bin2hex(random_bytes(5)).'.json';
try{
    foreach($schemas as $schema)sql($admin,"CREATE DATABASE `$schema` CHARACTER SET utf8mb4");
    $connections=[];foreach($schemas as $schema)$connections[]=DB(['dbdriver'=>'mysqli','hostname'=>'127.0.0.1','port'=>$port,'username'=>'root','password'=>$password,'database'=>$schema,'db_debug'=>false,'pconnect'=>false,'char_set'=>'utf8mb4','dbcollat'=>'utf8mb4_general_ci'],true);
    list($db,$acc,$auto)=$connections;
    sql($db,'CREATE TABLE tr_jurnal (id INT PRIMARY KEY,no_transaksi VARCHAR(255),jenis_transaksi VARCHAR(50),sts VARCHAR(10),id_company VARCHAR(10),nm_company VARCHAR(100),tgl_jurnal DATE,coa VARCHAR(30),debit DECIMAL(20,2),kredit DECIMAL(20,2),keterangan TEXT,tgl_posting DATETIME NULL,posted_by VARCHAR(100) NULL) ENGINE=InnoDB');
    sql($db,'CREATE TABLE payment_approve (id VARCHAR(100) PRIMARY KEY,id_payment VARCHAR(100),no_doc VARCHAR(100),tipe VARCHAR(50)) ENGINE=InnoDB');
    sql($db,'CREATE TABLE tr_payment_paid (id VARCHAR(100) PRIMARY KEY) ENGINE=InnoDB');
    sql($db,'CREATE TABLE tr_jurnal_postings (no_transaksi VARCHAR(255),jenis_transaksi VARCHAR(50)) ENGINE=InnoDB');
    sql($db,'CREATE TABLE jurnaltras (no_reff VARCHAR(255),no_request VARCHAR(255)) ENGINE=InnoDB');
    $migration=file_get_contents(dirname(__DIR__).'/migrations/007_payment_reference_attempts.sql');sql($db,$migration);sql($db,$migration);
    check($db->table_exists('tr_jurnal_payment_attempts'),'idempotent registry migration');
    foreach([$acc,$auto] as $target)foreach([
        'CREATE TABLE jurnal (tipe VARCHAR(10),nomor VARCHAR(100),tanggal DATETIME,no_reff VARCHAR(255),no_perkiraan VARCHAR(30),keterangan TEXT,debet DECIMAL(20,2),kredit DECIMAL(20,2)) ENGINE=InnoDB',
        'CREATE TABLE japh (nomor VARCHAR(100),tgl DATETIME,jml DECIMAL(20,2),kdcab VARCHAR(10),jenis_reff VARCHAR(10),no_reff VARCHAR(255),jenis_ap VARCHAR(10),note TEXT,user_id VARCHAR(100),ho_valid VARCHAR(10),batal VARCHAR(10)) ENGINE=InnoDB',
        'CREATE TABLE pastibisa_tb_cabang (id INT PRIMARY KEY,nocab VARCHAR(10),subcab VARCHAR(10),nobuk INT) ENGINE=InnoDB',
        'CREATE TABLE coa_master (no_perkiraan VARCHAR(30)) ENGINE=InnoDB'
    ] as $query)sql($target,$query);
    $acc->insert('pastibisa_tb_cabang',['id'=>1,'nocab'=>'101','subcab'=>'01','nobuk'=>0]);$acc->insert('coa_master',['no_perkiraan'=>'5101']);
    $db->insert('payment_approve',['id'=>'APP-1','id_payment'=>'BK--1026-0004','no_doc'=>'KS-2026-00331','tipe'=>'kasbon']);$db->insert('tr_payment_paid',['id'=>'BK--1026-0004']);
    foreach([[100,0],[0,100]] as $i=>$money)$db->insert('tr_jurnal',['id'=>$i+1,'no_transaksi'=>'APP-1','jenis_transaksi'=>'Payment','sts'=>'0','id_company'=>'7','nm_company'=>'STM','tgl_jurnal'=>'2026-10-15','coa'=>'5101','debit'=>$money[0],'kredit'=>$money[1],'keterangan'=>'Payment {REF:OLD-DOC}']);
    $loader=new class {public $targets; public function database($name,$return){return $this->targets[$name];}};
    $loader->targets=['accounting_stm'=>$acc,'accounting'=>$auto];
    $numbers=(new ReflectionClass('Jurnal_payment_nomor_model'))->newInstanceWithoutConstructor();$numbers->db=$db;
    $auth=new class {public function user_id(){return '42';} public function user_name(){return 'Finance';}};
    $ci=(object)['db'=>$db,'load'=>$loader,'auth'=>$auth,'Jurnal_payment_nomor_model'=>$numbers];
    $db->insert('payment_approve',['id'=>'BK--1026-0004','id_payment'=>'','no_doc'=>'PERIODIK-2026-00170','tipe'=>'periodik']);
    check((new Payment_reference($ci))->inspect('APP-1')['voucher']==='BK--1026-0004','source approval is not confused with an unrelated approval named like its voucher');
    $db->update('tr_jurnal',['no_transaksi'=>'BK--1026-0004']);
    check((new Payment_reference($ci))->inspect('BK--1026-0004')['voucher']==='BK--1026-0004','canonical voucher ignores invalid unpaid approval alternative');
    $db->insert('tr_payment_paid',['id'=>'OTHER']);$db->where('id','BK--1026-0004')->update('payment_approve',['id_payment'=>'OTHER']);
    try{(new Payment_reference($ci))->inspect('BK--1026-0004');throw new LogicException('Unexpected ambiguous success');}catch(Payment_reference_issue $e){check($e->category==='ambiguous','two paid relations without journal evidence remain blocked');}
    $db->update('tr_jurnal',['no_transaksi'=>'APP-1']);
    check((new Payment_patch($ci))->inventory()['items'][0]['status']==='skipped','patch cannot erase identity context when canonical target remains ambiguous');
    $db->where('id',1)->update('tr_jurnal',['keterangan'=>'Piutang Lain-lain - APP-1']);
    check((new Payment_reference($ci))->inspect('APP-1')['voucher']==='BK--1026-0004','stored source approval relationship establishes voucher');
    Payment_reference::acquire($db);
    $other=DB(['dbdriver'=>'mysqli','hostname'=>'127.0.0.1','port'=>$port,'username'=>'root','password'=>$password,'database'=>$schemas[0],'db_debug'=>false,'pconnect'=>false],true);
    check((int)$other->query('SELECT GET_LOCK(?, 0) AS acquired',[Payment_reference::LOCK])->row()->acquired===0,'real second connection cannot enter patch/payment/post lock');
    Payment_reference::release($db);
    $patch=new Payment_patch($ci);$manifest=$patch->inventory();check($manifest['items'][0]['status']==='eligible','real inventory eligible');
    Payment_patch::save($path,$manifest);$patch->run($path);
    check((int)$db->get_where('tr_jurnal',['no_transaksi'=>'BK--1026-0004'])->num_rows()===2,'real patch commits canonical voucher');
    check((new Payment_reference($ci))->inspect('BK--1026-0004')['voucher']==='BK--1026-0004','canonical patched rows retain journal evidence despite a paid approval collision');
    $patch->run($path);$patch->run($path,true);
    check((int)$db->get_where('tr_jurnal',['no_transaksi'=>'APP-1'])->num_rows()===2,'real rollback and rerun');
    $auto->insert('jurnal',['no_reff'=>'OLD-DOC']);
    check($patch->inventory()['items'][0]['status']==='skipped','automatic legacy accounting excludes pending group');
    $auto->delete('jurnal',['no_reff'=>'OLD-DOC']);
    $aliasMigration=file_get_contents(dirname(__DIR__).'/migrations/008_payment_reference_aliases.sql');sql($db,$aliasMigration);sql($db,$aliasMigration);
    $db->data_cache=[];
    check($db->table_exists('tr_jurnal_payment_reference_aliases'),'idempotent alias migration');
    $db->insert('payment_approve',['id'=>'APP-2','id_payment'=>'BK--1026-0004','no_doc'=>'DOC-2','tipe'=>'periodik']);
    foreach([[200,0],[0,200]] as $i=>$money)$db->insert('tr_jurnal',['id'=>$i+3,'no_transaksi'=>'APP-2','jenis_transaksi'=>'Payment','sts'=>'0','id_company'=>'7','nm_company'=>'STM','tgl_jurnal'=>'2026-10-15','coa'=>'5101','debit'=>$money[0],'kredit'=>$money[1],'keterangan'=>'Payment']);
    $auto->insert('japh',['no_reff'=>'APP-1','nomor'=>'EXISTING']);
    $normalizationPath=$path.'.normalization.json';$normalizer=new Payment_normalization($ci);$normalization=$normalizer->prepare($normalizationPath);
    check($normalization['items'][0]['status']==='eligible'&&count($normalization['items'][0]['groups'])===2,'real cohort includes pending groups despite existing accounting');
    $normalizer->run($normalizationPath);$verified=$normalizer->verify($normalizationPath);
    check($verified['changed_rows']===4&&$verified['metadata_unchanged'],'real merge changes only four numbers and preserves metadata');
    check((int)$db->get_where('tr_jurnal_payment_reference_aliases',['active'=>1])->num_rows()===4,'real active aliases cover merged members');
    check(in_array('APP-2',(new Payment_reference($ci))->resolve('BK--1026-0004')['references'],true),'real resolver retains old keys');
    try{(new Payment_reference($ci))->inspect('BK--1026-0004');throw new LogicException('Unexpected duplicate permission');}catch(Payment_reference_issue $e){check($e->category==='accounting_exists','old accounting reference still blocks posting after real normalization');}
    $normalizer->run($normalizationPath);$normalizer->run($normalizationPath,true);
    check((int)$db->get_where('tr_jurnal',['no_transaksi'=>'APP-1'])->num_rows()===2&&(int)$db->get_where('tr_jurnal',['no_transaksi'=>'APP-2'])->num_rows()===2,'real idempotent rerun and number-only rollback');
    check((int)$auto->get_where('japh',['nomor'=>'EXISTING'])->num_rows()===1&&(int)$db->get_where('tr_jurnal_payment_reference_aliases',['active'=>0])->num_rows()===4,'existing accounting and inactive alias history remain intact');
    $auto->delete('japh',['nomor'=>'EXISTING']);$db->where_in('id',[3,4])->delete('tr_jurnal');$db->delete('payment_approve',['id'=>'APP-2']);
    $response=(new Payment_posting($ci))->execute(1);
    check($response['save']===1,'real posting success');
    check((int)$acc->get_where('jurnal',['no_reff'=>'BK--1026-0004'])->num_rows()===2,'real detail voucher');
    check((int)$acc->get_where('japh',['no_reff'=>'BK--1026-0004'])->num_rows()===1,'real header voucher');
    check((int)$acc->get('pastibisa_tb_cabang')->row()->nobuk===1,'counter increment once');
    check($db->get('tr_jurnal_payment_attempts')->row()->status==='committed','real durable attempt');
    check((int)$db->get_where('tr_jurnal',['sts'=>'1'])->num_rows()===2,'real source committed');
    try{(new Payment_posting($ci))->execute(1);throw new LogicException('Unexpected duplicate success');}catch(DomainException $e){check(true,'duplicate refused');}
    check($patch->inventory()['items'][0]['status']==='skipped','posted group excluded');
    check((int)$acc->get('japh')->num_rows()===1,'posted accounting unchanged');
    $postedBefore=$db->get_where('tr_jurnal',['sts'=>'1'])->result_array();
    $targetBefore=$acc->get('jurnal')->result_array();
    foreach(['jurnal','japh','pastibisa_tb_cabang'] as $table)sql($acc,'ALTER TABLE '.$table.' ENGINE=MyISAM');
    $db->insert('payment_approve',['id'=>'LEGACY-APP','id_payment'=>'VOUCHER-2','no_doc'=>'DOC-LEGACY','tipe'=>'kasbon']);
    $db->insert('tr_payment_paid',['id'=>'VOUCHER-2']);
    foreach([[200,0],[0,200]] as $i=>$money)$db->insert('tr_jurnal',['id'=>$i+5,'no_transaksi'=>'LEGACY-APP','jenis_transaksi'=>'Payment','sts'=>'0','id_company'=>'7','nm_company'=>'STM','tgl_jurnal'=>'2026-10-15','coa'=>'5101','debit'=>$money[0],'kredit'=>$money[1],'keterangan'=>'Legacy {REF:DOC-LEGACY}']);
    $auto->insert('japh',['no_reff'=>'LEGACY-APP','nomor'=>'OLD-AUTOMATIC']);
    $db->insert('jurnaltras',['no_reff'=>'LEGACY-APP','no_request'=>'VOUCHER-2']);
    check((new Payment_reference($ci))->inspectForPosting('LEGACY-APP')['target_engines']['japh']==='MYISAM','real preview permits MyISAM target despite generic history');
    $response=(new Payment_posting($ci))->execute(5);
    check($response['save']===1,'real MyISAM writes succeed with native generator on locked target connection');
    check((int)$acc->get_where('jurnal',['no_reff'=>'VOUCHER-2'])->num_rows()===2&&(int)$acc->get_where('japh',['no_reff'=>'VOUCHER-2'])->num_rows()===1,'real MyISAM header and details agree');
    check((int)$acc->get('pastibisa_tb_cabang')->row()->nobuk===2,'real MyISAM counter incremented once');
    check($postedBefore===$db->where_in('id',[1,2])->get('tr_jurnal')->result_array(),'earlier posted source unchanged');
    check($targetBefore===$acc->get_where('jurnal',['no_reff'=>'BK--1026-0004'])->result_array(),'earlier target accounting unchanged');
    check((int)$auto->get_where('japh',['nomor'=>'OLD-AUTOMATIC'])->num_rows()===1&&(int)$db->get_where('jurnaltras',['no_request'=>'VOUCHER-2'])->num_rows()===1,'legacy automatic journals remain intact');
    try{(new Payment_posting($ci))->execute(5);throw new LogicException('Unexpected MyISAM duplicate');}catch(DomainException $e){check(true,'MyISAM duplicate refused');}
    $other->query('SET SESSION lock_wait_timeout=2');
    check($other->query('LOCK TABLES `'.$schemas[1].'`.jurnal WRITE')!==false,'real accounting locks released to other connection');$other->query('UNLOCK TABLES');
    echo 'PASS real MySQL Payment reference: '.$checks." assertions; random disposable schemas only.\n";
}finally{
    foreach([$path,$path.'.journal',$path.'.normalization.json',$path.'.normalization.json.journal',$path.'.normalization.json.backup.json'] as $file)if(is_file($file))unlink($file);
    foreach($schemas as $schema)if(strpos($schema,'codex_payment_ref_')===0)$admin->query("DROP DATABASE IF EXISTS `$schema`");
}
