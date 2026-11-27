# Sumber workbook kamus-alokasi-penerimaan.xlsx. Ubah isi di sini lalu jalankan:
#   py specs/docs/alokasi-penerimaan/build_kamus.py specs/docs/alokasi-penerimaan/kamus-alokasi-penerimaan.xlsx
import sys
from openpyxl import Workbook
from openpyxl.styles import Font, PatternFill, Alignment, Border, Side
from openpyxl.utils import get_column_letter

OUT = sys.argv[1]
F = 'Arial'
HFILL = PatternFill('solid', fgColor='1F4E78')
TFILL = PatternFill('solid', fgColor='DDEBF7')
STATUS_FILL = {'Sistem sekarang': 'E2EFDA', 'Baru': 'FFF2CC', 'Diubah': 'FCE4D6', 'Dihapus': 'EDEDED', 'Usulan': 'FFF2CC', 'Menyusul': 'F2F2F2'}
thin = Side(style='thin', color='BFBFBF')
BORDER = Border(left=thin, right=thin, top=thin, bottom=thin)

wb = Workbook()

def sheet(title, intro, headers, rows, widths, status_col=None):
    ws = wb.create_sheet(title)
    ws['A1'] = title
    ws['A1'].font = Font(name=F, size=14, bold=True, color='1F4E78')
    ws['A2'] = intro
    ws['A2'].font = Font(name=F, size=9, italic=True, color='595959')
    ws['A2'].alignment = Alignment(wrap_text=True, vertical='top')
    ws.merge_cells(start_row=2, start_column=1, end_row=2, end_column=len(headers))
    ws.row_dimensions[2].height = 30
    hr = 4
    for c, h in enumerate(headers, 1):
        cell = ws.cell(row=hr, column=c, value=h)
        cell.font = Font(name=F, size=10, bold=True, color='FFFFFF')
        cell.fill = HFILL
        cell.alignment = Alignment(wrap_text=True, vertical='center')
        cell.border = BORDER
    for r, row in enumerate(rows, hr + 1):
        for c, v in enumerate(row, 1):
            cell = ws.cell(row=r, column=c, value=v)
            cell.font = Font(name=F, size=10, bold=(c == 1))
            cell.alignment = Alignment(wrap_text=True, vertical='top')
            cell.border = BORDER
            if status_col and c == status_col and v in STATUS_FILL:
                cell.fill = PatternFill('solid', fgColor=STATUS_FILL[v])
    for i, w in enumerate(widths, 1):
        ws.column_dimensions[get_column_letter(i)].width = w
    ws.freeze_panes = ws.cell(row=hr + 1, column=2)
    ws.auto_filter.ref = f"A{hr}:{get_column_letter(len(headers))}{hr + len(rows)}"
    return ws

# ---------------- Petunjuk ----------------
ws = wb.active
ws.title = 'Petunjuk'
lines = [
    ('Kamus Alokasi Penerimaan', Font(name=F, size=16, bold=True, color='1F4E78')),
    ('Definisi, penomoran, fungsi menu, jenis alokasi, pola jurnal, dan keputusan untuk perubahan modul Alokasi (menu Penerimaan) dan sub modulnya: tampungan, Mutasi Bank, Transaksi Bank.', Font(name=F, size=10)),
    ('', None),
    ('Isi workbook', Font(name=F, size=11, bold=True)),
    ('Istilah: arti setiap istilah dan contohnya.', Font(name=F, size=10)),
    ('Penomoran: format nomor dokumen, contoh, kapan nomor dibuat.', Font(name=F, size=10)),
    ('Jenis Alokasi: 8 jenis alokasi, arah dana yang boleh, menu tampungan, proses lanjutan.', Font(name=F, size=10)),
    ('Menu & Fungsi: setiap menu dan sub menu beserta fungsinya.', Font(name=F, size=10)),
    ('Pola Jurnal: jurnal per transaksi (usulan versi awal untuk mutasi).', Font(name=F, size=10)),
    ('Keputusan: keputusan user yang membentuk perubahan (ID AP-R-xx di specs/docs/requests.md).', Font(name=F, size=10)),
    ('Pertanyaan Terbuka: hal yang belum diputuskan.', Font(name=F, size=10)),
    ('', None),
    ('Kolom Status', Font(name=F, size=11, bold=True)),
    ('Sistem sekarang = sudah ada di aplikasi, tidak berubah · Baru = belum ada, ditambahkan · Diubah = sudah ada, perilakunya diubah · Dihapus = ditiadakan · Usulan = versi awal, menunggu fix · Menyusul = dibahas belakangan.', Font(name=F, size=10)),
    ('', None),
    ('Sumber', Font(name=F, size=11, bold=True)),
    ('Kode aplikasi (application/modules/alokasi, unlocated_penerimaan, penerimaan_uang, request_mutasi, master_bank), mockup specs/docs/alokasi-penerimaan/index.html, dan keputusan user di specs/docs/requests.md bagian "Alokasi Penerimaan". COA bertanda * di mockup masih contoh.', Font(name=F, size=10)),
    ('', None),
    ('Dokumen ini diperbarui setiap ada perubahan pada modul Alokasi dan sub modulnya. Versi terakhir: 18-11-2026 (sampai AP-R-33).', Font(name=F, size=10, italic=True, color='595959')),
]
for i, (t, fnt) in enumerate(lines, 1):
    c = ws.cell(row=i, column=1, value=t)
    if fnt:
        c.font = fnt
    c.alignment = Alignment(wrap_text=True, vertical='top')
ws.column_dimensions['A'].width = 120
for r in (2, 15, 18):
    ws.row_dimensions[r].height = 30

