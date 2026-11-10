<?php
// Fase 4 / approval_invoice_so batch 1: controller asli + recorder asli; persistensi bisnis = fixture in-memory.
if (!defined('AUDIT_FIXTURE_ONLY')) { define('AUDIT_FIXTURE_ONLY', true); }
if (!defined('FCPATH')) { define('FCPATH', sys_get_temp_dir() . '/fcpath/'); }

require dirname(__DIR__, 2) . '/request_payment/tests/Audit_test.php';

class AiAuth { public function user_id() { return 10; } }
class AiTemplate {
    public function set($k, $v = null) {}
    public function title($t) {}
    public function render($v) { echo "<!-- rendered {$v} -->"; }
}
class AiSession { public function userdata($key = null) { return array('username' => 'tester', 'id_user' => 7); } }
class AiInput {
    public $values = array();
    public function post($key = null) { return $key === null ? $this->values : ($this->values[$key] ?? null); }
}
class AiLoad { public function library($l) {} public function model($m) {} }
class AiResult {
    private $rows;
    public function __construct($rows) { $this->rows = $rows; }
    public function result() { return $this->rows; }
}
class AiDb {
    public $calls = array(); public $writes = array();
    public function __call($n, $a) { $this->calls[] = $n; return $this; }
    public function get() { global $scenario; return new AiResult(!empty($scenario['empty']) ? array() : array((object) array('id_invoice' => 'INV/1'), (object) array('id_invoice' => 'INV/2'))); }
    public function update($t, $d, $w) { $this->calls[] = 'update'; $this->writes[] = array('update', $t, $d, $w); return empty($GLOBALS['scenario']['update_fail']); }
    public function affected_rows() { global $scenario; return isset($scenario['affected']) ? $scenario['affected'] : 1; }
    public function trans_begin() { $this->calls[] = 'trans_begin'; }
    public function trans_status() { $this->calls[] = 'trans_status'; return empty($GLOBALS['scenario']['status_fail']); }
    public function trans_commit() { $this->calls[] = 'trans_commit'; return empty($GLOBALS['scenario']['commit_fail']); }
    public function trans_rollback() { $this->calls[] = 'trans_rollback'; }
    public function error($r = null) { return array('code' => 0, 'message' => 'db error'); }
}

require dirname(__DIR__) . '/controllers/Approval_invoice_so.php';

function ai_make($options, $post) {
    global $scenario, $events, $ci;
    $scenario = $options; $events = array();
    $ci = (new ReflectionClass('Approval_invoice_so'))->newInstanceWithoutConstructor();
    $ci->module_audit_recorder = new Module_audit_recorder();
    $ci->db = new AiDb(); $ci->auth = new AiAuth(); $ci->template = new AiTemplate(); $ci->session = new AiSession();
    $ci->input = new AiInput(); $ci->load = new AiLoad();
    $ci->input->values = array_merge(array('id_invoice' => 'INV/1', 'password' => 'PRIVATE_PASSWORD'), $post);
    return $ci;
}

// Child process: jalur print_r + exit lama tidak bisa dijalankan in-process.
if (($argv[1] ?? '') === '--exit') {
    ai_make(json_decode(base64_decode($argv[3]), true), array());
    register_shutdown_function(function () { global $ci; echo 'CALLS:' . json_encode($ci->db->calls) . "\n"; });
    $ci->{$argv[2]}();
    exit;
}
if (($argv[1] ?? '') === '--subprocess-shutdown') {
    ai_make(array(), array());
    $ci->module_audit_recorder->begin('approval_invoice_so', 'index');
    exit;
}

function ai_run_exit($method, $options) {
    global $scenarioCount;
    $scenarioCount++;
    $raw = (string) shell_exec('"' . PHP_BINARY . '" "' . __FILE__ . '" --exit ' . $method . ' ' . base64_encode(json_encode($options)) . ' 2>&1');
    check(substr_count($raw, 'AUDIT:') === 1, $method . ' (exit): tepat satu event, output: ' . $raw);
    $event = json_decode(trim(strtok(substr($raw, strpos($raw, 'AUDIT:') + 6), "\n")), true);
    $calls = json_decode(trim(substr($raw, strpos($raw, 'CALLS:') + 6)), true) ?: array();
    return array($event, $raw, false, (object) array('db' => (object) array('calls' => $calls)));
}

