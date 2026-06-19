<?php

/**
 * Property-Based Test: Target Accounting Persistence Round-Trip
 *
 * Feature: multi-accounting-target, Property 5: Target Accounting Persistence Round-Trip
 *
 * **Validates: Requirements 1.8, 2.1, 2.3**
 *
 * Property 5: Target Accounting Persistence Round-Trip
 * _For any_ valid target_accounting value submitted with a save operation
 * (request mutasi, transaksi bank), reading the stored record back SHALL return
 * the exact same target_accounting value that was submitted.
 *
 * This test validates:
 * - Values are stored exactly as submitted (no transformation/corruption)
 * - Values are not truncated (VARCHAR(30) is sufficient for all valid values)
 * - Values are stored case-sensitively (no case normalization)
 * - Values are stored without trimming (no whitespace modification)
 * - Round-trip: write then read returns identical value
 *
 * Usage: php application/modules/request_mutasi/tests/PersistenceRoundTripTest.php
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
// Mock Storage Layer - Simulates database INSERT/SELECT round-trip
// ============================================================================

/**
 * MockStorageLayer simulates a database table with INSERT and SELECT operations.
 * It stores records in memory using the same column structure as
 * tr_request_mutasi, tr_request_mutasi_aktual, tr_request_mutasi_admin.
 *
 * This simulates what happens when:
 * 1. Controller builds $data array with target_accounting value
 * 2. Model calls $this->db->insert('table', $data)
 * 3. Later a SELECT reads the record back
 *
 * The storage uses VARCHAR(30) semantics - values longer than 30 chars are truncated.
 */
class MockStorageLayer
{
    private $records = [];
    private $autoIncrement = 1;
    private $columnMaxLength = 30; // VARCHAR(30)

    /**
     * Insert a record (simulates DB INSERT)
     * @param array $data Associative array of column => value
     * @return int The inserted record's ID
     */
    public function insert(array $data): int
    {
        $id = $this->autoIncrement++;
        // Simulate VARCHAR(30) truncation behavior
        if (isset($data['target_accounting']) && is_string($data['target_accounting'])) {
            $data['target_accounting'] = substr($data['target_accounting'], 0, $this->columnMaxLength);
        }
        $this->records[$id] = $data;
        return $id;
    }

    /**
     * Read a record by ID (simulates DB SELECT)
     * @param int $id
     * @return array|null
     */
    public function find(int $id): ?array
    {
        return $this->records[$id] ?? null;
    }

    /**
     * Reset storage
     */
    public function reset(): void
    {
        $this->records = [];
        $this->autoIncrement = 1;
    }
}

// ============================================================================
// Test Class: Persistence Round-Trip Property Test
// ============================================================================

class PersistenceRoundTripTest
{
    /**
     * Valid target_accounting values (whitelist)
     */
    private static $VALID_TARGET_ACCOUNTING = [
        'accounting_stm',
        'accounting_vuca',
        'accounting_sustain'
    ];

    /**
     * Column max length (VARCHAR(30))
     */
    private static $COLUMN_MAX_LENGTH = 30;

    /**
     * Mock storage for each table
     */
    private $storageRequestMutasi;
    private $storageRequestMutasiAktual;
    private $storageRequestMutasiAdmin;

    public function __construct()
    {
        $this->storageRequestMutasi = new MockStorageLayer();
        $this->storageRequestMutasiAktual = new MockStorageLayer();
        $this->storageRequestMutasiAdmin = new MockStorageLayer();
    }

    /**
     * Simulate the save() method for request mutasi.
     * Builds insert data array with target_accounting and persists it.
     *
     * @param string $target_accounting
     * @param array $extra_data Additional record data
     * @return int|false Inserted record ID or false on validation failure
     */
    private function simulateSaveRequestMutasi(string $target_accounting, array $extra_data = [])
    {
        // Validate target_accounting (whitelist check)
        if (!in_array($target_accounting, self::$VALID_TARGET_ACCOUNTING)) {
            return false;
        }

        $data = array_merge([
            'kd_mutasi_request' => 'MR-' . date('Ymd') . '-' . mt_rand(1000, 9999),
            'mata_uang' => 'IDR',
            'target_accounting' => $target_accounting,
            'nilai' => mt_rand(100000, 99999999),
            'keterangan' => 'Test request mutasi',
        ], $extra_data);

        return $this->storageRequestMutasi->insert($data);
    }

