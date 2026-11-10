<?php
// Fase 4 / invoicing batch 1-4 (38 endpoint): controller asli + recorder asli; persistensi bisnis = fixture in-memory.
// Service/writer (Invoice_consulting_service, Invoice_writer) memakai kelas asli di atas fixture DB, kecuali save yang dimock
// pada level hasil (status) karena transaksi internalnya sudah punya suite sendiri (Consulting_integration_test, integration_test).
if (!defined('AUDIT_FIXTURE_ONLY')) { define('AUDIT_FIXTURE_ONLY', true); }
if (!defined('FCPATH')) { define('FCPATH', sys_get_temp_dir() . '/fcpath/'); }
if (!defined('DBCNL')) { define('DBCNL', 'db_cnl'); }

require dirname(__DIR__, 2) . '/request_payment/tests/Audit_test.php';
require_once dirname(__DIR__) . '/libraries/Invoice_consulting.php';
require_once dirname(__DIR__) . '/libraries/Invoice_calculation.php';

if (!function_exists('has_permission')) {
    function has_permission($perm = '') { global $scenario; return empty($scenario['deny_perm']); }
}
if (!function_exists('base_url')) {
    function base_url($p = '') { return 'http://localhost/' . $p; }
}
if (!function_exists('show_error')) {
    function show_error($m, $code = 500) { $GLOBALS['show_error_code'] = $code; }
}
if (!function_exists('redirect')) {
    // redirect() CI3 memanggil exit; stub menghentikan eksekusi dengan cara yang sama.
    function redirect($uri = '') { $GLOBALS['redirectTarget'] = $uri; throw new IvStop('redirect'); }
}

class IvStop extends Exception {}

