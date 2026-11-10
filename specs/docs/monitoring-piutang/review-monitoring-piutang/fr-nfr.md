# FR & NFR — Invoice sampai Monitoring Piutang

Satu dokumen untuk seluruh alur piutang, dari invoice sampai dashboard:

1. **Invoicing**: tagihan per TOP dari Actual Plan Tagih menjadi invoice (tab Konsultasi). Invoice yang sudah terbit dibuka di tab Invoiced.
2. **Penerimaan Uang**: uang masuk di bank dialokasikan ke invoice dan mengurangi sisa piutang.
3. **Follow Up Piutang** (menu baru): finance AR mengirim invoice, mencatat tanggal diterima, follow up customer, mencatat rencana bayar, lalu close.
4. **Monitoring Piutang**: umur piutang dihitung dari tanggal diterima customer dan dibagi menjadi Lancar, Potensi Macet, dan Macet; ditambah card Belum Diterima dan total keseluruhan untuk dicocokkan dengan buku besar. Untuk Potensi Macet dan Macet tersedia surat konfirmasi.
5. **Dashboard Plan Bayar** (menu baru): rencana bayar per bulan dibandingkan dengan realisasi penerimaan.

Terakhir diperbarui: **2026-11-05**. Semua baris sudah dicocokkan dengan kode per tanggal ini. Dokumen ini **menggantikan** `fr-nfr-invoice-sampai-monitoring-piutang.md` (digabung ke sini, INV-R-10) dan versi 2026-11-02 dokumen ini.

> Salinan untuk review dari `docs/monitoring-piutang/fr-nfr-monitoring-piutang.md`. Perubahan dibuat di file sumber lalu disalin ulang ke sini.

**Dokumen pendamping**
- Alur proses: [`flow-invoice-sampai-monitoring-piutang.drawio`](flow-process.drawio).
- ERD: [`erd-invoice-sampai-monitoring-piutang.drawio`](erd.drawio).
- Mockup: [`mockup-monitoring-piutang.html`](mockup/index.html) (Invoicing, Follow Up, Monitoring, Dashboard).
- Use case dan paket review: [`review-monitoring-piutang/`](README.md).
- Contoh hasil export Dashboard: [`contoh-export-dashboard-plan-bayar.xlsx`](contoh-export-dashboard-plan-bayar.xlsx).

**Sumber**
- Permintaan: `MP-R-*` dan `INV-R-*` di [`requests.md`](../../requests.md). Kolom *Sumber* di tabel memakai ID ini. Jawaban konfirmasi 5 Nov: INV-R-08 dan INV-R-09.
- Bahan klien:
  - `input/Monitoring Piutang/Invoice Consulting.html`
  - `input/Monitoring Piutang/Invoicing Tab Invoiced.html`
  - `input/Monitoring Piutang/2026 - Visual Control invoice Konsultan.xlsx`, sheet "Data Invoice"
  - `input/Monitoring Piutang/Jawaban pertanyaan monitoring piutang.xlsx`: "Qn" = nomor pertanyaan, "CT-n" = poin n di *Catatan Tambahan Request*, "E28" = catatan di sel E28 ("buat inputan tanggal kirim invoice jne/driver")
  - `input/Monitoring Piutang/Format Surat Konfirmasi.pdf`
- **K-4Nov** = keputusan 4 Nov 2026 yang tercatat di komentar kode. Dikonfirmasi user 5 Nov (INV-R-08, INV-R-09): PIC wajib, FU tambahan, tab 1–4 + label "Baru", kunci setelah plan bayar. Invoice internal: diputuskan nanti.
- Kode:
  - `application/modules/invoicing`
  - `application/modules/penerimaan_uang`
  - `application/modules/monitoring_piutang`: library `Piutang_aging` (semua aturan umur, FU, kunci, filter) dan `Surat_konfirmasi`, model, menu Monitoring Piutang, migrasi, unit test
  - `application/modules/follow_up_piutang`: controller `Follow_up_piutang` (menu Follow Up Piutang) dan `Plan_bayar` (menu Dashboard Plan Bayar)
  - `application/config/piutang.php`: tanggal go-live Follow Up
- Dokumen teknis invoice v3: `docs/invoicing-consulting-v3-2026-10-18.md` (root repo).

**Status**

| Status | Arti |
|---|---|
| **Ada** | Sudah ada di kode dan lulus test tanpa database (unit test + smoke test tampilan) |
| **Ada, belum diuji DB** | Kode ada, tapi belum pernah dijalankan terhadap database berisi data SENDIGS |
| **Belum** | Belum ada di kode |

Selain integration test Invoicing + Penerimaan di database uji terisolasi, belum ada yang dijalankan terhadap database sungguhan (catatan terbuka 4).

## Aktor

| Aktor | Peran | Hak akses |
|---|---|---|
| Finance (Billing) | Membuat invoice dari TOP, mencetak invoice dan kwitansi, mengekspor daftar invoice, close invoice yang sudah lunas di menu Invoicing. | `Invoicing.View/Add/Manage` |
| Admin | Mengatur mapping COA invoice per company. | `Invoicing.Configure` |
| Finance (Penerimaan) | Mengalokasikan uang masuk bank ke invoice. | `Penerimaan_Uang.*` |
| Finance AR | Mengisi kirim, terima, follow up, dan rencana bayar, lalu close invoice. | `Follow_Up_Piutang.View`, `Follow_Up_Piutang.Manage` |
| Finance / Manajemen | Memantau Monitoring Piutang dan Dashboard Plan Bayar, mencetak surat konfirmasi, mengunduh Excel. | `Monitoring_Piutang.View`, `Dashboard_Plan_Bayar.View`, `Follow_Up_Piutang.View` |
| Sistem | Menghitung tagihan, sisa piutang, umur, kategori, jadwal FU, status tahap, dan realisasi. | – |
| Customer | Menerima invoice, menjawab follow up, memberi rencana bayar, membayar. Di luar sistem. | – |

## Functional requirements

### A. Invoicing

