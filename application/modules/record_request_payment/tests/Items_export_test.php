<?php
// Reuse controller/model/audit fixtures, but generate and read a real XLSX.
ob_start();
require __DIR__ . '/Tabs_test.php';
ob_clean();
spl_autoload_register(function ($class) {
    if (strpos($class, 'PHPExcel_') !== 0) { return; }
    $file = APPPATH . 'libraries/' . str_replace('_', '/', $class) . '.php';
    if (is_file($file)) { require_once $file; }
});
require APPPATH . 'libraries/PHPExcel.php';
// Windows sandbox cannot rename ZIP files in its virtual system temp directory.
PHPExcel_Shared_File::setUseUploadTempDirectory(true);
set_error_handler(function ($severity, $message, $file, $line) {
    if ($severity === E_DEPRECATED) { return true; }
    throw new ErrorException($message, 0, $severity, $file, $line);
});
function show_error($message, $status = 500) {
    global $exportError;
    $exportError = compact('message', 'status');
}
function export_items($opts = [], $input = []) {
    global $options, $events, $exportError;
    $options = $opts; $events = []; $exportError = null;
    $controller = new Record_request_payment();
    $controller->input->values = $input;
    $level = ob_get_level();
    ob_start();
    try { $controller->export_items_excel(); }
    catch (RuntimeException $e) { check($e->getMessage() === 'denied', 'only permission denial escapes'); }
    $bytes = ob_get_clean();
    check(ob_get_level() === $level, 'export restores output buffers');
    $controller->module_audit_recorder->finish_interrupted();
    check(count($events) === 1, 'one export audit event');
    check(strpos(json_encode($events), 'PRIVATE') === false, 'no names, search or exception details in audit');
    return [$bytes, $events[0], $exportError, $controller];
}
function read_export($bytes) {
    check(substr($bytes, 0, 2) === 'PK', 'valid XLSX ZIP signature');
    $file = tempnam(sys_get_temp_dir(), 'rp-export-');
    try {
        file_put_contents($file, $bytes);
        $zip = new ZipArchive();
        check($zip->open($file) === true, 'XLSX archive opens');
        $stringsXml = simplexml_load_string($zip->getFromName('xl/sharedStrings.xml'), 'SimpleXMLElement', LIBXML_NONET);
        $sheetXml = simplexml_load_string($zip->getFromName('xl/worksheets/sheet1.xml'), 'SimpleXMLElement', LIBXML_NONET);
        $zip->close();
        $strings = [];
        foreach ($stringsXml->si as $entry) { $strings[] = (string) $entry->t; }
        $cells = [];
        foreach ($sheetXml->sheetData->row as $row) {
            foreach ($row->c as $cell) {
                check(!isset($cell->f), 'snapshot text never becomes an Excel formula');
                $type = (string) $cell['t'];
                $value = $type === 's' ? $strings[(int) $cell->v] : (float) $cell->v;
                $cells[(string) $cell['r']] = new ExportCell($value, $type);
            }
        }
        return new ExportSheet($cells, (string) $sheetXml->sheetViews->sheetView->pane['topLeftCell']);
    } finally { unlink($file); }
}
class ExportCell {
    private $value; private $type;
    public function __construct($value, $type) { $this->value = $value; $this->type = $type; }
    public function getValue() { return $this->value; }
    public function getDataType() { return $this->type; }
}
class ExportSheet {
    private $cells; private $freeze;
    public function __construct($cells, $freeze) { $this->cells = $cells; $this->freeze = $freeze; }
    public function getCell($key) { return $this->cells[$key]; }
    public function getFreezePane() { return $this->freeze; }
}
if (($argv[1] ?? '') === '--save-failure') {
    class PHPExcel_IOFactory {
        public static function createWriter($excel, $type) {
            return new class {
                public function save($target) { echo 'PARTIAL'; throw new RuntimeException('PRIVATE_SAVE_FAILURE'); }
            };
        }
    }
    list($bytes, $event, $error) = export_items();
    check($bytes === '' && $event['status'] === 0 && $error['status'] === 500, 'failed save discards partial workbook and records failure');
    ob_end_clean();
    echo "PASS Record per-item export save failure\n";
    exit;
}
list($bytes, $event, $error, $controller) = export_items([], ['start' => 1, 'length' => 1]);
$sheet = read_export($bytes);
check($event['status'] === 1 && $error === null, 'real workbook generated successfully');
check($sheet->getCell('F5')->getValue() === 'DOC-1' && $sheet->getCell('F6')->getValue() === 'DOC-2', 'exports every matched item regardless of current pagination');
check($sheet->getCell('B5')->getValue() === $sheet->getCell('B6')->getValue(), 'separate items from same batch retained');
check($sheet->getCell('P7')->getValue() === 213000.0 && $sheet->getCell('M7')->getValue() === 4000.0, 'snapshot totals and PPH23 amounts');
check($sheet->getCell('N5')->getValue() === 0.0, 'PPH21 excluded when flag is off');
check($sheet->getCell('J5')->getDataType() === PHPExcel_Cell_DataType::TYPE_STRING, 'untrusted descriptions stored as text');
check($sheet->getFreezePane() === 'A5', 'column headers stay visible when scrolling workbook');
check(!array_filter($controller->db->calls, function ($c) { return $c[0] === 'limit'; }), 'unlimited query uses no page limit');
list($bytes, $event) = export_items([], ['search' => 'DOC-2']);
$sheet = read_export($bytes);
check($sheet->getCell('F5')->getValue() === 'DOC-2' && $sheet->getCell('J6')->getValue() === 'TOTAL', 'search exports only matching item');
list($bytes) = export_items(['record_values' => ['keperluan' => '=HYPERLINK("https://example.invalid")', 'request_by' => '00123', 'flag_pph23' => 0, 'flag_pph21' => 1, 'dpp' => 100000.25]]);
$sheet = read_export($bytes);
check($sheet->getCell('J5')->getValue() === '=HYPERLINK("https://example.invalid")' && $sheet->getCell('H5')->getValue() === '00123', 'formula-like text and leading zeros preserved');
check($sheet->getCell('M5')->getValue() === 0.0 && $sheet->getCell('N5')->getValue() === 2000.0, 'PPH21 branch uses snapshot flag');
check($sheet->getCell('K5')->getValue() === 100000.25, 'decimal nominal preserved');
foreach ([['empty' => true], ['fail_query' => 1], ['fail_query' => 2], ['fail_query' => 3], ['query_exception' => true]] as $opts) {
    list($bytes, $event, $error) = export_items($opts);
    check($bytes === '' && $event['status'] === 0, 'failed/empty query never sends workbook');
    check($error['status'] === (!empty($opts['empty']) ? 404 : 500), 'generic empty/query error status');
}
list($bytes, $event) = export_items(['denied' => ['Record_Request_Payment.View']]);
check($bytes === '' && $event['status'] === 0, 'permission denied cannot export');
foreach (['writer_failure', 'writer_exception'] as $key) {
    list($bytes, $event) = export_items([$key => true]);
    check(substr($bytes, 0, 2) === 'PK' && $event['status'] === 1, 'audit writer failure preserves real export');
}
list($bytes, $event) = export_items([], ['search' => ['invalid']]);
check(substr($bytes, 0, 2) === 'PK' && $event['status'] === 1, 'array search safely defaults to empty');
ob_end_clean();
echo "PASS Record per-item export: {$checks} cumulative assertions, real XLSX roundtrip\n";
