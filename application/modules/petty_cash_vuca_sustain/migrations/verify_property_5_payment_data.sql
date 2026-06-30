-- =============================================================================
-- Property 5: Payment Hutang Creates Correct Request Payment Data
-- =============================================================================
--
-- Validates: Requirements 4.2, 4.3
--
-- Property Statement:
-- For any Payment Hutang record with status "draft", when the Payment Hutang
-- action is executed, a new record SHALL be inserted into `request_payment` with:
--   - no_doc = No Payment Hutang from tr_petty_cash_vuca_sustain
--   - tipe = "petty_cash_hutang"
--   - keperluan = "Payment Hutang Petty Cash - " + No Payment Hutang
--   - jumlah = grand_total from tr_petty_cash_vuca_sustain
--   - status = 0 (at creation time)
-- Additionally, the original record's status SHALL change to "waiting payment".
--
-- How to use:
-- Run each query below against the database. Empty result sets for violation
-- checks = all good. Any rows returned indicate violations that need investigation.
--
-- Code Review Notes (process_payment_hutang method):
-- ✓ 1. Transaction wraps both INSERT and UPDATE (BEGIN → COMMIT/ROLLBACK)
-- ✓ 2. SELECT ... FOR UPDATE locks the record to prevent concurrent processing
-- ✓ 3. Status check (status = 'draft') is done in the SELECT query itself
-- ✓ 4. INSERT into request_payment uses:
--       - no_doc     = $record->no_payment_hutang
--       - nama       = $record->nama_pembuat
--       - tgl_doc    = date('Y-m-d') (today)
--       - tanggal    = date('Y-m-d') (today)
--       - keperluan  = 'Payment Hutang Petty Cash - ' . $record->no_payment_hutang
--       - tipe       = 'petty_cash_hutang'
--       - jumlah     = $record->grand_total
--       - status     = 0
--       - created_by = $user_id (user performing the action)
--       - created_on = date('Y-m-d H:i:s')
-- ✓ 5. UPDATE sets status = 'waiting payment', modified_on, modified_by
-- ✓ 6. On transaction failure, ROLLBACK is called (no partial state)
-- ✓ 7. Return false if record not found or status is not 'draft'
--
-- =============================================================================


-- =============================================================================
-- CHECK 1: Every Processed Record Has a Corresponding request_payment Entry
-- For all records with status 'waiting payment' or 'done payment' (meaning
-- Payment Hutang was executed), a matching request_payment record MUST exist.
-- Expected: Empty result (no orphan processed records without request_payment)
-- =============================================================================
SELECT
    pcvs.id,
    pcvs.no_payment_hutang,
    pcvs.status,
    pcvs.grand_total,
    'MISSING_REQUEST_PAYMENT' AS violation_type,
    'Processed record has no corresponding request_payment entry' AS description
FROM tr_petty_cash_vuca_sustain pcvs
LEFT JOIN request_payment rp ON rp.no_doc = pcvs.no_payment_hutang
WHERE pcvs.status IN ('waiting payment', 'done payment')
  AND rp.no_doc IS NULL;


-- =============================================================================
-- CHECK 2: no_doc in request_payment Matches no_payment_hutang
-- For all request_payment records with tipe='petty_cash_hutang', verify that
-- the no_doc references a valid no_payment_hutang in tr_petty_cash_vuca_sustain.
-- Expected: Empty result (no orphan request_payment records)
-- =============================================================================
SELECT
    rp.id AS rp_id,
    rp.no_doc,
    rp.tipe,
    'ORPHAN_REQUEST_PAYMENT' AS violation_type,
    'request_payment no_doc does not match any no_payment_hutang' AS description
FROM request_payment rp
LEFT JOIN tr_petty_cash_vuca_sustain pcvs ON pcvs.no_payment_hutang = rp.no_doc
WHERE rp.tipe = 'petty_cash_hutang'
  AND pcvs.id IS NULL;