| ID | Aktor | Requirement | Kriteria terima | Sumber | Status |
|---|---|---|---|---|---|
| INV-FR-01 | Finance (Billing) | Tab **Konsultasi** hanya berisi tagihan yang belum di-invoice. | Satu baris per TOP (`kons_tr_plan_tagih_detail`, `status_terakhir = 1`, `sts_invoice <> 1`).<br>**Kolom:** Dokumen ("Belum ada invoice" + No. SPK), Customer & Project, Company, Tim (Project Leader, Sales), Status *Uninvoiced*, Aksi **+ Buat Invoice**. Company Vuca diarahkan ke form invoice Vuca.<br>**Filter:** Tahun (tahun aktual plan tagih). Tidak ada filter status.<br>**Ekspor Excel:** hanya data uninvoiced untuk tahun terpilih. | INV-R-01, `Invoicing Tab Invoiced.html` | Ada, belum diuji DB |
| INV-FR-02 | Finance (Billing) | Tab **Invoiced** berisi invoice konsultasi yang sudah terbit. | **Kolom:** Dokumen (No. Invoice + No. SPK), **Tanggal Invoice** (dd/mm/yyyy), Customer & Project, Company, Tim, Status *Invoiced* (+ *Journaled* bila jurnal sudah diposting), Aksi (Lihat, Cetak invoice, Cetak kwitansi).<br>**Filter:** Tahun dan Bulan tanggal invoice, Company (`kons_tr_company`).<br>**Urutan:** tanggal invoice terbaru dulu.<br>**Ekspor Excel:** mengikuti ketiga filter dan memuat kolom Tanggal Invoice. | INV-R-01, `Invoicing Tab Invoiced.html` | Ada, belum diuji DB |
| INV-FR-03 | Finance (Billing) | Membuat invoice dari satu TOP. | Satu TOP menghasilkan tepat satu invoice. TOP yang sudah ber-invoice ditolak (unique `consulting_top_key`).<br>**Isian wajib:** tanggal invoice, nomor PO, nomor faktur, perlakuan PPN (PKP / WAPU / Kawasan Berikat), COA penjualan.<br>**Identitas** customer, alamat, dan SPK diambil dari SPK. Nominal TOP diambil dari actual plan tagih dan tidak bisa diubah di form. | `Invoice Consulting.html` | Ada |
| INV-FR-04 | Sistem | Menghitung tagihan sesuai perlakuan PPN. | Penjualan = nominal TOP; DPP = penjualan × 11/12; PPN = DPP × 12%; total tagihan = penjualan + PPN (untuk semua perlakuan). Pembulatan half-up per tahap, dalam sen.<br>**Piutang awal:** PKP = total tagihan; WAPU dan KB = penjualan.<br>**PPh** tidak dipotong saat invoice; dicatat saat penerimaan (PU-FR-02). | `Invoice Consulting.html` | Ada |
| INV-FR-05 | Sistem | Menyimpan invoice dan jurnal draf dalam satu transaksi. | Semua langkah berikut ikut commit bersama; bila satu gagal, semua dibatalkan:<br>• header `tr_invoicing` berisi nilai, `saldo_piutang` = piutang awal, dan snapshot (owner company, COA, TOP);<br>• jurnal `tr_jurnal` (`jenis_transaksi = 'Invoicing'`, `sts = 0`) sesuai perlakuan PPN dan seimbang;<br>• `sts_invoice = 1` di plan dan actual plan tagih;<br>• nomor invoice dari sequence per entitas per tahun.<br>Nomor invoice dibuat server; preview di form tidak memesan nomor. | `Invoice Consulting.html` | Ada |
| INV-FR-06 | Finance (Billing) | Edit invoice hanya selama belum ada transaksi lanjutan. | Edit ditolak bila invoice sudah punya penerimaan, jurnal invoice sudah diposting, atau form sudah basi (fingerprint berbeda). Nomor invoice tetap. | Implementasi v3 | Ada |
| INV-FR-07 | Finance (Billing) | Cetak invoice dan kwitansi. | Cetakan memakai angka dan snapshot yang tersimpan, bukan hitung ulang. Kwitansi v3 dua desimal. | Implementasi v3 | Ada |
| INV-FR-08 | Admin | Mapping COA invoice per company. | Sembilan peran akun per company sumber dipetakan ke COA master, termasuk owner company jurnal. Mapping yang tidak lengkap, ambigu, atau memakai akun piutang/pajak yang sama ditolak. Hanya admin dengan `Invoicing.Configure`. Perubahan mapping tidak mengubah invoice yang sudah ada. | Implementasi v3 | Ada |
| INV-FR-09 | Finance (Billing) | Invoice Non Konsultasi dan Vuca tetap di jalurnya. | Tab Non Konsultasi dan form Vuca memakai formula lama dan tidak dikonversi. Invoice ini tetap masuk `tr_invoicing`, sehingga ikut ke Penerimaan, Follow Up, Monitoring, dan Dashboard. Company invoice non konsultasi dibaca dari `kons_tr_penawaran_non_konsultasi.id_company` (INV-R-09). | Kode, INV-R-09 | Ada, belum diuji DB |
| INV-FR-10 | Finance (Billing) | Close Invoice di menu Invoicing hanya bila lunas. | Close ditolak bila sisa piutang > 0 (harus 0 persis); alasan wajib. Dicek sebelum simpan dan di dalam UPDATE (aman dari penerimaan yang masuk bersamaan). Aturan yang sama dengan Close di Follow Up (MP-FR-11). | INV-R-09 | Ada, belum diuji DB |

### B. Penerimaan Uang (penghubung ke sisa piutang)

| ID | Aktor | Requirement | Kriteria terima | Sumber | Status |
|---|---|---|---|---|---|
| PU-FR-01 | Finance (Penerimaan) | Alokasi uang masuk bank ke invoice customer. | Sumber dana: `tr_alokasi_split` → `tr_alokasi_detail` (id lama langsung ke `tr_alokasi_detail`). Invoice yang bisa dipilih: `saldo_piutang > 0`. Total alokasi wajib sama dengan uang masuk (kontrol = 0) dan tidak melebihi sisa dana bank. | `penerimaan_uang/md/prd_penerimaan_uang.md` | Ada |
| PU-FR-02 | Sistem | Mengurangi sisa piutang per invoice. | Pengurang piutang disimpan di `tr_penerimaan_piutang_detail.consulting_reduction` dan mengurangi `tr_invoicing.saldo_piutang`:<br>• invoice konsultasi v3: uang diterima + biaya admin + PPh aktual yang diinput user;<br>• jalur lama: uang diterima + biaya admin − penyesuaian PPh.<br>Header dan detail penerimaan, jurnal draf `Penerimaan Piutang`, dan `nilai_terpakai` alokasi disimpan dalam satu transaksi. | Implementasi v3 | Ada |
| PU-FR-03 | Finance (Penerimaan) | Rollback penerimaan. | Hanya bila jurnal penerimaan masih draf dan dimulai dari cicilan terbaru. Sisa piutang dan alokasi kembali persis ke nilai sebelumnya. | `prd_penerimaan_uang.md` | Ada |

### C. Follow Up Piutang

