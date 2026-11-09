<?php
if (!defined('AUDIT_FIXTURE_ONLY')) { define('AUDIT_FIXTURE_ONLY', true); }
if (!defined('HRIS')) { define('HRIS', 'fixture_hris'); }

require dirname(__DIR__, 2) . '/request_payment/tests/Audit_test.php';

if (!class_exists('BF_Model')) { class BF_Model {} }
if (!class_exists('CI_Model')) { class CI_Model {} }

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

if (!function_exists('base_url')) {
    function base_url($path = '') { return '/base/' . $path; }
}
if (!function_exists('has_permission')) {
    function has_permission($perm = '') {
        return empty($GLOBALS['scenario']['no_manage']) || strpos($perm, 'Manage') === false;
    }
}

require dirname(__DIR__) . '/models/Pengajuan_rutin_model.php';
require dirname(__DIR__, 2) . '/all/models/All_model.php';
require dirname(__DIR__) . '/controllers/Pengajuan_rutin.php';

/** Satu baris item dalam bentuk yang dikembalikan Pengajuan_rutin_model (lihat ItemRow). */
function rutin_item($letter = 'W', $extra = array()) {
    return array_merge(array(
        'id' => 1, 'no_item' => 'PI-2026-00001', 'no_batch' => 'BP-2611-001', 'id_budget' => 10, 'desc' => 'Budget 1',
        'status' => $letter, 'item_status' => $letter === 'W' ? 0 : ($letter === 'R' ? 8 : 1), 'nominal' => 100.0, 'ket' => '',
        'doc_file' => '', 'note' => $letter === 'R' ? 'Salah nominal' : '', 'by' => $letter === 'W' ? '' : 'Direktur', 'at' => $letter === 'W' ? '' : '2026-11-04 09:30:00',
        'ref' => '', 'resub' => '', 'closed_note' => '', 'closed_at' => null, 'periode' => '2026-11', 'tgl_batch' => '2026-11-03',
        'tanggal' => '25', 'budget' => 100, 'coa' => '1101-01', 'bank_id' => 'BCA', 'accnumber' => '123', 'accname' => 'Vendor', 'm_tipe' => 'bulan',
    ), $extra);
}

class RutinResult {
    private $group;
    private $table;
    private $params;

    public function __construct($group, $table, $params = array()) {
        $this->group = $group;
        $this->table = $table;
        $this->params = $params;
    }

    public function row() {
        global $scenario;
        if (!empty($scenario['empty']) || !empty($scenario['user_not_found'])) {
            return null;
        }

        if (strpos($this->table, 'ms_periodik_counter') !== false) {
            $kode = $this->params[1][0] ?? 'X';
            $GLOBALS['rutinCounter'][$kode] = ($GLOBALS['rutinCounter'][$kode] ?? 0) + 1;
            return (object) array('last_no' => $GLOBALS['rutinCounter'][$kode]);
        }

        if ($this->table === 'users') {
            if (!empty($scenario['user_no_dept'])) {
                return (object) array('user_dept' => null, 'emp_dept' => null);
            }
            if (!empty($scenario['user_emp_dept'])) {
                return (object) array('user_dept' => null, 'emp_dept' => 'DEP-EMP');
            }
            return (object) array('user_dept' => 'DEP-1', 'emp_dept' => null);
        }

        if (strpos($this->table, 'tr_pengajuan_rutin_detail') !== false) {
            return (object) array(
                'id' => 1, 'no_doc' => 'BP-2611-001', 'no_item' => 'PI-2026-00001', 'id_budget' => 10, 'coa' => '1101-01',
                'nama' => 'Budget Item', 'nilai' => 100, 'tanggal' => '2026-10-14', 'keterangan' => 'Detail Item Note',
                'item_status' => $scenario['db_item_status'] ?? 8, 'diajukan_ulang_sebagai' => !empty($scenario['resub']) ? 'PI-2026-00002' : null,
                'closed_at' => !empty($scenario['closed']) ? '2026-11-05 10:00:00' : null, 'revisi_dari' => '', 'item_reject_ket' => 'Salah nominal',
            );
        }

        if (strpos($this->table, 'tr_pengajuan_rutin') !== false) {
            if (!empty($scenario['missing_record'])) {
                return null;
            }
            return (object) array(
                'id' => 1,
                'no_doc' => 'BP-2611-001',
                'departement' => !empty($scenario['dept_mismatch']) ? 'OTHER-DEP' : 'DEP-1',
                'status' => 0,
                'sts_reject' => 0,
                'tanggal_doc' => '2026-11-03',
                'reject_ket' => null
            );
        }

        if ($this->table === 'ms_budget_rutin') {
            return (object) array('departement' => 'DEP-1', 'id' => 10, 'tipe' => 'bulan');
        }

        return (object) array('id' => 'DEP-1', 'name' => 'Dept 1', 'company_id' => 1, 'nm_dept' => 'Dept 1', 'nm_comp' => 'Comp 1');
    }

    public function result() {
        global $scenario;
        if (!empty($scenario['empty'])) {
            return array();
        }
        $row = $this->row();
        return $row ? array($row) : array();
    }

    public function num_rows() {
        return count($this->result());
    }
}

class RutinDb extends FixtureDb {
    public $group;
    public $table = '';
    public $queryError = 0;
    public $writes = array();
    public $committed = false;
    public $selects = array();
    public $wheres = array();

    public function __construct($group = 'business') {
        $this->group = $group;
    }

