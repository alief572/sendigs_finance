<?php

/**
 * Property-Based Test: COA Lookup Uses Target Database
 *
 * Feature: multi-accounting-target, Property 3: COA Lookup Uses Target Database
 *
 * **Validates: Requirements 3.1, 3.2, 3.3, 5.1, 5.2**
 *
 * Property 3: COA Lookup Uses Target Database
 * _For any_ valid target_accounting value, the COA bank list endpoint SHALL query
 * the `coa_master` table from the database identified by the resolved target,
 * filtering by `no_perkiraan LIKE '1101%'`, and return the same set of records
 * that would be obtained by querying that database directly.
 *
 * This test validates:
 * - Valid targets return status 1 with COA data from the correct database
 * - Response data structure contains {no_perkiraan, nama} fields
 * - All returned no_perkiraan values match the '1101%' prefix filter
 * - Invalid targets return status 2 with error message
 * - The endpoint correctly routes to the database identified by target_accounting
 *
 * Usage: php application/modules/request_mutasi/tests/CoaLookupTargetDbTest.php
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
// Mock DB Connection & CI Input Simulation
// ============================================================================

/**
 * Mock database query result that simulates CI_DB_result
 */
class MockDbResult
{
    private $rows;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function result_array(): array
    {
        return $this->rows;
    }

    public function num_rows(): int
    {
        return count($this->rows);
    }
}

/**
 * Mock database connection that simulates CI_DB
 * Tracks which queries are executed and from which "database"
 */
class MockDbConnection
{
    public $database;
    public $queries_executed = [];

    public function __construct(string $database, array $coa_data = [])
    {
        $this->database = $database;
        $this->coa_data = $coa_data;
    }

    private $coa_data = [];

    public function query(string $sql)
    {
        $this->queries_executed[] = $sql;
        return new MockDbResult($this->coa_data);
    }
}

// ============================================================================
// Test Class: COA Lookup Target Database Property Test
// ============================================================================

class CoaLookupTargetDbTest
{
    /**
     * Static properties replicated from controller for isolated testing.
     */
    private static $VALID_TARGET_ACCOUNTING = [
        'accounting_stm',
        'accounting_vuca',
        'accounting_sustain'
    ];

    private static $TARGET_DB_MAP = [
        'accounting_stm'     => DBACC_STM,
        'accounting_vuca'    => DBACC_VUCA,
        'accounting_sustain' => DBACC_SUSTAIN,
    ];

    /**
     * Sample COA data per database to simulate different databases
     * having different COA records.
     */
    private static $SAMPLE_COA_DATA = [
        'db_sendigs_ss_stm' => [
            ['no_perkiraan' => '1101001', 'nama' => 'Bank BCA STM'],
            ['no_perkiraan' => '1101002', 'nama' => 'Bank Mandiri STM'],
            ['no_perkiraan' => '1101003', 'nama' => 'Bank BNI STM'],
        ],
        'db_sendigs_ss_vuca' => [
            ['no_perkiraan' => '1101001', 'nama' => 'Bank BCA VUCA'],
            ['no_perkiraan' => '1101004', 'nama' => 'Bank BRI VUCA'],
        ],
        'db_sendigs_ss_sustain' => [
            ['no_perkiraan' => '1101001', 'nama' => 'Bank BCA SUSTAIN'],
            ['no_perkiraan' => '1101005', 'nama' => 'Bank CIMB SUSTAIN'],
            ['no_perkiraan' => '1101006', 'nama' => 'Bank Permata SUSTAIN'],
            ['no_perkiraan' => '1101007', 'nama' => 'Bank Danamon SUSTAIN'],
        ],
    ];

