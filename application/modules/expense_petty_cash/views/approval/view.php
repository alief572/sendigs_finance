<?php

/**
 * View Detail Pelaporan untuk Approval - Enhanced UI
 *
 * PHP vars:
 *   $pelaporan           - object with header + pencatatan_list
 *   $pencatatan_details  - array keyed by pencatatan_id with detail items + evidences
 *   $budget_info         - object (budget, budget_terpakai, sisa_budget) or false
 *   $coa_list            - array of COA objects
 */

$header = isset($pelaporan->header) ? $pelaporan->header : $pelaporan;
$pencatatan_list = isset($pelaporan->pencatatan_list) ? $pelaporan->pencatatan_list : [];
$jumlah_item = count($pencatatan_list);

$periode_start_fmt = date('d/m/Y', strtotime($header->periode_start));
$periode_end_fmt   = date('d/m/Y', strtotime($header->periode_end));

$sisa_budget = isset($budget_info->sisa_budget) ? $budget_info->sisa_budget : 0;

$coa_map = [];
if (!empty($coa_list)) {
    foreach ($coa_list as $coa) {
        $coa_map[$coa->coa_code] = $coa->coa_code . ' - ' . (isset($coa->coa_nama) ? $coa->coa_nama : '');
    }
}
?>

<style>
    .approval-summary-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 8px;
        padding: 20px 25px;
        color: #fff;
        margin-bottom: 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
    }

    .approval-summary-card .card-row {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 15px;
    }

    .approval-summary-card .card-item {
        flex: 1;
        min-width: 130px;
    }

    .approval-summary-card .card-item .label-text {
        font-size: 11px;
        text-transform: uppercase;
        opacity: 0.75;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .approval-summary-card .card-item .value-text {
        font-size: 18px;
        font-weight: 700;
    }

    .approval-summary-card .card-item .value-text.text-sm {
        font-size: 14px;
    }

    .pencatatan-panel {
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        margin-bottom: 15px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    .pencatatan-panel .panel-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 12px 18px;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #e0e0e0;
        transition: background 0.2s;
    }

    .pencatatan-panel .panel-header:hover {
        background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
    }

    .pencatatan-panel .panel-header .panel-title-text {
        font-weight: 600;
        font-size: 13px;
        color: #333;
    }

    .pencatatan-panel .panel-header .panel-amount {
        font-weight: 700;
        font-size: 14px;
        color: #2d6a4f;
    }

    .pencatatan-panel .panel-header .panel-meta {
        font-size: 11px;
        color: #666;
        margin-top: 2px;
    }

    .pencatatan-panel .panel-body-content {
        padding: 15px 18px;
        background: #fff;
    }

    .jurnal-section {
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px dashed #dee2e6;
    }

    .jurnal-label {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .jurnal-label-stm {
        background: #d1ecf1;
        color: #0c5460;
    }

    .jurnal-label-company {
        background: #fff3cd;
        color: #856404;
    }

    .jurnal-label-interco {
        background: #cce5ff;
        color: #004085;
    }

    .jurnal-table {
        font-size: 11px;
        margin-bottom: 10px;
    }

    .jurnal-table thead th {
        background: #f8f9fa;
        font-weight: 600;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        padding: 6px 8px !important;
    }

    .jurnal-table tbody td {
        padding: 5px 8px !important;
    }

    .jurnal-table .credit-row {
        background: #fffbeb;
    }

    .jurnal-table tfoot td {
        font-weight: 700;
        background: #f1f3f5;
        padding: 6px 8px !important;
    }

    .detail-item-table {
        font-size: 12px;
    }

    .detail-item-table thead th {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        background: #f1f3f5;
        padding: 7px 10px !important;
    }

    .detail-item-table tbody td {
        padding: 6px 10px !important;
    }

    .detail-item-table tfoot th {
        background: #e8f5e9;
    }

    .toggle-icon {
        transition: transform 0.2s;
        color: #999;
    }

    .collapsed .toggle-icon {
        transform: rotate(-90deg);
    }

    .approval-actions {
        text-align: center;
        padding: 25px 20px;
        background: #f8f9fa;
        border-top: 2px solid #e9ecef;
    }

    .btn-approve-lg,
    .btn-reject-lg {
        padding: 12px 35px;
        font-size: 15px;
        font-weight: 600;
        border-radius: 6px;
        margin: 0 8px;
    }

    .btn-approve-lg {
        box-shadow: 0 3px 10px rgba(0, 166, 101, 0.3);
    }

    .btn-reject-lg {
        box-shadow: 0 3px 10px rgba(220, 53, 69, 0.2);
    }

    .evidence-link {
        display: inline-block;
        padding: 2px 6px;
        background: #e3f2fd;
        border-radius: 3px;
        margin: 1px 2px;
        font-size: 10px;
        color: #1565c0;
        text-decoration: none;
    }

    .evidence-link:hover {
        background: #bbdefb;
        text-decoration: none;
    }
</style>

<!-- Summary Card -->
<div class="approval-summary-card">
    <div class="card-row">
        <div class="card-item">
            <div class="label-text">No Pelaporan</div>
            <div class="value-text text-sm"><?= htmlspecialchars($header->no_pelaporan) ?></div>
        </div>
        <div class="card-item">
            <div class="label-text">Periode</div>
            <div class="value-text text-sm"><?= $periode_start_fmt ?> — <?= $periode_end_fmt ?></div>
        </div>
        <div class="card-item">
            <div class="label-text">Company</div>
            <div class="value-text text-sm"><?= htmlspecialchars($header->company) ?></div>
        </div>
        <div class="card-item">
            <div class="label-text">Grand Total</div>
            <div class="value-text">Rp <?= number_format($header->grand_total, 0, ',', '.') ?></div>
        </div>
        <div class="card-item">
            <div class="label-text">Pencatatan</div>
            <div class="value-text text-sm"><?= $jumlah_item ?> item</div>
        </div>
        <div class="card-item">
            <div class="label-text">Sisa Budget</div>
            <div class="value-text text-sm" style="<?= ($sisa_budget < 0) ? 'color:#ff6b6b;' : '' ?>">Rp <?= number_format($sisa_budget, 0, ',', '.') ?></div>
        </div>
    </div>
</div>

<!-- Detail Per Pencatatan -->
<div class="box box-solid">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-list-alt"></i>&nbsp;Detail Pencatatan</h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" id="btn-expand-all" title="Expand All">
                <i class="fa fa-expand"></i> Expand All
            </button>
        </div>
    </div>
    <div class="box-body" style="padding: 15px;">
        <?php if (!empty($pencatatan_list)) : ?>
            <?php $no = 1; ?>
            <?php foreach ($pencatatan_list as $pencatatan) : ?>
                <?php
                $details = isset($pencatatan_details[$pencatatan->id]) ? $pencatatan_details[$pencatatan->id] : [];
                $panel_id = 'panel-' . $pencatatan->id;
                $jurnal_company = $header->company;
                $jurnal_tanggal = date('d/m/Y', strtotime($pencatatan->tanggal));
                $jurnal_total = (float) $pencatatan->grand_total;
                ?>
                <div class="pencatatan-panel">
                    <div class="panel-header" data-toggle="collapse" data-target="#<?= $panel_id ?>">
                        <div>
                            <div class="panel-title-text">
                                <i class="fa fa-file-text-o"></i>&nbsp;
                                <?= $no++ ?>. <?= htmlspecialchars($pencatatan->no_pencatatan) ?>
                            </div>
                            <div class="panel-meta">
                                <i class="fa fa-calendar-o"></i> <?= date('d/m/Y', strtotime($pencatatan->tanggal)) ?>
                                &nbsp;&bull;&nbsp;
                                <i class="fa fa-user"></i> <?= htmlspecialchars($pencatatan->request_by) ?>
                                <?php if (!empty($pencatatan->keterangan)) : ?>
                                    &nbsp;&bull;&nbsp;
                                    <i class="fa fa-comment-o"></i> <?= htmlspecialchars(mb_substr($pencatatan->keterangan, 0, 50)) ?><?= mb_strlen($pencatatan->keterangan) > 50 ? '...' : '' ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span class="panel-amount">Rp <?= number_format($pencatatan->grand_total, 0, ',', '.') ?></span>
                            <i class="fa fa-chevron-down toggle-icon"></i>
                        </div>
                    </div>
                    <div class="collapse in" id="<?= $panel_id ?>">
                        <div class="panel-body-content">
                            <!-- Detail Item Table -->
                            <table class="table table-bordered detail-item-table">
                                <thead>
                                    <tr>
                                        <th width="30" class="text-center">#</th>
                                        <th>COA</th>
                                        <th>Pengeluaran</th>
                                        <th>Spesifikasi</th>
                                        <th class="text-center" width="55">Qty</th>
                                        <th class="text-right" width="90">Nominal</th>
                                        <th class="text-right" width="90">Total</th>
                                        <th width="130">Evidence</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($details)) : ?>
                                        <?php $d_no = 1;
                                        foreach ($details as $detail) : ?>
                                            <tr>
                                                <td class="text-center"><?= $d_no++ ?></td>
                                                <td><?= isset($coa_map[$detail->coa_code]) ? htmlspecialchars($coa_map[$detail->coa_code]) : htmlspecialchars($detail->coa_code) ?></td>
                                                <td><?= htmlspecialchars($detail->pengeluaran) ?></td>
                                                <td><?= htmlspecialchars($detail->spesifikasi) ?></td>
                                                <td class="text-center"><?= $detail->jumlah ?></td>
                                                <td class="text-right"><?= number_format($detail->nominal, 0, ',', '.') ?></td>
                                                <td class="text-right"><?= number_format($detail->total, 0, ',', '.') ?></td>
                                                <td>
                                                    <?php if (isset($detail->evidences) && !empty($detail->evidences)) : ?>
                                                        <?php foreach ($detail->evidences as $ev) : ?>
                                                            <a href="<?= base_url('assets/expense_petty_cash/' . $ev->encrypted_name) ?>" target="_blank" class="evidence-link" title="<?= htmlspecialchars($ev->original_name) ?>">
                                                                <i class="fa fa-file"></i> <?= htmlspecialchars(strlen($ev->original_name) > 12 ? substr($ev->original_name, 0, 12) . '...' : $ev->original_name) ?>
                                                            </a>
                                                        <?php endforeach; ?>
                                                    <?php else : ?>
                                                        <span class="text-muted" style="font-size: 11px;">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted"><em>Detail item tidak tersedia</em></td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="6" class="text-right">Total</th>
                                        <th class="text-right"><?= number_format($pencatatan->grand_total, 0, ',', '.') ?></th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>

                            <!-- Preview Jurnal -->
                            <div class="jurnal-section">
                                <h5 style="font-size: 12px; font-weight: 600; color: #555; margin-bottom: 10px;">
                                    <i class="fa fa-book"></i>&nbsp;Preview Jurnal
                                </h5>

                                <?php if ($jurnal_company === 'STM') : ?>
                                    <span class="jurnal-label jurnal-label-stm"><i class="fa fa-building"></i> Jurnal STM</span>
                                    <table class="table table-bordered jurnal-table">
                                        <thead>
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>COA</th>
                                                <th>Nama Account</th>
                                                <th>Company</th>
                                                <th class="text-right">Debit</th>
                                                <th class="text-right">Kredit</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($details)) : foreach ($details as $d) :
                                                    $coa_nama = isset($coa_map[$d->coa_code]) ? $coa_map[$d->coa_code] : $d->coa_code;
                                            ?>
                                                    <tr>
                                                        <td><?= $jurnal_tanggal ?></td>
                                                        <td><?= htmlspecialchars($d->coa_code) ?></td>
                                                        <td><?= htmlspecialchars($coa_nama) ?></td>
                                                        <td>STM</td>
                                                        <td class="text-right"><?= number_format($d->total, 0, ',', '.') ?></td>
                                                        <td class="text-right">-</td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <tr class="credit-row">
                                                    <td><?= $jurnal_tanggal ?></td>
                                                    <td>1101-01-02</td>
                                                    <td>Kas Kecil</td>
                                                    <td>STM</td>
                                                    <td class="text-right">-</td>
                                                    <td class="text-right"><?= number_format($jurnal_total, 0, ',', '.') ?></td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="4" class="text-right">Balancing</td>
                                                <td class="text-right"><?= number_format($jurnal_total, 0, ',', '.') ?></td>
                                                <td class="text-right"><?= number_format($jurnal_total, 0, ',', '.') ?></td>
                                            </tr>
                                        </tfoot>
                                    </table>

                                <?php elseif ($jurnal_company === 'VUCA' || $jurnal_company === 'SUSTAIN') : ?>
                                    <?php
                                    $coa_hutang = ($jurnal_company === 'VUCA') ? '2103-01-01' : '2103-01-02';
                                    $coa_piutang = ($jurnal_company === 'VUCA') ? '1103-01-01' : '1103-01-02';
                                    ?>
                                    <span class="jurnal-label jurnal-label-company"><i class="fa fa-building"></i> Sisi <?= $jurnal_company ?></span>
                                    <table class="table table-bordered jurnal-table">
                                        <thead>
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>COA</th>
                                                <th>Nama Account</th>
                                                <th>Company</th>
                                                <th class="text-right">Debit</th>
                                                <th class="text-right">Kredit</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($details)) : foreach ($details as $d) :
                                                    $coa_nama = isset($coa_map[$d->coa_code]) ? $coa_map[$d->coa_code] : $d->coa_code;
                                            ?>
                                                    <tr>
                                                        <td><?= $jurnal_tanggal ?></td>
                                                        <td><?= htmlspecialchars($d->coa_code) ?></td>
                                                        <td><?= htmlspecialchars($coa_nama) ?></td>
                                                        <td><?= $jurnal_company ?></td>
                                                        <td class="text-right"><?= number_format($d->total, 0, ',', '.') ?></td>
                                                        <td class="text-right">-</td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <tr class="credit-row">
                                                    <td><?= $jurnal_tanggal ?></td>
                                                    <td><?= $coa_hutang ?></td>
                                                    <td>Hutang ke STM</td>
                                                    <td><?= $jurnal_company ?></td>
                                                    <td class="text-right">-</td>
                                                    <td class="text-right"><?= number_format($jurnal_total, 0, ',', '.') ?></td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="4" class="text-right">Balancing</td>
                                                <td class="text-right"><?= number_format($jurnal_total, 0, ',', '.') ?></td>
                                                <td class="text-right"><?= number_format($jurnal_total, 0, ',', '.') ?></td>
                                            </tr>
                                        </tfoot>
                                    </table>

                                    <span class="jurnal-label jurnal-label-interco" style="margin-top: 8px;"><i class="fa fa-building"></i> Sisi STM (Inter-Company)</span>
                                    <table class="table table-bordered jurnal-table">
                                        <thead>
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>COA</th>
                                                <th>Nama Account</th>
                                                <th>Company</th>
                                                <th class="text-right">Debit</th>
                                                <th class="text-right">Kredit</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><?= $jurnal_tanggal ?></td>
                                                <td><?= $coa_piutang ?></td>
                                                <td>Piutang <?= $jurnal_company ?></td>
                                                <td>STM</td>
                                                <td class="text-right"><?= number_format($jurnal_total, 0, ',', '.') ?></td>
                                                <td class="text-right">-</td>
                                            </tr>
                                            <tr class="credit-row">
                                                <td><?= $jurnal_tanggal ?></td>
                                                <td>1101-01-02</td>
                                                <td>Kas Kecil</td>
                                                <td>STM</td>
                                                <td class="text-right">-</td>
                                                <td class="text-right"><?= number_format($jurnal_total, 0, ',', '.') ?></td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="4" class="text-right">Balancing</td>
                                                <td class="text-right"><?= number_format($jurnal_total, 0, ',', '.') ?></td>
                                                <td class="text-right"><?= number_format($jurnal_total, 0, ',', '.') ?></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="callout callout-warning">
                <h4><i class="fa fa-info-circle"></i> Tidak Ada Data</h4>
                <p>Tidak ada pencatatan dalam pelaporan ini.</p>
            </div>
        <?php endif; ?>
    </div>
    <!-- /.box-body -->

    <!-- Approval Actions -->
    <div class="approval-actions">
        <button type="button" class="btn btn-success btn-approve-lg btn-approve" data-id="<?= $header->id ?>">
            <i class="fa fa-check-circle"></i>&nbsp;Approve
        </button>
        <button type="button" class="btn btn-danger btn-reject-lg btn-reject" data-id="<?= $header->id ?>">
            <i class="fa fa-times-circle"></i>&nbsp;Reject
        </button>
        &nbsp;&nbsp;&nbsp;
        <a href="<?= site_url('expense_petty_cash/approval') ?>" class="btn btn-default" style="padding: 12px 25px; font-size: 14px;">
            <i class="fa fa-arrow-left"></i>&nbsp;Kembali
        </a>
    </div>
