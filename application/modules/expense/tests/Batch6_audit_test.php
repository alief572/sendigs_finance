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

class ExpenseBatch6Result {
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

        if ($this->table === 'tr_expense') {
            return (object) array(
                'id' => 10,
                'no_doc' => 'EXP-2026-001',
                'nama' => 'Budi Santoso',
                'jumlah' => !empty($scenario['negative_jumlah']) ? -500000 : 500000,
                'pettycash' => '1101-01-01',
                'status' => '1'
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
        if (!empty($scenario['empty_result'])) {
            return array();
        }

        if ($this->table === 'tr_pengembalian_expense') {
            if (!empty($scenario['empty_pengembalian'])) {
                return array();
            }
            return array(
                (object) array(
                    'id' => 1,
                    'no_doc' => 'EXP-2026-001',
                    'transfer_jumlah' => !empty($scenario['existing_kembali']) ? $scenario['existing_kembali'] : 100000
                )
            );
        }

        if ($this->table === 'coa_master' || strpos($this->table, 'coa_master') !== false || (isset($this->params['sql']) && strpos($this->params['sql'], 'coa_master') !== false)) {
            return array(
                (object) array('no_perkiraan' => '1101-01-01', 'nama' => 'Kas Kecil Operational'),
                (object) array('no_perkiraan' => '1101-01-02', 'nama' => 'Bank Mandiri')
            );
        }

        if ($this->table === 'ms_department') {
            return array(
                (object) array('id' => 1, 'nama' => 'Finance'),
                (object) array('id' => 2, 'nama' => 'IT')
            );
        }

        return array(
            (object) array(
                'id' => 1,
                'no_doc' => 'EXP-2026-001',
                'nama' => 'Budi',
                'metode_pembayaran' => 2,
                'id_expense_pett_pr_non_po' => null
            )
        );
    }

    public function result_array() {
        global $scenario;
        if (!empty($scenario['empty_result'])) {
            return array();
        }

        if (!empty($scenario['empty_pr_details'])) {
            return array();
        }

        if (strpos($this->table, 'material_planning_base_on_produksi_detail') !== false) {
            return array(
                array(
                    'id' => 101,
                    'material_name' => 'Baut M8',
                    'qty' => 50,
                    'unit' => 'Pcs',
                    'tipe_pr' => 'pr stok',
                    'price_ref' => 1500
                )
            );
        }

        if (strpos($this->table, 'rutin_non_planning_detail') !== false) {
            return array(
                array(
                    'id' => 201,
                    'material_name' => 'Kertas A4',
                    'qty' => 5,
                    'unit' => 'Rim',
                    'price' => 45000,
                    'total_price' => 225000,
                    'tipe_pr' => 'pr departemen',
                    'price_ref' => 0
                )
            );
        }

        if (strpos($this->table, 'tran_pr_header') !== false) {
            return array(
                array(
                    'id' => 301,
                    'material_name' => 'Laptop Core i7',
                    'qty' => 1,
                    'unit' => 'Pcs',
                    'price' => 15000000,
                    'total_price' => 15000000,
                    'tipe_pr' => 'pr asset',
                    'price_ref' => 0
                )
            );
        }

        return array(
            array('id' => 1, 'name' => 'Mock Array Row')
        );
    }
}

class ExpenseBatch6Db {
    public $whereData = array();
    public $selectFields = '';
    public $fromTable = '';
    public $joinData = array();
    public $groupData = array();
    public $lastQuery = 'SELECT * FROM mock_table';
    public $transDepth = 0;
    public $calls = array();

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
        if (is_array($key)) {
            $this->whereData = array_merge($this->whereData, $key);
        } else {
            $this->whereData[$key] = $val;
        }
        return $this;
    }

    public function like($field, $match = '', $side = 'both') {
        return $this;
    }

    public function get($table = null, $limit = null, $offset = null) {
        $t = $table ?: $this->fromTable;
        return new ExpenseBatch6Result('get', $t);
    }

    public function get_where($table, $where = array(), $limit = null, $offset = null) {
        return new ExpenseBatch6Result('get_where', $table, $where);
    }

