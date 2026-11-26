<?php
define('BASEPATH', __DIR__);
class BF_Model {}
require dirname(__DIR__, 3) . '/helpers/audit_trail_helper.php';
require dirname(__DIR__, 3) . '/helpers/app_helper.php';
require dirname(__DIR__, 2) . '/aktifitas/models/Aktifitas_model.php';
function &get_instance() { return $GLOBALS['ci']; }
function check($condition, $message) { if (!$condition) { throw new RuntimeException($message); } }
function audit_log($message, $status = null, $context = array()) { $GLOBALS['events'][] = array($message, $status, $context); return false; }
class LegacyInput { public function ip_address() { return '127.0.0.1'; } }
class LegacyUri { public function segment($number) { return 'menus'; } }
class LegacySession { public function userdata($key) { return array('username' => 'admin'); } }
class LegacyDb {
    public $rows = array();
    public function insert($table, $row) { $this->rows[] = array($table, $row); return true; }
}
class LegacyPermission { public function find_by($where) { return (object) array('id_permission' => 29); } }
class LegacyAuth { public function user_id() { return 7; } }
class LegacyActivity extends Aktifitas_model {
    private $rows = array();
    public function __construct() { $this->permissions_model = new LegacyPermission(); $this->auth = new LegacyAuth(); }
    public function find($id) { return isset($this->rows[$id]) ? $this->rows[$id] : false; }
    public function insert($row) { $this->rows[$row['idlog']] = $row; return $row['idlog']; }
}
$GLOBALS['ci'] = (object) array('session' => new LegacySession(), 'uri' => new LegacyUri(), 'input' => new LegacyInput(), 'db' => new LegacyDb());
$GLOBALS['events'] = array();
history('View menus');
check($GLOBALS['ci']->db->rows[0][0] === 'histories' && $GLOBALS['events'][0][1] === null, 'Legacy history destination or unknown status changed');
history('Saved', array('status' => 1, 'data' => array('id' => 1)));
check($GLOBALS['events'][1][1] === 1 && !isset($GLOBALS['events'][1][2]['status']), 'Explicit history context not forwarded correctly');
$before = count($GLOBALS['events']); history('Login'); history('Logout');
check(count($GLOBALS['events']) === $before && count($GLOBALS['ci']->db->rows) === 4, 'Legacy authentication history must stay without duplicate audit events');
$activity = new LegacyActivity();
check($activity->simpan_aktifitas('Menus.Add', 'DOC-1', 'Saved menu', 1, 'INSERT INTO menus VALUES (1)', 1) === true, 'Audit writer failure changed legacy return');
$last = end($GLOBALS['events']);
check($last[1] === 1 && $last[2]['data']['kode_universal'] === 'DOC-1' && $last[2]['sql'] === 'INSERT INTO menus VALUES (1)', 'Activity metadata missing');
check($activity->simpan_aktifitas() === false, 'Legacy invalid parameters changed');
echo "PASS: legacy histories and activity logging retain destinations and return values\n";
