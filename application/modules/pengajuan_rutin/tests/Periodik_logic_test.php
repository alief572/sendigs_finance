<?php
// Jalankan: scratch/runtime/php74/php.exe application/modules/pengajuan_rutin/tests/Periodik_logic_test.php
define('BASEPATH', __DIR__);
require __DIR__ . '/../libraries/Periodik_logic.php';

$failures = 0;
function check($cond, $msg) { global $failures; if (!$cond) { $failures++; fwrite(STDERR, "FAIL: $msg\n"); } }
function item($status, $periode, $extra = array()) {
	return array_merge(array('no_item' => 'PI-2026-00001', 'no_batch' => 'BP-2611-001', 'status' => $status, 'periode' => $periode,
		'tgl_batch' => '2026-11-03', 'at' => '2026-11-04 09:30', 'by' => 'Direktur', 'note' => 'Salah nominal', 'ref' => '', 'resub' => '', 'closed_at' => null, 'closed_note' => ''), $extra);
}
$bulanan = array('tipe' => 'bulan', 'aktif' => 1, 'masa_berlaku' => 0, 'masa_awal' => 0);

// letter & status batch
check(Periodik_logic::letter(0) === 'W' && Periodik_logic::letter(8) === 'R' && Periodik_logic::letter(1) === 'A' && Periodik_logic::letter(9) === 'A', 'letter');
check(Periodik_logic::batch_status(array('W', 'W')) === array('W', 'Waiting Approval'), 'batch W');
check(Periodik_logic::batch_status(array('A', 'R')) === array('P', 'Partially Approved'), 'batch P');
check(Periodik_logic::batch_status(array('R')) === array('R', 'Rejected'), 'batch R');

// state: bebas, nonaktif, masa habis
check(Periodik_logic::state($bulanan, array(), '2026-11')['state'] === 'free', 'free tanpa riwayat');
check(Periodik_logic::state(array('aktif' => 0) + $bulanan, array(), '2026-11')['state'] === 'off', 'off');
$terbatas = array('masa_berlaku' => 3, 'masa_awal' => 2) + $bulanan;
check(Periodik_logic::state($terbatas, array(item('A', '2026-10')), '2026-11')['state'] === 'done', 'done: 2 awal + 1 disetujui = 3');

// lock: sudah diajukan di periode yang sama (bulanan) / tahun yang sama (tahunan)
check(Periodik_logic::state($bulanan, array(item('W', '2026-11')), '2026-11')['state'] === 'lock', 'lock bulan sama');
check(Periodik_logic::state($bulanan, array(item('A', '2026-10')), '2026-11')['state'] === 'free', 'bulan lain tidak terkunci');
$tahunan = array('tipe' => 'tahun') + $bulanan;
check(Periodik_logic::state($tahunan, array(item('A', '2026-03')), '2026-11')['state'] === 'lock', 'lock tahun sama');
check(Periodik_logic::state($tahunan, array(item('A', '2025-11')), '2026-11')['state'] === 'free', 'tahun lain tidak terkunci');

// lock pembayaran terakhir: used + pend >= masa
$tiga = array('masa_berlaku' => 3) + $bulanan;
$st = Periodik_logic::state($tiga, array(item('A', '2026-08'), item('A', '2026-09'), item('W', '2026-10', array('no_item' => 'PI-2026-00009'))), '2026-11');
check($st['state'] === 'lock' && !empty($st['last']) && $st['lock']['no_item'] === 'PI-2026-00009', 'lock pembayaran terakhir menunggu');
check(Periodik_logic::masa_tag($st) === 'Pembayaran terakhir sedang menunggu approval', 'tag terakhir menunggu');
$st = Periodik_logic::state($tiga, array(item('A', '2026-09')), '2026-11');
check(Periodik_logic::masa_tag($st) === 'Pembayaran ke-2 dari 3', 'tag ke-2');
$st = Periodik_logic::state($tiga, array(item('A', '2026-08'), item('A', '2026-09')), '2026-11');
check(Periodik_logic::masa_tag($st) === 'Pembayaran ke-3 dari 3 (terakhir)', 'tag ke-3 terakhir');
check(Periodik_logic::masa_tag(Periodik_logic::state($bulanan, array(), '2026-11')) === '', 'Selalu tanpa tag');
check(Periodik_logic::masa_tag(Periodik_logic::state($tiga, array(item('W', '2026-11')), '2026-11')) === '', 'terkunci karena sudah diajukan: tanpa tag');
check(Periodik_logic::ket_prefix(Periodik_logic::state($tiga, array(item('A', '2026-08'), item('A', '2026-09')), '2026-11')) === 'Pembayaran ke-3 dari 3', 'ket_prefix tanpa (terakhir)');
check(Periodik_logic::ket_prefix(Periodik_logic::state($bulanan, array(), '2026-11')) === '', 'ket_prefix kosong untuk Selalu');
check(Periodik_logic::user_ket('Pembayaran ke-2 dari 6 - Bayar via transfer') === 'Bayar via transfer', 'user_ket menghapus awalan');
check(Periodik_logic::user_ket('Pembayaran ke-2 dari 6') === '', 'user_ket hanya awalan');
check(Periodik_logic::user_ket('Catatan biasa') === 'Catatan biasa', 'user_ket tanpa awalan');

