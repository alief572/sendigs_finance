<?php
if (!defined('AUDIT_FIXTURE_ONLY')) { define('AUDIT_FIXTURE_ONLY', true); }
if (!defined('HRIS')) { define('HRIS', 'fixture_hris'); }
if (!defined('DBACC')) { define('DBACC', 'fixture_acc'); }
if (!defined('FCPATH')) { define('FCPATH', sys_get_temp_dir() . '/fcpath/'); }

require dirname(__DIR__, 2) . '/request_payment/tests/Audit_test.php';

$helper = dirname(__DIR__, 3) . '/helpers/kasbon_attachment_helper.php';
if (file_exists($helper)) {
    require_once $helper;
}

if (!class_exists('BF_Model')) { class BF_Model {} }
if (!class_exists('CI_Model')) { class CI_Model {} }

if (!function_exists('show_404')) {
    function show_404($page = '', $log_error = TRUE) {
        $GLOBALS['show404Called'] = true;
        throw new Show404Exception('404 Page Not Found');
    }
}
if (!class_exists('Show404Exception')) {
    class Show404Exception extends Exception {}
}

if (!function_exists('redirect')) {
    function redirect($uri = '') {
        $GLOBALS['redirectTarget'] = $uri;
    }
}

if (!function_exists('simpan_aktifitas')) {
    function simpan_aktifitas($nm_hak_akses = '', $kode_universal = '', $keterangan = '', $jumlah = 0, $sql = '', $status = null) {
        global $ci;
        if (isset($ci->module_audit_recorder)) {
            $ci->module_audit_recorder->defer($keterangan, $status, array(
                'sql' => $sql,
                'data' => array('kode_universal' => $kode_universal, 'jumlah' => $jumlah)
            ));
        }
        return true;
    }
}

if (!function_exists('has_permission')) {
    function has_permission($perm = '') {
        return true;
    }
}

if (!class_exists('Expense', false)) {
    require dirname(__DIR__) . '/controllers/Expense.php';
}

class ExpenseBatch5Result {
    private $group;
    private $table;
    private $params;

    public function __construct($group, $table, $params = array()) {
        $this->group = $group;
        $this->table = $table;
        $this->params = $params;
    }

    public function num_rows() {
        global $scenario;
        if (!empty($scenario['empty_result']) || !empty($scenario['missing_record'])) {
            return 0;
        }
        return 1;
    }

    public function row() {
        global $scenario;
        if (!empty($scenario['empty_result']) || !empty($scenario['missing_record'])) {
            return null;
        }

        return (object) array(
            'id' => 1,
            'name' => 'Mock Row'
        );
    }

    public function row_array() {
        global $scenario;
        if (!empty($scenario['empty_result']) || !empty($scenario['missing_record'])) {
            return array();
        }
        return array(
            'id' => 1,
            'name' => 'Mock Row'
        );
    }

    public function result() {
        global $scenario;
        if (!empty($scenario['empty_result'])) {
            return array();
        }

        return array(
            (object) array(
                'id' => 1,
                'no_doc' => 'TR/2026/10/0001',
                'no_req' => '',
                'nama' => 'budi',
                'tgl_doc' => '2026-10-15',
                'bensin' => 100000,
                'tol' => 50000,
                'parkir' => 20000,
                'lainnya' => 10000,
                'jumlah_kasbon' => 180000
            )
        );
    }

    public function result_array() {
        global $scenario;
        if (!empty($scenario['empty_result'])) {
            return array();
        }
        return array(
            array('id' => 1, 'name' => 'Mock Array Row')
        );
    }
}

class ExpenseBatch5Db {
    public $whereData = array();
    public $selectFields = '';
    public $fromTable = '';
    public $joinData = array();
    public $groupData = array();
    public $lastQuery = 'SELECT * FROM mock_table';

    public function select($fields) {
        $this->selectFields = $fields;
        return $this;
    }

    public function from($table) {
        $this->fromTable = $table;
        return $this;
    }

    public function join($table, $cond, $type = '') {
        $this->joinData[] = array($table, $cond, $type);
        return $this;
    }

