'use strict';
// Smoke test mockup Alokasi → sub modul tampungan (Pengembalian Kasbon, Mutasi, Transaksi Bank, Pembayaran).
// Jalankan: node specs/docs/alokasi-penerimaan/smoke-test.js
// jsdom dicari dari node_modules biasa, atau dari folder di env JSDOM_PATH.
const fs = require('fs');
const path = require('path');
const assert = require('assert');
let JSDOM;
try { ({JSDOM} = require(process.env.JSDOM_PATH || 'jsdom')); }
catch (e) { console.error('jsdom tidak ditemukan. Pasang: npm i jsdom, atau set JSDOM_PATH.'); process.exit(2); }

const XLSX_CALLS = [];
const fakeXlsx = {utils: {aoa_to_sheet(aoa) { return {__aoa: aoa}; }, book_new() { return {sheets: []}; }, book_append_sheet(wb, ws, name) { wb.sheets.push({ws, name}); }}, writeFile(wb, file) { XLSX_CALLS.push({wb, file}); }};
const html = fs.readFileSync(path.join(__dirname, 'index.html'), 'utf8').replace(/<script src=[^>]*><\/script>/g, '').replace(/<link [^>]*>/g, '');
const errors = [];
const dom = new JSDOM(html, {runScripts: 'dangerously', pretendToBeVisual: true,
    beforeParse(w) { w.scrollTo = () => {}; w.XLSX = fakeXlsx; w.addEventListener('error', e => errors.push(e.message)); }});
const {window} = dom, doc = window.document;
const $ = id => doc.getElementById(id);
const q = s => doc.querySelector(s);
const qa = s => [...doc.querySelectorAll(s)];
const click = el => { assert.ok(el, 'elemen untuk diklik tidak ada'); el.dispatchEvent(new window.MouseEvent('click', {bubbles: true})); };
const check = (el, v = true) => { assert.ok(el, 'checkbox tidak ada'); el.checked = v; el.dispatchEvent(new window.Event('change', {bubbles: true})); };
const setVal = (el, v, ev = 'change') => { el.value = v; el.dispatchEvent(new window.Event(ev, {bubbles: true})); };
const dlgOpen = () => $('dlg').classList.contains('on');
const ok_ = () => click($('dlgOk'));
const flush = () => new Promise(r => setTimeout(r, 0));
const nav = g => click(q(`.sidebar [data-go="${g}"]`));
const rowOf = ket => qa('#aT tbody tr').find(tr => tr.textContent.includes(ket));
// AP-R-35: Unlocated harus diproses (jurnal) dulu sebelum dipindah
const utab = k => click(q(`[data-utab="${k}"]`));
const procUnl = async name => { nav('unl'); utab('belum'); click(tRows().find(tr => tr.textContent.includes(name)).querySelector('[data-uproc]')); ok_(); await flush(); ok_(); utab('sudah'); };
const tRows = () => qa('#tT tbody tr').filter(tr => !tr.querySelector('.dt-empty'));
let checks = 0;
const ok = (c, m) => { assert.ok(c, m); checks++; };

