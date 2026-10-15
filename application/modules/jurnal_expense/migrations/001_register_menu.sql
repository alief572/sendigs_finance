-- Idempotent: inherit the location and equivalent grants of Jurnal Payment.
SET @payment_menu_id = (SELECT id FROM menus WHERE link IN ('jurnal_payment', 'jurnal_payment/index') ORDER BY id LIMIT 1);
SET @expense_menu_id = (SELECT id FROM menus WHERE link IN ('jurnal_expense', 'jurnal_expense/index') ORDER BY id LIMIT 1);
SET @payment_parent = (SELECT parent_id FROM menus WHERE id = @payment_menu_id);
SET @payment_group = (SELECT group_menu FROM menus WHERE id = @payment_menu_id);
SET @payment_order = (SELECT `order` FROM menus WHERE id = @payment_menu_id);

UPDATE menus SET `order` = `order` + 1
WHERE parent_id = @payment_parent AND group_menu = @payment_group
    AND `order` > @payment_order AND @expense_menu_id IS NULL;

INSERT INTO menus (title, link, icon, target, group_menu, parent_id, permission_id, status, `order`, created_on, pusat)
SELECT 'Jurnal Expense', 'jurnal_expense', icon, target, group_menu, parent_id, 0, 1, `order` + 1, NOW(), pusat
FROM menus WHERE id = @payment_menu_id AND @expense_menu_id IS NULL;

SET @expense_menu_id = (SELECT id FROM menus WHERE link IN ('jurnal_expense', 'jurnal_expense/index') ORDER BY id LIMIT 1);

INSERT INTO permissions (nm_permission, ket, id_menu, nm_menu, created_on)
SELECT CONCAT('Jurnal_Expense.', actions.action), actions.action, @expense_menu_id, 'Jurnal Expense', NOW()
FROM (SELECT 'View' AS action UNION ALL SELECT 'Add' UNION ALL SELECT 'Manage' UNION ALL SELECT 'Delete') actions
WHERE @expense_menu_id IS NOT NULL AND NOT EXISTS (
    SELECT 1 FROM permissions p WHERE p.nm_permission = CONCAT('Jurnal_Expense.', actions.action)
);

UPDATE menus SET permission_id = (SELECT id_permission FROM permissions WHERE nm_permission = 'Jurnal_Expense.View' ORDER BY id_permission LIMIT 1)
WHERE id = @expense_menu_id;

INSERT INTO group_permissions (id_group, id_permission)
SELECT source.id_group, expense.id_permission
FROM group_permissions source
JOIN permissions payment ON payment.id_permission = source.id_permission AND payment.nm_permission LIKE 'Jurnal\_Payment.%'
JOIN permissions expense ON expense.nm_permission = REPLACE(payment.nm_permission, 'Jurnal_Payment.', 'Jurnal_Expense.')
WHERE NOT EXISTS (SELECT 1 FROM group_permissions existing WHERE existing.id_group = source.id_group AND existing.id_permission = expense.id_permission);

INSERT INTO user_permissions (id_user, id_permission)
SELECT source.id_user, expense.id_permission
FROM user_permissions source
JOIN permissions payment ON payment.id_permission = source.id_permission AND payment.nm_permission LIKE 'Jurnal\_Payment.%'
JOIN permissions expense ON expense.nm_permission = REPLACE(payment.nm_permission, 'Jurnal_Payment.', 'Jurnal_Expense.')
WHERE NOT EXISTS (SELECT 1 FROM user_permissions existing WHERE existing.id_user = source.id_user AND existing.id_permission = expense.id_permission);
