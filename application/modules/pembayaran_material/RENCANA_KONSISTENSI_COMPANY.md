# Rencana Perbaikan: Konsistensi Company dari Request Payment → Pembayaran Material

**Modul terkait:** `request_payment`, `approval_request_payment`, `pembayaran_material`
**Status:** RENCANA (belum ada perubahan kode)
**Tanggal:** 2026-09-10

---

## 1. Masalah

Company yang muncul & tersimpan di alur **request_payment** (pengajuan/approval/record) **tidak terbawa** ke tahap proses pembayaran di **pembayaran_material**. Akibatnya company bisa tidak konsisten, dan jurnal payment mencatat company yang salah.

## 2. Temuan (terverifikasi di DB dev & kode)

1. **Company tersimpan rapi di sisi request** — `tr_rp_pengajuan_h` punya `company_id` (kons id 1..7) + `company_nama` (mis. PGJ-0007 = id 7 / STM). Snapshot `tr_rp_record` juga. Konsisten di halaman request/approval/record.

2. **`payment_approve` TIDAK punya kolom company** — daftar kolomnya (id, no_doc, nama, tipe, jumlah, total_ppn, total_pph, admin_bank, dst) tidak memuat company/perusahaan. Saat approve, `approval_request_payment/controllers/Approval_request_payment.php` (insert `payment_approve` ~baris 152-180) meng-carry pph/ppn/admin TAPI tidak meng-carry company. → **company hilang di sini.**

3. **Mapping company di jurnal payment di-HARDCODE** — `pembayaran_material/controllers/Pembayaran_material.php` baris 1497:
   ```php
   'id_company' => ($jr['company'] == 'STM') ? 1 : 1, // Defaulting to 1, or can map VUCA/SUSTAIN
   ```
   Selalu `1` apa pun company aslinya (komentar TODO belum dikerjakan). `nm_company` diisi dari `$jr['company']` yang sumbernya tidak dijamin company dokumen.

4. **Referensi mapping company:**
   - HRIS `hris_companies`: COM003 = Sentral Sistem Consulting, COM006 = Sentral Sustainability, COM012 = Vuca Strategi Bisnis.
   - Kons `kons_tr_company`: 3 = Sustain, 4 = Vuca, 7 = STM (dipakai `company_map()`: COM003→7, COM006→3, COM012→4).
   - Company yang dipakai di alur RP adalah **kons id (1..7)** + nama STM/Vuca/Sustain.

## 3. Rencana Perubahan (per file & baris)

### BAGIAN 1 — Carry company ke payment_approve

#### 3.1. DB — tambah kolom company di `payment_approve`
- `ALTER TABLE payment_approve ADD COLUMN company_id VARCHAR(10) NULL, ADD COLUMN company_nama VARCHAR(100) NULL;`
- Migration baru: `application/modules/pembayaran_material/migrations/002_add_company_to_payment_approve.sql`.
- **[verifikasi]** pastikan tidak bentrok dgn kolom lain; `payment_approve` dipakai lintas modul (kasbon/expense lama) — kolom baru nullable, aman untuk baris lama.

#### 3.2. `approval_request_payment/controllers/Approval_request_payment.php` — insert payment_approve (~baris 160)
- Tambahkan ke array insert:
  ```php
  'company_id'   => $header->company_id,
  'company_nama' => $header->company_nama,
  ```
- `$header` sudah tersedia di scope (punya `company_id`/`company_nama` dari `tr_rp_pengajuan_h`).

### BAGIAN 2 — Pakai company yang benar di proses payment & jurnal

#### 3.3. `pembayaran_material/controllers/Pembayaran_material.php` — form_payment_new() (~baris 159)
- Saat `SELECT a.*` dari `payment_approve`, kolom `company_id`/`company_nama` ikut terbawa. Sediakan ke view bila perlu ditampilkan/di-hidden-field-kan.

#### 3.4. `pembayaran_material/controllers/Pembayaran_material.php` — baris 1497 (mapping jurnal)
- Ganti hardcode `? 1 : 1` dengan mapping company→id yang benar. Buat helper kecil, mis.:
  ```php
  // STM=1? Vuca=? Sustain=?  -> PERLU KONFIRMASI id perusahaan di master jurnal (lihat 3.5)
  $map_company_jurnal = ['STM' => ?, 'Vuca' => ?, 'Sustain' => ?];
  'id_company' => $map_company_jurnal[$jr['company']] ?? 1,
  ```
- Terapkan juga di titik jurnal lain yg pakai pola sama (cari `id_company` lain di file ini).

#### 3.5. **[VERIFIKASI WAJIB sebelum 3.4]** id_company untuk jurnal
- Tabel jurnal (mis. `jurnal_payment`/tabel jurnal terkait) memakai `id_company` numerik. **Perlu dipastikan** id perusahaan yang benar untuk STM / Vuca / Sustain di konteks jurnal/akuntansi (DBACC). Hardcode `1` sekarang = STM. Jangan menebak id Vuca/Sustain — konfirmasi ke master perusahaan jurnal dulu.

## 4. Data lama

- Dokumen yang sudah terlanjur di `payment_approve` tanpa company (dan belum dibayar) bisa di-backfill dari `tr_rp_record`/`tr_rp_pengajuan_h` via `no_doc = no_dokumen`. Opsional, hanya jika diperlukan.

## 5. Risiko & Catatan

- **`payment_approve` dipakai lintas modul** (kasbon/expense/transport lama). Kolom baru nullable → tidak merusak alur lama. Tapi perubahan di titik insert (3.2) hanya untuk alur RP baru.
- **Bagian 2 menyentuh JURNAL keuangan** → risiko tinggi. Wajib: (a) konfirmasi id_company jurnal (3.5), (b) uji di dev dengan 1 dokumen per company (STM/Vuca/Sustain), (c) cek jurnal balance tidak berubah selain company.
- Company di alur RP = **kons id (1..7)**, sedangkan id_company jurnal mungkin **beda skema** — jangan asумsikan sama. Ini inti 3.5.
- DB dev diakses via `C:\xampp74\php\php.exe` + mysqli (user `alief`).
- Nomor baris per 2026-09-10; verifikasi ulang sebelum edit.

## 6. Urutan Eksekusi

1. **[3.5]** Konfirmasi id_company jurnal yang benar untuk STM/Vuca/Sustain (paling kritis, blocking untuk Bagian 2).
2. **[3.1 + 3.2]** Tambah kolom company di payment_approve + carry dari approval. Uji: approve 1 pengajuan, cek `payment_approve.company_nama` terisi.
3. **[3.3]** Sediakan company di form payment (tampil/hidden).
4. **[3.4]** Perbaiki mapping jurnal pakai company asli. Uji per company.
5. (Opsional) Backfill data lama.
