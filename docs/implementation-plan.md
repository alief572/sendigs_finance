## UMKM Non-PKP + card/ekspor tab Invoiced (INV-R-12 s.d. INV-R-25) — 10 November 2026

Acuan: `specs/docs/mockup-invoicing/` (FR UMKM-FR-01..07, NFR-01..05, mockup, ERD, serah-terima). Temuan awal: docs menyatakan UMKM-FR-01..06 "Ada", tetapi tidak ada kode UMKM di `development` (tidak ada `tax_treatments()`, `pph_final_self`, `PPH_FINAL_*_COA`, `Umkm_receipt_journal_test.php`; `Invoice_consulting::totals()` menolak selain PKP/WAPU/KB). Angka PKP/WAPU/KB mockup = kode asli (615.000 dicek), tidak ada regresi hitung.

Keputusan default (berlaku sampai user mengubah): COA Beban/Hutang PPh Final dikosongkan dan posting ditolak; setor sendiri + kurang bayar mencatat Hutang PPh Final penuh di penerimaan pertama (Excel kasus 4); Kawasan Berikat tidak berubah; baris PPN 0.00 tetap tampil di cetakan UMKM; faktur UMKM tetap wajib; tanpa migrasi (`tr_invoicing.tax_treatment` VARCHAR(8) sudah ada).

- Fase 0, dokumen kerja: rencana ini, checkpoint, status FR/serah-terima dikoreksi ke "Belum".
- Fase 1, invoice UMKM: `Invoice_consulting` (`tax_treatments`, `tax_suffix`, `totals`, `journal`), `consulting.js` (parity), `consulting_invoice.php`, `consulting-form.js`, `invoicing.css`, `consulting_print.php`.
- Fase 2, penerimaan hitung PPh Final 0,5%: `Penerimaan_calculation::tax_calc`, `Consulting_receipt::invoice_context`.
- Fase 3, jurnal PPh Final: konstanta COA kosong, `pph_final_self` di snapshot v4, `Invoice_consulting::receipt_journal`, `Consulting_posting::receipt` menolak baris tanpa COA, `Umkm_receipt_journal_test.php` (10 skenario Excel).
- Fase 4, UI/label Penerimaan: `add.php`, `consulting_process.php`, `detail.php`, daftar invoice, helper/CSS badge.
- Fase 5, tab Invoiced: card agregat, filter Bulan invoice/Perlakuan PPN/Semua tahun, ekspor `.xlsx` via `Xlsx_export`; fitur yang sudah ada (kolom Tanggal/Tim, badge Journaled, aksi Vuca) dipertahankan.
- Fase 6, mockup disesuaikan ke aplikasi; FR, README, serah-terima diperbarui sesuai hasil test.
- Fase 7, README modul, suite terdampak, checkpoint akhir.

Urutan ketat: 1 -> 2 -> 3 -> 4; Fase 5 independen dari 2-4. Detail dan bukti uji per fase: `docs/task-progress.md`. Rencana lengkap: `C:\Users\Febry\.claude\plans\sequential-percolating-coral.md`.

## Invoicing: tab Invoiced (INV-R-01, INV-FR-01/02) — 10 November 2026

Mengikuti `specs/input/Monitoring Piutang/Invoicing Tab Invoiced.html`. Daftar invoicing kini 3 tab: **Konsultasi** (hanya tagihan belum di-invoice, dropdown "Status tagihan" dihapus), **Non Konsultasi** (tetap), **Invoiced** (baru: filter Tahun, Bulan, Company; kolom Tanggal Invoice; ekspor Excel mengikuti filter; terbaru dulu).

- Fase 1, tampilan: `views/index.php` (tab, panel, `DataTablesInvoiced()`, ekspor), CSS tab membungkus di layar sempit.
- Fase 2, backend: `Invoicing_model::get_data_spk` (filter bulan dan company, kolom tanggal, urutan), `Invoicing::download_excel` (filter tahun/bulan/company, company mengikuti fallback daftar, kolom Tanggal Invoice).
- Fase 3, tes: `Ui_list_client_test.js` (acuan, tidak diubah), `Ui_render_test.php` (kontrak 3 tab), `List_filter_test.php` (baru), `Batch1_audit_test.php` (+2 skenario).

Detail, bukti uji, dan keterbatasan: `docs/task-progress.md`. Tidak ada migrasi.

## Rollout Audit Trail — Fase 5 batch 2 selesai (`penerimaan_uang` 18/18; Fase 5 18/28) — 10 November 2026