| ID | Aktor | Requirement | Kriteria terima | Sumber | Status |
|---|---|---|---|---|---|
| MP-FR-08 | Finance AR | Menu kerja finance AR berisi semua invoice yang belum close, dengan tab sesuai urutan kerja. | **Isi daftar.** Semua invoice di `tr_invoicing` yang belum close, termasuk yang belum dikirim dan invoice **non konsultasi**. **Invoice lama yang sudah lunas dan belum pernah diproses (belum ada tanggal kirim) tidak ditampilkan**; invoice lama = tanggal invoice sebelum **tanggal go-live** (`application/config/piutang.php`, `tanggal_mulai_follow_up`). Invoice mulai tanggal go-live selalu tampil sampai di-close (`Piutang_aging::ar_visible`).<br>**Tab** (dengan jumlah invoice, ikut filter): Semua; **1. Kirim Invoice**; **2. Konfirmasi Diterima**; **3. Follow Up Customer**; **4. Tunggu Bayar & Close**; **FU Terlambat** (FU berjalan yang jadwalnya sudah lewat dan belum ada plan bayar); **Invoice Close (Laporan)** per tahun close. Tiap tab punya petunjuk singkat.<br>**Kolom Status** memakai keadaan invoice: Belum dikirim / Menunggu diterima / Proses follow up / Menunggu pembayaran / Close.<br>**Filter di bawah tab:** Company, Customer, Cari (semua kolom yang tampil, termasuk tanggal dalam beberapa format dan nominal), Follow Up (belum ada FU / FU 1–4 berjalan / FU tambahan berjalan / selesai). Tab 4 menambah Bulan dan Tahun plan bayar; tab Invoice Close menambah Tahun close.<br>**Label "Baru".** Invoice yang pindah tahap dalam 3 hari terakhir (termasuk hari ini) tampil paling atas dengan label "Baru · dari …".<br>**Aksi:** Update dan Close. User tanpa hak Manage, invoice yang sudah close, dan invoice yang sudah ada plan bayar hanya bisa dilihat (Lihat). Daftar per halaman 100 invoice. | CT-1, CT-2, MP-R-05, MP-R-07, INV-R-08, INV-R-09 | Ada, belum diuji DB |
| MP-FR-09 | Finance AR | Pengiriman dan penerimaan, diisi berurutan. | **Pengiriman:** tanggal kirim, dikirim via (pilihan **JNE / Driver**), keterangan (label berubah: keterangan JNE / nama driver). Data lama dengan via di luar pilihan tetap tampil.<br>**No resi:** muncul setelah data pengiriman tersimpan (tab 2), boleh diisi belakangan, maks. 100 karakter.<br>**Diterima customer:** tanggal terima, keterangan (nama penerima, dll).<br>**Form bertahap.** Bagian Diterima Customer muncul setelah tanggal kirim diisi; Follow Up dan Plan Bayar muncul setelah tanggal terima diisi.<br>**Server menolak:** via di luar JNE / Driver; tanggal kirim tanpa via; tanggal terima tanpa tanggal kirim; tanggal terima sebelum tanggal kirim; FU, PIC, atau plan bayar sebelum ada tanggal terima. | CT-3, Q2, E28, MP-R-03, INV-R-08 | Ada |
| MP-FR-10 | Finance AR | Follow up berjalan satu per satu. | **PIC penerima follow up:** nama dan nomor WA / telepon (8–20 digit, boleh +, spasi, tanda -), satu per invoice, berlaku untuk semua FU. Wajib sebelum FU pertama ditandai dilakukan.<br>**Tampilan.** Hanya FU yang sedang berjalan yang bisa diisi: *Jadwal follow up*, *Tanggal dilakukan* (+ tombol "Hari ini"), *Keterangan / jawaban customer*. FU yang sudah tersimpan tampil sebagai daftar selesai (bisa dibuka / ditutup).<br>**Jadwal otomatis (boleh diubah / diundur), dihitung dari tanggal FU sebelumnya *dilakukan*:** FU1 = tanggal terima + 3 hari; FU2 = FU1 dilakukan + 7; FU3 = FU2 dilakukan + 14; FU4 = FU3 dilakukan + 14.<br>**Keterangan dan PIC wajib** sebelum tanggal dilakukan bisa diisi. Dicek di form dan di server.<br>**Tidak langsung tersimpan.** Mengisi tanggal dilakukan menandai FU "belum disimpan" dan menampilkan usulan jadwal FU berikutnya yang bisa diedit. FU berikutnya mulai berjalan setelah klik Simpan. Tanggal dilakukan boleh sebelum jadwalnya.<br>**FU tambahan (FU 5, 6, dst.)** boleh ditambah setelah FU 4 dilakukan, bila customer belum memberi rencana bayar. Jadwal = FU sebelumnya dilakukan + 14 hari. FU tambahan yang belum dilakukan boleh dihapus.<br>**Riwayat perubahan** per invoice (waktu, user, isi). | CT-3, CT-4, MP-R-03–06, MP-R-14, INV-R-08 | Ada |
| MP-FR-10a | Finance AR | Rencana bayar menghentikan follow up. | Plan tanggal bayar + keterangan diisi setelah tanggal terima; tanggalnya bebas, tanpa batasan (INV-R-09). Semua invoice wajib punya plan bayar sebelum close. Setelah disimpan: invoice pindah ke tab 4. Tunggu Bayar & Close, tidak ada FU berikutnya, tidak dihitung FU Terlambat, tombol Tambah FU hilang, dan **seluruh data AR invoice itu tidak bisa diubah lagi** (ditolak di server). Aksi yang tersisa hanya Close. Bila tanggal plan lewat dan customer belum bayar, data **tetap terkunci** (tidak ada plan baru / FU lagi); bulan itu tampil merah "Lewat" di Dashboard. | K-4Nov, INV-R-08, INV-R-09 | Ada |
| MP-FR-10b | Finance AR | **Step tersimpan terkunci saat step berikutnya disimpan.** | Form Update diisi step by step. Step yang sudah disimpan **masih bisa dikoreksi sampai step berikutnya disimpan**, lalu terkunci supaya data tidak berubah-ubah:<br>• **1. Pengiriman** (tanggal kirim, via, keterangan): terkunci setelah **tanggal terima** disimpan. Tanggal kirim yang tersimpan tidak bisa dikosongkan;<br>• **2. Diterima Customer** (tanggal terima, keterangan): terkunci setelah **FU 1** disimpan sebagai dilakukan. **No resi** boleh diisi belakangan; terkunci bila sudah terisi dan FU 1 sudah disimpan;<br>• **3. Follow Up**: FU n terkunci setelah **FU berikutnya** disimpan sebagai dilakukan; FU terakhir yang dilakukan masih bisa dikoreksi (tanggal dilakukan dan keterangan; tanggal tidak bisa dikosongkan). **PIC** bisa dikoreksi tapi tidak bisa dikosongkan;<br>• **4. Plan Bayar**: seluruh form terkunci (MP-FR-10a).<br>Di form, field yang terkunci dinonaktifkan dan diberi keterangan 🔒; yang masih bisa dikoreksi diberi keterangan ✎. Di server, isi form untuk step yang terkunci diganti isi database (`Piutang_aging::lock_saved`), jadi request yang dimanipulasi pun tidak mengubah data. | INV-R-04, INV-R-08 | Ada |
| MP-FR-11 | Finance AR | Close invoice dan laporannya. | **Syarat.** Close hanya bila **sudah ada plan bayar** dan **sudah lunas**: sisa piutang **0 persis**, tanpa toleransi; selisih kecil diselesaikan dulu di Penerimaan Uang. Invoice yang belum lunas tidak bisa di-close (aturan yang sama di menu Invoicing, INV-FR-10). Keterangan close wajib. Dicek di tombol, di server (`close_block_reason`), dan di dalam UPDATE (aman bila penerimaan masuk bersamaan).<br>**Kolom.** Sama dengan Close Invoice di menu Invoicing: `sts_close`, `close_by`, `close_date`, `close_reason` di `tr_invoicing`.<br>**Setelah close.** Invoice keluar dari Monitoring dan tampil di tab Invoice Close (Laporan) per tahun close. Close ganda (dua user bersamaan) ditolak.<br>**Write-off** piutang tak tertagih belum ada (catatan terbuka 1). | CT-5, Q13, INV-R-08, INV-R-09 | Ada |
| MP-FR-13 | Finance AR, Finance / Manajemen | Export Excel Follow Up Piutang. | **Isi** mengikuti tab dan filter yang aktif, termasuk bulan/tahun plan bayar dan tahun close.<br>**Kolom:** No, No Invoice, Tgl Invoice, Customer, Company, Project, Nilai Invoice, Sisa Piutang, Tgl Kirim, Dikirim Via, Ket Kirim, No Resi, Tgl Terima, Ket Terima, PIC Penerima FU, No WA / Telp PIC, Umur, Kategori, FU 1–4 (jadwal, dilakukan, keterangan), FU Tambahan (FU 5 dst.), Plan Bayar, Ket Plan Bayar, Status. Tab Close menambah Tgl Close dan Ket Close. Ada baris total.<br>**Penyaringan** dihitung ulang di server dengan `Piutang_aging::filter_ar_rows`, aturan yang sama dengan layar. Filter tidak valid ditolak. | MP-R-07 | Ada, belum diuji DB |

