<?php
    $ENABLE_ADD     = has_permission('Inventory_1.Add');
    $ENABLE_MANAGE  = has_permission('Inventory_1.Manage');
    $ENABLE_VIEW    = has_permission('Inventory_1.View');
    $ENABLE_DELETE  = has_permission('Inventory_1.Delete');


    $id  = (!empty($header))?$header[0]->id:'';
    $rate   = (!empty($header))?$header[0]->rate:'';
    $rate_fitting   = (!empty($header))?$header[0]->rate_fitting:'';
    $code   = (!empty($header))?$header[0]->code:'';
?>

<div class="box box-primary">
	<div class="box-body">
		<form id="data_form" autocomplete="off">
      <div class="form-group row">
        <div class="col-md-3">
          <label for="">Product</label>
        </div>
        <div class="col-md-9">
          <select name='code' id='code' class='form-control input-sm chosen-select'  width='100%'>
  					<option value='0'>All Product</option>
  					<?php
  					foreach(get_product() AS $val => $valx){
              $selx = ($valx['id_category2'] == $code)?'selected':'';
  						echo "<option value='".$valx['id_category2']."' ".$selx.">".strtoupper($valx['nama'])."</option>";
  					}
  					?>
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
          <label for="">Fitting Price Ref ($)</label>
        </div>
        <div class="col-md-9">
          <input type="text" class="form-control maskM" id="rate" required name="rate_fitting" placeholder="Fitting Price" value='<?=$rate_fitting;?>'>
          <input type="hidden" class="form-control" id="id" required name="id" placeholder="Nama Unit"  value='<?=$id;?>'>
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
<script src="<?= base_url('assets/js/jquery.maskMoney.js')?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>
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
