<?php
if (PHP_SAPI !== 'cli') exit;
mysqli_report(MYSQLI_REPORT_OFF);
define('BASEPATH', dirname(__DIR__, 4) . '/system/');
define('APPPATH', dirname(__DIR__, 3) . '/');
function log_message($level, $message) { if ($level==='error') fwrite(STDERR,$message."\n"); }
function is_php($version) { return version_compare(PHP_VERSION, $version, '>='); }
function show_error($message) { throw new RuntimeException($message); }
require BASEPATH . 'core/Model.php';
require APPPATH . 'core/BF_Model.php';
class Admin_Controller {}
class TestAuth { function user_id() { return 42; } }
class TestInput { public $values=[]; function post($key=null) { return $key===null ? $this->values : ($this->values[$key] ?? null); } }
class TestSession { function userdata($key=null) { return ['id_user'=>42]; } }
class TestUri { public $code; function segment($n) { return $n===3 ? $this->code : 'incoming_stok'; } }
class TestLoader {
    public $owner;
    function __construct($owner) { $this->owner=$owner; }
    function model($path) { $this->owner->incoming_cash_model=receiptModel($this->owner->db); }
    function view($name,$data,$return=false) {
        $render=function() use ($name,$data) { extract($data); include dirname(__DIR__).'/views/'.$name.'.php'; };
        ob_start(); $render->call($this->owner); $output=ob_get_clean();
        if ($return) return $output;
        echo $output;
    }
}
function base_url($path='') { return 'http://localhost:8080/'.$path; }
function tgl_indo($date) { return $date; }
function history($message) {}
function int_to_roman($month) { return 'X'; }
function capture($object,$method) { ob_start(); $object->$method(); return ob_get_clean(); }
function &get_instance() { return $GLOBALS['ci']; }
function get_name($table, $field, $where, $value) {
    $row = get_instance()->db->get_where($table, [$where=>$value])->row_array();
    return $row[$field] ?? '';
}
require APPPATH . 'helpers/json_helper.php';
function payload($qty, $doc = 'CASH1', $detail = 11) {
    return ['supplier'=>'cash','no_po'=>[$doc],'id_gudang'=>17,'pic'=>'Receiver',
        'tanggal'=>'2026-10-09','keterangan'=>'Test Cash',
        'Detail'=>[['id_cash'=>$doc,'id'=>$detail,'qty_in'=>$qty,'ket'=>'Test',
            'id_barang'=>'FORGED','qty_po'=>99999,'price_ref'=>0,'nm_barang'=>'FORGED']],
        'jurnal'=>[['debit'=>99999,'credit'=>99999]]];
}
function receiptModel($db) {
    $model = (new ReflectionClass('Incoming_cash_model'))->newInstanceWithoutConstructor();
    $model->db = $db;
    $GLOBALS['ci'] = (object)['db'=>$db, 'auth'=>new TestAuth];
    return $model;
}
function check($condition, $message) {
    if (!$condition) throw new RuntimeException($message);
    echo "PASS: $message\n";
}
require BASEPATH . 'database/DB.php';
require dirname(__DIR__).'/models/Incoming_cash_model.php';
require dirname(__DIR__).'/models/Incoming_stok_model.php';
require dirname(__DIR__).'/controllers/Incoming_stok.php';
$cfg = ['dbdriver'=>'mysqli','hostname'=>'127.0.0.1','port'=>3307,'username'=>'root',
    'password'=>getenv('INCOMING_TEST_PASSWORD') ?: 'sendigs_root_local','database'=>'',
    'db_debug'=>false,'char_set'=>'utf8mb4','dbcollat'=>'utf8mb4_unicode_ci'];
