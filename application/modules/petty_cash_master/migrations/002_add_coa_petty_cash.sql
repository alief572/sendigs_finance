-- =============================================================================
-- Migration: COA Petty Cash di Master Petty Cash (spec PC-R-04)
-- Description: Akun kas utama petty cash (sisi Kredit jurnal pencatatan,
--              sisi Debit jurnal refill) disimpan di master, bukan di kode.
-- Aman dijalankan ulang: kolom hanya ditambahkan bila belum ada.
-- Backfill: master lama diisi 1101-01-02 (Kas Kecil), akun yang selama ini
--           tertulis tetap di kode, sehingga perilaku jurnal tidak berubah.
-- Kolom dibiarkan NULL di database; aplikasi mewajibkannya pada simpan baru.
-- =============================================================================

SET @col_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'ms_petty_cash'
      AND COLUMN_NAME = 'coa_petty_cash'
);

SET @ddl = IF(
    @col_exists = 0,
    'ALTER TABLE `ms_petty_cash` ADD COLUMN `coa_petty_cash` VARCHAR(20) NULL DEFAULT NULL COMMENT ''COA Petty Cash (coa_master.no_perkiraan)'' AFTER `keterangan`',
    'SELECT 1'
);

PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE `ms_petty_cash`
SET `coa_petty_cash` = '1101-01-02'
WHERE `coa_petty_cash` IS NULL OR `coa_petty_cash` = '';
