<!-- Main content-->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.7/css/dataTables.dataTables.min.css">
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="tabs-container">
                    <ul class="nav nav-tabs">
                        <li><a href="<?php echo site_url('master_konsultan'); ?>">
                                Data Konsultan</a>
                        </li>
                        <li class="active"><a href="<?php echo site_url('master_konsultan/konsultan_new'); ?>">
                                Tambah Konsultan</a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active">
                            <div class="panel panel-filled">
                                <div class="panel-body">
                                    <div class="row">
                                        <?php
                                        $form_id = "FormTambah";
                                        echo form_open(site_url('master_konsultan/konsultan_new'), array('id' => $form_id));
                                        ?>
                                        <div class="col-md-12">
                                            <div class="table-responsive">
                                                <table id="my-grid" class="table table-striped table-bordered table-hover table-condensed" width="100%">
                                                    <thead>
                                                        <tr>
                                                            <th>Nama Konsultan</th>
                                                            <th>Nama Kompetensi</th>
                                                            <th width="10%">Bobot</th>
                                                            <th>Delete</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>
                                                                <select class="form-control" name="nm_konsultan[]">
                                                                    <?php
                                                                    if ($konsultan->num_rows() > 0) {
                                                                        foreach ($konsultan->result() as $d) {
                                                                            echo "<option value='" . $d->id_konsultan . "'>" . $d->nama_konsultan . "</option>";
                                                                        }
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <input type="text" class="form-control" name="nm_kompetensi[]">
                                                            </td>
                                                            <td>
                                                                <input type="number" class="form-control" name="bobot[]">
                                                            </td>
                                                            <td></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <!--  <button type="button" class="btn btn-default" id="BarisBaru">
                                            <img src="<?php echo config_item('img'); ?>add2.png" alt=""> Tambah Data
                                        </button> -->
                                            <a href="#" id="SaveKonfirmasi" class="btn btn-sm btn-primary">
                                                <i class="fa fa-save"></i> Simpan Data
                                            </a>
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
        $("#<?php echo $form_id; ?>").keypress(function(e) {
            //Enter key
            if (e.which == 13) {
                return false;
            }
        });
        // chosen bootstrap
        $('.chosen-select').chosen();
        $('.chosen-select-deselect').chosen({
            allow_single_deselect: true
        });

        // Append New Line
        $(document).on('click', '#BarisBaru', function(e) {
            e.preventDefault();
            AppendBaris();
        });

        // Delete New Line
        $(document).on('click', '#Batalkan', function(e) {
            e.preventDefault();
            $(this).parent().parent().remove();
        });


        /*
        !----------------------------------------------------------------------------------------------------
        * Proses Checking Before Saving
        */
        $(document).on('click', '#SaveKonfirmasi', function(e) {
            e.preventDefault();
            var btnSave = "<button type='button' class='btn btn-primary' id='SaveFormKonsultan' data-form-id='<?php echo $form_id; ?>'>Yes</button>";
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
        !----------------------------------------------------------------------------------------------------
        * Proses Saving
        */
        $(document).on('click', '#SaveFormKonsultan', function(e) {
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
                        // ModalOpen("modal-sm", "Oops !", data.pesan, "html", "Close");
                        swal({
                            type: 'warning',
                            title: 'Failed',
                            text: data.pesan
                        });
                    }

                    if (data.status == 1) {
                        AjaxNotif(data.pesan);
                        $('#MyModal').modal('hide');
                        // if (data.datatable_reload != undefined && data.datatable_reload.length > 0) {
                        //     $(data.datatable_reload).DataTable().ajax.reload(null, false);
                        // }

                        // JIKA REDIRECT PAGE
                        // if (data.redirect_page == "YES") {
                            setTimeout(function() {
                                window.location = siteurl + active_controller
                            }, 1500);
                        // }

                        // $('#' + FormID).each(function() {
                        //     this.reset();
                        // });
                        // $('#' + FormID).find('input[type=text],textarea,select').filter(':visible:first').focus();
                    }
                    if (data.status == 2) {
                        ModalOpen("modal-sm", "Oops !", data.pesan, "html", "Close");
                    }
                }
            });
        });
    });

    /*!-----------------------------------------------------------------------------------------------------------------
     * Function Add New Line
     *
     */
    function AppendBaris() {
        var Uniks = WaktuUnik();
        var Baris = "<tr>";
        Baris += "    <td>";
        Baris += "        <select class='form-control' name='nm_konsultan[]'>";
        Baris += "            <?php if ($konsultan->num_rows() > 0) { ?>";
        Baris += "            <?php foreach ($konsultan->result() as $d) { ?>";
        Baris += "            <?php echo "<option value='" . $d->id_konsultan . "'>" . $d->nama_konsultan . "</option>"; ?>";
        Baris += "            <?php }
                                } ?>";
        Baris += "        </select>";
        Baris += "    </td>";
        Baris += "    <td>";
        Baris += "        <input type='text' class='form-control' name='nm_kompetensi[]'>";
        Baris += "    </td>";
        Baris += "    <td>";
        Baris += "        <input type='number' class='form-control' name='bobot[]'>";
        Baris += "    </td>";
        Baris += "    <td align='center' style='padding-top:13px'><a href='#' id='Batalkan' title='Hapus Baris'><img src='<?php echo config_item("img"); ?>trash.png'></a></td>";
        Baris += "</tr>";
        $('#my-grid tbody').append(Baris);
    }
</script>