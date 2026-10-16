# Expense Report: keterangan dan referensi kasbon — 15 Oktober 2026

## Hasil

- Consultant: Expense Report Consultant - [no_transaksi]. Kasbon berasal dari kons_tr_expense_report_project_header.id_header → kons_tr_kasbon_project_header.id, sesuai request_payment print existing; tidak memakai nomor expense sebagai pengganti kasbon.
- Standard: Realisasi Kasbon [Pengembalian/Reimburse] - [pengaju PR]. No. Referensi memakai tr_kasbon.no_doc yang benar-benar ditemukan dari header id_kasbon, detail id_kasbon/kasbon_pr_non_po_pett atau reverse id_expense_pett_pr_non_po. Link dangling/tidak ada menghasilkan literal '-'.
- Pengaju: kasbon tipe_pr/id_pr → rutin_non_planning_header, material_planning_base_on_produksi, atau tran_pr_header → users.id_user=created_by. Mengikuti pemetaan di Expense::get_dat_kasbon* existing, tidak mengganti dengan pembuat jurnal atau penerima transfer. Bila tidak tersedia tampil '-'.
- Pengembalian/reimburse: lebih_bayar/kurang_bayar positif pada header, lalu fallback perbandingan total kasbon versus expense detail menggunakan integer cents. Impas/unknown tanpa subtype agar tidak membuat klaim palsu. Beberapa kasbon/nama valid didedup, diurutkan dan dipisah koma.

Implementasi pada enrichment model shared memakai batch maksimal200 nomor dan lookup terpisah dari SUM jurnal. Namespace standard/consultant dipisah walau no_transaksi sama. Daftar, pencarian/sort/header detail/report memakai metadata baru. Excel detail/keterangan COA dan payload/handler/status/balance/grouping/company/nomor/accounting routing tetap asli. Tidak ada update data sumber atau schema.

## File

- application/modules/jurnal_all/models/Jurnal_all_model.php: expenseMap dan expenseRequesters, dipanggil hanya untuk sourceEXP.
- tests/Query_test.php dan tests/Mysql_test.php: tabel fixture dan regression metadata.
- README, docs/implementation-plan.md, docs/task-progress.md, manifest dan laporan ini.

## Validasi

- RED sebelum fix pada format Consultant. Query126/controller84/dispatcher41/audit8/native168/PCS89/revisi24/client44/MariaDB30 PASS =614 assertions fitur.
- Fixture mencakup regular/consultant, actual/dangling/no relation, header/detail/PR-petty/reverse links, semua tipe PR, multiple dan dedup, pengembalian/reimburse/impas, fallback direction, no transaksi sama lintas namespace, search, posted report, native COA/export/amount/action identity unchanged.
- PHP lint3 PASS. Whitespace/JSON/hash dependency diperiksa pada penutupan. JS/CSS/preview/controller/dispatcher/legacy handler tidak berubah.
- MariaDB fixture berjalan lokal terisolasi (tidak memakai development/database.php remote).30 assertions lulus; cleanup runner awal gagal karena log masih terkunci setelah penghentian mysqld. Verifikasi PID fixture22260 sudah keluar, validasi resolved path dalam scratch, lalu cleanup khusus direktori fixture berhasil. Tidak mengulang tes SQL yang sudah lulus.
- Tidak ada query/write/posting pada server remote/accounting, browser test, commit/push/deploy. Data screenshot milik pengguna dipakai sebagai referensi hasil, bukan klaim runtime diuji agen.

## Batas dan penutupan

Tidak ada TODO kode yang diketahui. Requester yang tidak punya PR/user valid dan referensi yang tidak punya kasbon valid ditampilkan '-', bukan ditebak. Standard impas/unknown memakai Realisasi Kasbon - [pengaju] tanpa label Pengembalian/Reimburse. Data remote aktual tidak diperiksa langsung. Konsistensi lintas database/MyISAM tetap risiko existing.

Antislop delta gate PASS source/fixture: R-17/R-23/R-38/C-5 data metadata berasal dari relasi nyata dengan placeholder eksplisit; R-26/R-35 filter/search/report/identity dan native action regresi lulus, tanpa klaim browser click-through sesuai instruksi pengguna; R-03/R-25/R-29 layout/palet dan escaping existing tidak berubah. Arah UI dan format eksplisit mengikuti permintaan pengguna.
