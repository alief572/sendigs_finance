<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Register PhpSpreadsheet autoloader
spl_autoload_register(function ($class) {
    $prefix = 'PhpOffice\\PhpSpreadsheet\\';
    $base_dir = APPPATH . 'libraries/PhpSpreadsheet/src/PhpSpreadsheet/';

    if (strncmp($prefix, $class, strlen($prefix)) === 0) {
        $relative_class = substr($class, strlen($prefix));
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

        if (file_exists($file)) {
            require $file;
        }
    }
});

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Create new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Report Piutang');

// Column headers (row 1)
$headers = [
    'A1' => 'No',
    'B1' => 'No SPK',
    'C1' => 'Customer',
    'D1' => 'Nilai Kontrak',
    'E1' => 'No Invoice',
    'F1' => 'Tanggal Invoice',
    'G1' => 'Nilai Invoice',
    'H1' => 'No Penerimaan',
    'I1' => 'Tanggal Penerimaan',
    'J1' => 'Nilai Penerimaan',
    'K1' => 'Saldo Piutang'
];

foreach ($headers as $cell => $value) {
    $sheet->setCellValue($cell, $value);
    $sheet->getStyle($cell)->getFont()->setBold(true);
}

// Auto-size columns
foreach (range('A', 'K') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Populate rows with grouped SPK data
$row = 2;
$no = 0;

foreach ($report_data as $spk) {
    $no++;
    $is_first_spk_row = true;

    $invoices = isset($spk['invoices']) ? $spk['invoices'] : [];

    if (empty($invoices)) {
        // SPK without invoices
        $sheet->setCellValue('A' . $row, $no);
        $sheet->setCellValue('B' . $row, $spk['no_spk']);
        $sheet->setCellValue('C' . $row, $spk['nm_customer']);
        $sheet->setCellValue('D' . $row, $spk['nilai_kontrak']);
        $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->setCellValue('E' . $row, '-');
        $sheet->setCellValue('F' . $row, '-');
        $sheet->setCellValue('G' . $row, '-');
        $sheet->setCellValue('H' . $row, '-');
        $sheet->setCellValue('I' . $row, '-');
        $sheet->setCellValue('J' . $row, '-');
        $sheet->setCellValue('K' . $row, '-');
        $row++;
    } else {
        foreach ($invoices as $invoice) {
            $is_first_invoice_row = true;

            $payments = isset($invoice['payments']) ? $invoice['payments'] : [];

            if (empty($payments)) {
                // Invoice without payments
                if ($is_first_spk_row) {
                    $sheet->setCellValue('A' . $row, $no);
                    $sheet->setCellValue('B' . $row, $spk['no_spk']);
                    $sheet->setCellValue('C' . $row, $spk['nm_customer']);
                    $sheet->setCellValue('D' . $row, $spk['nilai_kontrak']);
                    $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0');
                    $is_first_spk_row = false;
                }

                $sheet->setCellValue('E' . $row, $invoice['no_invoice']);
                // Format tanggal invoice as dd-mm-yyyy
                $tgl_inv = '';
                if (!empty($invoice['tanggal_invoice'])) {
                    $tgl_inv = date('d-m-Y', strtotime($invoice['tanggal_invoice']));
                }
                $sheet->setCellValue('F' . $row, $tgl_inv);
                $sheet->setCellValue('G' . $row, $invoice['nilai_invoice']);
                $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->setCellValue('H' . $row, '-');
                $sheet->setCellValue('I' . $row, '-');
                $sheet->setCellValue('J' . $row, '-');
                $sheet->setCellValue('K' . $row, $invoice['saldo_piutang']);
                $sheet->getStyle('K' . $row)->getNumberFormat()->setFormatCode('#,##0');
                $row++;
            } else {
                // Invoice with payments
                foreach ($payments as $payment) {
                    if ($is_first_spk_row) {
                        $sheet->setCellValue('A' . $row, $no);
                        $sheet->setCellValue('B' . $row, $spk['no_spk']);
                        $sheet->setCellValue('C' . $row, $spk['nm_customer']);
                        $sheet->setCellValue('D' . $row, $spk['nilai_kontrak']);
                        $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0');
                        $is_first_spk_row = false;
                    }

                    if ($is_first_invoice_row) {
                        $sheet->setCellValue('E' . $row, $invoice['no_invoice']);
                        // Format tanggal invoice as dd-mm-yyyy
                        $tgl_inv = '';
                        if (!empty($invoice['tanggal_invoice'])) {
                            $tgl_inv = date('d-m-Y', strtotime($invoice['tanggal_invoice']));
                        }
                        $sheet->setCellValue('F' . $row, $tgl_inv);
                        $sheet->setCellValue('G' . $row, $invoice['nilai_invoice']);
                        $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('#,##0');
                        $sheet->setCellValue('K' . $row, $invoice['saldo_piutang']);
                        $sheet->getStyle('K' . $row)->getNumberFormat()->setFormatCode('#,##0');
                        $is_first_invoice_row = false;
                    }

                    // Payment data
                    $sheet->setCellValue('H' . $row, $payment['no_penerimaan']);
                    // Format tanggal penerimaan as dd-mm-yyyy
                    $tgl_pen = '';
                    if (!empty($payment['tanggal_penerimaan'])) {
                        $tgl_pen = date('d-m-Y', strtotime($payment['tanggal_penerimaan']));
                    }
                    $sheet->setCellValue('I' . $row, $tgl_pen);
                    $sheet->setCellValue('J' . $row, $payment['nilai_penerimaan']);
                    $sheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode('#,##0');
                    $row++;
                }
            }
        }
    }
}

// Total Piutang summary row
$sheet->setCellValue('A' . $row, '');
$sheet->setCellValue('J' . $row, 'Total Piutang');
$sheet->getStyle('J' . $row)->getFont()->setBold(true);
$sheet->setCellValue('K' . $row, $total_piutang);
$sheet->getStyle('K' . $row)->getNumberFormat()->setFormatCode('#,##0');
$sheet->getStyle('K' . $row)->getFont()->setBold(true);

// Set HTTP headers for .xlsx download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Output file
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
