<?php
define('BASEPATH', __DIR__);
class Admin_Controller {}
require dirname(__DIR__) . '/controllers/Expense.php';
function has_permission($permission) { return in_array($permission, $GLOBALS['rights'], true); }
function base_url($path) { return '/' . $path; }
class VisibilityAuth {
    public $admin = false;
    public $id = '7';
    public $username = 'alice';
    public function is_admin() { return $this->admin; }
    public function user_id() { return $this->id; }
    public function user_name() { return $this->username; }
}
class VisibilityInput {
    public $values = array();
    public function post() { return $this->values; }
    public function get($key) { return isset($this->values[$key]) ? $this->values[$key] : null; }
}
class VisibilityResult {
    public function row() { return (object) array('cnt' => 0); }
    public function result() { return array(); }
    public function result_array() { return array(); }
}
class VisibilityDb {
    public $conditions = array();
    public $queries;
    public function __construct() { $this->queries = (object) array('items' => array()); }
    public function __call($name, $args) {
        if (in_array($name, array('where', 'or_where', 'where_in', 'not_like'), true)) {
            $this->conditions[] = array($name, $args);
        }
        return $this;
    }
    public function get() { $this->record(true); return new VisibilityResult(); }
    public function count_all_results($table = '', $reset = true) { $this->record($reset); return 0; }
    private function record($reset) {
        $this->queries->items[] = $this->conditions;
        if ($reset) { $this->conditions = array(); }
    }
}
class VisibilityTemplate {
    public $values = array();
    public function set($key, $value) { $this->values[$key] = $value; }
    public function __call($name, $args) { return $this; }
}
$checks = 0;
function visibilityCheck($ok, $message) {
    global $checks;
    $checks++;
    if (!$ok) { throw new RuntimeException($message); }
}
$controller = (new ReflectionClass('Expense'))->newInstanceWithoutConstructor();
$controller->auth = new VisibilityAuth();
$controller->input = new VisibilityInput();
$controller->template = new VisibilityTemplate();
foreach (array(
    array(false, '7', 'alice', array('Expense.Manage'), false),
    array(false, '1', 'alice', array(), false),
    array(false, '42', 'alice', array('Approval Expense Management.Manage'), false),
    array(false, '206', 'alief', array('Expense_Approval.Manage'), false),
    array(false, '203', 'alice', array(), false),
    array(false, '42', 'finance-team', array('Expense_Approval.Manage'), false),
    array(false, '42', 'finance', array(), true),
    array(true, '42', 'alice', array(), true),
) as $case) {
    list($controller->auth->admin, $controller->auth->id, $controller->auth->username, $GLOBALS['rights'], $allUsers) = $case;
    foreach (array('', 'bob') as $search) {
        foreach (array('0', '1') as $all) {
            $controller->input->values = array('draw' => 1, 'start' => 10, 'length' => 10,
                'all' => $all, 'search' => array('value' => $search));
            foreach (array('index', 'get_dat_expense_data', 'get_dat_pengembalian_kasbon', 'get_dat_lebih_expense') as $method) {
                $controller->db = new VisibilityDb();
                ob_start();
                $controller->$method();
                $output = ob_get_clean();
                $queries = $controller->db->queries->items;
                if ($method === 'index') { array_shift($queries); }
                visibilityCheck(count($queries) >= 2, $method . ' must query counts and data');
                foreach ($queries as $conditions) {
                    $owner = array_filter($conditions, function ($condition) use ($controller) {
                        return $condition[0] === 'where' && in_array($condition[1][0], array('created_by', 'a.created_by'), true)
                            && $condition[1][1] === $controller->auth->user_name();
                    });
                    visibilityCheck((bool) $owner === !$allUsers, $method . ': creator scope case=' . json_encode($case));
                    foreach ($conditions as $condition) {
                        visibilityCheck(!in_array($condition[1][0], array('nama', 'a.nama'), true), 'Requester must not determine ownership');
                    }
                }
                if ($method !== 'index') {
                    $response = json_decode($output, true);
                    visibilityCheck($response['recordsTotal'] === 0 && $response['recordsFiltered'] === 0, 'DataTables counts remain valid');
                }
            }
        }
    }
}
foreach (array('get_expense_app_finance', 'get_dat_approval_expense_manage') as $method) {
    $controller->db = new VisibilityDb();
    $GLOBALS['rights'] = array();
    ob_start(); $controller->$method(); ob_end_clean();
    visibilityCheck(count($controller->db->queries->items) === 3, 'Approval total/search/data queries');
    foreach ($controller->db->queries->items as $conditions) {
        $serialized = json_encode($conditions);
        visibilityCheck(strpos($serialized, 'alice') === false && strpos($serialized, 'created_by') === false, 'Approval must span requesters');
        visibilityCheck(strpos($serialized, 'sts_finance') !== false, 'Approval stage retained');
        visibilityCheck(strpos($serialized, 'PI-') !== false, 'Material invoice exclusion retained');
        if ($method === 'get_dat_approval_expense_manage') {
            visibilityCheck(strpos($serialized, 'pettycash') !== false, 'Management petty cash exclusion retained');
        }
    }
}
echo 'PASS Expense visibility: ' . $checks . ' checks' . PHP_EOL;
