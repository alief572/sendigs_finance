<?php

/**
 * Property-Based Test: Modal Display Filter Invariant
 * Feature: jurnal-payment-petty-cash, Property 2: Modal Display Filter Invariant
 *
 * Validates: Requirements 2.4, 2.5
 *
 * Tests that for any collection of journal rows retrieved for modal display:
 * - The displayed set contains ONLY rows where debit > 0 OR kredit > 0
 * - No qualifying rows are missing from the displayed set (completeness)
 * - Footer total_debit == arithmetic sum of all displayed debit values
 * - Footer total_kredit == arithmetic sum of all displayed kredit values
 * - If all rows have debit=0 and kredit=0, the displayed set is empty
 *
 * Run: php property_test_modal_display_filter.php
 */

// ============================================================
// Pure functions under test (replicate modal display logic)
// ============================================================

/**
 * Filter rows for modal display: only rows with debit > 0 OR kredit > 0
 * Mirrors the logic in get_detail_by_transaksi() and modal_detail.php view
 *
 * @param array $rows Array of associative arrays with 'debit' and 'kredit' keys
 * @return array Filtered rows (preserving keys via array_values)
 */
function filter_display_rows($rows)
{
    return array_values(array_filter($rows, function ($row) {
        return $row['debit'] > 0 || $row['kredit'] > 0;
    }));
}

/**
 * Calculate footer totals from displayed rows
 * Mirrors the footer calculation in modal_detail.php view
 *
 * @param array $displayed_rows Filtered array of rows
 * @return array [total_debit, total_kredit]
 */
function calculate_footer_totals($displayed_rows)
{
    $total_debit  = 0;
    $total_kredit = 0;
    foreach ($displayed_rows as $row) {
        $total_debit  += $row['debit'];
        $total_kredit += $row['kredit'];
    }
    return [$total_debit, $total_kredit];
}

// ============================================================
// Random generators
// ============================================================

/**
 * Generate a random debit or kredit value.
 * 30% chance of being 0, otherwise random between 1 and 999999.
 *
 * @return float
 */
function random_amount_with_zero_chance()
{
    // 30% chance of being 0
    if (mt_rand(1, 100) <= 30) {
        return 0;
    }
    return (float) mt_rand(1, 999999);
}

/**
 * Generate a random collection of journal rows.
 *
 * @param int $count Number of rows to generate
 * @return array Array of associative arrays with 'debit' and 'kredit'
 */
function generate_random_journal_rows($count)
{
    $rows = [];
    for ($i = 0; $i < $count; $i++) {
        $rows[] = [
            'debit'  => random_amount_with_zero_chance(),
            'kredit' => random_amount_with_zero_chance(),
        ];
    }
    return $rows;
}

/**
 * Generate a collection where ALL rows have debit=0 and kredit=0.
 *
 * @param int $count Number of rows
 * @return array Array with all debit and kredit = 0
 */
function generate_all_zero_rows($count)
{
    $rows = [];
    for ($i = 0; $i < $count; $i++) {
        $rows[] = ['debit' => 0, 'kredit' => 0];
    }
    return $rows;
}

// ============================================================
// Test runner
// ============================================================

$iterations = 150;
$passed = 0;
$failed = 0;
$failures = [];

echo "==========================================================\n";
echo "Property Test: Modal Display Filter Invariant\n";
echo "Feature: jurnal-payment-petty-cash, Property 2: Modal Display Filter Invariant\n";
echo "Validates: Requirements 2.4, 2.5\n";
echo "Iterations: {$iterations}\n";
echo "==========================================================\n\n";

