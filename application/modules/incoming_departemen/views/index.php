<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.3/css/dataTables.dataTables.min.css">
<form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data" autocomplete='off'>
	<div class="box box-primary">
		<div class="box-header">
			<h3 class="box-title"><?php echo $title; ?></h3>
			<div class="box-tool pull-right">
			</div>
		</div>
		<!-- /.box-header -->
		<div class="box-body">
			<div class="box box-success">
				<div class="box-body">
					<br>
					<input type="hidden" id='tandax' name='tandax'>
					<div class='in_ipp'>
						<div class='form-group row'>
							<div class="col-md-6">
								<label class='label-control col-sm-4'><b>Supplier <span class='text-red'>*</span></b></label>
								<div class='col-sm-8'>
									<select id='supplier' name='supplier' class='form-control input-sm list_supplier' style='min-width:200px;'>
										<option value="">- Choose Supplier -</option>
										<option value="NON-PO">NON-PO</option>
										<?php
										foreach ($list_supplier as $item) {
											echo '<option value="' . $item['kode_supplier'] . '">' . $item['nama'] . '</option>';
										}
										?>
									</select>
								</div>

								<div class="col-md-12" style="margin-top: 1rem;">
									<table class="table table-bordered">
										<thead>
											<tr>
												<th class="text-center">No</th>
												<th class="text-center">Tipe PO</th>
												<th class="text-center">No. PO</th>
												<th class="text-center">No. PR</th>
												<th class="text-center">Keterangan</th>
												<th class="text-center">Action</th>
											</tr>
										</thead>
										<tbody class="list_po">

										</tbody>
									</table>
								</div>
							</div>
							<div class="col-md-6">
								<label class='label-control col-sm-4'><b>PIC <span class='text-red'>*</span></b></label>
								<div class='col-sm-8'>
									<?php
									echo form_input(array('id' => 'pic', 'name' => 'pic', 'class' => 'form-control input-md', 'placeholder' => 'PIC'));
									?>
								</div>
								<label class='label-control col-sm-4' style="margin-top: 1rem;"><b>Note</b></label>
								<div class='col-sm-8' style="margin-top: 1rem;">
									<?php
									echo form_textarea(array('id' => 'note', 'name' => 'note', 'class' => 'form-control input-md', 'rows' => '2', 'cols' => '75', 'placeholder' => 'Note'));
									?>
								</div>
								<label class='label-control col-sm-4' style="margin-top: 1rem;"><b>Date Transaksi</b></label>
								<div class='col-sm-8' style="margin-top: 1rem;">
									<input type="date" name="tanggal_trans" id="tanggal_trans" class='form-control input-sm' value='<?= date('Y-m-d'); ?>'>
								</div>
							</div>
						</div>
					</div>
					<div class='form-group row'>
						<div class="col-md-12">
							<div class="col-md-12">
								<?php
								echo form_button(array('type' => 'button', 'class' => 'btn btn-md btn-success', 'style' => 'min-width:100px; float:right; margin: 5px 0px 5px 0px;', 'value' => 'Process', 'content' => 'Process', 'id' => 'modalDetail')) . ' ';
								?>
							</div>
						</div>
					</div>
					<?php
					if ($akses_menu['create'] == '1') {
						echo form_button(array('type' => 'button', 'class' => 'btn btn-md btn-success', 'style' => 'min-width:100px; float:right; margin: 5px 0px 5px 0px;', 'value' => 'Process', 'content' => 'Process', 'id' => 'modalDetail')) . ' ';
					}
					?>
				</div>
			</div>

			<table class="table table-bordered table-striped" id="my-grid" width='100%'>
				<thead>
					<tr class='bg-blue'>
						<th class="text-center">#</th>
						<th class="text-center">No Trans</th>
						<th class="text-center">Incoming Date</th>
						<th class="text-center">No PO/Non-PO</th>
						<th class="text-center no-sort">Sum Barang</th>
						<th class="text-center no-sort">PIC</th>
						<th class="text-center no-sort">Receiver</th>
						<th class="text-center no-sort">Supplier</th>
						<th class="text-center no-sort">Detail</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$no = 1;
					foreach ($list_incoming as $item) {

						$print	= "&nbsp;<a href='" . base_url('incoming_departemen/print_incoming_dept/' . $item['kode_trans']) . "' target='_blank' class='btn btn-sm btn-warning' title='Print'><i class='fa fa-print'></i></a>";
						$view = '<button type="button" class="btn btn-sm btn-primary detailAjust" title="View Incoming" data-kode_trans="' . $item['kode_trans'] . '" ><i class="fa fa-eye"></i></button>';


						echo '<tr>';
						echo '<td class="text-center">' . $no . '</td>';
						echo '<td class="text-center">' . $item['kode_trans'] . '</td>';
						echo '<td class="text-center">' . date('d F Y', strtotime($item['incoming_date'])) . '</td>';
						echo '<td class="text-center">' . $item['no_po'] . '</td>';
						echo '<td class="text-center">' . number_format($item['sum_qty']) . '</td>';
						echo '<td class="text-center">' . ucfirst($item['pic']) . '</td>';
						echo '<td class="text-center">' . $item['receiver'] . '</td>';
						echo '<td class="text-center">' . $list_arr_supplier[$item['id_supplier']]['nama'] . '</td>';
						echo '<td class="text-center">' . $view . ' ' . $print . '</td>';
						echo '</tr>';

						$no++;
					}
					?>
				</tbody>
			</table>
		</div>
		<!-- /.box-body -->
	</div>
	<!-- /.box -->
	<!-- modal -->
	<div class="modal fade" id="ModalView2" style='overflow-y: auto;'>
		<div class="modal-dialog" style='width:80%; '>
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="head_title2"></h4>
				</div>
				<div class="modal-body" id="view2">
				</div>
				<div class="modal-footer">
					<!--<button type="button" class="btn btn-primary">Save</button>-->
					<button type="button" class="btn btn-default " data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
	<!-- modal -->
