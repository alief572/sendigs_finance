<?php
// Fase 5 / penerimaan_uang batch 1 (10 endpoint baca/preview): controller asli + recorder asli; persistensi = fixture in-memory.
// Env AUDIT_CONTROLLER_PATH (opsional) mengganti controller yang diuji; dipakai untuk bukti RED terhadap salinan HEAD.
$events = array(); $scenario = array(); $ci = null; $scenarioCount = 0; $child = $argv[1] ?? null;
function &get_instance() { global $ci; return $ci; }
function log_message($level, $message) {}
function has_permission($permission) { global $scenario; return empty($scenario['deny']); }
function redirect($url) { echo 'REDIRECT:' . $url; }
function base_url($path = '') { return '/' . $path; }
function audit_log($message, $status = null, $context = array()) {
    global $events, $scenario, $child, $ci;
    $event = compact('message', 'status', 'context'); $events[] = $event;
    $events[count($events) - 1]['calls_at_write'] = isset($ci->db->calls) ? $ci->db->calls : array();
    if ($child) { echo 'AUDIT:' . json_encode($events[count($events) - 1]) . "\n"; }
    if (!empty($scenario['writer_exception'])) { throw new RuntimeException('PRIVATE'); }
    return empty($scenario['writer_failure']);
}
// Stub Xlsx_export lewat APPPATH sementara: unduhan asli memanggil exit dan PHPExcel.
$tmpApp = sys_get_temp_dir() . '/pu_batch1_app_' . getmypid() . '/';
@mkdir($tmpApp . 'libraries', 0777, true);
file_put_contents($tmpApp . 'libraries/Xlsx_export.php', '<?php class Xlsx_export { static function report_spec($d, $y, $m, $c) { return array("filename" => "x.xlsx"); } static function download($s, $f) { echo "DOWNLOAD\n"; exit; } }');
define('APPPATH', $tmpApp);
register_shutdown_function(function () use ($tmpApp) { @unlink($tmpApp . 'libraries/Xlsx_export.php'); @rmdir($tmpApp . 'libraries'); @rmdir($tmpApp); });

require __DIR__ . '/legacy_fixture.php';
if (getenv('AUDIT_CONTROLLER_PATH')) { /* legacy_fixture sudah memuat controller HEAD/kerja; lihat blok RED di bawah */ }
require dirname(__DIR__, 3) . '/libraries/Module_audit_recorder.php';
function check($condition, $message) { if (!$condition) { throw new RuntimeException($message); } }

