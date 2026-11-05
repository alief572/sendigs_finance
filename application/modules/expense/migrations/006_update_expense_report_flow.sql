-- =====================================================================
-- Migration: 006_update_expense_report_flow.sql
-- Modul    : Pengeluaran Non Pembelian > Expense Report & Pengembalian Kasbon
-- Target DB: DBERP (db_sendigs_ss / db_sendigs_ss_dev) & Accounting DBs
-- Date     : 2026-10-22
-- =====================================================================

-- ---------------------------------------------------------------------
-- 1. Tambah kolom pelacak verifikasi dan audit pada tr_expense
-- ---------------------------------------------------------------------
ALTER TABLE `tr_expense`
  ADD COLUMN `tgl_approval_direktur` DATETIME NULL AFTER `app_finance_date`,
  ADD COLUMN `verified_by` VARCHAR(50) NULL AFTER `tgl_approval_direktur`,
  ADD COLUMN `verified_on` DATETIME NULL AFTER `verified_by`,
  ADD COLUMN `verif_checklist` VARCHAR(255) NULL AFTER `verified_on`,
  ADD COLUMN `verif_reject_reason` TEXT NULL AFTER `verif_checklist`;

-- ---------------------------------------------------------------------
-- 2. Registrasi Menu Baru 'Verifikasi Pengembalian Kasbon' pada grup Approval
-- ---------------------------------------------------------------------
INSERT INTO menus (title, link, icon, target, group_menu, parent_id, permission_id, status, `order`, created_on, pusat)
SELECT 'Verifikasi Pengembalian Kasbon', 'expense/verifikasi_pengembalian', 'fa fa-check-square-o', NULL, 1, 295, 979, 1, 12, NOW(), 0
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM menus WHERE link = 'expense/verifikasi_pengembalian');

-- ---------------------------------------------------------------------
-- 3. Nonaktifkan Menu Lama 'Pengembalian Expense' & 'Approval Pengembalian Expense'
-- ---------------------------------------------------------------------
UPDATE menus SET status = 0 WHERE id IN (354, 394);

-- ---------------------------------------------------------------------
-- 4. Registrasi COA 1103-01-15 (Piutang Kelebihan Kasbon) Lintas Database Akuntansi
-- ---------------------------------------------------------------------
-- STM Dev
INSERT INTO db_sendigs_ss_stm_dev.coa_master 
  (id, no_perkiraan, nama, kdcab, saldoawal, bln, thn, debet, kredit, tmp, tipe, level, faktor)
SELECT 
  COALESCE((SELECT MAX(id) + 1 FROM db_sendigs_ss_stm_dev.coa_master), 583),
  '1103-01-15', 'Piutang Kelebihan Kasbon', '101-A', 0, 10, 2026, 0, 0, 'O', 'A', 5, 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM db_sendigs_ss_stm_dev.coa_master WHERE no_perkiraan = '1103-01-15');

-- VUCA Dev
INSERT INTO db_sendigs_ss_vuca_dev.coa_master 
  (id, no_perkiraan, nama, kdcab, saldoawal, bln, thn, debet, kredit, tmp, tipe, level, faktor)
SELECT 
  COALESCE((SELECT MAX(id) + 1 FROM db_sendigs_ss_vuca_dev.coa_master), 583),
  '1103-01-15', 'Piutang Kelebihan Kasbon', '101-A', 0, 10, 2026, 0, 0, 'O', 'A', 5, 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM db_sendigs_ss_vuca_dev.coa_master WHERE no_perkiraan = '1103-01-15');

-- Sustain Dev
INSERT INTO db_sendigs_ss_sustain_dev.coa_master 
  (id, no_perkiraan, nama, kdcab, saldoawal, bln, thn, debet, kredit, tmp, tipe, level, faktor)
SELECT 
  COALESCE((SELECT MAX(id) + 1 FROM db_sendigs_ss_sustain_dev.coa_master), 583),
  '1103-01-15', 'Piutang Kelebihan Kasbon', '101-A', 0, 10, 2026, 0, 0, 'O', 'A', 5, 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM db_sendigs_ss_sustain_dev.coa_master WHERE no_perkiraan = '1103-01-15');

-- GL SS Dev
INSERT INTO db_sendigs_gl_ss_dev.coa_master 
  (id, no_perkiraan, nama, kdcab, saldoawal, bln, thn, debet, kredit, tmp, tipe, level, faktor)
SELECT 
  COALESCE((SELECT MAX(id) + 1 FROM db_sendigs_gl_ss_dev.coa_master), 583),
  '1103-01-15', 'Piutang Kelebihan Kasbon', '101-A', 0, 10, 2026, 0, 0, 'O', 'A', 5, 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM db_sendigs_gl_ss_dev.coa_master WHERE no_perkiraan = '1103-01-15');

-- STM Prod
INSERT INTO db_sendigs_ss_stm.coa_master 
  (id, no_perkiraan, nama, kdcab, saldoawal, bln, thn, debet, kredit, tmp, tipe, level, faktor)
