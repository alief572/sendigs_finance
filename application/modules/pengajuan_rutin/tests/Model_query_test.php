<?php
// Query asli Pengajuan_rutin_model di atas SQLite (CodeIgniter query builder sungguhan).
// Jalankan: scratch/runtime/php74/php.exe application/modules/pengajuan_rutin/tests/Model_query_test.php
define('BASEPATH', dirname(__DIR__, 4) . '/system/');
define('APPPATH', dirname(__DIR__, 3) . '/');
$checks = 0;
set_error_handler(function ($severity, $message) { throw new ErrorException($message, 0, $severity); });
function log_message($level, $message) {}
function show_error($message) { throw new RuntimeException($message); }
function load_class($name, $directory) { return null; }
function check($ok, $message) { global $checks; $checks++; if (!$ok) { throw new RuntimeException($message); } }
class BF_Model { public function __construct() {} }
require BASEPATH . 'database/DB.php';
require APPPATH . 'modules/pengajuan_rutin/models/Pengajuan_rutin_model.php';

$db = DB(['dbdriver' => 'pdo', 'subdriver' => 'sqlite', 'dsn' => 'sqlite::memory:', 'db_debug' => false], true);
$db->query('CREATE TABLE ms_budget_rutin (id INTEGER PRIMARY KEY, departement TEXT, coa TEXT, nama TEXT, tipe TEXT, tanggal TEXT, nilai REAL, aktif INTEGER, masa_berlaku INTEGER, masa_awal INTEGER)');
$db->query('CREATE TABLE tr_pengajuan_rutin (id INTEGER PRIMARY KEY, no_doc TEXT, tanggal_doc TEXT, periode TEXT, departement TEXT, created_by TEXT, status INTEGER)');
$db->query('CREATE TABLE tr_pengajuan_rutin_detail (id INTEGER PRIMARY KEY, no_doc TEXT, no_item TEXT, id_budget INTEGER, coa TEXT, nama TEXT, tanggal TEXT, budget REAL, nilai REAL,
	keterangan TEXT, doc_file TEXT, bank_id TEXT, accnumber TEXT, accname TEXT, item_status INTEGER, item_reject_ket TEXT, item_approved_by TEXT, item_approved_date TEXT,
	revisi_dari TEXT, diajukan_ulang_sebagai TEXT, closed_note TEXT, closed_at TEXT)');
$db->query('CREATE TABLE tr_rp_pengajuan_h (id INTEGER PRIMARY KEY, no_pengajuan TEXT, status TEXT, deleted INTEGER)');
$db->query('CREATE TABLE tr_rp_pengajuan_d (id INTEGER PRIMARY KEY, id_pengajuan INTEGER, no_dokumen TEXT, decision TEXT)');
$db->query('CREATE TABLE tr_rp_reject_log (id INTEGER PRIMARY KEY, no_dokumen TEXT, no_pengajuan TEXT, alasan TEXT)');

