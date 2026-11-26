<?php
define('BASEPATH', true);

function has_permission($permission) {
    global $permissions;
    return in_array($permission, $permissions, true);
}
function log_message($level, $message) {
    global $logged_messages;
    $logged_messages[] = [$level, $message];
}

class BF_Model {}
require dirname(__DIR__) . '/models/Jurnal_all_model.php';
require dirname(__DIR__) . '/libraries/Jurnal_all_dispatcher.php';

$checks = 0;
function check($ok, $msg) {
    global $checks;
    $checks++;
    if (!$ok) throw new RuntimeException("Assertion failed [{$checks}]: " . $msg);
}

class ChallengeResult {
    public $acquired = 1;
    public function row() { return $this; }
}

class ChallengeDb {
    public $locked = true;
    public $events = [];
    public $releaseSuccess = true;
    public $activeLocks = [];

    public function query($sql, $params = []) {
        $this->events[] = ['sql' => $sql, 'params' => $params];
        if (strpos($sql, 'GET_LOCK') !== false) {
            $key = $params[0] ?? '';
            $r = new ChallengeResult();
            if (!$this->locked || in_array($key, $this->activeLocks, true)) {
                $r->acquired = 0;
            } else {
                $r->acquired = 1;
                $this->activeLocks[] = $key;
            }
            return $r;
        }
        if (strpos($sql, 'RELEASE_LOCK') !== false) {
            $key = $params[0] ?? '';
            $this->activeLocks = array_values(array_diff($this->activeLocks, [$key]));
            if (!$this->releaseSuccess) return false;
            $r = new ChallengeResult();
            $r->acquired = 1;
            return $r;
        }
        $r = new ChallengeResult();
        return $r;
    }
}

class ChallengeData extends Jurnal_all_model {
    public $metadataReady = true;
    public $metadataFail = false;
    public $savedMetadata = [];
    public $posted = false;
    public $balance = true;
    public $type = 'Payment';
    public $company = '7';
    public $transaksi = 'TRX-001';
    public $negativeAmounts = false;
    public $centsDebit = 10000;
    public $centsKredit = 10000;
    public $keyMutatedOnFresh = false;

    public function posting_metadata_ready() {
        return $this->metadataReady;
    }

    public function save_posting_metadata($header, $actor, $numbers) {
        if ($this->metadataFail || empty($numbers)) {
            throw new RuntimeException('metadata storage failed');
        }
        $this->savedMetadata[] = ['header' => $header, 'actor' => $actor, 'numbers' => $numbers];
    }

    public $detailCalls = 0;

    public function detail($id, $postedCheck) {
        $this->detailCalls++;
        if ($this->posted) {
            throw new InvalidArgumentException('Status kelompok jurnal berubah atau sudah diposting.');
        }
        $isMutated = ($this->keyMutatedOnFresh && $this->detailCalls > 1);
        $header = [
            'id' => (int)$id,
            'source' => $this->source($this->type),
            'no_transaksi' => $isMutated ? 'MUTATED-TRX' : $this->transaksi,
            'jenis_transaksi' => $this->type,
            'id_company' => $this->company
        ];
        $rows = [
            [
                'id' => 1,
                'debit' => $this->negativeAmounts ? '-100.00' : ($this->centsDebit / 100),
                'kredit' => '0.00',
                'coa' => '5101-01-01',
                'sts' => '0'
            ],
            [
                'id' => 2,
                'debit' => '0.00',
                'kredit' => $this->negativeAmounts ? '-100.00' : ($this->centsKredit / 100),
                'coa' => '1101-01-01',
                'sts' => '0'
            ]
        ];
        return [
            'header' => $header,
            'rows' => $rows,
            'is_balance' => $this->balance && ($this->centsDebit === $this->centsKredit)
        ];
    }
}

class ChallengeModules {
    public static $calls = [];
    public static $response = ['save' => 1, 'msg' => 'posted', 'accounting_numbers' => [['company' => '7', 'number' => 'FINAL-JV-101']]];
    public static $failWith = null; // Exception / Error
    public static $leakBuffer = false;