SELECT 
  COALESCE((SELECT MAX(id) + 1 FROM db_sendigs_ss_stm.coa_master), 583),
  '1103-01-15', 'Piutang Kelebihan Kasbon', '101-A', 0, 10, 2026, 0, 0, 'O', 'A', 5, 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM db_sendigs_ss_stm.coa_master WHERE no_perkiraan = '1103-01-15');

-- VUCA Prod
INSERT INTO db_sendigs_ss_vuca.coa_master 
  (id, no_perkiraan, nama, kdcab, saldoawal, bln, thn, debet, kredit, tmp, tipe, level, faktor)
SELECT 
  COALESCE((SELECT MAX(id) + 1 FROM db_sendigs_ss_vuca.coa_master), 583),
  '1103-01-15', 'Piutang Kelebihan Kasbon', '101-A', 0, 10, 2026, 0, 0, 'O', 'A', 5, 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM db_sendigs_ss_vuca.coa_master WHERE no_perkiraan = '1103-01-15');

-- Sustain Prod
INSERT INTO db_sendigs_ss_sustain.coa_master 
  (id, no_perkiraan, nama, kdcab, saldoawal, bln, thn, debet, kredit, tmp, tipe, level, faktor)
SELECT 
  COALESCE((SELECT MAX(id) + 1 FROM db_sendigs_ss_sustain.coa_master), 583),
  '1103-01-15', 'Piutang Kelebihan Kasbon', '101-A', 0, 10, 2026, 0, 0, 'O', 'A', 5, 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM db_sendigs_ss_sustain.coa_master WHERE no_perkiraan = '1103-01-15');


