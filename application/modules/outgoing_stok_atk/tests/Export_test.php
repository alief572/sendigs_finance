<?php
require __DIR__ . '/Index_test.php';
$db = new IndexDb(); $model = new IndexModel($db);
$rows = iterator_to_array($model->get_export_rows('Kertas', 1, 'asc'));
verify(count($rows) === 4, 'Export includes all sibling items of matching transactions');
verify($rows[0]['kode_trans'] === 'TRM1' && $rows[3]['kode_trans'] === 'TRM2', 'Export follows requested order');
verify($rows[0]['qty'] === 2.5 && $rows[0]['unit'] === 'RIM', 'Export retains numeric quantity and unit');
verify(iterator_to_array($model->get_export_rows('NoSuchItem')) === array(), 'Empty search result exports no rows');
$all = iterator_to_array($model->get_export_rows('', 1, 'asc'));
verify(count($all) === 9, 'All detail lines and empty transaction exported');
verify($all[7]['name'] === 'Tidak ada detail barang' && $all[7]['qty'] === null, 'Empty transaction retained without invented quantity');
verify(strpos($all[4]['name'], '999') !== false && $all[4]['unit'] === '-', 'Deleted material fallback retained');
$db->failAt = count($db->queries) + 1;
try { iterator_to_array($model->get_export_rows()); verify(false, 'Query error must propagate'); }
catch (RuntimeException $e) { verify(true, 'Export query error propagates'); }

$db = new IndexDb(); $model = new IndexModel($db);
for ($i = 20; $i < 175; $i++) {
    $db->pdo->prepare('INSERT INTO warehouse_adjustment VALUES (?, ?, "Bulk", 1, "2026-10-20", 1, 0, 7, "2026-10-20", "Y", NULL, "outgoing stok", 1, 2)')->execute(array($i, 'BATCH-' . $i));
    $db->pdo->prepare('INSERT INTO warehouse_adjustment_detail VALUES (?, ?, 11, 1)')->execute(array($i, 'BATCH-' . $i));
}
$bulk = iterator_to_array($model->get_export_rows());
verify(count($bulk) === 164, 'Export spans more than 150 transactions');
verify(count(array_unique(array_column($bulk, 'kode_trans'))) === 161, 'Batch boundary neither skips nor duplicates transactions');
foreach (array(1, 2, 3, 4) as $failurePoint) {
    $db = new IndexDb(); $db->db_debug = true; $db->failAt = $failurePoint;
    try { iterator_to_array((new IndexModel($db))->get_export_rows('Kertas')); verify(false, 'Every query failure must propagate'); }
    catch (RuntimeException $e) { verify($db->db_debug === true, 'DB debug setting restored after query failure'); }
}
class FailingExportHris extends IndexHris {
    public $db_debug = true;
    public function __call($name, $args) { return $name === 'get' ? false : $this; }
}
class FailingDepartmentModel extends IndexModel {
    public function __construct($db) { parent::__construct($db); $this->dbhris = new FailingExportHris(); }
}
try { iterator_to_array((new FailingDepartmentModel(new IndexDb()))->get_export_rows()); verify(false, 'HRIS failure must not become empty department'); }
catch (RuntimeException $e) { verify(true, 'HRIS failure propagates'); }
$db = new IndexDb(); $db->db_debug = true;
iterator_to_array((new IndexModel($db))->get_export_rows());
verify($db->db_debug === true, 'DB debug setting restored after successful export');
foreach (array('100%_!' => 2, "%' OR 1=1 --" => 0) as $search => $expected) {
    verify(count(iterator_to_array((new IndexModel(new IndexDb()))->get_export_rows($search))) === $expected, 'Export treats special search characters literally');
}
echo 'PASS Outgoing export model: ' . $checks . " checks including index regression\n";
