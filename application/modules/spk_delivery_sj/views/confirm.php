<div class="box box-primary">
	<div class="box-body">
		<form id="data-form" method="post" autocomplete="off"><br>
			<div class="form-group row">
				<div class="col-md-12">
					<table width='50%'>
						<tr>
							<td width='20%'>Sales Order </td>
							<td width='1%'>:</td>
							<td><?= $getData[0]['no_so']; ?></td>
						</tr>
						<tr>
							<td>No Penawaran</td>
							<td>:</td>
							<td><?= strtoupper($getData[0]['no_penawaran']); ?></td>
						</tr>
						<tr>
							<td>No SPK Delivery</td>
							<td>:</td>
							<td><?= strtoupper($getData[0]['no_delivery']); ?></td>
						</tr>
						<tr>
							<td>No Surat Jalan</td>
							<td>:</td>
							<td><?= strtoupper($getData[0]['no_surat_jalan']); ?></td>
						</tr>
						<tr>
							<td>Customer</td>
							<td>:</td>
							<td><?= strtoupper($getData[0]['nm_customer']); ?></td>
						</tr>
						<tr>
							<td>Project</td>
							<td>:</td>
							<td><?= strtoupper($getData[0]['project']); ?></td>
						</tr>
						<tr>
							<td>Tanggal Kirim</td>
							<td>:</td>
							<td><?= date('d-M-Y', strtotime($getData[0]['delivery_date'])); ?></td>
						</tr>
						<tr>
							<td>Alamat Pengiriman</td>
							<td>:</td>
							<td><?= $getData[0]['delivery_address']; ?></td>
						</tr>
						<?php if (!empty($tanda)) { ?>
							<tr>
								<td>Tanggal Dikirim</td>
								<td>:</td>
								<td><?= date('d-M-Y', strtotime($getData[0]['tgl_dikirim'])); ?></td>
							</tr>
							<tr>
								<td>Tanggal Diterima</td>
								<td>:</td>
								<td><?= date('d-M-Y', strtotime($getData[0]['tgl_diterima'])); ?></td>
							</tr>
							<tr>
								<td>Ekspedisi</td>
								<td>:</td>
								<td><?= $getData[0]['ekspedisi']; ?></td>
							</tr>
							<tr>
								<td>Diterima Oleh</td>
								<td>:</td>
								<td><?= $getData[0]['diterima_oleh']; ?></td>
							</tr>
						<?php } ?>
					</table>
					<input type="hidden" id='no_delivery' name='no_delivery' value='<?= $getData[0]['no_delivery']; ?>'>
				</div>
			</div>
			<hr>
			<?php if (empty($tanda)) { ?>
				<div class="form-group row">
					<label class="label-control col-sm-2"><b>Tanggal Dikirim</b></label>
					<div class="col-sm-4">
						<input type="text" id="tgl_dikirim" name="tgl_dikirim" class="form-control input-md datepicker" placeholder="Tanggal Dikirim" value="" readonly="">
					</div>
					<label class="label-control col-sm-2"><b>Ekspedisi</b></label>
					<div class="col-sm-4">
						<input type="text" id="ekspedisi" name="ekspedisi" class="form-control input-md " placeholder="Ekspedisi" value="">
					</div>
				</div>
				<div class="form-group row">
					<label class="label-control col-sm-2"><b>Tanggal Diterima</b></label>
					<div class="col-sm-4">
						<input type="text" id="tgl_diterima" name="tgl_diterima" class="form-control input-md datepicker" placeholder="Tanggal Diterima" value="" readonly="">
					</div>
					<label class="label-control col-sm-2"><b>Diterima Oleh</b> <span class='text-danger'>*</span></label>
					<div class="col-sm-4">
						<input type="text" id="diterima_oleh" name="diterima_oleh" class="form-control input-md " placeholder="Diterima Oleh" value="">
					</div>
				</div>
				<div class="form-group row">
					<label class="label-control col-sm-2"><b>Upload Dokumen</b></label>
					<div class="col-sm-4 text-right">
						<input type="file" id="upload_spk" name="upload_spk" class="form-control input-md" placeholder="Upload SPK">

					</div>
				</div>
				<hr>
			<?php } ?>
			<h4>List Product</h4>
			<div class="form-group row">
				<div class="col-md-12">
					<table class="table table-bordered table-striped" width='100%'>
						<thead>
							<tr>
								<th width='5%' class='text-center'>#</th>
								<th>PRODUCT</th>
								<th width='12%' class='text-center'>QTY ORDER</th>
								<th width='12%' class='text-center'>QTY SPK</th>
								<th width='12%' class='text-center'>QTY DELIVERY</th>
							</tr>
						</thead>
						<tbody id='load-data'>
							<?php
							if (!empty($getDetail)) {
								foreach ($getDetail as $key => $value) {
									$key++;
									// $nama_product = (!empty($GET_DET_Lv4[$value['code_lv4']]['nama']))?$GET_DET_Lv4[$value['code_lv4']]['nama']:'';
									$nama_product = $value['nm_product'];
									echo "<tr class='tr_" . $key . "'>";
									echo "<td class='text-center'>" . $key . "</td>";
									echo "<td>" . $nama_product . "</td>";
									echo "<td class='text-center'>" . number_format($value['qty_order'], 2) . "</td>";
									echo "<td class='text-center'>" . number_format($value['qty_spk'], 2) . "</td>";
									echo "<td class='text-center'>" . number_format($value['qty_delivery'], 2) . "</td>";
									echo "</tr>";
								}
							} else {
								echo "<tr>";
								echo "<td colspan='5'>Tidak ada data yang ditampilkan.</td>";
								echo "</tr>";
							}
							?>
						</tbody>
					</table>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-6">
					<?php if (empty($tanda)) { ?>
						<button type="button" class="btn btn-primary" name="save" id="save">Save</button>
					<?php } ?>
					<button type="button" class="btn btn-danger" style='margin-left:5px;' name="back" id="back">Back</button>
				</div>
			</div>


		</form>
	</div>
