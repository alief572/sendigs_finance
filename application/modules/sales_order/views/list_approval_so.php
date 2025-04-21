<?php
$ENABLE_ADD     = has_permission('Sales_Order_New.Add');
$ENABLE_MANAGE  = has_permission('Sales_Order_New.Manage');
$ENABLE_VIEW    = has_permission('Sales_Order_New.View');
$ENABLE_DELETE  = has_permission('Sales_Order_New.Delete');
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
                    <th width="5">No</th>
                    <th>Date</th>
                    <th>SO No</th>
                    <th>Customer</th>
                    <th>Quotation No.</th>
                    <th>Project</th>
                    <th>Update By</th>
                    <th>Rev</th>
                    <th>Status</th>
                    <th width="13%">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $x = 1;
                foreach ($results['list_data'] as $data) {
                    $check_so = $this->db->get_where('tr_sales_order', ['no_penawaran' => $data->no_penawaran])->result();

                    $print = '&nbsp;<a href="sales_order/print_sales_order/' . $data->no_so . '" class="btn btn-sm bg-purple" data-no_so="' . $data->no_so . '">Print SO</a>';
                    if (count($check_so) < 1) {
                        $print = '';
                    }

                    $edit    = "&nbsp;<a href='" . site_url($this->uri->segment(1)) . '/deal_so/' . $data->no_penawaran . "' class='btn btn-sm btn-primary' title='Edit Data' data-role='qtip'>Deal SO</a>";
                    if (!$ENABLE_MANAGE) {
                        $edit = '';
                    }

                    $check_so = $this->db->get_where('tr_sales_order', ['no_penawaran' => $data->no_penawaran])->row();

                    $ajukan = '';
                    if ($data->status == '2' && count($check_so) > 0 && $ENABLE_MANAGE) {
                        $ajukan = '<button type="button" class="btn btn-sm btn-success ajukan" data-id_so="' . $data->no_so . '">Ajukan</button>';
                    }

                    $view = "";
                    if (count($check_so) > 0) {
                        $view = "<button type='button' class='btn btn-sm btn-warning detail' title='Detail' data-no_so='" . $data->no_so . "'>View</button>";
                    }

                    $approval = '';
                    if ($data->req_app > 0 && $ENABLE_MANAGE) {
                        $approval = '<button type="button" class="btn btn-sm btn-primary approval" data-id_so="' . $data->no_so . '">Approval</button>';
                    }

                    $buttons = $view . ' ' . $edit . ' ' . $print . ' ' . $ajukan . ' ' . $approval;
                    if ($data->req_app == '1') {
                        $buttons = $view . ' ' . $print;
                        if ($data->approve < 1 && $this->uri->segment(2) == 'approval') {
                            $buttons .= ' ' . $approval;
                        }
                    }

                    if ($data->approve == '1') {
                        $status = '<div class="badge bg-green">SO</div>';
                    } else {
                        if ($data->req_app == '1' && $data->approve == '0') {
                            $status = '<div class="badge bg-blue">Waiting Approval SO</div>';
                        } else {
                            $status = '<div class="badge bg-yellow">Waiting SO</div>';
                        }
                    }

                    echo "
                    <tr>
                        <td align='center'>" . $x . "</td>
                        <td align='center'>" . date('d F Y', strtotime($data->tgl_penawaran)) . "</td>
                        <td align='left'>" . strtoupper(strtolower($data->no_so)) . "</td>
                        <td align='left'>" . strtoupper(strtolower($data->nm_customer)) . "</td>
                        <td align='left'>" . strtoupper(strtolower($data->no_penawaran)) . "</td>
                        <td align='left'>" . $data->project . "</td>
                        <td align='center'>" . $data->update_by . "</td>
                        <td align='left'>" . $data->no_revisi . "</td>
                        <td align='left'>" . $status . "</td>
                        <td align='center'>" . $buttons . "</td>
                    </tr>";

                    $x++;
                }
                ?>
            </tbody>
        </table>
    </div>
    <!-- /.box-body -->
</div>


<div class="modal modal-default fade" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" style='width:50%; '>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel"><span class="fa fa-users"></span>&nbsp;Detail Data</h4>
            </div>
            <div class="modal-body" id="ModalView">
                ...
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>

