<?php
$ENABLE_ADD     = has_permission('Jurnal_Penerimaan.Add');
$ENABLE_MANAGE  = has_permission('Jurnal_Penerimaan.Manage');
$ENABLE_VIEW    = has_permission('Jurnal_Penerimaan.View');
$ENABLE_DELETE  = has_permission('Jurnal_Penerimaan.Delete');
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
</style>
<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<div class="box">
    <div class="box-header">
        <button type="button" class="btn btn-sm btn-success download_excel">
            <i class="fa fa-download"></i> Excel
        </button>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <!-- <button type="button" class="btn btn-sm btn-primary" onclick="fix_company()">Fix Company</button> -->
        <table id="table_penawaran" class="table table-bordered table-striped">
            <thead>
                <tr class="bg-blue">
                    <th class="text-center">No.</th>
                    <th class="text-center">Tgl</th>
                    <th class="text-center">Klien</th>
                    <th class="text-center">No. Invoice</th>
                    <th class="text-center">Keterangan Tagihan</th>
                    <th class="text-center">Company</th>
                    <th class="text-center">Nama Divisi</th>
                    <th class="text-center">COA</th>
                    <th class="text-center">Perkiraan</th>
                    <th class="text-center">Uraian</th>
                    <th class="text-center">Original</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>

        </table>
    </div>
    <!-- /.box-body -->
</div>

<div class="modal modal-default fade" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="width: 1200px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel"><span class="fa fa-users"></span> Posting Jurnal</h4>
            </div>
            <form action="" method="post" id="frm-data">
                <div class="modal-body" id="ModalView">

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary save_btn_modal"><i class="fa fa-save"></i> Save</button>
                    <button type="button" class="btn btn-warning" onclick="revisi_jurnal()"><i class="fa fa-pencil"></i> Revisi</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">
                        <span class="glyphicon glyphicon-remove"></span> Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="form-data"></div>
<!-- DataTables -->

<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script src="https://cdn.datatables.net/2.1.7/js/dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- page script -->
<script type="text/javascript">
    $(document).ready(function() {
        DataTables();
        autoNum();
    });

    $(document).on('click', '.posting_jurnal', function() {
        var id = $(this).data('id');

        $.ajax({
            type: 'post',
            url: siteurl + active_controller + 'modal_posting_jurnal',
            data: {
                'id': id
            },
            cache: false,
            success: function(result) {
                $('#ModalView').html(result);

                $('#dialog-popup').modal('show');
                autoNum();
            },
            error: function(result) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error !',
                    text: 'Please try again later !',
                    allowOutsideClick: false,
                    timer: 3000,
                    showConfirmButton: false,
                    showCancelButton: false
                });
            }
        });
    });

    $(document).on('submit', '#frm-data', function(e) {
        e.preventDefault();

        Swal.fire({
            icon: 'warning',
            title: 'Are you sure ?',
            text: 'This data will be moved to Tras !',
            showConfirmButton: true,
            showCancelButton: true,
            allowOutsideClick: false
        }).then((next) => {
            if (next.isConfirmed) {
                var formdata = $('#frm-data').serialize();

                $.ajax({
                    type: 'post',
                    url: siteurl + active_controller + 'save_posting_jurnal',
                    data: formdata,
                    cache: false,
                    dataType: 'json',
                    success: function(result) {
                        if (result.status == '1') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success !',
                                text: result.msg,
                                allowOutsideClick: false,
                                timer: 3000,
                                showConfirmButton: false,
                                showCancelButton: false
                            }).then(() => {
                                location.reload(true);
                            });
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Failed !',
                                text: result.msg,
                                allowOutsideClick: false,
                                timer: 3000,
                                showConfirmButton: false,
                                showCancelButton: false
                            });
                        }
                    },
                    error: function(result) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error !',
                            text: 'Please try again later !',
                            allowOutsideClick: false,
                            timer: 3000,
                            showConfirmButton: false,
                            showCancelButton: false
                        });
                    }
                });
            }
        });
    });

    $(document).on('click', '.download_excel', function() {
        window.open(siteurl + active_controller + 'download_excel', '_blank');
    })

    function revisi_jurnal() {
        var alasan_revisi = $('textarea[name="alasan_revisi"]').val();
        if (alasan_revisi == '') {
            Swal.fire({
                icon: 'warning',
                title: 'Warning !',
                text: 'Alasan revisi harus diisi !',
                showConfirmButton: false,
                showCancelButton: false,
                allowOutsideClick: false,
                timer: 3000
            }).then(() => {
                return false;
            });
        }

        Swal.fire({
            icon: 'warning',
            title: 'Anda yakin ?',
            text: 'Data jurnal ini akan masuk ke Revisi Jurnal !',
            showCancelButton: true,
            showConfirmButton: true,
            allowOutsideClick: false
        }).then((next) => {
            if (next.isConfirmed) {
                var id = $('input[name="id"]').val();

                $.ajax({
                    type: 'post',
                    url: siteurl + active_controller + 'update_sts_revisi_jurnal',
                    data: {
                        'id': id,
                        'alasan_revisi': alasan_revisi
                    },
                    cache: false,
                    dataType: 'json',
                    success: function(result) {
                        if (result.status == '1') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success !',
                                text: result.msg,
                                showConfirmButton: false,
                                showCancelButton: false,
                                allowOutsideClick: false,
                                timer: 3000
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Failed !',
                                text: result.msg,
                                showConfirmButton: false,
                                showCancelButton: false,
                                allowOutsideClick: false,
                                timer: 3000
                            });
                        }
                    },
                    error: function(result) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error !',
                            text: 'Please try again later !',
                            showConfirmButton: false,
                            showCancelButton: false,
                            allowOutsideClick: false,
                            timer: 3000
                        });
                    }
                });
            }
        });
    }

    function autoNum() {
        $('.autonum').autoNumeric('init');
    }

    function DataTables() {
        // var dataTables = $('#table_penawaran').dataTable();
        // dataTables.destroy();

        var dataTables = $('#table_penawaran').dataTable({
            ajax: {
                url: siteurl + active_controller + 'get_data_jurnal_invoicing',
                type: "POST",
                dataType: "JSON",
                data: function(d) {

                }
            },
            columns: [{
                    data: 'no'
                },
                {
                    data: 'tgl'
                },
                {
                    data: 'klien'
                },
                {
                    data: 'no_invoice'
                },
                {
                    data: 'keterangan_tagihan'
                },
                {
                    data: 'company'
                },
                {
                    data: 'nm_divisi'
                },
                {
                    data: 'coa'
                },
                {
                    data: 'perkiraan'
                },
                {
                    data: 'uraian'
                },
                {
                    data: 'original'
                },
                {
                    data: 'action'
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