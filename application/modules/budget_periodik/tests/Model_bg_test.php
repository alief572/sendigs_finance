<?php
// Budget_periodik_model di atas SQLite (query builder CodeIgniter sungguhan): ringkasan master, pembuatan BG, keputusan BG.
// Jalankan: scratch/runtime/php74/php.exe application/modules/budget_periodik/tests/Model_bg_test.php
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
require dirname(__DIR__, 3) . '/helpers/periodik_helper.php';
require APPPATH . 'modules/budget_periodik/models/Budget_periodik_model.php';

$real = DB(['dbdriver' => 'pdo', 'subdriver' => 'sqlite', 'dsn' => 'sqlite::memory:', 'db_debug' => false], true);
// periodik_next_number memakai INSERT IGNORE (MySQL); SQLite menyebutnya INSERT OR IGNORE.
class MysqlishDb {
	public $db;
	public function __construct($db) { $this->db = $db; }
	public function query($sql, $binds = false) { return $this->db->query(str_replace('INSERT IGNORE', 'INSERT OR IGNORE', $sql), $binds); }
	public function __call($name, $args) { return call_user_func_array([$this->db, $name], $args); }
}
$db = new MysqlishDb($real);
$real->query('CREATE TABLE ms_periodik_counter (kode TEXT, periode TEXT, last_no INTEGER NOT NULL DEFAULT 0, PRIMARY KEY (kode, periode))');
$real->query('CREATE TABLE ms_budget_rutin (id INTEGER PRIMARY KEY AUTOINCREMENT, departement TEXT, coa TEXT, nama TEXT, tipe TEXT, tanggal TEXT, nilai REAL, keterangan TEXT, kode_id TEXT, masa_berlaku INTEGER DEFAULT 0, masa_awal INTEGER DEFAULT 0, aktif INTEGER DEFAULT 1)');
$real->query('CREATE TABLE tr_pengajuan_rutin_detail (id INTEGER PRIMARY KEY AUTOINCREMENT, id_budget INTEGER, item_status INTEGER, no_doc TEXT, no_item TEXT, item_reject_ket TEXT, item_approved_date TEXT, closed_at TEXT)');
$real->query('CREATE TABLE tr_budget_bg (id INTEGER PRIMARY KEY AUTOINCREMENT, no_bg TEXT UNIQUE, departement TEXT, status TEXT, created_by TEXT, created_on TEXT, decided_on TEXT)');
$real->query('CREATE TABLE tr_budget_bg_line (id INTEGER PRIMARY KEY AUTOINCREMENT, no_bg TEXT, kind TEXT NOT NULL, id_budget INTEGER, before_json TEXT, after_json TEXT, fields TEXT, decision TEXT, note TEXT, decided_by TEXT, decided_at TEXT)');

function master($real, $dept, $nama, $nilai, $extra = []) {
	$real->insert('ms_budget_rutin', array_merge(['departement' => $dept, 'coa' => '6101', 'nama' => $nama, 'tipe' => 'bulan', 'tanggal' => '25', 'nilai' => $nilai,
		'keterangan' => 'baseline lama', 'kode_id' => 'ALK-1', 'masa_berlaku' => 0, 'masa_awal' => 0, 'aktif' => 1], $extra));
	return $real->insert_id();
}
$sewa = master($real, 'DEP-1', 'Sewa Kantor', 1500000);
$cicilan = master($real, 'DEP-1', 'Cicilan Laptop', 800000, ['masa_berlaku' => 5, 'masa_awal' => 1]);
$listrik = master($real, 'DEP-2', 'Listrik', 300000);
foreach ([[$cicilan, 1], [$cicilan, 2], [$cicilan, 9], [$cicilan, 0], [$cicilan, 8], [$sewa, 1]] as $d) { $real->insert('tr_pengajuan_rutin_detail', ['id_budget' => $d[0], 'item_status' => $d[1]]); }

$model = (new ReflectionClass('Budget_periodik_model'))->newInstanceWithoutConstructor();
$model->db = $db;

