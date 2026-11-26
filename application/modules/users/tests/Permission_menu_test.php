<?php
define('BASEPATH', __DIR__);
class Admin_Controller {}
class BF_Model {}
require dirname(__DIR__) . '/controllers/Setting.php';
require dirname(__DIR__) . '/models/Permissions_model.php';
function lang($key) { return $key; }
function site_url($path = '') { return '/' . $path; }
function form_open($path, $attributes = array()) { return '<form>'; }
function form_close() { return '</form>'; }
function form_hidden($name, $value) { return ''; }
$checks = 0;
function permissionCheck($ok, $message) {
    global $checks; $checks++;
    if (!$ok) { throw new RuntimeException($message); }
}
class PermissionMenuResult {
    private $rows;
    public function __construct($rows) { $this->rows = $rows; }
    public function result_array() { return $this->rows; }
}
class PermissionMenuDb {
    public $roots;
    public $details;
    public $actions;
    public function __call($method, $args) { return $this; }
    public function get_where($table, $where) {
        return new PermissionMenuResult(array_key_exists('id_menu !=', $where) ? $this->actions :
            (isset($where['parent_id']) ? $this->roots : $this->details));
    }
}
class PermissionMenuUsers {
    public $role = array();
    public $direct = array();
    private $roleQuery = false;
    public function find($id) { return (object) array('id_user' => $id, 'deleted' => 0, 'username' => 'alief', 'nm_lengkap' => 'Alief'); }
    public function __call($method, $args) {
        if ($method === 'select') { $this->roleQuery = false; }
        if ($method === 'join' && $args[0] === 'group_permissions') { $this->roleQuery = true; }
        return $this;
    }
    public function find_all() { return $this->roleQuery ? $this->role : $this->direct; }
}
class PermissionMenuTemplate {
    public $vars = array();
    public function set($key, $value) { $this->vars[$key] = $value; }
    public function __call($name, $args) { return $this; }
}
class PermissionMenuAuth { public function restrict($permission) { permissionCheck($permission === 'Users.Manage', 'Permission form remains restricted'); } }
class PermissionMenuInput { public $ids; public function post($key) { return $this->ids; } }
class PermissionMenuView {
    public $uri;
    public function render($variables) {
        $this->uri = new class { public function uri_string() { return 'users/setting/permission/206'; } };
        extract($variables);
        ob_start(); include dirname(__DIR__) . '/views/user_permissions.php'; return ob_get_clean();
    }
}
class PermissionMenuPersistence {
    public $saved = array();
    public function delete_where($where) { permissionCheck($where === array('id_user' => 206), 'Save targets selected user only'); return true; }
    public function insert_batch($rows) { $this->saved = $rows; return count($rows); }
}
function permissionMenu($id, $parent, $name, $permission) {
    return array('id' => $id, 'parent_id' => $parent, 'nama_menu' => $name, 'nm_permission' => $permission);
}
$setting = (new ReflectionClass('Setting'))->newInstanceWithoutConstructor();
$setting->db = new PermissionMenuDb();
$setting->db->roots = array(permissionMenu(298, 0, 'Pengeluaran Non Pembelian', 'Pengeluaran.View'));
$setting->db->details = array(permissionMenu(282, 298, 'Expense', 'Expense.View'), permissionMenu(279, 282, 'Expense', 'Expense.View'));
$setting->db->actions = array();
foreach (array('View', 'Add', 'Manage', 'Delete') as $index => $action) {
    $setting->db->actions[] = array('id_permission' => 955 + $index, 'id_menu' => 296, 'nm_permission' => 'Expense.' . $action);
}
$setting->users_model = new PermissionMenuUsers();
$setting->permissions_model = (new ReflectionClass('Permissions_model'))->newInstanceWithoutConstructor();
$setting->template = new PermissionMenuTemplate();
$setting->auth = new PermissionMenuAuth();
$_POST = array();
$setting->permission(206);
$map = $setting->template->vars['ArrActionPers'];
foreach (array(279, 282) as $menuId) {
    permissionCheck(isset($map[$menuId]) && count($map[$menuId]) === 4, 'Expense menu ' . $menuId . ' must expose View/Add/Manage/Delete despite orphan id_menu');
}
$html = (new PermissionMenuView())->render($setting->template->vars);
foreach (array(279, 282) as $menuId) {
    permissionCheck(preg_match_all('/name="id_permissions\[\]"[^>]*menuID' . $menuId . '\b/', $html) === 4, 'Rendered Expense action checkboxes');
}
permissionCheck(strpos($html, "checked='checked'") === false, 'Repair must not grant unchecked permissions');

