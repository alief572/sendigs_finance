<?php

/**
 * View Detail Payment Hutang - Petty Cash VUCA & Sustain (Enhanced UI)
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
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember'
    ];
    return isset($bulan[(int)$month_number]) ? $bulan[(int)$month_number] : '';
}

function pcvs_format_tanggal_indonesia($date)
{
    if (empty($date)) return '-';
    $day = date('d', strtotime($date));
    $month = pcvs_bulan_indonesia((int)date('m', strtotime($date)));
    $year = date('Y', strtotime($date));
    return $day . ' ' . $month . ' ' . $year;
}

function pcvs_status_label($status)
{
    $map = [
        'draft'           => '<span class="label label-warning">Draft</span>',
        'waiting payment' => '<span class="label label-info">Waiting Payment</span>',
        'done payment'    => '<span class="label label-success">Done Payment</span>',
    ];
    return isset($map[$status]) ? $map[$status] : '<span class="label label-default">' . htmlspecialchars($status) . '</span>';
}

$header = $record->header;
$pencatatan_list = isset($record->pencatatan_list) ? $record->pencatatan_list : [];
$jumlah_item = count($pencatatan_list);
?>

<style>
    .confirm-summary-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 8px;
        padding: 20px 25px;
        color: #fff;
        margin-bottom: 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .confirm-summary-card .card-row {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 15px;
    }

    .confirm-summary-card .card-item {
        flex: 1;
        min-width: 130px;
    }

    .confirm-summary-card .card-item .label-text {
        font-size: 11px;
        text-transform: uppercase;
        opacity: 0.8;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .confirm-summary-card .card-item .value-text {
        font-size: 18px;
        font-weight: 700;
    }

    .confirm-summary-card .card-item .value-text.text-sm {
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
</style>

<!-- Summary Card -->
<div class="confirm-summary-card">
    <div class="card-row">
        <div class="card-item">
            <div class="label-text">No Payment Hutang</div>
            <div class="value-text text-sm"><?= htmlspecialchars($header->no_payment_hutang) ?></div>
        </div>
        <div class="card-item">
            <div class="label-text">No Pelaporan</div>
            <div class="value-text text-sm"><?= htmlspecialchars($header->no_pelaporan) ?></div>
        </div>
        <div class="card-item">
            <div class="label-text">Periode</div>
            <div class="value-text text-sm"><?= pcvs_format_tanggal_indonesia($header->periode_start) ?> — <?= pcvs_format_tanggal_indonesia($header->periode_end) ?></div>
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
            <div class="label-text">Status</div>
            <div class="value-text text-sm"><?= pcvs_status_label($header->status) ?></div>
        </div>
    </div>
</div>

<!-- Detail Pencatatan -->
<div class="box box-solid">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-list-alt"></i>&nbsp;Daftar Pencatatan</h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" id="btn-expand-all" title="Expand All">
                <i class="fa fa-expand"></i> Expand All
            </button>
        </div>
    </div>
    <div class="box-body" style="padding: 15px;">
        <?php if (!empty($pencatatan_list)) : ?>
            <?php $no = 1;
            foreach ($pencatatan_list as $pencatatan) : ?>
                <?php $panel_id = 'panel-' . $no; ?>
                <div class="pencatatan-panel">
                    <div class="panel-header" data-toggle="collapse" data-target="#<?= $panel_id ?>">
                        <div>
                            <div class="panel-title-text">
                                <i class="fa fa-file-text-o"></i>&nbsp;
                                <?= $no ?>. <?= htmlspecialchars($pencatatan->no_pencatatan) ?>
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
                            <span class="panel-amount">Rp <?= number_format($pencatatan->nominal, 0, ',', '.') ?></span>
                            <i class="fa fa-chevron-down toggle-icon"></i>
                        </div>
                    </div>
                    <div class="collapse in" id="<?= $panel_id ?>">
                        <div class="panel-body-content">
                            <?php if (isset($pencatatan->items) && !empty($pencatatan->items)) : ?>
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
                                    <tfoot>
                                        <tr>
                                            <th colspan="6" class="text-right">Total</th>
                                            <th class="text-right"><?= number_format($pencatatan->nominal, 0, ',', '.') ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            <?php else : ?>
                                <p class="text-muted"><em>Detail item tidak tersedia</em></p>
                            <?php endif; ?>

                            <!-- Preview Jurnal -->
                            <?php
                            $jurnal_company = $header->company; // Always VUCA or SUSTAIN
                            $jurnal_tanggal = date('d/m/Y', strtotime($pencatatan->tanggal));
                            $jurnal_total = (float) $pencatatan->nominal;
                            $coa_hutang = ($jurnal_company === 'VUCA') ? '2103-01-01' : '2103-01-02';
                            $coa_piutang = ($jurnal_company === 'VUCA') ? '1103-01-01' : '1103-01-02';
                            ?>
                            <div style="margin-top: 12px; padding-top: 12px; border-top: 1px dashed #dee2e6;">
                                <h5 style="font-size: 12px; font-weight: 600; color: #555; margin-bottom: 10px;">
                                    <i class="fa fa-book"></i>&nbsp;Preview Jurnal
                                </h5>

                                <span style="display:inline-block;padding:3px 10px;border-radius:4px;font-size:11px;font-weight:600;margin-bottom:8px;background:#fff3cd;color:#856404;">
                                    <i class="fa fa-building"></i> Sisi <?= $jurnal_company ?>
                                </span>
                                <table class="table table-bordered" style="font-size:11px;margin-bottom:10px;">
                                    <thead>
                                        <tr style="background:#f8f9fa;">
                                            <th style="font-size:10px;">Tanggal</th>
                                            <th style="font-size:10px;">COA</th>
                                            <th style="font-size:10px;">Nama Account</th>
                                            <th style="font-size:10px;">Company</th>
                                            <th style="font-size:10px;" class="text-right">Debit</th>
                                            <th style="font-size:10px;" class="text-right">Kredit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (isset($pencatatan->items) && !empty($pencatatan->items)) : foreach ($pencatatan->items as $item) : ?>
                                                <tr>
                                                    <td><?= $jurnal_tanggal ?></td>
                                                    <td><?= htmlspecialchars($item->coa_code) ?></td>
                                                    <td><?= htmlspecialchars($item->coa_code) ?><?= !empty($item->coa_nama) ? ' - ' . htmlspecialchars($item->coa_nama) : '' ?></td>
                                                    <td><?= $jurnal_company ?></td>
                                                    <td class="text-right"><?= number_format($item->total, 0, ',', '.') ?></td>
                                                    <td class="text-right">-</td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <tr style="background:#fffbeb;">
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
                                        <tr style="background:#f1f3f5;font-weight:700;">
                                            <td colspan="4" class="text-right">Balancing</td>
                                            <td class="text-right"><?= number_format($jurnal_total, 0, ',', '.') ?></td>
                                            <td class="text-right"><?= number_format($jurnal_total, 0, ',', '.') ?></td>
                                        </tr>
                                    </tfoot>
                                </table>

                                <span style="display:inline-block;padding:3px 10px;border-radius:4px;font-size:11px;font-weight:600;margin-bottom:8px;margin-top:5px;background:#cce5ff;color:#004085;">
                                    <i class="fa fa-building"></i> Sisi STM (Inter-Company)
                                </span>
                                <table class="table table-bordered" style="font-size:11px;margin-bottom:0;">
                                    <thead>
                                        <tr style="background:#f8f9fa;">
                                            <th style="font-size:10px;">Tanggal</th>
                                            <th style="font-size:10px;">COA</th>
                                            <th style="font-size:10px;">Nama Account</th>
                                            <th style="font-size:10px;">Company</th>
                                            <th style="font-size:10px;" class="text-right">Debit</th>
                                            <th style="font-size:10px;" class="text-right">Kredit</th>
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
                                        <tr style="background:#fffbeb;">
                                            <td><?= $jurnal_tanggal ?></td>
                                            <td>1101-01-02</td>
                                            <td>Kas Kecil</td>
                                            <td>STM</td>
                                            <td class="text-right">-</td>
                                            <td class="text-right"><?= number_format($jurnal_total, 0, ',', '.') ?></td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr style="background:#f1f3f5;font-weight:700;">
                                            <td colspan="4" class="text-right">Balancing</td>
                                            <td class="text-right"><?= number_format($jurnal_total, 0, ',', '.') ?></td>
                                            <td class="text-right"><?= number_format($jurnal_total, 0, ',', '.') ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php $no++;
            endforeach; ?>
        <?php else : ?>
            <div class="callout callout-warning">
                <h4><i class="fa fa-info-circle"></i> Tidak Ada Data</h4>
                <p>Tidak ada pencatatan dalam payment hutang ini.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="box-footer" style="text-align: center; padding: 20px;">
        <a href="<?= site_url('petty_cash_vuca_sustain') ?>" class="btn btn-default" style="padding: 10px 25px; font-size: 14px;">
            <i class="fa fa-arrow-left"></i>&nbsp;Back
        </a>
        &nbsp;&nbsp;
        <a href="<?= site_url('petty_cash_vuca_sustain/print_pdf/' . $header->id) ?>" target="_blank" class="btn btn-info" style="padding: 10px 25px; font-size: 14px;">
            <i class="fa fa-print"></i>&nbsp;Print
        </a>
    </div>
</div>

<script>
    (function() {
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
        $('.pencatatan-panel .panel-header').on('click', function() {
            var $icon = $(this).find('.toggle-icon');
            var $target = $($(this).data('target'));
            $target.on('shown.bs.collapse', function() {
                    $icon.css('transform', 'rotate(0deg)');
                })
                .on('hidden.bs.collapse', function() {
                    $icon.css('transform', 'rotate(-90deg)');
                });
        });
    })();
</script>