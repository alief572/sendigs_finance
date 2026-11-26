// Jalankan: node application/modules/follow_up_piutang/tests/Kolom_client_test.js
// Geser kolom Follow Up Piutang dan Dashboard Plan Bayar: view asli + jQuery, DataTables, ColReorder lokal, dan
// piutang-kolom.js di jsdom, server ditiru. Butuh jsdom (set JSDOM_PATH bila tidak terpasang di repo). Gerakan mouse
// tidak ditiru (jsdom tanpa layout): pemindahan dipanggil lewat fnColReorder, jalur yang sama dengan saat judul dilepas.
const fs = require('fs'), path = require('path'), assert = require('assert');
const {JSDOM} = require(process.env.JSDOM_PATH || 'jsdom');
const root = path.join(__dirname, '../../../..');
const read = (p) => fs.readFileSync(path.join(root, p), 'utf8');

function page(file, respond) {
    const view = read('application/modules/follow_up_piutang/views/' + file)
        .replace("<?= $can_manage ? 'true' : 'false' ?>", 'true').replace("<?= (int) date('n') ?>", '11')
        .replace(/<\?php[\s\S]*?\?>/g, '').replace(/<\?=[\s\S]*?\?>/g, 'x');
    const html = view.replace(/<script[\s\S]*?<\/script>/g, '').replace(/<style[\s\S]*?<\/style>/g, '');
    const inline = view.match(/<script>([\s\S]*?)<\/script>/)[1];
    const w = new JSDOM('<!doctype html><body>' + html + '</body>', {runScripts: 'outside-only'}).window;
    ['assets/plugins/jQuery/jquery-2.2.3.min.js', 'assets/plugins/datatables/jquery.dataTables.min.js',
        'assets/plugins/datatables/extensions/ColReorder/js/dataTables.colReorder.min.js', 'assets/js/piutang-kolom.js'].forEach((p) => w.eval(read(p)));
    const $ = w.jQuery, calls = [];
    w.HTMLElement.prototype.scrollIntoView = () => {}; // tidak ada di jsdom (dipakai sorot baris)
    $.ajax = (o) => {
        calls.push(o);
        const data = $.extend({}, o.data);
        if (typeof o.data === 'function') { o.data(data); }
        const json = respond(o.url, data);
        if (o.success) { o.success(json); }
        return {abort() {}};
    };
    $.getJSON = (url) => {
        calls.push({url});
        const p = {done() { return p; }, fail() { return p; }};
        return p;
    };
    w.CompanyMulti = {val: () => '', set: () => {}, init: () => {}};
    $.fn.modal = function () { return this; };
    w.eval(inline);

    const texts = (sel) => $(sel).map(function () { return $(this).text().trim(); }).get();
    return {
        w, $, calls, inline,
        head: (t) => texts(t + ' > thead:not(.kol-grp) th'),
        groups: (t) => $(t + ' > thead.kol-grp tr > *').map(function () { return $(this).text() + ':' + (this.colSpan || 1); }).get(),
        cells: (t) => texts(t + ' tbody tr:first td'),
        foot: (t) => $(t + ' tfoot td').map(function () { return this.id || $(this).data('tot') || ''; }).get(),
        draggable: (t) => $(t + ' > thead:not(.kol-grp) th').map(function () {
            return !!(($._data(this, 'events') || {}).mousedown || []).some((h) => h.namespace === 'ColReorder');
        }).get(),
        move: (t, from, to) => $(t).dataTable().fnColReorder(from, to)
    };
}
// Tiap kolom di baris grup harus tepat menutupi kolom judul di bawahnya.
const span = (list) => list.reduce((n, s) => n + Number(s.split(':').pop()), 0);

// ==== Follow Up Piutang ====
const inv = {id: 'ID1', no_invoice: 'INV-1', tgl_invoice: '2026-11-05', customer: 'PT A', project: 'P', company_name: 'STM', nilai: 1000, saldo: 400, terbayar: 600,
    dpp: 900, ppn: 100, status: 'plan_bayar', status_label: 'Plan Bayar', plan_bayar: '2026-11-30', can_close: true, can_update: false, fu_pic_nama: 'Budi', no_faktur: 'FK-1'};
