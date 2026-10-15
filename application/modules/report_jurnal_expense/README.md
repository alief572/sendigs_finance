# Report Jurnal Expense

Route/menu Finance: `report_jurnal_expense`, tepat setelah Report Jurnal Payment. Semua endpoint memerlukan `Report_Jurnal_Expense.View`.

Daftar/dropdown/export hanya membaca `tr_jurnal` untuk `Expense Report` dan `Expense Report Consultant` dengan `sts='1'`. Histori melalui jalur posting lama ikut ditampilkan. Status0/kosong/NULL dan jenis lain tidak masuk report. Satu row daftar per nomor transaksi/jenis/id_company; nama company ditampilkan dari agregat group, bukan bagian identitas transaksi.

View memakai ID representative; server menentukan nomor transaksi/jenis/company dari jurnal posted tersebut. Detail hanya mengambil baris posted group itu dengan debit atau kredit positif, dalam urutan ID, serta mempertahankan cents dan reference `{REF:...}`. Seluruh tampilan read-only. Tidak ada endpoint posting atau koneksi accounting baru.

Tampilan/CSS, tujuh kolom, filter, search, pagination, View modal, serta Excel daftar mengikuti Report Jurnal Payment. Ekspor mengikuti filter tanggal/nomor/company, bukan ekspor detail; alias company sama dengan Jurnal Expense. `export_jurnal` dan `export_excel` berbagi handler internal dengan satu scope audit per request dan nama endpoint asli. Metadata audit hanya count/outcome/reason; writer failure tidak merusak hasil report.

`migrations/001_register_menu.sql` mencari anchor menu dinamis, menyisipkan menu setelah report payment, membuat satu permission View, dan menyalin hanya grant View group/user ekuivalen. Script telah diterapkan dua kali ke database lokal dengan hasil idempotent. Untuk environment lain, jalankan SQL saat pemasangan; tidak otomatis saat Git push.

`sts=1` adalah indikator posted sesuai kontrak existing. Report tidak merekonsiliasi accounting dan tidak membuat snapshot baru. Tidak mengubah jurnal, status, mapping company, atau report existing.

Tes tanpa browser:

```powershell
& C:/xampp74/php/php.exe application/modules/report_jurnal_expense/tests/Query_test.php
& C:/xampp74/php/php.exe application/modules/report_jurnal_expense/tests/Controller_test.php
& C:/xampp74/php/php.exe application/modules/report_jurnal_expense/tests/View_test.php
node application/modules/report_jurnal_expense/tests/Client_test.js C:/xampp74/php/php.exe
```
