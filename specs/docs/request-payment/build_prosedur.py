# Sumber workbook prosedur-pembayaran.xlsx (Prosedur Pembayaran: dokumen pengeluaran -> Request Payment -> Payment).
# Format mengikuti specs/input/Prosedur Pembelian Non Operation.xlsx: halaman depan memuat semua jalur,
# langkah Request Payment -> Payment, dan jurnal Payment semua jalur. Prosedur tiap jalur pengajuan ada di
# specs/docs/prosedur-pengajuan-pembayaran/ (satu file per jalur, RP-R-22). Jalankan:
#   py specs/docs/request-payment/build_prosedur.py specs/docs/request-payment/prosedur-pembayaran.xlsx
# Sumber: keputusan RP-R-xx (specs/docs/requests.md), mockup index.html, flow-request-payment.drawio,
# hasil telusur sistem specs/docs/prosedur-pengajuan-pembayaran/ (jalur dokumen dan COA apa adanya, PP-R-01),
# specs/input/Request Payment/JURNAL PEMBAYARAN - SSC.xlsx. Modul, jalur, dan approval tidak diubah (RP-R-22).
import os
import sys
from openpyxl import Workbook
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from prosedur_xlsx import ISI, setup, title, section, konsep, steps, table, inputs, journal_header, journal, note

OUT = sys.argv[1]
NJ = 'Tidak Ada Jurnal'
SF = 'Staff Finance'
BANK = 'COA bank (ms_bank)'
BERSAMA = 'Request Payment → Approval → Record → transfer → Payment'
wb = Workbook()

