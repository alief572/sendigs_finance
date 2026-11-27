# Sumber workbook prosedur-penerimaan.xlsx (Prosedur Penerimaan: rekening koran -> Alokasi -> proses lanjutan).
# Format mengikuti specs/input/Prosedur Pembelian Non Operation.xlsx. Ubah isi di sini lalu jalankan:
#   py specs/docs/alokasi-penerimaan/build_prosedur.py specs/docs/alokasi-penerimaan/prosedur-penerimaan.xlsx
# Isi bersumber dari README.md, kamus (build_kamus.py), keputusan AP-R-xx (specs/docs/requests.md),
# test-case-*.md, flow-alokasi-penerimaan.drawio, dan specs/input/Invoicing/Prosedur Jurnal Invoicing & Penerimaan.xlsx.
import os
import sys
from openpyxl import Workbook
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from prosedur_xlsx import ISI, setup, title, section, konsep, steps, table, inputs, journal_header, journal, note

OUT = sys.argv[1]
NJ = 'Tidak Ada Jurnal'
F = 'Finance'
wb = Workbook()

# ---------------- Sheet 1: Prosedur Penerimaan ----------------
ws = wb.active
ws.title = 'Prosedur Penerimaan'
setup(ws)
title(ws, 1, 'PROSEDUR PENERIMAAN (REKENING KORAN → ALOKASI → PROSES LANJUTAN)')
section(ws, 2, 'A. KONSEP')
r = konsep(ws, 3, [
    ('Tujuan', 'Memastikan setiap baris rekening koran (uang masuk dan uang keluar) diketahui transaksinya, diproses di modul tujuannya, dan terjurnal.'),
    ('Sumber', 'Rekening koran (CSV) per rekening perusahaan: BCA 436.300.5287 (STM), BCA 436.400.0300 (SUSTAIN), OCBC NISP 7788.0000.0417 (VUCA). Company dikenali dari rekening.'),
    ('Arah dana', 'Kolom Total Debit = uang masuk ke rekening, Total Credit = uang keluar. Arah dana membatasi jenis alokasi yang boleh dipilih.'),
    ('Alokasi', 'Setiap baris dialokasikan ke jenis alokasi; satu baris boleh dipecah (split) ke beberapa jenis. Total split wajib sama dengan nominal baris.'),
    ('Tampungan', 'Setiap jenis alokasi punya menu tampungan tempat proses lanjutannya dilakukan (lihat bagian C).'),
    ('Rollback', 'Membatalkan alokasi di menu tampungan itu saja; alokasi lain di baris yang sama tetap. Baris kembali Open atau Sebagian · sisa Rp …. Baris yang sudah diproses (MTR / KK / KM) harus dibatalkan dulu di modulnya.'),
    ('Jurnal', 'Upload, alokasi, rollback, dan pindah Unlocated tidak menjurnal. Jurnal terbentuk di proses lanjutan (termasuk Proses Unlocated: Bank / Penerimaan Belum Teridentifikasi). Pola jurnal masih usulan; detail semua jurnal menyusul dari user.'),
    ('Mulai bersih', 'Sistem dipakai mulai tanggal go-live tanpa data awal; semua transaksi dibuat dari rekening koran.'),
])
r += 1
section(ws, r, 'B. LANGKAH UTAMA')
r = steps(ws, r + 1, [
    (1, 'Ambil rekening koran dari bank', F, 'File CSV per rekening. Format dibaca sesuai bank: BCA (kode 7), OCBC NISP (kode 22).', NJ),
    (2, 'Upload Rekening Koran (Penerimaan → Alokasi)', F, 'Pilih rekening, preview, proses. Semua baris tersimpan berstatus Open. Nomor upload NNN/RKN-KRG/<bulan romawi>/YY.', NJ),
    (3, 'Alokasikan tiap baris', F, 'Pilih jenis alokasi per split, dibatasi arah dana. Total split = nominal baris.', NJ),
    (4, 'Proses lanjutan di menu tampungan', 'Sesuai jenis (lihat sheet)', 'Lihat bagian C dan sheet per jenis alokasi.', 'Sesuai jenis alokasi'),
    (5, 'Pantau Dashboard Alokasi', F, '9 card per jenis alokasi: jumlah dan total belum diproses, umur tertua, sebaran 0–7 / 8–30 / >30 hari. Filter tanggal, Company, Bank; export Excel. Isi card masih usulan.', NJ),
])
r += 1
section(ws, r, 'C. JENIS ALOKASI')
r = table(ws, r + 1, [('A', 'A'), ('B', 'B'), ('C', 'D'), ('E', 'E'), ('F', 'G'), ('H', 'I')],
          ['Kode', 'Jenis alokasi', 'Arah dana', 'Proses lanjutan', 'Lanjut ke sheet', 'Status'], [
    (1, 'Penerimaan Piutang', 'Uang masuk', 'Draft di Penerimaan Piutang tab Invoice; pilih invoice, PPh, admin, jurnal.', 'Penerimaan Piutang', 'Berjalan'),
    (8, 'Deposit', 'Uang masuk', 'Draft di Penerimaan Piutang tab Kwitansi Deposit.', 'Penerimaan Piutang', 'Berjalan'),
    (2, 'Unlocated Penerimaan', 'Uang masuk', 'Ditampung di tab Belum Diproses, lalu diproses (jurnal + nomor Unlocated). Setelah teridentifikasi, dari tab Sudah Diproses dipindah ke Penerimaan Piutang Invoice / Kwitansi Deposit. Rollback hanya sebelum diproses.', 'Unlocated', 'Diubah'),
    (3, 'Pengembalian Kasbon', 'Uang masuk', 'Draft di Expense → Verifikasi Pengembalian Kasbon; Finance memilih Expense Report.', 'Pengembalian Kasbon', 'Baru'),
    (4, 'Mutasi', 'Masuk dan keluar', 'Proses Mutasi: pilih Request Mutasi → MTR + jurnal per sisi.', 'Mutasi', 'Baru'),
    (5, 'Transaksi Bank', 'Masuk dan keluar', 'Proses: form terisi dari rekening koran, pilih akun lawan → KM / KK + jurnal.', 'Transaksi Bank', 'Baru'),
    (6, 'Pembayaran', 'Uang keluar', 'Tampungan uang keluar dari Payment; proses lanjutan belum dibahas.', 'Pembayaran', 'Belum dibahas'),
    (7, 'Alokasi Kalibrasi', 'Uang masuk', 'Daftar tampungan, tanpa proses lanjutan.', '-', 'Berjalan'),
])

