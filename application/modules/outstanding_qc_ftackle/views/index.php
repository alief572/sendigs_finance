<style type="text/css">
	thead input {
		width: 100%;
	}
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box">
	<div class="box-header">

	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<div class="form-group row" hidden>
			<div class="col-md-1">
				<b>Product Type</b>
			</div>
			<div class="col-md-3">
				<select name='product' id='product' class='form-control input-sm chosen-select'>
					<option value='0'>All Product Type</option>
					<?php
					foreach (get_list_inventory_lv1('product') as $val => $valx) {
						echo "<option value='" . $valx['code_lv1'] . "'>" . strtoupper($valx['nama']) . "</option>";
					}
					?>
				</select>
			</div>
		</div>
		<div class="form-group row" hidden>
			<div class="col-md-1">
				<b>Costcenter</b>
			</div>
			<div class="col-md-3">
				<select name='costcenter' id='costcenter' class='form-control input-sm chosen-select'>
					<option value='0'>All Costcenter</option>
					<?php
					foreach (get_costcenter() as $val => $valx) {
						echo "<option value='" . $valx['id_costcenter'] . "'>" . strtoupper($valx['nama_costcenter']) . "</option>";
					}
					?>
				</select>
			</div>
		</div>
		<table id="example1" class="table table-bordered table-striped" width='100%'>
			<thead>
				<tr>
					<th class='text-center'>#</th>
					<th>No SPK</th>
					<th>Product</th>
					<th>No SO</th>
					<th>Customer</th>
					<th>Project</th>
					<th>Qty</th>
					<th>Close Produksi</th>
					<th class='text-center no-sort'>Option</th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>
	<!-- /.box-body -->
</div>


<div class="modal modal-default fade" id="dialog-popup" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" style='width:90%; '>
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel">Quality Check Product</h4>
			</div>
			<div class="modal-body" id="ModalView">
				...
			</div>
		</div>
	</div>

	<!-- DataTables -->
	<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
	<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

	<!-- page script -->
	<script type="text/javascript">
		$(document).ready(function() {
			var product = $("#product").val();
			var costcenter = $("#costcenter").val();
			DataTables(costcenter, product);

			$(document).on('change', '#costcenter', function() {
				var costcenter = $("#costcenter").val();
				var product = $("#product").val();
				DataTables(costcenter, product);
			});

			$(document).on('change', '#product', function() {
				var costcenter = $("#costcenter").val();
				var product = $("#product").val();
				DataTables(costcenter, product);
			});
		});

		$(document).on('click', '.detail', function(e) {
			var id = $(this).data('id');
			var status = $(this).data('status');
			$("#head_title").html("<b>Process QC</b>");
			$.ajax({
				type: 'POST',
				url: siteurl + active_controller + '/qc/' + id + '/' + status,
				success: function(data) {
					$("#dialog-popup").modal();
					$("#ModalView").html(data);

				}
			})
		});

		$(document).on('click', '#sendCheckRelease', function(e) {
			e.preventDefault();
			// alert('Development');
			// return false;
			if ($('.chk_personal:checked').length == 0) {
				swal({
					title: "Error Message!",
					text: 'Checklist milimal satu terlebih dahulu',
					type: "warning"
				});
				$('#sendCheckRelease').prop('disabled', false);
				return false;
			}

			let error = false
			let daycode
			let qc_pass
			let status
			let nomor
			$('.chk_personal:checked').each(function() {
				nomor = $(this).data('nomor')
				daycode = $('#daycode_' + nomor).val()
				qc_pass = $('#qc_pass_date_' + nomor).val()
				status = $('#status' + nomor).val()
				if (qc_pass == '' || status == '0' || daycode == '') {
					error = true
					return false;
				}
			})

			if (error === true) {
				swal({
					title: "Error Message!",
					text: 'Status/Daycode/QC Pass Date Wajib Di Isi !!!',
					type: "warning"
				});
				$('#sendCheckRelease').prop('disabled', false);
				return false;
			}

			swal({
					title: "Are you sure?",
					text: "Release ke Finish Good !!!",
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
						var formData = new FormData($('#data_form')[0]);
						var baseurl = siteurl + active_controller + '/qc';
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
										timer: 3000
									});

									DataTables(0, 0)
									$("#dialog-popup").modal();
									$("#ModalView").load(siteurl + active_controller + '/qc/' + data.id + '/' + data.tanda);
								} else if (data.status == 2) {
									swal({
										title: "Save Failed!",
										text: data.pesan,
										type: "warning",
										timer: 3000
									});
								}
							},
							error: function() {

								swal({
									title: "Error Message !",
									text: 'An Error Occured During Process. Please try again..',
									type: "warning",
									timer: 3000
								});
							}
						});
					} else {
						swal("Cancelled", "Data can be process again :)", "error");
						return false;
					}
				});
		});

		$(document).on('click', '#sendCheckReleaseNew', function(e) {
			e.preventDefault();
			// alert('Development');
			// return false;
			if ($('.chk_personal:checked').length == 0) {
				swal({
					title: "Error Message!",
					text: 'Checklist milimal satu terlebih dahulu',
					type: "warning"
				});
				$('#sendCheckRelease').prop('disabled', false);
				return false;
			}

			let error = false
			let daycode
			let qc_pass
			let status
			let nomor
			$('.chk_personal:checked').each(function() {
				nomor = $(this).data('nomor')
				daycode = $('#daycode_' + nomor).val()
				qc_pass = $('#qc_pass_date_' + nomor).val()
				status = $('#status' + nomor).val()
				if (qc_pass == '' || status == '0' || daycode == '') {
					error = true
					return false;
				}
			})

			if (error === true) {
				swal({
					title: "Error Message!",
					text: 'Status/Daycode/QC Pass Date Wajib Di Isi !!!',
					type: "warning"
				});
				$('#sendCheckRelease').prop('disabled', false);
				return false;
			}

			swal({
					title: "Are you sure?",
					text: "Release ke Finish Good !!!",
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
						var formData = new FormData($('#data_form')[0]);
						var baseurl = siteurl + active_controller + '/qcNew';
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
										timer: 3000
									});

									DataTables(0, 0)
									$("#dialog-popup").modal();
									$("#ModalView").load(siteurl + active_controller + '/qc/' + data.id + '/' + data.tanda);
								} else if (data.status == 2) {
									swal({
										title: "Save Failed!",
										text: data.pesan,
										type: "warning",
										timer: 3000
									});
								}
							},
							error: function() {

								swal({
									title: "Error Message !",
									text: 'An Error Occured During Process. Please try again..',
									type: "warning",
									timer: 3000
								});
							}
						});
					} else {
						swal("Cancelled", "Data can be process again :)", "error");
						return false;
					}
				});
		});


		function DataTables(costcenter = null, product = null) {
			var dataTable = $('#example1').DataTable({
				// "scrollX": true,
				// "scrollCollapse" : true,
				// "scrollY": 500,
				"processing": true,
				"serverSide": true,
				"stateSave": true,
				"fixedHeader": true,
				"autoWidth": false,
				"destroy": true,
				"searching": true,
				"responsive": true,
				"aaSorting": [
					[1, "desc"]
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
					url: siteurl + active_controller + 'data_side_outstanding_qc',
					type: "post",
					data: function(d) {
						d.costcenter = costcenter,
						d.product = product
					},
					cache: false,
					error: function() {
						$(".my-grid-error").html("");
						$("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
						$("#my-grid_processing").css("display", "none");
					}
				}
			});
		}
	</script>