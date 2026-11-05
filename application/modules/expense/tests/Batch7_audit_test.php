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

class ExpenseBatch7Result {
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

        if ($this->table === 'tr_kasbon') {
            return (object) array(
                'id' => 10,
                'no_doc' => 'KB-2026-001',
                'keperluan' => 'Beli ATK Kantor',
                'jumlah_kasbon' => 1500000,
                'metode_pembayaran' => 2,
                'id_expense_pett_pr_non_po' => null,
                'created_by' => 'budi_santoso'
            );
        }

        if ($this->table === 'tr_expense') {
            return (object) array(
                'id' => 20,
                'no_doc' => 'EXP-2026-001',
                'informasi' => 'Reimburse Transport Luar Kota',
                'jumlah' => 750000,
                'status' => '1',
                'tipe_pengembalian' => 1,
                'tipe_penggantian' => 0,
                'exp_pib' => null,
                'exp_inv_po' => null,
                'pettycash' => null,
                'expense_id_kembalian' => null,
                'created_by' => 'budi_santoso'
            );
        }

        if ($this->table === 'tr_expense_detail') {
            return (object) array(
                'id' => 101,
                'no_doc' => '10',
                'tanggal' => date('Y-m-d'),
                'qty' => 1,
                'harga' => 750000,
                'total_harga' => 750000,
                'status' => 0,
                'expense' => 750000,
                'created_by' => 'budi_santoso',
                'id_expense_bayar_sisa' => 'EXP-2026-001'
            );
        }

        if ($this->table === 'rutin_non_planning_header') {
            if (!empty($scenario['empty_pr_dept'])) {
                return null;
            }
            return (object) array(
                'id' => 301,
                'no_pr' => 'PR-RUTIN-001',
                'document' => 'nota_dinas.pdf'
            );
        }

        if ($this->table === 'tran_pr_header') {
            if (!empty($scenario['empty_pr_asset'])) {
                return null;
            }
            return (object) array(
                'id' => 401,
                'no_pr' => 'PR-ASSET-001',
                'dokumen_pendukung' => 'surat_pr_asset.pdf'
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

        if ($this->table === 'tr_kasbon') {
            return array(
                (object) array(
                    'id' => 10,
                    'no_doc' => 'KB-2026-001',
                    'keperluan' => 'Beli ATK Kantor',
                    'jumlah_kasbon' => 1500000,
                    'metode_pembayaran' => 2,
                    'id_expense_pett_pr_non_po' => null,
                    'created_by' => 'budi_santoso'
                ),
                (object) array(
                    'id' => 11,
                    'no_doc' => 'KB-2026-002',
                    'keperluan' => 'Konsumsi Meeting',
                    'jumlah_kasbon' => 250000,
                    'metode_pembayaran' => 2,
                    'id_expense_pett_pr_non_po' => null,
                    'created_by' => 'siti_aminah'
                )
            );
        }

        if ($this->table === 'tr_expense') {
            return array(
                (object) array(
                    'id' => 20,
                    'no_doc' => 'EXP-2026-001',
                    'informasi' => 'Reimburse Transport Luar Kota',
                    'jumlah' => 750000,
                    'status' => '1',
                    'tipe_pengembalian' => 1,
                    'tipe_penggantian' => 0,
                    'exp_pib' => null,
                    'exp_inv_po' => null,
                    'pettycash' => null,
                    'expense_id_kembalian' => null,
                    'created_by' => 'budi_santoso'
                )
            );
        }

        if ($this->table === 'tr_transport' || $this->table === 'tr_transport_req') {
            return array(
                (object) array(
                    'id' => 1,
                    'no_doc' => 'TRP-2026-001',
                    'tgl_doc' => '2026-10-15',
                    'nama' => 'Budi Santoso',
                    'keperluan' => 'Meeting Klien',
                    'nopol' => 'B 1234 CD',
                    'status' => 0,
                    'ttl_transport' => 150000,
                    'created_by' => 'budi_santoso',
                    'departement' => 1
                )
            );
        }

        return array(
            (object) array(
                'id' => 1,
                'no_doc' => 'MOCK-001'
            )
        );
    }
}

class ExpenseBatch7Db {
    public $queries = array();
    public $transDepth = 0;
    public $transStatus = true;
    public $lastTable = '';
    public function select($s) { return $this; }
    public function from($f) { $parts = explode(' ', trim($f)); $this->lastTable = $parts[0]; return $this; }
    public function join($t, $c, $type = '') { return $this; }
    public function where($k, $v = null) { return $this; }
    public function or_where($k, $v = null) { return $this; }
    public function where_in($k, $v = array()) { return $this; }
    public function like($k, $v = '', $side = 'both') { return $this; }
    public function or_like($k, $v = '', $side = 'both') { return $this; }
    public function group_start() { return $this; }
    public function group_end() { return $this; }
    public function group_by($g) { return $this; }
    public function order_by($o, $d = '') { return $this; }
    public function limit($l, $s = 0) { return $this; }