# ---------------- Sheet 1: Prosedur Pembayaran ----------------
ws = wb.active
ws.title = 'Prosedur Pembayaran'
setup(ws)
title(ws, 1, 'PROSEDUR PEMBAYARAN (REQUEST PAYMENT → PAYMENT)')
section(ws, 2, 'A. KONSEP')
r = konsep(ws, 3, [
    ('Tujuan', 'Mengatur alur pembayaran semua dokumen pengeluaran, dari dokumen disetujui di modul asalnya sampai transfer tercatat dan terjurnal di Payment.'),
    ('Jalur dokumen', '6 jalur: Transport, Kasbon, Expense, Periodik, Cash / Direct Payment, Petty Cash. Modul, jalur, dan approval di modul asal tetap seperti sistem sekarang.'),
    ('Hubungan dengan prosedur jalur', 'Setiap jalur pengajuan punya prosedur sendiri (bagian B) sampai dokumen disetujui di modul asal. Setelah itu semua jalur masuk prosedur ini: Request Payment → Approval Request Payment → Record → transfer → Payment (bagian C).'),
    ('Batch pengajuan (PGJ)', '1 PGJ = 1 company = 1 batch approval. Satu PGJ berisi 1 atau lebih transfer.'),
    ('Transfer', '1 transfer = 1 rekening tujuan = 1 baris di Excel acuan = 1 kali kirim uang = 1 Payment. Default 1 dokumen = 1 transfer; beberapa dokumen boleh digabung walau jalur, request by, atau penerima beda. Gabung dulu, baru isi rekening tujuan.'),
    ('Admin bank', 'Ditentukan di Request Payment, 1x per transfer: 0 / 2.500 / 6.500, ditanggung perusahaan atau penerima.'),
    ('PPN / PPh', 'Dicentang per dokumen di Request Payment (PPh 23 dan PPh 21 tidak bisa bersamaan). DIBAYARKAN = DPP + PPN − PPh. Di Payment kolomnya sama dan terkunci.'),
    ('Jurnal Payment', 'Dr akun sesuai jalur (bagian D), Dr 1106-01-06 PPN, Cr 2104-01-02 PPh 21 / 2104-01-03 PPh 23, Dr 7201-01-04 Admin Charge bila admin ditanggung perusahaan, Cr COA bank 1x per transfer. COA mengikuti sistem.'),
])
r += 1
section(ws, r, 'B. JALUR PENGAJUAN PEMBAYARAN (PROSEDUR MASING-MASING)')
r = table(ws, r + 1, [('A', 'B'), ('C', 'D'), ('E', 'E'), ('F', 'G'), ('H', 'I')],
          ['Jalur', 'Dokumen dibuat oleh', 'Dibuat di (menu)', 'Approval sebelum Request Payment', 'Prosedur jalur'], [
    ('Transport', 'User', 'Transportasi → Pengajuan Penggantian Transport', 'Pengecekan Finance Penggantian Transport', 'Prosedur Transport'),
    ('Kasbon', 'User', 'Kasbon (atau kasbon project dari sistem konsultan)', 'Kasbon Approval by Finance → Kasbon Approval by Management', 'Prosedur Kasbon'),
    ('Expense', 'User', 'Expense Report', 'Expense Approval By Finance', 'Prosedur Expense'),
    ('Periodik', ISI, 'Pengajuan Pembayaran Periodik', 'Approval Pengajuan Periodik (per item)', 'Prosedur Periodik'),
    ('Cash / Direct Payment', 'User / Purchasing; Direct Payment ' + ISI, 'PR → Metode Pembelian non-PO; Direct Payment dari sistem konsultan', 'Approval Non PO; Direct Payment ' + ISI, 'Prosedur Cash / Direct Payment'),
    ('Petty Cash', 'User', 'Expense Petty Cash (Pencatatan → Pelaporan); Petty Cash VUCA & Sustain (Payment Hutang)', 'Approval Pelaporan Petty Cash; Payment Hutang tanpa approval', 'Prosedur Petty Cash'),
])
r += 1
section(ws, r, 'C. LANGKAH PROSEDUR PEMBAYARAN')
r = steps(ws, r + 1, [
    (1, 'Pilih dokumen di Request Payment', SF, 'Dokumen yang sudah disetujui di modul asal (lihat prosedur jalurnya) tampil di tab Belum Dibayar. Centang dokumen dari 1 company; centang PPN / PPh 23 / PPh 21 per dokumen.', NJ),
    (2, 'Atur Pembayaran', SF, 'Gabung dokumen jadi transfer (opsional), isi rekening tujuan (Bank, No. Rekening, Nama Penerima), admin dan penanggungnya, lampiran tambahan (opsional).', NJ),
    (3, 'Ajukan untuk approval (PGJ)', SF, 'Pengajuan tercatat sebagai PGJ dan masuk Approval Request Payment.', NJ),
    (4, 'Approval Request Payment', ISI, 'Keputusan per dokumen: setujui, atau tolak dengan alasan (dokumen kembali ke Request Payment). Approver boleh mengedit rekening tujuan saja. Transfer yang semua dokumennya ditolak batal.', NJ),
    (5, 'Print Excel acuan transfer (Record Request Payment)', SF, 'Excel per PGJ: 1 baris per dokumen, kolom per transfer di-merge. Print Dokumen memuat lampiran awal dan lampiran tambahan.', NJ),
    (6, 'Transaksi di internet banking', ISI, 'Transfer per baris transfer di Excel. Admin ditanggung penerima: nominal transfer = DIBAYARKAN − admin.', NJ),
    (7, 'Input Payment ke program', SF, 'Payment List → Payment → Pilih Jenis Payment → pilih 1 transfer → Proses. Form Payment: Pilih Bank, Payment Bank, Bank Charge (default admin pengajuan, bisa diedit), upload bukti bayar (wajib), Keterangan Pembayaran (wajib bila ada selisih). Kontrol 0, jurnal balance.', 'Jurnal P1–P8 sesuai jalur, variasi V1–V3 (sheet Jurnal Pembayaran)'),
    (8, 'Payment tersimpan', 'Sistem', 'Nomor BK-YYMM-NNNN; di Record transfer berstatus Sudah dibayar.', NJ),
    (9, 'Uang keluar muncul di rekening koran', 'Finance', 'Dialokasikan jenis Pembayaran di Penerimaan → Alokasi (lihat Prosedur Penerimaan). Biaya admin bank terpisah dialokasikan sebagai Transaksi Bank.', NJ),
])
r += 1
section(ws, r, 'D. JALUR DAN AKUN DEBIT DI PAYMENT')
r = table(ws, r + 1, [('A', 'B'), ('C', 'E'), ('F', 'G'), ('H', 'I')],
          ['Jalur', 'Akun debit di jurnal Payment (sesuai sistem)', 'Jurnal lain di luar Payment (di prosedur jalur)', 'Jurnal Payment'], [
    ('Transport', 'COA transport yang dipilih di Transportasi', '-', 'P1'),
    ('Kasbon', '1103-01-14 Piutang Lain-lain Konsultan', 'Realisasi kasbon (Expense Report); pengembalian sisa lewat Prosedur Penerimaan; kekurangan dibayar lewat P4', 'P2, P4'),
    ('Expense', 'Tanpa kasbon: COA item. Kurang bayar kasbon: 9999-99-99 Hutang Expense / Reimburse Karyawan', '-', 'P3, P4'),
    ('Periodik', 'COA tiap item', '-', 'P5'),
    ('Cash / Direct Payment', 'COA tiap item (PR atau master biaya konsultan)', '-', 'P6'),
    ('Petty Cash', 'Refill: 1101-01-02 Kas Kecil. Payment Hutang: Hutang ke STM', 'Jurnal pencatatan petty cash', 'P7, P8'),
])

