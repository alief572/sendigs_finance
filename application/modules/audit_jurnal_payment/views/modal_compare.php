<div style="margin-bottom: 20px;">
    <!-- Detail Transaksi Header -->
    <div class="row" style="background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:15px; margin: 0 0 15px 0;">
        <div class="col-md-3 col-sm-6">
            <small class="text-muted" style="font-weight:600;">NO. TRANSAKSI (ID PAYMENT)</small>
            <h5 style="margin:4px 0 0 0; font-weight:700; color:#0073b7;"><?= htmlspecialchars($payment->id) ?></h5>
        </div>
        <div class="col-md-3 col-sm-6">
            <small class="text-muted" style="font-weight:600;">NO. DOKUMEN</small>
            <h5 style="margin:4px 0 0 0; font-weight:700; color:#333;"><?= htmlspecialchars($payment->no_doc ?? '') ?></h5>
        </div>
        <div class="col-md-2 col-sm-4">
            <small class="text-muted" style="font-weight:600;">TIPE / KATEGORI</small>
            <h5 style="margin:4px 0 0 0; font-weight:600; text-transform:capitalize;"><?= htmlspecialchars(str_replace('_', ' ', $payment->tipe ?? '')) ?></h5>
        </div>
        <div class="col-md-2 col-sm-4">
            <small class="text-muted" style="font-weight:600;">TANGGAL BAYAR</small>
            <h5 style="margin:4px 0 0 0; font-weight:600;"><?= !empty($payment->tgl_bayar) ? date('d F Y', strtotime($payment->tgl_bayar)) : '-' ?></h5>
        </div>
        <div class="col-md-2 col-sm-4">
            <small class="text-muted" style="font-weight:600;">TOTAL DIBAYARKAN</small>
            <h5 style="margin:4px 0 0 0; font-weight:700; color:#00a65a;">Rp <?= number_format(floatval($payment->jumlah ?? $payment->total_payment ?? 0)) ?></h5>
        </div>
    </div>

    <!-- Alert Status Temuan Audit -->
    <?php if ($comparison['has_issue']): ?>
    <div class="alert alert-danger" style="border-radius:6px; padding:12px 15px; margin-bottom:18px;">
        <h5 style="margin:0 0 6px 0; font-weight:700;"><i class="fa fa-exclamation-triangle"></i> Ditemukan Ketidaksesuaian Data:</h5>
        <ul style="margin:0; padding-left:20px; font-size:13px;">
            <?php foreach ($comparison['issue_details'] as $detail): ?>
                <li><?= htmlspecialchars($detail) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php else: ?>
    <div class="alert alert-success" style="border-radius:6px; padding:12px 15px; margin-bottom:18px;">
        <i class="fa fa-check-circle"></i> <strong>Data Sesuai!</strong> Jurnal eksisting sudah balance, memiliki susunan akun yang tepat, dan suffix referensi lengkap.
    </div>
    <?php endif; ?>

    <!-- Side-by-side Tables -->
    <div class="row">
        <!-- Sisi Kiri: Eksisting -->
        <div class="col-md-6">
            <div style="background:#fff; border:1px solid #cbd5e1; border-radius:8px; overflow:hidden;">
                <div style="background:#e2e8f0; padding:10px 14px; border-bottom:1px solid #cbd5e1; display:flex; justify-content:space-between; align-items:center;">
                    <strong style="color:#334155;"><i class="fa fa-database text-danger"></i> 1. Data Eksisting di tr_jurnal</strong>
                    <?php if ($comparison['is_balanced']): ?>
                        <span class="label label-success">Balance</span>
                    <?php else: ?>
                        <span class="label label-danger">Tidak Balance</span>
                    <?php endif; ?>
                </div>
                <div class="table-responsive" style="max-height:380px; overflow-y:auto;">
                    <table class="table table-bordered table-striped" style="font-size:12px; margin-bottom:0;">
                        <thead>
                            <tr style="background:#f1f5f9;">
                                <th width="18%" class="text-center">No. COA</th>
                                <th width="42%">Keterangan</th>
                                <th width="20%" class="text-right">Debit</th>
                                <th width="20%" class="text-right">Kredit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $ttl_deb_ex = 0;
                            $ttl_krd_ex = 0;
                            if (!empty($existing_jurnal)):
                                foreach ($existing_jurnal as $ej):
                                    $ttl_deb_ex += $ej->debit;
                                    $ttl_krd_ex += $ej->kredit;
                            ?>
                                    <tr>
                                        <td class="text-center"><strong><?= htmlspecialchars($ej->coa) ?></strong></td>
                                        <td>
                                            <?= htmlspecialchars($ej->keterangan) ?>
                                            <div style="font-size:11px; color:#888;"><?= htmlspecialchars($ej->nm_coa) ?></div>
                                        </td>
                                        <td class="text-right"><?= number_format($ej->debit) ?></td>
                                        <td class="text-right"><?= number_format($ej->kredit) ?></td>
                                    </tr>
                            <?php
                                endforeach;
                            else:
                            ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted" style="padding:25px;">
                                        <em>Belum ada ayat jurnal yang terbentuk di tabel tr_jurnal.</em>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot style="background:#f8fafc; font-weight:bold;">
                            <tr>
                                <th colspan="2" class="text-right">TOTAL:</th>
                                <th class="text-right" style="color:<?= $comparison['is_balanced'] ? '#00a65a' : '#dd4b39' ?>;"><?= number_format($ttl_deb_ex) ?></th>
                                <th class="text-right" style="color:<?= $comparison['is_balanced'] ? '#00a65a' : '#dd4b39' ?>;"><?= number_format($ttl_krd_ex) ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sisi Kanan: Seharusnya -->
        <div class="col-md-6">
            <div style="background:#fff; border:2px solid #3c8dbc; border-radius:8px; overflow:hidden;">
                <div style="background:#3c8dbc; color:#fff; padding:10px 14px; display:flex; justify-content:space-between; align-items:center;">
                    <strong><i class="fa fa-check-circle"></i> 2. Susunan Jurnal Yang Seharusnya (Standar)</strong>
                    <span class="label label-success" style="background:#00a65a;">Verified Balance</span>
                </div>
                <div class="table-responsive" style="max-height:380px; overflow-y:auto;">
                    <table class="table table-bordered table-striped" style="font-size:12px; margin-bottom:0;">
                        <thead>
                            <tr style="background:#f1f5f9;">
                                <th width="18%" class="text-center">No. COA</th>
                                <th width="42%">Keterangan & Suffix</th>
                                <th width="20%" class="text-right">Debit</th>
                                <th width="20%" class="text-right">Kredit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $ttl_deb_exp = 0;
                            $ttl_krd_exp = 0;
                            if (!empty($expected_jurnal)):
                                foreach ($expected_jurnal as $exp_item):
                                    $ttl_deb_exp += $exp_item['debit'];
                                    $ttl_krd_exp += $exp_item['kredit'];
                            ?>
                                    <tr>
                                        <td class="text-center"><strong style="color:#0073b7;"><?= htmlspecialchars($exp_item['coa']) ?></strong></td>
                                        <td>
                                            <?= htmlspecialchars($exp_item['keterangan']) ?>
                                            <div style="font-size:11px; color:#666;"><?= htmlspecialchars($exp_item['nm_coa']) ?></div>
                                        </td>
                                        <td class="text-right" style="font-weight:600;"><?= number_format($exp_item['debit']) ?></td>
                                        <td class="text-right" style="font-weight:600;"><?= number_format($exp_item['kredit']) ?></td>
                                    </tr>
                            <?php
                                endforeach;
                            endif;
                            ?>
                        </tbody>
                        <tfoot style="background:#f8fafc; font-weight:bold;">
                            <tr>
                                <th colspan="2" class="text-right">TOTAL:</th>
                                <th class="text-right" style="color:#00a65a; font-size:13px;"><?= number_format($ttl_deb_exp) ?></th>
                                <th class="text-right" style="color:#00a65a; font-size:13px;"><?= number_format($ttl_krd_exp) ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tombol Perbaiki Modal Footer Action -->
    <?php if ($comparison['has_issue']): ?>
    <div style="margin-top:20px; text-align:right; border-top:1px solid #e2e8f0; padding-top:15px;">
        <button type="button" class="btn btn-success btn_fix_single" data-id="<?= $payment->id ?>" data-doc="<?= htmlspecialchars($payment->no_doc, ENT_QUOTES) ?>" style="font-size:14px; padding:8px 18px;">
            <i class="fa fa-wrench"></i> Terapkan Perbaikan Jurnal Untuk Transaksi Ini
        </button>
    </div>
    <?php endif; ?>
</div>