    public function count_all_results($table = '') {
        global $scenario;
        if (!empty($scenario['empty_result'])) {
            return 0;
        }
        return 1;
    }

    public function get($table = null) {
        if ($table) { $parts = explode(' ', trim($table)); $this->lastTable = $parts[0]; }
        return new ExpenseBatch7Result('default', $this->lastTable);
    }

    public function get_where($table, $where = array()) {
        $this->lastTable = $table;
        return new ExpenseBatch7Result('default', $table, array('where' => $where));
    }

    public function trans_begin() {
        $this->transDepth++;
        $this->transStatus = true;
        return true;
    }

    public function trans_start() {
        $this->transDepth++;
        $this->transStatus = true;
        return true;
    }

    public function trans_status() {
        global $scenario;
        if (!empty($scenario['trans_status_failure'])) {
            return false;
        }
        return $this->transStatus;
    }

    public function trans_commit() {
        global $scenario;
        $this->transDepth = max(0, $this->transDepth - 1);
        if (!empty($scenario['trans_commit_failure'])) {
            return false;
        }
        return true;
    }

    public function trans_rollback() {
        $this->transDepth = max(0, $this->transDepth - 1);
        return true;
    }

    public function insert($table, $data = array()) {
        global $scenario;
        $this->lastTable = $table;
        $this->queries[] = "INSERT INTO {$table}";
        if (!empty($scenario['fail_insert'])) {
            $this->transStatus = false;
            return false;
        }
        return true;
    }

    public function insert_batch($table, $data = array()) {
        $this->lastTable = $table;
        $this->queries[] = "INSERT BATCH INTO {$table}";
        return true;
    }

    public function update($table, $data = array(), $where = array()) {
        global $scenario;
        $this->lastTable = $table;
        $this->queries[] = "UPDATE {$table}";
        if (!empty($scenario['fail_update'])) {
            $this->transStatus = false;
            return false;
        }
        return true;
    }

    public function update_batch($table, $data = array(), $key = 'id') {
        $this->lastTable = $table;
        $this->queries[] = "UPDATE BATCH {$table}";
        return true;
    }

    public function delete($table, $where = array()) {
        global $scenario;
        $this->lastTable = $table;
        $this->queries[] = "DELETE FROM {$table}";
        if (!empty($scenario['fail_delete'])) {
            $this->transStatus = false;
            return false;
        }
        return true;
    }

    public function error() {
        return array('code' => 0, 'message' => '');
    }

    public function last_query() {
        return end($this->queries) ?: 'SELECT 1';
    }

    public function query($sql, $binds = false) {
        $this->queries[] = $sql;
        return new ExpenseBatch7Result('default', 'query', array('sql' => $sql));
    }
}

class ExpenseBatch7Model {
    public function get_data_transport_input() {
        echo json_encode(array(
            'draw' => 1,
            'recordsTotal' => 1,
            'recordsFiltered' => 1,
            'data' => array(
                array(
                    'no' => 1,
                    'no_doc' => 'TRP-2026-001',
                    'tgl_doc' => '2026-10-15',
                    'nama' => 'Budi Santoso',
                    'keperluan' => 'Meeting Klien',
                    'total' => '150,000',
                    'status' => 'Draft',
                    'action' => '<button>Edit</button>'
                )
            )
        ));
    }

