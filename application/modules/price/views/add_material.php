<?php
    $ENABLE_ADD     = has_permission('Inventory_1.Add');
    $ENABLE_MANAGE  = has_permission('Inventory_1.Manage');
    $ENABLE_VIEW    = has_permission('Inventory_1.View');
    $ENABLE_DELETE  = has_permission('Inventory_1.Delete');


    $id  = (!empty($header))?$header[0]->id:'';
    $rate   = (!empty($header))?$header[0]->rate:'';
	$kurs   = (!empty($header))?$header[0]->kurs:'';
    $code   = (!empty($header))?$header[0]->code:'';
    $remarks = (!empty($header))?$header[0]->remarks:'';
    $type_material = (!empty($header))?$header[0]->type_material:'';
?>

<div class="box box-primary">
	<div class="box-body">
		<form id="data_form" autocomplete="off">
      <div class="form-group row">
        <div class="col-md-3">
          <label for="">Material</label>
        </div>
        <div class="col-md-9">
          <select name='code' id='code' class='form-control input-sm chosen-select' width='100%'>
  					<option value='0'>Select Material</option>
  					<?php
  					foreach(get_material() AS $val => $valx){
              $selx = ($valx['code_material'] == $code)?'selected':'';
  						echo "<option value='".$valx['code_material']."' ".$selx.">".strtoupper($valx['nm_material'])."</option>";
  					}
  					?>
  				</select>
        </div>
      </div>
	  
	  <div class="form-group row">
        <div class="col-md-3">
          <label for="">Kurs</label>
        </div>
        <div class="col-md-9">
			<select name='kurs' id='kurs' class='form-control input-sm chosen-select' width='100%'>
  				<option value='IDR' <?=($kurs=='IDR')?'selected':'';?>>IDR</option>
				<option value='USD' <?=($kurs=='USD')?'selected':'';?>>USD</option>
  			</select>
        </div>
      </div>
	  
      <div class="form-group row">
        <div class="col-md-3">
          <label for="">Price Ref ($)</label>
        </div>
        <div class="col-md-9">
          <input type="text" class="form-control maskM" id="rate" required name="rate" placeholder="Price" value='<?=$rate;?>'>
          <input type="hidden" class="form-control" id="id" required name="id" placeholder="Nama Unit"  value='<?=$id;?>'>
        </div>
      </div>
      <div class="form-group row">
        <div class="col-md-3">
          <label for="">Type Material</label>
        </div>
        <div class="col-md-9">
          <select name='type_material' id='type_material' class='form-control input-sm chosen-select' width='100%'>
  					<option value='0'>Select Type Material</option>
  					<?php
  					foreach(get_type_material_price() AS $val => $valx){
              $selx = ($valx['type_material'] == $type_material)?'selected':'';
  						echo "<option value='".$valx['type_material']."' ".$selx.">".strtoupper($valx['type_material'])."</option>";
  					}
  					?>
  				</select>
        </div>
      </div>
      <div class="form-group row">
        <div class="col-md-3">
          <label for="">Remarks</label>
        </div>
        <div class="col-md-9">
          <input type="text" class="form-control input-md" id="remarks" name="remarks" placeholder="Remarks" value='<?=$remarks;?>'>
        </div>
      </div>
      <div class="form-group row">
        <div class="col-md-3"></div>
        <div class="col-md-9">
          <button type="button" class="btn btn-primary" name="save" id="save"><i class="fa fa-save"></i> Save</button>
        </div>
      </div>
		</form>
	</div>
</div>

<style media="screen">
.select2-container {
    box-sizing: border-box;
    display: inline-block;
    margin: 0;
    position: relative;
    vertical-align: middle;
    width: 100% !important;
}
</style>
<script type="text/javascript">
  $(document).ready(function(){
    $('.chosen-select').select2();
    $('.maskM').maskMoney();
  })
</script>
