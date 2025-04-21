<?php 
	$ENABLE_VIEW = has_permission('Rencana_Pembelian_Asset.View');
	$ENABLE_ADD = has_permission('Rencana_Pembelian_Asset.Add');
	$ENABLE_MANAGE = has_permission('Rencana_Pembelian_Asset.Manage');
	$ENABLE_DELETE = has_permission('Rencana_Pembelian_Asset.Delete');
?>
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">
<form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data">
	<div class="box box-primary">
		<div class="box-header">
			<h3 class="box-title"><?php echo $title; ?></h3>
			<div class="box-tool pull-right">
				<?php
					if($ENABLE_ADD && $label == '') {
						echo '
							<a href="'.base_url("asset_planning/add_asset").'" class="btn btn-sm btn-success" id="btn-add">
								<i class="fa fa-plus"></i> &nbsp;&nbsp;Add Budget
							</a>
						';
					}
				?>
			</div>
		</div>
		<!-- /.box-header -->
		<div class="box-body table-responsive">
			<input type='hidden' id='tanda' value='<?= $tanda; ?>'>
			<table class="table table-bordered table-striped" id="my-grid" width='100%'>
				<thead>
					<tr class='bg-blue'>
						<th class="text-center">#</th>
						<th class="text-center">Category</th>
						<th class="text-center">Department</th>
						<th class="text-center">Costcenter</th>
						<th class="text-center">Nama Asset</th>
						<th class="text-center">Qty</th>
						<th class="text-center">Budget</th>
						<th class="text-center">Sisa Budget PR</th>
						<th class="text-center">Sisa Budget PO</th>
						<th class="text-center no-sort">Planning</th>
						<th class="text-center no-sort">Status</th>
						<th class="text-center no-sort">Option</th>
					</tr>
				</thead>
				<tbody></tbody>
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
	.chosen-container-active .chosen-single {
		border: none;
		box-shadow: none;
	}

	.chosen-container-single .chosen-single {
		height: 34px;
		border: 1px solid #d2d6de;
		border-radius: 0px;
		background: none;
		box-shadow: none;
		color: #444;
		line-height: 32px;
	}

	.chosen-container-single .chosen-single div {
		top: 5px;
	}
</style>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script>
	$(document).ready(function() {
		var tanda = $('#tanda').val();
		DataTables(tanda);
	});

	$(document).on('click', '.detail', function(e) {
		e.preventDefault();
		$("#head_title2").html("<b>DETAIL BUDGET RUTIN [" + $(this).data('code') + "]</b>");
		$.ajax({
			type: 'POST',
			url: base_url + active_controller + 'detail_rutin/' + $(this).data('code'),
			success: function(data) {
				$("#ModalView2").modal();
				$("#view2").html(data);

			},
			error: function() {
				swal({
					title: "Error Message !",
					text: 'Connection Timed Out ...',
					type: "warning",
					timer: 5000,
					showCancelButton: false,
					showConfirmButton: false,
					allowOutsideClick: false
				});
			}
		});
	});

	$(document).on('click', '.hapus', function(e) {
		e.preventDefault();
		var id = $(this).data('id');

		swal({
				title: "Are you sure?",
				text: "You will save be able to process again this data!",
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
					var formData = new FormData($('#form_proses_bro')[0]);
					$.ajax({
						url: base_url + active_controller + 'hapus_asset/' + id,
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
								window.location.href = base_url + active_controller + 'index_asset';
							} else if (data.status == 0) {
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


	function DataTables(tanda = null) {
		var dataTable = $('#my-grid').DataTable({
			"serverSide": true,
			"stateSave": true,
			"bAutoWidth": true,
			"destroy": true,
			"processing": true,
			"responsive": true,
			"fixedHeader": {
				"header": true,
				"footer": true
			},
			"oLanguage": {
				"sSearch": "<b>Live Search : </b>",
				"sLengthMenu": "_MENU_ &nbsp;&nbsp;<b>Records Per Page</b>&nbsp;&nbsp;",
				"sInfo": "Showing _START_ to _END_ of _TOTAL_ entries",
				"sInfoFiltered": "(filtered from _MAX_ total entries)",
				"sZeroRecords": "No matching records found",
				"sEmptyTable": "No data available in table",
				"sLoadingRecords": "Please wait - loading...",
				"oPaginate": {
					"sPrevious": "Prev",
					"sNext": "Next"
				}
			},
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
				url: base_url + active_controller + 'server_side_asset',
				type: "post",
				data: function(d) {
					d.tanda = tanda
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