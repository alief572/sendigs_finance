'use strict';
// Smoke test mockup Invoicing (UMKM Non-PKP + tab Invoiced).
// Jalankan: node specs/docs/mockup-invoicing/smoke-test.js
// jsdom dicari dari node_modules biasa, atau dari folder di env JSDOM_PATH.
const fs = require('fs');
const path = require('path');
const assert = require('assert');
let JSDOM;
try { ({JSDOM} = require(process.env.JSDOM_PATH || 'jsdom')); }
catch (e) { console.error('jsdom tidak ditemukan. Pasang: npm i jsdom, atau set JSDOM_PATH.'); process.exit(2); }

const html = fs.readFileSync(path.join(__dirname, 'index.html'), 'utf8');
const XLSX_CALLS = [];
function fakeXlsx() {
    const col = n => String.fromCharCode(65 + n);
    return {
        utils: {
            aoa_to_sheet(aoa) { const ws = {}; aoa.forEach((row, r) => row.forEach((v, c) => { ws[col(c) + (r + 1)] = {v, t: typeof v === 'number' ? 'n' : 's'}; })); ws.__aoa = aoa; return ws; },
            book_new() { return {sheets: []}; },
            book_append_sheet(wb, ws, name) { wb.sheets.push({ws, name}); }
        },
        writeFile(wb, file) { XLSX_CALLS.push({wb, file}); }
    };
}
const errors = [];
const dom = new JSDOM(html, {runScripts: 'dangerously', pretendToBeVisual: true,
    beforeParse(w) { w.scrollTo = () => {}; w.XLSX = fakeXlsx(); w.addEventListener('error', e => errors.push(e.message)); }});
const {window} = dom, doc = window.document;
const $ = id => doc.getElementById(id);
const q = s => doc.querySelector(s);
const qa = s => [...doc.querySelectorAll(s)];
const click = el => el.dispatchEvent(new window.MouseEvent('click', {bubbles: true}));
const change = (el, v) => { el.value = v; el.dispatchEvent(new window.Event('change', {bubbles: true})); };
const journal = () => qa('#journalBody tr').map(tr => [...tr.children].map(td => td.textContent.trim()));
const rowOf = (tbody, text) => qa(`#${tbody} tr`).find(tr => tr.textContent.includes(text));
let checks = 0;
const ok = (c, m) => { assert.ok(c, m); checks++; };

// Daftar: tiga tab, tab Konsultasi hanya Uninvoiced
ok(qa('.invoice-tabs [data-tab]').map(b => b.dataset.tab).join() === 'consulting,other,invoiced', 'Tiga tab: Konsultasi, Non Konsultasi, Invoiced');
ok(qa('#bodyConsulting tr').length === 4 && qa('#bodyConsulting .badge').every(b => b.textContent === 'Uninvoiced'), 'Tab Konsultasi hanya TOP belum ditagihkan');
ok($('countInvoiced').textContent === '8', 'Tab Invoiced berisi 8 invoice (6 tahun 2026, 2 tahun 2025)');

// Buat invoice UMKM
click(rowOf('bodyConsulting', 'PT Karya Mandiri Sejahtera').querySelector('[data-add]'));
ok(!$('viewForm').classList.contains('hidden') && $('formTitle').textContent === 'Buat invoice konsultasi', 'Form buat invoice terbuka');
ok($('formBox').classList.contains('invoice-compact'), 'Mode tambah memakai tata letak compact');
ok($('fNumber').value === '263/STM/2026', 'Nomor invoice berikutnya');
ok(qa('#fTax option').map(o => o.textContent).join('|') === 'Pilih perlakuan PPN|PKP|WAPU|Kawasan Berikat|UMKM Non-PKP', 'Opsi perlakuan PPN termasuk UMKM Non-PKP');
ok(/Pilih perlakuan PPN dan COA/.test($('journalBody').textContent), 'Jurnal kosong sebelum memilih');
change($('fTax'), 'UMKM');
ok(/tidak memungut PPN/.test($('taxHelp').textContent), 'Keterangan UMKM tampil');
ok(q('[data-total="receivable"]').textContent === '2,000,000.00', 'Piutang awal UMKM = penjualan');
ok(['other', 'ppn', 'gross'].map(k => q(`[data-total="${k}"]`).textContent).join('|') === '1,833,333.33|0.00|2,000,000.00', 'UMKM: DPP tetap dihitung, PPN 0, total tagihan = penjualan');
ok(!$('vatNote').hidden, 'Catatan UMKM tidak memungut PPN tampil');
ok(/Pilih COA penjualan/.test($('journalBody').textContent), 'Jurnal minta COA penjualan');
change($('fSales'), 'sales_consulting');
let j = journal();
ok(j.length === 2, 'Jurnal UMKM dua baris');
ok(j[0][3] === 'Piutang Dagang Usaha' && j[0][5] === '2,000,000.00' && j[0][6] === '0.00', 'Debit Piutang = penjualan');
ok(j[1][2] === '4101-01-01' && j[1][5] === '0.00' && j[1][6] === '2,000,000.00', 'Kredit Penjualan Konsultasi = penjualan');
ok(!j.some(r => /PPN/.test(r[3])), 'Tanpa baris PPN');
ok(j.every(r => r[4] === `${r[3]} - 263/STM/2026 - 034/STM/MKT-SPK/IV/26 - UMKM Non-PKP`), 'Deskripsi berakhiran UMKM Non-PKP');
ok($('jDebit').textContent === $('jCredit').textContent, 'Jurnal balance');

