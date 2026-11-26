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
    public function get_where($t = '', $w = array()) { global $scenario; return new EpcResult(isset($scenario['evidence']) ? array($scenario['evidence']) : array()); }
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
class EpcUpload {
    public function initialize($c) {}
    public function do_upload($f) { global $scenario; return empty($scenario['upload_fail']); }
    public function display_errors($a = '', $b = '') { return 'upload error'; }
}
class EpcLoad {
    public $owner;
    public function library($l, $cfg = null) { if ($l === 'upload' && $this->owner) { $this->owner->upload = new EpcUpload(); } }
    public function view($v, $d = array()) { echo "<!-- view {$v} -->"; }
}

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
    public function generate_no_pencatatan() { global $scenario; return !empty($scenario['no_exhausted']) ? false : 'PC-2026-0001'; }
    public function save_pencatatan($h, $d, $e, $id = null) { global $scenario; if (!empty($scenario['save_fail'])) { return false; } return $id === null ? 77 : true; }
    public function create_journal_for_pencatatan($id, $is_edit = false) { global $scenario; return array('success' => empty($scenario['journal_fail']), 'message' => 'm'); }
    public function delete_pencatatan($id) { global $scenario; return isset($scenario['delete_result']) ? $scenario['delete_result'] : array('status' => 1, 'msg' => 'ok'); }
    public function retry_journal($id) { global $scenario; return empty($scenario['retry_fail']); }
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
    $ci->load->owner = $ci;
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

$evDir = FCPATH . 'assets/expense_petty_cash/';
if (!is_dir($evDir)) { mkdir($evDir, 0755, true); }

$validPost = array(
    'company' => 'STM', 'request_by' => 'Budi', 'keterangan' => 'ATK', 'petty_cash_id' => 4,
    'details' => array(array('coa_code' => '1101-01-01', 'pengeluaran' => 'ATK', 'jumlah' => 2, 'nominal' => 5000)),
    'evidences' => array(array(array('original_name' => 'nota-rahasia.png', 'encrypted_name' => 'abc.png', 'file_type' => 'png', 'file_size' => 100))),
);

// ---------------- save
list($e, $out) = epc_run('save', array(), $validPost);
$d = epc_data($e);
check($e['status'] === 1 && $d['outcome'] === 'saved' && $d['operation'] === 'create' && $d['detail_count'] === 1 && $d['journal'] === 'synced' && $d['id'] == 77, 'save: create saved, jurnal synced, got ' . json_encode($d));
check(json_decode($out, true)['status'] === true, 'save: response intact');
check(strpos(json_encode($e['context']), 'nota-rahasia') === false && strpos(json_encode($e['context']), 'ATK') === false, 'save: nama file dan isi detail tidak masuk audit');
list($e, $out) = epc_run('save', array('journal_fail' => true), $validPost);
check($e['status'] === 1 && epc_data($e)['outcome'] === 'saved' && epc_data($e)['journal'] === 'failed', 'save: data tersimpan, jurnal gagal dicatat');
check(isset(json_decode($out, true)['warning']), 'save: warning jurnal tetap di respons');
list($e) = epc_run('save', array(), array_merge($validPost, array('id' => 12)));
check($e['status'] === 1 && epc_data($e)['operation'] === 'update' && epc_data($e)['id'] == 12, 'save: update');
list($e, $out) = epc_run('save', array('deny_perm' => true), $validPost);
check($e['status'] === 0 && epc_data($e)['outcome'] === 'denied' && json_decode($out, true)['status'] === false, 'save: denied');
$bad = array(
    'company_invalid' => array_merge($validPost, array('company' => 'XXX')),
    'request_by_required' => array_merge($validPost, array('request_by' => ' ')),
    'details_required' => array_merge($validPost, array('details' => array())),
    'evidence_required' => array_merge($validPost, array('evidences' => array())),
    'nominal_invalid' => array_merge($validPost, array('details' => array(array('coa_code' => 'x', 'pengeluaran' => 'y', 'jumlah' => 1, 'nominal' => 0)))),
);
foreach ($bad as $name => $post) {
    list($e, $out) = epc_run('save', array(), $post);
    check($e['status'] === 0 && epc_data($e)['outcome'] === 'validation_failed' && epc_data($e)['reason'] === 'validation_errors' && epc_data($e)['error_count'] >= 1, "save: $name");
    check(json_decode($out, true)['status'] === false && isset(json_decode($out, true)['errors']), "save: $name response intact");
}
list($e) = epc_run('save', array('no_exhausted' => true), $validPost);
check($e['status'] === 0 && epc_data($e)['reason'] === 'number_exhausted', 'save: nomor urut habis');
list($e, $out) = epc_run('save', array('save_fail' => true), $validPost);
check($e['status'] === 0 && epc_data($e)['outcome'] === 'save_failed' && json_decode($out, true)['status'] === false, 'save: save_failed');
foreach (array('writer_failure', 'writer_exception') as $w) {
    list($e, $out) = epc_run('save', array($w => true), $validPost);
    check($e['status'] === 1 && json_decode($out, true)['status'] === true, "save: intact on $w");
}

