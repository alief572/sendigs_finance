<link rel="stylesheet" href="https://cdn.datatables.net/2.1.7/css/dataTables.dataTables.min.css">
<!-- Main content-->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="tabs-container">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="<?php echo site_url('master_paket'); ?>">
                                Data Paket</a>
                        </li>
                        <li><a href="<?php echo site_url('master_paket/paket_new'); ?>">
                                Tambah Paket</a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div id="tab-1" class="tab-pane active">
                            <div class="panel panel-filled">
                                <div class="panel-body">
                                    <div class="table-responsive">
                                        <table id="my-grid" class="table table-striped table-bordered table-hover" width="100%">
                                            <thead>
                                                <tr>
                                                    <th class="column-hide" width="10">No.</th>
                                                    <th class="no-sort">Kategori Paket</th>
                                                    <th class="">Nama Paket</th>
                                                    <th class="no-sort" width="17%">Option</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="tab-2" class="tab-pane">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="MyModal" role="dialog" aria-labelledby="MyModal" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header" id="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><i class="fa fa-times-circle"></i></button>
                    <h4 class="modal-title" id="modal-title">Edit Master paket</h4>
                </div>
                <div class="modal-body" id="modal-body">

                </div>
                <div class="modal-footer" id="modal-footer">
                    <button type="button" class="btn btn-sm btn-primary save_edit_paket"><i class="fa fa-save"></i> Simpan</button>
                    <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal"><i class="fa fa-remove"></i> Cancel</button>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.datatables.net/2.1.7/js/dataTables.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        datatables();
    });

    function datatables() {
        var dataTable = $('#my-grid').DataTable();
        dataTable.destroy();

        var dataTable = $('#my-grid').DataTable({
            "serverSide": true,
            "stateSave": false,
            "bAutoWidth": true,
            "oLanguage": {
                "sSearch": "Live Search : ",
                "sLengthMenu": "_MENU_", //"_MENU_ &nbsp;&nbsp;Per Halaman ",
                "sInfo": "Showing _START_ to _END_ of _TOTAL_ entries",
                "sInfoFiltered": "(filtered from _MAX_ total entries)",
                "sZeroRecords": "<center>Data tidak ditemukan</center>",
                "sEmptyTable": "No data available in table",
                "sLoadingRecords": "Please wait - loading...",
                "oPaginate": {
                    "sPrevious": "Prev",
                    "sNext": "Next"
                }
            },
            "aaSorting": [
                [0, "desc"]
            ],
            "columnDefs": [{
                    "aTargets": [0],
                    "sClass": "column-hide"
                },
                {
                    "aTargets": 'no-sort',
                    "orderable": false
                }
            ],
            "sPaginationType": "simple_numbers",
            "iDisplayLength": 10,
            "aLengthMenu": [
                [10, 20, 50, 100, 150],
                [10, 20, 50, 100, 150]
            ],
            "ajax": {
                url: "<?php echo site_url('master_paket/display_paket_json'); ?>",
                type: "post",
                error: function() {
                    $(".my-grid-error").html("");
                    $("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="7"><center>No data found in the server</center></th></tr></tbody>');
                    $("#my-grid_processing").css("display", "none");
                }
            }
        });
    }

    $(document).on('click', '.edit_modal', function() {
        var id = $(this).data('id');

        $.ajax({
            type: 'post',
            url: siteurl + active_controller + 'edit_modal',
            data: {
                'id': id
            },
            cache: false,
            success: function(result) {
                $('#modal-body').html(result);
                $('#MyModal').modal('show');
            },
            error: function(result) {
                swal({
                    type: 'error',
                    title: 'Error !',
                    text: 'Please try again later !'
                });
            }
        });
    });

    $(document).on('click', '.save_edit_paket', function() {
        swal({
            type: 'warning',
            title: 'Are you sure ?',
            text: 'Data will be edited !',
            showCancelButton: true
        }, function(next) {
            if (next) {
                var formData = $('#FormEdit').serialize()

                $.ajax({
                    type: 'POST',
                    url: siteurl + active_controller + 'edit_paket',
                    data: formData,
                    cache: false,
                    dataType: 'JSON',
                    success: function(result) {
                        if (result.status == 1) {
                            swal({
                                type: 'success',
                                title: 'Success !',
                                text: result.msg
                            }, function(after) {
                                $('#MyModal').modal('hide')
                                datatables()
                            });
                        } else {
                            swal({
                                type: 'warning',
                                title: 'Failed !',
                                text: result.msg
                            });
                        }
                    },
                    error: function(result) {
                        swal({
                            type: 'error',
                            title: 'Error !',
                            text: 'Please try again later !'
                        });
                    }
                })
            }
        });
    });

    $(document).on('click', '.delete_paket', function() {
        var id = $(this).data('id');

        swal({
            type: 'warning',
            title: 'Are you sure?',
            text: 'Data will be deleted !',
            showCancelButton: true
        }, function(next) {
            if (next) {
                $.ajax({
                    type: 'post',
                    url: siteurl + active_controller + 'delete_paket',
                    data: {
                        'id': id
                    },
                    cache: false,
                    dataType: 'JSON',
                    success: function(result) {
                        if (result.status == 1) {
                            swal({
                                type: 'success',
                                title: 'Success !',
                                text: result.msg
                            }, function(after) {
                                $('#MyModal').modal('hide')
                                datatables()
                            });
                        } else {
                            swal({
                                type: 'warning',
                                title: 'Failed !',
                                text: result.msg
                            });
                        }
                    },
                    error: function(result) {
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
</script>