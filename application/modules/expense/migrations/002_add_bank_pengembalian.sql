-- Migration: Add id_bank_pengembalian column to tr_expense
-- Menyimpan referensi bank perusahaan (FK ke ms_bank.id) yang dipilih user
-- sebagai tujuan pengembalian saat kondisi "Lebih Kasbon" pada Expense Report.
ALTER TABLE `tr_expense`
ADD COLUMN `id_bank_pengembalian` INT NULL AFTER `bukti_pengembalian`;
<<<<<<< HEAD
=======

-- Sync: 2026-09-22 Full re-push modul expense
>>>>>>> b127c8e19d1f7bed84ac03533b27c503f3837a47