# ---------------- Istilah ----------------
S, B, D, H, U, M = 'Sistem sekarang', 'Baru', 'Diubah', 'Dihapus', 'Usulan', 'Menyusul'
istilah = [
    ('Rekening Koran', 'Mutasi rekening bank perusahaan dari bank (file CSV). Setiap baris = satu transaksi bank.', 'Rekening koran BCA STM November 2026', 'Penerimaan → Alokasi', S),
    ('Upload Rekening Koran', 'Memasukkan file CSV rekening koran ke sistem. Pilih rekening, preview, lalu proses. Semua baris (uang masuk dan keluar) disimpan berstatus Open.', 'Upload rekening_koran_BCA_SUSTAIN_18112026.csv', 'Penerimaan → Alokasi', S),
    ('Format CSV', 'Cara sistem membaca file rekening koran, ditentukan jenis bank (list_bank). Kode 7 = format BCA ("No. rekening :", ada baris PEND). Kode 22 = format OCBC NISP ("PERIOD :", "ACCOUNT NO :").', 'BCA kode 7, OCBC kode 22', 'Rekening_koran_parser', S),
    ('Rekening (Master Bank)', 'Rekening bank milik perusahaan (tabel ms_bank): bank, no. rekening, nama rekening, COA bank. Company dikenali dari rekening; tidak ada kolom company.', 'BCA 436.300.5287 (STM), BCA 436.400.0300 (SUSTAIN), OCBC NISP 7788.0000.0417 (VUCA)', 'Master Bank', S),
    ('Company', 'Perusahaan pemilik rekening / pemilik buku jurnal: STM, VUCA, SUSTAIN. Setiap company punya database akuntansi (buku) sendiri.', 'Rekening BCA 436.400.0300 → company SUSTAIN', 'Semua', S),
    ('Total Debit / Total Credit (menu Alokasi)', 'Kolom di menu Alokasi dari sudut pandang perusahaan. Total Debit = uang masuk ke rekening; Total Credit = uang keluar.', 'Bunga 85.412 di Total Debit', 'Penerimaan → Alokasi', S),
    ('CR / DB (rekening koran)', 'Kode di rekening koran dari sudut pandang bank. CR = uang masuk ke rekening kita; DB = uang keluar. Di buku perusahaan dibalik: CR → akun Bank Debit, DB → akun Bank Kredit. Sistem menyimpannya dari sisi buku perusahaan: CR di kolom Total Debit (nominal_debit) = uang masuk, DB di kolom Total Credit (nominal_kredit) = uang keluar.', 'BUNGA ... CR → Dr Bank', 'Rekening koran', S),
    ('Alokasi', 'Menentukan setiap baris rekening koran itu transaksi apa (jenis alokasi). Satu baris bisa dipecah (split) ke beberapa jenis, total harus sama dengan nominal baris.', 'Transfer 15.500.000 = Penerimaan Piutang 15.000.000 + Unlocated 500.000', 'Penerimaan → Alokasi', S),
    ('Jenis Alokasi', '8 jenis tujuan alokasi (lihat sheet Jenis Alokasi). Pilihan dibatasi arah dana baris.', 'Mutasi, Transaksi Bank, Pembayaran untuk uang keluar', 'Penerimaan → Alokasi', D),
    ('Arah dana', 'Uang masuk atau uang keluar. Menentukan jenis alokasi yang boleh dipilih.', 'Pembayaran hanya untuk uang keluar', 'Penerimaan → Alokasi', B),
    ('Status Open', 'Baris rekening koran belum dialokasikan sama sekali.', '-', 'Penerimaan → Alokasi', S),
    ('Autodebet pembayaran', 'Tagihan yang ditarik bank langsung dari rekening perusahaan (mis. BPJS). Dialokasikan sebagai Transaksi Bank; arah mengikuti debit/kredit rekening koran (keluar = KK), user memilih COA lawan. Bila tagihan sudah tercatat sebagai hutang, lawannya akun hutang supaya beban tidak dobel.', 'AUTODEBET BPJS KETENAGAKERJAAN 1.850.000 → Dr 2103-01-07 Hutang Asuransi BPJS Ketenagakerjaan / Cr Bank BCA STM', 'Penerimaan → Transaksi Bank', B),
    ('Belum diproses (dashboard)', 'Bagian rekening koran yang belum tuntas: baris Open / sisa Sebagian, draft Penerimaan Piutang (Invoice / Kwitansi Deposit), isi Unlocated dan Alokasi Kalibrasi, Pengembalian Kasbon belum diverifikasi, Mutasi belum Proses Mutasi, Transaksi Bank belum KK/KM, Pembayaran (semua, proses belum dibahas).', 'Card Mutasi: 2 belum diproses, Rp 50.000.000', 'Penerimaan → Dashboard Alokasi', B),
    ('Umur transaksi', 'Selisih hari antara tanggal transaksi rekening koran dan hari ini. Card menampilkan umur tertua dan sebaran 0–7 / 8–30 / >30 hari.', 'Transaksi 04-11-2026 dilihat 18-11-2026 = 14 hari', 'Penerimaan → Dashboard Alokasi', B),
    ('Status Sebagian', 'Baris rekening koran baru sebagian dialokasikan (ada sisa), biasanya setelah rollback satu alokasi. Sisa bisa dialokasikan ulang.', 'Sebagian · sisa 500.000', 'Penerimaan → Alokasi', B),
    ('Tampungan', 'Sub menu yang menampung semua alokasi dengan jenis tertentu, tempat proses lanjutannya dilakukan.', 'Tampungan Mutasi, Transaksi Bank', 'Penerimaan', B),
    ('Nominal Alokasi', 'Nominal bagian alokasi yang masuk ke tampungan itu (bisa lebih kecil dari nominal baris kalau dipecah).', '500.000 dari baris 15.500.000', 'Semua tampungan', B),
    ('Rollback (tampungan)', 'Membatalkan alokasi yang dicentang di menu tampungan itu saja. Alokasi lain di baris yang sama tetap; baris jadi Open atau Sebagian. Baris yang sudah diproses (punya MTR/KK/KM, atau Unlocated yang sudah dijurnal) tidak bisa di-rollback.', 'Rollback Unlocated 500.000, Penerimaan Piutang 15.000.000 tetap', 'Semua tampungan', D),
    ('Proses Unlocated', 'Menjurnal Unlocated di tab Belum Diproses: Debit Bank / Kredit Penerimaan Belum Teridentifikasi, terbentuk 1 nomor Unlocated baru (referensi). Setelah diproses, baris pindah ke tab Sudah Diproses.', 'Transfer PT KAI 30.000.000 → UNL-261100001 + jurnal BUM', 'Penerimaan → Unlocated Penerimaan', B),
    ('Penerimaan Belum Teridentifikasi', 'Akun penampung uang masuk yang belum diketahui asalnya. Dikredit saat Unlocated diproses, didebit saat penerimaan piutang (invoice / kwitansi deposit) dari Unlocated diproses. Nomor COA (diisi user).', 'Kredit 30.000.000 saat UNL-261100001; Debit 30.000.000 saat penerimaan piutang PT KAI', 'Jurnal Unlocated dan Penerimaan Piutang', B),
    ('Pindahkan ke Penerimaan Piutang Invoice / Kwitansi Deposit', 'Dua tombol terpisah di tab Sudah Diproses Unlocated Penerimaan (hanya Unlocated yang sudah dijurnal), mengubah jenis alokasi tanpa rollback. Draft membawa nomor Unlocated sebagai referensi; jurnal penerimaannya Debit Penerimaan Belum Teridentifikasi (bukan Bank). Pindahkan ke Penerimaan Piutang Invoice: jenis 2 → 1, jadi draft di tab Penerimaan Piutang Invoice. Pindahkan ke Penerimaan Piutang Kwitansi Deposit: jenis 2 → 8, jadi draft di tab Penerimaan Piutang Kwitansi Deposit; nomor kwitansi (Menunggu Pembayaran) opsional, 1 data per kwitansi, aturan sama dengan jenis Deposit di Alokasi.', 'Unlocated PT KAI 30.000.000 → draft tab Kwitansi Deposit, kwitansi KD/2026/11/0003', 'Penerimaan → Unlocated Penerimaan', D),
    ('Request Mutasi (MTS)', 'Rencana/pengajuan pindah dana antar rekening perusahaan: bank asal, bank tujuan, nilai. Perlu approval. Belum ada jurnal.', 'MTS-261100007 BCA SUSTAIN → BCA STM 15.000.000', 'Mutasi Bank → Request Mutasi', S),
    ('Approval Mutasi', 'Persetujuan Request Mutasi (Approve / Reject dengan alasan). Hanya request approved yang bisa diproses.', '-', 'Mutasi Bank → Approval Mutasi', S),
    ('Realisasi Mutasi (MTR)', 'Bukti dana request mutasi sudah benar-benar pindah. Dibuat sistem saat baris rekening koran pertama dari request itu diproses. Satu MTS = satu MTR; MTR mengikat sisi keluar dan masuk.', 'MTR-261100001 dari MTS-261100007', 'Penerimaan → Mutasi (kolom Proses); status di Request Mutasi', D),
    ('Proses Mutasi', 'Tombol di tampungan Mutasi untuk baris keluar maupun masuk. User memilih Request Mutasi; sistem membuat MTR (kalau belum ada) atau menautkan baris ke MTR yang ada, dan membuat jurnal sisi itu.', 'Baris keluar BCA SUSTAIN dan baris masuk BCA STM sama-sama pilih MTS-261100007', 'Penerimaan → Mutasi', B),
    ('Sisi keluar / sisi masuk', 'Satu mutasi muncul dua kali di rekening koran: keluar di rekening bank asal, masuk di rekening bank tujuan. Masing-masing diproses dan dijurnal sendiri.', 'Keluar di BCA SUSTAIN, masuk di BCA STM', 'Penerimaan → Mutasi', B),
    ('Mutasi antar company / satu company', 'Antar company: bank asal dan tujuan milik company berbeda (mis. SUSTAIN → STM). Satu company: keduanya milik company yang sama.', 'SUSTAIN → STM (antar company)', 'Mutasi', B),
    ('Jurnal per sisi', 'Setiap sisi mutasi membuat jurnal di buku company rekening korannya, saat sisi itu diproses, dengan tanggal rekening koran masing-masing.', 'BUK di buku SUSTAIN, BUM di buku STM', 'Mutasi', U),
    ('Target Accounting', 'Dulu: pilihan buku (STM/VUCA/SUSTAIN) penerima jurnal di form Request Mutasi dan Transaksi Bank. Dihapus: buku ditentukan rekening.', '-', 'Request Mutasi, Transaksi Bank', H),
    ('Transaksi Bank', 'Transaksi dari bank sendiri yang muncul di rekening koran: bunga jasa giro, pajak bunga, biaya admin, biaya transfer. Satu baris rekening koran = satu transaksi. Hanya dari rekening koran (input manual ditutup).', 'BUNGA JASA GIRO 85.412', 'Penerimaan → Transaksi Bank; Transaksi Bank → Data Transaksi Bank', D),
    ('KM (Kas Masuk)', 'Nomor Transaksi Bank untuk uang masuk (Penerimaan). Jurnal BUM.', 'KM-261100001 (bunga)', 'Transaksi Bank', S),
    ('KK (Kas Keluar)', 'Nomor Transaksi Bank untuk uang keluar (Pengeluaran). Jurnal BUK.', 'KK-261100001 (pajak bunga)', 'Transaksi Bank', S),
    ('BUM / BUK', 'Jenis jurnal: BUM = Bukti Uang Masuk, BUK = Bukti Uang Keluar.', '-', 'Jurnal', S),
    ('Bank (COA Tujuan)', 'Akun bank di jurnal Transaksi Bank, otomatis dari rekening rekening koran. Di sistem disebut "COA Tujuan".', '1101-02-05 Bank BCA Sustain', 'Form Transaksi Bank', D),
    ('Akun Lawan (COA Asal)', 'Akun pasangan bank di jurnal Transaksi Bank, menjelaskan uang itu untuk apa. Di sistem disebut "COA Asal". Dipilih manual oleh user.', 'Pendapatan Bunga Jasa Giro, Admin Charge 7201-01-04', 'Form Transaksi Bank', D),
    ('Mulai bersih (cut-off)', 'Sistem dipakai mulai tanggal tertentu tanpa data awal. Rekening koran diupload mulai tanggal itu; tidak ada transaksi/realisasi manual lama yang perlu ditautkan.', 'Go-live 1 Desember 2026', 'Semua', B),
    ('Tutorial', 'Panduan langkah demi langkah di layar (driver.js): tombol hijau "Tutorial" di baris atas konten (AP-R-36) berisi "Tutorial halaman ini" dan "Alur proses" lintas halaman. Tutorial tidak menyimpan data. Butuh koneksi internet.', 'Alur "Unlocated → Penerimaan Piutang": 10 langkah', 'Semua halaman mockup', B),
    ('Jurnal All', 'Staging semua jurnal sebelum masuk accounting (tr_jurnal, belum posting). Card dan tab per sumber (Invoicing, Penerimaan, Pembayaran, Expense Report, Pencatatan Petty Cash, + Jurnal Bank); tombol + Post membuka Detail jurnal lalu Posting. Yang sudah diposting tampil di Report Jurnal All. Semua jurnal dari proses Alokasi masuk ke sini (AP-R-37).', 'UNL-261100001 belum posting → Posting → Report Jurnal All', 'Accounting → Jurnal All', D),
    ('Tab Jurnal Bank', 'Tab (dan card) baru di Jurnal All untuk jurnal Mutasi (per sisi) dan Transaksi Bank (KM/KK). Filter Kategori Transaksi (Mutasi / Transaksi Bank) dan Arah (uang masuk / keluar). Di sistem sekarang keduanya langsung ke accounting tanpa Jurnal All.', 'BUK/STM/2611/0003 Transaksi Bank KK', 'Accounting → Jurnal All', B),
    ('Kategori Transaksi (tab Penerimaan)', 'Filter baru di tab Penerimaan Jurnal All, seperti filter Kategori Transaksi di tab Pembayaran: Penerimaan Piutang dan Unlocated Penerimaan (jurnal Proses Unlocated).', '-', 'Accounting → Jurnal All', B),
    ('Penerimaan Piutang', 'Alokasi uang masuk ke invoice customer; diproses di menu Penerimaan Piutang (PPh, admin, jurnal).', '-', 'Penerimaan → Penerimaan Piutang', S),
    ('Unlocated Penerimaan', 'Uang masuk yang belum diketahui milik siapa / untuk apa. Ditampung sampai teridentifikasi.', 'Transfer PT KAI 30.000.000 tanpa keterangan invoice', 'Penerimaan → Unlocated Penerimaan', S),
    ('Deposit', 'Alokasi ke kwitansi deposit customer; diproses sebagai Penerimaan Piutang Kwitansi Deposit.', '-', 'Penerimaan → Penerimaan Piutang', S),
    ('Kwitansi Deposit', 'Kwitansi uang muka/deposit customer (tr_kwitansi_deposit). Yang berstatus Menunggu Pembayaran bisa dipasangkan dengan uang masuk.', 'KD/2026/11/0003 PT KAI 30.000.000', 'Kwitansi Deposit', S),
    ('Tab Penerimaan Piutang', 'Menu Penerimaan Piutang punya 3 tab: Penerimaan Piutang Invoice (draft jenis 1), Penerimaan Piutang Kwitansi Deposit (draft jenis 8), Report Penerimaan (yang sudah diproses).', '-', 'Penerimaan → Penerimaan Piutang', S),
    ('Pengembalian Kasbon', 'Uang masuk dari karyawan yang mengembalikan lebih kasbon. Barisnya dialokasikan sebagai Pengembalian Kasbon lalu menjadi draft di Verifikasi Pengembalian Kasbon, tempat Finance memilih Expense Report-nya. Tampungannya hanya status berjalan.', 'Setoran sisa kasbon KS-2026-00201 Rp 250.000', 'Penerimaan → Pengembalian Kasbon', B),
    ('Lebih kasbon (lebih bayar)', 'Selisih saat realisasi Expense Report lebih kecil dari kasbon; harus dikembalikan karyawan ke perusahaan (tr_expense.lebih_bayar).', 'Kasbon 1.000.000, realisasi 750.000 → lebih kasbon 250.000', 'Expense', S),
    ('Verifikasi Pengembalian Kasbon', 'Seperti Penerimaan Piutang: dimulai dari baris rekening koran beralokasi Pengembalian Kasbon (tab Mutasi Bank). Finance klik Proses Verifikasi, memilih sendiri Expense Report-nya (tanpa pencocokan otomatis), membandingkan bank/tanggal/nominal dengan bukti user, mencentang sendiri 3 checklist (bukti valid, mutasi bank cocok, nominal sesuai; tidak ada yang otomatis), lalu menyetujui dan memposting Jurnal 2.', 'Baris SETORAN TUNAI BUDI 04-Nov → pilih ER-2026-00265', 'Expense → Verifikasi Pengembalian Kasbon', D),
    ('Tab Verifikasi Pengembalian Kasbon', 'Mutasi Bank Pengembalian Kasbon (draft baris rekening koran, Proses Verifikasi), Pengajuan Karyawan (expense Menunggu Verifikasi / Ditolak, Tolak), Selesai (sudah diverifikasi dan dijurnal). Expense tanpa baris rekening koran tidak bisa diverifikasi; Finance menunggu.', 'ER-2026-00310 (BCA Sustain) hanya ada di Pengajuan Karyawan', 'Expense → Verifikasi Pengembalian Kasbon', B),
    ('Pembayaran', 'Uang keluar untuk pembayaran ke pihak lain. Tampungan baru; proses lanjutan (tautan ke Payment BK-…) belum dibahas.', 'Transfer ke PT Polytron', 'Penerimaan → Pembayaran', B),
]
sheet('Istilah', 'Arti setiap istilah di modul Alokasi dan sub modulnya. Urut mengikuti alur: upload → alokasi → tampungan → proses.',
      ['Istilah', 'Definisi', 'Contoh', 'Menu / Tempat', 'Status'], istilah, [28, 70, 42, 34, 16], status_col=5)