    public function where($key, $val = null) {
        $this->whereData[$key] = $val;
        return $this;
    }

    public function group_by($field) {
        $this->groupData[] = $field;
        return $this;
    }

    public function escape_str($str) {
        return addslashes($str);
    }

    public function get() {
        global $scenario;
        if (!empty($scenario['fail_query'])) {
            return false;
        }
        return new ExpenseBatch5Result('get', $this->fromTable, $this->whereData);
    }

    public function get_where($table, $where = array()) {
        global $scenario;
        if (!empty($scenario['fail_query'])) {
            return false;
        }
        if (!empty($scenario['missing_record'])) {
            return new ExpenseBatch5Result('get_where', $table, array('empty_result' => true));
        }
        return new ExpenseBatch5Result('get_where', $table, $where);
    }

    public function query($sql) {
        global $scenario;
        if (!empty($scenario['fail_query'])) {
            return false;
        }
        $this->lastQuery = $sql;
        return new ExpenseBatch5Result('query', $sql);
    }

    public function insert($table, $data) {
        global $scenario;
        if (!empty($scenario['fail_insert'])) {
            return false;
        }
        $this->lastQuery = "INSERT INTO $table";
        return true;
    }

    public function insert_id() {
        return 101;
    }

    public function update($table, $data, $where = array()) {
        global $scenario;
        if (!empty($scenario['fail_update'])) {
            return false;
        }
        $this->lastQuery = "UPDATE $table";
        return true;
    }

    public function delete($table, $where = array()) {
        global $scenario;
        if (!empty($scenario['fail_delete'])) {
            return false;
        }
        $this->lastQuery = "DELETE FROM $table";
        return true;
    }

    public function trans_begin() {}
    public function trans_status() {
        global $scenario;
        return empty($scenario['transaction_failure']);
    }
    public function trans_commit() {
        global $scenario;
        return empty($scenario['trans_commit_failure']);
    }
    public function trans_rollback() {}

    public function last_query() {
        return $this->lastQuery;
    }

    public function error($res = null) {
        global $scenario;
        if (!empty($scenario['fail_query']) || !empty($scenario['transaction_failure']) || !empty($scenario['trans_commit_failure'])) {
            return array('code' => 500, 'message' => 'Simulated DB error');
        }
        return array('code' => 0, 'message' => '');
    }
}

class ExpenseBatch5Model {
    public function GetDataTransport($id) {
        global $scenario;
        if (!empty($scenario['missing_record'])) {
            return null;
        }
        return (object) array(
            'id' => $id,
            'no_doc' => 'TR/2026/10/0001',
            'departement' => 'DEP-FIN',
            'nama' => 'budi',
            'status' => '0',
            'jumlah_kasbon' => 180000
        );
    }

    public function getCoaTransport() {
        global $scenario;
        if (!empty($scenario['fail_query'])) {
            return false;
        }
        if (!empty($scenario['empty_coa'])) {
            return array();
        }
        return array(
            (object) array('no_perkiraan' => '1304-01-01', 'nama' => 'Bensin & Tol'),
            (object) array('no_perkiraan' => '1304-01-02', 'nama' => 'Parkir')
        );
    }

    public function GetDataTransportReq($id) {
        global $scenario;
        if (!empty($scenario['missing_record'])) {
            return null;
        }
        return (object) array(
            'id' => $id,
            'no_doc' => 'TR-REQ-2026-001',
            'departement' => 'DEP-FIN',
            'nama' => 'budi',
            'status' => '0',
            'jumlah_expense' => 500000
        );
    }
}

class ExpenseBatch5AllModel {
    public function GetAutoGenerate($format) {
        return 'TR/2026/10/0001';
    }

    public function dataSave($table, $data) {
        global $scenario;
        if (!empty($scenario['fail_save'])) {
            return false;
        }
        return 101;
    }

    public function dataUpdate($table, $data, $where = array()) {
        global $scenario;
        if (!empty($scenario['fail_update'])) {
            return false;
        }
        return true;
    }