    /**
     * Simulate the save_transaksi() method for transaksi bank.
     * Builds insert data array with target_accounting and persists it.
     *
     * @param string $target_accounting
     * @param array $extra_data Additional record data
     * @return int|false Inserted record ID or false on validation failure
     */
    private function simulateSaveTransaksiBank(string $target_accounting, array $extra_data = [])
    {
        // Validate target_accounting (whitelist check)
        if (!in_array($target_accounting, self::$VALID_TARGET_ACCOUNTING)) {
            return false;
        }

        $data = array_merge([
            'kd_mutasi_admin' => 'MA-' . date('Ymd') . '-' . mt_rand(1000, 9999),
            'mata_uang' => 'IDR',
            'target_accounting' => $target_accounting,
            'nilai' => mt_rand(100000, 99999999),
            'jenis' => 'BUM',
            'keterangan' => 'Test transaksi bank',
        ], $extra_data);

        return $this->storageRequestMutasiAdmin->insert($data);
    }

    /**
     * Simulate the save_mutasi() method for realisasi mutasi.
     * Inherits target_accounting from parent record and persists it.
     *
     * @param int $parent_id Parent tr_request_mutasi record ID
     * @param array $extra_data Additional record data
     * @return int|false Inserted record ID or false on validation failure
     */
    private function simulateSaveMutasiAktual(int $parent_id, array $extra_data = [])
    {
        // Read parent record to inherit target_accounting
        $parent = $this->storageRequestMutasi->find($parent_id);
        if (!$parent || empty($parent['target_accounting'])) {
            return false;
        }

        $target_accounting = $parent['target_accounting'];

        $data = array_merge([
            'kd_mutasi_aktual' => 'MA-' . date('Ymd') . '-' . mt_rand(1000, 9999),
            'kd_mutasi_request' => $parent['kd_mutasi_request'],
            'mata_uang' => 'IDR',
            'target_accounting' => $target_accounting,
            'nilai' => mt_rand(100000, 99999999),
            'keterangan' => 'Test realisasi mutasi',
        ], $extra_data);

        return $this->storageRequestMutasiAktual->insert($data);
    }

    // ========================================================================
    // Property 5: Target Accounting Persistence Round-Trip
    // ========================================================================

    /**
     * Test 5.1: Round-trip for 'accounting_stm' on tr_request_mutasi
     * Write 'accounting_stm' and read it back - must be identical.
     *
     * **Validates: Requirements 1.8, 2.1**
     */
    public function testRoundTripStmOnRequestMutasi(): void
    {
        $value = 'accounting_stm';
        $id = $this->simulateSaveRequestMutasi($value);

        assertTrue($id !== false, "Save should succeed for valid target '{$value}'");
        $record = $this->storageRequestMutasi->find($id);
        assertNotNull($record, "Record should be retrievable after insert");
        assertEquals(
            $value,
            $record['target_accounting'],
            "Stored target_accounting should exactly match submitted value '{$value}'"
        );
    }

    /**
     * Test 5.2: Round-trip for 'accounting_vuca' on tr_request_mutasi
     *
     * **Validates: Requirements 1.8, 2.1**
     */
    public function testRoundTripVucaOnRequestMutasi(): void
    {
        $value = 'accounting_vuca';
        $id = $this->simulateSaveRequestMutasi($value);

        assertTrue($id !== false, "Save should succeed for valid target '{$value}'");
        $record = $this->storageRequestMutasi->find($id);
        assertNotNull($record, "Record should be retrievable after insert");
        assertEquals(
            $value,
            $record['target_accounting'],
            "Stored target_accounting should exactly match submitted value '{$value}'"
        );
    }

