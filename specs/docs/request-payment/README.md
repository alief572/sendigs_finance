# Request Payment → Approval → Excel → Payment (perombakan)

Dokumen perombakan alur Request Payment. Bahan dari klien: `specs/input/Request Payment/Request Payment ke Payment.html`.

## Konsep

- **1 PGJ = 1 batch approval** (tetap seperti sekarang, 1 company). Direktur tetap approve banyak dokumen sekaligus.
- Di dalam 1 PGJ ada **1 atau lebih transfer**. 1 transfer = 1 baris di Excel = 1 kali kirim uang = 1 catatan di Payment.
- Default 1 dokumen = 1 transfer. Beberapa dokumen boleh **digabung jadi 1 transfer** walau kategori, request by, atau penerima beda (company sudah pasti sama dalam 1 PGJ), admin cukup 1x. **Gabung dulu, baru isi rekening tujuan** (Bank, No. Rekening, Nama Penerima): 1 transfer = 1 rekening. Transfer 1 dokumen terisi otomatis dari rekening di dokumen (bisa diedit); transfer gabungan dikosongkan dan diisi ulang.
- Admin (nominal + ditanggung penerima/perusahaan) dan rekening tujuan ditentukan di **Request Payment**, karena Excel pengajuan adalah acuan transfer. Menu **Payment hanya mencatat dan menjurnal**.
- **Lampiran tambahan per dokumen** (opsional) di Atur Pembayaran, wadah file yang mendadak dibutuhkan. Ikut tercetak di **Print Dokumen** pada Record, setelah lampiran awal dokumen (cetakan sistem `print_cash` / `print_kasbon` / `print_direct_payment` menampilkan lampiran di bawah form).

## Isi folder

| File | Isi |
|---|---|
| `index.html` | Mockup yang bisa diklik. Tampilan dan alur per menu mengikuti view yang berjalan sekarang (`request_payment/views/rp_index.php`, `approval_request_payment/views/index.php` + `detail.php`, `record_request_payment/views/index.php` + `detail.php` + `items.php`, `pembayaran_material/views/index_payment_new.php` + `list_request_payment.php` + `form_payment_new.php`). Yang baru hanya halaman Atur Pembayaran dan bagian bertanda oranye |
| `flow-request-payment.drawio` / `.png` | Flow process BPMN (to-be) Request Payment → Approval → Record → Payment, termasuk hubungan ke Bank dan Alokasi Penerimaan (jenis Pembayaran) |
| `prosedur-pembayaran.xlsx` | Prosedur Pembayaran (format mengikuti `specs/input/Prosedur Pembelian Non Operation.xlsx`): konsep, jalur pengajuan yang masuk (prosedur tiap jalur di `../prosedur-pengajuan-pembayaran/`), langkah C1–C9 Request Payment → Payment, akun debit per jalur, jurnal Payment P1–P8 + variasi V1–V3 (COA sistem), pertanyaan terbuka. Dibuat dari `build_prosedur.py` (+ `prosedur_xlsx.py`): `py specs/docs/request-payment/build_prosedur.py specs/docs/request-payment/prosedur-pembayaran.xlsx` |
| `smoke-test.js` | Smoke test jsdom untuk seluruh alur (168 pemeriksaan) |
| `uji-coba-request-payment/` + `.zip` | Paket uji coba untuk Finance, bisa dibuka tanpa internet: `index.html` (salinan mockup yang memuat Font Awesome, driver.js, SheetJS dari folder `lib`), `lib/` (library + lisensi), `BACA DULU.txt`. Dibuat ulang setiap mockup berubah: salin `index.html` dengan 4 alamat CDN diganti ke `lib/...` dan 2 pesan "butuh koneksi internet" diganti, lalu zip ulang. |

Mockup dibuka langsung di browser. Ikon (Font Awesome) dan Excel (SheetJS) dimuat dari CDN, jadi butuh internet. **Reset data** di bar atas mengembalikan data contoh. **Ringkasan perubahan** di bar atas merangkum perubahan dan hal yang perlu dikonfirmasi.

Alur menu: Request Payment → (Atur Pembayaran) → Approval Request Payment → Record Request Payment (Print = Excel acuan transfer) → Payment List → Payment → Pilih Jenis Payment → List Request Payment → Proses → Form Payment → Submit.

## Smoke test

```
node specs/docs/request-payment/smoke-test.js
```

Butuh `jsdom` (`npm i jsdom`) atau env `JSDOM_PATH` yang menunjuk ke folder jsdom.

