-- Run in the ERP database. Undo of 033_add_fu_status.sql.
-- PERINGATAN: menghapus isi status FU. Backup tr_invoice_ar dan tr_invoice_ar_fu dulu.
-- Idempotent: kolom hanya dihapus bila ada.

SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_invoice_ar' AND COLUMN_NAME = 'fu1_status') > 0,
  'ALTER TABLE `tr_invoice_ar` DROP COLUMN `fu1_status`', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_invoice_ar' AND COLUMN_NAME = 'fu2_status') > 0,
  'ALTER TABLE `tr_invoice_ar` DROP COLUMN `fu2_status`', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_invoice_ar' AND COLUMN_NAME = 'fu3_status') > 0,
  'ALTER TABLE `tr_invoice_ar` DROP COLUMN `fu3_status`', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_invoice_ar' AND COLUMN_NAME = 'fu4_status') > 0,
  'ALTER TABLE `tr_invoice_ar` DROP COLUMN `fu4_status`', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_invoice_ar_fu' AND COLUMN_NAME = 'status') > 0,
  'ALTER TABLE `tr_invoice_ar_fu` DROP COLUMN `status`', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
