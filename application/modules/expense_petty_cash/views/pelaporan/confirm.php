<?php

/**
 * Pelaporan Petty Cash - Halaman Konfirmasi (Review Detail + Jurnal)
 *
 * UI yang lebih polished dengan AdminLTE components:
 * - Summary cards di atas
 * - Collapsible panels per pencatatan
 * - Styled jurnal tables
 *
 * PHP vars:
 *   $pelaporan, $budget_info, $pencatatan_details, $coa_list, $has_add, $has_manage
 */

$header         = $pelaporan->header;
$pencatatan_list = $pelaporan->pencatatan_list;
$jumlah_item    = count($pencatatan_list);

$periode_start_fmt = date('d/m/Y', strtotime($header->periode_start));
$periode_end_fmt   = date('d/m/Y', strtotime($header->periode_end));

$sisa_budget = isset($budget_info->sisa_budget) ? $budget_info->sisa_budget : 0;
$budget_total = isset($budget_info->budget) ? $budget_info->budget : 0;

$coa_map = [];
if (!empty($coa_list)) {
    foreach ($coa_list as $coa) {
        $coa_map[$coa->coa_code] = $coa->coa_code . ' - ' . (isset($coa->coa_nama) ? $coa->coa_nama : '');
    }
}
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
        min-width: 140px;
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

    .jurnal-section {
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px dashed #dee2e6;
    }

    .jurnal-section .jurnal-label {
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

    .btn-ajukan-lg {
        padding: 10px 30px;
        font-size: 15px;
        font-weight: 600;
        border-radius: 6px;
        box-shadow: 0 3px 10px rgba(0, 166, 101, 0.3);
    }

    .toggle-icon {
        transition: transform 0.2s;
        color: #999;
    }

    .collapsed .toggle-icon {
        transform: rotate(-90deg);
    }
</style>

<!-- Summary Card -->
<div class="confirm-summary-card">
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
            <div class="value-text text-sm <?= ($sisa_budget < 0) ? 'text-danger' : '' ?>" style="<?= ($sisa_budget < 0) ? 'color:#ff6b6b;' : '' ?>">Rp <?= number_format($sisa_budget, 0, ',', '.') ?></div>
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
            <?php foreach ($pencatatan_list as $item) : ?>
                <?php
                $detail_data = isset($pencatatan_details[$item->id]) ? $pencatatan_details[$item->id] : null;
                $item_details = ($detail_data && isset($detail_data->details)) ? $detail_data->details : [];
                $item_header = ($detail_data && isset($detail_data->header)) ? $detail_data->header : $item;
                $panel_id = 'panel-' . $item->id;
                ?>
                <div class="pencatatan-panel">
                    <div class="panel-header" data-toggle="collapse" data-target="#<?= $panel_id ?>">
                        <div>
                            <div class="panel-title-text">
                                <i class="fa fa-file-text-o"></i>&nbsp;
                                <?= $no++ ?>. <?= htmlspecialchars($item->no_pencatatan) ?>
                            </div>
                            <div class="panel-meta">
                                <i class="fa fa-calendar-o"></i> <?= date('d/m/Y', strtotime($item->tanggal)) ?>
                                &nbsp;&bull;&nbsp;
                                <i class="fa fa-user"></i> <?= htmlspecialchars($item->request_by) ?>
                                <?php if (!empty($item_header->keterangan)) : ?>
                                    &nbsp;&bull;&nbsp;
                                    <i class="fa fa-comment-o"></i> <?= htmlspecialchars(mb_substr($item_header->keterangan, 0, 50)) ?><?= mb_strlen($item_header->keterangan) > 50 ? '...' : '' ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span class="panel-amount">Rp <?= number_format($item->grand_total, 0, ',', '.') ?></span>
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
                                        <th class="text-right" width="100">Nominal</th>
                                        <th class="text-right" width="100">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($item_details)) : ?>
                                        <?php $d_no = 1;
                                        foreach ($item_details as $d) : ?>
                                            <tr>
                                                <td class="text-center"><?= $d_no++ ?></td>
                                                <td><?= isset($coa_map[$d->coa_code]) ? htmlspecialchars($coa_map[$d->coa_code]) : htmlspecialchars($d->coa_code) ?></td>
                                                <td><?= htmlspecialchars($d->pengeluaran) ?></td>
                                                <td><?= htmlspecialchars($d->spesifikasi) ?></td>
                                                <td class="text-center"><?= $d->jumlah ?></td>
                                                <td class="text-right"><?= number_format($d->nominal, 0, ',', '.') ?></td>
                                                <td class="text-right"><?= number_format($d->total, 0, ',', '.') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted"><em>Detail item tidak tersedia</em></td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="6" class="text-right">Total</th>
                                        <th class="text-right"><?= number_format($item->grand_total, 0, ',', '.') ?></th>
                                    </tr>
                                </tfoot>
                            </table>

                            <!-- Preview Jurnal -->
                            <?php
                            $jurnal_company = isset($item_header->company) ? $item_header->company : $header->company;
                            $jurnal_tanggal = date('d/m/Y', strtotime($item->tanggal));
                            $jurnal_total = (float) $item->grand_total;
                            ?>
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
                                            <?php if (!empty($item_details)) : foreach ($item_details as $d) :
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
                                            <?php if (!empty($item_details)) : foreach ($item_details as $d) :
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

    <div class="box-footer" style="text-align: center; padding: 20px;">
        <?php if ($header->status === 'draft' && ($has_add || $has_manage)) : ?>
            <button type="button" class="btn btn-success btn-ajukan-lg" id="btn-ajukan">
                <i class="fa fa-paper-plane"></i>&nbsp;Ajukan Pelaporan
            </button>
            &nbsp;&nbsp;
        <?php endif; ?>
        <a href="<?= site_url('expense_petty_cash/pelaporan') ?>" class="btn btn-default" style="padding: 10px 25px; font-size: 14px;">
            <i class="fa fa-arrow-left"></i>&nbsp;Kembali
        </a>
    </div>
