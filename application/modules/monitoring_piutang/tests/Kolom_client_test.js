// Jalankan: node application/modules/monitoring_piutang/tests/Kolom_client_test.js
// Geser kolom tabel Monitoring Piutang: view asli + jQuery, DataTables, dan ColReorder lokal di jsdom, server ditiru.
// Butuh jsdom (set JSDOM_PATH bila tidak terpasang di repo). Gerakan mouse tidak ditiru (jsdom tanpa layout):
// pemindahan dipanggil lewat fnColReorder, jalur yang sama dengan saat judul kolom dilepas.
const fs = require('fs'), path = require('path'), assert = require('assert');
const {JSDOM} = require(process.env.JSDOM_PATH || 'jsdom');
const root = path.join(__dirname, '../../../..');
const read = (p) => fs.readFileSync(path.join(root, p), 'utf8');

const view = read('application/modules/monitoring_piutang/views/index.php')
    .replace(/<\?php[\s\S]*?\?>/g, '').replace(/<\?=[\s\S]*?\?>/g, 'x');
const html = view.replace(/<script[\s\S]*?<\/script>/g, '').replace(/<style[\s\S]*?<\/style>/g, '');
const inline = view.match(/<script>([\s\S]*?)<\/script>/)[1];

const dom = new JSDOM('<!doctype html><body>' + html + '</body>', {runScripts: 'outside-only'});
const w = dom.window;
['assets/plugins/jQuery/jquery-2.2.3.min.js', 'assets/plugins/datatables/jquery.dataTables.min.js',
    'assets/plugins/datatables/extensions/ColReorder/js/dataTables.colReorder.min.js', 'assets/js/piutang-kolom.js'].forEach((p) => w.eval(read(p)));
const $ = w.jQuery;

const row = {id: 'ID1', no_invoice: 'INV-1', tgl_invoice: '2026-10-05', tgl_terima: '2026-10-06', customer: 'PT A', project: 'P', company_name: 'STM',
    nilai: 1000, terbayar: 0, saldo: 1000, umur: 50, kategori: 'lancar', bermasalah: true, ket_bermasalah: 'x', can_surat: false};
const rekap = {customer: 'PT A', company: 'STM', count: 1, lancar: 100000, potensi: 0, macet: 0, total: 100000, oldest: 50};
const calls = [];
$.getJSON = (url, data) => {
    calls.push({url, data});
    let res = null;
    if (/list_data/.test(url)) { res = {draw: data.draw, recordsTotal: 60, recordsFiltered: 60, data: [row], totals: {nilai: 1000, terbayar: 0, saldo: 1000}}; }
    if (/rekap_data/.test(url)) { res = {draw: data.draw, recordsTotal: 1, recordsFiltered: 1, data: [rekap], totals: {count: 1, lancar: 100000, potensi: 0, macet: 0, total: 100000}}; }
    const p = {done(fn) { if (res) { fn(res); } return p; }, fail() { return p; }};
    return p;
};
w.CompanyMulti = {val: () => '', set: () => {}, init: () => {}};
$.fn.modal = function () { return this; };
w.eval(inline);

const texts = (sel) => $(sel).map(function () { return $(this).text().trim(); }).get();
const head = (t) => texts(t + ' thead th').map((s) => s.replace(/\d.*$/, '').trim());
const cells = (t) => texts(t + ' tbody tr:first td');
const foot = (t) => $(t + ' tfoot td').map(function () { return this.id; }).get();
const draggable = (t) => $(t + ' thead th').map(function () {
    return !!(($._data(this, 'events') || {}).mousedown || []).some((h) => h.namespace === 'ColReorder');
}).get();
const lastCall = (re) => calls.filter((c) => re.test(c.url)).pop().data;
const move = (t, from, to) => $(t).dataTable().fnColReorder(from, to);

// ---- List per invoice: No dan No Invoice terkunci di kiri, sisanya bisa diseret ----
assert.deepEqual(draggable('#mp-list'), [false, false, true, true, true, true, true, true, true, true, true]);
assert.deepEqual(head('#mp-list').slice(0, 4), ['No', 'No Invoice', 'Customer', 'Company']);
assert.strictEqual($('#mp-list tfoot td').length, 11);
assert.strictEqual($('#tot-label').index(), 1);

move('#mp-list', 6, 2); // Sisa Piutang ke depan Customer
assert.strictEqual(head('#mp-list')[2], 'Sisa Piutang');
assert.strictEqual(head('#mp-list')[3], 'Customer');
assert.strictEqual(cells('#mp-list')[2], '1.000,00');
assert.ok($('#mp-list tbody tr:first td').eq(2).hasClass('num'), 'rata kanan ikut pindah');
assert.strictEqual(foot('#mp-list')[2], 'tot-saldo');
assert.strictEqual(foot('#mp-list')[1], 'tot-label');

