<?php
// Phase 1 Request Payment batch 5 standalone tests. No database/HTTP/browser calls.
define('AUDIT_FIXTURE_ONLY', true);
if (!defined('DBACC')) { define('DBACC', 'db_sendigs_gl_ss'); }
if (!defined('DBCNL')) { define('DBCNL', 'db_consultant_new'); }
if (!defined('DBHRIS')) { define('DBHRIS', 'hr_sentral'); }
require __DIR__ . '/Audit_test.php';
function has_permission($permission) { return true; }
function base_url($path = '') { return 'http://localhost/' . ltrim($path, '/'); }
function show_404() {
    global $scenario;
    $scenario['_404_called'] = true;
}

if (!class_exists('PHPExcel_Style_Fill')) {
    class PHPExcel_Style_Fill { const FILL_SOLID = 'solid'; }
    class PHPExcel_Style_Alignment { const HORIZONTAL_CENTER = 'center'; const HORIZONTAL_RIGHT = 'right'; const VERTICAL_CENTER = 'center'; }
    class PHPExcel_Style_Border { const BORDER_THIN = 'thin'; }
    class PHPExcel_Cell_DataType { const TYPE_STRING = 's'; }
    class PHPExcel_DummyChain {
        public function __call($name, $args) { return $this; }
        public function setCellValue($cell, $val) { return $this; }
        public function setCellValueExplicit($cell, $val, $type = null) { return $this; }
        public function getStyle($range) { return $this; }
        public function applyFromArray($arr) { return $this; }
        public function getColumnDimension($col) { return $this; }
        public function setWidth($w) { return $this; }
        public function getAlignment() { return $this; }
        public function setHorizontal($h) { return $this; }
        public function getNumberFormat() { return $this; }
        public function setFormatCode($f) { return $this; }
        public function getFont() { return $this; }
        public function setBold($b) { return $this; }
        public function setSize($s) { return $this; }
        public function mergeCells($r) { return $this; }
        public function setTitle($t) { return $this; }
    }
    class PHPExcel {
        public function getActiveSheet() { return new PHPExcel_DummyChain(); }
    }
    class PHPExcel_IOFactory {
        public static function createWriter($excel, $type) {
            return new class {
                public function save($target) {
                    global $scenario;
                    if (!empty($scenario['render_exit'])) { exit; }
                    if (!empty($scenario['export_exception'])) { throw new RuntimeException('PRIVATE_EXCEL_ERROR'); }
                }
            };
        }
    }
}
if (!class_exists('Mpdf')) {
    class Mpdf {
        public $showWatermarkText = false;
        public function __call($name, $args) { return $this; }
        public function Output($filename = '', $dest = '') {
            global $scenario;
            if (!empty($scenario['render_exit'])) { exit; }
            if (!empty($scenario['render_exception'])) { throw new RuntimeException('PRIVATE_PDF_ERROR'); }
        }
    }
}

if (!class_exists('BF_Model')) { class BF_Model { public function __construct() {} public function __get($name) { global $ci; return $ci->$name ?? null; } } }
require dirname(__DIR__) . '/models/Request_payment_model.php';

class Batch5Input extends FixtureInput {
    public $getValues = array();
    public function get($key = null) { return $key === null ? $this->getValues : ($this->getValues[$key] ?? null); }
    public function get_post($key) { return $this->post($key) ?? $this->get($key); }
}

