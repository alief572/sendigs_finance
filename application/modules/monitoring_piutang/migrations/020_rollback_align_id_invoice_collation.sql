-- Run in the ERP database. Mengembalikan id_invoice di tabel AR ke utf8mb4_general_ci (keadaan setelah migrasi 003/015).
-- Hanya berguna bila tr_invoicing.id juga general_ci; kalau tidak, join ke tr_invoicing kembali gagal (error 1267).
ALTER TABLE `tr_invoice_ar` MODIFY `id_invoice` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
ALTER TABLE `tr_invoice_ar_log` MODIFY `id_invoice` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
ALTER TABLE `tr_invoice_ar_fu` MODIFY `id_invoice` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
