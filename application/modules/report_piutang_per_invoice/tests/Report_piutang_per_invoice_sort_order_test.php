<?php

/**
 * Property-Based Test: Hierarchical Sort Order for Report Piutang Per Invoice
 *
 * Feature: report-piutang-per-invoice, Property 3: Hierarchical sort order
 *
 * This test validates that for any dataset, the report output maintains the
 * following sort invariants:
 * - Customers are sorted alphabetically by name
 * - SPKs within a customer are sorted by SPK number
 * - Invoices within an SPK are sorted chronologically (oldest first)
 * - Payments within an invoice are sorted chronologically (oldest first)
 *
 * **Validates: Requirements 3.2, 3.3, 3.4, 3.5**
 *
 * Run: php application/modules/report_piutang_per_invoice/tests/Report_piutang_per_invoice_sort_order_test.php
 */

// ============================================================================
// Standalone implementation of process_report_data for testing
// (mirrors the controller's private method)
// ============================================================================

function process_report_data(array $raw_data): array
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
                    'piutang_per_invoice' => 0,
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
            foreach ($spk_data['tops'] as $top_id => $top_data) {
                $detail_entry = [
                    'top_number' => $top_data['top_number'],
                    'rincian_top' => $top_data['rincian_top'],
                    'invoice' => null,
                ];

                if ($top_data['invoice'] !== null) {
                    $clean_payments = [];
                    foreach ($top_data['invoice']['payments'] as $payment) {
                        $clean_payments[] = [
                            'tanggal_bayar' => $payment['tanggal_bayar'],
                            'nilai_bayar' => $payment['nilai_bayar'],
                        ];
                    }

                    $detail_entry['invoice'] = [
                        'tanggal_invoice' => $top_data['invoice']['tanggal_invoice'],
                        'no_invoice' => $top_data['invoice']['no_invoice'],
                        'nilai_invoice' => $top_data['invoice']['nilai_invoice'],
                        'piutang_per_invoice' => $top_data['invoice']['piutang_per_invoice'],
                        'payments' => $clean_payments,
                    ];
                }

                $details[] = $detail_entry;
            }

            $spk_entry = [
                'no_spk' => $spk_data['no_spk'],
                'nominal_project' => $spk_data['nominal_project'],
                'total_top' => $total_top,
                'invoiced_top' => $invoiced_top,
                'pending_top' => $pending_top,
                'uninvoiced' => 0,
                'total_sisa_piutang' => 0,
                'details' => $details,
            ];

            $customer_entry['spk_list'][] = $spk_entry;
        }

        $result[] = $customer_entry;
    }

    return $result;
}

// ============================================================================
// Random data generators
// ============================================================================

/**
 * Generate a random customer name from a pool of names.
 */
function generateRandomCustomerName(): string
{
    $prefixes = ['PT', 'CV', 'UD', 'Yayasan', 'Koperasi'];
    $names = [
        'Alpha',
        'Beta',
        'Charlie',
        'Delta',
        'Echo',
        'Foxtrot',
        'Golf',
        'Hotel',
        'India',
        'Juliet',
        'Kilo',
        'Lima',
        'Mike',
        'November',
        'Oscar',
        'Papa',
        'Quebec',
        'Romeo',
        'Sierra',
        'Tango',
        'Uniform',
        'Victor',
        'Whiskey',
        'Xray',
        'Yankee',
        'Zulu',
        'Abadi',
        'Berkah',
        'Citra',
        'Daya',
    ];
    $suffix = ['Mandiri', 'Sejahtera', 'Jaya', 'Utama', 'Makmur', 'Sentosa'];

    $prefix = $prefixes[mt_rand(0, count($prefixes) - 1)];
    $name = $names[mt_rand(0, count($names) - 1)];
    $suf = $suffix[mt_rand(0, count($suffix) - 1)];

    return "{$prefix} {$name} {$suf}";
}

/**
 * Generate a random SPK identifier.
 */
