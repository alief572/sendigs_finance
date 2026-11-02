<?php
define('BASEPATH',__DIR__);class BF_Model{}
require dirname(__DIR__,2).'/../helpers/mockup_format_helper.php';
require dirname(__DIR__).'/models/Kwitansi_deposit_model.php';
set_error_handler(function($level,$message,$file,$line){throw new ErrorException($message,0,$level,$file,$line);});
function base_url($p=''){return '/'.$p;}function site_url($path=''){return '/'.$path;}
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
 foreach(array('index','detail')as $view){
  $results=array('kwitansiList'=>$list,'header'=>$header,'journals'=>array(),'customers'=>array(),'filters'=>array('status'=>'','id_customer'=>'','search'=>''));$kwitansiList=$list;
  ob_start();include dirname(__DIR__).'/views/'.$view.'.php';$html=ob_get_clean();
  $needle=$view==='index'?trim(json_encode($case[1],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT),'"'):htmlspecialchars($case[1]);spkCheck(strpos($html,$needle)!==false,'SPK visible in '.$view);
  spkCheck(strpos($html,'<snapshot>')===false,'SPK escaped in '.$view);
 }
 require_once dirname(__DIR__,3).'/libraries/Xlsx_export.php';$spec=Xlsx_export::kwitansi_spec($list);
 spkCheck(count($spec['head'])===9 && count($spec['rows'][0])===9 && $spec['rows'][0][4]===$case[1],'Export uses the nine mockup columns with SPK');
}
echo "PASS: SPK snapshot/identity/legacy sources, search, list/detail/export, escaping and nine-column export\n";
