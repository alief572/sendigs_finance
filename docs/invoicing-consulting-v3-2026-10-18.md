# Invoice konsultasi reguler — versi perhitungan 3

## Hasil dan batas

Implementasi pada branch development. Invoice reguler baru memakai PKP/WAPU/Kawasan Berikat; daftar tetap per TOP dan satu TOP satu invoice. Vuca, Non Konsultasi, serta invoice historis tidak dikonversi. Perhitungan lama tetap terpisah. Tidak ada commit, push, deploy, backfill, perubahan konfigurasi database, atau migration pada database aplikasi.

Penjualan berasal dari actual TOP tersimpan; DPP = penjualan ×11/12, PPN = DPP ×12%, half-up per tahap dalam sen. Total tampilan selalu penjualan+PPN, termasuk WAPU/KB sesuai keputusan user. Piutang PKP adalah total tersebut; piutang WAPU/KB hanya penjualan. PPh invoice nol; penerimaan baru mencatat nominal PPh aktual yang diinput user. Snapshot menyimpan owner company, COA, pilihan penjualan dan rincian TOP; perubahan mapping/master tidak mengubah invoice existing.

Sumber konsep: Invoice Consulting.xlsx dan Invoice Consulting.html dari folder OneDrive user. Data contoh/kode placeholder bukan konfigurasi produksi. Dasar implementasi adalah endpoint aktif, bukan klaim writer v2 di README historis.

## Integrasi dan API

- GET invoicing/coa_settings: Invoicing.Configure; company sumber dan owner dipilih eksplisit; sembilan role dipetakan ke COA master accounting tujuan. Permission baru hanya diberikan kepada admin user7 pada migration, tanpa menyalin grant user lain. Akses melalui tombol Pengaturan COA Invoicing.
- POST invoicing/save_coa_mapping: source_company_id, owner_company_id, accounts[role], mapping_fingerprint. Menolak placeholder, master ambigu/tidak ada, role akun piutang/pajak yang sama, pemilihan penjualan memakai akun piutang/pajak, dan form stale.
- add_invoice, save_invoice, view_invoicing, edit_invoicing, update_invoice, print_invoicing dan download_excel: rute existing; save reguler membuat v3. Payload baru: id_detail_plan_tagih, tanggal_invoice, nomor_po, nomor_faktur, tax_treatment (PKP/WAPU/KB), sales_role, source_fingerprint; edit memakai id_invoicing dan invoice_fingerprint. Nomor invoice edit tetap, create digenerasikan server. Nomor mengikuti periode generator existing, tidak direservasi saat preview. Respons status/msg dipertahankan.
- Form baru menampilkan identitas SPK/TOP, empat perhitungan, COA penjualan, tanggal/company/akun/deskripsi/debit/kredit dan balancing. Cetak invoice memakai snapshot/angka tersimpan; kwitansi v3 menampilkan dua desimal. List Excel menambah perlakuan dan nominal v3.
- Penerimaan process_alokasi/save_penerimaan_piutang memilih versi per invoice, termasuk batch campuran. Modal baru memakai input decimal canonical dengan dua desimal, pph_actual_N default0, receipt_fingerprint_N dan allocation_fingerprint. Old-only mempertahankan modal/formula existing. PPh aktual tidak mengikuti toggle header yang hanya mengendalikan baris legacy.
- Penerimaan baru: pengurangan = kas+admin+PPh aktual; debit bank/admin/PPh dan kredit piutang. Baris legacy dalam batch campuran tetap memakai formula legacy. Setiap detail menyimpan pengurangan dan snapshot owner/akun/jurnal. Penggunaan alokasi ditambah, dicek terhadap riwayat penerimaan, saldo split dan nominal induk bank; tidak ditimpa.
- Rollback batch v3 mengembalikan pengurangan persis, wajib urutan cicilan terbaru, menolak saldo/jurnal/snapshot tidak konsisten atau posted, dan mempertahankan split saudara. Legacy-only tidak boleh menulis/rollback parent alokasi yang masih dipakai batch v3; ini mencegah overwrite nilai_terpakai dan penghapusan split oleh kode lama. Histori alokasi tidak konsisten memerlukan rekonsiliasi, bukan dikoreksi otomatis.
- Posting baru memakai snapshot dan memvalidasi jurnal tersimpan. Penerimaan diposting per owner company; jurnal harus cocok persis dengan snapshot detail. Fix-company massal melewati batch v3. Endpoint Vuca/non tidak dapat mengedit invoice baru; edit legacy yang sudah masuk batch campuran juga ditolak.
- Modul Penerimaan PPh23 lama tidak menampilkan/menerima pengakuan kedua untuk detail v3. PPh aktual sudah menjadi debit aset pajak pada jurnal penerimaan; workflow bukti WAPU maupun desain ulang bukti PPh tidak ditambahkan.

