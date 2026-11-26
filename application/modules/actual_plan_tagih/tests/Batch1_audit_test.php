<?php
// Fase 4 / actual_plan_tagih batch 1+2: controller asli + recorder asli; persistensi bisnis = fixture in-memory.
if (!defined('AUDIT_FIXTURE_ONLY')) { define('AUDIT_FIXTURE_ONLY', true); }
if (!defined('FCPATH')) { define('FCPATH', sys_get_temp_dir() . '/fcpath/'); }

require dirname(__DIR__, 2) . '/request_payment/tests/Audit_test.php';

if (!function_exists('has_permission')) {
    function has_permission($perm = '') { global $scenario; return empty($scenario['deny_perm']); }
}

class AcStop extends Exception {}

class AcAuth {
    public function user_id() { return 10; }
    public function restrict($perm) { global $scenario; if (!empty($scenario['deny_perm'])) { throw new AcStop('restricted'); } return true; }
    public function has_permission($perm) { global $scenario; return empty($scenario['deny_manage']); }
}
class AcTemplate {
    public function set($k, $v = null) {}
    public function title($t) {}
    public function page_icon($i) {}
    public function render($v) { echo "<!-- rendered {$v} -->"; }
}
class AcSession { public function userdata($key = null) { return array('username' => 'tester', 'id_user' => 7); } }
class AcInput {
    public $values = array();
    public function post($key = null, $xss = null) { return $key === null ? $this->values : ($this->values[$key] ?? null); }
}
class AcLoad {
    public function library($l, $c = null) {}
    public function model($m, $alias = null) {}
    public function view($v, $d = array()) { echo "<!-- view {$v} rows=" . (isset($d['list_data']) ? count($d['list_data']) : '-') . " -->"; }
}
class AcOutput {
    public $status = 200; public $body = '';
    public function set_status_header($c) { $this->status = $c; return $this; }
    public function set_content_type($t) { return $this; }
    public function set_output($b) { $this->body = $b; echo $b; return $this; }
}
class AcUpload {
    public function initialize($c) {}
    public function do_upload($f) { global $scenario; return empty($scenario['upload_fail']); }
    public function data() { return array('file_name' => 'f.pdf'); }
    public function display_errors() { return 'upload error'; }
}
class AcResult {
    private $rows;
    public function __construct($rows) { $this->rows = $rows; }
    public function row() { return $this->rows[0] ?? null; }
    public function row_array() { $r = $this->rows[0] ?? null; return $r === null ? null : (array) $r; }
    public function result() { return $this->rows; }
    public function num_rows() { return count($this->rows); }
}
class AcConsultant {
    public function __call($n, $a) { return $this; }
    public function get() { global $scenario; return new AcResult(!empty($scenario['spk_missing']) ? array() : array((object) array('id' => 5, 'id_spk_penawaran' => 'SPK/1'))); }
}
class AcDb {
    public $calls = array(); public $writes = array(); private $table = ''; private $select = ''; private $offset = 0; private $inserts = 0;
    public function __call($n, $a) { $this->calls[] = $n; return $this; }
    public function select($s) { $this->select = (string) $s; return $this; }
    public function from($t) { $this->table = explode(' ', trim($t))[0]; return $this; }
    public function limit($n, $o = 0) { $this->offset = $o; return $this; }
    public function get() { return $this->resolve(); }
    public function get_where($t, $w) { $this->table = $t; return $this->resolve(); }
    private function resolve() {
        global $scenario;
        $offset = $this->offset; $this->offset = 0; $select = $this->select; $this->select = '';
        if ($this->table === 'kons_tr_plan_tagih_detail') {
            if (!empty($scenario['detail_missing'])) { return new AcResult(array()); }
            return new AcResult(array((object) array('id' => 9, 'tgl_plan_tagih' => '2026-11-01', 'id_detail_plan_tagih' => 9)));
        }
        if ($this->table === 'kons_tr_actual_plan_tagih') {
            if (!empty($scenario['actual_missing'])) { return new AcResult(array()); }
            if (stripos($select, 'COUNT') !== false) { return new AcResult(array((object) array('jumlah_mundur' => empty($scenario['no_sync']) ? 1 : 0))); }
            if ($offset === 1) { return new AcResult(array((object) array('tagih_mundur' => '2', 'tanggal_actual_plan_tagih' => '2026-11-05'))); }
            return new AcResult(array((object) array('id' => '00001/ACT-TGH/XI/26', 'tagih_mundur' => '2', 'tanggal_actual_plan_tagih' => '2026-11-01',
                'alasan_mundur' => 'PRIVATE', 'created_date' => '2026-11-01')));
        }
        return new AcResult(array());
    }
    public function insert($t, $d) {
        global $scenario;
        $this->calls[] = 'insert'; $this->writes[] = array('insert', $t);
        if ($t === 'kons_tr_actual_plan_tagih') { $this->inserts++; }
        if ($t === 'kons_tr_actual_plan_tagih' && !empty($scenario['insert_fail'])) { return false; }
        if ($t === 'kons_tr_actual_plan_tagih' && !empty($scenario['insert_fail_nth']) && $this->inserts === $scenario['insert_fail_nth']) { return false; }
        return true;
    }
    public function insert_batch($t, $d) { $this->calls[] = 'insert_batch'; $this->writes[] = array('insert_batch', $t); return empty($GLOBALS['scenario']['journal_fail']); }
    public function update($t, $d, $w = null) {
        global $scenario;
        $this->calls[] = 'update'; $this->writes[] = array('update', $t);
        if ($t === 'kons_tr_plan_tagih_detail' && !empty($scenario['detail_update_fail'])) { return false; }
        if ($t === 'kons_tr_actual_plan_tagih' && !empty($scenario['actual_update_fail'])) { return false; }
        return true;
    }
    public function update_batch($t, $d, $k) { $this->calls[] = 'update_batch'; $this->writes[] = array('update_batch', $t, count($d)); return empty($GLOBALS['scenario']['batch_fail']); }
    public function trans_begin() { $this->calls[] = 'trans_begin'; }
    public function trans_status() { $this->calls[] = 'trans_status'; return empty($GLOBALS['scenario']['status_fail']); }
    public function trans_commit() {
        global $scenario; $this->calls[] = 'trans_commit';
        if (!empty($scenario['commit_fail'])) { return false; }
        if (!empty($scenario['commit_fail_nth']) && count(array_keys($this->calls, 'trans_commit')) === $scenario['commit_fail_nth']) { return false; }
        return true;
    }
    public function trans_rollback() { $this->calls[] = 'trans_rollback'; }
    public function last_query() { return 'SELECT 1'; }
    public function error($r = null) { return array('code' => 0, 'message' => ''); }
}
class AcModel {
    public function generate_id($n = null) { return '00001/ACT-TGH/XI/26'; }
    public function dataDownloadExcel($tahun = null, $status = null) { global $scenario; return !empty($scenario['no_rows']) ? array() : array(array('id' => 1), array('id' => 2)); }
    public function get_actual_plan_tagih() { echo json_encode(array('draw' => 1, 'data' => array())); }
    public function get_batch_records($a, $b) {
        global $scenario;
        if (!empty($scenario['model_exception'])) { throw new Exception('PRIVATE internal'); }
        if (!empty($scenario['no_records'])) { return array(); }
        $n = !empty($scenario['two_records']) ? 2 : 1; $rows = array();
        for ($i = 1; $i <= $n; $i++) {
            $rows[] = (object) array('id' => 100 + $i, 'id_top' => 5, 'id_spk_penawaran' => 'SPK/1', 'id_penawaran' => 'Q1', 'term_payment' => 'T',
                'persen_payment' => 50, 'nominal_payment' => 1000, 'desc_payment' => 'D', 'tgl_plan_tagih' => '2026-11-01', 'urutan' => $i,
                'id_customer' => 'C1', 'nm_customer' => 'PRIVATE', 'address' => 'PRIVATE', 'id_project' => 'P1', 'nm_project' => 'PRIVATE',
                'id_project_leader' => 'L1', 'nm_project_leader' => 'PRIVATE', 'id_sales' => 'S1', 'nm_sales' => 'PRIVATE', 'nm_company' => 'PRIVATE');
        }
        return $rows;
    }
    public function get_record_for_processing($id) { global $scenario; return !empty($scenario['guard_null']) ? null : (object) array('id' => $id); }
}
class AcInvoicingModel {
    public function generate_id() { return 'INV-1'; }
    public function generate_id_invoice_jurnal($n) { return 'JV-' . $n; }
}

