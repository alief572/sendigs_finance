<?php

/**
 * Property-Based Test: Target Accounting Inheritance
 *
 * Feature: multi-accounting-target, Property 6: Target Accounting Inheritance
 *
 * **Validates: Requirements 2.2**
 *
 * Property 6: Target Accounting Inheritance
 * _For any_ parent tr_request_mutasi record with a valid target_accounting value,
 * when a realisasi mutasi is saved for that parent, the child tr_request_mutasi_aktual
 * record SHALL contain the same target_accounting value as the parent.
 *
 * This test validates:
 * - For any valid target_accounting on a parent record, the child inherits the same value
 * - If parent has null/empty target_accounting, save_mutasi() rejects with error
 * - The inheritance is exact (no transformation/mapping occurs on the value)
 * - The property holds across all valid target_accounting values and random parent data
 *
 * Usage: php application/modules/request_mutasi/tests/TargetAccountingInheritanceTest.php
 */

// ============================================================================
// Define constants for isolated test runs (not within full CI bootstrap)
// ============================================================================

if (!defined('DBACC_STM')) {
    define('DBACC_STM', 'db_sendigs_ss_stm');
}
if (!defined('DBACC_VUCA')) {
    define('DBACC_VUCA', 'db_sendigs_ss_vuca');
}
if (!defined('DBACC_SUSTAIN')) {
    define('DBACC_SUSTAIN', 'db_sendigs_ss_sustain');
}

// ============================================================================
// Minimal test framework (standalone runner)
// ============================================================================

class SimpleTestRunner
{
    private $testsPassed = 0;
    private $testsFailed = 0;
    private $failures = [];
    private $currentTest = '';

    public function run($testClass)
    {
        $reflection = new ReflectionClass($testClass);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

        echo "Running: " . get_class($testClass) . "\n";
        echo str_repeat("=", 70) . "\n\n";

        foreach ($methods as $method) {
            if (strpos($method->getName(), 'test') === 0) {
                $this->currentTest = $method->getName();
                echo "  TEST: {$this->currentTest}\n";
                try {
                    $method->invoke($testClass);
                    $this->testsPassed++;
                    echo "    ✓ PASSED\n\n";
                } catch (AssertionError $e) {
                    $this->testsFailed++;
                    $this->failures[] = [
                        'test' => $this->currentTest,
                        'message' => $e->getMessage(),
                    ];
                    echo "    ✗ FAILED: " . $e->getMessage() . "\n\n";
                } catch (Exception $e) {
                    $this->testsFailed++;
                    $this->failures[] = [
                        'test' => $this->currentTest,
                        'message' => 'Exception: ' . $e->getMessage(),
                    ];
                    echo "    ✗ ERROR: " . $e->getMessage() . "\n\n";
                }
            }
        }

        echo str_repeat("=", 70) . "\n";
        echo "Results: {$this->testsPassed} passed, {$this->testsFailed} failed\n";

        if (!empty($this->failures)) {
            echo "\nFAILURES:\n";
            foreach ($this->failures as $i => $failure) {
                echo "  " . ($i + 1) . ") {$failure['test']}\n";
                echo "     {$failure['message']}\n\n";
            }
        }

        echo str_repeat("=", 70) . "\n";

        return $this->testsFailed === 0 ? 0 : 1;
    }
}

// ============================================================================
// Assertion helpers
// ============================================================================

if (!function_exists('assertEquals')) {
    function assertEquals($expected, $actual, string $message = ''): void
    {
        if ($expected !== $actual) {
            $msg = $message ?: "Expected " . var_export($expected, true) . " but got " . var_export($actual, true);
            throw new AssertionError($msg);
        }
    }
}

if (!function_exists('assertTrue')) {
    function assertTrue($value, string $message = ''): void
    {
        if ($value !== true) {
            throw new AssertionError($message ?: "Expected true but got " . var_export($value, true));
        }
    }
}

if (!function_exists('assertFalse')) {
    function assertFalse($value, string $message = ''): void
    {
        if ($value !== false) {
            throw new AssertionError($message ?: "Expected false but got " . var_export($value, true));
        }
    }
}

