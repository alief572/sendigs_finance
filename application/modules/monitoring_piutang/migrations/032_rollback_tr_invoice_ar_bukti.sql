-- Run in the ERP database. Menghapus tabel bukti terima tambahan. Referensi file hilang dari database; file di uploads/bukti_terima_invoice/ TIDAK dihapus. Backup dulu bila sudah ada data.
DROP TABLE IF EXISTS `tr_invoice_ar_bukti`;