class Batch5Result {
    private $table;
    public function __construct($table) { $this->table = (string) $table; }
    public function row() {
        global $scenario;
        if (strpos($this->table, 'MAX(id)') !== false) { return (object) array('max_id' => 'BK-BNK-2610-0001'); }
        if (!empty($scenario['empty']) || ($scenario['missing_table'] ?? '') === $this->table) { return null; }
        if ($this->table === 'tr_pr_non_po') {
            if (!empty($scenario['no_cash'])) { return null; }
            if (!empty($scenario['delegate_to_cash'])) {
                return (object) array(
                    'id' => 11, 'no_non_po' => 'DPM-CASH-1', 'no_pr' => 'PR-01',
                    'jenis_pr' => $scenario['jenis_pr'] ?? 'pr departemen',
                    'created_date' => '2026-10-11', 'approved_date' => '2026-10-11',
                    'created_by' => 'USR-1', 'total_pr' => 150000, 'nm_pic' => 'PRIVATE_PIC',
                );
            }
            if (!empty($scenario['is_direct_payment'])) { return null; }
            return (object) array(
                'id' => 11, 'no_non_po' => 'CASH-01', 'no_pr' => 'PR-01',
                'jenis_pr' => $scenario['jenis_pr'] ?? 'pr departemen',
                'created_date' => '2026-10-11', 'approved_date' => '2026-10-11',
                'created_by' => 'USR-1', 'total_pr' => 150000, 'nm_pic' => 'PRIVATE_PIC',
            );
        }
        if ($this->table === 'tr_direct_payment') {
            if (!empty($scenario['no_dp'])) { return null; }
            return (object) array(
                'id' => 21, 'ids' => 'KNS-01', 'no_doc' => 'DPM-01', 'tgl_doc' => '2026-10-11',
                'created_date' => '2026-10-11', 'created_by' => 'USR-1', 'deskripsi' => 'PRIVATE_DESC',
                'grand_total' => 250000, 'id_spk_penawaran' => 'SPK-01', 'id_penawaran' => 'PEN-01',
            );
        }
        if (strpos($this->table, 'kons_tr_kasbon_project_header') !== false) {
            if (!empty($scenario['use_expense_branch'])) {
                if (!empty($scenario['joined_kasbon_for_expense'])) {
                    return (object) array(
                        'id' => 'KNS-01', 'id_spk_penawaran' => 'SPK-01', 'id_penawaran' => 'PEN-01',
                        'tipe' => $scenario['kons_tipe'] ?? '1', 'created_by' => 'USR-1',
                        'created_date' => '2026-10-11', 'tgl_pengajuan' => '2026-10-11', 'approved_date' => '2026-10-11',
                    );
                }
                return null;
            }
            if (!empty($scenario['no_kons_kasbon'])) { return null; }
            return (object) array(
                'id' => 'KNS-01', 'no_kasbon' => 'KNS-01', 'id_spk_penawaran' => 'SPK-01', 'id_penawaran' => 'PEN-01',
                'tipe' => $scenario['kons_tipe'] ?? '1', 'created_by' => 'USR-1',
                'created_date' => '2026-10-11', 'tgl_pengajuan' => '2026-10-11', 'approved_date' => '2026-10-11',
            );
        }
        if (strpos($this->table, 'kons_tr_expense_report_project_header') !== false) {
            if (!empty($scenario['no_kons_expense'])) { return null; }
            $scenario['joined_kasbon_for_expense'] = true;
            return (object) array(
                'id' => 'EXP-KNS-01', 'id_header' => 'KNS-01', 'id_spk_penawaran' => 'SPK-01',
                'tipe' => $scenario['kons_tipe'] ?? '1', 'created_by' => 'USR-1',
            );
        }
        if (strpos($this->table, 'kons_tr_spk_penawaran') !== false) {
            return (object) array(
                'id_spk_penawaran' => 'SPK-01', 'id_penawaran' => 'PEN-01', 'id_project' => 'PRJ-01', 'id_company' => '3',
                'nm_project_leader' => 'PRIVATE', 'nm_sales' => 'PRIVATE', 'nm_customer' => 'PRIVATE',
                'waktu_from' => '2026-10-01', 'waktu_to' => '2026-10-31', 'alamat' => 'PRIVATE', 'nm_paket' => 'Paket A',
            );
        }
        if (strpos($this->table, 'kons_tr_penawaran') !== false) {
            return (object) array(
                'id_quotation' => 'PEN-01', 'id_customer' => 'CUST-01', 'id_marketing' => 'EMP-01',
                'company' => '3', 'tipe_informasi_awal' => $scenario['info_awal'] ?? 'Sales',
                'detail_informasi_awal' => 'EMP-02',
            );
        }
        if (strpos($this->table, 'kons_tr_spk_budgeting') !== false) {
            return (object) array('id' => 1, 'id_spk_budgeting' => 'SB-01', 'id_aktifitas' => 'ACT-01', 'nm_aktifitas' => 'Aktifitas', 'nm_item' => 'Item');
        }
        if (strpos($this->table, 'kons_tr_kasbon_project_') !== false) {
            return (object) array('qty_pengajuan' => 1, 'nominal_pengajuan' => 100000);
        }
        if ($this->table === 'rutin_non_planning_header') {
            return (object) array(
                'no_pr' => 'PR-01', 'project_name' => 'PR Dept', 'created_date' => '2026-10-11',
                'app_2_date' => '2026-10-11', 'app_3_date' => '2026-10-11', 'created_by' => 'USR-1',
                'document' => 'doc.pdf', 'bank_name' => 'PRIVATE_BANK', 'bank_account_no' => 'PRIVATE_ACC',
                'bank_account_name' => 'PRIVATE_NAME', 'coa' => '1101-01', 'id_dept' => 5, 'nm_lengkap' => 'PRIVATE_USER',
            );
        }
        if ($this->table === 'material_planning_base_on_produksi') {
            return (object) array(
                'no_pr' => 'PR-01', 'so_number' => 'SO-01', 'project' => 'PR Stok',
                'created_date' => '2026-10-11', 'app_2_date' => '2026-10-11', 'app_3_date' => '2026-10-11',
                'created_by' => 'USR-1', 'tgl_dibutuhkan' => '2026-10-15', 'nm_lengkap' => 'PRIVATE_USER',
            );
        }
        if ($this->table === 'tran_pr_header') {
            return (object) array(
                'no_pr' => 'PR-01', 'category' => 'PR Asset', 'created_date' => '2026-10-11',
                'app_date_2' => '2026-10-11', 'app_date_3' => '2026-10-11', 'created_by' => 'USR-1',
                'dokumen_pendukung' => 'asset.pdf', 'no_coa' => '1101-01', 'nm_coa' => 'Kas',
            );
        }
        return (object) array(
            'id' => 1, 'ids' => 1, 'no_doc' => 'DOC-TEST', 'no_dokumen' => 'DOC-TEST', 'no_req' => 'DOC-TEST',
            'no_non_po' => 'DOC-TEST', 'no_pr' => 'PR-01', 'id_pr' => 'PR-01', 'tipe_pr' => $scenario['tipe_pr'] ?? 'pr departemen',
            'no_kasbon_consultant' => !empty($scenario['consultant_kasbon']) ? 'KNS-01' : '',
            'no_expense_consultant' => !empty($scenario['consultant_expense']) ? 'EXP-KNS-01' : '',
            'nama' => 'PRIVATE_NAME', 'nm_lengkap' => 'PRIVATE_USER', 'username' => 'PRIVATE_USER', 'nm_karyawan' => 'PRIVATE_EMP',
            'name' => 'Dept A', 'nm_company' => 'Sustain', 'company' => 'Sustain', 'company_id' => 'COM006',
            'department_id' => 5, 'id_company' => 'COM006', 'hris_company_id' => 'COM006',
            'no_perkiraan' => '1101-01', 'kode_bank' => 'BNK', 'created_by' => 'USR-1', 'request_by' => 'USR-1',
            'created_on' => '2026-10-11 10:00:00', 'created_date' => '2026-10-11',
            'approved_on' => '2026-10-11', 'approved_date' => '2026-10-11',
            'tgl_doc' => '2026-10-11', 'tgl_pr' => '2026-10-11', 'tanggal' => '2026-10-11',
            'keperluan' => 'PRIVATE_NOTE', 'deskripsi' => 'PRIVATE_DESC', 'keterangan' => 'PRIVATE_KET',
            'kategori' => $scenario['row_kategori'] ?? 'Kasbon', 'tipe' => $scenario['row_tipe'] ?? 'Kasbon',
            'jumlah_kasbon' => 100000, 'kurang_bayar' => null, 'nilai_pengajuan' => 100000,
            'nilai_satuan_request' => 100000, 'total_request' => 100000, 'grand_total' => 100000,
            'doc_file' => 'doc.pdf', 'coa' => '1101-01', 'nm_paket' => 'Paket A',
        );
    }
    public function row_array() {
        $row = $this->row();
        return $row ? (array) $row : array();
    }
    public function result() {
        global $scenario;
        if (!empty($scenario['empty']) || ($scenario['missing_table'] ?? '') === $this->table) { return array(); }
        if ($this->table === 'tr_added_req_payment' && isset($scenario['added_items'])) { return $scenario['added_items']; }
        if (strpos($this->table, 'kons_tr_company') !== false) {
            return array(
                (object) array('id' => '3', 'nama' => 'Sustain', 'nm_company' => 'Sustain'),
                (object) array('id' => '4', 'nama' => 'Vuca', 'nm_company' => 'Vuca'),
                (object) array('id' => '7', 'nama' => 'STM', 'nm_company' => 'STM'),
            );
        }
        if (strpos($this->table, 'kons_tr_expense_report_project_detail') !== false) {
            return array(
                (object) array('id' => 1, 'tipe' => '1', 'id_detail_kasbon' => 1, 'id_spk_budgeting' => 'SB-01', 'qty_expense' => 1, 'nominal_expense' => 50000),
                (object) array('id' => 2, 'tipe' => '2', 'id_detail_kasbon' => 2, 'id_akomodasi' => 'AK-01', 'qty_expense' => 1, 'nominal_expense' => 30000),
                (object) array('id' => 3, 'tipe' => '3', 'id_detail_kasbon' => 3, 'id_others' => 'OT-01', 'qty_expense' => 1, 'nominal_expense' => 20000),
            );
        }
        if (strpos($this->table, 'kons_tr_spk_penawaran_subcont') !== false) {
            return array((object) array('mandays_subcont' => 2, 'mandays_tandem' => 1, 'mandays_rate_tandem' => 50000));
        }
        $row = $this->row();
        return $row ? array($row) : array();
    }
    public function result_array() {
        global $scenario;
        if (!empty($scenario['empty']) || ($scenario['missing_table'] ?? '') === $this->table) { return array(); }
        if ($this->table === 'request_payment' && isset($scenario['copy_items'])) { return $scenario['copy_items']; }
        if ($this->table === 'request_payment') {
            return array(array(
                'no_doc' => 'DOC-TEST', 'nama' => 'PRIVATE_NAME', 'tgl_doc' => '2026-10-11',
                'keperluan' => 'PRIVATE_NOTE', 'tipe' => $scenario['copy_tipe'] ?? 'kasbon',
                'jumlah' => 100000, 'tanggal' => '2026-10-11', 'bank_coa' => '1101-01',
                'bank_nilai' => 100000, 'bank_admin' => 0, 'keterangan' => 'PRIVATE_KET',
                'created_by' => 'USR-1', 'created_on' => '2026-10-11 10:00:00',
                'pay_by' => null, 'pay_on' => null, 'doc_file' => 'doc.pdf', 'doc_file_2' => null,
                'bank_id' => 'BCA', 'accnumber' => 'PRIVATE_ACC', 'accname' => 'PRIVATE_ACC_NAME',
                'ids' => 1, 'no_request' => 'REQ-1', 'app_checker' => 1, 'app_checker_by' => 'CHK',
                'app_checker_date' => '2026-10-11', 'currency' => 'IDR', 'bank_name' => '1101-01 - BCA',
                'admin_bank' => 0, 'link_doc' => null, 'tipe_pph' => null, 'total_pph' => 0,
            ));
        }
        if ($this->table === 'tr_expense_detail') {
            return array(array(
                'id' => 10, 'no_doc' => 'DOC-TEST', 'tanggal' => '2026-10-11',
                'deskripsi' => 'PRIVATE_DESC', 'qty' => 1, 'harga' => 100000, 'total_harga' => 100000,
                'kasbon' => !empty($scenario['expense_kasbon']) ? 50000 : 0,
                'keterangan' => 'PRIVATE_KET', 'doc_file' => 'doc.pdf', 'coa' => '1101-01',
            ));
        }
        if ($this->table === 'tr_pengajuan_rutin_detail') {
            return array(array(
                'id' => 20, 'no_doc' => 'DOC-TEST', 'tanggal' => '2026-10-11',
                'keterangan' => 'PRIVATE_KET', 'nilai' => 100000, 'doc_file' => 'doc.pdf', 'coa' => '1101-01',
            ));
        }
        if ($this->table === 'material_planning_base_on_produksi_detail') {
            return array(array(
                'nm_barang' => 'Barang Stok', 'spec' => 'Spec A', 'qty' => 2,
                'harga' => 50000, 'app_date' => '2026-10-11', 'keterangan' => 'Ket',
                'no_coa' => '1101-01', 'nm_coa' => 'Persediaan',
            ));
        }
        if ($this->table === 'tran_pr_detail') {
            return array(array(
                'nm_barang' => 'Asset A', 'spec' => 'Spec Asset', 'qty' => 1,
                'nilai_pr' => 150000, 'tgl_dibutuhkan' => '2026-10-15', 'tgl_pr' => '2026-10-11', 'info' => 'Info',
            ));
        }
        if ($this->table === 'rutin_non_planning_detail') {
            return !empty($scenario['empty_pr_details']) ? array() : array(array(
                'nm_barang' => 'Dept Item', 'spec' => 'Spec Dept', 'qty' => 1,
                'harga' => 150000, 'tanggal' => '2026-10-11', 'keterangan' => 'Ket',
            ));
        }
        $row = $this->row_array();
        return $row ? array($row) : array();
    }
    public function num_rows() {
        $res = $this->result();
        return count($res);
    }
}

