# Request Payment: tiga tab — 14 Oktober 2026

## Revisi UI tombol terbaru

Sesuai referensi user ke modul `record_request_payment`, ACTION pada tab Approval dan Record sekarang memakai gaya `mini-btn` dari view index modul tersebut: putih, border tipis, padding 4px 10px, radius 4px, font 11.5px/600. View memakai warna/border biru muted; Print dan Excel slate. Selector dibatasi pada dua tabel tab baru. Grup tombol inline-flex/nowrap menjaga aksi Record sejajar; tabel tetap memakai wrapper scroll existing untuk layar sempit. Tidak mengubah CSS Request atau modul referensi.

Fungsi/label/URL tetap: Approval hanya Print; Record View, Cetak Dokumen, Excel Pengajuan. File aplikasi yang berubah pada revisi UI hanya `request_payment/views/rp_items.php`; dokumentasi dan manifest latest_revision disinkronkan. PHP lint, 46 assertions view, 93 checks client, whitespace PASS. Tidak membuat tes baru untuk perubahan styling saja; memakai tes existing. Backend tidak berubah; tidak menjalankan browser test. Bagian berikut merekam implementasi awal serta revisi aksi sebelumnya.

## Hasil dan batas cakupan

`request_payment/index` memiliki tab Request Payment, Approval Request Payment, dan Record Request Payment. Request mempertahankan isi/CSS existing. Approval/Record memakai daftar satu baris per dokumen dengan nomor PGJ. Sesuai revisi user 14 Oktober, ACTION tab Approval hanya Print dokumen sumber; approve/reject dilakukan di modul Approval Request Payment terpisah, tetap per PGJ. Rejected kembali ke Request melalui aturan lock existing; Record hanya snapshot approved. Histori legacy/menu/halaman lama tetap tersedia.

Tidak melakukan migration, browser test, DB bisnis writes, deployment, commit/push, atau melanjutkan rollout audit Fase 2. Source Request controller/model tidak diubah. Working tree audit existing dipertahankan.

## File dalam task

- Diubah: `request_payment/views/rp_index.php`; controller/model Approval dan Record; `approval_request_payment/views/detail.php`, `record_request_payment/views/detail.php`.
- Dibuat: `request_payment/views/rp_items.php`; `request_payment/tests/Tabs_test.php`, `Tabs_view_test.php`, `Tabs_query_test.php`, `Tabs_client_test.js`.
- Dokumentasi: bagian task terpisah dalam `docs/implementation-plan.md` dan `docs/task-progress.md`; laporan ini; `docs/request-payment-tabs-manifest.json`; manifest audit existing disinkronkan untuk dua endpoint baru, line endpoint terkait, dan SHA checkpoint dua controller.
- Snapshot lokal: `scratch/request_payment_tabs_baseline/`, hanya bukti pembanding sesi. Tes preservation menggunakan hash fixture dalam file tes dan tidak bergantung pada folder scratch.

## Keputusan implementasi

- Endpoint baru `approval_request_payment/get_data_pending_items` membaca detail/header pending non-deleted; nilai default decision approved tidak dianggap keputusan final. `record_request_payment/get_data_record_items` membaca snapshot/header done non-deleted. Format DataTables, pencarian bound melalui Query Builder, paging deterministik tanggal/id. Endpoint lama tidak berubah.
- Header/menu/filter/card/button/table Request tidak diubah; hanya ditambahkan wrapper tab dan nav Bootstrap. CSS baru terbatas pada ID tabel baru, dengan nilai styling yang sama dengan tabel Request. Tidak menambah filter/card/badge baru. Masing-masing tabel baru memiliki kontrol search/entries existing dan lazy load/refresh.
- Return context hanya literal `from=request_payment` ditambah permission Request View, menghasilkan tujuan internal fixed. Parameter tab allowlist dengan fallback Request. Deep link dan kembali dari detail membuka tab asal; keputusan berhasil kembali ke Approval.
- Tombol keputusan memakai `Approval_Request_Payment.Manage`, konsisten dengan server. Tab/endpoints memakai permission View modul masing-masing, denied sebelum query.
- Snapshot angka final tidak diubah; sumber ID cetak diambil dari detail PGJ, bukan primary key snapshot. Ekspor tetap per PGJ dengan label Excel Pengajuan.
- Audit read memakai recorder existing dengan metadata bounded/counts, error generik, dedup, dan isolasi kegagalan writer. Semua payload teks pada daftar baru di-escape; href cetak di-escape sebagai atribut dan membatasi protocol HTTP(S).

## Bukti validasi

Runtime PHP: `C:/xampp74/php/php.exe`; Node: v24.21.0. RED dibuktikan sebelum endpoint dan tab tersedia; semua pemeriksaan akhir di bawah PASS.

