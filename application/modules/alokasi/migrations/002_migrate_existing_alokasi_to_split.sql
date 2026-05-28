-- Migration: Migrate existing single allocation data to tr_alokasi_split
-- Module: Alokasi
-- Date: 2026-05-12
-- Description: Inserts records into tr_alokasi_split for all tr_alokasi_detail
--              that already have sts 1-7 (single allocation) but don't have
--              corresponding records in tr_alokasi_split yet.
--              This ensures all allocated transactions are tracked in the split table.
-- NOTE: Safe to run multiple times - only inserts for records not yet in tr_alokasi_split.

INSERT INTO tr_alokasi_split (id_alokasi_detail, jenis_alokasi, nominal, created_by, created_date)
SELECT 
    d.id AS id_alokasi_detail,
    d.sts AS jenis_alokasi,
    CASE 
        WHEN d.nominal_kredit > 0 THEN d.nominal_kredit
        ELSE d.nominal_debit
    END AS nominal,
    d.created_by,
    NOW() AS created_date
FROM tr_alokasi_detail d
WHERE d.sts BETWEEN 1 AND 7
AND d.sts <> '0'
AND d.id NOT IN (
    SELECT DISTINCT id_alokasi_detail FROM tr_alokasi_split
);
