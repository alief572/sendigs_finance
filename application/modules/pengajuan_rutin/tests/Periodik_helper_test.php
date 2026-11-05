<?php
// Jalankan: scratch/runtime/php74/php.exe application/modules/pengajuan_rutin/tests/Periodik_helper_test.php
define('BASEPATH', __DIR__);
require __DIR__ . '/../../../helpers/periodik_helper.php';

$failures = 0;
function check($cond, $msg)
{
	global $failures;
	if (!$cond) {
		$failures++;
		fwrite(STDERR, "FAIL: $msg\n");
	}
}

// DB SQLite kecil yang meniru query() CI untuk counter (INSERT IGNORE -> INSERT OR IGNORE).
class SqliteShimDb
{
	private $pdo;
	public function __construct()
	{
		$this->pdo = new PDO('sqlite::memory:');
		$this->pdo->exec('CREATE TABLE ms_periodik_counter (kode TEXT, periode TEXT, last_no INTEGER NOT NULL DEFAULT 0, PRIMARY KEY (kode, periode))');
	}
	public function query($sql, $binds = array())
	{
		$sql = str_replace('INSERT IGNORE', 'INSERT OR IGNORE', $sql);
		$st  = $this->pdo->prepare($sql);
		$st->execute($binds);
		return new SqliteShimResult($st);
	}
}
class SqliteShimResult
{
	private $st;
	public function __construct($st) { $this->st = $st; }
	public function row() { $r = $this->st->fetch(PDO::FETCH_OBJ); return $r ?: null; }
}

// 1. pola nomor item vs invoice PO vs nomor batch lama
check(periodik_is_item_no('PI-2026-00001') === true, 'PI-2026-00001 adalah nomor item');
check(periodik_is_item_no('PI-2026-11-000012') === false, 'invoice PO bukan nomor item');
check(periodik_is_item_no('PERIODIK-2026-00181') === false, 'nomor batch lama bukan nomor item');
check(periodik_is_item_no('PI-2026-0001') === false, 'terlalu pendek');
check(periodik_is_item_no('PI-2026-000012') === false, 'terlalu panjang');
check(periodik_is_item_no(null) === false, 'null bukan nomor item');

// 2. format nomor
check(periodik_format_number('BP', '2611', 1) === 'BP-2611-001', 'format BP');
check(periodik_format_number('BG', '2611', 12) === 'BG-2611-012', 'format BG');
check(periodik_format_number('PI', '2026', 25) === 'PI-2026-00025', 'format PI');
check(periodik_format_number('BP', '2611', 1000) === 'BP-2611-1000', 'melewati 999 tidak terpotong');
try {
	periodik_format_number('XX', '2611', 1);
	check(false, 'kode tidak dikenal harus melempar exception');
} catch (InvalidArgumentException $e) {
	check(true, '');
}

// 3. periode counter
$ts = mktime(0, 0, 0, 11, 5, 2026);
check(periodik_number_period('BP', $ts) === '2611', 'periode BP = YYMM');
check(periodik_number_period('BG', $ts) === '2611', 'periode BG = YYMM');
check(periodik_number_period('PI', $ts) === '2026', 'periode PI = YYYY');

// 4. urutan, reset per periode, dan counter terpisah per kode
$db  = new SqliteShimDb();
$nov = mktime(0, 0, 0, 11, 5, 2026);
$des = mktime(0, 0, 0, 12, 1, 2026);
$jan = mktime(0, 0, 0, 1, 2, 2027);
check(periodik_next_number($db, 'BP', $nov) === 'BP-2611-001', 'BP pertama bulan ini');
check(periodik_next_number($db, 'BP', $nov) === 'BP-2611-002', 'BP kedua bulan ini');
check(periodik_next_number($db, 'BG', $nov) === 'BG-2611-001', 'BG punya urutan sendiri');
check(periodik_next_number($db, 'BP', $des) === 'BP-2612-001', 'BP reset bulan berikutnya');
check(periodik_next_number($db, 'PI', $nov) === 'PI-2026-00001', 'PI pertama tahun ini');
check(periodik_next_number($db, 'PI', $des) === 'PI-2026-00002', 'PI tidak reset antar bulan');
check(periodik_next_number($db, 'PI', $jan) === 'PI-2027-00001', 'PI reset tahun berikutnya');

// 5. kunci pencarian untuk modul hilir
check(periodik_doc_key('PI-2026-00001') === array('col' => 'no_item', 'val' => 'PI-2026-00001'), 'kunci item');
check(periodik_doc_key('PERIODIK-2026-00181') === array('col' => 'no_doc', 'val' => 'PERIODIK-2026-00181'), 'kunci batch lama');
check(periodik_doc_key('BP-2611-001') === array('col' => 'no_doc', 'val' => 'BP-2611-001'), 'kunci nomor batch baru');

// 5b. jadwal pembayaran: format asli di data (tahunan = MM/DD/YYYY, bulanan = hari)
check(periodik_schedule_text('bulan', '25') === 'Tgl 25' && periodik_schedule_text('bulan', '05') === 'Tgl 5', 'jadwal bulanan');
check(periodik_schedule_text('tahun', '02/18/2026') === '18 Feb 2026' && periodik_schedule_text('tahun', '12/01/2026') === '1 Des 2026', 'jadwal tahunan MM/DD/YYYY (contoh dari data asli)');
check(periodik_schedule_text('tahun', '02/30/2026') === '02/30/2026' && periodik_schedule_text('tahun', '30') === '30', 'nilai tak lazim ditampilkan apa adanya');
check(periodik_schedule_text('tahun', '') === '-' && periodik_schedule_text('bulan', null) === '-', 'kosong menjadi -');

// 6. status batch dari status item (0 menunggu, 1/2/9 disetujui approver, 8 ditolak approver)
check(periodik_batch_status(array()) === 'W', 'batch kosong = W');
check(periodik_batch_status(array(0, 0)) === 'W', 'semua menunggu = W');
check(periodik_batch_status(array(1, 2, 9)) === 'A', 'semua disetujui = A');
check(periodik_batch_status(array(8, 8)) === 'R', 'semua ditolak = R');
check(periodik_batch_status(array(1, 8)) === 'P', 'campuran = Partially Approved');
check(periodik_batch_status(array(0, 1)) === 'P', 'sebagian menunggu = P');

if ($failures) {
	fwrite(STDERR, "$failures kegagalan\n");
	exit(1);
}
echo "PASS: periodik_helper (pola nomor, format, counter, kunci hilir, status batch)\n";