-- =============================================================================
-- CHECK 3: tipe Field is Always 'petty_cash_hutang'
-- For all request_payment records linked to tr_petty_cash_vuca_sustain,
-- verify tipe = 'petty_cash_hutang'.
-- Expected: Empty result (no incorrect tipe values)
-- =============================================================================
SELECT
    rp.id AS rp_id,
    rp.no_doc,
    rp.tipe,
    pcvs.no_payment_hutang,
    'INCORRECT_TIPE' AS violation_type,
    CONCAT('Expected tipe=petty_cash_hutang but found: ', IFNULL(rp.tipe, 'NULL')) AS description
FROM request_payment rp
INNER JOIN tr_petty_cash_vuca_sustain pcvs ON pcvs.no_payment_hutang = rp.no_doc
WHERE rp.tipe = 'petty_cash_hutang'
  AND rp.tipe <> 'petty_cash_hutang';
-- NOTE: This join already filters by tipe, so we also check the inverse:

SELECT
    rp.id AS rp_id,
    rp.no_doc,
    rp.tipe,
    'WRONG_TIPE_FOR_PAYMENT_HUTANG' AS violation_type,
    CONCAT('request_payment for Payment Hutang has tipe=', IFNULL(rp.tipe, 'NULL'), ' instead of petty_cash_hutang') AS description
FROM request_payment rp
INNER JOIN tr_petty_cash_vuca_sustain pcvs ON pcvs.no_payment_hutang = rp.no_doc
WHERE rp.tipe <> 'petty_cash_hutang';


-- =============================================================================
-- CHECK 4: keperluan Contains Correct Prefix "Payment Hutang Petty Cash - "
-- For all request_payment records with tipe='petty_cash_hutang', verify that
-- keperluan follows the format: "Payment Hutang Petty Cash - PHP-YYYY-NNNN"
-- Expected: Empty result (all keperluan values have correct format)
-- =============================================================================
SELECT
    rp.id AS rp_id,
    rp.no_doc,
    rp.keperluan,
    'INCORRECT_KEPERLUAN_PREFIX' AS violation_type,
    CONCAT('keperluan does not start with "Payment Hutang Petty Cash - ": ', IFNULL(rp.keperluan, 'NULL')) AS description
FROM request_payment rp
WHERE rp.tipe = 'petty_cash_hutang'
  AND (rp.keperluan IS NULL OR rp.keperluan NOT LIKE 'Payment Hutang Petty Cash - %');


-- =============================================================================
-- CHECK 5: keperluan Contains the Correct no_payment_hutang (PHP-YYYY-NNNN)
-- Verify that keperluan = "Payment Hutang Petty Cash - " + no_doc exactly
-- Expected: Empty result (keperluan always matches "prefix + no_doc")
-- =============================================================================
SELECT
    rp.id AS rp_id,
    rp.no_doc,
    rp.keperluan,
    CONCAT('Payment Hutang Petty Cash - ', rp.no_doc) AS expected_keperluan,
    'KEPERLUAN_MISMATCH' AS violation_type,
    'keperluan does not equal "Payment Hutang Petty Cash - " + no_doc' AS description
FROM request_payment rp
WHERE rp.tipe = 'petty_cash_hutang'
  AND rp.keperluan <> CONCAT('Payment Hutang Petty Cash - ', rp.no_doc);


-- =============================================================================
-- CHECK 6: jumlah Matches grand_total from tr_petty_cash_vuca_sustain
-- For all linked records, verify request_payment.jumlah = pcvs.grand_total
-- Expected: Empty result (amounts always match)
-- =============================================================================
SELECT
    rp.id AS rp_id,
    rp.no_doc,
    rp.jumlah AS rp_jumlah,
    pcvs.grand_total AS pcvs_grand_total,
    'JUMLAH_MISMATCH' AS violation_type,
    CONCAT('request_payment.jumlah=', IFNULL(rp.jumlah, 'NULL'),
           ' vs pcvs.grand_total=', IFNULL(pcvs.grand_total, 'NULL')) AS description
FROM request_payment rp
INNER JOIN tr_petty_cash_vuca_sustain pcvs ON pcvs.no_payment_hutang = rp.no_doc
WHERE rp.tipe = 'petty_cash_hutang'
  AND rp.jumlah <> pcvs.grand_total;


