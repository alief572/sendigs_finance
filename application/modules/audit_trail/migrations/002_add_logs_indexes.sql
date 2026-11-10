-- MySQL/MariaDB compatible, idempotent. DDL commits independently.
SET @audit_ddl = IF(EXISTS(SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'logs' AND index_name = 'idx_logs_created_at'), 'SELECT 1', 'ALTER TABLE logs ADD INDEX idx_logs_created_at (created_at)');
PREPARE audit_index FROM @audit_ddl; EXECUTE audit_index; DEALLOCATE PREPARE audit_index;
SET @audit_ddl = IF(EXISTS(SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'logs' AND index_name = 'idx_logs_module_created'), 'SELECT 1', 'ALTER TABLE logs ADD INDEX idx_logs_module_created (module, created_at)');
PREPARE audit_index FROM @audit_ddl; EXECUTE audit_index; DEALLOCATE PREPARE audit_index;
SET @audit_ddl = IF(EXISTS(SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'logs' AND index_name = 'idx_logs_status_created'), 'SELECT 1', 'ALTER TABLE logs ADD INDEX idx_logs_status_created (status, created_at)');
PREPARE audit_index FROM @audit_ddl; EXECUTE audit_index; DEALLOCATE PREPARE audit_index;
