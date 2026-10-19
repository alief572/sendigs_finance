<?php
// Read-only metadata inspection. Never performs application data writes.
if (PHP_SAPI !== 'cli') exit;
define('BASEPATH', dirname(__DIR__, 4) . '/system/');
define('ENVIRONMENT', 'development');
require dirname(__DIR__, 3) . '/config/development/database.php';
$c = $db['default'];
$conn = new mysqli($c['hostname'], $c['username'], $c['password'], $c['database'], isset($c['port']) ? $c['port'] : 3306);
$tables = ['tr_pr_non_po','material_planning_base_on_produksi','material_planning_base_on_produksi_detail','accessories','warehouse','warehouse_adjustment','warehouse_adjustment_detail','warehouse_stock','warehouse_history','warehouse_stock_per_day','tr_cost_book','price_book','ms_satuan'];
echo 'Target: ' . $c['hostname'] . '/' . $c['database'] . PHP_EOL;
foreach ($tables as $table) {
    $r = $conn->query("SHOW CREATE TABLE `$table`");
    if (!$r) throw new RuntimeException($table . ': ' . $conn->error);
    $row = $r->fetch_row();
    echo $table . ': ' . implode(', ', array_column($conn->query("SHOW COLUMNS FROM `$table`")->fetch_all(MYSQLI_ASSOC), 'Field')) . PHP_EOL;
    preg_match('/ENGINE=\w+/', $row[1], $m); echo implode('', $m) . PHP_EOL;
}
