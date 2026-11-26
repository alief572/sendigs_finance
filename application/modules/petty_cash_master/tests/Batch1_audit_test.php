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

require dirname(__DIR__) . '/controllers/Petty_cash_master.php';

class PcmStop extends Exception {}

class PcmAuth {
    public function user_id() { return 10; }
    public function user_name() { return 'budi_santoso'; }
    public function restrict($perm) { global $scenario; if (!empty($scenario['deny_perm'])) { throw new PcmStop('restricted'); } return true; }
}
class PcmOutput {
    public function set_content_type($t) { return $this; }
    public function set_output($o) { echo $o; return $this; }
}
class PcmTemplate {
    public function set($k, $v = null) {}
    public function title($t) {}
    public function page_icon($i) {}
    public function render($v) { echo "<!-- rendered {$v} -->"; }
}
class PcmInput {
    public $values = array();
    public function post($key = null) { return $key === null ? $this->values : (isset($this->values[$key]) ? $this->values[$key] : null); }
}
class PcmLoad {
    public function library($l) {}
    public function view($v, $d = array()) { echo "<!-- view {$v} {$d['mode']} -->"; }
}
class PcmModel {
    public function get_header($id) { global $scenario; return !empty($scenario['missing_record']) ? null : (object) array('id' => $id, 'nama' => 'Kas Kecil'); }
    public function get_details($id) { return array(); }
    public function get_coa_list() { return array(); }
    public function get_active_users() { return array(); }
    public function get_server_side_data($p) {
        global $scenario;
        $rows = !empty($scenario['empty_result']) ? array() : array(array('id' => 5, 'nama' => 'A'), array('id' => 6, 'nama' => 'B'));
        return array('draw' => 1, 'recordsTotal' => count($rows), 'recordsFiltered' => count($rows), 'data' => $rows);
    }
    public function save_with_details($h, $d, $id = null) {
        global $scenario;
        $GLOBALS['pcm_saved'] = array($h, $d, $id);
        return !empty($scenario['save_fail']) ? array('status' => 0, 'msg' => 'Gagal menyimpan data. Silakan coba lagi.') : array('status' => 1, 'msg' => 'ok', 'id' => $id ?: 77);
    }
    public function delete_with_details($id) {
        global $scenario;
        return !empty($scenario['delete_fail']) ? array('status' => 0, 'msg' => 'Gagal menghapus data.') : array('status' => 1, 'msg' => 'ok');
    }
}

function pcm_run($method, $options = array(), $post = array(), $args = array()) {
    global $scenario, $events, $ci;
    $scenario = $options;
    $events = array();
    $GLOBALS['scenarioCount']++;
    $ci = (new ReflectionClass('Petty_cash_master'))->newInstanceWithoutConstructor();
    $ci->module_audit_recorder = new Module_audit_recorder();
    $ci->auth = new PcmAuth();
    $ci->output = new PcmOutput();
    $ci->template = new PcmTemplate();
    $ci->input = new PcmInput();
    $ci->load = new PcmLoad();
    $ci->Petty_cash_master_model = new PcmModel();
    $ci->input->values = array_merge(array('draw' => 1, 'password' => 'PRIVATE_PASSWORD'), $post);

    ob_start();
    $stopped = false;
    try {
        call_user_func_array(array($ci, $method), $args);
    } catch (PcmStop $s) {
        $stopped = true;
    } catch (Throwable $e) {
        ob_end_clean();
        throw $e;
    }
    $output = ob_get_clean();
    $ci->module_audit_recorder->finish_interrupted();

    check(count($events) === 1, $method . ': expected exactly one audit event, got ' . count($events));
    check($events[0]['context']['module'] === 'petty_cash_master', $method . ': wrong module');
    check($events[0]['context']['methode'] === $method, $method . ': wrong method');
    $flat = json_encode($events[0]['context']);
    check(strpos($flat, 'PRIVATE_PASSWORD') === false, $method . ': password leaked');
    check(strpos($flat, 'Kas Kecil') === false, $method . ': nama master leaked');
    return array($events[0], $output, $stopped);
}

