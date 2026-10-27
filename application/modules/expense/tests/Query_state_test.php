<?php
define('BASEPATH', dirname(__DIR__, 4) . '/system/');
function log_message($level, $message) {}
function base_url($path) { return '/' . $path; }
function has_permission($permission) { global $ci; return $ci->auth->has_permission($permission); }
class Admin_Controller {}
require BASEPATH . 'core/Common.php';
require BASEPATH . 'database/DB_driver.php';
require BASEPATH . 'database/DB_query_builder.php';
require dirname(__DIR__, 2) . '/users/libraries/Auth.php';
require dirname(__DIR__) . '/controllers/Expense.php';

class QueryStateResult {
    private $row;
    public function __construct($row) { $this->row = $row; }
    public function row() { return $this->row; }
    public function num_rows() { return $this->row === null ? 0 : 1; }
    public function result_array() { return array((array) $this->row); }
}
class QueryStateDb extends CI_DB_query_builder {
    protected $_escape_char = '`';
    public $isAdmin = false;
    public $finance = false;
    public $record;
    public function __construct() {
        parent::__construct(array('dbdriver' => 'fixture'));
        $this->record = (object) array('sql' => array());
    }
    public function query($sql, $binds = false, $return_object = null) {
        if (strpos($sql, '`permissions`') !== false) {
            $allowed = $this->finance && strpos($sql, "'Expense_Approval.Manage'") !== false;
            return new QueryStateResult($allowed ? (object) array('id_user' => 42) : null);
        }
        if (strpos($sql, '`user_groups`') !== false) {
            return new QueryStateResult($this->isAdmin ? (object) array('id_user' => 42) : null);
        }
        if (!preg_match('/FROM `tr_expense` `a`/', $sql)) {
            throw new RuntimeException('Expense query lost its table after authentication lookup: ' . $sql);
        }
        $this->record->sql[] = $sql;
        if (strpos($sql, 'COUNT(') !== false) { return new QueryStateResult((object) array('numrows' => 1)); }
        return new QueryStateResult((object) array('id' => 10, 'no_doc' => 'EXP-TEST', 'tgl_doc' => '2026-10-27',
            'nama' => 'alice', 'nmuser' => 'Alice', 'nmapproval' => '', 'jumlah' => 100,
            'informasi' => 'Expense fixture', 'status' => 3, 'total_kasbon' => 150, 'lebih_bayar' => 50,
            'kurang_bayar' => 50, 'no_rp' => null, 'status_rp' => null));
    }
}
class QueryStateUserModel {
    private $db;
    private $table;
    public function __construct($db, $table) { $this->db = $db; $this->table = $table; }
    public function __call($method, $args) { call_user_func_array(array($this->db, $method), $args); return $this; }
    public function find_by($where) { return $this->db->where($where)->get($this->table)->row(); }
}
class QueryStateInput {
    public $values;
    public function post() { return $this->values; }
}
$checks = 0;
function queryStateCheck($ok, $message) {
    global $checks; $checks++;
    if (!$ok) { throw new RuntimeException($message); }
}
foreach (array(array(false, false), array(false, true), array(true, false)) as $roles) {
    foreach (array('', 'Expense') as $search) {
        foreach (array('0', '1') as $all) {
            foreach (array('get_dat_expense_data', 'get_dat_pengembalian_kasbon', 'get_dat_lebih_expense') as $endpoint) {
                $ci = (new ReflectionClass('Expense'))->newInstanceWithoutConstructor();
                $ci->db = new QueryStateDb();
                list($ci->db->isAdmin, $ci->db->finance) = $roles;
                $ci->users_model = new QueryStateUserModel($ci->db, 'users');
                $ci->user_groups_model = new QueryStateUserModel($ci->db, 'user_groups');
                $ci->auth = (new ReflectionClass('Auth'))->newInstanceWithoutConstructor();
                foreach (array('ci' => $ci, 'user' => array('id_user' => '42', 'username' => 'alice')) as $name => $value) {
                    $property = new ReflectionProperty('Auth', $name);
                    $property->setAccessible(true); $property->setValue($ci->auth, $value);
                }
                $ci->input = new QueryStateInput();
                $ci->input->values = array('draw' => 3, 'start' => 0, 'length' => 10, 'all' => $all,
                    'search' => array('value' => $search), 'order' => array(array('column' => 1, 'dir' => 'asc')));
                ob_start();
                try { $ci->$endpoint(); $output = ob_get_contents(); } finally { ob_end_clean(); }
                $response = json_decode($output, true);
                queryStateCheck($response['recordsTotal'] === 1 && $response['recordsFiltered'] === 1 && count($response['data']) === 1, $endpoint . ': populated result and counts');
                queryStateCheck(count($ci->db->record->sql) === 3, $endpoint . ': total, filtered count and page query');
                foreach ($ci->db->record->sql as $sql) {
                    $scoped = strpos($sql, "`a`.`nama` = 'alice'") !== false;
                    queryStateCheck($scoped === !($roles[0] || $roles[1]), $endpoint . ': requester scope retained');
                    queryStateCheck(strpos($sql, '`a`.`id_po`') !== false && strpos($sql, '`a`.`no_doc`') !== false, $endpoint . ': base filters retained');
                }
            }
        }
    }
}
echo 'PASS Expense query state: ' . $checks . ' checks with real Auth and CodeIgniter query builder' . PHP_EOL;
