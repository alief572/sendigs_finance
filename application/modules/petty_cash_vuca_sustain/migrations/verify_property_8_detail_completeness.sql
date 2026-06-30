-- =============================================================================
-- Property 8: Detail Retrieval Completeness
-- =============================================================================
--
-- Validates: Requirements 6.2, 6.3
--
-- Property Statement:
-- For any Payment Hutang record with a valid pelaporan_id that references a
-- pelaporan containing N pencatatan records, the detail retrieval SHALL return
-- exactly N pencatatan entries, each containing: no_pencatatan, tanggal,
-- request_by, keterangan, and nominal fields that match the source data.
--
-- How to use:
-- Run each query below against the database. Empty result sets for violation
-- checks = all good. Any rows returned indicate violations that need investigation.
--
-- Code Review Notes (get_payment_hutang method):
-- ✓ 1. Header query: SELECT a.* FROM tr_petty_cash_vuca_sustain a WHERE a.id = ?
--       Returns full record including pelaporan_id for subsequent queries
-- ✓ 2. Pencatatan query:
--       SELECT p.id, p.no_pencatatan, p.tanggal, p.request_by, p.keterangan, p.grand_total as nominal
--       FROM tr_pelaporan_petty_cash_detail pd
--       INNER JOIN tr_expense_petty_cash p ON p.id = pd.pencatatan_id
--       WHERE pd.pelaporan_id = ?
--       ORDER BY p.tanggal ASC
--       → Uses INNER JOIN so only pencatatan with valid references are returned
--       → All required fields (no_pencatatan, tanggal, request_by, keterangan, nominal) are selected
-- ✓ 3. Detail items per pencatatan:
--       SELECT d.*, c.nama as coa_nama
--       FROM tr_expense_petty_cash_detail d
--       LEFT JOIN coa_master c ON c.no_perkiraan = d.coa_code
--       WHERE d.pencatatan_id = ?
--       → Sub-items fetched per pencatatan for expandable detail display
-- ✓ 4. JOIN path is correct:
--       tr_petty_cash_vuca_sustain.pelaporan_id
--         → tr_pelaporan_petty_cash_detail.pelaporan_id
--           → tr_expense_petty_cash.id (via pencatatan_id)
-- ⚠ 5. INNER JOIN on tr_expense_petty_cash means if a pencatatan record was deleted
--       (soft or hard), it would silently reduce the count. This is acceptable
--       behavior since it reflects actual available data.
-- ✓ 6. Fields returned match Requirements 6.2 specification:
--       no_pencatatan, tanggal, request_by, keterangan, nominal (grand_total)
--
-- =============================================================================


-- =============================================================================
-- CHECK 1: Pencatatan Count Matches jumlah_pencatatan Field
-- For each tr_petty_cash_vuca_sustain record, the actual count of pencatatan
-- in tr_pelaporan_petty_cash_detail (via pelaporan_id) should equal the
-- jumlah_pencatatan stored in the record.
-- Expected: Empty result (counts always match)
-- =============================================================================
SELECT
    pcvs.id,
    pcvs.no_payment_hutang,
    pcvs.pelaporan_id,
    pcvs.jumlah_pencatatan AS stored_count,
    COUNT(pd.id) AS actual_count,
    'COUNT_MISMATCH' AS violation_type,
    CONCAT('jumlah_pencatatan=', pcvs.jumlah_pencatatan,
           ' but actual pencatatan in pelaporan_detail=', COUNT(pd.id)) AS description
FROM tr_petty_cash_vuca_sustain pcvs
LEFT JOIN tr_pelaporan_petty_cash_detail pd ON pd.pelaporan_id = pcvs.pelaporan_id
GROUP BY pcvs.id, pcvs.no_payment_hutang, pcvs.pelaporan_id, pcvs.jumlah_pencatatan
HAVING pcvs.jumlah_pencatatan <> COUNT(pd.id);


-- =============================================================================
-- CHECK 2: All Pencatatan Have Valid References to tr_expense_petty_cash
-- For each pencatatan referenced in tr_pelaporan_petty_cash_detail (for our
-- Payment Hutang records), the pencatatan_id must reference a valid record
-- in tr_expense_petty_cash.
-- Expected: Empty result (no broken references / orphan pencatatan_id)
-- =============================================================================
SELECT
    pcvs.id AS pcvs_id,
    pcvs.no_payment_hutang,
    pd.id AS detail_id,
    pd.pencatatan_id,
    'BROKEN_PENCATATAN_REFERENCE' AS violation_type,
    CONCAT('pelaporan_detail.pencatatan_id=', IFNULL(pd.pencatatan_id, 'NULL'),
           ' does not exist in tr_expense_petty_cash') AS description
