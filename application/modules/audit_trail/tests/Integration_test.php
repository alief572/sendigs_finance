<?php
// This test ONLY connects to the temporary localhost server; never loads app DB config.
if (!isset($argv[1]) || $argv[1] !== '--isolated-local-db') {
    throw new RuntimeException('Explicit --isolated-local-db flag required for disposable localhost fixtures.');
}
define('BASEPATH', dirname(__DIR__, 4) . '/system/');
define('APPPATH', dirname(__DIR__, 3) . '/');
function check($condition, $message) { if (!$condition) { throw new RuntimeException($message); } }
function log_message($level, $message) { $GLOBALS['errors'][] = $message; }
function show_error($message) { throw new RuntimeException($message); }
function &get_instance() { return $GLOBALS['ci']; }
function load_class($name, $directory) { return null; }
function is_php($version) { return version_compare(PHP_VERSION, $version, '>='); }
require BASEPATH . 'database/DB.php';
require APPPATH . 'libraries/Audit_logger.php';
require APPPATH . 'helpers/audit_trail_helper.php';
class CI_Model { public function __construct() {} }
require dirname(__DIR__) . '/models/Audit_trail_model.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$connection = new mysqli('127.0.0.1', 'root', 'audit_test_local', '', 33318);
$connection->query('CREATE DATABASE IF NOT EXISTS audit_trail_test');
$connection->select_db('audit_trail_test');
// The server/database is exclusively created for this test run.
foreach (array('menus', 'permissions', 'logs', 'business_items') as $table) { $connection->query('DROP TABLE IF EXISTS `' . $table . '`'); }
$connection->query("CREATE TABLE menus (id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(100), link VARCHAR(255), icon VARCHAR(45), target VARCHAR(10), group_menu INT, parent_id INT, permission_id INT, status TINYINT, `order` INT, created_on DATETIME, pusat INT DEFAULT 0) ENGINE=InnoDB");
$connection->query('CREATE TABLE permissions (id_permission INT AUTO_INCREMENT PRIMARY KEY, nm_permission VARCHAR(45), ket VARCHAR(255), id_menu INT, nm_menu VARCHAR(255), created_on DATETIME) ENGINE=InnoDB');
$connection->query('CREATE TABLE logs (id VARCHAR(50) PRIMARY KEY, module VARCHAR(50), methode VARCHAR(50), username VARBINARY(30), data TEXT, `sql` TEXT, ip_address VARCHAR(15), agent TEXT, platform VARCHAR(50), status CHAR(10), message TEXT, created_at DATETIME, created_by INT) ENGINE=InnoDB DEFAULT CHARSET=utf8');
$connection->query('CREATE TABLE business_items (id INT AUTO_INCREMENT PRIMARY KEY, label VARCHAR(100)) ENGINE=InnoDB');
$connection->query("INSERT INTO menus (id,title,link,parent_id,group_menu,`order`) VALUES (100,'Settings','#',0,1,99),(110,'Users','users/setting',100,1,4),(111,'Menus','menus',100,1,5),(112,'Permissions','permissions',100,1,6),(113,'Setup Stock','setup_stock',100,1,7)");
function migration($connection, $file) {
    $connection->multi_query(file_get_contents($file));
    do {
        if ($result = $connection->store_result()) { $result->free(); }
        if (!$connection->more_results()) { break; }
    } while ($connection->next_result());
}
foreach (array('001_register_audit_trail.sql', '002_add_logs_indexes.sql') as $file) {
    $path = dirname(__DIR__) . '/migrations/' . $file;
    migration($connection, $path); migration($connection, $path);
}
$menu = $connection->query("SELECT * FROM menus WHERE link='audit_trail'")->fetch_assoc();
check((int) $menu['parent_id'] === 100 && (int) $menu['order'] === 7, 'Menu must discover parent IDs and follow Permissions');
check((int) $connection->query("SELECT COUNT(*) FROM menus WHERE link='audit_trail'")->fetch_row()[0] === 1, 'Repeated migration duplicated menu');
check((int) $connection->query("SELECT COUNT(*) FROM permissions WHERE nm_permission='Audit_Trail.View'")->fetch_row()[0] === 1, 'Repeated migration duplicated permission');
check((int) $connection->query('SELECT `order` FROM menus WHERE id=113')->fetch_row()[0] === 8, 'Existing sibling moved more than once');
check((int) $connection->query("SELECT COUNT(DISTINCT index_name) FROM information_schema.statistics WHERE table_schema='audit_trail_test' AND table_name='logs'")->fetch_row()[0] === 4, 'Indexes missing or duplicated');
class IntegrationRouter { public function fetch_module() { return 'test'; } public function fetch_class() { return 'test'; } public function fetch_method() { return 'save'; } }
class IntegrationInput { public function ip_address() { return '127.0.0.1'; } public function user_agent() { return 'Windows Test'; } }
class IntegrationSession { public function userdata($key) { return array('username' => 'admin', 'id_user' => 7); } }
class IntegrationLoader { public function library($name) {} }
$main = DB(array('dbdriver' => 'mysqli', 'hostname' => '127.0.0.1', 'port' => 33318, 'username' => 'root', 'password' => 'audit_test_local', 'database' => 'audit_trail_test', 'char_set' => 'utf8', 'db_debug' => false), true);
$GLOBALS['ci'] = (object) array('db' => $main, 'router' => new IntegrationRouter(), 'input' => new IntegrationInput(), 'session' => new IntegrationSession(), 'load' => new IntegrationLoader());
$GLOBALS['ci']->audit_logger = new Audit_logger();
$main->trans_begin(); $main->insert('business_items', array('label' => 'rollback me'));
$lastQuery = $main->last_query(); $lastId = $main->insert_id();
check(audit_log('Attempted activity', null, array('data' => array('password' => 'DO_NOT_STORE', 'doc' => 'DOC-7'), 'sql' => $lastQuery)), 'Real logger insert failed');
check($main->last_query() === $lastQuery && $main->insert_id() === $lastId && $main->trans_status(), 'Logger altered the business connection');
$main->trans_rollback();
check((int) $connection->query('SELECT COUNT(*) FROM logs')->fetch_row()[0] === 1 && (int) $connection->query('SELECT COUNT(*) FROM business_items')->fetch_row()[0] === 0, 'Audit connection shared the business transaction');
$stored = $connection->query('SELECT * FROM logs')->fetch_assoc();
check(strpos($stored['data'], 'DO_NOT_STORE') === false && strpos($stored['sql'], 'rollback me') === false, 'Real persisted row contains sensitive values');
$connection->query('RENAME TABLE logs TO logs_hidden');
$main->trans_begin(); $main->insert('business_items', array('label' => 'commit despite logger failure'));
check(audit_log('Writer unavailable', 1) === false && $main->trans_status(), 'Failed logger damaged the main transaction');
$main->trans_commit(); $connection->query('RENAME TABLE logs_hidden TO logs');
check((int) $connection->query('SELECT COUNT(*) FROM business_items')->fetch_row()[0] === 1, 'Main transaction did not commit');
check(strpos(implode('', $GLOBALS['errors']), 'DO_NOT_STORE') === false, 'Failure logs exposed data');
$model = new Audit_trail_model(); $model->db = $main;
check($model->page($model->options(array('date_from' => '', 'date_to' => '', 'search' => 'admin')))['recordsFiltered'] === 1, 'MySQL varbinary username search or protected SQL column failed');
$main->query('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ'); $main->trans_begin();
$export = $model->export_rows($model->options(array('date_from' => '', 'date_to' => '')), 1);
$export->rewind();
$connection->query("INSERT INTO logs (id,module,methode,username,status,message,created_at) VALUES ('concurrent','AUTH','LOGIN','bob','1','Added during export','2099-01-01 00:00:00')");
$exported = iterator_to_array($export); $main->trans_commit();
check(count($exported) === 1 && $exported[0]['id'] === $stored['id'], 'Concurrent activity shifted or duplicated export pages');
echo "PASS: isolated MariaDB migrations are repeatable; real logger connection preserves business transaction and query state\n";
