# FR & NFR — Perlakuan PPN UMKM Non-PKP (Invoicing + Penerimaan Piutang) dan Tab Invoiced

Dokumen terpisah dari FR & NFR Monitoring Piutang (INV-R-25). Cakupan:

1. **Invoicing**: perlakuan PPN baru **UMKM Non-PKP (Omzet ≤ Rp4,8 M, PPh Final 0,5%)** pada invoice konsultasi v3.
2. **Penerimaan Piutang**: jurnal penerimaan invoice UMKM dengan **PPh Final 0,5%** (dipotong customer atau disetor sendiri).
3. **Tab Invoiced**: card Total Invoiced, filter, dan Ekspor Excel `.xlsx`.

> **Status per 10 Nov 2026**: kode UMKM Non-PKP dan tab Invoiced (card, filter, ekspor `.xlsx`) sudah ada di branch `development` dan lulus test tanpa database. Sebelumnya dokumen ini menandai fitur "Ada" padahal kodenya belum ada; status di bawah sudah dicocokkan ulang dengan kode dan hasil test. Jalur simpan, rollback, dan posting terhadap database nyata **belum** diuji (lihat catatan terbuka 6). Rencana dan bukti: `docs/implementation-plan.md`, `docs/task-progress.md`.

Terakhir diperbarui: **2026-11-10**. Status "Ada" sudah dicocokkan dengan kode per tanggal ini.

**Dokumen pendamping**
- ERD: [`erd-umkm-non-pkp.drawio`](erd-umkm-non-pkp.drawio).
- Mockup: [`mockup-invoicing/`](README.md) — `index.html` (Invoicing + tab Invoiced) dan `penerimaan-piutang.html`.
- Serah terima ke IT (file berubah, pemasangan, test, UAT, use case): [`serah-terima-umkm-non-pkp.md`](serah-terima-umkm-non-pkp.md).

**Sumber**
- Permintaan: `INV-R-12` s.d. `INV-R-25` di [`requests.md`](../requests.md), bagian "UMKM Non-PKP".
- Bahan klien:
  - `input/Invoicing/Prosedur Jurnal Invoicing.xlsx` — jurnal invoice per perlakuan PPN; UMKM di kolom O–P.
  - `input/Invoicing/Prosedur Jurnal Invoicing & Penerimaan.xlsx`, sheet "Jurnal Penerimaan" — jurnal penerimaan; UMKM di kolom I–L.
  - `input/Invoicing/Invoice Eksisting.mp4` — tampilan Invoicing saat ini (dasar mockup).
- Kode: `application/modules/invoicing` (`Invoice_consulting`, `Consulting_posting`, view `consulting_invoice`, `consulting_print`), `application/modules/penerimaan_uang` (`Penerimaan_calculation`, `Consulting_receipt`, view `add`, `consulting_process`), `assets/js/invoicing/consulting.js`.

**Status**

| Status | Arti |
|---|---|
| **Ada** | Sudah ada di kode dan lulus test tanpa database (fixture / DB perekam, file .xlsx dibaca balik) |
| **Ada, belum diuji DB** | Kode ada, belum dijalankan terhadap database |
| **Belum** | Belum ada di kode |

## Aktor

| Aktor | Peran | Hak akses |
|---|---|---|
| Finance (Billing) | Membuat invoice UMKM, melihat tab Invoiced, mengekspor Excel. | `Invoicing.View/Add/Manage` |
| Finance (Penerimaan) | Mengalokasikan uang masuk ke invoice UMKM, memilih PPh Final dipotong / setor sendiri. | `Penerimaan_Uang.*` |
| Finance / Accounting | Memposting jurnal penerimaan; membuat COA PPh Final di master. | posting accounting |
| Sistem | Menghitung DPP/PPN/tagihan, batas PPh Final, jurnal, dan total card. | – |

## Functional requirements

### A. Invoicing

