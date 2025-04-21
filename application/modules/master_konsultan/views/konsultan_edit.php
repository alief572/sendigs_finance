<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.css" integrity="sha512-0nkKORjFgcyxv3HbE4rzFUlENUMNqic/EzDIeYCgsKa/nwqr2B91Vu/tNAu4Q0cBuG4Xe/D1f/freEci/7GDRA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<div class="col-12">
    <?php
    $form_id = "FormTambah";
    echo form_open(site_url('master_konsultan/konsultan_edit/' . $id_kompetensi), array('id' => $form_id));
    ?>
    <div class="table-responsive">
        <table id="my-grid" class="table table-striped table-bordered table-hover table-condensed grid-detail" width="100%">
            <thead>
                <tr>
                    <th>Nama Konsultan</th>
                    <th>Nama Kompetensi</th>
                    <th width="10%">Bobot</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($detail->num_rows() > 0) {
                    foreach ($detail->result() as $dt) {
                ?>
                        <tr>
                            <td>
                                <select class="form-control" name="nm_konsultan">
                                    <?php
                                    if ($konsultan->num_rows() > 0) {
                                        foreach ($konsultan->result() as $d) {
                                            // $selected = '';
                                            if ($d->id_konsultan == $dt->id_konsultan) {
                                                // $selected = 'selected';
                                                echo "<option value='" . $d->id_konsultan . "' " . $selected . ">" . $d->nama_konsultan . "</option>";
                                            }
                                        }
                                    }
                                    ?>
                                </select>
                            </td>
                            <td>
                                <input type="hidden" name="id" value="<?= $id_kompetensi ?>">
                                <input type="text" class="form-control" name="nm_kompetensi" value="<?php echo $dt->nm_kompetensi; ?>">
                            </td>
                            <td>
                                <input type="number" class="form-control" name="bobot" value="<?php echo $dt->bobot; ?>">
                            </td>
                            <td></td>
                        </tr>
                <?php
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
    <!-- <div class="row">
    <div class="col-md-12">
        <button type="button" class="btn btn-default" id="BarisBaru">
            <img src="<?php echo config_item('img'); ?>add2.png" alt=""> Tambah Data
        </button>
    </div>
    </div> -->
    <?php echo form_close(); ?>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js" integrity="sha512-rMGGF4wg1R73ehtnxXBt5mbUfN9JUJwbk21KMlnLZDJh7BkPmeovBuddZCENJddHYYMkCh9hPFnPmS9sspki8g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    $(document).ready(function() {
        var btnSave = "<a href='submit' class='btn btn-primary' id='SaveFormModal' data-form-id='<?php echo $form_id; ?>' style='border-radius:0;'><img src='<?php echo config_item('img'); ?>save.png' /> Simpan</a>";
        var btnClose = "<button type='button' class='btn btn-default' data-dismiss='modal' style='border-radius:0;'><i class='fa fa-remove'></i> Close</button>";
        // $('#modal-footer').html(btnSave + btnClose);

        $("#<?php echo $form_id; ?>").keypress(function(e) {
            //Enter key
            if (e.which == 13) {
                return false;
            }
        });
        // chosen bootstrap
        // $('.chosen-select').chosen();
        // $('.chosen-select-deselect').chosen({ allow_single_deselect: true });

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
        $('.grid-detail tbody').append(Baris);
    }
</script>