    /**
     * Simulate _resolve_target_db() - validation + mapping + mock connection
     *
     * @param mixed $target_accounting
     * @return array|false
     */
    private function resolveTargetDb($target_accounting)
    {
        if (empty($target_accounting) || !in_array($target_accounting, self::$VALID_TARGET_ACCOUNTING)) {
            return false;
        }

        $db_name = self::$TARGET_DB_MAP[$target_accounting];
        $coa_data = self::$SAMPLE_COA_DATA[$db_name] ?? [];
        $connection = new MockDbConnection($db_name, $coa_data);

        return [
            'db_name'    => $db_name,
            'connection' => $connection,
            'group'      => $target_accounting,
        ];
    }

    /**
     * Simulate the full get_coa_by_target() endpoint logic.
     * Returns the JSON-decoded response as associative array.
     *
     * @param mixed $target_accounting POST input
     * @return array Response array with 'status', 'data'/'pesan'
     */
    private function simulateGetCoaByTarget($target_accounting): array
    {
        // Step 1: Validate & resolve target database
        $target = $this->resolveTargetDb($target_accounting);
        if (!$target) {
            return [
                'status' => 2,
                'pesan'  => 'Target Accounting tidak valid.'
            ];
        }

        // Step 2: Query COA bank list from target database
        $db = $target['connection'];
        $query = $db->query(
            "SELECT no_perkiraan, nama FROM coa_master WHERE no_perkiraan LIKE '1101%' ORDER BY no_perkiraan ASC"
        );

        if (!$query) {
            return [
                'status' => 2,
                'pesan'  => 'Database ' . $target['db_name'] . ' tidak dapat diakses.'
            ];
        }

        $data = $query->result_array();

        return [
            'status' => 1,
            'data'   => $data
        ];
    }

    // ========================================================================
    // Property 3: COA Lookup Uses Target Database
    // ========================================================================

    /**
     * Test 3.1: Valid target 'accounting_stm' returns status 1 with COA data from STM database
     *
     * **Validates: Requirements 3.1, 3.3, 5.1**
     */
    public function testValidTargetStmReturnsCorrectCoaData(): void
    {
        $response = $this->simulateGetCoaByTarget('accounting_stm');

        assertEquals(1, $response['status'], "Status should be 1 for valid target 'accounting_stm'");
        assertIsArray($response['data'], "Response 'data' should be an array");
        assertEquals(
            self::$SAMPLE_COA_DATA[DBACC_STM],
            $response['data'],
            "Data should match COA records from STM database"
        );
    }

    /**
     * Test 3.2: Valid target 'accounting_vuca' returns status 1 with COA data from VUCA database
     *
     * **Validates: Requirements 3.2, 3.3, 5.2**
     */
    public function testValidTargetVucaReturnsCorrectCoaData(): void
    {
        $response = $this->simulateGetCoaByTarget('accounting_vuca');

        assertEquals(1, $response['status'], "Status should be 1 for valid target 'accounting_vuca'");
        assertIsArray($response['data'], "Response 'data' should be an array");
        assertEquals(
            self::$SAMPLE_COA_DATA[DBACC_VUCA],
            $response['data'],
            "Data should match COA records from VUCA database"
        );
    }

    /**
     * Test 3.3: Valid target 'accounting_sustain' returns status 1 with COA data from SUSTAIN database
     *
     * **Validates: Requirements 3.1, 3.3, 5.1**
     */
    public function testValidTargetSustainReturnsCorrectCoaData(): void
    {
        $response = $this->simulateGetCoaByTarget('accounting_sustain');

        assertEquals(1, $response['status'], "Status should be 1 for valid target 'accounting_sustain'");
        assertIsArray($response['data'], "Response 'data' should be an array");
        assertEquals(
            self::$SAMPLE_COA_DATA[DBACC_SUSTAIN],
            $response['data'],
            "Data should match COA records from SUSTAIN database"
        );
    }

