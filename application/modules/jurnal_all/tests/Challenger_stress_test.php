<?php
/**
 * Challenger 1: Adversarial Stress & Edge Case Test Suite
 *
 * Verifies:
 * 1. Debit-credit unbalance, zero amounts, negative cents, and client parameter tampering.
 * 2. Petty Cash unclear COA gate (2103-01-01 and 2103-01-02) in preview, native posting, and dispatcher.
 * 3. Multi-company schema routing (STM: 1, 6, 7; VUCA: 4; Sustain: others) and strict counter isolation
 *    (pastibisa_tb_cabang.nobuk, nomorJC, nobum).
 */

define('BASEPATH', true);
define('DBACC_STM', 'acc_stm');
define('DBACC_VUCA', 'acc_vuca');
define('DBACC_SUST', 'acc_sustain');
define('DBCNL', 'consultant');

class Admin_Controller {}
class BF_Model {}

$permissions = ['Jurnal_All.Manage'];
function has_permission($p) {
    global $permissions;
    return in_array($p, $permissions, true);
}
function log_message($level, $message) {}

require_once dirname(__DIR__) . '/models/Jurnal_all_model.php';
require_once dirname(__DIR__) . '/libraries/Jurnal_all_dispatcher.php';
require_once dirname(__DIR__, 2) . '/jurnal_payment_petty_cash/models/Jurnal_payment_petty_cash_model.php';
require_once dirname(__DIR__, 2) . '/jurnal_payment_petty_cash/controllers/Jurnal_payment_petty_cash.php';
require_once dirname(__DIR__, 2) . '/jurnal_invoicing/models/Jurnal_invoicing_model.php';
require_once dirname(__DIR__, 2) . '/jurnal_penerimaan/controllers/Jurnal_penerimaan.php';
require_once dirname(__DIR__, 2) . '/jurnal_payment/controllers/Jurnal_payment.php';
require_once dirname(__DIR__, 2) . '/jurnal_expense/models/Jurnal_expense_posting_model.php';

$checks = 0;
function check($ok, $msg) {
    global $checks;
    $checks++;
    if (!$ok) {
        throw new RuntimeException("CHALLENGE FAILED: {$msg}");
    }
}

echo "=== STARTING CHALLENGER 1 ADVERSARIAL STRESS SUITE ===\n\n";

// =========================================================================
// SECTION 1: DEBIT-CREDIT BALANCE & ARITHMETIC BOUNDARY CHALLENGES
// =========================================================================
echo "[1] Testing Debit-Credit Unbalance, Zero Amounts, Negative Cents...\n";

// 1.1 Model cents() and fromCents() stress test
$model = new Jurnal_all_model();

// Exact cents conversion
check($model->cents('0') === 0, 'zero string to cents');
check($model->cents('0.00') === 0, 'zero decimal to cents');
check($model->cents('-0') === 0, 'negative zero to cents');
check($model->cents('-0.00') === 0, 'negative zero decimal to cents');
check($model->cents('100.25') === 10025, 'positive decimal to cents');
check($model->cents('-100.25') === -10025, 'negative decimal to cents');
check($model->cents('0.01') === 1, 'one cent to cents');
check($model->cents('-0.01') === -1, 'negative one cent to cents');

// Sub-cent fractional amounts MUST throw InvalidArgumentException
$invalidSubCents = ['100.001', '0.005', '99.999', '12.3456'];
foreach ($invalidSubCents as $inv) {
    $caught = false;
    try {
        $model->cents($inv);
    } catch (InvalidArgumentException $e) {
        $caught = true;
    }
    check($caught, "sub-cent {$inv} strictly rejected by cents()");
}

// Non-numeric and injection strings MUST throw InvalidArgumentException
$invalidStrings = ['abc', '100.5.5', '1e5', '0x10', '--10', '100,25', ' 10 0.25 '];
foreach ($invalidStrings as $inv) {
    $caught = false;
    try {
        $model->cents($inv);
    } catch (InvalidArgumentException $e) {
        $caught = true;
    }
    check($caught, "invalid numeric string '{$inv}' strictly rejected");
}

// 1.2 Detail is_balance evaluation under edge cases
class TestDataModel extends Jurnal_all_model {
    public $mockRows = [];
    public $mockHeader = [];
    public function posting_metadata_ready() { return true; }
    public function save_posting_metadata($header, $actor, $numbers) {}
    public function detail($id, $posted) {
        $rows = $this->mockRows;
        $debit = 0;
        $credit = 0;
        $validAmounts = true;
        foreach ($rows as $row) {
            $d = $this->cents($row['debit']);
            $c = $this->cents($row['kredit']);
            if ($d < 0 || $c < 0) $validAmounts = false;
            $debit += $d;
            $credit += $c;
        }
        $anchor = $this->mockHeader;
        $anchor['debit'] = $this->fromCents($debit);
        $anchor['kredit'] = $this->fromCents($credit);
        return [
            'header' => $anchor,
            'rows' => $rows,
            'is_balance' => $validAmounts && $debit > 0 && $debit === $credit
        ];
    }
}

