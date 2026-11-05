-- Additive contract. Existing rows remain unclassified until reconciled.
ALTER TABLE tr_kwitansi_deposit ADD COLUMN IF NOT EXISTS id_spk_penawaran VARCHAR(100) NULL;
ALTER TABLE tr_kwitansi_deposit ADD COLUMN IF NOT EXISTS spk_snapshot TEXT NULL;
ALTER TABLE tr_customer_deposit ADD COLUMN IF NOT EXISTS tanggal_aktif DATE NULL;
ALTER TABLE tr_customer_deposit ADD INDEX IF NOT EXISTS idx_customer_company_active (id_customer, id_company, status, tanggal_aktif, id);
ALTER TABLE tr_penerimaan_piutang ADD COLUMN IF NOT EXISTS jenis_penerimaan VARCHAR(16) NULL;

INSERT INTO permissions (nm_permission, ket, created_on, created_by)
SELECT 'Kwitansi_Deposit.Add', 'Create Kwitansi Deposit', NOW(), 'system'
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE nm_permission = 'Kwitansi_Deposit.Add');
INSERT INTO permissions (nm_permission, ket, created_on, created_by)
SELECT 'Kwitansi_Deposit.Export', 'Export Kwitansi Deposit', NOW(), 'system'
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE nm_permission = 'Kwitansi_Deposit.Export');
INSERT INTO permissions (nm_permission, ket, created_on, created_by)
SELECT 'Deposit_Customer.Export', 'Export Deposit Customer', NOW(), 'system'
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE nm_permission = 'Deposit_Customer.Export');
