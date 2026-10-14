# Implementasi Jurnal Expense — 14 Oktober 2026

Status: implementasi dan registrasi lokal selesai. Paket berhasil dipush ke branch development pada commit 1f4338959efe405b068e7a788c1d397be7d72466. Remote SHA diverifikasi; main tetap ee7f1e276c3c57a9390563f457b5394d955a834b. Tidak ada posting jurnal live atau pengujian browser.

## Perubahan

- Modul baru `application/modules/jurnal_expense`: controller lima endpoint, model query, model posting, tiga view, migrasi menu, README, dan tiga tes standalone.
- View index disalin dari Jurnal Payment; CSS identik. Permission view diganti menjadi Jurnal Expense; refresh setelah posting mempertahankan filter aktif. Jurnal Payment existing tidak diubah.
- Jenis yang diterima tepat `Expense Report` dan `Expense Report Consultant`. Pembacaan list/dropdown/export menerima `sts='0'`, `sts=''`, dan `sts IS NULL`. Grouping memakai nomor transaksi/jenis/company agar company berbeda tidak ikut terposting.
- Preview dan posting memeriksa ulang scope pada server. Group sebagian posted ditolak. Nilai jurnal berasal dari database; payload nominal/ID detail tidak dipakai. Preview menampilkan dua desimal dan reference existing.
- Jalur JV sama dengan cabang Expense Report Jurnal Payment: model nomor Sales existing, `javh`, `jurnal`, increment `nomorJC`, update source `sts=1`. Mapping company: 4 Vuca; 1/6/7 STM; lainnya Sustain. Tidak membuat entri payment approval atau membutuhkan dokumen Consultant berada di `tr_expense` lokal.
- Source transaction/row lock, advisory lock per schema/cabang, pemeriksaan nomor existing, validasi balance dalam cents, dan cleanup MyISAM pada kegagalan normal ditambahkan. Sukses hanya setelah commit. Counter dikembalikan secara kondisional jika cleanup berhasil; kegagalan cleanup ditandai untuk administrator.
- Recorder audit dipakai sekali per endpoint. Metadata outcome/reason/row_count/cleanup_ok; tidak merekam detail jurnal, SQL, atau kredensial. Writer failure tidak mengubah hasil posting.

## Registrasi lokal

- Menu481 `Jurnal Expense`, parent447 Finance, order8; Jurnal Payment tetap order7. Menu setelahnya bergeser satu tanpa mengubah urutan relatif.
- Permission1843–1846: `Jurnal_Expense.View/Add/Manage/Delete`. View untuk baca/export; Manage untuk preview/posting. Tidak memakai permission Request Payment atau Jurnal umum.
- Grant group/user disalin hanya dari suffix Jurnal Payment yang sama. Pemeriksaan menunjukkan nol grant ekuivalen yang hilang.
- Migrasi dijalankan dua kali: tidak ada menu/permission/grant duplikat atau pergeseran order kedua. Script memakai lookup dinamis, bukan ID lokal tetap.

## Bukti validasi

- Query test: **24 assertions PASS**, SQLite nyata + query builder CI. Cakupan: jenis/status termasuk NULL/kosong, company isolation/aliases, filter/export, search injection, pagination/counts, malformed input, ID invalid, partially posted group, tanggal inconsistent, rentang invalid, dan query failure.
- Posting test: **108 assertions PASS**. Dua jenis dan seluruh jalur company; header JV/detail/reference/counter/status, baris nol, cents, repeat posting, unbalance/negatif, invalid ID/jenis/status, lock/begin/read/branch/number collision/header/partial detail/source update/counter/transaction/commit failure, serta MyISAM compensation dan cleanup failure.
- Controller/view test: **35 assertions PASS**. Permission termasuk View-only, POST-only, lima endpoint/audit, query failure, failure setelah commit/cleanup, writer failure, payload nominal tidak dipercaya, CSS identik, preview dan export escaping.
- Regresi recorder/logger/legacy **PASS**; Expense Batch1 **37 fixture scenarios + 1 shutdown subprocess PASS**. Fondasi audit dan generator Expense tidak diubah.
- **14 file PHP lint PASS**: sembilan file modul baru dan lima file Jurnal Payment existing. Satu inline script lolos Node VM syntax setelah tag PHP dievaluasi/dihilangkan untuk harness. Manifest JSON valid; whitespace modul/dokumentasi PASS.
- Query **read-only MySQL lokal PASS** melalui model production: 25 transaksi pending tergroup. `ER-2026-00031` tampil satu transaksi, enam row sumber dengan status NULL, debit/kredit8800000.00. Tidak ada penulisan jurnal accounting selama tes.
- Diff Jurnal Payment kosong; CSS view kedua modul identik. Tidak mengubah Menu_generator atau pekerjaan lokal audit lainnya.

## Batasan

Tabel accounting tujuan lokal adalah MyISAM. Cleanup menangani kegagalan biasa, tetapi bukan transaksi atomik lintas engine: crash proses/server, putus koneksi saat commit, dan paralel posting dari modul legacy yang tidak mengikuti advisory lock tetap berisiko. Perubahan engine atau koordinasi posting semua modul merupakan pekerjaan terpisah. Tidak memigrasikan engine dalam task ini.

Publikasi task ini hanya ke development. Paket mencakup modul Jurnal Expense, tes, migrasi menu, manifest, dan dokumentasi task; main dan perubahan lokal lain tidak masuk cakupan. Migrasi menu perlu diterapkan pada database development ketika modul dipasang; push Git tidak menjalankan SQL otomatis.