// GetMasters
$m = $model->GetMasters('DEP-1');
check(array_column($m, 'nama') === ['Cicilan Laptop', 'Sewa Kantor'] || array_column($m, 'nama') === ['Sewa Kantor', 'Cicilan Laptop'], 'masters of the department');
$by = array_column($m, null, 'nama');
check((int) $by['Cicilan Laptop']['in_sys'] === 3 && (int) $by['Cicilan Laptop']['pend'] === 1, 'usage: approved = status 1, 2, 9; waiting = status 0; rejected (8) counts for neither');
check((int) $by['Sewa Kantor']['in_sys'] === 1 && (int) $by['Sewa Kantor']['pend'] === 0, 'usage of an item with one approved payment');
check(count($model->GetMasters()) === 3 && count($model->GetMasters('DEP-9')) === 0, 'all departments / unknown department');

// rej_*: pengajuan terakhir item ditolak (row 5 = status 8 terbaru milik Cicilan Laptop)
check($by['Cicilan Laptop']['rej_no'] === null && $by['Sewa Kantor']['rej_no'] === null, 'last rejection: none while the rejected row has no number, none for an item whose last submission was approved');
$real->update('tr_pengajuan_rutin_detail', ['no_doc' => 'BP-2611-001', 'no_item' => 'PI-2026-00005', 'item_reject_ket' => 'Dokumen kurang', 'item_approved_date' => '2026-11-05 10:00:00'], ['id' => 5]);
$c1 = array_column($model->GetMasters('DEP-1'), null, 'nama')['Cicilan Laptop'];
check($c1['rej_no'] === 'PI-2026-00005' && $c1['rej_note'] === 'Dokumen kurang' && $c1['rej_at'] === '2026-11-05 10:00:00' && (int) $c1['in_sys'] === 3 && (int) $c1['pend'] === 1 && count($model->GetMasters('DEP-1')) === 2, 'last rejection: number, reason, time; one row per master, usage counts unchanged');
check(Budget_logic::last_reject($c1) === ['no' => 'PI-2026-00005', 'note' => 'Dokumen kurang', 'at' => '2026-11-05', 'closed' => false], 'last_reject: shaped for the UI (date only, not closed)');
$real->update('tr_pengajuan_rutin_detail', ['no_item' => null], ['id' => 5]);
check(array_column($model->GetMasters('DEP-1'), null, 'nama')['Cicilan Laptop']['rej_no'] === 'BP-2611-001', 'last rejection: legacy batch without item number falls back to the batch number');
$real->update('tr_pengajuan_rutin_detail', ['no_item' => 'PI-2026-00005', 'closed_at' => '2026-11-06 08:00:00'], ['id' => 5]);
check(Budget_logic::last_reject(array_column($model->GetMasters('DEP-1'), null, 'nama')['Cicilan Laptop'])['closed'] === true, 'last rejection: closed item is flagged as not resubmitted');
$real->insert('tr_pengajuan_rutin_detail', ['id_budget' => $cicilan, 'item_status' => 0]);
$newerId = $real->insert_id();
check(array_column($model->GetMasters('DEP-1'), null, 'nama')['Cicilan Laptop']['rej_no'] === null && Budget_logic::last_reject(array_column($model->GetMasters('DEP-1'), null, 'nama')['Cicilan Laptop']) === null, 'last rejection: a newer submission (resubmitted) replaces it');
$real->delete('tr_pengajuan_rutin_detail', ['id' => $newerId]);
$real->update('tr_pengajuan_rutin_detail', ['no_doc' => null, 'no_item' => null, 'item_reject_ket' => null, 'item_approved_date' => null, 'closed_at' => null], ['id' => 5]);

// CreateBg: nomor, header, baris
$lines = Budget_logic::diff($m, [array_merge($by['Sewa Kantor'], ['nilai' => 1750000]), ['id' => '', 'coa' => '6102', 'nama' => 'Canva', 'tipe' => 'tahun', 'tanggal' => '11/15/2026', 'nilai' => 450000, 'masa_berlaku' => 3, 'masa_awal' => 0, 'aktif' => 1]]);
check(count($lines) === 2, 'two change lines (edit + new)');
list($ok, $no1, $msg) = $model->CreateBg('DEP-1', $lines, 'admin.dep1');
check($ok && preg_match('/^BG-\d{4}-001$/', $no1) === 1 && $msg === '', 'first BG gets BG-YYMM-001');
$bgs = $model->GetBgs('DEP-1');
check(array_keys($bgs) === [$no1] && $bgs[$no1]['status'] === 'pending' && $bgs[$no1]['by'] === 'admin.dep1' && count($bgs[$no1]['lines']) === 2, 'BG stored as pending with its lines');
$l0 = $bgs[$no1]['lines'][0];
check($l0['kind'] === 'edit' && $l0['id_budget'] === (int) $sewa && $l0['fields'] === ['nilai'] && $l0['before']['nilai'] === 1500000.0 && $l0['after']['nilai'] === 1750000.0, 'edit line keeps before/after/fields');
$l1 = $bgs[$no1]['lines'][1];
check($l1['kind'] === 'new' && $l1['id_budget'] === null && $l1['before'] === null && $l1['after']['nama'] === 'Canva' && $l1['fields'] === [], 'new line has no master id yet');
check((float) $model->GetMasters('DEP-1')[1]['nilai'] !== 1750000.0 && count($model->GetMasters('DEP-1')) === 2, 'master unchanged until the BG is approved');
check($model->GetPendingBg('DEP-1')['no'] === $no1 && $model->GetPendingBg('DEP-2') === null, 'pending BG per department');

