<?php
if (!defined('AUDIT_FIXTURE_ONLY')) { define('AUDIT_FIXTURE_ONLY', true); }
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
        global $scenario;
        return empty($scenario['deny_perm']);
    }
}

if (!defined('DBACC')) { define('DBACC', 'fixture_acc'); }
require dirname(__DIR__) . '/controllers/Expense_petty_cash.php';

if (!function_exists('site_url')) { function site_url($u = '') { return 'http://localhost/' . $u; } }
if (!class_exists('Template', false)) {
    class Template { public static function set_message($m, $t = '') { $GLOBALS['epc_messages'][] = $m; } }
}

class EpcStop extends Exception {}

class EpcRow extends ArrayObject {
    public function offsetExists($k) { return true; }
    public function offsetGet($k) { return parent::offsetExists($k) ? parent::offsetGet($k) : 0; }
    public function __get($k) { return 0; }
}

// Query builder: skenario mengatur isi get() untuk validasi create_pelaporan.
class EpcResult {
    private $rows;
    public function __construct($rows) { $this->rows = $rows; }
    public function result() { return $this->rows; }
    public function num_rows() { return count($this->rows); }
    public function row() { return $this->rows ? $this->rows[0] : null; }
}
class EpcDb {
    private $calls = 0;
    public function __call($n, $a) { return $this; }
    public function get($t = '') {
        global $scenario;
        $this->calls++;
        // panggilan 1 = pencatatan_records, 2 = pelaporan aktif, selebihnya (coa) kosong
        if ($this->calls === 1 && isset($scenario['records'])) { return new EpcResult($scenario['records']); }
        if ($this->calls === 2 && isset($scenario['existing'])) { return new EpcResult($scenario['existing']); }
        return new EpcResult(array());
    }
}

class EpcAuth {
    public function user_id() { return 10; }
    public function restrict($perm) { global $scenario; if (!empty($scenario['deny_perm'])) { throw new EpcStop('restricted'); } return true; }
}
class EpcTemplate {
    public function set($k, $v = null) {}
    public function title($t) {}
    public function page_icon($i) {}
    public function render($v) { echo "<!-- rendered {$v} -->"; }
}
class EpcInput {
    public $values = array();
    public function post($key = null) { return $key === null ? $this->values : (isset($this->values[$key]) ? $this->values[$key] : null); }
}
class EpcLoad { public function library($l) {} }

class EpcModel {
    public function get_server_side_data($p, $f = null) {
        global $scenario;
        $rows = !empty($scenario['empty_result']) ? array() : array(array('id' => 1), array('id' => 2), array('id' => 3));
        return array('draw' => 1, 'recordsTotal' => count($rows), 'recordsFiltered' => count($rows), 'data' => $rows);
    }
    public function get_budget_info($id) { global $scenario; return !empty($scenario['no_budget']) ? false : (object) array('budget' => 100); }
    public function get_pencatatan($id) {
        global $scenario;
        if (!empty($scenario['missing_record'])) { return false; }
        return (object) array('header' => (object) array('status' => isset($scenario['pc_status']) ? $scenario['pc_status'] : 'draft', 'petty_cash_id' => 4));
    }
    public function get_pelaporan($id) {
        global $scenario;
        if (!empty($scenario['missing_record'])) { return false; }
        return (object) array('header' => (object) array('status' => isset($scenario['pl_status']) ? $scenario['pl_status'] : 'draft', 'petty_cash_id' => 4), 'pencatatan_list' => array((object) array('id' => 1)));
    }
    public function validate_same_week($ids) { global $scenario; return empty($scenario['week_mismatch']); }
    public function create_pelaporan($data, $ids) { global $scenario; return !empty($scenario['create_fail']) ? false : 55; }
}

