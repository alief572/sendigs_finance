# Rencana Perubahan: Auto-fill PPH, PPN & Admin dari Request Payment ke Pembayaran Material

**Modul sumber:** `request_payment`  → **Modul target:** `pembayaran_material`
**Status:** RENCANA (belum ada perubahan kode)
**Tanggal:** 2026-09-10

> **KEPUTUSAN USER (2026-09-10):**
> Di modul **`request_payment`** yang sekarang, saat user men-checklist **PPN / PPH / Admin**, sistem memunculkan **nilai** (dihitung `calc_tax`). **Nilai itulah** yang diinginkan **otomatis muncul** ketika user memproses pembayaran di **`pembayaran_material`** (`form_payment_new`).

---

## 1. Tujuan

Nilai `nilai_ppn`, `nilai_pph`, dan `admin` yang sudah dihitung & tersimpan saat pengajuan di modul `request_payment`, **otomatis ter-prefill** di form proses payment `pembayaran_material` — tidak dihitung/diisi ulang.

---

## 2. Kondisi Saat Ini (hasil investigasi — sudah terverifikasi di kode)

### SUMBER — Modul `request_payment` (tempat pph/ppn/admin diisi)
- **Controller:** `application/modules/request_payment/controllers/Request_payment.php` → `save()` (baris 76–230).
  - Menerima `items = {no_dokumen, ppn, pph23, pph21, admin}` (baris 78).
  - Hitung nilai via `Request_payment_model->calc_tax()` (model baris 1888):
    - PPN = `ceil(DPP × 0.11)` bila flag_ppn
    - PPH21 = `ceil(DPP × 0.025)` / PPH23 = `ceil(DPP × 0.02)` (eksklusif)
    - Admin = nominal dari whitelist `{0, 2500, 6500}`
    - Dibayarkan = (DPP + PPN) − PPH − Admin
  - Simpan hasilnya ke **`tr_rp_pengajuan_d`** (detail, `insert_batch` baris 223) + header **`tr_rp_pengajuan_h`** (baris 203).
- **Skema `tr_rp_pengajuan_d`** (migration `request_payment/migrations/010_create_rp_pengajuan.sql`, baris 51+) — kolom kunci:
  `no_dokumen`, `dpp`, `flag_ppn`, `flag_pph21`, `flag_pph23`, **`nilai_ppn`**, **`nilai_pph`**, **`admin`**, `dibayarkan`, `decision`.
- Snapshot final (setelah approve) tersimpan juga di **`tr_rp_record`** dengan kolom yang sama (migration baris 74+).

### TARGET — Modul `pembayaran_material` (tempat nilai harus muncul)
- **Controller:** `application/modules/pembayaran_material/controllers/Pembayaran_material.php` → `form_payment_new()` (baris 159–221).
  - Ambil data dari tabel **`payment_approve`** by `id` (`SELECT a.*` baris 190–194), tiap baris punya `no_doc`, `jumlah`, `tipe`.
- **View:** `application/modules/pembayaran_material/views/form_payment_new.php`
  - **PPN** (`dt[..][nilai_ppn]`, baris 302): value di-prefill dari `tr_invoice_po.nilai_ppn` (baris 207 → var `$nilai_ppn` baris 298). **BUKAN dari request.**
  - **PPH** (`dt[..][nilai_pph]`, baris 300): `readonly`, **kosong**, dihitung ulang oleh JS dari dropdown `tipe_pph` (PPH 21/23).
  - **Admin**: tidak muncul di baris item; `biaya_admin` diinput manual saat `save_payment_new()` (baris 242).

### Akar masalah
Form `form_payment_new` (baca `payment_approve` + `tr_invoice_po`) **tidak terhubung** ke tabel `tr_rp_pengajuan_d` / `tr_rp_record` tempat nilai ppn/pph/admin sudah tersimpan.

**Jembatan yang tersedia:** `tr_rp_pengajuan_d.no_dokumen` (atau `tr_rp_record.no_dokumen`) vs `payment_approve.no_doc`. Keduanya menyimpan nomor dokumen — inilah kunci join yang akan dipakai untuk lookup.

---

## 3. Verifikasi DB (SUDAH DILAKUKAN — 2026-09-10)

