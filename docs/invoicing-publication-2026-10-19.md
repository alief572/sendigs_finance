# Publikasi Modul Invoicing ke Branch Development — 19 Oktober 2026

## Ringkasan Eksekutif
Sesuai arahan pengguna, seluruh perubahan pada modul **Invoicing** beserta aset terkait (CSS/JS), test suite, dan dokumentasi dipublikasikan ke branch `development` di remote repository GitHub (`https://github.com/alief572/sendigs_finance.git`).

Publikasi ini sekaligus menuntaskan pembersihan sisa marker konflik merge Git (`<<<<<<<`, `=======`, `>>>>>>>`) pada controller, model, dan views modul invoicing yang sebelumnya sengaja dikecualikan pada saat publikasi parsial commit `2d4ff08b`.

## Cakupan Berkas yang Dipublikasikan
Total 27 berkas terkait langsung dengan modul Invoicing dan dokumentasinya:

### 1. Controllers, Libraries, & Models
- `application/modules/invoicing/controllers/Invoicing.php`
  - Pembersihan total sisa marker konflik merge Git.
  - Integrasi form konsultasi v3, validasi owner jurnal canonical (STM 7, Sustain 3, Vuca 4).
  - Peningkatan sanitasi keamanan XSS pada rendering data invoice.
- `application/modules/invoicing/libraries/Invoice_consulting.php`
  - Penambahan validasi `is_numeric()` pada receipt amount dan pengetatan schema jurnal (`nm_company`, `code`, `name`).
- `application/modules/invoicing/models/Invoicing_model.php`
  - Pembersihan sisa marker konflik merge Git.
  - Perbaikan query penomoran invoice terkunci dan lookup customer/SPK.

### 2. Views
- `application/modules/invoicing/views/consulting_invoice.php`
  - Integrasi feedback notifikasi SweetAlert2 saat simpan invoice v3.
- `application/modules/invoicing/views/consulting_settings.php`
  - Restriksi company owner jurnal hanya ke canonical ID (7 STM, 3 Sustain, 4 Vuca).
  - Integrasi SweetAlert2 saat update konfigurasi COA.
- `application/modules/invoicing/views/index.php`
  - Compact table tab Konsultasi: 7 kolom terarah (No, Dokumen [Invoice+SPK], Customer & Project, Company, Tim [Project Leader+Sales], Status, Aksi) dengan escaping teks XSS lengkap.
  - Reset state guard untuk DataTables dan pembersihan sisa marker konflik.
- `application/modules/invoicing/views/print_kwitansi.php`
  - Perbaikan sanitasi XSS (`htmlspecialchars`) pada `nm_customer`, `no_invoice`, `no_po`, dan `print_keterangan`.
  - Koreksi malformed tag `<table class="body-table"">`.
  - Format angka desimal sesuai versi consulting.
- `application/modules/invoicing/views/print_kwitansi_vuca.php`
  - Perbaikan sanitasi XSS (`htmlspecialchars`) pada seluruh variabel yang dicetak.

### 3. Assets (CSS & JavaScript)
- `assets/css/invoicing.css`
  - Styling scoped untuk tabel compact tab konsultasi dan tata letak status tagihan.
- `assets/js/invoicing/calculation.js`
  - Logika kalkulasi desimal/sen murni dengan parity PHP.
- `assets/js/invoicing/consulting-form.js`
  - Form handler konsultasi v3 dengan integrasi SweetAlert2.
- `assets/js/invoicing/consulting.js`
  - Validasi ketat non-numerik pada nominal pelunasan/receipt (`Number.isFinite`).
- `assets/js/invoicing/save-feedback.js`
  - Helper notifikasi SweetAlert2 (status sukses `status = 1`, penguncian tombol simpan, penanganan pesan error aman).

### 4. Test Suites
- `application/modules/invoicing/tests/Consulting_calculation_test.php` (99 checks)
- `application/modules/invoicing/tests/Consulting_calculation_test.js` (89 checks)
- `application/modules/invoicing/tests/Consulting_integration_test.php` (341 checks)
- `application/modules/invoicing/tests/Save_feedback_client_test.js` (48 checks)
- `application/modules/invoicing/tests/Ui_client_test.js` (43 checks)
- `application/modules/invoicing/tests/Ui_list_client_test.js` (53 checks)
- `application/modules/invoicing/tests/Ui_render_test.php` (254 checks)
- `application/modules/invoicing/tests/calculation_test.js` (Parity & syntax check)

### 5. Dokumentasi & Manifest
- `docs/invoicing-compact-table-2026-10-18.md`
- `docs/invoicing-save-feedback-2026-10-18.md`
- `docs/invoicing-consulting-manifest.json`
- `docs/invoicing-publication-2026-10-19.md`

## Berkas yang Dikecualikan (Tetap Lokal)
- `application/config/constants.php`: Konfigurasi nama database lokal tanpa sufiks `_dev`.
- Modul audit & review expense lokal (`application/modules/expense/`, `audit_trail/`, dll.): Pekerjaan aktif lokal yang belum selesai dan berada di luar cakupan task invoicing.

## Hasil Validasi Sebelum Publikasi
1. **PHP Syntax Lint**: 34 berkas PHP pada `application/modules/invoicing/` diperiksa dengan `php -l` — **100% PASS** (No syntax errors detected).
2. **JavaScript Syntax Lint**: 10 berkas JavaScript pada `assets/js/invoicing/` dan test suite diperiksa dengan `node -c` — **100% PASS**.
3. **Unit & Integration Tests**: Total **927 assertions** lulus 100% (exit code 0):
   - `Consulting_calculation_test.php`: 99 PASS
   - `Consulting_calculation_test.js`: 89 PASS
   - `Ui_render_test.php`: 254 PASS
   - `Ui_client_test.js`: 43 PASS
   - `Ui_list_client_test.js`: 53 PASS
   - `Save_feedback_client_test.js`: 48 PASS
   - `calculation_test.js`: PASS
   - `Consulting_integration_test.php`: 341 PASS
   - `legacy_vuca_preview_test.php`: PASS