| ID | Aktor | Requirement | Kriteria terima | Sumber | Status |
|---|---|---|---|---|---|
| UMKM-FR-01 | Finance (Billing) | Pilihan perlakuan PPN **UMKM Non-PKP** di form invoice konsultasi. | Dropdown Perlakuan PPN: PKP / WAPU / Kawasan Berikat / **UMKM Non-PKP** (`Invoice_consulting::tax_treatments()`). Bila UMKM dipilih, tampil keterangan "UMKM Non-PKP tidak memungut PPN: DPP tetap dihitung, PPN 0, total tagihan sama dengan penjualan." Isian wajib lain tidak berubah (tanggal, PO, faktur, COA penjualan). | INV-R-12 | Ada |
| UMKM-FR-02 | Sistem | Hitung invoice UMKM. | Penjualan = nominal TOP; **DPP lain-lain = penjualan × 11/12 (tetap dihitung)**; **PPN = 0**; total tagihan = piutang awal = penjualan. Pembulatan half-up dalam sen, sama dengan perlakuan lain. Hasil PHP (`Invoice_consulting::totals`) dan JS (`consulting.js`) identik. Contoh: 615.000 → DPP 563.750, PPN 0, tagihan 615.000. PPh tidak dipotong saat invoice. | INV-R-15, INV-R-24 | Ada |
| UMKM-FR-03 | Sistem | Jurnal, data, dan label invoice UMKM. | **Jurnal** (`Invoice_consulting::journal`): **Piutang Dagang Usaha (D)** = penjualan dan **COA Penjualan (K)** = penjualan, tanpa baris PPN; deskripsi berakhiran `- UMKM Non-PKP`.<br>**Data** `tr_invoicing`: `tax_treatment = 'UMKM'`, `dpp_nilai_lain` = `dpp_lain_lain_jurnal` = penjualan × 11/12, `pajak` = `ppn_jurnal` = 0, `total_akhir` = `tagihan_ppn_jurnal` = `total_akhir_jurnal` = `saldo_piutang` = penjualan.<br>**Label** "UMKM Non-PKP" di cetakan invoice, badge TAX Code, daftar Penerimaan, dan Export Excel. | `Prosedur Jurnal Invoicing.xlsx` kolom O–P, INV-R-12 | Ada |
| UMKM-FR-07 | Finance (Billing), Finance / Manajemen | **Card Total Invoiced**, filter, dan **Ekspor Excel** di tab Invoiced. | **Letak:** paling atas tab Invoiced.<br>**Card:** Total Invoiced (Σ total tagihan; UMKM = penjualan) + jumlah invoice + teks filter aktif; Penjualan; PPN; **Saldo piutang** = Σ `tr_invoicing.saldo_piutang` (sisa saat ini, berkurang saat ada penerimaan).<br>**Filter:** Tahun (+ "Semua tahun"), Bulan invoice (12 bulan), **Company: STM, Vuca, Sustain**, Perlakuan PPN, dan pencarian. Card, tabel, dan ekspor memakai baris yang sama.<br>**Tabel:** kolom Nilai berisi Penjualan, PPN, Total tagihan, Saldo piutang.<br>**Ekspor `.xlsx`:** judul, baris filter, header No, No. Invoice, Tanggal Invoice (dd-mm-yyyy), No. SPK, No. PO, No. Faktur, Customer, Project, Company, TOP, Persen TOP, Perlakuan PPN, Penjualan, DPP Lain-lain, PPN, Total Tagihan, Saldo Piutang, Status; baris Total dengan rumus `SUM`; angka `#,##0.00`. Nama file `Invoiced_<tahun>_<bulan>_<company>_<PPN>.xlsx`. Tanpa baris → ekspor ditolak dengan pesan. | INV-R-21, INV-R-22, INV-R-23 | Ada, belum diuji DB (SQL `Invoicing_model::invoiced_rows` baru dijalankan lewat DB perekam; belum pada MySQL dan browser). Ekspor Konsultasi tetap `.xls` lama (`Invoicing::download_excel`, tidak diubah). |

### B. Penerimaan Piutang

