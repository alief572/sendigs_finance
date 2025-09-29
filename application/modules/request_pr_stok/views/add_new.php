<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data">
	<div class="box box-primary">
		<div class="box-header">
			<div class="box-tool pull-right">
				<?php
				echo form_button(array('type' => 'button', 'class' => 'btn btn-sm btn-danger', 'style' => 'min-width:100px; float:right; margin: 5px 0px 5px 5px;', 'content' => 'Clear Propose Request', 'id' => 'autoDelete'));
				echo form_button(array('type' => 'button', 'class' => 'btn btn-sm btn-primary', 'style' => 'min-width:100px; float:right; margin: 5px 0px 5px 0px;', 'content' => 'Update Otomatis', 'id' => 'autoUpdate'));
				?>
			</div><br><br>

			<div class="box-tool pull-right">
				<label for="tgl_butuh"><b>Tingkat PR</b></label>
				<select name="tingkat_pr" id="" class="form-control input-md tingkat_pr">
					<option value="1">Normal</option>
					<option value="2">Urgent</option>
				</select>
			</div>
			<div class="box-tool pull-right">
				<label for="tgl_butuh"><b>Tanggal Dibutuhkan</b></label>
				<?php
				$tgl_now = date('Y-m-d');
				$tgl_next_month = date('Y-m-' . '20', strtotime('+1 month', strtotime($tgl_now)));
				echo form_input(array('id' => 'tgl_butuh', 'name' => 'tgl_butuh', 'class' => 'form-control input-md text-center datepicker changeSaveDate', 'readonly' => 'readonly', 'placeholder' => 'Tanggal Dibutuhkan'), $tgl_next_month);
				?>
			</div>
		</div>
		<!-- /.box-header -->
		<div class="box-body">
			<div class="form-group row">
				<div class="col-md-3">
					<select name='category' id='category' class='form-control input-sm chosen-select'>
						<!-- <option value='0'>Select Category</option> -->
						<?php
						foreach ($category as $val => $valx) {
							$selected = ($valx['id'] == '8') ? 'selected' : '';
							echo "<option value='" . $valx['id'] . "' " . $selected . " data-nm_category='" . strtoupper($valx['nm_category']) . "'>" . strtoupper($valx['nm_category']) . "</option>";
						}
						?>
					</select>
				</div>
			</div>
			<div class="row">
				<div class="col-md-6">
					<label for="">Budget</label>
					<input type="text" name="" id="" class="form-control form-control-sm text-right autoNumeric0 nilai_budget" value="" readonly>
				</div>
				<div class="col-md-6">
					<label for="">Pengajuan</label>
					<input type="text" name="" id="" class="form-control form-control-sm text-right nilai_pengajuan" value="" readonly>
				</div>
			</div>
			<br><br>
			<table class="table table-bordered table-striped" id="example1" width='100%'>
				<thead>
					<tr class='bg-blue'>
						<th class="text-center" width='4%'>#</th>
						<th class="text-center">Nama Barang</th>
						<th class="text-center">Kebutuhan 1 Bulan</th>
						<th class="text-center">Stock</th>
						<th class="text-center">Max Stock</th>
						<th class="text-center">Propose Purhcase</th>
						<th class="text-center">Unit</th>
						<th class="text-center">Keterangan</th>
						<th class="text-center">Price Reference</th>
						<th class="text-center">Total Price</th>
					</tr>
				</thead>
				<tbody></tbody>
				<tfoot>
					<tr class="bg-blue">
						<th colspan="9" class="text-center">Total Price Pengajuan</th>
						<th class="text-right total-price">0</th>
					</tr>
				</tfoot>
			</table><br>
			<?php
			echo form_button(array('type' => 'button', 'class' => 'btn btn-md btn-danger', 'style' => 'min-width:100px; float:right; margin: 5px 5px 5px 5px;', 'content' => 'Back', 'id' => 'back')) . ' ';
			echo form_button(array('type' => 'button', 'class' => 'btn btn-md btn-success', 'style' => 'min-width:100px; float:right; margin: 5px 0px 5px 0px;', 'content' => 'Purchase Request', 'id' => 'saveRequest')) . ' ';
			?>
		</div>
		<!-- /.box-body -->
	</div>
	<!-- /.box -->

