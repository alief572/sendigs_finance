<?php
// Fase 4 / plan_tagih batch 1: controller asli + recorder asli; persistensi bisnis = fixture in-memory.
if (!defined('AUDIT_FIXTURE_ONLY')) { define('AUDIT_FIXTURE_ONLY', true); }
if (!defined('FCPATH')) { define('FCPATH', sys_get_temp_dir() . '/fcpath/'); }

require dirname(__DIR__, 2) . '/request_payment/tests/Audit_test.php';

if (!function_exists('has_permission')) {
    function has_permission($perm = '') { global $scenario; return empty($scenario['deny_perm']); }
}

class PtStop extends Exception {}

class PtAuth {
    public function user_id() { return 10; }
    public function restrict($perm) { global $scenario; if (!empty($scenario['deny_perm'])) { throw new PtStop('restricted'); } return true; }
}
class PtTemplate {
    public function set($k, $v = null) {}
    public function title($t) {}
    public function page_icon($i) {}
    public function render($v) { echo "<!-- rendered {$v} -->"; }
}
class PtSession { public function userdata($key = null) { return array('username' => 'tester', 'id_user' => 7); } }
class PtInput {
    public $values = array();
    public function post($key = null) { return $key === null ? $this->values : ($this->values[$key] ?? null); }
}
class PtLoad { public function library($l) {} public function model($m) {} }

class PtResult {
    private $rows;
    public function __construct($rows) { $this->rows = $rows; }
    public function row() { return $this->rows[0] ?? null; }
    public function result() { return $this->rows; }
}
// Konsultan: SPK + TOP. Skenario: spk_missing, top_missing.
class PtConsultant {
    private $table = '';
    public function __call($n, $a) { return $this; }
    public function from($t) { $this->table = explode(' ', trim($t))[0]; return $this; }
    public function get() { return $this->resolve(); }
    public function get_where($t, $w) { $this->table = $t; return $this->resolve(); }
    private function resolve() {
        global $scenario;
        if ($this->table === 'kons_tr_spk_penawaran') {
            return new PtResult(empty($scenario['spk_missing']) ? array((object) array('id_spk_penawaran' => 'SPK/1')) : array());
        }
        return new PtResult(empty($scenario['top_missing']) ? array((object) array('id' => 5, 'term_payment' => 'T', 'persen_payment' => 50,
            'nominal_payment' => 100, 'desc_payment' => 'D')) : array());
    }
}
class PtDb {
    public $calls = array(); public $writes = array(); private $table = '';
    public function __call($n, $a) { $this->calls[] = $n; return $this; }
    public function from($t) { $this->table = explode(' ', trim($t))[0]; return $this; }
    public function get() { return $this->resolve(); }
    public function get_where($t, $w) { $this->table = $t; return $this->resolve(); }
    private function resolve() {
        global $scenario;
        if ($this->table === 'kons_tr_plan_tagih_header') {
            return new PtResult(!empty($scenario['existing']) ? array((object) array('id' => 'H1', 'keterangan_penagihan' => 'K')) : array());
        }
        return new PtResult(array((object) array('tgl_plan_tagih' => '2026-11-01', 'id_top' => 5, 'id' => 9)));
    }
    public function insert($t, $d) { $this->calls[] = 'insert'; $this->writes[] = array('insert', $t); return empty($GLOBALS['scenario']['header_fail']); }
    public function insert_batch($t, $d) { $this->calls[] = 'insert_batch'; $this->writes[] = array('insert_batch', $t); return empty($GLOBALS['scenario']['detail_fail']); }
    public function update($t, $d, $w) { $this->calls[] = 'update'; $this->writes[] = array('update', $t); return empty($GLOBALS['scenario']['update_fail']); }
    public function update_batch($t, $d, $k) { $this->calls[] = 'update_batch'; $this->writes[] = array('update_batch', $t, count($d)); return empty($GLOBALS['scenario']['batch_fail']); }
    public function trans_begin() { $this->calls[] = 'trans_begin'; }
    public function trans_status() { $this->calls[] = 'trans_status'; return empty($GLOBALS['scenario']['status_fail']); }
    public function trans_commit() { $this->calls[] = 'trans_commit'; return empty($GLOBALS['scenario']['commit_fail']); }
    public function trans_rollback() { $this->calls[] = 'trans_rollback'; }
    public function last_query() { return 'SELECT 1'; }
    public function error($r = null) { return array('code' => 0, 'message' => ''); }
}
class PtModel {
    public function generate_id() { return '0001/PLN-TGH/XI/26'; }
    public function get_all_plan_tagih_detail() { global $scenario; return !empty($scenario['no_detail']) ? array() : array((object) array('id' => 9)); }
    public function get_data_last_aktual($id) { return (object) array('tanggal_actual_plan_tagih' => '2026-11-02', 'tagih_mundur' => 0, 'sts_invoice' => 0); }
    public function get_invoicing($id) { return (object) array('tanggal_invoice' => '2026-11-03'); }
    public function get_data_spk() { echo json_encode(array('draw' => 1, 'data' => array())); }
}