# ---------------- Penomoran ----------------
nomor = [
    ('Header upload rekening koran', 'NNN/RKN-KRG/<bulan romawi>/YY', '001/RKN-KRG/XI/26', 'Saat upload rekening koran (tr_alokasi.id)', 'NNN urut per tahun; bulan romawi = bulan upload.', 'Alokasi_model::generate_id', S),
    ('Request Mutasi', 'MTS-YYMM + 5 digit', 'MTS-261100007', 'Saat Request Mutasi disimpan', '-', 'request_mutasi (tr_request_mutasi)', S),
    ('Realisasi Mutasi', 'MTR-YYMM + 5 digit', 'MTR-261100001', 'Saat baris rekening koran pertama dari request itu diproses (Proses Mutasi)', 'Satu MTS = satu MTR. Di sistem sekarang dibuat lewat Add Mutasi manual.', 'request_mutasi (tr_request_mutasi_aktual)', D),
    ('Transaksi Bank masuk', 'KM-YYMM + 5 digit', 'KM-261100001', 'Saat baris uang masuk diproses di tampungan Transaksi Bank', 'Sistem sekarang memakai bulan hari ini; usulan: bulan tanggal transaksi.', 'Request_mutasi_model::generate_nokm (tr_request_mutasi_admin)', D),
    ('Transaksi Bank keluar', 'KK-YYMM + 5 digit', 'KK-261100001', 'Saat baris uang keluar diproses di tampungan Transaksi Bank', 'Sistem sekarang memakai bulan hari ini; usulan: bulan tanggal transaksi.', 'Request_mutasi_model::generate_notr (tr_request_mutasi_admin)', D),
    ('Jurnal pengembalian kasbon', 'NNNNN-BUM-<bulan romawi>-YY', '00001-BUM-XI-26', 'Saat verifikasi pengembalian kasbon disetujui', 'Urut per bulan; bulan dari tanggal transfer.', 'Expense::_post_jurnal_pengembalian_kasbon (tr_jurnal)', S),
    ('Kwitansi Deposit', 'KD/YYYY/MM/NNNN', 'KD/2026/11/0003', 'Saat kwitansi deposit dibuat', 'Urut per bulan; dicek terhadap tr_kwitansi_deposit, tr_invoicing, tr_customer_deposit, tr_jurnal.', 'Kwitansi_deposit_model::generate_no_kwitansi', S),
    ('Jurnal mutasi per sisi (mockup)', 'BUK|BUM/<COMPANY>/YYMM/NNNN', 'BUK/SUSTAIN/2611/0001', 'Saat Proses Mutasi tiap sisi', 'Format hanya untuk mockup. Di sistem nomor jurnal BUM/BUK diambil dari counter database akuntansi tiap company.', 'Mockup', U),
]
sheet('Penomoran', 'Format nomor dokumen. YYMM = tahun dan bulan (2611 = November 2026).',
      ['Dokumen', 'Format', 'Contoh', 'Dibuat saat', 'Catatan', 'Sumber / tabel', 'Status'], nomor, [28, 30, 22, 40, 44, 40, 16], status_col=7)

