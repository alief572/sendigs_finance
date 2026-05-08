<?php
$ENABLE_VIEW = has_permission('Laporan_Kunjungan.View');
?>

<style>
    .btn {
        border-radius: 10px;
    }
    .info-label {
        font-weight: bold;
        color: #555;
    }
    .mandays-exceeded {
        color: #d9534f;
        font-weight: bold;
    }
    .mandays-normal {
        color: #5cb85c;
        font-weight: bold;
    }
</style>

<div class="box">
    <div class="box-header">
        <h3 class="box-title">Detail Project SPK</h3>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <td class="info-label" width="35%">No SPK</td>
                        <td><?php echo htmlspecialchars($spk_detail->id_spk_budgeting); ?></td>
                    </tr>
                    <tr>
                        <td class="info-label">Perusahaan</td>
                        <td><?php echo htmlspecialchars($spk_detail->nm_customer); ?></td>
                    </tr>
                    <tr>
                        <td class="info-label">Project</td>
                        <td><?php echo htmlspecialchars($spk_detail->nm_project); ?></td>
                    </tr>
                    <tr>
                        <td class="info-label">Project Leader</td>
                        <td><?php echo htmlspecialchars(ucfirst($spk_detail->nm_project_leader)); ?></td>
                    </tr>
                    <tr>
                        <td class="info-label">Konsultan</td>
                        <td><?php echo htmlspecialchars(ucfirst($spk_detail->nama_konsultan)); ?></td>
                    </tr>
                    <tr>
                        <td class="info-label">Target Selesai</td>
                        <td>
                            <?php
                            if (!empty($spk_detail->waktu_to)) {
                                echo date('d-m-Y', strtotime($spk_detail->waktu_to));
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="info-label">Paket</td>
                        <td><?php echo htmlspecialchars($spk_detail->nm_paket ?? '-'); ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <div class="box box-solid">
                    <div class="box-header with-border">
                        <h4 class="box-title">Informasi Mandays</h4>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered">
                            <tr>
                                <td class="info-label" width="50%">Mandays Allocation</td>
                                <td><?php echo number_format($mandays_allocated, 2); ?></td>
                            </tr>
                            <tr>
                                <td class="info-label">Mandays Terpakai</td>
                                <td><?php echo number_format($mandays_used, 2); ?></td>
                            </tr>
                            <tr>
                                <td class="info-label">Sisa Mandays</td>
                                <td>
                                    <span class="<?php echo ($mandays_remaining < 0) ? 'mandays-exceeded' : 'mandays-normal'; ?>">
                                        <?php echo number_format($mandays_remaining, 2); ?>
                                        <?php if ($mandays_remaining < 0): ?>
                                            <i class="fa fa-exclamation-triangle"></i>
                                            <small>(Budget Exceeded)</small>
                                        <?php endif; ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <a href="<?php echo base_url('laporan_kunjungan'); ?>" class="btn btn-default">
                    <i class="fa fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>
    <!-- /.box-body -->
</div>
