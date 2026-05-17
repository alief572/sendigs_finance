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
        <div class="col-md-2">
            <div class="form-group">
                <label for="">Tahun</label>
                <input type="number" class="form-control form-control-sm inp_tahun" min="2000" max="2100" value="<?= date('Y') ?>">
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label for="">Status</label>
                <select name="status" id="status" class="form-control form-control-sm">
                    <option value="">Pilih Status</option>
                    <option value="1">Tagih</option>
                    <option value="2">Mundur</option>
                    <option value="3">Waiting Actual Plan Tagih</option>
                </select>
            </div>
        </div>
        <div class="col-md-1">
            <br>
            <button type="button" class="btn btn-sm btn-success download_excel" title="Download Excel"><i class="fa fa-download"></i> Download Excel</button>
            <?php if ($ENABLE_MANAGE): ?>
                <button type="button" class="btn btn-sm btn-primary btn_batch_process" title="Batch Process Tagih"><i class="fa fa-cogs"></i> Batch Process Tagih</button>
            <?php endif; ?>
            <!-- <button type="button" class="btn btn-sm btn-danger" onclick="update_actual_plan_tagih()">UPDATE !</button> -->
        </div>
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
                    <th class="text-center" width="15%">Keterangan</th>
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
                    <button type="submit" class="btn btn-sm btn-success"><i class="fa fa-save"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- DataTables -->
<!-- <script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script> -->