    public function select($cols) {
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

    public function where($k, $v = null) {
        $this->wheres[] = array($k, $v);
        return $this;
    }

    public function group_by($col) {
        return $this;
    }

    public function order_by($col, $dir = '') {
        return $this;
    }

    public function insert_id() {
        return 5;
    }

    public function affected_rows() {
        global $scenario;
        return !empty($scenario['affected_zero']) ? 0 : 1;
    }

    public function error() {
        return array('code' => $this->queryError);
    }

    public function __call($name, $args) {
        global $scenario;
        $this->calls[] = $name;

        if (in_array($name, array('get', 'get_where', 'query'), true)) {
            $GLOBALS['pengajuanQueryCount']++;
            if (($scenario['abort_query'] ?? 0) === $GLOBALS['pengajuanQueryCount']) {
                exit;
            }
            $targetTable = $args[0] ?? $this->table;
            $isHrisQuery = ($this->group === 'hris' || strpos($targetTable, 'departments') !== false);

            if ($isHrisQuery && !empty($scenario['fail_hris_query'])) {
                $this->queryError = 999;
                return !empty($scenario['query_false']) ? false : new RutinResult($this->group, $targetTable);
            }

            if (!$isHrisQuery && !empty($scenario['fail_query'])) {
                $this->queryError = 999;
                return !empty($scenario['query_false']) ? false : new RutinResult($this->group, $targetTable);
            }

            $this->queryError = 0;
            return new RutinResult($this->group, $targetTable, $args);
        }

        if (in_array($name, array('insert', 'insert_batch', 'update', 'delete'), true)) {
            $this->writes[] = array($name, $args);
            if (!empty($scenario['save_exception'])) {
                throw new Exception('Simulated DB write failure');
            }
            if ($name === 'update' && !empty($scenario['update_false'])) {
                return false;
            }
            return true;
        }

        if ($name === 'trans_begin') {
            return empty($scenario['trans_begin_failure']);
        }

        if ($name === 'trans_status') {
            return empty($scenario['transaction_failure']);
        }

        if ($name === 'trans_commit') {
            if (!empty($scenario['abort_commit'])) {
                exit;
            }
            $this->committed = empty($scenario['trans_commit_failure']);
            return $this->committed;
        }

        if ($name === 'trans_rollback') {
            $this->committed = false;
            return true;
        }

        if ($name === 'trans_complete') {
            return true;
        }

        if ($name === 'table_exists') {
            return true;
        }

        if ($name === 'last_query') {
            return 'SELECT * FROM ' . $this->table;
        }

        return $this;
    }
}

class RutinLoader extends FixtureLoad {
    public $db;
    public $hris;

    public function __construct($db, $hris) {
        $this->db = $db;
        $this->hris = $hris;
    }

    public function database($group, $separate = false) {
        if ($group === 'hris') {
            return $this->hris;
        }
        return $this->db;
    }

    public function model($models) {}

    public function view($name, $data = array()) {
        $GLOBALS['rutinView'] = array($name, $data);
        echo 'VIEW:' . $name;
    }

    public function library($name) {
        global $ci;
        if (!isset($ci->module_audit_recorder)) {
            $ci->module_audit_recorder = new Module_audit_recorder();
        }
    }
}

class RutinTemplate extends FixtureTemplate {
    public $messages = array();
    public $data = array();
    public $view = '';

    public function set_message($msg, $type = 'info') {
        $this->messages[] = array('msg' => $msg, 'type' => $type);
    }

    public function set($key, $val = null) {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $val;
        }
    }

    public function title($t = '') {}
    public function page_icon($p = '') {}

    public function render($view, $data = array()) {
        global $scenario;
        $this->view = $view;
        if (!empty($scenario['render_exception'])) {
            throw new RuntimeException('Render exception simulated');
        }
    }
}

class RutinOutput {
    public $status_header = 200;

    public function set_status_header($code) {
        $this->status_header = $code;
        return $this;
    }

    public function set_content_type($type) {
        return $this;
    }
}

class RutinAuth extends FixtureAuth {
    public $user_id = 7;

    public function user_id() {
        return $this->user_id;
    }

    public function is_admin() {
        global $scenario;
        return !empty($scenario['is_admin']);
    }
}

class RutinModel extends Pengajuan_rutin_model {
    public $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function GetDataPengajuanRutin($id = '') {
        global $scenario;
        $this->db->get_where('tr_pengajuan_rutin', array('id' => $id));
        if (!empty($scenario['missing_record'])) {
            return false;
        }
        return (object) array(
            'id' => $id,
            'no_doc' => 'BP-2611-001',
            'departement' => !empty($scenario['dept_mismatch']) ? 'OTHER-DEP' : 'DEP-1',
            'status' => 0
        );
    }

    /** Satu batch BP-2611-001 berisi satu item; huruf status item diatur lewat $scenario['item_letter']. */
    public function GetBatchesWithItems($dept = null, $noDoc = null) {
        global $scenario;
        $this->db->get('tr_pengajuan_rutin');
        if (!empty($scenario['query_error_detail'])) {
            return false;
        }
        if (!empty($scenario['missing_record'])) {
            return array();
        }
        $items = array(rutin_item($scenario['item_letter'] ?? 'W', array(
            'resub' => !empty($scenario['resub']) ? 'PI-2026-00002' : '', 'ref' => $scenario['item_ref'] ?? '',
            'no_item' => !empty($scenario['legacy_item']) ? 'BP-2611-001' : 'PI-2026-00001', // batch lama: nomor item = nomor batch
        )));
        if (!empty($scenario['two_items'])) {
            $items[] = rutin_item('W', array('id' => 2, 'no_item' => 'PI-2026-00002', 'id_budget' => 11, 'desc' => 'Budget 2', 'nominal' => 50.0));
        }
        return array('BP-2611-001' => array('id' => 1, 'no' => 'BP-2611-001',
            'dept' => !empty($scenario['dept_mismatch']) ? 'OTHER-DEP' : 'DEP-1', 'date' => '2026-11-03', 'periode' => '2026-11', 'created_by' => 7, 'items' => $items));
    }

    public function GetRpStates(array $noItems) {
        return array();
    }

    public function CountBatchViews($dept = null) {
        $this->db->get('tr_pengajuan_rutin');
        return !empty($GLOBALS['scenario']['query_error_detail']) ? false : array('wait' => 1, 'rec' => 0);
    }

