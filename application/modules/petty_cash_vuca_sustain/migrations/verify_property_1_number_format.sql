-- =============================================================================
-- Property 1: Number Generation Format and Sequencing
-- =============================================================================
-- 
-- Validates: Requirements 1.3, 2.1, 2.2
--
-- Property Statement:
-- For any valid year (2020–2099) and any sequence of number generation calls
-- within that year, the generated No_Payment_Hutang SHALL always match the
-- format PHP-{YYYY}-{NNNN} where:
--   - YYYY equals the input year
--   - NNNN is a zero-padded 4-digit number (0001-9999)
--   - NNNN is exactly 1 greater than the previous number for that year
--   - NNNN starts at 0001 for a year with no existing records
--
-- How to use:
-- Run each query below against the database. Empty result sets = all good.
-- Any rows returned indicate violations that need investigation.
--
-- Code Review Notes (generate_no_payment_hutang method):
-- ✓ Uses SELECT ... FOR UPDATE to prevent race conditions on concurrent access
-- ✓ Extracts last 4 chars as numeric part and increments by 1
-- ✓ Starts at 1 (→ 0001) when no existing records for the year
-- ✓ Returns false and logs error if next_number > 9999
-- ✓ Uses str_pad(4, '0', STR_PAD_LEFT) for zero-padding
-- ✓ Year derived from Asia/Bangkok timezone when not explicitly provided
-- ✓ Format constructed as 'PHP-' + YYYY + '-' + NNNN
-- =============================================================================


-- =============================================================================
-- CHECK 1: Format Validation
-- Verify ALL records match the regex pattern PHP-{YYYY}-{NNNN}
-- Expected: Empty result (no violations)
-- =============================================================================
SELECT 
    id,
    no_payment_hutang,
    'FORMAT_VIOLATION' AS violation_type,
    'no_payment_hutang does not match PHP-YYYY-NNNN pattern' AS description
FROM tr_petty_cash_vuca_sustain
WHERE no_payment_hutang NOT REGEXP '^PHP-[0-9]{4}-[0-9]{4}$';


-- =============================================================================
-- CHECK 2: Year Validity
-- Verify the year portion is within valid range (2020-2099)
-- Expected: Empty result (no violations)
-- =============================================================================
SELECT 
    id,
    no_payment_hutang,
    CAST(SUBSTRING(no_payment_hutang, 5, 4) AS UNSIGNED) AS extracted_year,
    'YEAR_OUT_OF_RANGE' AS violation_type,
    'Year portion is outside valid range 2020-2099' AS description
FROM tr_petty_cash_vuca_sustain
WHERE no_payment_hutang REGEXP '^PHP-[0-9]{4}-[0-9]{4}$'
  AND (
    CAST(SUBSTRING(no_payment_hutang, 5, 4) AS UNSIGNED) < 2020
    OR CAST(SUBSTRING(no_payment_hutang, 5, 4) AS UNSIGNED) > 2099
  );


-- =============================================================================
-- CHECK 3: Sequence Number Validity
-- Verify the sequence portion (NNNN) is between 0001 and 9999
-- Expected: Empty result (no violations)
-- =============================================================================
SELECT 
    id,
    no_payment_hutang,
    CAST(SUBSTRING(no_payment_hutang, 10, 4) AS UNSIGNED) AS extracted_seq,
    'SEQUENCE_OUT_OF_RANGE' AS violation_type,
    'Sequence number is outside valid range 0001-9999' AS description
FROM tr_petty_cash_vuca_sustain
WHERE no_payment_hutang REGEXP '^PHP-[0-9]{4}-[0-9]{4}$'
  AND (
    CAST(SUBSTRING(no_payment_hutang, 10, 4) AS UNSIGNED) < 1
    OR CAST(SUBSTRING(no_payment_hutang, 10, 4) AS UNSIGNED) > 9999
  );


-- =============================================================================
-- CHECK 4: No Duplicate Numbers
-- Verify no two records have the same no_payment_hutang
-- Expected: Empty result (no duplicates)
-- =============================================================================
SELECT 
    no_payment_hutang,
    COUNT(*) AS duplicate_count,
    'DUPLICATE_NUMBER' AS violation_type,
    'Same no_payment_hutang assigned to multiple records' AS description
FROM tr_petty_cash_vuca_sustain
GROUP BY no_payment_hutang
HAVING COUNT(*) > 1;


