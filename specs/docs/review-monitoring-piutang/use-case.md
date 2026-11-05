# Use Case — Follow Up Piutang, Monitoring Piutang, Dashboard Plan Bayar

Per 5 November 2026. Isinya diturunkan dari [`fr-nfr.md`](fr-nfr.md) (kolom **FR**) dan sudah dicocokkan dengan kode. Tidak ada aturan baru di dokumen ini.

## Aktor

| Aktor | Peran | Hak akses |
|---|---|---|
| Finance AR | Mengirim invoice, mencatat diterima, follow up customer, mencatat rencana bayar, close invoice. | `Follow_Up_Piutang.View`, `Follow_Up_Piutang.Manage` |
| Finance / Manajemen | Memantau umur piutang dan rencana bayar, mencetak surat konfirmasi, mengunduh Excel. | `Monitoring_Piutang.View`, `Dashboard_Plan_Bayar.View`, `Follow_Up_Piutang.View` |
| Sistem | Menghitung umur, kategori, jadwal FU, status tahap, dan realisasi bayar. Bukan pemicu use case. | – |
| Customer | Menerima invoice, menjawab follow up, memberi rencana bayar, membayar. Di luar sistem. | – |

Hulu dan hilir di luar dokumen ini: invoice dibuat di menu **Invoicing**, dan pembayaran dicatat di menu **Penerimaan Uang** (mengurangi sisa piutang).

## Ringkasan

| ID | Use case | Aktor | Menu | FR |
|---|---|---|---|---|
| UC-FU-01 | Melihat daftar follow up per tab | Finance AR, Finance / Manajemen | Follow Up Piutang | MP-FR-08 |
| UC-FU-02 | Mencatat pengiriman invoice | Finance AR | Follow Up Piutang | MP-FR-09 |
| UC-FU-03 | Mengisi no resi | Finance AR | Follow Up Piutang | MP-FR-09 |
| UC-FU-04 | Konfirmasi invoice diterima customer | Finance AR | Follow Up Piutang | MP-FR-09 |
| UC-FU-05 | Mengisi PIC penerima follow up | Finance AR | Follow Up Piutang | MP-FR-10 |
| UC-FU-06 | Melakukan follow up (FU 1–4) | Finance AR | Follow Up Piutang | MP-FR-10 |
| UC-FU-07 | Menambah follow up tambahan (FU 5 dst.) | Finance AR | Follow Up Piutang | MP-FR-10 |
| UC-FU-08 | Mencatat rencana bayar customer | Finance AR | Follow Up Piutang | MP-FR-10a |
| UC-FU-09 | Close invoice | Finance AR | Follow Up Piutang | MP-FR-11 |
| UC-FU-10 | Melihat laporan invoice close | Finance AR, Finance / Manajemen | Follow Up Piutang | MP-FR-11 |
| UC-FU-11 | Export Excel follow up | Finance AR, Finance / Manajemen | Follow Up Piutang | MP-FR-13 |
| UC-FU-12 | Melihat riwayat perubahan | Finance AR, Finance / Manajemen | Follow Up Piutang | MP-FR-13 |
| UC-MP-01 | Melihat card umur piutang | Finance / Manajemen | Monitoring Piutang | MP-FR-01/02/03, MP-FR-04 |
| UC-MP-02 | Melihat list per invoice dan rekap per customer | Finance / Manajemen | Monitoring Piutang | MP-FR-06/07 |
| UC-MP-03 | Melihat detail invoice | Finance / Manajemen | Monitoring Piutang | MP-FR-06/07 |
| UC-MP-04 | Download Excel monitoring | Finance / Manajemen | Monitoring Piutang | MP-FR-12 |
| UC-MP-05 | Cetak surat konfirmasi status tagihan | Finance / Manajemen | Monitoring Piutang | MP-FR-16 |
| UC-PB-01 | Melihat plan vs realisasi bayar per bulan | Finance / Manajemen | Dashboard Plan Bayar | MP-FR-14 |
| UC-PB-02 | Melihat rincian satu bulan | Finance / Manajemen | Dashboard Plan Bayar | MP-FR-14 |
| UC-PB-03 | Export Excel dashboard | Finance / Manajemen | Dashboard Plan Bayar | MP-FR-15 |

## Aturan umum form Update (berlaku untuk UC-FU-02 s.d. UC-FU-08)

