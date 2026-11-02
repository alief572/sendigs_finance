<?php
$tabCounts = $tab_counts ?? array('invoice' => 0, 'deposit' => 0, 'report' => 0);
$banksJson = array();
foreach ($list_bank as $b) $banksJson[] = array('id' => (string)$b['id'], 'name' => trim($b['nama_bank'] . ' - ' . $b['rekening'] . ' - ' . $b['nama'], ' -'));
$monthsId = array('Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
?>
<?= mk_ui_assets() ?>
<div class="mk">
<h1>Penerimaan Piutang</h1>

<div class="tabs" role="tablist" id="pu_tabs">
    <a role="tab" href="#tab_penerimaan" data-tab="invoice" class="on">Penerimaan Piutang Invoice <span class="cnt" id="cnt_invoice"><?= (int)$tabCounts['invoice'] ?></span></a>
    <a role="tab" href="#tab_kwitansi" data-tab="deposit">Penerimaan Piutang Kwitansi Deposit <span class="cnt" id="cnt_deposit"><?= (int)$tabCounts['deposit'] ?></span></a>
    <a role="tab" href="#tab_report" data-tab="report">Report Penerimaan <span class="cnt" id="cnt_report"><?= (int)$tabCounts['report'] ?></span></a>
</div>

<div class="box primary">
    <!-- Tab 1/2: daftar draft (dirender di browser) -->
    <div id="pane_draft"></div>

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
            <div class="f"><label for="rep_search">Search</label><input type="search" id="rep_search" placeholder="Cari apa saja: teks, angka, tanggal"></div>
            <div class="f" style="margin-left:auto"><button class="btn btn-success" id="btn_export_excel" disabled>Export to Excel</button></div>
        </div>
        <div class="scroll" style="margin-top:12px">
            <table id="table_report">
                <thead><tr><th>No.</th><th>Tanggal Penerimaan</th><th>Bank</th><th>Detail Penerimaan Bank</th><th>Customer</th><th>Nomor Invoice / Kwitansi</th><th>Company</th><th>TAX Code &amp; PPh 23</th><th class="r">Penerimaan Bank</th><th class="c">Action</th></tr></thead>
                <tbody id="report_body"></tbody>
            </table>
        </div>
        <div class="hint" style="margin-top:8px">Tanggal penerimaan mengikuti tanggal rekening koran yang diupload di Alokasi. Satu transfer yang membayar beberapa invoice tampil sebagai satu grup.</div>
    </div>
</div>
</div>

<script>
(function () {
    var U = MockupUI, esc = U.esc, en = U.en, fdate = U.fdate;
    var BANKS = <?= json_encode($banksJson, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var URL_ADD = <?= json_encode(base_url('penerimaan_uang/add_penerimaan_uang/')) ?>;
    var URL_DRAFT = <?= json_encode(site_url('penerimaan_uang/draft_rows')) ?>;
    var URL_REPORT = <?= json_encode(site_url('penerimaan_uang/get_report_penerimaan')) ?>;
    var URL_EXPORT = <?= json_encode(site_url('penerimaan_uang/export_excel')) ?>;
    var URL_ROLLBACK = <?= json_encode(site_url('penerimaan_uang/rollback_penerimaan')) ?>;
    var tab = 'invoice';
    var DATA = {invoice: null, deposit: null};
    var PF = {bank: '', q: '', per: 25, page: 1, kind: 'invoice'};
    var reportReq = null, exportUrl = '';

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

    // ---- daftar draft (viewPenerimaanList) ----
    function bankOpts(sel) { return '<option value="">- Pilih Bank -</option>' + BANKS.map(function (b) { return '<option value="' + esc(b.id) + '"' + (sel === b.id ? ' selected' : '') + '>' + esc(b.name) + '</option>'; }).join(''); }
    function drawDraft() {
        var all = DATA[tab] || [];
        var rows = all.filter(function (p) {
            if (PF.bank && p.bankId !== PF.bank) return false;
            if (PF.q && !U.matchQ(PF.q, U.hayOf([p.tgl, p.ket, p.bank, p.nominal, 'Draft']))) return false;
            return true;
        });
        var pages = Math.max(1, Math.ceil(rows.length / PF.per)); if (PF.page > pages) PF.page = pages;
        var from = (PF.page - 1) * PF.per, slice = rows.slice(from, from + PF.per);
        $('pane_draft').innerHTML =
            '<div class="filters"><div class="f" style="min-width:260px;flex:0 1 420px"><select id="pb" style="width:100%" aria-label="Bank">' + bankOpts(PF.bank) + '</select></div><button class="btn btn-primary" id="psearch">Search</button></div>' +
            '<div class="tbl-bar"><div class="f"><select id="pper">' + [10, 25, 50, 100].map(function (n) { return '<option' + (PF.per === n ? ' selected' : '') + '>' + n + '</option>'; }).join('') + '</select> entries per page</div><div class="f"><label for="pq">Search:</label><input type="search" id="pq" value="' + esc(PF.q) + '"></div></div>' +
            '<div class="scroll"><table><thead><tr><th>No.</th><th>Tanggal Transaksi</th><th>Bank</th><th>Keterangan</th><th class="r">Nominal</th><th>Status</th><th class="c">Action</th></tr></thead><tbody>' +
            (slice.length ? slice.map(function (p, i) {
                return '<tr><td>' + (from + i + 1) + '</td><td class="nowrap">' + (p.tgl ? fdate(p.tgl) : 'PEND') + '</td><td style="min-width:200px">' + esc(p.bank) + '</td><td style="min-width:260px">' + esc(p.ket) + '</td><td class="r">' + en(p.nominal) + '</td><td><span class="lbl draft">Draft</span></td><td class="c"><a class="btn btn-primary btn-xs" href="' + URL_ADD + p.id + '">Proses</a></td></tr>';
            }).join('') : '<tr><td colspan="7" class="empty">Tidak ada penerimaan yang menunggu proses. Yang sudah diproses ada di tab Report Penerimaan.</td></tr>') +
            '</tbody></table></div>' +
            '<div class="tbl-bar"><div class="muted">Showing ' + (rows.length ? from + 1 : 0) + ' to ' + (from + slice.length) + ' of ' + rows.length + ' entries</div><div class="pager">' + U.pager(PF.page, pages) + '</div></div>';
        $('psearch').onclick = function () { PF.bank = $('pb').value; PF.page = 1; drawDraft(); };
        $('pper').onchange = function () { PF.per = +this.value; PF.page = 1; drawDraft(); };
        $('pq').oninput = function () { PF.q = this.value; PF.page = 1; var pos = this.selectionStart; drawDraft(); var q = $('pq'); q.focus(); q.setSelectionRange(pos, pos); };
        Array.prototype.forEach.call(document.querySelectorAll('#pane_draft [data-page]'), function (b) { b.onclick = function () { PF.page = +b.getAttribute('data-page'); drawDraft(); }; });
    }
    function loadDraft(kind, done) {
        xhr('POST', URL_DRAFT, {receipt_kind: kind}, function (res) {
            if (res && res.rows) { DATA[kind] = res.rows; setCounts(res.counts); }
            if (done) done();
        });
    }

    // ---- report ----
    function loadReport() {
        if (reportReq) reportReq.abort();
        reportReq = xhr('POST', URL_REPORT, {year: $('rep_year').value, month: $('rep_month').value, company: $('rep_company').value, receipt_kind: $('rep_kind').value, id_customer: '', custom_search: $('rep_search').value}, function (res) {
            if (!res) return;
            var body = res.html || '';
            $('report_body').innerHTML = body
                ? body + '<tr class="sum"><td colspan="8" class="k"><b>Total (' + res.count_receipts + ' penerimaan, ' + res.count_invoices + ' invoice/kwitansi)</b></td><td class="r"><b>' + res.total_nominal + '</b></td><td></td></tr>'
                : '<tr><td colspan="10" class="empty">Tidak ada penerimaan yang sudah diproses untuk filter ini.</td></tr>';
            exportUrl = URL_EXPORT + '?year=' + encodeURIComponent($('rep_year').value) + '&month=' + encodeURIComponent($('rep_month').value) + '&company=' + encodeURIComponent($('rep_company').value) +
                '&search=' + encodeURIComponent($('rep_search').value) + '&receipt_kind=' + encodeURIComponent($('rep_kind').value) + '&id_customer=';
            $('btn_export_excel').disabled = !body;
        });
    }
    // Export (M:651): "Menyiapkan…" saat unduh, toast sesudahnya.
    $('btn_export_excel').onclick = function () {
        var btn = this, label = 'Export to Excel';
        btn.disabled = true; btn.textContent = 'Menyiapkan…';
        var done = function (msg) { btn.disabled = false; btn.textContent = label; if (msg) U.toast(msg); };
        fetch(exportUrl, {credentials: 'same-origin'}).then(function (r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            var cd = r.headers.get('Content-Disposition') || '', m = /filename="?([^";]+)"?/.exec(cd);
            return r.blob().then(function (b) { return {blob: b, name: m ? m[1] : 'Report_Penerimaan.xlsx'}; });
        }).then(function (f) {
            var a = document.createElement('a'); a.href = URL.createObjectURL(f.blob); a.download = f.name; document.body.appendChild(a); a.click();
            setTimeout(function () { URL.revokeObjectURL(a.href); a.remove(); }, 500);
            done('File Excel diunduh.');
        }).catch(function (e) { done('Export gagal: ' + e.message); });
    };
    var timer = null;
    $('rep_search').oninput = function () { clearTimeout(timer); timer = setTimeout(loadReport, 200); };
    ['rep_year', 'rep_month', 'rep_company', 'rep_kind'].forEach(function (id) { $(id).onchange = loadReport; });

    // ---- tab ----
    function show(next) {
        tab = next;
        Array.prototype.forEach.call(document.querySelectorAll('#pu_tabs a'), function (a) { a.className = a.getAttribute('data-tab') === tab ? 'on' : ''; });
        var isReport = tab === 'report';
        $('pane_draft').style.display = isReport ? 'none' : '';
        $('pane_report').style.display = isReport ? '' : 'none';
        if (isReport) { loadReport(); return; }
        if (PF.kind !== tab) PF = {bank: '', q: '', per: PF.per, page: 1, kind: tab};
        if (DATA[tab]) { drawDraft(); return; }
        $('pane_draft').innerHTML = '';
        loadDraft(tab, drawDraft);
    }
    Array.prototype.forEach.call(document.querySelectorAll('#pu_tabs a'), function (a) {
        a.onclick = function (e) { e.preventDefault(); history.replaceState(null, '', a.getAttribute('href')); show(a.getAttribute('data-tab')); };
    });

    // Rollback (fitur tambahan aplikasi): konfirmasi mockup, lalu muat ulang daftar dan angka tab.
    document.addEventListener('click', function (e) {
        var btn = e.target.closest ? e.target.closest('.rollback') : null;
        if (!btn) return;
        var id = btn.getAttribute('data-id');
        U.confirmBox('Konfirmasi', 'Rollback penerimaan ini? Saldo piutang dan sisa alokasi bank akan dipulihkan.', function () {
            xhr('POST', URL_ROLLBACK, {id_penerimaan: id}, function (res) {
                if (res && String(res.status) === '1') {
                    U.toast(res.msg || 'Penerimaan berhasil di-rollback.');
                    DATA.invoice = DATA.deposit = null;
                    loadDraft('invoice'); loadDraft('deposit'); loadReport();
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
