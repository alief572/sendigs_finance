# Serah Terima ke IT — Perlakuan PPN UMKM Non-PKP

Perlakuan PPN baru **UMKM Non-PKP (Omzet ≤ Rp4,8 M, PPh Final 0,5%)** di menu **Invoicing** (invoice konsultasi v3) dan **Penerimaan Piutang**, plus **card Total Invoiced + Ekspor Excel `.xlsx` di tab Invoiced** (bagian 9).
> **Status per 10 Nov 2026**: kode dan test di dokumen ini sudah ada di branch `development`. Sebelumnya dokumen ditulis seolah kode sudah ada padahal belum; daftar file dan hasil test di bawah sudah dicocokkan dengan repo. Belum diuji: jalur simpan, rollback, dan posting terhadap database nyata, serta tampilan di browser.

Tanggal: **2026-11-10** (terakhir diperbarui: DPP UMKM tetap dihitung, INV-R-24). Branch: `development`.

| Dokumen | Isi |
|---|---|
| [`fr-nfr-umkm-non-pkp.md`](fr-nfr-umkm-non-pkp.md) | FR & NFR (dokumen sendiri, terpisah dari Monitoring Piutang): **UMKM-FR-01 s.d. UMKM-FR-07, UMKM-NFR-01 s.d. UMKM-NFR-05**, aturan data, catatan terbuka |
| [`erd-umkm-non-pkp.drawio`](erd-umkm-non-pkp.drawio) | ERD sendiri: tabel yang dipakai invoice dan penerimaan UMKM; kolom yang isinya bertambah ditandai ungu (*). Tidak ada tabel/kolom baru. |
| [`mockup-invoicing/`](README.md) | Mockup satu paket: Invoicing (`index.html`, termasuk tab Invoiced dengan card dan Ekspor Excel) + Penerimaan Piutang (`penerimaan-piutang.html`) |
| [`requests.md`](../requests.md) | Permintaan asli user: INV-R-12 s.d. INV-R-24 |
| `application/modules/invoicing/README.md`, `application/modules/penerimaan_uang/README.md` | Catatan teknis modul (bagian "… UMKM Non-PKP — 10 November 2026") |

Sumber bisnis (bahan klien, jangan diubah):
- `specs/input/Invoicing/Prosedur Jurnal Invoicing.xlsx` — jurnal invoice, kolom O–P.
- `specs/input/Invoicing/Prosedur Jurnal Invoicing & Penerimaan.xlsx` — sheet "Jurnal Penerimaan", kolom I–L.
- `specs/input/Invoicing/Invoice Eksisting.mp4` — tampilan Invoicing saat ini.

## 1. Aturan bisnis

### Invoice

| | PKP | WAPU | Kawasan Berikat | **UMKM Non-PKP** |
|---|---|---|---|---|
| DPP lain-lain | penjualan × 11/12 | sama | sama | **penjualan × 11/12** (tetap dihitung) |
| PPN 12% | DPP × 12% | sama | sama | **0** |
| Total tagihan | penjualan + PPN | penjualan + PPN | penjualan + PPN | **penjualan** |
| Piutang awal | penjualan + PPN | penjualan | penjualan | **penjualan** |
| Jurnal | Piutang D / PPN Keluaran K / Penjualan K | PPN WAPU dibayar dimuka D / Piutang D / Hutang PPN WAPU K / Penjualan K | Piutang D / Penjualan K | **Piutang D / Penjualan K** |
| Akhiran deskripsi jurnal | – | – | ` - Kawasan Berikat` | ` - UMKM Non-PKP` |

Contoh: penjualan 615.000 → DPP 563.750, PPN 0, tagihan = piutang = 615.000; jurnal Piutang Dagang Usaha D 615.000 / Penjualan K 615.000.

### Penerimaan piutang

Pilihan **"PPh 23/PPh Final Disetor"** di form Penerimaan (Dipotong PPh / Tidak Dipotong PPh) berlaku per batch:
- invoice PKP/WAPU/KB: **PPh 23 2%** × penjualan → akun **PPh 23 Disetor (D)** (tidak berubah);
- invoice UMKM: **PPh Final 0,5%** × penjualan.

| Invoice UMKM | Dipotong customer (Y) | Tidak dipotong, setor sendiri (N) |
|---|---|---|
| Bank (D) | uang − admin − PPh Final | uang − admin (penuh) |
| Biaya Admin (D) | admin | admin |
| Deposit Customer (D) | deposit dipakai | deposit dipakai |
| Piutang Dagang Usaha (K) | saldo piutang | saldo piutang |
| Piutang Dagang Usaha (D) | sisa piutang (kurang bayar) | sisa piutang |
| Deposit Customer (K) | lebih bayar | lebih bayar |
| **Beban PPh Final (D)** | PPh Final dipotong | PPh Final 0,5% |
| **Hutang PPh Final (K)** | – | PPh Final 0,5% |

