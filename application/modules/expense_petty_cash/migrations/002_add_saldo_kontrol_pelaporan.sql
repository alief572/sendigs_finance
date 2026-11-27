-- =============================================================================
-- Migration: Kontrol Saldo Petty Cash di Pelaporan (spec PC-R-01, PC-R-05)
-- Description: Saldo hasil hitung fisik uang (diisi pemegang petty cash saat
--              Ajukan), saldo seharusnya (dihitung server saat Ajukan), dan
--              selisihnya. Selisih hanya informasi: tidak dijurnal dan tidak
--              memblokir Ajukan/Approve.
-- Aman dijalankan ulang: tiap kolom hanya ditambahkan bila belum ada.
-- Kolom NULL untuk pelaporan lama (sebelum fitur ini) dan pelaporan draft.
-- =============================================================================

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_pelaporan_petty_cash' AND COLUMN_NAME = 'saldo_seharusnya') = 0,
    'ALTER TABLE `tr_pelaporan_petty_cash` ADD COLUMN `saldo_seharusnya` DECIMAL(15,0) NULL DEFAULT NULL COMMENT ''Total Budget - pengeluaran belum di-refill, dihitung server saat Ajukan'' AFTER `grand_total`',
    'SELECT 1'
);
PREPARE stmt FROM @ddl; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_pelaporan_petty_cash' AND COLUMN_NAME = 'saldo_petty_cash') = 0,
    'ALTER TABLE `tr_pelaporan_petty_cash` ADD COLUMN `saldo_petty_cash` DECIMAL(15,0) NULL DEFAULT NULL COMMENT ''Saldo hitung fisik uang, diisi saat Ajukan'' AFTER `saldo_seharusnya`',
    'SELECT 1'
);
PREPARE stmt FROM @ddl; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @ddl = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_pelaporan_petty_cash' AND COLUMN_NAME = 'selisih_saldo') = 0,
    'ALTER TABLE `tr_pelaporan_petty_cash` ADD COLUMN `selisih_saldo` DECIMAL(15,0) NULL DEFAULT NULL COMMENT ''saldo_petty_cash - saldo_seharusnya; negatif = uang kurang'' AFTER `saldo_petty_cash`',
    'SELECT 1'
);
PREPARE stmt FROM @ddl; EXECUTE stmt; DEALLOCATE PREPARE stmt;
