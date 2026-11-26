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
    public function count_all_results($t = '', $reset = true) { return 0; }
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
    public function get_post($key) { return $this->post($key); }
}
class EpcLoad { public function library($l) {} public function view($v, $d = array()) { echo "<!-- view {$v} -->"; } }

class EpcModel {
    public function get_server_side_data($p, $f = null) {
        global $scenario;
        $rows = !empty($scenario['empty_result']) ? array() : array(array('id' => 1), array('id' => 2), array('id' => 3));
        return array('draw' => 1, 'recordsTotal' => count($rows), 'recordsFiltered' => count($rows), 'data' => $rows);
    }
    public function get_budget_info($id) { global $scenario; return !empty($scenario['no_budget']) ? false : (object) array('budget' => 100, 'budget_terpakai' => 10, 'sisa_budget' => 90); }
    public function get_pencatatan($id) {
        global $scenario;
        if (!empty($scenario['missing_record'])) { return false; }
        return (object) array('header' => (object) array('status' => isset($scenario['pc_status']) ? $scenario['pc_status'] : 'draft', 'petty_cash_id' => 4));
    }
    public function get_pelaporan($id) {
        global $scenario;
        if (!empty($scenario['missing_record'])) { return false; }
        return (object) array('header' => (object) array('status' => isset($scenario['pl_status']) ? $scenario['pl_status'] : 'draft', 'petty_cash_id' => 4, 'approver_id' => isset($scenario['approver_id']) ? $scenario['approver_id'] : null, 'created_by' => 0), 'pencatatan_list' => array((object) array('id' => 1)));
    }
    public $request_payment_ok = true;
    public function submit_pelaporan($id) { global $scenario; return empty($scenario['submit_fail']); }
    public function approve_pelaporan($id, $uid) { global $scenario; $this->request_payment_ok = empty($scenario['rp_failed']); return empty($scenario['approve_fail']); }
    public function reject_pelaporan($id, $a, $uid) { global $scenario; return empty($scenario['reject_fail']); }
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

$row = function ($id, $status = 'draft') { return (object) array('id' => $id, 'status' => $status, 'nama' => 'N', 'keterangan' => 'K', 'total_budget' => 1000, 'no_pelaporan' => 'RPC-1', 'periode_start' => 'a', 'periode_end' => 'b', 'company' => 'C1', 'grand_total' => 5000, 'jumlah_pencatatan' => 2); };

// ---------------- submit_pelaporan
list($e, $out) = epc_run('submit_pelaporan', array('records' => array($row(7))), array(), array(7));
check($e['status'] === 1 && epc_data($e)['outcome'] === 'submitted' && epc_data($e)['id'] == 7 && json_decode($out, true)['status'] === true, 'submit: submitted');
$cases = array(
    'permission_denied' => array(array('deny_perm' => true, 'records' => array($row(7))), array(7), 'denied'),
    'id_invalid' => array(array(), array(0), 'validation_failed'),
    'not_draft' => array(array('records' => array($row(7, 'waiting'))), array(7), 'validation_failed'),
);
foreach ($cases as $reason => $c) {
    list($e, $out) = epc_run('submit_pelaporan', $c[0], array(), $c[1]);
    check($e['status'] === 0 && epc_data($e)['reason'] === $reason && epc_data($e)['outcome'] === $c[2], "submit: $reason, got " . json_encode(epc_data($e)));
    check(json_decode($out, true)['status'] === false, "submit: $reason response intact");
}
list($e) = epc_run('submit_pelaporan', array('records' => array($row(7, 'waiting'))), array(), array(7));
check(epc_data($e)['current_status'] === 'waiting', 'submit: current_status dicatat');
list($e) = epc_run('submit_pelaporan', array(), array(), array(99));
check($e['status'] === 0 && epc_data($e)['outcome'] === 'not_found', 'submit: not_found');
list($e, $out) = epc_run('submit_pelaporan', array('records' => array($row(7)), 'submit_fail' => true), array(), array(7));
check($e['status'] === 0 && epc_data($e)['outcome'] === 'submit_failed' && json_decode($out, true)['status'] === false, 'submit: submit_failed');
foreach (array('writer_failure', 'writer_exception') as $w) {
    list($e, $out) = epc_run('submit_pelaporan', array($w => true, 'records' => array($row(7))), array(), array(7));
    check($e['status'] === 1 && json_decode($out, true)['status'] === true, "submit: intact on $w");
}

// ---------------- approval (halaman) + get_data_approval (DataTables)
list($e, $out) = epc_run('approval');
check($e['status'] === 1 && epc_data($e)['outcome'] === 'rendered' && strpos($out, 'approval/index') !== false, 'approval: rendered');
list($e, , $stopped) = epc_run('approval', array('deny_perm' => true));
check($stopped && epc_data($e)['outcome'] === 'denied', 'approval: denied');
epc_writers('approval', array(), array(), function ($e, $out, $w) { check(strpos($out, 'approval/index') !== false, "approval: intact on $w"); });

foreach (array(0, 2) as $n) {
    $recs = array();
    for ($i = 1; $i <= $n; $i++) { $recs[] = $row($i, 'waiting'); }
    list($e, $out) = epc_run('get_data_approval', array('records' => $recs));
    check($e['status'] === 1 && epc_data($e)['row_count'] === $n && epc_data($e)['outcome'] === 'rendered', "get_data_approval: row_count $n");
    check(json_decode($out, true)['draw'] === 1 && count(json_decode($out, true)['data']) === $n, "get_data_approval: response intact $n");
}
list($e, , $stopped) = epc_run('get_data_approval', array('deny_perm' => true));
check($stopped && epc_data($e)['outcome'] === 'denied', 'get_data_approval: denied');
epc_writers('get_data_approval', array(), array(), function ($e, $out, $w) { check(json_decode($out, true)['draw'] === 1, "get_data_approval: intact on $w"); });

// ---------------- view_approval
list($e, $out) = epc_run('view_approval', array(), array(), array(7));
check($e['status'] === 1 && epc_data($e)['outcome'] === 'rendered' && strpos($out, 'approval/view') !== false, 'view_approval: rendered');
list($e) = epc_run('view_approval', array('missing_record' => true), array(), array(99));
check($e['status'] === 0 && epc_data($e)['outcome'] === 'not_found' && $GLOBALS['redirectTarget'] === 'expense_petty_cash/approval', 'view_approval: not_found');
list($e) = epc_run('view_approval', array('approver_id' => 99), array(), array(7));
check($e['status'] === 0 && epc_data($e)['outcome'] === 'forbidden' && epc_data($e)['reason'] === 'not_assigned_approver' && $GLOBALS['redirectTarget'] === 'expense_petty_cash/approval', 'view_approval: bukan approver ditunjuk');
list($e) = epc_run('view_approval', array('approver_id' => 10), array(), array(7));
check($e['status'] === 1, 'view_approval: approver ditunjuk boleh');
list($e, , $stopped) = epc_run('view_approval', array('deny_perm' => true), array(), array(7));
check($stopped && epc_data($e)['outcome'] === 'denied', 'view_approval: denied');
epc_writers('view_approval', array(), array(7), function ($e, $out, $w) { check(strpos($out, 'approval/view') !== false, "view_approval: intact on $w"); });

// ---------------- approve
list($e, $out) = epc_run('approve', array(), array(), array(7));
check($e['status'] === 1 && epc_data($e)['outcome'] === 'approved' && epc_data($e)['integration'] === 'request_payment_ok' && json_decode($out, true)['status'] === true, 'approve: approved + integrasi ok');
list($e) = epc_run('approve', array('rp_failed' => true), array(), array(7));
check($e['status'] === 1 && epc_data($e)['outcome'] === 'approved' && epc_data($e)['integration'] === 'request_payment_failed', 'approve: approved tetapi integrasi request_payment gagal dicatat');
list($e, $out) = epc_run('approve', array('deny_perm' => true), array(), array(7));
check($e['status'] === 0 && epc_data($e)['outcome'] === 'denied' && json_decode($out, true)['status'] === false, 'approve: denied');
list($e) = epc_run('approve', array(), array(), array(0));
check($e['status'] === 0 && epc_data($e)['reason'] === 'id_invalid', 'approve: id invalid');
list($e, $out) = epc_run('approve', array('approve_fail' => true), array(), array(7));
check($e['status'] === 0 && epc_data($e)['outcome'] === 'approve_failed' && json_decode($out, true)['status'] === false, 'approve: approve_failed');
foreach (array('writer_failure', 'writer_exception') as $w) {
    list($e, $out) = epc_run('approve', array($w => true), array(), array(7));
    check($e['status'] === 1 && json_decode($out, true)['status'] === true, "approve: intact on $w");
}

// ---------------- reject
$alasan = 'Bukti tidak lengkap dan nominal tidak sesuai';
list($e, $out) = epc_run('reject', array(), array('alasan' => $alasan), array(7));
check($e['status'] === 1 && epc_data($e)['outcome'] === 'rejected' && json_decode($out, true)['status'] === true, 'reject: rejected');
check(strpos(json_encode($e['context']), 'Bukti tidak lengkap') === false, 'reject: alasan tidak masuk audit');
$rc = array(
    'permission_denied' => array(array('deny_perm' => true), array('alasan' => $alasan), array(7), 'denied'),
    'id_invalid' => array(array(), array('alasan' => $alasan), array(0), 'validation_failed'),
    'alasan_required' => array(array(), array(), array(7), 'validation_failed'),
    'alasan_too_short' => array(array(), array('alasan' => 'pendek'), array(7), 'validation_failed'),
    'alasan_too_long' => array(array(), array('alasan' => str_repeat('a', 501)), array(7), 'validation_failed'),
);
foreach ($rc as $reason => $c) {
    list($e, $out) = epc_run('reject', $c[0], $c[1], $c[2]);
    check($e['status'] === 0 && epc_data($e)['reason'] === $reason && epc_data($e)['outcome'] === $c[3], "reject: $reason, got " . json_encode(epc_data($e)));
    check(json_decode($out, true)['status'] === false, "reject: $reason response intact");
}
list($e, $out) = epc_run('reject', array('reject_fail' => true), array('alasan' => $alasan), array(7));
check($e['status'] === 0 && epc_data($e)['outcome'] === 'reject_failed', 'reject: reject_failed');
foreach (array('writer_failure', 'writer_exception') as $w) {
    list($e, $out) = epc_run('reject', array($w => true), array('alasan' => $alasan), array(7));
    check($e['status'] === 1 && json_decode($out, true)['status'] === true, "reject: intact on $w");
}

// ---------------- print_pelaporan
list($e, $out) = epc_run('print_pelaporan', array(), array(), array(7));
check($e['status'] === 1 && epc_data($e)['outcome'] === 'rendered' && strpos($out, 'pelaporan/print') !== false, 'print_pelaporan: rendered');
list($e) = epc_run('print_pelaporan', array('missing_record' => true), array(), array(99));
check($e['status'] === 0 && epc_data($e)['outcome'] === 'not_found' && $GLOBALS['redirectTarget'] === 'expense_petty_cash/pelaporan', 'print_pelaporan: not_found');
list($e, , $stopped) = epc_run('print_pelaporan', array('deny_perm' => true), array(), array(7));
check($stopped && epc_data($e)['outcome'] === 'denied', 'print_pelaporan: denied');
epc_writers('print_pelaporan', array(), array(7), function ($e, $out, $w) { check(strpos($out, 'pelaporan/print') !== false, "print_pelaporan: intact on $w"); });

// ---------------- get_budget_info
list($e, $out) = epc_run('get_budget_info', array(), array('petty_cash_id' => 4));
check($e['status'] === 1 && epc_data($e)['outcome'] === 'rendered' && epc_data($e)['petty_cash_id'] == 4 && json_decode($out, true)['status'] === 'success', 'get_budget_info: rendered');
list($e, $out) = epc_run('get_budget_info');
check($e['status'] === 0 && epc_data($e)['reason'] === 'petty_cash_required' && json_decode($out, true)['status'] === 'error', 'get_budget_info: param kosong');
list($e) = epc_run('get_budget_info', array('no_budget' => true), array('petty_cash_id' => 4));
check($e['status'] === 0 && epc_data($e)['outcome'] === 'not_found', 'get_budget_info: not_found');
list($e, , $stopped) = epc_run('get_budget_info', array('deny_perm' => true), array('petty_cash_id' => 4));
check($stopped && epc_data($e)['outcome'] === 'denied', 'get_budget_info: denied');
epc_writers('get_budget_info', array('petty_cash_id' => 4), array(), function ($e, $out, $w) { check(json_decode($out, true)['status'] === 'success', "get_budget_info: intact on $w"); });

// ---------------- get_coa_list
list($e, $out) = epc_run('get_coa_list', array('records' => array($row(1), $row(2))), array('petty_cash_id' => 4));
check($e['status'] === 1 && epc_data($e)['outcome'] === 'rendered' && epc_data($e)['row_count'] === 2 && count(json_decode($out, true)) === 2, 'get_coa_list: rendered row_count 2');
list($e, $out) = epc_run('get_coa_list');
check($e['status'] === 1 && epc_data($e)['outcome'] === 'empty' && epc_data($e)['row_count'] === 0 && json_decode($out, true) === array(), 'get_coa_list: tanpa master -> empty');
list($e, , $stopped) = epc_run('get_coa_list', array('deny_perm' => true), array('petty_cash_id' => 4));
check($stopped && epc_data($e)['outcome'] === 'denied', 'get_coa_list: denied');
epc_writers('get_coa_list', array('petty_cash_id' => 4), array(), function ($e, $out, $w) { check(json_decode($out, true) === array(), "get_coa_list: intact on $w"); });

// ---------------- get_petty_cash_list
foreach (array(0, 2) as $n) {
    $recs = array();
    for ($i = 1; $i <= $n; $i++) { $recs[] = $row($i); }
    list($e, $out) = epc_run('get_petty_cash_list', array('records' => $recs));
    check($e['status'] === 1 && epc_data($e)['row_count'] === $n && count(json_decode($out, true)['data']) === $n, "get_petty_cash_list: row_count $n");
}
list($e, , $stopped) = epc_run('get_petty_cash_list', array('deny_perm' => true));
check($stopped && epc_data($e)['outcome'] === 'denied', 'get_petty_cash_list: denied');
epc_writers('get_petty_cash_list', array(), array(), function ($e, $out, $w) { check(json_decode($out, true)['status'] === 1, "get_petty_cash_list: intact on $w"); });

// ---------------- Model: sukses tulis mengikuti hasil commit; integrasi request_payment dilaporkan
require dirname(__DIR__) . '/models/Expense_petty_cash_pelaporan_model.php';
class EpcWriteDb {
    public $complete = true; public $status = true;
    public function trans_start() {}
    public function trans_complete() { return $this->complete; }
    public function trans_status() { return $this->status; }
    public function __call($n, $a) { return $this; }
    public function get() { return new EpcResult(array((object) array('status' => 'waiting', 'pencatatan_id' => 1))); }
}
class EpcWriteModel extends Expense_petty_cash_pelaporan_model {
    public $rpThrow = false;
    public function create_request_payment($p) { if ($this->rpThrow) { throw new Exception('rp'); } return true; }
}
foreach (array(array(true, true, true), array(false, true, false), array(true, false, false)) as $c) {
    list($complete, $status, $expect) = $c;
    foreach (array('submit_pelaporan' => array(7), 'approve_pelaporan' => array(7, 10), 'reject_pelaporan' => array(7, 'Alasan reject yang cukup panjang', 10)) as $m => $args) {
        $model = (new ReflectionClass('EpcWriteModel'))->newInstanceWithoutConstructor();
        $model->db = new EpcWriteDb(); $model->db->complete = $complete; $model->db->status = $status;
        $GLOBALS['scenarioCount']++;
        $r = call_user_func_array(array($model, $m), $args);
        check((bool) $r === $expect, "model $m: hasil mengikuti commit/status " . json_encode($c));
    }
}
$model = (new ReflectionClass('EpcWriteModel'))->newInstanceWithoutConstructor();
$model->db = new EpcWriteDb(); $model->rpThrow = true;
$GLOBALS['scenarioCount']++;
check($model->approve_pelaporan(7, 10) === true && $model->request_payment_ok === false, 'model approve: integrasi request_payment gagal tidak membatalkan approve tetapi ditandai');
$model = (new ReflectionClass('EpcWriteModel'))->newInstanceWithoutConstructor();
$model->db = new EpcWriteDb();
$GLOBALS['scenarioCount']++;
check($model->approve_pelaporan(7, 10) === true && $model->request_payment_ok === true, 'model approve: integrasi ok');

$cmd = escapeshellcmd(PHP_BINARY) . ' ' . escapeshellarg(__FILE__) . ' --subprocess-shutdown 2>&1';
exec($cmd, $subOutput, $subExitCode);
check($subExitCode === 0, 'Subprocess shutdown exit code 0');

echo "PASS Expense petty cash batch 2: {$scenarioCount} fixture scenarios + 1 shutdown subprocess" . PHP_EOL;