</div>


<script src="<?= base_url('assets/js/jquery.maskMoney.js') ?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<style>
	.datepicker,
	.datepicker2 {
		cursor: pointer;
	}

	.mid-valign {
		vertical-align: middle !important;
	}
</style>

<script type="text/javascript">
	//$('#input-kendaraan').hide();
	var base_url = '<?php echo base_url(); ?>';
	var active_controller = '<?php echo ($this->uri->segment(1)); ?>';

	$(document).ready(function() {
		$('.chosen-select').select2();
		$('.autoNumeric0').autoNumeric('init', {
			mDec: '0',
			aPad: false
		})
		$('.datepicker').datepicker({
			dateFormat: 'dd-M-yy'
		});

		setTimeout(() => {
			$("#qr_code").focus();
			$('#help-text').html('<i>Ready to Scan QR...!!</i>')
		}, 500)

		$(document).on('focus', '#qr_code', function() {
			$('#help-text').html('<i>Ready to Scan QR...!!</i>')
		})
		$(document).on('blur', '#qr_code', function() {
			$('#help-text').html('')
		})


		//back
		$(document).on('click', '#back', function() {
			window.location.href = base_url + active_controller
		});

		$(document).on('keypress', '#qr_code', function(e) {
			const input = $(this)
			if (e.keyCode == '13') {
				var formData = new FormData($('#data-form')[0]);
				$.ajax({
					url: base_url + active_controller + '/save_detail_delivery',
					type: "POST",
					data: formData,
					cache: false,
					dataType: 'json',
					processData: false,
					contentType: false,
					success: function(data) {
						if (data.status == 1) {
							swal({
								title: "Success!",
								text: data.pesan,
								type: "success",
								timer: 3000
							});

							console.log(data);
							$('#load-data').load(base_url + active_controller + '/loadDataSS/' + data.no_delivery)
							$('.autoNumeric0').autoNumeric('init', {
								mDec: '0',
								aPad: false
							})
							// loadData(type = data.data[0].sts, kode_delivery = data.data[0].kode_delivery)
							$('.notif').fadeIn('slow').html(`
								<div class="alert alert-info" role="alert">
								<h4 class="alert-heading">Scan Berhasil!</h4>
								<p>` + input.val() + `</p>
								</div>
								`)

							input.val('').focus();
							setTimeout(function() {
								$('.notif').fadeToggle('slow')
							}, 7000)

						} else {
							swal({
								title: "Failed!",
								text: data.pesan,
								type: "warning",
								timer: 3000
							});

							$('.notif').fadeIn('slow').html(`
								<div class="alert alert-warning" role="alert">
								<h4 class="alert-heading">Scan Gagal!</h4>
								<p>` + data.pesan + `</p>
								</div>
								`)

							input.val('').focus();
							setTimeout(function() {
								$('.notif').fadeToggle('slow')
							}, 7000)
						}
					},
					error: function() {
						swal({
							title: "Error Message !",
							text: 'An Error Occured During Process. Please try again..',
							type: "error",
							timer: 3000
						});
					}
				});
			}
		})

		$(document).on('keyup', '.changeDelivery', function() {
			let qty_delivery = $(this).val()
			let id_spk = $(this).data('id_spk')

			let qty_spk = getNum($(this).parent().parent().find('.qtyBelumKirim').text().split(',').join(''))
			if (qty_delivery > qty_spk) {
				$(this).val(qty_spk)

				qty_delivery = qty_spk
			}

			$.ajax({
				url: base_url + active_controller + '/changeDeliveryTemp',
				type: "POST",
				data: {
					'id_spk': id_spk,
					'qty_delivery': qty_delivery,
				},
				cache: false,
				dataType: 'json',
				success: function(data) {
					if (data.status == '1') {
						console.log('Success !!!')
					} else {
						console.log('Failed !!!')
					}
				},
				error: function() {
					console.log('Error !!!')
				}
			});
		});

		$(document).on('click', '.delPart', function() {
			var get_id = $(this).parent().parent().attr('class');
			$("." + get_id).remove();


			let id_spk = $(this).data('id')

			$.ajax({
				url: base_url + active_controller + '/deleteDeliveryTemp',
				type: "POST",
				data: {
					'id_spk': id_spk
				},
				cache: false,
				dataType: 'json',
				success: function(data) {
					if (data.status == '1') {
						console.log('Success !!!')
					} else {
						console.log('Failed !!!')
					}
				},
				error: function() {
					console.log('Error !!!')
				}
			});
		});


		$('#save').click(function(e) {
			e.preventDefault();
			let diterima_oleh = $('#diterima_oleh').val()
			// console.log(no_surat_jalan)
			if (diterima_oleh == '') {
				swal({
					title: "Error Message!",
					text: 'Diterima Oleh Kosong !',
					type: "warning"
				});
				return false;
			}

			swal({
					title: "Are you sure ?",
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
						var baseurl = siteurl + active_controller + '/confirm';
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
									swal({
										title: "Save Failed!",
										text: data.pesan,
										type: "warning",
										timer: 7000
									});
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