# Audit kesesuaian penerimaan_uang dengan konsep Consultant

Tanggal: 26 Oktober 2026. Status: audit selesai; implementasi belum sepenuhnya sesuai. Tidak ada perbaikan kode aplikasi atau perubahan database dalam audit ini.

## Acuan dan cakupan

- `C:/Users/Febry/Downloads/Penerimaan Piutang - Consultant.xlsx`: kelima sheet Requirements, Definisi, Business Process, Konsep, Test Case dibaca dari XML workbook, termasuk formula dan cached values.
- `C:/Users/Febry/Downloads/Penerimaan Piutang Consultant.html`: alur form, rumus, jurnal, cicilan, laporan, dan validasi JavaScript dibaca sebagai sumber konsep.
- Instruksi pengguna: deposit memang tidak dibuat. Seluruh pembuatan, pemakaian, saldo, dan laporan deposit dikeluarkan dari penilaian.
- Grounding endpoint aktif: `get_inv_by_cust`, `process_alokasi`, `save_penerimaan_piutang`, `detail`, `rollback_penerimaan`, `get_report_penerimaan`, dan `export_excel`; dependensi controller/model/library/view/JS ditelusuri.
- Cabang baru hanya untuk invoice yang lolos `Invoice_consulting::is_new` (consulting_version=3, bukan Vuca tipe 1 atau non-kons). Batch seluruhnya lama masih memakai proses legacy. Batch campuran diarahkan ke Consulting_receipt.
- Tidak membuka browser atau mengakses database aplikasi. Hasil berlaku untuk kode workspace, bukan bukti bahwa kode/schema sudah terpasang pada server.

## Ringkasan kesesuaian

| Konsep tanpa deposit | Hasil |
| --- | --- |
| Pilih customer, pilihan potong/tidak potong PPh, pilih beberapa invoice | Tersedia |
| TAX code berasal dari invoice, bukan dipilih bebas saat penerimaan | Tersedia pada alur baru |
| DPP 11/12 penjualan, PPN PKP 12% DPP, WAPU/KB PPN 0 | Tersedia di kalkulasi alur baru |
| PPh default 2% DPP, bisa diedit, nol bila tidak dipotong | Tersedia; default cicilan belum memperhitungkan pemotongan sebelumnya |
| Sisa piutang menjadi saldo invoice berikutnya | Writer baru mendukung; kontrak form/save bermasalah |
| Jurnal bank/PPh/admin debit, piutang kredit, sisa piutang debit | Tersedia dan diuji dengan fixture |
| Kontrol 0 terhadap uang masuk dari bank | Form ada; server menggunakan sumber nilai berbeda |
| Batas total admin Rp10.000 | Form membatasi total, server baru membatasi per invoice |
| Report penerimaan, filter, detail, ekspor, rollback draft | Endpoint dan tampilan tersedia; report/ekspor belum diverifikasi lewat DB/runtime web |

## Temuan prioritas

### 1. P1 — Format angka yang dikirim form ditolak parser server

`views/add.php:254` menginisialisasi autoNumeric dengan pemisah ribuan koma dan desimal titik. Handler simpan pada baris 310 memakai `serialize()` biasa tanpa mengubah nilai menjadi angka mentah. `consulting_process.php` juga merender `number_format(..., 2)`, misalnya `222,000,000.00`.

`Consulting_receipt.php:165` meneruskan field tersebut ke `Penerimaan_calculation::cents`, yang hanya menerima angka tanpa pemisah ribuan. Reproduksi menghasilkan `Format nominal tidak valid.`. Ini memengaruhi penerimaan, admin, PPh, dan sisa piutang bernilai ribuan atau lebih.

Rekomendasi: sepakati serialisasi nominal mentah di batas form/server, lalu uji payload form sebenarnya. Jangan melemahkan validasi nominal agar menerima teks ambigu.

### 2. P1 — Alokasi pelunasan piutang disamakan dengan uang masuk bank

Konsep HTML menjelaskan `Alokasi Penerimaan` sebagai bagian piutang yang dilunasi, bukan uang bank. View baru sesuai konsep itu: nilai awal sama dengan saldo piutang (`consulting_process.php:147`), dan saat sisa diubah nilainya menjadi piutang dikurangi sisa.