### D. Monitoring Piutang

| ID | Aktor | Requirement | Kriteria terima | Sumber | Status |
|---|---|---|---|---|---|
| MP-FR-01 | Finance / Manajemen | Card Total Piutang, Lancar, Potensi Macet, Macet, dan **Belum Diterima Customer**, dengan nominal sisa piutang. | Lancar + Potensi Macet + Macet = Total Piutang (sudah diterima). Tiap card umur menampilkan jumlah invoice, persentase dari total, dan umur invoice paling lama. Klik card umur menyaring list ke kategori itu; klik lagi untuk melepas.<br>**Card Belum Diterima Customer** (abu-abu, tidak menyaring list): sisa piutang invoice yang belum ada tanggal terima, dengan link ke Follow Up Piutang.<br>**Total keseluruhan sisa piutang** = Total Piutang + Belum Diterima, tampil di atas card, untuk dicocokkan dengan saldo piutang di buku besar. Semua angka ikut filter Company, Customer, dan Cari. | MP-R-01, INV-R-09 | Ada |
| MP-FR-02 | Finance / Manajemen | Keterangan umur di tiap card. | Lancar "Umur 0 - 60 hari", Potensi Macet "Umur 61 - 90 hari", Macet "Umur > 90 hari". Batas: umur ≤ 60 Lancar (termasuk 0–30 hari, dikonfirmasi); 61–90 Potensi Macet; > 90 Macet. Kategori murni dari umur, status manual di Excel tidak dipakai. | Q4, Q5, Q6, MP-R-14 | Ada |
| MP-FR-03 | Sistem | Umur dihitung dari **tanggal customer menerima invoice**. | Umur = hari ini − tanggal terima, dalam hari kalender. Tanggal terima di masa depan dihitung 0. Pembayaran sebagian tidak mereset umur. | Q1, Q8 | Ada |
| MP-FR-04 | Sistem | Yang dihitung sebagai piutang ber-umur. | Invoice masuk kategori umur bila `saldo_piutang > 0`, belum close, dan sudah ada tanggal terima. Invoice yang belum diterima customer tidak masuk kategori umur dan list umur, tapi dihitung di card **Belum Diterima Customer** dan Total keseluruhan (MP-FR-01). | Q3, INV-R-09 | Ada |
| MP-FR-05 | Sistem | Invoice internal antar-company. | **Belum dibedakan, diputuskan nanti**: invoice internal belum dibuat di menu Invoicing, jadi semua invoice diperlakukan sama di Monitoring, Follow Up, dan Dashboard. (Versi 2 Nov: dikecualikan dari card; dibatalkan.) | Q9, MP-R-14, INV-R-09 | Ada |
| MP-FR-06 | Finance / Manajemen | List piutang per invoice, rekap per customer, dan detail. | **List**: no dan tanggal invoice, tanggal terima, customer dan project, company, nilai invoice, terbayar, sisa, umur, kategori, kolom Follow Up. Kolom Follow Up berisi FU berikutnya (merah bila lewat jadwal) atau plan bayar. Urut umur terlama, dengan baris total.<br>**Rekap per Customer**: sisa piutang per kategori; klik customer menyaring list.<br>**Detail**: data pengiriman (termasuk no resi), PIC, FU (termasuk FU tambahan), plan bayar, riwayat penerimaan, dan link "Buka di Follow Up Piutang". | MP-R-01 | Ada, belum diuji DB |
| MP-FR-07 | Finance / Manajemen | Filter Company, Customer, dan Cari. | Cari mencakup semua kolom yang tampil (sama dengan Follow Up). Card, list, rekap, dan Excel mengikuti filter yang aktif. | MP-R-01, Q12 | Ada |
| MP-FR-12 | Finance / Manajemen | Download Excel, termasuk per card. | **Tombol Download Excel di setiap card umur** (Total, Lancar, Potensi Macet, Macet) mengunduh kategori card itu saja, tanpa mengubah pilihan card. Tombol di atas mengikuti kategori yang sedang dipilih. Keduanya mengikuti filter Company, Customer, dan Cari. Nama file menyebut kategori, mis. `Monitoring_Piutang_Macet_<tanggal>.xlsx`.<br>**Isi:** ringkasan per kategori (file semua kategori juga berisi baris **Belum diterima customer** dan **Total keseluruhan sisa piutang**), daftar invoice (termasuk tanggal terima, FU berikutnya, plan bayar), baris total. | Q14, MP-R-11, INV-R-09 | Ada, belum diuji DB |
| MP-FR-16 | Finance / Manajemen | Surat Konfirmasi Status Tagihan. | Tombol **Surat Konfirmasi** hanya untuk invoice **Potensi Macet** atau **Macet** yang belum close dan masih bersisa; selain itu ditolak di server. Format mengikuti `Format Surat Konfirmasi.pdf`: kop, nomor surat, isi tagihan, penandatangan. Profil per company di `Surat_konfirmasi::profiles()`; saat ini hanya **STM**. Company tanpa profil memakai kop teks nama company. | Q4/Q5 ("minta format surat"), `Format Surat Konfirmasi.pdf` | Ada, belum diuji DB (catatan terbuka 2) |

