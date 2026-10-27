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

class ExpenseBatch2Result {
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

        if (strpos($this->table, 'tr_kasbon') !== false) {
            return (object) array(
                'id' => 10,
                'no_doc' => 'KB/2026/001',
                'tgl_doc' => '2026-10-14',
                'departement' => 'DEP-FIN',
                'keperluan' => 'Operasional Kantor',
                'keterangan' => 'Keterangan kasbon',
                'jumlah_kasbon' => '1500000',
                'no_coa' => '1304-01-01',
                'coa' => '1304-01-01'
            );
        }

        if (strpos($this->table, 'accessories') !== false) {
            return (object) array(
                'no_coa' => '1304-01-01',
                'nm_coa' => 'Peralatan Kantor'
            );
        }

        if (strpos($this->table, 'rutin_non_planning') !== false) {
            return (object) array(
                'coa' => '1304-01-01'
            );
        }

        if (strpos($this->table, 'coa_master') !== false) {
            return (object) array(
                'no_perkiraan' => '1304-01-01',
                'nama' => 'Peralatan Kantor'
            );
        }

        return (object) array(
            'id' => 1,
            'name' => 'Mock Row'
        );
    }

    public function result() {
        global $scenario;
        if (!empty($scenario['empty_result'])) {
            return array();
        }

        if (strpos($this->table, 'tr_expense_detail_file') !== false) {
            return array(
                (object) array('id_detail' => '1', 'file_name' => 'receipt.png')
            );
        }

        return array(
            (object) array('id' => 1)
        );
    }

    public function result_array() {
        global $scenario;
        if (!empty($scenario['empty_result'])) {
            return array();
        }

        if (strpos($this->table, 'tr_pr_detail_kasbon') !== false) {
            if (!empty($scenario['kasbon_umum'])) {
                return array();
            }
            return array(
                array(
                    'id' => 1,
                    'id_detail' => '10',
                    'id_material' => '20',
                    'tipe_pr' => !empty($scenario['tipe_pr']) ? $scenario['tipe_pr'] : 'pr stok',
                    'no_pr' => 'PR-STOK-001',
                    'nm_material' => 'Kertas A4',
                    'unit' => 1,
                    'qty' => 5,
                    'harga' => 50000,
                    'total_harga' => 250000
                )
            );
        }

        return array(
            array('id' => 1)
        );
    }
}

class ExpenseBatch2Db {
    public function order_by($key, $direction = '') { return $this; }
    public function group_start() { return $this; }
    public function group_end() { return $this; }
    public function or_where($key, $value = null) { return $this; }
    public function where_in($key, $values) { return $this; }
    public function not_like($key, $value, $side = '') { return $this; }
    public function count_all_results($table = '') { return 0; }
    public $whereData = array();
    public $selectFields = '';
    public $fromTable = '';
    public $joinData = array();
    public $calls = array();
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

    public function get() {
        global $scenario;
        if (!empty($scenario['fail_query'])) {
            return false;
        }
        return new ExpenseBatch2Result('get', $this->fromTable, $this->whereData);
    }

    public function get_where($table, $where = array()) {
        global $scenario;
        if (!empty($scenario['fail_query'])) {
            return false;
        }
        return new ExpenseBatch2Result('get_where', $table, $where);
    }

    public function table_exists($table) {
        global $scenario;
        if (!empty($scenario['no_detail_file_table'])) {
            return false;
        }
        return true;
    }

    public function query($sql) {
        global $scenario;
        if (!empty($scenario['fail_query'])) {
            return false;
        }
        $this->lastQuery = $sql;
        return new ExpenseBatch2Result('query', $sql);
    }

    public function insert($table, $data) {
        $this->lastQuery = "INSERT INTO $table";
        return true;
    }

    public function update($table, $data, $where = array()) {
        $this->lastQuery = "UPDATE $table";
        return true;
    }

