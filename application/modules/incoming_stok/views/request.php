<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<div class="box box-primary">
	<div class="box-body">
		<form id="data-form" method="post" autocomplete="off"><br>
			<div class="form-group row">
				<div class="col-md-2 text-bold">Supplier<span class='text-danger'>*</span></div>
				<div class="col-md-4">
					<select name='supplier' id='supplier' class='form-control input-sm pilih_supplier chosen-select'>

						<?php
						if (!empty($listSupplier)) {
							echo "<option value=''>- Select Supplier -</option>";
							foreach ($listSupplier as $item) {
								echo "<option value='" . $item->kode_supplier . "'>" . $item->nama . "</option>";
							}
						} else {
							echo "<option value='0'>Supplier Is Empty</option>";
						}
						?>
					</select>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2 text-bold">Nomor PO <span class='text-danger'>*</span></div>
				<div class="col-md-4">
					<table class="table table-bordered">
						<thead>
							<tr>
								<th class="text-center">No. PO</th>
								<th class="text-center">No. PR</th>
								<th class="text-center">Action</th>
							</tr>
						</thead>
						<tbody class="list_no_po"></tbody>
					</table>
				</div>
				<div class="col-md-2 text-bold">Date Transaksi</div>
				<div class="col-md-4">
					<input type="text" name='tanggal' id='tanggal' class='form-control input-sm datepicker' readonly value='<?= date('Y-m-d'); ?>'>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2 text-bold">Warehouse <span class='text-danger'>*</span></div>
				<div class="col-md-4">
					<select name='id_gudang' id='id_gudang' class='form-control input-sm chosen-select'>
						<option value='0'>Select Warehouse</option>
						<?php
						foreach ($listGudang as $key => $value) {
							echo "<option value='" . $value['id'] . "'>" . $value['nm_gudang'] . "</option>";
						}
						?>
					</select>
				</div>
				<div class="col-md-2 text-bold">PIC <span class='text-danger'>*</span></div>
				<div class="col-md-4">
					<input type="text" name='pic' id='pic' class='form-control input-sm'>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2 text-bold">Keterangan</div>
				<div class="col-md-4">
					<textarea name="keterangan" id="keterangan" class='form-control input-sm' rows="3"></textarea>
				</div>
				<div class="col-md-2 text-bold"></div>
				<div class="col-md-4"></div>
			</div>
			<hr>
			<h4>Daftar Incoming</h4>
			<div class="form-group row">
				<div class="col-md-12">
					<table class="table table-striped table-bordered table-hover table-condensed" width="100%">
						<thead>
							<tr class='bg-blue'>
								<th class="text-center" width='4%'>#</th>
								<th class="text-center" width='5%'>ID</th>
								<th class="text-center" width='10%'>Kode Barang</th>
								<th class="text-center">Nama Barang</th>
								<th class="text-center" width='8%'>Qty PO (Pack)</th>
								<th class="text-center" width='8%'>Unit Pack</th>
								<th class="text-center" width='8%'>Qty IN</th>
								<th class="text-center" width='8%'>Qty Outsanding</th>
								<th class="text-center" width='8%'>Qty Diterima</th>
								<th class="text-center" width='15%'>Keterangan</th>
							</tr>
						</thead>
						<tbody id='body_req'>
							<tr>
								<td colspan='8'>Empty data incoming.</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-6">
					<button type="button" class="btn btn-primary" name="save" id="save">Process</button>
					<button type="button" class="btn btn-danger" style='margin-left:5px;' name="back" id="back">Back</button>
				</div>
			</div>
		</form>
	</div>
</div>


<script src="<?= base_url('assets/js/jquery.maskMoney.js') ?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<style>
	.datepicker {
		cursor: pointer;
	}

	th,
	td {
		padding: 5px;
	}
</style>

<script type="text/javascript">
	//$('#input-kendaraan').hide();
	var base_url = '<?php echo base_url(); ?>';
	var active_controller = '<?php echo ($this->uri->segment(1)); ?>';

	$(document).ready(function() {
		$('.chosen-select').select2();
		$('.datepicker').datepicker();
		$('.autoNumeric4').autoNumeric('init', {
			mDec: '4',
			aPad: false
		})

		//back
		$(document).on('click', '#back', function() {
			window.location.href = base_url + active_controller
		});

		$(document).on('keyup', '.qty_in', function() {
			let qty_in = getNum($(this).val().split(',').join(''))
			let qty_max = getNum($(this).parent().parent().find('.qty_max').text().split(',').join(''))
			// console.log(qty_max)
			if (qty_in > qty_max) {
				$(this).val(qty_max)
			}
		});

		$('#save').click(function(e) {
			e.preventDefault();

			var no_po = [];
			$('.check_po').each(function() {
				var val = $(this).val();
				if ($(this).is(':checked')) {
					no_po.push(val);
				}
			});

			var id_gudang = $('#id_gudang').val();
			var pic = $('#pic').val();

			if (no_po.length < 1) {
				swal({
					title: "Error Message!",
					text: 'Nomor PO belum dipilih ...',
					type: "warning"
				});
				return false;
			}
			if (id_gudang == '0') {
				swal({
					title: "Error Message!",
					text: 'Warehouse belum dipilih ...',
					type: "warning"
				});
				return false;
			}
			if (pic == '') {
				swal({
					title: "Error Message!",
					text: 'PIC belum diinput ...',
					type: "warning"
				});
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
					closeOnConfirm: true,
					closeOnCancel: false
				},
				function(isConfirm) {
					if (isConfirm) {
						var formData = new FormData($('#data-form')[0]);
						var baseurl = siteurl + active_controller + '/request_stok';
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
									window.location.href = base_url + active_controller
								} else {

									if (data.status == 2) {
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
											timer: 7000
										});
									}

								}
							},
							error: function() {

								swal({
									title: "Error Message !",
									text: 'An Error Occured During Process. Please try again..',
									type: "warning",
									timer: 7000
								});
							}
						});
					} else {
						swal("Cancelled", "Data can be process again :)", "error");
						return false;
					}
				});
		});

		$(document).on('change', '.check_po, #id_gudang', function() {
			var no_po = [];
			$('.check_po').each(function() {
				var val = $(this).val();
				if ($(this).is(':checked')) {
					no_po.push(val);
				}
			});
			let id_gudang = $('#id_gudang').val()

			if (no_po != '0' && id_gudang != '0') {
				$.ajax({
					type: 'POST',
					url: base_url + active_controller + '/detail_purchasing_order',
					data: {
						'no_po': no_po,
						'id_gudang': id_gudang,
					},
					dataType: 'json',
					success: function(data) {
						// console.log(data)
						$('#body_req').html(data.header)
						$('.autoNumeric4').autoNumeric('init', {
							mDec: '4',
							aPad: false
						})
					}
				})
			}
		});

		$(document).on('change', '.pilih_supplier', function() {
			var supplier = $(this).val();

			$.ajax({
				type: 'POST',
				url: siteurl + active_controller + '/pilih_supplier',
				data: {
					'kode_supplier': supplier
				},
				cache: false,
				success: function(result) {
					$('.list_no_po').html(result);
				},
				error: function(result) {
					swal({
						title: 'Error',
						text: 'Please try again later !',
						type: 'error'
					});
				}
			})
		});

		$(document).on('click', '.check_po', function() {
			var no_po = [];
			$('.check_po').each(function() {
				var val = $(this).val();
				if ($(this).is(':checked')) {
					no_po.push(val);
				}
			});
		});

	});

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