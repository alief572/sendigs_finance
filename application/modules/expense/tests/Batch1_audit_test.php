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
class Show404Exception extends Exception {}

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

require dirname(__DIR__) . '/controllers/Expense.php';

class ExpenseTestResult {
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
                'id_user' => 1,
                'nm_lengkap' => 'Budi Santoso',
                'username' => 'budi'
            );
        }

        if (strpos($this->table, 'tr_pengajuan_rutin') !== false) {
            return (object) array(
                'id' => 10,
                'no_doc' => 'PR-RUTIN-001',
                'departement' => 'DEP-01',
                'status' => '2'
            );
        }

        if (strpos($this->table, 'coa_master') !== false || strpos($this->table, 'master_oto_jurnal_detail') !== false) {
            return (object) array(
                'no_perkiraan' => '1304-01-01',
                'nama' => 'Peralatan Kantor'
            );
        }

        if (strpos($this->table, 'tr_kasbon') !== false) {
            return (object) array(
                'id' => 55,
                'no_doc' => 'KB/2026/001',
                'departement' => 'DEP-FIN',
                'nama' => 'Budi Santoso',
                'keperluan' => 'Biaya Operasional',
                'jumlah_kasbon' => 1500000,
                'doc_file' => '["lampiran1.pdf"]',
                'doc_file_2' => '',
                'status' => '2',
                'coa' => '1304-01-01',
                'no_coa' => '1304-01-01',
                'tipe_pr' => $scenario['tipe_pr'] ?? '',
                'id_pr' => 'PR-001',
                'created_by' => 'budi'
            );
        }

        if (strpos($this->table, 'rutin_non_planning_header') !== false) {
            return (object) array(
                'no_pr' => 'PR-001',
                'id_dept' => 'DEP-FIN',
                'created_by' => 1,
                'coa' => '1304-01-01'
            );
        }

        if (strpos($this->table, 'material_planning_base_on_produksi') !== false) {
            return (object) array(
                'no_pr' => 'PR-001',
                'so_number' => 'SO-001',
                'created_by' => 1,
                'nama_user' => 'Budi Santoso',
                'nm_lengkap' => 'Budi Santoso'
            );
        }

        if (strpos($this->table, 'tran_pr_header') !== false) {
            return (object) array(
                'id' => 1,
                'no_pr' => 'PR-001',
                'created_by' => 1,
                'nm_lengkap' => 'Budi Santoso'
            );
        }

        if (strpos($this->table, 'accessories') !== false) {
            return (object) array(
                'id' => 1,
                'no_coa' => '1304-01-01',
                'nm_coa' => 'Peralatan Kantor'
            );
        }

        return (object) array(
            'id' => 1,
            'name' => 'Department Finance',
            'no_doc' => 'DOC-001',
            'no_perkiraan' => '1304-01-01',
            'nama' => 'Peralatan Kantor'
        );
    }

    public function row_array() {
        $row = $this->row();
        return $row ? (array)$row : array();
    }

    public function result() {
        global $scenario;
        if (!empty($scenario['empty_result']) || !empty($scenario['missing_record'])) {
            return array();
        }

        if (strpos($this->table, 'tr_pengajuan_rutin_detail') !== false) {
            return array(
                (object) array('id' => 1, 'no_doc' => 'PR-RUTIN-001', 'coa' => '1304-01-01', 'nama' => 'ATK', 'nilai' => 500000)
            );
        }

        if (strpos($this->table, 'tr_kasbon') !== false || strpos($this->table, 'SELECT') !== false) {
            return array(
                (object) array(
                    'id' => 55,
                    'no_doc' => 'KB/2026/001',
                    'tgl_doc' => '2026-10-14',
                    'departement' => 'DEP-FIN',
                    'nama' => 'Budi',
                    'jumlah_kasbon' => 1500000,
                    'keperluan' => 'Operasional',
                    'doc_file' => 'lampiran.pdf',
                    'status' => '2',
                    'coa' => '1304-01-01',
                    'doc_file_2' => '',
                    'bank_id' => 'BCA',
                    'accnumber' => '1234567890',
                    'accname' => 'Budi',
                    'project' => 'PRJ',
                    'keterangan' => 'Ket',
                    'id_expense_detail' => ''
                )
            );
        }

        return array(
            (object) array(
                'id' => 1,
                'no_doc' => 'DOC-001',
                'no_pr' => 'PR-001',
                'tipe_pr' => 'pr stok',
                'id_material' => 1,
                'unit' => 'Pcs',
                'category' => 'Raw Material',
                'project_name' => 'Project Alpha',
                'nama_asset' => 'Laptop Dell',
                'nm_material' => 'Kertas',
                'coa' => '1304-01-01',
                'nama' => 'Item'
            )
        );
    }

    public function result_array() {
        $res = $this->result();
        $arr = array();
        foreach ($res as $item) {
            $arr[] = (array)$item;
        }
        return $arr;
    }
}

