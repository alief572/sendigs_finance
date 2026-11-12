<?php $base = site_url('monitoring_piutang'); ?>
<style>
    .mpd { font-size: 13px; } .mpd .k { display: block; color: #777; font-size: 12px; font-weight: normal; }
    .mpd-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 10px 24px; margin-bottom: 12px; }
    .mpd-kpi { display: flex; flex-wrap: wrap; gap: 28px; margin: 4px 0 14px; padding: 10px 14px; background: #f7f7f7; border: 1px solid #eee; border-radius: 3px; }
    .mpd-kpi b { display: block; font-size: 17px; font-variant-numeric: tabular-nums; }
    .mpd-h { font-size: 14px; font-weight: bold; margin: 14px 0 8px; padding-top: 10px; border-top: 1px solid #eee; }
    .mpd-tbl { width: 100%; border-collapse: collapse; } .mpd-tbl th, .mpd-tbl td { padding: 5px 8px; border-bottom: 1px solid #eee; text-align: left; vertical-align: top; }
    .mpd-tbl th { white-space: nowrap; font-weight: bold; } .mpd-r { text-align: right !important; white-space: nowrap; font-variant-numeric: tabular-nums; }
    .mpd-red { color: #dd4b39; } .mpd-muted { color: #777; } .mpd-umur { font-variant-numeric: tabular-nums; }
    .mpd-lancar { color: #00a65a; } .mpd-potensi { color: #f39c12; } .mpd-macet { color: #dd4b39; }
    .mp-sort { cursor: pointer; white-space: nowrap; } .mp-sort:hover { text-decoration: underline; }
    .mp-card { border: 2px solid #ddd; border-radius: 3px; padding: 10px 12px; margin-bottom: 12px; cursor: pointer; background: #fff; }
    .mp-card.active { box-shadow: 0 0 0 2px #3c8dbc; }
    .mp-card h5 { margin: 0 0 4px; font-weight: bold; }
    .mp-card .amt { font-size: 18px; font-weight: bold; }
    .mp-card small { color: #666; display: block; }
    .mp-lancar { border-color: #00a65a; } .mp-potensi { border-color: #f39c12; } .mp-macet { border-color: #dd4b39; } .mp-belum { border-color: #999; background: #f7f7f7; cursor: default; }
    .lbl-lancar { background: #00a65a; } .lbl-potensi { background: #f39c12; } .lbl-macet { background: #dd4b39; }
    .mp-num { text-align: right; white-space: nowrap; }
    #mp-grand { font-size: 15px; margin-bottom: 10px; }
    .mp-table th { text-align: center; vertical-align: middle; white-space: nowrap; }
</style>
<div class="box box-primary"><div class="box-body">
    <div class="row" style="margin-bottom:10px">
        <div class="col-sm-3"><select id="f-company" class="form-control"><option value="">Semua Company</option></select></div>
        <div class="col-sm-3"><input type="text" id="f-customer" class="form-control" list="dl-customer" autocomplete="off" placeholder="Customer (pilih dari daftar)" aria-label="Filter customer"><datalist id="dl-customer"></datalist></div>
        <div class="col-sm-4"><input type="text" id="f-cari" class="form-control" placeholder="Cari no invoice, customer, tanggal, nominal..."></div>
        <div class="col-sm-2 text-right"><button class="btn btn-default btn-sm" id="btn-clear">Reset filter</button></div>
    </div>
    <div id="mp-err" class="alert alert-danger" role="alert" style="display:none"></div>
    <div id="mp-grand"></div>
    <div class="row">
        <div class="col-sm-3"><div class="mp-card" data-kat="all" id="card-all" role="button" tabindex="0"></div></div>
        <div class="col-sm-3"><div class="mp-card mp-lancar" data-kat="lancar" id="card-lancar" role="button" tabindex="0"></div></div>
        <div class="col-sm-3"><div class="mp-card mp-potensi" data-kat="potensi" id="card-potensi" role="button" tabindex="0"></div></div>
        <div class="col-sm-3"><div class="mp-card mp-macet" data-kat="macet" id="card-macet" role="button" tabindex="0"></div></div>
    </div>
    <div class="row"><div class="col-sm-12"><div class="mp-card mp-belum" id="card-belum"></div></div></div>

    <ul class="nav nav-tabs" id="mp-tabs">
        <li class="active"><a href="#" data-pane="list">List Piutang per Invoice</a></li>
        <li><a href="#" data-pane="rekap">Rekap per Customer</a></li>
    </ul>
    <div style="margin-top:10px" class="text-right"><button class="btn btn-success btn-sm" id="btn-export"><i class="fa fa-file-excel-o"></i> Download Excel <span id="exp-label"></span></button></div>
    <div id="pane-list" class="table-responsive"><table class="table table-bordered table-striped mp-table">
        <thead id="mp-list-head"><tr><th class="mp-sort" data-sort="no_invoice" title="Urutkan">Invoice</th><th>Diterima</th><th class="mp-sort" data-sort="customer" title="Urutkan">Customer &amp; Project</th><th>Company</th><th class="mp-sort" data-sort="nilai" title="Urutkan">Nilai</th><th class="mp-sort" data-sort="terbayar" title="Urutkan">Terbayar</th><th class="mp-sort" data-sort="saldo" title="Urutkan">Sisa</th><th class="mp-sort" data-sort="umur" title="Urutkan">Umur</th><th>Kategori</th><th>Follow Up</th><th>Aksi</th></tr></thead>
        <tbody id="mp-body"></tbody></table></div>
    <div id="pane-rekap" class="table-responsive" style="display:none"><table class="table table-bordered table-striped mp-table">
        <thead><tr><th>Customer</th><th>Invoice</th><th>Lancar</th><th>Potensi Macet</th><th>Macet</th><th>Total</th></tr></thead>
        <tbody id="rk-body"></tbody></table></div>
</div></div>

<div class="modal fade" id="mp-modal" tabindex="-1" role="dialog" aria-labelledby="mp-modal-title"><div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title" id="mp-modal-title"></h4></div>
    <div class="modal-body" id="mp-modal-body"></div>
    <div class="modal-footer"><a class="btn btn-default pull-left" id="mp-open-fu" href="#">Buka di Follow Up Piutang</a><a class="btn btn-warning pull-left" id="mp-print-surat" target="_blank" href="#" style="display:none;margin-left:5px">Print Surat Konfirmasi Tagihan</a><button class="btn btn-default" data-dismiss="modal">Tutup</button></div>
</div></div></div>

<script>
(function () {
    var BASE = '<?= $base ?>', FU = '<?= site_url('follow_up_piutang') ?>', kat = 'all', rows = [], sort = {key: null, dir: 1};
    var NAMA = {all: 'Total Piutang', lancar: 'Lancar', potensi: 'Potensi Macet', macet: 'Macet'}, KET = {lancar: 'Umur 0 - 60 hari', potensi: 'Umur 61 - 90 hari', macet: 'Umur > 90 hari', all: 'Sudah diterima customer'};
    function esc(s) { return $('<div>').text(s == null ? '' : String(s)).html(); }
    function rp(c) { return Number(c / 100).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}); }
    function nf(v) { return rp(Number(v || 0) * 100); }
    function dmy(d) { return d ? String(d).substr(8, 2) + '-' + String(d).substr(5, 2) + '-' + String(d).substr(0, 4) : ''; }
    function params() { return {company: $('#f-company').val(), customer: $('#f-customer').val(), cari: $('#f-cari').val(), kategori: kat}; }

    function card(id, key, s) {
        var x = s[key], extra = key === 'all' ? '' : '<small>' + x.pct + '% dari total</small>';
        $('#card-' + id).html('<h5>' + NAMA[key] + '</h5><div class="amt">' + rp(x.cents) + '</div><small>' + x.count + ' invoice · ' + KET[key] + '</small>' + extra +
            '<small>Umur tertua: ' + (x.oldest == null ? '-' : x.oldest + ' hari') + '</small>' +
            '<button class="btn btn-success btn-xs" data-dl="' + key + '" style="margin-top:6px"><i class="fa fa-file-excel-o"></i> Download Excel</button>')
            .toggleClass('active', kat === key);
    }

    function load() {
        $('#exp-label').text('(' + (kat === 'all' ? 'Semua' : NAMA[kat]) + ')');
        $('#mp-err').hide(); $('#mp-body, #rk-body').css('opacity', .5);
        $.getJSON(BASE + '/data', params()).done(function (r) {
            if (r.status !== 'success') { $('#mp-err').text(r.msg || 'Gagal memuat data piutang.').show(); return; }
            if ($('#f-company option').length === 1) { r.companies.forEach(function (c) { $('#f-company').append($('<option>').val(c.id).text(c.nm_company)); }); }
            var s = r.summary;
            $('#mp-grand').html('Total keseluruhan sisa piutang: <b>' + rp(s.grand_cents) + '</b> <small class="text-muted">(sudah diterima + belum diterima, untuk dicocokkan dengan buku besar)</small>');
            card('all', 'all', {all: s.total});
            ['lancar', 'potensi', 'macet'].forEach(function (k) { card(k, k, s); });
            $('#card-belum').html('<h5>Belum Diterima Customer</h5><div class="amt">' + rp(s.belum.cents) + '</div><small>' + s.belum.count + ' invoice belum ada tanggal terima · <a href="' + FU + '">buka Follow Up Piutang</a></small>');
            if (!$('#dl-customer option').length) { r.rekap.forEach(function (x) { $('#dl-customer').append($('<option>').val(x.customer)); }); }
            rows = r.rows; renderRows();
            var k = '';
            r.rekap.forEach(function (x) {
                k += '<tr><td><a href="#" data-cust="' + esc(x.customer) + '">' + esc(x.customer) + '</a></td><td class="mp-num">' + x.count + '</td><td class="mp-num">' + rp(x.lancar) + '</td><td class="mp-num">' + rp(x.potensi) + '</td><td class="mp-num">' + rp(x.macet) + '</td><td class="mp-num"><b>' + rp(x.total) + '</b></td></tr>';
            });
            $('#rk-body').html(k || '<tr><td colspan="6" class="text-center">Tidak ada piutang.</td></tr>');
        }).fail(function (x) {
            $('#mp-err').text((x.responseJSON || {}).msg || 'Gagal memuat data piutang. Periksa koneksi lalu coba lagi.').show();
        }).always(function () { $('#mp-body, #rk-body').css('opacity', 1); });
    }

    function renderRows() {
        var h = '', t = {n: 0, b: 0, s: 0}, list = rows.slice();
        if (sort.key) {
            list.sort(function (a, b) {
                var va = a[sort.key], vb = b[sort.key];
                if (sort.key === 'no_invoice' || sort.key === 'customer') { return String(va).localeCompare(String(vb)) * sort.dir; }
                return (Number(va) - Number(vb)) * sort.dir;
            });
        }
        $('#mp-list-head th[data-sort]').attr('aria-sort', 'none').each(function () {
            if ($(this).data('sort') === sort.key) { $(this).attr('aria-sort', sort.dir > 0 ? 'ascending' : 'descending'); }
        });
        list.forEach(function (x) {
                t.n += +x.nilai; t.b += +x.terbayar; t.s += +x.saldo;
                h += '<tr><td><a href="#" data-id="' + esc(x.id) + '"><b>' + esc(x.no_invoice) + '</b></a><br><small>' + dmy(x.tgl_invoice) + '</small></td><td>' + dmy(x.tgl_terima) + '</td>' +
                    '<td>' + esc(x.customer) + '<br><small>' + esc(x.project) + '</small></td><td>' + esc(x.company_name) + '</td><td class="mp-num">' + nf(x.nilai) + '</td><td class="mp-num">' + nf(x.terbayar) +
                    '</td><td class="mp-num">' + nf(x.saldo) + '</td><td class="mp-num">' + x.umur + ' hari</td><td><span class="label lbl-' + x.kategori + '">' + NAMA[x.kategori] + '</span></td><td' +
                    (x.fu_late ? ' style="color:#dd4b39;font-weight:bold"' : '') + '>' + esc(x.fu_text) + '</td><td>' +
                    '<a href="#" class="btn btn-info btn-xs" data-id="' + esc(x.id) + '">Detail</a> ' +
                    (x.can_surat ? '<a class="btn btn-warning btn-xs" target="_blank" href="' + BASE + '/surat/' + encodeURIComponent(x.id) + '">Surat Konfirmasi</a>' : '') + '</td></tr>';
        });
        if (list.length) { h += '<tr><th colspan="4">TOTAL</th><th class="mp-num">' + nf(t.n) + '</th><th class="mp-num">' + nf(t.b) + '</th><th class="mp-num">' + nf(t.s) + '</th><th colspan="4"></th></tr>'; }
        $('#mp-body').html(h || '<tr><td colspan="11" class="text-center">Tidak ada piutang.</td></tr>');
    }

    $('.mp-card[data-kat]').on('click', function (e) {
        if ($(e.target).closest('[data-dl]').length) { return; }
        var k = $(this).data('kat'); kat = (kat === k && k !== 'all') ? 'all' : k; load();
    });
    $('.row').on('click', '[data-dl]', function () { window.location = BASE + '/export?' + $.param($.extend(params(), {kategori: $(this).data('dl')})); });
    $('#btn-export').on('click', function () { window.location = BASE + '/export?' + $.param(params()); });
    $('#f-company').on('change', load);
    $('.mp-card[data-kat]').on('keydown', function (e) { if ((e.key === 'Enter' || e.key === ' ') && e.target === this) { e.preventDefault(); $(this).click(); } });
    $('#f-customer').on('change', function () { clearTimeout(window.__mpT); window.__mpT = setTimeout(load, 350); });
    $('#mp-list-head').on('click', 'th[data-sort]', function () {
        var k = $(this).data('sort'); sort = sort.key === k ? {key: k, dir: -sort.dir} : {key: k, dir: 1}; renderRows();
    });
    $('#f-cari').on('input', function () { clearTimeout(window.__mpT); window.__mpT = setTimeout(load, 350); });
    $('#btn-clear').on('click', function () { $('#f-company, #f-customer, #f-cari').val(''); kat = 'all'; load(); });
    $('#mp-tabs').on('click', 'a', function (e) { e.preventDefault(); $('#mp-tabs li').removeClass('active'); $(this).parent().addClass('active'); var p = $(this).data('pane'); $('#pane-list').toggle(p === 'list'); $('#pane-rekap').toggle(p === 'rekap'); });
    $('#rk-body').on('click', '[data-cust]', function (e) {
        e.preventDefault(); $('#f-customer').val($(this).data('cust')); $('#mp-tabs a[data-pane=list]').click(); load();
    });
    $('#mp-body').on('click', '[data-id]', function (e) {
        e.preventDefault();
        $.getJSON(BASE + '/detail/' + encodeURIComponent($(this).data('id'))).done(function (d) {
            var r = d.row, today = '<?= date('Y-m-d') ?>';
            function dv(v) { return v ? dmy(v) : '–'; }
            function kv(k, v) { return '<div><span class="k">' + k + '</span>' + v + '</div>'; }
            var umur = r.kategori ? '<b class="mpd-umur mpd-' + r.kategori + '">' + r.umur + ' hari</b> <span class="label lbl-' + r.kategori + '">' + NAMA[r.kategori] + '</span>' : '<span class="mpd-muted">Belum diterima customer</span>';
            var h = '<div class="mpd"><div class="mpd-grid">' +
                kv('Customer', '<b>' + esc(r.customer) + '</b>') + kv('Company', esc(r.company_name) || '–') + kv('Tanggal Invoice', dv(r.tgl_invoice)) +
                kv('Program / Project', esc(r.project) || '–') + kv('No SPK', esc(r.no_spk) || '–') + kv('Term of Payment', r.top_ke ? 'TOP ke-' + esc(r.top_ke) : '–') + kv('Umur Piutang', umur) + '</div>' +
                '<div class="mpd-kpi"><div>Nilai Invoice<b>' + nf(r.nilai) + '</b></div><div>Terbayar<b>' + nf(r.terbayar) + '</b></div><div>Sisa Piutang<b class="mpd-red">' + nf(r.saldo) + '</b></div></div>' +
                '<h5 class="mpd-h">Pengiriman &amp; Follow Up</h5><div class="mpd-grid">' +
                kv('Dikirim', dv(r.tgl_kirim) + (r.kirim_via ? ' via ' + esc(r.kirim_via) : '') + (r.ket_kirim ? '<span class="k">' + esc(r.ket_kirim) + '</span>' : '')) +
                kv('No Resi', esc(r.no_resi) || '–') +
                kv('Diterima customer', dv(r.tgl_terima) + (r.ket_terima ? '<span class="k">' + esc(r.ket_terima) + '</span>' : '') +
                    (r.bukti_terima ? [{path: r.bukti_terima, nama: r.bukti_terima_nama}] : []).concat(r.bukti_extra || []).map(function (b) {
                        return '<span class="k"><i class="fa fa-paperclip"></i> <a href="' + esc('<?= base_url() ?>' + b.path) + '" target="_blank" rel="noopener">Bukti terima: ' + esc(b.nama || 'file') + '</a></span>';
                    }).join('')) +
                kv('PIC penerima FU', r.fu_pic_nama ? esc(r.fu_pic_nama) + (r.fu_pic_telp ? ' · ' + esc(r.fu_pic_telp) : '') : '–') +
                kv('Plan bayar', dv(r.plan_bayar) + (r.ket_plan_bayar ? '<span class="k">' + esc(r.ket_plan_bayar) + '</span>' : '')) + '</div>';
            h += '<div class="table-responsive"><table class="mpd-tbl"><thead><tr><th></th><th>Plan</th><th>Aktual</th><th>Keterangan</th></tr></thead><tbody>';
            d.fu.forEach(function (x) {
                var akt = x.aktual ? '✓ ' + dmy(x.aktual) : (!x.plan ? '<span class="mpd-muted">Belum dijadwalkan</span>' : '<span class="' + (x.plan < today ? 'mpd-red' : 'mpd-muted') + '">Belum dilakukan</span>');
                h += '<tr><td><b>FU ' + x.ke + '</b>' + (x.ke > 4 ? '<span class="k">tambahan</span>' : '') + '</td><td>' + dv(x.plan) + '</td><td>' + akt + '</td><td>' + esc(x.ket) + '</td></tr>';
            });
            h += '</tbody></table></div><h5 class="mpd-h">Riwayat Penerimaan</h5>';
            h += d.receipts.length ? '<div class="table-responsive"><table class="mpd-tbl"><thead><tr><th>Tanggal</th><th>No Penerimaan</th><th class="mpd-r">Nominal</th></tr></thead><tbody>' +
                d.receipts.map(function (x) { return '<tr><td>' + dv(x.tanggal) + '</td><td>' + esc(x.no_penerimaan) + '</td><td class="mpd-r">' + nf(x.nominal) + '</td></tr>'; }).join('') + '</tbody></table></div>'
                : '<p class="mpd-muted">Belum ada penerimaan untuk invoice ini.</p>';
            $('#mp-modal-title').text('Detail Invoice ' + r.no_invoice); $('#mp-modal-body').html(h + '</div>'); $('#mp-open-fu').attr('href', FU);
            $('#mp-print-surat').attr('href', BASE + '/surat/' + encodeURIComponent(r.id)).toggle(!!r.can_surat); $('#mp-modal').modal('show');
        });
    });
    load();
})();
</script>
