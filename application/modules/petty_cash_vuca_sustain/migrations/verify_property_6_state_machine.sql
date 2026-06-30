-- =============================================================================
-- Property 6: State Machine Validity
-- =============================================================================
--
-- Validates: Requirements 4.5, 5.2, 5.3, 9.2, 9.3, 9.4
--
-- Property Statement:
-- For any Payment Hutang record with current status S and any attempted
-- status transition to target status T, the transition SHALL succeed only if:
--   (S="draft" AND T="waiting payment") OR (S="waiting payment" AND T="done payment")
-- All other transition combinations SHALL be rejected.
--
-- Valid transitions:
--   draft → waiting payment         (via Payment Hutang action / process_payment_hutang)
--   waiting payment → done payment  (via pembayaran_material / update_status_done)
--
-- Invalid transitions (all must be rejected):
--   draft → done payment            (INVALID - cannot skip waiting payment)
--   waiting payment → draft         (INVALID - no rollback allowed)
--   done payment → draft            (INVALID - terminal state, no reverse)
--   done payment → waiting payment  (INVALID - terminal state, no reverse)
--
-- How to use:
-- Run each query below against the database. Empty result sets = all good.
-- Any rows returned indicate violations that need investigation.
--
-- =============================================================================
-- CODE REVIEW CHECKLIST (State Machine Enforcement)
-- =============================================================================
--
-- The state machine is enforced at the MODEL level via two methods:
--
-- ✓ 1. process_payment_hutang($id, $user_id) — transition: draft → waiting payment
--       ENFORCEMENT:
--       - Uses SELECT ... WHERE id = ? AND status = 'draft' FOR UPDATE
--       - If status is NOT 'draft', query returns 0 rows → method returns false
--       - Only on success: UPDATE status = 'waiting payment'
--       - Wrapped in transaction (trans_begin / trans_commit / trans_rollback)
--       CONCLUSION: Only accepts status='draft', rejects all other states
--
-- ✓ 2. update_status_done($no_doc, $user_id) — transition: waiting payment → done payment
--       ENFORCEMENT:
--       - Loads record by no_payment_hutang (or id as fallback)
--       - Explicit check: if ($record->status !== self::STATUS_WAITING_PAYMENT) return false
--       - Only on success: UPDATE status = 'done payment'
--       - Logs warning on invalid transition attempt
--       CONCLUSION: Only accepts status='waiting payment', rejects all other states
--
-- ✓ 3. No other methods in the model change the status field:
--       - generate_no_payment_hutang() → only generates numbers, no status change
--       - get_server_side_data() → read-only query, no UPDATE
--       - get_payment_hutang() → read-only query, no UPDATE
--       - _format_periode() → string formatting helper, no DB access
--       STATUS CHANGE METHODS: ONLY process_payment_hutang and update_status_done
--
-- ✓ 4. No direct SQL UPDATE of status is exposed to controllers:
--       - Controller payment_hutang($id) → calls model process_payment_hutang()
--       - No controller method directly sets status via $this->db->update()
--       - No raw SQL query in controller that modifies status
--       - The model is the single point of status transition enforcement
--
-- ✓ 5. BF_Model parent class operations:
--       - The model extends BF_Model which has generic update() / insert()
--       - However, NO controller method calls generic $this->model->update()
--         with a status field change — all status changes go through the
--         dedicated methods above
--       - No delete operation exists (requirement 9.5: no edit/delete feature)
--
-- ✓ 6. External module (pembayaran_material) integration:
--       - pembayaran_material calls update_status_done() from the model
--       - This is the ONLY path for waiting payment → done payment transition
--       - The model validates current status before allowing the update
--
-- ✓ 7. Record creation always sets status = 'draft':
--       - send_to_petty_cash_vuca_sustain() in expense_petty_cash always
--         inserts with status = 'draft' (hardcoded in insert data array)
--       - No other code path creates records in tr_petty_cash_vuca_sustain
--
-- =============================================================================


