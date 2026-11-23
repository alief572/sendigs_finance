# Invoice Non Konsultasi versi 3 ? 23 November 2026

## Hasil dan batas

Invoice non konsultasi baru mengikuti konsultasi terbaru untuk empat perlakuan PPN, empat sales_role, snapshot company/COA, penerimaan dan posting. Invoice lama, termasuk draf lama, tidak dikonversi. Header/detail, jurnal, saldo, dan nomor ditulis atomik. Implementasi tidak mengubah database bisnis atau menjalankan migrasi. Publikasi Git dibatasi ke development; tidak ada deployment aplikasi.

Sumber tetap penawaran, bukan TOP: total item dibulatkan per baris ditambah biaya kirim; biaya kirim diambil dari penawaran dan dipertahankan saat edit. Batas penerbitan tetap grand_total dikurangi seluruh total_nominal terinvoice, mengecualikan invoice sendiri saat edit. Customer/identitas sumber diambil server. Nomor dialokasikan dengan lock/sequence bersama konsultasi dan Vuca.

PKP: piutang penjualan + PPN. WAPU/KB/UMKM: piutang penjualan. DPP penjualan x 11/12; PPN 12% dari DPP kecuali UMKM nol. Jurnal mengikuti helper konsultasi yang sama. PPh saat penerimaan, bukan pengurang saldo awal.

## Routing dan kompatibilitas

- URL non konsultasi tetap; source_fingerprint, tax_treatment dan sales_role wajib pada create. Source fingerprint mencakup penawaran, detail sumber, jumlah terinvoice dan mapping. Request lama yang belum dimuat ulang ditolak.
- Update memakai ID tersimpan, bukan marker browser. Fingerprint mencakup header + detail. Invoice posted/closed, ada penerimaan, saldo berubah, atau jurnal hilang ditolak. Snapshot lama tidak diperbarui dari mapping terbaru saat edit.
- Penanda baru: consulting_version=3, non_kons=1, consulting_snapshot.invoice_kind=non_consulting, consulting_top_key=NULL. is_new tetap khusus konsultasi; is_snapshot_invoice digunakan konsumen bersama. Snapshot rusak tidak jatuh ke writer legacy.
- Form konsultasi dipakai bersama; rincian TOP diganti item dengan qty dua desimal. Total, jurnal dan customer dari browser tidak dipercaya. PO/faktur wajib. Data dinamis di-escape; tambah/hapus item mempunyai label dan pemulihan fokus.
- Cetak memakai item/angka tersimpan, kop/rekening owner tersimpan, referensi penawaran, dan instruksi pembayaran. Aksi print modern melewati modal ganti company; endpoint catatan print tidak dapat mengubah owner jurnal.
- Penerimaan mendukung batch modern konsultasi/non konsultasi dengan customer/company sama, pembayaran sebagian/lunas, PPh/admin, lebih bayar/deposit dan rollback. Legacy tidak boleh dicampur. Posting memakai snapshot dan accounting owner. Guard PPh legacy mencegah pencatatan ganda.
- Company pada daftar non konsultasi, Jurnal All, Jurnal Invoicing/laporannya dan Monitoring mengikuti owner baru. Filter company historis tetap memakai pencocokan sebelumnya. Cakupan/whitelist Follow Up tidak diperluas.
- Respons status/msg, izin existing, audit sesudah commit dan penanganan kegagalan audit dipertahankan. Tanpa dependency baru.

## File runtime

- Invoicing: controllers/Invoicing.php; libraries/Invoice_consulting.php, Invoice_consulting_service.php, Consulting_posting.php; views/consulting_invoice.php, non_consulting_items.php, non_consulting_print.php.
- Client: assets/js/invoicing/consulting-form.js dan non-consulting-form.js. Memakai CSS/calculator/save feedback existing.
- Integrasi: penerimaan_uang controller + Consulting_receipt; jurnal_invoicing model; jurnal_all model; report_jurnal_invoicing model; monitoring_piutang model; penerimaan_pph_23 controller + model.
- Tes: Non_consulting_integration_cases.php, Non_consulting_render_test.php, Non_consulting_client_test.js; harness Consulting_integration_test.php, audit invoicing, Vuca audit expectations, fixture/COA tests Jurnal All. Manifest runtime: docs/invoicing-consulting-manifest.json.