class ExpenseTestDb {
    public $group;
    public $table;
    public $selects = array();
    public $wheres = array();
    public $calls = array();
    public $writes = array();
    public $queryError = 0;
    public $committed = false;

    public function __construct($group = 'default') {
        $this->group = $group;
    }

    public function select($cols, $escape = null) {
        $this->selects[] = $cols;
        return $this;
    }

    public function from($table) {
        $this->table = $table;
        return $this;
    }

    public function join($table, $cond, $type = '') {
        return $this;
    }

    public function where($k, $v = null, $escape = true) {
        $this->wheres[] = array($k, $v);
        return $this;
    }

    public function group_by($col) {
        return $this;
    }

    public function order_by($col, $dir = '') {
        return $this;
    }

    public function escape($val) {
        return "'" . addslashes((string)$val) . "'";
    }

    public function error() {
        return array('code' => $this->queryError, 'message' => $this->queryError ? 'Simulated query error' : '');
    }

    public function get($table = null) {
        return $this->handleQuery($table ?: $this->table);
    }

    public function get_where($table, $where) {
        return $this->handleQuery($table);
    }

    public function query($sql) {
        return $this->handleQuery($sql);
    }

    private function handleQuery($tableOrSql) {
        global $scenario;
        $this->calls[] = 'query:' . $tableOrSql;

        if (!empty($scenario['abort_query'])) {
            exit;
        }

        if (!empty($scenario['fail_query'])) {
            $this->queryError = 999;
            return !empty($scenario['query_false']) ? false : new ExpenseTestResult($this->group, $tableOrSql);
        }

        $this->queryError = 0;
        return new ExpenseTestResult($this->group, $tableOrSql);
    }

    public function trans_begin() {
        $this->committed = false;
        return true;
    }

    public function trans_status() {
        global $scenario;
        return empty($scenario['transaction_failure']);
    }

    public function trans_commit() {
        global $scenario;
        if (!empty($scenario['abort_commit'])) {
            exit;
        }
        $this->committed = empty($scenario['trans_commit_failure']);
        return $this->committed;
    }

    public function trans_rollback() {
        $this->committed = false;
        return true;
    }

    public function insert($table, $data) {
        $this->writes[] = array('insert', $table, $data);
        return 101;
    }

    public function insert_batch($table, $data) {
        $this->writes[] = array('insert_batch', $table, $data);
        return true;
    }

    public function update($table, $data, $where = array()) {
        $this->writes[] = array('update', $table, $data, $where);
        return true;
    }

    public function update_batch($table, $data, $key = null) {
        $this->writes[] = array('update_batch', $table, $data, $key);
        return true;
    }

    public function delete($table, $where) {
        $this->writes[] = array('delete', $table, $where);
        return true;
    }

    public function last_query() {
        return 'SELECT * FROM ' . $this->table;
    }
}

class ExpenseTestTemplate extends FixtureTemplate {
    public $title;
    public $page_icon;
    public $vars = array();
    public $renderedView;

    public function title($t = null) {
        if ($t !== null) { $this->title = $t; }
        return $this->title;
    }

    public function page_icon($icon = null) {
        if ($icon !== null) { $this->page_icon = $icon; }
        return $this->page_icon;
    }

