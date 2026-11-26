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

require dirname(__DIR__) . '/controllers/Petty_cash_vuca_sustain.php';

if (!class_exists('Template', false)) {
    class Template { public static function set_message($m, $t = '') {} }
}

class PvStop extends Exception {}

class PvAuth {
    public function user_id() { return 10; }
    public function restrict($perm) { global $scenario; if (!empty($scenario['deny_perm'])) { throw new PvStop('restricted'); } return true; }
}
class PvTemplate {
    public function set($k, $v = null) {}
    public function title($t) {}
    public function page_icon($i) {}
    public function render($v) { echo "<!-- rendered {$v} -->"; }
}
class PvSession {
    public function set_flashdata($k, $v) { $GLOBALS['pv_flash'] = $v; }
    public function userdata($key = null) { return array('username' => 'tester', 'id_user' => 7); }
}
class PvInput {
    public $values = array();
    public function post($key = null) { return $key === null ? $this->values : (isset($this->values[$key]) ? $this->values[$key] : null); }
    public function is_ajax_request() { global $scenario; return empty($scenario['not_ajax']); }
}
class PvLoad {
    public function library($l) {}
    public function view($v, $d = array()) { echo "<!-- view {$v} -->"; }
}
class PvModel {
    public function get_server_side_data($p, $f = null) {
        global $scenario;
        $rows = !empty($scenario['empty_result']) ? array() : array(array('id' => 1), array('id' => 2));
        return array('draw' => 1, 'recordsTotal' => count($rows), 'recordsFiltered' => count($rows), 'data' => $rows);
    }
    public function get_payment_hutang($id) {
        global $scenario;
        if (!empty($scenario['missing_record'])) { return false; }
        return (object) array('header' => (object) array('status' => isset($scenario['rec_status']) ? $scenario['rec_status'] : 'draft'));
    }
    public function process_payment_hutang($id, $uid) { global $scenario; return empty($scenario['process_fail']); }
}

function pv_run($method, $options = array(), $post = array(), $args = array()) {
    global $scenario, $events, $ci;
    $scenario = $options;
    $events = array();
    $GLOBALS['redirectTarget'] = null;
    $GLOBALS['scenarioCount']++;
    $ci = (new ReflectionClass('Petty_cash_vuca_sustain'))->newInstanceWithoutConstructor();
    $ci->module_audit_recorder = new Module_audit_recorder();
    $ci->auth = new PvAuth();
    $ci->template = new PvTemplate();
    $ci->session = new PvSession();
    $ci->input = new PvInput();
    $ci->load = new PvLoad();
    $ci->Petty_cash_vuca_sustain_model = new PvModel();
    $ci->input->values = array_merge(array('draw' => 1, 'password' => 'PRIVATE_PASSWORD'), $post);

    ob_start();
    $stopped = false;
    try {
        call_user_func_array(array($ci, $method), $args);
    } catch (PvStop $s) {
        $stopped = true;
    } catch (Throwable $e) {
        ob_end_clean();
        throw $e;
    }
    $output = ob_get_clean();
    $ci->module_audit_recorder->finish_interrupted();

    check(count($events) === 1, $method . ': expected exactly one audit event, got ' . count($events));
    check($events[0]['context']['module'] === 'petty_cash_vuca_sustain', $method . ': wrong module');
    check($events[0]['context']['methode'] === $method, $method . ': wrong method');
    check(strpos(json_encode($events[0]['context']), 'PRIVATE_PASSWORD') === false, $method . ': password leaked');
    return array($events[0], $output, $stopped);
}

if (in_array('--subprocess-shutdown', $argv ?? array(), true)) {
    list($e) = pv_run('get_data', array('writer_exception' => true));
    exit($e['status'] === 1 ? 0 : 1);
}

function pv_data($e) { return $e['context']['data']; }
function pv_writers($method, $post, $args, $assert) {
    foreach (array('writer_failure', 'writer_exception') as $writer) {
        list($e, $out) = pv_run($method, array($writer => true), $post, $args);
        $assert($e, $out, $writer);
    }
}

// index: rendered + denied
list($e, $out) = pv_run('index');
check($e['status'] === 1 && pv_data($e)['outcome'] === 'rendered' && strpos($out, 'rendered index') !== false, 'index: rendered');
list($e, $out, $stopped) = pv_run('index', array('deny_perm' => true));
check($stopped && $e['status'] === 0 && pv_data($e)['outcome'] === 'denied' && pv_data($e)['reason'] === 'permission_denied' && $out === '', 'index: denied');
pv_writers('index', array(), array(), function ($e, $out, $w) { check(strpos($out, 'rendered index') !== false, "index: intact on $w"); });

// get_data: row_count kosong/berisi + denied
foreach (array(array('empty_result' => true), array()) as $i => $opt) {
    list($e, $out) = pv_run('get_data', $opt);
    check($e['status'] === 1 && pv_data($e)['row_count'] === $i * 2 && pv_data($e)['outcome'] === 'rendered', 'get_data: row_count ' . $i * 2);
    check(json_decode($out, true)['draw'] === 1, 'get_data: response intact');
}
list($e, , $stopped) = pv_run('get_data', array('deny_perm' => true));
check($stopped && pv_data($e)['outcome'] === 'denied', 'get_data: denied');
pv_writers('get_data', array(), array(), function ($e, $out, $w) { check(json_decode($out, true)['draw'] === 1, "get_data: intact on $w"); });

