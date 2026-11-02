<link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css">
<style>
    .badge-status {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 10px;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }
    .badge-menunggu {
        background: #f39c12;
    }
    .badge-lunas {
        background: #00a65a;
    }
    .table_list th {
        font-weight: 700;
        white-space: nowrap;
    }
    .table_list td {
        vertical-align: middle !important;
    }
    .kd-filters {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }
    #modalJurnalKwitansi .kd-kv {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px 16px;
        font-size: 13px;
        margin-bottom: 10px;
    }
    #modalJurnalKwitansi .kd-kv > div > span:first-child {
        display: block;
        color: #777;
        font-size: 12px;
    }
</style>

<h1 style="font-size: 24px; margin: 0 0 12px;">Kwitansi Deposit</h1>

<div class="box box-primary">
    <div class="box-body">
        <div style="margin-bottom: 12px;">
            <a href="<?= site_url('kwitansi_deposit/add') ?>" class="btn btn-primary">+ Buat Kwitansi Deposit</a>
        </div>
        <form method="get" action="<?= site_url('kwitansi_deposit') ?>" class="kd-filters">
            <div style="min-width: 240px; flex: 0 1 360px;">
                <select name="id_customer" class="form-control input-sm select2" aria-label="Customer">
                    <option value="">- Semua Customer -</option>
                    <?php foreach ($results['customers'] as $c): ?>
                        <option value="<?= htmlspecialchars($c['id_customer']) ?>" <?= ($results['filters']['id_customer'] === $c['id_customer']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['nm_customer']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="min-width: 200px; flex: 0 1 240px;">
                <select name="status" class="form-control input-sm" aria-label="Status">
                    <option value="">- Semua Status -</option>
                    <option value="MENUNGGU_PEMBAYARAN" <?= ($results['filters']['status'] === 'MENUNGGU_PEMBAYARAN') ? 'selected' : '' ?>>Menunggu Pembayaran</option>
                    <option value="LUNAS" <?= ($results['filters']['status'] === 'LUNAS') ? 'selected' : '' ?>>Lunas</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary btn-filter">Search</button>
                <a href="<?= site_url('kwitansi_deposit') ?>" class="btn btn-danger">Reset</a>
            </div>
            <div style="margin-left: auto;">
                <?php if (!empty($results['kwitansiList'])): ?>
                    <a id="btn_export_kw" href="<?= site_url('kwitansi_deposit/export_excel?' . http_build_query($results['filters'])) ?>" class="btn btn-success">Export to Excel</a>
                <?php else: ?>
                    <button type="button" class="btn btn-success" disabled>Export to Excel</button>
                <?php endif; ?>
            </div>
        </form>

        <div class="table-responsive" style="margin-top: 12px;">
            <table class="table table-bordered table_list" id="table_kwitansi">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Tanggal</th>
                        <th>No Kwitansi</th>
                        <th>Customer</th>
                        <th>No SPK</th>
                        <th>Company</th>
                        <th class="text-right">Nilai Kwitansi</th>
                        <th>Status</th>
                        <th>No Penerimaan</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results['kwitansiList'] as $row): ?>
                        <?php
                        // Teks pencarian tambahan (mockup hayOf): tanggal dalam beberapa format, nominal dengan/tanpa pemisah, status.
                        $searchHay = implode(' ', array(
                            $row['tanggal_kwitansi'], mk_fdate($row['tanggal_kwitansi']), date('d/m/Y', strtotime($row['tanggal_kwitansi'])),
                            preg_replace('/\.00$/', '', (string)$row['nilai_kwitansi']), mk_en($row['nilai_kwitansi']), mk_idn($row['nilai_kwitansi']),
                            $row['status'] === 'MENUNGGU_PEMBAYARAN' ? 'Menunggu Pembayaran Belum Dibayar' : 'Lunas'
                        ));
                        ?>
                        <tr>
                            <td class="kd-no"></td>
                            <td style="white-space: nowrap;" data-search="<?= htmlspecialchars($searchHay) ?>"><?= htmlspecialchars(mk_fdate($row['tanggal_kwitansi'])) ?></td>
                            <td style="white-space: nowrap;"><?= htmlspecialchars($row['no_kwitansi']) ?></td>
                            <td style="min-width: 160px;"><?= htmlspecialchars($row['nm_customer']) ?></td>
                            <td style="white-space: nowrap;"><?= htmlspecialchars($row['no_spk'] ?? $row['id_spk_penawaran'] ?? $row['po_customer'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['nm_company'] ?? '-') ?></td>
                            <td class="text-right"><?= mk_en($row['nilai_kwitansi']) ?></td>
                            <td>
                                <?php if ($row['status'] === 'MENUNGGU_PEMBAYARAN'): ?>
                                    <span class="badge-status badge-menunggu">Menunggu Pembayaran</span>
                                <?php else: ?>
                                    <span class="badge-status badge-lunas">Lunas</span>
                                <?php endif; ?>
                            </td>
                            <td style="white-space: nowrap;">
                                <?php if (!empty($row['no_penerimaan'])): ?>
                                    <a href="<?= site_url('penerimaan_uang/view_surat?no=' . urlencode($row['no_penerimaan'])) ?>"><?= htmlspecialchars($row['no_penerimaan']) ?></a>
                                    <?php if (!empty($row['tanggal_bayar']) && $row['tanggal_bayar'] !== '0000-00-00'): ?>
                                        <span class="text-muted">· <?= htmlspecialchars(mk_fdate($row['tanggal_bayar'])) ?></span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center" style="white-space: nowrap;">
                                <?php if ($row['status'] === 'MENUNGGU_PEMBAYARAN' && !empty($row['draft_split_id'])): ?>
                                    <a href="<?= site_url('penerimaan_uang/add_penerimaan_uang/' . (int)$row['draft_split_id']) ?>" class="btn btn-primary btn-xs">Proses Penerimaan</a>
                                <?php endif; ?>
                                <button type="button" class="btn btn-info btn-xs btn-kwitansi-jurnal" data-id="<?= (int)$row['id'] ?>">Jurnal</button>
                                <a href="<?= site_url('kwitansi_deposit/detail/' . $row['id']) ?>" class="btn btn-default btn-xs">Detail</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalJurnalKwitansi" tabindex="-1" role="dialog" aria-labelledby="modalJurnalKwitansiTitle">
    <div class="modal-dialog" role="document" style="width: min(640px, 100%);">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modalJurnalKwitansiTitle">Jurnal Kwitansi Deposit</h4>
            </div>
            <div class="modal-body">
                <div id="jurnal_kw_loading" class="text-muted">Memuat jurnal...</div>
                <div id="jurnal_kw_error" class="alert alert-danger" style="display: none;"></div>
                <div id="jurnal_kw_content" style="display: none;">
                    <div class="kd-kv">
                        <div><span>No Kwitansi</span><span id="jurnal_kw_no"></span></div>
                        <div><span>Tanggal</span><span id="jurnal_kw_tgl"></span></div>
                        <div><span>Customer</span><span id="jurnal_kw_cust"></span></div>
                        <div><span>No SPK</span><span id="jurnal_kw_spk"></span></div>
                        <div><span>Company</span><span id="jurnal_kw_company"></span></div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table_list">
                            <thead>
                                <tr>
                                    <th>Tanggal Jurnal</th>
                                    <th>COA</th>
                                    <th>Nama Company</th>
                                    <th>Nama Account</th>
                                    <th>Deskripsi</th>
                                    <th class="text-right">Debit</th>
                                    <th class="text-right">Credit</th>
                                </tr>
                            </thead>
                            <tbody id="jurnal_kw_body"></tbody>
                        </table>
                    </div>
                    <div class="text-muted" id="jurnal_kw_note" style="margin-top: 8px;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        var MONTHS = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        function esc(value) {
            return $('<div>').text(value === null || value === undefined ? '' : String(value)).html();
        }
        // en() mockup: bulat, pemisah koma, tanpa Rp.
        function money(value) {
            return Number(value || 0).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});
        }
        // fdate() mockup: dd-FullMonth-yyyy.
        function tgl(value) {
            var d = new Date(String(value).replace(' ', 'T'));
            if (isNaN(d.getTime())) return esc(value);
            return ('0' + d.getDate()).slice(-2) + '-' + MONTHS[d.getMonth()] + '-' + d.getFullYear();
        }
        function renderJurnal(res) {
            var h = res.header, debit = 0, kredit = 0, rows = '';
            $('#jurnal_kw_no').text(h.no_kwitansi);
            $('#jurnal_kw_tgl').text(tgl(h.tanggal_kwitansi));
            $('#jurnal_kw_cust').text(h.nm_customer);
            $('#jurnal_kw_spk').text(h.no_spk);
            $('#jurnal_kw_company').text(h.nm_company || '-');
            $.each(res.journals, function(i, j) {
                debit += Number(j.debit);
                kredit += Number(j.kredit);
                rows += '<tr><td style="white-space:nowrap">' + tgl(j.tgl_jurnal) + '</td><td>' + esc(j.coa) + '</td><td>' + esc(j.nm_company) + '</td><td>' + esc(j.nm_coa) + '</td><td>' + esc(j.keterangan) + '</td><td class="text-right">' + money(j.debit) + '</td><td class="text-right">' + money(j.kredit) + '</td></tr>';
            });
            if (!rows) {
                rows = '<tr><td colspan="7" class="text-center text-muted">Tidak ada jurnal yang tercatat.</td></tr>';
            }
            rows += '<tr><th colspan="5" class="text-center">Balancing</th><td class="text-right"><b>' + money(debit) + '</b></td><td class="text-right"><b>' + money(kredit) + '</b></td></tr>';
            $('#jurnal_kw_body').html(rows);
            if (h.status === 'LUNAS' && h.no_penerimaan) {
                var link = '<a href="<?= site_url('penerimaan_uang/view_surat?no=') ?>' + encodeURIComponent(h.no_penerimaan) + '">' + esc(h.no_penerimaan) + '</a>';
                $('#jurnal_kw_note').html('Dibayar lewat ' + link + (h.tanggal_bayar ? ' &middot; ' + tgl(h.tanggal_bayar) : '') + ' &middot; Bank ' + money(h.nominal_bank) + '. Jurnal penerimaannya ada di halaman penerimaan tersebut.');
            } else {
                $('#jurnal_kw_note').text('Belum dibayar. Saat transfer masuk, alokasikan dengan jenis Deposit di Alokasi, lalu proses di Penerimaan Piutang › Penerimaan Piutang Kwitansi Deposit.');
            }
            $('#jurnal_kw_content').show();
        }
        $('#table_kwitansi').on('click', '.btn-kwitansi-jurnal', function() {
            $('#jurnal_kw_content, #jurnal_kw_error').hide();
            $('#jurnal_kw_loading').show();
            $('#modalJurnalKwitansi').modal('show');
            $.getJSON('<?= site_url('kwitansi_deposit/journal/') ?>' + encodeURIComponent($(this).data('id')))
                .done(function(res) {
                    $('#jurnal_kw_loading').hide();
                    if (res && res.status === 1) {
                        renderJurnal(res);
                    } else {
                        $('#jurnal_kw_error').text((res && res.msg) || 'Jurnal tidak dapat dimuat.').show();
                    }
                })
                .fail(function() {
                    $('#jurnal_kw_loading').hide();
                    $('#jurnal_kw_error').text('Jurnal tidak dapat dimuat.').show();
                });
        });
        $('#jurnal_kw_note').on('click', 'a', function() {
            $('#modalJurnalKwitansi').modal('hide');
        });
        $('select[name="id_customer"]').select2({
            width: '100%',
            minimumResultsForSearch: 0
        });
        if ($.fn.DataTable.isDataTable('#table_kwitansi')) {
            $('#table_kwitansi').DataTable().destroy();
        }
        // Urutan mockup: tanggal naik lalu nomor (diurutkan server); No. dihitung 1..n sesudah urut.
        var tableKw = $('#table_kwitansi').DataTable({
            ordering: false,
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            language: {
                emptyTable: "Tidak ada kwitansi deposit yang cocok.",
                zeroRecords: "Tidak ada kwitansi deposit yang cocok.",
                lengthMenu: "_MENU_ entries per page",
                search: "Search:",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                infoFiltered: ""
            },
            drawCallback: function() {
                var start = this.api().page.info().start;
                this.api().rows({page: 'current'}).nodes().each(function(tr, i) {
                    $(tr).find('.kd-no').text(start + i + 1);
                });
            }
        });
        // Export mengikuti kotak Search tabel (mockup: Export memakai baris yang tersaring).
        var exportBase = $('#btn_export_kw').attr('href');
        tableKw.on('search.dt', function() {
            $('#btn_export_kw').attr('href', exportBase + '&search=' + encodeURIComponent(tableKw.search()));
        });
    });
</script>
