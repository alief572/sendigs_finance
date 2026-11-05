-- Dry-run (READ ONLY) untuk reset 8 penerimaan piutang bank 30-Sep-2026 di db_sendigs_ss_dev.
-- Jalankan sebelum reset_penerimaan_20260930.php. Semua kolom "ok" harus bernilai 1.
SET SESSION TRANSACTION READ ONLY;

SELECT
  (SELECT COUNT(*) FROM tr_penerimaan_piutang WHERE (id, no_surat, id_alokasi) IN (
     (668,'00003/RKN-KRG/X/26','5251'),(680,'00015/RKN-KRG/X/26','5185'),(683,'00018/RKN-KRG/X/26','5184'),(684,'00019/RKN-KRG/X/26','5188'),
     (687,'00022/RKN-KRG/X/26','5187'),(688,'00023/RKN-KRG/X/26','5186'),(698,'00033/RKN-KRG/X/26','5285'),(702,'00037/RKN-KRG/X/26','5284'))) = 8 AS ok_header,
  (SELECT COUNT(*) FROM tr_penerimaan_piutang_detail WHERE id_header IN (SELECT no_surat FROM tr_penerimaan_piutang WHERE id IN (668,680,683,684,687,688,698,702))) = 8 AS ok_detail,
  (SELECT COUNT(*) FROM tr_jurnal WHERE jenis_transaksi='Penerimaan Piutang' AND sts='0' AND no_transaksi IN (SELECT no_surat FROM tr_penerimaan_piutang WHERE id IN (668,680,683,684,687,688,698,702))) = 24 AS ok_jurnal_24,
  (SELECT COUNT(*) FROM tr_jurnal WHERE sts='1' AND no_transaksi IN (SELECT no_surat FROM tr_penerimaan_piutang WHERE id IN (668,680,683,684,687,688,698,702))) = 0 AS ok_tidak_ada_jurnal_posted,
  (SELECT COUNT(*) FROM tr_invoicing i JOIN tr_penerimaan_piutang_detail d ON d.id_inv=i.id
     WHERE d.id_header IN (SELECT no_surat FROM tr_penerimaan_piutang WHERE id IN (668,680,683,684,687,688,698,702)) AND i.saldo_piutang=0
       AND (SELECT COUNT(*) FROM tr_penerimaan_piutang_detail d2 WHERE d2.id_inv=d.id_inv)=1) = 8 AS ok_invoice_lunas_dan_satu_penerimaan,
  (SELECT COUNT(*) FROM tr_alokasi_split s JOIN tr_alokasi_detail a ON a.id=s.id_alokasi_detail
     WHERE s.id IN (5251,5185,5184,5188,5187,5186,5285,5284) AND a.sts='7'
       AND (SELECT COUNT(*) FROM tr_alokasi_split s2 WHERE s2.id_alokasi_detail=s.id_alokasi_detail)=1) = 8 AS ok_alokasi,
  (SELECT COUNT(*) FROM tr_penerimaan_pph_23 WHERE id_detail_penerimaan IN (SELECT id FROM tr_penerimaan_piutang_detail WHERE id_header IN (SELECT no_surat FROM tr_penerimaan_piutang WHERE id IN (668,680,683,684,687,688,698,702)))) = 0 AS ok_tanpa_bukti_potong,
  (SELECT COUNT(*) FROM tr_customer_deposit WHERE no_referensi IN (SELECT no_surat FROM tr_penerimaan_piutang WHERE id IN (668,680,683,684,687,688,698,702))) = 0 AS ok_tanpa_voucher_deposit;

-- Saldo invoice yang akan dihasilkan (rumus rollback aplikasi): penerimaan + biaya_admin - (pph23 bila PPh tidak dipotong)
SELECT p.id, p.no_surat, i.no_invoice, i.saldo_piutang AS saldo_sekarang,
       i.saldo_piutang + d.penerimaan + d.biaya_admin - IF(p.pph23_dipotong='N', d.pph23, 0) AS saldo_setelah_reset
FROM tr_penerimaan_piutang p
JOIN tr_penerimaan_piutang_detail d ON d.id_header=p.no_surat
JOIN tr_invoicing i ON i.id=d.id_inv
WHERE p.id IN (668,680,683,684,687,688,698,702) ORDER BY p.id;
