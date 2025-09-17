<?php

$paths 	= base_url().'/assets/foto/'.$dataD[0]['foto'];
?>

<div class="box box-primary">
	<div class="box-header">
		<h3 class="box-title"></h3>
	</div>
	<div class="box-body">
		<div class='form-group row'>		 	 
			<label class='label-control col-sm-2'><b>Asset Milik </b></label>
			<div class='col-sm-4'>             
				<?php
					echo form_input(array('id'=>'kdcab','name'=>'kdcab','class'=>'form-control input-md','autocomplete'=>'off','placeholder'=>'Nama Asset','readonly'=>'readonly'), strtoupper($dataD[0]['kdcab']));
				?>		
			</div>
			<label class='label-control col-sm-2'><b>Kategori Pajak <span class='text-red'>*</span></b></label>
			<div class='col-sm-4'>  
				<select name='category_pajak' id='category_pajak' class='form-control input-md chosen-select' disabled>
					<option>Pilih Kategori Pajak</option>
					<?php
						foreach($list_pajak AS $val => $valx){
							$sexd	= ($valx['id'] == $dataD[0]['category_pajak'])?'selected':'';
							echo "<option value='".$valx['id']."' ".$sexd.">".strtoupper($valx['nm_category'])."</option>";
						}
					?>
				</select>	
			</div>
		</div>
		<div class='form-group row'>		 	 
			<label class='label-control col-sm-2'><b>Nama Asset</b></label>
			<div class='col-sm-4'>             
				<?php
					echo form_input(array('id'=>'nm_asset','name'=>'nm_asset','class'=>'form-control input-md','autocomplete'=>'off','placeholder'=>'Nama Asset','readonly'=>'readonly'), strtoupper($dataD[0]['nm_asset']));
				?>		
			</div>
			<label class='label-control col-sm-2'><b>Kategori</b></label>
			<div class='col-sm-4'>  
				<select name='category' id='category' class='form-control input-md' disabled>
					<?php
						foreach($list_catg AS $val => $valx){
							$selx = ($dataD[0]['category'] == $valx['id'])?'selected':'';
							echo "<option value='".$valx['id']."' ".$selx.">".strtoupper($valx['nm_category'])."</option>";
						}
					?>
				</select>	
			</div>
		</div>
		<div class='form-group row'>		 	 
			<label class='label-control col-sm-2'><b>Department</b></label>
			<div class='col-sm-4'> 
				<select name='lokasi_asset' id='lokasi_asset' class='form-control input-md' disabled>
					<?php
						foreach($list_dept AS $val => $valx){
							$selx = ($dataD[0]['lokasi_asset'] == $valx['id'])?'selected':''; 
							echo "<option value='".$valx['id']."' ".$selx.">".strtoupper($valx['nm_dept'])."</option>";
						}
					?>
				</select>	
			</div>
			<label class='label-control col-sm-2'><b>Cost Center</b></label>
			<div class='col-sm-4'>             
				<?php 
					echo form_input(array('id'=>'cost_center','name'=>'cost_center','class'=>'form-control input-md','autocomplete'=>'off','placeholder'=>'Cost Center','readonly'=>'readonly'), strtoupper($dataD[0]['nm_costcenter']));											
				?>		
			</div>
		</div>
		<div class='form-group row'>		 	 
			<label class='label-control col-sm-2'><b>Nilai Asset</b></label>
			<div class='col-sm-4'>             
					<?php
						echo form_input(array('id'=>'nilai_asset','name'=>'nilai_asset','class'=>'form-control input-md','autocomplete'=>'off','placeholder'=>'Nilai Asset','data-decimal'=>'.','data-thousand'=>'','data-precision'=>'0','data-allow-zero'=>false,'readonly'=>'readonly'), number_format($dataD[0]['nilai_asset']));											
					?>		
			</div>
			
			<label class='label-control col-sm-2'><b>Tanggal Perolehan</b></label>
			<div class='col-sm-4'>
				<?php
					echo form_input(array('id'=>'tgl_perolehan','class'=>'form-control input-md','autocomplete'=>'off','readonly'=>'readonly'),date('d F Y', strtotime($dataD[0]['tgl_perolehan'])));											
				?>	
			</div>
		</div>
		<?php if($dataD[0]['penyusutan']=='Y'){?>
		<div class='form-group row'>	
			<label class='label-control col-sm-2'><b>Jangka Waktu</b></label>
			<div class='col-sm-4'>
				<?php
					echo form_input(array('id'=>'depresiasi','depresiasi'=>'value','class'=>'form-control input-md','autocomplete'=>'off','placeholder'=>'Jangka Waktu', 'readonly'=>'readonly'),$dataD[0]['depresiasi']." Tahun");											
				?>	
			</div>
			<label class='label-control col-sm-2'><b>Dipresiasi Perbulan</b></label>
			<div class='col-sm-4'>             
					<?php
						echo form_input(array('id'=>'value','name'=>'value','class'=>'form-control input-md','autocomplete'=>'off','placeholder'=>'Dipresiasi Perbulan', 'readonly'=>'readonly','data-decimal'=>'.','data-thousand'=>'','data-precision'=>'0','data-allow-zero'=>false), number_format($dataD[0]['value']));											
					?>		
			</div>
		</div>
		<?php } ?>
		<div class='form-group row'>	
			<label class='label-control col-sm-2'><b>Penyusutan</b></label>
			<div class='col-sm-4'>
				<select name='penyusutan' id='penyusutan' class='form-control input-md chosen-select' disabled>
					<option value='Y' <?= ($dataD[0]['penyusutan']=='Y')?'selected':'';?>>Yes</option>
					<option value='N' <?= ($dataD[0]['penyusutan']=='N')?'selected':'';?>>No</option>
				</select>
			</div>
			<label class='label-control col-sm-2'><b>Kelompok Penyusutan</b></label>
			<div class='col-sm-4'>  
				<select name='id_coa' id='id_coa' class='form-control input-md' disabled>
					<?php
						foreach($list_coa AS $val => $valx){
							$selx = ($dataD[0]['id_coa'] == $valx['id'])?'selected':'';
							echo "<option value='".$valx['coa']."' ".$selx.">".strtoupper($valx['coa'])." | ".strtoupper($valx['keterangan'])."</option>";
						}
					?>
				</select>	
			</div>
		</div>
		<div class='form-group row'>	
			<label class='label-control col-sm-2'><b>Foto</b></label>
			<div class='col-sm-4'>
				<img src="<?=$paths;?>" width='400px' height='400px'>
			</div>
			<label class='label-control col-sm-2'><b>Tanggal Mulai Penyusutan</b></label>
			<div class='col-sm-4'>
				<?php
					echo form_input(array('id'=>'tgl_depresiasi','class'=>'form-control input-md','autocomplete'=>'off','readonly'=>'readonly'),date('d F Y', strtotime($dataD[0]['tgl_depresiasi'])));											
				?>	
			</div>
		</div>
	</div>
</div>

<script>
swal.close();
</script>