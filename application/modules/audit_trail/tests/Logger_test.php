<?php
define('BASEPATH', __DIR__);
set_error_handler(function ($severity, $message) { throw new ErrorException($message, 0, $severity); });
require dirname(__DIR__, 3) . '/libraries/Audit_logger.php';
require dirname(__DIR__, 3) . '/helpers/audit_trail_helper.php';

function check($condition, $message) {
    if (!$condition) { throw new RuntimeException($message); }
}
function log_message($level, $message) { $GLOBALS['errors'][] = $message; }
function &get_instance() { return $GLOBALS['ci']; }
class AuditTestRouter {
    public function fetch_module() { return 'payments'; }
    public function fetch_class() { return 'Payments'; }
    public function fetch_method() { return 'save'; }
}
class AuditTestSession {
    public function userdata($key) { return array('username' => 'admin', 'id_user' => 7); }
}
class AuditTestInput {
    public function ip_address() { return '::1'; }
    public function user_agent() { return 'Mozilla/5.0 (Windows NT 10.0)'; }
}
class AuditTestLoader {
    public function library($name) {}
}
class MemoryAuditLogger extends Audit_logger {
    public $rows = array();
    public $fail = false;
    protected function persist($row) {
        if ($this->fail) { throw new RuntimeException('password=DO_NOT_LOG'); }
        $this->rows[] = $row;
        return true;
    }
}
$GLOBALS['ci'] = (object) array('router' => new AuditTestRouter(), 'session' => new AuditTestSession(),
    'input' => new AuditTestInput(), 'load' => new AuditTestLoader());
$logger = new MemoryAuditLogger();
$GLOBALS['ci']->audit_logger = $logger;
check(audit_log('Saved', 1, array('data' => array('id' => 'DOC-7', 'nested' => array('password' => 'DO_NOT_LOG', 'otp' => '123456', 'session' => array('user' => 'secret'))), 'sql' => "UPDATE users SET password='DO_NOT_LOG', amount=123 WHERE id=7 /* secret */")), 'Valid activity must be stored');
$row = $logger->rows[0];
check($row['module'] === 'payments' && $row['methode'] === 'save' && $row['created_by'] === 7 && $row['username'] === 'admin', 'Automatic context missing');
check($row['ip_address'] === '127.0.0.1' && $row['platform'] === 'Windows', 'Device metadata missing');
check(strpos($row['data'], 'DO_NOT_LOG') === false && strpos($row['data'], '123456') === false && strpos($row['sql'], 'DO_NOT_LOG') === false && strpos($row['sql'], '123') === false && strpos($row['sql'], 'secret') === false, 'Secrets or SQL literals leaked');
check(strpos($row['data'], 'DOC-7') !== false, 'Non-sensitive context lost');
check(strpos($row['sql'], 'UPDATE users SET password=?') === 0, 'SQL structure must remain useful');
check(abs(strtotime($row['created_at'] . ' +0700') - time()) < 5, 'Timestamp must be WIB');
check(audit_log('Unknown') && audit_log('Failed', 0, array('module' => 'AUTH', 'methode' => 'LOGIN', 'username' => 'guest', 'created_by' => null)), 'Null and failed status must be supported');
check($logger->rows[1]['status'] === null && $logger->rows[2]['status'] === '0' && $logger->rows[2]['created_by'] === null, 'Status or identity override changed');
check(count(array_unique(array_column($logger->rows, 'id'))) === 3, 'IDs must be unique');
check(audit_log('Unicode 😀', null, array('data' => array('shipping_address' => 'Jakarta', 'pin' => '9999', 'json' => '{"password":"DO_NOT_LOG"}'))), 'Unicode or structured payload rejected');
$unicodeRow = end($logger->rows);
check(strpos($unicodeRow['message'], '😀') === false && strpos($unicodeRow['data'], 'Jakarta') !== false && strpos($unicodeRow['data'], '9999') === false && strpos($unicodeRow['data'], 'DO_NOT_LOG') === false, 'Encoding or nested JSON masking incorrect');
foreach (array(array('msg', 2, array()), array('', 1, array()), array('msg', 1, array('module' => array())), array('msg', 1, array('username' => str_repeat('x', 31))), array('msg', 1, array('data' => 'not json')), array('msg', 1, array('created_by' => -1)), array('msg', 1, array('created_by' => true))) as $args) {
    check(call_user_func_array('audit_log', $args) === false, 'Invalid input accepted');
}
$logger->fail = true;
check(audit_log('Failure', 1) === false, 'Writer failure must be non-fatal');
check(strpos(implode('', $GLOBALS['errors']), 'DO_NOT_LOG') === false, 'Error log leaked exception contents');
$GLOBALS['ci']->router = new class extends AuditTestRouter { public function fetch_module() { return 'Audit_trail'; } };
$logger->fail = false;
$before = count($logger->rows);
check(audit_log('View audit trail') && count($logger->rows) === $before, 'Viewer must not log itself');
echo "PASS: audit helper metadata, masking, statuses, validation, isolation and failure handling\n";
