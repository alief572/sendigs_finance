<?php
$ENABLE_ADD     = has_permission('Actual_Plan_Tagih.Add');
$ENABLE_MANAGE  = has_permission('Actual_Plan_Tagih.Manage');
$ENABLE_VIEW    = has_permission('Actual_Plan_Tagih.View');
$ENABLE_DELETE  = has_permission('Actual_Plan_Tagih.Delete');
?>
<!-- <link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>"> -->
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.7/css/dataTables.dataTables.min.css">

<style>
    .btn {
        border-radius: 10px;
    }

    .dropdown-menu {
        top: 100%;
        position: absolute;
        overflow: auto;
    }

    .tab-pin {
        width: 100% !important;
    }
</style>
<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<div class="box">
    <div class="box-header">

    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <ul class="nav nav-tabs" role="tablist">
            <!-- <li role="presentation" class="kasbon_tab tab_pin active"><a href="javascript:void();" onclick="change_tab('kasbon')">Kasbon</a></li>
            <li role="presentation" class="expense_tab tab_pin"><a href="javascript:void();" onclick="change_tab('expense')">Expense</a></li> -->
            <?php
            for ($i = 1; $i <= 12; $i++) {
                $active = '';
                if ($i == 1) {
                    $active = 'active';
                }
                echo '<li role="presentation" class="tab_pin tab_' . $i . ' ' . $active . '" data-no="' . $i . '"><a href="javascript:void(0);">' . date('F', strtotime(date('Y') . '-' . sprintf('%02d', $i) . '-01')) . '</a></li>';
            }

            echo '<li role="presentation" class="tab_pin" data-no="macet"><a href="javascript:void(0);">Tagihan Macet</a></li>';
            ?>

        </ul>

        <br><br>

        <table id="table_penawaran" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th class="text-center" width="5%">No.</th>
                    <th class="text-center" width="15%">Company</th>
                    <th class="text-center" width="15%">No. SPK</th>
                    <th class="text-center" width="20%">Customer</th>
                    <th class="text-center" width="15%">Project</th>
                    <th class="text-center" width="15%">Project Leader</th>
                    <th class="text-center" width="15%">Sales</th>
                    <th class="text-center" width="10%">Status</th>
                    <th class="text-center" width="15%">Action</th>
                </tr>
            </thead>

        </table>
    </div>
    <!-- /.box-body -->
</div>
<div id="form-data"></div>
<input type="hidden" id="bulan" value="1">

<div class="modal modal-default fade" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel">Update Actual Plan Tagih</h4>
            </div>
            <form action="" method="post" id="frm-data" enctype="multipart/form-data">
                <div class="modal-body" id="ModalViewCP">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <span class="glyphicon glyphicon-remove"></span> Cancel
                    </button>
                    <button type="submit" class="btn btn-sm btn-success">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal modal-default fade" id="dialog-popup-macet" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel">Update Tagihan Macet</h4>
            </div>
            <form action="" method="post" id="frm-data-macet" enctype="multipart/form-data">
                <div class="modal-body" id="ModalViewCPMacet">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <span class="glyphicon glyphicon-remove"></span> Cancel
                    </button>
                    <button type="submit" class="btn btn-sm btn-success">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- DataTables -->
<!-- <script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script> -->