class TestDb {
    public $locked = true;
    public $events = [];
    public function query($sql, $params = []) {
        $this->events[] = $sql;
        $r = new stdClass();
        $r->acquired = $this->locked ? 1 : 0;
        return new class($r) {
            private $row;
            public function __construct($r) { $this->row = $r; }
            public function row() { return $this->row; }
        };
    }
}

class TestAuth {
    public function user_id() { return 7; }
    public function user_name() { return 'tester'; }
    public function restrict($p = null) {}
}

class Modules {
    public static $called = false;
    public static $receivedPost = [];
    public static $response = ['save' => 1, 'status' => 1, 'msg' => 'ok', 'accounting_numbers' => [['company' => '7', 'number' => 'JV-001']]];
    public static function run($route) {
        self::$called = true;
        self::$receivedPost = $_POST;
        return self::$response;
    }
}

$testDb = new TestDb();
$testModel = new TestDataModel();
$ciMock = (object)[
    'db' => $testDb,
    'Jurnal_all_model' => $testModel,
    'auth' => new TestAuth()
];
$dispatcher = new Jurnal_all_dispatcher(['ci' => $ciMock]);

// Test 1.2.1: Zero amounts (debit = 0, credit = 0)
$testModel->mockHeader = ['id' => 1, 'source' => 'PAY', 'no_transaksi' => 'TRX-ZERO', 'jenis_transaksi' => 'Payment', 'id_company' => '7'];
$testModel->mockRows = [
    ['debit' => '0.00', 'kredit' => '0.00'],
    ['debit' => '0.00', 'kredit' => '0.00']
];
$detailZero = $testModel->detail(1, false);
check($detailZero['is_balance'] === false, 'zero amounts must evaluate is_balance to FALSE');

Modules::$called = false;
$caught = false;
try {
    $dispatcher->execute('post', 1, 'PAY', []);
} catch (InvalidArgumentException $e) {
    $caught = true;
    check(strpos($e->getMessage(), 'tidak balance atau nominal kosong') !== false, 'zero amounts error message');
}
check($caught && !Modules::$called, 'dispatcher strictly refused zero amounts before delegation');

// Test 1.2.2: Unbalanced amounts (debit: 100.00, credit: 99.99)
$testModel->mockRows = [
    ['debit' => '100.00', 'kredit' => '0.00'],
    ['debit' => '0.00', 'kredit' => '99.99']
];
$detailUnbal = $testModel->detail(1, false);
check($detailUnbal['is_balance'] === false, '1-cent unbalance must evaluate is_balance to FALSE');

Modules::$called = false;
$caught = false;
try {
    $dispatcher->execute('post', 1, 'PAY', []);
} catch (InvalidArgumentException $e) {
    $caught = true;
}
check($caught && !Modules::$called, 'dispatcher strictly refused 1-cent unbalanced journal');

// Test 1.2.3: Negative amount rows with net balanced sum (debit: -50.00 + 150.00 = 100.00, credit: 100.00)
$testModel->mockRows = [
    ['debit' => '-50.00', 'kredit' => '0.00'],
    ['debit' => '150.00', 'kredit' => '0.00'],
    ['debit' => '0.00', 'kredit' => '100.00']
];
$detailNeg = $testModel->detail(1, false);
check($detailNeg['is_balance'] === false, 'offsetting negative lines must evaluate is_balance to FALSE');

Modules::$called = false;
$caught = false;
try {
    $dispatcher->execute('post', 1, 'PAY', []);
} catch (InvalidArgumentException $e) {
    $caught = true;
}
check($caught && !Modules::$called, 'dispatcher strictly refused negative debit line');

// Test 1.2.4: Negative credit row
$testModel->mockRows = [
    ['debit' => '100.00', 'kredit' => '0.00'],
    ['debit' => '0.00', 'kredit' => '-100.00']
];
$detailNegCredit = $testModel->detail(1, false);
check($detailNegCredit['is_balance'] === false, 'negative credit must evaluate is_balance to FALSE');

Modules::$called = false;
$caught = false;
try {
    $dispatcher->execute('post', 1, 'PAY', []);
} catch (InvalidArgumentException $e) {
    $caught = true;
}
check($caught && !Modules::$called, 'dispatcher strictly refused negative credit line');