Kasus di Excel (admin 2.500), bank masuk:

| Kasus | Piutang | Dipotong (Y) | Setor sendiri (N) |
|---|---|---|---|
| 1. Penerimaan DP | 615.000 (PPh Final 3.075) | 609.425 | 612.500 |
| 2. Progress | 2.000.000 (PPh Final 10.000) | 1.987.500 | 1.997.500 |
| 3. Lebih bayar 1.000.000 | 2.000.000 | 2.987.500 | 2.997.500 |
| 4. Kurang bayar 1.000.000 | 2.000.000 | 987.500 | 997.500 |
| 5. Bank + Deposit 500.000 | 2.000.000 | 1.487.500 | 1.497.500 |

Catatan:
- Setor sendiri: Beban/Hutang PPh Final = **sisa batas PPh Final invoice**, dicatat penuh di penerimaan pertama (juga untuk kurang bayar, sesuai Excel). Penerimaan berikutnya untuk invoice yang sama tidak menambah lagi.
- Kontrol penerimaan tidak berubah: `((piutang − PPh − admin − sisa) − uang masuk − deposit) + lebih bayar = 0`.

## 2. Data dan database

**Tidak ada migrasi.** Semua memakai kolom yang sudah ada:

| Tabel.kolom | Isi untuk UMKM |
|---|---|
| `tr_invoicing.tax_treatment` (VARCHAR(8), dari migrasi invoice v3) | `'UMKM'` |
| `tr_invoicing.ppn_jurnal`, `pajak` | 0 |
| `tr_invoicing.dpp_nilai_lain`, `dpp_lain_lain_jurnal` | penjualan × 11/12 (tetap dihitung) |
| `tr_invoicing.tagihan_ppn_jurnal`, `total_akhir_jurnal`, `saldo_piutang` | penjualan |
| `tr_invoicing.consulting_snapshot` | `tax_treatment: "UMKM"` |
| `tr_penerimaan_piutang.pph23_dipotong` | Y = PPh Final dipotong customer, N = setor sendiri |
| `tr_penerimaan_piutang_detail.pph23` | PPh Final yang dipotong customer |
| `tr_penerimaan_piutang_detail.consulting_snapshot` (`calculation_version` 4) | tambahan `pph_final_self` (sen) = PPh Final setor sendiri; hanya untuk UMKM, invoice lain wajib 0/tidak ada |
| `tr_jurnal` | baris Beban PPh Final / Hutang PPh Final dengan `coa` kosong (lihat bagian 3) |

Modul **Penerimaan PPh 23** (bukti potong) tidak membaca detail penerimaan v3 (`consulting_version = 3`), jadi PPh Final UMKM **tidak** muncul sebagai PPh 23.

## 3. Konfigurasi: COA PPh Final

COA **Beban PPh Final** dan **Hutang PPh Final** belum ada di master accounting, jadi dikosongkan sesuai permintaan user:

```php
// application/modules/penerimaan_uang/libraries/Consulting_receipt.php
const PPH_FINAL_EXPENSE_COA = ''; const PPH_FINAL_EXPENSE_NAME = 'Beban PPh Final';
const PPH_FINAL_PAYABLE_COA = ''; const PPH_FINAL_PAYABLE_NAME = 'Hutang PPh Final';
```

- Penerimaan UMKM tetap bisa **disimpan** (jurnal draf tanpa COA di dua baris itu). Popup penerimaan menampilkan peringatan.
- **Posting ke accounting ditolak** (`Consulting_posting::receipt`) selama ada baris jurnal tanpa COA.
- Setelah COA dibuat: isi kedua konstanta (kode harus ada di master COA owner company). Jurnal draf yang tersimpan **sebelumnya** tetap tanpa COA (snapshot); perlu rollback lalu input ulang, atau keputusan koreksi lain (FR & NFR UMKM, catatan terbuka 1).

## 4. File yang berubah

**Invoicing**

