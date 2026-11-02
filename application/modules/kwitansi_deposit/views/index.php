<?php
// Daftar Kwitansi Deposit (mockup viewKwList): filter, pencarian, paging dan export dikerjakan di browser dari data yang dikirim server.
$rowsJson = array();
foreach ($results['kwitansiList'] as $row) {
    $rowsJson[] = array(
        'id' => (int)$row['id'],
        'tgl' => (string)$row['tanggal_kwitansi'],
        'no' => (string)$row['no_kwitansi'],
        'custId' => (string)$row['id_customer'],
        'cust' => (string)$row['nm_customer'],
        'spk' => (string)($row['no_spk'] ?? $row['id_spk_penawaran'] ?? $row['po_customer'] ?? ''),
        'company' => (string)($row['nm_company'] ?? ''),
        'nominal' => (float)$row['nilai_kwitansi'],
        'lunas' => $row['status'] !== 'MENUNGGU_PEMBAYARAN',
        'ref' => (string)($row['no_penerimaan'] ?? ''),
        'tglBayar' => (!empty($row['tanggal_bayar']) && $row['tanggal_bayar'] !== '0000-00-00') ? substr((string)$row['tanggal_bayar'], 0, 10) : '',
        'draft' => !empty($row['draft_split_id']) ? (int)$row['draft_split_id'] : 0
    );
}
$custJson = array();
foreach ($results['customers'] as $c) $custJson[] = array('id' => (string)$c['id_customer'], 'name' => (string)$c['nm_customer']);
?>
<?= mk_ui_assets() ?>
<div class="mk">
<h1>Kwitansi Deposit</h1>
<div class="box primary" id="kw_box"></div>
</div>

