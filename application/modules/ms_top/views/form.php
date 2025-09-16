<?php
$id			= (!empty($data[0]->id))?$data[0]->id:'';
$name	= (!empty($data[0]->name))?$data[0]->name:'';
$data1		= (!empty($data[0]->data1))?$data[0]->data1:'';

?>
<input type="hidden" class="form-control" id="id" name="id" value='<?=$id;?>'>
<div class="box box-primary"><br>
    <div class="box-body">
        <div class="form-group row">
            <div class="col-md-4">
                <label>Nama</label>
            </div>
            <div class="col-md-8">
				<input type="text" class="form-control" id="name" name="name" placeholder="Nama" value='<?=$name;?>' required>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-4">
                <label>Nilai</label>
            </div>
            <div class="col-md-8">
                <input type="text" class="form-control" id="data1" name="data1" placeholder="Nilai" value='<?=$data1;?>' required>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-4"></div>
            <div class="col-md-8">
                <button type="button" class="btn btn-primary" name="save" id="save"><i class="fa fa-save"></i> Save</button>
            </div>
        </div>
    </div>
</div>
<script src="<?= base_url('assets/js/number-divider.min.js')?>"></script>
<script>
    swal.close();
</script>