function epc_run($method, $options = array(), $post = array(), $args = array()) {
    global $scenario, $events, $ci;
    $scenario = $options;
    $events = array();
    $GLOBALS['redirectTarget'] = null;
    $GLOBALS['scenarioCount']++;
    $ci = (new ReflectionClass('Expense_petty_cash'))->newInstanceWithoutConstructor();
    $ci->module_audit_recorder = new Module_audit_recorder();
    $ci->auth = new EpcAuth();
    $ci->db = new EpcDb();
    $ci->template = new EpcTemplate();
    $ci->input = new EpcInput();
    $ci->load = new EpcLoad();
    $ci->Expense_petty_cash_model = new EpcModel();
    $ci->Expense_petty_cash_pelaporan_model = new EpcModel();
    $ci->input->values = array_merge(array('draw' => 1, 'password' => 'PRIVATE_PASSWORD'), $post);

    ob_start();
    $stopped = false;
    try {
        call_user_func_array(array($ci, $method), $args);
    } catch (EpcStop $s) {
        $stopped = true;
    } catch (Throwable $e) {
        ob_end_clean();
        throw $e;
    }
    $output = ob_get_clean();
    $ci->module_audit_recorder->finish_interrupted();

    check(count($events) === 1, $method . ': expected exactly one audit event, got ' . count($events));
    check($events[0]['context']['module'] === 'expense_petty_cash', $method . ': wrong module');
    check($events[0]['context']['methode'] === $method, $method . ': wrong method');
    check(strpos(json_encode($events[0]['context']), 'PRIVATE_PASSWORD') === false, $method . ': password leaked');
    return array($events[0], $output, $stopped);
}

if (in_array('--subprocess-shutdown', $argv ?? array(), true)) {
    list($e) = epc_run('get_data_pencatatan', array('writer_exception' => true));
    exit($e['status'] === 1 ? 0 : 1);
}

function epc_writers($method, $post, $args, $assert) {
    foreach (array('writer_failure', 'writer_exception') as $writer) {
        list($e, $out) = epc_run($method, array($writer => true), $post, $args);
        $assert($e, $out, $writer);
    }
}
function epc_data($e) { return $e['context']['data']; }

// Halaman daftar: rendered + denied
foreach (array('index' => 'pencatatan/index', 'pelaporan' => 'pelaporan/index') as $method => $view) {
    list($e, $out) = epc_run($method);
    check($e['status'] === 1 && epc_data($e)['outcome'] === 'rendered' && strpos($out, $view) !== false, "$method: rendered");
    list($e, $out, $stopped) = epc_run($method, array('deny_perm' => true));
    check($stopped && $e['status'] === 0 && epc_data($e)['outcome'] === 'denied' && epc_data($e)['reason'] === 'permission_denied' && $out === '', "$method: denied");
    epc_writers($method, array(), array(), function ($e, $out, $w) use ($method, $view) { check(strpos($out, $view) !== false, "$method: intact on $w"); });
}

// DataTables: row_count kosong/berisi + denied
foreach (array('get_data_pencatatan', 'get_data_pelaporan') as $method) {
    foreach (array(array('empty_result' => true), array()) as $i => $opt) {
        list($e, $out) = epc_run($method, $opt);
        check($e['status'] === 1 && epc_data($e)['row_count'] === $i * 3 && epc_data($e)['outcome'] === 'rendered', "$method: row_count " . $i * 3);
        check(json_decode($out, true)['draw'] === 1, "$method: response intact");
    }
    list($e, , $stopped) = epc_run($method, array('deny_perm' => true));
    check($stopped && epc_data($e)['outcome'] === 'denied', "$method: denied");
    epc_writers($method, array(), array(), function ($e, $out, $w) use ($method) { check(json_decode($out, true)['draw'] === 1, "$method: intact on $w"); });
}

// create: rendered, tanpa master, tanpa budget
list($e, $out) = epc_run('create', array(), array(), array(4));
check($e['status'] === 1 && epc_data($e)['outcome'] === 'rendered' && epc_data($e)['petty_cash_id'] == 4 && strpos($out, 'pencatatan/form') !== false, 'create: rendered');
list($e, $out) = epc_run('create');
check($e['status'] === 0 && epc_data($e)['reason'] === 'petty_cash_required' && $GLOBALS['redirectTarget'] === 'expense_petty_cash', 'create: tanpa master -> redirect dicatat');
list($e, $out) = epc_run('create', array('no_budget' => true), array(), array(4));
check($e['status'] === 0 && epc_data($e)['reason'] === 'budget_not_found' && $GLOBALS['redirectTarget'] === 'expense_petty_cash', 'create: budget tidak ada');
list($e, , $stopped) = epc_run('create', array('deny_perm' => true), array(), array(4));
check($stopped && epc_data($e)['outcome'] === 'denied', 'create: denied');
epc_writers('create', array(), array(4), function ($e, $out, $w) { check(strpos($out, 'pencatatan/form') !== false, "create: intact on $w"); });

