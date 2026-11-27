# PPh Invoice (update alur PPh 23 sendigs)

Folder spesifikasi modul **PPh Invoice**: konsep dari SISCAL (`specs/input/PPh Invoice/PPh Invoice.html`) **diadopsi ke sendigs** untuk memperbarui alur PPh 23 yang sudah ada (Penerimaan Piutang + menu Penerimaan PPH 23). Tampilan, invoice, dan COA mengikuti sendigs (PPH-R-03). Konsep Titip Setor dari SISCAL **diganti** pengembalian PPh ke client lewat Request Payment (PPH-R-05).

| File | Isi |
|---|---|
| `index.html` | Mockup to-be: Ringkasan Perubahan, Penerimaan Piutang (pilihan PPh 23 jadi 3), menu baru **PPh Invoice** (tab PPh Dipotong / PPh Tidak Dipotong / Bukti Potong Diterima / History, Process to Request Payment, Input Bukti Potong, View + Print record, Detail riwayat), halaman bantu Jurnal |
| `request-payment.html` | Salinan mockup Request Payment terbaru (`specs/docs/request-payment`) + kategori **Pengembalian PPh**: dokumen **KPPH/…** dari Process to Request Payment masuk otomatis, lalu Atur Pembayaran → Approval → Record → Payment. Tersambung dengan `index.html` lewat localStorage (PPH-R-11). Dibuat ulang dari mockup Request Payment terbaru bila mockup itu berubah |
| `test-case-pph-invoice.md` | Test case to-be (PPH-01..) |
| `smoke-test.js` | Smoke test jsdom (65 pemeriksaan, termasuk alur lintas halaman PPh Invoice → Request Payment → PPh Invoice): `node specs/docs/pph-invoice/smoke-test.js` |
| `specs/input/PPh Invoice/` | Bahan dari user (`PPh Invoice.html`, mockup SISCAL) |

## Konsep yang diadopsi ke sendigs (to-be)

Kewajiban setor PPh ada di **client**. Akun PPh hanya dua (PPH-R-06): **PPh 23 Disetor 1106-01-02** dan **PPh Final Disetor 1106-01-05** (0,5%, UMKM). Bagian PPh dari piutang baru lunas saat **bukti potong** diterima. Kalau client tidak memotong (bayar penuh), PPh **dikembalikan ke client** lewat Request Payment berdasarkan surat permintaan client, lalu client mengirim bukti potong.