    public function delete($table, $where = array()) {
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
}

class ExpenseBatch2Model {
    public function GetDataHeader($id) {
        global $scenario;
        if (!empty($scenario['missing_record'])) {
            return null;
        }

        $isReport = !empty($scenario['form_type']) && $scenario['form_type'] === 'report';
        return (object) array(
            'id' => $id,
            'no_doc' => 'EXP-2026-001',
            'id_kasbon' => $isReport ? 'KB/2026/001' : null,
            'total_kasbon' => $isReport ? 1000000 : 0,
            'status' => 1
        );
    }

    public function GetDataDetail($no_doc) {
        global $scenario;
        if (!empty($scenario['empty_detail'])) {
            return array();
        }

        return array(
            (object) array(
                'id' => 1,
                'deskripsi' => 'Pembelian ATK',
                'keterangan' => 'Nota 1',
                'tanggal' => '2026-10-15',
                'coa' => '1304-01-01',
                'qty' => 2,
                'harga' => 500000,
                'kasbon' => 1000000,
                'expense' => 1000000,
                'doc_file' => 'receipt.png',
                'id_expense_detail' => '10'
            )
        );
    }
}

class ExpenseBatch2AllModel {
    public function GetComboBudget($id = '', $type = 'EXPENSE', $year = '') {
        return array('BUDGET-01' => 'Budget Operasional');
    }

    public function GetListCoa($arr = array()) {
        return array('1304-01-01' => '1304-01-01 - Peralatan Kantor');
    }

    public function GetOneData($table, $where) {
        global $scenario;
        if (!empty($scenario['fail_generate']) || !empty($scenario['fail_query'])) {
            return false;
        }
        return (object) array(
            'info' => 'EXP-YEAR-XXXX',
            'info2' => 10,
            'info3' => '2026',
            'info4' => 4
        );
    }

    public function dataUpdate($table, $data, $where) {
        global $scenario;
        return empty($scenario['fail_update']);
    }

    public function dataDelete($table, $where) {
        global $scenario;
        return empty($scenario['fail_delete']);
    }
}

class ExpenseBatch2CoaModel {
    public function GetDataWithJenis($jenis) {
        return (object) array(
            'coa' => '1304-01-01;1304-01-02'
        );
    }
}

class ExpenseBatch2Template {
    public $vars = array();
    public $renderedView = '';
    public $viewTitle = '';

    public function set($key, $val = null) {
        $this->vars[$key] = $val;
    }

    public function title($title) {
        $this->viewTitle = $title;
    }

    public function page_icon($icon) {}

    public function render($view) {
        $this->renderedView = $view;
    }
}

class ExpenseBatch2Load {
    public $viewLoaded = '';
    public $viewData = array();

    public function view($view, $data = array()) {
        $this->viewLoaded = $view;
        $this->viewData = $data;
    }
}

class ExpenseBatch2Auth {
    public function is_admin() { return false; }
    public function user_name() {
        return 'tester';
    }
}

class ExpenseBatch2Input {
    public $values = array();