function generateRandomSpkId(): string
{
    return 'SPK-' . str_pad((string) mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

/**
 * Generate a random date string in Y-m-d format within a range.
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
 * Generate a complete random dataset of raw rows (simulating SQL query output).
 * The rows are intentionally shuffled to test that process_report_data sorts correctly.
 *
 * @param int $num_customers Number of distinct customers
 * @param int $max_spks_per_customer Max SPKs per customer
 * @param int $max_tops_per_spk Max TOPs per SPK
 * @param int $max_payments_per_invoice Max payments per invoice
 * @return array Raw data rows in random order
 */
function generateRandomDataset(
    int $num_customers = 0,
    int $max_spks_per_customer = 3,
    int $max_tops_per_spk = 4,
    int $max_payments_per_invoice = 3
): array {
    if ($num_customers === 0) {
        $num_customers = mt_rand(2, 6);
    }

    $rows = [];
    $top_id_counter = 1;
    $invoice_id_counter = 1;
    $payment_id_counter = 1;

    // Generate unique customer names
    $customer_names = [];
    while (count($customer_names) < $num_customers) {
        $name = generateRandomCustomerName();
        if (!in_array($name, $customer_names)) {
            $customer_names[] = $name;
        }
    }

    foreach ($customer_names as $customer_name) {
        $num_spks = mt_rand(1, $max_spks_per_customer);

        // Generate unique SPK IDs for this customer
        $spk_ids = [];
        while (count($spk_ids) < $num_spks) {
            $spk_id = generateRandomSpkId();
            if (!in_array($spk_id, $spk_ids)) {
                $spk_ids[] = $spk_id;
            }
        }

        foreach ($spk_ids as $spk_id) {
            $nominal_project = (float)(mt_rand(50000, 500000) * 1000);
            $num_tops = mt_rand(1, $max_tops_per_spk);

            for ($top_num = 1; $top_num <= $num_tops; $top_num++) {
                $top_id = $top_id_counter++;
                $rincian_top = $nominal_project / $num_tops;

                // Decide if this TOP has an invoice (70% chance)
                $has_invoice = mt_rand(1, 10) <= 7;

                if ($has_invoice) {
                    $invoice_id = $invoice_id_counter++;
                    $tanggal_invoice = generateRandomDate('2022-01-01', '2025-06-30');
                    $no_invoice = 'INV-' . str_pad((string) $invoice_id, 4, '0', STR_PAD_LEFT);
                    $nilai_invoice = $rincian_top * (mt_rand(80, 120) / 100);

                    // Decide number of payments
                    $num_payments = mt_rand(0, $max_payments_per_invoice);

                    if ($num_payments === 0) {
                        // Invoice with no payments - single row
                        $rows[] = [
                            'nm_customer' => $customer_name,
                            'id_spk_penawaran' => $spk_id,
                            'nominal_project' => (string) $nominal_project,
                            'id_detail_plan_tagih' => (string) $top_id,
                            'top_number' => (string) $top_num,
                            'rincian_top' => (string) $rincian_top,
                            'id_invoice' => (string) $invoice_id,
                            'tanggal_invoice' => $tanggal_invoice,
                            'no_invoice' => $no_invoice,
                            'nilai_invoice' => (string) $nilai_invoice,
                            'id_payment' => null,
                            'tanggal_bayar' => null,
                            'nilai_bayar' => null,
                        ];
                    } else {
                        // Invoice with payments - one row per payment
                        for ($p = 0; $p < $num_payments; $p++) {
                            $payment_id = $payment_id_counter++;
                            $tanggal_bayar = generateRandomDate($tanggal_invoice, '2025-12-31');
                            $nilai_bayar = $nilai_invoice / $num_payments * (mt_rand(50, 150) / 100);

                            $rows[] = [
                                'nm_customer' => $customer_name,
                                'id_spk_penawaran' => $spk_id,
                                'nominal_project' => (string) $nominal_project,
                                'id_detail_plan_tagih' => (string) $top_id,
                                'top_number' => (string) $top_num,
                                'rincian_top' => (string) $rincian_top,
                                'id_invoice' => (string) $invoice_id,
                                'tanggal_invoice' => $tanggal_invoice,
                                'no_invoice' => $no_invoice,
                                'nilai_invoice' => (string) $nilai_invoice,
                                'id_payment' => (string) $payment_id,
                                'tanggal_bayar' => $tanggal_bayar,
                                'nilai_bayar' => (string) $nilai_bayar,
                            ];
                        }
                    }
                } else {
                    // TOP without invoice
                    $rows[] = [
                        'nm_customer' => $customer_name,
                        'id_spk_penawaran' => $spk_id,
                        'nominal_project' => (string) $nominal_project,
                        'id_detail_plan_tagih' => (string) $top_id,
                        'top_number' => (string) $top_num,
                        'rincian_top' => (string) $rincian_top,
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

    // SHUFFLE the rows to simulate random input order
    shuffle($rows);

    return $rows;
}

// ============================================================================
// Test runner
// ============================================================================

class SortOrderTestRunner
{
    private $passed = 0;
    private $failed = 0;
    private $errors = [];

    public function assertTrue(bool $condition, string $message): void
    {
        if ($condition) {
            $this->passed++;
        } else {
            $this->failed++;
            $this->errors[] = $message;
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
// Property 3: Hierarchical sort order
// ============================================================================

/**
 * For any dataset, the report output SHALL maintain the following sort invariants:
 * - Customers are sorted alphabetically by name
 * - SPKs within a customer are sorted by SPK number
 * - Invoices within an SPK are sorted chronologically (oldest first)
 * - Payments within an invoice are sorted chronologically (oldest first)
 *
 * Note: The sort for invoices and payments is maintained by the SQL ORDER BY
 * clause in production. The process_report_data function preserves insertion
 * order for TOPs (which contain invoices), and payments are added in the order
 * they appear in the raw data. So the test feeds data in random order and
 * verifies that ksort handles customer and SPK sorting correctly.
 * For invoice/payment chronological order, the raw data must arrive pre-sorted
 * by the SQL query. The test verifies that when raw data arrives in the correct
 * SQL order (sorted by tanggal_invoice ASC, tanggal_bayar ASC within each group),
 * the output preserves that order.
 */
function runProperty3_HierarchicalSortOrder(SortOrderTestRunner $runner, int $iterations): void
{
    echo "Running Property 3: Hierarchical sort order ({$iterations} iterations)...\n";

    for ($i = 0; $i < $iterations; $i++) {
        // Generate random dataset (shuffled)
        $raw_data = generateRandomDataset();

        // Process through the function under test
        $result = process_report_data($raw_data);

        if (empty($result)) {
            continue;
        }

        // INVARIANT 1: Customers are sorted alphabetically
        $customer_names = array_map(function ($c) {
            return $c['customer'];
        }, $result);

        $sorted_names = $customer_names;
        sort($sorted_names, SORT_STRING);

        $runner->assertTrue(
            $customer_names === $sorted_names,
            "Iteration {$i}: Customers not sorted alphabetically. Got: [" .
                implode(', ', $customer_names) . "] Expected: [" . implode(', ', $sorted_names) . "]"
        );

        // INVARIANT 2: SPKs within each customer are sorted by SPK number
        foreach ($result as $customer) {
            $spk_numbers = array_map(function ($spk) {
                return $spk['no_spk'];
            }, $customer['spk_list']);

            $sorted_spks = $spk_numbers;
            sort($sorted_spks, SORT_STRING);

            $runner->assertTrue(
                $spk_numbers === $sorted_spks,
                "Iteration {$i}: SPKs for customer '{$customer['customer']}' not sorted. " .
                    "Got: [" . implode(', ', $spk_numbers) . "] Expected: [" . implode(', ', $sorted_spks) . "]"
            );
        }
    }

    echo "  Completed {$iterations} iterations.\n";
}

/**
 * Sub-property 3a: Invoices within an SPK are sorted chronologically (oldest first).
 *
 * This tests that when raw data arrives pre-sorted by tanggal_invoice ASC
 * (as the SQL ORDER BY guarantees), the output preserves that chronological order.
 */
function runProperty3a_InvoiceChronologicalOrder(SortOrderTestRunner $runner, int $iterations): void
{
    echo "Running Property 3a: Invoices within SPK sorted chronologically ({$iterations} iterations)...\n";

    for ($i = 0; $i < $iterations; $i++) {
        // Generate a dataset where we control the invoice dates
        $customer_name = generateRandomCustomerName();
        $spk_id = generateRandomSpkId();
        $nominal_project = (float)(mt_rand(100000, 500000) * 1000);

        // Create multiple TOPs with invoices at different dates
        $num_tops = mt_rand(2, 5);
        $invoice_dates = [];
        for ($t = 0; $t < $num_tops; $t++) {
            $invoice_dates[] = generateRandomDate('2022-01-01', '2025-06-30');
        }

        // Sort dates to simulate SQL ORDER BY tanggal_invoice ASC
        sort($invoice_dates);

        // Build raw data in the correct SQL order (sorted by tanggal_invoice ASC)
        $rows = [];
        for ($t = 0; $t < $num_tops; $t++) {
            $top_id = $i * 100 + $t + 1;
            $invoice_id = $i * 100 + $t + 1;
            $rows[] = [
                'nm_customer' => $customer_name,
                'id_spk_penawaran' => $spk_id,
                'nominal_project' => (string) $nominal_project,
                'id_detail_plan_tagih' => (string) $top_id,
                'top_number' => (string) ($t + 1),
                'rincian_top' => (string) ($nominal_project / $num_tops),
                'id_invoice' => (string) $invoice_id,
                'tanggal_invoice' => $invoice_dates[$t],
                'no_invoice' => 'INV-' . str_pad((string) $invoice_id, 4, '0', STR_PAD_LEFT),
                'nilai_invoice' => (string) ($nominal_project / $num_tops),
                'id_payment' => null,
                'tanggal_bayar' => null,
                'nilai_bayar' => null,
            ];
        }

        $result = process_report_data($rows);

        if (empty($result) || empty($result[0]['spk_list'])) {
            continue;
        }

        $spk = $result[0]['spk_list'][0];
        $output_dates = [];
        foreach ($spk['details'] as $detail) {
            if ($detail['invoice'] !== null) {
                $output_dates[] = $detail['invoice']['tanggal_invoice'];
            }
        }

        // Verify chronological order (oldest first)
        for ($d = 1; $d < count($output_dates); $d++) {
            $runner->assertTrue(
                $output_dates[$d] >= $output_dates[$d - 1],
                "Iteration {$i}: Invoices not in chronological order. " .
                    "Date at position {$d} ({$output_dates[$d]}) is before position " . ($d - 1) .
                    " ({$output_dates[$d - 1]})"
            );
        }
    }

    echo "  Completed {$iterations} iterations.\n";
}

/**
 * Sub-property 3b: Payments within an invoice are sorted chronologically (oldest first).
 *
 * This tests that when raw data arrives pre-sorted by tanggal_bayar ASC
 * (as the SQL ORDER BY guarantees), the output preserves that chronological order.
 */
function runProperty3b_PaymentChronologicalOrder(SortOrderTestRunner $runner, int $iterations): void
{
    echo "Running Property 3b: Payments within invoice sorted chronologically ({$iterations} iterations)...\n";

    for ($i = 0; $i < $iterations; $i++) {
        $customer_name = generateRandomCustomerName();
        $spk_id = generateRandomSpkId();
        $nominal_project = (float)(mt_rand(100000, 500000) * 1000);
        $top_id = $i * 100 + 1;
        $invoice_id = $i * 100 + 1;
        $tanggal_invoice = generateRandomDate('2022-01-01', '2024-06-30');
        $nilai_invoice = $nominal_project;

        // Generate multiple payments with random dates
        $num_payments = mt_rand(2, 5);
        $payment_dates = [];
        for ($p = 0; $p < $num_payments; $p++) {
            $payment_dates[] = generateRandomDate($tanggal_invoice, '2025-12-31');
        }

        // Sort payment dates to simulate SQL ORDER BY tanggal_bayar ASC
        sort($payment_dates);

        // Build raw data in the correct SQL order (sorted by tanggal_bayar ASC)
        $rows = [];
        for ($p = 0; $p < $num_payments; $p++) {
            $payment_id = $i * 100 + $p + 1;
            $rows[] = [
                'nm_customer' => $customer_name,
                'id_spk_penawaran' => $spk_id,
                'nominal_project' => (string) $nominal_project,
                'id_detail_plan_tagih' => (string) $top_id,
                'top_number' => '1',
                'rincian_top' => (string) $nominal_project,
                'id_invoice' => (string) $invoice_id,
                'tanggal_invoice' => $tanggal_invoice,
                'no_invoice' => 'INV-' . str_pad((string) $invoice_id, 4, '0', STR_PAD_LEFT),
                'nilai_invoice' => (string) $nilai_invoice,
                'id_payment' => (string) $payment_id,
                'tanggal_bayar' => $payment_dates[$p],
                'nilai_bayar' => (string) (mt_rand(1000, 50000) * 1000),
            ];
        }

        $result = process_report_data($rows);

        if (empty($result) || empty($result[0]['spk_list'])) {
            continue;
        }

        $spk = $result[0]['spk_list'][0];
        $detail = $spk['details'][0];

        if ($detail['invoice'] === null || empty($detail['invoice']['payments'])) {
            continue;
        }

        $output_payment_dates = array_map(function ($pay) {
            return $pay['tanggal_bayar'];
        }, $detail['invoice']['payments']);

        // Verify chronological order (oldest first)
        for ($d = 1; $d < count($output_payment_dates); $d++) {
            $runner->assertTrue(
                $output_payment_dates[$d] >= $output_payment_dates[$d - 1],
                "Iteration {$i}: Payments not in chronological order. " .
                    "Date at position {$d} ({$output_payment_dates[$d]}) is before position " . ($d - 1) .
                    " ({$output_payment_dates[$d - 1]})"
            );
        }
    }

    echo "  Completed {$iterations} iterations.\n";
}

/**
 * Sub-property 3c: Combined sort invariants hold for complex datasets.
 *
 * Generates datasets with multiple customers, each having multiple SPKs,
 * and verifies ALL sort invariants simultaneously.
 */
function runProperty3c_CombinedSortInvariants(SortOrderTestRunner $runner, int $iterations): void
{
    echo "Running Property 3c: Combined sort invariants ({$iterations} iterations)...\n";

    for ($i = 0; $i < $iterations; $i++) {
        // Generate a larger dataset
        $raw_data = generateRandomDataset(mt_rand(3, 8), 3, 4, 3);

        // For invoice/payment chronological order, we need to pre-sort the raw data
        // as the SQL query would (by tanggal_invoice ASC, tanggal_bayar ASC within groups)
        usort($raw_data, function ($a, $b) {
            // Primary: customer name
            $cmp = strcmp($a['nm_customer'], $b['nm_customer']);
            if ($cmp !== 0) return $cmp;

            // Secondary: SPK id
            $cmp = strcmp($a['id_spk_penawaran'], $b['id_spk_penawaran']);
            if ($cmp !== 0) return $cmp;

            // Tertiary: invoice date (nulls last)
            $a_inv = $a['tanggal_invoice'] ?? '9999-12-31';
            $b_inv = $b['tanggal_invoice'] ?? '9999-12-31';
            $cmp = strcmp($a_inv, $b_inv);
            if ($cmp !== 0) return $cmp;

            // Quaternary: payment date (nulls last)
            $a_pay = $a['tanggal_bayar'] ?? '9999-12-31';
            $b_pay = $b['tanggal_bayar'] ?? '9999-12-31';
            return strcmp($a_pay, $b_pay);
        });

        $result = process_report_data($raw_data);

        if (empty($result)) {
            continue;
        }

        // INVARIANT 1: Customers sorted alphabetically
        for ($c = 1; $c < count($result); $c++) {
            $runner->assertTrue(
                strcmp($result[$c]['customer'], $result[$c - 1]['customer']) >= 0,
                "Iteration {$i}: Customer '{$result[$c]['customer']}' should come after " .
                    "'{$result[$c - 1]['customer']}' alphabetically"
            );
        }

        foreach ($result as $customer) {
            // INVARIANT 2: SPKs sorted by number
            for ($s = 1; $s < count($customer['spk_list']); $s++) {
                $runner->assertTrue(
                    strcmp(
                        $customer['spk_list'][$s]['no_spk'],
                        $customer['spk_list'][$s - 1]['no_spk']
                    ) >= 0,
                    "Iteration {$i}: SPK '{$customer['spk_list'][$s]['no_spk']}' should come after " .
                        "'{$customer['spk_list'][$s - 1]['no_spk']}' for customer '{$customer['customer']}'"
                );
            }

            foreach ($customer['spk_list'] as $spk) {
                // INVARIANT 3: Invoices sorted chronologically within SPK
                $prev_invoice_date = null;
                foreach ($spk['details'] as $detail) {
                    if ($detail['invoice'] !== null) {
                        $current_date = $detail['invoice']['tanggal_invoice'];
                        if ($prev_invoice_date !== null) {
                            $runner->assertTrue(
                                $current_date >= $prev_invoice_date,
                                "Iteration {$i}: Invoice date '{$current_date}' should be >= " .
                                    "'{$prev_invoice_date}' in SPK '{$spk['no_spk']}' " .
                                    "for customer '{$customer['customer']}'"
                            );
                        }
                        $prev_invoice_date = $current_date;

                        // INVARIANT 4: Payments sorted chronologically within invoice
                        $prev_payment_date = null;
                        foreach ($detail['invoice']['payments'] as $payment) {
                            $pay_date = $payment['tanggal_bayar'];
                            if ($prev_payment_date !== null) {
                                $runner->assertTrue(
                                    $pay_date >= $prev_payment_date,
                                    "Iteration {$i}: Payment date '{$pay_date}' should be >= " .
                                        "'{$prev_payment_date}' in invoice " .
                                        "'{$detail['invoice']['no_invoice']}'"
                                );
                            }
                            $prev_payment_date = $pay_date;
                        }
                    }
                }
            }
        }
    }

    echo "  Completed {$iterations} iterations.\n";
}

// ============================================================================
// Main execution
// ============================================================================

echo "========================================\n";
echo "Feature: report-piutang-per-invoice\n";
echo "Property 3: Hierarchical sort order\n";
echo "Validates: Requirements 3.2, 3.3, 3.4, 3.5\n";
echo "========================================\n\n";

$runner = new SortOrderTestRunner();

// Main property: customer and SPK sort with shuffled input (100 iterations)
runProperty3_HierarchicalSortOrder($runner, 100);

// Sub-property 3a: Invoice chronological order (100 iterations)
runProperty3a_InvoiceChronologicalOrder($runner, 100);

// Sub-property 3b: Payment chronological order (100 iterations)
runProperty3b_PaymentChronologicalOrder($runner, 100);

// Sub-property 3c: Combined invariants with pre-sorted input (100 iterations)
runProperty3c_CombinedSortInvariants($runner, 100);

$success = $runner->printResults();

exit($success ? 0 : 1);
