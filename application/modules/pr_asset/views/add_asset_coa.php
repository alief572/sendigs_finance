<?php
$ArrSelect['Y']	= 'Active';
$ArrSelect['N']	= 'Not Active';

$id             = (!empty($data[0]->id))?$data[0]->id:'';
$keterangan		= (!empty($data[0]->keterangan))?$data[0]->keterangan:'';
$coa    		= (!empty($data[0]->coa))?$data[0]->coa:'';
$coa_kredit		= (!empty($data[0]->coa_kredit))?$data[0]->coa_kredit:'';
$status         = (!empty($data[0]->status))?$data[0]->status:'Y';
?>
<div class="box box-primary"><br>
    <div class="box-body">
        <div class="form-group row">
            <div class="col-md-3">
                <label>Keterangan</label> 
            </div>
            <div class="col-md-9">
                <input type="text" class="form-control" id="keterangan" name="keterangan" placeholder="Keterangan" value='<?=$keterangan;?>'>
                <input type="hidden" class="form-control" id="id" name="id" value='<?=$id;?>'>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-3">
                <label>COA Debet</label>
            </div>
            <div class="col-md-9">
                <?php
                    echo form_dropdown('coa', $coalist, $coa, array('id'=>'coa','class'=>'form-control input-md chosen-select'));
                ?>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-3">
                <label>COA Kredit</label>
            </div>
            <div class="col-md-9">
                <?php
                    echo form_dropdown('coa_kredit', $coalist, $coa_kredit, array('id'=>'coa_kredit','class'=>'form-control input-md chosen-select'));
                ?>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-3">
                <label>Status</label>
            </div>
            <div class="col-md-9">
                <?php
                    echo form_dropdown('status', $ArrSelect, $status, array('id'=>'status','class'=>'form-control input-md chosen-select'));
                ?>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-3"></div>
            <div class="col-md-9">
                <button type="button" class="btn btn-primary" name="save" id="save"><i class="fa fa-save"></i> Save</button>
            </div>
        </div>
    </div>
</div>
<style type="text/css">
	.chosen-container-active .chosen-single {
	     border: none;
	     box-shadow: none;
	}
	.chosen-container-single .chosen-single {
		height: 34px;
	    border: 1px solid #d2d6de;
	    border-radius: 0px;
	     background: none;
	    box-shadow: none;
	    color: #444;
	    line-height: 32px;
	}
	.chosen-container-single .chosen-single div{
		top: 5px;
	}
</style>
<script>
    swal.close();
    $(document).ready(function(){
        $('.chosen-select').chosen({
            width: '100%'
        });
    })
</script>