require dirname(__DIR__) . '/controllers/Actual_plan_tagih.php';

function ac_make($options, $post) {
    global $scenario, $events, $ci;
    $scenario = $options; $events = array();
    $ci = (new ReflectionClass('Actual_plan_tagih'))->newInstanceWithoutConstructor();
    $ci->module_audit_recorder = new Module_audit_recorder();
    $ci->db = new AcDb();
    $prop = new ReflectionProperty('Actual_plan_tagih', 'consultant'); $prop->setAccessible(true); $prop->setValue($ci, new AcConsultant());
    $ci->auth = new AcAuth(); $ci->template = new AcTemplate(); $ci->session = new AcSession();
    $ci->input = new AcInput(); $ci->load = new AcLoad(); $ci->output = new AcOutput(); $ci->upload = new AcUpload();
    $ci->Actual_plan_tagih_model = new AcModel(); $ci->Invoicing_model = new AcInvoicingModel();
    $ci->input->values = array_merge(array(
        'id' => 9, 'id_detail_plan_tagih' => 9, 'macet' => '0', 'tagih_mundur' => '1', 'tanggal_actual' => '2026-11-02',
        'tgl_plan_tagih' => '2026-11-01', 'id_top' => 5, 'id_spk_penawaran' => 'SPK/1', 'id_penawaran' => 'Q1', 'term_payment' => 'T',
        'persen_payment' => 50, 'nominal_payment' => 1000, 'desc_payment' => 'D', 'urutan' => 1, 'alasan_mundur' => 'PRIVATE',
        'password' => 'PRIVATE_PASSWORD'), $post);
    return $ci;
}