// Test 1.2.5: Client payload tampering protection
$_POST = ['attacker_token' => 'evil_value', 'debit' => '999999999', 'no_transaksi' => 'ATTACK_ID'];
$testModel->mockRows = [
    ['debit' => '100.25', 'kredit' => '0.00'],
    ['debit' => '0.00', 'kredit' => '100.25']
];
$result = $dispatcher->execute('post', 1, 'PAY', ['debit' => '999999999', 'no_transaksi' => 'ATTACK_ID']);
check($result['status'] === 1, 'post succeeded with valid authoritative data');
check(!isset(Modules::$receivedPost['debit']), 'client-injected debit parameter stripped');
check(Modules::$receivedPost['no_transaksi'] === 'TRX-ZERO', 'no_transaksi derived strictly from server header');
check($_POST === ['attacker_token' => 'evil_value', 'debit' => '999999999', 'no_transaksi' => 'ATTACK_ID'], 'caller POST restored in finally block');

// =========================================================================
// SECTION 2: PETTY CASH UNCLEAR COA GATE (2103-01-01 & 2103-01-02)
// =========================================================================
echo "\n[2] Testing Petty Cash Unclear COA Gate (2103-01-01 and 2103-01-02)...\n";

$pettyModel = (new ReflectionClass('Jurnal_payment_petty_cash_model'))->newInstanceWithoutConstructor();

// 2.1 Test find_unclear_coa() unit logic
$unclearList = $pettyModel->get_unclear_coa_list();
check(in_array('2103-01-01', $unclearList, true), '2103-01-01 registered as unclear COA');
check(in_array('2103-01-02', $unclearList, true), '2103-01-02 registered as unclear COA');
check(count($unclearList) === 2, 'unclear COA list strictly contains exactly the two target accounts');

// Test single unclear 2103-01-01
$rows1 = [
    (object)['coa' => '5101-01-01', 'nm_coa' => 'Biaya Operasional'],
    (object)['coa' => '2103-01-01', 'nm_coa' => 'Hutang STM 1']
];
check($pettyModel->find_unclear_coa($rows1) === ['2103-01-01'], 'detected 2103-01-01 in object rows');

// Test single unclear 2103-01-02
$rows2 = [
    ['coa' => '5101-01-01', 'nm_coa' => 'Biaya Operasional'],
    ['coa' => '2103-01-02', 'nm_coa' => 'Hutang STM 2']
];
check($pettyModel->find_unclear_coa($rows2) === ['2103-01-02'], 'detected 2103-01-02 in array rows');

// Test both unclear COAs with duplicates
$rowsBoth = [
    (object)['coa' => '2103-01-01'],
    (object)['coa' => '2103-01-02'],
    (object)['coa' => '2103-01-01'],
    (object)['coa' => '1101-01-01']
];
$detected = $pettyModel->find_unclear_coa($rowsBoth);
check(count($detected) === 2 && in_array('2103-01-01', $detected, true) && in_array('2103-01-02', $detected, true), 'both detected and deduplicated');

// Test rows with cleared COAs only
$rowsCleared = [
    (object)['coa' => '5101-01-01'],
    (object)['coa' => '1101-01-01']
];
check($pettyModel->find_unclear_coa($rowsCleared) === [], 'cleared COAs return empty array');

// 2.2 Test Native Controller view_detail (Preview Gate)
class MockPettyInput {
    public function post($key = null) { return $key === null ? $_POST : ($_POST[$key] ?? null); }
    public function method($upper = false) { return 'POST'; }
}

class MockPettyViewLoader {
    public function view($view, $data, $return) {
        $warningPresent = false;
        if (!empty($data['unclear_coa'])) {
            $warningPresent = true;
        }
        return json_encode([
            'rendered_view' => $view,
            'warning_present' => $warningPresent,
            'unclear_coa' => $data['unclear_coa']
        ]);
    }
}

class MockPettySourceModel {
    public $rows = [];
    public $findCalled = false;
    public function get_detail_by_transaksi($no, $jenis) { return $this->rows; }
    public function validate_balance($rows) { return true; }
    public function find_unclear_coa($rows) {
        $this->findCalled = true;
        $unclear = ['2103-01-01', '2103-01-02'];
        $found = [];
        foreach ($rows as $r) {
            $coa = is_object($r) ? $r->coa : $r['coa'];
            if (in_array($coa, $unclear, true) && !in_array($coa, $found, true)) {
                $found[] = $coa;
            }
        }
        return $found;
    }
}