if (in_array('--subprocess-shutdown', $argv ?? array(), true)) {
    list($e) = pcm_run('get_data', array('writer_exception' => true));
    exit($e['status'] === 1 ? 0 : 1);
}

function pcm_writers($method, $post, $args, $assert) {
    foreach (array('writer_failure', 'writer_exception') as $writer) {
        list($e, $out) = pcm_run($method, array($writer => true), $post, $args);
        $assert($e, $out, $writer);
    }
}

// index, create: rendered + denied
foreach (array('index' => 'index', 'create' => 'view form create') as $method => $needle) {
    list($e, $out) = pcm_run($method);
    check($e['status'] === 1 && $e['context']['data']['outcome'] === 'rendered', "$method: rendered");
    check(strpos($out, $needle) !== false, "$method: view output intact");
    list($e, $out, $stopped) = pcm_run($method, array('deny_perm' => true));
    check($stopped && $e['status'] === 0 && $e['context']['data']['outcome'] === 'denied' && $e['context']['data']['reason'] === 'permission_denied', "$method: denied dicatat sebelum restrict menghentikan request");
    check($out === '', "$method: tidak ada output saat ditolak");
    pcm_writers($method, array(), array(), function ($e, $out, $w) use ($method, $needle) { check(strpos($out, $needle) !== false, "$method: intact on $w"); });
}

// edit, view: rendered, not_found, denied
foreach (array('edit' => 'view form edit', 'view' => 'view view view') as $method => $needle) {
    list($e, $out) = pcm_run($method, array(), array(), array(5));
    check($e['status'] === 1 && $e['context']['data']['outcome'] === 'rendered' && $e['context']['data']['id'] == 5, "$method: rendered + id");
    check(strpos($out, $needle) !== false, "$method: view output intact");
    list($e, $out) = pcm_run($method, array('missing_record' => true), array(), array(99));
    check($e['status'] === 0 && $e['context']['data']['outcome'] === 'not_found' && $e['context']['data']['id'] == 99, "$method: not_found");
    check(json_decode($out, true)['status'] === 0, "$method: not_found response intact");
    list($e, , $stopped) = pcm_run($method, array('deny_perm' => true), array(), array(5));
    check($stopped && $e['context']['data']['outcome'] === 'denied', "$method: denied");
    pcm_writers($method, array(), array(5), function ($e, $out, $w) use ($method, $needle) { check(strpos($out, $needle) !== false, "$method: intact on $w"); });
}

// get_data: row_count kosong dan berisi (tanpa id baris di respons seperti perilaku lama tetap)
foreach (array(array('empty_result' => true), array()) as $i => $opt) {
    list($e, $out) = pcm_run('get_data', $opt);
    check($e['status'] === 1 && $e['context']['data']['row_count'] === $i * 2 && $e['context']['data']['outcome'] === 'rendered', 'get_data: row_count ' . $i * 2);
    $resp = json_decode($out, true);
    check($resp['draw'] === 1 && !isset($resp['data'][0]['id']), 'get_data: response intact, id dibuang');
}
pcm_writers('get_data', array(), array(), function ($e, $out, $w) { check(json_decode($out, true)['draw'] === 1, "get_data: intact on $w"); });

