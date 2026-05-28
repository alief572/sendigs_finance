-- Migration: Migrate existing single allocation data to tr_alokasi_split
-- Module: Alokasi
-- Date: 2026-05-12
-- Updated: 2026-05-13 - Fixed ENUM casting issue
-- Description: Inserts records into tr_alokasi_split for all tr_alokasi_detail
--              that already have sts 1-7 (single allocation) but don't have
--              corresponding records in tr_alokasi_split yet.
--              This ensures all allocated transactions are tracked in the split table.
-- NOTE: Safe to run multiple times - only inserts for records not yet in tr_alokasi_split.
-- NOTE: Column `sts` is ENUM('0','1','2','3','4','5','6','7'). When MySQL casts ENUM
--        to integer directly, it uses the index position (1-based), not the string value.
--        We use CAST(d.sts AS CHAR) + 0 to get the actual numeric value.

INSERT INTO tr_alokasi_split (id_alokasi_detail, jenis_alokasi, nominal, created_by, created_date)
SELECT 
    d.id AS id_alokasi_detail,
    CAST(d.sts AS CHAR) + 0 AS jenis_alokasi,
    CASE 
        WHEN d.nominal_kredit > 0 THEN d.nominal_kredit
        ELSE d.nominal_debit
    END AS nominal,
    d.created_by,
    NOW() AS created_date
FROM tr_alokasi_detail d
WHERE CAST(d.sts AS CHAR) + 0 BETWEEN 1 AND 7
AND d.id NOT IN (
    SELECT DISTINCT id_alokasi_detail FROM tr_alokasi_split
);
