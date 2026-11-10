-- Run in the ERP database. No resi pengiriman invoice, diisi di tab Dikirim (resi baru diketahui belakangan).
ALTER TABLE `tr_invoice_ar` ADD COLUMN `no_resi` varchar(100) DEFAULT NULL AFTER `ket_kirim`;
