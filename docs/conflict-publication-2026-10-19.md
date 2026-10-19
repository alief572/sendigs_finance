# Publikasi perbaikan konflik development - 19 Oktober 2026

## Cakupan yang diotorisasi

Resolusi konflik pada 46 file kode/aset di luar invoicing, dua dokumen gabungan, satu fixture native posting, dan laporan ini. Total 50 file. Patch diambil dari snapshot resolusi sebelum pekerjaan lokal lama dipulihkan, sehingga perubahan audit Expense, perubahan payment lain, serta compact table/SweetAlert invoicing tidak ikut terbawa.

Dikecualikan secara eksplisit: application/config/config.php, application/config/constants.php, seluruh application/modules/invoicing/, aset CSS/JS khusus invoicing, manifest dan dokumentasi khusus invoicing. Tidak menyentuh database, browser atau deployment server.

## Keputusan resolusi

Pertahankan fitur lampiran kasbon, petty cash, export toolbar, serta integrasi penerimaan yang sudah ada di hasil merge. Guard POST/permission dan pemeriksaan begin/commit pada jurnal payment petty cash dipertahankan. Fixture Legacy_posting_test mendapat metode row_array/result_array dan simulasi GET_LOCK/RELEASE_LOCK agar dapat menguji adapter consulting yang sudah ada di remote; assertion tidak dilemahkan.

## Validasi salinan publikasi

- Snapshot terpisah dari HEAD remote 2110bbb4 ditambah hanya kandidat commit; hasil ini berbeda dari working tree yang masih menyimpan pekerjaan lokal lain.
- 44 file PHP/JS kandidat lulus pemeriksaan sintaks; diff produksi selain fixture hanya membuang marker dan sisi konflik yang tidak dipakai.
- 18/19 suite PASS: query/controller/dispatcher/native petty posting/revision/metadata/client/native invoice-receipt-payment posting, lampiran kasbon PHP/JS, record request payment query/controller/view/client dan XLSX roundtrip, detail/perhitungan/inline installment penerimaan.
- legacy_vuca_preview_test.php FAIL: memuat application/modules/invoicing/controllers/Invoicing.php yang masih memiliki marker dari commit remote. Modul tersebut sengaja dikecualikan pengguna, sehingga kegagalan dependency ini tetap tercatat dan tidak disembunyikan dengan mengubah tes.
- 5 file remote tetap memiliki marker karena dikecualikan: config.php dan empat file modul invoicing. Constants remote juga tidak diperbarui; nama database lokal tanpa _dev tetap lokal.
- Pemeriksaan indeks sebelum commit wajib memastikan tak ada path config.php/constants.php/invoicing atau pekerjaan lokal lain di luar daftar ini. Push menggunakan development tanpa force; SHA remote diverifikasi sesudah publikasi.

## Berkas kode/aset

- `application/modules/accessories/views/add.php`
- `application/modules/accessories/views/detail.php`
- `application/modules/accessories/views/index.php`
- `application/modules/expense/controllers/Expense.php`
- `application/modules/expense/migrations/001_add_keterangan_pengembalian.sql`
- `application/modules/expense/migrations/002_add_bank_pengembalian.sql`
- `application/modules/expense/migrations/003_add_rejected_fields.sql`
- `application/modules/expense/views/expense_review.php`
- `application/modules/expense/views/form.php`
- `application/modules/expense/views/form_report.php`
- `application/modules/expense/views/index.php`
- `application/modules/expense/views/kasbon_pr_asset_print.php`
- `application/modules/expense/views/kasbon_pr_dept_print.php`
- `application/modules/expense/views/kasbon_pr_stok_print.php`
- `application/modules/expense/views/kasbon_print.php`
- `application/modules/expense/views/reject_card.php`
- `application/modules/expense/views/transport_req_print.php`
- `application/modules/expense_petty_cash/models/Expense_petty_cash_model.php`
- `application/modules/expense_petty_cash/models/Expense_petty_cash_pelaporan_model.php`
- `application/modules/expense_petty_cash/views/pelaporan/index.php`
- `application/modules/expense_petty_cash/views/pencatatan/index.php`
- `application/modules/jurnal_payment_petty_cash/controllers/Jurnal_payment_petty_cash.php`
- `application/modules/jurnal_payment_petty_cash/models/Jurnal_payment_petty_cash_model.php`
- `application/modules/jurnal_penerimaan/controllers/Jurnal_penerimaan.php`
- `application/modules/pembayaran_material/controllers/Pembayaran_material.php`
- `application/modules/pembayaran_material/models/Pembayaran_material_model.php`
- `application/modules/penerimaan_pph_23/controllers/Penerimaan_pph_23.php`
- `application/modules/penerimaan_pph_23/models/Penerimaan_pph_23_model.php`
- `application/modules/penerimaan_uang/controllers/Penerimaan_uang.php`
- `application/modules/penerimaan_uang/views/process.php`
- `application/modules/pengajuan_rutin/views/input_form.php`
- `application/modules/petty_cash_vuca_sustain/models/Petty_cash_vuca_sustain_model.php`
- `application/modules/petty_cash_vuca_sustain/views/index.php`
- `application/modules/petty_cash_vuca_sustain/views/view.php`
- `application/modules/pr_rutin/views/pr_list_approval.php`
- `application/modules/record_request_payment/controllers/Record_request_payment.php`
- `application/modules/record_request_payment/models/Record_request_payment_model.php`
- `application/modules/record_request_payment/tests/Tabs_client_test.js`
- `application/modules/record_request_payment/tests/Tabs_query_test.php`
- `application/modules/record_request_payment/tests/Tabs_test.php`
- `application/modules/record_request_payment/views/index.php`
- `application/modules/record_request_payment/views/items.php`
- `assets/js/modules/expense_petty_cash/approval.js`
- `assets/js/modules/expense_petty_cash/pelaporan.js`
- `assets/js/modules/expense_petty_cash/pencatatan.js`
- `themes/admin/views/header.php`
- `application/modules/jurnal_all/tests/Legacy_posting_test.php`
