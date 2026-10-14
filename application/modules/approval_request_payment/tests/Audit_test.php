<?php
// Real Approval_request_payment controller + real model + real Module_audit_recorder; in-memory DB fixture.
define('BASEPATH', __DIR__);
define('APPPATH', dirname(__DIR__, 3) . '/');
if (!defined('DBCNL')) { define('DBCNL', 'db_consultant_new'); }

$events = array();
$scenario = array();
$ci = null;
$child = $argv[1] ?? null;
$scenarioCount = 0;

set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});
function check($condition, $message) {
    if (!$condition) { throw new RuntimeException($message); }
}
function &get_instance() { global $ci; return $ci; }
function log_message($level, $message) {}
function site_url($uri = '') { return 'http://localhost/' . ltrim((string) $uri, '/'); }
function base_url($uri = '') { return 'http://localhost/' . ltrim((string) $uri, '/'); }
function redirect($uri = '') {
    global $scenario;
    $scenario['redirected_to'] = $uri;
}
function has_permission($perm) {
    global $scenario;
    return empty($scenario['permission_denied']);
}
function audit_log($message, $status = null, $context = array()) {
    global $events, $scenario, $child, $ci;
    $event = compact('message', 'status', 'context');
    $events[] = $event;
    $events[count($events) - 1]['calls_at_write'] = $ci->db->calls;
    if ($child) { echo 'AUDIT:' . json_encode($event) . "\n"; }
    if (!empty($scenario['writer_exception'])) { throw new RuntimeException('PRIVATE_WRITER_ERROR'); }
    return empty($scenario['writer_failure']);
}

require APPPATH . 'libraries/Module_audit_recorder.php';
if (!class_exists('BF_Model')) {
    class BF_Model {
        public function __construct() {}
        public function __get($name) { global $ci; return $ci->$name ?? null; }
    }
}
require dirname(__DIR__) . '/models/Approval_request_payment_model.php';

class ApprovalFixtureInput {
    public $values = array();
    public function post($key = null) { return $key === null ? $this->values : ($this->values[$key] ?? null); }
    public function get_post($key) { return $this->post($key); }
}
class ApprovalFixtureSession {
    public $flash = array();
    public function userdata($key) { return array('username' => 'approver_user', 'id_user' => 9); }
    public function set_flashdata($key, $val) { $this->flash[$key] = $val; }
}
class ApprovalFixtureAuth {
    public function restrict($permission) {
        global $child;
        if ($child === '--denied') { exit; }
    }
    public function user_name() { return 'approver_user'; }
}
class ApprovalFixtureTemplate {
    public function __call($name, $args) {
        global $scenario;
        if ($name === 'render') {
            if (!empty($scenario['render_exit'])) { exit; }
            if (!empty($scenario['render_exception'])) { throw new RuntimeException('PRIVATE_RENDER_ERROR'); }
        }
    }
}
class ApprovalFixturePrintModel {
    public function build_print_url($item) {
        global $ci;
        $ci->db->get_where('tr_kasbon', array('no_doc' => $item->no_dokumen))->row();
        return 'http://localhost/print/' . $item->no_dokumen;
    }
}
class ApprovalFixtureLoad {
    public function database($name = '', $return = false) { global $ci; return $ci->db; }
    public function model($models) {
        global $ci;
        foreach ((array) $models as $m) {
            if (stripos($m, 'Request_payment_model') !== false && !isset($ci->Request_payment_model)) {
                $ci->Request_payment_model = new ApprovalFixturePrintModel();
            }
        }
    }
    public function library($name) {
        global $ci;
        if (!isset($ci->module_audit_recorder)) {
            $ci->module_audit_recorder = new Module_audit_recorder();
        }
    }
}

