# Test case: Request Mutasi sampai alokasi mutasi

Dijalankan di mockup `index.html`. Klik **Reset data** dulu supaya nomor dokumen sama dengan hasil yang diharapkan. Kasus: memindahkan Rp15.000.000 dari **BCA SUSTAIN** (436.400.0300) ke **BCA STM** (436.300.5287) tanggal 18-11-2026.

Satu pemindahan dana muncul dua kali: uang **keluar** di rekening koran BCA SUSTAIN dan uang **masuk** di rekening koran BCA STM. Keduanya diproses dengan tombol yang sama, **Proses Mutasi**, dan memilih **Request Mutasi yang sama** (MTS). Baris yang diproses duluan membuat realisasi mutasi (MTR); baris pasangannya ditautkan ke realisasi itu. **Setiap sisi membuat jurnalnya sendiri** di buku company rekening korannya (usulan versi awal). Nomor MTR dibuat sistem, user tidak perlu memilihnya.

## Alur utama

| No | Menu | Langkah | Hasil yang diharapkan |
|---|---|---|---|
| TC-01 | Mutasi Bank → Request Mutasi | Klik **Tambah**. Tgl 18-11-2026, Dari **1101-02-05 - Bank BCA Sustain**, Ke **1101-02-09 - Bank BCA STM**, Keterangan "Pengembalian dana talangan ke STM", Nilai 15.000.000 → Simpan | Company terisi otomatis "SUSTAIN → STM (antar company)" (tidak ada Target Accounting). Request **MTS-261100007** berstatus Menunggu Approval |
| TC-02 | Mutasi Bank → Approval Mutasi | Klik **Approve** pada MTS-261100007 | Status di Request Mutasi menjadi **Approved** |
| TC-03 | Penerimaan → Alokasi | **Upload Rekening Koran**: Bank **BCA - 436.400.0300 - Bank Sustain**, file `rekening_koran_BCA_SUSTAIN_18112026.csv` → Preview → **Proses** | Baris "TRSF E-BANKING DB 1811 KE 4363005287 SENTRAL TEHNOLOGI", Total Credit 15.000.000, status **Open** |
| TC-04 | Penerimaan → Alokasi | Klik **Alokasi** pada baris itu → Jenis **Mutasi**, Nominal 15.000.000 → Simpan | Pilihan jenis hanya untuk uang keluar (Mutasi, Transaksi Bank, Pembayaran). Status alokasi **Mutasi**, baris masuk ke menu tampungan Mutasi |
| TC-05 | Penerimaan → Mutasi | Baris keluar → **Proses Mutasi** → No Request Mutasi **MTS-261100007 (cocok)** → Simpan | Form terisi: Dari/Ke (beserta company-nya) dari request, tanggal & nilai aktual dari rekening koran; keterangan "belum punya realisasi, akan dibuat" dan preview jurnal. Terbentuk realisasi **MTR-261100001** dan jurnal **BUK/SUSTAIN/2611/0001** di buku SUSTAIN: Dr Piutang ke STM 15.000.000, Cr 1101-02-05 Bank BCA Sustain 15.000.000. Request Mutasi berstatus **Terealisasi MTR-261100001** |
| TC-06 | Penerimaan → Alokasi | **Upload Rekening Koran**: Bank **BCA - 436.300.5287 - Bank STM**, file `rekening_koran_BCA_STM_18112026.csv` → Proses | Baris "TRSF E-BANKING CR 1811 DARI 4364000300 SENTRAL SUSTAINABILITY", Total Debit 15.000.000, status Open |
| TC-07 | Penerimaan → Alokasi | Alokasi baris itu → Jenis **Mutasi**, 15.000.000 → Simpan | Baris masuk ke menu tampungan Mutasi |
| TC-08 | Penerimaan → Mutasi | Baris masuk → **Proses Mutasi** → pilih request yang sama, **MTS-261100007 (cocok)** → Simpan | Keterangan "sudah punya realisasi MTR-261100001", ditautkan, dan jurnal **BUM/STM/2611/0001** di buku STM: Dr 1101-02-09 Bank BCA STM 15.000.000, Cr Hutang ke SUSTAIN 15.000.000. Di tampungan Mutasi, kedua baris (keluar dan masuk) menampilkan MTR-261100001 (dari MTS-261100007) dan jurnal per sisi; klik **Lihat jurnal** untuk melihat barisnya |
| TC-09 | Mutasi Bank → Request Mutasi | Buka daftar | MTS-261100007 berstatus **Terealisasi MTR-261100001**. Tidak ada menu / tab List Mutasi |

## Uji negatif

| No | Langkah | Hasil yang diharapkan |
|---|---|---|
| TN-01 | Form Request Mutasi disimpan kosong | Ditolak: semua field wajib |
| TN-02 | Dari dan Ke diisi bank yang sama | Ditolak: bank asal dan tujuan tidak boleh sama |
| TN-03 | Upload file rekening koran yang sama dua kali | Ditolak: file sudah pernah diupload |
| TN-04 | Di TC-05 pilih request yang bank asalnya bukan BCA Sustain (mis. MTS-261100005) | Ditolak: bank asal request beda dengan bank rekening koran |
| TN-05 | Di TC-08 pilih request yang bank tujuannya bukan BCA STM | Ditolak: bank tujuan request beda dengan bank rekening koran |
| TN-06 | Di tampungan Mutasi centang baris yang sudah diproses → Rollback | Ditolak: sudah diproses, batalkan dulu di modul Mutasi |

Urutan boleh terbalik: kalau rekening BCA STM diupload dan diproses duluan (TC-06–TC-08 sebelum TC-03–TC-05), baris masuk yang membuat realisasi MTR-261100001, lalu baris keluar di TC-05 hanya ditautkan. Hasil akhirnya sama.

Semua langkah di atas juga dijalankan otomatis oleh `smoke-test.js`.

## Catatan

- Jurnal per sisi adalah usulan versi awal: antar company memakai Piutang/Hutang antar company, satu company memakai Kas/Bank dalam Perjalanan. COA akun-akun itu masih "(belum ada)"; usulan fix jurnal menyusul.
- Tidak ada jurnal saat Request Mutasi dibuat atau di-approve.
- Form Request Mutasi di mockup disederhanakan dari `request_mutasi/views/form.php`; kurs dan terbilang tidak ditampilkan. Target Accounting dihapus: buku jurnal mengikuti rekening masing-masing sisi.
