<!-- Main content-->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.7/css/dataTables.dataTables.min.css">
<section class="content">
    <div class="container-fluid">

        <div class="row">
            <div class="col-md-12">
                <div class="tabs-container">
                    <ul class="nav nav-tabs">
                        <li><a href="<?php echo site_url('master_konsultasi'); ?>">
                                Data Konsultasi</a>
                        </li>
                        <li class="active"><a href="<?php echo site_url('master_konsultasi/konsultasi_new/' . $id_paket); ?>">
                                Add Konsultasi</a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active">
                            <div class="panel panel-filled">
                                <div class="panel-body">
                                    <div class="row">
                                        <?php
                                        $form_id  = 'FormKonsultasi';
                                        echo form_open(site_url('master_konsultasi/konsultasi_new'), array('id' => $form_id));
                                        ?>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Nama Paket <span style="color:#f00;">*</span></label>
                                                <select class="form-control select2" name="konsultasi" id="konsultasi">
                                                    <option value="">-Pilih Paket-</option>
                                                    <?php
                                                    if ($paket->num_rows() > 0) {
                                                        foreach ($paket->result() as $d) {
                                                            echo "<option value='" . $d->id_paket . "'>" . $d->nm_paket . "</option>";
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <!-- <div class="table-responsive"> -->
                                            <table id="my-grid" class="table table-striped table-bordered table-condensed table-hover TableKonsultasi" width="100%">
                                                <thead>
                                                    <tr>
                                                        <th width="5%">
                                                            <center>#</center>
                                                        </th>
                                                        <th>Aktifitas</th>
                                                        <th width="20%">Harga</th>
                                                        <th width="10%">Bobot</th>
                                                        <th width="10%">Mandays</th>
                                                        <th width="10%">Check Point</th>
                                                        <th width="6%">Hapus</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="listKonsultasi">

                                                </tbody>
                                            </table>
                                            <!-- </div> -->
                                        </div>
                                        <div class="col-md-12">
                                            <a href="<?php echo site_url('master_konsultasi'); ?>" class="btn btn-danger">
                                                <i class="fa fa-arrow-left"></i> Kembali
                                            </a>
                                            <button type="button" class="btn btn-success" id="BarisBaru">
                                                <i class="fa fa-plus"></i> Tambah Data
                                            </button>
                                            <button type="submit" class="btn btn-primary" id="SaveKonfirmasi">
                                                <i class="fa fa-save"></i> Simpan Data
                                            </button>

                                        </div>
                                        <?php echo form_close(); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal in" id="MyModal" role="dialog" aria-labelledby="MyModal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" id="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><i class="fa fa-times-circle"></i></button>
                    <h4 class="modal-title" id="modal-title">Add Check Point</h4>
                </div>
                <div class="modal-body" id="modal-body">

                </div>
                <div class="modal-footer" id="modal-footer">

                </div>
            </div>
        </div>
    </div>
</section>
<!-- End main content-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js" integrity="sha512-rMGGF4wg1R73ehtnxXBt5mbUfN9JUJwbk21KMlnLZDJh7BkPmeovBuddZCENJddHYYMkCh9hPFnPmS9sspki8g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.datatables.net/2.1.7/js/dataTables.min.js"></script>
<script src="<?= base_url('assets/js/basic.js') ?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script type="text/javascript">
    $(document).ready(function() {
        run_select2();

        for (i = 1; i <= 1; i++) {
            AppendBaris();
        }

        $("#<?php echo $form_id; ?>").keypress(function(e) {
            //Enter key
            if (e.which == 13) {
                return false;
            }
        });
        // chosen bootstrap


        // Append New Line
        $(document).on('click', '#BarisBaru', function(e) {
            e.preventDefault();
            AppendBaris();
        });

        // Delete New Line
        $(document).on('click', '#Batalkan', function(e) {
            e.preventDefault();
            $(this).parent().parent().remove();

            var Nomor = 1;
            $('.TableKonsultasi tbody tr').each(function() {
                $(this).find('td:nth-child(1)').html(Nomor);
                Nomor++;
            });
        });


        /*
        !----------------------------------------------------------------------------------------------------
        * Proses Checking Before Saving
        */
        $(document).on('click', '#SaveKonfirmasi', function(e) {
            e.preventDefault();
            var btnSave = "<button type='button' class='btn btn-primary' id='SaveFormKonsultasi' data-form-id='<?php echo $form_id; ?>'>Yes</button>";
            var btnClose = "<button type='button' class='btn btn-default' data-dismiss='modal'>Cancel</button>";

            BlurPage('MyModal');
            $('.modal-dialog').removeClass('modal-lg');
            $('.modal-dialog').addClass('modal-sm');
            $('#modal-title').html('Konfirmasi');
            $('#modal-body').html("Apakah Data Sudah Benar ?");
            $('#modal-footer').html(btnSave + btnClose);
            $('#MyModal').modal({
                backdrop: 'static',
                keyboard: false
            });
            $('#MyModal').modal('show');
        });

        /*
         *
         * //////////////////////////////////////////////////////////////////////////////////
         * PROCESSING SAVE ACTIVITY
         * //////////////////////////////////////////////////////////////////////////////////
         */
        $(document).on('click', '#SaveFormKonsultasi', function(e) {
            e.preventDefault();
            var FormID = $(this).data('form-id');
            $.ajax({
                url: $('#' + FormID).attr('action'),
                cache: false,
                type: 'POST',
                data: $('#' + FormID).serialize(),
                dataType: 'json',
                success: function(data) {
                    if (data.status == 0) {
                        swal({
                            type: 'warning',
                            title: 'Failed !',
                            text: data.pesan
                        }, function(after) {
                            location.reload(true);
                        });
                    }
                    if (data.status == 1) {
                        AjaxNotif(data.pesan);
                        $('#AnimateLoad').show();
                        $('#MyModal').modal('hide');
                        if (data.datatable_reload != undefined && data.datatable_reload.length > 0) {
                            $(data.datatable_reload).DataTable().ajax.reload(null, false);
                        }

                        // JIKA REDIRECT PAGE
                        // if (data.redirect_page == "YES") {
                            setTimeout(function() {
                                GoToPage(siteurl + active_controller);
                            }, 1500);
                        // }

                        $('#' + FormID).each(function() {
                            this.reset();
                        });
                        $('#' + FormID).find('input[type=text],textarea,select').filter(':visible:first').focus();
                    }
                }
            });
        });
    });


    /*
     * //////////////////////////////////////////////////////////////////////////////////
     * FUNCTION ADD NEW LINE ACTIVITY
     * //////////////////////////////////////////////////////////////////////////////////
     */
    function auto_num() {
        $('.auto_num').autoNumeric({
            decimalCharacter: '.',
            decimalPlaces: 2,
            minimumValue: '0.00',
        });
    }

    function run_select2() {
        $('.select2').chosen();
    }

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

    function AppendBaris() {

        var UnikNumber = "ACT-" + WaktuUnik();
        var Nomor = $('.TableKonsultasi tbody tr').length + 1;
        var Hapus = "<a href='#' class='btn btn-xs btn-danger' id='Batalkan' title='Hapus Baris'> <i class='fa fa-trash'></i> </a>";
        if (Nomor == 1) {
            Hapus = "";
        }
        var Baris = "<tr>";
        Baris += "    <td style='vertical-align:middle; width:40px;'><center>" + Nomor + "</center></td>";
        Baris += "    <td style='vertical-align:middle;'>";
        Baris += "         <select class='form-control id_aktifitas_" + Nomor + "' name='id_aktifitas[]' id='NamaAktifitas'>";
        Baris += "              <option value=''>Pilih Aktifitas</option>";
        Baris += "              <?php if ($all_aktifitas->num_rows() > 0) { ?>";
        Baris += "                  <?php foreach ($all_aktifitas->result() as $d) { ?>";
        Baris += "                      <option value='<?php echo $d->id_aktifitas . '*_*' . $d->nm_aktifitas; ?>'><?php echo $d->nm_aktifitas; ?></option>";
        Baris += "                  <?php } ?>";
        Baris += "              <?php } ?>";
        Baris += "         </select>";
        Baris += "    </td>";
        Baris += "    <td>";
        Baris += "        <input type='text' class='form-control text-right auto_num' name='hrg_aktifitas[]' id='hrg_aktifitas'>";
        Baris += "        <input type='hidden' class='form-control' name='nm_aktifitas[]' id='nm_aktifitas'>";
        Baris += "    </td>";
        Baris += "    <td>";
        Baris += "        <input type='number' class='form-control' name='bobot[]' id='bobot'>";
        Baris += "    </td>";
        Baris += "    <td>";
        Baris += "        <input type='number' class='form-control' name='mandays[]' id='mandays'>";
        Baris += "    </td>";
        Baris += "    <td></td>";
        Baris += "    <td align='center' style='padding-top:13px'>" + Hapus + "</td>";
        Baris += "</tr>";
        $('.listKonsultasi').append(Baris);

        $('.id_aktifitas_' + Nomor).chosen();
        auto_num();

        /*
         * //////////////////////////////////////////////////////////////////////////////////
         * SELECT ACTIVITY
         * //////////////////////////////////////////////////////////////////////////////////
         */
    }
    $(document).on('change', '#NamaAktifitas', function(e) {
        e.preventDefault();
        var nm_aktifitas = $(this).parent().parent().find('td:nth-child(3) input#nm_aktifitas');
        var hrg_aktifitas = $(this).parent().parent().find('td:nth-child(3) input#hrg_aktifitas');
        var bobot = $(this).parent().parent().find('td:nth-child(4) input');
        var mandays = $(this).parent().parent().find('td:nth-child(5) input');
        var total_check = $(this).parent().parent().find('td:nth-child(6)');
        $.ajax({
            url: "<?php echo site_url('master_konsultasi/get_data_aktifitas'); ?>",
            cache: false,
            data: "id_aktifitas=" + $(this).val(),
            type: "POST",
            dataType: "json",
            success: function(data) {
                if (data.status == 1) {
                    nm_aktifitas.val(data.nm_aktifitas);
                    hrg_aktifitas.val(number_format(data.harga, 2));
                    bobot.val(data.bobot);
                    mandays.val(data.mandays);
                    total_check.html("<a href='<?php echo base_url("master_konsultasi/aktifitas_check_point"); ?>/" + data.id_aktifitas + "' class='btn btn-default btn-xs add-point' id='AddChekPoint'>" + data.total_chk + " POINT</a>");
                }
            }
        });
    });

    /*
     * //////////////////////////////////////////////////////////////////////////////////
     * BUTTON FOR PROCESS SHOW MODAL FORM CHECK POINT
     * //////////////////////////////////////////////////////////////////////////////////
     */
    $(document).on('click', '#AddChekPoint', function(e) {
        e.preventDefault();
        var link = $(this).attr('href');
        var serialize = $(this).parent().parent().find('input').serialize();
        var index_parent = $(this).parent().parent().index();
        BlurPage('MyModal');
        $('.modal-dialog').removeClass('modal-lg');
        $('.modal-dialog').removeClass('modal-sm');
        $('.modal-dialog').addClass('modal-lg');
        $('#modal-title').html('Add Check Point');
        $('#modal-body').load(link + "?" + serialize + "&indexnya=" + index_parent);
        $('#MyModal').modal({
            backdrop: 'static',
            keyboard: false
        });
        $('#MyModal').modal('show');
    });

    /*
     * //////////////////////////////////////////////////////////////////////////////////
     * PROCESS NEW LINE & REMOVE LINE CHECK POINT
     * //////////////////////////////////////////////////////////////////////////////////
     */
    $(document).on('click', '#NewLine', function(e) {
        e.preventDefault();
        AppendCheckPoint();
    });
    $(document).on('click', '#RemoveCheck', function(e) {
        e.preventDefault();
        $(this).parent().parent().remove();
        var Nomor = 1;
        $('.TableCheckPoint tbody tr').each(function() {
            $(this).find('td:nth-child(1)').html(Nomor);
            Nomor++;
        });
    });
    /*
     * //////////////////////////////////////////////////////////////////////////////////
     * FUNCTION ADD NEW LINE CHECK POINT
     * //////////////////////////////////////////////////////////////////////////////////
     */
    function AppendCheckPoint() {
        var Nomor = $('.TableCheckPoint tbody tr').length + 1;
        var Hapus = "<a href='#' class='btn btn-xs btn-danger' id='RemoveCheck' title='Hapus Baris'><i class='fa fa-trash'></i></a>";
        if (Nomor == 1) {
            Hapus = "";
        }
        var Baris = "<tr>";
        Baris += "    <td style='vertical-align:middle; width:40px;'>" + Nomor + "</td>";
        Baris += "    <td>";
        Baris += "        <input type='text' class='form-control' name='check_point[]' id='check_point'>";
        Baris += "        <input type='hidden' name='id_chk_point[]' value=''>";
        Baris += "    </td>";
        Baris += "    <td align='center' style='padding-top:13px'>" + Hapus + "</td>";
        Baris += "</tr>";
        $('.TableCheckPoint tbody').append(Baris);
    }

    /*
     *
     * //////////////////////////////////////////////////////////////////////////////////
     * PROCESSING SAVE CHECK POINT
     * //////////////////////////////////////////////////////////////////////////////////
     */
    $(document).on('click', '#SaveFormPoint', function(e) {
        e.preventDefault();
        var FormID = $(this).data('form-id');
        $.ajax({
            url: $('#' + FormID).attr('action'),
            cache: false,
            type: 'POST',
            data: $('#' + FormID).serialize(),
            dataType: 'json',
            success: function(data) {
                // if (data.status == 1) {
                //     swal({
                //         title: 'Success !',
                //         text: data.msg,
                //         type: 'success'
                //     });
                // } else {
                //     swal({
                //         title: 'Failed !',
                //         text: data.msg,
                //         type: 'warning'
                //     });
                // }
                if (data.status == 0) {
                    $('#Notification_' + FormID).html("<div class='alert alert-danger fade in alert-dismissible'>" + data.msg + "</div>");
                    setTimeout(function() {
                        $('#Notification_' + FormID).html('')
                    }, 3000);
                }
                if (data.status == 1) {
                    AjaxNotif(data.msg);
                    $('#MyModal').modal('hide');
                    $('.TableKonsultasi tbody tr:eq(' + data.indexnya + ') td:nth-child(6) #AddChekPoint').html(data.count_point + " POINT");
                    $('.TableKonsultasi tbody tr:eq(' + data.indexnya + ') td:nth-child(6) #DeleteChekPoint').show();
                }
                if (data.status == 2) {
                    $('#Notification_' + FormID).html("<div class='alert alert-danger fade in alert-dismissible'>" + data.pesan + "</div>");
                    setTimeout(function() {
                        $('#Notification_' + FormID).html('')
                    }, 3000);
                }
            },
            error: function(result) {
                swal({
                    title: 'Error !',
                    text: 'Please try again later!',
                    type: 'error'
                });
            }
        });
    });

    /*
     * //////////////////////////////////////////////////////////////////////////////////
     * PROCESSING DELETE CHECK POINT
     */
    $(document).on('click', '#DeleteChekPoint', function(e) {
        e.preventDefault();
        var link = $(this).attr('href');
        var index = $(this).parent().parent().index();
        var btnSave = "<button type='button' class='btn btn-primary' id='ProsesDeletePoint' data-link-id='" + link + "' data-indexnya='" + index + "'>Yes</button>";
        var btnClose = "<button type='button' class='btn btn-default' data-dismiss='modal'>Cancel</button>";

        BlurPage('MyModal');
        $('.modal-dialog').removeClass('modal-lg');
        $('.modal-dialog').addClass('modal-sm');
        $('#modal-title').html('Konfirmasi');
        $('#modal-body').html("Anda yakin ingin menghapus semua point ini ?");
        $('#modal-footer').html(btnSave + btnClose);
        $('#MyModal').modal({
            backdrop: 'static',
            keyboard: false
        });
        $('#MyModal').modal('show');
    });
</script>