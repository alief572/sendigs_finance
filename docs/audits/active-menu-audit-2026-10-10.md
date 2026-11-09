# Audit pencatatan logs — menu aktif SENDIGS

Tanggal: 10 Oktober 2026. Database: `db_sendigs_ss` pada MySQL Docker lokal.

## Hasil utama

- 347 baris menu; 260 berstatus aktif.
- 126 baris memenuhi syarat sidebar: status aktif, group 1, parent aktif sampai root.
- 101 link menu mengarah ke 72 modul/controller unik; semuanya ditemukan di repository.
- 36 modul tanpa hook audit yang ditemukan; 31 memiliki hook pada sebagian endpoint.
- 3 modul memiliki hook di setiap endpoint, tetapi pencatatan setiap cabang/failure belum lengkap atau belum terverifikasi.
- `request_pr_stok`: seluruh 24 endpoint sudah diinstrumentasi dan diuji terfokus pada pekerjaan sebelumnya.
- `audit_trail`: sengaja tidak mencatat aktivitas pembacaan dirinya sendiri.

## Cara membaca hasil

Pemeriksaan dimulai dari tabel `menus`, bukan seluruh folder modul. Controller ditentukan dari link menu (misalnya `users/setting` mengarah ke `Setting.php`). Setelah itu, fungsi controller dan model yang dimuat eksplisit ditelusuri untuk pemanggilan `history()`, `simpan_aktifitas()` atau `audit_log()`, termasuk melalui helper privat controller. Komentar dan literal string tidak dihitung sebagai pemanggilan fungsi.

Kolom **endpoint punya hook / total** menyatakan keberadaan jalur pemanggilan pada kode, bukan persentase aktivitas yang pasti tersimpan. `history()` lama umumnya tidak mengirim status operasi, dan banyak ditempatkan hanya pada cabang sukses. Karena itu adanya hook belum berarti modul sepenuhnya tercakup. Pemanggilan dinamis, helper global lain, library/service eksternal dan callback model perlu pemeriksaan lanjutan saat implementasi per modul.

Scope sidebar mengikuti struktur menu untuk administrator; permission per user dapat menyembunyikan sebagian menu lagi. Dashboard yang dibangun langsung oleh Menu_generator dan rute login/logout tanpa menu bukan bagian dari hitungan 101 link. Login/logout/2FA sudah diinstrumentasi sebelumnya; itu tidak membuktikan menu Settings → Users sudah lengkap.

## Prioritas perbaikan

1. Transaksi uang dan persetujuan: `request_payment`, `approval_request_payment`, `pembayaran_material`, `expense`, `expense_petty_cash`, `petty_cash_vuca_sustain`, `pengajuan_rutin`, `request_mutasi`.
2. Penjualan dan penerimaan: `plan_tagih`, `actual_plan_tagih`, `invoicing`, `approval_invoice_so`, `penerimaan_uang`, `penerimaan_pph_23`, `alokasi`, `alokasi_kalibrasi`, `unlocated_penerimaan`.
3. Purchasing dan stok: `purchase_request`, `purchase_order`, `purchase_order_payment`, `ros`, `approval_po`, `non_rutin`, `app_pr_stock`, `pr_asset`, incoming/outgoing/adjustment.
4. Master data, Settings, jurnal dan laporan/export. Prioritas ini adalah usulan berdasarkan jenis aktivitas, bukan perubahan yang sudah dijalankan.

## Modul tanpa hook yang ditemukan

| Modul | Menu aktif | Endpoint punya hook / total | Contoh operasi tanpa hook |
|---|---|---|---|
| `actual_plan_tagih` [controller](C:/web_dev/sendigs_finance/application/modules/actual_plan_tagih/controllers/Actual_plan_tagih.php) | Actual Plan Tagih | 0 / 11 | `save_actual_plan_tagih`, `save_actual_plan_tagih_macet`, `update_actual_plan_tagih`, `batch_process_tagih` |
| `alokasi` [controller](C:/web_dev/sendigs_finance/application/modules/alokasi/controllers/Alokasi.php) | Alokasi | 0 / 15 | `upload_rekening_koran`, `save_alokasi`, `save_split_alokasi`, `approve_data` |
| `alokasi_kalibrasi` [controller](C:/web_dev/sendigs_finance/application/modules/alokasi_kalibrasi/controllers/Alokasi_kalibrasi.php) | Alokasi Kalibrasi | 0 / 7 | `upload_rekening_koran`, `save_alokasi` |
| `approval_invoice_so` [controller](C:/web_dev/sendigs_finance/application/modules/approval_invoice_so/controllers/Approval_invoice_so.php) | Approval Invoice | 0 / 3 | `approve_invoice`, `reject_invoice` |
| `approval_request_payment` [controller](C:/web_dev/sendigs_finance/application/modules/approval_request_payment/controllers/Approval_request_payment.php) | Approval Request Payment | 0 / 4 | `save_decision` |
| `closed_po` [controller](C:/web_dev/sendigs_finance/application/modules/closed_po/controllers/Closed_po.php) | Closed PO | 0 / 2 | `index`, `view_po` |
| `closed_pr` [controller](C:/web_dev/sendigs_finance/application/modules/closed_pr/controllers/Closed_pr.php) | Closed PR | 0 / 2 | `index`, `view_barang_pr` |
| `costbook_stock` [controller](C:/web_dev/sendigs_finance/application/modules/costbook_stock/controllers/Costbook_stock.php) | Costbook Stock Per Item; Costbook Stock Neraca Gudang | 0 / 5 | `per_item`, `neraca_gudang`, `change_warehouse`, `view_per_item` |
| `expense_petty_cash` [controller](C:/web_dev/sendigs_finance/application/modules/expense_petty_cash/controllers/Expense_petty_cash.php) | Transaksi Petty Cash; Pelaporan Petty Cash; Approval Expense Petty Cash | 0 / 25 | `create_pelaporan`, `submit_pelaporan`, `approve`, `reject` |
| `historical_alokasi` [controller](C:/web_dev/sendigs_finance/application/modules/historical_alokasi/controllers/Historical_alokasi.php) | Historical Alokasi | 0 / 3 | `index`, `get_historical_alokasi`, `get_timeline` |
| `invoicing` [controller](C:/web_dev/sendigs_finance/application/modules/invoicing/controllers/Invoicing.php) | Invoicing | 0 / 36 | `save_invoice`, `save_invoice_vuca`, `update_invoice`, `update_invoice_vuca` |
| `jurnal` [controller](C:/web_dev/sendigs_finance/application/modules/jurnal/controllers/Jurnal.php) | Jurnal | 0 / 5 | `save_posting_jurnal`, `fix_company` |
| `jurnal_invoicing` [controller](C:/web_dev/sendigs_finance/application/modules/jurnal_invoicing/controllers/Jurnal_invoicing.php) | Jurnal Invoicing | 0 / 8 | `fix_company`, `save_posting_jurnal`, `update_sts_revisi_jurnal` |
| `jurnal_payment` [controller](C:/web_dev/sendigs_finance/application/modules/jurnal_payment/controllers/Jurnal_payment.php) | Jurnal Payment | 0 / 6 | `save_posting_jurnal`, `fix_company` |
| `jurnal_payment_petty_cash` [controller](C:/web_dev/sendigs_finance/application/modules/jurnal_payment_petty_cash/controllers/Jurnal_payment_petty_cash.php) | Jurnal Payment Petty Cash | 0 / 8 | `save_posting_jurnal` |
| `jurnal_penerimaan` [controller](C:/web_dev/sendigs_finance/application/modules/jurnal_penerimaan/controllers/Jurnal_penerimaan.php) | Jurnal Penerimaan | 0 / 8 | `save_posting_jurnal`, `fix_company`, `update_sts_revisi_jurnal` |
| `master_customers` [controller](C:/web_dev/sendigs_finance/application/modules/master_customers/controllers/Master_customers.php) | Master Customer | 0 / 23 | `delDetail`, `deleteCategory`, `deletelokal`, `deleteinternational` |
| `penerimaan_pph_23` [controller](C:/web_dev/sendigs_finance/application/modules/penerimaan_pph_23/controllers/Penerimaan_pph_23.php) | Penerimaan PPH 23 | 0 / 4 | `save_penerimaan_pph_23` |
| `penerimaan_uang` [controller](C:/web_dev/sendigs_finance/application/modules/penerimaan_uang/controllers/Penerimaan_uang.php) | Penerimaan Piutang | 0 / 8 | `save_penerimaan_piutang`, `rollback_penerimaan` |
| `petty_cash_master` [controller](C:/web_dev/sendigs_finance/application/modules/petty_cash_master/controllers/Petty_cash_master.php) | Master Petty Cash | 0 / 7 | `save`, `delete` |
| `petty_cash_vuca_sustain` [controller](C:/web_dev/sendigs_finance/application/modules/petty_cash_vuca_sustain/controllers/Petty_cash_vuca_sustain.php) | Petty Cash Vuca Sustain | 0 / 6 | `payment_hutang` |
| `plan_tagih` [controller](C:/web_dev/sendigs_finance/application/modules/plan_tagih/controllers/Plan_tagih.php) | Plan Tagih | 0 / 8 | `save_plan_tagih`, `revisi_plan_tagih`, `updateee` |
| `purchase_order` [controller](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php) | Purchase Order | 0 / 89 | `delDetail`, `Approved`, `saveNewPenawaran`, `SaveNew` |
| `purchase_order_payment` [controller](C:/web_dev/sendigs_finance/application/modules/purchase_order_payment/controllers/Purchase_order_payment.php) | Receive Invoice | 0 / 16 | `save_invoice`, `check_invoice`, `clear_checked_invoice` |
| `purchase_request` [controller](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php) | List Outstanding PO | 0 / 52 | `delDetail`, `Approved`, `saveNewPenawaran`, `SaveNew` |
| `record_request_payment` [controller](C:/web_dev/sendigs_finance/application/modules/record_request_payment/controllers/Record_request_payment.php) | Record Request Payment | 0 / 5 | `index`, `get_data_record`, `get_data_record_legacy`, `detail` |
| `report_actual_plan_tagih` [controller](C:/web_dev/sendigs_finance/application/modules/report_actual_plan_tagih/controllers/Report_actual_plan_tagih.php) | Report Actual Plan Tagih | 0 / 7 | `index`, `download_excel`, `get_data_report_apt`, `get_data_report_apt_backup` |
| `report_jurnal_invoicing` [controller](C:/web_dev/sendigs_finance/application/modules/report_jurnal_invoicing/controllers/Report_jurnal_invoicing.php) | Report Jurnal Invoicing | 0 / 4 | `index`, `view_jurnal`, `export_excel`, `get_data_jurnal_invoicing` |
| `report_jurnal_payment` [controller](C:/web_dev/sendigs_finance/application/modules/report_jurnal_payment/controllers/Report_jurnal_payment.php) | Report Jurnal Payment | 0 / 5 | `index`, `get_data_jurnal`, `view_jurnal`, `export_excel` |
| `report_jurnal_penerimaan` [controller](C:/web_dev/sendigs_finance/application/modules/report_jurnal_penerimaan/controllers/Report_jurnal_penerimaan.php) | Report Jurnal Penerimaan | 0 / 4 | `index`, `view_jurnal`, `export_excel`, `get_data_jurnal_penerimaan` |
| `report_petty_cash` [controller](C:/web_dev/sendigs_finance/application/modules/report_petty_cash/controllers/Report_petty_cash.php) | Report Petty Cash | 0 / 3 | `index`, `get_data`, `export_excel` |
| `report_piutang_per_invoice` [controller](C:/web_dev/sendigs_finance/application/modules/report_piutang_per_invoice/controllers/Report_piutang_per_invoice.php) | Report Uninvoiced dan Piutang Per Invoice | 0 / 3 | `index`, `get_report_data`, `download_excel` |
| `request_mutasi` [controller](C:/web_dev/sendigs_finance/application/modules/request_mutasi/controllers/Request_mutasi.php) | Approval Mutasi; Request Mutasi; List Mutasi; Transaksi Bank | 0 / 21 | `save`, `save_mutasi`, `save_approval`, `reject_mutasi` |
| `request_payment` [controller](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php) | Approval Pengembalian Expense; Request Payment; Payment List; Pengembalian Expense | 0 / 46 | `submit_for_approval`, `save_request`, `save_approval_cons`, `save_approval` |
| `ros` [controller](C:/web_dev/sendigs_finance/application/modules/ros/controllers/Ros.php) | ROS | 0 / 14 | `add_custom_pembiayaan`, `del_custom_pib`, `save_ros`, `del_ros` |
| `unlocated_penerimaan` [controller](C:/web_dev/sendigs_finance/application/modules/unlocated_penerimaan/controllers/Unlocated_penerimaan.php) | Unlocated Penerimaan | 0 / 3 | `rollback` |

## Modul dengan cakupan sebagian

| Modul | Menu aktif | Endpoint punya hook / total | Contoh endpoint tanpa hook |
|---|---|---|---|
| `accessories` [controller](C:/web_dev/sendigs_finance/application/modules/accessories/controllers/Accessories.php) | Master Barang Stok | 3 / 6 | `data_side_accessories`, `detail`, `download_excel` |
| `adjustment_stock` [controller](C:/web_dev/sendigs_finance/application/modules/adjustment_stock/controllers/Adjustment_stock.php) | Adjustment Stock | 2 / 7 | `data_side_adjustment`, `get_stock`, `list_gudang_ke`, `list_material` |
| `app_pr_stock` [controller](C:/web_dev/sendigs_finance/application/modules/app_pr_stock/controllers/App_pr_stock.php) | Approval PR Stock Management | 5 / 10 | `approval_head`, `approval_cost_control`, `approval_management`, `data_side_approval_pr_material` |
| `approval_po` [controller](C:/web_dev/sendigs_finance/application/modules/approval_po/controllers/Approval_po.php) | Approval PO | 4 / 10 | `approve_po_process`, `reject_po_process` |
| `asset` [controller](C:/web_dev/sendigs_finance/application/modules/asset/controllers/Asset.php) | Assets; Asset Category | 6 / 15 | `InsertJurnal`, `saved_jurnal` |
| `asset_planning` [controller](C:/web_dev/sendigs_finance/application/modules/asset_planning/controllers/Asset_planning.php) | Rencana Pembelian Asset; Approval Pembelian Asset Finance; Approval Pembelian Asset Management | 9 / 17 | `server_side_rutin`, `detail_rutin`, `get_add`, `get_spec` |
| `budget_coa` [controller](C:/web_dev/sendigs_finance/application/modules/budget_coa/controllers/Budget_coa.php) | Master Budget; Budget Non Rutin; Budget Umum; Budget Expense | 5 / 24 | `approve_data_umum`, `revisi_data_umum`, `approve_data_category`, `revisi_data_category` |
| `budget_periodik` [controller](C:/web_dev/sendigs_finance/application/modules/budget_periodik/controllers/Budget_periodik.php) | Budget Pembayaran Periodik | 3 / 6 | `index`, `create`, `edit` |
| `budget_rutin` [controller](C:/web_dev/sendigs_finance/application/modules/budget_rutin/controllers/Budget_rutin.php) | Budget Stock | 1 / 10 | `index`, `get_cost_center`, `get_material`, `get_satuan` |
| `coa_expense` [controller](C:/web_dev/sendigs_finance/application/modules/coa_expense/controllers/Coa_expense.php) | Master COA | 1 / 5 | `index`, `create`, `edit`, `delete` |
| `departements` [controller](C:/web_dev/sendigs_finance/application/modules/departements/controllers/Departements.php) | Master Department | 3 / 5 | `edit`, `getDetail` |
| `employees` [controller](C:/web_dev/sendigs_finance/application/modules/employees/controllers/Employees.php) | Master Employee | 8 / 13 | `addfamily`, `getDetail`, `getDept`, `getTitle` |
| `expense` [controller](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php) | Expense; Approval Transportasi; Approval Kasbon Finance; Approval Kasbon Management; Approval Expense Finance; Approval Expense Management; Input Kasbon; Kasbon List; Input Transportasi; Pengajuan Transportasi; List Transportasi | 6 / 83 | `kasbon_save`, `kasbon_delete`, `save`, `delete` |
| `incoming_asset` [controller](C:/web_dev/sendigs_finance/application/modules/incoming_asset/controllers/Incoming_asset.php) | Incoming Asset | 5 / 13 | `server_side_incoming`, `query_data_json_incoming`, `modal_incoming`, `modal_detail` |
| `incoming_departemen` [controller](C:/web_dev/sendigs_finance/application/modules/incoming_departemen/controllers/Incoming_departemen.php) | Incoming Departemen | 5 / 14 | `server_side_incoming`, `query_data_json_incoming`, `modal_incoming`, `modal_detail` |
| `incoming_stok` [controller](C:/web_dev/sendigs_finance/application/modules/incoming_stok/controllers/Incoming_stok.php) | Incoming Stok | 2 / 8 | `data_side_request_material`, `print_incoming_stok`, `detail`, `detail_purchasing_order` |
| `master_bank` [controller](C:/web_dev/sendigs_finance/application/modules/master_bank/controllers/Master_bank.php) | Master Bank | 1 / 6 | `save_bank`, `del_bank` |
| `menus` [controller](C:/web_dev/sendigs_finance/application/modules/menus/controllers/Menus.php) | Menus | 2 / 8 | `index`, `create`, `edit`, `modalAdd_Permission` |
| `metode_pembelian` [controller](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php) | Metode Pembelian | 42 / 72 | `save_rfq` |
| `non_rutin` [controller](C:/web_dev/sendigs_finance/application/modules/non_rutin/controllers/Non_rutin.php) | PR Departemen; Approval PR Department Finance; Approval PR Depart Management | 4 / 19 | `edit_detail`, `close_pr` |
| `outgoing_stok_atk` [controller](C:/web_dev/sendigs_finance/application/modules/outgoing_stok_atk/controllers/Outgoing_stok_atk.php) | Outgoing | 5 / 11 | `request_to_subgudang`, `kurang_stock` |
| `pembayaran_material` [controller](C:/web_dev/sendigs_finance/application/modules/pembayaran_material/controllers/Pembayaran_material.php) | Payment | 4 / 18 | `list_request_payment`, `check_payment`, `clear_choosed_payment`, `save_payment` |
| `pengajuan_rutin` [controller](C:/web_dev/sendigs_finance/application/modules/pengajuan_rutin/controllers/Pengajuan_rutin.php) | Approval Pengajuan Pembayaran Rutin; Pengajuan Pembayaran Periodik | 3 / 12 | `save_data` |
| `permissions` [controller](C:/web_dev/sendigs_finance/application/modules/permissions/controllers/Permissions.php) | Permissions | 2 / 7 | `index`, `create`, `edit`, `print_request` |
| `pr_asset` [controller](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php) | PR Asset; Approval PR Asset Management | 20 / 40 | `upload_dokumen_pendukung`, `reset_pr_asset`, `generate_asset_manual`, `saved_jurnal` |
| `price_ref_barang_stok` [controller](C:/web_dev/sendigs_finance/application/modules/price_ref_barang_stok/controllers/Price_ref_barang_stok.php) | Approval Price Reference | 2 / 5 | `get_data`, `approval`, `view` |
| `price_sup_barang_stok` [controller](C:/web_dev/sendigs_finance/application/modules/price_sup_barang_stok/controllers/Price_sup_barang_stok.php) | Input Price Reference | 3 / 10 | `add` |
| `price_sup_raw_material` [controller](C:/web_dev/sendigs_finance/application/modules/price_sup_raw_material/controllers/Price_sup_raw_material.php) | Raw Material | 3 / 4 | `excel_report` |
| `stok_gudang_barang` [controller](C:/web_dev/sendigs_finance/application/modules/stok_gudang_barang/controllers/Stok_gudang_barang.php) | Stock | 1 / 5 | `data_side_stock`, `modal_history`, `download_excel`, `download_excel_history` |
| `supplier` [controller](C:/web_dev/sendigs_finance/application/modules/supplier/controllers/Supplier.php) | Master Supplier | 3 / 7 | `get_json_supplier`, `edit`, `detail`, `excel_report_all` |
| `users` [controller](C:/web_dev/sendigs_finance/application/modules/users/controllers/Setting.php) | Users | 1 / 8 | `update_user_permission` |

## Hook ada di semua endpoint, tetapi belum lengkap per cabang

- `accessories_category`: 3/3 endpoint. [controller](C:/web_dev/sendigs_finance/application/modules/accessories_category/controllers/Accessories_category.php). Contoh cabang buka form dan proses gagal belum mendapat pencatatan terpisah/status eksplisit; jangan dianggap setara cakupan PR Stok.
- `unit`: 3/3 endpoint. [controller](C:/web_dev/sendigs_finance/application/modules/unit/controllers/Unit.php). Contoh cabang buka form dan proses gagal belum mendapat pencatatan terpisah/status eksplisit; jangan dianggap setara cakupan PR Stok.
- `unit_packing`: 3/3 endpoint. [controller](C:/web_dev/sendigs_finance/application/modules/unit_packing/controllers/Unit_packing.php). Contoh cabang buka form dan proses gagal belum mendapat pencatatan terpisah/status eksplisit; jangan dianggap setara cakupan PR Stok.

## Bukti log yang sudah tersimpan

| Modul | Jumlah log | Terakhir tercatat (WIB) |
|---|---|---|
| `request_pr_stok` | 18 | 2026-10-10 22:41:40 |
| `unit` | 4 | 2026-10-10 21:41:24 |
| `non_rutin` | 2 | 2026-10-10 22:42:45 |

Log yang ada hanya membuktikan aktivitas tertentu pernah mencapai penulis audit. Tidak adanya log belum membuktikan kodenya tanpa hook: aktivitas bisa belum dijalankan. Contoh `non_rutin` sudah punya 2 log, tetapi masih ada endpoint tanpa hook.

## Detail endpoint dan bukti kode

### accessories

Menu: Master Barang Stok. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/accessories/controllers/Accessories.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/accessories/controllers/Accessories.php:36) | [history](C:/web_dev/sendigs_finance/application/modules/accessories/controllers/Accessories.php:46) | Tidak terdeteksi |
| [data_side_accessories](C:/web_dev/sendigs_finance/application/modules/accessories/controllers/Accessories.php:51) | Tidak ditemukan | Tidak terdeteksi |
| [add](C:/web_dev/sendigs_finance/application/modules/accessories/controllers/Accessories.php:56) | [history](C:/web_dev/sendigs_finance/application/modules/accessories/controllers/Accessories.php:132) | Ya |
| [detail](C:/web_dev/sendigs_finance/application/modules/accessories/controllers/Accessories.php:164) | Tidak ditemukan | Tidak terdeteksi |
| [hapus](C:/web_dev/sendigs_finance/application/modules/accessories/controllers/Accessories.php:183) | [history](C:/web_dev/sendigs_finance/application/modules/accessories/controllers/Accessories.php:211) | Ya |
| [download_excel](C:/web_dev/sendigs_finance/application/modules/accessories/controllers/Accessories.php:217) | Tidak ditemukan | Tidak terdeteksi |