    public function dataDelete($table, $where = array()) {
        global $scenario;
        if (!empty($scenario['fail_delete'])) {
            return false;
        }
        return true;
    }

    public function GetOneTable($table, $where = array(), $sort = '') {
        global $scenario;
        if (!empty($scenario['fail_query'])) {
            return false;
        }
        if (!empty($scenario['empty_result'])) {
            return array();
        }
        return array(
            (object) array(
                'id' => 1,
                'no_doc' => 'TR/2026/10/0001',
                'nama' => 'budi',
                'departement' => 'DEP-FIN',
                'status' => '1'
            )
        );
    }
}

class ExpenseBatch5Template {
    public $vars = array();
    public $renderedView = '';
    public $viewTitle = '';

    public function set($key, $val = null) {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->vars[$k] = $v;
            }
        } else {
            $this->vars[$key] = $val;
        }
    }

    public function title($title) {
        $this->viewTitle = $title;
    }

    public function page_icon($icon) {}

    public function render($view) {
        $this->renderedView = $view;
    }
}

class ExpenseBatch5Load {
    public $viewLoaded = '';
    public $viewData = array();

    public function view($view, $data = array()) {
        $this->viewLoaded = $view;
        $this->viewData = $data;
    }

    public function library($lib, $config = array()) {}
}

class ExpenseBatch5Upload {
    public function initialize($config = array()) {}
    public function do_upload($field = 'file') {
        global $scenario;
        return empty($scenario['upload_failure']);
    }
    public function data() {
        return array('file_name' => 'receipt_uploaded.png');
    }
    public function display_errors($open = '', $close = '') {
        return 'Mock upload error';
    }
}

class ExpenseBatch5Auth {
    public function user_name() {
        return 'tester';
    }
    public function user_id() {
        return 1;
    }
}

class ExpenseBatch5Input {
    public $values = array();

    public function post($key = null) {
        if ($key === null) {
            return $this->values;
        }
        return isset($this->values[$key]) ? $this->values[$key] : null;
    }
}

function expense_b5_run($method, $options = array(), $post = array(), $args = array()) {
    global $scenario, $events, $ci;
    $scenario = $options;
    $events = array();
    $GLOBALS['scenarioCount']++;

    $recorder = new Module_audit_recorder();

    $ci = (new ReflectionClass('Expense'))->newInstanceWithoutConstructor();
    $ci->module_audit_recorder = $recorder;
    $ci->db = new ExpenseBatch5Db();
    $ci->input = new ExpenseBatch5Input();
    $ci->auth = new ExpenseBatch5Auth();
    $ci->template = new ExpenseBatch5Template();
    $ci->load = new ExpenseBatch5Load();
    $ci->upload = new ExpenseBatch5Upload();
    $ci->Expense_model = new ExpenseBatch5Model();
    $ci->All_model = new ExpenseBatch5AllModel();

    $propStatus = new ReflectionProperty('Expense', 'status');
    $propStatus->setAccessible(true);
    $propStatus->setValue($ci, array("0" => "Waiting Approval Finance", "1" => "Approved", "2" => "Approved", "3" => "Approved", "9" => "Rejected"));

    $propManage = new ReflectionProperty('Expense', 'managePermission');
    $propManage->setAccessible(true);
    $propManage->setValue($ci, 'Expense.Manage');

    $ci->input->values = array_merge(array(
        'password' => 'PRIVATE_PASSWORD',
        'credit_card' => 'PRIVATE_CC',
        'doc_file' => 'binary_blob',
        'nama' => 'John Doe'
    ), $post);

    $caughtException = null;
    ob_start();
    try {
        $GLOBALS['expenseLastReturn'] = call_user_func_array(array($ci, $method), $args);
    } catch (Throwable $e) {
        $caughtException = $e;
        if (isset($ci->module_audit_recorder)) {
            $ci->module_audit_recorder->finish_interrupted();
        }
        if (empty($options['expected_exception'])) {
            ob_end_clean();
            throw $e;
        }
    }
    $output = ob_get_clean();

    if (isset($ci->module_audit_recorder)) {
        $ci->module_audit_recorder->finish_interrupted();
    }

    if (!empty($options['pending'])) {
        $pendingEvents = array_filter($events, function ($e) use ($method) {
            return ($e['context']['methode'] ?? '') === $method;
        });
        check(empty($pendingEvents), $method . ': pending endpoint activated prematurely');
        return array(null, $output, $ci);
    }

    check(count($events) === 1, $method . ': expected exactly one audit event, got ' . count($events));
    check($events[0]['context']['module'] === 'expense', 'Wrong module attribution: ' . ($events[0]['context']['module'] ?? 'none'));

    $event = $events[0];
    $flatPayload = json_encode($event['context']);
    check(strpos($flatPayload, 'PRIVATE_PASSWORD') === false, $method . ': password leaked in audit context');
    check(strpos($flatPayload, 'PRIVATE_CC') === false, $method . ': credit card leaked in audit context');
    check(strpos($flatPayload, 'binary_blob') === false, $method . ': binary blob leaked in audit context');
    check(strpos($flatPayload, 'John Doe') === false, $method . ': name leaked in audit context');

    return array($event, $output, $ci);
}

