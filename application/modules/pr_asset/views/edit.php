<?php
$id_dept 		= (!empty($data_asset)) ? $data_asset[0]->id_dept : '';
$id_costcenter 	= (!empty($data_asset)) ? $data_asset[0]->id_costcenter : '';
$coa 			= (!empty($data_asset)) ? $data_asset[0]->coa : '';
$coa_akum 		= (!empty($data_asset)) ? $data_asset[0]->coa_akum : '';
$nama_asset 	= (!empty($data_asset)) ? strtoupper($data_asset[0]->nama_asset) : '';
$tahun 			= (!empty($data_asset)) ? $data_asset[0]->tahun : date('Y');
$bulan 			= (!empty($data_asset)) ? $data_asset[0]->bulan : date('m');
$budget 		= (!empty($data_asset)) ? number_format($data_asset[0]->budget) : '';
$qty 			= (!empty($data_asset)) ? number_format($data_asset[0]->qty) : '';
$keterangan 	= (!empty($data_asset)) ? strtoupper($data_asset[0]->keterangan) : '';
$tanda 			= (!empty($id)) ? 'Update' : 'Insert';
$disabled		= (!empty($approve)) ? 'disabled' : '';
$disabled2		= ($approve == 'view') ? 'disabled' : '';

$rev_nama_asset 	= (!empty($data_asset)) ? strtoupper($data_asset[0]->rev_nama_asset) : '';
$rev_tahun 			= (!empty($data_asset)) ? $data_asset[0]->rev_tahun : date('Y');
$rev_bulan 			= (!empty($data_asset)) ? $data_asset[0]->rev_bulan : date('m');
$rev_budget 		= (!empty($data_asset)) ? number_format($data_asset[0]->rev_budget) : '';
$rev_qty 			= (!empty($data_asset)) ? number_format($data_asset[0]->rev_qty) : '';
$rev_keterangan 	= (!empty($data_asset)) ? strtoupper($data_asset[0]->rev_keterangan) : '';