    public function post($key = null) {
        if ($key === null) {
            return $this->values;
        }
        return isset($this->values[$key]) ? $this->values[$key] : null;
    }
}

function expense_b2_run($method, $options = array(), $post = array(), $args = array()) {
    global $scenario, $events, $ci;
    $scenario = $options;
    $events = array();
    $GLOBALS['scenarioCount']++;

    $recorder = new Module_audit_recorder();

    $ci = (new ReflectionClass('Expense'))->newInstanceWithoutConstructor();
    $ci->module_audit_recorder = $recorder;
    $ci->db = new ExpenseBatch2Db();
    $ci->input = new ExpenseBatch2Input();
    $ci->auth = new ExpenseBatch2Auth();
    $ci->template = new ExpenseBatch2Template();
    $ci->load = new ExpenseBatch2Load();
    $ci->Expense_model = new ExpenseBatch2Model();
    $ci->All_model = new ExpenseBatch2AllModel();
    $ci->Coa_expense_model = new ExpenseBatch2CoaModel();

    $propStatus = new ReflectionProperty('Expense', 'status');
    $propStatus->setAccessible(true);
    $propStatus->setValue($ci, array("0" => "Waiting Approval Finance", "1" => "Approved", "2" => "Approved", "3" => "Approved", "9" => "Rejected"));

    $propManage = new ReflectionProperty('Expense', 'managePermission');
    $propManage->setAccessible(true);
    $propManage->setValue($ci, 'Expense.Manage');

    $ci->input->values = array_merge(array(
        'coa' => '1304-01-01',
        'nm_coa' => 'Peralatan Kantor',
        'password' => 'PRIVATE_PASSWORD',
        'credit_card' => 'PRIVATE_CC',
        'doc_file' => 'binary_blob'
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
    check($events[0]['context']['methode'] === $method, 'Wrong methode attribution: ' . ($events[0]['context']['methode'] ?? 'none'));
    check(strpos(json_encode($events), 'PRIVATE_PASSWORD') === false, 'PII leaked in audit event');
    check(strpos(json_encode($events), 'PRIVATE_CC') === false, 'PII leaked in audit event');
    check(strpos(json_encode($events), 'binary_blob') === false, 'Binary attachment leaked in audit event');

    return array($events[0], $output, $ci, $caughtException);
}

// -------------------------------------------------------------
// Subprocess Runner for Shutdown Handler Coverage
// -------------------------------------------------------------
if (in_array('--subprocess-shutdown', $argv, true)) {
    expense_b2_run('kasbon_approve', array('abort_commit' => true), array(), array(10));
    exit(0);
}
if (defined('EXPENSE_B2_FIXTURE_ONLY')) { return; }

// =============================================================
// Main Test Execution for Batch 2 (10 Endpoints)
// =============================================================
$scenarioCount = 0;

// -------------------------------------------------------------
// 1. kasbon_fin()
// -------------------------------------------------------------
list($e) = expense_b2_run('kasbon_fin');
check($e['status'] === 1, 'kasbon_fin: status 1');
check($e['context']['data']['outcome'] === 'rendered', 'kasbon_fin: outcome rendered');
check($ci->template->renderedView === 'kasbon_list_fin', 'kasbon_fin: renderedView correct');
foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e) = expense_b2_run('kasbon_fin', array($writer => true));
    check($e['status'] === 1, 'kasbon_fin: unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 2. kasbon_approve($id = '')
// -------------------------------------------------------------
// A. Normal approve with ID
list($e, $out) = expense_b2_run('kasbon_approve', array(), array('coa' => '1304-01-01', 'nm_coa' => 'Peralatan'), array(15));
check($e['status'] === 1, 'kasbon_approve: status 1');
check($e['context']['data']['outcome'] === 'approved', 'kasbon_approve: outcome approved');
check($e['context']['data']['no_coa'] === '1304-01-01', 'kasbon_approve: no_coa matched');
check($e['context']['data']['sts_finance'] === '1', 'kasbon_approve: sts_finance 1');
$resp = json_decode($out, true);
check(!empty($resp['save']), 'kasbon_approve: save true');

// B. Empty ID
list($e, $out) = expense_b2_run('kasbon_approve', array(), array(), array(''));
check($e['status'] === 0, 'kasbon_approve (empty id): status 0');
check($e['context']['data']['outcome'] === 'empty_id', 'kasbon_approve (empty id): outcome empty_id');
$resp = json_decode($out, true);
check(empty($resp['save']), 'kasbon_approve (empty id): save false');

// C. Update failure
list($e, $out) = expense_b2_run('kasbon_approve', array('fail_update' => true), array(), array(15));
check($e['status'] === 0, 'kasbon_approve (fail update): status 0');
check($e['context']['data']['outcome'] === 'update_failed', 'kasbon_approve (fail update): outcome update_failed');
$resp = json_decode($out, true);
check(empty($resp['save']), 'kasbon_approve (fail update): save false');

// D. Writer immunity
foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e, $out) = expense_b2_run('kasbon_approve', array($writer => true), array(), array(15));
    $resp = json_decode($out, true);
    check(!empty($resp['save']), 'kasbon_approve: business response unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 3. kasbon_approve_manage($id = '')
// -------------------------------------------------------------
// A. Normal approve with ID
list($e, $out) = expense_b2_run('kasbon_approve_manage', array(), array(), array(20));
check($e['status'] === 1, 'kasbon_approve_manage: status 1');
check($e['context']['data']['outcome'] === 'approved', 'kasbon_approve_manage: outcome approved');
check($e['context']['data']['status'] === 1, 'kasbon_approve_manage: status 1 matched');
$resp = json_decode($out, true);
check(!empty($resp['save']), 'kasbon_approve_manage: save true');

// B. Empty ID
list($e, $out) = expense_b2_run('kasbon_approve_manage', array(), array(), array(''));
check($e['status'] === 0, 'kasbon_approve_manage (empty id): status 0');
check($e['context']['data']['outcome'] === 'empty_id', 'kasbon_approve_manage (empty id): outcome empty_id');
$resp = json_decode($out, true);
check(empty($resp['save']), 'kasbon_approve_manage (empty id): save false');

// C. Update failure
list($e, $out) = expense_b2_run('kasbon_approve_manage', array('fail_update' => true), array(), array(20));
check($e['status'] === 0, 'kasbon_approve_manage (fail update): status 0');
check($e['context']['data']['outcome'] === 'update_failed', 'kasbon_approve_manage (fail update): outcome update_failed');
$resp = json_decode($out, true);
check(empty($resp['save']), 'kasbon_approve_manage (fail update): save false');

// D. Writer immunity
foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e, $out) = expense_b2_run('kasbon_approve_manage', array($writer => true), array(), array(20));
    $resp = json_decode($out, true);
    check(!empty($resp['save']), 'kasbon_approve_manage: business response unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 4. kasbon_delete($id)
// -------------------------------------------------------------
// A. Normal delete existing
list($e, $out) = expense_b2_run('kasbon_delete', array(), array(), array(25));
check($e['status'] === 1, 'kasbon_delete: status 1');
check($e['context']['data']['outcome'] === 'deleted', 'kasbon_delete: outcome deleted');
check($e['context']['data']['no_doc'] === 'KB/2026/001', 'kasbon_delete: no_doc matched');
$resp = json_decode($out, true);
check(!empty($resp['delete']), 'kasbon_delete: delete true');

// B. Missing kasbon record
list($e, $out) = expense_b2_run('kasbon_delete', array('missing_record' => true), array(), array(999));
check($e['status'] === 0, 'kasbon_delete (missing): status 0');
check($e['context']['data']['outcome'] === 'not_found', 'kasbon_delete (missing): outcome not_found');
$resp = json_decode($out, true);
check(empty($resp['delete']), 'kasbon_delete (missing): delete false');

// C. Delete failure
list($e, $out) = expense_b2_run('kasbon_delete', array('fail_delete' => true), array(), array(25));
check($e['status'] === 0, 'kasbon_delete (fail delete): status 0');
check($e['context']['data']['outcome'] === 'delete_failed', 'kasbon_delete (fail delete): outcome delete_failed');
$resp = json_decode($out, true);
check(empty($resp['delete']), 'kasbon_delete (fail delete): delete false');

// D. Transaction failure
list($e, $out) = expense_b2_run('kasbon_delete', array('transaction_failure' => true), array(), array(25));
check($e['status'] === 0, 'kasbon_delete (tx fail): status 0');
check($e['context']['data']['outcome'] === 'delete_failed', 'kasbon_delete (tx fail): outcome delete_failed');

// E. Writer immunity
foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e, $out) = expense_b2_run('kasbon_delete', array($writer => true), array(), array(25));
    $resp = json_decode($out, true);
    check(!empty($resp['delete']), 'kasbon_delete: business response unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 5. index()
// -------------------------------------------------------------
list($e) = expense_b2_run('index');
check($e['status'] === 1, 'index: status 1');
check($e['context']['data']['outcome'] === 'rendered', 'index: outcome rendered');
check($ci->template->renderedView === 'index', 'index: renderedView index');
foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e) = expense_b2_run('index', array($writer => true));
    check($e['status'] === 1, 'index: unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 6. create()
// -------------------------------------------------------------
list($e) = expense_b2_run('create');
check($e['status'] === 1, 'create: status 1');
check($e['context']['data']['outcome'] === 'rendered', 'create: outcome rendered');
check($e['context']['data']['pre_id'] === 'EXP-2026-0011', 'create: pre_id matched');
check($ci->template->renderedView === 'form', 'create: renderedView form');

// Query error during create
list($e) = expense_b2_run('create', array('fail_query' => true));
check($e['status'] === 0, 'create (query fail): status 0');
check($e['context']['data']['outcome'] === 'query_error', 'create (query fail): outcome query_error');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e) = expense_b2_run('create', array($writer => true));
    check($e['status'] === 1, 'create: unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 7. create_report($id_kasbon = '')
// -------------------------------------------------------------
// A. With PR items
list($e) = expense_b2_run('create_report', array(), array(), array('KB/2026/001'));
check($e['status'] === 1, 'create_report (PR items): status 1');
check($e['context']['data']['outcome'] === 'rendered', 'create_report (PR items): outcome rendered');
check($e['context']['data']['item_count'] > 0, 'create_report (PR items): item_count > 0');
check($ci->template->renderedView === 'form_report', 'create_report: renderedView form_report');

// B. Kasbon umum (tanpa detail PR)
list($e) = expense_b2_run('create_report', array('kasbon_umum' => true), array(), array('KB/2026/001'));
check($e['status'] === 1, 'create_report (kasbon umum): status 1');
check($e['context']['data']['item_count'] === 1, 'create_report (kasbon umum): item_count 1');

// C. PR Departemen tipe
list($e) = expense_b2_run('create_report', array('tipe_pr' => 'pr departemen'), array(), array('KB/2026/001'));
check($e['status'] === 1, 'create_report (dept): status 1');

// D. Query error
list($e) = expense_b2_run('create_report', array('fail_query' => true), array(), array('KB/2026/001'));
check($e['status'] === 0, 'create_report (query fail): status 0');
check($e['context']['data']['outcome'] === 'query_error', 'create_report (query fail): outcome query_error');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e) = expense_b2_run('create_report', array($writer => true), array(), array('KB/2026/001'));
    check($e['status'] === 1, 'create_report: unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 8. edit($id)
// -------------------------------------------------------------
// A. Direct expense edit
list($e) = expense_b2_run('edit', array('form_type' => 'direct'), array(), array(30));
check($e['status'] === 1, 'edit (direct): status 1');
check($e['context']['data']['outcome'] === 'rendered', 'edit (direct): outcome rendered');
check($e['context']['data']['form_type'] === 'direct', 'edit (direct): form_type direct');
check($ci->template->renderedView === 'form', 'edit (direct): view form');

// B. Expense report edit (with kasbon)
list($e) = expense_b2_run('edit', array('form_type' => 'report'), array(), array(30));
check($e['status'] === 1, 'edit (report): status 1');
check($e['context']['data']['outcome'] === 'rendered', 'edit (report): outcome rendered');
check($e['context']['data']['form_type'] === 'report', 'edit (report): form_type report');
check($ci->template->renderedView === 'form_report', 'edit (report): view form_report');

// C. Missing record
list($e) = expense_b2_run('edit', array('missing_record' => true), array(), array(999));
check($e['status'] === 0, 'edit (missing): status 0');
check($e['context']['data']['outcome'] === 'not_found', 'edit (missing): outcome not_found');

// D. Query failure
list($e) = expense_b2_run('edit', array('fail_query' => true), array(), array(30));
check($e['status'] === 0, 'edit (query fail): status 0');
check($e['context']['data']['outcome'] === 'query_error', 'edit (query fail): outcome query_error');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e) = expense_b2_run('edit', array($writer => true), array(), array(30));
    check($e['status'] === 1, 'edit: unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 9. view($id)
// -------------------------------------------------------------
// A. Direct expense view
list($e) = expense_b2_run('view', array('form_type' => 'direct'), array(), array(35));
check($e['status'] === 1, 'view (direct): status 1');
check($e['context']['data']['outcome'] === 'rendered', 'view (direct): outcome rendered');
check($e['context']['data']['form_type'] === 'direct', 'view (direct): form_type direct');
check($ci->template->renderedView === 'form', 'view (direct): view form');

// B. Expense report view
list($e) = expense_b2_run('view', array('form_type' => 'report'), array(), array(35));
check($e['status'] === 1, 'view (report): status 1');
check($e['context']['data']['outcome'] === 'rendered', 'view (report): outcome rendered');
check($e['context']['data']['form_type'] === 'report', 'view (report): form_type report');
check($ci->template->renderedView === 'form_report', 'view (report): view form_report');

// C. Missing record
list($e) = expense_b2_run('view', array('missing_record' => true), array(), array(999));
check($e['status'] === 0, 'view (missing): status 0');
check($e['context']['data']['outcome'] === 'not_found', 'view (missing): outcome not_found');

// D. Query failure
list($e) = expense_b2_run('view', array('fail_query' => true), array(), array(35));
check($e['status'] === 0, 'view (query fail): status 0');
check($e['context']['data']['outcome'] === 'query_error', 'view (query fail): outcome query_error');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e) = expense_b2_run('view', array($writer => true), array(), array(35));
    check($e['status'] === 1, 'view: unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 10. expense_print($id)
// -------------------------------------------------------------
// A. Normal print
list($e) = expense_b2_run('expense_print', array(), array(), array(40));
check($e['status'] === 1, 'expense_print: status 1');
check($e['context']['data']['outcome'] === 'printed', 'expense_print: outcome printed');
check($e['context']['data']['no_doc'] === 'EXP-2026-001', 'expense_print: no_doc matched');
check($ci->load->viewLoaded === 'expense_print', 'expense_print: viewLoaded expense_print');

// B. Missing record
list($e) = expense_b2_run('expense_print', array('missing_record' => true), array(), array(999));
check($e['status'] === 0, 'expense_print (missing): status 0');
check($e['context']['data']['outcome'] === 'not_found', 'expense_print (missing): outcome not_found');

// C. Query failure
list($e) = expense_b2_run('expense_print', array('fail_query' => true), array(), array(40));
check($e['status'] === 0, 'expense_print (query fail): status 0');
check($e['context']['data']['outcome'] === 'query_error', 'expense_print (query fail): outcome query_error');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e) = expense_b2_run('expense_print', array($writer => true), array(), array(40));
    check($e['status'] === 1, 'expense_print: unaffected by ' . $writer);
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
        expense_b2_run($pendingMethod, array('pending' => true, 'expected_exception' => true), array(), $dummyArgs);
    }
}

// -------------------------------------------------------------
// 12. Subprocess Shutdown Runner
// -------------------------------------------------------------
$cmd = escapeshellcmd(PHP_BINARY) . ' ' . escapeshellarg(__FILE__) . ' --subprocess-shutdown 2>&1';
exec($cmd, $subOutput, $subExitCode);
check($subExitCode === 0, 'Subprocess shutdown exit code 0');

echo "PASS Expense batch 2: {$scenarioCount} fixture scenarios + 1 shutdown subprocess" . PHP_EOL;
