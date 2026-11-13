<?php $base = site_url('monitoring_piutang'); ?>
<link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css">
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
    <div id="pane-list" class="table-responsive"><table id="mp-list" class="table table-bordered table-striped mp-table" style="width:100%">
        <thead><tr><th>Invoice</th><th>Diterima</th><th>Customer &amp; Project</th><th>Company</th><th>Nilai</th><th>Terbayar</th><th>Sisa</th><th>Umur</th><th>Kategori</th><th>Follow Up</th><th>Aksi</th></tr></thead>
        <tbody></tbody>
        <tfoot><tr><th colspan="4">TOTAL (semua hasil filter)</th><th class="mp-num" id="tot-nilai"></th><th class="mp-num" id="tot-terbayar"></th><th class="mp-num" id="tot-saldo"></th><th colspan="4"></th></tr></tfoot></table></div>
    <div id="pane-rekap" class="table-responsive" style="display:none"><table id="mp-rekap" class="table table-bordered table-striped mp-table" style="width:100%">
        <thead><tr><th>Customer</th><th>Invoice</th><th>Lancar</th><th>Potensi Macet</th><th>Macet</th><th>Total</th></tr></thead>
        <tbody></tbody></table></div>
</div></div>

<div class="modal fade" id="mp-modal" tabindex="-1" role="dialog" aria-labelledby="mp-modal-title"><div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title" id="mp-modal-title"></h4></div>
    <div class="modal-body" id="mp-modal-body"></div>
    <div class="modal-footer"><a class="btn btn-default pull-left" id="mp-open-fu" href="#">Buka di Follow Up Piutang</a><a class="btn btn-warning pull-left" id="mp-print-surat" target="_blank" href="#" style="display:none;margin-left:5px">Print Surat Konfirmasi Tagihan</a><button class="btn btn-default" data-dismiss="modal">Tutup</button></div>
</div></div></div>