# ---------------- Jenis Alokasi ----------------
jenis = [
    (1, 'Penerimaan Piutang', 'Uang masuk', 'Penerimaan Piutang', 'Pilih invoice, PPh, admin, jurnal penerimaan.', S),
    (2, 'Unlocated Penerimaan', 'Uang masuk', 'Unlocated Penerimaan', 'Ditampung di tab Belum Diproses; Proses = jurnal Debit Bank / Kredit Penerimaan Belum Teridentifikasi + nomor Unlocated. Dari tab Sudah Diproses bisa dipindahkan ke Penerimaan Piutang Invoice (jenis 1) atau Penerimaan Piutang Kwitansi Deposit (jenis 8) lewat dua tombol terpisah. Rollback hanya untuk yang belum diproses.', D),
    (3, 'Pengembalian Kasbon', 'Uang masuk', 'Pengembalian Kasbon (baru, status berjalan)', 'Jadi draft di Expense → Verifikasi Pengembalian Kasbon; Finance memilih Expense Report-nya, jurnal saat disetujui.', B),
    (4, 'Mutasi', 'Masuk dan keluar', 'Mutasi (baru)', 'Proses Mutasi: pilih Request Mutasi → MTR + jurnal per sisi.', B),
    (5, 'Transaksi Bank', 'Masuk dan keluar', 'Transaksi Bank (baru)', 'Proses: form terisi dari rekening koran, user memilih akun lawan → KM/KK + jurnal.', B),
    (6, 'Pembayaran', 'Uang keluar', 'Pembayaran (baru)', 'Belum dibahas (rencana: tautan ke Payment BK-…).', B),
    (7, 'Alokasi Kalibrasi', 'Uang masuk', 'Alokasi Kalibrasi', 'Sudah ada.', S),
    (8, 'Deposit', 'Uang masuk', 'Penerimaan Piutang (Kwitansi Deposit)', 'Pilih kwitansi deposit; diproses di Penerimaan Piutang.', S),
]
sheet('Jenis Alokasi', 'Kode = tr_alokasi_split.jenis_alokasi. Arah dana dibatasi di modal Alokasi Split.',
      ['Kode', 'Jenis Alokasi', 'Arah dana yang boleh', 'Menu tampungan', 'Proses lanjutan', 'Status'], jenis, [8, 24, 20, 34, 60, 16], status_col=6)