### accessories_category

Menu: Category Stok. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/accessories_category/controllers/Accessories_category.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/accessories_category/controllers/Accessories_category.php:30) | [history](C:/web_dev/sendigs_finance/application/modules/accessories_category/controllers/Accessories_category.php:45) | Tidak terdeteksi |
| [add](C:/web_dev/sendigs_finance/application/modules/accessories_category/controllers/Accessories_category.php:51) | [history](C:/web_dev/sendigs_finance/application/modules/accessories_category/controllers/Accessories_category.php:98) | Ya |
| [delete](C:/web_dev/sendigs_finance/application/modules/accessories_category/controllers/Accessories_category.php:113) | [history](C:/web_dev/sendigs_finance/application/modules/accessories_category/controllers/Accessories_category.php:137) | Ya |

### actual_plan_tagih

Menu: Actual Plan Tagih. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/actual_plan_tagih/controllers/Actual_plan_tagih.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/actual_plan_tagih/controllers/Actual_plan_tagih.php:29) | Tidak ditemukan | Tidak terdeteksi |
| [add_plan_tagih](C:/web_dev/sendigs_finance/application/modules/actual_plan_tagih/controllers/Actual_plan_tagih.php:37) | Tidak ditemukan | Tidak terdeteksi |
| [aktual_tagihan_get](C:/web_dev/sendigs_finance/application/modules/actual_plan_tagih/controllers/Actual_plan_tagih.php:63) | Tidak ditemukan | Tidak terdeteksi |
| [aktual_tagihan_macet_get](C:/web_dev/sendigs_finance/application/modules/actual_plan_tagih/controllers/Actual_plan_tagih.php:91) | Tidak ditemukan | Tidak terdeteksi |
| [save_actual_plan_tagih](C:/web_dev/sendigs_finance/application/modules/actual_plan_tagih/controllers/Actual_plan_tagih.php:105) | Tidak ditemukan | Ya |
| [save_actual_plan_tagih_macet](C:/web_dev/sendigs_finance/application/modules/actual_plan_tagih/controllers/Actual_plan_tagih.php:254) | Tidak ditemukan | Ya |
| [download_excel](C:/web_dev/sendigs_finance/application/modules/actual_plan_tagih/controllers/Actual_plan_tagih.php:313) | Tidak ditemukan | Tidak terdeteksi |
| [update_actual_plan_tagih](C:/web_dev/sendigs_finance/application/modules/actual_plan_tagih/controllers/Actual_plan_tagih.php:323) | Tidak ditemukan | Ya |
| [get_actual_plan_tagih](C:/web_dev/sendigs_finance/application/modules/actual_plan_tagih/controllers/Actual_plan_tagih.php:368) | Tidak ditemukan | Tidak terdeteksi |
| [batch_process_tagih](C:/web_dev/sendigs_finance/application/modules/actual_plan_tagih/controllers/Actual_plan_tagih.php:379) | Tidak ditemukan | Ya |
| [view_last_input](C:/web_dev/sendigs_finance/application/modules/actual_plan_tagih/controllers/Actual_plan_tagih.php:658) | Tidak ditemukan | Tidak terdeteksi |

### adjustment_stock

Menu: Adjustment Stock. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/adjustment_stock/controllers/Adjustment_stock.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/adjustment_stock/controllers/Adjustment_stock.php:27) | [history](C:/web_dev/sendigs_finance/application/modules/adjustment_stock/controllers/Adjustment_stock.php:37) | Tidak terdeteksi |
| [data_side_adjustment](C:/web_dev/sendigs_finance/application/modules/adjustment_stock/controllers/Adjustment_stock.php:42) | Tidak ditemukan | Tidak terdeteksi |
| [add](C:/web_dev/sendigs_finance/application/modules/adjustment_stock/controllers/Adjustment_stock.php:47) | [history](C:/web_dev/sendigs_finance/application/modules/adjustment_stock/controllers/Adjustment_stock.php:158) | Ya |
| [get_stock](C:/web_dev/sendigs_finance/application/modules/adjustment_stock/controllers/Adjustment_stock.php:173) | Tidak ditemukan | Tidak terdeteksi |
| [list_gudang_ke](C:/web_dev/sendigs_finance/application/modules/adjustment_stock/controllers/Adjustment_stock.php:222) | Tidak ditemukan | Tidak terdeteksi |
| [list_material](C:/web_dev/sendigs_finance/application/modules/adjustment_stock/controllers/Adjustment_stock.php:258) | Tidak ditemukan | Tidak terdeteksi |
| [list_material_stock](C:/web_dev/sendigs_finance/application/modules/adjustment_stock/controllers/Adjustment_stock.php:271) | Tidak ditemukan | Tidak terdeteksi |

### alokasi

Menu: Alokasi. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/alokasi/controllers/Alokasi.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/alokasi/controllers/Alokasi.php:18) | Tidak ditemukan | Tidak terdeteksi |
| [upload_rekening_koran](C:/web_dev/sendigs_finance/application/modules/alokasi/controllers/Alokasi.php:33) | Tidak ditemukan | Ya |
| [get_bank_sesuai_alokasi](C:/web_dev/sendigs_finance/application/modules/alokasi/controllers/Alokasi.php:287) | Tidak ditemukan | Tidak terdeteksi |
| [get_alokasi_detail](C:/web_dev/sendigs_finance/application/modules/alokasi/controllers/Alokasi.php:332) | Tidak ditemukan | Tidak terdeteksi |
| [save_alokasi](C:/web_dev/sendigs_finance/application/modules/alokasi/controllers/Alokasi.php:428) | Tidak ditemukan | Ya |
| [get_jenis_bank](C:/web_dev/sendigs_finance/application/modules/alokasi/controllers/Alokasi.php:476) | Tidak ditemukan | Tidak terdeteksi |
| [get_alokasi](C:/web_dev/sendigs_finance/application/modules/alokasi/controllers/Alokasi.php:489) | Tidak ditemukan | Tidak terdeteksi |
| [get_alokasi_split_detail](C:/web_dev/sendigs_finance/application/modules/alokasi/controllers/Alokasi.php:494) | Tidak ditemukan | Tidak terdeteksi |
| [save_split_alokasi](C:/web_dev/sendigs_finance/application/modules/alokasi/controllers/Alokasi.php:525) | Tidak ditemukan | Ya |
| [get_view_split_alokasi](C:/web_dev/sendigs_finance/application/modules/alokasi/controllers/Alokasi.php:620) | Tidak ditemukan | Tidak terdeteksi |
| [review_upload](C:/web_dev/sendigs_finance/application/modules/alokasi/controllers/Alokasi.php:684) | Tidak ditemukan | Tidak terdeteksi |
| [get_pending_alokasi](C:/web_dev/sendigs_finance/application/modules/alokasi/controllers/Alokasi.php:694) | Tidak ditemukan | Tidak terdeteksi |
| [approve_data](C:/web_dev/sendigs_finance/application/modules/alokasi/controllers/Alokasi.php:732) | Tidak ditemukan | Ya |
| [delete_pending_data](C:/web_dev/sendigs_finance/application/modules/alokasi/controllers/Alokasi.php:749) | Tidak ditemukan | Ya |
| [update_pending_data](C:/web_dev/sendigs_finance/application/modules/alokasi/controllers/Alokasi.php:774) | Tidak ditemukan | Ya |

### alokasi_kalibrasi

Menu: Alokasi Kalibrasi. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/alokasi_kalibrasi/controllers/Alokasi_kalibrasi.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/alokasi_kalibrasi/controllers/Alokasi_kalibrasi.php:18) | Tidak ditemukan | Tidak terdeteksi |
| [upload_rekening_koran](C:/web_dev/sendigs_finance/application/modules/alokasi_kalibrasi/controllers/Alokasi_kalibrasi.php:33) | Tidak ditemukan | Ya |
| [get_bank_sesuai_alokasi](C:/web_dev/sendigs_finance/application/modules/alokasi_kalibrasi/controllers/Alokasi_kalibrasi.php:265) | Tidak ditemukan | Tidak terdeteksi |
| [get_alokasi_detail](C:/web_dev/sendigs_finance/application/modules/alokasi_kalibrasi/controllers/Alokasi_kalibrasi.php:310) | Tidak ditemukan | Tidak terdeteksi |
| [save_alokasi](C:/web_dev/sendigs_finance/application/modules/alokasi_kalibrasi/controllers/Alokasi_kalibrasi.php:399) | Tidak ditemukan | Ya |
| [get_jenis_bank](C:/web_dev/sendigs_finance/application/modules/alokasi_kalibrasi/controllers/Alokasi_kalibrasi.php:447) | Tidak ditemukan | Tidak terdeteksi |
| [get_alokasi](C:/web_dev/sendigs_finance/application/modules/alokasi_kalibrasi/controllers/Alokasi_kalibrasi.php:460) | Tidak ditemukan | Tidak terdeteksi |

### app_pr_stock

Menu: Approval PR Stock Management. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/app_pr_stock/controllers/App_pr_stock.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/app_pr_stock/controllers/App_pr_stock.php:41) | [history](C:/web_dev/sendigs_finance/application/modules/app_pr_stock/controllers/App_pr_stock.php:46) | Tidak terdeteksi |
| [approval_head](C:/web_dev/sendigs_finance/application/modules/app_pr_stock/controllers/App_pr_stock.php:51) | Tidak ditemukan | Tidak terdeteksi |
| [approval_cost_control](C:/web_dev/sendigs_finance/application/modules/app_pr_stock/controllers/App_pr_stock.php:61) | Tidak ditemukan | Tidak terdeteksi |
| [approval_management](C:/web_dev/sendigs_finance/application/modules/app_pr_stock/controllers/App_pr_stock.php:71) | Tidak ditemukan | Tidak terdeteksi |
| [data_side_approval_pr_material](C:/web_dev/sendigs_finance/application/modules/app_pr_stock/controllers/App_pr_stock.php:81) | Tidak ditemukan | Tidak terdeteksi |
| [approval_planning](C:/web_dev/sendigs_finance/application/modules/app_pr_stock/controllers/App_pr_stock.php:86) | [history](C:/web_dev/sendigs_finance/application/modules/app_pr_stock/controllers/App_pr_stock.php:150) | Ya |
| [detail_planning](C:/web_dev/sendigs_finance/application/modules/app_pr_stock/controllers/App_pr_stock.php:219) | Tidak ditemukan | Tidak terdeteksi |
| [process_approval_satuan](C:/web_dev/sendigs_finance/application/modules/app_pr_stock/controllers/App_pr_stock.php:271) | [history](C:/web_dev/sendigs_finance/application/modules/app_pr_stock/controllers/App_pr_stock.php:308) | Ya |
| [process_approval_all](C:/web_dev/sendigs_finance/application/modules/app_pr_stock/controllers/App_pr_stock.php:313) | [history](C:/web_dev/sendigs_finance/application/modules/app_pr_stock/controllers/App_pr_stock.php:371) | Ya |
| [process_reject_all](C:/web_dev/sendigs_finance/application/modules/app_pr_stock/controllers/App_pr_stock.php:376) | [history](C:/web_dev/sendigs_finance/application/modules/app_pr_stock/controllers/App_pr_stock.php:416) | Ya |

### approval_invoice_so

Menu: Approval Invoice. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/approval_invoice_so/controllers/Approval_invoice_so.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/approval_invoice_so/controllers/Approval_invoice_so.php:25) | Tidak ditemukan | Tidak terdeteksi |
| [approve_invoice](C:/web_dev/sendigs_finance/application/modules/approval_invoice_so/controllers/Approval_invoice_so.php:40) | Tidak ditemukan | Ya |
| [reject_invoice](C:/web_dev/sendigs_finance/application/modules/approval_invoice_so/controllers/Approval_invoice_so.php:72) | Tidak ditemukan | Ya |

### approval_po

Menu: Approval PO. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/approval_po/controllers/Approval_po.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/approval_po/controllers/Approval_po.php:27) | [history](C:/web_dev/sendigs_finance/application/modules/approval_po/controllers/Approval_po.php:32) | Tidak terdeteksi |
| [data_side_approval_pr_material](C:/web_dev/sendigs_finance/application/modules/approval_po/controllers/Approval_po.php:37) | Tidak ditemukan | Tidak terdeteksi |
| [approval_planning](C:/web_dev/sendigs_finance/application/modules/approval_po/controllers/Approval_po.php:42) | [history](C:/web_dev/sendigs_finance/application/modules/approval_po/controllers/Approval_po.php:106) | Ya |
| [detail_planning](C:/web_dev/sendigs_finance/application/modules/approval_po/controllers/Approval_po.php:145) | Tidak ditemukan | Tidak terdeteksi |
| [process_approval_satuan](C:/web_dev/sendigs_finance/application/modules/approval_po/controllers/Approval_po.php:181) | [history](C:/web_dev/sendigs_finance/application/modules/approval_po/controllers/Approval_po.php:218) | Ya |
| [process_approval_all](C:/web_dev/sendigs_finance/application/modules/approval_po/controllers/Approval_po.php:223) | [history](C:/web_dev/sendigs_finance/application/modules/approval_po/controllers/Approval_po.php:258) | Ya |
| [View](C:/web_dev/sendigs_finance/application/modules/approval_po/controllers/Approval_po.php:263) | Tidak ditemukan | Tidak terdeteksi |
| [po_approval](C:/web_dev/sendigs_finance/application/modules/approval_po/controllers/Approval_po.php:288) | Tidak ditemukan | Tidak terdeteksi |
| [approve_po_process](C:/web_dev/sendigs_finance/application/modules/approval_po/controllers/Approval_po.php:536) | Tidak ditemukan | Ya |
| [reject_po_process](C:/web_dev/sendigs_finance/application/modules/approval_po/controllers/Approval_po.php:566) | Tidak ditemukan | Ya |

### approval_request_payment

Menu: Approval Request Payment. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/approval_request_payment/controllers/Approval_request_payment.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/approval_request_payment/controllers/Approval_request_payment.php:27) | Tidak ditemukan | Tidak terdeteksi |
| [get_data_pending](C:/web_dev/sendigs_finance/application/modules/approval_request_payment/controllers/Approval_request_payment.php:35) | Tidak ditemukan | Tidak terdeteksi |
| [detail](C:/web_dev/sendigs_finance/application/modules/approval_request_payment/controllers/Approval_request_payment.php:41) | Tidak ditemukan | Tidak terdeteksi |
| [save_decision](C:/web_dev/sendigs_finance/application/modules/approval_request_payment/controllers/Approval_request_payment.php:75) | Tidak ditemukan | Ya |

### asset

Menu: Assets; Asset Category. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/asset/controllers/Asset.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/asset/controllers/Asset.php:32) | [history](C:/web_dev/sendigs_finance/application/modules/asset/controllers/Asset.php:41) | Tidak terdeteksi |
| [type](C:/web_dev/sendigs_finance/application/modules/asset/controllers/Asset.php:45) | [history](C:/web_dev/sendigs_finance/application/modules/asset/controllers/Asset.php:49) | Tidak terdeteksi |
| [data_side](C:/web_dev/sendigs_finance/application/modules/asset/controllers/Asset.php:53) | Tidak ditemukan | Tidak terdeteksi |
| [data_side_category](C:/web_dev/sendigs_finance/application/modules/asset/controllers/Asset.php:58) | Tidak ditemukan | Tidak terdeteksi |
| [modal_edit](C:/web_dev/sendigs_finance/application/modules/asset/controllers/Asset.php:63) | Tidak ditemukan | Tidak terdeteksi |
| [modal_jurnal](C:/web_dev/sendigs_finance/application/modules/asset/controllers/Asset.php:82) | Tidak ditemukan | Tidak terdeteksi |
| [modal_view](C:/web_dev/sendigs_finance/application/modules/asset/controllers/Asset.php:87) | Tidak ditemukan | Tidak terdeteksi |
| [modal](C:/web_dev/sendigs_finance/application/modules/asset/controllers/Asset.php:101) | Tidak ditemukan | Tidak terdeteksi |
| [InsertJurnal](C:/web_dev/sendigs_finance/application/modules/asset/controllers/Asset.php:123) | Tidak ditemukan | Ya |
| [saved_jurnal](C:/web_dev/sendigs_finance/application/modules/asset/controllers/Asset.php:207) | Tidak ditemukan | Ya |
| [saved](C:/web_dev/sendigs_finance/application/modules/asset/controllers/Asset.php:329) | [history](C:/web_dev/sendigs_finance/application/modules/asset/controllers/Asset.php:436) | Ya |
| [list_center](C:/web_dev/sendigs_finance/application/modules/asset/controllers/Asset.php:442) | Tidak ditemukan | Tidak terdeteksi |
| [edit](C:/web_dev/sendigs_finance/application/modules/asset/controllers/Asset.php:456) | [history](C:/web_dev/sendigs_finance/application/modules/asset/controllers/Asset.php:559) | Ya |
| [add_category](C:/web_dev/sendigs_finance/application/modules/asset/controllers/Asset.php:565) | [history](C:/web_dev/sendigs_finance/application/modules/asset/controllers/Asset.php:623) | Ya |
| [hapus_category](C:/web_dev/sendigs_finance/application/modules/asset/controllers/Asset.php:648) | [history](C:/web_dev/sendigs_finance/application/modules/asset/controllers/Asset.php:676) | Ya |

### asset_planning

Menu: Rencana Pembelian Asset; Approval Pembelian Asset Finance; Approval Pembelian Asset Management. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/asset_planning/controllers/Asset_planning.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index_rutin](C:/web_dev/sendigs_finance/application/modules/asset_planning/controllers/Asset_planning.php:25) | [history](C:/web_dev/sendigs_finance/application/modules/asset_planning/models/Budget_rutin_model.php:24) | Tidak terdeteksi |
| [server_side_rutin](C:/web_dev/sendigs_finance/application/modules/asset_planning/controllers/Asset_planning.php:30) | Tidak ditemukan | Tidak terdeteksi |
| [detail_rutin](C:/web_dev/sendigs_finance/application/modules/asset_planning/controllers/Asset_planning.php:35) | Tidak ditemukan | Tidak terdeteksi |
| [add_rutin](C:/web_dev/sendigs_finance/application/modules/asset_planning/controllers/Asset_planning.php:40) | [history](C:/web_dev/sendigs_finance/application/modules/asset_planning/models/Budget_rutin_model.php:173) | Ya |
| [get_add](C:/web_dev/sendigs_finance/application/modules/asset_planning/controllers/Asset_planning.php:45) | Tidak ditemukan | Tidak terdeteksi |
| [get_spec](C:/web_dev/sendigs_finance/application/modules/asset_planning/controllers/Asset_planning.php:50) | Tidak ditemukan | Tidak terdeteksi |
| [kompilasi](C:/web_dev/sendigs_finance/application/modules/asset_planning/controllers/Asset_planning.php:55) | [history](C:/web_dev/sendigs_finance/application/modules/asset_planning/models/Budget_rutin_model.php:54) | Tidak terdeteksi |
| [excel_kompilasi](C:/web_dev/sendigs_finance/application/modules/asset_planning/controllers/Asset_planning.php:60) | Tidak ditemukan | Tidak terdeteksi |
| [delete](C:/web_dev/sendigs_finance/application/modules/asset_planning/controllers/Asset_planning.php:67) | [history](C:/web_dev/sendigs_finance/application/modules/asset_planning/controllers/Asset_planning.php:92) | Ya |
| [index_asset](C:/web_dev/sendigs_finance/application/modules/asset_planning/controllers/Asset_planning.php:101) | [history](C:/web_dev/sendigs_finance/application/modules/asset_planning/models/Budget_asset_model.php:27) | Tidak terdeteksi |
| [index_asset_management](C:/web_dev/sendigs_finance/application/modules/asset_planning/controllers/Asset_planning.php:106) | [history](C:/web_dev/sendigs_finance/application/modules/asset_planning/models/Budget_asset_model.php:43) | Tidak terdeteksi |
| [server_side_asset](C:/web_dev/sendigs_finance/application/modules/asset_planning/controllers/Asset_planning.php:111) | Tidak ditemukan | Tidak terdeteksi |
| [server_side_asset_app_manage](C:/web_dev/sendigs_finance/application/modules/asset_planning/controllers/Asset_planning.php:116) | Tidak ditemukan | Tidak terdeteksi |
| [add_asset](C:/web_dev/sendigs_finance/application/modules/asset_planning/controllers/Asset_planning.php:121) | [history](C:/web_dev/sendigs_finance/application/modules/asset_planning/controllers/Asset_planning.php:287) | Ya |
| [add_asset_manage](C:/web_dev/sendigs_finance/application/modules/asset_planning/controllers/Asset_planning.php:334) | [history](C:/web_dev/sendigs_finance/application/modules/asset_planning/controllers/Asset_planning.php:502) | Ya |
| [get_coa](C:/web_dev/sendigs_finance/application/modules/asset_planning/controllers/Asset_planning.php:549) | Tidak ditemukan | Tidak terdeteksi |
| [hapus_asset](C:/web_dev/sendigs_finance/application/modules/asset_planning/controllers/Asset_planning.php:558) | [history](C:/web_dev/sendigs_finance/application/modules/asset_planning/models/Budget_asset_model.php:103) | Ya |

### audit_trail

Menu: Audit Trail. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/audit_trail/controllers/Audit_trail.php).

Pembacaan halaman, filter, detail dan export Audit Trail sengaja tidak menambahkan log.

### budget_coa

