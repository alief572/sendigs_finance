-- Run in the ERP database. Keep previous menu visibility for a reversible cutover.
CREATE TABLE IF NOT EXISTS jurnal_all_menu_backup (
    menu_id INT NOT NULL PRIMARY KEY,
    previous_status TINYINT NULL
) ENGINE=InnoDB;

START TRANSACTION;
SET @ja_anchor = (SELECT id FROM menus WHERE link IN ('jurnal_invoicing','jurnal_invoicing/index') ORDER BY id LIMIT 1);
SET @ra_anchor = (SELECT id FROM menus WHERE link IN ('report_jurnal_invoicing','report_jurnal_invoicing/index') ORDER BY id LIMIT 1);

INSERT INTO menus (title,link,icon,target,group_menu,parent_id,permission_id,status,`order`,created_on,pusat)
SELECT 'Jurnal','jurnal_all','fa fa-book',target,group_menu,parent_id,0,1,`order`,NOW(),pusat FROM menus
WHERE id=@ja_anchor AND NOT EXISTS (SELECT 1 FROM menus existing WHERE existing.link='jurnal_all');
INSERT INTO menus (title,link,icon,target,group_menu,parent_id,permission_id,status,`order`,created_on,pusat)
SELECT 'Report Jurnal','report_jurnal_all','fa fa-book',target,group_menu,parent_id,0,1,`order`,NOW(),pusat FROM menus
WHERE id=@ra_anchor AND NOT EXISTS (SELECT 1 FROM menus existing WHERE existing.link='report_jurnal_all');

SET @ja_id = (SELECT id FROM menus WHERE link='jurnal_all' ORDER BY id LIMIT 1);
SET @ra_id = (SELECT id FROM menus WHERE link='report_jurnal_all' ORDER BY id LIMIT 1);

INSERT INTO permissions (nm_permission,ket,id_menu,nm_menu,created_on)
SELECT 'Jurnal_All.View','View',@ja_id,'Jurnal',NOW() WHERE @ja_id IS NOT NULL
AND NOT EXISTS (SELECT 1 FROM permissions WHERE nm_permission='Jurnal_All.View');
INSERT INTO permissions (nm_permission,ket,id_menu,nm_menu,created_on)
SELECT 'Jurnal_All.Manage','Manage',@ja_id,'Jurnal',NOW() WHERE @ja_id IS NOT NULL
AND NOT EXISTS (SELECT 1 FROM permissions WHERE nm_permission='Jurnal_All.Manage');
INSERT INTO permissions (nm_permission,ket,id_menu,nm_menu,created_on)
SELECT 'Report_Jurnal_All.View','View',@ra_id,'Report Jurnal',NOW() WHERE @ra_id IS NOT NULL
AND NOT EXISTS (SELECT 1 FROM permissions WHERE nm_permission='Report_Jurnal_All.View');

UPDATE menus SET permission_id=(SELECT MIN(id_permission) FROM permissions WHERE nm_permission='Jurnal_All.View'),status=1 WHERE id=@ja_id;
UPDATE menus SET permission_id=(SELECT MIN(id_permission) FROM permissions WHERE nm_permission='Report_Jurnal_All.View'),status=1 WHERE id=@ra_id;

INSERT IGNORE INTO jurnal_all_menu_backup (menu_id,previous_status)
SELECT id,status FROM menus WHERE
(@ja_id IS NOT NULL AND link IN ('jurnal_invoicing','jurnal_invoicing/index','jurnal_penerimaan','jurnal_penerimaan/index','jurnal_payment','jurnal_payment/index','jurnal_expense','jurnal_expense/index','jurnal_payment_petty_cash','jurnal_payment_petty_cash/index'))
OR (@ra_id IS NOT NULL AND link IN ('report_jurnal_invoicing','report_jurnal_invoicing/index','report_jurnal_penerimaan','report_jurnal_penerimaan/index','report_jurnal_payment','report_jurnal_payment/index','report_jurnal_expense','report_jurnal_expense/index'));

UPDATE menus m JOIN jurnal_all_menu_backup b ON b.menu_id=m.id SET m.status=0;
-- Admin already bypasses permission checks. Do not grant aggregate permissions to other roles/users.
COMMIT;