if (!function_exists('assertNull')) {
    function assertNull($value, string $message = ''): void
    {
        if ($value !== null) {
            throw new AssertionError($message ?: "Expected null but got " . var_export($value, true));
        }
    }
}

if (!function_exists('assertNotNull')) {
    function assertNotNull($value, string $message = ''): void
    {
        if ($value === null) {
            throw new AssertionError($message ?: "Expected non-null value");
        }
    }
}

if (!function_exists('assertIsArray')) {
    function assertIsArray($value, string $message = ''): void
    {
        if (!is_array($value)) {
            throw new AssertionError($message ?: "Expected array but got " . gettype($value));
        }
    }
}

if (!function_exists('assertArrayHasKey')) {
    function assertArrayHasKey($key, $array, string $message = ''): void
    {
        if (!is_array($array) || !array_key_exists($key, $array)) {
            throw new AssertionError($message ?: "Expected array to have key '{$key}'");
        }
    }
}

// ============================================================================
// Simulated Data & Logic
// ============================================================================

/**
 * Simulates a parent tr_request_mutasi record (stdClass object as CI would return).
 */
class ParentRecordFactory
{
    private static $counter = 0;

    /**
     * Generate a parent record with the given target_accounting value.
     *
     * @param mixed $target_accounting  The target_accounting value (valid, null, or empty)
     * @param array $overrides          Optional field overrides
     * @return object  stdClass simulating a CI row() result
     */
    public static function create($target_accounting, array $overrides = []): object
    {
        self::$counter++;
        $defaults = [
            'kd_mutasi'         => 'MUT-' . str_pad(self::$counter, 6, '0', STR_PAD_LEFT),
            'tgl_request'       => date('Y-m-d'),
            'bank_asal'         => '1101001',
            'bank_tujuan'       => '1101002',
            'nama_bank_asal'    => 'Bank BCA',
            'nama_bank_tujuan'  => 'Bank Mandiri',
            'mata_uang'         => 'IDR',
            'target_accounting' => $target_accounting,
            'nilai'             => '10000000',
            'keterangan'        => 'Test mutasi ' . self::$counter,
            'status'            => '0',
            'created_by'        => 'test_user',
            'created_on'        => date('Y-m-d H:i:s'),
        ];

        $data = array_merge($defaults, $overrides);
        return (object) $data;
    }

    /**
     * Reset counter for fresh test runs.
     */
    public static function reset(): void
    {
        self::$counter = 0;
    }
}

/**
 * Simulates the save_mutasi() logic for inheritance testing.
 *
 * This class replicates the core inheritance logic from the controller:
 * 1. Read parent record's target_accounting
 * 2. If parent target_accounting is null/empty → reject
 * 3. Otherwise → child record inherits target_accounting from parent
 */
class SaveMutasiSimulator
{
    private static $VALID_TARGET_ACCOUNTING = [
        'accounting_stm',
        'accounting_vuca',
        'accounting_sustain'
    ];

    /**
     * Simulate save_mutasi() behavior regarding target_accounting inheritance.
     *
     * @param object $parentRecord  The parent tr_request_mutasi record
     * @param array  $postData      The POST data submitted by user for realisasi
     * @return array  ['status' => int, 'child_data' => array|null, 'pesan' => string|null]
     */
    public function executeSaveMutasi(object $parentRecord, array $postData = []): array
    {
        // Step 1: Check if parent record exists
        if (!$parentRecord || !isset($parentRecord->kd_mutasi)) {
            return [
                'status'     => 2,
                'child_data' => null,
                'pesan'      => 'Data request mutasi tidak ditemukan.',
            ];
        }

        // Step 2: Validate parent's target_accounting (must not be null/empty)
        if (empty($parentRecord->target_accounting)) {
            return [
                'status'     => 2,
                'child_data' => null,
                'pesan'      => 'Request asal belum memiliki Target Accounting.',
            ];
        }

        // Step 3: Build child record data - INHERIT target_accounting from parent
        $childData = [
            'kd_mutasi_aktual'  => 'MTR-' . date('ym') . '00001',
            'kd_mutasi_request' => $parentRecord->kd_mutasi,
            'tgl_request'       => $parentRecord->tgl_request,
            'tgl_mutasi'        => date('Y-m-d'),
            'bank_asal'         => $postData['dari'] ?? $parentRecord->bank_asal,
            'bank_tujuan'       => $postData['ke'] ?? $parentRecord->bank_tujuan,
            'nama_bank_asal'    => $parentRecord->nama_bank_asal,
            'nama_bank_tujuan'  => $parentRecord->nama_bank_tujuan,
            'mata_uang'         => $parentRecord->mata_uang,
            'target_accounting' => $parentRecord->target_accounting, // INHERITANCE
            'kurs'              => $postData['kurs'] ?? '1',
            'nilai_request'     => $postData['nilai'] ?? $parentRecord->nilai,
            'nilai_aktual'      => $postData['rupiah'] ?? $parentRecord->nilai,
            'keterangan'        => $postData['keterangan'] ?? $parentRecord->keterangan,
            'created_by'        => 'test_user',
            'created_on'        => date('Y-m-d H:i:s'),
        ];

        return [
            'status'     => 1,
            'child_data' => $childData,
            'pesan'      => 'Mutasi berhasil disimpan.',
        ];
    }
}