$no_pr = (!empty($data_asset)) ? $data_asset[0]->no_pr : '';
?>
<form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data" autocomplete='off'>
	<input type="hidden" name="id" value="<?= $id; ?>">
	<input type="hidden" name="no_pr" value="<?= $no_pr ?>">
	<input type="hidden" name="tanda" value="<?= $tanda; ?>">
	<input type="hidden" name="approve" value="<?= $approve; ?>">
	<div class="box box-primary" style='margin-right: 17px;'>
		<div class="box-header">
			<h3 class="box-title"><?php echo $title; ?></h3>
			<div class="box-tool pull-right">

			</div>
		</div>
		<div class="box-body">
			<div class='form-group row'>
				<label class='label-control col-sm-2'><b>Department <span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<select name='id_dept' id='id_dept' class='form-control input-md' <?= $disabled; ?>>
						<option value='0'>Select An Department</option>
						<?php
						foreach ($list_department as $val => $valx) {
							$dept = ($valx['id'] == $id_dept) ? 'selected' : '';
							echo "<option value='" . $valx['id'] . "' " . $dept . ">" . strtoupper($valx['nama']) . "</option>";
						}
						?>
					</select>
				</div>
				<label class='label-control col-sm-2'><b>Post Anggaran </b></label>
				<div class='col-sm-4'>
					<select name='coa' id='coa' class='form-control input-md' <?= $disabled; ?>>
						<option value='0'>Select An Post Anggaran</option>
						<?php
						foreach ($datacoa as $val => $valx) {
							$cc = ($valx['no_perkiraan'] == $coa) ? 'selected' : '';
							echo "<option value='" . $valx['no_perkiraan'] . "' " . $cc . ">" . strtoupper($valx['no_perkiraan']) . " - " . strtoupper($valx['nama']) . "</option>";
						}
						?>
					</select>
				</div>
			</div>
			<div class='form-group row'>
				<label class='label-control col-sm-2'><b>Cost Center</b></label>
				<div class='col-sm-4'>
					<select name='id_costcenter' id='id_costcenter' class='form-control input-md' <?= $disabled; ?>>
						<option value='0'>Select An Cost Center</option>
						<?php
						foreach ($list_costcenter as $val => $valx) {
							$cc = ($valx['id'] == $id_costcenter) ? 'selected' : '';
							echo "<option value='" . $valx['id'] . "' " . $cc . ">" . strtoupper($valx['nama_costcenter']) . "</option>";
						}
						?>
					</select>
				</div>
				<label class='label-control col-sm-2'><b>Post Penyusutan </b></label>
				<div class='col-sm-4'>
					<select name='coa_akum' id='coa_akum' class='form-control input-md' <?= $disabled; ?>>
						<option value='0'>Select An Post Penyusutan</option>
						<?php
						foreach ($penyusutan as $val => $valx) {
							$cc = ($valx['no_perkiraan'] == $coa_akum) ? 'selected' : '';
							echo "<option value='" . $valx['no_perkiraan'] . "' " . $cc . ">" . strtoupper($valx['no_perkiraan']) . " - " . strtoupper($valx['nama']) . "</option>";
						}
						?>
					</select>
				</div>
			</div>
			<div class='form-group row'>
				<label class='label-control col-sm-2'><b>Nama Asset</b></label>
				<div class='col-sm-4'>
					<?php
					echo form_input(array('id' => 'nama_asset', 'name' => 'nama_asset', 'class' => 'form-control input-md', 'placeholder' => 'Nama Assets'), $nama_asset);
					?>
				</div>
				<label class='label-control col-sm-2'><b>Bulan Tahun <span class='text-red'>*</span></b></label>
				<div class='col-sm-2'>
					<select name='bulan' id='bulan' class='form-control input-md' <?= $disabled2; ?>>
						<?php
						$selected = '';
						for ($i = 1; $i <= 12; $i++) {
							$selected = '';
							if (isset($bulan)) {
								if ($bulan == $i) $selected = ' selected';
							} else {
								if (date("m") == $i) $selected = ' selected';
							}
							echo "<option value='" . $i . "'" . $selected . ">" . date("F", mktime(0, 0, 0, $i, 10)) . "</option>";
						}
						?>
					</select>
				</div>
				<div class='col-sm-2'>
					<select name='tahun' id='tahun' class='form-control input-md' <?= $disabled2; ?>>
						<?php
						$selected = '';
						$tahunawal = (date("Y") + 1);
						for ($i = $tahunawal; $i >= 2019; $i--) {
							$selected = '';
							if (isset($tahun)) {
								if ($tahun == $i) $selected = ' selected';
							} else {
								if (date("Y") == $i) $selected = ' selected';
							}
							echo "<option value='" . $i . "'" . $selected . ">" . $i . "</option>";
						}
						?>
					</select>
				</div>
			</div>
			<div class='form-group row'>
				<label class='label-control col-sm-2'><b>Budget | Qty <span class='text-red'>*</span></b></label>
				<div class='col-sm-2'>
					<?php
					echo form_input(array('id' => 'budget', 'name' => 'budget', 'class' => 'form-control input-md maskM', 'placeholder' => 'Budget', 'data-decimal' => '.', 'data-thousand' => '', 'data-precision' => '0', 'data-allow-zero' => ''), $budget);
					?>
				</div>
				<div class='col-sm-2'>
					<?php
					echo form_input(array('id' => 'qty', 'name' => 'qty', 'class' => 'form-control input-md maskM', 'placeholder' => 'Qty', 'data-decimal' => '.', 'data-thousand' => '', 'data-precision' => '0', 'data-allow-zero' => ''), $qty);
					?>
				</div>
				<label class='label-control col-sm-2'><b>Keterangan</b></label>
				<div class='col-sm-4'>
					<?php
					echo form_textarea(array('id' => 'keterangan', 'name' => 'keterangan', 'class' => 'form-control input-md', 'rows' => '2', 'cols' => '75', 'placeholder' => 'Keterangan'), $keterangan);
					?>
				</div>
			</div>
			<?php
			if (!empty($approve)) {
				echo "<div class='box box-info' style='margin-right: 17px;'>
					<div class='box-header'>
					<h3 class='box-title'>Approve</h3>
					</div>
					<div class='box-body'>";
				if ($approve == 'view') {
			?>
					<div class='form-group row'>
						<label class='label-control col-sm-2'><b> Rev Nama Asset</b></label>
						<div class='col-sm-4'>
							<?php
							echo form_input(array('id' => 'rev_nama_asset', 'name' => 'rev_nama_asset', 'class' => 'form-control input-md', 'placeholder' => 'Nama Assets'), $rev_nama_asset);
							?>
						</div>
						<label class='label-control col-sm-2'><b>Rev Bulan Tahun</b></label>
						<div class='col-sm-2'>
							<select name='rev_bulan' id='rev_bulan' class='form-control input-md' <?= $disabled2; ?>>
								<?php
								$selected = '';
								for ($i = 1; $i <= 12; $i++) {
									$selected = '';
									if (isset($rev_bulan)) {
										if ($rev_bulan == $i) $selected = ' selected';
									} else {
										if (date("m") == $i) $selected = ' selected';
									}
									echo "<option value='" . $i . "'" . $selected . ">" . date("F", mktime(0, 0, 0, $i, 10)) . "</option>";
								}
								?>
							</select>
						</div>
						<div class='col-sm-2'>
							<select name='rev_tahun' id='rev_tahun' class='form-control input-md' <?= $disabled2; ?>>
								<?php
								$selected = '';
								$tahunawal = (date("Y") + 1);
								for ($i = $tahunawal; $i >= 2019; $i--) {
									$selected = '';
									if (isset($rev_tahun)) {
										if ($rev_tahun == $i) $selected = ' selected';
									} else {
										if (date("Y") == $i) $selected = ' selected';
									}
									echo "<option value='" . $i . "'" . $selected . ">" . $i . "</option>";
								}
								?>
							</select>
						</div>
					</div>
					<div class='form-group row'>
						<label class='label-control col-sm-2'><b>Rev Budget | Qty</b></label>
						<div class='col-sm-2'>
							<?php
							echo form_input(array('id' => 'rev_budget', 'name' => 'rev_budget', 'class' => 'form-control input-md maskM', 'placeholder' => 'Budget', 'data-decimal' => '.', 'data-thousand' => '', 'data-precision' => '0', 'data-allow-zero' => ''), $rev_budget);
							?>
						</div>
						<div class='col-sm-2'>
							<?php
							echo form_input(array('id' => 'rev_qty', 'name' => 'rev_qty', 'class' => 'form-control input-md maskM', 'placeholder' => 'Qty', 'data-decimal' => '.', 'data-thousand' => '', 'data-precision' => '0', 'data-allow-zero' => ''), $rev_qty);
							?>
						</div>
						<label class='label-control col-sm-2'><b>Rev Keterangan</b></label>
						<div class='col-sm-4'>
							<?php
							echo form_textarea(array('id' => 'rev_keterangan', 'name' => 'rev_keterangan', 'class' => 'form-control input-md', 'rows' => '2', 'cols' => '75', 'placeholder' => 'Keterangan'), $rev_keterangan);
							?>
						</div>
					</div>
				<?php
				}
				?>
				<div class='form-group row'>
					<label class='label-control col-sm-2'><b>Sisa Budget PR <span class='text-red'>*</span></b></label>
					<div class='col-sm-4'>
						<?php
						echo form_input(array('id' => 'budget_pr', 'name' => 'budget_pr', 'class' => 'form-control input-md maskM', 'placeholder' => 'Budget PR', 'data-decimal' => '.', 'data-thousand' => '', 'data-precision' => '0', 'data-allow-zero' => ''), $budget);
						?>
					</div>
					<label class='label-control col-sm-2'><b>Sisa Budget PO <span class='text-red'>*</span></b></label>
					<div class='col-sm-4'>
						<?php
						echo form_input(array('id' => 'budget_po', 'name' => 'budget_po', 'class' => 'form-control input-md maskM', 'placeholder' => 'Budget PO', 'data-decimal' => '.', 'data-thousand' => '', 'data-precision' => '0', 'data-allow-zero' => ''), $budget);
						?>
					</div>
				</div>
				<?php
				if ($approve == 'approve') {
				?>
					<div class='form-group row'>
						<label class='label-control col-sm-2'><b>Approve <span class='text-red'>*</span></b></label>
						<div class='col-sm-2'>
							<select name='status' id='status' class='form-control input-md'>
								<option value='0'>Select Approve</option>
								<option value='Y'>Approve</option>
								<option value='D'>Reject</option>
							</select>
						</div>
						<div class='col-sm-2'>

						</div>
						<label class='label-control col-sm-2 tnd_reason'><b>Reason <span class='text-red'>*</span></b></label>
						<div class='col-sm-4 tnd_reason'>
							<?php
							echo form_textarea(array('id' => 'reason', 'name' => 'reason', 'class' => 'form-control input-md', 'rows' => '2', 'cols' => '75', 'placeholder' => 'Reason'));
							?>
						</div>
					</div>
			<?php
				}
				echo "</div></div>";
			}
			?>
			<?php
			echo form_button(array('type' => 'button', 'class' => 'btn btn-md btn-danger', 'style' => 'min-width:100px; float:right; margin: 5px 0px 5px 0px;', 'content' => 'Back', 'id' => 'back')) . ' ';
			if ($approve != 'view') {
				echo form_button(array('type' => 'button', 'class' => 'btn btn-md btn-success', 'style' => 'min-width:100px; float:right; margin: 5px 5px 5px 0px;', 'value' => 'Create', 'content' => 'Save', 'id' => 'save')) . ' ';
			}
			?>
		</div>
	</div>
	<!-- /.box -->