FROM tr_petty_cash_vuca_sustain pcvs
INNER JOIN tr_pelaporan_petty_cash_detail pd ON pd.pelaporan_id = pcvs.pelaporan_id
LEFT JOIN tr_expense_petty_cash p ON p.id = pd.pencatatan_id
WHERE p.id IS NULL;


-- =============================================================================
-- CHECK 3: no_pencatatan Field Exists and Is Not Empty
-- For each pencatatan linked to our Payment Hutang records via the JOIN path,
-- verify that no_pencatatan is populated (non-NULL, non-empty).
-- Expected: Empty result (all pencatatan have valid no_pencatatan)
-- =============================================================================
SELECT
    pcvs.id AS pcvs_id,
    pcvs.no_payment_hutang,
    p.id AS pencatatan_id,
    p.no_pencatatan,
    'MISSING_NO_PENCATATAN' AS violation_type,
    'tr_expense_petty_cash.no_pencatatan is NULL or empty' AS description
FROM tr_petty_cash_vuca_sustain pcvs
INNER JOIN tr_pelaporan_petty_cash_detail pd ON pd.pelaporan_id = pcvs.pelaporan_id
INNER JOIN tr_expense_petty_cash p ON p.id = pd.pencatatan_id
WHERE p.no_pencatatan IS NULL OR p.no_pencatatan = '';


-- =============================================================================
-- CHECK 4: tanggal Field Exists and Is Valid Date
-- For each pencatatan linked to our Payment Hutang records, verify that
-- tanggal is populated and contains a valid date value.
-- Expected: Empty result (all pencatatan have valid tanggal)
-- =============================================================================
SELECT
    pcvs.id AS pcvs_id,
    pcvs.no_payment_hutang,
    p.id AS pencatatan_id,
    p.no_pencatatan,
    p.tanggal,
    'MISSING_OR_INVALID_TANGGAL' AS violation_type,
    CONCAT('tr_expense_petty_cash.tanggal=', IFNULL(p.tanggal, 'NULL')) AS description
FROM tr_petty_cash_vuca_sustain pcvs
INNER JOIN tr_pelaporan_petty_cash_detail pd ON pd.pelaporan_id = pcvs.pelaporan_id
INNER JOIN tr_expense_petty_cash p ON p.id = pd.pencatatan_id
WHERE p.tanggal IS NULL OR p.tanggal = '0000-00-00';


-- =============================================================================
-- CHECK 5: request_by Field Exists and Is Not Empty
-- For each pencatatan linked to our Payment Hutang records, verify that
-- request_by is populated.
-- Expected: Empty result (all pencatatan have valid request_by)
-- =============================================================================
SELECT
    pcvs.id AS pcvs_id,
    pcvs.no_payment_hutang,
    p.id AS pencatatan_id,
    p.no_pencatatan,
    p.request_by,
    'MISSING_REQUEST_BY' AS violation_type,
    'tr_expense_petty_cash.request_by is NULL or empty' AS description
FROM tr_petty_cash_vuca_sustain pcvs
INNER JOIN tr_pelaporan_petty_cash_detail pd ON pd.pelaporan_id = pcvs.pelaporan_id
INNER JOIN tr_expense_petty_cash p ON p.id = pd.pencatatan_id
WHERE p.request_by IS NULL OR p.request_by = '';


-- =============================================================================
-- CHECK 6: keterangan Field Exists and Is Not Empty
-- For each pencatatan linked to our Payment Hutang records, verify that
-- keterangan is populated.
-- Expected: Empty result (all pencatatan have valid keterangan)
-- =============================================================================
SELECT
    pcvs.id AS pcvs_id,
    pcvs.no_payment_hutang,
    p.id AS pencatatan_id,
    p.no_pencatatan,
    p.keterangan,
    'MISSING_KETERANGAN' AS violation_type,
    'tr_expense_petty_cash.keterangan is NULL or empty' AS description
FROM tr_petty_cash_vuca_sustain pcvs
INNER JOIN tr_pelaporan_petty_cash_detail pd ON pd.pelaporan_id = pcvs.pelaporan_id
INNER JOIN tr_expense_petty_cash p ON p.id = pd.pencatatan_id
WHERE p.keterangan IS NULL OR p.keterangan = '';


-- =============================================================================
-- CHECK 7: nominal (grand_total) Field Exists and Is Valid
-- For each pencatatan linked to our Payment Hutang records, verify that
-- grand_total (used as nominal in the view) is populated and > 0.
-- Expected: Empty result (all pencatatan have valid nominal/grand_total)
-- =============================================================================
SELECT
    pcvs.id AS pcvs_id,
    pcvs.no_payment_hutang,
    p.id AS pencatatan_id,
    p.no_pencatatan,
    p.grand_total AS nominal,
    'INVALID_NOMINAL' AS violation_type,
    CONCAT('tr_expense_petty_cash.grand_total=', IFNULL(p.grand_total, 'NULL'),
           ' (expected > 0)') AS description
