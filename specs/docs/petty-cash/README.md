# Petty Cash (mockup to-be, alur penuh)

Mockup alur Petty Cash dari awal sampai jurnal, mengikuti modul yang berjalan:

| Menu | Modul | Isi |
|---|---|---|
| Master Petty Cash | `petty_cash_master` | Budget per COA (plafon), COA Petty Cash. **Project ini: 1 master** untuk semua company |
| Transaksi Petty Cash | `expense_petty_cash` | Daftar pencatatan; Tambah / Edit = **halaman form** (small-box Budget / Budget Terpakai / Sisa Budget, Company, Request By, Keterangan opsional, Detail Item: COA, Pengeluaran, Spesifikasi, Jumlah, Nominal, Total, Evidence, Simulasi Jurnal); View = halaman detail; Buat Pelaporan |
| Pelaporan Petty Cash | `expense_petty_cash` | Daftar pelaporan (filter Company multi, Status; Aksi Ajukan / Detail / Print); **Ajukan** → halaman Konfirmasi |
| Approval Pelaporan | `expense_petty_cash` | Daftar (tombol Approval) → halaman detail dengan evidence → Approve / Reject (alasan 10–500 karakter) |
| Petty Cash VUCA/Sustain | `petty_cash_vuca_sustain` | Daftar (filter Company / Status; status Draft / Waiting Payment / Paid) → Payment Hutang = halaman konfirmasi → Proses Payment Hutang (dikirim ke Request Payment) |
| Request Payment → Approval Request Payment → Record Request Payment | `request_payment`, `approval_request_payment`, `record_request_payment` | **Konsep Request Payment terbaru** (mockup `../request-payment`): dokumen Refill / Petty Cash Hutang → Atur Pembayaran (transfer, gabung dokumen, rekening tujuan, admin) → PGJ → Approval (reject per dokumen) → Record |
| Payment List | `pembayaran_material` | Payment → Pembayaran Non PR → pilih **1 transfer** → Form Payment (pilih bank, bank charge, bukti bayar) → Submit. 1 transfer = 1 payment = 1 nomor BK |
| Jurnal All · Petty Cash | `jurnal_all` | Staging jurnal Petty Cash, Refill Pettycash, Payment Hutang Petty Cash; Posting |

## Tampilan mengikuti sistem (PC-R-08)

Halaman Transaksi, Pelaporan, Approval, dan Petty Cash VUCA/Sustain mengikuti view yang berjalan di `origin/development` (`expense_petty_cash/views/pencatatan|pelaporan|approval`, `petty_cash_vuca_sustain/views`, JS `assets/js/modules/expense_petty_cash/*.js`): kolom, label status, tombol, teks dialog, validasi form (Company, Request By, Jumlah, Nominal, Evidence per baris), Buat Pelaporan → draft lalu **Ajukan** dari daftar Pelaporan, beda minggu ditolak saat Buat Pelaporan. Field Approver di master tidak ada di sistem, jadi dihapus dari mockup. Input Nominal diketik langsung (format ribuan saat keluar dari field), seperti `form_pencatatan.js`. Yang ditandai oranye hanya perubahan yang diminta.

## Satu master untuk project ini (PC-R-06)

- Project ini hanya punya **1 Master Petty Cash** (satu kas fisik). Company tidak ada di master; company dipilih di tiap pencatatan, seperti form pencatatan sekarang.
- Pencatatan VUCA / SUSTAIN dari master itu menjadi **hutang ke STM** (jurnal company + jurnal STM, lalu Payment Hutang).
- Budget, terpakai, sisa, dan Saldo seharusnya dihitung dari satu master itu, mencakup pencatatan semua company.
- Tombol Tambah master tetap ada untuk project lain yang butuh lebih dari 1 master (mis. kas kecil per company dengan beban berbeda).
- Pelaporan tetap 1 company, 1 minggu.

## Yang berubah (to-be)

