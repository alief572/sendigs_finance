<style type="text/css">
	thead input {
		width: 100%;
	}
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box">
	<div class="box-header">
		<span class="pull-left">
			<!-- <button type='button' class='btn btn-sm btn-primary' id='btnUpdate'>Update Product Price</button>
			<br> -->
			<!-- <span class='text-bold text-red'><?= $last_update; ?></span> -->
		</span>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<div class="form-group row">
			<div class="col-md-10">

			</div>
			<div class="col-md-2">
				<select name="status" id="status" class='form-control select2'>
					<option value="0">ALL STATUS</option>
					<option value="N">Waiting Submission</option>
					<option value="WA">Waiting Approval</option>
					<option value="A">Approved</option>
					<option value="R">Rejected</option>
				</select>
			</div>
		</div>
		<div class="table-responsive">
			<table id="example1" class="table table-bordered table-striped">
				<thead>
					<tr>
						<th>#</th>
						<th>Product Type</th>
						<th>Product Name</th>
						<!-- <th>Variant</th>
			<th>Color</th>
			<th>Surface</th> -->
						<th class='text-right'>Total Weight</th>
						<!-- <th class='text-right'>Price Material</th> -->
						<!-- <th class='text-right'>Price MP</th> -->
						<!-- <th class='text-right'>Product Costing</th> -->
						<th class='text-right'>Price List USD</th>
						<th class='text-right'>Price List IDR</th>
						<th class='text-right'>Orindo (%)</th>
						<th class='text-right'>Price Orindo</th>
						<th>Status</th>
						<th width='7%'>Action</th>
					</tr>
				</thead>

				<tbody></tbody>
			</table>
		</div>
	</div>
	<!-- /.box-body -->
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<!-- page script -->
<script type="text/javascript">
	// DELETE DATA
	$(document).on('click', '#btnUpdate', function(e) {
		e.preventDefault()
		swal({
				title: "Anda Yakin?",
				text: "Menarik Price Ref & BOM Terbaru !",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-info",
				confirmButtonText: "Ya, Update!",
				cancelButtonText: "Batal",
				closeOnConfirm: false
			},
			function() {
				$.ajax({
					type: 'POST',
					url: base_url + active_controller + '/update_product_price',
					dataType: "json",
					//   data:{'id':id},
					success: function(result) {
						if (result.status == '1') {
							swal({
									title: "Sukses",
									text: "Data berhasil diupdate.",
									type: "success"
								},
								function() {
									window.location.reload(true);
								})
						} else {
							swal({
								title: "Error",
								text: "Data error. Gagal diupdate",
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

	$(function() {
		$('.select2').select2({
			width: '100%'
		});

		$(document).on('change', '#status', function() {
			var status = $('#status').val()
			DataTables(status);
		})

		var status = $('#status').val()
		DataTables(status);
	});


	function DataTables(status = null) {
		var dataTable = $('#example1').DataTable({
			"processing": true,
			"serverSide": true,
			"stateSave": true,
			"autoWidth": true,
			"destroy": true,
			"responsive": true,
			"aaSorting": [
				[1, "asc"]
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
				url: base_url + active_controller + 'data_side_product_price',
				type: "post",
				data: function(d) {
					d.status = status
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