-- =============================================================================
-- Migration: Create Expense Petty Cash Tables
-- Description: Create transaction tables for Pencatatan, Evidence, and Pelaporan
-- Module: expense_petty_cash
-- Date: 2024
-- =============================================================================

-- =============================================================================
-- TABLE 1: tr_expense_petty_cash (Header Pencatatan)
-- =============================================================================
CREATE TABLE IF NOT EXISTS `tr_expense_petty_cash` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `no_pencatatan` VARCHAR(15) NOT NULL COMMENT 'Format: PCP-YYYY-NNNN',
    `tanggal` DATE NOT NULL COMMENT 'Tanggal pencatatan',
    `company` VARCHAR(20) NOT NULL COMMENT 'STM, VUCA, SUSTAIN',
    `request_by` VARCHAR(100) NOT NULL COMMENT 'Nama pemohon',
    `keterangan` TEXT NULL COMMENT 'Keterangan umum',
    `grand_total` DECIMAL(15,0) NOT NULL DEFAULT 0 COMMENT 'SUM total detail',
    `status` ENUM('draft','waiting approval','approved','reject') NOT NULL DEFAULT 'draft' COMMENT 'Status pencatatan',
    `journal_status` ENUM('pending','success','failed') NOT NULL DEFAULT 'pending' COMMENT 'Status sinkronisasi jurnal ke DBACC',
    `petty_cash_id` SMALLINT NOT NULL COMMENT 'FK to ms_petty_cash.id',
    `created_on` DATETIME DEFAULT NULL COMMENT 'Auto-filled on create',
    `created_by` INT DEFAULT NULL COMMENT 'FK to users.id_user',
    `modified_on` DATETIME DEFAULT NULL COMMENT 'Auto-filled on update',
    `modified_by` INT DEFAULT NULL COMMENT 'FK to users.id_user',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_no_pencatatan` (`no_pencatatan`),
    KEY `idx_petty_cash_id` (`petty_cash_id`),
    KEY `idx_company` (`company`),
    KEY `idx_status` (`status`),
    KEY `idx_journal_status` (`journal_status`),
    KEY `idx_tanggal` (`tanggal`),
    KEY `idx_created_by` (`created_by`),
    CONSTRAINT `fk_expense_pc_petty_cash` FOREIGN KEY (`petty_cash_id`) REFERENCES `ms_petty_cash` (`id`) ON UPDATE CASCADE,
    CONSTRAINT `fk_expense_pc_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id_user`) ON UPDATE CASCADE,
    CONSTRAINT `fk_expense_pc_modified_by` FOREIGN KEY (`modified_by`) REFERENCES `users` (`id_user`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Header pencatatan pengeluaran petty cash';

-- =============================================================================
-- TABLE 2: tr_expense_petty_cash_detail (Detail Item)
-- =============================================================================
CREATE TABLE IF NOT EXISTS `tr_expense_petty_cash_detail` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `pencatatan_id` INT(11) NOT NULL COMMENT 'FK to tr_expense_petty_cash.id',
    `coa_code` VARCHAR(20) NOT NULL COMMENT 'FK to coa_master.no_perkiraan',
    `pengeluaran` VARCHAR(255) NOT NULL COMMENT 'Nama pengeluaran',
    `spesifikasi` VARCHAR(255) NULL COMMENT 'Spesifikasi item',
    `jumlah` INT(11) NOT NULL COMMENT 'Quantity (1-9999)',
    `nominal` DECIMAL(15,0) NOT NULL COMMENT 'Harga satuan (1-999.999.999)',
    `total` DECIMAL(15,0) NOT NULL COMMENT 'jumlah x nominal',
    `sort_order` INT(11) NOT NULL DEFAULT 0 COMMENT 'Urutan baris',
    PRIMARY KEY (`id`),
    KEY `idx_pencatatan_id` (`pencatatan_id`),
    KEY `idx_coa_code` (`coa_code`),
    CONSTRAINT `fk_detail_pencatatan` FOREIGN KEY (`pencatatan_id`) REFERENCES `tr_expense_petty_cash` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Detail item pencatatan pengeluaran petty cash';

-- =============================================================================
-- TABLE 3: tr_expense_petty_cash_evidence (File Bukti)
-- =============================================================================
CREATE TABLE IF NOT EXISTS `tr_expense_petty_cash_evidence` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `detail_id` INT(11) NOT NULL COMMENT 'FK to tr_expense_petty_cash_detail.id',
    `original_name` VARCHAR(255) NOT NULL COMMENT 'Nama file asli',
    `encrypted_name` VARCHAR(255) NOT NULL COMMENT 'Nama file terenkripsi (md5)',
    `file_type` VARCHAR(10) NOT NULL COMMENT 'Extension: png, jpg, pdf, xlsx, xls',
    `file_size` INT(11) NOT NULL COMMENT 'Ukuran dalam bytes (max 5MB)',
    `uploaded_on` DATETIME NOT NULL COMMENT 'Waktu upload',
    PRIMARY KEY (`id`),
    KEY `idx_detail_id` (`detail_id`),
    CONSTRAINT `fk_evidence_detail` FOREIGN KEY (`detail_id`) REFERENCES `tr_expense_petty_cash_detail` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='File bukti/evidence pencatatan petty cash';

-- =============================================================================
-- TABLE 4: tr_pelaporan_petty_cash (Header Pelaporan)
-- =============================================================================
CREATE TABLE IF NOT EXISTS `tr_pelaporan_petty_cash` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `no_pelaporan` VARCHAR(15) NOT NULL COMMENT 'Format: RPC-YYYY-NNNN',
    `periode_start` DATE NOT NULL COMMENT 'Senin dari minggu pencatatan',
    `periode_end` DATE NOT NULL COMMENT 'Jumat dari minggu pencatatan',
    `company` VARCHAR(20) NOT NULL COMMENT 'STM, VUCA, SUSTAIN',
    `grand_total` DECIMAL(15,0) NOT NULL DEFAULT 0 COMMENT 'SUM nominal pencatatan',
    `status` ENUM('draft','waiting','approved','reject') NOT NULL DEFAULT 'draft' COMMENT 'Status pelaporan',
    `approver_id` INT DEFAULT NULL COMMENT 'FK to users.id_user (approver dari master)',
    `alasan_reject` TEXT NULL COMMENT 'Alasan reject (min 10 char)',
    `petty_cash_id` SMALLINT NOT NULL COMMENT 'FK to ms_petty_cash.id',
    `created_on` DATETIME DEFAULT NULL COMMENT 'Auto-filled on create',
    `created_by` INT DEFAULT NULL COMMENT 'FK to users.id_user',
    `approved_on` DATETIME DEFAULT NULL COMMENT 'Waktu approval',
    `approved_by` INT DEFAULT NULL COMMENT 'User yang approve',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_no_pelaporan` (`no_pelaporan`),
    KEY `idx_petty_cash_id` (`petty_cash_id`),
    KEY `idx_company` (`company`),
    KEY `idx_status` (`status`),
    KEY `idx_approver_id` (`approver_id`),
    KEY `idx_created_by` (`created_by`),
    KEY `idx_periode` (`periode_start`, `periode_end`),
    CONSTRAINT `fk_pelaporan_petty_cash` FOREIGN KEY (`petty_cash_id`) REFERENCES `ms_petty_cash` (`id`) ON UPDATE CASCADE,
    CONSTRAINT `fk_pelaporan_approver` FOREIGN KEY (`approver_id`) REFERENCES `users` (`id_user`) ON UPDATE CASCADE,
    CONSTRAINT `fk_pelaporan_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id_user`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Header pelaporan petty cash (per minggu per company)';

