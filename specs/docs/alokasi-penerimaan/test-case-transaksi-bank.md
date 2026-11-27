# Test case: Transaksi Bank dari rekening koran

Dijalankan di mockup `index.html`. Klik **Reset data** dulu supaya nomor dokumen sama dengan hasil yang diharapkan.

Transaksi Bank hanya dibuat dari baris rekening koran yang dialokasikan sebagai **Transaksi Bank** (bunga, pajak bunga, biaya admin, biaya transfer). Satu baris rekening koran = satu transaksi. Arah jurnal mengikuti rekening koran: **CR** (uang masuk) → Penerimaan (KM): Dr Bank / Cr Akun Lawan; **DB** (uang keluar) → Pengeluaran (KK): Dr Akun Lawan / Cr Bank. Akun lawan dipilih manual oleh user; preview jurnal mengikuti pilihan itu. Sistem dipakai mulai bersih (tanpa data awal), jadi tidak ada transaksi manual lama yang perlu ditautkan; semua transaksi dibuat baru dari rekening koran.


Transaksi Bank juga dipakai untuk **autodebet pembayaran** (mis. BPJS). Arah keluar/masuk mengikuti kolom rekening koran di sistem (uang masuk = Total Debit, uang keluar = Total Credit); user cukup memilih COA lawan. Di sistem pilihan COA lawan = seluruh COA master. Kalau tagihannya sudah tercatat sebagai hutang, pilih akun hutangnya supaya beban tidak dobel.

## Alur utama

| No | Menu | Langkah | Hasil yang diharapkan |
|---|---|---|---|
| TB-02 | Penerimaan → Transaksi Bank | Baris **BUNGA JASA GIRO** (BCA Sustain, masuk 85.412) → **Proses** → pilih Akun Lawan **Pendapatan Bunga Jasa Giro** | Jenis Penerimaan, Company SUSTAIN, Bank 1101-02-05 Bank BCA Sustain terisi otomatis; Akun Lawan awalnya kosong. Preview jurnal: Dr Bank BCA Sustain 85.412 / Cr Pendapatan Bunga 85.412; catatan "CR (uang masuk) → akun Bank Debit" |
| TB-03 | (lanjutan) | Simpan | Transaksi **KM-261100001**, kolom Proses terisi |
| TB-04 | Penerimaan → Transaksi Bank | Baris **PAJAK BUNGA** (BCA Sustain, keluar 17.082) → Proses → pilih Akun Lawan **Beban Pajak Bunga** | Preview: Dr Beban Pajak Bunga / Cr Bank BCA Sustain; catatan "uang keluar (kode DB di mutasi bank) ada di kolom Total Credit → akun Bank Kredit" → Simpan → **KK-261100001** |
| TB-05 | Penerimaan → Transaksi Bank | Baris **BIAYA ADM** 06-Nov (BCA STM, keluar 30.000) → Proses → pilih Akun Lawan **Admin Charge 7201-01-04** → Simpan | Preview: Dr Admin Charge / Cr Bank BCA STM. Terbentuk **KK-261100002** |
| TB-06 | Penerimaan → Alokasi | **Upload Rekening Koran**: rekening **OCBC NISP - 7788.0000.0417 - Bank Vuca**, file `rekening_koran_OCBC_VUCA_18112026.csv` → Proses | Dibaca format OCBC (kode 22). Baris baru: TRF MASUK PT KERETA API, ADMINISTRATION FEE 25.000, TRANSFER FEE SKN 5.000 |
| TB-07 | Penerimaan → Alokasi | **TRANSFER FEE SKN** → Alokasi → **Transaksi Bank** 5.000 | Masuk tampungan Transaksi Bank |
| TB-08 | Penerimaan → Transaksi Bank | TRANSFER FEE SKN → Proses → pilih Akun Lawan **Beban Biaya Transfer** | Company VUCA, jurnal di buku VUCA: Dr Beban Biaya Transfer / Cr Bank OCBC Vuca → Simpan → **KK-261100003** |
| TB-09 | Transaksi Bank → Data Transaksi Bank | Buka daftar | Kolom **Akun Lawan (COA Asal)** dan **Bank (COA Tujuan)** dengan keterangan Debit/Kredit; kolom Rekening Koran menunjukkan baris asalnya; catatan "Input manual ditutup" |
| TB-10 | Penerimaan → Transaksi Bank | Baris **AUTODEBET BPJS KETENAGAKERJAAN 11/2026** (BCA STM, keluar 1.850.000) → **Proses** → pilih Akun Lawan **2103-01-07 Hutang Asuransi BPJS Ketenagakerjaan** → Simpan | Jenis **Pengeluaran (uang keluar)** dengan label merah **UANG KELUAR · kolom Total Credit**. Jurnal buku STM: Dr 2103-01-07 Hutang Asuransi BPJS Ketenagakerjaan 1.850.000 / Cr 1101-02-09 Bank BCA STM 1.850.000. Tersimpan sebagai KK baru (KK-261100004 kalau TB-02 s.d. TB-08 sudah disimpan) |
| TB-11 | Transaksi Bank → Data Transaksi Bank | Buka daftar | Di bawah Kode Transaksi ada label **Uang Masuk** (hijau, KM-…) atau **Uang Keluar** (merah, KK-…) |

## Uji negatif

| No | Langkah | Hasil yang diharapkan |
|---|---|---|
| TBN-01 | Di form, kosongkan Akun Lawan lalu Simpan | Ditolak: pilih COA lawan |
| TBN-02 | Ganti Akun Lawan ke akun lain | Preview jurnal ikut berubah |
| TBN-03 | Tampungan Transaksi Bank: centang baris yang sudah diproses → Rollback | Ditolak: sudah diproses |
| TBN-04 | Alokasi baris uang keluar | Jenis yang muncul hanya Mutasi, Transaksi Bank, Pembayaran |

Langkah-langkah di atas juga dijalankan otomatis oleh `smoke-test.js`.

## Catatan

- Akun lawan bertanda * (pendapatan bunga, pajak bunga, biaya transfer) masih contoh; hanya Admin Charge 7201-01-04 dari sistem. Tidak ada isi otomatis dari kata kunci; user selalu memilih akun lawan.
- Dashboard report rekening koran (semua baris harus teralokasi) menyusul.