// Pembanding perlakuan lain (angka JTEKT dari video aplikasi)
change($('fTax'), 'PKP'); ok(journal().length === 3, 'PKP 3 baris');
ok(q('[data-total="ppn"]').textContent === '220,000.00' && $('vatNote').hidden, 'PKP tetap menagih PPN, catatan UMKM tersembunyi');
change($('fTax'), 'WAPU'); ok(journal().length === 4, 'WAPU 4 baris');
change($('fTax'), 'KB'); ok(journal().every(r => r[4].endsWith(' - Kawasan Berikat')), 'KB berakhiran Kawasan Berikat');
change($('fTax'), 'UMKM');

// Validasi lalu simpan
click($('btnSave'));
ok($('formMsg').dataset.state === 'error' && /Nomor PO, Nomor faktur/.test($('formMsg').textContent), 'Simpan tanpa PO/faktur ditolak');
$('fPo').value = 'PO-KMS-002'; $('fFaktur').value = '-';
click($('btnSave'));
ok(!$('viewList').classList.contains('hidden') && !$('panelInvoiced').classList.contains('hidden'), 'Setelah simpan pindah ke tab Invoiced');
ok(!rowOf('bodyConsulting', 'PT Karya Mandiri Sejahtera'), 'TOP hilang dari tab Konsultasi');
const newRow = rowOf('bodyInvoiced', '263/STM/2026');
ok(newRow && /UMKM Non-PKP/.test(newRow.textContent) && /Invoiced/.test(newRow.textContent), 'Invoice baru ada di tab Invoiced');
ok($('countInvoiced').textContent === '9' && $('countConsulting').textContent === '3', 'Hitungan tab diperbarui');

// Filter tab Invoiced
change($('fTaxI'), 'UMKM'); click($('btnFilterI'));
ok(qa('#bodyInvoiced tr').length === 2 && qa('#bodyInvoiced tr').every(tr => /UMKM Non-PKP/.test(tr.textContent)), 'Filter perlakuan PPN UMKM');
change($('fTaxI'), ''); change($('fMonthI'), '10'); click($('btnFilterI'));
ok(qa('#bodyInvoiced tr').length === 2, 'Filter bulan Oktober');
change($('fMonthI'), ''); click($('btnFilterI'));
$('qI').value = 'PELNI'; $('qI').dispatchEvent(new window.Event('input'));
ok(qa('#bodyInvoiced tr').length === 1, 'Pencarian tab Invoiced');
$('qI').value = ''; $('qI').dispatchEvent(new window.Event('input'));

// Card Total Invoiced mengikuti filter, dan sama dengan jumlah baris tabel
const num = s => Number(String(s).replace(/,/g, ''));
const tableSum = label => qa('#bodyInvoiced tr').reduce((n, tr) => { const dts = [...tr.querySelectorAll('.money-stack dt')]; const i = dts.findIndex(d => d.textContent === label); return n + (i < 0 ? 0 : num(tr.querySelectorAll('.money-stack dd')[i].textContent)); }, 0);
const cardsMatch = () => Math.abs(num($('cardTotal').textContent) - tableSum('Total tagihan')) < 0.005 && Math.abs(num($('cardSales').textContent) - tableSum('Penjualan')) < 0.005
    && Math.abs(num($('cardPpn').textContent) - tableSum('PPN')) < 0.005 && Math.abs(num($('cardReceivable').textContent) - tableSum('Saldo piutang')) < 0.005
    && +$('cardCount').textContent === qa('#bodyInvoiced tr').length;
