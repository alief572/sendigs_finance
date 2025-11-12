<?php
$tanggal = date('Y-m-d');
foreach ($results['header'] as $header) {
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
										<label for="customer">No. Request</label>
									</div>
									<div class="col-md-8">
										<?= $header->so_number ?>
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-12">
							<div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-4">
										<label for="id_customer">Customer</label>
									</div>
									<div class="col-md-8">
										<?= $header->nm_customer ?>
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-12">
							<div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-4">
										<label for="customer">Tanggal Dibutuhkan</label>
									</div>
									<div class="col-md-8">
										<?= date('d F Y', strtotime($header->tgl_dibutuhkan)) ?>
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-12">
							<div class="form-group row">

							</div>
							<div class="form-group row">
								<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
									<thead class='thead'>
										<tr class='bg-blue'>
											<th class='text-center th'>#</th>
											<th class='text-center th'>Material Name</th>
											<th class='text-center th'>Min Stock</th>
											<th class='text-center th'>Max Stock</th>
											<th class='text-center th'>Min Order</th>
											<th class='text-center th'>Qty PR</th>
											<th class='text-center th'>Qty Rev</th>
											<th class='text-center th'>#</th>
										</tr>
									</thead>
									<tbody>
										<?php
										foreach ($results['detail'] as $key => $value) {
											$key++;
											$nm_material 	= (!empty($GET_LEVEL4[$value['id_material']]['nama'])) ? $GET_LEVEL4[$value['id_material']]['nama'] : '';
											$stock_free 	= $value['stock_free'];
											$use_stock 		= $value['use_stock'];
											$sisa_free 		= $stock_free - $use_stock;
											$propose 		= $value['propose_purchase'];

											echo "<tr>";
											echo "<td class='text-center'>" . $key . "</td>";
											echo "	<td class='text-left'>" . $value['nm_product'] . "
										<input type='hidden' name='detail[" . $key . "][id]' value='" . $value['id'] . "'>
										</td>";
											echo "<td class='text-right min_stok'>" . number_format($value['min_stok'], 2) . "</td>";
											echo "<td class='text-right max_stok'>" . number_format($value['max_stok'], 2) . "</td>";
											echo "<td class='text-right min_order'>" . number_format(0, 2) . "</td>";
											echo "<td class='text-right'>" . number_format($propose, 2) . "</td>";
											echo "<td class='text-center'>" . number_format($value['propose_rev'], 2) . "</td>";
											echo "<td class='text-center'></td>";

											echo "</tr>";
										}
										?>
									</tbody>
								</table>
							</div>
						</div>

					</div>
				</div>
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
						var baseurl = siteurl + 'purchase_request/SaveNew';
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
			}
		});
	}

	function CariProperties(id) {
		var idmaterial = $("#dt_idmaterial_" + id).val();
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_request/CariBentuk',
			data: "idmaterial=" + idmaterial + "&id=" + id,
			success: function(html) {
				$("#bentuk_" + id).html(html);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_request/CariIdBentuk',
			data: "idmaterial=" + idmaterial + "&id=" + id,
			success: function(html) {
				$("#idbentuk_" + id).html(html);
			}
		});
		$.ajax({
			type: "GET",
			url: siteurl + 'purchase_request/CariSupplier',
			data: "idmaterial=" + idmaterial + "&id=" + id,
			success: function(html) {
				$("#supplier_" + id).html(html);
			}
		});
	}

	function HapusItem(id) {
		$('#data_request #tr_' + id).remove();

	}
</script>