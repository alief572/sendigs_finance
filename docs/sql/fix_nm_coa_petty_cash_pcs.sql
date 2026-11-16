-- Perbaikan tr_jurnal.nm_coa yang terisi nomor COA, khusus data tab "Pencatatan Petty Cash" (PCS) di jurnal_all.
-- Cakupan = filter tab PCS belum posting di Jurnal_all_model::groups():
--   jenis_transaksi = 'Petty Cash', sts NULL/''/'0', no_transaksi ada di tr_expense_petty_cash.
-- Yang diubah: HANYA kolom nm_coa, HANYA baris dengan nm_coa = coa dan coa tidak kosong.
-- Yang sudah diposting (sts=1) dan jenis lain tidak disentuh. Baris dengan coa kosong dilewati.
-- Sumber nama: coa_master company sesuai id_company (4=VUCA, 5=STM, 6=SUSTAIN), lalu db_sendigs_gl_ss.
-- Bila nama tidak ketemu di keduanya, baris dibiarkan (tidak diubah).
-- Idempoten: setelah diperbaiki baris tidak lagi memenuhi nm_coa = coa.
-- Database transaksi diasumsikan db_sendigs_ss (DBERP). Sesuaikan bila remote memakai nama lain.
-- Urutan: 1) jalankan CEK, 2) BACKUP, 3) UPDATE, 4) VERIFIKASI.

-- ===== CEK (read-only) =====
SELECT j.id, j.no_transaksi, j.id_company, j.coa, j.nm_coa,
       COALESCE(NULLIF(TRIM(CASE j.id_company WHEN '4' THEN v.nama WHEN '5' THEN s.nama WHEN '6' THEN u.nama END),''), NULLIF(TRIM(g.nama),'')) AS nama_baru
  FROM db_sendigs_ss.tr_jurnal j
  LEFT JOIN db_sendigs_ss_vuca.coa_master    v ON v.no_perkiraan = j.coa AND j.id_company = '4'
  LEFT JOIN db_sendigs_ss_stm.coa_master     s ON s.no_perkiraan = j.coa AND j.id_company = '5'
  LEFT JOIN db_sendigs_ss_sustain.coa_master u ON u.no_perkiraan = j.coa AND j.id_company = '6'
  LEFT JOIN db_sendigs_gl_ss.coa_master      g ON g.no_perkiraan = j.coa
 WHERE j.jenis_transaksi = 'Petty Cash'
   AND (j.sts IS NULL OR j.sts IN ('','0'))
   AND j.coa <> '' AND j.nm_coa = j.coa
   AND EXISTS (SELECT 1 FROM db_sendigs_ss.tr_expense_petty_cash pc WHERE pc.no_pencatatan = j.no_transaksi)
 GROUP BY j.id ORDER BY j.id;

-- ===== BACKUP baris yang akan diubah =====
CREATE TABLE db_sendigs_ss.tr_jurnal_bak_nmcoa_20261116 AS
SELECT j.* FROM db_sendigs_ss.tr_jurnal j
 WHERE j.jenis_transaksi = 'Petty Cash'
   AND (j.sts IS NULL OR j.sts IN ('','0'))
   AND j.coa <> '' AND j.nm_coa = j.coa
   AND EXISTS (SELECT 1 FROM db_sendigs_ss.tr_expense_petty_cash pc WHERE pc.no_pencatatan = j.no_transaksi);

-- ===== UPDATE =====
-- Subquery tiap company dibatasi LIMIT 1 agar aman bila coa_master punya no_perkiraan ganda.
UPDATE db_sendigs_ss.tr_jurnal j
   SET j.nm_coa = COALESCE(
         NULLIF(TRIM(CASE j.id_company
           WHEN '4' THEN (SELECT nama FROM db_sendigs_ss_vuca.coa_master    WHERE no_perkiraan = j.coa ORDER BY id LIMIT 1)
           WHEN '5' THEN (SELECT nama FROM db_sendigs_ss_stm.coa_master     WHERE no_perkiraan = j.coa ORDER BY id LIMIT 1)
           WHEN '6' THEN (SELECT nama FROM db_sendigs_ss_sustain.coa_master WHERE no_perkiraan = j.coa ORDER BY id LIMIT 1)
         END), ''),
         NULLIF(TRIM((SELECT nama FROM db_sendigs_gl_ss.coa_master WHERE no_perkiraan = j.coa ORDER BY id LIMIT 1)), ''),
         j.nm_coa)
 WHERE j.jenis_transaksi = 'Petty Cash'
   AND (j.sts IS NULL OR j.sts IN ('','0'))
   AND j.coa <> '' AND j.nm_coa = j.coa
   AND EXISTS (SELECT 1 FROM db_sendigs_ss.tr_expense_petty_cash pc WHERE pc.no_pencatatan = j.no_transaksi);

-- ===== VERIFIKASI: sisa baris yang masih nm_coa = coa (hanya yang namanya memang tidak ada di coa_master) =====
SELECT j.id, j.no_transaksi, j.id_company, j.coa FROM db_sendigs_ss.tr_jurnal j
 WHERE j.jenis_transaksi = 'Petty Cash' AND (j.sts IS NULL OR j.sts IN ('','0'))
   AND j.coa <> '' AND j.nm_coa = j.coa
   AND EXISTS (SELECT 1 FROM db_sendigs_ss.tr_expense_petty_cash pc WHERE pc.no_pencatatan = j.no_transaksi);

-- ===== ROLLBACK manual bila perlu =====
-- UPDATE db_sendigs_ss.tr_jurnal j JOIN db_sendigs_ss.tr_jurnal_bak_nmcoa_20261116 b ON b.id = j.id SET j.nm_coa = b.nm_coa;