class Batch5Db extends FixtureDb {
    public $queryNumber = 0; public $queryError = 0; public $writeNumber = 0;
    public $writes = array(); public $committed = false; private $fromTable = '';
    public function __call($name, $args) {
        global $scenario;
        if ($name === 'from') { $this->fromTable = $args[0] ?? ''; $this->calls[] = $name; return $this; }
        if (in_array($name, array('get', 'get_where', 'query'), true)) {
            $this->calls[] = $name; $this->queryNumber++;
            $this->queryError = !empty($scenario['query_failure']) || ($scenario['fail_query'] ?? 0) === $this->queryNumber ? 999 : 0;
            $table = $args[0] ?? $this->fromTable;
            if ($name === 'query') {
                preg_match('/from\s+([\w.]+)/i', $table, $match);
                if (stripos($table, 'MAX(id)') !== false) { $table = 'MAX(id)'; }
                else { $table = $match[1] ?? $table; }
            }
            $table = trim(preg_replace('/\s+[a-z0-9_]+$/i', '', (string) $table));
            return new Batch5Result($table);
        }
        if ($name === 'error') { return array('code' => $this->queryError, 'message' => $this->queryError ? 'PRIVATE_SQL_ERROR' : ''); }
        if (in_array($name, array('insert', 'insert_batch', 'update', 'update_batch', 'delete'), true)) {
            $this->calls[] = $name; return $this->write($name, $args);
        }
        if ($name === 'trans_commit') {
            if (!empty($scenario['abort_commit'])) { exit; }
            $result = parent::__call($name, $args); $this->committed = $result; return $result;
        }
        if ($name === 'trans_rollback') { $this->writes = array(); $this->committed = false; }
        if ($name === 'escape') { return "'" . (string) ($args[0] ?? '') . "'"; }
        if ($name === 'escape_str' || $name === 'escape_like_str') { return (string) ($args[0] ?? ''); }
        return parent::__call($name, $args);
    }
    private function write($name, $args) {
        global $scenario;
        $this->writeNumber++;
        if (!empty($scenario[$name . '_failure']) || ($scenario['fail_write'] ?? 0) === $this->writeNumber) {
            $this->failed = true; $this->queryError = 999; return false;
        }
        $this->writes[] = array($name, $args); return true;
    }
}