    public static function run($route) {
        if (self::$leakBuffer) {
            ob_start();
            echo "LEAKED_BUFFER_DATA_FROM_NATIVE";
        }
        self::$calls[] = ['route' => $route, 'post' => $_POST];
        if (self::$failWith instanceof Throwable) {
            throw self::$failWith;
        }
        return self::$response;
    }
}
class_alias('ChallengeModules', 'Modules');

$permissions = ['Jurnal_All.Manage'];
$logged_messages = [];
$db = new ChallengeDb();
$model = new ChallengeData();
$auth = new class {
    public function user_id() { return 42; }
    public function user_name() { return 'security_challenger'; }
};
$ci = (object)['db' => $db, 'Jurnal_all_model' => $model, 'auth' => $auth];
$dispatcher = new Jurnal_all_dispatcher(['ci' => $ci]);

echo "=== SECTION 1: ADVISORY LOCK CONCURRENCY & LIFECYCLE ===\n";

// 1.1: Lock timeout / concurrent duplicate post rejection
$db->locked = false; // Simulates another worker holding the lock
try {
    $dispatcher->execute('post', 1, 'PAY', []);
    check(false, "Should have failed due to advisory lock timeout");
} catch (RuntimeException $e) {
    check(strpos($e->getMessage(), 'Transaksi sedang diproses') !== false, "Correct error on lock timeout: " . $e->getMessage());
    check(count(ChallengeModules::$calls) === 0, "Native module was NEVER called when lock timed out");
    check(count($db->activeLocks) === 0, "No active lock remained held by caller");
}
$db->locked = true;

// 1.2: Mutual Exclusion: Simulating two concurrent requests
$db->activeLocks = [];
$key1 = 'jurnal_all:' . substr(hash('sha256', $model->key([
    'no_transaksi' => 'TRX-001',
    'jenis_transaksi' => 'Payment',
    'id_company' => '7'
])), 0, 48);

// Manually acquire lock (simulating Thread 1 in progress)
$db->activeLocks[] = $key1;
try {
    // Thread 2 attempts to post same key
    $dispatcher->execute('post', 1, 'PAY', []);
    check(false, "Thread 2 should be rejected while Thread 1 holds lock");
} catch (RuntimeException $e) {
    check(strpos($e->getMessage(), 'Transaksi sedang diproses') !== false, "Thread 2 blocked by lock");
    check(count(ChallengeModules::$calls) === 0, "Thread 2 never touched native modules");
}
// Release Thread 1's lock
$db->activeLocks = [];

// 1.3: Race condition test: Thread 2 acquires lock AFTER Thread 1 posted
$model->posted = true; // Thread 1 marked rows posted
try {
    $dispatcher->execute('post', 1, 'PAY', []);
    check(false, "Thread 2 should detect already posted rows on fresh detail read");
} catch (InvalidArgumentException $e) {
    check(strpos($e->getMessage(), 'sudah diposting') !== false, "Thread 2 aborted cleanly on already posted data: " . $e->getMessage());
    check(count(ChallengeModules::$calls) === 0, "Thread 2 never executed duplicate post");
    check(count($db->activeLocks) === 0, "Advisory lock was properly released after fresh detail abort");
}
$model->posted = false;

// 1.4: Group mutation race condition (header key changed between pre-check and lock acquisition)
$model->detailCalls = 0;
$model->keyMutatedOnFresh = true;
try {
    $dispatcher->execute('post', 1, 'PAY', []);
    check(false, "Should reject when group key changes concurrently");
} catch (InvalidArgumentException $e) {
    check(strpos($e->getMessage(), 'Kelompok jurnal berubah') !== false, "Group change detected: " . $e->getMessage());
    check(count($db->activeLocks) === 0, "Lock released after group mutation abort");
}
$model->keyMutatedOnFresh = false;