Menu: Master Budget; Budget Non Rutin; Budget Umum; Budget Expense. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/budget_coa/controllers/Budget_coa.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/budget_coa/controllers/Budget_coa.php:38) | Tidak ditemukan | Tidak terdeteksi |
| [create](C:/web_dev/sendigs_finance/application/modules/budget_coa/controllers/Budget_coa.php:51) | Tidak ditemukan | Tidak terdeteksi |
| [detail](C:/web_dev/sendigs_finance/application/modules/budget_coa/controllers/Budget_coa.php:64) | Tidak ditemukan | Tidak terdeteksi |
| [detail_bulan](C:/web_dev/sendigs_finance/application/modules/budget_coa/controllers/Budget_coa.php:80) | Tidak ditemukan | Tidak terdeteksi |
| [edit](C:/web_dev/sendigs_finance/application/modules/budget_coa/controllers/Budget_coa.php:101) | Tidak ditemukan | Tidak terdeteksi |
| [save_data](C:/web_dev/sendigs_finance/application/modules/budget_coa/controllers/Budget_coa.php:118) | [simpan_aktifitas](C:/web_dev/sendigs_finance/application/modules/budget_coa/controllers/Budget_coa.php:207); [simpan_aktifitas](C:/web_dev/sendigs_finance/application/modules/budget_coa/controllers/Budget_coa.php:259) | Tidak terdeteksi |
| [approve_data](C:/web_dev/sendigs_finance/application/modules/budget_coa/controllers/Budget_coa.php:268) | [simpan_aktifitas](C:/web_dev/sendigs_finance/application/modules/budget_coa/controllers/Budget_coa.php:288) | Ya |
| [hapus_data](C:/web_dev/sendigs_finance/application/modules/budget_coa/controllers/Budget_coa.php:296) | [simpan_aktifitas](C:/web_dev/sendigs_finance/application/modules/budget_coa/controllers/Budget_coa.php:317) | Ya |
| [search_old](C:/web_dev/sendigs_finance/application/modules/budget_coa/controllers/Budget_coa.php:325) | Tidak ditemukan | Tidak terdeteksi |
| [search](C:/web_dev/sendigs_finance/application/modules/budget_coa/controllers/Budget_coa.php:355) | Tidak ditemukan | Tidak terdeteksi |
| [budget_umum](C:/web_dev/sendigs_finance/application/modules/budget_coa/controllers/Budget_coa.php:392) | Tidak ditemukan | Tidak terdeteksi |
| [create_budget_umum](C:/web_dev/sendigs_finance/application/modules/budget_coa/controllers/Budget_coa.php:403) | Tidak ditemukan | Tidak terdeteksi |
| [save_data_umum](C:/web_dev/sendigs_finance/application/modules/budget_coa/controllers/Budget_coa.php:431) | [simpan_aktifitas](C:/web_dev/sendigs_finance/application/modules/budget_coa/controllers/Budget_coa.php:485) | Ya |
| [approve_data_umum](C:/web_dev/sendigs_finance/application/modules/budget_coa/controllers/Budget_coa.php:495) | Tidak ditemukan | Ya |
| [revisi_data_umum](C:/web_dev/sendigs_finance/application/modules/budget_coa/controllers/Budget_coa.php:514) | Tidak ditemukan | Ya |
| [print_budget_umum](C:/web_dev/sendigs_finance/application/modules/budget_coa/controllers/Budget_coa.php:534) | Tidak ditemukan | Tidak terdeteksi |
| [budget_expense](C:/web_dev/sendigs_finance/application/modules/budget_coa/controllers/Budget_coa.php:567) | Tidak ditemukan | Tidak terdeteksi |
| [budget_nr](C:/web_dev/sendigs_finance/application/modules/budget_coa/controllers/Budget_coa.php:580) | Tidak ditemukan | Tidak terdeteksi |
| [approve_data_category](C:/web_dev/sendigs_finance/application/modules/budget_coa/controllers/Budget_coa.php:592) | Tidak ditemukan | Ya |
| [revisi_data_category](C:/web_dev/sendigs_finance/application/modules/budget_coa/controllers/Budget_coa.php:611) | Tidak ditemukan | Ya |
| [print_budget_category](C:/web_dev/sendigs_finance/application/modules/budget_coa/controllers/Budget_coa.php:631) | Tidak ditemukan | Tidak terdeteksi |
| [create_budget_category](C:/web_dev/sendigs_finance/application/modules/budget_coa/controllers/Budget_coa.php:663) | Tidak ditemukan | Tidak terdeteksi |
| [save_data_category](C:/web_dev/sendigs_finance/application/modules/budget_coa/controllers/Budget_coa.php:684) | [simpan_aktifitas](C:/web_dev/sendigs_finance/application/modules/budget_coa/controllers/Budget_coa.php:729) | Ya |
| [proses_budget_umum](C:/web_dev/sendigs_finance/application/modules/budget_coa/controllers/Budget_coa.php:739) | Tidak ditemukan | Ya |

### budget_periodik

Menu: Budget Pembayaran Periodik. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/budget_periodik/controllers/Budget_periodik.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/budget_periodik/controllers/Budget_periodik.php:33) | Tidak ditemukan | Tidak terdeteksi |
| [create](C:/web_dev/sendigs_finance/application/modules/budget_periodik/controllers/Budget_periodik.php:46) | Tidak ditemukan | Tidak terdeteksi |
| [edit](C:/web_dev/sendigs_finance/application/modules/budget_periodik/controllers/Budget_periodik.php:63) | Tidak ditemukan | Tidak terdeteksi |
| [save_data](C:/web_dev/sendigs_finance/application/modules/budget_periodik/controllers/Budget_periodik.php:80) | [simpan_aktifitas](C:/web_dev/sendigs_finance/application/modules/budget_periodik/controllers/Budget_periodik.php:172) | Ya |
| [hapus_data](C:/web_dev/sendigs_finance/application/modules/budget_periodik/controllers/Budget_periodik.php:180) | [simpan_aktifitas](C:/web_dev/sendigs_finance/application/modules/budget_periodik/controllers/Budget_periodik.php:202) | Ya |
| [proses_budget_periodik](C:/web_dev/sendigs_finance/application/modules/budget_periodik/controllers/Budget_periodik.php:210) | [simpan_aktifitas](C:/web_dev/sendigs_finance/application/modules/budget_periodik/controllers/Budget_periodik.php:260) | Tidak terdeteksi |

### budget_rutin

Menu: Budget Stock. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/budget_rutin/controllers/Budget_rutin.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/budget_rutin/controllers/Budget_rutin.php:25) | Tidak ditemukan | Tidak terdeteksi |
| [get_cost_center](C:/web_dev/sendigs_finance/application/modules/budget_rutin/controllers/Budget_rutin.php:34) | Tidak ditemukan | Tidak terdeteksi |
| [get_material](C:/web_dev/sendigs_finance/application/modules/budget_rutin/controllers/Budget_rutin.php:41) | Tidak ditemukan | Tidak terdeteksi |
| [get_satuan](C:/web_dev/sendigs_finance/application/modules/budget_rutin/controllers/Budget_rutin.php:47) | Tidak ditemukan | Tidak terdeteksi |
| [create](C:/web_dev/sendigs_finance/application/modules/budget_rutin/controllers/Budget_rutin.php:55) | Tidak ditemukan | Tidak terdeteksi |
| [kompilasi](C:/web_dev/sendigs_finance/application/modules/budget_rutin/controllers/Budget_rutin.php:65) | Tidak ditemukan | Tidak terdeteksi |
| [edit](C:/web_dev/sendigs_finance/application/modules/budget_rutin/controllers/Budget_rutin.php:75) | Tidak ditemukan | Tidak terdeteksi |
| [save_data](C:/web_dev/sendigs_finance/application/modules/budget_rutin/controllers/Budget_rutin.php:104) | [history](C:/web_dev/sendigs_finance/application/modules/budget_rutin/controllers/Budget_rutin.php:163) | Ya |
| [download_budget_stock](C:/web_dev/sendigs_finance/application/modules/budget_rutin/controllers/Budget_rutin.php:172) | Tidak ditemukan | Tidak terdeteksi |
| [get_data](C:/web_dev/sendigs_finance/application/modules/budget_rutin/controllers/Budget_rutin.php:186) | Tidak ditemukan | Tidak terdeteksi |

### closed_po

Menu: Closed PO. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/closed_po/controllers/Closed_po.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/closed_po/controllers/Closed_po.php:35) | Tidak ditemukan | Tidak terdeteksi |
| [view_po](C:/web_dev/sendigs_finance/application/modules/closed_po/controllers/Closed_po.php:70) | Tidak ditemukan | Tidak terdeteksi |

### closed_pr

Menu: Closed PR. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/closed_pr/controllers/Closed_pr.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/closed_pr/controllers/Closed_pr.php:35) | Tidak ditemukan | Tidak terdeteksi |
| [view_barang_pr](C:/web_dev/sendigs_finance/application/modules/closed_pr/controllers/Closed_pr.php:74) | Tidak ditemukan | Tidak terdeteksi |

### coa_expense

Menu: Master COA. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/coa_expense/controllers/Coa_expense.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/coa_expense/controllers/Coa_expense.php:30) | Tidak ditemukan | Tidak terdeteksi |
| [create](C:/web_dev/sendigs_finance/application/modules/coa_expense/controllers/Coa_expense.php:40) | Tidak ditemukan | Tidak terdeteksi |
| [edit](C:/web_dev/sendigs_finance/application/modules/coa_expense/controllers/Coa_expense.php:48) | Tidak ditemukan | Tidak terdeteksi |
| [save](C:/web_dev/sendigs_finance/application/modules/coa_expense/controllers/Coa_expense.php:61) | [simpan_aktifitas](C:/web_dev/sendigs_finance/application/modules/coa_expense/controllers/Coa_expense.php:98); [simpan_aktifitas](C:/web_dev/sendigs_finance/application/modules/coa_expense/controllers/Coa_expense.php:124) | Tidak terdeteksi |
| [delete](C:/web_dev/sendigs_finance/application/modules/coa_expense/controllers/Coa_expense.php:133) | Tidak ditemukan | Tidak terdeteksi |

### costbook_stock

Menu: Costbook Stock Per Item; Costbook Stock Neraca Gudang. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/costbook_stock/controllers/Costbook_stock.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [per_item](C:/web_dev/sendigs_finance/application/modules/costbook_stock/controllers/Costbook_stock.php:29) | Tidak ditemukan | Tidak terdeteksi |
| [neraca_gudang](C:/web_dev/sendigs_finance/application/modules/costbook_stock/controllers/Costbook_stock.php:53) | Tidak ditemukan | Tidak terdeteksi |
| [change_warehouse](C:/web_dev/sendigs_finance/application/modules/costbook_stock/controllers/Costbook_stock.php:69) | Tidak ditemukan | Tidak terdeteksi |
| [view_per_item](C:/web_dev/sendigs_finance/application/modules/costbook_stock/controllers/Costbook_stock.php:96) | Tidak ditemukan | Tidak terdeteksi |
| [get_costbook_data](C:/web_dev/sendigs_finance/application/modules/costbook_stock/controllers/Costbook_stock.php:130) | Tidak ditemukan | Tidak terdeteksi |

### departements

Menu: Master Department. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/departements/controllers/Departements.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/departements/controllers/Departements.php:18) | [history](C:/web_dev/sendigs_finance/application/modules/departements/controllers/Departements.php:32) | Tidak terdeteksi |
| [add](C:/web_dev/sendigs_finance/application/modules/departements/controllers/Departements.php:37) | [history](C:/web_dev/sendigs_finance/application/modules/departements/controllers/Departements.php:51) | Ya |
| [edit](C:/web_dev/sendigs_finance/application/modules/departements/controllers/Departements.php:81) | Tidak ditemukan | Tidak terdeteksi |
| [delete](C:/web_dev/sendigs_finance/application/modules/departements/controllers/Departements.php:99) | [history](C:/web_dev/sendigs_finance/application/modules/departements/controllers/Departements.php:112) | Ya |
| [getDetail](C:/web_dev/sendigs_finance/application/modules/departements/controllers/Departements.php:117) | Tidak ditemukan | Tidak terdeteksi |

### employees

Menu: Master Employee. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/employees/controllers/Employees.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/employees/controllers/Employees.php:19) | [history](C:/web_dev/sendigs_finance/application/modules/employees/controllers/Employees.php:26) | Tidak terdeteksi |
| [add](C:/web_dev/sendigs_finance/application/modules/employees/controllers/Employees.php:31) | [history](C:/web_dev/sendigs_finance/application/modules/employees/controllers/Employees.php:93) | Ya |
| [addHisfamily](C:/web_dev/sendigs_finance/application/modules/employees/controllers/Employees.php:138) | [history](C:/web_dev/sendigs_finance/application/modules/employees/controllers/Employees.php:155) | Ya |
| [family](C:/web_dev/sendigs_finance/application/modules/employees/controllers/Employees.php:182) | [history](C:/web_dev/sendigs_finance/application/modules/employees/controllers/Employees.php:195) | Ya |
| [listfamily](C:/web_dev/sendigs_finance/application/modules/employees/controllers/Employees.php:221) | [history](C:/web_dev/sendigs_finance/application/modules/employees/controllers/Employees.php:240) | Tidak terdeteksi |
| [addfamily](C:/web_dev/sendigs_finance/application/modules/employees/controllers/Employees.php:244) | Tidak ditemukan | Tidak terdeteksi |
| [edit](C:/web_dev/sendigs_finance/application/modules/employees/controllers/Employees.php:263) | [history](C:/web_dev/sendigs_finance/application/modules/employees/controllers/Employees.php:348) | Ya |
| [view](C:/web_dev/sendigs_finance/application/modules/employees/controllers/Employees.php:396) | [history](C:/web_dev/sendigs_finance/application/modules/employees/controllers/Employees.php:445) | Tidak terdeteksi |
| [delete](C:/web_dev/sendigs_finance/application/modules/employees/controllers/Employees.php:449) | [history](C:/web_dev/sendigs_finance/application/modules/employees/controllers/Employees.php:462) | Ya |
| [getDetail](C:/web_dev/sendigs_finance/application/modules/employees/controllers/Employees.php:466) | Tidak ditemukan | Tidak terdeteksi |
| [getDept](C:/web_dev/sendigs_finance/application/modules/employees/controllers/Employees.php:472) | Tidak ditemukan | Tidak terdeteksi |
| [getTitle](C:/web_dev/sendigs_finance/application/modules/employees/controllers/Employees.php:477) | Tidak ditemukan | Tidak terdeteksi |
| [get_data_employees](C:/web_dev/sendigs_finance/application/modules/employees/controllers/Employees.php:483) | Tidak ditemukan | Tidak terdeteksi |

### expense

Menu: Expense; Approval Transportasi; Approval Kasbon Finance; Approval Kasbon Management; Approval Expense Finance; Approval Expense Management; Input Kasbon; Kasbon List; Input Transportasi; Pengajuan Transportasi; List Transportasi. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [kasbon](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:54) | Tidak ditemukan | Tidak terdeteksi |
| [kasbon_list_all](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:65) | Tidak ditemukan | Tidak terdeteksi |
| [kasbon_create](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:76) | Tidak ditemukan | Tidak terdeteksi |
| [kasbon_save](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:140) | Tidak ditemukan | Ya |
| [get_kasbon](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:571) | Tidak ditemukan | Tidak terdeteksi |
| [kasbon_edit](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:719) | Tidak ditemukan | Tidak terdeteksi |
| [kasbon_edit_manage](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:742) | Tidak ditemukan | Tidak terdeteksi |
| [kasbon_print](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:765) | Tidak ditemukan | Tidak terdeteksi |
| [periodik_print](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:858) | Tidak ditemukan | Tidak terdeteksi |
| [kasbon_view](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:896) | Tidak ditemukan | Tidak terdeteksi |
| [kasbon_fin](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:916) | Tidak ditemukan | Tidak terdeteksi |
| [kasbon_approve](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:929) | [simpan_aktifitas](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:957) | Ya |
| [kasbon_approve_manage](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:966) | [simpan_aktifitas](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:992) | Ya |
| [kasbon_delete](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:1002) | Tidak ditemukan | Ya |
| [index](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:1029) | Tidak ditemukan | Tidak terdeteksi |
| [create](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:1042) | Tidak ditemukan | Tidak terdeteksi |
| [create_report](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:1080) | Tidak ditemukan | Tidak terdeteksi |
| [edit](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:1227) | Tidak ditemukan | Tidak terdeteksi |
| [view](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:1289) | Tidak ditemukan | Tidak terdeteksi |
| [expense_print](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:1350) | Tidak ditemukan | Tidak terdeteksi |
| [expense_pettycash_print](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:1371) | Tidak ditemukan | Tidak terdeteksi |
| [list_expense_approval](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:1392) | Tidak ditemukan | Tidak terdeteksi |
| [list_expense_approval_manage](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:1400) | Tidak ditemukan | Tidak terdeteksi |
| [expense_list_all](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:1422) | Tidak ditemukan | Tidak terdeteksi |
| [approval](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:1435) | Tidak ditemukan | Tidak terdeteksi |
| [approve](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:1497) | [simpan_aktifitas](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:1593) | Ya |
| [save](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:1604) | Tidak ditemukan | Ya |
| [delete](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:2292) | Tidak ditemukan | Ya |
| [cekbudget](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:2321) | Tidak ditemukan | Tidak terdeteksi |
| [transport_req_mgt](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:2362) | Tidak ditemukan | Tidak terdeteksi |
| [transport_req_fin](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:2373) | Tidak ditemukan | Tidak terdeteksi |
| [transport_req_all](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:2380) | Tidak ditemukan | Tidak terdeteksi |
| [transport_req](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:2394) | Tidak ditemukan | Tidak terdeteksi |
| [transport_req_create](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:2401) | Tidak ditemukan | Tidak terdeteksi |
| [transport_req_save](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:2410) | Tidak ditemukan | Ya |
| [transport_req_edit](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:2694) | Tidak ditemukan | Tidak terdeteksi |
| [transport_req_print](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:2711) | Tidak ditemukan | Tidak terdeteksi |
| [transport_req_view](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:2747) | Tidak ditemukan | Tidak terdeteksi |
| [transport](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:2765) | Tidak ditemukan | Tidak terdeteksi |
| [transport_create](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:2773) | Tidak ditemukan | Tidak terdeteksi |
| [transport_save](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:2788) | Tidak ditemukan | Ya |
| [get_list_req_transport](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:2924) | Tidak ditemukan | Tidak terdeteksi |
| [get_transport](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:2949) | Tidak ditemukan | Tidak terdeteksi |
| [transport_edit](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:2957) | Tidak ditemukan | Tidak terdeteksi |
| [transport_view](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:2970) | Tidak ditemukan | Tidak terdeteksi |
| [transport_req_approve](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:2983) | [simpan_aktifitas](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:3009) | Ya |
| [transport_approve](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:3022) | [simpan_aktifitas](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:3037) | Ya |
| [transport_delete](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:3047) | Tidak ditemukan | Ya |
| [transport_req_delete](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:3061) | Tidak ditemukan | Ya |
| [petty_cash](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:3077) | Tidak ditemukan | Tidak terdeteksi |
| [create_pc](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:3102) | Tidak ditemukan | Tidak terdeteksi |
| [edit_pc](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:3134) | Tidak ditemukan | Tidak terdeteksi |
| [view_pc](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:3169) | Tidak ditemukan | Tidak terdeteksi |
| [getcoabudget](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:3184) | Tidak ditemukan | Tidak terdeteksi |
| [reject](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:3196) | Tidak ditemukan | Ya |
| [reject_manage](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:3257) | [simpan_aktifitas](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:3283) | Ya |
| [review](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:3292) | Tidak ditemukan | Tidak terdeteksi |
| [return_confirm](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:3340) | Tidak ditemukan | Ya |
| [get_pr_non_po](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:3561) | Tidak ditemukan | Tidak terdeteksi |
| [del_detail](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:3687) | Tidak ditemukan | Ya |
| [del_detail_kasbon_non_po](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:3698) | Tidak ditemukan | Ya |
| [add_kasbon_pr](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:3711) | Tidak ditemukan | Tidak terdeteksi |
| [add_ganti_expense](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:3724) | Tidak ditemukan | Ya |
| [refresh_list_kasbon_non_pr](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:3763) | Tidak ditemukan | Tidak terdeteksi |
| [refresh_list_expense_kembalian](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:3789) | Tidak ditemukan | Tidak terdeteksi |
| [del_detail_kembalian_expense](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:3846) | Tidak ditemukan | Ya |
| [copy_pr_doc](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:3859) | Tidak ditemukan | Tidak terdeteksi |
| [get_data_transport_input](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:3908) | Tidak ditemukan | Tidak terdeteksi |
| [get_data_transport_req_fin_list](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:3912) | Tidak ditemukan | Tidak terdeteksi |
| [get_data_transport_req](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:3916) | Tidak ditemukan | Tidak terdeteksi |
| [get_data_transport_req_all](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:3921) | Tidak ditemukan | Tidak terdeteksi |
| [get_coa_name](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:3926) | Tidak ditemukan | Tidak terdeteksi |
| [kasbon_fin_manage](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:3937) | Tidak ditemukan | Tidak terdeteksi |
| [get_dat_list_kasbon](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:3949) | Tidak ditemukan | Tidak terdeteksi |
| [get_dat_kasbon_list](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:4137) | Tidak ditemukan | Tidak terdeteksi |
| [get_dat_app_kasbon](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:4199) | Tidak ditemukan | Tidak terdeteksi |
| [get_dat_app_kasbon_manage](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:4355) | Tidak ditemukan | Tidak terdeteksi |
| [get_dat_expense_data](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:4511) | Tidak ditemukan | Tidak terdeteksi |
| [get_dat_expense_pc](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:4655) | Tidak ditemukan | Tidak terdeteksi |
| [get_dat_approval_expense](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:4759) | Tidak ditemukan | Tidak terdeteksi |
| [get_dat_approval_expense_manage](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:4845) | Tidak ditemukan | Tidak terdeteksi |
| [get_expense_app_finance](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:4957) | Tidak ditemukan | Tidak terdeteksi |
| [set_jurnal_expense](C:/web_dev/sendigs_finance/application/modules/expense/controllers/Expense.php:5051) | Tidak ditemukan | Tidak terdeteksi |

### expense_petty_cash

