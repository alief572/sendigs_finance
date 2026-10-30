<?php
define('BASEPATH',dirname(__DIR__,4).'/system/');class Admin_Controller{}
require dirname(__DIR__).'/controllers/Penerimaan_uang.php';require dirname(__DIR__,3).'/libraries/Template.php';
set_error_handler(function($level,$message,$file,$line){throw new ErrorException($message,0,$level,$file,$line);});
function base_url($path=''){return '/'.$path;}
class ReceiptAddResult {private $rows;function __construct($rows){$this->rows=$rows;}function row_array(){return $this->rows[0]??array();}function result_array(){return $this->rows;}}
class ReceiptAddDb {
 public $kind=1;private $table;
 function select($s){return $this;}function from($s){$this->table=$s;return $this;}function join($t,$on){return $this;}function where($key,$value=null,$escape=null){return $this;}function group_by($s){return $this;}function order_by($s,$dir){return $this;}
 function get(){return new ReceiptAddResult($this->table==='tr_alokasi_split s'?array(array('id'=>2269,'jenis_alokasi'=>$this->kind,'nominal'=>'200.00','nilai_terpakai'=>'50.00')):array(array('id_customer'=>'C1','nm_customer'=>'Customer test')));}
}
class ReceiptAddAuth {function restrict($permission){if($permission!=='Penerimaan_Uang.View')throw new RuntimeException('Unexpected permission');}}
class ReceiptAddTemplate extends Template {
 public $html,$viewData;function __construct(){}function title($title=null){return $this;}
 function render($view,$data=array(),$return=false){$property=new ReflectionProperty('Template','_data');$property->setAccessible(true);$this->viewData=$property->getValue($this);extract($this->viewData);ob_start();try{include dirname(__DIR__).'/views/'.$view.'.php';$this->html=ob_get_contents();}finally{ob_end_clean();}}
}
$c=(new ReflectionClass('Penerimaan_uang'))->newInstanceWithoutConstructor();$c->db=new ReceiptAddDb;$c->auth=new ReceiptAddAuth;
foreach(array(1=>'invoice',8=>'deposit')as $allocationKind=>$receiptKind){
 $c->db->kind=$allocationKind;$c->template=new ReceiptAddTemplate;$c->add_penerimaan_uang(2269);
 $data=$c->template->viewData['results']??null;
 if(!$data||$data['receipt_kind']!==$receiptKind||$data['id_alokasi']!==2269||$data['nominal_penerimaan_bank']!=150)throw new RuntimeException('Add view payload contract mismatch');
 if(strpos($c->template->html,'value="C1"')===false||strpos($c->template->html,'name="receipt_kind" value="'.$receiptKind.'"')===false)throw new RuntimeException('Form lost customer or receipt kind');
}
echo "PASS: native Template::set contract and rendered receipt add for Invoice and Kwitansi Deposit\n";