// 1.5: Lock key length and isolation checks
$longTrx = str_repeat('LONG_NUMBER_TEST_', 20); // 340 chars
$headerLong = ['no_transaksi' => $longTrx, 'jenis_transaksi' => 'Payment', 'id_company' => '1'];
$computedKey = 'jurnal_all:' . substr(hash('sha256', $model->key($headerLong)), 0, 48);
check(strlen($computedKey) === 59, "Lock key length is exactly 59 chars (<= 64 MySQL limit)");
check(preg_match('/^jurnal_all:[a-f0-9]{48}$/', $computedKey) === 1, "Lock key format valid sha256 hex slice");

// 1.6: Expense Report company-level lock isolation vs non-EXP
$expCompany1 = ['no_transaksi' => 'ER-100', 'jenis_transaksi' => 'Expense Report', 'id_company' => '1'];
$expCompany2 = ['no_transaksi' => 'ER-100', 'jenis_transaksi' => 'Expense Report', 'id_company' => '4'];
check($model->key($expCompany1) !== $model->key($expCompany2), "Expense report keys are isolated by company");

$payCompany1 = ['no_transaksi' => 'PAY-100', 'jenis_transaksi' => 'Payment', 'id_company' => '1'];
$payCompany2 = ['no_transaksi' => 'PAY-100', 'jenis_transaksi' => 'Payment', 'id_company' => '4'];
check($model->key($payCompany1) === $model->key($payCompany2), "Payment keys lock across whole transaction ID");

// 1.7: RELEASE_LOCK guaranteed across all failure points in `try`
$failureScenarios = [
    'unbalanced' => function() use ($model) { $model->balance = false; },
    'negative_amounts' => function() use ($model) { $model->negativeAmounts = true; },
    'runtime_exception_in_handler' => function() { ChallengeModules::$failWith = new RuntimeException('Handler database died'); },
    'type_error_in_handler' => function() { ChallengeModules::$failWith = new TypeError('Handler fatal type mismatch'); },
    'invalid_json_response' => function() { ChallengeModules::$response = "{malformed_json"; },
    'missing_status_response' => function() { ChallengeModules::$response = ['foo' => 'bar']; },
    'handler_failure_response' => function() { ChallengeModules::$response = ['status' => 0, 'msg' => 'Posting rejected by accounting']; },
];

foreach ($failureScenarios as $name => $setup) {
    // Reset state
    $model->balance = true;
    $model->negativeAmounts = false;
    $model->keyMutatedOnFresh = false;
    ChallengeModules::$failWith = null;
    ChallengeModules::$response = ['save' => 1, 'msg' => 'ok', 'accounting_numbers' => [['company' => '7', 'number' => 'JV-001']]];
    $db->events = [];
    $db->activeLocks = [];
    $_POST = ['original_token' => 'SECRET_CALLER_TOKEN_123'];

    $setup();

    try {
        $res = $dispatcher->execute('post', 1, 'PAY', []);
        check($res['status'] === 0, "Returned status 0 for scenario: {$name}");
    } catch (Throwable $t) {
        // Expected exception
    }

    $releaseFound = false;
    foreach ($db->events as $ev) {
        if (strpos($ev['sql'], 'RELEASE_LOCK') !== false) {
            $releaseFound = true;
            break;
        }
    }
    check($releaseFound, "RELEASE_LOCK was executed in finally for failure scenario: {$name}");
    check($_POST === ['original_token' => 'SECRET_CALLER_TOKEN_123'], "\$_POST restored after failure: {$name}");
    check(count($db->activeLocks) === 0, "No active lock remained held after scenario: {$name}");
}

echo "=== SECTION 2: CLIENT PARAMETER TAMPERING PROTECTION ===\n";

// 2.1: Client injection of routing, accounting target, amounts, and metadata
$maliciousPayload = [
    'id' => 99999,
    'source' => 'INV',
    'id_company' => '1',
    'debit' => 999999999.00,
    'kredit' => 0.00,
    'coa' => '1101-01-01',
    'routing' => 'malicious_controller/evil_method',
    'acc_db' => 'malicious_database',
    'no_transaksi' => 'ATTACK_OVERRIDE_001',
    'jenis_transaksi' => 'Invoicing',
    'posted_by' => 666,
    'posted_by_name' => 'attacker_hacker',
    'accounting_numbers' => [['company' => '99', 'number' => 'FORGED-JV-666']]
];

