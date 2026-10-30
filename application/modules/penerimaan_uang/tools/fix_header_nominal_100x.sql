-- Koreksi nominal_penerimaan_bank yang tersimpan 100x lebih besar dari nominal bank di alokasi.
-- Gejala: Report Penerimaan menampilkan Rp 1.130.535.000 untuk transfer Rp 11.305.350; Kontrol di modal detail selisih.
-- Ciri baris yang dikoreksi (semua harus terpenuhi): header = 100 x tr_alokasi_split.nominal, dan alokasi hanya dipakai satu penerimaan.
-- Dijalankan di database lokal db_sendigs_ss pada 30 Oktober 2026: 237 baris. Tidak mengubah detail, jurnal, maupun alokasi.
--
-- LANGKAH (wajib berurutan, jangan lewati backup):
-- 1) Backup:  mysqldump --single-transaction <database> tr_penerimaan_piutang > tr_penerimaan_piutang.before-fix.sql
-- 2) Dry-run: jalankan blok SELECT di bawah; catat jumlah baris.
-- 3) Apply:   jalankan blok UPDATE; ROW_COUNT harus sama dengan jumlah dry-run.
-- 4) Verifikasi: blok SELECT terakhir harus menghasilkan 0.
-- Pulihkan: restore tr_penerimaan_piutang dari backup langkah 1 (jangan restore tabel lain).

-- 2) DRY-RUN
SELECT COUNT(*) AS kandidat, SUM(p.nominal_penerimaan_bank) AS nominal_sekarang, SUM(s.nominal) AS nominal_seharusnya
FROM tr_penerimaan_piutang p
JOIN tr_alokasi_split s ON s.id = p.id_alokasi
WHERE s.nominal > 0
  AND ABS(p.nominal_penerimaan_bank - s.nominal * 100) < 1
  AND (SELECT COUNT(*) FROM tr_penerimaan_piutang q WHERE q.id_alokasi = p.id_alokasi) = 1;

-- 3) APPLY
START TRANSACTION;
UPDATE tr_penerimaan_piutang p
JOIN tr_alokasi_split s ON s.id = p.id_alokasi
SET p.nominal_penerimaan_bank = ROUND(p.nominal_penerimaan_bank / 100, 2)
WHERE s.nominal > 0
  AND ABS(p.nominal_penerimaan_bank - s.nominal * 100) < 1
  AND (SELECT COUNT(*) FROM (SELECT id, id_alokasi FROM tr_penerimaan_piutang) q WHERE q.id_alokasi = p.id_alokasi) = 1;
SELECT ROW_COUNT() AS baris_diubah;
-- Bila baris_diubah tidak sama dengan kandidat dry-run, jalankan ROLLBACK; selain itu COMMIT;
COMMIT;

-- 4) VERIFIKASI (harus 0)
SELECT COUNT(*) AS sisa_100x
FROM tr_penerimaan_piutang p
JOIN tr_alokasi_split s ON s.id = p.id_alokasi
WHERE s.nominal > 0 AND ABS(p.nominal_penerimaan_bank - s.nominal * 100) < 1;
