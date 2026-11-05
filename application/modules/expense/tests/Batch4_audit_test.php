<?php
if (!defined('AUDIT_FIXTURE_ONLY')) { define('AUDIT_FIXTURE_ONLY', true); }
if (!defined('HRIS')) { define('HRIS', 'fixture_hris'); }
if (!defined('DBACC')) { define('DBACC', 'fixture_acc'); }
if (!defined('FCPATH')) { define('FCPATH', sys_get_temp_dir() . '/fcpath/'); }
if (!is_dir(FCPATH)) { mkdir(FCPATH, 0775, true); }

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

class ExpenseBatch4Result {
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

        if (strpos($this->table, 'users') !== false) {
            return (object) array(
                'id' => 1,
                'username' => 'budi',
                'nm_lengkap' => 'Budi Santoso'
            );
        }

        if (strpos($this->table, 'tr_transport_req') !== false) {
            return (object) array(
                'id' => 10,
                'no_doc' => 'TR-REQ-2026-001',
                'departement' => 'DEP-FIN',
                'nama' => 'budi',
                'status' => '0',
                'jumlah_expense' => 500000
            );
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
        if (!empty($scenario['empty_result']) || !empty($scenario['empty_detail'])) {
            return array();
        }

        if (strpos($this->table, 'tr_transport') !== false) {
            return array(
                (object) array(
                    'id' => 1,
                    'no_req' => 'TR-REQ-2026-001',
                    'bensin' => 100000,
                    'tol' => 50000,
                    'parkir' => 20000,
                    'lainnya' => 10000,
                    'no_coa' => '1304-01-01',
                    'nm_coa' => 'Bensin',
                    'keperluan' => 'Meeting Klien',
                    'rute' => 'Kantor - Klien',
                    'nopol' => 'B 1234 CD',
                    'tgl_doc' => '2026-10-15',
                    'jumlah_kasbon' => 180000,
                    'doc_file' => 'receipt.png'
                )
            );
        }

        return array(
            (object) array('id' => 1, 'no_doc' => 'TR-REQ-2026-001')
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

class ExpenseBatch4Db {
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

    public function get() {
        global $scenario;
        if (!empty($scenario['fail_query'])) {
            return false;
        }
        return new ExpenseBatch4Result('get', $this->fromTable, $this->whereData);
    }

    public function get_where($table, $where = array()) {
        global $scenario;
        if (!empty($scenario['fail_query'])) {
            return false;
        }
        if (!empty($scenario['missing_record'])) {
            return new ExpenseBatch4Result('get_where', $table, array('empty_result' => true));
        }
        return new ExpenseBatch4Result('get_where', $table, $where);
    }

    public function query($sql) {
        global $scenario;
        if (!empty($scenario['fail_query'])) {
            return false;
        }
        $this->lastQuery = $sql;
        return new ExpenseBatch4Result('query', $sql);
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

class ExpenseBatch4Model {
    public function GetListDataTransportRequest($username = '') {
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
                'no_doc' => 'TR-REQ-2026-001',
                'created_by' => 'tester',
                'status' => '0'
            )
        );
    }

    public function GetListDataTransportRequestAll() {
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
                'no_doc' => 'TR-REQ-2026-001',
                'status' => '0'
            )
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

    public function GetDataTransportInReq($no_doc) {
        global $scenario;
        if (!empty($scenario['fail_query'])) {
            return false;
        }
        if (!empty($scenario['empty_detail'])) {
            return array();
        }
        return array(
            (object) array(
                'id' => 1,
                'no_req' => $no_doc,
                'bensin' => 100000,
                'tol' => 50000,
                'parkir' => 20000,
                'lainnya' => 10000,
                'no_coa' => '1304-01-01',
                'nm_coa' => 'Bensin & Tol',
                'keperluan' => 'Meeting Kantor',
                'rute' => 'Jakarta - Bekasi',
                'nopol' => 'B 1234 CD',
                'tgl_doc' => '2026-10-15',
                'jumlah_kasbon' => 180000,
                'doc_file' => 'receipt.png'
            )
        );
    }

    public function GetDepartmentName($dept) {
        return 'Finance & Accounting';
    }
}

class ExpenseBatch4AllModel {
    public function GetAutoGenerate($format) {
        return 'TR-REQ/2026/10/0001';
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
}

class ExpenseBatch4Template {
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

class ExpenseBatch4Load {
    public $viewLoaded = '';
    public $viewData = array();

    public function view($view, $data = array()) {
        $this->viewLoaded = $view;
        $this->viewData = $data;
    }

    public function library($lib, $config = array()) {}
}

class ExpenseBatch4Upload {
    public function initialize($config = array()) {}
    public function do_upload($field = 'file') {
        global $scenario;
        if (empty($scenario['upload_failure'])) { file_put_contents(FCPATH . 'assets/expense/receipt_uploaded.png', 'upload fixture'); }
        return empty($scenario['upload_failure']);
    }
    public function data() {
        return array('file_name' => 'receipt_uploaded.png');
    }
    public function display_errors($open = '', $close = '') {
        return 'Mock upload error';
    }
}

class ExpenseBatch4Auth {
    public function user_name() {
        return 'tester';
    }
    public function user_id() {
        return 1;
    }
}

class ExpenseBatch4Input {
    public $values = array();

    public function post($key = null) {
        if ($key === null) {
            return $this->values;
        }
        return isset($this->values[$key]) ? $this->values[$key] : null;
    }
}

function expense_b4_run($method, $options = array(), $post = array(), $args = array()) {
    global $scenario, $events, $ci;
    $scenario = $options;
    $events = array();
    $GLOBALS['scenarioCount']++;

    $recorder = new Module_audit_recorder();

    $ci = (new ReflectionClass('Expense'))->newInstanceWithoutConstructor();
    $ci->module_audit_recorder = $recorder;
    $ci->db = new ExpenseBatch4Db();
    $ci->input = new ExpenseBatch4Input();
    $ci->auth = new ExpenseBatch4Auth();
    $ci->template = new ExpenseBatch4Template();
    $ci->load = new ExpenseBatch4Load();
    $ci->upload = new ExpenseBatch4Upload();
    $ci->Expense_model = new ExpenseBatch4Model();
    $ci->All_model = new ExpenseBatch4AllModel();

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
    list($e, $out) = expense_b4_run('transport_req_fin', array('writer_exception' => true));
    exit($e['status'] === 1 ? 0 : 1);
}

// -------------------------------------------------------------
// 1. transport_req_fin()
// -------------------------------------------------------------
// A. Normal render
list($e) = expense_b4_run('transport_req_fin');
check($e['status'] === 1, 'transport_req_fin: status 1');
check($e['context']['data']['outcome'] === 'rendered', 'transport_req_fin: outcome rendered');
check($e['context']['data']['page'] === 'transport_req_fin_list', 'transport_req_fin: page matched');
check($ci->template->renderedView === 'transport_req_fin_list', 'transport_req_fin: template renderedView matched');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e) = expense_b4_run('transport_req_fin', array($writer => true));
    check($e['status'] === 1, 'transport_req_fin: unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 2. transport_req_all()
// -------------------------------------------------------------
// A. Normal render
list($e) = expense_b4_run('transport_req_all');
check($e['status'] === 1, 'transport_req_all: status 1');
check($e['context']['data']['outcome'] === 'rendered', 'transport_req_all: outcome rendered');
check($e['context']['data']['page'] === 'transport_req_all', 'transport_req_all: page matched');
check($e['context']['data']['item_count'] === 1, 'transport_req_all: item_count matched');
check($ci->template->renderedView === 'transport_req_all', 'transport_req_all: template renderedView matched');

// B. Empty result
list($e) = expense_b4_run('transport_req_all', array('empty_result' => true));
check($e['status'] === 1, 'transport_req_all (empty): status 1');
check($e['context']['data']['outcome'] === 'rendered', 'transport_req_all (empty): outcome rendered');
check($e['context']['data']['item_count'] === 0, 'transport_req_all (empty): item_count 0');

// C. Query failure
list($e) = expense_b4_run('transport_req_all', array('fail_query' => true));
check($e['status'] === 0, 'transport_req_all (query fail): status 0');
check($e['context']['data']['outcome'] === 'query_error', 'transport_req_all (query fail): outcome query_error');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e) = expense_b4_run('transport_req_all', array($writer => true));
    check($e['status'] === 1, 'transport_req_all: unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 3. transport_req()
// -------------------------------------------------------------
// A. Normal render
list($e) = expense_b4_run('transport_req');
check($e['status'] === 1, 'transport_req: status 1');
check($e['context']['data']['outcome'] === 'rendered', 'transport_req: outcome rendered');
check($e['context']['data']['page'] === 'transport_req_list', 'transport_req: page matched');
check($ci->template->renderedView === 'transport_req_list', 'transport_req: template renderedView matched');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e) = expense_b4_run('transport_req', array($writer => true));
    check($e['status'] === 1, 'transport_req: unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 4. transport_req_create()
// -------------------------------------------------------------
// A. Normal render
list($e) = expense_b4_run('transport_req_create');
check($e['status'] === 1, 'transport_req_create: status 1');
check($e['context']['data']['outcome'] === 'rendered', 'transport_req_create: outcome rendered');
check($e['context']['data']['form'] === 'transport_req_form', 'transport_req_create: form matched');
check($e['context']['data']['item_count'] === 2, 'transport_req_create: item_count 2');
check($ci->template->renderedView === 'transport_req_form', 'transport_req_create: template renderedView matched');

// B. Empty COA
list($e) = expense_b4_run('transport_req_create', array('empty_coa' => true));
check($e['status'] === 1, 'transport_req_create (empty): status 1');
check($e['context']['data']['outcome'] === 'rendered', 'transport_req_create (empty): outcome rendered');
check($e['context']['data']['item_count'] === 0, 'transport_req_create (empty): item_count 0');

// C. Query failure
list($e) = expense_b4_run('transport_req_create', array('fail_query' => true));
check($e['status'] === 0, 'transport_req_create (query fail): status 0');
check($e['context']['data']['outcome'] === 'query_error', 'transport_req_create (query fail): outcome query_error');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e) = expense_b4_run('transport_req_create', array($writer => true));
    check($e['status'] === 1, 'transport_req_create: unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 5. transport_req_save()
// -------------------------------------------------------------
$samplePost = array(
    'tgl_doc' => '2026-10-15',
    'departement' => 'DEP-FIN',
    'date1' => '2026-10-01',
    'date2' => '2026-10-15',
    'id_transport' => array('1'),
    'jumlah_expense' => '180.000',
    'bank_id' => 'BCA',
    'accnumber' => '1234567890',
    'accname' => 'Budi Santoso',
    'bensin' => array('100.000'),
    'tol' => array('50.000'),
    'parkir' => array('20.000'),
    'lainnya' => array('10.000'),
    'km_awal' => array('10.000'),
    'km_akhir' => array('10.050'),
    'coa' => array('1304-01-01'),
    'nm_coa' => array('Bensin & Tol'),
    'keperluan' => array('Meeting'),
    'rute' => array('Jakarta - Bekasi'),
    'nopol' => array('B 1234 CD'),
    'tgl_doc_detail' => array('15/10/2026')
);

// A. Create new transport request ($id == '')
list($e, $out) = expense_b4_run('transport_req_save', array(), array_merge($samplePost, array('id' => '', 'no_doc' => '')));
check($e['status'] === 1, 'transport_req_save (create): status 1');
check($e['context']['data']['outcome'] === 'created', 'transport_req_save (create): outcome created');
check($e['context']['data']['amount'] == 180000, 'transport_req_save (create): amount cleaned');
check($e['context']['data']['departement'] === 'DEP-FIN', 'transport_req_save (create): departement matched');
$resp = json_decode($out, true);
check($resp['save'] == 1, 'transport_req_save (create): json save 1');
check($resp['id'] == 101, 'transport_req_save (create): json id 101');

// B. Update existing transport request ($id != '')
list($e, $out) = expense_b4_run('transport_req_save', array(), array_merge($samplePost, array(
    'id' => '10',
    'no_doc' => 'TR-REQ-2026-001',
    'removed_transport' => array('2')
)));
check($e['status'] === 1, 'transport_req_save (update): status 1');
check($e['context']['data']['outcome'] === 'updated', 'transport_req_save (update): outcome updated');
check($e['context']['data']['id'] == 10, 'transport_req_save (update): id matched');
check($e['context']['data']['no_doc'] === 'TR-REQ-2026-001', 'transport_req_save (update): no_doc matched');
$resp = json_decode($out, true);
check($resp['save'] == 1, 'transport_req_save (update): json save 1');
check($resp['id'] == 10, 'transport_req_save (update): json id 10');

// C. File upload handling
$_FILES['doc_file_1'] = array(
    'name' => 'struk.jpg',
    'type' => 'image/jpeg',
    'tmp_name' => sys_get_temp_dir() . '/tmp_struk.jpg',
    'error' => 0,
    'size' => 2048
);
list($e, $out) = expense_b4_run('transport_req_save', array(), array_merge($samplePost, array(
    'id' => '10',
    'no_doc' => 'TR-REQ-2026-001'
)));
check($e['status'] === 1, 'transport_req_save (upload): status 1');
unset($_FILES['doc_file_1']);

// D. Transaction status failure
list($e, $out) = expense_b4_run('transport_req_save', array('transaction_failure' => true), array_merge($samplePost, array('id' => '')));
check($e['status'] === 0, 'transport_req_save (trans fail): status 0');
check($e['context']['data']['outcome'] === 'transaction_failed', 'transport_req_save (trans fail): outcome transaction_failed');
$resp = json_decode($out, true);
check($resp['save'] == 0, 'transport_req_save (trans fail): json save 0');

// E. Transaction commit failure
list($e, $out) = expense_b4_run('transport_req_save', array('trans_commit_failure' => true), array_merge($samplePost, array('id' => '10')));
check($e['status'] === 0, 'transport_req_save (commit fail): status 0');
check($e['context']['data']['outcome'] === 'transaction_failed', 'transport_req_save (commit fail): outcome transaction_failed');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e, $out) = expense_b4_run('transport_req_save', array($writer => true), array_merge($samplePost, array('id' => '10')));
    check($e['status'] === 1, 'transport_req_save: unaffected by ' . $writer);
    $resp = json_decode($out, true);
    check($resp['save'] == 1, 'transport_req_save: business json response unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 6. transport_req_edit($id, $mod = '')
// -------------------------------------------------------------
// A. Normal found
list($e) = expense_b4_run('transport_req_edit', array(), array(), array(10, 'approval'));
check($e['status'] === 1, 'transport_req_edit: status 1');
check($e['context']['data']['outcome'] === 'rendered', 'transport_req_edit: outcome rendered');
check($e['context']['data']['id'] == 10, 'transport_req_edit: id matched');
check($e['context']['data']['no_doc'] === 'TR-REQ-2026-001', 'transport_req_edit: no_doc matched');
check($e['context']['data']['mod'] === 'approval', 'transport_req_edit: mod matched');
check($ci->template->renderedView === 'transport_req_form', 'transport_req_edit: template renderedView matched');

// B. Missing record
list($e) = expense_b4_run('transport_req_edit', array('missing_record' => true), array(), array(999));
check($e['status'] === 0, 'transport_req_edit (missing): status 0');
check($e['context']['data']['outcome'] === 'not_found', 'transport_req_edit (missing): outcome not_found');

// C. Query failure
list($e) = expense_b4_run('transport_req_edit', array('fail_query' => true), array(), array(10));
check($e['status'] === 0, 'transport_req_edit (query fail): status 0');
check($e['context']['data']['outcome'] === 'query_error', 'transport_req_edit (query fail): outcome query_error');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e) = expense_b4_run('transport_req_edit', array($writer => true), array(), array(10));
    check($e['status'] === 1, 'transport_req_edit: unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 7. transport_req_print($id)
// -------------------------------------------------------------
// A. Normal print
list($e) = expense_b4_run('transport_req_print', array(), array(), array(10));
check($e['status'] === 1, 'transport_req_print: status 1');
check($e['context']['data']['outcome'] === 'printed', 'transport_req_print: outcome printed');
check($e['context']['data']['id'] == 10, 'transport_req_print: id matched');
check($e['context']['data']['no_doc'] === 'TR-REQ-2026-001', 'transport_req_print: no_doc matched');
check($ci->load->viewLoaded === 'transport_req_print', 'transport_req_print: viewLoaded matched');
check($ci->load->viewData['dept_name'] === 'Finance & Accounting', 'transport_req_print: dept_name resolved');
check($ci->load->viewData['request_by'] === 'Budi Santoso', 'transport_req_print: request_by resolved');

// B. Missing record
list($e) = expense_b4_run('transport_req_print', array('missing_record' => true), array(), array(999));
check($e['status'] === 0, 'transport_req_print (missing): status 0');
check($e['context']['data']['outcome'] === 'not_found', 'transport_req_print (missing): outcome not_found');

// C. Query failure
list($e) = expense_b4_run('transport_req_print', array('fail_query' => true), array(), array(10));
check($e['status'] === 0, 'transport_req_print (query fail): status 0');
check($e['context']['data']['outcome'] === 'query_error', 'transport_req_print (query fail): outcome query_error');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e) = expense_b4_run('transport_req_print', array($writer => true), array(), array(10));
    check($e['status'] === 1, 'transport_req_print: unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 8. transport_req_view($id, $mod = '')
// -------------------------------------------------------------
// A. Normal view
list($e) = expense_b4_run('transport_req_view', array(), array(), array(10, 'view_mode'));
check($e['status'] === 1, 'transport_req_view: status 1');
check($e['context']['data']['outcome'] === 'rendered', 'transport_req_view: outcome rendered');
check($e['context']['data']['id'] == 10, 'transport_req_view: id matched');
check($e['context']['data']['no_doc'] === 'TR-REQ-2026-001', 'transport_req_view: no_doc matched');
check($e['context']['data']['mod'] === 'view_mode', 'transport_req_view: mod matched');
check($ci->template->renderedView === 'transport_req_form', 'transport_req_view: template renderedView matched');

// B. Missing record
list($e) = expense_b4_run('transport_req_view', array('missing_record' => true), array(), array(999));
check($e['status'] === 0, 'transport_req_view (missing): status 0');
check($e['context']['data']['outcome'] === 'not_found', 'transport_req_view (missing): outcome not_found');

// C. Query failure
list($e) = expense_b4_run('transport_req_view', array('fail_query' => true), array(), array(10));
check($e['status'] === 0, 'transport_req_view (query fail): status 0');
check($e['context']['data']['outcome'] === 'query_error', 'transport_req_view (query fail): outcome query_error');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e) = expense_b4_run('transport_req_view', array($writer => true), array(), array(10));
    check($e['status'] === 1, 'transport_req_view: unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 9. transport()
// -------------------------------------------------------------
// A. Normal render
list($e) = expense_b4_run('transport');
check($e['status'] === 1, 'transport: status 1');
check($e['context']['data']['outcome'] === 'rendered', 'transport: outcome rendered');
check($e['context']['data']['page'] === 'transport_list', 'transport: page matched');
check($ci->template->renderedView === 'transport_list', 'transport: template renderedView matched');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e) = expense_b4_run('transport', array($writer => true));
    check($e['status'] === 1, 'transport: unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 10. transport_create()
// -------------------------------------------------------------
// A. Normal render
list($e) = expense_b4_run('transport_create');
check($e['status'] === 1, 'transport_create: status 1');
check($e['context']['data']['outcome'] === 'rendered', 'transport_create: outcome rendered');
check($e['context']['data']['form'] === 'transport_form', 'transport_create: form matched');
check($e['context']['data']['item_count'] === 2, 'transport_create: item_count 2');
check($ci->template->renderedView === 'transport_form', 'transport_create: template renderedView matched');

// B. Empty COA
list($e) = expense_b4_run('transport_create', array('empty_coa' => true));
check($e['status'] === 1, 'transport_create (empty): status 1');
check($e['context']['data']['outcome'] === 'rendered', 'transport_create (empty): outcome rendered');
check($e['context']['data']['item_count'] === 0, 'transport_create (empty): item_count 0');

// C. Query failure
list($e) = expense_b4_run('transport_create', array('fail_query' => true));
check($e['status'] === 0, 'transport_create (query fail): status 0');
check($e['context']['data']['outcome'] === 'query_error', 'transport_create (query fail): outcome query_error');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e) = expense_b4_run('transport_create', array($writer => true));
    check($e['status'] === 1, 'transport_create: unaffected by ' . $writer);
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
        expense_b4_run($pendingMethod, array('pending' => true, 'expected_exception' => true), array(), $dummyArgs);
    }
}

// -------------------------------------------------------------
// 12. Subprocess Shutdown Runner
// -------------------------------------------------------------
$cmd = escapeshellcmd(PHP_BINARY) . ' ' . escapeshellarg(__FILE__) . ' --subprocess-shutdown 2>&1';
exec($cmd, $subOutput, $subExitCode);
check($subExitCode === 0, 'Subprocess shutdown exit code 0');

echo "PASS Expense batch 4: {$scenarioCount} fixture scenarios + 1 shutdown subprocess" . PHP_EOL;
