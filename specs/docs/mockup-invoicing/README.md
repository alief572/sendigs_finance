# Paket Invoicing + Penerimaan Piutang (UMKM Non-PKP): mockup, FR & NFR, ERD, serah terima

Satu paket mockup untuk perlakuan PPN **UMKM Non-PKP (Omzet ≤ Rp4,8 M, PPh Final 0,5%)**, dari invoice sampai penerimaan piutangnya.
Buka `index.html` langsung di browser; tidak butuh server atau backend (ikon Font Awesome dimuat dari cdnjs).
Kedua halaman saling terhubung lewat menu samping: **Finance › Invoicing** dan **Penerimaan › Penerimaan Piutang**.
FR & NFR: [`fr-nfr-umkm-non-pkp.md`](fr-nfr-umkm-non-pkp.md). ERD: [`erd-umkm-non-pkp.drawio`](erd-umkm-non-pkp.drawio). Pemasangan, test, dan UAT untuk IT: [`serah-terima-umkm-non-pkp.md`](serah-terima-umkm-non-pkp.md). Terpisah dari dokumen dan mockup Monitoring Piutang.

| File | Isi |
|---|---|
| `index.html` | **Invoicing**: Daftar invoice (tab Konsultasi, Non Konsultasi, **Invoiced**) + Buat/Detail invoice konsultasi dengan perlakuan PPN UMKM Non-PKP |
| `penerimaan-piutang.html` | **Penerimaan Piutang**: form Proses Penerimaan + popup alokasi & jurnal untuk invoice UMKM (PPh Final 0,5%) |
| `smoke-test.js` | Smoke test Invoicing (jsdom); di akhir ikut menjalankan `smoke-test-penerimaan.js` |
| `smoke-test-penerimaan.js` | Smoke test Penerimaan Piutang: 5 kasus Excel × dipotong/setor sendiri, batch campuran, validasi, Proses |
| `fr-nfr-umkm-non-pkp.md` | **FR & NFR** UMKM Non-PKP dan tab Invoiced (UMKM-FR-01 s.d. 07, UMKM-NFR-01 s.d. 05) |
| `erd-umkm-non-pkp.drawio` | **ERD** tabel yang dipakai invoice dan penerimaan UMKM (buka dengan draw.io / app.diagrams.net) |
| `serah-terima-umkm-non-pkp.md` | **Serah terima ke IT**: aturan, data, konfigurasi COA, file yang berubah, pemasangan, test, use case, UAT |

## Sumber

- **Invoicing**: tampilan mengikuti aplikasi saat ini sesuai video `specs/input/Invoicing/Invoice Eksisting.mp4`.
  CSS diambil dari `assets/css/invoicing.css`, struktur dari `application/modules/invoicing/views/index.php` dan `consulting_invoice.php`.
  Jurnal dari `specs/input/Invoicing/Prosedur Jurnal Invoicing.xlsx`, kolom O–P.
- **Penerimaan Piutang**: tampilan mengikuti `application/modules/penerimaan_uang/views/add.php` (form + modal), `views/consulting_process.php`
  (isi popup) dan `assets/css/mockup_ui.css`. Jurnal dari `specs/input/Invoicing/Prosedur Jurnal Invoicing & Penerimaan.xlsx`,
  sheet **"Jurnal Penerimaan"** (kolom I–J = PPh Final dipotong customer, K–L = PPh Final disetor sendiri).
  Logika hitung meniru `Penerimaan_calculation::tax_calc`, `Consulting_receipt::invoice_context` dan `Invoice_consulting::receipt_journal`.

## Yang berubah (garis putus-putus ungu; toggle "Tandai perubahan" di header)

### Invoicing

1. **Perlakuan PPN** punya opsi baru **UMKM Non-PKP**. PPN tidak ditagihkan: DPP tetap dihitung (11/12), PPN 0, Total tagihan = Piutang awal = penjualan (termasuk di cetakan invoice). Jurnal hanya
   **Piutang Dagang Usaha (D)** dan **COA Penjualan (K)**, tanpa baris PPN. Deskripsi berakhiran `- UMKM Non-PKP`.