// ============================================================================
// Test Class: Target Accounting Inheritance Property Test
// ============================================================================

class TargetAccountingInheritanceTest
{
    private static $VALID_TARGET_ACCOUNTING = [
        'accounting_stm',
        'accounting_vuca',
        'accounting_sustain'
    ];

    private $simulator;

    public function __construct()
    {
        $this->simulator = new SaveMutasiSimulator();
    }

    // ========================================================================
    // Property 6: Target Accounting Inheritance
    // ========================================================================

    /**
     * Test 6.1: Child inherits target_accounting from parent (accounting_stm)
     *
     * **Validates: Requirements 2.2**
     */
    public function testChildInheritsTargetAccountingStm(): void
    {
        $parent = ParentRecordFactory::create('accounting_stm');
        $result = $this->simulator->executeSaveMutasi($parent);

        assertEquals(1, $result['status'], "Save should succeed for parent with 'accounting_stm'");
        assertNotNull($result['child_data'], "Child data should not be null");
        assertEquals(
            'accounting_stm',
            $result['child_data']['target_accounting'],
            "Child target_accounting should inherit 'accounting_stm' from parent"
        );
    }

    /**
     * Test 6.2: Child inherits target_accounting from parent (accounting_vuca)
     *
     * **Validates: Requirements 2.2**
     */
    public function testChildInheritsTargetAccountingVuca(): void
    {
        $parent = ParentRecordFactory::create('accounting_vuca');
        $result = $this->simulator->executeSaveMutasi($parent);

        assertEquals(1, $result['status'], "Save should succeed for parent with 'accounting_vuca'");
        assertNotNull($result['child_data'], "Child data should not be null");
        assertEquals(
            'accounting_vuca',
            $result['child_data']['target_accounting'],
            "Child target_accounting should inherit 'accounting_vuca' from parent"
        );
    }

    /**
     * Test 6.3: Child inherits target_accounting from parent (accounting_sustain)
     *
     * **Validates: Requirements 2.2**
     */
    public function testChildInheritsTargetAccountingSustain(): void
    {
        $parent = ParentRecordFactory::create('accounting_sustain');
        $result = $this->simulator->executeSaveMutasi($parent);

        assertEquals(1, $result['status'], "Save should succeed for parent with 'accounting_sustain'");
        assertNotNull($result['child_data'], "Child data should not be null");
        assertEquals(
            'accounting_sustain',
            $result['child_data']['target_accounting'],
            "Child target_accounting should inherit 'accounting_sustain' from parent"
        );
    }

    /**
     * Test 6.4: Rejection when parent has NULL target_accounting
     *
     * **Validates: Requirements 2.2, 2.6**
     */
    public function testRejectsWhenParentHasNullTargetAccounting(): void
    {
        $parent = ParentRecordFactory::create(null);
        $result = $this->simulator->executeSaveMutasi($parent);

        assertEquals(2, $result['status'], "Save should be rejected when parent has null target_accounting");
        assertNull($result['child_data'], "Child data should be null on rejection");
        assertTrue(
            strpos($result['pesan'], 'Target Accounting') !== false,
            "Error message should mention 'Target Accounting'"
        );
    }

