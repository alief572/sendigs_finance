<?php
if (PHP_SAPI !== 'cli-server' || !getenv('EXPENSE_UPLOAD_TEST_ROOT')) { http_response_code(404); exit; }
define('BASEPATH', __DIR__);
define('APPPATH', dirname(__DIR__, 3) . '/');
define('DBACC', 'fixture_accounting');
$case = isset($_GET['case']) ? $_GET['case'] : '';
if (!preg_match('/^case[0-9]+$/', $case)) { http_response_code(400); exit; }
define('FCPATH', getenv('EXPENSE_UPLOAD_TEST_ROOT') . '/' . $case . '/');
mkdir(FCPATH);
if (!empty($_GET['existing'])) {
    mkdir(FCPATH . 'assets'); mkdir(FCPATH . 'assets/expense');
    file_put_contents(FCPATH . 'assets/expense/existing.pdf', 'existing attachment');
}
if (!empty($_GET['blocked'])) {
    if (!is_dir(FCPATH . 'assets')) { mkdir(FCPATH . 'assets'); }
    file_put_contents(FCPATH . 'assets/expense', 'blocked destination');
}
class Admin_Controller {}
function has_permission($name) { return true; }
function simpan_aktifitas() {}
function &get_instance() { global $controller; return $controller; }
function log_message($level, $message) {}
$events = array();
function audit_log($message, $status, $context) {
    global $events, $controller;
    $events[] = array('status' => $status, 'context' => $context, 'commits_at_write' => $controller->db->commits);
    if (!empty($_GET['writer_exception'])) { throw new RuntimeException('PRIVATE_WRITER_FAILURE'); }
    return empty($_GET['writer_failure']);
}
require APPPATH . 'libraries/Module_audit_recorder.php';
require dirname(__DIR__) . '/controllers/Expense.php';
require dirname(__DIR__, 3) . '/helpers/kasbon_attachment_helper.php';
set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) { return false; }
    throw new ErrorException($message, 0, $severity, $file, $line);
});
class UploadRequestResult {
    public function row() { return (object) array('id' => 10, 'no_doc' => 'EXP-TEST', 'status' => 3,
        'coa_bank' => '1101', 'no_perkiraan' => '1101', 'doc_file' => 'existing.pdf'); }
    public function row_array() { return array('doc_file' => 'existing.pdf', 'doc_file_2' => '', 'no_perkiraan' => '1101'); }
    public function result() { return array(); }
    public function result_array() { return array(); }
    public function num_rows() { return 0; }
}
class UploadRequestDb {
    public $commits = 0;
    public $rollbacks = 0;
    public $expenseUpdate = array();
    public function __call($name, $args) { return $this; }
    public function get() { return new UploadRequestResult(); }
    public function get_where($table, $where) { return new UploadRequestResult(); }
    public function query($sql) { return new UploadRequestResult(); }
    public function table_exists($table) { return true; }
    public function trans_begin() {}
    public function trans_status() { return empty($_GET['transaction_fail']); }
    public function trans_commit() { $this->commits++; return empty($_GET['commit_fail']); }
    public function trans_rollback() { $this->rollbacks++; }
    public function insert($table, $data) { return empty($_GET['insert_fail']); }
    public function insert_id() { return 11; }
    public function update($table, $data, $where) { if ($table === 'tr_expense') { $this->expenseUpdate = $data; } return true; }
    public function error() { return array('message' => 'fixture DB error'); }
}
class UploadRequestInput { public function post($key = null) { return $key === null ? $_POST : (isset($_POST[$key]) ? $_POST[$key] : null); } }
class UploadRequestAuth { public function user_name() { return 'alice'; } public function user_id() { return '42'; } }
class UploadRequestModel {
    public function GetAutoGenerate($type) { return 'EXP-TEST'; }
    public function dataSave($table, $data) { return 10; }
    public function dataUpdate($table, $data, $where) { return true; }
}
class UploadRequestLoad { public function library($name, $config = array()) {} }
class UploadRequestLibrary {
    private $config;
    private $data;
    private $calls = 0;
    public function initialize($config) { $this->config = $config; }
    public function do_upload($field) {
        $this->calls++;
        if (!empty($_GET['fail_at']) && $this->calls === (int) $_GET['fail_at']) { return false; }
        $filename = bin2hex(random_bytes(8)) . '.pdf';
        if (!move_uploaded_file($_FILES[$field]['tmp_name'], $this->config['upload_path'] . $filename)) { return false; }
        $this->data = array('file_name' => $filename);
        return true;
    }
    public function data() { return $this->data; }
}
$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : '';
if (!in_array($endpoint, array('save', 'kasbon_save', 'transport_save', 'transport_req_save', 'save_pengembalian_dana'), true)) { http_response_code(400); exit; }
$controller = (new ReflectionClass('Expense'))->newInstanceWithoutConstructor();
$controller->db = new UploadRequestDb();
$controller->input = new UploadRequestInput();
$controller->auth = new UploadRequestAuth();
$controller->All_model = new UploadRequestModel();
$controller->load = new UploadRequestLoad();
$controller->upload = new UploadRequestLibrary();
$controller->module_audit_recorder = new Module_audit_recorder();
ob_start(); $controller->$endpoint(); $body = ob_get_clean();
$controller->module_audit_recorder->finish_interrupted();
$files = array();
foreach (array('assets/expense', 'assets/expense_bukti_pengembalian') as $folder) {
    foreach (glob(FCPATH . $folder . '/*') as $path) {
        if (is_file($path)) { $files[$folder . '/' . basename($path)] = array('content' => file_get_contents($path), 'mode' => fileperms($path) & 07777, 'group' => filegroup($path)); }
    }
}
header('Content-Type: application/json');
echo json_encode(array('response' => json_decode($body, true), 'raw' => $body, 'files' => $files,
    'events' => $events, 'commits' => $controller->db->commits,
    'rollbacks' => $controller->db->rollbacks, 'expense_update' => $controller->db->expenseUpdate));