-- =============================================================================
-- CHECK 1: All Status Values Are In Valid Set
-- Verify no record has a status value outside the three allowed states.
-- A status outside the set would indicate a state machine violation.
-- Expected: Empty result (no violations)
-- =============================================================================
SELECT
    id,
    no_payment_hutang,
    status,
    created_on,
    modified_on,
    'INVALID_STATUS_VALUE' AS violation_type,
    'Record has status not in valid set (draft, waiting payment, done payment)' AS description
FROM tr_petty_cash_vuca_sustain
WHERE status NOT IN ('draft', 'waiting payment', 'done payment');


-- =============================================================================
-- CHECK 2: No Records Jumped from Draft Directly to Done Payment
-- If a record has status 'done payment', it MUST have gone through
-- 'waiting payment' first. Evidence: a corresponding record in request_payment
-- with matching no_doc (created during draft → waiting payment transition).
-- A 'done payment' record WITHOUT a request_payment entry means it skipped
-- the 'waiting payment' state — a state machine violation.
-- Expected: Empty result (no violations)
-- =============================================================================
SELECT
    pv.id,
    pv.no_payment_hutang,
    pv.status,
    pv.created_on,
    pv.modified_on,
    'SKIPPED_WAITING_PAYMENT' AS violation_type,
    'Record is done payment but has no request_payment record (skipped waiting payment state)' AS description
FROM tr_petty_cash_vuca_sustain pv
LEFT JOIN request_payment rp ON rp.no_doc = pv.no_payment_hutang
    AND rp.tipe = 'petty_cash_hutang'
WHERE pv.status = 'done payment'
  AND rp.no_doc IS NULL;


-- =============================================================================
-- CHECK 3: Waiting Payment Records Must Have Request Payment Entry
-- If a record is in 'waiting payment' status, it MUST have been processed
-- through process_payment_hutang() which inserts into request_payment.
-- A 'waiting payment' record without a request_payment entry is suspicious.
-- Expected: Empty result (no violations)
-- =============================================================================
SELECT
    pv.id,
    pv.no_payment_hutang,
    pv.status,
    pv.created_on,
    pv.modified_on,
    'WAITING_WITHOUT_REQUEST_PAYMENT' AS violation_type,
    'Record is waiting payment but has no corresponding request_payment record' AS description
FROM tr_petty_cash_vuca_sustain pv
LEFT JOIN request_payment rp ON rp.no_doc = pv.no_payment_hutang
    AND rp.tipe = 'petty_cash_hutang'
WHERE pv.status = 'waiting payment'
  AND rp.no_doc IS NULL;


-- =============================================================================
-- CHECK 4: Draft Records Should NOT Have Request Payment Entry
-- If a record is still in 'draft' status, there should be NO request_payment
-- record for it. The presence of a request_payment record for a draft record
-- would indicate a failed/inconsistent transition (the INSERT happened but
-- the status UPDATE was rolled back somehow).
-- Expected: Empty result (no violations)
-- =============================================================================
SELECT
    pv.id,
    pv.no_payment_hutang,
    pv.status,
    rp.no_doc AS rp_no_doc,
    rp.created_on AS rp_created_on,
    'DRAFT_WITH_REQUEST_PAYMENT' AS violation_type,
    'Record is still draft but has a request_payment record (inconsistent state)' AS description
FROM tr_petty_cash_vuca_sustain pv
INNER JOIN request_payment rp ON rp.no_doc = pv.no_payment_hutang
    AND rp.tipe = 'petty_cash_hutang'
WHERE pv.status = 'draft';


-- =============================================================================
-- CHECK 5: Modified Timestamp Ordering Consistency
-- For records that have been modified (status changed from draft):
-- - 'waiting payment' records MUST have modified_on set (transition happened)
-- - 'done payment' records MUST have modified_on set (transition happened)
-- - 'draft' records with modified_on set might indicate a rejected transition
--   attempt that somehow corrupted the timestamp (suspicious but not definitive)
-- Expected: Empty result (no violations)
-- =============================================================================
SELECT
    id,
    no_payment_hutang,
    status,
    created_on,
    modified_on,
    'MISSING_MODIFIED_TIMESTAMP' AS violation_type,
    'Record status is not draft but modified_on is NULL (transition should set modified_on)' AS description