function ai_run($method, $options = array(), $post = array()) {
    global $events, $ci, $scenarioCount;
    $scenarioCount++;
    ai_make($options, $post);
    ob_start();
    try { $ci->$method(); } catch (Throwable $e) { ob_end_clean(); throw $e; }
    $output = ob_get_clean();
    $ci->module_audit_recorder->finish_interrupted();
    check(count($events) === 1, $method . ': expected exactly one audit event, got ' . count($events));
    check($events[0]['context']['module'] === 'approval_invoice_so' && $events[0]['context']['methode'] === $method, $method . ': wrong identity');
    check(strpos(json_encode($events[0]['context']), 'PRIVATE') === false, $method . ': PII leaked');
    return array($events[0], $output, $ci);
}
function ai_data($e) { return $e['context']['data']; }

// ---- index
list($e, $out) = ai_run('index');
check($e['status'] === 1 && ai_data($e)['outcome'] === 'rendered' && ai_data($e)['row_count'] === 2 && strpos($out, 'rendered index') !== false, 'index: rendered 2 baris');
list($e) = ai_run('index', array('empty' => true));
check($e['status'] === 1 && ai_data($e)['row_count'] === 0, 'index: kosong tetap tercatat');
foreach (array('writer_failure', 'writer_exception') as $w) {
    list($e, $out) = ai_run('index', array($w => true));
    check($e['status'] === 1 && strpos($out, 'rendered index') !== false, "index: halaman utuh saat $w");
}

// ---- approve / reject
foreach (array('approve_invoice' => array('approved', 'sts', 1), 'reject_invoice' => array('rejected', 'sts', 0)) as $m => $cfg) {
    list($outcome, $col, $val) = $cfg;
    list($e, $out, $c) = ai_run($m);
    check($e['status'] === 1 && ai_data($e)['outcome'] === $outcome && ai_data($e)['id_invoice'] === 'INV/1' && ai_data($e)['affected_rows'] === 1 && json_decode($out, true)['status'] === 1, "$m: $outcome");
    check(in_array('trans_commit', $e['calls_at_write'], true), "$m: audit sukses sesudah commit");
    check($c->db->writes[0][2][$col] === $val && $c->db->writes[0][3] === array('id_invoice' => 'INV/1'), "$m: payload update tidak berubah");
    list($e, $out) = ai_run($m, array('affected' => 0));
    check($e['status'] === 1 && ai_data($e)['affected_rows'] === 0, "$m: ID tidak ada -> affected_rows 0 terlihat di log (perilaku bisnis lama tetap)");
    list($e, $out, $c) = ai_run($m, array('status_fail' => true));
    check($e['status'] === 0 && ai_data($e)['reason'] === 'transaction_failed' && json_decode($out, true)['status'] === 0 && !in_array('trans_commit', $c->db->calls, true) && in_array('trans_rollback', $c->db->calls, true), "$m: trans_status false -> rollback");
    list($e, $out, $c) = ai_run($m, array('commit_fail' => true));
    check($e['status'] === 0 && ai_data($e)['reason'] === 'commit_failed' && json_decode($out, true)['status'] === 0, "$m: commit gagal TIDAK boleh sukses");
    list($e, $raw, , $c) = ai_run_exit($m, array('update_fail' => true));
    check($e['status'] === 0 && ai_data($e)['reason'] === 'update_failed' && ai_data($e)['outcome'] === 'failed' && in_array('trans_rollback', $c->db->calls, true) && strpos($raw, 'db error') !== false, "$m: update gagal tercatat gagal, rollback, output lama utuh");
    list($e) = ai_run($m, array(), array('id_invoice' => str_repeat('X', 300)));
    check(!isset(ai_data($e)['id_invoice']), "$m: id tidak wajar tidak masuk metadata");
    foreach (array('writer_failure', 'writer_exception') as $w) {
        list($e, $out, $c) = ai_run($m, array($w => true));
        check($e['status'] === 1 && json_decode($out, true)['status'] === 1 && in_array('trans_commit', $c->db->calls, true), "$m: bisnis utuh saat $w");
    }
}

// ---- subprocess: exit nyata -> fallback interrupted
$GLOBALS['scenarioCount']++;
$sub = shell_exec('"' . PHP_BINARY . '" "' . __FILE__ . '" --subprocess-shutdown 2>&1');
check(substr_count((string) $sub, 'AUDIT:') === 1, 'subprocess: satu event interrupted');
$ev = json_decode(trim(substr($sub, strpos($sub, 'AUDIT:') + 6)), true);
check($ev['status'] === 0 && $ev['context']['data']['outcome'] === 'interrupted', 'subprocess: interrupted');

echo 'PASS Approval Invoice SO batch 1: ' . $scenarioCount . " skenario fixture/subprocess, controller+recorder asli\n";