| Bagian | Sekarang (sendigs) | Usulan |
|---|---|---|
| Penerimaan Piutang: pilihan PPh | "PPh 23/PPh Final Disetor": Dipotong PPh / Tidak Dipotong PPh | **PPh 23: Dipotong Client / Tidak Dipotong Client / Tidak Ada PPh** |
| Jurnal penerimaan, Dipotong Client | Dr Bank + Dr PPh 23 Disetor 1106-01-02 / Cr Piutang 1102-01-01 penuh | Dr Bank / Cr Piutang (tagihan − PPh). PPh **tetap di piutang**, status Menunggu Bukti Potong |
| Jurnal penerimaan, Tidak Dipotong Client | Dr Bank / Cr Piutang penuh, PPh tidak dilacak | Dr Bank (tagihan penuh) / Cr Piutang (tagihan − PPh) / **Cr PPh 23 Disetor 1106-01-02** (UMKM: PPh Final Disetor 1106-01-05) sampai uangnya dikembalikan, status Belum Diproses |
| Tidak Ada PPh | sama dengan Tidak Dipotong sekarang | tidak dilacak (UMKM: PPh Final setor sendiri seperti sekarang) |
| Daftar invoice di Penerimaan Piutang | semua invoice bersaldo | invoice yang sisanya tinggal PPh menunggu bukti potong tidak muncul |
| Menu | Penerimaan PPH 23 (bukti setor, jurnal tanggal simpan) | **PPh Invoice** menggantikannya; data lama di tab History |
| **Process to Request Payment** (baru, tab PPh Tidak Dipotong) | – | Tanggal Request, Nominal Pengembalian (= PPh, readonly), Bank Tujuan, No. Rekening (angka), Nama Rekening, **Surat Permintaan dari Client** (wajib), Keterangan. Membuat dokumen pengembalian di Request Payment (nomor usulan `KPPH/yyyy/mm/nnnn`). **Preview jurnal** saja: Dr PPh 23 Disetor (UMKM: PPh Final Disetor) / Cr Bank pembayar (**bank perusahaan belum ditampilkan, dipilih saat Payment**, PPH-R-08); jurnal **terbentuk saat Payment** (tanggal Payment; biaya transfer mengikuti RP-R-36..39). Status Proses Request Payment |
| Setelah Payment | – | PPh **pindah ke tab PPh Dipotong** (label "Dikembalikan KPPH/…"), status Menunggu Bukti Potong |
| Input Bukti Potong | – | No Bukti Potong, Tanggal (= tanggal jurnal), NPWP/NITKU Pemotong (dari master customer), Nilai (harus = sistem), File PDF; **tanpa Masa Pajak** (PPH-R-09). Jurnal Dr PPh 23 Disetor 1106-01-02 (UMKM: PPh Final Disetor 1106-01-05) / Cr Piutang 1102-01-01. Nomor record `BPT/yyyy/mm/nnnn` |
| **Bukti Potong Diterima** (tab baru) | – | Record perusahaan atas bukti potong yang sudah diterima: No. Bukti Potong, Tanggal, Pemotong, NPWP/NITKU, No. Invoice, Company, Jenis (PPh 23 / PPh Final), Nilai PPh, total. Filter Company + Search. Action **View** dan **Print** (record: data bukti potong, invoice, dasar & tarif, penerimaan, pengembalian bila ada, file, jurnal). **Print juga mencetak file bukti potong yang diupload** (PDF/gambar) sebagai lampiran di halaman berikutnya setelah halaman record (PPH-R-10) |

Aturan di mockup:
- Tidak ada pilih customer di PPh Invoice; invoice yang diproses dengan Dipotong Client / Tidak Dipotong Client langsung masuk tab masing-masing. Status (+ umur sejak penerimaan, merah > 30 hari), Action lewat gear. Filter Company, Status (Belum Selesai / Selesai / Semua), Search.
- Bukti potong untuk PPh yang tidak dipotong baru bisa diinput **setelah uangnya dikembalikan** (Payment).
- Input bukti potong per invoice; satu nomor bukti potong boleh untuk beberapa invoice **customer yang sama**; customer lain ditolak. Nilai harus sama dengan PPh di sistem.
- Penerimaan sebelum **tanggal berlaku** sudah menjurnal PPh saat penerimaan: bukti potongnya dicatat tanpa jurnal (label "Sebelum berlaku").
- Langkah Atur Pembayaran / Approval / Payment pengembalian dijalankan di **Request Payment** (`request-payment.html`, menu Pengeluaran Non Pembelian di sidebar), lihat bagian "Sambungan ke Request Payment".
- Penyesuaian ke sendigs: COA sendigs (Bank per rekening, Piutang 1102-01-01, PPh 23 Disetor 1106-01-02, Biaya Administrasi Bank 7201-01-04), format invoice `INV nnn/STM/2026`, nomor penerimaan `nnnnn/PU/XI/26`, tabel jurnal format sendigs. Biaya Admin di Penerimaan Piutang tetap seperti sekarang (maks Rp 10.000).

## Sambungan ke Request Payment (PPH-R-11)

Konsep Request Payment → Payment (`../request-payment/README.md`) dipakai apa adanya; yang ditambah hanya kategori **Pengembalian PPh**.

