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
class B8Row extends ArrayObject {
    public function offsetExists($k) { return true; }
    public function offsetGet($k) { return parent::offsetExists($k) ? parent::offsetGet($k) : 0; }
    public function __get($k) { return 0; }
}

class B8Result {
    public function result_array() { global $scenario; return !empty($scenario['empty_result']) ? array() : array(new B8Row(array('id' => 1, 'status' => 0, 'sts_finance' => '1'))); }
    public function result() { global $scenario; return !empty($scenario['empty_result']) ? array() : array(new B8Row(array('id' => 1))); }
    public function row() { return new B8Row(array('id' => 1)); }
}

class B8Db {
    public function __call($n, $a) { return $this; }
    public function count_all_results($t = '', $reset = true) { return 0; }
    public function get($t = '') { return new B8Result(); }
    public function get_where($t = '', $w = array()) { return new B8Result(); }
    public function escape($v) { return "'" . addslashes($v) . "'"; }
    public function error() { return array(); }
}

class B8Model {
    public function get_data_transport_req_all() { echo json_encode(array('recordsTotal' => 1)); }
    public function getCoaDetail($no) { global $scenario; return !empty($scenario['missing_record']) ? null : (object) array('nama' => 'Kas Kecil'); }
}

class B8Auth {
    public function user_id() { return 10; }
    public function user_name() { return 'budi_santoso'; }
    public function is_admin() { return false; }
}
class B8Output {
    public function set_content_type($t) { return $this; }
    public function set_status_header($c) { return $this; }
    public function set_output($o) { echo $o; return $this; }
}
class B8Template {
    public function set($k, $v = null) {}
    public function title($t) {}
    public function page_icon($i) {}
    public function render($v) { echo "<!-- rendered {$v} -->"; }
}
class B8Input {
    public $values = array();
    public function post($key = null) { return $key === null ? $this->values : (isset($this->values[$key]) ? $this->values[$key] : null); }
}
class B8Load { public function library($l) {} public function helper($h) {} public function model($m) {} }

function expense_b8_run($method, $options = array(), $post = array()) {
    global $scenario, $events, $ci;
    $scenario = $options;
    $events = array();
    $GLOBALS['scenarioCount']++;

    $ci = (new ReflectionClass('Expense'))->newInstanceWithoutConstructor();
    $ci->module_audit_recorder = new Module_audit_recorder();
    $ci->db = new B8Db();
    $ci->input = new B8Input();
    $ci->auth = new B8Auth();
    $ci->output = new B8Output();
    $ci->template = new B8Template();
    $ci->load = new B8Load();
    $ci->Expense_model = new B8Model();
    $ps = new ReflectionProperty('Expense', 'status');
    $ps->setAccessible(true);
    $ps->setValue($ci, array());
    $ci->input->values = array_merge(array('draw' => 1, 'length' => 10, 'start' => 0, 'search' => array('value' => ''),
        'password' => 'PRIVATE_PASSWORD', 'nama' => 'John Doe'), $post);

    ob_start();
    try {
        $ci->$method();
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
    list($e) = expense_b8_run('get_dat_list_kasbon', array('writer_exception' => true));
    exit($e['status'] === 1 ? 0 : 1);
}

// DataTables: page + row_count; kosong dan berisi.
$datatables = array(
    'get_dat_list_kasbon' => 'list_kasbon', 'get_dat_kasbon_list' => 'kasbon_list', 'get_dat_app_kasbon' => 'app_kasbon',
    'get_dat_app_kasbon_manage' => 'app_kasbon_manage', 'get_dat_expense_data' => 'expense_data', 'get_dat_expense_pc' => 'expense_pc',
);
foreach ($datatables as $method => $page) {
    foreach (array(array('empty_result' => true), array()) as $i => $opt) {
        list($e, $out) = expense_b8_run($method, $opt);
        $d = $e['context']['data'];
        check($e['status'] === 1, "$method: status 1");
        check($d['page'] === $page, "$method: page $page");
        check($d['outcome'] === 'rendered', "$method: outcome rendered");
        check($d['row_count'] === $i, "$method: row_count $i, got " . var_export($d['row_count'], true));
        $resp = json_decode($out, true);
        check(is_array($resp) && $resp['draw'] === 1, "$method: response intact");
    }
    foreach (array('writer_failure', 'writer_exception') as $writer) {
        list($e, $out) = expense_b8_run($method, array($writer => true));
        check($e['status'] === 1, "$method: unaffected by $writer");
        $resp = json_decode($out, true);
        check(is_array($resp) && $resp['draw'] === 1, "$method: response intact on $writer");
    }
}

// get_data_transport_req_all
list($e, $out) = expense_b8_run('get_data_transport_req_all');
check($e['status'] === 1 && $e['context']['data']['page'] === 'transport_req_all' && $e['context']['data']['outcome'] === 'rendered', 'transport_req_all: audit');
check(json_decode($out, true)['recordsTotal'] === 1, 'transport_req_all: response intact');
foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e, $out) = expense_b8_run('get_data_transport_req_all', array($writer => true));
    check($e['status'] === 1 && json_decode($out, true)['recordsTotal'] === 1, "transport_req_all: intact on $writer");
}