    public function get_data_transport_req_fin_list() {
        echo json_encode(array(
            'draw' => 1,
            'recordsTotal' => 1,
            'recordsFiltered' => 1,
            'data' => array(
                array(
                    'no' => 1,
                    'no_doc' => 'TRP-REQ-2026-001',
                    'tgl_doc' => '2026-10-15',
                    'nama' => 'Budi Santoso',
                    'keperluan' => 'Operasional',
                    'total' => '250,000',
                    'status' => 'Pending Approval Finance',
                    'action' => '<button>Approve</button>'
                )
            )
        ));
    }

    public function get_data_transport_req() {
        echo json_encode(array(
            'draw' => 1,
            'recordsTotal' => 1,
            'recordsFiltered' => 1,
            'data' => array(
                array(
                    'no' => 1,
                    'no_doc' => 'TRP-REQ-2026-001',
                    'tgl_doc' => '2026-10-15',
                    'nama' => 'Budi Santoso',
                    'keperluan' => 'Operasional',
                    'total' => '250,000',
                    'status' => 'Pending Approval Mgmt',
                    'action' => '<button>View</button>'
                )
            )
        ));
    }
}

class ExpenseBatch7AllModel {
    public function dataUpdate($table, $data, $where) {
        global $scenario;
        if (!empty($scenario['fail_update'])) {
            return false;
        }
        return true;
    }
}

class ExpenseBatch7Auth {
    public function user_id() { return 10; }
    public function user_name() { return 'budi_santoso'; }
}

class ExpenseBatch7Session {
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

class ExpenseBatch7Template {
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

class ExpenseBatch7Input {
    public $values = array();

