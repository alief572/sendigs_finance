-- UNTUK DB REMOTE SAJA. Jangan dijalankan di DB lokal/Docker.
-- Sinkron coa_master: salin akun yang ADA di db_sendigs_gl_ss tapi BELUM ADA di STM, VUCA, SUSTAIN.
-- Akun yang sudah ada (cek no_perkiraan) tidak disentuh: tanpa UPDATE, tanpa DELETE.
-- Kolom id (AUTO_INCREMENT) dan kode_bank (hanya ada di company) dibiarkan default.
-- Saldo akun baru dipaksa 0 (saldoawal/debet/kredit/prev_month), tidak mengikuti GL.
-- bln/thn/kdcab/level/tipe disalin apa adanya dari GL.
-- Syarat: keempat database ada di server yang sama. Cek skema dulu: SHOW CREATE TABLE coa_master di tiap DB,
-- kolom pada INSERT di bawah harus ada semua. Preview dengan blok "CEK" sebelum INSERT.
-- Tabel MyISAM: tidak ada rollback, jadi jalankan blok BACKUP sebelum INSERT.
-- Idempoten: aman dijalankan ulang, baris yang sudah masuk dilewati.

-- ===== CEK (read-only): daftar akun yang akan ditambahkan =====
-- SELECT g.no_perkiraan, g.nama FROM db_sendigs_gl_ss.coa_master g
--  WHERE NOT EXISTS (SELECT 1 FROM db_sendigs_ss_stm.coa_master c WHERE c.no_perkiraan = g.no_perkiraan)
--  GROUP BY g.no_perkiraan ORDER BY g.no_perkiraan;

-- ===== BACKUP =====
-- CREATE TABLE db_sendigs_ss_stm.coa_master_bak_20261116     AS SELECT * FROM db_sendigs_ss_stm.coa_master;
-- CREATE TABLE db_sendigs_ss_vuca.coa_master_bak_20261116    AS SELECT * FROM db_sendigs_ss_vuca.coa_master;
-- CREATE TABLE db_sendigs_ss_sustain.coa_master_bak_20261116 AS SELECT * FROM db_sendigs_ss_sustain.coa_master;

-- ===== SINKRON =====
-- Template per company. MIN(id) dipakai agar satu baris per no_perkiraan bila GL punya duplikat.

INSERT INTO db_sendigs_ss_stm.coa_master
  (no_perkiraan, nama, kdcab, saldoawal, bln, thn, debet, kredit, tmp, tipe, level, grup, faktor, cf_grup, cf_urut, cf_child, prev_month)
SELECT g.no_perkiraan, g.nama, g.kdcab, 0, g.bln, g.thn, 0, 0, g.tmp, g.tipe, g.level, g.grup, g.faktor, g.cf_grup, g.cf_urut, g.cf_child, NULL
  FROM db_sendigs_gl_ss.coa_master g
  JOIN (SELECT MIN(id) id FROM db_sendigs_gl_ss.coa_master GROUP BY no_perkiraan) pick ON pick.id = g.id
 WHERE NOT EXISTS (SELECT 1 FROM db_sendigs_ss_stm.coa_master c WHERE c.no_perkiraan = g.no_perkiraan)
 ORDER BY g.no_perkiraan;

INSERT INTO db_sendigs_ss_vuca.coa_master
  (no_perkiraan, nama, kdcab, saldoawal, bln, thn, debet, kredit, tmp, tipe, level, grup, faktor, cf_grup, cf_urut, cf_child, prev_month)
SELECT g.no_perkiraan, g.nama, g.kdcab, 0, g.bln, g.thn, 0, 0, g.tmp, g.tipe, g.level, g.grup, g.faktor, g.cf_grup, g.cf_urut, g.cf_child, NULL
  FROM db_sendigs_gl_ss.coa_master g
  JOIN (SELECT MIN(id) id FROM db_sendigs_gl_ss.coa_master GROUP BY no_perkiraan) pick ON pick.id = g.id
 WHERE NOT EXISTS (SELECT 1 FROM db_sendigs_ss_vuca.coa_master c WHERE c.no_perkiraan = g.no_perkiraan)
 ORDER BY g.no_perkiraan;

INSERT INTO db_sendigs_ss_sustain.coa_master
  (no_perkiraan, nama, kdcab, saldoawal, bln, thn, debet, kredit, tmp, tipe, level, grup, faktor, cf_grup, cf_urut, cf_child, prev_month)
SELECT g.no_perkiraan, g.nama, g.kdcab, 0, g.bln, g.thn, 0, 0, g.tmp, g.tipe, g.level, g.grup, g.faktor, g.cf_grup, g.cf_urut, g.cf_child, NULL
  FROM db_sendigs_gl_ss.coa_master g
  JOIN (SELECT MIN(id) id FROM db_sendigs_gl_ss.coa_master GROUP BY no_perkiraan) pick ON pick.id = g.id
 WHERE NOT EXISTS (SELECT 1 FROM db_sendigs_ss_sustain.coa_master c WHERE c.no_perkiraan = g.no_perkiraan)
 ORDER BY g.no_perkiraan;

-- ===== VERIFIKASI: harus 0 baris untuk ketiganya =====
-- SELECT 'stm' d, COUNT(*) sisa FROM db_sendigs_gl_ss.coa_master g WHERE NOT EXISTS (SELECT 1 FROM db_sendigs_ss_stm.coa_master c WHERE c.no_perkiraan=g.no_perkiraan)
-- UNION ALL SELECT 'vuca', COUNT(*) FROM db_sendigs_gl_ss.coa_master g WHERE NOT EXISTS (SELECT 1 FROM db_sendigs_ss_vuca.coa_master c WHERE c.no_perkiraan=g.no_perkiraan)
-- UNION ALL SELECT 'sustain', COUNT(*) FROM db_sendigs_gl_ss.coa_master g WHERE NOT EXISTS (SELECT 1 FROM db_sendigs_ss_sustain.coa_master c WHERE c.no_perkiraan=g.no_perkiraan);