// edit: rendered, not_found, status tidak boleh diedit
list($e, $out) = epc_run('edit', array(), array(), array(7));
check($e['status'] === 1 && epc_data($e)['outcome'] === 'rendered' && epc_data($e)['id'] == 7, 'edit: rendered');
list($e) = epc_run('edit', array('missing_record' => true), array(), array(99));
check($e['status'] === 0 && epc_data($e)['outcome'] === 'not_found' && $GLOBALS['redirectTarget'] === 'expense_petty_cash', 'edit: not_found');
list($e) = epc_run('edit', array('pc_status' => 'approved'), array(), array(7));
check($e['status'] === 0 && epc_data($e)['reason'] === 'status_not_editable' && epc_data($e)['current_status'] === 'approved', 'edit: status approved ditolak');
list($e) = epc_run('edit', array('pc_status' => 'reject'), array(), array(7));
check($e['status'] === 1, 'edit: status reject boleh diedit');
list($e, , $stopped) = epc_run('edit', array('deny_perm' => true), array(), array(7));
check($stopped && epc_data($e)['outcome'] === 'denied', 'edit: denied');
epc_writers('edit', array(), array(7), function ($e, $out, $w) { check(strpos($out, 'pencatatan/form') !== false, "edit: intact on $w"); });

// view, view_pelaporan: rendered, not_found
foreach (array('view' => 'pencatatan/view', 'view_pelaporan' => 'pelaporan/view') as $method => $view) {
    list($e, $out) = epc_run($method, array(), array(), array(7));
    check($e['status'] === 1 && epc_data($e)['outcome'] === 'rendered' && epc_data($e)['id'] == 7 && strpos($out, $view) !== false, "$method: rendered");
    list($e) = epc_run($method, array('missing_record' => true), array(), array(99));
    check($e['status'] === 0 && epc_data($e)['outcome'] === 'not_found' && $GLOBALS['redirectTarget'] !== null, "$method: not_found");
    list($e, , $stopped) = epc_run($method, array('deny_perm' => true), array(), array(7));
    check($stopped && epc_data($e)['outcome'] === 'denied', "$method: denied");
    epc_writers($method, array(), array(7), function ($e, $out, $w) use ($method, $view) { check(strpos($out, $view) !== false, "$method: intact on $w"); });
}

// confirm_pelaporan: rendered, not_found, bukan draft
list($e, $out) = epc_run('confirm_pelaporan', array(), array(), array(7));
check($e['status'] === 1 && epc_data($e)['outcome'] === 'rendered' && strpos($out, 'pelaporan/confirm') !== false, 'confirm_pelaporan: rendered');
list($e) = epc_run('confirm_pelaporan', array('missing_record' => true), array(), array(99));
check($e['status'] === 0 && epc_data($e)['outcome'] === 'not_found', 'confirm_pelaporan: not_found');
list($e) = epc_run('confirm_pelaporan', array('pl_status' => 'waiting'), array(), array(7));
check($e['status'] === 0 && epc_data($e)['reason'] === 'not_draft' && epc_data($e)['current_status'] === 'waiting' && $GLOBALS['redirectTarget'] === 'expense_petty_cash/pelaporan', 'confirm_pelaporan: bukan draft');
list($e, , $stopped) = epc_run('confirm_pelaporan', array('deny_perm' => true), array(), array(7));
check($stopped && epc_data($e)['outcome'] === 'denied', 'confirm_pelaporan: denied');
epc_writers('confirm_pelaporan', array(), array(7), function ($e, $out, $w) { check(strpos($out, 'pelaporan/confirm') !== false, "confirm_pelaporan: intact on $w"); });

