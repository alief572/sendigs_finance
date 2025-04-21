<?php
$id_dept 		= (!empty($header)) ? $header[0]->id_dept : '';
$id_costcenter 	= (!empty($header)) ? $header[0]->id_costcenter : '';
$budget 		= (!empty($header)) ? number_format($header[0]->budget) : '0';
$sisa_budget 	= (!empty($header)) ? number_format($header[0]->sisa_budget) : '0';
$coa 			= (!empty($header)) ? $header[0]->coa : '';
$upload_spk 	= (!empty($header)) ? $header[0]->document : '';
$no_so 			= (!empty($header)) ? $header[0]->no_so : '';
$project_name 	= (!empty($header)) ? $header[0]->project_name : '';
$pr_coa 	= (!empty($header)) ? $header[0]->coa : '';
$tingkat_pr = (!empty($header)) ? $header[0]->tingkat_pr : '';

// Detail Approval
$alasan_reject1 = (!empty($header)) ? $header[0]->reject_reason1 : '';
$alasan_reject2 = (!empty($header)) ? $header[0]->reject_reason2 : '';
$alasan_reject3 = (!empty($header)) ? $header[0]->reject_reason3 : '';

$keterangan_1 = (!empty($header)) ? $header[0]->keterangan_1 : '';
$keterangan_2 = (!empty($header)) ? $header[0]->keterangan_2 : '';
$keterangan_3 = (!empty($header)) ? $header[0]->keterangan_3 : '';

$status1 = '';
$tgl_appre_1 = '';
$status2 = '';
$tgl_appre_2 = '';
$status3 = '';
$tgl_appre_3 = '';
if (!empty($header)) {
	if ($header[0]->app_1 == '1') {
		$status1 = '<div class="badge bg-green">Approved</div>';
		$tgl_appre_1 = date('d F Y', strtotime($header[0]->app_1_date));
	} else {
		if ($header[0]->sts_reject1 == '1') {
			$status1 = '<div class="badge bg-red">Rejected</div>';
			$tgl_appre_1 = date('d F Y', strtotime($header[0]->sts_reject1_date));
		}
	}

	if ($header[0]->app_2 == '1') {
		$status2 = '<div class="badge bg-green">Approved</div>';
		$tgl_appre_2 = date('d F Y', strtotime($header[0]->app_2_date));
	} else {
		if ($header[0]->sts_reject2 == '1') {
			$status2 = '<div class="badge bg-red">Rejected</div>';
			$tgl_appre_2 = date('d F Y', strtotime($header[0]->sts_reject2_date));
		}
	}

	if ($header[0]->app_3 == '1') {
		$status3 = '<div class="badge bg-green">Approved</div>';
		$tgl_appre_3 = date('d F Y', strtotime($header[0]->app_3_date));
	} else {
		if ($header[0]->sts_reject3 == '1') {
			$status3 = '<div class="badge bg-red">Rejected</div>';
			$tgl_appre_3 = date('d F Y', strtotime($header[0]->sts_reject3_date));
		}
	}
}
// End Detail Status

$tanda 			= (!empty($code)) ? 'Update' : 'Insert';
$disabled		= (!empty($approve)) ? 'disabled' : '';
$disabled2		= ($approve == 'view') ? 'disabled' : '';
$disabled3		= ($approve == 'view') ? 'readonly' : '';

