<?php

/**
 * Pelaporan Petty Cash - Halaman Konfirmasi
 *
 * Menampilkan ringkasan pelaporan dan detail pencatatan sebelum submit (Ajukan).
 * PHP vars: $pelaporan (object with header + pencatatan_list), $budget_info, $has_add, $has_manage
 *
 * @author Sendigs Finance
 */

// Header data
$header         = $pelaporan->header;
$pencatatan_list = $pelaporan->pencatatan_list;
$jumlah_item    = count($pencatatan_list);

// Format periode DD/MM/YYYY
$periode_start_fmt = date('d/m/Y', strtotime($header->periode_start));
$periode_end_fmt   = date('d/m/Y', strtotime($header->periode_end));

// Budget info
$sisa_budget = isset($budget_info->sisa_budget) ? $budget_info->sisa_budget : 0;
?>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-file-text-o"></i>&nbsp;Konfirmasi Pelaporan Petty Cash</h3>
    </div>
    <div class="box-body">
        <!-- Ringkasan Info Pelaporan -->
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered table-condensed">
                    <tr>
                        <th width="180">No Pelaporan</th>
                        <td><strong><?= htmlspecialchars($header->no_pelaporan) ?></strong></td>
                    </tr>
                    <tr>
                        <th>Periode</th>
                        <td><?= $periode_start_fmt ?> s.d. <?= $periode_end_fmt ?></td>
                    </tr>
                    <tr>
                        <th>Company</th>
                        <td><span class="label label-info"><?= htmlspecialchars($header->company) ?></span></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-bordered table-condensed">
                    <tr>
                        <th width="180">Grand Total Periode</th>
                        <td class="text-right"><strong>Rp <?= number_format($header->grand_total, 0, ',', '.') ?></strong></td>
                    </tr>
                    <tr>
                        <th>Jumlah Item</th>
                        <td><?= $jumlah_item ?> Pencatatan</td>
                    </tr>
                    <tr>
                        <th>Sisa Budget</th>
                        <td class="text-right <?= ($sisa_budget < 0) ? 'text-red' : '' ?>">
                            Rp <?= number_format($sisa_budget, 0, ',', '.') ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <hr>

        <!-- Detail Pencatatan Table -->
        <h4><i class="fa fa-list"></i>&nbsp;Detail Pencatatan</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr class="bg-primary">
                        <th width="40" class="text-center">No</th>
                        <th>No Pencatatan</th>
                        <th width="100" class="text-center">Tanggal</th>
                        <th>Pengeluaran</th>
                        <th>Deskripsi</th>
                        <th>Request By</th>
                        <th width="130" class="text-right">Nominal</th>
                        <th width="80" class="text-center">Evidence</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($pencatatan_list)) : ?>
                        <?php $no = 1; ?>
                        <?php foreach ($pencatatan_list as $item) : ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td><?= htmlspecialchars($item->no_pencatatan) ?></td>
                                <td class="text-center"><?= date('d/m/Y', strtotime($item->tanggal)) ?></td>
                                <td><?= htmlspecialchars(isset($item->pengeluaran_summary) ? $item->pengeluaran_summary : '-') ?></td>
                                <td><?= htmlspecialchars($item->keterangan ?: '-') ?></td>
                                <td><?= htmlspecialchars($item->request_by) ?></td>
                                <td class="text-right"><?= number_format($item->grand_total, 0, ',', '.') ?></td>
                                <td class="text-center">
                                    <a href="<?= site_url('expense_petty_cash/view/' . $item->id) ?>" class="btn btn-xs btn-default" title="Lihat Detail & Evidence" target="_blank">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="8" class="text-center">Tidak ada data pencatatan</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="bg-gray">
                        <th colspan="6" class="text-right">Grand Total:</th>
                        <th class="text-right"><?= number_format($header->grand_total, 0, ',', '.') ?></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <!-- /.box-body -->

    <div class="box-footer">
        <?php if ($header->status === 'draft' && ($has_add || $has_manage)) : ?>
            <button type="button" class="btn btn-success" id="btn-ajukan">
                <i class="fa fa-paper-plane"></i>&nbsp;Ajukan Pelaporan
            </button>
        <?php endif; ?>
        <a href="<?= site_url('expense_petty_cash/pelaporan') ?>" class="btn btn-warning">
            <i class="fa fa-reply"></i>&nbsp;Kembali
        </a>
    </div>
</div>

<!-- SweetAlert2 CDN (if not already loaded by template) -->
<script>
    (function() {
        var BASE_URL = '<?= site_url('expense_petty_cash/') ?>';
        var PELAPORAN_ID = <?= json_encode($header->id) ?>;

        // "Ajukan" button handler
        var btnAjukan = document.getElementById('btn-ajukan');
        if (btnAjukan) {
            btnAjukan.addEventListener('click', function() {
                swal({
                    title: 'Konfirmasi Pengajuan',
                    text: 'Apakah Anda yakin ingin mengajukan pelaporan ini? Status akan berubah menjadi "Waiting Approval" dan pencatatan terkait tidak dapat diedit.',
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Ajukan!',
                    cancelButtonText: 'Batal',
                    showLoaderOnConfirm: true,
                    preConfirm: function() {
                        return new Promise(function(resolve, reject) {
                            $.ajax({
                                url: BASE_URL + 'submit_pelaporan/' + PELAPORAN_ID,
                                type: 'POST',
                                dataType: 'json',
                                data: {
                                    '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
                                },
                                success: function(response) {
                                    resolve(response);
                                },
                                error: function(xhr) {
                                    reject('Terjadi kesalahan pada server');
                                }
                            });
                        });
                    },
                    allowOutsideClick: false
                }).then(function(response) {
                    if (response.status === true) {
                        swal({
                            title: 'Berhasil!',
                            text: response.message || 'Pelaporan berhasil diajukan.',
                            type: 'success'
                        }).then(function() {
                            window.location.href = BASE_URL + 'pelaporan';
                        });
                    } else {
                        swal({
                            title: 'Gagal!',
                            text: response.message || 'Gagal mengajukan pelaporan.',
                            type: 'error'
                        });
                    }
                }).catch(function(error) {
                    swal({
                        title: 'Error!',
                        text: error || 'Terjadi kesalahan.',
                        type: 'error'
                    });
                });
            });
        }
    })();
</script>