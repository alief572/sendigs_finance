-- Migration: Add id_bank_pengembalian column to tr_expense
-- Menyimpan referensi bank perusahaan (FK ke ms_bank.id) yang dipilih user
-- sebagai tujuan pengembalian saat kondisi "Lebih Kasbon" pada Expense Report.
ALTER TABLE `tr_expense`
ADD COLUMN `id_bank_pengembalian` INT NULL AFTER `bukti_pengembalian`;
