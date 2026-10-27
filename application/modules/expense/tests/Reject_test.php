<?php
// Standalone regression test; no application bootstrap or real database writes.
define('BASEPATH', __DIR__);
class Admin_Controller {}
require dirname(__DIR__) . '/controllers/Expense.php';

class RejectTestInput
{
    public $values;
    public function post($key) { return isset($this->values[$key]) ? $this->values[$key] : null; }
}
class RejectTestAuth
{
    public function user_name() { return 'tester'; }
}
class RejectTestDb
{
    public $db_debug = true;
    public $fields = array();
    public $data = array();
    public $committed = false;
    public $rolledBack = false;
    public $failUpdate = false;
    public $failTransaction = false;
    public function trans_begin() {}
    public function field_exists($field, $table) { return in_array($field, $this->fields, true); }
    public function where($field, $value) {}
    public function update($table, $data)
    {
        if ($this->db_debug) { throw new Exception('Database debugging must be disabled during update'); }
        $this->data = $data;
        return !$this->failUpdate;
    }
    public function error() { return array('message' => 'Simulated database failure'); }
    public function trans_status() { return !$this->failTransaction; }
    public function trans_commit() { $this->committed = true; }
    public function trans_rollback() { $this->rolledBack = true; }
}
function checkReject($condition, $message)
{
    if (!$condition) { throw new Exception($message); }
}

$cases = array(
    array('transport without audit columns', 'tr_transport_req', array(), false, false, true),
    array('expense with audit columns', 'tr_expense', array('rejected_by', 'rejected_on'), false, false, true),
    array('kasbon without audit columns', 'tr_kasbon', array(), false, false, true),
    array('partial audit migration', 'tr_transport_req', array('rejected_by'), false, false, true),
    array('update failure', 'tr_transport_req', array(), true, false, false),
    array('transaction failure', 'tr_transport_req', array(), false, true, false),
    array('invalid table', 'users', array(), false, false, false),
    array('missing table', '', array(), false, false, false)
);
$results = array();
foreach ($cases as $case) {
    list($name, $table, $fields, $failUpdate, $failTransaction, $expected) = $case;
    $controller = (new ReflectionClass('Expense'))->newInstanceWithoutConstructor();
    $controller->input = new RejectTestInput();
    $controller->input->values = array('id' => '339', 'table' => $table, 'reason' => 'Dokumen perlu diperbaiki');
    $controller->auth = new RejectTestAuth();
    $controller->db = new RejectTestDb();
    $controller->db->fields = $fields;
    $controller->db->failUpdate = $failUpdate;
    $controller->db->failTransaction = $failTransaction;
    http_response_code(200);
    ob_start();
    $controller->reject();
    $response = json_decode(ob_get_clean(), true);
    checkReject(is_array($response) && $response['save'] === $expected, $name . ': unexpected JSON result');
    checkReject($controller->db->db_debug === true, $name . ': debugging was not restored');
    if ($expected) {
        checkReject($controller->db->committed && !$controller->db->rolledBack, $name . ': did not commit');
        checkReject($controller->db->data['status'] === 9, $name . ': incorrect status');
        foreach (array('reject_reason', 'reject_reason_finance', 'st_reject') as $field) {
            checkReject($controller->db->data[$field] === 'Dokumen perlu diperbaiki', $name . ': missing rejection reason');
        }
        checkReject($controller->db->data['sts_finance'] === '0' && $controller->db->data['app_finance_date'] === null, $name . ': finance approval was not reset');
        foreach (array('rejected_by', 'rejected_on') as $field) {
            checkReject(array_key_exists($field, $controller->db->data) === in_array($field, $fields, true), $name . ': unsupported audit column');
        }
    } else {
        checkReject($controller->db->rolledBack && !$controller->db->committed, $name . ': did not roll back');
        checkReject(http_response_code() === 400 && !empty($response['message']), $name . ': missing error response');
        if ($table === 'users' || $table === '') {
            checkReject(empty($controller->db->data), $name . ': attempted update on invalid table');
        }
    }
    $results[] = 'PASS: ' . $name;
}
echo implode(PHP_EOL, $results) . PHP_EOL;