# ---------------- Sheet: Penerimaan Piutang ----------------
ws = wb.create_sheet('Penerimaan Piutang')
setup(ws)
section(ws, 1, 'A. PROSEDUR PENERIMAAN – JALUR PENERIMAAN PIUTANG (JENIS 1) DAN DEPOSIT (JENIS 8)')
r = steps(ws, 2, [
    (1, 'Alokasikan baris uang masuk', F, 'Jenis 1 Penerimaan Piutang: pilih invoice. Jenis 8 Deposit: pilih kwitansi KD/YYYY/MM/NNNN berstatus Menunggu Pembayaran (1 data per kwitansi).', NJ),
    (2, 'Draft di Penerimaan Piutang', 'Sistem', 'Jenis 1 masuk tab Penerimaan Piutang Invoice; jenis 8 masuk tab Penerimaan Piutang Kwitansi Deposit.', NJ),
    (3, 'Proses penerimaan', F, 'Isi PPh, admin, lalu simpan. Proses berjalan seperti sistem sekarang (penerimaan_uang).', 'Jurnal R1 (dan varian di Prosedur Jurnal Invoicing & Penerimaan)'),
    (4, 'Report Penerimaan', F, 'Penerimaan yang sudah diproses tampil di tab Report Penerimaan.', NJ),
])
r = note(ws, r + 1, 'Varian lengkap jurnal penerimaan (DP, lebih bayar → Deposit Customer, kurang bayar, bayar pakai deposit, UMKM PPh Final) ada di specs/input/Invoicing/Prosedur Jurnal Invoicing & Penerimaan.xlsx, sheet Jurnal Penerimaan. Contoh di bawah hanya kasus dasar.')
r, ref = inputs(ws, r + 1, [
    ('piutang', 'Piutang invoice (DPP + PPN)', 2220000),
    ('pph', 'PPh 23 dipotong customer', 40000),
    ('adm', 'Biaya admin', 2500),
])
r += 1
section(ws, r, 'B. JURNAL JALUR PENERIMAAN PIUTANG')
r = journal_header(ws, r + 1)
r = journal(ws, r, 'R1', 'Penerimaan piutang, PKP potong PPh 23', 3, F,
            'Penjualan 2.000.000; PPN 220.000; piutang 2.220.000; PPh 23 2% × 2.000.000 = 40.000; admin 2.500.',
            [('(sesuai bank)', 'Bank', '{piutang}-{pph}-{adm}', None), ('(belum ada)', 'PPh 23 Disetor', '{pph}', None),
             ('(belum ada)', 'Biaya Admin', '{adm}', None), ('(belum ada)', 'Piutang Dagang Usaha', None, '{piutang}')], ref)

