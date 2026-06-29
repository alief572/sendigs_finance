<?php

/**
 * View Detail Pelaporan untuk Approval
 *
 * PHP vars:
 *   $pelaporan           - object with header + pencatatan_list
 *   $pencatatan_details  - array keyed by pencatatan_id with detail items + evidences
 */

// Extract header for convenience
$header = isset($pelaporan->header) ? $pelaporan->header : $pelaporan;
$pencatatan_list = isset($pelaporan->pencatatan_list) ? $pelaporan->pencatatan_list : [];
?>

<!-- Detail Pelaporan Header -->
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-file-text-o"></i>&nbsp;Detail Pelaporan - Approval</h3>
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
                    <label>Grand Total</label>
                    <input type="text" class="form-control text-right" value="<?= number_format($header->grand_total, 0, ',', '.') ?>" readonly>
                </div>
            </div>
        </div>

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
                            </tr>

                            <!-- Detail Items for this pencatatan -->
                            <?php
                            $pencatatan_id = $pencatatan->id;
                            $details = isset($pencatatan_details[$pencatatan_id]) ? $pencatatan_details[$pencatatan_id] : [];
                            if (!empty($details)) : ?>
                                <tr>
                                    <td colspan="6" style="padding: 5px 20px;">
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
                                                foreach ($details as $detail) : ?>
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
                            <td colspan="6" class="text-center text-muted">Tidak ada data pencatatan</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($pencatatan_list)) : ?>
                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-right">Grand Total</th>
                            <th class="text-right"><?= number_format($header->grand_total, 0, ',', '.') ?></th>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
    <!-- /.box-body -->

    <div class="box-footer">
        <a href="<?= site_url('expense_petty_cash/approval') ?>" class="btn btn-warning">
            <i class="fa fa-reply"></i>&nbsp;Kembali
        </a>
        <div class="pull-right">
            <button type="button" class="btn btn-success btn-approve" data-id="<?= $header->id ?>">
                <i class="fa fa-check"></i>&nbsp;Approve
            </button>
            <button type="button" class="btn btn-danger btn-reject" data-id="<?= $header->id ?>">
                <i class="fa fa-times"></i>&nbsp;Reject
            </button>
        </div>
    </div>
</div>

<!-- Reject Form (hidden by default) -->
<div class="box box-danger" id="reject-form-box" style="display: none;">
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