    public function post($key = null) {
        if ($key === null) {
            return $this->values;
        }
        return isset($this->values[$key]) ? $this->values[$key] : null;
    }
}

class ExpenseBatch7Load {
    public function library($lib) {}
    public function helper($h) {}
    public function model($m) {}
}

function expense_b7_run($method, $options = array(), $post = array(), $args = array()) {
    global $scenario, $events, $ci;
    $scenario = $options;
    $events = array();
    $GLOBALS['scenarioCount']++;

    $recorder = new Module_audit_recorder();

    $ci = (new ReflectionClass('Expense'))->newInstanceWithoutConstructor();
    $ci->module_audit_recorder = $recorder;
    $ci->db = new ExpenseBatch7Db();
    $ci->input = new ExpenseBatch7Input();
    $ci->auth = new ExpenseBatch7Auth();
    $ci->session = new ExpenseBatch7Session();
    $ci->template = new ExpenseBatch7Template();
    $ci->load = new ExpenseBatch7Load();
    $ci->Expense_model = new ExpenseBatch7Model();
    $ci->All_model = new ExpenseBatch7AllModel();

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
    list($e, $out) = expense_b7_run('del_detail_kasbon_non_po', array('writer_exception' => true), array('id_detail' => 55, 'id_kasbon_non_po' => 'KB-2026-001'));
    exit($e['status'] === 1 ? 0 : 1);
}

// -------------------------------------------------------------
// 1. del_detail_kasbon_non_po()
// -------------------------------------------------------------
// A. Normal delete
list($e, $out) = expense_b7_run('del_detail_kasbon_non_po', array(), array('id_detail' => 55, 'id_kasbon_non_po' => 'KB-2026-001'));
check($e['status'] === 1, 'del_detail_kasbon_non_po: status 1');
check($e['context']['data']['outcome'] === 'deleted', 'del_detail_kasbon_non_po: outcome deleted');
check($e['context']['data']['id_detail'] == 55, 'del_detail_kasbon_non_po: id_detail matched');
check($e['context']['data']['id_kasbon_non_po'] === 'KB-2026-001', 'del_detail_kasbon_non_po: id_kasbon_non_po matched');

// B. Commit failure
list($e, $out) = expense_b7_run('del_detail_kasbon_non_po', array('trans_commit_failure' => true), array('id_detail' => 55, 'id_kasbon_non_po' => 'KB-2026-001'));
check($e['status'] === 0, 'del_detail_kasbon_non_po (commit fail): status 0');
check($e['context']['data']['outcome'] === 'delete_failed', 'del_detail_kasbon_non_po (commit fail): outcome delete_failed');

// C. Trans status failure
list($e, $out) = expense_b7_run('del_detail_kasbon_non_po', array('trans_status_failure' => true), array('id_detail' => 55, 'id_kasbon_non_po' => 'KB-2026-001'));
check($e['status'] === 0, 'del_detail_kasbon_non_po (status fail): status 0');
check($e['context']['data']['outcome'] === 'delete_failed', 'del_detail_kasbon_non_po (status fail): outcome delete_failed');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e, $out) = expense_b7_run('del_detail_kasbon_non_po', array($writer => true), array('id_detail' => 55, 'id_kasbon_non_po' => 'KB-2026-001'));
    check($e['status'] === 1, 'del_detail_kasbon_non_po: unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 2. add_kasbon_pr()
// -------------------------------------------------------------
// A. Normal found
list($e, $out) = expense_b7_run('add_kasbon_pr', array(), array('no_doc' => 'KB-2026-001'));
check($e['status'] === 1, 'add_kasbon_pr: status 1');
check($e['context']['data']['outcome'] === 'found', 'add_kasbon_pr: outcome found');
check($e['context']['data']['no_doc'] === 'KB-2026-001', 'add_kasbon_pr: no_doc matched');
$resp = json_decode($out, true);
check($resp['no_doc'] === 'KB-2026-001', 'add_kasbon_pr: json no_doc matched');
check($resp['keperluan'] === 'Beli ATK Kantor', 'add_kasbon_pr: json keperluan matched');
check($resp['jumlah_kasbon'] == 1500000, 'add_kasbon_pr: json jumlah_kasbon matched');

// B. Missing / Not found record
list($e, $out) = expense_b7_run('add_kasbon_pr', array('missing_record' => true), array('no_doc' => 'KB-NOT-FOUND'));
check($e['status'] === 0, 'add_kasbon_pr (not found): status 0');
check($e['context']['data']['outcome'] === 'not_found', 'add_kasbon_pr (not found): outcome not_found');
$resp = json_decode($out, true);
check($resp['no_doc'] === '', 'add_kasbon_pr (not found): json empty no_doc');
check($resp['jumlah_kasbon'] === 0, 'add_kasbon_pr (not found): json zero jumlah');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e, $out) = expense_b7_run('add_kasbon_pr', array($writer => true), array('no_doc' => 'KB-2026-001'));
    check($e['status'] === 1, 'add_kasbon_pr: unaffected by ' . $writer);
    $resp = json_decode($out, true);
    check($resp['no_doc'] === 'KB-2026-001', 'add_kasbon_pr: response intact on ' . $writer);
}

// -------------------------------------------------------------
// 3. add_ganti_expense()
// -------------------------------------------------------------
// A. Normal create
list($e, $out) = expense_b7_run('add_ganti_expense', array(), array('no_doc' => 'EXP-2026-001', 'tgl' => '2026-10-15'));
check($e['status'] === 1, 'add_ganti_expense: status 1');
check($e['context']['data']['outcome'] === 'created', 'add_ganti_expense: outcome created');
check($e['context']['data']['no_doc'] === 'EXP-2026-001', 'add_ganti_expense: no_doc matched');
$resp = json_decode($out, true);
check($resp['no_doc'] === 'EXP-2026-001', 'add_ganti_expense: json no_doc matched');
check($resp['id_detail'] == 101, 'add_ganti_expense: json id_detail matched');
check($resp['jumlah'] == 750000, 'add_ganti_expense: json jumlah matched');

// B. Missing expense record
list($e, $out) = expense_b7_run('add_ganti_expense', array('missing_record' => true), array('no_doc' => 'EXP-NOT-FOUND'));
check($e['status'] === 0, 'add_ganti_expense (missing): status 0');
check($e['context']['data']['outcome'] === 'not_found', 'add_ganti_expense (missing): outcome not_found');
$resp = json_decode($out, true);
check($resp['jumlah'] === 0, 'add_ganti_expense (missing): zero jumlah');
check($resp['id_detail'] === null, 'add_ganti_expense (missing): null id_detail');

// C. Commit failure
list($e, $out) = expense_b7_run('add_ganti_expense', array('trans_commit_failure' => true), array('no_doc' => 'EXP-2026-001'));
check($e['status'] === 0, 'add_ganti_expense (commit fail): status 0');
check($e['context']['data']['outcome'] === 'insert_failed', 'add_ganti_expense (commit fail): outcome insert_failed');

// D. Trans status failure
list($e, $out) = expense_b7_run('add_ganti_expense', array('trans_status_failure' => true), array('no_doc' => 'EXP-2026-001'));
check($e['status'] === 0, 'add_ganti_expense (status fail): status 0');
check($e['context']['data']['outcome'] === 'insert_failed', 'add_ganti_expense (status fail): outcome insert_failed');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e, $out) = expense_b7_run('add_ganti_expense', array($writer => true), array('no_doc' => 'EXP-2026-001'));
    check($e['status'] === 1, 'add_ganti_expense: unaffected by ' . $writer);
    $resp = json_decode($out, true);
    check($resp['no_doc'] === 'EXP-2026-001', 'add_ganti_expense: response intact on ' . $writer);
}