class IvAuth {
    public function user_id() { return 10; }
    public function is_admin() { global $scenario; return empty($scenario['not_admin']); }
    public function restrict($perm) { global $scenario; if (!empty($scenario['deny_perm'])) { throw new IvStop('restricted'); } return true; }
    public function has_permission($perm) { global $scenario; return empty($scenario['deny_perm']); }
}
class IvTemplate {
    public function set($k, $v = null) {}
    public function title($t) {}
    public function render($v) { echo "<!-- rendered {$v} -->"; }
}
class IvSession {
    public function userdata($key = null) { return array('username' => 'tester', 'id_user' => 7); }
    public function set_flashdata($k, $v) {}
}
class IvInput {
    public $post = array(); public $get = array();
    public function post($key = null, $xss = null) { return $key === null ? $this->post : ($this->post[$key] ?? null); }
    public function get($key = null, $xss = null) { return $key === null ? $this->get : ($this->get[$key] ?? null); }
}
class IvLoad {
    public function library($l, $c = null) {}
    public function model($m, $a = null) {}
    public function view($v, $d = array(), $return = false) { if ($return) { return '<tr/>'; } echo "<!-- view {$v} -->"; }
}
class IvOutput {
    public $status = 200; public $body = '';
    public function set_content_type($t) { return $this; }
    public function set_status_header($c) { $this->status = $c; return $this; }
    public function set_output($b) { $this->body = $b; echo $b; return $this; }
}
class IvResult {
    private $rows;
    public function __construct($rows) { $this->rows = $rows; }
    public function row() { return $this->rows[0] ?? null; }
    public function row_array() { $r = $this->rows[0] ?? null; return $r === null ? null : (array) $r; }
    public function result() { return $this->rows; }
    public function result_array() { return array_map(function ($r) { return (array) $r; }, $this->rows); }
    public function num_rows() { return count($this->rows); }
}
// Satu fixture DB dipakai untuk db/consultant/accounting; perilaku dikendalikan $scenario.
class IvDb {
    public $calls = array(); public $writes = array(); public $db_debug = true; private $table = '';
    public function __call($n, $a) { $this->calls[] = $n; return $this; }
    public function from($t) { $this->table = explode(' ', trim($t))[0]; return $this; }
    public function get() { return $this->resolve(); }
    public function get_where($t, $w = null) { $this->table = $t; return $this->resolve(); }
    public function count_all_results() { return 3; }
    public function field_exists($c, $t) { return false; }
    public function table_exists($t) { return true; }
    public function query($sql, $b = array()) {
        global $scenario;
        if (stripos($sql, 'GET_LOCK') !== false) { return new IvResult(array(array('acquired' => empty($scenario['lock_fail']) ? 1 : 0))); }
        if (stripos($sql, 'saldo_piutang FROM tr_invoicing') !== false) { return new IvResult(array((object) array('saldo_piutang' => $scenario['saldo'] ?? 0))); }
        if (stripos($sql, 'tr_jurnal') !== false) { return new IvResult(array()); }
        return new IvResult(array());
    }
    private function resolve() {
        global $scenario;
        $t = $this->table; $this->table = '';
        if ($t === 'tr_invoicing') {
            if (!empty($scenario['invoice_missing'])) { return new IvResult(array()); }
            return new IvResult(array((object) array_merge(array('id' => '00001-INV-XI-26', 'id_actual_plan_tagih' => 'A1', 'id_detail_plan_tagih' => 'D1',
                'id_penawaran' => 'PN1', 'id_spk_penawaran' => 'SPK/1', 'tanggal_invoice' => '2026-11-01', 'no_invoice' => 'INV-1', 'no_revisi' => 0,
                'biaya_kirim' => 0, 'sts_close' => '0', 'consulting_version' => !empty($scenario['consulting']) ? 3 : 0, 'consulting_snapshot' => '', 'tipe_invoice' => '0', 'non_kons' => '0',
                'nm_customer' => 'PRIVATE', 'address' => 'PRIVATE', 'nm_company' => 'PRIVATE', 'penjualan' => 'PRIVATE', 'pic' => 'PRIVATE', 'total_invoice' => 1), $scenario['invoice_extra'] ?? array())));
        }
        if ($t === 'tr_penerimaan_piutang_detail' || $t === 'tr_jurnal') { return new IvResult(!empty($scenario['posted']) && $t === 'tr_jurnal' ? array((object) array('sts' => '1')) : array()); }
        if ($t === 'coa_master') {
            return new IvResult(!empty($scenario['coa_missing']) ? array() : array((object) array('no_perkiraan' => '1102-01-01', 'nm_coa' => 'Piutang', 'nama' => 'Piutang')));
        }
        if (in_array($t, array('kons_tr_actual_plan_tagih', 'kons_tr_plan_tagih_detail'), true)) {
            return new IvResult(array((object) array('id' => 'A1', 'id_company' => 3, 'nm_company' => 'PRIVATE', 'nominal_payment' => 1000, 'id_penawaran' => 'Q1',
                'id_spk_penawaran' => 'SPK/1', 'nm_customer' => 'PRIVATE', 'address' => 'PRIVATE')));
        }
        return new IvResult(array((object) array('id' => 5, 'id_company' => 3, 'nm_company' => 'PRIVATE', 'nm_customer' => 'PRIVATE', 'address' => 'PRIVATE', 'id_penawaran' => 'PN1', 'total_invoice' => 1)));
    }
    public function insert($t, $d) { $this->calls[] = 'insert'; $this->writes[] = array('insert', $t); return true; }
    public function insert_batch($t, $d) { $this->calls[] = 'insert_batch'; $this->writes[] = array('insert_batch', $t); return empty($GLOBALS['scenario']['batch_fail']); }
    public function update($t, $d = null, $w = null) {
        global $scenario; $this->calls[] = 'update'; $this->writes[] = array('update', $t);
        if ($t === 'tr_jurnal' && !empty($scenario['jurnal_update_fail'])) { return false; }
        if ($t === 'tr_invoicing' && !empty($scenario['update_fail'])) { return false; }
        return true;
    }
    public function delete($t, $w = null) { $this->calls[] = 'delete'; $this->writes[] = array('delete', $t); return empty($GLOBALS['scenario']['delete_fail']); }
    public function affected_rows() { global $scenario; return isset($scenario['affected']) ? $scenario['affected'] : 1; }
    public function trans_begin() { $this->calls[] = 'trans_begin'; return true; }
    public function trans_status() { $this->calls[] = 'trans_status'; return empty($GLOBALS['scenario']['status_fail']); }
    public function trans_commit() { $this->calls[] = 'trans_commit'; return empty($GLOBALS['scenario']['commit_fail']); }
    public function trans_rollback() { $this->calls[] = 'trans_rollback'; return true; }
    public function last_query() { return 'SELECT 1'; }
    public function error($r = null) { return array('code' => 0, 'message' => 'db error'); }
}
class IvModel {
    public function get_penawaran_non_konsultasi($id) {
        global $scenario;
        return !empty($scenario['penawaran_missing']) ? null : (object) array('id_penawaran' => 'PN1', 'id_company' => 3, 'grand_total' => 1000000,
            'keterangan_penawaran' => 'PRIVATE', 'nm_pic_penawaran' => 'PRIVATE');
    }
    public function get_detail_penawaran_non_konsultasi($id) { return array(); }
    public function total_invoiced_non_kons($id) { return 0; }
    public function jurnal_invoicing_non_konsultasi($id, $d = null) { return array('hasil_jurnal' => '', 'total_debit' => 0, 'total_kredit' => 0); }
    public function preview_no_invoice($c, $t) { return 'INV-PREVIEW'; }
    public function generate_no_invoice($c, $t) { return 'INV-NEW'; }
    public function generate_id() { return '00002-INV-XI-26'; }
    public function generate_id_invoice_jurnal($n) { return 'JV-' . $n; }
    public function get_invoice($id) { global $scenario; return !empty($scenario['invoice_missing']) ? null : (object) array('id' => $id, 'id_penawaran' => 'PN1'); }
    public function get_invoice_non_kons_detail($id) { return array(); }
    public function get_view_jurnal_invoice_non_kons($id) { return array('hasil_jurnal' => '', 'total_debit' => 0, 'total_kredit' => 0); }
    public function get_list_penawaran_non_kons() { return array((object) array('id_penawaran' => 'PN1'), (object) array('id_penawaran' => 'PN2')); }
    public function get_data_spk() { echo json_encode(array('draw' => 1, 'data' => array())); }
}

require dirname(__DIR__) . '/controllers/Invoicing.php';

// Subclass hanya untuk mengganti hasil service/writer pada jalur save; semua hook audit di controller asli.
function iv_make($options, $post = array(), $get = array()) {
    global $scenario, $events, $ci;
    $scenario = $options; $events = array(); $GLOBALS['redirectTarget'] = null;
    $ci = (new ReflectionClass('Invoicing'))->newInstanceWithoutConstructor();
    $ci->module_audit_recorder = new Module_audit_recorder();
    $db = new IvDb();
    $ci->db = $db; $ci->output = new IvOutput();
    foreach (array('consultant', 'accounting', 'accounting_vuca', 'hris') as $prop) {
        $rp = new ReflectionProperty('Invoicing', $prop); $rp->setAccessible(true); $rp->setValue($ci, $db);
    }
    $ci->auth = new IvAuth(); $ci->template = new IvTemplate(); $ci->session = new IvSession();
    $ci->input = new IvInput(); $ci->load = new IvLoad(); $ci->Invoicing_model = new IvModel();
    $ci->input->post = array_merge(array('id' => '00001-INV-XI-26', 'id_invoicing' => '00001-INV-XI-26', 'id_penawaran' => 'PN1', 'tanggal_invoice' => '2026-11-01',
        'keterangan_print' => 'PRIVATE', 'company' => 3, 'alasan_closing' => 'PRIVATE alasan', 'ppn_persen' => 12, 'biaya_kirim' => '0',
        'item' => array(array('nama' => 'PRIVATE', 'qty' => 1, 'harga' => '1,000')), 'password' => 'PRIVATE_PASSWORD'), $post);
    $ci->input->get = array_merge(array('id_penawaran' => 'PN1', 'dpp' => 1000, 'dpp_lain_lain' => 1, 'ppn' => 1, 'pph' => 1, 'total_tagihan_all' => 1, 'no' => 1, 'no_coa' => '1102-01-01'), $get);
    return $ci;
}

