<?php

/**
 * Property-Based Test: Filtering and Visibility for Report Piutang Per Invoice
 *
 * Feature: report-piutang-per-invoice, Property 2: Date-filtered balance determines invoice visibility
 *
 * This test validates that the date-filtered balance logic correctly determines
 * which invoices should be visible in the report based on:
 * (a) invoice date is on or before the filter date
 * (b) calculated piutang (invoice value - sum of payments on or before filter date) > 0
 *
 * **Validates: Requirements 2.2, 2.3, 2.4**
 *
 * Run: php application/modules/report_piutang_per_invoice/tests/Report_piutang_per_invoice_filtering_test.php
 */

// ============================================================================
// Pure logic implementations (mirrors the model/controller filtering logic)
// ============================================================================

/**
 * Determines which invoices should be visible in the report given a filter date.
 *
 * An invoice is visible if:
 * (a) invoice_date <= filter_date (invoice was created on or before filter date)
 * (b) piutang = invoice_value - sum(payments where payment_date <= filter_date) > 0
 *
 * @param array  $invoices    Array of invoices, each with keys: id, date (Y-m-d), value
 * @param array  $payments    Array of payments, each with keys: invoice_id, date (Y-m-d), amount
 * @param string $filter_date Filter date in Y-m-d format
 * @return array Array of invoice IDs that should be visible
 */
function filter_visible_invoices(array $invoices, array $payments, string $filter_date): array
{
    $visible = [];

    foreach ($invoices as $invoice) {
        // Condition (a): invoice date must be on or before filter date
        if ($invoice['date'] > $filter_date) {
            continue;
        }

        // Condition (b): calculate piutang for this invoice
        $total_payments = 0;
        foreach ($payments as $payment) {
            if ($payment['invoice_id'] === $invoice['id'] && $payment['date'] <= $filter_date) {
                $total_payments += $payment['amount'];
            }
        }

        $piutang = $invoice['value'] - $total_payments;

        // Only include if piutang > 0 (not fully paid)
        if ($piutang > 0) {
            $visible[] = $invoice['id'];
        }
    }

    return $visible;
}

/**
 * Calculates the piutang (outstanding balance) for a single invoice given a filter date.
 *
 * @param array  $invoice     Invoice with keys: id, date (Y-m-d), value
 * @param array  $payments    All payments array
 * @param string $filter_date Filter date in Y-m-d format
 * @return float The piutang value (clamped to 0 minimum for overpayment)
 */
function calculate_piutang_for_invoice(array $invoice, array $payments, string $filter_date): float
{
    $total_payments = 0;
    foreach ($payments as $payment) {
        if ($payment['invoice_id'] === $invoice['id'] && $payment['date'] <= $filter_date) {
            $total_payments += $payment['amount'];
        }
    }

    return max(0, $invoice['value'] - $total_payments);
}

// ============================================================================
// Random data generators
// ============================================================================

/**
 * Generate a random date string in Y-m-d format within a range.
 *
 * @param string $min_date Minimum date (Y-m-d)
 * @param string $max_date Maximum date (Y-m-d)
 * @return string Random date in Y-m-d format
 */
function generateRandomDate(string $min_date = '2020-01-01', string $max_date = '2025-12-31'): string
{
    $min_ts = strtotime($min_date);
    $max_ts = strtotime($max_date);

    // Guard against invalid range
    if ($min_ts > $max_ts) {
        return $min_date;
    }

    $random_ts = mt_rand($min_ts, $max_ts);
    return date('Y-m-d', $random_ts);
}

/**
 * Generate a random invoice value (positive).
 * Range: 1,000,000 to 500,000,000
 */
function generateRandomInvoiceValue(): float
{
    return (float)(mt_rand(1000, 500000) * 1000);
}

/**
 * Generate a random payment amount (positive, typically less than invoice value).
 * Range: 100,000 to max_amount
 */
function generateRandomPaymentAmount(float $max_amount): float
{
    $max = max(100000, (int)$max_amount);
    return (float)(mt_rand(100, (int)($max / 1000)) * 1000);
}

