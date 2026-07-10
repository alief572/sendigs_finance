# PRD – Modul Jurnal Payment Petty Cash

## 1. Overview

**Modul**: `jurnal_payment_petty_cash`  
**Tujuan**: Menyediakan fitur bagi user finance untuk memposting jurnal pembayaran petty cash dari staging table (`tr_jurnal`) ke database akuntansi final (`japh` + `jurnal`) pada masing-masing entity perusahaan (STM, VUCA, SUSTAIN).

Modul ini menjadi jembatan antara pencatatan pengeluaran kas kecil (modul `expense_petty_cash`) dengan sistem akuntansi. Setelah pencatatan di-approve dan entry jurnal staging tercipta, user finance melakukan review dan posting satu-per-satu ke database akuntansi yang tepat.

---

## 2. Problem Statement

Pencatatan pengeluaran petty cash menghasilkan entry jurnal di staging table (`tr_jurnal`) dengan status belum diposting (`sts = '0'`). Entry ini perlu diverifikasi balance (debit = kredit), lalu diposting ke database akuntansi final agar masuk ke general ledger. Proses ini membutuhkan:

- Validasi balance sebelum posting
- Penomoran otomatis (BUK number) per cabang dan per database akuntansi
- Posting multi-database untuk transaksi inter-company
- Audit trail dan pencegahan double-posting

---

## 3. Target User

| Role          | Aksi                                                                                     |
| ------------- | ---------------------------------------------------------------------------------------- |
| Finance Staff | View daftar jurnal, review detail, posting jurnal, view laporan buku besar, export Excel |

---

## 4. Scope

### In Scope

- Daftar jurnal petty cash yang belum diposting (DataTables server-side)
- Filter berdasarkan company
- View detail jurnal dalam modal dengan validasi balance
- Posting jurnal ke database akuntansi (3 skenario)
- Laporan Buku Besar Kas Kecil (running balance)
- Export Buku Besar ke Excel (.xlsx)
- Revisi status jurnal (manage permission)

### Out of Scope

- Approval workflow (tidak ada approval di modul ini)
- Pembuatan entry jurnal (dilakukan otomatis oleh modul `expense_petty_cash`)
- Cetak bukti pembayaran / voucher
- Manajemen COA (COA sudah muncul dari modul pencatatan)

---

## 5. Functional Requirements

### FR-01: Daftar Jurnal (Index)

| Item            | Detail                                                                                                                                                                      |
| --------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| URL             | `/jurnal_payment_petty_cash`                                                                                                                                                |
| Permission      | `Jurnal_Payment_Petty_Cash.View`                                                                                                                                            |
| Data Source     | `tr_jurnal` grouped by `no_transaksi` + `jenis_transaksi`                                                                                                                   |
| Filter          | WHERE `jenis_transaksi` IN ('Refill Pettycash', 'Payment Hutang Petty Cash') AND data terkait di `payment_approve` dengan tipe refill/petty_cash_hutang/no_doc LIKE '%RPC%' |
| Status Filter   | WHERE `sts` IN ('', '0') — hanya yang belum diposting                                                                                                                       |
| Company Filter  | Dropdown select2 berisi distinct `id_company` dari data                                                                                                                     |
| Kolom DataTable | No, Tanggal, No Transaksi, Tipe, Company, Keterangan, Debit, Kredit, Action                                                                                                 |
| Action Button   | View (membuka modal detail)                                                                                                                                                 |

### FR-02: Detail Jurnal (Modal)

| Item           | Detail                                                                          |
| -------------- | ------------------------------------------------------------------------------- |
| Trigger        | Klik button "View" pada tabel                                                   |
| Endpoint       | POST `/jurnal_payment_petty_cash/get_detail_jurnal`                             |
| Input          | `id` (row id dari DataTable)                                                    |
| Output         | HTML table detail yang di-render server-side                                    |
| Grouping       | Baris di-group berdasarkan `nm_company` (untuk inter-company: tampil 2 section) |
| Validasi       | Balance check: SUM(debit) == SUM(kredit) per group                              |
| Visual         | Hijau = balance, Merah = tidak balance                                          |
| Button Posting | Disabled jika tidak balance; Enabled jika balance                               |
| Kolom Detail   | Tanggal, Tipe, COA, Nama Account, Keterangan, No Reff, Debit, Kredit            |

### FR-03: Posting Jurnal

| Item       | Detail                                                                  |
| ---------- | ----------------------------------------------------------------------- |
| Endpoint   | POST `/jurnal_payment_petty_cash/save_posting_jurnal`                   |
| Permission | `Jurnal_Payment_Petty_Cash.Add`                                         |
| Input      | `no_transaksi`, `jenis_transaksi`, `jenis_posting` (optional: 'refill') |
| Konfirmasi | SweetAlert2 "Apakah Anda yakin ingin memposting jurnal ini?"            |

#### Skenario Posting:

**A. STM Internal (id_company = '5')**

- Generate 1 BUK number dari `pastibisa_tb_cabang` di DBACC_STM
- INSERT header ke `japh` table di DBACC_STM
- INSERT BATCH detail ke `jurnal` table di DBACC_STM
- Increment counter `nobuk` di `pastibisa_tb_cabang`
- Update `tr_jurnal.sts = '1'`

**B. Inter-Company (id_company = '4' VUCA / '6' SUSTAIN)**

- Generate 2 BUK number: satu dari DB company, satu dari DB STM
- INSERT header + detail ke DB company (DBACC_VUCA atau DBACC_SUSTAIN)
- INSERT header + detail ke DBACC_STM
- Increment kedua counter `nobuk`
- Update `tr_jurnal.sts = '1'`