if (($argv[1] ?? '') === '--exit') {
    $opts = json_decode(base64_decode($argv[3]), true);
    iv_make($opts);
    register_shutdown_function(function () { global $ci; echo 'CALLS:' . json_encode($ci->db->calls) . "\n"; });
    $ci->{$argv[2]}();
    exit;
}
if (($argv[1] ?? '') === '--subprocess-shutdown') {
    iv_make(array());
    $ci->module_audit_recorder->begin('invoicing', 'index');
    exit;
}

function iv_run_exit($method, $options) {
    global $scenarioCount;
    $scenarioCount++;
    $raw = (string) shell_exec('"' . PHP_BINARY . '" "' . __FILE__ . '" --exit ' . $method . ' ' . base64_encode(json_encode($options)) . ' 2>&1');
    check(substr_count($raw, 'AUDIT:') === 1, $method . ' (exit): tepat satu event, output: ' . $raw);
    $event = json_decode(trim(strtok(substr($raw, strpos($raw, 'AUDIT:') + 6), "\n")), true);
    $calls = json_decode(trim(substr($raw, strpos($raw, 'CALLS:') + 6)), true) ?: array();
    return array($event, $raw, false, (object) array('db' => (object) array('calls' => $calls)));
}

function iv_run($method, $options = array(), $post = array(), $args = array(), $get = array()) {
    global $scenario, $events, $ci, $scenarioCount;
    $scenarioCount++;
    iv_make($options, $post, $get);
    // Temuan di luar cakupan: view_invoicing_non_kons membaca properti invoice null sebelum empty() (notice di produksi, lalu redirect).
    $prevHandler = null;
    if (!empty($options['tolerate_notice'])) { $prevHandler = set_error_handler(function ($sev, $msg) { return true; }); }
    ob_start(); $stopped = false;
    try { call_user_func_array(array($ci, $method), $args); }
    catch (IvStop $s) { $stopped = true; }
    catch (Throwable $e) { ob_end_clean(); if ($prevHandler !== null) { restore_error_handler(); } throw $e; }
    if (!empty($options['tolerate_notice'])) { restore_error_handler(); }
    $output = ob_get_clean();
    $ci->module_audit_recorder->finish_interrupted();
    check(count($events) === 1, $method . ': expected exactly one audit event, got ' . count($events) . ' ' . json_encode(array_column($events, 'message')));
    check($events[0]['context']['module'] === 'invoicing' && $events[0]['context']['methode'] === $method, $method . ': wrong identity');
    check(strpos(json_encode($events[0]['context']), 'PRIVATE') === false, $method . ': PII leaked');
    return array($events[0], $output, $stopped, $ci);
}
function iv_data($e) { return $e['context']['data']; }
function iv_json($o) { return json_decode($o, true); }
function iv_writers($method, $post, $args, $assert, $get = array(), $opts = array()) {
    foreach (array('writer_failure', 'writer_exception') as $w) {
        list($e, $out, , $c) = iv_run($method, array_merge($opts, array($w => true)), $post, $args, $get);
        $assert($e, $out, $w, $c);
    }
}

// ============ batch 1: pengaturan, index, add, view, edit ============
// coa_settings: ditolak (bukan admin), denied permission, error muat (schema)
list($e, $out, $stopped) = iv_run('coa_settings', array('not_admin' => true));
check($e['status'] === 0 && iv_data($e)['outcome'] === 'denied' && iv_data($e)['reason'] === 'permission_denied' && $GLOBALS['show_error_code'] === 403, 'coa_settings: bukan admin ditolak');
list($e, $out, $stopped) = iv_run('coa_settings', array('deny_perm' => true));
check($stopped && $e['status'] === 0 && iv_data($e)['outcome'] === 'denied', 'coa_settings: tanpa Invoicing.Configure ditolak');
list($e) = iv_run('coa_settings');
check(iv_data($e)['invoice_type'] === 'consulting' && in_array(iv_data($e)['outcome'], array('rendered', 'error'), true), 'coa_settings: hasil render/error tercatat');

// save_coa_mapping: hasil service (status) menentukan; tanpa DB nyata -> rejected, dengan reason
list($e, $out) = iv_run('save_coa_mapping');
check($e['status'] === 0 && iv_data($e)['outcome'] === 'rejected' && iv_data($e)['invoice_type'] === 'consulting' && iv_json($out)['status'] === 0, 'save_coa_mapping: gagal -> rejected, respons utuh');
foreach (array('writer_failure', 'writer_exception') as $w) {
    list($e, $out) = iv_run('save_coa_mapping', array($w => true));
    check(iv_json($out)['status'] === 0, "save_coa_mapping: respons utuh saat $w");
}

// index
list($e, $out) = iv_run('index');
check($e['status'] === 1 && iv_data($e)['outcome'] === 'rendered' && strpos($out, 'rendered index') !== false, 'index: rendered');
list($e, $out, $stopped) = iv_run('index', array('deny_perm' => true));
check($stopped && $e['status'] === 0 && iv_data($e)['outcome'] === 'denied' && $out === '', 'index: denied');
iv_writers('index', array(), array(), function ($e, $out, $w) { check(strpos($out, 'rendered index') !== false, "index: utuh saat $w"); });

