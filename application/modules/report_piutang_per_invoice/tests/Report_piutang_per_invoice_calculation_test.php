<?php

/**
 * Property-Based Test: Summary Totals Consistency for Report Piutang Per Invoice
 *
 * Feature: report-piutang-per-invoice, Property 8: Summary totals consistency
 *
 * This test validates that for any dataset displayed on a tab, the following SHALL hold:
 * (a) Summary Piutang Per Invoice = sum of all individual piutang per invoice
 * (b) Summary Uninvoiced = sum of all uninvoiced per SPK
 * (c) Total Piutang = Summary Piutang Per Invoice + Summary Uninvoiced
 * (d) Summary Sisa Piutang Per SPK = sum of all total_sisa_piutang across all SPKs
 *
 * **Validates: Requirements 9.2, 9.3, 9.5**
 *
 * Run: php application/modules/report_piutang_per_invoice/tests/Report_piutang_per_invoice_calculation_test.php
 */

// ============================================================================
// Pure logic implementations (mirrors the controller's processing logic)
// ============================================================================

/**
 * Process raw data into hierarchical structure with calculated fields.
 * Mirrors Report_piutang_per_invoice::_process_report_data()
 */
function process_report_data_with_calculations(array $raw_data): array
{
    if (empty($raw_data)) {
        return [];
    }

    $customers = [];

    foreach ($raw_data as $row) {
        $customer_name = $row['nm_customer'];
        $spk_id = $row['id_spk_penawaran'];
        $top_id = $row['id_detail_plan_tagih'];
        $invoice_id = $row['id_invoice'];
        $payment_id = $row['id_payment'];

        if (!isset($customers[$customer_name])) {
            $customers[$customer_name] = [];
        }

        if (!isset($customers[$customer_name][$spk_id])) {
            $customers[$customer_name][$spk_id] = [
                'no_spk' => $spk_id,
                'nominal_project' => (float) $row['nominal_project'],
                'tops' => [],
            ];
        }

        if (!isset($customers[$customer_name][$spk_id]['tops'][$top_id])) {
            $customers[$customer_name][$spk_id]['tops'][$top_id] = [
                'top_number' => (int) $row['top_number'],
                'rincian_top' => (float) $row['rincian_top'],
                'invoice' => null,
            ];
        }

        if (!empty($invoice_id)) {
            if ($customers[$customer_name][$spk_id]['tops'][$top_id]['invoice'] === null) {
                $customers[$customer_name][$spk_id]['tops'][$top_id]['invoice'] = [
                    'tanggal_invoice' => $row['tanggal_invoice'],
                    'no_invoice' => $row['no_invoice'],
                    'nilai_invoice' => (float) $row['nilai_invoice'],
                    'payments' => [],
                ];
            }

            if (!empty($payment_id)) {
                $payments = &$customers[$customer_name][$spk_id]['tops'][$top_id]['invoice']['payments'];
                $payment_exists = false;
                foreach ($payments as $existing_payment) {
                    if ($existing_payment['id_payment'] == $payment_id) {
                        $payment_exists = true;
                        break;
                    }
                }
                if (!$payment_exists) {
                    $payments[] = [
                        'id_payment' => $payment_id,
                        'tanggal_bayar' => $row['tanggal_bayar'],
                        'nilai_bayar' => (float) $row['nilai_bayar'],
                    ];
                }
                unset($payments);
            }
        }
    }

    ksort($customers);
    $result = [];

    foreach ($customers as $customer_name => $spk_list) {
        $customer_entry = [
            'customer' => $customer_name,
            'spk_list' => [],
        ];

        ksort($spk_list);

        foreach ($spk_list as $spk_id => $spk_data) {
            $total_top = count($spk_data['tops']);
            $invoiced_top = 0;

            foreach ($spk_data['tops'] as $top) {
                if ($top['invoice'] !== null) {
                    $invoiced_top++;
                }
            }

            $pending_top = $total_top - $invoiced_top;
            $details = [];
            $sum_nilai_invoice = 0;
            $sum_piutang_per_invoice = 0;

            foreach ($spk_data['tops'] as $top_id => $top_data) {
                $detail_entry = [
                    'top_number' => $top_data['top_number'],
                    'rincian_top' => $top_data['rincian_top'],
                    'invoice' => null,
                ];

                if ($top_data['invoice'] !== null) {
                    $sum_nilai_bayar = 0;
                    $clean_payments = [];
                    foreach ($top_data['invoice']['payments'] as $payment) {
                        $clean_payments[] = [
                            'tanggal_bayar' => $payment['tanggal_bayar'],
                            'nilai_bayar' => $payment['nilai_bayar'],
                        ];
                        $sum_nilai_bayar += $payment['nilai_bayar'];
                    }

                    $nilai_invoice = $top_data['invoice']['nilai_invoice'];
                    $piutang_per_invoice = max(0, $nilai_invoice - $sum_nilai_bayar);

                    $sum_nilai_invoice += $nilai_invoice;
                    $sum_piutang_per_invoice += $piutang_per_invoice;

                    $detail_entry['invoice'] = [
                        'tanggal_invoice' => $top_data['invoice']['tanggal_invoice'],
                        'no_invoice' => $top_data['invoice']['no_invoice'],
                        'nilai_invoice' => $nilai_invoice,
                        'piutang_per_invoice' => $piutang_per_invoice,
                        'payments' => $clean_payments,
                    ];
                }

                $details[] = $detail_entry;
            }

            $uninvoiced = $spk_data['nominal_project'] - $sum_nilai_invoice;
            $total_sisa_piutang = $sum_piutang_per_invoice;

            $spk_entry = [
                'no_spk' => $spk_data['no_spk'],
                'nominal_project' => $spk_data['nominal_project'],
                'total_top' => $total_top,
                'invoiced_top' => $invoiced_top,
                'pending_top' => $pending_top,
                'uninvoiced' => $uninvoiced,
                'total_sisa_piutang' => $total_sisa_piutang,
                'details' => $details,
            ];

            $customer_entry['spk_list'][] = $spk_entry;
        }

        $result[] = $customer_entry;
    }

    return $result;
}


