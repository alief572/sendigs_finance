# Pemulihan print invoice konsultasi — 2026-10-21

## Penyebab dan perubahan
Jalur print_invoicing mendeteksi invoice konsultasi v3 lalu memanggil consulting_form dengan mode print. Mode ini memuat consulting_print, sebuah template sederhana terpisah, sehingga template print_invoice yang memiliki kop, rekening, tanda tangan dan window.print tidak digunakan. Screenshot invoice 261/STM/2026 sesuai dengan jalur tersebut.

consulting_print kini menjadi adapter ke print_invoice, tanpa layout terpisah. Adapter memakai owner snapshot, keterangan cetak tersimpan (fallback deskripsi TOP snapshot), dan nilai invoice tersimpan. Teks pelanggan/keterangan tetap di-escape. Label Kawasan Berikat tetap disertakan pada deskripsi. Tidak ada perubahan endpoint, proses simpan, kalkulasi/jurnal, atau database.

print_invoice menerima override angka PPN/total dan presisi untuk invoice v3. Ini diperlukan agar KB tidak dihitung pajak ulang dan nominal pecahan tidak hilang. Pemanggil legacy tetap mendapat fallback perhitungan dan presisi nol yang sama. CSS, struktur tabel, logo, instruksi bank, tanda tangan dan pemanggilan dialog print tidak diubah.

## Validasi
- Reproduksi awal: Print_invoice_test gagal pada assertion kop STM; suite UI existing 255 checks lolos sebelum assertion reproduksi.
- Akhir: UI render 255 checks dan print 70 assertions, meliputi tiga owner, PKP/WAPU/KB, subtotal/PPN/total tersimpan, escaping, error, keterangan cetak dan fallback legacy.
- Nilai screenshot diuji sebagai fixture: penjualan291000000, DPP266750000, PPN32010000 dan total323010000.
- Kalkulasi PHP99 checks; parity PHP/JavaScript89 checks.
- Lint kedua view dan tes; whitespace diff diperiksa.

## Batas
Tidak mengakses atau mengubah database, tidak melakukan browser/print fisik, commit, push atau deployment. Validasi membuktikan HTML hasil render dan pemakaian template lama; pagination printer dan pengaturan margin browser tidak diuji. Modul invoice non-konsultasi dan print_invoicing_vuca legacy tidak diubah.
