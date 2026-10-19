<?php
define('BASEPATH',dirname(__DIR__,4).'/system/'); define('APPPATH',dirname(__DIR__,3).'/');
class Admin_Controller {}
function log_message($level,$message) {}
function history($message) {}
require __DIR__.'/../controllers/Asset.php';
require APPPATH.'libraries/Asset_import_store.php';
function assert_controller($condition,$message) { if (!$condition) { throw new RuntimeException($message); } }
class ImportTestAuth {
    public $allowed=false; public $permissions=[];
    public function restrict($permission) { $this->permissions[]=$permission; if (!$this->allowed) { throw new Asset_import_failure('Permission ditolak'); } }
}
class ImportTestInput {
    public $method='POST'; public $data=[];
    public function method($upper=false) { return $this->method; }
    public function post($key=null) { return $key===null ? $this->data : ($this->data[$key] ?? null); }
}
class ImportTestSession {
    public $userdata=['app_session'=>['username'=>'tester','kdcab'=>'100']];
    public function userdata($key) { return $this->userdata[$key] ?? null; }
    public function set_userdata($key,$value) { $this->userdata[$key]=$value; }
}
class ImportTestOutput {
    public $data;
    public function set_content_type($type) { return $this; }
    public function set_output($json) { $this->data=json_decode($json,true); return $this; }
}
class ImportTestDB {
    public $inserted=[]; public $updated=[];
    public function initialize() { throw new RuntimeException('DB must not be reached for denied/invalid request'); }
    public function query($sql) { return new class { public function result_array() { return [['maxP'=>'AST-100202610-03-00501']]; } }; }
    public function insert_batch($table,$rows) { $this->inserted[$table]=array_values($rows); }
    public function where($key,$value) { return $this; }
    public function update($table,$values) { $this->updated[$table]=$values; }
    public function trans_start() {} public function trans_complete() {} public function trans_commit() {}
    public function trans_status() { return true; }
}
class ImportTestModel { public function getWhere($table,$key,$id) { return [['nm_category'=>'ALAT']]; } }
class ImportTestAsset extends Asset {
    public function __construct() {
        $this->auth=new ImportTestAuth(); $this->input=new ImportTestInput(); $this->output=new ImportTestOutput(); $this->session=new ImportTestSession(); $this->db=new ImportTestDB(); $this->Asset_model=new ImportTestModel();
    }
}
foreach (['download_import_template','preview_import','save_import'] as $method) {
    $controller=new ImportTestAsset(); $controller->$method();
    assert_controller($controller->auth->permissions===['Assets.Add'],'all import endpoints require Add');
    assert_controller($controller->output->data['status']===0,'permission denied');
}
$controller=new ImportTestAsset(); $controller->auth->allowed=true; $controller->input->method='GET'; $controller->save_import();
assert_controller(strpos($controller->output->data['pesan'],'Metode')!==false,'save POST only');
$controller->input->method='POST'; $controller->save_import();
assert_controller(strpos($controller->output->data['pesan'],'Session form')!==false,'missing nonce');
$controller->session->set_userdata('asset_import_nonce',str_repeat('a',64)); $controller->input->data=['nonce'=>str_repeat('b',64)]; $controller->preview_import();
assert_controller(strpos($controller->output->data['pesan'],'Session form')!==false,'wrong nonce');
// Execute the real existing saved() method with an in-memory DB adapter: no browser/no live writes.
$manual=new ImportTestAsset();
$manual->input->data=['nm_asset'=>'Laptop','category'=>3,'lokasi_asset'=>'','company_asset'=>1,'depresiasi'=>4,'nilai_asset'=>'3,000,000','value'=>'62,500','qty'=>2,'tanggal'=>'2026-10-12'];
ob_start(); $manual->saved(); $result=json_decode(ob_get_clean(),true);
assert_controller($result['status']===1,'manual saved regression');
$assets=$manual->db->inserted['asset']; $schedule=$manual->db->inserted['asset_generate'];
assert_controller(count($assets)===2 && count($schedule)===96,'manual qty/schedules');
assert_controller($assets[0]['kd_asset']==='AST-100202610-03-00601' && $assets[1]['kd_asset']==='AST-100202610-03-00602','manual code pattern');
assert_controller((int)$assets[0]['nilai_asset']===3000000 && (int)$assets[0]['value']===62500 && $assets[0]['lokasi_asset']==='' && $assets[0]['cost_center']==='','manual per-unit fields');
assert_controller($schedule[0]['bulan']==='10' && $schedule[0]['tahun']==='2026','manual acquisition schedule');
// Editing without the removed selector must not erase a historical Cost Center.
$manual->input->data=['helpa'=>'N','id'=>1,'lokasi_asset'=>'Finance','company_asset'=>1];
ob_start(); $manual->edit(); $edited=json_decode(ob_get_clean(),true);
assert_controller($edited['status']===1 && !array_key_exists('cost_center',$manual->db->updated['asset']),'edit preserves historical Cost Center');
echo "Controller permission/method/nonce and real manual saved() regression OK\n";