| Pemeriksaan | Hasil |
| --- | --- |
| Tabs_test | 142 assertions: real model/controller/recorder, per-doc/status, snapshot, search/count/paging/all/empty, setiap count/get failure, exception, print failure, permission, writer, dedup, metadata |
| Tabs_view_test | 46 assertions: active/invalid tab, permission rendering, fixed return/unsafe input, canonical Manage; hash CSS dan markup Request identik baseline |
| Tabs_query_test | 15 assertions: real CodeIgniter Query Builder + SQLite memory; status/deleted, paging/search/injection, snapshot tetap, source ID cetak, rejected unlocked/resubmitted, query failure |
| Tabs_client_test | 93 checks: sintaks seluruh inline JS hasil render tiga tab; VM untuk lazy load/refresh/search/entries, column alignment, XSS/URL attributes, Record origin, URL state, Request refresh; Approval Print-only/tanpa approve-detail, URL kosong/unsafe tanpa aksi |
| Request regression batch 1–5 | 94 + 242 + 282 + 810 + 371 skenario PASS |
| Approval / Record regression | 60 / 57 skenario PASS; total regresi Fase 1 1.916 skenario |
| Fondasi audit | Logger, Recorder, Legacy, Auth, Controller, Query, Menu — tujuh suite PASS |
| PHP / JS lint | Sebelas file PHP task tanpa syntax error; `node --check Tabs_client_test.js` PASS |
| Diff / source review | Whitespace PASS; semua method model existing identik hash token; pada dua controller hanya body detail existing berubah untuk return route, selain endpoint baru. Body save_decision dan export existing identik baseline |

Query SQLite memakai adapter kode error sukses PDO `00000` menjadi `0`, sesuai kontrak numeric mysqli yang digunakan konfigurasi project. Query Builder count legacy melempar Error ketika query gagal; endpoint baru menangkap Throwable dan mengembalikan JSON generik, diuji melalui fixture exception dan missing-table real SQLite. Tidak mengubah fondasi DB/audit untuk perbedaan driver test ini.

## Risiko dan TODO

- Schema/data MySQL live dan hasil visual browser belum diverifikasi; browser sengaja tidak diuji sesuai instruksi. Semua tes database task memakai SQLite memory atau fixture.
- Detail print melakukan lookup sumber existing; sumber/connection gagal menyebabkan respons daftar baru gagal generik dan audit failed, bukan menampilkan data parsial sebagai sukses.
- Record belum mencampur histori legacy. Approval per dokumen secara mandiri tidak termasuk task; keputusan PGJ hanya melalui modul Approval terpisah. Tab Approval dalam Request Payment hanya Print.
- Baseline audit Fase 1 tetap 55 endpoint historis; dua endpoint fitur ini adalah tambahan yang diverifikasi, sehingga controller Approval/Record sekarang memiliki 5/6 endpoint. Progres rollout Fase 2 tetap 38/51 dan tidak diubah.

Manifest task memuat endpoint/permission/source/SHA dan hasil verifikasi: `docs/request-payment-tabs-manifest.json`.

## Revisi ACTION tab Approval

User meminta aksi hanya Print setelah melihat implementasi awal. Perubahan terbatas pada partial `rp_items.php`, tes client, dan dokumentasi/manifest. Print memakai `print_url` existing, membuka dokumen sumber di tab baru dengan `noopener`; href di-escape dan dibatasi HTTP(S). Warna/kelas tombol existing dipertahankan. Jika URL cetak tidak tersedia, tampil `-` tanpa tombol lain. Aksi Record dan modul approval terpisah tidak diubah.

Tes Print-only RED sebelum perubahan, kemudian 93 checks client + 46 assertions view PASS. PHP lint partial, Node syntax, dan whitespace PASS. Hasil suite backend di atas tetap berlaku karena kode, konfigurasi, dan dependensinya tidak berubah; tidak mengulang tes backend atau browser.

## Publikasi main dan sinkronisasi development

User meminta push main serta menyamakan tiga modul dengan development. Fondasi audit minimum yang belum tersedia di remote ikut dikirim (autoload/helper/logger/recorder/bridge legacy dan tiga tes). Perubahan modul lain, file eksperimen, dump, dan manifest audit global tetap lokal.

- main code: 27a11cf57045e283c33f013cff3699f2ad650c4c (735ed206 dependensi; 63d0357d modul/tes/docs; 93714dcb whitespace; 27a11cf5 koreksi staging paralel).
- development: db0e9507b39ec35aad58a366c6c29fc10fba7db1, parent remote development sebelumnya; sync hanya tiga direktori dan dependensi minimum. Tidak merge seluruh main atau force push.
- Fetch/verifikasi remote: tree Request bbb8a9bdcdec6f749471134581388c969ee2efc1; Approval e7042d91d21e5bab3725403f31fb748dffe4ee3d; Record 0811a8d44740be8a00469932b8b83eca2f4fa201 sama persis pada kedua branch. Diff tiga direktori kosong.
- Validasi pra-push: 1.916 skenario regresi modul, 203 assertions Tabs PHP, client 93 checks, Logger/Recorder/Legacy PASS. Satu whitespace query print legacy dirapikan setelah terdeteksi diff development; PHP lint dan 371 skenario Batch5 PASS.
- Saat commit whitespace, staging paralel memasukkan input_form pengajuan_rutin/checkpoint. Main dikoreksi lewat commit 27a11cf5, tanpa menghapus perubahan lokalnya. Sinkronisasi berikutnya dan commit docs memakai index terpisah.
- Catatan penutupan dipublikasikan sebagai commit dokumentasi tambahan di main; tree modul tetap sama. Progres audit Fase 2 lokal tidak dilanjutkan task ini.