## Validasi

Suite fokus akhir PASS **1.050 pemeriksaan**. Bukti akhir dicatat pada docs/task-progress.md. Suite --non-consulting menggunakan database acak codex_consulting_* di loopback 33319, tidak membaca kredensial aplikasi, dan menghapus fixture pada finally. Mencakup regresi inti konsultasi/Vuca, 4 pajak ? 4 sales_role, owner 7/3/4, stale/double submit, batas outstanding, race dua worker, edit, tamper, fault header/detail/jurnal/sequence/commit, audit outage/deduplikasi, penerimaan/PPh/admin, rollback, overpayment/deposit, mixed modern batch, posting dan company SQL asli.

Render/cetak modern dan konsultasi: 487 checks. Audit invoicing: 162 skenario; penerimaan batch 1/2: 36/61. Regresi kalkulasi, print konsultasi, ekspor, Vuca, Jurnal All/COA/dispatcher/controller/audit, Monitoring dan jurnal UMKM dijalankan. PHP lint PASS 23 file; sintaks JS dan whitespace PASS. SQL asli daftar non konsultasi, Jurnal Invoicing dan laporan posting diuji dengan owner berbeda dari company sumber. Filter raw dibatasi konstanta; ENUM non_kons dibandingkan dengan string, bukan indeks numerik. Tanpa browser.

Kegagalan historis yang terpisah: suite integrasi default mengharapkan deposit per company pada Concept_integration_cases, sedangkan runtime kini menjumlah per customer; List_filter_test fixture tidak mempunyai total_nominal_jurnal; Ui_list_client_test tidak memuat CompanyMulti. Ketiga test tidak dilemahkan atau dihapus. Suite fokus baru menguji alur deposit/rollback modern secara langsung dan tetap menjalankan regresi inti konsultasi/Vuca. Bootstrap harness melengkapi helper tampilan/mbstring worker; allowlist metadata audit dan pembulatan print disesuaikan dengan kontrak runtime yang sudah ada.

## Prasyarat dan risiko

- Schema migration 003_consulting_v3 harus sudah tersedia; sumber penawaran/header/detail dan tabel transaksi harus InnoDB. Mapping sembilan akun harus valid pada accounting tujuan. Finance/accounting harus satu server untuk posting atomik.
- Rilis seluruh runtime bersama. Form lama yang masih terbuka perlu dimuat ulang. Tidak ada backfill atau pengubahan 138 header non konsultasi lama yang ID customernya kosong.
- Penawaran screenshot P-NON-KONS-001-07-26 sudah terinvoice penuh; form baru menolak penerbitan ulang. Invoice 228/STM/2026 tetap tidak diubah.
- Posting UMKM mengikuti batas existing: akun PPh Final harus dilengkapi sebelum jurnal terkait dapat diposting.

## Publikasi development

Paket publikasi disiapkan dari origin/development ebb52559 pada worktree terisolasi. Hanya 29 file task disertakan. Perubahan audit penerimaan lokal yang sudah ada sebelum task tidak disertakan: controller penerimaan remote hanya menerima perubahan pengenal versi, referensi dan field query yang diperlukan. Dokumentasi checkpoint/rencana bersama tetap lokal karena memuat pekerjaan lain.

Paket yang akan dikirim diuji kembali pada worktree ini dengan native integration --non-consulting, render/cetak, audit invoicing, item client dan query/COA Jurnal All. Hasil tercatat pada checkpoint lokal. Rilis ini hanya commit kode ke development, tanpa migrasi, perubahan data bisnis, deployment atau publikasi main.