// Child process: jalur print_r + exit lama tidak bisa dijalankan in-process.
if (($argv[1] ?? '') === '--exit') {
    $opts = json_decode(base64_decode($argv[3]), true);
    if (!empty($opts['files'])) { $_FILES = array('upload_laporan_progress' => array('error' => 0, 'filename' => 'x')); }
    ac_make($opts, array('tagih_mundur' => '3'));
    register_shutdown_function(function () { global $ci; echo 'CALLS:' . json_encode($ci->db->calls) . "\n"; });
    $ci->{$argv[2]}();
    exit;
}
if (($argv[1] ?? '') === '--subprocess-shutdown') {
    ac_make(array(), array());
    $ci->module_audit_recorder->begin('actual_plan_tagih', 'index');
    exit;
}

function ac_run_exit($method, $options) {
    global $scenarioCount;
    $scenarioCount++;
    $raw = (string) shell_exec('"' . PHP_BINARY . '" "' . __FILE__ . '" --exit ' . $method . ' ' . base64_encode(json_encode($options)) . ' 2>&1');
    check(substr_count($raw, 'AUDIT:') === 1, $method . ' (exit): tepat satu event, output: ' . $raw);
    $event = json_decode(trim(strtok(substr($raw, strpos($raw, 'AUDIT:') + 6), "\n")), true);
    $calls = json_decode(trim(substr($raw, strpos($raw, 'CALLS:') + 6)), true) ?: array();
    return array($event, $raw, false, (object) array('db' => (object) array('calls' => $calls)));
}

function ac_run($method, $options = array(), $post = array(), $args = array()) {
    global $scenario, $events, $ci, $scenarioCount;
    $scenarioCount++;
    ac_make($options, $post);
    ob_start(); $stopped = false;
    try { call_user_func_array(array($ci, $method), $args); }
    catch (AcStop $s) { $stopped = true; }
    catch (Throwable $e) { ob_end_clean(); throw $e; }
    $output = ob_get_clean();
    $ci->module_audit_recorder->finish_interrupted();
    check(count($events) === 1, $method . ': expected exactly one audit event, got ' . count($events));
    check($events[0]['context']['module'] === 'actual_plan_tagih', $method . ': wrong module');
    check($events[0]['context']['methode'] === $method, $method . ': wrong method');
    check(strpos(json_encode($events[0]['context']), 'PRIVATE') === false, $method . ': PII leaked');
    return array($events[0], $output, $stopped, $ci);
}
function ac_data($e) { return $e['context']['data']; }
function ac_json($o) { return json_decode($o, true); }
function ac_writers($method, $post, $args, $assert) {
    foreach (array('writer_failure', 'writer_exception') as $w) {
        list($e, $out) = ac_run($method, array($w => true), $post, $args);
        $assert($e, $out, $w);
    }
}