// ---------------- delete
list($e, $out) = epc_run('delete', array(), array(), array(5));
check($e['status'] === 1 && epc_data($e)['outcome'] === 'deleted' && epc_data($e)['id'] == 5, 'delete: deleted');
foreach (array('not_found' => 'delete_rejected', 'status_not_deletable' => 'delete_rejected', 'already_reported' => 'delete_rejected', 'transaction_failed' => 'delete_failed') as $reason => $outcome) {
    list($e, $out) = epc_run('delete', array('delete_result' => array('status' => 0, 'msg' => 'x', 'reason' => $reason)), array(), array(5));
    check($e['status'] === 0 && epc_data($e)['reason'] === $reason && epc_data($e)['outcome'] === $outcome, "delete: $reason -> $outcome");
    check(json_decode($out, true)['status'] === 0, "delete: $reason response intact");
}
list($e) = epc_run('delete', array('deny_perm' => true), array(), array(5));
check($e['status'] === 0 && epc_data($e)['outcome'] === 'denied', 'delete: denied');
list($e) = epc_run('delete', array(), array(), array(0));
check($e['status'] === 0 && epc_data($e)['reason'] === 'id_invalid', 'delete: id invalid');
foreach (array('writer_failure', 'writer_exception') as $w) {
    list($e, $out) = epc_run('delete', array($w => true), array(), array(5));
    check($e['status'] === 1 && json_decode($out, true)['status'] === 1, "delete: intact on $w");
}

// ---------------- retry_journal
list($e, $out) = epc_run('retry_journal', array(), array(), array(5));
check($e['status'] === 1 && epc_data($e)['outcome'] === 'retried' && json_decode($out, true)['status'] === '1', 'retry_journal: retried');
list($e, $out) = epc_run('retry_journal', array('retry_fail' => true), array(), array(5));
check($e['status'] === 0 && epc_data($e)['outcome'] === 'retry_failed' && json_decode($out, true)['status'] === '0', 'retry_journal: retry_failed');
list($e) = epc_run('retry_journal', array('deny_perm' => true), array(), array(5));
check($e['status'] === 0 && epc_data($e)['outcome'] === 'denied', 'retry_journal: denied');
list($e) = epc_run('retry_journal', array(), array(), array(null));
check($e['status'] === 0 && epc_data($e)['reason'] === 'id_invalid', 'retry_journal: id invalid');
foreach (array('writer_failure', 'writer_exception') as $w) {
    list($e, $out) = epc_run('retry_journal', array($w => true), array(), array(5));
    check($e['status'] === 1 && json_decode($out, true)['status'] === '1', "retry_journal: intact on $w");
}

// ---------------- upload_evidence
$setFile = function ($name, $size) { $_FILES = array('evidence_file' => array('name' => $name, 'size' => $size)); };
$setFile('Nota Rahasia.PNG', 1234);
list($e, $out) = epc_run('upload_evidence');
$d = epc_data($e);
check($e['status'] === 1 && $d['outcome'] === 'uploaded' && $d['file_type'] === 'png' && $d['file_size'] === 1234, 'upload_evidence: uploaded');
check(json_decode($out, true)['status'] === true && strpos(json_encode($e['context']), 'Rahasia') === false, 'upload_evidence: nama file asli tidak masuk audit');
list($e, $out) = epc_run('upload_evidence', array('deny_perm' => true));
check($e['status'] === 0 && epc_data($e)['outcome'] === 'denied', 'upload_evidence: denied');
$_FILES = array();
list($e) = epc_run('upload_evidence');
check($e['status'] === 0 && epc_data($e)['reason'] === 'file_required', 'upload_evidence: tanpa file');
$setFile('virus.exe', 10);
list($e) = epc_run('upload_evidence');
check($e['status'] === 0 && epc_data($e)['reason'] === 'file_type_invalid' && epc_data($e)['file_type'] === 'exe', 'upload_evidence: tipe tidak valid');
$setFile('besar.pdf', 6000000);
list($e) = epc_run('upload_evidence');
check($e['status'] === 0 && epc_data($e)['reason'] === 'file_too_large' && epc_data($e)['file_size'] === 6000000, 'upload_evidence: terlalu besar');
$setFile('ok.pdf', 100);
list($e, $out) = epc_run('upload_evidence', array('upload_fail' => true));
check($e['status'] === 0 && epc_data($e)['outcome'] === 'upload_failed' && json_decode($out, true)['status'] === false, 'upload_evidence: upload_failed');
foreach (array('writer_failure', 'writer_exception') as $w) {
    list($e, $out) = epc_run('upload_evidence', array($w => true));
    check($e['status'] === 1 && json_decode($out, true)['status'] === true, "upload_evidence: intact on $w");
}

