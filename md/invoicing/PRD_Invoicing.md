# PRD — Modul Invoicing

## 1. Ringkasan Produk

Modul **Invoicing** adalah sistem pembuatan dan pengelolaan invoice penjualan (Accounts Receivable) pada aplikasi Sendigs Finance. Modul ini melayani dua tipe bisnis utama: **Konsultasi** (layanan jasa konsultasi berbasis SPK/Penawaran) dan **Non Konsultasi** (penjualan barang/jasa umum). Sistem mendukung multi-company (Sentral dan VUCA) dengan perhitungan pajak otomatis dan pembuatan jurnal akuntansi secara real-time.

---

## 2. Tujuan & Manfaat

| Tujuan                | Deskripsi                                                                                |
| --------------------- | ---------------------------------------------------------------------------------------- |
| Centralized billing   | Satu titik pembuatan invoice untuk seluruh jenis penjualan perusahaan                    |
| Otomasi jurnal        | Jurnal akuntansi (piutang, PPN, PPh, pendapatan) tercipta otomatis saat invoice disimpan |
| Tracking piutang      | Saldo piutang per invoice terupdate real-time saat terjadi penerimaan uang               |
| Multi-company support | Mendukung entity Sentral (PPh 2%) dan VUCA (PPh 0.5%) dengan COA berbeda                 |
| Audit trail           | Setiap invoice memiliki `created_by`, `created_date`, nomor revisi, dan status close     |

---

## 3. Target Pengguna

| Role                  | Kebutuhan                                                          |
| --------------------- | ------------------------------------------------------------------ |
| Staff Finance/Billing | Membuat, mencetak, dan merevisi invoice harian                     |
| Finance Manager       | Melihat list invoice, filter status, download Excel, close invoice |
| Akuntan               | Memastikan jurnal yang ter-generate balance dan benar COA-nya      |

---

## 4. Fitur Utama

### 4.1 Invoice Konsultasi (Tab "Konsultasi")

| Fitur                  | Deskripsi                                                                           |
| ---------------------- | ----------------------------------------------------------------------------------- |
| List SPK/Plan Tagih    | Menampilkan seluruh `kons_tr_plan_tagih_detail` dengan status `status_terakhir = 1` |
| Filter Status          | Filter berdasarkan **Uninvoiced** / **Invoiced**                                    |
| Create Invoice Sentral | Membuat invoice dari plan tagih dengan tarif PPh 23 = 2%                            |
| Create Invoice VUCA    | Membuat invoice dari plan tagih dengan tarif PPh 23 = 0.5%                          |
| View Invoice           | Melihat detail invoice beserta jurnal yang sudah dibuat                             |
| Edit/Revisi Invoice    | Mengedit no. invoice, tanggal, PO, faktur (hanya jika jurnal belum diposting)       |
| Print Invoice          | Cetak PDF invoice dengan pilihan company header (Sentral/VUCA) + keterangan custom  |
| Print Kwitansi         | Cetak PDF kwitansi terpisah                                                         |
| Download Excel         | Export data list invoice ke format Excel                                            |

### 4.2 Invoice Non Konsultasi (Tab "Non Konsultasi")

| Fitur                   | Deskripsi                                                                  |
| ----------------------- | -------------------------------------------------------------------------- |
| List Penawaran Non Kons | Menampilkan penawaran non konsultasi yang sudah deal dan belum di-close    |
| Create Invoice Non Kons | Membuat invoice dengan input item dinamis (nama, qty, harga) + biaya kirim |
| Edit Invoice Non Kons   | Merevisi detail item & recalculate jurnal (jika jurnal belum posting)      |
| View Invoice Non Kons   | Melihat detail invoice, items, dan jurnal                                  |
| Print Invoice Non Kons  | Cetak PDF dengan pilihan company header                                    |
| Print Kwitansi          | Cetak PDF kwitansi                                                         |
| Close Invoice           | Menutup invoice secara permanen dengan alasan (irreversible)               |
| Close Penawaran         | Menutup penawaran non konsultasi agar tidak bisa diinvoice lagi            |

### 4.3 Fitur Pendukung

| Fitur                        | Deskripsi                                                                                  |
| ---------------------------- | ------------------------------------------------------------------------------------------ |
| Auto-generate No. Invoice    | Nomor invoice di-generate otomatis oleh sistem dengan format `XXX/{ENTITY}/{YEAR}`         |
| Entity Code Mapping          | SSC (default), STM (company 1,6,7), VSB (company 4 / VUCA)                                 |
| Auto-generate ID Transaksi   | Format internal ID: `XXXXX-INV-{romawi bulan}-{tahun}`                                     |
| Auto-generate Jurnal ID      | Format: `XXXXX-AJV-{romawi bulan}-{tahun}`                                                 |
| Hitung Jurnal (AJAX)         | Kalkulasi ulang jurnal secara real-time saat DPP berubah                                   |
| COA Revenue Selection        | User bisa memilih COA pendapatan (4101-01-03 atau 4101-01-07) untuk non konsultasi         |
| Partial Invoicing (Non Kons) | Satu penawaran bisa diinvoice berkali-kali selama outstanding masih tersedia               |
| Invoice Created Counter      | Halaman list penawaran non kons menampilkan jumlah invoice yang sudah dibuat per penawaran |

---

## 5. Perhitungan Keuangan

### 5.1 Invoice Sentral (tipe_invoice = 0 / null)

```
DPP             = nominal_payment (dari plan tagih) atau total item (non kons)
DPP Lain-lain   = DPP × 11/12
PPN 12%         = DPP Lain-lain × 12/100
PPh 23 (2%)     = DPP × 2/100
Total + PPN     = DPP + PPN
Total Akhir     = DPP + PPN - PPh
Saldo Piutang   = Total Akhir
```

