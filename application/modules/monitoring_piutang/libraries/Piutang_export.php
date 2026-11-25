<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Pembuat file .xlsx bersama Follow Up, Monitoring, dan Dashboard (MP-NFR-07):
 * nominal berformat ribuan, tanggal dd-mm-yyyy, no invoice sebagai teks, total pakai rumus
 * (tanpa baris total bila tidak ada data).
 *
 * Spec sheet: ['title'=>str, 'heading'=>[str,...] (baris judul opsional), 'cols'=>[['h'=>str,'k'=>key,'t'=>'text|num|date|int'],...],
 *              'rows'=>[assoc,...], 'sum'=>[key,...]]
 */
class Piutang_export
{
    public static function date_dmy($v)
    {
        $v = substr((string) $v, 0, 10);
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) ? substr($v, 8, 2) . '-' . substr($v, 5, 2) . '-' . substr($v, 0, 4) : '';
    }

    /** Isi satu sheet; mengembalikan nomor baris terakhir. Dipisah supaya bisa dites tanpa mengirim file. */
    public static function fill(PHPExcel_Worksheet $ws, array $spec)
    {
        $ws->setTitle(substr(preg_replace('/[\\\\\/\?\*\[\]:]/', '', $spec['title']), 0, 31));
        $r = 1;
        foreach ($spec['heading'] ?? [] as $line) {
            $ws->setCellValueExplicit('A' . $r++, $line, PHPExcel_Cell_DataType::TYPE_STRING);
        }
        if ($r > 1) {
            $r++;
        }
        // 'pre' = blok di atas tabel (mis. ringkasan Monitoring); callback($ws, baris mulai) mengembalikan baris header tabel.
        if (!empty($spec['pre'])) {
            $r = (int) call_user_func($spec['pre'], $ws, $r);
        }
        $head = $r;
        $cols = $spec['cols'];
        foreach ($cols as $i => $c) {
            $ws->setCellValueExplicit(PHPExcel_Cell::stringFromColumnIndex($i) . $head, $c['h'], PHPExcel_Cell_DataType::TYPE_STRING);
        }
        $last = PHPExcel_Cell::stringFromColumnIndex(count($cols) - 1);
        $ws->getStyle("A{$head}:{$last}{$head}")->applyFromArray(['font' => ['bold' => true],
            'fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => 'D9E2F3']],
            'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER]]);
        $r = $head + 1;
        $first = $r;
        foreach ($spec['rows'] as $n => $row) {
            foreach ($cols as $i => $c) {
                $addr = PHPExcel_Cell::stringFromColumnIndex($i) . $r;
                $v = $c['k'] === '#' ? $n + 1 : ($row[$c['k']] ?? null);
                $t = $c['t'] ?? 'text';
                if (($t === 'num' || $t === 'int') && ($v === null || $v === '')) {
                    continue; // kosong tetap kosong (mis. umur invoice yang belum diterima), bukan 0
                } elseif ($t === 'num' || $t === 'int') {
                    $ws->setCellValueExplicit($addr, (float) $v, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $ws->getStyle($addr)->getNumberFormat()->setFormatCode($t === 'num' ? '#,##0.00' : '#,##0');
                } elseif ($t === 'date') {
                    $ws->setCellValueExplicit($addr, self::date_dmy($v), PHPExcel_Cell_DataType::TYPE_STRING);
                } else {
                    $ws->setCellValueExplicit($addr, (string) $v, PHPExcel_Cell_DataType::TYPE_STRING);
                }
            }
            $r++;
        }
        $lastData = $r - 1;
        if ($lastData >= $first && !empty($spec['sum'])) {
            $ws->setCellValueExplicit('A' . $r, 'TOTAL', PHPExcel_Cell_DataType::TYPE_STRING);
            foreach ($cols as $i => $c) {
                if (in_array($c['k'], $spec['sum'], true)) {
                    $L = PHPExcel_Cell::stringFromColumnIndex($i);
                    $ws->setCellValue($L . $r, "=SUM({$L}{$first}:{$L}{$lastData})");
                    $ws->getStyle($L . $r)->getNumberFormat()->setFormatCode(($c['t'] ?? '') === 'int' ? '#,##0' : '#,##0.00');
                }
            }
            $ws->getStyle("A{$r}:{$last}{$r}")->getFont()->setBold(true);
        }
        if (!empty($spec['after'])) {
            call_user_func($spec['after'], $ws, $r); // $r = baris TOTAL (atau baris kosong pertama bila tanpa data)
        }
        foreach (array_keys($cols) as $i) {
            $ws->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($i))->setAutoSize(true);
        }
        $ws->freezePane('A' . ($head + 1));
        return $r;
    }

    /**
     * Sheet Ringkasan Dashboard Plan Bayar (12 bulan). Jumlah dan total tiap bulan berupa rumus ke sheet Rincian
     * (kolom B = Bulan, K = Nilai Plan) dan Realisasi (B = Bulan Bayar, K = Jumlah Dibayar); baris Total menjumlah kolomnya.
     * $np / $nr = jumlah baris data di Rincian / Realisasi; 0 baris = nilai 0 tanpa rumus. $from = baris data pertama di
     * Rincian/Realisasi (2 tanpa judul; 5 bila sheet itu berjudul 2 baris). $info = baris info di bawah judul (mockup).
     */
    public static function plan_summary_sheet(array $months, $np, $nr, $title, $from = 2, $info = '')
    {
        $rows = array_map(function ($m) {
            return ['nama' => $m['nama'], 'pc' => 0, 'pn' => 0, 'rc' => 0, 'rn' => 0];
        }, $months);
        $heading = ['Dashboard Plan Bayar - ' . $title . ' - Ringkasan per Bulan'];
        if ($info !== '') {
            $heading[] = $info;
        }
        return ['title' => 'Ringkasan', 'heading' => $heading, 'cols' => [['h' => 'Bulan', 'k' => 'nama'],
            ['h' => 'Jumlah Invoice (Plan)', 'k' => 'pc', 't' => 'int'], ['h' => 'Total Nilai Plan', 'k' => 'pn', 't' => 'num'],
            ['h' => 'Jumlah Penerimaan', 'k' => 'rc', 't' => 'int'], ['h' => 'Total Realisasi', 'k' => 'rn', 't' => 'num']],
            'rows' => $rows, 'sum' => ['pc', 'pn', 'rc', 'rn'],
            'after' => function ($ws, $total) use ($months, $np, $nr, $from) {
                $f = (int) $from;
                $lp = $np + $f - 1;
                $lr = $nr + $f - 1; // baris data terakhir Rincian / Realisasi
                foreach ($months as $i => $m) {
                    $r = $total - count($months) + $i;
                    $q = '"' . $m['nama'] . '"';
                    $ws->setCellValue("B$r", $np ? "=COUNTIF(Rincian!\$B\$$f:\$B\$$lp,$q)" : 0);
                    $ws->setCellValue("C$r", $np ? "=SUMIF(Rincian!\$B\$$f:\$B\$$lp,$q,Rincian!\$K\$$f:\$K\$$lp)" : 0);
                    $ws->setCellValue("D$r", $nr ? "=COUNTIF(Realisasi!\$B\$$f:\$B\$$lr,$q)" : 0);
                    $ws->setCellValue("E$r", $nr ? "=SUMIF(Realisasi!\$B\$$f:\$B\$$lr,$q,Realisasi!\$K\$$f:\$K\$$lr)" : 0);
                }
                $ws->setCellValueExplicit('A' . ($total + 2), 'Kolom B-C menghitung dari sheet Rincian (plan = sisa piutang saat plan dibuat, termasuk invoice yang sudah close), '
                    . 'kolom D-E dari sheet Realisasi (penerimaan).', PHPExcel_Cell_DataType::TYPE_STRING);
            }];
    }

    /** Kirim workbook ke browser. $sheets = daftar spec. */
    public static function send(array $sheets, $filename)
    {
        $xl = new PHPExcel();
        foreach ($sheets as $i => $spec) {
            self::fill($i === 0 ? $xl->getActiveSheet() : $xl->createSheet(), $spec);
        }
        $xl->setActiveSheetIndex(0);
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . preg_replace('/[^A-Za-z0-9_.\-]/', '_', $filename) . '"');
        header('Cache-Control: max-age=0');
        $writer = PHPExcel_IOFactory::createWriter($xl, 'Excel2007');
        $writer->setPreCalculateFormulas(false);
        $writer->save('php://output');
        exit;
    }
}
