<?php
$form_id = "FormEdit";
echo form_open(site_url('master_paket/paket_edit/' . @$paket->id_paket), array('id' => $form_id));
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.css" integrity="sha512-0nkKORjFgcyxv3HbE4rzFUlENUMNqic/EzDIeYCgsKa/nwqr2B91Vu/tNAu4Q0cBuG4Xe/D1f/freEci/7GDRA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<input type='hidden' name='id_paket' value='<?php echo @$paket->id_paket; ?>'>
<input type='hidden' name='nm_paket_old' value='<?php echo @$paket->nm_paket; ?>'>
<div class="col-12">
    <div class="form-group">
        <label>Nama Paket</label>
        <?php
        echo form_textarea(array(
            'name' => 'nm_paket',
            'class' => 'form-control',
            'rows' => 7,
            'autocomplete' => 'off',
            'placeholder' => 'Nama Paket',
            'style' => 'resize:vertical;',
            'value' => @$paket->nm_paket
        ));
        ?>
    </div>
    <div class="form-group">
        <label>Kategori</label>
        <select class="form-control" name="kategori">
            <?php
            if ($kategori->num_rows() > 0) {
                foreach ($kategori->result() as $d) {
                    $selected = '';
                    if ($d->id_kategori_paket == $paket->id_kategori) {
                        $selected = 'selected';
                    }
                    echo "<option value='" . $d->id_kategori_paket . "' $selected>" . $d->kategori_paket . "</option>";
                }
            }
            ?>
        </select>
    </div>
</div>
<?php echo form_close(); ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js" integrity="sha512-rMGGF4wg1R73ehtnxXBt5mbUfN9JUJwbk21KMlnLZDJh7BkPmeovBuddZCENJddHYYMkCh9hPFnPmS9sspki8g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
