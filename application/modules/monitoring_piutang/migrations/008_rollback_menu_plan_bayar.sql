-- Rollback 007_register_menu_plan_bayar.sql
START TRANSACTION;
DELETE up FROM user_permissions up
JOIN permissions p ON p.id_permission = up.id_permission AND p.nm_permission = 'Dashboard_Plan_Bayar.View';
DELETE FROM permissions WHERE nm_permission = 'Dashboard_Plan_Bayar.View';
DELETE FROM menus WHERE link = 'follow_up_piutang/plan_bayar';
COMMIT;
