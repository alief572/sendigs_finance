# Validasi UI Add Invoice Vuca - 2026-10-21

Status: selesai lokal, tanpa browser, perubahan database produksi, migrasi, commit, atau push.

## Hasil dan keputusan

- Form tambah memakai workspace, grid customer/dokumen, TOP, akun penjualan, ringkasan dan tabel jurnal dari desain invoice reguler melalui CSS yang sama. Tidak membuat tema baru.
- Empat kategori penjualan memakai label kanonis dan mapping company sumber. Owner wajib Vuca (4); master akun dibaca dari accounting_vuca. Hanya mapping penjualan yang diperlukan, bukan sembilan akun pajak konsultasi.
- Preview mengganti kode/nama pendapatan saja. Server menyelesaikan kembali sales_role, sumber TOP, master dan nominal; hidden journal/COA tidak dipercaya.
- Endpoint Vuca memakai Invoice_writer yang sudah memiliki locking, validasi InnoDB, transaksi dan commit checking. Cabang reguler/non konsultasi tidak diubah; verifikasi metode controller terhadap HEAD mengonfirmasi hanya metode Vuca berubah.
- DPP tetap berasal dari plan-detail yang ditampilkan. PPh Final 0,5%, piutang DPP-PPh, PPN jurnal nol. Rp1.000.000 menghasilkan PPh Rp5.000 dan piutang Rp995.000. Tes mencakup nominal actual yang berbeda agar hasil simpan tetap sesuai preview.
- Field dokumen legacy pada create tetap memakai rumus semula. Edit mempertahankan field legacy tersimpan, akun pendapatan, dan perilaku dokumen opsional historis. Penambahan fingerprint mencegah edit dari form usang.
- Detail/edit memakai jurnal tersimpan agar COA terpilih terlihat konsisten. Perubahan ini diperlukan karena tampilan lama membangun kembali COA bawaan.
- Satu audit scope mencatat hasil setelah commit/rollback dengan allowlist invoice_type/outcome. SQL dan payload customer/form tidak dicatat. Penulis audit yang gagal tidak mengubah keberhasilan transaksi.

## Bukti pengujian

| Pemeriksaan | Hasil |
| --- | --- |
| Vuca_accounts_test.php | PASS 15: empat kategori tanpa mapping PPN; owner salah, mapping hilang/rusak, master hilang/duplikat, akun tetap, dan pendapatan historis ambigu |
| Vuca_render_test.php | PASS 25: escaping, field wajib/readonly, hidden compatibility, contoh perhitungan, empty state, label, scroll region dan local assets |
| Vuca_client_test.js | PASS 30: empat kategori, preview kode/nama, total tetap, invalid/prototype key, submit/busy guard, endpoint dan redirect |
| Consulting_integration_test.php, termasuk Vuca_integration_cases.php | PASS 532 terhadap database acak sementara MariaDB lokal port 33319; database dihapus pada finally |
| Consulting_calculation_test.php / .js | PASS 99 / 89 |
| Ui_render_test.php | PASS 254 |
| Ui_client_test.js / Ui_list_client_test.js | PASS 43 / 53 |
| Save_feedback_client_test.js | PASS 48, termasuk pending, double-submit, sukses, gagal, network dan fallback feedback |
| calculation_test.php / .js | PASS monetary/parity PHP-JS dan syntax script seluruh enam form |
| penerimaan_uang/tests/legacy_vuca_preview_test.php | PASS gross/net Vuca, MINARTA, PPh parsial, saldo, preview dan receipt snapshot |
| audit_trail/tests Recorder, Logger, Legacy, Controller, Query, Menu, Auth | Tujuh suite standalone PASS: deduplikasi, metadata aman, hasil faktual, izin, kegagalan writer dan shutdown fallback |
| Lint | PASS 11 file PHP dan 2 file JS; file sumber terakhir yang berubah diperiksa ulang |
| Daftar rilis | PASS 21 path tersedia |
| Scope controller | PASS seluruh metode non-Vuca identik terhadap HEAD |

Integrasi create menguji keempat sales_role, jurnal seimbang, manipulasi input,
TOP duplikat, kategori kosong/array/tidak dikenal, PO wajib, mapping salah owner,
form konfigurasi gagal dan sukses, audit yang gagal, serta rollback sequence,
header, journal, TOP dan commit. Integrasi edit menguji pemeliharaan COA,
fingerprint usang, jurnal posted, dokumen historis opsional dan rollback pada
delete journal, update invoice, insert journal dan commit. Data/jurnal lama serta
status TOP tidak berubah pada transaksi gagal. Setiap kegagalan menghasilkan satu
event audit gagal, dan commit gagal tidak menghasilkan status sukses.

Hasil pengujian yang dependensinya tidak berubah dipakai kembali sesuai AGENTS.md.
Tes UI tidak dijalankan melalui browser sesuai instruksi pengguna; pemeriksaan
layout dilakukan pada struktur render dan penggunaan CSS referensi yang sama.
Tidak mengklaim screenshot atau pemeriksaan visual browser telah dilakukan.