Batch 2 (`save_kwitansi_deposit`, `save_penerimaan_piutang`, `rollback_penerimaan`, `detail`, `view`, `view_surat`, `draft_rows`, `get_alokasi_penerimaan`) selesai; sukses simpan dan rollback invoice lama kini mengikuti hasil commit. Detail dan bukti uji: `docs/audits/phase-5-penerimaan-uang-batch-2.md`. Batch 3 (`penerimaan_pph_23`, `unlocated_penerimaan`, `historical_alokasi`) belum dikerjakan; mulai hanya jika diminta.

## Rollout Audit Trail — Fase 5 batch 1 selesai, cakupan direvisi — 10 November 2026

Fase 5 (Penerimaan dan riwayat) kini 28 endpoint, bukan 18 seperti baseline manifest 10 Oktober: `penerimaan_uang` bertambah dari 8 ke 18 (penyelarasan Penerimaan Piutang dan Deposit, 30 Oktober). Pembagian batch: 1 `penerimaan_uang` 10 endpoint baca/preview (selesai), 2 `penerimaan_uang` 8 endpoint termasuk semua jalur tulis, 3 `penerimaan_pph_23` + `unlocated_penerimaan` + `historical_alokasi` (10 endpoint). Detail dan bukti uji: `docs/audits/phase-5-penerimaan-uang-batch-1.md`. Manifest: `docs/audits/active-menu-audit-2026-10-10.json`. Batch 2 belum dikerjakan; mulai hanya jika diminta.

## Rollout Audit Trail — Fase 4 selesai, cakupan direvisi — 9 November 2026

Fase 4 (Penagihan dan invoice) selesai: 60/60 endpoint, bukan 58 seperti baseline manifest 10 Oktober; `invoicing` bertambah `coa_settings` dan `save_coa_mapping`. Urutan eksekusi: `plan_tagih` → `actual_plan_tagih` → `approval_invoice_so` → `invoicing` (urutan manifest lama menaruh `approval_invoice_so` terakhir). Detail, bukti uji, perubahan perilaku (sukses tulis mengikuti hasil commit), temuan di luar cakupan, dan baseline gagal: `docs/audits/phase-4-completion.md`. Manifest: `docs/audits/active-menu-audit-2026-10-10.json`. Fase 5 belum dikerjakan; mulai hanya jika diminta.

## Rollout Audit Trail — Fase 3 selesai, cakupan direvisi — 9 November 2026

Fase 3 (Expense dan petty cash) selesai: 130/130 endpoint, bukan 121 seperti baseline manifest 10 Oktober; 9 endpoint pengembalian/verifikasi Expense ditemukan belakangan dan ditambahkan. Pembagian batch berubah dari rencana awal (batch 8–9 Expense, petty cash 1–3) menjadi batch 8–14. Detail, bukti uji, perubahan perilaku (sukses tulis mengikuti hasil commit), temuan di luar cakupan, dan 4 kegagalan baseline: `docs/audits/phase-3-completion.md`. Manifest: `docs/audits/active-menu-audit-2026-10-10.json`. Fase 4 belum dikerjakan; mulai hanya jika diminta.

## Penyelarasan Penerimaan Piutang, Kwitansi Deposit, dan Monitoring Deposit ke mockup to-be — 30 Oktober 2026

Acuan: mockup `Penerimaan Piutang + Deposit Consultant.html` (UI/alur) dan `Deposit - SSC.xlsx` (jurnal Kwitansi Deposit: Piutang Dagang Usaha D / Deposit Customer K). Keputusan pengguna: saldo Monitoring Deposit = total masuk - total keluar per customer tanpa dimensi company; Kwitansi tetap terikat 1 SPK.

1. Fase 1: deposit_customer menjadi ledger baca-saja per customer (sumber tunggal `get_ledger_rows`, KPI dan tabel dari sumber sama, label sumber, detail 6 field + export).
2. Fase 2: kwitansi_deposit: kolom Saldo Piutang dihapus (10 kolom), urutan filter, export 9 kolom (Company, status berlabel, ikut search).
3. Fase 3: penerimaan_uang: label "Kwitansi Deposit"/"Deposit" di Report, header Nomor Invoice / Kwitansi, urutan kolom export, README. Mesin FIFO voucher, jurnal, jenis alokasi (id 8) tidak diubah.
4. Fase 4: regresi seluruh suite lokal + Consulting_integration_test (MariaDB disposable) + update dokumen.

Status: seluruh fase selesai (lihat docs/task-progress.md).

## Print lampiran PDF Periodik Full Halaman (A4) — 30 Oktober 2026

