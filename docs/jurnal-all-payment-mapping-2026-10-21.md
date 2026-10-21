# Mapping Pembayaran Jurnal All — 21 Oktober 2026

## Hasil dan keputusan

Implementasi selesai. `payment_approve.id` adalah ID approval, `id_payment` adalah voucher pembayaran. Keduanya sebelumnya berada pada indeks lookup yang sama dengan no_doc sehingga benturan identitas memasukkan metadata transaksi lain. Resolver sekarang memisahkan tiga indeks dan memilih per token: id → id_payment → no_doc. Tier berikutnya hanya dipakai jika tier sebelumnya tidak memiliki kandidat sesuai tipe. Refill hanya menerima refill_pettycash; hutang hanya petty_cash_hutang; kategori pembayaran generik tetap didukung. Hasil dideduplikasi berdasarkan ID approval dan digunakan bersama untuk voucher, referensi, kategori, eligibility dan pencarian approval terpilih.

Native key, grouping, nominal, detail accounting dan status tetap; tidak ada migrasi/perubahan data/writer/posting/endpoint/modul legacy. Lookup tetap batch 200 kunci, dengan cache kandidat request-local dan resolusi tipe terpisah. Pencarian approval terpilih juga menemukan kelompok yang native key-nya voucher/dokumen; assertion existing diperluas tanpa menghapus pemeriksaan approval-key asli.

## File

- `application/modules/jurnal_all/models/Jurnal_all_model.php`: indeks, prioritas/type resolver, pencarian approval terpilih.
- `application/modules/jurnal_all/tests/Payment_mapping_test.php`: regresi khusus, menggunakan bootstrap SQLite Query_test existing.
- `application/modules/jurnal_all/tests/Query_test.php`: perluasan ekspektasi pencarian approval terpilih pada kelompok voucher.
- README modul, implementation-plan, task-progress dan laporan trace: kontrak, fase dan hasil.
- `scratch/verify-payment-mapping-20261021.php`: validasi model nyata lewat koneksi aplikasi pada port lokal dalam READ ONLY + rollback; kredensial tidak dicetak. Artefak `scratch/payment-baseline-check-20261021.php` dan baseline model dari HEAD dipakai membandingkan kegagalan stress tanpa mengganti file aplikasi.

## Validasi

- RED: model lama gagal pada assertion `collision must not add the unrelated Cash voucher` sesudah query suite existing lulus 129 assertions.
- GREEN: Query_test 130 assertions dan Payment_mapping_test 45 assertions lulus. Mencakup benturan nyata, dua sisi, prioritas tier sejenis/beda tipe, fallback voucher/dokumen, multi-ID, deduplikasi, voucher kosong/missing, eligibility, cache dua urutan, search/filter/options, detail, posted report/detail/export, sorting dan nominal. 205 kelompok jurnal dibaca dengan dua query payment_approve; pembacaan ulang cache tidak mengulang query payment.
- Suite PHP lain lulus: Audit 10; Challenger_concurrency_tampering 72; Controller 86; Dispatcher 45; Legacy_posting 198; Metadata 20; Petty_posting 113; Revision 24. Client_test.js lulus 48 assertions. Suite query dijalankan juga melalui bootstrap tes mapping.
- Challenger_stress gagal pada assertion rollback counter di jalur invoicing (baris 674). Kegagalan identik direproduksi dengan model Jurnal_all dari HEAD sebelum perubahan ini; bagian tersebut memanggil Jurnal_invoicing_model, bukan resolver pembayaran. Tidak diubah/dilemahkan/dilewati untuk mengklaim suite hijau.
- Mysql_test dijalankan melalui Run_mysql_fixture.ps1 pada MariaDB disposable port 33317. Berhenti saat migrasi menu di baris 24: kolom permissions.created_by tidak ada pada fixture (CREATE TABLE permissions di baris 16), sementara 001_register_menu.sql mengisinya. Gagal sebelum pengujian query model; schema fixture/migrasi tidak diubah. Database aplikasi tidak dipakai untuk migrasi.
- PHP lint model, kedua file tes terkait dan script verifikasi lokal lulus. Scoped git diff --check lulus (peringatan konversi LF/CRLF mengikuti pengaturan Git lokal).

## Verifikasi database lokal

Model aktual dipanggil pada `db_sendigs_ss` dalam transaksi READ ONLY, diakhiri ROLLBACK. Dua kelompok ditemukan:

| Native no_transaksi | Company | Voucher tampilan | Referensi | Kategori | Debit/Kredit masing-masing |
| --- | --- | --- | --- | --- | --- |
| BK--0926-0015 | STM | BK--0926-0015 | PHP-2026-0012 | Payment Hutang Petty Cash | 2647250.00 |
| BK--1026-0055 | SUSTAIN | BK--0926-0015 | PHP-2026-0012 | Payment Hutang Petty Cash | 2647250.00 |

Detail dua baris per kelompok balance; ekspor empat baris memakai metadata sama. Snapshot semua kolom tr_jurnal ID 10819–10822 identik sebelum/sesudah. Tidak ada koreksi data bisnis atau posting. Tidak ada browser, commit, push, atau deployment.

## Risiko/TODO

- Dua kegagalan suite lintasfitur di atas tetap terbuka untuk pekerjaan terpisah; validasi tidak diklaim seluruhnya hijau.
- Data dengan tipe petty yang tidak sesuai tidak lagi menjadi sumber metadata/eligibility; anomali demikian perlu pemeriksaan sumber dan tidak diperbaiki otomatis.
- Prioritas ID approval untuk pembayaran generik mengikuti kontrak penulisan jurnal; perubahan kontrak writer di masa depan harus disertai pembaruan resolver dan tes.
