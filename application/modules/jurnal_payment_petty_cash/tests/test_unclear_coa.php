<?php

/**
 * Self-check: unclear-COA detection that blocks posting.
 * Mirrors Jurnal_payment_petty_cash_model::find_unclear_coa().
 *
 * Run: php test_unclear_coa.php
 */

$UNCLEAR = ['2103-01-01', '2103-01-02'];

function find_unclear_coa($rows, $unclear)
{
    $found = [];
    foreach ($rows as $row) {
        $coa = isset($row['coa']) ? $row['coa'] : '';
        if (in_array($coa, $unclear, true) && !in_array($coa, $found, true)) {
            $found[] = $coa;
        }
    }
    return $found;
}

$errors = [];

// 1. SUSTAIN inter-company set contains 2103-01-02 → must be flagged
$sustain = [
    ['coa' => '1103-01-09'],
    ['coa' => '2103-01-02'],
    ['coa' => '1103-01-02'],
    ['coa' => '1101-01-02'],
];
$r = find_unclear_coa($sustain, $UNCLEAR);
if ($r !== ['2103-01-02']) $errors[] = 'SUSTAIN set should flag exactly [2103-01-02], got ' . json_encode($r);

// 2. VUCA set contains 2103-01-01 → flagged
$vuca = [['coa' => '5104-01-01'], ['coa' => '2103-01-01'], ['coa' => '1101-01-02']];
$r = find_unclear_coa($vuca, $UNCLEAR);
if ($r !== ['2103-01-01']) $errors[] = 'VUCA set should flag [2103-01-01], got ' . json_encode($r);

// 3. Clean STM-only petty cash → NOT flagged (posting allowed)
$stm = [['coa' => '5104-01-01'], ['coa' => '1101-01-02']];
$r = find_unclear_coa($stm, $UNCLEAR);
if ($r !== []) $errors[] = 'Clean STM set should NOT be flagged, got ' . json_encode($r);

// 4. No duplicates when COA appears multiple times
$dup = [['coa' => '2103-01-02'], ['coa' => '2103-01-02'], ['coa' => '1101-01-02']];
$r = find_unclear_coa($dup, $UNCLEAR);
if ($r !== ['2103-01-02']) $errors[] = 'Duplicate unclear COA should be reported once, got ' . json_encode($r);

// 5. Both unclear COA present → both reported
$both = [['coa' => '2103-01-01'], ['coa' => '2103-01-02']];
$r = find_unclear_coa($both, $UNCLEAR);
if ($r !== ['2103-01-01', '2103-01-02']) $errors[] = 'Both unclear COA should be reported, got ' . json_encode($r);

if (empty($errors)) {
    echo "ALL UNCLEAR-COA CHECKS PASSED\n";
    exit(0);
}
echo "FAILED:\n";
foreach ($errors as $e) echo "  - {$e}\n";
exit(1);
