-- Run in the ERP database (same database as tr_invoicing). Rollback: 026_rollback_fix_saldo_desimal.sql. Lihat dulu 025a_dryrun_fix_saldo_desimal.sql.
-- Invoice yang sudah dibayar penuh tetapi sisa saldonya hanya desimal (selisih nilai - penerimaan > 0 dan < Rp 1) dilunaskan: saldo_piutang = 0.
-- Penyebab umum: invoice non konsultasi diedit setelah penerimaan legacy; Invoicing::legacy_update_invoice_non_konsultasi menyetel ulang saldo ke nilai penuh.
-- Kolom saldo_piutang_tanpa_ppn / _tanpa_pph tidak diubah (invoice lunas lain juga mempertahankannya). Saldo lama dicatat di tr_invoice_saldo_fix untuk rollback.
CREATE TABLE IF NOT EXISTS `tr_invoice_saldo_fix` (
  `id_invoice` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `saldo_lama` decimal(20,2) NOT NULL,
  `fixed_at` datetime NOT NULL,
  PRIMARY KEY (`id_invoice`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

START TRANSACTION;

INSERT IGNORE INTO tr_invoice_saldo_fix (id_invoice, saldo_lama, fixed_at)
SELECT i.id, i.saldo_piutang, NOW()
FROM tr_invoicing i
JOIN (SELECT id_inv, SUM(COALESCE(consulting_reduction, COALESCE(penerimaan, 0) + COALESCE(biaya_admin, 0))) AS paid
      FROM tr_penerimaan_piutang_detail GROUP BY id_inv) p ON p.id_inv = i.id
WHERE i.saldo_piutang > 0
  AND (i.sts_close IS NULL OR i.sts_close <> '1')
  AND ABS(i.total_akhir_jurnal - p.paid) > 0 AND ABS(i.total_akhir_jurnal - p.paid) < 1
  AND (i.saldo_piutang < 1 OR i.saldo_piutang = i.total_akhir_jurnal);

UPDATE tr_invoicing i
JOIN (SELECT id_inv, SUM(COALESCE(consulting_reduction, COALESCE(penerimaan, 0) + COALESCE(biaya_admin, 0))) AS paid
      FROM tr_penerimaan_piutang_detail GROUP BY id_inv) p ON p.id_inv = i.id
SET i.saldo_piutang = 0
WHERE i.saldo_piutang > 0
  AND (i.sts_close IS NULL OR i.sts_close <> '1')
  AND ABS(i.total_akhir_jurnal - p.paid) > 0 AND ABS(i.total_akhir_jurnal - p.paid) < 1
  AND (i.saldo_piutang < 1 OR i.saldo_piutang = i.total_akhir_jurnal);

COMMIT;
