# Status Paid

Label pembayaran selesai pada daftar, filter, dan detail adalah **Paid**.
Nilai database dan JSON tetap `done payment` agar kompatibel dengan data lama.

Payment di `Pembayaran_material::save_payment()` mencocokkan `no_doc` dengan
`no_payment_hutang`, tanpa bergantung pada tipe atau awalan nomor dokumen.
Pembaruan laporan berada dalam transaksi yang sama dengan payment dan jurnal.
Kegagalan status membatalkan transaksi, sedangkan laporan yang sudah Paid
dianggap berhasil tanpa mengubah audit laporan. Rollback membutuhkan tabel
transaksional (InnoDB), seperti migration modul dan fixture pengujian.

## Preview data lama (read-only)

```powershell
C:\xampp74\php\php.exe application/modules/petty_cash_vuca_sustain/tools/preview_paid_status.php
```

Tool hanya melakukan SELECT dan memakai koneksi default pada konfigurasi
development aplikasi. Hasilnya mencantumkan target database, jumlah kandidat,
nomor dokumen kandidat, dan engine tabel payment; password tidak ditampilkan.

## Koreksi historis

Gunakan `migrations/003_reconcile_paid_status.sql` pada database lokal yang
dituju setelah backup. Jalankan bagian PREVIEW terlebih dahulu. Bagian APPLY
mengubah hanya laporan `waiting payment` dengan `payment_approve.status = 2`
dan header `tr_payment_paid` yang cocok. Draft, payment belum selesai, payment
tanpa header, serta laporan yang sudah Paid tidak diubah.

Audit diambil dari header terbaru menurut `created_on`, lalu ID header dan ID
approval sebagai penentu urutan. Jika tanggal header kosong, gunakan tanggal
bayar; jika tanggal atau user tidak tersedia, pertahankan audit laporan lama.
Skrip melaporkan jumlah laporan yang dikoreksi dan aman dijalankan ulang.
Perbandingan nomor dokumen menyamakan collation untuk tabel legacy.

Jangan menjalankan APPLY pada server remote sebagai bagian pengujian lokal.
Tidak diperlukan perubahan skema database atau rebuild image Docker untuk
perubahan PHP ini karena source project memakai bind mount.

## Pengujian regresi

```powershell
C:\xampp74\php\php.exe application/modules/petty_cash_vuca_sustain/tests/paid_status_test.php
```

Tes memakai driver CodeIgniter asli dan membuat schema acak
`codex_pcvs_test_*` pada MySQL Docker lokal `127.0.0.1:3307`, kemudian
menghapusnya dalam `finally`. Database aplikasi tidak digunakan. Set
`PCVS_TEST_PORT` dan `PCVS_TEST_PASSWORD` jika konfigurasi Docker berbeda;
`PCVS_TEST_HOST` hanya menerima `127.0.0.1`, `localhost`, atau service Docker
`mysql`. Default password mengikuti contoh development Docker project.

Skenario mencakup payment sukses, batch campuran, payment biasa, retry audit,
rollback saat status gagal, koreksi historis dengan duplikasi dan orphan header,
filter status/company, dan rendering label daftar/detail tanpa browser.