-- ---------------------------------------------------------------------
-- 5. Penyesuaian View v_request_payment untuk Kategori Expense
-- ---------------------------------------------------------------------
-- Memastikan baris tr_expense dengan id_kasbon & kurang_bayar > 0 (Lebih Expense)
-- menggunakan nilai_pengajuan = kurang_bayar, status = 1 saat a.status = 2 (disetujui Direktur).
CREATE OR REPLACE VIEW `v_request_payment` AS 
select `z`.`id` AS `id`,`z`.`no_dokumen` AS `no_dokumen`,`z`.`request_by` AS `request_by`,`z`.`tanggal` AS `tanggal`,`z`.`keperluan` AS `keperluan`,`z`.`kategori` AS `kategori`,`z`.`nilai_pengajuan` AS `nilai_pengajuan`,`z`.`status` AS `status` from (
  select `a`.`id` AS `id`,convert(`a`.`no_doc` using latin1) AS `no_dokumen`,convert(`a`.`created_by` using latin1) AS `request_by`,`a`.`tgl_doc` AS `tanggal`,convert(`b`.`keperluan` using latin1) AS `keperluan`,convert('Transport' using latin1) AS `kategori`,`a`.`jumlah_expense` AS `nilai_pengajuan`,`a`.`status` AS `status` from (`tr_transport_req` `a` left join `tr_transport` `b` on((`b`.`no_req` = `a`.`no_doc`))) 
  union all 
  select `a`.`id` AS `id`,convert(`a`.`no_doc` using latin1) AS `no_dokumen`,convert(coalesce(`u`.`nm_lengkap`,`a`.`created_by`) using latin1) AS `request_by`,(case when (`a`.`tipe_pr` = 'pr stok') then coalesce((select cast(`material_planning_base_on_produksi`.`created_date` as date) from `material_planning_base_on_produksi` where (`material_planning_base_on_produksi`.`no_pr` = `a`.`id_pr`) limit 1),`a`.`tgl_doc`) when (`a`.`tipe_pr` = 'pr departemen') then coalesce((select cast(`rutin_non_planning_header`.`created_date` as date) from `rutin_non_planning_header` where (`rutin_non_planning_header`.`no_pr` = `a`.`id_pr`) limit 1),`a`.`tgl_doc`) when (`a`.`tipe_pr` = 'pr asset') then coalesce((select cast(`tran_pr_header`.`created_date` as date) from `tran_pr_header` where (`tran_pr_header`.`no_pr` = `a`.`id_pr`) limit 1),`a`.`tgl_doc`) else `a`.`tgl_doc` end) AS `tanggal`,convert(`a`.`keperluan` using latin1) AS `keperluan`,convert('Kasbon' using latin1) AS `kategori`,`a`.`jumlah_kasbon` AS `nilai_pengajuan`,`a`.`status` AS `status` from (`tr_kasbon` `a` left join `users` `u` on((`u`.`id_user` = `a`.`created_by`))) 
  union all 
  select `a`.`id` AS `id`,convert(`a`.`no_doc` using latin1) AS `no_dokumen`,convert(`a`.`created_by` using latin1) AS `request_by`,`a`.`tgl_doc` AS `tanggal`,convert((case when ((`a`.`id_kasbon` is not null) and (`a`.`kurang_bayar` is not null) and (`a`.`kurang_bayar` > 0)) then concat('Lebih Expense - ', coalesce(`a`.`informasi`,'')) else `a`.`informasi` end) using latin1) AS `keperluan`,convert('Expense' using latin1) AS `kategori`,(case when ((`a`.`kurang_bayar` is not null) and (`a`.`kurang_bayar` > 0)) then `a`.`kurang_bayar` else `a`.`jumlah` end) AS `nilai_pengajuan`,(case when ((`a`.`id_kasbon` is not null) and (`a`.`kurang_bayar` is not null) and (`a`.`kurang_bayar` > 0)) then (case when `a`.`status` = 2 then '1' else '0' end) else `a`.`status` end) AS `status` from `tr_expense` `a` 
  union all 
  select `a`.`id` AS `id`,convert(`a`.`no_doc` using latin1) AS `no_dokumen`,convert(`u`.`nm_lengkap` using latin1) AS `request_by`,`a`.`tanggal_doc` AS `tanggal`,convert((select group_concat(`dd`.`keterangan` separator '\n') from `tr_pengajuan_rutin_detail` `dd` where (`dd`.`no_doc` = `a`.`no_doc`)) using latin1) AS `keperluan`,convert('Periodik' using latin1) AS `kategori`,(select sum(`aa`.`nilai`) from `tr_pengajuan_rutin_detail` `aa` where (`aa`.`no_doc` = `a`.`no_doc`)) AS `nilai_pengajuan`,`a`.`status` AS `status` from (`tr_pengajuan_rutin` `a` left join `users` `u` on((`u`.`id_user` = `a`.`created_by`))) 
  union all 
  select `a`.`id` AS `id`,convert(`a`.`no_non_po` using latin1) AS `no_dokumen`,convert(`u`.`nm_lengkap` using latin1) AS `request_by`,(case when (`a`.`jenis_pr` = 'pr stok') then coalesce((select cast(`material_planning_base_on_produksi`.`created_date` as date) from `material_planning_base_on_produksi` where (`material_planning_base_on_produksi`.`no_pr` = `a`.`no_pr`) limit 1),cast(`a`.`created_date` as date)) when (`a`.`jenis_pr` = 'pr departemen') then coalesce((select cast(`rutin_non_planning_header`.`created_date` as date) from `rutin_non_planning_header` where (`rutin_non_planning_header`.`no_pr` = `a`.`no_pr`) limit 1),cast(`a`.`created_date` as date)) when (`a`.`jenis_pr` = 'pr asset') then coalesce((select cast(`tran_pr_header`.`created_date` as date) from `tran_pr_header` where (`tran_pr_header`.`no_pr` = `a`.`no_pr`) limit 1),cast(`a`.`created_date` as date)) else cast(`a`.`created_date` as date) end) AS `tanggal`,convert((case when (`a`.`jenis_pr` = 'pr stok') then convert(concat('PR Stock - ',`a`.`no_pr`) using utf8mb4) when (`a`.`jenis_pr` = 'pr departemen') then (select `rutin_non_planning_header`.`project_name` from `rutin_non_planning_header` where (`rutin_non_planning_header`.`no_pr` = `a`.`no_pr`) limit 1) else convert((select concat('PR Asset - ',`a`.`no_pr`,' - ',`tran_pr_detail`.`nm_barang`) from `tran_pr_detail` where (`tran_pr_detail`.`no_pr` = `a`.`no_pr`) limit 1) using utf8mb4) end) using latin1) AS `keperluan`,convert('Cash' using latin1) AS `kategori`,`a`.`total_pr` AS `nilai_pengajuan`,`a`.`sts` AS `status` from (`tr_pr_non_po` `a` left join `users` `u` on((`u`.`id_user` = `a`.`created_by`))) 
  union all 
  select `a`.`id` AS `id`,convert(`a`.`no_doc` using latin1) AS `no_dokumen`,convert(coalesce(`u`.`nm_lengkap`,`a`.`nama`,`a`.`created_by`) using latin1) AS `request_by`,`a`.`tgl_doc` AS `tanggal`,convert(`a`.`keperluan` using latin1) AS `keperluan`,convert((case when (`a`.`tipe` = 'petty_cash') then 'Petty Cash' when (`a`.`tipe` = 'petty_cash_hutang') then 'Petty Cash Hutang' else `a`.`tipe` end) using latin1) AS `kategori`,`a`.`jumlah` AS `nilai_pengajuan`,`a`.`status` AS `status` from (`request_payment` `a` left join `users` `u` on((`u`.`id_user` = `a`.`created_by`))) 
  union all 
  select `a`.`id` AS `id`,convert(`a`.`no_doc` using latin1) AS `no_dokumen`,convert(coalesce(`u`.`nm_lengkap`,`a`.`created_by`) using latin1) AS `request_by`,`a`.`tgl_doc` AS `tanggal`,convert(`a`.`deskripsi` using latin1) AS `keperluan`,convert('Direct Payment' using latin1) AS `kategori`,`a`.`grand_total` AS `nilai_pengajuan`,`a`.`sts` AS `status` from (`tr_direct_payment` `a` left join `users` `u` on((`u`.`id_user` = `a`.`created_by`)))
) `z` group by `z`.`no_dokumen` order by `z`.`tanggal` desc;