    /** Satu halaman daftar: batch (mode batch) atau pasangan batch+item (mode item). */
    public function ListPage($dept, $view, $mode, $filter, $search, $start, $length) {
        $all = $this->GetBatchesWithItems($dept);
        if ($all === false) {
            return false;
        }
        $rows = array();
        foreach ($all as $b) {
            if ($mode === 'item') {
                foreach ($b['items'] as $i) { $rows[] = array('batch' => $b, 'item' => $i); }
            } else {
                $rows[] = $b;
            }
        }
        $GLOBALS['rutinListArgs'] = array($dept, $view, $mode, $filter, $search, $start, $length);
        return array(count($rows), count($rows), $rows);
    }

    /** Item yang sudah diputuskan (huruf status bukan W) sebagai pasangan batch+item. */
    public function ListDecidedItems($search, $start, $length) {
        $all = $this->GetBatchesWithItems(null);
        if ($all === false) {
            return false;
        }
        $rows = array();
        foreach ($all as $b) {
            foreach ($b['items'] as $i) {
                if ($i['status'] !== 'W') { $rows[] = array('batch' => $b, 'item' => $i); }
            }
        }
        $GLOBALS['rutinDecidedArgs'] = array($search, $start, $length);
        return array(count($rows), count($rows), $rows);
    }

    public function GetLastBank($idBudget) {
        return (object) array('bank_id' => 'BCA', 'accnumber' => '123', 'accname' => 'Vendor');
    }

    /** Master 10 (dan 11 bila two_items). locked_master/rejected_master mengisi riwayat item di periode 2026-11. */
    public function GetMasterWithItems($dept, $excludeNoDoc = null) {
        global $scenario;
        $this->db->get('ms_budget_rutin');
        if (!empty($scenario['query_error_budget'])) {
            return false;
        }
        $master = array('id' => 10, 'departement' => $dept, 'coa' => '1101-01', 'nama' => 'Budget 1', 'tipe' => 'bulan', 'tanggal' => '25',
            'nilai' => 100, 'aktif' => 1, 'masa_berlaku' => 0, 'masa_awal' => 0, 'items' => array());
        if (!empty($scenario['locked_master'])) {
            $master['items'][] = rutin_item('W', array('no_item' => 'PI-2026-00007', 'no_batch' => 'BP-2611-009'));
        }
        if (!empty($scenario['rejected_master'])) {
            $master['items'][] = rutin_item('R', array('no_item' => 'PI-2026-00005', 'no_batch' => 'BP-2611-004'));
        }
        if (!empty($scenario['closed_master'])) {
            $master['items'][] = rutin_item('R', array('no_item' => 'PI-2026-00005', 'closed_at' => '2026-11-05 10:00:00', 'closed_note' => 'batal'));
        }
        if (!empty($scenario['inactive_master'])) {
            $master['aktif'] = 0;
        }
        $out = array($master);
        if (!empty($scenario['two_items'])) {
            $out[] = array_merge($master, array('id' => 11, 'nama' => 'Budget 2', 'nilai' => 50, 'items' => array())); // tanpa riwayat: tetap bebas walau master 10 terkunci
        }
        return $out;
    }
}

class RutinAllModel extends All_model {
    public $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function GetDeptCombo($key = '') {
        return array('DEP-1' => 'Department 1', 'OTHER-DEP' => 'Department Other');
    }

    public function GetAutoGenerate($format) {
        return 'DOC-GEN-001';
    }

    public function dataSave($table, $data) {
        return 1;
    }

    public function dataUpdate($table, $data, $where) {
        return 1;
    }

    public function dataDelete($table, $where) {
        return 1;
    }
}

class RutinTestController extends Pengajuan_rutin {
    public function setHris($hris) {
        $this->hris = $hris;
    }

    public function getHris() {
        return $this->hris;
    }
}

