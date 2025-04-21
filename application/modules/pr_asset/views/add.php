<?php
$this->load->view('include/side_menu');
$id             = (!empty($data)) ? $data[0]['id'] : '';
$disabled       = (!empty($data)) ? 'disabled' : '';
$kdcab       	= (!empty($data)) ? $data[0]['kdcab'] : '';
$kd_asset       = (!empty($data)) ? $data[0]['kd_asset'] : '';
$nm_asset       = (!empty($data)) ? $data[0]['nm_asset'] : '';
$category       = (!empty($data)) ? $data[0]['category'] : '';
$category_pajak = (!empty($data)) ? $data[0]['category_pajak'] : '';
$id_dept        = (!empty($data)) ? $data[0]['id_dept'] : '';
$nilai_asset    = (!empty($data)) ? $data[0]['nilai_asset'] : '';
$depresiasi     = (!empty($data)) ? $data[0]['depresiasi'] : '';
$value          = (!empty($data)) ? $data[0]['value'] : '';
$foto 			= (!empty($data[0]['id'])) ? $data[0]['foto'] : '';
$qty            = (!empty($data)) ? $data[0]['qty'] : '';
$id_costcenter  = (!empty($data)) ? $data[0]['id_costcenter'] : '';
$tgl_depresiasi  = (!empty($data)) ? $data[0]['tgl_depresiasi'] : '';
$tgl_perolehan  = (!empty($data)) ? $data[0]['tgl_perolehan'] : '';
$nama_user  	= (!empty($data)) ? $data[0]['nama_user'] : '';
$id_coa  		= (!empty($data)) ? $data[0]['id_coa'] : '';