$model->balance = true;
$model->negativeAmounts = false;
$model->type = 'Payment';
$model->transaksi = 'SERVER_AUTHORITATIVE_TRX';
$model->company = '7';
ChallengeModules::$calls = [];
ChallengeModules::$failWith = null;
ChallengeModules::$response = [
    'save' => 1,
    'msg' => 'Posting ok',
    'accounting_numbers' => [['company' => '7', 'number' => 'SERVER-JV-007']]
];
$_POST = ['attacker_post_global' => 'should_be_wiped_during_handler_and_restored_after'];

$result = $dispatcher->execute('post', 1, 'PAY', $maliciousPayload);

check($result['status'] === 1, "Posting executed successfully");
check(count(ChallengeModules::$calls) === 1, "Native module was called exactly once");

$receivedCall = ChallengeModules::$calls[0];
check($receivedCall['route'] === 'jurnal_payment/save_posting_jurnal', "Route determined solely by server: " . $receivedCall['route']);

// Verify sanitized $_POST seen by legacy handler
$forwardedPost = $receivedCall['post'];
check($forwardedPost['id'] === 1, "id is strictly cast to integer matching target id (1)");
check($forwardedPost['no_transaksi'] === 'SERVER_AUTHORITATIVE_TRX', "no_transaksi came from server DB, NOT attacker payload: " . $forwardedPost['no_transaksi']);
check($forwardedPost['jenis_transaksi'] === 'Payment', "jenis_transaksi came from server DB, NOT attacker payload");
check(!isset($forwardedPost['debit']), "debit field was completely discarded");
check(!isset($forwardedPost['kredit']), "kredit field was completely discarded");
check(!isset($forwardedPost['id_company']), "id_company field was completely discarded");
check(!isset($forwardedPost['acc_db']), "acc_db field was completely discarded");
check(!isset($forwardedPost['routing']), "routing field was completely discarded");
check(!isset($forwardedPost['posted_by']), "posted_by field was completely discarded");
check(!isset($forwardedPost['accounting_numbers']), "accounting_numbers field was completely discarded");

// Verify metadata recorded on server
check(count($model->savedMetadata) === 1, "Metadata was recorded");
$recorded = $model->savedMetadata[0];
check($recorded['actor']['id'] === 42, "Actor ID came strictly from CI Auth (42), attacker value (666) discarded");
check($recorded['actor']['name'] === 'security_challenger', "Actor name came strictly from CI Auth, attacker value discarded");
check($recorded['numbers'][0]['number'] === 'SERVER-JV-007', "Final accounting number came strictly from handler response, attacker value discarded");
check($recorded['numbers'][0]['company'] === '7', "Accounting company came strictly from handler response, attacker value discarded");

// Verify caller's $_POST restored
check($_POST === ['attacker_post_global' => 'should_be_wiped_during_handler_and_restored_after'], "\$_POST restored to original after tampering attempt");

// 2.2: Expected source spoofing attempt
try {
    $model->type = 'Payment'; // Source is PAY
    $dispatcher->execute('post', 1, 'EXP', $maliciousPayload); // Attacker tries to pretend expectedSource is EXP
    check(false, "Should have thrown InvalidArgumentException on source spoofing");
} catch (InvalidArgumentException $e) {
    check(strpos($e->getMessage(), 'Aksi atau sumber jurnal tidak valid') !== false, "Rejected source spoofing: " . $e->getMessage());
}

// 2.3: Revise action tampering on non-revisable source
try {
    $model->type = 'Payment'; // Source PAY does not allow revise
    $dispatcher->execute('revise', 1, 'PAY', ['alasan_revisi' => 'Trying to revise payment']);
    check(false, "Should reject revise on PAY source");
} catch (InvalidArgumentException $e) {
    check(strpos($e->getMessage(), 'Aksi atau sumber jurnal tidak valid') !== false, "PAY revise blocked");
}

