'use strict';
// Smoke test mockup Penerimaan Piutang UMKM Non-PKP.
// Jalankan: node specs/docs/mockup-invoicing/smoke-test-penerimaan.js (ikut jalan dari smoke-test.js)
// jsdom dicari dari node_modules biasa, atau dari folder di env JSDOM_PATH.
const fs = require('fs');
const path = require('path');
const assert = require('assert');
let JSDOM;
try { ({JSDOM} = require(process.env.JSDOM_PATH || 'jsdom')); }
catch (e) { console.error('jsdom tidak ditemukan. Pasang: npm i jsdom, atau set JSDOM_PATH.'); process.exit(2); }

const html = fs.readFileSync(path.join(__dirname, 'penerimaan-piutang.html'), 'utf8');
const errors = [];
const dom = new JSDOM(html, {runScripts: 'dangerously', pretendToBeVisual: true,
    beforeParse(w) { w.addEventListener('error', e => errors.push(e.message)); }});
const {window} = dom, doc = window.document;
const $ = id => doc.getElementById(id);
const qa = s => [...doc.querySelectorAll(s)];
const click = el => el.dispatchEvent(new window.MouseEvent('click', {bubbles: true}));
const change = (el, v) => { el.value = v; el.dispatchEvent(new window.Event('change', {bubbles: true})); };
const num = s => Number(String(s).replace(/,/g, '')) || 0;
let checks = 0;
const ok = (c, m) => { assert.ok(c, m); checks++; };

// Ringkas jurnal: {nama akun: [debit, kredit]}
function journal() {
    const out = {};
    qa('#journalBody tr').forEach(tr => {
        const td = [...tr.children].map(c => c.textContent.trim());
        out[td[3]] = out[td[3]] || [0, 0];
        out[td[3]][0] += num(td[5]); out[td[3]][1] += num(td[6]);
    });
    return out;
}
const same = (a, b) => JSON.stringify(Object.keys(a).sort().map(k => [k, a[k]])) === JSON.stringify(Object.keys(b).sort().map(k => [k, b[k]]));
const loadCase = (k, choice) => click(doc.querySelector(`[data-case="${k}"][data-choice="${choice}"]`));

// Validasi form
change($('customer'), 'C1');
ok(qa('#invList input[type=checkbox]').length === 3, 'Tiga invoice terbuka untuk CV Kreasi Mandiri');
ok(!$('deposit_choice').disabled, 'Deposit bisa dipilih (saldo > 0)');
click($('btnProcess'));
ok(/Centang minimal satu invoice/.test($('fmsg').textContent), 'Validasi centang invoice');
ok(doc.querySelector('label[for=pph23_dipotong]').textContent.trim().startsWith('PPh 23/PPh Final Disetor'), 'Label pilihan potong: PPh 23/PPh Final Disetor');
doc.querySelector('#invList input[type=checkbox]').checked = true; click($('btnProcess'));
ok($('fmsg').textContent.includes('Pilih PPh 23/PPh Final Disetor: Dipotong atau Tidak Dipotong'), 'Validasi pilihan potong memakai label baru');
doc.querySelector('#invList input[type=checkbox]').checked = false;
change($('pph23_dipotong'), 'Y');
const umkmRow = qa('#invList tr').find(tr => tr.textContent.includes('262/STM/2026'));
ok(umkmRow.children[4].textContent === '1,833,333.33' && umkmRow.children[5].textContent === '0.00', 'Daftar: UMKM DPP dihitung, PPN 0');
ok(umkmRow.children[6].textContent === '10,000.00', 'Daftar: PPh Final 0,5% x 2.000.000 = 10.000');
ok(qa('#invList tr').find(tr => tr.textContent.includes('255/STM/2026')).children[6].textContent === '40,000.00', 'Daftar: PKP tetap PPh 23 2%');