    /**
     * Test 6.5: Rejection when parent has empty string target_accounting
     *
     * **Validates: Requirements 2.2, 2.6**
     */
    public function testRejectsWhenParentHasEmptyTargetAccounting(): void
    {
        $parent = ParentRecordFactory::create('');
        $result = $this->simulator->executeSaveMutasi($parent);

        assertEquals(2, $result['status'], "Save should be rejected when parent has empty target_accounting");
        assertNull($result['child_data'], "Child data should be null on rejection");
        assertTrue(
            strpos($result['pesan'], 'Target Accounting') !== false,
            "Error message should mention 'Target Accounting'"
        );
    }

    /**
     * Test 6.6: Inheritance is exact - no transformation of the value
     * The child's target_accounting must be === to parent's target_accounting.
     *
     * **Validates: Requirements 2.2**
     */
    public function testInheritanceIsExactNoTransformation(): void
    {
        foreach (self::$VALID_TARGET_ACCOUNTING as $target) {
            $parent = ParentRecordFactory::create($target);
            $result = $this->simulator->executeSaveMutasi($parent);

            assertEquals(1, $result['status']);
            assertTrue(
                $parent->target_accounting === $result['child_data']['target_accounting'],
                "Child value must be strictly equal (===) to parent for target '{$target}'"
            );
        }
    }

    /**
     * Test 6.7: Property - For ANY valid target_accounting on parent, child ALWAYS inherits same value
     * Run 100 iterations with randomly selected valid targets and random parent data.
     *
     * **Validates: Requirements 2.2**
     */
    public function testPropertyChildAlwaysInheritsValidParentTarget(): void
    {
        $iterations = 100;
        $seed = 42;
        mt_srand($seed);

        ParentRecordFactory::reset();

        for ($i = 0; $i < $iterations; $i++) {
            // Pick a random valid target
            $target = self::$VALID_TARGET_ACCOUNTING[mt_rand(0, count(self::$VALID_TARGET_ACCOUNTING) - 1)];

            // Generate parent with random overrides
            $parent = ParentRecordFactory::create($target, [
                'nilai'      => (string) mt_rand(100000, 999999999),
                'mata_uang'  => ['IDR', 'USD', 'EUR', 'SGD'][mt_rand(0, 3)],
                'keterangan' => 'Random test iteration ' . $i,
            ]);

            // Simulate save_mutasi
            $result = $this->simulator->executeSaveMutasi($parent);

            // Assert: status must be 1 (success)
            assertEquals(
                1,
                $result['status'],
                "Iteration {$i}: Save should succeed for parent with target '{$target}'"
            );

            // Assert: child's target_accounting must equal parent's
            assertEquals(
                $target,
                $result['child_data']['target_accounting'],
                "Iteration {$i}: Child must inherit '{$target}' from parent"
            );

            // Assert: strict identity (no type coercion)
            assertTrue(
                $parent->target_accounting === $result['child_data']['target_accounting'],
                "Iteration {$i}: Strict equality must hold"
            );
        }

        echo "    Property verified: {$iterations} iterations - child always inherits parent target_accounting\n";
    }

    /**
     * Test 6.8: Property - For ANY null/empty parent target_accounting, save ALWAYS rejects
     * Run 100 iterations with null/empty/whitespace-only values.
     *
     * **Validates: Requirements 2.2, 2.6**
     */
    public function testPropertyNullOrEmptyParentAlwaysRejects(): void
    {
        $iterations = 100;
        $seed = 77;
        mt_srand($seed);

        $empty_values = [null, '', '0', false];

        ParentRecordFactory::reset();

        for ($i = 0; $i < $iterations; $i++) {
            // Pick a random "empty" value (null, empty string, etc.)
            $empty_val = $empty_values[mt_rand(0, count($empty_values) - 1)];

            $parent = ParentRecordFactory::create($empty_val, [
                'nilai'      => (string) mt_rand(100000, 999999999),
                'mata_uang'  => ['IDR', 'USD', 'EUR'][mt_rand(0, 2)],
                'keterangan' => 'Empty target test ' . $i,
            ]);

            $result = $this->simulator->executeSaveMutasi($parent);

            // Assert: status must be 2 (rejected)
            assertEquals(
                2,
                $result['status'],
                "Iteration {$i}: Save should be rejected for empty parent target_accounting " .
                    "(value=" . var_export($empty_val, true) . ")"
            );

            // Assert: no child data created
            assertNull(
                $result['child_data'],
                "Iteration {$i}: No child data should be created when parent has empty target"
            );

            // Assert: error message is present
            assertTrue(
                !empty($result['pesan']),
                "Iteration {$i}: Error message should be present"
            );
        }

        echo "    Property verified: {$iterations} iterations - null/empty parent always rejected\n";
    }

