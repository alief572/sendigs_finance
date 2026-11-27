## Perbaikan keamanan request_payment: upload dan SQL injection (25 November 2026)

Fase: (1) whitelist upload + `.htaccess`; (2) escape/binding SQL; (3) blokir `request_payment_backup`; (4) test dan dokumentasi. Kriteria: input normal berperilaku sama, input berkutip tidak mengubah SQL, script tidak bisa diunggah atau diakses dari `assets/expense/`. Di luar cakupan: temuan audit lain dan modul lain yang memakai `allowed_types = '*'`.

## Request Payment → Payment: specs RP-R-36..39 + UI mockup (25 November 2026)

### Context
Spec `specs/docs/request-payment/` di-update di commit `e5cc29a9` (RP-R-36..39, 24–25 Nov 2026). Kode yang sudah ada (`2298698e`, flag `RP_TRANSFER_ENABLED`, migrasi 012/013) baru mencakup RP-R-01..35. Perubahan RP-R-36..39:
- RP-R-36/38: **Payment Bank = nominal transfer ke penerima** (bukan total keluar bank). Bank Charge = biaya yang dipotong bank. Jurnal **Cr Bank 2 baris (TRF + BIAYA TRANSFER)**. Administrasi Bank otomatis = Payment Bank + Bank Charge − Total Payment (Dr kalau ditanggung perusahaan, 0 kalau ditanggung penerima, Cr kalau Payment Bank diperkecil padahal bank tidak memotong). Gratis = transfer penuh, charge 0, tanpa baris admin. "Kontrol" dan "Isi otomatis" dihapus.
- RP-R-37/39: Record Payment mendapat tombol **Voucher** (Bukti Bank Keluar format "BBK (2)", Debit/Kredit, print → PDF) dan **Preview Jurnal** di View.
Ditambah atas permintaan user: **UI semua halaman disamakan dengan mockup** (selisih RP-R-01..35 hasil perbandingan view vs mockup).

Keputusan user (25-Nov-2026): cakupan UI semua halaman; voucher ikut mockup (logo + TTD Tati Rina / Fikri Darmawan A hanya STM, Sustain/Vuca nama company + TTD kosong); no voucher dihitung saat cetak (tanpa migrasi); catatan "Dipecah otomatis" tidak dibuat.

Yang sudah benar dan **tidak** diubah: `Rp_transfer::totals()` (`bank` = transfer + admin = total keluar bank) sudah sesuai RP-R-36 untuk Atur Pembayaran, Approval, Excel JUMLAH DIBAYARKAN BANK, ringkasan per company. Mode `free` sudah memberi transfer penuh + charge 0.

Langkah awal tiap sesi: catat rencana ini di `docs/implementation-plan.md` dan checkpoint di `docs/task-progress.md` (aturan AGENTS.md). File lain yang sedang dimodifikasi di workspace (voucher repair, piutang, dsb.) tidak disentuh dan tidak ikut commit.

### Fase 1 — Jurnal & Form Payment (RP-R-36/38)
`application/modules/pembayaran_material/libraries/Rp_transfer_payment.php`
- `actual()`: input `payment_bank` = transfer (default = transfer rencana sesuai mode aktual dari `Rp_transfer::totals()`), boleh diedit, wajib > 0. `bank` (total keluar) = payment_bank + charge; `adjustment` = payment_bank + charge − net (rumus sama dengan sekarang, jadi tidak ada perubahan skema). Hapus syarat kontrol `payment_bank === received + charge`. Pertahankan: mode `free` → charge harus 0. `difference` = mode beda ∨ charge ≠ default ∨ payment_bank ≠ transfer rencana. Kembalikan juga `transfer` = payment_bank.
- `journal()`: ganti 1 baris Cr Bank jadi 2: `"{bank} - TRF ke {nama}"` (kredit = payment_bank) dan `"{bank} - BIAYA TRANSFER ke {nama}"` (kredit = charge; dilewati bila 0 lewat `$add`). Label admin: "Biaya Transfer - {no dokumen}" (Dr) / "Admin dipotong dari transfer, bank tidak memotong biaya - {no dokumen}" (Cr).
- Penyimpanan tetap: `payment_approve.payment_bank` = total keluar bank (`actual['bank']`), sehingga Jurnal All, laporan lama dan `Rp_transfer_record::comparison()` (`received = payment_bank − charge`) tidak berubah.

`application/modules/pembayaran_material/views/transfer_payment.php`
- Payment Bank default = `planned['transfer']`, hint "nominal transfer ke penerima (baris TRF di jurnal)".
- Hapus baris Kontrol + tombol Isi otomatis. Tambah baris: Transfer ke penerima (= Payment Bank, tanda oranye bila beda dari pengajuan), Bank Charge (hint default sesuai mode), Total keluar bank (transfer + charge), Administrasi Bank (otomatis + keterangan Dr/0/Cr).
- JS `refresh()`: tidak lagi memblok pada kontrol; validasi Payment Bank > 0, keterangan wajib bila mode/charge/transfer beda. Saat ganti admin aktual: charge = default mode, payment_bank = transfer mode.