<div class="modal modal-default fade" id="ModalPrintQuote" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id='head_title'>Print SO</h4>
            </div>
            <form action="" id="print_so_form">
                <div class="modal-body" id="viewX">
                    <input type="hidden" name="no_so" class="no_so">
                    <div class="form-group">
                        <label for="">Show PPN / Hide PPN</label>
                        <select name="show_hide_ppn" id="" class="form-control form-control-sm show_hide_ppn">
                            <option value="1">Show PPN</option>
                            <option value="0">Hide PPN</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="">Show Disc / Hide Disc</label>
                        <select name="show_hide_disc" id="" class="form-control form-control-sm show_hide_disc">
                            <option value="1">Show Disc</option>
                            <option value="0">Hide Disc</option>
                        </select>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<!-- page script -->
<script type="text/javascript">
    $(document).on('click', '.detail', function() {
        var no_so = $(this).data('no_so');
        // alert(id);
        $("#head_title").html("<i class='fa fa-list-alt'></i><b>Detail Cycletime</b>");
        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + '/detail_sales_order/' + no_so,
            data: {
                'no_so': no_so
            },
            success: function(data) {
                $("#dialog-popup").modal();
                $("#ModalView").html(data);

                $('.modal-footer').html('');
            }
        });
    });

    $(document).on('click', '.approval', function() {
        var no_so = $(this).data('id_so');

        $('#myModalLabel').html('<span class="fa fa-check"></span>&nbsp;Approval SO');
        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + '/approval_modal/' + no_so,
            data: {
                'no_so': no_so
            },
            cache: false,
            success: function(data) {
                $("#dialog-popup").modal();
                $("#ModalView").html(data);

                $('.modal-footer').html('<button type="button" class="btn btn-sm btn-success save_approval"><i class="fa fa-save"></i> Update</button>');
            }
        });
    });

    $(document).on('click', '.ajukan', function() {
        var id_so = $(this).data('id_so');

        swal({
                title: "Anda yakin?",
                text: "Apa anda yakin ingin Request Approval SO ini ?",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-danger",
                confirmButtonText: "Ya",
                cancelButtonText: "Tidak",
                closeOnConfirm: true,
                closeOnCancel: true
            },
            function(isConfirm) {
                if (isConfirm) {
                    $.ajax({
                        type: 'post',
                        url: siteurl + active_controller + '/update_status',
                        data: {
                            'id_so': id_so
                        },
                        cache: false,
                        dataType: 'json',
                        success: function(result) {
                            if (result.status == '1') {
                                swal({
                                    title: "Request Approval Berhasil !",
                                    text: result.pesan,
                                    type: "success",
                                    timer: 5000,
                                    showCancelButton: false,
                                    showConfirmButton: false,
                                    allowOutsideClick: false
                                });

                                location.reload();
                            } else {
                                swal({
                                    title: "Request Approval Gagal !",
                                    text: result.pesan,
                                    type: "error",
                                    timer: 5000,
                                    showCancelButton: false,
                                    showConfirmButton: false,
                                    allowOutsideClick: false
                                });
                            }
                        }
                    });
                }
            });
    });

    $(document).on('click', '.save_approval', function() {
        var no_so = $('.no_so').val();
        var action_type = $('.action_type').val();
        var keterangan_approve_reject = $('.keterangan_approve_reject').val();

        if (action_type == '') {
            swal({
                title: "Peringatan",
                text: "Mohon pilih status SO nya Approve / Reject terlebih dahulu !",
                type: "warning",
                timer: 5000,
                showCancelButton: false,
                showConfirmButton: false,
                allowOutsideClick: false
            });
        } else {
            if (action_type == '1') {
                var type_ac = 'Approve';
            } else {
                var type_ac = 'Reject';
            }
            swal({
                    title: "Anda yakin?",
                    text: "Apa anda yakin ingin " + type_ac + " SO ini ?",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-danger",
                    confirmButtonText: "Ya",
                    cancelButtonText: "Tidak",
                    closeOnConfirm: true,
                    closeOnCancel: true
                },
                function(isConfirm) {
                    if (isConfirm) {
                        $.ajax({
                            type: 'post',
                            url: siteurl + active_controller + '/save_approval',
                            data: {
                                'no_so': no_so,
                                'action_type': action_type,
                                'keterangan_approve_reject': keterangan_approve_reject
                            },
                            cache: false,
                            dataType: 'json',
                            success: function(result) {
                                if (result.status == '1') {
                                    swal({
                                        title: type_ac + " Berhasil",
                                        text: result.pesan,
                                        type: "success",
                                        timer: 5000,
                                        showCancelButton: false,
                                        showConfirmButton: false,
                                        allowOutsideClick: false
                                    });

                                    location.reload();
                                } else {
                                    swal({
                                        title: type_ac + " Gagal",
                                        text: result.pesan,
                                        type: "error",
                                        timer: 5000,
                                        showCancelButton: false,
                                        showConfirmButton: false,
                                        allowOutsideClick: false
                                    });
                                }
                            }
                        });
                    }
                });
        }
    });

    $(document).on('click', '.print_so', function() {
        var no_so = $(this).data('no_so');

        $('.no_so').val(no_so);
        $('#ModalPrintQuote').modal('show');
    });

    $(document).on('submit', '#print_so_form', function(e) {
        e.preventDefault();
        var no_so = $('.no_so').val();

        var show_hide_ppn = $('.show_hide_ppn').val();
        var show_hide_disc = $('.show_hide_disc').val();
        if (show_hide_ppn > 0) {
            if (show_hide_disc == '1') {
                window.open(siteurl + active_controller + 'print_sales_order/' + no_so + '/' + show_hide_disc);
            } else {
                window.open(siteurl + active_controller + 'print_sales_order/' + no_so);
            }
        } else {
            if (show_hide_ppn !== null) {
                if (show_hide_disc == '1') {
                    window.open(siteurl + active_controller + 'print_sales_order_non_ppn/' + no_so + '/' + show_hide_disc);
                } else {
                    window.open(siteurl + active_controller + 'print_sales_order_non_ppn/' + no_so);
                }
            } else {
                swal({
                    title: "Error !",
                    text: 'Please select show / hide PPn First !!',
                    type: "warning"
                });
            }
        }
    });



    $(function() {
        DataTables();
    });


    // function DataTables() {
    //     var dataTable = $('#example1').DataTable({
    //         // "scrollX": true,
    //         "scrollY": "500",
    //         "scrollCollapse": true,
    //         "processing": true,
    //         "serverSide": true,
    //         "stateSave": true,
    //         "bAutoWidth": true,
    //         "destroy": true,
    //         "responsive": true,
    //         "oLanguage": {
    //             "sSearch": "<b>Live Search : </b>",
    //             "sLengthMenu": "_MENU_ &nbsp;&nbsp;<b>Records Per Page</b>&nbsp;&nbsp;",
    //             "sInfo": "Showing _START_ to _END_ of _TOTAL_ entries",
    //             "sInfoFiltered": "(filtered from _MAX_ total entries)",
    //             "sZeroRecords": "No matching records found",
    //             "sEmptyTable": "No data available in table",
    //             "sLoadingRecords": "Please wait - loading...",
    //             "oPaginate": {
    //                 "sPrevious": "Prev",
    //                 "sNext": "Next"
    //             }
    //         },
    //         "aaSorting": [
    //             [1, "desc"]
    //         ],
    //         "columnDefs": [{
    //             "targets": 'no-sort',
    //             "orderable": false,
    //         }],
    //         "sPaginationType": "simple_numbers",
    //         "iDisplayLength": 10,
    //         "aLengthMenu": [
    //             [10, 20, 50, 100, 150],
    //             [10, 20, 50, 100, 150]
    //         ],
    //         "ajax": {
    //             url: siteurl + active_controller + 'data_side_approval_sales_order',
    //             type: "post",
    //             data: function(d) {
    //                 // d.kode_partner = $('#kode_partner').val()
    //             },
    //             cache: false,
    //             error: function() {
    //                 $(".my-grid-error").html("");
    //                 $("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
    //                 $("#my-grid_processing").css("display", "none");
    //             }
    //         }
    //     });
    // }

    function DataTables() {
        var dataTable = $('#example1').DataTable();
    }
</script>