// Subprocess entrypoint
if (in_array('--subprocess-shutdown', $argv ?? array(), true)) {
    list($e, $out) = expense_b5_run('petty_cash', array('writer_exception' => true));
    exit($e['status'] === 1 ? 0 : 1);
}

// -------------------------------------------------------------
// 1. transport_save()
// -------------------------------------------------------------
$sampleTransport = array(
    'tgl_doc' => '2026-10-15',
    'departement' => 'DEP-FIN',
    'keperluan' => 'Meeting Kantor',
    'rute' => 'Jakarta - Bekasi',
    'nopol' => 'B 1234 CD',
    'km_awal' => '100',
    'km_akhir' => '150',
    'bensin' => 100000,
    'tol' => 50000,
    'parkir' => 20000,
    'lainnya' => 10000,
    'filename' => '',
    'keterangan' => 'Keperluan meeting klien',
    'coa' => '1304-01-01',
    'nm_coa' => 'Bensin & Tol'
);

// A. Create mode ($id === '')
list($e, $out) = expense_b5_run('transport_save', array(), array_merge($sampleTransport, array('id' => '', 'no_doc' => '')));
check($e['status'] === 1, 'transport_save (create): status 1');
check($e['context']['data']['outcome'] === 'created', 'transport_save (create): outcome created');
check($e['context']['data']['amount'] == 180000, 'transport_save (create): amount matched');
check($e['context']['data']['departement'] === 'DEP-FIN', 'transport_save (create): departement matched');
$resp = json_decode($out, true);
check($resp['save'] === true, 'transport_save (create): json save true');
check($resp['id'] == 101, 'transport_save (create): json id 101');

// B. Update mode ($id !== '')
list($e, $out) = expense_b5_run('transport_save', array(), array_merge($sampleTransport, array('id' => '10', 'no_doc' => 'TR/2026/10/0001')));
check($e['status'] === 1, 'transport_save (update): status 1');
check($e['context']['data']['outcome'] === 'updated', 'transport_save (update): outcome updated');
check($e['context']['data']['id'] == 10, 'transport_save (update): id matched');
$resp = json_decode($out, true);
check($resp['save'] === true, 'transport_save (update): json save true');

// C. Upload failure
$_FILES['doc_file'] = array(
    'name' => 'struk.jpg',
    'type' => 'image/jpeg',
    'tmp_name' => sys_get_temp_dir() . '/tmp.jpg',
    'error' => 0,
    'size' => 2048
);
list($e, $out) = expense_b5_run('transport_save', array('upload_failure' => true), array_merge($sampleTransport, array('id' => '10')));
check($e['status'] === 0, 'transport_save (upload fail): status 0');
check($e['context']['data']['outcome'] === 'upload_failed', 'transport_save (upload fail): outcome upload_failed');
$resp = json_decode($out, true);
check($resp['save'] === false, 'transport_save (upload fail): json save false');
unset($_FILES['doc_file']);