for ($i = 1; $i <= $iterations; $i++) {
    $errors = [];
    $scenario = '';
    $rows = [];

    // Distribute scenarios across iterations
    $rand = mt_rand(1, 100);

    if ($rand <= 10) {
        // Scenario: All zeros (~10% of iterations)
        $scenario = 'all_zeros';
        $row_count = mt_rand(1, 15);
        $rows = generate_all_zero_rows($row_count);
    } else {
        // Scenario: Random mix (~90% of iterations)
        $scenario = 'random_mix';
        $row_count = mt_rand(1, 15);
        $rows = generate_random_journal_rows($row_count);
    }

    // Apply the functions under test
    $displayed = filter_display_rows($rows);
    list($footer_total_debit, $footer_total_kredit) = calculate_footer_totals($displayed);

    // --------------------------------------------------------
    // Assertion 1: ALL displayed rows have debit > 0 OR kredit > 0
    // (No zero-zero rows in displayed set)
    // --------------------------------------------------------
    foreach ($displayed as $idx => $row) {
        if ($row['debit'] <= 0 && $row['kredit'] <= 0) {
            $errors[] = "Displayed row [{$idx}] has debit={$row['debit']} and kredit={$row['kredit']} "
                . "(both <= 0, should not be displayed)";
            break; // Report only first violation
        }
    }

    // --------------------------------------------------------
    // Assertion 2: NO rows with debit > 0 or kredit > 0 are missing
    // from displayed set (completeness check)
    // --------------------------------------------------------
    $expected_count = 0;
    foreach ($rows as $row) {
        if ($row['debit'] > 0 || $row['kredit'] > 0) {
            $expected_count++;
        }
    }
    if (count($displayed) !== $expected_count) {
        $errors[] = "Completeness violation: expected {$expected_count} qualifying rows in display, "
            . "got " . count($displayed);
    }

    // --------------------------------------------------------
    // Assertion 3: footer total_debit == sum of all displayed row debits (exact match)
    // --------------------------------------------------------
    $manual_sum_debit = 0;
    foreach ($displayed as $row) {
        $manual_sum_debit += $row['debit'];
    }
    if (abs($footer_total_debit - $manual_sum_debit) > 0.001) {
        $errors[] = "Footer total_debit ({$footer_total_debit}) != sum of displayed debits ({$manual_sum_debit})";
    }

    // --------------------------------------------------------
    // Assertion 4: footer total_kredit == sum of all displayed row kredits (exact match)
    // --------------------------------------------------------
    $manual_sum_kredit = 0;
    foreach ($displayed as $row) {
        $manual_sum_kredit += $row['kredit'];
    }
    if (abs($footer_total_kredit - $manual_sum_kredit) > 0.001) {
        $errors[] = "Footer total_kredit ({$footer_total_kredit}) != sum of displayed kredits ({$manual_sum_kredit})";
    }

    // --------------------------------------------------------
    // Assertion 5: If all input rows have debit=0 and kredit=0,
    // displayed set should be empty
    // --------------------------------------------------------
    $all_zero = true;
    foreach ($rows as $row) {
        if ($row['debit'] > 0 || $row['kredit'] > 0) {
            $all_zero = false;
            break;
        }
    }
    if ($all_zero && count($displayed) !== 0) {
        $errors[] = "All input rows are zero but displayed set is not empty (count=" . count($displayed) . ")";
    }

    // Record result
    if (empty($errors)) {
        $passed++;
    } else {
        $failed++;
        if (count($failures) < 5) {
            $failures[] = [
                'iteration'      => $i,
                'scenario'       => $scenario,
                'input_rows'     => count($rows),
                'displayed_rows' => count($displayed),
                'footer_debit'   => $footer_total_debit,
                'footer_kredit'  => $footer_total_kredit,
                'errors'         => $errors,
                'sample_input'   => array_slice($rows, 0, 5), // First 5 rows for debug
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
        echo "    Input rows: {$f['input_rows']}, Displayed rows: {$f['displayed_rows']}\n";
        echo "    Footer: debit={$f['footer_debit']}, kredit={$f['footer_kredit']}\n";
        echo "    Sample input (first 5): \n";
        foreach ($f['sample_input'] as $idx => $row) {
            echo "      [{$idx}] debit={$row['debit']}, kredit={$row['kredit']}\n";
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
