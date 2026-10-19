-- Run on the intended LOCAL application database only, after backup.
-- Preview candidates before applying this file. Re-running is safe.
-- Paid evidence requires payment_approve.status=2 and a tr_payment_paid header.
-- When there are duplicates, newest header timestamp, header ID, then approval ID wins.
-- PREVIEW
SELECT pc.id, pc.no_pelaporan, pc.no_payment_hutang, pc.company, pc.status
FROM tr_petty_cash_vuca_sustain pc
WHERE pc.status = 'waiting payment'
  AND EXISTS (
      SELECT 1 FROM payment_approve pa
      INNER JOIN tr_payment_paid pp ON pp.id = pa.id_payment
      WHERE pc.no_payment_hutang = CONVERT(pa.no_doc USING utf8mb4) COLLATE utf8mb4_general_ci AND pa.status = 2
  )
ORDER BY pc.id;

-- APPLY
START TRANSACTION;
UPDATE tr_petty_cash_vuca_sustain pc
SET pc.status = 'done payment',
    pc.modified_on = (
        SELECT COALESCE(pp.created_on, CAST(pa.tgl_bayar AS DATETIME), pc.modified_on)
        FROM payment_approve pa
        INNER JOIN tr_payment_paid pp ON pp.id = pa.id_payment
        WHERE pc.no_payment_hutang = CONVERT(pa.no_doc USING utf8mb4) COLLATE utf8mb4_general_ci AND pa.status = 2
        ORDER BY pp.created_on DESC, pp.id DESC, pa.id DESC
        LIMIT 1
    ),
    pc.modified_by = (
        SELECT COALESCE(pp.created_by, pc.modified_by)
        FROM payment_approve pa
        INNER JOIN tr_payment_paid pp ON pp.id = pa.id_payment
        WHERE pc.no_payment_hutang = CONVERT(pa.no_doc USING utf8mb4) COLLATE utf8mb4_general_ci AND pa.status = 2
        ORDER BY pp.created_on DESC, pp.id DESC, pa.id DESC
        LIMIT 1
    )
WHERE pc.status = 'waiting payment'
  AND EXISTS (
      SELECT 1 FROM payment_approve pa
      INNER JOIN tr_payment_paid pp ON pp.id = pa.id_payment
      WHERE pc.no_payment_hutang = CONVERT(pa.no_doc USING utf8mb4) COLLATE utf8mb4_general_ci AND pa.status = 2
  );
SELECT ROW_COUNT() AS reports_corrected;
COMMIT;