Menu: Transaksi Petty Cash; Pelaporan Petty Cash; Approval Expense Petty Cash. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/expense_petty_cash/controllers/Expense_petty_cash.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/expense_petty_cash/controllers/Expense_petty_cash.php:50) | Tidak ditemukan | Tidak terdeteksi |
| [get_data_pencatatan](C:/web_dev/sendigs_finance/application/modules/expense_petty_cash/controllers/Expense_petty_cash.php:73) | Tidak ditemukan | Tidak terdeteksi |
| [create](C:/web_dev/sendigs_finance/application/modules/expense_petty_cash/controllers/Expense_petty_cash.php:94) | Tidak ditemukan | Tidak terdeteksi |
| [edit](C:/web_dev/sendigs_finance/application/modules/expense_petty_cash/controllers/Expense_petty_cash.php:137) | Tidak ditemukan | Tidak terdeteksi |
| [view](C:/web_dev/sendigs_finance/application/modules/expense_petty_cash/controllers/Expense_petty_cash.php:190) | Tidak ditemukan | Tidak terdeteksi |
| [pelaporan](C:/web_dev/sendigs_finance/application/modules/expense_petty_cash/controllers/Expense_petty_cash.php:241) | Tidak ditemukan | Tidak terdeteksi |
| [get_data_pelaporan](C:/web_dev/sendigs_finance/application/modules/expense_petty_cash/controllers/Expense_petty_cash.php:263) | Tidak ditemukan | Tidak terdeteksi |
| [create_pelaporan](C:/web_dev/sendigs_finance/application/modules/expense_petty_cash/controllers/Expense_petty_cash.php:290) | Tidak ditemukan | Ya |
| [view_pelaporan](C:/web_dev/sendigs_finance/application/modules/expense_petty_cash/controllers/Expense_petty_cash.php:451) | Tidak ditemukan | Tidak terdeteksi |
| [confirm_pelaporan](C:/web_dev/sendigs_finance/application/modules/expense_petty_cash/controllers/Expense_petty_cash.php:508) | Tidak ditemukan | Tidak terdeteksi |
| [submit_pelaporan](C:/web_dev/sendigs_finance/application/modules/expense_petty_cash/controllers/Expense_petty_cash.php:574) | Tidak ditemukan | Ya |
| [approval](C:/web_dev/sendigs_finance/application/modules/expense_petty_cash/controllers/Expense_petty_cash.php:645) | Tidak ditemukan | Tidak terdeteksi |
| [get_data_approval](C:/web_dev/sendigs_finance/application/modules/expense_petty_cash/controllers/Expense_petty_cash.php:661) | Tidak ditemukan | Tidak terdeteksi |
| [view_approval](C:/web_dev/sendigs_finance/application/modules/expense_petty_cash/controllers/Expense_petty_cash.php:746) | Tidak ditemukan | Tidak terdeteksi |
| [approve](C:/web_dev/sendigs_finance/application/modules/expense_petty_cash/controllers/Expense_petty_cash.php:819) | Tidak ditemukan | Ya |
| [reject](C:/web_dev/sendigs_finance/application/modules/expense_petty_cash/controllers/Expense_petty_cash.php:867) | Tidak ditemukan | Ya |
| [print_pelaporan](C:/web_dev/sendigs_finance/application/modules/expense_petty_cash/controllers/Expense_petty_cash.php:946) | Tidak ditemukan | Tidak terdeteksi |
| [get_budget_info](C:/web_dev/sendigs_finance/application/modules/expense_petty_cash/controllers/Expense_petty_cash.php:1006) | Tidak ditemukan | Tidak terdeteksi |
| [get_coa_list](C:/web_dev/sendigs_finance/application/modules/expense_petty_cash/controllers/Expense_petty_cash.php:1048) | Tidak ditemukan | Tidak terdeteksi |
| [get_petty_cash_list](C:/web_dev/sendigs_finance/application/modules/expense_petty_cash/controllers/Expense_petty_cash.php:1071) | Tidak ditemukan | Tidak terdeteksi |
| [upload_evidence](C:/web_dev/sendigs_finance/application/modules/expense_petty_cash/controllers/Expense_petty_cash.php:1151) | Tidak ditemukan | Tidak terdeteksi |
| [delete](C:/web_dev/sendigs_finance/application/modules/expense_petty_cash/controllers/Expense_petty_cash.php:1245) | Tidak ditemukan | Ya |
| [save](C:/web_dev/sendigs_finance/application/modules/expense_petty_cash/controllers/Expense_petty_cash.php:1274) | Tidak ditemukan | Ya |
| [retry_journal](C:/web_dev/sendigs_finance/application/modules/expense_petty_cash/controllers/Expense_petty_cash.php:1461) | Tidak ditemukan | Ya |
| [delete_evidence](C:/web_dev/sendigs_finance/application/modules/expense_petty_cash/controllers/Expense_petty_cash.php:1507) | Tidak ditemukan | Ya |

### historical_alokasi

Menu: Historical Alokasi. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/historical_alokasi/controllers/Historical_alokasi.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/historical_alokasi/controllers/Historical_alokasi.php:14) | Tidak ditemukan | Tidak terdeteksi |
| [get_historical_alokasi](C:/web_dev/sendigs_finance/application/modules/historical_alokasi/controllers/Historical_alokasi.php:29) | Tidak ditemukan | Tidak terdeteksi |
| [get_timeline](C:/web_dev/sendigs_finance/application/modules/historical_alokasi/controllers/Historical_alokasi.php:34) | Tidak ditemukan | Tidak terdeteksi |

### incoming_asset

Menu: Incoming Asset. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/incoming_asset/controllers/Incoming_asset.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [departemen](C:/web_dev/sendigs_finance/application/modules/incoming_asset/controllers/Incoming_asset.php:13) | [history](C:/web_dev/sendigs_finance/application/modules/incoming_asset/controllers/Incoming_asset.php:42) | Tidak terdeteksi |
| [server_side_incoming](C:/web_dev/sendigs_finance/application/modules/incoming_asset/controllers/Incoming_asset.php:46) | Tidak ditemukan | Tidak terdeteksi |
| [query_data_json_incoming](C:/web_dev/sendigs_finance/application/modules/incoming_asset/controllers/Incoming_asset.php:116) | Tidak ditemukan | Tidak terdeteksi |
| [modal_incoming](C:/web_dev/sendigs_finance/application/modules/incoming_asset/controllers/Incoming_asset.php:162) | Tidak ditemukan | Tidak terdeteksi |
| [process_incoming](C:/web_dev/sendigs_finance/application/modules/incoming_asset/controllers/Incoming_asset.php:195) | [history](C:/web_dev/sendigs_finance/application/modules/incoming_asset/controllers/Incoming_asset.php:397) | Ya |
| [modal_detail](C:/web_dev/sendigs_finance/application/modules/incoming_asset/controllers/Incoming_asset.php:402) | Tidak ditemukan | Tidak terdeteksi |
| [asset](C:/web_dev/sendigs_finance/application/modules/incoming_asset/controllers/Incoming_asset.php:428) | Tidak ditemukan | Tidak terdeteksi |
| [server_side_incoming_asset](C:/web_dev/sendigs_finance/application/modules/incoming_asset/controllers/Incoming_asset.php:468) | Tidak ditemukan | Tidak terdeteksi |
| [query_data_json_incoming_asset](C:/web_dev/sendigs_finance/application/modules/incoming_asset/controllers/Incoming_asset.php:539) | Tidak ditemukan | Tidak terdeteksi |
| [modal_incoming_asset](C:/web_dev/sendigs_finance/application/modules/incoming_asset/controllers/Incoming_asset.php:581) | Tidak ditemukan | Tidak terdeteksi |
| [process_incoming_asset](C:/web_dev/sendigs_finance/application/modules/incoming_asset/controllers/Incoming_asset.php:621) | [history](C:/web_dev/sendigs_finance/application/modules/incoming_asset/controllers/Incoming_asset.php:824) | Ya |
| [print_incoming_dept](C:/web_dev/sendigs_finance/application/modules/incoming_asset/controllers/Incoming_asset.php:831) | [history](C:/web_dev/sendigs_finance/application/modules/incoming_asset/controllers/Incoming_asset.php:848) | Tidak terdeteksi |
| [print_incoming_assets](C:/web_dev/sendigs_finance/application/modules/incoming_asset/controllers/Incoming_asset.php:852) | [history](C:/web_dev/sendigs_finance/application/modules/incoming_asset/controllers/Incoming_asset.php:869) | Tidak terdeteksi |

### incoming_departemen

Menu: Incoming Departemen. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/incoming_departemen/controllers/Incoming_departemen.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/incoming_departemen/controllers/Incoming_departemen.php:30) | [history](C:/web_dev/sendigs_finance/application/modules/incoming_departemen/controllers/Incoming_departemen.php:83) | Tidak terdeteksi |
| [server_side_incoming](C:/web_dev/sendigs_finance/application/modules/incoming_departemen/controllers/Incoming_departemen.php:88) | Tidak ditemukan | Tidak terdeteksi |
| [query_data_json_incoming](C:/web_dev/sendigs_finance/application/modules/incoming_departemen/controllers/Incoming_departemen.php:158) | Tidak ditemukan | Tidak terdeteksi |
| [modal_incoming](C:/web_dev/sendigs_finance/application/modules/incoming_departemen/controllers/Incoming_departemen.php:204) | Tidak ditemukan | Tidak terdeteksi |
| [process_incoming](C:/web_dev/sendigs_finance/application/modules/incoming_departemen/controllers/Incoming_departemen.php:264) | [history](C:/web_dev/sendigs_finance/application/modules/incoming_departemen/controllers/Incoming_departemen.php:503) | Ya |
| [modal_detail](C:/web_dev/sendigs_finance/application/modules/incoming_departemen/controllers/Incoming_departemen.php:508) | Tidak ditemukan | Tidak terdeteksi |
| [asset](C:/web_dev/sendigs_finance/application/modules/incoming_departemen/controllers/Incoming_departemen.php:534) | [history](C:/web_dev/sendigs_finance/application/modules/incoming_departemen/controllers/Incoming_departemen.php:563) | Tidak terdeteksi |
| [server_side_incoming_asset](C:/web_dev/sendigs_finance/application/modules/incoming_departemen/controllers/Incoming_departemen.php:567) | Tidak ditemukan | Tidak terdeteksi |
| [query_data_json_incoming_asset](C:/web_dev/sendigs_finance/application/modules/incoming_departemen/controllers/Incoming_departemen.php:638) | Tidak ditemukan | Tidak terdeteksi |
| [modal_incoming_asset](C:/web_dev/sendigs_finance/application/modules/incoming_departemen/controllers/Incoming_departemen.php:682) | Tidak ditemukan | Tidak terdeteksi |
| [process_incoming_asset](C:/web_dev/sendigs_finance/application/modules/incoming_departemen/controllers/Incoming_departemen.php:713) | [history](C:/web_dev/sendigs_finance/application/modules/incoming_departemen/controllers/Incoming_departemen.php:889) | Ya |
| [print_incoming_dept](C:/web_dev/sendigs_finance/application/modules/incoming_departemen/controllers/Incoming_departemen.php:894) | Tidak ditemukan | Tidak terdeteksi |
| [print_incoming_assets](C:/web_dev/sendigs_finance/application/modules/incoming_departemen/controllers/Incoming_departemen.php:913) | [history](C:/web_dev/sendigs_finance/application/modules/incoming_departemen/controllers/Incoming_departemen.php:930) | Tidak terdeteksi |
| [get_list_po_depart](C:/web_dev/sendigs_finance/application/modules/incoming_departemen/controllers/Incoming_departemen.php:934) | Tidak ditemukan | Tidak terdeteksi |

### incoming_stok

Menu: Incoming Stok. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/incoming_stok/controllers/Incoming_stok.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/incoming_stok/controllers/Incoming_stok.php:35) | [history](C:/web_dev/sendigs_finance/application/modules/incoming_stok/controllers/Incoming_stok.php:40) | Tidak terdeteksi |
| [data_side_request_material](C:/web_dev/sendigs_finance/application/modules/incoming_stok/controllers/Incoming_stok.php:45) | Tidak ditemukan | Tidak terdeteksi |
| [request_stok](C:/web_dev/sendigs_finance/application/modules/incoming_stok/controllers/Incoming_stok.php:50) | [history](C:/web_dev/sendigs_finance/application/modules/incoming_stok/controllers/Incoming_stok.php:346) | Ya |
| [print_incoming_stok](C:/web_dev/sendigs_finance/application/modules/incoming_stok/controllers/Incoming_stok.php:405) | Tidak ditemukan | Tidak terdeteksi |
| [detail](C:/web_dev/sendigs_finance/application/modules/incoming_stok/controllers/Incoming_stok.php:502) | Tidak ditemukan | Tidak terdeteksi |
| [detail_purchasing_order](C:/web_dev/sendigs_finance/application/modules/incoming_stok/controllers/Incoming_stok.php:547) | Tidak ditemukan | Tidak terdeteksi |
| [pilih_supplier](C:/web_dev/sendigs_finance/application/modules/incoming_stok/controllers/Incoming_stok.php:706) | Tidak ditemukan | Tidak terdeteksi |
| [set_jurnal](C:/web_dev/sendigs_finance/application/modules/incoming_stok/controllers/Incoming_stok.php:817) | Tidak ditemukan | Tidak terdeteksi |

### invoicing

Menu: Invoicing. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/invoicing/controllers/Invoicing.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/invoicing/controllers/Invoicing.php:27) | Tidak ditemukan | Tidak terdeteksi |
| [add_invoice](C:/web_dev/sendigs_finance/application/modules/invoicing/controllers/Invoicing.php:40) | Tidak ditemukan | Tidak terdeteksi |
| [add_invoice_non_konsultasi](C:/web_dev/sendigs_finance/application/modules/invoicing/controllers/Invoicing.php:173) | Tidak ditemukan | Tidak terdeteksi |
| [add_invoice_vuca](C:/web_dev/sendigs_finance/application/modules/invoicing/controllers/Invoicing.php:184) | Tidak ditemukan | Tidak terdeteksi |
| [view_invoicing](C:/web_dev/sendigs_finance/application/modules/invoicing/controllers/Invoicing.php:305) | Tidak ditemukan | Tidak terdeteksi |
| [view_invoicing_vuca](C:/web_dev/sendigs_finance/application/modules/invoicing/controllers/Invoicing.php:428) | Tidak ditemukan | Tidak terdeteksi |
| [edit_invoicing](C:/web_dev/sendigs_finance/application/modules/invoicing/controllers/Invoicing.php:537) | Tidak ditemukan | Tidak terdeteksi |
| [edit_invoicing_vuca](C:/web_dev/sendigs_finance/application/modules/invoicing/controllers/Invoicing.php:661) | Tidak ditemukan | Tidak terdeteksi |
| [print_invoicing](C:/web_dev/sendigs_finance/application/modules/invoicing/controllers/Invoicing.php:772) | Tidak ditemukan | Tidak terdeteksi |
| [print_invoice_non_kons](C:/web_dev/sendigs_finance/application/modules/invoicing/controllers/Invoicing.php:801) | Tidak ditemukan | Tidak terdeteksi |
| [print_invoicing_vuca](C:/web_dev/sendigs_finance/application/modules/invoicing/controllers/Invoicing.php:836) | Tidak ditemukan | Tidak terdeteksi |
| [print_kwitansi](C:/web_dev/sendigs_finance/application/modules/invoicing/controllers/Invoicing.php:860) | Tidak ditemukan | Tidak terdeteksi |
| [print_kwitansi_vuca](C:/web_dev/sendigs_finance/application/modules/invoicing/controllers/Invoicing.php:877) | Tidak ditemukan | Tidak terdeteksi |
| [save_invoice](C:/web_dev/sendigs_finance/application/modules/invoicing/controllers/Invoicing.php:894) | Tidak ditemukan | Ya |
| [save_invoice_vuca](C:/web_dev/sendigs_finance/application/modules/invoicing/controllers/Invoicing.php:1031) | Tidak ditemukan | Ya |
| [update_invoice](C:/web_dev/sendigs_finance/application/modules/invoicing/controllers/Invoicing.php:1155) | Tidak ditemukan | Ya |
| [update_invoice_vuca](C:/web_dev/sendigs_finance/application/modules/invoicing/controllers/Invoicing.php:1273) | Tidak ditemukan | Ya |
| [save_keterangan_print](C:/web_dev/sendigs_finance/application/modules/invoicing/controllers/Invoicing.php:1391) | Tidak ditemukan | Ya |
| [save_keterangan_print_vuca](C:/web_dev/sendigs_finance/application/modules/invoicing/controllers/Invoicing.php:1448) | Tidak ditemukan | Ya |
| [save_keterangan_print_non_kons](C:/web_dev/sendigs_finance/application/modules/invoicing/controllers/Invoicing.php:1480) | Tidak ditemukan | Ya |
| [save_keterangan_print_kwitansi](C:/web_dev/sendigs_finance/application/modules/invoicing/controllers/Invoicing.php:1537) | Tidak ditemukan | Ya |
| [save_keterangan_print_kwitansi_vuca](C:/web_dev/sendigs_finance/application/modules/invoicing/controllers/Invoicing.php:1581) | Tidak ditemukan | Ya |
| [get_data_spk](C:/web_dev/sendigs_finance/application/modules/invoicing/controllers/Invoicing.php:1613) | Tidak ditemukan | Tidak terdeteksi |
| [download_excel](C:/web_dev/sendigs_finance/application/modules/invoicing/controllers/Invoicing.php:1618) | Tidak ditemukan | Tidak terdeteksi |
| [get_data_quotation_non_konsultasi](C:/web_dev/sendigs_finance/application/modules/invoicing/controllers/Invoicing.php:1708) | Tidak ditemukan | Tidak terdeteksi |
| [create_invoice_non_konsultasi](C:/web_dev/sendigs_finance/application/modules/invoicing/controllers/Invoicing.php:1793) | Tidak ditemukan | Tidak terdeteksi |
| [edit_invoice_non_kons](C:/web_dev/sendigs_finance/application/modules/invoicing/controllers/Invoicing.php:1826) | Tidak ditemukan | Tidak terdeteksi |
| [view_invoicing_non_kons](C:/web_dev/sendigs_finance/application/modules/invoicing/controllers/Invoicing.php:1856) | Tidak ditemukan | Tidak terdeteksi |
| [save_invoice_non_konsultasi](C:/web_dev/sendigs_finance/application/modules/invoicing/controllers/Invoicing.php:1889) | Tidak ditemukan | Ya |
| [update_invoice_non_konsultasi](C:/web_dev/sendigs_finance/application/modules/invoicing/controllers/Invoicing.php:2105) | Tidak ditemukan | Ya |
| [list_penawaran_non_kons](C:/web_dev/sendigs_finance/application/modules/invoicing/controllers/Invoicing.php:2307) | Tidak ditemukan | Tidak terdeteksi |
| [hitung_jurnal](C:/web_dev/sendigs_finance/application/modules/invoicing/controllers/Invoicing.php:2319) | Tidak ditemukan | Tidak terdeteksi |
| [change_jurnal_invoice](C:/web_dev/sendigs_finance/application/modules/invoicing/controllers/Invoicing.php:2474) | Tidak ditemukan | Tidak terdeteksi |
| [view_invoice_non_kons](C:/web_dev/sendigs_finance/application/modules/invoicing/controllers/Invoicing.php:2509) | Tidak ditemukan | Tidak terdeteksi |
| [save_close_invoice](C:/web_dev/sendigs_finance/application/modules/invoicing/controllers/Invoicing.php:2543) | Tidak ditemukan | Ya |
| [close_penawaran_non_kons](C:/web_dev/sendigs_finance/application/modules/invoicing/controllers/Invoicing.php:2589) | Tidak ditemukan | Ya |

### jurnal

Menu: Jurnal. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/jurnal/controllers/Jurnal.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/jurnal/controllers/Jurnal.php:30) | Tidak ditemukan | Tidak terdeteksi |
| [add_jurnal](C:/web_dev/sendigs_finance/application/modules/jurnal/controllers/Jurnal.php:36) | Tidak ditemukan | Tidak terdeteksi |
| [save_posting_jurnal](C:/web_dev/sendigs_finance/application/modules/jurnal/controllers/Jurnal.php:118) | Tidak ditemukan | Ya |
| [fix_company](C:/web_dev/sendigs_finance/application/modules/jurnal/controllers/Jurnal.php:345) | Tidak ditemukan | Ya |
| [get_data_jurnal](C:/web_dev/sendigs_finance/application/modules/jurnal/controllers/Jurnal.php:395) | Tidak ditemukan | Tidak terdeteksi |

### jurnal_invoicing

Menu: Jurnal Invoicing. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/jurnal_invoicing/controllers/Jurnal_invoicing.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/jurnal_invoicing/controllers/Jurnal_invoicing.php:30) | Tidak ditemukan | Tidak terdeteksi |
| [add_jurnal](C:/web_dev/sendigs_finance/application/modules/jurnal_invoicing/controllers/Jurnal_invoicing.php:48) | Tidak ditemukan | Tidak terdeteksi |
| [fix_company](C:/web_dev/sendigs_finance/application/modules/jurnal_invoicing/controllers/Jurnal_invoicing.php:131) | Tidak ditemukan | Ya |
| [modal_posting_jurnal](C:/web_dev/sendigs_finance/application/modules/jurnal_invoicing/controllers/Jurnal_invoicing.php:180) | Tidak ditemukan | Tidak terdeteksi |
| [download_excel](C:/web_dev/sendigs_finance/application/modules/jurnal_invoicing/controllers/Jurnal_invoicing.php:196) | Tidak ditemukan | Tidak terdeteksi |
| [save_posting_jurnal](C:/web_dev/sendigs_finance/application/modules/jurnal_invoicing/controllers/Jurnal_invoicing.php:207) | Tidak ditemukan | Ya |
| [update_sts_revisi_jurnal](C:/web_dev/sendigs_finance/application/modules/jurnal_invoicing/controllers/Jurnal_invoicing.php:212) | Tidak ditemukan | Ya |
| [get_data_jurnal_invoicing](C:/web_dev/sendigs_finance/application/modules/jurnal_invoicing/controllers/Jurnal_invoicing.php:217) | Tidak ditemukan | Tidak terdeteksi |

### jurnal_payment

Menu: Jurnal Payment. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/jurnal_payment/controllers/Jurnal_payment.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/jurnal_payment/controllers/Jurnal_payment.php:30) | Tidak ditemukan | Tidak terdeteksi |
| [add_jurnal](C:/web_dev/sendigs_finance/application/modules/jurnal_payment/controllers/Jurnal_payment.php:46) | Tidak ditemukan | Tidak terdeteksi |
| [save_posting_jurnal](C:/web_dev/sendigs_finance/application/modules/jurnal_payment/controllers/Jurnal_payment.php:143) | Tidak ditemukan | Ya |
| [fix_company](C:/web_dev/sendigs_finance/application/modules/jurnal_payment/controllers/Jurnal_payment.php:540) | Tidak ditemukan | Ya |
| [get_data_jurnal](C:/web_dev/sendigs_finance/application/modules/jurnal_payment/controllers/Jurnal_payment.php:590) | Tidak ditemukan | Tidak terdeteksi |
| [export_jurnal](C:/web_dev/sendigs_finance/application/modules/jurnal_payment/controllers/Jurnal_payment.php:634) | Tidak ditemukan | Tidak terdeteksi |

### jurnal_payment_petty_cash