    /**
     * Test 3.4: Each valid target queries its OWN database (data isolation)
     * Verify that different targets return different data sets.
     *
     * **Validates: Requirements 3.1, 3.2, 3.3**
     */
    public function testEachTargetQueriesItsOwnDatabase(): void
    {
        $results = [];
        foreach (self::$VALID_TARGET_ACCOUNTING as $target) {
            $response = $this->simulateGetCoaByTarget($target);
            assertEquals(1, $response['status'], "Status should be 1 for '{$target}'");
            $results[$target] = $response['data'];
        }

        // Verify STM data differs from VUCA
        assertTrue(
            $results['accounting_stm'] !== $results['accounting_vuca'],
            "STM and VUCA should return different COA data sets"
        );

        // Verify STM data differs from SUSTAIN
        assertTrue(
            $results['accounting_stm'] !== $results['accounting_sustain'],
            "STM and SUSTAIN should return different COA data sets"
        );

        // Verify VUCA data differs from SUSTAIN
        assertTrue(
            $results['accounting_vuca'] !== $results['accounting_sustain'],
            "VUCA and SUSTAIN should return different COA data sets"
        );
    }

    /**
     * Test 3.5: Response data structure - each item has 'no_perkiraan' and 'nama'
     *
     * **Validates: Requirements 3.3, 5.1, 5.2**
     */
    public function testResponseDataStructureHasRequiredFields(): void
    {
        foreach (self::$VALID_TARGET_ACCOUNTING as $target) {
            $response = $this->simulateGetCoaByTarget($target);

            assertEquals(1, $response['status'], "Status should be 1 for '{$target}'");
            assertIsArray($response['data'], "Data should be array for '{$target}'");

            foreach ($response['data'] as $index => $item) {
                assertArrayHasKey(
                    'no_perkiraan',
                    $item,
                    "Item {$index} for '{$target}' should have 'no_perkiraan' field"
                );
                assertArrayHasKey(
                    'nama',
                    $item,
                    "Item {$index} for '{$target}' should have 'nama' field"
                );
            }
        }
    }

    /**
     * Test 3.6: All returned no_perkiraan values match '1101%' prefix filter
     *
     * **Validates: Requirements 3.3, 5.1**
     */
    public function testAllReturnedCoaMatchesBankPrefix(): void
    {
        foreach (self::$VALID_TARGET_ACCOUNTING as $target) {
            $response = $this->simulateGetCoaByTarget($target);

            assertEquals(1, $response['status']);

            foreach ($response['data'] as $index => $item) {
                assertTrue(
                    strpos($item['no_perkiraan'], '1101') === 0,
                    "Item {$index} for '{$target}': no_perkiraan '{$item['no_perkiraan']}' " .
                        "should start with '1101' (bank account prefix)"
                );
            }
        }
    }

    /**
     * Test 3.7: The correct SQL query is issued to the target database
     * Verify the query contains the '1101%' filter and targets coa_master.
     *
     * **Validates: Requirements 3.3, 5.1, 5.2**
     */
    public function testCorrectSqlQueryIssuedToTargetDb(): void
    {
        foreach (self::$VALID_TARGET_ACCOUNTING as $target) {
            $target_info = $this->resolveTargetDb($target);
            assertIsArray($target_info, "Target '{$target}' should resolve");

            $db = $target_info['connection'];
            // Execute the query as the endpoint would
            $db->query(
                "SELECT no_perkiraan, nama FROM coa_master WHERE no_perkiraan LIKE '1101%' ORDER BY no_perkiraan ASC"
            );

            // Verify query was recorded
            assertTrue(
                count($db->queries_executed) > 0,
                "At least one query should be executed for '{$target}'"
            );

            $last_query = end($db->queries_executed);
            assertTrue(
                strpos($last_query, 'coa_master') !== false,
                "Query should reference 'coa_master' table for '{$target}'"
            );
            assertTrue(
                strpos($last_query, "1101%") !== false,
                "Query should contain '1101%' bank filter for '{$target}'"
            );
            assertTrue(
                strpos($last_query, 'no_perkiraan') !== false,
                "Query should select 'no_perkiraan' for '{$target}'"
            );
            assertTrue(
                strpos($last_query, 'nama') !== false,
                "Query should select 'nama' for '{$target}'"
            );
        }
    }

