<?php
define('BASEPATH', dirname(__DIR__, 4) . '/system/');
define('APPPATH', dirname(__DIR__, 3) . '/');
set_error_handler(function ($severity, $message) { throw new ErrorException($message, 0, $severity); });
function log_message($level, $message) {}
function show_error($message) { throw new RuntimeException($message); }
function load_class($name, $directory) { return null; }
class CI_Model { public function __construct() {} }
require BASEPATH . 'database/DB.php';
require dirname(__DIR__) . '/models/Audit_trail_model.php';
function check($condition, $message) { if (!$condition) { throw new RuntimeException($message); } }
$db = DB(array('dbdriver' => 'pdo', 'subdriver' => 'sqlite', 'dsn' => 'sqlite::memory:', 'db_debug' => false), true);
$db->query('CREATE TABLE logs (id TEXT PRIMARY KEY, module TEXT, methode TEXT, username TEXT, data TEXT, sql TEXT, ip_address TEXT, agent TEXT, platform TEXT, status TEXT, message TEXT, created_at TEXT, created_by INTEGER)');
$model = new Audit_trail_model();
$model->db = $db;
foreach (array(
    array('a', 'AUTH', 'LOGIN', 'alice', '1', '2026-10-01 00:00:00', 'Login successful'),
    array('b', 'AUTH', 'LOGIN', 'bob', '0', '2026-10-10 23:59:59', 'Login failed'),
    array('c', 'payments', 'save', 'alice', null, '2026-10-10 12:00:00', 'DOC-123'),
    array('d', 'payments', 'save', 'bob', 'legacy', '2026-10-11 00:00:00', 'Next day'),
    array('e', 'AUTH', 'LOGIN', 'alice', '1', '2026-09-30 23:59:59', 'Previous month')
) as $entry) {
    $db->insert('logs', array_combine(array('id', 'module', 'methode', 'username', 'status', 'created_at', 'message'), $entry));
}
$options = $model->options(array('date_from' => '2026-10-01', 'date_to' => '2026-10-10', 'length' => 2));
$page = $model->page($options);
check($page['recordsTotal'] === 5 && $page['recordsFiltered'] === 3 && array_column($page['data'], 'id') === array('b', 'c'), 'Inclusive range, counts, latest sorting or pagination wrong');
$options['start'] = 2;
check(array_column($model->page($options)['data'], 'id') === array('a'), 'Second page incorrect');
$options['module'] = 'AUTH'; $options['status'] = '0'; $options['search'] = 'bob'; $options['start'] = 0;
check(array_column($model->page($options)['data'], 'id') === array('b'), 'Combined filters incorrect');
$options['search'] = "bob' OR 1=1 --";
check($model->page($options)['recordsFiltered'] === 0, 'Search injection altered results');
$unknown = $model->options(array('date_from' => '', 'date_to' => '', 'status' => 'unknown'));
check($model->page($unknown)['recordsFiltered'] === 2, 'Null and unsupported statuses must be unknown');
check($model->detail('missing') === null && $model->detail('a')['username'] === 'alice', 'Detail lookup incorrect');
$export = iterator_to_array($model->export_rows($model->options(array('date_from' => '2026-10-01', 'date_to' => '2026-10-10', 'length' => 1)), 2));
check(array_column($export, 'id') === array('b', 'c', 'a'), 'Export must include all matching pages');
$sorted = $model->options(array('date_from' => '', 'date_to' => '', 'order' => array(array('column' => 2, 'dir' => 'asc'))));
check($model->page($sorted)['data'][0]['username'] === 'alice', 'Allowed sorting not applied');
foreach (array(array('date_from' => '2026-02-30'), array('date_from' => '2026-10-11', 'date_to' => '2026-10-01'), array('status' => 'garbage'), array('module' => array()), array('search' => array('value' => array()))) as $invalid) {
    try { $model->options($invalid); throw new RuntimeException('Invalid filter accepted'); }
    catch (InvalidArgumentException $expected) {}
}
check(Audit_trail_model::csv_cell('=SUM(A1:A2)') === "'=SUM(A1:A2)" && Audit_trail_model::csv_cell("\t+cmd") === "'\t+cmd", 'CSV formulas not neutralized');
$db->query('DELETE FROM logs');
check($model->page($options)['data'] === array(), 'Empty table must be supported');
echo "PASS: real SQLite query builder filters, pagination, sorting, detail, export and input safety\n";
