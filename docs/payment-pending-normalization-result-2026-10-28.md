# Hasil normalisasi Payment pending — 28 Oktober 2026

## Hasil development

Penerapan dilakukan pada `db_sendigs_ss_dev`, setelah migrasi alias008, backup, dry-run/review, transaksi per voucher dan verifikasi tiap batch. Tidak ada posting jurnal yang dijalankan.

| Cakupan | Hasil |
| --- | ---: |
| Payment sebelum/sesudah | 2.022 baris |
| Pending sebelum/sesudah | 1.968 baris |
| Nomor pending berhasil disamakan ke id_payment | **1.518 baris** |
| Pending sudah canonical sejak awal | **5 baris** |
| Pending tidak diubah karena tidak memenuhi kriteria | **445 baris / 103 kelompok** |
| Posted dipertahankan identik | **54 baris / 11 kelompok** |
| Non-Payment dipertahankan identik | **5.621 baris** |
| Seluruh metadata posting dipertahankan identik | **5 record** |
| Cohort voucher diterapkan | **249** |
| Kelompok pending asal yang dinormalisasi | **293** |
| Pengurangan kelompok melalui penggabungan aman | **44** |
| Alias aktif | **1.518**, satu per baris yang dinormalisasi |

Tidak mengubah status, nominal, COA, tanggal, company, keterangan, ID baris, data posted ataupun accounting existing. Verifikasi agregat terhadap backup awal memastikan seluruh field lain identik. Snapshot detail/header accounting dan jurnaltras dari seluruh249 cohort cocok, tanpa cohort yang tidak terverifikasi.

## Batch dan penghentian aman

| Batch | Cohort diterapkan | Baris berubah | Kelompok tergabung (pengurangan) |
| --- | ---: | ---: | ---: |
| 1 | 192 | 1.177 | 34 |
| 2 | 34 | 214 | 8 |
| 3 | 14 | 77 | 1 |
| 4 | 5 | 30 | 1 |
| 5 | 2 | 10 | 0 |
| 6 | 2 | 10 | 0 |
| 7 | 0 | 0 | 0 |

Batch7 merupakan dry-run akhir: tidak ada eligible tambahan. Tahap berulang membebaskan nomor target yang sebelumnya masih dipakai kelompok pending lain; tidak menggunakan temporary key atau menggabungkan kelompok berbeda secara paksa.

Batch2 berhenti pada guard membership setelah31 baris berhasil commit. Bug pemeriksaan token yang mengabaikan alias direproduksi dalam fixture dan diperbaiki; verifikasi memastikan hasil parsial aman. Resume memakai manifest/checkpoint yang sama dan cohort committed menjadi no-op. Seluruh214 baris batch2 kemudian selesai dan terverifikasi. Guard posting juga diperbaiki agar memakai relasi alias ketika nomor canonical kelompok berbeda kebetulan sama dengan ID approval. Benturan target aktual, relasi ambigu, metadata/registry/accounting posted tetap diblokir.

## Kasus BK--1026-0004

ID staging11176–11179 sekarang mempunyai `no_transaksi = BK--1026-0004`, sebelumnya `BK--1026-0073`. Keempat baris tetap `sts=0`; debit/kredit total masing-masing600000 dan dua baris nol dipertahankan. Alias menghubungkan ke approval `BK--1026-0073` dan dokumen `KS-2026-00331`.

Hasil accounting historis nomor `101BKA260000276` tidak diubah. Pemeriksaan runtime sesudah normalisasi masih menolak posting ulang berdasarkan jurnal pembayaran existing melalui referensi lama. Perbaikan status/routing accounting default versus VUCA tidak termasuk pekerjaan ini.

## Pengecualian pending yang tetap dipertahankan

Angka berikut dihitung per ID/key unik. Satu kelompok ambigu dapat berhubungan dengan beberapa kandidat voucher; jangan menjumlahkan anggota cohort mentah karena akan menghitung ulang ID yang sama.

| Alasan utama | Kelompok | Baris |
| --- | ---: | ---: |
| Terhubung histori posted | 4 | 20 |
| Mapping approval/voucher ambigu | 22 | 84 |
| Target masih ditempati kelompok berbeda | 39 | 201 |
| Company/tanggal berbeda | 5 | 32 |
| Record pembayaran aktual tidak valid | 1 | 5 |
| Company belum dikenali | 18 | 85 |
| Jurnal tidak balance atau kosong | 13 | 13 |
| Relasi approval/voucher tidak ditemukan | 1 | 5 |
| **Total** | **103** | **445** |

Seluruh85 baris company belum dikenali mempunyai id_company dan nm_company kosong. Field tersebut tidak diisi secara tebakan. Kelompok yang terhalang target, ambigu atau tidak balance membutuhkan pemeriksaan/perbaikan sumber terpisah; tidak dibuka dengan melonggarkan guard.

## File kode dan verifikasi

Baru: `Payment_normalization.php`, migrasi008, `Payment_normalization_test.php`, panduan normalisasi dan laporan hasil ini. Integrasi: `Payment_reference.php` (alias resolver/batch lookup/guard legacy reference), `Payment_reference_cli.php` (normalize dry-run/apply/verify/rollback), fixture Payment_reference dan MySQL. Alat operasi CLI-only berada di `scratch/run_payment_normalization.php` dan tidak berisi kredensial tertanam. Rencana serta checkpoint diperbarui.

Validasi penutupan: Payment_reference531, Payment_normalization464, MariaDB disposable29 assertion lulus. Suite lokal Jurnal All terkait (Audit10, Concurrency72, Controller89, Query135, Debt_group26, Dispatcher45, Legacy198, Metadata20, Mixed20, Mapping47, Petty113, Revision24) lulus; Client54 dan regresi pembayaran Batch1 248/Batch2 581/Selection98/invalid journal tetap lulus. Lint dan whitespace diperiksa. Tidak menguji browser. Kegagalan baseline Invoice stress/migrasi umum yang sebelumnya direproduksi tetap tercatat, tidak disamarkan atau diuji berulang tanpa perubahan fondasi terkait.

## Artefak pemulihan

Di luar document root:

`C:/Users/Febry/.codex/visualizations/2026/10/28/01a18142-f6d5-75c0-bb7f-b65a7c778d4c/payment-normalization/`

- `development-20261028.json` dan `development-20261028-batch2.json` sampai `-batch7.json`: manifest tetap.
- `.backup.json`, `.protected.json`, `.journal`, `.result.json`: backup lengkap, checksum, checkpoint delta dan hasil tiap batch. Batch7 tanpa mutasi tidak memiliki hasil apply.
- `final-verification.json`: bukti agregat terhadap backup awal.
- `final-exceptions.json`: daftar103 kelompok/445 ID pending yang tidak diubah, alasan, company dan tanggal.

Simpan manifest, backup dan journal bersama. Rollback batch dependent dilakukan dari batch terbaru ke terlama, bukan dengan menimpa slot canonical yang sudah dipakai batch berikutnya. Gunakan alur rollback terjaga; transaksi yang sudah berubah atau mempunyai bukti posting baru ditolak. Jangan rollback kode ke handler lama tanpa dukungan alias/voucher setelah normalisasi.

Migrasi007/konversi engine accounting tidak diterapkan; tidak diperlukan untuk normalisasi nomor dan tidak boleh dipakai untuk membuka posting ulang. Kode belum di-commit/push ke GitHub dalam pekerjaan ini. Checkout lain yang memakai database development ini perlu memakai dukungan alias/voucher yang sama sebelum menjalankan posting.