// D. Transaction status failure
list($e, $out) = expense_b5_run('transport_save', array('transaction_failure' => true), array_merge($sampleTransport, array('id' => '')));
check($e['status'] === 0, 'transport_save (trans fail): status 0');
check($e['context']['data']['outcome'] === 'transaction_failed', 'transport_save (trans fail): outcome transaction_failed');
$resp = json_decode($out, true);
check($resp['save'] === false, 'transport_save (trans fail): json save false');

// E. Transaction commit failure
list($e, $out) = expense_b5_run('transport_save', array('trans_commit_failure' => true), array_merge($sampleTransport, array('id' => '10')));
check($e['status'] === 0, 'transport_save (commit fail): status 0');
check($e['context']['data']['outcome'] === 'transaction_failed', 'transport_save (commit fail): outcome transaction_failed');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e, $out) = expense_b5_run('transport_save', array($writer => true), array_merge($sampleTransport, array('id' => '10')));
    check($e['status'] === 1, 'transport_save: unaffected by ' . $writer);
    $resp = json_decode($out, true);
    check($resp['save'] === true, 'transport_save: business json unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 2. get_list_req_transport($nama, $departement, $date1, $date2)
// -------------------------------------------------------------
// A. Normal fetch
list($e, $out) = expense_b5_run('get_list_req_transport', array(), array(
    'existing_ids' => array('99'),
    'no_doc' => 'TR-REQ-2026-001'
), array('budi', 'DEP-FIN', '2026-10-01', '2026-10-15'));
check($e['status'] === 1, 'get_list_req_transport: status 1');
check($e['context']['data']['outcome'] === 'fetched', 'get_list_req_transport: outcome fetched');
check($e['context']['data']['item_count'] === 1, 'get_list_req_transport: item_count matched');
check($e['context']['data']['departement'] === 'DEP-FIN', 'get_list_req_transport: departement matched');
$resp = json_decode($out, true);
check(is_array($resp) && count($resp) === 1, 'get_list_req_transport: json array returned');

// B. Empty result
list($e, $out) = expense_b5_run('get_list_req_transport', array('empty_result' => true), array(), array('budi', 'DEP-FIN', '2026-10-01', '2026-10-15'));
check($e['status'] === 1, 'get_list_req_transport (empty): status 1');
check($e['context']['data']['outcome'] === 'fetched', 'get_list_req_transport (empty): outcome fetched');
check($e['context']['data']['item_count'] === 0, 'get_list_req_transport (empty): item_count 0');

// C. Query failure
list($e, $out) = expense_b5_run('get_list_req_transport', array('fail_query' => true), array(), array('budi', 'DEP-FIN', '2026-10-01', '2026-10-15'));
check($e['status'] === 0, 'get_list_req_transport (query fail): status 0');
check($e['context']['data']['outcome'] === 'query_error', 'get_list_req_transport (query fail): outcome query_error');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e, $out) = expense_b5_run('get_list_req_transport', array($writer => true), array(), array('budi', 'DEP-FIN', '2026-10-01', '2026-10-15'));
    check($e['status'] === 1, 'get_list_req_transport: unaffected by ' . $writer);
    $resp = json_decode($out, true);
    check(is_array($resp), 'get_list_req_transport: business response unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 3. get_transport($nama, $departement)
// -------------------------------------------------------------
// A. Normal fetch
list($e, $out) = expense_b5_run('get_transport', array(), array(), array('budi', 'DEP-FIN'));
check($e['status'] === 1, 'get_transport: status 1');
check($e['context']['data']['outcome'] === 'fetched', 'get_transport: outcome fetched');
check($e['context']['data']['departement'] === 'DEP-FIN', 'get_transport: departement matched');
check($e['context']['data']['item_count'] === 1, 'get_transport: item_count matched');
$resp = json_decode($out, true);
check(is_array($resp) && count($resp) === 1, 'get_transport: json array returned');

// B. Empty result
list($e, $out) = expense_b5_run('get_transport', array('empty_result' => true), array(), array('budi', 'DEP-FIN'));
check($e['status'] === 1, 'get_transport (empty): status 1');
check($e['context']['data']['outcome'] === 'fetched', 'get_transport (empty): outcome fetched');
check($e['context']['data']['item_count'] === 0, 'get_transport (empty): item_count 0');

// C. Query failure
list($e, $out) = expense_b5_run('get_transport', array('fail_query' => true), array(), array('budi', 'DEP-FIN'));
check($e['status'] === 0, 'get_transport (query fail): status 0');
check($e['context']['data']['outcome'] === 'query_error', 'get_transport (query fail): outcome query_error');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e, $out) = expense_b5_run('get_transport', array($writer => true), array(), array('budi', 'DEP-FIN'));
    check($e['status'] === 1, 'get_transport: unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 4. transport_edit($id)
// -------------------------------------------------------------
// A. Normal found
list($e) = expense_b5_run('transport_edit', array(), array(), array(10));
check($e['status'] === 1, 'transport_edit: status 1');
check($e['context']['data']['outcome'] === 'rendered', 'transport_edit: outcome rendered');
check($e['context']['data']['id'] == 10, 'transport_edit: id matched');
check($e['context']['data']['no_doc'] === 'TR/2026/10/0001', 'transport_edit: no_doc matched');
check($ci->template->renderedView === 'transport_form', 'transport_edit: template renderedView matched');

// B. Missing record
list($e) = expense_b5_run('transport_edit', array('missing_record' => true), array(), array(999));
check($e['status'] === 0, 'transport_edit (missing): status 0');
check($e['context']['data']['outcome'] === 'not_found', 'transport_edit (missing): outcome not_found');

// C. Query failure
list($e) = expense_b5_run('transport_edit', array('fail_query' => true), array(), array(10));
check($e['status'] === 0, 'transport_edit (query fail): status 0');
check($e['context']['data']['outcome'] === 'query_error', 'transport_edit (query fail): outcome query_error');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e) = expense_b5_run('transport_edit', array($writer => true), array(), array(10));
    check($e['status'] === 1, 'transport_edit: unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 5. transport_view($id)
// -------------------------------------------------------------
// A. Normal view
list($e) = expense_b5_run('transport_view', array(), array(), array(10));
check($e['status'] === 1, 'transport_view: status 1');
check($e['context']['data']['outcome'] === 'rendered', 'transport_view: outcome rendered');
check($e['context']['data']['id'] == 10, 'transport_view: id matched');
check($e['context']['data']['no_doc'] === 'TR/2026/10/0001', 'transport_view: no_doc matched');
check($ci->template->renderedView === 'transport_form', 'transport_view: template renderedView matched');

// B. Missing record
list($e) = expense_b5_run('transport_view', array('missing_record' => true), array(), array(999));
check($e['status'] === 0, 'transport_view (missing): status 0');
check($e['context']['data']['outcome'] === 'not_found', 'transport_view (missing): outcome not_found');

// C. Query failure
list($e) = expense_b5_run('transport_view', array('fail_query' => true), array(), array(10));
check($e['status'] === 0, 'transport_view (query fail): status 0');
check($e['context']['data']['outcome'] === 'query_error', 'transport_view (query fail): outcome query_error');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e) = expense_b5_run('transport_view', array($writer => true), array(), array(10));
    check($e['status'] === 1, 'transport_view: unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 6. transport_req_approve($id = '', $status = null)
// -------------------------------------------------------------
// A. Finance approve (status = 1)
list($e, $out) = expense_b5_run('transport_req_approve', array(), array(), array(10, 1));
check($e['status'] === 1, 'transport_req_approve (fin): status 1');
check($e['context']['data']['outcome'] === 'approved', 'transport_req_approve (fin): outcome approved');
check($e['context']['data']['stage'] === 'finance', 'transport_req_approve (fin): stage finance');
$resp = json_decode($out, true);
check($resp['save'] === true, 'transport_req_approve (fin): json save true');
check($resp['id'] == 10, 'transport_req_approve (fin): json id 10');

// B. Management approve (status = 2)
list($e, $out) = expense_b5_run('transport_req_approve', array(), array(), array(10, 2));
check($e['status'] === 1, 'transport_req_approve (mgt): status 1');
check($e['context']['data']['outcome'] === 'approved', 'transport_req_approve (mgt): outcome approved');
check($e['context']['data']['stage'] === 'management', 'transport_req_approve (mgt): stage management');

// C. Empty ID
list($e, $out) = expense_b5_run('transport_req_approve', array(), array(), array('', 1));
check($e['status'] === 0, 'transport_req_approve (empty id): status 0');
check($e['context']['data']['outcome'] === 'empty_id', 'transport_req_approve (empty id): outcome empty_id');
$resp = json_decode($out, true);
check($resp['save'] === false, 'transport_req_approve (empty id): json save false');

// D. Update failure
list($e, $out) = expense_b5_run('transport_req_approve', array('fail_update' => true), array(), array(10, 1));
check($e['status'] === 0, 'transport_req_approve (fail update): status 0');
check($e['context']['data']['outcome'] === 'update_failed', 'transport_req_approve (fail update): outcome update_failed');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e, $out) = expense_b5_run('transport_req_approve', array($writer => true), array(), array(10, 1));
    check($e['status'] === 1, 'transport_req_approve: unaffected by ' . $writer);
    $resp = json_decode($out, true);
    check($resp['save'] === true, 'transport_req_approve: business response unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 7. transport_approve($id = '')
// -------------------------------------------------------------
// A. Normal approve
list($e, $out) = expense_b5_run('transport_approve', array(), array(), array(10));
check($e['status'] === 1, 'transport_approve: status 1');
check($e['context']['data']['outcome'] === 'approved', 'transport_approve: outcome approved');
$resp = json_decode($out, true);
check($resp['save'] === true, 'transport_approve: json save true');
check($resp['id'] == 10, 'transport_approve: json id 10');

// B. Empty ID
list($e, $out) = expense_b5_run('transport_approve', array(), array(), array(''));
check($e['status'] === 0, 'transport_approve (empty id): status 0');
check($e['context']['data']['outcome'] === 'empty_id', 'transport_approve (empty id): outcome empty_id');
$resp = json_decode($out, true);
check($resp['save'] === false, 'transport_approve (empty id): json save false');

// C. Update failure
list($e, $out) = expense_b5_run('transport_approve', array('fail_update' => true), array(), array(10));
check($e['status'] === 0, 'transport_approve (fail update): status 0');
check($e['context']['data']['outcome'] === 'update_failed', 'transport_approve (fail update): outcome update_failed');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e, $out) = expense_b5_run('transport_approve', array($writer => true), array(), array(10));
    check($e['status'] === 1, 'transport_approve: unaffected by ' . $writer);
    $resp = json_decode($out, true);
    check($resp['save'] === true, 'transport_approve: business response unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 8. transport_delete($id)
// -------------------------------------------------------------
// A. Normal delete
list($e, $out) = expense_b5_run('transport_delete', array(), array(), array(10));
check($e['status'] === 1, 'transport_delete: status 1');
check($e['context']['data']['outcome'] === 'deleted', 'transport_delete: outcome deleted');
check($e['context']['data']['id'] == 10, 'transport_delete: id matched');
$resp = json_decode($out, true);
check($resp['delete'] === true, 'transport_delete: json delete true');

// B. Transaction failure
list($e, $out) = expense_b5_run('transport_delete', array('transaction_failure' => true), array(), array(10));
check($e['status'] === 0, 'transport_delete (trans fail): status 0');
check($e['context']['data']['outcome'] === 'delete_failed', 'transport_delete (trans fail): outcome delete_failed');
$resp = json_decode($out, true);
check($resp['delete'] === false, 'transport_delete (trans fail): json delete false');

// C. Trans commit failure
list($e, $out) = expense_b5_run('transport_delete', array('trans_commit_failure' => true), array(), array(10));
check($e['status'] === 0, 'transport_delete (commit fail): status 0');
check($e['context']['data']['outcome'] === 'delete_failed', 'transport_delete (commit fail): outcome delete_failed');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e, $out) = expense_b5_run('transport_delete', array($writer => true), array(), array(10));
    check($e['status'] === 1, 'transport_delete: unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 9. transport_req_delete($id)
// -------------------------------------------------------------
// A. Normal delete
list($e, $out) = expense_b5_run('transport_req_delete', array(), array(), array(10));
check($e['status'] === 1, 'transport_req_delete: status 1');
check($e['context']['data']['outcome'] === 'deleted', 'transport_req_delete: outcome deleted');
check($e['context']['data']['id'] == 10, 'transport_req_delete: id matched');
check($e['context']['data']['no_doc'] === 'TR-REQ-2026-001', 'transport_req_delete: no_doc matched');
$resp = json_decode($out, true);
check($resp['delete'] === true, 'transport_req_delete: json delete true');

// B. Missing record
list($e, $out) = expense_b5_run('transport_req_delete', array('missing_record' => true), array(), array(999));
check($e['status'] === 0, 'transport_req_delete (missing): status 0');
check($e['context']['data']['outcome'] === 'not_found', 'transport_req_delete (missing): outcome not_found');
$resp = json_decode($out, true);
check($resp['delete'] === false, 'transport_req_delete (missing): json delete false');

// C. Transaction failure
list($e, $out) = expense_b5_run('transport_req_delete', array('transaction_failure' => true), array(), array(10));
check($e['status'] === 0, 'transport_req_delete (trans fail): status 0');
check($e['context']['data']['outcome'] === 'delete_failed', 'transport_req_delete (trans fail): outcome delete_failed');

// D. Trans commit failure
list($e, $out) = expense_b5_run('transport_req_delete', array('trans_commit_failure' => true), array(), array(10));
check($e['status'] === 0, 'transport_req_delete (commit fail): status 0');
check($e['context']['data']['outcome'] === 'delete_failed', 'transport_req_delete (commit fail): outcome delete_failed');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e, $out) = expense_b5_run('transport_req_delete', array($writer => true), array(), array(10));
    check($e['status'] === 1, 'transport_req_delete: unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 10. petty_cash()
// -------------------------------------------------------------
// A. Normal render
list($e) = expense_b5_run('petty_cash');
check($e['status'] === 1, 'petty_cash: status 1');
check($e['context']['data']['outcome'] === 'rendered', 'petty_cash: outcome rendered');
check($e['context']['data']['page'] === 'index_pc', 'petty_cash: page matched');
check($ci->template->renderedView === 'index_pc', 'petty_cash: template renderedView matched');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e) = expense_b5_run('petty_cash', array($writer => true));
    check($e['status'] === 1, 'petty_cash: unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 11. Uninstrumented Batch 8 endpoint protection
// -------------------------------------------------------------
foreach (array('get_data_transport_req_all', 'get_coa_name', 'kasbon_fin_manage', 'get_dat_list_kasbon', 'get_dat_kasbon_list') as $pendingMethod) {
    if (method_exists($ci, $pendingMethod)) {
        $refM = new ReflectionMethod($ci, $pendingMethod);
        $dummyArgs = array();
        for ($i = 0; $i < $refM->getNumberOfRequiredParameters(); $i++) {
            $dummyArgs[] = 1;
        }
        expense_b5_run($pendingMethod, array('pending' => true, 'expected_exception' => true), array(), $dummyArgs);
    }
}

// -------------------------------------------------------------
// 12. Subprocess Shutdown Runner
// -------------------------------------------------------------
$cmd = escapeshellcmd(PHP_BINARY) . ' ' . escapeshellarg(__FILE__) . ' --subprocess-shutdown 2>&1';
exec($cmd, $subOutput, $subExitCode);
check($subExitCode === 0, 'Subprocess shutdown exit code 0');

echo "PASS Expense batch 5: {$scenarioCount} fixture scenarios + 1 shutdown subprocess" . PHP_EOL;
