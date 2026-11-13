# Monitoring Piutang, Follow Up Piutang, Dashboard Plan Bayar

Spec: `specs/docs/review-monitoring-piutang/` (`fr-nfr.md`, `use-case.md`, mockup, ERD). Progres: `docs/task-progress.md`.

| Menu | Controller | Permission |
|---|---|---|
| Monitoring Piutang | `monitoring_piutang/controllers/Monitoring_piutang.php` | `Monitoring_Piutang.View` |
| Follow Up Piutang | `follow_up_piutang/controllers/Follow_up_piutang.php` | `Follow_Up_Piutang.View` (lihat), `.Manage` (simpan, close) |
| Dashboard Plan Bayar | `follow_up_piutang/controllers/Plan_bayar.php` | `Dashboard_Plan_Bayar.View` |

## Struktur
- `libraries/Piutang_aging.php`: **satu-satunya** tempat aturan (umur, kategori, tahap, jadwal FU, validasi, kunci step, syarat close, filter, plan vs realisasi). PHP murni tanpa CI/DB; layar dan Excel memanggil fungsi yang sama.
- Tab "List Piutang per Invoice" memakai DataTables server-side (`Monitoring_piutang::list_data`, DataTables 2.3.2 dari CDN seperti modul lain). Filter sama dengan `data()`/Excel; paging dan urutan (`Piutang_aging::page_rows`, kunci urut di daftar putih `PAGE_SORT_KEYS`) dikerjakan di server, maks 500 baris per halaman, baris TOTAL = seluruh hasil filter. Tab "Rekap per Customer" juga server-side (`Monitoring_piutang::rekap_data`, urut lewat `Piutang_aging::REKAP_SORT_KEYS`; tabel dibuat saat tab pertama dibuka, nominal dalam sen). `data()` hanya mengirim ringkasan, daftar nama customer (datalist filter), dan opsi company (tanpa `rows`/`rekap`).
- Batas mulai Monitoring: `Piutang_aging::monitoring_base()` hanya memuat invoice belum close dengan `tgl_invoice >= TANGGAL_MULAI_FOLLOW_UP` (2026-10-01), sama dengan Follow Up Piutang. Kartu, list, rekap, dan Excel ikut. Total keseluruhan tidak lagi mencakup invoice sebelum batas, jadi tidak otomatis sama dengan buku besar.
- Card Total Piutang (`summary()['semua']`) = diterima + belum diterima + lunas belum close, jadi jumlah invoice sama dengan tab Semua Follow Up Piutang; nominal hanya dari yang bersisa. Lancar/Potensi/Macet tetap hanya yang sudah diterima dan persennya terhadap card Total Piutang. List, rekap, dan Excel tetap hanya invoice bersisa yang sudah diterima.
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
