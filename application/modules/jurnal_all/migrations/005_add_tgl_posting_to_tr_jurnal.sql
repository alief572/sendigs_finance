-- Migration 005: Add tgl_posting and posted_by to tr_jurnal
-- Database: db_sendigs_ss

ALTER TABLE `tr_jurnal`
  ADD COLUMN `tgl_posting` DATETIME NULL AFTER `sts`,
  ADD COLUMN `posted_by` VARCHAR(255) NULL AFTER `tgl_posting`;

-- Backfill historical posted rows (sts = '1')
UPDATE `tr_jurnal`
SET `tgl_posting` = CAST(`tgl_jurnal` AS DATETIME),
    `posted_by` = '203'
WHERE `sts` = '1' AND `tgl_posting` IS NULL;