Tes: update `request_payment/tests/Rp_transfer_test.php` (baris 53–63, 116–123): kasus ditanggung penerima (2.000.000 − 2.500), ditanggung perusahaan, gratis, Payment Bank diperkecil → Cr admin, 2 baris Cr bank, balance.

### Fase 2 — Record Payment: Preview Jurnal + Voucher (RP-R-37/39)
`application/modules/record_request_payment/`
- `libraries/Rp_transfer_record.php`: fungsi `journalRows($db, $idPayment)` membaca `tr_jurnal WHERE no_transaksi = id_payment` (jurnal yang benar-benar disimpan, bukan dihitung ulang); fungsi `voucherNo($db, $group)` = `YYYY-{COMPANY}/BBK/{romawi}/{urut 2 digit}`, urut berdasarkan `id_payment` di antara transfer paid company + bulan `tgl_bayar` yang sama.
- `controllers/Record_request_payment.php`: `payment_detail` kirim jurnal; method baru `print_voucher($id)` (permission view yang sama, audit seperti `payment_detail`).
- `views/payment_detail.php`: samakan isi dengan modal mockup: tabel info (No. Pengajuan/Transfer/company, Tgl Bayar + bank, Transfer ke, Keterangan, Bukti bayar), tabel perbandingan (label "Bank charge (dipotong bank)", baris beda kuning), tabel dokumen (Dokumen, Kategori, Request By, Dibayarkan), **Preview Jurnal** (Company, COA, Nama Account, Keterangan, Debit, Kredit, Balancing; baris bank berlatar), tombol Voucher di bawah.
- `views/print_voucher.php` baru: HTML mandiri A4 portrait sesuai `vcHtml` mockup (min 7 baris, Total Debit/Kredit, Disetujui/Kasir), `window.print()` saat load. Logo STM: pakai `assets/images/logo_sentral.png` bila sama dengan logo di mockup, kalau tidak, simpan logo base64 mockup sebagai file di `assets/images/`. Nama TTD STM sebagai konstanta di view (shortcut: pindah ke config bila TTD berganti).
- `views/record_payment.php`: header jadi NO. PAYMENT, NO. PENGAJUAN, PENERIMA, DOKUMEN, REQUEST PAYMENT, PAYMENT, PERBEDAAN, KETERANGAN PEMBAYARAN, ACTION; baris beda diberi tanda; tombol View + Voucher; isi kolom Request Payment/Payment sesuai mockup ("sesuai pengajuan" / "tanpa admin").

### Fase 3 — UI halaman lain (RP-R-01..35, hasil perbandingan)
Hanya pada cabang transfer (`Rp_transfer::enabled()`), pakai komponen/kelas yang sudah dipakai view sekitarnya.
1. **Request Payment** `request_payment/views/rp_index.php`: catatan "Kolom ADMIN per dokumen dihapus… Dibayarkan = DPP + PPN − PPh".
2. **Atur Pembayaran** `atur_pembayaran.php` + `assets/js/request-payment-transfer.js`: hint asal rekening (otomatis dari dokumen / diubah / gabungan isi ulang), datalist bank (dari master bank yang sudah dipakai sistem), cek klien admin ditanggung penerima < dibayarkan + pesan "Rekening tujuan belum lengkap", judul "Penerima dokumen: …" bila nama kosong, kalimat intro, step admin 1.
3. **Approval** `approval_request_payment/views/detail.php` (+ controller bila perlu data asal rekening): kartu JUMLAH TRANSFER; Rincian Transfer 7 kolom (tambah DOKUMEN, TOTAL DIBAYARKAN) + badge jumlah + kalimat penjelasan; rekening tampil read-only dengan tombol **Edit rekening** → modal, catatan "diubah approver, dari: …" (data asal: rekening saat diajukan; cek apakah sudah tersimpan di `tr_rp_transfer`, kalau tidak, bandingkan dengan rekening dokumen); kolom TRANSFER ke posisi 2; kalimat footer.
4. **Record Request Payment**: `index.php` kolom JUMLAH TRANSFER, urutan tombol export; `items.php` kolom TANGGAL PENGAJUAN + APPROVER, badge "N lampiran tambahan", urutan kolom mockup; `detail.php` kartu JUMLAH TRANSFER, Rincian Transfer dengan DOKUMEN + TOTAL DIBAYARKAN, "STATUS BAYAR", catatan; `transfer_export.php` jadi modal di halaman Batch yang refresh saat filter berubah (endpoint data JSON dari logika filter yang ada), kolom Diputuskan, "Transfer ikut x dari y", Status PGJ, Dibayarkan, catatan; info Excel memuat status, kategori, daftar PGJ.
5. **List Request Payment** `pembayaran_material/views/list_request_payment.php`: transfer jadi baris tabel utama (bukan kotak kedua), kolom Tgl Approve + Currency, angka = Jumlah Transfer (`planned['transfer']`), checkbox di kolom terakhir dengan aturan 1 payment = 1 transfer, tombol header Back/Clear/Proses bekerja pada transfer, catatan beda PGJ lengkap.

