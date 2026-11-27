# Alokasi Penerimaan: sub modul tampungan

Pengembangan menu Penerimaan → Alokasi. Jenis alokasi yang selama ini hanya label (tanpa modul lanjutan) dibuatkan sub modul tampungan. Proses lanjutan sudah dibuat untuk Unlocated Penerimaan (pindah ke Penerimaan Piutang Invoice / Kwitansi Deposit), Pengembalian Kasbon (Verifikasi Pengembalian Kasbon), Mutasi (Request Mutasi + Proses Mutasi), dan Transaksi Bank; Pembayaran belum dibahas.

## Status per bagian (AP-R-42, 27-Nov-2026), baca dulu sebelum mengerjakan

| Bagian | Status |
|---|---|
| **Alokasi** → split ke **Unlocated Penerimaan** | ✅ Siap dikerjakan |
| **Unlocated Penerimaan**: Proses (jurnal Debit Bank / Kredit Penerimaan Belum Teridentifikasi + nomor UNL, tanggal jurnal = tanggal bank di rekening koran), Sudah Diproses (pindah ke Penerimaan Piutang Invoice / Kwitansi Deposit), **Daftar UNL** (record + pencarian dua arah, nomor UNL di Status Alokasi) | ✅ Siap dikerjakan |
| **Penerimaan Piutang**: tampilan dan proses **tetap seperti sistem**; yang berubah **hanya jurnal**: baris kas Debit **Penerimaan Belum Teridentifikasi** bila draft berasal dari Unlocated, Debit **Bank** bila langsung dari rekening koran | ✅ Siap dikerjakan (jurnal saja) |
| Pengembalian Kasbon, Verifikasi Pengembalian Kasbon, Mutasi, Request / Approval Mutasi, Transaksi Bank, Data Transaksi Bank, Pembayaran, Alokasi Kalibrasi, Dashboard Alokasi | ⏳ **Draft, jangan dikerjakan dulu** (banner merah di mockup) |
| Jurnal All / Report Jurnal All di mockup | Halaman bantu untuk melihat jurnal yang terbentuk, bukan bagian yang dikerjakan |

Preview jurnal di form proses (sebelum masuk Jurnal All) hanya tabel jurnal (Tanggal, Tipe, No. COA, Nama COA, Keterangan, No. Reff, Debit, Kredit); kotak info No. Transaksi / Status dsb. hanya ada di Jurnal All.

## Kondisi sistem saat ini (dari kode)

- **Alokasi** (`application/modules/alokasi`): upload rekening koran CSV (2 format bank, `Rekening_koran_parser`), tiap baris `tr_alokasi_detail` dialokasikan/split ke `tr_alokasi_split.jenis_alokasi`. Total split wajib = nominal transaksi.
- Jenis: 1 Penerimaan Piutang dan 8 Deposit → draft di Penerimaan Piutang (`penerimaan_uang`); 2 Unlocated Penerimaan → `unlocated_penerimaan` (daftar + rollback); 7 Alokasi Kalibrasi → `alokasi_kalibrasi`. **3 Pengembalian Kasbon, 4 Mutasi, 5 Transaksi Bank, 6 Pembayaran: tidak ada modul lanjutan dan tidak dijurnal.**
- `tr_alokasi_detail.nominal_debit` = uang masuk, `nominal_kredit` = uang keluar.

## Isi folder