// ---- index
list($e, $out) = ac_run('index');
check($e['status'] === 1 && ac_data($e)['outcome'] === 'rendered' && strpos($out, 'rendered index') !== false, 'index: rendered');
list($e, $out, $stopped) = ac_run('index', array('deny_perm' => true));
check($stopped && $e['status'] === 0 && ac_data($e)['outcome'] === 'denied' && ac_data($e)['reason'] === 'permission_denied' && $out === '', 'index: denied');
ac_writers('index', array(), array(), function ($e, $out, $w) { check(strpos($out, 'rendered index') !== false, "index: utuh saat $w"); });

// ---- add_plan_tagih
list($e, $out) = ac_run('add_plan_tagih', array(), array(), array('SPK|1'));
check($e['status'] === 1 && ac_data($e)['outcome'] === 'rendered' && ac_data($e)['id_spk_penawaran'] === 'SPK/1', 'add_plan_tagih: rendered');
list($e, $out) = ac_run('add_plan_tagih', array('spk_missing' => true), array(), array('SPK|1'));
check($e['status'] === 0 && ac_data($e)['outcome'] === 'not_found' && strpos($out, 'rendered add_plan_tagih') !== false, 'add_plan_tagih: not_found');
ac_writers('add_plan_tagih', array(), array('SPK|1'), function ($e, $out, $w) { check(strpos($out, 'rendered add_plan_tagih') !== false, "add_plan_tagih: utuh saat $w"); });

// ---- aktual_tagihan_get / macet_get
foreach (array('aktual_tagihan_get' => 'form_actual_plan_tagih', 'aktual_tagihan_macet_get' => 'form_actual_plan_tagih_macet') as $m => $tpl) {
    list($e, $out) = ac_run($m);
    check($e['status'] === 1 && ac_data($e)['outcome'] === 'rendered' && ac_data($e)['id_detail_plan_tagih'] == 9 && strpos($out, "rendered $tpl") !== false, "$m: rendered");
    // detail tidak ada; riwayat actual tersedia agar kode lama tidak menyentuh properti null
    list($e, $out) = ac_run($m, array('detail_missing' => true));
    check($e['status'] === 0 && ac_data($e)['outcome'] === 'not_found' && strpos($out, "rendered $tpl") !== false, "$m: not_found");
    ac_writers($m, array(), array(), function ($e, $out, $w) use ($m, $tpl) { check(strpos($out, "rendered $tpl") !== false, "$m: utuh saat $w"); });
}