| Langkah | Di mana | Yang terjadi |
|---|---|---|
| 1. Process to Request Payment | PPh Invoice, tab PPh Tidak Dipotong | Dokumen **KPPH/yyyy/mm/nnnn** dibuat: penerima = Nama Rekening client, rekening tujuan = Bank / No. Rekening / Nama Rekening yang diisi, lampiran = surat permintaan client, keperluan = Keterangan, nominal = PPh (tanpa PPN / PPh). Status PPh: Proses Request Payment, tahap "Di Request Payment" |
| 2. Request Payment | Request Payment | Dokumen muncul otomatis, kategori **Pengembalian PPh** (kartu dan filter Kategori). Centang → **Atur Pembayaran** (transfer, admin, rekening) → ajukan jadi PGJ. Tahap di PPh Invoice: "Approval PGJ-…" |
| 3. Approval | Approval Request Payment | Direktur menyetujui PGJ. Tahap di PPh Invoice: "Menunggu Payment (PGJ-…)" |
| 4. Payment | Payment List → Form Payment | **Bank pembayar dipilih di sini** (PPH-R-08), upload bukti transfer. Jurnal Payment mengikuti aturan Request Payment (RP-R-36..39): **Dr PPh 23 Disetor 1106-01-02** (PPh Final: PPh Final Disetor 1106-01-05) / Cr Bank TRF (+ biaya transfer bila ada). No Payment BK-… |
| 5. Kembali ke PPh Invoice | PPh Invoice | Tanggal bayar, No Payment, dan jurnal Payment tercatat; PPh pindah ke tab **PPh Dipotong** (Menunggu Bukti Potong, label "Dikembalikan KPPH/…") lalu Input Bukti Potong |

Di mockup kedua halaman tersambung lewat localStorage (kunci `sendigs_mock_pph23_v3` dan `sendigs-pph-rp-v1`); Reset data di PPh Invoice juga menghapus data Request Payment.

## Print record bukti potong (PPH-R-10)

Print di tab **Bukti Potong Diterima** menghasilkan satu dokumen cetak:
1. **Halaman record**: data bukti potong (No. Bukti Potong, Tanggal, Pemotong, NPWP/NITKU), invoice, penjualan dan tarif, nilai PPh, penerimaan, pengembalian ke client (bila ada), nama file, jurnal, "Dicetak <tanggal> · <user>".
2. **Lampiran: file bukti potong** yang diupload saat Input Bukti Potong, **ikut tercetak** mulai halaman baru setelah halaman record. PDF beberapa halaman dicetak semua halamannya; file gambar dicetak satu halaman.

Catatan untuk developer: pola sama dengan lampiran yang ikut tercetak di Print Dokumen Request Payment (lampiran di bawah form, lihat `../request-payment/README.md` bagian Konsep). Di mockup, file tidak bisa ditampilkan; diganti kotak penanda "Lampiran: File Bukti Potong" di halaman berikutnya.

## Belum diputuskan (diisi user)

1. **Kategori** dokumen pengembalian PPh di Request Payment dan **format nomornya** (mockup: usulan `KPPH/yyyy/mm/nnnn`).
2. Konfirmasi kode **PPh Final Disetor = 1106-01-05** (diambil dari menu lama Penerimaan PPH 23: invoice tipe 1 tarif 0,5% memakai 1106-01-05).
3. Tanggal berlaku (dikonfirmasi ke Finance & Accounting).

## Keputusan user (PPH-R-06, 27-Nov-2026)

"COA di PPh itu hanya PPh 23 Disetor dan yang 0,5% maka PPh Final Disetor". Diterapkan ke semua baris PPh di alur baru:

| Tahap | PPh 23 | PPh Final 0,5% (UMKM) |
|---|---|---|
| Penerimaan, Tidak Dipotong Client | Cr PPh 23 Disetor | Cr PPh Final Disetor |
| Payment pengembalian ke client | Dr PPh 23 Disetor / Cr Bank | Dr PPh Final Disetor / Cr Bank |
| Bukti potong diterima | Dr PPh 23 Disetor / Cr Piutang | Dr PPh Final Disetor / Cr Piutang |