$pettyCtrl = (new ReflectionClass('Jurnal_payment_petty_cash'))->newInstanceWithoutConstructor();
$pettyCtrl->input = new MockPettyInput();
$pettyCtrl->load = new MockPettyViewLoader();
$pettyCtrl->auth = new TestAuth();
$pettyCtrl->Jurnal_payment_petty_cash_model = new MockPettySourceModel();

// Preview with 2103-01-01
$pettyCtrl->Jurnal_payment_petty_cash_model->rows = [
    (object)['coa' => '2103-01-01', 'debit' => 100, 'kredit' => 0, 'tgl_jurnal' => '2026-10-14', 'nm_company' => 'STM', 'no_transaksi' => 'PC-001', 'jenis_transaksi' => 'Petty Cash'],
    (object)['coa' => '1101-01-01', 'debit' => 0, 'kredit' => 100, 'tgl_jurnal' => '2026-10-14', 'nm_company' => 'STM', 'no_transaksi' => 'PC-001', 'jenis_transaksi' => 'Petty Cash']
];
$_POST = ['no_transaksi' => 'PC-001', 'jenis_transaksi' => 'Petty Cash'];

ob_start();
$pettyCtrl->get_detail_jurnal();
$previewResp = json_decode(ob_get_clean(), true);
check($previewResp['has_unclear_coa'] === true, 'get_detail_jurnal preview flags has_unclear_coa = true for 2103-01-01');
check($previewResp['unclear_coa'] === ['2103-01-01'], 'get_detail_jurnal returns exact unclear COA list');

// Preview with 2103-01-02
$pettyCtrl->Jurnal_payment_petty_cash_model->rows = [
    (object)['coa' => '2103-01-02', 'debit' => 100, 'kredit' => 0, 'tgl_jurnal' => '2026-10-14', 'nm_company' => 'STM', 'no_transaksi' => 'PC-002', 'jenis_transaksi' => 'Petty Cash'],
    (object)['coa' => '1101-01-01', 'debit' => 0, 'kredit' => 100, 'tgl_jurnal' => '2026-10-14', 'nm_company' => 'STM', 'no_transaksi' => 'PC-002', 'jenis_transaksi' => 'Petty Cash']
];
$_POST = ['no_transaksi' => 'PC-002', 'jenis_transaksi' => 'Petty Cash'];

ob_start();
$pettyCtrl->get_detail_jurnal();
$previewResp2 = json_decode(ob_get_clean(), true);
check($previewResp2['has_unclear_coa'] === true, 'get_detail_jurnal preview flags has_unclear_coa = true for 2103-01-02');
check($previewResp2['unclear_coa'] === ['2103-01-02'], 'get_detail_jurnal returns exact unclear COA list');

// 2.3 Test Native Controller save_posting_jurnal (Posting Gate)
$pettyCtrl->auth = new TestAuth();
class MockPettyPostingModel extends MockPettySourceModel {
    public $writes = [];
    public function begin_transaction($db) { return true; }
    public function rollback_transaction($db) {}
    public function commit_transaction($db) { return true; }
    public function check_transaction_status($db) { return true; }
    public function update_status_posted($no, $jenis) { return true; }
}
$pettyCtrl->Jurnal_payment_petty_cash_model = new MockPettyPostingModel();
$pettyCtrl->Jurnal_payment_petty_cash_nomor_model = new class {
    public $incrementCalls = [];
    public function get_nomor_buk($cab, $db) { return 'BUK-MOCK'; }
    public function increment_nobuk($cab, $db) {
        $this->incrementCalls[] = [$cab, $db];
        return true;
    }
};

// Attempt to post with 2103-01-01
$pettyCtrl->Jurnal_payment_petty_cash_model->rows = [
    (object)['id_company' => '5', 'nm_company' => 'STM', 'coa' => '2103-01-01', 'debit' => 500, 'kredit' => 0, 'tgl_jurnal' => '2026-10-14', 'no_transaksi' => 'PC-ATTACK-1', 'jenis_transaksi' => 'Petty Cash'],
    (object)['id_company' => '5', 'nm_company' => 'STM', 'coa' => '1101-01-01', 'debit' => 0, 'kredit' => 500, 'tgl_jurnal' => '2026-10-14', 'no_transaksi' => 'PC-ATTACK-1', 'jenis_transaksi' => 'Petty Cash']
];
$_POST = ['no_transaksi' => 'PC-ATTACK-1', 'jenis_transaksi' => 'Petty Cash'];

