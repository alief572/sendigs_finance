-- Apply before deploying multi-attachment kasbon support. Existing values are preserved.
ALTER TABLE tr_kasbon MODIFY COLUMN doc_file TEXT NULL DEFAULT NULL;