class AuDb extends LegacyDb {
    public $calls = array(); public $db_debug = false;
    function __call($n, $a) { $this->calls[] = $n; return $this; }
    function get() { global $scenario; $this->calls[] = 'get'; if (!empty($scenario['real_get'])) { return parent::get(); } return new LegacyResult($scenario['get_rows'] ?? array()); }
    function delete($table, $where) { global $scenario; if (!empty($scenario['fail_delete_' . $table])) { return false; } return parent::delete($table, $where); }
    function get_where($table, $where) {
        global $scenario; $this->calls[] = 'get_where';
        if (isset($scenario['rows'][$table])) { return new LegacyResult($scenario['rows'][$table]); }
        return parent::get_where($table, $where);
    }
    function query($sql, $params = array()) {
        global $scenario;
        if (!empty($scenario['lock_fail']) && strpos($sql, 'SELECT GET_LOCK(') === 0) { return new LegacyResult(array(array('acquired' => 0))); }
        if (strpos($sql, 'SELECT GET_LOCK(') === 0 || strpos($sql, 'SELECT RELEASE_LOCK(') === 0) { return parent::query($sql, $params); }
        if (!empty($scenario['strict_sql'])) { return parent::query($sql, $params); }
        $rows = $scenario['query_rows'] ?? array();
        if (isset($scenario['query_match']) && strpos($sql, $scenario['query_match']) === false) { $rows = array(); }
        return new LegacyResult($rows);
    }
    function field_exists($field, $table) { global $scenario; return empty($scenario['no_schema']) && (empty($scenario['legacy_schema']) || parent::field_exists($field, $table)); }
    function last_query() { return 'SQL'; }
    function trans_commit() { global $scenario; $this->calls[] = 'trans_commit'; return empty($scenario['commit_fail']) ? true : false; }
    function trans_status() { global $scenario; return empty($scenario['status_fail']); }
    function trans_rollback() { $this->calls[] = 'trans_rollback'; }
    function insert($table, $values) { global $scenario; if (!empty($scenario['fail_' . $table])) { return false; } return parent::insert($table, $values); }
    function insert_batch($table, $values) { global $scenario; if (!empty($scenario['fail_' . $table])) { return false; } return parent::insert_batch($table, $values); }
    function update_batch($table, $values, $key) { global $scenario; return empty($scenario['fail_' . $table]); }
    function update($table, $values = null, $where = null) { global $scenario; return empty($scenario['fail_' . $table]); }
}
class AuInput extends LegacyInput { function get($key = null) { return $this->values['get_' . $key] ?? null; } function is_ajax_request() { return false; } }
class AuTemplate { public $data = array(); public $rendered = null;
    function set($k, $v = null) { if (is_array($k)) { $this->data = $k; } } function title($t) {} function render($v, $d = null) { $this->rendered = $v; echo "<!-- rendered {$v} -->"; } }
class AuAuth { function user_id() { return 'fixture'; }
    function restrict($permission) { global $scenario, $child; if (!empty($scenario['deny'])) { exit; } } }
class AuSession { function userdata($key = null) { return array('username' => 'tester', 'id_user' => 7); } function set_flashdata($k, $v) {} }
class AuLoad { function library($l) {} function model($m, $n = '') {} function helper($h) {} function database($n, $r = false) { global $ci; return $ci->db; } }
class AuModel extends LegacyModel {
    function resolve_alokasi($id) { global $scenario; if (!empty($scenario['model_exception'])) { throw new RuntimeException('PRIVATE'); } return parent::resolve_alokasi($id); }
    function __call($name, $args) {
        global $scenario;
        if (!empty($scenario['model_exception'])) { throw new RuntimeException('PRIVATE'); }
        if ($name === 'get_tab_counts') { return array('invoice' => 0); }
        if ($name === 'get_report_penerimaan_serverside') {
            $rows = $scenario['receipts'] ?? array();
            return array('receipts' => $rows, 'recordsTotal' => count($rows), 'recordsFiltered' => count($rows), 'total_invoices' => 0, 'total_nominal' => 0);
        }
        if ($name === 'get_customer_mutasi_ledger_all') { return $scenario['ledger'] ?? array(); }
        if ($name === 'get_draft_serverside') { $rows = $scenario['draft_rows'] ?? array(); return array('rows' => $rows, 'recordsTotal' => count($rows), 'recordsFiltered' => count($rows)); }
        if ($name === 'get_alokasi_penerimaan') { echo json_encode(array('data' => array())); return null; }
        return array();
    }
}