    public function query($sql, $binds = FALSE, $return_object = NULL) {
        $this->lastQuery = $sql;
        return new ExpenseBatch6Result('query', 'raw_query', array('sql' => $sql));
    }

    public function trans_begin() {
        $this->transDepth++;
        $this->calls[] = 'trans_begin';
        return true;
    }

    public function trans_status() {
        global $scenario;
        if (!empty($scenario['transaction_failure'])) {
            return false;
        }
        return true;
    }

    public function trans_commit() {
        global $scenario;
        if (!empty($scenario['trans_commit_failure'])) {
            return false;
        }
        $this->transDepth = max(0, $this->transDepth - 1);
        $this->calls[] = 'trans_commit';
        return true;
    }

    public function trans_rollback() {
        $this->transDepth = max(0, $this->transDepth - 1);
        $this->calls[] = 'trans_rollback';
        return true;
    }

    public function update($table, $data = array(), $where = array()) {
        global $scenario;
        $this->calls[] = 'update';
        if (!empty($scenario['fail_update'])) {
            return false;
        }
        return true;
    }

    public function insert($table, $data = array()) {
        global $scenario;
        $this->calls[] = 'insert';
        if (!empty($scenario['fail_insert'])) {
            return false;
        }
        return true;
    }

    public function insert_batch($table, $data = array()) {
        $this->calls[] = 'insert_batch';
        return true;
    }

    public function delete($table, $where = array()) {
        global $scenario;
        $this->calls[] = 'delete';
        if (!empty($scenario['fail_delete'])) {
            return false;
        }
        return true;
    }

    public function error() {
        return array('code' => 500, 'message' => 'Simulated database error');
    }

    public function last_query() {
        return $this->lastQuery;
    }
}

class ExpenseBatch6Model {
    public function GetDataHeader($id) {
        global $scenario;
        if (!empty($scenario['missing_record'])) {
            return null;
        }
        return (object) array(
            'id' => $id,
            'no_doc' => 'EXP-2026-001',
            'pettycash' => '1101-01-01',
            'jumlah' => 250000
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
                'no_doc' => $no_doc,
                'deskripsi' => 'Konsumsi Rapat',
                'expense' => 250000
            )
        );
    }

    public function getArray($table, $where, $key, $val) {
        return array('1' => 'Finance', '2' => 'IT');
    }
}

class ExpenseBatch6AllModel {
    public function GetComboBudget($type = '', $cat = '', $year = '') {
        return array(
            (object) array('id' => 1, 'nama' => 'Budget Operasional')
        );
    }

    public function GetOneTable($table, $where = '', $order = '') {
        return array(
            (object) array('id' => 1, 'nama' => 'Petty Cash HO')
        );
    }

    public function GetPettyCashComboCoa($pc = '') {
        return array(
            (object) array('no_perkiraan' => '1101-01-01', 'nama' => 'Petty Cash Operational')
        );
    }

    public function GetCoaCombo($cat = '', $where = '') {
        return array(
            (object) array('no_perkiraan' => '5101-01-01', 'nama' => 'Biaya Kantor')
        );
    }

    public function GetUserCombo() {
        return array('budi' => 'Budi Santoso');
    }

    public function dataUpdate($table, $data, $where) {
        global $scenario;
        if (!empty($scenario['fail_update'])) {
            return false;
        }
        return true;
    }
}

class ExpenseBatch6Auth {
    public function user_id() { return 10; }
    public function user_name() { return 'budi_santoso'; }
}

class ExpenseBatch6Session {
    public $userdata = array(
        'app_session' => array(
            'username' => 'budi_santoso',
            'user_id' => 10
        )
    );

    public function userdata($key = null) {
        if ($key === null) {
            return $this->userdata;
        }
        return isset($this->userdata[$key]) ? $this->userdata[$key] : null;
    }
}

class ExpenseBatch6Template {
    public $vars = array();
    public $renderedView = '';

    public function set($key, $val = null) {
        if (is_array($key)) {
            $this->vars = array_merge($this->vars, $key);
        } else {
            $this->vars[$key] = $val;
        }
    }

    public function page_icon($icon) {}