class Batch5Load extends FixtureLoad {
    public function view($name, $data = array(), $return = false) {
        global $scenario;
        if (!empty($scenario['render_exit'])) { exit; }
        if (!empty($scenario['render_exception'])) { throw new RuntimeException('PRIVATE_VIEW_ERROR'); }
        if ($return) { return '<html></html>'; }
        parent::view($name, $data);
    }
    public function database($name = '', $return = false) {
        global $ci;
        return $ci->db;
    }
}

class Batch5Template extends FixtureTemplate {
    public function __call($name, $args) {
        global $scenario;
        if ($name === 'load_view') {
            if (!empty($scenario['render_exception'])) { throw new RuntimeException('PRIVATE_TEMPLATE_ERROR'); }
            return '<html></html>';
        }
        return parent::__call($name, $args);
    }
}

function run_batch5($method, $options = array(), $post = array(), $get = array(), $callArgs = array()) {
    global $events, $scenario, $ci, $scenarioCount;
    $events = array(); $scenario = $options; $scenarioCount++;
    $ci = new Request_payment();
    $ci->db = new Batch5Db();
    $ci->load = new Batch5Load();
    $ci->input = new Batch5Input();
    $ci->template = new Batch5Template();
    $consultantProp = new ReflectionProperty('Request_payment', 'consultant');
    $consultantProp->setAccessible(true);
    $consultantProp->setValue($ci, $ci->db);

    $model = new Request_payment_model();
    $mConsultant = new ReflectionProperty('Request_payment_model', 'consultant');
    $mConsultant->setAccessible(true);
    $mConsultant->setValue($model, $ci->db);
    $mHris = new ReflectionProperty('Request_payment_model', 'hris');
    $mHris->setAccessible(true);
    $mHris->setValue($model, $ci->db);
    $ci->Request_payment_model = $model;

    $ci->input->values = array_merge(array('reject_reason' => 'PRIVATE_REASON'), $post);
    $ci->input->getValues = array_merge(array(
        'company_id' => '3', 'date_from' => '2026-10-01', 'date_to' => '2026-10-11', 'kategori' => 'Kasbon',
    ), $get);

    ob_start();
    try {
        call_user_func_array(array($ci, $method), $callArgs);
    } catch (Throwable $error) {
        if (isset($ci->module_audit_recorder)) { $ci->module_audit_recorder->finish_interrupted(); }
        if (empty($options['render_exception']) && empty($options['export_exception'])) { ob_end_clean(); throw $error; }
    }
    $output = ob_get_clean();
    if (isset($ci->module_audit_recorder)) { $ci->module_audit_recorder->finish_interrupted(); }
    check(count($events) === 1, $method . ': exactly one audit');
    $event = $events[0];
    check($event['context']['module'] === 'request_payment' && $event['context']['methode'] === $method, 'Endpoint identity');
    check(strpos(json_encode($events), 'PRIVATE') === false, $method . ': private metadata leaked');
    return array($event, $output, $ci);
}