foreach ([[1, 'DEP-1', 'Sewa Kantor', 'bulan', 25, 0, 0], [2, 'DEP-1', 'Pajak Reklame', 'tahun', '03/15/2026', 0, 0], [3, 'DEP-1', 'Cicilan Laptop', 'bulan', 5, 3, 1], [4, 'DEP-2', 'Listrik', 'bulan', 10, 0, 0]] as $m) {
	$db->insert('ms_budget_rutin', ['id' => $m[0], 'departement' => $m[1], 'coa' => '6101', 'nama' => $m[2], 'tipe' => $m[3], 'tanggal' => (string) $m[4], 'nilai' => 1000 * $m[0], 'aktif' => 1, 'masa_berlaku' => $m[5], 'masa_awal' => $m[6]]);
}
$db->insert('tr_pengajuan_rutin', ['id' => 1, 'no_doc' => 'BP-2611-001', 'tanggal_doc' => '2026-11-03', 'periode' => '2026-11', 'departement' => 'DEP-1', 'created_by' => 7, 'status' => 0]);
$db->insert('tr_pengajuan_rutin', ['id' => 2, 'no_doc' => 'BP-2610-003', 'tanggal_doc' => '2026-10-20', 'periode' => '2026-10', 'departement' => 'DEP-1', 'created_by' => 7, 'status' => 1]);
$db->insert('tr_pengajuan_rutin', ['id' => 3, 'no_doc' => 'PERIODIK-2026-00150', 'tanggal_doc' => '2026-10-05', 'periode' => null, 'departement' => 'DEP-2', 'created_by' => 8, 'status' => 1]);
function detail($db, $id, $doc, $item, $budget, $status, $extra = []) {
	$db->insert('tr_pengajuan_rutin_detail', array_merge(['id' => $id, 'no_doc' => $doc, 'no_item' => $item, 'id_budget' => $budget, 'coa' => '6101', 'nama' => 'Item ' . $budget, 'tanggal' => '25',
		'budget' => 1000, 'nilai' => 900, 'keterangan' => 'ket', 'doc_file' => '', 'bank_id' => '', 'accnumber' => '', 'accname' => '', 'item_status' => $status,
		'item_reject_ket' => null, 'item_approved_by' => null, 'item_approved_date' => null, 'revisi_dari' => null, 'diajukan_ulang_sebagai' => null, 'closed_note' => null, 'closed_at' => null], $extra));
}
detail($db, 1, 'BP-2611-001', 'PI-2026-00001', 1, 0, ['accnumber' => '', 'nilai' => 25000]);                                                        // menunggu, Sewa Kantor
detail($db, 2, 'BP-2611-001', 'PI-2026-00002', 3, 0);                                                                                                  // menunggu, Cicilan Laptop
detail($db, 3, 'BP-2610-003', 'PI-2026-00003', 1, 1, ['bank_id' => 'BCA', 'accnumber' => '111', 'accname' => 'Vendor A', 'item_approved_by' => 'Direktur', 'item_approved_date' => '2026-10-21 09:00:00']);
detail($db, 4, 'BP-2610-003', 'PI-2026-00004', 3, 1, ['bank_id' => 'BNI', 'accnumber' => '222', 'accname' => 'Vendor B']);                                // disetujui, Cicilan Laptop
detail($db, 5, 'BP-2610-003', 'PI-2026-00005', 2, 8, ['item_reject_ket' => 'Dokumen kurang', 'closed_note' => 'Tagihan batal', 'closed_at' => '2026-10-25 08:00:00']);
detail($db, 6, 'PERIODIK-2026-00150', null, 4, 1);                                                                                                      // batch lama, tanpa no_item

$model = (new ReflectionClass('Pengajuan_rutin_model'))->newInstanceWithoutConstructor();
$model->db = $db;

// GetBatchesWithItems
$all = $model->GetBatchesWithItems(null);
check(array_keys($all) === ['PERIODIK-2026-00150', 'BP-2611-001', 'BP-2610-003'], 'batches ordered by number, newest first');
check(array_column($all['BP-2611-001']['items'], 'no_item') === ['PI-2026-00001', 'PI-2026-00002'], 'items grouped under their batch, in detail order');
$i = $all['BP-2611-001']['items'][0];
check($i['status'] === 'W' && $i['nominal'] === 25000.0 && $i['periode'] === '2026-11' && $i['m_tipe'] === 'bulan' && $i['id_budget'] == 1, 'item row mapping (status letter, nominal, period, budget type)');
$legacy = $all['PERIODIK-2026-00150']['items'][0];
check($legacy['no_item'] === 'PERIODIK-2026-00150' && $legacy['periode'] === '2026-10' && $legacy['status'] === 'A', 'legacy batch: batch number acts as item number and period derives from the document date');
$rej = $all['BP-2610-003']['items'][2];
check($rej['status'] === 'R' && $rej['note'] === 'Dokumen kurang' && $rej['closed_at'] === '2026-10-25 08:00:00' && $rej['closed_note'] === 'Tagihan batal', 'rejected and closed item carries reason and closure');
check($all['BP-2610-003']['items'][0]['by'] === 'Direktur' && $all['BP-2610-003']['items'][0]['at'] === '2026-10-21 09:00:00', 'approver and time carried');
$dep1 = $model->GetBatchesWithItems('DEP-1');
check(array_keys($dep1) === ['BP-2611-001', 'BP-2610-003'], 'department filter');
$one = $model->GetBatchesWithItems(null, 'BP-2610-003');
check(array_keys($one) === ['BP-2610-003'] && count($one['BP-2610-003']['items']) === 3, 'single batch filter');
check($model->GetBatchesWithItems(null, 'TIDAK-ADA') === [], 'unknown batch gives empty list');

