<?php
    $ENABLE_ADD     = has_permission('Inventory_1.Add');
    $ENABLE_MANAGE  = has_permission('Inventory_1.Manage');
    $ENABLE_VIEW    = has_permission('Inventory_1.View');
    $ENABLE_DELETE  = has_permission('Inventory_1.Delete');


    $id  = (!empty($header))?$header[0]->id:'';
    $code   = (!empty($header))?$header[0]->code:'';
?>
<style type="text/css">
thead input {
	width: 100%;
}
</style>

<div class="box box-primary">
	<div class="box-body">
		<form id="data_form" autocomplete="off">
      <div class="form-group row">
        <div class="col-md-3">
          <label for="">Nama Unit</label>
        </div>
        <div class="col-md-9">
          <input type="text" class="form-control" id="code" required name="code" placeholder="Nama Unit" value='<?=$code;?>'>
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
