# Payment: nomor staging dan referensi posting

## Kontrak

Hanya jenis tepat `Payment` yang memakai `payment_approve.id_payment` (dengan record `tr_payment_paid` valid) sebagai nomor transaksi staging dan `no_reff` header/detail posting Jurnal All. Nomor jurnal accounting tetap dihasilkan generator native. Nomor dokumen, misalnya KS, tetap menjadi No. Referensi dokumen di daftar; No. Reff pada detail preview mengikuti voucher yang akan diposting. Token `{REF:...}` dibersihkan dari salinan keterangan yang dikirim, tanpa mengedit keterangan sumber historis.

Jurnal baru dibentuk menggunakan voucher server, bukan input daftar approval. Daftar approval tetap dipakai oleh lookup bisnis dan auto-jurnal existing. Jenis lain, termasuk Refill Pettycash dan Payment Hutang Petty Cash, tidak dinormalisasi.

## Perlindungan histori dan retry

- Tidak ada backfill atau update terhadap kelompok posted, metadata posting, ataupun accounting historis.
- Pending dengan hasil `jurnaltras`, `accounting.jurnal/japh`, atau jurnal/header pada accounting tujuan dilewati. Pencarian memakai key lama, voucher, ID approval, dokumen sumber, dan semua token REF detail. Pembacaan yang tidak dapat dipastikan memblokir operasi.
- Company harus tunggal: 4 menuju VUCA, 1/6/7 menuju STM, 3 menuju SUSTAIN; company lain ditolak untuk Payment.
- Nomor yang cocok sebagai ID approval dan voucher tidak otomatis dianggap ambigu. Resolver memakai suffix ID approval yang tepat atau token REF tepat pada jurnal server untuk memilih relasi; token REF merupakan bukti sumber, bukan pengganti no_reff yang dikirim. Bila tidak ada bukti pembeda, hanya satu jalur dengan relasi record pembayaran valid boleh dipilih. Dua jalur valid tanpa pembeda atau bukti jurnal yang bertentangan tetap ditolak. Bukti yang menunjuk relasi invalid tidak boleh dialihkan ke jalur lain secara diam-diam.
- Voucher yang sudah ditentukan melalui approval sumber tidak ditafsirkan ulang sebagai nomor approval bebas. Metadata kategori/dokumen pada preview mengikuti approval hasil resolusi. Beberapa voucher dalam satu kelompok, target kelompok bertabrakan, status campuran/tidak diketahui, atau jurnal tidak balance tetap tidak diperbaiki otomatis.
- Companion dipisahkan berdasarkan hubungan approval, termasuk ketika Payment sudah canonical tetapi key companion masih daftar approval lama.
- Writer pembayaran, dispatcher, handler native Payment, dan patch memakai advisory lock `payment_reference:write` pada koneksi staging. Dispatcher menahannya sampai penulisan metadata selesai. Native mengecek ulang anchor dari server; input browser tidak menentukan voucher/tujuan.
- Registry `tr_jurnal_payment_attempts` dibuat oleh migrasi `007_payment_reference_attempts.sql`. Status started/committing/committed/reconcile memblokir percobaan ulang. Hanya failed_safe (kegagalan sebelum commit, rollback terkonfirmasi) yang boleh dicoba kembali. Jangan reset/hapus registry untuk mengulang transaksi tidak pasti.
- Staging dan registry, serta tabel tujuan `jurnal`, `japh`, `pastibisa_tb_cabang`, wajib InnoDB. MyISAM ditolak sebelum penulisan; konversi engine bukan bagian task ini.
- Registry claim disimpan sebelum accounting ditulis; accounting commit mendahului staging commit. Ini bukan transaksi atomik lintas koneksi. Crash/commit tidak terkonfirmasi tetap diblokir untuk rekonsiliasi, bukan dinyatakan sukses atau dicoba ulang otomatis.
- Kegagalan metadata/audit setelah posting tidak membatalkan hasil bisnis dan tidak membuka retry. Jalur dispatcher/recorder existing tetap menjadi pemilik satu hasil audit.

## Rollout dan CLI

1. Deploy kode kompatibilitas dan jalankan migrasi registry pada database staging menggunakan prosedur deployment project. Migrasi tidak dijalankan otomatis oleh request atau CLI.
2. Pastikan akses baca accounting tujuan dan koneksi `accounting` tersedia serta engine transactional memenuhi syarat. Jika tidak, data dilewati/posting diblokir.
3. Hentikan sementara pembayaran, posting dan perubahan jurnal pada lingkungan target. Lock melindungi jalur yang sudah diupgrade; tidak melindungi worker/kode versi lama atau perubahan SQL manual. Jangan campur versi saat patch.
4. Jalankan dry-run ke path JSON absolut di luar document root; gunakan folder dengan akses terbatas untuk operator. Jangan overwrite manifest hasil sebelumnya.