# ---------------- Menu & Fungsi ----------------
menu = [
    ('Master Data', 'Master Bank', 'Rekening bank perusahaan dan COA bank-nya.', 'Tambah / ubah rekening', S),
    ('Penerimaan', 'Alokasi', 'Upload rekening koran dan alokasikan setiap baris ke jenis alokasi.', 'Upload Rekening Koran, Alokasi (split), View, Tambah Alokasi Manual (admin)', D),
    ('Penerimaan', 'Penerimaan Piutang', 'Tab Penerimaan Piutang Invoice (draft jenis 1), tab Penerimaan Piutang Kwitansi Deposit (draft jenis 8), tab Report Penerimaan.', 'Proses penerimaan, jurnal, export report', S),
    ('Penerimaan', 'Dashboard Alokasi', 'Card per jenis alokasi (9 card) berisi jumlah dan total nominal belum diproses, umur tertua, sebaran umur; tabel Rincian Belum Diproses. Filter rentang tanggal transaksi, Company (STM/VUCA/SUSTAIN), Bank (menyesuaikan Company). Klik card menampilkan rinciannya di tabel bawah (tidak pindah halaman); tombol Buka menu membawa ke menunya.', 'Filter, Reset, Export to Excel (sheet Ringkasan + Rincian, mengikuti filter; tanpa filter = semua)', B),
    ('Penerimaan', 'Unlocated Penerimaan', 'Tampungan uang masuk yang belum teridentifikasi. Dua tab: Belum Diproses dan Sudah Diproses.', 'Belum Diproses: Proses (jurnal), Rollback. Sudah Diproses: Pindahkan ke Penerimaan Piutang Invoice, Pindahkan ke Penerimaan Piutang Kwitansi Deposit', D),
    ('Penerimaan', 'Alokasi Kalibrasi', 'Tampungan alokasi kalibrasi.', 'Rollback', S),
    ('Penerimaan', 'Pengembalian Kasbon', 'Daftar status berjalan baris rekening koran pengembalian kasbon (Belum diverifikasi / Terverifikasi).', 'Rollback (kalau belum terverifikasi)', B),
    ('Expense', 'Verifikasi Pengembalian Kasbon', 'Tab Mutasi Bank (draft dari alokasi Pengembalian Kasbon), Pengajuan Karyawan, Selesai. Proses Verifikasi: pilih Expense Report, perbandingan dengan bukti user, 3 checklist, setujui.', 'Proses Verifikasi, Setujui & Posting Jurnal, Tolak (alasan)', D),
    ('Penerimaan', 'Mutasi', 'Tampungan baris mutasi antar rekening.', 'Proses Mutasi, Rollback', B),
    ('Penerimaan', 'Transaksi Bank', 'Tampungan bunga, pajak bunga, biaya admin, biaya transfer.', 'Proses (buat KM/KK), Rollback', B),
    ('Penerimaan', 'Pembayaran', 'Tampungan uang keluar untuk pembayaran.', 'Rollback', B),
    ('Penerimaan', 'Historical Alokasi', 'Riwayat aktivitas alokasi.', 'Lihat', S),
    ('Mutasi Bank', 'Request Mutasi', 'Pengajuan pindah dana antar rekening. Company tampil otomatis dari rekening (Target Accounting dihapus).', 'Tambah request', D),
    ('Mutasi Bank', 'Approval Mutasi', 'Persetujuan Request Mutasi.', 'Approve, Reject (alasan)', S),
    ('Mutasi Bank', 'List Mutasi (dihapus, AP-R-33)', 'Tidak ada lagi. Realisasi MTR dan jurnal per sisi dilihat di tampungan Penerimaan → Mutasi (kolom Proses, link Lihat jurnal); status request di Request Mutasi: Terealisasi MTR-….', '-', D),
    ('Transaksi Bank', 'Data Transaksi Bank', 'Daftar KM/KK hasil proses tampungan Transaksi Bank. Input manual ditutup.', 'Lihat', D),
    ('Accounting', 'Jurnal All', 'Staging jurnal sebelum diposting, tampilan seperti sistem. Tab Penerimaan (Kategori Transaksi: Penerimaan Piutang / Unlocated Penerimaan), tab baru Jurnal Bank (Mutasi, Transaksi Bank; Kategori & Arah), tab Expense Report (Pengembalian Kasbon).', '+ Post → Detail jurnal → Posting, Export Excel', D),
    ('Accounting', 'Report Jurnal All', 'Jurnal yang sudah diposting (No. Jurnal Accounting, Tgl Posting), tab sama dengan Jurnal All.', 'Detail, Export Excel', S),
]
sheet('Menu & Fungsi', 'Penempatan menu mengikuti mockup. Di kode, Mutasi dan Transaksi Bank tetap satu modul request_mutasi.',
      ['Grup menu', 'Menu', 'Fungsi', 'Aksi utama', 'Status'], menu, [18, 26, 64, 42, 16], status_col=5)

