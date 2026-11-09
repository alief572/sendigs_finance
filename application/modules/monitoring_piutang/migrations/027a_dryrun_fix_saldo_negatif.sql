-- DRY-RUN (hanya SELECT) untuk 027_fix_saldo_negatif.sql. Jalankan dulu dan periksa daftarnya sebelum 027.
-- Aturan 1 'N_bruto': PPh 23 tidak dipotong (semua penerimaan invoice ber-header N), customer bayar bruto, saldo = -PPh invoice.
-- Aturan 2 'bulat': saldo antara -1,00 dan 0 (pembulatan nominal bank).
-- Tidak termasuk: PPh Y dibayar bruto (035/106/036 STM/2026), lebih bayar nyata (184/STM/2025), 020/STM/2025, dan grup VSB.
SELECT i.id, i.no_invoice, i.total_akhir_jurnal AS nilai, i.pph_jurnal AS pph, i.saldo_piutang AS saldo_sekarang, p.paid AS dibayar,
       IF(p.semua_n = 1 AND ABS(i.saldo_piutang + i.pph_jurnal) < 1 AND ABS(i.saldo_piutang - (i.total_akhir_jurnal - p.paid)) < 1, 'N_bruto', 'bulat') AS aturan
FROM tr_invoicing i
JOIN (SELECT d.id_inv, SUM(COALESCE(d.consulting_reduction, COALESCE(d.penerimaan, 0) + COALESCE(d.biaya_admin, 0))) AS paid,
             MIN(h.pph23_dipotong = 'N') AS semua_n
      FROM tr_penerimaan_piutang_detail d LEFT JOIN tr_penerimaan_piutang h ON h.no_surat = d.id_header GROUP BY d.id_inv) p ON p.id_inv = i.id
WHERE i.saldo_piutang < 0
  AND (i.sts_close IS NULL OR i.sts_close <> '1')
  AND ((p.semua_n = 1 AND ABS(i.saldo_piutang + i.pph_jurnal) < 1 AND ABS(i.saldo_piutang - (i.total_akhir_jurnal - p.paid)) < 1)
       OR i.saldo_piutang >= -1)
ORDER BY aturan, i.no_invoice;
