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

if (!function_exists('base_url')) { function base_url($u = '') { return 'http://localhost/' . $u; } }
if (!function_exists('site_url')) { function site_url($u = '') { return 'http://localhost/' . $u; } }

// Baris generik: array/objek yang mengembalikan 0 untuk key yang tidak ada.
class B10Row extends ArrayObject {
    public function offsetExists($k) { return true; }
    public function offsetGet($k) { return parent::offsetExists($k) ? parent::offsetGet($k) : 0; }
    public function __get($k) { return 0; }
}

class B10Result {
    public function result_array() { global $scenario; return !empty($scenario['empty_result']) ? array() : array(new B10Row(array('id' => 1, 'status' => 0, 'sts_finance' => '1'))); }
    public function result() { global $scenario; return !empty($scenario['empty_result']) ? array() : array(new B10Row(array('id' => 1, 'tgl_jurnal' => '2026-11-09', 'coa' => '1304-01-01'))); }
    public function row() { global $scenario; return !empty($scenario['missing_record']) ? null : new B10Row(array('id' => 1, 'no_doc' => 'EXP-2026-001', 'status' => '1', 'id_kasbon' => '')); }
}

class B10Db {
    public function __call($n, $a) { return $this; }
    public function count_all_results($t = '', $reset = true) { return 0; }
    public function get($t = '') { return new B10Result(); }
    public function get_where($t = '', $w = array()) { return new B10Result(); }
    public function query($sql) { return new B10Result(); }
    public function update($t, $d = array(), $w = array()) { return true; }
    public function trans_begin() { $GLOBALS['b10_rollbacks'] = 0; }
    public function trans_status() { global $scenario; return empty($scenario['trans_status_failure']); }
    public function trans_commit() { global $scenario; return empty($scenario['trans_commit_failure']); }
    public function trans_rollback() { $GLOBALS['b10_rollbacks']++; }
    public function escape($v) { return "'" . addslashes($v) . "'"; }
    public function error() { return array(); }
}

class B10Auth {
    public function user_id() { return 10; }
    public function user_name() { return 'budi_santoso'; }
    public function is_admin() { return false; }
}
class B10Output {
    public function set_content_type($t) { return $this; }
    public function set_status_header($c) { return $this; }
    public function set_output($o) { echo $o; return $this; }
}
class B10Template {
    public function set($k, $v = null) {}
    public function title($t) {}
    public function page_icon($i) {}
    public function render($v) { echo "<!-- rendered {$v} -->"; }
}
class B10Input {
    public $values = array();
    public $query = array();
    public function post($key = null) { return $key === null ? $this->values : (isset($this->values[$key]) ? $this->values[$key] : null); }
    public function get($key = null) { return isset($this->query[$key]) ? $this->query[$key] : (isset($this->values[$key]) ? $this->values[$key] : null); }
}
class B10Load { public function library($l) {} public function helper($h) {} public function model($m) {} }

function expense_b10_run($method, $options = array(), $post = array(), $args = array(), $query = array()) {
    global $scenario, $events, $ci;
    $scenario = $options;
    $events = array();
    $GLOBALS['scenarioCount']++;

    $ci = (new ReflectionClass('Expense'))->newInstanceWithoutConstructor();
    $ci->module_audit_recorder = new Module_audit_recorder();
    $ci->db = new B10Db();
    $ci->input = new B10Input();
    $ci->auth = new B10Auth();
    $ci->output = new B10Output();
    $ci->template = new B10Template();
    $ci->load = new B10Load();
    $ps = new ReflectionProperty('Expense', 'status');
    $ps->setAccessible(true);
    $ps->setValue($ci, array());
    $ci->input->query = $query;
    $ci->input->values = array_merge(array('draw' => 1, 'length' => 10, 'start' => 0, 'search' => array('value' => ''),
        'password' => 'PRIVATE_PASSWORD', 'nama' => 'John Doe'), $post);

    ob_start();
    try {
        call_user_func_array(array($ci, $method), $args);
    } catch (Throwable $e) {
        ob_end_clean();
        throw $e;
    }
    $output = ob_get_clean();
    $ci->module_audit_recorder->finish_interrupted();

    check(count($events) === 1, $method . ': expected exactly one audit event, got ' . count($events));
    check($events[0]['context']['module'] === 'expense', $method . ': wrong module');
    check($events[0]['context']['methode'] === $method, $method . ': wrong method');
    $flat = json_encode($events[0]['context']);
    check(strpos($flat, 'PRIVATE_PASSWORD') === false, $method . ': password leaked');
    check(strpos($flat, 'John Doe') === false, $method . ': name leaked');
    return array($events[0], $output);
}

if (in_array('--subprocess-shutdown', $argv ?? array(), true)) {
    list($e) = expense_b10_run('verifikasi_pengembalian_reject', array('writer_exception' => true), array('id' => 20, 'reason' => 'Bukti tidak sesuai'));
    exit($e['status'] === 1 ? 0 : 1);
}

