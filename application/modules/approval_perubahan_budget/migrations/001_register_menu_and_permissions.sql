-- =====================================================================
-- Registrasi menu "Approval Perubahan Budget" dan permission-nya.
-- Target DB : DBERP (db_sendigs_ss)
-- Date      : 2026-11-05
--
-- Menu ditempatkan di parent dan group yang sama dengan menu "Approval Pengajuan Periodik"
-- (link pengajuan_rutin/app_list...). Bila menu itu tidak ditemukan, tidak ada yang dibuat:
-- buat lewat layar Menus (permission View/Add/Manage/Delete ikut dibuat otomatis).
-- Idempotent. Setelah dijalankan, beri izin Approval_Perubahan_Budget.View ke pengaju/pelihat dan
-- Approval_Perubahan_Budget.Manage ke grup Direktur lewat layar hak akses grup.
-- Catatan: dijalankan manual (tidak ada migration runner).
-- =====================================================================

INSERT INTO menus (title, link, icon, target, group_menu, parent_id, permission_id, status, `order`, created_on, pusat)
SELECT 'Approval Perubahan Budget', 'approval_perubahan_budget', 'fa fa-angle-right', NULL, a.group_menu, a.parent_id, 0, 1, a.`order` + 1, NOW(), 0
FROM menus a
WHERE a.link LIKE 'pengajuan_rutin/app_list%'
  AND NOT EXISTS (SELECT 1 FROM menus WHERE link = 'approval_perubahan_budget')
LIMIT 1;

INSERT INTO permissions (nm_permission, id_menu, nm_menu, ket, created_on)
SELECT 'Approval_Perubahan_Budget.View', m.id, m.title, 'View', NOW() FROM menus m
WHERE m.link = 'approval_perubahan_budget'
  AND NOT EXISTS (SELECT 1 FROM permissions WHERE nm_permission = 'Approval_Perubahan_Budget.View');

INSERT INTO permissions (nm_permission, id_menu, nm_menu, ket, created_on)
SELECT 'Approval_Perubahan_Budget.Add', m.id, m.title, 'Add', NOW() FROM menus m
WHERE m.link = 'approval_perubahan_budget'
  AND NOT EXISTS (SELECT 1 FROM permissions WHERE nm_permission = 'Approval_Perubahan_Budget.Add');

INSERT INTO permissions (nm_permission, id_menu, nm_menu, ket, created_on)
SELECT 'Approval_Perubahan_Budget.Manage', m.id, m.title, 'Manage', NOW() FROM menus m
WHERE m.link = 'approval_perubahan_budget'
  AND NOT EXISTS (SELECT 1 FROM permissions WHERE nm_permission = 'Approval_Perubahan_Budget.Manage');

INSERT INTO permissions (nm_permission, id_menu, nm_menu, ket, created_on)
SELECT 'Approval_Perubahan_Budget.Delete', m.id, m.title, 'Delete', NOW() FROM menus m
WHERE m.link = 'approval_perubahan_budget'
  AND NOT EXISTS (SELECT 1 FROM permissions WHERE nm_permission = 'Approval_Perubahan_Budget.Delete');

UPDATE menus
SET permission_id = (SELECT id_permission FROM permissions WHERE nm_permission = 'Approval_Perubahan_Budget.View' LIMIT 1)
WHERE link = 'approval_perubahan_budget' AND permission_id = 0;