| ID | Aktor | Requirement | Kriteria terima | Sumber | Status |
|---|---|---|---|---|---|
| UMKM-FR-04 | Finance (Penerimaan), Sistem | Penerimaan invoice UMKM memakai **PPh Final 0,5%**, bukan PPh 23. | **Batas PPh** = PPh Final 0,5% × penjualan (`Penerimaan_calculation::tax_calc`), contoh 2.000.000 → 10.000. PKP/WAPU/KB tetap PPh 23 2%. Pesan error menyebut "PPh Final 0,5%".<br>**Dipotong customer** (pilihan Y): PPh yang diinput mengurangi piutang dan dijurnal ke **Beban PPh Final (D)**, bukan PPh 23 Disetor.<br>**Tidak dipotong / setor sendiri** (pilihan N): bank menerima penuh; jurnal ditambah **Beban PPh Final (D) / Hutang PPh Final (K)** sebesar sisa batas PPh Final invoice (`pph_final_self`), dicatat penuh di penerimaan pertama (juga untuk kurang bayar, sesuai Excel).<br>**Kasus Excel** (admin 2.500), bank masuk dipotong / setor sendiri: DP 615.000 → 609.425 / 612.500; Progress 2.000.000 → 1.987.500 / 1.997.500; Lebih bayar 1.000.000 → 2.987.500 / 2.997.500 (+ Deposit Customer K 1.000.000); Kurang bayar 1.000.000 → 987.500 / 997.500 (Piutang D 1.000.000); Bank + Deposit 500.000 → 1.487.500 / 1.497.500 (Deposit Customer D 500.000).<br>**Batch campuran** PKP + UMKM: tiap invoice memakai PPh-nya sendiri. Deskripsi jurnal berakhiran `- UMKM Non-PKP`. | `Prosedur Jurnal Invoicing & Penerimaan.xlsx` sheet "Jurnal Penerimaan", INV-R-16 | Ada, belum diuji DB |
| UMKM-FR-05 | Sistem | COA PPh Final belum ada: jurnal tetap tersimpan, posting ditolak. | Akun **Beban PPh Final** dan **Hutang PPh Final** belum ada di master accounting, jadi nomor COA-nya dikosongkan (`Consulting_receipt::PPH_FINAL_EXPENSE_COA` / `PPH_FINAL_PAYABLE_COA` = `''`). Hanya dua akun ini yang boleh tanpa COA.<br>Penerimaan tetap bisa disimpan (jurnal draf); popup menampilkan peringatan COA kosong.<br>**Posting** (`Consulting_posting::receipt`) menolak jurnal yang punya baris tanpa COA ("Akun … belum memiliki nomor COA"). Setelah COA dibuat, isi kedua konstanta; jurnal draf lama tetap tanpa COA (catatan terbuka 1). | INV-R-12 ("kalo nama COA dan no COA belum ada dikosongin dulu aja") | Ada |
| UMKM-FR-06 | Finance (Penerimaan) | Label pilihan potong PPh. | Form Penerimaan Piutang: label **"PPh 23/PPh Final Disetor"** (sebelumnya "PPH23 Dipotong"), opsi "Dipotong PPh" / "Tidak Dipotong PPh"; pesan bila kosong "Pilih PPh 23/PPh Final Disetor: Dipotong atau Tidak Dipotong."<br>Kolom PPh di daftar invoice "PPH 23 / PPh Final"; di popup ditambah "/ PPh Final" dan "(UMKM 0,5%)" bila ada invoice UMKM. Baris UMKM diberi keterangan "PPh Final 0,5% dipotong customer" atau "PPh Final 0,5% setor sendiri: X". | INV-R-19 | Ada |
## Non-functional requirements

| ID | Berlaku untuk | Kebutuhan | Kriteria terukur | Sumber | Status |
|---|---|---|---|---|---|
| UMKM-NFR-01 | Invoicing, Penerimaan | Jurnal UMKM sama persis dengan Excel klien. | 10 skenario sheet "Jurnal Penerimaan" (5 kasus × dipotong / setor sendiri) menghasilkan total debit/kredit per akun sama dengan Excel dan seimbang (`Umkm_receipt_journal_test.php`, 212 cek). | INV-R-16 | Ada |
| UMKM-NFR-02 | Invoicing, Penerimaan | Perlakuan lain tidak berubah. | PKP/WAPU/KB: angka, jurnal, dan validasi sama seperti sebelumnya (test hitung, cetak, UI lulus). PKP menolak `pph_final_self`. Snapshot penerimaan lama tetap tervalidasi sama (`receipt_history`, rollback, posting). | Implementasi | Ada |
| UMKM-NFR-03 | Invoicing, Penerimaan | Server tidak mempercayai angka dari browser. | DPP/PPN/tagihan dan jurnal dihitung ulang di server; hasil PHP = JS (parity test). Batas PPh Final dicek di server. | Implementasi | Ada |
| UMKM-NFR-04 | Database | Tanpa perubahan skema. | Tidak ada migrasi: `tr_invoicing.tax_treatment` (VARCHAR(8)) sudah ada; PPh Final dipotong di `tr_penerimaan_piutang_detail.pph23`; `pph_final_self` di `consulting_snapshot` (calculation_version 4). | Implementasi | Ada |
| UMKM-NFR-05 | Tab Invoiced | Angka layar = angka Excel. | Card Total Invoiced = baris Total Excel = jumlah tabel untuk filter yang sama (0 selisih). | INV-R-21 | Ada (card dan ekspor memakai satu query `invoiced_rows`; `Invoiced_export_test.php` membaca balik file .xlsx dan mengevaluasi rumus SUM) |