### 5.2 Invoice VUCA (tipe_invoice = 1)

```
DPP             = nominal_payment (dari plan tagih)
PPh 23 (0.5%)   = DPP × 0.5/100
Total Akhir     = DPP - PPh
Saldo Piutang   = Total Akhir
PPN             = tidak dikenakan (kredit PPN = 0)
```

---

## 6. Jurnal Otomatis

### 6.1 Jurnal Invoice Sentral

| COA        | Nama Akun             | Debit       | Kredit |
| ---------- | --------------------- | ----------- | ------ |
| 1102-01-01 | Piutang Dagang        | Total Akhir | -      |
| 1106-01-02 | PPh 23 dibayar dimuka | PPh 23      | -      |
| 2104-01-07 | PPN Keluaran          | -           | PPN    |
| 4101-01-01 | Pendapatan Jasa       | -           | DPP    |

### 6.2 Jurnal Invoice VUCA

| COA        | Nama Akun                  | Debit                   | Kredit |
| ---------- | -------------------------- | ----------------------- | ------ |
| 1102-01-01 | Piutang Dagang             | Total Akhir (DPP - PPh) | -      |
| 1106-01-05 | PPh 23 VUCA dibayar dimuka | PPh 23                  | -      |
| 4101-01-01 | Pendapatan Jasa            | -                       | DPP    |

---

## 7. Integrasi dengan Modul Lain

### 7.1 Penerimaan Uang (penerimaan_uang)

- Saat dilakukan penerimaan piutang, field `saldo_piutang` pada `tr_invoicing` dikurangi.
- Modul penerimaan_uang menampilkan invoice berdasarkan `nm_customer` dengan `saldo_piutang > 0`.
- Rollback penerimaan akan mengembalikan `saldo_piutang` ke nilai semula.

### 7.2 Penerimaan PPh 23 (penerimaan_pph_23)

- Setelah penerimaan piutang dengan opsi "PPh23 dipotong = Y", detail penerimaan masuk ke modul penerimaan PPh 23.
- Modul ini mencatat bukti potong PPh 23 dari customer dan menggenerate jurnal balik (debit PPh, kredit Piutang).

---

## 8. Permission Model

| Permission String  | Deskripsi                                                    |
| ------------------ | ------------------------------------------------------------ |
| `Invoicing.View`   | Melihat list invoice dan detail                              |
| `Invoicing.Add`    | Membuat invoice baru                                         |
| `Invoicing.Manage` | Edit, revisi, close invoice                                  |
| `Invoicing.Delete` | Menghapus invoice (belum diimplementasikan secara eksplisit) |

---

## 9. Status & Lifecycle

```
[Plan Tagih / Penawaran]
        │
        ▼ (Create Invoice)
   ┌─────────┐
   │  Active  │──── Print Invoice / Kwitansi
   └────┬────┘
        │
        ├─── Revisi (jika jurnal belum posting)
        │
        ├─── Jurnal Posting → status "Journaled"
        │
        ├─── Penerimaan Uang → saldo_piutang berkurang
        │
        └─── Close Invoice → status "Closed" (permanen)
```

---

## 10. Constraint & Aturan Bisnis

1. **No. Invoice auto-generated** — Sistem otomatis generate `XXX/{ENTITY}/{YEAR}` berdasarkan company; user tidak bisa input manual.
2. **Sequence per entity per tahun** — Counter nomor invoice di-reset per tahun dan per kode entitas (STM/VSB/SSC).
3. **Revisi invoice** hanya bisa dilakukan jika jurnal belum diposting (`sts = 0`).
4. **Close invoice** bersifat permanen — tidak bisa dibuka kembali, wajib isi alasan.
5. **Close penawaran** (Non Kons) — Menutup penawaran agar tidak bisa diinvoice lagi (permanen).
6. **Outstanding validation** (Non Kons) — DPP tidak boleh melebihi sisa outstanding dari penawaran.
7. **Jurnal di-regenerate** setiap kali revisi (delete lama → insert baru).
8. **Saldo piutang** hanya berubah melalui modul penerimaan_uang.
9. **IDOR prevention** — View/edit invoice Non Kons memvalidasi bahwa invoice dan penawaran terkait benar-benar ada sebelum render.

---

## 11. Non-Functional Requirements

| Aspek           | Detail                                                                             |
| --------------- | ---------------------------------------------------------------------------------- |
| Performance     | DataTables server-side processing untuk list utama                                 |
| Security        | Permission check di setiap method; XSS filtering via CI `$this->input->post(true)` |
| Audit           | `created_by`, `created_date` di setiap record                                      |
| Data Integrity  | DB transaction (`trans_begin/commit/rollback`) di setiap operasi tulis             |
| Browser Support | Kompatibel dengan browser modern (Chrome, Firefox, Edge)                           |

---

## 12. Data Source & Dependencies

| Data Source           | Modul/Tabel                                              |
| --------------------- | -------------------------------------------------------- |
| Plan Tagih Konsultasi | `kons_tr_plan_tagih_detail`, `kons_tr_actual_plan_tagih` |
| SPK Penawaran         | `kons_tr_spk_penawaran` (DB Consultant)                  |
| Penawaran Non Kons    | `kons_tr_penawaran_non_konsultasi` (DB Consultant)       |
| Company               | `kons_tr_company` (DB Consultant)                        |
| COA Master            | `coa_master` (DB Accounting)                             |
| Jurnal                | `tr_jurnal` (DB ERP)                                     |