// get_coa_name: found / not_found (tanpa notice PHP)
list($e, $out) = expense_b8_run('get_coa_name', array(), array('no_coa' => '1101-01-01'));
check($e['status'] === 1 && $e['context']['data']['outcome'] === 'found', 'get_coa_name: found');
check(json_decode($out, true)['nm_coa'] === 'Kas Kecil', 'get_coa_name: nm_coa');
list($e, $out) = expense_b8_run('get_coa_name', array('missing_record' => true), array('no_coa' => 'X'));
check($e['context']['data']['outcome'] === 'not_found', 'get_coa_name: not_found');
check(array_key_exists('nm_coa', json_decode($out, true)) && json_decode($out, true)['nm_coa'] === null, 'get_coa_name: null nm_coa when missing');
foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e, $out) = expense_b8_run('get_coa_name', array($writer => true), array('no_coa' => '1101-01-01'));
    check($e['status'] === 1 && json_decode($out, true)['nm_coa'] === 'Kas Kecil', "get_coa_name: intact on $writer");
}

// kasbon_fin_manage (halaman)
list($e, $out) = expense_b8_run('kasbon_fin_manage');
check($e['status'] === 1 && $e['context']['data']['page'] === 'kasbon_fin_manage' && $e['context']['data']['outcome'] === 'rendered', 'kasbon_fin_manage: audit');
check(strpos($out, 'kasbon_list_fin_manage') !== false, 'kasbon_fin_manage: view rendered');
foreach (array('writer_failure', 'writer_exception') as $writer) {
    list($e, $out) = expense_b8_run('kasbon_fin_manage', array($writer => true));
    check($e['status'] === 1 && strpos($out, 'kasbon_list_fin_manage') !== false, "kasbon_fin_manage: intact on $writer");
}

// get_dat_approval_expense: query jalan, tidak ada output (perilaku lama dipertahankan)
list($e, $out) = expense_b8_run('get_dat_approval_expense');
check($e['status'] === 1 && $e['context']['data']['outcome'] === 'no_output' && $out === '', 'get_dat_approval_expense: no_output, empty body');

// Endpoint batch 9 tetap belum terinstrumentasi
foreach (array() as $pending) {
    $rm = new ReflectionMethod('Expense', $pending);
    $code = implode('', array_slice(file($rm->getFileName()), $rm->getStartLine() - 1, $rm->getEndLine() - $rm->getStartLine() + 1));
    check(strpos($code, 'beginAudit') === false, "$pending: must stay uninstrumented until batch 10");
}

$cmd = escapeshellcmd(PHP_BINARY) . ' ' . escapeshellarg(__FILE__) . ' --subprocess-shutdown 2>&1';
exec($cmd, $subOutput, $subExitCode);
check($subExitCode === 0, 'Subprocess shutdown exit code 0');

echo "PASS Expense batch 8: {$scenarioCount} fixture scenarios + 1 shutdown subprocess" . PHP_EOL;
