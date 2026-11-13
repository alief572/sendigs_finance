# Filter periode Invoicing diperbaiki — 15 Oktober 2026

## Perilaku akhir

Pengguna mengklarifikasi tetap satu baris per invoice, bukan satu baris per COA. Khusus INV, invoice dipilih jika ada baris pending (atau posted untuk report) pada tahun/bulan terpilih. Query menggunakan HAVING SUM(CASE WHEN tanggal memenuhi periode THEN1 ELSE0 END)>0 setelah grouping. Tidak memakai WHERE periode yang memangkas detail dan tidak memakai overlap MIN/MAX yang bisa memasukkan tahun tanpa baris nyata.

Semua Tahun ditambahkan khusus tab INV; bulan dapat dipilih lintas tahun. Default/reset tetap tahun berjalan. Empat tab lainnya tetap memakai kontrak tahun wajib dan MIN date. Nilai tahun kosong hanya valid untuk INV; input tahun/bulan invalid tetap ditolak. Daftar, summary, opsi company, pencarian, report dan ekspor memakai seleksi yang sama.

Satu kelompok source tetap satu row/action. Tgl Jurnal/Tertua tetap tanggal pertama seluruh kelompok dan dapat di luar periode terpilih; hint UI menjelaskan ini. Nominal serta COA detail/Excel mencakup seluruh transaksi, termasuk baris di luar periode. Invoice lintas periode dapat muncul pada masing-masing filter tahun yang cocok; jumlah antarperiode tersebut tidak boleh dijumlahkan untuk menghitung invoice unik. Posting/nomor/company/counter/permission/audit tidak diubah.

## File

- application/modules/jurnal_all/models/Jurnal_all_model.php: validasi Semua Tahun INV dan HAVING match baris periode.
- assets/js/jurnal-all.js: opsi Semua Tahun INV, hint per tab, state/reset/export tetap.
- application/modules/jurnal_all/views/workspace.php: hint perilaku periode invoice.
- tests/Query_test.php, tests/Client_test.js, tests/Mysql_test.php: fixture lintas tahun/bulan, gap tanpa baris nyata, nominal/export utuh, posted, all-years, validation dan tab lain.
- README modul, plan/checkpoint/manifest dan laporan ini: kontrak dan bukti terbaru.

## Validasi

- RED: fixture lintas tahun gagal sebelum fix, karena invoice masih disaring dari MIN date.
- PHP suites: query70, controller76, dispatcher41, audit8, native posting168, petty89, revision24 PASS.
- Client41 PASS: Semua Tahun, export year kosong+bulan, state tersimpan per tab, tab lain tetap tahun wajib, serta loading race/keyboard/posting guards sebelumnya.
- MariaDB fixture terisolasi20 PASS: HAVING nyata lintas periode, all-years month, totals/export/detail utuh, posted dan migrasi/menu existing. Runner membersihkan schema/proses/datadir fixture.
- Total537 assertions fitur PASS. PHP lint4 dan JavaScript syntax aplikasi/test PASS; render workspace pending/report dan unique IDs PASS.
- Snapshot aplikasi via SELECT dalam transaksi READ ONLY+consistent snapshot, ditutup ROLLBACK: tahun2026 sekarang6 row/6 ID unik, listing6 dan summary6. Semua Tahun10, sama dengan10 ID legacy; legacy-only[]/combined-only[]. Dua source00112-INV-V-26 dan00114-INV-V-26 masing-masing tetap8 baris ekspor. Opsi company Semua Tahun5.
- Hash source handler/controller/dispatcher/preview/export dan dependensi nonubah tetap. Tidak ada write data aplikasi, accounting posting, tes browser, commit/push/deploy.

## Antislop delta gate dan batas

R-26/R-27/R-32/R-35 PASS dalam cakupan pengguna: opsi filter tersambung ke query/export/state riil dengan client41 dan fixture SQL; hint menjelaskan tanggal/totals lintas periode; keyboard/loading lama tetap lulus. R-25/R-03 PASS source: hint memakai ja-hint dan warna/responsive existing yang telah diaudit, tidak menambah layout/fixed width/asset/warna. Audit warna sebelumnya tetap berlaku; tidak mengklaim browser click-through.

Tidak ada TODO implementasi yang diketahui. Hasil read-only adalah snapshot lokal yang dapat berubah setelah aktivitas bisnis. Batas konsistensi lintas database/MyISAM tetap existing. Dokumen pemeriksaan sebelumnya merupakan kondisi sebelum fix.