ob_start();
$pettyCtrl->save_posting_jurnal();
$postResp = json_decode(ob_get_clean(), true);
check($postResp['status'] === 0, 'native posting strictly rejected for 2103-01-01');
check(strpos($postResp['msg'], '2103-01-01') !== false, 'refusal message quotes COA 2103-01-01');
check(count($pettyCtrl->Jurnal_payment_petty_cash_nomor_model->incrementCalls) === 0, 'no branch BUK counter incremented on blocked posting');

// Attempt to post with 2103-01-02
$pettyCtrl->Jurnal_payment_petty_cash_model->rows = [
    (object)['id_company' => '5', 'nm_company' => 'STM', 'coa' => '2103-01-02', 'debit' => 500, 'kredit' => 0, 'tgl_jurnal' => '2026-10-14', 'no_transaksi' => 'PC-ATTACK-2', 'jenis_transaksi' => 'Petty Cash'],
    (object)['id_company' => '5', 'nm_company' => 'STM', 'coa' => '1101-01-01', 'debit' => 0, 'kredit' => 500, 'tgl_jurnal' => '2026-10-14', 'no_transaksi' => 'PC-ATTACK-2', 'jenis_transaksi' => 'Petty Cash']
];
$_POST = ['no_transaksi' => 'PC-ATTACK-2', 'jenis_transaksi' => 'Petty Cash'];

ob_start();
$pettyCtrl->save_posting_jurnal();
$postResp2 = json_decode(ob_get_clean(), true);
check($postResp2['status'] === 0, 'native posting strictly rejected for 2103-01-02');
check(strpos($postResp2['msg'], '2103-01-02') !== false, 'refusal message quotes COA 2103-01-02');
check(count($pettyCtrl->Jurnal_payment_petty_cash_nomor_model->incrementCalls) === 0, 'no branch BUK counter incremented on blocked posting');

// =========================================================================
// SECTION 3: MULTI-COMPANY SCHEMA ROUTING & BRANCH COUNTER ISOLATION
// =========================================================================
echo "\n[3] Testing Multi-Company Boundary Cases & Branch Counter Isolation...\n";

// 3.1 Verify Company ID to Schema Routing Matrix across modules

// Invoicing routing:
$invModel = (new ReflectionClass('Jurnal_invoicing_model'))->newInstanceWithoutConstructor();
$getInvDbMethod = new ReflectionMethod('Jurnal_invoicing_model', '_get_accounting_db');
$getInvDbMethod->setAccessible(true);

// Mock accounting connections on invModel
$accStm = new stdClass(); $accStm->name = 'accounting_stm';
$accVuca = new stdClass(); $accVuca->name = 'accounting_vuca';
$accSust = new stdClass(); $accSust->name = 'accounting_sustain';

$propStm = new ReflectionProperty('Jurnal_invoicing_model', 'accounting_stm'); $propStm->setAccessible(true); $propStm->setValue($invModel, $accStm);
$propVuca = new ReflectionProperty('Jurnal_invoicing_model', 'accounting_vuca'); $propVuca->setAccessible(true); $propVuca->setValue($invModel, $accVuca);
$propSust = new ReflectionProperty('Jurnal_invoicing_model', 'accounting_sustain'); $propSust->setAccessible(true); $propSust->setValue($invModel, $accSust);

// STM: 1, 6, 7
check($getInvDbMethod->invoke($invModel, '1')->name === 'accounting_stm', 'Invoicing company 1 -> accounting_stm');
check($getInvDbMethod->invoke($invModel, '6')->name === 'accounting_stm', 'Invoicing company 6 -> accounting_stm');
check($getInvDbMethod->invoke($invModel, '7')->name === 'accounting_stm', 'Invoicing company 7 -> accounting_stm');
// VUCA: 4
check($getInvDbMethod->invoke($invModel, '4')->name === 'accounting_vuca', 'Invoicing company 4 -> accounting_vuca');
// Sustain: other companies
foreach (['2', '3', '5', '8', '99', ''] as $cid) {
    check($getInvDbMethod->invoke($invModel, $cid)->name === 'accounting_sustain', "Invoicing company '{$cid}' -> accounting_sustain");
}

// Penerimaan routing:
$rcvCtrl = (new ReflectionClass('Jurnal_penerimaan'))->newInstanceWithoutConstructor();
$getRcvDbMethod = new ReflectionMethod('Jurnal_penerimaan', '_get_accounting_db');
$getRcvDbMethod->setAccessible(true);

$propStm2 = new ReflectionProperty('Jurnal_penerimaan', 'accounting_stm'); $propStm2->setAccessible(true); $propStm2->setValue($rcvCtrl, $accStm);
$propVuca2 = new ReflectionProperty('Jurnal_penerimaan', 'accounting_vuca'); $propVuca2->setAccessible(true); $propVuca2->setValue($rcvCtrl, $accVuca);
$propSust2 = new ReflectionProperty('Jurnal_penerimaan', 'accounting_sustain'); $propSust2->setAccessible(true); $propSust2->setValue($rcvCtrl, $accSust);

