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

class ExpenseBatch3Result {
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

        if (strpos($this->table, 'tr_expense') !== false) {
            $sts_finance = isset($scenario['sts_finance']) ? $scenario['sts_finance'] : '0';
            return (object) array(
                'id' => 10,
                'no_doc' => 'EXP-2026-001',
                'sts_finance' => $sts_finance,
                'status' => $sts_finance === '1' ? 1 : 0,
                'total_kasbon' => 0,
                'id_kasbon' => !empty($scenario['id_kasbon']) ? $scenario['id_kasbon'] : null,
                'kurang_bayar' => !empty($scenario['kurang_bayar']) ? $scenario['kurang_bayar'] : null,
                'lebih_bayar' => !empty($scenario['lebih_bayar']) ? $scenario['lebih_bayar'] : null,
                'pettycash' => !empty($scenario['pettycash']) ? $scenario['pettycash'] : null,
                'jumlah' => 500000
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
            'doc_file' => 'receipt.png',
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
            'doc_file' => 'receipt.png'
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

        if (strpos($this->table, 'tr_expense_detail') !== false) {
            return array(
                (object) array(
                    'id' => 1,
                    'no_doc' => 'EXP-2026-001',
                    'expense' => 500000,
                    'kasbon_pr_non_po_pett' => !empty($scenario['has_kasbon_non_po']) ? 'KB-001' : null,
                    'id_expense_bayar_sisa' => !empty($scenario['has_kembalian']) ? 'EXP-KEMB-001' : null
                )
            );
        }

        return array(
            (object) array('id' => 1, 'nominal' => 500000, 'no_doc' => 'EXP-2026-001')
        );
    }

    public function result_array() {
        global $scenario;
        if (!empty($scenario['empty_result'])) {
            return array();
        }
        return array(
            array('id' => 1, 'doc_file' => 'receipt.png')
        );
    }
}

class ExpenseBatch3Db {
    public function group_start() { return $this; }
    public function group_end() { return $this; }
    public function or_where($key, $value = null) { return $this; }
    public function not_like($key, $value, $side = '') { return $this; }
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
        return new ExpenseBatch3Result('get', $this->fromTable, $this->whereData);
    }

    public function get_where($table, $where = array()) {
        global $scenario;
        if (!empty($scenario['fail_query'])) {
            return false;
        }
        if (!empty($scenario['missing_record'])) {
            return new ExpenseBatch3Result('get_where', $table, array('empty_result' => true));
        }
        return new ExpenseBatch3Result('get_where', $table, $where);
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
        return new ExpenseBatch3Result('query', $sql);
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
        return 88;
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
        if (!empty($scenario['fail_query'])) {
            return array('code' => 500, 'message' => 'Simulated DB error');
        }
        return array('code' => 0, 'message' => '');
    }
}

class ExpenseBatch3Model {
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
                'harga' => 250000,
                'kasbon' => 500000,
                'expense' => 500000,
                'doc_file' => 'receipt.png',
                'id_expense_detail' => '10'
            )
        );
    }

    public function update_batch($data, $key) {
        global $scenario;
        if (!empty($scenario['fail_update'])) {
            return false;
        }
        return true;
    }

    public function GetBudget($coa, $tahun) {
        global $scenario;
        if (!empty($scenario['fail_query'])) {
            return false;
        }
        if (!empty($scenario['budget_not_found'])) {
            return false;
        }
        return (object) array(
            'coa' => $coa,
            'tahun' => $tahun,
            'bulan_1' => 1000000,
            'bulan_2' => 1000000,
            'bulan_3' => 1000000,
            'bulan_4' => 1000000,
            'bulan_5' => 1000000,
            'bulan_6' => 1000000,
            'bulan_7' => 1000000,
            'bulan_8' => 1000000,
            'bulan_9' => 1000000,
            'bulan_10' => 1000000,
            'bulan_11' => 1000000,
            'bulan_12' => 1000000,
            'terpakai_bulan_1' => 200000,
            'terpakai_bulan_2' => 200000,
            'terpakai_bulan_3' => 200000,
            'terpakai_bulan_4' => 200000,
            'terpakai_bulan_5' => 200000,
            'terpakai_bulan_6' => 200000,
            'terpakai_bulan_7' => 200000,
            'terpakai_bulan_8' => 200000,
            'terpakai_bulan_9' => 200000,
            'terpakai_bulan_10' => 200000,
            'terpakai_bulan_11' => 200000,
            'terpakai_bulan_12' => 200000
        );
    }

    public function GetListDataTransportRequest($username = '') {
        global $scenario;
        if (!empty($scenario['fail_query'])) {
            return false;
        }
        if (!empty($scenario['empty_transport'])) {
            return array();
        }
        return array(
            (object) array(
                'id' => 1,
                'no_doc' => 'TR-2026-001',
                'created_by' => $username,
                'status' => '0'
            )
        );
    }
}