let fuReq = [];
const fu = page('index.php', (url, data) => {
    fuReq.push(data);
    return {draw: data.draw, recordsTotal: 300, recordsFiltered: 300, data: [inv], totals: {dpp: 900, ppn: 100, nilai: 1000, terbayar: 600, saldo: 400}, cards: {}, counts: {}};
});
const $ = fu.$, T = '#fu-table';

// Tab 1: No dan Invoice terkunci, sisanya (termasuk Aksi, seperti mockup gridFU) bisa diseret; tanpa baris grup.
assert.deepEqual(fu.head(T), ['No', 'Invoice', 'Customer', 'Nilai Invoice', 'Aksi']);
assert.deepEqual(fu.draggable(T), [false, false, true, true, true]);
assert.strictEqual($(T + ' > thead.kol-grp').length, 0);
assert.ok($(T + ' tbody tr:first td').eq(1).hasClass('fu-pin2'));
fu.move(T, 3, 2);
assert.deepEqual(fu.head(T).slice(2, 4), ['Nilai Invoice', 'Customer']);
assert.strictEqual(fu.cells(T)[2], 'Rp 1.000');
assert.ok($(T + ' tbody tr:first td').eq(2).hasClass('num'));

// Klik judul tidak mengurutkan / memuat ulang (urutan baris tetap dari server).
let n = fuReq.length;
$(T + ' > thead th').eq(2).click();
$(T + ' > thead th').eq(3).click();
assert.strictEqual(fuReq.length, n, 'klik judul setelah geser tidak memicu urut');

// Pindah halaman, cari, dan export tetap jalan; urutan kolom bertahan.
$(T).DataTable().page(1).draw(false);
assert.strictEqual(fuReq[fuReq.length - 1].start, 100);
assert.strictEqual(fu.cells(T)[2], 'Rp 1.000');
assert.strictEqual(fu.cells(T)[0], '101');
$(T).DataTable().search('budi').draw();
assert.strictEqual(fuReq[fuReq.length - 1].search.value, 'budi');
assert.strictEqual(fu.cells(T)[2], 'Rp 1.000');

// Aksi per baris (kolom Aksi ikut dipindah).
fu.move(T, 4, 2);
fu.calls.length = 0;
$(T + ' tbody [data-act=update]').click();
assert.ok(/form\/ID1$/.test((fu.calls[0] || {}).url), 'tombol Update / Lihat membuka form');

// Tab Semua: header berkelompok.
$('#fu-groups button[data-group=monitoring]').click();
const awal = ['No:1', 'Invoice:1'];
assert.deepEqual(fu.draggable(T).slice(0, 3), [false, false, true]);
const g0 = fu.groups(T);
assert.deepEqual(g0, [':1', ':1', 'Invoice & Pajak:3', ':1', 'Nilai:5', ':1', 'PIC Follow Up:2', 'FU 1:2', 'FU 2:2', 'FU 3:2', 'FU 4:2', ':1', ':1', 'Status:2', ':1']);
assert.strictEqual(span(g0), fu.head(T).length);
assert.strictEqual($(T + ' tfoot td').length, fu.head(T).length, awal.join());
assert.ok($(T + ' > thead.kol-grp tr > *').eq(1).hasClass('fu-pin2'), 'sel grup di atas kolom kunci ikut terkunci');

// Anak grup dipindah keluar: grup terbelah (sama dengan AG Grid), isi dan total ikut pindah.
const iSisa = fu.head(T).indexOf('Sisa Piutang');
fu.move(T, iSisa, 2);
assert.strictEqual(fu.head(T)[2], 'Sisa Piutang');
assert.deepEqual(fu.groups(T).slice(2, 5), ['Nilai:1', 'Invoice & Pajak:3', ':1']);
assert.ok(fu.groups(T).indexOf('Nilai:4') > 0);
assert.strictEqual(span(fu.groups(T)), fu.head(T).length);
assert.strictEqual(fu.cells(T)[2], 'Rp 400');
assert.strictEqual(fu.foot(T)[2], 'saldo');
assert.strictEqual(fu.foot(T)[1], 'fu-tot-label');
$(T).DataTable().page(1).draw(false);
assert.strictEqual(fu.cells(T)[2], 'Rp 400');
assert.strictEqual($(T + ' tfoot [data-tot=saldo]').text(), 'Rp 400');
assert.strictEqual($('#fu-tot-label').text(), 'Total 300 invoice');
// Dikembalikan ke samping saudaranya: grup bergabung lagi.
fu.move(T, 2, iSisa);
assert.deepEqual(fu.groups(T), g0);