<script src="https://cdn.datatables.net/2.1.7/js/dataTables.min.js"></script>
<!-- page script -->
<script type="text/javascript">
    $(document).ready(function() {
        var bulan = $('#bulan').val();
        DataTables(bulan);
    });

    $(document).on('click', '.tab_pin', function() {
        var bulan = $(this).data('no');

        $('.tab_pin').removeClass('active');
        $('.tab_' + bulan).addClass('active');
        $('#bulan').val(bulan);
        DataTables(bulan);
    });

    $(document).on('click', '.aktual_tagihan', function() {
        var id = $(this).data('id');

        $.ajax({
            type: 'post',
            url: siteurl + active_controller + 'aktual_tagihan_get',
            data: {
                'id': id
            },
            cache: false,
            success: function(result) {
                $('#ModalViewCP').html(result);
                $('#dialog-popup').modal('show');
            },
            error: function(result) {
                swal({
                    type: 'error',
                    title: 'Error !',
                    text: 'Please, try again later !'
                });
            }
        });
    });

    $(document).on('click', '.aktual_tagihan_macet', function() {
        var id = $(this).data('id');

        $.ajax({
            type: 'post',
            url: siteurl + active_controller + 'aktual_tagihan_macet_get',
            data: {
                'id': id
            },
            cache: false,
            success: function(result) {
                $('#ModalViewCPMacet').html(result);
                $('#dialog-popup-macet').modal('show');
            },
            error: function(result) {
                swal({
                    type: 'error',
                    title: 'Error !',
                    text: 'Please, try again later !'
                });
            }
        });
    });

    $(document).on('change', 'select[name="tagih_mundur"]', function() {
        var tagih_mundur = $(this).val();

        if (tagih_mundur == '1' || tagih_mundur == '3') {
            $('input[name="tanggal_actual"]').attr('readonly', true);
            $('textarea[name="alasan_mundur"]').attr('readonly', true);
            $('input[name="upload_surat_mundur"]').prop('disabled', true);
        }
        if (tagih_mundur == '2') {
            $('input[name="tanggal_actual"]').attr('readonly', false);
            $('textarea[name="alasan_mundur"]').attr('readonly', false);
            $('input[name="upload_surat_mundur"]').prop('disabled', false);
        }

    });

    $(document).on('submit', '#frm-data', function(e) {
        e.preventDefault();

        var tagih_mundur = $('select[name="tagih_mundur"]').val();

        if (tagih_mundur == '2') {
            var tanggal_actual = $('input[name="tanggal_actual"]').val();
            var alasan_mundur = $('textarea[name="alasan_mundur"]').val();
            var upload_surat_mundur = $('input[name="upload_surat_mundur"]').val();

            var valid = 1;
            var msg = '';
            if (valid == 1 && tanggal_actual.length < 1) {
                var valid = 0;

                var msg = 'Mohon pilih dulu tanggal actual plan tagih nya !';
            }
            if (valid == 1 && alasan_mundur.length < 1) {
                var valid = 0;

                var msg = 'Mohon isi dulu alasan mundur plan tagih nya !';
            }
            if (valid == 1 && upload_surat_mundur.length < 1) {
                var valid = 0;

                var msg = 'Mohon pilih dulu file surat mundur plan tagih nya !';
            }

            if (valid !== 1) {
                swal({
                    type: 'warning',
                    title: 'Warning !',
                    text: msg
                });

                return false;
            }
        }

        swal({
            type: 'warning',
            title: 'Warning !',
            text: 'Are you sure ?',
            showCancelButton: true
        }, function(next) {
            if (next) {
                var form_data = new FormData($('#frm-data')[0]);

                $.ajax({
                    type: 'post',
                    url: siteurl + active_controller + 'save_actual_plan_tagih',
                    data: form_data,
                    dataType: 'json',
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(result) {
                        if (result.status == '1') {
                            swal({
                                type: 'success',
                                title: 'Success !',
                                text: result.msg
                            }, function(lanjut) {
                                location.reload();
                            });
                        } else {
                            swal({
                                type: 'warning',
                                title: 'Warning !',
                                text: result.msg
                            });
                        }
                    },
                    error: function() {
                        swal({
                            type: 'error',
                            title: 'Error !',
                            text: 'Please try again later !'
                        });
                    }
                });
            }
        });
    });

    $(document).on('submit', '#frm-data-macet', function(e) {
        e.preventDefault();

        var tagih_mundur = $('select[name="tagih_mundur"]').val();

        swal({
            type: 'warning',
            title: 'Warning !',
            text: 'Are you sure ?',
            showCancelButton: true
        }, function(next) {
            if (next) {
                var form_data = new FormData($('#frm-data-macet')[0]);

                $.ajax({
                    type: 'post',
                    url: siteurl + active_controller + 'save_actual_plan_tagih_macet',
                    data: form_data,
                    dataType: 'json',
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(result) {
                        if (result.status == '1') {
                            swal({
                                type: 'success',
                                title: 'Success !',
                                text: result.msg
                            }, function(lanjut) {
                                location.reload();
                            });
                        } else {
                            swal({
                                type: 'warning',
                                title: 'Warning !',
                                text: result.msg
                            });
                        }
                    },
                    error: function() {
                        swal({
                            type: 'error',
                            title: 'Error !',
                            text: 'Please try again later !'
                        });
                    }
                });
            }
        });
    });

    function DataTables(bulan) {
        // var dataTables = $('#table_penawaran').dataTable();
        // dataTables.destroy();

        var dataTables = $('#table_penawaran').dataTable({
            ajax: {
                url: siteurl + active_controller + 'get_actual_plan_tagih',
                type: "POST",
                dataType: "JSON",
                data: function(d) {
                    d.bulan = bulan;
                }
            },
            columns: [{
                    data: 'no',
                },
                {
                    data: 'company'
                },
                {
                    data: 'no_spk'
                },
                {
                    data: 'customer'
                },
                {
                    data: 'project'
                },
                {
                    data: 'project_leader'
                },
                {
                    data: 'sales'
                },
                {
                    data: 'status'
                },
                {
                    data: 'option'
                }
            ],
            responsive: true,
            processing: true,
            serverSide: true,
            stateSave: true,
            destroy: true,
            paging: true
        });
    }
</script>
<script src="<?= base_url('assets/js/basic.js') ?>"></script>