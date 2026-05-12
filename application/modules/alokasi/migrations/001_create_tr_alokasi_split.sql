-- Migration: Create tr_alokasi_split table
-- Module: Alokasi
-- Date: 2025-01-01
-- Description: Creates table for storing split allocation details per transaction
-- NOTE: Uses CREATE TABLE IF NOT EXISTS - safe to run multiple times, will NOT affect existing tables

-- --------------------------------------------------------
-- Table: tr_alokasi_split
-- Stores split allocation records per tr_alokasi_detail transaction
-- One tr_alokasi_detail can have multiple split records (when sts = 8)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `tr_alokasi_split` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_alokasi_detail` VARCHAR(50) NOT NULL COMMENT 'FK ke tr_alokasi_detail.id',
  `jenis_alokasi` TINYINT(1) NOT NULL COMMENT '1-7 sesuai kode jenis alokasi',
  `nominal` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `created_by` VARCHAR(50) DEFAULT NULL,
  `created_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_id_alokasi_detail` (`id_alokasi_detail`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