// ---- save_actual_plan_tagih
list($e, $out, , $c) = ac_run('save_actual_plan_tagih');
check($e['status'] === 1 && ac_data($e)['outcome'] === 'saved' && ac_data($e)['id'] === '00001/ACT-TGH/XI/26' && ac_data($e)['upload_failed_count'] === 0 && ac_json($out)['status'] === '1' && $c->output->status === 200, 'save_actual: saved');
check(in_array('trans_commit', $e['calls_at_write'], true), 'save_actual: audit sukses sesudah commit');
list($e, $out, , $c) = ac_run('save_actual_plan_tagih', array(), array('macet' => '1', 'tagih_mundur' => '3'));
check($e['status'] === 1 && ac_data($e)['outcome'] === 'macet_updated' && ac_json($out)['status'] === '1', 'save_actual: macet_updated');
list($e, $out, , $c) = ac_run('save_actual_plan_tagih', array(), array('tagih_mundur' => '2', 'tanggal_actual' => ''));
check($e['status'] === 0 && ac_data($e)['reason'] === 'validation_failed' && $c->output->status === 500 && in_array('trans_rollback', $c->db->calls, true) && !$c->db->writes, 'save_actual: validasi mundur tanpa tanggal, tidak menulis');
list($e, $out, , $c) = ac_run('save_actual_plan_tagih', array('insert_fail' => true));
check($e['status'] === 0 && ac_data($e)['reason'] === 'insert_failed' && $c->output->status === 500 && in_array('trans_rollback', $c->db->calls, true), 'save_actual: insert gagal');
list($e, $out, , $c) = ac_run('save_actual_plan_tagih', array('detail_update_fail' => true));
check($e['status'] === 0 && ac_data($e)['reason'] === 'update_failed' && $c->output->status === 500 && !in_array('trans_commit', $c->db->calls, true), 'save_actual: update detail gagal tidak dianggap sukses');
list($e, $out, , $c) = ac_run('save_actual_plan_tagih', array('actual_update_fail' => true), array('macet' => '1'));
check($e['status'] === 0 && ac_data($e)['reason'] === 'update_failed' && !in_array('trans_commit', $c->db->calls, true), 'save_actual: macet update gagal tidak dianggap sukses');
list($e, $out, , $c) = ac_run('save_actual_plan_tagih', array('status_fail' => true));
check($e['status'] === 0 && ac_data($e)['reason'] === 'transaction_failed' && !in_array('trans_commit', $c->db->calls, true), 'save_actual: trans_status false -> tidak commit');
list($e, $out, , $c) = ac_run('save_actual_plan_tagih', array('commit_fail' => true));
check($e['status'] === 0 && ac_data($e)['reason'] === 'commit_failed' && $c->output->status === 500 && ac_json($out)['msg'] !== null && !isset(ac_json($out)['status']), 'save_actual: commit gagal TIDAK boleh sukses');
// upload opsional gagal: bisnis tetap sukses, tercatat
$_FILES = array('upload_surat_mundur' => array('error' => 0));
list($e, $out) = ac_run('save_actual_plan_tagih', array('upload_fail' => true));
check($e['status'] === 1 && ac_data($e)['upload_failed_count'] === 1 && ac_json($out)['status'] === '1', 'save_actual: upload gagal dihitung, bisnis tetap sukses');
list($e) = ac_run('save_actual_plan_tagih');
check($e['status'] === 1 && ac_data($e)['upload_failed_count'] === 0, 'save_actual: upload berhasil -> 0');
$_FILES = array();
foreach (array('writer_failure', 'writer_exception') as $w) {
    list($e, $out, , $c) = ac_run('save_actual_plan_tagih', array($w => true));
    check($e['status'] === 1 && ac_json($out)['status'] === '1' && in_array('trans_commit', $c->db->calls, true), "save_actual: bisnis utuh saat $w");
}

// ---- save_actual_plan_tagih_macet
list($e, $out, , $c) = ac_run('save_actual_plan_tagih_macet');
check($e['status'] === 1 && ac_data($e)['outcome'] === 'saved' && ac_json($out)['status'] === 1 && in_array('trans_commit', $e['calls_at_write'], true), 'macet: saved sesudah commit');
list($e, $out, , $c) = ac_run('save_actual_plan_tagih_macet', array('status_fail' => true));
check($e['status'] === 0 && ac_data($e)['reason'] === 'transaction_failed' && ac_json($out)['status'] === 0 && !in_array('trans_commit', $c->db->calls, true), 'macet: trans_status false');
list($e, $out, , $c) = ac_run('save_actual_plan_tagih_macet', array('commit_fail' => true));
check($e['status'] === 0 && ac_data($e)['reason'] === 'commit_failed' && ac_json($out)['status'] === 0, 'macet: commit gagal TIDAK boleh sukses');
foreach (array('actual_update_fail' => 'update_failed') as $opt => $reason) {
    list($e, $raw, , $c) = ac_run_exit('save_actual_plan_tagih_macet', array($opt => true));
    check($e['status'] === 0 && ac_data($e)['reason'] === $reason && ac_data($e)['outcome'] === 'failed' && in_array('trans_rollback', $c->db->calls, true) && strpos($raw, 'SELECT 1') !== false, "macet: $opt tercatat gagal, rollback, output lama utuh");
}
list($e, $raw) = ac_run_exit('save_actual_plan_tagih_macet', array('files' => true, 'upload_fail' => true));
check($e['status'] === 0 && ac_data($e)['reason'] === 'upload_failed' && strpos($raw, 'upload error') !== false, 'macet: upload gagal tercatat gagal sebelum exit, output lama utuh');
foreach (array('writer_failure', 'writer_exception') as $w) {
    list($e, $out) = ac_run('save_actual_plan_tagih_macet', array($w => true));
    check($e['status'] === 1 && ac_json($out)['status'] === 1, "macet: bisnis utuh saat $w");
}

