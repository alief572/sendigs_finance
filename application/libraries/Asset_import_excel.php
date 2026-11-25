<?php
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\NamedRange;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
require_once __DIR__ . '/Asset_import.php';

class Asset_import_excel_filter implements IReadFilter
{
    public function readCell(string $column, int $row, string $worksheetName = ''): bool
    {
        return $worksheetName === 'Data Asset' && $row <= 10001 && Coordinate::columnIndexFromString($column) <= 8;
    }
}

class Asset_import_excel
{
    public function template(array $masters, $path)
    {
        return $this->withExcelErrors(function () use ($masters, $path) { return $this->writeTemplate($masters, $path); });
    }

    private function withExcelErrors(callable $operation)
    {
        $internal = libxml_use_internal_errors(true);
        set_error_handler(function ($severity,$message,$file,$line) {
            if (!(error_reporting() & $severity)) { return false; }
            throw new ErrorException($message,0,$severity,$file,$line);
        });
        try { return $operation(); }
        finally { restore_error_handler(); libxml_clear_errors(); libxml_use_internal_errors($internal); }
    }

    private function writeTemplate(array $masters, $path)
    {
        $book = new Spreadsheet(); $sheet = $book->getActiveSheet()->setTitle('Data Asset');
        $sheet->fromArray(Asset_import::HEADERS, null, 'A1');
        $sheet->freezePane('A2');
        $sheet->getRowDimension(1)->setRowHeight(42);
        $sheet->getStyle('A1:H1')->applyFromArray(['font'=>['bold'=>true,'color'=>['rgb'=>'FFFFFF']], 'fill'=>['fillType'=>'solid','startColor'=>['rgb'=>'0073B7']], 'alignment'=>['wrapText'=>true,'vertical'=>'center']]);
        $sheet->getStyle('A2:H501')->getFill()->setFillType('solid')->getStartColor()->setRGB('FFF8DD');
        $sheet->getStyle('D2:D501')->getFill()->getStartColor()->setRGB('EDF5FC');
        $sheet->getStyle('E2:E501')->getNumberFormat()->setFormatCode('dd/mm/yyyy');
        $sheet->getStyle('F2:H501')->getNumberFormat()->setFormatCode('#,##0');
        foreach (['A'=>35,'B'=>35,'C'=>35,'D'=>45,'E'=>22,'F'=>10,'G'=>30,'H'=>24] as $column=>$width) { $sheet->getColumnDimension($column)->setWidth($width); }
        $guide = $book->createSheet()->setTitle('Panduan & Contoh');
        $guide->fromArray([
            ['PANDUAN IMPOR ASSET'],
            ['1. Isi hanya sheet Data Asset mulai baris 2. Kolom * wajib. Kuning: isian; biru muda: opsional.'],
            ['2. Pilih master dari dropdown; jangan mengubah header. Department boleh kosong.'],
            ['3. Qty kosong dianggap 1. Qty 5 dan nilai Rp3.000.000 berarti lima asset, masing-masing Rp3.000.000.'],
            ['4. Tanggal: dd/mm/yyyy atau yyyy-mm-dd. Nilai: angka utuh tanpa mengetik Rp/titik/koma ribuan.'],
            ['5. Upload melalui Import Excel, periksa semua baris, lalu Simpan Semua Asset. Preview berlaku 30 menit.'],
            ['6. Maksimal 5 MB, 500 baris data, 1.000 unit. Jangan gunakan formula. Semua baris harus valid.'],
            ['7. Kode asset dan penyusutan dihitung sistem. Tidak mengimpor saldo awal/akumulasi/jurnal.'],
            ['8. Master berubah? Download ulang format. Label [ID ...] membedakan nama master yang sama.'],
            ['CONTOH DI BAWAH TIDAK IKUT DIIMPOR'],
            Asset_import::HEADERS,
            ['Laptop kantor', key($masters['category']), key($masters['company']), '', '12/10/2026', 5, 3000000, 4]
        ], null, 'A1');
        $guide->setCellValueExplicit('B12', (string)key($masters['category']), DataType::TYPE_STRING);
        $guide->setCellValueExplicit('C12', (string)key($masters['company']), DataType::TYPE_STRING);
        foreach (Asset_import::HEADERS as $i=>$header) {
            $hint = ['Nama asset, maksimal 255 byte.','Kategori aktif dari dropdown.','Company dari dropdown.','Opsional; nama department disertai company HRIS.','Tanggal Excel atau dd/mm/yyyy / yyyy-mm-dd.','Jumlah unit 1–99; kosong = 1.','Nilai satu unit, rupiah utuh positif. Contoh: 3000000.','Lama penyusutan 1–20 tahun.'][$i];
            $sheet->getComment([$i + 1, 1])->getText()->createTextRun($hint);
            $guide->setCellValue('A'.(15+$i), str_replace(' *','',$header));
            $guide->setCellValue('B'.(15+$i), $hint);
        }
        $guide->getColumnDimension('A')->setWidth(35); $guide->getColumnDimension('B')->setWidth(85);
        for ($r=1;$r<=10;$r++) { $guide->mergeCells('A'.$r.':H'.$r); $guide->getRowDimension($r)->setRowHeight(32); }
        $guide->getStyle('A1:H23')->getAlignment()->setWrapText(true);
        $guide->getStyle('A1:H1')->getFont()->setBold(true)->setSize(16);
        foreach (range('C','H') as $col) { $guide->getColumnDimension($col)->setWidth(20); }
        $ref = $book->createSheet()->setTitle('Referensi');
        foreach (['category','company','department','years'] as $i=>$key) {
            $labels = $key === 'years' ? range(1,20) : array_keys($masters[$key]);
            if (count($labels) > 65535) { throw new RuntimeException('Master terlalu banyak untuk dropdown Excel.'); }
            if (!$labels) { $labels = ['']; }
            $col = Coordinate::stringFromColumnIndex($i + 1);
            foreach ($labels as $j=>$label) { $ref->setCellValueExplicit($col.($j+1), (string)$label, DataType::TYPE_STRING); }
            $rangeName = 'Import_' . $key;
            $book->addNamedRange(new NamedRange($rangeName, $ref, '$'.$col.'$1:$'.$col.'$'.count($labels)));
            $target = [0=>'B',1=>'C',2=>'D',3=>'H'][$i];
            for ($r=2;$r<=501;$r++) {
                $validation = $sheet->getCell($target.$r)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST)->setErrorStyle(DataValidation::STYLE_STOP)->setAllowBlank($i===2)->setShowDropDown(true)->setShowErrorMessage(true)->setShowInputMessage(true)->setFormula1($rangeName)->setErrorTitle('Pilihan tidak valid')->setError('Pilih nilai dari dropdown.')->setPromptTitle('Pilih dari master')->setPrompt('Download ulang bila pilihan master berubah.');
            }
        }
        foreach (['F'=>99,'G'=>999999999999] as $col=>$max) {
            for ($r=2;$r<=501;$r++) { $sheet->getCell($col.$r)->getDataValidation()->setType(DataValidation::TYPE_WHOLE)->setOperator(DataValidation::OPERATOR_BETWEEN)->setFormula1('1')->setFormula2((string) $max)->setAllowBlank($col==='F')->setShowErrorMessage(true)->setErrorStyle(DataValidation::STYLE_STOP)->setError('Gunakan angka bulat positif tanpa pemisah ribuan.'); }
        }
        $ref->setSheetState(Worksheet::SHEETSTATE_HIDDEN); $book->setActiveSheetIndex(0);
        try { (new XlsxWriter($book))->save($path); }
        finally { $book->disconnectWorksheets(); }
    }

    public function read($path)
    {
        return $this->withExcelErrors(function () use ($path) { return $this->readWorkbook($path); });
    }

    private function readWorkbook($path)
    {
        clearstatcache(true, $path);
        if (filesize($path) > 5 * 1024 * 1024 || file_get_contents($path, false, null, 0, 2) !== 'PK') { throw new RuntimeException('Gunakan file .xlsx maksimal 5 MB.'); }
        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::RDONLY | ZipArchive::CHECKCONS) !== true) {
            throw new RuntimeException('Arsip XLSX tidak valid atau terlalu kompleks.');
        }
        try {
            [$seen, $xmlParts, $bounds] = $this->inspectArchive($zip);
        } finally {
            $zip->close();
        }
        if (!isset($seen['xl/workbook.xml'],$seen['[Content_Types].xml'])) { throw new RuntimeException('File bukan workbook XLSX.'); }
        return $this->loadWorkbook($path, $xmlParts, $bounds);
    }

    private function inspectArchive(ZipArchive $zip): array
    {
        if ($zip->numFiles < 1 || $zip->numFiles > 200) { throw new RuntimeException('Arsip XLSX tidak valid atau terlalu kompleks.'); }
        $bytes = 0; $seen = []; $xmlParts = []; $bounds = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = $zip->statIndex($i);
            if ($entry === false || $entry['encryption_method'] !== ZipArchive::EM_NONE
                || !in_array($entry['comp_method'], [ZipArchive::CM_STORE, ZipArchive::CM_DEFLATE], true)) {
                throw new RuntimeException('Header arsip XLSX tidak valid atau terenkripsi.');
            }
            $name = $entry['name']; $bytes += $entry['size'];
            if ($bytes > 30*1024*1024 || isset($seen[$name]) || strpos($name,'..') !== false || preg_match('~^[/\\\\]|vbaProject|externalLinks~i',$name)) { throw new RuntimeException('Arsip XLSX tidak aman atau melebihi batas isi 30 MB.'); }
            $seen[$name] = true;
            $content = $zip->getFromIndex($i, $entry['size'] + 1);
            if ($content === false || strlen($content) !== $entry['size'] || crc32($content) !== $entry['crc']) {
                throw new RuntimeException('Ukuran atau checksum arsip XLSX tidak sesuai.');
            }
            if (preg_match('/\.(xml|rels)$/i', $name)) {
                if (strpos($content,"\0")!==false || preg_match('/<!\s*(DOCTYPE|ENTITY)/i',$content)) { throw new RuntimeException('XML dalam file tidak valid.'); }
                $xml = $content;
                if (substr($name,-5)==='.rels') {
                    $relationships=simplexml_load_string($xml,'SimpleXMLElement',LIBXML_NONET);
                    if ($relationships===false) { throw new RuntimeException('Relasi workbook tidak valid.'); }
                    foreach ($relationships->Relationship as $relationship) {
                        if (strtolower((string)$relationship['TargetMode'])==='external' || preg_match('~^[a-z]+://~i',(string)$relationship['Target'])) { throw new RuntimeException('Link eksternal tidak diperbolehkan dalam format impor.'); }
                    }
                }
                if ($name==='xl/workbook.xml' || $name==='xl/_rels/workbook.xml.rels') { $xmlParts[$name]=$xml; }
                if (strpos($name,'xl/worksheets/')===0 && substr($name,-4)==='.xml') {
                    preg_match_all('/\br\s*=\s*["\']([A-Z]+)([0-9]+)["\']/', $xml, $matches, PREG_SET_ORDER);
                    $maxRow=0; $maxCol=0;
                    foreach ($matches as $match) { $maxRow=max($maxRow,(int)$match[2]); $maxCol=max($maxCol,Coordinate::columnIndexFromString($match[1])); }
                    $bounds[$name]=[$maxRow,$maxCol];
                }
            }
        }
        return [$seen, $xmlParts, $bounds];
    }

    private function loadWorkbook($path, array $xmlParts, array $bounds): array
    {
        $workbook = isset($xmlParts['xl/workbook.xml']) ? simplexml_load_string($xmlParts['xl/workbook.xml']) : false;
        $rels = isset($xmlParts['xl/_rels/workbook.xml.rels']) ? simplexml_load_string($xmlParts['xl/_rels/workbook.xml.rels']) : false;
        if (!$workbook || !$rels) { throw new RuntimeException('Struktur workbook tidak valid.'); }
        $targets=[]; foreach ($rels->Relationship as $rel) { $targets[(string)$rel['Id']]=(string)$rel['Target']; }
        $found=false;
        foreach ($workbook->sheets->sheet as $meta) {
            if ((string)$meta['name'] !== 'Data Asset') { continue; }
            $found=true; $attr=$meta->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships');
            $target=isset($targets[(string)$attr['id']]) ? $targets[(string)$attr['id']] : '';
            $target=$target && $target[0]==='/' ? ltrim($target,'/') : 'xl/'.$target;
            if (!isset($bounds[$target]) || $bounds[$target][0]>10001 || $bounds[$target][1]>8) { throw new RuntimeException('Sheet Data Asset maksimal kolom A–H dan 10.000 baris fisik (500 baris berisi data).'); }
        }
        if (!$found) { throw new RuntimeException('Sheet Data Asset tidak ditemukan.'); }
        $reader = new XlsxReader();
        $reader->setReadDataOnly(true); $reader->setLoadSheetsOnly(['Data Asset']); $reader->setReadFilter(new Asset_import_excel_filter());
        $book = $reader->load($path);
        try {
            $sheet = $book->getSheetByName('Data Asset');
            if (!$sheet) { throw new RuntimeException('Sheet Data Asset tidak ditemukan. Download format resmi.'); }
            foreach (Asset_import::HEADERS as $i=>$header) {
                if ($sheet->getCell([$i + 1, 1])->getValue() !== $header) { throw new RuntimeException('Header kolom tidak sesuai format. Download format resmi.'); }
            }
            $rows = [];
            for ($r=2;$r<=$sheet->getHighestRow();$r++) {
                $values = [];
                for ($c=0;$c<8;$c++) {
                    $cell = $sheet->getCell([$c + 1, $r]);
                    $value = $cell->getDataType() === DataType::TYPE_FORMULA ? '=FORMULA' : (string)$cell->getValue();
                    if ($cell->getDataType() === DataType::TYPE_BOOL) { $value=$cell->getValue() ? 'TRUE' : 'FALSE'; }
                    if ($c===4 && is_numeric($value) && $book->getExcelCalendar()===Date::CALENDAR_MAC_1904) { $value=(string)((float)$value+1462); }
                    $values[] = $value;
                }
                if (count(array_filter($values, function($v) { return trim($v)!==''; }))) { $rows[$r]=$values; }
            }
            return $rows;
        } finally { $book->disconnectWorksheets(); }
    }
}
