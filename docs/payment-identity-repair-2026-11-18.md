# Koreksi identitas Payment — 18 November 2026

Koreksi database selesai tanpa posting: 71 keterangan `tr_jurnal`, 15 keterangan `jurnal` accounting company, dan 80 alias aktif baru. Nomor `tr_jurnal.no_transaksi` serta `jurnal.no_reff` sudah sesuai voucher sebelum koreksi dan tidak ditulis ulang. Status, COA, tanggal, nominal, nomor jurnal, header, metadata posting, registry percobaan, dan counter tetap.

## Cakupan dan perubahan aktual

| Voucher | Status awal/akhir | Keterangan staging | Keterangan accounting | Alias baru |
|---|---|---:|---:|---:|
| BK--0826-0016 | pending | 3 | 0 | 0 |
| BK--0826-0017 | pending | 3 | 0 | 0 |
| BK--0826-0020 | pending | 3 | 0 | 0 |
| BK--1126-0007 | pending | 3 | 0 | 4 |
| BK--1126-0008 | pending | 3 | 0 | 4 |
| BK--1126-0010 | pending | 3 | 0 | 4 |
| BK--1126-0018 | pending | 6 | 0 | 7 |
| BK--1126-0019 | pending | 3 | 0 | 4 |
| BK--1126-0020 | pending | 4 | 0 | 5 |
| BK--1126-0021 | pending | 6 | 0 | 7 |
| BK--0926-0011 | posted | 0 | 0 | 0 |
| BK--0926-0014 | posted | 3 | 1 | 0 |
| BK--0926-0018 | posted, Refill Pettycash | 0 | 0 | 0 |
| BK--1126-0011 | posted | 3 | 1 | 4 |
| BK--1126-0015 | posted | 3 | 1 | 4 |
| BK--1126-0012 | posted | 7 | 5 | 8 |
| BK--1126-0027 | posted | 3 | 1 | 4 |
| BK--1126-0028 | posted | 3 | 1 | 6 |
| BK--1126-0029 | posted | 3 | 1 | 6 |
| BK--1126-0030 | posted | 3 | 1 | 4 |
| BK--1126-0031 | posted | 3 | 1 | 4 |
| BK--1126-0025 | posted | 3 | 2 | 5 |

Sebanyak 102 baris staging dan 36 baris accounting diperiksa; hanya suffix approval yang diganti dengan voucher pemilik sebenarnya. Teks rekening, Admin Charge, dan nomor dokumen RPC tetap. Perkiraan awal70 berubah menjadi71 setelah inventory menghitung seluruh baris, termasuk keterangan yang berulang. Dua kelompok posted tidak memerlukan perubahan.

## Alasan dan perlindungan

- Approval dan voucher memiliki namespace nomor yang dapat bertabrakan. Contoh: approval kasbon BK--1126-0011 membayar voucher BK--1126-0007, sedangkan voucher BK--1126-0011 adalah transport transaksi lain. Relasi approval asli dipertahankan melalui alias lengkap sebelum suffix diubah; resolver tidak bergantung pada keterangan baru untuk menebak pemilik.
- CLI `payment_reference_cli/repair_identity` menyediakan dry-run, apply, verify, rollback, dan verify-rollback. Cakupan 22 kelompok dibatasi dalam kode; mode apply memeriksa environment, hash backup, seluruh nilai awal, relasi approval, status, company/tanggal tunggal, balance, serta rincian accounting.
- Source dan alias memakai InnoDB. Accounting STM/SUSTAIN memakai MyISAM: koreksi dilindungi table lock dan kompensasi dari before-image bila gagal sebelum commit source. Commit tidak pasti menghentikan proses untuk rekonsiliasi. Tidak ada pemanggilan writer posting atau counter.
- Backup/manifest/baseline disimpan di luar document root dan tidak masuk Git. SHA256 backup: `D90D42BBE052978D9782472FFB550D2A84EB7E329C276FD010AB584AEE68EEEF`.
- Filter referensi milik voucher lain dari commit development `3cf0fe05` tetap diperlukan pada main; memperbaiki keterangan tidak menghapus ID approval dari jejak relasi. Regresi main juga membuktikan endpoint native Payment belum meneruskan ke Payment_posting seperti development. Paket main menyertakan filter, tes existing, dan delegasi khusus Payment dengan pemeriksaan POST/permission; jalur jenis jurnal lain tetap.

## Bukti validasi

- Rekonsiliasi READ ONLY sebelum apply: seluruh 12 kelompok posted memiliki rincian tanggal/COA/debit/kredit yang identik dengan staging setelah baris nol dikeluarkan; header balance dan voucher benar.
- Fixture SQLite: base679 + identity150 assertion PASS, termasuk penolakan GET oleh endpoint Payment tanpa business writes. MariaDB disposable: identity21 assertion PASS dengan InnoDB source/MyISAM accounting, apply/ulang/rollback serta kegagalan setelah partial accounting dan staging write. Tidak memakai DB aplikasi untuk tes tulis.
- Regresi PASS: foreign51, mapping47, mixed23, normalization464, posted467, serta base reference/posting679 yang digunakan suite. Mutasi pembanding suffix ditolak; file produksi tidak dimutasi.
- Verify-before remote PASS; apply mengonfirmasi22 kelompok. Verify-after READ ONLY PASS: source71/accounting15/alias80, seluruh profile Payment identik, tabel terlindungi identik, dan10 pending lolos inspectForPosting.
- Profile membandingkan status, voucher, approval, dokumen dan hasil kesiapan seluruh kelompok Payment sebelum/sesudah. Hash protected meliputi source di luar cakupan, payment_approve, tr_payment_paid, tr_jurnal_postings, tr_jurnal_payment_attempts, jurnaltras dan accounting umum (jurnal/japh/counter).
- Verifikasi exact per kelompok membuktikan seluruh kolom di luar keterangan/alias sama dengan backup;12 posted tetap posted. Tanpa browser, posting baru, reset, perubahan jurnaltras/accounting umum, atau normalisasi82 kelompok lama.

## Operasional

Simpan backup dan checkpoint bersama untuk verify/rollback. Rollback accounting hanya mengembalikan keterangan yang masih persis sesuai hasil koreksi; perubahan sesudah koreksi harus direkonsiliasi, bukan ditimpa. Tool ini khusus data historis batch tersebut; pembentukan keterangan transaksi baru tidak diubah.

Publikasi Git dan deployment adalah dua pemeriksaan berbeda. Hasil verifikasi database di atas memakai kode development dengan filter existing; versi aplikasi yang sedang berjalan di server belum diverifikasi. Hash publikasi dicatat pada checkpoint project.