| File | Perubahan |
|---|---|
| `application/modules/invoicing/libraries/Invoice_consulting.php` | `tax_treatments()` (PKP/WAPU/KB/UMKM), `tax_suffix()`; `totals()` UMKM: DPP tetap dihitung, PPN 0; `journal()` dan `receipt_journal()` memakai suffix; `receipt_journal()` UMKM: Beban/Hutang PPh Final, validasi `pph_final_self`, COA kosong hanya untuk dua akun PPh Final |
| `application/modules/invoicing/libraries/Consulting_posting.php` | Posting penerimaan ditolak bila ada baris jurnal tanpa COA |
| `application/modules/invoicing/views/consulting_invoice.php` | Dropdown dari `tax_treatments()`, teks bantuan, catatan `[data-umkm-note]` |
| `application/modules/invoicing/views/consulting_print.php` | Label perlakuan KB/UMKM di cetakan |
| `application/modules/invoicing/controllers/Invoicing.php` | Tab Invoiced: `invoiced_summary()` (card) dan `download_invoiced()` (ekspor `.xlsx`), filter lewat `invoiced_filters()` |
| `application/modules/invoicing/models/Invoicing_model.php` | `invoiced_rows()` (satu query untuk tabel, card, ekspor), `invoiced_totals()`, `filter_company_ppn()` (grup STM/VUCA/SUSTAIN + Perlakuan PPN), `money_for()`, `tax_label_for()`; invoice non-v3 tampil `-` di kolom Nilai dan tidak masuk nominal card |
| `application/modules/invoicing/views/index.php` | Tab Invoiced: 4 card, filter Tahun/Bulan/Company/Perlakuan PPN, kolom Perlakuan PPN dan Nilai (10 kolom) |
| `application/libraries/Xlsx_export.php` | `invoiced_spec()` (baris Total dengan rumus `SUM`), dukungan sel rumus, label UMKM |
| `assets/js/invoicing/consulting.js` | Parity JS: UMKM DPP 11/12, PPN 0, suffix |
| `assets/js/invoicing/consulting-form.js` | Tampil/sembunyi catatan UMKM |
| `assets/css/invoicing.css` | Gaya `.invoice-vat-note` |

**Penerimaan Piutang**

| File | Perubahan |
|---|---|
| `application/modules/penerimaan_uang/libraries/Penerimaan_calculation.php` | `tax_calc` UMKM (DPP tetap dihitung, PPN 0, PPh default 0,5%), `pph_label()`, `pph_limit_label()` |
| `application/modules/penerimaan_uang/libraries/Consulting_receipt.php` | Konstanta COA PPh Final, `pph_final_account()`, `pph_role()`; `invoice_context` (`pph_final_self`, `pph_label`); `preview()` akun PPh Final; `save()` pesan error dan snapshot `pph_final_self`; `receipt_history` memvalidasi akun per perlakuan |
| `application/modules/penerimaan_uang/controllers/Penerimaan_uang.php` | Daftar invoice: UMKM PPN 0 |
| `application/modules/penerimaan_uang/models/Penerimaan_uang_model.php` | Label `UMKM Non-PKP` |
| `application/modules/penerimaan_uang/views/add.php` | Label "PPh 23/PPh Final Disetor", pesan validasi, kolom "PPH 23 / PPh Final" |
| `application/modules/penerimaan_uang/views/consulting_process.php` | Judul kolom PPh, keterangan per baris UMKM, preview jurnal Beban/Hutang PPh Final, peringatan COA kosong |
| `application/modules/penerimaan_uang/views/detail.php` | Badge UMKM |
| `application/modules/penerimaan_uang/README.md` | Catatan teknis |

`application/modules/invoicing/README.md` juga diberi bagian UMKM.

**Bersama**: `application/libraries/Xlsx_export.php`, `application/helpers/mockup_format_helper.php` (label UMKM), `assets/css/mockup_ui.css` (warna badge UMKM).

**Test**: baru `application/modules/penerimaan_uang/tests/Umkm_receipt_journal_test.php`, `application/modules/invoicing/tests/Invoiced_export_test.php`, `Posting_coa_guard_test.php`; diperbarui `invoicing/tests/Consulting_calculation_test.php`, `Consulting_calculation_test.js`, `Print_invoice_test.php`, `Ui_render_test.php`, `Ui_client_test.js`.

## 5. Langkah pemasangan

1. Deploy file di bagian 4 ke server dev. Tidak ada SQL yang perlu dijalankan.
2. Bersihkan cache browser / naikkan versi aset untuk `assets/js/invoicing/consulting.js` dan `consulting-form.js`.
3. Jalankan test di bagian 6.
4. Uji manual (UAT) di bagian 8.
5. Setelah accounting membuat COA PPh Final: isi konstanta di bagian 3, deploy ulang `Consulting_receipt.php`.