Menu: Jurnal Payment Petty Cash. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/jurnal_payment_petty_cash/controllers/Jurnal_payment_petty_cash.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/jurnal_payment_petty_cash/controllers/Jurnal_payment_petty_cash.php:35) | Tidak ditemukan | Tidak terdeteksi |
| [get_data_jurnal](C:/web_dev/sendigs_finance/application/modules/jurnal_payment_petty_cash/controllers/Jurnal_payment_petty_cash.php:50) | Tidak ditemukan | Tidak terdeteksi |
| [get_detail_jurnal](C:/web_dev/sendigs_finance/application/modules/jurnal_payment_petty_cash/controllers/Jurnal_payment_petty_cash.php:61) | Tidak ditemukan | Tidak terdeteksi |
| [save_posting_jurnal](C:/web_dev/sendigs_finance/application/modules/jurnal_payment_petty_cash/controllers/Jurnal_payment_petty_cash.php:142) | Tidak ditemukan | Ya |
| [buku_besar](C:/web_dev/sendigs_finance/application/modules/jurnal_payment_petty_cash/controllers/Jurnal_payment_petty_cash.php:605) | Tidak ditemukan | Tidak terdeteksi |
| [get_data_buku_besar](C:/web_dev/sendigs_finance/application/modules/jurnal_payment_petty_cash/controllers/Jurnal_payment_petty_cash.php:617) | Tidak ditemukan | Tidak terdeteksi |
| [export_buku_besar](C:/web_dev/sendigs_finance/application/modules/jurnal_payment_petty_cash/controllers/Jurnal_payment_petty_cash.php:652) | Tidak ditemukan | Tidak terdeteksi |
| [revisi_jurnal](C:/web_dev/sendigs_finance/application/modules/jurnal_payment_petty_cash/controllers/Jurnal_payment_petty_cash.php:750) | Tidak ditemukan | Tidak terdeteksi |

### jurnal_penerimaan

Menu: Jurnal Penerimaan. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/jurnal_penerimaan/controllers/Jurnal_penerimaan.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/jurnal_penerimaan/controllers/Jurnal_penerimaan.php:36) | Tidak ditemukan | Tidak terdeteksi |
| [add_jurnal](C:/web_dev/sendigs_finance/application/modules/jurnal_penerimaan/controllers/Jurnal_penerimaan.php:53) | Tidak ditemukan | Tidak terdeteksi |
| [modal_posting_jurnal](C:/web_dev/sendigs_finance/application/modules/jurnal_penerimaan/controllers/Jurnal_penerimaan.php:133) | Tidak ditemukan | Tidak terdeteksi |
| [save_posting_jurnal](C:/web_dev/sendigs_finance/application/modules/jurnal_penerimaan/controllers/Jurnal_penerimaan.php:148) | Tidak ditemukan | Ya |
| [fix_company](C:/web_dev/sendigs_finance/application/modules/jurnal_penerimaan/controllers/Jurnal_penerimaan.php:246) | Tidak ditemukan | Ya |
| [download_excel](C:/web_dev/sendigs_finance/application/modules/jurnal_penerimaan/controllers/Jurnal_penerimaan.php:297) | Tidak ditemukan | Tidak terdeteksi |
| [update_sts_revisi_jurnal](C:/web_dev/sendigs_finance/application/modules/jurnal_penerimaan/controllers/Jurnal_penerimaan.php:340) | Tidak ditemukan | Ya |
| [get_data_jurnal_invoicing](C:/web_dev/sendigs_finance/application/modules/jurnal_penerimaan/controllers/Jurnal_penerimaan.php:345) | Tidak ditemukan | Tidak terdeteksi |

### master_bank

Menu: Master Bank. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/master_bank/controllers/Master_bank.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/master_bank/controllers/Master_bank.php:30) | [history](C:/web_dev/sendigs_finance/application/modules/master_bank/controllers/Master_bank.php:46) | Tidak terdeteksi |
| [add](C:/web_dev/sendigs_finance/application/modules/master_bank/controllers/Master_bank.php:53) | Tidak ditemukan | Tidak terdeteksi |
| [get_data_bank](C:/web_dev/sendigs_finance/application/modules/master_bank/controllers/Master_bank.php:58) | Tidak ditemukan | Tidak terdeteksi |
| [save_bank](C:/web_dev/sendigs_finance/application/modules/master_bank/controllers/Master_bank.php:63) | Tidak ditemukan | Ya |
| [del_bank](C:/web_dev/sendigs_finance/application/modules/master_bank/controllers/Master_bank.php:126) | Tidak ditemukan | Ya |
| [EditBank](C:/web_dev/sendigs_finance/application/modules/master_bank/controllers/Master_bank.php:154) | Tidak ditemukan | Tidak terdeteksi |

### master_customers

Menu: Master Customer. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/master_customers/controllers/Master_customers.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/master_customers/controllers/Master_customers.php:37) | Tidak ditemukan | Tidak terdeteksi |
| [viewCustomer](C:/web_dev/sendigs_finance/application/modules/master_customers/controllers/Master_customers.php:53) | Tidak ditemukan | Tidak terdeteksi |
| [editCustomer](C:/web_dev/sendigs_finance/application/modules/master_customers/controllers/Master_customers.php:78) | Tidak ditemukan | Tidak terdeteksi |
| [EditCategory](C:/web_dev/sendigs_finance/application/modules/master_customers/controllers/Master_customers.php:104) | Tidak ditemukan | Tidak terdeteksi |
| [ViewCategory](C:/web_dev/sendigs_finance/application/modules/master_customers/controllers/Master_customers.php:117) | Tidak ditemukan | Tidak terdeteksi |
| [viewInventory](C:/web_dev/sendigs_finance/application/modules/master_customers/controllers/Master_customers.php:130) | Tidak ditemukan | Tidak terdeteksi |
| [addCustomer](C:/web_dev/sendigs_finance/application/modules/master_customers/controllers/Master_customers.php:146) | Tidak ditemukan | Tidak terdeteksi |
| [addInternational](C:/web_dev/sendigs_finance/application/modules/master_customers/controllers/Master_customers.php:165) | Tidak ditemukan | Tidak terdeteksi |
| [addCategory](C:/web_dev/sendigs_finance/application/modules/master_customers/controllers/Master_customers.php:180) | Tidak ditemukan | Tidak terdeteksi |
| [delDetail](C:/web_dev/sendigs_finance/application/modules/master_customers/controllers/Master_customers.php:189) | Tidak ditemukan | Ya |
| [deleteCategory](C:/web_dev/sendigs_finance/application/modules/master_customers/controllers/Master_customers.php:220) | Tidak ditemukan | Ya |
| [deletelokal](C:/web_dev/sendigs_finance/application/modules/master_customers/controllers/Master_customers.php:249) | Tidak ditemukan | Ya |
| [deleteinternational](C:/web_dev/sendigs_finance/application/modules/master_customers/controllers/Master_customers.php:278) | Tidak ditemukan | Ya |
| [saveNewCategory](C:/web_dev/sendigs_finance/application/modules/master_customers/controllers/Master_customers.php:307) | Tidak ditemukan | Ya |
| [saveNewcustomer](C:/web_dev/sendigs_finance/application/modules/master_customers/controllers/Master_customers.php:340) | Tidak ditemukan | Ya |
| [saveEditcustomer](C:/web_dev/sendigs_finance/application/modules/master_customers/controllers/Master_customers.php:533) | Tidak ditemukan | Ya |
| [saveNewInternational](C:/web_dev/sendigs_finance/application/modules/master_customers/controllers/Master_customers.php:726) | Tidak ditemukan | Ya |
| [saveEditLocal](C:/web_dev/sendigs_finance/application/modules/master_customers/controllers/Master_customers.php:795) | Tidak ditemukan | Ya |
| [saveEditInternational](C:/web_dev/sendigs_finance/application/modules/master_customers/controllers/Master_customers.php:866) | Tidak ditemukan | Ya |
| [saveEditCategory](C:/web_dev/sendigs_finance/application/modules/master_customers/controllers/Master_customers.php:935) | Tidak ditemukan | Ya |
| [saveEditinventory](C:/web_dev/sendigs_finance/application/modules/master_customers/controllers/Master_customers.php:965) | Tidak ditemukan | Ya |
| [getkota](C:/web_dev/sendigs_finance/application/modules/master_customers/controllers/Master_customers.php:1035) | Tidak ditemukan | Tidak terdeteksi |
| [saveNewinventoryold](C:/web_dev/sendigs_finance/application/modules/master_customers/controllers/Master_customers.php:1047) | Tidak ditemukan | Ya |

### menus

Menu: Menus. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/menus/controllers/Menus.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/menus/controllers/Menus.php:33) | Tidak ditemukan | Tidak terdeteksi |
| [create](C:/web_dev/sendigs_finance/application/modules/menus/controllers/Menus.php:45) | Tidak ditemukan | Tidak terdeteksi |
| [edit](C:/web_dev/sendigs_finance/application/modules/menus/controllers/Menus.php:60) | Tidak ditemukan | Tidak terdeteksi |
| [modalAdd_Permission](C:/web_dev/sendigs_finance/application/modules/menus/controllers/Menus.php:81) | Tidak ditemukan | Tidak terdeteksi |
| [save_data_Menus](C:/web_dev/sendigs_finance/application/modules/menus/controllers/Menus.php:86) | [simpan_aktifitas](C:/web_dev/sendigs_finance/application/modules/menus/controllers/Menus.php:183); [simpan_aktifitas](C:/web_dev/sendigs_finance/application/modules/menus/controllers/Menus.php:218) | Ya |
| [hapus_Menus](C:/web_dev/sendigs_finance/application/modules/menus/controllers/Menus.php:225) | [simpan_aktifitas](C:/web_dev/sendigs_finance/application/modules/menus/controllers/Menus.php:249) | Ya |
| [print_request](C:/web_dev/sendigs_finance/application/modules/menus/controllers/Menus.php:256) | Tidak ditemukan | Tidak terdeteksi |
| [downloadExcel](C:/web_dev/sendigs_finance/application/modules/menus/controllers/Menus.php:283) | Tidak ditemukan | Tidak terdeteksi |

### metode_pembelian

Menu: Metode Pembelian. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [pr](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:28) | [history](C:/web_dev/sendigs_finance/application/modules/Metode_pembelian/models/Metode_pembelian_model.php:57) | Tidak terdeteksi |
| [server_side_progress_pr](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:34) | Tidak ditemukan | Tidak terdeteksi |
| [modal_detail_pr](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:40) | Tidak ditemukan | Tidak terdeteksi |
| [rfq](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:76) | [history](C:/web_dev/sendigs_finance/application/modules/Metode_pembelian/models/Metode_pembelian_model.php:510) | Tidak terdeteksi |
| [server_side_rfq](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:81) | Tidak ditemukan | Tidak terdeteksi |
| [add_rfq](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:86) | Tidak ditemukan | Tidak terdeteksi |
| [server_side_list_pr](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:91) | Tidak ditemukan | Tidak terdeteksi |
| [save_rfq](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:96) | Tidak ditemukan | Ya |
| [modal_detail_rfq](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:101) | Tidak ditemukan | Tidak terdeteksi |
| [modal_edit_rfq](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:106) | Tidak ditemukan | Tidak terdeteksi |
| [modal_edit_rfq_print](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:111) | [history](C:/web_dev/sendigs_finance/application/modules/Metode_pembelian/models/Metode_pembelian_model.php:1200) | Ya |
| [update_rfq](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:116) | [history](C:/web_dev/sendigs_finance/application/modules/Metode_pembelian/models/Metode_pembelian_model.php:1345) | Ya |
| [cancel_sebagian_rfq](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:121) | [history](C:/web_dev/sendigs_finance/application/modules/Metode_pembelian/models/Metode_pembelian_model.php:1402) | Ya |
| [print_rfq](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:126) | [history](C:/web_dev/sendigs_finance/application/modules/Metode_pembelian/models/Metode_pembelian_model.php:1425) | Tidak terdeteksi |
| [hapus_rfq](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:131) | [history](C:/web_dev/sendigs_finance/application/modules/Metode_pembelian/models/Metode_pembelian_model.php:1471) | Ya |
| [save_checked_rfq](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:136) | [history](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:172) | Ya |
| [changeSpec](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:177) | [history](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:208) | Ya |
| [perbandingan](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:217) | [history](C:/web_dev/sendigs_finance/application/modules/Metode_pembelian/models/Metode_pembelian_model.php:1501) | Tidak terdeteksi |
| [server_side_perbandingan](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:222) | Tidak ditemukan | Tidak terdeteksi |
| [add_perbandingan](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:227) | [history](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:290) | Ya |
| [modal_detail_perbandingan](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:307) | Tidak ditemukan | Tidak terdeteksi |
| [pengajuan_rfq](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:312) | [history](C:/web_dev/sendigs_finance/application/modules/Metode_pembelian/models/Metode_pembelian_model.php:1778) | Ya |
| [pengajuan](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:321) | [history](C:/web_dev/sendigs_finance/application/modules/Metode_pembelian/models/Metode_pembelian_model.php:1806) | Tidak terdeteksi |
| [server_side_pengajuan](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:326) | Tidak ditemukan | Tidak terdeteksi |
| [modal_detail_pengajuan](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:331) | Tidak ditemukan | Tidak terdeteksi |
| [modal_pemilihan](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:336) | [history](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:392) | Ya |
| [modal_hasil_pengajuan](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:417) | Tidak ditemukan | Tidak terdeteksi |
| [print_hasil_pemilihan](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:442) | [history](C:/web_dev/sendigs_finance/application/modules/Metode_pembelian/models/Metode_pembelian_model.php:2021) | Tidak terdeteksi |
| [approval](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:451) | [history](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:469) | Tidak terdeteksi |
| [server_side_approval](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:473) | Tidak ditemukan | Tidak terdeteksi |
| [modal_detail_approve](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:478) | Tidak ditemukan | Tidak terdeteksi |
| [modal_approve](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:483) | [history](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:535) | Ya |
| [purchase_order](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:568) | [history](C:/web_dev/sendigs_finance/application/modules/Metode_pembelian/models/Metode_pembelian_model.php:2242) | Tidak terdeteksi |
| [server_side_purchase_order](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:573) | Tidak ditemukan | Tidak terdeteksi |
| [modal_detail_purchase_order](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:578) | Tidak ditemukan | Tidak terdeteksi |
| [modal_edit_purchase_order](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:607) | [history](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:725) | Ya |
| [print_po](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:759) | [history](C:/web_dev/sendigs_finance/application/modules/Metode_pembelian/models/Metode_pembelian_model.php:2498) | Tidak terdeteksi |
| [print_po2xxx](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:764) | [history](C:/web_dev/sendigs_finance/application/modules/Metode_pembelian/models/Metode_pembelian_model.php:2518) | Tidak terdeteksi |
| [print_po2](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:769) | [history](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:785) | Tidak terdeteksi |
| [get_add](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:789) | Tidak ditemukan | Tidak terdeteksi |
| [get_kurs](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:794) | Tidak ditemukan | Tidak terdeteksi |
| [modal_po](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:799) | [history](C:/web_dev/sendigs_finance/application/modules/Metode_pembelian/models/Metode_pembelian_model.php:3621) | Ya |
| [server_side_list_rfq](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:804) | Tidak ditemukan | Tidak terdeteksi |
| [edit_po_qty](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:809) | [history](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:877) | Ya |
| [delete_sebagian_po](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:908) | [history](C:/web_dev/sendigs_finance/application/modules/Metode_pembelian/models/Metode_pembelian_model.php:2725) | Ya |
| [delete_sebagian_po_new](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:913) | [history](C:/web_dev/sendigs_finance/application/modules/Metode_pembelian/models/Metode_pembelian_model.php:2887) | Ya |
| [delete_sebagian_po_new_repeat](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:918) | [history](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:959) | Ya |
| [delete_semua_po](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:964) | [history](C:/web_dev/sendigs_finance/application/modules/Metode_pembelian/models/Metode_pembelian_model.php:3052) | Ya |
| [po_top](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:968) | Tidak ditemukan | Tidak terdeteksi |
| [save_po_top](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:972) | [history](C:/web_dev/sendigs_finance/application/modules/Metode_pembelian/models/Metode_pembelian_model.php:3993) | Ya |
| [invoice_receive](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:976) | [history](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:1000) | Tidak terdeteksi |
| [receive_invoice_save](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:1004) | [history](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:1184) | Ya |
| [invoice_receive_top](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:1189) | [history](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:1210) | Tidak terdeteksi |
| [receive_invoice_top_save](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:1214) | [history](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:1392) | Ya |
| [request_payment](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:1397) | Tidak ditemukan | Tidak terdeteksi |
| [request_payment_save](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:1437) | [history](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:1550) | Ya |
| [print_request](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:1554) | Tidak ditemukan | Tidak terdeteksi |
| [close_po](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:1562) | Tidak ditemukan | Tidak terdeteksi |
| [save_close_po](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:1580) | [history](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:1615) | Tidak terdeteksi |
| [approval_non_po](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:1629) | [history](C:/web_dev/sendigs_finance/application/modules/Metode_pembelian/models/Metode_pembelian_model.php:3088) | Tidak terdeteksi |
| [server_side_app_non_po](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:1634) | Tidak ditemukan | Tidak terdeteksi |
| [app_non_po](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:1639) | [history](C:/web_dev/sendigs_finance/application/modules/Metode_pembelian/models/Metode_pembelian_model.php:3425) | Ya |
| [get_add_non_po](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:1644) | Tidak ditemukan | Tidak terdeteksi |
| [expense_non_po](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:1649) | [history](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:1715) | Ya |
| [approval_po](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:1759) | [history](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:1776) | Tidak terdeteksi |
| [server_side_purchase_order_approve](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:1780) | Tidak ditemukan | Tidak terdeteksi |
| [modal_approval_po](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:1785) | [history](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:1843) | Ya |
| [repeat_po](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:1870) | [history](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:1886) | Tidak terdeteksi |
| [server_side_repeat_po](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:1890) | Tidak ditemukan | Tidak terdeteksi |
| [query_data_json_repeat_po](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:2048) | Tidak ditemukan | Tidak terdeteksi |
| [repeat_po_process](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:2081) | [history](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:2167) | Ya |
| [view_tracking_pr](C:/web_dev/sendigs_finance/application/modules/metode_pembelian/controllers/Metode_pembelian.php:2172) | Tidak ditemukan | Tidak terdeteksi |

### non_rutin

Menu: PR Departemen; Approval PR Department Finance; Approval PR Depart Management. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/non_rutin/controllers/Non_rutin.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/non_rutin/controllers/Non_rutin.php:58) | [history](C:/web_dev/sendigs_finance/application/modules/non_rutin/controllers/Non_rutin.php:100) | Tidak terdeteksi |
| [server_side_non_rutin](C:/web_dev/sendigs_finance/application/modules/non_rutin/controllers/Non_rutin.php:105) | Tidak ditemukan | Tidak terdeteksi |
| [server_side_non_rutin_approval_head](C:/web_dev/sendigs_finance/application/modules/non_rutin/controllers/Non_rutin.php:109) | Tidak ditemukan | Tidak terdeteksi |
| [server_side_non_rutin_approval_cost_control](C:/web_dev/sendigs_finance/application/modules/non_rutin/controllers/Non_rutin.php:113) | Tidak ditemukan | Tidak terdeteksi |
| [server_side_non_rutin_approval_management](C:/web_dev/sendigs_finance/application/modules/non_rutin/controllers/Non_rutin.php:117) | Tidak ditemukan | Tidak terdeteksi |
| [server_side_non_rutin_approval_finance](C:/web_dev/sendigs_finance/application/modules/non_rutin/controllers/Non_rutin.php:122) | Tidak ditemukan | Tidak terdeteksi |
| [add](C:/web_dev/sendigs_finance/application/modules/non_rutin/controllers/Non_rutin.php:127) | [history](C:/web_dev/sendigs_finance/application/modules/non_rutin/controllers/Non_rutin.php:628) | Ya |
| [add_finance](C:/web_dev/sendigs_finance/application/modules/non_rutin/controllers/Non_rutin.php:710) | [history](C:/web_dev/sendigs_finance/application/modules/non_rutin/controllers/Non_rutin.php:1111) | Ya |
| [get_add](C:/web_dev/sendigs_finance/application/modules/non_rutin/controllers/Non_rutin.php:1177) | Tidak ditemukan | Tidak terdeteksi |
| [approval_head](C:/web_dev/sendigs_finance/application/modules/non_rutin/controllers/Non_rutin.php:1238) | Tidak ditemukan | Tidak terdeteksi |
| [approval_cost_control](C:/web_dev/sendigs_finance/application/modules/non_rutin/controllers/Non_rutin.php:1253) | Tidak ditemukan | Tidak terdeteksi |
| [approval_management](C:/web_dev/sendigs_finance/application/modules/non_rutin/controllers/Non_rutin.php:1268) | Tidak ditemukan | Tidak terdeteksi |
| [print_pengajuan_non_rutin](C:/web_dev/sendigs_finance/application/modules/non_rutin/controllers/Non_rutin.php:1283) | [history](C:/web_dev/sendigs_finance/application/modules/non_rutin/controllers/Non_rutin.php:1313) | Tidak terdeteksi |
| [edit_detail](C:/web_dev/sendigs_finance/application/modules/non_rutin/controllers/Non_rutin.php:1343) | Tidak ditemukan | Ya |
| [search_by_depart](C:/web_dev/sendigs_finance/application/modules/non_rutin/controllers/Non_rutin.php:1373) | Tidak ditemukan | Tidak terdeteksi |
| [close_pr_modal](C:/web_dev/sendigs_finance/application/modules/non_rutin/controllers/Non_rutin.php:1537) | Tidak ditemukan | Tidak terdeteksi |
| [close_pr](C:/web_dev/sendigs_finance/application/modules/non_rutin/controllers/Non_rutin.php:1548) | Tidak ditemukan | Ya |
| [app_pr_dept_finance](C:/web_dev/sendigs_finance/application/modules/non_rutin/controllers/Non_rutin.php:1572) | Tidak ditemukan | Tidak terdeteksi |
| [get_data_non_rutin](C:/web_dev/sendigs_finance/application/modules/non_rutin/controllers/Non_rutin.php:1587) | Tidak ditemukan | Tidak terdeteksi |

### outgoing_stok_atk

