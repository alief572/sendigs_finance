-- DRY-RUN (hanya SELECT) untuk 025_fix_saldo_desimal.sql. Jalankan dulu dan periksa daftarnya sebelum 025.
-- Sasaran: invoice belum close yang sudah dibayar penuh (selisih nilai - penerimaan tidak nol tetapi < Rp 1) dan saldonya
-- (a) tidak pernah turun (saldo = nilai; mis. invoice non konsultasi diedit setelah penerimaan) atau (b) sudah turun dan tersisa < Rp 1.
-- Tidak termasuk: selisih tepat 0 (kasus 324/325, bukan desimal), sisa bulat Rp 1.000-2.900, dan baris INJECT.
SELECT i.id, i.no_invoice, i.non_kons, i.no_revisi, i.total_akhir_jurnal AS nilai, i.saldo_piutang AS saldo_sekarang, p.paid AS dibayar,
       ROUND(i.total_akhir_jurnal - p.paid, 2) AS selisih
FROM tr_invoicing i
JOIN (SELECT id_inv, SUM(COALESCE(consulting_reduction, COALESCE(penerimaan, 0) + COALESCE(biaya_admin, 0))) AS paid
      FROM tr_penerimaan_piutang_detail GROUP BY id_inv) p ON p.id_inv = i.id
WHERE i.saldo_piutang > 0
  AND (i.sts_close IS NULL OR i.sts_close <> '1')
  AND ABS(i.total_akhir_jurnal - p.paid) > 0 AND ABS(i.total_akhir_jurnal - p.paid) < 1
  AND (i.saldo_piutang < 1 OR i.saldo_piutang = i.total_akhir_jurnal)
ORDER BY i.no_invoice;