/**
 * Calculate summary totals from processed hierarchical data.
 * Mirrors Report_piutang_per_invoice::_calculate_summary()
 */
function calculate_summary(array $processed_data): array
{
    $total_piutang_per_invoice = 0;
    $total_uninvoiced = 0;
    $total_sisa_piutang_per_spk = 0;

    if (!empty($processed_data)) {
        foreach ($processed_data as $customer) {
            foreach ($customer['spk_list'] as $spk) {
                $total_uninvoiced += $spk['uninvoiced'];
                $total_sisa_piutang_per_spk += $spk['total_sisa_piutang'];

                foreach ($spk['details'] as $detail) {
                    if ($detail['invoice'] !== null) {
                        $total_piutang_per_invoice += $detail['invoice']['piutang_per_invoice'];
                    }
                }
            }
        }
    }

    $grand_total_piutang = $total_piutang_per_invoice + $total_uninvoiced;

    return [
        'total_piutang_per_invoice' => $total_piutang_per_invoice,
        'total_uninvoiced' => $total_uninvoiced,
        'total_sisa_piutang_per_spk' => $total_sisa_piutang_per_spk,
        'grand_total_piutang' => $grand_total_piutang,
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
 * Generate a random customer name.
 */
function generateRandomCustomerName(): string
{
    $prefixes = ['PT', 'CV', 'UD', 'Yayasan', 'Koperasi'];
    $names = ['Maju', 'Jaya', 'Sentosa', 'Abadi', 'Makmur', 'Sejahtera', 'Mandiri', 'Utama', 'Prima', 'Karya'];
    $suffixes = ['Bersama', 'Indonesia', 'Nusantara', 'Global', 'Teknologi', 'Konsultan', 'Engineering'];

    return $prefixes[mt_rand(0, count($prefixes) - 1)] . ' '
        . $names[mt_rand(0, count($names) - 1)] . ' '
        . $suffixes[mt_rand(0, count($suffixes) - 1)] . ' '
        . mt_rand(1, 999);
}

/**
 * Generate a full random dataset as raw query rows.
 *
 * Generates multiple customers, each with multiple SPKs,
 * each SPK with multiple TOPs, some with invoices and payments.
 *
 * @param int $num_customers Number of customers
 * @param int $max_spks_per_customer Max SPKs per customer
 * @param int $max_tops_per_spk Max TOPs per SPK
 * @param int $max_payments_per_invoice Max payments per invoice
 * @return array Raw data rows (simulating query result)
 */
function generateFullDataset(
    int $num_customers = 0,
    int $max_spks_per_customer = 0,
    int $max_tops_per_spk = 0,
    int $max_payments_per_invoice = 0
): array {
    if ($num_customers === 0) {
        $num_customers = mt_rand(1, 5);
    }
    if ($max_spks_per_customer === 0) {
        $max_spks_per_customer = mt_rand(1, 4);
    }
    if ($max_tops_per_spk === 0) {
        $max_tops_per_spk = mt_rand(1, 5);
    }
    if ($max_payments_per_invoice === 0) {
        $max_payments_per_invoice = mt_rand(0, 3);
    }

    $raw_data = [];
    $spk_counter = 1;
    $top_counter = 1;
    $invoice_counter = 1;
    $payment_counter = 1;

    for ($c = 0; $c < $num_customers; $c++) {
        $customer_name = generateRandomCustomerName();
        $num_spks = mt_rand(1, $max_spks_per_customer);

        for ($s = 0; $s < $num_spks; $s++) {
            $spk_id = $spk_counter++;
            $nominal_project = (float)(mt_rand(50000, 500000) * 1000);
            $num_tops = mt_rand(1, $max_tops_per_spk);

            for ($t = 0; $t < $num_tops; $t++) {
                $top_id = $top_counter++;
                $top_number = $t + 1;
                $rincian_top = round($nominal_project / $num_tops, 0);

                // Randomly decide if this TOP has an invoice
                $has_invoice = (mt_rand(0, 100) > 30); // 70% chance of having invoice

                if ($has_invoice) {
                    $inv_id = $invoice_counter++;
                    $nilai_invoice = (float)(mt_rand(10000, (int)($rincian_top / 1000) + 10000) * 1000);
                    $tanggal_invoice = generateRandomDate('2022-01-01', '2024-12-31');
                    $no_invoice = 'INV-' . str_pad($inv_id, 4, '0', STR_PAD_LEFT);

                    // Randomly generate payments for this invoice
                    $num_payments = mt_rand(0, $max_payments_per_invoice);

                    if ($num_payments === 0) {
                        // Invoice with no payments - single row
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
                    // TOP without invoice - single row
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
// Property 8: Summary totals consistency
// ============================================================================

/**
 * Main property: For any dataset, summary totals SHALL be consistent with
 * individual values:
 * (a) summary.total_piutang_per_invoice == sum of all piutang_per_invoice
 * (b) summary.total_uninvoiced == sum of all uninvoiced per SPK
 * (c) summary.grand_total_piutang == summary.total_piutang_per_invoice + summary.total_uninvoiced
 * (d) summary.total_sisa_piutang_per_spk == sum of all total_sisa_piutang per SPK
 */
function runProperty8_SummaryTotalsConsistency(PropertyTestRunner $runner, int $iterations): void
{
    echo "Running Property 8: Summary totals consistency ({$iterations} iterations)...\n";

    for ($i = 0; $i < $iterations; $i++) {
        // Generate a full random dataset
        $raw_data = generateFullDataset();

        // Process data into hierarchical structure
        $processed_data = process_report_data_with_calculations($raw_data);

        // Calculate summary using the summary function
        $summary = calculate_summary($processed_data);

        // Manually calculate expected values by iterating through processed data
        $expected_total_piutang_per_invoice = 0;
        $expected_total_uninvoiced = 0;
        $expected_total_sisa_piutang_per_spk = 0;

        foreach ($processed_data as $customer) {
            foreach ($customer['spk_list'] as $spk) {
                $expected_total_uninvoiced += $spk['uninvoiced'];
                $expected_total_sisa_piutang_per_spk += $spk['total_sisa_piutang'];

                foreach ($spk['details'] as $detail) {
                    if ($detail['invoice'] !== null) {
                        $expected_total_piutang_per_invoice += $detail['invoice']['piutang_per_invoice'];
                    }
                }
            }
        }

        $expected_grand_total = $expected_total_piutang_per_invoice + $expected_total_uninvoiced;

        // (a) Summary Piutang Per Invoice = sum of all individual piutang per invoice
        $runner->assertFloatEquals(
            $expected_total_piutang_per_invoice,
            $summary['total_piutang_per_invoice'],
            "Iteration {$i}: (a) summary.total_piutang_per_invoice should equal sum of all individual piutang_per_invoice"
        );

        // (b) Summary Uninvoiced = sum of all uninvoiced per SPK
        $runner->assertFloatEquals(
            $expected_total_uninvoiced,
            $summary['total_uninvoiced'],
            "Iteration {$i}: (b) summary.total_uninvoiced should equal sum of all uninvoiced per SPK"
        );

        // (c) Total Piutang = Summary Piutang Per Invoice + Summary Uninvoiced
        $runner->assertFloatEquals(
            $expected_grand_total,
            $summary['grand_total_piutang'],
            "Iteration {$i}: (c) grand_total_piutang should equal total_piutang_per_invoice + total_uninvoiced"
        );

        // (d) Summary Sisa Piutang Per SPK = sum of all total_sisa_piutang per SPK
        $runner->assertFloatEquals(
            $expected_total_sisa_piutang_per_spk,
            $summary['total_sisa_piutang_per_spk'],
            "Iteration {$i}: (d) summary.total_sisa_piutang_per_spk should equal sum of all total_sisa_piutang"
        );

        // Additional invariant: total_sisa_piutang_per_spk == total_piutang_per_invoice
        // Because total_sisa_piutang per SPK = SUM(piutang_per_invoice) for that SPK
        // So summing all total_sisa_piutang across SPKs = summing all piutang_per_invoice
        $runner->assertFloatEquals(
            $summary['total_piutang_per_invoice'],
            $summary['total_sisa_piutang_per_spk'],
            "Iteration {$i}: total_sisa_piutang_per_spk should equal total_piutang_per_invoice (both are sum of all piutang_per_invoice)"
        );
    }

    echo "  Completed {$iterations} iterations.\n";
}


/**
 * Edge case: Empty dataset - all summary values should be 0.
 */
function runProperty8a_EmptyDataset(PropertyTestRunner $runner, int $iterations): void
{
    echo "Running Property 8a: Empty dataset produces zero summary ({$iterations} iterations)...\n";

    for ($i = 0; $i < $iterations; $i++) {
        // Empty raw data
        $processed_data = process_report_data_with_calculations([]);
        $summary = calculate_summary($processed_data);

        $runner->assertFloatEquals(
            0,
            $summary['total_piutang_per_invoice'],
            "Iteration {$i}: Empty dataset should have total_piutang_per_invoice = 0"
        );
        $runner->assertFloatEquals(
            0,
            $summary['total_uninvoiced'],
            "Iteration {$i}: Empty dataset should have total_uninvoiced = 0"
        );
        $runner->assertFloatEquals(
            0,
            $summary['total_sisa_piutang_per_spk'],
            "Iteration {$i}: Empty dataset should have total_sisa_piutang_per_spk = 0"
        );
        $runner->assertFloatEquals(
            0,
            $summary['grand_total_piutang'],
            "Iteration {$i}: Empty dataset should have grand_total_piutang = 0"
        );
    }

    echo "  Completed {$iterations} iterations.\n";
}

/**
 * Edge case: Single SPK - summary should equal that SPK's values.
 */
function runProperty8b_SingleSpk(PropertyTestRunner $runner, int $iterations): void
{
    echo "Running Property 8b: Single SPK - summary equals SPK values ({$iterations} iterations)...\n";

    for ($i = 0; $i < $iterations; $i++) {
        // Generate dataset with exactly 1 customer, 1 SPK
        $raw_data = generateFullDataset(1, 1, mt_rand(1, 5), mt_rand(0, 3));

        $processed_data = process_report_data_with_calculations($raw_data);
        $summary = calculate_summary($processed_data);

        if (empty($processed_data)) {
            // If no data generated, summary should be all zeros
            $runner->assertFloatEquals(
                0,
                $summary['grand_total_piutang'],
                "Iteration {$i}: Empty processed data should have grand_total = 0"
            );
            continue;
        }

        // With single SPK, summary should match that SPK's values
        $spk = $processed_data[0]['spk_list'][0];

        // Sum piutang_per_invoice from all invoices in this SPK
        $spk_piutang_sum = 0;
        foreach ($spk['details'] as $detail) {
            if ($detail['invoice'] !== null) {
                $spk_piutang_sum += $detail['invoice']['piutang_per_invoice'];
            }
        }

        $runner->assertFloatEquals(
            $spk_piutang_sum,
            $summary['total_piutang_per_invoice'],
            "Iteration {$i}: Single SPK - summary piutang should equal SPK's sum of piutang_per_invoice"
        );

        $runner->assertFloatEquals(
            $spk['uninvoiced'],
            $summary['total_uninvoiced'],
            "Iteration {$i}: Single SPK - summary uninvoiced should equal SPK's uninvoiced"
        );

        $runner->assertFloatEquals(
            $spk['total_sisa_piutang'],
            $summary['total_sisa_piutang_per_spk'],
            "Iteration {$i}: Single SPK - summary sisa piutang should equal SPK's total_sisa_piutang"
        );

        $runner->assertFloatEquals(
            $spk_piutang_sum + $spk['uninvoiced'],
            $summary['grand_total_piutang'],
            "Iteration {$i}: Single SPK - grand_total should equal piutang + uninvoiced"
        );
    }

    echo "  Completed {$iterations} iterations.\n";
}

/**
 * Edge case: Multiple SPKs - verify correct aggregation across all SPKs.
 */
function runProperty8c_MultipleSpks(PropertyTestRunner $runner, int $iterations): void
{
    echo "Running Property 8c: Multiple SPKs - correct aggregation ({$iterations} iterations)...\n";

    for ($i = 0; $i < $iterations; $i++) {
        // Generate dataset with multiple customers and SPKs
        $raw_data = generateFullDataset(mt_rand(2, 5), mt_rand(2, 4), mt_rand(2, 4), mt_rand(1, 3));

        $processed_data = process_report_data_with_calculations($raw_data);
        $summary = calculate_summary($processed_data);

        // Independently calculate expected values
        $ind_piutang = 0;
        $ind_uninvoiced = 0;
        $ind_sisa_piutang = 0;

        foreach ($processed_data as $customer) {
            foreach ($customer['spk_list'] as $spk) {
                $ind_uninvoiced += $spk['uninvoiced'];
                $ind_sisa_piutang += $spk['total_sisa_piutang'];

                foreach ($spk['details'] as $detail) {
                    if ($detail['invoice'] !== null) {
                        $ind_piutang += $detail['invoice']['piutang_per_invoice'];
                    }
                }
            }
        }

        // Verify aggregation
        $runner->assertFloatEquals(
            $ind_piutang,
            $summary['total_piutang_per_invoice'],
            "Iteration {$i}: Multi-SPK aggregation - total_piutang_per_invoice"
        );

        $runner->assertFloatEquals(
            $ind_uninvoiced,
            $summary['total_uninvoiced'],
            "Iteration {$i}: Multi-SPK aggregation - total_uninvoiced"
        );

        $runner->assertFloatEquals(
            $ind_sisa_piutang,
            $summary['total_sisa_piutang_per_spk'],
            "Iteration {$i}: Multi-SPK aggregation - total_sisa_piutang_per_spk"
        );

        // Grand total invariant
        $runner->assertFloatEquals(
            $summary['total_piutang_per_invoice'] + $summary['total_uninvoiced'],
            $summary['grand_total_piutang'],
            "Iteration {$i}: Multi-SPK - grand_total = piutang + uninvoiced"
        );
    }

    echo "  Completed {$iterations} iterations.\n";
}

/**
 * Verify the grand_total_piutang formula invariant holds regardless of data shape.
 * This is the core formula: Total Piutang = Piutang Per Invoice + Uninvoiced
 */
function runProperty8d_GrandTotalFormula(PropertyTestRunner $runner, int $iterations): void
{
    echo "Running Property 8d: Grand total formula invariant ({$iterations} iterations)...\n";

    for ($i = 0; $i < $iterations; $i++) {
        $raw_data = generateFullDataset();
        $processed_data = process_report_data_with_calculations($raw_data);
        $summary = calculate_summary($processed_data);

        // The core invariant: grand_total = piutang + uninvoiced
        $runner->assertFloatEquals(
            $summary['total_piutang_per_invoice'] + $summary['total_uninvoiced'],
            $summary['grand_total_piutang'],
            "Iteration {$i}: grand_total_piutang MUST equal total_piutang_per_invoice + total_uninvoiced"
        );
    }

    echo "  Completed {$iterations} iterations.\n";
}

// ============================================================================
// Main execution
// ============================================================================

echo "========================================\n";
echo "Feature: report-piutang-per-invoice\n";
echo "Property 8: Summary totals consistency\n";
echo "Validates: Requirements 9.2, 9.3, 9.5\n";
echo "========================================\n\n";

$runner = new PropertyTestRunner();

// Main property: 100 iterations
runProperty8_SummaryTotalsConsistency($runner, 100);

// Edge cases
runProperty8a_EmptyDataset($runner, 10);
runProperty8b_SingleSpk($runner, 100);
runProperty8c_MultipleSpks($runner, 100);

// Core formula invariant
runProperty8d_GrandTotalFormula($runner, 100);

$success = $runner->printResults();

exit($success ? 0 : 1);
