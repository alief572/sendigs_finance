<?php
$ENABLE_ADD     = has_permission('Approval_Quotation_2.Add');
$ENABLE_MANAGE  = has_permission('Approval_Quotation_2.Manage');
$ENABLE_VIEW    = has_permission('Approval_Quotation_2.View');
$ENABLE_DELETE  = has_permission('Approval_Quotation_2.Delete');

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
    <!-- /.box-header -->
    <div class="box-body">
        <table id="example1" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th class="text-center">No</th>
                    <th class="text-center">Date</th>
                    <th class="text-center">Customer</th>
                    <th class="text-center">Quotation No.</th>
                    <th class="text-center">Project</th>
                    <th class="text-center">Update By</th>
                    <th class="text-center">Rev</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($results)) {
                } else {

                    $numb = 0;
                    foreach ($results as $record) {
                        $numb++;


                        if ($record->status == 0) {
                            $Status = "<span class='badge bg-yellow'>Draft</span>";
                        } elseif ($record->status == 1) {
                            $num_approval = 'Supervisor';
                            if ($record->req_app2 == '1' && $record->app_1 == '1') {
                                $num_approval = 'Manager';
                            }
                            if ($record->req_app3 == '1' && $record->app_2 == '1') {
                                $num_approval = 'Cost Control';
                            }

                            $Status = "<span class='badge bg-blue'>Waiting Approval " . $num_approval . "</span>";
                        } elseif ($record->status == 2) {
                            $Status = "<span class='badge bg-green'>Waiting SO</span>";
                        } elseif ($record->status == 3) {
                            $Status = "<span class='badge bg-purple'>SO Approved</span>";
                        } elseif ($record->status == 4) {
                            $Status = "<span class='badge bg-red'>Loss</span>";
                        }
                ?>

                        <?php if ($record->status <> '4' && $record->req_app1 == '1') {
                            $get_created_user = $this->db->get_where('users', ['id_user' => $record->created_by])->row();
                            $get_modified_user = $this->db->get_where('users', ['id_user' => $record->modified_by])->row();
                        ?>
                            <tr>
                                <td class="text-center"><?= $numb; ?></td>
                                <td class="text-center"><?= date('d F Y', strtotime($record->tgl_penawaran)) ?></td>
                                <td class="text-center"><?= $record->nm_customer ?></td>
                                <td class="text-center"><?= $record->no_penawaran ?></td>
                                <td class="text-center"><?= $record->project ?></td>
                                <?= ($record->modified_by !== '' && $record->modified_by !== null) ? '<td class="text-center">' . $get_modified_user->nm_lengkap . '</td>' : '<td class="text-center">' . $get_created_user->nm_lengkap . '</td>' ?>
                                <td class="text-center"><?= $record->no_revisi ?></td>
                                <td class="text-center"><?= $Status ?></td>

                                <?php
                                $btn_edit = '<a href="quotation/modal_detail_invoice/' . $record->no_penawaran . '" class="btn btn-sm btn-success">Edit</a>';

                                $btn_view = '<a href="' . base_url() . 'Approval_quotation/view_quotation2/' . $record->no_penawaran . '" class="btn btn-sm btn-info">View</a>';

                                $btn_ajukan = '<a href="javascript:void(0);" class="btn btn-sm btn-success ajukan" data-id="' . $record->no_penawaran . '" data-status="' . $record->status . '">Ajukan</a>';

                                $btn_print = '<a href="javascript:void(0);" class="btn btn-sm bg-purple">Print</a>';

                                $btn_loss = '<a href="javascript:void(0);" class="btn btn-sm btn-danger">Loss</a>';

                                $btn_approval = '<a href="' . base_url() . 'Approval_quotation/approval_quotation_2/' . $record->no_penawaran . '" class="btn btn-sm btn-primary">Approval</a>';

                                $buttons = $btn_view;
                                if ($record->app_2 == '1' || $record->app_1 !== '1') {
                                    $buttons = $btn_view;
                                } else {
                                    if ($ENABLE_MANAGE) {
                                        $buttons = $btn_approval . ' ' . $btn_view;
                                    }
                                }
                                ?>
                                <td class="text-center">
                                    <?= $buttons ?>
                                </td>

                            </tr>
                <?php      }
                    }
                }  ?>
            </tbody>
        </table>
    </div>
    <!-- /.box-body -->
</div>

<!-- awal untuk modal dialog -->
<!-- Modal -->
<div class="modal modal-primary" id="dialog-rekap" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel"><span class="fa fa-file-pdf-o"></span>&nbsp;Rekap Data Customer</h4>
            </div>
            <div class="modal-body" id="MyModalBody">
                ...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <span class="glyphicon glyphicon-remove"></span> Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal modal-default fade" id="dialog-popup" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel"><span class="fa fa-users"></span>&nbsp;Data Penawaran</h4>
            </div>
            <div class="modal-body" id="ModalView">
                ...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">
                    <span class="glyphicon glyphicon-remove"></span> Close</button>
            </div>
        </div>
    </div>
