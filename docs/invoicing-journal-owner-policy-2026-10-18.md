# Company SPK dan pemilik jurnal

Perbaikan lokal setelah publikasi913c336d, atas permintaan user. Label Company sumber diganti menjadi **Company SPK**. Company SPK tetap menggunakan seluruh master company, termasuk gabungan. Pilihan pemilik jurnal hanya ID7STM, ID3Sustain, ID4Vuca, sesuai identitas master dan routing accounting project. Daftar menggunakan ID eksplisit, bukan menebak nama company.

Policy bersama `Invoice_consulting_service::is_journal_owner()` dan `journal_owner()` digunakan pada GET pengaturan, POST save_mapping, dan pembacaan mapping saat penerbitan invoice baru. Request company gabungan/Calibration/ID tidak dikenal ditolak dengan pesan yang meminta STM, Sustain, atau Vuca. Validasi berjalan sebelum lookup COA dan penulisan mapping. Sumber SPK gabungan tetap sah.

Mapping lama dengan owner gabungan tidak dikonversi otomatis. Pengaturan tanpa parameter owner menampilkan pesan, mengosongkan pilihan owner yang tidak valid, dan tetap memungkinkan admin memuat pemilik yang benar lalu menyimpan ulang dengan fingerprint mapping lama. Mapping noncanonical tidak dapat digunakan untuk penerbitan invoice baru sampai diperbaiki. Tidak mengubah snapshot invoice tersimpan atau routing accounting_group yang diperlukan histori, edit, penerimaan, dan posting invoice yang sudah terbit.

Ini membatasi jenis pemilik jurnal, bukan menambahkan pemilihan owner otomatis atau aturan pasangan source-owner baru. Petunjuk company gabungan tetap meminta mengikuti company depan pada SPK. Nama field API `source_company_id` dan `owner_company_id`, permission, fingerprint, kontrak status/msg dan search Select2 dipertahankan. Tidak perubahan schema/data aplikasi, commit atau push pada task ini.

File runtime: `application/modules/invoicing/controllers/Invoicing.php`, `libraries/Invoice_consulting_service.php`, `views/consulting_settings.php`. Tes: `tests/Ui_render_test.php` dan `tests/Consulting_integration_test.php`. Manifest/hash dan dokumen rencana/checkpoint diperbarui.

Validasi:

- Native CI + MariaDB disposable **341 checks PASS**, termasuk GET pemilik gabungan ditolak, POST owner1/2/5/6/99 ditolak tanpa perubahan mapping, owner7/3/4 diterima dengan source SPK gabungan, pembacaan mapping noncanonical ditolak, UI perbaikan mapping lama, serta seluruh regresi invoice/penerimaan/snapshot/posting/fault/concurrency dalam suite tersebut. Fixture canonical owner disesuaikan ke master ID7STM, source gabungan tetap source1/6; tidak melemahkan assertion lama.
- Render PHP **208 checks PASS**, termasuk label Company SPK, source gabungan tetap tersedia, tepat tiga opsi owner tanpa gabungan, warning aman/escaped dan seluruh regression render lama.
- Client form38 dan list28 checks PASS; lint5 file PHP dan scoped whitespace PASS.
- Suite memakai port33319 dan database acak disposable, bukan database aplikasi. Sandbox awal menolak socket; dijalankan dengan akses loopback yang sesuai, kemudian server khusus pengujian dihentikan. Tidak browser sesuai instruksi user.

Antislop during sesi existing: hanya label, pilihan, pesan dan policy yang berubah. Tata letak, stylesheet dan aset tidak berubah; tidak membuat aset/dekorasi baru. Gate fungsi dan state dibuktikan render/native/client di atas, dengan batas browser yang sama seperti laporan UI. Mapping lama harus dipilih ulang oleh admin; histori tidak diubah diam-diam.
