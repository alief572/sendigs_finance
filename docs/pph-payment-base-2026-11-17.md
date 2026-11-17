# Koreksi dasar PPh pembayaran_material - 17 November 2026

PPh 21 dan PPh 23 dihitung dari nominal Request Payment tanpa PPN:

- PPh 21 = ceil(Request Payment * 2,5%).
- PPh 23 = ceil(Request Payment * 2%).
- Ceiling ke rupiah penuh dilakukan per item sebelum penjumlahan.
- Total pembayaran = Request Payment + PPN - PPh, sebelum penyesuaian biaya admin.

Backend payment_pph dan frontend calculatePPh memakai dasar yang sama. Tarif,
validasi input, normalisasi kode legacy, dan jalur khusus petty cash/refill tetap.
Perubahan PPN memengaruhi total pembayaran, tanpa mengubah PPh 21/23.

Contoh Request Payment 1.000.028 dan PPN 110.003,08:
PPh 21 = 25.001, payment = 1.085.030,08;
PPh 23 = 20.001, payment = 1.090.030,08.

File kode/tes: Pembayaran_material_model.php, form_payment_new.php,
Payment_rounding_test.php, Payment_rounding_client_test.js,
Payment_rounding_integration_test.php, Batch2_test.php.

Validasi paket development dan main: tes hitung PPh dan frontend, integrasi 83
skenario, Batch1 248, Batch2 595, Selection 98, Journal_validator 43,
Journal_validation, Reconciliation 24, Voucher_repair 19, Coa_completion 17,
Selection_client 27; lint PHP/JS dan whitespace. Mutasi mengembalikan PPN ke dasar
PPh harus ditolak tes, kemudian berkas dipulihkan identik.

Tidak ada migrasi atau perubahan transaksi selesai. Form menghitung ulang PPh
saat dibuka, sehingga dapat berbeda dari nominal pengajuan tersimpan.
Tanpa tes browser atau database live. Paket tidak menyertakan perubahan task lain.