FROM tr_petty_cash_vuca_sustain pcvs
INNER JOIN tr_pelaporan_petty_cash_detail pd ON pd.pelaporan_id = pcvs.pelaporan_id
INNER JOIN tr_expense_petty_cash p ON p.id = pd.pencatatan_id
WHERE p.grand_total IS NULL OR p.grand_total <= 0;


-- =============================================================================
-- CHECK 8: JOIN Path Integrity (Full Chain Validation)
-- Validate the complete JOIN path works for all records:
-- tr_petty_cash_vuca_sustain → tr_pelaporan_petty_cash_detail → tr_expense_petty_cash
-- Records where pelaporan_id does not match any pelaporan_detail entry indicate
-- a broken reference chain.
-- Expected: Empty result (all pelaporan_id references are valid)
-- =============================================================================
SELECT
    pcvs.id,
    pcvs.no_payment_hutang,
    pcvs.pelaporan_id,
    pcvs.jumlah_pencatatan,
    'BROKEN_JOIN_PATH' AS violation_type,
    CONCAT('pelaporan_id=', IFNULL(pcvs.pelaporan_id, 'NULL'),
           ' has no matching records in tr_pelaporan_petty_cash_detail') AS description
FROM tr_petty_cash_vuca_sustain pcvs
LEFT JOIN tr_pelaporan_petty_cash_detail pd ON pd.pelaporan_id = pcvs.pelaporan_id
WHERE pd.id IS NULL
  AND pcvs.jumlah_pencatatan > 0;


-- =============================================================================
-- CHECK 9: Retrievable Pencatatan Count Matches After Full JOIN
-- Simulates the exact query used in get_payment_hutang() to verify that
-- the INNER JOIN between tr_pelaporan_petty_cash_detail and tr_expense_petty_cash
-- returns the expected number of rows matching jumlah_pencatatan.
-- This is the "end-to-end" completeness check.
-- Expected: Empty result (retrieved count via full JOIN = jumlah_pencatatan)
-- =============================================================================
SELECT
    pcvs.id,
    pcvs.no_payment_hutang,
    pcvs.pelaporan_id,
    pcvs.jumlah_pencatatan AS expected_count,
    COUNT(p.id) AS retrievable_count,
    'RETRIEVAL_COUNT_MISMATCH' AS violation_type,
    CONCAT('Expected ', pcvs.jumlah_pencatatan, ' pencatatan via full JOIN path, got ', COUNT(p.id)) AS description
FROM tr_petty_cash_vuca_sustain pcvs
LEFT JOIN tr_pelaporan_petty_cash_detail pd ON pd.pelaporan_id = pcvs.pelaporan_id
LEFT JOIN tr_expense_petty_cash p ON p.id = pd.pencatatan_id
GROUP BY pcvs.id, pcvs.no_payment_hutang, pcvs.pelaporan_id, pcvs.jumlah_pencatatan
HAVING pcvs.jumlah_pencatatan <> COUNT(p.id);


-- =============================================================================
-- CHECK 10: Verify Sum of Nominal Matches Grand Total
-- For each Payment Hutang record, the sum of individual pencatatan nominal
-- (grand_total from tr_expense_petty_cash) should equal the grand_total
-- stored in tr_petty_cash_vuca_sustain.
-- Expected: Empty result (sums always match)
-- =============================================================================
SELECT
    pcvs.id,
    pcvs.no_payment_hutang,
    pcvs.grand_total AS stored_grand_total,
    IFNULL(SUM(p.grand_total), 0) AS sum_pencatatan_nominal,
    'GRAND_TOTAL_SUM_MISMATCH' AS violation_type,
    CONCAT('stored grand_total=', pcvs.grand_total,
           ' vs sum of pencatatan nominal=', IFNULL(SUM(p.grand_total), 0)) AS description
FROM tr_petty_cash_vuca_sustain pcvs
LEFT JOIN tr_pelaporan_petty_cash_detail pd ON pd.pelaporan_id = pcvs.pelaporan_id
LEFT JOIN tr_expense_petty_cash p ON p.id = pd.pencatatan_id
GROUP BY pcvs.id, pcvs.no_payment_hutang, pcvs.grand_total
HAVING pcvs.grand_total <> IFNULL(SUM(p.grand_total), 0);


