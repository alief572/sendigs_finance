-- Read-only. Before migration, existing ERP tables below must use InnoDB.
-- Apply jurnal_all/migrations/008_payment_reference_aliases.sql first if that table is absent.
SELECT DATABASE() AS selected_erp_schema, VERSION() AS server_version, @@max_allowed_packet AS max_allowed_packet;
SELECT TABLE_NAME, ENGINE
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN ('ms_generate','tr_rp_pengajuan_h','tr_rp_pengajuan_d','tr_rp_record','tr_rp_reject_log',
    'payment_approve','tr_payment_paid','tr_jurnal','tr_jurnal_payment_reference_aliases','tr_rp_transfer','tr_rp_transfer_file')
ORDER BY TABLE_NAME;

-- Confirm signed bank_charge and compatible PK types before running the migration.
SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND ((TABLE_NAME IN ('tr_rp_pengajuan_h', 'tr_rp_pengajuan_d', 'payment_approve', 'tr_payment_paid') AND COLUMN_NAME = 'id')
    OR (TABLE_NAME = 'tr_payment_paid' AND COLUMN_NAME IN ('bank_charge', 'admin_charge_bearer'))
    OR (TABLE_NAME = 'tr_rp_pengajuan_d' AND COLUMN_NAME IN ('id_transfer','payment_approval_id','keterangan_finance'))
    OR TABLE_NAME = 'tr_rp_transfer')
ORDER BY TABLE_NAME, ORDINAL_POSITION;

SELECT TABLE_NAME, INDEX_NAME, NON_UNIQUE, COLUMN_NAME
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
  AND (TABLE_NAME IN ('tr_rp_transfer', 'tr_rp_transfer_file') OR INDEX_NAME = 'uq_rp_detail_payment_approval')
ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX;
