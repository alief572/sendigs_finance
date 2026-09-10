-- Registrasi menu untuk 3 modul Request Payment (alur baru).
-- Menu "Request Payment" (request_payment/index) sudah ada (id 293) -> tetap dipakai.
-- Tambahkan menu Approval & Record di bawah parent 298 (Pengeluaran Non Pembelian),
-- permission_id 983 (Request_Payment.View), group_menu 1. Idempotent.

INSERT INTO menus (title, link, icon, target, group_menu, parent_id, permission_id, status, `order`, created_on, pusat)
SELECT 'Approval Request Payment', 'approval_request_payment/index', 'fa fa-angle-right', NULL, 1, 298, 983, 1, 10, NOW(), 0
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM menus WHERE link = 'approval_request_payment/index');

INSERT INTO menus (title, link, icon, target, group_menu, parent_id, permission_id, status, `order`, created_on, pusat)
SELECT 'Record Request Payment', 'record_request_payment/index', 'fa fa-angle-right', NULL, 1, 298, 983, 1, 11, NOW(), 0
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM menus WHERE link = 'record_request_payment/index');
