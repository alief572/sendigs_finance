-- Migration 002: Schema for Customer Deposit, Deposit Mutasi, and Kwitansi Deposit

-- 1. Master Saldo & Voucher Deposit Customer
CREATE TABLE IF NOT EXISTS `tr_customer_deposit` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `no_deposit` VARCHAR(50) NOT NULL UNIQUE,
  `id_customer` VARCHAR(100) NOT NULL,
  `nm_customer` VARCHAR(255) NOT NULL,
  `id_company` VARCHAR(50) NOT NULL DEFAULT '1',
  `tipe_sumber` ENUM('LEBIH_BAYAR', 'KWITANSI_DEPOSIT') NOT NULL,
  `no_referensi` VARCHAR(100) NOT NULL COMMENT 'No Surat Penerimaan atau No Kwitansi',
  `po_customer` TEXT NULL,
  `tanggal` DATE NOT NULL,
  `nominal_awal` DECIMAL(20,2) NOT NULL,
  `nominal_terpakai` DECIMAL(20,2) NOT NULL DEFAULT '0.00',
  `sisa_saldo` DECIMAL(20,2) NOT NULL,
  `status` ENUM('MENUNGGU_PEMBAYARAN', 'AKTIF', 'TERPAKAI_SEBAGIAN', 'LUNAS_TERPAKAI') NOT NULL DEFAULT 'AKTIF',
  `keterangan` TEXT NULL,
  `created_by` VARCHAR(100) NOT NULL,
  `created_date` DATETIME NOT NULL,
  `updated_by` VARCHAR(100) NULL,
  `updated_date` DATETIME NULL,
  INDEX `idx_cust_status` (`id_customer`, `status`),
  INDEX `idx_reff` (`no_referensi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Buku Pembantu Mutasi Deposit Customer (Audit Trail & Ledger)
CREATE TABLE IF NOT EXISTS `tr_customer_deposit_mutasi` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `id_deposit` INT NOT NULL,
  `id_customer` VARCHAR(100) NOT NULL,
  `no_transaksi` VARCHAR(100) NOT NULL COMMENT 'No Transaksi Penerimaan Piutang / Pelunasan',
  `tanggal` DATE NOT NULL,
  `jenis_mutasi` ENUM('IN', 'OUT') NOT NULL,
  `nominal` DECIMAL(20,2) NOT NULL,
  `saldo_sebelum` DECIMAL(20,2) NOT NULL,
  `saldo_sesudah` DECIMAL(20,2) NOT NULL,
  `keterangan` TEXT NULL,
  `created_by` VARCHAR(100) NOT NULL,
  `created_date` DATETIME NOT NULL,
  INDEX `idx_deposit` (`id_deposit`),
  INDEX `idx_transaksi` (`no_transaksi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Tagihan Kwitansi Deposit Customer per PO
CREATE TABLE IF NOT EXISTS `tr_kwitansi_deposit` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `no_kwitansi` VARCHAR(100) NOT NULL UNIQUE,
  `tanggal_kwitansi` DATE NOT NULL,
  `id_customer` VARCHAR(100) NOT NULL,
  `nm_customer` VARCHAR(255) NOT NULL,
  `id_company` VARCHAR(50) NOT NULL DEFAULT '1',
  `po_customer` TEXT NOT NULL,
  `nilai_kwitansi` DECIMAL(20,2) NOT NULL,
  `status` ENUM('MENUNGGU_PEMBAYARAN', 'LUNAS') NOT NULL DEFAULT 'MENUNGGU_PEMBAYARAN',
  `no_penerimaan` VARCHAR(100) NULL,
  `keterangan` TEXT NULL,
  `created_by` VARCHAR(100) NOT NULL,
  `created_date` DATETIME NOT NULL,
  `updated_by` VARCHAR(100) NULL,
  `updated_date` DATETIME NULL,
  INDEX `idx_kw_cust` (`id_customer`),
  INDEX `idx_kw_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Register Permissions for Deposit Customer and Kwitansi Deposit
INSERT INTO `permissions` (`nm_permission`, `ket`, `created_on`, `created_by`)
SELECT 'Deposit_Customer.View', 'View Deposit Customer', NOW(), 'system'
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Deposit_Customer.View');

INSERT INTO `permissions` (`nm_permission`, `ket`, `created_on`, `created_by`)
SELECT 'Deposit_Customer.Manage', 'Manage Deposit Customer', NOW(), 'system'
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Deposit_Customer.Manage');

INSERT INTO `permissions` (`nm_permission`, `ket`, `created_on`, `created_by`)
SELECT 'Kwitansi_Deposit.View', 'View Kwitansi Deposit', NOW(), 'system'
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Kwitansi_Deposit.View');

INSERT INTO `permissions` (`nm_permission`, `ket`, `created_on`, `created_by`)
SELECT 'Kwitansi_Deposit.Manage', 'Manage Kwitansi Deposit', NOW(), 'system'
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `nm_permission` = 'Kwitansi_Deposit.Manage');

-- 5. Register Menus under Finance Menu Group (anchor from penerimaan_uang)
SET @pu_anchor = (SELECT id FROM menus WHERE link IN ('penerimaan_uang', 'penerimaan_uang/index') ORDER BY id LIMIT 1);
SET @parent_menu = (SELECT parent_id FROM menus WHERE id = @pu_anchor);
SET @grp_menu = (SELECT group_menu FROM menus WHERE id = @pu_anchor);

INSERT INTO `menus` (`title`, `link`, `icon`, `target`, `group_menu`, `parent_id`, `permission_id`, `status`, `order`, `created_on`, `pusat`)
SELECT 'Monitoring Deposit', 'deposit_customer', 'fa fa-money', '_self', COALESCE(@grp_menu, 2), COALESCE(@parent_menu, 0),
       (SELECT MIN(id_permission) FROM `permissions` WHERE `nm_permission` = 'Deposit_Customer.View'), 1, 15, NOW(), 'Y'
WHERE NOT EXISTS (SELECT 1 FROM `menus` WHERE `link` = 'deposit_customer');

INSERT INTO `menus` (`title`, `link`, `icon`, `target`, `group_menu`, `parent_id`, `permission_id`, `status`, `order`, `created_on`, `pusat`)
SELECT 'Kwitansi Deposit', 'kwitansi_deposit', 'fa fa-ticket', '_self', COALESCE(@grp_menu, 2), COALESCE(@parent_menu, 0),
       (SELECT MIN(id_permission) FROM `permissions` WHERE `nm_permission` = 'Kwitansi_Deposit.View'), 1, 16, NOW(), 'Y'
WHERE NOT EXISTS (SELECT 1 FROM `menus` WHERE `link` = 'kwitansi_deposit');

-- 6. Grant to Administrator group (id_group = 1) if group_permissions exists
INSERT IGNORE INTO `group_permissions` (`id_group`, `id_permission`)
SELECT 1, p.id_permission FROM `permissions` p
WHERE p.nm_permission IN ('Deposit_Customer.View', 'Deposit_Customer.Manage', 'Kwitansi_Deposit.View', 'Kwitansi_Deposit.Manage');

