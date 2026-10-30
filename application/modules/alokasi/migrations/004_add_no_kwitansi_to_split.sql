-- Migration: Add no_kwitansi column to tr_alokasi_split
-- Module: Alokasi / Kwitansi Deposit / Penerimaan Uang
-- Date: 2026-10-30
-- Description: Menyimpan kwitansi deposit yang dituju oleh split berjenis Deposit (jenis_alokasi = 8).
--              Opsional: split lama dan split tanpa pilihan kwitansi tetap NULL.
-- NOTE: Kompatibel MySQL 8 dan MariaDB (tanpa IF NOT EXISTS). Jalankan SEKALI;
--       bila kolom sudah ada, perintah akan gagal dengan "Duplicate column name" dan tidak mengubah apa pun.

ALTER TABLE `tr_alokasi_split`
  ADD COLUMN `no_kwitansi` VARCHAR(50) DEFAULT NULL COMMENT 'No kwitansi deposit tujuan (jenis_alokasi = 8)' AFTER `nominal`,
  ADD KEY `idx_no_kwitansi` (`no_kwitansi`);
