<?php
$ENABLE_ADD     = has_permission('Approval_Non_PO.Add');
$ENABLE_MANAGE  = has_permission('Approval_Non_PO.Manage');
$ENABLE_VIEW    = has_permission('Approval_Non_PO.View');
$ENABLE_DELETE  = has_permission('Approval_Non_PO.Delete');
?>
<style type="text/css">
	thead input {
		width: 100%;
	}
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.dataTables.min.css">

<div class="box">
	<div class="box-header">

	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<table id="table_non_po" class="table table-bordered table-striped" width='100%'>
			<thead>
				<tr>
					<th class="text-center">#</th>
					<th class="text-center">No. Non PO</th>
					<th class="text-center">No. PR</th>
					<th class="text-center">Tipe PR</th>
					<th class="text-center">Total PR</th>
					<th class="text-center">Action</th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>
	<!-- /.box-body -->
</div>


<div class="modal modal-default fade" id="dialog-popup" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel">Default</h4>
			</div>
			<form action="" method="" id="frm-data">
				<div class=" modal-body" id="ModalView">

				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default " data-dismiss="modal">Close</button>
					<button type="submit" class="btn btn-sm btn-danger btn_save_modal"><i class="fa fa-check"></i> Save</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- DataTables -->
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- page script -->
<script type="text/javascript">
	$(document).ready(function() {
		DataTables();
	})

	$(document).on('click', '.btn-approve', function() {
		var id = $(this).data('id');

		Swal.fire({
			title: 'Are you sure?',
			text: "You are about to approve this Non PO.",
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: 'Yes, approve it!'
		}).then((result) => {
			if (result.isConfirmed) {
				$.ajax({
					type: "POST",
					url: siteurl + active_controller + 'approve_non_po',
					data: {
						id: id
					},
					dataType: "json",
					success: function(response) {
						if (response.status == '1') {
							Swal.fire({
								icon: 'success',
								title: 'Approved !',
								text: 'The Non PO has been approved.',
								timer: 2000,
								showConfirmButton: false,
								allowOutsideClick: false
							}).then(() => {
								DataTables();
							});
						} else {
							Swal.fire({
								icon: 'warning',
								title: 'Failed !',
								text: 'The Non PO has not been approved.',
								timer: 2000,
								showConfirmButton: false,
								allowOutsideClick: false
							}).then(() => {
								DataTables();
							});
						}
					},
					error: function(xhr, status, error) {
						Swal.fire({
							icon: 'error',
							title: 'Error!',
							text: 'An error occurred while processing your request.',
							timer: 2000,
							showConfirmButton: false,
							allowOutsideClick: false
						});
						console.log(error); // Log error for debugging
					}
				});
			}
		})
	})

	$(document).on('click', '.btn-reject', function() {
		var id = $(this).data('id'); // Mendapatkan ID data yang ingin di-reject

		$.ajax({
			type: 'post',
			url: siteurl + active_controller + 'reject_modal',
			data: {
				'id': id
			},
			cache: false,
			success: function(result) {
				$('.modal-body').html(result);
				$('#myModalLabel').html('Reject Non PO');
				$('.btn_save_modal').html('<i class="fa fa-check"></i> Reject');
				$('#dialog-popup').modal('show');
			},
			error: function(result) {
				Swal.fire({
					icon: 'error',
					title: 'Error!',
					text: 'An error occurred while processing your request.',
					timer: 2000,
					showConfirmButton: false,
					allowOutsideClick: false
				});
			}
		});
	});

	$(document).on('submit', '#frm-data', function(e) {
		e.preventDefault();

		var reject_reason = $('textarea[name="reject_reason"]').val();

		if (reject_reason.length == 0) {
			Swal.fire({
				icon: 'warning',
				title: 'Warning !',
				text: 'Reject reason must be filled',
				timer: 3000,
				showConfirmButton: false,
				allowOutsideClick: false
			}).then((next) => {
				return false;
			});
		}

		Swal.fire({
			icon: 'warning',
			title: 'Are you sure ?',
			text: 'This data will be rejected !',
			showCancelButton: true,
			showConfirmButton: true,
			allowOutsideClick: false
		}).then((next) => {
			if (next.isConfirmed) {
				var formdata = $('#frm-data').serialize();

				$.ajax({
					type: 'post',
					url: siteurl + active_controller + 'reject_non_po',
					data: formdata,
					cache: false,
					dataType: 'json',
					success: function(result) {
						$('#dialog-popup').modal('hide');

						if (result.status == '1') {
							Swal.fire({
								icon: 'success',
								title: 'Rejected !',
								text: 'The Non PO has been rejected.',
								timer: 2000,
								showConfirmButton: false,
								allowOutsideClick: false
							}).then((lanjut) => {
								DataTables();
							});
						} else {
							Swal.fire({
								icon: 'warning',
								title: 'Failed !',
								text: 'The Non PO has not been rejected.',
								timer: 2000,
								showConfirmButton: false,
								allowOutsideClick: false
							}).then((lanjut) => {
								DataTables();
							});
						}
					},
					error: function(result) {
						Swal.fire({
							icon: 'error',
							title: 'Error!',
							text: 'An error occurred while processing your request.',
							timer: 2000,
							showConfirmButton: false,
							allowOutsideClick: false
						}).then((lanjut) => {
							DataTables();
						});
					}
				});
			}
		});
	});


	function DataTables() {
		var DataTables = $('#table_non_po').DataTable({
			serverSide: true,
			processing: true,
			stateSave: true,
			paging: true,
			destroy: true,
			ajax: {
				type: 'post',
				url: siteurl + active_controller + 'get_data_non_po',
				cache: false,
				dataType: 'json',
				error: function(xhr, status, error) {
					// Error handling for AJAX request
					alert('An error occurred while fetching data. Please try again.');
					console.log(error); // Log error for debugging
				}
			},
			columns: [{
					data: 'no',
					searchable: false,
					orderable: false,
					className: "text-center",
					width: '5%'
				},
				{
					data: 'no_non_po',
					className: "text-center",
					width: '20%'
				},
				{
					data: 'no_pr',
					className: "text-center",
					width: '20%'
				},
				{
					data: 'tipe_pr',
					className: "text-center",
					width: '20%'
				},
				{
					data: 'total_pr',
					className: "text-center",
					width: '20%'
				},
				{
					data: 'action',
					searchable: false,
					orderable: false,
					className: "text-center",
					width: '15%'
				}
			],
		});
	}
</script>