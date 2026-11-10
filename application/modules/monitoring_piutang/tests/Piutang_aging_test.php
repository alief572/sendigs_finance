<?php
// Jalankan: scratch/runtime/php74/php.exe application/modules/monitoring_piutang/tests/Piutang_aging_test.php
require_once __DIR__ . '/../libraries/Piutang_aging.php';

$n = 0;
function ok($cond, $msg) { global $n; $n++; if (!$cond) { fwrite(STDERR, "FAIL: $msg\n"); exit(1); } }
function same($a, $b, $msg) { ok($a === $b, "$msg (got " . var_export($a, true) . ", want " . var_export($b, true) . ")"); }
function row($o = []) {
    return $o + ['id' => 'INV1', 'no_invoice' => 'INV-001', 'tgl_invoice' => '2026-10-01', 'customer' => 'PT A', 'project' => 'P', 'company' => '7',
        'nilai' => 1000, 'saldo' => 1000, 'sts_close' => '0', 'tgl_kirim' => null, 'kirim_via' => null, 'tgl_terima' => null, 'plan_bayar' => null,
        'extra_fu' => []];
}
$A = 'Piutang_aging';
$today = '2026-11-06';

// umur + kategori: batas 60/61/90/91, terima masa depan = 0, tanpa terima = null
same($A::umur('2026-09-07', $today), 60, 'umur 60'); same($A::kategori(60), 'lancar', '60 lancar');
same($A::kategori(61), 'potensi', '61 potensi'); same($A::kategori(90), 'potensi', '90 potensi'); same($A::kategori(91), 'macet', '91 macet');
same($A::kategori(0), 'lancar', '0 lancar'); same($A::umur('2026-12-01', $today), 0, 'masa depan 0'); same($A::umur(null, $today), null, 'belum terima');
same($A::umur('2025-12-31', '2026-01-01'), 1, 'lintas tahun');

// summary: lancar+potensi+macet = total; belum terima terpisah; close dan lunas keluar
$rows = [row(['id' => 'a', 'tgl_terima' => '2026-10-30', 'saldo' => 100]), row(['id' => 'b', 'tgl_terima' => '2026-08-30', 'saldo' => 200]),
    row(['id' => 'c', 'tgl_terima' => '2026-06-01', 'saldo' => 300]), row(['id' => 'd', 'saldo' => 50]),
    row(['id' => 'e', 'tgl_terima' => '2026-06-01', 'saldo' => 0]), row(['id' => 'f', 'tgl_terima' => '2026-06-01', 'saldo' => 70, 'sts_close' => '1'])];
$s = $A::summary($rows, $today);
same($s['lancar']['cents'], 10000, 'lancar'); same($s['potensi']['cents'], 20000, 'potensi'); same($s['macet']['cents'], 30000, 'macet');
same($s['total']['cents'], 60000, 'total'); same($s['belum']['cents'], 5000, 'belum'); same($s['grand_cents'], 65000, 'grand');
same($s['macet']['oldest'], 158, 'oldest macet'); same($s['macet']['pct'], 50.0, 'pct');

// ar_status berurutan
same($A::ar_status(row()), 'belum_kirim', 's1'); same($A::ar_status(row(['tgl_kirim' => '2026-10-02'])), 'dikirim', 's2');
same($A::ar_status(row(['tgl_kirim' => '2026-10-02', 'tgl_terima' => '2026-10-03'])), 'diterima', 's3');
same($A::ar_status(row(['plan_bayar' => '2026-12-01', 'tgl_terima' => '2026-10-03'])), 'plan_bayar', 's4');
same($A::ar_status(row(['sts_close' => '1', 'plan_bayar' => '2026-12-01'])), 'close', 's5');

