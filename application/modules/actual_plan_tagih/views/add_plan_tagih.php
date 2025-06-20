<?php
$ENABLE_ADD     = has_permission('Plan_Tagih.Add');
$ENABLE_MANAGE  = has_permission('Plan_Tagih.Manage');
$ENABLE_VIEW    = has_permission('Plan_Tagih.View');
$ENABLE_DELETE  = has_permission('Plan_Tagih.Delete');
?>
<!-- <link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>"> -->
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.7/css/dataTables.dataTables.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    .btn {
        border-radius: 10px;
    }

    .dropdown-menu {
        top: 100%;
        position: absolute;
        overflow: auto;
    }

    .pd-5 {
        padding: 5px;
    }

    .form-inline .form-control {
        width: auto;
        /* Let elements adjust automatically */
        max-width: 100%;
        /* Prevent overflow */
    }

    .form-inline {
        display: flex;
        /* Use flexbox for better alignment */
        justify-content: flex-start;
        /* Align items to the left */
        flex-wrap: nowrap;
        /* Prevent wrapping to the next line */
    }

    .top-total-project {
        width: 280px;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 15px;
    }

    .select2-container {
        width: 100% !important;
    }

    table {
        table-layout: fixed;
        width: 100%;
    }

    td,
    th {
        word-wrap: break-word;
        overflow: hidden;
    }

    .table_client th,
    td {
        padding: 5px;
    }
</style>

<form action="" method="post" id="frm-data">
    <div class="box">
        <div class="box-header">
            <h4 style="font-weight: bold;">Data Client</h4>
        </div>

        <div class="box-body">

            <table class="table_client" style="width: 100%;" border="0">
                <tr>
                    <th width="20%">No. SPK</th>
                    <td><?= $data_spk_penawaran->id_spk_penawaran ?></td>
                    <th width="20%">Project</th>
                    <td><?= $data_spk_penawaran->nm_project ?></td>
                </tr>
                <tr>
                    <th width="20%">Customer</th>
                    <td><?= $data_spk_penawaran->nm_customer ?></td>
                    <th width="20%">Project Leader</th>
                    <td><?= $data_spk_penawaran->nm_project_leader ?></td>
                </tr>
                <tr>
                    <th width="20%">Nominal</th>
                    <td>Rp. <?= number_format($data_spk_penawaran->nilai_kontrak_bersih, 2) ?></td>
                    <th width="20%"></th>
                    <td></td>
                </tr>
                <tr>
                    <th width="20%">Keterangan Penagihan</th>
                    <td>
                        <textarea class="form-control form-control-sm" name="keterangan_penagihan"></textarea>
                    </td>
                    <th width="20%"></th>
                    <td></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="box">
        <div class="box-header">
            <h4 style="font-weight: bold">TOP</h4>
        </div>

        <div class="box-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-center">TOP</th>
                        <th class="text-center">Nominal</th>
                        <th class="text-center">Desription</th>
                        <th class="text-center">Tanggal Plan Tagih</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 0;
                    foreach ($data_top_spk_penawaran as $item) {
                        $no++;

                        echo '<tr>';

                        echo '<td class="text-center">';
                        echo $item->term_payment;
                        echo '<input type="hidden" name="dt[' . $no . '][id]" value="' . $item->id . '">';
                        echo '</td>';
                        echo '<td class="text-right">Rp. ' . number_format($item->nominal_payment, 2) . '</td>';
                        echo '<td class="text-left">' . $item->desc_payment . '</td>';
                        echo '<td class="text-left">';
                        echo '<input type="date" class="form-control form-control-sm" name="dt[' . $no . '][tgl_plan_tagih]" required>';
                        echo '</td>';

                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <input type="hidden" name="id_spk_penawaran" value="<?= $data_spk_penawaran->id_spk_penawaran ?>">
    <input type="hidden" name="id_penawaran" value="<?= $data_spk_penawaran->id_penawaran ?>">
    <input type="hidden" name="id_customer" value="<?= $data_spk_penawaran->id_customer ?>">
    <input type="hidden" name="nm_customer" value="<?= $data_spk_penawaran->nm_customer ?>">
    <input type="hidden" name="id_project" value="<?= $data_spk_penawaran->id_project ?>">
    <input type="hidden" name="nm_project" value="<?= $data_spk_penawaran->nm_project ?>">
    <input type="hidden" name="id_project_leader" value="<?= $data_spk_penawaran->id_project_leader ?>">
    <input type="hidden" name="nm_project_leader" value="<?= $data_spk_penawaran->nm_project_leader ?>">
    <input type="hidden" name="nilai_bersih_project" value="<?= $data_spk_penawaran->nilai_kontrak_bersih ?>">

    <a href="<?= base_url('plan_tagih'); ?>" class="btn btn-sm btn-danger"><i class="fa fa-arrow-left"></i> Back</a>
    <button type="submit" class="btn btn-sm btn-primary">
        <i class="fa fa-save"></i> Save
    </button>
</form>

<div class="modal" id="dialog-rekap" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel"></h4>
            </div>
            <div class="modal-body" id="MyModalBody">
                ...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">
                    <span class="glyphicon glyphicon-remove"></span> Close</button>
            </div>
        </div>
    </div>
</div>

<input type="hidden" name="no_payment" value="1">
<script src="<?= base_url('assets/js/autoNumeric.js'); ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    var no_payment = parseFloat($('input[name="no_payment"]').val());
    $(document).ready(function() {
        $('.chosen_select').select2({
            width: "100%"
        });

        $('.auto_num').autoNumeric();
    });

    $(document).on('submit', '#frm-data', function(e) {
        e.preventDefault();

        swal({
            type: 'warning',
            title: 'Warning !',
            text: 'Are you sure to save this data ?',
            showCancelButton: true
        }, function(next) {
            if (next) {
                var form_data = $('#frm-data').serialize();

                $.ajax({
                    type: 'post',
                    url: siteurl + active_controller + 'save_plan_tagih',
                    data: form_data,
                    cache: false,
                    dataType: 'json',
                    success: function(result) {
                        if (result.status == '1') {
                            swal({
                                type: 'success',
                                title: 'Success !',
                                text: result.msg
                            }, function(lanjut) {
                                window.location.href = siteurl + active_controller;
                            });
                        } else {
                            swal({
                                type: 'warning',
                                title: 'Caution !',
                                text: result.msg
                            });
                        }
                    },
                    error: function(result) {
                        swal({
                            type: 'error',
                            title: 'Error !',
                            text: 'Please, try again later !'
                        });
                    }
                });
            }
        });
    });
</script>