Menu: Outgoing. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/outgoing_stok_atk/controllers/Outgoing_stok_atk.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/outgoing_stok_atk/controllers/Outgoing_stok_atk.php:34) | Tidak ditemukan | Tidak terdeteksi |
| [data_side_request_material](C:/web_dev/sendigs_finance/application/modules/outgoing_stok_atk/controllers/Outgoing_stok_atk.php:44) | Tidak ditemukan | Tidak terdeteksi |
| [request](C:/web_dev/sendigs_finance/application/modules/outgoing_stok_atk/controllers/Outgoing_stok_atk.php:49) | [history](C:/web_dev/sendigs_finance/application/modules/outgoing_stok_atk/controllers/Outgoing_stok_atk.php:352) | Ya |
| [server_side_request_produksi](C:/web_dev/sendigs_finance/application/modules/outgoing_stok_atk/controllers/Outgoing_stok_atk.php:383) | Tidak ditemukan | Tidak terdeteksi |
| [modal_request_edit](C:/web_dev/sendigs_finance/application/modules/outgoing_stok_atk/controllers/Outgoing_stok_atk.php:388) | [history](C:/web_dev/sendigs_finance/application/modules/outgoing_stok_atk/controllers/Outgoing_stok_atk.php:457) | Ya |
| [print_spk_request](C:/web_dev/sendigs_finance/application/modules/outgoing_stok_atk/controllers/Outgoing_stok_atk.php:492) | [history](C:/web_dev/sendigs_finance/application/modules/outgoing_stok_atk/controllers/Outgoing_stok_atk.php:524) | Tidak terdeteksi |
| [data_side_spk_material](C:/web_dev/sendigs_finance/application/modules/outgoing_stok_atk/controllers/Outgoing_stok_atk.php:537) | Tidak ditemukan | Tidak terdeteksi |
| [request_to_subgudang](C:/web_dev/sendigs_finance/application/modules/outgoing_stok_atk/controllers/Outgoing_stok_atk.php:542) | Tidak ditemukan | Ya |
| [add](C:/web_dev/sendigs_finance/application/modules/outgoing_stok_atk/controllers/Outgoing_stok_atk.php:560) | [history](C:/web_dev/sendigs_finance/application/modules/outgoing_stok_atk/controllers/Outgoing_stok_atk.php:623) | Ya |
| [print_spk](C:/web_dev/sendigs_finance/application/modules/outgoing_stok_atk/controllers/Outgoing_stok_atk.php:655) | [history](C:/web_dev/sendigs_finance/application/modules/outgoing_stok_atk/controllers/Outgoing_stok_atk.php:690) | Tidak terdeteksi |
| [kurang_stock](C:/web_dev/sendigs_finance/application/modules/outgoing_stok_atk/controllers/Outgoing_stok_atk.php:696) | Tidak ditemukan | Ya |

### pembayaran_material

Menu: Payment. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/pembayaran_material/controllers/Pembayaran_material.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/pembayaran_material/controllers/Pembayaran_material.php:28) | [history](C:/web_dev/sendigs_finance/application/modules/pembayaran_material/controllers/Pembayaran_material.php:41) | Tidak terdeteksi |
| [request_payment_save](C:/web_dev/sendigs_finance/application/modules/pembayaran_material/controllers/Pembayaran_material.php:46) | [history](C:/web_dev/sendigs_finance/application/modules/pembayaran_material/controllers/Pembayaran_material.php:124) | Ya |
| [payment_list](C:/web_dev/sendigs_finance/application/modules/pembayaran_material/controllers/Pembayaran_material.php:137) | Tidak ditemukan | Tidak terdeteksi |
| [get_data_payment_list_pr](C:/web_dev/sendigs_finance/application/modules/pembayaran_material/controllers/Pembayaran_material.php:148) | Tidak ditemukan | Tidak terdeteksi |
| [get_data_payment_list_non_pr](C:/web_dev/sendigs_finance/application/modules/pembayaran_material/controllers/Pembayaran_material.php:153) | Tidak ditemukan | Tidak terdeteksi |
| [form_payment_new](C:/web_dev/sendigs_finance/application/modules/pembayaran_material/controllers/Pembayaran_material.php:159) | Tidak ditemukan | Tidak terdeteksi |
| [save_payment_new](C:/web_dev/sendigs_finance/application/modules/pembayaran_material/controllers/Pembayaran_material.php:226) | [history](C:/web_dev/sendigs_finance/application/modules/pembayaran_material/controllers/Pembayaran_material.php:712) | Ya |
| [view_payment_new](C:/web_dev/sendigs_finance/application/modules/pembayaran_material/controllers/Pembayaran_material.php:727) | Tidak ditemukan | Tidak terdeteksi |
| [save_payment_new_nonmaterial](C:/web_dev/sendigs_finance/application/modules/pembayaran_material/controllers/Pembayaran_material.php:843) | [history](C:/web_dev/sendigs_finance/application/modules/pembayaran_material/controllers/Pembayaran_material.php:1242) | Ya |
| [list_request_payment](C:/web_dev/sendigs_finance/application/modules/pembayaran_material/controllers/Pembayaran_material.php:1258) | Tidak ditemukan | Ya |
| [check_payment](C:/web_dev/sendigs_finance/application/modules/pembayaran_material/controllers/Pembayaran_material.php:1266) | Tidak ditemukan | Ya |
| [clear_choosed_payment](C:/web_dev/sendigs_finance/application/modules/pembayaran_material/controllers/Pembayaran_material.php:1289) | Tidak ditemukan | Ya |
| [proses_payment](C:/web_dev/sendigs_finance/application/modules/pembayaran_material/controllers/Pembayaran_material.php:1310) | Tidak ditemukan | Tidak terdeteksi |
| [save_payment](C:/web_dev/sendigs_finance/application/modules/pembayaran_material/controllers/Pembayaran_material.php:1368) | Tidak ditemukan | Ya |
| [used_choosed_payment](C:/web_dev/sendigs_finance/application/modules/pembayaran_material/controllers/Pembayaran_material.php:1919) | Tidak ditemukan | Ya |
| [get_list_req_payment](C:/web_dev/sendigs_finance/application/modules/pembayaran_material/controllers/Pembayaran_material.php:1932) | Tidak ditemukan | Tidak terdeteksi |
| [set_jurnal](C:/web_dev/sendigs_finance/application/modules/pembayaran_material/controllers/Pembayaran_material.php:1937) | Tidak ditemukan | Tidak terdeteksi |
| [set_jurnal_refill](C:/web_dev/sendigs_finance/application/modules/pembayaran_material/controllers/Pembayaran_material.php:1943) | Tidak ditemukan | Tidak terdeteksi |

### penerimaan_pph_23

Menu: Penerimaan PPH 23. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/penerimaan_pph_23/controllers/Penerimaan_pph_23.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/penerimaan_pph_23/controllers/Penerimaan_pph_23.php:24) | Tidak ditemukan | Tidak terdeteksi |
| [add](C:/web_dev/sendigs_finance/application/modules/penerimaan_pph_23/controllers/Penerimaan_pph_23.php:35) | Tidak ditemukan | Tidak terdeteksi |
| [save_penerimaan_pph_23](C:/web_dev/sendigs_finance/application/modules/penerimaan_pph_23/controllers/Penerimaan_pph_23.php:113) | Tidak ditemukan | Ya |
| [get_alokasi_penerimaan_pph23](C:/web_dev/sendigs_finance/application/modules/penerimaan_pph_23/controllers/Penerimaan_pph_23.php:198) | Tidak ditemukan | Tidak terdeteksi |

### penerimaan_uang

Menu: Penerimaan Piutang. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/penerimaan_uang/controllers/Penerimaan_uang.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/penerimaan_uang/controllers/Penerimaan_uang.php:24) | Tidak ditemukan | Tidak terdeteksi |
| [add_penerimaan_uang](C:/web_dev/sendigs_finance/application/modules/penerimaan_uang/controllers/Penerimaan_uang.php:39) | Tidak ditemukan | Tidak terdeteksi |
| [get_inv_by_cust](C:/web_dev/sendigs_finance/application/modules/penerimaan_uang/controllers/Penerimaan_uang.php:78) | Tidak ditemukan | Tidak terdeteksi |
| [process_alokasi](C:/web_dev/sendigs_finance/application/modules/penerimaan_uang/controllers/Penerimaan_uang.php:122) | Tidak ditemukan | Tidak terdeteksi |
| [save_penerimaan_piutang](C:/web_dev/sendigs_finance/application/modules/penerimaan_uang/controllers/Penerimaan_uang.php:411) | Tidak ditemukan | Ya |
| [detail](C:/web_dev/sendigs_finance/application/modules/penerimaan_uang/controllers/Penerimaan_uang.php:686) | Tidak ditemukan | Tidak terdeteksi |
| [get_alokasi_penerimaan](C:/web_dev/sendigs_finance/application/modules/penerimaan_uang/controllers/Penerimaan_uang.php:728) | Tidak ditemukan | Tidak terdeteksi |
| [rollback_penerimaan](C:/web_dev/sendigs_finance/application/modules/penerimaan_uang/controllers/Penerimaan_uang.php:733) | Tidak ditemukan | Ya |

### pengajuan_rutin

Menu: Approval Pengajuan Pembayaran Rutin; Pengajuan Pembayaran Periodik. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/pengajuan_rutin/controllers/Pengajuan_rutin.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [get_user_department](C:/web_dev/sendigs_finance/application/modules/pengajuan_rutin/controllers/Pengajuan_rutin.php:39) | Tidak ditemukan | Tidak terdeteksi |
| [index](C:/web_dev/sendigs_finance/application/modules/pengajuan_rutin/controllers/Pengajuan_rutin.php:54) | Tidak ditemukan | Tidak terdeteksi |
| [app_list](C:/web_dev/sendigs_finance/application/modules/pengajuan_rutin/controllers/Pengajuan_rutin.php:90) | Tidak ditemukan | Tidak terdeteksi |
| [create](C:/web_dev/sendigs_finance/application/modules/pengajuan_rutin/controllers/Pengajuan_rutin.php:116) | Tidak ditemukan | Tidak terdeteksi |
| [edit](C:/web_dev/sendigs_finance/application/modules/pengajuan_rutin/controllers/Pengajuan_rutin.php:139) | Tidak ditemukan | Tidak terdeteksi |
| [view](C:/web_dev/sendigs_finance/application/modules/pengajuan_rutin/controllers/Pengajuan_rutin.php:165) | Tidak ditemukan | Tidak terdeteksi |
| [get_data](C:/web_dev/sendigs_finance/application/modules/pengajuan_rutin/controllers/Pengajuan_rutin.php:191) | Tidak ditemukan | Tidak terdeteksi |
| [save_data](C:/web_dev/sendigs_finance/application/modules/pengajuan_rutin/controllers/Pengajuan_rutin.php:207) | Tidak ditemukan | Ya |
| [hapus_data](C:/web_dev/sendigs_finance/application/modules/pengajuan_rutin/controllers/Pengajuan_rutin.php:428) | [simpan_aktifitas](C:/web_dev/sendigs_finance/application/modules/pengajuan_rutin/controllers/Pengajuan_rutin.php:462) | Ya |
| [approve](C:/web_dev/sendigs_finance/application/modules/pengajuan_rutin/controllers/Pengajuan_rutin.php:471) | [simpan_aktifitas](C:/web_dev/sendigs_finance/application/modules/pengajuan_rutin/controllers/Pengajuan_rutin.php:492) | Tidak terdeteksi |
| [reject](C:/web_dev/sendigs_finance/application/modules/pengajuan_rutin/controllers/Pengajuan_rutin.php:500) | [simpan_aktifitas](C:/web_dev/sendigs_finance/application/modules/pengajuan_rutin/controllers/Pengajuan_rutin.php:520) | Tidak terdeteksi |
| [get_pengajuan_periodik](C:/web_dev/sendigs_finance/application/modules/pengajuan_rutin/controllers/Pengajuan_rutin.php:529) | Tidak ditemukan | Tidak terdeteksi |

### permissions

Menu: Permissions. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/permissions/controllers/Permissions.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/permissions/controllers/Permissions.php:31) | Tidak ditemukan | Tidak terdeteksi |
| [create](C:/web_dev/sendigs_finance/application/modules/permissions/controllers/Permissions.php:43) | Tidak ditemukan | Tidak terdeteksi |
| [edit](C:/web_dev/sendigs_finance/application/modules/permissions/controllers/Permissions.php:60) | Tidak ditemukan | Tidak terdeteksi |
| [save_data_permissions](C:/web_dev/sendigs_finance/application/modules/permissions/controllers/Permissions.php:89) | [simpan_aktifitas](C:/web_dev/sendigs_finance/application/modules/permissions/controllers/Permissions.php:138); [simpan_aktifitas](C:/web_dev/sendigs_finance/application/modules/permissions/controllers/Permissions.php:177) | Tidak terdeteksi |
| [hapus_Permissions](C:/web_dev/sendigs_finance/application/modules/permissions/controllers/Permissions.php:188) | [simpan_aktifitas](C:/web_dev/sendigs_finance/application/modules/permissions/controllers/Permissions.php:218) | Tidak terdeteksi |
| [print_request](C:/web_dev/sendigs_finance/application/modules/permissions/controllers/Permissions.php:228) | Tidak ditemukan | Tidak terdeteksi |
| [downloadExcel](C:/web_dev/sendigs_finance/application/modules/permissions/controllers/Permissions.php:254) | Tidak ditemukan | Tidak terdeteksi |

### petty_cash_master

Menu: Master Petty Cash. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/petty_cash_master/controllers/Petty_cash_master.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/petty_cash_master/controllers/Petty_cash_master.php:34) | Tidak ditemukan | Tidak terdeteksi |
| [create](C:/web_dev/sendigs_finance/application/modules/petty_cash_master/controllers/Petty_cash_master.php:46) | Tidak ditemukan | Tidak terdeteksi |
| [edit](C:/web_dev/sendigs_finance/application/modules/petty_cash_master/controllers/Petty_cash_master.php:62) | Tidak ditemukan | Tidak terdeteksi |
| [view](C:/web_dev/sendigs_finance/application/modules/petty_cash_master/controllers/Petty_cash_master.php:88) | Tidak ditemukan | Tidak terdeteksi |
| [get_data](C:/web_dev/sendigs_finance/application/modules/petty_cash_master/controllers/Petty_cash_master.php:113) | Tidak ditemukan | Tidak terdeteksi |
| [save](C:/web_dev/sendigs_finance/application/modules/petty_cash_master/controllers/Petty_cash_master.php:148) | Tidak ditemukan | Ya |
| [delete](C:/web_dev/sendigs_finance/application/modules/petty_cash_master/controllers/Petty_cash_master.php:214) | Tidak ditemukan | Ya |

### petty_cash_vuca_sustain

Menu: Petty Cash Vuca Sustain. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/petty_cash_vuca_sustain/controllers/Petty_cash_vuca_sustain.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/petty_cash_vuca_sustain/controllers/Petty_cash_vuca_sustain.php:44) | Tidak ditemukan | Tidak terdeteksi |
| [get_data](C:/web_dev/sendigs_finance/application/modules/petty_cash_vuca_sustain/controllers/Petty_cash_vuca_sustain.php:64) | Tidak ditemukan | Tidak terdeteksi |
| [view](C:/web_dev/sendigs_finance/application/modules/petty_cash_vuca_sustain/controllers/Petty_cash_vuca_sustain.php:91) | Tidak ditemukan | Tidak terdeteksi |
| [print_pdf](C:/web_dev/sendigs_finance/application/modules/petty_cash_vuca_sustain/controllers/Petty_cash_vuca_sustain.php:116) | Tidak ditemukan | Tidak terdeteksi |
| [confirm_payment](C:/web_dev/sendigs_finance/application/modules/petty_cash_vuca_sustain/controllers/Petty_cash_vuca_sustain.php:150) | Tidak ditemukan | Tidak terdeteksi |
| [payment_hutang](C:/web_dev/sendigs_finance/application/modules/petty_cash_vuca_sustain/controllers/Petty_cash_vuca_sustain.php:183) | Tidak ditemukan | Ya |

### plan_tagih

Menu: Plan Tagih. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/plan_tagih/controllers/Plan_tagih.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/plan_tagih/controllers/Plan_tagih.php:27) | Tidak ditemukan | Tidak terdeteksi |
| [add_plan_tagih](C:/web_dev/sendigs_finance/application/modules/plan_tagih/controllers/Plan_tagih.php:35) | Tidak ditemukan | Tidak terdeteksi |
| [view_plan_tagih](C:/web_dev/sendigs_finance/application/modules/plan_tagih/controllers/Plan_tagih.php:61) | Tidak ditemukan | Tidak terdeteksi |
| [edit_plan_tagih](C:/web_dev/sendigs_finance/application/modules/plan_tagih/controllers/Plan_tagih.php:107) | Tidak ditemukan | Tidak terdeteksi |
| [save_plan_tagih](C:/web_dev/sendigs_finance/application/modules/plan_tagih/controllers/Plan_tagih.php:154) | Tidak ditemukan | Ya |
| [revisi_plan_tagih](C:/web_dev/sendigs_finance/application/modules/plan_tagih/controllers/Plan_tagih.php:252) | Tidak ditemukan | Ya |
| [updateee](C:/web_dev/sendigs_finance/application/modules/plan_tagih/controllers/Plan_tagih.php:310) | Tidak ditemukan | Ya |
| [get_data_spk](C:/web_dev/sendigs_finance/application/modules/plan_tagih/controllers/Plan_tagih.php:345) | Tidak ditemukan | Tidak terdeteksi |

### pr_asset

Menu: PR Asset; Approval PR Asset Management. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:40) | [history](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:51) | Tidak terdeteksi |
| [data_side](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:55) | Tidak ditemukan | Tidak terdeteksi |
| [modal_view](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:60) | [history](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:76) | Tidak terdeteksi |
| [add](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:80) | [history](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:299) | Ya |
| [edit](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:321) | Tidak ditemukan | Tidak terdeteksi |
| [view](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:357) | Tidak ditemukan | Tidak terdeteksi |
| [edited](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:395) | [history](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:433) | Ya |
| [move_asset](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:441) | [history](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:496) | Ya |
| [delete_asset](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:503) | [history](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:543) | Ya |
| [list_center](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:549) | Tidak ditemukan | Tidak terdeteksi |
| [get_jangka_waktu](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:569) | Tidak ditemukan | Tidak terdeteksi |
| [category](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:584) | [history](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:600) | Tidak terdeteksi |
| [data_side_category](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:604) | Tidak ditemukan | Tidak terdeteksi |
| [add_category](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:609) | [history](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:668) | Ya |
| [hapus_category](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:693) | [history](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:722) | Ya |
| [pr](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:731) | [history](C:/web_dev/sendigs_finance/application/modules/pr_asset/models/Pr_asset_model.php:349) | Tidak terdeteksi |
| [approval_head](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:736) | [history](C:/web_dev/sendigs_finance/application/modules/pr_asset/models/Pr_asset_model.php:361) | Tidak terdeteksi |
| [approval_cost_control](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:742) | [history](C:/web_dev/sendigs_finance/application/modules/pr_asset/models/Pr_asset_model.php:373) | Tidak terdeteksi |
| [approval_management](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:748) | [history](C:/web_dev/sendigs_finance/application/modules/pr_asset/models/Pr_asset_model.php:385) | Tidak terdeteksi |
| [server_side_pr_asset](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:754) | Tidak ditemukan | Tidak terdeteksi |
| [add_pr](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:759) | [history](C:/web_dev/sendigs_finance/application/modules/pr_asset/models/Pr_asset_model.php:460) | Ya |
| [upload_dokumen_pendukung](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:764) | Tidak ditemukan | Ya |
| [server_side_add_pr_asset](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:769) | Tidak ditemukan | Tidak terdeteksi |
| [approve_pr](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:774) | [history](C:/web_dev/sendigs_finance/application/modules/pr_asset/models/Pr_asset_model.php:1015) | Ya |
| [reset_pr_asset](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:779) | Tidak ditemukan | Ya |
| [print_pr_asset](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:784) | Tidak ditemukan | Tidak terdeteksi |
| [generate_asset_manual](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:824) | Tidak ditemukan | Ya |
| [modal_jurnal](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:875) | Tidak ditemukan | Tidak terdeteksi |
| [saved_jurnal](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:880) | Tidak ditemukan | Ya |
| [download_excel](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:1002) | Tidak ditemukan | Tidak terdeteksi |
| [download_excel_all](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:1334) | Tidak ditemukan | Tidak terdeteksi |
| [download_excel_all_default](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:1657) | Tidak ditemukan | Tidak terdeteksi |
| [saved_jurnal_erp](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:1932) | [history](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:1980) | Ya |
| [depreciation](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:1990) | [history](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:2008) | Tidak terdeteksi |
| [data_side_depreciation](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:2012) | Tidak ditemukan | Tidak terdeteksi |
| [asset_coa](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:2021) | [history](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:2037) | Tidak terdeteksi |
| [data_side_asset_coa](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:2041) | Tidak ditemukan | Tidak terdeteksi |
| [add_asset_coa](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:2046) | [history](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:2104) | Ya |
| [hapus_asset_coa](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:2131) | [history](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:2158) | Ya |
| [edit_asset](C:/web_dev/sendigs_finance/application/modules/pr_asset/controllers/Pr_asset.php:2163) | Tidak ditemukan | Ya |

### price_ref_barang_stok

Menu: Approval Price Reference. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/price_ref_barang_stok/controllers/Price_ref_barang_stok.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/price_ref_barang_stok/controllers/Price_ref_barang_stok.php:31) | [history](C:/web_dev/sendigs_finance/application/modules/price_ref_barang_stok/controllers/Price_ref_barang_stok.php:34) | Tidak terdeteksi |
| [get_data](C:/web_dev/sendigs_finance/application/modules/price_ref_barang_stok/controllers/Price_ref_barang_stok.php:39) | Tidak ditemukan | Tidak terdeteksi |
| [approval](C:/web_dev/sendigs_finance/application/modules/price_ref_barang_stok/controllers/Price_ref_barang_stok.php:45) | Tidak ditemukan | Tidak terdeteksi |
| [process_approval](C:/web_dev/sendigs_finance/application/modules/price_ref_barang_stok/controllers/Price_ref_barang_stok.php:75) | [history](C:/web_dev/sendigs_finance/application/modules/Price_ref_barang_stok/models/Price_ref_barang_stok_model.php:298) | Ya |
| [view](C:/web_dev/sendigs_finance/application/modules/price_ref_barang_stok/controllers/Price_ref_barang_stok.php:97) | Tidak ditemukan | Tidak terdeteksi |

### price_sup_barang_stok

