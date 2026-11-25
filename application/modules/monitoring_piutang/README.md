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
- Batas mulai Monitoring: `Piutang_aging::eligible_invoice_date()` memuat tanggal invoice valid sejak 2026-10-01 atau salah satu dari 20 nomor `FOLLOW_UP_INVOICES`, sama dengan Follow Up Piutang. Status close dan filter lain tetap berlaku; kartu, list, rekap, dan Excel mengikuti basis ini. Nomor target boleh memiliki beberapa baris invoice; setiap ID tetap diproses sendiri.
- Dashboard Plan Bayar juga mengecualikan 20 nomor tersebut dari batas periode Oktober 2026, termasuk realisasi. Pilihan tahun mengikuti tanggal plan/penerimaan target paling awal; seluruh bulan dapat dipilih. Tanpa plan tersimpan, invoice tidak masuk tabel plan. Tujuh nomor yang belum diinput tidak dibuat otomatis; rincian verifikasi: `docs/invoice-follow-up-targets-2026-11-19.json`.
- Card Total Piutang (`summary()['semua']`) = diterima + belum diterima + lunas belum close, jadi jumlah invoice sama dengan tab Semua Follow Up Piutang; nominal hanya dari yang bersisa. Lancar/Potensi/Macet tetap hanya yang sudah diterima dan persennya terhadap card Total Piutang. List, rekap, dan Excel tetap hanya invoice bersisa yang sudah diterima.
- `libraries/Piutang_export.php`: pembuat `.xlsx` bersama (PHPExcel).
- `libraries/Surat_konfirmasi.php`: aturan dan HTML Surat Konfirmasi. Profil kop per company di `profiles()` (kunci = id company; baru STM id 7).
- `models/Monitoring_piutang_model.php`: query dan transaksi simpan/close. Hanya membaca `tr_invoicing.saldo_piutang`.
- `migrations/`: urutan pemasangan ada di `fr-nfr.md` bagian "Urutan pemasangan", **ditambah `019_align_id_invoice_collation.sql` setelah 015 dan sebelum 017** (menyamakan collation `id_invoice` dengan `tr_invoicing.id`; tanpa ini join gagal di MySQL 8 default). Rollback 019 = 020. Sudah dijalankan di DB dev 6 Nov 2026.
- Bukti terima (file wajib di step Diterima Customer): migrasi `021_add_bukti_terima.sql` (rollback 022), file disimpan di `uploads/bukti_terima_invoice/` (PDF/JPG/PNG, maks 5 MB per file), diunggah lewat `Follow_up_piutang::save()`.
  Boleh banyak file (maks 20 per simpan) dan bisa ditempel dengan Ctrl+V. File pertama di kolom `bukti_terima`; file ke-2 dst di tabel `tr_invoice_ar_bukti` (migrasi `031`, rollback `032` — jalankan 031 sebelum deploy kode). File hanya bisa ditambah selama tanggal terima belum tersimpan (`Piutang_aging::bukti_boleh_ditambah`), setelah itu terkunci. Invoice lama yang sudah terima tanpa bukti tetap bisa melengkapi bukti di form (flag `bukti_boleh`).
- `application/config/piutang.php`: `tanggal_mulai_follow_up` (go-live, `2026-11-01` per MP-R-33; aturan invoice lunas belum dikirim) dan `tanggal_mulai_daftar` (`2026-10-01`, batas daftar Monitoring / Follow Up / Dashboard, dibaca `Piutang_aging::tanggal_mulai()`; dipertahankan Oktober agar 34 invoice Oktober 2026 yang sudah berjalan tidak hilang). Folder config di-`.gitignore`, perlu `git add -f`.
- **Beda dari specs (keputusan user, specs belum diperbarui):** bukti terima wajib (spec: opsional); tanggal terima + no resi terkunci begitu tanggal terima disimpan (spec: setelah FU 1); keterangan plan bayar wajib; tampilan tetap DataTables (spec: AG Grid).
- Tutorial (driver.js lokal, `assets/js/piutang-tutorial.js`): tombol Tutorial di Follow Up (halaman + alur Invoice sampai Close), Monitoring, dan Dashboard. Tidak menyimpan data.
- Tautan: Follow Up menerima `?tab=<tab>&open=<id invoice>` (dipakai Monitoring, Dashboard) dan `?open=<id>` tanpa tab (dipakai nomor invoice di Invoicing tab Invoiced).

## Test (tanpa database)
```
scratch/runtime/php74/php.exe application/modules/monitoring_piutang/tests/Piutang_aging_test.php
scratch/runtime/php74/php.exe application/modules/monitoring_piutang/tests/Piutang_export_test.php
scratch/runtime/php74/php.exe application/modules/monitoring_piutang/tests/Surat_konfirmasi_test.php
scratch/runtime/php74/php.exe application/modules/monitoring_piutang/tests/Follow_up_targets_test.php
node application/modules/follow_up_piutang/tests/Bukti_client_test.js
node application/modules/follow_up_piutang/tests/Tutorial_client_test.js
```

## Belum ada / diketahui
Write-off piutang tak tertagih, invoice internal antar-company, profil surat selain STM, notifikasi. SQL model dan semua tampilan belum diuji di DB/web.