| No | Perubahan | Sekarang | To-be |
|---|---|---|---|
| 1 | Field **Saldo Petty Cash** di Pelaporan (PC-R-01) | Tidak ada | Diisi pemegang petty cash dari **hitung fisik uang** di halaman konfirmasi sebelum Ajukan, sebagai kontrol (arahan atasan) |
| 2 | Waktu jurnal pencatatan (PC-R-02) | Dibuat saat pencatatan disimpan (`create_journal_for_pencatatan`); dihapus dan dibuat ulang saat edit | Dibuat saat **Pelaporan di-approve**, untuk semua pencatatan di RPC itu |
| 3 | **Preview jurnal di Approval** (PC-R-04) | Approval menampilkan jurnal yang sudah tersimpan per pencatatan | Kotak tersendiri **Preview Jurnal (dibentuk saat Approve)**: semua jurnal per pencatatan (tanggal pencatatan), total debit, info Request Payment refill / Payment Hutang |
| 5 | **Peringatan selisih saldo** (PC-R-05) | Tidak ada | Bila Saldo Petty Cash ≠ saldo seharusnya: kotak peringatan di konfirmasi, dialog Ajukan, baris Approval disorot ("Perlu dicek"), halaman Approval ("Dept Head Finance wajib mengecek"), dan dialog Approve. Ajukan dan Approve tetap bisa |
| 7 | **Request Payment & Payment konsep terbaru** (PC-R-07) | Saat approve Pelaporan / Proses Payment Hutang, Request Payment langsung siap bayar di Payment List; dipilih per dokumen | Masuk menu **Request Payment** dulu → Atur Pembayaran (PGJ) → Approval Request Payment → Record → Payment **per transfer**, 1 nomor BK per payment. Refill: company **STM**, rekening pemegang petty cash (juga untuk pelaporan VUCA/SUSTAIN, karena 1 kas). Payment Hutang: company VUCA/SUSTAIN, rekening STM |
| 8 | **Bank dipilih saat Payment** (PC-R-07) | Payment Hutang: bank ditulis tetap di kode (company 1101-02-09, STM 1101-02-01) | Pilih Bank (rekening company yang membayar) + **Bank STM penerima** (default sesuai rekening tujuan) untuk jurnal sisi STM. Refill tetap pilih bank STM |
| 6 | **COA Payment Hutang** (PC-R-05) | Company Dr 2203-01-01 Hutang Hubungan Istimewa; STM Cr 1103-01-01 (juga untuk SUSTAIN) | Company Dr **2103-01-0x Hutang ke STM**; STM Cr **1103-01-0x Piutang VUCA/SUSTAIN**, sama dengan jurnal pencatatan. Bank masih seperti kode sekarang |
| 4 | **COA Petty Cash di Master** (PC-R-04) | Master hanya punya COA biaya (Detail COA); akun kas di jurnal tertulis tetap `1101-01-02 Kas Kecil` | Field **COA Petty Cash** (wajib, pilihan seluruh COA master) di bawah Keterangan + kolom di daftar master. Dipakai di jurnal pencatatan (Kredit) dan refill (Debit) |

Selain dua hal itu, alur dan aturan di mockup mengikuti kode yang ada sekarang.

### Kontrol Saldo Petty Cash

Total Budget berfungsi sebagai **plafon** (dana tetap). Pencatatan yang refill-nya sudah dibayar tidak dihitung terpakai lagi (`get_budget_terpakai`).

```
Saldo seharusnya = Total Budget − pengeluaran yang belum di-refill   (= Sisa Budget)
Selisih          = Saldo Petty Cash (hitung fisik) − Saldo seharusnya
```

Contoh RPC STM: 5.000.000 − pelaporan ini 1.300.000 − pencatatan STM minggu ini yang belum dilaporkan 50.000 − pelaporan VUCA yang belum di-refill 1.000.000 = **2.650.000**. Kalau hitung fisik 2.600.000, selisihnya −50.000.

- Saldo seharusnya dan Saldo Petty Cash disimpan saat Ajukan.
- Kontrol tampil di halaman konfirmasi, daftar Pelaporan, daftar dan halaman Approval, dan cetak Pelaporan.
- Bila ada selisih: **peringatan** tampil (konfirmasi, Ajukan, daftar & halaman Approval, Approve). Tetap bisa diajukan dan di-approve; Dept Head Finance mengecek selisihnya (PC-R-05).
- Saldo Petty Cash wajib diisi saat Ajukan.

### Jurnal pencatatan saat approve

- Tanggal jurnal = **tanggal masing-masing pencatatan**.
- Bentuk tetap seperti sekarang, 1 jurnal per pencatatan:
  - **STM:** Dr Beban / Cr 1101-01-02 Kas Kecil.
  - **VUCA / SUSTAIN:** buku company Dr Beban / Cr Hutang ke STM (2103-01-01 / 2103-01-02), dan buku STM Dr Piutang (1103-01-01 / 1103-01-02) / Cr Kas Kecil.
- Konfirmasi dan Approval menampilkan preview jurnal.
- Draft, pencatatan yang diedit, dan pencatatan yang pelaporannya ditolak tidak punya jurnal.
- Jurnal pencatatan lama (dibuat saat simpan) dibiarkan.

## Yang tidak berubah (as is)

- **Saat approve Pelaporan:** dibuat dokumen Request Payment `refill_pettycash` (jumlah = Grand Total RPC); untuk VUCA/SUSTAIN juga record Payment Hutang `PHP-…` (draft). Klik Payment Hutang → dokumen `petty_cash_hutang`, status waiting payment.
- **Refill:** Dr COA Petty Cash master / Cr Bank yang dipilih, di buku STM. Setelah dibayar, Sisa Budget kembali.

## Request Payment & Payment (to-be, PC-R-07)

Mengikuti mockup `../request-payment` (RP-R-xx), hanya untuk dokumen petty cash:

