-- Migration: 001_add_target_accounting
-- Feature: Multi Accounting Target
-- Description: Menambahkan kolom target_accounting pada tabel request mutasi
-- Requirements: 2.1, 2.2, 2.3
--
-- CATATAN: Jalankan migration ini di dalam container Docker MySQL.
-- Kolom target_accounting menyimpan CI connection group name:
--   - accounting_stm
--   - accounting_vuca
--   - accounting_sustain

ALTER TABLE tr_request_mutasi
ADD COLUMN target_accounting VARCHAR(30) NULL AFTER mata_uang;

ALTER TABLE tr_request_mutasi_aktual
ADD COLUMN target_accounting VARCHAR(30) NULL AFTER mata_uang;

ALTER TABLE tr_request_mutasi_admin
ADD COLUMN target_accounting VARCHAR(30) NULL AFTER mata_uang;