// jadwal FU: +3, +7, +14, +14, tambahan +14 dari FU sebelumnya DILAKUKAN
$r = row(['tgl_kirim' => '2026-10-01', 'tgl_terima' => '2026-10-10']);
same($A::default_fu_plan($r, 1), '2026-10-13', 'fu1'); same($A::fu_stage($r), '1', 'stage 1');
$r['fu1_aktual'] = '2026-10-20'; same($A::default_fu_plan($r, 2), '2026-10-27', 'fu2'); same($A::fu_stage($r), '2', 'stage 2');
$r['fu2_aktual'] = '2026-10-27'; same($A::default_fu_plan($r, 3), '2026-11-10', 'fu3');
$r['fu3_aktual'] = '2026-11-01'; same($A::default_fu_plan($r, 4), '2026-11-15', 'fu4');
$r['fu4_aktual'] = '2026-11-02'; same($A::fu_stage($r), 'selesai', 'selesai setelah fu4'); same($A::default_fu_plan($r, 5), '2026-11-16', 'fu5');
$r['extra_fu'] = [['ke' => 5, 'plan' => '2026-11-16', 'aktual' => null, 'ket' => null]]; same($A::fu_stage($r), 'tambahan', 'tambahan');
same($A::fu_late($r, '2026-11-17'), true, 'late'); same($A::fu_late($r, '2026-11-16'), false, 'tepat jadwal bukan late');
$r['plan_bayar'] = '2026-12-01'; same($A::fu_stage($r), 'selesai', 'plan bayar menghentikan FU'); same($A::fu_late($r, '2027-01-01'), false, 'plan bayar tidak late');

// label Baru: hari ini dan 2 hari lalu ya; 3 hari lalu tidak
same($A::is_baru(['status_changed_date' => '2026-11-06 08:00:00'], $today), true, 'baru hari ini');
same($A::is_baru(['status_changed_date' => '2026-11-04 23:59:00'], $today), true, 'baru 2 hari');
same($A::is_baru(['status_changed_date' => '2026-11-03 10:00:00'], $today), false, 'bukan baru 3 hari');

// ar_visible + go-live
same($A::ar_visible(row(['saldo' => 0, 'tgl_invoice' => '2026-10-01']), ''), false, 'lunas belum kirim tanpa go-live disembunyikan');
same($A::ar_visible(row(['saldo' => 0, 'tgl_invoice' => '2026-10-01']), '2026-10-01'), true, 'go-live tampil');
same($A::ar_visible(row(['saldo' => 0, 'tgl_invoice' => '2026-09-30']), '2026-10-01'), false, 'sebelum go-live');
same($A::ar_visible(row(['saldo' => 0, 'tgl_kirim' => '2026-09-01', 'tgl_invoice' => '2026-01-01']), ''), true, 'sudah dikirim tampil');
same($A::ar_visible(row(['sts_close' => '1']), ''), false, 'close tidak di daftar');

// close_block_reason: plan bayar + lunas persis
ok($A::close_block_reason(row(['saldo' => 0])) !== null, 'tanpa plan ditolak');
ok($A::close_block_reason(row(['plan_bayar' => '2026-12-01', 'saldo' => 0.01])) !== null, 'sisa 0.01 ditolak');
same($A::close_block_reason(row(['plan_bayar' => '2026-12-01', 'saldo' => 0])), null, 'boleh close');
same($A::close_block_reason(row(['plan_bayar' => '2026-12-01', 'saldo' => 0, 'sts_close' => '1'])), 'Invoice sudah close.', 'close ganda');

// plan_nominal
same($A::plan_nominal(row(['saldo' => 5, 'plan_bayar_nominal' => '900.00'])), 900.0, 'nominal tersimpan'); same($A::plan_nominal(row(['saldo' => 5])), 5.0, 'cadangan saldo');

