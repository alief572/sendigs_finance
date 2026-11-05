# Invoice konsultasi reguler versi 3 — 18 Oktober 2026

Endpoint reguler baru sekarang memakai Invoice_consulting_service untuk PKP/WAPU/Kawasan Berikat, snapshot mapping/owner, transaksi dan jurnal tanpa PPh saat invoice. Invoice lama, Vuca dan Non Konsultasi mempertahankan formula lama; mutex dan allocator nomor bersama melindungi interaksi antarjalur. Invoice_writer yang disebut dalam bagian historis di bawah bukan entrypoint aktif untuk semua enam endpoint.

Rilis memerlukan migration003 dan mapping admin sembilan COA. Laporan implementasi, manifest, pengujian dan batas: docs/invoicing-consulting-v3-2026-10-18.md dan docs/invoicing-consulting-manifest.json. Jangan menjalankan integration_test.php v2 historis sebagai bukti integrasi controller aktif; gunakan tests/Consulting_integration_test.php --isolated-local-db.

# Pencegahan piutang minus — 6 Oktober 2026

Enam endpoint save/update regular, Vuca, dan nonkonsultasi sekarang memakai
Invoice_writer. Invoice_calculation menghasilkan angka dalam sen dengan
pembulatan half-up. Saldo awal = DPP + PPN - PPh; gross tidak menjadi nol hanya
karena PPN nol. Aturan pajak aplikasi dipertahankan: DPP lain 11/12, PPN 0/12,
PPh regular/nonkonsultasi 2%, Vuca 0,5% dan PPN nol.

Sumber DPP konsultasi baru adalah actual plan tersimpan; saat edit adalah DPP
invoice tersimpan. Nonkonsultasi menjumlahkan total item yang sudah dibulatkan
dan biaya kirim, lalu memeriksa outstanding penawaran dengan mengecualikan
invoice yang diedit. Input harga menerima canonical decimal atau US grouping
yang dipakai autoNumeric; format Indonesia tidak ditebak. Quantity positif
maksimal dua desimal mengikuti kolom DECIMAL aktual, bukan dibulatkan ke integer.

Total turunan, company, nama COA, debit/kredit hidden tidak dipercaya. Company
dibaca dari penawaran/SPK dan master; empat COA harus lengkap. COA pendapatan
nonkonsultasi boleh berupa 4101-01-xx yang benar-benar ada di master. Edit tanpa
pilihan COA mempertahankan satu COA pendapatan existing yang dapat dipetakan.

## Transaksi dan kompatibilitas

- Endpoint dan respons status/msg tetap. Izin tambah/edit diverifikasi ulang.
- Semua writer memperoleh sendigs_receipt_v2 kemudian sendigs_invoice_write,
  sehingga generator nomor dan jurnal tidak bertabrakan dengan penerimaan.
- Edit mengunci invoice, riwayat penerimaan, lalu jurnal; fingerprint exact row
  dari form harus cocok. Invoice berpiutang berubah, memiliki penerimaan/jurnal
  penerimaan, atau jurnal invoicing posted tidak boleh ditulis ulang.
- Pembuatan mengunci sumber actual/plan atau penawaran sebelum menghitung ulang
  jatah. Sumber yang sudah dipakai tidak bisa diterbitkan ulang.
- Header, detail, jurnal, sequence, dan status plan berkomit bersama. Error
  nominal, COA, outstanding, insert, atau status transaksi menyebabkan rollback.
- Tabel mutasi dan sumber lock harus InnoDB; writer menolak prasyarat yang tidak
  terpenuhi. Audit aktual memastikan kedelapan tabel terkait InnoDB.
- Nomor jurnal bersama dihitung dari prefix no_jurnal pada periode yang tepat.
  Ini mengganti pembacaan MAX(id) dengan pola periode double-hyphen yang salah.
- Penerimaan_calculation mempertahankan wrapper helper invoice lama. Fallback
  gross Vuca dipakai hanya untuk tagihan, tidak mengganti saldo aktual.
- Tidak ada migration baru, backfill, atau koreksi historis pada pekerjaan ini.
  Koreksi khusus 036/VSB/2026 sebelumnya tidak dijalankan ulang.

## Pengujian tanpa web

Jalankan PHP 7.4 dan Node:

```
php application/modules/invoicing/tests/calculation_test.php
node application/modules/invoicing/tests/calculation_test.js
php application/modules/invoicing/tests/integration_test.php
php application/modules/penerimaan_uang/tests/calculation_test.php
node application/modules/penerimaan_uang/tests/calculation_test.js
php application/modules/penerimaan_uang/tests/integration_test.php
```

Integration test memakai database acak codex_invoice_test_* pada loopback/root
dan menghapusnya di finally. Menguji keenam endpoint asli dan generator model
asli, tax/cents, item/shipping, tamper, posted/receipt guards, stale forms,
nomor unik, rollback status plan/sequence, serta dua koneksi untuk create/edit.
Audit diuji dalam transaksi READ ONLY; bukti yang hilang tidak direkonstruksi.
Regresi penerimaan termasuk MINARTA, kedua COA PPh, partial PPh, rollback,
atomic failure, dan concurrent stale preview tetap dijalankan.

## Audit dan rilis