-- =============================================================================
-- CHECK 7: request_payment.status is 0 at Creation
-- For all request_payment records with tipe='petty_cash_hutang' that are still
-- in initial state (not yet processed by pembayaran_material), status should be 0.
-- Note: status may change later as payment is processed, so we check records
-- where the linked pcvs record is still 'waiting payment' (not yet done).
-- Expected: Empty result (all unprocessed request_payment have status=0)
-- =============================================================================
SELECT
    rp.id AS rp_id,
    rp.no_doc,
    rp.status AS rp_status,
    pcvs.status AS pcvs_status,
    'INCORRECT_INITIAL_STATUS' AS violation_type,
    CONCAT('request_payment.status=', IFNULL(rp.status, 'NULL'),
           ' expected 0 for unprocessed payment') AS description
FROM request_payment rp
INNER JOIN tr_petty_cash_vuca_sustain pcvs ON pcvs.no_payment_hutang = rp.no_doc
WHERE rp.tipe = 'petty_cash_hutang'
  AND pcvs.status = 'waiting payment'
  AND rp.status <> 0;


-- =============================================================================
-- CHECK 8: Processed Records Have Status 'waiting payment' or 'done payment'
-- Verify that any record with a corresponding request_payment entry is NOT
-- still in 'draft' status (the status should have been updated).
-- Expected: Empty result (no draft records with existing request_payment)
-- =============================================================================
SELECT
    pcvs.id,
    pcvs.no_payment_hutang,
    pcvs.status,
    rp.id AS rp_id,
    'DRAFT_WITH_REQUEST_PAYMENT' AS violation_type,
    'Record is still draft but has a request_payment entry (status not updated)' AS description
FROM tr_petty_cash_vuca_sustain pcvs
INNER JOIN request_payment rp ON rp.no_doc = pcvs.no_payment_hutang
WHERE rp.tipe = 'petty_cash_hutang'
  AND pcvs.status = 'draft';


-- =============================================================================
-- CHECK 9: no_doc Format Validation in request_payment
-- For all request_payment records with tipe='petty_cash_hutang', verify that
-- no_doc follows the PHP-YYYY-NNNN format.
-- Expected: Empty result (all no_doc values have valid format)
-- =============================================================================
SELECT
    rp.id AS rp_id,
    rp.no_doc,
    'INVALID_NO_DOC_FORMAT' AS violation_type,
    CONCAT('no_doc does not match PHP-YYYY-NNNN format: ', IFNULL(rp.no_doc, 'NULL')) AS description
FROM request_payment rp
WHERE rp.tipe = 'petty_cash_hutang'
  AND rp.no_doc NOT REGEXP '^PHP-[0-9]{4}-[0-9]{4}$';


-- =============================================================================
-- CHECK 10: No Duplicate request_payment Records per Payment Hutang
-- Each no_payment_hutang should have at most ONE corresponding record in
-- request_payment with tipe='petty_cash_hutang'. Duplicates indicate the
-- Payment Hutang action was incorrectly executed multiple times.
-- Expected: Empty result (no duplicate request_payment entries)
-- =============================================================================
SELECT
    rp.no_doc,
    COUNT(*) AS duplicate_count,
    'DUPLICATE_REQUEST_PAYMENT' AS violation_type,
    CONCAT('Found ', COUNT(*), ' request_payment records for same no_doc') AS description
FROM request_payment rp
WHERE rp.tipe = 'petty_cash_hutang'
GROUP BY rp.no_doc
HAVING COUNT(*) > 1;


-- =============================================================================
-- CHECK 11: Data Consistency Cross-Check
-- For all linked records, verify bidirectional integrity:
-- - pcvs status is NOT 'draft' (since it was processed)
-- - request_payment has valid data (no_doc, tipe, keperluan, jumlah all present)
-- Expected: Empty result (all linked records are internally consistent)
-- =============================================================================
SELECT
    pcvs.id AS pcvs_id,
    pcvs.no_payment_hutang,
    rp.id AS rp_id,
    rp.no_doc,
    rp.tipe,
    rp.keperluan,
    rp.jumlah,
    'DATA_CONSISTENCY_VIOLATION' AS violation_type,
    CASE
        WHEN rp.no_doc IS NULL OR rp.no_doc = '' THEN 'no_doc is empty'
        WHEN rp.tipe IS NULL OR rp.tipe = '' THEN 'tipe is empty'
        WHEN rp.keperluan IS NULL OR rp.keperluan = '' THEN 'keperluan is empty'
        WHEN rp.jumlah IS NULL THEN 'jumlah is NULL'
        WHEN rp.jumlah <= 0 THEN 'jumlah is zero or negative'
        ELSE 'unknown consistency issue'
    END AS description