class ApprovalFixtureResult {
    private $table;
    public function __construct($table) { $this->table = (string) $table; }
    public function row() {
        global $scenario;
        if (strpos($this->table, 'MAX(id)') !== false) { return (object) array('max_id' => 'BK--2610-0001'); }
        if ($this->table === 'tr_rp_pengajuan_h') {
            if (!empty($scenario['missing_header'])) { return null; }
            return (object) array(
                'id' => 15,
                'no_pengajuan' => 'PGJ-2610-0015',
                'company_nama' => 'Sustain',
                'status' => $scenario['header_status'] ?? 'pending',
                'submitted_at' => '2026-10-11 09:00:00',
                'jumlah_dokumen' => 2,
                'total_dibayarkan' => 250000,
            );
        }
        if ($this->table === 'kons_tr_kasbon_project_header') {
            return (object) array('created_by' => 'USR-1');
        }
        if ($this->table === 'users') {
            return (object) array('nm_lengkap' => 'PRIVATE_CREATOR_NAME', 'username' => 'PRIVATE_CREATOR_USER');
        }
        if ($this->table === 'v_request_payment') {
            return (object) array('tanggal' => '2026-10-11');
        }
        return (object) array('id' => 1, 'no_doc' => 'DOC-01');
    }
    public function result() {
        global $scenario;
        if (!empty($scenario['empty'])) { return array(); }
        if ($this->table === 'tr_rp_pengajuan_d') {
            if (isset($scenario['batch_items'])) { return $scenario['batch_items']; }
            return array(
                (object) array(
                    'id' => 101, 'id_pengajuan' => 15, 'id_dokumen' => 1, 'no_dokumen' => 'DPM-01',
                    'kategori' => $scenario['item_kategori'] ?? 'Direct Payment',
                    'request_by' => 'PRIVATE_REQ_BY', 'keperluan' => 'PRIVATE_KEPERLUAN',
                    'dpp' => 100000, 'flag_ppn' => 1, 'flag_pph23' => 1, 'flag_pph21' => 0,
                    'nilai_ppn' => 11000, 'nilai_pph' => 2000, 'admin' => 2500, 'dibayarkan' => 106500,
                ),
                (object) array(
                    'id' => 102, 'id_pengajuan' => 15, 'id_dokumen' => 2, 'no_dokumen' => 'KS-02',
                    'kategori' => 'Kasbon',
                    'request_by' => 'PRIVATE_REQ_BY_2', 'keperluan' => 'PRIVATE_KEPERLUAN_2',
                    'dpp' => 150000, 'flag_ppn' => 0, 'flag_pph23' => 0, 'flag_pph21' => 1,
                    'nilai_ppn' => 0, 'nilai_pph' => 3750, 'admin' => 0, 'dibayarkan' => 146250,
                ),
            );
        }
        $r = $this->row();
        return $r ? array($r) : array();
    }
    public function num_rows() {
        global $scenario;
        if ($this->table === 'tr_pr_non_po') { return !empty($scenario['is_cash_doc']) ? 1 : 0; }
        if ($this->table === 'payment_approve') { return !empty($scenario['payment_already_exists']) ? 1 : 0; }
        return 0;
    }
}

