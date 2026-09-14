-- =====================================================================
-- Migration: Request Payment overhaul (Request - Approval - Record)
-- Target DB : DBERP (db_sendigs_ss) -- main ERP database
-- Author    : Kiro
-- Date       : 2026-09-09
--
-- Membuat entitas untuk alur batch pengajuan approval:
--   tr_rp_pengajuan_h  : header batch pengajuan (PGJ-XXXX), 1 batch = 1 company
--   tr_rp_pengajuan_d  : item dokumen dalam batch + flag pajak/admin + hasil hitung
--   tr_rp_record       : snapshot final dokumen yang disetujui (read-only)
--   tr_rp_reject_log   : audit trail reject (append-only, tidak menimpa)
--
-- Catatan: dijalankan manual (tidak ada migration runner).
-- =====================================================================

-- ---------------------------------------------------------------------
-- 1. Header Pengajuan (batch)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tr_rp_pengajuan_h` (
  `id`            INT(11)      NOT NULL AUTO_INCREMENT,
  `no_pengajuan`  VARCHAR(30)  NOT NULL COMMENT 'Format PGJ-XXXX',
  `company_id`    VARCHAR(20)  DEFAULT NULL COMMENT 'kons_tr_company.id (7/3/4)',
  `company_nama`  VARCHAR(150) DEFAULT NULL COMMENT 'Nama company (snapshot saat submit)',
  `status`        ENUM('pending','done') NOT NULL DEFAULT 'pending',
  `jumlah_dokumen` INT(11)     NOT NULL DEFAULT 0,
  `total_dibayarkan` DECIMAL(20,2) NOT NULL DEFAULT 0.00,
  `submitted_at`  DATETIME     DEFAULT NULL COMMENT 'Waktu diajukan',
  `decided_at`    DATETIME     DEFAULT NULL COMMENT 'Waktu diputuskan approver',
  `approver`      VARCHAR(100) DEFAULT NULL COMMENT 'Username approver yang memutuskan',
  `created_by`    VARCHAR(100) DEFAULT NULL,
  `created_on`    DATETIME     DEFAULT NULL,
  `modified_by`   VARCHAR(100) DEFAULT NULL,
  `modified_on`   DATETIME     DEFAULT NULL,
  `deleted`       TINYINT(1)   NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_no_pengajuan` (`no_pengajuan`),
  KEY `idx_status` (`status`),
  KEY `idx_company` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ---------------------------------------------------------------------
