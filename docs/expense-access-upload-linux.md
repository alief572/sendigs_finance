# Akses Expense dan folder upload Linux

## Perilaku aplikasi

- Ketiga tab `expense/index` membatasi data user biasa berdasarkan `tr_expense.nama = username login`, termasuk count total/filter dan badge. Akses semua pemohon menggunakan `Auth::is_admin()` atau permission `Expense_Approval.Manage`. `Expense.Manage`, ID akun tertentu, dan parameter `all` tidak memberi pengecualian.
- Approval finance dan management mengambil antrean lintas pemohon menurut tahap existing. Pengecualian kategori tetap berlaku: management mengecualikan petty cash; invoice PO/material mengikuti modul asal. Routing tombol dan aturan self-approval sebelumnya dipertahankan.
- Seluruh upload di controller Expense, termasuk kasbon, transport, detail multi-file, dan salinan PR, memakai direktori absolut di bawah `FCPATH`. Lampiran umum menuju `assets/expense`; bukti pengembalian menuju `assets/expense_bukti_pengembalian`.
- Folder yang hilang dibuat saat diperlukan. Di Linux, folder upload memakai grup `www-data`, mode `02775` (setgid), dan file baru memakai `0664`. Windows tidak menjalankan perubahan grup/mode POSIX. Folder existing diperiksa dan diperbaiki jika hak proses mengizinkan.
- Kegagalan folder/grup/permission atau upload menggagalkan penyimpanan; file baru request yang belum commit dibersihkan, lampiran lama tidak dihapus. Pesan ke pengguna tidak memuat path server atau SQL. File yang sudah commit dipertahankan walaupun penulis audit gagal.
- Salinan PR memakai `.pr-attachments.lock` sampai commit/cleanup selesai agar request lain tidak memakai salinan yang masih dapat dibatalkan. Jangan menghapus lock saat worker aplikasi aktif. Lock ini tetap tersimpan dengan mode `0664`; bukan lampiran dokumen.

## Provisioning server

Proses PHP harus mempunyai hak menulis parent `assets`, mengatur mode folder miliknya, dan memakai grup `www-data`. PHP tidak menjalankan `sudo` dan tidak dapat mengambil hak administrator secara otomatis. Bila folder existing dimiliki akun lain dan perlu diubah modenya, siapkan lewat administrator/deployment terlebih dahulu.

Contoh berikut dijalankan **administrator server**, dari root deployment yang sudah diverifikasi. Ganti path serta akun deploy sesuai server; perintah ini bukan bagian request upload dan belum dijalankan pada server pengguna.

```sh
cd /var/www/sendigs_finance
sudo install -d -o www-data -g www-data -m 2775 assets/expense assets/expense_bukti_pengembalian
```

Jika parent `assets` tidak ada, administrator perlu membuatnya terlebih dahulu dan memberi akses tulis kepada proses PHP. Jika parent sudah ada, pertahankan pengaturan modul lain; berikan write/traverse bagi PHP sesuai kebijakan deployment. Bila proses PHP menggunakan akun selain `www-data`, akun tersebut harus menjadi anggota grup `www-data`; restart worker PHP setelah perubahan keanggotaan grup. Direktori existing dengan mode yang perlu diperbaiki juga harus dimiliki akun PHP atau diperbaiki oleh administrator.

Setgid pada direktori mempertahankan grup `www-data` untuk file/subfolder baru. Helper tetap menerapkan `chmod` setelah pembuatan agar `umask` ketat tidak menghilangkan akses tulis grup. Tidak ada fallback `0777` atau perubahan rekursif pada seluruh project. Lampiran lama tidak dimigrasikan permission-nya secara massal.

## Validasi task 2026-10-27

Tes dijalankan pada image Docker lokal melalui WSL: repository read-only, jaringan Docker dimatikan, fixture filesystem pada tmpfs `/tmp`. Tidak menggunakan browser atau database aplikasi. Tes multipart memakai server fixture lokal di loopback container dan recorder audit asli; persistensi bisnis diganti fixture.

- `Visibility_test.php`: aturan pemohon/admin/finance, ID akun 1/7, Expense.Manage, pencarian, parameter all, count/data ketiga tab dan kedua approval.
- `Upload_directory_test.php` dan `Upload_permission_failure_test.php`: folder baru/existing, umask ketat, grup/mode nyata, path/symlink, file upload parsial, cleanup setelah gagal, lock PR, dan penolakan saat proses tidak berhak memakai www-data.
- `Upload_requests_test.php`: upload multipart tambah/ubah kelima endpoint, multi-file detail, folder terblokir, bukti pada folder terpisah, kegagalan upload/database/transaksi/commit, deduplikasi/metadata audit, serta kegagalan penulis audit.
- Regresi ketujuh batch audit Expense, salinan/lampiran kasbon, tombol management, report, dan tes JavaScript terkait. Bagian report yang memerlukan database nyata tidak divalidasi dalam lingkungan tanpa jaringan ini; hasil bagian fixture dicatat terpisah pada checkpoint.

Tidak ada migrasi database atau perubahan URL/kontrak respons sukses. Publikasi perubahan ditujukan ke branch development; status publikasi dicatat di docs/task-progress.md.

Jalankan dari root project pada Docker Linux dengan image lokal PHP 7.4 yang tersedia:

```sh
docker run --rm --network none --read-only --user 1000:33 --tmpfs /tmp -v "$PWD:/app:ro" -w /app php:7.4-apache php application/modules/expense/tests/Run_access_upload_tests.php
docker run --rm --network none --read-only --user 1000:1000 --tmpfs /tmp -v "$PWD:/app:ro" -w /app php:7.4-apache php application/modules/expense/tests/Upload_permission_failure_test.php
```

Runner menjalankan lint dan 14 suite fixture; tes penolakan permission dijalankan terpisah dengan user yang bukan anggota grup www-data. `Query_state_test.php` memakai Auth dan query builder CodeIgniter asli untuk memastikan lookup akses tidak merusak SELECT/FROM/JOIN/WHERE daftar. `Expense_report_updated_test.php` memerlukan ekstensi mysqli dan memiliki bagian integrasi database tersendiri; tidak termasuk runner fixture ini.