function pengajuan_run($method, $options = array(), $post = array(), $args = array()) {
    global $ci, $scenario, $events, $scenarioCount;
    $scenarioCount++;
    $scenario = $options;
    $events = array();
    $GLOBALS['pengajuanQueryCount'] = 0;
    $GLOBALS['redirectTarget'] = null;
    $GLOBALS['rutinCounter'] = array();
    $GLOBALS['rutinView'] = null;

    $ci = new RutinTestController();
    $db = new RutinDb('business');
    $hris = new RutinDb('hris');
    $ci->setHris($hris);
    $ci->db = $db;
    $ci->load = new RutinLoader($db, $hris);
    $ci->auth = new RutinAuth();
    $ci->template = new RutinTemplate();
    $ci->output = new RutinOutput();
    $ci->All_model = new RutinAllModel($db);
    $ci->Pengajuan_rutin_model = new RutinModel($db);
    $defaultPost = array(
        'departement' => 'DEP-1',
        'periode' => '2026-11',
        'id' => '',
        'biaya' => array('10' => '100'),
        'ket' => array('10' => 'catatan'),
        'password' => 'PRIVATE_PASSWORD',
        'credit_card' => 'PRIVATE_CC',
    );
    $ci->input->values = array_merge($defaultPost, $post);

    ob_start();
    try {
        $GLOBALS['pengajuanLastReturn'] = call_user_func_array(array($ci, $method), $args);
    } catch (Throwable $error) {
        if (isset($ci->module_audit_recorder)) {
            $ci->module_audit_recorder->finish_interrupted();
        }
        if (empty($options['expected_exception'])) {
            ob_end_clean();
            throw $error;
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
    check($events[0]['context']['module'] === 'pengajuan_rutin', 'Wrong module attribution: ' . ($events[0]['context']['module'] ?? 'none'));
    check($events[0]['context']['methode'] === $method, 'Wrong methode attribution: ' . ($events[0]['context']['methode'] ?? 'none'));
    check(strpos(json_encode($events), 'PRIVATE_PASSWORD') === false, 'PII leaked in audit event');
    check(strpos(json_encode($events), 'PRIVATE_CC') === false, 'PII leaked in audit event');

    return array($events[0], $output, $ci);
}

// Subprocess entrypoint
$child = $argv[1] ?? '';
if ($child === '--pengajuan1-abort') {
    pengajuan_run('close_item', array('abort_query' => 1), array('no_item' => 'PI-2026-00001', 'note' => 'batal'));
    exit;
}

if (defined('PENGAJUAN_FIXTURE_ONLY')) { return; }

$scenarioCount = 0;

// ==========================================
// 1. get_user_department
// ==========================================
// A. Resolved from user_dept
list($event) = pengajuan_run('get_user_department');
check($event['status'] === 1, 'get_user_department: status 1');
check($event['context']['data']['outcome'] === 'resolved', 'get_user_department: outcome resolved');
check($event['context']['data']['department_id'] === 'DEP-1', 'get_user_department: department_id matched');
check($GLOBALS['pengajuanLastReturn'] === 'DEP-1', 'get_user_department: return matched');

// B. Resolved from emp_dept
list($event) = pengajuan_run('get_user_department', array('user_emp_dept' => true));
check($event['status'] === 1, 'get_user_department (emp): status 1');
check($event['context']['data']['outcome'] === 'resolved', 'get_user_department (emp): outcome resolved');
check($event['context']['data']['department_id'] === 'DEP-EMP', 'get_user_department (emp): department_id matched');
check($GLOBALS['pengajuanLastReturn'] === 'DEP-EMP', 'get_user_department (emp): return matched');

// C. Unresolved (user has no department)
list($event) = pengajuan_run('get_user_department', array('user_no_dept' => true));
check($event['status'] === 1, 'get_user_department (no dept): status 1');
check($event['context']['data']['outcome'] === 'unresolved', 'get_user_department (no dept): outcome unresolved');
check(!isset($event['context']['data']['department_id']) || $event['context']['data']['department_id'] === null, 'get_user_department (no dept): null dept');

// D. Query error
list($event) = pengajuan_run('get_user_department', array('fail_query' => 1));
check($event['status'] === 0, 'get_user_department (query fail): status 0');
check($event['context']['data']['outcome'] === 'unresolved', 'get_user_department (query fail): outcome unresolved');

// E. Writer failure immunity
foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($event) = pengajuan_run('get_user_department', array($writer => true));
    check($GLOBALS['pengajuanLastReturn'] === 'DEP-1', 'get_user_department: return unaffected by writer error');
}

// ==========================================
// 2. index (daftar batch per item)
// ==========================================
// A. Admin view: semua departemen, tombol New aktif
list($event, , $ctrl) = pengajuan_run('index', array('is_admin' => true));
check($event['status'] === 1, 'index (admin): status 1');
check($event['context']['data']['outcome'] === 'rendered', 'index (admin): outcome rendered');
check($ctrl->template->view === 'list', 'index (admin): renders list');
check($ctrl->template->data['counts'] === array('wait' => 1, 'rec' => 0), 'index (admin): tab counts passed to view');
$dd = json_decode($ctrl->template->data['dd_items'], true);
check(count($dd) === 2 && $dd[0]['id'] === 'DEP-1' && $dd[0]['disabled'] === false && strpos($dd[0]['sub'], '1 item bisa diajukan') === 0, 'index (admin): department choices carry open item count');

// B. Non-admin view: hanya departemennya sendiri
list($event, , $ctrl) = pengajuan_run('index', array('is_admin' => false));
check($event['status'] === 1, 'index (non-admin): status 1');
check($event['context']['data']['outcome'] === 'rendered', 'index (non-admin): outcome rendered');
check($event['context']['data']['department_id'] === 'DEP-1', 'index (non-admin): department_id matched');
$dd = json_decode($ctrl->template->data['dd_items'], true);
check(count($dd) === 1 && $dd[0]['id'] === 'DEP-1', 'index (non-admin): only own department selectable');

// C. Business query error
list($event) = pengajuan_run('index', array('query_error_detail' => true));
check($event['status'] === 0, 'index (detail error): status 0');
check($event['context']['data']['outcome'] === 'query_error', 'index (detail error): outcome query_error');

// D. Writer resilience
foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($event) = pengajuan_run('index', array($writer => true));
    check($event['status'] === 1, 'index: writer resilience');
}

// E. data_list (DataTables serverside)
list($event, $output, $ctrl) = pengajuan_run('data_list', array('is_admin' => false), array('draw' => '3', 'view' => 'wait', 'mode' => 'batch', 'start' => '0', 'length' => '10', 'search' => array('value' => ' sewa ')));
$dt = json_decode($output, true);
check($event['status'] === 1 && $event['context']['data']['outcome'] === 'rendered', 'data_list: audited as rendered');
check($dt['draw'] === 3 && $dt['recordsTotal'] === 1 && $dt['recordsFiltered'] === 1 && count($dt['data']) === 1, 'data_list: DataTables envelope');
check($GLOBALS['rutinListArgs'] === array('DEP-1', 'wait', 'batch', 'all', 'sewa', 0, 10), 'data_list: non-admin locked to own department, search trimmed');
check($dt['data'][0][0] === 1 && $dt['data'][0][2] === 'BP-2611-001' && $dt['data'][0]['DT_RowAttr']['data-batch'] === 'BP-2611-001', 'data_list: batch row number, batch no, row attribute');
check(strpos($dt['data'][0][7], 'data-view="1"') !== false, 'data_list: Lihat action present');
list($event, $output) = pengajuan_run('data_list', array('is_admin' => true), array('view' => 'rec', 'mode' => 'item', 'filter' => 'R', 'start' => '20', 'length' => '10', 'draw' => '1'));
$dt = json_decode($output, true);
check($GLOBALS['rutinListArgs'] === array(null, 'rec', 'item', 'R', '', 20, 10), 'data_list: admin sees all departments, filter and paging forwarded');
check(count($dt['data'][0]) === 7 && $dt['data'][0][1] === '<span style="white-space:nowrap">BP-2611-001</span>', 'data_list: rec item row has no Keterangan column');
list($event, $output) = pengajuan_run('data_list', array('is_admin' => true), array('view' => 'wait', 'mode' => 'item'));
check(count(json_decode($output, true)['data'][0]) === 8, 'data_list: wait item row carries Keterangan column');
list($event, $output) = pengajuan_run('data_list', array('query_error_detail' => true), array('view' => 'wait', 'mode' => 'batch'));
check($event['status'] === 0 && isset(json_decode($output, true)['error']) && json_decode($output, true)['data'] === array(), 'data_list: query error reported and audited');

