<?php
// Report "No. Jurnal Accounting" fallback (japh lookup) for Payment/Refill whose journal key is a long approval-ID list.
define('BASEPATH',dirname(__DIR__,4).'/system/');define('APPPATH',dirname(__DIR__,3).'/');
function log_message($l,$m){}function show_error($m){throw new RuntimeException($m);}function load_class($n,$d){return null;}class BF_Model{}
require BASEPATH.'database/DB.php';require dirname(__DIR__).'/models/Jurnal_all_model.php';
$checks=0;function check($v,$m){global $checks;$checks++;if(!$v)throw new RuntimeException($m);}
$gl=DB(['dbdriver'=>'pdo','subdriver'=>'sqlite','dsn'=>'sqlite::memory:','db_debug'=>false],true);
$gl->conn_id=$gl->conn_id?:true;
$gl->query('CREATE TABLE japh (nomor TEXT,tgl TEXT,user_id TEXT,no_reff TEXT,jenis_ap TEXT)');
$gl->query('CREATE TABLE jurnal (nomor TEXT,tanggal TEXT,created_on TEXT,created_by TEXT,no_reff TEXT)');
$long='BK--1026-0040,BK--1026-0006,BK--1026-0008,BK--1026-0039,BK--1026-0041,BK--1026-0042';
$cut=substr($long,0,25);
// Same shape as db_sendigs_ss_stm: Payment (V) and Refill (BKA) share the cut-off no_reff; single Refill uses its RPC number.
foreach([['101BK2600013','2026-10-05','finance',$cut,'V'],['101BKA2600008','2026-10-05','203',$cut,''],['101BKA2600007','2026-10-15','203','RPC-2026-0033',''],['101BK2600011','2026-09-05','finance','BK--0926-0011','V']] as $r)$gl->insert('japh',array_combine(['nomor','tgl','user_id','no_reff','jenis_ap'],$r));
class Gl_loader{public $dbs=[];public function database($g,$r=true){return $this->dbs[$g];}}
// other ledgers: empty japh/jurnal tables; STM (target of company 7) is $gl
$other=function(){$d=DB(['dbdriver'=>'pdo','subdriver'=>'sqlite','dsn'=>'sqlite::memory:','db_debug'=>false],true);$d->conn_id=$d->conn_id?:true;$d->query('CREATE TABLE japh (nomor TEXT,tgl TEXT,user_id TEXT,no_reff TEXT,jenis_ap TEXT)');$d->query('CREATE TABLE jurnal (nomor TEXT,tanggal TEXT,created_on TEXT,created_by TEXT,no_reff TEXT)');return $d;};
$ci=(object)['load'=>new Gl_loader()];$ci->load->dbs=['accounting_stm'=>$gl,'accounting_vuca'=>$other(),'accounting_sustain'=>$other()];
function &get_instance(){global $ci;return $ci;}
$model=new Jurnal_all_model();
$reader=new ReflectionMethod($model,'resolveGlMetadata');$reader->setAccessible(true);
$row=function($type,$no,$ref='')use($long){return ['source'=>$type==='Payment'?'PAY':'PCS','jenis_transaksi'=>$type,'no_transaksi'=>$no,'no_referensi'=>$ref,'id_company'=>'7','source_numbers'=>null,'posted_at'=>null,'posted_by_id'=>null,'posted_by'=>null];};
$rows=[$row('Payment',$long),$row('Refill Pettycash',$long,'RPC-2026-0029'),$row('Refill Pettycash','BK--1026-0074','RPC-2026-0033'),$row('Payment','BK--0926-0011')];
$reader->invokeArgs($model,[&$rows]);
check($rows[0]['source_numbers']==='101BK2600013','multi-ID Payment resolves to its Payment header, not the Refill one');
check($rows[1]['source_numbers']==='101BKA2600008','multi-ID Refill resolves to its Refill header, not the Payment one');
check($rows[2]['source_numbers']==='101BKA2600007','single Refill resolves through its RPC document number');
check($rows[3]['source_numbers']==='101BK2600011','plain voucher key still matches directly');
check($rows[0]['posted_at']==='2026-10-05'&&$rows[1]['posted_by_id']==='203','posted metadata follows the matched header');
// Only a Refill header exists for the cut-off key: Payment must stay empty rather than borrow it.
$gl->query("DELETE FROM japh WHERE nomor='101BK2600013'");
$m2=new Jurnal_all_model();
$rows=[$row('Payment',$long),$row('Refill Pettycash',$long,'RPC-2026-0029')];
$reader->invokeArgs($m2,[&$rows]);
check(empty($rows[0]['source_numbers'])&&$rows[1]['source_numbers']==='101BKA2600008','Payment never borrows the Refill number');
// History posted under older routing: company 1 (Vuca by today's rule) whose japh header lives in the STM ledger.
$gl->insert('japh',['nomor'=>'101BK2600001','tgl'=>'2026-01-24','user_id'=>'finance','no_reff'=>'BK--0726-0040','jenis_ap'=>'V']);
$m3=new Jurnal_all_model();
$old=$row('Payment','BK--0726-0040');$old['id_company']='1';
$none=$row('Payment','BK--NO-HEADER-ANYWHERE');$none['id_company']='1';
$rows=[$old,$none];$reader->invokeArgs($m3,[&$rows]);
check($rows[0]['source_numbers']==='101BK2600001','Payment posted under older routing is found in the other ledger');
check(empty($rows[1]['source_numbers']),'row with no header in any ledger stays empty');
// After the key repair Payment and Refill share ONE full voucher (BK--1026-0002), no longer a cut-off id list: the two japh headers share that no_reff.
$gl->query("DELETE FROM japh");
foreach([['101BK2600013','2026-10-05','finance','BK--1026-0002','V'],['101BKA2600008','2026-10-05','203','BK--1026-0002',''],['101BK2600021','2026-10-06','finance','BK--1026-0009','V']] as $r)$gl->insert('japh',array_combine(['nomor','tgl','user_id','no_reff','jenis_ap'],$r));
$m4=new Jurnal_all_model();
$rows=[$row('Payment','BK--1026-0002'),$row('Refill Pettycash','BK--1026-0002','RPC-2026-0029'),$row('Payment','BK--1026-0009')];
$reader->invokeArgs($m4,[&$rows]);
check($rows[0]['source_numbers']==='101BK2600013','shared full voucher: Payment takes the jenis_ap V header');
check($rows[1]['source_numbers']==='101BKA2600008','shared full voucher: Refill takes the other header, not the Payment one');
check($rows[2]['source_numbers']==='101BK2600021','a voucher with a single header is unchanged by the kind filter');
// Shared voucher but only a Payment header exists: the Refill row must stay empty rather than borrow it.
$gl->query("DELETE FROM japh WHERE nomor='101BKA2600008'");
$m5=new Jurnal_all_model();
$rows=[$row('Payment','BK--1026-0002'),$row('Refill Pettycash','BK--1026-0002','RPC-2026-0029')];
$reader->invokeArgs($m5,[&$rows]);
check($rows[0]['source_numbers']==='101BK2600013'&&empty($rows[1]['source_numbers']),'shared voucher with one header: Refill does not borrow the Payment number');
echo "PASS report accounting number: $checks assertions\n";
