<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Excel (.xlsx) Report Jurnal tab Pembayaran. Urutan dan judul kolom mengikuti
 * "Template Fix Excel Report Jurnal.xlsx"; barisnya berasal dari Jurnal_all_model::export_payment_rows().
 */
class Jurnal_pembayaran_xlsx
{
    const HEADERS = [
        'Status', 'Jenis Jurnal', 'Tgl Jurnal/Bayar', 'No. Jurnal Accounting (Transaksi)', 'Tgl Posting',
        'No. Transaksi', 'No. Referensi', 'No. Voucher', 'Kategori Transaksi', 'Company', 'Divisi',
        'No. COA', 'Nama COA', 'Keterangan', 'Debit', 'Kredit',
    ];
    const WIDTHS = [12, 15.2, 21.7, 27.2, 12.7, 20, 35.6, 35.6, 17.4, 21.3, 12, 12, 35.6, 35.6, 14, 14];

    /** Nilai satu baris sesuai urutan HEADERS; Debit/Kredit tetap string desimal dari database. */
    public static function values($item, $titles)
    {
        $h = $item['header'];
        $l = $item['line'];
        $date = trim((string) ($l['tgl_bayar'] ?? ''));
        return [
            $h['group_status'] ?? 'Sudah posting',
            $titles[$h['tab']] ?? '',
            $date !== '' ? $date : (string) $l['tgl_jurnal'],
            $h['source_numbers'] ?? '-',
            !empty($h['posted_at']) ? substr((string) $h['posted_at'], 0, 10) : '-',
            $h['display_no_transaksi'] ?? $h['no_transaksi'],
            $h['no_referensi'],
            $l['bbk_voucher'] ?? '-',
            $h['category'],
            $l['nm_company'],
            $l['nm_divisi'],
            $l['coa'],
            $l['nm_coa'],
            $l['keterangan'],
            $l['debit'],
            $l['kredit'],
        ];
    }

    /** Isi file .xlsx sebagai string biner. */
    public static function render($rows, $titles, $sheetName)
    {
        require_once APPPATH . 'libraries/PHPExcel.php';
        $book = new PHPExcel();
        $sheet = $book->getActiveSheet();
        $sheet->setTitle(substr(preg_replace('/[\\\\\/?*\[\]:]/', '_', $sheetName), 0, 31));
        $last = 'P';
        foreach (self::HEADERS as $i => $title) {
            $sheet->setCellValueExplicit(PHPExcel_Cell::stringFromColumnIndex($i) . '1', $title, PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getColumnDimensionByColumn($i)->setWidth(self::WIDTHS[$i]);
        }
        $sheet->getStyle("A1:{$last}1")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => 'D9E1F2']],
            'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER],
            'borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]],
        ]);
        $sheet->freezePane('A2');
        $row = 2;
        foreach ($rows as $item) {
            foreach (self::values($item, $titles) as $i => $value) {
                $cell = PHPExcel_Cell::stringFromColumnIndex($i) . $row;
                if ($i >= 14) {
                    $sheet->setCellValueExplicit($cell, (float) $value, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                } else {
                    // Selalu string eksplisit: nilai berawalan = + - @ tidak pernah dievaluasi sebagai rumus.
                    $sheet->setCellValueExplicit($cell, (string) $value, PHPExcel_Cell_DataType::TYPE_STRING);
                }
            }
            $row++;
        }
        if ($row > 2) {
            $sheet->getStyle('O2:P' . ($row - 1))->getNumberFormat()->setFormatCode('#,##0.00');
        }
        $file = tempnam(sys_get_temp_dir(), 'rjp');
        try {
            PHPExcel_IOFactory::createWriter($book, 'Excel2007')->save($file);
            $binary = file_get_contents($file);
        } finally {
            @unlink($file);
        }
        $book->disconnectWorksheets();
        return $binary;
    }
}