### E. Dashboard Plan Bayar

| ID | Aktor | Requirement | Kriteria terima | Sumber | Status |
|---|---|---|---|---|---|
| MP-FR-14 | Finance / Manajemen | Dashboard 12 bulan: plan bayar vs realisasi bayar, dengan rincian. | **Card.** Card **All** (total setahun, pilihan awal) + 12 card Januari–Desember untuk tahun yang dipilih. Tiap card berisi:<br>• **Plan**: total **nilai plan** invoice yang plan bayarnya jatuh di bulan itu, **termasuk yang sudah lunas / close**, dan jumlah invoice. Nilai plan = **sisa piutang saat plan bayar disimpan** (`plan_bayar_nominal`); plan lama diisi ulang dari riwayat (migrasi 017);<br>• **Realisasi**: total **semua** penerimaan dari modul Penerimaan Uang (PU-FR-02), termasuk invoice yang belum punya plan bayar dan yang sudah close, dihitung di bulan **tanggal transaksi bank**, dan jumlah penerimaan.<br>Penanda: "Bulan ini" = bulan berjalan; merah "Lewat" = bulan sudah lewat dan masih ada invoice yang belum close.<br>**Rincian plan bayar**: No, Plan Bayar (tanggal + keterangan), Invoice, Customer, Company, Nilai Invoice, **Nilai Plan**, Sisa Sekarang (+ label Lunas / Close), Umur.<br>**Rincian realisasi**: Invoice, Customer / Project, **Janji vs Bayar** (janji bayar = plan, tanggal bayar + no penerimaan, **Ketepatan**: Sesuai janji / Lebih cepat n hari / Telat n hari; "–" bila tanpa plan bayar), Jumlah Dibayar, **Posisi invoice sekarang** (sisa tagihan dari nilai invoice, status: Lunas & close / Lunas, belum close / Bayar sebagian).<br>Klik card bulan menampilkan rincian bulan itu; klik lagi atau klik All untuk semua bulan.<br>**Filter:** Tahun dan Company. | MP-R-08, MP-R-09, MP-R-14, INV-R-08, INV-R-09 | Ada, belum diuji DB |
| MP-FR-15 | Finance / Manajemen | Export Excel Dashboard Plan Bayar. | Mengikuti filter Tahun/Company dan card yang dipilih.<br>**All:** sheet *Ringkasan*, *Rincian*, *Realisasi*. Ringkasan berisi 12 bulan: jumlah invoice dan total nilai plan (rumus ke kolom Nilai Plan di Rincian), jumlah penerimaan dan total realisasi (rumus ke Realisasi), plus baris Total.<br>**Rincian:** No, Bulan, Tgl Plan Bayar, Ket Plan Bayar, No Invoice, Tgl Invoice, Customer, Project, Company, Nilai Invoice, Nilai Plan, Sisa Piutang Sekarang, Umur, Kategori, Status Invoice, plus Total.<br>**Realisasi:** No, Bulan Bayar, No Invoice, Tgl Invoice, Customer, Project, Company, Janji Bayar (Plan), Tanggal Bayar, Ketepatan, Jumlah Dibayar, No Penerimaan, Sisa Tagihan Sekarang, Nilai Invoice, Status Invoice, plus Total.<br>**Card bulan:** sheet Rincian dan Realisasi bulan itu saja.<br>Contoh hasil: `contoh-export-dashboard-plan-bayar.xlsx`. | MP-R-09, MP-R-14, INV-R-08 | Ada, belum diuji DB |

## Non-functional requirements

Kolom *Sumber*: ID permintaan bila diminta user. "Implementasi" berarti kualitas yang dipegang kode tapi belum diminta eksplisit oleh klien; boleh ditinjau ulang.