# ---------------- Sheet: Unlocated ----------------
ws = wb.create_sheet('Unlocated')
setup(ws)
section(ws, 1, 'A. PROSEDUR PENERIMAAN – JALUR UNLOCATED PENERIMAAN (JENIS 2)')
r = steps(ws, 2, [
    (1, 'Alokasikan baris sebagai Unlocated Penerimaan', F, 'Uang masuk yang belum diketahui milik siapa / untuk apa.', NJ),
    (2, 'Proses Unlocated (tab Belum Diproses)', F, 'Klik Proses. Terbentuk 1 nomor Unlocated baru sebagai referensi (format nomor diisi user). Baris pindah ke tab Sudah Diproses.', 'Jurnal U1 (bagian B)'),
    ('2a', 'Posting jurnal di Jurnal All · tab Penerimaan (Kategori Transaksi: Unlocated Penerimaan)', 'Finance (Jurnal All)', 'Jurnal Proses Unlocated masuk Jurnal All (belum posting); klik + Post, periksa Detail jurnal, lalu Posting (AP-R-37). Pemindahan ke Penerimaan Piutang tidak menunggu posting.', NJ),
    ('2x', 'Rollback alokasi (hanya sebelum diproses)', F, 'Salah alokasi. Baris kembali Open / Sebagian lalu dialokasikan ulang. Unlocated yang sudah diproses tidak bisa di-rollback.', NJ),
    (3, 'Identifikasi pemilik / tujuan dana', F, 'Selama belum diketahui, baris tetap di tab Sudah Diproses dan terpantau di Dashboard Alokasi.', NJ),
    ('4a', 'Pindahkan ke Penerimaan Piutang Invoice (tab Sudah Diproses)', F, 'Teridentifikasi sebagai pembayaran invoice. Jenis 2 → 1, jadi draft di tab Penerimaan Piutang Invoice dengan referensi nomor Unlocated. Tanpa rollback.', NJ),
    ('4b', 'Pindahkan ke Penerimaan Piutang Kwitansi Deposit (tab Sudah Diproses)', F, 'Teridentifikasi sebagai pembayaran kwitansi deposit. Jenis 2 → 8, jadi draft di tab Kwitansi Deposit dengan referensi nomor Unlocated; nomor kwitansi opsional, 1 data per kwitansi.', NJ),
    (5, 'Proses di Penerimaan Piutang', F, 'Lanjut seperti sheet Penerimaan Piutang, tetapi sisi debit bukan Bank: bank sudah dijurnal saat Unlocated diproses.', 'Jurnal U2 (bagian B)'),
])