    public function set($key, $val = null) {
        if (is_array($key)) {
            $this->vars = array_merge($this->vars, $key);
        } else {
            $this->vars[$key] = $val;
        }
    }

    public function render($view) {
        $this->renderedView = $view;
        echo '<!-- Rendered view: ' . htmlspecialchars($view) . ' -->';
    }
}

class ExpenseTestModel {
    public $db;
    public function __construct($db) { $this->db = $db; }

    public function GetListDataKasbon($where = '') {
        $q = $this->db->get('tr_kasbon');
        return $q ? $q->result() : false;
    }

    public function GetDataKasbon($id) {
        global $scenario;
        if (!empty($scenario['missing_record'])) { return false; }
        $q = $this->db->get('tr_kasbon');
        return $q ? $q->row() : false;
    }

    public function GetListCoa($type = '') {
        $q = $this->db->get('coa_master');
        return $q ? $q->result() : array();
    }

    public function GetPrDeptHeader($id_pr) {
        $q = $this->db->get('rutin_non_planning_header');
        return $q ? $q->row() : null;
    }

    public function GetPrDeptDetails($id_pr) {
        $q = $this->db->get('rutin_non_planning_detail');
        return $q ? $q->result() : array();
    }

    public function GetDepartmentName($id_dept) {
        return 'Department Keuangan';
    }

    public function GetPrStokHeader($id_pr) {
        $q = $this->db->get('material_planning_base_on_produksi');
        return $q ? $q->row() : null;
    }

    public function GetPrStokDetails($so_number) {
        $q = $this->db->get('material_planning_base_on_produksi_detail');
        return $q ? $q->result() : array();
    }

    public function GetPrAssetData($id_pr) {
        $q = $this->db->get('tran_pr_header');
        return $q ? $q->row() : null;
    }
}

class ExpenseAllModel {
    public function GetAutoGenerate($type) {
        return 'KB/2026/001';
    }
}

class ExpenseTestUpload {
    public function initialize($cfg) {}
    public function do_upload($field = 'file') { return true; }
    public function data() { return array('file_name' => 'uploaded_file.pdf'); }
}

class ExpenseTestLoader extends FixtureLoad {
    public $db;
    public function __construct($db) { $this->db = $db; }

    public function helper($h) {}
    public function model($m) {}
    public function library($lib, $config = null) {
        global $ci;
        if ($lib === 'Module_audit_recorder') {
            if (!isset($ci->module_audit_recorder)) {
                $ci->module_audit_recorder = new Module_audit_recorder();
            }
        }
    }
    public function database($grp, $separate = false) {
        return $this->db;
    }
    public function view($view, $data = array()) {
        echo '<!-- View Loaded: ' . htmlspecialchars($view) . ' -->';
    }
}

class ExpenseTestController extends Expense {}

