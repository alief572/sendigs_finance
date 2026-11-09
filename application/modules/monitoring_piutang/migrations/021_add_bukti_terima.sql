-- Run in the ERP database (same database as tr_invoice_ar). Rollback: 022_rollback_add_bukti_terima.sql.
-- Bukti terima invoice dari customer (file wajib di step Diterima Customer): path relatif di bawah uploads/ dan nama asli file untuk tampilan.
-- Aditif dan idempotent: kolom hanya ditambah bila belum ada.
SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_invoice_ar' AND COLUMN_NAME = 'bukti_terima') = 0,
  'ALTER TABLE `tr_invoice_ar` ADD COLUMN `bukti_terima` varchar(255) DEFAULT NULL AFTER `ket_terima`', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_invoice_ar' AND COLUMN_NAME = 'bukti_terima_nama') = 0,
  'ALTER TABLE `tr_invoice_ar` ADD COLUMN `bukti_terima_nama` varchar(255) DEFAULT NULL AFTER `bukti_terima`', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