Tujuan: Di modul expense/periodik_print, jika attachment berupa PDF, tampilkan berkas PDF tersebut menjadi 1 halaman penuh (A4 utuh, proporsional, per halaman tanpa container iframe terpotong). Pola mengikuti arsitektur yang sudah terbukti pada transport_req_print.php dan assets/js/transport-print.js.

1. Fase 1: Analisis & Spesifikasi Pengujian (RED Test) - Buat unit test Node.js application/modules/expense/tests/Periodik_print_js_test.js.
2. Fase 2: Implementasi Script & View - Buat assets/js/periodik-print.js dan perbarui application/modules/expense/views/periodik_print.php.
3. Fase 3: Validasi & Regresi - Jalankan Periodik_print_js_test.js, Transport_print_js_test.js, PHP lint, Batch1_audit_test.php, dan git diff --check.

Status: Seluruh fase selesai. Kriteria keberhasilan terpenuhi 100%.

# Implementation Plan: Penyelarasan Jalur Posting Tab Pembayaran Jurnal All Mengikuti Entitas Jurnal Payment (VUCA)

Dokumen perencanaan teknis ini disusun untuk menyelaraskan jalur posting transaksi pada modul **Jurnal All (Tab Pembayaran)** agar mengikuti aturan entitas perusahaan yang benar sebagaimana diakui pada modul **Jurnal Payment**.

---

## 1. Analisis Kebutuhan & Hasil Investigasi

### Keputusan User
- Jalur yang benar adalah aturan entitas pada modul `jurnal_payment`, di mana company `STM-Vuca` diakui sebagai entitas **VUCA**.
- Jalur posting pada modul `jurnal_all` di tab Pembayaran harus diselaraskan 100% mengikuti aturan tersebut sehingga data `STM-Vuca` masuk ke **`db_sendigs_ss_vuca_dev` (`accounting_vuca`)**.

### Pemetaan Lengkap Multi-Entity
1. **ID 1 (`STM-Vuca`) & ID 4 (`Vuca`)** $\rightarrow$ Entitas **VUCA** $\rightarrow$ Database: `db_sendigs_ss_vuca_dev` (`accounting_vuca`).
2. **ID 6 (`STM-Sustain`) & ID 3 (`Sustain`)** $\rightarrow$ Entitas **Sustain** $\rightarrow$ Database: `db_sendigs_ss_sustain_dev` (`accounting_sustain`).
3. **ID 7 (`STM`)** $\rightarrow$ Entitas **STM** $\rightarrow$ Database: `db_sendigs_ss_stm_dev` (`accounting_stm`).

---

## 2. Rincian Perubahan Kode

### Modul Jurnal All
1. `application/modules/jurnal_all/libraries/Payment_reference.php`:
   - `target($company)`: petakan `['1', '4']` ke `accounting_vuca`, `['3', '6']` ke `accounting_sustain`, `['7']` ke `accounting_stm`.
2. `application/modules/jurnal_all/models/Jurnal_all_model.php`:
   - `getGlDb($companyId)` & `resolveGlMetadata(&$rows)`: selaraskan penentuan database accounting dan GL connection selector.

### Modul Jurnal Payment
1. `application/modules/jurnal_payment/models/Jurnal_payment_nomor_model.php`:
   - `get_no_buk()` & `get_Nomor_Jurnal_Sales()`: ambil counter BUK/JC dari database VUCA bila `$id_company == '4' || $id_company == '1'`.
2. `application/modules/jurnal_payment/controllers/Jurnal_payment.php`:
   - `_get_acc_db()`: arahkan `$id_company == '1' || $id_company == '4'` ke `accounting_vuca` (`DBACC_VUCA`).

### Rangkaian Pengujian & Test Fixtures
1. `application/modules/jurnal_all/tests/Payment_reference_test.php`:
   - Perbarui loop company target agar menguji ID 1 ke `accounting_vuca` dan ID 6 ke `accounting_sustain`.
2. Konfigurasi verifikasi histori dan read-only checks diselaraskan.

---

## 4. Status Implementasi: SELESAI (29 Oktober 2026)
- Seluruh perubahan pada `Payment_reference.php`, `Jurnal_all_model.php`, `Jurnal_payment_nomor_model.php`, `Jurnal_payment.php`, `Payment_reference_test.php`, dan `Legacy_posting_test.php` telah diimplementasikan secara komprehensif.
- Seluruh 15 test suite unit dan regresi telah dijalankan via container Docker `sendigs_ss` dengan status 100% PASS tanpa error.
- Checkpoint dan dokumentasi kemajuan pekerjaan telah dicatat di `docs/task-progress.md`.

