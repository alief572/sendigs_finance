-- Run in the ERP database. Menghapus kolom close di tr_invoicing. Alasan/tanggal/user close yang sudah tercatat HILANG; backup dulu bila ada invoice yang sudah di-close.
-- Setelah ini tombol Close (Invoicing dan Follow Up) error lagi sampai kolom ditambahkan kembali.
ALTER TABLE `tr_invoicing` DROP COLUMN `close_reason`, DROP COLUMN `close_date`, DROP COLUMN `close_by`;