Hasil akhir tidak dipotong: Cr lalu Dr saat pengembalian (saldo kembali 0), lalu Dr saat bukti potong, sama dengan Dipotong Client. Akun usulan "Hutang Pengembalian PPh ke Client" dihapus. Yang tidak diubah (perilaku sistem sekarang): jurnal pembanding "sekarang", dan UMKM + Tidak Ada PPh (setor sendiri: Beban PPh Final / Hutang PPh Final, COA di `Consulting_receipt` masih kosong).

## Keputusan user (PPH-R-05, 27-Nov-2026)

| No | Permintaan | Diterapkan |
|---|---|---|
| 1 | Kewajiban setor PPh ada di client; yang tidak dipotong diganti **Process to Request Payment** dengan lampiran surat request client, jurnal terbentuk saat Payment, di proses ini ada preview jurnal | Titip Setor (Setor Titipan, kode billing, NTPN) dihapus. Pilihan "Tidak Dipotong Client"; tab PPh Tidak Dipotong dengan aksi Process to Request Payment + preview jurnal |
| 2 | Setelah uang dikembalikan ke client baru masuk tab PPh Dipotong untuk upload bukti potong | Setelah Payment, status Menunggu Bukti Potong di tab PPh Dipotong |
| 3 | Satu tab khusus record perusahaan untuk PPh 23 yang sudah diterima bukti potongnya, bisa view dan print | Tab **Bukti Potong Diterima** dengan View dan Print |

## Keputusan user (PPH-R-04, 27-Nov-2026)

| No | Pertanyaan | Jawaban |
|---|---|---|
| 1 | COA Hutang PPh 23 Titipan | **COA menyusul** (sejak PPH-R-06 tidak ada akun titipan/pengembalian; akun PPh hanya PPh 23 Disetor dan PPh Final Disetor). Yang masih COA menyusul: Beban PPh Final, Hutang PPh Final, Penerimaan Belum Teridentifikasi; di kode `Consulting_receipt` COA PPh Final memang masih kosong) |
| 2 | Tanggal berlaku | Dikonfirmasi user ke **Finance & Accounting** dulu. Mockup memakai contoh 01-Nov-2026 |
| 3 | PPh Final UMKM 0,5% | **Ikut pola yang sama** bila dipotong / dititipkan customer: tetap di piutang sampai bukti potong; bukti potong Dr **PPh Final Disetor 1106-01-05** / Cr Piutang (PPH-R-06). UMKM + Tidak Ada PPh tetap seperti sekarang (setor sendiri: Dr Beban PPh Final / Cr Hutang PPh Final) |
| 4 | Penerimaan dari Unlocated | **Ikut pola yang sama**; baris kas Dr Penerimaan Belum Teridentifikasi menggantikan Dr Bank |
| 5 | Biaya admin ("kok ada admin?") | Bukan tambahan baru: kolom **Biaya Admin** (maks Rp 10.000, Dr 7201-01-04) sudah ada di Penerimaan Piutang sendigs (`Penerimaan_calculation::MAX_ADMIN_CENTS`, `consulting_process.php`). Tidak berubah, tidak terkait PPh |
| 6 | Data lama menu Penerimaan PPH 23 | Dibuat **tab History** di PPh Invoice (read-only: No. Invoice, Customer, Company, Project, Keterangan, Nilai PPh, Nilai Setor, Tanggal Setor, Bukti Setor). Menu lama dihapus dari sidebar |
| 7 | Hal yang masih dibahas dari SISCAL (bukti potong tidak terbit, selisih kecil, bukti potong sebelum uang masuk, pembatalan) | **Nanti dulu** |

## Sumber: konsep SISCAL dari input (`specs/input/PPh Invoice/PPh Invoice.html`, PPH-R-02)