// Urut memakai kolom yang benar setelah dipindah
$('#mp-list thead th').eq(2).click();
assert.strictEqual(lastCall(/list_data/).sort, 'saldo');
$('#mp-list thead th').eq(3).click();
assert.strictEqual(lastCall(/list_data/).sort, 'customer');
assert.strictEqual(lastCall(/list_data/).dir, 'asc');

// Data baru dari server (urut / filter / halaman) tetap mengikuti urutan baru
$('#mp-list').DataTable().page(1).draw(false);
assert.strictEqual(lastCall(/list_data/).start, 25);
assert.strictEqual(head('#mp-list')[2], 'Sisa Piutang');
assert.strictEqual(cells('#mp-list')[2], '1.000,00');
assert.strictEqual(cells('#mp-list')[0], '26');
assert.ok(/^INV-1/.test(cells('#mp-list')[1]));
assert.ok(/^PT A/.test(cells('#mp-list')[3]));
assert.ok($('#mp-list tbody tr:first').hasClass('bad'), 'penanda bermasalah tetap');
assert.strictEqual($('#tot-saldo').text(), '1.000,00');

// Aksi per baris tetap jalan saat kolom Action dipindah
move('#mp-list', 10, 2);
assert.strictEqual(cells('#mp-list')[2], 'Detail');
calls.length = 0;
$('#mp-list tbody button[data-id]').click();
assert.ok(/detail\/ID1$/.test(calls[0].url));
calls.length = 0;
$('#mp-list tbody tr:first td:first').click();
assert.ok(/detail\/ID1$/.test(calls[0].url));

// ---- Rekap per Customer: Customer terkunci ----
$('#mp-tabs button[data-pane=rekap]').click();
assert.deepEqual(draggable('#mp-rekap'), [false, true, true, true, true, true, true, true]);
assert.strictEqual($('#mp-rekap tfoot td').length, 8);
move('#mp-rekap', 6, 1); // Total Sisa Piutang
assert.strictEqual(head('#mp-rekap')[1], 'Total Sisa Piutang');
assert.strictEqual(cells('#mp-rekap')[1], '1.000,00');
assert.strictEqual(foot('#mp-rekap')[1], 'rk-total');
$('#mp-rekap thead th').eq(1).click();
assert.strictEqual(lastCall(/rekap_data/).sort, 'total');
assert.strictEqual(cells('#mp-rekap')[1], '1.000,00');
assert.strictEqual($('#rk-total').text(), '1.000,00');

// ---- Ubah lebar: seret tepi kanan judul (jsdom tanpa layout: offsetWidth ditiru 120px) ----
Object.defineProperty(w.HTMLElement.prototype, 'offsetWidth', {get: () => 120});
const cols = (t) => $(t + ' > colgroup.kol-w col').map(function () { return parseInt(this.style.width, 10); }).get();
const mouse = (el, type, x) => el.dispatchEvent(new w.MouseEvent(type, {bubbles: true, cancelable: true, button: 0, clientX: x}));
function resize(t, i, dx) {
    const handle = $(t + ' thead th').eq(i).find('.kol-rz')[0];
    $(handle).trigger($.Event('mousedown', {which: 1, pageX: 500}));
    $(w.document).trigger($.Event('mousemove', {pageX: 500 + dx})).trigger('mouseup');
}
['#mp-list', '#mp-rekap'].forEach((t) => {
    const iNo = head(t).indexOf('No');
    $(t + ' thead th').each(function (i) { assert.strictEqual($(this).find('.kol-rz').length, i === iNo ? 0 : 1); });
});

calls.length = 0;
const iSisa = head('#mp-list').indexOf('Sisa Piutang');
resize('#mp-list', iSisa, 60);
// click bawaan browser setelah mouseup, jatuh di judul: tidak boleh mengurutkan
mouse($('#mp-list thead th')[iSisa], 'click', 560);
assert.strictEqual(calls.length, 0, 'ubah lebar tidak ikut mengurutkan');
assert.strictEqual(cols('#mp-list')[iSisa], 180);
assert.strictEqual(cols('#mp-list')[head('#mp-list').indexOf('No')], 52, 'lebar No tetap, kolom kunci kedua tidak bergeser');
move('#mp-list', iSisa, 3); // lebar ikut pindah
assert.strictEqual(cols('#mp-list')[3], 180);
resize('#mp-rekap', 1, -500);
assert.strictEqual(cols('#mp-rekap')[1], 70, 'lebar minimal 70px');

// Setelah itu klik judul biasa tetap mengurutkan
setTimeout(() => {
    calls.length = 0;
    mouse($('#mp-list thead th')[3], 'click', 10);
    assert.strictEqual(lastCall(/list_data/).sort, 'saldo');
    assert.strictEqual(cols('#mp-list')[3], 180);

    // Urutan dan lebar tidak disimpan (mockup tidak menyimpan state kolom)
    assert.ok(!/stateSave|localStorage/.test(inline + read('assets/js/piutang-kolom.js')));

    console.log('Kolom_client_test: OK');
}, 10);
