<?php

/**
 * View Detail Pencatatan Petty Cash (Read-Only)
 *
 * PHP vars:
 *   $pencatatan   - object with: header (object), details (array), evidences (array keyed by detail_id)
 *   $budget_info  - object (budget, budget_terpakai, sisa_budget)
 *   $coa_list     - array of COA objects
 *
 * Requirements: 1.3, 1.4, 7.1
 */

// Helper: format status label with color
function pencatatan_view_status_label($status)
{
    $map = [
        'draft'            => '<span class="label label-default">Draft</span>',
        'waiting approval' => '<span class="label label-warning">Waiting Approval</span>',
        'approved'         => '<span class="label label-success">Approved</span>',
        'reject'           => '<span class="label label-danger">Reject</span>',
    ];
    return isset($map[$status]) ? $map[$status] : '<span class="label label-default">' . htmlspecialchars($status) . '</span>';
}

// Helper: format journal status label
function journal_status_label($status)
{
    $map = [
        'pending' => '<span class="label label-info">Pending</span>',
        'success' => '<span class="label label-success">Success</span>',
        'failed'  => '<span class="label label-danger">Failed</span>',
    ];
    return isset($map[$status]) ? $map[$status] : '<span class="label label-default">' . htmlspecialchars($status) . '</span>';
}

// Extract header for convenience
$header = $pencatatan->header;
$details = isset($pencatatan->details) ? $pencatatan->details : [];
$evidences = isset($pencatatan->evidences) ? $pencatatan->evidences : [];

// Permission check
$has_manage = has_permission('Expense_Petty_Cash.Manage');

// Build COA lookup map for display
$coa_map = [];
if (!empty($coa_list)) {
    foreach ($coa_list as $coa) {
        $coa_map[$coa->coa_code] = $coa->coa_code . ' - ' . (isset($coa->coa_nama) ? $coa->coa_nama : '');
    }
}
?>

<!-- Budget Info Row - 3 small-box AdminLTE -->
<div class="row">
    <div class="col-lg-4 col-xs-6">
        <div class="small-box bg-aqua">
            <div class="inner">
                <h3><?= isset($budget_info->budget) ? number_format($budget_info->budget, 0, ',', '.') : '0' ?></h3>
                <p>Budget</p>
            </div>
            <div class="icon">
                <i class="fa fa-money"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-xs-6">
        <div class="small-box bg-yellow">
            <div class="inner">
                <h3><?= isset($budget_info->budget_terpakai) ? number_format($budget_info->budget_terpakai, 0, ',', '.') : '0' ?></h3>
                <p>Budget Terpakai</p>
            </div>
            <div class="icon">
                <i class="fa fa-credit-card"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-xs-6">
        <div class="small-box bg-green">
            <div class="inner">
                <h3><?= isset($budget_info->sisa_budget) ? number_format($budget_info->sisa_budget, 0, ',', '.') : '0' ?></h3>
                <p>Sisa Budget</p>
            </div>
            <div class="icon">
                <i class="fa fa-balance-scale"></i>
            </div>
        </div>
    </div>
</div>