// create_pelaporan: semua cabang validasi + sukses
$draft = function ($id, $company = 'C1', $status = 'draft') { return (object) array('id' => $id, 'company' => $company, 'status' => $status, 'petty_cash_id' => 4); };
$cases = array(
    'permission_denied' => array(array('deny_perm' => true), array('pencatatan_ids' => array(1)), 'denied'),
    'ids_required' => array(array(), array(), 'validation_failed'),
    'ids_invalid' => array(array(), array('pencatatan_ids' => array('x', 0)), 'validation_failed'),
    'records_not_found' => array(array('records' => array($draft(1))), array('pencatatan_ids' => array(1, 2)), 'validation_failed'),
    'not_draft' => array(array('records' => array($draft(1, 'C1', 'waiting'))), array('pencatatan_ids' => array(1)), 'validation_failed'),
    'already_reported' => array(array('records' => array($draft(1)), 'existing' => array((object) array('pencatatan_id' => 1))), array('pencatatan_ids' => array(1)), 'validation_failed'),
    'company_mismatch' => array(array('records' => array($draft(1, 'C1'), $draft(2, 'C2'))), array('pencatatan_ids' => array(1, 2)), 'validation_failed'),
    'week_mismatch' => array(array('records' => array($draft(1), $draft(2)), 'week_mismatch' => true), array('pencatatan_ids' => array(1, 2)), 'validation_failed'),
);
foreach ($cases as $reason => $c) {
    list($opt, $post, $outcome) = $c;
    list($e, $out) = epc_run('create_pelaporan', $opt, $post);
    check($e['status'] === 0 && epc_data($e)['reason'] === $reason && epc_data($e)['outcome'] === $outcome, "create_pelaporan: $reason, got " . json_encode(epc_data($e)));
    check(json_decode($out, true)['status'] === false, "create_pelaporan: $reason response intact");
}
list($e, $out) = epc_run('create_pelaporan', array('records' => array($draft(1), $draft(2)), 'create_fail' => true), array('pencatatan_ids' => array(1, 2)));
check($e['status'] === 0 && epc_data($e)['outcome'] === 'create_failed' && epc_data($e)['item_count'] === 2, 'create_pelaporan: create_failed');
list($e, $out) = epc_run('create_pelaporan', array('records' => array($draft(1), $draft(2))), array('pencatatan_ids' => array(1, 2)));
check($e['status'] === 1 && epc_data($e)['outcome'] === 'created' && epc_data($e)['pelaporan_id'] == 55 && epc_data($e)['item_count'] === 2, 'create_pelaporan: created');
check(json_decode($out, true)['status'] === true, 'create_pelaporan: created response intact');
epc_writers('create_pelaporan', array('pencatatan_ids' => array(1, 2)), array(), function ($e, $out, $w) { check(json_decode($out, true)['status'] === false || $e['status'] === 1, "create_pelaporan: intact on $w"); });
foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e, $out) = epc_run('create_pelaporan', array($writer => true, 'records' => array($draft(1), $draft(2))), array('pencatatan_ids' => array(1, 2)));
    check($e['status'] === 1 && json_decode($out, true)['status'] === true, "create_pelaporan: sukses tetap utuh pada $writer");
}

// Model: sukses create_pelaporan mengikuti hasil trans_complete (commit gagal -> false)
require dirname(__DIR__) . '/models/Expense_petty_cash_pelaporan_model.php';
class EpcFakeModelDb {
    public $complete = true; public $status = true; public $rollbacks = 0;
    public function trans_start() {}
    public function trans_complete() { return $this->complete; }
    public function trans_status() { return $this->status; }
    public function trans_rollback() { $this->rollbacks++; }
    public function insert_id() { return 9; }
    public function query($sql, $b = array()) { return new EpcResult(array()); }
    public function __call($n, $a) { return $this; }
    public function get() { $r = (object) array('grand_total' => 10, 'approver' => 0, 'periode_start' => '2026-11-02', 'periode_end' => '2026-11-08', 'tanggal' => '2026-11-03', 'no_pelaporan' => 'PL-1', 'max_no' => 0); return new EpcResult(array($r, $r)); }
}
foreach (array(array(true, true, true), array(false, true, false), array(true, false, false)) as $c) {
    list($complete, $status, $expectId) = $c;
    $m = (new ReflectionClass('Expense_petty_cash_pelaporan_model'))->newInstanceWithoutConstructor();
    $m->db = new EpcFakeModelDb(); $m->db->complete = $complete; $m->db->status = $status;
    $GLOBALS['scenarioCount']++;
    $r = $m->create_pelaporan(array('company' => 'C1', 'petty_cash_id' => 4, 'created_by' => 1), array(1, 2));
    check(($r !== false) === $expectId, 'model create_pelaporan: hasil mengikuti commit/status ' . json_encode($c));
}

$cmd = escapeshellcmd(PHP_BINARY) . ' ' . escapeshellarg(__FILE__) . ' --subprocess-shutdown 2>&1';
exec($cmd, $subOutput, $subExitCode);
check($subExitCode === 0, 'Subprocess shutdown exit code 0');

echo "PASS Expense petty cash batch 1: {$scenarioCount} fixture scenarios + 1 shutdown subprocess" . PHP_EOL;