</div>

<!-- Reject Form (hidden by default) -->
<div class="box box-danger" id="reject-form-box" style="display: none; margin-top: 15px;">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-times-circle"></i>&nbsp;Form Alasan Reject</h3>
    </div>
    <div class="box-body">
        <div class="form-group">
            <label for="alasan-reject">Alasan Reject <span class="text-red">*</span></label>
            <textarea id="alasan-reject" class="form-control" rows="4" placeholder="Masukkan alasan reject (minimal 10 karakter)" maxlength="500"></textarea>
            <span class="help-block text-red" id="alasan-reject-error" style="display: none;">Alasan reject wajib diisi minimal 10 karakter.</span>
            <span class="help-block text-muted"><span id="char-count">0</span>/500 karakter</span>
        </div>
    </div>
    <div class="box-footer">
        <button type="button" class="btn btn-default" id="btn-cancel-reject">
            <i class="fa fa-ban"></i>&nbsp;Batal
        </button>
        <button type="button" class="btn btn-danger pull-right" id="btn-submit-reject" data-id="<?= $header->id ?>">
            <i class="fa fa-send"></i>&nbsp;Submit Reject
        </button>
    </div>
</div>

<script>
    var BASE_URL = '<?= site_url('expense_petty_cash/') ?>';
</script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= base_url('assets/js/modules/expense_petty_cash/approval.js') ?>"></script>

<script>
    (function() {
        // Expand/Collapse All
        var allExpanded = true;
        $('#btn-expand-all').on('click', function() {
            if (allExpanded) {
                $('.pencatatan-panel .collapse').collapse('hide');
                $(this).html('<i class="fa fa-expand"></i> Expand All');
                allExpanded = false;
            } else {
                $('.pencatatan-panel .collapse').collapse('show');
                $(this).html('<i class="fa fa-compress"></i> Collapse All');
                allExpanded = true;
            }
        });

        // Toggle icon rotation
        $('.pencatatan-panel .panel-header').on('click', function() {
            var $icon = $(this).find('.toggle-icon');
            var $target = $($(this).data('target'));
            $target.on('shown.bs.collapse', function() {
                $icon.css('transform', 'rotate(0deg)');
            }).on('hidden.bs.collapse', function() {
                $icon.css('transform', 'rotate(-90deg)');
            });
        });
    })();
</script>