<?php
$ENABLE_ADD     = has_permission('Incoming_Material.Add');
$ENABLE_MANAGE  = has_permission('Incoming_Material.Manage');
$ENABLE_VIEW    = has_permission('Incoming_Material.View');
$ENABLE_DELETE  = has_permission('Incoming_Material.Delete');

?>
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.3/css/dataTables.dataTables.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data">
    <div class="box box-primary">
        <div class="box-header">
            <h3 class="box-title"><?php echo $title; ?></h3>
            <div class="box-tool pull-right">
            </div>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
            <div class="box box-success">
                <div class="box-body">
                    <br>
                    <input type="hidden" id='tandax' name='tandax'>
                    <div class='in_ipp'>
                        <div class='form-group row'>
                            <label class='label-control col-sm-2'><b>Supplier</b></label>
                            <div class="col-md-4">
                                <select name="supplier" id="" class="form-control form-control-sm select2 choose_supplier">
                                    <option value="">- Select Supplier -</option>
                                    <?php
                                    foreach ($list_supplier as $item) {
                                        echo '<option value="' . $item->kode_supplier . '">' . $item->nama . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class='form-group row'>
                            <label class='label-control col-sm-2'><b>No PO</b></label>
                            <div class="col-md-4">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="text-center">NO. PO</th>
                                            <th class="text-center">No. PR</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="list_no_po"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- <div class='form-group row'>
                        <label class='label-control col-sm-2'><b>Warehouse</b></label>
                        <div class='col-sm-4'>
                            <select id='gudang_before' name='gudang_before' class='form-control input-sm' style='min-width:200px;'>
                                <option value='0'>Select Gudang</option>
                                <?php
                                foreach ($pusat as $val => $valx) {
                                    echo "<option value='" . $valx['id'] . "'>" . strtoupper($valx['nm_gudang']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div> -->
                    <!-- <div class='form-group row non-po-label'>
                        <label class='label-control col-sm-2'><b>Asal Incoming</b></label>
                        <div class='col-sm-4'>
                            <input type="text" name='asal_incoming' id='asal_incoming' class='form-control input-md' placeholder='Asal Incoming'>
                        </div>
                    </div> -->

                    <?php
                    // if ($akses_menu['create'] == '1') {
                    echo form_button(array('type' => 'button', 'class' => 'btn btn-md btn-success', 'style' => 'min-width:100px; float:right; margin: 5px 0px 5px 0px;', 'value' => 'Process', 'content' => 'Process', 'id' => 'modalDetail')) . ' ';
                    // }
                    ?>
                </div>
            </div>
            <!-- <div class="box-tool pull-right">
                <select id='no_po' name='no_po' class='form-control input-sm' style='min-width:200px;'>
                    <option value='0'>All PO Number</option>
                    <?php
                    foreach ($list_po as $val => $valx) {
                        echo "<option value='" . $valx['no_ipp'] . "'>" . strtoupper($valx['no_ipp']) . "</option>";
                    }
                    ?>
                </select>
            </div> -->
            <br><br>
            <table class="table table-bordered table-striped" id="my-grid" width='100%'>
                <thead>
                    <tr class='bg-blue'>
                        <th class="text-center">No</th>
                        <th class="text-center">No Trans</th>
                        <th class="text-center">No PR</th>
                        <th class="text-center">Supplier</th>
                        <th class="text-center">Sum Material</th>
                        <th class="text-center">Receiver</th>
                        <th class="text-center">Incoming Date</th>
                        <th class="text-center">Option</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        <!-- /.box-body -->
    </div>
    <!-- /.box -->
    <!-- modal -->
    <div class="modal fade" id="ModalView2" style='overflow-y: auto;'>
        <div class="modal-dialog" style='width:85%; '>
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="head_title2"></h4>
                </div>
                <div class="modal-body" id="view2">
                </div>
                <div class="modal-footer">
                    <!--<button type="button" class="btn btn-primary">Save</button>-->
                    <button type="button" class="btn btn-default " data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- modal -->
</form>



<script src="https://cdn.datatables.net/2.0.3/js/dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script>
    $(document).ready(function() {
        $('.maskM').autoNumeric('init');
        $('.non-po-label').hide();

        $('.select2').select2();

        var no_po = $('#no_po').val();
        var gudang = $('#gudang').val();
        DataTables(no_po, gudang);

        $(document).on('change', '#gudang, #no_po', function(e) {
            e.preventDefault();
            var no_po = $('#no_po').val();
            var gudang = $('#gudang').val();
            DataTables(no_po, gudang);
        });

        // $(document).on('change', '#no_ipp', function(e) {
        //     e.preventDefault();
        //     var no_ipp = $('#no_ipp').val();
        //     let result = no_ipp.substring(0, 1);
        //     if (result == 'P') {
        //         $('.non-po-label').hide();
        //     }
        //     if (result == 'N') {
        //         $('.non-po-label').show();
        //     }
        // });
    });

    $(document).on('click', '.detailAjust', function(e) {
        e.preventDefault();

        $("#head_title2").html("<b>PERMINTAAN PENGECEKAN MATERIAL</b>");
        $.ajax({
            type: 'POST',
            url: base_url + active_controller + 'modal_detail_adjustment/' + $(this).data('kode_trans'),
            success: function(data) {
                $("#ModalView2").modal();
                $("#view2").html(data);

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

    $(document).on('click', '#modalDetail', function(e) {
        e.preventDefault();
        var gudang_before = $('#gudang_before').val();
        // var no_ipp = $('#no_ipp').val();

        var arrNoPO = [];
        $('.check_po').each(function() {
            if ($(this).is(':checked')) {
                arrNoPO.push($(this).val());
            }
        });

        // if (no_ipp == '0') {
        //     swal({
        //         title: "Error Message!",
        //         text: 'PO Number Not Select, please input first ...',
        //         type: "warning"
        //     });
        //     $('#modalDetail').prop('disabled', false);
        //     return false;
        // }

        if (gudang_before == '0') {
            swal({
                title: "Error Message!",
                text: 'Warehouse Not Select, please input first ...',
                type: "warning"
            });
            $('#modalDetail').prop('disabled', false);
            return false;
        }

        if(arrNoPO.length < 1) {
            swal({
                title: 'Warning',
                text: 'Please check at least 1 PO number !',
                type: 'warning'
            });
            return false;
        }


        // var no_ipp = $('#no_ipp').val();
        var no_ros = $('#no_ros').val();
        var gudang_before = $('#gudang_before').val();
        var asal_incoming = $('#asal_incoming').val();
        // let pembeda = no_ipp.substring(0, 1);
        var kode_trans = $(this).data('kode_trans');

        $("#head_title2").html("<b>INCOMING MATERIAL</b>");
        $.ajax({
            url: base_url + active_controller + 'modal_incoming_material',
            type: "POST",
            data: {
                "kode_trans": kode_trans,
                "list_no_po": arrNoPO,
            },
            cache: false,
            success: function(data) {
                $("#ModalView2").modal();
                $("#view2").html(data);
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

    $(document).on('click', '#processAjust', function() {
        var gudang_before = $('#gudang_before').val();
        var no_ipp = $('#no_ipp').val();
        var gudang_after = $('#gudang_after').val();

        if (no_ipp == '0') {
            swal({
                title: "Error Message!",
                text: 'IPP Not Select, please input first ...',
                type: "warning"
            });
            $('#processAjust').prop('disabled', false);
            return false;
        }

        if (gudang_before == '0') {
            swal({
                title: "Error Message!",
                text: 'Origin Warehouse Not Select, please input first ...',
                type: "warning"
            });
            $('#processAjust').prop('disabled', false);
            return false;
        }

        if (gudang_after == '0') {
            swal({
                title: "Error Message!",
                text: 'Destination Warehouse Not Select, please input first ...',
                type: "warning"
            });
            $('#processAjust').prop('disabled', false);
            return false;
        }

        swal({
                title: "Are you sure?",
                text: "You will not be able to process again this data!",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-danger",
                confirmButtonText: "Yes, Process it!",
                cancelButtonText: "No, cancel process!",
                closeOnConfirm: false,
                closeOnCancel: false
            },
            function(isConfirm) {
                if (isConfirm) {

                    var formData = new FormData($('#form_proses_bro')[0]);
                    $.ajax({
                        url: base_url + active_controller + '/process_adjustment',
                        type: "POST",
                        data: formData,
                        cache: false,
                        dataType: 'json',
                        processData: false,
                        contentType: false,
                        success: function(data) {
                            if (data.status == 1) {
                                swal({
                                    title: "Save Success!",
                                    text: data.pesan,
                                    type: "success",
                                    timer: 7000,
                                    showCancelButton: false,
                                    showConfirmButton: false,
                                    allowOutsideClick: false
                                });
                                window.location.href = base_url + active_controller + '/material_adjustment';
                            } else if (data.status == 0) {
                                swal({
                                    title: "Save Failed!",
                                    text: data.pesan,
                                    type: "warning",
                                    timer: 7000,
                                    showCancelButton: false,
                                    showConfirmButton: false,
                                    allowOutsideClick: false
                                });
                            }
                        },
                        error: function() {
                            swal({
                                title: "Error Message !",
                                text: 'An Error Occured During Process. Please try again..',
                                type: "warning",
                                timer: 7000,
                                showCancelButton: false,
                                showConfirmButton: false,
                                allowOutsideClick: false
                            });
                        }
                    });
                } else {
                    swal("Cancelled", "Data can be process again :)", "error");
                    return false;
                }
            });
    });

    $(document).on('click', '#saveINMaterial', function() {

        swal({
                title: "Are you sure?",
                text: "You will not be able to process again this data!",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-danger",
                confirmButtonText: "Yes, Process it!",
                cancelButtonText: "No, cancel process!",
                closeOnConfirm: false,
                closeOnCancel: false
            },
            function(isConfirm) {
                if (isConfirm) {

                    var formData = new FormData($('#form_adjustment')[0]);
                    $.ajax({
                        url: base_url + active_controller + '/process_in_material',
                        type: "POST",
                        data: formData,
                        cache: false,
                        dataType: 'json',
                        processData: false,
                        contentType: false,
                        success: function(data) {
                            if (data.status == 1) {
                                swal({
                                    title: "Save Success!",
                                    text: data.pesan,
                                    type: "success",
                                    timer: 7000,
                                    showCancelButton: false,
                                    showConfirmButton: false,
                                    allowOutsideClick: false
                                });
                                window.location.href = base_url + active_controller;
                            } else if (data.status == 0) {
                                swal({
                                    title: "Save Failed!",
                                    text: data.pesan,
                                    type: "warning",
                                    timer: 7000,
                                    showCancelButton: false,
                                    showConfirmButton: false,
                                    allowOutsideClick: false
                                });
                            }
                        },
                        error: function() {
                            swal({
                                title: "Error Message !",
                                text: 'An Error Occured During Process. Please try again..',
                                type: "warning",
                                timer: 7000,
                                showCancelButton: false,
                                showConfirmButton: false,
                                allowOutsideClick: false
                            });
                        }
                    });
                } else {
                    swal("Cancelled", "Data can be process again :)", "error");
                    return false;
                }
            });
    });

    $(document).on('click', '#moveMat', function() {

        swal({
                title: "Are you sure?",
                text: "You will not be able to process again this data!",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-danger",
                confirmButtonText: "Yes, Process it!",
                cancelButtonText: "No, cancel process!",
                closeOnConfirm: false,
                closeOnCancel: false
            },
            function(isConfirm) {
                if (isConfirm) {

                    var formData = new FormData($('#form_move')[0]);
                    $.ajax({
                        url: base_url + active_controller + '/move_material',
                        type: "POST",
                        data: formData,
                        cache: false,
                        dataType: 'json',
                        processData: false,
                        contentType: false,
                        success: function(data) {
                            if (data.status == 1) {
                                swal({
                                    title: "Save Success!",
                                    text: data.pesan,
                                    type: "success",
                                    timer: 7000,
                                    showCancelButton: false,
                                    showConfirmButton: false,
                                    allowOutsideClick: false
                                });
                                window.location.href = base_url + active_controller + '/material_adjustment';
                            } else if (data.status == 0) {
                                swal({
                                    title: "Save Failed!",
                                    text: data.pesan,
                                    type: "warning",
                                    timer: 7000,
                                    showCancelButton: false,
                                    showConfirmButton: false,
                                    allowOutsideClick: false
                                });
                            }
                        },
                        error: function() {
                            swal({
                                title: "Error Message !",
                                text: 'An Error Occured During Process. Please try again..',
                                type: "warning",
                                timer: 7000,
                                showCancelButton: false,
                                showConfirmButton: false,
                                allowOutsideClick: false
                            });
                        }
                    });
                } else {
                    swal("Cancelled", "Data can be process again :)", "error");
                    return false;
                }
            });
    });

    $(document).on('change', '.choose_supplier', function() {
        var kode_supplier = $(this).val();

        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + 'incoming_list_po',
            data: {
                'kode_supplier': kode_supplier
            },
            cache: false,
            success: function(result) {
                $('.list_no_po').html(result);
            },
            error: function(result) {
                swal({
                    title: 'Error',
                    text: 'Please try again later !',
                    type: 'error'
                });
            }
        });
    });

    function DataTables(no_po = null, gudang = null) {
        var dataTable = $('#my-grid').DataTable({
            "processing": true,
            "serverSide": true,
            "stateSave": true,
            "autoWidth": false,
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
                url: base_url + active_controller + '/server_side_incoming_material',
                type: "post",
                data: function(d) {
                    d.no_po = no_po,
                        d.gudang = gudang
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

    function DataTables2(gudang1 = null) {
        var dataTable = $('#my-grid2').DataTable({
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
                url: base_url + active_controller + '/server_side_move_gudang',
                type: "post",
                data: function(d) {
                    d.gudang1 = gudang1
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

    // $(document).on('change', '#no_ipp', function() {
    //     var no_ipp = $('#no_ipp').val();
    //     $('#no_ros').html('');
    //     $('#spinner').show();
    //     $.ajax({
    //         url: base_url + active_controller + '/get_ros/' + no_ipp,
    //         type: "POST",
    //         cache: false,
    //         dataType: 'json',
    //         processData: false,
    //         contentType: false,
    //         success: function(data) {
    //             console.log(data);
    //             $('#no_ros').html(data.option).trigger("chosen:updated");
    //             $('#spinner').hide();
    //         },
    //         error: function() {
    //             swal({
    //                 title: "Error Message !",
    //                 text: 'An Error Occured During Process. Please try again..',
    //                 type: "warning",
    //                 timer: 7000,
    //                 showCancelButton: false,
    //                 showConfirmButton: false,
    //                 allowOutsideClick: false
    //             });
    //         }
    //     });
    // });
</script>