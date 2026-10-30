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
                        <td><?= date('d F Y', strtotime($results['header']['tanggal_kwitansi'])) ?></td>
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
                        <td><strong style="font-size: 16px; color: #0284c7;">Rp <?= number_format((float)$results['header']['nilai_kwitansi'], 2) ?></strong></td>
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
                            <?= !empty($results['header']['no_penerimaan']) ? htmlspecialchars($results['header']['no_penerimaan']) : '<span class="text-muted">Belum ada pelunasan bank</span>' ?>
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
            <i class="fa fa-book"></i> Jurnal Akrual Kwitansi Deposit
        </h4>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr style="background: #f8fafc;">
                        <th width="150">No Jurnal</th>
                        <th width="120">Tanggal</th>
                        <th width="120">No Akun (COA)</th>
                        <th>Nama Akun</th>
                        <th>Keterangan</th>
                        <th width="150" class="text-right">Debit (Rp)</th>
                        <th width="150" class="text-right">Kredit (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totDebit = 0; $totKredit = 0;
                    if (!empty($results['journals'])): 
                    ?>
                        <?php foreach ($results['journals'] as $j): 
                            $totDebit += (float)$j['debit'];
                            $totKredit += (float)$j['kredit'];
                        ?>
                            <tr>
                                <td><code><?= htmlspecialchars($j['no_jurnal']) ?></code></td>
                                <td><?= date('d-M-Y', strtotime($j['tgl_jurnal'])) ?></td>
                                <td><code><?= htmlspecialchars($j['coa']) ?></code></td>
                                <td><strong><?= htmlspecialchars($j['nm_coa']) ?></strong></td>
                                <td><?= htmlspecialchars($j['keterangan']) ?></td>
                                <td class="text-right font-tabular"><?= number_format((float)$j['debit'], 2) ?></td>
                                <td class="text-right font-tabular"><?= number_format((float)$j['kredit'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr style="background: #f1f5f9; font-weight: 700;">
                            <td colspan="5" class="text-right">TOTAL:</td>
                            <td class="text-right font-tabular"><?= number_format($totDebit, 2) ?></td>
                            <td class="text-right font-tabular"><?= number_format($totKredit, 2) ?></td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted" style="padding: 16px;">Tidak ada jurnal akrual yang tercatat.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