<script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>
<script>
(function () {
    var BASE = '<?= $base ?>', FU = '<?= site_url('follow_up_piutang') ?>', kat = 'all', tbl, rkTbl;
    var NAMA = {all: 'Total Piutang', lancar: 'Lancar', potensi: 'Potensi Macet', macet: 'Macet'}, KET = {lancar: 'Umur 0 - 60 hari', potensi: 'Umur 61 - 90 hari', macet: 'Umur > 90 hari', all: 'Diterima + belum diterima + lunas belum close'};
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

    // Kolom DataTable -> kunci urut di server (null = tidak bisa diurutkan)
    var SORT = ['no_invoice', null, 'customer', null, 'nilai', 'terbayar', 'saldo', 'umur', null, null, null];

    function initTable() {
        tbl = $('#mp-list').DataTable({
            serverSide: true, processing: true, searching: false, pageLength: 25, lengthMenu: [10, 25, 50, 100], order: [],
            language: {emptyTable: 'Tidak ada piutang.', zeroRecords: 'Tidak ada piutang.', processing: 'Memuat...'},
            columns: SORT.map(function (k) { return {orderable: k !== null}; }),
            ajax: function (d, cb) {
                var o = d.order && d.order.length ? d.order[0] : null, key = o ? SORT[o.column] : null;
                $.getJSON(BASE + '/list_data', $.extend(params(), {draw: d.draw, start: d.start, length: d.length, sort: key || '', dir: o ? o.dir : ''})).done(function (r) {
                    $('#mp-err').hide();
                    $('#tot-nilai').text(nf(r.totals.nilai)); $('#tot-terbayar').text(nf(r.totals.terbayar)); $('#tot-saldo').text(nf(r.totals.saldo));
                    cb({draw: r.draw, recordsTotal: r.recordsTotal, recordsFiltered: r.recordsFiltered, data: r.data.map(rowCells)});
                }).fail(function (x) {
                    $('#mp-err').text((x.responseJSON || {}).error || 'Gagal memuat daftar piutang. Periksa koneksi lalu coba lagi.').show();
                    cb({draw: d.draw, recordsTotal: 0, recordsFiltered: 0, data: []});
                });
            },
            columnDefs: [{targets: [4, 5, 6, 7], className: 'mp-num'}]
        });
    }

    // Rekap dibuat saat tab pertama kali dibuka (DataTables butuh tabel terlihat untuk menghitung lebar kolom)
    var RK_SORT = ['customer', 'count', 'lancar', 'potensi', 'macet', 'total'];

    function initRekap() {
        rkTbl = $('#mp-rekap').DataTable({
            serverSide: true, processing: true, searching: false, pageLength: 25, lengthMenu: [10, 25, 50, 100], order: [],
            language: {emptyTable: 'Tidak ada piutang.', zeroRecords: 'Tidak ada piutang.', processing: 'Memuat...'},
            columns: RK_SORT.map(function () { return {orderable: true}; }),
            ajax: function (d, cb) {
                var o = d.order && d.order.length ? d.order[0] : null;
                $.getJSON(BASE + '/rekap_data', $.extend(params(), {draw: d.draw, start: d.start, length: d.length, sort: o ? RK_SORT[o.column] : '', dir: o ? o.dir : ''})).done(function (r) {
                    $('#mp-err').hide();
                    cb({draw: r.draw, recordsTotal: r.recordsTotal, recordsFiltered: r.recordsFiltered, data: r.data.map(function (x) {
                        return ['<a href="#" data-cust="' + esc(x.customer) + '">' + esc(x.customer) + '</a>', x.count, rp(x.lancar), rp(x.potensi), rp(x.macet), '<b>' + rp(x.total) + '</b>'];
                    })});
                }).fail(function (x) {
                    $('#mp-err').text((x.responseJSON || {}).error || 'Gagal memuat rekap piutang. Periksa koneksi lalu coba lagi.').show();
                    cb({draw: d.draw, recordsTotal: 0, recordsFiltered: 0, data: []});
                });
            },
            columnDefs: [{targets: [1, 2, 3, 4, 5], className: 'mp-num'}]
        });
    }

    function rowCells(x) {
        return ['<a href="#" data-id="' + esc(x.id) + '"><b>' + esc(x.no_invoice) + '</b></a><br><small>' + dmy(x.tgl_invoice) + '</small>', dmy(x.tgl_terima),
            esc(x.customer) + '<br><small>' + esc(x.project) + '</small>', esc(x.company_name), nf(x.nilai), nf(x.terbayar), nf(x.saldo), x.umur + ' hari',
            '<span class="label lbl-' + x.kategori + '">' + NAMA[x.kategori] + '</span>',
            x.fu_late ? '<span style="color:#dd4b39;font-weight:bold">' + esc(x.fu_text) + '</span>' : esc(x.fu_text),
            '<a href="#" class="btn btn-info btn-xs" data-id="' + esc(x.id) + '">Detail</a> ' +
            (x.can_surat ? '<a class="btn btn-warning btn-xs" target="_blank" href="' + BASE + '/surat/' + encodeURIComponent(x.id) + '">Surat Konfirmasi</a>' : '')];
    }

    function load(skipTable) {
        $('#exp-label').text('(' + (kat === 'all' ? 'Semua' : NAMA[kat]) + ')');
        $('#mp-err').hide();
        if (skipTable !== true) { tbl.ajax.reload(null, true); if (rkTbl) { rkTbl.ajax.reload(null, true); } }
        $.getJSON(BASE + '/data', params()).done(function (r) {
            if (r.status !== 'success') { $('#mp-err').text(r.msg || 'Gagal memuat data piutang.').show(); return; }
            if ($('#f-company option').length === 1) { r.companies.forEach(function (c) { $('#f-company').append($('<option>').val(c.id).text(c.nm_company)); }); }
            var s = r.summary;
            $('#mp-grand').html('Total keseluruhan sisa piutang: <b>' + rp(s.grand_cents) + '</b> <small class="text-muted">(sudah diterima + belum diterima, hanya invoice tanggal 1 Oktober 2026 ke atas, sama dengan Follow Up Piutang)</small>');
            card('all', 'all', {all: s.semua});
            ['lancar', 'potensi', 'macet'].forEach(function (k) { card(k, k, s); });
            $('#card-belum').html('<h5>Belum Diterima Customer</h5><div class="amt">' + rp(s.belum.cents) + '</div><small>' + s.belum.count + ' invoice belum ada tanggal terima · <a href="' + FU + '">buka Follow Up Piutang</a></small>');
            if (!$('#dl-customer option').length) { r.customers.forEach(function (c) { $('#dl-customer').append($('<option>').val(c)); }); }
        }).fail(function (x) {
            $('#mp-err').text((x.responseJSON || {}).msg || 'Gagal memuat data piutang. Periksa koneksi lalu coba lagi.').show();
        });
    }

    $('.mp-card[data-kat]').on('click', function (e) {
        if ($(e.target).closest('[data-dl]').length) { return; }
        var k = $(this).data('kat'); kat = (kat === k && k !== 'all') ? 'all' : k; load();
    });
    $('.row').on('click', '[data-dl]', function () { window.location = BASE + '/export?' + $.param($.extend(params(), {kategori: $(this).data('dl')})); });
    $('#btn-export').on('click', function () { window.location = BASE + '/export?' + $.param(params()); });
    $('#f-company').on('change', function () { load(); });
    $('.mp-card[data-kat]').on('keydown', function (e) { if ((e.key === 'Enter' || e.key === ' ') && e.target === this) { e.preventDefault(); $(this).click(); } });
    $('#f-customer').on('change', function () { clearTimeout(window.__mpT); window.__mpT = setTimeout(load, 350); });
    $('#f-cari').on('input', function () { clearTimeout(window.__mpT); window.__mpT = setTimeout(load, 350); });
    $('#btn-clear').on('click', function () { $('#f-company, #f-customer, #f-cari').val(''); kat = 'all'; load(); });
    $('#mp-tabs').on('click', 'a', function (e) { e.preventDefault(); $('#mp-tabs li').removeClass('active'); $(this).parent().addClass('active'); var p = $(this).data('pane'); $('#pane-list').toggle(p === 'list'); $('#pane-rekap').toggle(p === 'rekap'); if (p === 'rekap' && !rkTbl) { initRekap(); } });
    $('#mp-rekap tbody').on('click', '[data-cust]', function (e) {
        e.preventDefault(); $('#f-customer').val($(this).data('cust')); $('#mp-tabs a[data-pane=list]').click(); load();
    });
    $('#mp-list tbody').on('click', '[data-id]', function (e) {
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
    initTable(); load(true);
})();
</script>
