-- Rollback 025_fix_saldo_desimal.sql: kembalikan saldo lama hanya bila saldo sekarang masih 0 (belum berubah sejak koreksi), lalu hapus catatannya.
-- Tabel tr_invoice_saldo_fix sengaja tidak di-drop.
START TRANSACTION;

UPDATE tr_invoicing i
JOIN tr_invoice_saldo_fix f ON f.id_invoice = i.id
SET i.saldo_piutang = f.saldo_lama
WHERE i.saldo_piutang = 0;

DELETE f FROM tr_invoice_saldo_fix f
JOIN tr_invoicing i ON i.id = f.id_invoice
WHERE i.saldo_piutang = f.saldo_lama;

COMMIT;