function b10_writers($method, $post, $assert, $args = array()) {
    foreach (array('writer_failure', 'writer_exception') as $writer) {
        list($e, $out) = expense_b10_run($method, array($writer => true), $post, $args);
        $assert($e, $out, $writer);
    }
}

// ---------------- verifikasi_pengembalian_reject (batch 10, baru) ----------------
$rej = array('id' => 20, 'reason' => 'Bukti tidak sesuai');

list($e, $out) = expense_b10_run('verifikasi_pengembalian_reject', array(), $rej);
$d = $e['context']['data'];
check($e['status'] === 1 && $d['outcome'] === 'rejected' && $d['id'] == 20, 'reject: rejected + id');
check(json_decode($out, true)['save'] === true, 'reject: response save true');
check(strpos(json_encode($e['context']), 'Bukti tidak sesuai') === false, 'reject: alasan penolakan tidak masuk audit');
b10_writers('verifikasi_pengembalian_reject', $rej, function ($e, $out, $w) {
    check($e['status'] === 1 && json_decode($out, true)['save'] === true, "reject: intact on $w");
});

list($e, $out) = expense_b10_run('verifikasi_pengembalian_reject', array(), array('id' => '', 'reason' => 'x'));
check($e['status'] === 0 && $e['context']['data']['outcome'] === 'validation_failed' && $e['context']['data']['reason'] === 'id_required', 'reject: id kosong');
check(json_decode($out, true)['save'] === false, 'reject: id kosong response');

list($e, $out) = expense_b10_run('verifikasi_pengembalian_reject', array(), array('id' => 20, 'reason' => '   '));
check($e['status'] === 0 && $e['context']['data']['reason'] === 'reason_required', 'reject: alasan kosong');

list($e, $out) = expense_b10_run('verifikasi_pengembalian_reject', array('missing_record' => true), $rej);
check($e['status'] === 0 && $e['context']['data']['outcome'] === 'not_found', 'reject: not_found');

foreach (array('trans_commit_failure', 'trans_status_failure') as $fail) {
    list($e, $out) = expense_b10_run('verifikasi_pengembalian_reject', array($fail => true), $rej);
    check($e['status'] === 0 && $e['context']['data']['outcome'] === 'transaction_failed', "reject: $fail -> transaction_failed");
    check(json_decode($out, true)['save'] === false, "reject: $fail response save false");
    check($GLOBALS['b10_rollbacks'] === 1, "reject: $fail rollback dipanggil");
}

// ------------- verifikasi_pengembalian_approve (sudah ada recorder; verifikasi cabang validasi) -------------
list($e, $out) = expense_b10_run('verifikasi_pengembalian_approve', array(), array('id' => 'abc'));
check($e['status'] === 0 && $e['context']['data']['outcome'] === 'verification_failed', 'approve: id tidak valid');
check(json_decode($out, true)['save'] === false, 'approve: id tidak valid response');
list($e, $out) = expense_b10_run('verifikasi_pengembalian_approve', array(), array('id' => 20, 'chk_bukti' => '1', 'chk_mutasi' => '1'));
check($e['status'] === 0 && $e['context']['data']['outcome'] === 'verification_failed', 'approve: checklist tidak lengkap');
check(strpos(json_decode($out, true)['message'], 'checklist') !== false, 'approve: pesan checklist dipertahankan');
b10_writers('verifikasi_pengembalian_approve', array('id' => 'abc'), function ($e, $out, $w) {
    check($e['status'] === 0 && json_decode($out, true)['save'] === false, "approve: intact on $w");
});

// ------------- save_pengembalian_dana (recorder ada di helper privat; verifikasi cabang validasi) -------------
list($e, $out) = expense_b10_run('save_pengembalian_dana', array(), array('id_expense' => ''));
check($e['status'] === 0 && $e['context']['data']['outcome'] === 'validation_failed', 'save_pengembalian_dana: id kosong');
list($e, $out) = expense_b10_run('save_pengembalian_dana', array('missing_record' => true), array('id_expense' => 20));
check($e['status'] === 0 && $e['context']['data']['outcome'] === 'validation_failed' && strpos($out, 'tidak ditemukan') !== false, 'save_pengembalian_dana: expense tidak ada');
list($e, $out) = expense_b10_run('save_pengembalian_dana', array(), array('id_expense' => 20));
check($e['status'] === 0 && $e['context']['data']['outcome'] === 'validation_failed' && strpos($out, 'Bank Tujuan') !== false, 'save_pengembalian_dana: bank belum dipilih');
b10_writers('save_pengembalian_dana', array('id_expense' => ''), function ($e, $out, $w) {
    check($e['status'] === 0 && json_decode($out, true)['save'] === false, "save_pengembalian_dana: intact on $w");
});

$cmd = escapeshellcmd(PHP_BINARY) . ' ' . escapeshellarg(__FILE__) . ' --subprocess-shutdown 2>&1';
exec($cmd, $subOutput, $subExitCode);
check($subExitCode === 0, 'Subprocess shutdown exit code 0');

echo "PASS Expense batch 10: {$scenarioCount} fixture scenarios + 1 shutdown subprocess" . PHP_EOL;
