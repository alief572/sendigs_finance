-- REGISTRASI SIDEBAR: EMPAT MODUL PENAMPUNG ALOKASI
-- Siap dijalankan pada database ERP production yang dipilih pengguna.
-- Jangan jalankan pada database accounting/GL. Tidak ada USE atau ID menu tetap.
-- Pastikan kode empat modul sudah tersedia di server aplikasi.
-- Simpan backup menus, permissions, group_permissions dan user_permissions sebelum import.
-- Gunakan klien SQL yang BERHENTI saat error; jangan gunakan --force.
-- Jalankan seluruh file dalam satu koneksi. Tabel metadata harus memakai InnoDB.
--
-- Menu dibuat setelah Alokasi Kalibrasi, mengikuti parent/group/target referensi:
--   Alokasi Pengembalian Kasbon  -> alokasi_pengembalian_kasbon
--   Alokasi Mutasi               -> alokasi_mutasi
--   Alokasi Transaksi Bank       -> alokasi_transaksi_bank
--   Alokasi Pembayaran           -> alokasi_pembayaran
-- Akses diwarisi dari pengguna/grup yang dapat membuka sidebar DAN halaman
-- Alokasi Kalibrasi (permission menu referensi dan Alokasi.View).
-- Idempoten: pengulangan tidak menduplikasi menu, permission, grant atau urutan.
-- Tidak mengubah data transaksi/alokasi. Query terakhir menampilkan hasil registrasi.
SET @ap_anchor = (SELECT id FROM menus WHERE link IN ('alokasi_kalibrasi', 'alokasi_kalibrasi/index') ORDER BY id LIMIT 1);
SET @ap_permission = (SELECT permission_id FROM menus WHERE id = @ap_anchor);
SET @ap_controller = (SELECT id_permission FROM permissions WHERE nm_permission = 'Alokasi.View' ORDER BY id_permission LIMIT 1);
SET @ap_parent = (SELECT parent_id FROM menus WHERE id = @ap_anchor);
SET @ap_group = (SELECT group_menu FROM menus WHERE id = @ap_anchor);
SET @ap_order = (SELECT `order` FROM menus WHERE id = @ap_anchor);
SET @ap_icon = (SELECT icon FROM menus WHERE id = @ap_anchor);
SET @ap_target = (SELECT target FROM menus WHERE id = @ap_anchor);
SET @ap_pusat = (SELECT pusat FROM menus WHERE id = @ap_anchor);

-- Duplicate-key guard fails even without strict SQL mode, before persistent writes.
CREATE TEMPORARY TABLE ap_reference_guard (id INT PRIMARY KEY);
INSERT INTO ap_reference_guard VALUES (1);
INSERT INTO ap_reference_guard SELECT 1
WHERE @ap_anchor IS NULL OR @ap_order IS NULL OR @ap_controller IS NULL
   OR NOT EXISTS (SELECT 1 FROM permissions WHERE id_permission = @ap_permission);
DROP TEMPORARY TABLE ap_reference_guard;

CREATE TEMPORARY TABLE ap_targets (position INT, title VARCHAR(100), link VARCHAR(100), permission_name VARCHAR(100));
INSERT INTO ap_targets VALUES
    (1, 'Alokasi Pengembalian Kasbon', 'alokasi_pengembalian_kasbon', 'Alokasi_Pengembalian_Kasbon.View'),
    (2, 'Alokasi Mutasi', 'alokasi_mutasi', 'Alokasi_Mutasi.View'),
    (3, 'Alokasi Transaksi Bank', 'alokasi_transaksi_bank', 'Alokasi_Transaksi_Bank.View'),
    (4, 'Alokasi Pembayaran', 'alokasi_pembayaran', 'Alokasi_Pembayaran.View');

CREATE TEMPORARY TABLE ap_effective_users AS
    SELECT up.id_user, up.id_permission FROM user_permissions up WHERE up.id_permission IN (@ap_permission, @ap_controller)
    UNION
    SELECT ug.id_user, gp.id_permission FROM user_groups ug JOIN group_permissions gp ON gp.id_group = ug.id_group
    WHERE gp.id_permission IN (@ap_permission, @ap_controller);
CREATE TEMPORARY TABLE ap_eligible_users AS
    SELECT id_user FROM ap_effective_users GROUP BY id_user
    HAVING COUNT(DISTINCT id_permission) = IF(@ap_permission = @ap_controller, 1, 2);
