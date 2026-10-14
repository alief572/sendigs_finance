<?php
// Run standalone with PHP 7.4+: fixture persistence only, no application DB.
define('BASEPATH', __DIR__);
class BF_Model {}
function &get_instance() { return $GLOBALS['ci']; }
function check($condition, $message) { if (!$condition) { throw new RuntimeException($message); } }
function log_message($level, $message) { $GLOBALS['errors'][] = $message; }
set_error_handler(function ($severity, $message, $file, $line) { throw new ErrorException($message, 0, $severity, $file, $line); });
require dirname(__DIR__, 3) . '/helpers/audit_trail_helper.php';
require dirname(__DIR__, 3) . '/libraries/Audit_logger.php';
require dirname(__DIR__, 3) . '/libraries/Module_audit_recorder.php';
require dirname(__DIR__, 3) . '/helpers/app_helper.php';
require dirname(__DIR__, 2) . '/aktifitas/models/Aktifitas_model.php';
class RecorderRouter {
    public function fetch_module() { return 'request_payment'; }
    public function fetch_class() { return 'request_payment'; }
    public function fetch_method() { return 'save_request'; }
}
class RecorderSession {
    public $data = array('username' => 'admin', 'id_user' => 7);
    public function userdata($key) { return $this->data; }
}
class RecorderInput {
    public function ip_address() { return '127.0.0.1'; }
    public function user_agent() { return 'Windows test'; }
}
class RecorderUri { public function segment($key) { return 'request_payment'; } }
class RecorderDb {
    public $rows = array(), $last_query = 'BUSINESS SQL', $insert_id = 17, $transaction = true;
    public function insert($table, $row) { $this->rows[] = array($table, $row); return true; }
}
class RecorderLoader { public function library($name) {} public function model($name) {} }
class RecorderWriter extends Audit_logger {
    public $rows = array(), $fail = false, $throw = false;
    protected function persist($row) {
        if ($this->throw) { throw new RuntimeException('password=DO-NOT-LOG'); }
        if ($this->fail) { return false; }
        $this->rows[] = $row; return true;
    }
}
class RecorderPermission { public function find_by($where) { return (object) array('id_permission' => 29); } }
class RecorderAuth { public function user_id() { return 7; } }
class RecorderActivity extends Aktifitas_model {
    public $rows = array(), $store = true;
    public function __construct() { $this->permissions_model = new RecorderPermission(); $this->auth = new RecorderAuth(); }
    public function find($id) { return $this->rows[$id] ?? false; }
    public function insert($row) { if ($this->store) { $this->rows[$row['idlog']] = $row; } return $this->store; }
}
function fixture() {
    $GLOBALS['errors'] = array();
    $GLOBALS['ci'] = (object) array('router' => new RecorderRouter(), 'session' => new RecorderSession(),
        'input' => new RecorderInput(), 'uri' => new RecorderUri(), 'load' => new RecorderLoader(), 'db' => new RecorderDb());
    $GLOBALS['ci']->audit_logger = new RecorderWriter();
    $GLOBALS['ci']->module_audit_recorder = new Module_audit_recorder();
    return $GLOBALS['ci'];
}
function begin($ci) {
    return $ci->module_audit_recorder->begin('request_payment', 'save_request',
        array('data' => array('no_dokumen' => 'RP-TEST', 'password' => 'DO-NOT-LOG', 'arbitrary' => 'DO-NOT-LOG')),
        array('no_dokumen', 'amount', 'document_ids', 'kode_universal', 'jumlah', 'search'));
}
$child = $argv[1] ?? null;
if ($child) {
    $ci = fixture(); if ($child !== '--idle') { begin($ci); }
    register_shutdown_function(function () use ($ci) { echo json_encode($ci->audit_logger->rows); });
    if ($child === '--exception') { try { throw new RuntimeException('password=DO-NOT-LOG'); } catch (Throwable $error) { exit; } }
    exit;
}
$ci = fixture();
history('Outside scope');
check(count($ci->audit_logger->rows) === 1 && $ci->audit_logger->rows[0]['status'] === null, 'Outside scope history changed');
$activity = new RecorderActivity();
check($activity->simpan_aktifitas('Payment.Save', 'RP-TEST', 'Outside activity', 2, 'UPDATE payments SET amount=12', 1) === true, 'Outside activity return changed');
check(count($ci->audit_logger->rows) === 2, 'Outside activity not audited');