r, ref = inputs(ws, r + 2, [('piutang', 'Piutang yang dibayar customer', 2220000), ('pph', 'PPh 23 dipotong customer', 40000), ('adm', 'Biaya admin', 2500)])
r += 1
section(ws, r, 'B. JURNAL JALUR UNLOCATED PENERIMAAN')
r = journal_header(ws, r + 1)
r = journal(ws, r, 'U1', 'Proses Unlocated', 2, F,
            'Uang masuk belum diketahui asalnya. Terbentuk 1 nomor Unlocated baru (referensi). Nomor COA Penerimaan Belum Teridentifikasi diisi user.\nContoh: uang masuk 2.177.500.',
            [('(sesuai bank)', 'Bank', '{piutang}-{pph}-{adm}', None), ('(diisi user)', 'Penerimaan Belum Teridentifikasi', None, '{piutang}-{pph}-{adm}')], ref)
r = journal(ws, r, 'U2', 'Penerimaan piutang dari Unlocated', 5, F,
            'Sama seperti jurnal R1, tetapi sisi Bank diganti Penerimaan Belum Teridentifikasi (referensi nomor Unlocated), karena bank sudah dijurnal di U1.',
            [('(diisi user)', 'Penerimaan Belum Teridentifikasi', '{piutang}-{pph}-{adm}', None), ('(belum ada)', 'PPh 23 Disetor', '{pph}', None),
             ('(belum ada)', 'Biaya Admin', '{adm}', None), ('(belum ada)', 'Piutang Dagang Usaha', None, '{piutang}')], ref)

# ---------------- Sheet: Pengembalian Kasbon ----------------
ws = wb.create_sheet('Pengembalian Kasbon')
setup(ws)
section(ws, 1, 'A. PROSEDUR PENERIMAAN – JALUR PENGEMBALIAN KASBON (JENIS 3)')
r = steps(ws, 2, [
    (1, 'Expense Report disetujui, ada lebih kasbon', 'Sistem', 'Realisasi lebih kecil dari kasbon; sisa wajib dikembalikan (lihat Prosedur Pembelian Non Operation, K3).', NJ),
    (2, 'Transfer lebih kasbon ke rekening perusahaan', 'Karyawan', '-', NJ),
    (3, 'Upload bukti transfer', 'Karyawan', 'Bukti berisi bank, tanggal, jumlah.', NJ),
    (4, 'Alokasikan baris sebagai Pengembalian Kasbon', F, 'Baris rekening koran jadi draft di Expense → Verifikasi Pengembalian Kasbon, tab Mutasi Bank. Tampungan Pengembalian Kasbon di menu Penerimaan hanya menampilkan status berjalan.', NJ),
    (5, 'Proses Verifikasi', F, 'Finance memilih sendiri Expense Report-nya (tidak ada pencocokan otomatis), lalu membandingkan bank, tanggal, nominal dengan bukti karyawan.', NJ),
    (6, 'Centang 3 checklist', F, 'Bukti transfer valid, mutasi rekening bank cocok, nominal sesuai. Ketiganya dicentang manual oleh Finance.', NJ),
    ('7a', 'Setujui & Buat Jurnal', F, 'Semua sesuai. Expense Report pindah ke tab Selesai. Nomor jurnal NNNNN-BUM-<bulan romawi>-YY, masuk Jurnal All · tab Expense Report (belum posting) (AP-R-37).', 'Jurnal PK1: Bank (D) / Piutang Lain-lain Konsultan (K)'),
    ('7b', 'Posting jurnal di Jurnal All · tab Expense Report', 'Finance (Jurnal All)', '+ Post → Detail jurnal → Posting. Setelah diposting tampil di Report Jurnal All.', NJ),
    ('7b', 'Tolak (alasan wajib)', F, 'Tidak sesuai. Karyawan memperbaiki bukti. Tanpa bukti karyawan atau tanpa baris rekening koran, verifikasi tidak bisa disetujui.', NJ),
])
r, ref = inputs(ws, r + 2, [('kemb', 'Lebih kasbon yang dikembalikan', 250000)])
r += 1
section(ws, r, 'B. JURNAL JALUR PENGEMBALIAN KASBON')
r = journal_header(ws, r + 1)
r = journal(ws, r, 'PK1', 'Pengembalian lebih kasbon', '7a', F,
            'Karyawan mengembalikan lebih kasbon ke rekening perusahaan. Jurnal sekali saat verifikasi disetujui; alokasi tidak menjurnal.\nContoh: 250.000 ke BCA STM.',
            [('1101-02-09', 'Bank BCA STM', '{kemb}', None), ('1103-01-14', 'Piutang Lain-lain Konsultan', None, '{kemb}')], ref)

