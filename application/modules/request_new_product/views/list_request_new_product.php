<?php
$ENABLE_ADD     = has_permission('Request_Product2.Add');
$ENABLE_MANAGE  = has_permission('Request_Product2.Manage');
$ENABLE_VIEW    = has_permission('Request_Product2.View');
$ENABLE_DELETE  = has_permission('Request_Product2.Delete');
?>
<style type="text/css">
    thead input {
        width: 100%;
    }
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box">
    <div class="box-header">
        <span class="pull-right">

        </span>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <table id="example1" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th class="text-center">#</th>
                    <th class="text-center">IPP</th>
                    <th class="text-center">Customer</th>
                    <th class="text-center">Project</th>
                    <th class="text-center">Type Product</th>
                    <th class="text-center">Product Name</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Option</th>
                </tr>
            </thead>

            <tbody></tbody>
        </table>
    </div>
    <!-- /.box-body -->
</div>


<div class="modal modal-default fade" id="dialog-popup" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" style='width:70%;'>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel"><span class="fa fa-users"></span>&nbsp;Detail Data</h4>
            </div>
            <div class="modal-body" id="ModalView">
                ...
            </div>
        </div>
    </div>

    <!-- DataTables -->
    <script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
    <script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

    <!-- page script -->
    <script type="text/javascript">
        // DELETE DATA
        $(document).on('click', '.delete', function(e) {
            e.preventDefault()
            var id = $(this).data('id');
            // alert(id);
            swal({
                    title: "Anda Yakin?",
                    text: "Data BOM akan di hapus !",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-info",
                    confirmButtonText: "Ya, Hapus!",
                    cancelButtonText: "Batal",
                    closeOnConfirm: false
                },
                function() {
                    $.ajax({
                        type: 'POST',
                        url: base_url + active_controller + '/hapus',
                        dataType: "json",
                        data: {
                            'id': id
                        },
                        success: function(result) {
                            if (result.status == '1') {
                                swal({
                                        title: "Sukses",
                                        text: "Data berhasil dihapus.",
                                        type: "success"
                                    },
                                    function() {
                                        window.location.reload(true);
                                    })
                            } else {
                                swal({
                                    title: "Error",
                                    text: "Data error. Gagal hapus data",
                                    type: "error"
                                })

                            }
                        },
                        error: function() {
                            swal({
                                title: "Error",
                                text: "Data error. Gagal request Ajax",
                                type: "error"
                            })
                        }
                    })
                });

        });

        $(function() {
            DataTables();
        });


        function DataTables() {
            var dataTable = $('#example1').DataTable({
                // "scrollX": true,
                "scrollY": "500",
                "scrollCollapse": true,
                "processing": true,
                "serverSide": true,
                "stateSave": true,
                "bAutoWidth": true,
                "destroy": true,
                "responsive": true,
                "aaSorting": [
                    [1, "asc"]
                ],
                "columnDefs": [{
                    "targets": 'no-sort',
                    "orderable": false,
                }],
                "sPaginationType": "simple_numbers",
                "iDisplayLength": 10,
                "aLengthMenu": [
                    [10, 20, 50, 100, 150],
                    [10, 20, 50, 100, 150]
                ],
                "ajax": {
                    url: base_url + active_controller + '/get_json_ipp',
                    type: "post",
                    data: function(d) {
                        // d.kode_partner = $('#kode_partner').val()
                    },
                    cache: false,
                    error: function() {
                        $(".my-grid-error").html("");
                        $("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                        $("#my-grid_processing").css("display", "none");
                    }
                }
            });
        }
    </script>