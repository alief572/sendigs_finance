<?php
// Jalankan: scratch/runtime/php74/php.exe application/modules/monitoring_piutang/tests/Surat_konfirmasi_test.php
require_once __DIR__ . '/../libraries/Surat_konfirmasi.php';

$n = 0;
function ok($c, $m) { global $n; $n++; if (!$c) { fwrite(STDERR, "FAIL: $m\n"); exit(1); } }
function same($a, $b, $m) { ok($a === $b, "$m (got " . var_export($a, true) . ", want " . var_export($b, true) . ")"); }
$S = 'Surat_konfirmasi';
$today = '2026-11-06';
function inv($o = []) {
    return $o + ['id' => 'I1', 'no_invoice' => '239/STM/2026', 'tgl_invoice' => '2026-05-05', 'customer' => 'PT. INTI', 'project' => 'Inhouse Training', 'company' => '7',
        'company_name' => 'STM', 'nilai' => 22200000, 'saldo' => 22200000, 'sts_close' => '0', 'tgl_terima' => '2026-06-01', 'top_ke' => 2];
}

// can_print: hanya Potensi Macet / Macet, belum close, masih bersisa, sudah diterima
same($S::can_print(inv(), $today), null, 'macet boleh');
same($S::can_print(inv(['tgl_terima' => '2026-08-30']), $today), null, 'potensi (68 hari) boleh');
ok($S::can_print(inv(['tgl_terima' => '2026-09-07']), $today) !== null, 'lancar (60 hari) ditolak');
same($S::can_print(inv(['tgl_terima' => '2026-09-06']), $today), null, '61 hari potensi boleh');
ok($S::can_print(inv(['sts_close' => '1']), $today) !== null, 'close ditolak');
ok($S::can_print(inv(['saldo' => 0]), $today) !== null, 'lunas ditolak');
ok($S::can_print(inv(['tgl_terima' => null]), $today) !== null, 'belum diterima ditolak');

// lama penagihan
same($S::lama_tagih('2026-08-01', $today), 'lebih dari 3 bulan', 'lebih dari 3 bulan');
same($S::lama_tagih('2026-08-06', $today), '3 bulan', 'tepat 3 bulan');
same($S::lama_tagih('2026-10-20', $today), '17 hari', 'kurang dari sebulan');
same($S::lama_tagih('2025-11-06', $today), '12 bulan', 'tepat setahun = 12 bulan');

// teks
same($S::tagihan(2, 'Proj'), 'Tagihan Kedua Proj', 'tagihan kedua'); same($S::tagihan(0, 'Proj'), 'Tagihan Proj', 'tanpa TOP');
same($S::tagihan(11, 'Proj'), 'Tagihan ke-11 Proj', 'TOP di atas 10');
same($S::nomor('STM', $today), '…/STM-Fnc/SK/XI/26', 'nomor placeholder'); same($S::tanggal_id('2026-05-05'), '5 Mei 2026', 'tanggal Indonesia');
same($S::tanggal_id(''), '–', 'tanggal kosong'); same($S::rp(22200000.4), 'Rp. 22.200.000', 'rupiah');

// render STM: kop gambar + footer; belum ada pembayaran -> tanpa baris Sudah dibayar
$html = $S::render($S::data(inv(), $today), '/assets/images/surat/');
ok(strpos($html, '/assets/images/surat/stm_kop_logo.jpg') !== false && strpos($html, 'Fnc/SK/XI/26') !== false, 'kop STM dan nomor');
ok(strpos($html, 'Tagihan Kedua Inhouse Training') !== false && strpos($html, 'Rp. 22.200.000') !== false && strpos($html, '5 Mei 2026') !== false, 'isi data');
ok(strpos($html, 'Sudah dibayar') === false, 'tanpa baris dibayar bila belum ada pembayaran');
ok(strpos($html, 'sudah lebih dari 5 bulan') !== false, 'lama penagihan: ' . (preg_match('/sudah <\/b>|<b>sudah[^<]*/', $html, $m) ? $m[0] : ''));
ok(strpos($html, 'Tati Rina') !== false && strpos($html, 'sspm@sentral-sistem.com') !== false, 'penandatangan dan footer STM');

// pembayaran sebagian -> ada baris Sudah dibayar dan Sisa tagihan
$part = $S::render($S::data(inv(['saldo' => 8700000]), $today), '/x/');
ok(strpos($part, 'Sudah dibayar</td><td>: Rp. 13.500.000') !== false && strpos($part, 'Sisa tagihan</td><td>: <b>Rp. 8.700.000') !== false, 'baris dibayar dan sisa');

// company tanpa profil -> kop teks nama company, tanpa gambar dan footer
$other = $S::render($S::data(inv(['company' => '3', 'company_name' => 'Sustain']), $today), '/x/');
ok(strpos($other, 'class="kop-default">Sustain<') !== false && strpos($other, 'stm_kop_logo') === false && strpos($other, 'class="footer"') === false, 'kop default');

// escape: nama customer berisi HTML tidak boleh lolos
$xss = $S::render($S::data(inv(['customer' => '<script>alert(1)</script>', 'project' => 'a"b']), $today), '/x/');
ok(strpos($xss, '<script>alert(1)</script>') === false && strpos($xss, '&lt;script&gt;alert(1)&lt;/script&gt;') !== false, 'customer di-escape');
ok(strpos($xss, 'a&quot;b') !== false, 'project di-escape');

echo "PASS: $n assertions\n";