</div>

<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    (function() {
        var BASE_URL = '<?= site_url('expense_petty_cash/') ?>';
        var PELAPORAN_ID = <?= json_encode($header->id) ?>;

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

        // Ajukan button
        var btnAjukan = document.getElementById('btn-ajukan');
        if (btnAjukan) {
            btnAjukan.addEventListener('click', function() {
                Swal.fire({
                    icon: 'question',
                    title: 'Ajukan Pelaporan?',
                    html: '<p>Status akan berubah menjadi <strong>"Waiting Approval"</strong> dan pencatatan terkait tidak dapat diedit.</p>',
                    showCancelButton: true,
                    confirmButtonColor: '#00a65a',
                    cancelButtonColor: '#aaa',
                    confirmButtonText: '<i class="fa fa-paper-plane"></i> Ya, Ajukan!',
                    cancelButtonText: 'Batal',
                }).then(function(result) {
                    if (result.isConfirmed) {
                        // Show loading
                        Swal.fire({
                            title: 'Memproses...',
                            allowOutsideClick: false,
                            didOpen: function() {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: BASE_URL + 'submit_pelaporan/' + PELAPORAN_ID,
                            type: 'POST',
                            dataType: 'json',
                            success: function(response) {
                                if (response.status == true || response.status == '1') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: response.message || 'Pelaporan berhasil diajukan.',
                                        timer: 2500,
                                        showConfirmButton: false,
                                    }).then(function() {
                                        window.location.href = BASE_URL + 'pelaporan';
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal',
                                        text: response.message || 'Gagal mengajukan pelaporan.',
                                    });
                                }
                            },
                            error: function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Terjadi kesalahan. Silakan coba lagi.',
                                });
                            }
                        });
                    }
                });
            });
        }
    })();
</script>