## Transaksi, concurrency, dan audit

Mutex sendigs_receipt_v2 kemudian sendigs_invoice_write dipakai konsisten oleh penulis/rollback/posting yang berinteraksi. Alokasi nomor invoice memakai UPDATE atomik LAST_INSERT_ID untuk semua pemanggil, termasuk legacy. Create mengunci TOP/actual, memeriksa duplikat seluruh invoice, dan memiliki unique consulting_top_key untuk v3. Server menghitung ulang nilai dan mengabaikan hidden nominal/company/jurnal browser.

Header/detail/jurnal/sequence/status TOP berkomit bersama. Edit menolak fingerprint stale, penerimaan, saldo berubah dan posted. GL baru ditulis menggunakan koneksi finance yang sama dengan schema accounting terpilih, sehingga commit/rollback meliputi kedua schema. Posting membaca ulang di dalam mutex; stale ID setelah edit ditolak.

Audit Module_audit_recorder pada mutasi baru hanya mengizinkan version/outcome dan metadata recorder; SQL tidak dicatat. Sukses dicatat setelah commit. Scope nested tidak diselesaikan dua kali dan kegagalan writer audit tidak membatalkan commit atau menghasilkan event fallback palsu.

## Migration dan prasyarat rilis

1. Cadangkan database sesuai prosedur operasional, lalu jalankan application/modules/invoicing/migrations/003_consulting_v3.sql pada database finance/ERP sebelum kode baru dirilis. File aditif/idempotent; tidak mengubah nominal/data historis atau engine tabel existing. ms_invoice_sequence dipertahankan bila sudah ada.
2. Pastikan tabel mutasi finance InnoDB: tr_invoicing, tr_jurnal, kons_tr_actual_plan_tagih, kons_tr_plan_tagih_detail, ms_invoice_sequence, ms_invoice_coa_mapping, tr_penerimaan_piutang, tr_penerimaan_piutang_detail, tr_alokasi_detail, tr_alokasi_split, tr_kartu_piutang. Posting memerlukan accounting.jurnal, javh/jarh, dan pastibisa_tb_cabang juga InnoDB.
3. Finance dan accounting tujuan harus server MySQL yang sama; identitas server diperiksa. Akun koneksi finance harus memiliki akses ke schema tujuan. Jika prasyarat tidak terpenuhi, posting ditolak, bukan dikirim melalui transaksi lintas koneksi yang tidak atomik. Perubahan engine/privilege bukan bagian deployment otomatis.
4. Rilis semua runtime file dalam manifest bersama. Jangan merilis tes, scratch, portable runtime atau data fixture. Admin mengisi mapping sembilan role dan owner sesuai company depan, kemudian user berizin Add/Manage memakai form baru.
5. 003_consulting_v3_rollback.sql menolak menghapus schema ketika transaksi v3 sudah ada. Setelah transaksi baru dipakai, pertahankan schema dan lakukan penanganan operasional; jangan downgrade kode sambil menghapus snapshot. Tidak ada konversi otomatis ke versi lama.

## Bukti validasi tanpa browser

Runtime: PHP7.4.33 resmi portable dalam scratch/runtime/php74; MariaDB10.11.10 disposable di loopback33319. Harness hanya memakai database acak codex_consulting_* dan menghapusnya pada finally; tidak memuat kredensial aplikasi. Server pengujian terpisah dari Docker/database aplikasi.