/**
 * Generate a random set of invoices.
 *
 * @param int $count Number of invoices to generate
 * @return array Array of invoices with id, date, value
 */
function generateRandomInvoices(int $count): array
{
    $invoices = [];
    for ($i = 1; $i <= $count; $i++) {
        $invoices[] = [
            'id' => $i,
            'date' => generateRandomDate(),
            'value' => generateRandomInvoiceValue(),
        ];
    }
    return $invoices;
}

/**
 * Generate random payments linked to invoices.
 *
 * @param array $invoices Array of invoices to link payments to
 * @param int   $max_payments_per_invoice Maximum payments per invoice
 * @return array Array of payments with invoice_id, date, amount
 */
function generateRandomPayments(array $invoices, int $max_payments_per_invoice = 3): array
{
    $payments = [];
    foreach ($invoices as $invoice) {
        $num_payments = mt_rand(0, $max_payments_per_invoice);
        for ($j = 0; $j < $num_payments; $j++) {
            $payments[] = [
                'invoice_id' => $invoice['id'],
                'date' => generateRandomDate(),
                'amount' => generateRandomPaymentAmount($invoice['value']),
            ];
        }
    }
    return $payments;
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
// Property 2: Date-filtered balance determines invoice visibility
// ============================================================================

/**
 * For any set of invoices and payments with various dates, and for any valid
 * filter date, the report SHALL only display invoices where:
 * (a) the invoice date is on or before the filter date, AND
 * (b) the calculated piutang (invoice value minus sum of payments with
 *     payment date on or before filter date) is greater than zero.
 */
function runProperty2DateFilteredBalance(PropertyTestRunner $runner, int $iterations): void
{
    echo "Running Property 2: Date-filtered balance determines invoice visibility ({$iterations} iterations)...\n";

    for ($i = 0; $i < $iterations; $i++) {
        // Generate random test data
        $num_invoices = mt_rand(1, 10);
        $invoices = generateRandomInvoices($num_invoices);
        $payments = generateRandomPayments($invoices);
        $filter_date = generateRandomDate();

        // Get visible invoices from our filtering function
        $visible_ids = filter_visible_invoices($invoices, $payments, $filter_date);

        // Verify each visible invoice satisfies both conditions
        foreach ($visible_ids as $visible_id) {
            $invoice = null;
            foreach ($invoices as $inv) {
                if ($inv['id'] === $visible_id) {
                    $invoice = $inv;
                    break;
                }
            }

            // Condition (a): invoice date <= filter date
            $runner->assertTrue(
                $invoice['date'] <= $filter_date,
                "Iteration {$i}: Visible invoice ID={$visible_id} has date {$invoice['date']} which is AFTER filter date {$filter_date}"
            );

            // Condition (b): piutang > 0
            $piutang = calculate_piutang_for_invoice($invoice, $payments, $filter_date);
            $runner->assertTrue(
                $piutang > 0,
                "Iteration {$i}: Visible invoice ID={$visible_id} has piutang={$piutang} which is NOT > 0 (value={$invoice['value']}, filter_date={$filter_date})"
            );
        }

        // Verify each NON-visible invoice fails at least one condition
        foreach ($invoices as $invoice) {
            if (in_array($invoice['id'], $visible_ids)) {
                continue; // Skip visible ones
            }

            $invoice_after_filter = $invoice['date'] > $filter_date;
            $piutang = calculate_piutang_for_invoice($invoice, $payments, $filter_date);
            $piutang_is_zero = $piutang <= 0;

            // Must fail condition (a) OR condition (b)
            $runner->assertTrue(
                $invoice_after_filter || $piutang_is_zero,
                "Iteration {$i}: Non-visible invoice ID={$invoice['id']} satisfies BOTH conditions " .
                    "(date={$invoice['date']} <= {$filter_date}, piutang={$piutang} > 0) but was excluded"
            );
        }
    }

    echo "  Completed {$iterations} iterations.\n";
}

/**
 * Sub-property: Invoices with date AFTER filter date are NEVER visible.
 * This specifically validates Requirement 2.3.
 */
function runProperty2a_InvoiceAfterFilterNeverVisible(PropertyTestRunner $runner, int $iterations): void
{
    echo "Running Property 2a: Invoices after filter date are never visible ({$iterations} iterations)...\n";

    for ($i = 0; $i < $iterations; $i++) {
        // Create invoices where some are definitely after the filter date
        // Use a filter date that leaves room for "after" dates
        $filter_date = generateRandomDate('2022-01-01', '2024-06-30');
        $invoices = [];

        // Create some invoices before filter date
        $num_before = mt_rand(1, 5);
        for ($j = 1; $j <= $num_before; $j++) {
            $invoices[] = [
                'id' => $j,
                'date' => generateRandomDate('2020-01-01', $filter_date),
                'value' => generateRandomInvoiceValue(),
            ];
        }

        // Create some invoices AFTER filter date
        $after_start = date('Y-m-d', strtotime($filter_date . ' +1 day'));
        $num_after = mt_rand(1, 5);
        $after_ids = [];
        for ($k = 0; $k < $num_after; $k++) {
            $id = $num_before + $k + 1;
            $invoices[] = [
                'id' => $id,
                'date' => generateRandomDate($after_start, '2025-12-31'),
                'value' => generateRandomInvoiceValue(),
            ];
            $after_ids[] = $id;
        }

        // Generate payments (limited to avoid memory issues)
        $payments = generateRandomPayments($invoices, 2);

        // Get visible invoices
        $visible_ids = filter_visible_invoices($invoices, $payments, $filter_date);

        // Verify: no invoice with date > filter_date is visible
        foreach ($after_ids as $after_id) {
            $runner->assertTrue(
                !in_array($after_id, $visible_ids),
                "Iteration {$i}: Invoice ID={$after_id} with date AFTER filter_date={$filter_date} should NOT be visible"
            );
        }
    }

    echo "  Completed {$iterations} iterations.\n";
}

/**
 * Sub-property: Fully paid invoices (payments >= invoice value) are NEVER visible.
 * This specifically validates Requirement 2.2 (saldo piutang > 0).
 */
function runProperty2b_FullyPaidNeverVisible(PropertyTestRunner $runner, int $iterations): void
{
    echo "Running Property 2b: Fully paid invoices are never visible ({$iterations} iterations)...\n";

    for ($i = 0; $i < $iterations; $i++) {
        $filter_date = generateRandomDate('2023-01-01', '2025-12-31');

        // Create invoices all before filter date
        $invoices = [];
        $num_invoices = mt_rand(1, 5);
        for ($j = 1; $j <= $num_invoices; $j++) {
            $invoices[] = [
                'id' => $j,
                'date' => generateRandomDate('2020-01-01', $filter_date),
                'value' => generateRandomInvoiceValue(),
            ];
        }

        // Create payments that fully pay some invoices (before filter date)
        $payments = [];
        $fully_paid_ids = [];
        foreach ($invoices as $invoice) {
            if (mt_rand(0, 1) === 1) {
                // Fully pay this invoice (payment >= invoice value, before filter date)
                $payments[] = [
                    'invoice_id' => $invoice['id'],
                    'date' => generateRandomDate($invoice['date'], $filter_date),
                    'amount' => $invoice['value'] + mt_rand(0, 1000000), // equal or overpay
                ];
                $fully_paid_ids[] = $invoice['id'];
            }
        }

        // Get visible invoices
        $visible_ids = filter_visible_invoices($invoices, $payments, $filter_date);

        // Verify: no fully paid invoice is visible
        foreach ($fully_paid_ids as $paid_id) {
            $runner->assertTrue(
                !in_array($paid_id, $visible_ids),
                "Iteration {$i}: Fully paid invoice ID={$paid_id} should NOT be visible"
            );
        }
    }

    echo "  Completed {$iterations} iterations.\n";
}

/**
 * Sub-property: Payments AFTER filter date do NOT reduce piutang.
 * This specifically validates Requirement 2.4.
 */
function runProperty2c_PaymentsAfterFilterIgnored(PropertyTestRunner $runner, int $iterations): void
{
    echo "Running Property 2c: Payments after filter date do not reduce piutang ({$iterations} iterations)...\n";

    for ($i = 0; $i < $iterations; $i++) {
        $filter_date = generateRandomDate('2022-06-01', '2024-06-30');

        // Create a single invoice before filter date
        $invoice = [
            'id' => 1,
            'date' => generateRandomDate('2020-01-01', $filter_date),
            'value' => generateRandomInvoiceValue(),
        ];
        $invoices = [$invoice];

        // Create payments: some before filter, some after
        $payments_before = [];
        $payments_after = [];
        $num_before = mt_rand(0, 3);
        $num_after = mt_rand(1, 3);

        for ($j = 0; $j < $num_before; $j++) {
            $payments_before[] = [
                'invoice_id' => 1,
                'date' => generateRandomDate($invoice['date'], $filter_date),
                'amount' => generateRandomPaymentAmount($invoice['value'] / 4),
            ];
        }

        $after_start = date('Y-m-d', strtotime($filter_date . ' +1 day'));
        for ($j = 0; $j < $num_after; $j++) {
            $payments_after[] = [
                'invoice_id' => 1,
                'date' => generateRandomDate($after_start, '2025-12-31'),
                'amount' => generateRandomPaymentAmount($invoice['value'] / 2),
            ];
        }

        // Calculate piutang with only before-filter payments
        $all_payments = array_merge($payments_before, $payments_after);
        $piutang_with_all = calculate_piutang_for_invoice($invoice, $all_payments, $filter_date);
        $piutang_before_only = calculate_piutang_for_invoice($invoice, $payments_before, $filter_date);

        // Property: piutang should be the same whether or not after-filter payments exist
        $runner->assertFloatEquals(
            $piutang_before_only,
            $piutang_with_all,
            "Iteration {$i}: Piutang should ignore payments after filter_date={$filter_date} " .
                "(before_only={$piutang_before_only}, with_all={$piutang_with_all})"
        );
    }

    echo "  Completed {$iterations} iterations.\n";
}

/**
 * Edge case: Invoice exactly on filter date should be included (if piutang > 0).
 */
function runProperty2d_InvoiceOnFilterDateIncluded(PropertyTestRunner $runner, int $iterations): void
{
    echo "Running Property 2d: Invoice exactly on filter date is included ({$iterations} iterations)...\n";

    for ($i = 0; $i < $iterations; $i++) {
        $filter_date = generateRandomDate();

        // Create invoice exactly on filter date with no payments
        $invoice = [
            'id' => 1,
            'date' => $filter_date,
            'value' => generateRandomInvoiceValue(),
        ];
        $invoices = [$invoice];
        $payments = []; // No payments, so piutang = invoice value > 0

        $visible_ids = filter_visible_invoices($invoices, $payments, $filter_date);

        // Invoice on filter date with no payments should be visible
        $runner->assertTrue(
            in_array(1, $visible_ids),
            "Iteration {$i}: Invoice on filter_date={$filter_date} with no payments should be visible"
        );
    }

    echo "  Completed {$iterations} iterations.\n";
}

/**
 * Edge case: Payment exactly on filter date should be counted.
 */
function runProperty2e_PaymentOnFilterDateCounted(PropertyTestRunner $runner, int $iterations): void
{
    echo "Running Property 2e: Payment exactly on filter date is counted ({$iterations} iterations)...\n";

    for ($i = 0; $i < $iterations; $i++) {
        $filter_date = generateRandomDate('2022-01-01', '2024-12-31');

        // Create invoice before filter date
        $invoice_value = generateRandomInvoiceValue();
        $invoice = [
            'id' => 1,
            'date' => generateRandomDate('2020-01-01', $filter_date),
            'value' => $invoice_value,
        ];
        $invoices = [$invoice];

        // Create a payment exactly on filter date that fully pays the invoice
        $payments = [
            [
                'invoice_id' => 1,
                'date' => $filter_date,
                'amount' => $invoice_value, // Exact full payment
            ]
        ];

        $visible_ids = filter_visible_invoices($invoices, $payments, $filter_date);

        // Invoice should NOT be visible because payment on filter date makes piutang = 0
        $runner->assertTrue(
            !in_array(1, $visible_ids),
            "Iteration {$i}: Invoice fully paid on filter_date={$filter_date} should NOT be visible (value={$invoice_value})"
        );
    }

    echo "  Completed {$iterations} iterations.\n";
}

// ============================================================================
// Property 10: Uninvoiced date filtering consistency
// ============================================================================

/**
 * Feature: report-piutang-per-invoice, Property 10: Uninvoiced date filtering consistency
 *
 * For any SPK and for any filter date, the uninvoiced calculation SHALL only
 * consider invoices whose creation date is on or before the filter date.
 * Invoices created after the filter date SHALL not reduce the uninvoiced amount.
 *
 * **Validates: Requirements 2.6**
 */

/**
 * Calculate uninvoiced for an SPK given a filter date.
 * Uninvoiced = nominal_project - SUM(invoice values where invoice_date <= filter_date)
 *
 * @param float  $nominal_project The SPK nominal project value
 * @param array  $invoices        Array of invoices with keys: date (Y-m-d), value
 * @param string $filter_date     Filter date in Y-m-d format
 * @return float The uninvoiced amount
 */
function calculate_uninvoiced(float $nominal_project, array $invoices, string $filter_date): float
{
    $sum_invoiced = 0;
    foreach ($invoices as $invoice) {
        if ($invoice['date'] <= $filter_date) {
            $sum_invoiced += $invoice['value'];
        }
    }
    return $nominal_project - $sum_invoiced;
}

/**
 * Generate a random SPK with invoices at various dates.
 *
 * @return array SPK data with nominal_project and invoices array
 */
function generateRandomSPKWithInvoices(): array
{
    $nominal_project = (float)(mt_rand(50000, 1000000) * 1000); // 50M - 1B
    $num_invoices = mt_rand(1, 8);
    $invoices = [];

    for ($i = 0; $i < $num_invoices; $i++) {
        $invoices[] = [
            'id' => $i + 1,
            'date' => generateRandomDate('2020-01-01', '2025-12-31'),
            'value' => (float)(mt_rand(1000, (int)($nominal_project / ($num_invoices * 1000))) * 1000),
        ];
    }

    return [
        'nominal_project' => $nominal_project,
        'invoices' => $invoices,
    ];
}

/**
 * Main Property 10: Uninvoiced only considers invoices on or before filter date.
 * Verifies: uninvoiced = nominal_project - SUM(invoice values where invoice_date <= filter_date)
 */
function runProperty10_UninvoicedDateFilteringConsistency(PropertyTestRunner $runner, int $iterations): void
{
    echo "Running Property 10: Uninvoiced date filtering consistency ({$iterations} iterations)...\n";

    for ($i = 0; $i < $iterations; $i++) {
        $spk = generateRandomSPKWithInvoices();
        $filter_date = generateRandomDate('2021-01-01', '2025-06-30');

        // Calculate uninvoiced using our function
        $uninvoiced = calculate_uninvoiced($spk['nominal_project'], $spk['invoices'], $filter_date);

        // Manually calculate expected: only sum invoices where date <= filter_date
        $sum_before_or_on = 0;
        foreach ($spk['invoices'] as $invoice) {
            if ($invoice['date'] <= $filter_date) {
                $sum_before_or_on += $invoice['value'];
            }
        }
        $expected_uninvoiced = $spk['nominal_project'] - $sum_before_or_on;

        $runner->assertFloatEquals(
            $expected_uninvoiced,
            $uninvoiced,
            "Iteration {$i}: Uninvoiced mismatch for nominal={$spk['nominal_project']}, filter_date={$filter_date}"
        );
    }

    echo "  Completed {$iterations} iterations.\n";
}

/**
 * Sub-property 10a: All invoices before filter date - uninvoiced = nominal - sum(all invoices)
 */
function runProperty10a_AllInvoicesBeforeFilter(PropertyTestRunner $runner, int $iterations): void
{
    echo "Running Property 10a: All invoices before filter - uninvoiced = nominal - sum(all) ({$iterations} iterations)...\n";

    for ($i = 0; $i < $iterations; $i++) {
        $nominal_project = (float)(mt_rand(100000, 1000000) * 1000);
        $filter_date = generateRandomDate('2024-06-01', '2025-12-31');

        // Generate invoices ALL before filter date
        $num_invoices = mt_rand(1, 6);
        $invoices = [];
        $sum_all = 0;
        for ($j = 0; $j < $num_invoices; $j++) {
            $value = (float)(mt_rand(1000, 50000) * 1000);
            $invoices[] = [
                'id' => $j + 1,
                'date' => generateRandomDate('2020-01-01', $filter_date),
                'value' => $value,
            ];
            $sum_all += $value;
        }

        $uninvoiced = calculate_uninvoiced($nominal_project, $invoices, $filter_date);
        $expected = $nominal_project - $sum_all;

        $runner->assertFloatEquals(
            $expected,
            $uninvoiced,
            "Iteration {$i}: When all invoices before filter, uninvoiced should = nominal - sum(all). " .
                "Expected={$expected}, Got={$uninvoiced}"
        );
    }

    echo "  Completed {$iterations} iterations.\n";
}

/**
 * Sub-property 10b: All invoices after filter date - uninvoiced = nominal (none counted)
 */
function runProperty10b_AllInvoicesAfterFilter(PropertyTestRunner $runner, int $iterations): void
{
    echo "Running Property 10b: All invoices after filter - uninvoiced = nominal ({$iterations} iterations)...\n";

    for ($i = 0; $i < $iterations; $i++) {
        $nominal_project = (float)(mt_rand(100000, 1000000) * 1000);
        $filter_date = generateRandomDate('2020-01-01', '2022-12-31');

        // Generate invoices ALL after filter date
        $after_start = date('Y-m-d', strtotime($filter_date . ' +1 day'));
        $num_invoices = mt_rand(1, 6);
        $invoices = [];
        for ($j = 0; $j < $num_invoices; $j++) {
            $invoices[] = [
                'id' => $j + 1,
                'date' => generateRandomDate($after_start, '2025-12-31'),
                'value' => (float)(mt_rand(1000, 50000) * 1000),
            ];
        }

        $uninvoiced = calculate_uninvoiced($nominal_project, $invoices, $filter_date);

        // When all invoices are after filter, none are counted, so uninvoiced = nominal
        $runner->assertFloatEquals(
            $nominal_project,
            $uninvoiced,
            "Iteration {$i}: When all invoices after filter_date={$filter_date}, uninvoiced should = nominal_project={$nominal_project}. Got={$uninvoiced}"
        );
    }

    echo "  Completed {$iterations} iterations.\n";
}

/**
 * Sub-property 10c: Mix of before/after - uninvoiced = nominal - sum(only before/on filter date)
 */
function runProperty10c_MixedInvoiceDates(PropertyTestRunner $runner, int $iterations): void
{
    echo "Running Property 10c: Mixed invoice dates - only before/on filter counted ({$iterations} iterations)...\n";

    for ($i = 0; $i < $iterations; $i++) {
        $nominal_project = (float)(mt_rand(200000, 1000000) * 1000);
        $filter_date = generateRandomDate('2022-06-01', '2024-06-30');

        $invoices = [];
        $sum_before = 0;
        $after_start = date('Y-m-d', strtotime($filter_date . ' +1 day'));

        // Generate some invoices BEFORE/ON filter date
        $num_before = mt_rand(1, 4);
        for ($j = 0; $j < $num_before; $j++) {
            $value = (float)(mt_rand(1000, 30000) * 1000);
            $invoices[] = [
                'id' => $j + 1,
                'date' => generateRandomDate('2020-01-01', $filter_date),
                'value' => $value,
            ];
            $sum_before += $value;
        }

        // Generate some invoices AFTER filter date
        $num_after = mt_rand(1, 4);
        for ($k = 0; $k < $num_after; $k++) {
            $invoices[] = [
                'id' => $num_before + $k + 1,
                'date' => generateRandomDate($after_start, '2025-12-31'),
                'value' => (float)(mt_rand(1000, 30000) * 1000),
            ];
        }

        $uninvoiced = calculate_uninvoiced($nominal_project, $invoices, $filter_date);
        $expected = $nominal_project - $sum_before;

        $runner->assertFloatEquals(
            $expected,
            $uninvoiced,
            "Iteration {$i}: Mixed dates - uninvoiced should = nominal - sum(before). " .
                "Expected={$expected}, Got={$uninvoiced}, filter_date={$filter_date}"
        );
    }

    echo "  Completed {$iterations} iterations.\n";
}

/**
 * Sub-property 10d: Moving filter date earlier should increase uninvoiced
 * (fewer invoices counted means less subtracted from nominal)
 */
function runProperty10d_EarlierFilterIncreasesUninvoiced(PropertyTestRunner $runner, int $iterations): void
{
    echo "Running Property 10d: Earlier filter date increases or maintains uninvoiced ({$iterations} iterations)...\n";

    for ($i = 0; $i < $iterations; $i++) {
        $spk = generateRandomSPKWithInvoices();

        // Generate two filter dates where date1 < date2
        $date1 = generateRandomDate('2021-01-01', '2023-06-30');
        $date2 = generateRandomDate('2023-07-01', '2025-12-31');

        // Ensure date1 < date2
        if ($date1 > $date2) {
            $temp = $date1;
            $date1 = $date2;
            $date2 = $temp;
        }

        $uninvoiced_earlier = calculate_uninvoiced($spk['nominal_project'], $spk['invoices'], $date1);
        $uninvoiced_later = calculate_uninvoiced($spk['nominal_project'], $spk['invoices'], $date2);

        // Earlier filter date means fewer invoices counted, so uninvoiced should be >= later
        $runner->assertTrue(
            $uninvoiced_earlier >= $uninvoiced_later - 0.01, // small epsilon for float comparison
            "Iteration {$i}: Earlier filter ({$date1}) should have uninvoiced >= later filter ({$date2}). " .
                "Earlier={$uninvoiced_earlier}, Later={$uninvoiced_later}"
        );
    }

    echo "  Completed {$iterations} iterations.\n";
}

/**
 * Sub-property 10e: Moving filter date later should decrease or maintain uninvoiced
 * (more invoices counted means more subtracted from nominal)
 */
function runProperty10e_LaterFilterDecreasesUninvoiced(PropertyTestRunner $runner, int $iterations): void
{
    echo "Running Property 10e: Later filter date decreases or maintains uninvoiced ({$iterations} iterations)...\n";

    for ($i = 0; $i < $iterations; $i++) {
        $spk = generateRandomSPKWithInvoices();

        // Generate two filter dates
        $date1 = generateRandomDate('2021-01-01', '2023-06-30');
        $date2 = generateRandomDate('2023-07-01', '2025-12-31');

        // Ensure date1 < date2
        if ($date1 > $date2) {
            $temp = $date1;
            $date1 = $date2;
            $date2 = $temp;
        }

        $uninvoiced_earlier = calculate_uninvoiced($spk['nominal_project'], $spk['invoices'], $date1);
        $uninvoiced_later = calculate_uninvoiced($spk['nominal_project'], $spk['invoices'], $date2);

        // Later filter date means more invoices counted, so uninvoiced should be <= earlier
        $runner->assertTrue(
            $uninvoiced_later <= $uninvoiced_earlier + 0.01, // small epsilon for float comparison
            "Iteration {$i}: Later filter ({$date2}) should have uninvoiced <= earlier filter ({$date1}). " .
                "Later={$uninvoiced_later}, Earlier={$uninvoiced_earlier}"
        );
    }

    echo "  Completed {$iterations} iterations.\n";
}

/**
 * Sub-property 10f: Invoices after filter date do NOT reduce uninvoiced amount.
 * Specifically tests that adding invoices after filter_date doesn't change the uninvoiced.
 */
function runProperty10f_InvoicesAfterFilterDontReduceUninvoiced(PropertyTestRunner $runner, int $iterations): void
{
    echo "Running Property 10f: Invoices after filter do not reduce uninvoiced ({$iterations} iterations)...\n";

    for ($i = 0; $i < $iterations; $i++) {
        $nominal_project = (float)(mt_rand(200000, 1000000) * 1000);
        $filter_date = generateRandomDate('2022-06-01', '2024-06-30');
        $after_start = date('Y-m-d', strtotime($filter_date . ' +1 day'));

        // Create base invoices (before filter date)
        $num_before = mt_rand(0, 4);
        $invoices_before = [];
        for ($j = 0; $j < $num_before; $j++) {
            $invoices_before[] = [
                'id' => $j + 1,
                'date' => generateRandomDate('2020-01-01', $filter_date),
                'value' => (float)(mt_rand(1000, 30000) * 1000),
            ];
        }

        // Create additional invoices AFTER filter date
        $num_after = mt_rand(1, 5);
        $invoices_after = [];
        for ($k = 0; $k < $num_after; $k++) {
            $invoices_after[] = [
                'id' => $num_before + $k + 1,
                'date' => generateRandomDate($after_start, '2025-12-31'),
                'value' => (float)(mt_rand(1000, 50000) * 1000),
            ];
        }

        // Calculate uninvoiced with only before-filter invoices
        $uninvoiced_without_after = calculate_uninvoiced($nominal_project, $invoices_before, $filter_date);

        // Calculate uninvoiced with ALL invoices (before + after)
        $all_invoices = array_merge($invoices_before, $invoices_after);
        $uninvoiced_with_after = calculate_uninvoiced($nominal_project, $all_invoices, $filter_date);

        // Property: adding invoices after filter_date should NOT change uninvoiced
        $runner->assertFloatEquals(
            $uninvoiced_without_after,
            $uninvoiced_with_after,
            "Iteration {$i}: Adding invoices after filter_date should not change uninvoiced. " .
                "Without_after={$uninvoiced_without_after}, With_after={$uninvoiced_with_after}, filter_date={$filter_date}"
        );
    }

    echo "  Completed {$iterations} iterations.\n";
}

// ============================================================================
// Main execution
// ============================================================================

echo "========================================\n";
echo "Feature: report-piutang-per-invoice\n";
echo "Property 2: Date-filtered balance determines invoice visibility\n";
echo "Property 10: Uninvoiced date filtering consistency\n";
echo "Validates: Requirements 2.2, 2.3, 2.4, 2.6\n";
echo "========================================\n\n";

$runner = new PropertyTestRunner();

// Property 2: Main property and sub-properties (100 iterations each)
runProperty2DateFilteredBalance($runner, 100);
runProperty2a_InvoiceAfterFilterNeverVisible($runner, 100);
runProperty2b_FullyPaidNeverVisible($runner, 100);
runProperty2c_PaymentsAfterFilterIgnored($runner, 100);
runProperty2d_InvoiceOnFilterDateIncluded($runner, 100);
runProperty2e_PaymentOnFilterDateCounted($runner, 100);

// Property 10: Uninvoiced date filtering consistency (100 iterations each)
runProperty10_UninvoicedDateFilteringConsistency($runner, 100);
runProperty10a_AllInvoicesBeforeFilter($runner, 100);
runProperty10b_AllInvoicesAfterFilter($runner, 100);
runProperty10c_MixedInvoiceDates($runner, 100);
runProperty10d_EarlierFilterIncreasesUninvoiced($runner, 100);
runProperty10e_LaterFilterDecreasesUninvoiced($runner, 100);
runProperty10f_InvoicesAfterFilterDontReduceUninvoiced($runner, 100);

$success = $runner->printResults();

exit($success ? 0 : 1);
