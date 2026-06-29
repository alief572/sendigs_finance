<?php

/**
 * View Detail Pelaporan Petty Cash
 *
 * PHP vars:
 *   $pelaporan       - object with header + pencatatan_list
 *   $budget_info     - object (budget, budget_terpakai, sisa_budget)
 *   $has_add         - bool permission Expense_Petty_Cash.Add
 *   $has_manage      - bool permission Expense_Petty_Cash.Manage
 */

// Helper: format status label
function pelaporan_status_label($status)
{
    $map = [
        'draft'   => '<span class="label label-default">Draft</span>',
        'waiting' => '<span class="label label-warning">Waiting Approval</span>',
        'approved' => '<span class="label label-success">Approved</span>',
        'reject'  => '<span class="label label-danger">Reject</span>',
    ];
    return isset($map[$status]) ? $map[$status] : '<span class="label label-default">' . htmlspecialchars($status) . '</span>';
}

function pencatatan_status_label($status)
{
    $map = [
        'draft'            => '<span class="label label-default">Draft</span>',
        'waiting approval' => '<span class="label label-warning">Waiting Approval</span>',
        'approved'         => '<span class="label label-success">Approved</span>',
        'reject'           => '<span class="label label-danger">Reject</span>',
    ];
    return isset($map[$status]) ? $map[$status] : '<span class="label label-default">' . htmlspecialchars($status) . '</span>';
}

// Extract header for convenience (controller passes $pelaporan with ->header and ->pencatatan_list)
$header = isset($pelaporan->header) ? $pelaporan->header : $pelaporan;
$pencatatan_list = isset($pelaporan->pencatatan_list) ? $pelaporan->pencatatan_list : [];
?>

<!-- Budget Info Row -->
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

<!-- Detail Pelaporan Header -->
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-file-text-o"></i>&nbsp;Detail Pelaporan Petty Cash</h3>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>No Pelaporan</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($header->no_pelaporan) ?>" readonly>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Periode</label>
                    <input type="text" class="form-control" value="<?= date('d/m/Y', strtotime($header->periode_start)) ?> - <?= date('d/m/Y', strtotime($header->periode_end)) ?>" readonly>
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
                    <label>Status</label>
                    <p class="form-control-static"><?= pelaporan_status_label($header->status) ?></p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Grand Total</label>
                    <input type="text" class="form-control text-right" value="<?= number_format($header->grand_total, 0, ',', '.') ?>" readonly>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Approver</label>
                    <input type="text" class="form-control" value="<?= isset($header->approver_name) ? htmlspecialchars($header->approver_name) : '-' ?>" readonly>
                </div>
            </div>
        </div>

        <?php if (!empty($header->alasan_reject)) : ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Alasan Reject</label>
                        <textarea class="form-control" rows="2" readonly><?= htmlspecialchars($header->alasan_reject) ?></textarea>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <hr>

        <!-- Daftar Pencatatan -->
        <h4><i class="fa fa-list"></i>&nbsp;Daftar Pencatatan</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr class="bg-primary">
                        <th width="40" class="text-center">No</th>
                        <th>No Pencatatan</th>
                        <th class="text-center">Tanggal</th>
                        <th>Request By</th>
                        <th>Keterangan</th>
                        <th class="text-right">Nominal</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($pencatatan_list)) : ?>
                        <?php $no = 1;
                        foreach ($pencatatan_list as $pencatatan) : ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td><?= htmlspecialchars($pencatatan->no_pencatatan) ?></td>
                                <td class="text-center"><?= date('d/m/Y', strtotime($pencatatan->tanggal)) ?></td>
                                <td><?= htmlspecialchars($pencatatan->request_by) ?></td>
                                <td><?= htmlspecialchars($pencatatan->keterangan) ?></td>
                                <td class="text-right"><?= number_format($pencatatan->grand_total, 0, ',', '.') ?></td>
                                <td class="text-center"><?= pencatatan_status_label($pencatatan->status) ?></td>
                            </tr>

                            <!-- Detail Items for this pencatatan -->
                            <?php if (isset($pencatatan->details) && !empty($pencatatan->details)) : ?>
                                <tr>
                                    <td colspan="7" style="padding: 5px 20px;">
                                        <table class="table table-bordered table-condensed" style="margin-bottom: 0; background: #f9f9f9;">
                                            <thead>
                                                <tr>
                                                    <th width="30" class="text-center">#</th>
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
                                                <?php $detail_no = 1;
                                                foreach ($pencatatan->details as $detail) : ?>
                                                    <tr>
                                                        <td class="text-center"><?= $detail_no++ ?></td>
                                                        <td><?= htmlspecialchars($detail->coa_code) ?></td>
                                                        <td><?= htmlspecialchars($detail->pengeluaran) ?></td>
                                                        <td><?= htmlspecialchars($detail->spesifikasi) ?></td>
                                                        <td class="text-center"><?= $detail->jumlah ?></td>
                                                        <td class="text-right"><?= number_format($detail->nominal, 0, ',', '.') ?></td>
                                                        <td class="text-right"><?= number_format($detail->total, 0, ',', '.') ?></td>
                                                        <td>
                                                            <?php if (isset($detail->evidences) && !empty($detail->evidences)) : ?>
                                                                <?php foreach ($detail->evidences as $evidence) : ?>
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
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">Tidak ada data pencatatan</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($pencatatan_list)) : ?>
                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-right">Grand Total</th>
                            <th class="text-right"><?= number_format($header->grand_total, 0, ',', '.') ?></th>
                            <th></th>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
    <!-- /.box-body -->

    <div class="box-footer">
        <a href="<?= site_url('expense_petty_cash/pelaporan') ?>" class="btn btn-warning">
            <i class="fa fa-reply"></i>&nbsp;Kembali
        </a>
    </div>
</div>