```text
php index.php jurnal_all/payment_reference_cli/index dry-run C:/secure/payment-review.json
php index.php jurnal_all/payment_reference_cli/index apply C:/secure/payment-review.json
php index.php jurnal_all/payment_reference_cli/index rollback C:/secure/payment-review.json
```

Tanpa mode, CLI default dry-run tetapi tetap memerlukan path manifest (gunakan `index dry-run <path>`). Endpoint menolak akses HTTP. Tidak ada apply otomatis saat deployment. Review setiap kandidat eligible sebelum apply; konsisten/posted/accounting_exists/ambiguous/collision/invalid/unverified/reconciliation_required tidak ditulis. Nominal, tanggal, COA, company, keterangan dan status tidak diubah oleh patch.

Manifest awal bersifat tetap. Setiap checkpoint ditambahkan sebagai satu baris JSON lengkap ke `<manifest>.journal`; kedua file wajib disimpan bersama. Library membaca keadaan terakhir dari journal, bukan hanya JSON awal. Ini menghindari overwrite rename yang ditolak pada Windows. Checkpoint incomplete/invalid menolak operasi; jangan memotong journal untuk memaksa retry. Fingerprint snapshot, ID eksplisit, environment hash, key/voucher/company diverifikasi lagi sebelum commit. Checkpoint commit_unknown ditulis sebelum update, sehingga proses terputus dapat diperiksa ulang. Jika proses berhenti ketika commit terjadi, rerun menguji fingerprint sebelum/sesudah dan bukti posting/accounting sebelum menyatakan no-op. Bukan berarti boleh mengulang posting.

Inventory dan apply/rollback memproyeksikan nomor tujuan terhadap baris jurnal server sebelum menulis. Jika perubahan nomor akan menghilangkan konteks sehingga kelompok tidak dapat dibedakan lagi dari approval lain, kandidat diblokir. Relasi pembayaran yang berubah setelah dry-run juga diperiksa ulang walaupun fingerprint tr_jurnal masih sama. Library tidak menambahkan marker atau mengubah keterangan historis untuk memaksa kandidat lolos.

Rollback mengembalikan nomor saja dan hanya untuk kelompok yang masih pending, bebas hasil accounting/metadata, mapping masih sama, fingerprint cocok, dan tidak menabrak kelompok lain. Kelompok yang sudah diposting setelah patch tidak boleh dirollback. Kegagalan satu kandidat menghentikan batch; checkpoint menjelaskan kandidat yang sudah commit.

5. Setelah apply, jalankan inventory baru dan periksa jumlah/nominal baris tetap, kandidat terubah sudah konsisten, dan daftar pengecualian tetap utuh. Aktifkan kembali penulisan setelah verifikasi. Jangan rollback kode lama setelah normalisasi tanpa mengecek kompatibilitas lookup voucher.

## Batas yang disengaja

Tombol Posting pada preview tetap terlihat tetapi nonaktif ketika pengguna mempunyai izin manage namun guard menolak; tooltip/warning menjelaskan alasannya. Report dan pengguna tanpa izin tetap tidak memperoleh aksi posting. Menampilkan tombol tidak mengubah can_post dan tidak mengizinkan submit keyboard/manual melewati guard. Bukti histori yang sudah ditemukan didahulukan dari registry yang belum dimigrasikan; prerequisite yang diketahui diberi pesan spesifik, sedangkan error tak dikenal tetap disamarkan.

Pemeriksaan aktual voucher BK--1026-0004 menemukan staging pending tetapi jurnaltras serta hasil accounting default sudah ada, dengan accounting VUCA masih kosong. Temuan read-only dan batas rekonsiliasinya tersedia di `docs/payment-bk-1026-0004-posting-diagnosis.md`; mengubah no_transaksi saja tidak membuat posting ulang aman.

Auto-jurnal saat pembayaran tetap memakai referensi lama. Oleh karena itu Payment baru/lama yang sudah memiliki hasil auto-jurnal akan diblokir di posting Jurnal All dan masuk laporan rekonsiliasi. Tidak menghapus atau menulis ulang hasil otomatis untuk membuka posting kedua.

Task ini menyiapkan kode dan alat patch; tidak menerapkan patch, migrasi atau perubahan engine pada database aplikasi. Bukti pengujian dan batas baseline tercatat di `docs/task-progress.md`.