# ---------------- Sheet 2: Jurnal Pembayaran ----------------
ADM = ('adm', 'Admin bank per transfer (ditanggung perusahaan)', 2500)


def pay(base, ppn=False, pph=False):
    """Baris PPN, admin, PPh, bank untuk jurnal Payment dengan nilai dasar {base}."""
    rows, cr = [], '{%s}' % base
    if ppn:
        rows.append(('1106-01-06', 'PPN', 'ROUND({%s}*{ppn},0)' % base, None))
        cr += '+ROUND({%s}*{ppn},0)' % base
    rows.append(('7201-01-04', 'Admin Charge', '{adm}', None))
    cr += '+{adm}'
    if pph:
        rows.append(('2104-01-03', 'PPh 23', None, 'ROUND({%s}*{pph},0)' % base))
        cr += '-ROUND({%s}*{pph},0)' % base
    rows.append((BANK, 'Bank', None, cr))
    return rows


ws = wb.create_sheet('Jurnal Pembayaran')
setup(ws)
section(ws, 1, 'JURNAL PAYMENT')
r = note(ws, 2, 'Jurnal terbentuk di langkah C7 (Input Payment). Cr Bank 1x per transfer. COA mengikuti sistem (Pembayaran_material_model::set_jurnal). Jurnal di luar Payment (realisasi kasbon, pencatatan petty cash) ada di prosedur jalurnya.')
r, ref = inputs(ws, r + 1, [
    ('trn', 'Transport: penggantian transport', 894000),
    ('kas', 'Kasbon: nilai kasbon', 2000000),
    ('exp', 'Expense tanpa kasbon', 150000),
    ('kur', 'Kekurangan kasbon (dari Expense Report)', 250000),
    ('per', 'Periodik: tagihan internet (DPP)', 850000),
    ('cash', 'Cash / Direct Payment: pembelian ATK (DPP)', 1250000),
    ('pc', 'Petty Cash: refill / hutang', 500000),
    ('ppn', 'Tarif PPN (contoh)', 0.11), ('pph', 'Tarif PPh 23 (contoh)', 0.02), ADM,
    ('ex2', 'Variasi: Expense digabung 1 transfer dengan Transport', 185000),
    ('kom', 'Variasi: komisi (DPP, potong PPh 21)', 1000000), ('pph21', 'Tarif PPh 21', 0.025),
    ('admbukti', 'Variasi: admin di bukti bayar (pengajuan 2.500)', 1500),
])
r += 1
section(ws, r, 'JURNAL PAYMENT PER JALUR')
r = journal_header(ws, r + 1)
for j in [
    ('P1', 'Transport', 'C7', SF, 'Angkutan umum biasanya tanpa PPN dan PPh.\nContoh: 894.000 + admin 2.500.',
     [('dari Transportasi', 'Biaya transport', '{trn}', None)] + pay('trn')),
    ('P2', 'Kasbon', 'C7', SF, 'Kasbon ditransfer ke penerima.\nContoh: 2.000.000 + admin 2.500.',
     [('1103-01-14', 'Piutang Lain-lain Konsultan', '{kas}', None)] + pay('kas')),
    ('P3', 'Expense tanpa kasbon', 'C7', SF, 'Reimburse ke karyawan.\nContoh: 150.000 + admin 2.500.',
     [('COA item', 'Biaya sesuai item', '{exp}', None)] + pay('exp')),
    ('P4', 'Kekurangan kasbon', 'C7', SF, 'Kekurangan dari Expense Report (Prosedur Kasbon) dibayar ke karyawan.\nContoh: 250.000.',
     [('9999-99-99', 'Hutang Expense / Reimburse Karyawan', '{kur}', None)] + pay('kur')),
    ('P5', 'Periodik', 'C7', SF, 'Per item.\nContoh: DPP 850.000, PPN, PPh 23, admin 2.500.',
     [('COA item', 'Biaya sesuai item', '{per}', None)] + pay('per', ppn=True, pph=True)),
    ('P6', 'Cash / Direct Payment', 'C7', SF, 'Dibayar langsung ke vendor.\nContoh: DPP 1.250.000, PPN, admin 2.500.',
     [('COA item', 'Biaya sesuai item', '{cash}', None)] + pay('cash', ppn=True)),
    ('P7', 'Petty Cash: refill kas kecil', 'C7', SF, 'Buku STM.\nContoh: 500.000.',
     [('1101-01-02', 'Kas Kecil', '{pc}', None), (BANK, 'Bank STM', None, '{pc}')]),
    ('P8', 'Petty Cash: payment hutang VUCA ke STM', 'C7', SF, 'Buku VUCA (SUSTAIN memakai 2103-01-02). Sistem sekarang menjurnal berbeda (lihat Pertanyaan Terbuka).',
     [('2103-01-01', 'Hutang VUCA ke STM', '{pc}', None), (BANK, 'Bank VUCA', None, '{pc}')]),
]:
    r = journal(ws, r, *j, ref=ref)