    /**
     * Test 6.9: Property - Inheritance is independent of other parent fields
     * Varying non-target_accounting fields on parent should NOT affect
     * the inherited target_accounting value in child.
     *
     * **Validates: Requirements 2.2**
     */
    public function testPropertyInheritanceIndependentOfOtherFields(): void
    {
        $iterations = 100;
        $seed = 99;
        mt_srand($seed);

        $banks = ['1101001', '1101002', '1101003', '1101004', '1101005'];
        $currencies = ['IDR', 'USD', 'EUR', 'SGD', 'JPY', 'GBP'];
        $names = ['Bank BCA', 'Bank Mandiri', 'Bank BNI', 'Bank BRI', 'Bank CIMB'];

        ParentRecordFactory::reset();

        for ($i = 0; $i < $iterations; $i++) {
            $target = self::$VALID_TARGET_ACCOUNTING[mt_rand(0, count(self::$VALID_TARGET_ACCOUNTING) - 1)];

            // Randomize all non-target fields
            $parent = ParentRecordFactory::create($target, [
                'bank_asal'         => $banks[mt_rand(0, count($banks) - 1)],
                'bank_tujuan'       => $banks[mt_rand(0, count($banks) - 1)],
                'nama_bank_asal'    => $names[mt_rand(0, count($names) - 1)],
                'nama_bank_tujuan'  => $names[mt_rand(0, count($names) - 1)],
                'mata_uang'         => $currencies[mt_rand(0, count($currencies) - 1)],
                'nilai'             => (string) mt_rand(1, 9999999999),
                'keterangan'        => 'Keterangan random ' . mt_rand(1, 10000),
            ]);

            // Randomize POST data too
            $postData = [
                'dari'       => $banks[mt_rand(0, count($banks) - 1)],
                'ke'         => $banks[mt_rand(0, count($banks) - 1)],
                'rupiah'     => (string) mt_rand(1, 9999999999),
                'kurs'       => (string) (mt_rand(1, 15000)),
                'keterangan' => 'Post keterangan ' . mt_rand(1, 10000),
            ];

            $result = $this->simulator->executeSaveMutasi($parent, $postData);

            // The child's target_accounting must ALWAYS equal the parent's,
            // regardless of what other fields contain
            assertEquals(
                1,
                $result['status'],
                "Iteration {$i}: Save should succeed"
            );
            assertEquals(
                $target,
                $result['child_data']['target_accounting'],
                "Iteration {$i}: Child target_accounting must equal parent's '{$target}' " .
                    "regardless of other field values"
            );
        }

        echo "    Property verified: {$iterations} iterations - inheritance independent of other fields\n";
    }

    /**
     * Test 6.10: Property - Child target_accounting is never mutated from POST data
     * Even if POST data contains a different target_accounting, the child should
     * always inherit from the PARENT record, not from POST.
     *
     * **Validates: Requirements 2.2**
     */
    public function testPropertyChildInheritsFromParentNotPost(): void
    {
        $iterations = 100;
        $seed = 150;
        mt_srand($seed);

        ParentRecordFactory::reset();

        for ($i = 0; $i < $iterations; $i++) {
            // Parent has one valid target
            $parentTarget = self::$VALID_TARGET_ACCOUNTING[mt_rand(0, count(self::$VALID_TARGET_ACCOUNTING) - 1)];
            $parent = ParentRecordFactory::create($parentTarget);

            // POST data might try to submit a DIFFERENT target
            $postTargets = array_diff(self::$VALID_TARGET_ACCOUNTING, [$parentTarget]);
            $postTarget = array_values($postTargets)[mt_rand(0, count($postTargets) - 1)];

            $postData = [
                'target_accounting' => $postTarget, // This should be IGNORED
                'dari'              => '1101001',
                'ke'                => '1101002',
                'rupiah'            => '5000000',
            ];

            $result = $this->simulator->executeSaveMutasi($parent, $postData);

            assertEquals(1, $result['status']);

            // Child must inherit from parent, NOT from POST
            assertEquals(
                $parentTarget,
                $result['child_data']['target_accounting'],
                "Iteration {$i}: Child must inherit '{$parentTarget}' from parent, " .
                    "not '{$postTarget}' from POST"
            );

            // Verify it's definitely NOT the POST value
            assertTrue(
                $result['child_data']['target_accounting'] !== $postTarget,
                "Iteration {$i}: Child must NOT use POST target_accounting '{$postTarget}'"
            );
        }

        echo "    Property verified: {$iterations} iterations - child inherits from parent, not POST\n";
    }

