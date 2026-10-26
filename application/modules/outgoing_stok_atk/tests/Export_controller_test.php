<?php
define('BASEPATH', __DIR__);
class Admin_Controller {}
function log_message($level, $message) {}
function show_error($message, $status) { throw new RuntimeException($message, $status); }
require dirname(__DIR__) . '/controllers/Outgoing_stok_atk.php';
require dirname(__DIR__, 3) . '/libraries/PHPExcel.php';
set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) { return false; }
    throw new ErrorException($message, 0, $severity, $file, $line);
});
class ExportAuth {
    public $allowed = true, $permission;
    public function restrict($permission) { $this->permission = $permission; if (!$this->allowed) { throw new RuntimeException('Denied', 403); } }
}
class ExportInput {
    public function get($key) { return array('search' => 'Kertas', 'column' => '2', 'direction' => 'asc')[$key] ?? null; }
}
class ExportLoader { public function library($name) {} }
class ExportOutput {
    public $headers = array(), $body;
    public function set_header($value) { $this->headers[] = $value; return $this; }
    public function set_content_type($value) { return $this->set_header($value); }
    public function set_output($value) { $this->body = $value; return $this; }
}
class ExportModel {
    public $rows = array(), $arguments, $fail = false;
    public function get_export_rows(...$args) {
        $this->arguments = $args;
        foreach ($this->rows as $row) { yield $row; }
        if ($this->fail) { throw new RuntimeException('Sensitive SQL error'); }
    }
}
class ExportController extends Outgoing_stok_atk {
    public function __construct() {
        $this->auth = new ExportAuth(); $this->input = new ExportInput(); $this->load = new ExportLoader();
        $this->output = new ExportOutput(); $this->outgoing_stok_atk_model = new ExportModel();
    }
}
$checks = 0;
function export_check($value, $message) { global $checks; $checks++; if (!$value) { throw new RuntimeException($message); } }
$controller = new ExportController();
$controller->outgoing_stok_atk_model->rows = array(array('kode_trans' => '0001', 'tanggal' => '20-Oct-2026',
    'department' => 'SALES', 'name' => '=1+1', 'qty' => 2.5555, 'unit' => 'PCS', 'pic' => '+SUM(A1)', 'by' => 'Finance', 'dated' => '20-Oct-2026 10:00:00'));
$controller->export_excel();
export_check($controller->auth->permission === 'Outgoing_Stok_ATK.View', 'Endpoint restricts View permission');
export_check($controller->outgoing_stok_atk_model->arguments === array('Kertas', '2', 'asc'), 'Search and ordering forwarded');
export_check(substr($controller->output->body, 0, 2) === 'PK', 'Response contains real XLSX bytes');
export_check(strpos(implode('\n', $controller->output->headers), '.xlsx') !== false, 'Attachment filename uses xlsx');
$file = tempnam(sys_get_temp_dir(), 'atk_test_');
try {
    file_put_contents($file, $controller->output->body);
    $book = PHPExcel_IOFactory::load($file); $sheet = $book->getActiveSheet();
    export_check($sheet->getCell('B2')->getValue() === '0001', 'Leading zeros retained');
    export_check($sheet->getCell('E2')->getValue() === '=1+1' && $sheet->getCell('E2')->getDataType() === 's', 'Item formula remains text');
    export_check($sheet->getCell('H2')->getDataType() === 's', 'PIC formula remains text');
    export_check($sheet->getCell('F2')->getDataType() === 'n' && abs($sheet->getCell('F2')->getValue() - 2.5555) < 0.00001, 'Quantity retains numeric precision');
    export_check($sheet->getFreezePane() === 'A2' && $sheet->getAutoFilter()->getRange() === 'A1:J2', 'Header frozen and filterable');
    $sheet->getAutoFilter()->getColumn('E')->createRule()->setRule(PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_COLUMN_RULE_EQUAL, '=1+1');
    PHPExcel_IOFactory::createWriter($book, 'Excel2007')->save($file);
    $filtered = PHPExcel_IOFactory::load($file);
    export_check(count($filtered->getActiveSheet()->getAutoFilter()->getColumn('E')->getRules()) === 1, 'Shared writer serializes actual filter rules without warnings');
    $filtered->disconnectWorksheets();
    $book->disconnectWorksheets();
} finally { unlink($file); }
$empty = new ExportController(); $empty->export_excel();
$file = tempnam(sys_get_temp_dir(), 'atk_test_');
try {
    file_put_contents($file, $empty->output->body); $book = PHPExcel_IOFactory::load($file);
    export_check($book->getActiveSheet()->getHighestRow() === 1, 'Empty export contains header only');
    $book->disconnectWorksheets();
} finally { unlink($file); }
$denied = new ExportController(); $denied->auth->allowed = false;
try { $denied->export_excel(); export_check(false, 'Unauthorized export must fail'); }
catch (RuntimeException $e) { export_check($e->getCode() === 403 && $denied->output->body === null, 'Denied request sends no workbook'); }
$failed = new ExportController(); $failed->outgoing_stok_atk_model->rows = $controller->outgoing_stok_atk_model->rows;
$failed->outgoing_stok_atk_model->fail = true;
try { $failed->export_excel(); export_check(false, 'Failed export must fail'); }
catch (RuntimeException $e) {
    export_check($e->getCode() === 500 && strpos($e->getMessage(), 'SQL') === false, 'Safe error response');
    export_check($failed->output->body === null && !$failed->output->headers, 'Failure after first row sends no partial workbook or attachment headers');
}
echo 'PASS Outgoing export controller/workbook: ' . $checks . " checks\n";
