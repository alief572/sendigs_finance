<?php

/**
 * Property-Based Test: Running Balance Sequential Consistency
 * Feature: jurnal-payment-petty-cash, Property 4: Running Balance Sequential Consistency
 *
 * Validates: Requirements 7.3, 7.4
 *
 * Tests that for any starting saldo_awal and any ordered sequence of N transactions,
 * the running balance at position i equals saldo_awal + sum(debit[0..i]) - sum(kredit[0..i]).
 * Also verifies determinism (same input produces same output) and final balance consistency.
 *
 * Run: php property_test_running_balance.php
 */

// ============================================================
// Pure function under test (extracted from model logic)
// ============================================================

/**
 * Calculate running balance for a sequence of transactions.
 * Mirrors the logic in Jurnal_payment_petty_cash_model::get_buku_besar_data()
 *
 * @param float $saldo_awal Starting balance
 * @param array $transactions Array of associative arrays with 'debit' and 'kredit' keys
 * @return array Array of running balance values at each position
 */
function calculate_running_balance($saldo_awal, $transactions)
{
    $results = [];
    $saldo = $saldo_awal;
    foreach ($transactions as $tx) {
        $saldo = $saldo + $tx['debit'] - $tx['kredit'];
        $results[] = $saldo;
    }
    return $results;
}

// ============================================================
// Random generators
// ============================================================

/**
 * Generate a random saldo_awal between -1000000 and 1000000
 *
 * @return float Random saldo rounded to 2 decimals
 */
function random_saldo_awal()
{
    return round((mt_rand(-100000000, 100000000) / 100), 2);
}

/**
 * Generate a random transaction amount (0 to 999999)
 *
 * @return float Random amount rounded to 2 decimals
 */
function random_amount()
{
    return round(mt_rand(0, 99999900) / 100, 2);
}

/**
 * Generate a random sequence of transactions
 *
 * @param int $count Number of transactions
 * @return array Array of associative arrays with 'debit' and 'kredit'
 */
function generate_random_transactions($count)
{
    $transactions = [];
    for ($i = 0; $i < $count; $i++) {
        $transactions[] = [
            'debit'  => random_amount(),
            'kredit' => random_amount(),
        ];
    }
    return $transactions;
}

// ============================================================
// Test runner
// ============================================================

$iterations = 150;
$passed = 0;
$failed = 0;
$failures = [];

echo "==========================================================\n";
echo "Property Test: Running Balance Sequential Consistency\n";
echo "Feature: jurnal-payment-petty-cash, Property 4: Running Balance Sequential Consistency\n";
echo "Validates: Requirements 7.3, 7.4\n";
echo "Iterations: {$iterations}\n";
echo "==========================================================\n\n";

for ($i = 1; $i <= $iterations; $i++) {
    $errors = [];

    // Generate random inputs
    $saldo_awal = random_saldo_awal();
    $tx_count = mt_rand(1, 20);
    $transactions = generate_random_transactions($tx_count);

    // Execute the function
    $balances = calculate_running_balance($saldo_awal, $transactions);

    // Assertion 1: balance_i == saldo_awal + sum(debit[0..i]) - sum(kredit[0..i])
    $cumulative_debit = 0;
    $cumulative_kredit = 0;
    for ($j = 0; $j < $tx_count; $j++) {
        $cumulative_debit  += $transactions[$j]['debit'];
        $cumulative_kredit += $transactions[$j]['kredit'];
        $expected = $saldo_awal + $cumulative_debit - $cumulative_kredit;

        if (abs($balances[$j] - $expected) > 0.001) {
            $errors[] = "Position {$j}: expected balance " . number_format($expected, 2)
                . ", got " . number_format($balances[$j], 2)
                . " (saldo_awal={$saldo_awal}, cum_debit={$cumulative_debit}, cum_kredit={$cumulative_kredit})";
            break; // Report first failure only per iteration
        }
    }

    // Assertion 2: Determinism - calling twice with same input gives same output
    $balances_second = calculate_running_balance($saldo_awal, $transactions);
    if ($balances !== $balances_second) {
        $errors[] = "Determinism failed: two calls with identical input produced different results";
    }

    // Assertion 3: Final balance == saldo_awal + sum(all_debit) - sum(all_kredit)
    $total_debit = 0;
    $total_kredit = 0;
    foreach ($transactions as $tx) {
        $total_debit  += $tx['debit'];
        $total_kredit += $tx['kredit'];
    }
    $expected_final = $saldo_awal + $total_debit - $total_kredit;
    $actual_final = end($balances);

    if (abs($actual_final - $expected_final) > 0.001) {
        $errors[] = "Final balance mismatch: expected " . number_format($expected_final, 2)
            . ", got " . number_format($actual_final, 2)
            . " (saldo_awal={$saldo_awal}, total_debit={$total_debit}, total_kredit={$total_kredit})";
    }

    // Assertion 4: Result array length must equal transaction count
    if (count($balances) !== $tx_count) {
        $errors[] = "Result length mismatch: expected {$tx_count} entries, got " . count($balances);
    }

    if (empty($errors)) {
        $passed++;
    } else {
        $failed++;
        if (count($failures) < 5) {
            $failures[] = [
                'iteration'    => $i,
                'saldo_awal'   => $saldo_awal,
                'tx_count'     => $tx_count,
                'transactions' => array_slice($transactions, 0, 5), // Show first 5 for brevity
                'balances'     => array_slice($balances, 0, 5),
                'errors'       => $errors,
            ];
        }
    }
}

// ============================================================
// Results
// ============================================================

echo "Results:\n";
echo "  Passed: {$passed}/{$iterations}\n";
echo "  Failed: {$failed}/{$iterations}\n\n";

if ($failed > 0) {
    echo "FAILURES (first " . count($failures) . "):\n";
    echo str_repeat('-', 60) . "\n";
    foreach ($failures as $f) {
        echo "  Iteration {$f['iteration']}:\n";
        echo "    saldo_awal: {$f['saldo_awal']}\n";
        echo "    tx_count: {$f['tx_count']}\n";
        echo "    First 5 transactions:\n";
        foreach ($f['transactions'] as $idx => $tx) {
            echo "      [{$idx}] debit={$tx['debit']}, kredit={$tx['kredit']}\n";
        }
        echo "    First 5 balances:\n";
        foreach ($f['balances'] as $idx => $bal) {
            echo "      [{$idx}] {$bal}\n";
        }
        foreach ($f['errors'] as $err) {
            echo "    ERROR: {$err}\n";
        }
        echo "\n";
    }
    echo str_repeat('=', 60) . "\n";
    echo "PROPERTY TEST FAILED\n";
    echo str_repeat('=', 60) . "\n";
    exit(1);
} else {
    echo str_repeat('=', 60) . "\n";
    echo "ALL PROPERTY TESTS PASSED ({$passed}/{$iterations} iterations)\n";
    echo str_repeat('=', 60) . "\n";
    exit(0);
}