const B = 'Bank Mandiri STM', A = 'Biaya Admin Bank', P = 'Piutang Dagang Usaha', D = 'Deposit Customer', E = 'Beban PPh Final', H = 'Hutang PPh Final';
// Ekspektasi sheet "Jurnal Penerimaan" kolom I (dipotong) dan K (setor sendiri)
const expected = [
    [{[B]: [609425, 0], [A]: [2500, 0], [P]: [0, 615000], [E]: [3075, 0]},
     {[B]: [612500, 0], [A]: [2500, 0], [P]: [0, 615000], [E]: [3075, 0], [H]: [0, 3075]}],
    [{[B]: [1987500, 0], [A]: [2500, 0], [P]: [0, 2000000], [E]: [10000, 0]},
     {[B]: [1997500, 0], [A]: [2500, 0], [P]: [0, 2000000], [E]: [10000, 0], [H]: [0, 10000]}],
    [{[B]: [2987500, 0], [A]: [2500, 0], [D]: [0, 1000000], [P]: [0, 2000000], [E]: [10000, 0]},
     {[B]: [2997500, 0], [A]: [2500, 0], [D]: [0, 1000000], [P]: [0, 2000000], [E]: [10000, 0], [H]: [0, 10000]}],
    [{[B]: [987500, 0], [A]: [2500, 0], [P]: [1000000, 2000000], [E]: [10000, 0]},
     {[B]: [997500, 0], [A]: [2500, 0], [P]: [1000000, 2000000], [E]: [10000, 0], [H]: [0, 10000]}],
    [{[B]: [1487500, 0], [A]: [2500, 0], [D]: [500000, 0], [P]: [0, 2000000], [E]: [10000, 0]},
     {[B]: [1497500, 0], [A]: [2500, 0], [D]: [500000, 0], [P]: [0, 2000000], [E]: [10000, 0], [H]: [0, 10000]}]
];
expected.forEach((pair, k) => ['Y', 'N'].forEach((choice, j) => {
    loadCase(k, choice);
    const name = `Kasus ${k + 1} (${choice})`;
    ok($('badge_kontrol').textContent === '0.00', name + ': kontrol 0');
    ok(!$('btnProses').disabled && !$('journalSection').classList.contains('hidden'), name + ': jurnal tampil dan Proses aktif');
    ok(same(journal(), pair[j]), name + ': jurnal sama dengan Excel ' + JSON.stringify(journal()));
    ok($('jDr').textContent === $('jCr').textContent, name + ': balance');
    ok(!/PPh 23 Disetor/.test($('journalBody').textContent), name + ': tanpa PPh 23');
    ok(qa('#journalBody tr').every(tr => / - UMKM Non-PKP$/.test(tr.children[4].textContent)), name + ': deskripsi UMKM');
    ok(!$('coaNote').classList.contains('hidden'), name + ': catatan COA PPh Final kosong tampil');
}));

// Catatan per baris
loadCase(1, 'N');
ok(/PPh Final 0,5% setor sendiri: 10,000/.test($('dlgBody').textContent), 'Catatan setor sendiri');
const finalRow = qa('#journalBody tr').find(tr => tr.children[3].textContent === E);
ok(finalRow.children[1].textContent === '', 'COA Beban PPh Final kosong');

// Campuran UMKM + PKP
loadCase(5, 'Y');
let j = journal();
ok($('badge_kontrol').textContent === '0.00', 'Campuran Y: kontrol 0');
ok(j['PPh 23 Disetor'][0] === 40000 && j[E][0] === 10000 && !j[H], 'Campuran Y: PKP PPh 23 40.000, UMKM PPh Final 10.000');
loadCase(5, 'N');
j = journal();
ok(!j['PPh 23 Disetor'] && j[E][0] === 10000 && j[H][1] === 10000, 'Campuran N: hanya UMKM yang setor sendiri');

// PPh Final melebihi batas
loadCase(1, 'Y');
$('pph_1').value = '10,000.01';
$('pph_1').dispatchEvent(new window.Event('input', {bubbles: true}));
ok(/PPh Final 0,5% melebihi sisa batas PPh Final 0,5% Penjualan/.test($('errText').textContent), 'Pesan batas PPh Final');
ok($('btnProses').disabled, 'Proses nonaktif saat error');
click(doc.querySelector('[data-reset="1"]'));
ok($('pph_1').value === '10,000.00', 'Kembalikan PPh default');

// Kontrol belum 0
loadCase(1, 'Y');
$('admin_1').value = '0.00'; $('admin_1').dispatchEvent(new window.Event('input', {bubbles: true}));
ok(/selisih kurang 2,500.00/.test($('errText').textContent) && $('journalSection').classList.contains('hidden'), 'Kontrol belum 0: jurnal disembunyikan');

// Proses: saldo berkurang
loadCase(3, 'Y');
click($('btnProses'));
ok($('dialog').classList.contains('hidden'), 'Popup tertutup setelah Proses');
ok(/posting ke accounting menunggu COA PPh Final/.test($('toast').textContent), 'Toast menyebut posting menunggu COA');
ok(qa('#invList tr').find(tr => tr.textContent.includes('262/STM/2026')).children[9].textContent === '1,000,000.00', 'Saldo piutang 262 tinggal 1.000.000');

ok(errors.length === 0, 'Tanpa error JS: ' + errors.join('; '));
console.log(`PASS: ${checks} smoke checks mockup Penerimaan Piutang`);
