<?php
// Render view sungguhan dengan data contoh: teks mockup, escaping, dan sintaks JS inline.
// Jalankan: scratch/runtime/php74/php.exe application/modules/pengajuan_rutin/tests/Views_test.php
define('BASEPATH', dirname(__DIR__, 4) . '/system/');
$checks = 0;
set_error_handler(function ($severity, $message, $file, $line) { throw new ErrorException($message . ' @' . basename($file) . ':' . $line, 0, $severity); });
function check($ok, $message) { global $checks; $checks++; if (!$ok) { throw new RuntimeException($message); } }
function base_url($p = '') { return '/base/' . $p; }
require dirname(__DIR__) . '/libraries/Periodik_logic.php';
require_once dirname(__DIR__, 3) . '/helpers/periodik_helper.php';

function render($view, $vars) {
	extract($vars);
	ob_start();
	include dirname(__DIR__) . '/views/' . $view . '.php';
	return ob_get_clean();
}
function item($letter, $extra = []) {
	return array_merge(['id' => 1, 'no_item' => 'PI-2026-00001', 'no_batch' => 'BP-2611-001', 'id_budget' => 10, 'desc' => 'Sewa <b>Kantor</b>', 'status' => $letter,
		'item_status' => $letter === 'W' ? 0 : ($letter === 'R' ? 8 : 1), 'nominal' => 1500000.0, 'ket' => 'Catatan "khusus"', 'doc_file' => 'abc123.pdf',
		'note' => $letter === 'R' ? 'Salah <script>x</script>' : '', 'by' => $letter === 'W' ? '' : 'Direktur', 'at' => $letter === 'W' ? '' : '2026-11-04 09:30:00',
		'ref' => '', 'resub' => '', 'closed_note' => '', 'closed_at' => null, 'periode' => '2026-11', 'tgl_batch' => '2026-11-03', 'tanggal' => '25', 'budget' => 1500000,
		'coa' => '6101', 'bank_id' => 'BCA', 'accnumber' => '123', 'accname' => 'Vendor', 'm_tipe' => 'bulan'], $extra);
}
function batch($no, $id, $items, $date = '2026-11-03') {
	return ['id' => $id, 'no' => $no, 'dept' => 'DEP-1', 'date' => $date, 'periode' => substr($date, 0, 7), 'created_by' => 7, 'items' => $items];
}
function js_syntax($html, $label) {
	if (!preg_match_all('#<script>(.*?)</script>#s', $html, $m)) { return; }
	$js = implode("\n;\n", $m[1]);
	$file = tempnam(sys_get_temp_dir(), 'pjv') . '.js';
	file_put_contents($file, $js);
	exec('node --check ' . escapeshellarg($file) . ' 2>&1', $out, $code);
	unlink($file);
	check($code === 0, "inline JS syntax ($label): " . implode(' ', $out));
}
$names = ['DEP-1' => 'Finance & Accounting - STM'];

