<?php

/**
 * Property-Based Test: Balance Validation Gate
 * Feature: jurnal-payment-petty-cash, Property 1: Balance Validation Gate
 *
 * Validates: Requirements 3.1, 3.2, 5.6, 6.6
 *
 * Tests that for any set of journal entries, posting is allowed if and only if
 * sum(debit) == sum(kredit). If sum(debit) != sum(kredit), the system MUST reject.
 * If all values are 0 or array is empty, the system MUST reject.
 *
 * Run: php property_test_balance_validation.php
 */

// ============================================================
// Pure function under test (extracted from model logic)
// ============================================================

/**
 * Validate balance: check sum(debit) == sum(kredit)
 * Mirrors the logic in Jurnal_payment_petty_cash_model::validate_balance()
 *
 * @param array $rows Array of associative arrays with 'debit' and 'kredit' keys
 * @return bool True if balanced and at least one side > 0
 */
function validate_balance($rows)
{
    if (empty($rows)) {
        return false;
    }

    $sum_debit  = 0;
    $sum_kredit = 0;

    foreach ($rows as $row) {
        $sum_debit  += (float) $row['debit'];
        $sum_kredit += (float) $row['kredit'];
    }

    // At least one side must be > 0 (reject empty journals)
    if ($sum_debit <= 0 && $sum_kredit <= 0) {
        return false;
    }

    // Use epsilon for floating point comparison
    return abs($sum_debit - $sum_kredit) < 0.01;
}

// ============================================================
// Random generators
// ============================================================

/**
 * Generate a random float amount between 0 and max (inclusive)
 *
 * @param float $max Maximum value
 * @return float Random amount rounded to 2 decimals
 */
function random_amount($max = 999999)
{
    return round(mt_rand(0, (int) ($max * 100)) / 100, 2);
}

/**
 * Generate random number of rows (1-10)
 *
 * @return int
 */
function random_row_count()
{
    return mt_rand(1, 10);
}

/**
 * Generate a random set of journal entries (unbalanced)
 *
 * @param int $count Number of rows to generate
 * @return array Array of associative arrays with 'debit' and 'kredit'
 */
function generate_random_rows($count)
{
    $rows = [];
    for ($i = 0; $i < $count; $i++) {
        $rows[] = [
            'debit'  => random_amount(),
            'kredit' => random_amount(),
        ];
    }
    return $rows;
}

/**
 * Generate a balanced set of journal entries.
 * Creates random rows then adjusts the last row so sum(debit) == sum(kredit).
 *
 * @param int $count Number of rows
 * @return array Balanced array of rows
 */
function generate_balanced_rows($count)
{
    $rows = [];
    $sum_debit  = 0;
    $sum_kredit = 0;

    // Generate all rows except the last one
    for ($i = 0; $i < $count - 1; $i++) {
        $d = random_amount();
        $k = random_amount();
        $rows[] = ['debit' => $d, 'kredit' => $k];
        $sum_debit  += $d;
        $sum_kredit += $k;
    }

    // Adjust last row to make balanced
    $diff = $sum_debit - $sum_kredit;
    if ($diff >= 0) {
        // Need more kredit or more debit on last row
        $last_debit  = random_amount(500000);
        $last_kredit = round($last_debit + $diff, 2);
        $rows[] = ['debit' => $last_debit, 'kredit' => $last_kredit];
    } else {
        // Need more debit
        $last_kredit = random_amount(500000);
        $last_debit  = round($last_kredit + abs($diff), 2);
        $rows[] = ['debit' => $last_debit, 'kredit' => $last_kredit];
    }

    return $rows;
}

/**
 * Generate an all-zero set of journal entries
 *
 * @param int $count Number of rows
 * @return array Array with all debit and kredit = 0
 */
function generate_zero_rows($count)
{
    $rows = [];
    for ($i = 0; $i < $count; $i++) {
        $rows[] = ['debit' => 0, 'kredit' => 0];
    }
    return $rows;
}

