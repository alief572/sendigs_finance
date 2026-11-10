-- DRY-RUN (hanya SELECT) untuk 028_fix_saldo_lunas.sql. Jalankan dulu dan periksa daftarnya sebelum 028.
-- Invoice belum close dengan saldo <> 0 yang penerimaannya sudah menutup tagihan bruto (tagihan_ppn_jurnal):
--   cash = bruto | cash + PPh 23 = bruto | cash + PPh 23 + biaya admin = bruto | aturan D: sisa <= 5000 tanpa admin, detail terakhir sisa 0, bruto - (cash + PPh 23) = saldo.
-- Tidak termasuk: lebih bayar nyata (184/STM/2025), kurang bayar nyata (015/029 VSB, 049, 142, 148, 205, 206), dan 194/195 STM/2024.
SELECT i.id, i.no_invoice, i.tagihan_ppn_jurnal AS bruto, i.saldo_piutang AS saldo_sekarang, p.cash, p.pph23, p.adm,
       CASE WHEN ABS(i.tagihan_ppn_jurnal - p.cash) < 1 THEN 'cash=bruto'
            WHEN ABS(i.tagihan_ppn_jurnal - (p.cash + p.pph23)) < 1 THEN 'cash+pph=bruto'
            WHEN ABS(i.tagihan_ppn_jurnal - (p.cash + p.pph23 + p.adm)) < 1 THEN 'cash+pph+adm=bruto'
            ELSE 'D sisa kecil' END AS aturan
FROM tr_invoicing i
JOIN (SELECT d.id_inv, SUM(COALESCE(d.penerimaan, 0)) AS cash, SUM(COALESCE(d.pph23, 0)) AS pph23, SUM(COALESCE(d.biaya_admin, 0)) AS adm,
             SUBSTRING_INDEX(GROUP_CONCAT(d.sisa_piutang ORDER BY d.id DESC), ',', 1) AS last_sisa
      FROM tr_penerimaan_piutang_detail d GROUP BY d.id_inv) p ON p.id_inv = i.id
WHERE ROUND(i.saldo_piutang * 100) <> 0 AND COALESCE(i.sts_close, '0') <> '1' AND p.cash > 0
  AND (ABS(i.tagihan_ppn_jurnal - p.cash) < 1
       OR ABS(i.tagihan_ppn_jurnal - (p.cash + p.pph23)) < 1
       OR ABS(i.tagihan_ppn_jurnal - (p.cash + p.pph23 + p.adm)) < 1
       OR (i.saldo_piutang > 0 AND i.saldo_piutang <= 5000 AND p.adm = 0 AND p.last_sisa <= 0 AND ABS(i.tagihan_ppn_jurnal - (p.cash + p.pph23) - i.saldo_piutang) < 1))
ORDER BY aturan, i.no_invoice;
