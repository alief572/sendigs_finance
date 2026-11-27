# Test case: Pengembalian Kasbon (rekening koran → pilih Expense Report)

Dijalankan di mockup `index.html`. Klik **Reset data** dulu.

Pengembalian kasbon terjadi saat Expense Report dari kasbon punya **lebih kasbon** (realisasi lebih kecil dari kasbon). Karyawan mentransfer sisanya ke rekening perusahaan dan mengunggah bukti transfer (status Menunggu Verifikasi). Finance mengunggah rekening koran dan mengalokasikan baris uang masuk itu sebagai **Pengembalian Kasbon**.

Polanya sama dengan Penerimaan Piutang: baris rekening koran itu menjadi **draft** di menu **Expense → Verifikasi Pengembalian Kasbon**, tab Mutasi Bank. Finance klik **Proses Verifikasi** lalu **memilih sendiri Expense Report** yang dibayar oleh uang itu. Sistem tidak memasangkan otomatis. Perbandingan bank, tanggal, dan nominal dengan bukti transfer karyawan hanya membantu Finance mengisi 3 checklist. Ketiga checklist dicentang manual oleh Finance, tidak ada yang otomatis. Jurnal dibuat satu kali saat disetujui: **Dr Bank / Cr 1103-01-14 Piutang Lain-lain Konsultan**. Tampungan Pengembalian Kasbon di menu Penerimaan hanya menampilkan status berjalan.

Menu Verifikasi punya 3 tab:

| Tab | Isi |
|---|---|
| Mutasi Bank Pengembalian Kasbon | Baris rekening koran beralokasi Pengembalian Kasbon yang belum diverifikasi (draft), tombol Proses Verifikasi |
| Pengajuan Karyawan | Expense Report yang buktinya sudah diunggah (Menunggu Verifikasi) atau Ditolak, tombol Tolak |
| Selesai | Expense Report yang sudah diverifikasi, beserta baris rekening koran dan nomor jurnalnya |

## Alur utama

| No | Menu | Langkah | Hasil yang diharapkan |
|---|---|---|---|
| PK-01 | Penerimaan → Pengembalian Kasbon | Buka halaman | Baris "SETORAN TUNAI BUDI SANTOSO" 250.000 berstatus **Belum diverifikasi**; tidak ada tombol proses |
| PK-02 | Expense → Verifikasi Pengembalian Kasbon | Buka tab Mutasi Bank | 1 draft: baris Budi. Baris Zuhdi (12-Nov) tidak muncul karena belum dialokasikan. Tab Pengajuan Karyawan berisi 3 expense |
| PK-03 | (lanjutan) | Baris Budi → **Proses Verifikasi** | Tampil data mutasi bank (tanggal, bank, keterangan, nominal) dan pilihan **Expense Report** (wajib) tanpa tanda cocok. Ketiga checklist belum tercentang |
| PK-04 | (lanjutan) | Pilih **ER-2026-00287** (salah) | Perbandingan menandai **✖ beda** (tanggal, nominal, lebih kasbon). Finance tidak mencentang checklist |
| PK-05 | (lanjutan) | Ganti ke **ER-2026-00265** (Budi) | Data pengajuan dan bukti transfer tampil; perbandingan semua **✔ sama**. Preview Jurnal 2: Dr 1101-02-09 Bank BCA STM 250.000 / Cr 1103-01-14 Piutang Lain-lain Konsultan 250.000 |
| PK-06 | (lanjutan) | Finance mencentang sendiri **Bukti Transfer Valid**, **Mutasi Rekening Bank Cocok**, dan **Nominal Sesuai** → **Setujui & Buat Jurnal** | Jurnal **00001-BUM-XI-26** masuk **Jurnal All · tab Expense Report** (belum posting; + Post → Posting); ER-2026-00265 pindah ke tab **Selesai** |
| PK-07 | Penerimaan → Pengembalian Kasbon | Buka halaman | Baris Budi berstatus **Terverifikasi · 00001-BUM-XI-26** |
| PK-08 | Penerimaan → Alokasi | Baris "ZUHDI RAHMAN PENGEMBALIAN KASBON" 1.250.000 → Alokasi → **Pengembalian Kasbon** | Baris Zuhdi muncul di tab Mutasi Bank, siap diverifikasi |

## Uji negatif

| No | Langkah | Hasil yang diharapkan |
|---|---|---|
| PKN-01 | Setujui tanpa memilih Expense Report | Ditolak: pilih Expense Report |
| PKN-02 | Setujui dengan salah satu checklist (Bukti / Mutasi / Nominal) belum dicentang | Ditolak: ketiga checklist wajib |
| PKN-03 | Expense yang uangnya belum ada di rekening koran (ER-2026-00310, BCA Sustain) | Tidak bisa diverifikasi karena tidak ada baris di tab Mutasi Bank; Finance menunggu |
| PKN-04 | Tab Pengajuan Karyawan → Tolak tanpa alasan | Ditolak: alasan wajib |
| PKN-05 | Tolak ER-2026-00310 dengan alasan | Status **Ditolak**, laporan kembali ke pelapor |
| PKN-06 | Tampungan Pengembalian Kasbon: rollback baris yang sudah terverifikasi | Ditolak: sudah diproses |

Langkah-langkah di atas juga dijalankan otomatis oleh `smoke-test.js`.

## Catatan

- Kalau karyawan belum mengunggah bukti, expense-nya tidak ada di pilihan; Finance **menunggu**, supaya semua pihak disiplin.
- Bila bank, tanggal, atau nominal beda, perbandingan menandai **✖ beda**; keputusan mengikuti 3 checklist yang berlaku sekarang. Prosedur resmi untuk selisih masih perlu dipastikan dengan Finance.
- Nomor jurnal mengikuti sistem sekarang: `NNNNN-BUM-<bulan romawi>-YY`, bulan dari tanggal mutasi bank.