// save: validasi, create, update, gagal simpan
$valid = array('nama' => 'Kas Kecil', 'keterangan' => 'Operasional', 'detail' => array(array('coa_code' => '1101-01-01', 'jenis_pengeluaran' => 'ATK', 'nominal' => '1.000.000')));
$cases = array(
    'nama_required' => array_merge($valid, array('nama' => ' ')),
    'keterangan_required' => array_merge($valid, array('keterangan' => '')),
    'detail_required' => array_merge($valid, array('detail' => array())),
    'nominal_invalid' => array_merge($valid, array('detail' => array(array('coa_code' => 'x', 'jenis_pengeluaran' => 'y', 'nominal' => 'abc')))),
);
foreach ($cases as $reason => $post) {
    list($e, $out) = pcm_run('save', array(), $post);
    check($e['status'] === 0 && $e['context']['data']['outcome'] === 'validation_failed' && $e['context']['data']['reason'] === $reason, "save: $reason");
    check(json_decode($out, true)['status'] === 0, "save: $reason response intact");
    check(!isset($GLOBALS['pcm_saved']) || $GLOBALS['pcm_saved'] === null, "save: $reason tidak menyentuh model");
}
list($e, $out) = pcm_run('save', array(), $valid);
$d = $e['context']['data'];
check($e['status'] === 1 && $d['outcome'] === 'saved' && $d['operation'] === 'create' && $d['detail_count'] === 1 && $d['id'] == 77, 'save: create saved');
list($e, $out) = pcm_run('save', array(), array_merge($valid, array('id' => 12)));
check($e['status'] === 1 && $e['context']['data']['operation'] === 'update' && $e['context']['data']['id'] == 12, 'save: update saved');
list($e, $out) = pcm_run('save', array('save_fail' => true), array_merge($valid, array('id' => 12)));
check($e['status'] === 0 && $e['context']['data']['outcome'] === 'save_failed', 'save: save_failed');
check(json_decode($out, true)['status'] === 0, 'save: save_failed response intact');
pcm_writers('save', $valid, array(), function ($e, $out, $w) { check($e['status'] === 1 && json_decode($out, true)['status'] === 1, "save: intact on $w"); });

// delete: deleted, delete_failed, not_found, denied
list($e, $out) = pcm_run('delete', array(), array(), array(5));
check($e['status'] === 1 && $e['context']['data']['outcome'] === 'deleted' && $e['context']['data']['id'] == 5, 'delete: deleted');
list($e, $out) = pcm_run('delete', array('delete_fail' => true), array(), array(5));
check($e['status'] === 0 && $e['context']['data']['outcome'] === 'delete_failed', 'delete: delete_failed');
list($e, $out) = pcm_run('delete', array('missing_record' => true), array(), array(99));
check($e['status'] === 0 && $e['context']['data']['outcome'] === 'not_found', 'delete: not_found');
list($e, , $stopped) = pcm_run('delete', array('deny_perm' => true), array(), array(5));
check($stopped && $e['context']['data']['outcome'] === 'denied', 'delete: denied');
pcm_writers('delete', array(), array(5), function ($e, $out, $w) { check($e['status'] === 1 && json_decode($out, true)['status'] === 1, "delete: intact on $w"); });

// Model: sukses tulis ditentukan setelah commit berhasil (commit gagal -> rollback + status 0).
require dirname(__DIR__) . '/models/Petty_cash_master_model.php';
class PcmFakeDb {
    public $commit = true; public $rollbacks = 0;
    public function trans_begin() {}
    public function trans_status() { return true; }
    public function trans_commit() { return $this->commit; }
    public function trans_rollback() { $this->rollbacks++; }
    public function __call($n, $a) { return $this; }
    public function insert_id() { return 3; }
}
foreach (array('save_with_details' => array(array('nama' => 'A'), array(array('coa_code' => 'c', 'jenis_pengeluaran' => 'j', 'nominal' => 5))), 'delete_with_details' => array(5)) as $m => $args) {
    foreach (array(true, false) as $commitOk) {
        $model = (new ReflectionClass('Petty_cash_master_model'))->newInstanceWithoutConstructor();
        $model->db = new PcmFakeDb();
        $model->auth = new PcmAuth();
        $model->db->commit = $commitOk;
        $GLOBALS['scenarioCount']++;
        $r = call_user_func_array(array($model, $m), $args);
        check(($r['status'] === 1) === $commitOk, "model $m: status mengikuti hasil commit");
        check($model->db->rollbacks === ($commitOk ? 0 : 1), "model $m: rollback hanya saat commit gagal");
    }
}

$cmd = escapeshellcmd(PHP_BINARY) . ' ' . escapeshellarg(__FILE__) . ' --subprocess-shutdown 2>&1';
exec($cmd, $subOutput, $subExitCode);
check($subExitCode === 0, 'Subprocess shutdown exit code 0');

echo "PASS Petty cash master batch 1: {$scenarioCount} fixture scenarios + 1 shutdown subprocess" . PHP_EOL;
