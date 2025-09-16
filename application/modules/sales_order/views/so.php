<?php
$ENABLE_ADD     = has_permission('Sales_Order.Add');
$ENABLE_MANAGE  = has_permission('Sales_Order.Manage');
$ENABLE_VIEW    = has_permission('Sales_Order.View');
$ENABLE_DELETE  = has_permission('Sales_Order.Delete');
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


		<span class="pull-right">
		</span>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped">
			<thead>
				<tr>
					<th width="5">No</th>
					<th>SO No</th>
					<th>Customer</th>
					<th>Quotation No.</th>
					<th>Project</th>
					<th>Update By</th>
					<th>Date</th>
					<th>Rev</th>
					<th>Status</th>
					<th width="13%">Action</th>
				</tr>
			</thead>

			<tbody></tbody>
		</table>
	</div>
	<!-- /.box-body -->
</div>


<div class="modal modal-default fade" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" style='width:50%; '>
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
	<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
	<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

	<!-- page script -->
	<script type="text/javascript">
		$(document).on('click', '.detail', function() {
			var no_so = $(this).data('no_so');
			// alert(id);
			$("#head_title").html("<i class='fa fa-list-alt'></i><b>Detail Cycletime</b>");
			$.ajax({
				type: 'POST',
				url: siteurl + active_controller + '/detail_sales_order/' + no_so,
				data: {
					'no_so': no_so
				},
				success: function(data) {
					$("#dialog-popup").modal();
					$("#ModalView").html(data);

				}
			})
		});

		$(document).on('click', '.ajukan', function() {
			var id_so = $(this).data('id_so');

			swal({
					title: "Anda yakin?",
					text: "Anda yakin ingin update status SO ini ke Request Approval ?",
					type: "warning",
					showCancelButton: true,
					confirmButtonClass: "btn-danger",
					confirmButtonText: "Update",
					cancelButtonText: "Batal",
					closeOnConfirm: true,
					closeOnCancel: true
				},
				function(isConfirm) {
					if (isConfirm) {
						$.ajax({
							url: siteurl + active_controller + '/update_status',
							type: "POST",
							data: {
								'id_so': id_so
							},
							cache: false,
							dataType: 'json',
							success: function(data) {
								if (data.status == 1) {
									swal({
										title: "Update Status Success!",
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
											title: "Update Status Failed!",
											text: data.pesan,
											type: "warning",
											timer: 7000,
											showCancelButton: false,
											showConfirmButton: false,
											allowOutsideClick: false
										});
									} else {
										swal({
											title: "Update Status Failed!",
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
					}
				});
		});

		$(function() {
			DataTables();
		});


		function DataTables() {
			var dataTable = $('#example1').DataTable({
				// "scrollX": true,
				"scrollY": "500",
				"scrollCollapse": true,
				"processing": true,
				"serverSide": true,
				"stateSave": true,
				"bAutoWidth": true,
				"destroy": true,
				"responsive": true,
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
					url: siteurl + active_controller + 'data_side_sales_order',
					type: "post",
					data: function(d) {
						// d.kode_partner = $('#kode_partner').val()
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