function expense_run($method, $options = array(), $post = array(), $args = array()) {
    global $ci, $scenario, $events, $scenarioCount;
    $scenarioCount++;
    $scenario = $options;
    $events = array();
    $GLOBALS['show404Called'] = false;
    $GLOBALS['redirectTarget'] = null;

    $db = new ExpenseTestDb();
    $ci = (new ReflectionClass('ExpenseTestController'))->newInstanceWithoutConstructor();
    $ci->db = $db;
    $ci->load = new ExpenseTestLoader($db);
    $ci->input = new FixtureInput();
    $ci->template = new ExpenseTestTemplate();
    $ci->upload = new ExpenseTestUpload();
    $ci->auth = new FixtureAuth();
    $ci->Expense_model = new ExpenseTestModel($db);
    $ci->All_model = new ExpenseAllModel();

    $propStatus = new ReflectionProperty('Expense', 'status');
    $propStatus->setAccessible(true);
    $propStatus->setValue($ci, array("0" => "Waiting Approval Finance", "1" => "Approved", "2" => "Approved", "3" => "Approved", "9" => "Rejected"));

    $defaultPost = array(
        'id' => '',
        'no_doc' => 'KB/2026/001',
        'tgl_doc' => '2026-10-14',
        'departement' => 'DEP-FIN',
        'nama' => 'Budi',
        'keperluan' => 'Operasional Kantor',
        'keterangan' => 'Keterangan',
        'jumlah_kasbon' => '1500000',
        'filename' => '',
        'bank_id' => 'BCA',
        'accnumber' => '1234567890',
        'accname' => 'Budi Santoso',
        'filename2' => '',
        'project' => 'PRJ',
        'no_pr' => 'PR-001',
        'tipe_pr' => '',
        'file_name' => '',
        'doc_pr' => '',
        'to_doc_pr' => '',
        'coa' => '1304-01-01',
        'nm_coa' => 'Peralatan Kantor',
        'password' => 'PRIVATE_PASSWORD',
        'credit_card' => 'PRIVATE_CC',
        'doc_file' => 'binary_blob',
        'doc_file_2' => 'binary_blob_2'
    );
    $ci->input->values = array_merge($defaultPost, $post);

    $caughtException = null;
    ob_start();
    try {
        $GLOBALS['expenseLastReturn'] = call_user_func_array(array($ci, $method), $args);
    } catch (Throwable $e) {
        $caughtException = $e;
        if (isset($ci->module_audit_recorder)) {
            $ci->module_audit_recorder->finish_interrupted();
        }
        if (empty($options['expected_exception']) && !($e instanceof Show404Exception && !empty($options['expect_404']))) {
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
    expense_run('kasbon_save', array('abort_commit' => true));
    exit(0);
}
if (defined('EXPENSE_FIXTURE_ONLY')) { return; }

// =============================================================
// Main Test Execution
// =============================================================
$scenarioCount = 0;

// 1. kasbon()
list($e) = expense_run('kasbon');
check($e['status'] === 1, 'kasbon: status 1');
check($e['context']['data']['outcome'] === 'rendered', 'kasbon: outcome rendered');
foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e) = expense_run('kasbon', array($writer => true));
    check($e['status'] === 1, 'kasbon: unaffected by writer error');
}

// 2. kasbon_list_all()
list($e) = expense_run('kasbon_list_all');
check($e['status'] === 1, 'kasbon_list_all: status 1');
check($e['context']['data']['outcome'] === 'rendered', 'kasbon_list_all: outcome rendered');

// 3. kasbon_create()
list($e) = expense_run('kasbon_create');
check($e['status'] === 1, 'kasbon_create: status 1');
check($e['context']['data']['outcome'] === 'rendered', 'kasbon_create: outcome rendered');
check(isset($e['context']['data']['item_count']), 'kasbon_create: item_count present');
list($e) = expense_run('kasbon_create', array('fail_query' => true));
check($e['status'] === 0, 'kasbon_create (query error): status 0');
check($e['context']['data']['outcome'] === 'query_error', 'kasbon_create (query error): outcome query_error');

// 4. kasbon_save()
// A. New kasbon save
list($e, $out) = expense_run('kasbon_save', array(), array('id' => '', 'jumlah_kasbon' => '2500000'));
check($e['status'] === 1, 'kasbon_save (new): status 1');
check($e['context']['data']['outcome'] === 'saved', 'kasbon_save (new): outcome saved');
check($e['context']['data']['amount'] == 2500000, 'kasbon_save (new): amount matched');
$json = json_decode($out, true);
check(!empty($json['save']), 'kasbon_save (new): json save true');

// B. Update kasbon save
list($e, $out) = expense_run('kasbon_save', array(), array('id' => '55', 'no_doc' => 'KB/2026/001', 'jumlah_kasbon' => '3000000'));
check($e['status'] === 1, 'kasbon_save (update): status 1');
check($e['context']['data']['outcome'] === 'saved', 'kasbon_save (update): outcome saved');
check($e['context']['data']['id'] === '55', 'kasbon_save (update): id matched');

// C. Commit failure
list($e, $out) = expense_run('kasbon_save', array('trans_commit_failure' => true));
check($e['status'] === 0, 'kasbon_save (commit fail): status 0');
check($e['context']['data']['outcome'] === 'commit_failed', 'kasbon_save (commit fail): outcome commit_failed');