// ==========================================
// 3. create
// ==========================================
// A. Non-admin without department configured
list($event) = pengajuan_run('create', array('is_admin' => false, 'user_no_dept' => true));
check($event['status'] === 0, 'create (no dept): status 0');
check($event['context']['data']['outcome'] === 'dept_unconfigured', 'create (no dept): outcome dept_unconfigured');
check($GLOBALS['redirectTarget'] === 'pengajuan_rutin', 'create (no dept): redirected to pengajuan_rutin');

// B. Admin create: baris bebas
list($event, , $ctrl) = pengajuan_run('create', array('is_admin' => true), array(), array('DEP-1'));
check($event['status'] === 1, 'create (admin): status 1');
check($event['context']['data']['outcome'] === 'rendered', 'create (admin): outcome rendered');
check($ctrl->template->view === 'input_form' && $ctrl->template->data['type'] === 'add', 'create (admin): add form');
check(count($ctrl->template->data['rows']) === 1 && $ctrl->template->data['rows'][0]['st']['state'] === 'free', 'create (admin): open row is free');

// C. Non-admin create dikunci ke departemennya sendiri
list($event, , $ctrl) = pengajuan_run('create', array('is_admin' => false), array(), array('OTHER-DEP'));
check($event['status'] === 1, 'create (non-admin): status 1');
check($event['context']['data']['outcome'] === 'rendered', 'create (non-admin): outcome rendered');
check($ctrl->template->data['dept'] === 'DEP-1', 'create (non-admin): department forced to own');

// D. Status baris: terkunci, ditolak, ditutup, nonaktif
list(, , $ctrl) = pengajuan_run('create', array('is_admin' => true, 'locked_master' => true), array(), array('DEP-1'));
check($ctrl->template->data['rows'][0]['st']['state'] === 'lock' && $ctrl->template->data['rows'][0]['st']['lock']['no_item'] === 'PI-2026-00007', 'create: sudah diajukan -> lock dengan nomor item');
list(, , $ctrl) = pengajuan_run('create', array('is_admin' => true, 'locked_master' => true, 'two_items' => true), array(), array('DEP-1'));
check(array_column(array_column($ctrl->template->data['rows'], 'm'), 'id') === array(11, 10) && $ctrl->template->data['rows'][1]['st']['state'] === 'lock', 'create: item yang bisa diajukan tampil dulu, item terkunci di paling bawah');
list(, , $ctrl) = pengajuan_run('create', array('is_admin' => true, 'rejected_master' => true), array(), array('DEP-1'));
check($ctrl->template->data['rows'][0]['st']['state'] === 'back' && $ctrl->template->data['rows'][0]['st']['back']['no_item'] === 'PI-2026-00005', 'create: ditolak -> back');
list(, , $ctrl) = pengajuan_run('create', array('is_admin' => true, 'closed_master' => true), array(), array('DEP-1'));
check(!$ctrl->template->data['rows'] && $ctrl->template->data['hidden'][0]['st']['state'] === 'closed', 'create: item ditutup tidak tampil di form');
list(, , $ctrl) = pengajuan_run('create', array('is_admin' => true, 'inactive_master' => true), array(), array('DEP-1'));
check(!$ctrl->template->data['rows'] && $ctrl->template->data['hidden'][0]['st']['state'] === 'off', 'create: nonaktif tidak tampil di form');

// E. Periode dari query string dibatasi ke pilihan yang tersedia
list(, , $ctrl) = pengajuan_run('create', array('is_admin' => true), array(), array('DEP-1'));
check(array_key_exists($ctrl->template->data['periode'], $ctrl->template->data['period_options']), 'create: default period is one of the options');

// F. DB query error
list($event) = pengajuan_run('create', array('fail_query' => 1), array(), array('DEP-1'));
check($event['status'] === 0, 'create (query error): status 0');
check($event['context']['data']['outcome'] === 'query_error', 'create (query error): outcome query_error');

// G. Writer resilience
foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($event) = pengajuan_run('create', array($writer => true), array(), array('DEP-1'));
    check($event['status'] === 1, 'create: writer resilience');
}

// ==========================================
// 4. edit
// ==========================================
// A. Not found
list($event) = pengajuan_run('edit', array('missing_record' => true), array(), array('999'));
check($event['status'] === 0, 'edit (not found): status 0');
check($event['context']['data']['outcome'] === 'not_found', 'edit (not found): outcome not_found');
check($GLOBALS['redirectTarget'] === 'pengajuan_rutin', 'edit (not found): redirected');

// B. Non-admin unauthorized (department mismatch)
list($event) = pengajuan_run('edit', array('is_admin' => false, 'dept_mismatch' => true), array(), array('1'));
check($event['status'] === 0, 'edit (unauthorized): status 0');
check($event['context']['data']['outcome'] === 'unauthorized', 'edit (unauthorized): outcome unauthorized');
check($GLOBALS['redirectTarget'] === 'pengajuan_rutin', 'edit (unauthorized): redirected');

// C. Authorized edit: item milik batch tampil sebagai baris yang bisa diubah
list($event, , $ctrl) = pengajuan_run('edit', array('is_admin' => false), array(), array('1'));
check($event['status'] === 1, 'edit (authorized): status 1');
check($event['context']['data']['outcome'] === 'rendered', 'edit (authorized): outcome rendered');
check($event['context']['data']['no_doc'] === 'BP-2611-001', 'edit (authorized): no_doc matched');
check($event['context']['data']['department_id'] === 'DEP-1', 'edit (authorized): department_id matched');
check($ctrl->template->data['type'] === 'edit' && $ctrl->template->data['rows'][0]['mine']['no_item'] === 'PI-2026-00001', 'edit (authorized): existing item kept with its number');