// Aksi Buka di tab Semua.
fu.move(T, fu.head(T).length - 1, 2);
n = fuReq.length;
$(T + ' tbody [data-act=buka]').click();
assert.strictEqual(fuReq[fuReq.length - 1].tab, '4', 'tombol Buka pindah ke tab kerja');
assert.ok(fuReq.length > n);

// Tab 4: Close per baris setelah kolom dipindah; ganti tab = urutan kembali ke bawaan (tidak disimpan).
assert.deepEqual(fu.head(T), ['No', 'Invoice', 'Customer', 'Nilai Invoice', 'Rencana Bayar', 'Pembayaran', 'Aksi']);
assert.strictEqual($(T + ' > thead.kol-grp').length, 0);
fu.move(T, 6, 2);
$(T + ' tbody [data-act=close]').click();
assert.strictEqual($('#fu-close-title').text(), 'Close Invoice INV-1');

// ==== Dashboard Plan Bayar ====
const plan = {id: 'ID1', no_invoice: 'INV-1', tgl_invoice: '2026-11-05', customer: 'PT A', project: 'P', company_name: 'STM', nilai: 1000, nilai_plan: 700,
    plan_bayar: '2026-11-30', status: 'plan_bayar'};
const real = {no_invoice: 'INV-2', tgl_invoice: '2026-11-01', customer: 'PT B', project: 'Q', company_name: 'STM', plan_bayar: '2026-11-10', tgl_bayar: '2026-11-12',
    no_penerimaan: 'RC-1', ketepatan_days: 2, nominal: 500, saldo: 250, nilai: 750, posisi: 'Sebagian'};
const req = {plan: [], real: []};
const pb = page('plan_bayar.php', (url, data) => {
    const k = /plan_data/.test(url) ? 'plan' : 'real';
    req[k].push(data);
    return {draw: data.draw, recordsTotal: 60, recordsFiltered: 60, data: [k === 'plan' ? plan : real], agg: k === 'plan' ? {nilai: 1000, nilai_plan: 700} : {nominal: 500, tepat: 0, telat: 1}};
});
const q = pb.$, P = '#pb-plan-tbl', R = '#pb-real-tbl';

// Rincian plan: tanpa kolom terkunci (mockup gridPB), tanpa grup.
assert.deepEqual(pb.draggable(P), Array(8).fill(true));
assert.strictEqual(q(P + ' tfoot td').length, 8);
pb.move(P, 6, 0); // Nilai Plan ke paling kiri
assert.strictEqual(pb.cells(P)[0], '700,00');
assert.strictEqual(pb.foot(P)[0], 'pp-plan');
assert.strictEqual(q('#pp-label').index(), 2);
n = req.plan.length;
q(P + ' > thead th').eq(0).click();
assert.strictEqual(req.plan.length, n);
q(P).DataTable().search('pt a').draw();
assert.strictEqual(req.plan[req.plan.length - 1].search.value, 'pt a');
assert.strictEqual(pb.cells(P)[0], '700,00');
assert.strictEqual(q('#pp-plan').text(), '700,00');
assert.strictEqual(q('#pp-label').text(), 'Total 60 invoice');
assert.ok(/tab=4&open=ID1/.test(q(P + ' tbody tr:first').attr('data-inv')), 'klik baris tetap membawa tujuan');

// Realisasi: header berkelompok.
const r0 = [':1', ':1', ':1', 'Plan vs Bayar:3', ':1', 'Posisi Invoice Sekarang:2'];
assert.deepEqual(pb.groups(R), r0);
assert.deepEqual(pb.draggable(R), Array(9).fill(true));
pb.move(R, 4, 0); // Tanggal Bayar keluar dari grup
assert.deepEqual(pb.groups(R), ['Plan vs Bayar:1', ':1', ':1', ':1', 'Plan vs Bayar:2', ':1', 'Posisi Invoice Sekarang:2']);
assert.ok(/RC-1/.test(pb.cells(R)[0]));
pb.move(R, 6, 1); // Jumlah Dibayar
assert.strictEqual(pb.cells(R)[1], '500,00');
assert.strictEqual(pb.foot(R)[1], 'pr-nominal');
q(R).DataTable().page(1).draw(false);
assert.strictEqual(pb.cells(R)[1], '500,00');
assert.strictEqual(q('#pr-nominal').text(), '500,00');
assert.strictEqual(span(pb.groups(R)), 9);