require dirname(__DIR__) . '/controllers/Plan_tagih.php';

function pt_make($options, $post) {
    global $scenario, $events, $ci;
    $scenario = $options; $events = array();
    $ci = (new ReflectionClass('Plan_tagih'))->newInstanceWithoutConstructor();
    $ci->module_audit_recorder = new Module_audit_recorder();
    $ci->db = new PtDb();
    $prop = new ReflectionProperty('Plan_tagih', 'consultant'); $prop->setAccessible(true); $prop->setValue($ci, new PtConsultant());
    $ci->auth = new PtAuth(); $ci->template = new PtTemplate(); $ci->session = new PtSession();
    $ci->input = new PtInput(); $ci->load = new PtLoad(); $ci->Plan_tagih_model = new PtModel();
    $ci->input->values = array_merge(array(
        'id_spk_penawaran' => 'SPK/1', 'id_penawaran' => 'Q1', 'id_customer' => 'C1', 'nm_customer' => 'PRIVATE',
        'id_project' => 'P1', 'nm_project' => 'PRIVATE', 'id_project_leader' => 'L1', 'nm_project_leader' => 'PRIVATE',
        'nilai_bersih_project' => '1000', 'keterangan_penagihan' => 'PRIVATE', 'id_header' => '0001/PLN-TGH/XI/26',
        'dt' => array(array('id' => 5, 'tgl_plan_tagih' => '2026-11-01', 'id_plan_tagih' => 9)),
        'password' => 'PRIVATE_PASSWORD'), $post);
    return $ci;
}