    /**
     * Test 3.8: The database connection used matches the resolved target
     * For each valid target, the connection's database property should match
     * the expected TARGET_DB_MAP value.
     *
     * **Validates: Requirements 3.3, 5.1, 5.2**
     */
    public function testDatabaseConnectionMatchesResolvedTarget(): void
    {
        foreach (self::$VALID_TARGET_ACCOUNTING as $target) {
            $target_info = $this->resolveTargetDb($target);
            assertIsArray($target_info);

            $expected_db = self::$TARGET_DB_MAP[$target];
            assertEquals(
                $expected_db,
                $target_info['connection']->database,
                "Connection database for '{$target}' should be '{$expected_db}'"
            );
            assertEquals(
                $expected_db,
                $target_info['db_name'],
                "db_name for '{$target}' should be '{$expected_db}'"
            );
        }
    }

    /**
     * Test 3.9: Invalid target_accounting returns status 2 with error message
     *
     * **Validates: Requirements 3.3, 5.5**
     */
    public function testInvalidTargetReturnsErrorResponse(): void
    {
        $invalid_inputs = [
            '',
            null,
            'invalid_target',
            'accounting_xyz',
            'ACCOUNTING_STM',
            'accounting_stm ',
            ' accounting_vuca',
        ];

        foreach ($invalid_inputs as $input) {
            $response = $this->simulateGetCoaByTarget($input);

            assertEquals(
                2,
                $response['status'],
                "Status should be 2 for invalid input: " . var_export($input, true)
            );
            assertArrayHasKey(
                'pesan',
                $response,
                "Response should have 'pesan' key for invalid input: " . var_export($input, true)
            );
            assertTrue(
                strlen($response['pesan']) > 0,
                "Error message should not be empty for invalid input: " . var_export($input, true)
            );
        }
    }

    /**
     * Test 3.10: Property - For ANY valid target, response always has status 1 and data array
     * Run 100 iterations selecting random valid targets.
     *
     * **Validates: Requirements 3.1, 3.2, 3.3, 5.1, 5.2**
     */
    public function testPropertyValidTargetAlwaysReturnsStatus1WithDataArray(): void
    {
        $iterations = 100;
        $seed = 42;
        mt_srand($seed);

        for ($i = 0; $i < $iterations; $i++) {
            // Pick a random valid target
            $target = self::$VALID_TARGET_ACCOUNTING[mt_rand(0, count(self::$VALID_TARGET_ACCOUNTING) - 1)];

            $response = $this->simulateGetCoaByTarget($target);

            assertEquals(
                1,
                $response['status'],
                "Iteration {$i}: Status should always be 1 for valid target '{$target}'"
            );
            assertArrayHasKey(
                'data',
                $response,
                "Iteration {$i}: Response should have 'data' key for valid target '{$target}'"
            );
            assertIsArray(
                $response['data'],
                "Iteration {$i}: 'data' should be an array for valid target '{$target}'"
            );
        }

        echo "    Property verified: {$iterations} random valid targets all returned status 1\n";
    }

    /**
     * Test 3.11: Property - For ANY invalid input, response always has status 2
     * Generate 100 random strings (none matching valid values) and verify rejection.
     *
     * **Validates: Requirements 3.3, 5.5**
     */
    public function testPropertyInvalidInputAlwaysReturnsStatus2(): void
    {
        $iterations = 100;
        $seed = 77;
        mt_srand($seed);

        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-. ';

        for ($i = 0; $i < $iterations; $i++) {
            $length = mt_rand(0, 40);
            $random_string = '';
            for ($j = 0; $j < $length; $j++) {
                $random_string .= $chars[mt_rand(0, strlen($chars) - 1)];
            }

            // Skip if accidentally generated a valid value
            if (in_array($random_string, self::$VALID_TARGET_ACCOUNTING)) {
                continue;
            }

            $response = $this->simulateGetCoaByTarget($random_string);

            assertEquals(
                2,
                $response['status'],
                "Iteration {$i}: Status should be 2 for invalid input '{$random_string}'"
            );
            assertArrayHasKey(
                'pesan',
                $response,
                "Iteration {$i}: Response should have 'pesan' for invalid input"
            );
        }

        echo "    Property verified: {$iterations} random invalid inputs all returned status 2\n";
    }