// Export membawa kata cari kedua tabel.
q(R).DataTable().search('rc-1').draw();
assert.strictEqual(q(P).DataTable().search(), 'pt a');
assert.strictEqual(q(R).DataTable().search(), 'rc-1');

// ==== Ubah lebar (mockup resizable): seret tepi kanan judul ====
// jsdom tanpa layout: offsetWidth ditiru 120px.
function resize(pg, t, i, dx) {
    const $$ = pg.$, handle = $$(t + ' > thead:not(.kol-grp) th').eq(i).find('.kol-rz');
    handle.trigger($$.Event('mousedown', {which: 1, pageX: 500}));
    $$(pg.w.document).trigger($$.Event('mousemove', {pageX: 500 + dx})).trigger('mouseup');
}
const cols = (pg, t) => pg.$(t + ' > colgroup.kol-w col').map(function () { return parseInt(this.style.width, 10); }).get();
[fu, pb].forEach((pg) => Object.defineProperty(pg.w.HTMLElement.prototype, 'offsetWidth', {get: () => 120}));

$('#fu-groups button[data-group=monitoring]').click();
const jml = fu.head(T).length;
assert.strictEqual($(T + ' > thead:not(.kol-grp) th').eq(0).find('.kol-rz').length, 0, 'No tidak bisa diubah lebar');
assert.strictEqual($(T + ' > thead:not(.kol-grp) .kol-rz').length, jml - 1);
n = fuReq.length;
resize(fu, T, 2, 80);
const w = cols(fu, T);
assert.strictEqual(w.length, jml);
assert.deepEqual(w.slice(0, 3), [52, 120, 200]);
// kolom kunci kedua mengikuti lebar No yang terukur (offsetWidth tiruan 120px)
assert.strictEqual($(T)[0].style.getPropertyValue('--kol-pin'), '120px');
assert.strictEqual(parseInt($(T)[0].style.width, 10), w.reduce((a, b) => a + b, 0));
resize(fu, T, 2, -500);
assert.strictEqual(cols(fu, T)[2], 70, 'lebar minimal 70px');
assert.strictEqual(fuReq.length, n, 'ubah lebar tidak memuat ulang');
fu.move(T, 2, 4); // lebar ikut pindah bersama kolomnya
assert.strictEqual(cols(fu, T)[4], 70);
assert.strictEqual(cols(fu, T)[2], 120);
assert.strictEqual(span(fu.groups(T)), jml);
$(T).DataTable().page(1).draw(false);
assert.strictEqual(cols(fu, T)[4], 70);
$('#fu-groups button[data-group=proses]').click(); // ganti tab: lebar kembali bawaan
assert.strictEqual($(T + ' > colgroup.kol-w').length, 0);
assert.ok(!$(T).hasClass('kol-fixed'));
assert.strictEqual($(T)[0].style.width.indexOf('px'), -1);

assert.strictEqual(pb.head(P)[1], 'No'); // No sudah tergeser, tetap tanpa pegangan ubah lebar
assert.strictEqual(q(P + ' > thead th').eq(1).find('.kol-rz').length, 0);
resize(pb, P, 2, 30);
assert.strictEqual(cols(pb, P).length, 8);
assert.strictEqual(cols(pb, P)[2], 150);
resize(pb, R, 8, 40);
assert.strictEqual(cols(pb, R)[8], 160);

// Urutan dan lebar tidak disimpan (mockup tidak menyimpan state kolom).
[fu.inline, pb.inline, read('assets/js/piutang-kolom.js')].forEach((s) => assert.ok(!/stateSave|localStorage|sessionStorage/.test(s)));

console.log('Kolom_client_test (follow_up_piutang): OK');