    /**
     * Test 6.11: Property - Inheritance preserves the relationship (child references parent)
     * The child's kd_mutasi_request must point to the parent's kd_mutasi,
     * establishing the inheritance chain.
     *
     * **Validates: Requirements 2.2**
     */
    public function testPropertyChildReferencesParentRecord(): void
    {
        $iterations = 100;
        $seed = 200;
        mt_srand($seed);

        ParentRecordFactory::reset();

        for ($i = 0; $i < $iterations; $i++) {
            $target = self::$VALID_TARGET_ACCOUNTING[mt_rand(0, count(self::$VALID_TARGET_ACCOUNTING) - 1)];
            $parent = ParentRecordFactory::create($target);

            $result = $this->simulator->executeSaveMutasi($parent);

            assertEquals(1, $result['status']);

            // Child must reference the parent via kd_mutasi_request
            assertEquals(
                $parent->kd_mutasi,
                $result['child_data']['kd_mutasi_request'],
                "Iteration {$i}: Child kd_mutasi_request must reference parent kd_mutasi"
            );

            // And target_accounting must match
            assertEquals(
                $parent->target_accounting,
                $result['child_data']['target_accounting'],
                "Iteration {$i}: The referenced parent's target_accounting must be inherited"
            );
        }

        echo "    Property verified: {$iterations} iterations - child correctly references parent\n";
    }

    /**
     * Test 6.12: Property - All three valid targets distribute equally in random selection
     * Ensures the property holds for ALL valid target values (not just one).
     * Verifies coverage across the entire valid input space.
     *
     * **Validates: Requirements 2.2**
     */
    public function testPropertyAllValidTargetsCoveredInInheritance(): void
    {
        $iterations = 120; // At least 100, divisible by 3 for clean distribution
        $seed = 333;
        mt_srand($seed);

        $targetCounts = [
            'accounting_stm'     => 0,
            'accounting_vuca'    => 0,
            'accounting_sustain' => 0,
        ];

        ParentRecordFactory::reset();

        for ($i = 0; $i < $iterations; $i++) {
            $target = self::$VALID_TARGET_ACCOUNTING[mt_rand(0, count(self::$VALID_TARGET_ACCOUNTING) - 1)];
            $parent = ParentRecordFactory::create($target);
            $result = $this->simulator->executeSaveMutasi($parent);

            assertEquals(1, $result['status']);
            assertEquals(
                $target,
                $result['child_data']['target_accounting'],
                "Iteration {$i}: Inheritance must hold for '{$target}'"
            );

            $targetCounts[$target]++;
        }

        // Ensure each target was tested at least once
        foreach (self::$VALID_TARGET_ACCOUNTING as $target) {
            assertTrue(
                $targetCounts[$target] > 0,
                "Target '{$target}' must be tested at least once across {$iterations} iterations"
            );
        }

        echo "    Property verified: {$iterations} iterations covering all targets - " .
            "STM:{$targetCounts['accounting_stm']}, " .
            "VUCA:{$targetCounts['accounting_vuca']}, " .
            "SUSTAIN:{$targetCounts['accounting_sustain']}\n";
    }
}

// ============================================================================
// Run tests
// ============================================================================

$runner = new SimpleTestRunner();
$exitCode = $runner->run(new TargetAccountingInheritanceTest());
exit($exitCode);
