-- =====================================================================
-- Migration: Pengajuan Periodik per item
-- Target DB : DBERP (db_sendigs_ss)
-- Date      : 2026-10-28
--
-- 1 batch pengajuan periodik (tr_pengajuan_rutin, nomor BP-YYMM-NNN) berisi
-- beberapa item (tr_pengajuan_rutin_detail). Setiap item punya nomor sendiri
-- (no_item, format lama PERIODIK-YYYY-NNNNN dari ms_generate format_nonpo)
-- dan di-approve / reject sendiri-sendiri.
--
-- item_status:
--   0 = menunggu approval
--   1 = approved (siap diajukan di Request Payment)
--   2 = diproses finance
--   8 = ditolak approver (boleh diajukan ulang di batch baru)
--   9 = ditolak di Request Payment
--
-- Data lama: no_item dibiarkan NULL sehingga dokumen yang sudah masuk
-- pembayaran (no_doc = nomor batch lama) tetap terbaca seperti sebelumnya.
-- Batch lama yang masih menunggu approval mendapat no_item saat diputuskan.
--
-- Catatan: dijalankan manual (tidak ada migration runner).
-- =====================================================================

-- ---------------------------------------------------------------------
-- 1. Kolom per item
-- ---------------------------------------------------------------------
ALTER TABLE `tr_pengajuan_rutin_detail`
  ADD COLUMN `no_item` VARCHAR(30) NULL DEFAULT NULL COMMENT 'Nomor item, format PERIODIK-YYYY-NNNNN' AFTER `no_doc`,
  ADD COLUMN `item_status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0 menunggu, 1 approved, 2 diproses finance, 8 ditolak approver, 9 ditolak RP',
  ADD COLUMN `item_reject_ket` TEXT NULL DEFAULT NULL,
  ADD COLUMN `item_approved_by` VARCHAR(100) NULL DEFAULT NULL,
  ADD COLUMN `item_approved_date` DATETIME NULL DEFAULT NULL,
  ADD UNIQUE KEY `uq_no_item` (`no_item`),
  ADD KEY `idx_item_status` (`item_status`);

-- ---------------------------------------------------------------------
-- 2. Backfill status item dari status header lama
-- ---------------------------------------------------------------------
UPDATE `tr_pengajuan_rutin_detail` d
JOIN `tr_pengajuan_rutin` h ON h.no_doc = d.no_doc
SET d.item_status = CASE
      WHEN h.status = 9 THEN 9
      WHEN h.sts_reject = 1 THEN 8
      WHEN h.status = 2 THEN 2
      WHEN h.status = 1 THEN 1
      ELSE 0
    END,
    d.item_reject_ket = IF(h.status = 9 OR h.sts_reject = 1, h.reject_ket, NULL),
    d.item_approved_by = IF(h.status IN (1, 2), h.approved_by, NULL),
    d.item_approved_date = IF(h.status IN (1, 2), h.approved_date, NULL);

-- ---------------------------------------------------------------------
-- 3. v_request_payment: periodik baru masuk per item (no_dokumen = no_item),
--    batch lama (tanpa no_item) tetap per batch seperti sebelumnya.
--    Disalin dari expense/migrations/006, hanya cabang Periodik yang berubah.
-- ---------------------------------------------------------------------
CREATE OR REPLACE VIEW `v_request_payment` AS 
select `z`.`id` AS `id`,`z`.`no_dokumen` AS `no_dokumen`,`z`.`request_by` AS `request_by`,`z`.`tanggal` AS `tanggal`,`z`.`keperluan` AS `keperluan`,`z`.`kategori` AS `kategori`,`z`.`nilai_pengajuan` AS `nilai_pengajuan`,`z`.`status` AS `status` from (
  select `a`.`id` AS `id`,convert(`a`.`no_doc` using latin1) AS `no_dokumen`,convert(`a`.`created_by` using latin1) AS `request_by`,`a`.`tgl_doc` AS `tanggal`,convert(`b`.`keperluan` using latin1) AS `keperluan`,convert('Transport' using latin1) AS `kategori`,`a`.`jumlah_expense` AS `nilai_pengajuan`,`a`.`status` AS `status` from (`tr_transport_req` `a` left join `tr_transport` `b` on((`b`.`no_req` = `a`.`no_doc`))) 
  union all 
  select `a`.`id` AS `id`,convert(`a`.`no_doc` using latin1) AS `no_dokumen`,convert(coalesce(`u`.`nm_lengkap`,`a`.`created_by`) using latin1) AS `request_by`,(case when (`a`.`tipe_pr` = 'pr stok') then coalesce((select cast(`material_planning_base_on_produksi`.`created_date` as date) from `material_planning_base_on_produksi` where (`material_planning_base_on_produksi`.`no_pr` = `a`.`id_pr`) limit 1),`a`.`tgl_doc`) when (`a`.`tipe_pr` = 'pr departemen') then coalesce((select cast(`rutin_non_planning_header`.`created_date` as date) from `rutin_non_planning_header` where (`rutin_non_planning_header`.`no_pr` = `a`.`id_pr`) limit 1),`a`.`tgl_doc`) when (`a`.`tipe_pr` = 'pr asset') then coalesce((select cast(`tran_pr_header`.`created_date` as date) from `tran_pr_header` where (`tran_pr_header`.`no_pr` = `a`.`id_pr`) limit 1),`a`.`tgl_doc`) else `a`.`tgl_doc` end) AS `tanggal`,convert(`a`.`keperluan` using latin1) AS `keperluan`,convert('Kasbon' using latin1) AS `kategori`,`a`.`jumlah_kasbon` AS `nilai_pengajuan`,`a`.`status` AS `status` from (`tr_kasbon` `a` left join `users` `u` on((`u`.`id_user` = `a`.`created_by`))) 
  union all 
  select `a`.`id` AS `id`,convert(`a`.`no_doc` using latin1) AS `no_dokumen`,convert(`a`.`created_by` using latin1) AS `request_by`,`a`.`tgl_doc` AS `tanggal`,convert((case when ((`a`.`id_kasbon` is not null) and (`a`.`kurang_bayar` is not null) and (`a`.`kurang_bayar` > 0)) then concat('Lebih Expense - ', coalesce(`a`.`informasi`,'')) else `a`.`informasi` end) using latin1) AS `keperluan`,convert('Expense' using latin1) AS `kategori`,(case when ((`a`.`kurang_bayar` is not null) and (`a`.`kurang_bayar` > 0)) then `a`.`kurang_bayar` else `a`.`jumlah` end) AS `nilai_pengajuan`,(case when ((`a`.`id_kasbon` is not null) and (`a`.`kurang_bayar` is not null) and (`a`.`kurang_bayar` > 0)) then (case when `a`.`status` = 2 then '1' else '0' end) else `a`.`status` end) AS `status` from `tr_expense` `a` 
  union all 
  select `a`.`id` AS `id`,convert(`a`.`no_doc` using latin1) AS `no_dokumen`,convert(`u`.`nm_lengkap` using latin1) AS `request_by`,`a`.`tanggal_doc` AS `tanggal`,convert((select group_concat(`dd`.`keterangan` separator '\n') from `tr_pengajuan_rutin_detail` `dd` where (`dd`.`no_doc` = `a`.`no_doc`)) using latin1) AS `keperluan`,convert('Periodik' using latin1) AS `kategori`,(select sum(`aa`.`nilai`) from `tr_pengajuan_rutin_detail` `aa` where (`aa`.`no_doc` = `a`.`no_doc`)) AS `nilai_pengajuan`,`a`.`status` AS `status` from (`tr_pengajuan_rutin` `a` left join `users` `u` on((`u`.`id_user` = `a`.`created_by`))) where not exists (select 1 from `tr_pengajuan_rutin_detail` `x` where ((`x`.`no_doc` = `a`.`no_doc`) and (`x`.`no_item` is not null)))
  union all 
  select `d`.`id` AS `id`,convert(`d`.`no_item` using latin1) AS `no_dokumen`,convert(`u`.`nm_lengkap` using latin1) AS `request_by`,`a`.`tanggal_doc` AS `tanggal`,convert(concat(coalesce(nullif(trim(`d`.`keterangan`),''),`d`.`nama`),' (Batch ',`a`.`no_doc`,')') using latin1) AS `keperluan`,convert('Periodik' using latin1) AS `kategori`,`d`.`nilai` AS `nilai_pengajuan`,`d`.`item_status` AS `status` from ((`tr_pengajuan_rutin_detail` `d` join `tr_pengajuan_rutin` `a` on((`a`.`no_doc` = `d`.`no_doc`))) left join `users` `u` on((`u`.`id_user` = `a`.`created_by`))) where (`d`.`no_item` is not null)
  union all 
  select `a`.`id` AS `id`,convert(`a`.`no_non_po` using latin1) AS `no_dokumen`,convert(`u`.`nm_lengkap` using latin1) AS `request_by`,(case when (`a`.`jenis_pr` = 'pr stok') then coalesce((select cast(`material_planning_base_on_produksi`.`created_date` as date) from `material_planning_base_on_produksi` where (`material_planning_base_on_produksi`.`no_pr` = `a`.`no_pr`) limit 1),cast(`a`.`created_date` as date)) when (`a`.`jenis_pr` = 'pr departemen') then coalesce((select cast(`rutin_non_planning_header`.`created_date` as date) from `rutin_non_planning_header` where (`rutin_non_planning_header`.`no_pr` = `a`.`no_pr`) limit 1),cast(`a`.`created_date` as date)) when (`a`.`jenis_pr` = 'pr asset') then coalesce((select cast(`tran_pr_header`.`created_date` as date) from `tran_pr_header` where (`tran_pr_header`.`no_pr` = `a`.`no_pr`) limit 1),cast(`a`.`created_date` as date)) else cast(`a`.`created_date` as date) end) AS `tanggal`,convert((case when (`a`.`jenis_pr` = 'pr stok') then convert(concat('PR Stock - ',`a`.`no_pr`) using utf8mb4) when (`a`.`jenis_pr` = 'pr departemen') then (select `rutin_non_planning_header`.`project_name` from `rutin_non_planning_header` where (`rutin_non_planning_header`.`no_pr` = `a`.`no_pr`) limit 1) else convert((select concat('PR Asset - ',`a`.`no_pr`,' - ',`tran_pr_detail`.`nm_barang`) from `tran_pr_detail` where (`tran_pr_detail`.`no_pr` = `a`.`no_pr`) limit 1) using utf8mb4) end) using latin1) AS `keperluan`,convert('Cash' using latin1) AS `kategori`,`a`.`total_pr` AS `nilai_pengajuan`,`a`.`sts` AS `status` from (`tr_pr_non_po` `a` left join `users` `u` on((`u`.`id_user` = `a`.`created_by`))) 
  union all 
  select `a`.`id` AS `id`,convert(`a`.`no_doc` using latin1) AS `no_dokumen`,convert(coalesce(`u`.`nm_lengkap`,`a`.`nama`,`a`.`created_by`) using latin1) AS `request_by`,`a`.`tgl_doc` AS `tanggal`,convert(`a`.`keperluan` using latin1) AS `keperluan`,convert((case when (`a`.`tipe` = 'petty_cash') then 'Petty Cash' when (`a`.`tipe` = 'petty_cash_hutang') then 'Petty Cash Hutang' else `a`.`tipe` end) using latin1) AS `kategori`,`a`.`jumlah` AS `nilai_pengajuan`,`a`.`status` AS `status` from (`request_payment` `a` left join `users` `u` on((`u`.`id_user` = `a`.`created_by`))) 
  union all 
  select `a`.`id` AS `id`,convert(`a`.`no_doc` using latin1) AS `no_dokumen`,convert(coalesce(`u`.`nm_lengkap`,`a`.`created_by`) using latin1) AS `request_by`,`a`.`tgl_doc` AS `tanggal`,convert(`a`.`deskripsi` using latin1) AS `keperluan`,convert('Direct Payment' using latin1) AS `kategori`,`a`.`grand_total` AS `nilai_pengajuan`,`a`.`sts` AS `status` from (`tr_direct_payment` `a` left join `users` `u` on((`u`.`id_user` = `a`.`created_by`)))
) `z` group by `z`.`no_dokumen` order by `z`.`tanggal` desc;
