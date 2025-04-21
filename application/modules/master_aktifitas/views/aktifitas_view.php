<!-- Main content-->
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.7/css/dataTables.dataTables.min.css">
<section class="content">
    <div class="container-fluid">

        <div class="row">
            <div class="col-md-12">
                <div class="tabs-container">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="<?php echo site_url('master_aktifitas'); ?>">
                                Data Aktifitas</a>
                        </li>
                        <li><a href="<?php echo site_url('master_aktifitas/aktifitas_new'); ?>">
                                Tambah Aktifitas</a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div id="tab-1" class="tab-pane active">
                            <div class="panel panel-filled">
                                <div class="panel-body">
                                    <div class="table-responsive">
                                        <table id="my-grid" class="table table-striped table-bordered table-hover table-condensed" width="100%">
                                            <thead>
                                                <tr>
                                                    <th class="column-hide">No.</th>
                                                    <th class="no-sort">ID Aktifitas</th>
                                                    <th>Input Date</th>
                                                    <th>Nama Aktifitas</th>
                                                    <th class="no-sort">Harga</th>
                                                    <th>Bobot</th>
                                                    <th>Mandays</th>
                                                    <th class="no-sort">Point</th>
                                                    <th class="no-sort" width="">Option</th>
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
</section>
<script src="https://cdn.datatables.net/2.1.7/js/dataTables.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        datatable();

        function datatable() {
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
                [2, "desc"]
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
                url: siteurl + active_controller + 'display_aktifitas_json',
                type: "post",
                error: function() {
                    $(".my-grid-error").html("");
                    $("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="9"><center>No data found in the server</center></th></tr></tbody>');
                    $("#my-grid_processing").css("display", "none");
                }
            }
        });
        }

        $(document).on('click', '.delete_aktifitas', function() {
            var id = $(this).data('id');
    
            swal({
                type: 'warning',
                title: 'Are you sure?',
                text: 'This data will be deleted !',
                showCancelButton: true
            }, function(submit) {
                if (submit) {
                    $.ajax({
                        url: siteurl + active_controller + 'delete_aktifitas',
                        type: 'POST',
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
                                }, function(data) {
                                    datatable();
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
    });

</script>