# ---------------- Pola Jurnal ----------------
jurnal = [
    ('Transaksi Bank masuk (KM)', 'Bunga jasa giro', 'Company rekening', 'Bank', 'Akun lawan (mis. Pendapatan Bunga Jasa Giro)', 'Saat Proses di tampungan Transaksi Bank → Jurnal All tab Jurnal Bank (belum posting), + Post → Posting', D),
    ('Transaksi Bank keluar (KK)', 'Pajak bunga, biaya admin, biaya transfer', 'Company rekening', 'Akun lawan (mis. Admin Charge 7201-01-04)', 'Bank', 'Saat Proses di tampungan Transaksi Bank → Jurnal All tab Jurnal Bank (belum posting), + Post → Posting', D),
    ('Mutasi, sisi keluar', 'Antar company (mis. SUSTAIN → STM)', 'Company bank asal (SUSTAIN)', 'Piutang ke STM (COA belum ada)', 'Bank asal', 'Saat Proses Mutasi baris keluar → Jurnal All tab Jurnal Bank (belum posting)', U),
    ('Mutasi, sisi masuk', 'Antar company (mis. SUSTAIN → STM)', 'Company bank tujuan (STM)', 'Bank tujuan', 'Hutang ke SUSTAIN (COA belum ada)', 'Saat Proses Mutasi baris masuk → Jurnal All tab Jurnal Bank (belum posting)', U),
    ('Mutasi, sisi keluar', 'Satu company', 'Company rekening', 'Kas/Bank dalam Perjalanan (COA belum ada)', 'Bank asal', 'Saat Proses Mutasi baris keluar → Jurnal All tab Jurnal Bank (belum posting)', U),
    ('Mutasi, sisi masuk', 'Satu company', 'Company rekening', 'Bank tujuan', 'Kas/Bank dalam Perjalanan (COA belum ada)', 'Saat Proses Mutasi baris masuk → Jurnal All tab Jurnal Bank (belum posting)', U),
    ('Pengembalian kasbon', 'Lebih kasbon dikembalikan karyawan', 'Company rekening', 'Bank (rekening tujuan)', '1103-01-14 Piutang Lain-lain Konsultan', 'Saat verifikasi disetujui (sekali; alokasi tidak menjurnal) → Jurnal All tab Expense Report (belum posting), + Post → Posting', S),
    ('Request / Approval Mutasi', '-', '-', '-', '-', 'Tidak ada jurnal', S),
    ('Proses Unlocated (BUM)', 'Uang masuk belum diketahui asalnya', 'Company rekening', 'Bank', 'Penerimaan Belum Teridentifikasi (COA diisi user)', 'Saat Proses di tab Belum Diproses Unlocated → Jurnal All tab Penerimaan, Kategori Transaksi Unlocated Penerimaan (belum posting); terbentuk nomor Unlocated', B),
    ('Penerimaan Piutang dari Unlocated', 'Unlocated sudah dipindahkan ke Penerimaan Piutang Invoice / Kwitansi Deposit', 'Company rekening', 'Penerimaan Belum Teridentifikasi (bukan Bank), referensi nomor Unlocated', 'Sesuai penerimaan (Piutang Dagang Usaha, PPh 23, biaya admin, dll.)', 'Saat penerimaan piutang diproses', B),
    ('Alokasi, Rollback, Pindahkan Unlocated', '-', '-', '-', '-', 'Tidak ada jurnal', S),
]
sheet('Pola Jurnal', 'Jurnal mutasi per sisi adalah usulan versi awal; usulan fix menyusul. COA yang "belum ada" disetting IT Developer.',
      ['Transaksi', 'Kondisi', 'Buku (company)', 'Debit', 'Kredit', 'Kapan dibuat', 'Status'], jurnal, [30, 32, 26, 38, 38, 34, 16], status_col=7)

