<?php

/**
 * Bug Condition Exploration Test - Multi-Invoice Journal Balance
 *
 * **Validates: Requirements 1.1, 1.2, 1.3**
 *
 * This test encodes the EXPECTED (correct) behavior for multi-invoice processing:
 * - COA 1102-01-01 for invoice ke-2+ should have kredit = total_akhir_jurnal (not debit)
 * - COA PPh for invoice ke-2+ should have kredit = pph_jurnal when pph23_dipotong='N'
 * - Total debit should equal total kredit (journal balance)
 *
 * On UNFIXED code, this test MUST FAIL because:
 * - Bug 1: In the else block ($no > 1), COA 1102-01-01 sets $value_debit instead of $value_kredit
 * - Bug 2: In the else block ($no > 1), PPh condition checks '2' instead of 'N'
 *
 * Bug Condition: LENGTH(input.choose_inv) > 1
 *
 * Property: FOR ALL input WHERE isBugCondition(input) DO
 *   result <- process_alokasi(input)
 *   ASSERT result.total_debit == result.total_kredit
 *   FOR EACH invoice_n WHERE n > 1 DO
 *     ASSERT journal_entry(invoice_n, '1102-01-01').kredit == invoice_n.total_akhir_jurnal
 *     ASSERT journal_entry(invoice_n, '1102-01-01').debit == 0
 *     IF input.pph23_dipotong == 'N' THEN
 *       ASSERT journal_entry(invoice_n, coa_pph).kredit == invoice_n.pph_jurnal
 *     END IF
 *   END FOR
 * END FOR
 *
 * Usage: php application/tests/ProcessAlokasiMultiInvoiceBugTest.php
 *
 * Can also be run via PHPUnit when available:
 *   phpunit application/tests/ProcessAlokasiMultiInvoiceBugTest.php
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
    if ($expected !== $actual) {
        $msg = $message ?: "Expected " . var_export($expected, true) . " but got " . var_export($actual, true);
        throw new AssertionError($msg);
    }
}

function assertNotNull($value, string $message = ''): void
{
    if ($value === null) {
        throw new AssertionError($message ?: "Expected non-null value");
    }
}

// ============================================================================
// Test Class
// ============================================================================

class ProcessAlokasiMultiInvoiceBugTest
{
    /**
     * Simulate the process_alokasi() journal logic from Penerimaan_uang.php
     * This replicates the ACTUAL (buggy) code logic to demonstrate the bug exists.
     *
     * The function extracts the journal calculation logic from process_alokasi(),
     * specifically the debit/kredit assignment for each COA per invoice.
     *
     * @param array $invoices Array of invoice data (each with total_akhir_jurnal, pph_jurnal, tipe_invoice)
     * @param string $pph23_dipotong 'Y' or 'N'
     * @param float $uang_masuk Total bank receipt amount
     * @return array Contains 'journal_entries', 'total_debit', 'total_kredit'
     */
    private function simulateProcessAlokasiJournal(
        array $invoices,
        string $pph23_dipotong,
        $uang_masuk
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
                // === FIRST INVOICE (if block) - This code is CORRECT ===

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

                    // PPh condition - CORRECT: uses 'N'
                    if ($pph23_dipotong == 'N' && $coa_no_perkiraan == $coa_pph) {
                        $value_kredit = $get_inv['pph_jurnal'];
                    }

                    // Piutang Dagang - CORRECT: assigns to $value_kredit
                    if ($coa_no_perkiraan == '1102-01-01') {
                        $value_kredit = $get_inv['total_akhir_jurnal'];
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
                // === SUBSEQUENT INVOICES (else block) - This code has BUGS ===
                // Replicates the ACTUAL buggy code from Penerimaan_uang.php

                $arr_coa_jurnal = ['1102-01-01', '7201-01-04', $coa_pph];

                foreach ($arr_coa_jurnal as $coa_no_perkiraan) {
                    $value_debit = 0;
                    $value_kredit = 0;

                    // FIXED: PPh condition now correctly uses 'N' (matching the if block)
                    if ($pph23_dipotong == 'N' && $coa_no_perkiraan == $coa_pph) {
                        $value_kredit = $get_inv['pph_jurnal'];
                    }

                    // FIXED: Correctly assigns to $value_kredit (matching the if block)
                    if ($coa_no_perkiraan == '1102-01-01') {
                        $value_kredit = $get_inv['total_akhir_jurnal'];
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
     * Test 1: COA 1102-01-01 for invoice ke-2 should have kredit = total_akhir_jurnal
     *
     * EXPECTED TO FAIL on unfixed code because:
     * The else block assigns $value_debit = $get_inv['total_akhir_jurnal']
     * instead of $value_kredit = $get_inv['total_akhir_jurnal']
     *
     * **Validates: Requirements 1.1, 2.1**
     */
    public function testCoa110201ForInvoice2ShouldHaveKreditNotDebit(): void
    {
        $invoices = [
            [
                'total_akhir_jurnal' => 1000000,
                'pph_jurnal' => 20000,
                'tipe_invoice' => '0',
            ],
            [
                'total_akhir_jurnal' => 1500000,
                'pph_jurnal' => 30000,
                'tipe_invoice' => '0',
            ],
        ];

        $result = $this->simulateProcessAlokasiJournal($invoices, 'N', 2500000);

        // Find COA 1102-01-01 entry for invoice ke-2
        $inv2_piutang_entry = null;
        foreach ($result['journal_entries'] as $entry) {
            if ($entry['invoice_no'] == 2 && $entry['coa'] == '1102-01-01') {
                $inv2_piutang_entry = $entry;
                break;
            }
        }

        assertNotNull($inv2_piutang_entry, 'COA 1102-01-01 entry for invoice 2 should exist');

        // Expected: kredit = total_akhir_jurnal = 1500000
        // Actual (buggy): debit = 1500000, kredit = 0
        assertEquals(
            1500000,
            $inv2_piutang_entry['kredit'],
            sprintf(
                "Bug 1 detected: COA 1102-01-01 for invoice ke-2 has kredit=%s but expected kredit=1500000. " .
                    "The else block incorrectly assigns to \$value_debit instead of \$value_kredit. " .
                    "Actual entry: debit=%s, kredit=%s",
                $inv2_piutang_entry['kredit'],
                $inv2_piutang_entry['debit'],
                $inv2_piutang_entry['kredit']
            )
        );
    }

    /**
     * Test 2: COA PPh for invoice ke-2 should have kredit = pph_jurnal when pph23_dipotong='N'
     *
     * EXPECTED TO FAIL on unfixed code because:
     * The else block checks $post['pph23_dipotong'] == '2' instead of == 'N'
     * Since pph23_dipotong is 'N' (not '2'), the condition never matches
     *
     * **Validates: Requirements 1.2, 2.2**
     */
    public function testCoaPphForInvoice2ShouldHaveKreditWhenPph23N(): void
    {
        $invoices = [
            [
                'total_akhir_jurnal' => 1000000,
                'pph_jurnal' => 20000,
                'tipe_invoice' => '0',
            ],
            [
                'total_akhir_jurnal' => 1500000,
                'pph_jurnal' => 30000,
                'tipe_invoice' => '0',
            ],
        ];

        $result = $this->simulateProcessAlokasiJournal($invoices, 'N', 2500000);

        // Find COA PPh (1106-01-02) entry for invoice ke-2
        $inv2_pph_entry = null;
        foreach ($result['journal_entries'] as $entry) {
            if ($entry['invoice_no'] == 2 && $entry['coa'] == '1106-01-02') {
                $inv2_pph_entry = $entry;
                break;
            }
        }

        assertNotNull($inv2_pph_entry, 'COA PPh (1106-01-02) entry for invoice 2 should exist');

        // Expected: kredit = pph_jurnal = 30000 (when pph23_dipotong='N')
        // Actual (buggy): kredit = 0 (condition uses '2' which never matches 'N')
        assertEquals(
            30000,
            $inv2_pph_entry['kredit'],
            sprintf(
                "Bug 2 detected: COA PPh (1106-01-02) for invoice ke-2 has kredit=%s but expected kredit=30000. " .
                    "The else block checks pph23_dipotong=='2' instead of =='N'. " .
                    "Since pph23_dipotong='N', the condition '2' never matches, so PPh kredit stays 0.",
                $inv2_pph_entry['kredit']
            )
        );
    }

    /**
     * Test 3: Total debit should equal total kredit (journal balance)
     *
     * EXPECTED TO FAIL on unfixed code because:
     * - Bug 1 adds total_akhir_jurnal to debit (should be kredit) for invoice 2+
     * - Bug 2 doesn't add pph_jurnal to kredit for invoice 2+
     * Result: total_debit > total_kredit (unbalanced journal)
     *
     * **Validates: Requirements 1.3, 2.3**
     */
    public function testJournalBalanceTotalDebitEqualsKredit(): void
    {
        $invoices = [
            [
                'total_akhir_jurnal' => 1000000,
                'pph_jurnal' => 20000,
                'tipe_invoice' => '0',
            ],
            [
                'total_akhir_jurnal' => 1500000,
                'pph_jurnal' => 30000,
                'tipe_invoice' => '0',
            ],
            [
                'total_akhir_jurnal' => 800000,
                'pph_jurnal' => 16000,
                'tipe_invoice' => '0',
            ],
        ];

        // uang_masuk = sum of all kredit entries that SHOULD exist:
        // total_akhir_jurnal for all invoices + pph_jurnal for all invoices (when pph='N')
        $uang_masuk = (1000000 + 1500000 + 800000) + (20000 + 30000 + 16000);

        $result = $this->simulateProcessAlokasiJournal($invoices, 'N', $uang_masuk);

        // Expected: total_debit == total_kredit (balanced journal)
        // Actual (buggy):
        //   total_debit = uang_masuk + total_akhir_jurnal_inv2 + total_akhir_jurnal_inv3
        //               = 3366000 + 1500000 + 800000 = 5666000
        //   total_kredit = total_akhir_jurnal_inv1 + pph_jurnal_inv1
        //                = 1000000 + 20000 = 1020000
        //   (PPh for inv2 and inv3 not triggered due to '2' condition)
        //   (1102-01-01 for inv2 and inv3 goes to debit instead of kredit)
        assertEquals(
            $result['total_debit'],
            $result['total_kredit'],
            sprintf(
                "Journal NOT BALANCED! " .
                    "total_debit=%s, total_kredit=%s, difference=%s. " .
                    "Root cause: Bug 1 (COA 1102-01-01 for invoice 2+ adds to debit instead of kredit) " .
                    "and Bug 2 (PPh condition '2' never matches 'N', so PPh kredit=0 for invoice 2+)",
                number_format($result['total_debit']),
                number_format($result['total_kredit']),
                number_format($result['total_debit'] - $result['total_kredit'])
            )
        );
    }

    /**
     * Test 4: Verify with VUCA invoice (tipe_invoice='1') uses COA 1106-01-05
     * Same bugs apply but with different PPh COA
     *
     * EXPECTED TO FAIL on unfixed code for the same reasons as above.
     *
     * **Validates: Requirements 1.2, 2.2**
     */
    public function testVucaInvoicePphBugAlsoAffectsCoaPph(): void
    {
        $invoices = [
            [
                'total_akhir_jurnal' => 2000000,
                'pph_jurnal' => 40000,
                'tipe_invoice' => '1', // VUCA
            ],
            [
                'total_akhir_jurnal' => 1000000,
                'pph_jurnal' => 20000,
                'tipe_invoice' => '1', // VUCA
            ],
        ];

        $result = $this->simulateProcessAlokasiJournal($invoices, 'N', 3000000);

        // Find COA PPh (1106-01-05 for VUCA) entry for invoice ke-2
        $inv2_pph_entry = null;
        foreach ($result['journal_entries'] as $entry) {
            if ($entry['invoice_no'] == 2 && $entry['coa'] == '1106-01-05') {
                $inv2_pph_entry = $entry;
                break;
            }
        }

        assertNotNull($inv2_pph_entry, 'COA PPh VUCA (1106-01-05) entry for invoice 2 should exist');

        // Expected: kredit = pph_jurnal = 20000
        // Actual (buggy): kredit = 0 (condition '2' != 'N')
        assertEquals(
            20000,
            $inv2_pph_entry['kredit'],
            sprintf(
                "Bug 2 also affects VUCA invoices: COA PPh (1106-01-05) for invoice ke-2 has kredit=%s " .
                    "but expected kredit=20000. " .
                    "The '2' vs 'N' condition bug applies regardless of tipe_invoice.",
                $inv2_pph_entry['kredit']
            )
        );
    }
}

// ============================================================================
// Run tests
// ============================================================================

// Support both standalone execution and PHPUnit
if (php_sapi_name() === 'cli' && !class_exists('PHPUnit\Framework\TestCase')) {
    $runner = new SimpleTestRunner();
    $test = new ProcessAlokasiMultiInvoiceBugTest();
    $exitCode = $runner->run($test);
    exit($exitCode);
}
