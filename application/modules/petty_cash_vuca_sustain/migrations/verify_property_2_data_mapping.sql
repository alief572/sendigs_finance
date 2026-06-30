-- =============================================================================
-- Property 2: Data Mapping Completeness from Pelaporan
-- =============================================================================
-- Validates: Requirements 1.2, 9.1
--
-- Property Statement:
-- For any valid pelaporan object with company VUCA or SUSTAIN, when a record
-- is created in tr_petty_cash_vuca_sustain, the resulting record SHALL contain:
--   - matching no_pelaporan
--   - matching company
--   - matching periode_start and periode_end
--   - matching grand_total
--   - correct jumlah_pencatatan
--   - valid pelaporan_id reference
--   - status equal to "draft"
--
-- How to interpret results:
--   - Empty result set = ALL GOOD (no mismatches found)
--   - Any rows returned = data mapping violation detected
-- =============================================================================

-- ---------------------------------------------------------------------------
-- CHECK 1: Field mapping correctness
-- JOIN tr_petty_cash_vuca_sustain with tr_pelaporan_petty_cash and verify
-- that mapped fields match between source (pelaporan) and target (vuca_sustain)
-- ---------------------------------------------------------------------------
SELECT
    vs.id AS vuca_sustain_id,
    vs.no_payment_hutang,
    vs.pelaporan_id,
    -- Report which fields have mismatches
    CASE WHEN vs.no_pelaporan != p.no_pelaporan
         THEN CONCAT('MISMATCH: vs=', vs.no_pelaporan, ' vs p=', p.no_pelaporan)
         ELSE 'OK' END AS check_no_pelaporan,
    CASE WHEN vs.company != UPPER(TRIM(p.company))
         THEN CONCAT('MISMATCH: vs=', vs.company, ' vs p=', p.company)
         ELSE 'OK' END AS check_company,
    CASE WHEN vs.periode_start != p.periode_start
         THEN CONCAT('MISMATCH: vs=', vs.periode_start, ' vs p=', p.periode_start)
         ELSE 'OK' END AS check_periode_start,
    CASE WHEN vs.periode_end != p.periode_end
         THEN CONCAT('MISMATCH: vs=', vs.periode_end, ' vs p=', p.periode_end)
         ELSE 'OK' END AS check_periode_end,
    CASE WHEN vs.grand_total != p.grand_total
         THEN CONCAT('MISMATCH: vs=', vs.grand_total, ' vs p=', p.grand_total)
         ELSE 'OK' END AS check_grand_total,
    CASE WHEN vs.jumlah_pencatatan != (
             SELECT COUNT(*) FROM tr_pelaporan_petty_cash_detail d
             WHERE d.pelaporan_id = p.id
         )
         THEN CONCAT('MISMATCH: vs=', vs.jumlah_pencatatan, ' vs actual=',
              (SELECT COUNT(*) FROM tr_pelaporan_petty_cash_detail d WHERE d.pelaporan_id = p.id))
         ELSE 'OK' END AS check_jumlah_pencatatan
FROM tr_petty_cash_vuca_sustain vs
JOIN tr_pelaporan_petty_cash p ON p.id = vs.pelaporan_id
WHERE vs.no_pelaporan != p.no_pelaporan
   OR vs.company != UPPER(TRIM(p.company))
   OR vs.periode_start != p.periode_start
   OR vs.periode_end != p.periode_end
   OR vs.grand_total != p.grand_total
   OR vs.jumlah_pencatatan != (
       SELECT COUNT(*) FROM tr_pelaporan_petty_cash_detail d
       WHERE d.pelaporan_id = p.id
   );

-- ---------------------------------------------------------------------------
-- CHECK 2: Invalid pelaporan_id references
-- Verify that every pelaporan_id in tr_petty_cash_vuca_sustain points to
-- an existing record in tr_pelaporan_petty_cash
-- ---------------------------------------------------------------------------
SELECT
    vs.id AS vuca_sustain_id,
    vs.no_payment_hutang,
    vs.pelaporan_id,
    'INVALID REFERENCE: pelaporan_id does not exist in tr_pelaporan_petty_cash' AS issue
FROM tr_petty_cash_vuca_sustain vs
LEFT JOIN tr_pelaporan_petty_cash p ON p.id = vs.pelaporan_id
WHERE p.id IS NULL;

-- ---------------------------------------------------------------------------
-- CHECK 3: Initial status must be "draft"
-- Records that have never been modified (created_on = modified_on OR
-- modified_on IS NULL) should always have status = 'draft'.
-- This validates Requirement 9.1: status awal selalu "draft".
-- ---------------------------------------------------------------------------
SELECT
    vs.id AS vuca_sustain_id,
    vs.no_payment_hutang,
    vs.status,
    vs.created_on,
    vs.modified_on,
    'VIOLATION: Newly created record does not have status=draft' AS issue
FROM tr_petty_cash_vuca_sustain vs
WHERE (vs.modified_on IS NULL OR vs.modified_on = vs.created_on)
  AND vs.status != 'draft';
