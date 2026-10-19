# Notifikasi simpan invoicing — 2026-10-18

Status: selesai lokal; tanpa commit, push, database, atau deployment.

Form konsultasi v3 tambah/edit dan simpan mapping COA sekarang memakai SweetAlert2 v11.23.0 dari assets/dist yang sudah tersedia. Form legacy/Vuca/nonkons sudah memakai SweetAlert2 dan dipertahankan. Tidak mengganti SweetAlert v1 global atau menambah konfirmasi sebelum simpan.

Helper bersama assets/js/invoicing/save-feedback.js menampilkan loading selama AJAX; sukses hanya respons status numerik1 dari endpoint existing, kemudian navigasi/reload setelah OK. Tombol simpan dikunci sejak request hingga navigasi pada sukses untuk menghindari submit ganda. Penolakan server ditampilkan memakai text, tanpa HTML; respons tidak valid dan gangguan jaringan tidak mengklaim sukses. Kegagalan mengembalikan tombol dan aria-busy. Pesan inline tetap tersedia bila library modal tidak tersedia/bermasalah. Native required validation berjalan sebelum request.

Runtime: assets/js/invoicing/consulting-form.js, helper baru, views/consulting_invoice.php dan consulting_settings.php. Detail readonly tidak memuat script simpan/modal. Backend/status-msg/fingerprint/permission/audit/commit transaksi tidak berubah. Company SPK/policy owner, sidebar search, serta compact table lokal dipertahankan.

Validasi PASS:

- Save_feedback_client_test.js: 48 checks, mencakup loading, modal sukses menunggu OK, error server, jaringan, respons null/status salah, string literal, native validity, submit ganda, fallback library, exception request, serta callback mapping.
- Ui_client_test.js: 43 checks; preview existing dan integrasi form add/edit ke helper/modal/redirect.
- Ui_render_test.php: 228 checks, asset lokal/form editable/settings, readonly, field/permission/owner dan tabel compact existing.
- Ui_list_client_test.js: 45 checks, regresi compact table/tab/filter/aksi.
- PHP lint kedua view dan render test; syntax helper/form/dua client test dan inline script render kedua view; scoped whitespace.
- Manifest memverifikasi hash backend/owner/sidebar dan file invoicing lain yang tidak disentuh tetap identik.

Tidak browser sesuai instruksi project; perilaku fokus/animasi SweetAlert2 pada browser aktual belum diuji. Tidak rerun database341 checks sebelumnya karena backend/schema tidak berubah. Tidak TODO implementasi. Asset modal lokal tidak menambah request ke CDN; file library existing tidak dimodifikasi. Antislop during mengikuti pilihan sesi, ENERGY1/RHYTHM1/MOTION1 mengikuti UI existing tanpa dekorasi baru; gate source/render/client lolos dengan batas verifikasi browser tersebut.
