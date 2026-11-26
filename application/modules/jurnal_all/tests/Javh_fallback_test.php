<?php
// Report accounting-number fallback: javh keterangan lookup is batched (one scan per 50 keys) and keeps the old per-key result.
define('BASEPATH',dirname(__DIR__,4).'/system/');define('APPPATH',dirname(__DIR__,3).'/');
function log_message($l,$m){}function show_error($m){throw new RuntimeException($m);}function load_class($n,$d){return null;}class BF_Model{}
require BASEPATH.'database/DB.php';require dirname(__DIR__).'/models/Jurnal_all_model.php';
$checks=0;function check($v,$m){global $checks;$checks++;if(!$v)throw new RuntimeException($m);}
$mk=function(){$d=DB(['dbdriver'=>'pdo','subdriver'=>'sqlite','dsn'=>'sqlite::memory:','db_debug'=>false],true);$d->conn_id=$d->conn_id?:true;
    $d->query('CREATE TABLE jurnal (nomor TEXT,tanggal TEXT,created_on TEXT,created_by TEXT,no_reff TEXT)');
    $d->query('CREATE TABLE japh (nomor TEXT,tgl TEXT,user_id TEXT,no_reff TEXT,jenis_ap TEXT)');
    $d->query('CREATE TABLE javh (nomor TEXT,tgl TEXT,user_id TEXT,keterangan TEXT)');return $d;};
$gl=$mk();
class Gl_loader{public $dbs=[];public function database($g,$r=true){return $this->dbs[$g];}}
$ci=(object)['load'=>new Gl_loader()];$ci->load->dbs=['accounting_stm'=>$gl,'accounting_vuca'=>$mk(),'accounting_sustain'=>$mk()];
function &get_instance(){global $ci;return $ci;}
// Matched through jurnal.no_reff; the javh header then supplies date and user.
$gl->insert('jurnal',['nomor'=>'J-1','tanggal'=>'2026-01-01','created_on'=>'2026-01-02','created_by'=>'u-jurnal','no_reff'=>'INV/1']);
$gl->insert('javh',['nomor'=>'J-1','tgl'=>'2026-01-03','user_id'=>'u-javh','keterangan'=>'Jurnal INV/1']);
// Unmatched keys resolved by keterangan. 'A_B' must not match 'AXB' (LIKE wildcard stays literal).
$gl->insert('javh',['nomor'=>'J-2','tgl'=>'2026-02-01','user_id'=>'u-2','keterangan'=>'Penjualan inv/2 PT X']);
$gl->insert('javh',['nomor'=>'J-3','tgl'=>'2026-02-02','user_id'=>'u-3','keterangan'=>'Dokumen AXB']);
$gl->insert('javh',['nomor'=>'J-4','tgl'=>'2026-02-03','user_id'=>'u-4','keterangan'=>'Dokumen A_B lunas']);
$gl->insert('javh',['nomor'=>'J-5','tgl'=>'2026-02-04','user_id'=>'u-5','keterangan'=>'Realisasi ER-2026-9']);
// Two javh rows mention the same key: the first one in table order wins, as LIMIT 1 did.
$gl->insert('javh',['nomor'=>'J-6','tgl'=>'2026-02-05','user_id'=>'u-6','keterangan'=>'Pertama DUP-1']);
$gl->insert('javh',['nomor'=>'J-7','tgl'=>'2026-02-06','user_id'=>'u-7','keterangan'=>'Kedua DUP-1']);
$bulk=[];for($i=1;$i<=120;$i++){$bulk[]='BULK/'.$i;$gl->insert('javh',['nomor'=>'JB-'.$i,'tgl'=>'2026-03-01','user_id'=>'ub','keterangan'=>'Massal BULK/'.$i.' selesai']);}
$inv=function($no){return ['source'=>'INV','id_company'=>'7','no_transaksi'=>'INTERNAL-'.$no,'invoice_number'=>$no,'source_numbers'=>''];};
$rows=[$inv('INV/1'),$inv('INV/2'),$inv('A_B'),$inv('NONE/9'),$inv('DUP-1'),['source'=>'EXP','id_company'=>'7','no_transaksi'=>'ER-2026-9','source_numbers'=>'']];
foreach($bulk as $b)$rows[]=$inv($b);
$model=new Jurnal_all_model();
$reader=new ReflectionMethod($model,'resolveGlMetadata');$reader->setAccessible(true);
$gl->queries=[];
$reader->invokeArgs($model,[&$rows]);
check($rows[0]['source_numbers']==='J-1'&&$rows[0]['posted_at']==='2026-01-03'&&$rows[0]['posted_by_id']==='u-javh','jurnal match keeps javh date and user');
check($rows[1]['source_numbers']==='J-2'&&$rows[1]['posted_at']==='2026-02-01','unmatched invoice resolved by javh keterangan, case-insensitive');
check($rows[2]['source_numbers']==='J-4','LIKE wildcard in the key stays literal');
check(empty($rows[3]['source_numbers']),'key without any javh row stays empty');
check($rows[4]['source_numbers']==='J-6','first javh row in table order wins');
check($rows[5]['source_numbers']==='J-5'&&$rows[5]['posted_by_id']==='u-5','expense fallback resolved');
foreach($bulk as $i=>$b)check($rows[6+$i]['source_numbers']==='JB-'.($i+1),'bulk key '.$b);
$likeScans=count(array_filter($gl->queries,function($q){return stripos($q,'FROM "javh"')!==false&&stripos($q,'LIKE')!==false||stripos($q,'FROM javh')!==false&&stripos($q,'LIKE')!==false;}));
// 5 invoice keys + 120 bulk = 125 invoice keys -> 3 batches; plus 1 expense batch. Previously one query per key (126).
check($likeScans>0&&$likeScans<=4,'javh keyword scans are batched, got '.$likeScans);
echo "PASS javh fallback batching: $checks assertions\n";
