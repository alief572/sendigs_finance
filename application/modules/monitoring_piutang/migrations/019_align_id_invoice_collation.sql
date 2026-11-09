-- Run in the ERP database (same database as tr_invoicing). Rollback: 020_rollback_align_id_invoice_collation.sql.
-- Migrasi 003/015 mengunci id_invoice ke utf8mb4_general_ci. Di server dengan default utf8mb4_0900_ai_ci (MySQL 8),
-- tr_invoicing.id memakai collation lain dan join/perbandingan gagal: "Illegal mix of collations" (error 1267).
-- Migrasi ini menyamakan kolom id_invoice di tiga tabel AR dengan collation tr_invoicing.id. Idempotent: tidak berbuat apa-apa bila sudah sama.
SET @inv_coll = (SELECT COLLATION_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_invoicing' AND COLUMN_NAME = 'id');

SET @s = IF(@inv_coll IS NOT NULL AND (SELECT COLLATION_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_invoice_ar' AND COLUMN_NAME = 'id_invoice') <> @inv_coll,
  CONCAT('ALTER TABLE `tr_invoice_ar` MODIFY `id_invoice` varchar(100) CHARACTER SET utf8mb4 COLLATE ', @inv_coll, ' NOT NULL'), 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s = IF(@inv_coll IS NOT NULL AND (SELECT COLLATION_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_invoice_ar_log' AND COLUMN_NAME = 'id_invoice') <> @inv_coll,
  CONCAT('ALTER TABLE `tr_invoice_ar_log` MODIFY `id_invoice` varchar(100) CHARACTER SET utf8mb4 COLLATE ', @inv_coll, ' NOT NULL'), 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s = IF(@inv_coll IS NOT NULL AND (SELECT COLLATION_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_invoice_ar_fu' AND COLUMN_NAME = 'id_invoice') <> @inv_coll,
  CONCAT('ALTER TABLE `tr_invoice_ar_fu` MODIFY `id_invoice` varchar(100) CHARACTER SET utf8mb4 COLLATE ', @inv_coll, ' NOT NULL'), 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