**Koneksi DB dev: BERES.** MariaDB 10.4 client CLI (`C:\xampp74\mysql\bin\mysql.exe`) gagal karena `sha256_password` plugin — server dev adalah **MySQL 8.0.46**. Solusi: pakai **PHP mysqli** (`C:\xampp74\php\php.exe`) dengan user aplikasi `alief` (dari `config/development/database.php`). Ini jalur yang dipakai aplikasi & kompatibel dengan MySQL 8.

**Hasil verifikasi:**
- **[V1 — TERVERIFIKASI ✅]** `payment_approve.no_doc` **BERIRISAN** dengan `tr_rp_pengajuan_d.no_dokumen` (3 dokumen cocok saat cek). Join lewat nomor dokumen **valid**. Dokumen bertipe **kasbon & transport** (bukan PO/material murni).
- **[TEMUAN BARU — penting]** Tabel **`payment_approve` SUDAH punya kolom pajak sendiri**: `total_ppn` (decimal), `total_pph` (decimal), `admin_bank` (decimal), `bank_admin` (double), `tipe_pph` (varchar). **Tidak perlu ALTER TABLE.**
- **[Arah data saat ini TERBALIK]**
  - `views/form_payment_new.php` **sudah membaca** `$item->admin_bank` (baris 340) untuk bank charge, tapi PPH/PPN masih **dihitung ulang** (tidak pakai `total_pph`/`total_ppn`).
  - `controllers/Pembayaran_material.php` (baris 1449–1451) justru **MENULIS** `total_ppn`/`total_pph`/`tipe_pph` ke `payment_approve` **saat proses payment** — jadi kolom ini diisi belakangan, bukan dari request.
  - `views/view_payment_new.php` (baris 227–228) **menampilkan** `total_ppn`/`total_pph` dari `payment_approve` → membuktikan kolom ini memang dipakai sebagai sumber tampil.

**Kesimpulan arah final:** cukup **isi `payment_approve.total_ppn` / `total_pph` / `admin_bank` dari nilai `tr_rp_pengajuan_d`** pada titik dokumen masuk ke `payment_approve`, LALU ubah `form_payment_new` untuk **prefill dari kolom itu** (bukan hitung ulang). Tidak ada perubahan skema.

### 3b. Penelusuran titik masuk `payment_approve` + status data (2026-09-10)

Titik INSERT ke `payment_approve` (abaikan `request_payment_backup` = backup):
- `request_payment/controllers/Request_payment.php`: `save_approval_cons()` (baris 752), `save_approval()` (insert baris 1073) — alur approve lama per-tipe.
- `request_payment/models/Request_payment_model.php`: `insert_batch('payment_approve')` (baris 1274) — batch approve per-tipe (kasbon/transport/periodik/direct_payment/nonpo). `$arr_header`-nya saat ini **tidak** memuat kolom pajak untuk semua tipe.
- `approval_request_payment/controllers/Approval_request_payment.php` (baris 155).
- Alur pengajuan BARU (`tr_rp_pengajuan_h/d`) hanya di `save()` (baris 203/223); method approve lain tidak menyentuh tabel itu.

**TEMUAN UTAMA (via cek DB dev): untuk PPH & PPN, DATA SUDAH MENGALIR.** Untuk 3 dokumen kasbon yang overlap, nilai `payment_approve.total_ppn`/`total_pph` **PERSIS SAMA** dengan `tr_rp_pengajuan_d.nilai_ppn`/`nilai_pph`:
- KS-2026-00221: req(22000/5000) = pay(22000/5000) ✅
- KS-2026-00254: req(44000/10000) = pay(44000/10000) ✅
- KS-2026-00220: req(1.171.500/266.250) = pay(sama) ✅
- Pengajuan PGJ-0001 & PGJ-0002 sudah `status=done`; `tr_rp_record` terisi 5 baris.

**Artinya masalahnya BUKAN alur data, tapi TAMPILAN FORM.** Nilai sudah ada di `payment_approve`, tapi `form_payment_new` mengabaikannya (PPN dihitung ulang dari invoice, PPH di-nol-kan lalu dihitung JS).

**Catatan Admin:** dokumen dgn `admin>0` (RQ transport) **belum masuk** ke `payment_approve` (join NULL) — tipe transport tampaknya belum/diproses lewat jalur berbeda. Untuk kasbon, admin kebetulan 0. Perlu dipastikan saat dokumen ber-admin masuk payment, nilai `admin` ikut ditulis ke `admin_bank` (lihat §4.1).

