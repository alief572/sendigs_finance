-- Rollback 003_create_tr_invoice_ar.sql. MENGHAPUS semua data follow up; backup dulu bila sudah dipakai.
DROP TABLE IF EXISTS `tr_invoice_ar_log`;
DROP TABLE IF EXISTS `tr_invoice_ar`;