-- =============================================================================
-- SUMMARY: Combined Violation Count
-- Quick overview of all violations found across checks.
-- Expected: All counts = 0
-- =============================================================================
SELECT 'COUNT_MISMATCH' AS check_name, COUNT(*) AS violations
FROM (
    SELECT pcvs.id
    FROM tr_petty_cash_vuca_sustain pcvs
    LEFT JOIN tr_pelaporan_petty_cash_detail pd ON pd.pelaporan_id = pcvs.pelaporan_id
    GROUP BY pcvs.id, pcvs.jumlah_pencatatan
    HAVING pcvs.jumlah_pencatatan <> COUNT(pd.id)
) t1

UNION ALL

SELECT 'BROKEN_PENCATATAN_REFERENCE', COUNT(*)
FROM tr_petty_cash_vuca_sustain pcvs
INNER JOIN tr_pelaporan_petty_cash_detail pd ON pd.pelaporan_id = pcvs.pelaporan_id
LEFT JOIN tr_expense_petty_cash p ON p.id = pd.pencatatan_id
WHERE p.id IS NULL

UNION ALL

SELECT 'MISSING_NO_PENCATATAN', COUNT(*)
FROM tr_petty_cash_vuca_sustain pcvs
INNER JOIN tr_pelaporan_petty_cash_detail pd ON pd.pelaporan_id = pcvs.pelaporan_id
INNER JOIN tr_expense_petty_cash p ON p.id = pd.pencatatan_id
WHERE p.no_pencatatan IS NULL OR p.no_pencatatan = ''

UNION ALL

SELECT 'MISSING_OR_INVALID_TANGGAL', COUNT(*)
FROM tr_petty_cash_vuca_sustain pcvs
INNER JOIN tr_pelaporan_petty_cash_detail pd ON pd.pelaporan_id = pcvs.pelaporan_id
INNER JOIN tr_expense_petty_cash p ON p.id = pd.pencatatan_id
WHERE p.tanggal IS NULL OR p.tanggal = '0000-00-00'

UNION ALL

SELECT 'MISSING_REQUEST_BY', COUNT(*)
FROM tr_petty_cash_vuca_sustain pcvs
INNER JOIN tr_pelaporan_petty_cash_detail pd ON pd.pelaporan_id = pcvs.pelaporan_id
INNER JOIN tr_expense_petty_cash p ON p.id = pd.pencatatan_id
WHERE p.request_by IS NULL OR p.request_by = ''

UNION ALL

SELECT 'MISSING_KETERANGAN', COUNT(*)
FROM tr_petty_cash_vuca_sustain pcvs
INNER JOIN tr_pelaporan_petty_cash_detail pd ON pd.pelaporan_id = pcvs.pelaporan_id
INNER JOIN tr_expense_petty_cash p ON p.id = pd.pencatatan_id
WHERE p.keterangan IS NULL OR p.keterangan = ''

UNION ALL

SELECT 'INVALID_NOMINAL', COUNT(*)
FROM tr_petty_cash_vuca_sustain pcvs
INNER JOIN tr_pelaporan_petty_cash_detail pd ON pd.pelaporan_id = pcvs.pelaporan_id
INNER JOIN tr_expense_petty_cash p ON p.id = pd.pencatatan_id
WHERE p.grand_total IS NULL OR p.grand_total <= 0

UNION ALL

SELECT 'BROKEN_JOIN_PATH', COUNT(*)
FROM tr_petty_cash_vuca_sustain pcvs
LEFT JOIN tr_pelaporan_petty_cash_detail pd ON pd.pelaporan_id = pcvs.pelaporan_id
WHERE pd.id IS NULL AND pcvs.jumlah_pencatatan > 0

UNION ALL

SELECT 'RETRIEVAL_COUNT_MISMATCH', COUNT(*)
FROM (
    SELECT pcvs.id
    FROM tr_petty_cash_vuca_sustain pcvs
    LEFT JOIN tr_pelaporan_petty_cash_detail pd ON pd.pelaporan_id = pcvs.pelaporan_id
    LEFT JOIN tr_expense_petty_cash p ON p.id = pd.pencatatan_id
    GROUP BY pcvs.id, pcvs.jumlah_pencatatan
    HAVING pcvs.jumlah_pencatatan <> COUNT(p.id)
) t2

UNION ALL

SELECT 'GRAND_TOTAL_SUM_MISMATCH', COUNT(*)
FROM (
    SELECT pcvs.id
    FROM tr_petty_cash_vuca_sustain pcvs
    LEFT JOIN tr_pelaporan_petty_cash_detail pd ON pd.pelaporan_id = pcvs.pelaporan_id
    LEFT JOIN tr_expense_petty_cash p ON p.id = pd.pencatatan_id
    GROUP BY pcvs.id, pcvs.grand_total
    HAVING pcvs.grand_total <> IFNULL(SUM(p.grand_total), 0)
) t3;
