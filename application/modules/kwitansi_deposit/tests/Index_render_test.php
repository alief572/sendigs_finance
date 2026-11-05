<?php
// Native controller/view regression for empty, filtered-empty and populated receipt lists.
define('BASEPATH',__DIR__);class Admin_Controller{}
require dirname(__DIR__,3).'/helpers/mockup_format_helper.php';
require dirname(__DIR__).'/controllers/Kwitansi_deposit.php';
set_error_handler(function($level,$message,$file,$line){throw new ErrorException($message,0,$level,$file,$line);});
function has_permission($permission){return true;}function base_url($p=''){return '/'.$p;}function site_url($path=''){return '/'.$path;}
function checkIndex($condition,$message){if(!$condition)throw new RuntimeException($message);}
class KwitansiIndexInput {public $values=array();function get($key){return $this->values[$key]??null;}}
class KwitansiIndexModel {
 public $rows=array(),$filters;function get_kwitansi_list($filters){$this->filters=$filters;return $this->rows;}function get_kwitansi_customer_list(){return array(array('id_customer'=>'C1','nm_customer'=>'Customer <unsafe>'));}
}
class KwitansiIndexTemplate {
 public $data=array(),$html;function set($name,$value){$this->data[$name]=$value;}function title($title){}function render($view){extract($this->data);ob_start();include dirname(__DIR__).'/views/'.$view.'.php';$this->html=ob_get_clean();}
}
$c=(new ReflectionClass('Kwitansi_deposit'))->newInstanceWithoutConstructor();$c->input=new KwitansiIndexInput;$c->Kwitansi_deposit_model=new KwitansiIndexModel;$c->template=new KwitansiIndexTemplate;
$c->index();$empty=$c->template->html;
checkIndex(strpos($empty,'<h1>')===false&&strpos($empty,'id="kw_box"')!==false,'Mockup heading and box');
$pending=array('id'=>'1','tanggal_kwitansi'=>'2026-10-30','no_kwitansi'=>'KD/2026/10/0001','id_customer'=>'C1','nm_customer'=>'Customer <unsafe>','po_customer'=>'SPK-1','nm_company'=>'STM','nilai_kwitansi'=>'100.00','saldo_piutang'=>'100.00','status'=>'MENUNGGU_PEMBAYARAN','no_penerimaan'=>null,'draft_split_id'=>'7');
$c->Kwitansi_deposit_model->rows=array($pending,array_merge($pending,array('id'=>'2','no_kwitansi'=>'KD/2026/10/0002','nilai_kwitansi'=>'200.00','saldo_piutang'=>'0.00','status'=>'LUNAS','no_penerimaan'=>'RECEIPT-2','tanggal_bayar'=>'2026-10-29')));
$c->index();$html=$c->template->html;
checkIndex(strpos($html,'KD\/2026\/10\/0001')!==false&&strpos($html,'RECEIPT-2')!==false&&strpos($html,'"draft":7')!==false,'Rows, payment ref and draft split are embedded for the client-side list');
checkIndex(strpos($html,'<unsafe>')===false&&strpos($html,'\u003Cunsafe\u003E')!==false,'Customer text is JSON-escaped');
foreach(array('Tidak ada kwitansi deposit yang cocok.','entries per page','Showing ','+ Buat Kwitansi Deposit','- Semua Customer -','- Semua Status -','Proses Penerimaan','>Jurnal<','>Detail<','Export to Excel','Dibayar lewat','Belum dibayar. Saat transfer masuk') as $needle)checkIndex(strpos($html,$needle)!==false,'Mockup text: '.$needle);
foreach(array('nginduk','kwitansi belum lunas','DataTable','Reset data demo') as $bad)checkIndex(strpos($html,$bad)===false,'Not copied / removed: '.$bad);
checkIndex(strpos($html,'kwitansi_deposit\/export_excel')!==false&&strpos($html,'ids=')!==false,'Export sends the visible ids');
echo "PASS: kwitansi index renders the mockup list shell with embedded data, texts, export by visible ids and no copied notes\n";