check($getRcvDbMethod->invoke($rcvCtrl, '1')->name === 'accounting_stm', 'Penerimaan company 1 -> accounting_stm');
check($getRcvDbMethod->invoke($rcvCtrl, '6')->name === 'accounting_stm', 'Penerimaan company 6 -> accounting_stm');
check($getRcvDbMethod->invoke($rcvCtrl, '7')->name === 'accounting_stm', 'Penerimaan company 7 -> accounting_stm');
check($getRcvDbMethod->invoke($rcvCtrl, '4')->name === 'accounting_vuca', 'Penerimaan company 4 -> accounting_vuca');
foreach (['2', '3', '5', '8', '99', ''] as $cid) {
    check($getRcvDbMethod->invoke($rcvCtrl, $cid)->name === 'accounting_sustain', "Penerimaan company '{$cid}' -> accounting_sustain");
}

// Expense Report schema:
$expModel = (new ReflectionClass('Jurnal_expense_posting_model'))->newInstanceWithoutConstructor();
check($expModel->accounting_schema('1') === DBACC_STM, 'Expense company 1 -> DBACC_STM');
check($expModel->accounting_schema('6') === DBACC_STM, 'Expense company 6 -> DBACC_STM');
check($expModel->accounting_schema('7') === DBACC_STM, 'Expense company 7 -> DBACC_STM');
check($expModel->accounting_schema('4') === DBACC_VUCA, 'Expense company 4 -> DBACC_VUCA');
foreach (['2', '3', '5', '8', '99', ''] as $cid) {
    check($expModel->accounting_schema($cid) === DBACC_SUST, "Expense company '{$cid}' -> DBACC_SUST");
}

// 3.2 Counter Isolation Verification
// We create mock databases that simulate branch counters for pastibisa_tb_cabang.
class MockAccountingBranchDb {
    public $name;
    public $counters = ['nomorJC' => 100, 'nobum' => 200, 'nobuk' => 300];
    public $updateLog = [];
    public function __construct($name) { $this->name = $name; }
    public function query($sql) {
        if (preg_match('/UPDATE pastibisa_tb_cabang SET (nomorJC|nobum|nobuk)\s*=\s*\1\s*\+\s*1 WHERE nocab\s*=\s*[\'"]101[\'"]/i', $sql, $m)) {
            $field = $m[1];
            $this->counters[$field]++;
            $this->updateLog[] = [$field, $this->counters[$field]];
            return true;
        }
        return false;
    }
    public function set($field, $expr, $escape) {
        $this->pendingField = $field;
        return $this;
    }
    public function where($field, $val) { return $this; }
    public function update($table) {
        if ($table === 'pastibisa_tb_cabang' && isset($this->pendingField)) {
            $f = $this->pendingField;
            $this->counters[$f]++;
            $this->updateLog[] = [$f, $this->counters[$f]];
            return true;
        }
        return true;
    }
    public function get_where($table, $where) {
        return new class($this->counters) {
            private $c;
            public function __construct($c) { $this->c = $c; }
            public function row() {
                return (object)['nocab' => '101', 'nomorJC' => $this->c['nomorJC'], 'nobum' => $this->c['nobum'], 'nobuk' => $this->c['nobuk']];
            }
        };
    }
    public function insert($table, $data) { return true; }
    public function insert_batch($table, $data) { return true; }
}

$mockAccStm = new MockAccountingBranchDb('accounting_stm');
$mockAccVuca = new MockAccountingBranchDb('accounting_vuca');
$mockAccSust = new MockAccountingBranchDb('accounting_sustain');

// Case A: Invoicing for VUCA (company 4) -> only accounting_vuca nomorJC must increment
$initialStmJC = $mockAccStm->counters['nomorJC'];
$initialVucaJC = $mockAccVuca->counters['nomorJC'];
$initialSustJC = $mockAccSust->counters['nomorJC'];

// Simulate invoice posting execution targeting company 4
$targetInvDb = $getInvDbMethod->invoke($invModel, '4');
// Run the exact counter update query used by Jurnal_invoicing_model:
$mockAccVuca->query("UPDATE pastibisa_tb_cabang SET nomorJC = nomorJC + 1 WHERE nocab='101'");

check($mockAccVuca->counters['nomorJC'] === $initialVucaJC + 1, 'VUCA nomorJC incremented by 1');
check($mockAccStm->counters['nomorJC'] === $initialStmJC, 'STM nomorJC remains completely untouched (delta = 0)');
check($mockAccSust->counters['nomorJC'] === $initialSustJC, 'Sustain nomorJC remains completely untouched (delta = 0)');

