<?php
define('BASEPATH', __DIR__);
require dirname(__DIR__, 3) . '/libraries/Menu_generator.php';
function &get_instance() { return $GLOBALS['ci']; }
function site_url($path = '') { return '/' . $path; }
function check_class($path, $flag) { return ''; }
function check($condition, $message) { if (!$condition) { throw new RuntimeException($message); } }
class MenuAuth {
    public $admin = false;
    public function is_admin() { return $this->admin; }
    public function user_id() { return 7; }
    public function user_name() { return 'auditor'; }
}
class MenuLoad { public function helper($name) {} public function library($name) {} }
class MenuUri { public function uri_string() { return 'audit_trail'; } }
class MenuUsers {
    public $allowed = true;
    public function select($value) { return $this; }
    public function join($table, $condition) { return $this; }
    public function where($field, $value) { return $this; }
    public function find_all() { return $this->allowed ? array((object) array('id_permission' => 200)) : array(); }
}
class MenuDb {
    public $dbprefix = '';
    private $filters = array(), $permissions;
    public function select($value) { return $this; }
    public function from($table) { return $this; }
    public function join($table, $condition, $type) { return $this; }
    public function where($field, $value) { $this->filters[str_replace('t1.', '', $field)] = $value; return $this; }
    public function where_in($field, $values) { $this->permissions = $values; return $this; }
    public function group_by($field) { return $this; }
    public function order_by($field, $direction) { return $this; }
    public function get() {
        $rows = array(
            (object) array('id' => 100, 'title' => 'Settings', 'link' => '#', 'parent_id' => 0, 'permission_id' => 0, 'group_menu' => 1, 'status' => 1, 'icon' => 'fa fa-cog', 'target' => 'sametab'),
            (object) array('id' => 101, 'title' => 'Audit Trail', 'link' => 'audit_trail', 'parent_id' => 100, 'permission_id' => 200, 'group_menu' => 1, 'status' => 1, 'icon' => 'fa fa-history', 'target' => 'sametab')
        );
        $rows = array_values(array_filter($rows, function ($row) {
            foreach ($this->filters as $field => $value) { if ($row->$field != $value) { return false; } }
            return $this->permissions === null || in_array($row->permission_id, $this->permissions, true);
        }));
        $this->filters = array(); $this->permissions = null;
        return new class($rows) { private $rows; public function __construct($rows) { $this->rows = $rows; } public function result() { return $this->rows; } };
    }
}
$GLOBALS['ci'] = (object) array('db' => new MenuDb(), 'load' => new MenuLoad(), 'uri' => new MenuUri(), 'auth' => new MenuAuth(), 'users_model' => new MenuUsers());
check(strpos((new Menu_generator())->build_menus(), 'Audit Trail') !== false, 'User granted Audit_Trail.View cannot see Settings submenu');
$GLOBALS['ci']->users_model->allowed = false;
check(strpos((new Menu_generator())->build_menus(), 'Audit Trail') === false, 'Unauthorized user can see Audit Trail');
$GLOBALS['ci']->auth->admin = true;
check(strpos((new Menu_generator())->build_menus(), 'Audit Trail') !== false, 'Admin must see Audit Trail automatically');
echo "PASS: Audit Trail menu visible to granted users and admins, hidden without permission\n";
