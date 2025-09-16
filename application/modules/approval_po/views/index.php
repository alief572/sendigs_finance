<?php
$ENABLE_ADD     = has_permission('Approval_PR_Material.Add');
$ENABLE_MANAGE  = has_permission('Approval_PR_Material.Manage');
$ENABLE_VIEW    = has_permission('Approval_PR_Material.View');
$ENABLE_DELETE  = has_permission('Approval_PR_Material.Delete');
?>
<style type="text/css">
	thead input {
		width: 100%;
	}
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box">
	<div class="box-header">
		<?php if ($ENABLE_ADD) : ?>
			<!-- <a class="btn btn-info btn-sm" style='float:right; margin-left:5px;' href="<?= base_url('stock_origa/download_excel'); ?>" target='_blank' title="Download"><i class="fa fa-excel">&nbsp;</i>Excel</a>
					<a class="btn btn-success btn-sm" style='float:right;' href="<?= base_url('stock_origa/add') ?>" title="Add">Add Stock</a> -->
		<?php endif; ?>
		<br>
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
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped" width='100%'>
			<thead>
				<tr>
					<td class="text-center">#</td>
					<td class="text-center">No. PO</td>
					<td class="text-center">Tanggal PO</td>
					<td class="text-center">Created By</td>
					<td class="text-center">Created Date</td>
					<td class="text-center">Action</td>
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
				<h4 class="modal-title" id="myModalLabel">Default</h4>
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
		$(document).on('click', '.detail', function() {
			var so_number = $(this).data('so_number');
			// alert(id);
			$("#head_title").html("<b>Detail>");
			$.ajax({
				type: 'POST',
				url: base_url + active_controller + 'detail',
				data: {
					'so_number': so_number,
				},
				success: function(data) {
					$("#dialog-popup").modal();
					$("#ModalView").html(data);

				}
			})
		});

		// DELETE DATA
		$(document).on('click', '.booking', function(e) {
			e.preventDefault()
			var so_number = $(this).data('so_number');
			// alert(id);
			swal({
					title: "Anda Yakin?",
					text: "Process Booking Material & PR !",
					type: "warning",
					showCancelButton: true,
					confirmButtonClass: "btn-info",
					confirmButtonText: "Ya!",
					cancelButtonText: "Batal",
					closeOnConfirm: false
				},
				function() {
					$.ajax({
						type: 'POST',
						url: base_url + active_controller + 'process_booking',
						dataType: "json",
						data: {
							'so_number': so_number
						},
						success: function(result) {
							if (result.status == '1') {
								swal({
										title: "Sukses",
										text: result.pesan,
										type: "success"
									},
									function() {
										window.location.reload(true);
									})
							} else {
								swal({
									title: "Error",
									text: result.pesan,
									type: "error"
								})

							}
						},
						error: function() {
							swal({
								title: "Error",
								text: "Data error. Gagal request Ajax",
								type: "error"
							})
						}
					})
				});
		});

		$(document).on('click', '.Approve', function(e) {
			e.preventDefault()
			var id = $(this).data('no_po');
			// alert(id);
			swal({
					title: "Anda Yakin?",
					text: "PO. Akan Di Approve.",
					type: "warning",
					showCancelButton: true,
					confirmButtonClass: "btn-info",
					confirmButtonText: "Ya, Approve!",
					cancelButtonText: "Batal",
					closeOnConfirm: false
				},
				function() {
					$.ajax({
						type: 'POST',
						url: siteurl + 'purchase_order/Approved',
						dataType: "json",
						data: {
							'id': id
						},
						success: function(result) {
							if (result.status == '1') {
								swal({
										title: "Sukses",
										text: "P.R Approved.",
										type: "success"
									},
									function() {
										window.location.reload(true);
									})
							} else {
								swal({
									title: "Error",
									text: "Data error. Gagal Approve data",
									type: "error"
								})

							}
						},
						error: function() {
							swal({
								title: "Error",
								text: "Data error. Gagal request Ajax",
								type: "error"
							})
						}
					})
				});

		})

		$(document).on('click', '.view', function() {
			var id = $(this).data('no_pr');
			// alert(id);
			$("#head_title").html("<i class='fa fa-list-alt'></i><b>Detail Inventory</b>");
			$.ajax({
				type: 'POST',
				url: siteurl + 'purchase_request/View/' + id,
				data: {
					'id': id
				},
				success: function(data) {
					$("#dialog-popup").modal();
					$("#ModalView").html(data);

				}
			})
		});

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
					url: siteurl + active_controller + 'data_side_approval_pr_material',
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