function pu_make($options, $post = array()) {
    global $scenario, $events, $ci;
    $scenario = $options; $events = array();
    $db = new AuDb;
    $db->invoice = array('id' => '189', 'no_inv' => '189', 'no_invoice' => '189/STM/2026', 'tipe_invoice' => '0', 'non_kons' => '0', 'id_spk_penawaran' => 'SPK', 'tanggal_invoice' => '2026-09-15',
        'total_nominal_jurnal' => '15000000.00', 'dpp_lain_lain_jurnal' => '13750000.00', 'ppn_jurnal' => '0.00', 'pph_jurnal' => '468180.00', 'tagihan_ppn_jurnal' => '25983990.00', 'total_akhir_jurnal' => '25515810.00',
        'saldo_piutang' => '2574990.00', 'nm_customer' => 'PRIVATE_CUSTOMER', 'created_date' => '2026-08-20');
    $db->history = array(array('id_header' => 'OLD'));
    $db->journals = array(array('coa' => '1106-01-02', 'nm_coa' => 'PPh 23 Disetor', 'keterangan' => 'PPh 23 Disetor - 189/STM/2026', 'debit' => '0.00', 'kredit' => '468180.00'));
    if (!empty($options['journals_duplicate'])) { $db->journals[] = $db->journals[0]; }
    if (!empty($options['no_journals'])) { $db->journals = array(); }
    if (!empty($options['invoice_patch'])) { $db->invoice = array_replace($db->invoice, $options['invoice_patch']); }
    if (isset($options['company_id'])) { $db->companyId = $options['company_id']; }
    if (array_key_exists('details', $options)) { $db->history = $options['details']; }
    if (!empty($options['deposit'])) { $db->deposit = true; }
    if (isset($options['voucher'])) { $db->voucher = $options['voucher']; }
    if (isset($options['voucher_mutations'])) { $db->voucherMutations = $options['voucher_mutations']; }
    $ci = legacyController('Penerimaan_uang', $db);
    $ci->input = new AuInput; $ci->template = new AuTemplate;
    $ci->module_audit_recorder = new Module_audit_recorder();
    $ci->auth = new AuAuth; $ci->session = new AuSession; $ci->load = new AuLoad;
    $model = new AuModel; $model->db = $db; $ci->Penerimaan_uang_model = $model; $ci->Deposit_customer_model = new AuModel;
    $ci->input->values = array_merge(array('password' => 'PRIVATE_PASSWORD', 'nm_customer_secret' => 'PRIVATE_CUSTOMER'), $post);
    return $ci;
}
function pu_post_alokasi($extra = array()) {
    return array_merge(array('choose_inv' => array('189'), 'id_alokasi' => 5102, 'pph23_dipotong' => 'N', 'ppn_dipotong' => 'Y', 'customer' => 'Customer', 'nominal_penerimaan_bank' => '2,574,990'), $extra);
}

// Child: jalur exit (unduhan) dan penolakan restrict() tidak bisa dijalankan in-process.
if (defined('AUDIT_FIXTURE_ONLY') && AUDIT_FIXTURE_ONLY) { return; }
if ($child === '--exit' || $child === '--denied') {
    pu_make(json_decode(base64_decode($argv[3]), true), array());
    $GLOBALS['ci']->input->values['year'] = '2026';
    $m = $argv[2]; $ci->$m(55); exit;
}
if ($child === '--subprocess-shutdown') { pu_make(array(), array()); $ci->module_audit_recorder->begin('penerimaan_uang', 'index'); exit; }

function pu_child($mode, $method, $options) {
    global $scenarioCount; $scenarioCount++;
    $raw = (string) shell_exec('"' . PHP_BINARY . '" "' . __FILE__ . '" ' . $mode . ' ' . $method . ' ' . base64_encode(json_encode($options)) . ' 2>&1');
    check(substr_count($raw, 'AUDIT:') === 1, $method . " ($mode): tepat satu event, output: " . $raw);
    $line = strtok(substr($raw, strpos($raw, 'AUDIT:') + 6), "\n");
    return array(json_decode(trim($line), true), $raw);
}
function pu_run($method, $options = array(), $post = array(), $args = array()) {
    global $events, $ci, $scenarioCount; $scenarioCount++;
    pu_make($options, $post);
    ob_start();
    try { call_user_func_array(array($ci, $method), $args); } catch (Throwable $e) { if (empty($options['model_exception'])) { ob_end_clean(); throw $e; } }
    $out = ob_get_clean();
    $ci->module_audit_recorder->finish_interrupted();
    check(count($events) === 1, $method . ': harus tepat satu event, dapat ' . count($events));
    check($events[0]['context']['module'] === 'penerimaan_uang' && $events[0]['context']['methode'] === $method, $method . ': identitas salah');
    check(strpos(json_encode($events[0]['context']), 'PRIVATE') === false, $method . ': data privat bocor');
    return array($events[0], $out, $ci);
}
function pd($e) { return $e['context']['data']; }