// Child process: jalur yang memanggil exit (print_r + exit lama) tidak bisa dijalankan in-process.
if (($argv[1] ?? '') === '--exit') {
    pt_make(json_decode(base64_decode($argv[3]), true), array());
    register_shutdown_function(function () { global $ci; echo 'CALLS:' . json_encode($ci->db->calls) . "
"; });
    $ci->{$argv[2]}();
    exit;
}
if (($argv[1] ?? '') === '--subprocess-shutdown') {
    pt_make(array(), array());
    $ci->module_audit_recorder->begin('plan_tagih', 'index');
    exit;
}

function pt_run_exit($method, $options) {
    global $scenarioCount;
    $scenarioCount++;
    $raw = (string) shell_exec('"' . PHP_BINARY . '" "' . __FILE__ . '" --exit ' . $method . ' ' . base64_encode(json_encode($options)) . ' 2>&1');
    check(substr_count($raw, 'AUDIT:') === 1, $method . ' (exit): tepat satu event, output: ' . $raw);
    $event = json_decode(trim(strtok(substr($raw, strpos($raw, 'AUDIT:') + 6), "
")), true);
    $calls = json_decode(trim(substr($raw, strpos($raw, 'CALLS:') + 6)), true) ?: array();
    $stub = (object) array('db' => (object) array('calls' => $calls));
    return array($event, $raw, false, $stub);
}

function pt_run($method, $options = array(), $post = array(), $args = array()) {
    global $scenario, $events, $ci, $scenarioCount;
    $scenarioCount++;
    pt_make($options, $post);
    ob_start(); $stopped = false;
    try { call_user_func_array(array($ci, $method), $args); }
    catch (PtStop $s) { $stopped = true; }
    catch (Throwable $e) { ob_end_clean(); throw $e; }
    $output = ob_get_clean();
    $ci->module_audit_recorder->finish_interrupted();
    check(count($events) === 1, $method . ': expected exactly one audit event, got ' . count($events));
    check($events[0]['context']['module'] === 'plan_tagih', $method . ': wrong module');
    check($events[0]['context']['methode'] === $method, $method . ': wrong method');
    check(strpos(json_encode($events[0]['context']), 'PRIVATE') === false, $method . ': PII leaked');
    return array($events[0], $output, $stopped, $ci);
}
function pt_data($e) { return $e['context']['data']; }
function pt_json($o) { return json_decode($o, true); }


// ---- index: rendered + denied
list($e, $out) = pt_run('index');
check($e['status'] === 1 && pt_data($e)['outcome'] === 'rendered' && strpos($out, 'rendered index') !== false, 'index: rendered');
list($e, $out, $stopped) = pt_run('index', array('deny_perm' => true));
check($stopped && $e['status'] === 0 && pt_data($e)['outcome'] === 'denied' && pt_data($e)['reason'] === 'permission_denied' && $out === '', 'index: denied');

// ---- add/view/edit: rendered vs not_found; penulis audit tidak mengubah halaman
foreach (array('add_plan_tagih' => 'add_plan_tagih', 'view_plan_tagih' => 'view_plan_tagih', 'edit_plan_tagih' => 'revisi_plan_tagih') as $method => $tpl) {
    list($e, $out) = pt_run($method, array(), array(), array('SPK|1'));
    check($e['status'] === 1 && pt_data($e)['outcome'] === 'rendered' && pt_data($e)['id_spk_penawaran'] === 'SPK/1' && strpos($out, "rendered $tpl") !== false, "$method: rendered");
    list($e, $out) = pt_run($method, array('spk_missing' => true), array(), array('SPK|1'));
    check($e['status'] === 0 && pt_data($e)['outcome'] === 'not_found' && strpos($out, "rendered $tpl") !== false, "$method: SPK tidak ada -> not_found, halaman tetap sama");
    foreach (array('writer_failure', 'writer_exception') as $w) {
        list($e, $out) = pt_run($method, array($w => true), array(), array('SPK|1'));
        check($e['status'] === 1 && strpos($out, "rendered $tpl") !== false, "$method: halaman utuh saat $w");
    }
}
list($e) = pt_run('view_plan_tagih', array(), array(), array(str_repeat('X', 300)));
check(!isset(pt_data($e)['id_spk_penawaran']), 'view: id panjang/tidak valid tidak masuk metadata');

// ---- get_data_spk
list($e, $out) = pt_run('get_data_spk');
check($e['status'] === 1 && pt_data($e)['outcome'] === 'rendered' && pt_json($out)['draw'] === 1, 'get_data_spk: rendered');
foreach (array('writer_failure', 'writer_exception') as $w) {
    list($e, $out) = pt_run('get_data_spk', array($w => true));
    check(pt_json($out)['draw'] === 1, "get_data_spk: respons utuh saat $w");
}

// ---- save_plan_tagih
list($e, $out, , $c) = pt_run('save_plan_tagih');
check($e['status'] === 1 && pt_data($e)['outcome'] === 'saved' && pt_data($e)['id'] === '0001/PLN-TGH/XI/26' && pt_data($e)['detail_count'] === 1 && pt_json($out)['status'] === 1, 'save: saved');
check(in_array('trans_commit', $e['calls_at_write'], true), 'save: audit sukses sesudah commit');
list($e, $out, , $c) = pt_run('save_plan_tagih', array('existing' => true));
check($e['status'] === 0 && pt_data($e)['outcome'] === 'duplicate' && pt_json($out)['status'] === 0 && !$c->db->writes, 'save: duplicate, tidak menulis');
list($e, $out, , $c) = pt_run('save_plan_tagih', array('top_missing' => true));
check($e['status'] === 0 && pt_data($e)['outcome'] === 'top_not_found' && pt_json($out)['status'] === 0 && !$c->db->writes, 'save: TOP tidak ada, tidak menulis');
list($e, $out, , $c) = pt_run('save_plan_tagih', array('detail_fail' => true));
check($e['status'] === 0 && pt_data($e)['reason'] === 'detail_failed' && pt_json($out)['status'] === 0 && in_array('trans_rollback', $c->db->calls, true) && !in_array('trans_commit', $c->db->calls, true), 'save: detail gagal -> rollback');
list($e, $out, , $c) = pt_run('save_plan_tagih', array('status_fail' => true));
check($e['status'] === 0 && pt_data($e)['reason'] === 'transaction_failed' && pt_json($out)['status'] === 0 && !in_array('trans_commit', $c->db->calls, true), 'save: trans_status false -> tidak commit');
list($e, $out, , $c) = pt_run('save_plan_tagih', array('commit_fail' => true));
check($e['status'] === 0 && pt_data($e)['reason'] === 'commit_failed' && pt_json($out)['status'] === 0, 'save: commit gagal TIDAK boleh sukses');
// header gagal: perilaku lama print_r + exit dipertahankan; audit tetap tercatat gagal (bukan interrupted)
list($e, $out, , $c) = pt_run_exit('save_plan_tagih', array('header_fail' => true));
check($e['status'] === 0 && pt_data($e)['reason'] === 'header_failed' && pt_data($e)['outcome'] === 'failed' && $out !== '', 'save: header gagal tercatat gagal sebelum exit, output lama utuh');
foreach (array('writer_failure', 'writer_exception') as $w) {
    list($e, $out) = pt_run('save_plan_tagih', array($w => true));
    check(pt_json($out)['status'] === 1, "save: bisnis utuh saat $w");
}

// ---- revisi_plan_tagih
list($e, $out, , $c) = pt_run('revisi_plan_tagih');
check($e['status'] === 1 && pt_data($e)['outcome'] === 'revised' && pt_data($e)['id_header'] === '0001/PLN-TGH/XI/26' && pt_data($e)['detail_count'] === 1 && pt_json($out)['status'] === 1, 'revisi: revised');
check(in_array('trans_commit', $e['calls_at_write'], true), 'revisi: audit sukses sesudah commit');
list($e, $out, , $c) = pt_run('revisi_plan_tagih', array('status_fail' => true));
check($e['status'] === 0 && pt_data($e)['reason'] === 'transaction_failed' && pt_json($out)['status'] === 0 && in_array('trans_rollback', $c->db->calls, true), 'revisi: trans_status false -> rollback');
list($e, $out, , $c) = pt_run('revisi_plan_tagih', array('commit_fail' => true));
check($e['status'] === 0 && pt_data($e)['reason'] === 'commit_failed' && pt_json($out)['status'] === 0, 'revisi: commit gagal TIDAK boleh sukses');
foreach (array('update_fail' => 'header_failed', 'batch_fail' => 'detail_failed') as $opt => $reason) {
    list($e, $out, , $c) = pt_run_exit('revisi_plan_tagih', array($opt => true));
    check($e['status'] === 0 && pt_data($e)['reason'] === $reason && pt_data($e)['outcome'] === 'failed' && in_array('trans_rollback', $c->db->calls, true) && $out !== '', "revisi: $opt tercatat gagal, rollback, output lama utuh");
}
foreach (array('writer_failure', 'writer_exception') as $w) {
    list($e, $out) = pt_run('revisi_plan_tagih', array($w => true));
    check(pt_json($out)['status'] === 1, "revisi: bisnis utuh saat $w");
}

// ---- updateee
list($e, , , $c) = pt_run('updateee');
check($e['status'] === 1 && pt_data($e)['outcome'] === 'synced' && pt_data($e)['processed_count'] === 1 && in_array('trans_commit', $e['calls_at_write'], true), 'updateee: synced setelah commit');
list($e, , , $c) = pt_run('updateee', array('no_detail' => true));
check($e['status'] === 1 && pt_data($e)['outcome'] === 'synced' && pt_data($e)['processed_count'] === 0 && !in_array('update_batch', $c->db->calls, true), 'updateee: tidak ada data -> sukses 0, tanpa update_batch');
list($e, , , $c) = pt_run('updateee', array('batch_fail' => true));
check($e['status'] === 0 && pt_data($e)['reason'] === 'update_failed' && in_array('trans_rollback', $c->db->calls, true) && !in_array('trans_commit', $c->db->calls, true), 'updateee: update gagal -> rollback');
list($e, , , $c) = pt_run('updateee', array('commit_fail' => true));
check($e['status'] === 0 && pt_data($e)['reason'] === 'commit_failed', 'updateee: commit gagal TIDAK boleh sukses');
foreach (array('writer_failure', 'writer_exception') as $w) {
    list($e, , , $c) = pt_run('updateee', array($w => true));
    check($e['status'] === 1 && in_array('trans_commit', $c->db->calls, true), "updateee: bisnis utuh saat $w");
}

// ---- subprocess: exit nyata -> fallback interrupted
$GLOBALS['scenarioCount']++;
$sub = shell_exec('"' . PHP_BINARY . '" "' . __FILE__ . '" --subprocess-shutdown 2>&1');
check(substr_count((string) $sub, 'AUDIT:') === 1, 'subprocess: satu event interrupted');
$ev = json_decode(trim(substr($sub, strpos($sub, 'AUDIT:') + 6)), true);
check($ev['status'] === 0 && $ev['context']['data']['outcome'] === 'interrupted', 'subprocess: interrupted');

echo 'PASS Plan Tagih batch 1: ' . $scenarioCount . " skenario fixture/subprocess, controller+recorder asli\n";