</div>

<!-- modal -->
<div class="modal modal-default fade" id="ModalViewX" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id='head_title'>Closing Penawaran</h4>
            </div>
            <div class="modal-body" id="viewX">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal" id='close_penawaran'>Save</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<!-- page script -->
<script type="text/javascript">
    $(document).on('click', '.edit', function(e) {
        var id = $(this).data('no_penawaran');
        $("#head_title").html("<i class='fa fa-list-alt'></i><b>Edit Inventory</b>");
        $.ajax({
            type: 'POST',
            url: siteurl + 'wt_penawaran/editPenawaran/' + id,
            success: function(data) {
                $("#dialog-popup").modal();
                $("#ModalView").html(data);

            }
        })
    });

    $(document).on('click', '.cetak', function(e) {
        var id = $(this).data('no_penawaran');
        $("#head_title").html("<i class='fa fa-list-alt'></i><b>Edit Inventory</b>");
        $.ajax({
            type: 'POST',
            url: siteurl + 'xtes/cetak' + id,
            success: function(data) {

            }
        })
    });

    $(document).on('click', '.view', function() {
        var id = $(this).data('no_penawaran');
        // alert(id);
        $("#head_title").html("<i class='fa fa-list-alt'></i><b>Detail Inventory</b>");
        $.ajax({
            type: 'POST',
            url: siteurl + 'penawaran/ViewHeader/' + id,
            data: {
                'id': id
            },
            success: function(data) {
                $("#dialog-popup").modal();
                $("#ModalView").html(data);

            }
        })
    });



    // CLOSE PENAWARAN
    $(document).on('click', '.close_penawaran', function(e) {
        e.preventDefault();
        var id = $(this).data('no_penawaran');

        $("#head_title").html("Closing Penawaran");
        $.ajax({
            type: 'POST',
            url: base_url + active_controller + '/modal_closing_penawaran/' + id,
            success: function(data) {
                $("#ModalViewX").modal();
                $("#viewX").html(data);

            },
            error: function() {
                swal({
                    title: "Error Message !",
                    text: 'Connection Timed Out ...',
                    type: "warning",
                    timer: 5000,
                    showCancelButton: false,
                    showConfirmButton: false,
                    allowOutsideClick: false
                });
            }
        });
    });

    $(document).on('click', '.ajukan', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');

        msg = 'Anda yakin ingin update penawaran ini ke Waiting Approval ?';


        swal({
                title: "Peringatan !",
                text: msg,
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Update!",
                cancelButtonText: "Batal!",
                closeOnConfirm: false,
                closeOnCancel: true
            },
            function(isConfirm) {
                if (isConfirm) {
                    $.ajax({
                        url: siteurl + active_controller + "update_status",
                        dataType: "json",
                        type: 'POST',
                        data: {
                            'id': id,
                            'status': status
                        },
                        success: function(data) {
                            var updated_status = 'Waiting Approval';
                            if (data.updated_status == '2') {
                                updated_status = 'Waiting SO';
                            }
                            if (data.status == 1) {
                                swal({
                                    title: "Save Success!",
                                    text: 'Selamat, status penawaran telah terupdate menjadi ' + updated_status,
                                    type: "success",
                                    timer: 15000,
                                    showCancelButton: false,
                                    showConfirmButton: false,
                                    allowOutsideClick: true
                                });
                                location.reload();
                            } else {

                                if (data.status == 2) {
                                    swal({
                                        title: "Save Failed!",
                                        text: data.pesan,
                                        type: "warning",
                                        timer: 10000,
                                        showCancelButton: false,
                                        showConfirmButton: false,
                                        allowOutsideClick: false
                                    });
                                } else {
                                    swal({
                                        title: "Save Failed!",
                                        text: data.pesan,
                                        type: "warning",
                                        timer: 10000,
                                        showCancelButton: false,
                                        showConfirmButton: false,
                                        allowOutsideClick: false
                                    });
                                }

                            }
                        },
                        error: function() {
                            swal({
                                title: "Gagal!",
                                text: "Batal Proses, Data bisa diproses nanti",
                                type: "error",
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                    });
                }
            });
    });

    function add_inv() {
        window.location.href = base_url + active_controller + 'modal_detail_invoice';
    }

    //Delete

    function PreviewPdf(id) {
        param = id;
        tujuan = 'customer/print_request/' + param;

        $(".modal-body").html('<iframe src="' + tujuan + '" frameborder="no" width="570" height="400"></iframe>');
    }

    function PreviewRekap() {
        tujuan = 'customer/rekap_pdf';
        $(".modal-body").html('<iframe src="' + tujuan + '" frameborder="no" width="100%" height="400"></iframe>');
    }
</script>