// view, print_pdf: rendered, not_found, denied
foreach (array('view' => 'rendered view', 'print_pdf' => 'view print') as $method => $needle) {
    list($e, $out) = pv_run($method, array(), array(), array(7));
    check($e['status'] === 1 && pv_data($e)['outcome'] === 'rendered' && pv_data($e)['id'] == 7 && strpos($out, $needle) !== false, "$method: rendered");
    list($e) = pv_run($method, array('missing_record' => true), array(), array(99));
    check($e['status'] === 0 && pv_data($e)['outcome'] === 'not_found' && $GLOBALS['redirectTarget'] === 'petty_cash_vuca_sustain', "$method: not_found + redirect");
    list($e, , $stopped) = pv_run($method, array('deny_perm' => true), array(), array(7));
    check($stopped && pv_data($e)['outcome'] === 'denied', "$method: denied");
    pv_writers($method, array(), array(7), function ($e, $out, $w) use ($method, $needle) { check(strpos($out, $needle) !== false, "$method: intact on $w"); });
}

// confirm_payment: rendered, not_found, bukan draft, denied
list($e, $out) = pv_run('confirm_payment', array(), array(), array(7));
check($e['status'] === 1 && pv_data($e)['outcome'] === 'rendered' && strpos($out, 'confirm_payment') !== false, 'confirm_payment: rendered');
list($e) = pv_run('confirm_payment', array('missing_record' => true), array(), array(99));
check($e['status'] === 0 && pv_data($e)['outcome'] === 'not_found', 'confirm_payment: not_found');
list($e) = pv_run('confirm_payment', array('rec_status' => 'waiting payment'), array(), array(7));
check($e['status'] === 0 && pv_data($e)['reason'] === 'not_draft' && pv_data($e)['current_status'] === 'waiting payment' && pv_data($e)['outcome'] === 'redirected', 'confirm_payment: bukan draft');
list($e, , $stopped) = pv_run('confirm_payment', array('deny_perm' => true), array(), array(7));
check($stopped && pv_data($e)['outcome'] === 'denied', 'confirm_payment: denied');
pv_writers('confirm_payment', array(), array(7), function ($e, $out, $w) { check(strpos($out, 'confirm_payment') !== false, "confirm_payment: intact on $w"); });

// payment_hutang: processed, process_failed, bukan AJAX, denied
list($e, $out) = pv_run('payment_hutang', array(), array(), array(7));
check($e['status'] === 1 && pv_data($e)['outcome'] === 'processed' && pv_data($e)['id'] == 7 && json_decode($out, true)['status'] === true, 'payment_hutang: processed');
list($e, $out) = pv_run('payment_hutang', array('process_fail' => true), array(), array(7));
check($e['status'] === 0 && pv_data($e)['outcome'] === 'process_failed' && json_decode($out, true)['status'] === false, 'payment_hutang: process_failed');
list($e, $out) = pv_run('payment_hutang', array('not_ajax' => true), array(), array(7));
check($e['status'] === 0 && pv_data($e)['outcome'] === 'invalid_request' && pv_data($e)['reason'] === 'not_ajax' && json_decode($out, true)['status'] === false, 'payment_hutang: bukan AJAX');
list($e, $out, $stopped) = pv_run('payment_hutang', array('deny_perm' => true), array(), array(7));
check($stopped && pv_data($e)['outcome'] === 'denied' && $out === '', 'payment_hutang: denied');
foreach (array('writer_failure', 'writer_exception') as $w) {
    list($e, $out) = pv_run('payment_hutang', array($w => true), array(), array(7));
    check($e['status'] === 1 && json_decode($out, true)['status'] === true, "payment_hutang: intact on $w");
}

// Model: sukses process_payment_hutang mengikuti hasil commit
require dirname(__DIR__) . '/models/Petty_cash_vuca_sustain_model.php';
class PvFakeDb {
    public $commit = true; public $status = true; public $rollbacks = 0; public $found = true;
    public function trans_begin() {}
    public function trans_status() { return $this->status; }
    public function trans_commit() { return $this->commit; }
    public function trans_rollback() { $this->rollbacks++; }
    public function query($sql, $b = array()) {
        return new class($this->found) {
            private $f; function __construct($f) { $this->f = $f; }
            function num_rows() { return $this->f ? 1 : 0; }
            function row() { return (object) array('no_payment_hutang' => 'PH-1', 'nama_pembuat' => 'N', 'grand_total' => 100); }
        };
    }
    public function __call($n, $a) { return $this; }
}
foreach (array(array(true, true, true), array(false, true, false), array(true, false, false)) as $c) {
    $m = (new ReflectionClass('Petty_cash_vuca_sustain_model'))->newInstanceWithoutConstructor();
    $m->db = new PvFakeDb(); $m->db->commit = $c[0]; $m->db->status = $c[1];
    $GLOBALS['scenarioCount']++;
    check($m->process_payment_hutang(7, 10) === $c[2], 'model process_payment_hutang: hasil mengikuti commit/status ' . json_encode($c));
    check($m->db->rollbacks === ($c[2] ? 0 : 1), 'model process_payment_hutang: rollback hanya saat gagal ' . json_encode($c));
}
$m = (new ReflectionClass('Petty_cash_vuca_sustain_model'))->newInstanceWithoutConstructor();
$m->db = new PvFakeDb(); $m->db->found = false;
$GLOBALS['scenarioCount']++;
check($m->process_payment_hutang(7, 10) === false, 'model process_payment_hutang: record bukan draft/tidak ada -> false');

$cmd = escapeshellcmd(PHP_BINARY) . ' ' . escapeshellarg(__FILE__) . ' --subprocess-shutdown 2>&1';
exec($cmd, $subOutput, $subExitCode);
check($subExitCode === 0, 'Subprocess shutdown exit code 0');

echo "PASS Petty cash vuca sustain batch 1: {$scenarioCount} fixture scenarios + 1 shutdown subprocess" . PHP_EOL;
