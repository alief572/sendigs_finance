-- Run in the database that holds `menus` / `permissions` (same as jurnal_all/migrations/001_register_menu.sql).
-- Registers "Monitoring Piutang" next to "Report Piutang Per Invoice" and grants it to
-- users who can already view Report Piutang Per Invoice, plus admin (user 7).
START TRANSACTION;
SET @mp_anchor = (SELECT id FROM menus WHERE link IN ('report_piutang_per_invoice','report_piutang_per_invoice/index') ORDER BY id LIMIT 1);

INSERT INTO menus (title,link,icon,target,group_menu,parent_id,permission_id,status,`order`,created_on,pusat)
SELECT 'Monitoring Piutang','monitoring_piutang','fa fa-line-chart',target,group_menu,parent_id,0,1,`order`+1,NOW(),pusat FROM menus
WHERE id=@mp_anchor AND NOT EXISTS (SELECT 1 FROM menus existing WHERE existing.link='monitoring_piutang');

SET @mp_id = (SELECT id FROM menus WHERE link='monitoring_piutang' ORDER BY id LIMIT 1);

INSERT INTO permissions (nm_permission,ket,id_menu,nm_menu,created_on,created_by)
SELECT 'Monitoring_Piutang.View','View',@mp_id,'Monitoring Piutang',NOW(),'7' WHERE @mp_id IS NOT NULL
AND NOT EXISTS (SELECT 1 FROM permissions WHERE nm_permission='Monitoring_Piutang.View');

UPDATE menus SET permission_id=(SELECT MIN(id_permission) FROM permissions WHERE nm_permission='Monitoring_Piutang.View'), status=1
WHERE id=@mp_id;

INSERT IGNORE INTO user_permissions (id_user, id_permission)
SELECT up.id_user, p_new.id_permission
FROM user_permissions up
JOIN permissions p_old ON p_old.id_permission = up.id_permission AND p_old.nm_permission = 'Report_Piutang_Per_Invoice.View'
CROSS JOIN permissions p_new WHERE p_new.nm_permission = 'Monitoring_Piutang.View';

INSERT IGNORE INTO user_permissions (id_user, id_permission)
SELECT 7, id_permission FROM permissions WHERE nm_permission = 'Monitoring_Piutang.View';
COMMIT;
