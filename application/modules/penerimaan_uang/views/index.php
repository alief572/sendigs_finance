<?php
$tabCounts = $tab_counts ?? array('invoice' => 0, 'deposit' => 0, 'report' => 0);
$bankOptions = '';
foreach ($list_bank as $b) {
    $bankOptions .= '<option value="' . htmlspecialchars((string)$b['id'], ENT_QUOTES, 'UTF-8') . '">'
        . htmlspecialchars(trim($b['nama_bank'] . ' - ' . $b['rekening'] . ' - ' . $b['nama'], ' -'), ENT_QUOTES, 'UTF-8') . '</option>';
}
$monthsId = array('Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
?>
<?= mk_ui_assets() ?>
<link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css">
<?php if (!isset($GLOBALS['pu_s2'])) : $GLOBALS['pu_s2'] = 1; ?><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css"><script>window.jQuery && !jQuery.fn.select2 && document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"><\/script>');</script><?php endif; ?>
<div class="mk">
<div class="tabs" role="tablist" id="pu_tabs">
    <a role="tab" href="#tab_penerimaan" data-tab="invoice" class="on">Penerimaan Piutang Invoice <span class="cnt" id="cnt_invoice"><?= (int)$tabCounts['invoice'] ?></span></a>
    <a role="tab" href="#tab_kwitansi" data-tab="deposit">Penerimaan Piutang Kwitansi Deposit <span class="cnt" id="cnt_deposit"><?= (int)$tabCounts['deposit'] ?></span></a>
    <a role="tab" href="#tab_report" data-tab="report">Report Penerimaan <span class="cnt" id="cnt_report"><?= (int)$tabCounts['report'] ?></span></a>
</div>

<div class="box primary">
    <!-- Tab 1/2: daftar draft, masing-masing DataTables server-side -->
    <?php foreach (array('invoice', 'deposit') as $kind) : ?>
    <div id="pane_<?= $kind ?>" style="display:none">
        <div class="filters">
            <div class="f" style="min-width:260px;flex:0 1 420px"><select id="pb_<?= $kind ?>" style="width:100%" aria-label="Bank"><option value="">- Pilih Bank -</option><?= $bankOptions ?></select></div>
        </div>
        <div class="scroll" style="margin-top:12px">
            <table id="table_<?= $kind ?>" class="display" style="width:100%">
                <thead><tr><th>No.</th><th>Tanggal Transaksi</th><th>Bank</th><th>Keterangan</th><th class="r">Nominal</th><th>Status</th><th class="c">Action</th></tr></thead>
            </table>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Tab 3: report -->
    <div id="pane_report" style="display:none">
        <div class="filters">
            <div class="f"><label for="rep_year">Tahun</label>
                <select id="rep_year"><option value="">Semua</option><?php foreach ($list_years as $yr) : ?><option><?= $yr ?></option><?php endforeach; ?></select></div>
            <div class="f"><label for="rep_month">Bulan</label>
                <select id="rep_month"><option value="">Semua</option><?php foreach ($monthsId as $i => $mn) : ?><option value="<?= $i + 1 ?>"><?= $mn ?></option><?php endforeach; ?></select></div>
            <div class="f"><label for="rep_company">Company</label>
                <select id="rep_company"><option value="">Semua</option><?php foreach ($list_companies as $cmp) : ?><option><?= $cmp ?></option><?php endforeach; ?></select></div>
            <div class="f"><label for="rep_kind">Jenis</label>
                <select id="rep_kind"><option value="">Semua</option><option value="invoice">Penerimaan Piutang Invoice</option><option value="deposit">Penerimaan Piutang Kwitansi Deposit</option></select></div>
            <div class="f" style="margin-left:auto"><button class="btn btn-success" id="btn_export_excel" disabled>Export to Excel</button></div>
        </div>
        <div class="scroll" style="margin-top:12px">
            <table id="table_report" class="display" style="width:100%">
                <thead><tr><th>No.</th><th>Tanggal Penerimaan</th><th>Bank</th><th>Detail Penerimaan Bank</th><th>Customer</th><th>Nomor Invoice / Kwitansi</th><th>Company</th><th>TAX Code &amp; PPh 23</th><th class="r">Penerimaan Bank</th><th class="c">Action</th></tr></thead>
            </table>
        </div>
        <div class="hint" id="report_summary" style="margin-top:8px"></div>
        <div class="hint" style="margin-top:4px">Tanggal penerimaan mengikuti tanggal rekening koran yang diupload di Alokasi. Satu transfer yang membayar beberapa invoice tampil sebagai satu baris dengan invoice bertumpuk.</div>
    </div>
</div>
</div>

<script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>
<script>
(function () {
    var U = MockupUI, esc = U.esc, en = U.en, fdate = U.fdate, jq = window.jQuery;
    if (jq.fn.select2) jq('.mk select').not('[name="table_report_length"]').select2({width: 'resolve'});
    var URL_ADD = <?= json_encode(base_url('penerimaan_uang/add_penerimaan_uang/')) ?>;
    var URL_DRAFT = <?= json_encode(site_url('penerimaan_uang/draft_rows')) ?>;
    var URL_REPORT = <?= json_encode(site_url('penerimaan_uang/get_report_penerimaan')) ?>;
    var URL_EXPORT = <?= json_encode(site_url('penerimaan_uang/export_excel')) ?>;
    var URL_ROLLBACK = <?= json_encode(site_url('penerimaan_uang/rollback_penerimaan')) ?>;
    var tab = 'invoice';
    var TABLES = {};

    function $(id) { return document.getElementById(id); }
    function xhr(method, url, data, cb) {
        var r = new XMLHttpRequest();
        r.open(method, url);
        r.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        r.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        r.onload = function () { var j = null; try { j = JSON.parse(r.responseText); } catch (e) {} cb(j, r); };
        r.onerror = function () { cb(null, r); };
        r.send(Object.keys(data || {}).map(function (k) { return encodeURIComponent(k) + '=' + encodeURIComponent(data[k]); }).join('&'));
        return r;
    }
    function setCounts(c) {
        if (!c) return;
        $('cnt_invoice').textContent = c.invoice; $('cnt_deposit').textContent = c.deposit; $('cnt_report').textContent = c.report;
    }

    // ---- daftar draft (tab 1 dan 2): satu DataTable server-side per tab ----
    function initDraft(kind) {
        jq('#table_' + kind).on('xhr.dt', function (e, settings, json) { if (json) setCounts(json.counts); });
        TABLES[kind] = jq('#table_' + kind).DataTable({
            processing: true, serverSide: true, pageLength: 25, lengthMenu: [10, 25, 50, 100], order: [[1, 'asc']],
            ajax: {url: URL_DRAFT, type: 'POST', data: function (d) { d.receipt_kind = kind; d.bank = $('pb_' + kind).value; }},
            columns: [
                {data: 'no', orderable: false},
                {data: 'tgl', className: 'nowrap', render: function (v) { return v ? fdate(v) : 'PEND'; }},
                {data: 'bank', render: function (v) { return esc(v); }},
                {data: 'ket', render: function (v) { return esc(v); }},
                {data: 'nominal', className: 'r', render: function (v) { return en(v); }},
                {data: null, orderable: false, defaultContent: '<span class="lbl draft">Draft</span>'},
                {data: 'id', className: 'c', orderable: false, render: function (id) { return '<a class="btn btn-primary btn-xs" href="' + URL_ADD + id + '">Proses</a>'; }}
            ],
            language: {emptyTable: 'Tidak ada penerimaan yang menunggu proses. Yang sudah diproses ada di tab Report Penerimaan.'}
        });
        $('pb_' + kind).onchange = function () { TABLES[kind].ajax.reload(); };
    }

    // ---- report (tab 3) ----
    function initReport() {
        jq('#table_report').on('xhr.dt', function (e, settings, json) {
            if (!json) return;
            setCounts(json.counts);
            $('report_summary').innerHTML = '<b>Total (' + json.recordsFiltered + ' penerimaan, ' + json.count_invoices + ' invoice/kwitansi): ' + esc(json.total_nominal) + '</b>';
            $('btn_export_excel').disabled = !json.recordsFiltered;
        });
        TABLES.report = jq('#table_report').DataTable({
            processing: true, serverSide: true, pageLength: 25, lengthMenu: [10, 25, 50, 100], order: [[1, 'desc']],
            ajax: {url: URL_REPORT, type: 'POST', data: function (d) {
                d.year = $('rep_year').value; d.month = $('rep_month').value; d.company = $('rep_company').value; d.receipt_kind = $('rep_kind').value; d.id_customer = '';
            }},
            columns: [
                {data: 'no', orderable: false},
                {data: 'tanggal', className: 'nowrap'},
                {data: 'bank'},
                {data: 'detail_bank'},
                {data: 'customer'},
                {data: 'no_invoice', orderable: false},
                {data: 'company', orderable: false},
                {data: 'tax_pph', orderable: false},
                {data: 'penerimaan_bank', className: 'r', orderData: 8},
                {data: 'action', className: 'c', orderable: false}
            ],
            language: {emptyTable: 'Tidak ada penerimaan yang sudah diproses untuk filter ini.'}
        });
        ['rep_year', 'rep_month', 'rep_company', 'rep_kind'].forEach(function (id) { $(id).onchange = function () { TABLES.report.ajax.reload(); }; });
    }
    function exportUrl() {
        return URL_EXPORT + '?year=' + encodeURIComponent($('rep_year').value) + '&month=' + encodeURIComponent($('rep_month').value) + '&company=' + encodeURIComponent($('rep_company').value) +
            '&search=' + encodeURIComponent(TABLES.report.search()) + '&receipt_kind=' + encodeURIComponent($('rep_kind').value) + '&id_customer=';
    }
    // Export (M:651): "Menyiapkan…" saat unduh, toast sesudahnya.
    $('btn_export_excel').onclick = function () {
        var btn = this, label = 'Export to Excel';
        btn.disabled = true; btn.textContent = 'Menyiapkan…';
        var done = function (msg) { btn.disabled = false; btn.textContent = label; if (msg) U.toast(msg); };
        fetch(exportUrl(), {credentials: 'same-origin'}).then(function (r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            var cd = r.headers.get('Content-Disposition') || '', m = /filename="?([^";]+)"?/.exec(cd);
            return r.blob().then(function (b) { return {blob: b, name: m ? m[1] : 'Report_Penerimaan.xlsx'}; });
        }).then(function (f) {
            var a = document.createElement('a'); a.href = URL.createObjectURL(f.blob); a.download = f.name; document.body.appendChild(a); a.click();
            setTimeout(function () { URL.revokeObjectURL(a.href); a.remove(); }, 500);
            done('File Excel diunduh.');
        }).catch(function (e) { done('Export gagal: ' + e.message); });
    };

    // ---- tab ----
    function show(next) {
        tab = next;
        Array.prototype.forEach.call(document.querySelectorAll('#pu_tabs a'), function (a) { a.className = a.getAttribute('data-tab') === tab ? 'on' : ''; });
        ['invoice', 'deposit', 'report'].forEach(function (k) { $('pane_' + k).style.display = k === tab ? '' : 'none'; });
        // DataTable dibuat saat tab pertama kali dibuka (lebar kolom tidak bisa dihitung di panel tersembunyi).
        if (!TABLES[tab]) { tab === 'report' ? initReport() : initDraft(tab); }
        else TABLES[tab].columns.adjust();
    }
    Array.prototype.forEach.call(document.querySelectorAll('#pu_tabs a'), function (a) {
        a.onclick = function (e) { e.preventDefault(); history.replaceState(null, '', a.getAttribute('href')); show(a.getAttribute('data-tab')); };
    });

    // Rollback (fitur tambahan aplikasi): konfirmasi mockup, lalu muat ulang semua tabel yang sudah dibuka (angka tab ikut diperbarui).
    document.addEventListener('click', function (e) {
        var btn = e.target.closest ? e.target.closest('.rollback') : null;
        if (!btn) return;
        var id = btn.getAttribute('data-id');
        U.confirmBox('Konfirmasi', 'Rollback penerimaan ini? Saldo piutang dan sisa alokasi bank akan dipulihkan.', function () {
            xhr('POST', URL_ROLLBACK, {id_penerimaan: id}, function (res) {
                if (res && String(res.status) === '1') {
                    U.toast(res.msg || 'Penerimaan berhasil di-rollback.');
                    Object.keys(TABLES).forEach(function (k) { TABLES[k].ajax.reload(null, false); });
                } else {
                    U.toast((res && (res.msg || res.message)) || 'Tidak dapat melakukan rollback.');
                }
            });
        });
    });

    var hash = window.location.hash;
    show(hash === '#tab_kwitansi' ? 'deposit' : hash === '#tab_report' ? 'report' : 'invoice');
})();
</script>