// add_invoice (konsultasi form): denied tercatat di consulting_form; error muat (schema) -> error
list($e, $out, $stopped) = iv_run('add_invoice', array('deny_perm' => true), array(), array('D1'));
check($stopped && iv_data($e)['outcome'] === 'denied', 'add_invoice: denied');
list($e, $out) = iv_run('add_invoice', array(), array(), array('D1'));
check(iv_data($e)['invoice_type'] === 'consulting' && $e['status'] === 0 && iv_data($e)['outcome'] === 'error' && strpos($out, 'rendered consulting_invoice') !== false, 'add_invoice: schema/sumber tidak tersedia -> error, halaman tetap');

// add_invoice_non_konsultasi
list($e, $out) = iv_run('add_invoice_non_konsultasi', array(), array(), array('PN1'));
check($e['status'] === 1 && iv_data($e)['id_penawaran'] === 'PN1' && iv_data($e)['invoice_type'] === 'non_kons' && strpos($out, 'rendered add_invoice_non_konsultasi') !== false, 'add_invoice_non_konsultasi: rendered');
list($e, $out) = iv_run('add_invoice_non_konsultasi', array('penawaran_missing' => true), array(), array('PN1'));
check($e['status'] === 0 && iv_data($e)['outcome'] === 'not_found' && strpos($out, 'rendered add_invoice_non_konsultasi') !== false, 'add_invoice_non_konsultasi: not_found');
list($e, , $stopped) = iv_run('add_invoice_non_konsultasi', array('deny_perm' => true), array(), array('PN1'));
check($stopped && iv_data($e)['outcome'] === 'denied', 'add_invoice_non_konsultasi: denied');

// add_invoice_vuca: detail ada tetapi actual belum tersedia -> error halaman (RuntimeException ditangkap controller)
list($e, $out) = iv_run('add_invoice_vuca', array(), array(), array('D1'));
check(iv_data($e)['invoice_type'] === 'vuca' && strpos($out, 'rendered add_invoice_vuca') !== false && in_array(iv_data($e)['outcome'], array('rendered', 'error'), true), 'add_invoice_vuca: tercatat, halaman tetap');
list($e, , $stopped) = iv_run('add_invoice_vuca', array('deny_perm' => true), array(), array('D1'));
check($stopped && iv_data($e)['outcome'] === 'denied', 'add_invoice_vuca: denied');

// view/edit legacy + vuca: rendered / not_found / denied
foreach (array(
    'view_invoicing' => array('rendered view_invoice', 'Melihat invoice'),
    'view_invoicing_vuca' => array('rendered view_invoice_vuca', 'Melihat invoice Vuca'),
    'edit_invoicing' => array('rendered edit_invoice', 'Membuka form edit invoice'),
    'edit_invoicing_vuca' => array('rendered edit_invoice_vuca', 'Membuka form edit invoice Vuca'),
) as $m => $cfg) {
    list($e, $out) = iv_run($m, array(), array(), array('00001-INV-XI-26'));
    check($e['status'] === 1 && iv_data($e)['outcome'] === 'rendered' && iv_data($e)['id_invoicing'] === '00001-INV-XI-26' && strpos($out, $cfg[0]) !== false, "$m: rendered");
    list($e, , $stopped) = iv_run($m, array('deny_perm' => true), array(), array('00001-INV-XI-26'));
    check($stopped && $e['status'] === 0 && iv_data($e)['outcome'] === 'denied', "$m: denied");
    iv_writers($m, array(), array('00001-INV-XI-26'), function ($e, $out, $w) use ($m, $cfg) { check(strpos($out, $cfg[0]) !== false, "$m: halaman utuh saat $w"); });
}
// jalur invoice konsultasi baru lewat consulting_form (view/edit/print)
foreach (array('view_invoicing', 'edit_invoicing', 'print_invoicing') as $m) {
    list($e, $out) = iv_run($m, array('consulting' => true, 'invoice_extra' => array('consulting_snapshot' => '{}')), array(), array('00001-INV-XI-26'));
    check(iv_data($e)['invoice_type'] === 'consulting' && $e['status'] === 0 && iv_data($e)['outcome'] === 'error', "$m: invoice konsultasi, snapshot tidak lengkap -> error tercatat, bukan sukses");
}

// ============ batch 2: print, save, update ============
foreach (array(
    'print_invoicing' => array('view print_invoice', array('00001-INV-XI-26')),
    'print_invoice_non_kons' => array('view print_invoice_non_konsultasi', array('00001-INV-XI-26')),
    'print_invoicing_vuca' => array('view print_invoice_vuca', array('00001-INV-XI-26')),
    'print_kwitansi' => array('view print_kwitansi', array('00001-INV-XI-26')),
    'print_kwitansi_vuca' => array('view print_kwitansi_vuca', array('00001-INV-XI-26')),
) as $m => $cfg) {
    list($e, $out) = iv_run($m, array(), array(), $cfg[1]);
    check($e['status'] === 1 && iv_data($e)['outcome'] === 'rendered' && strpos($out, $cfg[0]) !== false, "$m: rendered");
    check(!in_array('insert', $ci->db->calls, true) && !in_array('update', $ci->db->calls, true), "$m: tidak menulis");
    list($e, , $stopped) = iv_run($m, array('deny_perm' => true), array(), $cfg[1]);
    check($stopped && iv_data($e)['outcome'] === 'denied', "$m: denied");
    iv_writers($m, array(), $cfg[1], function ($e, $out, $w) use ($m, $cfg) { check(strpos($out, $cfg[0]) !== false, "$m: cetak utuh saat $w"); });
}

// save_invoice konsultasi: service asli; tanpa skema konsultasi -> rejected
list($e, $out) = iv_run('save_invoice');
check($e['status'] === 0 && iv_data($e)['outcome'] === 'rejected' && iv_data($e)['invoice_type'] === 'consulting' && iv_json($out)['status'] === 0, 'save_invoice: ditolak service -> rejected');
foreach (array('writer_failure', 'writer_exception') as $w) {
    list($e, $out) = iv_run('save_invoice', array($w => true));
    check(iv_json($out)['status'] === 0, "save_invoice: respons utuh saat $w");
}

