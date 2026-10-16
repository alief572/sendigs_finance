<?php
define('BASEPATH',dirname(__DIR__,4).'/system/');define('APPPATH',dirname(__DIR__,3).'/');
function log_message($l,$m){}function show_error($m){throw new RuntimeException($m);}function load_class($n,$d){return null;}class BF_Model{}
require BASEPATH.'database/DB.php';require dirname(__DIR__).'/models/Jurnal_all_model.php';
$checks=0;function check($v,$m){global $checks;$checks++;if(!$v)throw new RuntimeException($m);}
$db=DB(['dbdriver'=>'pdo','subdriver'=>'sqlite','dsn'=>'sqlite::memory:','db_debug'=>false],true);
$model=new Jurnal_all_model();$model->db=$db;
check(!$model->posting_metadata_ready(),'migration missing preflight');
$db->query('CREATE TABLE tr_jurnal_postings (group_key TEXT PRIMARY KEY,source TEXT,no_transaksi TEXT,jenis_transaksi TEXT,id_company TEXT,posted_at TEXT,posted_by TEXT,posted_by_name TEXT,accounting_numbers TEXT)');
$db->data_cache=[];
$model=new Jurnal_all_model();$model->db=$db;check($model->posting_metadata_ready(),'metadata schema ready');
$header=['source'=>'INV','no_transaksi'=>'INV-1','jenis_transaksi'=>'Invoicing','id_company'=>'7'];$actor=['id'=>7,'name'=>'Finance'];
$model->save_posting_metadata($header,$actor,[['company'=>'7','number'=>'FINAL-JV-1']]);
$stored=$db->get('tr_jurnal_postings')->row_array();
check($stored['posted_by']==='7'&&$stored['posted_by_name']==='Finance','authenticated ID and username snapshot stored');
$now=(new DateTimeImmutable('now',new DateTimeZone('Asia/Jakarta')))->getTimestamp();
check(abs($now-(new DateTimeImmutable($stored['posted_at'],new DateTimeZone('Asia/Jakarta')))->getTimestamp())<10,'timestamp recorded in Jakarta after handler completion');
check(json_decode($stored['accounting_numbers'],true)===[['company'=>'7','number'=>'FINAL-JV-1']],'actual final number stored without staging substitute');
$reader=new ReflectionMethod($model,'postingMetadata');$reader->setAccessible(true);
$rows=[array_merge($header,['companies'=>[['id'=>'7','name'=>'STM']],'posted_at'=>null,'posted_by'=>null,'source_numbers'=>null]),['source'=>'INV','no_transaksi'=>'OLD','jenis_transaksi'=>'Invoicing','id_company'=>'7','companies'=>[],'posted_at'=>null,'posted_by'=>null,'source_numbers'=>null]];
$reader->invokeArgs($model,[&$rows]);
check($rows[0]['source_numbers']==='FINAL-JV-1'&&$rows[0]['posted_by']==='Finance','report enrichment reads confirmed accounting number and actor');
check($rows[1]['posted_at']===null&&$rows[1]['source_numbers']===null,'historic row without metadata stays unavailable');
foreach(['4','7'] as $company)$model->save_posting_metadata(['source'=>'EXP','no_transaksi'=>'SAME','jenis_transaksi'=>'Expense Report','id_company'=>$company],$actor,[['company'=>$company,'number'=>'JV-'.$company]]);
check($db->count_all('tr_jurnal_postings')===3,'expense companies are distinct native groups');
$pcs=['source'=>'PCS','no_transaksi'=>'PCP-1','jenis_transaksi'=>'Petty Cash','id_company'=>'4'];
$model->save_posting_metadata($pcs,$actor,[['company'=>'VUCA','number'=>'BUK-1'],['company'=>'STM','number'=>'BUK-1']]);
$rows=[array_merge($pcs,['companies'=>[]])];$reader->invokeArgs($model,[&$rows]);
check($rows[0]['source_numbers']==='VUCA: BUK-1; STM: BUK-1','equal numbers in separate ledgers retain target company identity');
try{$model->save_posting_metadata($header,$actor,[['company'=>'7','number'=>'OVERWRITE']]);check(false,'duplicate');}catch(Throwable $e){check($db->count_all('tr_jurnal_postings')===4,'existing posting metadata is immutable');}
foreach([[],[['company'=>'7','number'=>'']]] as $invalid){try{$model->save_posting_metadata($header,$actor,$invalid);check(false,'invalid number');}catch(RuntimeException $e){check(true,'unconfirmed number rejected');}}
class FailingMetadataDb {
 public $db_debug=true,$base,$fail;public function __construct($base,$fail){$this->base=$base;$this->fail=$fail;}
 public function __call($method,$args){if(($this->fail==='begin'&&$method==='trans_begin')||($this->fail==='write'&&$method==='insert')||($this->fail==='status'&&$method==='trans_status')||($this->fail==='commit'&&$method==='trans_commit'))return false;return $this->base->$method(...$args);}
}
foreach(['begin','write','status','commit'] as $failure){
 $wrapper=new FailingMetadataDb($db,$failure);$model=new Jurnal_all_model();$model->db=$wrapper;
 try{$model->save_posting_metadata(array_merge($header,['no_transaksi'=>'FAIL-'.$failure]),$actor,[['company'=>'7','number'=>'JV-FAIL']]);check(false,$failure);}catch(RuntimeException $e){check($db->count_all('tr_jurnal_postings')===4,'metadata failure leaves no row '.$failure);}
 check($wrapper->db_debug===true,'db debug restored '.$failure);
}
echo "PASS posting metadata storage: {$checks} assertions\n";
