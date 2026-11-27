# Test case: Unlocated Penerimaan (Proses jurnal → pindah ke Penerimaan Piutang)

Dijalankan di mockup `index.html`. Klik **Reset data** dulu supaya nomor Unlocated dan nomor jurnal sama dengan hasil yang diharapkan.

Unlocated Penerimaan punya dua tab (AP-R-35):

- **Belum Diproses**: tombol **Proses** menjurnal Debit Bank / Kredit **Penerimaan Belum Teridentifikasi** dan membentuk 1 nomor Unlocated baru. Rollback hanya bisa di tab ini.
- **Sudah Diproses**: tombol **Pindahkan ke Penerimaan Piutang Invoice** dan **Pindahkan ke Penerimaan Piutang Kwitansi Deposit**. Draft di Penerimaan Piutang membawa nomor Unlocated sebagai referensi; jurnal penerimaannya Debit Penerimaan Belum Teridentifikasi (bukan Bank).

Nomor COA Penerimaan Belum Teridentifikasi, format nomor Unlocated (mockup `UNL-YYMMNNNNN`), dan tipe jurnal (mockup BUM) **(diisi user)**.

## Data Unlocated di mockup

| Case | Tanggal | Bank (buku) | Keterangan rekening koran | Nominal Unlocated | Yang dicoba |
|---|---|---|---|---|---|
| U-A | 10-Nov | BCA STM (STM) | TRSF E-BANKING CR PT MADA WIKRI TUNGGAL INV 189/STM/2026 | 500.000 | Sisa transfer yang dipecah (15.000.000 Penerimaan Piutang + 500.000 Unlocated) |
| U-B | 11-Nov | OCBC Vuca (VUCA) | TRSF CR PT KERETA API INDONESIA | 30.000.000 | Transfer tanpa keterangan invoice |
| U-C | 18-Nov | BCA Sustain (SUSTAIN) | TRSF E-BANKING CR 1811 PT INDO KORDSA TBK | 8.325.000 | Customer diketahui, invoice belum diketahui |
| U-D | 18-Nov | BCA STM (STM) | SETORAN TUNAI 1811 TANPA KETERANGAN | 2.000.000 | Setoran tunai yang belum diketahui asalnya |
| U-E | 19-Nov | OCBC Vuca (VUCA) | TRF MASUK PT SURYA BANGUN FAJAR | 6.000.000 | Ternyata pembayaran kwitansi deposit KD/2026/11/0007 (6.000.000) |
| U-F | 19-Nov | BCA STM (STM) | TRSF E-BANKING CR 1911 PT HEINZ ABC INDONESIA INV 276/STM/2026 | 630.000 | Lebih bayar: 19.500.000 masuk, 18.870.000 untuk invoice, sisa 630.000 Unlocated |

## Alur utama