## Pemeriksaan desain

- Hard Gate PASS dalam cakupan source/fixture: label, escaping, kontrol aktif, empty/error/loading state dan wilayah tabel bergulir diverifikasi oleh tes render/client dan shared feedback.
- Purpose Gate PASS: grid dua kolom mengelompokkan dokumen dan customer; aksen biru, tabel, tipografi, tombol serta focus state memakai CSS invoice referensi. Tidak menambah aset, dekorasi atau navigasi baru.
- Liveliness PASS: ENERGY 1 / RHYTHM 1 / MOTION 1 untuk form operasional; ringkasan tagihan menjadi fokus, whitespace membagi kelompok data, dan interaksi mengikuti form yang sudah ada.
- Craftsmanship PASS dalam cakupan source/fixture: empat pilihan benar-benar memengaruhi jurnal tersimpan; server mengabaikan manipulasi hidden field; hasil sukses/gagal diuji sampai transaksi dan audit.

## Inventaris file sesi ini

Runtime (7):

- application/modules/invoicing/controllers/Invoicing.php
- application/modules/invoicing/libraries/Invoice_writer.php
- application/modules/invoicing/libraries/Invoice_vuca.php (baru)
- application/modules/invoicing/views/add_invoice_vuca.php
- application/modules/invoicing/views/edit_invoice_vuca.php
- application/modules/invoicing/views/vuca_journal_rows.php (baru)
- assets/js/invoicing/vuca-form.js (baru)

Tes (6):

- application/modules/invoicing/tests/Vuca_accounts_test.php (baru)
- application/modules/invoicing/tests/Vuca_render_test.php (baru)
- application/modules/invoicing/tests/Vuca_client_test.js (baru)
- application/modules/invoicing/tests/Vuca_integration_cases.php (baru)
- application/modules/invoicing/tests/Consulting_integration_test.php
- application/modules/penerimaan_uang/tests/legacy_vuca_preview_test.php (hanya fixture sesuai kontrak endpoint aktif; assertion sebelumnya dipertahankan)

Dokumentasi/daftar rilis (6):

- application/modules/invoicing/README.md
- application/modules/invoicing/RELEASE_FILES.txt
- docs/implementation-plan.md (catatan sebelumnya dipertahankan)
- docs/task-progress.md (catatan sebelumnya dipertahankan)
- docs/invoicing-vuca-ui-manifest.json (baru; endpoint dan hash runtime)
- docs/invoicing-vuca-ui-validation-2026-10-21.md (baru)

## Risiko dan tindak lanjut

- Company sumber harus mempunyai mapping penjualan dengan owner Vuca dan master COA tujuan lengkap. Tidak ada mapping yang ditulis otomatis.
- Actual plan tagih harus tersedia; writer mensyaratkan tabel transaksi InnoDB. Prasyarat yang belum terpenuhi menghasilkan pesan gagal.
- Field dokumen pajak legacy tetap berbeda dari PPN jurnal nol. Normalisasi data historis bukan bagian pekerjaan ini.
- Invoice historis dengan pendapatan ambigu atau COA yang hilang dari master ditolak untuk diedit; perlu rekonsiliasi data terkait.
- Tidak ada TODO implementasi. Pemeriksaan visual browser dan konfigurasi lingkungan operasional tidak dilakukan dalam sesi ini.

## Revisi whitespace berdasarkan screenshot pengguna

- Runtime yang berubah hanya assets/css/invoicing.css dan view add_invoice_vuca.php; seluruh perubahan CSS dibatasi selector .invoice-workspace.vuca-invoice. Blok CSS lama diverifikasi identik terhadap HEAD.
- Customer menjadi grid label/nilai yang lebih pendek. Field dokumen memakai align-start dan align-content-start, sehingga baris PO/faktur tidak lagi diregangkan mengikuti tinggi panel customer. Padding heading/body, jarak section, tabel dan ringkasan dipadatkan dengan ukuran kontrol tetap.
- TOP dan COA sekarang berada dalam satu kolom di samping ringkasan selebar maksimum 360px. Ini mengurangi ruang kosong di bawah COA dan mendekatkan jurnal ke bagian atas. Di <=1000px kedua kolom kembali ditumpuk; tabel tetap bergulir secara lokal.
- PASS: 25 render Vuca, 254 render invoice reguler, 30 client Vuca, lint PHP view, DOM grouping TOP/COA dan ringkasan sebagai sibling, pemeriksaan scope/braces CSS dan whitespace. Manifest memuat SHA256 view/CSS terbaru.
- Hasil 532 tes integrasi transaksi dipakai kembali: controller, writer, mapping, rumus, hidden field dan JavaScript tidak berubah. Tidak menjalankan browser, database, commit atau push.
- Arah desain tetap ENERGY 1 / RHYTHM 1 / MOTION 1. Pemadatan memperbaiki hierarki dan pemakaian ruang; warna, pola tabel dan focus state memakai referensi yang sama. Validasi visual browser tetap tidak dilakukan sesuai instruksi.