if ($child && strpos($child, '--batch5-exit-') === 0) {
    $m = substr($child, strlen('--batch5-exit-'));
    $args = in_array($m, array('print_cash', 'print_direct_payment', 'print_kasbon'), true) ? array('DOC-TEST') : array();
    run_batch5($m, array('render_exit' => true, 'abort_commit' => true), array(), array(), $args);
    exit;
}

// 1. copy_to_payment tests
list($event, $output, $controller) = run_batch5('copy_to_payment', array('empty' => true));
check($event['status'] === 1 && $event['context']['data']['outcome'] === 'empty_selection' && $event['context']['data']['processed_count'] === 0 && $controller->db->committed, 'copy_to_payment empty selection');

foreach (array('expense', 'kasbon', 'transportasi', 'nonpo', 'periodik', 'direct_payment') as $tipe) {
    foreach (array(array(), array('writer_failure' => true), array('writer_exception' => true)) as $wOpt) {
        list($event, $output, $controller) = run_batch5('copy_to_payment', array_merge(array('copy_tipe' => $tipe), $wOpt));
        check($event['status'] === 1 && $event['context']['data']['outcome'] === 'copied' && $event['context']['data']['processed_count'] === 1 && $controller->db->committed, 'copy_to_payment ' . $tipe);
    }
    list($event, $output, $controller) = run_batch5('copy_to_payment', array('copy_tipe' => $tipe));
    $qTotal = $controller->db->queryNumber;
    $wTotal = $controller->db->writeNumber;
    for ($q = 1; $q <= $qTotal; $q++) {
        list($failed, , $fCtrl) = run_batch5('copy_to_payment', array('copy_tipe' => $tipe, 'fail_query' => $q));
        check($failed['status'] === 0 && !$fCtrl->db->committed, 'copy_to_payment ' . $tipe . ' query failure ' . $q);
    }
    for ($w = 1; $w <= $wTotal; $w++) {
        list($failed, , $fCtrl) = run_batch5('copy_to_payment', array('copy_tipe' => $tipe, 'fail_write' => $w));
        check($failed['status'] === 0 && !$fCtrl->db->committed, 'copy_to_payment ' . $tipe . ' write failure ' . $w);
    }
}
foreach (array(
    array('copy_tipe' => 'expense', 'missing_table' => 'tr_expense'),
    array('copy_tipe' => 'kasbon', 'missing_table' => 'tr_kasbon'),
    array('copy_tipe' => 'transportasi', 'missing_table' => 'tr_transport'),
    array('copy_tipe' => 'nonpo', 'missing_table' => 'tr_non_po_detail'),
    array('copy_tipe' => 'periodik', 'missing_table' => 'tr_pengajuan_rutin_detail'),
    array('copy_tipe' => 'direct_payment', 'missing_table' => 'tr_direct_payment'),
    array('copy_items' => array(array('no_doc' => '', 'tipe' => 'kasbon'))),
    array('trans_begin_failure' => true),
    array('transaction_failure' => true),
    array('trans_commit_failure' => true),
) as $failOpt) {
    list($event, , $controller) = run_batch5('copy_to_payment', $failOpt);
    check($event['status'] === 0 && !$controller->db->committed, 'copy_to_payment failure case');
}
$manyCopy = array();
for ($i = 1; $i <= 101; $i++) {
    $manyCopy[] = array('no_doc' => 'DOC-' . $i, 'tipe' => 'kasbon', 'bank_name' => '1101-01 - BCA');
}
list($event, , $controller) = run_batch5('copy_to_payment', array('copy_items' => $manyCopy));
check($event['status'] === 1 && $event['context']['data']['document_count'] === 101 && count($event['context']['data']['document_ids']) === 100 && $event['context']['data']['processed_count'] === 101, 'copy_to_payment bounded 100 IDs');

