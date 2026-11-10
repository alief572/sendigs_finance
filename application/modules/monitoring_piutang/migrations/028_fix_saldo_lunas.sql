-- Run in the ERP database (same database as tr_invoicing). Lihat dulu 028a_dryrun_fix_saldo_lunas.sql.
-- Rollback: 026_rollback_fix_saldo_desimal.sql (generik: memulihkan semua baris tr_invoice_saldo_fix yang saldonya masih 0).
-- Invoice yang penerimaannya sudah menutup tagihan bruto tetapi saldo_piutang tidak ikut turun (biaya admin tidak mengurangi saldo, saldo neto dikurangi uang bruto,
-- invoice diedit setelah penerimaan, PPh ditambahkan dua kali pada cicilan). Hanya saldo_piutang yang diubah menjadi 0; saldo lama dicatat di tr_invoice_saldo_fix.
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
JOIN (SELECT d.id_inv, SUM(COALESCE(d.penerimaan, 0)) AS cash, SUM(COALESCE(d.pph23, 0)) AS pph23, SUM(COALESCE(d.biaya_admin, 0)) AS adm,
             SUBSTRING_INDEX(GROUP_CONCAT(d.sisa_piutang ORDER BY d.id DESC), ',', 1) AS last_sisa
      FROM tr_penerimaan_piutang_detail d GROUP BY d.id_inv) p ON p.id_inv = i.id
WHERE ROUND(i.saldo_piutang * 100) <> 0 AND COALESCE(i.sts_close, '0') <> '1' AND p.cash > 0
  AND (ABS(i.tagihan_ppn_jurnal - p.cash) < 1
       OR ABS(i.tagihan_ppn_jurnal - (p.cash + p.pph23)) < 1
       OR ABS(i.tagihan_ppn_jurnal - (p.cash + p.pph23 + p.adm)) < 1
       OR (i.saldo_piutang > 0 AND i.saldo_piutang <= 5000 AND p.adm = 0 AND p.last_sisa <= 0 AND ABS(i.tagihan_ppn_jurnal - (p.cash + p.pph23) - i.saldo_piutang) < 1));

UPDATE tr_invoicing i
JOIN (SELECT d.id_inv, SUM(COALESCE(d.penerimaan, 0)) AS cash, SUM(COALESCE(d.pph23, 0)) AS pph23, SUM(COALESCE(d.biaya_admin, 0)) AS adm,
             SUBSTRING_INDEX(GROUP_CONCAT(d.sisa_piutang ORDER BY d.id DESC), ',', 1) AS last_sisa
      FROM tr_penerimaan_piutang_detail d GROUP BY d.id_inv) p ON p.id_inv = i.id
SET i.saldo_piutang = 0
WHERE ROUND(i.saldo_piutang * 100) <> 0 AND COALESCE(i.sts_close, '0') <> '1' AND p.cash > 0
  AND (ABS(i.tagihan_ppn_jurnal - p.cash) < 1
       OR ABS(i.tagihan_ppn_jurnal - (p.cash + p.pph23)) < 1
       OR ABS(i.tagihan_ppn_jurnal - (p.cash + p.pph23 + p.adm)) < 1
       OR (i.saldo_piutang > 0 AND i.saldo_piutang <= 5000 AND p.adm = 0 AND p.last_sisa <= 0 AND ABS(i.tagihan_ppn_jurnal - (p.cash + p.pph23) - i.saldo_piutang) < 1));

COMMIT;
