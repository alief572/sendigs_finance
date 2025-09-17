<?php
    $ENABLE_ADD     = has_permission('Master_Unit.Add');
    $ENABLE_MANAGE  = has_permission('Master_Unit.Manage');
    $ENABLE_VIEW    = has_permission('Master_Unit.View');
    $ENABLE_DELETE  = has_permission('Master_Unit.Delete');
	
	$id		= (!empty($header[0]->id))?$header[0]->id:'';
	$code	= (!empty($header[0]->code))?$header[0]->code:'';
	$nama	= (!empty($header[0]->nama))?$header[0]->nama:'';
?>

	<div class="box-body">
		<form id="data_form" autocomplete="off">
			<div class="form-group row">
				<div class="col-md-3">
					<label>Code</label>
				</div>
				<div class="col-md-9">
					<input type="text" class="form-control" id="code" required name="code" placeholder="Code" value='<?=$code;?>'>
					<input type="hidden" class="form-control" id="id" required name="id" value='<?=$id;?>'>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-3">
					<label>Unit Name</label>
				</div>
				<div class="col-md-9">
					<input type="text" class="form-control" id="nama" required name="nama" placeholder="Unit Name" value='<?=$nama;?>'>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-3"></div>
				<div class="col-md-9">
					<button type="submit" class="btn btn-primary" name="save" id="save"><i class="fa fa-save"></i> Save</button>
				</div>
			</div>
		</form>
	</div>
