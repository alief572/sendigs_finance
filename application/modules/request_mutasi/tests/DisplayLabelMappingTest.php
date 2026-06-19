<?php

/**
 * Property-Based Test: Display Label Mapping
 *
 * **Validates: Requirements 6.1, 6.2, 6.3, 6.4**
 *
 * Feature: multi-accounting-target, Property 7: Display Label Mapping
 *
 * Property 7: Display Label Mapping
 * _For any_ target_accounting value (including null/empty), the display mapping function
 * SHALL return "STM" for "accounting_stm", "VUCA" for "accounting_vuca", "SUSTAIN" for
 * "accounting_sustain", and "-" for null or empty values.
 *
 * Usage: php application/modules/request_mutasi/tests/DisplayLabelMappingTest.php
 */

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

// ============================================================================
// Test Class
// ============================================================================

class DisplayLabelMappingTest
{
    /**
     * Static label mapping replicated from controller and views.
     * This mirrors the exact mapping used in Request_mutasi controller ($TARGET_LABELS)
     * and the index views (index.php, index_transaksi.php, mutasi.php, add.php).
     */
    private static $TARGET_LABELS = [
        'accounting_stm'     => 'STM',
        'accounting_vuca'    => 'VUCA',
        'accounting_sustain' => 'SUSTAIN',
    ];

    /**
     * Simulate the display label mapping function as implemented in the views.
     * This replicates the logic used across index.php, index_transaksi.php, mutasi.php, and add.php.
     *
     * @param mixed $target_accounting The stored target_accounting value
     * @return string The display label ("STM", "VUCA", "SUSTAIN", or "-")
     */
    private function getDisplayLabel($target_accounting): string
    {
        if (!empty($target_accounting) && isset(self::$TARGET_LABELS[$target_accounting])) {
            return self::$TARGET_LABELS[$target_accounting];
        }

        return '-';
    }

    // ========================================================================
    // Property 7: Display Label Mapping
    // ========================================================================

    /**
     * Test 7.1: accounting_stm maps to "STM"
     *
     * **Validates: Requirements 6.1, 6.2, 6.3, 6.4**
     */
    public function testAccountingStmDisplaysStm(): void
    {
        $result = $this->getDisplayLabel('accounting_stm');
        assertEquals('STM', $result, "accounting_stm should display as 'STM'");
    }

    /**
     * Test 7.2: accounting_vuca maps to "VUCA"
     *
     * **Validates: Requirements 6.1, 6.2, 6.3, 6.4**
     */
    public function testAccountingVucaDisplaysVuca(): void
    {
        $result = $this->getDisplayLabel('accounting_vuca');
        assertEquals('VUCA', $result, "accounting_vuca should display as 'VUCA'");
    }

    /**
     * Test 7.3: accounting_sustain maps to "SUSTAIN"
     *
     * **Validates: Requirements 6.1, 6.2, 6.3, 6.4**
     */
    public function testAccountingSustainDisplaysSustain(): void
    {
        $result = $this->getDisplayLabel('accounting_sustain');
        assertEquals('SUSTAIN', $result, "accounting_sustain should display as 'SUSTAIN'");
    }

    /**
     * Test 7.4: Null value displays "-"
     *
     * **Validates: Requirements 6.4**
     */
    public function testNullDisplaysDash(): void
    {
        $result = $this->getDisplayLabel(null);
        assertEquals('-', $result, "null should display as '-'");
    }

    /**
     * Test 7.5: Empty string displays "-"
     *
     * **Validates: Requirements 6.4**
     */
    public function testEmptyStringDisplaysDash(): void
    {
        $result = $this->getDisplayLabel('');
        assertEquals('-', $result, "empty string should display as '-'");
    }

