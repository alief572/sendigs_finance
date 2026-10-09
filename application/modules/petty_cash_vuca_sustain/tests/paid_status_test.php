<?php
/** CLI regression suite: only a disposable database on local Docker MySQL. */
if (PHP_SAPI !== 'cli') exit('CLI only');
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_OFF);
define('BASEPATH', dirname(__DIR__, 4) . '/system/');
define('APPPATH', dirname(__DIR__, 3) . '/');
function log_message($level, $message) {
    if ($level === 'error' && strpos($message, 'Query error:') === 0) fwrite(STDERR, $message . "\n");
}
function is_php($version) { return version_compare(PHP_VERSION, $version, '>='); }
function show_error($message) { throw new RuntimeException($message); }
class BF_Model {}
class Admin_Controller {}
class PaidTestAuth { function user_id() { return 42; } }
class PaidTestInput { public $values; function post() { return $this->values; } }
class PaidTestPayments {
    private $number = 0;
    function generate_id_payment_paid($bank, $date) { return 'PAID-TEST-' . ++$this->number; }
    function generate_id_invoice_jurnal($number) { return 'JOURNAL-TEST-' . $number; }
}
class PaidTestJournal {
    private $number = 0;
    function get_no_buk($branch, $date) { return 'BUK-TEST-' . ++$this->number; }
}
class PaidTestLoader {
    private $controller;
    function __construct($controller) { $this->controller = $controller; }
    function model($path, $alias) { $this->controller->$alias = model($this->controller->db); }
}
function base_url($path = '') { return 'http://localhost:8080/' . $path; }
function site_url($path = '') { return base_url($path); }
require BASEPATH . 'database/DB.php';
require dirname(__DIR__) . '/models/Petty_cash_vuca_sustain_model.php';
// Load the actual payment model too: a parse error here breaks every payment page.
require dirname(__DIR__, 2) . '/pembayaran_material/models/Pembayaran_material_model.php';
require dirname(__DIR__, 2) . '/pembayaran_material/controllers/Pembayaran_material.php';

function check($condition, $message) {
    if (!$condition) throw new RuntimeException($message);
    echo "PASS: $message\n";
}
function model($db) {
    $model = (new ReflectionClass('Petty_cash_vuca_sustain_model'))->newInstanceWithoutConstructor();
    $model->db = $db;
    return $model;
}
function reportFixture($db, $id, $status = 'waiting payment') {
    $db->insert('tr_petty_cash_vuca_sustain', [
        'id' => $id, 'no_payment_hutang' => 'PHP-TEST-' . $id,
        'no_pelaporan' => 'RPC-TEST-' . $id, 'pelaporan_id' => $id,
        'company' => $id % 2 ? 'VUCA' : 'SUSTAIN',
        'periode_start' => '2026-10-01', 'periode_end' => '2026-10-09',
        'jumlah_pencatatan' => 1, 'grand_total' => 100, 'status' => $status,
        'modified_by' => 7, 'modified_on' => '2026-10-01 00:00:00'
    ]);
}
function reportRow($db, $id) { return $db->get_where('tr_petty_cash_vuca_sustain', ['id' => $id])->row(); }
function paymentFixture($db, $id, $noDoc, $type = 'petty_cash_hutang', $status = 1, $paidId = null) {
    $db->insert('payment_approve', ['id' => $id, 'no_doc' => $noDoc, 'tipe' => $type, 'status' => $status, 'id_payment' => $paidId]);
}
function savePayment($controller, $ids, $extra = []) {
    $controller->input->values = [
        'bank' => 'BANK', 'tgl_bayar' => '2026-10-09', 'keterangan_pembayaran' => 'Test payment',
        'mata_uang' => 'IDR', 'payment_bank' => '100', 'total_payment' => '100',
        'id_payment' => implode(',', $ids), 'kurs_payment' => 1, 'admin_charge_bearer' => 'company',
        'bank_charge' => 0, 'supplier_input' => 'TEST', 'nm_supplier_input' => 'Supplier Test'
    ];
    $controller->input->values = array_merge($controller->input->values, $extra);
    ob_start();
    $controller->save_payment();
    $response = json_decode(ob_get_clean(), true);
    check(is_array($response), 'Payment returns JSON');
    if ($response['status'] !== 1) echo 'Payment rejected: ' . $response['pesan'] . "\n";
    return $response;
}
function runSqlFile($db, $path) {
    $results = [];
    $sql = preg_replace('/^\s*--.*$/m', '', file_get_contents($path));
    foreach (explode(';', $sql) as $statement) {
        if (trim($statement) === '') continue;
        $result = $db->query($statement);
        check($result !== false, 'SQL statement succeeds');
        if (preg_match('/^\s*SELECT\b/i', $statement)) $results[] = $result->result_array();
    }
    return $results;
}

