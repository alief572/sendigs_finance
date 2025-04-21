<?php
$ArrSelect['Y']	= 'Active';
$ArrSelect['N']	= 'Not Active';

$id             = (!empty($data[0]->id))?$data[0]->id:'';
$nm_category    = (!empty($data[0]->nm_category))?$data[0]->nm_category:'';
$status         = (!empty($data[0]->status))?$data[0]->status:'Y';
?>
<div class="box box-primary"><br>
    <div class="box-body">
        <div class="form-group row">
            <div class="col-md-3">
                <label>Category Name</label> 
            </div>
            <div class="col-md-9">
                <input type="text" class="form-control" id="nm_category" name="nm_category" placeholder="Category Name" value='<?=$nm_category;?>'>
                <input type="hidden" class="form-control" id="id" name="id" value='<?=$id;?>'>
            </div>
        </div>
        <div class="form-group row" hidden>
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