// ---- list.php
$batches = [
	'BP-2611-002' => batch('BP-2611-002', 2, [item('W', ['id' => 3, 'no_item' => 'PI-2026-00003'])]),
	'BP-2611-001' => batch('BP-2611-001', 1, [item('A'), item('R', ['id' => 2, 'no_item' => 'PI-2026-00002', 'desc' => 'Listrik', 'resub' => 'PI-2026-00009'])]),
	'BP-2610-004' => batch('BP-2610-004', 4, [item('R', ['id' => 4, 'no_item' => 'PI-2026-00004', 'closed_at' => '2026-11-05 10:00:00', 'closed_note' => 'Tagihan batal'])], '2026-10-04'),
];
$rp = ['PI-2026-00001' => ['state' => 'pending', 'no' => 'PGJ-0007']];
$html = render('list', ['counts' => ['wait' => 1, 'rec' => 2], 'dd_items' => '[]', 'can_add' => true]);
check(strpos($html, 'Menunggu approval (1)') !== false && strpos($html, 'Sudah diputuskan (2)') !== false, 'list: tab counts');
foreach (['wait-batch', 'wait-item', 'rec-batch', 'rec-item'] as $pane) { check(strpos($html, 'data-dt="' . $pane . '"') !== false, "list: serverside table $pane"); }
check(strpos($html, 'serverSide: true') !== false && strpos($html, 'pengajuan_rutin/data_list') !== false, 'list: DataTables serverside wired to data_list');
foreach (['No. Item', 'No. Batch', 'Departemen', 'Deskripsi', 'Nominal', 'Keterangan', 'Progres', 'Status batch', 'Action'] as $col) { check(strpos($html, '>' . $col . '<') !== false, "list: column $col"); }
js_syntax($html, 'list');
$noAdd = render('list', ['counts' => ['wait' => 0, 'rec' => 0], 'dd_items' => '[]', 'can_add' => false]);
check(strpos($noAdd, 'id="pjNew"') === false && strpos($noAdd, 'Tidak ada batch yang menunggu approval. Klik + New untuk membuat pengajuan.') !== false, 'list: no New button without Add permission, empty-state text');

// ---- batch_detail.php
$detail = render('batch_detail', ['batch' => $batches['BP-2611-001'], 'dept_name' => 'Finance & Accounting - STM', 'rp' => $rp, 'chain' => ['PI-2026-00001' => [['no' => 'PI-2026-00000', 'note' => 'Salah <i>x</i>']]], 'from' => 'app', 'can_decide' => false]);
check(strpos($detail, 'Proses approval') === false, 'detail: no decide button for a decided batch');
check(strpos($detail, 'Riwayat revisi: PI-2026-00000 (Salah &lt;i&gt;x&lt;/i&gt;)') !== false, 'detail: revision history escaped');
check(strpos($detail, 'href="/base/assets/bayar_rutin/abc123.pdf"') !== false, 'detail: attachment link');
$detail = render('batch_detail', ['batch' => $batches['BP-2611-002'], 'dept_name' => 'X', 'rp' => [], 'chain' => [], 'from' => 'app', 'can_decide' => true]);
check(strpos($detail, 'href="/base/pengajuan_rutin/proses/2"') !== false, 'detail: decide button for approver on waiting batch');
check(strpos(render('batch_detail', ['batch' => null, 'dept_name' => '', 'rp' => [], 'chain' => [], 'from' => '', 'can_decide' => false]), 'Data tidak ditemukan') !== false, 'detail: missing batch message');