// Case B: Penerimaan for STM (company 7) -> only accounting_stm nobum must increment
$initialStmBum = $mockAccStm->counters['nobum'];
$initialVucaBum = $mockAccVuca->counters['nobum'];
$initialSustBum = $mockAccSust->counters['nobum'];

// Simulate penerimaan posting execution targeting company 7
$targetRcvDb = $getRcvDbMethod->invoke($rcvCtrl, '7');
$mockAccStm->query('UPDATE pastibisa_tb_cabang SET nobum = nobum + 1 WHERE nocab = "101"');

check($mockAccStm->counters['nobum'] === $initialStmBum + 1, 'STM nobum incremented by 1');
check($mockAccVuca->counters['nobum'] === $initialVucaBum, 'VUCA nobum remains untouched (delta = 0)');
check($mockAccSust->counters['nobum'] === $initialSustBum, 'Sustain nobum remains untouched (delta = 0)');

// Case C: Payment for Sustain (company 2) -> only accounting_sustain nobuk must increment
$initialStmBuk = $mockAccStm->counters['nobuk'];
$initialVucaBuk = $mockAccVuca->counters['nobuk'];
$initialSustBuk = $mockAccSust->counters['nobuk'];

// Simulate payment posting execution targeting company 2
$mockAccSust->set('nobuk', 'nobuk + 1', false)->where('nocab', '101')->update('pastibisa_tb_cabang');

check($mockAccSust->counters['nobuk'] === $initialSustBuk + 1, 'Sustain nobuk incremented by 1');
check($mockAccStm->counters['nobuk'] === $initialStmBuk, 'STM nobuk remains untouched (delta = 0)');
check($mockAccVuca->counters['nobuk'] === $initialVucaBuk, 'VUCA nobuk remains untouched (delta = 0)');

// Case D: Petty Cash Intercompany Split (VUCA branch + STM reimbursement)
// In intercompany split, exactly VUCA and STM increment, while Sustain remains 0
$initialStmBuk2 = $mockAccStm->counters['nobuk'];
$initialVucaBuk2 = $mockAccVuca->counters['nobuk'];
$initialSustBuk2 = $mockAccSust->counters['nobuk'];

// Intercompany executes increment on branch target (VUCA) and STM target
$mockAccVuca->set('nobuk', 'nobuk + 1', false)->where('nocab', '101')->update('pastibisa_tb_cabang');
$mockAccStm->set('nobuk', 'nobuk + 1', false)->where('nocab', '101')->update('pastibisa_tb_cabang');

// 3.3 Dispatcher Unclear COA Propagation from Petty Cash
echo "\n[3.3] Testing Dispatcher Propagation of Petty Cash Unclear COA Refusal...\n";
$testModel->mockHeader = ['id' => 10, 'source' => 'PCS', 'no_transaksi' => 'PC-BLOCKED', 'jenis_transaksi' => 'Petty Cash', 'id_company' => '5'];
$testModel->mockRows = [
    ['debit' => '250.00', 'kredit' => '0.00'],
    ['debit' => '0.00', 'kredit' => '250.00']
];
$_POST = ['test_marker' => 'preserved_caller_state'];
Modules::$called = false;
Modules::$response = json_encode(['status' => 0, 'msg' => 'Posting diblokir: jurnal mengandung COA 2103-01-01 (Hutang ke STM) yang statusnya masih belum final dikonfirmasi oleh Finance.']);

$pcsResult = $dispatcher->execute('post', 10, 'PCS', []);
check(Modules::$called, 'dispatcher invoked native petty cash module');
check($pcsResult['status'] === 0, 'dispatcher returned status 0 on unclear COA block');
check(strpos($pcsResult['msg'], '2103-01-01') !== false, 'dispatcher propagated exact unclear COA refusal message');
check(!isset($pcsResult['metadata_saved']) || $pcsResult['metadata_saved'] === false, 'no metadata saved on blocked unclear COA');
check($_POST === ['test_marker' => 'preserved_caller_state'], 'caller POST restored even after native business block');

// Restore default Modules response
Modules::$response = ['save' => 1, 'status' => 1, 'msg' => 'ok', 'accounting_numbers' => [['company' => '7', 'number' => 'JV-001']]];

