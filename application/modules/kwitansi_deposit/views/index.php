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
        background-color: #f8fafc;
        font-weight: 700;
        font-size: 12px;
        color: #334155;
        border-bottom: 2px solid #e2e8f0 !important;
        white-space: nowrap;
    }
    .table_list td {
        vertical-align: middle !important;
        font-size: 13px;
    }
    .font-tabular {
        font-variant-numeric: tabular-nums;
    }
    .kwitansi-actions {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .kwitansi-actions .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        min-height: 30px;
        padding: 5px 10px;
    }
</style>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title" style="font-weight: 700; color: #1e293b;">
            <i class="fa fa-list"></i> Daftar Tagihan Kwitansi Deposit Customer
        </h3>
        <div class="box-tools pull-right">
            <a href="<?= site_url('kwitansi_deposit/add') ?>" class="btn btn-sm btn-primary">
                <i class="fa fa-plus"></i> Buat Kwitansi Deposit
            </a>
            <a href="<?= site_url('kwitansi_deposit/export_excel?' . http_build_query($results['filters'])) ?>" class="btn btn-sm btn-success">
                <i class="fa fa-file-excel-o"></i> Export Excel
            </a>
        </div>
    </div>
    <div class="box-body">
        <div class="alert alert-info" style="font-size: 12px; padding: 10px 14px;">
            Kwitansi Deposit dibuat di sini (terikat ke SPK). Pembayarannya <strong>tidak diproses di halaman ini</strong>: transfer dari rekening koran dialokasikan dengan jenis <strong>Deposit</strong> di <a href="<?= site_url('alokasi') ?>">Alokasi</a> (pilih kwitansinya), lalu diproses di <a href="<?= site_url('penerimaan_uang') ?>#tab_kwitansi">Penerimaan Piutang › Penerimaan Piutang Kwitansi Deposit</a>. Status kwitansi dan <a href="<?= site_url('deposit_customer') ?>">Monitoring Deposit</a> ikut berubah otomatis. Saat ini ada <strong><?= (int)($results['summary']['count_menunggu'] ?? 0) ?></strong> kwitansi belum lunas pada daftar ini.
        </div>
        <form method="get" action="<?= site_url('kwitansi_deposit') ?>" class="well well-sm">
            <div class="row">
                <div class="col-md-3">
                    <label>Customer</label>
                    <select name="id_customer" class="form-control input-sm select2">
                        <option value="">-- Semua Customer --</option>
                        <?php foreach ($results['customers'] as $c): ?>
                            <option value="<?= htmlspecialchars($c['id_customer']) ?>" <?= ($results['filters']['id_customer'] === $c['id_customer']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['nm_customer']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Status</label>
                    <select name="status" class="form-control input-sm">
                        <option value="">-- Semua Status --</option>
                        <option value="MENUNGGU_PEMBAYARAN" <?= ($results['filters']['status'] === 'MENUNGGU_PEMBAYARAN') ? 'selected' : '' ?>>Menunggu Pembayaran</option>
                        <option value="LUNAS" <?= ($results['filters']['status'] === 'LUNAS') ? 'selected' : '' ?>>Lunas</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Pencarian</label>
                    <input type="text" name="search" class="form-control input-sm" placeholder="No Kwitansi / No SPK / Customer..." value="<?= htmlspecialchars($results['filters']['search'] ?? '') ?>">
                </div>
                <div class="col-md-2" style="padding-top: 24px;">
                    <button type="submit" class="btn btn-sm btn-info btn-filter" style="width: 58%;"><i class="fa fa-filter"></i> Filter</button>
                    <a href="<?= site_url('kwitansi_deposit') ?>" class="btn btn-sm btn-danger" style="width: 38%;" title="Hapus semua filter"><i class="fa fa-refresh"></i> Reset</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table_list" id="table_kwitansi">
                <thead>
                    <tr>
                        <th class="text-center" width="40">No</th>
                        <th class="text-center">Tanggal</th>
                        <th class="text-center">No Kwitansi</th>
                        <th>Customer</th>
                        <th>No SPK</th>
                        <th>Company</th>
                        <th class="text-right">Nilai Kwitansi</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">No Penerimaan</th>
                        <th class="text-center" width="260">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($results['kwitansiList'])): ?>
                        <?php $no = 1; foreach ($results['kwitansiList'] as $row): ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td class="text-center nowrap"><?= date('d-M-Y', strtotime($row['tanggal_kwitansi'])) ?></td>
                                <td class="text-center font-bold" style="font-family: monospace; font-size: 13px;">
                                    <?= htmlspecialchars($row['no_kwitansi']) ?>
                                </td>
                                <td><strong><?= htmlspecialchars($row['nm_customer']) ?></strong></td>
                                <td><?= htmlspecialchars($row['no_spk'] ?? $row['id_spk_penawaran'] ?? $row['po_customer'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['nm_company'] ?? '-') ?></td>
                                <td class="text-right font-tabular font-bold">
                                    Rp <?= number_format((float)$row['nilai_kwitansi'], 2) ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($row['status'] === 'MENUNGGU_PEMBAYARAN'): ?>
                                        <span class="badge-status badge-menunggu">Menunggu Pembayaran</span>
                                    <?php else: ?>
                                        <span class="badge-status badge-lunas">Lunas</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center nowrap font-tabular">
                                    <?php if (!empty($row['no_penerimaan'])): ?>
                                        <?= htmlspecialchars($row['no_penerimaan']) ?>
                                        <?php if (!empty($row['tanggal_bayar']) && $row['tanggal_bayar'] !== '0000-00-00'): ?>
                                            <small class="text-muted">· <?= date('d-M-Y', strtotime($row['tanggal_bayar'])) ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center nowrap">
                                    <div class="kwitansi-actions">
                                    <a href="<?= site_url('kwitansi_deposit/detail/' . $row['id']) ?>" class="btn btn-sm btn-info" title="Lihat detail kwitansi">
                                        <i class="fa fa-eye"></i> Detail
                                    </a>
                                    <?php if ($row['status'] === 'MENUNGGU_PEMBAYARAN' && !empty($row['draft_split_id'])): ?>
                                        <a href="<?= site_url('penerimaan_uang/add_penerimaan_uang/' . (int)$row['draft_split_id']) ?>" class="btn btn-sm btn-primary" title="Proses draft penerimaan untuk kwitansi ini">
                                            <i class="fa fa-bank"></i> Proses Penerimaan
                                        </a>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-sm btn-default btn-kwitansi-jurnal" data-id="<?= (int)$row['id'] ?>" title="Lihat jurnal kwitansi">
                                        <i class="fa fa-book"></i> Jurnal
                                    </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalJurnalKwitansi" tabindex="-1" role="dialog" aria-labelledby="modalJurnalKwitansiTitle">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modalJurnalKwitansiTitle">Jurnal Kwitansi Deposit</h4>
            </div>
            <div class="modal-body">
                <div id="jurnal_kw_loading" class="text-muted">Memuat jurnal...</div>
                <div id="jurnal_kw_error" class="alert alert-danger" style="display: none;"></div>
                <div id="jurnal_kw_content" style="display: none;">
                    <div class="row" style="margin-bottom: 10px;">
                        <div class="col-md-4"><small class="text-muted">No Kwitansi</small><div id="jurnal_kw_no"></div></div>
                        <div class="col-md-4"><small class="text-muted">Tanggal</small><div id="jurnal_kw_tgl"></div></div>
                        <div class="col-md-4"><small class="text-muted">Customer</small><div id="jurnal_kw_cust"></div></div>
                    </div>
                    <div class="row" style="margin-bottom: 10px;">
                        <div class="col-md-4"><small class="text-muted">No SPK</small><div id="jurnal_kw_spk"></div></div>
                        <div class="col-md-4"><small class="text-muted">Company</small><div id="jurnal_kw_company"></div></div>
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
                    <p class="text-muted" id="jurnal_kw_note" style="margin-top: 8px;"></p>
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
        function esc(value) {
            return $('<div>').text(value === null || value === undefined ? '' : String(value)).html();
        }
        function money(value) {
            return Number(value || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }
        function tgl(value) {
            var d = new Date(String(value).replace(' ', 'T'));
            if (isNaN(d.getTime())) return esc(value);
            return ('0' + d.getDate()).slice(-2) + '-' + ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'][d.getMonth()] + '-' + d.getFullYear();
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
                rows += '<tr><td class="nowrap">' + tgl(j.tgl_jurnal) + '</td><td>' + esc(j.coa) + '</td><td>' + esc(j.nm_company) + '</td><td>' + esc(j.nm_coa) + '</td><td>' + esc(j.keterangan) + '</td><td class="text-right">' + money(j.debit) + '</td><td class="text-right">' + money(j.kredit) + '</td></tr>';
            });
            if (!rows) {
                rows = '<tr><td colspan="7" class="text-center text-muted">Tidak ada jurnal yang tercatat.</td></tr>';
            }
            rows += '<tr style="background: #f1f5f9; font-weight: 700;"><td colspan="5" class="text-center">Balancing</td><td class="text-right">' + money(debit) + '</td><td class="text-right">' + money(kredit) + '</td></tr>';
            $('#jurnal_kw_body').html(rows);
            $('#jurnal_kw_note').text(h.status === 'LUNAS' && h.no_penerimaan
                ? 'Dibayar lewat ' + h.no_penerimaan + '. Jurnal penerimaannya ada di halaman penerimaan tersebut.'
                : 'Belum dibayar. Saat transfer masuk, alokasikan dengan jenis Deposit di Alokasi, lalu proses di Penerimaan Piutang.');
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
        $('select[name="id_customer"]').select2({
            width: '100%',
            minimumResultsForSearch: 0
        });
        if ($.fn.DataTable.isDataTable('#table_kwitansi')) {
            $('#table_kwitansi').DataTable().destroy();
        }
        $('#table_kwitansi').DataTable({
            order: [[1, 'desc']],
            pageLength: 25,
            responsive: true,
            language: {
                emptyTable: "Belum ada tagihan kwitansi deposit."
            }
        });
    });
</script>
