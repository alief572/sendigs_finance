<?php

/**
 * View Detail Payment Hutang - Petty Cash VUCA & Sustain
 *
 * PHP vars:
 *   $record->header          - object with tr_petty_cash_vuca_sustain fields
 *   $record->pencatatan_list - array of objects, each with:
 *       ->no_pencatatan, ->tanggal, ->request_by, ->keterangan, ->nominal
 *       ->items (array: ->coa_code, ->coa_nama, ->pengeluaran, ->spesifikasi, ->jumlah, ->nominal, ->total)
 */

// Helper: Indonesian month names
function pcvs_bulan_indonesia($month_number)
{
    $bulan = [
        1  => 'Januari',
        2  => 'Februari',
        3  => 'Maret',
        4  => 'April',
        5  => 'Mei',
        6  => 'Juni',
        7  => 'Juli',
        8  => 'Agustus',
        9  => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];
    return isset($bulan[(int)$month_number]) ? $bulan[(int)$month_number] : '';
}

// Helper: format date to "DD Month YYYY" (Indonesian)
function pcvs_format_tanggal_indonesia($date)
{
    if (empty($date)) return '-';
    $timestamp = strtotime($date);
    $day   = date('d', $timestamp);
    $month = pcvs_bulan_indonesia((int)date('m', $timestamp));
    $year  = date('Y', $timestamp);
    return $day . ' ' . $month . ' ' . $year;
}

// Helper: format status badge
function pcvs_status_label($status)
{
    $map = [
        'draft'           => '<span class="label label-warning">Draft</span>',
        'waiting payment' => '<span class="label label-info">Waiting Payment</span>',
        'done payment'    => '<span class="label label-success">Done Payment</span>',
    ];
    return isset($map[$status]) ? $map[$status] : '<span class="label label-default">' . htmlspecialchars($status) . '</span>';
}

// Extract data
$header = $record->header;
$pencatatan_list = isset($record->pencatatan_list) ? $record->pencatatan_list : [];
?>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-file-text-o"></i>&nbsp;Detail Payment Hutang</h3>
    </div>
    <div class="box-body">

        <!-- Header Info: 2-column grid -->
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>No Payment Hutang</label>
                    <p class="form-control-static"><strong><?= htmlspecialchars($header->no_payment_hutang) ?></strong></p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>No Pelaporan</label>
                    <p class="form-control-static"><?= htmlspecialchars($header->no_pelaporan) ?></p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Periode</label>
                    <p class="form-control-static">
                        <?= pcvs_format_tanggal_indonesia($header->periode_start) ?> - <?= pcvs_format_tanggal_indonesia($header->periode_end) ?>
                    </p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Company</label>
                    <p class="form-control-static"><?= htmlspecialchars($header->company) ?></p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Grand Total</label>
                    <p class="form-control-static"><strong>Rp <?= number_format($header->grand_total, 0, ',', '.') ?></strong></p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Status</label>
                    <p class="form-control-static"><?= pcvs_status_label($header->status) ?></p>
                </div>
            </div>
        </div>

        <hr>

        <!-- Tabel Pencatatan -->
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
                        $grand_total = 0;
                        foreach ($pencatatan_list as $pencatatan) : ?>
                            <?php $grand_total += (float)$pencatatan->nominal; ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td><?= htmlspecialchars($pencatatan->no_pencatatan) ?></td>
                                <td class="text-center"><?= date('d/m/Y', strtotime($pencatatan->tanggal)) ?></td>
                                <td><?= htmlspecialchars($pencatatan->request_by) ?></td>
                                <td><?= htmlspecialchars($pencatatan->keterangan) ?></td>
                                <td class="text-right"><?= number_format($pencatatan->nominal, 0, ',', '.') ?></td>
                            </tr>

                            <!-- Sub-tabel detail item per pencatatan -->
                            <?php if (isset($pencatatan->items) && !empty($pencatatan->items)) : ?>
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
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $item_no = 1;
                                                foreach ($pencatatan->items as $item) : ?>
                                                    <tr>
                                                        <td class="text-center"><?= $item_no++ ?></td>
                                                        <td><?= htmlspecialchars($item->coa_code) ?><?= !empty($item->coa_nama) ? ' - ' . htmlspecialchars($item->coa_nama) : '' ?></td>
                                                        <td><?= htmlspecialchars($item->pengeluaran) ?></td>
                                                        <td><?= htmlspecialchars($item->spesifikasi) ?></td>
                                                        <td class="text-center"><?= $item->jumlah ?></td>
                                                        <td class="text-right"><?= number_format($item->nominal, 0, ',', '.') ?></td>
                                                        <td class="text-right"><?= number_format($item->total, 0, ',', '.') ?></td>
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
                            <th colspan="5" class="text-right">Total</th>
                            <th class="text-right"><?= number_format($grand_total, 0, ',', '.') ?></th>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
    <!-- /.box-body -->

    <div class="box-footer">
        <a href="<?= site_url('petty_cash_vuca_sustain') ?>" class="btn btn-default">
            <i class="fa fa-arrow-left"></i>&nbsp;Back
        </a>
        <a href="<?= site_url('petty_cash_vuca_sustain/print_pdf/' . $header->id) ?>" target="_blank" class="btn btn-info">
            <i class="fa fa-print"></i>&nbsp;Print
        </a>
    </div>
</div>