list($ok, $no, $msg) = $model->CreateBg('DEP-1', $lines, 'admin.dep1');
check(!$ok && $no === '' && strpos($msg, 'Masih ada perubahan ' . $no1) === 0, 'second BG for a department with a pending BG is refused');
check(count($model->GetBgs('DEP-1')) === 1, 'refused BG leaves nothing behind');
list($ok, $no2, $msg) = $model->CreateBg('DEP-2', Budget_logic::diff($model->GetMasters('DEP-2'), [['id' => $listrik, 'coa' => '6101', 'nama' => 'Listrik', 'tipe' => 'bulan', 'tanggal' => '10', 'nilai' => 300000, 'masa_berlaku' => 0, 'masa_awal' => 0, 'aktif' => 0]]), 'admin.dep2');
check($ok && preg_match('/-002$/', $no2) === 1, 'another department can submit; numbering continues');
list($ok, , $msg) = $model->CreateBg('DEP-3', [], 'x');
check(!$ok && $msg === 'Tidak ada perubahan untuk disimpan.', 'no lines refused');
// baris tidak valid membatalkan seluruh BG (header ikut dibatalkan)
$before = count($real->get('tr_budget_bg')->result());
$broken = [['kind' => null, 'id' => null, 'before' => null, 'after' => ['nama' => 'x'], 'fields' => []]];
list($ok) = $model->CreateBg('DEP-3', $broken, 'x');
check(!$ok && count($real->get('tr_budget_bg')->result()) === $before, 'a failing line rolls back the whole BG');

// DecideBg: validasi
list($ok, $msg) = $model->DecideBg('BG-TIDAK-ADA', [], 'direktur');
check(!$ok && $msg === 'Perubahan budget tidak ditemukan.', 'unknown BG');
$ids = array_column($bgs[$no1]['lines'], 'id');
list($ok, $msg) = $model->DecideBg($no1, [$ids[0] => ['A', '']], 'direktur');
check(!$ok && $msg === 'Keputusan belum lengkap untuk semua perubahan.', 'every line needs a decision');
list($ok, $msg) = $model->DecideBg($no1, [$ids[0] => ['A', ''], $ids[1] => ['R', '  ']], 'direktur');
check(!$ok && $msg === 'Ada perubahan yang di-reject tanpa alasan.', 'reject requires a reason');
check($model->GetPendingBg('DEP-1')['no'] === $no1 && (float) $real->get_where('ms_budget_rutin', ['id' => $sewa])->row()->nilai === 1500000.0, 'failed validation changes nothing');

