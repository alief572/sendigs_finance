<?php
// Ekspor tab Invoiced (UMKM-FR-07): file .xlsx sungguhan ditulis lalu dibaca balik. Total Excel = card (UMKM-NFR-05), 0 selisih.
// Tanpa DB. Jalankan: php application/modules/invoicing/tests/Invoiced_export_test.php
define('BASEPATH', __DIR__);
define('DBCNL', 'consultant_fixture');
class BF_Model {}
require dirname(__DIR__) . '/libraries/Invoice_consulting.php';
require dirname(__DIR__) . '/models/Invoicing_model.php';
require dirname(__DIR__, 3) . '/libraries/Xlsx_export.php';
function xe($ok, $m) { if (!$ok) throw new RuntimeException('GAGAL: ' . $m); $GLOBALS['n'] = ($GLOBALS['n'] ?? 0) + 1; }

$item = function ($tax, $sales, $ppn, $gross, $saldo, $v = 3) { return (object) array('consulting_version' => $v, 'tax_treatment' => $tax, 'total_nominal_jurnal' => $sales,
    'ppn_jurnal' => $ppn, 'tagihan_ppn_jurnal' => $gross, 'saldo_piutang' => $saldo, 'dpp_nilai_lain' => number_format((float)$sales * 11 / 12, 2, '.', '')); };
$items = array($item('UMKM', '615000.00', '0.00', '615000.00', '615000.00'), $item('PKP', '1000.55', '110.06', '1110.61', '500.00'), $item('WAPU', '0.10', '0.00', '0.10', '0.10'), $item(null, '9', '0', '9', '9', 0));

// Baris ekspor dibangun seperti Invoicing::download_invoiced.
$out = array();
foreach ($items as $i => $it) {
    $m = Invoicing_model::money_for($it);
    $out[] = array('no_invoice' => 'INV-' . $i, 'tanggal' => '2026-10-05', 'no_spk' => 'SPK', 'no_po' => '=CMD()', 'no_faktur' => 'F', 'customer' => 'C', 'project' => 'P', 'leader' => 'L',
        'sales_name' => 'S', 'company' => 'STM', 'top' => 'DP', 'persen_top' => '30', 'tax_label' => Invoicing_model::tax_label_for($it->tax_treatment),
        'sales' => $m['sales'] ?? null, 'dpp' => $m === null ? null : Invoice_calculation::cents($it->dpp_nilai_lain), 'ppn' => $m['ppn'] ?? null, 'gross' => $m['gross'] ?? null, 'saldo' => $m['saldo'] ?? null, 'status' => 'Invoiced');
}
$spec = Xlsx_export::invoiced_spec($out, 'Tahun: 2026 | Bulan: Oktober | Company: STM', 'Invoiced_2026_Oktober_STM_Semua.xlsx');
$path = tempnam(sys_get_temp_dir(), 'inv') . '.xlsx';
Xlsx_export::save($spec, $path);
xe(substr(file_get_contents($path, false, null, 0, 2), 0, 2) === 'PK', 'file xlsx sungguhan (zip)');
$wb = PHPExcel_IOFactory::createReader('Excel2007')->load($path); $ws = $wb->getActiveSheet(); unlink($path);

xe($ws->getTitle() === 'Invoiced' && $ws->getCell('A1')->getValue() === 'Daftar Invoice (Invoiced)', 'judul sheet');
xe($ws->getCell('A2')->getValue() === 'Tahun: 2026 | Bulan: Oktober | Company: STM', 'baris filter');
$head = array('No', 'No. Invoice', 'Tanggal Invoice', 'No. SPK', 'No. PO', 'No. Faktur', 'Customer', 'Project', 'Project Leader', 'Sales', 'Company', 'TOP', 'Persen TOP', 'Perlakuan PPN', 'Penjualan', 'DPP Lain-lain', 'PPN', 'Total Tagihan', 'Saldo Piutang', 'Status');
foreach ($head as $i => $h) xe($ws->getCellByColumnAndRow($i, 4)->getValue() === $h, "header kolom $i $h");
xe($ws->getCell('N5')->getValue() === 'UMKM Non-PKP' && $ws->getCell('N6')->getValue() === 'PKP', 'Perlakuan PPN berupa label');
xe($ws->getCell('E5')->getValue() === '=CMD()' && $ws->getCell('E5')->getDataType() === 's', 'input user yang berawalan = tetap teks, bukan rumus');
xe(abs($ws->getCell('O5')->getValue() - 615000) < 0.001 && abs($ws->getCell('Q5')->getValue() - 0) < 0.001 && abs($ws->getCell('R5')->getValue() - 615000) < 0.001, 'UMKM: penjualan 615.000, PPN 0, tagihan 615.000');
xe(abs($ws->getCell('P5')->getValue() - 563750) < 0.001, 'UMKM: DPP 11/12 = 563.750');
xe($ws->getCell('O8')->getValue() === null && $ws->getCell('R8')->getValue() === null, 'invoice lama: sel nominal kosong');
xe($ws->getStyle('O5')->getNumberFormat()->getFormatCode() === '#,##0.00' && $ws->getStyle('C5')->getNumberFormat()->getFormatCode() === 'dd-mmm-yyyy', 'format angka dan tanggal');

// Baris Total = rumus SUM atas data baris 5..8, dan nilainya = card.
foreach (array('O', 'P', 'Q', 'R', 'S') as $col) {
    $f = $ws->getCell($col . '9')->getValue();
    xe($f === '=SUM(' . $col . '5:' . $col . '8)' && $ws->getCell($col . '9')->getDataType() === 'f', "Total kolom $col memakai rumus SUM");
}
xe($ws->getCell('A9')->getValue() === 'Total', 'label Total');
$card = Invoicing_model::invoiced_totals($items);
$calc = function ($col) use ($ws) { $s = 0; for ($r = 5; $r <= 8; $r++) $s += (float)$ws->getCell($col . $r)->getValue(); return (int)round($s * 100); };
xe($calc('O') === $card['sales'] && $calc('Q') === $card['ppn'] && $calc('R') === $card['gross'] && $calc('S') === $card['saldo'], 'jumlah baris Excel = card (0 selisih)');
xe($card['count'] === 4 && count($out) === 4, 'jumlah baris = jumlah invoice di card');

// Evaluasi rumus sungguhan memakai mesin PHPExcel (bukan hanya teks rumus).
$path2 = tempnam(sys_get_temp_dir(), 'inv') . '.xlsx';
Xlsx_export::save($spec, $path2);
$wb2 = PHPExcel_IOFactory::load($path2);
unlink($path2);
$ws2 = $wb2->getActiveSheet();
xe(abs($ws2->getCell('R9')->getCalculatedValue() - $card['gross'] / 100) < 0.001, 'rumus SUM Total tagihan dievaluasi = card');
xe(abs($ws2->getCell('Q9')->getCalculatedValue() - $card['ppn'] / 100) < 0.001, 'rumus SUM PPN dievaluasi = card');

// Nama file dan ekspor kosong.
xe($spec['filename'] === 'Invoiced_2026_Oktober_STM_Semua.xlsx', 'nama file mengikuti pola');
$empty = Xlsx_export::invoiced_spec(array(), 'f', 'x.xlsx');
xe($empty['rows'] === array() && $empty['total'][14]['formula'] === '=SUM(O5:O4)', 'spec kosong dibentuk (controller menolak sebelum menulis file)');
echo "PASS: {$GLOBALS['n']} cek ekspor Invoiced .xlsx\n";
