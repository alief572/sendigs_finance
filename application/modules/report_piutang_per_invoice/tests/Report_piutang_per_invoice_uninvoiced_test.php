<?php

/**
 * Property-Based Test: Uninvoiced Calculation for Report Piutang Per Invoice
 *
 * Feature: report-piutang-per-invoice, Property 6: Uninvoiced calculation
 *
 * This test validates that the uninvoiced calculation correctly computes
 * the difference between nominal project and total invoice values:
 * uninvoiced = nominal_project - SUM(invoice_values)
 *
 * The uninvoiced value MAY be negative when total invoices exceed nominal project.
 * This is different from piutang_per_invoice which is clamped to 0.
 *
 * **Validates: Requirements 7.1, 7.5**
 *
 * Run: php application/modules/report_piutang_per_invoice/tests/Report_piutang_per_invoice_uninvoiced_test.php
 */

// ============================================================================
// Pure logic implementation (mirrors the controller uninvoiced calculation)
// ============================================================================

/**
 * Calculate the uninvoiced amount for an SPK.
 *
 * Formula: nominal_project - SUM(invoice_values)
 * This value CAN be negative (when total invoices exceed nominal project).
 * Unlike piutang_per_invoice, this is NOT clamped to 0.
 *
 * @param float $nominal_project The total nominal project value for the SPK
 * @param array $invoices Array of invoices, each with key 'value' (float)
 * @return float The uninvoiced amount (may be negative)
 */
function calculate_uninvoiced(float $nominal_project, array $invoices): float
{
    $total_invoiced = 0.0;
    foreach ($invoices as $invoice) {
        $total_invoiced += $invoice['value'];
    }

    return $nominal_project - $total_invoiced;
}

// ============================================================================
// Random data generators
// ============================================================================

/**
 * Generate a random nominal project value.
 * Range: 10,000,000 to 5,000,000,000 (10 million to 5 billion)
 */
function generateRandomNominalProject(): float
{
    return (float)(mt_rand(10000, 5000000) * 1000);
}

/**
 * Generate a random invoice value.
 * Range: 1,000,000 to max_amount
 *
 * @param float $max_amount Maximum invoice value
 * @return float Random invoice value
 */
function generateRandomInvoiceValue(float $max_amount = 500000000): float
{
    $max = max(1000, (int)($max_amount / 1000));
    return (float)(mt_rand(1000, $max) * 1000);
}

/**
 * Generate a random set of invoices for an SPK.
 *
 * @param int   $count     Number of invoices to generate
 * @param float $max_value Maximum value per invoice
 * @return array Array of invoices with 'value' key
 */
