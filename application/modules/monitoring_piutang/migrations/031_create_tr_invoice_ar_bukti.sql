-- Run in the ERP database (same database as tr_invoice_ar). Rollback: 032_rollback_tr_invoice_ar_bukti.sql.
-- Bukti terima tambahan (file ke-2 dst), satu baris per file. File pertama tetap di tr_invoice_ar.bukti_terima / bukti_terima_nama.
-- path = relatif terhadap base_url (uploads/bukti_terima_invoice/...), nama = nama asli file untuk tampilan.
-- File hanya ditambah selama tanggal terima belum tersimpan; setelah itu terkunci (aturan di Piutang_aging::bukti_boleh_ditambah).
-- Aditif dan idempotent: tabel hanya dibuat bila belum ada; collation id_invoice disamakan dengan tr_invoicing.id (lihat 019).
CREATE TABLE IF NOT EXISTS `tr_invoice_ar_bukti` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_invoice` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `path` varchar(255) NOT NULL,
  `nama` varchar(255) DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_invoice` (`id_invoice`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET @inv_coll = (SELECT COLLATION_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_invoicing' AND COLUMN_NAME = 'id');
SET @s = IF(@inv_coll IS NOT NULL AND (SELECT COLLATION_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_invoice_ar_bukti' AND COLUMN_NAME = 'id_invoice') <> @inv_coll,
  CONCAT('ALTER TABLE `tr_invoice_ar_bukti` MODIFY `id_invoice` varchar(100) CHARACTER SET utf8mb4 COLLATE ', @inv_coll, ' NOT NULL'), 'DO 0');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