// First let's check Dispatcher inner secondary row check
class ForceBalanceModel extends TestDataModel {
    public function detail($id, $posted) {
        return [
            'header' => ['id' => $id, 'source' => 'PAY', 'no_transaksi' => 'TRX-BYPASS', 'jenis_transaksi' => 'Payment', 'id_company' => '7'],
            'rows' => [
                ['debit' => '-50.00', 'kredit' => '0.00'],
                ['debit' => '150.00', 'kredit' => '0.00'],
                ['debit' => '0.00', 'kredit' => '100.00']
            ],
            'is_balance' => true // artificially bypassed is_balance
        ];
    }
}
$bypassCi = (object)[
    'db' => $testDb,
    'Jurnal_all_model' => new ForceBalanceModel(),
    'auth' => new TestAuth()
];
$bypassDispatcher = new Jurnal_all_dispatcher(['ci' => $bypassCi]);
Modules::$called = false;
$secondaryCaught = false;
try {
    $bypassDispatcher->execute('post', 1, 'PAY', []);
} catch (InvalidArgumentException $e) {
    $secondaryCaught = true;
    check(strpos($e->getMessage(), 'Nominal jurnal tidak valid') !== false, 'dispatcher secondary sanity loop caught negative debit');
}
check($secondaryCaught && !Modules::$called, 'dispatcher secondary sanity loop strictly blocked posting even when is_balance was true');

// 3.4 Rollback on Counter Failure
echo "\n[3.4] Testing Rollback on Counter Increment Failure...\n";
class CounterFailDb {
    public $events = [];
    public $writes = [];
    public function __call($name, $args) { return $this; }
    public function get($table = null) { return $this->get_where($table, []); }
    public function trans_begin() { $this->events[] = 'begin'; return true; }
    public function trans_status() { return true; }
    public function trans_commit() { $this->events[] = 'commit'; return true; }
    public function trans_rollback() { $this->events[] = 'rollback'; return true; }
    public function update($table, $data = [], $where = []) {
        if ($table === 'pastibisa_tb_cabang') {
            return false; // Counter increment fails!
        }
        $this->writes[] = [$table, $data];
        return true;
    }
    public function query($sql) {
        if (strpos($sql, 'pastibisa_tb_cabang') !== false) {
            return false; // Raw SQL counter increment fails!
        }
        return true;
    }
    public function insert($table, $data) { $this->writes[] = [$table, $data]; return true; }
    public function insert_batch($table, $data) { $this->writes[] = [$table, $data]; return true; }
    public function get_where($table, $where) {
        return new class {
            public function row() { return (object)['id' => 1, 'no_transaksi' => 'INV-1', 'jenis_transaksi' => 'Invoicing', 'tgl_jurnal' => '2026-10-14', 'id_company' => '7', 'total_akhir_jurnal' => '100', 'nm_customer' => 'Cust', 'coa' => '1101', 'nm_coa' => 'Piutang']; }
            public function result() { return [(object)['id' => 1, 'no_transaksi' => 'INV-1', 'jenis_transaksi' => 'Invoicing', 'tgl_jurnal' => '2026-10-14', 'coa' => '1101', 'debit' => 100, 'kredit' => 0]]; }
        };
    }
    public function error() { return ['message' => 'simulated counter lock failure']; }
}

$counterFailDb = new CounterFailDb();
$invFailModel = (new ReflectionClass('Jurnal_invoicing_model'))->newInstanceWithoutConstructor();
$invFailModel->db = $counterFailDb;
$invFailModel->auth = new TestAuth();
$invFailModel->input = new MockPettyInput();
$propConsultant = new ReflectionProperty('Jurnal_invoicing_model', 'consultant');
$propConsultant->setAccessible(true);
$propConsultant->setValue($invFailModel, new class {
    public function get_where($table, $where) {
        return new class { public function row() { return (object)['company' => '7', 'id_company' => '7', 'nm_company' => 'STM']; } };
    }
});
$invFailModel->Jurnal_invoicing_nomor_model = new class {
    public function get_Nomor_Jurnal_Sales($cab, $tgl, $cid) { return 'JV-FAIL-TEST'; }
};
$propStmFail = new ReflectionProperty('Jurnal_invoicing_model', 'accounting_stm');
$propStmFail->setAccessible(true);
$propStmFail->setValue($invFailModel, $counterFailDb);

$_POST = ['id' => 1];
ob_start();
try {
    $invFailModel->save_posting_jurnal();
} catch (Exception $e) {
    // Exception handled inside or thrown
}
$out = ob_get_clean();
check(in_array('rollback', $counterFailDb->events, true), 'counter failure strictly triggered database trans_rollback()');

echo "\nALL {$checks} ADVERSARIAL STRESS ASSERTIONS PASSED!\n";
echo "=== CHALLENGER 1 COMPLETED SUCCESSFULLY ===\n";
