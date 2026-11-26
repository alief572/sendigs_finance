-- Run in the ERP database (same database as tr_invoice_ar). Rollback: 034_rollback_add_fu_status.sql.
-- Status per FU (MP-FR-17, MP-R-16..18, MP-R-25): fu1..4_status di tr_invoice_ar, status di tr_invoice_ar_fu (FU 5 dst.).
-- Nilai (mis. SUDAH DI TERIMA DI KEUANGAN, INVOICE BERMASALAH) divalidasi di server; varchar karena daftar beda per FU. NULL = data lama, tanpa backfill.
-- Aditif dan idempotent: kolom hanya ditambah bila belum ada.

SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_invoice_ar' AND COLUMN_NAME = 'fu1_status') = 0,
  'ALTER TABLE `tr_invoice_ar` ADD COLUMN `fu1_status` varchar(50) DEFAULT NULL AFTER `fu1_ket`', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_invoice_ar' AND COLUMN_NAME = 'fu2_status') = 0,
  'ALTER TABLE `tr_invoice_ar` ADD COLUMN `fu2_status` varchar(50) DEFAULT NULL AFTER `fu2_ket`', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_invoice_ar' AND COLUMN_NAME = 'fu3_status') = 0,
  'ALTER TABLE `tr_invoice_ar` ADD COLUMN `fu3_status` varchar(50) DEFAULT NULL AFTER `fu3_ket`', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_invoice_ar' AND COLUMN_NAME = 'fu4_status') = 0,
  'ALTER TABLE `tr_invoice_ar` ADD COLUMN `fu4_status` varchar(50) DEFAULT NULL AFTER `fu4_ket`', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_invoice_ar_fu' AND COLUMN_NAME = 'status') = 0,
  'ALTER TABLE `tr_invoice_ar_fu` ADD COLUMN `status` varchar(50) DEFAULT NULL AFTER `ket`', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