// ---------------- delete_evidence
file_put_contents($evDir . 'hapus.png', 'x');
list($e, $out) = epc_run('delete_evidence', array('evidence' => (object) array('id' => 9, 'encrypted_name' => 'hapus.png')), array(), array(9));
check($e['status'] === 1 && epc_data($e)['outcome'] === 'deleted' && epc_data($e)['source'] === 'id' && epc_data($e)['file_removed'] === 1 && !file_exists($evDir . 'hapus.png'), 'delete_evidence: by id, file terhapus');
check(json_decode($out, true)['status'] === true, 'delete_evidence: response intact');
file_put_contents($evDir . 'byname.png', 'x');
list($e) = epc_run('delete_evidence', array(), array('encrypted_name' => 'byname.png'), array(null));
check($e['status'] === 1 && epc_data($e)['source'] === 'name' && epc_data($e)['file_removed'] === 1, 'delete_evidence: by name');
list($e) = epc_run('delete_evidence', array(), array('encrypted_name' => 'tidak-ada.png'), array(null));
check($e['status'] === 1 && epc_data($e)['file_removed'] === 0, 'delete_evidence: file fisik tidak ada -> file_removed 0');
list($e, $out) = epc_run('delete_evidence', array(), array(), array(null));
check($e['status'] === 0 && epc_data($e)['outcome'] === 'not_found' && json_decode($out, true)['status'] === false, 'delete_evidence: not_found');
list($e) = epc_run('delete_evidence', array('deny_perm' => true), array('encrypted_name' => 'x.png'), array(null));
check($e['status'] === 0 && epc_data($e)['outcome'] === 'denied', 'delete_evidence: denied');
foreach (array('writer_failure', 'writer_exception') as $w) {
    list($e, $out) = epc_run('delete_evidence', array($w => true), array('encrypted_name' => 'x.png'), array(null));
    check($e['status'] === 1 && json_decode($out, true)['status'] === true, "delete_evidence: intact on $w");
}

// ---------------- Model: commit dicek (save_pencatatan, delete_pencatatan)
require dirname(__DIR__) . '/models/Expense_petty_cash_model.php';
class EpcCommitDb {
    public $complete = true; public $status = true; public $commit = true; public $rollbacks = 0; private $gets = 0;
    public function trans_start() {}
    public function trans_begin() {}
    public function trans_complete() { return $this->complete; }
    public function trans_status() { return $this->status; }
    public function trans_commit() { return $this->commit; }
    public function trans_rollback() { $this->rollbacks++; }
    public function insert_id() { return 31; }
    public function __call($n, $a) { return $this; }
    public function get() {
        $this->gets++;
        if ($this->gets === 1) { return new EpcResult(array((object) array('id' => 5, 'no_pencatatan' => 'PC-1', 'status' => 'draft'))); }
        if ($this->gets === 3) { return new EpcResult(array((object) array('encrypted_name' => 'model-evidence.png'))); }
        return new EpcResult(array());
    }
}
$hdr = array('company' => 'STM'); $dts = array(array('coa_code' => 'c', 'pengeluaran' => 'p', 'jumlah' => 1, 'nominal' => 5)); $evs = array();
foreach (array(array(true, true, true), array(false, true, false), array(true, false, false)) as $c) {
    $model = (new ReflectionClass('Expense_petty_cash_model'))->newInstanceWithoutConstructor();
    $model->db = new EpcCommitDb(); $model->db->complete = $c[0]; $model->db->status = $c[1]; $model->auth = new EpcAuth();
    $GLOBALS['scenarioCount']++;
    $r = $model->save_pencatatan($hdr, $dts, $evs, null);
    check(($r !== false) === $c[2], 'model save_pencatatan: hasil mengikuti commit/status ' . json_encode($c));
}
foreach (array(array(true, true), array(false, false)) as $c) {
    file_put_contents($evDir . 'model-evidence.png', 'x');
    $model = (new ReflectionClass('Expense_petty_cash_model'))->newInstanceWithoutConstructor();
    $model->db = new EpcCommitDb(); $model->db->commit = $c[0];
    $GLOBALS['scenarioCount']++;
    $r = $model->delete_pencatatan(5);
    check(($r['status'] === 1) === $c[1], 'model delete_pencatatan: status mengikuti commit ' . json_encode($c));
    if (!$c[0]) {
        check($r['reason'] === 'transaction_failed' && $model->db->rollbacks === 1, 'model delete_pencatatan: commit gagal -> rollback + reason');
        check(file_exists($evDir . 'model-evidence.png'), 'model delete_pencatatan: file evidence TIDAK dihapus saat commit gagal');
    } else {
        check(!file_exists($evDir . 'model-evidence.png'), 'model delete_pencatatan: file evidence dihapus setelah commit sukses');
    }
}
@unlink($evDir . 'model-evidence.png');

$cmd = escapeshellcmd(PHP_BINARY) . ' ' . escapeshellarg(__FILE__) . ' --subprocess-shutdown 2>&1';
exec($cmd, $subOutput, $subExitCode);
check($subExitCode === 0, 'Subprocess shutdown exit code 0');

echo "PASS Expense petty cash batch 3: {$scenarioCount} fixture scenarios + 1 shutdown subprocess" . PHP_EOL;