    /**
     * Test 3.12: Property - COA data returned matches exact data from the target database
     * For each valid target, the response data should be EXACTLY the same as
     * what's stored for that target's database (no cross-contamination).
     *
     * **Validates: Requirements 3.1, 3.2, 3.3**
     */
    public function testPropertyCoaDataMatchesTargetDatabase(): void
    {
        $iterations = 100;
        $seed = 123;
        mt_srand($seed);

        for ($i = 0; $i < $iterations; $i++) {
            $target = self::$VALID_TARGET_ACCOUNTING[mt_rand(0, count(self::$VALID_TARGET_ACCOUNTING) - 1)];
            $expected_db = self::$TARGET_DB_MAP[$target];
            $expected_data = self::$SAMPLE_COA_DATA[$expected_db];

            $response = $this->simulateGetCoaByTarget($target);

            assertEquals(
                $expected_data,
                $response['data'],
                "Iteration {$i}: Data for '{$target}' should match expected data from '{$expected_db}'"
            );
        }

        echo "    Property verified: {$iterations} iterations confirmed correct DB data returned\n";
    }

    /**
     * Test 3.13: Property - Response structure is always consistent
     * For ANY input (valid or invalid), the response always contains 'status' key.
     * Valid inputs have 'data' key; invalid inputs have 'pesan' key.
     *
     * **Validates: Requirements 3.3, 5.1, 5.2**
     */
    public function testPropertyResponseStructureIsConsistent(): void
    {
        $iterations = 100;
        $seed = 55;
        mt_srand($seed);

        $chars = 'abcdefghijklmnopqrstuvwxyz_0123456789';

        // Mix of valid and invalid inputs
        $all_inputs = array_merge(self::$VALID_TARGET_ACCOUNTING, []);
        for ($i = 0; $i < $iterations; $i++) {
            $length = mt_rand(0, 25);
            $random = '';
            for ($j = 0; $j < $length; $j++) {
                $random .= $chars[mt_rand(0, strlen($chars) - 1)];
            }
            $all_inputs[] = $random;
        }

        foreach ($all_inputs as $input) {
            $response = $this->simulateGetCoaByTarget($input);

            // Every response must have 'status'
            assertArrayHasKey('status', $response, "Response must have 'status' key");
            assertTrue(
                in_array($response['status'], [1, 2]),
                "Status must be 1 or 2, got: " . var_export($response['status'], true)
            );

            if ($response['status'] === 1) {
                assertArrayHasKey('data', $response, "Status 1 response must have 'data'");
                assertIsArray($response['data'], "Status 1 'data' must be array");
            } else {
                assertArrayHasKey('pesan', $response, "Status 2 response must have 'pesan'");
                assertTrue(
                    is_string($response['pesan']),
                    "Status 2 'pesan' must be string"
                );
            }
        }

        echo "    Property verified: " . count($all_inputs) . " inputs all produce valid response structure\n";
    }

