<?php

/**
 * Unit Test: Data Grouping and Hierarchical Structure
 *
 * Tests the _process_report_data() method which transforms flat query results
 * into hierarchical structure: Customer → SPK → TOP/Invoice → Payment
 *
 * Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7
 *
 * Run: php application/modules/report_piutang_per_invoice/tests/Report_piutang_per_invoice_grouping_test.php
 */

// ============================================================================
// Standalone implementation of _process_report_data for testing
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
// Test runner
// ============================================================================

class GroupingTestRunner
{
    private $passed = 0;
    private $failed = 0;
    private $errors = [];

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
        if ($condition) {
            $this->passed++;
        } else {
            $this->failed++;
            $this->errors[] = $message;
        }
    }

    public function assertNull($value, string $message): void
    {
        if ($value === null) {
            $this->passed++;
        } else {
            $this->failed++;
            $this->errors[] = "{$message} — expected null, got " . var_export($value, true);
        }
    }

    public function assertCount(int $expected, array $arr, string $message): void
    {
        $actual = count($arr);
        if ($expected === $actual) {
            $this->passed++;
        } else {
            $this->failed++;
            $this->errors[] = "{$message} — expected count {$expected}, got {$actual}";
        }
    }

    public function printResults(): bool
    {
        echo "\n========================================\n";
        echo "Grouping Test Results\n";
        echo "========================================\n";
        echo "Assertions passed: {$this->passed}\n";
        echo "Assertions failed: {$this->failed}\n";

        if (!empty($this->errors)) {
            echo "\nFAILURES:\n";
            foreach (array_slice($this->errors, 0, 20) as $i => $error) {
                echo "  " . ($i + 1) . ") {$error}\n";
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
// Test cases
// ============================================================================

function test_empty_data(GroupingTestRunner $runner): void
{
    echo "  Test: Empty data returns empty array...\n";
    $result = process_report_data([]);
    $runner->assertEquals([], $result, "Empty input should return empty array");
}

function test_single_customer_single_spk_single_top_with_invoice_and_payment(GroupingTestRunner $runner): void
{
    echo "  Test: Single customer, single SPK, single TOP with invoice and payment...\n";

    $raw_data = [
        [
            'nm_customer' => 'PT ABC',
            'id_spk_penawaran' => 'SPK-001',
            'nominal_project' => '100000000',
            'id_detail_plan_tagih' => '101',
            'top_number' => '1',
            'rincian_top' => '35000000',
            'id_invoice' => '201',
            'tanggal_invoice' => '2024-01-15',
            'no_invoice' => 'INV-001',
            'nilai_invoice' => '35000000',
            'id_payment' => '301',
            'tanggal_bayar' => '2024-02-01',
            'nilai_bayar' => '25000000',
        ],
    ];

    $result = process_report_data($raw_data);

    $runner->assertCount(1, $result, "Should have 1 customer");
    $runner->assertEquals('PT ABC', $result[0]['customer'], "Customer name");
    $runner->assertCount(1, $result[0]['spk_list'], "Should have 1 SPK");

    $spk = $result[0]['spk_list'][0];
    $runner->assertEquals('SPK-001', $spk['no_spk'], "SPK number");
    $runner->assertEquals(100000000.0, $spk['nominal_project'], "Nominal project");
    $runner->assertEquals(1, $spk['total_top'], "Total TOP");
    $runner->assertEquals(1, $spk['invoiced_top'], "Invoiced TOP");
    $runner->assertEquals(0, $spk['pending_top'], "Pending TOP");

    $runner->assertCount(1, $spk['details'], "Should have 1 detail");
    $detail = $spk['details'][0];
    $runner->assertEquals(1, $detail['top_number'], "TOP number");
    $runner->assertEquals(35000000.0, $detail['rincian_top'], "Rincian TOP");
    $runner->assertTrue($detail['invoice'] !== null, "Invoice should not be null");
    $runner->assertEquals('2024-01-15', $detail['invoice']['tanggal_invoice'], "Tanggal invoice");
    $runner->assertEquals('INV-001', $detail['invoice']['no_invoice'], "No invoice");
    $runner->assertEquals(35000000.0, $detail['invoice']['nilai_invoice'], "Nilai invoice");

    $runner->assertCount(1, $detail['invoice']['payments'], "Should have 1 payment");
    $runner->assertEquals('2024-02-01', $detail['invoice']['payments'][0]['tanggal_bayar'], "Tanggal bayar");
    $runner->assertEquals(25000000.0, $detail['invoice']['payments'][0]['nilai_bayar'], "Nilai bayar");
}

function test_spk_without_invoice(GroupingTestRunner $runner): void
{
    echo "  Test: SPK without invoice (TOP has no invoice)...\n";

    $raw_data = [
        [
            'nm_customer' => 'PT XYZ',
            'id_spk_penawaran' => 'SPK-002',
            'nominal_project' => '50000000',
            'id_detail_plan_tagih' => '102',
            'top_number' => '1',
            'rincian_top' => '25000000',
            'id_invoice' => null,
            'tanggal_invoice' => null,
            'no_invoice' => null,
            'nilai_invoice' => null,
            'id_payment' => null,
            'tanggal_bayar' => null,
            'nilai_bayar' => null,
        ],
    ];

    $result = process_report_data($raw_data);

    $runner->assertCount(1, $result, "Should have 1 customer");
    $spk = $result[0]['spk_list'][0];
    $runner->assertEquals(1, $spk['total_top'], "Total TOP");
    $runner->assertEquals(0, $spk['invoiced_top'], "Invoiced TOP should be 0");
    $runner->assertEquals(1, $spk['pending_top'], "Pending TOP should be 1");

    $detail = $spk['details'][0];
    $runner->assertNull($detail['invoice'], "Invoice should be null for uninvoiced TOP");
}

function test_invoice_without_payment(GroupingTestRunner $runner): void
{
    echo "  Test: Invoice without payment...\n";

    $raw_data = [
        [
            'nm_customer' => 'PT DEF',
            'id_spk_penawaran' => 'SPK-003',
            'nominal_project' => '80000000',
            'id_detail_plan_tagih' => '103',
            'top_number' => '1',
            'rincian_top' => '40000000',
            'id_invoice' => '202',
            'tanggal_invoice' => '2024-03-01',
            'no_invoice' => 'INV-002',
            'nilai_invoice' => '40000000',
            'id_payment' => null,
            'tanggal_bayar' => null,
            'nilai_bayar' => null,
        ],
    ];

    $result = process_report_data($raw_data);

    $spk = $result[0]['spk_list'][0];
    $detail = $spk['details'][0];
    $runner->assertTrue($detail['invoice'] !== null, "Invoice should exist");
    $runner->assertCount(0, $detail['invoice']['payments'], "Payments should be empty");
}


function test_multiple_payments_deduplication(GroupingTestRunner $runner): void
{
    echo "  Test: Multiple payments per invoice (deduplication)...\n";

    // Same TOP/invoice appears multiple times due to JOIN with multiple payments
    $raw_data = [
        [
            'nm_customer' => 'PT GHI',
            'id_spk_penawaran' => 'SPK-004',
            'nominal_project' => '120000000',
            'id_detail_plan_tagih' => '104',
            'top_number' => '1',
            'rincian_top' => '60000000',
            'id_invoice' => '203',
            'tanggal_invoice' => '2024-01-10',
            'no_invoice' => 'INV-003',
            'nilai_invoice' => '60000000',
            'id_payment' => '302',
            'tanggal_bayar' => '2024-02-15',
            'nilai_bayar' => '20000000',
        ],
        [
            'nm_customer' => 'PT GHI',
            'id_spk_penawaran' => 'SPK-004',
            'nominal_project' => '120000000',
            'id_detail_plan_tagih' => '104',
            'top_number' => '1',
            'rincian_top' => '60000000',
            'id_invoice' => '203',
            'tanggal_invoice' => '2024-01-10',
            'no_invoice' => 'INV-003',
            'nilai_invoice' => '60000000',
            'id_payment' => '303',
            'tanggal_bayar' => '2024-03-20',
            'nilai_bayar' => '30000000',
        ],
    ];

    $result = process_report_data($raw_data);

    $spk = $result[0]['spk_list'][0];
    $runner->assertEquals(1, $spk['total_top'], "Should have 1 TOP (not duplicated)");
    $runner->assertEquals(1, $spk['invoiced_top'], "Should have 1 invoiced TOP");

    $detail = $spk['details'][0];
    $runner->assertCount(2, $detail['invoice']['payments'], "Should have 2 payments");
    $runner->assertEquals(20000000.0, $detail['invoice']['payments'][0]['nilai_bayar'], "First payment");
    $runner->assertEquals(30000000.0, $detail['invoice']['payments'][1]['nilai_bayar'], "Second payment");
}

function test_multiple_customers_sorted_alphabetically(GroupingTestRunner $runner): void
{
    echo "  Test: Multiple customers sorted alphabetically...\n";

    $raw_data = [
        [
            'nm_customer' => 'PT Zebra',
            'id_spk_penawaran' => 'SPK-010',
            'nominal_project' => '50000000',
            'id_detail_plan_tagih' => '110',
            'top_number' => '1',
            'rincian_top' => '50000000',
            'id_invoice' => null,
            'tanggal_invoice' => null,
            'no_invoice' => null,
            'nilai_invoice' => null,
            'id_payment' => null,
            'tanggal_bayar' => null,
            'nilai_bayar' => null,
        ],
        [
            'nm_customer' => 'PT Alpha',
            'id_spk_penawaran' => 'SPK-011',
            'nominal_project' => '60000000',
            'id_detail_plan_tagih' => '111',
            'top_number' => '1',
            'rincian_top' => '60000000',
            'id_invoice' => null,
            'tanggal_invoice' => null,
            'no_invoice' => null,
            'nilai_invoice' => null,
            'id_payment' => null,
            'tanggal_bayar' => null,
            'nilai_bayar' => null,
        ],
        [
            'nm_customer' => 'PT Beta',
            'id_spk_penawaran' => 'SPK-012',
            'nominal_project' => '70000000',
            'id_detail_plan_tagih' => '112',
            'top_number' => '1',
            'rincian_top' => '70000000',
            'id_invoice' => null,
            'tanggal_invoice' => null,
            'no_invoice' => null,
            'nilai_invoice' => null,
            'id_payment' => null,
            'tanggal_bayar' => null,
            'nilai_bayar' => null,
        ],
    ];

    $result = process_report_data($raw_data);

    $runner->assertCount(3, $result, "Should have 3 customers");
    $runner->assertEquals('PT Alpha', $result[0]['customer'], "First customer alphabetically");
    $runner->assertEquals('PT Beta', $result[1]['customer'], "Second customer alphabetically");
    $runner->assertEquals('PT Zebra', $result[2]['customer'], "Third customer alphabetically");
}

function test_multiple_spks_per_customer(GroupingTestRunner $runner): void
{
    echo "  Test: Multiple SPKs per customer sorted by SPK number...\n";

    $raw_data = [
        [
            'nm_customer' => 'PT Multi',
            'id_spk_penawaran' => 'SPK-020',
            'nominal_project' => '100000000',
            'id_detail_plan_tagih' => '120',
            'top_number' => '1',
            'rincian_top' => '50000000',
            'id_invoice' => null,
            'tanggal_invoice' => null,
            'no_invoice' => null,
            'nilai_invoice' => null,
            'id_payment' => null,
            'tanggal_bayar' => null,
            'nilai_bayar' => null,
        ],
        [
            'nm_customer' => 'PT Multi',
            'id_spk_penawaran' => 'SPK-019',
            'nominal_project' => '80000000',
            'id_detail_plan_tagih' => '121',
            'top_number' => '1',
            'rincian_top' => '40000000',
            'id_invoice' => null,
            'tanggal_invoice' => null,
            'no_invoice' => null,
            'nilai_invoice' => null,
            'id_payment' => null,
            'tanggal_bayar' => null,
            'nilai_bayar' => null,
        ],
    ];

    $result = process_report_data($raw_data);

    $runner->assertCount(1, $result, "Should have 1 customer");
    $runner->assertCount(2, $result[0]['spk_list'], "Should have 2 SPKs");
    $runner->assertEquals('SPK-019', $result[0]['spk_list'][0]['no_spk'], "First SPK sorted");
    $runner->assertEquals('SPK-020', $result[0]['spk_list'][1]['no_spk'], "Second SPK sorted");
}

function test_multiple_tops_per_spk_mixed_invoiced(GroupingTestRunner $runner): void
{
    echo "  Test: Multiple TOPs per SPK with mixed invoice status...\n";

    $raw_data = [
        [
            'nm_customer' => 'PT Mixed',
            'id_spk_penawaran' => 'SPK-030',
            'nominal_project' => '150000000',
            'id_detail_plan_tagih' => '130',
            'top_number' => '1',
            'rincian_top' => '50000000',
            'id_invoice' => '210',
            'tanggal_invoice' => '2024-01-15',
            'no_invoice' => 'INV-010',
            'nilai_invoice' => '50000000',
            'id_payment' => null,
            'tanggal_bayar' => null,
            'nilai_bayar' => null,
        ],
        [
            'nm_customer' => 'PT Mixed',
            'id_spk_penawaran' => 'SPK-030',
            'nominal_project' => '150000000',
            'id_detail_plan_tagih' => '131',
            'top_number' => '2',
            'rincian_top' => '50000000',
            'id_invoice' => '211',
            'tanggal_invoice' => '2024-02-15',
            'no_invoice' => 'INV-011',
            'nilai_invoice' => '50000000',
            'id_payment' => '310',
            'tanggal_bayar' => '2024-03-01',
            'nilai_bayar' => '50000000',
        ],
        [
            'nm_customer' => 'PT Mixed',
            'id_spk_penawaran' => 'SPK-030',
            'nominal_project' => '150000000',
            'id_detail_plan_tagih' => '132',
            'top_number' => '3',
            'rincian_top' => '50000000',
            'id_invoice' => null,
            'tanggal_invoice' => null,
            'no_invoice' => null,
            'nilai_invoice' => null,
            'id_payment' => null,
            'tanggal_bayar' => null,
            'nilai_bayar' => null,
        ],
    ];

    $result = process_report_data($raw_data);

    $spk = $result[0]['spk_list'][0];
    $runner->assertEquals(3, $spk['total_top'], "Total TOP should be 3");
    $runner->assertEquals(2, $spk['invoiced_top'], "Invoiced TOP should be 2");
    $runner->assertEquals(1, $spk['pending_top'], "Pending TOP should be 1");

    $runner->assertCount(3, $spk['details'], "Should have 3 details");

    // TOP 1: has invoice, no payment
    $runner->assertTrue($spk['details'][0]['invoice'] !== null, "TOP 1 has invoice");
    $runner->assertCount(0, $spk['details'][0]['invoice']['payments'], "TOP 1 no payments");

    // TOP 2: has invoice and payment
    $runner->assertTrue($spk['details'][1]['invoice'] !== null, "TOP 2 has invoice");
    $runner->assertCount(1, $spk['details'][1]['invoice']['payments'], "TOP 2 has 1 payment");

    // TOP 3: no invoice
    $runner->assertNull($spk['details'][2]['invoice'], "TOP 3 has no invoice");
}

function test_payment_id_not_in_output(GroupingTestRunner $runner): void
{
    echo "  Test: Payment id_payment is not in final output...\n";

    $raw_data = [
        [
            'nm_customer' => 'PT Clean',
            'id_spk_penawaran' => 'SPK-040',
            'nominal_project' => '50000000',
            'id_detail_plan_tagih' => '140',
            'top_number' => '1',
            'rincian_top' => '50000000',
            'id_invoice' => '220',
            'tanggal_invoice' => '2024-01-01',
            'no_invoice' => 'INV-020',
            'nilai_invoice' => '50000000',
            'id_payment' => '320',
            'tanggal_bayar' => '2024-02-01',
            'nilai_bayar' => '10000000',
        ],
    ];

    $result = process_report_data($raw_data);

    $payment = $result[0]['spk_list'][0]['details'][0]['invoice']['payments'][0];
    $runner->assertTrue(!isset($payment['id_payment']), "id_payment should not be in output");
    $runner->assertTrue(isset($payment['tanggal_bayar']), "tanggal_bayar should be in output");
    $runner->assertTrue(isset($payment['nilai_bayar']), "nilai_bayar should be in output");
}


function test_formula_fields_are_zero(GroupingTestRunner $runner): void
{
    echo "  Test: Formula fields are set to 0 (placeholder for task 3.3)...\n";

    $raw_data = [
        [
            'nm_customer' => 'PT Formula',
            'id_spk_penawaran' => 'SPK-050',
            'nominal_project' => '90000000',
            'id_detail_plan_tagih' => '150',
            'top_number' => '1',
            'rincian_top' => '45000000',
            'id_invoice' => '230',
            'tanggal_invoice' => '2024-01-01',
            'no_invoice' => 'INV-030',
            'nilai_invoice' => '45000000',
            'id_payment' => '330',
            'tanggal_bayar' => '2024-02-01',
            'nilai_bayar' => '20000000',
        ],
    ];

    $result = process_report_data($raw_data);

    $spk = $result[0]['spk_list'][0];
    $runner->assertEquals(0, $spk['uninvoiced'], "uninvoiced should be 0 (placeholder)");
    $runner->assertEquals(0, $spk['total_sisa_piutang'], "total_sisa_piutang should be 0 (placeholder)");
    $runner->assertEquals(0, $spk['details'][0]['invoice']['piutang_per_invoice'], "piutang_per_invoice should be 0 (placeholder)");
}

function test_complex_scenario(GroupingTestRunner $runner): void
{
    echo "  Test: Complex scenario - 2 customers, multiple SPKs, mixed states...\n";

    $raw_data = [
        // Customer A, SPK-001, TOP 1 with invoice and 2 payments
        [
            'nm_customer' => 'Customer A',
            'id_spk_penawaran' => 'SPK-001',
            'nominal_project' => '200000000',
            'id_detail_plan_tagih' => '1',
            'top_number' => '1',
            'rincian_top' => '100000000',
            'id_invoice' => '1',
            'tanggal_invoice' => '2024-01-01',
            'no_invoice' => 'INV-A1',
            'nilai_invoice' => '100000000',
            'id_payment' => '1',
            'tanggal_bayar' => '2024-01-15',
            'nilai_bayar' => '50000000',
        ],
        [
            'nm_customer' => 'Customer A',
            'id_spk_penawaran' => 'SPK-001',
            'nominal_project' => '200000000',
            'id_detail_plan_tagih' => '1',
            'top_number' => '1',
            'rincian_top' => '100000000',
            'id_invoice' => '1',
            'tanggal_invoice' => '2024-01-01',
            'no_invoice' => 'INV-A1',
            'nilai_invoice' => '100000000',
            'id_payment' => '2',
            'tanggal_bayar' => '2024-02-01',
            'nilai_bayar' => '30000000',
        ],
        // Customer A, SPK-001, TOP 2 without invoice
        [
            'nm_customer' => 'Customer A',
            'id_spk_penawaran' => 'SPK-001',
            'nominal_project' => '200000000',
            'id_detail_plan_tagih' => '2',
            'top_number' => '2',
            'rincian_top' => '100000000',
            'id_invoice' => null,
            'tanggal_invoice' => null,
            'no_invoice' => null,
            'nilai_invoice' => null,
            'id_payment' => null,
            'tanggal_bayar' => null,
            'nilai_bayar' => null,
        ],
        // Customer B, SPK-002, TOP 1 with invoice, no payment
        [
            'nm_customer' => 'Customer B',
            'id_spk_penawaran' => 'SPK-002',
            'nominal_project' => '80000000',
            'id_detail_plan_tagih' => '3',
            'top_number' => '1',
            'rincian_top' => '80000000',
            'id_invoice' => '2',
            'tanggal_invoice' => '2024-03-01',
            'no_invoice' => 'INV-B1',
            'nilai_invoice' => '80000000',
            'id_payment' => null,
            'tanggal_bayar' => null,
            'nilai_bayar' => null,
        ],
    ];

    $result = process_report_data($raw_data);

    // Verify structure
    $runner->assertCount(2, $result, "Should have 2 customers");
    $runner->assertEquals('Customer A', $result[0]['customer'], "First customer");
    $runner->assertEquals('Customer B', $result[1]['customer'], "Second customer");

    // Customer A
    $spk_a = $result[0]['spk_list'][0];
    $runner->assertEquals('SPK-001', $spk_a['no_spk'], "Customer A SPK");
    $runner->assertEquals(200000000.0, $spk_a['nominal_project'], "Customer A nominal");
    $runner->assertEquals(2, $spk_a['total_top'], "Customer A total TOP");
    $runner->assertEquals(1, $spk_a['invoiced_top'], "Customer A invoiced TOP");
    $runner->assertEquals(1, $spk_a['pending_top'], "Customer A pending TOP");

    // Customer A, TOP 1 - invoice with 2 payments
    $detail_a1 = $spk_a['details'][0];
    $runner->assertEquals(1, $detail_a1['top_number'], "TOP 1 number");
    $runner->assertTrue($detail_a1['invoice'] !== null, "TOP 1 has invoice");
    $runner->assertCount(2, $detail_a1['invoice']['payments'], "TOP 1 has 2 payments");

    // Customer A, TOP 2 - no invoice
    $detail_a2 = $spk_a['details'][1];
    $runner->assertEquals(2, $detail_a2['top_number'], "TOP 2 number");
    $runner->assertNull($detail_a2['invoice'], "TOP 2 has no invoice");

    // Customer B
    $spk_b = $result[1]['spk_list'][0];
    $runner->assertEquals('SPK-002', $spk_b['no_spk'], "Customer B SPK");
    $runner->assertEquals(1, $spk_b['total_top'], "Customer B total TOP");
    $runner->assertEquals(1, $spk_b['invoiced_top'], "Customer B invoiced TOP");
    $runner->assertEquals(0, $spk_b['pending_top'], "Customer B pending TOP");

    $detail_b = $spk_b['details'][0];
    $runner->assertTrue($detail_b['invoice'] !== null, "Customer B has invoice");
    $runner->assertCount(0, $detail_b['invoice']['payments'], "Customer B no payments");
}

// ============================================================================
// Main execution
// ============================================================================

echo "========================================\n";
echo "Feature: report-piutang-per-invoice\n";
echo "Task 3.2: Data Grouping and Hierarchical Structure\n";
echo "Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7\n";
echo "========================================\n\n";

$runner = new GroupingTestRunner();

test_empty_data($runner);
test_single_customer_single_spk_single_top_with_invoice_and_payment($runner);
test_spk_without_invoice($runner);
test_invoice_without_payment($runner);
test_multiple_payments_deduplication($runner);
test_multiple_customers_sorted_alphabetically($runner);
test_multiple_spks_per_customer($runner);
test_multiple_tops_per_spk_mixed_invoiced($runner);
test_payment_id_not_in_output($runner);
test_formula_fields_are_zero($runner);
test_complex_scenario($runner);

$success = $runner->printResults();

exit($success ? 0 : 1);