// validate_ar
function errs($r, $old = []) { return Piutang_aging::validate_ar($r, $old); }
ok(count(errs(row(['tgl_kirim' => '2026-10-02', 'kirim_via' => 'JNE', 'no_resi' => 'JNE123']))) === 0, 'kirim valid');
ok(count(errs(row(['tgl_kirim' => '2026-10-02', 'kirim_via' => 'JNE']))) > 0, 'JNE tanpa no resi ditolak');
ok(count(errs(row(['tgl_kirim' => '2026-10-02', 'kirim_via' => 'Driver', 'ket_kirim' => 'Pak Budi']))) === 0, 'Driver tanpa resi boleh');
ok(count(errs(row(['tgl_kirim' => '2026-10-02', 'kirim_via' => 'Grab']))) > 0, 'via luar pilihan');
ok(count(errs(row(['tgl_kirim' => '2026-10-02', 'kirim_via' => 'Kurir X']), ['kirim_via' => 'Kurir X'])) === 0, 'via lama tetap boleh');
ok(count(errs(row(['tgl_kirim' => '2026-10-02']))) > 0, 'kirim tanpa via');
ok(count(errs(row(['tgl_terima' => '2026-10-03']))) > 0, 'terima tanpa kirim');
ok(count(errs(row(['tgl_kirim' => '2026-10-05', 'kirim_via' => 'JNE', 'tgl_terima' => '2026-10-03']))) > 0, 'terima sebelum kirim');
ok(count(errs(row(['fu1_aktual' => '2026-10-03', 'fu1_ket' => 'x', 'fu_pic_nama' => 'B', 'fu_pic_telp' => '08123456789']))) > 0, 'FU tanpa terima');
ok(count(errs(row(['plan_bayar' => '2026-12-01', 'ket_plan_bayar' => 'x']))) > 0, 'plan tanpa terima');
$base = row(['tgl_kirim' => '2026-10-01', 'kirim_via' => 'Driver', 'tgl_terima' => '2026-10-02', 'bukti_terima' => 'uploads/bukti_terima_invoice/a.pdf', 'bukti_terima_nama' => 'tanda terima.pdf',
    'fu_pic_nama' => 'B', 'fu_pic_telp' => '+62 812-3456-7890']);
ok(count(errs(array_merge($base, ['bukti_terima' => null]))) > 0, 'terima tanpa bukti ditolak');
ok(in_array('Bukti terima (file) wajib diunggah.', errs(array_merge($base, ['bukti_terima' => '']))), 'pesan bukti wajib');
ok(count(errs(row(['tgl_kirim' => '2026-10-01', 'kirim_via' => 'JNE', 'no_resi' => 'R1']))) === 0, 'tanpa tanggal terima bukti tidak diminta');
ok(count(errs($base)) === 0, 'terima + bukti valid');
ok(count(errs($base + ['fu1_aktual' => '2026-10-05', 'fu1_ket' => 'ok'])) === 0, 'FU1 valid');
ok(count(errs($base + ['fu1_aktual' => '2026-10-05'])) > 0, 'FU tanpa keterangan');
ok(count(errs(['fu_pic_nama' => null, 'fu1_aktual' => '2026-10-05', 'fu1_ket' => 'ok'] + $base)) > 0, 'FU tanpa PIC');
ok(count(errs(['fu_pic_telp' => '1234'] + $base)) > 0, 'telp terlalu pendek');
ok(count(errs(['fu_pic_telp' => '08ab34567890'] + $base)) > 0, 'telp huruf');
ok(count(errs($base + ['fu2_aktual' => '2026-10-05', 'fu2_ket' => 'ok'])) > 0, 'FU2 sebelum FU1');
ok(count(errs(array_merge($base, ['plan_bayar' => '2026-12-01']))) > 0, 'plan tanpa keterangan');
ok(count(errs(array_merge($base, ['plan_bayar' => '2020-01-01', 'ket_plan_bayar' => 'x']))) === 0, 'tanggal plan bebas');

// lock_saved
$old = row(['tgl_kirim' => '2026-10-01', 'kirim_via' => 'JNE', 'ket_kirim' => 'resi A', 'tgl_terima' => '2026-10-02', 'ket_terima' => 'Budi',
    'fu_pic_nama' => 'B', 'fu_pic_telp' => '08123456789']);
