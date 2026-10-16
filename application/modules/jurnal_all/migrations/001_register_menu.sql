-- Run in the ERP database. Registers Jurnal All and Report Jurnal All in sidebar menus.
CREATE TABLE IF NOT EXISTS jurnal_all_menu_backup (
    menu_id INT NOT NULL PRIMARY KEY,
    previous_status TINYINT NULL
) ENGINE=InnoDB;

START TRANSACTION;
SET @ja_anchor = (SELECT id FROM menus WHERE link IN ('jurnal_invoicing','jurnal_invoicing/index') ORDER BY id LIMIT 1);
SET @ra_anchor = (SELECT id FROM menus WHERE link IN ('report_jurnal_invoicing','report_jurnal_invoicing/index') ORDER BY id LIMIT 1);

INSERT INTO menus (title,link,icon,target,group_menu,parent_id,permission_id,status,`order`,created_on,pusat)
SELECT 'Jurnal All','jurnal_all','fa fa-book',target,group_menu,parent_id,0,1,4,NOW(),pusat FROM menus
WHERE id=@ja_anchor AND NOT EXISTS (SELECT 1 FROM menus existing WHERE existing.link='jurnal_all');

INSERT INTO menus (title,link,icon,target,group_menu,parent_id,permission_id,status,`order`,created_on,pusat)
SELECT 'Report Jurnal All','report_jurnal_all','fa fa-book',target,group_menu,parent_id,0,1,17,NOW(),pusat FROM menus
WHERE id=@ra_anchor AND NOT EXISTS (SELECT 1 FROM menus existing WHERE existing.link='report_jurnal_all');

SET @ja_id = (SELECT id FROM menus WHERE link='jurnal_all' ORDER BY id LIMIT 1);
SET @ra_id = (SELECT id FROM menus WHERE link='report_jurnal_all' ORDER BY id LIMIT 1);

INSERT INTO permissions (nm_permission,ket,id_menu,nm_menu,created_on,created_by)
SELECT 'Jurnal_All.View','View',@ja_id,'Jurnal All',NOW(),'7' WHERE @ja_id IS NOT NULL
AND NOT EXISTS (SELECT 1 FROM permissions WHERE nm_permission='Jurnal_All.View');

INSERT INTO permissions (nm_permission,ket,id_menu,nm_menu,created_on,created_by)
SELECT 'Jurnal_All.Manage','Manage',@ja_id,'Jurnal All',NOW(),'7' WHERE @ja_id IS NOT NULL
AND NOT EXISTS (SELECT 1 FROM permissions WHERE nm_permission='Jurnal_All.Manage');

INSERT INTO permissions (nm_permission,ket,id_menu,nm_menu,created_on,created_by)
SELECT 'Report_Jurnal_All.View','View',@ra_id,'Report Jurnal All',NOW(),'7' WHERE @ra_id IS NOT NULL
AND NOT EXISTS (SELECT 1 FROM permissions WHERE nm_permission='Report_Jurnal_All.View');

UPDATE permissions SET id_menu=@ja_id, nm_menu='Jurnal All' WHERE nm_permission IN ('Jurnal_All.View', 'Jurnal_All.Manage');
UPDATE permissions SET id_menu=@ra_id, nm_menu='Report Jurnal All' WHERE nm_permission='Report_Jurnal_All.View';

UPDATE menus SET title='Jurnal All', permission_id=(SELECT MIN(id_permission) FROM permissions WHERE nm_permission='Jurnal_All.View'),status=1,`order`=4 WHERE id=@ja_id;
UPDATE menus SET title='Report Jurnal All', permission_id=(SELECT MIN(id_permission) FROM permissions WHERE nm_permission='Report_Jurnal_All.View'),status=1,`order`=17 WHERE id=@ra_id;

-- Deactivate legacy single-table 'jurnal'
UPDATE menus SET status=0 WHERE link IN ('jurnal', 'jurnal/index');

-- Backup and deactivate individual legacy journal menus
INSERT IGNORE INTO jurnal_all_menu_backup (menu_id, previous_status)
SELECT id, status FROM menus
WHERE link IN (
    'jurnal_invoicing', 'jurnal_invoicing/index',
    'jurnal_penerimaan', 'jurnal_penerimaan/index',
    'jurnal_payment', 'jurnal_payment/index',
    'jurnal_payment_petty_cash', 'jurnal_payment_petty_cash/index',
    'jurnal_expense', 'jurnal_expense/index',
    'report_jurnal_invoicing', 'report_jurnal_invoicing/index',
    'report_jurnal_penerimaan', 'report_jurnal_penerimaan/index',
    'report_jurnal_payment', 'report_jurnal_payment/index',
    'report_jurnal_expense', 'report_jurnal_expense/index'
);

UPDATE menus SET status=0 WHERE link IN (
    'jurnal_invoicing', 'jurnal_invoicing/index',
    'jurnal_penerimaan', 'jurnal_penerimaan/index',
    'jurnal_payment', 'jurnal_payment/index',
    'jurnal_payment_petty_cash', 'jurnal_payment_petty_cash/index',
    'jurnal_expense', 'jurnal_expense/index',
    'report_jurnal_invoicing', 'report_jurnal_invoicing/index',
    'report_jurnal_penerimaan', 'report_jurnal_penerimaan/index',
    'report_jurnal_payment', 'report_jurnal_payment/index',
    'report_jurnal_expense', 'report_jurnal_expense/index'
);

-- Grant permissions to users who already have Jurnal_Invoicing permissions
INSERT IGNORE INTO user_permissions (id_user, id_permission)
SELECT up.id_user, p_new.id_permission
FROM user_permissions up
JOIN permissions p_inv ON p_inv.id_permission = up.id_permission AND p_inv.nm_permission = 'Jurnal_Invoicing.View'
CROSS JOIN permissions p_new WHERE p_new.nm_permission IN ('Jurnal_All.View', 'Jurnal_All.Manage', 'Report_Jurnal_All.View');

-- Also ensure user 7 (Admin) has explicit user_permissions
INSERT IGNORE INTO user_permissions (id_user, id_permission)
SELECT 7, id_permission FROM permissions WHERE nm_permission IN ('Jurnal_All.View', 'Jurnal_All.Manage', 'Report_Jurnal_All.View');

COMMIT;