// ---- input_form.php
$m = ['id' => 10, 'nama' => 'Sewa <Kantor>', 'tipe' => 'bulan', 'tanggal' => '25', 'nilai' => 1500000, 'coa' => '6101', 'aktif' => 1, 'masa_berlaku' => 3, 'masa_awal' => 0, 'items' => []];
$mAnnual = array_merge($m, ['id' => 11, 'nama' => 'Pajak', 'tipe' => 'tahun', 'tanggal' => '03/15/2026', 'masa_berlaku' => 0]);
$mkRow = function ($master, $periode, $items = [], $mine = null) {
	$st = Periodik_logic::state($master, $items, $periode);
	return ['m' => $master, 'st' => $st, 'tag' => Periodik_logic::masa_tag($st), 'mine' => $mine];
};
$locked = array_merge($m, ['id' => 12, 'nama' => 'Terkunci']);
$rows = [
	$mkRow($m, '2026-11'),
	$mkRow($mAnnual, '2026-11'),
	$mkRow($locked, '2026-11', [item('W', ['id_budget' => 12, 'no_item' => 'PI-2026-00020'])]),
	$mkRow(array_merge($m, ['id' => 13, 'nama' => 'Ditolak', 'masa_berlaku' => 0]), '2026-11', [item('R', ['id_budget' => 13, 'no_item' => 'PI-2026-00021', 'nominal' => 777.0])]),
];
$hidden = [$mkRow(array_merge($m, ['id' => 14, 'nama' => 'Mati', 'aktif' => 0]), '2026-11')];
$form = render('input_form', ['type' => 'add', 'dept' => 'DEP-1', 'dept_name' => 'Finance & Accounting', 'periode' => '2026-11', 'period_options' => ['2026-11' => 'November 2026', '2026-12' => 'Desember 2026'], 'rows' => $rows, 'hidden' => $hidden, 'batch' => null]);
check(strpos($form, 'Buat batch baru') !== false && strpos($form, 'Auto (BP-' . date('ym') . '-NNN)') !== false, 'form: new batch heading and auto batch number');
check(strpos($form, 'Sewa &lt;Kantor&gt;') !== false && strpos($form, 'Sewa <Kantor>') === false, 'form: budget name escaped');
check(strpos($form, 'Pembayaran ke-1 dari 3') !== false, 'form: masa berlaku tag');
check(strpos($form, 'Tgl 25') !== false && strpos($form, '15 Mar 2026') !== false && strpos($form, '(tahunan)') !== false, 'form: schedule shown for monthly and annual');
check(preg_match('/data-mid="12"[^>]*>.*?PI-2026-00020.*?Sudah diajukan \(PI-2026-00020\)/s', $form) === 1 && strpos($form, 'placeholder="Terkunci"') !== false, 'form: locked row shows its item number and is disabled');
check(strpos($form, 'Ditolak di PI-2026-00021') !== false && strpos($form, 'data-closeitem="PI-2026-00021"') !== false && strpos($form, 'value="777"') !== false, 'form: rejected row reopens with its amount and the close button');
check(strpos($form, '1 item tidak ditampilkan:') !== false && strpos($form, 'Mati (dinonaktifkan)') !== false, 'form: hidden items note');
check(strpos($form, 'name="biaya[10]"') !== false && strpos($form, 'name="doc_10"') !== false && strpos($form, 'name="biaya[12]"') !== false, 'form: field names carry the budget id');
check(strpos($form, 'name="biaya[12]" data-biaya="12" value="0" disabled') !== false, 'form: locked amount is disabled so it is never posted');
check(strpos($form, 'type="number"') === false && substr_count($form, 'inputmode="numeric"') === 4, 'form: every cost input is a masked text field (no type=number)');
check(strpos($form, 'fd.set(inp.name, PJ.moneyValue(inp.value))') !== false && strpos($form, 'PJ.maskMoney(e.target)') !== false, 'form: masks while typing and posts plain numbers (FormData rewritten before send)');
js_syntax($form, 'input_form');
$edit = render('input_form', ['type' => 'edit', 'dept' => 'DEP-1', 'dept_name' => 'FA', 'periode' => '2026-11', 'period_options' => ['2026-11' => 'November 2026'],
	'rows' => [$mkRow($m, '2026-11', [], item('W', ['ket' => 'Pembayaran ke-1 dari 3 - bayar tunai', 'nominal' => 1500000.0, 'doc_file' => 'file.pdf']))], 'hidden' => [], 'batch' => $batches['BP-2611-001']]);
check(strpos($edit, 'name="biaya[10]" data-biaya="10" value="1.500.000"') !== false, 'form (edit): existing amount shown with thousand separators');
check(strpos($edit, 'Edit BP-2611-001') !== false && strpos($edit, 'name="id" value="1"') !== false && strpos($edit, 'id="perSel" disabled') !== false, 'form (edit): heading, batch id, period locked');
check(strpos($edit, 'data-keep="PI-2026-00001"') !== false && strpos($edit, '>bayar tunai</textarea>') !== false && strpos($edit, 'File: file.pdf') !== false && strpos($edit, 'Simpan perubahan') !== false, 'form (edit): keeps item number, strips auto prefix from note, shows file');
check(strpos(render('input_form', ['type' => 'add', 'dept' => 'DEP-1', 'dept_name' => 'FA', 'periode' => '2026-11', 'period_options' => ['2026-11' => 'N'], 'rows' => [], 'hidden' => [], 'batch' => null]), 'Tidak ada item aktif untuk departemen ini.') !== false, 'form: empty department text');

