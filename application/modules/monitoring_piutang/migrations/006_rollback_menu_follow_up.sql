-- Rollback 005_register_menu_follow_up.sql
START TRANSACTION;
DELETE up FROM user_permissions up
JOIN permissions p ON p.id_permission = up.id_permission AND p.nm_permission IN ('Follow_Up_Piutang.View','Follow_Up_Piutang.Manage');
DELETE FROM permissions WHERE nm_permission IN ('Follow_Up_Piutang.View','Follow_Up_Piutang.Manage');
DELETE FROM menus WHERE link = 'follow_up_piutang';
COMMIT;