## 6. Test

| Perintah | Hasil 10 Nov | Butuh DB |
|---|---|---|
| `php application/modules/penerimaan_uang/tests/Umkm_receipt_journal_test.php` | PASS 212 cek (10 skenario Excel = jurnal sistem) | Tidak |
| `php .../penerimaan_uang/tests/Consulting_receipt_tobe_test.php` (TEST 7), `Umkm_view_test.php` (12), `calculation_test.php`; `php .../invoicing/tests/Posting_coa_guard_test.php` (3) | PASS | Tidak |
| `php .../invoicing/tests/List_filter_test.php` (62), `Invoiced_export_test.php` (41), `Batch1_audit_test.php` (161); `node .../Ui_list_client_test.js` (77) | PASS | Tidak |
| `php application/modules/invoicing/tests/Consulting_calculation_test.php` | PASS | Tidak |
| `node application/modules/invoicing/tests/Consulting_calculation_test.js` | PASS | Tidak |
| `php application/modules/invoicing/tests/Print_invoice_test.php`, `Ui_render_test.php`; `node .../Ui_client_test.js` | PASS | Tidak |
| `node specs/docs/mockup-invoicing/smoke-test.js` | PASS (66 + 91 cek) | Tidak (butuh jsdom) |
| `php application/modules/invoicing/tests/Consulting_integration_test.php --isolated-local-db` | **Belum dijalankan** — laptop pengembang tidak punya DB uji terisolasi | **Ya, hanya DB uji terisolasi** |

Test yang **sudah gagal sebelum perubahan ini** (dicek dengan `git stash`, bukan regresi): `invoicing/tests/Ui_list_client_test.js`, `penerimaan_uang/tests/` `Xlsx_export_test.php`, `detail_journal_test.php`, `consulting_view_test.js`, `consulting_fifo_view_test.js`, `legacy_vuca_preview_test.php`, `installment_view_test.js`, `consulting_kwitansi_view_test.js`, `company_context_test.js`; `Add_template_contract_test.php` gagal karena deprecation dynamic property di PHP lokal.

**Jangan** menjalankan integration test terhadap database aplikasi; hanya DB uji terisolasi (`docs/invoicing-consulting-v3-2026-10-18.md`).

## 7. Use case

| ID | Use case | Aktor | Alur utama | Alternatif / tolakan | Hasil | FR |
|---|---|---|---|---|---|---|
| UC-UMKM-01 | Buat invoice UMKM Non-PKP | Finance (Billing) | 1. Tab Konsultasi → **+ Buat Invoice**.<br>2. Pilih Perlakuan PPN **UMKM Non-PKP** dan COA penjualan.<br>3. Isi tanggal, PO, faktur → **Simpan invoice**. | Isian wajib kosong → ditolak. | Invoice: DPP 11/12, PPN 0, tagihan = saldo piutang = penjualan; jurnal Piutang D / Penjualan K. | UMKM-FR-01, 02, 03 |
| UC-UMKM-02 | Terima pembayaran, PPh Final dipotong customer | Finance (Penerimaan) | 1. Penerimaan Piutang: pilih customer, **PPh 23/PPh Final Disetor = Dipotong PPh**, centang invoice → **Process**.<br>2. Isi admin / sisa / lebih bayar sampai Kontrol 0 → **Proses**. | PPh > 0,5% × penjualan → ditolak; kontrol ≠ 0 → tidak bisa simpan. | Saldo piutang berkurang; jurnal Bank, Admin, Piutang, **Beban PPh Final (D)**. | UMKM-FR-04, 06 |
| UC-UMKM-03 | Terima pembayaran, PPh Final setor sendiri | Finance (Penerimaan) | Sama dengan UC-UMKM-02 dengan **Tidak Dipotong PPh**. | Sama. | Bank penuh; jurnal + **Beban PPh Final (D) / Hutang PPh Final (K)** = 0,5% × penjualan (sekali per invoice). | UMKM-FR-04 |
| UC-UMKM-04 | Posting jurnal penerimaan UMKM | Finance / Accounting | Posting jurnal penerimaan ke accounting. | COA PPh Final masih kosong → **ditolak** "belum memiliki nomor COA". | Setelah konstanta COA diisi, penerimaan baru bisa diposting. | UMKM-FR-05 |
| UC-UMKM-05 | Lihat total invoiced dan ekspor | Finance (Billing), Finance / Manajemen | 1. Tab **Invoiced**: pilih Tahun, Bulan, Company, Perlakuan PPN → **Terapkan filter**.<br>2. Card Total Invoiced berubah sesuai filter.<br>3. **Ekspor Excel**. | Tidak ada baris → ekspor ditolak. | File `.xlsx` berisi baris yang tampil + baris Total = card. | UMKM-FR-07 (belum di kode) |