$m = $A::lock_saved(['tgl_kirim' => '2026-09-01', 'kirim_via' => 'Driver', 'ket_kirim' => 'x'] + $old, $old);
same($m['tgl_kirim'], '2026-10-01', 'kirim terkunci setelah terima'); same($m['kirim_via'], 'JNE', 'via terkunci');
$m = $A::lock_saved(['tgl_terima' => '2026-10-04', 'ket_terima' => 'Ani'] + $old, $old);
same([$m['tgl_terima'], $m['ket_terima']], ['2026-10-02', 'Budi'], 'terima dan keterangan terkunci begitu tanggal terima tersimpan (tanpa menunggu FU1)');
$m = $A::lock_saved(['tgl_kirim' => '2026-10-01', 'kirim_via' => 'JNE', 'tgl_terima' => '2026-10-04'] + $old, ['tgl_terima' => null] + $old);
same($m['tgl_terima'], '2026-10-04', 'tanggal terima pertama kali masih bisa diisi');
$m = $A::lock_saved(['tgl_terima' => '2026-10-02'] + $old, $old); same($m['tgl_terima'], '2026-10-02', 'simpan ulang dengan nilai sama tetap utuh');
$m = $A::lock_saved(['tgl_terima' => '', 'fu_pic_nama' => ''] + $old, $old); same($m['tgl_terima'], '2026-10-02', 'terima tidak dikosongkan'); same($m['fu_pic_nama'], 'B', 'PIC tidak dikosongkan');
// bukti terima: terkunci begitu tanggal terima tersimpan; data lama tanpa bukti masih bisa dilengkapi sekali, lalu terkunci
$oldB = ['bukti_terima' => 'u/lama.pdf', 'bukti_terima_nama' => 'lama.pdf'] + $old;
$m = $A::lock_saved(['bukti_terima' => 'u/baru.png', 'bukti_terima_nama' => 'baru.png'] + $oldB, $oldB);
same([$m['bukti_terima'], $m['bukti_terima_nama']], ['u/lama.pdf', 'lama.pdf'], 'bukti terkunci begitu tanggal terima tersimpan (tanpa menunggu FU1)');
$oldNoB = ['bukti_terima' => null, 'bukti_terima_nama' => null] + $old;
$m = $A::lock_saved(['bukti_terima' => 'u/susulan.pdf', 'bukti_terima_nama' => 'susulan.pdf', 'tgl_terima' => '2026-10-09', 'ket_terima' => 'X'] + $oldNoB, $oldNoB);
same([$m['bukti_terima'], $m['bukti_terima_nama']], ['u/susulan.pdf', 'susulan.pdf'], 'data lama tanpa bukti: bukti masih bisa dilengkapi');
same([$m['tgl_terima'], $m['ket_terima']], ['2026-10-02', 'Budi'], 'data lama tanpa bukti: tanggal dan keterangan tetap terkunci');
$oldDone = ['bukti_terima' => 'u/susulan.pdf', 'bukti_terima_nama' => 'susulan.pdf'] + $old;
$m = $A::lock_saved(['bukti_terima' => 'u/lagi.pdf', 'bukti_terima_nama' => 'lagi.pdf'] + $oldDone, $oldDone);
same($m['bukti_terima'], 'u/susulan.pdf', 'setelah bukti susulan terisi, bukti terkunci');
$m = $A::lock_saved(['bukti_terima' => '', 'bukti_terima_nama' => ''] + $oldB, $oldB);
same([$m['bukti_terima'], $m['bukti_terima_nama']], ['u/lama.pdf', 'lama.pdf'], 'bukti tidak dikosongkan, nama ikut file lama');
$m = $A::lock_saved(['bukti_terima_nama' => 'ganti-nama-saja.pdf'] + $oldB + ['bukti_terima' => 'u/lama.pdf'], $oldB);
same($m['bukti_terima_nama'], 'lama.pdf', 'nama tidak bisa diubah tanpa file baru');
$oldB1 = ['fu1_plan' => '2026-10-05', 'fu1_aktual' => '2026-10-05', 'fu1_ket' => 'ok'] + $oldB;
$m = $A::lock_saved(['bukti_terima' => 'u/baru.png', 'bukti_terima_nama' => 'baru.png'] + $oldB1, $oldB1);
same([$m['bukti_terima'], $m['bukti_terima_nama']], ['u/lama.pdf', 'lama.pdf'], 'bukti terkunci setelah FU1');
$m = $A::lock_saved(['fu_pic_nama' => 'B2'] + $oldB1, $oldB1);
same($m['bukti_terima'], 'u/lama.pdf', 'simpan tanpa memilih file mempertahankan bukti');
$old1 = ['fu1_plan' => '2026-10-05', 'fu1_aktual' => '2026-10-05', 'fu1_ket' => 'ok', 'no_resi' => 'R1'] + $old;
$m = $A::lock_saved(['tgl_terima' => '2026-10-09', 'no_resi' => 'R2', 'fu1_aktual' => '2026-10-06'] + $old1, $old1);
same($m['tgl_terima'], '2026-10-02', 'terima terkunci setelah FU1'); same($m['no_resi'], 'R1', 'resi terkunci'); same($m['fu1_aktual'], '2026-10-06', 'FU terakhir dikoreksi');
$m = $A::lock_saved(['fu1_aktual' => ''] + $old1, $old1); same($m['fu1_aktual'], '2026-10-05', 'FU terakhir tidak dikosongkan');
$m = $A::lock_saved(['no_resi' => 'R9'] + $old, $old); same($m['no_resi'], 'R9', 'resi diisi belakangan');
$old2 = ['fu2_plan' => '2026-10-12', 'fu2_aktual' => '2026-10-12', 'fu2_ket' => 'ok2'] + $old1;
$m = $A::lock_saved(['fu1_aktual' => '2026-10-01', 'fu1_ket' => 'ubah'] + $old2, $old2);
same($m['fu1_aktual'], '2026-10-05', 'FU1 terkunci setelah FU2'); same($m['fu1_ket'], 'ok', 'ket FU1 terkunci');
$oldP = ['plan_bayar' => '2026-12-01', 'ket_plan_bayar' => 'x'] + $old2;
same($A::lock_saved(['plan_bayar' => '2027-01-01', 'fu2_ket' => 'z'] + $oldP, $oldP), $oldP, 'plan bayar mengunci semua');
// FU tambahan: yang sudah dilakukan tidak hilang, yang belum dilakukan boleh dihapus
$oldX = ['fu3_aktual' => '2026-10-20', 'fu3_ket' => 'k3', 'fu4_aktual' => '2026-11-03', 'fu4_ket' => 'k4',
    'extra_fu' => [['ke' => 5, 'plan' => '2026-11-17', 'aktual' => '2026-11-05', 'ket' => 'k5'], ['ke' => 6, 'plan' => '2026-11-19', 'aktual' => null, 'ket' => null]]] + $old2;