(async () => {
// ===== Menu & Alokasi =====
ok(['kas', 'mut', 'tb', 'pay'].every(g => q(`.sidebar [data-go="${g}"]`).closest('li').classList.contains('chg')), '4 sub modul tampungan baru di menu Penerimaan');
ok(qa('#aT tbody tr').length === 23 && $('c_alo').textContent === '7', 'Alokasi: 23 transaksi, 7 masih Open');
{ const ban = () => !$('draftBanner').classList.contains('hidden') && $('draftBanner').textContent;
  ok(!ban(), 'AP-R-42: Alokasi final, tanpa banner'); nav('unl'); ok(!ban(), 'Unlocated final, tanpa banner'); nav('pp'); ok(!ban(), 'Penerimaan Piutang final, tanpa banner');
  nav('mut'); ok(/DRAFT/.test(ban()), 'Mutasi: banner DRAFT'); nav('tb'); ok(/DRAFT/.test(ban()), 'Transaksi Bank: banner DRAFT'); nav('dash'); ok(/DRAFT/.test(ban()), 'Dashboard: banner DRAFT');
  nav('jall'); ok(/HALAMAN BANTU/.test(ban()), 'Jurnal All: banner halaman bantu'); nav('alo'); }
ok(/Open/.test(rowOf('ZUHDI RAHMAN').textContent) && rowOf('ZUHDI RAHMAN').querySelector('[data-alo]'), 'Baris Open punya tombol Alokasi');
setVal($('aBank'), 'b3'); click($('aSearch'));
ok(qa('#aT tbody tr').length === 3, 'Filter bank');
click($('aReset'));

// ===== Tampungan baru berisi data dari split =====
const counts = {kas: 1, mut: 2, tb: 4, pay: 1};
for (const [g, n] of Object.entries(counts)) {
    nav(g);
    ok(!$('pg-tamp').classList.contains('hidden') && tRows().length === n && $('c_' + g).textContent === String(n), `Tampungan ${g}: ${n} baris`);
}
nav('tb');
ok(/Transaksi Bank/.test($('tTitle').textContent) && /biaya admin/.test($('tNote').textContent) && $('tTot').textContent === 'masuk 85,412.00keluar 1,897,082.00', 'Transaksi Bank: catatan + total nominal alokasi');
ok(q('#tT thead').textContent.includes('Nominal Alokasi'), 'Kolom Nominal Alokasi');
nav('unl');
ok(tRows().length === 6 && /dipecah: Penerimaan Piutang \+ Unlocated Penerimaan/.test($('tT').textContent) && /500,000\.00/.test($('tT').textContent), 'Unlocated: transaksi dipecah tampil nominal alokasinya saja');

// ===== Alokasi baru masuk ke tampungan =====
nav('alo');
click(rowOf('TELKOM INDONESIA').querySelector('[data-alo]'));
ok(dlgOpen() && /Alokasi Split Transaksi/.test($('dlgT').textContent), 'Modal Alokasi Split');
const opts = () => qa('[data-sj="0"] option').map(o => o.textContent).filter(t => !/Pilih/.test(t));
ok(/uang keluar/.test($('spArah').textContent) && opts().join('|') === 'Mutasi|Transaksi Bank|Pembayaran', 'Uang keluar: hanya Mutasi, Transaksi Bank, Pembayaran');
ok_();
ok(/jenis alokasi/.test($('dlgErr').textContent), 'Jenis wajib dipilih');
setVal(q('[data-sj="0"]'), '6'); setVal(q('[data-sn="0"]'), '900000', 'input');
ok($('spSts').textContent === 'Tidak Sesuai' && /20,000\.00/.test($('spSisa').textContent), 'Sisa & status tidak sesuai');
ok_();
ok(/sama dengan total transaksi/.test($('dlgErr').textContent), 'Total harus sama');
setVal(q('[data-sn="0"]'), '920000', 'input'); ok_(); await flush();
ok(dlgOpen() && /menu Pembayaran/.test($('dlgB').textContent), 'Tersimpan, info menu tujuan'); ok_();
ok(/Pembayaran/.test(rowOf('TELKOM INDONESIA').textContent) && rowOf('TELKOM INDONESIA').querySelector('[data-view]'), 'Status alokasi berubah');
click(rowOf('ZUHDI RAHMAN').querySelector('[data-alo]'));
ok(/uang masuk/.test($('spArah').textContent) && !opts().includes('Pembayaran') && opts().includes('Pengembalian Kasbon') && opts().length === 7, 'Uang masuk: tanpa Pembayaran');
click($('dlgNo'));
// split 2 jenis
click(rowOf('ASAHI DENSO').querySelector('[data-alo]'));
setVal(q('[data-sj="0"]'), '1'); setVal(q('[data-sn="0"]'), '12200000', 'input');
click($('spAdd')); setVal(q('[data-sj="1"]'), '5'); setVal(q('[data-sn="1"]'), '10000', 'input');
ok($('spSts').textContent === 'Sesuai', 'Split 2 jenis sesuai'); ok_(); await flush(); ok_();
click(rowOf('ASAHI DENSO').querySelector('[data-view]'));
ok(qa('#vT tbody tr').length === 2 && /Draft/.test($('vT').textContent) && q('#vT [data-gotamp="tb"]'), 'View: split + link ke menu tampungan');
click(q('#vT [data-gotamp="tb"]'));
ok(!$('pg-tamp').classList.contains('hidden') && /Transaksi Bank/.test($('tTitle').textContent) && tRows().length === 5 && /10,000\.00/.test($('tT').textContent), 'Link membuka menu Transaksi Bank, alokasi baru ada');
nav('pay');
ok(tRows().length === 2, 'Pembayaran bertambah');

// ===== Rollback =====
click($('tRollback'));
ok(dlgOpen() && /minimal satu/.test($('dlgB').textContent), 'Rollback tanpa pilihan ditolak'); ok_();
check(tRows().find(tr => tr.textContent.includes('TELKOM')).querySelector('.check_item'));
click($('tRollback'));
ok(dlgOpen() && /1 data terpilih/.test($('dlgB').textContent), 'Konfirmasi rollback'); ok_(); await flush(); ok_();
ok(tRows().length === 1, 'Baris hilang dari tampungan');
nav('alo');
ok(/Open/.test(rowOf('TELKOM INDONESIA').textContent), 'Transaksi kembali Open di Alokasi');
// pilihan B: rollback hanya alokasi di menu ini; Penerimaan Piutang yang sudah diproses tetap
nav('unl');
check(tRows().find(tr => tr.textContent.includes('MADA WIKRI')).querySelector('.check_item'));
click($('tRollback'));
ok(dlgOpen() && /Alokasi lain pada transaksi yang sama tetap: Penerimaan Piutang Rp 15,000,000.00/.test($('dlgB').textContent), 'Rollback: alokasi lain tetap'); ok_(); await flush(); ok_();
ok(tRows().length === 5 && !tRows().some(tr => tr.textContent.includes('MADA WIKRI')), 'Unlocated Mada Wikri hilang dari tampungan');
nav('alo');
const mw = () => rowOf('MADA WIKRI');
ok(/Sebagian · sisa 500,000\.00/.test(mw().textContent) && /Penerimaan Piutang/.test(mw().textContent) && mw().querySelector('[data-alo]') && mw().querySelector('[data-view]'), 'Transaksi jadi Sebagian, bisa dialokasikan lagi');
ok($('c_alo').textContent === '7', 'Badge Alokasi menghitung Open + Sebagian');
click(mw().querySelector('[data-alo]'));
ok(/Sudah dialokasikan/.test($('spExist').textContent) && /500,000\.00/.test($('spSisa').textContent), 'Modal: alokasi lama tetap, isi sisa saja');
setVal(q('[data-sj="0"]'), '3'); setVal(q('[data-sn="0"]'), '600000', 'input'); ok_();
ok(/sisa transaksi/.test($('dlgErr').textContent), 'Total harus sama dengan sisa');
setVal(q('[data-sn="0"]'), '500000', 'input'); ok_(); await flush(); ok_();
ok(!/Sebagian/.test(mw().textContent) && /Pengembalian Kasbon/.test(mw().textContent) && !mw().querySelector('[data-alo]'), 'Sisa teralokasi, status penuh lagi');
nav('pp');
ok(/MADA WIKRI/.test($('ppRT').textContent) && /00051\/PU\/XI\/26/.test($('ppRT').textContent), 'Penerimaan Piutang yang sudah diproses tidak tersentuh (Report Penerimaan)');
// draft Penerimaan Piutang (ASAHI) juga tetap saat Transaksi Bank di-rollback
nav('tb');
check(tRows().find(tr => tr.textContent.includes('ASAHI')).querySelector('.check_item'));
click($('tRollback')); ok_(); await flush(); ok_();
nav('pp');
ok(/ASAHI/.test($('ppT').textContent), 'Draft Penerimaan Piutang tetap');
nav('his');
ok(qa('#hisT tbody tr').length === 6, 'Historical mencatat split & rollback');

// ===== Proses lanjutan Mutasi (satu tombol, pilih Request Mutasi) =====
const procBtn = (ket, sel) => tRows().find(tr => tr.textContent.includes(ket)).querySelector(sel);
const procTxt = ket => tRows().find(tr => tr.textContent.includes(ket)).querySelector('.tproc').textContent;
nav('mut');
ok(!$('tProcTh').classList.contains('hidden') && procBtn('KE 4364000300', '[data-mproc]') && procBtn('CR 0511 DARI', '[data-mproc]'), 'Mutasi: satu tombol Proses Mutasi untuk keluar & masuk');
click(procBtn('KE 4364000300', '[data-mproc]'));
ok(dlgOpen() && !/MTS-261100006/.test($('dlgB').textContent) && /MTS-261100003/.test($('rmReq').textContent), 'Hanya request approved');
ok_(); ok(/Pilih Request Mutasi/.test($('dlgErr').textContent), 'Request wajib dipilih');
setVal($('rmReq'), 'MTS-261100003');
ok(/belum punya realisasi/.test($('rmInfo').textContent) && /buku STM/.test($('rmInfo').textContent) && /Piutang ke SUSTAIN/.test($('rmJv').textContent), 'Request belum terealisasi: realisasi baru + jurnal sisi keluar di buku STM');
ok(/Mutasi keluar MTS-/.test($('rmJv').textContent) && !q('#rmJv .ja-meta') && /MTR-… \(otomatis\)/.test($('rmJv').textContent), 'Mutasi: keterangan per baris (Mutasi keluar MTS- dari ... ke ...), No. Transaksi MTR otomatis');
ok_(); await flush(); ok_();
ok(/MTR-261100001/.test(procTxt('KE 4364000300')) && /realisasi baru/.test(procTxt('KE 4364000300')) && /BUK\/STM\/2611\/0001/.test(procTxt('KE 4364000300')), 'Keluar: realisasi MTR-261100001 + jurnal BUK STM');
click(procBtn('CR 0511 DARI', '[data-mproc]')); setVal($('rmReq'), 'MTS-261100003');
ok(/sudah punya realisasi .*MTR-261100001.* dari sisi keluar/.test($('rmInfo').textContent) && /Hutang ke STM/.test($('rmJv').textContent), 'Masuk: ditautkan ke realisasi sisi keluar + jurnal sisi masuk');
ok_(); await flush(); ok_();
ok(/MTR-261100001/.test(procTxt('CR 0511 DARI')) && /BUM\/SUSTAIN\/2611\/0001/.test(procTxt('CR 0511 DARI')), 'Masuk ditautkan (request yang sama) + jurnal BUM SUSTAIN');
ok(!/dibuat manual|jurnal lama|transaksi lama/.test(doc.body.textContent), 'Tidak ada pilihan transaksi/realisasi manual lama');
nav('mts'); ok(!q('.sidebar [data-go="mtr"]') && !$('pg-mtr') && !q('[data-mtab]'), 'List Mutasi dihapus (tidak ada menu / tab)');
nav('mut'); ok(qa('#tT .tproc [data-jv]').length >= 1, 'Tampungan Mutasi: link Lihat jurnal di kolom Proses');
// mutasi baru BCA STM -> OCBC Vuca, sisi masuk diproses duluan
nav('alo');
for (const ket of ['KE OCBC', 'TRF MASUK DARI BCA']) {
    click(rowOf(ket).querySelector('[data-alo]')); setVal(q('[data-sj="0"]'), '4'); setVal(q('[data-sn="0"]'), '10000000', 'input'); ok_(); await flush(); ok_();
}
nav('mut');
click(procBtn('TRF MASUK DARI BCA', '[data-mproc]'));
ok(/MTS-261100005 .*\(cocok\)/.test($('rmReq').textContent), 'Request yang cocok ditandai');
setVal($('rmReq'), 'MTS-261100005');
const det = qa('#rmDet input').map(i => i.value).join('|');
ok(/Hutang ke STM/.test($('rmJv').textContent) && /VUCA/.test($('rmInfo').textContent) && /OCBC Vuca/.test(det) && /13-November-2026/.test(det) && /10,000,000.00/.test(det) && /belum punya realisasi/.test($('rmInfo').textContent), 'Form terisi dari request + rekening koran, akan membuat realisasi');
ok_(); await flush(); ok_();
ok(/MTR-261100002/.test(procTxt('TRF MASUK DARI BCA')) && /realisasi baru/.test(procTxt('TRF MASUK DARI BCA')) && /BUM\/VUCA\/2611\/0001/.test(procTxt('TRF MASUK DARI BCA')), 'Sisi masuk duluan: realisasi MTR-261100002 + jurnal di buku VUCA');
click(procBtn('KE OCBC', '[data-mproc]')); setVal($('rmReq'), 'MTS-261100005'); ok_(); await flush(); ok_();
ok(/MTR-261100002/.test(procTxt('KE OCBC')) && /ditautkan/.test(procTxt('KE OCBC')) && /BUK\/STM\/2611\/0002/.test(procTxt('KE OCBC')), 'Sisi keluar ditautkan + jurnal sendiri di buku STM');
nav('mts');
ok(/Terealisasi MTR-261100002/.test($('mtsT').textContent) && /Menunggu Approval/.test($('mtsT').textContent), 'Request Mutasi: status terealisasi');
nav('mut');
check(tRows().find(tr => tr.textContent.includes('KE OCBC')).querySelector('.check_item'));
click($('tRollback'));
ok(dlgOpen() && /sudah diproses/.test($('dlgB').textContent), 'Rollback baris yang sudah diproses ditolak'); ok_();

// ===== Proses lanjutan Transaksi Bank =====
nav('tb');
click(tRows().find(tr => tr.textContent.includes('BUNGA JASA GIRO')).querySelector('[data-tbp]'));
const tbv = qa('#tbNew input').map(i => i.value).join('|'); ok(/Penerimaan/.test(tbv) && /1101-02-05 - Bank BCA Sustain/.test(tbv) && /85,412.00/.test(tbv), 'Form Transaksi Bank terisi dari rekening koran');
ok($('tbLawan').value === '' && !$('tbAutoNote') && /belum dipilih/.test($('tbJvT').textContent), 'Akun lawan kosong, dipilih manual (tanpa kata kunci)');
ok_(); ok(/COA lawan/.test($('dlgErr').textContent), 'COA lawan wajib');
setVal($('tbLawan'), $('tbLawan').options[2].value);
const jvr = qa('#tbJvT tbody tr').map(tr => [...tr.cells].map(c => c.textContent));
ok(jvr[0][3] === 'Bank BCA Sustain' && jvr[0][6] === '85,412.00' && jvr[0][7] === '0.00' && /Pendapatan Bunga Jasa Giro/.test(jvr[1][3]) && jvr[1][6] === '0.00' && jvr[1][7] === '85,412.00' && /CR.*Debit/.test($('dlgB').textContent), 'Preview jurnal: CR rekening koran = Dr Bank / Cr Pendapatan Bunga');
ok(qa('#tbJvT thead th').map(t => t.textContent).join('|') === 'Tanggal|Tipe|No. COA|Nama COA|Keterangan|No. Reff|Debit|Kredit' && !q('#tbJv .ja-meta') && /GRAND TOTAL/.test($('tbJvT').textContent) && /seimbang/.test($('tbJv').textContent), 'Template jurnal mengikuti Jurnal All (kolom + GRAND TOTAL, preview tanpa kotak info)');
ok(/Uang masuk ke Bank BCA Sustain/.test(jvr[0][4]) && /diterima di Bank BCA Sustain/.test(jvr[1][4]) && /KM-/.test(jvr[0][5]), 'Keterangan per baris informatif + No. Reff');
setVal($('tbLawan'), $('tbLawan').options[3].value);
ok(/Beban Pajak Bunga/.test(qa('#tbJvT tbody tr')[1].textContent), 'Ganti akun lawan: preview jurnal ikut berubah');
setVal($('tbLawan'), $('tbLawan').options[2].value); ok_(); await flush(); ok_();
click(tRows().find(tr => tr.textContent.includes('PAJAK BUNGA')).querySelector('[data-tbp]'));
setVal($('tbLawan'), $('tbLawan').options[3].value);
ok(/Beban Pajak Bunga/.test(qa('#tbJvT tbody tr')[0].textContent) && /Total Credit.*Bank Kredit/.test($('dlgB').textContent), 'PAJAK BUNGA: DB = Dr Beban Pajak Bunga / Cr Bank'); click($('dlgNo'));
ok(/KM-261100001/.test(tRows().find(tr => tr.textContent.includes('BUNGA JASA GIRO')).textContent), 'Transaksi baru KM-261100001');
click(tRows().find(tr => tr.textContent.includes('BIAYA ADM')).querySelector('[data-tbp]'));
ok($('tbLawan').value === '' && !q('[name=tbm]'), 'BIAYA ADM: akun lawan dipilih manual, tanpa pilihan transaksi lama');
setVal($('tbLawan'), $('tbLawan').options[1].value); ok_(); await flush(); ok_();
ok(/KK-261100001/.test(tRows().find(tr => tr.textContent.includes('BIAYA ADM')).textContent), 'Transaksi baru KK-261100001');
nav('tbk');
ok(qa('#tbkT tbody tr').length === 2 && qa('#tbkT tbody tr').every(tr => /2026/.test(tr.children[8].textContent)), 'Data Transaksi Bank: semua dari rekening koran');
const km = qa('#tbkT tbody tr').find(tr => /KM-261100001/.test(tr.textContent)).children, kk = qa('#tbkT tbody tr').find(tr => /KK-261100001/.test(tr.textContent)).children;
ok(/Kredit/.test(km[4].textContent) && /1101-02-05 - Bank BCA Sustain/.test(km[5].textContent) && /Debit/.test(km[5].textContent), 'Penerimaan: Dr Bank / Cr Akun Lawan; Bank selalu di kolom Bank (COA Tujuan)');
ok(/Admin Charge/.test(kk[4].textContent) && /Debit/.test(kk[4].textContent) && /Bank BCA STM/.test(kk[5].textContent) && /Kredit/.test(kk[5].textContent), 'Pengeluaran: Dr Akun Lawan / Cr Bank');
ok(/Input manual .*ditutup/.test(q('#pg-tbk').textContent), 'Input manual Transaksi Bank ditutup');
// AP-R-37: Jurnal All meniru tampilan sistem (card per sumber, tab, filter Tahun/Bulan/Company/Kategori, + Post → detail → Posting)
const jaRowsT = () => qa('#jaT tbody tr').filter(tr => !tr.querySelector('.ja-empty'));
nav('jall');
ok(qa('#jaCards .ja-card').length === 6 && qa('#jaTabs .ja-tab').map(b => b.dataset.jt).join() === 'INV,RCV,PAY,EXP,PCS,BANK' && q('#jaTabs [data-jt="BANK"]').classList.contains('chg'), 'Jurnal All: card + tab per sumber seperti sistem, tab baru Jurnal Bank');
ok(/Jurnal Penerimaan/.test($('jaTitle').textContent) && $('jaYear') && $('jaMonth') && $('jaCo') && $('jaCat') && !$('jaAr'), 'Tab Penerimaan: filter Tahun, Bulan, Company, Kategori Transaksi (baru)');
ok(qa('#jaT thead th').map(t => t.textContent).join('|') === 'No.|Tgl Jurnal|No. Transaksi|No. Referensi|Kategori Transaksi|Keterangan|Company|Debit|Kredit|Aksi', 'Kolom daftar seperti sistem (+ Kategori Transaksi)');
click(q('#jaTabs [data-jt="BANK"]'));
setVal($('jaCat'), 'Transaksi Bank', 'change'); click($('jaApply'));
ok(jaRowsT().length === 2 && /KM-261100001/.test($('jaT').textContent) && /KK-261100001/.test($('jaT').textContent) && qa('#jaT [data-jdet]').every(b => b.textContent === '+ Post'), 'Jurnal Bank · Transaksi Bank: KM / KK belum posting, tombol + Post');
setVal($('jaAr'), 'out', 'change'); click($('jaApply'));
ok(jaRowsT().length === 1 && /KK-261100001/.test($('jaT').textContent) && /uang keluar/.test($('jaT').textContent), 'Filter Kategori Transaksi Bank + Arah uang keluar');
const jaPend = $('c_jall').textContent;
click(q('#jaT [data-jdet]'));
ok(/Detail jurnal/.test($('dlgT').textContent) && /No\. TransaksiKK-261100001/.test($('dlgB').textContent) && /GRAND TOTAL/.test($('jaLines').textContent) && /Admin Charge/.test($('jaLines').textContent) && $('dlgOk').textContent === 'Posting', 'Modal Detail jurnal: meta, baris COA, GRAND TOTAL, tombol Posting');
ok_(); ok(/Report Jurnal All/.test($('dlgB').textContent), 'Posting'); ok_();
ok(jaRowsT().length === 0 && +$('c_jall').textContent === +jaPend - 1, 'Sudah diposting hilang dari Jurnal All, badge berkurang');
nav('jrep'); click(q('#jaTabs [data-jt="BANK"]'));
ok(!q('#jaRep #jaCards') && /No\. Jurnal Accounting/.test(q('#jaT thead').textContent) && /KK-261100001/.test($('jaT').textContent) && q('#jaT [data-jdet]').textContent === 'Detail', 'Report Jurnal All: jurnal yang sudah diposting + No. Jurnal Accounting');
nav('jall'); click(q('#jaTabs [data-jt="RCV"]')); setVal($('jaCat'), 'Penerimaan Piutang', 'change'); click($('jaApply'));
ok(jaRowsT().length === 1 && /00052\/PU\/XI\/26/.test($('jaT').textContent), 'Kategori Penerimaan Piutang: 1 contoh belum posting (yang lain sudah di Report)');
click($('jaReset'));

// Unlocated → Penerimaan Piutang
nav('tb');
ok($('tMoveWrap').classList.contains('hidden'), 'Tombol pindah hanya di Unlocated');
nav('unl');
// ===== AP-R-35: Unlocated Belum / Sudah Diproses + jurnal =====
ok(!$('tUnlTabs').classList.contains('hidden') && q('#tUnlTabs li.active [data-utab="belum"]') && $('tMoveWrap').classList.contains('hidden') && !$('tRollback').classList.contains('hidden'), 'Unlocated: tab Belum Diproses default, tombol pindah belum ada');
const nBelum = +$('cnt_unlb').textContent;
click(tRows().find(tr => tr.textContent.includes('KERETA API')).querySelector('[data-uproc]'));
ok(dlgOpen() && /Proses Unlocated Penerimaan/.test($('dlgT') ? $('dlgT').textContent + $('dlgB').textContent : $('dlgB').textContent) && /Penerimaan Belum Teridentifikasi/.test($('dlgB').textContent) && /30,000,000\.00/.test($('dlgB').textContent) && /\(diisi user\)/.test($('dlgB').textContent), 'Proses: preview jurnal Debit Bank / Kredit Penerimaan Belum Teridentifikasi');
ok($('unTrx') && /Detail Transaksi Bank/.test($('dlgB').textContent) && ($('unTrx').compareDocumentPosition($('unJv')) & 4) && /Nominal dialokasikan ke Unlocated/.test($('unTrx').textContent) && qa('#dlgB input').some(i => /tanggal transaksi bank/.test(i.value)), 'AP-R-43: pop-up Proses Unlocated: detail transaksi bank dulu, jurnal di bawahnya');
ok(/Penerimaan belum teridentifikasi - /.test($('unJv').textContent) && /menunggu dipindahkan ke Penerimaan Piutang/.test($('unJv').textContent), 'Unlocated: keterangan per baris'); setVal($('unKet'), 'Transfer tanpa berita', 'input'); ok(/Transfer tanpa berita/.test($('unJv').textContent), 'Unlocated: keterangan per baris, ikut keterangan yang diketik') || 0;
ok_(); await flush();
ok(/Unlocated UNL-261100001 dibuat, jurnal BUM\//.test($('dlgB').textContent), 'Terbentuk nomor Unlocated baru + jurnal'); ok_();
ok(!tRows().some(tr => tr.textContent.includes('KERETA API')) && +$('cnt_unlb').textContent === nBelum - 1 && $('cnt_unls').textContent === '1', 'Pindah dari Belum Diproses');
utab('sudah');
ok(tRows().length === 1 && /UNL-261100001/.test($('tT').textContent) && !$('tMoveWrap').classList.contains('hidden') && $('tRollback').classList.contains('hidden'), 'Tab Sudah Diproses: nomor Unlocated, tombol pindah, tanpa rollback');
click(q('#tT [data-jv]'));
ok(/Penerimaan Belum Teridentifikasi/.test($('dlgB').textContent) && /30,000,000\.00/.test($('dlgB').textContent), 'Lihat jurnal Unlocated'); click($('dlgNo') || $('dlgOk'));
ok(!$('tMoveWrap').classList.contains('hidden') && /Invoice/.test($('tMove').textContent) && /Kwitansi Deposit/.test($('tMoveKw').textContent), 'Dua tombol pindah di Unlocated: Invoice dan Kwitansi Deposit');
click($('tMove')); ok(/minimal satu/.test($('dlgB').textContent), 'Pindah tanpa pilihan ditolak'); ok_();
check(tRows().find(tr => tr.textContent.includes('KERETA API')).querySelector('.check_item'));
click($('tMove'));
ok(/30,000,000.00/.test($('dlgB').textContent) && /Referensi: UNL-261100001/.test($('dlgB').textContent) && /Debit Penerimaan Belum Teridentifikasi/.test($('dlgB').textContent), 'Konfirmasi pindah + referensi Unlocated + jurnal penerimaan'); ok_(); await flush(); ok_();
ok(!/KERETA API/.test($('tT').textContent), 'Hilang dari Unlocated');
nav('pp');
ok(/Dari Unlocated UNL-261100001/.test(qa('#ppT tbody tr').find(tr => tr.textContent.includes('KERETA API')).textContent), 'Draft Penerimaan Piutang membawa referensi Unlocated');
ok(/KERETA API/.test($('ppT').textContent) && /Draft/.test(qa('#ppT tbody tr').find(tr => tr.textContent.includes('KERETA API')).textContent) && !/KERETA API/.test($('ppKT').textContent), 'Muncul sebagai draft di tab Penerimaan Piutang Invoice');
ok(q('#ppTabs li.active [data-pptab="inv"]') && q('[data-pppane="dep"]').classList.contains('hidden'), 'Penerimaan Piutang: tab Invoice aktif');
click(q('[data-pptab="dep"]')); ok(!q('[data-pppane="dep"]').classList.contains('hidden') && q('[data-pppane="inv"]').classList.contains('hidden'), 'Pindah tab Kwitansi Deposit'); click(q('[data-pptab="inv"]'));
nav('alo');
ok(/Penerimaan Piutang/.test(rowOf('KERETA API').textContent) && !/Unlocated/.test(rowOf('KERETA API').textContent), 'Status alokasi ikut berubah');

// biaya transfer: upload OCBC, alokasi Transaksi Bank, akun lawan otomatis
nav('alo'); click($('aUpload')); setVal($('upBank'), 'b3');
ok(/format kode 22/.test($('upFmt').textContent), 'OCBC dibaca format kode 22');
setVal($('upFile'), 'f3'); ok_(); await flush(); ok_();
click(rowOf('TRANSFER FEE SKN').querySelector('[data-alo]')); setVal(q('[data-sj="0"]'), '5'); setVal(q('[data-sn="0"]'), '5000', 'input'); ok_(); await flush(); ok_();
nav('tb'); click(tRows().find(tr => tr.textContent.includes('TRANSFER FEE SKN')).querySelector('[data-tbp]'));
setVal($('tbLawan'), $('tbLawan').options[4].value);
ok(/Beban Biaya Transfer/.test(qa('#tbJvT tbody tr')[0].textContent) && /buku VUCA/.test($('dlgB').textContent), 'Biaya transfer: pilih Beban Biaya Transfer, jurnal di buku VUCA'); click($('dlgNo'));
// autodebet pembayaran lewat Transaksi Bank
click(tRows().find(tr => tr.textContent.includes('AUTODEBET BPJS')).querySelector('[data-tbp]'));
ok(/seluruh COA master/.test($('dlgB').textContent), 'Form: catatan COA lawan = seluruh COA master');
ok(/Pengeluaran [(]uang keluar[)]/.test($('tbNew').textContent + [...$('tbNew').querySelectorAll('input')].map(i => i.value).join(' ')) && /UANG KELUAR · kolom Total Credit/.test($('tbArah').textContent), 'Form: keterangan uang keluar = kolom Total Credit');
setVal($('tbLawan'), '2103-01-07 - Hutang Asuransi BPJS Ketenagakerjaan');
ok(/Hutang Asuransi BPJS Ketenagakerjaan/.test(qa('#tbJvT tbody tr')[0].textContent) && /1,850,000\.00/.test(qa('#tbJvT tbody tr')[0].textContent) && /Bank BCA STM/.test(qa('#tbJvT tbody tr')[1].textContent), 'Autodebet BPJS: Dr Hutang BPJS / Cr Bank (keluar mengikuti rekening koran)');
ok_(); await flush(); ok(/KK-2611/.test($('dlgB').textContent), 'Autodebet tersimpan sebagai KK'); ok_();
ok(!tRows().find(tr => tr.textContent.includes('AUTODEBET BPJS')).querySelector('[data-tbp]'), 'Baris autodebet sudah diproses');
nav('tbk'); const tbkRow = qa('#tbkT tbody tr').find(tr => tr.textContent.includes('AUTODEBET BPJS'));
ok(/Uang Keluar/.test(tbkRow.textContent) && qa('#tbkT tbody tr').some(tr => /Uang Masuk/.test(tr.textContent)), 'Data Transaksi Bank: keterangan uang masuk / keluar'); nav('tb');
ok(!q('.sidebar [data-go="kw"]') && !$('pg-kw') && q('.sidebar [data-go="tbk"]').closest('.item').textContent.includes('Transaksi Bank') && !q('.sidebar [data-go="tbk"]').closest('.item').textContent.includes('Request Mutasi'), 'Menu Transaksi Bank terpisah dari Mutasi Bank; menu Kata Kunci dihapus');

// Reset
click($('btnReset')); ok_(); await flush();
ok($('c_alo').textContent === '7' && $('c_pay').textContent === '1', 'Reset data mockup');


// ===== Test case: Request Mutasi SUSTAIN -> STM sampai alokasi (data segar setelah Reset) =====
nav('mts'); click($('mtsAdd'));
ok_(); ok(/wajib/.test($('dlgErr').textContent), 'TC1: field request wajib');
ok(!$('mfAcc') && !/Target Accounting/.test($('dlgB').textContent), 'TC1: tanpa Target Accounting'); setVal($('mfDari'), 'b2'); setVal($('mfKe'), 'b2'); setVal($('mfKet'), 'Pengembalian dana talangan ke STM', 'input'); setVal($('mfNilai'), '15000000', 'input');
ok_(); ok(/tidak boleh sama/.test($('dlgErr').textContent), 'TC1: bank asal = tujuan ditolak');
setVal($('mfKe'), 'b1'); ok($('mfCo').textContent === 'SUSTAIN → STM (antar company)', 'TC1: company otomatis dari rekening'); ok_(); await flush(); ok_();
ok(/Menunggu Approval/.test(qa('#mtsT tbody tr').find(tr => /MTS-261100007/.test(tr.textContent)).textContent), 'TC1: MTS-261100007 menunggu approval');
nav('mtsapv');
ok($('c_mtsapv').textContent === '2', 'TC2: 2 request menunggu approval');
click(q('[data-apv="MTS-261100007"]')); ok_(); await flush(); ok_();
nav('mts'); ok(/Approved/.test(qa('#mtsT tbody tr').find(tr => /MTS-261100007/.test(tr.textContent)).textContent), 'TC2: approved');
// TC3-5: rekening Sustain (keluar)
nav('alo'); click($('aUpload')); setVal($('upBank'), 'b2');
ok(/format kode 7/.test($('upFmt').textContent) && $('upFile').options.length === 2, 'TC3: file contoh per rekening, format BCA');
setVal($('upFile'), 'f1'); click($('upPrev')); ok(qa('#upPrevT tbody tr').length === 2, 'Preview upload'); ok_(); await flush(); ok_();
const sustainOut = () => rowOf('KE 4363005287 SENTRAL TEHNOLOGI');
ok(sustainOut() && /Open/.test(sustainOut().textContent) && /15,000,000\.00/.test(sustainOut().textContent), 'TC3: baris keluar di BCA Sustain');
click($('aUpload')); setVal($('upBank'), 'b2'); setVal($('upFile'), 'f1'); ok_();
ok(/sudah pernah diupload/.test($('dlgErr').textContent), 'TN: upload file yang sama ditolak'); click($('dlgNo'));
click(sustainOut().querySelector('[data-alo]'));
ok(!qa('[data-sj="0"] option').some(o => o.textContent === 'Penerimaan Piutang'), 'TC4: jenis sesuai uang keluar');
setVal(q('[data-sj="0"]'), '4'); setVal(q('[data-sn="0"]'), '15000000', 'input'); ok_(); await flush(); ok_();
nav('mut');
click(procBtn('KE 4363005287', '[data-mproc]'));
ok(/MTS-261100007 .*\(cocok\)/.test($('rmReq').textContent), 'TC5: request cocok ditandai');
setVal($('rmReq'), 'MTS-261100005'); ok_();
ok(/Bank asal request/.test($('dlgErr').textContent), 'TN: bank asal beda ditolak');
setVal($('rmReq'), 'MTS-261100007'); ok_(); await flush(); ok_();
ok(/MTR-261100001/.test(procTxt('KE 4363005287')) && /realisasi baru/.test(procTxt('KE 4363005287')) && /BUK\/SUSTAIN\/2611\/0001/.test(procTxt('KE 4363005287')), 'TC5: realisasi MTR-261100001 + jurnal sisi keluar di buku SUSTAIN');
nav('mts'); ok(/Terealisasi MTR-261100001/.test($('mtsT').textContent), 'TC5: request terealisasi');
// TC6-8: rekening STM (masuk), pilih request yang sama
nav('alo'); click($('aUpload')); setVal($('upBank'), 'b1'); setVal($('upFile'), 'f2'); ok_(); await flush(); ok_();
const stmIn = () => rowOf('DARI 4364000300 SENTRAL SUSTAINABILITY');
ok(stmIn() && /15,000,000\.00/.test(stmIn().textContent), 'TC6: baris masuk di BCA STM');
click(stmIn().querySelector('[data-alo]')); setVal(q('[data-sj="0"]'), '4'); setVal(q('[data-sn="0"]'), '15000000', 'input'); ok_(); await flush(); ok_();
nav('mut');
click(tRows().find(tr => tr.textContent.includes('1811 DARI 4364000300')).querySelector('[data-mproc]'));
setVal($('rmReq'), 'MTS-261100007');
ok(/sudah punya realisasi .*MTR-261100001/.test($('rmInfo').textContent) && /buku STM/.test($('rmInfo').textContent) && /Hutang ke SUSTAIN/.test($('rmJv').textContent), 'TC8: request yang sama; jurnal sisi masuk di buku STM (Hutang ke SUSTAIN)');
ok_(); await flush(); ok_();
nav('mut');
ok(/MTR-261100001/.test(procTxt('KE 4363005287')) && /MTS-261100007/.test(procTxt('KE 4363005287')) && /BUK\/SUSTAIN\/2611\/0001/.test(procTxt('KE 4363005287')) && /MTR-261100001/.test(procTxt('1811 DARI 4364000300')) && /BUM\/STM\/2611\/0001/.test(procTxt('1811 DARI 4364000300')), 'TC8: kedua sisi tertaut ke MTR-261100001 + jurnal per sisi di tampungan');
click(tRows().find(tr => tr.textContent.includes('KE 4363005287')).querySelector('[data-jv^="BUK"]'));
ok(/Piutang ke STM/.test($('dlgB').textContent) && /1101-02-05/.test($('dlgB').textContent) && /15,000,000\.00/.test($('dlgB').textContent), 'Jurnal sisi keluar: Dr Piutang ke STM, Cr Bank BCA Sustain'); click($('dlgNo'));
nav('mut');
check(tRows().find(tr => tr.textContent.includes('KE 4363005287')).querySelector('.check_item')); click($('tRollback'));
ok(/sudah diproses/.test($('dlgB').textContent), 'TN: rollback setelah diproses ditolak'); ok_();

// ===== Pengembalian Kasbon: tampungan status + Verifikasi Pengembalian Kasbon (pilih expense manual) =====
nav('kas');
ok(!$('tProcTh').classList.contains('hidden') && /Belum diverifikasi/.test($('tT').textContent) && !/ER-2026/.test($('tT').textContent), 'Tampungan Pengembalian Kasbon: status berjalan, tanpa tebakan expense');
ok(!q('#tT [data-mproc]') && !q('#tT [data-tbp]'), 'Tampungan Pengembalian Kasbon tanpa aksi proses');
nav('verk');
const vrow = (tb, txt) => qa(tb + ' tbody tr').find(tr => tr.textContent.includes(txt));
ok($('c_verk').textContent === '1' && $('cnt_vmb').textContent === '1' && /BUDI SANTOSO/.test($('verkT').textContent) && !/ZUHDI/.test($('verkT').textContent), 'Tab Mutasi Bank: hanya baris yang sudah dialokasikan Pengembalian Kasbon');
ok($('cnt_vaj').textContent === '3' && q('[data-vpane="aj"]').classList.contains('hidden'), 'Tab Pengajuan Karyawan berisi 3 expense');
click(vrow('#verkT', 'BUDI SANTOSO').querySelector('[data-verk]'));
ok(/SETORAN TUNAI BUDI/.test($('dlgB').textContent) && $('vkEr').options.length === 4 && !/cocok/.test($('vkEr').textContent), 'Modal: data mutasi bank + pilihan expense tanpa tanda cocok');
ok(!$('chk_mutasi').checked && !$('chk_mutasi').disabled && !$('chk_bukti').checked && !$('chk_nominal').checked, 'Ketiga checklist dicentang manual oleh Finance (tidak otomatis)');
ok_(); ok(/Pilih Expense Report/.test($('dlgErr').textContent), 'Expense wajib dipilih');
setVal($('vkEr'), 'ER-2026-00287');
ok(qa('#vkCmpT .ctl-bad').length >= 2, 'Expense yang salah: perbandingan menandai beda');
setVal($('vkEr'), 'ER-2026-00265');
ok(/bukti-transfer-budi\.jpg/.test($('vkEd').textContent) && qa('#vkCmpT .ctl-ok').length === 4, 'Expense benar: perbandingan sama');
ok(/1103-01-14/.test($('vkJv').textContent) && /Bank BCA STM/.test($('vkJv').textContent), 'Preview jurnal: Dr Bank / Cr Piutang Lain-lain Konsultan');
ok(/Pengembalian lebih kasbon/.test($('vkJv').textContent) && /Pelunasan sisa kasbon/.test($('vkJv').textContent) && /GRAND TOTAL/.test($('vkJv').textContent), 'Kasbon: keterangan per baris + GRAND TOTAL');
ok_(); ok(/checklist/.test($('dlgErr').textContent), 'Ketiga checklist wajib');
check($('chk_bukti')); check($('chk_nominal')); ok_(); ok(/checklist/.test($('dlgErr').textContent), 'Tanpa checklist Mutasi tidak bisa disetujui');
check($('chk_mutasi')); ok_(); await flush();
ok(/00001-BUM-XI-26/.test($('dlgB').textContent), 'Disetujui, Jurnal 2 dibukukan'); ok_();
ok($('cnt_vmb').textContent === '0' && $('cnt_vok').textContent === '1' && /ER-2026-00265/.test($('verkDT').textContent) && /00001-BUM-XI-26/.test($('verkDT').textContent), 'Pindah ke tab Selesai');
ok($('cnt_vaj').textContent === '2', 'Pengajuan berkurang');
nav('kas');
ok(/Terverifikasi · 00001-BUM-XI-26/.test($('tT').textContent), 'Tampungan menampilkan status terverifikasi');
check(tRows().find(tr => tr.textContent.includes('BUDI SANTOSO')).querySelector('.check_item')); click($('tRollback'));
ok(/sudah diproses/.test($('dlgB').textContent), 'Baris terverifikasi tidak bisa di-rollback'); ok_();
// Zuhdi: alokasikan dulu
nav('alo'); click(rowOf('ZUHDI RAHMAN PENGEMBALIAN').querySelector('[data-alo]')); setVal(q('[data-sj="0"]'), '3'); setVal(q('[data-sn="0"]'), '1250000', 'input'); ok_(); await flush(); ok_();
nav('verk'); ok(/ZUHDI/.test($('verkT').textContent) && $('c_verk').textContent === '1', 'Setelah dialokasikan, muncul di tab Mutasi Bank');
// Tolak dari tab Pengajuan Karyawan
click(q('[data-vtab="aj"]')); ok(!q('[data-vpane="aj"]').classList.contains('hidden'), 'Pindah tab Pengajuan Karyawan');
click(vrow('#verkAT', 'ER-2026-00310').querySelector('[data-vrej]')); ok_(); ok(/wajib/.test($('dlgErr').textContent), 'Alasan tolak wajib');
setVal($('vkAl'), 'Belum ada dana masuk di rekening BCA Sustain', 'input'); ok_(); await flush(); ok_();
ok(/Ditolak/.test(vrow('#verkAT', 'ER-2026-00310').textContent) && $('cnt_vaj').textContent === '1', 'Ditolak, kembali ke pelapor');
click(q('[data-vtab="mb"]'));

// AP-R-37: jurnal Mutasi (Jurnal Bank) dan Pengembalian Kasbon (Expense) di Jurnal All
{
const J = window.eval('S').jur;
nav('jall'); click(q('#jaTabs [data-jt="EXP"]'));
ok(jaRowsT().length === J.filter(j => j.jt === 'EXP').length && J.some(j => j.jt === 'EXP') && /Pengembalian kasbon/.test($('jaT').textContent) && !$('jaCat'), 'Jurnal All · Expense Report: jurnal Pengembalian Kasbon (dibuat saat disetujui, belum posting)');
ok(J.filter(j => j.jt === 'EXP').every(j => j.lines[1][0] === '1103-01-14'), 'Kasbon: Cr Piutang Lain-lain Konsultan');
click(q('#jaTabs [data-jt="BANK"]')); setVal($('jaCat'), 'Mutasi', 'change'); click($('jaApply'));
ok(jaRowsT().length === J.filter(j => j.jenis === 'Mutasi').length && J.some(j => j.jenis === 'Mutasi') && /MTR-/.test($('jaT').textContent), 'Jurnal Bank · Mutasi: jurnal per sisi dengan nomor MTR');
ok(J.every(j => Math.round(j.lines.reduce((a, l) => a + l[2] - l[3], 0) * 100) === 0), 'Semua jurnal di Jurnal All balance');
}

// ===== Unlocated -> Penerimaan Piutang Kwitansi Deposit =====
await procUnl('KERETA API'); await procUnl('MADA WIKRI');
check(tRows().find(tr => tr.textContent.includes('KERETA API')).querySelector('.check_item'));
check(tRows().find(tr => tr.textContent.includes('MADA WIKRI')).querySelector('.check_item'));
click($('tMoveKw'));
ok(/Penerimaan Piutang Kwitansi Deposit/.test($('dlgB').textContent) && $('mvKwSel') && !q('[name=mvTo]'), 'Tombol Kwitansi Deposit langsung ke modal kwitansi');
setVal($('mvKwSel'), 'KD/2026/11/0003'); ok_();
ok(/hanya bisa dipilih untuk 1 data/.test($('dlgErr').textContent), 'Kwitansi hanya untuk 1 data'); click($('dlgNo'));
check(tRows().find(tr => tr.textContent.includes('MADA WIKRI')).querySelector('.check_item'), false);
click($('tMoveKw'));
ok(/KD\/2026\/11\/0003 .*\(cocok\)/.test($('mvKwSel').textContent), 'Kwitansi yang nominalnya sama ditandai cocok');
setVal($('mvKwSel'), 'KD/2026/11/0003'); ok_(); await flush();
ok(/tab Penerimaan Piutang Kwitansi Deposit/.test($('dlgB').textContent), 'Pesan sukses menyebut tab tujuan'); ok_();
nav('pp');
const ppk = qa('#ppKT tbody tr').find(tr => tr.textContent.includes('KERETA API'));
ok(ppk && /KD\/2026\/11\/0003/.test(ppk.textContent) && /Draft/.test(ppk.textContent) && !/KERETA API/.test($('ppT').textContent), 'Draft di tab Penerimaan Piutang Kwitansi Deposit');
ok($('cnt_ppdep').textContent === '1', 'Jumlah di tab Kwitansi Deposit');
nav('alo'); ok(/Deposit/.test(rowOf('KERETA API').textContent), 'Status alokasi jadi Deposit');
nav('unl'); utab('sudah'); check(tRows().find(tr => tr.textContent.includes('MADA WIKRI')).querySelector('.check_item')); click($('tMoveKw'));
ok(!/KD\/2026\/11\/0003/.test($('mvKwSel').textContent) && /KD\/2026\/11\/0005 .*\(cocok\)/.test($('mvKwSel').textContent), 'Kwitansi yang sudah dipakai tidak muncul lagi'); click($('dlgNo'));
click($('tMoveKw')); ok_(); await flush(); ok_();
nav('pp'); ok(qa('#ppKT tbody tr').some(tr => tr.textContent.includes('MADA WIKRI')), 'Tanpa kwitansi juga boleh (opsional)');

// ===== Dashboard Alokasi =====
nav('dash');
const cards = () => qa('#dashCards .dash-card');
const cardN = k => { const c = q(`[data-dash="${k}"]`); const m = c.querySelector('.dc-n').textContent.match(/^(\d+)/); return m ? +m[1] : 0; };
const nAll = () => cards().reduce((a, c) => a + cardN(c.dataset.dash), 0);
ok(cards().length === 9 && ['open','inv','dep','unl','kal','kas','mut','tb','pay'].every(k => q(`[data-dash="${k}"]`)), 'Dashboard: 9 card per jenis alokasi');
ok(nAll() === qa('#dashT tbody tr').filter(tr => !tr.querySelector('.dt-empty')).length && /semua data/.test($('dashTot').textContent), 'Jumlah card = baris rincian (tanpa filter, semua data)');
ok(cardN('inv') === 0 && /Tuntas/.test(q('[data-dash="inv"]').textContent) || cardN('inv') > 0, 'Card menampilkan jumlah atau Tuntas');
ok(/Tertua/.test($('dashCards').textContent) && /0–7/.test($('dashCards').textContent), 'Card memuat umur tertua dan sebaran umur');
const totalAll = nAll();
click($('dfXls'));
let xw = XLSX_CALLS.pop(); const ring = xw.wb.sheets[0].ws.__aoa, rin = xw.wb.sheets[1].ws.__aoa;
ok(/^Dashboard_Alokasi_/.test(xw.file) && xw.wb.sheets.map(x => x.name).join() === 'Ringkasan,Rincian' && ring[2][1] === 'Semua Company' && rin.length === 7 + totalAll, 'Export tanpa filter: semua data');
ok(ring[7][0] === 'Belum Dialokasikan' && ring[ring.length - 1][0] === 'TOTAL' && ring[ring.length - 1][1] === totalAll, 'Export: sheet Ringkasan per card + total');
setVal($('dfCo'), 'STM');
ok(qa('#dfBank option').length === 2 && /Bank STM/.test($('dfBank').textContent), 'Company STM: pilihan Bank menyempit');
const nStm = nAll();
ok(nStm < totalAll && qa('#dashT tbody tr').every(tr => tr.children[3].textContent === 'STM') && /sesuai filter/.test($('dashTot').textContent), 'Filter Company STM');
click($('dfXls')); xw = XLSX_CALLS.pop();
ok(xw.wb.sheets[0].ws.__aoa[2][1] === 'STM' && xw.wb.sheets[1].ws.__aoa.length === 7 + nStm && xw.wb.sheets[1].ws.__aoa.slice(7).every(r => r[3] === 'STM'), 'Export mengikuti filter Company');
setVal($('dfCo'), ''); setVal($('dfStart'), '2026-11-18'); setVal($('dfEnd'), '2026-11-18');
ok(qa('#dashT tbody tr').every(tr => /18-Nov/.test(tr.children[2].textContent) || tr.querySelector('.dt-empty')) && nAll() < totalAll, 'Filter rentang tanggal');
click($('dfXls')); ok(/18-November-2026 s.d. 18-November-2026/.test(XLSX_CALLS.pop().wb.sheets[0].ws.__aoa[1][1]), 'Export mencatat periode filter');
setVal($('dfStart'), ''); setVal($('dfEnd'), ''); setVal($('dfBank'), 'b3');
ok(qa('#dashT tbody tr').every(tr => tr.children[3].textContent === 'VUCA' || tr.querySelector('.dt-empty')), 'Filter Bank OCBC Vuca');
click($('dfReset')); ok(nAll() === totalAll && $('dfBank').value === '' && $('dfCo').value === '', 'Reset filter');
click(q('[data-dash="mut"]'));
ok(!$('pg-dash').classList.contains('hidden') && q('[data-dash="mut"]').classList.contains('sel') && /— Mutasi/.test($('dashH').textContent), 'Klik card: tetap di dashboard, card tersorot');
ok(qa('#dashT tbody tr').length === cardN('mut') && qa('#dashT tbody tr').every(tr => tr.children[1].textContent === 'Mutasi'), 'Rincian di bawah hanya jenis card terpilih');
click(q('[data-dash="mut"]')); ok(!q('.dash-card.sel') && /semua jenis/.test($('dashH').textContent) && qa('#dashT tbody tr').length === totalAll, 'Klik card lagi: kembali ke semua jenis');
click(q('[data-dash="tb"]')); click($('dashAll')); ok(!q('.dash-card.sel') && $('dashGo').classList.contains('hidden'), 'Tombol Tampilkan semua jenis');
click(q('[data-dash="open"]')); click($('dashGo')); ok(!$('pg-alo').classList.contains('hidden'), 'Buka menu dari rincian card');
nav('dash'); click(q('[data-dash="dep"]')); click($('dashGo')); ok(!$('pg-pp').classList.contains('hidden') && !q('[data-pppane="dep"]').classList.contains('hidden'), 'Card Kwitansi Deposit membuka tab Kwitansi Deposit');

// ===== test-case-unlocated.md (setelah Reset data) =====
click($('btnReset')); ok_(); await flush();
nav('unl');
ok(tRows().length === 6 && ['MADA WIKRI', 'KERETA API', 'INDO KORDSA', 'SETORAN TUNAI', 'SURYA BANGUN', 'HEINZ ABC'].every(n => tRows().some(tr => tr.textContent.includes(n))), 'UNL-01: 6 case Unlocated di Belum Diproses');
const procBy = async n => { click(tRows().find(tr => tr.textContent.includes(n)).querySelector('[data-uproc]')); const b = $('dlgB').textContent; ok_(); await flush(); const m = $('dlgB').textContent; ok_(); return [b, m]; };
let [pb, pm] = await procBy('KERETA API');
ok(/1101-02-07/.test(pb) && /UNL-261100001/.test(pm) && /BUM\/VUCA\/2611\/0001/.test(pm), 'UNL-02/03: KERETA API → UNL-261100001, BUM/VUCA/2611/0001');
[pb, pm] = await procBy('INDO KORDSA');
ok(/1101-02-05/.test(pb) && /UNL-261100002/.test(pm) && /BUM\/SUSTAIN\//.test(pm), 'UNL-06: INDO KORDSA → UNL-261100002 buku SUSTAIN');
[pb, pm] = await procBy('SETORAN TUNAI');
ok(/1101-02-09/.test(pb) && /UNL-261100003/.test(pm) && /BUM\/STM\//.test(pm), 'UNL-06: SETORAN TUNAI → UNL-261100003 buku STM');
utab('sudah'); click($('tMove'));
ok(/minimal satu/.test($('dlgB').textContent), 'UNL-N1: pindah tanpa pilihan ditolak'); ok_();
check(tRows().find(tr => tr.textContent.includes('INDO KORDSA')).querySelector('.check_item')); click($('tMove'));
ok(/Referensi: UNL-261100002/.test($('dlgB').textContent), 'UNL-07: referensi UNL-261100002'); ok_(); await flush(); ok_();
nav('pp'); ok(/Dari Unlocated UNL-261100002/.test(qa('#ppT tbody tr').find(tr => tr.textContent.includes('INDO KORDSA')).textContent), 'UNL-08: draft Invoice membawa referensi');
nav('unl'); utab('belum'); await procBy('SURYA BANGUN'); utab('sudah');
check(tRows().find(tr => tr.textContent.includes('SURYA BANGUN')).querySelector('.check_item')); click($('tMoveKw'));
ok(/KD\/2026\/11\/0007 .*\(cocok\)/.test($('mvKwSel').textContent), 'UNL-09: KD/2026/11/0007 ditandai cocok');
setVal($('mvKwSel'), 'KD/2026/11/0007'); ok_(); await flush(); ok_();
nav('pp'); click(q('[data-pptab="dep"]'));
const sbf = qa('#ppKT tbody tr').find(tr => tr.textContent.includes('SURYA BANGUN'));
ok(sbf && /KD\/2026\/11\/0007/.test(sbf.textContent) && /Dari Unlocated UNL-/.test(sbf.textContent), 'UNL-10: draft Kwitansi Deposit + referensi'); click(q('[data-pptab="inv"]'));
nav('unl'); utab('belum'); [pb, pm] = await procBy('HEINZ ABC');
ok(/630,000\.00/.test(pb) && /BUM\/STM\//.test(pm), 'UNL-11: HEINZ ABC 630.000 buku STM');
nav('alo'); ok(/Penerimaan Piutang/.test(rowOf('HEINZ ABC').textContent), 'UNL-11: Penerimaan Piutang 18.870.000 di transaksi yang sama tetap');
nav('unl'); utab('sudah'); ok($('tRollback').classList.contains('hidden'), 'UNL-N4: tanpa Rollback di Sudah Diproses');

// Tutorial (driver.js)
nav('mut'); ok(!q('#pg-tamp .nav-tabs:not(.hidden)'), 'Menu Mutasi di Penerimaan tetap tanpa tab'); nav('unl'); ok(q('#pg-tamp #tUnlTabs:not(.hidden)'), 'Hanya Unlocated yang punya tab');
for (const p of ['alo','mut','tb','unl','kas','mts','mtsapv','tbk','verk','dash']) {
    nav(p); const pg = window.tourPage(), T = window.tourDef()[pg];
    ok(T && T.length && T.every(st => doc.querySelector(st.element.split(' ')[0]) && st.popover.title && st.popover.description), 'Tutorial halaman ' + p + ' menunjuk elemen yang ada');
}
ok(errors.length === 0, 'Tidak ada error JS: ' + errors.join(' | '));

// AP-R-37: jurnal Proses Unlocated di Jurnal All tab Penerimaan
nav('jall'); click(q('#jaTabs [data-jt="RCV"]')); setVal($('jaCat'), 'Unlocated Penerimaan', 'change'); click($('jaApply'));
{ const U = window.eval('S').jur.filter(j => j.jenis === 'Unlocated Penerimaan');
  ok(U.length > 0 && jaRowsT().length === U.length && /UNL-2611/.test($('jaT').textContent) && /RK /.test($('jaT').textContent), 'Jurnal All · Penerimaan · Kategori Unlocated Penerimaan: jurnal Proses Unlocated (No. Referensi = rekening koran)'); }
click($('jaReset'));

// ===== AP-R-40: Proses Penerimaan Piutang (penerimaan_uang add.php → consulting_process.php) =====
{
nav('pp'); const ikRow = () => qa('#ppT tbody tr').find(tr => tr.textContent.includes('INDO KORDSA'));
click(ikRow().querySelector('[data-ppgo]'));
ok($('ppMain').classList.contains('hidden') && $('puCust').value === '' && !q('#ppProcBox .tamp-note'), 'PP-01: Proses membuka form seperti sistem (customer dipilih sendiri, tanpa catatan tambahan)');
setVal($('puCust'), 'PT INDO KORDSA TBK');
click($('puNext')); ok(/PPh 23/.test($('dlgB').textContent), 'PP-02: PPh 23 Disetor wajib dipilih'); ok_();
setVal($('puPph'), 'N'); click($('puNext')); ok(/minimal satu/.test($('dlgB').textContent), 'PP-03: centang minimal satu invoice'); ok_();
ok(qa('#puDocT tbody tr').length === 1 && /INV 058\/SSC\/2026/.test($('puDocT').textContent) && /8,325,000\.00/.test($('puDocT').textContent), 'PP-04: daftar invoice terbuka customer (saldo 8.325.000)');
check(q('[data-puchk="INV 058/SSC/2026"]')); click($('puNext'));
const jr = () => qa('#puJvT tbody tr').map(tr => [...tr.cells].map(c => c.textContent));
ok(!q('#puJv .ja-meta') && /Penerimaan Belum Teridentifikasi/.test($('puJvNote').textContent) && /UNL-261100002/.test($('puJvNote').textContent) && qa('#puAlT thead th').map(t => t.childNodes[0].textContent.trim()).join('|') === 'Nomor Invoice|TAX Code|Penjualan|DPP|PPN|PPH 23|Total Tagihan|Total Tagihan|Piutang Dagang|Alokasi Penerimaan|Biaya Admin|Lebih Bayar|Sisa Piutang', 'PP-04b: kolom seperti consulting_process.php; preview jurnal tanpa kotak info; catatan hanya soal jurnal');
ok($('puKontrol').textContent === '0.00' && $('puOk') && jr()[0][3] === 'Penerimaan Belum Teridentifikasi' && jr()[0][6] === '8,325,000.00' && /Piutang Dagang Usaha/.test(jr()[1][3]) && jr()[1][7] === '8,325,000.00', 'PP-05: Kontrol 0; jurnal Dr Penerimaan Belum Teridentifikasi / Cr Piutang Dagang (draft dari Unlocated)');
ok(/INV 058\/SSC\/2026/.test(jr()[0][4]) && /UNL-261100002/.test(jr()[0][4]) && /GRAND TOTAL/.test($('puJvT').textContent), 'PP-06: keterangan per baris (akun - invoice - SPK, referensi Unlocated)');
setVal(q('[data-pu="INV 058/SSC/2026"][data-k="admin"]'), '15000');
ok(/maksimal Rp 10\.000/.test($('puErr').textContent) && /Kontrol belum 0/.test($('puErr').textContent) && $('puSave').disabled, 'PP-07: admin > 10.000 dan kontrol tidak 0 ditolak');
setVal(q('[data-pu="INV 058/SSC/2026"][data-k="admin"]'), '0'); setVal(q('[data-pu="INV 058/SSC/2026"][data-k="alok"]'), '8000000');
ok(/Kontrol belum 0/.test($('puErr').textContent), 'PP-08: alokasi kurang dari uang masuk = kontrol tidak 0');
setVal(q('[data-pu="INV 058/SSC/2026"][data-k="alok"]'), '8325000'); ok(!$('puSave').disabled, 'PP-09: kembali kontrol 0, tombol Proses aktif');
click($('puSave')); await flush(); ok(/00053\/PU\/XI\/26/.test($('dlgB').textContent), 'PP-10: tersimpan 00053/PU/XI/26'); ok_();
ok(q('#ppTabs li.active [data-pptab="rep"]') && /00053\/PU\/XI\/26/.test($('ppRT').textContent) && !ikRow(), 'PP-11: pindah ke Report Penerimaan, draft hilang');
ok(window.eval("S.inv.find(i=>i.no==='INV 058/SSC/2026').saldo") === 0, 'PP-12: saldo invoice jadi 0');
ok(!q('#ppRT [data-ppjv]') && window.eval("S.jur.find(j=>j.trx==='00053/PU/XI/26').lines[0][1]") === 'Penerimaan Belum Teridentifikasi' && window.eval("S.jur.find(j=>j.trx==='00053/PU/XI/26').tgl") === '2026-11-18', 'PP-13: jurnal tersimpan, tanggal = tanggal rekening koran (18-Nov), Report tanpa tombol tambahan');
// draft biasa (bukan Unlocated): Dr Bank, PPh 23 dipotong, cicilan + sisa piutang
window.eval("S.tx.find(t=>t.id==='D17').splits.push({id:'D17-1',j:1,nominal:12210000,used:null});"); click(q('[data-pptab="inv"]'));
click(qa('#ppT tbody tr').find(tr => tr.textContent.includes('ASAHI')).querySelector('[data-ppgo]'));
setVal($('puCust'), 'PT ASAHI DENSO INDONESIA'); ok(qa('#puDocT tbody tr').length === 2, 'PP-14: ASAHI punya 2 invoice terbuka');
setVal($('puPph'), 'Y'); check(q('[data-puchk="INV 281/STM/2026"]')); check(q('[data-puchk="INV 283/STM/2026"]')); click($('puNext'));
ok(q('[data-pu="INV 281/STM/2026"][data-k="pph"]').value === '220,000.00' && q('[data-pu="INV 283/STM/2026"][data-k="pph"]').value === '100,000.00', 'PP-15: PPh 23 default 2% Penjualan per invoice');
ok($('puKontrol').textContent === '0.00' && jr()[0][3] === 'Bank BCA STM' && jr()[0][6] === '11,990,000.00', 'PP-16: draft biasa dijurnal Dr Bank (kas invoice 1 = 12.210.000 - 220.000)');
ok(jr().some(r => /PPh 23 Disetor/.test(r[3]) && r[6] === '220,000.00') && jr().some(r => /sisa piutang/.test(r[4]) && r[6] === '5,230,000.00'), 'PP-17: Dr PPh 23 dan Dr Piutang sisa (invoice 2 teralokasi 320.000 = kas 220.000 + PPh 100.000)');
click($('puSave')); await flush(); ok_();
ok(window.eval("S.inv.find(i=>i.no==='INV 283/STM/2026').saldo") === 5230000 && window.eval("S.jur[S.jur.length-1].trx") === '00054/PU/XI/26', 'PP-18: invoice 2 tersisa 5.230.000, jurnal 00054');
// Kwitansi Deposit dari Unlocated (SURYA BANGUN → KD/2026/11/0007)
click(q('[data-pptab="dep"]')); click(qa('#ppKT tbody tr').find(tr => tr.textContent.includes('SURYA BANGUN')).querySelector('[data-ppgo]'));
setVal($('puCust'), 'PT SURYA BANGUN FAJAR'); check(q('[data-puchk="KD/2026/11/0007"]')); click($('puNext'));
ok($('puKontrol').textContent === '0.00' && jr()[0][3] === 'Penerimaan Belum Teridentifikasi' && q('[data-pu="KD/2026/11/0007"][data-k="alok"]').readOnly, 'PP-19: kwitansi deposit lunas penuh, Dr Penerimaan Belum Teridentifikasi');
click($('puSave')); await flush(); ok_();
ok(window.eval("S.kwd.find(k=>k.no==='KD/2026/11/0007').sts") === 'LUNAS', 'PP-20: kwitansi deposit jadi LUNAS');
nav('jall'); click(q('#jaTabs [data-jt="RCV"]')); setVal($('jaCat'), 'Penerimaan Piutang', 'change'); click($('jaApply'));
ok(/00053\/PU\/XI\/26/.test($('jaT').textContent) && /00054\/PU\/XI\/26/.test($('jaT').textContent), 'PP-21: jurnal penerimaan masuk Jurnal All · Penerimaan (belum posting)'); click($('jaReset'));
}

// ===== AP-R-41: Daftar UNL (record nomor Unlocated, dicari dua arah) =====
{
nav('unl'); utab('daftar');
const regRows = () => qa('#unlRegT tbody tr[data-unl]'); const regOf = n => regRows().find(tr => tr.textContent.includes(n));
ok(!$('unlReg').classList.contains('hidden') && $('tWrap').classList.contains('hidden') && regRows().length === 5 && $('cnt_unld').textContent === '5', 'UNL-R1: tab Daftar UNL berisi 5 nomor Unlocated (tabel biasa disembunyikan)');
ok(/Belum dipindahkan/.test(regOf('KERETA API').textContent) && /Sudah diterima/.test(regOf('INDO KORDSA').textContent) && /00053\/PU\/XI\/26/.test(regOf('INDO KORDSA').textContent) && /INV 058\/SSC\/2026/.test(regOf('INDO KORDSA').textContent), 'UNL-R2: status per UNL + dipakai di no penerimaan dan invoice');
ok(/Sudah diterima/.test(regOf('SURYA BANGUN').textContent) && /KD\/2026\/11\/0007/.test(regOf('SURYA BANGUN').textContent), 'UNL-R3: UNL kwitansi deposit tercatat diterima');
ok(window.eval("S.jur.find(j=>j.trx==='UNL-261100001').tgl") === '2026-11-11' && /11-Nov-2026/.test(window.eval("jvAll(S.jur.find(j=>j.trx==='UNL-261100001'),{preview:true})")), 'UNL-R3b: tanggal jurnal Unlocated = tanggal bank di rekening koran (11-Nov)');
ok($('unlSaldo').textContent === 'Rp 32,630,000.00', 'UNL-R4: saldo belum diterima = KERETA API 30 jt + SETORAN TUNAI 2 jt + HEINZ 630 rb (Penerimaan Belum Teridentifikasi)');
setVal($('unlQ'), '00053', 'input'); ok(regRows().length === 1 && regOf('INDO KORDSA'), 'UNL-R5: cari dari no penerimaan → UNL');
setVal($('unlQ'), 'OCBC', 'input'); ok(regRows().length === 2 && regOf('KERETA API') && regOf('SURYA BANGUN'), 'UNL-R6: cari dari bank → UNL');
setVal($('unlQ'), '', 'input'); click(regOf('KERETA API').querySelector('[data-jv]')); ok(/Unlocated Penerimaan/.test($('dlgB').textContent) && /GRAND TOTAL/.test($('dlgB').textContent), 'UNL-R7: buka jurnal UNL dari daftar'); click($('dlgNo'));
nav('alo'); const kai = qa('#aT tbody tr').find(tr => tr.textContent.includes('KERETA API'));
ok(/UNL-261100001/.test(kai.textContent), 'UNL-R8: Alokasi menampilkan nomor UNL di Status');
click(kai.querySelector('[data-unlno]')); ok(!$('unlReg').classList.contains('hidden') && $('unlQ').value === 'UNL-261100001' && regRows().length === 1, 'UNL-R9: klik nomor UNL di Alokasi → Daftar UNL tersaring');
setVal($('unlQ'), '', 'input'); nav('alo'); setVal($('aQ'), 'UNL-261100002', 'input'); ok(qa('#aT tbody tr').length === 1 && /INDO KORDSA/.test($('aT').textContent), 'UNL-R10: cari nomor UNL di Alokasi → rekening koran'); setVal($('aQ'), '', 'input');
nav('unl'); utab('belum');
}

// ===== Tutorial v2: tombol di baris atas konten, menu Halaman ini + alur proses lintas halaman =====
{
const TC = window.eval('TOUR_CFG');
ok(doc.querySelector('.tour-bar #tourBtn') && !doc.querySelector('.tour-fab'), 'Tutorial: tombol di baris atas konten, bukan mengambang di bawah (tidak menutupi Simpan)');
doc.getElementById('tourBtn').dispatchEvent(new window.MouseEvent('click', {bubbles: true}));
const tmenu = doc.getElementById('tourMenu');
ok(!tmenu.hidden && tmenu.querySelectorAll('[data-tour]').length === TC.flows().length + 1 && tmenu.querySelector('[data-tour="page"]'), 'Menu Tutorial: Halaman ini + ' + TC.flows().length + ' alur proses');
const tmiss = [];
const hid = el => !el || el.closest('.hidden,[hidden]') || (el.closest('.modal') && !el.closest('#dlg') && !el.closest('.modal').classList.contains('show')) || (el.closest('#dlg') && !el.closest('#dlg').classList.contains('on'));
for (const f of TC.flows()) { f.steps.forEach((s, i) => { if (s.go) s.go();  if (hid(doc.querySelector(s.el))) tmiss.push(f.title + ' #' + (i + 1) + ' ' + s.el); }); TC.cleanup(); }
ok(!tmiss.length, 'Setiap langkah alur proses menunjuk elemen yang tampil setelah halamannya dibuka: ' + tmiss.join('; '));
ok(TC.flows().every(f => f.steps.length >= 5 && f.steps.every(s => s.t && s.d && s.el)), 'Setiap langkah punya judul, penjelasan, dan elemen');
doc.getElementById('tourBtn').dispatchEvent(new window.MouseEvent('click', {bubbles: true}));
doc.getElementById('tourMenu').querySelector('[data-tour="0"]').dispatchEvent(new window.MouseEvent('click', {bubbles: true}));
ok(doc.getElementById('tourMenu').hidden && dlgOpen() && /belum termuat/.test($('dlgB').textContent), 'Tanpa driver.js: pesan tutorial belum termuat');
ok_();
}
console.log(`OK — ${checks} pemeriksaan lulus.`);
})().catch(e => { console.error(e); process.exit(1); });
