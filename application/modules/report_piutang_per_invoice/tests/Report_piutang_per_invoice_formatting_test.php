<?php

/**
 * Property-Based Test: Number Formatting for Report Piutang Per Invoice
 *
 * Feature: report-piutang-per-invoice, Property 9: Number formatting
 *
 * This test validates that for any numeric value, the formatted output SHALL use
 * dot (.) as thousands separator with no decimal places and no "Rp" prefix.
 * For negative values, a minus sign SHALL precede the formatted absolute value.
 * For zero values in formula fields, a dash (-) SHALL be displayed.
 *
 * **Validates: Requirements 11.1, 11.3, 11.5**
 *
 * Run: php application/modules/report_piutang_per_invoice/tests/Report_piutang_per_invoice_formatting_test.php
 */

// ============================================================================
// Pure logic implementations (mirrors the JavaScript formatting functions)
// ============================================================================

/**
 * Format a numeric value with dot as thousands separator, no decimals, no "Rp" prefix.
 * Mirrors JavaScript formatNumber() in views/index.php
 *
 * Examples: 1500000 → "1.500.000", -500000 → "-500.000", 0 → "0"
 *
 * @param float|int|string $value Numeric value to format
 * @return string Formatted number string
 */
function format_number_value($value): string
{
    $num = floatval($value);
    if (is_nan($num)) {
        return '0';
    }

    $is_negative = $num < 0;
    $abs_num = abs(round($num));
    $formatted = number_format($abs_num, 0, '', '.');

    return $is_negative ? '-' . $formatted : $formatted;
}

/**
 * Format a formula field value (Piutang Per Invoice, Uninvoiced, Total Sisa Piutang).
 * Zero values display as "-", non-zero values are formatted with thousands separator.
 * Mirrors JavaScript formatFormulaField() in views/index.php
 *
 * @param float|int|string $value Numeric value for formula field
 * @return string Formatted value or "-" for zero
 */
function format_formula_field($value): string
{
    $num = floatval($value);
    if ($num == 0) {
        return '-';
    }

    return format_number_value($num);
}

/**
 * Format a data field value (Nilai Invoice, Nilai Bayar).
 * Zero values display as "0", non-zero values are formatted with thousands separator.
 * Mirrors JavaScript formatDataField() in views/index.php
 *
 * @param float|int|string $value Numeric value for data field
 * @return string Formatted value or "0" for zero
 */
function format_data_field($value): string
{
    $num = floatval($value);
    if ($num == 0) {
        return '0';
    }

    return format_number_value($num);
}

// ============================================================================
// Test runner
// ============================================================================

class PropertyTestRunner
{
    private $passed = 0;
    private $failed = 0;
    private $errors = [];

    public function assert(bool $condition, string $message): void
    {
        if ($condition) {
            $this->passed++;
        } else {
            $this->failed++;
            $this->errors[] = $message;
        }
    }

    public function assertEquals($expected, $actual, string $message): void
    {
        $this->assert(
            $expected === $actual,
            "{$message} — expected \"{$expected}\", got \"{$actual}\""
        );
    }

    public function assertTrue(bool $condition, string $message): void
    {
        $this->assert($condition, $message);
    }

    public function printResults(): bool
    {
        echo "\n";
        echo "========================================\n";
        echo "Property Test Results\n";
        echo "========================================\n";
        echo "Assertions passed: {$this->passed}\n";
        echo "Assertions failed: {$this->failed}\n";

        if (!empty($this->errors)) {
            echo "\nFAILURES:\n";
            foreach (array_slice($this->errors, 0, 20) as $i => $error) {
                echo "  " . ($i + 1) . ") {$error}\n";
            }
            if (count($this->errors) > 20) {
                echo "  ... and " . (count($this->errors) - 20) . " more failures\n";
            }
        }

        echo "========================================\n";

        if ($this->failed === 0) {
            echo "ALL TESTS PASSED\n";
            return true;
        } else {
            echo "TESTS FAILED\n";
            return false;
        }
    }
}

// ============================================================================
// Helper: Verify dot separator pattern
// ============================================================================

/**
 * Verify that a formatted number string uses dot as thousands separator correctly.
 * Rules:
 * - Digits are grouped in sets of 3 from the right
 * - Groups are separated by dots
 * - No decimal places
 * - No "Rp" prefix
 *
 * @param string $formatted The formatted string (without minus sign)
 * @return bool True if format is valid
 */
