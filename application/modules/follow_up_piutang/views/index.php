<?php
$base = site_url('follow_up_piutang');
$bulan = array('Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<style>
    /* Tampilan mengikuti specs/docs/review-monitoring-piutang/mockup; warna pill memakai varian lebih gelap agar teks putih lolos kontras 4.5:1. */
    .fu-page { --line: #dfe3e8; --row: #f7f9fb; --muted: #5b6670; --blue: #1d5f93; --green: #067647; --orange: #b54708; --red: #b42318; --lock: #eef0f2; }
    .fu-page :focus-visible { outline: 2px solid #1d4ed8; outline-offset: 1px; }
    .fu-panel { background: #fff; border-top: 3px solid #3f8bba; border-radius: 3px; padding: 14px; margin-bottom: 16px; box-shadow: 0 1px 1px rgba(0, 0, 0, .08); }
    .fu-tabs { display: flex; flex-wrap: wrap; gap: 2px; border-bottom: 1px solid var(--line); margin-bottom: 12px; }
    .fu-tabs button { border: 1px solid transparent; border-bottom: 0; background: none; padding: 7px 14px; cursor: pointer; color: var(--muted); border-radius: 3px 3px 0 0; }
    .fu-tabs button.on { border-color: var(--line); background: #fff; color: #333a40; font-weight: 600; margin-bottom: -1px; }
    .fu-tabs .cnt { display: inline-block; min-width: 20px; padding: 0 6px; border-radius: 9px; background: var(--line); color: #333a40; font-size: 11.5px; font-weight: 700; text-align: center; margin-left: 4px; }
    .fu-tabs .cnt.red { background: var(--red); color: #fff; }
    .fu-tabs button.on .cnt { background: var(--blue); color: #fff; }
    .fu-tabhint { margin: -4px 0 12px; padding: 7px 12px; background: rgba(47, 127, 184, .10); border-left: 3px solid var(--blue); font-size: 13px; }
    .fu-filters { display: flex; flex-wrap: wrap; gap: 8px; align-items: flex-end; margin-bottom: 12px; }
    .fu-filters .sp { flex: 1; }
    .fu-page .fl { display: flex; flex-direction: column; gap: 3px; font-size: 12px; color: var(--muted); font-weight: 400; margin: 0; }
    .fu-page .fl select, .fu-page .fl input { min-width: 170px; }
    .fu-page .searchbox input { width: 280px; }
    .fu-page select, .fu-page input[type=text], .fu-page input[type=tel], .fu-page input[type=date], .fu-page input[type=search], .fu-page input[type=file] {
        background: #fff; border: 1px solid #c8d0d8; border-radius: 3px; padding: 5px 7px; width: 100%; font-size: 13px; color: #333a40; }
    .fu-page .fl select, .fu-page .fl input[type=search], .fu-page .fl input[type=text] { width: auto; }
    .fu-page .searchbox input { width: 280px; }
    .fu-page input:disabled, .fu-page select:disabled { background: var(--lock); }
    .fu-btn { border: 0; border-radius: 3px; padding: 6px 12px; cursor: pointer; color: #fff; background: var(--green); font-weight: 600; font-size: 13px; }
    .fu-btn.bl { background: var(--blue); } .fu-btn.r { background: var(--red); }
    .fu-btn.g { background: transparent; color: #333a40; border: 1px solid #c8d0d8; }
    .fu-btn.sm { padding: 3px 8px; font-size: 12px; }
    .fu-btn[disabled] { opacity: .5; cursor: not-allowed; }
    @media (pointer: coarse) { .fu-btn.sm, .fu-page select, .fu-page input { min-height: 44px; } }
    .okmsg { background: rgba(6, 118, 71, .10); border: 1px solid rgba(6, 118, 71, .45); border-radius: 3px; padding: 8px 12px; margin-bottom: 10px; font-size: 13px; }
    .okmsg.danger { background: rgba(180, 35, 24, .08); border-color: rgba(180, 35, 24, .45); }
    .linkbtn { background: none; border: 0; padding: 0; color: var(--blue); cursor: pointer; font-size: 13px; font-weight: 600; }
    table.fu-tbl { border-collapse: collapse; width: 100%; min-width: 860px; }
    .fu-tbl th, .fu-tbl td { padding: 8px 9px; border-bottom: 1px solid var(--line); text-align: left; vertical-align: top; }
    .fu-tbl th { font-weight: 700; font-size: 13px; white-space: nowrap; border-top: 0; }
    .fu-tbl tbody tr:nth-child(odd) { background: var(--row); }
    .fu-tbl td.num, .fu-tbl th.num { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
    .fu-tbl .no { white-space: nowrap; font-weight: 600; }
    .fu-tbl .sub { display: block; font-size: 12px; color: var(--muted); font-weight: 400; }
    .fu-tbl .asof { font-size: 12.5px; color: var(--muted); }
    .fu-pill { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 11.5px; font-weight: 700; color: #fff; white-space: nowrap; }
    .fu-pill.lancar { background: var(--green); } .fu-pill.potensi { background: var(--orange); } .fu-pill.macet { background: var(--red); }
    .fu-pill.st-belum_kirim { background: #5f6b76; } .fu-pill.st-dikirim { background: var(--blue); } .fu-pill.st-diterima { background: #0e7490; }
    .fu-pill.st-plan_bayar { background: var(--green); } .fu-pill.st-close { background: #4b5563; }
    .fu-pill.baru { background: var(--orange); margin-top: 3px; }
    .fu-pill.info { background: var(--blue); } .fu-pill.warn { background: var(--orange); }
    .umur { font-weight: 700; font-variant-numeric: tabular-nums; }
    .umur.lancar { color: var(--green); } .umur.potensi { color: var(--orange); } .umur.macet { color: var(--red); }
    .late { color: var(--red); }
    #fu-table.fu-tbl > tbody > tr.fu-baru > td { background-color: rgba(181, 71, 8, .10); }
    #fu-table.fu-tbl > tbody > tr.fu-baru > td:first-child { box-shadow: inset 3px 0 0 var(--orange); }
    .fu-foot { display: flex; flex-wrap: wrap; gap: 8px 16px; align-items: center; justify-content: space-between; margin-top: 10px; font-size: 13px; }
    .fu-foot .dataTables_length select { width: auto; display: inline-block; }
    .fu-empty-note { padding: 26px; text-align: center; color: var(--muted); }
    /* form modal */
    .fu-page .frm h3 { font-size: 14px; margin: 14px 0 8px; padding-bottom: 4px; border-bottom: 1px solid var(--line); font-weight: 700; }
    .fu-page .frm h3:first-of-type { margin-top: 4px; }
    .fu-page .hint { font-size: 12.5px; color: var(--muted); margin: 0 0 10px; }
    .frow { display: grid; grid-template-columns: 170px 170px 1fr; gap: 10px; }
    .frow label { display: flex; flex-direction: column; gap: 3px; font-size: 12px; color: var(--muted); font-weight: 400; margin: 0; }
    .frow .span2 { grid-column: span 2; }
    .req { color: var(--red); }
    .picbox { background: var(--row); border: 1px solid var(--line); border-radius: 3px; padding: 8px 10px; margin-bottom: 10px; }
    .fudone { list-style: none; padding: 0; margin: 0 0 8px; }
    .fudone li { border-left: 3px solid var(--green); background: var(--row); margin-bottom: 4px; font-size: 13px; }
    .fudone .fuhead { display: block; width: 100%; text-align: left; background: none; border: 0; padding: 6px 10px; cursor: pointer; font: inherit; color: inherit; }
    .fudone .fuhead:hover { background: var(--line); }
    .fudone .fubody { padding: 2px 10px 8px 32px; }
    .fuact { border: 1px solid var(--blue); border-left-width: 4px; border-radius: 3px; padding: 10px 12px; margin-bottom: 8px; }
    .fuact-t { font-weight: 700; margin-bottom: 8px; }
    .funext { margin-top: 8px; padding: 8px 10px; border: 1px dashed var(--blue); border-radius: 3px; background: var(--row); max-width: 480px; display: flex; flex-direction: column; gap: 4px; }
    .ingrp { display: flex; gap: 4px; } .ingrp input { flex: 1; }
    .fu-err { color: var(--red); font-size: 12.5px; margin: 0 0 8px; }
    .fu-modal-bar { display: flex; gap: 8px; align-items: center; }
    .fu-modal-bar .sp { flex: 1; }
    @media (max-width: 760px) { .frow { grid-template-columns: 1fr; } .frow .span2 { grid-column: auto; } }
</style>

<div class="fu-page">
<div class="fu-panel">
    <div class="fu-tabs" id="fu-tabs" role="tablist" aria-label="Tahap follow up">
        <button type="button" role="tab" aria-selected="true" class="on" data-tab="all">Semua <span class="cnt" data-c="all"></span></button>
        <button type="button" role="tab" aria-selected="false" data-tab="1">1. Kirim Invoice <span class="cnt" data-c="1"></span></button>
        <button type="button" role="tab" aria-selected="false" data-tab="2">2. Konfirmasi Diterima <span class="cnt" data-c="2"></span></button>
        <button type="button" role="tab" aria-selected="false" data-tab="3">3. Follow Up Customer <span class="cnt" data-c="3"></span></button>
        <button type="button" role="tab" aria-selected="false" data-tab="4">4. Tunggu Bayar &amp; Close <span class="cnt" data-c="4"></span></button>
        <button type="button" role="tab" aria-selected="false" data-tab="late">FU Terlambat <span class="cnt red" data-c="late"></span></button>
        <button type="button" role="tab" aria-selected="false" data-tab="close">Invoice Close (Laporan)</button>
    </div>
    <p class="fu-tabhint" id="fu-hint"><i class="fa fa-info-circle" aria-hidden="true"></i> <span></span></p>

    <div class="fu-filters">
        <label class="fl">Company<select id="f-company"><option value="">Semua Company</option></select></label>
        <label class="fl">Customer<input type="text" id="f-customer" list="dl-customer" autocomplete="off" placeholder="Pilih dari daftar"></label><datalist id="dl-customer"></datalist>
        <label class="fl">Tahun Invoice
            <select id="f-inv-tahun"><option value="">Semua Tahun</option><?php foreach ($inv_years as $y) echo '<option>' . $y . '</option>'; ?></select>
        </label>
        <label class="fl">Bulan Invoice
            <select id="f-inv-bulan"><option value="">Semua Bulan</option><?php foreach ($bulan as $i => $b) echo '<option value="' . ($i + 1) . '">' . $b . '</option>'; ?></select>
        </label>
        <label class="fl" id="f-fu-wrap">Follow Up
            <select id="f-fu">
                <option value="">Semua</option><option value="none">Belum ada FU (belum diterima)</option>
                <option value="1">FU 1 berjalan</option><option value="2">FU 2 berjalan</option><option value="3">FU 3 berjalan</option>
                <option value="4">FU 4 berjalan</option><option value="tambahan">FU tambahan berjalan (FU 5 dst.)</option><option value="selesai">Semua FU selesai</option>
            </select>
        </label>
        <label class="fl" id="f-plan-bulan-wrap" style="display:none">Bulan Plan Bayar
            <select id="f-plan-bulan"><option value="">Semua Bulan</option><?php foreach ($bulan as $i => $b) echo '<option value="' . ($i + 1) . '">' . $b . '</option>'; ?></select>
        </label>
        <label class="fl" id="f-plan-tahun-wrap" style="display:none">Tahun
            <select id="f-plan-tahun"><option value="">Semua</option><?php foreach ($years as $y) echo '<option>' . $y . '</option>'; ?></select>
        </label>
        <label class="fl" id="f-close-wrap" style="display:none">Tahun Close
            <select id="f-close-tahun"><?php foreach ($years as $y) echo '<option' . ($y == date('Y') ? ' selected' : '') . '>' . $y . '</option>'; ?></select>
        </label>
        <div class="sp"></div>
        <label class="fl searchbox">Cari
            <input type="search" id="fu-search" placeholder="Cari: invoice, SPK, customer, dll." title="Mencari di no invoice, tanggal, customer, project, no SPK, company, via kirim, no resi, nominal, status, dan FU. Beberapa kata = semua kata harus cocok.">
        </label>
        <button type="button" class="fu-btn" id="btn-export"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export Excel</button>
    </div>

    <div id="fu-msg" role="status" style="display:none"></div>

    <div class="table-responsive" id="fu-panel" role="tabpanel">
        <table id="fu-table" class="fu-tbl">
            <thead><tr><th>No</th><th>Invoice</th><th>Customer</th><th class="num">Sisa Piutang</th><th>Dikirim</th><th>Diterima / Umur</th><th>Follow Up Berikutnya</th><th>Plan Bayar</th><th>Status</th><th>Close</th><th><span class="sr-only">Aksi</span></th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="fu-modal" tabindex="-1" role="dialog" aria-labelledby="fu-modal-title"><div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Tutup">&times;</button><h4 class="modal-title" id="fu-modal-title"></h4></div>
    <div class="modal-body">
        <div id="fu-modal-msg" class="fu-err" role="alert" style="display:none"></div>
        <div id="fu-modal-body"></div>
        <div id="fu-log"></div>
    </div>
    <div class="modal-footer fu-modal-bar">
        <button type="button" class="linkbtn" id="btn-history">Riwayat perubahan</button><span class="sp"></span>
        <button type="button" class="fu-btn g" data-dismiss="modal">Batal</button>
        <button type="button" class="fu-btn bl" id="btn-save">Simpan</button>
    </div>
</div></div></div>

<div class="modal fade" id="fu-close-modal" tabindex="-1" role="dialog" aria-labelledby="fu-close-title"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Tutup">&times;</button><h4 class="modal-title" id="fu-close-title"></h4></div>
    <div class="modal-body">
        <p id="fu-close-info"></p>
        <p class="hint">Invoice sudah lunas. Close hanya bisa untuk invoice yang sudah lunas dan punya plan bayar.</p>
        <label class="fl">Keterangan close<textarea id="fu-close-ket" class="form-control" rows="3" placeholder="Mis. lunas sesuai penerimaan ..."></textarea></label>
        <div id="fu-close-err" class="fu-err" role="alert" style="margin-top:6px"></div>
        <p class="hint">Setelah close, invoice tidak lagi dihitung di Monitoring Piutang dan pindah ke tab Invoice Close.</p>
    </div>
    <div class="modal-footer"><button type="button" class="fu-btn g" data-dismiss="modal">Batal</button> <button type="button" class="fu-btn r" id="btn-do-close">Close Invoice</button></div>
</div></div></div>
</div>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script>
(function () {
    var BASE = '<?= $base ?>', FILES = '<?= base_url() ?>', TODAY = '<?= date('Y-m-d') ?>', MANAGE = <?= $can_manage ? 'true' : 'false' ?>, INV_MULAI_TAHUN = <?= (int) $inv_mulai_tahun ?>, INV_MULAI_BULAN = <?= (int) $inv_mulai_bulan ?>;
    var HINTS = {
        all: 'Semua invoice yang belum close.',
        '1': 'Isi tanggal kirim dan dikirim via (JNE / Driver).',
        '2': 'Invoice sudah dikirim. Isi tanggal terima setelah customer menerima.',
        '3': 'Customer sudah menerima. Lakukan follow up satu per satu sampai customer memberi rencana bayar.',
        '4': 'Rencana bayar sudah ada. Data terkunci; satu-satunya aksi adalah Close setelah lunas.',
        late: 'Follow up berjalan yang jadwalnya sudah lewat dan belum ada rencana bayar. Segera lakukan follow up.',
        close: 'Laporan invoice yang sudah di-close, per tahun close.'
    };
    var KAT = {lancar: 'Lancar', potensi: 'Potensi', macet: 'Macet'};
    var C = {fu: 6, plan: 7, status: 8, close: 9};
    var f = {tab: 'all'}, cur = null, table = null, VIA = ['JNE', 'Driver'], picked = null, closeRow = null;

    function esc(s) { return $('<div>').text(s == null ? '' : String(s)).html(); }
    function nf(v) { return Number(v || 0).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}); }
    function dmy(d) { return d ? String(d).substr(8, 2) + '-' + String(d).substr(5, 2) + '-' + String(d).substr(0, 4) : ''; }
    function ico(n) { return '<i class="fa fa-' + n + '" aria-hidden="true"></i>'; }

    // Pesan di halaman dan di modal (menggantikan alert()).
    function say(text, kind) { $('#fu-msg').attr('class', 'okmsg' + (kind === 'danger' ? ' danger' : '')).text(text).show(); }
    function mfail(text) { $('#fu-modal-msg').text(text).show(); $('#fu-modal').scrollTop(0); }

    function params() {
        return $.extend({}, f, {
            company: $('#f-company').val(), customer: $('#f-customer').val(), fu: $('#f-fu').val(),
            plan_bulan: $('#f-plan-bulan').val(), plan_tahun: $('#f-plan-tahun').val(), close_tahun: $('#f-close-tahun').val(),
            inv_tahun: $('#f-inv-tahun').val(), inv_bulan: $('#f-inv-bulan').val()
        });
    }

    // Kolom dan filter mengikuti tab: tab 4 tanpa kolom FU (follow up berhenti), tab close menampilkan kolom Close.
    function applyTab() {
        var t = f.tab, t4 = t === '4', cl = t === 'close';
        $('#fu-hint span').text(HINTS[t] || '');
        $('#f-fu-wrap').toggle(!t4 && !cl);
        $('#f-plan-bulan-wrap, #f-plan-tahun-wrap').toggle(t4);
        $('#f-close-wrap').toggle(cl);
        table.column(C.fu).visible(!t4 && !cl, false);
        table.column(C.status).visible(!cl, false);
        table.column(C.close).visible(cl, false);
        $(table.column(C.plan).header()).text(t4 ? 'Rencana Bayar' : 'Plan Bayar');
    }
    function load(keep) { applyTab(); table.ajax.reload(null, !keep); }

    function fuCell(x) {
        if (x.status === 'plan_bayar') { return '<span class="asof">Selesai (ada rencana bayar)</span>'; }
        if (x.status !== 'diterima') { return '<span class="asof">–</span>'; }
        if (x.fu_stage === 'selesai' || !x.fu_next_plan) { return '<span class="asof">Semua FU selesai</span>'; }
        var n = x.fu_stage === 'tambahan' ? 'FU tambahan' : 'FU ' + x.fu_stage;
        return '<span class="' + (x.fu_late ? 'late' : '') + '"><b>' + n + '</b> · ' + dmy(x.fu_next_plan) + (x.fu_late ? '<span class="sub late">lewat jadwal</span>' : '') + '</span>';
    }

    function aksiCell(x) {
        var inv = esc(x.no_invoice), h = '';
        var look = !x.can_update;
        h += '<button type="button" class="fu-btn sm ' + (look ? 'g' : 'bl') + '" data-act="update" data-id="' + esc(x.id) + '" aria-label="' + (look ? 'Lihat' : 'Update') + ' follow up invoice ' + inv + '">' + (look ? 'Lihat' : 'Update') + '</button> ';
        if (MANAGE && x.status !== 'close') {
            h += '<button type="button" class="fu-btn sm r" data-act="close" data-id="' + esc(x.id) + '" aria-label="Close invoice ' + inv + '"' +
                (x.can_close ? '' : ' disabled title="' + esc(x.close_block || 'Isi plan tanggal bayar dulu') + '"') + '>Close</button>' +
                (x.can_close ? '' : '<span class="sub">' + esc(x.close_block || 'Isi plan tanggal bayar dulu') + '</span>'); // alasan terlihat di layar sentuh/keyboard
        }
        return h;
    }

    function initTable() {
        $.fn.dataTable.ext.errMode = 'none'; // error ajax ditampilkan lewat banner #fu-msg
        table = $('#fu-table').DataTable({
            serverSide: true, processing: true, ordering: false, autoWidth: false, pageLength: 100, lengthMenu: [25, 50, 100, 250],
            dom: 'rt<"fu-foot"<"dataTables_length_wrap"l><"dataTables_info_wrap"i>p>', searchDelay: 400,
            language: {lengthMenu: 'Tampilkan _MENU_ invoice', processing: 'Memuat daftar invoice...',
                info: 'Menampilkan _START_-_END_ dari _TOTAL_ invoice', infoEmpty: 'Tidak ada invoice', infoFiltered: '(dari _MAX_ di tab ini)',
                zeroRecords: 'Tidak ada invoice yang cocok dengan tab, filter, atau kata cari ini. Hapus kata cari, ubah filter, atau pilih tab lain.',
                emptyTable: 'Belum ada invoice pada tab ini.', paginate: {previous: '&laquo;', next: '&raquo;'}},
            ajax: {url: BASE + '/data', type: 'GET', data: function (d) { $.extend(d, params()); }},
            createdRow: function (tr, x) { if (x.baru) { $(tr).addClass('fu-baru'); } },
            columns: [
                {data: null, render: function (_, t, x, m) { return m.row + m.settings._iDisplayStart + 1; }},
                {data: null, className: 'no', render: function (_, t, x) {
                    return esc(x.no_invoice) + '<span class="sub">' + dmy(x.tgl_invoice) + '</span>' +
                        (x.baru ? '<span class="fu-pill baru">' + ico('star') + ' Baru' + (x.baru_from ? ' · dari ' + esc(x.baru_from) : '') + '</span>' +
                            (x.status_changed_date ? '<span class="sub">pindah ' + dmy(x.status_changed_date) + '</span>' : '') : ''); }},
                {data: null, render: function (_, t, x) {
                    return esc(x.customer) + '<span class="sub">' + esc(x.project) + ' · ' + esc(x.company_name) + '</span>' +
                        ((x.no_spk || x.top_ke) ? '<span class="sub">SPK ' + esc(x.no_spk || '–') + ' · TOP ' + esc(x.top_ke || '–') + '</span>' : ''); }},
                {data: null, className: 'num', render: function (_, t, x) { return '<b>' + nf(x.saldo) + '</b><span class="sub">dari ' + nf(x.nilai) + '</span>'; }},
                {data: null, render: function (_, t, x) {
                    return x.tgl_kirim ? dmy(x.tgl_kirim) + (x.kirim_via ? '<span class="sub">' + esc(x.kirim_via) + '</span>' : '') + (x.no_resi ? '<span class="sub">Resi ' + esc(x.no_resi) + '</span>' : '') : '<span class="asof">Belum</span>'; }},
                {data: null, render: function (_, t, x) {
                    if (!x.tgl_terima) { return '<span class="asof">Belum diterima</span>'; }
                    var k = x.kategori && Number(x.saldo) > 0 ? '<span class="sub"><span class="umur ' + x.kategori + '">' + x.umur + ' hari</span> <span class="fu-pill ' + x.kategori + '">' + (KAT[x.kategori] || x.kategori) + '</span></span>' : '';
                    return dmy(x.tgl_terima) + k; }},
                {data: null, render: function (_, t, x) { return fuCell(x); }},
                {data: null, render: function (_, t, x) {
                    return x.plan_bayar ? '<b>' + dmy(x.plan_bayar) + '</b>' + (x.ket_plan_bayar ? '<span class="sub">' + esc(x.ket_plan_bayar) + '</span>' : '') : '<span class="asof">–</span>'; }},
                {data: null, render: function (_, t, x) { return '<span class="fu-pill st-' + esc(x.status) + '">' + esc(x.status_label) + '</span>'; }},
                {data: null, render: function (_, t, x) { return dmy(x.close_date) + '<span class="sub">' + esc(x.close_reason) + '</span>'; }},
                {data: null, className: 'no', render: function (_, t, x) { return aksiCell(x); }}
            ]
        });
        table.on('error.dt', function () { say('Daftar invoice gagal dimuat. Periksa koneksi lalu ganti tab atau muat ulang halaman.', 'danger'); });
        // jumlah per tab dan daftar company datang bersama respons tabel
        table.on('xhr.dt', function (e, settings, json) {
            if (!json) { return; }
            $('#fu-msg.danger').hide(); // pesan sukses (mis. setelah Close) tetap tampil
            if (json.error) { say(json.error, 'danger'); } // filter tidak valid: server membalas 200 + error
            if (json.customers && !$('#dl-customer option').length) { json.customers.sort().forEach(function (c) { $('#dl-customer').append($('<option>').val(c)); }); }
            $.each(json.counts || {}, function (k, v) { $('[data-c="' + k + '"]').text(v); });
            if (json.companies && $('#f-company option').length === 1) {
                json.companies.forEach(function (c) { $('#f-company').append($('<option>').val(c.id).text(c.nm_company)); });
            }
        });
    }

    // ---- Form Update (step by step), mengikuti mockup ----
    function inp(name, val, type, locked, extra) {
        return '<input type="' + (type || 'text') + '" name="' + name + '" value="' + esc(val) + '"' + (locked ? ' disabled' : '') + (extra || '') + '>';
    }
    function note(locked, canEdit) {
        return locked ? '<p class="hint">' + ico('lock') + ' Terkunci</p>' : (canEdit ? '<p class="hint">' + ico('pencil') + ' Masih bisa dikoreksi</p>' : '');
    }

    // Bukti terima: link file yang sudah tersimpan + input file. File dipilih disimpan di `picked` karena form dibangun ulang saat tanggal berubah.
    function buktiHtml(r, locked) {
        var h = '';
        if (r.bukti_terima) {
            h += '<div>' + ico('paperclip') + ' <a href="' + esc(FILES + r.bukti_terima) + '" target="_blank" rel="noopener">Lihat bukti: ' + esc(r.bukti_terima_nama || 'file') + '</a></div>';
        }
        if (!locked) {
            h += '<input type="file" id="bukti-file" accept=".pdf,.jpg,.jpeg,.png">' +
                '<span class="hint" style="margin:0">' + (picked ? 'File dipilih: ' + esc(picked.name) : (r.bukti_terima ? 'Pilih file baru hanya bila ingin mengganti.' : 'Belum ada file.')) + '</span>';
        } else if (!r.bukti_terima) {
            h += '<span class="hint" style="margin:0">Tidak ada file.</span>';
        }
        return h;
    }

    // Jarak jadwal FU (sama dengan Piutang_aging::FU_OFFSET dan mockup): FU1 = terima + 3, FU2 = FU1 + 7, FU3 = FU2 + 14, FU4 = FU3 + 14, FU tambahan = +14.
    var GAP = [3, 7, 14, 14];
    function gapOf(ke) { return GAP[ke - 1] || 14; }
    function fuRule(ke) { return ke === 1 ? 'tanggal terima + ' + gapOf(1) + ' hari' : 'FU ' + (ke - 1) + ' dilakukan + ' + gapOf(ke) + ' hari'; }

    function fuFields(x, N, lkf, done) {
        var plan = x.plan || (cur.suggest && cur.suggest[x.ke]) || '';
        return '<div class="frow"><label>Jadwal follow up' + inp(N('plan'), plan, 'date', lkf || done) +
            (done ? '' : '<span class="hint" style="margin:0">Otomatis: ' + fuRule(x.ke) + '. Boleh diubah / diundur.</span>') + '</label>' +
            '<label>Tanggal dilakukan<span class="ingrp">' + inp(N('aktual'), x.aktual, 'date', lkf) +
            (lkf ? '' : '<button type="button" class="fu-btn g sm fu-today" data-name="' + N('aktual') + '">Hari ini</button>') + '</span></label>' +
            '<label>Keterangan / jawaban customer' + inp(N('ket'), x.ket, 'text', lkf) + '</label></div>';
    }

    function buildForm(d) {
        var r = d.row, L = d.locks, all = L.all, ro = all || !MANAGE;
        var h = '<form id="fu-form" class="frm"><input type="hidden" name="id" value="' + esc(r.id) + '">';
        h += '<p class="hint">' + esc(r.customer) + ' · sisa piutang <b>' + nf(r.saldo) + '</b> dari ' + nf(r.nilai) + '</p>';
        if (r.status === 'plan_bayar') {
            h += '<div class="okmsg">' + ico('lock') + ' Customer sudah memberi rencana bayar <b>' + dmy(r.plan_bayar) + '</b>. Data tidak bisa diubah lagi; klik <b>Close</b> di daftar setelah pembayaran diterima.</div>';
        } else if (r.status === 'close') {
            h += '<div class="okmsg">' + ico('lock') + ' Invoice sudah di-close. Data hanya bisa dilihat.</div>';
        } else if (!MANAGE) {
            h += '<div class="okmsg">' + ico('lock') + ' Anda hanya dapat melihat data ini.</div>';
        }
        if (!ro) { h += '<p class="hint">' + ico('info-circle') + ' Form diisi step by step. Step yang sudah disimpan masih bisa dikoreksi sampai step berikutnya disimpan, setelah itu terkunci supaya data tidak berubah-ubah.</p>'; }

        // 1 pengiriman
        var lk = ro || L.kirim;
        h += '<h3>1. Pengiriman Invoice</h3><div class="frow"><label>Tanggal kirim' + inp('tgl_kirim', r.tgl_kirim, 'date', lk) + '</label>' +
            '<label>Dikirim via<select name="kirim_via"' + (lk ? ' disabled' : '') + '><option value="">- Pilih -</option>' +
            VIA.map(function (v) { return '<option' + (r.kirim_via === v ? ' selected' : '') + '>' + v + '</option>'; }).join('') +
            (r.kirim_via && VIA.indexOf(r.kirim_via) < 0 ? '<option selected>' + esc(r.kirim_via) + '</option>' : '') + '</select></label>' +
            '<label id="ket-kirim-wrap"' + (r.kirim_via ? '' : ' hidden') + '><span id="ket-kirim-lbl">' + (r.kirim_via === 'Driver' ? 'Keterangan Driver (nama driver, dll)' : 'Keterangan JNE (layanan, dll)') + '</span>' + inp('ket_kirim', r.ket_kirim, 'text', lk) + '</label></div>';
        if (r.tgl_kirim) {
            h += (lk ? '<p class="hint" style="margin-top:6px">' + ico('lock') + ' Data pengiriman terkunci karena tanggal terima sudah disimpan.</p>' : (ro ? '' : '<p class="hint" style="margin-top:6px">' + ico('pencil') + ' Data pengiriman sudah tersimpan dan masih bisa dikoreksi sampai tanggal terima disimpan.</p>')) +
                '<div class="frow" style="margin-top:8px"><label>No resi <span class="req" id="resi-req"' + (r.kirim_via === 'JNE' ? '' : ' hidden') + '>(wajib bila via JNE)</span>' +
                inp('no_resi', r.no_resi, 'text', ro || L.resi, ' maxlength="100"') + '</label>' +
                '<p class="hint span2" style="align-self:end">' + (L.resi ? ico('lock') + ' No resi terkunci karena FU 1 sudah disimpan.' : 'No resi bisa diisi belakangan (baru diketahui setelah kirim). Terkunci setelah terisi dan FU 1 disimpan.') + '</p></div>';
        }
        // 2 diterima
        if (r.tgl_kirim) {
            var lt = ro || L.terima;
            h += '<h3>2. Diterima Customer</h3><div class="frow"><label>Tanggal terima' + inp('tgl_terima', r.tgl_terima, 'date', lt) + '</label>' +
                '<label class="span2">Keterangan' + inp('ket_terima', r.ket_terima, 'text', lt, ' placeholder="Nama penerima"') + '</label></div>' +
                '<div class="frow" style="margin-top:8px"><label class="span2" style="grid-column:1/-1">Bukti terima <span class="req">(wajib: PDF / JPG / PNG, maks 5 MB)</span>' + buktiHtml(r, lt) + '</label></div>' +
                (r.tgl_terima ? note(lt, !ro) : '') +
                '<p class="hint" style="margin-top:6px">Umur piutang dihitung dari tanggal terima. Tanpa tanggal terima, invoice belum masuk Monitoring Piutang.</p>';
        }
        // 3 follow up + 4 plan bayar
        if (r.tgl_terima) {
            h += '<h3>3. Follow Up</h3><div class="picbox"><div class="frow"><label>Nama PIC penerima follow up <span class="req">*</span>' + inp('fu_pic_nama', r.fu_pic_nama, 'text', ro, ' placeholder="Nama orang yang dihubungi"') + '</label>' +
                '<label>No WA / Telepon PIC <span class="req">*</span>' + inp('fu_pic_telp', r.fu_pic_telp, 'tel', ro, ' placeholder="0812xxxxxxxx"') + '</label></div>' +
                '<p class="hint" style="margin:6px 0 0">Diisi sekali untuk invoice ini dan berlaku untuk semua FU. Bisa dikoreksi (tidak bisa dikosongkan) sampai plan bayar disimpan.</p></div>' +
                '<p class="hint">Follow up berjalan satu per satu. Isi <b>tanggal dilakukan</b> setelah follow up benar-benar dilakukan, lalu FU berikutnya dijadwalkan otomatis.</p>';
            var doneHtml = '', activeHtml = '', shownCurrent = false, lastDone = -1;
            d.fu.forEach(function (x, i) { if (x.aktual) { lastDone = i; } });
            d.fu.forEach(function (x, i) {
                var done = !!x.aktual, isCur = !done && !shownCurrent;
                if (!done && !isCur) { return; }
                if (isCur) { shownCurrent = true; }
                var lkf = ro || !!L.fu[x.ke];
                var N = function (k) { return x.ke <= 4 ? 'fu' + x.ke + '_' + k : 'extra_fu[' + (x.ke - 5) + '][' + k + ']'; };
                var extraKe = x.ke > 4 ? '<input type="hidden" name="' + N('ke') + '" value="' + x.ke + '">' : '';
                if (done) {
                    var open = i === lastDone && !lkf;
                    doneHtml += '<li><button type="button" class="fuhead" data-fuopen aria-expanded="' + open + '">' + ico(open ? 'caret-down' : 'caret-right') + ' ' + ico('check') + ' <b>FU ' + x.ke + '</b> dilakukan ' + dmy(x.aktual) + ' ' +
                        (lkf ? '<span class="fu-pill st-close">' + ico('lock') + ' terkunci</span>' : '<span class="fu-pill info">bisa dikoreksi</span>') + '</button>' +
                        '<div class="fubody"' + (open ? '' : ' hidden') + '>' + extraKe + fuFields(x, N, lkf, true) + '</div></li>';
                } else {
                    activeHtml += '<div class="fuact"><div class="fuact-t">FU ' + x.ke + ' <span class="fu-pill st-diterima">berjalan</span></div>' + extraKe + fuFields(x, N, lkf, false) +
                        '<p class="hint" style="margin:6px 0 0">' + (x.ke < 4 ? 'Setelah FU ' + x.ke + ' disimpan sebagai dilakukan, FU ' + (x.ke + 1) + ' dijadwalkan otomatis ' + gapOf(x.ke + 1) + ' hari setelah tanggal dilakukan. Jadwalnya masih bisa diubah saat FU ' + (x.ke + 1) + ' berjalan.' :
                            (x.ke === 4 ? 'FU terakhir dari standar. Setelah FU 4 dilakukan, bisa tambah FU 5 dst. bila customer belum memberi rencana bayar. Jadwal boleh diundur sampai ada plan bayar.' : 'Follow up tambahan di luar standar FU 1 – 4. Jadwal boleh diundur sampai ada plan bayar.')) + '</p></div>';
                }
            });
            h += (doneHtml ? '<ul class="fudone">' + doneHtml + '</ul>' : '') + activeHtml;
            if (r.status === 'plan_bayar') {
                h += '<p class="hint">' + ico('stop') + ' Follow up berhenti karena customer sudah memberi rencana tanggal bayar.</p>';
            } else if (!activeHtml && ro) {
                h += '<p class="hint">' + ico('check') + ' Semua follow up sudah dilakukan.</p>';
            }
            if (!ro && d.can_add_extra) {
                h += '<div class="funext"><button type="button" class="fu-btn g sm" id="btn-add-fu" data-ke="' + d.next_extra_ke + '">+ Tambah FU ' + d.next_extra_ke + '</button>' +
                    '<span class="hint" style="margin:0">Bila customer belum memberi rencana bayar. Jadwal otomatis 14 hari setelah FU ' + (d.next_extra_ke - 1) + ' dilakukan.</span></div>';
            }
            h += '<h3>4. Plan Bayar Customer</h3><div class="frow"><label>Plan tanggal bayar' + inp('plan_bayar', r.plan_bayar, 'date', ro) + '</label>' +
                '<label class="span2">Keterangan' + inp('ket_plan_bayar', r.ket_plan_bayar, 'text', ro) + '</label></div>' +
                (ro ? '' : '<p class="hint" style="margin-top:6px">' + ico('exclamation-triangle') + ' Setelah rencana bayar disimpan, follow up berhenti dan data invoice ini tidak bisa diubah lagi. Invoice pindah ke tab <b>4. Tunggu Bayar &amp; Close</b>, tinggal di-Close setelah pembayaran diterima.</p>');
        } else if (!ro) {
            h += '<p class="hint" style="margin-top:12px">' + ico('info-circle') + ' ' + (!r.tgl_kirim ? 'Isi tanggal kirim dulu. Bagian "Diterima Customer" muncul setelah tanggal kirim diisi.' : 'Isi tanggal terima setelah customer menerima invoice. Follow up dan plan bayar muncul setelah itu.') + '</p>';
        }
        $('#fu-modal-body').html(h + '</form>');
        $('#btn-save').toggle(!ro);
    }

    function openForm(id) {
        $('#fu-msg').hide();
        $.getJSON(BASE + '/form/' + encodeURIComponent(id)).done(function (d) {
            if (d.status !== 'success') { say(d.msg || 'Form follow up tidak dapat dibuka.', 'danger'); return; }
            cur = d; picked = null; $('#fu-modal-msg').hide(); $('#fu-log').empty();
            $('#fu-modal-title').text((d.locks.all || !MANAGE ? 'Follow Up ' : 'Update Follow Up ') + d.row.no_invoice); buildForm(d); $('#fu-modal').modal('show');
        }).fail(function (x) { say((x.responseJSON || {}).msg || 'Form follow up gagal dimuat. Coba lagi; bila terulang, hubungi admin.', 'danger'); });
    }

    // Close invoice: modal dengan keterangan wajib (menggantikan prompt()).
    function openClose(x) {
        closeRow = x; $('#fu-close-err').text(''); $('#fu-close-ket').val('');
        $('#fu-close-title').text('Close Invoice ' + x.no_invoice);
        $('#fu-close-info').html(esc(x.customer) + ' · plan bayar <b>' + dmy(x.plan_bayar) + '</b>');
        $('#fu-close-modal').modal('show');
    }

    $('#fu-tabs').on('click', 'button', function () {
        $('#fu-tabs button').removeClass('on').attr('aria-selected', 'false');
        $(this).addClass('on').attr('aria-selected', 'true'); f.tab = $(this).data('tab'); load();
    });
    // Bulan sebelum batas mulai (Jan-Sep 2026) pasti kosong: disembunyikan bila tahun 2026 atau semua tahun dipilih.
    function syncInvBulan() {
        var y = $('#f-inv-tahun').val(), $b = $('#f-inv-bulan');
        $b.find('option[value!=""]').each(function () { $(this).prop('hidden', (y === '' || +y === INV_MULAI_TAHUN) && +this.value < INV_MULAI_BULAN).prop('disabled', (y === '' || +y === INV_MULAI_TAHUN) && +this.value < INV_MULAI_BULAN); });
        if ($b.val() !== '' && $b.find('option:selected').prop('disabled')) { $b.val(''); }
    }
    syncInvBulan();
    $('#f-inv-tahun').on('change', syncInvBulan);
    $('#f-company, #f-fu, #f-plan-bulan, #f-plan-tahun, #f-close-tahun, #f-inv-tahun, #f-inv-bulan').on('change', function () { load(); });
    $('#f-customer').on('change', function () { load(); }); // nama harus persis: muat ulang saat pilihan dipastikan, bukan tiap ketikan
    $('#fu-search').on('input', function () { var v = this.value; clearTimeout(window.__fuS); window.__fuS = setTimeout(function () { table.search(v).draw(); }, 400); });
    $('#btn-export').on('click', function () { window.location = BASE + '/export?' + $.param($.extend(params(), {cari: table.search()})); });
    $('#fu-table tbody').on('click', '[data-act=update]', function () { openForm($(this).data('id')); });
    $('#fu-table tbody').on('click', '[data-act=close]', function () { openClose(table.row($(this).closest('tr')).data()); });
    $('#btn-do-close').on('click', function () {
        var why = $.trim($('#fu-close-ket').val());
        if (!why) { $('#fu-close-err').text('Mohon isi keterangan close invoice.'); return; }
        var $b = $(this).prop('disabled', true);
        $.post(BASE + '/close', {id: closeRow.id, reason: why}).done(function (r) {
            $('#fu-close-modal').modal('hide'); say(r.msg || 'Invoice di-close.', 'success'); load(true);
        }).fail(function (x) { $('#fu-close-err').text((x.responseJSON || {}).msg || 'Gagal close invoice.'); }).always(function () { $b.prop('disabled', false); });
    });
    // form bertahap: bagian berikutnya muncul setelah tanggal sebelumnya terisi
    $('#fu-modal-body').on('change', '[name=tgl_kirim], [name=tgl_terima]', function () {
        var data = $('#fu-form').serializeArray(), m = {}, keep = this.name; data.forEach(function (x) { m[x.name] = x.value; });
        cur.row = $.extend(cur.row, m); buildForm(cur);
        $('#fu-form [name="' + keep + '"]').focus(); // form dibangun ulang: kembalikan fokus keyboard
    });
    $('#fu-modal-body').on('click', '.fu-today', function () { $('[name="' + $(this).data('name') + '"]').val(TODAY); });
    $('#fu-modal-body').on('click', '[data-fuopen]', function () {
        var $b = $(this), open = $b.attr('aria-expanded') !== 'true';
        $b.attr('aria-expanded', open).next('.fubody').prop('hidden', !open);
        $b.find('.fa').first().attr('class', 'fa fa-' + (open ? 'caret-down' : 'caret-right'));
    });
    $('#fu-modal-body').on('click', '#btn-add-fu', function () {
        var ke = $(this).data('ke'), i = ke - 5, n = function (k) { return 'extra_fu[' + i + '][' + k + ']'; };
        $(this).closest('.funext').before('<div class="fuact"><div class="fuact-t">FU ' + ke + '</div><input type="hidden" name="' + n('ke') + '" value="' + ke + '">' +
            '<div class="frow"><label>Jadwal follow up' + inp(n('plan'), '', 'date') + '</label><label>Tanggal dilakukan' + inp(n('aktual'), '', 'date') + '</label>' +
            '<label>Keterangan / jawaban customer' + inp(n('ket'), '', 'text') + '</label></div></div>');
        $(this).closest('.funext').remove();
    });
    $('#fu-modal-body').on('change', '[name=kirim_via]', function () {
        var v = $(this).val();
        $('#resi-req').prop('hidden', v !== 'JNE'); $('#ket-kirim-wrap').prop('hidden', !v);
        $('#ket-kirim-lbl').text(v === 'Driver' ? 'Keterangan Driver (nama driver, dll)' : 'Keterangan JNE (layanan, dll)');
    });
    $('#fu-modal-body').on('change', '#bukti-file', function () {
        var fl = this.files[0];
        if (!fl) { picked = null; return; }
        if (!/\.(pdf|jpe?g|png)$/i.test(fl.name)) { mfail('Bukti terima harus PDF, JPG, atau PNG.'); this.value = ''; picked = null; return; }
        if (fl.size > 5 * 1024 * 1024) { mfail('Ukuran bukti terima maksimal 5 MB.'); this.value = ''; picked = null; return; }
        $('#fu-modal-msg').hide(); picked = fl; $(this).next('.hint').text('File dipilih: ' + fl.name);
    });
    $('#btn-save').on('click', function () {
        $('#fu-modal-msg').hide();
        // wajib: tanggal terima terisi harus punya file (yang sudah tersimpan atau yang baru dipilih); server memeriksa lagi
        if ($('[name=tgl_terima]').val() && !picked && !cur.row.bukti_terima) { mfail('Bukti terima (file) wajib diunggah.'); return; }
        // plan bayar mengunci seluruh data dan tidak bisa diubah: minta konfirmasi saat pertama kali diisi
        if ($('[name=plan_bayar]').val() && !cur.row.plan_bayar && !confirm('Plan bayar akan MENGUNCI seluruh data invoice ini dan tidak bisa diubah. Lanjut simpan?')) { return; }
        var $b = $(this).prop('disabled', true), fd = new FormData($('#fu-form')[0]), no = cur.row.no_invoice;
        fd.delete('bukti_terima');
        if (picked) { fd.append('bukti_terima', picked); }
        $.ajax({url: BASE + '/save', type: 'POST', data: fd, processData: false, contentType: false, dataType: 'json'})
            .done(function () { $('#fu-modal').modal('hide'); say('Data follow up ' + no + ' tersimpan.', 'success'); load(true); })
            .fail(function (x) { mfail((x.responseJSON || {}).msg || 'Gagal menyimpan. Coba lagi; bila terulang, hubungi admin.'); }).always(function () { $b.prop('disabled', false); });
    });
    // Riwayat tampil di dalam modal sebagai tabel (isi data_json diringkas), bukan alert().
    function ringkas(j) {
        var o; try { o = JSON.parse(j); } catch (e) { return esc(j); }
        if (!o || typeof o !== 'object') { return esc(j); }
        return $.map(o, function (v, k) { return (v === null || v === '' || typeof v === 'object') ? null : esc(k) + ': ' + esc(v); }).join(' · ');
    }
    $('#btn-history').on('click', function () {
        $.getJSON(BASE + '/history/' + encodeURIComponent(cur.row.id)).done(function (r) {
            if (!r.rows.length) { $('#fu-log').html('<p class="hint" style="margin-top:10px">Belum ada riwayat.</p>'); return; }
            $('#fu-log').html('<div class="table-responsive" style="margin-top:10px"><table class="fu-tbl" style="min-width:0"><thead><tr><th>Waktu</th><th>User</th><th>Aksi</th><th>Isi</th></tr></thead><tbody>' +
                r.rows.map(function (x) { return '<tr><td class="no">' + esc(x.created_date) + '</td><td>' + esc(x.created_by) + '</td><td>' + esc(x.aksi) + '</td><td>' + ringkas(x.data_json) + '</td></tr>'; }).join('') + '</tbody></table></div>');
        }).fail(function () { mfail('Riwayat perubahan gagal dimuat. Coba lagi.'); });
    });
    initTable(); applyTab();
})();
</script>
