CREATE DATABASE IF NOT EXISTS sendigs_upgrade_fixture;
USE sendigs_upgrade_fixture;

CREATE TABLE IF NOT EXISTS ci_sessions (
    id VARCHAR(128) NOT NULL PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    timestamp INT UNSIGNED NOT NULL DEFAULT 0,
    data BLOB NOT NULL,
    KEY ci_sessions_timestamp (timestamp)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS probe_log (
    counter INT NOT NULL PRIMARY KEY
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users (
    id_user INT NOT NULL PRIMARY KEY
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS user_groups (
    id_user INT NOT NULL,
    id_group INT NOT NULL,
    PRIMARY KEY (id_user, id_group)
) ENGINE=InnoDB;

REPLACE INTO ci_sessions (id, ip_address, timestamp, data)
VALUES ('vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv', '127.0.0.1', UNIX_TIMESTAMP(), 'runtime_probe|i:10;');
