<?php
$tanggal = date('Y-m-d');
foreach ($results['head'] as $head) {
}
?>

<div class="box box-primary">
	<div class="box-body">
		<form id="data-form" method="post">
			<div class="col-sm-12">
				<div class="input_fields_wrap2">
					<div class="row">
						<center><label for="customer">
								<h3>Purchase Request</h3>
							</label></center>
						<div class="col-sm-12">
							<div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-4">
										<label for="customer">NO.PR</label>
									</div>
									<div class="col-md-8" hidden>
										<input type="text" class="form-control" id="no_pr" value="<?= $head->no_pr  ?>" required name="no_pr" readonly placeholder="ID PR">
									</div>
									<div class="col-md-8">
										<input type="text" class="form-control" id="no_surat" value="<?= $head->no_surat  ?>" required name="no_surat" readonly placeholder="No.PR">
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-12">
							<div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-4">
										<label for="customer">Tanggal PR</label>
									</div>
									<div class="col-md-8">
										<input type="text" class="form-control datepicker" id="tanggal" value="<?= $head->tanggal  ?>" onkeyup required name="tanggal" readonly>
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-12">
							<div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-4">
										<label for="id_customer">Requestor</label>
									</div>
									<div class="col-md-8">
										<input type="text" class="form-control" value="<?= $head->requestor  ?>" id="requestor" readonly name="requestor">
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-12">
							<div class="form-group row">
								<button type='button' class='btn btn-sm btn-success' title='Ambil' id='tbh_ata' data-role='qtip' onClick='addmaterial();'><i class='fa fa-plus'></i>Add</button>

							</div>
							<div class="form-group row">
								<table class='table table-bordered table-striped'>
									<thead>
										<tr class='bg-blue'>
											<th width='30%'>Produk</th>
											<th width='8%'>Kode Produk</th>
											<th width='8%'>Qty (Unit)</th>
											<th width='20%'>Supplier</th>
											<th width='10%'>Tanggal Dibutuhkan</th>
											<th width='30%'>Keterangan</th>
											<th width='5%'>Aksi</th>
										</tr>
									</thead>
									<tbody id="data_request">
										<?php
										$loop = 0;
										foreach ($results['detail'] as $detail) {
											$loop++;
											$material = $this->db->query("SELECT a.* FROM new_inventory_4 as a WHERE a.category = 'material' AND a.deleted_by IS NULL")->result();
											$suppliers = $this->db->query("SELECT a.* FROM new_supplier as a WHERE a.deleted_by IS NULL")->result();
											echo "
		<tr id='tr_$loop'>
		<td><select class='form-control select2' id='dt_idmaterial_$loop' name='dt[$loop][idmaterial]'	onchange ='CariProperties($loop)'>
		<option value=''>--Pilih--</option>";
											foreach ($material as $material) {

												$selected = ($detail->idmaterial == $material->code_lv4) ? 'selected' : '';
												echo "<option value='$material->code_lv4' $selected>$material->nama</option>";
											};
											echo "</select></td>
		<td id='kodeproduk_" . $loop . "'><input value='" . $detail->kode_barang . "'   readonly type='text' class='form-control' id='dt_kodeproduk_$loop' required name='dt[$loop][kodeproduk]' ></td>
		<td id='bentuk_" . $loop . "' hidden><input readonly value='" . $detail->bentuk . "' type='text' class='form-control' id='dt_bentuk_$loop' required name='dt[$loop][bentuk]' ></td>
		<td id='idbentuk_" . $loop . "' hidden><input value='" . $detail->id_bentuk . "'   readonly type='number' class='form-control' id='dt_idbentuk_$loop' required name='dt[$loop][idbentuk]' ></td>
		<td hidden>
		<select class='form-control select2' id='dt_idameter_$loop' name='dt[$loop][idameter]'>";
											if ($detail->id_bentuk == 200) {
												echo "<option value=''>Pilih</option>
		<option value='200'>200</option>
		<option value='250' selected>250</option>
		<option value='300'>300</option>
		<option value='400'>400</option>
		<option value='500'>500</option>";
											} elseif ($detail->id_bentuk == 250) {
												echo "<option value=''>Pilih</option>
		<option value='200'>200</option>
		<option value='250' selected>250</option>
		<option value='300'>300</option>
		<option value='400'>400</option>
		<option value='500'>500</option>";
											} elseif ($detail->id_bentuk == 300) {
												echo "<option value=''>Pilih</option>
		<option value='200'>200</option>
		<option value='250'>250</option>
		<option value='300' selected>300</option>
		<option value='400'>400</option>
		<option value='500'>500</option>";
											} elseif ($detail->id_bentuk == 400) {
												echo "<option value=''>Pilih</option>
		<option value='200'>200</option>
		<option value='250'>250</option>
		<option value='300'>300</option>
		<option value='400' selected>400</option>
		<option value='500'>500</option>";
											} elseif ($detail->id_bentuk == 500) {
												echo "<option value=''>Pilih</option>
		<option value='200'>200</option>
		<option value='250'>250</option>
		<option value='300'>300</option>
		<option value='400'>400</option>
		<option value='500' selected>500</option>";
											} else {
												echo "<option value=''>Pilih</option>
		<option value='200'>200</option>
		<option value='250'>250</option>
		<option value='300'>300</option>
		<option value='400'>400</option>
		<option value='500'>500</option>";
											}
											echo "</select>
		</td>
		<td hidden><input type='text' class='form-control autoNumeric text-right' value='" . $detail->odameter . "' id='dt_odameter_$loop' 			required name='dt[$loop][odameter]' 	></td>
		<td><input type='number' class='form-control' value='" . $detail->qty . "'  id='dt_qty_$loop' 			required name='dt[$loop][qty]' 		onkeyup='HitungTweight(" . $loop . ")'></td>
		<td hidden><input type='number' class='form-control' value='" . $detail->weight . "' id='dt_weight_$loop' 			required name='dt[$loop][weight]' 	onkeyup='HitungTweight(" . $loop . ")'></td>
		<td hidden id='HasilTwight_" . $loop . "'><input value='" . $detail->totalweight . "' type='text' class='form-control autoNumeric text-right' id='dt_totalweight_$loop' 	required name='dt[$loop][totalweight]' ></td>
		<td hidden><input type='text' class='form-control autoNumeric text-right' id='dt_width_$loop' value='" . $detail->width . "'	required name='dt[$loop][width]' ></td>
		<td hidden><input type='text' class='form-control autoNumeric text-right' id='dt_length_$loop' value='" . $detail->length . "'	required name='dt[$loop][length]' ></td>
		<td id='supplier_" . $loop . "'><select class='form-control select2' id='dt_suplier_$loop'  name='dt[$loop][suplier]'>
		<option value=''>--Pilih--</option>";
											foreach ($suppliers as $suppliers) {
												$selectedsup = ($suppliers->id == $detail->suplier) ? 'selected' : '';
												echo "<option value='" . $suppliers->id . "' " . $selectedsup . ">" . $suppliers->nama . "</option>";
											};
											echo "</select></td>
		<td><input type='text' class='form-control datepicker' readonly id='dt_tanggal_$loop' value='" . $detail->tanggal . "'	required name='dt[$loop][tanggal]'></td>
		<td><input type='text' class='form-control' id='dt_keterangan_$loop' value='" . $detail->keterangan . "'	required name='dt[$loop][keterangan]' 	></td>
		<td><button type='button' class='btn btn-sm btn-danger' title='Hapus Data' data-role='qtip' onClick='return HapusItem($loop);'><i class='fa fa-close'></i></button></td>
		</tr>";
										}
										?>
									</tbody>
								</table>
							</div>
						</div>
						<center>
							<button type="submit" class="btn btn-success btn-sm" name="save" id="simpan-com"><i class="fa fa-save"></i>Simpan</button>
						</center>
					</div>
				</div>
		</form>
	</div>
