# Urutan detail jurnal pembayaran

Modal detail pembayaran pada Jurnal All menampilkan debit, kredit, lalu bank (COA 1101-02-*). Urutan asli dalam setiap kelompok dipertahankan, termasuk beberapa baris bank dan bank debit. Biaya administrasi bank mengikuti COA biaya.

Perubahan hanya pada tampilan source PAY yang belum posted. Nominal, total, ID input keterangan, proses posting, export dan report posted tetap.

Validasi PHP 7.4: Preview_order_test.php 14 checks dan Payment_reference_test.php 679 asersi lulus pada development dan main; lint view/tes dan whitespace lulus. Controller_test.php lulus 90 asersi pada development. Pada main tes controller gagal pada entry-only redirect jurnal_invoicing; kegagalan identik diverifikasi pada view baseline sebelum perubahan. Tes pembayaran menggunakan SQLite dan simulasi lock/engine MySQL. Tanpa browser atau DB live. Jika COA bank memakai keluarga selain 1101-02- maka aturan deteksi perlu diperluas.