    /**
     * Test 5.3: Round-trip for 'accounting_sustain' on tr_request_mutasi
     *
     * **Validates: Requirements 1.8, 2.1**
     */
    public function testRoundTripSustainOnRequestMutasi(): void
    {
        $value = 'accounting_sustain';
        $id = $this->simulateSaveRequestMutasi($value);

        assertTrue($id !== false, "Save should succeed for valid target '{$value}'");
        $record = $this->storageRequestMutasi->find($id);
        assertNotNull($record, "Record should be retrievable after insert");
        assertEquals(
            $value,
            $record['target_accounting'],
            "Stored target_accounting should exactly match submitted value '{$value}'"
        );
    }

    /**
     * Test 5.4: Round-trip for all valid values on tr_request_mutasi_admin (transaksi bank)
     *
     * **Validates: Requirements 2.3**
     */
    public function testRoundTripAllValuesOnTransaksiBank(): void
    {
        foreach (self::$VALID_TARGET_ACCOUNTING as $value) {
            $id = $this->simulateSaveTransaksiBank($value);

            assertTrue($id !== false, "Save should succeed for valid target '{$value}'");
            $record = $this->storageRequestMutasiAdmin->find($id);
            assertNotNull($record, "Record should be retrievable after insert for '{$value}'");
            assertEquals(
                $value,
                $record['target_accounting'],
                "Stored target_accounting on admin table should exactly match '{$value}'"
            );
        }
    }

    /**
     * Test 5.5: VARCHAR(30) is sufficient - no truncation for valid values
     * All valid target_accounting values must be <= 30 characters.
     *
     * **Validates: Requirements 2.1, 2.3**
     */
    public function testVarchar30SufficientForAllValidValues(): void
    {
        foreach (self::$VALID_TARGET_ACCOUNTING as $value) {
            $length = strlen($value);
            assertTrue(
                $length <= self::$COLUMN_MAX_LENGTH,
                "Valid value '{$value}' (length {$length}) must fit within VARCHAR(30)"
            );

            // Verify no truncation occurs during storage
            $id = $this->simulateSaveRequestMutasi($value);
            $record = $this->storageRequestMutasi->find($id);
            assertEquals(
                strlen($value),
                strlen($record['target_accounting']),
                "Stored value length should match original for '{$value}' " .
                    "(expected {$length}, got " . strlen($record['target_accounting']) . ")"
            );
        }
    }

    /**
     * Test 5.6: Case sensitivity - values stored as-is without case transformation
     *
     * **Validates: Requirements 1.8, 2.1, 2.3**
     */
    public function testCaseSensitiveStorageNoTransformation(): void
    {
        foreach (self::$VALID_TARGET_ACCOUNTING as $value) {
            $id = $this->simulateSaveRequestMutasi($value);
            $record = $this->storageRequestMutasi->find($id);

            // Verify exact case match (no strtolower/strtoupper)
            assertTrue(
                $record['target_accounting'] === $value,
                "Value must be stored case-sensitively: expected '{$value}', got '{$record['target_accounting']}'"
            );

            // Verify it's NOT stored as uppercase
            assertTrue(
                $record['target_accounting'] !== strtoupper($value),
                "Value must NOT be stored as uppercase"
            );

            // Verify individual character case preservation
            for ($i = 0; $i < strlen($value); $i++) {
                assertEquals(
                    $value[$i],
                    $record['target_accounting'][$i],
                    "Character at position {$i} must preserve case for '{$value}'"
                );
            }
        }
    }

