<?= form_open($this->uri->uri_string(), array('id' => 'frm_data', 'name' => 'frm_data', 'role' => 'form', 'class' => 'form-horizontal')) ?>
<?php
$readonly = "";
if (isset($data->id)) $readonly = " readonly"; ?>
<input type="hidden" id="id" name="id" value="<?php echo (isset($data->id) ? $data->id : ''); ?>">
<div class="tab-content">
    <div class="tab-pane active">
        <div class="box box-primary">
            <div class="box-body">

                <div class="form-group ">
                    <label for="jenis_pengeluaran" class="col-sm-2 control-label">Jenis Pengeluaran<b class="text-red">*</b></label>
                    <div class="col-sm-4">
                        <select name="jenis_pengeluaran" class="form-control select2" id="jenis_pengeluaran" required>
                            <option value="" disabled selected>- Pilih Jenis Pengeluaran -</option>
                            <option value="Expense" <?= isset($data) && ($data->jenis_pengeluaran == 'Expense') ? "selected" : ""; ?>>Expense</option>
                            <option value="PR Department" <?= isset($data) && ($data->jenis_pengeluaran == 'PR Department') ? "selected" : ""; ?>>PR Department</option>
                        </select>
                    </div>
                    <label for="coa" class="col-sm-2 control-label">COA<b class="text-red">*</b></label>
                    <div class="col-sm-4">
                        <select name="coa[]" id="coa" class="form-control select2" multiple placeholder="COA">
                            <?php
                            $arraycoa = array();
                            if (isset($data->coa)) $arraycoa = explode(';', $data->coa);
                            foreach ($datacoa as $key => $val) {
                                $selected = '';
                                if (isset($data->coa)) {
                                    if (in_array($key, $arraycoa)) {
                                        $selected = ' selected';
                                    }
                                }
                                echo '<option value="' . $val . '" ' . $selected . '>' . $val . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-group ">
                    <label for="keterangan" class="col-sm-2 control-label">Keterangan</label>
                    <div class="col-sm-4">
                        <textarea class="form-control" id="keterangan" name="keterangan" placeholder="Keterangan"><?php echo (isset($data->keterangan) ? $data->keterangan : ""); ?></textarea>
                    </div>
                </div>

                <div class="box-footer">
                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            <button type="submit" name="save" class="btn btn-success btn-sm" id="submit"><i class="fa fa-save">&nbsp;</i>Simpan</button>
                            <a class="btn btn-warning btn-sm" onclick="cancel()"><i class="fa fa-reply">&nbsp;</i>Batal</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= form_close() ?>
<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>
<script type="text/javascript">
    var url_save = siteurl + 'coa_expense/save/';
    $('.select2').select2();
    $('.divide').divide();

    $('#frm_data').on('submit', function(e) {
        e.preventDefault();
        var errors = "";
        if ($("#jenis_pengeluaran").val() == "") errors = "Jenis Pengeluaran tidak boleh kosong";
        if ($("#keterangan").val() == "") errors = "Keterangan tidak boleh kosong";
        if (errors == "") {
            data_save();
        } else {
            swal(errors);
            return false;
        }
    });
</script>