<?php
$form_id = "FormEdit";
echo form_open(site_url('master_kategori/kategori_edit/' . @$detail->id_kategori_paket), array('id' => $form_id));
?>
<input type="hidden" name="id" value="<?= $id ?>">
<input type='hidden' name='nm_kategori_old' value='<?php echo @$detail->kategori_paket; ?>'>
<div class="col-12">
    <div class="form-group">
        <label>Nama kategori</label>
        <?php
        echo form_input(array(
            'name' => 'nm_kategori',
            'class' => 'form-control',
            'autocomplete' => 'off',
            'placeholder' => 'Nama kategori',
            'value' => @$detail->kategori_paket
        ));
        ?>
    </div>
    <div class="form-group">
        <div id="Notification_<?php echo $form_id; ?>"></div>
    </div>
</div>
<?php echo form_close(); ?>
<script type="text/javascript">
    $(document).ready(function() {
        $("#<?php echo $form_id; ?>").keypress(function(e) {
            //Enter key
            if (e.which == 13) {
                return false;
            }
        });

        // var btnSave  = "<button type='button' class='btn btn-sm btn-accent' id='SaveFormModal' data-form-id='<?php echo $form_id; ?>'><img src='<?php echo config_item('img'); ?>save.png' /> Simpan</button>";
        // var btnClose = "<button type='button' class='btn btn-sm btn-default' data-dismiss='modal'><i class='fa fa-remove'></i> Cancel</button>";

        // $('#modal-footer').html(btnSave + btnClose);
    });
</script>