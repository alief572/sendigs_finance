-- Run in the ERP database (same database as tr_invoicing).
-- Data kerja finance AR per invoice: pengiriman, penerimaan customer, follow up 1-4, plan bayar.
-- Satu baris per invoice (id_invoice = tr_invoicing.id). Tidak mengubah tr_invoicing.
-- Sumber: specs/input/Monitoring Piutang/Jawaban pertanyaan monitoring piutang.xlsx, Catatan Tambahan Request.
CREATE TABLE IF NOT EXISTS `tr_invoice_ar` (
  `id_invoice` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tgl_kirim` date DEFAULT NULL,
  `kirim_via` varchar(50) DEFAULT NULL COMMENT 'JNE / Driver / lainnya',
  `ket_kirim` text,
  `tgl_terima` date DEFAULT NULL COMMENT 'dasar umur piutang',
  `ket_terima` text,
  `fu1_plan` date DEFAULT NULL, `fu1_aktual` date DEFAULT NULL, `fu1_ket` text,
  `fu2_plan` date DEFAULT NULL, `fu2_aktual` date DEFAULT NULL, `fu2_ket` text,
  `fu3_plan` date DEFAULT NULL, `fu3_aktual` date DEFAULT NULL, `fu3_ket` text,
  `fu4_plan` date DEFAULT NULL, `fu4_aktual` date DEFAULT NULL, `fu4_ket` text,
  `plan_bayar` date DEFAULT NULL,
  `ket_plan_bayar` text,
  `created_by` varchar(100) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_invoice`),
  KEY `idx_tgl_terima` (`tgl_terima`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Riwayat setiap simpan / close (isi form disimpan sebagai JSON), supaya FU yang diundur tetap terlacak.
CREATE TABLE IF NOT EXISTS `tr_invoice_ar_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_invoice` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `aksi` varchar(20) NOT NULL COMMENT 'simpan / close',
  `data_json` text,
  `created_by` varchar(100) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_invoice` (`id_invoice`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