function verify_dot_separator(string $formatted): bool
{
    // Must not be empty
    if ($formatted === '') {
        return false;
    }

    // Must not contain "Rp"
    if (strpos($formatted, 'Rp') !== false) {
        return false;
    }

    // Must not contain comma (decimal separator)
    if (strpos($formatted, ',') !== false) {
        return false;
    }

    // Split by dots
    $parts = explode('.', $formatted);

    // First group can be 1-3 digits
    if (!preg_match('/^\d{1,3}$/', $parts[0])) {
        return false;
    }

    // Remaining groups must be exactly 3 digits
    for ($i = 1; $i < count($parts); $i++) {
        if (!preg_match('/^\d{3}$/', $parts[$i])) {
            return false;
        }
    }

    return true;
}

// ============================================================================
// Property 9: Number formatting tests
// ============================================================================

/**
 * Main property: For any positive number, format_number_value SHALL produce
 * a string with dot thousands separator, no decimal, no "Rp" prefix.
 */
function runProperty9a_PositiveNumbers(PropertyTestRunner $runner, int $iterations): void
{
    echo "Running Property 9a: Positive number formatting ({$iterations} iterations)...\n";

    for ($i = 0; $i < $iterations; $i++) {
        // Generate random positive number (1 to 999,999,999,999)
        $value = mt_rand(1, 999999999);
        // Occasionally generate very large numbers
        if ($i % 10 === 0) {
            $value = mt_rand(1000000000, 999999999999);
        }

        $formatted = format_number_value($value);

        // Must not contain "Rp" prefix
        $runner->assertTrue(
            strpos($formatted, 'Rp') === false,
            "Iteration {$i}: value={$value}, formatted=\"{$formatted}\" should not contain 'Rp'"
        );

        // Must not contain comma
        $runner->assertTrue(
            strpos($formatted, ',') === false,
            "Iteration {$i}: value={$value}, formatted=\"{$formatted}\" should not contain comma"
        );

        // Must not contain decimal point as decimal separator (all dots are thousands separators)
        // Verify the dot separator pattern is correct
        $runner->assertTrue(
            verify_dot_separator($formatted),
            "Iteration {$i}: value={$value}, formatted=\"{$formatted}\" should have valid dot separator pattern"
        );

        // Must not have minus sign for positive numbers
        $runner->assertTrue(
            $formatted[0] !== '-',
            "Iteration {$i}: value={$value}, formatted=\"{$formatted}\" should not start with minus"
        );

        // Removing dots should give us the original number
        $stripped = str_replace('.', '', $formatted);
        $runner->assertEquals(
            (string) $value,
            $stripped,
            "Iteration {$i}: value={$value}, stripped=\"{$stripped}\" should equal original number"
        );
    }

    echo "  Completed {$iterations} iterations.\n";
}

/**
 * Property: For any negative number, format_number_value SHALL produce
 * a minus sign followed by the formatted absolute value.
 */
function runProperty9b_NegativeNumbers(PropertyTestRunner $runner, int $iterations): void
{
    echo "Running Property 9b: Negative number formatting ({$iterations} iterations)...\n";

    for ($i = 0; $i < $iterations; $i++) {
        // Generate random negative number (-999,999,999,999 to -1)
        $abs_value = mt_rand(1, 999999999);
        if ($i % 10 === 0) {
            $abs_value = mt_rand(1000000000, 999999999999);
        }
        $value = -$abs_value;

        $formatted = format_number_value($value);

        // Must start with minus sign
        $runner->assertTrue(
            strlen($formatted) > 0 && $formatted[0] === '-',
            "Iteration {$i}: value={$value}, formatted=\"{$formatted}\" should start with minus sign"
        );

        // After removing minus sign, the rest should be valid formatted number
        $without_minus = substr($formatted, 1);
        $runner->assertTrue(
            verify_dot_separator($without_minus),
            "Iteration {$i}: value={$value}, without_minus=\"{$without_minus}\" should have valid dot separator pattern"
        );

        // Must not contain "Rp" prefix
        $runner->assertTrue(
            strpos($formatted, 'Rp') === false,
            "Iteration {$i}: value={$value}, formatted=\"{$formatted}\" should not contain 'Rp'"
        );

        // Removing minus and dots should give us the absolute value
        $stripped = str_replace('.', '', $without_minus);
        $runner->assertEquals(
            (string) $abs_value,
            $stripped,
            "Iteration {$i}: value={$value}, stripped=\"{$stripped}\" should equal absolute value {$abs_value}"
        );
    }

    echo "  Completed {$iterations} iterations.\n";
}