## Aturan data

| | Invoice | Penerimaan, PPh dipotong customer (Y) | Penerimaan, PPh Final setor sendiri (N) |
|---|---|---|---|
| Nilai | DPP = penjualan × 11/12, PPN 0, piutang = penjualan | PPh = input user, maks. 0,5% × penjualan | PPh input 0; `pph_final_self` = sisa batas 0,5% |
| Debit | Piutang Dagang Usaha | Bank (uang − admin), Biaya Admin, Deposit dipakai, Piutang (sisa), **Beban PPh Final** (= PPh) | Bank, Biaya Admin, Deposit dipakai, Piutang (sisa), **Beban PPh Final** (= `pph_final_self`) |
| Kredit | COA Penjualan | Piutang Dagang Usaha (saldo), Deposit Customer (lebih bayar) | Piutang Dagang Usaha, Deposit Customer (lebih bayar), **Hutang PPh Final** (= `pph_final_self`) |

- Kontrol penerimaan tetap `((piutang − PPh − admin − sisa) − uang masuk − deposit) + lebih bayar = 0`; `pph_final_self` tidak ikut kontrol karena tidak mengurangi piutang.
- Invarian snapshot penerimaan: `balance − sisa = reduction`, `cash + admin + pph + dep = reduction + lebih`.
- Modul **Penerimaan PPh 23** (bukti potong) tidak membaca detail penerimaan v3 (`consulting_version = 3`), jadi PPh Final UMKM tidak muncul sebagai PPh 23.
- Company di Invoicing hanya **STM, Vuca, Sustain** (INV-R-23).

## Pemasangan

Tanpa migrasi. Deploy file yang berubah (daftar di [`serah-terima-umkm-non-pkp.md`](serah-terima-umkm-non-pkp.md)), bersihkan cache aset `assets/js/invoicing/*.js`. Setelah COA PPh Final dibuat di master accounting, isi `PPH_FINAL_EXPENSE_COA` dan `PPH_FINAL_PAYABLE_COA` di `application/modules/penerimaan_uang/libraries/Consulting_receipt.php`.

## Catatan / masih terbuka

1. **COA PPh Final** (UMKM-FR-05): nomor COA Beban PPh Final dan Hutang PPh Final dari accounting. Jurnal draf yang tersimpan sebelum COA diisi tetap tanpa COA: rollback + input ulang, atau koreksi langsung?
2. **Kurang bayar + setor sendiri**: Hutang PPh Final dicatat penuh di penerimaan pertama (sesuai Excel kasus 4), atau proporsional?
3. **Kawasan Berikat**: ikut PPN 0 seperti UMKM? Cetakan invoice UMKM: baris PPN 0.00 ditampilkan atau disembunyikan?
4. **Label "PPh 23" lain** di daftar dan detail Penerimaan ("Potong PPh 23", "TAX Code & PPh 23", "PPh 23 Dipotong (2%)", "Total PPh 23") diganti "PPh 23/PPh Final"?
5. **Tab Invoiced** (UMKM-FR-07): invoice Vuca dan invoice lama ikut tampil? Card dan ekspor `.xlsx` disetujui untuk dibuat di aplikasi?
6. **Belum diuji di database**: integration test penerimaan UMKM (simpan, rollback, posting) belum dijalankan; hanya di DB uji terisolasi.

## Test

| Bagian | Perintah | Butuh DB |
|---|---|---|
| Jurnal penerimaan UMKM = Excel | `php application/modules/penerimaan_uang/tests/Umkm_receipt_journal_test.php` | Tidak |
| Hitung invoice (PHP + parity JS) | `php application/modules/invoicing/tests/Consulting_calculation_test.php`, `node application/modules/invoicing/tests/Consulting_calculation_test.js` | Tidak |
| Cetak dan UI | `php application/modules/invoicing/tests/Print_invoice_test.php`, `php application/modules/invoicing/tests/Ui_render_test.php`, `node application/modules/invoicing/tests/Ui_client_test.js` | Tidak |
| Mockup | `node specs/docs/mockup-invoicing/smoke-test.js` (butuh jsdom) | Tidak |
| Integrasi | `php application/modules/invoicing/tests/Consulting_integration_test.php --isolated-local-db` | **Ya, hanya DB uji terisolasi** |