$ci = fixture(); $recorder = $ci->module_audit_recorder;
check(begin($ci) && $recorder->is_active(), 'Scope must begin explicitly');
check(!$recorder->begin('expense', 'save'), 'Nested begin must not replace the active endpoint');
history('First legacy success', array('status' => 1, 'data' => array('amount' => 10, 'password' => 'DO-NOT-LOG')));
history('Second legacy success');
$activity = new RecorderActivity();
$ci->aktifitas_model = $activity;
check(simpan_aktifitas('Payment.Save', 'RP-TEST', 'Legacy activity', 2, "UPDATE payments SET token='DO-NOT-LOG', amount=10", 1) === true, 'Deferred public helper return changed');
check(count($ci->db->rows) === 2 && count($activity->rows) === 1 && !$ci->audit_logger->rows, 'Legacy writes must persist without premature audit inserts');
check($recorder->complete('Pengajuan berhasil disimpan', 1, array('data' => array('amount' => 20, 'document_ids' => array('A','B')))), 'Complete failed');
check(!$recorder->complete('Duplicate', 1), 'Complete must not write twice');
history('Late legacy helper');
check(count($ci->audit_logger->rows) === 1 && count($ci->db->rows) === 3, 'Late helper duplicated audit or lost legacy write');
$row = $ci->audit_logger->rows[0]; $data = json_decode($row['data'], true);
check($row['module'] === 'request_payment' && $row['methode'] === 'save_request' && $row['status'] === '1', 'Endpoint attribution/status wrong');
check($data['amount'] === 20 && $data['legacy_event_count'] === 3 && $data['kode_universal'] === 'RP-TEST', 'Metadata precedence or legacy context wrong');
check(strpos(json_encode($row), 'DO-NOT-LOG') === false && !isset($data['password'], $data['arbitrary']), 'Allowlist/SQL masking leaked a secret');
check($ci->db->last_query === 'BUSINESS SQL' && $ci->db->insert_id === 17 && $ci->db->transaction, 'Audit changed business connection state');

foreach (array(0, 1, null) as $status) {
    $ci = fixture(); begin($ci);
    history('Legacy says success', array('status' => 1));
    check($ci->module_audit_recorder->complete('Hasil akhir', $status), 'Valid final status rejected');
    check($ci->audit_logger->rows[0]['status'] === ($status === null ? null : (string) $status), 'Legacy status incorrectly overrode final result');
}
$ci = fixture(); begin($ci); $ci->session->data = array();
check($ci->module_audit_recorder->complete('Session removed', 0), 'Identity snapshot write failed');
check($ci->audit_logger->rows[0]['username'] === 'admin' && $ci->audit_logger->rows[0]['created_by'] === 7, 'Original actor lost after session change');

$ci = fixture(); $recorder = $ci->module_audit_recorder;
check(!$recorder->begin('audit_trail', 'data'), 'Audit Trail viewer must not activate recorder');
check(!$recorder->begin('', 'save'), 'Invalid module accepted');
check(!$recorder->begin('request_payment', 'save', array('username' => 'impersonated')), 'Identity override accepted');
check(!$recorder->begin('request_payment', 'save', array(), array('password')), 'Sensitive allowlist accepted');
check(!$recorder->begin('request_payment', 'save', array(), array('no_rekening')), 'Private account allowlist accepted');
check(!$recorder->is_active(), 'Invalid begin armed fallback');
check(begin($ci), 'Valid begin after invalid inputs failed');
check(!$recorder->complete('Invalid status', true), 'Boolean status accepted');
check(!$recorder->complete('Nested payload', 1, array('data' => array('document_ids' => array(array('password' => 'DO-NOT-LOG'))))), 'Nested request accepted');
check(!$recorder->complete('Oversized list', 1, array('data' => array('document_ids' => array_fill(0, 101, 'ID')))), 'Oversized list accepted');
check(!$recorder->complete('Impersonation', 1, array('username' => 'other')), 'Completion identity override accepted');
check($recorder->complete('Validated', 0), 'Scope lost after rejected completion');

foreach (array('fail','throw') as $mode) {
    $ci = fixture(); begin($ci); $ci->audit_logger->$mode = true;
    check(!$ci->module_audit_recorder->complete('Business completed', 1), 'Writer failure must return false');
    $ci->module_audit_recorder->finish_interrupted();
    check(!$ci->audit_logger->rows && !$ci->module_audit_recorder->is_active(), 'Writer failure was retried as business interruption');
    check($GLOBALS['errors'] && strpos(implode(' ', $GLOBALS['errors']), 'DO-NOT-LOG') === false, 'Sensitive error logging');
}
$ci = fixture(); begin($ci); $activity = new RecorderActivity(); $activity->store = false;
check($activity->simpan_aktifitas('Payment.Save', 'RP-TEST', 'Legacy storage failure', 2, '', 1) === false, 'Legacy storage failure return changed');
check($ci->module_audit_recorder->complete('Business failed', 0), 'Legacy failure prevented final audit');

foreach (array('--exit','--exception') as $mode) {
    $output = array(); $code = 0;
    exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__FILE__) . ' ' . $mode, $output, $code);
    $rows = json_decode(implode("\n", $output), true);
    check($code === 0 && count($rows) === 1 && $rows[0]['status'] === '0', 'Actual shutdown fallback failed');
    check(json_decode($rows[0]['data'], true)['outcome'] === 'interrupted', 'Fallback outcome missing');
    check(strpos(json_encode($rows), 'DO-NOT-LOG') === false, 'Fallback leaked exception/request secrets');
}
$output = array(); $code = 0;
exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__FILE__) . ' --idle', $output, $code);
check($code === 0 && json_decode(implode("\n", $output), true) === array(), 'Uninstrumented request emitted a false failure');
echo "PASS: explicit scopes, legacy compatibility, one final event, truthful statuses, metadata allowlist, actor snapshot, isolated writer failures and real shutdown fallback\n";