</form>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<style>
	.datepicker {
		cursor: pointer;
	}
</style>
<script>
	$(document).ready(function() {
		var category = $("#category").val();
		DataTables(category);



		$(document).on('click', '#back', function() {
			window.location.href = siteurl + active_controller;
		});

		$(document).on('change', '#category', function() {
			var category = $("#category").val();
			DataTables(category);
			hitungBudget();
			hitungPengajuan();
		});

		$('.autoNumeric2').autoNumeric('init', {
			mDec: '2',
			aPad: false
		})
		$('.datepicker').datepicker({
			dateFormat: 'yy-mm-dd',
			changeMonth: true,
			changeYear: true,
		});

		hitungBudget();
		hitungPengajuan();
	});

	$(document).on('click', '#autoUpdate', function() {
		var inventory = $('#category').val();
		var nm_category = $('#category').find(':selected').data('nm_category')

		if (inventory == '0') {
			swal({
				title: "Error Message!",
				text: 'Filter category terlebih dahulu ...',
				type: "warning"
			});
			return false;
		}
		swal({
				title: "Are you sure?",
				text: "Update otomatis Category " + nm_category + " !",
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
					$.ajax({
						url: base_url + active_controller + '/auto_update_rutin/' + inventory,
						type: "POST",
						cache: false,
						dataType: 'json',
						success: function(data) {
							if (data.status == 1) {
								swal({
									title: "Save Success!",
									text: data.pesan,
									type: "success",
									timer: 7000
								});
								// window.location.href = base_url + active_controller + 'add_new';
								DataTables(inventory);
								hitungPengajuan();
							} else if (data.status == 0) {
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

	$(document).on('click', '#autoDelete', function() {
		var id_category = $('#category').val();
		var nm_category = $('#category').find(':selected').data('nm_category')

		if (id_category == '0') {
			swal({
				title: "Error Message!",
				text: 'Pilih Category Terlebih Dahulu ...',
				type: "warning"
			});
			return false;
		}
		swal({
				title: "Are you sure?",
				text: "Clear Propose Category " + nm_category + " !",
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
					$.ajax({
						url: base_url + active_controller + '/clear_update_reorder/' + id_category,
						type: "POST",
						cache: false,
						dataType: 'json',
						success: function(data) {
							if (data.status == 1) {
								swal({
									title: "Save Success!",
									text: data.pesan,
									type: "success",
									timer: 7000
								});
								// window.location.href = base_url + active_controller + 'add_new';
								DataTables(id_category);
								hitungBudget();
								hitungPengajuan();
							} else if (data.status == 0) {
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



	$(document).on('change', '.changeSave', function() {
		var id = $(this).data('id');
		var inventory = $('#category').val();
		var max_propose = $(this).data('max_propose');
		var qty_satuan = $(this).val();
		// if (qty_satuan == '' || qty_satuan == null) {
		// 	qty_satuan = 0;
		// } else {
		// 	qty_satuan = qty_satuan.split(',').join('');
		// 	qty_satuan = parseFloat(qty_satuan);
		// }

		// if (qty_satuan > max_propose) {
		// 	swal({
		// 		type: 'warning',
		// 		title: 'Peringatan !',
		// 		text: 'Nilai propose tidak boleh lebih dari Max Stock !',
		// 		showCancelButton: false,
		// 		allowoOutsideClick: false
		// 	});

		// 	qty_satuan = max_propose;
		// }

		// var konversi = $(this).data('konversi');
		// if (konversi == '' || konversi == 0 || konversi == null) {
		// 	konversi = 1;
		// }

		// var nilai = (qty_satuan / konversi);

		// var qty = $('.purchase_' + id).val().split(",").join("");

		var nomor = $(this).data('no');
		var id_material = $(this).data('id');
		// var input_pack = $('#purchase_pack_' + nomor).val().split(",").join("");
		var purchase = $('#purchase_' + nomor).val().split(",").join("");
		if (purchase > max_propose) {
			swal({
				type: 'warning',
				title: 'Peringatan !',
				text: 'Nilai propose tidak boleh lebih dari Max Stock !',
				showCancelButton: false,
				allowoOutsideClick: false
			});

			purchase = max_propose;
		}
		// if ($(this).hasClass('input_qty_packing')) {
		// 	purchase = (input_pack * konversi);
		// }
		// var purchase_pack = $('#purchase_pack_' + nomor).val().split(",").join("");
		// if ($(this).hasClass('input_qty_satuan')) {
		// 	purchase_pack = (purchase / konversi);
		// }
		// var tanggal 	= $('#tanggal_'+nomor).val();
		var tanggal = $('#tgl_butuh').val();
		var satuan = $('#satuan_' + nomor).val();
		// var spec = $('#spec_' + nomor).val();
		var info = $('#info_' + nomor).val();

		$.ajax({
			url: base_url + active_controller + 'save_reorder_change',
			type: "POST",
			data: {
				"id_material": id_material,
				"purchase": purchase,
				"tanggal": tanggal,
				"info": info,
				"satuan": satuan
			},
			cache: false,
			dataType: 'json',
			success: function(data) {
				DataTables(inventory);
				hitungPengajuan();
			},
			error: function() {
				console.log('error connection serve !')
			}
		});
	});

	function check_inputed_qty_stock() {
		// Create a deferred object (promise)
		var deferred = $.Deferred();

		// Perform the AJAX request
		$.ajax({
			url: siteurl + active_controller + 'check_inputed_qty_stock', // Replace with your API endpoint
			method: 'POST', // Use POST, GET, PUT, DELETE, etc. as per your requirement
			dataType: 'json', // Change the data type as per your API response
			success: function(response) {
				// Resolve the deferred object with the response data
				deferred.resolve(response.jumlah_data);
			},
			error: function(error) {
				// Reject the deferred object with the error message
				deferred.reject(error);
			}
		});

		// Return the promise object
		return deferred.promise();
	}

	$(document).on('click', '#saveRequest', function() {
		var category = $('#category').val();
		var tingkat_pr = $('.tingkat_pr').val();

		var nilai_budget = $('.nilai_budget').val();
		if (nilai_budget !== '') {
			nilai_budget = nilai_budget.split(',').join('');
			nilai_budget = parseFloat(nilai_budget);
		} else {
			nilai_budget = 0;
		}

		var nilai_pengajuan = $('.nilai_pengajuan').val();
		if (nilai_pengajuan !== '') {
			nilai_pengajuan = nilai_pengajuan.split(',').join('');
			nilai_pengajuan = parseFloat(nilai_pengajuan);
		} else {
			nilai_pengajuan = 0;
		}

		if (category == '0') {
			swal({
				title: "Error Message!",
				text: 'Pilih Category Terlebih Dahulu ...',
				type: "warning"
			});
			return false;
		}

		var valid = 0;
		check_inputed_qty_stock().then(function(data) {
			if (data > 0) {
				swal({
						title: "Are you sure?",
						text: "Membuat semua Propose berdasarkan Category !!!",
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
							var nilai_budget = $('.nilai_budget').val();
							if (nilai_budget !== '') {
								nilai_budget = nilai_budget.split(',').join('');
								nilai_budget = parseFloat(nilai_budget);
							} else {
								nilai_budget = 0;
							}

							var nilai_pengajuan = $('.nilai_pengajuan').val();
							if (nilai_pengajuan !== '') {
								nilai_pengajuan = nilai_pengajuan.split(',').join('');
								nilai_pengajuan = parseFloat(nilai_pengajuan);
							} else {
								nilai_pengajuan = 0;
							}

							$.ajax({
								url: base_url + active_controller + '/save_reorder_all',
								type: "POST",
								data: {
									'category': category,
									'tingkat_pr': tingkat_pr,
									'nilai_budget': nilai_budget,
									'nilai_pengajuan': nilai_pengajuan
								},
								cache: false,
								dataType: 'json',
								success: function(data) {
									if (data.status == 1) {
										swal({
											title: "Save Success!",
											text: data.pesan,
											type: "success",
											timer: 7000
										});
										window.location.href = base_url + active_controller
									} else if (data.status == 0) {
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
			} else {
				swal({
					title: 'Warning !',
					text: 'Please fill qty pack at least at 1 product !',
					type: 'warning'
				});
			}
		}).fail(function(error) {
			swal({
				title: 'Error !',
				text: 'Please try again later !',
				type: 'error'
			});
		});

	});

	$(document).on('change', '.changeSaveDate', function() {
		var tanggal = $('#tgl_butuh').val();
		var id_category = $('#category').val();

		if (id_category == '0') {
			swal({
				title: "Error Message!",
				text: 'Pilih Category Terlebih Dahulu ...',
				type: "warning"
			});
			return false;
		}

		$.ajax({
			url: base_url + active_controller + '/save_reorder_change_date',
			type: "POST",
			data: {
				"tanggal": tanggal,
				"id_category": id_category,
			},
			cache: false,
			dataType: 'json',
			success: function(data) {
				console.log(data.pesan)
			},
			error: function() {
				console.log('error connection serve !')
			}
		});
	});

	$(document).on('change', '.input_qty_packing', function() {
		var id = $(this).data('id');
		var qty_packing = $(this).val();
		if (qty_packing == '' || qty_packing == null) {
			qty_packing = 0;
		} else {
			qty_packing = qty_packing.split(',').join('');
			qty_packing = parseFloat(qty_packing);
		}

		var konversi = $(this).data('konversi');
		if (konversi == '' || konversi == 0 || konversi == null) {
			konversi = 1;
		}

		var nilai = (qty_packing * konversi);

		$('.purchase_' + id).val(nilai.toLocaleString());
	});

	$(document).on('change', '.input_qty_satuan', function() {
		var id = $(this).data('id');
		var max_propose = $(this).data('max_propose');
		var qty_satuan = $(this).val();
		if (qty_satuan == '' || qty_satuan == null) {
			qty_satuan = 0;
		} else {
			qty_satuan = qty_satuan.split(',').join('');
			qty_satuan = parseFloat(qty_satuan);
		}

		if (qty_satuan > max_propose) {
			qty_satuan = max_propose;
		}

		var konversi = $(this).data('konversi');
		if (konversi == '' || konversi == 0 || konversi == null) {
			konversi = 1;
		}

		var nilai = (qty_satuan / konversi);

		$('.purchase_pack_' + id).val(nilai.toLocaleString());
	});

	function hitungBudget() {
		var category = $('#category').val();

		$.ajax({
			type: 'post',
			url: siteurl + active_controller + 'hitung_budget',
			data: {
				'category': category
			},
			cache: false,
			dataType: 'json',
			success: function(result) {
				$('.nilai_budget').val(number_format(result.nilai_budget));
			}
		});
	}

	function hitungPengajuan() {
		var category = $('#category').val();

		$.ajax({
			type: 'post',
			url: siteurl + active_controller + 'hitung_pengajuan',
			data: {
				'category': category
			},
			cache: false,
			dataType: 'json',
			success: function(result) {
				$('.nilai_pengajuan').val(number_format(result.nilai_pengajuan));
			}
		});
	}

	function DataTables(category = null) {
		var dataTable = $('#example1').DataTable({
			"processing": true,
			"serverSide": true,
			"stateSave": true,
			"autoWidth": true,
			"destroy": true,
			"responsive": true,
			"aaSorting": [
				[2, "asc"]
			],
			"columnDefs": [{
				"targets": 'no-sort',
				"orderable": false,
			}],
			"sPaginationType": "simple_numbers",
			"iDisplayLength": 10,
			"aLengthMenu": [
				[10, 20, 50, 100, 150],
				[10, 20, 50, 100, 150]
			],
			"ajax": {
				url: base_url + active_controller + '/server_side_reorder_point_new',
				type: "post",
				data: function(d) {
					d.category = category
				},
				cache: false,
				dataSrc: function(json) {
					var total_price = json.total_price;

					// Menampilkan total harga di footer
					$('#example1 tfoot .total-price').text(number_format(total_price));

					// Mengembalikan data untuk DataTable
					return json.data;
				},
				error: function() {
					$(".my-grid-error").html("");
					$("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
					$("#my-grid_processing").css("display", "none");
				}
			}
		});
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