$m = $A::lock_saved(['extra_fu' => []] + $oldX, $oldX);
same(count($m['extra_fu']), 1, 'FU5 dilakukan tidak bisa dihapus, FU6 belum dilakukan dihapus'); same($m['extra_fu'][0]['ke'], 5, 'sisa FU5');

// filter_ar_rows: tab, cari tanggal/nominal, filter tidak valid
$set = [row(['id' => '1', 'no_invoice' => 'INV-A', 'saldo' => 1500000]),
    row(['id' => '2', 'no_invoice' => 'INV-B', 'tgl_kirim' => '2026-10-02', 'kirim_via' => 'JNE']),
    row(['id' => '3', 'no_invoice' => 'INV-C', 'tgl_kirim' => '2026-10-02', 'tgl_terima' => '2026-10-03', 'company' => '3']),
    row(['id' => '4', 'no_invoice' => 'INV-D', 'tgl_kirim' => '2026-10-02', 'tgl_terima' => '2026-10-03', 'plan_bayar' => '2026-12-05']),
    row(['id' => '5', 'no_invoice' => 'INV-E', 'sts_close' => '1', 'close_date' => '2026-11-01 10:00:00', 'saldo' => 0])];
$ids = function ($rows) { return implode(',', array_map(function ($r) { return $r['id']; }, $rows)); };
same($ids($A::filter_ar_rows($set, ['tab' => 'all'], $today)), '1,2,3,4', 'all tanpa close');
same($ids($A::filter_ar_rows($set, ['tab' => '1'], $today)), '1', 'tab 1'); same($ids($A::filter_ar_rows($set, ['tab' => '2'], $today)), '2', 'tab 2');
same($ids($A::filter_ar_rows($set, ['tab' => '3'], $today)), '3', 'tab 3'); same($ids($A::filter_ar_rows($set, ['tab' => '4', 'plan_bulan' => '12', 'plan_tahun' => '2026'], $today)), '4', 'tab 4');
same($ids($A::filter_ar_rows($set, ['tab' => '4', 'plan_bulan' => '11'], $today)), '', 'tab 4 bulan lain');
same($ids($A::filter_ar_rows($set, ['tab' => 'late'], $today)), '3', 'late: FU1 jadwal 06-10 lewat');
same($ids($A::filter_ar_rows($set, ['tab' => 'close', 'close_tahun' => '2026'], $today)), '5', 'tab close'); same($ids($A::filter_ar_rows($set, ['tab' => 'close', 'close_tahun' => '2025'], $today)), '', 'close tahun lain');
same($ids($A::filter_ar_rows($set, ['tab' => 'all', 'company' => '3'], $today)), '3', 'company');
same($ids($A::filter_ar_rows($set, ['tab' => 'all', 'cari' => '02/10/2026'], $today)), '2,3,4', 'cari tanggal d/m/Y');
same($ids($A::filter_ar_rows($set, ['tab' => 'all', 'cari' => '1.500.000'], $today)), '1', 'cari nominal');
same($ids($A::filter_ar_rows($set, ['tab' => 'all', 'fu' => 'selesai'], $today)), '4', 'filter FU selesai');
same($ids($A::filter_ar_rows($set, ['tab' => 'all', 'fu' => '1'], $today)), '3', 'filter FU1');
foreach ([['tab' => 'x'], ['fu' => 'x'], ['plan_bulan' => '13']] as $bad) {
    $threw = false;
    try { $A::filter_ar_rows($set, $bad, $today); } catch (InvalidArgumentException $e) { $threw = true; }
    ok($threw, 'filter tidak valid harus ditolak');
}
$c = $A::tab_counts($set, [], $today); same($c['all'], 4, 'count all'); same($c['close'], 1, 'count close');
$sorted = $A::sort_baru([row(['id' => 'x', 'status_changed_date' => '2026-10-01 00:00:00']), row(['id' => 'y', 'status_changed_date' => '2026-11-06 01:00:00'])], $today);
same($sorted[0]['id'], 'y', 'Baru paling atas');