| File | Isi |
|---|---|
| `index.html` | Mockup klik: Alokasi (seperti `alokasi/views/index.php`, modal Alokasi Split dan Detail Alokasi), 4 sub modul tampungan baru dengan template `unlocated_penerimaan/views/index.php`, plus Unlocated dan Alokasi Kalibrasi memakai template yang sama |
| `test-case-mutasi.md` | Test case Request Mutasi → Approval → Upload rekening koran → Alokasi Mutasi → Realisasi / Pasangkan (SUSTAIN → STM) |
| `test-case-unlocated.md` | Test case Unlocated Penerimaan: 6 case (Belum Diproses → Proses jurnal → Sudah Diproses → pindah ke Penerimaan Piutang Invoice / Kwitansi Deposit) |
| `test-case-transaksi-bank.md` | Test case Transaksi Bank dari rekening koran (bunga, pajak bunga, biaya admin, biaya transfer) |
| `kamus-alokasi-penerimaan.xlsx` | Kamus: istilah, penomoran, jenis alokasi, menu & fungsi, pola jurnal, keputusan, pertanyaan terbuka. Dibuat dari `build_kamus.py`; diperbarui setiap ada perubahan |
| `test-case-pengembalian-kasbon.md` | Test case Pengembalian Kasbon: draft rekening koran di Verifikasi Pengembalian Kasbon, Finance memilih Expense Report |
| `test-case-dashboard.md` | Test case Dashboard Alokasi: card belum diproses, filter, export Excel |
| `prosedur-penerimaan.xlsx` | Prosedur Penerimaan (format mengikuti `specs/input/Prosedur Pembelian Non Operation.xlsx`): konsep, langkah utama, jenis alokasi, satu sheet per jalur (Penerimaan Piutang, Unlocated, Pengembalian Kasbon, Mutasi, Transaksi Bank, Pembayaran) dengan langkah + PIC + jurnal, pertanyaan terbuka. Dibuat dari `build_prosedur.py` (+ `prosedur_xlsx.py`): `py specs/docs/alokasi-penerimaan/build_prosedur.py specs/docs/alokasi-penerimaan/prosedur-penerimaan.xlsx` |
| `flow-alokasi-penerimaan.drawio` | Flow process BPMN (to-be), 5 halaman: Alur Utama (upload → alokasi → 8 jenis → Dashboard) dan detail Unlocated, Pengembalian Kasbon, Mutasi, Transaksi Bank. Kotak biru bisa diklik ke halaman detail. Buka di draw.io / app.diagrams.net |
| `flow-alokasi-*.png` | Gambar tiap halaman flow process (pratinjau tanpa draw.io) |
| `smoke-test.js` | Smoke test jsdom (207 pemeriksaan) |

Buka `index.html` langsung di browser (ikon dari CDN). **Reset data** di bar atas mengembalikan data contoh; **Ringkasan perubahan** berisi yang baru dan yang perlu dikonfirmasi.

## Smoke test

```
node specs/docs/alokasi-penerimaan/smoke-test.js
```

Butuh `jsdom` (`npm i jsdom`) atau env `JSDOM_PATH` yang menunjuk ke folder jsdom.

Permintaan dicatat di `specs/docs/requests.md`, bagian "Alokasi Penerimaan" (ID `AP-R-xx`).

## Keputusan (17-Nov-2026)