$readers = array('index', 'get_report_penerimaan', 'get_receipt_companies', 'get_customer_deposit', 'get_customer_deposit_ledger', 'get_inv_by_cust');

// ---- index
list($e, $out) = pu_run('index');
check($e['status'] === 1 && pd($e)['outcome'] === 'rendered' && strpos($out, 'rendered index') !== false, 'index: rendered');
list($e) = pu_run('index', array('model_exception' => true));
check($e['status'] === 0 && pd($e)['outcome'] === 'interrupted', 'index: model abort -> interrupted');
list($e, $out) = pu_run('index', array('writer_failure' => true));
check($e['status'] === 1 && strpos($out, 'rendered index') !== false, 'index: writer gagal tidak mengubah halaman');
list($e, $out) = pu_run('index', array('writer_exception' => true));
check(strpos($out, 'rendered index') !== false, 'index: writer exception tidak mengubah halaman');
list($e) = pu_child('--denied', 'index', array('deny' => true)); check($e['status'] === 0 && $e['context']['data']['reason'] === 'permission_denied', 'index: denied');

// ---- get_report_penerimaan
$receipt = array('id' => 1, 'tanggal_transaksi' => '2026-10-01', 'nm_bank' => 'BCA', 'nama_bank_acc' => 'STM', 'keterangan' => 'ket', 'nm_customer' => 'PRIVATE_CUSTOMER',
    'company_main' => 'STM', 'pph23_dipotong' => 'N', 'nominal_penerimaan_bank' => 1000, 'is_posted' => 0, 'details' => array());
list($e, $out) = pu_run('get_report_penerimaan', array('receipts' => array($receipt, $receipt)));
check($e['status'] === 1 && pd($e)['row_count'] === 2 && pd($e)['outcome'] === 'listed' && json_decode($out, true)['recordsTotal'] === 2, 'report: 2 baris');
list($e) = pu_run('get_report_penerimaan');
check($e['status'] === 1 && pd($e)['row_count'] === 0, 'report: kosong tetap tercatat');
list($e, $out) = pu_run('get_report_penerimaan', array('deny' => true));
check($e['status'] === 0 && pd($e)['reason'] === 'permission_denied' && json_decode($out, true)['status'] === 0, 'report: denied');
list($e) = pu_run('get_report_penerimaan', array('model_exception' => true));
check($e['status'] === 0 && pd($e)['outcome'] === 'interrupted', 'report: model abort');

// ---- export_excel
list($e, $raw) = pu_child('--exit', 'export_excel', array());
check($e['status'] === 1 && $e['context']['data']['outcome'] === 'exported' && strpos($raw, 'DOWNLOAD') !== false && strpos($raw, 'AUDIT:') < strpos($raw, 'DOWNLOAD'), 'export_excel: event dicatat sebelum unduhan');
list($e) = pu_child('--denied', 'export_excel', array('deny' => true));
check($e['status'] === 0 && $e['context']['data']['reason'] === 'permission_denied', 'export_excel: denied');

// ---- add_penerimaan_uang
$split = array('id' => 55, 'nominal' => 1000, 'nilai_terpakai' => 0, 'jenis_alokasi' => '1', 'no_kwitansi' => '', 'tanggal_transaksi' => '2026-10-01', 'tipe_bank' => '1', 'jenis_bank' => '1');
list($e, $out) = pu_run('add_penerimaan_uang', array('get_rows' => array($split)), array(), array(55));
check($e['status'] === 1 && pd($e)['outcome'] === 'rendered' && pd($e)['receipt_kind'] === 'invoice' && pd($e)['id_alokasi'] === 55 && strpos($out, 'rendered add') !== false, 'add: rendered');
list($e, $out) = pu_run('add_penerimaan_uang', array(), array(), array(999));
check($e['status'] === 0 && pd($e)['outcome'] === 'not_found' && strpos($out, 'REDIRECT:penerimaan_uang') !== false, 'add: alokasi tidak ada -> not_found');
list($e) = pu_child('--denied', 'add_penerimaan_uang', array('deny' => true)); check($e['status'] === 0, 'add: denied');

