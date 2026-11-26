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
class B9Row extends ArrayObject {
    public function offsetExists($k) { return true; }
    public function offsetGet($k) { return parent::offsetExists($k) ? parent::offsetGet($k) : 0; }
    public function __get($k) { return 0; }
}

class B9Result {
    public function result_array() { global $scenario; return !empty($scenario['empty_result']) ? array() : array(new B9Row(array('id' => 1, 'status' => 0, 'sts_finance' => '1'))); }
    public function result() { global $scenario; return !empty($scenario['empty_result']) ? array() : array(new B9Row(array('id' => 1, 'tgl_jurnal' => '2026-11-09', 'coa' => '1304-01-01'))); }
    public function row() { global $scenario; return !empty($scenario['missing_record']) ? null : new B9Row(array('id' => 1, 'no_doc' => 'EXP-2026-001', 'status' => '1', 'id_kasbon' => '')); }
}

class B9Db {
    public function __call($n, $a) { return $this; }
    public function count_all_results($t = '', $reset = true) { return 0; }
    public function get($t = '') { return new B9Result(); }
    public function get_where($t = '', $w = array()) { return new B9Result(); }
    public function query($sql) { return new B9Result(); }
    public function escape($v) { return "'" . addslashes($v) . "'"; }
    public function error() { return array(); }
}

class B9Auth {
    public function user_id() { return 10; }
    public function user_name() { return 'budi_santoso'; }
    public function is_admin() { return false; }
}
class B9Output {
    public function set_content_type($t) { return $this; }
    public function set_status_header($c) { return $this; }
    public function set_output($o) { echo $o; return $this; }
}
class B9Template {
    public function set($k, $v = null) {}
    public function title($t) {}
    public function page_icon($i) {}
    public function render($v) { echo "<!-- rendered {$v} -->"; }
}
class B9Input {
    public $values = array();
    public $query = array();
    public function post($key = null) { return $key === null ? $this->values : (isset($this->values[$key]) ? $this->values[$key] : null); }
    public function get($key = null) { return isset($this->query[$key]) ? $this->query[$key] : (isset($this->values[$key]) ? $this->values[$key] : null); }
}
class B9Load { public function library($l) {} public function helper($h) {} public function model($m) {} }

function expense_b9_run($method, $options = array(), $post = array(), $args = array(), $query = array()) {
    global $scenario, $events, $ci;
    $scenario = $options;
    $events = array();
    $GLOBALS['scenarioCount']++;

    $ci = (new ReflectionClass('Expense'))->newInstanceWithoutConstructor();
    $ci->module_audit_recorder = new Module_audit_recorder();
    $ci->db = new B9Db();
    $ci->input = new B9Input();
    $ci->auth = new B9Auth();
    $ci->output = new B9Output();
    $ci->template = new B9Template();
    $ci->load = new B9Load();
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
    list($e) = expense_b9_run('get_dat_lebih_expense', array('writer_exception' => true));
    exit($e['status'] === 1 ? 0 : 1);
}

function b9_writers($method, $post, $args, $assert) {
    foreach (array('writer_failure', 'writer_exception') as $writer) {
        list($e, $out) = expense_b9_run($method, array($writer => true), $post, $args);
        check($e['status'] === 1 || $e['status'] === 0, "$method: unaffected by $writer");
        $assert($out, $writer);
    }
}

// DataTables: page + row_count; kosong dan berisi.
$datatables = array(
    'get_dat_approval_expense_manage' => 'approval_expense_manage', 'get_expense_app_finance' => 'expense_app_finance',
    'get_dat_pengembalian_kasbon' => 'pengembalian_kasbon', 'get_dat_lebih_expense' => 'lebih_expense',
    'get_dat_verifikasi_pengembalian' => 'verifikasi_pengembalian',
);
foreach ($datatables as $method => $page) {
    foreach (array(array('empty_result' => true), array()) as $i => $opt) {
        list($e, $out) = expense_b9_run($method, $opt);
        $d = $e['context']['data'];
        check($e['status'] === 1, "$method: status 1");
        check($d['page'] === $page, "$method: page $page");
        check($d['outcome'] === 'rendered', "$method: outcome rendered");
        check($d['row_count'] === $i, "$method: row_count $i, got " . var_export($d['row_count'], true));
        $resp = json_decode($out, true);
        check(is_array($resp) && $resp['draw'] === 1, "$method: response intact");
    }
    b9_writers($method, array(), array(), function ($out, $w) use ($method) {
        $resp = json_decode($out, true);
        check(is_array($resp) && $resp['draw'] === 1, "$method: response intact on $w");
    });
}

