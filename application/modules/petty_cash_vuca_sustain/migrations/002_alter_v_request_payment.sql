-- Migration: Add request_payment table to v_request_payment view
-- This adds petty_cash and petty_cash_hutang records to the unified request payment view
-- Run this after 001_create_tr_petty_cash_vuca_sustain.sql

-- The new UNION ALL added at the end of the existing view:
-- SELECT from request_payment table with kategori mapped from tipe field

-- To apply, run the full ALTER VIEW statement.
-- The key addition is this UNION ALL block appended to the existing view:

/*
UNION ALL
SELECT
    a.id AS id,
    CONVERT(a.no_doc USING latin1) AS no_dokumen,
    CONVERT(COALESCE(u.nm_lengkap, a.nama, a.created_by) USING latin1) AS request_by,
    a.tgl_doc AS tanggal,
    CONVERT(a.keperluan USING latin1) AS keperluan,
    CONVERT(CASE
        WHEN a.tipe = 'petty_cash' THEN 'Petty Cash'
        WHEN a.tipe = 'petty_cash_hutang' THEN 'Petty Cash Hutang'
        ELSE a.tipe
    END USING latin1) AS kategori,
    a.jumlah AS nilai_pengajuan,
    a.status AS status
FROM request_payment a
LEFT JOIN users u ON u.id_user = a.created_by
*/