- Consulting_calculation_test.php: 72 checks, contoh Excel WAPU291.000.000/PKP19.581.600/KB60.000.000, sen, batas angka, jurnal empat pilihan pendapatan, validasi dan piutang/PPh.
- Consulting_calculation_test.js: 35 checks parity PHP/JS, batas parsing, dan penerimaan aktual; kalkulasi frontend dan backend identik hingga nilai ekstrem yang didukung.
- Consulting_integration_test.php --isolated-local-db: 303 checks native CI+MariaDB, migration dua run/admin grant/rollback guard, endpoint mapping/create/edit, stale/duplicate/permission/input/engine/schema, snapshot immutability, audit writer failure/allowlist, cetak/form/settings/XSS, endpoint Excel dan SQL report, penerimaan/cicilan/actual PPh/admin/overpayment/rollback/mixed/posting, penolakan company jurnal yang berubah dan legacy PPh ganda.
- Fault injection: sequence, header insert, journal insert, TOP status dan commit invoice; commit penerimaan dan GL; tidak ada perubahan parsial pada saldo/alokasi/counter/status/sequence.
- Dua proses native: alokasi nomor serentak menghasilkan nomor berbeda; TOP sama hanya satu commit; edit vs posting tidak dapat memposting jurnal yang sudah diganti.
- Regresi PHP invoice calculation, receipt calculation, active legacy Vuca/MINARTA, detail journal PASS. Native Invoice/Receipt/Payment198, Jurnal All query126/dispatcher45/controller86/audit10/metadata20/revision24 PASS.
- Tujuh suite audit non-live (Recorder/Logger/Legacy/Controller/Query/Menu/Auth) PASS; Recorder termasuk subprocess shutdown nyata. Runtime php.ini lokal ditambahkan agar child process memuat mbstring; bukan perubahan aplikasi.
- Tujuh suite Report Piutang Per Invoice PASS; sebagian suite ini berbasis properti/mirror, sehingga bukti query aktif tambahan berasal dari integration native di atas.
- JS legacy invoice parity/enam form, receipt calculation/inline syntax, installment view, Jurnal All client48 PASS. PHP lint25 file dan syntax script baru serta scoped diff whitespace PASS.

Perintah utama dari root project:

```powershell
& scratch/runtime/php74/php.exe application/modules/invoicing/tests/Consulting_calculation_test.php
& scratch/runtime/php74/php.exe application/modules/invoicing/tests/Consulting_integration_test.php --isolated-local-db
& 'C:/Users/Febry/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/bin/node.exe' application/modules/invoicing/tests/Consulting_calculation_test.js scratch/runtime/php74/php.exe
```

Tes integrasi memerlukan server disposable pada port33319, bukan server aplikasi. Path runtime ini hanya dukungan lokal. Saat penutupan implementasi, lingkungan produksi, akun/master aktual, hasil visual browser dan migration aplikasi belum diuji/diterapkan. Seluruh implementasi selesai; prasyarat rilis di atas harus dipenuhi sebelum penggunaan.

Migration database aplikasi lokal kemudian diterapkan atas permintaan user pada 18 Oktober 2026 pukul 11:19 WIB: `003_consulting_v3.sql`, database `db_sendigs_ss`, service Docker mysql lokal dengan port host 127.0.0.1:3307. Sebelas statement berhasil, sembilan kolom nullable dan indeks unik TOP tersedia, dua tabel pendukung tersedia, permission `Invoicing.Configure` diberikan ke user7. Backup skema dan data tabel terdampak beserta hasil verifikasi disimpan di `C:/Users/Febry/AppData/Local/Temp/sendigs-local-migration-003-20261018-111906/` di luar web root. Fingerprint data kolom lama identik sebelum/sesudah: 747 invoice, 679 header penerimaan, 715 detail penerimaan, dan 3 sequence. Mapping COA masih kosong; admin harus mengisi company pemilik jurnal dan sembilan mapping aktual melalui `invoicing/coa_settings`. Prasyarat engine/privilege GL posting belum diverifikasi pada eksekusi migration ini. Tidak ada pengujian browser, konversi histori, commit/push, atau deployment remote.