// 2. reject_req_payment tests
list($event, $output, $controller) = run_batch5('reject_req_payment', array('empty' => true));
check($event['status'] === 0 && $event['context']['data']['reason'] === 'empty_selection' && json_decode($output, true)['status'] === 0 && !$controller->db->committed, 'reject_req_payment empty selection');
list($event, $output, $controller) = run_batch5('reject_req_payment', array('fail_query' => 1));
check($event['status'] === 0 && $event['context']['data']['reason'] === 'list_added_query_failure' && json_decode($output, true)['status'] === 0, 'reject_req_payment query failure');

foreach (array('Kasbon', 'Transport', 'Expense', 'Periodik', 'Direct Payment') as $tipe) {
    foreach (array(array(), array('writer_failure' => true), array('writer_exception' => true)) as $wOpt) {
        list($event, $output, $controller) = run_batch5('reject_req_payment', array_merge(array('row_tipe' => $tipe), $wOpt));
        check($event['status'] === 1 && $event['context']['data']['outcome'] === 'rejected' && $event['context']['data']['rejected_count'] === 1 && json_decode($output, true)['status'] === 1 && $controller->db->committed, 'reject_req_payment ' . $tipe);
    }
    foreach (array('update_failure', 'trans_begin_failure', 'transaction_failure', 'trans_commit_failure') as $fKey) {
        list($event, $output, $controller) = run_batch5('reject_req_payment', array('row_tipe' => $tipe, $fKey => true));
        check($event['status'] === 0 && json_decode($output, true)['status'] === 0 && !$controller->db->committed, 'reject_req_payment ' . $tipe . ' ' . $fKey);
    }
}
foreach (array(
    array((object) array('no_doc' => '', 'tipe' => 'Kasbon')),
    array((object) array('no_doc' => 'DOC-1', 'tipe' => 'UnknownType')),
) as $badItems) {
    list($event, $output, $controller) = run_batch5('reject_req_payment', array('added_items' => $badItems));
    check($event['status'] === 0 && json_decode($output, true)['status'] === 0 && !$controller->db->committed, 'reject_req_payment invalid item');
}
$manyReject = array();
for ($i = 1; $i <= 101; $i++) { $manyReject[] = (object) array('no_doc' => 'DOC-' . $i, 'tipe' => 'Kasbon'); }
list($event, $output, $controller) = run_batch5('reject_req_payment', array('added_items' => $manyReject));
check($event['status'] === 1 && $event['context']['data']['document_count'] === 101 && count($event['context']['data']['document_ids']) === 100 && $event['context']['data']['rejected_count'] === 101, 'reject_req_payment bounded 100 IDs');

