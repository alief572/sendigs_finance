-- Read-only diagnostics. Review results before migrating or enabling deposit use.
-- A kwitansi marked paid must have exactly one fully settled DEP invoice.
SELECT k.id, k.no_kwitansi, k.id_customer, k.id_company, k.status,
       COUNT(i.id) AS invoice_count, MAX(i.saldo_piutang) AS saldo_piutang
FROM tr_kwitansi_deposit k
LEFT JOIN tr_invoicing i ON i.no_invoice = k.no_kwitansi AND i.tax_treatment = 'DEP'
GROUP BY k.id, k.no_kwitansi, k.id_customer, k.id_company, k.status
HAVING invoice_count <> 1 OR (k.status = 'LUNAS' AND (saldo_piutang IS NULL OR saldo_piutang <> 0));

-- Pending kwitansi must not contribute to an active deposit balance.
SELECT d.id, d.no_deposit, d.no_referensi, d.id_customer, d.id_company, d.status, k.status AS kwitansi_status
FROM tr_customer_deposit d
LEFT JOIN tr_kwitansi_deposit k ON k.no_kwitansi = d.no_referensi
WHERE d.tipe_sumber = 'KWITANSI_DEPOSIT'
  AND (k.id IS NULL OR d.id_customer <> k.id_customer OR d.id_company <> k.id_company
       OR (d.status <> 'MENUNGGU_PEMBAYARAN' AND k.status <> 'LUNAS')
       OR (d.status = 'MENUNGGU_PEMBAYARAN' AND k.status = 'LUNAS'));

-- Active/historically consumed vouchers must reconcile to their ledger.
SELECT d.id, d.no_deposit, d.nominal_awal, d.nominal_terpakai, d.sisa_saldo,
       COALESCE(SUM(CASE WHEN m.jenis_mutasi = 'IN' THEN m.nominal ELSE 0 END), 0) AS ledger_in,
       COALESCE(SUM(CASE WHEN m.jenis_mutasi = 'OUT' THEN m.nominal ELSE 0 END), 0) AS ledger_out
FROM tr_customer_deposit d
LEFT JOIN tr_customer_deposit_mutasi m ON m.id_deposit = d.id
WHERE d.status <> 'MENUNGGU_PEMBAYARAN'
GROUP BY d.id, d.no_deposit, d.nominal_awal, d.nominal_terpakai, d.sisa_saldo
HAVING ledger_in <> d.nominal_awal OR ledger_out <> d.nominal_terpakai
    OR d.nominal_awal - d.nominal_terpakai <> d.sisa_saldo;

-- New FIFO order requires an evidenced activation date.
SELECT id, no_deposit, id_customer, id_company, status
FROM tr_customer_deposit
WHERE status IN ('AKTIF', 'TERPAKAI_SEBAGIAN') AND sisa_saldo > 0 AND tanggal_aktif IS NULL;
