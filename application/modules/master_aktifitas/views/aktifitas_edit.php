<!-- Main content-->
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.7/css/dataTables.dataTables.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.css" integrity="sha512-0nkKORjFgcyxv3HbE4rzFUlENUMNqic/EzDIeYCgsKa/nwqr2B91Vu/tNAu4Q0cBuG4Xe/D1f/freEci/7GDRA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<section class="content">
    <div class="container-fluid">

        <div class="row">
            <div class="col-md-12">
                <div class="tabs-container">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="<?php echo site_url('master_aktifitas/aktifitas_edit/' . $id_aktifitas); ?>">
                                <i class="fa fa-pencil"></i> Update aktifitas</a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active">
                            <div class="panel panel-filled">
                                <div class="panel-body">
                                    <div class="row">
                                        <?php
                                        $form_id  = 'FormAktifitas';
                                        echo form_open(site_url('master_aktifitas/aktifitas_edit/' . $id_aktifitas), array('id' => $form_id));
                                        ?>
                                        <div class="col-md-12">
                                            <div class="table-responsive">
                                                <table id="my-grid" class="table table-striped table-bordered table-hover Tableaktifitas" width="100%">
                                                    <thead>
                                                        <tr>
                                                            <th>Aktifitas</th>
                                                            <th width="15%">Harga</th>
                                                            <th width="10%">Bobot</th>
                                                            <th width="10%">Mandays</th>
                                                            <th width="17%">Check Point</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        if ($aktifitas->num_rows() > 0) {
                                                            foreach ($aktifitas->result() as $d) {
                                                        ?>
                                                                <tr>
                                                                    <td>
                                                                        <input type='text' class='form-control' name='nm_aktifitas[]' value="<?php echo @$d->nm_aktifitas; ?>">
                                                                        <input type='hidden' class='form-control' name='aktifitas_num[]' value="<?php echo @$d->id_aktifitas; ?>">
                                                                        <input type='hidden' class='form-control' name='aktifitas_unique_id[]' value="<?php echo @$d->unique_id; ?>">
                                                                    </td>
                                                                    <td>
                                                                        <input type='number' class='form-control' name='hrg_aktifitas[]' value="<?php echo @$d->harga_aktifitas; ?>">
                                                                    </td>
                                                                    <td>
                                                                        <input type='number' class='form-control' name='bobot[]' value="<?php echo @$d->bobot; ?>">
                                                                    </td>
                                                                    <td>
                                                                        <input type='number' class='form-control' name='mandays[]' value="<?php echo @$d->mandays; ?>">
                                                                    </td>
                                                                    <td>
                                                                        <?php
                                                                        $total_point = $this->db->where('id_aktifitas', $d->id_aktifitas)->get('kons_master_check_point')->num_rows();
                                                                        $display = "style='display:none;'";
                                                                        if ($total_point > 0) {
                                                                            $display = "";
                                                                        }
                                                                        ?>
                                                                        <a href='<?php echo site_url('master_aktifitas/aktifitas_check_point/' . @$d->id_aktifitas); ?>' class='btn btn-default btn-xs add-point' id='AddChekPoint'>
                                                                            <?php echo $total_point; ?> POINT
                                                                        </a>
                                                                        <a href='<?php echo site_url('master_aktifitas/aktifitas_delete_point/' . @$d->id_aktifitas); ?>' class='btn btn-danger btn-xs add-point' id='DeleteChekPoint' <?php echo $display; ?>>
                                                                            DELETE POINT
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                        <?php
                                                            }
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <a href="<?php echo site_url('master_aktifitas'); ?>" class="btn btn-danger">
                                                <i class="fa fa-arrow-left"></i> Kembali
                                            </a>
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
<script src="<?= base_url('assets/js/basic.js') ?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script src="https://cdn.datatables.net/2.1.7/js/dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js" integrity="sha512-rMGGF4wg1R73ehtnxXBt5mbUfN9JUJwbk21KMlnLZDJh7BkPmeovBuddZCENJddHYYMkCh9hPFnPmS9sspki8g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $("#<?php echo $form_id; ?>").keypress(function(e) {
            //Enter key
            if (e.which == 13) {
                return false;
            }
        });
        // chosen bootstrap
        $('.chosen-select').chosen({
            width: '100%'
        });
        $('.chosen-select-deselect').chosen({
            allow_single_deselect: true
        });

        /*
        !----------------------------------------------------------------------------------------------------
        * Proses Checking Before Saving
        */
        $(document).on('click', '#SaveKonfirmasi', function(e) {
            e.preventDefault();
            var btnSave = "<button type='button' class='btn btn-primary' id='SaveFormaktifitas' data-form-id='<?php echo $form_id; ?>'>Yes</button>";
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
         * //////////////////////////////////////////////////////////////////////////////////
         * PROCESSING SAVE ACTIVITY
         */
        $(document).on('click', '#SaveFormaktifitas', function(e) {
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
                        ModalOpen("modal-sm", "Oops !", data.pesan, "html", "Close");
                    }
                    if (data.status == 1) {
                        AjaxNotif(data.pesan);
                        $('#AnimateLoad').show();
                        $('#MyModal').modal('hide');
                        if (data.datatable_reload != undefined && data.datatable_reload.length > 0) {
                            $(data.datatable_reload).DataTable().ajax.reload(null, false);
                        }

                        // JIKA REDIRECT PAGE
                        if (data.redirect_page == "YES") {
                            setTimeout(function() {
                                GoToPage(data.redirect_page_URL);
                            }, 1500);
                        }

                        $('#' + FormID).each(function() {
                            this.reset();
                        });
                        $('#' + FormID).find('input[type=text],textarea,select').filter(':visible:first').focus();
                    }
                    if (data.status == 2) {
                        ModalOpen("modal-sm", "Oops !", data.pesan, "html", "Close");
                    }
                }
            });
        });
    });

    /*
     * //////////////////////////////////////////////////////////////////////////////////
     * BUTTON FOR PROCESS SHOW MODAL FORM CHECK POINT
     */
    $(document).on('click', '#AddChekPoint', function(e) {
        e.preventDefault();
        var link = $(this).attr('href');
        var nm_aktifitas = $(this).parent().parent().find('td:nth-child(1) input');
        var hrg_aktifitas = $(this).parent().parent().find('td:nth-child(2) input');
        var bobot = $(this).parent().parent().find('td:nth-child(3) input');
        var mandays = $(this).parent().parent().find('td:nth-child(4) input');
        var serialize = $(this).parent().parent().find('input').serialize();
        var index_parent = $(this).parent().parent().index();

        if (nm_aktifitas.val() == '') {
            alert('Aktifitas tidak boleh kosong');
            nm_aktifitas.focus();
            return false;
        } else if (hrg_aktifitas.val() == '') {
            alert('Harga tidak boleh kosong');
            hrg_aktifitas.focus();
            return false;
        } else if (bobot.val() == '') {
            alert('Bobot tidak boleh kosong');
            bobot.focus();
            return false;
        } else if (mandays.val() == '') {
            alert('Mandays tidak boleh kosong');
            mandays.focus();
            return false;
        } else {
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
        }
    });

    /*
     * //////////////////////////////////////////////////////////////////////////////////
     * PROCESS NEW LINE & REMOVE LINE CHECK POINT
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
                if (data.status == 0) {
                    $('#Notification_' + FormID).html("<div class='alert alert-danger fade in alert-dismissible'>" + data.msg + "</div>");
                    setTimeout(function() {
                        $('#Notification_' + FormID).html('')
                    }, 3000);
                }
                if (data.status == 1) {
                    AjaxNotif(data.msg);
                    $('#MyModal').modal('hide');
                    $('.Tableaktifitas tbody tr:eq(' + data.indexnya + ') td:nth-child(5) #AddChekPoint').html(data.count_point + " POINT");
                    $('.Tableaktifitas tbody tr:eq(' + data.indexnya + ') td:nth-child(5) #DeleteChekPoint').show();
                }
                if (data.status == 2) {
                    $('#Notification_' + FormID).html("<div class='alert alert-danger fade in alert-dismissible'>" + data.msg + "</div>");
                    setTimeout(function() {
                        $('#Notification_' + FormID).html('')
                    }, 3000);
                }
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
    /*
     * //////////////////////////////////////////////////////////////////////////////////
     * PROCESSING DELETE CHECK POINT
     */
    $(document).on('click', '#ProsesDeletePoint', function(e) {
        e.preventDefault();
        var link_URL = $(this).data('link-id');
        var index = $(this).data('indexnya');
        $.ajax({
            url: link_URL,
            cache: false,
            type: 'POST',
            data: 'indexnya=' + index,
            dataType: 'json',
            success: function(data) {
                if (data.status == 1) {
                    AjaxNotif(data.pesan);
                    $('.Tableaktifitas tbody tr:eq(' + data.indexnya + ') td:nth-child(5) #AddChekPoint').html("ADD POINT");
                    $('.Tableaktifitas tbody tr:eq(' + data.indexnya + ') td:nth-child(5) #DeleteChekPoint').hide();
                    $('#MyModal').modal('hide');
                } else {
                    AjaxNotif(data.pesan);
                }
            }
        });
    });
</script>