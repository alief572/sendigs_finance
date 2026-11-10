-- Run in the ERP database (same database as tr_invoicing). Lihat dulu 027a_dryrun_fix_saldo_negatif.sql.
-- Rollback: 026_rollback_fix_saldo_desimal.sql (generik: memulihkan semua baris tr_invoice_saldo_fix yang saldonya masih 0).
-- Saldo negatif karena penerimaan lama menurunkan saldo neto (setelah PPh) dengan uang bruto saat PPh 23 tidak dipotong (N), dan karena pembulatan Rp 1.
-- Aturan 1 (N_bruto): semua penerimaan invoice ber-header pph23_dipotong = 'N', saldo = -pph_jurnal = nilai - dibayar. Aturan 2 (bulat): -1 <= saldo < 0.
-- Hanya saldo_piutang yang diubah menjadi 0; saldo lama dicatat di tr_invoice_saldo_fix.
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
JOIN (SELECT d.id_inv, SUM(COALESCE(d.consulting_reduction, COALESCE(d.penerimaan, 0) + COALESCE(d.biaya_admin, 0))) AS paid,
             MIN(h.pph23_dipotong = 'N') AS semua_n
      FROM tr_penerimaan_piutang_detail d LEFT JOIN tr_penerimaan_piutang h ON h.no_surat = d.id_header GROUP BY d.id_inv) p ON p.id_inv = i.id
WHERE i.saldo_piutang < 0
  AND (i.sts_close IS NULL OR i.sts_close <> '1')
  AND ((p.semua_n = 1 AND ABS(i.saldo_piutang + i.pph_jurnal) < 1 AND ABS(i.saldo_piutang - (i.total_akhir_jurnal - p.paid)) < 1)
       OR i.saldo_piutang >= -1);

UPDATE tr_invoicing i
JOIN (SELECT d.id_inv, SUM(COALESCE(d.consulting_reduction, COALESCE(d.penerimaan, 0) + COALESCE(d.biaya_admin, 0))) AS paid,
             MIN(h.pph23_dipotong = 'N') AS semua_n
      FROM tr_penerimaan_piutang_detail d LEFT JOIN tr_penerimaan_piutang h ON h.no_surat = d.id_header GROUP BY d.id_inv) p ON p.id_inv = i.id
SET i.saldo_piutang = 0
WHERE i.saldo_piutang < 0
  AND (i.sts_close IS NULL OR i.sts_close <> '1')
  AND ((p.semua_n = 1 AND ABS(i.saldo_piutang + i.pph_jurnal) < 1 AND ABS(i.saldo_piutang - (i.total_akhir_jurnal - p.paid)) < 1)
       OR i.saldo_piutang >= -1);

COMMIT;