class ApprovalFixtureDb {
    public $calls = array(); public $queryNumber = 0; public $queryError = 0;
    public $writeNumber = 0; public $writes = array(); public $failed = false;
    public $committed = false; private $fromTable = '';
    public function __call($name, $args) {
        global $scenario;
        $this->calls[] = $name;
        if ($name === 'from') { $this->fromTable = $args[0] ?? ''; return $this; }
        if ($name === 'count_all_results') {
            $this->queryNumber++;
            $this->queryError = !empty($scenario['query_failure']) || ($scenario['fail_query'] ?? 0) === $this->queryNumber ? 999 : 0;
            return empty($scenario['empty']) ? 2 : 0;
        }
        if (in_array($name, array('get', 'get_where', 'query'), true)) {
            $this->queryNumber++;
            $this->queryError = !empty($scenario['query_failure']) || ($scenario['fail_query'] ?? 0) === $this->queryNumber ? 999 : 0;
            $table = $args[0] ?? $this->fromTable;
            if ($name === 'query') {
                if (stripos($table, 'MAX(id)') !== false) { $table = 'MAX(id)'; }
                elseif (preg_match('/from\s+([\w.]+)/i', $table, $m)) { $table = $m[1]; }
            }
            $table = trim(preg_replace('/\s+[a-z0-9_]+$/i', '', (string) $table));
            return new ApprovalFixtureResult($table);
        }
        if ($name === 'error') {
            return array('code' => $this->queryError, 'message' => $this->queryError ? 'PRIVATE_DB_ERROR' : '');
        }
        if (in_array($name, array('insert', 'insert_batch', 'update'), true)) {
            $this->writeNumber++;
            if (!empty($scenario[$name . '_failure']) || ($scenario['fail_write'] ?? 0) === $this->writeNumber) {
                $this->failed = true; $this->queryError = 999; return false;
            }
            $this->writes[] = array($name, $args);
            return true;
        }
        if ($name === 'trans_begin') { return empty($scenario['trans_begin_failure']); }
        if ($name === 'trans_status') { return !$this->failed && empty($scenario['transaction_failure']); }
        if ($name === 'trans_commit') {
            if (!empty($scenario['abort_commit'])) { exit; }
            $ok = empty($scenario['trans_commit_failure']);
            $this->committed = $ok;
            return $ok;
        }
        if ($name === 'trans_rollback') { $this->writes = array(); $this->committed = false; return true; }
        return $this;
    }
}

class Admin_Controller {
    public function __construct() {
        global $ci;
        $ci = $this;
        $this->db = new ApprovalFixtureDb();
        $this->load = new ApprovalFixtureLoad();
        $this->input = new ApprovalFixtureInput();
        $this->session = new ApprovalFixtureSession();
        $this->auth = new ApprovalFixtureAuth();
        $this->template = new ApprovalFixtureTemplate();
        $this->Approval_request_payment_model = new Approval_request_payment_model();
        $ref = new ReflectionProperty('Approval_request_payment_model', 'consultant');
        $ref->setAccessible(true);
        $ref->setValue($this->Approval_request_payment_model, $this->db);
    }
}

require dirname(__DIR__) . '/controllers/Approval_request_payment.php';

function run_approval($method, $options = array(), $post = array(), $callArgs = array()) {
    global $events, $scenario, $ci, $scenarioCount;
    $events = array();
    $scenario = $options;
    $scenarioCount++;
    $controller = new Approval_request_payment();
    $controller->input->values = array_merge(array(
        'draw' => 1, 'start' => 0, 'length' => 25,
        'search' => array('value' => 'PRIVATE_SEARCH_TERM'),
        'id_pengajuan' => 15,
        'decisions' => array(
            array('no_dokumen' => 'DPM-01', 'decision' => 'approved', 'alasan' => ''),
            array('no_dokumen' => 'KS-02', 'decision' => 'rejected', 'alasan' => 'PRIVATE_REJECT_REASON'),
        ),
    ), $post);

    ob_start();
    try {
        call_user_func_array(array($controller, $method), $callArgs);
    } catch (Throwable $e) {
        if (isset($controller->module_audit_recorder)) { $controller->module_audit_recorder->finish_interrupted(); }
        if (empty($options['render_exception'])) { ob_end_clean(); throw $e; }
    }
    $output = ob_get_clean();
    if (isset($controller->module_audit_recorder)) { $controller->module_audit_recorder->finish_interrupted(); }
    check(count($events) === 1, $method . ': expected exactly one audit event');
    $event = $events[0];
    check($event['context']['module'] === 'approval_request_payment' && $event['context']['methode'] === $method, $method . ': identity');
    check(strpos(json_encode($events), 'PRIVATE') === false, $method . ': private data leaked');
    return array($event, $output, $controller);
}

if ($child && strpos($child, '--approval-exit-') === 0) {
    $m = substr($child, strlen('--approval-exit-'));
    $args = $m === 'detail' ? array(15) : array();
    run_approval($m, array('render_exit' => true, 'abort_commit' => true), array(), $args);
    exit;
}

