<?php
$ENABLE_ADD     = has_permission('Stock_Milik_Origa.Add');
$ENABLE_MANAGE  = has_permission('Stock_Milik_Origa.Manage');
$ENABLE_VIEW    = has_permission('Stock_Milik_Origa.View');
$ENABLE_DELETE  = has_permission('Stock_Milik_Origa.Delete');
?>
<style type="text/css">
	thead input {
		width: 100%;
	}
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.6/css/dataTables.dataTables.min.css">

<div class="box">
	<div class="box-header">
		<button type="button" class="btn btn-sm btn-danger clear_propose_request" style="float: right; margin-right: 5px;">Clear Propose Request</button>
		<button type="button" class="btn btn-sm btn-success save_pr" style="float: right; margin-right: 5px;">Save</button>
		<br>
		<div class="form-group row">
			<div class="col-md-1">
				<b>Product Type</b>
			</div>
			<div class="col-md-3">
				<select name='product' id='product' class='form-control input-sm chosen-select' onchange="DataTables()">
					<option value=''>All Product Type</option>
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
			<thead class="bg-primary">
				<tr>
					<th class='text-center'>#</th>
					<th>Type BOM</th>
					<th>Product Name</th>
					<th class='text-center no-sort'>Actual Stock<br>Downgrade</th>
					<th class='text-center no-sort'>Actual Stock<br>Oke</th>
					<th class='text-center no-sort'>Propose</th>
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
				<h4 class="modal-title" id="myModalLabel"><span class="fa fa-users"></span>&nbsp;Detail Data</h4>
			</div>
			<div class="modal-body" id="ModalView">
				...
			</div>
		</div>
	</div>

	<!-- DataTables -->

	<script src="https://cdn.datatables.net/2.1.6/js/dataTables.min.js"></script>
	<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
	<!-- page script -->
	<script type="text/javascript">
		// DELETE DATA
		$('.auto_num').autoNumeric('init', {
			vMin: 0,
			mDec: 0
		});

		function get_num(nilai) {
			if (nilai !== '' && nilai !== null) {
				nilai = nilai.split(',').join('');
				nilai = parseFloat(nilai);
			} else {
				nilai = 0;
			}

			return nilai;
		}

		$(document).on('click', '.hapus', function(e) {
			e.preventDefault()
			var id = $(this).data('id');
			// alert(id);
			swal({
					title: "Anda Yakin?",
					text: "Data Product akan di hapus !",
					type: "warning",
					showCancelButton: true,
					confirmButtonClass: "btn-info",
					confirmButtonText: "Ya, Hapus!",
					cancelButtonText: "Batal",
					closeOnConfirm: false
				},
				function() {
					$.ajax({
						type: 'POST',
						url: siteurl + active_controller + 'hapus',
						dataType: "json",
						data: {
							'id': id
						},
						success: function(result) {
							if (result.status == '1') {
								swal({
										title: "Sukses",
										text: "Data berhasil dihapus.",
										type: "success"
									},
									function() {
										window.location.reload(true);
									})
							} else {
								swal({
									title: "Error",
									text: "Data error. Gagal hapus data",
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

		$(document).on('change', '.propose', function() {
			var id = $(this).data('id');
			var nilai = get_num($(this).val());

			$.ajax({
				type: "POST",
				url: siteurl + active_controller + 'input_propose',
				data: {
					'id': id,
					'nilai': nilai
				},
				cache: false,
				dataType: 'JSON',
				success: function(result) {
					if (result.status == 1) {

					} else {
						swal({
							title: 'Failed !',
							text: 'New PR has been failed !',
							type: 'error'
						});
					}
				},
				error: function(result) {
					swal({
						title: 'Error !',
						text: 'Please try again later !',
						type: 'error'
					});
				}
			});
		});

		$(document).on('click', '.clear_propose_request', function() {
			swal({
				title: 'Are you sure?',
				text: 'All inputed propose will be cleared !',
				type: 'warning',
				showCancelButton: true
			}, function(after) {
				if (after) {
					$.ajax({
						type: 'POST',
						url: siteurl + active_controller + 'clear_propose_request',
						data: '',
						cache: false,
						dataType: 'JSON',
						success: function(result) {
							if (result.status == 1) {
								swal({
									title: 'Success !',
									text: 'All inputed propose request has been cleared !',
									type: 'success'
								}, function(suc) {
									location.reload();
								});
							} else {
								swal({
									title: 'Failed !',
									text: 'All inputed propose request has been not cleared !',
									type: 'error'
								});
							}
						},
						error: function(result) {
							swal({
								title: 'Error !',
								text: 'Please try again later !',
								type: 'error'
							});
						}
					});
				}
			});
		});

		$(document).on('click', '.save_pr', function() {
			swal({
				type: 'warning',
				title: 'Are you sure?',
				text: 'New PR will be created',
				showCancelButton: true
			}, function(save) {
				if(save) {
					
				}
			});
		});

		$(document).ready(function() {
			DataTables();
		});

		function DataTables() {
			$('#example1').dataTable({
				ajax: {
					url: siteurl + active_controller + 'get_data_product',
					type: "POST",
					dataType: "JSON",
					data: function(d) {
						d.product_type = $('#product').val();
					}
				},
				columns: [{
						data: 'no',
					}, {
						data: 'type_bom'
					},
					{
						data: 'product_name'
					},
					{
						data: 'actual_stock_downgrade'
					},
					{
						data: 'actual_stock_oke'
					},
					{
						data: 'propose'
					}
				],
				responsive: true,
				processing: true,
				serverSide: true,
				stateSave: true,
				destroy: true,
				paging: true
			});
		}
	</script>