// DecideBg: approve edit, reject new
list($ok, $msg, $a, $r) = $model->DecideBg($no1, [$ids[0] => ['A', ''], $ids[1] => ['R', 'Belum ada kontrak']], 'direktur');
check($ok && $a === 1 && $r === 1, 'decided: one approved, one rejected');
$row = $real->get_where('ms_budget_rutin', ['id' => $sewa])->row();
check((float) $row->nilai === 1750000.0 && $row->nama === 'Sewa Kantor' && $row->kode_id === 'ALK-1' && $row->keterangan === 'baseline lama', 'approved edit changes only the edited column; allocation link and old Baseline stay');
check(count($model->GetMasters('DEP-1')) === 2, 'rejected new item is not inserted');
$done = $model->GetBgs('DEP-1', 'done')[$no1];
check($done['status'] === 'done' && $done['decided_on'] !== null && $done['lines'][0]['decision'] === 'A' && $done['lines'][1]['decision'] === 'R' && $done['lines'][1]['note'] === 'Belum ada kontrak' && $done['lines'][0]['decided_by'] === 'direktur', 'decision, reason, approver recorded');
check($model->GetPendingBg('DEP-1') === null, 'department can submit again after the decision');
list($ok, $msg) = $model->DecideBg($no1, [$ids[0] => ['R', 'x'], $ids[1] => ['R', 'x']], 'direktur');
check(!$ok && $msg === 'Keputusan perubahan ini sudah disimpan dan tidak bisa diubah.', 'decision is final');
check((float) $real->get_where('ms_budget_rutin', ['id' => $sewa])->row()->nilai === 1750000.0, 'final decision cannot be overwritten');

// approve item baru + nonaktifkan
$cur = $model->GetMasters('DEP-1');
list(, $no3) = $model->CreateBg('DEP-1', Budget_logic::diff($cur, [array_merge(array_column($cur, null, 'nama')['Cicilan Laptop'], ['aktif' => 0]),
	['id' => '', 'coa' => '6102', 'nama' => 'Canva', 'tipe' => 'tahun', 'tanggal' => '11/15/2026', 'nilai' => 450000, 'masa_berlaku' => 3, 'masa_awal' => 0, 'aktif' => 1]]), 'admin.dep1');
$b3 = $model->GetBgs('DEP-1', 'pending')[$no3];
check($b3['lines'][0]['kind'] === 'off', 'only aktif changed -> off');
$d3 = [];
foreach ($b3['lines'] as $l) { $d3[$l['id']] = ['A', '']; }
list($ok, , $a) = $model->DecideBg($no3, $d3, 'direktur');
check($ok && $a === 2, 'approve off + new');
$after = array_column($model->GetMasters('DEP-1'), null, 'nama');
check((int) $after['Cicilan Laptop']['aktif'] === 0 && (int) $after['Cicilan Laptop']['id'] === (int) $cicilan, 'inactive item keeps its id (history intact)');
check(isset($after['Canva']) && $after['Canva']['departement'] === 'DEP-1' && $after['Canva']['tipe'] === 'tahun' && $after['Canva']['tanggal'] === '11/15/2026' && (int) $after['Canva']['masa_berlaku'] === 3 && (int) $after['Canva']['aktif'] === 1, 'approved new item inserted into the department');
$newLine = $model->GetBgs('DEP-1', 'done')[$no3]['lines'][1];
check($newLine['id_budget'] === (int) $after['Canva']['id'], 'new line records the master id it created');

// masa berlaku tidak boleh di bawah pemakaian saat disetujui; seluruh keputusan dibatalkan
$cur = array_column($model->GetMasters('DEP-1'), null, 'nama');
list(, $no4) = $model->CreateBg('DEP-1', Budget_logic::diff(array_values($cur), [array_merge($cur['Sewa Kantor'], ['nilai' => 1]), array_merge($cur['Cicilan Laptop'], ['masa_berlaku' => 6])]), 'admin.dep1');
$real->insert('tr_pengajuan_rutin_detail', ['id_budget' => $cicilan, 'item_status' => 1]);
$real->insert('tr_pengajuan_rutin_detail', ['id_budget' => $cicilan, 'item_status' => 1]);
$real->insert('tr_pengajuan_rutin_detail', ['id_budget' => $cicilan, 'item_status' => 1]);                     // used sekarang 1 + 6 = 7 > 6
$b4 = $model->GetBgs('DEP-1', 'pending')[$no4];
$d4 = [];
foreach ($b4['lines'] as $l) { $d4[$l['id']] = ['A', '']; }
list($ok, $msg) = $model->DecideBg($no4, $d4, 'direktur');
check(!$ok && strpos($msg, 'Masa berlaku "Cicilan Laptop" tidak boleh kurang dari 7 kali') === 0, 'approval blocked when masa fell below usage in the meantime');
check($model->GetPendingBg('DEP-1')['no'] === $no4 && (float) $real->get_where('ms_budget_rutin', ['id' => $sewa])->row()->nilai === 1750000.0, 'blocked approval rolled back entirely (no partial apply)');

echo "PASS Budget_periodik_model on SQLite: {$checks} assertions\n";