    public function render($view) {
        $this->renderedView = $view;
        echo "<!-- rendered {$view} -->";
    }
}

class ExpenseBatch6Input {
    public $values = array();

    public function post($key = null) {
        if ($key === null) {
            return $this->values;
        }
        return isset($this->values[$key]) ? $this->values[$key] : null;
    }
}

class ExpenseBatch6Load {
    public function library($lib) {}
    public function helper($h) {}
    public function model($m) {}
}

function expense_b6_run($method, $options = array(), $post = array(), $args = array()) {
    global $scenario, $events, $ci;
    $scenario = $options;
    $events = array();
    $GLOBALS['scenarioCount']++;

    $recorder = new Module_audit_recorder();

    $ci = (new ReflectionClass('Expense'))->newInstanceWithoutConstructor();
    $ci->module_audit_recorder = $recorder;
    $ci->db = new ExpenseBatch6Db();
    $ci->input = new ExpenseBatch6Input();
    $ci->auth = new ExpenseBatch6Auth();
    $ci->session = new ExpenseBatch6Session();
    $ci->template = new ExpenseBatch6Template();
    $ci->load = new ExpenseBatch6Load();
    $ci->Expense_model = new ExpenseBatch6Model();
    $ci->All_model = new ExpenseBatch6AllModel();

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
    list($e, $out) = expense_b6_run('create_pc', array('writer_exception' => true));
    exit($e['status'] === 1 ? 0 : 1);
}

// -------------------------------------------------------------
// 1. create_pc()
// -------------------------------------------------------------
list($e, $out, $inst) = expense_b6_run('create_pc');
check($e['status'] === 1, 'create_pc: status 1');
check($e['context']['data']['page'] === 'form_pc', 'create_pc: page matched');
check($e['context']['data']['outcome'] === 'rendered', 'create_pc: outcome rendered');
check($inst->template->renderedView === 'form_pc', 'create_pc: renderedView matched');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e, $out, $inst) = expense_b6_run('create_pc', array($writer => true));
    check($e['status'] === 1, 'create_pc: unaffected by ' . $writer);
    check($inst->template->renderedView === 'form_pc', 'create_pc: render intact on ' . $writer);
}

// -------------------------------------------------------------
// 2. edit_pc($id)
// -------------------------------------------------------------
list($e, $out, $inst) = expense_b6_run('edit_pc', array(), array(), array(10));
check($e['status'] === 1, 'edit_pc: status 1');
check($e['context']['data']['id'] == 10, 'edit_pc: id matched');
check($e['context']['data']['no_doc'] === 'EXP-2026-001', 'edit_pc: no_doc matched');
check($e['context']['data']['outcome'] === 'rendered', 'edit_pc: outcome rendered');
check($inst->template->renderedView === 'form_pc', 'edit_pc: renderedView matched');

// Missing record
list($e, $out) = expense_b6_run('edit_pc', array('missing_record' => true), array(), array(999));
check($e['status'] === 1, 'edit_pc (missing): status 1');
check($e['context']['data']['outcome'] === 'not_found', 'edit_pc (missing): outcome not_found');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e, $out) = expense_b6_run('edit_pc', array($writer => true), array(), array(10));
    check($e['status'] === 1, 'edit_pc: unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 3. view_pc($id)
// -------------------------------------------------------------
list($e, $out, $inst) = expense_b6_run('view_pc', array(), array(), array(10));
check($e['status'] === 1, 'view_pc: status 1');
check($e['context']['data']['id'] == 10, 'view_pc: id matched');
check($e['context']['data']['no_doc'] === 'EXP-2026-001', 'view_pc: no_doc matched');
check($e['context']['data']['outcome'] === 'rendered', 'view_pc: outcome rendered');
check($inst->template->renderedView === 'form_pc', 'view_pc: renderedView matched');

// Missing record
list($e, $out) = expense_b6_run('view_pc', array('missing_record' => true), array(), array(999));
check($e['status'] === 1, 'view_pc (missing): status 1');
check($e['context']['data']['outcome'] === 'not_found', 'view_pc (missing): outcome not_found');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e, $out) = expense_b6_run('view_pc', array($writer => true), array(), array(10));
    check($e['status'] === 1, 'view_pc: unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 4. getcoabudget()
// -------------------------------------------------------------
list($e, $out) = expense_b6_run('getcoabudget', array(), array('coa' => '1101-01-01;1101-01-02'));
check($e['status'] === 1, 'getcoabudget: status 1');
check($e['context']['data']['count'] == 2, 'getcoabudget: options count matched');
check($e['context']['data']['outcome'] === 'success', 'getcoabudget: outcome success');
check(strpos($out, '<option value=\'1101-01-01\'>') !== false, 'getcoabudget: html options rendered');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e, $out) = expense_b6_run('getcoabudget', array($writer => true), array('coa' => '1101-01-01'));
    check($e['status'] === 1, 'getcoabudget: unaffected by ' . $writer);
    check(strpos($out, '<option') !== false, 'getcoabudget: output intact on ' . $writer);
}

