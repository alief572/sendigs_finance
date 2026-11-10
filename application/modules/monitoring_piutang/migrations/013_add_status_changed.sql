-- Run in the ERP database. Kapan invoice pindah tahap (Belum Dikirim -> Dikirim -> Diterima -> Ada Plan Bayar) dan dari tahap mana,
-- untuk menandai invoice yang baru masuk ke tab Follow Up Piutang dan menaruhnya paling atas.
ALTER TABLE `tr_invoice_ar`
  ADD COLUMN `status_from` varchar(20) DEFAULT NULL AFTER `ket_plan_bayar`,
  ADD COLUMN `status_changed_date` datetime DEFAULT NULL AFTER `status_from`;
