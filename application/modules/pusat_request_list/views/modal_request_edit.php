<style type="text/css">
    thead input {
        width: 100%;
    }
</style>
<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<form action="#" method="POST" id="form_proses" enctype="multipart/form-data">
    <div class="box">
        <div class="box-body">
            <input type="hidden" name='kode_trans' id='kode_trans' value='<?= $getData[0]['kode_trans']; ?>'>
            <input type="hidden" name='id_gudang_dari' id='id_gudang_dari' value='<?= $getData[0]['id_gudang_dari']; ?>'>
            <input type="hidden" name='id_gudang_ke' id='id_gudang_ke' value='<?= $getData[0]['id_gudang_ke']; ?>'>
            <table class="table" width="100%" border='0'>
                <thead>
                    <tr>
                        <td class="text-left" style='vertical-align:middle;' width='15%'>No Transaksi</td>
                        <td class="text-left" style='vertical-align:middle;' width='2%'>:</td>
                        <td class="text-left" style='vertical-align:middle;'><?= $getData[0]['kode_trans']; ?></td>
                    </tr>
                    <tr>
                        <td class="text-left" style='vertical-align:middle;'>Tanggal Request</td>
                        <td class="text-left" style='vertical-align:middle;'>:</td>
                        <td class="text-left" style='vertical-align:middle;'><?= tgl_indo($getData[0]['tanggal']); ?></td>
                    </tr>
                </thead>
            </table><br>
            <table class="table table-striped table-bordered table-hover table-condensed" width="100%">
                <thead>
                    <tr>
                        <th class='text-center' width='3%'>#</th>
                        <th class='text-center'>Code</th>
                        <th class='text-center'>Material Name</th>
                        <th class='text-center' width='9%'>Request (Pack)</th>
                        <th class='text-center' width='5%'>Packing</th>
                        <th class='text-center' width='6%'>Konversi</th>
                        <th class='text-center' width='9%'>Total (Unit)</th>
                        <th class='text-center' width='5%'>Unit</th>
                        <?php
                        if ($tanda == 'edit') {
                        ?>
                            <th class='text-center' width='10%'>Keterangan Req</th>
                            <th class='text-center' width='9%'>Stok (Pack)</th>
                            <th class='text-center' width='9%'>Aktual Keluar</th>
                        <?php
                        }
                        ?>
                        <th class='text-center' width='10%'>Keterangan</th>
                        <th class='text-center' width='10%'>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($getDataDetail)) {
                        $No = 0;
                        foreach ($getDataDetail as $key => $value) {
                            $No++;
                            $id_material     = $value['id_material'];
                            $nm_material    = (!empty($GET_MATERIAL[$id_material]['nama'])) ? $GET_MATERIAL[$id_material]['nama'] : 0;
                            $code_material  = (!empty($GET_MATERIAL[$id_material]['code'])) ? $GET_MATERIAL[$id_material]['code'] : 0;
                            $id_packing     = (!empty($GET_MATERIAL[$id_material]['id_packing'])) ? $GET_MATERIAL[$id_material]['id_packing'] : 0;
                            $id_unit         = (!empty($GET_MATERIAL[$id_material]['id_unit'])) ? $GET_MATERIAL[$id_material]['id_unit'] : 0;
                            $konversi       = (!empty($GET_MATERIAL[$id_material]['konversi'])) ? $GET_MATERIAL[$id_material]['konversi'] : 0;
                            $packing        = (!empty($GET_SATUAN[$id_packing]['code'])) ? $GET_SATUAN[$id_packing]['code'] : 0;
                            $unit            = (!empty($GET_SATUAN[$id_unit]['code'])) ? $GET_SATUAN[$id_unit]['code'] : 0;
                            $berat_req        = $value['qty_order'] / $konversi;
                            $stok = (!empty($GET_STOK[$id_material]['stok_packing'])) ? $GET_STOK[$id_material]['stok_packing'] : 0;

                            if ($tanda == 'edit') {
                                $this->db->select('a.*');
                                $this->db->from('tr_checked_incoming_detail a');
                                $this->db->where('a.id_material', $id_material);
                                $this->db->where('(a.qty_oke - a.qty_used) >', 0);
                                $check_lot = $this->db->get()->result();


                                echo "<tr>";
                                echo "<td align='center'>" . $No . "</td>";
                                echo "<td>" . strtoupper($code_material) . "</td>";
                                echo "<td>" . strtoupper($nm_material) . "</td>";

                                echo "<td align='center'>" . number_format($berat_req, 2) . " <input type='hidden' name='' class='qty_order qty_order_" . $id_material . "' value='" . $berat_req . "'></td>";
                                echo "<td align='center'>" . strtoupper($packing) . "</td>";
                                echo "<td align='center'>" . number_format($konversi, 2) . "</td>";
                                echo "<td align='center'>" . number_format($konversi * $berat_req, 2) . "</td>";
                                echo "<td align='center'>" . strtoupper($unit) . "</td>";
                                echo "<td align='left'>" . $value['keterangan'] . "</td>";
                                echo "<td align='center'>" . number_format($stok, 2) . "</td>";
                                echo "<td align='center'>
                                    <input type='hidden' name='detail[" . $No . "][id]' value='" . $value['id'] . "'>
                                    <input type='hidden' name='detail[" . $No . "][id_material]' value='" . $value['id_material'] . "'>
                                    <input type='text' name='detail[" . $No . "][edit_qty]' class='form-control autoNumeric2 qty_aktual qty_aktual_" . $value['id_material'] . "' readonly>
                                    <input type='hidden' name='detail[" . $No . "][id_lot]' class='id_lot id_lot_" . $value['id_material'] . "'>
                                    <input type='hidden' name='detail[" . $No . "][input_qty_app]' class='input_qty_app input_qty_app_" . $value['id_material'] . "'>
                                    <input type='hidden' name='detail[" . $No . "][stock]' value='" . $stok . "'>
                                </td>";
                                echo "<td align='center'><input type='text' name='detail[" . $No . "][keterangan]' data-no='$No' class='form-control input-md text-left'></td>";
                                echo "<td align='center'>";
                                echo "<button type='button' class='btn btn-sm btn-warning scan_qr' data-id_material='" . $value['id_material'] . "'>Scan QR</button>";
                                // if(count($check_lot) < 1){
                                echo "<button type='button' class='btn btn-sm btn-primary scan_no_qr' data-id_material='" . $value['id_material'] . "'>Input Qty</button>";
                                // }
                                echo "</td>";
                                echo '</tr>';
                            } else {
                                echo "<tr>";
                                echo "<td align='center'>" . $No . "</td>";
                                echo "<td>" . strtoupper($code_material) . "</td>";
                                echo "<td>" . strtoupper($nm_material) . "</td>";
                                echo "<td align='center'>" . number_format($berat_req, 2) . "</td>";
                                echo "<td align='center'>" . strtoupper($packing) . "</td>";
                                echo "<td align='center'>" . number_format($konversi, 2) . "</td>";
                                echo "<td align='center'>" . number_format($konversi * $berat_req, 2) . "</td>";
                                echo "<td align='center'>" . strtoupper($unit) . "</td>";
                                echo "<td align='left'>" . $value['keterangan'] . "</td>";
                                echo "</tr>";
                            }
                        }
                    ?>
                    <?php
                    } else {
                        echo "<tr>";
                        echo "<td colspan='6'><b>Tidak ada data yang ditampilkan !</b></td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
            <?php
            echo "<a href='" . base_url('pusat_request_list') . "' class='btn btn-sm btn-danger' style='min-width:100px; float:right;margin: 5px 0px 5px 5px;'>Back</a>";
            if ($tanda == 'edit') {
                echo form_button(array('type' => 'button', 'class' => 'btn btn-sm btn-primary', 'style' => 'min-width:100px; float:right; margin: 5px 0px 5px 0px;', 'value' => 'Save', 'content' => 'Confirm', 'id' => 'edit_material'));
            }
            ?>
        </div>
    </div>

    <div class="modal fade" id="ModalView3" style='overflow-y: auto;'>
        <div class="modal-dialog" style='width:90%; '>
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="head_title2"></h4>
                </div>
                <div class="modal-body" id="view3">
                    <form id="data-form" method="post" autocomplete="off"><br>
                        <input type="hidden" name="id_material" class="id_material" value="">
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title">SCAN QRCODE</h3>
                            </div>
                            <div class="box-body">
                                <div class="form-group row">
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <span class="input-group-addon" style="padding: 4px 10px 0px 10px;">
                                                <i class="fa fa-qrcode fa-3x"></i>
                                            </span>
                                            <input type="text" name="qr_code" id="qr_code" class="form-control input-lg" placeholder="QR Code">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <span id="help-text" class="text-success text-bold text-lg"></span>
                                        <div class="notif">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <h4>List Product</h4>
                        <div class="form-group row">
                            <div class="col-md-12">
                                <table class="table table-bordered table-striped" width='100%'>
                                    <thead>
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th class="text-center">Hist Date</th>
                                            <th class="text-center">Hist By</th>
                                            <th class="text-center">Qty NG</th>
                                            <th class="text-center">Qty OK</th>
                                            <th class="text-center">Konversi</th>
                                            <th class="text-center">Qty OK (Pack)</th>
                                            <th class="text-center">Keterangan Lot</th>
                                            <th class="text-center">Expired Date</th>
                                            <th class="text-center">Check List</th>
                                            <th class="text-center">Input Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody id='load-data'>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-6">
                                <input type="hidden" class="id_lot_scan">
                                <input type="hidden" name="qty_limit_order" class="qty_limit_order">
                                <button type="button" class="btn btn-primary" name="save" id="save" disabled>Save</button>
                                <button type="button" class="btn btn-danger" style='margin-left:5px;' name="back" id="back">Back</button>
                            </div>
                        </div>


                    </form>
                </div>
                <div class="modal-footer">
                    <!--<button type="button" class="btn btn-primary">Save</button>-->
                    <button type="button" class="btn btn-default " data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="ModalView4" style='overflow-y: auto;'>
        <div class="modal-dialog" style='width:90%; '>
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="head_title2"></h4>
                </div>
                <div class="modal-body" id="view3">
                    <form id="data-form" method="post" autocomplete="off"><br>
                        <input type="hidden" name="id_material" class="id_material" value="">
                        <h4>List Product</h4>
                        <div class="form-group row">
                            <div class="col-md-12">
                                <table class="table table-bordered table-striped" width='100%'>
                                    <thead>
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th class="text-center">Hist Date</th>
                                            <th class="text-center">Hist By</th>
                                            <th class="text-center">Qty NG</th>
                                            <th class="text-center">Qty OK</th>
                                            <th class="text-center">Konversi</th>
                                            <th class="text-center">Qty OK (Pack)</th>
                                            <th class="text-center">Keterangan Lot</th>
                                            <th class="text-center">Expired Date</th>
                                            <th class="text-center">Check List</th>
                                            <th class="text-center">Input Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody id='load-data1'>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-6">
                                <input type="hidden" class="id_lot_scan">
                                <input type="hidden" name="qty_limit_order" class="qty_limit_order">
                                <button type="button" class="btn btn-primary" name="save" id="save1">Save</button>
                                <button type="button" class="btn btn-danger" style='margin-left:5px;' name="back" id="back">Back</button>
                            </div>
                        </div>


                    </form>
                </div>
                <div class="modal-footer">
                    <!--<button type="button" class="btn btn-primary">Save</button>-->
                    <button type="button" class="btn btn-default " data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</form>
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTablesootstrap.min.js') ?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>

<script>
    var base_url = '<?php echo base_url(); ?>';
    var active_controller = '<?php echo ($this->uri->segment(1)); ?>';
    $(document).ready(function() {
        $('.autoNumeric2').autoNumeric('init');
        $('.tanggal').datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true,
            minDate: 0
        });
        $('.chosen-select').select2();
        // $('.autoNumeric0').autoNumeric('init', {mDec: '0', aPad: false})
        $('.datepicker').datepicker({
            dateFormat: 'dd-M-yy'
        });

        setTimeout(() => {
            $("#qr_code").focus();
            $('#help-text').html('<i>Ready to Scan QR...!!</i>')
        }, 500)

        $(document).on('focus', '#qr_code', function() {
            $('#help-text').html('<i>Ready to Scan QR...!!</i>')
        })
        $(document).on('blur', '#qr_code', function() {
            $('#help-text').html('')
        })


        //back
        $(document).on('click', '#back', function() {
            window.location.href = base_url + active_controller
        });

        $(document).on('keypress', '#qr_code', function(e) {
            const input = $(this).val();
            var id_material = $('.id_material').val();
            if (e.keyCode == '13') {
                $.ajax({
                    type: 'post',
                    url: siteurl + active_controller + '/check_qr',
                    data: {
                        'qr_code': input,
                        'id_material': id_material
                    },
                    cache: false,
                    dataType: 'json',
                    success: function(result) {
                        if (result.valid == 1) {
                            $('#save').prop('disabled', false);

                            var isChecked = $('.check_lot_' + result.hasil).prop('checked');
                            if (isChecked) {
                                $('.qty_aktual_input_' + result.hasil).prop('readonly', false);
                            } else {
                                swal({
                                    title: 'Error !',
                                    text: 'Please check the proper checkbox first !',
                                    type: 'error'
                                });
                            }
                            $('#qr_code').val('');
                        } else {
                            if (result.valid == 2) {
                                swal({
                                    title: 'Error !',
                                    text: 'Please select same lot with outgoing material !',
                                    type: 'error'
                                });
                            } else if (result.valid == 3) {
                                swal({
                                    title: 'Error !',
                                    text: 'This lot has been all used !',
                                    type: 'error'
                                });
                            } else {
                                swal({
                                    title: 'Error !',
                                    text: 'Please try again later !',
                                    type: 'error'
                                });
                            }
                        }
                    }
                })
            }
        })

        $(document).on('keyup', '.changeDelivery', function() {
            let qty_delivery = $(this).val()
            let id_spk = $(this).data('id_spk')

            let qty_spk = getNum($(this).parent().parent().find('.qtyBelumKirim').text().split(',').join(''))
            if (qty_delivery > qty_spk) {
                $(this).val(qty_spk)

                qty_delivery = qty_spk
            }

            $.ajax({
                url: base_url + active_controller + '/changeDeliveryTemp',
                type: "POST",
                data: {
                    'id_spk': id_spk,
                    'qty_delivery': qty_delivery,
                },
                cache: false,
                dataType: 'json',
                success: function(data) {
                    if (data.status == '1') {
                        console.log('Success !!!')
                    } else {
                        console.log('Failed !!!')
                    }
                },
                error: function() {
                    console.log('Error !!!')
                }
            });
        });

        $(document).on('click', '.delPart', function() {
            var get_id = $(this).parent().parent().attr('class');
            $("." + get_id).remove();


            let id_spk = $(this).data('id')

            $.ajax({
                url: base_url + active_controller + '/deleteDeliveryTemp',
                type: "POST",
                data: {
                    'id_spk': id_spk
                },
                cache: false,
                dataType: 'json',
                success: function(data) {
                    if (data.status == '1') {
                        console.log('Success !!!')
                    } else {
                        console.log('Failed !!!')
                    }
                },
                error: function() {
                    console.log('Error !!!')
                }
            });
        });


        $('#save').click(function(e) {
            e.preventDefault();

            var checked_lot = $('.check_lot:checked').length;

            var aktual_qty = 0;
            $('.qty_aktual_input').each(function() {
                var qty = parseFloat($(this).val());
                if (!isNaN(qty)) {
                    aktual_qty += qty;
                }
            });

            if (checked_lot < 1) {
                swal({
                    title: 'Error !',
                    text: 'Please check first at least 1 Lot',
                    type: 'error'
                });
            } else if (aktual_qty <= 0) {
                swal({
                    title: 'Error !',
                    text: 'Please input the qty first !',
                    type: 'error'
                });
            } else {
                var qty_aktual_input = 0;
                $('.qty_aktual_input').each(function() {
                    var qty = parseFloat($(this).val());
                    if (!isNaN(qty)) {
                        qty_aktual_input += qty;
                    }
                });

                var id_lot = [];
                $('.check_lot:checked').each(function() {
                    var lot = $(this).val();
                    var qty_aktual_input = parseFloat($('.qty_aktual_input_' + lot).val());
                    var lot_qty = lot + '-' + qty_aktual_input;

                    id_lot.push(lot_qty);
                });

                var id_lot = id_lot.join(',');

                var id_material = $('.id_material').val();
                var id_lot_scan = id_lot;

                $('.qty_aktual_' + id_material).val(qty_aktual_input);
                $('.id_lot_' + id_material).val(id_lot_scan);

                $('#qr_code').val('');
                $('.id_material').val('');
                $('#load-data').html('');

                $("#ModalView3").modal('hide');

            }

            // swal({
            //         title: "Are you sure ?",
            //         text: "You will not be able to process again this data!",
            //         type: "warning",
            //         showCancelButton: true,
            //         confirmButtonClass: "btn-danger",
            //         confirmButtonText: "Yes, Process it!",
            //         cancelButtonText: "No, cancel process!",
            //         closeOnConfirm: true,
            //         closeOnCancel: false
            //     },
            //     function(isConfirm) {
            //         if (isConfirm) {
            //             var baseurl = siteurl + active_controller + '/save_qty_aktual';
            //             $.ajax({
            //                 url: baseurl,
            //                 type: "POST",
            //                 data: {
            //                     'id_material': id_material
            //                 },
            //                 cache: false,
            //                 dataType: 'json',
            //                 processData: false,
            //                 contentType: false,
            //                 success: function(data) {
            //                     if (data.status == 1) {
            //                         swal({
            //                             title: "Save Success!",
            //                             text: data.pesan,
            //                             type: "success",
            //                             timer: 7000
            //                         });
            //                         window.location.href = base_url + active_controller
            //                     } else {
            //                         swal({
            //                             title: "Save Failed!",
            //                             text: data.pesan,
            //                             type: "warning",
            //                             timer: 7000
            //                         });
            //                     }
            //                 },
            //                 error: function() {

            //                     swal({
            //                         title: "Error Message !",
            //                         text: 'An Error Occured During Process. Please try again..',
            //                         type: "warning",
            //                         timer: 7000
            //                     });
            //                 }
            //             });
            //         } else {
            //             swal("Cancelled", "Data can be process again :)", "error");
            //             return false;
            //         }
            //     });
        });

        $('#save1').click(function(e) {
            e.preventDefault();

            var checked_lot = $('.check_lott:checked').length;

            var aktual_qty = 0;
            $('.qty_aktual_input').each(function() {
                var qty = parseFloat($(this).val());
                if (!isNaN(qty)) {
                    aktual_qty += qty;
                }
            });

            if (checked_lot < 1) {
                swal({
                    title: 'Error !',
                    text: 'Please check first at least 1 Lot',
                    type: 'error'
                });
            } else if (aktual_qty <= 0) {
                swal({
                    title: 'Error !',
                    text: 'Please input the qty first !',
                    type: 'error'
                });
            } else {
                var qty_aktual_input = 0;
                $('.qty_aktual_input').each(function() {
                    var qty = parseFloat($(this).val());
                    if (!isNaN(qty)) {
                        qty_aktual_input += qty;
                    }
                });

                var id_lot = [];
                $('.check_lott:checked').each(function() {
                    var lot = $(this).val();
                    var qty_aktual_input = parseFloat($('.qty_aktual_input_' + lot).val());
                    var lot_qty = lot + '-' + qty_aktual_input;

                    id_lot.push(lot_qty);
                });

                var id_lot = id_lot.join(',');

                var id_material = $('.id_material').val();
                var id_lot_scan = id_lot;

                $('.qty_aktual_' + id_material).val(qty_aktual_input);
                $('.id_lot_' + id_material).val(id_lot_scan);

                $('#qr_code').val('');
                $('.id_material').val('');
                $('#load-data1').html('');

                $("#ModalView4").modal('hide');

            }

            // swal({
            //         title: "Are you sure ?",
            //         text: "You will not be able to process again this data!",
            //         type: "warning",
            //         showCancelButton: true,
            //         confirmButtonClass: "btn-danger",
            //         confirmButtonText: "Yes, Process it!",
            //         cancelButtonText: "No, cancel process!",
            //         closeOnConfirm: true,
            //         closeOnCancel: false
            //     },
            //     function(isConfirm) {
            //         if (isConfirm) {
            //             var baseurl = siteurl + active_controller + '/save_qty_aktual';
            //             $.ajax({
            //                 url: baseurl,
            //                 type: "POST",
            //                 data: {
            //                     'id_material': id_material
            //                 },
            //                 cache: false,
            //                 dataType: 'json',
            //                 processData: false,
            //                 contentType: false,
            //                 success: function(data) {
            //                     if (data.status == 1) {
            //                         swal({
            //                             title: "Save Success!",
            //                             text: data.pesan,
            //                             type: "success",
            //                             timer: 7000
            //                         });
            //                         window.location.href = base_url + active_controller
            //                     } else {
            //                         swal({
            //                             title: "Save Failed!",
            //                             text: data.pesan,
            //                             type: "warning",
            //                             timer: 7000
            //                         });
            //                     }
            //                 },
            //                 error: function() {

            //                     swal({
            //                         title: "Error Message !",
            //                         text: 'An Error Occured During Process. Please try again..',
            //                         type: "warning",
            //                         timer: 7000
            //                     });
            //                 }
            //             });
            //         } else {
            //             swal("Cancelled", "Data can be process again :)", "error");
            //             return false;
            //         }
            //     });
        });

    });

    $(document).on('click', '.scan_qr', function() {
        var id_material = $(this).data('id_material');
        var qty_order = $(".qty_order_" + id_material).val();

        $.ajax({
            type: 'post',
            url: siteurl + active_controller + '/modal_scan_qr',
            data: {
                'id_material': id_material
            },
            cache: false,
            dataType: 'json',
            success: function(result) {
                $("#ModalView3").modal();
                $(".id_material").val(result.id_material);
                $(".qty_limit_order").val(qty_order);
                $("#load-data").html(result.hasil);
            }
        });
    });

    $(document).on('click', '.scan_no_qr', function() {
        var id_material = $(this).data('id_material');
        var qty_order = $(".qty_order_" + id_material).val();

        // $.ajax({
        //     type: 'post',
        //     url: siteurl + active_controller + '/modal_scan_no_qr',
        //     data: {
        //         'id_material': id_material
        //     },
        //     cache: false,
        //     dataType: 'json',
        //     success: function(result) {
        //         $("#ModalView4").modal();
        //         $(".id_material").val(result.id_material);
        //         $(".qty_limit_order").val(qty_order);
        //         $("#load-data1").html(result.hasil);
        //     }
        // });

        $('.qty_aktual_' + id_material).prop('readonly', false);
        $('.input_qty_app_' + id_material).val(1);
    })

    $(document).on('click', '#edit_material', function() {

        var emptyInputs = $(".id_lot").filter(function() {
            return $.trim($(this).val()) === ''; // Check if input value is empty
        });
        var count = emptyInputs.length;

        var count_input_app = 0;
        var appInputQty = $(".input_qty_app").each(function() {
            var app = $(this).val();
            if (app !== '' && app !== null) {
                count_input_app += 1;
            }
        });

        var ttl_qty_order = 0;
        $('.qty_order').each(function() {
            qty = parseFloat($(this).val());
            if (!isNaN(qty)) {
                ttl_qty_order += qty
            }
        });

        var ttl_qty_aktual = 0;
        $('.qty_aktual').each(function() {
            qty = parseFloat($(this).val());
            if (!isNaN(qty)) {
                ttl_qty_aktual += qty
            }
        });

        if ((count - count_input_app) > 0) {
            swal({
                title: 'Error !',
                text: 'Please make sure you check at least 1 Lot and qty is filled already !',
                type: 'error'
            });
        } else {
            if ((ttl_qty_order - ttl_qty_aktual) < 0) {
                swal({
                    title: 'Error !',
                    text: 'Sorry, there is exceeding amount of actual qty towards qty request !',
                    type: 'error'
                });
            } else {
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
                            var formData = new FormData($('#form_proses')[0]);
                            $.ajax({
                                url: base_url + active_controller + '/modal_request_edit',
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
                                            timer: 7000
                                        });

                                        window.location.href = base_url + active_controller
                                    } else {
                                        swal({
                                            title: "Save Failed!",
                                            text: data.pesan,
                                            type: "warning",
                                            timer: 7000
                                        });
                                    }
                                },
                                error: function() {
                                    swal({
                                        title: "Error Message !",
                                        text: 'An Error Occured During Process. Please try again..',
                                        type: "warning",
                                        timer: 7000
                                    });
                                }
                            });
                        } else {
                            swal("Cancelled", "Data can be process again :)", "error");
                            return false;
                        }
                    });
            }
        }
    });

    $(document).on('click', '.check_lot', function() {
        var id = $(this).data('id');

        var isChecked = $('.check_lot_' + id).prop('checked');
        if (isChecked) {
            $('.qty_aktual_input_' + id).prop('readonly', true);
        } else {
            $('.qty_aktual_input_' + id).prop('readonly', true);
        }
    });

    function number_format(number, decimals, dec_point, thousands_sep) {
        // Strip all characters but numerical ones.
        number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
        var n = !isFinite(+number) ? 0 : +number,
            prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
            sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
            dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
            s = '',
            toFixedFix = function(n, prec) {
                var k = Math.pow(10, prec);
                return '' + Math.round(n * k) / k;
            };
        // Fix for IE parseFloat(0.55).toFixed(0) = 0;
        s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
        if (s[0].length > 3) {
            s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
        }
        if ((s[1] || '').length < prec) {
            s[1] = s[1] || '';
            s[1] += new Array(prec - s[1].length + 1).join('0');
        }
        return s.join(dec);
    }
</script>