-- Migration: Create Laporan Kunjungan tables
-- Database: db_consultant_new_dev (development)
-- Date: 2025-01-01
-- Description: Creates tables for Laporan Kunjungan Konsultan module
-- NOTE: Uses CREATE TABLE IF NOT EXISTS - safe to run multiple times, will NOT affect existing tables

USE `db_consultant_new_dev`;

-- --------------------------------------------------------
-- Table: lk_visit_header
-- Stores visit session header data
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `lk_visit_header` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_spk_budgeting` VARCHAR(50) NOT NULL COMMENT 'FK ke kons_tr_spk_budgeting',
  `konsultan_id` VARCHAR(50) NOT NULL COMMENT 'ID konsultan dari session user',
  `konsultan_name` VARCHAR(100) NOT NULL COMMENT 'Nama konsultan',
  `visit_date` DATE NOT NULL COMMENT 'Tanggal kunjungan',
  `start_time` TIME NULL DEFAULT NULL COMMENT 'Waktu mulai (HH:mm:ss)',
  `finish_time` TIME NULL DEFAULT NULL COMMENT 'Waktu selesai (HH:mm:ss)',
  `duration_minutes` INT NULL DEFAULT NULL COMMENT 'Durasi dalam menit',
  `mandays_used` DECIMAL(5,2) NULL DEFAULT NULL COMMENT 'Mandays terpakai (durasi/8)',
  `potensi_improvement` TEXT NULL COMMENT 'Max 2000 chars',
  `hasil_improvement` TEXT NULL COMMENT 'Max 2000 chars',
  `status` ENUM('draft','final') NOT NULL DEFAULT 'draft' COMMENT 'Status laporan',
  `created_at` DATETIME NOT NULL COMMENT 'Timestamp pembuatan',
  `updated_at` DATETIME NULL DEFAULT NULL COMMENT 'Timestamp update terakhir',
  `created_by` VARCHAR(50) NULL DEFAULT NULL COMMENT 'User yang membuat',
  PRIMARY KEY (`id`),
  INDEX `idx_lk_visit_header_spk` (`id_spk_budgeting`),
  INDEX `idx_lk_visit_header_konsultan` (`konsultan_id`),
  INDEX `idx_lk_visit_header_status` (`status`),
  INDEX `idx_lk_visit_header_visit_date` (`visit_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='Header laporan kunjungan konsultan';

-- --------------------------------------------------------
-- Table: lk_visit_kegiatan
-- Stores activities/kegiatan for each visit
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `lk_visit_kegiatan` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `visit_id` INT(11) NOT NULL COMMENT 'FK ke lk_visit_header.id',
  `id_aktifitas` VARCHAR(50) NULL DEFAULT NULL COMMENT 'FK ke SPK aktifitas (NULL jika custom)',
  `nama_kegiatan` VARCHAR(500) NOT NULL COMMENT 'Nama kegiatan (dari SPK atau custom)',
  `is_custom` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 jika kegiatan custom',
  `sort_order` INT NOT NULL DEFAULT 0 COMMENT 'Urutan tampilan',
  PRIMARY KEY (`id`),
  INDEX `idx_lk_visit_kegiatan_visit` (`visit_id`),
  CONSTRAINT `fk_lk_kegiatan_visit` FOREIGN KEY (`visit_id`) REFERENCES `lk_visit_header` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='Kegiatan per kunjungan';

-- --------------------------------------------------------
-- Table: lk_visit_action_plan
-- Stores action plans for each kegiatan
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `lk_visit_action_plan` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `kegiatan_id` INT(11) NOT NULL COMMENT 'FK ke lk_visit_kegiatan.id',
  `visit_id` INT(11) NOT NULL COMMENT 'FK ke lk_visit_header.id',
  `description` VARCHAR(500) NOT NULL COMMENT 'Deskripsi action plan',
  `pic` VARCHAR(100) NOT NULL COMMENT 'Person in charge',
  `due_date` DATE NOT NULL COMMENT 'Tanggal target',
  `status` ENUM('Progress','Done') NOT NULL DEFAULT 'Progress' COMMENT 'Status action plan',
  `created_at` DATETIME NOT NULL COMMENT 'Timestamp pembuatan',
  `updated_at` DATETIME NULL DEFAULT NULL COMMENT 'Timestamp update',
  PRIMARY KEY (`id`),
  INDEX `idx_lk_action_plan_kegiatan` (`kegiatan_id`),
  INDEX `idx_lk_action_plan_visit` (`visit_id`),
  CONSTRAINT `fk_lk_action_plan_kegiatan` FOREIGN KEY (`kegiatan_id`) REFERENCES `lk_visit_kegiatan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_lk_action_plan_visit` FOREIGN KEY (`visit_id`) REFERENCES `lk_visit_header` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='Action plan per kegiatan';