// ---- get_receipt_companies
list($e, $out) = pu_run('get_receipt_companies', array('rows' => array('tr_invoicing' => array(array('id' => 1, 'consulting_version' => 0, 'tax_treatment' => 'KB')))), array('id_customer' => 'C1'));
check($e['status'] === 1 && pd($e)['outcome'] === 'listed' && pd($e)['row_count'] === 1 && json_decode($out, true)['status'] === 1, 'companies: 1 grup legacy');
list($e, $out) = pu_run('get_receipt_companies', array(), array('id_customer' => array('x')));
check($e['status'] === 0 && pd($e)['reason'] === 'invalid_customer' && json_decode($out, true)['status'] === 0, 'companies: customer tidak valid');
list($e) = pu_run('get_receipt_companies', array(), array());
check($e['status'] === 0, 'companies: customer kosong gagal');

// ---- get_customer_deposit
list($e, $out) = pu_run('get_customer_deposit', array(), array('id_customer' => 'C1'));
check($e['status'] === 1 && pd($e)['outcome'] === 'loaded' && pd($e)['scope'] === 'customer' && json_decode($out, true)['status'] === 1, 'deposit: per customer');
list($e) = pu_run('get_customer_deposit', array(), array('id_customer' => 'C1', 'id_company' => '7'));
check($e['status'] === 1 && pd($e)['scope'] === 'company' && pd($e)['id_company'] === '7', 'deposit: per company');
list($e, $out) = pu_run('get_customer_deposit', array('no_schema' => true), array('id_customer' => 'C1'));
check($e['status'] === 0 && pd($e)['reason'] === 'deposit_unavailable' && json_decode($out, true)['status'] === 0, 'deposit: skema belum ada -> gagal');
list($e) = pu_run('get_customer_deposit', array(), array('id_customer' => 'C1', 'id_company' => '99'));
check($e['status'] === 0 && pd($e)['reason'] === 'deposit_unavailable', 'deposit: company bukan pemilik jurnal');

// ---- get_customer_deposit_ledger
list($e, $out) = pu_run('get_customer_deposit_ledger', array('ledger' => array(array('jenis_mutasi' => 'IN', 'nominal' => '100', 'tanggal' => '2026-10-01'), array('jenis_mutasi' => 'OUT', 'nominal' => '40', 'tanggal' => '2026-10-02'))), array('id_customer' => 'C1'));
check($e['status'] === 1 && pd($e)['row_count'] === 2 && pd($e)['outcome'] === 'listed' && json_decode($out, true)['status'] === 1, 'ledger: 2 mutasi');
list($e, $out) = pu_run('get_customer_deposit_ledger', array(), array());
check($e['status'] === 0 && pd($e)['reason'] === 'invalid_context' && json_decode($out, true)['status'] === 0, 'ledger: konteks tidak valid');
list($e) = pu_run('get_customer_deposit_ledger', array('no_schema' => true), array('id_customer' => 'C1'));
check($e['status'] === 1 && pd($e)['outcome'] === 'listed', 'ledger: saldo gagal dibaca tetap daftar (perilaku lama)');

// ---- get_inv_by_cust
list($e, $out) = pu_run('get_inv_by_cust', array(), array('id_customer' => 'C1'));
check($e['status'] === 1 && pd($e)['row_count'] === 0 && pd($e)['outcome'] === 'listed' && strpos($out, 'No Data Found') !== false, 'inv_by_cust: kosong');
list($e, $out) = pu_run('get_inv_by_cust', array(), array('id_customer' => array('x')));
check($e['status'] === 0 && pd($e)['reason'] === 'invalid_customer', 'inv_by_cust: customer tidak valid');