# ---------------- Keputusan ----------------
kep = [
    ('AP-R-02', '17-11-2026', 'Buat sub modul tampungan untuk jenis alokasi yang belum punya tampungan.', 'Tampungan Pengembalian Kasbon, Mutasi, Transaksi Bank, Pembayaran.'),
    ('AP-R-03', '17-11-2026', 'Nama menu = nama jenis alokasi; kolom Nominal Alokasi juga di Unlocated; jenis alokasi dibatasi arah dana.', 'Modal Alokasi Split menyaring pilihan jenis.'),
    ('AP-R-04', '17-11-2026', 'Rollback pilihan B: hanya alokasi di menu itu yang dibatalkan.', 'Status Sebagian di menu Alokasi.'),
    ('AP-R-05', '17-11-2026', 'Unlocated bisa dipindahkan ke Penerimaan Piutang.', 'Tombol Pindahkan ke Penerimaan Piutang.'),
    ('AP-R-06', '17-11-2026', 'Mutasi tetap lewat Request Mutasi + approval (menunggu atasan); proses dari tombol di tampungan; jurnal antar company menyusul.', 'Tampungan Mutasi membuka form modul Mutasi.'),
    ('AP-R-07', '17-11-2026', 'Tidak menambah kolom company di Master Bank; company dikenali dari rekening.', 'Rekening asli: BCA STM, BCA SUSTAIN, OCBC VUCA.'),
    ('AP-R-08', '17-11-2026', 'Di kedua sisi mutasi pilih Request Mutasi (bukan MTR); satu tombol Proses Mutasi.', 'MTR dibuat sistem.'),
    ('AP-R-09', '17-11-2026', 'Jurnal mutasi per sisi; usulan versi awal dulu.', 'Jurnal di buku company rekening masing-masing.'),
    ('AP-R-10', '18-11-2026', 'Target Accounting dihapus.', 'Company otomatis dari rekening.'),
    ('AP-R-11', '18-11-2026', 'Transaksi Bank termasuk biaya transfer; hanya dari rekening koran; 1 baris = 1 transaksi.', 'Input manual Transaksi Bank ditutup.'),
    ('AP-R-12', '18-11-2026', 'Akun lawan Transaksi Bank terisi otomatis dari kata kunci, bisa diganti.', 'Menu Kata Kunci Akun Lawan, preview jurnal.'),
    ('AP-R-13', '18-11-2026', 'Menu Transaksi Bank dipisah dari Mutasi Bank.', 'Grup menu Transaksi Bank; Kata Kunci di Master Data.'),
    ('AP-R-14', '18-11-2026', 'Sistem dipakai mulai bersih tanpa data awal.', 'Tidak ada tautan ke transaksi/realisasi manual lama.'),
    ('AP-R-15', '18-11-2026', 'Buat satu Excel kamus untuk modul Alokasi dan sub modulnya.', 'Workbook ini; diperbarui setiap ada perubahan.'),
    ('AP-R-16', '18-11-2026', 'Tutorial memakai driver.js.', 'Tombol Tutorial per halaman di mockup Alokasi dan Request Payment.'),
    ('AP-R-17', '18-11-2026', 'Tombol tutorial harus langsung terlihat, bukan di bar atas.', '(Diganti AP-R-36: tombol di baris atas konten karena tombol mengambang menutupi Simpan.) Tombol mengambang Tutorial halaman ini di pojok kanan bawah.'),
    ('AP-R-18', '18-11-2026', 'Kata Kunci Akun Lawan tidak dipakai; akun lawan dipilih manual.', 'Menu Kata Kunci Akun Lawan dan isi otomatis dihapus.'),
    ('AP-R-19', '18-11-2026', 'Alokasi Pengembalian Kasbon masuk ke Verifikasi Pengembalian Kasbon, dicocokkan dengan bukti transfer user (bank vs bukti).', '(Diganti AP-R-23: mulai dari rekening koran, expense dipilih manual.) Perbandingan bank/tanggal/nominal; checklist mutasi otomatis; tanpa pasangan tidak bisa disetujui.'),
    ('AP-R-20', '18-11-2026', 'Tampungan Pengembalian Kasbon = status berjalan; 3 checklist tetap; tanpa bukti user Finance menunggu.', 'Mendisiplinkan Finance upload rekening koran dan karyawan upload bukti.'),
    ('AP-R-21', '18-11-2026', 'Unlocated Penerimaan bisa dipindahkan ke Penerimaan Piutang Invoice atau Penerimaan Piutang Kwitansi Deposit.', '(Diganti AP-R-22: dua tombol terpisah.) Modal pindah memilih tujuan; kwitansi deposit opsional, 1 data per kwitansi; kolom Jenis di Penerimaan Piutang.'),
    ('AP-R-22', '18-11-2026', 'Satu tombol untuk satu jenis: Unlocated punya tombol Pindahkan ke Penerimaan Piutang Invoice dan Pindahkan ke Penerimaan Piutang Kwitansi Deposit.', 'Mengikuti tab Penerimaan Piutang yang memisahkan Invoice dan Kwitansi Deposit.'),
    ('AP-R-23', '18-11-2026', 'Verifikasi Pengembalian Kasbon seperti Penerimaan Piutang: mulai dari baris rekening koran, Finance memilih Expense Report sendiri, tidak ada pencocokan otomatis.', 'Menggantikan AP-R-19 (antrian per expense + tanda cocok otomatis).'),
    ('AP-R-24', '18-11-2026', 'Checklist verifikasi pengembalian kasbon (termasuk Mutasi Rekening Bank Cocok) dicentang manual oleh Finance, tidak otomatis.', 'Seperti sistem sekarang: Finance yang menyatakan sudah dicek.'),
    ('AP-R-25', '18-11-2026', 'Dashboard Alokasi sebagai menu baru: card per jenis alokasi yang belum diproses, filter rentang tanggal / Company / Bank, Export to Excel mengikuti filter.', 'Isi card sesuai usulan dulu, akan dicek user.'),
    ('AP-R-26', '18-11-2026', 'Klik card Dashboard Alokasi menampilkan detail di bawahnya, tidak langsung pindah ke modul.', 'Card tersorot, Rincian difilter ke jenis itu; tombol Buka menu untuk pindah.'),
    ('AP-R-27', '18-11-2026', '(Salah paham, dibatalkan oleh AP-R-28.) List Mutasi sempat dijadikan tab di menu Penerimaan → Mutasi.', 'Dikembalikan.'),
    ('AP-R-28', '18-11-2026', '(Tab List Mutasi dihapus oleh AP-R-33.) Request Mutasi dan List Mutasi digabung dalam satu menu (Mutasi Bank → Request Mutasi) dengan dua tab. Menu Penerimaan → Mutasi tetap seperti semula.', 'Mengurangi jumlah menu; sidebar Mutasi Bank tinggal Request Mutasi dan Approval Mutasi.'),
    ('AP-R-29', '18-11-2026', 'Autodebet pembayaran dicatat lewat Transaksi Bank; arah dari rekening koran, user hanya memilih COA lawan (tanpa field kategori).', 'Pilihan COA lawan = seluruh COA master seperti sistem sekarang; mockup memberi contoh 2103-01-07 dan 5101-01-03.'),
    ('AP-R-30', '18-11-2026', 'Transaksi Bank diberi keterangan uang masuk / uang keluar.', 'Form: Pengeluaran (uang keluar) / Penerimaan (uang masuk) + label; Data Transaksi Bank: label Uang Masuk / Uang Keluar di bawah kode.'),
    ('AP-R-31', '18-11-2026', 'Flow process BPMN to-be dibuat: Alokasi Penerimaan beserta modul terhubung (flow-alokasi-penerimaan.drawio) dan Request Payment ke Payment (folder request-payment).', 'Detail semua jurnal menyusul dari user.'),
    ('AP-R-32', '18-11-2026', 'Keterangan arah di Transaksi Bank mengikuti kolom sistem: uang masuk = Total Debit, uang keluar = Total Credit (bukan kode CR/DB bank).', 'Parser rekening koran menyimpan CR ke nominal_debit dan DB ke nominal_kredit (sisi buku perusahaan).'),
    ('AP-R-36', '20-11-2026', 'Tutorial mockup: tombol Tutorial di baris atas konten (tidak menutupi Simpan), menu Tutorial halaman ini + alur proses lintas halaman.', 'Menggantikan tombol mengambang kanan bawah (AP-R-17). Berlaku juga di Request Payment dan Monitoring Piutang.'),
    ('AP-R-35', '20-11-2026', 'Unlocated Penerimaan punya tab Belum Diproses dan Sudah Diproses. Proses = jurnal Debit Bank / Kredit Penerimaan Belum Teridentifikasi + 1 nomor Unlocated baru. Pindah ke Penerimaan Piutang hanya dari Sudah Diproses.', 'Jurnal penerimaan piutang dari Unlocated: Debit Penerimaan Belum Teridentifikasi (bukan Bank) dengan referensi nomor Unlocated.'),
    ('AP-R-37', '23-11-2026', 'Jurnal All ditambahkan ke mockup untuk melihat jurnal yang terbentuk. Semua jurnal Alokasi masuk Jurnal All (belum posting) lalu diposting. Tab Penerimaan + filter Jenis (Penerimaan Piutang / Unlocated Penerimaan); tab baru Jurnal Bank (Mutasi, Transaksi Bank; filter Jenis & Arah); Pengembalian Kasbon di tab Expense.', 'Mutasi dan Transaksi Bank tidak lagi langsung ke accounting. Pengembalian Kasbon: jurnal dibuat saat disetujui, diposting dari Jurnal All. Unlocated yang sudah diproses boleh dipindah walau jurnalnya belum diposting.'),
    ('AP-R-42', '27-11-2026', 'Yang siap dikerjakan IT hanya Alokasi → Unlocated Penerimaan → Penerimaan Piutang (jurnal saja). Penerimaan Piutang tampil dan berproses seperti sistem; jurnalnya Debit Penerimaan Belum Teridentifikasi bila dari Unlocated, Debit Bank bila dari rekening koran. Halaman lain DRAFT (banner merah).', 'Preview jurnal di form proses tanpa kotak info (hanya di Jurnal All). Tanggal jurnal Unlocated = tanggal bank di rekening koran.'),
    ('AP-R-41', '26-11-2026', 'Unlocated Penerimaan punya tab ketiga Daftar UNL: record semua nomor Unlocated sejak dibuat sampai diterima (No UNL, Tgl & bank rekening koran, keterangan, nominal, jurnal UNL, status Belum dipindahkan / Draft di Penerimaan Piutang / Sudah diterima, dipakai di no penerimaan + invoice / kwitansi + customer, jurnal penerimaan). Tidak hilang walau sudah dipindahkan.', 'Dicari dua arah: kotak cari Daftar UNL membaca No UNL, bank, keterangan, nominal, invoice / kwitansi, customer, no penerimaan; menu Alokasi menampilkan nomor UNL di Status Alokasi (klik = buka Daftar UNL) dan kotak cari Alokasi membaca nomor UNL; referensi UNL di Penerimaan Piutang bisa diklik. Card Belum diterima = saldo yang seharusnya sama dengan akun Penerimaan Belum Teridentifikasi per company.'),
    ('AP-R-40', '26-11-2026', 'Penerimaan Piutang punya proses lanjutan seperti sistem penerimaan_uang: Proses di draft → form customer + PPh 23 dipotong + centang invoice / kwitansi → alokasi per invoice (PPh 23 default 2% Penjualan, Alokasi, Biaya Admin maks Rp 10.000 total, Lebih Bayar) dengan Kontrol 0 → Proses: nomor NNNNN/PU/XI/26, draft ke Report Penerimaan, jurnal ke Jurnal All · Penerimaan (belum posting).', 'Jurnal per invoice: Dr kas / Dr PPh 23 Disetor / Dr Biaya Administrasi Bank / Cr Deposit Customer (lebih bayar) / Cr Piutang Dagang Usaha (saldo awal) / Dr Piutang (sisa). Draft dari Unlocated: baris kas = Debit Penerimaan Belum Teridentifikasi (bukan Bank). Kwitansi Deposit harus lunas penuh tanpa PPh/lebih bayar. Pakai Deposit tidak dimockup. COA PPh 23 Disetor (sistem dua kode) dan Penerimaan Belum Teridentifikasi = diisi user.'),
    ('AP-R-39', '26-11-2026', 'Semua tampilan jurnal di Alokasi (preview Proses Unlocated, Proses Mutasi, Form Transaksi Bank, Verifikasi Pengembalian Kasbon, Lihat jurnal, Detail jurnal di Jurnal All) memakai template Jurnal All sistem (jurnal_all/views/preview.php): kotak info No. Transaksi, No. Referensi, Company, Divisi, Tgl Jurnal, Status, Kategori Transaksi + tabel Tanggal, Tipe, No. COA, Nama COA, Keterangan, No. Reff, Debit, Kredit + GRAND TOTAL + keterangan seimbang.', 'Setiap baris jurnal punya keterangan sendiri yang menjelaskan transaksinya (mis. Uang masuk ke Bank BCA STM - <keterangan> (lawan ...); Mutasi keluar MTS-... dari ... ke ...; Pengembalian lebih kasbon ... - pelapor). Divisi belum diketahui (tampil -).'),
    ('AP-R-38', '23-11-2026', 'Jurnal All di mockup disamakan dengan tampilan sistem (jurnal_all/views/workspace.php): card + tab per sumber, filter Tahun / Bulan / Company, kolom No. / Tgl Jurnal / No. Transaksi / No. Referensi / Keterangan / Company / Debit / Kredit, tombol + Post → Detail jurnal → Posting, Report Jurnal All untuk yang sudah diposting.', 'Filter Jenis diganti Kategori Transaksi (sama seperti tab Pembayaran); tab Expense = Expense Report.'),
    ('AP-R-33', '18-11-2026', 'List Mutasi dihapus.', 'Realisasi sudah otomatis dari Proses Mutasi; MTR dan jurnal per sisi cukup dilihat di tampungan Mutasi, status di Request Mutasi.'),
]
sheet('Keputusan', 'Ringkasan keputusan user. Kutipan lengkap ada di specs/docs/requests.md bagian "Alokasi Penerimaan".',
      ['ID', 'Tanggal', 'Keputusan', 'Dampak'], kep, [10, 12, 70, 54])