</div>


<style>
	.select2 {
		width: 100% !important;
	}

	.datepicker {
		cursor: pointer;
	}
</style>

<script src="<?= base_url('assets/js/autoNumeric.js'); ?>"></script>
<script type="text/javascript">
	//$('#input-kendaraan').hide();
	var base_url = '<?php echo base_url(); ?>';
	var active_controller = '<?php echo ($this->uri->segment(1)); ?>';
	$(document).ready(function() {
		var max_fields2 = 10; //maximum input boxes allowed
		var wrapper2 = $(".input_fields_wrap2"); //Fields wrapper
		var add_button2 = $(".add_field_button2"); //Add button ID

		$('.select2').select2();
		$('.autoNumeric').autoNumeric();
		$('.datepicker').datepicker({
			dateFormat: 'yy-mm-dd',
			changeMonth: true,
			changeYear: true,
		});

		$('#simpan-com').click(function(e) {
			e.preventDefault();
			var deskripsi = $('#deskripsi').val();
			var image = $('#image').val();
			var idtype = $('#inventory_1').val();

			var data, xhr;
			swal({
					title: "Are you sure?",
					text: "You will not be able to process again this data!",
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
						var formData = new FormData($('#data-form')[0]);
						var baseurl = siteurl + 'purchase_request/SaveEdit';
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
									window.location.href = base_url + active_controller;
								} else {

									if (data.status == 2) {
										swal({
											title: "Save Failed!",
											text: data.pesan,
											type: "warning",
											timer: 7000,
											showCancelButton: false,
											showConfirmButton: false,
											allowOutsideClick: false
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
							}
						});
					} else {
						swal("Cancelled", "Data can be process again :)", "error");
						return false;
					}
				});
		});

	});

	function addmaterial() {
		var jumlah = $('#data_request').find('tr').length;
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_request/AddMaterial',
			data: "jumlah=" + jumlah,
			success: function(html) {
				$("#data_request").append(html);
				$('.select2').select2();
				$('.autoNumeric').autoNumeric();
				$('.datepicker').datepicker({
					dateFormat: 'yy-mm-dd',
					changeMonth: true,
					changeYear: true,
				});
			}
		});
	}

	function HitungTweight(id) {
		var dt_qty = $("#dt_qty_" + id).val();
		var dt_weight = $("#dt_weight_" + id).val();
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_request/HitungTwight',
			data: "dt_weight=" + dt_weight + "&dt_qty=" + dt_qty + "&id=" + id,
			success: function(html) {
				$("#HasilTwight_" + id).html(html);
			}
		});
	}

	function CariProperties(id) {
		var idmaterial = $("#dt_idmaterial_" + id).val();

		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_request/CariKodeproduk',
			data: "idmaterial=" + idmaterial + "&id=" + id,
			success: function(html) {
				$("#kodeproduk_" + id).html(html);
			}
		});
	}

	function HapusItem(id) {
		$('#data_request #tr_' + id).remove();

	}
</script>