---

## 4. Rencana Perubahan (per file & baris) — FINAL

Kabar baik: untuk **PPH & PPN**, nilai request SUDAH tersimpan di `payment_approve.total_pph`/`total_ppn`. Jadi perubahan utama hanya di **form** (tampilkan yang sudah ada). **Admin** perlu 1 pemastian alur tulis.

### FOKUS UTAMA — Form menampilkan nilai yang sudah ada

#### 4.1. `views/form_payment_new.php` — input per item (baris ~300–340)
- **PPN (baris 302):** ganti sumber `value` input `dt[..][nilai_ppn]` menjadi `$item->total_ppn` bila > 0 (fallback ke perhitungan `tr_invoice_po` lama bila kosong).
- **PPH (baris 300):** isi `value` input `dt[..][nilai_pph]` dari `$item->total_pph`; set dropdown `tipe_pph` (baris 320) sesuai `$item->tipe_pph`.
- **Admin:** `$item->admin_bank` sudah dibaca (baris 340); pastikan nilainya ter-prefill ke input `biaya_admin` yang dikirim ke `save_payment_new`.

#### 4.2. `views/form_payment_new.php` — blok `<script>` (~780–850)
- Handler `change_nilai_pph` / `tipe_pph`: **jangan menimpa** nilai prefill saat halaman load; hanya recalc bila user aktif mengubah dropdown.

#### 4.3. `controllers/Pembayaran_material.php` — `form_payment_new()` (baris 159–221)
- `SELECT a.*` (baris 190–194) sudah membawa `total_ppn`, `total_pph`, `admin_bank`, `tipe_pph` — tidak perlu query tambahan. Cukup pastikan field tersedia di view.

### PEMASTIAN — Alur tulis Admin ke payment_approve

#### 4.4. Titik INSERT ke `payment_approve` (untuk tipe ber-admin, mis. transport)
- Di `request_payment/models/Request_payment_model.php` (batch, baris ~1180–1274) & `controllers/Request_payment.php` (`save_approval*`), saat menyusun `$arr_header`/`$header`, **tambahkan** `admin_bank` (dan bila perlu `total_ppn`/`total_pph`) dari `tr_rp_pengajuan_d`/`tr_rp_record` untuk tipe yang belum membawanya — agar konsisten dengan kasbon.
- **[perlu cek]** kenapa dokumen transport ber-admin belum masuk `payment_approve` (apakah memang belum diproses, atau jalur berbeda).

#### 4.5. Konsistensi
- `controllers/Pembayaran_material.php` baris 1449–1451 menulis `total_ppn`/`total_pph`/`tipe_pph` ke `payment_approve` saat simpan payment. Setelah 4.1, pastikan pembacaan awal (prefill) dan penulisan akhir tidak saling menimpa dengan nilai keliru.

---

## 5. Risiko & Catatan

- **Sangat aman:** §4.1–§4.3 murni tampilan/prefill (code-only), nilai sumber sudah ada di DB. Rollback mudah.
- **Tidak ada ALTER TABLE.**
- **PPH & PPN praktis tinggal 1 langkah** (perbaiki form). **Admin** perlu pemastian §4.4 untuk tipe transport.
- **DB dev diakses via** `C:\xampp74\php\php.exe` + mysqli (user `alief`) — MariaDB client CLI TIDAK kompatibel (MySQL 8).
- Nomor baris per 2026-09-10; verifikasi ulang sebelum edit.

---

## 6. Urutan Eksekusi yang Disarankan

1. Implement §4.1 + §4.2 + §4.3: form menampilkan `total_ppn`/`total_pph`/`admin_bank` + stop JS menimpa. **Ini sudah cukup untuk PPH & PPN.**
2. Uji cepat di dev pakai dokumen kasbon overlap (KS-2026-00220/221/254) — nilai harus langsung muncul.
3. §4.4: pastikan admin ikut tertulis ke `payment_approve` untuk tipe transport; uji dgn dokumen ber-admin (RQ-2026-00151/152).
4. §4.5: selaraskan baca-prefill vs tulis-simpan.
5. Uji end-to-end: pengajuan `request_payment` (checklist ppn/pph/admin) → proses payment `pembayaran_material` → ketiga nilai auto-terisi.