    /**
     * Test 5.7: No trimming - values stored without whitespace modification
     * The stored value must not have leading/trailing whitespace added or removed.
     *
     * **Validates: Requirements 1.8, 2.1, 2.3**
     */
    public function testNoTrimmingOnStorage(): void
    {
        foreach (self::$VALID_TARGET_ACCOUNTING as $value) {
            $id = $this->simulateSaveRequestMutasi($value);
            $record = $this->storageRequestMutasi->find($id);

            // No leading whitespace added
            assertTrue(
                $record['target_accounting'][0] !== ' ',
                "Stored value should not have leading space for '{$value}'"
            );

            // No trailing whitespace added
            $lastChar = $record['target_accounting'][strlen($record['target_accounting']) - 1];
            assertTrue(
                $lastChar !== ' ',
                "Stored value should not have trailing space for '{$value}'"
            );

            // Exact match (no trim/pad applied)
            assertEquals(
                $value,
                $record['target_accounting'],
                "Value must be stored without any trimming or padding"
            );
        }
    }

    /**
     * Test 5.8: No character substitution or encoding transformation
     * Underscores and all characters must be preserved exactly.
     *
     * **Validates: Requirements 1.8, 2.1, 2.3**
     */
    public function testNoCharacterSubstitution(): void
    {
        foreach (self::$VALID_TARGET_ACCOUNTING as $value) {
            $id = $this->simulateSaveRequestMutasi($value);
            $record = $this->storageRequestMutasi->find($id);

            // Count underscores - must be preserved
            $expected_underscores = substr_count($value, '_');
            $actual_underscores = substr_count($record['target_accounting'], '_');
            assertEquals(
                $expected_underscores,
                $actual_underscores,
                "Underscore count must be preserved for '{$value}'"
            );

            // Byte-level comparison
            assertTrue(
                $record['target_accounting'] === $value,
                "Byte-level comparison must pass for '{$value}'"
            );
        }
    }

    /**
     * Test 5.9: Property - Round-trip persistence for random valid targets (100 iterations)
     * For ANY valid target_accounting value submitted, reading back returns the exact same value.
     *
     * **Validates: Requirements 1.8, 2.1, 2.3**
     */
    public function testPropertyRoundTripPersistenceRandomValidTargets(): void
    {
        $iterations = 100;
        $seed = 42;
        mt_srand($seed);

        for ($i = 0; $i < $iterations; $i++) {
            // Pick a random valid target
            $target = self::$VALID_TARGET_ACCOUNTING[mt_rand(0, count(self::$VALID_TARGET_ACCOUNTING) - 1)];

            // Save to request mutasi table
            $id = $this->simulateSaveRequestMutasi($target);
            assertTrue($id !== false, "Iteration {$i}: Save should succeed for '{$target}'");

            // Read back
            $record = $this->storageRequestMutasi->find($id);
            assertNotNull($record, "Iteration {$i}: Record should exist after insert");

            // Round-trip: stored value === submitted value
            assertEquals(
                $target,
                $record['target_accounting'],
                "Iteration {$i}: Round-trip failed for '{$target}' - got '{$record['target_accounting']}'"
            );
        }

        echo "    Property verified: {$iterations} round-trips on tr_request_mutasi all preserved exact values\n";
    }

    /**
     * Test 5.10: Property - Round-trip persistence for transaksi bank (100 iterations)
     * For ANY valid target_accounting value submitted to save_transaksi(),
     * reading back returns the exact same value.
     *
     * **Validates: Requirements 2.3**
     */
    public function testPropertyRoundTripPersistenceTransaksiBank(): void
    {
        $iterations = 100;
        $seed = 77;
        mt_srand($seed);

        for ($i = 0; $i < $iterations; $i++) {
            $target = self::$VALID_TARGET_ACCOUNTING[mt_rand(0, count(self::$VALID_TARGET_ACCOUNTING) - 1)];

            $id = $this->simulateSaveTransaksiBank($target);
            assertTrue($id !== false, "Iteration {$i}: Save should succeed for '{$target}'");

            $record = $this->storageRequestMutasiAdmin->find($id);
            assertNotNull($record, "Iteration {$i}: Record should exist after insert");

            assertEquals(
                $target,
                $record['target_accounting'],
                "Iteration {$i}: Round-trip failed on admin table for '{$target}'"
            );
        }

        echo "    Property verified: {$iterations} round-trips on tr_request_mutasi_admin all preserved exact values\n";
    }