// D. Transaction status failure
list($e, $out) = expense_run('kasbon_save', array('transaction_failure' => true));
check($e['status'] === 0, 'kasbon_save (trans fail): status 0');
check($e['context']['data']['outcome'] === 'commit_failed', 'kasbon_save (trans fail): outcome commit_failed');

// E. Writer failure immunity on save
foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e, $out) = expense_run('kasbon_save', array($writer => true));
    $json = json_decode($out, true);
    check(!empty($json['save']), 'kasbon_save: business json response unaffected by ' . $writer);
}

// 5. get_kasbon()
// A. Normal fetch
list($e, $out) = expense_run('get_kasbon', array(), array(), array('Budi', 'DEP-FIN'));
check($e['status'] === 1, 'get_kasbon: status 1');
check($e['context']['data']['outcome'] === 'fetched', 'get_kasbon: outcome fetched');
check($e['context']['data']['department_id'] === 'DEP-FIN', 'get_kasbon: department_id matched');
$items = json_decode($out, true);
check(is_array($items) && count($items) > 0, 'get_kasbon: json items returned');

// 6. kasbon_edit()
// A. Found record
list($e) = expense_run('kasbon_edit', array(), array(), array(55));
check($e['status'] === 1, 'kasbon_edit: status 1');
check($e['context']['data']['outcome'] === 'rendered', 'kasbon_edit: outcome rendered');
check($e['context']['data']['id'] == 55, 'kasbon_edit: id matched');

// B. Missing record
list($e) = expense_run('kasbon_edit', array('missing_record' => true), array(), array(999));
check($e['status'] === 0, 'kasbon_edit (missing): status 0');
check($e['context']['data']['outcome'] === 'not_found', 'kasbon_edit (missing): outcome not_found');

// C. Query failure
list($e) = expense_run('kasbon_edit', array('fail_query' => true), array(), array(55));
check($e['status'] === 0, 'kasbon_edit (query fail): status 0');
check($e['context']['data']['outcome'] === 'query_error', 'kasbon_edit (query fail): outcome query_error');

// 7. kasbon_edit_manage()
// A. Found record
list($e) = expense_run('kasbon_edit_manage', array(), array(), array(55));
check($e['status'] === 1, 'kasbon_edit_manage: status 1');
check($e['context']['data']['outcome'] === 'rendered', 'kasbon_edit_manage: outcome rendered');

// B. Missing record
list($e) = expense_run('kasbon_edit_manage', array('missing_record' => true), array(), array(999));
check($e['status'] === 0, 'kasbon_edit_manage (missing): status 0');
check($e['context']['data']['outcome'] === 'not_found', 'kasbon_edit_manage (missing): outcome not_found');

// C. Query failure
list($e) = expense_run('kasbon_edit_manage', array('fail_query' => true), array(), array(55));
check($e['status'] === 0, 'kasbon_edit_manage (query fail): status 0');
check($e['context']['data']['outcome'] === 'query_error', 'kasbon_edit_manage (query fail): outcome query_error');

// 8. kasbon_print()
// A. Standard print
list($e) = expense_run('kasbon_print', array('tipe_pr' => ''), array(), array(55));
check($e['status'] === 1, 'kasbon_print (standard): status 1');
check($e['context']['data']['outcome'] === 'printed', 'kasbon_print (standard): outcome printed');

// B. PR Dept print
list($e) = expense_run('kasbon_print', array('tipe_pr' => 'pr departemen'), array(), array(55));
check($e['status'] === 1, 'kasbon_print (dept): status 1');
check($e['context']['data']['outcome'] === 'printed', 'kasbon_print (dept): outcome printed');
check($e['context']['data']['tipe_pr'] === 'pr departemen', 'kasbon_print (dept): tipe_pr matched');

// C. PR Stok print
list($e) = expense_run('kasbon_print', array('tipe_pr' => 'pr stok'), array(), array(55));
check($e['status'] === 1, 'kasbon_print (stok): status 1');
check($e['context']['data']['outcome'] === 'printed', 'kasbon_print (stok): outcome printed');