-- =============================================================================
-- CHECK 5: Sequential Ordering Within Each Year (No Gaps)
-- Verify that sequence numbers are contiguous (1, 2, 3, ...) within each year.
-- A gap indicates a missing number in the sequence.
-- Expected: Empty result (no gaps)
-- =============================================================================
SELECT 
    t1.no_payment_hutang AS current_number,
    t1.seq_num AS current_seq,
    t1.seq_num - 1 AS expected_previous_seq,
    'SEQUENCE_GAP' AS violation_type,
    CONCAT('Gap detected: expected sequence ', t1.seq_num - 1, ' before ', t1.seq_num) AS description
FROM (
    SELECT 
        no_payment_hutang,
        SUBSTRING(no_payment_hutang, 5, 4) AS year_part,
        CAST(SUBSTRING(no_payment_hutang, 10, 4) AS UNSIGNED) AS seq_num
    FROM tr_petty_cash_vuca_sustain
    WHERE no_payment_hutang REGEXP '^PHP-[0-9]{4}-[0-9]{4}$'
) t1
WHERE t1.seq_num > 1
  AND NOT EXISTS (
    SELECT 1 
    FROM tr_petty_cash_vuca_sustain t2
    WHERE t2.no_payment_hutang = CONCAT('PHP-', t1.year_part, '-', LPAD(t1.seq_num - 1, 4, '0'))
  );


-- =============================================================================
-- CHECK 6: First Number in Each Year Starts at 0001
-- Verify the minimum sequence number per year is always 0001
-- Expected: Empty result (each year starts at 0001)
-- =============================================================================
SELECT 
    year_part,
    min_seq,
    'YEAR_NOT_STARTING_AT_0001' AS violation_type,
    CONCAT('Year ', year_part, ' starts at ', LPAD(min_seq, 4, '0'), ' instead of 0001') AS description
FROM (
    SELECT 
        SUBSTRING(no_payment_hutang, 5, 4) AS year_part,
        MIN(CAST(SUBSTRING(no_payment_hutang, 10, 4) AS UNSIGNED)) AS min_seq
    FROM tr_petty_cash_vuca_sustain
    WHERE no_payment_hutang REGEXP '^PHP-[0-9]{4}-[0-9]{4}$'
    GROUP BY SUBSTRING(no_payment_hutang, 5, 4)
) yearly_min
WHERE min_seq != 1;


-- =============================================================================
-- SUMMARY: Combined Violation Count
-- Quick overview of all violations found across all checks
-- Expected: All counts = 0
-- =============================================================================
SELECT 'FORMAT_VIOLATION' AS check_name, COUNT(*) AS violations
FROM tr_petty_cash_vuca_sustain
WHERE no_payment_hutang NOT REGEXP '^PHP-[0-9]{4}-[0-9]{4}$'

UNION ALL

SELECT 'DUPLICATE_NUMBER', COUNT(*) - COUNT(DISTINCT no_payment_hutang)
FROM tr_petty_cash_vuca_sustain

UNION ALL

SELECT 'SEQUENCE_GAPS', COUNT(*)
FROM (
    SELECT 
        no_payment_hutang,
        SUBSTRING(no_payment_hutang, 5, 4) AS year_part,
        CAST(SUBSTRING(no_payment_hutang, 10, 4) AS UNSIGNED) AS seq_num
    FROM tr_petty_cash_vuca_sustain
    WHERE no_payment_hutang REGEXP '^PHP-[0-9]{4}-[0-9]{4}$'
) t1
WHERE t1.seq_num > 1
  AND NOT EXISTS (
    SELECT 1 
    FROM tr_petty_cash_vuca_sustain t2
    WHERE t2.no_payment_hutang = CONCAT('PHP-', t1.year_part, '-', LPAD(t1.seq_num - 1, 4, '0'))
  )

UNION ALL

SELECT 'YEAR_START_NOT_0001', COUNT(*)
FROM (
    SELECT 
        SUBSTRING(no_payment_hutang, 5, 4) AS year_part,
        MIN(CAST(SUBSTRING(no_payment_hutang, 10, 4) AS UNSIGNED)) AS min_seq
    FROM tr_petty_cash_vuca_sustain
    WHERE no_payment_hutang REGEXP '^PHP-[0-9]{4}-[0-9]{4}$'
    GROUP BY SUBSTRING(no_payment_hutang, 5, 4)
) yearly_min
WHERE min_seq != 1;