| ID | Berlaku untuk | Kebutuhan | Kriteria terukur | Sumber | Status |
|---|---|---|---|---|---|
| E2E-NFR-01 | Invoicing, Penerimaan | Transaksi utuh, tanpa simpan sebagian. | Invoice (header, jurnal, status TOP, sequence) dan penerimaan (header, detail, jurnal, saldo, alokasi) masing-masing satu transaksi InnoDB. Fault injection di setiap langkah tidak meninggalkan perubahan parsial. Simpan Follow Up (data AR, FU tambahan, log) juga satu transaksi. | Implementasi v3 | Ada (integration test DB terisolasi) |
| E2E-NFR-02 | Invoicing, Penerimaan, Close | Aman dipakai bersamaan. | Dua simpan bersamaan tidak menghasilkan nomor invoice ganda dan tidak membuat invoice ganda untuk satu TOP. Penulis invoice dan penerimaan memakai mutex yang sama (`sendigs_receipt_v2` → `sendigs_invoice_write`). Close dicek ulang di dalam UPDATE (lunas, belum close). | Implementasi v3 | Ada |
| E2E-NFR-03 | Semua tahap | Satu sumber kebenaran untuk sisa piutang. | `tr_invoicing.saldo_piutang` hanya diubah oleh Invoicing (nilai awal) dan Penerimaan (pengurang / rollback). Follow Up, Monitoring, dan Dashboard hanya membaca. 0 selisih antara sisa di Monitoring dan di Penerimaan untuk invoice yang sama. Total keseluruhan di Monitoring = jumlah `saldo_piutang` invoice belum close, untuk dicocokkan dengan buku besar. | Implementasi, INV-R-09 | Ada |
| E2E-NFR-04 | Invoicing, Penerimaan | Server tidak mempercayai angka dari browser. | Nominal, company, COA, dan jurnal dihitung ulang di server. Form basi ditolak lewat fingerprint. Hasil hitung PHP dan JS identik (parity test). | Implementasi v3 | Ada |
| MP-NFR-01 | Monitoring, Follow Up, Dashboard + Excel | Angka di layar sama dengan angka di Excel. | Kategori, umur, filter tab, pencarian, dan plan bayar per bulan dihitung oleh satu library (`Piutang_aging`) yang dipakai layar dan export. 0 selisih jumlah invoice / nominal antara layar dan file untuk filter yang sama. | MP-R-07, MP-R-09, MP-R-11 | Ada (diuji di mockup; di server belum dicoba) |
| MP-NFR-02 | Follow Up | Urutan, isian wajib, dan kunci tidak bisa dilewati. | Semua aturan MP-FR-09, MP-FR-10, MP-FR-10a, MP-FR-10b, dan MP-FR-11 dicek di server, bukan hanya di form. Data yang terkunci diambil dari database, bukan dari form. Data yang melanggar ditolak dengan pesan yang menyebut apa yang kurang. | MP-R-03, MP-R-05, INV-R-04, INV-R-08 | Ada (unit test) |
| MP-NFR-03 | Follow Up | Jejak perubahan lengkap. | 100% simpan dan close tercatat di `tr_invoice_ar_log` dengan user, waktu, dan isi form (JSON), termasuk FU tambahan. Jadwal FU yang diundur tetap terlacak walau nilainya ditimpa. | CT-4, Implementasi | Ada |
| E2E-NFR-05 | Semua menu | Akses sesuai hak. | Setiap halaman dan endpoint data / export / cetak memeriksa permission menunya: `Invoicing.View/Add/Manage/Configure`, `Penerimaan_Uang.*`, `Monitoring_Piutang.View`, `Follow_Up_Piutang.View/Manage` (simpan dan close butuh Manage), `Dashboard_Plan_Bayar.View`. Parameter filter divalidasi (angka / tanggal / pilihan tetap) dan query memakai parameter binding. | Implementasi (Q19 tidak dijawab) | Ada |
| E2E-NFR-06 | Database | Perubahan skema aditif dan bisa dibatalkan. | Invoice v3: migrasi `invoicing/migrations/003` (kolom nullable + 2 tabel master); rollback menolak berjalan bila sudah ada transaksi v3. AR: migrasi `monitoring_piutang/migrations/003–017`, hanya tabel baru (`tr_invoice_ar`, `tr_invoice_ar_log`, `tr_invoice_ar_fu`) dan kolom nullable di `tr_invoice_ar`; `tr_invoicing` tidak berubah strukturnya. Setiap migrasi punya rollback. | Aturan repo | Ada |
| MP-NFR-06 | Umur dan jadwal | Perhitungan tanggal konsisten. | Umur dan jadwal FU dihitung dalam hari kalender, tidak terpengaruh jam atau pergantian bulan / tahun / DST. "Hari ini" memakai zona waktu server `Asia/Bangkok`, sama seperti modul lain. | Implementasi | Ada (unit test) |
| MP-NFR-07 | Semua file Excel | File siap dipakai. | Nominal berformat ribuan; tanggal `dd-mm-yyyy`; no invoice disimpan sebagai teks; total memakai rumus (bukan angka mati); judul / nama file menyebut filter, kategori, atau bulan. File tanpa data tidak berisi rumus yang menjumlah dirinya sendiri. **Pengecualian:** Ekspor Excel di Invoicing masih format lama (tabel HTML `.xls`). | MP-R-09, MP-R-11 | Sebagian |
| MP-NFR-08 | Semua layar | Konsisten dengan tampilan SENDIGS. | Pola AdminLTE yang sama dengan menu lain (box, tab, modal, label warna). Warna kategori sama di semua menu: Lancar hijau, Potensi Macet oranye, Macet merah. | Implementasi | **Belum dicek** di server |
| MP-NFR-09 | Paket uji coba dan paket review | Bisa dibuka user tanpa instalasi dan tanpa internet. | Setelah diekstrak, `index.html` terbuka di Chrome/Edge. Semua menu tampil, dan semua tombol Download / Export Excel menghasilkan file `.xlsx` tanpa koneksi internet (library dan kop surat ada di folder `lib`). | MP-R-10, MP-R-12 | Ada |
| E2E-NFR-07 | Daftar | Tetap ringan saat data banyak. | Daftar Invoicing server-side per halaman. Follow Up 100 invoice per halaman. Waktu muat dengan jumlah invoice riil belum ditetapkan dan belum diukur. | Implementasi | **Belum diukur** |

## Aturan data (di kode)

**Kunci antar tahap**

| Tahap | Kunci | Tanda status |
|---|---|---|
| TOP belum di-invoice | `kons_tr_plan_tagih_detail.id` | `sts_invoice <> 1` |
| Invoice terbit | `tr_invoicing.id` (`id_detail_plan_tagih`, `consulting_top_key` unik) | `sts_invoice = 1`, `saldo_piutang` = piutang awal |
| Jurnal invoice | `tr_jurnal.no_transaksi = tr_invoicing.id`, `jenis_transaksi = 'Invoicing'` | `sts` 0 = draf, 1 = posting |
| Dikirim / diterima / FU / plan bayar | `tr_invoice_ar.id_invoice = tr_invoicing.id` | `Piutang_aging::ar_status` (di bawah) |
| Penerimaan | `tr_penerimaan_piutang_detail.id_inv = tr_invoicing.id` | `saldo_piutang` berkurang sebesar `consulting_reduction` |
| Close | `tr_invoicing.sts_close = '1'` | Keluar dari Monitoring; tetap dihitung di sisi plan Dashboard |

**Piutang ber-umur di Monitoring.** Invoice ada di `tr_invoicing`, `saldo_piutang > 0`, `sts_close` bukan `'1'`, dan sudah ada `tr_invoice_ar.tgl_terima`. Yang belum ada tanggal terima masuk card Belum Diterima.

**Nilai.** Sisa piutang = `saldo_piutang` (sudah tanpa PPh 23, sesuai Q10: "sekarang piutang exc pph 23"). Nilai invoice = `total_akhir_jurnal` (untuk invoice v3 = piutang awal). Terbayar = nilai − sisa.

**Company.** Konsultasi: `kons_tr_penawaran.company`, cadangan `kons_tr_spk_penawaran.id_company` (join sama dengan modul Invoicing). Non konsultasi: `kons_tr_penawaran_non_konsultasi.id_company` lewat `tr_invoicing.id_penawaran`. Dipakai di semua daftar, filter, dan total per company.