Menu: Input Price Reference. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/price_sup_barang_stok/controllers/Price_sup_barang_stok.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/price_sup_barang_stok/controllers/Price_sup_barang_stok.php:31) | [history](C:/web_dev/sendigs_finance/application/modules/price_sup_barang_stok/controllers/Price_sup_barang_stok.php:34) | Tidak terdeteksi |
| [get_data](C:/web_dev/sendigs_finance/application/modules/price_sup_barang_stok/controllers/Price_sup_barang_stok.php:47) | Tidak ditemukan | Tidak terdeteksi |
| [add](C:/web_dev/sendigs_finance/application/modules/price_sup_barang_stok/controllers/Price_sup_barang_stok.php:53) | Tidak ditemukan | Ya |
| [edit](C:/web_dev/sendigs_finance/application/modules/price_sup_barang_stok/controllers/Price_sup_barang_stok.php:95) | Tidak ditemukan | Tidak terdeteksi |
| [view](C:/web_dev/sendigs_finance/application/modules/price_sup_barang_stok/controllers/Price_sup_barang_stok.php:135) | Tidak ditemukan | Tidak terdeteksi |
| [save_data](C:/web_dev/sendigs_finance/application/modules/price_sup_barang_stok/controllers/Price_sup_barang_stok.php:150) | [history](C:/web_dev/sendigs_finance/application/modules/price_sup_barang_stok/controllers/Price_sup_barang_stok.php:306) | Ya |
| [delete_data](C:/web_dev/sendigs_finance/application/modules/price_sup_barang_stok/controllers/Price_sup_barang_stok.php:315) | [history](C:/web_dev/sendigs_finance/application/modules/price_sup_barang_stok/controllers/Price_sup_barang_stok.php:347) | Ya |
| [history](C:/web_dev/sendigs_finance/application/modules/price_sup_barang_stok/controllers/Price_sup_barang_stok.php:352) | Tidak ditemukan | Tidak terdeteksi |
| [get_history_data](C:/web_dev/sendigs_finance/application/modules/price_sup_barang_stok/controllers/Price_sup_barang_stok.php:366) | Tidak ditemukan | Tidak terdeteksi |
| [get_evidence_modal](C:/web_dev/sendigs_finance/application/modules/price_sup_barang_stok/controllers/Price_sup_barang_stok.php:374) | Tidak ditemukan | Tidak terdeteksi |

### price_sup_raw_material

Menu: Raw Material. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/price_sup_raw_material/controllers/Price_sup_raw_material.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/price_sup_raw_material/controllers/Price_sup_raw_material.php:30) | [history](C:/web_dev/sendigs_finance/application/modules/price_sup_raw_material/controllers/Price_sup_raw_material.php:46) | Tidak terdeteksi |
| [add](C:/web_dev/sendigs_finance/application/modules/price_sup_raw_material/controllers/Price_sup_raw_material.php:52) | [history](C:/web_dev/sendigs_finance/application/modules/price_sup_raw_material/controllers/Price_sup_raw_material.php:129) | Ya |
| [update_kurs](C:/web_dev/sendigs_finance/application/modules/price_sup_raw_material/controllers/Price_sup_raw_material.php:144) | [history](C:/web_dev/sendigs_finance/application/modules/price_sup_raw_material/controllers/Price_sup_raw_material.php:178) | Ya |
| [excel_report](C:/web_dev/sendigs_finance/application/modules/price_sup_raw_material/controllers/Price_sup_raw_material.php:184) | Tidak ditemukan | Tidak terdeteksi |

### purchase_order

Menu: Purchase Order. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:39) | Tidak ditemukan | Tidak terdeteksi |
| [add](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:109) | Tidak ditemukan | Tidak terdeteksi |
| [edit](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:134) | Tidak ditemukan | Tidak terdeteksi |
| [Lihat](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:354) | Tidak ditemukan | Tidak terdeteksi |
| [add_pr](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:386) | Tidak ditemukan | Tidak terdeteksi |
| [CariKurs](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:405) | Tidak ditemukan | Tidak terdeteksi |
| [PrintHeader1](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:449) | Tidak ditemukan | Tidak terdeteksi |
| [PrintHeader](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:457) | Tidak ditemukan | Tidak terdeteksi |
| [EditHeader](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:475) | Tidak ditemukan | Tidak terdeteksi |
| [detail](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:496) | Tidak ditemukan | Tidak terdeteksi |
| [editPenawaran](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:514) | Tidak ditemukan | Tidak terdeteksi |
| [View](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:532) | Tidak ditemukan | Tidak terdeteksi |
| [viewPenawaran](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:565) | Tidak ditemukan | Tidak terdeteksi |
| [viewBentuk](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:581) | Tidak ditemukan | Tidak terdeteksi |
| [addPenawaran](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:596) | Tidak ditemukan | Tidak terdeteksi |
| [AddMaterial](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:612) | Tidak ditemukan | Tidak terdeteksi |
| [AddMaterial_Direct](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:707) | Tidak ditemukan | Tidak terdeteksi |
| [LockMatrial](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:775) | Tidak ditemukan | Tidak terdeteksi |
| [CariPrice](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:826) | Tidak ditemukan | Tidak terdeteksi |
| [CariPPN](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:905) | Tidak ditemukan | Tidak terdeteksi |
| [HitungHarga](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:914) | Tidak ditemukan | Tidak terdeteksi |
| [UbahImport](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:927) | Tidak ditemukan | Tidak terdeteksi |
| [TotalWeight](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:932) | Tidak ditemukan | Tidak terdeteksi |
| [CariIdMaterial](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:940) | Tidak ditemukan | Tidak terdeteksi |
| [HitungUP](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:948) | Tidak ditemukan | Tidak terdeteksi |
| [Hitjumlah](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:968) | Tidak ditemukan | Tidak terdeteksi |
| [CariNamaMaterial](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:1002) | Tidak ditemukan | Tidak terdeteksi |
| [CariDescripitionMaterial](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:1010) | Tidak ditemukan | Tidak terdeteksi |
| [CariPanjangMaterial](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:1018) | Tidak ditemukan | Tidak terdeteksi |
| [CariLebarMaterial](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:1026) | Tidak ditemukan | Tidak terdeteksi |
| [FormInputKurs](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:1034) | Tidak ditemukan | Tidak terdeteksi |
| [CariQtyMaterial](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:1083) | Tidak ditemukan | Tidak terdeteksi |
| [CariweightMaterial](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:1091) | Tidak ditemukan | Tidak terdeteksi |
| [CariTweightMaterial](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:1099) | Tidak ditemukan | Tidak terdeteksi |
| [CariWidthMaterial](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:1107) | Tidak ditemukan | Tidak terdeteksi |
| [CariIdBentuk](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:1115) | Tidak ditemukan | Tidak terdeteksi |
| [CariSupplier](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:1123) | Tidak ditemukan | Tidak terdeteksi |
| [CariTHarga](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:1135) | Tidak ditemukan | Tidak terdeteksi |
| [CariTDiskon](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:1142) | Tidak ditemukan | Tidak terdeteksi |
| [CariTPajak](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:1151) | Tidak ditemukan | Tidak terdeteksi |
| [CariTSum](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:1160) | Tidak ditemukan | Tidak terdeteksi |
| [CariMinHarga](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:1176) | Tidak ditemukan | Tidak terdeteksi |
| [CariMinDiskon](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:1183) | Tidak ditemukan | Tidak terdeteksi |
| [CariMinPajak](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:1192) | Tidak ditemukan | Tidak terdeteksi |
| [CariMinSum](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:1201) | Tidak ditemukan | Tidak terdeteksi |
| [HitungTwight](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:1217) | Tidak ditemukan | Tidak terdeteksi |
| [getsales](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:1225) | Tidak ditemukan | Tidak terdeteksi |
| [getpic](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:1239) | Tidak ditemukan | Tidak terdeteksi |
| [cari_thickness](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:1251) | Tidak ditemukan | Tidak terdeteksi |
| [cari_density](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:1258) | Tidak ditemukan | Tidak terdeteksi |
| [hitung_komisi](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:1265) | Tidak ditemukan | Tidak terdeteksi |
| [carimsprofit](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:1273) | Tidak ditemukan | Tidak terdeteksi |
| [cari_inven1](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:1294) | Tidak ditemukan | Tidak terdeteksi |
| [delDetail](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:1301) | Tidak ditemukan | Ya |
| [Approved](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:1332) | Tidak ditemukan | Ya |
| [get_inven2](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:1359) | Tidak ditemukan | Tidak terdeteksi |
| [get_inven3](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:1371) | Tidak ditemukan | Tidak terdeteksi |
| [saveNewPenawaran](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:1386) | Tidak ditemukan | Ya |
| [SaveNew](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:1467) | Tidak ditemukan | Ya |
| [SaveEditPO](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:1784) | Tidak ditemukan | Ya |
| [SaveEdit](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:1934) | Tidak ditemukan | Ya |
| [PrintH](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:2009) | Tidak ditemukan | Tidak terdeteksi |
| [PrintH2](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:2029) | Tidak ditemukan | Tidak terdeteksi |
| [PrintH3](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:2225) | Tidak ditemukan | Tidak terdeteksi |
| [SaveEditHeader](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:2236) | Tidak ditemukan | Ya |
| [saveEditPenawaran](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:2281) | Tidak ditemukan | Ya |
| [deletePenawaran](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:2357) | Tidak ditemukan | Ya |
| [saveEditInventory](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:2381) | Tidak ditemukan | Ya |
| [get_compotition_new](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:2490) | Tidak ditemukan | Tidak terdeteksi |
| [get_dimensi](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:2513) | Tidak ditemukan | Tidak terdeteksi |
| [get_compotition_old](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:2535) | Tidak ditemukan | Tidak terdeteksi |
| [get_dimensi_old](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:2558) | Tidak ditemukan | Tidak terdeteksi |
| [saveEditInventorylama](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:2580) | Tidak ditemukan | Ya |
| [saveEditInventoryOld](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:2683) | Tidak ditemukan | Ya |
| [get_compotition](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:2786) | Tidak ditemukan | Tidak terdeteksi |
| [CariLokasi](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:2810) | Tidak ditemukan | Tidak terdeteksi |
| [getDateExp](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:2826) | Tidak ditemukan | Tidak terdeteksi |
| [getPR](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:2852) | Tidak ditemukan | Tidak terdeteksi |
| [addPurchaseOrder](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:2900) | Tidak ditemukan | Tidak terdeteksi |
| [proses](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:2923) | Tidak ditemukan | Tidak terdeteksi |
| [po_input_notes](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:3075) | Tidak ditemukan | Ya |
| [add_checked_pr](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:3094) | Tidak ditemukan | Ya |
| [del_checked_pr](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:3120) | Tidak ditemukan | Ya |
| [clear_checked_pr](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:3135) | Tidak ditemukan | Ya |
| [process_do](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:3157) | Tidak ditemukan | Tidak terdeteksi |
| [view_po](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:3189) | Tidak ditemukan | Tidak terdeteksi |
| [add_top_po](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:3428) | Tidak ditemukan | Tidak terdeteksi |
| [close_po_modal](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:3442) | Tidak ditemukan | Tidak terdeteksi |
| [close_po](C:/web_dev/sendigs_finance/application/modules/purchase_order/controllers/Purchase_order.php:3453) | Tidak ditemukan | Ya |

### purchase_order_payment

Menu: Receive Invoice. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/purchase_order_payment/controllers/Purchase_order_payment.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/purchase_order_payment/controllers/Purchase_order_payment.php:48) | Tidak ditemukan | Tidak terdeteksi |
| [checkbx](C:/web_dev/sendigs_finance/application/modules/purchase_order_payment/controllers/Purchase_order_payment.php:65) | Tidak ditemukan | Tidak terdeteksi |
| [req_app](C:/web_dev/sendigs_finance/application/modules/purchase_order_payment/controllers/Purchase_order_payment.php:145) | Tidak ditemukan | Tidak terdeteksi |
| [req_inc_app](C:/web_dev/sendigs_finance/application/modules/purchase_order_payment/controllers/Purchase_order_payment.php:664) | Tidak ditemukan | Tidak terdeteksi |
| [view](C:/web_dev/sendigs_finance/application/modules/purchase_order_payment/controllers/Purchase_order_payment.php:774) | Tidak ditemukan | Tidak terdeteksi |
| [view_inc](C:/web_dev/sendigs_finance/application/modules/purchase_order_payment/controllers/Purchase_order_payment.php:800) | Tidak ditemukan | Tidak terdeteksi |
| [save_invoice](C:/web_dev/sendigs_finance/application/modules/purchase_order_payment/controllers/Purchase_order_payment.php:874) | Tidak ditemukan | Ya |
| [search_inc](C:/web_dev/sendigs_finance/application/modules/purchase_order_payment/controllers/Purchase_order_payment.php:1292) | Tidak ditemukan | Tidak terdeteksi |
| [search_dp](C:/web_dev/sendigs_finance/application/modules/purchase_order_payment/controllers/Purchase_order_payment.php:1436) | Tidak ditemukan | Tidak terdeteksi |
| [search_pro](C:/web_dev/sendigs_finance/application/modules/purchase_order_payment/controllers/Purchase_order_payment.php:1604) | Tidak ditemukan | Tidak terdeteksi |
| [search_ret](C:/web_dev/sendigs_finance/application/modules/purchase_order_payment/controllers/Purchase_order_payment.php:1771) | Tidak ditemukan | Tidak terdeteksi |
| [check_list_inc](C:/web_dev/sendigs_finance/application/modules/purchase_order_payment/controllers/Purchase_order_payment.php:1938) | Tidak ditemukan | Tidak terdeteksi |
| [check_invoice](C:/web_dev/sendigs_finance/application/modules/purchase_order_payment/controllers/Purchase_order_payment.php:2023) | Tidak ditemukan | Ya |
| [clear_checked_invoice](C:/web_dev/sendigs_finance/application/modules/purchase_order_payment/controllers/Purchase_order_payment.php:2059) | Tidak ditemukan | Ya |
| [checkCheckedInv](C:/web_dev/sendigs_finance/application/modules/purchase_order_payment/controllers/Purchase_order_payment.php:2077) | Tidak ditemukan | Tidak terdeteksi |
| [rec_invoice_btn](C:/web_dev/sendigs_finance/application/modules/purchase_order_payment/controllers/Purchase_order_payment.php:2084) | Tidak ditemukan | Tidak terdeteksi |

### purchase_request

Menu: List Outstanding PO. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:35) | Tidak ditemukan | Tidak terdeteksi |
| [add](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:247) | Tidak ditemukan | Tidak terdeteksi |
| [edit](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:266) | Tidak ditemukan | Tidak terdeteksi |
| [add_pr](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:290) | Tidak ditemukan | Tidak terdeteksi |
| [PrintHeader1](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:309) | Tidak ditemukan | Tidak terdeteksi |
| [PrintHeader](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:317) | Tidak ditemukan | Tidak terdeteksi |
| [EditHeader](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:335) | Tidak ditemukan | Tidak terdeteksi |
| [detail](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:356) | Tidak ditemukan | Tidak terdeteksi |
| [editPenawaran](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:374) | Tidak ditemukan | Tidak terdeteksi |
| [View](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:392) | Tidak ditemukan | Tidak terdeteksi |
| [view_asset](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:421) | Tidak ditemukan | Tidak terdeteksi |
| [View_depart](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:450) | Tidak ditemukan | Tidak terdeteksi |
| [viewPenawaran](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:481) | Tidak ditemukan | Tidak terdeteksi |
| [viewBentuk](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:497) | Tidak ditemukan | Tidak terdeteksi |
| [addPenawaran](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:512) | Tidak ditemukan | Tidak terdeteksi |
| [AddMaterial](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:528) | Tidak ditemukan | Tidak terdeteksi |
| [CariBentuk](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:568) | Tidak ditemukan | Tidak terdeteksi |
| [CariIdBentuk](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:578) | Tidak ditemukan | Tidak terdeteksi |
| [CariSupplier](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:586) | Tidak ditemukan | Tidak terdeteksi |
| [getemail](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:598) | Tidak ditemukan | Tidak terdeteksi |
| [HitungTwight](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:605) | Tidak ditemukan | Tidak terdeteksi |
| [getsales](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:613) | Tidak ditemukan | Tidak terdeteksi |
| [getpic](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:627) | Tidak ditemukan | Tidak terdeteksi |
| [cari_thickness](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:639) | Tidak ditemukan | Tidak terdeteksi |
| [cari_density](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:646) | Tidak ditemukan | Tidak terdeteksi |
| [hitung_komisi](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:653) | Tidak ditemukan | Tidak terdeteksi |
| [CariKodeproduk](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:662) | Tidak ditemukan | Tidak terdeteksi |
| [carimsprofit](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:670) | Tidak ditemukan | Tidak terdeteksi |
| [cari_inven1](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:691) | Tidak ditemukan | Tidak terdeteksi |
| [delDetail](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:698) | Tidak ditemukan | Ya |
| [Approved](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:729) | Tidak ditemukan | Ya |
| [get_inven2](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:756) | Tidak ditemukan | Tidak terdeteksi |
| [get_inven3](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:768) | Tidak ditemukan | Tidak terdeteksi |
| [saveNewPenawaran](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:783) | Tidak ditemukan | Ya |
| [SaveNew](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:864) | Tidak ditemukan | Ya |
| [SaveEdit](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:933) | Tidak ditemukan | Ya |
| [SaveEditHeader](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:999) | Tidak ditemukan | Ya |
| [saveEditPenawaran](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:1044) | Tidak ditemukan | Ya |
| [deletePenawaran](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:1120) | Tidak ditemukan | Ya |
| [saveEditInventory](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:1144) | Tidak ditemukan | Ya |
| [get_compotition_new](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:1253) | Tidak ditemukan | Tidak terdeteksi |
| [get_dimensi](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:1276) | Tidak ditemukan | Tidak terdeteksi |
| [get_compotition_old](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:1298) | Tidak ditemukan | Tidak terdeteksi |
| [get_dimensi_old](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:1321) | Tidak ditemukan | Tidak terdeteksi |
| [saveEditInventorylama](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:1343) | Tidak ditemukan | Ya |
| [saveEditInventoryOld](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:1446) | Tidak ditemukan | Ya |
| [get_compotition](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:1549) | Tidak ditemukan | Tidak terdeteksi |
| [index_approval](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:1574) | Tidak ditemukan | Tidak terdeteksi |
| [update_dtpr](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:1595) | Tidak ditemukan | Tidak terdeteksi |
| [request_approval](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:1607) | Tidak ditemukan | Ya |
| [approval](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:1635) | Tidak ditemukan | Ya |
| [PrintH2](C:/web_dev/sendigs_finance/application/modules/purchase_request/controllers/Purchase_request.php:1659) | Tidak ditemukan | Tidak terdeteksi |

### record_request_payment

Menu: Record Request Payment. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/record_request_payment/controllers/Record_request_payment.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/record_request_payment/controllers/Record_request_payment.php:22) | Tidak ditemukan | Tidak terdeteksi |
| [get_data_record](C:/web_dev/sendigs_finance/application/modules/record_request_payment/controllers/Record_request_payment.php:29) | Tidak ditemukan | Tidak terdeteksi |
| [get_data_record_legacy](C:/web_dev/sendigs_finance/application/modules/record_request_payment/controllers/Record_request_payment.php:34) | Tidak ditemukan | Tidak terdeteksi |
| [detail](C:/web_dev/sendigs_finance/application/modules/record_request_payment/controllers/Record_request_payment.php:39) | Tidak ditemukan | Tidak terdeteksi |
| [export_excel](C:/web_dev/sendigs_finance/application/modules/record_request_payment/controllers/Record_request_payment.php:70) | Tidak ditemukan | Tidak terdeteksi |

### report_actual_plan_tagih

Menu: Report Actual Plan Tagih. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/report_actual_plan_tagih/controllers/Report_actual_plan_tagih.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/report_actual_plan_tagih/controllers/Report_actual_plan_tagih.php:152) | Tidak ditemukan | Tidak terdeteksi |
| [download_excel](C:/web_dev/sendigs_finance/application/modules/report_actual_plan_tagih/controllers/Report_actual_plan_tagih.php:168) | Tidak ditemukan | Tidak terdeteksi |
| [get_data_report_apt](C:/web_dev/sendigs_finance/application/modules/report_actual_plan_tagih/controllers/Report_actual_plan_tagih.php:347) | Tidak ditemukan | Tidak terdeteksi |
| [get_data_report_apt_backup](C:/web_dev/sendigs_finance/application/modules/report_actual_plan_tagih/controllers/Report_actual_plan_tagih.php:555) | Tidak ditemukan | Tidak terdeteksi |
| [get_data_report_macet](C:/web_dev/sendigs_finance/application/modules/report_actual_plan_tagih/controllers/Report_actual_plan_tagih.php:933) | Tidak ditemukan | Tidak terdeteksi |
| [get_detail_termin_macet](C:/web_dev/sendigs_finance/application/modules/report_actual_plan_tagih/controllers/Report_actual_plan_tagih.php:1112) | Tidak ditemukan | Tidak terdeteksi |
| [download_excel_macet](C:/web_dev/sendigs_finance/application/modules/report_actual_plan_tagih/controllers/Report_actual_plan_tagih.php:1170) | Tidak ditemukan | Tidak terdeteksi |

### report_jurnal_invoicing

Menu: Report Jurnal Invoicing. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/report_jurnal_invoicing/controllers/Report_jurnal_invoicing.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/report_jurnal_invoicing/controllers/Report_jurnal_invoicing.php:34) | Tidak ditemukan | Tidak terdeteksi |
| [view_jurnal](C:/web_dev/sendigs_finance/application/modules/report_jurnal_invoicing/controllers/Report_jurnal_invoicing.php:70) | Tidak ditemukan | Tidak terdeteksi |
| [export_excel](C:/web_dev/sendigs_finance/application/modules/report_jurnal_invoicing/controllers/Report_jurnal_invoicing.php:86) | Tidak ditemukan | Tidak terdeteksi |
| [get_data_jurnal_invoicing](C:/web_dev/sendigs_finance/application/modules/report_jurnal_invoicing/controllers/Report_jurnal_invoicing.php:105) | Tidak ditemukan | Tidak terdeteksi |

### report_jurnal_payment

Menu: Report Jurnal Payment. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/report_jurnal_payment/controllers/Report_jurnal_payment.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/report_jurnal_payment/controllers/Report_jurnal_payment.php:29) | Tidak ditemukan | Tidak terdeteksi |
| [get_data_jurnal](C:/web_dev/sendigs_finance/application/modules/report_jurnal_payment/controllers/Report_jurnal_payment.php:46) | Tidak ditemukan | Tidak terdeteksi |
| [view_jurnal](C:/web_dev/sendigs_finance/application/modules/report_jurnal_payment/controllers/Report_jurnal_payment.php:51) | Tidak ditemukan | Tidak terdeteksi |
| [export_excel](C:/web_dev/sendigs_finance/application/modules/report_jurnal_payment/controllers/Report_jurnal_payment.php:69) | Tidak ditemukan | Tidak terdeteksi |
| [export_jurnal](C:/web_dev/sendigs_finance/application/modules/report_jurnal_payment/controllers/Report_jurnal_payment.php:107) | Tidak ditemukan | Tidak terdeteksi |

### report_jurnal_penerimaan

