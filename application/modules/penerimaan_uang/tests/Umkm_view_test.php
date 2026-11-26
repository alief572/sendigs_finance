<?php
// Render consulting_process.php untuk invoice UMKM dan PKP: judul kolom, keterangan baris, peringatan COA, meta JS (akun PPh Final).
// Murni, tanpa DB/browser. Jalankan: php application/modules/penerimaan_uang/tests/Umkm_view_test.php
ob_start(); require __DIR__ . '/concept_fixture.php'; ob_end_clean();
require_once dirname(__DIR__, 3) . '/helpers/mockup_format_helper.php';

function umkmViewHtml($tax, $choice) {
    $db = createFixtures();
    $db->alloc_details[0]['nominal_debit'] = '1997500.00'; $db->alloc_splits[0]['nominal'] = '1997500.00';
    $db->invoices[] = makeInvoice(1, 'V-1', $tax, 200000000, 183333333, $tax === 'PKP' ? 24000000 : 0, 0, $tax === 'PKP' ? 224000000 : 200000000, $tax === 'PKP' ? 224000000 : 200000000);
    $ci = new MockCi($db); $engine = new Consulting_receipt($ci);
    $ref = new ReflectionProperty('Consulting_receipt', 's'); $ref->setAccessible(true); $ref->setValue($engine, new CustomService($ci));
    $preview = $engine->preview(array('choose_inv' => array(1), 'id_alokasi' => 100, 'pph23_dipotong' => $choice));
    extract($preview);
    ob_start(); include dirname(__DIR__) . '/views/consulting_process.php'; return ob_get_clean();
}
function viewCheck($ok, $m) { static $n = 0; $n++; if (!$ok) throw new RuntimeException('GAGAL: ' . $m); $GLOBALS['viewChecks'] = $n; }

$y = umkmViewHtml('UMKM', 'Y');
viewCheck(strpos($y, 'PPH 23 / PPh Final') !== false, 'UMKM: judul kolom PPH 23 / PPh Final');
viewCheck(strpos($y, 'default 2% Penjualan (UMKM 0,5%)') !== false, 'UMKM dipotong: petunjuk default 0,5%');
viewCheck(strpos($y, 'PPh Final 0,5% dipotong customer') !== false, 'UMKM dipotong: keterangan baris');
viewCheck(strpos($y, 'data-umkm-coa-warning') !== false && strpos($y, 'Beban PPh Final dan Hutang PPh Final belum memiliki nomor COA') !== false, 'UMKM: peringatan COA kosong menyebut kedua akun');
viewCheck(strpos($y, '"tax_treatment":"UMKM"') !== false && strpos($y, '"pph_final_expense"') !== false && strpos($y, '"pph_final_payable"') !== false, 'UMKM: meta JS memuat akun PPh Final');
viewCheck(strpos($y, '"pph_label":"PPh Final 0,5%"') !== false, 'UMKM: pph_label di meta JS');
viewCheck(strpos($y, 'value="10,000.00"') !== false || strpos($y, 'value="10000.00"') !== false, 'UMKM dipotong: nilai default PPh Final 10.000');

$n = umkmViewHtml('UMKM', 'N');
viewCheck(strpos($n, 'PPh Final 0,5% setor sendiri: 10,000') !== false, 'UMKM setor sendiri: keterangan baris dengan nominal');
viewCheck(strpos($n, 'name="pph_actual_1" value="0.00"') !== false, 'UMKM setor sendiri: PPh input 0');

$p = umkmViewHtml('PKP', 'Y');
viewCheck(strpos($p, 'PPH 23 / PPh Final') === false && strpos($p, '(UMKM 0,5%)') === false, 'PKP: judul kolom tidak berubah');
viewCheck(strpos($p, 'data-umkm-coa-warning') === false && strpos($p, 'data-umkm-pph-note') === false, 'PKP: tanpa peringatan/keterangan UMKM');
viewCheck(strpos($p, '"pph_label":"PPh 23"') !== false, 'PKP: pph_label PPh 23');
echo "PASS: {$GLOBALS['viewChecks']} cek tampilan penerimaan UMKM\n";