// -------------------------------------------------------------
// 4. refresh_list_kasbon_non_pr()
// -------------------------------------------------------------
// A. Normal with no_doc
list($e, $out) = expense_b7_run('refresh_list_kasbon_non_pr', array(), array('no_doc' => 'KB-EXCLUDE'));
check($e['status'] === 1, 'refresh_list_kasbon_non_pr (with no_doc): status 1');
check($e['context']['data']['outcome'] === 'rendered', 'refresh_list_kasbon_non_pr: outcome rendered');
check($e['context']['data']['row_count'] === 2, 'refresh_list_kasbon_non_pr: row_count 2');
check(strpos($out, 'KB-2026-001') !== false, 'refresh_list_kasbon_non_pr: HTML row 1 rendered');
check(strpos($out, 'KB-2026-002') !== false, 'refresh_list_kasbon_non_pr: HTML row 2 rendered');

// B. Without no_doc
list($e, $out) = expense_b7_run('refresh_list_kasbon_non_pr', array(), array('no_doc' => ''));
check($e['status'] === 1, 'refresh_list_kasbon_non_pr (empty no_doc): status 1');
check($e['context']['data']['row_count'] === 2, 'refresh_list_kasbon_non_pr: row_count 2');

// C. Empty result
list($e, $out) = expense_b7_run('refresh_list_kasbon_non_pr', array('empty_result' => true), array('no_doc' => ''));
check($e['status'] === 1, 'refresh_list_kasbon_non_pr (empty result): status 1');
check($e['context']['data']['row_count'] === 0, 'refresh_list_kasbon_non_pr: row_count 0');
check(empty($out), 'refresh_list_kasbon_non_pr: empty html output');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e, $out) = expense_b7_run('refresh_list_kasbon_non_pr', array($writer => true), array('no_doc' => 'KB-2026-001'));
    check($e['status'] === 1, 'refresh_list_kasbon_non_pr: unaffected by ' . $writer);
    check(strpos($out, 'KB-2026-001') !== false, 'refresh_list_kasbon_non_pr: HTML intact on ' . $writer);
}

// -------------------------------------------------------------
// 5. refresh_list_expense_kembalian()
// -------------------------------------------------------------
// A. Normal render
list($e, $out) = expense_b7_run('refresh_list_expense_kembalian', array(), array('no_doc' => 'EXP-2026-001'));
check($e['status'] === 1, 'refresh_list_expense_kembalian: status 1');
check($e['context']['data']['outcome'] === 'rendered', 'refresh_list_expense_kembalian: outcome rendered');
check($e['context']['data']['row_count'] === 1, 'refresh_list_expense_kembalian: row_count 1');
check(strpos($out, 'EXP-2026-001') !== false, 'refresh_list_expense_kembalian: HTML row rendered');
check(strpos($out, 'add_ganti_expense') !== false, 'refresh_list_expense_kembalian: action button rendered');

// B. Empty result
list($e, $out) = expense_b7_run('refresh_list_expense_kembalian', array('empty_result' => true), array('no_doc' => ''));
check($e['status'] === 1, 'refresh_list_expense_kembalian (empty): status 1');
check($e['context']['data']['row_count'] === 0, 'refresh_list_expense_kembalian: row_count 0');
check(empty($out), 'refresh_list_expense_kembalian: empty html');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e, $out) = expense_b7_run('refresh_list_expense_kembalian', array($writer => true), array('no_doc' => 'EXP-2026-001'));
    check($e['status'] === 1, 'refresh_list_expense_kembalian: unaffected by ' . $writer);
    check(strpos($out, 'EXP-2026-001') !== false, 'refresh_list_expense_kembalian: HTML intact on ' . $writer);
}

