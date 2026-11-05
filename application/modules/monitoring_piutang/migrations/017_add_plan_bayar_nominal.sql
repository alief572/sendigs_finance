-- Run in the ERP database (same database as tr_invoicing / tr_invoice_ar). Rollback: 018_rollback_plan_bayar_nominal.sql.
-- Nilai plan bayar = sisa piutang saat plan bayar pertama kali disimpan (INV-R-08), supaya plan di Dashboard Plan Bayar
-- tetap sama walau invoice kemudian dibayar / close. Diisi aplikasi (Monitoring_piutang_model::save_ar) untuk plan baru.
-- Plan bayar yang sudah ada diisi ulang sekali dari riwayat (INV-R-09):
--   nilai plan = sisa piutang sekarang + semua pengurang piutang dari penerimaan yang diinput SETELAH plan bayar disimpan.
--   Waktu plan disimpan = log 'simpan' pertama di tr_invoice_ar_log yang berisi plan_bayar, cadangan status_changed_date / updated_date.
-- Aman dijalankan ulang: kolom hanya ditambah bila belum ada, dan hanya baris yang nilainya masih NULL yang diisi.
DELIMITER $$
DROP PROCEDURE IF EXISTS migrate_plan_bayar_nominal$$
CREATE PROCEDURE migrate_plan_bayar_nominal()
BEGIN
 IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tr_invoice_ar' AND COLUMN_NAME='plan_bayar_nominal') THEN
  ALTER TABLE `tr_invoice_ar` ADD COLUMN `plan_bayar_nominal` decimal(20,2) DEFAULT NULL AFTER `ket_plan_bayar`;
 END IF;
 IF EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tr_penerimaan_piutang_detail' AND COLUMN_NAME='consulting_reduction') THEN
  UPDATE tr_invoice_ar r
  JOIN tr_invoicing a ON a.id = r.id_invoice
  LEFT JOIN (SELECT id_invoice, MIN(created_date) AS plan_at FROM tr_invoice_ar_log
             WHERE aksi = 'simpan' AND data_json LIKE '%"plan_bayar":"2%' GROUP BY id_invoice) l ON l.id_invoice = r.id_invoice
  SET r.plan_bayar_nominal = ROUND(a.saldo_piutang + COALESCE((
        SELECT SUM(COALESCE(ppd.consulting_reduction, COALESCE(ppd.penerimaan, 0) + COALESCE(ppd.biaya_admin, 0)))
        FROM tr_penerimaan_piutang_detail ppd
        LEFT JOIN tr_penerimaan_piutang pph ON pph.no_surat = ppd.id_header
        WHERE ppd.id_inv = r.id_invoice
          AND COALESCE(pph.created_date, ppd.created_date) >= COALESCE(l.plan_at, r.status_changed_date, r.updated_date)), 0), 2)
  WHERE r.plan_bayar IS NOT NULL AND r.plan_bayar_nominal IS NULL;
 ELSE
  UPDATE tr_invoice_ar r
  JOIN tr_invoicing a ON a.id = r.id_invoice
  LEFT JOIN (SELECT id_invoice, MIN(created_date) AS plan_at FROM tr_invoice_ar_log
             WHERE aksi = 'simpan' AND data_json LIKE '%"plan_bayar":"2%' GROUP BY id_invoice) l ON l.id_invoice = r.id_invoice
  SET r.plan_bayar_nominal = ROUND(a.saldo_piutang + COALESCE((
        SELECT SUM(COALESCE(ppd.penerimaan, 0) + COALESCE(ppd.biaya_admin, 0))
        FROM tr_penerimaan_piutang_detail ppd
        LEFT JOIN tr_penerimaan_piutang pph ON pph.no_surat = ppd.id_header
        WHERE ppd.id_inv = r.id_invoice
          AND COALESCE(pph.created_date, ppd.created_date) >= COALESCE(l.plan_at, r.status_changed_date, r.updated_date)), 0), 2)
  WHERE r.plan_bayar IS NOT NULL AND r.plan_bayar_nominal IS NULL;
 END IF;
END$$
DELIMITER ;
CALL migrate_plan_bayar_nominal();
DROP PROCEDURE IF EXISTS migrate_plan_bayar_nominal;
