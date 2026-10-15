# Jurnal Expense

Route: `jurnal_expense`. Menu Finance berada tepat setelah Jurnal Payment.

Daftar, filter nomor, dan export hanya membaca `tr_jurnal` dengan jenis `Expense Report` (generator modul Expense) atau `Expense Report Consultant`, serta status `0`, string kosong, atau `NULL`. Satu baris mewakili satu nomor transaksi/jenis/company; jumlah baris debit/kredit terlihat di modal Post. `Expense`, `Payment`, pengembalian Expense, dan jenis lainnya tidak masuk modul ini.

Tampilan/CSS mengikuti `jurnal_payment`. Permission baca/ekspor `Jurnal_Expense.View`, preview/posting `Jurnal_Expense.Manage`. Migrasi `migrations/001_register_menu.sql` membuat empat permission project standar dan menyalin hanya grant Jurnal Payment yang ekuivalen. Migrasi idempotent, mencari parent/order secara dinamis, dan telah diterapkan ke database lokal.

Posting memakai jalur JV dari cabang Expense Report Jurnal Payment: nomor dari `Jurnal_payment_nomor_model::get_Nomor_Jurnal_Sales`, header `javh`, detail `jurnal`, counter `pastibisa_tb_cabang.nomorJC`, lalu `tr_jurnal.sts = 1`. Company 4 menuju `DBACC_VUCA`; 1/6/7 `DBACC_STM`; lainnya `DBACC_SUST`. Nama tampilan company tidak mengubah routing existing. Dokumen Consultant tidak diwajibkan ada dalam `tr_expense` lokal.

Nominal, COA, reference, dan keterangan diambil ulang dari database, tidak dari payload browser. Preview hanya untuk pemeriksaan. Server menolak ID tidak valid, tipe/status lain, group yang sebagian sudah posted, tanggal tidak konsisten, nominal negatif/nol, atau debit/kredit tidak seimbang. Presisi dua desimal dipertahankan; baris nol tidak dikirim ke accounting tetapi statusnya ikut diperbarui.

Tujuan accounting lokal memakai MyISAM. Source InnoDB memakai transaksi dan row lock; advisory lock menserialisasi posting antar-request modul ini. Kegagalan normal menghapus header/detail JV yang dibuat request dan mengembalikan counter secara kondisional. Kegagalan cleanup tidak dilaporkan sebagai sukses dan memerlukan pemeriksaan administrator. Tidak ada perubahan engine database. Crash proses/server, putus koneksi saat commit, serta posting paralel dari modul legacy yang tidak mengikuti advisory lock masih memiliki risiko konsistensi; cleanup ini bukan transaksi atomik lintas MyISAM/InnoDB. Jangan menguji posting nyata hanya untuk memverifikasi UI.

Lima endpoint controller memakai recorder audit existing. Metadata hanya outcome/reason/count/cleanup, tanpa payload jurnal, SQL, atau kredensial; sukses posting dicatat setelah commit, dan kegagalan writer tidak membatalkan hasil bisnis.

Tes tanpa browser:

```powershell
& C:/xampp74/php/php.exe application/modules/jurnal_expense/tests/Query_test.php
& C:/xampp74/php/php.exe application/modules/jurnal_expense/tests/Posting_test.php
& C:/xampp74/php/php.exe application/modules/jurnal_expense/tests/Controller_test.php
```

Query test memakai CodeIgniter query builder dan SQLite nyata. Posting test mensimulasikan sifat MyISAM (header/detail/counter tetap ada setelah rollback source), semua tujuan company, serta kegagalan write/commit/cleanup. Controller test memeriksa permission, POST-only, audit, output escaping, dan kesamaan CSS.