/**
 * Property: For zero value in formula fields, format_formula_field SHALL return "-".
 */
function runProperty9c_ZeroFormulaField(PropertyTestRunner $runner, int $iterations): void
{
    echo "Running Property 9c: Zero in formula fields shows dash ({$iterations} iterations)...\n";

    for ($i = 0; $i < $iterations; $i++) {
        // Test zero in various representations
        $zero_values = [0, 0.0, '0', '0.0', '0.00', -0, -0.0];
        $zero_val = $zero_values[$i % count($zero_values)];

        $formatted = format_formula_field($zero_val);

        $runner->assertEquals(
            '-',
            $formatted,
            "Iteration {$i}: zero value ({$zero_val}) in formula field should display as '-'"
        );
    }

    echo "  Completed {$iterations} iterations.\n";
}

/**
 * Property: For zero value in data fields, format_data_field SHALL return "0".
 */
function runProperty9d_ZeroDataField(PropertyTestRunner $runner, int $iterations): void
{
    echo "Running Property 9d: Zero in data fields shows '0' ({$iterations} iterations)...\n";

    for ($i = 0; $i < $iterations; $i++) {
        // Test zero in various representations
        $zero_values = [0, 0.0, '0', '0.0', '0.00', -0, -0.0];
        $zero_val = $zero_values[$i % count($zero_values)];

        $formatted = format_data_field($zero_val);

        $runner->assertEquals(
            '0',
            $formatted,
            "Iteration {$i}: zero value ({$zero_val}) in data field should display as '0'"
        );
    }

    echo "  Completed {$iterations} iterations.\n";
}

/**
 * Property: For non-zero values, formula fields and data fields should produce
 * the same formatted output as format_number_value.
 */
function runProperty9e_NonZeroFieldsConsistency(PropertyTestRunner $runner, int $iterations): void
{
    echo "Running Property 9e: Non-zero fields use same formatting as format_number_value ({$iterations} iterations)...\n";

    for ($i = 0; $i < $iterations; $i++) {
        // Generate random non-zero number
        $value = mt_rand(1, 999999999);
        if ($i % 3 === 0) {
            $value = -$value; // Make some negative
        }

        $expected = format_number_value($value);
        $formula_result = format_formula_field($value);
        $data_result = format_data_field($value);

        $runner->assertEquals(
            $expected,
            $formula_result,
            "Iteration {$i}: value={$value}, formula field should match format_number_value"
        );

        $runner->assertEquals(
            $expected,
            $data_result,
            "Iteration {$i}: value={$value}, data field should match format_number_value"
        );
    }

    echo "  Completed {$iterations} iterations.\n";
}

/**
 * Edge cases: Specific known values to verify formatting correctness.
 */
function runProperty9f_EdgeCases(PropertyTestRunner $runner): void
{
    echo "Running Property 9f: Edge cases for number formatting...\n";

    // Edge case: 0
    $runner->assertEquals('0', format_number_value(0), "format_number_value(0) should be '0'");

    // Edge case: 1
    $runner->assertEquals('1', format_number_value(1), "format_number_value(1) should be '1'");

    // Edge case: 999 (no separator needed)
    $runner->assertEquals('999', format_number_value(999), "format_number_value(999) should be '999'");

    // Edge case: 1000 (first separator)
    $runner->assertEquals('1.000', format_number_value(1000), "format_number_value(1000) should be '1.000'");

    // Edge case: 1000000
    $runner->assertEquals('1.000.000', format_number_value(1000000), "format_number_value(1000000) should be '1.000.000'");

    // Edge case: 1500000
    $runner->assertEquals('1.500.000', format_number_value(1500000), "format_number_value(1500000) should be '1.500.000'");

    // Edge case: very large number
    $runner->assertEquals('999.999.999.999', format_number_value(999999999999), "format_number_value(999999999999) should be '999.999.999.999'");

    // Edge case: negative
    $runner->assertEquals('-500.000', format_number_value(-500000), "format_number_value(-500000) should be '-500.000'");

    // Edge case: negative 1
    $runner->assertEquals('-1', format_number_value(-1), "format_number_value(-1) should be '-1'");

    // Edge case: negative large
    $runner->assertEquals('-1.000.000.000', format_number_value(-1000000000), "format_number_value(-1000000000) should be '-1.000.000.000'");

    // Formula field: zero → "-"
    $runner->assertEquals('-', format_formula_field(0), "format_formula_field(0) should be '-'");

    // Formula field: non-zero → formatted
    $runner->assertEquals('1.500.000', format_formula_field(1500000), "format_formula_field(1500000) should be '1.500.000'");

    // Formula field: negative → formatted with minus
    $runner->assertEquals('-500.000', format_formula_field(-500000), "format_formula_field(-500000) should be '-500.000'");

    // Data field: zero → "0"
    $runner->assertEquals('0', format_data_field(0), "format_data_field(0) should be '0'");

    // Data field: non-zero → formatted
    $runner->assertEquals('1.500.000', format_data_field(1500000), "format_data_field(1500000) should be '1.500.000'");

    // Data field: negative → formatted with minus
    $runner->assertEquals('-500.000', format_data_field(-500000), "format_data_field(-500000) should be '-500.000'");

    echo "  Completed edge cases.\n";
}

