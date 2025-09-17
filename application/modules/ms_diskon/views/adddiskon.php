<?php
$ENABLE_ADD     = has_permission('Master_Discount.Add');
$ENABLE_MANAGE  = has_permission('Master_Discount.Manage');
$ENABLE_VIEW    = has_permission('Master_Discount.View');
$ENABLE_DELETE  = has_permission('Master_Discount.Delete');
$tanggal = date('Y-m-d');
?>

<div class="box box-primary">
	<div class="box-body">
		<form id="data-form" method="post">
			<?= (isset($results)) ? '<input type="hidden" name="id_diskon" value="' . $results['data_diskon']->id . '">' : null ?>
			<div class="col-sm-12">
				<div class="input_fields_wrap2">
					<div class="row">
						<center><label for="customer">
								<h3>Discount</h3>
							</label></center>
						<div class="col-sm-12">
							<div class="col-sm-12">
								<?php if (!isset($results)) { ?>
									<div class="form-group row">
										<button type='button' class='btn btn-sm btn-success' title='Ambil' id='tbh_ata' data-role='qtip' onClick='GetProduk();'><i class='fa fa-plus'></i>Add</button>
									</div>
								<?php } ?>
								<div class="form-group row">
									<table class='table table-bordered table-striped'>
										<thead>
											<tr class='bg-blue'>
												<th width='3%'>No</th>
												<th>Tingkatan</th>
												<th>Keterangan</th>
												<th>Discount Awal</th>
												<th>Discount Akhir</th>
												<th>Approve By</th>
												<th width='5%'>Aksi</th>
											</tr>
										</thead>
										<tbody id="list_spk">
											<?php
											if (isset($results)) {
												echo '
														<tr>
															<td class="text-center">1</td>
															<td>
																<input type="text" class="form-control form-control-sm" name="tingkatan" value="' . $results['data_diskon']->tingkatan . '">
															</td>
															<td>
																<input type="text" class="form-control form-control-sm" name="keterangan" value="' . $results['data_diskon']->keterangan . '">
															</td>
															<td>
																<input type="text" class="form-control form-control-sm" name="diskon_awal" value="' . $results['data_diskon']->diskon_awal . '">
															</td>
															<td>
																<input type="text" class="form-control form-control-sm" name="diskon_akhir" value="' . $results['data_diskon']->diskon_akhir . '">
															</td>
															<td class="text-center" style="width: 150px;">
																<select name="approved_by" id="" class="form-control form-control-sm select">
																	<option value="">- Approve By -</option>
																';

												foreach ($results['list_user'] as $user) {
													$selected = '';
													if ($results['data_diskon']->approved_by == $user->id_user) {
														$selected = 'selected';
													}
													echo '<option value="' . $user->id_user . '" ' . $selected . '>' . $user->nm_lengkap . '</option>';
												}

												echo '
																</select>
															</td>
															<td></td>
														</tr>
													';
											}
											?>
										</tbody>
									</table>
								</div>
							</div>
						</div>

						<center>
							<button type="submit" class="btn btn-success btn-sm" name="save" id="simpan-com"><i class="fa fa-save"></i>Simpan</button>
							<a class="btn btn-danger btn-sm" href="<?= base_url('/ms_diskon/') ?>" title="Edit">Kembali</a>
						</center>
		</form>
	</div>
</div>




<script type="text/javascript">
	//$('#input-kendaraan').hide();
	var base_url = '<?php echo base_url(); ?>';
	var active_controller = '<?php echo ($this->uri->segment(1)); ?>';
	$(document).ready(function() {
		var max_fields2 = 10; //maximum input boxes allowed
		var wrapper2 = $(".input_fields_wrap2"); //Fields wrapper
		var add_button2 = $(".add_field_button2"); //Add button ID		

		$('.select').select2({
			width: '100%'
		});
		$(document).on('submit', '#data-form', function(e) {
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
						// var formData = new FormData($('#data-form')[0]);
						var formData = $('#data-form').serialize();
						var baseurl = siteurl + active_controller + '/SaveNewDiskon';
						$.ajax({
							url: baseurl,
							type: "POST",
							data: formData,
							cache: false,
							dataType: 'json',
							success: function(data) {
								console.log(formData);
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

	function get_customer() {
		var id_customer = $("#id_customer").val();
		$.ajax({
			type: "GET",
			url: siteurl + 'wt_penawaran/getemail',
			data: "id_customer=" + id_customer,
			success: function(html) {
				$("#email_slot").html(html);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'wt_penawaran/getpic',
			data: "id_customer=" + id_customer,
			success: function(html) {
				$("#pic_slot").html(html);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'wt_penawaran/getsales',
			data: "id_customer=" + id_customer,
			success: function(html) {
				$("#sales_slot").html(html);
			}
		});
	}

	function DelItem(id) {
		$('#data_barang #tr_' + id).remove();

	}


	function GetProduk() {
		var jumlah = $('#list_spk').find('tr').length;
		$.ajax({
			type: "GET",
			url: siteurl + 'ms_diskon/GetProduk',
			data: "jumlah=" + jumlah,
			success: function(html) {
				$("#list_spk").append(html);
				$('.select').select2({
					width: '100%'
				});
			}
		});
	}

	function HapusItem(id) {
		$('#list_spk #tr_' + id).remove();
		changeChecked();
	}

	function CariDetail(id) {

		var id_material = $('#used_no_surat_' + id).val();

		$.ajax({
			type: "GET",
			url: siteurl + 'wt_penawaran/CariNamaProduk',
			data: "id_category3=" + id_material + "&id=" + id,
			success: function(html) {
				$('#nama_produk_' + id).html(html);
			}
		});

	}
</script>