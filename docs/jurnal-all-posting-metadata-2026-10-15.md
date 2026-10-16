# Metadata posting dan nomor final accounting — 2026-10-15

## Hasil dan kontrak

Selesai fase 1–4: posting dari Jurnal All menyimpan waktu posting WIB, ID/snapshot username auth, dan nomor final accounting. Report menampilkan kolom No. Jurnal Accounting, Tgl Posting, Diposting Oleh; detail, Excel dan pencarian ikut membaca metadata. Histori tanpa bukti metadata tampil literal `-`. Tidak ada backfill, renumber staging, posting accounting nyata, commit/push, atau penerapan ke environment lain.

Tabel InnoDB `tr_jurnal_postings` menggunakan SHA256 native group sebagai primary key, termasuk company pada Expense. Record immutable dan nomor company+number unik. Kolom accounting_numbers menyimpan JSON nomor hasil native: INV=Nomor_JV, RCV=Nomor_BUM, PAY=nomor cabang native JV/BUM, EXP=nomor hasil posting model, PCS=BUK single/refill atau dua nomor intercompany. Nomor identik di ledger berbeda tetap dibedakan label company. Excel mengulang metadata nomor tingkat transaksi pada detail COA; tidak mengklaim pemetaan nomor per baris COA.

## Keputusan integrasi

Algoritme, counter, database tujuan dan urutan commit handler asal dipertahankan. Perubahan handler hanya menambahkan nomor yang dihasilkan pada respons sukses sesudah commit terkonfirmasi. Dispatcher memeriksa schema dan auth actor sebelum delegasi, mempertahankan allowlist payload/route, lock kelompok, validasi status/balance/ID dan baca ulang.

Setelah native berhasil, metadata disimpan dalam transaksi InnoDB tersendiri dengan pemeriksaan begin/insert/status/commit. Native gagal tidak mencatat metadata. Writer metadata menonaktifkan db_debug sementara agar kegagalan insert bisa ditangani dan memulihkan nilainya sesudahnya. Jika accounting sudah sukses tetapi metadata gagal, business status tetap sukses dengan warning yang terlihat sesudah reload (`posted_metadata_missing`); retry accounting bukan pemulihan metadata. Audit gabungan tetap satu hasil per request, metadata audit terbatas outcome/count/reason, log teknis hanya hash kelompok; kegagalan writer audit tidak mengubah hasil bisnis.

Tanggal berasal dari jam server Asia/Jakarta sesudah respons native berhasil, bukan tanggal dokumen. Actor berasal dari auth server; browser tidak dapat memasok user/nomor final. Pembaca memakai query batch terpisah dari SUM agar total tetap. Tidak memanggil accounting untuk membaca report. Metadata hilang tidak diganti staging, created_date/by, atau user jurnal.

## File dan verifikasi

- Model/dispatcher Jurnal All, handler native INV/RCV/PAY/EXP/PCS, shared JS/workspace/preview/Excel, migrasi003/004, README dan manifest.
- Suite Jurnal All: audit10, controller86, dispatcher45, native198, metadata20, petty113, query126, revisi24, client46 assertions PASS.
- Expense: controller35, posting118, query24; Report Expense: controller42, query27, views12 assertions PASS. Suite terkait mencakup perbedaan company/routing/counter dan kegagalan write/commit, balance/permission/ID tampering, audit dedup/writer failure, warning UI, histori kosong dan escaping actor.
- MariaDB disposable:37 assertions PASS, termasuk migrasi metadata dua kali, InnoDB, pembacaan nomor/user/waktu/search, histori lama dan rollback metadata dengan source tetap ada. Runner menunggu proses server benar-benar keluar sebelum menghapus datadir untuk mengatasi lock log cleanup.
- PHP lint18 file, JavaScript syntax aplikasi/test, PowerShell parse, whitespace dan hash dependency diperiksa. Tidak ada pengujian browser sesuai instruksi pengguna.
- Migrasi003 diterapkan hanya di development lokal (hostname mysql melalui port lokal3307),9 kolom/InnoDB diverifikasi, jumlah baris jurnal/metadata sebelum-sesudah tetap. Tidak melakukan posting atau menambahkan metadata palsu. Rollback004 hanya diuji pada fixture disposable.

Antislop mengikuti mode sesi during dan arah visual existing. Perubahan UI hanya kolom metadata faktual, label nomor accounting, tanggal WIB dan warning; tidak mengganti palet/shell. Histori kosong memakai placeholder yang jujur.

## Batas dan tindak lanjut

Celah crash atau kegagalan storage antara native commit dan metadata commit tetap mungkin; warning memerlukan pemeriksaan administrator bila terjadi. Risiko MyISAM/lintas database existing tetap, tanpa klaim atomic accounting+metadata. Posting langsung di handler legacy tidak melewati pencatatan dispatcher gabungan. Histori tidak bisa diberi nomor final/tanggal/user secara andal tanpa bukti rekonsiliasi tersendiri. Environment lain harus menerapkan003 sebelum posting; rollback004 akan menghapus seluruh metadata. Tidak ada TODO kode dalam cakupan ini.
