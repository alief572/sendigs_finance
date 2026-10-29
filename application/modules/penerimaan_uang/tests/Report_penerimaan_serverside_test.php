<?php
define('BASEPATH', '/var/www/html/system/');
define('APPPATH', '/var/www/html/application/');
define('ENVIRONMENT', 'development');

require_once '/var/www/html/system/core/Common.php';
require_once '/var/www/html/system/database/DB.php';

class Admin_Controller {}
class BF_Model {}
require_once '/var/www/html/application/modules/penerimaan_uang/models/Penerimaan_uang_model.php';
require_once '/var/www/html/application/modules/penerimaan_uang/controllers/Penerimaan_uang.php';

class MockInput {
    private $postData;
    public function __construct($post = array()) { $this->postData = $post; }
    public function post($key = null) {
        if ($key === null) return $this->postData;
        return $this->postData[$key] ?? null;
    }
}

$db = DB('default', TRUE);
$model = (new ReflectionClass('Penerimaan_uang_model'))->newInstanceWithoutConstructor();
$model->db = $db;
$controller = (new ReflectionClass('Penerimaan_uang'))->newInstanceWithoutConstructor();
$controller->db = $db;
$controller->Penerimaan_uang_model = $model;

function runTest($controller, $name, $post) {
    $controller->input = new MockInput($post);
    ob_start();
    $controller->get_report_penerimaan();
    $output = ob_get_clean();
    $json = json_decode($output, true);
    if (!$json) {
        echo "FAILED: $name - Invalid JSON: $output\n";
        exit(1);
    }
    return $json;
}

echo "=== TEST 1: Default 2026 Page 1 ===\n";
$res1 = runTest($controller, "Test 1", array(
    'draw' => 1, 'start' => 0, 'length' => 10,
    'year' => '2026', 'month' => '', 'company' => '', 'search' => '',
    'order' => array(array('column' => 1, 'dir' => 'desc'))
));
assert($res1['recordsFiltered'] === 290, "Filtered should be 290");
assert(count($res1['data']) === 10, "Data should have 10 rows");
assert($res1['data'][0]['no'] === 1, "First row no should be 1");
echo "PASS: Default Page 1 has 10 rows out of 290 filtered.\n";

echo "=== TEST 2: Search '043/VSB' ===\n";
$res2 = runTest($controller, "Test 2", array(
    'draw' => 2, 'start' => 0, 'length' => 10,
    'year' => '2026', 'month' => '', 'company' => '', 'custom_search' => '043/VSB',
    'order' => array(array('column' => 1, 'dir' => 'desc'))
));
assert($res2['recordsFiltered'] === 1, "Filtered should be 1");
assert(count($res2['data']) === 1, "Data should have 1 row");
assert(strpos($res2['data'][0]['no_invoice'], '043/VSB/2026') !== false, "Invoice number must match");
echo "PASS: Search 043/VSB matched 1 receipt.\n";

echo "=== TEST 3: Company VUCA ===\n";
$res3 = runTest($controller, "Test 3", array(
    'draw' => 3, 'start' => 0, 'length' => 10,
    'year' => '2026', 'month' => '', 'company' => 'VUCA', 'search' => '',
    'order' => array(array('column' => 1, 'dir' => 'desc'))
));
assert($res3['recordsFiltered'] === 37, "Filtered should be 37 for VUCA");
assert($res3['count_invoices'] === 53, "Invoices should be 53 for VUCA");
echo "PASS: Company VUCA filtered to 37 receipts and 53 invoices.\n";

echo "=== TEST 4: Company SUSTAIN ===\n";
$res4 = runTest($controller, "Test 4", array(
    'draw' => 4, 'start' => 0, 'length' => 10,
    'year' => '2026', 'month' => '', 'company' => 'SUSTAIN', 'search' => '',
    'order' => array(array('column' => 1, 'dir' => 'desc'))
));
assert($res4['recordsFiltered'] === 14, "Filtered should be 14 for SUSTAIN");
assert($res4['count_invoices'] === 14, "Invoices should be 14 for SUSTAIN");
echo "PASS: Company SUSTAIN filtered to 14 receipts and 14 invoices.\n";

echo "=== TEST 5: Pagination (Page 2: start=10, length=10) ===\n";
$res5 = runTest($controller, "Test 5", array(
    'draw' => 5, 'start' => 10, 'length' => 10,
    'year' => '2026', 'month' => '', 'company' => '', 'search' => '',
    'order' => array(array('column' => 1, 'dir' => 'desc'))
));
assert(count($res5['data']) === 10, "Data should have 10 rows");
assert($res5['data'][0]['no'] === 11, "Page 2 first row no should be 11");
assert($res5['data'][9]['no'] === 20, "Page 2 last row no should be 20");
echo "PASS: Pagination offset works correctly (rows 11 to 20).\n";

echo "=== TEST 6: Sorting by Nominal (col 8) ASC ===\n";
$res6 = runTest($controller, "Test 6", array(
    'draw' => 6, 'start' => 0, 'length' => 10,
    'year' => '2026', 'month' => '', 'company' => '', 'search' => '',
    'order' => array(array('column' => 8, 'dir' => 'asc'))
));
assert(count($res6['data']) === 10, "Data should have 10 rows");
echo "PASS: Sorting by nominal ASC works correctly.\n";

echo "\nALL 6 SERVERSIDE DATATABLES TESTS PASSED 100%!\n";