<!-- Detail Pencatatan Header -->
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-file-text-o"></i>&nbsp;Detail Pencatatan Petty Cash</h3>
    </div>
    <div class="box-body">
        <!-- Header Info -->
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>No Pencatatan</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($header->no_pencatatan) ?>" readonly>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Tanggal</label>
                    <input type="text" class="form-control" value="<?= date('d/m/Y', strtotime($header->tanggal)) ?>" readonly>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Company</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($header->company) ?>" readonly>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Request By</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($header->request_by) ?>" readonly>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label>Keterangan</label>
                    <textarea class="form-control" rows="2" readonly><?= htmlspecialchars($header->keterangan) ?></textarea>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Status</label>
                    <p class="form-control-static"><?= pencatatan_view_status_label($header->status) ?></p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Journal Status</label>
                    <p class="form-control-static"><?= journal_status_label($header->journal_status) ?></p>
                </div>
            </div>
        </div>

        <hr>

        <!-- Detail Items Table -->
        <h4><i class="fa fa-list"></i>&nbsp;Detail Item Pengeluaran</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr class="bg-primary">
                        <th width="40" class="text-center">No</th>
                        <th>COA</th>
                        <th>Pengeluaran</th>
                        <th>Spesifikasi</th>
                        <th class="text-center">Jumlah</th>
                        <th class="text-right">Nominal</th>
                        <th class="text-right">Total</th>
                        <th>Evidence</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($details)) : ?>
                        <?php $no = 1;
                        foreach ($details as $detail) : ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td><?= isset($coa_map[$detail->coa_code]) ? htmlspecialchars($coa_map[$detail->coa_code]) : htmlspecialchars($detail->coa_code) ?></td>
                                <td><?= htmlspecialchars($detail->pengeluaran) ?></td>
                                <td><?= htmlspecialchars($detail->spesifikasi) ?></td>
                                <td class="text-center"><?= $detail->jumlah ?></td>
                                <td class="text-right"><?= number_format($detail->nominal, 0, ',', '.') ?></td>
                                <td class="text-right"><?= number_format($detail->total, 0, ',', '.') ?></td>
                                <td>
                                    <?php
                                    $detail_evidences = isset($evidences[$detail->id]) ? $evidences[$detail->id] : [];
                                    if (!empty($detail_evidences)) : ?>
                                        <?php foreach ($detail_evidences as $evidence) : ?>
                                            <a href="<?= base_url('assets/expense_petty_cash/' . $evidence->encrypted_name) ?>" target="_blank" title="<?= htmlspecialchars($evidence->original_name) ?>">
                                                <i class="fa fa-file"></i> <?= htmlspecialchars(strlen($evidence->original_name) > 20 ? substr($evidence->original_name, 0, 20) . '...' : $evidence->original_name) ?>
                                            </a><br>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">Tidak ada detail item</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($details)) : ?>
                    <tfoot>
                        <tr>
                            <th colspan="6" class="text-right">Grand Total</th>
                            <th class="text-right"><?= number_format($header->grand_total, 0, ',', '.') ?></th>
                            <th></th>
                        </tr>
                        <tr style="background-color: #fff3cd;">
                            <th colspan="6" class="text-right">Budget</th>
                            <th class="text-right"><?= isset($budget_info->budget) ? number_format($budget_info->budget, 0, ',', '.') : '0' ?></th>
                            <th></th>
                        </tr>
                        <tr style="background-color: #d4edda;">
                            <th colspan="6" class="text-right">Sisa Budget</th>
                            <th class="text-right"><?= isset($budget_info->sisa_budget) ? number_format($budget_info->sisa_budget, 0, ',', '.') : '0' ?></th>
                            <th></th>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>

        <hr>

        <!-- Simulasi Jurnal (Preview) -->
        <?php
        // Build jurnal preview from pencatatan data (same logic as form JS)
        $jurnal_company = $header->company;
        $jurnal_tanggal = date('d/m/Y', strtotime($header->tanggal));
        $jurnal_grand_total = (float) $header->grand_total;
        ?>
        <h4><i class="fa fa-book"></i>&nbsp;Preview Jurnal</h4>

        <?php if ($jurnal_company === 'STM') : ?>
            <!-- Jurnal STM -->
            <label class="label label-primary" style="font-size: 12px; margin-bottom: 8px; display: inline-block;">
                <i class="fa fa-building"></i> Jurnal Pencatatan STM
            </label>
            <div class="table-responsive">
                <table class="table table-bordered table-condensed" style="font-size: 12px;">
                    <thead style="background: #f5f5f5;">
                        <tr>
                            <th width="100">Tanggal</th>
                            <th width="100">COA</th>
                            <th>Nama Account</th>
                            <th width="80">Company</th>
                            <th width="120" class="text-right">Debit</th>
                            <th width="120" class="text-right">Kredit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($details)) : ?>
                            <?php foreach ($details as $d) :
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
                            <tr style="background: #fff3cd;">
                                <td><?= $jurnal_tanggal ?></td>
                                <td>1101-01-02</td>
                                <td>Kas Kecil</td>
                                <td>STM</td>
                                <td class="text-right">-</td>
                                <td class="text-right"><?= number_format($jurnal_grand_total, 0, ',', '.') ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot style="background: #f9f9f9; font-weight: bold;">
                        <tr>
                            <td colspan="4" class="text-right">Balancing</td>
                            <td class="text-right">Rp <?= number_format($jurnal_grand_total, 0, ',', '.') ?></td>
                            <td class="text-right">Rp <?= number_format($jurnal_grand_total, 0, ',', '.') ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

        <?php elseif ($jurnal_company === 'VUCA' || $jurnal_company === 'SUSTAIN') : ?>
            <?php
            $coa_hutang = ($jurnal_company === 'VUCA') ? '2103-01-01' : '2103-01-02';
            $coa_piutang = ($jurnal_company === 'VUCA') ? '1103-01-01' : '1103-01-02';
            ?>

            <!-- Sisi Company (VUCA/SUSTAIN) -->
            <label class="label label-warning" style="font-size: 12px; margin-bottom: 8px; display: inline-block;">
                <i class="fa fa-building"></i> Sisi <?= $jurnal_company ?>
            </label>
            <div class="table-responsive">
                <table class="table table-bordered table-condensed" style="font-size: 12px;">
                    <thead style="background: #f5f5f5;">
                        <tr>
                            <th width="100">Tanggal</th>
                            <th width="100">COA</th>
                            <th>Nama Account</th>
                            <th width="80">Company</th>
                            <th width="120" class="text-right">Debit</th>
                            <th width="120" class="text-right">Kredit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($details)) : ?>
                            <?php foreach ($details as $d) :
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
                            <tr style="background: #fff3cd;">
                                <td><?= $jurnal_tanggal ?></td>
                                <td><?= $coa_hutang ?></td>
                                <td>Hutang ke STM</td>
                                <td><?= $jurnal_company ?></td>
                                <td class="text-right">-</td>
                                <td class="text-right"><?= number_format($jurnal_grand_total, 0, ',', '.') ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot style="background: #f9f9f9; font-weight: bold;">
                        <tr>
                            <td colspan="4" class="text-right">Balancing</td>
                            <td class="text-right">Rp <?= number_format($jurnal_grand_total, 0, ',', '.') ?></td>
                            <td class="text-right">Rp <?= number_format($jurnal_grand_total, 0, ',', '.') ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Sisi STM (Inter-Company) -->
            <label class="label label-info" style="font-size: 12px; margin-bottom: 8px; display: inline-block; margin-top: 10px;">
                <i class="fa fa-building"></i> Sisi STM (Inter-Company)
            </label>
            <div class="table-responsive">
                <table class="table table-bordered table-condensed" style="font-size: 12px;">
                    <thead style="background: #f5f5f5;">
                        <tr>
                            <th width="100">Tanggal</th>
                            <th width="100">COA</th>
                            <th>Nama Account</th>
                            <th width="80">Company</th>
                            <th width="120" class="text-right">Debit</th>
                            <th width="120" class="text-right">Kredit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= $jurnal_tanggal ?></td>
                            <td><?= $coa_piutang ?></td>
                            <td>Piutang <?= $jurnal_company ?></td>
                            <td>STM</td>
                            <td class="text-right"><?= number_format($jurnal_grand_total, 0, ',', '.') ?></td>
                            <td class="text-right">-</td>
                        </tr>
                        <tr style="background: #fff3cd;">
                            <td><?= $jurnal_tanggal ?></td>
                            <td>1101-01-02</td>
                            <td>Kas Kecil</td>
                            <td>STM</td>
                            <td class="text-right">-</td>
                            <td class="text-right"><?= number_format($jurnal_grand_total, 0, ',', '.') ?></td>
                        </tr>
                    </tbody>
                    <tfoot style="background: #f9f9f9; font-weight: bold;">
                        <tr>
                            <td colspan="4" class="text-right">Balancing</td>
                            <td class="text-right">Rp <?= number_format($jurnal_grand_total, 0, ',', '.') ?></td>
                            <td class="text-right">Rp <?= number_format($jurnal_grand_total, 0, ',', '.') ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endif; ?>

    </div>
    <!-- /.box-body -->

    <div class="box-footer">
        <a href="<?= site_url('expense_petty_cash') ?>" class="btn btn-warning">
            <i class="fa fa-reply"></i>&nbsp;Kembali
        </a>
    </div>
</div>