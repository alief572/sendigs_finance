# Incoming Pembelian Cash PR Stok

Pilihan **Pembelian Cash** menggunakan `tr_pr_non_po` yang terhubung ke PR Stok
approved (`app_3 = 1`, detail `status_app = Y`). Dokumen tidak perlu Paid.
Status dokumen yang sudah diteruskan ke payment tetap diizinkan selama tidak
memiliki penanda penolakan dan PR tidak ditolak/dihapus.

Detail penerimaan memakai `tipe_po = cash`, `id_po_detail = ID detail PR`, dan
`no_ipp = nomor Cash`. Outstanding dijumlahkan lintas dokumen Cash untuk detail
PR yang sama. Server membaca ulang barang, kuantitas pembelian dan harga.
Header/detail penerimaan, stok/riwayat, costbook dan price book disimpan dalam
satu transaksi. Cash mengabaikan jurnal yang dikirim oleh form.

Dokumen, header/detail PR, master barang dan stok dikunci sebelum perhitungan.
Advisory lock per database juga menyerialkan penomoran penerimaan Cash; jalur
PO/kasbon tetap memakai alur sebelumnya. Deadlock/kegagalan query dibatalkan
dan dapat di-submit ulang setelah pengguna memperbarui outstanding.

## Pemeriksaan

```powershell
C:/xampp74/php/php.exe application/modules/incoming_stok/tests/cash_receipt_test.php
node application/modules/incoming_stok/tests/form_state_test.js
C:/xampp74/php/php.exe application/modules/incoming_stok/tests/schema_check.php
```

Suite PHP hanya mengakses MySQL lokal `127.0.0.1:3307`. Password default Docker
dapat diganti dengan environment `INCOMING_TEST_PASSWORD`. Suite membuat
database `codex_incoming_test_*` lalu menghapusnya di blok `finally`. Fixture
Cash merupakan struktur tabel target tanpa data; fixture dependensi legacy
dibatasi pada kolom yang diperlukan oleh regresi. Tes Cash dijalankan pada
SQL mode ketat, sedangkan tes alur legacy memakai SQL mode permisif sebagaimana
asumsi insert pada alur tersebut.

`schema_check.php` hanya menjalankan `SHOW CREATE TABLE`/`SHOW COLUMNS` pada
koneksi aplikasi untuk verifikasi rollout. Pemeriksaan saat implementasi
menemukan kolom metadata sudah tersedia dan seluruh tabel terkait memakai
InnoDB. Tidak ada migrasi skema atau perubahan data remote.

Pembulatan stok, costbook dan price book mengikuti presisi kolom serta rumus
konversi aplikasi yang sudah berlaku. Cash belum membuat jurnal, sesuai scope.
Pengujian cetak memverifikasi HTML template; browser dan renderer mPDF tidak
dijalankan. Commit/push menunggu permintaan pengguna.