Mockup dari user berjudul **"SISCAL | PPh 23 Invoice (Mockup)"** (style `side_menu.php (siscal)`, `deposit.css (php85)`; COA dan nomor invoice SISCAL, mis. `0115/STM/XI/26`). Isi konsepnya:

**Intinya.** Piutang tetap Penjualan + PPN; jurnal pembentukan invoice tidak berubah. Bagian **PPh 23 dari piutang baru dianggap lunas saat bukti potong diterima** (bukti potong = "alat bayar" client). Baris jurnal PPh 23 yang sekarang dibuat saat penerimaan **dipindah ke saat bukti potong diterima**.

**Tiga pilihan PPh 23 di Penerimaan Uang** (sekarang Ya / Tidak):

| Pilihan | Kapan | Uang masuk | Status PPh setelah penerimaan |
|---|---|---|---|
| Dipotong Client | client memotong dan menyetor sendiri | Tagihan − PPh 23 | Menunggu Bukti Potong |
| Titip Setor | client bayar penuh, menitipkan PPh 23 untuk kita setorkan | Tagihan penuh | Titipan Belum Disetor |
| Tidak Ada PPh | client bukan pemotong | Tagihan penuh | tidak dilacak |

**Jurnal** (contoh Penjualan 10 jt, PPN 1,1 jt, PPh 23 2% = 200 rb):

| Tahap | Dipotong Client | Titip Setor |
|---|---|---|
| Penerimaan | Dr Bank 10.900.000 / Cr Piutang 10.900.000 (sisa piutang 200.000) | Dr Bank 11.100.000 / Cr Piutang 10.900.000 / Cr Hutang Titipan PPh 23 200.000 |
| Setor titipan | – | Dr Hutang Titipan PPh 23 / Cr Bank (kode billing, NTPN) |
| Bukti potong diterima | Dr PPh 23 / Cr Piutang 200.000 | sama |
| Sekarang (pembanding) | Dr Bank + Dr PPh 23 / Cr Piutang langsung saat penerimaan | Dr Bank / Cr Piutang, titipan tidak tercatat |

**Menu baru PPh 23 Invoice**: tanpa pilih customer; invoice yang diterima dengan Dipotong Client / Titip Setor langsung masuk, tab **PPh 23 Dipotong** dan **PPh 23 Tidak Dipotong (Titip Setor)**; kolom No, No. Invoice, Customer, Penerimaan, Nominal PPh 23, Status (+ umur sejak penerimaan), Setoran (NTPN), Bukti Potong, Action; filter Status (Belum Selesai / Selesai / Semua) + Search; detail per invoice berisi riwayat dan jurnal per tahap.
- **Setor Titipan** (per invoice): Tanggal Setor, Bank Pembayar, Masa Pajak, Kode Billing (15 digit), NTPN (16 karakter, boleh dipakai beberapa invoice customer yang sama), Bukti Setor. Nomor `STP/yyyy/mm/nnnn`, jurnal `JUR-STP-…`, tanggal jurnal = tanggal setor.
- **Bukti Potong** (per invoice; titipan harus sudah disetor): No Bukti Potong (boleh untuk beberapa invoice customer yang sama), Tanggal (= tanggal jurnal), Masa Pajak, NPWP / NITKU Pemotong (dari master customer), Nilai PPh 23 (harus sama dengan sistem, selisih ditolak), File PDF. Nomor `BPT/yyyy/mm/nnnn`, jurnal `JUR-BPT-…`.
- Penerimaan Uang: invoice yang sisa piutangnya tinggal PPh 23 tidak muncul lagi (pelunasan lewat PPh 23 Invoice).
- Penerimaan sebelum **tanggal berlaku** (contoh 01-11-2026) sudah menjurnal PPh saat penerimaan, jadi input bukti potongnya administratif tanpa jurnal.
- Menu lama **Kontrol PPh23 Dipotong / Update PPh23 Tidak Dipotong** dimatikan setelah modul berjalan.
- COA di mockup SISCAL: PPh 23 `1050-40-20`, Piutang `1030-10-10`, Admin `9010-10-40`, Hutang Titipan PPh 23 `2010302` (asumsi, menunggu Accounting).
- Masih dibahas (dari mockup): bukti potong tidak pernah terbit (beban atau tagih ulang?), selisih kecil nilai bukti potong, bukti potong datang sebelum uang diterima, pembatalan setoran / bukti potong salah input.