// echo $_SERVER['DOCUMENT_ROOT'];
?>
<form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data">
	<input type="hidden" name='id' id='id' value='<?= $id; ?>'>
	<input type="hidden" name='kd_asset' value='<?= $kd_asset; ?>'>
	<input type="hidden" id='cs' value='<?= $id_costcenter; ?>'>
	<div class="box box-primary">
		<div class="box-header">
			<h3 class="box-title"><?php echo $title; ?></h3>
		</div>
		<div class="box-body">
			<div class='form-group row'>
				<label class='label-control col-sm-2'><b>Owned Assets <span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<select name='branch' id='branch' class='form-control input-md chosen-select'>
						<option value='0'>Select Owned Assets</option>
						<?php
						foreach ($list_cab as $val => $valx) {
							$sexd	= ($valx['id_branch'] == $kdcab) ? 'selected' : '';
							echo "<option value='" . $valx['id_branch'] . "' " . $sexd . ">" . strtoupper($valx['nm_alias']) . "</option>";
						}
						?>
					</select>
				</div>
			</div>
			<div class='form-group row'>
				<label class='label-control col-sm-2'><b>Tax Category <span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<select name='category_pajak' id='category_pajak' class='form-control input-md chosen-select' <?= $disabled; ?>>
						<option value='0'>Select Tax Category</option>
						<?php
						foreach ($list_pajak as $val => $valx) {
							$sexd	= ($valx['id'] == $category_pajak) ? 'selected' : '';
							echo "<option value='" . $valx['id'] . "' " . $sexd . ">" . strtoupper($valx['nm_category']) . "</option>";
						}
						?>
					</select>
				</div>
			</div>
			<div class='form-group row'>
				<label class='label-control col-sm-2'><b>Category <span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<select name='category' id='category' class='form-control input-md chosen_select' <?= $disabled; ?>>
						<option value='0'>Select Category</option>
						<?php
						foreach ($list_catg as $val => $valx) {
							$sexd	= ($valx['id'] == $category) ? 'selected' : '';
							echo "<option value='" . $valx['id'] . "' " . $sexd . ">" . strtoupper($valx['nm_category']) . "</option>";
						}
						?>
					</select>
				</div>
			</div>
			<div class='form-group row'>
				<label class='label-control col-sm-2'><b>Asset Name <span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<?php
					echo form_input(array('id' => 'nm_asset', 'name' => 'nm_asset', 'class' => 'form-control input-md', 'autocomplete' => 'off', 'placeholder' => 'Asset Name'), $nm_asset);
					?>
				</div>
				<label class='label-control col-sm-2'><b>Kelompok Penyusutan</b></label>
				<div class='col-sm-4'>
					<select name='id_coa' id='id_coa' class='form-control input-md chosen_select' <?= $disabled; ?>>
						<option value='0'>Select Kelompok Penyusutan</option>
						<option value='0'>TIDAK ADA PENYUSUTAN</option>
						<?php
						foreach ($list_coa as $val => $valx) {
							$sexd	= ($valx['id'] == $id_coa) ? 'selected' : '';
							echo "<option value='" . $valx['id'] . "' " . $sexd . ">" . strtoupper($valx['coa'] . ' | ' . $valx['keterangan']) . "</option>";
						}
						?>
					</select>
				</div>
			</div>
			<div class='form-group row'>
				<label class='label-control col-sm-2'><b>Department <span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<select name='lokasi_asset' id='lokasi_asset' class='form-control input-md chosen_select' <?= $disabled; ?>>
						<option value='0'>Select Department</option>
						<?php
						foreach ($list_dept as $val => $valx) {
							if ($valx['deleted'] == 'N') {
								$sexd	= ($valx['id'] == $id_dept) ? 'selected' : '';
								echo "<option value='" . $valx['id'] . "' " . $sexd . ">" . strtoupper($valx['nm_dept']) . "</option>";
							}
						}
						?>
					</select>
				</div>
				<label class='label-control col-sm-2'><b>Cost Center</b></label>
				<div class='col-sm-4'>
					<select name='cost_center' id='cost_center' class='form-control input-md chosen_select' <?= $disabled; ?>>
						<option value='0'>List Empty</option>
					</select>
				</div>
			</div>
			<?php if (!empty($id)) { ?>
				<div class='form-group row'>
					<label class='label-control col-sm-2'><b>Department New <span class='text-red'>*</span></b></label>
					<div class='col-sm-4'>
						<select name='lokasi_asset_new' id='lokasi_asset_new' class='form-control input-md chosen-select'>
							<option value='0'>Pilih Department</option>
							<?php
							foreach ($list_dept as $val => $valx) {
								// $sexd	= ($valx['nm_dept'] == 'UMUM')?'selected':'';
								$sexd	= "";
								echo "<option value='" . $valx['id'] . "' " . $sexd . ">" . strtoupper($valx['nm_dept']) . "</option>";
							}
							?>
						</select>
					</div>
					<label class='label-control col-sm-2'><b>Cost Center New <span class='text-red'>*</span></b></label>
					<div class='col-sm-4'>
						<select name='cost_center_new' id='cost_center_new' class='form-control input-md chosen-select'>
							<option value='0'>List Empty</option>
						</select>
					</div>
				</div>
			<?php } ?>
			<div class='form-group row'>
				<label class='label-control col-sm-2'><b>Value Asset <span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<?php
					echo form_input(array('id' => 'nilai_asset', 'name' => 'nilai_asset', 'class' => 'form-control input-md', 'autocomplete' => 'off', 'placeholder' => 'Value Asset', 'data-decimal' => '.', 'data-thousand' => '', 'data-precision' => '0', 'data-allow-zero' => false), $nilai_asset);
					?>
				</div>

				<label class='label-control col-sm-2 va'><b>Date Start Depreciation</b></label>
				<div class='col-sm-4'>
					<?php
					echo form_input(array('id' => 'tanggal', 'name' => 'tanggal', 'class' => 'form-control input-md', 'autocomplete' => 'off', 'placeholder' => 'Date Start Depreciation', 'readonly' => 'readonly'), $tgl_depresiasi);
					?>
				</div>
			</div>

			<div class='form-group row hide_penyusutan'>
				<label class='label-control col-sm-2'><b>Period of Time <span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<?php
					echo form_input(array('id' => 'depresiasi', 'name' => 'depresiasi', 'class' => 'form-control input-md', 'autocomplete' => 'off', 'placeholder' => 'Period of Time', 'readonly' => 'readonly', $disabled => $disabled), $depresiasi);
					?>
				</div>
				<label class='label-control col-sm-2'><b>Depreciation per month</b></label>
				<div class='col-sm-4'>
					<?php
					echo form_input(array('id' => 'value', 'name' => 'value', 'class' => 'form-control input-md', 'autocomplete' => 'off', 'placeholder' => 'Depreciation per month', 'readonly' => 'readonly', 'data-decimal' => '.', 'data-thousand' => '', 'data-precision' => '0', 'data-allow-zero' => false), $value);
					?>
				</div>
			</div>
			<div class='form-group row'>

				<label class='label-control col-sm-2'><b>Qty <span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<?php
					echo form_input(array('id' => 'qty', 'name' => 'qty', 'class' => 'form-control input-md', 'autocomplete' => 'off', 'placeholder' => 'Qty Assets', 'data-decimal' => '.', 'data-thousand' => '', 'data-precision' => '0', 'data-allow-zero' => false), $qty);
					?>
				</div>
				<?php if (empty($id)) { ?>
					<label class='label-control col-sm-2'><b>Photo</b></label>
					<div class='col-sm-4'>
						<?php
						echo form_input(array('type' => 'file', 'id' => 'foto', 'name' => 'foto', 'class' => 'form-control input-md', 'accept' => 'image/*'));
						?>
					</div>
				<?php } ?>
			</div>
			<div class='form-group row'>
				<label class='label-control col-sm-2'><b>Depresiasi <span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<select name='penyusutan' id='penyusutan' class='form-control input-md chosen-select' <?= $disabled; ?>>
						<option value='Y'>Yes</option>
						<option value='N'>No</option>
					</select>
				</div>
				<label class='label-control col-sm-2 va'><b>Date of Acquisition</b></label>
				<div class='col-sm-4'>
					<?php
					echo form_input(array('id' => 'tanggal_oleh', 'name' => 'tanggal_oleh', 'class' => 'form-control input-md', 'autocomplete' => 'off', 'placeholder' => 'Date of Acquisition', 'readonly' => 'readonly'), $tgl_perolehan);
					?>
				</div>
			</div>
			<div class='form-group row'>
				<label class='label-control col-sm-2'><b>Nama User</b></label>
				<div class='col-sm-4'>
					<?php
					echo form_input(array('id' => 'nama_user', 'name' => 'nama_user', 'class' => 'form-control input-md', 'autocomplete' => 'off', 'placeholder' => 'Nama User', $disabled => $disabled), $nama_user);
					?>
				</div>
			</div>
			<div class='box-footer'>
				<?php
				echo form_button(array('type' => 'button', 'style' => 'float:right; margin-left:5px;width:100px;', 'class' => 'btn btn-md btn-danger', 'value' => 'back', 'content' => 'Back', 'onClick' => 'javascript:back()'));

				if (empty($data[0]['id'])) {
					echo form_button(array('type' => 'button', 'class' => 'btn btn-md btn-primary', 'value' => 'save', 'content' => 'Save', 'id' => 'simpan-bro', 'style' => 'width:100px; float:right;')) . ' ';
				}
				if (!empty($data[0]['id'])) {
					echo form_button(array('type' => 'button', 'class' => 'btn btn-md btn-success', 'value' => 'save', 'content' => 'Save', 'id' => 'move_asset', 'style' => 'width:100px; float:right;')) . ' ';
				}

				// echo form_button(array('type'=>'button','style'=>'float:right;','class'=>'btn btn-md btn-primary','value'=>'save','content'=>'Save','id'=>'simpan-bro')).' ';
				?>
			</div>
		</div>
	</div>