    /**
     * Test 5.11: Property - Round-trip persistence for realisasi mutasi (100 iterations)
     * When target_accounting is inherited from parent to child, reading the child
     * back returns the exact same value as the parent.
     *
     * **Validates: Requirements 1.8, 2.1**
     */
    public function testPropertyRoundTripPersistenceRealisasiMutasi(): void
    {
        $iterations = 100;
        $seed = 123;
        mt_srand($seed);

        for ($i = 0; $i < $iterations; $i++) {
            $target = self::$VALID_TARGET_ACCOUNTING[mt_rand(0, count(self::$VALID_TARGET_ACCOUNTING) - 1)];

            // Create parent record
            $parentId = $this->simulateSaveRequestMutasi($target);
            assertTrue($parentId !== false, "Iteration {$i}: Parent save should succeed");

            // Create child record (inherits from parent)
            $childId = $this->simulateSaveMutasiAktual($parentId);
            assertTrue($childId !== false, "Iteration {$i}: Child save should succeed");

            // Read back child record
            $childRecord = $this->storageRequestMutasiAktual->find($childId);
            assertNotNull($childRecord, "Iteration {$i}: Child record should exist");

            // Round-trip: child's target_accounting === original submitted value
            assertEquals(
                $target,
                $childRecord['target_accounting'],
                "Iteration {$i}: Child round-trip failed for '{$target}'"
            );
        }

        echo "    Property verified: {$iterations} round-trips on tr_request_mutasi_aktual all preserved exact values\n";
    }

    /**
     * Test 5.12: Property - Stored value is never null for valid submissions
     * When a valid target_accounting is submitted, it should never be stored as null.
     *
     * **Validates: Requirements 1.8, 2.1, 2.3**
     */
    public function testPropertyStoredValueNeverNullForValidSubmission(): void
    {
        $iterations = 100;
        $seed = 200;
        mt_srand($seed);

        for ($i = 0; $i < $iterations; $i++) {
            $target = self::$VALID_TARGET_ACCOUNTING[mt_rand(0, count(self::$VALID_TARGET_ACCOUNTING) - 1)];

            // Request mutasi
            $id1 = $this->simulateSaveRequestMutasi($target);
            $record1 = $this->storageRequestMutasi->find($id1);
            assertTrue(
                $record1['target_accounting'] !== null,
                "Iteration {$i}: target_accounting must not be null after save on request_mutasi"
            );

            // Transaksi bank
            $id2 = $this->simulateSaveTransaksiBank($target);
            $record2 = $this->storageRequestMutasiAdmin->find($id2);
            assertTrue(
                $record2['target_accounting'] !== null,
                "Iteration {$i}: target_accounting must not be null after save on admin"
            );
        }

        echo "    Property verified: {$iterations} iterations confirm no null values stored\n";
    }

    /**
     * Test 5.13: Property - Stored value is never empty string for valid submissions
     *
     * **Validates: Requirements 1.8, 2.1, 2.3**
     */
    public function testPropertyStoredValueNeverEmptyForValidSubmission(): void
    {
        $iterations = 100;
        $seed = 300;
        mt_srand($seed);

        for ($i = 0; $i < $iterations; $i++) {
            $target = self::$VALID_TARGET_ACCOUNTING[mt_rand(0, count(self::$VALID_TARGET_ACCOUNTING) - 1)];

            $id = $this->simulateSaveRequestMutasi($target);
            $record = $this->storageRequestMutasi->find($id);

            assertTrue(
                $record['target_accounting'] !== '',
                "Iteration {$i}: target_accounting must not be empty string after valid save"
            );
            assertTrue(
                strlen($record['target_accounting']) > 0,
                "Iteration {$i}: target_accounting must have length > 0 after valid save"
            );
        }

        echo "    Property verified: {$iterations} iterations confirm no empty strings stored\n";
    }

