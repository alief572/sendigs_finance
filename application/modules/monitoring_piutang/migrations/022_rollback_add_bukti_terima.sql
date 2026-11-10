-- Run in the ERP database. Menghapus kolom bukti terima. Referensi file hilang dari database; file di uploads/bukti_terima_invoice/ TIDAK dihapus. Backup dulu bila sudah ada data.
ALTER TABLE `tr_invoice_ar` DROP COLUMN `bukti_terima_nama`, DROP COLUMN `bukti_terima`;
