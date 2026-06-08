<?php

/**
 * Property-Based Test: Invoice Progress Calculation for Report Piutang Per Invoice
 *
 * Feature: report-piutang-per-invoice, Property 4: Invoice progress calculation
 *
 * This test validates that for any SPK with N total TOP records and M invoiced TOPs
 * (where M ≤ N), the displayed total TOP count SHALL equal N, the invoiced count
 * SHALL equal M, and the pending count SHALL equal N - M.
 *
 * **Validates: Requirements 5.1, 5.2, 5.3**
 *
 * Run: php application/modules/report_piutang_per_invoice/tests/Report_piutang_per_invoice_invoice_progress_test.php
 */

// ============================================================================
// Pure logic implementation (mirrors the controller's invoice progress logic)
// ============================================================================

/**
 * Calculate invoice progress for an SPK given its array of TOPs.
 *
 * Each TOP is an associative array with at minimum a key 'has_invoice' (bool)
 * indicating whether an invoice has been created for that TOP.
 *
 * Returns [total_top, invoiced_top, pending_top]
 *
 * @param array $tops Array of TOP records, each with 'has_invoice' boolean
 * @return array [total_top, invoiced_top, pending_top]
 */
function calculate_invoice_progress(array $tops): array
{
    $total_top = count($tops);
    $invoiced_top = 0;

    foreach ($tops as $top) {
        if (!empty($top['has_invoice'])) {
            $invoiced_top++;
        }
    }

    $pending_top = $total_top - $invoiced_top;

    return [
        'total_top' => $total_top,
        'invoiced_top' => $invoiced_top,
        'pending_top' => $pending_top,
    ];
}

/**
 * Process raw data rows for a single SPK and extract invoice progress.
 * Mirrors the relevant portion of Report_piutang_per_invoice::_process_report_data()
 *
 * @param array $raw_data Raw query rows for a single SPK
 * @return array SPK entry with total_top, invoiced_top, pending_top
 */
function process_spk_invoice_progress(array $raw_data): array
{
    if (empty($raw_data)) {
        return [
            'total_top' => 0,
            'invoiced_top' => 0,
            'pending_top' => 0,
        ];
    }

    // Group by TOP id and determine if each has an invoice
    $tops = [];

    foreach ($raw_data as $row) {
        $top_id = $row['id_detail_plan_tagih'];

        if (!isset($tops[$top_id])) {
            $tops[$top_id] = [
                'top_number' => (int) $row['top_number'],
                'has_invoice' => false,
            ];
        }

        // If this row has an invoice_id, mark the TOP as having an invoice
        if (!empty($row['id_invoice'])) {
            $tops[$top_id]['has_invoice'] = true;
        }
    }

    $total_top = count($tops);
    $invoiced_top = 0;

    foreach ($tops as $top) {
        if ($top['has_invoice']) {
            $invoiced_top++;
        }
    }

    $pending_top = $total_top - $invoiced_top;

    return [
        'total_top' => $total_top,
        'invoiced_top' => $invoiced_top,
        'pending_top' => $pending_top,
    ];
}

// ============================================================================
// Random data generators
// ============================================================================

/**
 * Generate a random date string in Y-m-d format.
 */
function generateRandomDate(string $min_date = '2020-01-01', string $max_date = '2025-12-31'): string
{
    $min_ts = strtotime($min_date);
    $max_ts = strtotime($max_date);
    if ($min_ts > $max_ts) {
        return $min_date;
    }
    $random_ts = mt_rand($min_ts, $max_ts);
    return date('Y-m-d', $random_ts);
}

/**
 * Generate random raw data rows for a single SPK with N TOPs,
 * where M of them have invoices (M <= N).
 *
 * @param int $num_tops Total number of TOPs (N)
 * @param int $num_invoiced Number of TOPs that have invoices (M, where M <= N)
 * @param int $max_payments_per_invoice Max payments per invoiced TOP
 * @return array Raw data rows simulating query result for one SPK
 */
