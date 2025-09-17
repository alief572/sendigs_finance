<?php

$qData	= "SELECT * FROM asset WHERE id='" . $this->uri->segment(3) . "'";
$dataD	= $this->db->query($qData)->result_array();
$list_dept = $this->Asset_model->getList('department');
$list_catg = $this->Asset_model->getList('asset_category');

$QUERY	 	= "SELECT * FROM ms_costcenter WHERE id_costcenter = '" . $dataD[0]['cost_center'] . "' ORDER BY nama_costcenter ASC";
$costcenter	= $this->db->query($QUERY)->row();

$nama_costcenter = (!empty($costcenter)) ? $costcenter->nama_costcenter : '';
?>

<div class="box box-primary">
	<div class="box-header">
		<h3 class="box-title"></h3>
	</div>
	<div class="box-body">
		<div class='form-group row'>
			<label class='label-control col-sm-2'><b>Nama Asset <span class='text-red'>*</span></b></label>
			<div class='col-sm-4'>
				<?php
				echo form_input(array('id' => 'nm_asset', 'name' => 'nm_asset', 'class' => 'form-control input-md', 'autocomplete' => 'off', 'placeholder' => 'Nama Asset', 'readonly' => 'readonly'), strtoupper($dataD[0]['nm_asset']));
				echo form_input(array('type' => 'hidden', 'id' => 'id', 'name' => 'id'), $dataD[0]['id']);
				echo form_input(array('type' => 'hidden', 'id' => 'kd_asset', 'name' => 'kd_asset'), $dataD[0]['kd_asset']);
				echo form_input(array('type' => 'hidden', 'id' => 'helpa', 'name' => 'helpa', 'value' => 'N'));
				?>
			</div>
			<label class='label-control col-sm-2'><b>Kategori <span class='text-red'>*</span></b></label>
			<div class='col-sm-4'>
				<select name='category' id='category' class='form-control input-md' disabled>
					<?php
					foreach ($list_catg as $val => $valx) {
						$selx = ($dataD[0]['category'] == $valx['id']) ? 'selected' : '';
						echo "<option value='" . $valx['id'] . "' " . $selx . ">" . strtoupper($valx['nm_category']) . "</option>";
					}
					?>
				</select>
			</div>
		</div>
		<div class='form-group row'>
			<label class='label-control col-sm-2'><b>Department <span class='text-red'>*</span></b></label>
			<div class='col-sm-4'>
				<select name='lokasi_asset' id='lokasi_asset' class='form-control input-md' disabled>
					<?php
					foreach ($list_dept as $val => $valx) {
						$selx = ($dataD[0]['lokasi_asset'] == $valx['nm_dept']) ? 'selected' : '';
						echo "<option value='" . $valx['nm_dept'] . "' " . $selx . ">" . strtoupper($valx['nm_dept']) . "</option>";
					}
					?>
				</select>
			</div>
			<label class='label-control col-sm-2'><b>Company <span class='text-red'>*</span></b></label>
			<div class='col-sm-4'>
				<select name='company_asset' id='company_asset' class='form-control input-md' disabled>
					<?php
					foreach ($list_company as $val => $valx) {
						$selx = ($dataD[0]['id_company'] == $valx['id']) ? 'selected' : '';
						echo "<option value='" . $valx['id'] . "' " . $selx . ">" . strtoupper($valx['nm_company']) . "</option>";
					}
					?>
				</select>
			</div>
		</div>
		<div class='form-group row'>
			<label class='label-control col-sm-2'><b>Nilai Asset <span class='text-red'>*</span></b></label>
			<div class='col-sm-4'>
				<?php
				echo form_input(array('id' => 'nilai_asset', 'name' => 'nilai_asset', 'class' => 'form-control input-md', 'autocomplete' => 'off', 'placeholder' => 'Nilai Asset', 'data-decimal' => '.', 'data-thousand' => '', 'data-precision' => '0', 'data-allow-zero' => false, 'readonly' => 'readonly'), number_format($dataD[0]['nilai_asset']));
				?>
			</div>
			<label class='label-control col-sm-2'><b>Cost Center <span class='text-red'>*</span></b></label>
			<div class='col-sm-4'>
				<?php
				echo form_input(array('id' => 'cost_center', 'name' => 'cost_center', 'class' => 'form-control input-md', 'autocomplete' => 'off', 'placeholder' => 'Nilai Asset', 'readonly' => 'readonly'), strtoupper($nama_costcenter));
				?>
			</div>

		</div>
		<div class='form-group row'>
			<label class='label-control col-sm-2'><b>Jangka Waktu <span class='text-red'>*</span></b></label>
			<div class='col-sm-4'>
				<select name='depresiasi' id='depresiasi' class='form-control input-md' disabled>
					<?php
					for ($a = 1; $a <= 20; $a++) {
						$selx = ($dataD[0]['depresiasi'] == $a) ? 'selected' : '';
						echo "<option value='" . $a . "' " . $selx . ">" . $a . " Tahun</option>";
					}
					?>
				</select>
			</div>
			<label class='label-control col-sm-2'><b>Dipresiasi Perbulan</b></label>
			<div class='col-sm-4'>
				<?php
				echo form_input(array('id' => 'value', 'name' => 'value', 'class' => 'form-control input-md', 'autocomplete' => 'off', 'placeholder' => 'Dipresiasi Perbulan', 'readonly' => 'readonly', 'data-decimal' => '.', 'data-thousand' => '', 'data-precision' => '0', 'data-allow-zero' => false), number_format($dataD[0]['value']));
				?>
			</div>
		</div>
	</div>
</div>