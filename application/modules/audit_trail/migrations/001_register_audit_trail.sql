-- Run on the intended application database after reviewing its target.
-- Idempotent: one read-only permission and one Settings submenu.
START TRANSACTION;
SET @audit_parent = NULL, @audit_group = NULL, @audit_order = NULL;
SELECT p.parent_id, p.group_menu, COALESCE(p.`order`, 0) + 1
INTO @audit_parent, @audit_group, @audit_order
FROM menus p JOIN menus s ON s.id = p.parent_id
WHERE s.title = 'Settings' AND p.link IN ('permissions', 'permissions/index')
ORDER BY p.id LIMIT 1;

INSERT INTO permissions (nm_permission, ket, nm_menu, created_on)
SELECT 'Audit_Trail.View', 'View', 'Audit Trail', NOW()
FROM DUAL WHERE @audit_parent IS NOT NULL
AND NOT EXISTS (SELECT 1 FROM permissions WHERE nm_permission = 'Audit_Trail.View');
SET @audit_permission = (SELECT MIN(id_permission) FROM permissions WHERE nm_permission = 'Audit_Trail.View');
SET @audit_menu = (SELECT MIN(id) FROM menus WHERE link IN ('audit_trail', 'audit_trail/index'));
SET @audit_positioned = (SELECT COUNT(*) FROM menus WHERE id = @audit_menu AND parent_id = @audit_parent AND `order` = @audit_order);

UPDATE menus SET `order` = `order` + 1
WHERE parent_id = @audit_parent AND group_menu = @audit_group AND `order` >= @audit_order
AND (id != @audit_menu OR @audit_menu IS NULL) AND @audit_positioned = 0;

INSERT INTO menus (title, link, icon, target, group_menu, parent_id, permission_id, status, `order`, created_on, pusat)
SELECT 'Audit Trail', 'audit_trail', 'fa fa-history', 'sametab', @audit_group, @audit_parent, @audit_permission, 1, @audit_order, NOW(), 0
FROM DUAL WHERE @audit_parent IS NOT NULL AND @audit_menu IS NULL;
SET @audit_menu = (SELECT MIN(id) FROM menus WHERE link IN ('audit_trail', 'audit_trail/index'));
UPDATE menus SET title = 'Audit Trail', link = 'audit_trail', icon = 'fa fa-history', parent_id = @audit_parent,
group_menu = @audit_group, permission_id = @audit_permission, status = 1, `order` = @audit_order
WHERE id = @audit_menu AND @audit_parent IS NOT NULL;
UPDATE permissions SET id_menu = @audit_menu, nm_menu = 'Audit Trail'
WHERE id_permission = @audit_permission AND @audit_menu IS NOT NULL;
COMMIT;
SELECT IF(@audit_parent IS NULL, 'SKIPPED: Settings / Permissions menu not found', 'Audit Trail menu registered') AS result;
