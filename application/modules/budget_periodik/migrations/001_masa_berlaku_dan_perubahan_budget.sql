-- =====================================================================
-- Migration: Budget Pembayaran Periodik, masa berlaku dan perubahan budget (BG)
-- Target DB : DBERP (db_sendigs_ss)
-- Date      : 2026-11-05
--
-- 1. ms_budget_rutin  : masa berlaku (0 = Selalu), jumlah yang sudah dibayar
--                       sebelum sistem, dan status aktif.
-- 2. tr_budget_bg / tr_budget_bg_line : perubahan budget yang menunggu
--    Approval Perubahan Budget. Master ms_budget_rutin hanya berubah setelah
--    baris perubahan disetujui. id master tidak pernah diganti (dipakai
--    tr_pengajuan_rutin_detail.id_budget dan jurnal_all).
--
-- Kolom ms_budget_rutin.keterangan (Baseline lama) tidak dihapus; form baru
-- tidak lagi menampilkannya.
-- Catatan: dijalankan manual (tidak ada migration runner).
-- =====================================================================

ALTER TABLE `ms_budget_rutin`
  ADD COLUMN `masa_berlaku` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 = Selalu, n = Terbatas n kali',
  ADD COLUMN `masa_awal` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Sudah dibayar sebelum sistem',
  ADD COLUMN `aktif` TINYINT(1) NOT NULL DEFAULT 1;

CREATE TABLE IF NOT EXISTS `tr_budget_bg` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `no_bg` VARCHAR(20) NOT NULL COMMENT 'BG-YYMM-NNN',
  `departement` VARCHAR(50) NOT NULL,
  `status` VARCHAR(10) NOT NULL DEFAULT 'pending' COMMENT 'pending atau done',
  `created_by` VARCHAR(100) NULL DEFAULT NULL,
  `created_on` DATETIME NULL DEFAULT NULL,
  `decided_on` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_no_bg` (`no_bg`),
  KEY `idx_dept_status` (`departement`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `tr_budget_bg_line` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `no_bg` VARCHAR(20) NOT NULL,
  `kind` VARCHAR(5) NOT NULL COMMENT 'new, edit, off, on',
  `id_budget` INT NULL DEFAULT NULL COMMENT 'ms_budget_rutin.id; NULL untuk item baru sampai disetujui',
  `before_json` TEXT NULL DEFAULT NULL,
  `after_json` TEXT NULL DEFAULT NULL,
  `fields` VARCHAR(255) NULL DEFAULT NULL COMMENT 'daftar field yang berubah, dipisah koma',
  `decision` CHAR(1) NULL DEFAULT NULL COMMENT 'A atau R',
  `note` TEXT NULL DEFAULT NULL COMMENT 'alasan reject',
  `decided_by` VARCHAR(100) NULL DEFAULT NULL,
  `decided_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_no_bg` (`no_bg`),
  KEY `idx_id_budget` (`id_budget`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
