-- Run in the database that holds `menus` (same as 001/005/007). Rollback: 030_rollback_reorder_menu_piutang.sql.
-- Urutan sidebar Finance: Monitoring Piutang, Follow Up Piutang, Dashboard Plan Bayar, tepat setelah menu dengan order terbesar lain (Report Jurnal All).
-- Idempotent: nilai order dihitung dari saudara menu selain ketiganya.
SET @p = (SELECT parent_id FROM menus WHERE link = 'report_jurnal_all' ORDER BY id LIMIT 1);
SET @mx = (SELECT MAX(`order`) FROM menus WHERE parent_id = @p AND link NOT IN ('monitoring_piutang', 'follow_up_piutang', 'follow_up_piutang/plan_bayar'));
UPDATE menus SET `order` = @mx + 1 WHERE parent_id = @p AND link = 'monitoring_piutang';
UPDATE menus SET `order` = @mx + 2 WHERE parent_id = @p AND link = 'follow_up_piutang';
UPDATE menus SET `order` = @mx + 3 WHERE parent_id = @p AND link = 'follow_up_piutang/plan_bayar';