# ---------------- Pertanyaan Terbuka ----------------
tanya = [
    ('Mutasi tanpa Request Mutasi', 'Apakah mutasi boleh dibuat langsung dari rekening koran tanpa request + approval?', 'Menunggu jawaban atasan'),
    ('Usulan fix jurnal mutasi', 'Akun Piutang/Hutang antar company dan Kas/Bank dalam Perjalanan, serta nomor COA-nya.', 'Menunggu usulan user'),
    ('COA akun lawan Transaksi Bank', 'Nomor COA pendapatan bunga, pajak bunga, biaya transfer per company.', 'Disetting IT Developer'),
    ('Selisih pengembalian kasbon', 'Prosedur Finance bila bank/tanggal/nominal bukti user beda dengan rekening koran (sementara mengikuti 3 checklist).', 'Perlu dipastikan dengan Finance'),
    ('Proses lanjutan Pembayaran', 'Tautan ke Payment (BK-…) dari menu Payment List.', 'Belum dibahas'),
    ('Isi card Dashboard Alokasi', 'Isi card (jumlah, total, umur tertua, sebaran umur) masih usulan; user akan mengecek.', 'Menunggu review user'),
    ('Detail semua jurnal', 'Pola jurnal tiap proses (alokasi, mutasi, transaksi bank, pengembalian kasbon, payment) masih usulan versi awal.', 'Menyusul dari user'),
    ('COA dan nomor Unlocated', 'Nomor COA Penerimaan Belum Teridentifikasi, format nomor Unlocated (mockup: UNL-YYMMNNNNN), dan tipe jurnalnya (mockup: BUM).', '(diisi user)'),
    ('Nama tampungan Transaksi Bank', 'Ada dua menu bernama "Transaksi Bank" (tampungan dan grup menu). Perlu diganti salah satu?', 'Menunggu jawaban'),
]
sheet('Pertanyaan Terbuka', 'Hal yang belum diputuskan.', ['Topik', 'Pertanyaan', 'Status'], tanya, [34, 80, 26])

wb.save(OUT)
print('saved', OUT)