Setiap sub-langkah = 1 batch kecil, update checkpoint setelah selesai.

### Verifikasi
- Lint: `docker exec -w /var/www/html sendigs_ss php -l <file>` untuk tiap PHP yang diubah; `node --check assets/js/request-payment-transfer.js`.
- Tes (container PHP, DB tes terisolasi), flag aktif dan tidak aktif: `request_payment/tests/Rp_transfer_test.php`, Batch1–5, Audit_test; `approval_request_payment/tests/Audit_test.php`; `record_request_payment` Audit, Tabs*, Items_export, `Tabs_client_test.js`; `pembayaran_material` Batch1–2, Selection, Payment_rounding*, Periodic_tax_settlement, View_payment_new, Journal_validator. Tambah tes untuk `journalRows`/`voucherNo` dan perhitungan Fase 1.
- Uji web tidak dilakukan (instruksi user); `node specs/docs/request-payment/smoke-test.js` hanya sebagai acuan perilaku mockup.

### Risiko / TODO
- Jurnal transfer yang sudah tersimpan dengan pola lama (1 baris Cr bank) tetap apa adanya; Preview Jurnal & Voucher menampilkan apa yang tersimpan.
- No voucher dihitung saat cetak: berubah bila ada payment transfer yang dihapus di bulan yang sama.
- Migrasi 012/013 + flag belum aktif di development/production (di luar cakupan, sama seperti rilis `2298698e`).
- Commit/push hanya bila diminta user; file workspace lain yang belum di-commit tidak ikut.

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

## Pembulatan PPh per item — 16 November 2026

Menggantikan rancangan pembulatan bank. PPh 23 = ceil((request + PPN) × 2%); PPh 21 = ceil((request + PPN) × 2,5%), per item ke rupiah penuh. Hitung ulang dari jenis saat load, perubahan jenis/PPN, preview, dan simpan; PPh tetap readonly. Pilihan kosong = 0. Parser integer sen dipakai ulang agar batas rupiah tepat; komponen lain tidak dibulatkan.

1. Fondasi: helper hitung PPh integer dan total payment server, tes per jenis/pecahan/bulat/invalid. Selesai bila perhitungan tepat dan payload PPh berbeda ditolak; kompatibilitas kode legacy dan petty cash dijaga.
2. Integrasi: sinkronkan form/preview/writer/jurnal; hapus ceiling bank, baris Pembulatan, konfigurasi COA dan field respons khususnya. Pertahankan guard preview, normalisasi bank, deduplikasi bank/admin, balance, commit dan audit. Selesai bila preview sampai accounting memakai PPh/bank identik tanpa COA pembulatan.
3. Verifikasi: adaptasi tes pembulatan PHP/JS, regresi pembayaran/jurnal/selection dan kegagalan tulis/query/commit/audit, lint/whitespace. Perbarui checkpoint tiap fase. Tanpa browser, DB live, migrasi, perubahan historis, commit/push atau perubahan workspace lain.

Kriteria: request 1.000.028 + PPN 0, PPh 23 20.001, payment 980.027 sebelum admin. Multi-item dibulatkan per item. Payment masih bisa pecahan bila request/PPN/admin pecahan; kontrol tetap harus nol tanpa toleransi selisih.

Status: fase 1–3 selesai. PPh form/preview/simpan dan jurnal tersinkron; konfigurasi serta jurnal pembulatan bank dilepas. Tidak perlu pengisian COA pembulatan untuk memakai aturan PPh ini. Bukti tes dan keterbatasan: checkpoint bagian Pembulatan PPh per item.

Publikasi atas permintaan user: siapkan commit terisolasi dari tip terbaru development dan main, sertakan hanya kode/tes/dokumen PPh dan fixture yang dibutuhkan; jangan ikutkan konfigurasi DB lokal atau perubahan task lain. Verifikasi paket masing-masing branch, lalu push fast-forward tanpa force dan cek hash remote.

## Petty Cash PC-R-01..10: implementasi spec (27 November 2026)

Rencana lengkap: `C:\Users\Febry\.claude\plans\nah-jadi-di-folder-squishy-flurry.md`. Cakupan A penuh + B terbatas. Status dan bukti: `docs/task-progress.md` bagian "Petty Cash PC-R".

1. Fase 0-1: grounding, COA Petty Cash di master (`petty_cash_master`), jurnal memakai COA master (selesai).
2. Fase 2: Saldo Petty Cash, selisih, peringatan di Pelaporan/Approval (selesai).
3. Fase 3: jurnal pencatatan dibuat saat Approve, idempoten terhadap jurnal lama (selesai).
4. Fase 4: Preview Jurnal di Approval dan Konfirmasi, paritas dengan jurnal nyata (selesai).
5. Fase 5: Payment Hutang: COA Hutang ke STM/Piutang per company, bank pembayar dan Bank STM penerima dipilih saat Payment (selesai).
6. Fase 6 (tampilan template Jurnal All) tidak dikerjakan; penyamaan Payment dengan RP-R-36..39 ditunda sesuai spec.