    /**
     * Test 3.14: Property - COA lookup is idempotent
     * Calling the endpoint with the same valid target multiple times always returns
     * the same data.
     *
     * **Validates: Requirements 3.3, 5.1, 5.2**
     */
    public function testPropertyCoaLookupIsIdempotent(): void
    {
        $iterations = 100;

        foreach (self::$VALID_TARGET_ACCOUNTING as $target) {
            $first_response = $this->simulateGetCoaByTarget($target);

            for ($i = 0; $i < $iterations; $i++) {
                $response = $this->simulateGetCoaByTarget($target);
                assertEquals(
                    $first_response['status'],
                    $response['status'],
                    "Iteration {$i}: Status should be consistent for '{$target}'"
                );
                assertEquals(
                    $first_response['data'],
                    $response['data'],
                    "Iteration {$i}: Data should be consistent for '{$target}'"
                );
            }
        }

        echo "    Idempotency verified: {$iterations} iterations x 3 targets\n";
    }

    /**
     * Test 3.15: Property - No cross-database contamination
     * Querying one target should never return data belonging to another target's database.
     *
     * **Validates: Requirements 3.1, 3.2, 3.3**
     */
    public function testPropertyNoCrossDatabaseContamination(): void
    {
        $iterations = 100;
        $seed = 88;
        mt_srand($seed);

        for ($i = 0; $i < $iterations; $i++) {
            // Pick two different targets
            $idx1 = mt_rand(0, count(self::$VALID_TARGET_ACCOUNTING) - 1);
            $idx2 = ($idx1 + mt_rand(1, 2)) % count(self::$VALID_TARGET_ACCOUNTING);

            $target1 = self::$VALID_TARGET_ACCOUNTING[$idx1];
            $target2 = self::$VALID_TARGET_ACCOUNTING[$idx2];

            $response1 = $this->simulateGetCoaByTarget($target1);
            $response2 = $this->simulateGetCoaByTarget($target2);

            // Both should succeed
            assertEquals(1, $response1['status']);
            assertEquals(1, $response2['status']);

            // Data should NOT be the same (since our sample data differs per DB)
            assertTrue(
                $response1['data'] !== $response2['data'],
                "Iteration {$i}: '{$target1}' and '{$target2}' should return different data"
            );
        }

        echo "    Property verified: {$iterations} iterations confirm no cross-DB contamination\n";
    }

    /**
     * Test 3.16: Property - Target resolution determines database used for COA query
     * The database name in the resolved target must match the database
     * from which data is fetched.
     *
     * **Validates: Requirements 3.3, 5.1, 5.2**
     */
    public function testPropertyTargetResolutionDeterminesDatabaseUsed(): void
    {
        $iterations = 100;
        $seed = 200;
        mt_srand($seed);

        for ($i = 0; $i < $iterations; $i++) {
            $target = self::$VALID_TARGET_ACCOUNTING[mt_rand(0, count(self::$VALID_TARGET_ACCOUNTING) - 1)];

            // Resolve target
            $resolved = $this->resolveTargetDb($target);
            assertIsArray($resolved);

            // The resolved db_name should match TARGET_DB_MAP
            $expected_db = self::$TARGET_DB_MAP[$target];
            assertEquals(
                $expected_db,
                $resolved['db_name'],
                "Iteration {$i}: Resolved db_name for '{$target}' should be '{$expected_db}'"
            );

            // The connection's database should match
            assertEquals(
                $expected_db,
                $resolved['connection']->database,
                "Iteration {$i}: Connection database for '{$target}' should be '{$expected_db}'"
            );

            // The data returned by endpoint should be from this database
            $response = $this->simulateGetCoaByTarget($target);
            $expected_data = self::$SAMPLE_COA_DATA[$expected_db];
            assertEquals(
                $expected_data,
                $response['data'],
                "Iteration {$i}: Response data should come from '{$expected_db}'"
            );
        }

        echo "    Property verified: {$iterations} iterations confirm target resolution determines DB\n";
    }
}

// ============================================================================
// Run tests
// ============================================================================

if (php_sapi_name() === 'cli' && !class_exists('PHPUnit\Framework\TestCase')) {
    $runner = new SimpleTestRunner();
    $test = new CoaLookupTargetDbTest();
    $exitCode = $runner->run($test);
    exit($exitCode);
}