## Deposit untuk invoice lama (2 November 2026)
Invoice lama tanpa histori penerimaan boleh memakai deposit customer (company pemilik jurnal, FIFO, jurnal debit 2102-01-02, snapshot `dep`, rollback memulihkan). Rencana lengkap dan pemetaan file: lihat `docs/task-progress.md` (bagian "Deposit untuk invoice lama") dan rencana sesi.

---

# Implementation Plan: Pembayaran Periodik per Item (mengikuti `specs/input/Periodik.html`) — 5 November 2026

Acuan UI: `specs/input/Periodik.html` (persis mockup). Requirement: `specs/docs/fr-nfr.md` (FR-01..12, NFR-01..06). Rencana lengkap disetujui pengguna; ringkasannya:

Keputusan pengguna: (1) 4 fase berurutan; (2) prefix item tetap `PI-YYYY-NNNNN`, lookup lama diperbaiki; (3) "Tidak diajukan ulang": dialog tetap terbuka bila alasan kosong dan item ditutup hilang dari form periode itu; (4) Alokasi COA dan tombol Proses `budget_periodik` dipertahankan tanpa perubahan.

Temuan teknis yang memengaruhi desain:
- `PI-` sudah dipakai invoice PO (`PI-YYYY-MM-NNNNNN`, `purchase_order_payment/models/Pr_model.php`). Nomor item periodik berbentuk `PI-YYYY-NNNNN` (5 digit setelah tahun), jadi dibedakan lewat pola `PI-____-__-%` (invoice) vs `^PI-\d{4}-\d{5}$` (item). Filter `%PI-%` di `Pembayaran_material_model.php` harus dipersempit ke pola invoice.
- `ms_budget_rutin.id` dipakai `pengajuan_rutin`, `pembayaran_rutin`, `jurnal_all`: persetujuan BG wajib update/insert berdasarkan id.
- Modul hilir (request_payment, approval/record RP, jurnal_all, audit_jurnal_payment, pembayaran_material) mengaitkan dokumen periodik lewat `no_doc` header. Dibuat helper tunggal `application/helpers/periodik_helper.php` untuk menerjemahkan `no_dokumen` (nomor item `PI-...` atau nomor batch lama) ke baris detail/header.

## Fase
1. **Fondasi**: migrasi (`pengajuan_rutin/migrations/002_*`, `budget_periodik/migrations/001_*`), penomoran atomik BP/BG/PI (tabel `ms_periodik_counter`, library `Periodik_numbering`), `periodik_helper`, adaptasi pembaca hilir. Kriteria: kedua cabang (item baru vs batch lama `no_item NULL`) terbaca di semua modul hilir; tes penomoran lulus.
2. **Pengajuan + Approval Pengajuan per item** (`p-list`, `p-form`, `p-appr`, stepper, revisi, tutup item).
3. **Budget Pembayaran Periodik** (`m-list`, `m-master`, Masa berlaku, tanpa baseline, perubahan lewat BG).
4. **Approval Perubahan Budget** (modul baru `approval_perubahan_budget`, menu + permission).

Migrasi SQL dijalankan manual oleh pengguna (DB dev remote); kode tidak mengasumsikan akses DB hidup.

# Modul Monitoring Piutang, Follow Up Piutang, Dashboard Plan Bayar — 6 November 2026

Acuan: `specs/docs/review-monitoring-piutang/` (fr-nfr.md, use-case.md, mockup, ERD). Keadaan awal: hanya migrasi `monitoring_piutang/migrations/001-018` yang ada; semua kode PHP dibangun dari nol. Rencana rinci: `C:\Users\Febry\.claude\plans\full-memoized-kurzweil.md`.

1. Fase 0: config `application/config/piutang.php`, cocokkan skema migrasi dengan spec, dokumen checkpoint.
2. Fase 1: library `Piutang_aging` (satu sumber aturan) + model + menu Follow Up Piutang + cek lunas di `Invoicing::save_close_invoice`.
3. Fase 2: menu Monitoring Piutang (card, list, rekap, detail, Excel).
4. Fase 3: Dashboard Plan Bayar (plan vs realisasi, Excel).
5. Fase 4: Surat Konfirmasi (profil STM), regresi, dokumentasi.

Di luar cakupan: write-off, invoice internal, profil surat non-STM, notifikasi, tab Invoiced di Invoicing (INV-FR-02: saat ini hanya tab Konsultasi + Non Konsultasi dengan filter status).