// -------------------------------------------------------------
// 5. reject()
// -------------------------------------------------------------
// A. Normal reject
list($e, $out) = expense_b6_run('reject', array(), array('id' => 10, 'reason' => 'Data tidak sesuai', 'table' => 'tr_expense'));
check($e['status'] === 1, 'reject: status 1');
check($e['context']['data']['outcome'] === 'rejected', 'reject: outcome rejected');
check($e['context']['data']['reason'] === 'Data tidak sesuai', 'reject: reason recorded');
$resp = json_decode($out, true);
check($resp['save'] === true, 'reject: json save true');
check($resp['id'] == 10, 'reject: json id 10');

// B. Empty input
list($e, $out) = expense_b6_run('reject', array(), array('id' => '', 'table' => ''));
check($e['status'] === 0, 'reject (empty): status 0');
check($e['context']['data']['outcome'] === 'empty_id', 'reject (empty): outcome empty_id');
$resp = json_decode($out, true);
check($resp['save'] === false, 'reject (empty): json save false');

// C. Update failure
list($e, $out) = expense_b6_run('reject', array('fail_update' => true), array('id' => 10, 'table' => 'tr_expense', 'reason' => 'test'));
check($e['status'] === 0, 'reject (fail update): status 0');
check($e['context']['data']['outcome'] === 'update_failed', 'reject (fail update): outcome update_failed');

// D. Transaction / Commit failure
list($e, $out) = expense_b6_run('reject', array('transaction_failure' => true), array('id' => 10, 'table' => 'tr_expense', 'reason' => 'test'));
check($e['status'] === 0, 'reject (trans fail): status 0');
check($e['context']['data']['outcome'] === 'transaction_failed', 'reject (trans fail): outcome transaction_failed');

