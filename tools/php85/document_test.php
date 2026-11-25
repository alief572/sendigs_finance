<?php

error_reporting(E_ALL);
set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    if (error_reporting() & $severity) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
    return false;
});

$root = dirname(__DIR__, 2);
require $root . '/application/libraries/Mpdf.php';
require $root . '/application/libraries/PHPExcel.php';
require $root . '/assets/html2pdf/html2pdf/html2pdf.class.php';

function check(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$tmp = tempnam(sys_get_temp_dir(), 'sendigs-documents-');
try {
    $pdf = new Mpdf('utf-8', 'A4-L');
    $pdf->SetImportUse();
    $pdf->SetTitle('Sendigs – Voucher');
    $pdf->SetFooter('Halaman {PAGENO}');
    $pdf->WriteHTML('<h1>Voucher pembayaran</h1><p>Rp 1.250.000</p>');
    $bytes = $pdf->Output('', 'S');
    check(str_starts_with($bytes, '%PDF-') && str_contains($bytes, '%%EOF'), 'PDF header and footer');
    check($pdf->w > $pdf->h, 'A4-L preserves landscape');

    $htmlPdf = new HTML2PDF('P', 'A4', 'en');
    $htmlPdf->pdf->SetDisplayMode('fullpage');
    $htmlPdf->WriteHTML('<page><h1>Bukti</h1><table><tr><td>Rp 1.250.000</td></tr></table></page>');
    check(str_starts_with($htmlPdf->Output('bukti.pdf', 'S'), '%PDF-'), 'HTML2PDF compatibility');

    $book = new PHPExcel();
    $book->getActiveSheet()
        ->setCellValueExplicit(PHPExcel_Cell::stringFromColumnIndex(0) . '1', '000123', PHPExcel_Cell_DataType::TYPE_STRING)
        ->setCellValue('B1', 1250000)
        ->setCellValue('B2', '=SUM(B1:B1)')
        ->getStyle('A1:B1')->applyFromArray([
            'fill' => ['type' => 'solid', 'color' => ['rgb' => '4472C4']],
            'borders' => ['allborders' => ['style' => 'thin']],
            'alignment' => ['wrap' => true],
        ]);
    PHPExcel_IOFactory::createWriter($book, 'Excel2007')->save($tmp);
    $read = PHPExcel_IOFactory::createReader('Excel2007')->load($tmp)->getActiveSheet();
    check($read->getCell('A1')->getValue() === '000123', 'bank account zeros preserved');
    check($read->getCell('B2')->getCalculatedValue() === 1250000, 'amount and formula preserved');
    check($read->getStyle('A1')->getFill()->getStartColor()->getRGB() === '4472C4', 'legacy fill');
    check($read->getStyle('A1')->getBorders()->getBottom()->getBorderStyle() === 'thin', 'legacy border');
    check($read->getStyle('A1')->getAlignment()->getWrapText(), 'legacy wrap');
    echo "PASS PDF and XLSX compatibility\n";
} finally {
    unlink($tmp);
}
