<?php
$code_material  = (!empty($header))?$header[0]->code_material:'';
$code_company   = (!empty($header))?$header[0]->code_company:'';
$nm_material    = (!empty($header))?strtoupper($header[0]->nm_material):'';
$satuan_packing = (!empty($header))?$header[0]->satuan_packing:'';
$konversi       = (!empty($header))?$header[0]->konversi:'';
$unit           = (!empty($header))?$header[0]->unit:'';
$begin_balance  = (!empty($header))?$header[0]->begin_balance:'';
$incoming       = (!empty($header))?$header[0]->incoming:'';
$outgoing       = (!empty($header))?$header[0]->outgoing:'';
$ending_balance = (!empty($header))?$header[0]->ending_balance:'';
$unit_fisik     = (!empty($header))?$header[0]->unit_fisik:'';
?>
<div class="box box-primary">
	<div class="box-body">
		<div class="form-group row">
			<div class="col-md-2">
				<label>Code Material</label>
			</div>
			<div class="col-md-4">
				<input type="text" id="code_company" name="code_company" class="form-control input-md" disabled placeholder="Code Material" value='<?=$code_company;?>'>
				<input type="hidden" id="code_material" name="code_material" class="form-control input-md" disabled  value='<?=$code_material;?>'>
			</div>
			<div class="col-md-2">
				<label>Material Name</label>
			</div>
			<div class="col-md-4">
				<input type="text" id="nm_material" name="nm_material" class="form-control input-md" disabled placeholder="Material Name" value='<?=$nm_material;?>'>
			</div>
		</div>
		<div class="form-group row">
			<div class="col-md-2">
				<label>Packing Unit</label>
			</div>
			<div class="col-md-4">
				<select id="satuan_packing" name="satuan_packing" class="form-control input-md chosen-select" disabled>
					<option value="0">Select An Option</option>
					<?php foreach ($results['satuan'] as $satuan){
						$sel = ($satuan->code == $satuan_packing)?'selected':'';
					?>
					<option value="<?= $satuan->code;?>" <?=$sel;?>><?= strtoupper(strtolower($satuan->code))?></option>
					<?php } ?>
				</select>
			</div>
			<div class="col-md-2">
				<label>Conversion</label>
			</div>
			<div class="col-md-4">
				<input type="text" id="konversi" name="konversi" class="form-control input-md maskM" disabled placeholder="Conversion" value='<?=$konversi;?>'>
			</div>
		</div>
		<div class="form-group row">
			<div class="col-md-2">
				<label>Unit</label>
			</div>
			<div class="col-md-4">
				<select id="unit" name="unit" class="form-control input-md chosen-select" disabled>
					<option value="0">Select An Option</option>
					<?php foreach ($results['satuan'] as $satuan){
						$sel = ($satuan->code == $unit)?'selected':'';
					?>
					<option value="<?= $satuan->code;?>" <?=$sel;?>><?= strtoupper(strtolower($satuan->code))?></option>
					<?php } ?>
				</select>
			</div>
			<div class="col-md-2">
				<label>Begin Balance /kg</label>
			</div>
			<div class="col-md-4">
				<input type="text" id="begin_balance" name="begin_balance" class="form-control input-md maskM" disabled placeholder="Begin Balance /kg" value='<?=$begin_balance;?>'>
			</div>
		</div>
		<div class="form-group row">
			<div class="col-md-2">
				<label>Incoming /kg</label>
			</div>
			<div class="col-md-4">
				<input type="text" id="incoming" name="incoming" class="form-control input-md maskM" disabled placeholder="Incoming /kg" value='<?=$incoming;?>'>
			</div>
			<div class="col-md-2">
				<label>Outgoing /kg</label>
			</div>
			<div class="col-md-4">
				<input type="text" id="outgoing" name="outgoing" class="form-control input-md maskM" disabled placeholder="Outgoing /kg" value='<?=$outgoing;?>'>
			</div>
		</div>
		<div class="form-group row">
			<div class="col-md-2">
				<label>Ending Balance /kg</label>
			</div>
			<div class="col-md-4">
				<input type="text" id="ending_balance" name="ending_balance" class="form-control input-md maskM" disabled placeholder="Ending Balance /kg" value='<?=$ending_balance;?>'>
			</div>
			<div class="col-md-2">
				<label>Unit Fisik</label>
			</div>
			<div class="col-md-4">
				<input type="text" id="unit_fisik" name="unit_fisik" class="form-control input-md" placeholder="Unit Fisik" disabled value='<?=$unit_fisik;?>'>
			</div>
		</div>
	</div>
</div>
