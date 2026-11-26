<?php
$c = @mysqli_connect('206.84.97.107', 'alief', 'Alief@ssc1', 'db_sendigs_ss');
if (!$c) {
    echo "SKIP: Database connection not available for live grouping verification\n";
    exit(0);
}

function verify($condition, $message) {
    if (!$condition) {
        throw new RuntimeException("Assertion failed: $message");
    }
}

$base_grouped_sql = "
    SELECT 
        COALESCE(NULLIF(a.id_payment, ''), a.id) as no_payment,
        GROUP_CONCAT(DISTINCT a.no_doc ORDER BY a.id ASC SEPARATOR ', ') as no_doc,
        MAX(a.tgl_bayar) as tgl_bayar,
        SUBSTRING_INDEX(GROUP_CONCAT(COALESCE(NULLIF(a.created_by, ''), a.nm_supplier, '-') ORDER BY a.id ASC SEPARATOR '|||'), '|||', 1) as requestor,
        COALESCE(NULLIF(MAX(a.payment_bank), 0), SUM(a.jumlah), 0) as nilai_bayar,
        MAX(a.keterangan_pembayaran) as keterangan,
        GROUP_CONCAT(DISTINCT a.link_doc SEPARATOR ',') as link_docs
    FROM payment_approve a
    LEFT JOIN tr_expense b ON b.no_doc = a.no_doc
    WHERE a.status = 2 AND a.no_doc NOT LIKE '%INV-%' AND a.no_doc NOT LIKE '%PI-%' AND a.id_payment IS NOT NULL
    GROUP BY COALESCE(NULLIF(a.id_payment, ''), a.id)
";

// Test 1: BK--1026-0002 returns strictly 1 row
$search = 'BK--1026-0002';
$sql = "SELECT * FROM ({$base_grouped_sql}) as grouped WHERE grouped.no_payment LIKE '%$search%' OR grouped.no_doc LIKE '%$search%'";
$res = mysqli_query($c, $sql);
verify($res && mysqli_num_rows($res) === 1, "BK--1026-0002 must return exactly 1 row (returned " . mysqli_num_rows($res) . ")");
$row = mysqli_fetch_assoc($res);

verify($row['no_payment'] === 'BK--1026-0002', "no_payment must be BK--1026-0002");
verify((float)$row['nilai_bayar'] === 8920202.0, "nilai_bayar must be 8920202 (got " . $row['nilai_bayar'] . ")");
verify($row['requestor'] === 'finance', "requestor must be finance");

$expected_docs = [
    'PERIODIK-2026-00181',
    'PERIODIK-2026-00177',
    'RPC-2026-0029',
    'DPM-2026-00081',
    'DPM-2026-00083',
    'DPM-2026-00082'
];
foreach ($expected_docs as $doc) {
    verify(strpos($row['no_doc'], $doc) !== false, "no_doc must contain $doc (got: {$row['no_doc']})");
}
verify(substr_count($row['no_doc'], ',') === 5, "no_doc must separate 6 documents with 5 commas");

// Test 2: Search by child document finds the voucher
$child_doc = 'RPC-2026-0029';
$sql_child = "SELECT * FROM ({$base_grouped_sql}) as grouped WHERE grouped.no_payment LIKE '%$child_doc%' OR grouped.no_doc LIKE '%$child_doc%'";
$res_child = mysqli_query($c, $sql_child);
verify($res_child && mysqli_num_rows($res_child) === 1, "Search by $child_doc must find parent voucher");
$row_child = mysqli_fetch_assoc($res_child);
verify($row_child['no_payment'] === 'BK--1026-0002', "Parent voucher must be BK--1026-0002");

// Test 3: Other grouped vouchers appear strictly once
$vouchers = ['BK--0126-0003', 'BK--0126-0006', 'BK--0126-0008', 'BK--0126-0014'];
foreach ($vouchers as $v) {
    $sql_v = "SELECT * FROM ({$base_grouped_sql}) as grouped WHERE grouped.no_payment = '$v'";
    $res_v = mysqli_query($c, $sql_v);
    verify($res_v && mysqli_num_rows($res_v) === 1, "Voucher $v must return exactly 1 row");
    $r_v = mysqli_fetch_assoc($res_v);
    verify(strpos($r_v['no_doc'], ',') !== false, "Voucher $v must contain multiple comma-separated documents");
}

echo "PASS: Payment list Non-PR grouping test (all assertions passed)\n";