-- =============================================================================
-- TABLE 5: tr_pelaporan_petty_cash_detail (Link Pelaporan <-> Pencatatan)
-- =============================================================================
CREATE TABLE IF NOT EXISTS `tr_pelaporan_petty_cash_detail` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `pelaporan_id` INT(11) NOT NULL COMMENT 'FK to tr_pelaporan_petty_cash.id',
    `pencatatan_id` INT(11) NOT NULL COMMENT 'FK to tr_expense_petty_cash.id',
    PRIMARY KEY (`id`),
    KEY `idx_pelaporan_id` (`pelaporan_id`),
    KEY `idx_pencatatan_id` (`pencatatan_id`),
    UNIQUE KEY `uk_pelaporan_pencatatan` (`pelaporan_id`, `pencatatan_id`),
    CONSTRAINT `fk_pelaporan_detail_pelaporan` FOREIGN KEY (`pelaporan_id`) REFERENCES `tr_pelaporan_petty_cash` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_pelaporan_detail_pencatatan` FOREIGN KEY (`pencatatan_id`) REFERENCES `tr_expense_petty_cash` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Link table pelaporan dengan pencatatan petty cash';

-- =============================================================================
-- Migration complete!
-- Tables created:
--   1. tr_expense_petty_cash         - Header pencatatan (with journal_status)
--   2. tr_expense_petty_cash_detail  - Detail item pencatatan
--   3. tr_expense_petty_cash_evidence - File bukti per detail item
--   4. tr_pelaporan_petty_cash       - Header pelaporan (per minggu)
--   5. tr_pelaporan_petty_cash_detail - Link pelaporan <-> pencatatan
-- =============================================================================
