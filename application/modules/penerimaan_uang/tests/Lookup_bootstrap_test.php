<?php
// Start with only the controller's declared dependencies, as an HTTP request does.
define('BASEPATH',__DIR__);class Admin_Controller{}
require dirname(__DIR__).'/controllers/Penerimaan_uang.php';
class LookupInput { public $values=array('id_customer'=>'C1','id_company'=>'7');function post($key){return $this->values[$key]??null;} }
class LookupAuth {function restrict($p){}}
class LookupResult {function result_array(){return array();}}
class LookupDb {function field_exists($field,$table){return true;}function query($sql,$args){if($args!==array('C1','7'))throw new RuntimeException('Unscoped lookup');return new LookupResult;}}
$c=(new ReflectionClass('Penerimaan_uang'))->newInstanceWithoutConstructor();$c->input=new LookupInput;$c->auth=new LookupAuth;$c->db=new LookupDb;
ob_start();$c->get_customer_deposit();$result=json_decode(ob_get_clean(),true);
if($result['status']!==1||$result['saldo']!=='0.00')throw new RuntimeException('Standalone lookup bootstrap failed: '.json_encode($result));
$c->input->values=array('id_customer'=>'C1');ob_start();$c->get_customer_deposit();$result=json_decode(ob_get_clean(),true);
if($result['status']!==0||$result['saldo']!==null)throw new RuntimeException('Missing company must not query combined balances');
echo "PASS: standalone controller dependencies and required company context\n";