<script>
(function () {
    var U = MockupUI, esc = U.esc, en = U.en, fdate = U.fdate;
    var ROWS = <?= json_encode($rowsJson, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var CUSTS = <?= json_encode($custJson, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var URL_ADD = <?= json_encode(site_url('kwitansi_deposit/add')) ?>;
    var URL_DETAIL = <?= json_encode(site_url('kwitansi_deposit/detail/')) ?>;
    var URL_JOURNAL = <?= json_encode(site_url('kwitansi_deposit/journal/')) ?>;
    var URL_EXPORT = <?= json_encode(site_url('kwitansi_deposit/export_excel')) ?>;
    var URL_PROSES = <?= json_encode(site_url('penerimaan_uang/add_penerimaan_uang/')) ?>;
    var URL_REF = <?= json_encode(site_url('penerimaan_uang/view_surat?no=')) ?>;
    var TF = {cust: '', st: '', q: '', per: 25, page: 1};

    function statusOf(x) { return x.lunas ? 'Lunas' : 'Belum Dibayar'; }
    function lbl(x) { return x.lunas ? '<span class="lbl lunas">Lunas</span>' : '<span class="lbl draft">Menunggu Pembayaran</span>'; }
    function refLink(ref) { return '<a href="' + URL_REF + encodeURIComponent(ref) + '">' + esc(ref) + '</a>'; }

    function rows() {
        return ROWS.slice().sort(function (a, b) { return a.tgl < b.tgl ? -1 : a.tgl > b.tgl ? 1 : (a.no < b.no ? -1 : 1); }).filter(function (x) {
            if (TF.cust && x.custId !== TF.cust) return false;
            if (TF.st && statusOf(x) !== TF.st) return false;
            if (TF.q && !U.matchQ(TF.q, U.hayOf([x.tgl, x.no, x.cust, x.spk, x.company, x.nominal, statusOf(x), x.ref]))) return false;
            return true;
        });
    }

    function draw() {
        var list = rows(), pages = Math.max(1, Math.ceil(list.length / TF.per));
        if (TF.page > pages) TF.page = pages;
        var from = (TF.page - 1) * TF.per, slice = list.slice(from, from + TF.per);
        document.getElementById('kw_box').innerHTML =
            '<div style="margin-bottom:12px"><a class="btn btn-primary" href="' + URL_ADD + '">+ Buat Kwitansi Deposit</a></div>' +
            '<div class="filters">' +
            '<div class="f" style="min-width:240px;flex:0 1 360px"><select id="tfc" style="width:100%" aria-label="Customer"><option value="">- Semua Customer -</option>' + CUSTS.map(function (c) { return '<option value="' + esc(c.id) + '"' + (TF.cust === c.id ? ' selected' : '') + '>' + esc(c.name) + '</option>'; }).join('') + '</select></div>' +
            '<div class="f" style="min-width:200px;flex:0 1 240px"><select id="tfst" style="width:100%" aria-label="Status"><option value="">- Semua Status -</option>' + [['Belum Dibayar', 'Menunggu Pembayaran'], ['Lunas', 'Lunas']].map(function (o) { return '<option value="' + o[0] + '"' + (TF.st === o[0] ? ' selected' : '') + '>' + o[1] + '</option>'; }).join('') + '</select></div>' +
            '<div class="f"><button class="btn btn-primary" id="tfs">Search</button><button class="btn btn-danger" id="tfr">Reset</button></div>' +
            '<div class="f" style="margin-left:auto"><button class="btn btn-success" id="tfx"' + (list.length ? '' : ' disabled') + '>Export to Excel</button></div></div>' +
            '<div class="tbl-bar"><div class="f"><select id="tfp">' + [10, 25, 50, 100].map(function (n) { return '<option' + (TF.per === n ? ' selected' : '') + '>' + n + '</option>'; }).join('') + '</select> entries per page</div>' +
            '<div class="f"><label for="tfq">Search:</label><input type="search" id="tfq" value="' + esc(TF.q) + '"></div></div>' +
            '<div class="scroll"><table><thead><tr><th>No.</th><th>Tanggal</th><th>No Kwitansi</th><th>Customer</th><th>No SPK</th><th>Company</th><th class="r">Nilai Kwitansi</th><th>Status</th><th>No Penerimaan</th><th class="c">Action</th></tr></thead><tbody>' +
            (slice.length ? slice.map(function (x, i) {
                return '<tr><td>' + (from + i + 1) + '</td><td class="nowrap">' + fdate(x.tgl) + '</td><td class="nowrap">' + esc(x.no) + '</td><td style="min-width:160px">' + esc(x.cust) + '</td><td class="nowrap">' + esc(x.spk) + '</td><td>' + esc(x.company) + '</td><td class="r">' + en(x.nominal) + '</td><td>' + lbl(x) + '</td>' +
                    '<td class="nowrap">' + (x.ref ? refLink(x.ref) + (x.tglBayar ? ' <span class="muted">· ' + fdate(x.tglBayar) + '</span>' : '') : '<span class="muted">-</span>') + '</td>' +
                    '<td class="c nowrap">' + (!x.lunas && x.draft ? '<a class="btn btn-primary btn-xs" href="' + URL_PROSES + x.draft + '">Proses Penerimaan</a> ' : '') + '<button class="btn btn-info btn-xs" data-jr="' + x.id + '">Jurnal</button> <a class="btn btn-default btn-xs" href="' + URL_DETAIL + x.id + '">Detail</a></td></tr>';
            }).join('') : '<tr><td colspan="10" class="empty">Tidak ada kwitansi deposit yang cocok.</td></tr>') +
            '</tbody></table></div>' +
            '<div class="tbl-bar"><div class="muted">Showing ' + (list.length ? from + 1 : 0) + ' to ' + (from + slice.length) + ' of ' + list.length + ' entries</div><div class="pager">' + U.pager(TF.page, pages) + '</div></div>';

        document.getElementById('tfs').onclick = function () { TF.cust = document.getElementById('tfc').value; TF.st = document.getElementById('tfst').value; TF.page = 1; draw(); };
        document.getElementById('tfr').onclick = function () { TF = {cust: '', st: '', q: '', per: TF.per, page: 1}; draw(); };
        document.getElementById('tfp').onchange = function () { TF.per = +this.value; TF.page = 1; draw(); };
        document.getElementById('tfq').oninput = function () {
            TF.q = this.value; TF.page = 1;
            var pos = this.selectionStart; draw();
            var q = document.getElementById('tfq'); q.focus(); q.setSelectionRange(pos, pos);
        };
        document.getElementById('tfx').onclick = function () {
            window.location.href = URL_EXPORT + '?ids=' + list.map(function (x) { return x.id; }).join(',');
        };
        Array.prototype.forEach.call(document.querySelectorAll('[data-page]'), function (b) { b.onclick = function () { TF.page = +b.getAttribute('data-page'); draw(); }; });
        Array.prototype.forEach.call(document.querySelectorAll('[data-jr]'), function (b) { b.onclick = function () { jurnalModal(b.getAttribute('data-jr')); }; });
    }

    // Modal Jurnal (M:1075): kolom journalTable + Balancing, catatan pembayaran.
    function jurnalModal(id) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', URL_JOURNAL + encodeURIComponent(id));
        xhr.onload = function () {
            var res;
            try { res = JSON.parse(xhr.responseText); } catch (e) { res = null; }
            if (!res || res.status !== 1) { U.toast((res && res.msg) || 'Jurnal tidak dapat dimuat.'); return; }
            var h = res.header, dr = 0, cr = 0;
            var body = res.journals.map(function (j) {
                dr += Number(j.debit); cr += Number(j.kredit);
                return '<tr><td class="nowrap">' + fdate(j.tgl_jurnal) + '</td><td>' + esc(j.coa) + '</td><td>' + esc(j.nm_company) + '</td><td>' + esc(j.nm_coa) + '</td><td>' + esc(j.keterangan) + '</td><td class="r">' + en(j.debit) + '</td><td class="r">' + en(j.kredit) + '</td></tr>';
            }).join('');
            var note = h.status === 'LUNAS' && h.no_penerimaan
                ? 'Dibayar lewat ' + refLink(h.no_penerimaan) + (h.tanggal_bayar ? ' · ' + fdate(h.tanggal_bayar) : '') + ' · Bank ' + en(h.nominal_bank) + '. Jurnal penerimaannya ada di halaman penerimaan tersebut.'
                : 'Belum dibayar. Saat transfer masuk, alokasikan dengan jenis Deposit di Alokasi, lalu proses di Penerimaan Piutang › Penerimaan Piutang Kwitansi Deposit.';
            var w = U.openModal('<div class="mk-mh"><span>Jurnal Kwitansi Deposit</span><button data-x aria-label="Tutup">×</button></div><div class="mk-mb mk">' +
                '<div class="kv"><div><span>No Kwitansi</span>' + esc(h.no_kwitansi) + '</div><div><span>Tanggal</span>' + fdate(h.tanggal_kwitansi) + '</div><div><span>Customer</span>' + esc(h.nm_customer) + '</div><div><span>No SPK</span>' + esc(h.no_spk) + '</div><div><span>Company</span>' + esc(h.nm_company) + '</div></div>' +
                '<div class="scroll"><table><thead><tr><th>Tanggal Jurnal</th><th>COA</th><th>Nama Company</th><th>Nama Account</th><th>Deskripsi</th><th class="r">Debit</th><th class="r">Credit</th></tr></thead><tbody>' + body +
                '<tr class="sum"><td colspan="5" class="c"><b>Balancing</b></td><td class="r"><b>' + en(dr) + '</b></td><td class="r"><b>' + en(cr) + '</b></td></tr></tbody></table></div>' +
                '<div class="hint" style="margin-top:8px">' + note + '</div></div><div class="mk-mf mk"><button class="btn btn-default" data-x>Tutup</button></div>', 'sm');
            Array.prototype.forEach.call(w.querySelectorAll('[data-x]'), function (b) { b.onclick = function () { U.closeModal(w); }; });
            Array.prototype.forEach.call(w.querySelectorAll('a'), function (a) { a.onclick = function () { U.closeModal(w); }; });
        };
        xhr.onerror = function () { U.toast('Jurnal tidak dapat dimuat.'); };
        xhr.send();
    }

    draw();
    try { var flash = sessionStorage.getItem('mk_flash'); if (flash) { sessionStorage.removeItem('mk_flash'); U.toast(flash); } } catch (err) {}
})();
</script>
