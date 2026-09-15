<?php

/**
 * Self-check: Petty Cash posting routing + per-side batch split.
 *
 * Verifies the decision made in save_posting_jurnal():
 *   - Petty Cash rows spanning >1 company that include VUCA/SUSTAIN  → inter-company
 *     posting (2 sides, 2 target DBs).
 *   - Single-company Petty Cash                                      → single-DB posting.
 * And that post_jurnal_intercompany() splits detail rows to the correct side
 * (company vs STM) with per-side jml == sum(debit) of that side.
 *
 * Pure re-implementation of the branching logic — no DB, no framework.
 * Run: php test_posting_routing.php
 */

// --- Logic under test (mirrors controller + model) ---

function decide_route($jenis_transaksi, $rows)
{
    if ($jenis_transaksi !== 'Petty Cash') {
        return 'other';
    }
    $companies = [];
    foreach ($rows as $r) {
        $c = strtoupper($r['nm_company']);
        if ($c !== '') $companies[$c] = true;
    }
    $inter = (count($companies) > 1) && (isset($companies['VUCA']) || isset($companies['SUSTAIN']));
    return $inter ? 'intercompany' : 'petty_cash_single';
}

function split_sides($rows, $company_side)
{
    $company_id = ($company_side === 'VUCA') ? '4' : '6';
    $batch_company = [];
    $batch_stm = [];
    $jml_company = 0;
    $jml_stm = 0;
    foreach ($rows as $r) {
        if ($r['debit'] <= 0 && $r['kredit'] <= 0) continue;
        if ($r['id_company'] == $company_id || strtoupper($r['nm_company']) === $company_side) {
            $batch_company[] = $r;
            $jml_company += $r['debit'];
        } elseif ($r['id_company'] == '5' || strtoupper($r['nm_company']) === 'STM') {
            $batch_stm[] = $r;
            $jml_stm += $r['debit'];
        }
    }
    return [$batch_company, $batch_stm, $jml_company, $jml_stm];
}

// --- Fixtures ---

$sustain_intercompany = [
    ['coa' => '1103-01-09', 'nm_company' => 'SUSTAIN', 'id_company' => '6', 'debit' => 170098, 'kredit' => 0],
    ['coa' => '2103-01-02', 'nm_company' => 'SUSTAIN', 'id_company' => '6', 'debit' => 0, 'kredit' => 170098],
    ['coa' => '1103-01-02', 'nm_company' => 'STM',     'id_company' => '5', 'debit' => 170098, 'kredit' => 0],
    ['coa' => '1101-01-02', 'nm_company' => 'STM',     'id_company' => '5', 'debit' => 0, 'kredit' => 170098],
];

$stm_only = [
    ['coa' => '5xxx', 'nm_company' => 'STM', 'id_company' => '5', 'debit' => 50000, 'kredit' => 0],
    ['coa' => '1101-01-02', 'nm_company' => 'STM', 'id_company' => '5', 'debit' => 0, 'kredit' => 50000],
];

$errors = [];

// 1. Inter-company detection
if (decide_route('Petty Cash', $sustain_intercompany) !== 'intercompany') {
    $errors[] = 'SUSTAIN+STM petty cash should route to intercompany';
}

// 2. Single-company detection
if (decide_route('Petty Cash', $stm_only) !== 'petty_cash_single') {
    $errors[] = 'STM-only petty cash should route to single-DB posting';
}

// 3. Side split correctness
list($bc, $bs, $jc, $js) = split_sides($sustain_intercompany, 'SUSTAIN');
if (count($bc) !== 2) $errors[] = "SUSTAIN side should have 2 rows, got " . count($bc);
if (count($bs) !== 2) $errors[] = "STM side should have 2 rows, got " . count($bs);
foreach ($bc as $r) if (strtoupper($r['nm_company']) !== 'SUSTAIN') $errors[] = "STM row leaked into SUSTAIN batch: {$r['coa']}";
foreach ($bs as $r) if (strtoupper($r['nm_company']) !== 'STM') $errors[] = "SUSTAIN row leaked into STM batch: {$r['coa']}";

// 4. Per-side jml == sum(debit) of that side, and each side balances
if ($jc != 170098) $errors[] = "SUSTAIN side jml should be 170098, got {$jc}";
if ($js != 170098) $errors[] = "STM side jml should be 170098, got {$js}";

$sum = function ($rows, $k) { $t = 0; foreach ($rows as $r) $t += $r[$k]; return $t; };
if ($sum($bc, 'debit') !== $sum($bc, 'kredit')) $errors[] = 'SUSTAIN side not balanced';
if ($sum($bs, 'debit') !== $sum($bs, 'kredit')) $errors[] = 'STM side not balanced';

// --- Report ---
if (empty($errors)) {
    echo "ALL POSTING ROUTING CHECKS PASSED\n";
    exit(0);
}
echo "FAILED:\n";
foreach ($errors as $e) echo "  - {$e}\n";
exit(1);
