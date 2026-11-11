<?php $base = site_url('follow_up_piutang/plan_bayar'); ?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<style>
    /* Tampilan mengikuti specs/docs/review-monitoring-piutang/mockup; warna pill memakai varian lebih gelap agar teks putih lolos kontras 4.5:1. */
    .pb-page { --line: #dfe3e8; --row: #f7f9fb; --muted: #5b6670; --blue: #1d5f93; --green: #067647; --orange: #b54708; --red: #b42318; }
    .pb-page :focus-visible { outline: 2px solid #1d4ed8; outline-offset: 2px; }
    .pb-panel { background: #fff; border-top: 3px solid #3f8bba; border-radius: 3px; padding: 14px; margin-bottom: 16px; box-shadow: 0 1px 1px rgba(0, 0, 0, .08); }
    .pb-panel h2 { font-size: 16px; margin: 0 0 10px; font-weight: 600; }
    .pb-filters { display: flex; flex-wrap: wrap; gap: 8px; align-items: flex-end; }
    .pb-filters .sp { flex: 1; }
    .pb-page .fl { display: flex; flex-direction: column; gap: 3px; font-size: 12px; color: var(--muted); font-weight: 400; margin: 0; }
    .pb-page .fl select { min-width: 170px; width: auto; background: #fff; border: 1px solid #c8d0d8; border-radius: 3px; padding: 5px 7px; font-size: 13px; color: #333a40; }
    .pb-btn { border: 0; border-radius: 3px; padding: 6px 12px; cursor: pointer; color: #fff; background: var(--green); font-weight: 600; font-size: 13px; }
    @media (pointer: coarse) { .pb-btn, .pb-page .fl select { min-height: 44px; } }
    .linkbtn { background: none; border: 0; padding: 0; color: var(--blue); cursor: pointer; font-size: 13px; font-weight: 600; }
    .pbcards { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-bottom: 16px; }
    @media (max-width: 900px) { .pbcards { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    @media (max-width: 560px) { .pbcards { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    .pbcard { background: #fff; border: 1px solid var(--line); border-top: 4px solid var(--line); border-radius: 3px; padding: 10px 12px; text-align: left; cursor: pointer; display: flex; flex-direction: column; gap: 3px; min-width: 0; font: inherit; color: #333a40; }
    .pbcard:hover { box-shadow: 0 2px 8px rgba(0, 0, 0, .08); }
    .pbcard.pball { grid-column: 1 / -1; flex-direction: row; flex-wrap: wrap; align-items: center; gap: 8px 24px; border-top-color: var(--blue); }
    .pbcard.pball > span { display: flex; flex-direction: column; gap: 2px; min-width: 220px; }
    .pbcard.pball .cv { color: var(--blue); font-size: 22px; }
    .pbcard.has { border-top-color: var(--green); } .pbcard.lewat { border-top-color: var(--red); }
    .pbcard.now { background: rgba(14, 116, 144, .06); } .pbcard.on { box-shadow: 0 0 0 2px #0e7490; }
    .pbcard .ct { font-size: 12px; font-weight: 700; letter-spacing: .4px; text-transform: uppercase; color: var(--muted); display: flex; justify-content: space-between; gap: 6px; align-items: center; }
    .pbcard .cv { display: block; font-size: 19px; font-weight: 700; font-variant-numeric: tabular-nums; word-break: break-word; }
    .pbcard.has .cv { color: var(--green); } .pbcard.lewat .cv { color: var(--red); }
    .pbcard .cm { display: block; font-size: 12.5px; color: var(--muted); }
    .pbcard .cm b { color: #333a40; }
    .pbcard .rea { display: block; font-size: 12.5px; color: var(--blue); margin-top: 4px; padding-top: 4px; border-top: 1px dashed var(--line); }
    .pbcard .rea b { color: #333a40; }
    .pbcard .cv.rea { border: 0; padding: 0; margin: 0; font-size: 22px; color: var(--blue); }
    .pbcard .lbl { display: block; font-size: 11px; text-transform: uppercase; letter-spacing: .3px; color: var(--muted); }
    .pb-pill { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 11.5px; font-weight: 700; color: #fff; white-space: nowrap; text-transform: none; letter-spacing: 0; }
    .pb-pill.lancar, .pb-pill.L { background: var(--green); } .pb-pill.potensi { background: var(--orange); } .pb-pill.macet, .pb-pill.M { background: var(--red); }
    .pb-pill.C { background: var(--blue); } .pb-pill.close { background: #4b5563; }
    .umur { font-weight: 700; font-variant-numeric: tabular-nums; }
    .umur.lancar { color: var(--green); } .umur.potensi { color: var(--orange); } .umur.macet { color: var(--red); }
    table.pb-tbl { border-collapse: collapse; width: 100%; min-width: 760px; }
    .pb-tbl th, .pb-tbl td { padding: 8px 9px; border-bottom: 1px solid var(--line); text-align: left; vertical-align: top; }
    .pb-tbl th { font-weight: 700; font-size: 13px; white-space: nowrap; border-top: 0; }
    .pb-tbl th.grp { text-align: center; background: var(--row); }
    .pb-tbl tbody tr:nth-child(odd) { background: var(--row); }
    .pb-tbl td.num, .pb-tbl th.num { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
    .pb-tbl .no { white-space: nowrap; font-weight: 600; }
    .pb-tbl .sub { display: block; font-size: 12px; color: var(--muted); font-weight: 400; }
    .pb-tbl tfoot td { font-weight: 700; background: var(--row); border-top: 2px solid var(--line); }
    .pb-empty { padding: 26px; text-align: center; color: var(--muted); }
    .pb-hint { font-size: 12.5px; color: var(--muted); margin: -4px 0 6px; }
    .pb-late { color: var(--red); } .pb-ok { color: var(--green); }
    .pb-page .dataTables_filter { text-align: right; margin-bottom: 8px; font-size: 12px; color: var(--muted); }
    .pb-page .dataTables_filter input { margin-left: 6px; width: 260px; background: #fff; border: 1px solid #c8d0d8; border-radius: 3px; padding: 5px 7px; font-size: 13px; color: #333a40; }
    .pb-foot { display: flex; flex-wrap: wrap; gap: 8px 16px; align-items: center; justify-content: space-between; margin-top: 10px; font-size: 13px; }
    .pb-foot .dataTables_length select { width: auto; display: inline-block; }
    .pb-msg { background: rgba(180, 35, 24, .08); border: 1px solid rgba(180, 35, 24, .45); border-radius: 3px; padding: 8px 12px; margin-bottom: 12px; font-size: 13px; }
</style>

<div class="pb-page">
    <div class="pb-panel">
        <div class="pb-filters">
            <label class="fl">Tahun Plan Bayar<select id="f-tahun"><?php foreach ($years as $y) echo '<option' . ($y == date('Y') ? ' selected' : '') . '>' . $y . '</option>'; ?></select></label>
            <label class="fl">Company<select id="f-company"><option value="">Semua Company</option></select></label>
            <div class="sp"></div>
            <button type="button" class="pb-btn" id="btn-export"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export Excel <span id="exp-label"></span></button>
        </div>
    </div>
    <div id="pb-msg" class="pb-msg" role="alert" style="display:none"></div>
    <div class="pbcards" id="pb-cards"><div class="pb-empty" style="grid-column:1/-1">Memuat ringkasan plan bayar...</div></div>

    <div class="pb-panel"><h2 id="pb-title-plan">Plan bayar</h2>
        <div class="table-responsive"><table id="pb-plan-tbl" class="pb-tbl">
            <thead><tr><th>No</th><th>Plan Bayar</th><th>Invoice</th><th>Customer</th><th>Company</th><th class="num">Nilai Invoice</th><th class="num">Nilai Plan<span class="sub">sisa saat plan dibuat</span></th><th class="num">Sisa Sekarang</th><th>Umur</th></tr></thead>
            <tbody></tbody>
            <tfoot><tr><td colspan="5" id="pp-label">Total</td><td class="num" id="pp-nilai"></td><td class="num" id="pp-plan"></td><td class="num" id="pp-saldo"></td><td></td></tr></tfoot>
        </table></div>
    </div>
    <div class="pb-panel"><h2 id="pb-title-real">Realisasi bayar</h2>
        <p class="pb-hint">Setiap baris = satu pembayaran customer. Bandingkan <b>janji bayar</b> (plan) dengan <b>kapan benar-benar dibayar</b>, lalu lihat apakah invoicenya sudah lunas.</p>
        <p id="pr-sum" style="margin:0 0 10px"></p>
        <div class="table-responsive"><table id="pb-real-tbl" class="pb-tbl">
            <thead><tr><th rowspan="2">No</th><th rowspan="2">Invoice</th><th rowspan="2">Customer / Project</th><th colspan="3" class="grp">Janji vs Bayar</th><th rowspan="2" class="num">Jumlah Dibayar</th><th colspan="2" class="grp">Posisi Invoice Sekarang</th></tr>
                <tr><th>Janji Bayar<span class="sub">plan bayar</span></th><th>Tanggal Bayar<span class="sub">no penerimaan</span></th><th>Ketepatan</th><th class="num">Sisa Tagihan<span class="sub">dari nilai invoice</span></th><th>Status</th></tr></thead>
            <tbody></tbody>
            <tfoot><tr><td colspan="6" id="pr-label">Total</td><td class="num" id="pr-nominal"></td><td colspan="2"></td></tr></tfoot>
        </table></div>
    </div>
</div>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script>
(function () {
    var BASE = '<?= $base ?>', bulan = 'all', D = null, pt = null, rt = null, MULAI_TAHUN = <?= (int) $mulai_tahun ?>, MULAI_BULAN = <?= (int) $mulai_bulan ?>;
    var NAMA = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    var KAT = {lancar: 'Lancar', potensi: 'Potensi', macet: 'Macet'};
    function esc(s) { return $('<div>').text(s == null ? '' : String(s)).html(); }
    function rp(c) { return Number(c / 100).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}); }
    function nf(v) { return rp(Number(v || 0) * 100); }
    function dmy(d) { return d ? String(d).substr(8, 2) + '-' + String(d).substr(5, 2) + '-' + String(d).substr(0, 4) : ''; }
    function q() { return {tahun: $('#f-tahun').val(), company: $('#f-company').val()}; }
    function umurCell(x) { return x.kategori && x.umur != null ? '<span class="umur ' + x.kategori + '">' + x.umur + ' hari</span> <span class="pb-pill ' + x.kategori + '">' + (KAT[x.kategori] || x.kategori) + '</span>' : '<span class="sub">–</span>'; }

    // Bulan sebelum batas mulai (Jan-Sep 2026) tidak ada datanya: tidak ditampilkan.
    function bulanAda(m) { return !(+D.year === MULAI_TAHUN && m < MULAI_BULAN) && +D.year >= MULAI_TAHUN; }
    function rentang() { return +D.year === MULAI_TAHUN ? NAMA[MULAI_BULAN - 1] + ' – Desember' : 'Januari – Desember'; }

    function cards() {
        var tp = {c: 0, n: 0, rc: 0, rn: 0}, h = '';
        D.summary.forEach(function (m) { tp.c += m.plan_count; tp.n += m.plan_cents; tp.rc += m.real_count; tp.rn += m.real_cents; });
        h += '<button type="button" class="pbcard pball' + (bulan === 'all' ? ' on' : '') + '" data-b="all" aria-pressed="' + (bulan === 'all') + '">' +
            '<span><span class="ct">All · ' + esc(D.year) + '</span><span class="lbl">Plan bayar</span><span class="cv">' + rp(tp.n) + '</span><span class="cm"><b>' + tp.c + '</b> invoice (' + rentang() + ')</span></span>' +
            '<span><span class="ct">&nbsp;</span><span class="lbl">Realisasi bayar</span><span class="cv rea">' + rp(tp.rn) + '</span><span class="cm"><b>' + tp.rc + '</b> penerimaan</span></span></button>';
        D.summary.forEach(function (m) {
            if (!bulanAda(m.bulan)) { return; }
            var cls = m.plan_count ? (m.lewat ? 'lewat' : 'has') : '';
            h += '<button type="button" class="pbcard ' + cls + (m.current ? ' now' : '') + (bulan === m.bulan ? ' on' : '') + '" data-b="' + m.bulan + '" aria-pressed="' + (bulan === m.bulan) + '">' +
                '<span class="ct">' + esc(m.nama) + (m.current ? '<span class="pb-pill C">Bulan ini</span>' : (m.lewat ? '<span class="pb-pill M">Lewat</span>' : '')) + '</span>' +
                '<span class="cv">' + rp(m.plan_cents) + '</span><span class="cm"><b>' + m.plan_count + '</b> invoice</span>' +
                '<span class="rea">Realisasi <b>' + rp(m.real_cents) + '</b>' + (m.real_count ? ' · ' + m.real_count + ' penerimaan' : '') + '</span></button>';
        });
        $('#pb-cards').html(h);
    }

    // Judul dan label export mengikuti kartu yang dipilih; isi tabel diambil serverside (plan_data / real_data).
    function titles() {
        var t = bulan === 'all' ? D.year + ' (' + rentang() + ')' : NAMA[bulan - 1] + ' ' + D.year;
        $('#exp-label').text('(' + (bulan === 'all' ? 'All' : NAMA[bulan - 1]) + ')');
        $('#pb-title-plan').html('Plan bayar ' + esc(t) + (bulan === 'all' ? '' : ' <button type="button" class="linkbtn" id="pb-show-all">tampilkan All</button>'));
        $('#pb-title-real').text('Realisasi bayar ' + t);
    }
    function reloadTables() { pt.ajax.reload(null, true); rt.ajax.reload(null, true); }
    function tparams(d) { return $.extend(d, q(), {bulan: bulan === 'all' ? '' : bulan}); }
    function rowNo(_, t, x, m) { return m.row + m.settings._iDisplayStart + 1; }
    function pill(cls, text) { return '<span class="pb-pill ' + cls + '">' + text + '</span>'; }

    function initTables() {
        $.fn.dataTable.ext.errMode = 'none'; // error ajax tampil sebagai banner, bukan alert bawaan
        var common = {serverSide: true, processing: true, ordering: false, autoWidth: false, pageLength: 25, lengthMenu: [10, 25, 50, 100], searchDelay: 400,
            dom: 'frt<"pb-foot"<"pb-len"l><"pb-info"i>p>'};
        var lang = function (empty, zero) {
            return {search: 'Cari:', searchPlaceholder: 'invoice, customer, tanggal, ...', lengthMenu: 'Tampilkan _MENU_ baris', processing: 'Memuat...',
                info: 'Menampilkan _START_-_END_ dari _TOTAL_', infoEmpty: 'Tidak ada data', infoFiltered: '(dari _MAX_ pada periode ini)',
                emptyTable: empty, zeroRecords: zero, paginate: {previous: '&laquo;', next: '&raquo;'}};
        };
        pt = $('#pb-plan-tbl').DataTable($.extend({}, common, {
            ajax: {url: BASE + '/plan_data', type: 'GET', data: tparams},
            language: lang('Belum ada plan bayar pada periode ini. Plan bayar muncul setelah invoice di menu Follow Up Piutang diberi tanggal rencana bayar.', 'Tidak ada plan bayar yang cocok dengan kata cari. Hapus kata cari atau pilih bulan lain.'),
            columns: [
                {data: null, render: rowNo},
                {data: null, render: function (_, t, x) { return '<b>' + dmy(x.plan_bayar) + '</b>' + (x.ket_plan_bayar ? '<span class="sub">' + esc(x.ket_plan_bayar) + '</span>' : ''); }},
                {data: null, className: 'no', render: function (_, t, x) { return esc(x.no_invoice) + '<span class="sub">' + dmy(x.tgl_invoice) + '</span>'; }},
                {data: null, render: function (_, t, x) { return esc(x.customer) + '<span class="sub">' + esc(x.project) + '</span>'; }},
                {data: null, render: function (_, t, x) { return esc(x.company_name); }},
                {data: null, className: 'num', render: function (_, t, x) { return nf(x.nilai); }},
                {data: null, className: 'num', render: function (_, t, x) { return '<b>' + nf(x.nilai_plan) + '</b>'; }},
                {data: null, className: 'num', render: function (_, t, x) {
                    return nf(x.saldo) + (x.sts_close === '1' ? ' ' + pill('close', 'Close') : (+x.saldo === 0 ? ' ' + pill('L', 'Lunas') : '')); }},
                {data: null, render: function (_, t, x) { return umurCell(x); }}
            ]
        }));
        rt = $('#pb-real-tbl').DataTable($.extend({}, common, {
            ajax: {url: BASE + '/real_data', type: 'GET', data: tparams},
            language: lang('Belum ada pembayaran pada periode ini.', 'Tidak ada pembayaran yang cocok dengan kata cari. Hapus kata cari atau pilih bulan lain.'),
            columns: [
                {data: null, render: rowNo},
                {data: null, className: 'no', render: function (_, t, x) { return esc(x.no_invoice) + '<span class="sub">' + dmy(x.tgl_invoice) + '</span>'; }},
                {data: null, render: function (_, t, x) { return esc(x.customer) + '<span class="sub">' + esc(x.project) + ' · ' + esc(x.company_name) + '</span>'; }},
                {data: null, render: function (_, t, x) { return x.plan_bayar ? dmy(x.plan_bayar) : '<span class="sub">–</span>'; }},
                {data: null, render: function (_, t, x) { return '<b>' + dmy(x.tgl_bayar) + '</b><span class="sub">' + esc(x.no_penerimaan) + '</span>'; }},
                {data: null, render: function (_, t, x) {
                    var d = x.ketepatan_days;
                    return d === null || d === undefined ? '<span class="sub">–</span>' : (+d === 0 ? pill('L', 'Sesuai janji') : (+d < 0 ? pill('C', 'Lebih cepat ' + (-d) + ' hari') : pill('M', 'Telat ' + d + ' hari'))); }},
                {data: null, className: 'num', render: function (_, t, x) { return '<b>' + nf(x.nominal) + '</b>'; }},
                {data: null, className: 'num', render: function (_, t, x) { return nf(x.saldo) + '<span class="sub">dari ' + nf(x.nilai) + '</span>'; }},
                {data: null, render: function (_, t, x) { return pill(x.posisi === 'Lunas & close' ? 'close' : (x.posisi === 'Lunas, belum close' ? 'L' : 'C'), esc(x.posisi)); }}
            ]
        }));
        // total dan ringkasan mengikuti hasil filter (bulan + cari), bukan hanya halaman yang tampil
        pt.on('xhr.dt', function (e, s, json) {
            if (!json || !json.agg) { return; }
            $('#pp-label').text('Total ' + json.recordsFiltered + ' invoice');
            $('#pp-nilai').text(nf(json.agg.nilai)); $('#pp-plan').text(nf(json.agg.nilai_plan)); $('#pp-saldo').text(nf(json.agg.saldo));
        });
        rt.on('xhr.dt', function (e, s, json) {
            if (!json || !json.agg) { return; }
            $('#pr-label').text('Total ' + json.recordsFiltered + ' pembayaran'); $('#pr-nominal').text(nf(json.agg.nominal));
            $('#pr-sum').html(json.recordsFiltered ? 'Total dibayar <b>' + nf(json.agg.nominal) + '</b> dari ' + json.recordsFiltered + ' pembayaran · <span class="pb-ok"><b>' + json.agg.tepat +
                '</b> tepat waktu / lebih cepat</span> · <span class="pb-late"><b>' + json.agg.telat + '</b> telat dari janji</span>' : '');
        });
        $.each([pt, rt], function (i, t) {
            t.on('error.dt', function () { $('#pb-msg').text('Tabel rincian gagal dimuat. Ganti tahun, company, atau bulan untuk mencoba lagi, atau muat ulang halaman.').show(); });
        });
    }

    function load() {
        $('#pb-msg').hide();
        $.getJSON(BASE + '/data', q()).done(function (r) {
            if (r.status !== 'success') { $('#pb-msg').text(r.msg || 'Data plan bayar tidak dapat dimuat.').show(); return; }
            if ($('#f-company option').length === 1) { r.companies.forEach(function (c) { $('#f-company').append($('<option>').val(c.id).text(c.nm_company)); }); }
            D = r; cards(); titles();
        }).fail(function (x) {
            $('#pb-cards').empty(); $('#pb-msg').text((x.responseJSON || {}).msg || 'Data plan bayar gagal dimuat. Ganti tahun atau company untuk mencoba lagi, atau muat ulang halaman.').show();
        });
    }
    $('#pb-cards').on('click', '.pbcard', function () {
        var b = $(this).data('b'); bulan = (b === 'all' || b === bulan) ? 'all' : b; cards(); titles(); reloadTables();
        $('#pb-cards [data-b="' + bulan + '"]').focus(); // kartu dirender ulang: kembalikan fokus keyboard
    });
    $('#pb-title-plan').on('click', '#pb-show-all', function () { bulan = 'all'; cards(); titles(); reloadTables(); });
    $('#f-tahun, #f-company').on('change', function () { bulan = 'all'; load(); reloadTables(); });
    $('#btn-export').on('click', function () { window.location = BASE + '/export?' + $.param($.extend(q(), {bulan: bulan === 'all' ? '' : bulan, cari_plan: pt.search(), cari_real: rt.search()})); });
    initTables(); load();
})();
</script>