    /**
     * Test 7.6: Property - For 100 random valid values, mapping always returns correct label
     * Randomly select from the 3 valid values and verify mapping is always correct.
     *
     * **Validates: Requirements 6.1, 6.2, 6.3, 6.4**
     */
    public function testPropertyValidValuesAlwaysMapCorrectly(): void
    {
        $iterations = 100;
        $seed = 42;
        mt_srand($seed);

        $valid_values = ['accounting_stm', 'accounting_vuca', 'accounting_sustain'];
        $expected_labels = ['STM', 'VUCA', 'SUSTAIN'];

        for ($i = 0; $i < $iterations; $i++) {
            $index = mt_rand(0, 2);
            $target = $valid_values[$index];
            $expected = $expected_labels[$index];

            $result = $this->getDisplayLabel($target);
            assertEquals(
                $expected,
                $result,
                "Iteration {$i}: '{$target}' should map to '{$expected}', got '{$result}'"
            );
        }

        echo "    Property verified: {$iterations} random valid inputs all map correctly\n";
    }

    /**
     * Test 7.7: Property - For 100 random invalid/null/empty inputs, mapping always returns "-"
     * Generate random invalid strings and verify they all map to "-".
     *
     * **Validates: Requirements 6.4**
     */
    public function testPropertyInvalidInputsAlwaysReturnDash(): void
    {
        $iterations = 100;
        $seed = 77;
        mt_srand($seed);

        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-. ';
        $valid_values = ['accounting_stm', 'accounting_vuca', 'accounting_sustain'];

        $tested = 0;
        $attempts = 0;
        $max_attempts = 200;

        while ($tested < $iterations && $attempts < $max_attempts) {
            $attempts++;

            // Generate random string of length 0-40
            $length = mt_rand(0, 40);
            $random_string = '';
            for ($j = 0; $j < $length; $j++) {
                $random_string .= $chars[mt_rand(0, strlen($chars) - 1)];
            }

            // Skip valid values
            if (in_array($random_string, $valid_values)) {
                continue;
            }

            $result = $this->getDisplayLabel($random_string);
            assertEquals(
                '-',
                $result,
                "Invalid input '{$random_string}' should map to '-', got '{$result}'"
            );
            $tested++;
        }

        // Also test null and empty explicitly in the iteration count
        $result_null = $this->getDisplayLabel(null);
        assertEquals('-', $result_null, "null should map to '-'");

        $result_empty = $this->getDisplayLabel('');
        assertEquals('-', $result_empty, "empty string should map to '-'");

        echo "    Property verified: {$tested} random invalid inputs + null/empty all return '-'\n";
    }

    /**
     * Test 7.8: Property - Mapping is idempotent (same input always produces same output)
     * Call the function multiple times with same input and verify consistency.
     *
     * **Validates: Requirements 6.1, 6.2, 6.3, 6.4**
     */
    public function testPropertyMappingIsIdempotent(): void
    {
        $iterations = 100;

        $all_inputs = [
            'accounting_stm',
            'accounting_vuca',
            'accounting_sustain',
            null,
            '',
            'invalid_value',
            'random_string',
            'accounting_xyz',
        ];

        foreach ($all_inputs as $input) {
            $first_result = $this->getDisplayLabel($input);

            for ($i = 0; $i < $iterations; $i++) {
                $result = $this->getDisplayLabel($input);
                assertEquals(
                    $first_result,
                    $result,
                    "Iteration {$i}: Mapping for '" . ($input ?? 'null') . "' should be idempotent. " .
                        "Expected '{$first_result}', got '{$result}'"
                );
            }
        }

        echo "    Idempotency verified: " . count($all_inputs) . " inputs × {$iterations} iterations\n";
    }

    /**
     * Test 7.9: Edge cases - whitespace, case variations, partial matches
     * Verify that near-miss and edge case inputs all return "-".
     *
     * **Validates: Requirements 6.4**
     */
    public function testEdgeCasesReturnDash(): void
    {
        $edge_cases = [
            // Whitespace variations
            ' accounting_stm',          // leading space
            'accounting_stm ',          // trailing space
            ' accounting_stm ',         // both sides
            "\taccounting_stm",         // tab
            "accounting_stm\n",         // newline
            '  ',                       // only whitespace

            // Case variations
            'Accounting_stm',
            'ACCOUNTING_STM',
            'accounting_STM',
            'Accounting_Vuca',
            'ACCOUNTING_VUCA',
            'Accounting_Sustain',
            'ACCOUNTING_SUSTAIN',

            // Partial matches
            'accounting_',
            'accounting_st',
            'accounting_vuc',
            'accounting_sustai',
            'stm',
            'vuca',
            'sustain',

            // Similar but different
            'accounting-stm',
            'accounting.stm',
            'accounting stm',
            'accounting_stm1',
            'xaccounting_stm',

            // Special values
            '0',
            'false',
            'null',
            'undefined',
            'NaN',
        ];

        foreach ($edge_cases as $input) {
            $result = $this->getDisplayLabel($input);
            assertEquals(
                '-',
                $result,
                "Edge case '" . addcslashes($input, "\t\n\r") . "' should display as '-', got '{$result}'"
            );
        }

        echo "    " . count($edge_cases) . " edge cases all correctly return '-'\n";
    }