// 2.4: Revise malicious reason payload (> 2000 chars, non-scalar)
$model->type = 'Invoicing'; // INV allows revise
try {
    $dispatcher->execute('revise', 1, 'INV', ['alasan_revisi' => str_repeat('A', 2001)]);
    check(false, "Should reject revision reason > 2000 chars");
} catch (InvalidArgumentException $e) {
    check(strpos($e->getMessage(), 'maksimal 2000 karakter') !== false, "Long reason blocked: " . $e->getMessage());
}

try {
    $dispatcher->execute('revise', 1, 'INV', ['alasan_revisi' => ['array_injection']]);
    check(false, "Should reject non-scalar revision reason");
} catch (InvalidArgumentException $e) {
    check(strpos($e->getMessage(), 'Alasan revisi wajib diisi') !== false, "Non-scalar reason blocked");
}

echo "=== SECTION 3: TRANSACTION ROLLBACK & REQUEST ISOLATION ===\n";

// 3.1: Output buffer cleanup on native handler buffer leaks
$initialBufferLevel = ob_get_level();
ChallengeModules::$leakBuffer = true;
ChallengeModules::$failWith = new RuntimeException("Handler exploded after leaking buffer");

try {
    $dispatcher->execute('post', 1, 'INV', []);
    check(false, "Should throw on handler exception");
} catch (RuntimeException $e) {
    check(ob_get_level() === $initialBufferLevel, "Output buffer was completely cleaned up back to level {$initialBufferLevel}");
}
ChallengeModules::$leakBuffer = false;
ChallengeModules::$failWith = null;

// 3.2: Native handler commit failure prevents metadata write
$model->savedMetadata = [];
ChallengeModules::$response = ['save' => 0, 'msg' => 'Database commit rollback in native handler'];
$res = $dispatcher->execute('post', 1, 'INV', []);
check($res['status'] === 0, "Dispatcher returns status 0 on native commit failure");
check(count($model->savedMetadata) === 0, "No metadata was recorded when native handler returned save=0");

// 3.3: Metadata storage failure handling (preserves business outcome, warns without retry)
$model->metadataFail = true;
ChallengeModules::$response = [
    'save' => 1,
    'msg' => 'Posting successful',
    'accounting_numbers' => [['company' => '1', 'number' => 'JV-COMMITTED-999']]
];
$res = $dispatcher->execute('post', 1, 'INV', []);
check($res['status'] === 1, "Status remains 1 because accounting write already committed");
check($res['metadata_saved'] === false, "metadata_saved flag is false");
check($res['outcome'] === 'posted_metadata_missing', "Outcome flags posted_metadata_missing");
check(isset($res['warning']) && strpos($res['warning'], 'Jangan posting ulang') !== false, "Clear warning given not to repost");
$model->metadataFail = false;

// 3.4: Pre-delegation negative cents rejection prevents dirty transaction start
$model->type = 'Payment';
$model->negativeAmounts = true;
ChallengeModules::$calls = [];

try {
    $dispatcher->execute('post', 1, 'PAY', []);
    check(false, "Negative amounts should be rejected pre-delegation");
} catch (InvalidArgumentException $e) {
    check(strpos($e->getMessage(), 'Nominal jurnal tidak valid') !== false, "Negative amount rejected: " . $e->getMessage());
    check(count(ChallengeModules::$calls) === 0, "Native module was NEVER called for negative amounts");
    check(count($model->savedMetadata) === 0, "No metadata written for negative amounts");
}
$model->negativeAmounts = false;

// 3.5: Pre-delegation unbalanced journal rejection prevents dirty transaction start
$model->balance = false;
ChallengeModules::$calls = [];

try {
    $dispatcher->execute('post', 1, 'PAY', []);
    check(false, "Unbalanced journal should be rejected pre-delegation");
} catch (InvalidArgumentException $e) {
    check(strpos($e->getMessage(), 'Jurnal tidak balance') !== false, "Unbalanced journal rejected: " . $e->getMessage());
    check(count(ChallengeModules::$calls) === 0, "Native module was NEVER called for unbalanced journal");
    check(count($model->savedMetadata) === 0, "No metadata written for unbalanced journal");
}
$model->balance = true;

echo "\nSUCCESS: All {$checks} adversarial challenge assertions PASSED!\n";
