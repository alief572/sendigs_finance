-- ============================================================================
-- Migration: Fix no_transaksi pada tr_jurnal untuk Penerimaan Piutang
-- ============================================================================
-- Bug: no_transaksi menggunakan id_inv (ID invoice) alih-alih no_surat
--      (nomor surat penerimaan piutang)
-- Fix: Update no_transaksi dari id_inv menjadi no_surat via JOIN ke
--      tr_penerimaan_piutang_detail dan tr_penerimaan_piutang
-- ============================================================================

-- ============================================================================
-- DATABASE 1: db_sendigs_ss_vuca
-- ============================================================================
USE db_sendigs_ss_vuca;

-- Preview (opsional)
SELECT COUNT(*) AS rows_affected FROM tr_jurnal j
INNER JOIN tr_penerimaan_piutang_detail d ON j.no_transaksi = d.id_inv
INNER JOIN tr_penerimaan_piutang h ON d.id_header = h.no_surat
WHERE j.jenis_transaksi = 'Penerimaan Piutang';

-- Eksekusi fix
UPDATE tr_jurnal j
INNER JOIN tr_penerimaan_piutang_detail d ON j.no_transaksi = d.id_inv
INNER JOIN tr_penerimaan_piutang h ON d.id_header = h.no_surat
SET j.no_transaksi = h.no_surat
WHERE j.jenis_transaksi = 'Penerimaan Piutang';

-- ============================================================================
-- DATABASE 2: db_sendigs_sustain
-- ============================================================================
USE db_sendigs_sustain;

-- Preview (opsional)
SELECT COUNT(*) AS rows_affected FROM tr_jurnal j
INNER JOIN tr_penerimaan_piutang_detail d ON j.no_transaksi = d.id_inv
INNER JOIN tr_penerimaan_piutang h ON d.id_header = h.no_surat
WHERE j.jenis_transaksi = 'Penerimaan Piutang';

-- Eksekusi fix
UPDATE tr_jurnal j
INNER JOIN tr_penerimaan_piutang_detail d ON j.no_transaksi = d.id_inv
INNER JOIN tr_penerimaan_piutang h ON d.id_header = h.no_surat
SET j.no_transaksi = h.no_surat
WHERE j.jenis_transaksi = 'Penerimaan Piutang';

-- ============================================================================
-- DATABASE 3: db_sendigs_stm
-- ============================================================================
USE db_sendigs_stm;

-- Preview (opsional)
SELECT COUNT(*) AS rows_affected FROM tr_jurnal j
INNER JOIN tr_penerimaan_piutang_detail d ON j.no_transaksi = d.id_inv
INNER JOIN tr_penerimaan_piutang h ON d.id_header = h.no_surat
WHERE j.jenis_transaksi = 'Penerimaan Piutang';

-- Eksekusi fix
UPDATE tr_jurnal j
INNER JOIN tr_penerimaan_piutang_detail d ON j.no_transaksi = d.id_inv
INNER JOIN tr_penerimaan_piutang h ON d.id_header = h.no_surat
SET j.no_transaksi = h.no_surat
WHERE j.jenis_transaksi = 'Penerimaan Piutang';