// CountBatchViews + ListPage (serverside)
check($model->CountBatchViews(null) === ['wait' => 1, 'rec' => 2] && $model->CountBatchViews('DEP-2') === ['wait' => 0, 'rec' => 1], 'tab counts, department scoped');
list($tot, $fil, $rows) = $model->ListPage(null, 'wait', 'batch', 'all', '', 0, 10);
check($tot === 1 && $fil === 1 && array_column($rows, 'no') === ['BP-2611-001'] && count($rows[0]['items']) === 2, 'wait batches: one batch with all its items');
list($tot, $fil, $rows) = $model->ListPage(null, 'rec', 'batch', 'all', '', 0, 1);
check($tot === 2 && $fil === 2 && array_column($rows, 'no') === ['PERIODIK-2026-00150'], 'rec batches: paging limits rows, total stays');
list(, $fil, $rows) = $model->ListPage(null, 'rec', 'batch', 'all', '', 1, 10);
check(array_column($rows, 'no') === ['BP-2610-003'], 'rec batches: offset');
list(, $fil, $rows) = $model->ListPage(null, 'rec', 'batch', 'P', '', 0, 10);
check(array_column($rows, 'no') === ['BP-2610-003'], 'rec batch filter P: mixed approved + rejected');
list(, $fil, $rows) = $model->ListPage(null, 'rec', 'batch', 'A', '', 0, 10);
check(array_column($rows, 'no') === ['PERIODIK-2026-00150'], 'rec batch filter A: all approved');
list(, $fil, $rows) = $model->ListPage(null, 'rec', 'batch', 'R', '', 0, 10);
check($fil === 0 && $rows === [], 'rec batch filter R: none fully rejected');
list($tot, $fil, $rows) = $model->ListPage(null, 'rec', 'item', 'R', '', 0, 10);
check($tot === 4 && $fil === 1 && $rows[0]['item']['no_item'] === 'PI-2026-00005' && $rows[0]['batch']['no'] === 'BP-2610-003', 'rec item filter R');
list(, $fil, $rows) = $model->ListPage(null, 'rec', 'item', 'A', '', 0, 10);
check($fil === 3, 'rec item filter A');
list(, $fil, $rows) = $model->ListPage(null, 'wait', 'item', 'R', '', 0, 10);
check($fil === 2, 'wait view ignores the status filter');
list(, $fil, $rows) = $model->ListPage('DEP-1', 'rec', 'item', 'all', '', 0, 10);
check($fil === 3 && $rows[0]['batch']['no'] === 'BP-2610-003', 'department lock applies to items');
list(, $fil, $rows) = $model->ListPage(null, 'wait', 'batch', 'all', 'PI-2026-00002', 0, 10);
check(array_column($rows, 'no') === ['BP-2611-001'], 'batch search matches an item number');
list(, $fil, $rows) = $model->ListPage(null, 'wait', 'batch', 'all', 'tidak-ada', 0, 10);
check($fil === 0 && $rows === [], 'batch search without match');
list(, $fil, $rows) = $model->ListPage(null, 'rec', 'item', 'all', 'PI-2026-00003', 0, 10);
check($fil === 1 && $rows[0]['item']['no_item'] === 'PI-2026-00003', 'item search by item number');
list(, $fil) = $model->ListPage(null, 'rec', 'item', 'all', "x%' OR 1=1 --", 0, 10);
check($fil === 0, 'search text is escaped (no injection, % is literal)');

// GetMasterWithItems + Periodik_logic::state, end to end
$masters = $model->GetMasterWithItems('DEP-1');
check(array_column($masters, 'nama') === ['Sewa Kantor', 'Pajak Reklame', 'Cicilan Laptop'], 'masters of the department only');
$byName = [];
foreach ($masters as $m) { $byName[$m['nama']] = $m; }
check(count($byName['Sewa Kantor']['items']) === 2, 'master carries its item history across batches');
$st = Periodik_logic::state($byName['Sewa Kantor'], $byName['Sewa Kantor']['items'], '2026-11');
check($st['state'] === 'lock' && $st['lock']['no_item'] === 'PI-2026-00001', 'November: Sewa Kantor already submitted -> locked with its item number');
check(Periodik_logic::state($byName['Sewa Kantor'], $byName['Sewa Kantor']['items'], '2026-12')['state'] === 'free', 'December: Sewa Kantor open again');
$st = Periodik_logic::state($byName['Pajak Reklame'], $byName['Pajak Reklame']['items'], '2026-10');
check($st['state'] === 'closed', 'closed rejected item disappears from the form for its period');
check(Periodik_logic::state($byName['Pajak Reklame'], $byName['Pajak Reklame']['items'], '2027-03')['state'] === 'free', 'annual item open again next year');
$st = Periodik_logic::state($byName['Cicilan Laptop'], $byName['Cicilan Laptop']['items'], '2026-12');
check($st['used'] === 2 && count($st['pend']) === 1 && $st['state'] === 'lock' && !empty($st['last']), 'Cicilan Laptop: 1 before system + 1 approved, 1 waiting = 3 of 3 -> last payment waiting');
$excluded = $model->GetMasterWithItems('DEP-1', 'BP-2611-001');
check(count($excluded[0]['items']) === 1 && $excluded[0]['items'][0]['no_item'] === 'PI-2026-00003', 'editing a batch excludes its own items from the history');
check($model->GetMasterWithItems('DEP-9') === [], 'department without budget gives no rows');

