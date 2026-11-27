# Test case: Dashboard Alokasi

Dijalankan di mockup `index.html`, menu **Penerimaan → Dashboard Alokasi**. Klik **Reset data** dulu.

Dashboard menunjukkan apa yang **belum diproses** dari rekening koran, supaya semua baris akhirnya tuntas. Ada 9 card, masing-masing berisi jumlah baris, total nominal, umur transaksi tertua (dihitung dari 18-11-2026), dan sebaran umur 0–7 / 8–30 / >30 hari. Card tanpa sisa berwarna hijau dengan tulisan **Tuntas**. **Klik card** untuk menampilkan rinciannya di tabel bawah (tidak pindah halaman); dari situ tombol **Buka menu** membawa ke menunya.

| Card | Dihitung sebagai belum diproses | Tombol Buka menu |
|---|---|---|
| Belum Dialokasikan | Baris Open, atau sisa yang belum dialokasikan dari baris Sebagian | Alokasi |
| Penerimaan Piutang Invoice | Draft jenis Penerimaan Piutang yang belum diproses | Penerimaan Piutang, tab Invoice |
| Penerimaan Piutang Kwitansi Deposit | Draft jenis Deposit yang belum diproses | Penerimaan Piutang, tab Kwitansi Deposit |
| Unlocated Penerimaan | Semua yang masih di Unlocated | Unlocated Penerimaan |
| Alokasi Kalibrasi | Semua yang ada di tampungan (modulnya tidak punya proses lanjutan) | Alokasi Kalibrasi |
| Pengembalian Kasbon | Belum diverifikasi | Expense → Verifikasi Pengembalian Kasbon |
| Mutasi | Belum Proses Mutasi | Mutasi |
| Transaksi Bank | Belum dibuat KK/KM | Transaksi Bank |
| Pembayaran | Semua (proses lanjutan belum dibahas) | Pembayaran |

Di bawah card ada tabel **Rincian Belum Diproses** (urut dari yang paling lama). Tanpa card terpilih, isinya semua jenis.

## Alur

| No | Langkah | Hasil yang diharapkan |
|---|---|---|
| DA-01 | Buka Dashboard Alokasi tanpa filter | 9 card; total belum diproses tertulis "(semua data)"; jumlah di card = jumlah baris rincian |
| DA-02 | **Export to Excel** tanpa filter | File `Dashboard_Alokasi_20261118.xlsx`, sheet **Ringkasan** (per card + TOTAL) dan **Rincian** (per baris). Kepala sheet: Periode Semua, Company Semua, Bank Semua. Semua data terekspor |
| DA-03 | Company = **STM** | Pilihan Bank tinggal BCA 436.300.5287; card dan rincian hanya STM; tertulis "(sesuai filter)" |
| DA-04 | Export to Excel dengan filter STM | Hanya data STM; kepala sheet Company = STM |
| DA-05 | Tanggal 18-11-2026 s.d. 18-11-2026 | Hanya transaksi tanggal 18-Nov; periode tercatat di Excel |
| DA-06 | Bank = OCBC NISP 7788.0000.0417 | Hanya data VUCA |
| DA-07 | **Reset** | Semua filter kosong, angka kembali ke semua data |
| DA-08 | Klik card **Mutasi** | Tetap di dashboard; card tersorot; Rincian berjudul "— Mutasi" dan hanya berisi baris Mutasi |
| DA-09 | Klik card Mutasi lagi, atau **Tampilkan semua jenis** | Sorotan hilang, Rincian kembali ke semua jenis |
| DA-10 | Pilih card **Belum Dialokasikan** → **Buka menu Alokasi** | Pindah ke menu Alokasi |
| DA-11 | Pilih card **Penerimaan Piutang Kwitansi Deposit** → **Buka menu Penerimaan Piutang** | Pindah ke Penerimaan Piutang, tab Kwitansi Deposit |
| DA-12 | Alokasikan satu baris Open, lalu kembali ke dashboard | Card Belum Dialokasikan berkurang, card jenis tujuannya bertambah |

Langkah DA-01 sampai DA-11 juga dijalankan otomatis oleh `smoke-test.js`.
