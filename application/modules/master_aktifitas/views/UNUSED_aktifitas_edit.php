
<?php 
$form_id = "FormEdit";
echo form_open(site_url('master-aktifitas/aktifitas-edit/'.$id_aktifitas), array('id'=>$form_id));
?>
<input type='hidden' name='nm_aktifitas_old' value='<?php echo @$aktif->nm_aktifitas; ?>'>
<div class="row">
    <div class="form-group">
        <label>Nama Aktifitas</label>
        <?php 
        echo form_textarea(array(
            'name' => 'nm_aktifitas', 
            'class' => 'form-control', 
            'rows' => 7,
            'autocomplete' => 'off',
            'placeholder' => 'Nama Aktifitas',
            'style' => 'resize:vertical;',
            'value' => @$aktif->nm_aktifitas
        )); 
        ?>
    </div>
    <div class="form-group">
        <label>Harga</label>
        <?php 
        echo form_input(array(
            'type'  => 'number',
            'name'  => 'hrg_aktifitas', 
            'class' => 'form-control', 
            'autocomplete' => 'off',
            'placeholder' => 'Harga Aktifitas',
            'value' => @$aktif->harga_aktifitas
        )); 
        ?>
    </div>
    <div class="form-group">
        <label>Bobot</label>
        <?php 
        echo form_input(array(
            'type'  => 'number',
            'name'  => 'bobot', 
            'class' => 'form-control', 
            'autocomplete' => 'off',
            'placeholder' => 'Bobot Aktifitas',
            'value' => @$aktif->bobot
        )); 
        ?>
    </div>
    <div class="form-group">
        <label>Mandays</label>
        <?php 
        echo form_input(array(
            'type'  => 'number',
            'name'  => 'mandays', 
            'class' => 'form-control', 
            'autocomplete' => 'off',
            'placeholder' => 'Mandays Aktifitas',
            'value' => @$aktif->mandays
        )); 
        ?>
    </div>

    <div class="form-group">
        <div id="Notification_<?php echo $form_id; ?>"></div>
    </div>
</div>
<?php echo form_close(); ?>
<script type="text/javascript">
$(document).ready(function(){
    var btnSave  = "<button type='button' class='btn btn-sm btn-accent' id='SaveFormModal' data-form-id='<?php echo $form_id; ?>'><img src='<?php echo config_item('img'); ?>save.png' /> Simpan</button>";
    var btnClose = "<button type='button' class='btn btn-sm btn-default' data-dismiss='modal'><i class='fa fa-remove'></i> Cancel</button>";
    $('#modal-footer').html(btnSave + btnClose);
});
</script>