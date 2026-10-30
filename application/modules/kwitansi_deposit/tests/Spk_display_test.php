<?php
define('BASEPATH',__DIR__);class BF_Model{}
require dirname(__DIR__).'/models/Kwitansi_deposit_model.php';
set_error_handler(function($level,$message,$file,$line){throw new ErrorException($message,0,$level,$file,$line);});
function site_url($path=''){return '/'.$path;}
function spkCheck($ok,$message){if(!$ok)throw new RuntimeException($message);}
class SpkDisplayDb {
 public $rows=array(),$searchColumns=array();
 function __call($method,$args){if($method==='or_like')$this->searchColumns[]=$args[0];return $this;}
 function get(){return $this;}function row_array(){return $this->rows[0]??array();}function result_array(){return $this->rows;}
}
$model=(new ReflectionClass('Kwitansi_deposit_model'))->newInstanceWithoutConstructor();$model->db=$db=new SpkDisplayDb;
$base=array('id'=>1,'no_kwitansi'=>'KD/2026/10/0001','tanggal_kwitansi'=>'2026-10-30','id_customer'=>'C1','nm_customer'=>'Customer','nm_company'=>'Vuca','nilai_kwitansi'=>'1500000.00','saldo_piutang'=>'1500000.00','status'=>'MENUNGGU_PEMBAYARAN','no_penerimaan'=>null,'po_customer'=>'','id_spk_penawaran'=>'090/STM/MKT-SPK/VII/25','spk_snapshot'=>null);
foreach(array(
 array(array(),'090/STM/MKT-SPK/VII/25'),
 array(array('spk_snapshot'=>json_encode(array('spk'=>array('no_spk'=>'SPK <snapshot>'))),'po_customer'=>'PO-DIFFERENT'),'SPK <snapshot>'),
 array(array('spk_snapshot'=>'bad json','po_customer'=>'PO-DIFFERENT'),'090/STM/MKT-SPK/VII/25'),
 array(array('id_spk_penawaran'=>null,'po_customer'=>'LEGACY-REF'),'LEGACY-REF'),
 array(array('id_spk_penawaran'=>null),'-')
) as $case){
 $db->rows=array(array_merge($base,$case[0]));$before=$db->rows;
 $list=$model->get_kwitansi_list(array('search'=>'090/STM'));$header=$model->get_kwitansi_by_id(1);
 spkCheck($list[0]['no_spk']===$case[1]&&$header['no_spk']===$case[1],'List/detail source agreement');
 spkCheck(in_array('k.id_spk_penawaran',$db->searchColumns,true),'Search includes stored SPK identity');
 spkCheck($db->rows===$before,'No stored data modified');
 foreach(array('index','detail','export_excel')as $view){
  $results=array('kwitansiList'=>$list,'header'=>$header,'journals'=>array(),'customers'=>array(),'filters'=>array('status'=>'','id_customer'=>'','search'=>''));$kwitansiList=$list;
  ob_start();include dirname(__DIR__).'/views/'.$view.'.php';$html=ob_get_clean();
  spkCheck(strpos($html,htmlspecialchars($case[1]))!==false,'SPK visible in '.$view);
  spkCheck(strpos($html,'<snapshot>')===false,'SPK escaped in '.$view);
  if($view==='export_excel'){
   $previous=libxml_use_internal_errors(true);$dom=new DOMDocument;$dom->loadHTML($html);libxml_clear_errors();libxml_use_internal_errors($previous);$x=new DOMXPath($dom);
   spkCheck($x->query('//thead/tr/th')->length===9 && $x->query('//tbody/tr[1]/td')->length===9,'Export uses the nine mockup columns including SPK and Company');
  }
 }
}
echo "PASS: SPK snapshot/identity/legacy sources, search, list/detail/export, escaping and nine-column export\n";