/**
 * Property: No "Rp" prefix in any formatted output regardless of input value.
 */
function runProperty9g_NoRpPrefix(PropertyTestRunner $runner, int $iterations): void
{
    echo "Running Property 9g: No 'Rp' prefix in any output ({$iterations} iterations)...\n";

    for ($i = 0; $i < $iterations; $i++) {
        // Generate random value (positive, negative, zero)
        $rand_type = mt_rand(0, 2);
        if ($rand_type === 0) {
            $value = 0;
        } elseif ($rand_type === 1) {
            $value = mt_rand(1, 999999999999);
        } else {
            $value = -mt_rand(1, 999999999999);
        }

        $formatted_number = format_number_value($value);
        $formatted_formula = format_formula_field($value);
        $formatted_data = format_data_field($value);

        $runner->assertTrue(
            strpos($formatted_number, 'Rp') === false,
            "Iteration {$i}: value={$value}, format_number_value should not contain 'Rp'"
        );

        $runner->assertTrue(
            strpos($formatted_formula, 'Rp') === false,
            "Iteration {$i}: value={$value}, format_formula_field should not contain 'Rp'"
        );

        $runner->assertTrue(
            strpos($formatted_data, 'Rp') === false,
            "Iteration {$i}: value={$value}, format_data_field should not contain 'Rp'"
        );
    }

    echo "  Completed {$iterations} iterations.\n";
}

/**
 * Property: No decimal places in any formatted output.
 * After removing the minus sign and dots (thousands separators), the result should be all digits.
 */
function runProperty9h_NoDecimalPlaces(PropertyTestRunner $runner, int $iterations): void
{
    echo "Running Property 9h: No decimal places in output ({$iterations} iterations)...\n";

    for ($i = 0; $i < $iterations; $i++) {
        // Generate random value with decimal part
        $int_part = mt_rand(1, 999999999);
        $decimal_part = mt_rand(1, 99) / 100;
        $value = $int_part + $decimal_part;

        if ($i % 3 === 0) {
            $value = -$value;
        }

        $formatted = format_number_value($value);

        // Remove minus sign if present
        $check_str = $formatted;
        if (strlen($check_str) > 0 && $check_str[0] === '-') {
            $check_str = substr($check_str, 1);
        }

        // After removing dots (thousands separators), should be all digits
        $stripped = str_replace('.', '', $check_str);
        $runner->assertTrue(
            ctype_digit($stripped),
            "Iteration {$i}: value={$value}, stripped=\"{$stripped}\" should be all digits (no decimals)"
        );

        // The numeric value of stripped should equal the rounded absolute value
        $expected_abs = abs(round($value));
        $runner->assertEquals(
            (string) (int) $expected_abs,
            $stripped,
            "Iteration {$i}: value={$value}, stripped should equal rounded absolute value"
        );
    }

    echo "  Completed {$iterations} iterations.\n";
}

// ============================================================================
// Main execution
// ============================================================================

echo "========================================\n";
echo "Feature: report-piutang-per-invoice\n";
echo "Property 9: Number formatting\n";
echo "Validates: Requirements 11.1, 11.3, 11.5\n";
echo "========================================\n\n";

$runner = new PropertyTestRunner();

// Main properties: 100+ iterations each
runProperty9a_PositiveNumbers($runner, 100);
runProperty9b_NegativeNumbers($runner, 100);
runProperty9c_ZeroFormulaField($runner, 100);
runProperty9d_ZeroDataField($runner, 100);
runProperty9e_NonZeroFieldsConsistency($runner, 100);

// Edge cases
runProperty9f_EdgeCases($runner);

// Additional properties
runProperty9g_NoRpPrefix($runner, 100);
runProperty9h_NoDecimalPlaces($runner, 100);

$success = $runner->printResults();

exit($success ? 0 : 1);
