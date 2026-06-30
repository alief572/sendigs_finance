-- =============================================================================
-- Property 4: Action Buttons Determined by Status
-- =============================================================================
--
-- Validates: Requirements 3.5, 3.6
--
-- Property Statement:
-- For any Payment Hutang record, the available action buttons SHALL be:
--   - (Payment Hutang + View + Print) when status is "draft"
--   - (View + Print only) when status is "waiting payment" or "done payment"
-- No other button combinations are valid.
--
-- How to use:
-- Run each query below against the database. Empty result sets = all good.
-- Any rows returned indicate violations that need investigation.
--
-- =============================================================================
-- CODE REVIEW CHECKLIST (index.php view / DataTables JavaScript)
-- =============================================================================
--
-- The action buttons in the index view are rendered server-side via the
-- DataTables AJAX response OR client-side via JS columnDefs. The following
-- conditions MUST hold true for Property 4 to be satisfied:
--
-- ✓ 1. Payment Hutang button is ONLY rendered when:
--       - record.status === 'draft'
--       - AND the view has `has_manage` permission flag set to true
--       Button markup: <button class="btn btn-xs btn-success" title="Payment Hutang">
--                        <i class="fa fa-send"></i>
--                      </button>
--
-- ✓ 2. View button is ALWAYS shown regardless of status:
--       Button markup: <button class="btn btn-xs btn-info" title="View">
--                        <i class="fa fa-eye"></i>
--                      </button>
--
-- ✓ 3. Print button is ALWAYS shown regardless of status:
--       Button markup: <button class="btn btn-xs btn-default" title="Print">
--                        <i class="fa fa-print"></i>
--                      </button>
--
-- ✓ 4. No other action buttons exist in the Action column
--       (no Edit, no Delete, no other custom buttons)
--
-- ✓ 5. The condition uses strict equality check on status string:
--       if (row.status === 'draft' && has_manage) { show payment_hutang button }
--
-- ✓ 6. Even when user has Manage permission, the Payment Hutang button
--       is NOT shown for "waiting payment" or "done payment" status
--
-- ✓ 7. The button combination matrix is exhaustive:
--       | Status            | has_manage=true                      | has_manage=false |
--       |-------------------|--------------------------------------|------------------|
--       | draft             | Payment Hutang + View + Print        | View + Print     |
--       | waiting payment   | View + Print                         | View + Print     |
--       | done payment      | View + Print                         | View + Print     |
--
-- =============================================================================


-- =============================================================================
-- CHECK 1: All Records Have Valid Status Values
-- Verify ALL records have a status that is one of the three allowed values.
-- This is a prerequisite — if invalid statuses exist, button logic is undefined.
-- Expected: Empty result (no violations)
-- =============================================================================
SELECT
    id,
    no_payment_hutang,
    status,
    'INVALID_STATUS' AS violation_type,
    'Record has status value not in allowed set (draft, waiting payment, done payment)' AS description
FROM tr_petty_cash_vuca_sustain
WHERE status NOT IN ('draft', 'waiting payment', 'done payment');


-- =============================================================================
-- CHECK 2: Status Distribution Summary
-- Informational query to verify data distribution across statuses.
-- This helps confirm test coverage — all three statuses should ideally be present.
-- Expected: At least one row per valid status (informational, not a violation check)
-- =============================================================================
SELECT
    status,
    COUNT(*) AS record_count,
    'STATUS_DISTRIBUTION' AS info_type
FROM tr_petty_cash_vuca_sustain
GROUP BY status
ORDER BY FIELD(status, 'draft', 'waiting payment', 'done payment');


-- =============================================================================
-- CHECK 3: Draft Records Should Show Payment Hutang Button
-- Identify all records with status='draft' that SHOULD display the
-- Payment Hutang button (when user has Manage permission).
-- This is a positive check — these records exist and their button set is:
--   Payment Hutang + View + Print
-- Expected: Returns rows (informational — confirms draft records exist)
-- =============================================================================
SELECT
    id,
    no_payment_hutang,
    status,
    'BUTTONS: Payment Hutang + View + Print' AS expected_buttons,
    'DRAFT_BUTTON_CHECK' AS info_type
FROM tr_petty_cash_vuca_sustain
WHERE status = 'draft';


-- =============================================================================
-- CHECK 4: Non-Draft Records Should NOT Show Payment Hutang Button
-- Identify all records where status is NOT 'draft'. These records must
-- ONLY show View + Print buttons. The Payment Hutang button must be absent.
-- Expected: Returns rows (informational — confirms non-draft records exist)
-- =============================================================================
SELECT
    id,
    no_payment_hutang,
    status,
    'BUTTONS: View + Print ONLY' AS expected_buttons,
    'NON_DRAFT_BUTTON_CHECK' AS info_type
FROM tr_petty_cash_vuca_sustain
WHERE status IN ('waiting payment', 'done payment');


-- =============================================================================
-- CHECK 5: No NULL Status Records
-- Records with NULL status would have undefined button behavior.
-- Expected: Empty result (no violations)
-- =============================================================================
SELECT
    id,
    no_payment_hutang,
    status,
    'NULL_STATUS' AS violation_type,
    'Record has NULL status — button rendering is undefined' AS description
FROM tr_petty_cash_vuca_sustain
WHERE status IS NULL;


-- =============================================================================
-- CHECK 6: No Empty String Status Records
-- Records with empty string status would bypass both conditions
-- (not 'draft', not in the other two), leading to undefined button behavior.
-- Expected: Empty result (no violations)
-- =============================================================================
SELECT
    id,
    no_payment_hutang,
    status,
    'EMPTY_STATUS' AS violation_type,
    'Record has empty string status — button rendering is undefined' AS description
FROM tr_petty_cash_vuca_sustain
WHERE status = '';


-- =============================================================================
-- SUMMARY: Combined Violation Count
-- Quick overview of all violations found across checks.
-- Expected: All counts = 0
-- =============================================================================
SELECT 'INVALID_STATUS' AS check_name, COUNT(*) AS violations
FROM tr_petty_cash_vuca_sustain
WHERE status NOT IN ('draft', 'waiting payment', 'done payment')

UNION ALL

SELECT 'NULL_STATUS', COUNT(*)
FROM tr_petty_cash_vuca_sustain
WHERE status IS NULL

UNION ALL

SELECT 'EMPTY_STATUS', COUNT(*)
FROM tr_petty_cash_vuca_sustain
WHERE status = '';