// 3. download_excel_request_payment tests
list($event) = run_batch5('download_excel_request_payment', array('empty' => true));
check($event['status'] === 1 && $event['context']['data']['row_count'] === 0, 'download_excel_request_payment empty');
foreach (array(
    array('row_kategori' => 'Kasbon', 'tipe_pr' => 'pr departemen', 'consultant_kasbon' => true),
    array('row_kategori' => 'Kasbon', 'tipe_pr' => 'pr stok'),
    array('row_kategori' => 'Direct Payment'),
    array('row_kategori' => 'Transport'),
    array('row_kategori' => 'Cash'),
    array('row_kategori' => 'Expense', 'consultant_expense' => true),
    array('row_kategori' => 'Periodik'),
    array('row_kategori' => 'Petty Cash Hutang'),
) as $dlScenario) {
    list($event, , $controller) = run_batch5('download_excel_request_payment', $dlScenario);
    check($event['status'] === 1 && $event['context']['data']['row_count'] === 1, 'download_excel_request_payment success');
    $qCount = $controller->db->queryNumber;
    for ($q = 1; $q <= $qCount; $q++) {
        list($failed) = run_batch5('download_excel_request_payment', array_merge($dlScenario, array('fail_query' => $q)));
        check($failed['status'] === 0, 'download_excel_request_payment query failure ' . $q . ' ' . json_encode($dlScenario));
    }
}
list($event) = run_batch5('download_excel_request_payment', array('export_exception' => true));
check($event['status'] === 0 && $event['context']['data']['reason'] === 'export_exception', 'download_excel_request_payment export exception');

// 4. print_cash tests
list($event) = run_batch5('print_cash', array(), array(), array(), array(''));
check($event['status'] === 0 && $event['context']['data']['reason'] === 'missing_id', 'print_cash missing id');
list($event) = run_batch5('print_cash', array('no_cash' => true, 'no_dp' => true, 'no_kons_kasbon' => true), array(), array(), array('NOT-FOUND'));
check($event['status'] === 0 && $event['context']['data']['reason'] === 'not_found', 'print_cash not found');
list($event) = run_batch5('print_cash', array('no_cash' => true), array(), array(), array('DPM-01'));
check($event['status'] === 1 && $event['context']['methode'] === 'print_cash', 'print_cash delegated to print_direct_payment');

foreach (array('pr departemen', 'pr stok', 'pr asset', 'unknown_jenis') as $jenisPr) {
    list($event, , $controller) = run_batch5('print_cash', array('jenis_pr' => $jenisPr), array(), array(), array('11'));
    check($event['status'] === 1 && $event['context']['data']['jenis_pr'] === $jenisPr, 'print_cash ' . $jenisPr);
    $qCount = $controller->db->queryNumber;
    for ($q = 1; $q <= $qCount; $q++) {
        list($failed) = run_batch5('print_cash', array('jenis_pr' => $jenisPr, 'fail_query' => $q), array(), array(), array('11'));
        check($failed['status'] === 0, 'print_cash ' . $jenisPr . ' query failure ' . $q);
    }
}
list($event) = run_batch5('print_cash', array('render_exception' => true), array(), array(), array('CASH-01'));
check($event['status'] === 0 && $event['context']['data']['reason'] === 'render_exception', 'print_cash render exception');