</form>
<?php $this->load->view('include/footer'); ?>
<style>
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

	.chosen-container-single .chosen-single div {
		top: 5px;
	}

	<?php if (empty($data[0]['id'])) { ?>#tanggal {
		cursor: pointer;
		background-color: white;
	}

	#tanggal_oleh {
		cursor: pointer;
		background-color: white;
	}

	<?php } ?>
</style>
<script>
	$(function() {
		swal.close();
		$(".chosen_select").chosen();
		$('#nilai_asset').maskMoney();
		$('#qty').maskMoney();

		$('#tanggal').datepicker({
			format: 'yyyy-mm-dd'
		});

		$('#tanggal_oleh').datepicker({
			format: 'yyyy-mm-dd'
		});

		var id = $('#id').val();
		if (id != '') {
			$('#nm_asset').attr('readonly', 'true');
			$('#nilai_asset').attr('readonly', 'true');
			$('#qty').attr('readonly', 'true');
			// $('#tanggal').hide();
			// $('.va').hide();

			var lokasi_asset = $("#lokasi_asset").val();
			var cs = $("#cs").val();
			$.ajax({
				url: base_url + 'index.php/' + active_controller + '/list_center/' + lokasi_asset + '/' + cs,
				cache: false,
				type: "POST",
				dataType: "json",
				success: function(data) {
					$("#cost_center").html(data.option).trigger("chosen:updated");
					swal.close();
				},
				error: function() {
					swal({
						title: "Error Message !",
						text: 'Connection Time Out. Please try again..',
						type: "warning",
						timer: 3000,
						showCancelButton: false,
						showConfirmButton: false,
						allowOutsideClick: false
					});
				}
			});

		}

	});

	$(document).on('change', '#category_pajak', function() {
		var category = $(this).val();

		$.ajax({
			url: base_url + active_controller + '/get_jangka_waktu/' + category,
			cache: false,
			type: "POST",
			dataType: "json",
			success: function(data) {
				$("#depresiasi").val(data.jangka_waktu);
				get_depresiasi();
			},
			error: function() {
				swal({
					title: "Error Message !",
					text: 'Connection Time Out. Please try again..',
					type: "warning",
					timer: 3000,
					showCancelButton: false,
					showConfirmButton: false,
					allowOutsideClick: false
				});
			}
		});
	});

	$(document).on('change', '#penyusutan', function() {
		var penyusutan = $(this).val();
		if (penyusutan == 'Y') {
			$('.hide_penyusutan').show();
		}
		if (penyusutan == 'N') {
			$('.hide_penyusutan').hide();
		}

	});

	$(document).on('keyup', '#nilai_asset', function() {
		get_depresiasi();
	});

	$(document).on('change', '#lokasi_asset', function() {
		var nilai_asset = $(this).val();
		var cost_center = $("#cost_center");
		loading_spinner();
		$.ajax({
			url: base_url + 'index.php/' + active_controller + '/list_center/' + nilai_asset,
			cache: false,
			type: "POST",
			dataType: "json",
			success: function(data) {
				$(cost_center).html(data.option).trigger("chosen:updated");
				swal.close();
			},
			error: function() {
				swal({
					title: "Error Message !",
					text: 'Connection Time Out. Please try again..',
					type: "warning",
					timer: 3000,
					showCancelButton: false,
					showConfirmButton: false,
					allowOutsideClick: false
				});
			}
		});
	});

	$(document).on('change', '#lokasi_asset_new', function() {
		var nilai_asset = $(this).val();
		var cost_center = $("#cost_center_new");

		$.ajax({
			url: base_url + 'index.php/' + active_controller + '/list_center/' + nilai_asset,
			cache: false,
			type: "POST",
			dataType: "json",
			success: function(data) {
				$(cost_center).html(data.option).trigger("chosen:updated");
				$('.chosen-select').chosen();

				swal.close();
			},
			error: function() {
				swal({
					title: "Error Message !",
					text: 'Connection Time Out. Please try again..',
					type: "warning",
					timer: 3000,
					showCancelButton: false,
					showConfirmButton: false,
					allowOutsideClick: false
				});
			}
		});
	});

	$(document).on('click', '#simpan-bro', function(e) {
		e.preventDefault();
		$(this).prop('disabled', true);
		var nm_asset = $('#nm_asset').val();
		var category = $('#category').val();
		var lokasi_asset = $('#lokasi_asset').val();
		var cost_center = $('#cost_center').val();
		var depresiasi = $('#depresiasi').val();
		var nilai_asset = $('#nilai_asset').val();
		var qty = $('#qty').val();
		// var tanggal			= $('#tanggal').val();

		if (nm_asset == '' || nm_asset == null) {
			// $("#error").html("Nama asset masih kosong !!!");
			// $('#myModal').modal("show");
			swal({
				title: "Error Message!",
				text: 'Nama asset masih kosong ...',
				type: "warning"
			});

			$('#simpan-bro').prop('disabled', false);
			return false;
		}
		if (category == '' || category == null || category == 0) {
			swal({
				title: "Error Message!",
				text: 'Kategori asset belum dipilih ...',
				type: "warning"
			});

			$('#simpan-bro').prop('disabled', false);
			return false;
		}

		if (lokasi_asset == '' || lokasi_asset == null || lokasi_asset == 0) {
			swal({
				title: "Error Message!",
				text: 'Lokasi asset belum dipilih ...',
				type: "warning"
			});

			$('#simpan-bro').prop('disabled', false);
			return false;
		}

		// if(cost_center == '' || cost_center == null || cost_center == 0){
		// 	swal({
		// 		title	: "Error Message!",
		// 		text	: 'Cost Center belum dipilih ...',
		// 		type	: "warning"
		// 	});

		// 	$('#simpan-bro').prop('disabled',false);
		// 	return false;
		// }

		if (depresiasi == '' || depresiasi == null || depresiasi == 0) {
			swal({
				title: "Error Message!",
				text: 'Jangka waktu asset belum dipilih ...',
				type: "warning"
			});

			$('#simpan-bro').prop('disabled', false);
			return false;
		}
		if (nilai_asset == '' || nilai_asset == null || nilai_asset == 0) {
			swal({
				title: "Error Message!",
				text: 'Nilai asset belum dipilih ...',
				type: "warning"
			});

			$('#simpan-bro').prop('disabled', false);
			return false;
		}
		if (qty == '' || qty == null || qty == 0) {
			swal({
				title: "Error Message!",
				text: 'Qty asset belum dipilih ...',
				type: "warning"
			});

			$('#simpan-bro').prop('disabled', false);
			return false;
		}
		// if(tanggal == '' || tanggal == null || tanggal == 0){
		// swal({
		// title	: "Error Message!",
		// text	: 'Tanggal asset belum dipilih ...',
		// type	: "warning"
		// });

		// $('#simpan-bro').prop('disabled',false);
		// return false;
		// }
		// swal({
		// title	: "Error Message!",
		// text	: 'STOP',
		// type	: "warning"
		// });

		// return false;

		swal({
				title: "Are you sure?",
				text: "You will not be able to process again this data!",
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
					loading_spinner();
					var formData = new FormData($('#form_proses_bro')[0]);
					var baseurl = base_url + active_controller + '/add';
					$.ajax({
						url: baseurl,
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
									timer: 7000
								});
								window.location.href = base_url + active_controller;
							} else {
								if (data.status == 2) {
									swal({
										title: "Save Failed!",
										text: data.pesan,
										type: "warning",
										timer: 7000
									});
								} else if (data.status == 3) {
									swal({
										title: "Save Failed!",
										text: data.pesan,
										type: "warning",
										timer: 7000
									});
								} else {
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
								$('#simpan-bro').prop('disabled', false);
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
							$('#simpan-bro').prop('disabled', false);
						}
					});
				} else {
					swal("Cancelled", "Data can be process again :)", "error");
					$('#simpan-bro').prop('disabled', false);
					return false;
				}
			});
	});

	//move asesset
	$(document).on('click', '#move_asset', function(e) {
		e.preventDefault();
		$(this).prop('disabled', true);
		var branch = $('#branch').val();
		var lokasi_asset = $('#lokasi_asset_new').val();
		var cost_center = $('#cost_center_new').val();

		if (branch == '' || branch == null) {
			swal({
				title: "Error Message!",
				text: 'Asset milik masih kosong ...',
				type: "warning"
			});

			$('#move_asset').prop('disabled', false);
			return false;
		}

		if (lokasi_asset == '' || lokasi_asset == null || lokasi_asset == 0) {
			swal({
				title: "Error Message!",
				text: 'Department New belum dipilih ...',
				type: "warning"
			});

			$('#move_asset').prop('disabled', false);
			return false;
		}

		if (cost_center == '' || cost_center == null || cost_center == 0) {
			swal({
				title: "Error Message!",
				text: 'Cost Center New belum dipilih ...',
				type: "warning"
			});

			$('#move_asset').prop('disabled', false);
			return false;
		}

		swal({
				title: "Are you sure?",
				text: "You will not be able to process again this data!",
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
					// loading_spinner();
					var formData = new FormData($('#form_proses_bro')[0]);
					var baseurl = base_url + active_controller + '/move_asset';
					$.ajax({
						url: baseurl,
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
									timer: 7000
								});
								window.location.href = base_url + active_controller;
							} else {
								if (data.status == 2) {
									swal({
										title: "Save Failed!",
										text: data.pesan,
										type: "warning",
										timer: 7000
									});
								} else if (data.status == 3) {
									swal({
										title: "Save Failed!",
										text: data.pesan,
										type: "warning",
										timer: 7000
									});
								} else {
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
								$('#move_asset').prop('disabled', false);
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
							$('#move_asset').prop('disabled', false);
						}
					});
				} else {
					swal("Cancelled", "Data can be process again :)", "error");
					$('#move_asset').prop('disabled', false);
					return false;
				}
			});

	});

	function get_depresiasi() {
		var nilai_asset = $('#nilai_asset').val();
		var qty_asset = $('#qty').val();
		var depresiasi = parseFloat($('#depresiasi').val());
		var nilai = parseFloat(nilai_asset.split(',').join(''));

		var per_bulan = (nilai / (depresiasi * 12));
		if (isNaN(per_bulan)) {
			var per_bulan = 0;
		}
		$('#value').val(per_bulan.toFixed(0));
	}
</script>