// Monitoring: basis, daftar per kategori, rekap per customer
$mon = [row(['id' => 'a', 'customer' => 'X', 'company' => '7', 'tgl_terima' => '2026-10-30', 'saldo' => 100, 'no_invoice' => 'INV-A']),
    row(['id' => 'b', 'customer' => 'X', 'company' => '7', 'tgl_terima' => '2026-08-30', 'saldo' => 200, 'no_invoice' => 'INV-B']),
    row(['id' => 'c', 'customer' => 'Y', 'company' => '3', 'tgl_terima' => '2026-06-01', 'saldo' => 300, 'no_invoice' => 'INV-C']),
    row(['id' => 'd', 'customer' => 'Y', 'company' => '3', 'saldo' => 50, 'no_invoice' => 'INV-D']),
    row(['id' => 'e', 'customer' => 'Y', 'company' => '3', 'tgl_terima' => '2026-06-01', 'saldo' => 0]),
    row(['id' => 'f', 'customer' => 'Y', 'company' => '3', 'tgl_terima' => '2026-06-01', 'saldo' => 9, 'sts_close' => '1'])];
$base = $A::monitoring_base($mon, [], $today);
same($ids($base), 'a,b,c,d', 'basis: tanpa lunas dan close');
same($ids($A::aged_list($base, 'all', $today)), 'c,b,a', 'aged terlama dulu, belum diterima keluar');
same($ids($A::aged_list($base, 'potensi', $today)), 'b', 'kategori potensi');
same($A::summary($base, $today)['grand_cents'], 65000, 'summary dari basis = total keseluruhan');
$by = $A::monitoring_base($mon, ['company' => '3'], $today); same($ids($by), 'c,d', 'filter company');
same($A::summary($by, $today)['belum']['cents'], 5000, 'card belum diterima ikut filter');
same($ids($A::monitoring_base($mon, ['customer' => 'X'], $today)), 'a,b', 'filter customer persis');
same($ids($A::monitoring_base($mon, ['cari' => 'inv-c'], $today)), 'c', 'cari no invoice');
$rk = $A::rekap_customer($base, $today);
same($rk[0]['customer'], 'X', 'rekap seri total: nama menentukan'); same($rk[1]['macet'], 30000, 'rekap macet Y'); same($rk[0]['total'], 30000, 'rekap total X'); same($rk[0]['count'], 2, 'rekap count X');
$threw = false; try { $A::monitoring_base($mon, ['kategori' => 'x'], $today); } catch (InvalidArgumentException $e) { $threw = true; }
ok($threw, 'kategori tidak valid ditolak');

