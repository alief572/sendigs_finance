-- Run in the ERP database (same database as tr_invoice_ar).
-- Follow up tambahan di luar standar FU 1 - 4 (FU 5, 6, dst.), satu baris per FU.
-- Ditambah setelah FU 4 dilakukan; jadwal otomatis = tanggal dilakukan FU sebelumnya + 14 hari (bisa diubah).
-- FU yang sudah dilakukan (aktual terisi) tidak diubah lagi; jadwal yang belum dilakukan boleh dihapus.
CREATE TABLE IF NOT EXISTS `tr_invoice_ar_fu` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_invoice` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ke` smallint NOT NULL COMMENT 'nomor FU: 5, 6, ...',
  `plan` date DEFAULT NULL,
  `aktual` date DEFAULT NULL,
  `ket` text,
  `created_by` varchar(100) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_invoice_ke` (`id_invoice`, `ke`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