$db = DB($cfg, true);
if (($argv[1] ?? '') === '--worker') {
    $schema = $argv[2] ?? '';
    if (!preg_match('/^codex_incoming_test_[a-f0-9]+$/D', $schema)) exit(2);
    $db->db_select($schema);
    echo json_encode(receiptModel($db)->receive(payload(6),42));
    exit;
}
$schema = 'codex_incoming_test_' . bin2hex(random_bytes(5));
$db->query("CREATE DATABASE `$schema` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$db->db_select($schema);
try {
    $fixture=preg_replace('/^\s*--.*$/m','',file_get_contents(__DIR__.'/fixture_schema.sql').file_get_contents(__DIR__.'/legacy_fixture.sql'));
    foreach (explode(';', $fixture) as $sql) {
        if (trim($sql)) check($db->query($sql) !== false, 'Create fixture table');
    }
    $db->insert('warehouse', ['id'=>17,'desc'=>'stok','kd_gudang'=>'STOK','nm_gudang'=>'Stok']);
    check($db->insert('accessories', ['id'=>71,'id_stock'=>'ST-1','stock_name'=>'Item One','konversi'=>1]), 'Seed stock');
    check($db->insert('material_planning_base_on_produksi', ['id'=>1,'so_number'=>'PR1','no_pr'=>'PR-ST-1','category'=>'pr stok','app_3'=>'1','sts_req_app'=>0,'app_req'=>0,'sts_po_req'=>0,'app_po_req'=>0,'no_rev'=>0,'reject_status'=>0]), 'Seed PR');
    $db->insert('material_planning_base_on_produksi_detail', ['id'=>11,'so_number'=>'PR1','id_material'=>'71','propose_purchase'=>10,'price_ref'=>100,'status_app'=>'Y']);
    check($db->insert('tr_pr_non_po', ['id'=>1,'no_non_po'=>'CASH1','id_pr'=>1,'no_pr'=>'PR-ST-1','jenis_pr'=>'pr stok','sts'=>'1','id_pic'=>42,'nm_pic'=>'Test','created_by'=>42,'created_date'=>'2026-10-09 00:00:00']), 'Seed Cash');
    $model = receiptModel($db);
    check(count($model->documents()) === 1, 'Approved Cash without Paid is selectable');
    foreach ([-1,11,'NaN','0','1e2'] as $qty) {
        check($model->receive(payload($qty),42)['status'] === 0, 'Reject invalid or excessive qty '.$qty);
    }
    $db->where('id',1)->update('material_planning_base_on_produksi',['app_3'=>null]);
    check(!$model->documents() && $model->receive(payload(1),42)['status']===0,'Reject draft PR');
    $db->where('id',1)->update('material_planning_base_on_produksi',['app_3'=>'1','rejected'=>'1']);
    check(!$model->documents() && $model->receive(payload(1),42)['status']===0,'Reject rejected PR');
    $db->where('id',1)->update('material_planning_base_on_produksi',['rejected'=>null,'deleted_date'=>'2026-10-09 00:00:00']);
    check(!$model->documents(),'Exclude deleted PR');
    $db->where('id',1)->update('material_planning_base_on_produksi',['deleted_date'=>null]);
    $db->where('id',1)->update('tr_pr_non_po',['sts'=>'0']);
    check(!$model->documents(),'Exclude draft Cash');
    $db->where('id',1)->update('tr_pr_non_po',['sts'=>'2','reject_reason'=>'Rejected']);
    check(!$model->documents(),'Exclude rejected Cash');
    $db->where('id',1)->update('tr_pr_non_po',['reject_reason'=>null]);
    check(count($model->documents())===1,'Forwarded to payment remains eligible');
    $db->where('id',11)->update('material_planning_base_on_produksi_detail',['status_app'=>'N']);
    check(!$model->documents() && $model->receive(payload(1),42)['status']===0,'Reject unapproved item');
    $db->where('id',11)->update('material_planning_base_on_produksi_detail',['status_app'=>'Y','id_material'=>'999']);
    check($model->receive(payload(1),42)['status']===0,'Reject missing stock master');
    $db->where('id',11)->update('material_planning_base_on_produksi_detail',['id_material'=>'71']);
    $result = $model->receive(payload(4),42);
    check($result['status']===1,'Partial receipt succeeds: '.json_encode($result));
    check((float)$model->items(['CASH1'])[0]['qty_in']===4.0,'Partial outstanding counts saved Cash');
    $received = $db->get('warehouse_adjustment_detail')->row_array();
    check($received['id_material']==='71' && $received['qty_order']==10 && $received['tipe_po']==='cash' && $received['id_po_detail']==11,'Server resolves material, qty, source metadata');
    check((float)$db->get('tr_cost_book')->row()->nilai_beli===100.0,'Server resolves PR price despite forged input');
    check((float)$db->get('warehouse_stock')->row()->qty_stock===4.0,'Stock updated');
    check((float)$db->get('tr_cost_book')->row()->value_neraca===400.0,'Inventory value updated');
    check($db->count_all('tr_jurnal')===0,'Forged journal payload ignored');
    check($model->references('CASH1')[0]['no_pr']==='PR-ST-1','Cash document retains PR reference');
    // Failure after stock/history updates must undo all inventory writes.
    $db->query("CREATE TRIGGER fail_price BEFORE INSERT ON price_book FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='test failure'");
    check($model->receive(payload(2),42)['status']===0,'Price book failure rejects receipt');
    check($db->count_all('warehouse_adjustment')===1 && $db->count_all('warehouse_adjustment_detail')===1 && $db->count_all('tr_cost_book')===1 && $db->count_all('warehouse_history')===1 && $db->count_all('warehouse_stock_per_day')===1,'Rollback header, detail, cost, history, daily stock');
    check((float)$db->get('warehouse_stock')->row()->qty_stock===4.0 && (float)$db->get('tr_cost_book')->row()->value_neraca===400.0,'Rollback stock and inventory value');
    $db->query('DROP TRIGGER fail_price');
    // Different Cash documents share one PR detail outstanding, not a per-document allowance.
    $cash = $db->get('tr_pr_non_po')->row_array(); unset($cash['id']); $cash['no_non_po']='CASH2';
    check($db->insert('tr_pr_non_po',$cash),'Seed second document for same PR');
    $multi = payload(4); $multi['no_po'][]='CASH2';
    $multi['Detail'][]=payload(4,'CASH2')['Detail'][0];
    check($model->receive($multi,42)['status']===0,'Reject combined over-receipt across documents');
    $multi['Detail'][0]['qty_in']=3; $multi['Detail'][1]['qty_in']=3;
    check($model->receive($multi,42)['status']===1,'Receive multiple documents sharing a PR');
    check(!$model->documents(),'Fully received documents disappear');
    check($model->receive(payload(1),42)['status']===0,'Cannot receive fully received item again');
    check((float)$db->get('warehouse_stock')->row()->qty_stock===10.0,'Total stock never exceeds purchase qty');
    // Two independent PHP/MySQL sessions race on the same outstanding.
    foreach (['warehouse_adjustment_detail','warehouse_adjustment','warehouse_stock','warehouse_history','warehouse_stock_per_day','tr_cost_book','price_book'] as $table) $db->query("DELETE FROM `$table`");
    $gate = 'incoming_cash:'.$schema;
    $db->query('SELECT GET_LOCK(?,10)',[$gate]);
    $workers = [];
    for ($i=0;$i<2;$i++) {
        $pipes=[];
        $process=proc_open([PHP_BINARY,__FILE__,'--worker',$schema],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);
        check(is_resource($process),'Start concurrent worker');
        fclose($pipes[0]);
        $workers[]=[$process,$pipes];
    }
    $db->query('SELECT RELEASE_LOCK(?)',[$gate]);
    $statuses=[];
    foreach ($workers as [$process,$pipes]) {
        $response=json_decode(stream_get_contents($pipes[1]),true);
        $errors=stream_get_contents($pipes[2]); fclose($pipes[1]); fclose($pipes[2]);
        check(proc_close($process)===0 && is_array($response),'Concurrent worker returns JSON '.$errors);
        $statuses[]=$response['status'];
    }
    sort($statuses);
    check($statuses===[0,1],'Only one concurrent six-of-ten receipt succeeds');
    check((float)$db->get('warehouse_stock')->row()->qty_stock===6.0 && $db->count_all('warehouse_adjustment')===1,'Concurrent receipt preserves outstanding and inventory');
    // Independent PR/item and conversion follow the existing inventory valuation rules.
    $db->insert('accessories',['id'=>72,'id_stock'=>'ST-2','stock_name'=>'Item Two','konversi'=>2]);
    $pr=$db->get('material_planning_base_on_produksi')->row_array(); $pr['id']=2; $pr['so_number']='PR2'; $pr['no_pr']='PR-ST-2';
    $db->insert('material_planning_base_on_produksi',$pr);
    $db->insert('material_planning_base_on_produksi_detail',['id'=>22,'so_number'=>'PR2','id_material'=>'72','propose_purchase'=>8,'price_ref'=>200,'status_app'=>'Y']);
    $cash['no_non_po']='CASH3';$cash['id_pr']=2;$cash['no_pr']='PR-ST-2'; $db->insert('tr_pr_non_po',$cash);
    $multi=payload(4);$multi['no_po'][]='CASH3';$multi['Detail'][]=payload(3,'CASH3',22)['Detail'][0];
    check($model->receive($multi,42)['status']===1,'Multiple PRs and materials received together');
    check((float)$db->get_where('warehouse_stock',['id_material'=>'72'])->row()->qty_stock===3.0,'Second material stock recorded');
    check($model->receive(payload(2,'CASH3',22),42)['status']===1,'Existing stock with conversion can receive again');
    $cost=$db->order_by('id','DESC')->get_where('tr_cost_book',['id_material'=>'72'])->row();
    check((float)$cost->qty===3.5 && (float)$cost->value_neraca===1000.0,'Existing conversion and valuation formula preserved');
    check($db->count_all('tr_jurnal')===0,'No Cash journals after every scenario');
    $controller=(new ReflectionClass('Incoming_stok'))->newInstanceWithoutConstructor();
    $controller->db=$db;$controller->input=new TestInput;$controller->uri=new TestUri;
    $controller->auth=new TestAuth;$controller->session=new TestSession;
    $controller->load=new TestLoader($controller);$controller->incoming_cash_model=$model;
    foreach (['id_user'=>42,'datetime'=>date('Y-m-d H:i:s')] as $field=>$value) {
        $property=new ReflectionProperty('Incoming_stok',$field);$property->setAccessible(true);$property->setValue($controller,$value);
    }
    $legacy=(new ReflectionClass('Incoming_stok_model'))->newInstanceWithoutConstructor();
    $legacy->db=$db;$legacy->uri=$controller->uri;$legacy->load=new TestLoader($legacy);
    $controller->incoming_stok_model=$legacy;
    $controller->input->values=['kode_supplier'=>'cash'];
    $options=capture($controller,'pilih_supplier');
    check(strpos($options,'CASH3')!==false && strpos($options,'PR-ST-2')!==false && strpos($options,'CASH1')===false,'Cash selector shows partial doc/PR and hides full docs');
    $controller->input->values=['supplier'=>'cash','no_po'=>['CASH3'],'id_gudang'=>17];
    $details=json_decode(capture($controller,'detail_purchasing_order'),true)['header'];
    check(strpos($details,'CASH3 / PR-ST-2')!==false && strpos($details,'[id_cash]')!==false,'Detail endpoint preserves HTML-in-JSON and Cash row identity');
    $journal=json_decode(capture($controller,'set_jurnal'),true);
    check($journal===['hasil_jurnal'=>'','ttl_debit'=>0,'ttl_kredit'=>0],'Cash journal endpoint returns empty journal');
    $header=$db->order_by('id','DESC')->get('warehouse_adjustment')->row_array();
    $controller->uri->code=$header['kode_trans'];
    $html=capture($controller,'detail');
    check(strpos($html,'CASH3 (PR: PR-ST-2)')!==false,'Detail page renders Cash doc and PR');
    $print=$controller->load->view('print_incoming_stok',['getData'=>[$header],
        'getDataDetail'=>$db->get_where('warehouse_adjustment_detail',['kode_trans'=>$header['kode_trans']])->result_array(),
        'GET_MATERIAL'=>get_accessories(),'GET_SATUAN'=>get_list_satuan(),'no_po'=>'CASH3 (PR: PR-ST-2)','printby'=>'Test'],true);
    check(strpos($print,'CASH3 (PR: PR-ST-2)')!==false,'Print HTML renders Cash doc and PR without browser');
    $_REQUEST=['draw'=>1,'search'=>['value'=>''],'order'=>[['column'=>1,'dir'=>'desc']],'start'=>0,'length'=>100];
    $listing=json_decode(capture($legacy,'data_side_request_material'),true);
    check(strpos(json_encode($listing),'CASH3')!==false && strpos(json_encode($listing),'PR-ST-2')!==false,'Incoming list renders Cash document and PR');
    // Regress the existing PO/kasbon endpoints and receive flow.
    $db->query("INSERT INTO tr_purchase_order VALUES ('PO1','PO-TEST-1','2','SUP1',NULL,0)");
    $db->query("INSERT INTO dt_trans_po VALUES (101,'PO1',11,NULL,'71','Item One',5,0,150,0)");
    $db->query("INSERT INTO tr_kasbon VALUES ('KB1','PR-ST-1','pr stok','1',NULL)");
    $db->query("INSERT INTO tr_pr_detail_kasbon VALUES (201,'KB1','71','Item One',5,0,125,'PCS','stok')");
    foreach (['SUP1'=>'PO-TEST-1','kasbon'=>'KB1'] as $source=>$doc) {
        $controller->input->values=['kode_supplier'=>$source];
        check(strpos(capture($controller,'pilih_supplier'),$doc)!==false,'Legacy document selector '.$source);
        $controller->input->values=['supplier'=>$source,'no_po'=>[$source==='kasbon'?'KB1':'PO1'],'id_gudang'=>17];
        $result=json_decode(capture($controller,'detail_purchasing_order'),true);
        check(strpos($result['header'],'Item One')!==false,'Legacy item endpoint '.$source);
    }
    // Legacy inserts rely on permissive SQL mode; Cash was tested above in strict mode.
    $db->query("SET SESSION sql_mode=''");
    foreach (['SUP1'=>101,'kasbon'=>201] as $source=>$id) {
        $data=payload(2);unset($data['Detail'][0]['id_cash']);
        $data['supplier']=$source;$data['no_po']=[$source==='kasbon'?'KB1':'PO1'];
        $data['Detail'][0]=['id'=>$id,'qty_in'=>2,'qty_po'=>5,'id_barang'=>'71','nm_barang'=>'Item One','ket'=>'Legacy'];
        if ($source==='kasbon') $data['Detail'][0]['id_kasbon']='KB1';
        $data['jurnal']=[['tanggal_jurnal'=>'2026-10-09','no_coa'=>'TEST','id_company'=>'TEST','nm_company'=>'Test','nm_coa'=>'Test',
            'debit'=>100,'kredit'=>0,'id_div'=>'TEST','nm_div'=>'Test']];
        $controller->input->values=$data;
        check(json_decode(capture($controller,'request_stok'),true)['status']===1,'Legacy receive succeeds '.$source);
        $table=$source==='kasbon'?'tr_pr_detail_kasbon':'dt_trans_po';
        check((float)$db->get_where($table,['id'=>$id])->row()->qty_in===2.0,'Legacy received qty updated '.$source);
    }
    check($db->count_all('tr_jurnal')===2,'Legacy journals remain enabled');
    $forged=payload(1,'CASH3',22);$forged['supplier']='SUP1';
    $controller->input->values=$forged;
    check(json_decode(capture($controller,'request_stok'),true)['status']===1 && $db->count_all('tr_jurnal')===2,'Forged source cannot route Cash through legacy journals');
    $controller->input->values=['supplier'=>'SUP1','no_po'=>['CASH3'],'id_gudang'=>17];
    check(json_decode(capture($controller,'set_jurnal'),true)['hasil_jurnal']==='','Cash documents never request a legacy journal');
} finally {
    $db->query("DROP DATABASE `$schema`");
}