// Dashboard Plan Bayar
same($A::ketepatan('2026-10-05', '2026-10-05')['label'], 'Sesuai janji', 'tepat'); same($A::ketepatan('2026-10-03', '2026-10-05')['label'], 'Lebih cepat 2 hari', 'cepat');
same($A::ketepatan('2026-10-08', '2026-10-05')['label'], 'Telat 3 hari', 'telat'); same($A::ketepatan('2026-10-08', null), ['days' => null, 'label' => '–'], 'tanpa plan');
same($A::posisi(row(['saldo' => 0, 'sts_close' => '1'])), 'Lunas & close', 'posisi close'); same($A::posisi(row(['saldo' => 0])), 'Lunas, belum close', 'posisi lunas');
same($A::posisi(row(['saldo' => 5])), 'Bayar sebagian', 'posisi sebagian');
$inv = [
    'p1' => row(['id' => 'p1', 'no_invoice' => 'I1', 'plan_bayar' => '2026-10-20', 'plan_bayar_nominal' => '1000', 'saldo' => 0, 'sts_close' => '1', 'company' => '7']),
    'p2' => row(['id' => 'p2', 'no_invoice' => 'I2', 'plan_bayar' => '2026-10-05', 'plan_bayar_nominal' => '500', 'saldo' => 200, 'company' => '7']),
    'p3' => row(['id' => 'p3', 'no_invoice' => 'I3', 'plan_bayar' => '2026-12-01', 'saldo' => 300, 'company' => '3']),
    'p4' => row(['id' => 'p4', 'no_invoice' => 'I4', 'plan_bayar' => '2025-10-05', 'saldo' => 1, 'company' => '7']),
    'p5' => row(['id' => 'p5', 'no_invoice' => 'I5', 'saldo' => 50, 'company' => '7'])];
$plan = $A::plan_rows(array_values($inv), 2026, '', '2026-11-06');
same($ids($plan), 'p2,p1,p3', 'plan: tahun terpilih, termasuk close, urut tanggal');
same($plan[1]['nilai_plan'], 1000.0, 'nilai plan close tetap'); same($plan[2]['nilai_plan'], 300.0, 'cadangan sisa sekarang');
same($ids($A::plan_rows(array_values($inv), 2026, '3', '2026-11-06')), 'p3', 'plan filter company');
$receipts = [['id_inv' => 'p2', 'no_penerimaan' => 'R1', 'tanggal' => '2026-10-07', 'nominal' => '300'], ['id_inv' => 'p5', 'no_penerimaan' => 'R2', 'tanggal' => '2026-11-02', 'nominal' => '50'],
    ['id_inv' => 'p1', 'no_penerimaan' => 'R3', 'tanggal' => '2026-10-20', 'nominal' => '1000'], ['id_inv' => 'p4', 'no_penerimaan' => 'R4', 'tanggal' => '2025-10-01', 'nominal' => '9'],
    ['id_inv' => 'zz', 'no_penerimaan' => 'R5', 'tanggal' => '2026-10-01', 'nominal' => '9']];
$real = $A::realisasi_rows($receipts, $inv, 2026, '', '2026-11-06');
same(implode(',', array_column($real, 'no_penerimaan')), 'R1,R3,R2', 'realisasi: tahun ini, semua invoice termasuk tanpa plan dan close');
same($real[0]['ketepatan'], 'Telat 2 hari', 'ketepatan R1'); same($real[1]['ketepatan'], 'Sesuai janji', 'ketepatan R3'); same($real[2]['ketepatan'], '–', 'tanpa plan');
same($real[1]['posisi'], 'Lunas & close', 'posisi R3');
$sum = $A::dashboard_summary($plan, $real, 2026, '2026-11-06');
same(count($sum), 12, '12 bulan'); same($sum[9]['plan_count'], 2, 'okt plan 2'); same($sum[9]['plan_cents'], 150000, 'okt plan nominal'); same($sum[9]['real_cents'], 130000, 'okt realisasi');
same($sum[9]['lewat'], true, 'okt lewat: p2 belum close'); same($sum[10]['current'], true, 'nov bulan ini'); same($sum[10]['lewat'], false, 'nov bukan lewat');
same($sum[11]['lewat'], false, 'des belum lewat'); same($sum[10]['real_count'], 1, 'nov realisasi tanpa plan');
$closedOnly = $A::dashboard_summary($A::plan_rows([$inv['p1']], 2026, '', '2026-11-06'), [], 2026, '2026-11-06');
same($closedOnly[9]['lewat'], false, 'bulan lewat tapi semua close bukan Lewat');
same($A::dashboard_summary([], [], 2025, '2026-11-06')[11]['lewat'], false, 'tahun lalu tanpa invoice terbuka');
same(count($A::by_month($real, 10)), 2, 'by_month'); same(count($A::by_month($real, 'all')), 3, 'by_month all');