| No | Menu | Langkah | Hasil yang diharapkan |
|---|---|---|---|
| UNL-01 | Penerimaan → Unlocated Penerimaan | Buka menu | Tab **Belum Diproses** aktif, berisi 6 baris (U-A s.d. U-F). Tombol Pindahkan belum tampil; tombol Rollback ada |
| UNL-02 | (lanjutan) | Baris **U-B KERETA API** → **Proses** | Form Proses Unlocated: info rekening koran, No Unlocated "Automatic (UNL-…)", preview jurnal buku VUCA: Dr 1101-02-07 Bank OCBC Vuca 30.000.000 / Cr (diisi user) Penerimaan Belum Teridentifikasi 30.000.000 |
| UNL-03 | (lanjutan) | Simpan | Pesan "Unlocated **UNL-261100001** dibuat, jurnal **BUM/VUCA/2611/0001**". Baris hilang dari Belum Diproses; jumlah tab Sudah Diproses jadi 1 |
| UNL-04 | Tab **Sudah Diproses** | Lihat baris U-B | Kolom Proses: UNL-261100001, nomor jurnal, link **Lihat jurnal**. Tombol Pindahkan tampil, tombol Rollback tidak ada |
| UNL-05 | (lanjutan) | Klik **Lihat jurnal** | Jurnal Bank / Penerimaan Belum Teridentifikasi 30.000.000 |
| UNL-06 | Tab Belum Diproses | Proses **U-C INDO KORDSA** lalu **U-D SETORAN TUNAI** | U-C: jurnal buku SUSTAIN (Bank BCA Sustain), UNL-261100002. U-D: jurnal buku STM (Bank BCA STM), UNL-261100003 |
| UNL-07 | Tab Sudah Diproses | Centang U-C → **Pindahkan ke Penerimaan Piutang Invoice** | Konfirmasi menyebut **Referensi: UNL-261100002** dan jurnal penerimaan **Debit Penerimaan Belum Teridentifikasi** (bukan Bank) → Ya, pindahkan |
| UNL-08 | Penerimaan → Penerimaan Piutang | Tab Penerimaan Piutang Invoice | Draft INDO KORDSA 8.325.000 dengan keterangan "Dari Unlocated UNL-261100002 · jurnal penerimaan: Debit Penerimaan Belum Teridentifikasi (bukan Bank)" |
| UNL-09 | Unlocated → Belum Diproses | Proses **U-E SURYA BANGUN FAJAR** → tab Sudah Diproses → centang U-E → **Pindahkan ke Penerimaan Piutang Kwitansi Deposit** | Pilihan kwitansi menandai **KD/2026/11/0007 (cocok)**. Pilih → Ya, pindahkan |
| UNL-10 | Penerimaan Piutang | Tab Penerimaan Piutang Kwitansi Deposit | Draft SURYA BANGUN FAJAR, Kwitansi KD/2026/11/0007, dengan referensi UNL |
| UNL-11 | Unlocated → Belum Diproses | Proses **U-F HEINZ ABC** (630.000) | Jurnal buku STM 630.000. Baris lain transaksi yang sama (Penerimaan Piutang 18.870.000) tidak berubah |
| UNL-12 | Penerimaan → Alokasi | Cari baris yang sudah dipindah | Status alokasinya ikut berubah jadi Penerimaan Piutang / Deposit |

## Uji negatif

| No | Langkah | Hasil yang diharapkan |
|---|---|---|
| UNL-N1 | Tab Sudah Diproses, klik Pindahkan tanpa mencentang | Ditolak: "Silakan pilih minimal satu data untuk dipindahkan!" |
| UNL-N2 | Tab Sudah Diproses, centang 2 baris → Pindahkan ke Kwitansi Deposit → pilih satu kwitansi | Ditolak: kwitansi deposit hanya bisa dipilih untuk 1 data |
| UNL-N3 | Tab Belum Diproses, centang **U-A MADA WIKRI** → Rollback | Hanya Unlocated 500.000 yang dibatalkan; Penerimaan Piutang 15.000.000 di transaksi yang sama tetap |
| UNL-N4 | Unlocated yang sudah diproses | Tidak bisa di-rollback (tombol Rollback tidak ada di tab Sudah Diproses) |

## Daftar UNL (AP-R-41)

| No | Langkah | Hasil yang diharapkan | Hasil |
|---|---|---|---|
| UNL-R1 | Setelah beberapa Unlocated diproses / dipindahkan / diterima: Unlocated Penerimaan → tab **Daftar UNL** | Semua nomor UNL tampil (termasuk yang sudah dipindahkan / diterima), dengan status Belum dipindahkan / Draft di Penerimaan Piutang / Sudah diterima | |
| UNL-R2 | Lihat baris UNL yang sudah diterima | Kolom Dipakai di: no penerimaan (mis. 00053/PU/XI/26) + invoice / kwitansi + customer; link Jurnal Penerimaan | |
| UNL-R3 | Card **Belum diterima** | Total nominal UNL yang belum diterima, dipecah per company. Seharusnya sama dengan saldo akun Penerimaan Belum Teridentifikasi | |
| UNL-R4 | Cari no penerimaan, nama bank (mis. `OCBC`), nominal, atau no invoice | Baris UNL terkait muncul (cari dari bank / penerimaan ke UNL) | |
| UNL-R5 | Klik link Jurnal UNL | Jurnal Debit Bank / Kredit Penerimaan Belum Teridentifikasi dengan template Jurnal All | |
| UNL-R6 | Menu **Alokasi** → baris rekening koran yang di-Unlocated | Status Alokasi menampilkan nomor UNL; klik → Daftar UNL tersaring nomor itu (cari dari UNL / bank) | |
| UNL-R7 | Menu Alokasi → kotak Search ketik nomor UNL | Baris rekening koran asalnya muncul | |