1. Form diisi **step by step**: 1. Pengiriman → 2. Diterima Customer → 3. Follow Up → 4. Plan Bayar. Step berikutnya baru muncul setelah step sebelumnya terisi.
2. **Step yang sudah disimpan masih bisa dikoreksi sampai step berikutnya disimpan, lalu terkunci** (MP-FR-10b, INV-R-04, INV-R-08): kirim terkunci saat tanggal terima disimpan; terima dan no resi saat FU 1 disimpan; FU n saat FU n+1 disimpan; PIC bisa dikoreksi sampai plan bayar. Server memakai isi database untuk step yang terkunci, bukan isi form.
3. Setelah rencana bayar disimpan, **seluruh form terkunci**, juga bila tanggal plan lewat dan customer belum bayar; aksi yang tersisa hanya Close (setelah lunas).
4. Setiap simpan tercatat di riwayat (user, waktu, isi).

## Follow Up Piutang

| ID | Use case | Aktor | Prasyarat | Alur utama | Alur alternatif / pengecualian | Hasil akhir | FR |
|---|---|---|---|---|---|---|---|
| UC-FU-01 | Melihat daftar follow up per tab | Finance AR, Finance / Manajemen | Punya hak `Follow_Up_Piutang.View`. | 1. Buka menu Follow Up Piutang.<br>2. Pilih tab: Semua, **1. Kirim Invoice**, **2. Konfirmasi Diterima**, **3. Follow Up Customer**, **4. Tunggu Bayar & Close**, **FU Terlambat**, **Invoice Close (Laporan)**. Tiap tab menampilkan jumlah invoice.<br>3. Saring dengan Company, Customer, Cari, dan Follow Up (belum / FU 1–4 / FU tambahan / selesai). Tab 4 menambah filter Bulan dan Tahun plan bayar. | Invoice yang pindah tahap dalam 3 hari terakhir tampil paling atas dengan label "Baru · dari …".<br>Termasuk invoice non konsultasi. Invoice lama (tanggal invoice sebelum tanggal go-live) yang sudah lunas dan belum pernah dikirim tidak ditampilkan.<br>Tanpa hak `Manage`, tombol Update dan Close tidak tampil; form hanya bisa dilihat. | Daftar invoice belum close sesuai tab dan filter. | MP-FR-08 |
| UC-FU-02 | Mencatat pengiriman invoice | Finance AR | Invoice sudah dibuat di Invoicing dan ada di tab 1. Kirim Invoice. Punya hak `Manage`. | 1. Klik **Update** pada invoice.<br>2. Isi tanggal kirim dan pilih dikirim via (JNE / Driver).<br>3. Isi keterangan (keterangan JNE atau nama driver).<br>4. Simpan. | Via belum dipilih → simpan ditolak. | Invoice pindah ke tab 2. Konfirmasi Diterima. Data kirim masih bisa dikoreksi sampai tanggal terima disimpan. | MP-FR-09 |
| UC-FU-03 | Mengisi no resi | Finance AR | Data pengiriman sudah tersimpan. No resi masih kosong. | 1. Klik **Update**.<br>2. Isi no resi (maks. 100 karakter).<br>3. Simpan. | No resi boleh dikosongkan dulu dan diisi belakangan. | No resi tersimpan; terkunci setelah FU 1 disimpan. | MP-FR-09 |
| UC-FU-04 | Konfirmasi invoice diterima customer | Finance AR | Invoice ada di tab 2. Konfirmasi Diterima. | 1. Klik **Update**.<br>2. Isi tanggal terima dan keterangan (nama penerima, dll).<br>3. Simpan. | Tanggal terima sebelum tanggal kirim → ditolak.<br>Tanggal terima tanpa tanggal kirim → ditolak. | Invoice pindah ke tab 3. Follow Up Customer dan mulai masuk Monitoring Piutang (umur dihitung dari tanggal terima). Jadwal FU 1 = terima + 3 hari. Data kirim terkunci; tanggal dan keterangan terima masih bisa dikoreksi sampai FU 1 disimpan. | MP-FR-09, MP-FR-01/02/03 |
| UC-FU-05 | Mengisi PIC penerima follow up | Finance AR | Tanggal terima sudah tersimpan. | 1. Klik **Update**.<br>2. Isi nama PIC dan nomor WA / telepon PIC.<br>3. Simpan (boleh bersamaan dengan UC-FU-06). | Nomor tidak 8–20 digit → ditolak.<br>PIC data lama yang baru terisi sebagian masih bisa dilengkapi. | PIC tersimpan dan berlaku untuk semua FU invoice itu. Bisa dikoreksi (tidak bisa dikosongkan) sampai plan bayar disimpan. | MP-FR-10 |
| UC-FU-06 | Melakukan follow up (FU 1–4) | Finance AR | Tanggal terima tersimpan. Belum ada rencana bayar. | 1. Klik **Update**; form menampilkan FU yang sedang berjalan dengan jadwal otomatis.<br>2. Hubungi customer.<br>3. Isi keterangan / jawaban customer, lalu tanggal dilakukan (atau tombol "Hari ini").<br>4. Periksa usulan jadwal FU berikutnya (boleh diubah).<br>5. Simpan. | Keterangan atau PIC kosong → tanggal dilakukan tidak bisa diisi.<br>Jadwal FU lewat tanpa dilakukan → invoice tampil di tab **FU Terlambat**.<br>Customer memberi rencana bayar → lanjut UC-FU-08. | FU tersimpan sebagai dilakukan; masih bisa dikoreksi sampai FU berikutnya disimpan, FU sebelumnya terkunci (tanggal dilakukan tidak bisa dikosongkan). Setelah FU 1 disimpan, data terima dan no resi terkunci. FU berikutnya dijadwalkan: FU 2 = FU 1 + 7 hari, FU 3 = FU 2 + 14 hari, FU 4 = FU 3 + 14 hari. | MP-FR-10 |
| UC-FU-07 | Menambah follow up tambahan (FU 5 dst.) | Finance AR | FU 4 sudah dilakukan. Belum ada rencana bayar. | 1. Klik **Update**.<br>2. Klik **Tambah FU 5** (atau FU berikutnya).<br>3. Jalankan seperti UC-FU-06. | FU tambahan yang belum dilakukan boleh dihapus. | FU tambahan dijadwalkan FU sebelumnya + 14 hari; kuncinya sama dengan FU 1–4. | MP-FR-10 |
| UC-FU-08 | Mencatat rencana bayar customer | Finance AR | Tanggal terima tersimpan. | 1. Klik **Update**.<br>2. Isi plan tanggal bayar dan keterangan.<br>3. Simpan. | Rencana bayar sebelum tanggal terima → ditolak. Tanggal plan sendiri bebas (boleh tanggal lampau).<br>Tanggal plan lewat dan customer belum bayar → data tetap terkunci; bulan itu tampil merah "Lewat" di Dashboard. | Invoice pindah ke tab 4. Tunggu Bayar & Close. Follow up berhenti, tidak dihitung FU Terlambat, seluruh data AR terkunci. Plan masuk Dashboard Plan Bayar dengan nilai = sisa piutang saat itu. | MP-FR-10a, MP-FR-14 |
| UC-FU-09 | Close invoice | Finance AR | Rencana bayar sudah ada, **sudah lunas** (sisa piutang 0), invoice belum close. | 1. Klik **Close** pada invoice di tab 4.<br>2. Isi keterangan close.<br>3. Konfirmasi. | Belum ada rencana bayar atau belum lunas (sisa harus 0 persis; selisih kecil diselesaikan dulu di Penerimaan Uang) → tombol Close nonaktif, dan ditolak di server. Aturan lunas yang sama berlaku di Close Invoice menu Invoicing.<br>Keterangan kosong → ditolak.<br>Piutang tak tertagih (write-off) belum didukung: invoice tetap Macet. | Invoice close (data close sama dengan Close Invoice di menu Invoicing), keluar dari Monitoring dan sisi plan Dashboard, masuk tab Invoice Close (Laporan). | MP-FR-11 |
| UC-FU-10 | Melihat laporan invoice close | Finance AR, Finance / Manajemen | – | 1. Buka tab **Invoice Close (Laporan)**.<br>2. Pilih tahun close. | – | Daftar invoice yang close di tahun itu beserta tanggal dan keterangan close. | MP-FR-11 |
| UC-FU-11 | Export Excel follow up | Finance AR, Finance / Manajemen | – | 1. Pilih tab dan filter.<br>2. Klik **Export Excel**. | Filter tidak valid → ditolak. | File .xlsx berisi baris yang sama dengan layar (aturan saring yang sama dipakai di server). | MP-FR-13, MP-NFR-01 |
| UC-FU-12 | Melihat riwayat perubahan | Finance AR, Finance / Manajemen | – | 1. Buka form invoice.<br>2. Klik **Riwayat perubahan**. | – | Daftar simpan dan close: waktu, user, isi yang berubah. | MP-FR-13, MP-NFR-03 |

