<?php
// Render the real detail page view (mockup layout) with fixtures; no DB or browser.
define('BASEPATH', __DIR__);
require dirname(__DIR__,3).'/helpers/mockup_format_helper.php';
set_error_handler(function ($severity, $message, $file, $line) { throw new ErrorException($message, 0, $severity, $file, $line); });
require dirname(__DIR__) . '/libraries/Penerimaan_calculation.php';
function site_url($uri = '') { return '/' . ltrim($uri, '/'); }
function base_url($uri = '') { return '/' . ltrim($uri, '/'); }
function detailCheck($ok, $message) { if (!$ok) throw new RuntimeException($message); }
function renderPage($data) { extract($data); ob_start(); include dirname(__DIR__) . '/views/detail_page.php'; return ob_get_clean(); }
function textOf($html) { return trim(preg_replace('/\s+/', ' ', preg_replace('/<style.*?<\/style>/s', ' ', preg_replace('/<[^>]+>/', ' ', preg_replace('/<style.*?<\/style>/s', ' ', $html))))); }
function kontrolOf($html) { preg_match('~<span>Kontrol</span><span class="[^"]*">([^<]*)</span>~', $html, $m); return $m[1] ?? null; }

$header = array('no_surat'=>'00040/RKN-KRG/XI/26','id_customer'=>'C1','nm_customer'=>'PT Contoh','nominal_penerimaan_bank'=>'1000000.00','pph23_dipotong'=>'Y','created_date'=>'2026-11-02 10:00:00','jenis_penerimaan'=>'');
$alokasi = array('tanggal_transaksi'=>'2026-10-30','nm_bank'=>'Bank BCA','rekening'=>'123','nilai_terpakai'=>'1000000.00');
$row = array('id_inv'=>'1','no_invoice'=>'INV-1','tax_treatment'=>'','consulting_snapshot'=>'','pph23'=>'0.00','total_akhir_jurnal'=>'1000000.00','penerimaan'=>'1000000.00','biaya_admin'=>'0.00','sisa_piutang'=>'0.00','dep'=>'0.00','lebih'=>'0.00','id_spk_penawaran'=>'SPK-1','nm_customer'=>'PT Contoh');
$j = function ($coa, $name, $dr, $cr, $ket) { return array('tgl_jurnal'=>'2026-10-30','coa'=>$coa,'nm_company'=>'STM','nm_coa'=>$name,'keterangan'=>$ket,'debit'=>$dr,'kredit'=>$cr); };
$jurnal = array($j('1101-02-01','Bank BCA','1000000.00','0.00','Bank BCA - INV-1'), $j('1102-01-01','Piutang Dagang Usaha','0.00','1000000.00','Piutang Dagang Usaha - INV-1'),
    $j('1106-01-02','PPh 23 Disetor','0.00','0.00','PPh 23 Disetor - INV-1'), $j('7201-01-04','Administrasi Bank','0.00','0.00','Administrasi Bank - INV-1'));
$base = array('header'=>$header, 'detail'=>array($row), 'alokasi'=>$alokasi, 'list_jurnal'=>$jurnal, 'kartu_ledger'=>array());

// 1) Penerimaan invoice (alur lama): judul, crumb, Back, ringkasan, tabel, jurnal; tanpa kartu deposit.
$html = renderPage($base); $text = textOf($html);
detailCheck(strpos($html, '>Penerimaan Piutang</h1>') !== false && strpos($html, 'Report Penerimaan</a> / Detail') !== false, 'Title and crumb');
detailCheck(preg_match('~<a class="btn btn-danger" href="/penerimaan_uang#tab_report">Back</a>~', $html) === 1, 'Back button');
foreach (array('Customer PT Contoh', 'Tanggal rekening koran 30-October-2026', 'Bank Bank BCA', 'Status Used', 'PPh 23 Dipotong', 'Uang masuk Rp 1.000.000,00', 'Total PPh 23', 'Deposit dipakai', 'Lebih bayar', 'Kontrol 0,00') as $needle) detailCheck(strpos($text, $needle) !== false, "Summary shows: $needle");
detailCheck(strpos($text, 'Nomor Invoice TAX Code PPH 23 Piutang Dagang Alokasi Penerimaan Deposit Dipakai Biaya Admin Lebih Bayar Sisa Piutang Status Invoice') !== false, 'Invoice table columns follow the mockup');
detailCheck(strpos($text, 'Tanggal Jurnal COA Nama Company Nama Account Deskripsi Debit Credit') !== false && strpos($text, 'Balancing 1,000,000 1,000,000') !== false, 'Journal columns and Balancing row');
detailCheck(strpos($html, '7201-01-04') === false && strpos($html, '1106-01-02') === false, 'Zero journal rows are hidden');
detailCheck(strpos($html, 'Lunas') !== false && strpos($html, 'Kartu Deposit Customer') === false, 'Lunas status and no deposit card');