// back: ditolak belum diajukan ulang; sudah diajukan ulang -> lock oleh item pengganti; ditutup -> closed
$rej = item('R', '2026-11', array('nominal' => 500000));
$st = Periodik_logic::state($bulanan, array($rej), '2026-11');
check($st['state'] === 'back' && $st['back']['note'] === 'Salah nominal', 'back');
$resubbed = item('R', '2026-11', array('resub' => 'PI-2026-00002'));
$new = item('W', '2026-11', array('no_item' => 'PI-2026-00002', 'ref' => 'PI-2026-00001'));
check(Periodik_logic::state($bulanan, array($resubbed, $new), '2026-11')['state'] === 'lock', 'lock oleh item pengganti');
$both = Periodik_logic::state($bulanan, array($resubbed, item('R', '2026-11', array('no_item' => 'PI-2026-00002'))), '2026-11');
check($both['state'] === 'back' && $both['back']['no_item'] === 'PI-2026-00002', 'back memakai penolakan terakhir');
$closed = item('R', '2026-11', array('closed_at' => '2026-11-05 10:00:00', 'closed_note' => 'Tagihan batal'));
check(Periodik_logic::state($bulanan, array($closed), '2026-11')['state'] === 'closed', 'closed tersembunyi dari form');
check(Periodik_logic::state($bulanan, array($closed), '2026-12')['state'] === 'free', 'closed hanya berlaku untuk periodenya');

// fdate
check(Periodik_logic::fdate('2026-11-05 10:00:00') === '05 Nov 2026', 'fdate');
check(Periodik_logic::fdate('') === '', 'fdate kosong');

// stepper
$r = Periodik_logic::item_steps(item('W', '2026-11'));
check(array_column($r['dots'], 'c') === array('done', 'cur', '', ''), 'W: titik');
check($r['card']['bd'] === 'Menunggu Approval Pengajuan' && $r['card']['t'] === 'Approval Pengajuan — Direktur', 'W: kartu');
$r = Periodik_logic::item_steps(item('R', '2026-11', array('closed_at' => 'x', 'closed_note' => 'batal')));
check(array_column($r['dots'], 'c') === array('done', 'rej', 'skip', 'skip'), 'R: titik');
check($r['card']['m'][0] === 'Alasan: Salah nominal' && strpos($r['card']['m'][1], 'Ditutup, tidak diajukan ulang (batal)') === 0, 'R: ditutup');
$r = Periodik_logic::item_steps(item('R', '2026-11'));
check(strpos($r['card']['m'][1], 'Belum diajukan ulang') === 0, 'R: belum diajukan ulang');
$r = Periodik_logic::item_steps(item('R', '2026-11', array('resub' => 'PI-2026-00002')));
check(strpos($r['card']['m'][1], 'Diajukan ulang sebagai PI-2026-00002') === 0, 'R: diajukan ulang');

$r = Periodik_logic::item_steps(item('A', '2026-11'), null);
check(array_column($r['dots'], 'c') === array('done', 'done', 'cur', ''), 'A tanpa RP: titik');
check($r['card']['bd'] === 'Menunggu Diajukan Finance' && $r['card']['m'][0] === 'Disetujui approver 04 Nov 2026 09:30', 'A tanpa RP: kartu');
$r = Periodik_logic::item_steps(item('A', '2026-11'), array('state' => 'belum', 'no' => 'PGJ-0003', 'reject' => 'PPN belum dicentang'));
check($r['card']['bd'] === 'Menunggu Diajukan Ulang Finance' && $r['card']['m'][0] === 'Ditolak Finance di PGJ-0003: PPN belum dicentang', 'A ditolak Finance');
$r = Periodik_logic::item_steps(item('A', '2026-11'), array('state' => 'pending', 'no' => 'PGJ-0004'));
check(array_column($r['dots'], 'c') === array('done', 'done', 'done', 'cur') && $r['card']['bd'] === 'Menunggu Approval Request Payment' && $r['dots'][2]['s'] === 'PGJ-0004', 'A pending RP');
$r = Periodik_logic::item_steps(item('A', '2026-11'), array('state' => 'done', 'no' => 'PGJ-0004'));
check(array_column($r['dots'], 'c') === array('done', 'done', 'done', 'done') && $r['card']['bd'] === 'Disetujui Finance', 'A disetujui Finance');

// html: karakter berbahaya di-escape
$html = Periodik_logic::steps_html(item('R', '2026-11', array('note' => '<script>x</script>')));
check(strpos($html, '<script>') === false && strpos($html, '&lt;script&gt;') !== false, 'steps_html escape');
check(strpos($html, 'class="pstp"') !== false && substr_count($html, '<i class=') === 4, 'steps_html 4 titik');

if ($failures) { fwrite(STDERR, "$failures kegagalan\n"); exit(1); }
echo "PASS: Periodik_logic (status baris, masa berlaku, revisi/tutup, stepper, escape)\n";