## Monitoring Piutang

| ID | Use case | Aktor | Prasyarat | Alur utama | Alur alternatif / pengecualian | Hasil akhir | FR |
|---|---|---|---|---|---|---|---|
| UC-MP-01 | Melihat card umur piutang | Finance / Manajemen | Punya hak `Monitoring_Piutang.View`. | 1. Buka menu Monitoring Piutang.<br>2. Lihat card Total, **Lancar** (0–60 hari), **Potensi Macet** (61–90), **Macet** (> 90): nominal sisa, jumlah invoice, persentase, umur tertua; dan card **Belum Diterima Customer**.<br>3. Lihat **Total keseluruhan sisa piutang** (Total + Belum Diterima) untuk dicocokkan dengan buku besar.<br>4. Klik card umur untuk menyaring list. | Invoice yang belum diterima customer tidak masuk kategori umur; dihitung di card Belum Diterima (link ke Follow Up Piutang). | Posisi piutang per hari ini. Lancar + Potensi Macet + Macet = Total; Total + Belum Diterima = total keseluruhan. | MP-FR-01/02/03, MP-FR-04 |
| UC-MP-02 | Melihat list per invoice dan rekap per customer | Finance / Manajemen | – | 1. Tab **List Piutang per Invoice**: urut dari umur terlama; kolom Follow Up berisi FU berikutnya atau plan bayar.<br>2. Tab **Rekap per Customer**: total per customer; klik customer untuk melihat invoicenya.<br>3. Saring dengan Company, Customer, Cari. | – | Daftar piutang sesuai filter. | MP-FR-06/07 |
| UC-MP-03 | Melihat detail invoice | Finance / Manajemen | – | 1. Klik nomor invoice. | – | Detail pengiriman, follow up, plan bayar, dan riwayat penerimaan invoice itu. | MP-FR-06/07 |
| UC-MP-04 | Download Excel monitoring | Finance / Manajemen | – | 1. Klik **Download Excel** di atas (semua kategori) atau di card (satu kategori). | – | File .xlsx sesuai filter / kategori, total memakai rumus. | MP-FR-12, MP-NFR-07 |
| UC-MP-05 | Cetak surat konfirmasi status tagihan | Finance / Manajemen | Invoice Potensi Macet atau Macet, belum close, masih ada sisa. | 1. Klik **Surat Konfirmasi** pada invoice.<br>2. Cetak. | Invoice Lancar → tombol tidak tersedia.<br>Company tanpa profil surat → kop berupa teks nama company. | Surat sesuai `Format Surat Konfirmasi.pdf`. | MP-FR-16 |

