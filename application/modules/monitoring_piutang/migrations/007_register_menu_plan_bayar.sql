-- Run in the database that holds `menus` / `permissions` (same as 001 / 005).
-- Registers "Dashboard Plan Bayar" (12 card bulan plan bayar + rincian invoice) right after "Follow Up Piutang".
-- View diberikan ke pemegang Follow_Up_Piutang.View + admin (user 7).
START TRANSACTION;
SET @pb_anchor = (SELECT id FROM menus WHERE link='follow_up_piutang' ORDER BY id LIMIT 1);

INSERT INTO menus (title,link,icon,target,group_menu,parent_id,permission_id,status,`order`,created_on,pusat)
SELECT 'Dashboard Plan Bayar','follow_up_piutang/plan_bayar','fa fa-calendar',target,group_menu,parent_id,0,1,`order`+1,NOW(),pusat FROM menus
WHERE id=@pb_anchor AND NOT EXISTS (SELECT 1 FROM menus existing WHERE existing.link='follow_up_piutang/plan_bayar');

SET @pb_id = (SELECT id FROM menus WHERE link='follow_up_piutang/plan_bayar' ORDER BY id LIMIT 1);

INSERT INTO permissions (nm_permission,ket,id_menu,nm_menu,created_on,created_by)
SELECT 'Dashboard_Plan_Bayar.View','View',@pb_id,'Dashboard Plan Bayar',NOW(),'7' WHERE @pb_id IS NOT NULL
AND NOT EXISTS (SELECT 1 FROM permissions WHERE nm_permission='Dashboard_Plan_Bayar.View');

UPDATE menus SET permission_id=(SELECT MIN(id_permission) FROM permissions WHERE nm_permission='Dashboard_Plan_Bayar.View'), status=1
WHERE id=@pb_id;

INSERT IGNORE INTO user_permissions (id_user, id_permission)
SELECT up.id_user, p_new.id_permission
FROM user_permissions up
JOIN permissions p_old ON p_old.id_permission = up.id_permission AND p_old.nm_permission = 'Follow_Up_Piutang.View'
CROSS JOIN permissions p_new WHERE p_new.nm_permission = 'Dashboard_Plan_Bayar.View';

INSERT IGNORE INTO user_permissions (id_user, id_permission)
SELECT 7, id_permission FROM permissions WHERE nm_permission = 'Dashboard_Plan_Bayar.View';
COMMIT;
