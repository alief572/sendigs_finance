CREATE TABLE IF NOT EXISTS tr_jurnal_payment_attempts (
    voucher VARCHAR(191) NOT NULL PRIMARY KEY,
    source_key TEXT NOT NULL,
    fingerprint CHAR(64) NOT NULL,
    status VARCHAR(32) NOT NULL,
    accounting_number VARCHAR(191) NULL,
    company VARCHAR(32) NOT NULL,
    actor_id VARCHAR(100) NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