// 5. print_direct_payment tests
list($event) = run_batch5('print_direct_payment', array(), array(), array(), array(''));
check($event['status'] === 0 && $event['context']['data']['reason'] === 'missing_id', 'print_direct_payment missing id');
list($event) = run_batch5('print_direct_payment', array('no_cash' => true, 'no_dp' => true, 'no_kons_kasbon' => true, 'no_kons_expense' => true), array(), array(), array('NOT-FOUND'));
check($event['status'] === 0 && $event['context']['data']['reason'] === 'not_found', 'print_direct_payment not found');
list($event) = run_batch5('print_direct_payment', array('delegate_to_cash' => true), array(), array(), array('DPM-CASH-1'));
check($event['status'] === 1 && $event['context']['methode'] === 'print_direct_payment', 'print_direct_payment delegated to print_cash');

foreach (array(
    array('no_cash' => true, 'kons_tipe' => '1', 'info_awal' => 'Sales'),
    array('no_cash' => true, 'kons_tipe' => '2', 'info_awal' => 'Website'),
    array('no_cash' => true, 'use_expense_branch' => true, 'kons_tipe' => '3'),
) as $dpScenario) {
    list($event, , $controller) = run_batch5('print_direct_payment', $dpScenario, array(), array(), array('21'));
    check($event['status'] === 1, 'print_direct_payment success');
    $qCount = $controller->db->queryNumber;
    for ($q = 1; $q <= $qCount; $q++) {
        list($failed) = run_batch5('print_direct_payment', array_merge($dpScenario, array('fail_query' => $q)), array(), array(), array('21'));
        check($failed['status'] === 0, 'print_direct_payment query failure ' . $q);
    }
}
list($event) = run_batch5('print_direct_payment', array('no_cash' => true, 'render_exception' => true), array(), array(), array('DPM-01'));
check($event['status'] === 0 && $event['context']['data']['reason'] === 'render_exception', 'print_direct_payment render exception');

// 6. print_kasbon tests
list($event) = run_batch5('print_kasbon', array(), array(), array(), array(''));
check($event['status'] === 0 && $event['context']['data']['reason'] === 'missing_id', 'print_kasbon missing id');
list($event) = run_batch5('print_kasbon', array('no_kons_kasbon' => true, 'no_kons_expense' => true), array(), array(), array('NOT-FOUND'));
check($event['status'] === 0 && $event['context']['data']['reason'] === 'not_found', 'print_kasbon not found');

foreach (array(
    array('consultant_kasbon' => true, 'kons_tipe' => '1'),
    array('use_expense_branch' => true, 'kons_tipe' => '2'),
) as $kbScenario) {
    list($event, , $controller) = run_batch5('print_kasbon', $kbScenario, array(), array(), array('KNS-01'));
    check($event['status'] === 1, 'print_kasbon success');
    $qCount = $controller->db->queryNumber;
    for ($q = 1; $q <= $qCount; $q++) {
        list($failed) = run_batch5('print_kasbon', array_merge($kbScenario, array('fail_query' => $q)), array(), array(), array('KNS-01'));
        check($failed['status'] === 0, 'print_kasbon query failure ' . $q);
    }
}
list($event) = run_batch5('print_kasbon', array('render_exception' => true), array(), array(), array('KNS-01'));
check($event['status'] === 0 && $event['context']['data']['reason'] === 'render_exception', 'print_kasbon render exception');

// Subprocess shutdown tests
foreach (array('copy_to_payment', 'reject_req_payment', 'download_excel_request_payment', 'print_direct_payment') as $method) {
    $output = shell_exec('"' . PHP_BINARY . '" "' . __FILE__ . '" --batch5-exit-' . $method);
    check(substr_count($output, 'AUDIT:') === 1, $method . ': real shutdown exactly one event');
    $event = json_decode(trim(substr($output, strpos($output, 'AUDIT:') + 6)), true);
    check($event['status'] === 0 && $event['context']['data']['outcome'] === 'interrupted', $method . ': real shutdown interrupted');
}

// Constructor remains opt-in.
$events = array(); $scenario = array(); $ci = new Request_payment();
check(!isset($ci->module_audit_recorder) && !$events, 'Constructor must not activate scope'); $scenarioCount++;
echo 'PASS Request Payment batch 5: ' . ($scenarioCount + 4) . " scenarios, real controller/recorder and model\n";
