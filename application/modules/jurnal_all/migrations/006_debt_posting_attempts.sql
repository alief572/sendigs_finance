CREATE TABLE IF NOT EXISTS tr_jurnal_debt_attempts (
    group_key CHAR(64) NOT NULL,
    fingerprint CHAR(64) NOT NULL,
    status VARCHAR(32) NOT NULL,
    actor_id VARCHAR(64) NOT NULL,
    actor_name VARCHAR(191) NOT NULL,
    target_results TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (group_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
