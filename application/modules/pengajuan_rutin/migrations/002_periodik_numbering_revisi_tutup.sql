-- =====================================================================
-- Migration: Pengajuan Periodik, penomoran BP/PI/BG, revisi, dan tutup item
-- Target DB : DBERP (db_sendigs_ss)
-- Date      : 2026-11-05
-- Prasyarat : 001_periodik_per_item.sql sudah dijalankan.
--
-- Isi:
--   1. ms_periodik_counter  : counter atomik untuk BP-YYMM-NNN, BG-YYMM-NNN,
--                             dan PI-YYYY-NNNNN (reset per periode).
--   2. tr_pengajuan_rutin   : kolom periode pembayaran (YYYY-MM).
--   3. tr_pengajuan_rutin_detail : tautan revisi dan penutupan item.
--
-- Nomor item PI-YYYY-NNNNN tidak sama bentuknya dengan nomor invoice PO
-- PI-YYYY-MM-NNNNNN (purchase_order_payment/models/Pr_model.php).
-- Item ditutup ("Tidak diajukan ulang") tetap item_status = 8; penutupan
-- dibedakan dengan closed_at IS NOT NULL, sehingga v_request_payment tidak berubah.
--
-- Catatan: dijalankan manual (tidak ada migration runner). Aman diulang
-- untuk CREATE TABLE; ALTER TABLE hanya sekali.
-- =====================================================================

-- ---------------------------------------------------------------------
-- 1. Counter penomoran
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ms_periodik_counter` (
  `kode` VARCHAR(5) NOT NULL COMMENT 'BP, BG, atau PI',
  `periode` VARCHAR(6) NOT NULL COMMENT 'YYMM untuk BP/BG, YYYY untuk PI',
  `last_no` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`kode`, `periode`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ---------------------------------------------------------------------
-- 2. Header pengajuan: periode pembayaran
-- ---------------------------------------------------------------------
ALTER TABLE `tr_pengajuan_rutin`
  ADD COLUMN `periode` CHAR(7) NULL DEFAULT NULL COMMENT 'Periode pembayaran YYYY-MM' AFTER `tanggal_doc`;

-- Batch lama: periode diambil dari tanggal dokumen.
UPDATE `tr_pengajuan_rutin` SET `periode` = DATE_FORMAT(`tanggal_doc`, '%Y-%m') WHERE `periode` IS NULL;

-- ---------------------------------------------------------------------
-- 3. Detail pengajuan: revisi dan tutup item
-- ---------------------------------------------------------------------
ALTER TABLE `tr_pengajuan_rutin_detail`
  MODIFY COLUMN `no_item` VARCHAR(30) NULL DEFAULT NULL COMMENT 'Nomor item, format PI-YYYY-NNNNN',
  ADD COLUMN `revisi_dari` VARCHAR(30) NULL DEFAULT NULL COMMENT 'no_item yang direvisi oleh item ini',
  ADD COLUMN `diajukan_ulang_sebagai` VARCHAR(30) NULL DEFAULT NULL COMMENT 'no_item pengganti bila item ini ditolak lalu diajukan ulang',
  ADD COLUMN `closed_note` TEXT NULL DEFAULT NULL COMMENT 'Alasan Tidak diajukan ulang',
  ADD COLUMN `closed_by` VARCHAR(100) NULL DEFAULT NULL,
  ADD COLUMN `closed_at` DATETIME NULL DEFAULT NULL,
  ADD KEY `idx_revisi_dari` (`revisi_dari`),
  ADD KEY `idx_id_budget` (`id_budget`);