# ---------------- Sheet: Mutasi ----------------
ws = wb.create_sheet('Mutasi')
setup(ws)
section(ws, 1, 'A. PROSEDUR PENERIMAAN – JALUR MUTASI (JENIS 4)')
r = steps(ws, 2, [
    (1, 'Buat Request Mutasi (MTS)', 'Pemohon (Finance)', 'Bank asal, bank tujuan, nilai. Company tampil otomatis dari rekening (Target Accounting dihapus). Nomor MTS-YYMM + 5 digit.', NJ),
    (2, 'Approval Mutasi', 'Approver', 'Approve, atau reject dengan alasan. Hanya request yang approved bisa diproses.', NJ),
    (3, 'Transfer antar rekening (internet banking)', 'Pemohon (Finance)', '-', NJ),
    (4, 'Upload rekening koran kedua rekening, alokasikan sebagai Mutasi', 'Finance (Alokasi)', 'Satu mutasi muncul dua kali: keluar di rekening asal, masuk di rekening tujuan.', NJ),
    (5, 'Proses Mutasi tiap sisi', 'Finance (Alokasi)', 'Pilih MTS yang sudah approved. Sisi pertama yang diproses membuat realisasi MTR; sisi pasangannya ditautkan ke MTR itu. Tanggal dan nilai aktual dari rekening koran.', 'Jurnal M1–M4 per sisi (usulan versi awal)'),
    ('5a', 'Posting jurnal di Jurnal All · tab Jurnal Bank (Kategori Transaksi: Mutasi)', 'Finance (Jurnal All)', 'Jurnal tiap sisi masuk Jurnal All (belum posting); + Post → Posting (AP-R-37). Di sistem sekarang Mutasi langsung ke accounting tanpa Jurnal All.', NJ),
    (6, 'Request Mutasi berstatus Terealisasi MTR-…', 'Sistem', 'Realisasi dan jurnal per sisi dilihat di tampungan Penerimaan → Mutasi (link Lihat jurnal).', NJ),
])
r = note(ws, r + 1, 'Tidak ada jurnal saat Request Mutasi dibuat atau di-approve. Jurnal dibuat per sisi di buku company rekening koran baris itu. COA Piutang / Hutang antar company dan Kas/Bank dalam Perjalanan belum ada; usulan fix jurnal menyusul.')
r, ref = inputs(ws, r + 1, [('mut', 'Nilai mutasi', 15000000)])
r += 1
section(ws, r, 'B. JURNAL JALUR MUTASI (USULAN VERSI AWAL)')
r = journal_header(ws, r + 1)
FA = 'Finance (Alokasi)'
for args in [
    ('M1', 'Antar company, sisi keluar', 5, FA, 'SUSTAIN → STM, buku SUSTAIN.\nContoh: 15.000.000.',
     [('(belum ada)', 'Piutang ke STM', '{mut}', None), ('1101-02-05', 'Bank BCA Sustain', None, '{mut}')]),
    ('M2', 'Antar company, sisi masuk', 5, FA, 'SUSTAIN → STM, buku STM.\nContoh: 15.000.000.',
     [('1101-02-09', 'Bank BCA STM', '{mut}', None), ('(belum ada)', 'Hutang ke SUSTAIN', None, '{mut}')]),
    ('M3', 'Satu company, sisi keluar', 5, FA, 'Bank asal dan tujuan milik company yang sama.',
     [('(belum ada)', 'Kas/Bank dalam Perjalanan', '{mut}', None), ('(sesuai bank)', 'Bank asal', None, '{mut}')]),
    ('M4', 'Satu company, sisi masuk', 5, FA, 'Bank asal dan tujuan milik company yang sama.',
     [('(sesuai bank)', 'Bank tujuan', '{mut}', None), ('(belum ada)', 'Kas/Bank dalam Perjalanan', None, '{mut}')]),
]:
    r = journal(ws, r, *args, ref=ref)

