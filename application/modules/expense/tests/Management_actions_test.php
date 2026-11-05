<?php
define('BASEPATH', __DIR__);
class Admin_Controller {}
require dirname(__DIR__) . '/controllers/Expense.php';

function has_permission($permission)
{
    return in_array($permission, $GLOBALS['permissions'], true);
}

class ManagementActionsInput
{
    public function post() { return array(); }
}

class ManagementActionsDb
{
    public $rows = array();
    public function __call($method, $args) { return $this; }
    public function get() { return $this; }
    public function row() { return (object) array('cnt' => count($this->rows)); }
    public function result() { return $this->rows; }
}

$controller = (new ReflectionClass('Expense'))->newInstanceWithoutConstructor();
$controller->input = new ManagementActionsInput();
$controller->db = new ManagementActionsDb();
$checks = 0;
$failures = array();
foreach (array(array(1, '1'), array(0, '1'), array(0, '0'), array(2, '1'), array(9, '1')) as $state) {
    foreach (array(array(true, true), array(true, false), array(false, true), array(false, false)) as $rights) {
        $GLOBALS['permissions'] = array();
        if ($rights[0]) { $GLOBALS['permissions'][] = 'Approval Expense Management.View'; }
        if ($rights[1]) { $GLOBALS['permissions'][] = 'Approval Expense Management.Manage'; }
        $controller->db->rows = array((object) array(
            'id' => 42, 'no_doc' => 'EXP-TEST', 'tgl_doc' => '2026-10-26',
            'nmuser' => 'Tester', 'informasi' => 'Expense test', 'nominal' => 100,
            'status' => $state[0], 'sts_finance' => $state[1],
        ));
        ob_start();
        $controller->get_dat_approval_expense_manage();
        $response = json_decode(ob_get_clean(), true);
        $action = $response['data'][0]['action'];
        $eligible = $state[0] === 1 || ($state[0] === 0 && $state[1] === '1');
        $expected = array(
            'view route' => array(substr_count($action, 'openExpenseView('), $rights[0] ? 1 : 0),
            'approval route' => array(substr_count($action, 'openExpenseApproval('), $rights[1] && $eligible ? 1 : 0),
            'button count' => array(substr_count($action, '<a '), ($rights[0] ? 1 : 0) + ($rights[1] && $eligible ? 1 : 0)),
        );
        foreach ($expected as $label => $values) {
            $checks++;
            if ($values[0] !== $values[1]) {
                $failures[] = $label . ' state=' . json_encode($state) . ' rights=' . json_encode($rights);
            }
        }
    }
}
if ($failures) {
    fwrite(STDERR, implode(PHP_EOL, $failures) . PHP_EOL);
    exit(1);
}
echo 'PASS Management actions: ' . $checks . ' checks' . PHP_EOL;