// ============================================================
// Helper: calculate sums from rows
// ============================================================

function calc_sums($rows)
{
    $sum_debit  = 0;
    $sum_kredit = 0;
    foreach ($rows as $row) {
        $sum_debit  += (float) $row['debit'];
        $sum_kredit += (float) $row['kredit'];
    }
    return [$sum_debit, $sum_kredit];
}

// ============================================================
// Test runner
// ============================================================

$iterations = 150;
$passed = 0;
$failed = 0;
$failures = [];

echo "==========================================================\n";
echo "Property Test: Balance Validation Gate\n";
echo "Feature: jurnal-payment-petty-cash, Property 1: Balance Validation Gate\n";
echo "Validates: Requirements 3.1, 3.2, 5.6, 6.6\n";
echo "Iterations: {$iterations}\n";
echo "==========================================================\n\n";

for ($i = 1; $i <= $iterations; $i++) {
    $errors = [];
    $scenario = '';
    $rows = [];

    // Distribute scenarios across iterations
    $rand = mt_rand(1, 100);

    if ($rand <= 5) {
        // Scenario: Empty array (~5% of iterations)
        $scenario = 'empty';
        $rows = [];
    } elseif ($rand <= 15) {
        // Scenario: All zeros (~10% of iterations)
        $scenario = 'all_zeros';
        $rows = generate_zero_rows(random_row_count());
    } elseif ($rand <= 55) {
        // Scenario: Balanced (~40% of iterations)
        $scenario = 'balanced';
        $rows = generate_balanced_rows(random_row_count());
    } else {
        // Scenario: Unbalanced/random (~45% of iterations)
        $scenario = 'unbalanced';
        $rows = generate_random_rows(random_row_count());
    }

    $result = validate_balance($rows);
    list($sum_debit, $sum_kredit) = empty($rows) ? [0, 0] : calc_sums($rows);
    $is_balanced = abs($sum_debit - $sum_kredit) < 0.01;
    $has_positive = ($sum_debit > 0 || $sum_kredit > 0);

    // Assertion 1: If sum(debit) == sum(kredit) AND at least one > 0: MUST return true
    if ($is_balanced && $has_positive && !empty($rows)) {
        if ($result !== true) {
            $errors[] = "Balanced with positive values should return true, got false. "
                . "sum_debit={$sum_debit}, sum_kredit={$sum_kredit}";
        }
    }

    // Assertion 2: If sum(debit) != sum(kredit): MUST return false
    if (!$is_balanced && !empty($rows)) {
        if ($result !== false) {
            $errors[] = "Unbalanced should return false, got true. "
                . "sum_debit={$sum_debit}, sum_kredit={$sum_kredit}, diff=" . abs($sum_debit - $sum_kredit);
        }
    }

    // Assertion 3: If all values are 0: MUST return false
    if (!empty($rows) && $sum_debit == 0 && $sum_kredit == 0) {
        if ($result !== false) {
            $errors[] = "All-zero rows should return false, got true";
        }
    }

    // Assertion 4: Empty array: MUST return false
    if (empty($rows)) {
        if ($result !== false) {
            $errors[] = "Empty array should return false, got true";
        }
    }

    if (empty($errors)) {
        $passed++;
    } else {
        $failed++;
        if (count($failures) < 5) {
            $failures[] = [
                'iteration' => $i,
                'scenario'  => $scenario,
                'row_count' => count($rows),
                'sum_debit' => $sum_debit,
                'sum_kredit' => $sum_kredit,
                'result'    => $result ? 'true' : 'false',
                'errors'    => $errors,
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
        echo "  Iteration {$f['iteration']} [{$f['scenario']}]:\n";
        echo "    Rows: {$f['row_count']}, sum_debit={$f['sum_debit']}, sum_kredit={$f['sum_kredit']}\n";
        echo "    validate_balance() returned: {$f['result']}\n";
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