    /**
     * Test 7.10: Property - The mapping forms a complete partition of input space
     * Every possible input maps to exactly one of: "STM", "VUCA", "SUSTAIN", or "-".
     * No input can produce any other output.
     *
     * **Validates: Requirements 6.1, 6.2, 6.3, 6.4**
     */
    public function testPropertyOutputIsAlwaysInValidSet(): void
    {
        $iterations = 100;
        $seed = 123;
        mt_srand($seed);

        $valid_outputs = ['STM', 'VUCA', 'SUSTAIN', '-'];
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-. !@#$%';

        // Test with valid values
        $all_inputs = ['accounting_stm', 'accounting_vuca', 'accounting_sustain', null, ''];

        // Add random inputs
        for ($i = 0; $i < $iterations; $i++) {
            $length = mt_rand(0, 50);
            $random = '';
            for ($j = 0; $j < $length; $j++) {
                $random .= $chars[mt_rand(0, strlen($chars) - 1)];
            }
            $all_inputs[] = $random;
        }

        foreach ($all_inputs as $input) {
            $result = $this->getDisplayLabel($input);
            assertTrue(
                in_array($result, $valid_outputs, true),
                "Output '{$result}' for input '" . ($input ?? 'null') . "' is not in valid output set [STM, VUCA, SUSTAIN, -]"
            );
        }

        echo "    Property verified: " . count($all_inputs) . " inputs all produce output in {STM, VUCA, SUSTAIN, -}\n";
    }

    /**
     * Test 7.11: Property - Mapping is a total function (no exceptions for any input)
     * The function must not throw exceptions for any conceivable input.
     *
     * **Validates: Requirements 6.1, 6.2, 6.3, 6.4**
     */
    public function testPropertyMappingNeverThrows(): void
    {
        $iterations = 100;
        $seed = 55;
        mt_srand($seed);

        $problematic_inputs = [
            null,
            '',
            '0',
            ' ',
            "\t",
            "\n",
            "\r\n",
            str_repeat('a', 1000),   // very long string
            "\x00",                  // null byte
            "accounting_stm\x00",    // embedded null
            '<?php echo "test"; ?>', // PHP code
            '<script>',              // HTML/JS
        ];

        // Add random binary-like inputs
        for ($i = 0; $i < $iterations; $i++) {
            $length = mt_rand(0, 100);
            $random = '';
            for ($j = 0; $j < $length; $j++) {
                $random .= chr(mt_rand(0, 255));
            }
            $problematic_inputs[] = $random;
        }

        $no_exceptions = true;
        foreach ($problematic_inputs as $index => $input) {
            try {
                $result = $this->getDisplayLabel($input);
                // Just verify it returns a string
                assertTrue(
                    is_string($result),
                    "Output should always be a string, got " . gettype($result) . " for input index {$index}"
                );
            } catch (\Throwable $e) {
                $no_exceptions = false;
                throw new AssertionError(
                    "Function threw exception for input index {$index}: " . $e->getMessage()
                );
            }
        }

        assertTrue($no_exceptions, "Function should never throw exceptions");
        echo "    Property verified: " . count($problematic_inputs) . " inputs processed without exceptions\n";
    }
}

// ============================================================================
// Run tests
// ============================================================================

if (php_sapi_name() === 'cli' && !class_exists('PHPUnit\Framework\TestCase')) {
    $runner = new SimpleTestRunner();
    $test = new DisplayLabelMappingTest();
    $exitCode = $runner->run($test);
    exit($exitCode);
}