// 1. index tests
foreach (array(array(), array('writer_failure' => true), array('writer_exception' => true)) as $wOpt) {
    list($event) = run_approval('index', $wOpt);
    check($event['status'] === 1, 'index success');
}
list($event) = run_approval('index', array('render_exception' => true));
check($event['status'] === 0, 'index render exception');

// 2. get_data_pending tests
foreach (array(array(), array('empty' => true), array('writer_failure' => true), array('writer_exception' => true)) as $opt) {
    list($event, $output, $controller) = run_approval('get_data_pending', $opt);
    $expectedRows = !empty($opt['empty']) ? 0 : 1;
    check($event['status'] === 1 && $event['context']['data']['row_count'] === $expectedRows && $event['context']['data']['search_present'] === true, 'get_data_pending success');
}
list(, , $controller) = run_approval('get_data_pending');
$qTotal = $controller->db->queryNumber;
for ($q = 1; $q <= $qTotal; $q++) {
    list($failed) = run_approval('get_data_pending', array('fail_query' => $q));
    check($failed['status'] === 0 && $failed['context']['data']['reason'] === 'query_failure', 'get_data_pending query failure ' . $q);
}

// 3. detail tests
list($event) = run_approval('detail', array('missing_header' => true), array(), array(15));
check($event['status'] === 0 && $event['context']['data']['reason'] === 'not_found', 'detail missing header');
list($event) = run_approval('detail', array('header_status' => 'done'), array(), array(15));
check($event['status'] === 0 && $event['context']['data']['reason'] === 'already_decided', 'detail already decided');

foreach (array(array(), array('is_cash_doc' => true), array('writer_failure' => true), array('writer_exception' => true)) as $opt) {
    list($event, , $controller) = run_approval('detail', $opt, array(), array(15));
    check($event['status'] === 1 && $event['context']['data']['item_count'] === 2 && $event['context']['data']['no_pengajuan'] === 'PGJ-2610-0015', 'detail success');
}
list(, , $controller) = run_approval('detail', array(), array(), array(15));
$qTotal = $controller->db->queryNumber;
for ($q = 1; $q <= $qTotal; $q++) {
    list($failed) = run_approval('detail', array('fail_query' => $q), array(), array(15));
    check($failed['status'] === 0 && $failed['context']['data']['reason'] === 'query_failure', 'detail query failure ' . $q);
}
list($event) = run_approval('detail', array('render_exception' => true), array(), array(15));
check($event['status'] === 0, 'detail render exception');

// 4. save_decision tests
list($event, $output, $controller) = run_approval('save_decision', array('permission_denied' => true));
check($event['status'] === 0 && $event['context']['data']['reason'] === 'permission_denied' && json_decode($output, true)['status'] === 0 && !$controller->db->committed, 'save_decision permission denied');

list($event, $output, $controller) = run_approval('save_decision', array('missing_header' => true));
check($event['status'] === 0 && $event['context']['data']['reason'] === 'not_found' && json_decode($output, true)['status'] === 0, 'save_decision missing header');

list($event, $output, $controller) = run_approval('save_decision', array('header_status' => 'done'));
check($event['status'] === 0 && $event['context']['data']['reason'] === 'already_decided' && json_decode($output, true)['status'] === 0, 'save_decision already decided');

list($event, $output, $controller) = run_approval('save_decision', array(), array('decisions' => array()));
check($event['status'] === 0 && $event['context']['data']['reason'] === 'invalid_decisions' && json_decode($output, true)['status'] === 0, 'save_decision invalid decisions');

list($event, $output, $controller) = run_approval('save_decision', array('empty' => true));
check($event['status'] === 0 && $event['context']['data']['reason'] === 'empty_batch' && json_decode($output, true)['status'] === 0, 'save_decision empty batch');

list($event, $output, $controller) = run_approval('save_decision', array(), array(
    'decisions' => array(
        array('no_dokumen' => 'DPM-01', 'decision' => 'rejected', 'alasan' => '   '),
    ),
));
check($event['status'] === 0 && $event['context']['data']['reason'] === 'missing_reject_reason' && json_decode($output, true)['status'] === 0, 'save_decision missing reject reason');