const applyI = (y, m, c, t) => { change($('fYearI'), y); change($('fMonthI'), m); change($('fCompanyI'), c); change($('fTaxI'), t); click($('btnFilterI')); };
applyI('2026', '', '', '');
ok(qa('#bodyInvoiced tr').length === 7 && $('cardCount').textContent === '7' && cardsMatch(), 'Card default: tahun 2026 (7 invoice), sama dengan tabel');
ok(/Tahun 2026 · Semua bulan · Semua company/.test($('cardScope').textContent), 'Card menyebut filter aktif');
ok(qa('#fMonthI option').length === 13 && qa('#fCompanyI option').map(o => o.value).join() === ',STM,Vuca,Sustain', 'Pilihan bulan 12 dan company: STM, Vuca, Sustain');
applyI('2025', '', '', '');
ok(qa('#bodyInvoiced tr').length === 2 && cardsMatch() && $('cardSales').textContent === '48,954,000.00', 'Filter tahun 2025');
applyI('2026', '10', '', '');
ok(qa('#bodyInvoiced tr').length === 2 && cardsMatch(), 'Filter bulan Oktober 2026');
applyI('2026', '', 'STM', '');
ok(qa('#bodyInvoiced tr').length === 3 && cardsMatch() && $('cardCount').textContent === '3', 'Filter company STM');
applyI('2026', '', 'Vuca', '');
ok($('cardCount').textContent === '0' && /Tidak ada invoice yang sesuai/.test($('bodyInvoiced').textContent), 'Filter company Vuca (tanpa data contoh)');
applyI('2026', '', 'Sustain', 'UMKM');
ok(qa('#bodyInvoiced tr').length === 2 && $('cardPpn').textContent === '0.00' && $('cardTotal').textContent === $('cardSales').textContent, 'Company + UMKM: PPN 0, total = penjualan');
applyI('2026', '12', '', '');
ok(/Tidak ada invoice yang sesuai/.test($('bodyInvoiced').textContent) && $('cardTotal').textContent === '0.00' && $('cardCount').textContent === '0', 'Filter tanpa hasil: card 0');
click($('btnExportI'));
ok(XLSX_CALLS.length === 0 && /Tidak ada invoice untuk diekspor/.test($('toast').textContent), 'Ekspor kosong ditolak');

// Ekspor Excel = baris yang tampil + baris Total
applyI('2026', '', 'Sustain', '');
click($('btnExportI'));
const exp = XLSX_CALLS[XLSX_CALLS.length - 1], ws = exp.wb.sheets[0].ws, aoa = ws.__aoa;
ok(exp.file === 'Invoiced_2026_Semua-bulan_Sustain_Semua-PPN.xlsx' && exp.wb.sheets[0].name === 'Invoiced', 'Nama file mengikuti filter');
const dataRows = aoa.slice(4, -1);
ok(aoa[3][1] === 'No. Invoice' && aoa[3][15] === 'Total Tagihan' && dataRows.length === qa('#bodyInvoiced tr').length, 'Header dan jumlah baris Excel = tabel');
ok(dataRows.map(r => r[1]).join() === qa('#bodyInvoiced tr').map(tr => tr.querySelector('.invoice-cell-primary').textContent).join(), 'Urutan invoice Excel = tabel');
const last = 4 + dataRows.length, totalRow = last + 1;
ok(ws['P' + totalRow].f === `SUM(P5:P${last})` && Math.abs(ws['P' + totalRow].v - num($('cardTotal').textContent)) < 0.005, 'Baris Total memakai rumus dan sama dengan card');
const umkmRow = dataRows.find(r => r[11] === 'UMKM Non-PKP');
ok(umkmRow && umkmRow[13] > 0 && umkmRow[14] === 0 && umkmRow[15] === umkmRow[12], 'Excel: UMKM DPP dihitung, PPN 0, total = penjualan');
ok(/Filter: Tahun 2026, Semua bulan, Sustain/.test(aoa[1][0]) && /Ekspor 4 invoice/.test($('toast').textContent), 'Excel menulis filter; toast jumlah');
applyI('2026', '', '', '');

// Aksi -> Lihat invoice (detail)
click(rowOf('bodyInvoiced', '261/STM/2026').querySelector('[data-menu]'));
ok(!$('actionMenu').hidden && qa('#actionMenu [data-act]').length === 3, 'Menu Aksi terbuka dengan 3 pilihan');
click(q('#actionMenu [data-act="view"]'));
ok($('formTitle').textContent === 'Detail invoice konsultasi' && $('fTax').disabled && $('fPo').readOnly, 'Detail invoice read-only');
ok($('btnSave').classList.contains('hidden') && !$('salesView').classList.contains('hidden'), 'Detail tanpa tombol simpan');
j = journal();
ok(j.length === 2 && j[0][5] === '615,000.00' && j[0][1] === 'STM', 'Detail menampilkan jurnal tersimpan UMKM');
click(q('[data-back]'));

// Cetak (toast) dan toggle penanda perubahan
click(rowOf('bodyInvoiced', '258/STM/2026').querySelector('[data-menu]'));
click(q('#actionMenu [data-act="print"]'));
ok(/Cetak invoice 258/.test($('toast').textContent), 'Cetak invoice');
$('chgToggle').checked = false; $('chgToggle').dispatchEvent(new window.Event('change'));
ok(!doc.body.classList.contains('show-chg'), 'Toggle penanda perubahan');

ok(errors.length === 0, 'Tidak ada error JS: ' + errors.join('; '));
console.log(`PASS: ${checks} smoke checks mockup Invoicing`);

// Halaman kedua paket mockup: Penerimaan Piutang
require('./smoke-test-penerimaan.js');