class ExpenseBatch3AllModel {
    public function GetComboBudget($id = '', $type = 'EXPENSE', $year = '') {
        return array('BUDGET-01' => 'Budget Operasional');
    }

    public function GetListCoa($arr = array()) {
        return array('1304-01-01' => '1304-01-01 - Peralatan Kantor');
    }

    public function GetAutoGenerate($format) {
        return 'EXP/2026/10/0001';
    }

    public function dataSave($table, $data) {
        global $scenario;
        return empty($scenario['fail_save']);
    }

    public function dataDelete($table, $where) {
        global $scenario;
        return empty($scenario['fail_delete']);
    }
}

class ExpenseBatch3CoaModel {
    public function GetDataWithJenis($jenis) {
        return (object) array(
            'coa' => '1304-01-01;1304-01-02'
        );
    }
}

class ExpenseBatch3Template {
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

class ExpenseBatch3Load {
    public $viewLoaded = '';
    public $viewData = array();

    public function view($view, $data = array()) {
        $this->viewLoaded = $view;
        $this->viewData = $data;
    }

    public function library($lib, $config = array()) {}
}

class ExpenseBatch3Upload {
    public function initialize($config = array()) {}
    public function do_upload($field = 'file') {
        global $scenario;
        return empty($scenario['upload_failure']);
    }
    public function data() {
        return array('file_name' => 'uploaded_file.jpg');
    }
    public function display_errors($open = '', $close = '') {
        return 'Mock upload error';
    }
}

class ExpenseBatch3Auth {
    public function user_name() {
        return 'tester';
    }
    public function user_id() {
        return 1;
    }
}

class ExpenseBatch3Input {
    public $values = array();

