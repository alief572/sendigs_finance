-- Run in the database that holds `menus` / `permissions` (same as 001_register_menu.sql).
-- Registers "Follow Up Piutang" (modul kerja finance AR) right after "Monitoring Piutang".
-- View  : diberikan ke pemegang Monitoring_Piutang.View + admin (user 7).
-- Manage: (update pengiriman/terima/FU/plan bayar, close invoice) hanya admin (user 7);
--         user finance AR diberi lewat menu permission setelah ditentukan.
START TRANSACTION;
SET @fu_anchor = (SELECT id FROM menus WHERE link='monitoring_piutang' ORDER BY id LIMIT 1);

INSERT INTO menus (title,link,icon,target,group_menu,parent_id,permission_id,status,`order`,created_on,pusat)
SELECT 'Follow Up Piutang','follow_up_piutang','fa fa-phone',target,group_menu,parent_id,0,1,`order`+1,NOW(),pusat FROM menus
WHERE id=@fu_anchor AND NOT EXISTS (SELECT 1 FROM menus existing WHERE existing.link='follow_up_piutang');

SET @fu_id = (SELECT id FROM menus WHERE link='follow_up_piutang' ORDER BY id LIMIT 1);

INSERT INTO permissions (nm_permission,ket,id_menu,nm_menu,created_on,created_by)
SELECT 'Follow_Up_Piutang.View','View',@fu_id,'Follow Up Piutang',NOW(),'7' WHERE @fu_id IS NOT NULL
AND NOT EXISTS (SELECT 1 FROM permissions WHERE nm_permission='Follow_Up_Piutang.View');

INSERT INTO permissions (nm_permission,ket,id_menu,nm_menu,created_on,created_by)
SELECT 'Follow_Up_Piutang.Manage','Manage',@fu_id,'Follow Up Piutang',NOW(),'7' WHERE @fu_id IS NOT NULL
AND NOT EXISTS (SELECT 1 FROM permissions WHERE nm_permission='Follow_Up_Piutang.Manage');

UPDATE menus SET permission_id=(SELECT MIN(id_permission) FROM permissions WHERE nm_permission='Follow_Up_Piutang.View'), status=1
WHERE id=@fu_id;

INSERT IGNORE INTO user_permissions (id_user, id_permission)
SELECT up.id_user, p_new.id_permission
FROM user_permissions up
JOIN permissions p_old ON p_old.id_permission = up.id_permission AND p_old.nm_permission = 'Monitoring_Piutang.View'
CROSS JOIN permissions p_new WHERE p_new.nm_permission = 'Follow_Up_Piutang.View';

INSERT IGNORE INTO user_permissions (id_user, id_permission)
SELECT 7, id_permission FROM permissions WHERE nm_permission IN ('Follow_Up_Piutang.View','Follow_Up_Piutang.Manage');
COMMIT;