## Kondisi sistem sendigs saat ini (dari kode)

### Di mana PPh invoice muncul sekarang

| Tempat | Yang terjadi | Sumber kode |
|---|---|---|
| **Invoicing** | Nilai PPh invoice disimpan di `tr_invoicing.pph_jurnal`. Invoice tipe `1` memakai tarif 0,5%, lainnya 2% (dipakai sebagai cadangan bila `pph_jurnal` kosong) | `penerimaan_pph_23` controller `add()` |
| **Penerimaan Piutang** (`penerimaan_uang`) | Saat proses penerimaan dipilih **PPh 23/PPh Final Disetor: Dipotong / Tidak Dipotong** (`tr_penerimaan_piutang.pph23_dipotong` Y/N). Untuk invoice konsultasi versi baru (snapshot v3/v4) PPh **langsung dijurnal di jurnal penerimaan** (Debit PPh 23 Disetor; UMKM: Beban PPh Final) | `Consulting_receipt`, `Invoice_consulting` |
| **Penerimaan PPH 23** (`penerimaan_pph_23`) | Menu terpisah untuk mencatat **bukti setor PPh 23** dari customer atas penerimaan yang PPh-nya dipotong, lalu membuat jurnal pengakuan PPh | modul `penerimaan_pph_23` |

### Alur menu Penerimaan PPH 23 (as-is)

1. **Daftar**: semua detail Penerimaan Piutang (`tr_penerimaan_piutang_detail`) yang header-nya `pph23_dipotong = 'Y'`. Kolom: No., No. Invoice, Customer, Company, Project, Keterangan Invoice, Nilai PPh, Status, Action. Filter: Company (multi), Tahun, Status (Lunas / Belum Lunas).
2. **Status**: *Lunas* bila sudah ada baris di `tr_penerimaan_pph_23` untuk detail itu (action = lihat bukti setor); *Belum Lunas* (action = Setor PPH 23).
3. **Form Setor PPH 23** (`views/add.php`): No. Invoice, Customer Name, Project Name, Keterangan Invoice, Nilai PPH (readonly), **Nilai Setor** (wajib, default = Nilai PPH), **Upload Bukti Setor** (wajib; tipe file bebas), preview **Jurnal Penerimaan PPH 23**, Back / Save.
4. **Save** (`save_penerimaan_pph_23`): insert `tr_penerimaan_pph_23` (id, id_detail_penerimaan, id_inv, customer, project, keterangan, nilai_pph, nilai_setor, upload_bukti_setor), update `tr_penerimaan_piutang_detail.sts_pph_23 = 1`, lalu buat jurnal.
5. **Jurnal** (`Penerimaan_pph_23_model::create_jurnal`): **Debit** `1106-01-02` (tipe invoice 1: `1106-01-05`) / **Kredit** `1102-01-01` Piutang Dagang Usaha sebesar **Nilai Setor**; tanggal jurnal = tanggal simpan; keterangan `Penerimaan Piutang PPh 23 - <COA> - <ID>`; jenis transaksi `Penerimaan Piutang PPH 23`.
6. **Penolakan**: penerimaan invoice konsultasi v3 / invoice snapshot ditolak ("PPh aktual sudah dicatat pada jurnal penerimaan; jurnal pengakuan kedua tidak diperbolehkan").