$host = getenv('PCVS_TEST_HOST') ?: '127.0.0.1';
if (!in_array($host, ['127.0.0.1', 'localhost', 'mysql'], true)) throw new RuntimeException('Local test hosts only');
$database = 'codex_pcvs_test_' . bin2hex(random_bytes(6));
$db = DB([
    'dbdriver' => 'mysqli', 'hostname' => $host,
    'port' => getenv('PCVS_TEST_PORT') ?: 3307,
    'username' => 'root', 'password' => getenv('PCVS_TEST_PASSWORD') ?: 'sendigs_root_local',
    'database' => '', 'db_debug' => false, 'char_set' => 'utf8mb4', 'dbcollat' => 'utf8mb4_general_ci'
]);
check((bool)$db->conn_id, 'Connected to local disposable test database');
check($db->query('CREATE DATABASE `' . $database . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci'), 'Create isolated schema');
$db->db_select($database);
define('DBACC', $database);
try {
    check($db->query(file_get_contents(dirname(__DIR__) . '/migrations/001_create_tr_petty_cash_vuca_sustain.sql')), 'Create report fixture');
    $model = model($db);
    reportFixture($db, 1);
    check($model->update_status_done('PHP-TEST-1', 42), 'Waiting payment changes to done payment');
    $row = reportRow($db, 1);
    check($row->status === 'done payment' && (int)$row->modified_by === 42, 'Paid status and audit saved');
    check($model->update_status_done('PHP-TEST-1', 99), 'Repeated Paid update succeeds');
    check(reportRow($db, 1) == $row, 'Repeated update preserves audit');
    reportFixture($db, 2, 'draft');
    check(!$model->update_status_done('PHP-TEST-2', 42), 'Draft cannot become Paid');
    check(!$model->update_status_done('MISSING', 42), 'Unknown report rejected');
    $db->query("CREATE TRIGGER fail_pcvs_update BEFORE UPDATE ON tr_petty_cash_vuca_sustain FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Injected status failure'");
    reportFixture($db, 3);
    check(!$model->update_status_done('PHP-TEST-3', 42), 'SQL failure returns false');
    $db->query('DROP TRIGGER fail_pcvs_update');
    runSqlFile($db, __DIR__ . '/fixture.sql');
    $controller = (new ReflectionClass('Pembayaran_material'))->newInstanceWithoutConstructor();
    $controller->db = $db;
    $controller->auth = new PaidTestAuth;
    $controller->input = new PaidTestInput;
    $controller->load = new PaidTestLoader($controller);
    $controller->Pembayaran_material_model = new PaidTestPayments;
    $controller->Jurnal_model = new PaidTestJournal;
    $_FILES = [];

    reportFixture($db, 10);
    paymentFixture($db, 'P10', 'PHP-TEST-10');
    check(savePayment($controller, ['P10'])['status'] === 1, 'Petty cash payment succeeds');
    check(reportRow($db, 10)->status === 'done payment', 'Committed payment marks report Paid');
    check($db->get_where('payment_approve', ['id' => 'P10'])->row()->status == 2, 'Payment completion committed');
    $audit = reportRow($db, 10);
    check(savePayment($controller, ['P10'])['status'] === 1 && reportRow($db, 10) == $audit, 'Repeated payment status sync preserves report audit');

    reportFixture($db, 11);
    // Document matching must work regardless of type or prefix.
    $db->where('id', 11)->update('tr_petty_cash_vuca_sustain', ['no_payment_hutang' => 'LEGACY-DOC']);
    paymentFixture($db, 'P11', 'LEGACY-DOC', 'expense');
    paymentFixture($db, 'P12', 'UNRELATED', 'expense');
    check(savePayment($controller, ['P11', 'P12'])['status'] === 1, 'Mixed payment batch succeeds');
    check(reportRow($db, 11)->status === 'done payment', 'Matching document Paid despite different type and prefix');
    check($db->get_where('payment_approve', ['id' => 'P12'])->row()->status == 2, 'Unrelated payment processed normally');
    paymentFixture($db, 'P13', 'ORDINARY', 'expense');
    check(savePayment($controller, ['P13'])['status'] === 1, 'Ordinary payment succeeds without petty cash match');

    // A failed status transition must undo both earlier status updates and payment writes.
    reportFixture($db, 14);
    reportFixture($db, 15, 'draft');
    paymentFixture($db, 'P14', 'PHP-TEST-14');
    paymentFixture($db, 'P15', 'PHP-TEST-15');
    $headersBefore = $db->count_all('tr_payment_paid');
    check(savePayment($controller, ['P14', 'P15'])['status'] === 0, 'Invalid report transition rejects payment batch');
    check(reportRow($db, 14)->status === 'waiting payment' && reportRow($db, 15)->status === 'draft', 'Report updates rolled back');
    check($db->get_where('payment_approve', ['id' => 'P14'])->row()->status == 1 && $db->count_all('tr_payment_paid') === $headersBefore, 'Payment row and header rolled back');

    reportFixture($db, 16);
    paymentFixture($db, 'P16', 'PHP-TEST-16');
    $db->query("CREATE TRIGGER fail_payment_journal BEFORE INSERT ON tr_jurnal FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Injected journal failure'");
    $headersBefore = $db->count_all('tr_payment_paid');
    $failedJournal = ['jurnal_hutang_1' => [[
        'nama_account' => 'Bank Test', 'coa' => 'BANK', 'tanggal_jurnal' => '2026-10-09',
        'company' => 'STM', 'debit' => '100', 'kredit' => '0', 'keterangan' => 'Test rollback'
    ]]];
    check(savePayment($controller, ['P16'], $failedJournal)['status'] === 0, 'Journal failure rejects payment before report completion');
    check(reportRow($db, 16)->status === 'waiting payment' && $db->get_where('payment_approve', ['id' => 'P16'])->row()->status == 1 && $db->count_all('tr_payment_paid') === $headersBefore, 'Failed payment leaves report waiting and rolls back header');
    $db->query('DROP TRIGGER fail_payment_journal');

    foreach ([20, 21, 22, 23, 24] as $id) reportFixture($db, $id, $id === 23 ? 'draft' : 'waiting payment');
    $db->insert('tr_payment_paid', ['id' => 'HIST-A', 'created_by' => 50, 'created_on' => '2026-10-02 08:00:00']);
    $db->insert('tr_payment_paid', ['id' => 'HIST-B', 'created_by' => 51, 'created_on' => '2026-10-03 08:00:00']);
    $db->insert('tr_payment_paid', ['id' => 'HIST-C', 'created_by' => 52, 'created_on' => '2026-10-03 08:00:00']);
    paymentFixture($db, 'H20-A', 'PHP-TEST-20', 'petty_cash_hutang', 2, 'HIST-A');
    paymentFixture($db, 'H20-B', 'PHP-TEST-20', 'expense', 2, 'HIST-B');
    paymentFixture($db, 'H20-C', 'PHP-TEST-20', 'expense', 2, 'HIST-C');
    paymentFixture($db, 'H21', 'PHP-TEST-21', 'petty_cash_hutang', 1, 'HIST-A');
    paymentFixture($db, 'H22', 'PHP-TEST-22', 'petty_cash_hutang', 2, 'MISSING');
    paymentFixture($db, 'H23', 'PHP-TEST-23', 'petty_cash_hutang', 2, 'HIST-A');
    paymentFixture($db, 'H24', 'PHP-TEST-24', 'petty_cash_hutang', 2, 'HIST-A');
    $migration = dirname(__DIR__) . '/migrations/003_reconcile_paid_status.sql';
    $firstRun = runSqlFile($db, $migration);
    check(count($firstRun[0]) === 2 && (int)$firstRun[1][0]['reports_corrected'] === 2, 'Historical preview and update count two reports, without duplicates');
    check(reportRow($db, 20)->status === 'done payment' && reportRow($db, 20)->modified_by == 52 && reportRow($db, 20)->modified_on === '2026-10-03 08:00:00', 'Historical audit selects newest header and deterministic ID tie-break');
    check(reportRow($db, 21)->status === 'waiting payment' && reportRow($db, 22)->status === 'waiting payment' && reportRow($db, 23)->status === 'draft', 'Unpaid, orphan header, and draft remain unchanged');
    $historicalAudit = reportRow($db, 20);
    $secondRun = runSqlFile($db, $migration);
    check(count($secondRun[0]) === 0 && (int)$secondRun[1][0]['reports_corrected'] === 0 && reportRow($db, 20) == $historicalAudit, 'Historical correction is idempotent');

    $list = $model->get_server_side_data(['length' => 100], ['status' => 'done payment']);
    check($list['recordsFiltered'] === count($list['data']), 'Paid filter count matches returned rows');
    foreach ($list['data'] as $row) check($row['status'] === 'done payment', 'Paid filter returns only completed reports');
    $companyList = $model->get_server_side_data(['length' => 100], ['status' => 'done payment', 'company' => 'SUSTAIN']);
    foreach ($companyList['data'] as $row) check($row['status'] === 'done payment' && $row['company'] === 'SUSTAIN', 'Company and Paid filters combine');
    $record = $model->get_payment_hutang(20);
    check($record->header->status === 'done payment', 'Detail uses corrected historical status');
    ob_start();
    require dirname(__DIR__) . '/views/view.php';
    $detailHtml = ob_get_clean();
    check(strpos($detailHtml, '>Paid</span>') !== false, 'Detail renders Paid label');
    $has_manage = true;
    ob_start();
    require dirname(__DIR__) . '/views/index.php';
    $listHtml = ob_get_clean();
    check(strpos($listHtml, '<option value="done payment">Paid</option>') !== false && strpos($listHtml, "labelText = 'Paid'") !== false, 'List badge and filter display Paid with compatible status value');

    reportFixture($db, 30);
    paymentFixture($db, 'P30', 'PHP-TEST-30');
    $headersBefore = $db->count_all('tr_payment_paid');
    $db->query("CREATE TRIGGER fail_pcvs_update BEFORE UPDATE ON tr_petty_cash_vuca_sustain FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Injected status failure'");
    $db->db_debug = true;
    check(savePayment($controller, ['P30'])['status'] === 0, 'SQL status failure rejects payment');
    check($db->db_debug === true, 'Development debug configuration restored after status failure');
    check(reportRow($db, 30)->status === 'waiting payment' && $db->get_where('payment_approve', ['id' => 'P30'])->row()->status == 1 && $db->count_all('tr_payment_paid') === $headersBefore, 'SQL status failure rolls back payment and report');
    $db->query('DROP TRIGGER fail_pcvs_update');
    echo "All Paid status tests passed.\n";
} finally {
    $db->trans_rollback();
    if (!preg_match('/^codex_pcvs_test_[a-f0-9]{12}$/', $database)) throw new RuntimeException('Unsafe cleanup');
    $db->query('DROP DATABASE `' . $database . '`');
    $db->close();
}
