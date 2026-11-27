# Test case: Penerimaan Piutang (Proses lanjutan, AP-R-40)

Dijalankan di mockup `index.html`. Klik **Reset data** dulu supaya nomor penerimaan (`000NN/PU/XI/26`) dan nomor jurnal sama dengan hasil yang diharapkan.

Alur mengikuti sistem `penerimaan_uang`:

1. **Penerimaan Piutang** → tab **Penerimaan Piutang Invoice** (atau **Kwitansi Deposit**) → tombol **Proses** di baris draft.
Tampilan dan proses Penerimaan Piutang **sama dengan sistem**; yang berubah hanya **jurnal** (AP-R-42). Preview jurnal di form hanya tabel jurnal, tanpa kotak info.

2. Form pertama (`add.php`): Nama Customer, PPh 23/PPh Final Disetor (Dipotong / Tidak Dipotong), Deposit (Pakai Deposit tidak dimockup), Nominal Penerimaan Bank, daftar invoice terbuka → centang → **Process**.
3. Form kedua (`consulting_process.php`): per invoice PPh 23 (default 2% Penjualan, bisa diedit), Alokasi Penerimaan, Biaya Admin, Lebih Bayar → Sisa Piutang dan Kas Masuk. **Kontrol** harus 0 (Uang Masuk = total kas yang dialokasikan). Di bawahnya preview jurnal dengan template Jurnal All.
4. **Proses** → nomor penerimaan terbentuk, draft pindah ke tab **Report Penerimaan**, jurnal masuk **Jurnal All · Penerimaan** (Kategori Penerimaan Piutang, belum posting).

**Jurnal per invoice** (pola sistem `Invoice_consulting`):

| Baris | Debit | Kredit |
|---|---|---|
| Kas | **Penerimaan Belum Teridentifikasi** bila draft berasal dari Unlocated; **Bank** bila dari Alokasi biasa | |
| PPh 23 Disetor | PPh 23 dipotong customer | |
| Biaya Administrasi Bank (7201-01-04) | biaya admin | |
| Deposit Customer (2102-01-02) | | lebih bayar |
| Piutang Dagang Usaha (1102-01-01) | | saldo piutang sebelum penerimaan |
| Piutang Dagang Usaha (1102-01-01) | sisa piutang (bila belum lunas) | |

Keterangan baris: `Nama akun - No Invoice - No SPK` (+ nomor Unlocated / tanggal rekening koran di baris kas).

Belum diketahui: nomor COA **PPh 23 Disetor** (sistem punya dua kode: 1106-01-02 dan 1108-01-01), nomor COA **Penerimaan Belum Teridentifikasi** **(diisi user)**.

## Data invoice terbuka di mockup

| Invoice | Customer | Company | Penjualan | PPN | Saldo Piutang |
|---|---|---|---|---|---|
| INV 281/STM/2026 | PT ASAHI DENSO INDONESIA | STM | 11.000.000 | 1.210.000 | 12.210.000 |
| INV 283/STM/2026 | PT ASAHI DENSO INDONESIA | STM | 5.000.000 | 550.000 | 5.550.000 |
| INV 195/STM/2026 | PT MADA WIKRI TUNGGAL | STM | 4.000.000 | 440.000 | 4.440.000 |
| INV 058/SSC/2026 | PT INDO KORDSA TBK | SUSTAIN | 7.500.000 | 825.000 | 8.325.000 |
| INV 036/VSB/2026 | PT KERETA API INDONESIA | VUCA | 30.000.000 | 3.300.000 | 33.300.000 |
| INV 040/VSB/2026 | PT SURYA BANGUN FAJAR | VUCA | 5.500.000 | 605.000 | 6.105.000 |

## Case

| No | Langkah | Hasil yang diharapkan | Hasil |
|---|---|---|---|
| PP-01 | **Unlocated Penerimaan** → Proses **PT INDO KORDSA TBK** (8.325.000) → tab Sudah Diproses → centang → **Pindahkan ke Penerimaan Piutang Invoice** | Draft di Penerimaan Piutang tab Invoice, keterangan "Dari Unlocated UNL-…" | |
| PP-02 | Klik **Proses** di draft INDO KORDSA, pilih customer **PT INDO KORDSA TBK** | Form sama seperti sistem (customer dipilih sendiri, tanpa catatan tambahan) | |
| PP-03 | Klik **Process** tanpa memilih PPh 23 | Ditolak: pilih PPh 23/PPh Final Disetor | |
| PP-04 | Pilih **Tidak Dipotong PPh**, Process tanpa centang | Ditolak: centang minimal satu invoice | |
| PP-05 | Centang INV 058/SSC/2026 → Process | Alokasi 8.325.000, Sisa 0, **Kontrol 0.00**. Jurnal: Dr **Penerimaan Belum Teridentifikasi** 8.325.000 / Cr Piutang Dagang Usaha 8.325.000 | |
| PP-06 | Isi Biaya Admin `15000` | Pesan: biaya admin maksimal Rp 10.000 dan Kontrol belum 0; tombol Proses nonaktif | |
| PP-07 | Admin kembali `0`, Alokasi `8000000` | Kontrol 325.000 (uang masuk lebih besar dari kas yang dialokasikan), ditolak | |
| PP-08 | Alokasi kembali `8325000` → **Proses** | Tersimpan **00053/PU/XI/26**, pindah ke tab Report Penerimaan (Used + nomor penerimaan, seperti sistem). Saldo INV 058/SSC/2026 jadi 0 | |
| PP-09 | Unlocated → tab **Daftar UNL** → baris INDO KORDSA → link Jurnal Penerimaan | Jurnal 00053/PU/XI/26: Debit Penerimaan Belum Teridentifikasi / Kredit Piutang Dagang Usaha, tanggal 18-Nov-2026 (tanggal rekening koran) | |
| PP-10 | Unlocated **PT KERETA API INDONESIA** (30.000.000) → Pindahkan ke Invoice → Proses, **Dipotong PPh**, centang INV 036/VSB/2026 | PPh 23 default 600.000 (2% × 30 jt). Alokasi 30.600.000, Sisa Piutang 2.700.000, Kontrol 0. Jurnal: Dr Penerimaan Belum Teridentifikasi 30.000.000 + Dr PPh 23 Disetor 600.000 / Cr Piutang 33.300.000 / Dr Piutang (sisa) 2.700.000 | |
| PP-11 | Unlocated **PT SURYA BANGUN FAJAR** → Pindahkan ke Kwitansi Deposit (KD/2026/11/0007) → Penerimaan Piutang tab **Kwitansi Deposit** → Proses → centang KD/2026/11/0007 → Process | Alokasi 6.000.000 terkunci (kwitansi harus lunas penuh, tanpa PPh dan lebih bayar), Kontrol 0. Jurnal Dr Penerimaan Belum Teridentifikasi / Cr Piutang. Setelah Proses, kwitansi jadi LUNAS | |
| PP-12 | Kasus lebih bayar: Unlocated **SETORAN TUNAI** 2.000.000 → Pindahkan ke Invoice → Proses, customer PT MADA WIKRI TUNGGAL, Tidak Dipotong PPh, centang INV 195/STM/2026 | Alokasi 2.000.000, Sisa 2.440.000. Coba isi Lebih Bayar `100` → ditolak (lebih bayar hanya bila invoice lunas) | |
| PP-13 | **Jurnal All** → tab Penerimaan → Kategori **Penerimaan Piutang** | Semua penerimaan yang diproses tampil, belum posting. + Post → Detail jurnal → Posting | |