// -------------------------------------------------------------
// 6. del_detail_kembalian_expense()
// -------------------------------------------------------------
// A. Normal delete
list($e, $out) = expense_b7_run('del_detail_kembalian_expense', array(), array('id_detail' => 77, 'id_expense_kembalian' => 'EXP-KMB-001'));
check($e['status'] === 1, 'del_detail_kembalian_expense: status 1');
check($e['context']['data']['outcome'] === 'deleted', 'del_detail_kembalian_expense: outcome deleted');
check($e['context']['data']['id_detail'] == 77, 'del_detail_kembalian_expense: id_detail matched');
check($e['context']['data']['id_expense_kembalian'] === 'EXP-KMB-001', 'del_detail_kembalian_expense: id_expense_kembalian matched');

// B. Commit failure
list($e, $out) = expense_b7_run('del_detail_kembalian_expense', array('trans_commit_failure' => true), array('id_detail' => 77, 'id_expense_kembalian' => 'EXP-KMB-001'));
check($e['status'] === 0, 'del_detail_kembalian_expense (commit fail): status 0');
check($e['context']['data']['outcome'] === 'delete_failed', 'del_detail_kembalian_expense (commit fail): outcome delete_failed');

// C. Trans status failure
list($e, $out) = expense_b7_run('del_detail_kembalian_expense', array('trans_status_failure' => true), array('id_detail' => 77, 'id_expense_kembalian' => 'EXP-KMB-001'));
check($e['status'] === 0, 'del_detail_kembalian_expense (status fail): status 0');
check($e['context']['data']['outcome'] === 'delete_failed', 'del_detail_kembalian_expense (status fail): outcome delete_failed');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e, $out) = expense_b7_run('del_detail_kembalian_expense', array($writer => true), array('id_detail' => 77, 'id_expense_kembalian' => 'EXP-KMB-001'));
    check($e['status'] === 1, 'del_detail_kembalian_expense: unaffected by ' . $writer);
}

// -------------------------------------------------------------
// 7. copy_pr_doc()
// -------------------------------------------------------------
// A. Rutin non planning PR
list($e, $out) = expense_b7_run('copy_pr_doc', array(), array('no_pr' => 'PR-RUTIN-001'));
check($e['status'] === 1, 'copy_pr_doc (rutin): status 1');
check($e['context']['data']['source_type'] === 'rutin_non_planning', 'copy_pr_doc: source_type rutin_non_planning');
check($e['context']['data']['outcome'] === 'found', 'copy_pr_doc: outcome found');
$resp = json_decode($out, true);
check($resp['file_name'] === 'nota_dinas.pdf', 'copy_pr_doc: file_name matched');
check($resp['doc_file'] === 'assets/pr/nota_dinas.pdf', 'copy_pr_doc: doc_file matched');
check($resp['to_doc_file'] === 'assets/expense/nota_dinas.pdf', 'copy_pr_doc: to_doc_file matched');
check(count($resp['attachments']) === 1, 'copy_pr_doc: attachments count 1');

// B. Asset PR
list($e, $out) = expense_b7_run('copy_pr_doc', array('empty_pr_dept' => true), array('no_pr' => 'PR-ASSET-001'));
check($e['status'] === 1, 'copy_pr_doc (asset): status 1');
check($e['context']['data']['source_type'] === 'asset', 'copy_pr_doc: source_type asset');
check($e['context']['data']['outcome'] === 'found', 'copy_pr_doc: outcome found');
$resp = json_decode($out, true);
check($resp['file_name'] === 'surat_pr_asset.pdf', 'copy_pr_doc (asset): file_name matched');
check($resp['doc_file'] === 'uploads/pr_asset/surat_pr_asset.pdf', 'copy_pr_doc (asset): doc_file matched');

