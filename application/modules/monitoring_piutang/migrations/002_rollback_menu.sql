-- Rollback 001_register_menu.sql
START TRANSACTION;
DELETE up FROM user_permissions up
JOIN permissions p ON p.id_permission = up.id_permission AND p.nm_permission = 'Monitoring_Piutang.View';
DELETE FROM permissions WHERE nm_permission = 'Monitoring_Piutang.View';
DELETE FROM menus WHERE link = 'monitoring_piutang';
COMMIT;