</form>
<style>
	.chosen-container {
		width: 100% !important;
		text-align: left !important;
	}
</style>
<script>
	$(document).ready(function() {
		$('.maskM').maskMoney();
		$('.chosen_select').chosen();
		$('.tnd_reason').hide();
	});
	$(document).on('click', '#back', function(e) {
		window.location.href = base_url + active_controller + 'pr';
	});

	$(document).on('change', '#status', function(e) {
		var sts = $(this).val();
		if (sts == 'D') {
			$('.tnd_reason').show();
		} else {
			$('.tnd_reason').hide();
		}
	});
	$(document).on('keyup', '#budget', function(e) {
		var budget = $(this).val();
		$('#budget_pr').val(budget);
		$('#budget_po').val(budget);
	});

	$(document).on('click', '#save', function(e) {
		e.preventDefault();
		$('#save').prop('disabled', true);

		var department = $('#department').val();

		if (department == '0') {
			swal({
				title: "Error Message!",
				text: 'Department empty, select first ...',
				type: "warning"
			});

			$('#save').prop('disabled', false);
			return false;
		}

		swal({
				title: "Are you sure?",
				text: "You will save be able to process again this data!",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-danger",
				confirmButtonText: "Yes, Process it!",
				cancelButtonText: "No, cancel process!",
				closeOnConfirm: false,
				closeOnCancel: false
			},
			function(isConfirm) {
				if (isConfirm) {
					var formData = new FormData($('#form_proses_bro')[0]);
					$.ajax({
						url: base_url + active_controller + 'edit_asset',
						type: "POST",
						data: formData,
						cache: false,
						dataType: 'json',
						processData: false,
						contentType: false,
						success: function(data) {
							if (data.status == 1) {
								swal({
									title: "Save Success!",
									text: data.pesan,
									type: "success",
									timer: 7000,
									showCancelButton: false,
									showConfirmButton: false,
									allowOutsideClick: false
								});
								window.location.href = base_url + active_controller + 'pr';
							} else if (data.status == 0) {
								swal({
									title: "Save Failed!",
									text: data.pesan,
									type: "warning",
									timer: 7000,
									showCancelButton: false,
									showConfirmButton: false,
									allowOutsideClick: false
								});
							}
						},
						error: function() {
							swal({
								title: "Error Message !",
								text: 'An Error Occured During Process. Please try again..',
								type: "warning",
								timer: 7000,
								showCancelButton: false,
								showConfirmButton: false,
								allowOutsideClick: false
							});
							$('#save').prop('disabled', false);
						}
					});
				} else {
					swal("Cancelled", "Data can be process again :)", "error");
					$('#save').prop('disabled', false);
					return false;
				}
			});
	});
</script>