**Tahap invoice di Follow Up** (`Piutang_aging::ar_status`), dicek berurutan:
- `close` bila `sts_close = '1'`;
- `plan_bayar` (tab 4) bila plan bayar terisi;
- `diterima` (tab 3) bila tanggal terima terisi;
- `dikirim` (tab 2) bila tanggal kirim terisi;
- selain itu `belum_kirim` (tab 1).

Setiap kali tahap berubah, `status_from` dan `status_changed_date` di `tr_invoice_ar` diisi; dipakai label "Baru" (3 hari).

**Tahap FU** (`fu_stage`): `1`–`4` = FU itu sedang berjalan; `tambahan` = FU 5 dst. sedang berjalan; `selesai` = semua FU yang ada sudah dilakukan, atau sudah ada plan bayar; kosong = belum diterima.

**Daftar Follow Up** (`ar_visible`): invoice belum close tampil bila sisa piutang > 0, sudah ada tanggal kirim, atau tanggal invoice ≥ tanggal go-live (`application/config/piutang.php`; kosong = semua invoice lunas yang belum dikirim disembunyikan).

**Close** (`close_block_reason`, `Invoicing::save_close_invoice`): plan bayar terisi (Follow Up) dan sisa piutang 0 persis (Follow Up dan Invoicing); dicek ulang di dalam UPDATE.

**Kunci step** (`lock_saved`): lihat MP-FR-10b. Dipanggil di `Follow_up_piutang::save()` sebelum validasi; tolakan untuk invoice close dan invoice yang sudah ada plan bayar dilakukan lebih dulu di controller.

**Dashboard Plan Bayar** (`plan_bayar_monthly`, `realisasi_info`):
- *Plan*: semua invoice (termasuk yang sudah close) dengan `plan_bayar` di tahun terpilih, per bulan plan bayar. Nominal = `plan_nominal()` = `plan_bayar_nominal` (sisa piutang saat plan bayar pertama kali disimpan, diisi di `save_ar`), cadangan sisa piutang sekarang. Penanda "Lewat" hanya menghitung invoice yang belum close.
- *Realisasi* (`Monitoring_piutang_model::get_realisasi`): semua baris `tr_penerimaan_piutang_detail` di tahun terpilih, termasuk invoice yang belum punya plan bayar, per bulan tanggal penerimaan.
  - **Tanggal penerimaan** = tanggal transaksi bank dari alokasi penerimaan (`tr_penerimaan_piutang.id_alokasi` → `tr_alokasi_split` → `tr_alokasi_detail.tanggal_transaksi`, atau id lama langsung ke `tr_alokasi_detail`). Ini tanggal yang sama dengan tanggal jurnal di modul Penerimaan Uang. Cadangannya tanggal input penerimaan.
  - **Nominal** = pengurang piutang dari penerimaan itu (`consulting_reduction`, PU-FR-02). Untuk data lama yang kolom itu kosong: penerimaan + biaya admin.
  - **Ketepatan** = tanggal bayar − plan bayar (0 = Sesuai janji, negatif = Lebih cepat, positif = Telat; "–" bila tanpa plan). **Status** = close / lunas (sisa 0, belum close) / sebagian.

**Tabel Follow Up** (database ERP, sama dengan `tr_invoicing`):
- `tr_invoice_ar`: satu baris per invoice (PK `id_invoice` = `tr_invoicing.id`). Kolom:
  - pengiriman: `tgl_kirim`, `kirim_via`, `ket_kirim`, `no_resi`;
  - penerimaan: `tgl_terima`, `ket_terima`;
  - PIC: `fu_pic_nama`, `fu_pic_telp`;
  - follow up: `fu1..4_plan`, `fu1..4_aktual`, `fu1..4_ket`;
  - plan bayar: `plan_bayar`, `ket_plan_bayar`, `plan_bayar_nominal`;
  - perpindahan tahap: `status_from`, `status_changed_date`;
  - audit: `created_*`, `updated_*`.
- `tr_invoice_ar_fu`: FU tambahan (FU 5 dst.): `id_invoice`, `ke`, `plan`, `aktual`, `ket`, audit.
- `tr_invoice_ar_log`: riwayat setiap simpan / close, isi form disimpan sebagai JSON.

**Akses dan menu.** Menu diletakkan setelah "Report Piutang Per Invoice". Q19 dan Q20 tidak dijawab, jadi mengikuti usulan awal.

| Menu | Link | Permission | Diberikan ke |
|---|---|---|---|
| Monitoring Piutang | `monitoring_piutang` | `Monitoring_Piutang.View` | pemegang Report Piutang Per Invoice + admin (user 7) |
| Follow Up Piutang | `follow_up_piutang` | `Follow_Up_Piutang.View` | pemegang Monitoring Piutang + admin |
| | | `Follow_Up_Piutang.Manage` (simpan, close) | **admin saja**. User finance AR perlu diberi lewat menu permission. |
| Dashboard Plan Bayar | `follow_up_piutang/plan_bayar` | `Dashboard_Plan_Bayar.View` | pemegang Follow Up Piutang + admin |

## Mockup, paket uji coba, dan paket review

- **Mockup** (`mockup-monitoring-piutang.html`) berisi empat menu yang saling terhubung dengan data contoh: Invoicing (tab Konsultasi / Non Konsultasi / Invoiced), Follow Up Piutang, Monitoring Piutang, Dashboard Plan Bayar. Daftar invoice diambil dari sheet Data Invoice; data kirim, terima, FU, dan plan bayar adalah contoh. "Hari ini" di mockup = 2 November 2026. Di setiap halaman ada bagian "Aturan & skenario uji". Semua keputusan 5 Nov sudah ada di mockup. Tab Invoiced punya status Invoiced / Journaled / Close dan aksi Lihat, Cetak invoice, Cetak kwitansi, Close (hanya bila lunas, INV-FR-10).
- **Batasan mockup** (tidak dibuat, INV-R-11): menu Penerimaan Uang tidak ada (pembayaran = data contoh); setting tanggal go-live tidak ada (mockup berperilaku seperti setting kosong); tab Non Konsultasi di Invoicing hanya keterangan, tapi invoice non konsultasi tetap muncul di Follow Up, Monitoring, dan Dashboard; cetak invoice / kwitansi hanya pesan.
- **Export di mockup** mengunduh file `.xlsx` sungguhan yang dibuat di browser (SheetJS), dengan format sama seperti export server (MP-R-10).
- **Paket uji coba** (`uji-coba-monitoring-piutang/`, MP-R-12): `index.html` (salinan mockup yang memuat library dari folder `lib`), `lib/` (library Excel, lisensi, gambar kop surat), contoh export, dan `BACA DULU.txt`. Folder ini sudah diperbarui 5 Nov; **file `.zip`-nya masih versi lama** dan perlu dibuat ulang sebelum dibagikan.
- **Paket review** (`review-monitoring-piutang/`, INV-R-05): mockup, FR & NFR, use case, flow process, ERD. Isinya salinan; perubahan dibuat di file sumber lalu disalin ulang.