</form>
<style>
	#tanggal_trans {
		cursor: pointer;
	}
</style>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/2.1.3/js/dataTables.min.js"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script>
	$(document).ready(function() {
		$('.list_supplier').select2();

		var no_po = $('#no_po').val();
		var gudang = $('#gudang').val();

		$('#my-grid').DataTable();

		// DataTables(no_po, gudang);

		// $(document).on('change', '#gudang, #no_po', function(e) {
		// 	e.preventDefault();
		// 	var no_po = $('#no_po').val();
		// 	var gudang = $('#gudang').val();
		// 	DataTables(no_po, gudang);
		// });
	});

	$(document).on('change', '.list_supplier', function() {
		var id_supplier = $(this).val();

		$.ajax({
			type: 'post',
			url: siteurl + active_controller + 'get_list_po_depart',
			data: {
				'id_supplier': id_supplier
			},
			cache: false,
			dataType: 'json',
			success: function(result) {
				$('.list_po').html(result.hasil);
			}
		});
	});

	$(document).on('click', '.detailAjust', function(e) {
		e.preventDefault();
		$("#head_title2").html("<b>DETAIL INCOMING</b>");
		$.ajax({
			type: 'POST',
			url: base_url + active_controller + '/modal_detail/' + $(this).data('kode_trans'),
			success: function(data) {
				$("#ModalView2").modal();
				$("#view2").html(data);

			},
			error: function() {
				swal({
					title: "Error Message !",
					text: 'Connection Timed Out ...',
					type: "warning",
					timer: 5000
				});
			}
		});
	});

	$(document).on('click', '#modalDetail', function(e) {
		e.preventDefault();
		var pic = $('#pic').val();
		var note = $('#note').val();
		var supplier = $('#supplier').val();
		var tanggal_trans = $('#tanggal_trans').val();

		var check_po = [];
		$('.check_po').each(function() {
			if ($(this).is(':checked')) {
				var value = $(this).val();
				check_po.push(value);
			}
		});

		if (check_po.length < 1) {
			swal({
				title: 'Error !',
				text: 'Please check some PO first !',
				type: 'error'
			});

			return false;
		}

		if (pic == '' || pic == null) {
			swal({
				title: 'Error !',
				text: 'PIC cannot be empty !',
				type: 'error'
			});

			return false;
		}

		$("#head_title2").html("<b>INCOMING DEPARTEMEN</b>");
		$.ajax({
			type: 'POST',
			url: base_url + active_controller + '/modal_incoming',
			data: {
				"no_po": check_po,
				"pic": pic,
				"note": note,
				'tanggal_trans': tanggal_trans
			},
			success: function(data) {
				$("#ModalView2").modal();
				$("#view2").html(data);

			},
			error: function() {
				swal({
					title: "Error Message !",
					text: 'Connection Timed Out ...',
					type: "warning",
					timer: 5000
				});
			}
		});
	});

	$(document).on('click', '#saveINMaterial', function() {

		var sts_qty = 1;
		var sts_not_exc = 1;
		$('.qtyDiterima').each(function() {
			var id = $(this).data('id');
			var value = $(this).val();
			var max = $('.max_qty_' + id).val();
			if (sts_qty == 1) {
				if (value == '' && value == null && max > 0) {
					sts_qty = 0;
				}
			}

			if(sts_not_exc == 1) {
				if(value > max) {
					sts_not_exc = 0;
				}
			}
		});

		if (sts_qty !== 1) {
			swal({
				title: 'Error !',
				text: 'Please make sure all qty form is filled!',
				type: 'error'
			});

			return false;
		}

		if (sts_not_exc !== 1) {
			swal({
				title: 'Error !',
				text: 'Please make sure all qty are not exceeding the limit !',
				type: 'error'
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
				closeOnConfirm: false,
				closeOnCancel: false
			},
			function(isConfirm) {
				if (isConfirm) {
					var formData = new FormData($('#form_adjustment')[0]);
					$.ajax({
						url: base_url + active_controller + '/process_incoming',
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

	// function DataTables(no_po = null, gudang = null) {
	// 	var dataTable = $('#my-grid').DataTable({
	// 		"processing": true,
	// 		"serverSide": true,
	// 		"stateSave": true,
	// 		"bAutoWidth": true,
	// 		"destroy": true,
	// 		"responsive": true,
	// 		"aaSorting": [
	// 			[1, "asc"]
	// 		],
	// 		"columnDefs": [{
	// 			"targets": 'no-sort',
	// 			"orderable": false,
	// 		}],
	// 		"sPaginationType": "simple_numbers",
	// 		"iDisplayLength": 10,
	// 		"aLengthMenu": [
	// 			[10, 20, 50, 100, 150],
	// 			[10, 20, 50, 100, 150]
	// 		],
	// 		"ajax": {
	// 			url: base_url + active_controller + '/server_side_incoming',
	// 			type: "post",
	// 			data: function(d) {
	// 				d.no_po = no_po,
	// 					d.gudang = gudang
	// 			},
	// 			cache: false,
	// 			error: function() {
	// 				$(".my-grid-error").html("");
	// 				$("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
	// 				$("#my-grid_processing").css("display", "none");
	// 			}
	// 		}
	// 	});
	// }

	// function DataTables2(gudang1 = null) {
	// 	var dataTable = $('#my-grid2').DataTable({
	// 		"processing": true,
	// 		"serverSide": true,
	// 		"stateSave": true,
	// 		"bAutoWidth": true,
	// 		"destroy": true,
	// 		"responsive": true,
	// 		"aaSorting": [
	// 			[1, "asc"]
	// 		],
	// 		"columnDefs": [{
	// 			"targets": 'no-sort',
	// 			"orderable": false,
	// 		}],
	// 		"sPaginationType": "simple_numbers",
	// 		"iDisplayLength": 10,
	// 		"aLengthMenu": [
	// 			[10, 20, 50, 100, 150],
	// 			[10, 20, 50, 100, 150]
	// 		],
	// 		"ajax": {
	// 			url: base_url + active_controller + '/server_side_move_gudang',
	// 			type: "post",
	// 			data: function(d) {
	// 				d.gudang1 = gudang1
	// 			},
	// 			cache: false,
	// 			error: function() {
	// 				$(".my-grid-error").html("");
	// 				$("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
	// 				$("#my-grid_processing").css("display", "none");
	// 			}
	// 		}
	// 	});
	// }
</script>