// save_invoice_vuca / update_invoice_vuca lewat write_vuca (recorder sudah ada di HEAD; diverifikasi)
foreach (array('save_invoice_vuca', 'update_invoice_vuca') as $m) {
    list($e, $out, , $c) = iv_run($m);
    check($e['status'] === 0 && iv_data($e)['invoice_type'] === 'vuca' && iv_data($e)['outcome'] === 'rejected' && iv_json($out)['status'] === 0, "$m: writer menolak -> rejected, respons utuh");
    list($e, , $stopped) = iv_run($m, array('deny_perm' => true));
    check($stopped && $e['status'] === 0 && iv_data($e)['outcome'] === 'denied' && iv_data($e)['reason'] === 'permission_denied', "$m: denied tercatat sebelum restrict");
    foreach (array('writer_failure', 'writer_exception') as $w) {
        list($e, $out) = iv_run($m, array($w => true));
        check(iv_json($out)['status'] === 0 && $e['status'] === 0, "$m: bisnis utuh saat $w");
    }
}

// update_invoice: ID tidak valid; invoice lama via boundary (legacy)
list($e, $out) = iv_run('update_invoice', array(), array('id_invoicing' => array('x')));
check($e['status'] === 0 && iv_data($e)['reason'] === 'invalid_id' && iv_json($out)['status'] === 0, 'update_invoice: ID tidak valid');

// legacy_update_invoice dipanggil langsung lewat refleksi agar semua cabang commit terlihat
function iv_call_private($method, $options, $post = array()) {
    global $scenario, $events, $ci, $scenarioCount;
    $scenarioCount++;
    iv_make($options, $post);
    $rm = new ReflectionMethod('Invoicing', $method); $rm->setAccessible(true);
    ob_start(); $stopped = false;
    try { $rm->invoke($ci); } catch (IvStop $s) { $stopped = true; } catch (Throwable $e) { ob_end_clean(); throw $e; }
    $out = ob_get_clean();
    return array($out, $stopped, $ci);
}
function iv_private_event($method, $options, $post = array()) {
    global $events, $ci;
    iv_make($options, $post);
    $ci->module_audit_recorder->begin('invoicing', $method, array('data' => array()),
        array('id', 'id_invoicing', 'id_penawaran', 'invoice_type', 'row_count', 'filter_status', 'reason', 'outcome'));
    $rm = new ReflectionMethod('Invoicing', 'legacy_' . $method); $rm->setAccessible(true);
    ob_start(); $stopped = false;
    try { $rm->invoke($ci); } catch (IvStop $s) { $stopped = true; } catch (Throwable $e) { ob_end_clean(); throw $e; }
    $out = ob_get_clean();
    $ci->module_audit_recorder->finish_interrupted();
    check(count($events) === 1, "legacy_$method: tepat satu event, dapat " . count($events));
    check(strpos(json_encode($events[0]['context']), 'PRIVATE') === false, "legacy_$method: PII leaked");
    $GLOBALS['scenarioCount']++;
    return array($events[0], $out, $stopped, $ci);
}
list($e, $out, , $c) = iv_private_event('update_invoice', array(), array('nomor_invoice' => 'INV-1', 'no_po' => 'PO', 'nomor_faktur' => 'F', 'tgl_jurnal_1' => '2026-11-01',
    'coa_jurnal_1' => '1102-01-01', 'id_company_1' => 3, 'nm_company_1' => 'PRIVATE', 'nm_coa_1' => 'Piutang', 'debit_1' => 1, 'kredit_1' => 0));
check($e['status'] === 1 && iv_data($e)['outcome'] === 'committed' && iv_data($e)['invoice_type'] === 'legacy' && iv_json($out)['status'] === 1 && in_array('trans_commit', $e['calls_at_write'], true), 'legacy_update_invoice: committed sesudah commit');
$ju = array('nomor_invoice' => 'INV-1', 'no_po' => 'PO', 'nomor_faktur' => 'F', 'tgl_jurnal_1' => '2026-11-01', 'coa_jurnal_1' => '1102-01-01', 'id_company_1' => 3, 'nm_company_1' => 'PRIVATE', 'nm_coa_1' => 'Piutang', 'debit_1' => 1, 'kredit_1' => 0);
foreach (array('delete_fail' => 'delete_failed', 'update_fail' => 'update_failed', 'batch_fail' => 'insert_failed', 'status_fail' => 'transaction_failed', 'commit_fail' => 'commit_failed') as $opt => $reason) {
    list($e, $out, , $c) = iv_private_event('update_invoice', array($opt => true), $ju);
    check($e['status'] === 0 && iv_data($e)['reason'] === $reason && iv_json($out)['status'] === 0 && in_array('trans_rollback', $c->db->calls, true), "legacy_update_invoice: $opt -> $reason, rollback");
}

// ============ batch 2-3: save_keterangan_print (7 varian) ============
$ketUpdate = array(
    'save_keterangan_print' => array('legacy', true), 'save_keterangan_print_non_kons' => array('non_kons', true),
    'save_keterangan_print_kwitansi' => array('legacy', true), 'save_keterangan_print_vuca' => array('vuca', false),
    'save_keterangan_print_kwitansi_vuca' => array('vuca', false));