1. **Request Payment**: Refill (company STM, rekening pemegang petty cash) dan Petty Cash Hutang (company VUCA/SUSTAIN, rekening STM). Centang dokumen 1 company → **Atur Pembayaran & Ajukan**.
2. **Atur Pembayaran**: default 1 dokumen = 1 transfer; bisa digabung (rekening dikosongkan, diisi ulang); admin 0 / 2.500 / 6.500, ditanggung penerima / perusahaan → **PGJ**.
3. **Approval Request Payment**: Rincian Transfer; reject per dokumen (alasan wajib) → dokumen kembali ke Request Payment.
4. **Record Request Payment**: PGJ yang sudah diputuskan, status bayar per transfer.
5. **Payment List**: Payment → Pembayaran Non PR → pilih 1 transfer → Form Payment: Pilih Bank (rekening company), Bank STM penerima (Payment Hutang), Bank Charge (default admin, keterangan wajib bila beda), bukti bayar wajib → Submit. **1 nomor BK** untuk seluruh jurnal payment itu.

Jurnal Payment:

| Jenis | Buku | Jurnal |
|---|---|---|
| Refill | STM | Dr COA Petty Cash (per dokumen), Dr Administrasi Bank (bila ditanggung perusahaan), Cr Bank yang dipilih |
| Payment Hutang | company | Dr 2103-01-0x Hutang ke STM (per dokumen), Dr Administrasi Bank (bila ditanggung perusahaan), Cr Bank company yang dipilih |
| Payment Hutang | STM | Dr Bank STM penerima, Dr Administrasi Bank (bila dipotong dari transfer), Cr 1103-01-0x Piutang VUCA/SUSTAIN |

Sekarang di kode Payment Hutang memakai Dr 2203-01-01 Hutang Hubungan Istimewa dan Cr 1103-01-01 untuk semua company, dengan bank ditulis tetap. No COA Administrasi Bank belum ada (sama dengan mockup Request Payment). Nama bank VUCA/SUSTAIN di mockup hanya contoh.

## Isi folder

| File | Isi |
|---|---|
| `index.html` | Mockup alur penuh. Tutorial: "Pencatatan sampai Approval" (12 langkah), "Request Payment sampai Payment" (10 langkah) |
| `test-case-petty-cash.md` | Test case alur utama dan uji negatif |
| `smoke-test.js` | Smoke test jsdom (123 pemeriksaan) |

Tanggal hari ini di mockup: 23-Nov-2026. COA beban (6101-…), nomor payment (`PAY-2611-…`), dan nama bank hanya contoh.

## Tampilan jurnal (PC-R-10)

Semua preview / lihat jurnal (form pencatatan, view, Approval Pelaporan, Payment refill / Payment Hutang, Jurnal All · Petty Cash) memakai template Jurnal All sistem (`jurnal_all/views/preview.php`, sumber PCS): kotak info No. Transaksi, No. Referensi, Company, Divisi, Tgl Jurnal, Status, Kategori Transaksi; per buku company tabel Tanggal · Tipe · Company · No. COA · Nama COA · Keterangan · No. Reff · Debit · Kredit; jurnal 2 company per sisi (Sisi VUCA / Sisi STM (Inter-Company)) dengan TOTAL COMPANY, satu company dengan GRAND TOTAL; keterangan seimbang. Keterangan per baris informatif (mis. *Konsumsi - PCP-2026-0086 (10 x 35.000)*, *Hutang ke STM atas petty cash PCP-… (dibayar dari kas kecil STM)*, *Pengeluaran petty cash PCP-… untuk VUCA (Petty Cash)*).

**Belum disamakan (keputusan 26-Nov-2026):** form Payment di mockup ini masih konsep Request Payment sebelum RP-R-36..39 (Bank Charge, Payment Bank = transfer + charge, jurnal Administrasi Bank). Konsep terbaru ada di `specs/docs/request-payment` (Payment Bank = transfer, Cr Bank TRF + BIAYA TRANSFER, admin gratis tanpa jurnal, Print Voucher BBK); penyamaan menyusul.

## Perlu dikonfirmasi (diisi user)

Tidak ada pertanyaan terbuka.

Sudah dijawab:
- PC-R-05: selisih → peringatan, tetap bisa disimpan, dicek Dept Head Finance; Saldo Petty Cash wajib; Payment Hutang pakai Hutang ke STM; COA Petty Cash wajib, untuk VUCA/SUSTAIN dipakai di sisi STM.
- PC-R-07: selisih saldo hanya informasi (tidak dijurnal); bank Payment Hutang dipilih saat Payment.
- PC-R-09: refill hanya untuk kas kecil STM, jadi refill selalu company STM / PGJ STM (juga untuk pelaporan VUCA/SUSTAIN); penerimaan STM dari Payment Hutang dijurnal saat Payment (Bank STM penerima), tidak lewat Alokasi Penerimaan.

## Smoke test

```
node specs/docs/petty-cash/smoke-test.js
```
