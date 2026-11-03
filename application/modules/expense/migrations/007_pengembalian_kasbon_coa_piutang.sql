-- Pindahkan sisi kredit jurnal "Pengembalian Kasbon" yang BELUM POSTING dari 1103-01-15 ke 1103-01-14 (Piutang Lain-lain Konsultan).
-- Idempoten: dijalankan ulang tidak mengubah apa pun. Jurnal yang sudah posting tidak disentuh.
-- Jalankan di schema utama (yang berisi tr_jurnal). Baca hasil bagian A dulu sebelum bagian B.
-- Syarat: tabel tr_jurnal_postings ada (migrasi jurnal_all 003).

-- ===== A. PRA-CEK (hanya baca) =====
DROP TEMPORARY TABLE IF EXISTS tmp_pengembalian_target;
CREATE TEMPORARY TABLE tmp_pengembalian_target (id INT PRIMARY KEY) AS
SELECT j.id
FROM tr_jurnal j
WHERE j.jenis_transaksi = 'Pengembalian Kasbon'
  AND j.coa = '1103-01-15' AND j.kredit > 0
  AND (j.sts IS NULL OR j.sts IN ('', '0')) AND j.tgl_posting IS NULL
  AND NOT EXISTS (SELECT 1 FROM tr_jurnal x
                  WHERE x.no_transaksi = j.no_transaksi AND x.jenis_transaksi = j.jenis_transaksi
                    AND x.id_company <=> j.id_company AND (x.sts = '1' OR x.tgl_posting IS NOT NULL))
  AND NOT EXISTS (SELECT 1 FROM tr_jurnal_postings p
                  WHERE p.no_transaksi = j.no_transaksi AND p.jenis_transaksi = j.jenis_transaksi);

-- Baris yang AKAN diubah:
SELECT j.id, j.no_transaksi, j.id_company, j.coa, j.nm_coa, j.kredit, j.sts
FROM tr_jurnal j JOIN tmp_pengembalian_target t ON t.id = j.id ORDER BY j.no_transaksi;

-- Baris 1103-01-15 pengembalian yang DILEWATI (sudah posting / ada metadata posting):
SELECT j.id, j.no_transaksi, j.id_company, j.kredit, j.sts, j.tgl_posting
FROM tr_jurnal j
WHERE j.jenis_transaksi = 'Pengembalian Kasbon' AND j.coa = '1103-01-15' AND j.kredit > 0
  AND j.id NOT IN (SELECT id FROM tmp_pengembalian_target) ORDER BY j.no_transaksi;

-- ===== B. APPLY =====
CREATE TABLE IF NOT EXISTS tr_jurnal_bak_pengembalian_coa_20261103 LIKE tr_jurnal;

START TRANSACTION;
INSERT IGNORE INTO tr_jurnal_bak_pengembalian_coa_20261103 SELECT j.* FROM tr_jurnal j JOIN tmp_pengembalian_target t ON t.id = j.id;
UPDATE tr_jurnal j JOIN tmp_pengembalian_target t ON t.id = j.id
SET j.coa = '1103-01-14', j.nm_coa = 'Piutang Lain-lain Konsultan';
COMMIT;

-- ===== C. VERIFIKASI =====
-- Debit harus sama dengan kredit per transaksi; sisa baris belum-posting dengan 1103-01-15 harus 0.
SELECT no_transaksi, SUM(debit) AS debit, SUM(kredit) AS kredit
FROM tr_jurnal WHERE jenis_transaksi = 'Pengembalian Kasbon' AND no_transaksi IN
  (SELECT DISTINCT no_transaksi FROM tr_jurnal_bak_pengembalian_coa_20261103) GROUP BY no_transaksi;
SELECT COUNT(*) AS sisa_belum_posting_1103_01_15 FROM tr_jurnal j JOIN tmp_pengembalian_target t ON t.id = j.id WHERE j.coa = '1103-01-15';

-- Rollback (jika perlu): UPDATE tr_jurnal j JOIN tr_jurnal_bak_pengembalian_coa_20261103 b ON b.id = j.id SET j.coa = b.coa, j.nm_coa = b.nm_coa;