foreach ($ketUpdate as $m => $cfg) {
    list($typ, $isTry) = $cfg;
    list($e, $out, , $c) = iv_run($m);
    $j = iv_json($out);
    check($e['status'] === 1 && iv_data($e)['outcome'] === 'saved' && iv_data($e)['invoice_type'] === $typ && iv_data($e)['id_invoicing'] === '00001-INV-XI-26' && $j['status'] == 1 && in_array('trans_commit', $e['calls_at_write'], true), "$m: saved sesudah commit");
    list($e, $out, , $c) = iv_run($m, array('commit_fail' => true));
    check($e['status'] === 0 && iv_data($e)['reason'] === 'commit_failed' && iv_json($out)['status'] == 0, "$m: commit gagal TIDAK boleh sukses");
    if ($isTry) {
        list($e, $out, , $c) = iv_run($m, array('update_fail' => true));
        check($e['status'] === 0 && iv_data($e)['reason'] === 'update_failed' && iv_json($out)['status'] == 0 && !in_array('trans_commit', $c->db->calls, true), "$m: update gagal tidak dicommit");
    } else {
        list($e, $raw, , $c) = iv_run_exit($m, array('update_fail' => true));
        check($e['status'] === 0 && iv_data($e)['reason'] === 'update_failed' && iv_data($e)['outcome'] === 'failed' && in_array('trans_rollback', $c->db->calls, true) && strpos($raw, 'SELECT 1') !== false, "$m: update gagal tercatat, rollback, output lama utuh");
        list($e, $out, , $c) = iv_run($m, array('status_fail' => true));
        check($e['status'] === 0 && iv_data($e)['reason'] === 'transaction_failed' && !in_array('trans_commit', $c->db->calls, true), "$m: trans_status false");
    }
    foreach (array('writer_failure', 'writer_exception') as $w) {
        list($e, $out, , $c) = iv_run($m, array($w => true));
        check($e['status'] === 1 && iv_json($out)['status'] == 1 && in_array('trans_commit', $c->db->calls, true), "$m: bisnis utuh saat $w");
    }
}

// ============ batch 3: data, unduhan, non konsultasi ============
list($e, $out) = iv_run('get_data_spk');
check($e['status'] === 1 && iv_data($e)['outcome'] === 'rendered' && iv_json($out)['draw'] === 1, 'get_data_spk: rendered');
list($e, $out, , $c) = iv_run('download_excel', array(), array(), array(), array('filter_status' => 'invoiced'));
check($e['status'] === 1 && iv_data($e)['outcome'] === 'generated' && iv_data($e)['filter_status'] === 'invoiced' && strpos($out, 'view download_excel') !== false, 'download_excel: generated');
list($e) = iv_run('download_excel', array(), array(), array(), array('filter_status' => 'x; DROP'));
check(!isset(iv_data($e)['filter_status']), 'download_excel: filter tidak wajar tidak masuk metadata');
iv_writers('download_excel', array(), array(), function ($e, $out, $w) { check(strpos($out, 'view download_excel') !== false, "download_excel: file utuh saat $w"); });
list($e, $out) = iv_run('get_data_quotation_non_konsultasi');
check($e['status'] === 1 && iv_data($e)['outcome'] === 'rendered' && iv_data($e)['invoice_type'] === 'non_kons' && is_array(iv_json($out)), 'get_data_quotation_non_konsultasi: rendered');

list($e, $out) = iv_run('create_invoice_non_konsultasi', array(), array(), array('PN1'));
check($e['status'] === 1 && iv_data($e)['outcome'] === 'rendered' && strpos($out, 'rendered add_invoice_non_konsultasi') !== false, 'create_invoice_non_konsultasi: rendered');
list($e) = iv_run('create_invoice_non_konsultasi', array('penawaran_missing' => true), array(), array('PN1'));
check($e['status'] === 0 && iv_data($e)['outcome'] === 'not_found' && $GLOBALS['redirectTarget'] === 'invoicing', 'create_invoice_non_konsultasi: not_found + redirect');
list($e, , $stopped) = iv_run('create_invoice_non_konsultasi', array('deny_perm' => true), array(), array('PN1'));
check($stopped && iv_data($e)['outcome'] === 'denied', 'create_invoice_non_konsultasi: denied');

list($e, $out) = iv_run('edit_invoice_non_kons', array(), array(), array('00001-INV-XI-26'));
check($e['status'] === 1 && iv_data($e)['outcome'] === 'rendered' && strpos($out, 'rendered edit_invoice_non_konsultasi') !== false, 'edit_invoice_non_kons: rendered');
list($e) = iv_run('edit_invoice_non_kons', array('invoice_missing' => true), array(), array('x'));
check($e['status'] === 0 && iv_data($e)['outcome'] === 'not_found' && $GLOBALS['redirectTarget'] === 'invoicing', 'edit_invoice_non_kons: invoice tidak ada');
list($e) = iv_run('edit_invoice_non_kons', array('penawaran_missing' => true), array(), array('x'));
check($e['status'] === 0 && iv_data($e)['outcome'] === 'not_found', 'edit_invoice_non_kons: penawaran tidak ada');
list($e, , $stopped) = iv_run('edit_invoice_non_kons', array('deny_perm' => true), array(), array('x'));
check($stopped && iv_data($e)['outcome'] === 'denied', 'edit_invoice_non_kons: denied');

list($e, $out) = iv_run('view_invoicing_non_kons', array(), array(), array('00001-INV-XI-26'));
check($e['status'] === 1 && iv_data($e)['outcome'] === 'rendered' && strpos($out, 'rendered view_invoice_non_konsultasi') !== false, 'view_invoicing_non_kons: rendered');
list($e) = iv_run('view_invoicing_non_kons', array('invoice_missing' => true, 'tolerate_notice' => true), array(), array('x'));
check($e['status'] === 0 && iv_data($e)['outcome'] === 'not_found' && $GLOBALS['redirectTarget'] === 'invoicing', 'view_invoicing_non_kons: not_found + redirect');

// ============ batch 4 ============
list($e, $out) = iv_run('list_penawaran_non_kons');
check($e['status'] === 1 && iv_data($e)['row_count'] === 2 && strpos($out, 'rendered list_penawaran_non_kons') !== false, 'list_penawaran_non_kons: rendered 2 baris');
list($e, , $stopped) = iv_run('list_penawaran_non_kons', array('deny_perm' => true));
check($stopped && iv_data($e)['outcome'] === 'denied', 'list_penawaran_non_kons: denied');