Namun `Consulting_receipt.php:186` menjumlahkan nilai field tersebut sebagai `submittedCashTotal`, lalu baris 364 memakai total itu sebagai uang bank pada kontrol. Contoh piutang Rp222.000.000, PPh Rp3.666.666,67, admin Rp2.500, bank Rp218.330.833,33: form memiliki kontrol 0 dan alokasi Rp222.000.000, tetapi server menolak dengan `Kontrol belum 0 (selisih -3669166.67)` bahkan setelah format angka dinormalisasi.

Rekomendasi: validasi per baris alokasi + sisa = saldo piutang; hitung uang bank terpisah. Gunakan nominal bank dari alokasi database untuk kontrol seluruh batch.

### 3. P1 — Kontrol server tidak memastikan uang bank seluruhnya direkonsiliasi

Pada `Consulting_receipt.php:360`, server hanya menolak ketika hasil kas melebihi kapasitas. Kontrol berikutnya membandingkan dengan angka POST, bukan kapasitas bank yang menjadi `Uang Masuk` pada form.

Fixture membuktikan split bank Rp500.000.000 dapat disimpan dengan penerimaan Rp218.330.833,33. Sisa Rp281.669.166,67 dibiarkan. Model daftar kemudian menganggap split Used begitu ada penerimaan (`Penerimaan_uang_model.php:75,119`), sehingga sisa tidak lagi tampil sebagai draft biasa. Konsep tanpa deposit mewajibkan menambah invoice atau kembali membagi alokasi ketika uang bank terlalu besar.

Rekomendasi: kontrol server harus memakai sisa nominal split bank aktual dan menolak selisih positif/negatif, sesuai form dan konsep. Pisahkan aturan ini dari pemeriksaan batas kapasitas.

### 4. P1 — Default PPh berulang pada penerimaan cicilan

`Consulting_receipt.php:66` selalu mengambil seluruh `tax_calc.pph_default`; konteks invoice baru mengisi `remaining_pph=0` tanpa membaca penerimaan sebelumnya. HTML acuan memakai `pphRemain = default - pphT`.

Fixture menyimpan cicilan Rp100.000.000 dan PPh penuh Rp3.666.666,67. Preview cicilan berikutnya tetap menawarkan PPh Rp3.666.666,67, seharusnya default 0 karena sudah terpakai. Writer hanya membatasi PPh terhadap saldo invoice, sehingga batas kumulatif PPh belum terlindungi.

Rekomendasi: hitung PPh yang sudah digunakan dari riwayat snapshot valid, tampilkan sisa sebagai default, dan tetapkan validasi kumulatif yang konsisten dengan aturan edit PPh/bukti potong.

### 5. P1 — Batch invoice baru dan lama tidak memiliki kontrak view yang lengkap

`process_alokasi` mengirim seluruh batch ke `consulting_process` ketika salah satu invoice baru. `invoice_context` cabang legacy (`Consulting_receipt.php:97`) tidak menyediakan `tax_calc` dan `default_pph`, sedangkan view mengakses keduanya tanpa fallback (`consulting_process.php:104`).

Reproduksi preview invoice konsultasi baru + invoice konsultasi lama menghasilkan `Undefined index: tax_calc` saat view dirender dengan warning sebagai exception. Pada runtime yang tidak menaikkan warning, rincian pajak/default dapat menjadi kosong atau nol. Formula jurnal view juga selalu memakai formula baru sementara writer legacy masih memakai formula lama.

Rekomendasi: buat kontrak konteks/render dan validasi yang mendukung tiap jenis invoice secara eksplisit, atau tolak batch campuran dengan pesan jelas sampai dukungannya lengkap. Jangan mengubah saldo/jurnal historis secara massal.

### 6. P2 — Batas total biaya admin hanya dilindungi di client

Form menolak total admin > Rp10.000. Writer baru (`Consulting_receipt.php:174`) hanya memeriksa admin per invoice; `totAdmin` dihitung tetapi tidak dibandingkan dengan batas total.

Fixture dua invoice dengan admin masing-masing Rp6.000 berhasil disimpan, total Rp12.000. Helper `validate_batch` memiliki aturan total yang benar, tetapi tidak dipanggil writer tersebut.

