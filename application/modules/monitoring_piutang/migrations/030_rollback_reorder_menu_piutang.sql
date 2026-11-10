-- Rollback 029_reorder_menu_piutang.sql: kembalikan order ke nilai awal (12, 13, 14) di bawah menu induk yang sama.
SET @p = (SELECT parent_id FROM menus WHERE link = 'report_jurnal_all' ORDER BY id LIMIT 1);
UPDATE menus SET `order` = 12 WHERE parent_id = @p AND link = 'monitoring_piutang';
UPDATE menus SET `order` = 13 WHERE parent_id = @p AND link = 'follow_up_piutang';
UPDATE menus SET `order` = 14 WHERE parent_id = @p AND link = 'follow_up_piutang/plan_bayar';
