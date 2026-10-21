# Penelusuran No. Transaksi Pembayaran — 21 Oktober 2026

- Status penelusuran awal: selesai. Perbaikan pemetaan kemudian diimplementasikan atas permintaan pengguna; hasil akhir dan validasi ada di `docs/jurnal-all-payment-mapping-2026-10-21.md`.
- Sumber verifikasi: database lokal `db_sendigs_ss`, koneksi aplikasi development melalui localhost:3307. Semua query dalam `START TRANSACTION READ ONLY`, diakhiri rollback.
- Baris screenshot STM tanggal 2026-10-13, debit/kredit 2647250.00: `tr_jurnal.no_transaksi = BK--0926-0015`, jenis Payment Hutang Petty Cash, ID 10821 (Bank BCA/debit) dan 10822 (Piutang Hubungan Istimewa/kredit).
- Sisi SUSTAIN: `tr_jurnal.no_transaksi = BK--1026-0055`, ID 10819 dan 10820, nominal sama.
- Tidak ada baris `tr_jurnal` dengan no_transaksi persis BK--0826-0018 pada saat pemeriksaan.

## Benturan identitas pembayaran

| payment_approve.id | id_payment | no_doc | tipe |
| --- | --- | --- | --- |
| BK--0926-0015 | BK--0826-0018 | DPM-2026-00020 | Cash |
| BK--1026-0055 | BK--0926-0015 | PHP-2026-0012 | petty_cash_hutang |

`Jurnal_all_model::paymentMap()` mencocokkan kunci jurnal terhadap id, id_payment, dan no_doc sekaligus; `enrich()` menggabungkan seluruh voucher, referensi, dan kategori yang ditemukan. Untuk kunci BK--0926-0015, kedua record di atas ikut ditemukan. Ini menjelaskan tampilan dua voucher dan kategori Cash/Payment Hutang Petty Cash meskipun kelompok jurnal berjenis Payment Hutang Petty Cash. Tampilan gabungan tidak membuktikan dua dokumen tersebut satu transaksi bisnis.

## File dan validasi

- Dibuat `scratch/trace-payment-vouchers-20261021.php`: query diagnostik baca saja, kredensial dibaca dari konfigurasi tanpa dicetak; hostname disesuaikan ke port lokal tanpa mengubah konfigurasi aplikasi.
- Query langsung relasi payment_approve, empat baris jurnal, dan jumlah exact-match berhasil. PHP lint script lulus. Tidak ada pengujian browser atau perubahan data bisnis.
- Tindak lanjut: pemetaan berdasarkan jenis dan prioritas identitas sudah diterapkan berikut tes regresinya. Rincian batas validasi suite tercatat pada laporan implementasi terpisah.