**C. Refill (terdeteksi dari parameter atau keterangan mengandung "Refill")**

- Determine target DB berdasarkan company name
- Generate 1 BUK number dari target DB
- INSERT header + detail ke target DB saja
- Increment counter `nobuk` di target DB
- Update `tr_jurnal.sts = '1'`

#### Transaction Safety:

- Multi-database transaction: `trans_begin()` pada setiap DB yang terlibat
- Jika satu langkah gagal → rollback ALL database
- Pencegahan double-posting: staging data hanya muncul jika `sts IN ('', '0')`

### FR-04: Laporan Buku Besar Kas Kecil

| Item            | Detail                                                                                               |
| --------------- | ---------------------------------------------------------------------------------------------------- |
| URL             | `/jurnal_payment_petty_cash/buku_besar`                                                              |
| Permission      | `Jurnal_Payment_Petty_Cash.View`                                                                     |
| Filter          | Tanggal Dari, Tanggal Sampai                                                                         |
| Kolom           | No, No Transaksi, Tanggal, COA, Company, Pengeluaran, Jenis Jurnal, Debit, Kredit, Saldo, Keterangan |
| Saldo Awal      | SUM(debit) - SUM(kredit) dari semua posted transactions sebelum `tgl_from`                           |
| Running Balance | Saldo = saldo_sebelumnya + debit - kredit                                                            |
| Jenis Jurnal    | Refill (hijau) atau Transaksi (merah) berdasarkan keterangan                                         |
| Data Source     | `tr_jurnal` WHERE `jenis_transaksi` IN ('Petty Cash', 'Payment', 'Refill Pettycash') AND `sts = '1'` |

### FR-05: Export Buku Besar ke Excel

| Item       | Detail                                                                     |
| ---------- | -------------------------------------------------------------------------- |
| URL        | GET `/jurnal_payment_petty_cash/export_buku_besar?tgl_from=...&tgl_to=...` |
| Permission | `Jurnal_Payment_Petty_Cash.View`                                           |
| Library    | PHPExcel (Excel2007 writer)                                                |
| Filename   | `Buku_Besar_Kas_Kecil_{tgl_from}_{tgl_to}.xlsx`                            |
| Content    | Header bold, saldo awal bold, number format #,##0, auto-size columns       |

### FR-06: Revisi Jurnal

| Item       | Detail                                                                   |
| ---------- | ------------------------------------------------------------------------ |
| Endpoint   | POST `/jurnal_payment_petty_cash/revisi_jurnal`                          |
| Permission | `Jurnal_Payment_Petty_Cash.Manage`                                       |
| Fungsi     | Mengubah status jurnal (misalnya revert posted → unposted untuk koreksi) |

---

## 6. Nomor BUK (Auto-Number)

**Format**: `{nocab}BK{subcab}{yy}{sequence}`  
**Contoh**: `101BKA2500001`

| Segment    | Sumber                          | Keterangan                |
| ---------- | ------------------------------- | ------------------------- |
| `nocab`    | `pastibisa_tb_cabang.nocab`     | Kode cabang (e.g., '101') |
| `BK`       | Literal                         | Prefix Buku Kas           |
| `subcab`   | `pastibisa_tb_cabang.subcab`    | Sub-cabang (e.g., 'A')    |
| `yy`       | `date('y')`                     | 2-digit tahun             |
| `sequence` | `pastibisa_tb_cabang.nobuk + 1` | Zero-padded 5 digit       |

Counter di-increment setelah posting berhasil via `UPDATE pastibisa_tb_cabang SET nobuk = nobuk + 1 WHERE nocab = ?`.

---

## 7. Permission Matrix

| Permission String                  | Aksi                                                       |
| ---------------------------------- | ---------------------------------------------------------- |
| `Jurnal_Payment_Petty_Cash.View`   | Lihat daftar jurnal, view detail, akses buku besar, export |
| `Jurnal_Payment_Petty_Cash.Add`    | Posting jurnal ke database akuntansi                       |
| `Jurnal_Payment_Petty_Cash.Manage` | Revisi status jurnal                                       |

---

## 8. Multi-Company Database Mapping

| Company | id_company | DB Connection Key    | Database Name         |
| ------- | ---------- | -------------------- | --------------------- |
| STM     | 5          | `accounting_stm`     | db_sendigs_ss_stm     |
| VUCA    | 4          | `accounting_vuca`    | db_sendigs_ss_vuca    |
| SUSTAIN | 6          | `accounting_sustain` | db_sendigs_ss_sustain |

---

## 9. Success Criteria

| Metric             | Target                                                                      |
| ------------------ | --------------------------------------------------------------------------- |
| Posting akurat     | Debit == Kredit pada semua posting (zero tolerance)                         |
| Data integrity     | Tidak ada double-posting (sts flag)                                         |
| Transaction safety | Rollback sempurna jika satu langkah gagal                                   |
| Performance        | DataTables response < 2 detik untuk 1000+ records                           |
| Audit              | Setiap posting menghasilkan record di `japh` (header) dan `jurnal` (detail) |

---

## 10. Dependensi

| Modul/Komponen            | Relasi                                                                  |
| ------------------------- | ----------------------------------------------------------------------- |
| `expense_petty_cash`      | Sumber data: menciptakan entry di `tr_jurnal` dengan status pending     |
| `payment_approve`         | Referensi: berisi data payment yang di-link ke `tr_jurnal.no_transaksi` |
| `pastibisa_tb_cabang`     | Counter: sequence number BUK per cabang per database akuntansi          |
| Database akuntansi (3 DB) | Target: `japh` (header) + `jurnal` (detail)                             |
| PHPExcel                  | Library: export buku besar ke .xlsx                                     |