// ---- download_excel
list($e, $out) = ac_run('download_excel', array(), array(), array('2026', 'waiting'));
check($e['status'] === 1 && ac_data($e)['outcome'] === 'generated' && ac_data($e)['row_count'] === 2 && ac_data($e)['tahun'] === '2026' && ac_data($e)['status_filter'] === 'waiting' && strpos($out, 'view download_excel rows=2') !== false, 'download_excel: generated');
list($e, $out) = ac_run('download_excel', array('no_rows' => true), array(), array(null, null));
check($e['status'] === 1 && ac_data($e)['row_count'] === 0, 'download_excel: kosong tetap tercatat');
list($e) = ac_run('download_excel', array(), array(), array('2026; DROP', 'x y'));
check(!isset(ac_data($e)['tahun']) && !isset(ac_data($e)['status_filter']), 'download_excel: filter URL tidak wajar tidak masuk metadata');
ac_writers('download_excel', array(), array('2026', null), function ($e, $out, $w) { check(strpos($out, 'view download_excel') !== false, "download_excel: file utuh saat $w"); });

// ---- update_actual_plan_tagih (tanpa transaksi)
list($e, , , $c) = ac_run('update_actual_plan_tagih');
check($e['status'] === 1 && ac_data($e)['outcome'] === 'synced' && ac_data($e)['processed_count'] === 1 && in_array('update_batch', $c->db->calls, true), 'update_actual: synced 1');
list($e, , , $c) = ac_run('update_actual_plan_tagih', array('no_sync' => true));
check($e['status'] === 1 && ac_data($e)['processed_count'] === 0 && !in_array('update_batch', $c->db->calls, true), 'update_actual: tidak ada perubahan -> 0 tanpa update_batch');
list($e) = ac_run('update_actual_plan_tagih', array('batch_fail' => true));
check($e['status'] === 0 && ac_data($e)['reason'] === 'update_failed' && ac_data($e)['outcome'] === 'failed', 'update_actual: update_batch gagal');
foreach (array('writer_failure', 'writer_exception') as $w) {
    list($e, , , $c) = ac_run('update_actual_plan_tagih', array($w => true));
    check($e['status'] === 1 && in_array('update_batch', $c->db->calls, true), "update_actual: bisnis utuh saat $w");
}

// ---- get_actual_plan_tagih
list($e, $out) = ac_run('get_actual_plan_tagih');
check($e['status'] === 1 && ac_data($e)['outcome'] === 'rendered' && ac_json($out)['draw'] === 1, 'get_actual: rendered');
ac_writers('get_actual_plan_tagih', array(), array(), function ($e, $out, $w) { check(ac_json($out)['draw'] === 1, "get_actual: utuh saat $w"); });

