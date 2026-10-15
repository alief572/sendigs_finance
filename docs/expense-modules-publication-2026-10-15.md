# Publikasi modul Expense — 15 Oktober 2026

Pengguna mengotorisasi jurnal_expense dan report_jurnal_expense ke main/development. Paket mencakup kedua modul lengkap (controller/model/view/tes/README/migrasi) serta dokumen task. Commit diparentkan ke remote branch masing-masing; tidak melakukan merge seluruh branch atau force push.

Validasi sebelum publikasi: Jurnal Expense167 + Report93 =260 assertions/checks PASS, recorder/logger/legacy PASS, 17 PHP lint PASS, rendered/inline JavaScript PASS. Dependency constants, Jurnal Payment/Report Payment dan fondasi audit identik pada kedua branch. Suite dipakai dari source workspace yang sama dengan blob commit; tidak mengulang setelah perubahan dokumentasi saja.

Index Git terpisah menjaga checkout, staging utama dan pekerjaan lokal lain. Cakupan tidak mencakup revisi kolom Record Request Payment, Incoming, Expense controller, pembayaran_material, atau rollout audit lokal.

Status: commit dan verifikasi remote sedang disiapkan. Kriteria penutupan: SHA remote sesuai commit, tree dua modul identik di main/development dan sama dengan source lokal teruji, diff di luar allowlist kosong, HEAD/index utama utuh.

Migrations menu/permission disertakan dan sudah diuji idempotent lokal pada task implementasi. Push Git tidak menjalankan SQL pada database deployment; jalankan 001_register_menu.sql masing-masing modul saat pemasangan. Tidak melakukan posting accounting atau browser test.

Batasan existing: accounting MyISAM bukan transaksi atomik lintas engine saat crash/commit ambigu/legacy concurrency; report memakai sts=1, bukan rekonsiliasi ledger. Publikasi ini tidak mengubah kontrak tersebut.