// 2) Cicilan invoice lama: Piutang Dagang = saldo saat penerimaan (kredit piutang di jurnal), Kontrol = kas + lebih - uang masuk = 0.
$inst = array_replace($row, array('total_akhir_jurnal'=>'49050000.00','penerimaan'=>'16050000.00','sisa_piutang'=>'0.00','lebih'=>'3950000.00'));
$instJ = array($j('1101-02-01','Bank BCA','20000000.00','0.00','Bank BCA - INV-1'), $j('1102-01-01','Piutang Dagang Usaha','0.00','16050000.00','Piutang Dagang Usaha - INV-1'), $j('2102-01-02','Deposit Customer','0.00','3950000.00','Deposit Customer - INV-1'));
$html = renderPage(array_replace($base, array('header'=>array_replace($header, array('nominal_penerimaan_bank'=>'20000000.00')), 'detail'=>array($inst), 'list_jurnal'=>$instJ)));
detailCheck(kontrolOf($html) === '0,00', 'Installment with Lebih Bayar has Kontrol 0, got '.kontrolOf($html));
detailCheck(strpos($html, '16,050,000') !== false && strpos($html, '49,050,000') === false, 'Piutang Dagang shows the balance at receipt, not the invoice total');

// 3) Tidak dipotong dengan penyesuaian PPh (Sisa terisi) tetap Kontrol 0 karena kas = uang masuk.
$nRow = array_replace($row, array('total_akhir_jurnal'=>'15843150.00','penerimaan'=>'15843150.00','sisa_piutang'=>'290700.00'));
$html = renderPage(array_replace($base, array('header'=>array_replace($header, array('pph23_dipotong'=>'N','nominal_penerimaan_bank'=>'15843150.00')), 'detail'=>array($nRow))));
detailCheck(kontrolOf($html) === '0,00' && strpos(textOf($html), 'PPh 23 Tidak dipotong') !== false && strpos($html, 'Sisa piutang') !== false, 'N receipt keeps Kontrol 0 and shows the Sisa piutang status');

// 4) Baris snapshot baru memakai rumus snapshot: ((piutang - pph - admin - sisa) - uang masuk - deposit) + lebih.
$snap = json_encode(array('calculation_version'=>4,'tax_treatment'=>'PKP','date'=>'2026-10-31','balance'=>100000000,'pph'=>0,'admin'=>0,'sisa'=>0,'dep'=>0,'lebih'=>0,'cash'=>100000000));
$v4 = array_replace($row, array('consulting_snapshot'=>$snap,'tax_treatment'=>'PKP'));
$html = renderPage(array_replace($base, array('detail'=>array($v4))));
detailCheck(kontrolOf($html) === '0,00' && strpos($html, 'class="tax PKP"') !== false && strpos($html, '31-October-2026') !== false, 'Snapshot row: formula, TAX badge, snapshot date');

// 5) Lebih Bayar / Deposit Dipakai: kartu deposit dengan saldo berjalan dan tautan referensi.
$ledger = array(array('tanggal'=>'2026-10-29','jenis_mutasi'=>'IN','nominal'=>'500000.00','no_transaksi'=>'00039/RKN-KRG/X/26','keterangan'=>'Lebih bayar'),
    array('tanggal'=>'2026-10-30','jenis_mutasi'=>'OUT','nominal'=>'200000.00','no_transaksi'=>'00040/RKN-KRG/XI/26','keterangan'=>'Dipakai'));
$html = renderPage(array_replace($base, array('detail'=>array(array_replace($row, array('dep'=>'200000.00'))), 'kartu_ledger'=>$ledger)));
$t = textOf($html);
detailCheck(strpos($t, 'Kartu Deposit Customer (setelah penerimaan)') !== false && strpos($t, 'Tanggal Referensi Keterangan Masuk Keluar Saldo') !== false, 'Deposit card and columns');
detailCheck(strpos($html, '/penerimaan_uang/view_surat?no=00039%2FRKN-KRG%2FX%2F26') !== false && strpos($t, '500,000') !== false && strpos($t, '300,000') !== false, 'Reference link and running balance');

// 6) Penerimaan Kwitansi Deposit: ringkasan + tabel kwitansi + jurnal khusus + kartu selalu tampil.
$kw = array_replace($row, array('tax_treatment'=>'DEP','no_invoice'=>'KD/2026/10/0001','total_akhir_jurnal'=>'1000000.00','penerimaan'=>'1000000.00','biaya_admin'=>'0.00'));
$html = renderPage(array('header'=>array_replace($header, array('jenis_penerimaan'=>'deposit','nominal_penerimaan_bank'=>'1000000.00')), 'detail'=>array($kw), 'alokasi'=>$alokasi, 'list_jurnal'=>$jurnal, 'kartu_ledger'=>array()));
$t = textOf($html);
foreach (array('Nomor Penerimaan 00040/RKN-KRG/XI/26', 'Jenis Kwitansi Deposit', 'Masuk ke deposit customer Rp 1.000.000,00', 'Nomor Kwitansi No SPK Nilai Kwitansi Alokasi Penerimaan Biaya Admin Status Kwitansi', 'Jurnal Penerimaan Piutang Kwitansi Deposit', 'Belum ada mutasi deposit untuk customer ini.') as $needle) detailCheck(strpos($t, $needle) !== false, "Kwitansi receipt shows: $needle");
detailCheck(kontrolOf($html) === '0,00', 'Kwitansi Kontrol = (piutang - admin) - uang masuk');

echo "PASS: mockup detail page (summary, tables, journal without zero rows, Kontrol for old/new/installment/N receipts, deposit card, kwitansi receipt)\n";
