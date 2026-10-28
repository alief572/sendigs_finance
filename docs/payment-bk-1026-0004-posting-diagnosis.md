# Diagnosis tombol Posting BK--1026-0004 — 28 Oktober 2026

## Bukti runtime development

Diperiksa dengan konfigurasi aplikasi dan koneksi yang menjalankan `SET SESSION TRANSACTION READ ONLY` serta `START TRANSACTION READ ONLY`. Hanya SELECT/pemeriksaan schema, diakhiri ROLLBACK. Tidak mengubah schema, registry, status atau data transaksi aplikasi.

| Bagian | Hasil |
| --- | --- |
| Approval Kasbon | `payment_approve.id = BK--1026-0073` |
| Voucher aktual | `payment_approve.id_payment = BK--1026-0004` |
| Dokumen | `KS-2026-00331` |
| Approval lain dengan nomor sama | `id = BK--1026-0004`, dokumen `PERIODIK-2026-00170`, `id_payment/tgl_bayar` kosong |
| Staging Payment | ID 11176–11179, `no_transaksi = BK--1026-0073`, company4 (VUCA), `sts=0`, tanpa tgl_posting/posted_by |
| Nominal staging | Debit/kredit masing-masing 600000; dua baris bernilai nol tetap dipertahankan |
| Jurnaltras | 3 baris, `no_reff = BK--1026-0073`, `no_request = BK--1026-0004` |
| Accounting default (`accounting`) | Nomor `101BKA260000276`: 3 detail `jurnal`, 1 header `japh`, `no_reff = BK--1026-0073` |
| Accounting tujuan VUCA | Tidak ditemukan header/detail dengan key/voucher/dokumen terkait |
| Registry percobaan Payment | `tr_jurnal_payment_attempts` belum tersedia |
| Engine tujuan/default | `jurnal`, `japh`, `pastibisa_tb_cabang` masih MyISAM |

## Kesimpulan

Validasi relasi nomor sudah melewati masalah benturan approval/voucher. Exception runtime awal berikutnya adalah registry percobaan belum tersedia, tetapi controller menyamarkannya menjadi pesan validasi umum. Pemeriksaan lanjutan mengonfirmasi bahwa transaksi sudah memiliki hasil auto-jurnal pada accounting default sementara staging masih pending dan accounting VUCA belum memiliki hasil terkait.

Karena guard yang disepakati melarang posting ulang jika hasil accounting sudah ada, tidak aman sekadar mengaktifkan tombol atau mengganti `tr_jurnal.no_transaksi`. Mengganti nomor tidak menghapus bukti posting. Perlu rekonsiliasi status dan tujuan accounting terlebih dahulu untuk menentukan apakah hasil default adalah hasil yang benar atau perlu penanganan routing terpisah. Jangan langsung memberi sts=1, membuat tanggal/user posting historis, memindahkan jurnal, menghapus hasil accounting, atau memposting kedua kalinya.

## Perbaikan kode pada task ini

- Modal menampilkan tombol Posting nonaktif bagi pengguna yang berhak ketika guard menolak; alasan tersedia pada warning/tooltip. Mode laporan/tanpa izin tetap tidak menampilkan aksi tersebut.
- Client memblokir submit keyboard/manual bila preview tidak mengizinkan posting. Setelah request/penutupan modal, status tombol mengikuti izin preview dan tidak dipulihkan menjadi aktif secara membabi-buta.
- Server tetap menjadi otoritas can_post, menambahkan show_post dan post_block_reason. Tidak membuka guard apa pun.
- Bukti jurnal historis ditampilkan sebelum prerequisite registry yang hilang. Prerequisite yang diketahui memakai exception aman agar UI menampilkan alasan spesifik, tanpa membocorkan SQL/kredensial atau exception tak dikenal.
- Validasi aktual sesudah perbaikan: `Payment_reference_issue` dengan pesan voucher sudah memiliki catatan jurnal pembayaran, staging masih pending dan perlu rekonsiliasi; posting ulang diblokir.

## Sisa pekerjaan operasional

Tidak ada patch nomor/status atau migrasi database aplikasi dijalankan. Untuk transaksi ini, perlu keputusan rekonsiliasi status/routing berdasarkan bukti di atas. Untuk Payment lain tanpa hasil accounting, migrasi007 dan dukungan engine transactional masih menjadi prerequisite; task ini tidak mengubah engine tabel historis.