list($e, $out) = expense_b6_run('reject', array('trans_commit_failure' => true), array('id' => 10, 'table' => 'tr_expense', 'reason' => 'test'));
check($e['status'] === 0, 'reject (commit fail): status 0');
check($e['context']['data']['outcome'] === 'transaction_failed', 'reject (commit fail): outcome transaction_failed');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e, $out) = expense_b6_run('reject', array($writer => true), array('id' => 10, 'table' => 'tr_expense', 'reason' => 'test'));
    check($e['status'] === 1, 'reject: unaffected by ' . $writer);
    $resp = json_decode($out, true);
    check($resp['save'] === true, 'reject: business response unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 6. reject_manage()
// -------------------------------------------------------------
// A. Normal reject_manage
list($e, $out) = expense_b6_run('reject_manage', array(), array('id' => 10, 'reason' => 'Revisi mgt', 'table' => 'tr_expense'));
check($e['status'] === 1, 'reject_manage: status 1');
check($e['context']['data']['outcome'] === 'rejected', 'reject_manage: outcome rejected');
$resp = json_decode($out, true);
check($resp['save'] === true, 'reject_manage: json save true');

// B. Empty ID
list($e, $out) = expense_b6_run('reject_manage', array(), array('id' => '', 'table' => 'tr_expense'));
check($e['status'] === 0, 'reject_manage (empty id): status 0');
check($e['context']['data']['outcome'] === 'empty_id', 'reject_manage (empty id): outcome empty_id');
$resp = json_decode($out, true);
check($resp['save'] === false, 'reject_manage (empty id): json save false');

// C. Update failure
list($e, $out) = expense_b6_run('reject_manage', array('fail_update' => true), array('id' => 10, 'table' => 'tr_expense'));
check($e['status'] === 0, 'reject_manage (fail update): status 0');
check($e['context']['data']['outcome'] === 'update_failed', 'reject_manage (fail update): outcome update_failed');

// D. Trans commit failure
list($e, $out) = expense_b6_run('reject_manage', array('trans_commit_failure' => true), array('id' => 10, 'table' => 'tr_expense'));
check($e['status'] === 0, 'reject_manage (commit fail): status 0');
check($e['context']['data']['outcome'] === 'update_failed', 'reject_manage (commit fail): outcome update_failed');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e, $out) = expense_b6_run('reject_manage', array($writer => true), array('id' => 10, 'table' => 'tr_expense', 'reason' => 'test'));
    check($e['status'] === 1, 'reject_manage: unaffected by ' . $writer);
    $resp = json_decode($out, true);
    check($resp['save'] === true, 'reject_manage: business response unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 7. review($id)
// -------------------------------------------------------------
list($e, $out, $inst) = expense_b6_run('review', array(), array(), array(10));
check($e['status'] === 1, 'review: status 1');
check($e['context']['data']['id'] == 10, 'review: id matched');
check($e['context']['data']['no_doc'] === 'EXP-2026-001', 'review: no_doc matched');
check($e['context']['data']['outcome'] === 'rendered', 'review: outcome rendered');
check($inst->template->renderedView === 'expense_review', 'review: renderedView matched');

// Missing record
list($e, $out) = expense_b6_run('review', array('missing_record' => true), array(), array(999));
check($e['status'] === 1, 'review (missing): status 1');
check($e['context']['data']['outcome'] === 'not_found', 'review (missing): outcome not_found');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e, $out) = expense_b6_run('review', array($writer => true), array(), array(10));
    check($e['status'] === 1, 'review: unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 8. return_confirm($id = '')
// -------------------------------------------------------------
// A. Normal confirm (valid = 1)
$postData = array(
    'transfer_coa_bank' => '1101-01-02',
    'transfer_tanggal'  => '2026-10-15',
    'transfer_jumlah'   => 150000
);
list($e, $out) = expense_b6_run('return_confirm', array(), $postData, array(10));
check($e['status'] === 1, 'return_confirm: status 1');
check($e['context']['data']['outcome'] === 'confirmed', 'return_confirm: outcome confirmed');
check($e['context']['data']['valid'] === 1, 'return_confirm: valid 1');
$resp = json_decode($out, true);
check($resp['save'] === true, 'return_confirm: json save true');
check($resp['valid'] === 1, 'return_confirm: json valid 1');

// B. Over limit (valid = 2)
$postDataOver = array(
    'transfer_coa_bank' => '1101-01-02',
    'transfer_tanggal'  => '2026-10-15',
    'transfer_jumlah'   => 800000 // total > 500000
);
list($e, $out) = expense_b6_run('return_confirm', array(), $postDataOver, array(10));
check($e['status'] === 0, 'return_confirm (over limit): status 0');
check($e['context']['data']['valid'] === 2, 'return_confirm (over limit): valid 2');
check($e['context']['data']['outcome'] === 'over_limit', 'return_confirm (over limit): outcome over_limit');
$resp = json_decode($out, true);
check($resp['save'] === false, 'return_confirm (over limit): json save false');
check($resp['valid'] === 2, 'return_confirm (over limit): json valid 2');

// C. Empty ID
list($e, $out) = expense_b6_run('return_confirm', array(), $postData, array(''));
check($e['status'] === 0, 'return_confirm (empty id): status 0');
check($e['context']['data']['outcome'] === 'empty_id', 'return_confirm (empty id): outcome empty_id');
$resp = json_decode($out, true);
check($resp['save'] === false, 'return_confirm (empty id): json save false');

// D. Trans commit failure
list($e, $out) = expense_b6_run('return_confirm', array('trans_commit_failure' => true), $postData, array(10));
check($e['status'] === 0, 'return_confirm (commit fail): status 0');
check($e['context']['data']['outcome'] === 'insert_failed', 'return_confirm (commit fail): outcome insert_failed');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e, $out) = expense_b6_run('return_confirm', array($writer => true), $postData, array(10));
    check($e['status'] === 1, 'return_confirm: unaffected by ' . $writer);
    $resp = json_decode($out, true);
    check($resp['save'] === true, 'return_confirm: business response unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 9. get_pr_non_po()
// -------------------------------------------------------------
// A. Normal get PR details
list($e, $out) = expense_b6_run('get_pr_non_po', array(), array('no_pr' => 'PR-2026-001'));
check($e['status'] === 1, 'get_pr_non_po: status 1');
check($e['context']['data']['valid'] === 1, 'get_pr_non_po: valid 1');
check($e['context']['data']['outcome'] === 'found', 'get_pr_non_po: outcome found');
$resp = json_decode($out, true);
check($resp['sts'] === 1, 'get_pr_non_po: json sts 1');
check(strpos($resp['hasil'], 'detail_pr_101') !== false, 'get_pr_non_po: stok material rendered');
check(strpos($resp['hasil'], 'detail_pr_201') !== false, 'get_pr_non_po: dept detail rendered');
check(strpos($resp['hasil'], 'detail_pr_301') !== false, 'get_pr_non_po: asset detail rendered');

// B. Empty / Not found PR
list($e, $out) = expense_b6_run('get_pr_non_po', array('empty_pr_details' => true), array('no_pr' => 'PR-NOT-FOUND'));
check($e['status'] === 1, 'get_pr_non_po (not found): status 1');
check($e['context']['data']['valid'] === 0, 'get_pr_non_po (not found): valid 0');
check($e['context']['data']['outcome'] === 'not_found', 'get_pr_non_po (not found): outcome not_found');
$resp = json_decode($out, true);
check($resp['sts'] === 0, 'get_pr_non_po (not found): json sts 0');
check($resp['pesan'] === 'Sorry, PR not found !', 'get_pr_non_po (not found): json error message');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e, $out) = expense_b6_run('get_pr_non_po', array($writer => true), array('no_pr' => 'PR-2026-001'));
    check($e['status'] === 1, 'get_pr_non_po: unaffected by ' . $writer);
    $resp = json_decode($out, true);
    check($resp['sts'] === 1, 'get_pr_non_po: response intact on ' . $writer);
}

