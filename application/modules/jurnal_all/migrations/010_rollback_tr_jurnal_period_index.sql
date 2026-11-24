-- Rollback of 009: drops only the index it added. No data is changed. Idempotent.
SET @exists := (SELECT COUNT(*) FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_jurnal' AND INDEX_NAME = 'idx_jurnal_jenis_tgl');
SET @ddl := IF(@exists = 1, 'ALTER TABLE `tr_jurnal` DROP INDEX `idx_jurnal_jenis_tgl`', 'SELECT ''idx_jurnal_jenis_tgl not present'' AS note');
PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
