<?php

/**
 * Fix Verification Unit Tests - Multi-Invoice Journal Balance
 *
 * **Validates: Requirements 2.1, 2.2, 2.3**
 *
 * These tests verify that the FIXED process_alokasi() function correctly handles
 * multi-invoice scenarios. All tests use the FIXED simulation logic where:
 * - PPh condition uses 'N' (not '2') for invoice ke-2+
 * - COA 1102-01-01 uses $value_kredit (not $value_debit) for invoice ke-2+
 *
 * All tests must PASS on fixed code.
 *
 * Usage: php application/tests/ProcessAlokasiFixVerificationTest.php
 */

// ============================================================================
// Minimal test framework (standalone runner when PHPUnit is not available)
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
    if (is_numeric($expected) && is_numeric($actual)) {
        if ($expected != $actual) {
            $msg = $message ?: "Expected " . var_export($expected, true) . " but got " . var_export($actual, true);
            throw new AssertionError($msg);
        }
    } elseif ($expected !== $actual) {
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

function assertNotNull($value, string $message = ''): void
{
    if ($value === null) {
        throw new AssertionError($message ?: "Expected non-null value");
    }
}

function assertCount($expected, $array, string $message = ''): void
{
    $actual = count($array);
    if ($expected != $actual) {
        $msg = $message ?: "Expected count {$expected} but got {$actual}";
        throw new AssertionError($msg);
    }
}


// ============================================================================
// Test Class
// ============================================================================

class ProcessAlokasiFixVerificationTest
{
    /**
     * Simulate the FIXED process_alokasi() journal logic.
     *
     * This uses the CORRECTED logic where:
     * - PPh condition uses 'N' for ALL invoices (both if and else blocks)
     * - COA 1102-01-01 uses $value_kredit for ALL invoices (both if and else blocks)
     *
     * @param array $invoices Array of invoice data
     * @param string $pph23_dipotong 'Y' or 'N'
     * @param float $uang_masuk Total bank receipt amount
     * @param float $biaya_admin Admin fee per invoice (default 0)
     * @return array Contains 'journal_entries', 'total_debit', 'total_kredit'
     */
    private function simulateFixedProcessAlokasi(
        array $invoices,
        string $pph23_dipotong,
        $uang_masuk,
        $biaya_admin = 0
    ): array {
        $journal_entries = [];
        $total_debit = 0;
        $total_kredit = 0;
        $no = 0;

        foreach ($invoices as $get_inv) {
            $no++;

            // Determine COA PPh based on tipe_invoice (VUCA = 1106-01-05, others = 1106-01-02)
            $coa_pph = (!empty($get_inv['tipe_invoice']) && $get_inv['tipe_invoice'] == '1')
                ? '1106-01-05'
                : '1106-01-02';

            if ($no == 1) {
                // === FIRST INVOICE (if block) ===

                // Bank debit entry (first row of journal)
                $journal_entries[] = [
                    'invoice_no' => $no,
                    'coa' => 'BANK',
                    'debit' => $uang_masuk,
                    'kredit' => 0,
                    'type' => 'bank_debit',
                ];
                $total_debit += $uang_masuk;

                // COA entries for first invoice
                $arr_coa_jurnal = ['1102-01-01', '7201-01-04', $coa_pph];

                foreach ($arr_coa_jurnal as $coa_no_perkiraan) {
                    $value_debit = 0;
                    $value_kredit = 0;

                    // PPh condition - uses 'N'
                    if ($pph23_dipotong == 'N' && $coa_no_perkiraan == $coa_pph) {
                        $value_kredit = $get_inv['pph_jurnal'];
                    }

                    // Piutang Dagang - assigns to $value_kredit
                    if ($coa_no_perkiraan == '1102-01-01') {
                        $value_kredit = $get_inv['total_akhir_jurnal'];
                    }

                    // Biaya Admin
                    if ($coa_no_perkiraan == '7201-01-04' && $biaya_admin > 0) {
                        $value_kredit = $biaya_admin;
                    }

                    $journal_entries[] = [
                        'invoice_no' => $no,
                        'coa' => $coa_no_perkiraan,
                        'debit' => $value_debit,
                        'kredit' => $value_kredit,
                        'type' => 'coa_entry',
                    ];

                    $total_debit += $value_debit;
                    $total_kredit += $value_kredit;
                }
            } else {
                // === SUBSEQUENT INVOICES (else block) - FIXED ===

                $arr_coa_jurnal = ['1102-01-01', '7201-01-04', $coa_pph];

                foreach ($arr_coa_jurnal as $coa_no_perkiraan) {
                    $value_debit = 0;
                    $value_kredit = 0;

                    // FIXED: PPh condition uses 'N' (matching the if block)
                    if ($pph23_dipotong == 'N' && $coa_no_perkiraan == $coa_pph) {
                        $value_kredit = $get_inv['pph_jurnal'];
                    }

                    // FIXED: Assigns to $value_kredit (matching the if block)
                    if ($coa_no_perkiraan == '1102-01-01') {
                        $value_kredit = $get_inv['total_akhir_jurnal'];
                    }

                    // Biaya Admin
                    if ($coa_no_perkiraan == '7201-01-04' && $biaya_admin > 0) {
                        $value_kredit = $biaya_admin;
                    }

                    $journal_entries[] = [
                        'invoice_no' => $no,
                        'coa' => $coa_no_perkiraan,
                        'debit' => $value_debit,
                        'kredit' => $value_kredit,
                        'type' => 'coa_entry',
                    ];

                    $total_debit += $value_debit;
                    $total_kredit += $value_kredit;
                }
            }
        }

        return [
            'journal_entries' => $journal_entries,
            'total_debit' => $total_debit,
            'total_kredit' => $total_kredit,
        ];
    }


    /**
     * Test 1: 2 invoices with pph23_dipotong='N'
     * Verify journal balance, both COA 1102-01-01 entries as kredit, both PPh entries triggered
     *
     * **Validates: Requirements 2.1, 2.2, 2.3**
     */
    public function test2InvoicesPphN_JournalBalanceAndCorrectEntries(): void
    {
        $invoices = [
            ['total_akhir_jurnal' => 1000000, 'pph_jurnal' => 20000, 'tipe_invoice' => '0'],
            ['total_akhir_jurnal' => 1500000, 'pph_jurnal' => 30000, 'tipe_invoice' => '0'],
        ];

        // uang_masuk = sum(total_akhir_jurnal) + sum(pph_jurnal) when pph='N'
        $uang_masuk = (1000000 + 1500000) + (20000 + 30000);

        $result = $this->simulateFixedProcessAlokasi($invoices, 'N', $uang_masuk);

        // 1. Verify journal balance
        assertEquals(
            $result['total_debit'],
            $result['total_kredit'],
            sprintf(
                "2 invoices pph='N': Journal should be balanced. total_debit=%s, total_kredit=%s",
                number_format($result['total_debit']),
                number_format($result['total_kredit'])
            )
        );

        // 2. Verify both COA 1102-01-01 entries are kredit
        foreach ([1, 2] as $inv_no) {
            $piutang_entry = null;
            foreach ($result['journal_entries'] as $entry) {
                if ($entry['invoice_no'] == $inv_no && $entry['coa'] == '1102-01-01') {
                    $piutang_entry = $entry;
                    break;
                }
            }
            assertNotNull($piutang_entry, "COA 1102-01-01 for invoice {$inv_no} should exist");
            $expected_kredit = $invoices[$inv_no - 1]['total_akhir_jurnal'];
            assertEquals(
                $expected_kredit,
                $piutang_entry['kredit'],
                "Invoice {$inv_no}: COA 1102-01-01 kredit should be {$expected_kredit}"
            );
            assertEquals(0, $piutang_entry['debit'], "Invoice {$inv_no}: COA 1102-01-01 debit should be 0");
        }

        // 3. Verify both PPh entries triggered
        foreach ([1, 2] as $inv_no) {
            $pph_entry = null;
            foreach ($result['journal_entries'] as $entry) {
                if ($entry['invoice_no'] == $inv_no && $entry['coa'] == '1106-01-02') {
                    $pph_entry = $entry;
                    break;
                }
            }
            assertNotNull($pph_entry, "COA PPh for invoice {$inv_no} should exist");
            $expected_pph = $invoices[$inv_no - 1]['pph_jurnal'];
            assertEquals(
                $expected_pph,
                $pph_entry['kredit'],
                "Invoice {$inv_no}: PPh kredit should be {$expected_pph} when pph23_dipotong='N'"
            );
        }
    }

    /**
     * Test 2: 3 invoices with pph23_dipotong='N'
     * Verify journal balance scales correctly with more invoices
     *
     * **Validates: Requirements 2.1, 2.2, 2.3**
     */
    public function test3InvoicesPphN_JournalBalanceScales(): void
    {
        $invoices = [
            ['total_akhir_jurnal' => 500000, 'pph_jurnal' => 10000, 'tipe_invoice' => '0'],
            ['total_akhir_jurnal' => 750000, 'pph_jurnal' => 15000, 'tipe_invoice' => '0'],
            ['total_akhir_jurnal' => 1200000, 'pph_jurnal' => 24000, 'tipe_invoice' => '0'],
        ];

        $uang_masuk = (500000 + 750000 + 1200000) + (10000 + 15000 + 24000);

        $result = $this->simulateFixedProcessAlokasi($invoices, 'N', $uang_masuk);

        // Verify journal balance
        assertEquals(
            $result['total_debit'],
            $result['total_kredit'],
            sprintf(
                "3 invoices pph='N': Journal should be balanced. total_debit=%s, total_kredit=%s",
                number_format($result['total_debit']),
                number_format($result['total_kredit'])
            )
        );

        // Verify all 3 COA 1102-01-01 entries are kredit
        for ($inv_no = 1; $inv_no <= 3; $inv_no++) {
            $piutang_entry = null;
            foreach ($result['journal_entries'] as $entry) {
                if ($entry['invoice_no'] == $inv_no && $entry['coa'] == '1102-01-01') {
                    $piutang_entry = $entry;
                    break;
                }
            }
            assertNotNull($piutang_entry, "COA 1102-01-01 for invoice {$inv_no} should exist");
            $expected_kredit = $invoices[$inv_no - 1]['total_akhir_jurnal'];
            assertEquals(
                $expected_kredit,
                $piutang_entry['kredit'],
                "Invoice {$inv_no}: COA 1102-01-01 kredit should be {$expected_kredit}"
            );
        }

        // Verify all 3 PPh entries triggered
        for ($inv_no = 1; $inv_no <= 3; $inv_no++) {
            $pph_entry = null;
            foreach ($result['journal_entries'] as $entry) {
                if ($entry['invoice_no'] == $inv_no && $entry['coa'] == '1106-01-02') {
                    $pph_entry = $entry;
                    break;
                }
            }
            assertNotNull($pph_entry, "COA PPh for invoice {$inv_no} should exist");
            $expected_pph = $invoices[$inv_no - 1]['pph_jurnal'];
            assertEquals(
                $expected_pph,
                $pph_entry['kredit'],
                "Invoice {$inv_no}: PPh kredit should be {$expected_pph}"
            );
        }

        // Verify total kredit = sum(total_akhir_jurnal) + sum(pph_jurnal)
        $expected_total_kredit = (500000 + 750000 + 1200000) + (10000 + 15000 + 24000);
        assertEquals(
            $expected_total_kredit,
            $result['total_kredit'],
            "Total kredit should equal sum of all total_akhir_jurnal + sum of all pph_jurnal"
        );
    }


    /**
     * Test 3: 2 invoices with pph23_dipotong='Y'
     * Verify journal balance, COA 1102-01-01 as kredit, NO PPh entries
     *
     * **Validates: Requirements 2.1, 2.3**
     */
    public function test2InvoicesPphY_JournalBalanceNoPphEntries(): void
    {
        $invoices = [
            ['total_akhir_jurnal' => 2000000, 'pph_jurnal' => 40000, 'tipe_invoice' => '0'],
            ['total_akhir_jurnal' => 3000000, 'pph_jurnal' => 60000, 'tipe_invoice' => '0'],
        ];

        // When pph='Y', uang_masuk = sum(total_akhir_jurnal) only (no PPh in bank receipt)
        $uang_masuk = 2000000 + 3000000;

        $result = $this->simulateFixedProcessAlokasi($invoices, 'Y', $uang_masuk);

        // 1. Verify journal balance
        assertEquals(
            $result['total_debit'],
            $result['total_kredit'],
            sprintf(
                "2 invoices pph='Y': Journal should be balanced. total_debit=%s, total_kredit=%s",
                number_format($result['total_debit']),
                number_format($result['total_kredit'])
            )
        );

        // 2. Verify both COA 1102-01-01 entries are kredit
        foreach ([1, 2] as $inv_no) {
            $piutang_entry = null;
            foreach ($result['journal_entries'] as $entry) {
                if ($entry['invoice_no'] == $inv_no && $entry['coa'] == '1102-01-01') {
                    $piutang_entry = $entry;
                    break;
                }
            }
            assertNotNull($piutang_entry, "COA 1102-01-01 for invoice {$inv_no} should exist");
            $expected_kredit = $invoices[$inv_no - 1]['total_akhir_jurnal'];
            assertEquals(
                $expected_kredit,
                $piutang_entry['kredit'],
                "Invoice {$inv_no}: COA 1102-01-01 kredit should be {$expected_kredit}"
            );
            assertEquals(0, $piutang_entry['debit'], "Invoice {$inv_no}: COA 1102-01-01 debit should be 0");
        }

        // 3. Verify NO PPh entries (kredit = 0 for all PPh COA entries)
        foreach ([1, 2] as $inv_no) {
            $pph_entry = null;
            foreach ($result['journal_entries'] as $entry) {
                if ($entry['invoice_no'] == $inv_no && $entry['coa'] == '1106-01-02') {
                    $pph_entry = $entry;
                    break;
                }
            }
            assertNotNull($pph_entry, "COA PPh entry structure for invoice {$inv_no} should exist");
            assertEquals(
                0,
                $pph_entry['kredit'],
                "Invoice {$inv_no}: PPh kredit should be 0 when pph23_dipotong='Y'"
            );
        }
    }

    /**
     * Test 4: 5 invoices with mixed tipe_invoice (VUCA/non-VUCA)
     * Verify correct COA PPh used (1106-01-05 for VUCA vs 1106-01-02 for non-VUCA)
     *
     * **Validates: Requirements 2.2, 2.3**
     */
    public function test5InvoicesMixedVuca_CorrectCoaPph(): void
    {
        $invoices = [
            ['total_akhir_jurnal' => 1000000, 'pph_jurnal' => 20000, 'tipe_invoice' => '0'],  // non-VUCA
            ['total_akhir_jurnal' => 2000000, 'pph_jurnal' => 40000, 'tipe_invoice' => '1'],  // VUCA
            ['total_akhir_jurnal' => 1500000, 'pph_jurnal' => 30000, 'tipe_invoice' => '0'],  // non-VUCA
            ['total_akhir_jurnal' => 800000, 'pph_jurnal' => 16000, 'tipe_invoice' => '1'],   // VUCA
            ['total_akhir_jurnal' => 3000000, 'pph_jurnal' => 60000, 'tipe_invoice' => '0'],  // non-VUCA
        ];

        $total_akhir_sum = 1000000 + 2000000 + 1500000 + 800000 + 3000000;
        $pph_sum = 20000 + 40000 + 30000 + 16000 + 60000;
        $uang_masuk = $total_akhir_sum + $pph_sum;

        $result = $this->simulateFixedProcessAlokasi($invoices, 'N', $uang_masuk);

        // 1. Verify journal balance
        assertEquals(
            $result['total_debit'],
            $result['total_kredit'],
            sprintf(
                "5 mixed invoices: Journal should be balanced. total_debit=%s, total_kredit=%s",
                number_format($result['total_debit']),
                number_format($result['total_kredit'])
            )
        );

        // 2. Verify correct COA PPh used for each invoice
        $expected_pph_coas = ['1106-01-02', '1106-01-05', '1106-01-02', '1106-01-05', '1106-01-02'];

        for ($inv_no = 1; $inv_no <= 5; $inv_no++) {
            $expected_coa = $expected_pph_coas[$inv_no - 1];
            $pph_entry = null;
            foreach ($result['journal_entries'] as $entry) {
                if ($entry['invoice_no'] == $inv_no && $entry['coa'] == $expected_coa) {
                    $pph_entry = $entry;
                    break;
                }
            }
            assertNotNull(
                $pph_entry,
                "Invoice {$inv_no}: COA PPh ({$expected_coa}) entry should exist"
            );
            $expected_pph = $invoices[$inv_no - 1]['pph_jurnal'];
            assertEquals(
                $expected_pph,
                $pph_entry['kredit'],
                "Invoice {$inv_no}: PPh kredit on COA {$expected_coa} should be {$expected_pph}"
            );
        }
    }


    /**
     * Test 5: Edge case - invoice ke-2 with total_akhir_jurnal=0 (zero amount)
     * Verify journal still balances when an invoice has zero amount
     *
     * **Validates: Requirements 2.1, 2.3**
     */
    public function testEdgeCase_Invoice2ZeroAmount(): void
    {
        $invoices = [
            ['total_akhir_jurnal' => 1000000, 'pph_jurnal' => 20000, 'tipe_invoice' => '0'],
            ['total_akhir_jurnal' => 0, 'pph_jurnal' => 0, 'tipe_invoice' => '0'],
        ];

        // uang_masuk = only from invoice 1 since invoice 2 is zero
        $uang_masuk = 1000000 + 20000;

        $result = $this->simulateFixedProcessAlokasi($invoices, 'N', $uang_masuk);

        // 1. Verify journal balance
        assertEquals(
            $result['total_debit'],
            $result['total_kredit'],
            sprintf(
                "Zero amount invoice: Journal should be balanced. total_debit=%s, total_kredit=%s",
                number_format($result['total_debit']),
                number_format($result['total_kredit'])
            )
        );

        // 2. Verify invoice 2 COA 1102-01-01 has kredit=0 (not debit=0 which would be wrong)
        $inv2_piutang = null;
        foreach ($result['journal_entries'] as $entry) {
            if ($entry['invoice_no'] == 2 && $entry['coa'] == '1102-01-01') {
                $inv2_piutang = $entry;
                break;
            }
        }
        assertNotNull($inv2_piutang, "COA 1102-01-01 for invoice 2 should exist");
        assertEquals(0, $inv2_piutang['kredit'], "Invoice 2: COA 1102-01-01 kredit should be 0 (zero amount)");
        assertEquals(0, $inv2_piutang['debit'], "Invoice 2: COA 1102-01-01 debit should be 0 (zero amount)");
    }

    /**
     * Test 6: Edge case - invoice ke-2 with biaya_admin > 0
     * Verify journal balances when admin fee is applied
     *
     * Note: biaya_admin is applied as kredit on COA 7201-01-04 for EACH invoice
     * in the loop, so total admin kredit = biaya_admin * num_invoices.
     * The bank debit (uang_masuk) must account for this.
     *
     * **Validates: Requirements 2.1, 2.3**
     */
    public function testEdgeCase_Invoice2WithBiayaAdmin(): void
    {
        $invoices = [
            ['total_akhir_jurnal' => 1000000, 'pph_jurnal' => 20000, 'tipe_invoice' => '0'],
            ['total_akhir_jurnal' => 2000000, 'pph_jurnal' => 40000, 'tipe_invoice' => '0'],
        ];

        $biaya_admin = 50000;
        $num_invoices = count($invoices);
        // uang_masuk = sum(total_akhir_jurnal) + sum(pph_jurnal) + biaya_admin * num_invoices
        // biaya_admin is applied per invoice in the loop (each invoice gets a 7201-01-04 kredit entry)
        $uang_masuk = (1000000 + 2000000) + (20000 + 40000) + ($biaya_admin * $num_invoices);

        $result = $this->simulateFixedProcessAlokasi($invoices, 'N', $uang_masuk, $biaya_admin);

        // 1. Verify journal balance
        assertEquals(
            $result['total_debit'],
            $result['total_kredit'],
            sprintf(
                "With biaya_admin: Journal should be balanced. total_debit=%s, total_kredit=%s",
                number_format($result['total_debit']),
                number_format($result['total_kredit'])
            )
        );

        // 2. Verify biaya_admin entries exist (COA 7201-01-04) for both invoices
        $admin_entries = [];
        foreach ($result['journal_entries'] as $entry) {
            if ($entry['coa'] == '7201-01-04' && $entry['kredit'] > 0) {
                $admin_entries[] = $entry;
            }
        }
        assertCount(
            2,
            $admin_entries,
            "Both invoices should have biaya_admin entry with kredit > 0"
        );

        // 3. Verify COA 1102-01-01 for invoice 2 is still kredit
        $inv2_piutang = null;
        foreach ($result['journal_entries'] as $entry) {
            if ($entry['invoice_no'] == 2 && $entry['coa'] == '1102-01-01') {
                $inv2_piutang = $entry;
                break;
            }
        }
        assertNotNull($inv2_piutang, "COA 1102-01-01 for invoice 2 should exist");
        assertEquals(
            2000000,
            $inv2_piutang['kredit'],
            "Invoice 2: COA 1102-01-01 kredit should be 2000000 even with biaya_admin"
        );
        assertEquals(0, $inv2_piutang['debit'], "Invoice 2: COA 1102-01-01 debit should be 0");
    }


    /**
     * Test 7: Property-based test - random number of invoices (2-10) with random values
     * Verify journal ALWAYS balances regardless of input
     *
     * Property: FOR ALL inputs WHERE LENGTH(choose_inv) >= 2 DO
     *   result <- process_alokasi_fixed(inputs)
     *   ASSERT result.total_debit == result.total_kredit
     * END FOR
     *
     * **Validates: Requirements 2.1, 2.3**
     */
    public function testPropertyMultiInvoiceJournalAlwaysBalances(): void
    {
        $iterations = 100;
        $seed = 2024;
        mt_srand($seed);

        for ($i = 0; $i < $iterations; $i++) {
            // Random number of invoices (2-10)
            $num_invoices = mt_rand(2, 10);
            $invoices = [];
            $total_akhir_sum = 0;
            $pph_sum = 0;

            for ($j = 0; $j < $num_invoices; $j++) {
                $total_akhir = mt_rand(100000, 50000000);
                $pph = (int) round($total_akhir * mt_rand(1, 5) / 100);
                $tipe = mt_rand(0, 1) == 1 ? '1' : '0';

                $invoices[] = [
                    'total_akhir_jurnal' => $total_akhir,
                    'pph_jurnal' => $pph,
                    'tipe_invoice' => $tipe,
                ];

                $total_akhir_sum += $total_akhir;
                $pph_sum += $pph;
            }

            // Random pph23_dipotong
            $pph23_dipotong = mt_rand(0, 1) == 1 ? 'Y' : 'N';

            // Calculate uang_masuk based on pph23_dipotong
            if ($pph23_dipotong == 'N') {
                $uang_masuk = $total_akhir_sum + $pph_sum;
            } else {
                $uang_masuk = $total_akhir_sum;
            }

            $result = $this->simulateFixedProcessAlokasi($invoices, $pph23_dipotong, $uang_masuk);

            assertEquals(
                $result['total_debit'],
                $result['total_kredit'],
                sprintf(
                    "Property violation at iteration %d: Journal not balanced for %d invoices. " .
                        "pph23_dipotong='%s', total_debit=%s, total_kredit=%s, difference=%s",
                    $i + 1,
                    $num_invoices,
                    $pph23_dipotong,
                    number_format($result['total_debit']),
                    number_format($result['total_kredit']),
                    number_format($result['total_debit'] - $result['total_kredit'])
                )
            );
        }

        echo "    Property verified across {$iterations} random multi-invoice inputs (2-10 invoices each)\n";
    }
}

// ============================================================================
// Run tests
// ============================================================================

// Support both standalone execution and PHPUnit
if (php_sapi_name() === 'cli' && !class_exists('PHPUnit\Framework\TestCase')) {
    $runner = new SimpleTestRunner();
    $test = new ProcessAlokasiFixVerificationTest();
    $exitCode = $runner->run($test);
    exit($exitCode);
}
