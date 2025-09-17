<?php
	$id 				= (!empty($listData[0]->id))?$listData[0]->id:'';
	$kd_mesin 			= (!empty($listData[0]->kd_mesin))?$listData[0]->kd_mesin:'';
	$kapasitas 			= (!empty($listData[0]->kapasitas))?$listData[0]->kapasitas:'';
	$id_unit 			= (!empty($listData[0]->id_unit))?$listData[0]->id_unit:'';
	$harga_mesin 		= (!empty($listData[0]->harga_mesin))?$listData[0]->harga_mesin:'';
	$harga_mesin_usd 	= (!empty($listData[0]->harga_mesin_usd))?$listData[0]->harga_mesin_usd:'';
	$est_manfaat 		= (!empty($listData[0]->est_manfaat))?$listData[0]->est_manfaat:'';
	$depresiasi_bulan	= (!empty($listData[0]->depresiasi_bulan))?$listData[0]->depresiasi_bulan:'';
	$used_hour_month 	= (!empty($listData[0]->used_hour_month))?$listData[0]->used_hour_month:'';
	$biaya_mesin 		= (!empty($listData[0]->biaya_mesin))?$listData[0]->biaya_mesin:'';

	$kurs 				= (!empty($listData[0]->kurs))?$listData[0]->kurs:0;
	$kurs_tanggal 		= (!empty($listData[0]->kurs_tanggal))?date('d-M-Y',strtotime($listData[0]->kurs_tanggal)):'';
	$kurs_date 			= (!empty($listData[0]->kurs_date))?date('d-M-Y H:i:s',strtotime($listData[0]->kurs_date)):'';

?>
<div class="box box-primary">
	<div class="box-body">
		<form id="data_form" method="post"  autocomplete="off" enctype='multiple/form-data'>
			<div class="form-group row">
				<div class="col-md-2">
				<label for="">Machine Name <span class='text-danger'>*</span></label>
				</div>
				<div class="col-md-10">
				<select name="kd_mesin" id="kd_mesin" class='chosen-select'>
					<?php
					if(empty($id)){
						echo "<option value='0'>Select Machine</option>";
					}
					foreach ($list_asset as $key => $value) {
						if(empty($id)){
							if (!in_array($value['kd_asset'], $ArrProductCT)) {
								$selected = ($kd_mesin == $value['kd_asset'])?'selected':'';
								echo "<option value='".$value['kd_asset']."' data-nilai='".$value['nilai']."' data-dept_year='".$value['dept_year']."' data-dept_value='".$value['dept_value']."' ".$selected.">".strtoupper($value['nm_asset'])."</option>";
							}
						}
						else{
							if ($kd_mesin == $value['kd_asset']) {
								$selected = ($kd_mesin == $value['kd_asset'])?'selected':'';
								echo "<option value='".$value['kd_asset']."' data-nilai='".$value['nilai']."' data-dept_year='".$value['dept_year']."' data-dept_value='".$value['dept_value']."' ".$selected.">".strtoupper($value['nm_asset'])."</option>";
							}
						}
					}
					?>
				</select>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2">
					<label>Kapasitas</label>
				</div>
				<div class="col-md-4">
					<input type="hidden" id="id" name="id" value='<?=$id;?>'>
					<input type="text" class="form-control" id="kapasitas" name="kapasitas" value='<?=$kapasitas;?>' placeholder="Kapasitas">
				</div>
				<div class="col-md-2">
					<label>Unit Measurement</label>
				</div>
				<div class="col-md-4">
					<select id="id_unit" name="id_unit" class="form-control input-md chosen-select">
						<option value="0">Select An Option</option>
						<?php foreach ($satuan as $value){
						$sel = ($value->id == $id_unit)?'selected':'';
						?>
						<option value="<?= $value->id;?>" <?=$sel;?>><?= strtoupper($value->code)?></option>
						<?php } ?>
					</select>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2">
					<label>Machine Price</label>
				</div>
				<div class="col-md-4">
					<div class='input-group'>
						<input type="text" class="form-control maskM getDep" id="harga_mesin" name="harga_mesin" value='<?=$harga_mesin;?>' placeholder="Machine Price">
						<span class='input-group-btn'>
							<button type='button' class='btn btn-md btn-success' id='update-kurs'>Update Kurs</button>
						</span>
					</div>
				</div>
				<div class="col-md-2">
					<label>Machine Price (USD)</label>
				</div>
				<div class="col-md-4">
					<input type="text" class="form-control maskM getDep" id="harga_mesin_usd" name="harga_mesin_usd" value='<?=$harga_mesin_usd;?>' placeholder="Machine Price (USD)">
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2">
					<label>Depresiasi /Month</label>
				</div>
				<div class="col-md-4">
					<input type="text" class="form-control maskM" id="depresiasi_bulan" name="depresiasi_bulan" value='<?=$depresiasi_bulan;?>' placeholder="Depresiasi /Month" readonly>
				</div>
				<div class="col-md-2">
					<label>Used Estimation /Year</label>
				</div>
				<div class="col-md-4">
					<input type="text" class="form-control maskM getDep" id="est_manfaat" name="est_manfaat" value='<?=$est_manfaat;?>' placeholder="Used Estimation /Year">
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2">
					<label>Machine Price /Hour</label>
				</div>
				<div class="col-md-4">
					<input type="text" class="form-control maskM" id="biaya_mesin" name="biaya_mesin" value='<?=$biaya_mesin;?>' placeholder="Machine Price /Hour" readonly>
				</div>
				<div class="col-md-2">
					<label>Used Hour/Month</label>
				</div>
				<div class="col-md-4">
					<input type="text" class="form-control maskM getDep" id="used_hour_month" name="used_hour_month" value='<?=$used_hour_month;?>' placeholder="Used Hour/Month">
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2"></div>
				<div class="col-md-4">
				<button type="submit" class="btn btn-primary" name="save" id="save"><i class="fa fa-save"></i> Save</button>
				</div>
				<div class="col-md-6">
					<span class='text-bold'>Kurs:</span> <span id='label_kurs'><?= number_format($kurs);?></span><br>
					<span class='text-bold'>Kurs date:</span> <span id='label_kurs_date'><?= $kurs_tanggal;?></span><br>
					<span class='text-bold'>Last update kurs in this rate machine:</span> <span id='label_kurs_last'><?= $kurs_date;?></span>
				</div>
			</div>
		</form>
	</div>
</div>

<script>
	$(document).ready(function(){
    	$('.chosen-select').select2({width: '100%'});
		$('.maskM').autoNumeric();

		$(document).on('change','#kd_mesin',function(){
			var nilai 		= $(this).find(':selected').data('nilai')
			var dept_year 	= $(this).find(':selected').data('dept_year')
			var dept_value 	= $(this).find(':selected').data('dept_value')
			
			$('#dialog-popup #harga_mesin').val(nilai)
			$('#dialog-popup #est_manfaat').val(dept_year)
			$('#dialog-popup #depresiasi_bulan').val(dept_value)

			get_depresiasi();

		})
  	});
</script>