r += 1
section(ws, r, 'VARIASI PAYMENT (BERLAKU UNTUK SEMUA JALUR)')
r = journal_header(ws, r + 1)
for j in [
    ('V1', 'Beberapa dokumen digabung 1 transfer', 'C7', SF, 'Admin 1x dan Cr Bank 1x per transfer.\nContoh: Transport 894.000 + Expense 185.000.',
     [('dari Transportasi', 'Biaya transport', '{trn}', None), ('COA item', 'Biaya expense', '{ex2}', None), ('7201-01-04', 'Admin Charge', '{adm}', None), (BANK, 'Bank', None, '{trn}+{ex2}+{adm}')]),
    ('V2', 'Admin ditanggung penerima', 'C7', SF, 'Komisi 1.000.000 potong PPh 21 = DIBAYARKAN 975.000. Transfer 968.500, bank memotong admin 6.500; total keluar bank 975.000. Tidak ada baris Admin Charge.',
     [('COA item', 'Biaya komisi', '{kom}', None), ('2104-01-02', 'PPh 21', None, 'ROUND({kom}*{pph21},0)'), (BANK, 'Bank', None, '{kom}-ROUND({kom}*{pph21},0)')]),
    ('V3', 'Bank Charge beda dari bukti bayar', 'C7', SF, 'Admin di pengajuan 2.500, di bukti 1.500. Payment Bank dan Bank Charge diubah sesuai bukti; Keterangan Pembayaran wajib.',
     [('1103-01-14', 'Piutang Lain-lain Konsultan', '{kas}', None), ('7201-01-04', 'Admin Charge', '{admbukti}', None), (BANK, 'Bank', None, '{kas}+{admbukti}')]),
]:
    r = journal(ws, r, *j, ref=ref)

# ---------------- Pertanyaan Terbuka ----------------
ws = wb.create_sheet('Pertanyaan Terbuka')
setup(ws)
section(ws, 1, 'PERTANYAAN TERBUKA')
table(ws, 2, [('A', 'B'), ('C', 'F'), ('G', 'G'), ('H', 'I')], ['Topik', 'Pertanyaan', 'Status', 'Jawaban'], [(*q, ISI) for q in [
    ('Approver Request Payment', 'Siapa approver PGJ? Mockup dan flow process memakai Direktur, Prosedur Pembelian Non Operation menulis Dept Head Finance.', 'Perlu konfirmasi'),
    ('Pelaku transfer internet banking', 'Siapa yang melakukan transfer? Prosedur Pembelian Non Operation menulis Direktur.', 'Perlu konfirmasi'),
        ('Jurnal Payment Hutang petty cash', 'Sistem menjurnal Dr COA item / Cr Kas Kecil tanpa Cr Bank; prosedur memakai Dr Hutang ke STM / Cr Bank (P8).', 'Perlu konfirmasi'),
    ('Tarif PPN', 'Mockup memakai PPN 11% × DPP; file JURNAL PEMBAYARAN - SSC memakai DPP = harga × 11/12, PPN = DPP × 12%.', 'Perlu konfirmasi'),
    ('Detail jurnal', 'Detail semua jurnal menyusul dari user (RP-R-17).', 'Menyusul'),
    ('Tampungan Pembayaran (Alokasi)', 'Tautan baris rekening koran jenis Pembayaran ke Payment BK-… belum dibahas.', 'Belum dibahas'),
    ('Kategori Pinjaman', 'Pencairan Peminjaman lewat Request Payment → Payment; jalurnya belum ada di prosedur.', 'Belum dibahas'),
    ('Lampiran tambahan di Approval', 'Apakah approver juga bisa melihat / menambah lampiran tambahan di halaman Approval?', 'Belum dijawab'),
]])
wb.save(OUT)
print('saved', OUT)
