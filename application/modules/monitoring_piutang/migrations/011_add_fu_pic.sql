-- Run in the ERP database. PIC customer penerima follow up (satu per invoice, diisi sekali di awal follow up).
ALTER TABLE `tr_invoice_ar`
  ADD COLUMN `fu_pic_nama` varchar(100) DEFAULT NULL AFTER `ket_terima`,
  ADD COLUMN `fu_pic_telp` varchar(30) DEFAULT NULL AFTER `fu_pic_nama`;