$menus = array_merge($setting->db->roots, $setting->db->details, array(permissionMenu(281, 282, 'Petty Cash', 'Expense_Petty_Cash.View')));
$actions = array_merge($setting->db->actions, array(
    array('id_permission' => 1048, 'id_menu' => 281, 'nm_permission' => 'Expense_Petty_Cash.View'),
    array('id_permission' => 1100, 'id_menu' => 999, 'nm_permission' => 'Unregistered.View'),
));
$mapped = $setting->permissions_model->actions_by_menu($menus, $actions);
permissionCheck(count($mapped[281]) === 1 && $mapped[281][0]['id_permission'] === 1048, 'Valid mapping retained');
permissionCheck(count($mapped[279]) === 4, 'Unrelated permission families cannot leak into Expense');
permissionCheck(isset($mapped[999]) && count($mapped[999]) === 1, 'Unknown family not assigned to an unrelated menu');
$duplicated = $setting->permissions_model->actions_by_menu(array_merge($menus, $menus), $setting->db->actions);
permissionCheck(count($duplicated[279]) === 4 && count($duplicated[282]) === 4, 'Repeated menu rows do not duplicate action checkboxes');
$legacy = $setting->permissions_model->actions_by_menu($menus, array(array('id_permission'=>1201,'id_menu'=>998,'nm_permission'=>'Legacy')));
permissionCheck(isset($legacy[998]) && !isset($legacy[279]), 'Unrecognized permission names keep their original mapping');
$valid = $setting->db->actions;
foreach ($valid as &$row) { $row['id_menu'] = 279; } unset($row);
$mapped = $setting->permissions_model->actions_by_menu($menus, $valid);
permissionCheck(isset($mapped[279]) && !isset($mapped[282]), 'Existing valid mapping is not overridden');

$setting->users_model->role = array((object) array('id_permission' => 955));
$setting->permission(206);
$html = (new PermissionMenuView())->render($setting->template->vars);
permissionCheck(preg_match_all('/value="955"[^>]*checked=\x27checked\x27[^>]*disabled=\x27disabled\x27/', $html) === 2, 'Role permissions remain checked and disabled on both aliases');

$setting->input = new PermissionMenuInput();
$setting->input->ids = array('955', '956', '955', '956');
$setting->user_permissions_model = new PermissionMenuPersistence();
$save = new ReflectionMethod('Setting', 'save_permission'); $save->setAccessible(true);
permissionCheck((bool)$save->invoke($setting, 206), 'Selected permissions save successfully');
permissionCheck($setting->user_permissions_model->saved === array(array('id_user' => 206, 'id_permission' => '955'), array('id_user' => 206, 'id_permission' => '956')), 'Save aliases once, only View/Add selected; no Manage/Delete escalation');

require dirname(__DIR__, 3) . '/libraries/Menu_generator.php';
function &get_instance() { return $GLOBALS['ci']; }
function check_class($path, $active) { return ''; }
class PermissionSidebarAuth {
    public function is_admin() { return false; }
    public function user_id() { return 206; }
    public function user_name() { return 'alief'; }
}
class PermissionSidebarDb {
    public $dbprefix = '';
    private $filters = array();
    private $allowed;
    public function __call($name, $args) { return $this; }
    public function where($field, $value) { $this->filters[str_replace('t1.', '', $field)] = $value; return $this; }
    public function where_in($field, $values) { $this->allowed = $values; return $this; }
    public function get() {
        $rows = array();
        foreach (array(array(298,0,1027,'Pengeluaran Non Pembelian','#'), array(282,298,955,'Expense','#'), array(279,282,955,'Expense','expense/index')) as $entry) {
            $row = (object) array('id'=>$entry[0], 'parent_id'=>$entry[1], 'permission_id'=>$entry[2],
                'title'=>$entry[3], 'link'=>$entry[4], 'status'=>1, 'group_menu'=>1, 'icon'=>'fa fa-money', 'target'=>'');
            foreach ($this->filters as $key=>$value) { if ($row->$key != $value) { continue 2; } }
            if ($this->allowed !== null && !in_array($row->permission_id, $this->allowed)) { continue; }
            $rows[] = $row;
        }
        $this->filters = array(); $this->allowed = null;
        return new class($rows) { private $rows; public function __construct($rows) { $this->rows=$rows; } public function result() { return $this->rows; } };
    }
}
$setting->users_model->role = array();
$GLOBALS['ci'] = (object) array('db'=>new PermissionSidebarDb(), 'users_model'=>$setting->users_model,
    'auth'=>new PermissionSidebarAuth(), 'load'=>new class { public function helper($name) {} public function library($name) {} },
    'uri'=>new class { public function uri_string() { return ''; } });
$setting->users_model->direct = array_map(function($row) { return (object)$row; }, $setting->user_permissions_model->saved);
permissionCheck(strpos((new Menu_generator())->build_menus(), '/expense/index') !== false, 'Expense visible after chosen View permission is saved');
$setting->input->ids = array('956');
$save->invoke($setting,206);
$setting->users_model->direct = array_map(function($row) { return (object)$row; }, $setting->user_permissions_model->saved);
permissionCheck(strpos((new Menu_generator())->build_menus(), '/expense/index') === false, 'Add alone cannot expose Expense without View');
echo 'PASS Permission menus: ' . $checks . ' checks' . PHP_EOL;
