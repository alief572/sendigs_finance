# Pemeriksaan selisih Invoicing — 15 Oktober 2026

## Hasil

Query index jurnal_invoicing tanpa filter menghasilkan 10 ID transaksi. Gabungan tab INV jurnal_all lintas tahun menghasilkan 10 ID unik yang persis sama: tidak ada legacy-only maupun jurnal_all-only ID.

| Tahun kelompok jurnal_all | Jumlah |
|---|---:|
| 2023 | 1 |
| 2024 | 2 |
| 2025 | 3 |
| 2026 | 4 |
| Total | 10 |

Angka 4 yang dilaporkan pengguna terbukti berasal dari default tahun berjalan2026. Index lama tidak membatasi tahun. Tidak ditemukan invoice hilang akibat eligibility pada data lokal yang diperiksa.

## Perbedaan tanggal kelompok

- Legacy base_query_jurnal menyeleksi a.tgl_jurnal tetapi hanya GROUP BY a.no_transaksi, tanpa agregat untuk tanggal. Tanggal yang dipilih tidak dijamin SQL.
- Jurnal_all mengelompokkan no_transaksi+jenis_transaksi dan memakai MIN(a.tgl_jurnal) sebagai tanggal representatif serta HAVING periode. Ini sengaja menjaga satu transaksi tetap satu aksi dan seluruh detail/saldo tidak terpotong oleh filter.
- Source ID00112-INV-V-26 mempunyai empat baris tanggal2025-04-28 dan empat baris2026-05-17, semua pending. Jurnal_all menempatkannya pada2025. Legacy pada snapshot ini memilih2026.
- Source ID00114-INV-V-26 mempunyai empat baris tanggal2023-05-08 dan empat baris2026-05-17, semua pending. Jurnal_all menempatkannya pada2023. Legacy pada snapshot ini memilih2026.
- Karena dua kelompok lintas tahun itu, row-year legacy2026=6 bukan perbandingan setara dengan group-year jurnal_all2026=4.
- Pending source sama: sts kosong/0/NULL, invoice tidak kosong, company tersedia dan ada baris debit/kredit positif. Dua ID lintas tahun memiliki company valid; bukan penyebab eligibility.

## Validasi dan ruang lingkup

Pemeriksaan menjalankan query model asli (legacy private base query melalui reflection tanpa constructor/accounting connections, serta Jurnal_all_model::groups) pada MySQL lokal3307. SET SESSION TRANSACTION READ ONLY + START TRANSACTION WITH CONSISTENT SNAPSHOT; ditutup ROLLBACK. Hanya SELECT dan session transaction control; tidak ada posting, migrasi, UPDATE/INSERT/DELETE maupun audit bisnis.

Pemeriksaan final himpunan ID: legacy_total10; jurnal_all_total10; unique_source_ids10; legacy_only_ids[]; jurnal_all_only_ids[]. Koneksi yang sebelumnya terhalang sandbox kini dapat diperiksa lewat eksekusi socket localhost yang mendapat approval auto-review. Bootstrap diagnosis awal diperbaiki dengan ENVIRONMENT development; alias SQL diagnosis lines diganti line_count setelah syntax error. Tidak ada perubahan kode aplikasi untuk diagnosis tersebut.

Tidak ada kode aplikasi diubah: default tahun berjalan dan MIN group date mengikuti keputusan implementasi awal. Menu saat ini belum menyediakan Semua Tahun. Bila dibutuhkan kesamaan cakupan semua tahun dalam satu tampilan, itu merupakan perluasan filter; jangan mengganti MIN menjadi tanggal baris arbitrer atau memecah grup posting hanya demi menyamakan angka.

File perubahan hanya laporan ini dan checkpoint. Hasil lint/suite sebelumnya tidak diulang karena kode/dependency tetap; validasi dokumentasi/whitespace/hash dilakukan. Tidak ada tes web atau commit/push. Angka merupakan snapshot data lokal saat pemeriksaan, dapat berubah setelah aktivitas bisnis.