    public function post($key = null) {
        if ($key === null) {
            return $this->values;
        }
        return isset($this->values[$key]) ? $this->values[$key] : null;
    }
}

function expense_b3_run($method, $options = array(), $post = array(), $args = array()) {
    global $scenario, $events, $ci;
    $scenario = $options;
    $events = array();
    $GLOBALS['scenarioCount']++;

    $recorder = new Module_audit_recorder();

    $ci = (new ReflectionClass('Expense'))->newInstanceWithoutConstructor();
    $ci->module_audit_recorder = $recorder;
    $ci->db = new ExpenseBatch3Db();
    $ci->input = new ExpenseBatch3Input();
    $ci->auth = new ExpenseBatch3Auth();
    $ci->template = new ExpenseBatch3Template();
    $ci->load = new ExpenseBatch3Load();
    $ci->upload = new ExpenseBatch3Upload();
    $ci->Expense_model = new ExpenseBatch3Model();
    $ci->All_model = new ExpenseBatch3AllModel();
    $ci->Coa_expense_model = new ExpenseBatch3CoaModel();

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
        'doc_file' => 'binary_blob',
        'informasi' => 'Pengeluaran operasional',
        'total_expense' => 500000,
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

    return array($event, $output, $ci);
}

// Subprocess entrypoint
if (in_array('--subprocess-shutdown', $argv ?? array(), true)) {
    list($e, $out) = expense_b3_run('list_expense_approval', array('writer_exception' => true));
    exit($e['status'] === 1 ? 0 : 1);
}

// -------------------------------------------------------------
// 1. expense_pettycash_print($id)
// -------------------------------------------------------------
// A. Normal print
list($e) = expense_b3_run('expense_pettycash_print', array(), array(), array(10));
check($e['status'] === 1, 'expense_pettycash_print: status 1');
check($e['context']['data']['outcome'] === 'printed', 'expense_pettycash_print: outcome printed');
check($e['context']['data']['no_doc'] === 'EXP-2026-001', 'expense_pettycash_print: no_doc matched');
check($ci->load->viewLoaded === 'expense_pettycash_print', 'expense_pettycash_print: viewLoaded correct');

// B. Missing record
list($e) = expense_b3_run('expense_pettycash_print', array('missing_record' => true), array(), array(999));
check($e['status'] === 0, 'expense_pettycash_print (missing): status 0');
check($e['context']['data']['outcome'] === 'not_found', 'expense_pettycash_print (missing): outcome not_found');

// C. Query failure
list($e) = expense_b3_run('expense_pettycash_print', array('fail_query' => true), array(), array(10));
check($e['status'] === 0, 'expense_pettycash_print (query fail): status 0');
check($e['context']['data']['outcome'] === 'query_error', 'expense_pettycash_print (query fail): outcome query_error');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e) = expense_b3_run('expense_pettycash_print', array($writer => true), array(), array(10));
    check($e['status'] === 1, 'expense_pettycash_print: unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 2. list_expense_approval()
// -------------------------------------------------------------
list($e) = expense_b3_run('list_expense_approval');
check($e['status'] === 1, 'list_expense_approval: status 1');
check($e['context']['data']['outcome'] === 'rendered', 'list_expense_approval: outcome rendered');
check($ci->template->renderedView === 'index_approval', 'list_expense_approval: renderedView index_approval');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e) = expense_b3_run('list_expense_approval', array($writer => true));
    check($e['status'] === 1, 'list_expense_approval: unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 3. list_expense_approval_manage()
// -------------------------------------------------------------
list($e) = expense_b3_run('list_expense_approval_manage');
check($e['status'] === 1, 'list_expense_approval_manage: status 1');
check($e['context']['data']['outcome'] === 'rendered', 'list_expense_approval_manage: outcome rendered');
check($e['context']['data']['item_count'] >= 1, 'list_expense_approval_manage: item_count >= 1');
check($ci->template->renderedView === 'index_approval_manage', 'list_expense_approval_manage: renderedView index_approval_manage');

// B. Query failure
list($e) = expense_b3_run('list_expense_approval_manage', array('fail_query' => true));
check($e['status'] === 0, 'list_expense_approval_manage (query fail): status 0');
check($e['context']['data']['outcome'] === 'query_error', 'list_expense_approval_manage (query fail): outcome query_error');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e) = expense_b3_run('list_expense_approval_manage', array($writer => true));
    check($e['status'] === 1, 'list_expense_approval_manage: unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 4. expense_list_all()
// -------------------------------------------------------------
list($e) = expense_b3_run('expense_list_all');
check($e['status'] === 1, 'expense_list_all: status 1');
check($e['context']['data']['outcome'] === 'rendered', 'expense_list_all: outcome rendered');
check($e['context']['data']['all'] === 1, 'expense_list_all: all == 1');
check($ci->template->renderedView === 'index', 'expense_list_all: renderedView index');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e) = expense_b3_run('expense_list_all', array($writer => true));
    check($e['status'] === 1, 'expense_list_all: unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 5. approval($id)
// -------------------------------------------------------------
// A. Direct form
list($e) = expense_b3_run('approval', array('form_type' => 'direct'), array(), array(20));
check($e['status'] === 1, 'approval (direct): status 1');
check($e['context']['data']['outcome'] === 'rendered', 'approval (direct): outcome rendered');
check($e['context']['data']['form_type'] === 'direct', 'approval (direct): form_type direct');
check($ci->template->renderedView === 'form', 'approval (direct): renderedView form');

// B. Report form
list($e) = expense_b3_run('approval', array('form_type' => 'report'), array(), array(21));
check($e['status'] === 1, 'approval (report): status 1');
check($e['context']['data']['outcome'] === 'rendered', 'approval (report): outcome rendered');
check($e['context']['data']['form_type'] === 'report', 'approval (report): form_type report');
check($ci->template->renderedView === 'form_report', 'approval (report): renderedView form_report');

// C. Missing record
list($e) = expense_b3_run('approval', array('missing_record' => true), array(), array(999));
check($e['status'] === 0, 'approval (missing): status 0');
check($e['context']['data']['outcome'] === 'not_found', 'approval (missing): outcome not_found');

// D. Query failure
list($e) = expense_b3_run('approval', array('fail_query' => true), array(), array(20));
check($e['status'] === 0, 'approval (query fail): status 0');
check($e['context']['data']['outcome'] === 'query_error', 'approval (query fail): outcome query_error');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e) = expense_b3_run('approval', array($writer => true), array(), array(20));
    check($e['status'] === 1, 'approval: unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 6. approve($id)
// -------------------------------------------------------------
// A. Finance stage (sts_finance == 0)
list($e, $out) = expense_b3_run('approve', array('sts_finance' => '0'), array(), array(30));
check($e['status'] === 1, 'approve (finance): status 1');
check($e['context']['data']['stage'] === 'finance', 'approve (finance): stage finance');
check($e['context']['data']['outcome'] === 'approved', 'approve (finance): outcome approved');
$resp = json_decode($out, true);
check(!empty($resp['save']), 'approve (finance): save response true');

// B. Management stage (sts_finance == 1)
list($e, $out) = expense_b3_run('approve', array('sts_finance' => '1'), array(), array(30));
check($e['status'] === 1, 'approve (management): status 1');
check($e['context']['data']['stage'] === 'management', 'approve (management): stage management');
check($e['context']['data']['outcome'] === 'approved', 'approve (management): outcome approved');

// C. Empty ID
list($e, $out) = expense_b3_run('approve', array(), array(), array(''));
check($e['status'] === 0, 'approve (empty ID): status 0');
check($e['context']['data']['outcome'] === 'empty_id', 'approve (empty ID): outcome empty_id');

// D. Missing record
list($e, $out) = expense_b3_run('approve', array('missing_record' => true), array(), array(999));
check($e['status'] === 0, 'approve (missing): status 0');
check($e['context']['data']['outcome'] === 'not_found', 'approve (missing): outcome not_found');

// E. Update failure
list($e, $out) = expense_b3_run('approve', array('fail_update' => true, 'sts_finance' => '0'), array(), array(30));
check($e['status'] === 0, 'approve (update fail): status 0');
check($e['context']['data']['outcome'] === 'update_failed', 'approve (update fail): outcome update_failed');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e) = expense_b3_run('approve', array($writer => true, 'sts_finance' => '0'), array(), array(30));
    check($e['status'] === 1, 'approve: unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 7. save()
// -------------------------------------------------------------
// A. Validation failure: petty cash missing bon bukti
list($e, $out) = expense_b3_run('save', array(), array(
    'pettycash' => '1',
    'detail_id' => array('1'),
    'id_kasbon' => array(''),
    'filename' => array('')
));
check($e['status'] === 0, 'save (val bon bukti): status 0');
check($e['context']['data']['outcome'] === 'validation_failed', 'save (val bon bukti): outcome validation_failed');
check($e['context']['data']['reason'] === 'bon_bukti_required', 'save (val bon bukti): reason bon_bukti_required');

// B. Bukti pengembalian diunggah setelah approval, bukan saat laporan awal.
list($e, $out) = expense_b3_run('save', array(), array(
    'total_kasbon' => 1000000,
    'total_expense' => 500000,
    'existing_bukti_pengembalian' => array()
));
check($e['status'] === 1, 'save (deferred bukti transfer): status 1');
check($e['context']['data']['outcome'] === 'created', 'save (deferred bukti transfer): outcome created');
check(json_decode($out, true)['save'] === true, 'save (deferred bukti transfer): laporan tersimpan sebelum upload pengembalian');

// C. Success create new expense ($id == '')
list($e, $out) = expense_b3_run('save', array(), array(
    'id' => '',
    'no_doc' => '',
    'departement' => 'DEP-FIN',
    'tgl_doc' => '2026-10-15',
    'total_kasbon' => 0,
    'total_expense' => 500000
));
check($e['status'] === 1, 'save (create): status 1');
check($e['context']['data']['outcome'] === 'created', 'save (create): outcome created');
check($e['context']['data']['amount'] == 500000, 'save (create): amount matched');

// D. Success update existing expense ($id != '')
list($e, $out) = expense_b3_run('save', array(), array(
    'id' => '50',
    'no_doc' => 'EXP-2026-001',
    'departement' => 'DEP-FIN',
    'tgl_doc' => '2026-10-15',
    'total_kasbon' => 0,
    'total_expense' => 500000
));
check($e['status'] === 1, 'save (update): status 1');
check($e['context']['data']['outcome'] === 'updated', 'save (update): outcome updated');

// E. Upload failure in update branch
list($e, $out) = expense_b3_run('save', array('upload_failure' => true), array(
    'id' => '50',
    'no_doc' => 'EXP-2026-001',
    'pettycash' => '1',
    'detail_id' => array('1'),
    'id_detail' => array('10'),
    'qty' => array(1)
));
$_FILES['doc_file_1']['name'] = 'test.jpg';
$_FILES['doc_file_1']['type'] = 'image/jpeg';
$_FILES['doc_file_1']['tmp_name'] = sys_get_temp_dir() . '/tmp.jpg';
$_FILES['doc_file_1']['error'] = 0;
$_FILES['doc_file_1']['size'] = 1024;
list($e, $out) = expense_b3_run('save', array('upload_failure' => true), array(
    'id' => '50',
    'no_doc' => 'EXP-2026-001',
    'pettycash' => '1',
    'detail_id' => array('1'),
    'id_detail' => array('10'),
    'qty' => array(1)
));
check($e['status'] === 0, 'save (upload fail update): status 0');
check($e['context']['data']['outcome'] === 'upload_failed', 'save (upload fail update): outcome upload_failed');

// F. Transaction failure
list($e, $out) = expense_b3_run('save', array('transaction_failure' => true), array(
    'id' => '50',
    'no_doc' => 'EXP-2026-001',
    'total_expense' => 500000
));
check($e['status'] === 0, 'save (trans fail): status 0');
check($e['context']['data']['outcome'] === 'transaction_failed', 'save (trans fail): outcome transaction_failed');

// G. Commit failure
list($e, $out) = expense_b3_run('save', array('trans_commit_failure' => true), array(
    'id' => '50',
    'no_doc' => 'EXP-2026-001',
    'total_expense' => 500000
));
check($e['status'] === 0, 'save (commit fail): status 0');
check($e['context']['data']['outcome'] === 'transaction_failed', 'save (commit fail): outcome transaction_failed');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e) = expense_b3_run('save', array($writer => true), array('id' => '50', 'no_doc' => 'EXP-2026-001'));
    check($e['status'] === 1, 'save: unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 8. delete($id)
// -------------------------------------------------------------
// A. Normal delete
list($e, $out) = expense_b3_run('delete', array('has_kasbon_non_po' => true, 'has_kembalian' => true), array(), array(60));
check($e['status'] === 1, 'delete: status 1');
check($e['context']['data']['outcome'] === 'deleted', 'delete: outcome deleted');
check($e['context']['data']['no_doc'] === 'EXP-2026-001', 'delete: no_doc matched');
$resp = json_decode($out, true);
check($resp['delete'] === true, 'delete: json response delete true');

// B. Missing record
list($e, $out) = expense_b3_run('delete', array('missing_record' => true), array(), array(999));
check($e['status'] === 0, 'delete (missing): status 0');
check($e['context']['data']['outcome'] === 'not_found', 'delete (missing): outcome not_found');

// C. Transaction failure
list($e, $out) = expense_b3_run('delete', array('transaction_failure' => true), array(), array(60));
check($e['status'] === 0, 'delete (trans fail): status 0');
check($e['context']['data']['outcome'] === 'delete_failed', 'delete (trans fail): outcome delete_failed');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e) = expense_b3_run('delete', array($writer => true), array(), array(60));
    check($e['status'] === 1, 'delete: unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 9. cekbudget()
// -------------------------------------------------------------
// A. Budget found, dtl empty
list($e, $out) = expense_b3_run('cekbudget', array(), array(
    'coa' => '1304-01-01',
    'tgl_doc' => '2026-10-15',
    'dtl' => ''
));
check($e['status'] === 1, 'cekbudget (found dtl empty): status 1');
check($e['context']['data']['outcome'] === 'found', 'cekbudget (found dtl empty): outcome found');
check($e['context']['data']['sisa'] !== null, 'cekbudget (found dtl empty): sisa calculated');
$resp = json_decode($out, true);
check(isset($resp['budget']) && isset($resp['sisa']), 'cekbudget: json response valid');

// B. Budget found, dtl not empty
list($e, $out) = expense_b3_run('cekbudget', array(), array(
    'coa' => '1304-01-01',
    'tgl_doc' => '2026-10-15',
    'dtl' => '1'
));
check($e['status'] === 1, 'cekbudget (found dtl not empty): status 1');
check($e['context']['data']['outcome'] === 'found', 'cekbudget (found dtl not empty): outcome found');

// C. Budget not found
list($e, $out) = expense_b3_run('cekbudget', array('budget_not_found' => true), array(
    'coa' => '9999-99-99',
    'tgl_doc' => '2026-10-15',
    'dtl' => ''
));
check($e['status'] === 1, 'cekbudget (not found): status 1');
check($e['context']['data']['outcome'] === 'not_found', 'cekbudget (not found): outcome not_found');

// D. Query failure
list($e, $out) = expense_b3_run('cekbudget', array('fail_query' => true), array(
    'coa' => '1304-01-01',
    'tgl_doc' => '2026-10-15',
    'dtl' => ''
));
check($e['status'] === 0, 'cekbudget (query fail): status 0');
check($e['context']['data']['outcome'] === 'query_error', 'cekbudget (query fail): outcome query_error');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e) = expense_b3_run('cekbudget', array($writer => true), array('coa' => '1304-01-01', 'tgl_doc' => '2026-10-15'));
    check($e['status'] === 1, 'cekbudget: unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 10. transport_req_mgt()
// -------------------------------------------------------------
// A. Normal render
list($e) = expense_b3_run('transport_req_mgt');
check($e['status'] === 1, 'transport_req_mgt: status 1');
check($e['context']['data']['outcome'] === 'rendered', 'transport_req_mgt: outcome rendered');
check($ci->template->renderedView === 'transport_req_mgt_list', 'transport_req_mgt: renderedView transport_req_mgt_list');

// B. Empty transport list
list($e) = expense_b3_run('transport_req_mgt', array('empty_transport' => true));
check($e['status'] === 1, 'transport_req_mgt (empty): status 1');
check($e['context']['data']['outcome'] === 'rendered', 'transport_req_mgt (empty): outcome rendered');

// C. Query failure
list($e) = expense_b3_run('transport_req_mgt', array('fail_query' => true));
check($e['status'] === 0, 'transport_req_mgt (query fail): status 0');
check($e['context']['data']['outcome'] === 'query_error', 'transport_req_mgt (query fail): outcome query_error');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e) = expense_b3_run('transport_req_mgt', array($writer => true));
    check($e['status'] === 1, 'transport_req_mgt: unaffected by ' . $writer);
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
        expense_b3_run($pendingMethod, array('pending' => true, 'expected_exception' => true), array(), $dummyArgs);
    }
}

// -------------------------------------------------------------
// 12. Subprocess Shutdown Runner
// -------------------------------------------------------------
$cmd = escapeshellcmd(PHP_BINARY) . ' ' . escapeshellarg(__FILE__) . ' --subprocess-shutdown 2>&1';
exec($cmd, $subOutput, $subExitCode);
check($subExitCode === 0, 'Subprocess shutdown exit code 0');

echo "PASS Expense batch 3: {$scenarioCount} fixture scenarios + 1 shutdown subprocess" . PHP_EOL;