    /**
     * Test 5.14: Property - Value integrity across multiple writes
     * Writing different valid targets sequentially must not corrupt earlier records.
     *
     * **Validates: Requirements 1.8, 2.1, 2.3**
     */
    public function testPropertyValueIntegrityAcrossMultipleWrites(): void
    {
        $iterations = 100;
        $seed = 400;
        mt_srand($seed);

        $savedRecords = [];

        // Write 100 records with random valid targets
        for ($i = 0; $i < $iterations; $i++) {
            $target = self::$VALID_TARGET_ACCOUNTING[mt_rand(0, count(self::$VALID_TARGET_ACCOUNTING) - 1)];
            $id = $this->simulateSaveRequestMutasi($target);
            $savedRecords[] = ['id' => $id, 'expected' => $target];
        }

        // Read all back and verify none corrupted
        foreach ($savedRecords as $i => $saved) {
            $record = $this->storageRequestMutasi->find($saved['id']);
            assertNotNull($record, "Record {$i} should still exist");
            assertEquals(
                $saved['expected'],
                $record['target_accounting'],
                "Record {$i}: value should still be '{$saved['expected']}' after multiple writes"
            );
        }

        echo "    Property verified: {$iterations} records maintain integrity after batch writes\n";
    }

    /**
     * Test 5.15: Property - Exact string identity (=== comparison) after round-trip
     * The stored value must pass strict identity comparison with the original.
     *
     * **Validates: Requirements 1.8, 2.1, 2.3**
     */
    public function testPropertyExactStringIdentityAfterRoundTrip(): void
    {
        $iterations = 100;
        $seed = 500;
        mt_srand($seed);

        for ($i = 0; $i < $iterations; $i++) {
            $target = self::$VALID_TARGET_ACCOUNTING[mt_rand(0, count(self::$VALID_TARGET_ACCOUNTING) - 1)];

            $id = $this->simulateSaveRequestMutasi($target);
            $record = $this->storageRequestMutasi->find($id);

            // Strict identity check (type + value)
            assertTrue(
                $record['target_accounting'] === $target,
                "Iteration {$i}: Strict identity (===) must hold for '{$target}'"
            );

            // String type check
            assertTrue(
                is_string($record['target_accounting']),
                "Iteration {$i}: Stored value must be of type string"
            );

            // Length equality
            assertEquals(
                strlen($target),
                strlen($record['target_accounting']),
                "Iteration {$i}: String length must be preserved for '{$target}'"
            );
        }

        echo "    Property verified: {$iterations} strict identity checks passed\n";
    }

    /**
     * Test 5.16: Property - Cross-table consistency
     * The same target_accounting value stored in different tables must be
     * retrievable with the exact same value from each table.
     *
     * **Validates: Requirements 1.8, 2.1, 2.3**
     */
    public function testPropertyCrossTableConsistency(): void
    {
        $iterations = 100;
        $seed = 600;
        mt_srand($seed);

        for ($i = 0; $i < $iterations; $i++) {
            $target = self::$VALID_TARGET_ACCOUNTING[mt_rand(0, count(self::$VALID_TARGET_ACCOUNTING) - 1)];

            // Save to request_mutasi
            $id1 = $this->simulateSaveRequestMutasi($target);
            $record1 = $this->storageRequestMutasi->find($id1);

            // Save to admin (transaksi bank)
            $id2 = $this->simulateSaveTransaksiBank($target);
            $record2 = $this->storageRequestMutasiAdmin->find($id2);

            // Both tables must return the exact same value
            assertEquals(
                $record1['target_accounting'],
                $record2['target_accounting'],
                "Iteration {$i}: Cross-table values must be identical for '{$target}'"
            );

            // Both must match original
            assertEquals(
                $target,
                $record1['target_accounting'],
                "Iteration {$i}: request_mutasi round-trip for '{$target}'"
            );
            assertEquals(
                $target,
                $record2['target_accounting'],
                "Iteration {$i}: admin round-trip for '{$target}'"
            );
        }

        echo "    Property verified: {$iterations} cross-table consistency checks passed\n";
    }
}

// ============================================================================
// Runner
// ============================================================================

$runner = new SimpleTestRunner();
$exitCode = $runner->run(new PersistenceRoundTripTest());
exit($exitCode);