foreach (array(array(), array('payment_already_exists' => true), array('writer_failure' => true), array('writer_exception' => true)) as $opt) {
    list($event, $output, $controller) = run_approval('save_decision', $opt);
    check(
        $event['status'] === 1
        && $event['context']['data']['outcome'] === 'saved'
        && $event['context']['data']['approved_count'] === 1
        && $event['context']['data']['rejected_count'] === 1
        && $event['context']['data']['document_count'] === 2
        && json_decode($output, true)['status'] === 1
        && $controller->db->committed,
        'save_decision mixed approve/reject success'
    );
    check(in_array('trans_commit', $event['calls_at_write'], true), 'save_decision audit written after commit');
}

list(, , $controller) = run_approval('save_decision');
$qTotal = $controller->db->queryNumber;
$wTotal = $controller->db->writeNumber;
for ($q = 1; $q <= $qTotal; $q++) {
    list($failed, $fOut, $fCtrl) = run_approval('save_decision', array('fail_query' => $q));
    check($failed['status'] === 0 && json_decode($fOut, true)['status'] === 0 && !$fCtrl->db->committed, 'save_decision query failure ' . $q);
}
for ($w = 1; $w <= $wTotal; $w++) {
    list($failed, $fOut, $fCtrl) = run_approval('save_decision', array('fail_write' => $w));
    check($failed['status'] === 0 && json_decode($fOut, true)['status'] === 0 && !$fCtrl->db->committed, 'save_decision write failure ' . $w);
}
foreach (array('trans_begin_failure', 'transaction_failure', 'trans_commit_failure') as $txFail) {
    list($failed, $fOut, $fCtrl) = run_approval('save_decision', array($txFail => true));
    check($failed['status'] === 0 && json_decode($fOut, true)['status'] === 0 && !$fCtrl->db->committed, 'save_decision ' . $txFail);
}

// Bounded 100 document_ids
$manyBatchItems = array();
$manyDecisions = array();
for ($i = 1; $i <= 101; $i++) {
    $docNo = 'DOC-' . $i;
    $manyBatchItems[] = (object) array(
        'id' => $i, 'id_pengajuan' => 15, 'id_dokumen' => $i, 'no_dokumen' => $docNo,
        'kategori' => 'Kasbon', 'request_by' => 'PRIVATE_REQ', 'keperluan' => 'PRIVATE_KEP',
        'dpp' => 10000, 'flag_ppn' => 0, 'flag_pph23' => 0, 'flag_pph21' => 0,
        'nilai_ppn' => 0, 'nilai_pph' => 0, 'admin' => 0, 'dibayarkan' => 10000,
    );
    $manyDecisions[] = array('no_dokumen' => $docNo, 'decision' => 'approved', 'alasan' => '');
}
list($event, $output, $controller) = run_approval('save_decision', array('batch_items' => $manyBatchItems), array('decisions' => $manyDecisions));
check($event['status'] === 1 && $event['context']['data']['document_count'] === 101 && count($event['context']['data']['document_ids']) === 100 && $event['context']['data']['approved_count'] === 101, 'save_decision bounded 100 IDs');

// Subprocess shutdown tests
foreach (array('index', 'detail', 'save_decision') as $method) {
    $output = shell_exec('"' . PHP_BINARY . '" "' . __FILE__ . '" --approval-exit-' . $method);
    check(substr_count($output, 'AUDIT:') === 1, $method . ': real shutdown one event');
    $event = json_decode(trim(substr($output, strpos($output, 'AUDIT:') + 6)), true);
    check($event['status'] === 0 && $event['context']['data']['outcome'] === 'interrupted', $method . ': real shutdown interrupted');
}

// Constructor remains opt-in
$events = array(); $scenario = array(); $ci = new Approval_request_payment();
check(!isset($ci->module_audit_recorder) && !$events, 'Constructor must not activate scope'); $scenarioCount++;

echo 'PASS Approval Request Payment: ' . ($scenarioCount + 3) . " scenarios, real controller/recorder and model\n";
