-- Migration: Tambah kolom rejected_by dan rejected_on pada tr_expense
ALTER TABLE `tr_expense` ADD COLUMN `rejected_by` VARCHAR(100) NULL AFTER `sts_reject_manage`;
ALTER TABLE `tr_expense` ADD COLUMN `rejected_on` DATETIME NULL AFTER `rejected_by`;
<<<<<<< HEAD
=======

-- Sync: 2026-09-22 Full re-push modul expense
>>>>>>> b127c8e19d1f7bed84ac03533b27c503f3837a47