-- 2. Detail Pengajuan (item dokumen dalam batch)
--    dpp        = nilai pokok (DPP) ditarik dari v_request_payment.nilai_pengajuan
--    flag_ppn   = 1 -> PPN 11% ditambahkan
--    flag_pph23 = 1 -> PPH23 2% dipotong  (eksklusif dg pph21)
--    flag_pph21 = 1 -> PPH21 2.5% dipotong (eksklusif dg pph23)
--    admin      = biaya admin bank nominal {0,2500,6500} dipotong
--    dibayarkan = (dpp + ppn) - pph - admin  (dihitung server, ceiling)
--    decision   = keputusan approver (default approved sampai di-reject)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tr_rp_pengajuan_d` (
  `id`            INT(11)      NOT NULL AUTO_INCREMENT,
  `id_pengajuan`  INT(11)      NOT NULL,
  `no_dokumen`    VARCHAR(100) NOT NULL COMMENT 'v_request_payment.no_dokumen',
  `id_dokumen`    VARCHAR(100) DEFAULT NULL COMMENT 'v_request_payment.id (referensi dokumen sumber)',
  `kategori`      VARCHAR(60)  DEFAULT NULL,
  `request_by`    VARCHAR(150) DEFAULT NULL,
  `keperluan`     TEXT         DEFAULT NULL,
  `dpp`           DECIMAL(20,2) NOT NULL DEFAULT 0.00,
  `flag_ppn`      TINYINT(1)   NOT NULL DEFAULT 0,
  `flag_pph23`    TINYINT(1)   NOT NULL DEFAULT 0,
  `flag_pph21`    TINYINT(1)   NOT NULL DEFAULT 0,
  `nilai_ppn`     DECIMAL(20,2) NOT NULL DEFAULT 0.00,
  `nilai_pph`     DECIMAL(20,2) NOT NULL DEFAULT 0.00,
  `admin`         INT(11)      NOT NULL DEFAULT 0,
  `dibayarkan`    DECIMAL(20,2) NOT NULL DEFAULT 0.00,
  `decision`      ENUM('approved','rejected') NOT NULL DEFAULT 'approved',
  `created_on`    DATETIME     DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pengajuan` (`id_pengajuan`),
  KEY `idx_no_dokumen` (`no_dokumen`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ---------------------------------------------------------------------
-- 3. Record snapshot (salinan final dokumen approved)
--    Snapshot = tidak berubah walau dokumen master diedit/dihapus kemudian.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tr_rp_record` (
  `id`            INT(11)      NOT NULL AUTO_INCREMENT,
  `id_pengajuan`  INT(11)      NOT NULL,
  `no_pengajuan`  VARCHAR(30)  NOT NULL,
  `no_dokumen`    VARCHAR(100) NOT NULL,
  `kategori`      VARCHAR(60)  DEFAULT NULL,
  `request_by`    VARCHAR(150) DEFAULT NULL,
  `company_nama`  VARCHAR(150) DEFAULT NULL,
  `keperluan`     TEXT         DEFAULT NULL,
  `dpp`           DECIMAL(20,2) NOT NULL DEFAULT 0.00,
  `flag_ppn`      TINYINT(1)   NOT NULL DEFAULT 0,
  `flag_pph23`    TINYINT(1)   NOT NULL DEFAULT 0,
  `flag_pph21`    TINYINT(1)   NOT NULL DEFAULT 0,
  `nilai_ppn`     DECIMAL(20,2) NOT NULL DEFAULT 0.00,
  `nilai_pph`     DECIMAL(20,2) NOT NULL DEFAULT 0.00,
  `admin`         INT(11)      NOT NULL DEFAULT 0,
  `dibayarkan`    DECIMAL(20,2) NOT NULL DEFAULT 0.00,
  `created_on`    DATETIME     DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pengajuan` (`id_pengajuan`),
  KEY `idx_no_dokumen` (`no_dokumen`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ---------------------------------------------------------------------
-- 4. Log Reject (audit trail, append-only)
--    Setiap aksi reject dicatat sebagai entry BARU (bukan overwrite),
--    sehingga histori lengkap reject per dokumen dapat ditelusuri.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tr_rp_reject_log` (
  `id`            INT(11)      NOT NULL AUTO_INCREMENT,
  `no_dokumen`    VARCHAR(100) NOT NULL,
  `id_pengajuan`  INT(11)      DEFAULT NULL,
  `no_pengajuan`  VARCHAR(30)  DEFAULT NULL,
  `approver`      VARCHAR(100) DEFAULT NULL,
  `rejected_at`   DATETIME     DEFAULT NULL,
  `alasan`        TEXT         DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_no_dokumen` (`no_dokumen`),
  KEY `idx_pengajuan` (`id_pengajuan`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ---------------------------------------------------------------------
-- 5. Auto-number generator untuk No. Pengajuan (PGJ-XXXX)
--    Dipakai oleh All_model->GetAutoGenerate('format_pengajuan_rp').
--    info  = pola kode (XXXX diganti nomor urut, YEAR opsional)
--    info2 = last number, info3 = tahun, info4 = panjang padding
-- ---------------------------------------------------------------------
INSERT INTO `ms_generate` (`tipe`, `info`, `info2`, `info3`, `info4`)
SELECT 'format_pengajuan_rp', 'PGJ-XXXX', 0, YEAR(NOW()), 4
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM `ms_generate` WHERE `tipe` = 'format_pengajuan_rp'
);