Menu: Report Jurnal Penerimaan. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/report_jurnal_penerimaan/controllers/Report_jurnal_penerimaan.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/report_jurnal_penerimaan/controllers/Report_jurnal_penerimaan.php:34) | Tidak ditemukan | Tidak terdeteksi |
| [view_jurnal](C:/web_dev/sendigs_finance/application/modules/report_jurnal_penerimaan/controllers/Report_jurnal_penerimaan.php:67) | Tidak ditemukan | Tidak terdeteksi |
| [export_excel](C:/web_dev/sendigs_finance/application/modules/report_jurnal_penerimaan/controllers/Report_jurnal_penerimaan.php:83) | Tidak ditemukan | Tidak terdeteksi |
| [get_data_jurnal_penerimaan](C:/web_dev/sendigs_finance/application/modules/report_jurnal_penerimaan/controllers/Report_jurnal_penerimaan.php:102) | Tidak ditemukan | Tidak terdeteksi |

### report_petty_cash

Menu: Report Petty Cash. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/report_petty_cash/controllers/Report_petty_cash.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/report_petty_cash/controllers/Report_petty_cash.php:19) | Tidak ditemukan | Tidak terdeteksi |
| [get_data](C:/web_dev/sendigs_finance/application/modules/report_petty_cash/controllers/Report_petty_cash.php:31) | Tidak ditemukan | Tidak terdeteksi |
| [export_excel](C:/web_dev/sendigs_finance/application/modules/report_petty_cash/controllers/Report_petty_cash.php:96) | Tidak ditemukan | Tidak terdeteksi |

### report_piutang_per_invoice

Menu: Report Uninvoiced dan Piutang Per Invoice. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/report_piutang_per_invoice/controllers/Report_piutang_per_invoice.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/report_piutang_per_invoice/controllers/Report_piutang_per_invoice.php:34) | Tidak ditemukan | Tidak terdeteksi |
| [get_report_data](C:/web_dev/sendigs_finance/application/modules/report_piutang_per_invoice/controllers/Report_piutang_per_invoice.php:42) | Tidak ditemukan | Tidak terdeteksi |
| [download_excel](C:/web_dev/sendigs_finance/application/modules/report_piutang_per_invoice/controllers/Report_piutang_per_invoice.php:350) | Tidak ditemukan | Tidak terdeteksi |

### request_mutasi

Menu: Approval Mutasi; Request Mutasi; List Mutasi; Transaksi Bank. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/request_mutasi/controllers/Request_mutasi.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/request_mutasi/controllers/Request_mutasi.php:47) | Tidak ditemukan | Tidak terdeteksi |
| [create](C:/web_dev/sendigs_finance/application/modules/request_mutasi/controllers/Request_mutasi.php:55) | Tidak ditemukan | Tidak terdeteksi |
| [save](C:/web_dev/sendigs_finance/application/modules/request_mutasi/controllers/Request_mutasi.php:81) | Tidak ditemukan | Ya |
| [mutasi](C:/web_dev/sendigs_finance/application/modules/request_mutasi/controllers/Request_mutasi.php:161) | Tidak ditemukan | Tidak terdeteksi |
| [approval_mutasi](C:/web_dev/sendigs_finance/application/modules/request_mutasi/controllers/Request_mutasi.php:169) | Tidak ditemukan | Tidak terdeteksi |
| [add_mutasi](C:/web_dev/sendigs_finance/application/modules/request_mutasi/controllers/Request_mutasi.php:178) | Tidak ditemukan | Tidak terdeteksi |
| [save_mutasi](C:/web_dev/sendigs_finance/application/modules/request_mutasi/controllers/Request_mutasi.php:206) | Tidak ditemukan | Ya |
| [get_coa_by_target](C:/web_dev/sendigs_finance/application/modules/request_mutasi/controllers/Request_mutasi.php:332) | Tidak ditemukan | Tidak terdeteksi |
| [approval](C:/web_dev/sendigs_finance/application/modules/request_mutasi/controllers/Request_mutasi.php:438) | Tidak ditemukan | Tidak terdeteksi |
| [save_approval](C:/web_dev/sendigs_finance/application/modules/request_mutasi/controllers/Request_mutasi.php:447) | Tidak ditemukan | Ya |
| [reject_mutasi](C:/web_dev/sendigs_finance/application/modules/request_mutasi/controllers/Request_mutasi.php:477) | Tidak ditemukan | Ya |
| [index_transaksi](C:/web_dev/sendigs_finance/application/modules/request_mutasi/controllers/Request_mutasi.php:506) | Tidak ditemukan | Tidak terdeteksi |
| [create_transaksi](C:/web_dev/sendigs_finance/application/modules/request_mutasi/controllers/Request_mutasi.php:514) | Tidak ditemukan | Tidak terdeteksi |
| [save_transaksi](C:/web_dev/sendigs_finance/application/modules/request_mutasi/controllers/Request_mutasi.php:540) | Tidak ditemukan | Ya |
| [save_jurnal_BUM](C:/web_dev/sendigs_finance/application/modules/request_mutasi/controllers/Request_mutasi.php:646) | Tidak ditemukan | Ya |
| [save_jurnal_BUK](C:/web_dev/sendigs_finance/application/modules/request_mutasi/controllers/Request_mutasi.php:719) | Tidak ditemukan | Ya |
| [printout](C:/web_dev/sendigs_finance/application/modules/request_mutasi/controllers/Request_mutasi.php:792) | Tidak ditemukan | Tidak terdeteksi |
| [printout_mutasi](C:/web_dev/sendigs_finance/application/modules/request_mutasi/controllers/Request_mutasi.php:804) | Tidak ditemukan | Tidak terdeteksi |
| [printout_transaksi](C:/web_dev/sendigs_finance/application/modules/request_mutasi/controllers/Request_mutasi.php:822) | Tidak ditemukan | Tidak terdeteksi |
| [terbilang](C:/web_dev/sendigs_finance/application/modules/request_mutasi/controllers/Request_mutasi.php:831) | Tidak ditemukan | Tidak terdeteksi |
| [getMataUangByKode](C:/web_dev/sendigs_finance/application/modules/request_mutasi/controllers/Request_mutasi.php:842) | Tidak ditemukan | Tidak terdeteksi |

### request_payment

Menu: Approval Pengembalian Expense; Request Payment; Payment List; Pengembalian Expense. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:35) | Tidak ditemukan | Tidak terdeteksi |
| [get_data_request](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:49) | Tidak ditemukan | Tidak terdeteksi |
| [get_summary_request](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:55) | Tidak ditemukan | Tidak terdeteksi |
| [export_excel_request](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:61) | Tidak ditemukan | Tidak terdeteksi |
| [reject_history](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:92) | Tidak ditemukan | Tidak terdeteksi |
| [submit_for_approval](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:113) | Tidak ditemukan | Ya |
| [payment_list](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:277) | Tidak ditemukan | Tidak terdeteksi |
| [get_data_payment_list](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:283) | Tidak ditemukan | Tidak terdeteksi |
| [save_request](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:288) | Tidak ditemukan | Ya |
| [list_approve](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:372) | Tidak ditemukan | Tidak terdeteksi |
| [list_approve_checker](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:390) | Tidak ditemukan | Tidak terdeteksi |
| [list_approve_management](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:414) | Tidak ditemukan | Tidak terdeteksi |
| [approval_payment](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:443) | Tidak ditemukan | Tidak terdeteksi |
| [approval_payment_checker](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:526) | Tidak ditemukan | Tidak terdeteksi |
| [save_approval_cons](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:673) | Tidak ditemukan | Ya |
| [save_approval](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:842) | Tidak ditemukan | Ya |
| [save_approval_checker](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:1271) | Tidak ditemukan | Ya |
| [list_payment](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:1329) | Tidak ditemukan | Tidak terdeteksi |
| [save_payment](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:1339) | Tidak ditemukan | Ya |
| [payment_jurnal_list](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:1665) | Tidak ditemukan | Tidak terdeteksi |
| [view_jurnal](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:1672) | Tidak ditemukan | Tidak terdeteksi |
| [edit_jurnal](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:1684) | Tidak ditemukan | Tidak terdeteksi |
| [jurnal_save](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:1695) | Tidak ditemukan | Ya |
| [list_return](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:1774) | Tidak ditemukan | Tidak terdeteksi |
| [list_return_approval](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:1808) | Tidak ditemukan | Tidak terdeteksi |
| [reject_approval](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:1817) | Tidak ditemukan | Ya |
| [approve_pengembalian_expense](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:1886) | Tidak ditemukan | Ya |
| [reject_pengembalian_expense](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:2080) | Tidak ditemukan | Ya |
| [search_payment_list](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:2101) | Tidak ditemukan | Tidak terdeteksi |
| [search_req_payment](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:2110) | Tidak ditemukan | Tidak terdeteksi |
| [change_tab](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:2402) | Tidak ditemukan | Tidak terdeteksi |
| [excel_payment_list](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:2681) | Tidak ditemukan | Tidak terdeteksi |
| [view_receive_invoice](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:2691) | Tidak ditemukan | Tidak terdeteksi |
| [save_approval_checker_consultant](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:2722) | Tidak ditemukan | Ya |
| [save_approval_consultant](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:2789) | Tidak ditemukan | Ya |
| [get_data_req_payment](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:2955) | Tidak ditemukan | Tidak terdeteksi |
| [get_summary_cards](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:2966) | Tidak ditemukan | Tidak terdeteksi |
| [added_pilih_data](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:2978) | Tidak ditemukan | Ya |
| [save_request_payment](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:3006) | Tidak ditemukan | Ya |
| [reset_choosed_req_payment](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:3289) | Tidak ditemukan | Ya |
| [copy_to_payment](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:3304) | Tidak ditemukan | Ya |
| [reject_req_payment](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:3309) | Tidak ditemukan | Ya |
| [download_excel_request_payment](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:3428) | Tidak ditemukan | Tidak terdeteksi |
| [print_cash](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:3664) | Tidak ditemukan | Tidak terdeteksi |
| [print_direct_payment](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:3766) | Tidak ditemukan | Tidak terdeteksi |
| [print_kasbon](C:/web_dev/sendigs_finance/application/modules/request_payment/controllers/Request_payment.php:4207) | Tidak ditemukan | Tidak terdeteksi |

### request_pr_stok

Menu: PR Stok. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:62) | [history](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:914); [audit_log](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:916) | Tidak terdeteksi |
| [data_side_approval_pr_material](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:113) | [history](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:914); [audit_log](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:916) | Tidak terdeteksi |
| [add](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:119) | [history](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:914); [audit_log](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:916) | Tidak terdeteksi |
| [add_new](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:129) | [history](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:914); [audit_log](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:916) | Tidak terdeteksi |
| [server_side_reorder_point](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:139) | [history](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:914); [audit_log](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:916) | Tidak terdeteksi |
| [server_side_reorder_point_new](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:145) | [history](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:914); [audit_log](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:916) | Tidak terdeteksi |
| [clear_update_reorder](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:151) | [history](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:914); [audit_log](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:916) | Ya |
| [save_reorder_change](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:190) | [history](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:914); [audit_log](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:916) | Ya |
| [save_reorder_all](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:238) | [history](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:914); [audit_log](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:916) | Ya |
| [save_reorder_change_date](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:341) | [history](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:914); [audit_log](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:916) | Ya |
| [detail_planning](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:377) | [history](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:914); [audit_log](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:916) | Tidak terdeteksi |
| [edit_planning](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:414) | [history](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:914); [audit_log](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:916) | Tidak terdeteksi |
| [process_update_all](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:457) | [history](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:914); [audit_log](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:916) | Ya |
| [auto_update_rutin](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:530) | [history](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:914); [audit_log](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:916) | Ya |
| [PrintH2](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:592) | [history](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:914); [audit_log](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:916) | Tidak terdeteksi |
| [edit_detail](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:606) | [history](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:914); [audit_log](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:916) | Ya |
| [del_detail](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:632) | [history](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:914); [audit_log](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:916) | Ya |
| [refresh_pr_detail](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:653) | [history](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:914); [audit_log](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:916) | Tidak terdeteksi |
| [add_stok](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:719) | [history](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:914); [audit_log](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:916) | Ya |
| [check_inputed_qty_stock](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:763) | [history](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:914); [audit_log](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:916) | Tidak terdeteksi |
| [close_pr_modal](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:780) | [history](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:914); [audit_log](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:916) | Tidak terdeteksi |
| [close_pr](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:792) | [history](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:914); [audit_log](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:916) | Ya |
| [hitung_budget](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:816) | [history](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:914); [audit_log](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:916) | Tidak terdeteksi |
| [hitung_pengajuan](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:839) | [history](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:914); [audit_log](C:/web_dev/sendigs_finance/application/modules/request_pr_stok/controllers/Request_pr_stok.php:916) | Tidak terdeteksi |

### ros

Menu: ROS. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/ros/controllers/Ros.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/ros/controllers/Ros.php:20) | Tidak ditemukan | Tidak terdeteksi |
| [add](C:/web_dev/sendigs_finance/application/modules/ros/controllers/Ros.php:29) | Tidak ditemukan | Tidak terdeteksi |
| [edit](C:/web_dev/sendigs_finance/application/modules/ros/controllers/Ros.php:45) | Tidak ditemukan | Tidak terdeteksi |
| [view](C:/web_dev/sendigs_finance/application/modules/ros/controllers/Ros.php:78) | Tidak ditemukan | Tidak terdeteksi |
| [data_side_ros](C:/web_dev/sendigs_finance/application/modules/ros/controllers/Ros.php:109) | Tidak ditemukan | Tidak terdeteksi |
| [get_no_po_detail](C:/web_dev/sendigs_finance/application/modules/ros/controllers/Ros.php:114) | Tidak ditemukan | Tidak terdeteksi |
| [add_custom_pembiayaan](C:/web_dev/sendigs_finance/application/modules/ros/controllers/Ros.php:181) | Tidak ditemukan | Ya |
| [hitung_custom_pib](C:/web_dev/sendigs_finance/application/modules/ros/controllers/Ros.php:210) | Tidak ditemukan | Tidak terdeteksi |
| [refresh_list_pib](C:/web_dev/sendigs_finance/application/modules/ros/controllers/Ros.php:226) | Tidak ditemukan | Tidak terdeteksi |
| [del_custom_pib](C:/web_dev/sendigs_finance/application/modules/ros/controllers/Ros.php:257) | Tidak ditemukan | Ya |
| [save_ros](C:/web_dev/sendigs_finance/application/modules/ros/controllers/Ros.php:276) | Tidak ditemukan | Ya |
| [del_ros](C:/web_dev/sendigs_finance/application/modules/ros/controllers/Ros.php:463) | Tidak ditemukan | Ya |
| [req_payment_ros](C:/web_dev/sendigs_finance/application/modules/ros/controllers/Ros.php:486) | Tidak ditemukan | Ya |
| [get_po_by_supplier](C:/web_dev/sendigs_finance/application/modules/ros/controllers/Ros.php:608) | Tidak ditemukan | Tidak terdeteksi |

### stok_gudang_barang

Menu: Stock. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/stok_gudang_barang/controllers/Stok_gudang_barang.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/stok_gudang_barang/controllers/Stok_gudang_barang.php:27) | [history](C:/web_dev/sendigs_finance/application/modules/stok_gudang_barang/controllers/Stok_gudang_barang.php:37) | Tidak terdeteksi |
| [data_side_stock](C:/web_dev/sendigs_finance/application/modules/stok_gudang_barang/controllers/Stok_gudang_barang.php:42) | Tidak ditemukan | Tidak terdeteksi |
| [modal_history](C:/web_dev/sendigs_finance/application/modules/stok_gudang_barang/controllers/Stok_gudang_barang.php:47) | Tidak ditemukan | Tidak terdeteksi |
| [download_excel](C:/web_dev/sendigs_finance/application/modules/stok_gudang_barang/controllers/Stok_gudang_barang.php:64) | Tidak ditemukan | Tidak terdeteksi |
| [download_excel_history](C:/web_dev/sendigs_finance/application/modules/stok_gudang_barang/controllers/Stok_gudang_barang.php:322) | Tidak ditemukan | Tidak terdeteksi |

### supplier

Menu: Master Supplier. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/supplier/controllers/Supplier.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/supplier/controllers/Supplier.php:28) | [history](C:/web_dev/sendigs_finance/application/modules/supplier/controllers/Supplier.php:33) | Tidak terdeteksi |
| [get_json_supplier](C:/web_dev/sendigs_finance/application/modules/supplier/controllers/Supplier.php:39) | Tidak ditemukan | Tidak terdeteksi |
| [add](C:/web_dev/sendigs_finance/application/modules/supplier/controllers/Supplier.php:44) | [history](C:/web_dev/sendigs_finance/application/modules/supplier/controllers/Supplier.php:139) | Ya |
| [edit](C:/web_dev/sendigs_finance/application/modules/supplier/controllers/Supplier.php:164) | Tidak ditemukan | Tidak terdeteksi |
| [detail](C:/web_dev/sendigs_finance/application/modules/supplier/controllers/Supplier.php:189) | Tidak ditemukan | Tidak terdeteksi |
| [hapus](C:/web_dev/sendigs_finance/application/modules/supplier/controllers/Supplier.php:207) | [history](C:/web_dev/sendigs_finance/application/modules/supplier/controllers/Supplier.php:235) | Ya |
| [excel_report_all](C:/web_dev/sendigs_finance/application/modules/supplier/controllers/Supplier.php:241) | Tidak ditemukan | Tidak terdeteksi |

### unit

Menu: Master Unit Measurement. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/unit/controllers/Unit.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/unit/controllers/Unit.php:26) | [history](C:/web_dev/sendigs_finance/application/modules/unit/controllers/Unit.php:33) | Tidak terdeteksi |
| [add](C:/web_dev/sendigs_finance/application/modules/unit/controllers/Unit.php:39) | [history](C:/web_dev/sendigs_finance/application/modules/unit/controllers/Unit.php:88) | Ya |
| [hapus](C:/web_dev/sendigs_finance/application/modules/unit/controllers/Unit.php:106) | [history](C:/web_dev/sendigs_finance/application/modules/unit/controllers/Unit.php:135) | Ya |

### unit_packing

Menu: Master Unit Packing. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/unit_packing/controllers/Unit_packing.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/unit_packing/controllers/Unit_packing.php:26) | [history](C:/web_dev/sendigs_finance/application/modules/unit_packing/controllers/Unit_packing.php:33) | Tidak terdeteksi |
| [add](C:/web_dev/sendigs_finance/application/modules/unit_packing/controllers/Unit_packing.php:39) | [history](C:/web_dev/sendigs_finance/application/modules/unit_packing/controllers/Unit_packing.php:89) | Ya |
| [hapus](C:/web_dev/sendigs_finance/application/modules/unit_packing/controllers/Unit_packing.php:107) | [history](C:/web_dev/sendigs_finance/application/modules/unit_packing/controllers/Unit_packing.php:136) | Ya |

### unlocated_penerimaan

Menu: Unlocated Penerimaan. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/unlocated_penerimaan/controllers/Unlocated_penerimaan.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/unlocated_penerimaan/controllers/Unlocated_penerimaan.php:18) | Tidak ditemukan | Tidak terdeteksi |
| [get_unlocated_penerimaan](C:/web_dev/sendigs_finance/application/modules/unlocated_penerimaan/controllers/Unlocated_penerimaan.php:33) | Tidak ditemukan | Tidak terdeteksi |
| [rollback](C:/web_dev/sendigs_finance/application/modules/unlocated_penerimaan/controllers/Unlocated_penerimaan.php:38) | Tidak ditemukan | Ya |

### users

Menu: Users. Controller: [controller](C:/web_dev/sendigs_finance/application/modules/users/controllers/Setting.php).

| Endpoint | Jalur ke hook | Indikasi operasi tulis DB |
|---|---|---|
| [index](C:/web_dev/sendigs_finance/application/modules/users/controllers/Setting.php:45) | [simpan_aktifitas](C:/web_dev/sendigs_finance/application/modules/users/controllers/Setting.php:73); [history](C:/web_dev/sendigs_finance/application/modules/users/controllers/Setting.php:151) | Tidak terdeteksi |
| [create](C:/web_dev/sendigs_finance/application/modules/users/controllers/Setting.php:160) | Tidak ditemukan | Tidak terdeteksi |
| [edit](C:/web_dev/sendigs_finance/application/modules/users/controllers/Setting.php:183) | Tidak ditemukan | Tidak terdeteksi |
| [permission](C:/web_dev/sendigs_finance/application/modules/users/controllers/Setting.php:234) | Tidak ditemukan | Tidak terdeteksi |
| [default_select](C:/web_dev/sendigs_finance/application/modules/users/controllers/Setting.php:507) | Tidak ditemukan | Tidak terdeteksi |
| [getArray](C:/web_dev/sendigs_finance/application/modules/users/controllers/Setting.php:512) | Tidak ditemukan | Tidak terdeteksi |
| [update_user_permission](C:/web_dev/sendigs_finance/application/modules/users/controllers/Setting.php:539) | Tidak ditemukan | Ya |
| [get_titles](C:/web_dev/sendigs_finance/application/modules/users/controllers/Setting.php:544) | Tidak ditemukan | Tidak terdeteksi |

## Pemeriksaan dan batasan

- Database dibaca menggunakan SELECT: daftar menu dan agregat jumlah/waktu logs. Tidak ada INSERT/UPDATE/DELETE atau migrasi dijalankan.
- Tidak ada kode aplikasi diubah. Artefak laporan Markdown/JSON saja yang ditambahkan.
- Cakupan seluruh link menu diperiksa, semua controller dan method menu ditemukan. Analisis token serta sampel controller/model diperiksa langsung.
- Indikasi operasi tulis adalah bantuan prioritas dari call graph, bukan pembuktian perilaku transaksi; metode export yang menulis metadata bisnis dapat ikut terdeteksi.
- Tidak ada pengujian browser atau pemanggilan endpoint bisnis, sehingga klasifikasi selain PR Stok belum merupakan verifikasi runtime seluruh cabang.
- Jika menu/permission atau kode berubah, snapshot ini perlu diperbarui.

## Checkpoint implementasi bertahap

Fase 0 fondasi recorder selesai; fase bisnis 1–19 dan verifikasi akhir 20 belum dikerjakan. Klasifikasi di atas adalah audit baseline, bukan status rollout terbaru. Lihat [checkpoint](C:/web_dev/sendigs_finance/docs/audits/audit-rollout-checkpoint.md) dan field rollout pada manifest JSON untuk status setiap fase/batch/endpoint.

## Rollout Fase 1 — checkpoint batch 1

request_payment batch 1 selesai: 10 endpoint, 90 skenario fixture/subprocess PASS. Fase 1 masih sedang dikerjakan (10/55); batch 2 berikutnya. Tidak ada controller fase lain yang diaktifkan. Lihat `phase-1-request-payment.md` dan `audit-rollout-checkpoint.md` untuk rincian.
