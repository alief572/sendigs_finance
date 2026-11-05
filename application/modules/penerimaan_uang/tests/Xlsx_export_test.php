<?php
// Builds the four export specs from fixtures, writes real .xlsx files and reads them back; no DB or browser.
require_once dirname(__DIR__, 3) . '/libraries/Xlsx_export.php';
function xCheck($ok, $m) { if (!$ok) throw new RuntimeException($m); }
function roundTrip($spec) {
    $path = tempnam(sys_get_temp_dir(), 'xl') . '.xlsx';
    Xlsx_export::save($spec, $path);
    xCheck(substr(file_get_contents($path, false, null, 0, 2), 0, 2) === 'PK', 'Real zip-based xlsx');
    $ws = PHPExcel_IOFactory::createReader('Excel2007')->load($path)->getActiveSheet();
    unlink($path);
    return $ws;
}

// Report: title, filter line, blank row, header, repeated group columns, Penerimaan Bank only on first row, Total row, formula-safe text.
$g = array('tanggal_transaksi' => '2026-10-30', 'created_date' => '2026-11-01 10:00:00', 'bank_name' => 'Bank BCA', 'keterangan' => '=CMD()', 'nm_customer' => 'PT A',
    'nominal_penerimaan_bank' => '3000000.00', 'pph23_dipotong' => 'Y', 'details' => array(
        array('no_invoice' => 'INV-1', 'company_name' => 'STM', 'tax_treatment' => 'PKP', 'pph23' => '0', 'total_akhir_jurnal' => '2000000', 'penerimaan' => '2000000', 'dep' => '0', 'biaya_admin' => '0', 'lebih' => '0', 'sisa_piutang' => '0'),
        array('no_invoice' => 'INV-2', 'company_name' => 'STM', 'tax_treatment' => '', 'pph23' => '0', 'total_akhir_jurnal' => '1000000', 'penerimaan' => '1000000', 'dep' => '0', 'biaya_admin' => '0', 'lebih' => '0', 'sisa_piutang' => '0')));
$spec = Xlsx_export::report_spec(array($g), 2026, 10, 'STM');
xCheck($spec['filename'] === 'Report_Penerimaan_2026_Oktober_STM.xlsx', 'Report filename uses month name, no timestamp');
$ws = roundTrip($spec);
xCheck($ws->getTitle() === 'Report Penerimaan' && $ws->getCell('A1')->getValue() === 'Report Penerimaan Piutang', 'Report sheet and title');
xCheck($ws->getCell('A2')->getValue() === 'Tahun: 2026 | Bulan: Oktober | Company: STM', 'Report filter line');
xCheck($ws->getCell('A4')->getValue() === 'No' && $ws->getCell('Q4')->getValue() === 'Sisa Piutang', 'Report 17 columns');
xCheck($ws->getCell('D5')->getValue() === '=CMD()' && $ws->getCell('D5')->getDataType() === 's', 'Formula text stays a string');
xCheck($ws->getCell('A6')->getValue() == 1 && $ws->getCell('K5')->getValue() == 3000000 && $ws->getCell('K6')->getValue() === null, 'Penerimaan Bank only on first row of the group');
xCheck($ws->getCell('H6')->getValue() === '-', 'Empty TAX Code shows a dash');
xCheck($ws->getCell('A7')->getValue() === 'Total' && $ws->getCell('K7')->getValue() == 3000000 && $ws->getCell('M7')->getValue() == 3000000, 'Total row');
xCheck($ws->getStyle('K5')->getNumberFormat()->getFormatCode() === '#,##0' && $ws->getStyle('B5')->getNumberFormat()->getFormatCode() === 'dd-mmm-yyyy', 'Number and date formats');
xCheck(Xlsx_export::report_spec(array(), '', '', '')['filename'] === 'Report_Penerimaan_Semua_Semua_Semua.xlsx', 'Report filename without filters');

// Kwitansi
$ws = roundTrip(Xlsx_export::kwitansi_spec(array(array('tanggal_kwitansi' => '2026-10-01', 'no_kwitansi' => 'KD/1', 'nm_customer' => 'PT A', 'no_spk' => 'SPK-1', 'nm_company' => 'STM', 'nilai_kwitansi' => '1500.50', 'status' => 'LUNAS', 'no_penerimaan' => '00001/RKN'))));
xCheck($ws->getTitle() === 'Kwitansi Deposit' && $ws->getCell('I3')->getValue() === 'No Penerimaan' && $ws->getCell('H4')->getValue() === 'Lunas' && $ws->getCell('G4')->getValue() == 1500.5, 'Kwitansi sheet');
xCheck(Xlsx_export::kwitansi_spec(array())['filename'] === 'Kwitansi_Deposit.xlsx', 'Kwitansi filename');

// Monitoring: index + ledger with running balance
$ws = roundTrip(Xlsx_export::deposit_index_spec(array(array('nm_customer' => 'PT A', 'jumlah_deposit' => 2, 'total_dari_kwitansi' => '100', 'total_dari_lebih_bayar' => '50', 'total_nominal_awal' => '150', 'total_nominal_terpakai' => '40', 'sisa_saldo' => '110', 'last_deposit_date' => null))));
xCheck($ws->getTitle() === 'Index Deposit' && $ws->getCell('A1')->getValue() === 'Monitoring Deposit' && $ws->getCell('H4')->getValue() == 110 && $ws->getCell('I4')->getValue() === '-', 'Deposit index sheet');
$ledger = Xlsx_export::deposit_ledger_spec('PT A/B', array(
    array('tanggal' => '2026-10-01', 'jenis_mutasi' => 'IN', 'nominal' => '100', 'no_transaksi' => 'N1', 'keterangan' => 'x'),
    array('tanggal' => '2026-10-02', 'jenis_mutasi' => 'OUT', 'nominal' => '40', 'no_transaksi' => 'N2')), function ($r) { return $r['jenis_mutasi']; });
xCheck($ledger['filename'] === 'Deposit_PT_A_B.xlsx', 'Ledger filename');
$ws = roundTrip($ledger);
xCheck($ws->getCell('A1')->getValue() === 'Mutasi Deposit - PT A/B' && $ws->getCell('H4')->getValue() == 100 && $ws->getCell('H5')->getValue() == 60 && $ws->getCell('G5')->getValue() == 40, 'Ledger running balance');
echo "PASS: xlsx exports (report, kwitansi, deposit index and ledger) round-trip with titles, totals, formats and formula-safe text\n";