list($e, $out) = iv_run('hitung_jurnal');
check($e['status'] === 1 && iv_data($e)['outcome'] === 'rendered' && iv_json($out)['hasil_jurnal'] !== null, 'hitung_jurnal: rendered');
list($e, , $stopped) = iv_run('hitung_jurnal', array('deny_perm' => true));
check($stopped && iv_data($e)['outcome'] === 'denied', 'hitung_jurnal: denied');

list($e, $out) = iv_run('change_jurnal_invoice');
check($e['status'] === 1 && iv_data($e)['outcome'] === 'rendered' && iv_json($out)['hasil'] !== null, 'change_jurnal_invoice: rendered');
list($e, $out) = iv_run('change_jurnal_invoice', array('coa_missing' => true));
check($e['status'] === 0 && iv_data($e)['outcome'] === 'not_found' && isset(iv_json($out)['msg']), 'change_jurnal_invoice: COA tidak ditemukan');

list($e, $out) = iv_run('view_invoice_non_kons', array(), array(), array('00001-INV-XI-26'));
check($e['status'] === 1 && iv_data($e)['outcome'] === 'rendered', 'view_invoice_non_kons: rendered');
list($e) = iv_run('view_invoice_non_kons', array('invoice_missing' => true), array(), array('x'));
check($e['status'] === 0 && iv_data($e)['outcome'] === 'not_found' && $GLOBALS['redirectTarget'] === 'invoicing', 'view_invoice_non_kons: not_found');
list($e) = iv_run('view_invoice_non_kons', array('penawaran_missing' => true), array(), array('x'));
check($e['status'] === 0 && iv_data($e)['outcome'] === 'not_found', 'view_invoice_non_kons: penawaran tidak ada');
list($e, , $stopped) = iv_run('view_invoice_non_kons', array('deny_perm' => true), array(), array('x'));
check($stopped && iv_data($e)['outcome'] === 'denied', 'view_invoice_non_kons: denied');

// save_invoice_non_konsultasi (legacy langsung)
list($e, $out, , $c) = iv_private_event('save_invoice_non_konsultasi', array());
check($e['status'] === 1 && iv_data($e)['outcome'] === 'committed' && iv_data($e)['invoice_type'] === 'non_kons' && iv_data($e)['id_invoicing'] === '00002-INV-XI-26' && iv_json($out)['status'] === 1 && in_array('trans_commit', $e['calls_at_write'], true), 'legacy_save_non_kons: committed sesudah commit');
list($e, $out, , $c) = iv_private_event('save_invoice_non_konsultasi', array('commit_fail' => true));
check($e['status'] === 0 && iv_data($e)['reason'] === 'commit_failed' && iv_json($out)['status'] === 0 && in_array('trans_rollback', $c->db->calls, true), 'legacy_save_non_kons: commit gagal TIDAK boleh sukses');
list($e, $out, , $c) = iv_private_event('save_invoice_non_konsultasi', array('batch_fail' => true));
check($e['status'] === 0 && iv_data($e)['outcome'] === 'rejected' && iv_json($out)['status'] === 0 && !in_array('trans_commit', $c->db->calls, true), 'legacy_save_non_kons: insert batch gagal');
list($e, $out, , $c) = iv_private_event('save_invoice_non_konsultasi', array(), array('item' => array(array('nama' => 'PRIVATE', 'qty' => 1, 'harga' => '99,999,999'))));
check($e['status'] === 0 && iv_data($e)['reason'] === 'over_outstanding' && in_array('trans_rollback', $c->db->calls, true) && !$c->db->writes, 'legacy_save_non_kons: melebihi outstanding, tidak menulis, transaksi ditutup');

// update_invoice_non_konsultasi (legacy langsung)
list($e, $out, , $c) = iv_private_event('update_invoice_non_konsultasi', array(), array('nomor_invoice' => 'INV-1'));
check($e['status'] === 1 && iv_data($e)['outcome'] === 'committed' && iv_json($out)['status'] === 1 && in_array('trans_commit', $e['calls_at_write'], true), 'legacy_update_non_kons: committed sesudah commit');
foreach (array('commit_fail' => 'commit_failed', 'status_fail' => 'transaction_failed') as $opt => $reason) {
    list($e, $out, , $c) = iv_private_event('update_invoice_non_konsultasi', array($opt => true), array('nomor_invoice' => 'INV-1'));
    check($e['status'] === 0 && iv_data($e)['reason'] === $reason && iv_json($out)['status'] === 0, "legacy_update_non_kons: $opt -> $reason");
}
list($e, $out) = iv_private_event('update_invoice_non_konsultasi', array('delete_fail' => true), array('nomor_invoice' => 'INV-1'));
check($e['status'] === 0 && iv_data($e)['outcome'] === 'rejected', 'legacy_update_non_kons: hapus data lama gagal');

// wrapper publik save/update non konsultasi (lewat legacy_consulting_boundary + lock)
foreach (array('save_invoice_non_konsultasi' => 'Menyimpan invoice non konsultasi', 'update_invoice_non_konsultasi' => 'Menyimpan perubahan invoice non konsultasi') as $m => $msg) {
    list($e, $out, , $c) = iv_run($m, array(), array('nomor_invoice' => 'INV-1'));
    check($e['status'] === 1 && iv_data($e)['outcome'] === 'committed' && iv_json($out)['status'] === 1 && in_array('trans_commit', $e['calls_at_write'], true), "$m: lewat boundary, committed sesudah commit");
    // lock tidak didapat -> boundary menelan, tidak menjalankan endpoint -> satu event rejected (bukan interrupted)
    list($e, $out, , $c) = iv_run($m, array('lock_fail' => true), array('nomor_invoice' => 'INV-1'));
    check($e['status'] === 0 && iv_data($e)['outcome'] === 'rejected' && iv_data($e)['reason'] === 'rejected' && iv_json($out)['status'] === 0 && !$c->db->writes, "$m: lock gagal -> rejected, tidak menulis");
}
// update_invoice lewat boundary (invoice lama): jalur sukses dan lock gagal
list($e, $out, , $c) = iv_run('update_invoice', array(), array('nomor_invoice' => 'INV-1', 'no_po' => 'PO', 'nomor_faktur' => 'F', 'tgl_jurnal_1' => '2026-11-01', 'coa_jurnal_1' => '1102-01-01', 'id_company_1' => 3, 'nm_company_1' => 'PRIVATE', 'nm_coa_1' => 'Piutang', 'debit_1' => 1, 'kredit_1' => 0));
check($e['status'] === 1 && iv_data($e)['outcome'] === 'committed' && iv_data($e)['invoice_type'] === 'legacy', 'update_invoice: lewat boundary, committed');
list($e, $out, , $c) = iv_run('update_invoice', array('lock_fail' => true));
check($e['status'] === 0 && iv_data($e)['outcome'] === 'rejected' && !$c->db->writes, 'update_invoice: lock gagal -> rejected');