function generateRandomInvoices(int $count, float $max_value = 500000000): array
{
    $invoices = [];
    for ($i = 0; $i < $count; $i++) {
        $invoices[] = [
            'value' => generateRandomInvoiceValue($max_value),
        ];
    }
    return $invoices;
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
        if ($expected === $actual) {
            $this->passed++;
        } else {
            $this->failed++;
            $this->errors[] = "{$message} — expected " . var_export($expected, true) . ", got " . var_export($actual, true);
        }
    }

    public function assertTrue(bool $condition, string $message): void
    {
        $this->assert($condition, $message);
    }

    public function assertFloatEquals(float $expected, float $actual, string $message, float $epsilon = 0.01): void
    {
        $this->assert(
            abs($expected - $actual) < $epsilon,
            "{$message} — expected {$expected}, got {$actual} (diff: " . abs($expected - $actual) . ")"
        );
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
// Property 6: Uninvoiced calculation
// ============================================================================

/**
 * Main property: For any SPK with nominal project N and a set of invoices
 * with total value I, the uninvoiced amount SHALL equal N - I.
 * This value MAY be negative when I > N.
 */
function runProperty6_UninvoicedCalculation(PropertyTestRunner $runner, int $iterations): void
{
    echo "Running Property 6: Uninvoiced calculation ({$iterations} iterations)...\n";

    for ($i = 0; $i < $iterations; $i++) {
        // Generate random nominal project
        $nominal_project = generateRandomNominalProject();

        // Generate random number of invoices (0 to 10)
        $num_invoices = mt_rand(0, 10);
        $invoices = generateRandomInvoices($num_invoices, $nominal_project * 1.5);

        // Calculate expected uninvoiced manually
        $total_invoiced = 0.0;
        foreach ($invoices as $invoice) {
            $total_invoiced += $invoice['value'];
        }
        $expected_uninvoiced = $nominal_project - $total_invoiced;

        // Calculate using the function under test
        $actual_uninvoiced = calculate_uninvoiced($nominal_project, $invoices);

        // Verify: uninvoiced == nominal_project - sum(invoice_values)
        $runner->assertFloatEquals(
            $expected_uninvoiced,
            $actual_uninvoiced,
            "Iteration {$i}: Uninvoiced should be N - I (nominal={$nominal_project}, total_invoiced={$total_invoiced})"
        );
    }

    echo "  Completed {$iterations} iterations.\n";
}

/**
 * Sub-property: When no invoices exist, uninvoiced equals nominal_project.
 * Validates Requirement 7.4.
 */
function runProperty6a_NoInvoicesEqualsNominal(PropertyTestRunner $runner, int $iterations): void
{
    echo "Running Property 6a: No invoices means uninvoiced = nominal_project ({$iterations} iterations)...\n";

    for ($i = 0; $i < $iterations; $i++) {
        $nominal_project = generateRandomNominalProject();
        $invoices = []; // No invoices

        $uninvoiced = calculate_uninvoiced($nominal_project, $invoices);

        $runner->assertFloatEquals(
            $nominal_project,
            $uninvoiced,
            "Iteration {$i}: With no invoices, uninvoiced should equal nominal_project={$nominal_project}"
        );
    }

    echo "  Completed {$iterations} iterations.\n";
}

/**
 * Sub-property: When partial invoicing, uninvoiced = nominal_project - sum(invoices).
 * The result should be positive when total invoices < nominal.
 */
function runProperty6b_PartialInvoicing(PropertyTestRunner $runner, int $iterations): void
{
    echo "Running Property 6b: Partial invoicing gives positive uninvoiced ({$iterations} iterations)...\n";

    for ($i = 0; $i < $iterations; $i++) {
        $nominal_project = generateRandomNominalProject();

        // Generate invoices that sum to LESS than nominal_project
        $num_invoices = mt_rand(1, 5);
        $max_per_invoice = $nominal_project / ($num_invoices + 1); // Ensure sum < nominal
        $invoices = generateRandomInvoices($num_invoices, $max_per_invoice);

        $total_invoiced = 0.0;
        foreach ($invoices as $invoice) {
            $total_invoiced += $invoice['value'];
        }

        $uninvoiced = calculate_uninvoiced($nominal_project, $invoices);

        // Verify uninvoiced is positive
        $runner->assertTrue(
            $uninvoiced > 0,
            "Iteration {$i}: Partial invoicing should give positive uninvoiced " .
                "(nominal={$nominal_project}, total_invoiced={$total_invoiced}, uninvoiced={$uninvoiced})"
        );

        // Verify exact calculation
        $expected = $nominal_project - $total_invoiced;
        $runner->assertFloatEquals(
            $expected,
            $uninvoiced,
            "Iteration {$i}: Uninvoiced should be {$expected} (nominal={$nominal_project} - invoiced={$total_invoiced})"
        );
    }

    echo "  Completed {$iterations} iterations.\n";
}

/**
 * Sub-property: When fully invoiced (sum of invoices == nominal), uninvoiced = 0.
 * Validates Requirement 7.3.
 */
function runProperty6c_FullyInvoicedEqualsZero(PropertyTestRunner $runner, int $iterations): void
{
    echo "Running Property 6c: Fully invoiced means uninvoiced = 0 ({$iterations} iterations)...\n";

    for ($i = 0; $i < $iterations; $i++) {
        $nominal_project = generateRandomNominalProject();

        // Create invoices that sum exactly to nominal_project
        $num_invoices = mt_rand(1, 5);
        $invoices = [];
        $remaining = $nominal_project;

        for ($j = 0; $j < $num_invoices - 1; $j++) {
            // Each invoice takes a portion of the remaining amount
            $max_portion = $remaining / ($num_invoices - $j);
            $value = (float)(mt_rand(1000, max(1001, (int)($max_portion / 1000))) * 1000);
            $value = min($value, $remaining - (($num_invoices - $j - 1) * 1000000));
            if ($value <= 0) {
                $value = 1000000.0;
            }
            $invoices[] = ['value' => $value];
            $remaining -= $value;
        }
        // Last invoice takes the exact remainder
        $invoices[] = ['value' => $remaining];

        $uninvoiced = calculate_uninvoiced($nominal_project, $invoices);

        $runner->assertFloatEquals(
            0.0,
            $uninvoiced,
            "Iteration {$i}: Fully invoiced SPK should have uninvoiced=0 (nominal={$nominal_project})"
        );
    }

    echo "  Completed {$iterations} iterations.\n";
}

/**
 * Sub-property: When over-invoiced (sum > nominal), uninvoiced is negative.
 * This is explicitly allowed per spec (Requirement 7.5).
 * Unlike piutang_per_invoice which uses max(0, ...), uninvoiced CAN be negative.
 */
function runProperty6d_OverInvoicedIsNegative(PropertyTestRunner $runner, int $iterations): void
{
    echo "Running Property 6d: Over-invoiced gives negative uninvoiced ({$iterations} iterations)...\n";

    for ($i = 0; $i < $iterations; $i++) {
        $nominal_project = generateRandomNominalProject();

        // Create a single invoice that exceeds nominal_project
        $over_amount = (float)(mt_rand(1000, 50000) * 1000); // 1M to 50M extra
        $invoices = [
            ['value' => $nominal_project + $over_amount],
        ];

        $uninvoiced = calculate_uninvoiced($nominal_project, $invoices);

        // Verify uninvoiced is negative
        $runner->assertTrue(
            $uninvoiced < 0,
            "Iteration {$i}: Over-invoiced should give negative uninvoiced " .
                "(nominal={$nominal_project}, invoice=" . ($nominal_project + $over_amount) . ", uninvoiced={$uninvoiced})"
        );

        // Verify exact calculation: should be -over_amount
        $expected = -$over_amount;
        $runner->assertFloatEquals(
            $expected,
            $uninvoiced,
            "Iteration {$i}: Uninvoiced should be {$expected} (NOT clamped to 0)"
        );
    }

    echo "  Completed {$iterations} iterations.\n";
}

/**
 * Sub-property: Uninvoiced is NOT clamped to 0 (unlike piutang per invoice).
 * This distinguishes uninvoiced from piutang_per_invoice which uses max(0, ...).
 * Tests with multiple invoices that collectively exceed nominal.
 */
function runProperty6e_NotClampedToZero(PropertyTestRunner $runner, int $iterations): void
{
    echo "Running Property 6e: Uninvoiced is NOT clamped to zero ({$iterations} iterations)...\n";

    for ($i = 0; $i < $iterations; $i++) {
        // Create a scenario where multiple invoices exceed nominal
        $nominal_project = (float)(mt_rand(10000, 100000) * 1000); // 10M to 100M

        // Generate multiple invoices that collectively exceed nominal
        $num_invoices = mt_rand(2, 5);
        $invoices = [];
        $total_invoiced = 0.0;

        for ($j = 0; $j < $num_invoices; $j++) {
            // Each invoice is roughly nominal/num_invoices + some extra
            $value = ($nominal_project / $num_invoices) + (float)(mt_rand(1000, 10000) * 1000);
            $invoices[] = ['value' => $value];
            $total_invoiced += $value;
        }

        // Only test if we actually exceeded nominal
        if ($total_invoiced > $nominal_project) {
            $uninvoiced = calculate_uninvoiced($nominal_project, $invoices);

            // Verify: uninvoiced should be exactly N - I (negative value, not clamped)
            $expected = $nominal_project - $total_invoiced;
            $runner->assertFloatEquals(
                $expected,
                $uninvoiced,
                "Iteration {$i}: Uninvoiced should be {$expected}, NOT clamped to 0 " .
                    "(nominal={$nominal_project}, total_invoiced={$total_invoiced})"
            );

            // Explicitly verify it's negative
            $runner->assertTrue(
                $uninvoiced < 0,
                "Iteration {$i}: Uninvoiced must be negative when invoices exceed nominal"
            );
        }
    }

    echo "  Completed {$iterations} iterations.\n";
}

// ============================================================================
// Main execution
// ============================================================================

echo "========================================\n";
echo "Feature: report-piutang-per-invoice\n";
echo "Property 6: Uninvoiced calculation\n";
echo "Validates: Requirements 7.1, 7.5\n";
echo "========================================\n\n";

$runner = new PropertyTestRunner();

// Main property: 100 iterations
runProperty6_UninvoicedCalculation($runner, 100);

// Sub-properties
runProperty6a_NoInvoicesEqualsNominal($runner, 100);
runProperty6b_PartialInvoicing($runner, 100);
runProperty6c_FullyInvoicedEqualsZero($runner, 100);
runProperty6d_OverInvoicedIsNegative($runner, 100);
runProperty6e_NotClampedToZero($runner, 100);

$success = $runner->printResults();

exit($success ? 0 : 1);