- Nama menu tampungan = nama jenis alokasi: Pengembalian Kasbon, Mutasi, Transaksi Bank, Pembayaran.
- Kolom Nominal Alokasi juga dipakai di Unlocated Penerimaan.
- Jenis alokasi dibatasi arah dana. Uang masuk: Penerimaan Piutang, Unlocated Penerimaan, Pengembalian Kasbon, Alokasi Kalibrasi, Deposit. Uang keluar: Pembayaran. Keduanya: Mutasi, Transaksi Bank.
- Rollback dari tampungan (pilihan B): hanya alokasi di menu itu yang dibatalkan. Alokasi lain di transaksi yang sama tetap; transaksinya berstatus **Sebagian · sisa Rp …** di menu Alokasi, dan modal Alokasi hanya mengisi sisanya.
- Unlocated Penerimaan (AP-R-35): dua tab. **Belum Diproses**: tombol **Proses** menjurnal Debit Bank / Kredit **Penerimaan Belum Teridentifikasi** dan membentuk 1 nomor Unlocated baru (mockup `UNL-YYMMNNNNN`; format nomor dan nomor COA diisi user); rollback hanya di tab ini. **Sudah Diproses**: dua tombol, **Pindahkan ke Penerimaan Piutang Invoice** (jenis 2 → 1, draft di tab Invoice) dan **Pindahkan ke Penerimaan Piutang Kwitansi Deposit** (jenis 2 → 8, draft di tab Kwitansi Deposit), tanpa rollback. Draft membawa nomor Unlocated sebagai referensi; jurnal penerimaannya **Debit Penerimaan Belum Teridentifikasi** (bukan Bank) / Kredit sesuai penerimaan.
- Mutasi tetap wajib lewat Request Mutasi + approval (menunggu konfirmasi atasan). Proses dari satu tombol **Proses Mutasi** di tampungan, untuk baris keluar maupun masuk: user memilih **Request Mutasi** yang sudah di-approve. Belum ada realisasi → sistem membuat MTR + jurnal (tanggal & nilai aktual dari rekening koran); sudah ada dari sisi lain → baris ditautkan ke realisasi itu (jurnal tetap per sisi).
- Transaksi Bank: tombol **Proses** di tampungan membuka form Transaksi Bank yang terisi dari rekening koran (jenis, tanggal, company, COA bank, nilai); user memilih COA lawan.
- Baris tampungan yang sudah diproses (MTR/KK/KM) tidak bisa di-rollback sebelum dibatalkan di modulnya.
- Jurnal mutasi dibuat **per sisi** saat Proses Mutasi, di buku company rekening koran baris itu (usulan versi awal, fix menyusul). Antar company: keluar Dr Piutang ke company tujuan / Cr bank asal; masuk Dr bank tujuan / Cr Hutang ke company asal. Satu company: lewat Kas/Bank dalam Perjalanan. Tidak ada jurnal saat request dibuat/di-approve.
- Rekening di mockup mengikuti sistem: BCA 436.300.5287 (STM), BCA 436.400.0300 (SUSTAIN), OCBC NISP 7788.0000.0417 (VUCA). Company dikenali dari rekening; tidak ada kolom company baru di Master Bank.
- **Target Accounting dihapus** dari Request Mutasi dan Transaksi Bank. Buku jurnal ditentukan rekening: sisi keluar di buku company bank asal, sisi masuk di buku company bank tujuan. Form dan daftar menampilkan company otomatis, mis. "SUSTAIN → STM (antar company)".
- Transaksi Bank hanya dari rekening koran (input manual ditutup); satu baris rekening koran = satu transaksi, termasuk biaya transfer. Form: **Bank** (COA Tujuan, otomatis) dan **Akun Lawan** (COA Asal, dipilih). Jurnal mengikuti sistem: Penerimaan Dr Bank / Cr Akun Lawan, Pengeluaran Dr Akun Lawan / Cr Bank.
- Akun lawan Transaksi Bank dipilih manual oleh user (menu dan isi otomatis Kata Kunci Akun Lawan dihapus). Form menampilkan preview jurnal dan arah CR/DB.
- Menu dipisah: Mutasi Bank (Request Mutasi, Approval Mutasi), Transaksi Bank (Data Transaksi Bank). **List Mutasi dihapus** (AP-R-33): realisasi MTR dan jurnal per sisi dilihat di tampungan Penerimaan → Mutasi (kolom Proses, link Lihat jurnal); di Request Mutasi status request menjadi Terealisasi MTR-…. Menu Penerimaan → Mutasi tidak berubah. Di kode tetap satu modul `request_mutasi`.
- Sistem dipakai mulai bersih tanpa data awal (mis. go-live per Desember): tidak ada pilihan menautkan ke transaksi KK/KM atau realisasi MTR manual lama; semua dibuat dari rekening koran.
- **Tutorial (driver.js):** tombol mengambang **Tutorial halaman ini** di pojok kanan bawah menjalankan panduan langkah demi langkah untuk halaman yang sedang dibuka. driver.js 1.3.1 dari jsDelivr (butuh internet); langkah yang elemennya belum tampil dilewati.
- **Pengembalian Kasbon:** seperti Penerimaan Piutang. Baris rekening koran yang dialokasikan Pengembalian Kasbon jadi draft di menu Expense → Verifikasi Pengembalian Kasbon (tab Mutasi Bank, Pengajuan Karyawan, Selesai). Finance klik Proses Verifikasi dan memilih sendiri Expense Report-nya; tidak ada pencocokan otomatis. Perbandingan bank, tanggal, nominal dengan bukti user membantu 3 checklist; ketiganya (bukti, mutasi, nominal) dicentang manual oleh Finance, tidak ada yang otomatis. Tanpa bukti user Finance menunggu. Jurnal tetap satu saat disetujui (Dr Bank / Cr 1103-01-14). Tampungan Pengembalian Kasbon = status berjalan.
- **Unlocated → Kwitansi Deposit:** satu tombol untuk satu jenis. Tombol Kwitansi Deposit membuka pilihan kwitansi `KD/YYYY/MM/NNNN` yang Menunggu Pembayaran (opsional, 1 data per kwitansi, aturan sama dengan jenis Deposit di Alokasi). Halaman Penerimaan Piutang di mockup mengikuti 3 tab sistem: Penerimaan Piutang Invoice, Penerimaan Piutang Kwitansi Deposit, Report Penerimaan.
- **Dashboard Alokasi** (menu baru Penerimaan → Dashboard Alokasi): 9 card per jenis alokasi berisi jumlah dan total nominal yang belum diproses, umur tertua, dan sebaran umur 0–7 / 8–30 / >30 hari; tabel Rincian Belum Diproses; klik card menampilkan rincian jenis itu di bawahnya (tidak pindah halaman), tombol Buka menu untuk ke modulnya. Filter rentang tanggal transaksi, Company (STM / VUCA / SUSTAIN), dan Bank. Export to Excel (sheet Ringkasan + Rincian) mengikuti filter; tanpa filter semua data terekspor. Isi card masih usulan, akan dicek user.
- **Autodebet pembayaran** (mis. BPJS) dicatat lewat **Transaksi Bank**: arah keluar/masuk mengikuti debit/kredit rekening koran, user hanya memilih COA lawan. Di sistem pilihan COA lawan = seluruh COA master; mockup menambah contoh 2103-01-07 Hutang Asuransi BPJS Ketenagakerjaan dan 5101-01-03 Biaya Pengeluaran Lainnya, serta baris contoh AUTODEBET BPJS KETENAGAKERJAAN 11/2026.
- Transaksi Bank menampilkan keterangan **uang masuk / uang keluar**: di form (Penerimaan (uang masuk) / Pengeluaran (uang keluar) + label) dan di Data Transaksi Bank (label di bawah Kode Transaksi).
- **Jurnal:** jurnal **Unlocated Penerimaan** (Dr Bank / Cr Penerimaan Belum Teridentifikasi) dan **Penerimaan Piutang** (Dr Bank atau Dr Penerimaan Belum Teridentifikasi sesuai asal) sudah final (AP-R-35, AP-R-40, AP-R-42); nomor COA Penerimaan Belum Teridentifikasi dan PPh 23 Disetor masih (diisi user). Jurnal Mutasi, Transaksi Bank, Pengembalian Kasbon masih usulan dan menyusul dari user.
- **Jurnal All (AP-R-37, AP-R-38):** menu Accounting → Jurnal All dan Report Jurnal All di mockup, tampilan mengikuti Jurnal All sistem (card + tab per sumber, filter Tahun / Bulan / Company, + Post → Detail jurnal → Posting). Semua jurnal Alokasi masuk staging (belum posting): tab **Penerimaan** + filter **Kategori Transaksi** (Penerimaan Piutang / Unlocated Penerimaan), tab baru **Jurnal Bank** (Mutasi, Transaksi Bank; Kategori & Arah), tab **Expense Report** (Pengembalian Kasbon). Unlocated yang sudah diproses boleh dipindah walau jurnalnya belum diposting.
- **Template jurnal (AP-R-39):** semua tampilan jurnal di Alokasi (Proses Unlocated, Proses Mutasi, Form Transaksi Bank, Verifikasi Pengembalian Kasbon, Lihat jurnal, Detail jurnal) mengikuti `jurnal_all/views/preview.php`: kotak info (No. Transaksi, No. Referensi, Company, Divisi, Tgl Jurnal, Status, Kategori Transaksi) + tabel Tanggal · Tipe · No. COA · Nama COA · **Keterangan per baris** · No. Reff · Debit · Kredit + GRAND TOTAL. Preview di form menampilkan No. Transaksi "(otomatis)" dan status "Preview · terbentuk saat Simpan"; preview Unlocated dan Transaksi Bank ikut berubah saat keterangan diketik.
- **Proses Penerimaan Piutang (AP-R-40, AP-R-42):** tombol **Proses** di tab Invoice / Kwitansi Deposit mengikuti `penerimaan_uang` apa adanya (`add.php` → `consulting_process.php`: customer dipilih, PPh 23 dipotong, centang invoice → kolom Nomor Invoice … Sisa Piutang + Total Piutang / Uang Masuk / Lebih Bayar / Kontrol → Proses, nomor `NNNNN/PU/XI/26`, Report Penerimaan). Yang berubah hanya jurnalnya. Draft dari Unlocated dijurnal **Debit Penerimaan Belum Teridentifikasi**, bukan Bank. Test case: `test-case-penerimaan-piutang.md`.
- **Pop-up Proses Unlocated (AP-R-43):** berurutan Detail Transaksi Bank (dari rekening koran: tanggal, bank + COA + buku company, keterangan, total debit / credit, saldo akhir, nominal dialokasikan ke Unlocated, rincian bila transaksi dipecah) → Data Unlocated (No Unlocated otomatis, Tanggal Jurnal = tanggal transaksi bank, Keterangan) → Jurnal (tabel saja).
- **Daftar UNL (AP-R-41):** tab ketiga di Unlocated Penerimaan, record semua nomor UNL dari dibuat sampai diterima (status Belum dipindahkan / Draft di Penerimaan Piutang / Sudah diterima, dipakai di no penerimaan + invoice, link jurnal UNL dan jurnal penerimaan). Card **Belum diterima** per company = saldo Penerimaan Belum Teridentifikasi untuk dicocokkan. Dicari dua arah: dari UNL ke bank / rekening koran dan dari bank / nominal / invoice / no penerimaan ke UNL; nomor UNL tampil (dan bisa diklik) di Status Alokasi dan di Penerimaan Piutang; kotak cari Alokasi membaca nomor UNL.