2. **Tab baru "Invoiced"** berisi record invoice yang sudah terbit:
   - kolom Dokumen (no. invoice, tanggal, SPK, PO), Customer & Project (+ TOP), Company, Perlakuan PPN, Nilai (Penjualan, PPN, Total tagihan, Saldo piutang), Status, Aksi;
   - **card Total Invoiced** di paling atas (Total Invoiced + jumlah invoice, Penjualan, PPN, Saldo piutang) yang mengikuti filter dan pencarian (UMKM-FR-07);
   - filter Tahun, Bulan invoice, **Company** (STM, Vuca, Sustain; Vuca belum punya data contoh), Perlakuan PPN; pencarian;
   - **Ekspor Excel** mengunduh file `.xlsx` sungguhan berisi invoice yang tampil, dengan baris Total (rumus SUM). Dibuat di browser dengan SheetJS dari cdnjs, jadi butuh internet saat mockup dibuka;
   - Aksi: Lihat invoice (detail read-only beserta jurnal), Cetak invoice, Cetak kwitansi.
3. **Tab Konsultasi** hanya menampilkan TOP yang **belum ditagihkan** (filter Status tagihan dihapus).
   Setelah **Simpan invoice**, baris pindah ke tab Invoiced.

### Penerimaan Piutang

1. Invoice UMKM: DPP tetap dihitung, PPN 0. Batas PPh = **PPh Final 0,5% × penjualan**, bukan PPh 23 2%. Kolom PPh berjudul "PPH 23 / PPh Final".
2. Label pilihan potong/tidak potong diganti dari "PPH23 Dipotong" menjadi **"PPh 23/PPh Final Disetor"**. **Dipotong PPh**: PPh Final yang dipotong customer dijurnal ke **Beban PPh Final (D)**, bukan PPh 23 Disetor.
3. **Tidak Dipotong PPh** (setor sendiri): bank menerima penuh, jurnal ditambah **Beban PPh Final (D) / Hutang PPh Final (K)**.
   Keterangan "PPh Final 0,5% setor sendiri: X" tampil di baris invoice.
4. COA Beban/Hutang PPh Final **dikosongkan** (belum ada di master). Di bawah jurnal muncul catatan bahwa posting ke accounting ditolak sampai COA diisi.
5. Deskripsi jurnal berakhiran `- UMKM Non-PKP`. Invoice PKP di batch yang sama tetap memakai PPh 23 2% (kasus 6).

Data PT JTEKT di Invoicing diambil dari video; data lain dummy. Company data contoh: STM dan Sustain (Vuca belum ada contoh). Kedua halaman memakai data dummy masing-masing (invoice yang disimpan di
halaman Invoicing tidak otomatis muncul di Penerimaan Piutang). Nominal Penerimaan Bank di mockup bisa diubah; di aplikasi nilainya dari
Alokasi Penerimaan Bank.

## Pertanyaan terbuka

- Kawasan Berikat masih menampilkan DPP/PPN dan Total tagihan termasuk PPN (perilaku lama). Perlu ikut dibuat PPN 0 seperti UMKM?
- Cetakan invoice UMKM: baris PPN 0.00 ditampilkan atau disembunyikan?
- Label "PPh 23" di daftar dan detail Penerimaan (mis. "Potong PPh 23", "Total PPh 23") ikut diganti "PPh 23/PPh Final"?
- Card Total Invoiced + Ekspor Excel `.xlsx` (UMKM-FR-07) sudah sesuai? Bila ya, dibuat di aplikasi.
- Apakah invoice Vuca dan invoice lama juga ikut tampil di tab Invoiced?
- Nomor COA Beban PPh Final dan Hutang PPh Final.
- Kurang bayar dengan setor sendiri: Hutang PPh Final dicatat penuh (10.000) di penerimaan pertama, mengikuti Excel. Benar begitu, atau proporsional terhadap nilai yang diterima?

## Smoke test

```
node specs/docs/mockup-invoicing/smoke-test.js
```

Menjalankan kedua halaman. Butuh paket `jsdom` (dari `node_modules`, atau arahkan dengan env `JSDOM_PATH`).