## 8. Checklist UAT (server dev)

- [ ] Buat invoice konsultasi, perlakuan **UMKM Non-PKP**: DPP = penjualan × 11/12, PPN 0, tagihan = piutang = penjualan; jurnal 2 baris berakhiran "- UMKM Non-PKP"; cetakan menyebut UMKM Non-PKP.
- [ ] Invoice PKP/WAPU/KB baru: angka dan jurnal sama seperti sebelumnya.
- [ ] Penerimaan invoice UMKM, **Dipotong PPh**: PPh default 0,5%; jurnal Bank, Admin, Piutang, Beban PPh Final; tidak ada PPh 23 Disetor; angka sesuai tabel kasus.
- [ ] Penerimaan invoice UMKM, **Tidak Dipotong PPh**: bank penuh; jurnal + Beban PPh Final D / Hutang PPh Final K.
- [ ] Lebih bayar, kurang bayar, pakai deposit: sesuai tabel kasus.
- [ ] Batch campuran PKP + UMKM: PKP pakai PPh 23, UMKM pakai PPh Final.
- [ ] PPh UMKM > 0,5% ditolak dengan pesan "PPh Final 0,5% melebihi …".
- [ ] Peringatan COA kosong tampil; **posting** penerimaan UMKM ditolak dengan pesan "belum memiliki nomor COA".
- [ ] Rollback penerimaan UMKM mengembalikan saldo piutang dan alokasi.
- [ ] Penerimaan PPh 23 (bukti potong) tidak menampilkan PPh Final UMKM.
- [ ] Label form: "PPh 23/PPh Final Disetor".

## 9. Tab Invoiced: card Total Invoiced + Ekspor Excel (UMKM-FR-07)

Diminta user (INV-R-21 s.d. INV-R-23) dan sudah ada di mockup (`mockup-invoicing/index.html`, tab Invoiced). Di aplikasi, tab Invoiced belum punya card, filter Perlakuan PPN, dan ekspornya masih format lama `.xls` (`Invoicing::download_excel`).

| Bagian | Spesifikasi |
|---|---|
| Letak | Paling atas tab Invoiced, di atas filter. |
| Card | **Total Invoiced** = Σ total tagihan (penjualan + PPN; UMKM = penjualan) + jumlah invoice + teks filter aktif; **Penjualan** = Σ penjualan; **PPN** = Σ PPN; **Saldo piutang** = Σ `tr_invoicing.saldo_piutang` (sisa saat ini, berkurang saat ada penerimaan). |
| Filter | Tahun (+ "Semua tahun"), Bulan invoice (12 bulan), **Company: STM, Vuca, Sustain**, Perlakuan PPN, pencarian. Card, tabel, dan ekspor memakai query yang sama. |
| Ekspor `.xlsx` | Baris 1 judul, baris 2 filter, baris 4 header: No, No. Invoice, Tanggal Invoice (dd-mm-yyyy), No. SPK, No. PO, No. Faktur, Customer, Project, Company, TOP, Persen TOP, Perlakuan PPN, Penjualan, DPP Lain-lain, PPN, Total Tagihan, Saldo Piutang, Status. Baris Total dengan rumus `SUM`. Angka `#,##0.00`. Nama file `Invoiced_<tahun>_<bulan>_<company>_<PPN>.xlsx`. |
| Acceptance | Total di card = baris Total Excel = jumlah tabel untuk filter yang sama (0 selisih). |

## 10. Masih terbuka (perlu jawaban user / accounting)

1. Nomor COA **Beban PPh Final** dan **Hutang PPh Final**, dan perlakuan jurnal draf yang tersimpan sebelum COA diisi.
2. Kurang bayar + setor sendiri: Hutang PPh Final penuh di penerimaan pertama (sesuai Excel) atau proporsional?
3. Kawasan Berikat: ikut PPN 0 seperti UMKM? Baris PPN 0.00 di cetakan UMKM: tampil atau disembunyikan?
4. Label "PPh 23" di daftar dan detail Penerimaan diganti "PPh 23/PPh Final"?
5. Invoice Vuca dan invoice lama ikut tampil di tab Invoiced?
6. **Card Total Invoiced + Ekspor Excel `.xlsx` di tab Invoiced** (UMKM-FR-07, bagian 9): mockup disetujui → dibuat di aplikasi?
