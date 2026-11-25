'use strict';
// Smoke test mockup Request Payment → Approval → Record (Excel) → Payment List.
// Jalankan: node specs/docs/request-payment/smoke-test.js
// jsdom dicari dari node_modules biasa, atau dari folder di env JSDOM_PATH.
const fs = require('fs');
const path = require('path');
const assert = require('assert');
let JSDOM;
try { ({JSDOM} = require(process.env.JSDOM_PATH || 'jsdom')); }
catch (e) { console.error('jsdom tidak ditemukan. Pasang: npm i jsdom, atau set JSDOM_PATH.'); process.exit(2); }

const html = fs.readFileSync(path.join(__dirname, 'index.html'), 'utf8').replace(/<script src=[^>]*><\/script>/g, '').replace(/<link [^>]*>/g, '');
const XLSX_CALLS = [];
const fakeXlsx = {
    utils: {
        aoa_to_sheet(aoa) { return {__aoa: aoa}; },
        book_new() { return {sheets: []}; },
        book_append_sheet(wb, ws, name) { wb.sheets.push({ws, name}); }
    },
    writeFile(wb, file) { XLSX_CALLS.push({wb, file}); }
};
const errors = [];
const OPENED = [];
const dom = new JSDOM(html, {runScripts: 'dangerously', pretendToBeVisual: true,
    beforeParse(w) { w.scrollTo = () => {}; w.XLSX = fakeXlsx; w.open = () => { const win = {html: '', document: {open() {}, write(h) { win.html += h; }, close() {}}}; OPENED.push(win); return win; }; w.alert = m => errors.push('alert: ' + m); w.addEventListener('error', e => errors.push(e.message)); }});
const {window} = dom, doc = window.document;
const $ = id => doc.getElementById(id);
const q = s => doc.querySelector(s);
const qa = s => [...doc.querySelectorAll(s)];
const click = el => { assert.ok(el, 'elemen untuk diklik tidak ada'); el.dispatchEvent(new window.MouseEvent('click', {bubbles: true})); };
const check = (el, v = true) => { assert.ok(el, 'checkbox tidak ada'); el.checked = v; el.dispatchEvent(new window.Event('change', {bubbles: true})); };
const setVal = (el, v, ev = 'change') => { el.value = v; el.dispatchEvent(new window.Event(ev, {bubbles: true})); };
const visible = id => !$(id).classList.contains('hidden');
const dlgOpen = () => $('dlg').classList.contains('on');
const ok_ = () => click($('dlgOk'));
const flush = () => new Promise(r => setTimeout(r, 0));
const nav = g => click(q(`.sidebar [data-go="${g}"]`));
let checks = 0;
const ok = (c, m) => { assert.ok(c, m); checks++; };