function generateSpkRawData(int $num_tops, int $num_invoiced, int $max_payments_per_invoice = 2): array
{
    $raw_data = [];
    $spk_id = mt_rand(1, 9999);
    $customer_name = 'PT Customer ' . mt_rand(1, 999);
    $nominal_project = (float)(mt_rand(50000, 500000) * 1000);

    // Determine which TOPs will have invoices (randomly select M out of N)
    $top_indices = range(0, $num_tops - 1);
    shuffle($top_indices);
    $invoiced_indices = array_slice($top_indices, 0, $num_invoiced);

    $top_counter = mt_rand(100, 999);
    $invoice_counter = mt_rand(1000, 9999);
    $payment_counter = mt_rand(10000, 99999);

    for ($t = 0; $t < $num_tops; $t++) {
        $top_id = $top_counter + $t;
        $top_number = $t + 1;
        $rincian_top = round($nominal_project / $num_tops, 0);
        $has_invoice = in_array($t, $invoiced_indices);

        if ($has_invoice) {
            $inv_id = $invoice_counter++;
            $nilai_invoice = (float)(mt_rand(10000, 100000) * 1000);
            $tanggal_invoice = generateRandomDate('2022-01-01', '2024-12-31');
            $no_invoice = 'INV-' . str_pad($inv_id, 4, '0', STR_PAD_LEFT);

            $num_payments = mt_rand(0, $max_payments_per_invoice);

            if ($num_payments === 0) {
                // Invoice with no payments
                $raw_data[] = [
                    'nm_customer' => $customer_name,
                    'id_spk_penawaran' => $spk_id,
                    'nominal_project' => $nominal_project,
                    'id_detail_plan_tagih' => $top_id,
                    'top_number' => $top_number,
                    'rincian_top' => $rincian_top,
                    'id_invoice' => $inv_id,
                    'tanggal_invoice' => $tanggal_invoice,
                    'no_invoice' => $no_invoice,
                    'nilai_invoice' => $nilai_invoice,
                    'id_payment' => null,
                    'tanggal_bayar' => null,
                    'nilai_bayar' => null,
                ];
            } else {
                // Invoice with payments - one row per payment
                for ($p = 0; $p < $num_payments; $p++) {
                    $pay_id = $payment_counter++;
                    $nilai_bayar = (float)(mt_rand(1000, max(1000, (int)($nilai_invoice / 1000 / $num_payments))) * 1000);
                    $tanggal_bayar = generateRandomDate($tanggal_invoice, '2025-06-30');

                    $raw_data[] = [
                        'nm_customer' => $customer_name,
                        'id_spk_penawaran' => $spk_id,
                        'nominal_project' => $nominal_project,
                        'id_detail_plan_tagih' => $top_id,
                        'top_number' => $top_number,
                        'rincian_top' => $rincian_top,
                        'id_invoice' => $inv_id,
                        'tanggal_invoice' => $tanggal_invoice,
                        'no_invoice' => $no_invoice,
                        'nilai_invoice' => $nilai_invoice,
                        'id_payment' => $pay_id,
                        'tanggal_bayar' => $tanggal_bayar,
                        'nilai_bayar' => $nilai_bayar,
                    ];
                }
            }
        } else {
            // TOP without invoice
            $raw_data[] = [
                'nm_customer' => $customer_name,
                'id_spk_penawaran' => $spk_id,
                'nominal_project' => $nominal_project,
                'id_detail_plan_tagih' => $top_id,
                'top_number' => $top_number,
                'rincian_top' => $rincian_top,
                'id_invoice' => null,
                'tanggal_invoice' => null,
                'no_invoice' => null,
                'nilai_invoice' => null,
                'id_payment' => null,
                'tanggal_bayar' => null,
                'nilai_bayar' => null,
            ];
        }
    }

    return $raw_data;
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

    public function assertTrue(bool $condition, string $message): void
    {
        $this->assert($condition, $message);
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
// Property 4: Invoice progress calculation
// ============================================================================

/**
 * Main property: For any SPK with N total TOP records and M invoiced TOPs
 * (where M ≤ N), the displayed total TOP count SHALL equal N, the invoiced
 * count SHALL equal M, and the pending count SHALL equal N - M.
 *
 * Generates random SPKs with varying TOP/invoice counts and verifies the math.
 */
function runProperty4_InvoiceProgressCalculation(PropertyTestRunner $runner, int $iterations): void
{
    echo "Running Property 4: Invoice progress calculation ({$iterations} iterations)...\n";

    for ($i = 0; $i < $iterations; $i++) {
        // Generate random N (1-20 TOPs) and M (0 to N invoiced)
        $n = mt_rand(1, 20);
        $m = mt_rand(0, $n);

        // Generate raw data for this SPK
        $raw_data = generateSpkRawData($n, $m);

        // Process using the function under test
        $progress = process_spk_invoice_progress($raw_data);

        // Verify: total_top == N
        $runner->assertEquals(
            $n,
            $progress['total_top'],
            "Iteration {$i}: total_top should equal N={$n} (got {$progress['total_top']})"
        );

        // Verify: invoiced_top == M
        $runner->assertEquals(
            $m,
            $progress['invoiced_top'],
            "Iteration {$i}: invoiced_top should equal M={$m} (got {$progress['invoiced_top']})"
        );

        // Verify: pending_top == N - M
        $expected_pending = $n - $m;
        $runner->assertEquals(
            $expected_pending,
            $progress['pending_top'],
            "Iteration {$i}: pending_top should equal N-M={$expected_pending} (got {$progress['pending_top']})"
        );
    }

    echo "  Completed {$iterations} iterations.\n";
}

/**
 * Sub-property: All TOPs have invoices → pending = 0.
 * When M = N, pending_top must be 0.
 */
function runProperty4a_AllInvoiced(PropertyTestRunner $runner, int $iterations): void
{
    echo "Running Property 4a: All TOPs invoiced → pending = 0 ({$iterations} iterations)...\n";

    for ($i = 0; $i < $iterations; $i++) {
        $n = mt_rand(1, 20);
        $m = $n; // All TOPs have invoices

        $raw_data = generateSpkRawData($n, $m);
        $progress = process_spk_invoice_progress($raw_data);

        $runner->assertEquals(
            $n,
            $progress['total_top'],
            "Iteration {$i}: total_top should equal N={$n}"
        );

        $runner->assertEquals(
            $n,
            $progress['invoiced_top'],
            "Iteration {$i}: invoiced_top should equal N={$n} (all invoiced)"
        );

        $runner->assertEquals(
            0,
            $progress['pending_top'],
            "Iteration {$i}: pending_top should be 0 when all TOPs are invoiced (got {$progress['pending_top']})"
        );
    }

    echo "  Completed {$iterations} iterations.\n";
}

/**
 * Sub-property: No TOPs have invoices → invoiced = 0, pending = N.
 * When M = 0, invoiced_top must be 0 and pending_top must equal N.
 */
function runProperty4b_NoneInvoiced(PropertyTestRunner $runner, int $iterations): void
{
    echo "Running Property 4b: No TOPs invoiced → invoiced = 0, pending = N ({$iterations} iterations)...\n";

    for ($i = 0; $i < $iterations; $i++) {
        $n = mt_rand(1, 20);
        $m = 0; // No TOPs have invoices

        $raw_data = generateSpkRawData($n, $m);
        $progress = process_spk_invoice_progress($raw_data);

        $runner->assertEquals(
            $n,
            $progress['total_top'],
            "Iteration {$i}: total_top should equal N={$n}"
        );

        $runner->assertEquals(
            0,
            $progress['invoiced_top'],
            "Iteration {$i}: invoiced_top should be 0 when no TOPs are invoiced (got {$progress['invoiced_top']})"
        );

        $runner->assertEquals(
            $n,
            $progress['pending_top'],
            "Iteration {$i}: pending_top should equal N={$n} when no TOPs are invoiced (got {$progress['pending_top']})"
        );
    }

    echo "  Completed {$iterations} iterations.\n";
}

/**
 * Sub-property: pending is never negative.
 * For any valid input, pending_top >= 0.
 */
function runProperty4c_PendingNeverNegative(PropertyTestRunner $runner, int $iterations): void
{
    echo "Running Property 4c: Pending is never negative ({$iterations} iterations)...\n";

    for ($i = 0; $i < $iterations; $i++) {
        $n = mt_rand(1, 20);
        $m = mt_rand(0, $n);

        $raw_data = generateSpkRawData($n, $m);
        $progress = process_spk_invoice_progress($raw_data);

        $runner->assertTrue(
            $progress['pending_top'] >= 0,
            "Iteration {$i}: pending_top should never be negative (got {$progress['pending_top']}, N={$n}, M={$m})"
        );
    }

    echo "  Completed {$iterations} iterations.\n";
}

/**
 * Sub-property: invoiced + pending always equals total.
 * For any valid input, invoiced_top + pending_top == total_top.
 */
function runProperty4d_InvoicedPlusPendingEqualsTotal(PropertyTestRunner $runner, int $iterations): void
{
    echo "Running Property 4d: invoiced + pending = total ({$iterations} iterations)...\n";

    for ($i = 0; $i < $iterations; $i++) {
        $n = mt_rand(1, 20);
        $m = mt_rand(0, $n);

        $raw_data = generateSpkRawData($n, $m);
        $progress = process_spk_invoice_progress($raw_data);

        $sum = $progress['invoiced_top'] + $progress['pending_top'];

        $runner->assertEquals(
            $progress['total_top'],
            $sum,
            "Iteration {$i}: invoiced_top ({$progress['invoiced_top']}) + pending_top ({$progress['pending_top']}) " .
                "should equal total_top ({$progress['total_top']})"
        );
    }

    echo "  Completed {$iterations} iterations.\n";
}

/**
 * Sub-property: Using calculate_invoice_progress with abstract TOP array.
 * Tests the pure function directly with generated TOP arrays.
 */
function runProperty4e_PureFunctionTest(PropertyTestRunner $runner, int $iterations): void
{
    echo "Running Property 4e: Pure function calculate_invoice_progress ({$iterations} iterations)...\n";

    for ($i = 0; $i < $iterations; $i++) {
        $n = mt_rand(1, 20);
        $m = mt_rand(0, $n);

        // Build a TOP array with exactly M having invoices
        $tops = [];
        $indices = range(0, $n - 1);
        shuffle($indices);
        $invoiced_indices = array_slice($indices, 0, $m);

        for ($t = 0; $t < $n; $t++) {
            $tops[] = [
                'has_invoice' => in_array($t, $invoiced_indices),
            ];
        }

        $progress = calculate_invoice_progress($tops);

        // Verify all three values
        $runner->assertEquals(
            $n,
            $progress['total_top'],
            "Iteration {$i}: Pure function - total_top should equal N={$n}"
        );

        $runner->assertEquals(
            $m,
            $progress['invoiced_top'],
            "Iteration {$i}: Pure function - invoiced_top should equal M={$m}"
        );

        $runner->assertEquals(
            $n - $m,
            $progress['pending_top'],
            "Iteration {$i}: Pure function - pending_top should equal N-M=" . ($n - $m)
        );

        // Invariant: sum check
        $runner->assertEquals(
            $progress['total_top'],
            $progress['invoiced_top'] + $progress['pending_top'],
            "Iteration {$i}: Pure function - invoiced + pending must equal total"
        );
    }

    echo "  Completed {$iterations} iterations.\n";
}

// ============================================================================
// Main execution
// ============================================================================

echo "========================================\n";
echo "Feature: report-piutang-per-invoice\n";
echo "Property 4: Invoice progress calculation\n";
echo "Validates: Requirements 5.1, 5.2, 5.3\n";
echo "========================================\n\n";

$runner = new PropertyTestRunner();

// Main property: 100 iterations
runProperty4_InvoiceProgressCalculation($runner, 100);

// Sub-properties
runProperty4a_AllInvoiced($runner, 100);
runProperty4b_NoneInvoiced($runner, 100);
runProperty4c_PendingNeverNegative($runner, 100);
runProperty4d_InvoicedPlusPendingEqualsTotal($runner, 100);

// Pure function test
runProperty4e_PureFunctionTest($runner, 100);

$success = $runner->printResults();

exit($success ? 0 : 1);
