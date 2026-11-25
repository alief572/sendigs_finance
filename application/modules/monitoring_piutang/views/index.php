<?php
$base = site_url('monitoring_piutang');
$bulan = array('Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<style>
    /* Tampilan mengikuti specs/docs/monitoring-piutang/mockup-monitoring-piutang.html (menu Monitoring Piutang).
       Warna pill memakai varian lebih gelap agar teks putih lolos kontras 4.5:1. */
    .mp-page { --line: #dfe3e8; --row: #f7f9fb; --muted: #5b6670; --blue: #1d5f93; --green: #067647; --orange: #b54708; --red: #b42318; --cyan: #0e7490; --text: #333a40; }
    .mp-page :focus-visible { outline: 2px solid #1d4ed8; outline-offset: 1px; }
    .mp-panel { background: #fff; border-top: 3px solid #3f8bba; border-radius: 3px; padding: 14px; margin-bottom: 16px; box-shadow: 0 1px 1px rgba(0, 0, 0, .08); }
    .mp-bar { display: flex; flex-wrap: wrap; gap: 8px; align-items: flex-end; }
    .mp-bar .sp { flex: 1; }
    .mp-page .fl { display: flex; flex-direction: column; gap: 3px; font-size: 12px; color: var(--muted); font-weight: 400; margin: 0; }
    .mp-page .fl select, .mp-page .fl input { min-width: 150px; width: auto; background: #fff; border: 1px solid #c8d0d8; border-radius: 3px; padding: 5px 7px; font-size: 13px; color: var(--text); }
    .mp-page .fl.searchbox input { width: 280px; }
    .mp-btn { border: 0; border-radius: 3px; padding: 6px 12px; cursor: pointer; color: #fff; background: var(--green); font-weight: 600; font-size: 13px; }
    .mp-btn.sm { padding: 3px 8px; font-size: 12px; } .mp-btn.c { background: var(--cyan); } .mp-btn.o { background: var(--orange); }
    .mp-btn.g { background: transparent; color: var(--text); border: 1px solid #c8d0d8; }
    .mp-excl { background: var(--row); border: 1px solid var(--line); border-left: 4px solid var(--cyan); border-radius: 3px; padding: 8px 12px; margin: 12px 0; font-size: 13px; }
    .mp-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; margin-bottom: 16px; }
    .mp-card { background: #fff; border: 1px solid var(--line); border-top: 4px solid var(--line); border-radius: 3px; padding: 12px 14px; text-align: left; cursor: pointer; display: flex; flex-direction: column; gap: 4px; min-width: 0; width: 100%; font: inherit; }
    .mp-card:hover { box-shadow: 0 2px 8px rgba(0, 0, 0, .08); }
    .mp-card.on { box-shadow: 0 0 0 2px var(--cyan); }
    .mp-card.tot { border-top-color: var(--blue); } .mp-card.L { border-top-color: var(--green); } .mp-card.P { border-top-color: var(--orange); } .mp-card.M { border-top-color: var(--red); }
    .mp-card.nb { border-top-color: #95a5a6; cursor: default; background: #fafbfc; } .mp-card.nb .cv { color: var(--muted); }
    .mp-card .ct { font-size: 12px; font-weight: 700; letter-spacing: .4px; text-transform: uppercase; color: var(--muted); display: flex; justify-content: space-between; gap: 8px; align-items: center; }
    .mp-card .cv { font-size: 21px; font-weight: 700; font-variant-numeric: tabular-nums; line-height: 1.2; word-break: break-word; }
    .mp-card.L .cv { color: var(--green); } .mp-card.P .cv { color: var(--orange); } .mp-card.M .cv { color: var(--red); }
    .mp-card .age { align-self: flex-start; font-size: 12px; font-weight: 700; padding: 2px 8px; border-radius: 3px; background: var(--row); border: 1px solid var(--line); }
    .mp-card .cm { font-size: 12.5px; color: var(--muted); } .mp-card .cm b { color: var(--text); }
    .mp-card .cardxl { display: flex; justify-content: flex-end; margin-top: 8px; }
    .mp-prog { height: 6px; background: var(--line); border-radius: 3px; margin: 4px 0; overflow: hidden; } .mp-prog i { display: block; height: 100%; }
    .mp-card.L .mp-prog i { background: var(--green); } .mp-card.P .mp-prog i { background: var(--orange); } .mp-card.M .mp-prog i { background: var(--red); }
    .mp-stack { display: flex; height: 10px; border-radius: 5px; overflow: hidden; background: var(--line); margin: 6px 0 2px; } .mp-stack i { display: block; height: 100%; }
    .mp-stack .L { background: var(--green); } .mp-stack .P { background: var(--orange); } .mp-stack .M { background: var(--red); }
    .mp-tabs { display: flex; gap: 2px; border-bottom: 1px solid var(--line); margin-bottom: 12px; }
    .mp-tabs button { border: 1px solid transparent; border-bottom: 0; background: none; padding: 7px 14px; cursor: pointer; color: var(--muted); border-radius: 3px 3px 0 0; font-size: 14px; }
    .mp-tabs button.on { border-color: var(--line); background: #fff; color: var(--text); font-weight: 600; margin-bottom: -1px; }
    .mp-chips { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin-bottom: 12px; }
    .mp-chip { display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px; padding: 3px 10px; border-radius: 12px; background: var(--row); border: 1px solid var(--line); }
    .mp-chip button { border: 0; background: none; cursor: pointer; color: var(--muted); font-weight: 700; padding: 0; }
    .asof { font-size: 12.5px; color: var(--muted); }
    table.mp-tbl { border-collapse: collapse; width: 100%; min-width: 860px; }
    .mp-tbl th, .mp-tbl td { padding: 8px 9px; border-bottom: 1px solid var(--line); text-align: left; vertical-align: top; }
    .mp-tbl th { font-weight: 700; font-size: 13px; white-space: nowrap; border-top: 0; }
    .mp-tbl tbody tr:nth-child(odd) { background: var(--row); }
    .mp-tbl td.num, .mp-tbl th.num { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
    .mp-tbl .no { white-space: nowrap; font-weight: 600; }
    .mp-tbl .sub { display: block; font-size: 12px; color: var(--muted); font-weight: 400; }
    .mp-tbl tfoot td { font-weight: 700; background: var(--row); border-top: 2px solid var(--line); }
    .mp-tbl tr.bad > td { background-color: rgba(180, 35, 24, .08); } .mp-tbl tr.bad > td:first-child { box-shadow: inset 3px 0 0 var(--red); }
    .mp-tbl tr.click { cursor: pointer; } .mp-tbl tr.click:hover > td { background-color: rgba(63, 139, 186, .08); }
    .mp-tbl a.inv { color: var(--blue); font-weight: 700; }
    .mp-pill { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 11.5px; font-weight: 700; color: #fff; white-space: nowrap; }
    .mp-pill.L { background: var(--green); } .mp-pill.P { background: var(--orange); } .mp-pill.M { background: var(--red); } .mp-pill.A { background: var(--green); } .mp-pill.R { background: var(--red); }
    .mp-umur { font-weight: 700; font-variant-numeric: tabular-nums; } .mp-umur.L { color: var(--green); } .mp-umur.P { color: var(--orange); } .mp-umur.M { color: var(--red); }
    .late { color: var(--red); } .z { opacity: .35; }
    .mp-pill.B { background: #1d5f93; }
    .mp-asof-top { margin: 0 0 10px; font-size: 13px; color: var(--muted); }
    .mp-foot { display: flex; flex-wrap: wrap; gap: 8px 16px; align-items: center; justify-content: space-between; margin-top: 10px; font-size: 13px; }
    .mp-empty { padding: 26px; text-align: center; color: var(--muted); }
    .mp-err { background: rgba(180, 35, 24, .08); border: 1px solid rgba(180, 35, 24, .45); border-radius: 3px; padding: 8px 12px; margin-bottom: 12px; font-size: 13px; }
    /* skeleton (MP-NFR-11) */
    .mp-sk { display: block; border-radius: 4px; height: 12px; margin: 6px 0; background: linear-gradient(90deg, #eceff2 25%, #f6f8fa 37%, #eceff2 63%); background-size: 400% 100%; animation: mpmove 1.2s ease infinite; }
    .mp-sk.lg { height: 24px; width: 70%; } .mp-sk.row { height: 30px; margin: 8px 0; }
    @keyframes mpmove { 0% { background-position: 100% 50%; } 100% { background-position: 0 50%; } }
    @media (prefers-reduced-motion: reduce) { .mp-sk { animation: none; } }
    /* modal detail */
    .mpd { font-size: 13px; } .mpd .k { display: block; color: #777; font-size: 12px; font-weight: normal; }
    .mpd-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 10px 24px; margin-bottom: 12px; }
    .mpd-kpi { display: flex; flex-wrap: wrap; gap: 28px; margin: 4px 0 14px; padding: 10px 14px; background: #f7f7f7; border: 1px solid #eee; border-radius: 3px; }
    .mpd-kpi b { display: block; font-size: 17px; font-variant-numeric: tabular-nums; }
    .mpd-h { font-size: 14px; font-weight: bold; margin: 14px 0 8px; padding-top: 10px; border-top: 1px solid #eee; }
    .mpd-tbl { width: 100%; border-collapse: collapse; } .mpd-tbl th, .mpd-tbl td { padding: 5px 8px; border-bottom: 1px solid #eee; text-align: left; vertical-align: top; }
    .mpd-tbl th { white-space: nowrap; font-weight: bold; } .mpd-r { text-align: right !important; white-space: nowrap; font-variant-numeric: tabular-nums; }
    .mpd-red { color: #b42318; } .mpd-muted { color: #5b6670; } .mpd-umur { font-variant-numeric: tabular-nums; }
    .mpd-lancar { color: #067647; } .mpd-potensi { color: #b54708; } .mpd-macet { color: #b42318; }
</style>

<div class="mp-page">
<div id="mp-tour"></div>
<p class="mp-asof-top">Monitoring Piutang <i>posisi per <?= date('j') . ' ' . $bulan[(int) date('n') - 1] . ' ' . date('Y') ?></i></p>
<div class="mp-panel">
    <div class="mp-bar">
        <label class="fl">Company<select id="f-company" multiple></select></label>
        <label class="fl">Tahun<select id="f-tahun"><option value="">Semua</option><?php for ($y = (int) date('Y'); $y >= (int) date('Y') - 6; $y--) echo '<option>' . $y . '</option>'; ?></select></label>
        <label class="fl">Bulan<select id="f-bulan"><option value="">Semua</option><?php foreach ($bulan as $i => $b) echo '<option value="' . ($i + 1) . '">' . $b . '</option>'; ?></select></label>
        <label class="fl">Tgl invoice dari<input type="date" id="f-dari"></label>
        <label class="fl">sampai<input type="date" id="f-sampai"></label>
        <label class="fl">Customer<input type="text" id="f-customer" list="dl-customer" autocomplete="off" placeholder="Pilih dari daftar" aria-label="Filter customer"><datalist id="dl-customer"></datalist></label>
        <div class="sp"></div>
        <label class="fl searchbox">Cari<input type="search" id="f-cari" placeholder="Cari: invoice, SPK, customer, dll." title="Mencari di no invoice, tanggal, customer, project, no SPK, company, via kirim, no resi, nominal, umur, kategori, status, dan FU. Beberapa kata = semua kata harus cocok."></label>
        <button type="button" class="mp-btn g" id="btn-clear">Reset filter</button>
        <button type="button" class="mp-btn" id="btn-export"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Download Excel <span id="exp-label"></span></button>
    </div>
</div>

<div id="mp-err" class="mp-err" role="alert" style="display:none"></div>
<div id="mp-grand" class="mp-excl" role="status"></div>
<div class="mp-cards" id="mp-cards">
    <div class="mp-card"><span class="mp-sk"></span><span class="mp-sk lg"></span><span class="mp-sk"></span></div>
    <div class="mp-card"><span class="mp-sk"></span><span class="mp-sk lg"></span><span class="mp-sk"></span></div>
    <div class="mp-card"><span class="mp-sk"></span><span class="mp-sk lg"></span><span class="mp-sk"></span></div>
    <div class="mp-card"><span class="mp-sk"></span><span class="mp-sk lg"></span><span class="mp-sk"></span></div>
    <div class="mp-card"><span class="mp-sk"></span><span class="mp-sk lg"></span><span class="mp-sk"></span></div>
</div>

<div class="mp-panel">
    <div class="mp-tabs" id="mp-tabs" role="tablist">
        <button type="button" role="tab" class="on" aria-selected="true" data-pane="list">List Piutang per Invoice</button>
        <button type="button" role="tab" aria-selected="false" data-pane="rekap">Rekap per Customer</button>
    </div>
    <div class="mp-chips" id="mp-chips"></div>
    <div id="pane-list" class="table-responsive">
        <table id="mp-list" class="mp-tbl">
            <thead><tr><th>No</th><th>No Invoice</th><th>Customer</th><th>Company</th><th class="num">Nilai Invoice</th><th class="num">Terbayar</th><th class="num">Sisa Piutang</th><th class="num">Umur</th><th>Kategori</th><th>Follow Up</th><th>Action</th></tr></thead>
            <tbody></tbody>
            <tfoot><tr><td colspan="4" id="tot-label">Total</td><td class="num" id="tot-nilai"></td><td class="num" id="tot-terbayar"></td><td class="num" id="tot-saldo"></td><td colspan="4"></td></tr></tfoot>
        </table>
    </div>
    <div id="pane-rekap" class="table-responsive" style="display:none">
        <table id="mp-rekap" class="mp-tbl">
            <thead><tr><th>Customer</th><th>Company</th><th class="num">Invoice</th><th class="num">Lancar<span class="sub">0–60 hari</span></th><th class="num">Potensi Macet<span class="sub">61–90 hari</span></th><th class="num">Macet<span class="sub">&gt; 90 hari</span></th><th class="num">Total Sisa Piutang</th><th class="num">Umur tertua</th></tr></thead>
            <tbody></tbody>
            <tfoot><tr><td colspan="2" id="rk-label">Total</td><td class="num" id="rk-count"></td><td class="num" id="rk-lancar"></td><td class="num" id="rk-potensi"></td><td class="num" id="rk-macet"></td><td class="num" id="rk-total"></td><td></td></tr></tfoot>
        </table>
    </div>
</div>
</div>

<div class="modal fade" id="mp-modal" tabindex="-1" role="dialog" aria-labelledby="mp-modal-title"><div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Tutup">&times;</button><h4 class="modal-title" id="mp-modal-title"></h4></div>
    <div class="modal-body" id="mp-modal-body"></div>
    <div class="modal-footer"><a class="btn btn-default pull-left" id="mp-open-fu" href="#">Buka di Follow Up Piutang</a><a class="btn btn-warning pull-left" id="mp-print-surat" target="_blank" href="#" style="display:none;margin-left:5px">Print Surat Konfirmasi Tagihan</a><button class="btn btn-default" data-dismiss="modal">Tutup</button></div>
</div></div></div>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/js/company-multi.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<link rel="stylesheet" href="<?= base_url('assets/plugins/driver/driver.css') ?>">
<script src="<?= base_url('assets/plugins/driver/driver.js.iife.js') ?>"></script>
<script src="<?= base_url('assets/js/piutang-tutorial.js') ?>"></script>
<script>
(function () {
    var BASE = '<?= $base ?>', FU = '<?= site_url('follow_up_piutang') ?>', FILES = '<?= base_url() ?>', TODAY = '<?= date('Y-m-d') ?>', kat = 'all', tbl, rkTbl;
    var NAMA = {all: 'Total Piutang', lancar: 'Lancar', potensi: 'Potensi Macet', macet: 'Macet'};
    var KET = {lancar: 'Umur 0 - 60 hari', potensi: 'Umur 61 - 90 hari', macet: 'Umur > 90 hari', all: 'Lancar + Potensi Macet + Macet (sudah diterima)'};
    var CODE = {lancar: 'L', potensi: 'P', macet: 'M'};
    function esc(s) { return $('<div>').text(s == null ? '' : String(s)).html(); }
    function rp(c) { return Number(c / 100).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}); }
    function nf(v) { return rp(Number(v || 0) * 100); }
    function dmy(d) { return d ? String(d).substr(8, 2) + '-' + String(d).substr(5, 2) + '-' + String(d).substr(0, 4) : ''; }
    function params() {
        return {company: CompanyMulti.val('#f-company'), customer: $('#f-customer').val(), cari: $('#f-cari').val(), kategori: kat,
            tahun: $('#f-tahun').val(), bulan: $('#f-bulan').val(), dari: $('#f-dari').val(), sampai: $('#f-sampai').val()};
    }

    // ---- Card (mockup: Total + Lancar / Potensi / Macet + Belum Diterima) ----
    var SUM = null;
    function xl(k, nm) { return '<span class="cardxl"><button type="button" class="mp-btn sm" data-dl="' + k + '" title="Download Excel ' + esc(nm) + '">Download Excel</button></span>'; }
    function cardsHtml(s) {
        // Total Piutang = Lancar + Potensi Macet + Macet (mockup renderCards); belum diterima ada di card dan banner sendiri.
        var tot = s.total.cents;
        var stack = ['lancar', 'potensi', 'macet'].map(function (k) { return '<i class="' + CODE[k] + '" style="width:' + s[k].pct + '%" title="' + NAMA[k] + ' ' + s[k].pct + '%"></i>'; }).join('');
        var h = '<div role="button" tabindex="0" class="mp-card tot' + (kat === 'all' ? ' on' : '') + '" data-kat="all" aria-pressed="' + (kat === 'all') + '">' +
            '<span class="ct">Total Piutang</span><span class="cv">' + rp(tot) + '</span>' +
            '<span class="cm"><b>' + s.total.count + '</b> invoice · <b>' + (s.total.customers || 0) + '</b> customer</span><span class="age">' + KET.all + '</span>' +
            '<span class="mp-stack">' + stack + '</span><span class="cm">Umur tertua: ' + (s.total.oldest == null || !s.total.count ? '–' : '<b>' + s.total.oldest + ' hari</b>') + '</span>' + xl('all', 'Total Piutang') + '</div>';
        ['lancar', 'potensi', 'macet'].forEach(function (k) {
            var x = s[k];
            h += '<div role="button" tabindex="0" class="mp-card ' + CODE[k] + (kat === k ? ' on' : '') + '" data-kat="' + k + '" aria-pressed="' + (kat === k) + '">' +
                '<span class="ct">' + NAMA[k] + '<span class="mp-pill ' + CODE[k] + '">' + x.pct + '%</span></span><span class="cv">' + rp(x.cents) + '</span>' +
                '<span class="age">' + KET[k] + '</span>' +
                '<span class="cm"><b>' + x.count + '</b> invoice' + (x.count ? ' · paling lama <b>' + x.oldest + ' hari</b>' : '') + '</span>' +
                '<span class="mp-prog"><i style="width:' + x.pct + '%"></i></span>' + xl(k, NAMA[k]) + '</div>';
        });
        h += '<div class="mp-card nb" id="card-belum"><span class="ct">Belum Diterima Customer</span><span class="cv">' + rp(s.belum.cents) + '</span>' +
            '<span class="age">belum masuk umur piutang</span><span class="cm"><b>' + s.belum.count + '</b> invoice belum ada tanggal terima</span>' +
            (s.belum.count ? '<span class="cardxl"><a class="mp-btn sm c" href="' + FU + '?tab=2">Isi di Follow Up Piutang →</a></span>' : '') + '</div>';
        return h;
    }
    function skelCards() { return '<div class="mp-card"><span class="mp-sk"></span><span class="mp-sk lg"></span><span class="mp-sk"></span></div>'.repeat(5); }

    // ---- Chips filter aktif ----
    function chips() {
        var c = [], p = params();
        if (p.company) { c.push(['company', 'Company', p.company]); }
        if (p.customer) { c.push(['customer', 'Customer', p.customer]); }
        if (kat !== 'all') { c.push(['kat', 'Kategori', NAMA[kat] + ' (' + KET[kat].toLowerCase() + ')']); }
        if (p.cari) { c.push(['cari', 'Cari', p.cari]); }
        $('#mp-chips').html(c.map(function (x) {
            return '<span class="mp-chip">' + x[1] + ': <b>' + esc(x[2]) + '</b> <button type="button" data-clr="' + x[0] + '" aria-label="Hapus filter ' + x[1] + '">×</button></span>';
        }).join('') || '<span class="asof">Menampilkan semua piutang. Klik card untuk menyaring per kategori umur.</span>');
    }

    // Kolom DataTable -> kunci urut di server (null = tidak bisa diurutkan)
    var SORT = [null, 'no_invoice', 'customer', null, 'nilai', 'terbayar', 'saldo', 'umur', null, null, null];

    function rowCells(x, i, start) {
        // Kolom Follow Up (mockup fuCell): pill status FU terakhir + "status FU n", lalu FU berikutnya "FU n (tambahan) · tgl" atau plan bayar.
        var bm = x.bermasalah ? '<span class="mp-pill R">INVOICE BERMASALAH</span><span class="sub late">' + esc(x.ket_bermasalah) + '</span>' : '';
        var last = x.fu_last_status && !x.bermasalah ? '<span class="sub"><span class="mp-pill B">' + esc(x.fu_last_status) + '</span> status FU ' + x.fu_last_ke + '</span>' : '';
        var nxt = x.fu_next_ke && x.fu_next_plan ? '<b>FU ' + x.fu_next_ke + '</b>' + (x.fu_next_ke > 4 ? ' (tambahan)' : '') + ' · ' + dmy(x.fu_next_plan) : '';
        var fu = x.plan_bayar ? '<span class="mp-pill A">Plan bayar ' + dmy(x.plan_bayar) + '</span>'
            : (nxt ? '<span class="' + (x.fu_late ? 'late' : '') + '">' + nxt + '</span>' + (x.fu_late ? '<span class="sub late">lewat jadwal</span>' : '') : (x.bermasalah ? '' : '<span class="asof">–</span>'));
        return [start + i + 1,
            '<a href="#" class="inv" data-id="' + esc(x.id) + '">' + esc(x.no_invoice) + '</a><span class="sub">Inv ' + dmy(x.tgl_invoice) + '</span><span class="sub">Diterima ' + dmy(x.tgl_terima) + '</span>' +
                (x.bermasalah ? '<span class="mp-pill R">Invoice bermasalah</span><span class="sub">FU berhenti, lihat keterangan</span>' : ''),
            esc(x.customer) + '<span class="sub">' + esc(x.project) + '</span>' + ((x.no_spk || x.top_ke) ? '<span class="sub">SPK ' + esc(x.no_spk || '–') + ' · TOP ' + esc(x.top_ke || '–') + '</span>' : ''),
            esc(x.company_name), nf(x.nilai), x.terbayar ? nf(x.terbayar) : '–', '<b>' + nf(x.saldo) + '</b>',
            '<span class="mp-umur ' + CODE[x.kategori] + '">' + x.umur + ' hari</span>',
            '<span class="mp-pill ' + CODE[x.kategori] + '">' + NAMA[x.kategori] + '</span>',
            bm + last + fu,
            '<button type="button" class="mp-btn sm c" data-id="' + esc(x.id) + '">Detail</button>' +
            (x.can_surat ? ' <a class="mp-btn sm o" target="_blank" href="' + BASE + '/surat/' + encodeURIComponent(x.id) + '" title="Print surat konfirmasi tagihan">Surat Konfirmasi</a>' : '')];
    }

    function dtLang(empty) {
        return {emptyTable: empty, zeroRecords: empty, lengthMenu: 'Tampilkan _MENU_ baris', processing: '', info: 'Menampilkan _START_-_END_ dari _TOTAL_ invoice', infoEmpty: 'Tidak ada data',
            paginate: {previous: '&laquo;', next: '&raquo;'}};
    }
    var DOM = 'rt<"mp-foot"<"mp-len"l><"mp-info"i>p>';
    function skelRows(sel, n) { $(sel + ' tbody').html('<tr><td colspan="11">' + '<span class="mp-sk row"></span>'.repeat(n) + '</td></tr>'); }

    function initTable() {
        $.fn.dataTable.ext.errMode = 'none';
        tbl = $('#mp-list').DataTable({
            serverSide: true, processing: false, searching: false, ordering: true, order: [], autoWidth: false, pageLength: 25, lengthMenu: [10, 25, 50, 100], dom: DOM,
            language: dtLang('Tidak ada piutang untuk filter ini.'),
            columns: SORT.map(function (k) { return {orderable: !!k}; }), // urut di server (Piutang_aging::PAGE_SORT_KEYS)
            createdRow: function (tr, data, idx) { $(tr).addClass('click'); },
            columnDefs: [{targets: [4, 5, 6, 7], className: 'num'}, {targets: 1, className: 'no'}],
            ajax: function (d, cb) {
                skelRows('#mp-list', 5);
                var o = d.order && d.order.length ? d.order[0] : null;
                $.getJSON(BASE + '/list_data', $.extend(params(), {draw: d.draw, start: d.start, length: d.length, sort: o ? SORT[o.column] : '', dir: o ? o.dir : ''})).done(function (r) {
                    $('#mp-err').hide();
                    $('#tot-label').text('Total ' + r.recordsFiltered + ' invoice');
                    $('#tot-nilai').text(nf(r.totals.nilai)); $('#tot-terbayar').text(nf(r.totals.terbayar)); $('#tot-saldo').text(nf(r.totals.saldo));
                    cb({draw: r.draw, recordsTotal: r.recordsTotal, recordsFiltered: r.recordsFiltered, data: r.data.map(function (x, i) { return rowCells(x, i, d.start); })});
                    $('#mp-list tbody tr').each(function (i) { if (r.data[i] && r.data[i].bermasalah) { $(this).addClass('bad'); } });
                }).fail(function (x) {
                    $('#mp-err').text((x.responseJSON || {}).error || 'Gagal memuat daftar piutang. Periksa koneksi lalu coba lagi.').show();
                    cb({draw: d.draw, recordsTotal: 0, recordsFiltered: 0, data: []});
                });
            }
        });
    }

    // Rekap dibuat saat tab pertama kali dibuka (DataTables butuh tabel terlihat untuk menghitung lebar kolom)
    var RK_SORT = ['customer', null, 'count', 'lancar', 'potensi', 'macet', 'total', 'oldest'];
    function zc(v) { return '<span class="' + (v ? '' : 'z') + '">' + (v ? rp(v) : '–') + '</span>'; }
    function initRekap() {
        rkTbl = $('#mp-rekap').DataTable({
            serverSide: true, processing: false, searching: false, pageLength: 25, lengthMenu: [10, 25, 50, 100], order: [], autoWidth: false, dom: DOM,
            language: dtLang('Tidak ada piutang untuk filter ini.'),
            columns: RK_SORT.map(function (k) { return {orderable: !!k}; }),
            columnDefs: [{targets: [2, 3, 4, 5, 6, 7], className: 'num'}],
            ajax: function (d, cb) {
                var o = d.order && d.order.length ? d.order[0] : null;
                $.getJSON(BASE + '/rekap_data', $.extend(params(), {draw: d.draw, start: d.start, length: d.length, sort: o ? RK_SORT[o.column] : '', dir: o ? o.dir : ''})).done(function (r) {
                    $('#mp-err').hide();
                    var t = r.totals || {};
                    $('#rk-label').text('Total ' + r.recordsFiltered + ' customer'); $('#rk-count').text(t.count || 0);
                    ['lancar', 'potensi', 'macet', 'total'].forEach(function (k) { $('#rk-' + k).html(k === 'total' ? '<b>' + rp(t[k] || 0) + '</b>' : zc(t[k] || 0)); });
                    cb({draw: r.draw, recordsTotal: r.recordsTotal, recordsFiltered: r.recordsFiltered, data: r.data.map(function (x) {
                        return ['<a href="#" data-cust="' + esc(x.customer) + '"><b>' + esc(x.customer) + '</b></a>', esc(x.company) || '–', x.count, zc(x.lancar), zc(x.potensi), zc(x.macet), '<b>' + rp(x.total) + '</b>',
                            '<span class="mp-umur">' + x.oldest + ' hari</span>'];
                    })});
                }).fail(function (x) {
                    $('#mp-err').text((x.responseJSON || {}).error || 'Gagal memuat rekap piutang. Periksa koneksi lalu coba lagi.').show();
                    cb({draw: d.draw, recordsTotal: 0, recordsFiltered: 0, data: []});
                });
            }
        });
    }

    function load(skipTable) {
        $('#exp-label').text('(' + (kat === 'all' ? 'Semua' : NAMA[kat]) + ')');
        $('#mp-err').hide(); chips();
        $('#mp-cards').html(skelCards());
        if (skipTable !== true) { tbl.ajax.reload(null, true); if (rkTbl) { rkTbl.ajax.reload(null, true); } }
        $.getJSON(BASE + '/data', params()).done(function (r) {
            if (r.status !== 'success') { $('#mp-err').text(r.msg || 'Gagal memuat data piutang.').show(); return; }
            if (!$('#f-company option').length) { r.companies.forEach(function (c) { $('#f-company').append($('<option>').val(c.id).text(c.nm_company)); }); CompanyMulti.init('#f-company'); }
            var s = SUM = r.summary;
            $('#mp-grand').html('⚖ <b>Total keseluruhan sisa piutang ' + rp(s.grand_cents) + '</b> (' + (s.total.count + s.belum.count) + ' invoice) = Total Piutang sudah diterima ' + rp(s.total.cents) +
                ' + belum diterima customer ' + rp(s.belum.cents) + '. <span class="asof">Angka ini yang dicocokkan dengan saldo piutang di buku besar. Basis: invoice tanggal 1 Oktober 2026 ke atas dan 20 invoice pengecualian, sama dengan Follow Up Piutang.</span>');
            $('#mp-cards').html(cardsHtml(s));
            if (!$('#dl-customer option').length) { r.customers.forEach(function (c) { $('#dl-customer').append($('<option>').val(c)); }); }
        }).fail(function (x) {
            $('#mp-cards').empty();
            $('#mp-err').text((x.responseJSON || {}).msg || 'Gagal memuat data piutang. Periksa koneksi lalu coba lagi.').show();
        });
    }

    $('#mp-cards').on('click', '.mp-card[data-kat]', function (e) {
        if ($(e.target).closest('[data-dl]').length) { return; }
        var k = $(this).data('kat'); kat = (kat === k && k !== 'all') ? 'all' : k; load();
    });
    $('#mp-cards').on('keydown', '.mp-card[data-kat]', function (e) { if ((e.key === 'Enter' || e.key === ' ') && e.target === this) { e.preventDefault(); $(this).click(); } });
    $('#mp-cards').on('click', '[data-dl]', function () { window.location = BASE + '/export?' + $.param($.extend(params(), {kategori: $(this).data('dl')})); });
    $('#btn-export').on('click', function () { window.location = BASE + '/export?' + $.param(params()); });
    // Tahun, Bulan, dan tanggal dari-sampai boleh dipakai bersamaan (mockup perOk); server menolak dari > sampai.
    $('#f-company, #f-tahun, #f-bulan, #f-dari, #f-sampai').on('change', function () { load(); });
    $('#f-customer').on('change', function () { clearTimeout(window.__mpT); window.__mpT = setTimeout(load, 350); });
    $('#f-cari').on('input', function () { clearTimeout(window.__mpT); window.__mpT = setTimeout(load, 350); });
    $('#btn-clear').on('click', function () { CompanyMulti.set('#f-company', []); $('#f-customer, #f-cari, #f-tahun, #f-bulan, #f-dari, #f-sampai').val(''); kat = 'all'; load(); });
    $('#mp-chips').on('click', '[data-clr]', function () {
        var k = $(this).data('clr');
        if (k === 'company') { CompanyMulti.set('#f-company', []); } else if (k === 'customer') { $('#f-customer').val(''); } else if (k === 'cari') { $('#f-cari').val(''); } else if (k === 'kat') { kat = 'all'; }
        load();
    });
    $('#mp-tabs').on('click', 'button', function () {
        $('#mp-tabs button').removeClass('on').attr('aria-selected', 'false'); $(this).addClass('on').attr('aria-selected', 'true');
        var p = $(this).data('pane'); $('#pane-list').toggle(p === 'list'); $('#pane-rekap').toggle(p === 'rekap'); if (p === 'rekap' && !rkTbl) { initRekap(); }
    });
    $('#mp-rekap tbody').on('click', '[data-cust]', function (e) {
        e.preventDefault(); $('#f-customer').val($(this).data('cust')); $('#mp-tabs button[data-pane=list]').click(); load();
    });
    // Klik baris (selain tautan / tombol di dalamnya) membuka Detail, sama dengan tombol Detail (mockup openDetail).
    $('#mp-list tbody').on('click', 'tr.click', function (e) {
        if ($(e.target).closest('a, button').length) { return; }
        var id = $(this).find('[data-id]').first().data('id'); if (id) { showDetail(id); }
    });
    $('#mp-list tbody').on('click', '[data-id]', function (e) { e.preventDefault(); showDetail($(this).data('id')); });
    function fuTab(r) { return r.bermasalah && r.status !== 'close' ? 'bermasalah' : ({belum_kirim: '1', dikirim: '2', diterima: '3', plan_bayar: '4', close: 'close'})[r.status] || 'all'; }
    function showDetail(id) {
        $.getJSON(BASE + '/detail/' + encodeURIComponent(id)).done(function (d) {
            var r = d.row, today = TODAY;
            function dv(v) { return v ? dmy(v) : '–'; }
            function kv(k, v) { return '<div><span class="k">' + k + '</span>' + v + '</div>'; }
            var umur = r.kategori ? '<b class="mpd-umur mpd-' + r.kategori + '">' + r.umur + ' hari</b> <span class="mp-pill ' + CODE[r.kategori] + '">' + NAMA[r.kategori] + '</span>' : '<span class="mpd-muted">Belum diterima customer</span>';
            var h = '<div class="mpd"><div class="mpd-grid">' +
                kv('Customer', '<b>' + esc(r.customer) + '</b>') + kv('Company', esc(r.company_name) || '–') + kv('Tanggal Invoice', dv(r.tgl_invoice)) +
                kv('Program / Project', esc(r.project) || '–') + kv('No SPK', esc(r.no_spk) || '–') + kv('No Faktur', esc(r.no_faktur) || '–') +
                kv('Term of Payment', r.top_ke ? 'TOP ke-' + esc(r.top_ke) : '–') + kv('Umur Piutang', umur) + '</div>' +
                (r.bermasalah ? '<div class="mp-err"><b>INVOICE BERMASALAH</b> · Status FU ' + (r.fu_last_ke || '') + ': ' + esc(r.fu_last_status) + ' · ' + esc(r.ket_bermasalah) + '</div>' : '') +
                '<div class="mpd-kpi"><div>Nilai Invoice<b>' + nf(r.nilai) + '</b></div><div>Terbayar<b>' + nf(r.terbayar) + '</b></div><div>Sisa Piutang<b class="mpd-red">' + nf(r.saldo) + '</b></div></div>' +
                '<h5 class="mpd-h">Pengiriman &amp; Follow Up</h5><div class="mpd-grid">' +
                kv('Dikirim', dv(r.tgl_kirim) + (r.kirim_via ? ' via ' + esc(r.kirim_via) : '') + (r.ket_kirim ? '<span class="k">' + esc(r.ket_kirim) + '</span>' : '')) +
                kv('No Resi', esc(r.no_resi) || '–') +
                kv('Diterima customer', dv(r.tgl_terima) + (r.ket_terima ? '<span class="k">' + esc(r.ket_terima) + '</span>' : '') +
                    (r.bukti_terima ? [{path: r.bukti_terima, nama: r.bukti_terima_nama}] : []).concat(r.bukti_extra || []).map(function (b) {
                        return '<span class="k"><i class="fa fa-paperclip"></i> <a href="' + esc(FILES + b.path) + '" target="_blank" rel="noopener">Bukti terima: ' + esc(b.nama || 'file') + '</a></span>';
                    }).join('')) +
                kv('PIC penerima FU', r.fu_pic_nama ? esc(r.fu_pic_nama) + (r.fu_pic_telp ? ' · ' + esc(r.fu_pic_telp) : '') : '–') +
                kv('Plan bayar', dv(r.plan_bayar) + (r.ket_plan_bayar ? '<span class="k">' + esc(r.ket_plan_bayar) + '</span>' : '')) + '</div>';
            h += '<div class="table-responsive"><table class="mpd-tbl"><thead><tr><th></th><th>Plan</th><th>Aktual</th><th>Status</th><th>Keterangan</th></tr></thead><tbody>';
            d.fu.forEach(function (x) {
                var akt = x.aktual ? '✓ ' + dmy(x.aktual) : (!x.plan ? '<span class="mpd-muted">Belum dijadwalkan</span>' : '<span class="' + (x.plan < today ? 'mpd-red' : 'mpd-muted') + '">Belum dilakukan</span>');
                h += '<tr><td><b>FU ' + x.ke + '</b>' + (x.ke > 4 ? '<span class="k">tambahan</span>' : '') + '</td><td>' + dv(x.plan) + '</td><td>' + akt + '</td><td>' +
                    (x.status ? '<span class="mp-pill ' + (x.status === 'INVOICE BERMASALAH' ? 'R' : 'B') + '">' + esc(x.status) + '</span>' : '') + '</td><td>' + esc(x.ket) + '</td></tr>';
            });
            h += '</tbody></table></div><h5 class="mpd-h">Riwayat Penerimaan</h5>';
            h += d.receipts.length ? '<div class="table-responsive"><table class="mpd-tbl"><thead><tr><th>Tanggal</th><th>No Penerimaan</th><th class="mpd-r">Nominal</th></tr></thead><tbody>' +
                d.receipts.map(function (x) { return '<tr><td>' + dv(x.tanggal) + '</td><td>' + esc(x.no_penerimaan) + '</td><td class="mpd-r">' + nf(x.nominal) + '</td></tr>'; }).join('') + '</tbody></table></div>'
                : '<p class="mpd-muted">Belum ada penerimaan untuk invoice ini.</p>';
            $('#mp-modal-title').text('Detail Invoice ' + r.no_invoice); $('#mp-modal-body').html(h + '</div>'); $('#mp-open-fu').attr('href', FU + '?tab=' + fuTab(r) + '&open=' + encodeURIComponent(r.id));
            $('#mp-print-surat').attr('href', BASE + '/surat/' + encodeURIComponent(r.id)).toggle(!!r.can_surat); $('#mp-modal').modal('show');
        }).fail(function (x) { $('#mp-err').text((x.responseJSON || {}).msg || 'Detail invoice gagal dimuat. Coba lagi.').show(); });
    }
    // Tutorial halaman (mockup TOUR_CFG flow "Monitoring Piutang", 7 langkah). Tidak mengubah data.
    var listTab = function () { $('#mp-modal').modal('hide'); $('#mp-tabs button[data-pane=list]').click(); };
    if (window.PiutangTour) {
        PiutangTour.mount(document.getElementById('mp-tour'), {title: 'Monitoring Piutang', cleanup: function () { $('#mp-modal').modal('hide'); }, page: [
            {pg: 'Monitoring Piutang', go: listTab, el: '#mp-cards', t: '1. Card umur piutang', d: 'Total, Lancar (0-60 hari), Potensi Macet (61-90), Macet (&gt; 90), dan Belum Diterima Customer. Klik card untuk menyaring daftar.'},
            {el: '.mp-bar', t: '2. Filter', d: 'Company, Tahun / Bulan / Tanggal invoice (default Semua), Customer, dan Cari.'},
            {el: '#mp-cards [data-dl]', t: '3. Download per card', d: 'Unduh Excel invoice di kategori itu saja.'},
            {el: '#pane-list', t: '4. List piutang per invoice', d: 'Klik judul kolom untuk mengurutkan, klik baris untuk detail. Invoice bermasalah ditandai merah beserta keterangannya.'},
            {pg: 'Detail invoice', go: function () { listTab(); var b = $('#mp-list tbody [data-id]').first(); if (b.length) { showDetail(b.data('id')); } }, wait: 1200,
                el: '#mp-modal .modal-content', t: '5. Detail invoice', d: 'Data invoice, pengiriman, follow up, dan riwayat penerimaan. Tombol <b>Buka di Follow Up Piutang</b> membuka invoice ini di tab kerjanya.'},
            {pg: 'Rekap per Customer', go: function () { $('#mp-modal').modal('hide'); $('#mp-tabs button[data-pane=rekap]').click(); }, wait: 900, el: '#pane-rekap', t: '6. Rekap per customer', d: 'Total sisa piutang per customer per kategori umur, dengan company dan umur tertua.'},
            {go: listTab, el: '#btn-export', t: '7. Download Excel', d: 'Unduh daftar sesuai filter, dengan ringkasan kategori di atasnya.'}
        ]});
    }
    initTable(); load(true);
})();
</script>