# ---------------- Sheet: Transaksi Bank ----------------
ws = wb.create_sheet('Transaksi Bank')
setup(ws)
section(ws, 1, 'A. PROSEDUR PENERIMAAN – JALUR TRANSAKSI BANK (JENIS 5)')
r = steps(ws, 2, [
    (1, 'Alokasikan baris sebagai Transaksi Bank', F, 'Bunga jasa giro, pajak bunga, biaya admin, biaya transfer, autodebet (mis. BPJS). Satu baris rekening koran = satu transaksi. Input manual ditutup.', NJ),
    (2, 'Proses', F, 'Form terisi dari rekening koran: jenis (uang masuk = Total Debit → Penerimaan, uang keluar = Total Credit → Pengeluaran), tanggal, company, Bank (COA Tujuan) otomatis, nilai.', NJ),
    (3, 'Pilih akun lawan (COA Asal)', F, 'Dipilih manual dari seluruh COA master. Autodebet tagihan yang sudah tercatat sebagai hutang memakai akun hutang supaya beban tidak dobel.', NJ),
    (4, 'Simpan KM / KK', F, 'KM-YYMM + 5 digit (uang masuk, jurnal BUM) atau KK-YYMM + 5 digit (uang keluar, jurnal BUK).', 'Jurnal T1–T4'),
    ('4a', 'Posting jurnal di Jurnal All · tab Jurnal Bank (Kategori Transaksi: Transaksi Bank)', 'Finance (Jurnal All)', 'Jurnal KM / KK masuk Jurnal All (belum posting); + Post → Posting (AP-R-37). Di sistem sekarang Transaksi Bank langsung ke accounting.', NJ),
    (5, 'Tercatat di Data Transaksi Bank', 'Sistem', '-', NJ),
])
r, ref = inputs(ws, r + 2, [
    ('bunga', 'Bunga jasa giro (BCA Sustain, masuk)', 85412),
    ('pajak', 'Pajak bunga (BCA Sustain, keluar)', 17082),
    ('admin', 'Biaya admin (BCA STM, keluar)', 30000),
    ('bpjs', 'Autodebet BPJS Ketenagakerjaan (BCA STM, keluar)', 1850000),
])
r += 1
section(ws, r, 'B. JURNAL JALUR TRANSAKSI BANK')
r = journal_header(ws, r + 1)
for args in [
    ('T1', 'Penerimaan (KM): bunga jasa giro', 4, F, 'Uang masuk: Dr Bank / Cr akun lawan. Buku SUSTAIN.',
     [('1101-02-05', 'Bank BCA Sustain', '{bunga}', None), ('(dipilih user)', 'Pendapatan Bunga Jasa Giro', None, '{bunga}')]),
    ('T2', 'Pengeluaran (KK): pajak bunga', 4, F, 'Uang keluar: Dr akun lawan / Cr Bank. Buku SUSTAIN.',
     [('(dipilih user)', 'Beban Pajak Bunga', '{pajak}', None), ('1101-02-05', 'Bank BCA Sustain', None, '{pajak}')]),
    ('T3', 'Pengeluaran (KK): biaya admin / biaya transfer', 4, F, 'Termasuk biaya admin bank Payment yang muncul sebagai baris terpisah. Buku STM.',
     [('7201-01-04', 'Admin Charge', '{admin}', None), ('1101-02-09', 'Bank BCA STM', None, '{admin}')]),
    ('T4', 'Pengeluaran (KK): autodebet BPJS', 4, F, 'Tagihan sudah tercatat sebagai hutang, jadi lawannya akun hutang. Buku STM.',
     [('2103-01-07', 'Hutang Asuransi BPJS Ketenagakerjaan', '{bpjs}', None), ('1101-02-09', 'Bank BCA STM', None, '{bpjs}')]),
]:
    r = journal(ws, r, *args, ref=ref)