// D. Batch sudah diputuskan tidak bisa diedit
list($event) = pengajuan_run('edit', array('item_letter' => 'A'), array(), array('1'));
check($event['status'] === 0, 'edit (decided): status 0');
check($event['context']['data']['outcome'] === 'already_decided', 'edit (decided): outcome already_decided');
check($GLOBALS['redirectTarget'] === 'pengajuan_rutin', 'edit (decided): redirected');

// E. Detail query error
list($event) = pengajuan_run('edit', array('query_error_detail' => true), array(), array('1'));
check($event['status'] === 0, 'edit (query error): status 0');
check($event['context']['data']['outcome'] === 'query_error', 'edit (query error): outcome query_error');

// F. Writer resilience
foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($event) = pengajuan_run('edit', array($writer => true), array(), array('1'));
    check($event['status'] === 1, 'edit: writer resilience');
}

// ==========================================
// 5. view (modal Lihat)
// ==========================================
// A. Not found
list($event) = pengajuan_run('view', array('missing_record' => true), array(), array('999'));
check($event['status'] === 0, 'view (not found): status 0');
check($event['context']['data']['outcome'] === 'not_found', 'view (not found): outcome not_found');

// B. Non-admin unauthorized when app != 'app'
list($event, $output) = pengajuan_run('view', array('is_admin' => false, 'dept_mismatch' => true), array(), array('1', ''));
check($event['status'] === 0, 'view (unauthorized): status 0');
check($event['context']['data']['outcome'] === 'unauthorized', 'view (unauthorized): outcome unauthorized');
check(strpos($output, 'VIEW:') === false, 'view (unauthorized): partial not rendered');

// C. Approval view with app = 'app' allows viewing regardless of dept mismatch, dengan tombol proses
list($event) = pengajuan_run('view', array('is_admin' => false, 'dept_mismatch' => true), array(), array('1', 'app'));
check($event['status'] === 1, 'view (approval override): status 1');
check($event['context']['data']['outcome'] === 'rendered', 'view (approval override): outcome rendered');
check($event['context']['data']['app'] === 'app', 'view (approval override): app context matched');
check($GLOBALS['rutinView'][0] === 'batch_detail' && $GLOBALS['rutinView'][1]['can_decide'] === true, 'view (approval override): can_decide for approver');

// D. Authorized view tanpa tombol proses
list($event) = pengajuan_run('view', array('is_admin' => false), array(), array('1', ''));
check($event['status'] === 1, 'view (authorized): status 1');
check($event['context']['data']['outcome'] === 'rendered', 'view (authorized): outcome rendered');
check($GLOBALS['rutinView'][1]['can_decide'] === false, 'view (authorized): no decide button outside approval');

// E. Rantai revisi dibawa ke partial
list($event) = pengajuan_run('view', array('item_ref' => 'PI-2026-00000'), array(), array('1', ''));
check(isset($GLOBALS['rutinView'][1]['chain']['PI-2026-00001']), 'view: revision chain computed for revised item');

// F. Detail query error
list($event) = pengajuan_run('view', array('query_error_detail' => true), array(), array('1', ''));
check($event['status'] === 0, 'view (query error): status 0');
check($event['context']['data']['outcome'] === 'query_error', 'view (query error): outcome query_error');

// G. Writer resilience
foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($event) = pengajuan_run('view', array($writer => true), array(), array('1', ''));
    check($event['status'] === 1, 'view: writer resilience');
}

// ==========================================
// 6. save_data
// ==========================================
function item_writes($ctrl, $op, $table) {
    return array_values(array_filter($ctrl->db->writes, function ($w) use ($op, $table) { return $w[0] === $op && ($w[1][0] ?? '') === $table; }));
}

// A. Departemen lain ditolak untuk non-admin
list($event, $output) = pengajuan_run('save_data', array('is_admin' => false), array('departement' => 'OTHER-DEP'));
check($event['status'] === 0 && $event['context']['data']['outcome'] === 'unauthorized', 'save_data (unauthorized): outcome unauthorized');
check(json_decode($output, true)['save'] === 0, 'save_data (unauthorized): save 0');

// B. Periode tidak valid
list($event) = pengajuan_run('save_data', array('is_admin' => true), array('periode' => '2026-13'));
check($event['status'] === 0 && $event['context']['data']['outcome'] === 'invalid_period', 'save_data: invalid period rejected');

// C. Batch tidak ditemukan / sudah diputuskan
list($event) = pengajuan_run('save_data', array('is_admin' => true, 'missing_record' => true), array('id' => '999'));
check($event['status'] === 0 && $event['context']['data']['outcome'] === 'not_found', 'save_data: unknown batch');
list($event, $output) = pengajuan_run('save_data', array('is_admin' => true, 'item_letter' => 'A'), array('id' => '1'));
check($event['status'] === 0 && $event['context']['data']['outcome'] === 'already_decided', 'save_data: decided batch cannot be edited');
check(strpos(json_decode($output, true)['msg'], 'sudah diputuskan') !== false, 'save_data: decided message');

// D. Tidak ada biaya yang diisi
list($event, $output) = pengajuan_run('save_data', array('is_admin' => true), array('biaya' => array('10' => '0')));
check($event['status'] === 0 && $event['context']['data']['outcome'] === 'empty', 'save_data (new): empty rejected');
check(json_decode($output, true)['msg'] === 'Isi perkiraan biaya minimal satu item sebelum menyimpan.', 'save_data (new): empty message');
list($event, $output) = pengajuan_run('save_data', array('is_admin' => true), array('id' => '1', 'biaya' => array('10' => '')));
check($event['status'] === 0 && $event['context']['data']['outcome'] === 'empty', 'save_data (edit): empty rejected');
check(strpos(json_decode($output, true)['msg'], 'pakai tombol Hapus') !== false, 'save_data (edit): empty message points to Hapus');

