-- Run in the ERP database (same database as tr_invoicing). Rollback: 024_rollback_add_invoice_close_columns.sql.
-- Kolom close di tr_invoicing sesuai md/invoicing/Design_Invoicing.md. Dipakai Invoicing::save_close_invoice dan Follow Up Piutang (close invoice),
-- tetapi belum ada di database dev (hanya sts_close). Aditif dan idempotent: tiap kolom hanya ditambah bila belum ada, tidak mengubah data.
SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_invoicing' AND COLUMN_NAME = 'close_by') = 0,
  'ALTER TABLE `tr_invoicing` ADD COLUMN `close_by` varchar(100) DEFAULT NULL', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_invoicing' AND COLUMN_NAME = 'close_date') = 0,
  'ALTER TABLE `tr_invoicing` ADD COLUMN `close_date` datetime DEFAULT NULL', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_invoicing' AND COLUMN_NAME = 'close_reason') = 0,
  'ALTER TABLE `tr_invoicing` ADD COLUMN `close_reason` text', 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