tools/audit_invoices.php hanya CLI. Menggunakan konfigurasi database yang ada,
consistent snapshot READ ONLY, dan adapter SELECT-only. Hasil pemeriksaan
aktual disimpan di tests/invoice_audit_readonly.json: 737 invoice, 529 ditandai
perlu rekonsiliasi. Flag meliputi bukti yang tidak terbaca/tidak dapat dipetakan,
jurnal penerimaan tanpa detail, saldo negatif, dan selisih nilai. Flag bukan
bukti bahwa seluruh invoice itu salah; koreksi harus berdasarkan bukti per
invoice. 036/VSB/2026 konsisten: saldo dan rekonstruksi 14.925.000, selisih nol.
Lima invoice Vuca gross nol sebelumnya tetap belum dikoreksi.

Kode baru masih di workspace. RELEASE_FILES.txt mencantumkan file runtime yang
harus diupload bersama, termasuk dependency lintas modul dan form fingerprint.
Pastikan perbaikan penerimaan versi 2 sebelumnya ikut dirilis jika server belum
memakainya; schema versi 2 sudah diterapkan pada database yang diaudit. Jangan
upload fixture, harness, tool koreksi, atau laporan bisnis ke folder publik.
Pengujian tidak membuka web dan deployment server belum dilakukan.
# Form tambah Invoice Vuca (2026-10-21)

## Dropdown aksi daftar invoice

Kolom Aksi pada tab Konsultasi dan Non Konsultasi memakai satu tombol dropdown.
`assets/js/invoicing/action-menu.js` mengelompokkan node aksi yang sudah dikirim
server, tanpa mengubah URL, handler cetak/modal, atribut data, atau aturan
status/izin. Menu terbuka dipasang pada body agar tidak terpotong area scroll
tabel, lalu dikembalikan saat ditutup. Keyboard panah/Home/End, Escape/Tab,
klik luar, scroll/resize dan redraw DataTables didukung.

Tes: `node application/modules/invoicing/tests/Action_menu_test.js` dan
`node application/modules/invoicing/tests/Ui_list_client_test.js`.
Laporan: `docs/invoicing-action-dropdown-2026-10-21.md`.

Tombol Pengaturan COA hanya tampil untuk admin (`Auth::is_admin()`, group 1)
yang memiliki izin Configure. Non-admin tetap ditolak meskipun memiliki izin
Configure: GET pengaturan menghasilkan 403 dan POST save mapping ditolak di
boundary transaksi/audit tanpa mengubah konfigurasi.

`add_invoice_vuca` memakai layout yang sama dengan `add_invoice`, tanpa pilihan
PPN. Empat `sales_role` mengikuti kategori `Invoice_consulting::revenues()`.
Mapping diambil dari `ms_invoice_coa_mapping` company sumber, wajib owner Vuca
(`4`), dan hanya akun penjualan yang diperiksa pada master `accounting_vuca`.
Mapping PPN tidak diperlukan untuk form ini. Konfigurasi yang hilang/salah
ditampilkan sebagai pesan error; form tidak menebak atau membuat akun baru.

`save_invoice_vuca` dan `update_invoice_vuca` menggunakan `Invoice_writer` dengan
koneksi accounting Vuca, validasi server, transaksi, locking, dan audit aman.
Request tambah wajib membawa `sales_role`, nomor PO, dan nomor faktur. Request
edit membawa fingerprint invoice; akun pendapatan diambil dari jurnal tersimpan,
sehingga hidden field tidak dapat mengganti COA. Halaman edit/detail menampilkan
jurnal tersimpan tanpa redesign.

DPP mengikuti TOP, PPh Final 0,5%, piutang DPP dikurangi PPh, PPN jurnal nol.
Field dokumen legacy `dpp_nilai_lain`, `pajak`, `total_akhir` pada create tetap
mengikuti rumus lama untuk kompatibilitas. Nilai tersebut bukan PPN jurnal dan
normalisasi data historis tidak termasuk perubahan ini. Tidak ada migrasi baru.

Tes tambahan (tanpa browser/database produksi):

```
php application/modules/invoicing/tests/Vuca_accounts_test.php
php application/modules/invoicing/tests/Vuca_render_test.php
node application/modules/invoicing/tests/Vuca_client_test.js
php application/modules/invoicing/tests/Consulting_integration_test.php
```

Suite integrasi terakhir mencakup `Vuca_integration_cases.php` dan hanya memakai
database acak sementara di MariaDB lokal port 33319. Laporan validasi sesi ini:
`docs/invoicing-vuca-ui-validation-2026-10-21.md`.

### Format print invoice konsultasi
Print invoice v3 memakai template `views/print_invoice.php` yang sama dengan invoice konsultasi legacy. `consulting_print.php` hanya mengadaptasikan snapshot menjadi data template: kop/company berdasarkan owner tersimpan, deskripsi memakai print_keterangan atau deskripsi TOP snapshot, nominal/PPN/total memakai invoice tersimpan. Template legacy tetap memakai fallback perhitungan dan format angka sebelumnya; v3 mempertahankan dua desimal. Tidak membaca ulang nilai TOP yang dapat berubah.

Regresi tanpa browser: `php application/modules/invoicing/tests/Print_invoice_test.php` (termasuk suite Ui_render_test). Laporan: `docs/invoicing-print-restoration-2026-10-21.md`.