(async () => {
const rek = (i, bank, no, an) => { setVal(q(`[data-rb="${i}"]`), bank, 'input'); setVal(q(`[data-rn="${i}"]`), no, 'input'); setVal(q(`[data-ra="${i}"]`), an, 'input'); };
const pgjRek = (no, i) => { const p = window.eval(`pgj('${no}')`); return window.eval('rekTxt')(p.transfers[i].rek); };
const jrow = (re) => qa('#pfJ tr').some(tr => re.test(tr.textContent));

// ===== Data uji Finance (RP-R-29) =====
ok($('dataSet').value === 'finance' && !q('#finCases') && !q('#finXls'), 'Default data uji Finance, tanpa panel daftar test case');
const finDocs = window.eval('S.docs.filter(d => d.status === "wait").map(d => d.no)');
ok(['PERIODIK-2026-00234-1', 'RQ-2026-00216', 'DPM-2026-00100', 'PERIODIK-2026-00207-3', 'KS-2026-00259'].every(n => finDocs.includes(n)) && window.eval('S.pgjs.map(p => p.no).join()') === 'PGJ-0129,PGJ-0131,PGJ-0133', 'Data uji: dokumen Plan Cash Flow + PGJ real case');
ok(window.eval("tax(doc('PERIODIK-2026-00202-1')).net") === 5214499 - Math.ceil(5214499 * 0.025) && window.eval("tax(doc('DPM-2026-00100')).net") === 27010000 + Math.ceil(27010000 * 0.11) - Math.ceil(27010000 * 0.02), 'Data uji: PPh 21 2,5% dan PPN 11% + PPh 23 2%');
ok(window.eval("doc('KS-2026-00354').norek") === true && window.eval("docRek(doc('RQ-2026-00216')).bank") === 'OCBC', 'Data uji: dokumen tanpa rekening + rekening OCBC');
nav('pay'); click($('payChoose')); setVal($('jenisPay'), '2'); ok_(); await flush();
const cbF = k => qa('#plT .check_payment').find(c => c.value === k);
check(cbF('PGJ-0129/T1')); check(cbF('PGJ-0131/T1'));
ok(dlgOpen() && /PGJ berbeda/.test($('dlgB').textContent), 'Data uji RC3: transfer beda PGJ ditolak'); ok_();
nav('req');
check(q('[data-sel="PERIODIK-2026-00223"]')); click($('rpAtur'));
setVal(q('[data-adm="0"]'), '16500');
ok(!q('[data-bear="0"]').disabled && /16\.500/.test($('aturList').textContent), 'Admin bebas 16.500 (cicilan mobil) bisa diisi');
setVal(q('[data-adm="0"]'), '-300');
ok(q('[data-adm="0"]').value === '' && q('[data-bear="0"]').disabled, 'Admin minus dianggap 0');
setVal(q('[data-adm="0"]'), '3000'); setVal(qa('[data-bear="0"]').find(r => r.value === 'perusahaan'), 'perusahaan');
click($('aturOk')); ok_(); await flush(); ok_();
const pNew = window.eval('S.pgjs[S.pgjs.length - 1]');
ok(pNew.transfers[0].adm === 3000 && pNew.transfers[0].bearer === 'perusahaan', 'Admin 3.000 ditanggung perusahaan tersimpan di pengajuan');
setVal($('dataSet'), 'contoh'); await flush();
ok(visible('pg-req') && qa('#table_rp tbody tr').length === 19, 'Ganti ke data contoh mockup');

// ===== Request Payment index (sama dengan sistem) =====
ok(visible('pg-req'), 'Halaman awal Request Payment');
ok(qa('#table_rp tbody tr').length === 19 && $('rpCount').textContent === '19', '19 dokumen menunggu (semua kategori, termasuk 6 item pecahan PERIODIK-2026-00234), badge 19');
const r234 = q('tr[data-no="PERIODIK-2026-00234-6"]');
ok(r234 && /Dipecah otomatis dari pengajuan lama PERIODIK-2026-00234 \(item 6 dari 6\)/.test(r234.textContent) && /Ditolak: Ini ada kendala di PPN dan adminnya/.test(r234.textContent) && qa('#table_rp tbody tr').filter(tr => /^PERIODIK-2026-00234-/.test(tr.dataset.no)).length === 6, 'Real case 1: periodik lama 1 nomor 6 item dipecah otomatis per item');
ok(window.eval('S.docs.filter(d => d.split && d.split.from === "PERIODIK-2026-00234").reduce((a, d) => a + d.dpp, 0)') === 7279535, 'Total pecahan tetap 7.279.535');
ok(['Transport','Expense','Refill Pettycash','Periodik','Cash','Direct Payment','Kasbon','Petty Cash Hutang'].every(k => qa('#table_rp .rp-kategori-badge').some(b => b.textContent === k)), 'Contoh mencakup semua kategori sistem (label seperti sistem)');
ok(qa('#table_rp thead th').map(t => t.textContent.trim()).join('|') === '|NO|NO. DOKUMEN / KATEGORI|REQUEST BY|TANGGAL|KEPERLUAN|DPP (RP)|PPN|PPH 23|PPH 21|DIBAYARKAN', 'Kolom index sama dengan sistem, tanpa ADMIN');
ok(qa('#rpCards .rp-card').length === 9, '9 card ringkasan (Total Pengajuan, Total Nilai + 7 kategori)');
ok(/Ditolak: Tagihan September sudah dibayar lewat kartu fleet/.test(q('tr[data-no="PERIODIK-2026-00169"]').textContent), 'Catatan ditolak tampil');
click(q('[data-hist="PERIODIK-2026-00169"]'));
ok(dlgOpen() && /Histori Reject/.test($('dlgT').textContent), 'Histori reject terbuka'); ok_();
// filter
setVal($('fCo'), 'Vuca'); click($('rpFilter'));
ok(qa('#table_rp tbody tr').length === 3 && /Vuca/.test($('rpChip').textContent), 'Filter company Vuca');
click($('rpReset'));
ok(qa('#table_rp tbody tr').length === 19, 'Reset filter');
// kunci company
check(q('[data-sel="PERIODIK-2026-00176"]'));
ok($('rpBulk').classList.contains('show') && /Company: STM/.test($('rpBulk').textContent), 'Bulk bar muncul dengan company');
ok(q('tr[data-no="KS-2026-00210"]').classList.contains('rp-locked') && q('[data-sel="KS-2026-00210"]').disabled, 'Company lain terkunci');
check(q('[data-sel="PERIODIK-2026-00177"]'));
check(q('[data-no="PERIODIK-2026-00176"][data-f="ppn"]'));
check(q('[data-no="PERIODIK-2026-00177"][data-f="ppn"]'));
ok(/1\.332\.000/.test(q('tr[data-no="PERIODIK-2026-00176"]').textContent), 'PPN dihitung, dibayarkan 1.332.000');
check(q('[data-no="RQ-2026-00224"][data-f="pph21"]')); check(q('[data-no="RQ-2026-00224"][data-f="pph23"]'));
ok(!q('[data-no="RQ-2026-00224"][data-f="pph21"]').checked, 'PPh 23 dan 21 saling eksklusif');
check(q('[data-no="RQ-2026-00224"][data-f="pph23"]'), false);
// export
click($('rpExport'));
ok(XLSX_CALLS.pop().wb.sheets[0].ws.__aoa.length === 20, 'Export Excel index');

// ===== Atur Pembayaran =====
click($('rpAtur'));
ok(visible('pg-atur') && qa('#aturList .trf-panel').length === 2, 'Atur Pembayaran: default 1 dokumen = 1 transfer');
ok(q('[data-rb="0"]').value === 'BCA' && q('[data-rn="0"]').value === '555.666.7777' && q('[data-ra="0"]').value === 'PT Polytron' && /Terisi otomatis dari rekening di dokumen/.test($('rsrc0').textContent), 'Transfer 1 dokumen: rekening terisi otomatis dari dokumen');
setVal(q('[data-ra="1"]'), 'PT Polytron Indonesia', 'input');
ok(/Transfer ke PT Polytron Indonesia/.test($('thd1').textContent) && /diubah/.test($('rsrc1').textContent), 'Rekening dari dokumen tetap bisa diedit');
ok($('aturMerge').disabled, 'Gabung nonaktif sebelum 2 dicentang');
check(q('[data-pick="PERIODIK-2026-00176"]')); check(q('[data-pick="PERIODIK-2026-00177"]'));
click($('aturMerge'));
ok(qa('#aturList .trf-panel').length === 1 && /Gabungan 2 dokumen/.test($('aturList').textContent), 'Digabung jadi 1 transfer');
setVal(q('[data-adm="0"]'), '2500');
ok(!q('[data-bear="0"]').disabled && /2\.661\.500/.test($('tsum0').textContent), 'Admin 2.500 ditanggung penerima: transfer 2.661.500');
const per = q('[data-bear="0"][value="perusahaan"]'); per.checked = true; per.dispatchEvent(new window.Event('change', {bubbles: true}));
ok(/2\.666\.500/.test($('tsum0').textContent) && $('aturOut').textContent === 'Rp 2.666.500', 'Ditanggung perusahaan: keluar bank 2.666.500');
click(q('[data-split="0"]'));
ok(qa('#aturList .trf-panel').length === 2 && q('[data-rn="1"]').value === '555.666.7777', 'Pisahkan: tiap transfer kembali terisi dari dokumennya');
check(q('[data-pick="PERIODIK-2026-00176"]')); check(q('[data-pick="PERIODIK-2026-00177"]')); click($('aturMerge'));
ok(q('[data-adm="0"]').value === '2500', 'Setting admin ikut saat digabung lagi');
ok(q('[data-adm="0"]').tagName === 'INPUT' && q('[data-adm="0"]').type === 'number', 'RP-R-31: admin diisi nominal (bukan pilihan)');
// lampiran tambahan per item (opsional)
const attach = (n, names) => { const inp = q(`[data-att="${n}"]`); Object.defineProperty(inp, 'files', {value: names.map(name => ({name}))}); inp.dispatchEvent(new window.Event('change', {bubbles: true})); };
ok(qa('[data-att]').length === 2 && /Tidak wajib/.test($('aturList').textContent), 'Kolom lampiran tambahan per item, tidak wajib');
attach('PERIODIK-2026-00176', ['faktur-revisi.pdf', 'foto-barang.jpg']);
ok(/faktur-revisi\.pdf/.test($('aturList').textContent) && /foto-barang\.jpg/.test($('aturList').textContent), 'File terlampir di item');
click(q('[data-attdel="PERIODIK-2026-00176"][data-atti="1"]'));
ok(!/foto-barang/.test($('aturList').textContent) && /faktur-revisi/.test($('aturList').textContent), 'Lampiran bisa dihapus');
click(q('[data-split="0"]')); ok(/faktur-revisi/.test($('aturList').textContent), 'Lampiran tetap saat dipisah');
check(q('[data-pick="PERIODIK-2026-00176"]')); check(q('[data-pick="PERIODIK-2026-00177"]')); click($('aturMerge'));
ok(/faktur-revisi/.test($('aturList').textContent), 'Lampiran tetap saat digabung');
// RP-R-33: keterangan dokumen bisa dilengkapi di Atur Pembayaran (to be), cetak dokumen tetap asli (as is)
const KEP = 'Sewa baterai mobil Polytron Oktober 2026 - unit B 1482 RYP, faktur revisi';
ok(q('[data-kep="PERIODIK-2026-00176"]') && q('[data-kep="PERIODIK-2026-00176"]').value === 'Sewa baterai mobil Polytron Oktober 2026 (Batch BP-2611-001)', 'Atur Pembayaran: keterangan bisa diedit, terisi keterangan dokumen');
setVal(q('[data-kep="PERIODIK-2026-00176"]'), KEP, 'input');
click(q('[data-split="0"]')); check(q('[data-pick="PERIODIK-2026-00176"]')); check(q('[data-pick="PERIODIK-2026-00177"]')); click($('aturMerge'));
ok(q('[data-kep="PERIODIK-2026-00176"]').value === KEP, 'Keterangan yang dilengkapi tetap saat dipisah / digabung');
// guard
nav('apv');
ok(dlgOpen() && /Batalkan pengaturan/.test($('dlgB').textContent), 'Guard meninggalkan Atur Pembayaran'); click($('dlgNo'));
ok(visible('pg-atur'), 'Tetap di Atur Pembayaran');
ok(qa('[data-rb="0"]').length === 1 && q('[data-rb="0"]').value === '' && q('[data-rn="0"]').value === '' && q('[data-ra="0"]').value === '' && /Transfer gabungan: isi ulang/.test($('rsrc0').textContent), 'Transfer gabungan: Bank, No. Rekening, Nama dikosongkan untuk diisi ulang');
click($('aturOk'));
ok(dlgOpen() && /Rekening tujuan belum lengkap/.test($('dlgB').textContent) && q('[data-rn="0"]').classList.contains('err'), 'Rekening tujuan wajib diisi'); ok_();
rek(0, 'BCA', 'abc', 'PT Polytron');
click($('aturOk'));
ok(dlgOpen() && /belum lengkap/.test($('dlgB').textContent), 'No. rekening harus angka'); ok_();
setVal(q('[data-rn="0"]'), '555.666.7777', 'input');
ok(/Transfer ke PT Polytron/.test($('thd0').textContent), 'Kepala transfer ikut nama penerima yang diketik');
click($('aturOk'));
ok(dlgOpen() && /2 dokumen \(1 transfer\)/.test($('dlgB').textContent), 'Konfirmasi ajukan'); ok_(); await flush();
ok(dlgOpen() && /PGJ-0077 berhasil dibuat/.test($('dlgB').textContent), 'Swal berhasil'); ok_();
ok(visible('pg-req') && $('rpCount').textContent === '17', 'Kembali ke Request Payment, sisa 17 dokumen');
ok(window.eval("doc('PERIODIK-2026-00176').kepRp") === KEP && window.eval("doc('PERIODIK-2026-00176').kep") === 'Sewa baterai mobil Polytron Oktober 2026 (Batch BP-2611-001)' && window.eval("doc('PERIODIK-2026-00177').kepRp") == null, 'Keterangan to be tersimpan, keterangan asli tetap; dokumen yang tidak diedit tanpa to be');
click(q('[data-pdoc="RQ-2026-00224"]'));
ok(dlgOpen() && /Visit lab PT IPC, PT Asahi Denso/.test($('dlgB').textContent), 'Cetak dokumen di daftar Request Payment memakai keterangan asli'); ok_();

// Vuca: rekening wajib + gabung
check(q('[data-sel="KS-2026-00210"]')); check(q('[data-sel="KS-2026-00159"]'));
click($('rpAtur'));
check(q('[data-pick="KS-2026-00210"]')); check(q('[data-pick="KS-2026-00159"]')); click($('aturMerge'));
rek(0, 'Mandiri', '157.00.2233445.1', 'Zuhdi Rahman');
click($('aturOk')); ok_(); await flush(); ok_();
// STM: gabung dulu (kategori, request by, penerima boleh beda), baru pilih rekening tujuan
check(q('[data-sel="RQ-2026-00224"]')); check(q('[data-sel="ER-2026-00312"]')); check(q('[data-sel="RPC-2026-0037"]'));
click($('rpAtur'));
check(q('[data-pick="RQ-2026-00224"]')); check(q('[data-pick="RPC-2026-0037"]')); check(q('[data-pick="ER-2026-00312"]')); click($('aturMerge'));
ok(!dlgOpen() && qa('#aturList .trf-panel').length === 1 && /Gabungan 3 dokumen/.test($('aturList').textContent), 'Beda penerima (AndriH + Erna) tetap bisa digabung');
ok(/Penerima dokumen: AndriH, Erna/.test($('thd0').textContent), 'Setelah digabung: kepala transfer menampilkan penerima dokumen');
click($('aturOk'));
ok(dlgOpen() && /Rekening tujuan belum lengkap/.test($('dlgB').textContent), 'Rekening transfer gabungan wajib diisi'); ok_();
rek(0, 'BCA', '436.300.9911', 'Erna Susanti');
ok(/Transfer ke Erna Susanti/.test($('thd0').textContent), 'Rekening diisi → transfer ke nama penerima');
click(q('[data-split="0"]'));
ok(qa('#aturList .trf-panel').length === 3, 'Pisahkan lagi jadi 3 transfer');
check(q('[data-pick="RQ-2026-00224"]')); check(q('[data-pick="ER-2026-00312"]')); click($('aturMerge'));
ok(qa('#aturList .trf-panel').length === 2 && /Transport [+] Expense/.test($('aturList').textContent), 'Beda kategori (Transport + Expense) digabung');
ok(/Dimas/.test($('aturList').textContent) && q('[data-rb="0"]').value === '', 'Request by beda (AndriH + Dimas) digabung, rekening diisi setelah digabung');
rek(0, 'BCA', '301.220.4410', 'Andri Hidayat'); rek(1, 'BCA', '436.300.9911', 'Erna Susanti');
ok(/1\.079\.000/.test($('tsum0').textContent), 'Total gabungan 894.000 + 185.000');
click($('aturOk')); ok_(); await flush(); ok_();
ok($('cApv').textContent === '4', '4 pengajuan menunggu approval');

// ===== Approval =====
nav('apv');
ok(qa('#apvT tbody tr').length === 4 && $('apvCount').textContent === '4', 'Daftar Menunggu Approval');
ok(/PGJ-0077/.test($('apvT').textContent) && q('#apvT thead').textContent.includes('JUMLAH TRANSFER'), 'Kolom Jumlah Transfer');
click(q('[data-open="PGJ-0077"]'));
ok(visible('pg-apvdet') && qa('#adTrfT tbody tr').length === 1, 'Detail: Rincian Transfer 1 baris');
click(q('#adTA [data-rej="PERIODIK-2026-00177"]'));
ok(!$('adRejWrap').classList.contains('hidden') && qa('#adTR tbody tr').length === 2, 'Pindah ke Akan Ditolak dengan kotak alasan');
ok(/1\.332\.000/.test(q('#adTrfT tbody').textContent) && /1\.334\.500/.test(q('#adTrfT tbody').textContent), 'Transfer dihitung ulang, admin tetap 1x');
click($('adSave'));
ok(dlgOpen() && /Alasan reject kosong/.test($('dlgB').textContent), 'Alasan reject wajib'); ok_();
setVal(q('[data-reason="PERIODIK-2026-00177"]'), 'Tagihan November belum terbit', 'input');
click($('adSave')); ok_(); await flush(); ok_();
ok(visible('pg-apv') && qa('#apvT tbody tr').length === 3, 'Keputusan tersimpan, kembali ke daftar');
nav('req');
ok(/Ditolak: Tagihan November belum terbit/.test(q('tr[data-no="PERIODIK-2026-00177"]').textContent), 'Dokumen ditolak kembali ke Request Payment');
// PGJ-0079 (STM): tolak RPC → transfer Erna batal, transfer AndriH (Transport + Expense) tetap
nav('apv'); click(q('[data-open="PGJ-0079"]'));
click(q('#adTA [data-rej="RPC-2026-0037"]'));
ok(q('#adTrfT tbody tr.batal') && qa('#adTrfT tbody tr').length === 2, 'Semua dokumen 1 transfer ditolak → transfer batal');
setVal(q('[data-reason="RPC-2026-0037"]'), 'Nota tidak lengkap', 'input');
click($('adSave')); ok_(); await flush(); ok_();
// edit rekening di approval (hanya rekening)
click(q('[data-open="PGJ-0076"]'));
click(q('#adTrfT [data-erek="0"]'));
ok(dlgOpen() && $('erN').value === '157.00.1234567.8' && !q('#dlgB [data-adm]'), 'Approval: modal edit rekening (hanya rekening)');
setVal($('erN'), 'salah'); ok_();
ok(dlgOpen() && /No\. Rekening/.test($('dlgErr').textContent), 'Edit rekening divalidasi');
setVal($('erN'), '157.00.7654321.0'); ok_();
ok(/157\.00\.7654321\.0/.test($('adTrfT').textContent) && /diubah approver/.test($('adTrfT').textContent), 'Rekening baru tampil di Rincian Transfer');
click($('adSave')); ok_(); await flush(); ok_();
ok(pgjRek('PGJ-0076', 0) === 'Mandiri 157.00.7654321.0 a/n Budi Santoso', 'Rekening hasil edit tersimpan saat Simpan Keputusan');
for (const no of ['PGJ-0078']) { click(q(`[data-open="${no}"]`)); click($('adSave')); ok_(); await flush(); ok_(); }
ok($('apvCount').textContent === '0', 'Semua pengajuan diputuskan');

// ===== Record & Excel =====
nav('rec');
ok(qa('#recT tbody tr').length === 9, '9 batch di Record (termasuk contoh PGJ-0071..0074)');
const r72 = qa('#recT tbody tr').find(tr => tr.textContent.includes('PGJ-0072'));
ok(/Sebagian/.test(r72.textContent) && /1 dari 2 transfer/.test(r72.textContent) && /Sudah dibayar/.test(qa('#recT tbody tr').find(tr => tr.textContent.includes('PGJ-0071')).textContent), 'Kolom Status Pembayaran per PGJ');
click(q('[data-print="PGJ-0075"]'));
const a75 = XLSX_CALLS.pop().wb.sheets[0].ws;
ok(a75.__aoa.filter(r => /^Transfer \d/.test(r[16])).length === 5 && a75['!merges'].filter(m => m.s.c === 12 && m.e.r > m.s.r).length === 2 && a75.__aoa.some(r => /Digabung jadi 1 payment: 2 dokumen \(PERIODIK-2026-00161, PERIODIK-2026-00162\)/.test(r[18])), 'Contoh PGJ-0075: 3 transfer per item + 2 transfer gabungan');
click(q('[data-print="PGJ-0077"]'));
const xl = XLSX_CALLS.pop();
ok(xl.file === 'Record_Request_Payment_PGJ-0077.xlsx' && xl.wb.sheets[0].name === 'Record Payment', 'Print = unduh Excel pengajuan');
const aoa = xl.wb.sheets[0].ws.__aoa;
const H = ['NO','NO. DOKUMEN','KATEGORI','REQUEST BY','COMPANY','KEPERLUAN','DPP','PPN','PPH23','PPH21','ADMIN','DIBAYARKAN','JUMLAH DIBAYARKAN BANK','NAMA','NO. REK','NO. PENGAJUAN','TRANSFER','ADMIN DITANGGUNG','KETERANGAN PEMBAYARAN'];
ok(aoa[0][0] === 'Pengajuan Pembayaran PGJ-0077 (17 November 2026) - Pengajuan' && /^Company: STM/.test(aoa[1][0]) && /Dicetak pada: /.test(aoa[1][0]), 'RP-R-28 Excel: judul seperti Plan Cash Flow');
ok(H.every((x, i) => aoa[2][i] === x) && aoa[2].length === H.length && !aoa[2].some(h => /Nilai Pengajuan|Catatan|^DP$/i.test(h)), 'Excel: kolom Plan Cash Flow (tanpa Nilai Pengajuan, Catatan, DP) + kolom transfer di kanan');
const r1 = aoa[3];
ok(r1[5] === KEP, 'Excel Record: kolom KEPERLUAN memakai keterangan to be');
ok(r1[1] === 'PERIODIK-2026-00176' && r1[3] === 'Rini - STM' && r1[4] === 'STM' && r1[7] === 132000 && r1[10] === 2500 && r1[13] === 'PT Polytron' && r1[14] === 'BCA - 555.666.7777' && r1[15] === 'PGJ-0077', 'Excel: baris dokumen + transfer, NO. REK = Bank - nomor');
ok(r1[11] === 1332000 && r1[12] === 1334500 && /Perusahaan/.test(r1[17]) && /1 dokumen = 1 payment/.test(r1[18]) && !aoa.some(r => r[1] === 'PERIODIK-2026-00177') && aoa.filter(r => typeof r[0] === 'number').every(r => r.slice(6, 13).every(v => v === '' || v >= 0)), 'Excel: semua angka positif, JUMLAH DIBAYARKAN BANK = DIBAYARKAN + admin perusahaan (= Payment Bank), dokumen ditolak tidak ikut');
click(q('[data-print="PGJ-0078"]'));
const x78 = XLSX_CALLS.pop().wb.sheets[0].ws; const a78 = x78.__aoa;
const out78 = a78[3][11] + a78[4][11] + (/^Perusahaan/.test(a78[3][17]) ? a78[3][10] : 0);
ok(a78[3][11] === 4321027 && a78[4][11] === 6734801 && a78[3][12] === out78 && a78[4][12] === '' && x78['!merges'].some(m => m.s.r === 3 && m.e.r === 4 && m.s.c === 12), 'Excel: JUMLAH DIBAYARKAN BANK = uang keluar 1 transfer (2 dokumen), di-merge');
ok(/Digabung jadi 1 payment: 2 dokumen \(KS-2026-00210, KS-2026-00159\)/.test(a78[3][18]) && a78[3][13] === 'Zuhdi Rahman' && a78[4][18] === '' && x78['!merges'].some(m => m.s.r === 3 && m.e.r === 4 && m.s.c === 10), 'Excel: dokumen yang digabung diberi keterangan, sel transfer di-merge');
click(q('[data-print="PGJ-0079"]'));
const a79 = XLSX_CALLS.pop().wb.sheets[0].ws.__aoa;
ok(a79[3][13] === 'Andri Hidayat' && a79[3][11] + a79[4][11] === 1079000 && a79[3][12] === window.eval("pgjSum(pgj('PGJ-0079')).out") && /Transport \+ Expense/.test(a79[3][18]) && a79[5][5] === 'TOTAL', 'Transfer batal tidak masuk Excel');
// RP-R-27/28: ringkasan per company di bawah Print (Pengajuan, Saldo, Sisa Saldo)
const cs = a79.findIndex(r => r.includes('RINGKASAN PER COMPANY'));
ok(cs > 0 && a79[cs + 1].slice(5, 9).join('|') === 'COMPANY|PENGAJUAN|SALDO (isi manual)|SISA SALDO' && a79[cs + 2][5] === 'STM' && a79[cs + 2][6] === window.eval("pgjSum(pgj('PGJ-0079')).out") && a79[cs + 2][6] === a79.find(r => r[5] === 'TOTAL')[12] && a79[cs + 3][5] === 'Sustain' && a79[cs + 3][6] === 0 && a79[cs + 4][5] === 'Vuca' && a79[cs + 2][7] === '' && /^IF\(H\d+="","",H\d+-G\d+\)$/.test(a79[cs + 2][8].f) && a79[cs + 5][5] === 'GRAND TOTAL' && a79[cs + 5][6].f === `SUM(G${cs + 3}:G${cs + 5})`, 'Print: ringkasan per company (Pengajuan = uang keluar bank, Saldo manual, Sisa Saldo rumus, Grand Total)');
// Export Excel beberapa PGJ (tanpa pilih company, urut STM, Sustain, Vuca; PGJ sebagai kolom)
click($('recMultiXls'));
ok(dlgOpen() && !$('mxCo') && $('mxSt').value === 'belum' && $('mxFrom').value === '2026-11-01' && $('mxTo').value === '2026-11-17', 'Export beberapa PGJ: tanpa pilih company, default Belum dibayar, periode bulan berjalan');
const mx = () => qa('[data-mx]').map(c => c.dataset.mx).join(',');
ok(mx() === 'PGJ-0072,PGJ-0075,PGJ-0077,PGJ-0079,PGJ-0073,PGJ-0076,PGJ-0074,PGJ-0078' && /1 dari 2/.test(qa('#mxList tr').find(tr => tr.textContent.includes('PGJ-0072')).textContent), 'Semua company, urut STM, Sustain, Vuca; PGJ-0071 (lunas) tidak ikut, PGJ-0072 hanya 1 transfer');
check(q('[data-mx="PGJ-0077"]'), false);
ok(/^7 PGJ/.test($('mxSum').textContent), 'Lepas centang mengubah ringkasan');
setVal($('mxFrom'), '2026-10-01'); setVal($('mxSt'), '');
ok(mx().startsWith('PGJ-0071,PGJ-0072,PGJ-0075') && q('[data-mx="PGJ-0077"]').checked, 'Status Semua + periode Oktober: PGJ-0071 ikut, centang direset');
setVal($('mxTo'), '2026-09-01'); ok_();
ok(dlgOpen() && /melewati/.test($('dlgErr').textContent), 'Periode terbalik ditolak');
setVal($('mxTo'), '2026-11-17'); setVal($('mxSt'), 'belum'); setVal($('mxKat'), 'Kasbon');
ok(mx() === 'PGJ-0076,PGJ-0074,PGJ-0078', 'Filter kategori Kasbon: Sustain lalu Vuca');
setVal($('mxKat'), '');
ok_();
const xm = XLSX_CALLS.pop(); const wm = xm.wb.sheets[0].ws, am = wm.__aoa;
ok(!dlgOpen() && xm.file === 'Record_Request_Payment_01Oct2026-17Nov2026.xlsx' && xm.wb.sheets.length === 1 && am[0][0] === 'Pengajuan Pembayaran (01 Oktober - 17 November 2026) - Pengajuan' && /Status pembayaran: Belum dibayar \| Kategori: Semua/.test(am[1][0]), 'Excel beberapa PGJ: judul dari periode filter, info filter');
const hd = am[2];
ok(hd[4] === 'COMPANY' && hd[15] === 'NO. PENGAJUAN' && hd[17] === 'ADMIN DITANGGUNG', 'Excel: kolom COMPANY, NO. PENGAJUAN di kanan');
const dr = am.filter(r => typeof r[0] === 'number'), pg = dr.map(r => r[15]);
ok(dr.every(r => /^PGJ-\d{4}$/.test(r[15])) && dr.map(r => r[0]).join() === dr.map((r, i) => i + 1).join() && !am.some(r => typeof r[0] === 'string' && /^COMPANY: /.test(r[0])), 'Excel: nomor urut bersambung, tanpa baris judul company');
const coSeq = dr.map(r => r[4]).filter((c, i, a) => c !== a[i - 1]);
const firstSus = am.findIndex(r => r[4] === 'Sustain' && typeof r[0] === 'number'), firstVuca = am.findIndex(r => r[4] === 'Vuca' && typeof r[0] === 'number');
ok(coSeq.join('|') === 'STM|Sustain|Vuca' && am[firstSus - 1].length === 0 && am[firstVuca - 1].length === 0, 'Excel: STM di atas, Sustain di tengah, Vuca di bawah, dipisah baris kosong');
ok(pg.indexOf('PGJ-0073') > pg.lastIndexOf('PGJ-0079') && pg.indexOf('PGJ-0074') > pg.lastIndexOf('PGJ-0076') && !dr.some(r => r[1] === 'RPC-2026-0033' || r[1] === 'PERIODIK-2026-00159'), 'Excel: transfer yang sudah dibayar tidak ikut');
const rK = am.findIndex(r => r[1] === 'KS-2026-00180');
ok(am[rK][12] === 3950000 && am[rK + 1][1] === 'KS-2026-00181' && am[rK + 1][12] === '' && wm['!merges'].some(m => m.s.r === rK && m.e.r === rK + 1 && m.s.c === 12) && am[rK + 2][16] === 'Transfer 2', 'Excel: PGJ-0074 transfer gabungan (merge) lalu transfer per item');
const totRow = am.find(r => r[5] === 'TOTAL');
const ms = am.findIndex(r => r.includes('RINGKASAN PER COMPANY'));
const outCo = co => window.eval(`(() => { const f = {from: '2026-10-01', to: '2026-11-17', st: 'belum', kat: ''}; return mxMatch(f).filter(m => m.p.co === '${co}').reduce((a, m) => a + liveTrf(m.p).filter(m.keep).reduce((b, t) => b + calcT(actItems(m.p, t), t.adm, t.bearer).out, 0), 0); })()`);
ok(ms > 0 && ['STM', 'Sustain', 'Vuca'].every((co, k) => am[ms + 2 + k][5] === co && am[ms + 2 + k][6] === outCo(co)) && am[ms + 5][5] === 'GRAND TOTAL', 'Export beberapa PGJ: ringkasan per company = uang keluar bank (termasuk admin perusahaan)');
const admNet = dr.reduce((a, r) => a + (typeof r[10] === 'number' && r[10] ? (/^Perusahaan/.test(r[17]) ? r[10] : 0) : 0), 0);
ok(am[ms + 2][6] + am[ms + 3][6] + am[ms + 4][6] === totRow[12] && totRow[12] === totRow[11] + admNet, 'TOTAL JUMLAH DIBAYARKAN BANK = jumlah Pengajuan 3 company = DIBAYARKAN + admin perusahaan (admin penerima: transfer dikurangi, biaya bank tetap keluar) (RP-R-36)');
click(q('[data-view="PGJ-0076"]'));
ok(visible('pg-recdet') && qa('#rdTrfT tbody tr').length === 2 && /Belum dibayar/.test($('rdTrfT').textContent), 'Detail record: Rincian Transfer + status bayar');
click($('rdBack')); click(q('[data-view="PGJ-0077"]'));
const r176 = qa('#rdT tbody tr').find(tr => tr.textContent.includes('PERIODIK-2026-00176'));
ok(r176.textContent.includes(KEP) && /Keterangan asli: Sewa baterai mobil Polytron Oktober 2026 \(Batch/.test(r176.textContent), 'Record detail: keterangan to be + keterangan asli');
ok(/1 lampiran tambahan/.test(r176.textContent), 'Record detail: tanda lampiran tambahan');
click(r176.querySelector('[data-pdoc]'));
ok(!$('dlgB').textContent.includes(KEP) && /Sewa baterai mobil Polytron Oktober 2026 \(Batch/.test($('dlgB').textContent), 'Cetak dokumen di Record tetap keterangan asli');
ok(dlgOpen() && /Lampiran awal/.test($('dlgB').textContent) && /dokumen-pendukung-PERIODIK-2026-00176\.pdf/.test($('dlgB').textContent) && /Lampiran tambahan dari Request Payment/.test($('dlgB').textContent) && /faktur-revisi\.pdf/.test($('dlgB').textContent), 'Print Dokumen: lampiran awal + lampiran tambahan'); ok_();
const rOther = qa('#rdT tbody tr').find(tr => !tr.textContent.includes('PERIODIK-2026-00176'));
if (rOther) { click(rOther.querySelector('[data-pdoc]')); ok(/Tidak ada\./.test($('dlgB').textContent), 'Tanpa lampiran tambahan: tertulis tidak ada'); ok_(); }
click($('rdBack'));
click(q('#recTabs [data-t="items"]'));
ok(!$('recItems').classList.contains('hidden') && qa('#recIT tbody tr').length >= 6, 'Tab Per Transaksi');
click(qa('#recIT tbody tr').find(tr => tr.textContent.includes('PERIODIK-2026-00176')).querySelector('[data-pdoc]'));
ok(/faktur-revisi\.pdf/.test($('dlgB').textContent), 'Cetak Dokumen di Per Transaksi juga membawa lampiran tambahan'); ok_();

// ===== Payment List =====
nav('pay');
ok(visible('pg-pay') && qa('#payT tbody tr').length === 5, 'Payment List tab Non PR');
click($('payChoose')); ok_();
ok(/Jenis Payment/.test($('dlgErr').textContent), 'Jenis payment wajib dipilih');
setVal($('jenisPay'), '2'); ok_(); await flush();
ok(visible('pg-paylist'), 'List Request Payment');
const rows = qa('#plT tbody tr');
ok(rows.length === 15, '15 transfer belum dibayar (0072 T2, 0073×2, 0074×2, 0075×5, 0076×2, 0077, 0078, 0079 tanpa transfer batal)');
ok(qa('#plT thead th')[1].textContent.trim() === 'No. Pengajuan' && !q('#plT .trf-tag'), 'Payment List tanpa keterangan Transfer');
click($('plProses'));
ok(dlgOpen() && /Pilih minimal satu/.test($('dlgB').textContent), 'Proses tanpa pilih ditolak'); ok_();
const cb = k => qa('#plT .check_payment').find(c => c.value === k);
check(cb('PGJ-0077/T1')); check(cb('PGJ-0076/T1'));
ok(dlgOpen() && /1 payment = 1 transfer/.test($('dlgB').textContent) && !cb('PGJ-0076/T1').checked, 'Hanya 1 transfer per payment'); ok_();
click($('plProses'));
ok(visible('pg-payform') && /ditanggung perusahaan/.test($('pfBanner').textContent), 'Form Payment, banner admin dari pengajuan');
ok($('pfPayBank').value === '1332000' && $('pfCharge').value === '2500' && $('pfOut').textContent === '1,334,500.00' && $('pfAdmB').textContent === '2,500.00', 'RP-R-36: Payment Bank = nominal transfer 1.332.000, Bank Charge 2.500, total keluar bank 1.334.500, Administrasi Bank 2.500');
ok(!$('pfJWrap').classList.contains('hidden') && /pilih bank/.test($('pfJ').textContent) && /Pilih Bank/.test($('pfMsg').textContent), 'Jurnal langsung tampil, baris bank menunggu Pilih Bank');
ok(!/ · Periodik/.test($('pfJ').textContent) && jrow(/PERIODIK-2026-00176/) && /6202-01-05/.test($('pfJ').textContent) && jrow(/\(belum ada\)\s*PPN DN/), 'Jurnal periodik tanpa baris penanda: Biaya COA item + PPN DN');
ok(q('.pf-ppn').textContent === '132,000.00', 'PPN dari pengajuan tetap tampil angkanya');
ok(qa('#pfT thead th').map(t => t.textContent.trim()).join('|') === 'Request By / Supplier|Nomor Dokumen|DPP|PPN|PPH 23|PPH 21|DIBAYARKAN' && !q('#pfT select'), 'Kolom Form Payment seragam dengan Request Payment, tanpa dropdown PPh');
setVal($('pfBank'), 'BCA');
ok(!$('pfJWrap').classList.contains('hidden') && $('pfJD').textContent === $('pfJK').textContent, 'Jurnal balance');
ok(/No Transaksi \(No Payment\): BK-1126-\d{4}/.test($('pfNoTrx').textContent) && /1 nomor/.test($('pfNoTrx').textContent) && (($('pfNoTrx').textContent.match(/BK-/g) || []).length === 1), 'RP-R-35: 1 payment = 1 nomor BK untuk seluruh jurnal');
ok(jrow(/Administrasi Bank\s*Biaya Transfer.*2,500\.00/) && jrow(/Bank STM.*TRF.*1,332,000\.00/) && jrow(/Bank STM.*BIAYA TRANSFER.*2,500\.00/), 'RP-R-36 ditanggung perusahaan (jurnal aktual): Dr Administrasi Bank, Cr Bank TRF + Cr Bank BIAYA TRANSFER');
Object.defineProperty($('pfFile'), 'files', {value: [{name: 'bukti-0077.pdf'}], configurable: true});
$('pfFile').dispatchEvent(new window.Event('change'));
ok(!$('pfSave').disabled && $('pfFileCnt').textContent === '1', 'Bukti diupload, siap submit');
setVal($('pfCharge'), '1500');
ok($('pfSave').disabled && /Keterangan Pembayaran wajib/.test($('pfMsg').textContent) && $('pfAdmB').textContent === '1,500.00' && jrow(/Bank STM.*BIAYA TRANSFER.*1,500\.00/), 'Bank Charge sesuai bukti bayar 1.500: Administrasi Bank ikut, keterangan wajib');
setVal($('pfKet'), 'Biaya transfer di bukti bayar Rp1.500', 'input');
ok(!$('pfSave').disabled && $('pfJD').textContent === $('pfJK').textContent, 'Siap submit, jurnal balance');
setVal($('pfPayBank'), '0', 'input');
ok($('pfSave').disabled && /Payment Bank harus lebih dari 0/.test($('pfMsg').textContent), 'Payment Bank 0 ditolak');
setVal($('pfPayBank'), '1332000', 'input'); setVal($('pfCharge'), '2500');
click($('pfSave')); ok_(); await flush(); ok_();
ok(visible('pg-pay') && /BK-1126-0005/.test($('payT').textContent) && $('cPay').textContent === '14', 'Payment tersimpan di tab Non PR, sisa 14 transfer');
// Jurnal per kategori: DPM (PPh 21, admin ditanggung penerima), Refill Petty Cash, Kasbon, Transport + Expense
const jurnal = async key => { nav('pay'); click($('payChoose')); setVal($('jenisPay'), '2'); ok_(); await flush(); check(cb(key)); click($('plProses')); setVal($('pfBank'), 'BCA'); return $('pfJ').textContent; };
let jt = await jurnal('PGJ-0075/T3');
ok(jrow(/Komisi - DPM-2026-00098/) && jrow(/sesuai item\s*Biaya\s*Komisi/) && jrow(/PPh\s*PPh 21/) && !jrow(/Administrasi Bank/) && $('pfJD').textContent === '1,000,000.00' && $('pfJK').textContent === '1,000,000.00', 'Jurnal DPM: COA master biaya, PPh 21, admin penerima tanpa Admin Charge');
ok(q('.pf-pph21').textContent === '25,000.00' && q('.pf-pph23').textContent === '-' && q('.pf-dpp').textContent === '1,000,000.00' && q('.pf-net').textContent === '975,000.00' && /Fany\s*Request by: Erna/.test($('pfT').textContent), 'PPh 21 di kolomnya sendiri, DIBAYARKAN = DPP - PPh, penerima beda dari pengaju tampil Request by');
ok(jrow(/TRF.*dikurangi admin Rp 6\.500.*968,500\.00/) && jrow(/BIAYA TRANSFER.*6,500\.00/) && $('pfPayBank').value === '968500' && $('pfCharge').value === '6500' && $('pfAdmB').textContent === '0.00' && !jrow(/Administrasi Bank/) && $('pfJD').textContent === '1,000,000.00', 'RP-R-36 ditanggung penerima (jurnal aktual): Cr Bank TRF 968.500 (975.000 − 6.500) + Cr Bank BIAYA TRANSFER 6.500, tanpa Administrasi Bank');
setVal($('pfCharge'), '7500');
ok($('pfAdmB').textContent === '1,000.00' && jrow(/Administrasi Bank.*1,000\.00/) && $('pfJD').textContent === $('pfJK').textContent, 'Bank charge diedit (penerima): selisih 1.000 jadi Administrasi Bank, jurnal balance');
click($('pfBack'));
jt = await jurnal('PGJ-0075/T2');
ok(!/Refill Petty Cash/.test(jt) && jrow(/1101-01-02\s*Kas Kecil\s*Refill Kas Kecil/), 'Jurnal Refill Petty Cash: debit Kas Kecil');
click($('pfBack'));
jt = await jurnal('PGJ-0078/T1');
ok(qa('#pfJ tr').filter(tr => /\(belum ada\)\s*Piutang Kasbon/.test(tr.textContent)).length === 2 && !/· Kasbon/.test(jt), 'Jurnal Kasbon: Dr Piutang Kasbon per dokumen');
click($('pfBack'));
jt = await jurnal('PGJ-0079/T1');
ok(jrow(/Transportasi - RQ-2026-00224/) && jrow(/Parkir visit lab PT IPC/) && jrow(/Tol visit lab PT IPC/) && $('pfJD').textContent === $('pfJK').textContent, 'Jurnal Transport + Expense (per item expense), balance');
click($('pfBack'));
click(q('[data-pview="BK-1126-0005"]'));
ok(/bukti-0077\.pdf/.test($('dlgB').textContent), 'View payment'); ok_();
nav('rec'); click(q('#recTabs [data-t="batch"]')); click(q('[data-view="PGJ-0077"]'));
ok(/Sudah dibayar/.test($('rdTrfT').textContent) && /BK-1126-0005/.test($('rdTrfT').textContent), 'Record menampilkan status sudah dibayar');

// Real case 4 (OCBC): admin aktual sesuai bukti bayar
jt = await jurnal('PGJ-0075/T1');
ok(!$('pfAktRow').classList.contains('hidden') && $('pfPayBank').value === '920000' && $('pfCharge').value === '6500', 'Form Payment: admin aktual default sesuai pengajuan (ditanggung penerima): Payment Bank = 926.500 − 6.500, Bank Charge 6.500');
setVal($('pfAkt'), 'gratis');
ok($('pfPayBank').value === '926500' && $('pfCharge').value === '0' && !jrow(/Administrasi Bank/) && !jrow(/BIAYA TRANSFER/) && !jrow(/dikurangi admin/) && $('pfJD').textContent === $('pfJK').textContent, 'Admin ga jadi (Gratis): transfer penuh 926.500, Bank Charge 0, tanpa admin di jurnal, balance');
setVal($('pfPayBank'), '920000', 'input');
ok(jrow(/Administrasi Bank\s*Admin dipotong dari transfer.*6,500\.00/) && $('pfJD').textContent === $('pfJK').textContent, 'Gratis tapi transfer tetap dikurangi admin (Payment Bank diubah sesuai bukti): selisih Cr Administrasi Bank');
setVal($('pfPayBank'), '926500', 'input');
ok(/admin aktual beda/.test($('pfMsg').textContent), 'Admin aktual beda pengajuan: keterangan wajib');
setVal($('pfAkt'), 'perusahaan');
ok($('pfPayBank').value === '926500' && $('pfCharge').value === '6500' && jrow(/Administrasi Bank\s*Biaya Transfer.*6,500\.00/) && $('pfJD').textContent === $('pfJK').textContent, 'Aktual ditanggung perusahaan: Payment Bank = DIBAYARKAN, Bank Charge 6.500, Dr Administrasi Bank');
setVal($('pfAkt'), 'gratis');
setVal($('pfKet'), 'OCBC tidak memotong admin transfer', 'input');
Object.defineProperty($('pfFile'), 'files', {value: [{name: 'bukti-ocbc.pdf'}], configurable: true});
$('pfFile').dispatchEvent(new window.Event('change'));
ok(!$('pfSave').disabled, 'Siap submit dengan admin gratis');
click($('pfSave')); ok_(); await flush(); ok_();
nav('rec'); click(q('#recTabs [data-t="batch"]')); click(q('[data-view="PGJ-0075"]'));
ok(/Admin aktual: Gratis/.test($('rdTrfT').textContent), 'Record: admin aktual tercatat');
// Real case 3: transfer beda PGJ tidak bisa digabung
nav('pay'); click($('payChoose')); setVal($('jenisPay'), '2'); ok_(); await flush();
check(cb('PGJ-0073/T1')); check(cb('PGJ-0074/T1'));
ok(dlgOpen() && /PGJ berbeda/.test($('dlgB').textContent) && !cb('PGJ-0074/T1').checked, 'Real case 3: transfer dari PGJ berbeda tidak bisa digabung jadi 1 payment'); ok_();

// ===== Record Payment (RP-R-25) =====
nav('recpay');
const rpyRows = () => qa('#rpyT tbody tr');
const rpyRow = no => rpyRows().find(tr => tr.textContent.includes(no));
ok(visible('pg-recpay') && rpyRows().length === 5 && $('rpyNAll').textContent === '5' && $('rpyNDiff').textContent === '3', 'Record Payment: 5 payment dari Request Payment, 3 ada perbedaan');
ok(/Penanggung admin.*Gratis/.test(rpyRow('BK-1126-0001').textContent) && /Bank charge: 6\.500 → 0 \(-6\.500\)/.test(rpyRow('BK-1126-0001').textContent) && /Transfer diterima penerima: 920\.000 → 926\.500 \(\+6\.500\)/.test(rpyRow('BK-1126-0001').textContent) && !/Total keluar bank/.test(rpyRow('BK-1126-0001').textContent) && /OCBC/.test(rpyRow('BK-1126-0001').textContent), 'OCBC (admin ga jadi): penerima terima penuh, bank charge 0, keluar bank sama, keterangan tampil');
ok(/Bank charge: 6\.500 → 2\.500 \(-4\.000\)/.test(rpyRow('BK-1026-0011').textContent) && /Total keluar bank: 3\.306\.500 → 3\.302\.500/.test(rpyRow('BK-1026-0011').textContent), 'Bank charge lebih kecil tercatat dengan selisih');
ok(/Sesuai pengajuan/.test(rpyRow('BK-1026-0012').textContent), 'Payment tanpa perbedaan: Sesuai pengajuan');
ok(/Penanggung admin/.test(rpyRow('PGJ-0075').textContent) && /OCBC tidak memotong admin transfer/.test(rpyRow('PGJ-0075').textContent), 'Payment baru (admin gratis) ikut tercatat dengan keterangannya');
click(q('#rpyTabs [data-t="diff"]'));
ok(rpyRows().length === 3 && !rpyRow('BK-1026-0012'), 'Tab Ada perbedaan');
click(q('#rpyTabs [data-t="same"]'));
ok(rpyRows().length === 2, 'Tab Sesuai pengajuan');
click(q('#rpyTabs [data-t="all"]'));
setVal($('rpySearch'), 'ocbc', 'input');
ok(rpyRows().length === 2, 'Cari keterangan');
setVal($('rpySearch'), '', 'input'); setVal($('rpyCo'), 'Sustain');
ok(/Belum ada payment/.test($('rpyT').textContent), 'Filter company');
setVal($('rpyCo'), '');
click(q('[data-rpyview="BK-1126-0001"]'));
ok(dlgOpen() && qa('#dlgB tr.warning').length === 3 && /PERIODIK-2026-00159/.test($('dlgB').textContent) && /bukti-BK-1126-0001\.pdf/.test($('dlgB').textContent), 'View: perbandingan per item, baris beda ditandai, dokumen dan bukti'); ok_();
// ===== Print Voucher / Bukti Bank Keluar (RP-R-37) =====
ok(rpyRows().every(tr => tr.querySelector('[data-vc]')), 'Record Payment: tombol Print Voucher per payment');
click(q('[data-vc="BK-1026-0011"]'));
const vc1 = OPENED[OPENED.length - 1].html;
ok(/BUKTI BANK KELUAR/.test(vc1) && /2026-STM\/BBK\/X\/01/.test(vc1) && /29 Oktober 2026/.test(vc1) && /: Pengeluaran/.test(vc1) && /data:image\/png;base64/.test(vc1), 'Voucher: judul, No 2026-STM/BBK/X/01, tanggal, keterangan, logo STM');
ok(/No\. Account<\/th><th>Nama Account<\/th><th>Keterangan<\/th><th>Debit<\/th><th>Kredit/.test(vc1) && /Listrik kantor - PERIODIK-2026-00158/.test(vc1) && /3\.300\.000,00/.test(vc1) && /Administrasi Bank/.test(vc1), 'Voucher: kolom Debit / Kredit, baris biaya dan Administrasi Bank');
ok(/Bank STM<\/td><td>BCA 436\.300\.5287 → TRF[^<]*<\/td><td class="r"><\/td><td class="r">3\.300\.000,00/.test(vc1) && /BIAYA TRANSFER[^<]*<\/td><td class="r"><\/td><td class="r">2\.500,00/.test(vc1) && /<b>Total<\/b> <b>:<\/b><\/td><td class="r">3\.302\.500,00<\/td><td class="r">3\.302\.500,00/.test(vc1), 'Voucher: jurnal lengkap dengan Cr Bank TRF + BIAYA TRANSFER, Total Debit = Kredit 3.302.500');
ok(/Disetujui/.test(vc1) && /Tati Rina/.test(vc1) && /Kasir/.test(vc1) && /Fikri Darmawan A/.test(vc1) && /@page\{size:A4 portrait/.test(vc1) && /window\.print\(\)/.test(vc1), 'Voucher: tanda tangan, A4 portrait, langsung dialog print (simpan PDF)');
click(q('[data-vc="BK-1126-0001"]'));
const vc2 = OPENED[OPENED.length - 1].html;
ok(/2026-STM\/BBK\/XI\/01/.test(vc2) && !/Administrasi Bank/.test(vc2) && !/BIAYA TRANSFER/.test(vc2) && /→ TRF[^<]*<\/td><td class="r"><\/td><td class="r">926\.500,00/.test(vc2) && /<b>Total<\/b> <b>:<\/b><\/td><td class="r">([\d.,]+)<\/td><td class="r">\1<\/td>/.test(vc2), 'Voucher admin ga jadi (OCBC): tanpa baris admin, Cr Bank TRF 926.500 penuh, Total Debit = Kredit');
const vcAll = window.eval('rpyAll().map(x => { const v = vcData(x); return v.totD === v.totK && v.bankOut === x.bank && v.L.some(l => l.bank); })');
ok(vcAll.length && vcAll.every(Boolean), 'Semua voucher: Debit = Kredit, baris bank = total keluar bank (Payment Bank + Bank Charge)');
click(q('[data-rpyview="BK-1026-0011"]'));
ok(dlgOpen() && $('dlgBox').classList.contains('lg') && /Preview Jurnal/.test($('dlgB').textContent) && qa('#rpyJ tbody tr').length === 4 && /TRF/.test($('rpyJ').textContent) && /BIAYA TRANSFER/.test($('rpyJ').textContent) && /Balancing\s*3,302,500\.00\s*3,302,500\.00/.test(q('#rpyJ tfoot').textContent), 'View Record Payment: preview jurnal (biaya, Administrasi Bank, Cr Bank TRF + BIAYA TRANSFER), balance'); ok_();
click($('rpyXls'));
const rx = XLSX_CALLS.pop(), ra = rx.wb.sheets[0].ws.__aoa;
ok(rx.file === 'Record_Payment.xlsx' && ra.length === 6 && ra[0].includes('PERBEDAAN') && ra[0].includes('KETERANGAN PEMBAYARAN') && ra.some(r => r[1] === 'BK-1126-0001' && /Gratis/.test(r[12])), 'Export Excel Record Payment');

// Reset
click($('btnReset')); ok_(); await flush();
ok(qa('#table_rp tbody tr').length === 19 && $('cApv').textContent === '1', 'Reset data mockup');

// Tutorial (driver.js): definisi langkah per halaman menunjuk elemen yang ada
const T = window.tourDef();
ok(['req','atur','apv','apvdet','rec','recdet','pay','paylist','payform'].every(p => T[p] && T[p].length), 'Tutorial tersedia untuk setiap halaman');
ok(Object.values(T).flat().every(st => doc.querySelector(st.element.split(' ')[0]) && st.popover.title && st.popover.description), 'Setiap langkah tutorial menunjuk elemen yang ada');

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
ok(errors.length === 0, 'Tidak ada error JS: ' + errors.join(' | '));
console.log(`OK — ${checks} pemeriksaan lulus.`);
})().catch(e => { console.error(e); process.exit(1); });
