# Report Jurnal gabungan

Menu **Report Jurnal**, route `report_jurnal_all`, memakai controller/data/view bersama Jurnal All dalam mode read-only. Semua endpoint membutuhkan `Report_Jurnal_All.View`; posting/revisi ditolak server dalam mode report. Detail source PCS tidak memerlukan pemanggilan model posting/accounting.

Klasifikasi lima tab identik dengan pending. Histori `tr_jurnal.sts=1`, termasuk posting lama, ikut dibaca. Kolom **No. Jurnal Accounting** menggantikan No. Jurnal Source: isinya nomor final yang dikembalikan handler accounting sesudah commit berhasil dan tersimpan di `tr_jurnal_postings`. Nomor staging `tr_jurnal.no_jurnal` tidak dianggap nomor final. Transaksi antar-company menampilkan semua nomor beserta company tujuan, termasuk bila nomor dua ledger kebetulan sama.

Daftar, detail dan Excel menampilkan tanggal/jam posting WIB serta snapshot username user posting. Data lama atau posting di luar dispatcher gabungan yang tidak memiliki metadata ditampilkan literal `-`; tanggal jurnal/pembuatan dan user pembuat tidak dipakai sebagai pengganti. Tidak ada backfill atau renumber histori. Nomor final pada setiap baris Excel merupakan metadata tingkat transaksi, bukan pemetaan nomor ledger per COA.

Filter tahun/bulan/company/kategori Pembayaran, pencarian header/COA/metadata posting, paging, total seluruh hasil filter, detail read-only dan Excel seluruh COA mengikuti kontrak bersama. Histori source bukan rekonsiliasi ledger accounting.

Migrasi metadata `../jurnal_all/migrations/003_posting_metadata.sql` sudah diterapkan pada database development lokal. Environment lain memerlukan penerapan migrasi ini sebelum posting melalui Jurnal All. Pembaca report tetap mendukung schema lama dengan placeholder. Rollback terpisah `004_rollback_posting_metadata.sql` menghapus tabel beserta metadata; gunakan hanya bila penghapusan tersebut memang dikehendaki.

Pemasangan menu, tes dan batas transaksi dijelaskan di `../jurnal_all/README.md`. Empat URL report entry lama dialihkan melalui route exact; Buku Besar Petty Cash tetap terpisah.