// C. Not found PR
list($e, $out) = expense_b7_run('copy_pr_doc', array('empty_pr_dept' => true, 'empty_pr_asset' => true), array('no_pr' => 'PR-NOT-FOUND'));
check($e['status'] === 1, 'copy_pr_doc (not found): status 1');
check($e['context']['data']['source_type'] === 'none', 'copy_pr_doc (not found): source_type none');
check($e['context']['data']['outcome'] === 'not_found', 'copy_pr_doc (not found): outcome not_found');
$resp = json_decode($out, true);
check($resp['file_name'] === '', 'copy_pr_doc (not found): empty file_name');
check(empty($resp['attachments']), 'copy_pr_doc (not found): empty attachments');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e, $out) = expense_b7_run('copy_pr_doc', array($writer => true), array('no_pr' => 'PR-RUTIN-001'));
    check($e['status'] === 1, 'copy_pr_doc: unaffected by ' . $writer);
    $resp = json_decode($out, true);
    check($resp['file_name'] === 'nota_dinas.pdf', 'copy_pr_doc: response intact on ' . $writer);
}

// -------------------------------------------------------------
// 8. get_data_transport_input()
// -------------------------------------------------------------
list($e, $out) = expense_b7_run('get_data_transport_input', array(), array('draw' => 1));
check($e['status'] === 1, 'get_data_transport_input: status 1');
check($e['context']['data']['page'] === 'transport_input', 'get_data_transport_input: page transport_input');
check($e['context']['data']['outcome'] === 'rendered', 'get_data_transport_input: outcome rendered');
$resp = json_decode($out, true);
check($resp['recordsTotal'] === 1, 'get_data_transport_input: recordsTotal 1');
check(count($resp['data']) === 1, 'get_data_transport_input: data count 1');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e, $out) = expense_b7_run('get_data_transport_input', array($writer => true), array('draw' => 1));
    check($e['status'] === 1, 'get_data_transport_input: unaffected by ' . $writer);
    $resp = json_decode($out, true);
    check($resp['recordsTotal'] === 1, 'get_data_transport_input: response intact on ' . $writer);
}

// -------------------------------------------------------------
// 9. get_data_transport_req_fin_list()
// -------------------------------------------------------------
list($e, $out) = expense_b7_run('get_data_transport_req_fin_list', array(), array('draw' => 1));
check($e['status'] === 1, 'get_data_transport_req_fin_list: status 1');
check($e['context']['data']['page'] === 'transport_req_fin_list', 'get_data_transport_req_fin_list: page transport_req_fin_list');
check($e['context']['data']['outcome'] === 'rendered', 'get_data_transport_req_fin_list: outcome rendered');
$resp = json_decode($out, true);
check($resp['recordsTotal'] === 1, 'get_data_transport_req_fin_list: recordsTotal 1');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e, $out) = expense_b7_run('get_data_transport_req_fin_list', array($writer => true), array('draw' => 1));
    check($e['status'] === 1, 'get_data_transport_req_fin_list: unaffected by ' . $writer);
    $resp = json_decode($out, true);
    check($resp['recordsTotal'] === 1, 'get_data_transport_req_fin_list: response intact on ' . $writer);
}

// -------------------------------------------------------------
// 10. get_data_transport_req()
// -------------------------------------------------------------
list($e, $out) = expense_b7_run('get_data_transport_req', array(), array('draw' => 1));
check($e['status'] === 1, 'get_data_transport_req: status 1');
check($e['context']['data']['page'] === 'transport_req', 'get_data_transport_req: page transport_req');
check($e['context']['data']['outcome'] === 'rendered', 'get_data_transport_req: outcome rendered');
$resp = json_decode($out, true);
check($resp['recordsTotal'] === 1, 'get_data_transport_req: recordsTotal 1');

foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e, $out) = expense_b7_run('get_data_transport_req', array($writer => true), array('draw' => 1));
    check($e['status'] === 1, 'get_data_transport_req: unaffected by ' . $writer);
    $resp = json_decode($out, true);
    check($resp['recordsTotal'] === 1, 'get_data_transport_req: response intact on ' . $writer);
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
        expense_b7_run($pendingMethod, array('pending' => true, 'expected_exception' => true), array(), $dummyArgs);
    }
}

// -------------------------------------------------------------
// 12. Subprocess Shutdown Runner
// -------------------------------------------------------------
$cmd = escapeshellcmd(PHP_BINARY) . ' ' . escapeshellarg(__FILE__) . ' --subprocess-shutdown 2>&1';
exec($cmd, $subOutput, $subExitCode);
check($subExitCode === 0, 'Subprocess shutdown exit code 0');

echo "PASS Expense batch 7: {$scenarioCount} fixture scenarios + 1 shutdown subprocess" . PHP_EOL;
