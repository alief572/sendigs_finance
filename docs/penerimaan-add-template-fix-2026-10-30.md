# Perbaikan kontrak form Penerimaan Piutang - 30 Oktober 2026

Status: SELESAI. Error pengguna: Undefined variable results pada penerimaan_uang/views/add.php line60, melalui add_penerimaan_uang/2269.

Penyebab: controller memakai Template::set($data), yang membuat variabel terpisah. View add membaca payload dari $results. Ini ketidaksesuaian kontrak pengiriman data, bukan masalah saldo atau migration.

Perubahan runtime: application/modules/penerimaan_uang/controllers/Penerimaan_uang.php, hanya metode add_penerimaan_uang, mengirim Template::set('results', $data). Identitas alokasi, nominal bank tersisa, customer dan receipt_kind sekarang tersedia sesuai view. Tidak menutupi notice dengan nilai default atau mengubah transaksi/database.

Tes baru Add_template_contract_test.php pada penerimaan_uang/tests memakai Template::set produksi yang asli, controller asli dan view asli. Data model/otorisasi disediakan fixture; rendering mengubah notice menjadi exception. Kondisi Invoice dan Kwitansi Deposit diuji, termasuk kind, ID alokasi, sisa bank dan opsi customer. RED menghasilkan notice results yang sama dengan screenshot; GREEN sesudah perbaikan.

Adapter ClosureTemplate di penerimaan_uang/tests/Deposit_closure_cases.php disesuaikan agar menyimpan named payload seperti Template asli. Adapter lama hanya menyimpan flat array, sehingga celah ini luput dari tes sebelumnya. Tes view manual sebelumnya dipertahankan sebagai pengujian terpisah, bukan bukti kontrak controller.

Validasi: Add_template_contract_test PASS; Node company_context dan consulting_view PASS; legacy_vuca_preview PASS; native CI/MariaDB disposable943checks PASS; lint3PHP dan whitespace PASS. Tidak browser atau write database aplikasi. Snapshot hash manifest penutupan sebelumnya merupakan baseline historis sebelum koreksi ini.

Tidak ada TODO coding task ini. Pengguna dapat refresh halaman penerimaan, termasuk route alokasi2269.