Nama akun dibaca dari `coa_master` (tidak ada di repo). Di mockup: 1106-01-02 = PPh 23 Disetor (dari fixture tes `penerimaan_uang`), 1106-01-05 = (nama dari master COA).

### Catatan dari kode

- Ada dua kode COA PPh 23 Disetor di fixture tes sistem (`1106-01-02` dan `1108-01-01`). Perlu dicek di master COA.
- Upload bukti setor tidak membatasi tipe file (`allowed_types = '*'`).
- Jurnal memakai tanggal simpan, bukan tanggal bukti setor / bukti potong.

## Riwayat

| Tanggal | ID | Isi |
|---|---|---|
| 27-Nov-2026 | PPH-R-01 | Folder input, docs, dan mockup dibuat. Mockup = as-is Penerimaan PPH 23; kebutuhan modul baru menunggu user |
| 27-Nov-2026 | PPH-R-02 | Konsep dari `PPh Invoice.html` (SISCAL) dipelajari dan diringkas di atas; target sistem dan pemetaan ke sendigs menunggu konfirmasi user |
| 27-Nov-2026 | PPH-R-03 | Konsep SISCAL diadopsi ke sendigs: mockup diganti jadi to-be (Penerimaan Piutang 3 pilihan PPh 23, menu PPh 23 Invoice, Setor Titipan, Bukti Potong), test case to-be, smoke 40 |
| 27-Nov-2026 | PPH-R-04 | Jawaban user: COA menyusul, tanggal berlaku ke F&A, UMKM dan Unlocated ikut pola, admin tetap (sudah ada di sendigs), tab History data lama; kode pajak contoh diperbaiki ke kode sendigs (WAPU, bukan Non PKP); smoke 47 |
| 27-Nov-2026 | PPH-R-05 | Titip Setor diganti pengembalian PPh ke client lewat Request Payment (surat permintaan client, preview jurnal, jurnal saat Payment); setelah Payment pindah ke tab PPh 23 Dipotong; tab baru Bukti Potong Diterima (view + print); smoke 53 |
| 27-Nov-2026 | PPH-R-06 | Akun PPh hanya PPh 23 Disetor 1106-01-02 dan PPh Final Disetor 1106-01-05: dipakai di penerimaan tidak dipotong, Payment pengembalian, dan bukti potong; akun Hutang Pengembalian dan Beban PPh Final (bukti potong) dihapus; smoke 54 |
| 27-Nov-2026 | PPH-R-07 | Label menu dan tab tanpa "23": menu **PPh Invoice**, tab **PPh Dipotong** / **PPh Tidak Dipotong**, pilihan Penerimaan Piutang "PPh"; nama akun PPh 23 Disetor dan jenis PPh per invoice tetap; smoke 55 |
| 27-Nov-2026 | PPH-R-08 | Preview jurnal pengembalian tidak menampilkan bank perusahaan (baris Cr "Bank pembayar (dipilih saat Payment)"); bank dipilih di Payment (Simulasi Payment wajib pilih Bank Pembayar); smoke 57 |
| 27-Nov-2026 | PPH-R-09 | Masa Pajak dihapus dari Input Bukti Potong, tab Bukti Potong Diterima, View/Print, dan Detail; smoke 58 |
| 27-Nov-2026 | PPH-R-10 | Print di Bukti Potong Diterima ikut mencetak file bukti potong (lampiran di halaman berikutnya); mockup memakai penanda; dicatat di bagian "Print record bukti potong"; smoke 59 |
| 27-Nov-2026 | PPH-R-11 | Konsep Request Payment → Payment disambungkan: `request-payment.html` (salinan mockup Request Payment terbaru + kategori Pengembalian PPh), KPPH masuk otomatis, tahap tampil di PPh Invoice, setelah Payment kembali ke tab PPh Dipotong; Simulasi Payment dihapus; smoke 65 |
