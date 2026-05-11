-- Migration: 002_alter_time_to_datetime.sql
-- Description: Mengubah kolom start_time dan finish_time dari TIME ke DATETIME
-- Date: 2025-01-15
-- Requirements: 1.1, 1.2, 1.3, 1.4, 1.5

-- Step 1: Add temporary columns to preserve old TIME data
ALTER TABLE lk_visit_header ADD COLUMN start_time_old TIME NULL AFTER start_time;
ALTER TABLE lk_visit_header ADD COLUMN finish_time_old TIME NULL AFTER finish_time;

-- Step 2: Backup old TIME values to temporary columns
UPDATE lk_visit_header SET start_time_old = start_time WHERE start_time IS NOT NULL;
UPDATE lk_visit_header SET finish_time_old = finish_time WHERE finish_time IS NOT NULL;

-- Step 3: Alter columns from TIME to DATETIME
ALTER TABLE lk_visit_header MODIFY COLUMN start_time DATETIME NULL;
ALTER TABLE lk_visit_header MODIFY COLUMN finish_time DATETIME NULL;

-- Step 4: Migrate existing data - combine visit_date + old TIME → DATETIME
UPDATE lk_visit_header 
SET start_time = CONCAT(visit_date, ' ', start_time_old)
WHERE start_time_old IS NOT NULL AND visit_date IS NOT NULL;

UPDATE lk_visit_header 
SET finish_time = CONCAT(visit_date, ' ', finish_time_old)
WHERE finish_time_old IS NOT NULL AND visit_date IS NOT NULL;

-- Step 5: Drop temporary columns
ALTER TABLE lk_visit_header DROP COLUMN start_time_old;
ALTER TABLE lk_visit_header DROP COLUMN finish_time_old;

-- Rollback SQL (lossy - loses date component):
-- ALTER TABLE lk_visit_header MODIFY COLUMN start_time TIME NULL;
-- ALTER TABLE lk_visit_header MODIFY COLUMN finish_time TIME NULL;
