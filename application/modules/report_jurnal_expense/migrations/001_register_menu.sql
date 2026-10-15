-- Register only View access; inherit equivalent Report Jurnal Payment grants.
SET @report_payment_id = (SELECT id FROM menus WHERE link IN ('report_jurnal_payment', 'report_jurnal_payment/index') ORDER BY id LIMIT 1);
SET @report_expense_id = (SELECT id FROM menus WHERE link IN ('report_jurnal_expense', 'report_jurnal_expense/index') ORDER BY id LIMIT 1);
SET @report_parent = (SELECT parent_id FROM menus WHERE id = @report_payment_id);
SET @report_group = (SELECT group_menu FROM menus WHERE id = @report_payment_id);
SET @report_order = (SELECT `order` FROM menus WHERE id = @report_payment_id);

UPDATE menus SET `order` = `order` + 1
WHERE parent_id = @report_parent AND group_menu = @report_group
    AND `order` > @report_order AND @report_expense_id IS NULL;

INSERT INTO menus (title, link, icon, target, group_menu, parent_id, permission_id, status, `order`, created_on, pusat)
SELECT 'Report Jurnal Expense', 'report_jurnal_expense', icon, target, group_menu, parent_id, 0, 1, `order` + 1, NOW(), pusat
FROM menus WHERE id = @report_payment_id AND @report_expense_id IS NULL;

SET @report_expense_id = (SELECT id FROM menus WHERE link IN ('report_jurnal_expense', 'report_jurnal_expense/index') ORDER BY id LIMIT 1);

INSERT INTO permissions (nm_permission, ket, id_menu, nm_menu, created_on)
SELECT 'Report_Jurnal_Expense.View', 'View', @report_expense_id, 'Report Jurnal Expense', NOW()
WHERE @report_expense_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM permissions WHERE nm_permission = 'Report_Jurnal_Expense.View');

UPDATE menus SET permission_id = (SELECT id_permission FROM permissions WHERE nm_permission = 'Report_Jurnal_Expense.View' ORDER BY id_permission LIMIT 1)
WHERE id = @report_expense_id;

INSERT INTO group_permissions (id_group, id_permission)
SELECT source.id_group, expense.id_permission FROM group_permissions source
JOIN permissions payment ON payment.id_permission = source.id_permission AND payment.nm_permission = 'Report_Jurnal_Payment.View'
JOIN permissions expense ON expense.nm_permission = 'Report_Jurnal_Expense.View'
WHERE NOT EXISTS (SELECT 1 FROM group_permissions existing WHERE existing.id_group = source.id_group AND existing.id_permission = expense.id_permission);

INSERT INTO user_permissions (id_user, id_permission)
SELECT source.id_user, expense.id_permission FROM user_permissions source
JOIN permissions payment ON payment.id_permission = source.id_permission AND payment.nm_permission = 'Report_Jurnal_Payment.View'
JOIN permissions expense ON expense.nm_permission = 'Report_Jurnal_Expense.View'
WHERE NOT EXISTS (SELECT 1 FROM user_permissions existing WHERE existing.id_user = source.id_user AND existing.id_permission = expense.id_permission);
