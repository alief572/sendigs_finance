-- Migration 009: index for period/type filtering of the combined journal listing.
-- Database: db_sendigs_ss. tr_jurnal is InnoDB, so the index is built online. Idempotent: skips when it already exists.
-- Check first: SHOW INDEX FROM tr_jurnal;  (only idx_penerimaan_transaction_coa existed when this was written)

SET @exists := (SELECT COUNT(*) FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_jurnal' AND INDEX_NAME = 'idx_jurnal_jenis_tgl');
SET @ddl := IF(@exists = 0,
               'ALTER TABLE `tr_jurnal` ADD INDEX `idx_jurnal_jenis_tgl` (`jenis_transaksi`, `tgl_jurnal`), ALGORITHM=INPLACE, LOCK=NONE',
               'SELECT ''idx_jurnal_jenis_tgl already exists'' AS note');
PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