// ---- app_list.php
$apps = render('app_list', ['batches' => $batches, 'names' => $names, 'can_decide' => true]);
check(strpos($apps, 'Menunggu approval (1)') !== false && strpos($apps, 'href="/base/pengajuan_rutin/proses/2"') !== false && strpos($apps, 'proses/1"') === false, 'app_list: only waiting batches have a decide action');
check(strpos($apps, 'Direktur') !== false && strpos($apps, 'Approved') !== false && strpos($apps, 'Rejected') !== false, 'app_list: record tab lists decisions');
check(strpos($apps, '2026-11-04 09:30') !== false, 'app_list: decision time');
check(strpos(render('app_list', ['batches' => $batches, 'names' => $names, 'can_decide' => false]), 'Proses approval') === false, 'app_list: no decide link without Manage permission');
check(strpos(render('app_list', ['batches' => [], 'names' => $names, 'can_decide' => true]), 'Belum ada keputusan approval.') !== false, 'app_list: empty record text');
js_syntax($apps, 'app_list');

// ---- approval_form.php
$appr = render('approval_form', ['batch' => batch('BP-2611-009', 9, [item('W'), item('W', ['id' => 2, 'no_item' => 'PI-2026-00002', 'ref' => 'PI-2026-00000', 'tanggal' => '03/15/2026', 'm_tipe' => 'tahun'])]),
	'dept_name' => 'FA', 'chain' => ['PI-2026-00002' => [['no' => 'PI-2026-00000', 'note' => 'Salah <b>x</b>']]]]);
check(strpos($appr, 'Approval Pengajuan Periodik') !== false && strpos($appr, 'BP-2611-009') !== false, 'approval: heading with batch number');
check(substr_count($appr, 'data-v="A"') === 2 && substr_count($appr, 'data-v="R"') === 2, 'approval: approve/reject toggle for each waiting item');
check(strpos($appr, 'Ditolak sebelumnya: PI-2026-00000: Salah &lt;b&gt;x&lt;/b&gt;') !== false && strpos($appr, 'Revisi dari PI-2026-00000') !== false, 'approval: rejection history of a revised item');
check(strpos($appr, '15 Mar 2026') !== false && strpos($appr, 'Tgl 25') !== false, 'approval: schedule by budget type');
check(strpos($appr, 'BCA') !== false && strpos($appr, 'Vendor') !== false, 'approval: bank details shown');
check(strpos($appr, 'Simpan keputusan') !== false && strpos($appr, 'Alasan reject umum') !== false && strpos($appr, 'Approve semua') !== false, 'approval: actions');
$legacy = render('approval_form', ['batch' => batch('PERIODIK-2026-00201', 9, [item('W', ['id' => 5, 'no_item' => 'PERIODIK-2026-00201']), item('W', ['id' => 6, 'no_item' => 'PERIODIK-2026-00201'])]), 'dept_name' => 'FA', 'chain' => []]);
check(strpos($legacy, 'data-dec="5" data-v="R"') !== false && strpos($legacy, 'data-dec="6" data-v="R"') !== false && strpos($legacy, 'data-dec="PERIODIK') === false, 'approval: items sharing a legacy item number are keyed by their own row id');
js_syntax($appr, 'approval_form');
$decided = render('approval_form', ['batch' => batch('BP-2611-009', 9, [item('A')]), 'dept_name' => 'FA', 'chain' => []]);
check(strpos($decided, 'data-v="A"') === false && strpos($decided, '>Approved</span>') !== false, 'approval: decided items show their result instead of a toggle');

// ---- aset JS bersama
exec('node --check ' . escapeshellarg(dirname(__DIR__, 4) . '/assets/js/periodik_ui.js') . ' 2>&1', $out, $code);
check($code === 0, 'periodik_ui.js syntax: ' . implode(' ', $out));

echo "PASS Pengajuan Rutin views: {$checks} assertions\n";