// D. PR Asset print
list($e) = expense_run('kasbon_print', array('tipe_pr' => 'pr asset'), array(), array(55));
check($e['status'] === 1, 'kasbon_print (asset): status 1');
check($e['context']['data']['outcome'] === 'printed', 'kasbon_print (asset): outcome printed');

// E. Missing record
list($e) = expense_run('kasbon_print', array('missing_record' => true), array(), array(999));
check($e['status'] === 0, 'kasbon_print (missing): status 0');
check($e['context']['data']['outcome'] === 'not_found', 'kasbon_print (missing): outcome not_found');

// F. Query failure
list($e) = expense_run('kasbon_print', array('fail_query' => true, 'tipe_pr' => 'pr stok'), array(), array(55));
check($e['status'] === 0, 'kasbon_print (query fail): status 0');
check($e['context']['data']['outcome'] === 'query_error', 'kasbon_print (query fail): outcome query_error');

// 9. periodik_print()
// A. Success print
list($e) = expense_run('periodik_print', array(), array(), array(10));
check($e['status'] === 1, 'periodik_print: status 1');
check($e['context']['data']['outcome'] === 'printed', 'periodik_print: outcome printed');
check($e['context']['data']['no_doc'] === 'PR-RUTIN-001', 'periodik_print: no_doc matched');

// B. Missing record (404)
list($e, $out, $ctrl, $ex) = expense_run('periodik_print', array('missing_record' => true, 'expect_404' => true), array(), array(999));
check($e['status'] === 0, 'periodik_print (404): status 0');
check($e['context']['data']['outcome'] === 'not_found', 'periodik_print (404): outcome not_found');
check($ex instanceof Show404Exception, 'periodik_print: Show404Exception thrown');

// C. Query failure
list($e) = expense_run('periodik_print', array('fail_query' => true), array(), array(10));
check($e['status'] === 0, 'periodik_print (query fail): status 0');
check($e['context']['data']['outcome'] === 'query_error', 'periodik_print (query fail): outcome query_error');

// 10. kasbon_view()
// A. Found record
list($e) = expense_run('kasbon_view', array(), array(), array(55));
check($e['status'] === 1, 'kasbon_view: status 1');
check($e['context']['data']['outcome'] === 'rendered', 'kasbon_view: outcome rendered');
check($e['context']['data']['id'] == 55, 'kasbon_view: id matched');

// B. Missing record
list($e) = expense_run('kasbon_view', array('missing_record' => true), array(), array(999));
check($e['status'] === 0, 'kasbon_view (missing): status 0');
check($e['context']['data']['outcome'] === 'not_found', 'kasbon_view (missing): outcome not_found');

// C. Query failure
list($e) = expense_run('kasbon_view', array('fail_query' => true), array(), array(55));
check($e['status'] === 0, 'kasbon_view (query fail): status 0');
check($e['context']['data']['outcome'] === 'query_error', 'kasbon_view (query fail): outcome query_error');

// 11. Uninstrumented Batch 8 endpoint protection
foreach (array('get_data_transport_req_all', 'get_coa_name', 'kasbon_fin_manage', 'get_dat_list_kasbon', 'get_dat_kasbon_list') as $pendingMethod) {
    if (method_exists($ci, $pendingMethod)) {
        $refM = new ReflectionMethod($ci, $pendingMethod);
        $dummyArgs = array();
        for ($i = 0; $i < $refM->getNumberOfRequiredParameters(); $i++) {
            $dummyArgs[] = 1;
        }
        expense_run($pendingMethod, array('pending' => true, 'expected_exception' => true), array(), $dummyArgs);
    }
}

// 12. Subprocess Shutdown Execution
$cmd = escapeshellcmd(PHP_BINARY) . ' ' . escapeshellarg(__FILE__) . ' --subprocess-shutdown 2>&1';
exec($cmd, $subOutput, $subExitCode);
check($subExitCode === 0, 'Subprocess shutdown exit code 0');

echo "PASS Expense batch 1: {$scenarioCount} fixture scenarios + 1 shutdown subprocess" . PHP_EOL;