// $dataso = $this->db->query("select a.project, b.so_number from table_sales_order a LEFT JOIN so_bf_header b ON a.no_ipp=b.no_ipp order by so_number")->result();
?>
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.2/css/dataTables.dataTables.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.css" integrity="sha512-0nkKORjFgcyxv3HbE4rzFUlENUMNqic/EzDIeYCgsKa/nwqr2B91Vu/tNAu4Q0cBuG4Xe/D1f/freEci/7GDRA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<form action="#" method="POST" id="form_ct" enctype="multipart/form-data" autocomplete='off'>
	<input type="hidden" name="id" value="<?= $id; ?>">
	<input type="hidden" name="tanda" value="<?= $tanda; ?>">
	<input type="hidden" id="approve" name="approve" value="<?= $approve; ?>">
	<input type="hidden" name="tingkat_approval" id="tingkat_approval" value="<?= $tingkat_approval ?>">
	<div class="box box-primary">
		<div class="box-header">
			<h3 class="box-title"><?php echo $title; ?></h3>
			<div class="box-tool pull-right">

			</div>
		</div>
		<!-- /.box-header -->

		<div class="box-body">
			<div class='form-group row'>
				<label class='label-control col-sm-2'><b>Department <span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<select name='id_dept' id='id_dept' class='form-control input-md chosen_select' <?= $disabled; ?>>
						<option value='0'>Select An Department</option>
						<?php
						// foreach (get_list_dept() as $val => $valx) {
						// 	$dept = ($valx['id'] == $id_dept) ? 'selected' : '';
						// 	echo "<option value='" . $valx['id'] . "' " . $dept . ">" . $valx['nm_dept'] . "</option>";
						// }


						foreach ($list_departement as $departement) {
							$selected = '';
							if ($departement->id == $id_dept) {
								$selected = 'selected';
							}
							echo "<option value='" . $departement->id . "' " . $selected . ">" . strtoupper($departement->nama) . "</option>";
						}
						?>
					</select>
				</div>
				<label class='label-control col-sm-2'><b>Project Name</b></label>
				<div class='col-sm-4'>
					<?php
					echo form_input(array('id' => 'project_name', 'name' => 'project_name', 'class' => 'form-control input-md', 'placeholder' => 'Project Name'), $project_name);
					?>
				</div>
			</div>

			<div class='form-group row'>
				<label class='label-control col-sm-2'><b>Upload Document</b></label>
				<div class='col-sm-4  text-right'>
					<input type='file' id='upload_spk' name='upload_spk' class='form-control input-md' placeholder='Upload Document'>
					<?php if (!empty($upload_spk)) { ?>
						<a href='<?= base_url('assets/pr/' . $upload_spk); ?>' target='_blank' title='Download' data-role='qtip'>Download</a>
					<?php } ?>
				</div>
				<label class='label-control col-sm-2'><b>COA <span class='text-red'>*</span></b> </label>
				<div class='col-sm-4'>
					<select name="coa" id="coa" class="form-control chosen_select" required>
						<option value="">- Select COA -</option>
						<?php
						foreach ($list_coa as $coa) :
							$selected = "";
							if ($coa['no_perkiraan'] == $pr_coa) {
								$selected = "selected";
							}
							echo '<option value="' . $coa['no_perkiraan'] . '" ' . $selected . '>' . $coa['no_perkiraan'] . ' - ' . $coa['nama'] . '</option>';
						endforeach;
						?>
					</select>
				</div>
			</div>
			<div class='form-group row'>
				<label class='label-control col-sm-2'><b>Tingkat PR</b></label>
				<div class='col-sm-4  text-right'>
					<select name="tingkat_pr" id="" class="form-control input-md">
						<option value="1" <?= ($tingkat_pr == '1') ? 'selected' : null ?>>Normal</option>
						<option value="2" <?= ($tingkat_pr == '2') ? 'selected' : null ?>>Urgent</option>
					</select>
				</div>
			</div>

			<div class="form-group row">
				<div class="col-md-8">
					<table class="table">
						<thead>
							<tr>
								<th class="text-center">Approval By</th>
								<th class="text-center">Status</th>
								<th class="text-center">Tgl Approve / Reject</th>
								<th class="text-center">Alasan Reject</th>
								<th class="text-center">Keterangan</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td class="text-center">Departement Head</td>
								<td class="text-center">
									<?= $status1 ?>
								</td>
								<td class="text-center">
									<?= $tgl_appre_1 ?>
								</td>
								<td>
									<input type="text" name="reject_reason1" id="" class="form-control" value="<?= $alasan_reject1 ?>" readonly>
								</td>
								<td>
									<input type="text" name="keterangan_1" id="" class="form-control" value="<?= $keterangan_1 ?>">
								</td>
							</tr>
							<tr>
								<td class="text-center">Cost Control</td>
								<td class="text-center">
									<?= $status2 ?>
								</td>
								<td class="text-center">
									<?= $tgl_appre_2 ?>
								</td>
								<td>
									<input type="text" name="reject_reason2" id="" class="form-control" value="<?= $alasan_reject2 ?>" readonly>
								</td>
								<td>
									<input type="text" name="keterangan_2" id="" class="form-control" value="<?= $keterangan_2 ?>">
								</td>
							</tr>
							<tr>
								<td class="text-center">Management</td>
								<td class="text-center">
									<?= $status3 ?>
								</td>
								<td class="text-center">
									<?= $tgl_appre_3 ?>
								</td>
								<td>
									<input type="text" name="reject_reason3" id="" class="form-control" value="<?= $alasan_reject3 ?>" readonly>
								</td>
								<td>
									<input type="text" name="keterangan_3" id="" class="form-control" value="<?= $keterangan_3 ?>">
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>

			<?php
			if ($approve == 'approve') {
			?>
				<div class='form-group row'>
					<label class='label-control col-sm-2'><b>Approve <span class='text-red'>*</span></b></label>
					<div class='col-sm-2'>
						<select name='sts_app' id='sts_app' class='form-control input-md'>
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
			?>
			<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
				<thead>
					<tr class='bg-blue'>
						<th class='text-center' style='width: 3%;'>#</th>
						<th class='text-center'>Nama Barang/Jasa</th>
						<th class='text-center' style='width: 13%;'>Spec/ Requirement</th>
						<th class='text-center' style='width: 7%;'>Qty</th>
						<th class='text-center' style='width: 8%;'>Satuan</th>
						<th class='text-center' style='width: 9%;'>Est Harga</th>
						<th class='text-center' style='width: 9%;'>Est Total Harga</th>
						<th class='text-center' style='width: 9%;'>Tanggal Dibutuhkan</th>
						<th class='text-center' style='width: 15%;'>Keterangan</th>
						<?php
						if (empty($approve)) {
						?>
							<th class='text-center' style='width: 8%;'>#</th>
						<?php } ?>
					</tr>
				</thead>
				<tbody>
					<?php
					$nomor = 0;
					if (!empty($detail)) {
						foreach ($detail as $val => $valx) {
							$nomor++;
							echo "<tr class='header_" . $nomor . "'>";
							echo "<td align='center'>" . $nomor . "<input type='hidden' name='detail[" . $nomor . "][id]' value='" . $valx['id'] . "'></td>";
							echo "<td align='left'><input type='text' " . $disabled3 . " name='detail[" . $nomor . "][nm_barang]' class='form-control input-md nm_barang_" . $nomor . "' value='" . strtoupper($valx['nm_barang']) . "'></td>";
							echo "<td align='left'><input type='text' " . $disabled3 . " name='detail[" . $nomor . "][spec]' class='form-control input-md spec_" . $nomor . "' value='" . strtoupper($valx['spec']) . "'></td>";
							echo "<td align='left'><input type='text' " . $disabled2 . " id='qty_" . $nomor . "' name='detail[" . $nomor . "][qty]' class='form-control input-md text-right autoNumeric2 sum_tot qty_" . $nomor . "' value='" . $valx['qty'] . "'></td>";
							echo "<td align='left'>
									<select name='detail[" . $nomor . "][satuan]' class='form-control wajib satuan_" . $nomor . "' " . $disabled2 . " required>";
							echo "<option value=''>Pilih</option>";
							foreach ($satuan as $key => $value) {
								$selected = ($value['id'] == $valx['satuan']) ? 'selected' : '';
								echo "<option value='" . $value['id'] . "' " . $selected . ">" . $value['code'] . "</option>";
							}
							echo "	</select>
									</td>";
							echo "<td align='left'><input type='text' " . $disabled2 . " id='harga_" . $nomor . "' name='detail[" . $nomor . "][harga]' class='form-control input-md text-right maskM sum_tot harga_" . $nomor . "' value='" . number_format($valx['harga']) . "' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''></td>";
							echo "<td align='left'><input type='text' " . $disabled2 . " id='total_harga_" . $nomor . "' name='detail[" . $nomor . "][total_harga]' class='form-control input-md text-right maskM jumlah_all total_harga_" . $nomor . "' value='" . number_format($valx['qty'] * $valx['harga']) . "' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero='' readonly></td>";
							echo "<td align='left'><input type='text' " . $disabled3 . " name='detail[" . $nomor . "][tanggal]' class='form-control input-md text-center datepicker tgl_dibutuhkan tanggal_" . $nomor . "' readonly value='" . strtoupper($valx['tanggal']) . "'></td>";
							echo "<td align='left'><input type='text' " . $disabled3 . " name='detail[" . $nomor . "][keterangan]' class='form-control input-md keterangan_" . $nomor . "' value='" . strtoupper($valx['keterangan']) . "'></td>";
							if (empty($approve)) {
								echo "<td align='center'><button type='button' class='btn btn-sm btn-warning edit_detail edit_detail_" . $nomor . "' data-id='" . $valx['id'] . "' data-nomor='" . $nomor . "' style='margin-right: 0.5em;'><i class='fa fa-pencil'></i>
								</button><button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button></td>";
							}
							echo "</tr>";
						}
					}
					if (empty($approve)) {
					?>
						<tr id='add_<?= $nomor; ?>'>
							<td align='center'></td>
							<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-warning addPart' title='Add Barang'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Barang</button></td>
							<td align='center' colspan='8'></td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
			<div class='box-footer'>
				<?php
				echo form_button(array('type' => 'button', 'class' => 'btn btn-md btn-danger', 'style' => 'float:right; margin-left:5px;', 'value' => 'back', 'content' => 'Back', 'id' => 'back'));
				if ($approve <> 'view') {
					echo form_button(array('type' => 'button', 'class' => 'btn btn-md btn-primary', 'style' => 'float:right;', 'value' => 'save', 'content' => 'Save', 'id' => 'save')) . ' ';
				}
				?>
			</div>
		</div>
		<!-- /.box-body -->
	</div>
	<!-- /.box -->

</form>
<style type="text/css">
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

	.datepicker {
		cursor: pointer;
	}
</style>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script src="https://cdn.datatables.net/2.0.2/js/dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js" integrity="sha512-rMGGF4wg1R73ehtnxXBt5mbUfN9JUJwbk21KMlnLZDJh7BkPmeovBuddZCENJddHYYMkCh9hPFnPmS9sspki8g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
	$(document).ready(function() {
		$('.maskM').autoNumeric();
		$('.autoNumeric2').autoNumeric('init', {
			mDec: '2',
			aPad: false
		});
		$('.chosen_select').chosen();
		$('.datepicker').datepicker({
			dateFormat: 'yy-mm-dd',
			//minDate: 0
		});
		$('.tnd_reason').hide();
	});
	$('#no_so').on('change', function(evt, params) {
		var data = $("select#no_so").find(":selected").data("project");
		$("#project_name").val(data);
	});
	$(document).on('change', '#sts_app', function(e) {
		var sts = $(this).val();
		if (sts == 'D') {
			$('.tnd_reason').show();
		} else {
			$('.tnd_reason').hide();
		}
	});

	$(document).on('click', '#back', function(e) {
		var app = $("#approve").val();
		var tingkat_approval = $('#tingkat_approval').val();
		var tanda = "";
		if (app == 'approve') {
			if (tingkat_approval == '1') {
				var tanda = 'approval_head';
			}
			if (tingkat_approval == '2') {
				var tanda = 'approval_cost_control';
			}
			if (tingkat_approval == '3') {
				var tanda = 'approval_management';
			}
		}
		window.location.href = base_url + active_controller + tanda;
	});

	$(document).on('click', '.addPart', function() {
		// loading_spinner();
		var get_id = $(this).parent().parent().attr('id');
		// console.log(get_id);
		var split_id = get_id.split('_');
		var id = parseInt(split_id[1]) + 1;
		var id_bef = split_id[1];

		$.ajax({
			url: base_url + active_controller + '/get_add/' + id,
			cache: false,
			type: "POST",
			dataType: "json",
			success: function(data) {
				$("#add_" + id_bef).before(data.header);
				$("#add_" + id_bef).remove();
				$('.chosen_select').chosen({
					width: '100%'
				});
				$('.maskM').autoNumeric();
				$('.datepicker').datepicker({
					dateFormat: 'yy-mm-dd',
					//minDate: 0
				});
				$('.chosen_select').chosen();
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

	//delete part
	$(document).on('click', '.delPart', function() {
		var get_id = $(this).parent().parent().attr('class');
		$("." + get_id).remove();
	});

	$(document).on('keyup', '.sum_tot', function() {
		var id = $(this).attr('id');
		var det_id = id.split('_');
		var a = det_id[1];
		sum_total(a);
	});


	//SAVE
	$(document).on('click', '#save', function(e) {
		e.preventDefault();
		$('#save').prop('disabled', true);

		var tingkat_approval = $('#tingkat_approval').val();
		var id_dept = $('#id_dept').val();
		var coa = $('#coa').val();
		var sts_app = $('#sts_app').val();
		// alert('Tahan'); return false;
		if (id_dept == '0') {
			swal({
				title: "Error Message!",
				text: 'Department name empty, select first ...',
				type: "warning"
			});

			$('#save').prop('disabled', false);
			return false;
		}
		if (coa == '0' || coa == '') {
			swal({
				title: "Error Message!",
				text: 'COA is empty, select first ...',
				type: "warning"
			});

			$('#save').prop('disabled', false);
			return false;
		}


		var app = $("#approve").val();
		var tanda = "";
		if (app == 'approve') {
			if (sts_app == '0') {
				swal({
					title: "Error Message!",
					text: 'Status Approve empty, select first ...',
					type: "warning"
				});

				$('#save').prop('disabled', false);
				return false;
			}
		}
		let wajib
		let FALIDASIwajib = true
		$(".wajib").each(function() {
			satuan = $(this).val()
			// console.log(tgl_butuh)
			// console.log(typeof(tgl_butuh))
			if (satuan == '' || satuan == '0') {
				FALIDASIwajib = false
				return false;
			}
		});
		if (FALIDASIwajib === false) {
			swal({
				title: "Error Message!",
				text: 'Satuan wajib diisi !',
				type: "warning"
			});

			$('#save').prop('disabled', false);
			return false;
		}
		let tgl_butuh
		let FALIDASI = true
		$(".tgl_dibutuhkan").each(function() {
			tgl_butuh = $(this).val()
			// console.log(tgl_butuh)
			// console.log(typeof(tgl_butuh))
			if (tgl_butuh == '' || tgl_butuh == '0000-00-00') {
				FALIDASI = false
				return false;
			}
		});
		if (FALIDASI === false) {
			swal({
				title: "Error Message!",
				text: 'Tgl dibutuhkan wajib diisi !',
				type: "warning"
			});

			$('#save').prop('disabled', false);
			return false;
		}

		$('#save').prop('disabled', true);

		swal({
				title: "Are you sure?",
				text: "Save this data ?",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-danger",
				confirmButtonText: "Yes, Process it!",
				cancelButtonText: "No, cancel process!",
				closeOnConfirm: true,
				closeOnCancel: false
			},
			function(isConfirm) {
				if (isConfirm) {
					// loading_spinner();
					var formData = new FormData($('#form_ct')[0]);
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
									timer: 7000,
									showCancelButton: false,
									showConfirmButton: false,
									allowOutsideClick: false
								});
								var return_link = '';
								if (tingkat_approval == '1') {
									return_link = 'approval_head';
								}
								if (tingkat_approval == '2') {
									return_link = 'approval_cost_control';
								}
								if (tingkat_approval == '3') {
									return_link = 'approval_management';
								}
								window.location.href = base_url + active_controller + return_link;
							} else if (data.status == 0) {
								swal({
									title: "Save Failed!",
									text: data.pesan,
									type: "warning",
									timer: 3000,
									showCancelButton: false,
									showConfirmButton: false,
									allowOutsideClick: false
								});
								$('#save').prop('disabled', false);
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

	$(document).on('click', '.edit_detail', function() {
		var id = $(this).data('id');
		var nomor = $(this).data('nomor');

		var nm_barang = $('.nm_barang_' + nomor).val();
		var spec = $('.spec_' + nomor).val();
		var qty = $('.qty_' + nomor).val();
		var satuan = $('.satuan_' + nomor).val();
		var harga = $('.harga_' + nomor).val();
		var total_harga = $('.total_harga_' + nomor).val();
		var tanggal = $('.tanggal_' + nomor).val();
		var keterangan = $('.keterangan_' + nomor).val();

		if (qty == '' || qty == null) {
			qty = 0;
		} else {
			qty = qty.split(',').join();
			qty = parseFloat(qty);
		}

		if (harga == '' || harga == null) {
			harga = 0;
		} else {
			harga = harga.split(',').join();
			harga = parseFloat(harga);
		}

		if (total_harga == '' || total_harga == null) {
			total_harga = 0;
		} else {
			total_harga = total_harga.split(',').join();
			total_harga = parseFloat(total_harga);
		}

		$.ajax({
			type: 'POST',
			url: siteurl + active_controller + '/edit_detail',
			data: {
				'id': id,
				'nm_barang': nm_barang,
				'spec': spec,
				'qty': qty,
				'satuan': satuan,
				'harga': harga,
				'total_harga': total_harga,
				'tanggal': tanggal,
				'keterangan': keterangan
			},
			cache: false,
			dataType: 'json',
			beforeSend: function(result) {
				$('.edit_detail_' + nomor).html('<i class="fa fa-spin fa-spinner"></i>');
				$('.edit_detail_' + nomor).prop('disabled', true);
			},
			success: function(result) {
				if (result.status == 1) {
					swal({
						title: 'Success !',
						text: 'Success, item data has been updated !',
						type: 'success'
					});
				} else {
					swal({
						title: 'Failed !',
						text: 'Failed, item data has not been updated !',
						type: 'error'
					});
				}
				location.reload();
			},
			error: function(result) {
				swal({
					title: 'Failed !',
					text: 'Failed, item data has not been updated !',
					type: 'error'
				});
				location.reload();
			}
		});
	});

	function sum_total(a) {
		var qty = getNum($('#qty_' + a).val().split(",").join(""));
		var harga = getNum($('#harga_' + a).val().split(",").join(""));

		var total = qty * harga;
		// console.log(total);
		$('#total_harga_' + a).val(number_format(total));

		var SUM = 0;
		$(".jumlah_all").each(function() {
			SUM += Number(getNum($(this).val().split(",").join("")));
		});

		$('#budget').val(number_format(SUM));
	}

	function number_format(number, decimals, dec_point, thousands_sep) {
		// Strip all characters but numerical ones.
		number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
		var n = !isFinite(+number) ? 0 : +number,
			prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
			sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
			dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
			s = '',
			toFixedFix = function(n, prec) {
				var k = Math.pow(10, prec);
				return '' + Math.round(n * k) / k;
			};
		// Fix for IE parseFloat(0.55).toFixed(0) = 0;
		s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
		if (s[0].length > 3) {
			s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
		}
		if ((s[1] || '').length < prec) {
			s[1] = s[1] || '';
			s[1] += new Array(prec - s[1].length + 1).join('0');
		}
		return s.join(dec);
	}
</script>