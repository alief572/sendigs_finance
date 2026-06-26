-- =============================================================================
-- Migration: Petty Cash Overhaul
-- Description: Migrate ms_petty_cash from flat structure to header-detail
-- Date: 2024
-- =============================================================================

-- =============================================================================
-- STEP 1: Backup data lama (opsional, untuk rollback manual)
-- =============================================================================
-- CREATE TABLE `ms_petty_cash_backup` AS SELECT * FROM `ms_petty_cash`;

-- =============================================================================
-- STEP 2: CREATE detail table (sebelum ALTER, agar bisa migrasi data)
-- =============================================================================
CREATE TABLE IF NOT EXISTS `ms_petty_cash_detail` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `petty_cash_id` INT(11) NOT NULL COMMENT 'FK to ms_petty_cash.id',
    `coa_code` VARCHAR(20) NOT NULL COMMENT 'FK to coa_master.no_perkiraan',
    `jenis_pengeluaran` VARCHAR(255) NOT NULL COMMENT 'Deskripsi jenis pengeluaran',
    `nominal` DECIMAL(15,0) NOT NULL COMMENT 'Budget amount for this COA',
    PRIMARY KEY (`id`),
    KEY `idx_petty_cash_id` (`petty_cash_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================================================
-- STEP 3: MIGRATE data - Split semicolon-separated COA into detail rows
-- For each old row, split `coa` field into individual detail rows.
-- Since old data does not have per-COA jenis_pengeluaran and nominal,
-- set jenis_pengeluaran = '-' (placeholder) and nominal = 0 (to be updated manually).
-- =============================================================================
-- NOTE: MySQL does not natively support string splitting in a single query.
-- This migration uses a stored procedure for reliable data migration.
-- =============================================================================

DELIMITER $$

DROP PROCEDURE IF EXISTS `migrate_petty_cash_coa_to_detail`$$

CREATE PROCEDURE `migrate_petty_cash_coa_to_detail`()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_id INT;
    DECLARE v_coa TEXT;
    DECLARE v_budget DECIMAL(15,0);
    DECLARE v_coa_item VARCHAR(20);
    DECLARE v_coa_count INT;
    DECLARE v_nominal_per_coa DECIMAL(15,0);
    DECLARE v_pos INT;
    DECLARE v_remaining TEXT;

    DECLARE cur CURSOR FOR
        SELECT id, coa, budget FROM ms_petty_cash WHERE coa IS NOT NULL AND coa != '';
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    OPEN cur;

    read_loop: LOOP
        FETCH cur INTO v_id, v_coa, v_budget;
        IF done THEN
            LEAVE read_loop;
        END IF;

        -- Count number of COA items (number of semicolons + 1)
        SET v_coa_count = LENGTH(v_coa) - LENGTH(REPLACE(v_coa, ';', '')) + 1;

        -- Distribute budget equally among COA items (if budget > 0)
        IF v_budget IS NOT NULL AND v_budget > 0 AND v_coa_count > 0 THEN
            SET v_nominal_per_coa = FLOOR(v_budget / v_coa_count);
        ELSE
            SET v_nominal_per_coa = 0;
        END IF;

        -- Split and insert each COA code
        SET v_remaining = v_coa;

        split_loop: LOOP
            IF v_remaining IS NULL OR v_remaining = '' THEN
                LEAVE split_loop;
            END IF;

            SET v_pos = LOCATE(';', v_remaining);

            IF v_pos > 0 THEN
                SET v_coa_item = TRIM(SUBSTRING(v_remaining, 1, v_pos - 1));
                SET v_remaining = SUBSTRING(v_remaining, v_pos + 1);
            ELSE
                SET v_coa_item = TRIM(v_remaining);
                SET v_remaining = '';
            END IF;

            -- Insert detail row only if coa_item is not empty
            IF v_coa_item != '' THEN
                INSERT INTO `ms_petty_cash_detail` (`petty_cash_id`, `coa_code`, `jenis_pengeluaran`, `nominal`)
                VALUES (v_id, v_coa_item, '-', v_nominal_per_coa);
            END IF;

        END LOOP split_loop;

    END LOOP read_loop;

    CLOSE cur;
END$$

DELIMITER ;

-- Execute the migration procedure
CALL `migrate_petty_cash_coa_to_detail`();

-- Clean up: drop the procedure after use
DROP PROCEDURE IF EXISTS `migrate_petty_cash_coa_to_detail`;

-- =============================================================================
-- STEP 4: ALTER ms_petty_cash table
-- Rename columns and drop unused columns
-- =============================================================================

-- Rename `approval` to `approver`
ALTER TABLE `ms_petty_cash` CHANGE COLUMN `approval` `approver` INT(11) NOT NULL COMMENT 'FK to users.id_user';

-- Rename `budget` to `total_budget`
ALTER TABLE `ms_petty_cash` CHANGE COLUMN `budget` `total_budget` DECIMAL(15,0) NOT NULL DEFAULT 0 COMMENT 'SUM of detail nominals (redundant)';

-- Drop `pengelola` column
ALTER TABLE `ms_petty_cash` DROP COLUMN `pengelola`;

-- Drop `coa` column (data already migrated to ms_petty_cash_detail)
ALTER TABLE `ms_petty_cash` DROP COLUMN `coa`;

-- =============================================================================
-- STEP 5: Update total_budget to reflect sum of detail nominals
-- (In case the distributed amounts don't match the original budget exactly)
-- =============================================================================
UPDATE `ms_petty_cash` h
SET h.`total_budget` = (
    SELECT COALESCE(SUM(d.`nominal`), 0)
    FROM `ms_petty_cash_detail` d
    WHERE d.`petty_cash_id` = h.`id`
);

-- =============================================================================
-- Migration complete!
-- Old structure: ms_petty_cash (id, nama, pengelola, keterangan, coa, budget, approval, ...)
-- New structure: ms_petty_cash (id, nama, keterangan, approver, total_budget, ...)
--              + ms_petty_cash_detail (id, petty_cash_id, coa_code, jenis_pengeluaran, nominal)
-- =============================================================================