CREATE TEMPORARY TABLE ap_eligible_groups AS
    SELECT id_group FROM group_permissions WHERE id_permission IN (@ap_permission, @ap_controller)
    GROUP BY id_group HAVING COUNT(DISTINCT id_permission) = IF(@ap_permission = @ap_controller, 1, 2);

START TRANSACTION;
SET @ap_missing = (SELECT COUNT(*) FROM ap_targets t WHERE NOT EXISTS (SELECT 1 FROM menus m WHERE m.link IN (t.link, CONCAT(t.link, '/index'))));
UPDATE menus m SET m.`order` = m.`order` + @ap_missing
WHERE m.parent_id = @ap_parent AND m.group_menu = @ap_group AND m.`order` > @ap_order
    AND NOT EXISTS (SELECT 1 FROM ap_targets t WHERE m.link IN (t.link, CONCAT(t.link, '/index')));

INSERT INTO menus (title, link, icon, target, group_menu, parent_id, permission_id, status, `order`, created_on, pusat)
SELECT t.title, t.link, @ap_icon, @ap_target, @ap_group, @ap_parent, 0, 1, @ap_order + t.position, NOW(), @ap_pusat
FROM ap_targets t WHERE NOT EXISTS (SELECT 1 FROM menus m WHERE m.link IN (t.link, CONCAT(t.link, '/index')));

INSERT INTO permissions (nm_permission, ket, id_menu, nm_menu, created_on)
SELECT t.permission_name, 'View', m.id, t.title, NOW()
FROM ap_targets t JOIN menus m ON m.link IN (t.link, CONCAT(t.link, '/index'))
WHERE NOT EXISTS (SELECT 1 FROM permissions p WHERE p.nm_permission = t.permission_name);

UPDATE menus m JOIN ap_targets t ON m.link IN (t.link, CONCAT(t.link, '/index'))
JOIN permissions p ON p.nm_permission = t.permission_name
SET m.title = t.title, m.permission_id = p.id_permission, m.status = 1, m.parent_id = @ap_parent,
    m.group_menu = @ap_group, m.`order` = @ap_order + t.position, m.icon = @ap_icon, m.target = @ap_target, m.pusat = @ap_pusat;

INSERT INTO group_permissions (id_group, id_permission)
SELECT g.id_group, p.id_permission FROM ap_eligible_groups g CROSS JOIN ap_targets t
JOIN permissions p ON p.nm_permission = t.permission_name
WHERE NOT EXISTS (SELECT 1 FROM group_permissions old WHERE old.id_group = g.id_group AND old.id_permission = p.id_permission);

INSERT INTO user_permissions (id_user, id_permission)
SELECT u.id_user, p.id_permission FROM ap_eligible_users u CROSS JOIN ap_targets t
JOIN permissions p ON p.nm_permission = t.permission_name
WHERE NOT EXISTS (SELECT 1 FROM user_permissions old WHERE old.id_user = u.id_user AND old.id_permission = p.id_permission)
    AND NOT EXISTS (SELECT 1 FROM user_groups ug JOIN group_permissions gp ON gp.id_group = ug.id_group
        WHERE ug.id_user = u.id_user AND gp.id_permission = p.id_permission);
COMMIT;

DROP TEMPORARY TABLE ap_eligible_groups;
DROP TEMPORARY TABLE ap_eligible_users;
DROP TEMPORARY TABLE ap_effective_users;
DROP TEMPORARY TABLE ap_targets;

-- VERIFIKASI: harus menghasilkan empat baris aktif beserta View permission-nya.
SELECT m.title AS menu, m.link AS route, m.status AS aktif,
       m.parent_id, m.group_menu, m.`order` AS urutan, p.nm_permission AS permission
FROM menus m
JOIN permissions p ON p.id_permission = m.permission_id
WHERE m.link IN (
    'alokasi_pengembalian_kasbon', 'alokasi_pengembalian_kasbon/index',
    'alokasi_mutasi', 'alokasi_mutasi/index',
    'alokasi_transaksi_bank', 'alokasi_transaksi_bank/index',
    'alokasi_pembayaran', 'alokasi_pembayaran/index'
)
ORDER BY m.`order`, m.id;