// ---- process_alokasi (preview invoice lama)
list($e, $out, $c) = pu_run('process_alokasi', array(), pu_post_alokasi());
check($e['status'] === 1 && pd($e)['outcome'] === 'previewed' && pd($e)['invoice_count'] === 1 && pd($e)['mode'] === 'legacy' && pd($e)['deposit_used'] === false && strpos($out, 'rendered process') !== false, 'process_alokasi: preview lama');
list($e, $out) = pu_run('process_alokasi', array('deny' => true), pu_post_alokasi());
check($e['status'] === 0 && pd($e)['reason'] === 'permission_denied', 'process_alokasi: denied');
list($e, $out) = pu_run('process_alokasi', array(), pu_post_alokasi(array('deposit_choice' => 'Y', 'deposit_amount' => '0')));
check($e['status'] === 0 && pd($e)['reason'] === 'invalid_deposit_amount' && strpos($out, 'Nominal deposit') !== false, 'process_alokasi: nominal deposit nol');
list($e, $out) = pu_run('process_alokasi', array('journals_duplicate' => true), pu_post_alokasi());
check($e['status'] === 0 && pd($e)['reason'] === 'preview_rejected', 'process_alokasi: riwayat PPh tidak valid ditolak');
list($e, $out) = pu_run('process_alokasi', array(), pu_post_alokasi(array('deposit_choice' => 'Y', 'deposit_amount' => '100.00')));
check($e['status'] === 0 && pd($e)['reason'] === 'deposit_invoice_processed', 'process_alokasi: deposit untuk invoice yang sudah pernah diproses');
list($e, $out) = pu_run('process_alokasi', array('rows' => array('tr_invoicing' => array(array('id' => 189, 'consulting_version' => 3, 'tipe_invoice' => '0', 'non_kons' => '0')))), pu_post_alokasi());
check($e['status'] === 0 && pd($e)['mode'] === 'consulting' && pd($e)['reason'] === 'preview_rejected' && strpos($out, 'alert-danger') !== false, 'process_alokasi: preview konsultasi gagal -> div error + tercatat gagal');

// ---- process_kwitansi_deposit
list($e, $out) = pu_run('process_kwitansi_deposit', array('deny' => true), array());
check($e['status'] === 0 && pd($e)['reason'] === 'permission_denied', 'process_kwitansi_deposit: denied');
list($e, $out) = pu_run('process_kwitansi_deposit', array('no_schema' => true), array('choose_inv' => array('1')));
check($e['status'] === 0 && pd($e)['reason'] === 'preview_rejected' && strpos($out, 'alert-danger') !== false, 'process_kwitansi_deposit: preview gagal');
list($e, $out) = pu_run('process_kwitansi_deposit', array('writer_exception' => true, 'no_schema' => true), array('choose_inv' => array('1')));
check(strpos($out, 'alert-danger') !== false, 'process_kwitansi_deposit: writer exception tidak mengubah keluaran');

// ---- subprocess: exit nyata -> fallback interrupted
$GLOBALS['scenarioCount']++;
$sub = shell_exec('"' . PHP_BINARY . '" "' . __FILE__ . '" --subprocess-shutdown 2>&1');
check(substr_count((string) $sub, 'AUDIT:') === 1, 'subprocess: satu event interrupted');
$ev = json_decode(trim(substr($sub, strpos($sub, 'AUDIT:') + 6)), true);
check($ev['status'] === 0 && $ev['context']['data']['outcome'] === 'interrupted', 'subprocess: interrupted');

echo 'PASS Penerimaan Uang batch 1: ' . $scenarioCount . " skenario fixture/subprocess, controller+recorder asli\n";