// -------------------------------------------------------------
// 10. del_detail()
// -------------------------------------------------------------
// A. Normal delete detail
list($e, $out) = expense_b6_run('del_detail', array(), array('id_detail' => 55));
check($e['status'] === 1, 'del_detail: status 1');
check($e['context']['data']['outcome'] === 'deleted', 'del_detail: outcome deleted');
check($e['context']['data']['id_detail'] == 55, 'del_detail: id_detail matched');

// B. Trans commit failure
list($e, $out) = expense_b6_run('del_detail', array('trans_commit_failure' => true), array('id_detail' => 55));
check($e['status'] === 0, 'del_detail (commit fail): status 0');
check($e['context']['data']['outcome'] === 'delete_failed', 'del_detail (commit fail): outcome delete_failed');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e, $out) = expense_b6_run('del_detail', array($writer => true), array('id_detail' => 55));
    check($e['status'] === 1, 'del_detail: unaffected by ' . $writer);
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
        expense_b6_run($pendingMethod, array('pending' => true, 'expected_exception' => true), array(), $dummyArgs);
    }
}

// -------------------------------------------------------------
// 12. Subprocess Shutdown Runner
// -------------------------------------------------------------
$cmd = escapeshellcmd(PHP_BINARY) . ' ' . escapeshellarg(__FILE__) . ' --subprocess-shutdown 2>&1';
exec($cmd, $subOutput, $subExitCode);
check($subExitCode === 0, 'Subprocess shutdown exit code 0');

echo "PASS Expense batch 6: {$scenarioCount} fixture scenarios + 1 shutdown subprocess" . PHP_EOL;