// E. Item terkunci tidak bisa diajukan (validasi di server, bukan hanya di form)
list($event, $output, $ctrl) = pengajuan_run('save_data', array('is_admin' => true, 'locked_master' => true));
check($event['status'] === 0 && $event['context']['data']['outcome'] === 'not_editable', 'save_data: locked item rejected on server');
check(!item_writes($ctrl, 'insert', 'tr_pengajuan_rutin'), 'save_data: nothing written for locked item');

// F. Item ditutup juga tidak bisa diajukan
list($event, , $ctrl) = pengajuan_run('save_data', array('is_admin' => true, 'closed_master' => true));
check($event['status'] === 0 && $event['context']['data']['outcome'] === 'empty' && !item_writes($ctrl, 'insert', 'tr_pengajuan_rutin_detail'), 'save_data: closed item is not part of the form so posting it writes nothing');

// G. Gagal membaca budget
list($event) = pengajuan_run('save_data', array('is_admin' => true, 'query_error_budget' => true));
check($event['status'] === 0 && $event['context']['data']['outcome'] === 'query_error', 'save_data: budget read error');

// H. Gagal memulai transaksi, commit, atau menulis
list($event) = pengajuan_run('save_data', array('is_admin' => true, 'trans_begin_failure' => true));
check($event['status'] === 0 && $event['context']['data']['outcome'] === 'trans_begin_failure', 'save_data: trans_begin failure');
list($event, , $ctrl) = pengajuan_run('save_data', array('is_admin' => true, 'trans_commit_failure' => true));
check($event['status'] === 0 && $event['context']['data']['outcome'] === 'commit_failed' && !$ctrl->db->committed, 'save_data: commit failure');
list($event, , $ctrl) = pengajuan_run('save_data', array('is_admin' => true, 'save_exception' => true));
check($event['status'] === 0 && $event['context']['data']['outcome'] === 'exception' && !$ctrl->db->committed, 'save_data: exception rolls back');
list($event, , $ctrl) = pengajuan_run('save_data', array('is_admin' => true, 'transaction_failure' => true));
check($event['status'] === 0 && $event['context']['data']['outcome'] === 'write_failed' && !$ctrl->db->committed, 'save_data: transaction_status failure rolls back');
list($event) = pengajuan_run('save_data', array('is_admin' => true, 'update_false' => true), array('id' => '1'));
check($event['status'] === 0 && $event['context']['data']['outcome'] === 'write_failed', 'save_data (edit): update failure');

// I. Berhasil: batch baru mendapat BP + PI
list($event, $output, $ctrl) = pengajuan_run('save_data', array('is_admin' => true));
$decoded = json_decode($output, true);
check($event['status'] === 1 && $event['context']['data']['outcome'] === 'saved', 'save_data (new): saved');
check($decoded['save'] === 1 && $decoded['edit'] === 0 && $decoded['count'] === 1, 'save_data (new): response');
check(preg_match('/^BP-\d{4}-001$/', $decoded['no_doc']) === 1, 'save_data (new): batch number BP-YYMM-001');
check(preg_match('/^PI-\d{4}-00001$/', $decoded['first']) === 1 && $decoded['first'] === $decoded['last'], 'save_data (new): item number PI-YYYY-00001');
check($event['context']['data']['amount'] == 100 && $event['context']['data']['item_count'] == 1, 'save_data (new): amount and count in audit');
check($ctrl->db->committed === true, 'save_data (new): committed');
$hdr = item_writes($ctrl, 'insert', 'tr_pengajuan_rutin')[0][1][1];
check($hdr['status'] === 0 && $hdr['periode'] === '2026-11' && $hdr['departement'] === 'DEP-1', 'save_data (new): header waiting with period');
$det = item_writes($ctrl, 'insert', 'tr_pengajuan_rutin_detail')[0][1][1];
check($det['no_item'] === $decoded['first'] && $det['item_status'] === 0 && $det['no_doc'] === $decoded['no_doc'] && $det['id_budget'] === 10, 'save_data (new): detail waiting with item number');
check($det['keterangan'] === 'catatan' && $det['accnumber'] === '123', 'save_data (new): note and last known bank copied');

// J. Dua item mendapat nomor berurutan
list(, $output) = pengajuan_run('save_data', array('is_admin' => true, 'two_items' => true), array('biaya' => array('10' => '100', '11' => '50'), 'ket' => array('10' => '', '11' => '')));
$decoded = json_decode($output, true);
check($decoded['count'] === 2 && preg_match('/-00001$/', $decoded['first']) === 1 && preg_match('/-00002$/', $decoded['last']) === 1, 'save_data: two items numbered sequentially');

// K. Berhasil: edit mempertahankan nomor item, tidak membuat nomor baru
list($event, $output, $ctrl) = pengajuan_run('save_data', array('is_admin' => true), array('biaya' => array('10' => '100.01')));
check($event['status'] === 0 && $event['context']['data']['outcome'] === 'over_budget' && strpos(json_decode($output, true)['msg'], 'tidak boleh melebihi budget') !== false && !$ctrl->db->writes, 'save_data: biaya above budget rejected, nothing written');
list($event, $output, $ctrl) = pengajuan_run('save_data', array('is_admin' => true), array('id' => '1', 'biaya' => array('10' => '90')));
$decoded = json_decode($output, true);
check($event['status'] === 1 && $decoded['save'] === 1 && $decoded['edit'] === 1 && $decoded['no_doc'] === 'BP-2611-001' && $decoded['first'] === '', 'save_data (edit): saved keeps batch and creates no new item number');
check(!item_writes($ctrl, 'insert', 'tr_pengajuan_rutin_detail') && item_writes($ctrl, 'update', 'tr_pengajuan_rutin_detail')[0][1][1]['nilai'] === 90.0, 'save_data (edit): existing item updated');
check($ctrl->db->committed === true, 'save_data (edit): committed');