## Urutan pemasangan (database dev dulu)

1. Invoice v3: `application/modules/invoicing/migrations/003_consulting_v3.sql` (database ERP), bila belum.
2. Follow Up / Monitoring / Dashboard, semua di `application/modules/monitoring_piutang/migrations/`:
   1. `001_register_menu.sql`: menu Monitoring Piutang (database `menus` / `permissions`). Lewati bila sudah pernah dijalankan.
   2. `003_create_tr_invoice_ar.sql`: database ERP (sama dengan `tr_invoicing`).
   3. `005_register_menu_follow_up.sql`: menu Follow Up Piutang.
   4. `007_register_menu_plan_bayar.sql`: menu Dashboard Plan Bayar.
   5. `009_add_no_resi.sql`: kolom `no_resi`.
   6. `011_add_fu_pic.sql`: kolom `fu_pic_nama`, `fu_pic_telp`.
   7. `013_add_status_changed.sql`: kolom `status_from`, `status_changed_date`.
   8. `015_create_tr_invoice_ar_fu.sql`: tabel FU tambahan.
   9. `017_add_plan_bayar_nominal.sql`: kolom `plan_bayar_nominal` **dan pengisian ulang** nilai plan bayar yang sudah ada = sisa piutang sekarang + pengurang piutang dari penerimaan yang diinput setelah plan disimpan (waktu plan dari `tr_invoice_ar_log`, cadangan `status_changed_date` / `updated_date`). Aman dijalankan ulang.
3. **Isi tanggal go-live** di `application/config/piutang.php` (`tanggal_mulai_follow_up`, format YYYY-MM-DD) saat modul mulai dipakai.

Rollback Follow Up urut terbalik: `018`, `016`, `014`, `012`, `010`, `008`, `006`, `004`, lalu `002`. Rollback `004` dan `016` menghapus data follow up; backup dulu.

## Catatan / masih terbuka

1. **Piutang tak tertagih / write-off ditunda** (INV-R-09: "nanti dulu"). Invoice yang tidak akan lunas (bangkrut, sengketa) tidak bisa di-close dan tetap tampil sebagai Macet. Yang perlu diputuskan nanti: jurnal penghapusan (akun, draft jurnal, saldo jadi 0?) dan siapa yang berhak.
2. **Surat konfirmasi** baru punya profil STM. Format company lain belum diterima.
3. **Tanggal go-live belum diisi** (INV-R-09: "Tanggal go-live nanti"). Sampai `tanggal_mulai_follow_up` diisi, invoice baru yang langsung lunas sebelum dikirim ikut tersembunyi di Follow Up.
4. **Belum diuji di database sungguhan.** Di laptop ini tidak ada database (MySQL) berisi data SENDIGS dan PhpSpreadsheet-nya kurang dua komponen. Di server dev:
   - jalankan migrasi (lihat Urutan pemasangan), lalu cek hasil pengisian ulang nilai plan (migrasi 017);
   - buka tab Invoiced, Follow Up, Monitoring, dan Dashboard, cek angkanya dengan beberapa invoice nyata, termasuk invoice non konsultasi (company);
   - cocokkan Total keseluruhan di Monitoring dengan saldo piutang di buku besar;
   - coba semua Download / Export Excel sekali;
   - jalankan integration test Invoicing (ekspor setelah perubahan tab Invoiced) di database uji terisolasi.
5. **Invoice internal** antar-company: diputuskan nanti, saat fiturnya dibuat di Invoicing (MP-FR-05).
6. **Pertanyaan yang tidak dijawab**, dibiarkan seperti usulan awal: Q14 (fitur tambahan dipertahankan), Q15–16 (kolom / filter tambahan hanya yang diminta kemudian), Q17 (posisi hari ini saja), Q19–21 (akses, letak menu, notifikasi; belum ada notifikasi).

**Sudah diputuskan** (riwayat):
- MP-R-14: umur 0–30 hari = Lancar; jadwal FU dihitung dari tanggal FU sebelumnya dilakukan; realisasi bayar ditambahkan ke Dashboard; invoice lama yang belum ikut update invoice tidak berpengaruh. Format surat sudah diterima dan dibuat (MP-FR-16).
- INV-R-08: dikirim via JNE / Driver saja; close hanya bila lunas + ada plan bayar; kunci saat step berikutnya disimpan (termasuk FU); plan Dashboard tetap menghitung invoice close; plan bayar yang lewat tetap terkunci; PIC wajib dan FU tambahan dikonfirmasi.
- INV-R-09: invoice non konsultasi ikut (company diperbaiki); close di Invoicing juga wajib lunas; selisih harus 0 persis; card Belum Diterima + total keseluruhan; realisasi = semua penerimaan; tab 1–4 + label "Baru" dipertahankan; nilai plan lama diisi ulang dari riwayat; tanggal plan bayar bebas.

## Test

| Bagian | Perintah | Butuh DB |
|---|---|---|
| Invoicing UI | `node application/modules/invoicing/tests/Ui_list_client_test.js`, `php application/modules/invoicing/tests/Ui_render_test.php` | Tidak |
| Invoicing hitung | `php application/modules/invoicing/tests/Consulting_calculation_test.php`, `node application/modules/invoicing/tests/Consulting_calculation_test.js` | Tidak |
| Invoicing + Penerimaan integrasi | `php application/modules/invoicing/tests/Consulting_integration_test.php --isolated-local-db` | **Ya, hanya DB uji terisolasi** (lihat `docs/invoicing-consulting-v3-2026-10-18.md`) |
| Monitoring / Follow Up / Dashboard | `php application/modules/monitoring_piutang/tests/Piutang_aging_test.php`, `php application/modules/monitoring_piutang/tests/Surat_konfirmasi_test.php` | Tidak |

Cakupan test Monitoring / Follow Up / Dashboard: umur, kategori, partisi (belum diterima); jadwal FU, FU tambahan, PIC, validasi urutan / keterangan; kunci step (`lock_saved`), daftar Follow Up (`ar_visible`), syarat close (`close_block_reason`), nilai plan (`plan_nominal`); status, tahap FU, label "Baru"; filter tab / pencarian / Export; plan bayar per bulan dan realisasi (ketepatan, status); data surat konfirmasi.

Smoke test tampilan mockup (jsdom) dijalankan dari folder kerja sementara dan belum disimpan di repo.
