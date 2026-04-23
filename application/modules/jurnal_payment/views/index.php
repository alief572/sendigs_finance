<?php
$ENABLE_ADD     = has_permission('Jurnal.Add');
$ENABLE_MANAGE  = has_permission('Jurnal.Manage');
$ENABLE_VIEW    = has_permission('Jurnal.View');
$ENABLE_DELETE  = has_permission('Jurnal.Delete');
?>
<!-- <link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>"> -->
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.7/css/dataTables.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
</style>
<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<div class="box">
    <div class="box-header">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="">Tgl Jurnal</label>
                    <input type="text" class="form-control form-control-sm" id="tgl_jurnal" name="tgl_jurnal">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="">No. Transaksi</label>
                    <select name="no_transaksi" id="no_transaksi" class="form-control form-control-sm select2">
                        <option value="">- Pilih No. Transaksi -</option>
                        <?php
                        if (!empty($list_no_transaksi)) {
                            foreach ($list_no_transaksi as $row) {
                        ?>
                                <option value="<?= $row['no_transaksi'] ?>"><?= $row['no_transaksi'] ?></option>
                        <?php
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="">Company</label>
                    <select name="company" id="company" class="form-control form-control-sm select2">
                        <option value="">- Pilih Company -</option>
                        <?php
                        if (!empty($list_company)) {
                            foreach ($list_company as $row) {
                        ?>
                                <option value="<?= $row['id_company'] ?>"><?= $row['nm_company'] ?></option>
                        <?php
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <br>
                <button type="button" class="btn btn-sm btn-primary" onclick="search_jurnal()"><i class="fa fa-search"></i> Search</button>
                <button type="button" class="btn btn-sm btn-danger" onclick="reset_search_jurnal()"><i class="fa fa-refresh"></i> Reset</button>
                <button type="button" class="btn btn-sm btn-success" onclick="export_jurnal()"><i class="fa fa-download"></i> Export</button>
            </div>
        </div>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <!-- <button type="button" class="btn btn-sm btn-primary" onclick="fix_company()">Fix Company</button> -->
        <table id="table_penawaran" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th class="text-center">No.</th>
                    <th class="text-center">No. Transaksi</th>
                    <th class="text-center">Jenis Transaksi</th>
                    <th class="text-center">Tanggal Jurnal</th>
                    <th class="text-center">Company</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>

        </table>
    </div>
    <!-- /.box-body -->
</div>

<div class="modal modal-default fade" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="width: 150vh;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel"><span class="fa fa-users"></span>Posting Jurnal</h4>
            </div>
            <form action="" method="post" id="frm-data">
                <div class="modal-body" id="ModalView">

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary save_btn_modal"><i class="fa fa-save"></i> Save</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">
                        <span class="glyphicon glyphicon-remove"></span> Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="form-data"></div>
<!-- DataTables -->

<script src="https://cdn.datatables.net/2.1.7/js/dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- page script -->
<script type="text/javascript">
    $(document).ready(function() {
        DataTables();

        select2();

        $('#tgl_jurnal').flatpickr({
            dateFormat: 'Y-m-d',
            allowInput: true,
            mode: 'range'
        });
    });

    $(document).on('submit', '#frm-data', function(e) {
        e.preventDefault();

        swal({
            type: 'warning',
            title: 'Are you sure ?',
            text: 'This data will be posted to Tras!',
            showCancelButton: true,
            allowOutsideClick: false
        }, function(value) {
            if (value) {
                var data = $('#frm-data').serialize();

                $.ajax({
                    type: 'post',
                    url: siteurl + active_controller + 'save_posting_jurnal',
                    data: data,
                    cache: false,
                    dataType: 'json',
                    success: function(result) {
                        if (result.save == '1') {
                            swal({
                                type: 'success',
                                title: 'Success !',
                                text: result.msg,
                                timer: 3000,
                                allowOutsideClick: false
                            }, function(lanjut) {
                                swal.close();
                                $('#dialog-popup').modal('hide');

                                DataTables();
                            });
                        } else {
                            swal({
                                type: 'warning',
                                title: 'Failed !',
                                text: 'Please try again later !',
                                timer: 3000,
                                allowOutsideClick: false
                            });
                        }
                    },
                    error: function(result) {
                        swal({
                            type: 'error',
                            title: 'Error !',
                            text: 'Please try again later !',
                            timer: 3000,
                            allowOutsideClick: false
                        });
                    }
                });
            }
        });
    });

    function add_jurnal(id) {

        $.ajax({
            type: 'post',
            url: siteurl + active_controller + 'add_jurnal',
            data: {
                'id': id
            },
            cache: false,
            success: function(result) {
                $('#ModalView').html(result);
                $('#dialog-popup').modal('show');
            },
            error: function(result) {
                swal({
                    type: 'error',
                    title: 'Error !',
                    text: 'Please try again later !',
                    timer: 3000,
                    allowOutsideClick: false
                });
            }
        });


    }

    function fix_company() {
        $.ajax({
            type: 'post',
            url: siteurl + active_controller + 'fix_company',
            cache: false,
            dataType: 'json',
            success: function(result) {
                if (result.status == '1') {
                    swal({
                        type: 'success',
                        title: 'Success !',
                        text: 'Company fixed !',
                        allowOutsideClick: false,
                        timer: 3000
                    });
                } else {
                    swal({
                        type: 'warning',
                        title: 'Failed !',
                        text: 'Please try again later !',
                        allowOutsideClick: false,
                        timer: 3000
                    });
                }
            },
            error: function(result) {
                swal({
                    type: 'error',
                    title: 'Error !',
                    text: 'Please try again later !',
                    allowOutsideClick: false,
                    timer: 3000
                });
            }
        });
    }

    function DataTables(tgl_jurnal = null, no_transaksi = null, company = null) {
        if ($.fn.DataTable.isDataTable('#table_penawaran')) {
            $('#table_penawaran').DataTable().ajax.reload(null, false);
            return;
        }

        $('#table_penawaran').DataTable({
            ajax: {
                url: siteurl + active_controller + 'get_data_jurnal',
                type: "POST",
                dataType: "JSON",
                data: function(d) {
                    d.tgl_jurnal = tgl_jurnal;
                    d.no_transaksi = no_transaksi;
                    d.company = company;
                }
            },
            columns: [{
                    data: 'no'
                },
                {
                    data: 'no_transaksi'
                },
                {
                    data: 'jenis_transaksi'
                },
                {
                    data: 'tanggal_jurnal'
                },
                {
                    data: 'company'
                },
                {
                    data: 'action'
                }
            ],
            columnDefs: [{
                    targets: [0, 5],
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                {
                    targets: [1, 2, 3, 4],
                    className: 'text-center'
                }
            ],
            responsive: true,
            processing: true,
            serverSide: true,
            stateSave: true,
            paging: true,
            searchDelay: 500,
            lengthMenu: [
                [10, 25, 50, 100],
                [10, 25, 50, 100]
            ],
            pageLength: 10
        });
    }

    function search_jurnal() {
        var tgl_jurnal = $('#tgl_jurnal').val();
        var no_transaksi = $('#no_transaksi').val();
        var company = $('#company').val();

        DataTables(tgl_jurnal, no_transaksi, company)
    }

    function reset_search_jurnal() {
        var tgl_jurnal = $('#tgl_jurnal').val('');
        var no_transaksi = $('#no_transaksi').val('');
        var company = $('#company').val('');

        DataTables()
    }

    function export_jurnal() {
        var tgl_jurnal = $('#tgl_jurnal').val();
        var no_transaksi = $('#no_transaksi').val();
        var company = $('#company').val();

        window.open(siteurl + active_controller + 'export_jurnal?tgl_jurnal=' + tgl_jurnal + '&no_transaksi=' + no_transaksi + '&company=' + company, '_blank');
    }

    function select2() {
        $('.select2').select2({
            width: '100%'
        });
    }
</script>
<script src="<?= base_url('assets/js/basic.js') ?>"></script>