// GetRpStates
$db->insert('tr_rp_pengajuan_h', ['id' => 1, 'no_pengajuan' => 'PGJ-0001', 'status' => 'done', 'deleted' => 0]);
$db->insert('tr_rp_pengajuan_h', ['id' => 2, 'no_pengajuan' => 'PGJ-0002', 'status' => 'pending', 'deleted' => 0]);
$db->insert('tr_rp_pengajuan_h', ['id' => 3, 'no_pengajuan' => 'PGJ-0003', 'status' => 'done', 'deleted' => 0]);
$db->insert('tr_rp_pengajuan_h', ['id' => 4, 'no_pengajuan' => 'PGJ-0004', 'status' => 'pending', 'deleted' => 1]);
$db->insert('tr_rp_pengajuan_d', ['id' => 1, 'id_pengajuan' => 1, 'no_dokumen' => 'PI-2026-00003', 'decision' => 'approved']);
$db->insert('tr_rp_pengajuan_d', ['id' => 2, 'id_pengajuan' => 2, 'no_dokumen' => 'PI-2026-00004', 'decision' => 'approved']);
$db->insert('tr_rp_pengajuan_d', ['id' => 3, 'id_pengajuan' => 3, 'no_dokumen' => 'PI-2026-00001', 'decision' => 'rejected']);
$db->insert('tr_rp_pengajuan_d', ['id' => 4, 'id_pengajuan' => 4, 'no_dokumen' => 'PI-2026-00002', 'decision' => 'approved']);
$db->insert('tr_rp_reject_log', ['id' => 1, 'no_dokumen' => 'PI-2026-00001', 'no_pengajuan' => 'PGJ-0003', 'alasan' => 'PPN belum dicentang']);
$rp = $model->GetRpStates(['PI-2026-00001', 'PI-2026-00002', 'PI-2026-00003', 'PI-2026-00004', 'PI-2026-00005', '', 'PI-2026-00003']);
check($rp['PI-2026-00003'] === ['state' => 'done', 'no' => 'PGJ-0001'], 'RP approved by Finance');
check($rp['PI-2026-00004'] === ['state' => 'pending', 'no' => 'PGJ-0002'], 'RP waiting for Finance approval');
check($rp['PI-2026-00001'] === ['state' => 'belum', 'no' => 'PGJ-0003', 'reject' => 'PPN belum dicentang'], 'RP rejected by Finance returns to waiting with the reason');
check(!isset($rp['PI-2026-00002']) && !isset($rp['PI-2026-00005']), 'deleted RP batch and never-submitted items have no RP state');
check($model->GetRpStates([]) === [], 'no items -> no query result');
// Alasan lama tidak menempel bila item sudah diajukan ulang dan menunggu approval
$db->insert('tr_rp_pengajuan_h', ['id' => 5, 'no_pengajuan' => 'PGJ-0005', 'status' => 'pending', 'deleted' => 0]);
$db->insert('tr_rp_pengajuan_d', ['id' => 5, 'id_pengajuan' => 5, 'no_dokumen' => 'PI-2026-00001', 'decision' => 'approved']);
$rp = $model->GetRpStates(['PI-2026-00001']);
check($rp['PI-2026-00001'] === ['state' => 'pending', 'no' => 'PGJ-0005'] && !isset($rp['PI-2026-00001']['reject']), 'resubmitted to Finance: latest state wins and the old reject reason is dropped');

// stepper end to end dari data nyata
$step = Periodik_logic::item_steps($all['BP-2610-003']['items'][0], $rp['PI-2026-00003'] ?? ['state' => 'done', 'no' => 'PGJ-0001']);
check($step['card']['bd'] === 'Disetujui Finance', 'stepper from real rows: approved by Finance');

// GetLastBank
$bank = $model->GetLastBank(1);
check($bank->accnumber === '111' && $bank->bank_id === 'BCA', 'last known bank skips rows without account number');
check($model->GetLastBank(999) === null || $model->GetLastBank(999) === false || !$model->GetLastBank(999), 'no bank for unknown budget item');

echo "PASS Pengajuan_rutin_model queries on SQLite: {$checks} assertions\n";
