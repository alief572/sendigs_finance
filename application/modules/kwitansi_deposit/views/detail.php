<style>
    .info-table th {
        width: 180px;
        background: #f8fafc;
        color: #475569;
        font-weight: 600;
        font-size: 13px;
    }
    .info-table td {
        font-size: 13px;
        color: #1e293b;
    }
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
</style>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title" style="font-weight: 700; color: #1e293b;">
            <i class="fa fa-file-text-o"></i> Detail Kwitansi Deposit: <?= htmlspecialchars($results['header']['no_kwitansi']) ?>
        </h3>
        <div class="box-tools pull-right">
            <a href="<?= site_url('kwitansi_deposit') ?>" class="btn btn-sm btn-default">
                <i class="fa fa-arrow-left"></i> Kembali ke Daftar
            </a>
            <?php if ($results['header']['status'] === 'MENUNGGU_PEMBAYARAN'): ?>
                <a href="<?= site_url('penerimaan_uang') ?>" class="btn btn-sm btn-primary">
                    <i class="fa fa-money"></i> Proses di Penerimaan Piutang
                </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered info-table">
                    <tr>
                        <th>No Kwitansi</th>
                        <td><strong style="font-family: monospace; font-size: 14px;"><?= htmlspecialchars($results['header']['no_kwitansi']) ?></strong></td>
                    </tr>
                    <tr>
                        <th>Tanggal Terbit</th>
                        <td><?= htmlspecialchars(mk_fdate($results['header']['tanggal_kwitansi'])) ?></td>
                    </tr>
                    <tr>
                        <th>Customer</th>
                        <td>
                            <strong><?= htmlspecialchars($results['header']['nm_customer']) ?></strong>
                            <small class="text-muted">(<?= htmlspecialchars($results['header']['id_customer']) ?>)</small>
                        </td>
                    </tr>
                    <tr>
                        <th>No SPK</th>
                        <td><?= htmlspecialchars($results['header']['no_spk'] ?? $results['header']['id_spk_penawaran'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <th>No PO</th>
                        <td><?= !empty($results['header']['po_customer']) ? htmlspecialchars($results['header']['po_customer']) : '-' ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-bordered info-table">
                    <tr>
                        <th>Nilai Kwitansi</th>
                        <td><strong style="font-size: 16px;"><?= mk_en($results['header']['nilai_kwitansi']) ?></strong></td>
                    </tr>
                    <tr>
                        <th>Status Tagihan</th>
                        <td>
                            <?php if ($results['header']['status'] === 'MENUNGGU_PEMBAYARAN'): ?>
                                <span class="badge-status badge-menunggu">Menunggu Pembayaran</span>
                            <?php else: ?>
                                <span class="badge-status badge-lunas">Lunas</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>No Penerimaan Bank</th>
                        <td>
                            <?= !empty($results['header']['no_penerimaan']) ? '<a href="' . site_url('penerimaan_uang/view_surat?no=' . urlencode($results['header']['no_penerimaan'])) . '">' . htmlspecialchars($results['header']['no_penerimaan']) . '</a>' : '<span class="text-muted">Belum ada pelunasan bank</span>' ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Keterangan</th>
                        <td><?= htmlspecialchars($results['header']['keterangan'] ?? '-') ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <h4 style="font-weight: 700; margin-top: 25px; margin-bottom: 15px; color: #1e293b;">
            Jurnal Kwitansi Deposit
        </h4>
        <div class="table-responsive">
            <table class="table">
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
                <tbody>
                    <?php
                    $totDebit = 0; $totKredit = 0;
                    if (!empty($results['journals'])):
                        foreach ($results['journals'] as $j):
                            $totDebit += (float)$j['debit'];
                            $totKredit += (float)$j['kredit'];
                    ?>
                            <tr>
                                <td style="white-space: nowrap;"><?= htmlspecialchars(mk_fdate($j['tgl_jurnal'])) ?></td>
                                <td><?= htmlspecialchars($j['coa']) ?></td>
                                <td><?= htmlspecialchars($j['nm_company'] ?? '') ?></td>
                                <td><?= htmlspecialchars($j['nm_coa']) ?></td>
                                <td><?= htmlspecialchars($j['keterangan']) ?></td>
                                <td class="text-right"><?= mk_en($j['debit']) ?></td>
                                <td class="text-right"><?= mk_en($j['kredit']) ?></td>
                            </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted" style="padding: 16px;">Tidak ada jurnal yang tercatat.</td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <td colspan="5" style="text-align: center;"><b>Balancing</b></td>
                        <td class="text-right"><b><?= mk_en($totDebit) ?></b></td>
                        <td class="text-right"><b><?= mk_en($totKredit) ?></b></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