// Halaman/form dengan id: rendered dan not_found (status 0), metadata id saja.
foreach (array('form_pengembalian' => 'form_pengembalian', 'verifikasi_pengembalian_detail' => 'verifikasi_pengembalian_detail') as $method => $view) {
    list($e, $out) = expense_b9_run($method, array(), array(), array(20));
    check($e['status'] === 1 && $e['context']['data']['outcome'] === 'rendered' && $e['context']['data']['id'] == 20, "$method: rendered + id");
    check(strpos($out, $view) !== false, "$method: view rendered");
    list($e, $out) = expense_b9_run($method, array('missing_record' => true), array(), array(99));
    check($e['status'] === 0 && $e['context']['data']['outcome'] === 'not_found' && $e['context']['data']['id'] == 99, "$method: not_found status 0");
    check(strpos($out, 'tidak ditemukan') !== false, "$method: not-found message kept");
    b9_writers($method, array(), array(20), function ($out, $w) use ($method, $view) {
        check(strpos($out, $view) !== false, "$method: view intact on $w");
    });
}
list($e) = expense_b9_run('form_pengembalian', array(), array(), array(20), array('readonly' => '1'));
check($e['context']['data']['is_readonly'] === 1, 'form_pengembalian: is_readonly 1 dari query readonly=1');

// verifikasi_pengembalian: sudah diinstrumentasi sebelumnya; diverifikasi di sini.
list($e, $out) = expense_b9_run('verifikasi_pengembalian');
check($e['status'] === 1 && $e['context']['data']['outcome'] === 'rendered', 'verifikasi_pengembalian: audit rendered');
check(strpos($out, 'verifikasi_pengembalian_index') !== false, 'verifikasi_pengembalian: view rendered');
b9_writers('verifikasi_pengembalian', array(), array(), function ($out, $w) { check(strpos($out, 'verifikasi_pengembalian_index') !== false, "verifikasi_pengembalian: view intact on $w"); });

// set_jurnal_expense: preview jurnal (read-only); 4 jalur hasil.
$jur = array(
    'stored_view' => array(array('stsview' => 'view', 'no_doc' => 'EXP-2026-001'), array(), 1),
    'empty' => array(array('stsview' => 'view', 'no_doc' => 'EXP-2026-001'), array('empty_result' => true), 0),
    'generated_lebih_expense' => array(array('total_expense' => '1,000', 'total_kasbon' => '600', 'id_kasbon' => array('KB-1'), 'expense' => array('1,000'), 'coa' => array('1304-01-01'), 'no_doc' => 'EXP-2026-001'), array(), 3),
    'generated_direct' => array(array('total_expense' => '500', 'total_kasbon' => '0', 'expense' => array('500'), 'no_doc' => 'EXP-2026-002'), array(), 1),
    'generated_lebih_kasbon' => array(array('total_expense' => '400', 'total_kasbon' => '1,000', 'expense' => array('400'), 'no_doc' => 'EXP-2026-003'), array(), 3),
);
foreach ($jur as $name => $c) {
    list($post, $opt, $rows) = $c;
    $outcomeName = strpos($name, 'generated') === 0 ? 'generated' : $name;
    if ($name === 'empty') { $post = array('total_expense' => '0', 'total_kasbon' => '0', 'no_doc' => 'EXP-2026-001'); }
    list($e, $out) = expense_b9_run('set_jurnal_expense', $opt, $post);
    $d = $e['context']['data'];
    check($e['status'] === 1 && $d['outcome'] === $outcomeName, "set_jurnal_expense $name: outcome $outcomeName, got " . var_export($d['outcome'], true));
    check($d['row_count'] === $rows, "set_jurnal_expense $name: row_count $rows, got " . var_export($d['row_count'], true));
    check($d['no_doc'] === $post['no_doc'], "set_jurnal_expense $name: no_doc");
    $resp = json_decode($out, true);
    check(is_array($resp) && $resp['status'] === 1, "set_jurnal_expense $name: response status 1");
    b9_writers('set_jurnal_expense', $post, array(), function ($out, $w) use ($name) {
        $resp = json_decode($out, true);
        check(is_array($resp) && $resp['status'] === 1, "set_jurnal_expense $name: response intact on $w");
    });
}
// view tanpa jurnal tersimpan jatuh ke hitung dari form (bukan stored_view)
list($e) = expense_b9_run('set_jurnal_expense', array('empty_result' => true), array('stsview' => 'view', 'no_doc' => 'EXP-2026-001', 'total_expense' => '300', 'total_kasbon' => '0', 'expense' => array('300')));
check($e['context']['data']['outcome'] === 'generated', 'set_jurnal_expense: view tanpa jurnal tersimpan -> generated');

// Endpoint batch 10 tetap belum terinstrumentasi
foreach (array() as $pending) {
    $rm = new ReflectionMethod('Expense', $pending);
    $code = implode('', array_slice(file($rm->getFileName()), $rm->getStartLine() - 1, $rm->getEndLine() - $rm->getStartLine() + 1));
    check(strpos($code, 'beginAudit') === false, "$pending: must stay uninstrumented until batch 10");
}

$cmd = escapeshellcmd(PHP_BINARY) . ' ' . escapeshellarg(__FILE__) . ' --subprocess-shutdown 2>&1';
exec($cmd, $subOutput, $subExitCode);
check($subExitCode === 0, 'Subprocess shutdown exit code 0');

echo "PASS Expense batch 9: {$scenarioCount} fixture scenarios + 1 shutdown subprocess" . PHP_EOL;
