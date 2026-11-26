-- Adds Record Payment below the same parent as Record Request Payment.
-- Reuses the permission that the controller checks. No user or role grants are changed.
INSERT INTO menus (title, link, icon, target, group_menu, parent_id, permission_id, status, `order`, created_on, pusat)
SELECT 'Record Payment', 'record_request_payment/record_payment', 'fa fa-angle-right',
       NULL, m.group_menu, m.parent_id, p.id_permission, 1, m.`order` + 1, NOW(), m.pusat
FROM menus m
JOIN permissions p ON p.nm_permission='Record_Request_Payment.View'
WHERE m.link IN ('record_request_payment/index', 'record_request_payment')
  AND NOT EXISTS (SELECT 1 FROM menus existing WHERE existing.link='record_request_payment/record_payment')
ORDER BY m.id, p.id_permission LIMIT 1;

SELECT m.id, m.title, m.link, m.parent_id, p.nm_permission
FROM menus m JOIN permissions p ON p.id_permission=m.permission_id
WHERE m.link='record_request_payment/record_payment';
