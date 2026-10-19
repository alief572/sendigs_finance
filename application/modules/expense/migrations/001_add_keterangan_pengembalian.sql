-- Migration: Add keterangan_pengembalian column to tr_expense
ALTER TABLE `tr_expense` 
ADD COLUMN `keterangan_pengembalian` TEXT NULL AFTER `keterangan_kurang_bayar`;
<<<<<<< HEAD
=======

-- Sync: 2026-09-22 Full re-push modul expense
>>>>>>> b127c8e19d1f7bed84ac03533b27c503f3837a47