## Keputusan (17-Nov-2026)

- Approver bisa edit **rekening tujuan saja** di Approval; berlaku saat Simpan Keputusan.
- Bank pembayar per company, No COA jurnal, dan letak menu Payment List disetting IT Developer.
- PGJ lama (admin per dokumen) dibiarkan apa adanya.
- Format Request Payment seragam: PPN/PPh tetap ada untuk semua kategori, kasbon tanpa pajak cukup tidak dicentang.
- Bank Charge di Payment = biaya yang dipotong bank, default admin dari Request Payment (0 bila gratis), bisa diedit sesuai bukti bayar (keterangan wajib kalau beda).
- Jurnal Payment mengikuti jurnal aktual (RP-R-36, `input/Ditanggung Penerima.png`, `input/Ditanggung Perusahaan.png`, `input/Admin 0.png`): Payment Bank = nominal transfer (ditanggung penerima: DIBAYARKAN − admin), Cr Bank 2 baris (TRF + BIAYA TRANSFER), Administrasi Bank otomatis = Payment Bank + Bank Charge − Total Payment (Dr perusahaan, 0 penerima). Admin aktual Gratis (RP-R-38): transfer penuh, Bank Charge 0, tanpa admin di jurnal; Cr Administrasi Bank hanya kalau Payment Bank diubah lebih kecil padahal bank tidak memotong. Total keluar bank = Payment Bank + Bank Charge.
- Nominal admin tetap 0 / 2.500 / 6.500.
- Excel: cukup 1 kolom JUMLAH DIBAYARKAN BANK setelah DIBAYARKAN (Jumlah Transfer dan Total Keluar Bank dihapus); kolom Nama menjadi Nama Rekening; KETERANGAN PEMBAYARAN paling kanan.
- Jurnal Payment tanpa baris penanda per kategori; pola per kategori ada di halaman Ringkasan.

Permintaan dicatat di `specs/docs/requests.md`, bagian "Request Payment" (ID `RP-R-xx`).

## Print Record dan jurnal Payment

- **Print Voucher (Bukti Bank Keluar)** di Record Payment, per payment (RP-R-37): format `specs/input/Voucher STM - Copy.xlsx` sheet "BBK (2)", A4 portrait, dialog print → Save as PDF. Isi = jurnal Payment lengkap dengan kolom Debit / Kredit, termasuk Cr Bank TRF dan BIAYA TRANSFER (RP-R-39); Total Debit = Total Kredit. View Record Payment juga menampilkan Preview Jurnal. No `YYYY-COMPANY/BBK/romawi/urut` per company per bulan. Tanda tangan dan logo company selain STM (diisi user).

- **Print Record** (Excel) mengikuti `specs/input/Request Payment/Print di Record.xlsx`: judul, Company, No. Pengajuan, Dicetak pada, lalu kolom NO s.d. DIBAYARKAN per dokumen. Setelah DIBAYARKAN: JUMLAH DIBAYARKAN BANK (total DIBAYARKAN per transfer), Nama Rekening, Bank, No. Rek, TRANSFER, ADMIN DITANGGUNG, dan KETERANGAN PEMBAYARAN paling kanan (pengajuan apa, dokumen mana yang digabung jadi 1 payment). Kolom per transfer di-merge untuk dokumen yang digabung.
- **Jurnal Payment** mengikuti `specs/input/Request Payment/JURNAL PEMBAYARAN - SSC.xlsx` (sheet Jurnal Non Operation & Jurnal Petty Cash): Kasbon Dr Piutang Kasbon; Direct Payment, Periodik, Transportasi, Expense Dr Biaya; bayar kurang kasbon Dr Hutang Expense; Refill Petty Cash Dr Kas Kecil 1101-01-02, Cr Bank STM. PPN Dr PPN DN, PPh Cr PPh, Administrasi Bank otomatis (Dr ditanggung perusahaan, Cr bila bank tidak memotong), Cr Bank 2 baris per transfer: TRF dan BIAYA TRANSFER (RP-R-36). No COA apa adanya dari file: kosong ditulis (belum ada), akun Biaya ditulis sesuai item.

**Tutorial (driver.js):** tombol mengambang **Tutorial halaman ini** di pojok kanan bawah menjalankan panduan langkah demi langkah untuk halaman yang sedang dibuka (Request Payment, Atur Pembayaran, Approval, Record, Payment List, Form Payment). driver.js 1.3.1 dari jsDelivr, butuh internet.