// L. Edit: item yang biayanya dikosongkan dikeluarkan dari batch
list($event, , $ctrl) = pengajuan_run('save_data', array('is_admin' => true, 'two_items' => true), array('id' => '1', 'biaya' => array('10' => '100', '11' => ''), 'ket' => array('10' => '', '11' => '')));
$deleted = item_writes($ctrl, 'delete', 'tr_pengajuan_rutin_detail');
check($event['status'] === 1 && count($deleted) === 1 && $deleted[0][1][1] === array('id' => 2), 'save_data (edit): item whose cost was cleared is removed from the batch');

// M. Mengajukan ulang item yang ditolak: revisi_dari + diajukan_ulang_sebagai
list($event, , $ctrl) = pengajuan_run('save_data', array('is_admin' => true, 'rejected_master' => true));
$det = item_writes($ctrl, 'insert', 'tr_pengajuan_rutin_detail')[0][1][1];
check($event['status'] === 1 && $det['revisi_dari'] === 'PI-2026-00005', 'save_data (resubmit): new item records revisi_dari');
$link = array_values(array_filter(item_writes($ctrl, 'update', 'tr_pengajuan_rutin_detail'), function ($w) { return isset($w[1][1]['diajukan_ulang_sebagai']); }));
check($link && $link[0][1][1]['diajukan_ulang_sebagai'] === $det['no_item'] && $link[0][1][2] === array('no_item' => 'PI-2026-00005'), 'save_data (resubmit): original item links to the new number');

// N. Writer failure immunity
foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($event, $output) = pengajuan_run('save_data', array('is_admin' => true, $writer => true));
    check(json_decode($output, true)['save'] === 1, 'save_data: business save unaffected by writer failure');
}

// ==========================================
// 7. hapus_data
// ==========================================
// A. Batch tidak ada / kosong
list($event, $output) = pengajuan_run('hapus_data', array('missing_record' => true), array(), array(''));
check($event['status'] === 0 && $event['context']['data']['outcome'] === 'not_found', 'hapus_data (empty): outcome not_found');
check(json_decode($output, true)['delete'] === 0, 'hapus_data (empty): delete 0');

// B. Non-admin unauthorized (department mismatch)
list($event, $output) = pengajuan_run('hapus_data', array('is_admin' => false, 'dept_mismatch' => true), array(), array('BP-2611-001'));
check($event['status'] === 0 && $event['context']['data']['outcome'] === 'unauthorized', 'hapus_data (unauthorized): outcome unauthorized');
$decoded = json_decode($output, true);
check($decoded['delete'] === 0 && $decoded['msg'] === 'Akses ditolak', 'hapus_data (unauthorized): response matched');

// C. Batch yang sudah diputuskan tidak bisa dihapus
list($event, $output, $ctrl) = pengajuan_run('hapus_data', array('is_admin' => true, 'item_letter' => 'R'), array(), array('BP-2611-001'));
check($event['status'] === 0 && $event['context']['data']['outcome'] === 'already_decided', 'hapus_data (decided): outcome already_decided');
check(!item_writes($ctrl, 'delete', 'tr_pengajuan_rutin'), 'hapus_data (decided): nothing deleted');

// D. Transaction failure
list($event, $output) = pengajuan_run('hapus_data', array('is_admin' => true, 'transaction_failure' => true), array(), array('BP-2611-001'));
check($event['status'] === 0 && $event['context']['data']['outcome'] === 'write_failed', 'hapus_data (trans fail): outcome write_failed');
check(json_decode($output, true)['delete'] === 0, 'hapus_data (trans fail): delete 0');
list($event) = pengajuan_run('hapus_data', array('is_admin' => true, 'trans_commit_failure' => true), array(), array('BP-2611-001'));
check($event['status'] === 0 && $event['context']['data']['outcome'] === 'commit_failed', 'hapus_data: commit failure');

// E. Success delete + tautan revisi dilepas
list($event, $output, $ctrl) = pengajuan_run('hapus_data', array('is_admin' => true, 'item_ref' => 'PI-2026-00000'), array(), array('BP-2611-001'));
$decoded = json_decode($output, true);
check($event['status'] === 1 && $event['context']['data']['outcome'] === 'deleted' && $event['context']['data']['no_doc'] === 'BP-2611-001', 'hapus_data (success): audited');
check($decoded['delete'] === 1 && $decoded['idx'] === 'BP-2611-001', 'hapus_data (success): delete 1');
check(count(item_writes($ctrl, 'delete', 'tr_pengajuan_rutin_detail')) === 1 && count(item_writes($ctrl, 'delete', 'tr_pengajuan_rutin')) === 1, 'hapus_data (success): detail and header removed');
$unlink = item_writes($ctrl, 'update', 'tr_pengajuan_rutin_detail');
check($unlink && $unlink[0][1][1] === array('diajukan_ulang_sebagai' => null) && $unlink[0][1][2] === array('no_item' => 'PI-2026-00000'), 'hapus_data (success): original item freed for resubmission');

// F. Writer failure immunity
foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($event, $output) = pengajuan_run('hapus_data', array('is_admin' => true, $writer => true), array(), array('BP-2611-001'));
    check(json_decode($output, true)['delete'] === 1, 'hapus_data: delete 1 unaffected by writer failure');
}

// ==========================================
// 8. Subprocess Shutdown Test
// ==========================================
$lines = array();
$exitCode = 0;
exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__FILE__) . ' --pengajuan1-abort', $lines, $exitCode);
$auditLines = array_values(array_filter($lines, function ($line) { return strpos($line, 'AUDIT:') === 0; }));
check($exitCode === 0, 'Subprocess exit code 0');
check(count($auditLines) === 1, 'Subprocess exactly 1 audit event on query abort');
$shutdownEvent = json_decode(substr($auditLines[0], 6), true);
check($shutdownEvent['context']['methode'] === 'close_item', 'Shutdown event method attribution');
check($shutdownEvent['status'] === 0, 'Shutdown event status 0');
check($shutdownEvent['context']['data']['outcome'] === 'interrupted', 'Shutdown event outcome interrupted');

echo "PASS Pengajuan Rutin batch 1: {$scenarioCount} fixture scenarios + 1 shutdown subprocess\n";
