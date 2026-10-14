<?php
// Real controller + recorder; all business persistence is an in-memory fixture.
define('BASEPATH', __DIR__);
define('APPPATH', dirname(__DIR__, 3) . '/');
$events = array(); $scenario = array(); $ci = null; $child = $argv[1] ?? null; $scenarioCount = 0;
set_error_handler(function ($severity, $message, $file, $line) { throw new ErrorException($message, 0, $severity, $file, $line); });
function check($condition, $message) { if (!$condition) { throw new RuntimeException($message); } }
function &get_instance() { global $ci; return $ci; }
function log_message($level, $message) {}
function audit_log($message, $status = null, $context = array()) {
    global $events, $scenario, $child, $ci;
    $event = compact('message', 'status', 'context'); $events[] = $event;
    $events[count($events) - 1]['calls_at_write'] = $ci->db->calls;
    if ($child) { echo 'AUDIT:' . json_encode($event) . "\n"; }
    if (!empty($scenario['writer_exception'])) { throw new RuntimeException('PRIVATE'); }
    return empty($scenario['writer_failure']);
}
require APPPATH . 'libraries/Module_audit_recorder.php';
class FixtureInput {
    public $values = array();
    public function post($key = null) { return $key === null ? $this->values : ($this->values[$key] ?? null); }
    public function get_post($key) { return $this->post($key); }
}
class FixtureSession { public function userdata($key) { return array('username' => 'tester', 'id_user' => 7); } }
class FixtureAuth {
    public function restrict($permission) { global $child; if ($child === '--denied') { exit; } }
    public function user_name() { return 'tester'; }
}
class FixtureResult {
    public function result() {
        global $scenario;
        if (!empty($scenario['empty']) || !empty($scenario['missing'])) { return array(); }
        $rows = array((object) array('id' => 1, 'invoice_no' => 'INV-TEST', 'status' => 2,
            'no_dokumen' => $scenario['source_document'] ?? 'DOC-TEST', 'kategori' => $scenario['source_category'] ?? 'Expense', 'request_by' => 'PRIVATE',
            'keperluan' => 'PRIVATE', 'nilai_pengajuan' => 100));
        if (!empty($scenario['companies'])) { $second = clone $rows[0]; $second->no_dokumen = 'DOC-SECOND'; $rows[] = $second; }
        return $rows;
    }
    public function row() {
        global $scenario;
        return (object) array('status' => 2, 'created_by' => empty($scenario['no_consultant_creator']) ? 7 : null,
            'nm_lengkap' => empty($scenario['no_consultant_name']) ? 'PRIVATE' : null, 'username' => 'PRIVATE');
    }
    public function num_rows() { global $scenario; return empty($scenario['cash_source']) ? 0 : 1; }
}
class FixtureDb {
    public $calls = array(); public $failed = false;
    public function __call($name, $args) {
        global $scenario;
        $this->calls[] = $name;
        if (in_array($name, array('insert', 'insert_batch', 'update'), true)) {
            if (!empty($scenario[$name . '_failure'])) { $this->failed = true; return false; }
            return true;
        }
        if (in_array($name, array('get', 'get_where', 'query'), true)) { return new FixtureResult(); }
        if ($name === 'trans_status') { return (!empty($scenario['status_does_not_fail']) || !$this->failed) && empty($scenario['transaction_failure']); }
        if ($name === 'trans_begin' || $name === 'trans_commit') { return empty($scenario[$name . '_failure']); }
        if ($name === 'trans_rollback') { return true; }
        if ($name === 'insert_id') { return $this->failed ? 0 : 1; }
        if ($name === 'error') { return array('code' => !empty($scenario['query_failure']) ? 999 : 0); }
        return $this;
    }
}
class FixtureModel {
    public function __call($name, $args) {
        global $scenario;
        if (!empty($scenario['model_exception'])) { throw new RuntimeException('PRIVATE'); }
        if ($name === 'get_locked_docs') { return !empty($scenario['locked']) ? array('DOC-TEST') : array(); }
        if ($name === 'derive_company_for_docs') {
            $out = array(); foreach ($args[0] as $i => $doc) {
                $out[$doc] = array('company_id' => !empty($scenario['companies']) ? $i + 1 : 3, 'company_nama' => 'Company');
            } return $out;
        }
        if ($name === 'calc_tax') { return array('nilai_ppn' => 0, 'nilai_pph' => 0, 'admin' => 0, 'dibayarkan' => 100); }
        if ($name === 'get_company_names_lookup') { return array(3 => 'Company'); }
        if (in_array($name, array('get_data_request_baru', 'get_summary_request', 'get_server_side_payment_list'), true)) {
            echo json_encode(array('data' => array(), 'recordsFiltered' => 0)); return;
        }
        return empty($scenario['empty']) ? array((object) array('approver' => 'PRIVATE', 'rejected_at' => '', 'alasan' => 'PRIVATE', 'no_pengajuan' => 'PGJ-TEST')) : array();
    }
}
class FixtureAllModel {
    public function GetCoaCombo() { return array(); }
    public function GetAutoGenerate($format) { global $scenario; return empty($scenario['generate_failure']) ? 'PGJ-TEST' : false; }
    public function dataSave($table, $data) { global $ci; $ci->db->insert($table, $data); return $ci->db->insert_id(); }
    public function dataUpdate($table, $data, $where) { global $ci; $ci->db->update($table, $data, $where); return 1; }
}
class FixtureTemplate {
    public function __call($name, $args) { global $scenario; if ($name === 'render' && !empty($scenario['render_exception'])) { throw new RuntimeException('PRIVATE'); } }
}
class FixtureLoad {
    public function database($name, $separate) { global $ci; return $ci->db; }
    public function model($models) {}
    public function library($name) { global $ci; if (!isset($ci->module_audit_recorder)) { $ci->module_audit_recorder = new Module_audit_recorder(); } }
    public function view($name, $data) { global $scenario; if (!empty($scenario['render_exception'])) { throw new RuntimeException('PRIVATE'); } echo 'EXPORT'; }
}
class FixtureUpload {
    public function initialize($config) {}
    public function do_upload($field) { global $scenario; return empty($scenario['upload_failure']); }
    public function data() { return array('file_name' => 'private-file'); }
}
class Admin_Controller {
    public function __construct() {
        global $ci; $ci = $this;
        $this->db = new FixtureDb(); $this->load = new FixtureLoad(); $this->input = new FixtureInput();
        $this->session = new FixtureSession(); $this->auth = new FixtureAuth(); $this->template = new FixtureTemplate();
        $this->Request_payment_model = new FixtureModel(); $this->All_model = new FixtureAllModel(); $this->upload = new FixtureUpload();
    }
}
require dirname(__DIR__) . '/controllers/Request_payment.php';
if ($child === '--denied' || $child === '--shutdown') {
    $ci = new Request_payment();
    if ($child === '--denied') { $ci->index(); }
    else { $ci->load->library('Module_audit_recorder'); $ci->module_audit_recorder->begin('request_payment', 'index'); exit; }
    exit;
}
function run_endpoint($method, $options = array(), $post = array()) {
    global $events, $scenario, $ci, $scenarioCount;
    $scenarioCount++;
    $events = array(); $scenario = $options;
    $controller = new Request_payment();
    $controller->input->values = array_merge(array('items' => array(array('no_dokumen' => 'DOC-TEST')), 'no_dokumen' => 'DOC-TEST',
        'company_id' => 3, 'date_from' => '2026-10-01', 'date_to' => '2026-10-10', 'kategori' => 'Expense',
        'search' => array('value' => 'PRIVATE'), 'start' => 0, 'length' => 25,
        'status' => array(1), 'tipe_1' => 'expense', 'no_doc_1' => 'DOC-TEST', 'tanggal_1' => '2026-10-10',
        'admin_charge_1' => '0', 'nilai_pph_1' => '0', 'nama_1' => 'PRIVATE', 'accnumber_1' => 'PRIVATE', 'password' => 'PRIVATE'), $post);
    ob_start(); try { $controller->$method(); } catch (Throwable $error) {
        if (isset($controller->module_audit_recorder)) { $controller->module_audit_recorder->finish_interrupted(); }
        if (empty($options['model_exception']) && empty($options['render_exception'])) { ob_end_clean(); throw $error; }
    } $output = ob_get_clean();
    if (isset($controller->module_audit_recorder)) { $controller->module_audit_recorder->finish_interrupted(); }
    check(count($events) === 1, $method . ': expected exactly one event');
    check($events[0]['context']['module'] === 'request_payment' && $events[0]['context']['methode'] === $method, 'Endpoint identity');
    check(strpos(json_encode($events), 'PRIVATE') === false, 'PII leaked');
    return array($events[0], $output, $controller);
}
if (defined('AUDIT_FIXTURE_ONLY')) { return; }
$reads = array('index', 'get_data_request', 'get_summary_request', 'export_excel_request', 'reject_history', 'payment_list', 'get_data_payment_list', 'list_approve');
foreach ($reads as $method) {
    foreach (array(array(), array('empty' => true), array('writer_failure' => true), array('writer_exception' => true)) as $options) {
        list($event) = run_endpoint($method, $options); check($event['status'] === 1, $method . ': read status');
    }
    list($event) = run_endpoint($method, array('query_failure' => true)); check($event['status'] === 0, $method . ': query failure');
}
foreach (array('get_data_request', 'get_summary_request', 'get_data_payment_list') as $method) {
    list($event) = run_endpoint($method, array('model_exception' => true)); check($event['context']['data']['outcome'] === 'interrupted', 'Model abort');
}
foreach (array('index', 'payment_list', 'list_approve', 'export_excel_request') as $method) {
    list($event) = run_endpoint($method, array('render_exception' => true)); check($event['status'] === 0, 'Render abort');
}
list($event) = run_endpoint('reject_history', array(), array('no_dokumen' => '')); check($event['status'] === 0, 'Missing history parameter');
foreach (array('locked', 'missing', 'generate_failure', 'insert_failure', 'insert_batch_failure', 'transaction_failure', 'trans_begin_failure', 'trans_commit_failure') as $failure) {
    list($event, $output, $controller) = run_endpoint('submit_for_approval', array($failure => true));
    check($event['status'] === 0 && json_decode($output, true)['status'] === 0, 'Submit failure: ' . $failure);
    check(!in_array('trans_commit', $controller->db->calls, true) || $failure === 'trans_commit_failure', 'Committed failed submit');
}
foreach (array(array('items' => array()), array('items' => array(array('no_dokumen' => '')))) as $post) {
    list($event) = run_endpoint('submit_for_approval', array(), $post); check($event['status'] === 0, 'Invalid selection');
}
foreach (array('insert_failure', 'insert_batch_failure') as $failure) {
    list($event, $output) = run_endpoint('submit_for_approval', array($failure => true, 'status_does_not_fail' => true));
    check($event['status'] === 0 && json_decode($output, true)['status'] === 0, 'Explicit insert result must override healthy trans_status');
}
list($event, $output) = run_endpoint('submit_for_approval'); check($event['status'] === 1 && json_decode($output, true)['status'] === 1, 'Submit success');
check($event['context']['data']['no_pengajuan'] === 'PGJ-TEST', 'Generated document metadata');
check(in_array('trans_commit', $event['calls_at_write'], true), 'Audit success must follow commit');
// main has consultant creator resolution that must survive restoration of auditing.
foreach (array(array(), array('cash_source' => true), array('no_consultant_creator' => true), array('no_consultant_name' => true)) as $extra) {
    list($event, $output) = run_endpoint('submit_for_approval', array_merge(array('source_document' => 'DPM-TEST', 'source_category' => 'Direct Payment'), $extra), array('items' => array(array('no_dokumen' => 'DPM-TEST'))));
    check($event['status'] === 1 && json_decode($output, true)['status'] === 1, 'main consultant resolution changed');
}
list($event) = run_endpoint('submit_for_approval', array('companies' => true), array('items' => array(array('no_dokumen' => 'DOC-TEST'), array('no_dokumen' => 'DOC-SECOND'))));
check($event['status'] === 0 && $event['context']['data']['reason'] === 'mixed_companies', 'Company lock');
foreach (array('writer_failure', 'writer_exception') as $failure) {
    foreach (array('submit_for_approval', 'save_request') as $method) {
        list($event, $output, $controller) = run_endpoint($method, array($failure => true));
        check($event['status'] === 1 && in_array('trans_commit', $controller->db->calls, true), 'Writer cannot change business success');
        check((json_decode($output, true)['status'] ?? json_decode($output, true)['save']) == 1, 'Writer changed response');
    }
}
foreach (array('transportasi', 'kasbon', 'expense', 'nonpo', 'periodik', 'direct_payment') as $type) {
    list($event, $output) = run_endpoint('save_request', array(), array('tipe_1' => $type));
    check($event['status'] === 1 && json_decode($output, true)['save'] === true, 'Save type ' . $type);
    check(in_array('trans_commit', $event['calls_at_write'], true), 'Save audit before commit');
    list($event, $output) = run_endpoint('save_request', array('update_failure' => true), array('tipe_1' => $type));
    check($event['status'] === 0 && json_decode($output, true)['save'] === false, 'Source update failure ' . $type);
}
foreach (array('insert_failure', 'transaction_failure', 'trans_begin_failure', 'trans_commit_failure') as $failure) {
    list($event, $output) = run_endpoint('save_request', array($failure => true));
    check($event['status'] === 0 && json_decode($output, true)['save'] === false, 'Save failure ' . $failure);
}
list($event, $output) = run_endpoint('save_request', array('insert_failure' => true, 'status_does_not_fail' => true));
check($event['status'] === 0 && json_decode($output, true)['save'] === false, 'Zero insert ID cannot be success');
$_FILES = array('upload_doc_1' => array('error' => UPLOAD_ERR_OK));
list($event, $output) = run_endpoint('save_request', array('upload_failure' => true));
check($event['status'] === 0 && json_decode($output, true)['save'] === true, 'Partial upload preserves business response');
$_FILES = array();
list($event, $output) = run_endpoint('save_request', array('upload_failure' => true)); check($event['status'] === 1, 'No optional upload is not failure');
list($event, $output) = run_endpoint('save_request', array(), array('status' => array())); check(json_decode($output, true)['save'] === true && $event['context']['data']['processed_count'] === 0, 'Empty legacy selection compatibility');
$many = array('status' => range(1, 101));
for ($i = 1; $i <= 101; $i++) {
    $many['no_doc_' . $i] = str_repeat('文', 200); $many['tipe_' . $i] = 'expense';
    $many['tanggal_' . $i] = '2026-10-10'; $many['admin_charge_' . $i] = '0'; $many['nilai_pph_' . $i] = '0';
}
list($event) = run_endpoint('save_request', array(), $many);
check($event['status'] === 1 && $event['context']['data']['document_count'] === 101 && count($event['context']['data']['document_ids']) === 100, 'Batch metadata preserves counts with bounded IDs');
foreach (array('--denied', '--shutdown') as $mode) {
    $output = shell_exec('"' . PHP_BINARY . '" "' . __FILE__ . '" ' . $mode);
    check(substr_count($output, 'AUDIT:') === 1, 'Actual shutdown must produce one event');
    $event = json_decode(trim(substr($output, strpos($output, 'AUDIT:') + 6)), true);
    check($event['status'] === 0 && $event['context']['data']['outcome'] === 'interrupted', 'Denied/exit fallback');
}
// Constructor remains opt-in; pending endpoints do not inherit a scope.
$events = array(); $scenario = array(); $ci = new Request_payment();
check(!isset($ci->module_audit_recorder) && count($events) === 0, 'Pending endpoint activated prematurely');
echo 'PASS Request Payment batch 1: ' . ($scenarioCount + 3) . " fixture/subprocess scenarios, real controller/recorder and response compatibility\n";