FROM tr_petty_cash_vuca_sustain
WHERE status IN ('waiting payment', 'done payment')
  AND modified_on IS NULL;


-- =============================================================================
-- CHECK 6: No Reverse Transitions (done payment back to earlier states)
-- A record with status 'done payment' is in a terminal state. If its
-- modified_on is EARLIER than its created_on, that's a temporal anomaly
-- suggesting data manipulation.
-- Expected: Empty result (no violations)
-- =============================================================================
SELECT
    id,
    no_payment_hutang,
    status,
    created_on,
    modified_on,
    'TEMPORAL_ANOMALY' AS violation_type,
    'Record modified_on is earlier than created_on (possible reverse transition or data manipulation)' AS description
FROM tr_petty_cash_vuca_sustain
WHERE modified_on IS NOT NULL
  AND modified_on < created_on;


-- =============================================================================
-- CHECK 7: Request Payment Created After Record Creation
-- The request_payment record should be created AFTER the tr_petty_cash_vuca_sustain
-- record (since it's triggered by user action after record already exists in draft).
-- If rp.created_on < pv.created_on, something is wrong with the ordering.
-- Expected: Empty result (no violations)
-- =============================================================================
SELECT
    pv.id,
    pv.no_payment_hutang,
    pv.status,
    pv.created_on AS pcvs_created_on,
    rp.created_on AS rp_created_on,
    'REQUEST_PAYMENT_TEMPORAL_VIOLATION' AS violation_type,
    'request_payment was created before the petty_cash_vuca_sustain record' AS description
FROM tr_petty_cash_vuca_sustain pv
INNER JOIN request_payment rp ON rp.no_doc = pv.no_payment_hutang
    AND rp.tipe = 'petty_cash_hutang'
WHERE rp.created_on < pv.created_on;


-- =============================================================================
-- SUMMARY: Combined Violation Count
-- Quick overview of all violations found across checks.
-- Expected: All counts = 0
-- =============================================================================
SELECT 'INVALID_STATUS_VALUE' AS check_name, COUNT(*) AS violations
FROM tr_petty_cash_vuca_sustain
WHERE status NOT IN ('draft', 'waiting payment', 'done payment')

UNION ALL

SELECT 'SKIPPED_WAITING_PAYMENT', COUNT(*)
FROM tr_petty_cash_vuca_sustain pv
LEFT JOIN request_payment rp ON rp.no_doc = pv.no_payment_hutang
    AND rp.tipe = 'petty_cash_hutang'
WHERE pv.status = 'done payment'
  AND rp.no_doc IS NULL

UNION ALL

SELECT 'WAITING_WITHOUT_REQUEST_PAYMENT', COUNT(*)
FROM tr_petty_cash_vuca_sustain pv
LEFT JOIN request_payment rp ON rp.no_doc = pv.no_payment_hutang
    AND rp.tipe = 'petty_cash_hutang'
WHERE pv.status = 'waiting payment'
  AND rp.no_doc IS NULL

UNION ALL

SELECT 'DRAFT_WITH_REQUEST_PAYMENT', COUNT(*)
FROM tr_petty_cash_vuca_sustain pv
INNER JOIN request_payment rp ON rp.no_doc = pv.no_payment_hutang
    AND rp.tipe = 'petty_cash_hutang'
WHERE pv.status = 'draft'

UNION ALL

SELECT 'MISSING_MODIFIED_TIMESTAMP', COUNT(*)
FROM tr_petty_cash_vuca_sustain
WHERE status IN ('waiting payment', 'done payment')
  AND modified_on IS NULL

UNION ALL

SELECT 'TEMPORAL_ANOMALY', COUNT(*)
FROM tr_petty_cash_vuca_sustain
WHERE modified_on IS NOT NULL
  AND modified_on < created_on

UNION ALL

SELECT 'REQUEST_PAYMENT_TEMPORAL_VIOLATION', COUNT(*)
FROM tr_petty_cash_vuca_sustain pv
INNER JOIN request_payment rp ON rp.no_doc = pv.no_payment_hutang
    AND rp.tipe = 'petty_cash_hutang'
WHERE rp.created_on < pv.created_on;