// ---- batch_process_tagih
list($e, $out, , $c) = ac_run('batch_process_tagih', array('deny_manage' => true));
check($e['status'] === 0 && ac_data($e)['outcome'] === 'denied' && ac_data($e)['reason'] === 'permission_denied' && $c->output->status === 403 && !$c->db->writes, 'batch: denied 403, tidak menulis');
list($e, $out) = ac_run('batch_process_tagih', array('no_records' => true));
check($e['status'] === 1 && ac_data($e)['outcome'] === 'no_records' && ac_data($e)['total_found'] === 0 && ac_json($out)['total_found'] === 0, 'batch: no_records');
list($e, $out, , $c) = ac_run('batch_process_tagih');
check($e['status'] === 1 && ac_data($e)['outcome'] === 'processed' && ac_data($e)['processed_count'] === 1 && ac_data($e)['failed_count'] === 0 && ac_json($out)['total_success'] === 1, 'batch: processed');
check(in_array('trans_commit', $e['calls_at_write'], true), 'batch: audit sesudah commit');
list($e, $out, , $c) = ac_run('batch_process_tagih', array('two_records' => true));
check($e['status'] === 1 && ac_data($e)['processed_count'] === 2 && ac_data($e)['total_found'] === 2, 'batch: dua record');
list($e, $out, , $c) = ac_run('batch_process_tagih', array('two_records' => true, 'insert_fail_nth' => 2));
check($e['status'] === 1 && ac_data($e)['outcome'] === 'partial_failure' && ac_data($e)['processed_count'] === 1 && ac_data($e)['failed_count'] === 1 && ac_json($out)['total_failed'] === 1, 'batch: sebagian gagal = partial_failure, sukses karena sebagian sudah commit');
list($e, $out, , $c) = ac_run('batch_process_tagih', array('insert_fail' => true));
check($e['status'] === 0 && ac_data($e)['outcome'] === 'all_failed' && ac_data($e)['processed_count'] === 0 && ac_data($e)['failed_count'] === 1, 'batch: semua gagal');
list($e, $out, , $c) = ac_run('batch_process_tagih', array('journal_fail' => true));
check($e['status'] === 0 && ac_data($e)['outcome'] === 'all_failed' && in_array('trans_rollback', $c->db->calls, true) && !in_array('trans_commit', $c->db->calls, true), 'batch: jurnal gagal -> rollback record');
list($e, $out, , $c) = ac_run('batch_process_tagih', array('commit_fail' => true));
check($e['status'] === 0 && ac_data($e)['outcome'] === 'all_failed' && ac_json($out)['total_success'] === 0 && ac_json($out)['total_failed'] === 1, 'batch: commit gagal TIDAK dihitung sukses');
list($e, $out, , $c) = ac_run('batch_process_tagih', array('two_records' => true, 'commit_fail_nth' => 2));
check($e['status'] === 1 && ac_data($e)['outcome'] === 'partial_failure' && ac_data($e)['processed_count'] === 1 && ac_data($e)['failed_count'] === 1, 'batch: commit record ke-2 gagal = partial_failure');
list($e, $out, , $c) = ac_run('batch_process_tagih', array('guard_null' => true));
check($e['status'] === 1 && ac_data($e)['processed_count'] === 0 && ac_data($e)['macet_excluded'] === 1 && ac_data($e)['total_found'] === 1, 'batch: record dilewati guard dihitung macet_excluded');
list($e, $out, , $c) = ac_run('batch_process_tagih', array('model_exception' => true));
check($e['status'] === 0 && ac_data($e)['reason'] === 'system_error' && $c->output->status === 500, 'batch: system error');
foreach (array('writer_failure', 'writer_exception') as $w) {
    list($e, $out, , $c) = ac_run('batch_process_tagih', array($w => true));
    check($e['status'] === 1 && ac_json($out)['total_success'] === 1 && in_array('trans_commit', $c->db->calls, true), "batch: bisnis utuh saat $w");
}

// ---- view_last_input
list($e, $out) = ac_run('view_last_input');
check($e['status'] === 1 && ac_data($e)['outcome'] === 'rendered' && ac_data($e)['id'] == 9 && strpos($out, 'view view_actual_plan_tagih') !== false, 'view_last_input: rendered');
list($e, $out) = ac_run('view_last_input', array('actual_missing' => true));
check($e['status'] === 0 && ac_data($e)['outcome'] === 'not_found' && strpos($out, 'view view_actual_plan_tagih') !== false, 'view_last_input: not_found');
ac_writers('view_last_input', array(), array(), function ($e, $out, $w) { check(strpos($out, 'view view_actual_plan_tagih') !== false, "view_last_input: utuh saat $w"); });

// ---- subprocess: exit nyata -> fallback interrupted
$GLOBALS['scenarioCount']++;
$sub = shell_exec('"' . PHP_BINARY . '" "' . __FILE__ . '" --subprocess-shutdown 2>&1');
check(substr_count((string) $sub, 'AUDIT:') === 1, 'subprocess: satu event interrupted');
$ev = json_decode(trim(substr($sub, strpos($sub, 'AUDIT:') + 6)), true);
check($ev['status'] === 0 && $ev['context']['data']['outcome'] === 'interrupted', 'subprocess: interrupted');

echo 'PASS Actual Plan Tagih batch 1+2: ' . $scenarioCount . " skenario fixture/subprocess, controller+recorder asli\n";