<script src="https://cdn.datatables.net/2.1.7/js/dataTables.min.js"></script>
<!-- page script -->
<script type="text/javascript">
    $(document).ready(function() {
        var bulan = $('#bulan').val();
        var tahun = $('.inp_tahun').val();
        var status = $('#status').val();

        DataTables(bulan, tahun, status);
    });

    $(document).on('click', '.tab_pin', function() {
        var bulan = $(this).data('no');
        var tahun = $('.inp_tahun').val();
        var status = $('#status').val();

        $('.tab_pin').removeClass('active');
        $('.tab_' + bulan).addClass('active');
        $('#bulan').val(bulan);
        DataTables(bulan, tahun, status);
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
                Swal.fire({
                    icon: 'error',
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
                Swal.fire({
                    icon: 'error',
                    title: 'Error !',
                    text: 'Please, try again later !'
                });
            }
        });
    });

    $(document).on('change', 'select[name="tagih_mundur"]', function() {
        var tagih_mundur = $(this).val();
        var tgl_plan_tagih = $('input[name="tgl_plan_tagih"]').val();
        var current_tanggal_actual = $('input[name="tanggal_actual"]').val();

        if (tagih_mundur == '1' || tagih_mundur == '3') {
            $('input[name="tanggal_actual"]').prop('disabled', true);
            $('input[name="tanggal_actual"]').val(tgl_plan_tagih);
            $('textarea[name="alasan_mundur"]').attr('readonly', true);
            $('input[name="upload_surat_mundur"]').prop('disabled', true);

            if (tagih_mundur == '1') {
                Swal.fire({
                    icon: 'info',
                    title: 'Info',
                    text: 'Anda memilih "Tagih". Tanggal actual akan menggunakan tgl_plan_tagih.',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
            }
        }
        if (tagih_mundur == '2') {
            if (current_tanggal_actual && current_tanggal_actual !== tgl_plan_tagih) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Warning',
                    text: 'Akan switch ke "Mundur". Tanggal yang Anda input sebelumnya akan di-reset. Mohon isi tanggal baru.',
                    confirmButtonColor: '#f0ad4e',
                    confirmButtonText: 'OK'
                });
            }
            $('input[name="tanggal_actual"]').prop('disabled', false);
            $('input[name="tanggal_actual"]').val('');
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
                Swal.fire({
                    icon: 'warning',
                    title: 'Warning !',
                    text: msg
                });

                return false;
            }
        }

        Swal.fire({
            type: 'warning',
            title: 'Warning !',
            text: 'Are you sure ?',
            showCancelButton: true
        }).then((next) => {
            if (next.isConfirmed) {
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
                            Swal.fire({
                                icon: 'success',
                                title: 'Success !',
                                text: result.msg,
                                timer: 3000,
                                showConfirmButton: false,
                                allowOutsideClick: false
                            }).then(() => {
                                Swal.close();
                                $('#dialog-popup').modal('hide');
                                DataTables();
                            });

                        } else {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Warning !',
                                text: result.msg,
                                showConfirmButton: false,
                                allowOutsideClick: false,
                                timer: 3000
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        try {
                            // Kita parse isi dari xhr.responseText
                            var response = JSON.parse(xhr.responseText);

                            // Munculin pesannya (msg sesuai dengan key di PHP)
                            var msg = "Terjadi kesalahan :" + response.msg;
                        } catch (e) {
                            var msg = "Terjadi kesalahan sistem";
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error !',
                            text: msg,
                            showConfirmButton: false,
                            allowOutsideClick: false,
                            timer: 3000
                        });
                    }
                });
            }
        });
    });

    $(document).on('submit', '#frm-data-macet', function(e) {
        e.preventDefault();

        var tagih_mundur = $('select[name="tagih_mundur"]').val();

        Swal.fire({
            icon: 'warning',
            title: 'Warning !',
            text: 'Are you sure ?',
            showCancelButton: true
        }).then((next) => {
            if (next.isConfirmed) {
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
                            Swal.fire({
                                icon: 'success',
                                title: 'Success !',
                                text: result.msg
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Warning !',
                                text: result.msg
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error !',
                            text: 'Please try again later !'
                        });
                    }
                });
            }
        });
    });

    $(document).on('change', '.inp_tahun', function() {
        var bulan = $('#bulan').val();
        var tahun = $(this).val();
        var status = $('#status').val();

        DataTables(bulan, tahun, status);
    });

    $(document).on('change', '#status', function() {
        var bulan = $('#bulan').val();
        var tahun = $('.inp_tahun').val();
        var status = $(this).val();

        DataTables(bulan, tahun, status);
    });

    $(document).on('click', '.download_excel', function() {
        var tahun = $('.inp_tahun').val();
        var status = $('#status').val();

        window.open(siteurl + active_controller + 'download_excel/' + tahun + '/' + status, '_blank');
    })

    function update_actual_plan_tagih() {
        Swal.fire({
            icon: 'warning',
            title: 'Apakah anda yakin ingin mengupdate actual plan tagih ?',
            showCancelButton: true,
            confirmButtonText: 'Ya',
            cancelButtonText: 'Tidak'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'post',
                    url: siteurl + active_controller + 'update_actual_plan_tagih',
                    data: {},
                    cache: false,
                    success: function(result) {
                        if (result.status == '1') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success !',
                                text: result.msg
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Warning !',
                                text: result.msg
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error !',
                            text: 'Please try again later !'
                        });
                    }
                });
            }
        });
    }

    // Batch Process Tagih button handler
    $(document).on('click', '.btn_batch_process', function() {
        Swal.fire({
            icon: 'warning',
            title: 'Batch Process Tagih',
            html: "Proses ini akan mengubah semua data dengan status 'Waiting Actual Plan Tagih' pada tahun 2019-2025 menjadi status 'Tagih' dan membuat Invoice secara otomatis.<br><br><strong>Aksi ini tidak dapat dibatalkan.</strong>",
            showCancelButton: true,
            confirmButtonText: 'Ya, Proses Sekarang',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                // Disable button and show loading overlay
                $('.btn_batch_process').prop('disabled', true);
                $('.box').append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');

                $.ajax({
                    type: 'POST',
                    url: '<?= site_url("actual_plan_tagih/batch_process_tagih") ?>',
                    dataType: 'json',
                    timeout: 120000,
                    success: function(response) {
                        $('.box .overlay').remove();
                        $('.btn_batch_process').prop('disabled', false);

                        Swal.fire({
                            icon: 'success',
                            title: 'Batch Process Selesai',
                            html: 'Total ditemukan: ' + response.total_found + '<br>Berhasil diproses: ' + response.total_success + '<br>Gagal: ' + response.total_failed + '<br>Durasi: ' + response.duration_seconds + ' detik'
                        }).then(function() {
                            var bulan = $('#bulan').val();
                            var tahun = $('.inp_tahun').val();
                            var status = $('#status').val();
                            DataTables(bulan, tahun, status);
                        });
                    },
                    error: function() {
                        $('.box .overlay').remove();
                        $('.btn_batch_process').prop('disabled', false);

                        Swal.fire({
                            icon: 'error',
                            title: 'Batch Process Gagal',
                            text: 'Terjadi kesalahan saat memproses batch. Silakan coba lagi.'
                        });
                    }
                });
            }
        });
    });

    function DataTables(bulan, tahun, status = null) {
        var dataTables = $('#table_penawaran').dataTable({
            ajax: {
                url: siteurl + active_controller + 'get_actual_plan_tagih',
                type: "POST",
                dataType: "JSON",
                data: function(d) {
                    d.bulan = bulan;
                    d.tahun = tahun;
                    d.status = status;
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
                    data: 'keterangan'
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