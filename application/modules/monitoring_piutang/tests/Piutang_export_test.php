<?php
// Jalankan: scratch/runtime/php74/php.exe application/modules/monitoring_piutang/tests/Piutang_export_test.php
define('BASEPATH', 1);
require_once __DIR__ . '/../../../libraries/PHPExcel.php';
require_once __DIR__ . '/../libraries/Piutang_export.php';

$n = 0;
function ok($c, $m) { global $n; $n++; if (!$c) { fwrite(STDERR, "FAIL: $m\n"); exit(1); } }

$cols = [['h' => 'No', 'k' => '#', 't' => 'int'], ['h' => 'Inv', 'k' => 'inv'], ['h' => 'Tgl', 'k' => 'tgl', 't' => 'date'], ['h' => 'Sisa', 'k' => 'sisa', 't' => 'num']];
$ws = (new PHPExcel())->getActiveSheet();
$last = Piutang_export::fill($ws, ['title' => 'T/est', 'heading' => ['H'], 'cols' => $cols, 'sum' => ['sisa'],
    'rows' => [['inv' => '0012', 'tgl' => '2026-10-02', 'sisa' => 1500.5], ['inv' => '7', 'tgl' => null, 'sisa' => 2]]]);
ok($last === 6, 'baris total setelah heading, header, 2 data');
ok($ws->getCell('B4')->getValue() === '0012' && $ws->getCell('B4')->getDataType() === 's', 'no invoice tetap teks dengan nol di depan');
ok($ws->getCell('C4')->getValue() === '02-10-2026', 'tanggal dd-mm-yyyy');
ok($ws->getCell('C5')->getValue() === '', 'tanggal kosong tetap kosong');
ok($ws->getCell('D6')->getValue() === '=SUM(D4:D5)', 'total memakai rumus');
ok($ws->getCell('D4')->getStyle()->getNumberFormat()->getFormatCode() === '#,##0.00', 'nominal berformat ribuan');
ok($ws->getTitle() === 'Test', 'judul sheet disanitasi');

$empty = (new PHPExcel())->getActiveSheet();
Piutang_export::fill($empty, ['title' => 'Kosong', 'cols' => $cols, 'sum' => ['sisa'], 'rows' => []]);
ok($empty->getCell('D2')->getValue() === null && $empty->getCell('A2')->getValue() === null, 'tanpa data tidak ada rumus total');

// numerik kosong tetap kosong (bukan 0)
$blank = (new PHPExcel())->getActiveSheet();
Piutang_export::fill($blank, ['title' => 'B', 'cols' => [['h' => 'Umur', 'k' => 'u', 't' => 'int']], 'rows' => [['u' => null], ['u' => 0]]]);
ok($blank->getCell('A2')->getValue() === null && $blank->getCell('A3')->getValue() === 0.0, 'null kosong, 0 tetap 0');

// Ringkasan Dashboard Plan Bayar: rumus per bulan + total
$months = [];
foreach ([1 => 'Januari', 2 => 'Februari'] as $i => $nm) { $months[] = ['nama' => $nm]; }
$sheet = (new PHPExcel())->getActiveSheet();
$tot = Piutang_export::fill($sheet, Piutang_export::plan_summary_sheet($months, 3, 2, 'Tahun 2026'));
ok($tot === 6, 'heading, kosong, header, 2 bulan, lalu total di baris 6 (got ' . $tot . ')');
ok($sheet->getCell('B4')->getValue() === '=COUNTIF(Rincian!$B$2:$B$4,"Januari")', 'COUNTIF plan Januari: ' . $sheet->getCell('B4')->getValue());
ok($sheet->getCell('C5')->getValue() === '=SUMIF(Rincian!$B$2:$B$4,"Februari",Rincian!$K$2:$K$4)', 'SUMIF plan Februari');
ok($sheet->getCell('E4')->getValue() === '=SUMIF(Realisasi!$B$2:$B$3,"Januari",Realisasi!$K$2:$K$3)', 'SUMIF realisasi Januari');
ok($sheet->getCell('E6')->getValue() === '=SUM(E4:E5)', 'total realisasi');
$sheet0 = (new PHPExcel())->getActiveSheet();
Piutang_export::fill($sheet0, Piutang_export::plan_summary_sheet($months, 0, 0, 'Tahun 2026'));
ok($sheet0->getCell('B4')->getValue() == 0 && $sheet0->getCell('B4')->getDataType() === 'n' && $sheet0->getCell('E5')->getDataType() === 'n','tanpa data: nilai 0 tanpa rumus ke range kosong');

echo "PASS: $n assertions\n";