## Dashboard Plan Bayar

| ID | Use case | Aktor | Prasyarat | Alur utama | Alur alternatif / pengecualian | Hasil akhir | FR |
|---|---|---|---|---|---|---|---|
| UC-PB-01 | Melihat plan vs realisasi bayar per bulan | Finance / Manajemen | Punya hak `Dashboard_Plan_Bayar.View`. | 1. Buka menu Dashboard Plan Bayar.<br>2. Pilih Tahun dan Company.<br>3. Lihat 12 card bulan + All: **plan** (nilai plan = sisa piutang saat plan bayar disimpan, untuk invoice yang plan bayarnya di bulan itu, termasuk yang sudah close) dan **realisasi** (semua penerimaan piutang, termasuk invoice tanpa plan bayar, di bulan transaksi bank). | – | Perbandingan rencana dan realisasi bayar per bulan. | MP-FR-14 |
| UC-PB-02 | Melihat rincian satu bulan | Finance / Manajemen | – | 1. Klik card bulan.<br>2. Lihat rincian plan dan rincian realisasi. | Klik card yang sama lagi → kembali ke All. | Realisasi per invoice dengan ketepatan (Sesuai janji / Lebih cepat n hari / Telat n hari) dan status (Lunas & close / Lunas, belum close / Bayar sebagian). | MP-FR-14 |
| UC-PB-03 | Export Excel dashboard | Finance / Manajemen | – | 1. Klik **Export Excel** (All) atau export di card bulan. | – | All: sheet Ringkasan, Rincian, Realisasi. Bulan: sheet Rincian dan Realisasi bulan itu. | MP-FR-15 |