// save_close_invoice
list($e, $out, , $c) = iv_run('save_close_invoice');
check($e['status'] === 1 && iv_data($e)['outcome'] === 'closed' && iv_data($e)['id_invoicing'] === '00001-INV-XI-26' && $c->output->status === 200 && in_array('trans_commit', $e['calls_at_write'], true), 'save_close_invoice: closed sesudah commit');
list($e, $out, , $c) = iv_run('save_close_invoice', array(), array('id_invoicing' => ''));
check($e['status'] === 0 && iv_data($e)['reason'] === 'invalid_id' && $c->output->status === 400, 'save_close_invoice: id kosong');
list($e, $out, , $c) = iv_run('save_close_invoice', array(), array('alasan_closing' => ''));
check($e['status'] === 0 && iv_data($e)['reason'] === 'reason_required' && $c->output->status === 400, 'save_close_invoice: alasan wajib');
list($e, $out, , $c) = iv_run('save_close_invoice', array('saldo' => 100));
check($e['status'] === 0 && iv_data($e)['reason'] === 'not_settled' && $c->output->status === 500 && in_array('trans_rollback', $c->db->calls, true), 'save_close_invoice: belum lunas');
list($e, $out, , $c) = iv_run('save_close_invoice', array('affected' => 0));
check($e['status'] === 0 && iv_data($e)['reason'] === 'no_rows_updated' && $c->output->status === 500, 'save_close_invoice: tidak ada baris');
list($e, $out, , $c) = iv_run('save_close_invoice', array('commit_fail' => true));
check($e['status'] === 0 && iv_data($e)['reason'] === 'commit_failed' && $c->output->status === 500, 'save_close_invoice: commit gagal TIDAK boleh sukses');
list($e, , $stopped) = iv_run('save_close_invoice', array('deny_perm' => true));
check($stopped && iv_data($e)['outcome'] === 'denied', 'save_close_invoice: denied');
foreach (array('writer_failure', 'writer_exception') as $w) {
    list($e, $out, , $c) = iv_run('save_close_invoice', array($w => true));
    check($e['status'] === 1 && $c->output->status === 200, "save_close_invoice: bisnis utuh saat $w");
}

// close_penawaran_non_kons
list($e, $out, , $c) = iv_run('close_penawaran_non_kons');
check($e['status'] === 1 && iv_data($e)['outcome'] === 'closed' && iv_data($e)['id_penawaran'] === 'PN1' && $c->output->status === 200 && in_array('trans_commit', $e['calls_at_write'], true), 'close_penawaran: closed sesudah commit');
list($e, $out, , $c) = iv_run('close_penawaran_non_kons', array(), array('id_penawaran' => ''));
check($e['status'] === 0 && iv_data($e)['reason'] === 'invalid_id' && $c->output->status === 400, 'close_penawaran: id kosong');
list($e, $out, , $c) = iv_run('close_penawaran_non_kons', array('affected' => 0));
check($e['status'] === 0 && iv_data($e)['reason'] === 'no_rows_updated' && $c->output->status === 500, 'close_penawaran: tidak ada baris');
list($e, $out, , $c) = iv_run('close_penawaran_non_kons', array('commit_fail' => true));
check($e['status'] === 0 && iv_data($e)['reason'] === 'commit_failed' && $c->output->status === 500, 'close_penawaran: commit gagal TIDAK boleh sukses');
list($e, , $stopped) = iv_run('close_penawaran_non_kons', array('deny_perm' => true));
check($stopped && iv_data($e)['outcome'] === 'denied', 'close_penawaran: denied');

// ---- seluruh endpoint publik tercakup (drift terhadap kode)
$src = file_get_contents(dirname(__DIR__) . '/controllers/Invoicing.php');
preg_match_all('/\n    public function (\w+)\(/', $src, $m);
$public = array_values(array_filter($m[1], function ($n) { return $n[0] !== '_' && $n !== '__construct'; }));
check(count($public) === 38, 'jumlah endpoint publik 38, dapat ' . count($public));
foreach ($public as $name) {
    preg_match('/\n    public function ' . $name . '\(.*?(?=\n    (?:public|private) function )/s', $src, $mm);
    $body = $mm[0] ?? '';
    check(strpos($body, 'beginAudit(') !== false || in_array($name, array('save_invoice_vuca', 'update_invoice_vuca'), true), "$name: tidak memanggil beginAudit");
}

// ---- subprocess: exit nyata -> fallback interrupted
$GLOBALS['scenarioCount']++;
$sub = shell_exec('"' . PHP_BINARY . '" "' . __FILE__ . '" --subprocess-shutdown 2>&1');
check(substr_count((string) $sub, 'AUDIT:') === 1, 'subprocess: satu event interrupted');
$ev = json_decode(trim(substr($sub, strpos($sub, 'AUDIT:') + 6)), true);
check($ev['status'] === 0 && $ev['context']['data']['outcome'] === 'interrupted', 'subprocess: interrupted');

echo 'PASS Invoicing batch 1-4: ' . $scenarioCount . " skenario fixture/subprocess, controller+recorder asli\n";
