<?php
// Native controller/view regression for empty, filtered-empty and populated receipt lists.
define('BASEPATH',__DIR__);class Admin_Controller{}
require dirname(__DIR__).'/controllers/Kwitansi_deposit.php';
set_error_handler(function($level,$message,$file,$line){throw new ErrorException($message,0,$level,$file,$line);});
function has_permission($permission){return true;}function site_url($path=''){return '/'.$path;}
function checkIndex($condition,$message){if(!$condition)throw new RuntimeException($message);}
class KwitansiIndexInput {public $values=array();function get($key){return $this->values[$key]??null;}}
class KwitansiIndexModel {
 public $rows=array(),$filters;function get_kwitansi_list($filters){$this->filters=$filters;return $this->rows;}function get_kwitansi_customer_list(){return array(array('id_customer'=>'C1','nm_customer'=>'Customer <unsafe>'));}
}
class KwitansiIndexTemplate {
 public $data=array(),$html;function set($name,$value){$this->data[$name]=$value;}function title($title){}function render($view){extract($this->data);ob_start();include dirname(__DIR__).'/views/'.$view.'.php';$this->html=ob_get_clean();}
}
$c=(new ReflectionClass('Kwitansi_deposit'))->newInstanceWithoutConstructor();$c->input=new KwitansiIndexInput;$c->Kwitansi_deposit_model=new KwitansiIndexModel;$c->template=new KwitansiIndexTemplate;
$c->index();$rendered=array('empty'=>$c->template->html);
$c->input->values=array('id_customer'=>'C1','status'=>'LUNAS','search'=>'Not found');$c->index();$rendered['filtered_empty']=$c->template->html;
checkIndex($c->Kwitansi_deposit_model->filters['id_customer']==='C1'&&$c->Kwitansi_deposit_model->filters['status']==='LUNAS','GET filters must reach the model');
checkIndex(strpos($rendered['filtered_empty'],'id_customer=C1')!==false,'Export link preserves customer filter');
$c->input->values=array();
$pending=array('id'=>'1','tanggal_kwitansi'=>'2026-10-30','no_kwitansi'=>'KD/2026/10/0001','nm_customer'=>'Customer <unsafe>','po_customer'=>'SPK-1','nm_company'=>'STM','nilai_kwitansi'=>'100.00','saldo_piutang'=>'100.00','status'=>'MENUNGGU_PEMBAYARAN','no_penerimaan'=>null,'draft_split_id'=>'7');
$c->Kwitansi_deposit_model->rows=array($pending,array_merge($pending,array('id'=>'2','no_kwitansi'=>'KD/2026/10/0002','nilai_kwitansi'=>'200.00','saldo_piutang'=>'0.00','status'=>'LUNAS','no_penerimaan'=>'RECEIPT-2','tanggal_bayar'=>'2026-10-29')));
$c->index();$rendered['populated']=$c->template->html;
checkIndex($c->template->data['results']['summary']['total_menunggu']===100.0&&$c->template->data['results']['summary']['total_lunas']===200.0,'Kwitansi summary preserved');
if(($argv[1]??'')==='--json'){echo json_encode($rendered);exit;}
foreach($rendered as $mode=>$html){
 $previous=libxml_use_internal_errors(true);$doc=new DOMDocument;$doc->loadHTML($html);libxml_clear_errors();libxml_use_internal_errors($previous);$x=new DOMXPath($doc);
 $headers=$x->query('//table[@id="table_kwitansi"]/thead/tr/th')->length;checkIndex($headers===10,'Expected ten kwitansi columns');
 $rows=$x->query('//table[@id="table_kwitansi"]/tbody/tr');checkIndex($rows->length===($mode==='populated'?2:0),'Empty state must not create a synthetic data row');
 foreach($rows as $row){checkIndex($x->query('./td',$row)->length===$headers,'Each receipt supplies ten data cells');checkIndex($x->query('./td[@colspan or @rowspan]',$row)->length===0,'No merged body cells allowed');}
 checkIndex($x->query('//unsafe')->length===0,'Customer text remains escaped');
 checkIndex(strpos($html,'emptyTable: "Belum ada tagihan kwitansi deposit."')!==false,'DataTables owns empty-table messaging');
}
checkIndex(substr_count($rendered['populated'],'Proses Penerimaan')===1,'Receipt action is shown only for pending receipt');
checkIndex(substr_count($rendered['populated'],'class="kwitansi-actions"')===2,'Each populated row groups its actions');
checkIndex(substr_count($rendered['populated'],'class="btn btn-sm btn-info"')===2,'Each detail action uses the consistent compact style');
checkIndex(strpos($rendered['populated'],'fa fa-bank')!==false,'Bank action has a distinct icon');
checkIndex(strpos($rendered['populated'],'/penerimaan_uang/add_penerimaan_uang/7"')!==false,'Receipt action opens the linked draft');
$c->Kwitansi_deposit_model->rows=array(array_merge($pending,array('draft_split_id'=>null)));$c->index();
checkIndex(strpos($c->template->html,'Proses Penerimaan')===false,'Pending receipt without linked draft has no receipt action');
checkIndex(strpos($rendered['populated'],'· 29-Oct-2026')!==false,'Paid receipt shows payment date next to receipt number');
checkIndex(substr_count($rendered['populated'],'· 29-Oct-2026')===1,'Pending receipt shows no payment date');
checkIndex(strpos($rendered['populated'],'Reset')!==false&&strpos($rendered['populated'],'href="/kwitansi_deposit"')!==false,'Reset clears all filters');
checkIndex(strpos($rendered['populated'],'<strong>1</strong> kwitansi belum lunas')!==false&&strpos($rendered['populated'],'/alokasi')!==false,'Flow note shows open count and links');
checkIndex($c->template->data['results']['summary']['count_menunggu']===1,'Open receipt count in summary');
echo "PASS: native kwitansi index empty/filtered-empty/populated render, column contract, summaries, escaped text and actions\n";