Rekomendasi: terapkan batas total setelah seluruh invoice dihitung, termasuk kebijakan untuk baris legacy dalam batch campuran, sebelum mutasi disimpan.

### 7. P2 — Tanggal preview jurnal berbeda dengan jurnal yang disimpan

`consulting_process.php:249` mengisi `transDate` dari tanggal invoice. Writer `Consulting_receipt.php` memakai tanggal transaksi rekening koran dari alokasi. Pada fixture, invoice 25 Oktober dan koran 26 Oktober, sehingga preview dan hasil simpan berbeda tanggal. Konsep Excel Test Case memakai tanggal rekening koran sebagai tanggal jurnal.

Rekomendasi: kirim tanggal alokasi ke metadata preview jurnal.

## Catatan sumber konsep

- Workbook sendiri memiliki ketidakkonsistenan: sheet Test Case bagian "Tidak Potong PPH 23 + 2 Invoice" tetap mengisi PPh (`G66:G67`); berbeda dengan Definisi/Konsep dan HTML yang mewajibkan PPh=0 untuk pilihan tidak dipotong. Audit mengikuti definisi dan HTML yang konsisten untuk pilihan N.
- Beberapa contoh menggunakan uang bank bulat tetapi formula PPh pecahan sehingga kontrol menyisakan sekitar Rp0,33 (`K44`, `K71`, `K98`). Ini perlu kebijakan pembulatan eksplisit sebelum menjadikan nilai contoh sebagai expected test. Audit tidak menganggap selisih contoh tersebut sebagai bug tersendiri pada aplikasi.
- Requirements Excel menyebut Non PPN, sementara HTML/kode memakai KB (Kawasan Berikat). Keduanya tidak otomatis merupakan kategori bisnis yang sama. Audit memastikan perilaku yang eksplisit pada HTML: WAPU/KB bernilai PPN 0; cakupan kategori Non PPN umum belum dapat disimpulkan.

## Bukti pengujian tanpa browser

Runner PHP: `scratch/runtime/php74/php.exe`. Seluruh tes berikut lulus:

- `application/modules/penerimaan_uang/tests/Consulting_receipt_tobe_test.php`: enam kelompok skenario PKP, WAPU, kurang bayar/jurnal, edit PPh, penolakan input, rollback.
- `application/modules/penerimaan_uang/tests/calculation_test.php`.
- `node application/modules/penerimaan_uang/tests/calculation_test.js`.
- `node application/modules/penerimaan_uang/tests/installment_view_test.js`.
- `application/modules/penerimaan_uang/tests/detail_journal_test.php`.
- `application/modules/penerimaan_uang/tests/legacy_vuca_preview_test.php`.
- `scratch/penerimaan_concept_audit.php`: reproduksi format POST, gross versus bank, kapasitas bank tidak habis, default PPh cicilan setelah transaksi fixture, total admin Rp12.000, serta render batch campuran. Exit 0 berarti kondisi bug yang dinyatakan berhasil dibuktikan, bukan implementasi sudah benar.

Tes To-Be yang sudah ada memasukkan nominal mentah dan uang bank net pada field `penerimaan`, sedangkan view mengirim nominal berformat dan alokasi piutang gross. Karena itu kelulusan tes tersebut tidak membuktikan alur form/save benar. Fixture audit memakai adapter in-memory dari tes existing; tidak membuktikan commit, locking, konkurensi, schema, laporan/ekspor nyata, atau deployment.

## File audit dan tindak lanjut

- Ditambah laporan ini dan `scratch/penerimaan_concept_audit.php` sebagai bukti yang bisa dijalankan ulang.
- `docs/implementation-plan.md` dan `docs/task-progress.md` diberi catatan hasil audit untuk mengoreksi klaim kesesuaian 100% pada catatan implementasi sebelumnya.
- Tidak ada kode produksi, file referensi pengguna, data invoice, jurnal, atau saldo bank yang diubah.
- Prioritas perbaikan: kontrak nominal/form/save dan kontrol terhadap bank, kemudian riwayat PPh, batch campuran, batas admin, dan tanggal preview. Setelah perbaikan, uji payload yang berasal dari view dan seluruh cabang terdampak. Deposit tetap di luar scope.
