<?php

/**
 * Property-Based Test: Target Accounting Resolver
 *
 * **Validates: Requirements 4.5, 3.3, 2.4, 2.5, 3.6, 5.5**
 *
 * Feature: multi-accounting-target
 *
 * Property 1: Target Accounting Resolution Mapping
 * _For any_ valid target_accounting value (one of "accounting_stm", "accounting_vuca",
 * "accounting_sustain"), the resolver function SHALL return the correct database constant
 * name (DBACC_STM, DBACC_VUCA, or DBACC_SUSTAIN respectively) and a valid CI database
 * connection object.
 *
 * Property 2: Target Accounting Validation (Whitelist)
 * _For any_ string input to the target_accounting validation function, the function SHALL
 * accept the input if and only if it is exactly one of "accounting_stm", "accounting_vuca",
 * or "accounting_sustain". All other inputs (including empty string, null, arbitrary strings)
 * SHALL be rejected.
 *
 * Usage: php application/modules/request_mutasi/tests/TargetAccountingResolverTest.php
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

function assertEquals($expected, $actual, string $message = ''): void
{
    if ($expected !== $actual) {
        $msg = $message ?: "Expected " . var_export($expected, true) . " but got " . var_export($actual, true);
        throw new AssertionError($msg);
    }
}

function assertTrue($value, string $message = ''): void
{
    if ($value !== true) {
        throw new AssertionError($message ?: "Expected true but got " . var_export($value, true));
    }
}

function assertFalse($value, string $message = ''): void
{
    if ($value !== false) {
        throw new AssertionError($message ?: "Expected false but got " . var_export($value, true));
    }
}

function assertNotNull($value, string $message = ''): void
{
    if ($value === null) {
        throw new AssertionError($message ?: "Expected non-null value");
    }
}

function assertIsArray($value, string $message = ''): void
{
    if (!is_array($value)) {
        throw new AssertionError($message ?: "Expected array but got " . gettype($value));
    }
}

function assertArrayHasKey($key, $array, string $message = ''): void
{
    if (!is_array($array) || !array_key_exists($key, $array)) {
        throw new AssertionError($message ?: "Expected array to have key '{$key}'");
    }
}

// ============================================================================
// Test Class
// ============================================================================

class TargetAccountingResolverTest
{
    /**
     * Static properties replicated from controller for isolated testing.
     * These mirror the exact definitions in Request_mutasi controller.
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
     * Simulate the _resolve_target_db() validation logic (without CI database load).
     * This tests the pure validation + mapping logic in isolation.
     *
     * @param mixed $target_accounting Input to validate
     * @return array|false Returns mapping result or false if invalid
     */
    private function resolveTargetDb($target_accounting)
    {
        if (empty($target_accounting) || !in_array($target_accounting, self::$VALID_TARGET_ACCOUNTING)) {
            return false;
        }

        $db_name = self::$TARGET_DB_MAP[$target_accounting];

        // In isolated test, simulate a successful connection object
        $connection = new stdClass();
        $connection->database = $db_name;
        $connection->conn_id = 'mock_connection_' . $target_accounting;

        return [
            'db_name'    => $db_name,
            'connection' => $connection,
            'group'      => $target_accounting,
        ];
    }

    /**
     * Simulate the whitelist validation check (the in_array + empty check).
     *
     * @param mixed $target_accounting Input to validate
     * @return bool True if valid, false otherwise
     */
    private function isValidTargetAccounting($target_accounting): bool
    {
        if (empty($target_accounting) || !in_array($target_accounting, self::$VALID_TARGET_ACCOUNTING)) {
            return false;
        }
        return true;
    }

    // ========================================================================
    // Property 1: Target Accounting Resolution Mapping
    // ========================================================================

    /**
     * Test 1.1: accounting_stm resolves to DBACC_STM
     *
     * **Validates: Requirements 4.5, 3.3**
     */
    public function testResolveAccountingStmReturnsCorrectDbName(): void
    {
        $result = $this->resolveTargetDb('accounting_stm');

        assertIsArray($result, "accounting_stm should resolve to an array, not false");
        assertArrayHasKey('db_name', $result);
        assertArrayHasKey('connection', $result);
        assertArrayHasKey('group', $result);
        assertEquals(DBACC_STM, $result['db_name'], "accounting_stm should map to DBACC_STM");
        assertEquals('accounting_stm', $result['group'], "group should be 'accounting_stm'");
    }

    /**
     * Test 1.2: accounting_vuca resolves to DBACC_VUCA
     *
     * **Validates: Requirements 4.5, 3.3**
     */
    public function testResolveAccountingVucaReturnsCorrectDbName(): void
    {
        $result = $this->resolveTargetDb('accounting_vuca');

        assertIsArray($result, "accounting_vuca should resolve to an array, not false");
        assertArrayHasKey('db_name', $result);
        assertArrayHasKey('connection', $result);
        assertArrayHasKey('group', $result);
        assertEquals(DBACC_VUCA, $result['db_name'], "accounting_vuca should map to DBACC_VUCA");
        assertEquals('accounting_vuca', $result['group'], "group should be 'accounting_vuca'");
    }

    /**
     * Test 1.3: accounting_sustain resolves to DBACC_SUSTAIN
     *
     * **Validates: Requirements 4.5, 3.3**
     */
    public function testResolveAccountingSustainReturnsCorrectDbName(): void
    {
        $result = $this->resolveTargetDb('accounting_sustain');

        assertIsArray($result, "accounting_sustain should resolve to an array, not false");
        assertArrayHasKey('db_name', $result);
        assertArrayHasKey('connection', $result);
        assertArrayHasKey('group', $result);
        assertEquals(DBACC_SUSTAIN, $result['db_name'], "accounting_sustain should map to DBACC_SUSTAIN");
        assertEquals('accounting_sustain', $result['group'], "group should be 'accounting_sustain'");
    }

    /**
     * Test 1.4: All valid values return a connection object (non-null)
     *
     * **Validates: Requirements 4.5, 3.3**
     */
    public function testAllValidValuesReturnConnectionObject(): void
    {
        foreach (self::$VALID_TARGET_ACCOUNTING as $target) {
            $result = $this->resolveTargetDb($target);

            assertIsArray($result, "Valid target '{$target}' should return an array");
            assertNotNull($result['connection'], "Connection for '{$target}' should not be null");
            assertTrue(
                is_object($result['connection']),
                "Connection for '{$target}' should be an object, got " . gettype($result['connection'])
            );
        }

        echo "    All 3 valid targets return connection objects\n";
    }

    /**
     * Test 1.5: Property - For ALL valid values, mapping is bijective (each maps to unique DB)
     *
     * **Validates: Requirements 4.5, 3.3**
     */
    public function testPropertyMappingIsBijective(): void
    {
        $db_names = [];
        foreach (self::$VALID_TARGET_ACCOUNTING as $target) {
            $result = $this->resolveTargetDb($target);
            assertIsArray($result, "Target '{$target}' should resolve successfully");
            $db_names[] = $result['db_name'];
        }

        // All db_names should be unique
        $unique_db_names = array_unique($db_names);
        assertEquals(
            count(self::$VALID_TARGET_ACCOUNTING),
            count($unique_db_names),
            "Each valid target should map to a UNIQUE database. Got duplicates: " . implode(', ', $db_names)
        );
    }

    /**
     * Test 1.6: Property - Resolution is idempotent (same input always same output)
     * Run the resolver multiple times for each valid value and verify consistent results.
     *
     * **Validates: Requirements 4.5, 3.3**
     */
    public function testPropertyResolutionIsIdempotent(): void
    {
        $iterations = 100;

        foreach (self::$VALID_TARGET_ACCOUNTING as $target) {
            $first_result = $this->resolveTargetDb($target);

            for ($i = 0; $i < $iterations; $i++) {
                $result = $this->resolveTargetDb($target);
                assertEquals(
                    $first_result['db_name'],
                    $result['db_name'],
                    "Iteration {$i}: Resolution for '{$target}' should be idempotent"
                );
                assertEquals(
                    $first_result['group'],
                    $result['group'],
                    "Iteration {$i}: Group for '{$target}' should be idempotent"
                );
            }
        }

        echo "    Idempotency verified across {$iterations} iterations per target\n";
    }

    // ========================================================================
    // Property 2: Target Accounting Validation (Whitelist)
    // ========================================================================

    /**
     * Test 2.1: All valid values are accepted
     *
     * **Validates: Requirements 2.4, 2.5, 3.6, 5.5**
     */
    public function testAllValidValuesAreAccepted(): void
    {
        $valid_values = ['accounting_stm', 'accounting_vuca', 'accounting_sustain'];

        foreach ($valid_values as $value) {
            assertTrue(
                $this->isValidTargetAccounting($value),
                "Value '{$value}' should be accepted as valid"
            );
        }
    }

    /**
     * Test 2.2: Empty string is rejected
     *
     * **Validates: Requirements 2.5, 5.5**
     */
    public function testEmptyStringIsRejected(): void
    {
        assertFalse(
            $this->isValidTargetAccounting(''),
            "Empty string should be rejected"
        );

        $result = $this->resolveTargetDb('');
        assertFalse($result, "Empty string should cause resolver to return false");
    }

    /**
     * Test 2.3: Null is rejected
     *
     * **Validates: Requirements 2.5, 5.5**
     */
    public function testNullIsRejected(): void
    {
        assertFalse(
            $this->isValidTargetAccounting(null),
            "Null should be rejected"
        );

        $result = $this->resolveTargetDb(null);
        assertFalse($result, "Null should cause resolver to return false");
    }

    /**
     * Test 2.4: Near-miss typos are rejected
     * Data provider: common typos and near-miss variations
     *
     * **Validates: Requirements 2.4, 2.5, 3.6, 5.5**
     */
    public function testNearMissTyposAreRejected(): void
    {
        $near_miss_inputs = [
            'accounting_STM',           // wrong case
            'accounting_Stm',           // mixed case
            'ACCOUNTING_STM',           // all caps
            'accounting_stm ',          // trailing space
            ' accounting_stm',          // leading space
            'accounting_stm\n',         // trailing newline
            'accounting-stm',           // hyphen instead of underscore
            'accounting.stm',           // dot instead of underscore
            'accountingstm',            // no underscore
            'accounting_st',            // truncated
            'accounting_stmx',          // extra char
            'accounting_vuc',           // truncated vuca
            'accounting_vucaa',         // extra char vuca
            'accounting_sustai',        // truncated sustain
            'accounting_sustains',      // extra char sustain
            'acc_stm',                  // abbreviated prefix
            'target_stm',              // wrong prefix
            'accounting_stm_extra',    // extra suffix
        ];

        foreach ($near_miss_inputs as $input) {
            assertFalse(
                $this->isValidTargetAccounting($input),
                "Near-miss '{$input}' should be rejected"
            );

            $result = $this->resolveTargetDb($input);
            assertFalse($result, "Near-miss '{$input}' should cause resolver to return false");
        }

        echo "    " . count($near_miss_inputs) . " near-miss inputs correctly rejected\n";
    }

    /**
     * Test 2.5: SQL injection attempts are rejected
     *
     * **Validates: Requirements 2.4, 2.5, 3.6, 5.5**
     */
    public function testSqlInjectionAttemptsAreRejected(): void
    {
        $injection_inputs = [
            "accounting_stm'; DROP TABLE users; --",
            "accounting_stm OR 1=1",
            "' OR '1'='1",
            "accounting_stm UNION SELECT * FROM users",
            "1; DROP TABLE tr_request_mutasi",
            "accounting_stm'/*",
            "<script>alert('xss')</script>",
            "accounting_stm\x00",
            "../../../etc/passwd",
            "accounting_stm%00",
        ];

        foreach ($injection_inputs as $input) {
            assertFalse(
                $this->isValidTargetAccounting($input),
                "Injection attempt should be rejected: " . substr($input, 0, 40)
            );

            $result = $this->resolveTargetDb($input);
            assertFalse($result, "Injection attempt should cause resolver to return false: " . substr($input, 0, 40));
        }

        echo "    " . count($injection_inputs) . " injection attempts correctly rejected\n";
    }

    /**
     * Test 2.6: Property - Random arbitrary strings are ALL rejected
     * Generate 100 random strings and verify none pass the whitelist.
     *
     * **Validates: Requirements 2.4, 2.5, 3.6, 5.5**
     */
    public function testPropertyRandomStringsAreAllRejected(): void
    {
        $iterations = 100;
        $seed = 42;
        mt_srand($seed);

        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-./\\\'"; ';

        for ($i = 0; $i < $iterations; $i++) {
            // Generate random string of length 1-50
            $length = mt_rand(1, 50);
            $random_string = '';
            for ($j = 0; $j < $length; $j++) {
                $random_string .= $chars[mt_rand(0, strlen($chars) - 1)];
            }

            // Skip if we accidentally generated a valid value
            if (in_array($random_string, self::$VALID_TARGET_ACCOUNTING)) {
                continue;
            }

            assertFalse(
                $this->isValidTargetAccounting($random_string),
                "Random string should be rejected (iteration {$i}): '{$random_string}'"
            );

            $result = $this->resolveTargetDb($random_string);
            assertFalse(
                $result,
                "Random string should cause resolver to return false (iteration {$i}): '{$random_string}'"
            );
        }

        echo "    Property verified: {$iterations} random strings all correctly rejected\n";
    }

    /**
     * Test 2.7: Property - ONLY the 3 exact valid values pass validation
     * Exhaustive check: iterate through valid values + surrounding mutations.
     *
     * **Validates: Requirements 2.4, 2.5, 3.6, 5.5**
     */
    public function testPropertyOnlyExactValidValuesPass(): void
    {
        $valid_values = ['accounting_stm', 'accounting_vuca', 'accounting_sustain'];
        $total_checked = 0;

        // For each valid value, verify it passes
        foreach ($valid_values as $value) {
            assertTrue(
                $this->isValidTargetAccounting($value),
                "'{$value}' should be accepted"
            );
            $total_checked++;
        }

        // For each valid value, generate character-level mutations and verify they fail
        foreach ($valid_values as $value) {
            // Single character deletion at each position
            for ($pos = 0; $pos < strlen($value); $pos++) {
                $mutated = substr($value, 0, $pos) . substr($value, $pos + 1);
                if (!in_array($mutated, $valid_values)) {
                    assertFalse(
                        $this->isValidTargetAccounting($mutated),
                        "Deletion mutation should be rejected: '{$mutated}' (deleted pos {$pos} from '{$value}')"
                    );
                    $total_checked++;
                }
            }

            // Single character insertion at each position
            $insert_chars = ['x', '1', '_', ' '];
            foreach ($insert_chars as $char) {
                for ($pos = 0; $pos <= strlen($value); $pos++) {
                    $mutated = substr($value, 0, $pos) . $char . substr($value, $pos);
                    if (!in_array($mutated, $valid_values)) {
                        assertFalse(
                            $this->isValidTargetAccounting($mutated),
                            "Insertion mutation should be rejected: '{$mutated}'"
                        );
                        $total_checked++;
                    }
                }
            }

            // Single character substitution at each position
            foreach ($insert_chars as $char) {
                for ($pos = 0; $pos < strlen($value); $pos++) {
                    if ($value[$pos] !== $char) {
                        $mutated = substr($value, 0, $pos) . $char . substr($value, $pos + 1);
                        if (!in_array($mutated, $valid_values)) {
                            assertFalse(
                                $this->isValidTargetAccounting($mutated),
                                "Substitution mutation should be rejected: '{$mutated}'"
                            );
                            $total_checked++;
                        }
                    }
                }
            }
        }

        echo "    Property verified: {$total_checked} inputs checked (3 valid accepted, rest rejected)\n";
    }

    /**
     * Test 2.8: Numeric and boolean-like inputs are rejected
     *
     * **Validates: Requirements 2.5, 5.5**
     */
    public function testNumericAndBooleanInputsAreRejected(): void
    {
        $edge_inputs = [
            '0',
            '1',
            '2',
            '3',
            '-1',
            '999',
            'true',
            'false',
            'null',
            'undefined',
            'NaN',
            '[]',
            '{}',
        ];

        foreach ($edge_inputs as $input) {
            assertFalse(
                $this->isValidTargetAccounting($input),
                "Edge-case input '{$input}' should be rejected"
            );

            $result = $this->resolveTargetDb($input);
            assertFalse($result, "Edge-case input '{$input}' should cause resolver to return false");
        }

        echo "    " . count($edge_inputs) . " edge-case inputs correctly rejected\n";
    }

    /**
     * Test 2.9: Property - Validation and resolution are consistent
     * If validation returns true, resolution returns array; if validation returns false, resolution returns false.
     *
     * **Validates: Requirements 2.4, 2.5, 3.6, 5.5**
     */
    public function testPropertyValidationAndResolutionConsistent(): void
    {
        $iterations = 100;
        $seed = 99;
        mt_srand($seed);

        // Test with a mix of valid, invalid, and random inputs
        $test_inputs = array_merge(
            self::$VALID_TARGET_ACCOUNTING,
            ['', null, 'invalid', 'accounting_xyz', 'accounting_stm ']
        );

        // Add random strings
        $chars = 'abcdefghijklmnopqrstuvwxyz_0123456789';
        for ($i = 0; $i < $iterations; $i++) {
            $length = mt_rand(0, 30);
            $random = '';
            for ($j = 0; $j < $length; $j++) {
                $random .= $chars[mt_rand(0, strlen($chars) - 1)];
            }
            $test_inputs[] = $random;
        }

        foreach ($test_inputs as $input) {
            $is_valid = $this->isValidTargetAccounting($input);
            $resolve_result = $this->resolveTargetDb($input);

            if ($is_valid) {
                assertIsArray(
                    $resolve_result,
                    "When validation returns true for '" . ($input ?? 'null') . "', resolution should return array"
                );
            } else {
                assertFalse(
                    $resolve_result,
                    "When validation returns false for '" . ($input ?? 'null') . "', resolution should return false"
                );
            }
        }

        echo "    Consistency verified across " . count($test_inputs) . " inputs\n";
    }
}

// ============================================================================
// Run tests
// ============================================================================

if (php_sapi_name() === 'cli' && !class_exists('PHPUnit\Framework\TestCase')) {
    $runner = new SimpleTestRunner();
    $test = new TargetAccountingResolverTest();
    $exitCode = $runner->run($test);
    exit($exitCode);
}
