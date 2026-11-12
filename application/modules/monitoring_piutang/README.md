# Monitoring Piutang, Follow Up Piutang, Dashboard Plan Bayar

Spec: `specs/docs/review-monitoring-piutang/` (`fr-nfr.md`, `use-case.md`, mockup, ERD). Progres: `docs/task-progress.md`.

| Menu | Controller | Permission |
|---|---|---|
| Monitoring Piutang | `monitoring_piutang/controllers/Monitoring_piutang.php` | `Monitoring_Piutang.View` |
| Follow Up Piutang | `follow_up_piutang/controllers/Follow_up_piutang.php` | `Follow_Up_Piutang.View` (lihat), `.Manage` (simpan, close) |
| Dashboard Plan Bayar | `follow_up_piutang/controllers/Plan_bayar.php` | `Dashboard_Plan_Bayar.View` |

## Struktur
- `libraries/Piutang_aging.php`: **satu-satunya** tempat aturan (umur, kategori, tahap, jadwal FU, validasi, kunci step, syarat close, filter, plan vs realisasi). PHP murni tanpa CI/DB; layar dan Excel memanggil fungsi yang sama.
- `libraries/Piutang_export.php`: pembuat `.xlsx` bersama (PHPExcel).
- `libraries/Surat_konfirmasi.php`: aturan dan HTML Surat Konfirmasi. Profil kop per company di `profiles()` (kunci = id company; baru STM id 7).
- `models/Monitoring_piutang_model.php`: query dan transaksi simpan/close. Hanya membaca `tr_invoicing.saldo_piutang`.
- `migrations/`: urutan pemasangan ada di `fr-nfr.md` bagian "Urutan pemasangan", **ditambah `019_align_id_invoice_collation.sql` setelah 015 dan sebelum 017** (menyamakan collation `id_invoice` dengan `tr_invoicing.id`; tanpa ini join gagal di MySQL 8 default). Rollback 019 = 020. Sudah dijalankan di DB dev 6 Nov 2026.
- Bukti terima (file wajib di step Diterima Customer): migrasi `021_add_bukti_terima.sql` (rollback 022), file disimpan di `uploads/bukti_terima_invoice/` (PDF/JPG/PNG, maks 5 MB per file), diunggah lewat `Follow_up_piutang::save()`.
  Boleh banyak file (maks 10 per simpan) dan bisa ditempel dengan Ctrl+V. File pertama di kolom `bukti_terima`; file ke-2 dst di tabel `tr_invoice_ar_bukti` (migrasi `031`, rollback `032` — jalankan 031 sebelum deploy kode). File hanya bisa ditambah selama tanggal terima belum tersimpan (`Piutang_aging::bukti_boleh_ditambah`), setelah itu terkunci.
- `application/config/piutang.php`: `tanggal_mulai_follow_up` (go-live, kosong). Folder config di-`.gitignore`, perlu `git add -f`.

## Test (tanpa database)
```
scratch/runtime/php74/php.exe application/modules/monitoring_piutang/tests/Piutang_aging_test.php
scratch/runtime/php74/php.exe application/modules/monitoring_piutang/tests/Piutang_export_test.php
scratch/runtime/php74/php.exe application/modules/monitoring_piutang/tests/Surat_konfirmasi_test.php
```

## Belum ada / diketahui
Write-off piutang tak tertagih, invoice internal antar-company, profil surat selain STM, notifikasi. SQL model dan semua tampilan belum diuji di DB/web.