// Filter company grup (STM, VUCA, SUSTAIN): STM = id 7, 6 (STM-Sustain), 1 (STM-Vuca); VUCA = 4; SUSTAIN = 3; id di luar grup (5) hanya muncul di "Semua".
same(array_column($A::company_options(), 'id'), ['STM', 'VUCA', 'SUSTAIN'], 'dropdown hanya 3 pilihan berurutan');
same(array_column($A::company_options(), 'nm_company'), ['STM', 'VUCA', 'SUSTAIN'], 'label dropdown = nama grup');
$cg = [];
foreach (['7' => 'STM', '6' => 'STM-Sustain', '1' => 'STM-Vuca', '4' => 'Vuca', '3' => 'Sustain', '5' => 'Lain'] as $cid => $nm) {
    $cg[] = row(['id' => 'c' . $cid, 'company' => (string) $cid, 'tgl_terima' => '2026-10-30', 'saldo' => 100, 'plan_bayar' => '2026-11-20', 'no_invoice' => 'I-' . $nm]);
}
$cgById = [];
foreach ($cg as $r) { $cgById[$r['id']] = $r; }
$grp = function ($company) use ($A, $cg, $cgById, $ids, $today) {
    $sets = [
        'ar' => $ids($A::filter_ar_rows($cg, ['tab' => 'all', 'company' => $company], $today)),
        'monitoring' => $ids($A::monitoring_base($cg, ['company' => $company], $today)),
        'plan' => $ids($A::plan_rows($cg, 2026, $company, $today)),
    ];
    $rec = [];
    foreach ($cg as $r) { $rec[] = ['id_inv' => $r['id'], 'no_penerimaan' => 'R-' . $r['id'], 'tanggal' => '2026-11-02', 'nominal' => '10']; }
    $sets['realisasi'] = implode(',', array_map(function ($x) { return substr($x['no_penerimaan'], 2); }, $A::realisasi_rows($rec, $cgById, 2026, $company, $today)));
    return $sets;
};
foreach (['STM' => 'c7,c6,c1', 'VUCA' => 'c4', 'SUSTAIN' => 'c3', '' => 'c7,c6,c1,c4,c3,c5'] as $want => $expect) {
    foreach ($grp($want) as $fn => $got) {
        $g = array_filter(explode(',', $got));
        $e = explode(',', $expect);
        sort($g);
        sort($e);
        same(array_values($g), $e, "company '$want' di $fn");
    }
}
// kompatibel mundur: id numerik tetap cocok persis (Follow Up Piutang dan Plan Bayar masih mengirim id)
foreach (['7' => 'c7', '6' => 'c6', '1' => 'c1', '4' => 'c4', '3' => 'c3', '5' => 'c5'] as $cid => $only) {
    same($ids($A::monitoring_base($cg, ['company' => (string) $cid], $today)), $only, "company id $cid tetap persis");
}
same($ids($A::monitoring_base($cg, ['company' => '99'], $today)), '', 'id tak dikenal tidak cocok apa pun');
same($A::company_match('4', ' '), true, 'filter hanya spasi = semua');
same($A::company_match(null, 'STM'), false, 'company null tidak masuk grup');
same($A::company_match('7', 'stm'), false, 'kunci grup peka huruf besar (dropdown selalu mengirim huruf besar)');
// nama company asli tetap tampil di baris (filter tidak mengubah isi baris)
$stm = $A::monitoring_base($cg, ['company' => 'STM'], $today);
same(array_column($stm, 'company'), ['7', '6', '1'], 'baris hasil filter tidak diubah');

echo "PASS: $n assertions\n";
