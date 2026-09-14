-- Migration: Tambah kolom rejected_by dan rejected_on pada tr_expense
ALTER TABLE `tr_expense` ADD COLUMN `rejected_by` VARCHAR(100) NULL AFTER `sts_reject_manage`;
ALTER TABLE `tr_expense` ADD COLUMN `rejected_on` DATETIME NULL AFTER `rejected_by`;