# ---------------- Sheet: Pembayaran ----------------
ws = wb.create_sheet('Pembayaran')
setup(ws)
section(ws, 1, 'A. PROSEDUR PENERIMAAN – JALUR PEMBAYARAN (JENIS 6)')
r = steps(ws, 2, [
    (1, 'Payment tersimpan dan terjurnal', 'Staff Finance', 'Lihat Prosedur Pembayaran (Request Payment → Payment), langkah 8. Jurnal pembayaran sudah dibuat di Payment.', 'Jurnal P1–P9 (Prosedur Pembayaran)'),
    (2, 'Alokasikan baris uang keluar sebagai Pembayaran', F, 'Hanya untuk uang keluar. Biaya admin bank yang muncul sebagai baris terpisah dialokasikan sebagai Transaksi Bank (T3).', NJ),
    (3, 'Tampungan Pembayaran', F, 'Saat ini hanya Rollback. Proses lanjutan (tautan ke Payment BK-…) belum dibahas.', NJ),
])

# ---------------- Sheet: Pertanyaan Terbuka ----------------
ws = wb.create_sheet('Pertanyaan Terbuka')
setup(ws)
section(ws, 1, 'PERTANYAAN TERBUKA')
table(ws, 2, [('A', 'B'), ('C', 'F'), ('G', 'G'), ('H', 'I')], ['Topik', 'Pertanyaan', 'Status', 'Jawaban'], [(*q, ISI) for q in [
    ('Proses lanjutan Pembayaran', 'Tautan baris rekening koran jenis Pembayaran ke Payment (BK-…).', 'Belum dibahas'),
    ('Mutasi tanpa Request Mutasi', 'Apakah mutasi boleh dibuat langsung dari rekening koran tanpa request + approval?', 'Menunggu jawaban atasan'),
    ('Usulan fix jurnal mutasi', 'Akun Piutang / Hutang antar company dan Kas/Bank dalam Perjalanan, serta nomor COA-nya.', 'Menunggu usulan user'),
    ('COA akun lawan Transaksi Bank', 'Nomor COA pendapatan bunga, pajak bunga, biaya transfer per company.', 'Disetting IT Developer'),
    ('Akun pengembalian kasbon', 'Sistem menjurnal Cr 1103-01-14 Piutang Lain-lain Konsultan; Prosedur Pembelian Non Operation (K4) menulis Piutang Kelebihan Kasbon 1103-xx. Mana yang dipakai?', 'Perlu konfirmasi'),
    ('Selisih pengembalian kasbon', 'Prosedur Finance bila bank / tanggal / nominal bukti karyawan beda dengan rekening koran.', 'Perlu dipastikan dengan Finance'),
    ('Isi card Dashboard Alokasi', 'Isi card masih usulan; user akan mengecek.', 'Menunggu review user'),
    ('Detail semua jurnal', 'Pola jurnal tiap proses masih usulan versi awal.', 'Menyusul dari user'),
]])
wb.save(OUT)
print('saved', OUT)
