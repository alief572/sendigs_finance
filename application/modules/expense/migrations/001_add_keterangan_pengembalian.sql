-- Migration: Add keterangan_pengembalian column to tr_expense
ALTER TABLE `tr_expense` 
ADD COLUMN `keterangan_pengembalian` TEXT NULL AFTER `keterangan_kurang_bayar`;

-- Sync: 2026-09-22 Full re-push modul expense
