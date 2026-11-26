<?php
// Exercise the native index/detail controller and views for empty and populated datasets.
define('BASEPATH',__DIR__);class Admin_Controller{}
require dirname(__DIR__).'/controllers/Deposit_customer.php';
require dirname(__DIR__).'/models/Deposit_customer_model.php';
class BF_Model{}
require dirname(__DIR__,3).'/helpers/mockup_format_helper.php';
require dirname(__DIR__,2).'/penerimaan_uang/libraries/Penerimaan_calculation.php';
set_error_handler(function($level,$message,$file,$line){throw new ErrorException($message,0,$level,$file,$line);});
function has_permission($p){return true;}function base_url($p=''){return '/'.$p;}
function assertIndex($ok,$message){if(!$ok)throw new RuntimeException($message);}
class IndexModel {
 public $customers=array(),$ledger=array();function get_kpi_summary(){return array();}function get_customer_summary(){return $this->customers;}function get_ledger_rows($id=null){return $this->ledger;}function get_customer_name($id){return 'Customer <unsafe>';}
}
class IndexTemplate {
 public $data=array(),$html;function set($name,$value){$this->data[$name]=$value;}function title($title){}function render($view){extract($this->data);ob_start();include dirname(__DIR__).'/views/'.$view.'.php';$this->html=ob_get_clean();}
}
$c=(new ReflectionClass('Deposit_customer'))->newInstanceWithoutConstructor();$c->Deposit_customer_model=new IndexModel;$c->template=new IndexTemplate;
$c->index();$empty=$c->template->html;
$fixture=array('id_customer'=>'C1','nm_customer'=>'Customer <unsafe>','jumlah_deposit'=>1,'sisa_saldo'=>'10.00','total_nominal_awal'=>'15.00','total_nominal_terpakai'=>'5.00','total_dari_kwitansi'=>'15.00','total_dari_lebih_bayar'=>'0.00','last_deposit_date'=>'2026-10-30');
$c->Deposit_customer_model->customers=array($fixture,array_merge($fixture,array('id_customer'=>'C2','nm_customer'=>'Customer Dua','sisa_saldo'=>'0.00')));$c->index();$populated=$c->template->html;
if(($argv[1]??'')==='--json'){echo json_encode(array('empty'=>$empty,'populated'=>$populated));exit;}
foreach(array('empty'=>$empty,'populated'=>$populated)as $mode=>$html){
 $previous=libxml_use_internal_errors(true);$d=new DOMDocument;$d->loadHTML($html);libxml_clear_errors();libxml_use_internal_errors($previous);$x=new DOMXPath($d);
 $header=$x->query('//table[@id="table_deposit_summary"]/thead/tr/th')->length;assertIndex($header===10,'Expected ten monitoring columns');
 $rows=$x->query('//table[@id="table_deposit_summary"]/tbody/tr[contains(@class,"md-row")]');assertIndex($rows->length===($mode==='empty'?0:2),'Customer rows only (total/empty rows are separate)');
 foreach($rows as $row){assertIndex($x->query('./td',$row)->length===$header,'Body cells must match header');}
 assertIndex($x->query('//tr[@id="md_total_row"]')->length===1&&$x->query('//tr[@id="md_empty_row"]')->length===1,'Total and empty rows exist (mockup)');
 assertIndex($x->query('//unsafe')->length===0,'Customer text remains escaped');
 assertIndex(strpos($html,'Company')===false&&strpos($html,'id_company')===false,'Monitoring has no company dimension');
 assertIndex(strpos($html,'filter_status_saldo')===false,'No voucher/status filter');
}
assertIndex(strpos($empty,'Customer tidak ditemukan.')!==false,'Mockup empty-state message');
assertIndex(strpos($populated,'deposit_customer/detail?id_customer=C1')!==false&&strpos($populated,'deposit_customer/export_excel?id_customer=C2')!==false,'Customer-scoped links');
assertIndex(strpos($populated,'hanya untuk memantau')===false,'Mockup-only explanation note is not copied');
assertIndex(strpos($populated,'30-October-2026')!==false,'Dates use full month name (mockup fdate)');

// Detail: three sources, running balance and summary, no voucher section.
$c->Deposit_customer_model->ledger=array(
 array('id'=>1,'id_customer'=>'C1','nm_customer'=>'Customer <unsafe>','penerimaan_id'=>11,'no_transaksi'=>'KD/2026/10/0001','tanggal'=>'2026-10-01','jenis_mutasi'=>'IN','nominal'=>'1000.00','keterangan'=>'Kwitansi','tipe_sumber'=>'KWITANSI_DEPOSIT'),
 array('id'=>2,'id_customer'=>'C1','nm_customer'=>'Customer <unsafe>','no_transaksi'=>'PP-2','tanggal'=>'2026-10-02','jenis_mutasi'=>'IN','nominal'=>'500.00','keterangan'=>'Lebih','tipe_sumber'=>'LEBIH_BAYAR'),
 array('id'=>3,'id_customer'=>'C1','nm_customer'=>'Customer <unsafe>','no_transaksi'=>'PP-3','tanggal'=>'2026-10-03','jenis_mutasi'=>'OUT','nominal'=>'300.00','keterangan'=>'Dipakai','tipe_sumber'=>'KWITANSI_DEPOSIT'));
$c->detail('C1');$detail=$c->template->html;
foreach(array('Penerimaan Kwitansi Deposit','Lebih Bayar (Penerimaan Invoice)','Deposit Dipakai (Penerimaan Invoice)','Rp 1.200,00')as $needle){
 assertIndex(strpos($detail,$needle)!==false,'Detail content: '.$needle);
}
assertIndex(strpos($detail,'Daftar Voucher')===false,'Detail has no voucher section');
assertIndex(preg_match('~<a href="[^"]*penerimaan_uang/view/11">~',$detail)===1,'No Penerimaan links to the receipt page (mockup refLink)');
assertIndex(strpos($detail,'&lt;unsafe&gt;')!==false&&strpos($detail,'<unsafe>')===false,'Detail customer text escaped');
$px=new DOMDocument;libxml_use_internal_errors(true);$px->loadHTML($detail);libxml_clear_errors();$xp=new DOMXPath($px);
assertIndex($xp->query('//table/thead/tr/th')->length===8,'Detail ledger has eight columns');
assertIndex($xp->query('//table/tbody/tr')->length===3,'Detail has three mutations');
echo "PASS: native monitoring index/detail render, ten-column contract, safe text, no company/voucher UI, three ledger sources\n";