FROM tr_petty_cash_vuca_sustain pcvs
INNER JOIN request_payment rp ON rp.no_doc = pcvs.no_payment_hutang
WHERE rp.tipe = 'petty_cash_hutang'
  AND (
    rp.no_doc IS NULL OR rp.no_doc = ''
    OR rp.tipe IS NULL OR rp.tipe = ''
    OR rp.keperluan IS NULL OR rp.keperluan = ''
    OR rp.jumlah IS NULL
    OR rp.jumlah <= 0
  );


-- =============================================================================
-- SUMMARY: Combined Violation Count
-- Quick overview of all violations found across checks.
-- Expected: All counts = 0
-- =============================================================================
SELECT 'MISSING_REQUEST_PAYMENT' AS check_name, COUNT(*) AS violations
FROM tr_petty_cash_vuca_sustain pcvs
LEFT JOIN request_payment rp ON rp.no_doc = pcvs.no_payment_hutang
WHERE pcvs.status IN ('waiting payment', 'done payment')
  AND rp.no_doc IS NULL

UNION ALL

SELECT 'ORPHAN_REQUEST_PAYMENT', COUNT(*)
FROM request_payment rp
LEFT JOIN tr_petty_cash_vuca_sustain pcvs ON pcvs.no_payment_hutang = rp.no_doc
WHERE rp.tipe = 'petty_cash_hutang'
  AND pcvs.id IS NULL

UNION ALL

SELECT 'WRONG_TIPE', COUNT(*)
FROM request_payment rp
INNER JOIN tr_petty_cash_vuca_sustain pcvs ON pcvs.no_payment_hutang = rp.no_doc
WHERE rp.tipe <> 'petty_cash_hutang'

UNION ALL

SELECT 'INCORRECT_KEPERLUAN', COUNT(*)
FROM request_payment rp
WHERE rp.tipe = 'petty_cash_hutang'
  AND rp.keperluan <> CONCAT('Payment Hutang Petty Cash - ', rp.no_doc)

UNION ALL

SELECT 'JUMLAH_MISMATCH', COUNT(*)
FROM request_payment rp
INNER JOIN tr_petty_cash_vuca_sustain pcvs ON pcvs.no_payment_hutang = rp.no_doc
WHERE rp.tipe = 'petty_cash_hutang'
  AND rp.jumlah <> pcvs.grand_total

UNION ALL

SELECT 'INCORRECT_INITIAL_STATUS', COUNT(*)
FROM request_payment rp
INNER JOIN tr_petty_cash_vuca_sustain pcvs ON pcvs.no_payment_hutang = rp.no_doc
WHERE rp.tipe = 'petty_cash_hutang'
  AND pcvs.status = 'waiting payment'
  AND rp.status <> 0

UNION ALL

SELECT 'DRAFT_WITH_REQUEST_PAYMENT', COUNT(*)
FROM tr_petty_cash_vuca_sustain pcvs
INNER JOIN request_payment rp ON rp.no_doc = pcvs.no_payment_hutang
WHERE rp.tipe = 'petty_cash_hutang'
  AND pcvs.status = 'draft'

UNION ALL

SELECT 'INVALID_NO_DOC_FORMAT', COUNT(*)
FROM request_payment rp
WHERE rp.tipe = 'petty_cash_hutang'
  AND rp.no_doc NOT REGEXP '^PHP-[0-9]{4}-[0-9]{4}$'

UNION ALL

SELECT 'DUPLICATE_REQUEST_PAYMENT', IFNULL(SUM(dup_count), 0)
FROM (
    SELECT COUNT(*) - 1 AS dup_count
    FROM request_payment rp
    WHERE rp.tipe = 'petty_cash_hutang'
    GROUP BY rp.no_doc
    HAVING COUNT(*) > 1
) duplicates;
