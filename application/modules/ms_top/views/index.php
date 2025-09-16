<?php
$ENABLE_ADD     = has_permission('TOP.Add');
$ENABLE_MANAGE  = has_permission('TOP.Manage');
$ENABLE_VIEW    = has_permission('TOP.View');
$ENABLE_DELETE  = has_permission('TOP.Delete');
?>
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.2/css/dataTables.dataTables.min.css">
<form action="#" method="POST" id="form_ct">
	<div class="box box-primary">
		<div class="box-header">
			<h3 class="box-title"><?php echo $title; ?></h3>
			<div class="box-tool pull-right">
				<?php if ($ENABLE_ADD) { ?>
					<button type='button' class="btn btn-md btn-info" id='add'><i class="fa fa-plus"></i> Add TOP</button>
				<?php } ?>
			</div>
		</div>
		<div class="box-body">
			<div class="table-responsive col-lg-12">
				<table class="table table-bordered table-striped" id="my-grid" width='100%'>
					<thead>
						<tr class='bg-blue'>
							<th class="text-center">#</th>
							<th class="text-center">Nama</th>
							<th class="text-center">Nilai</th>
							<th class="text-center">Option</th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>
		</div>
	</div>
	<div class="modal fade" id="ModalView">
		<div class="modal-dialog" style='width:60%; '>
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="head_title"></h4>
				</div>
				<div class="modal-body" id="view"></div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default " data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
</form>

<script src="https://cdn.datatables.net/2.0.2/js/dataTables.min.js"></script>
<script>
	$(document).ready(function() {
		DataTables();
	});
	$(document).on('click', '#add', function(e) {
		e.preventDefault();

		$("#head_title").html("<b>ADD TOP</b>");
		$("#view").load(base_url + 'index.php/' + active_controller + '/add_data');
		$("#ModalView").modal();
	});
	$(document).on('click', '.edit', function(e) {
		e.preventDefault();

		var id = $(this).data('code');
		$("#head_title").html("<b>EDIT TOP</b>");
		$("#view").load(base_url + 'index.php/' + active_controller + '/add_data/' + id);
		$("#ModalView").modal();
	});

	$(document).on('click', '#save', function() {
		var name = $("#name").val();
		var data1 = $("#data1").val();
		if (name == '') {
			swal({
				title: "Error Message!",
				text: 'Name Empty',
				type: "warning"
			});
			$('#save').prop('disabled', false);
			return false;
		}
		if (data1 == '') {
			swal({
				title: "Error Message!",
				text: 'Nilai Empty',
				type: "warning"
			});
			$('#save').prop('disabled', false);
			return false;
		}
		swal({
				title: "Are you sure?",
				text: "Save this data ?",
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

					var formData = new FormData($('#form_ct')[0]);
					var baseurl = base_url + active_controller + '/add_data';
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
									timer: 7000,
									showCancelButton: false,
									showConfirmButton: false,
									allowOutsideClick: false
								});
								window.location.href = base_url + active_controller;
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
					swal("Cancelled", "Data can be process again", "error");
					return false;
				}
			});
	});

	$(document).on('click', '.delete', function() {
		var code = $(this).data('code');
		swal({
				title: "Are you sure?",
				text: "Delete this data ?",
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

					$.ajax({
						url: base_url + 'index.php/' + active_controller + '/hapus_data/' + code,
						type: "POST",
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
								window.location.href = base_url + active_controller + '/index';
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

	function DataTables() {
		var dataTable = $('#my-grid').DataTable({
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
				[2, "asc"]
			],
			"columnDefs": [{
				"targets": 'no-sort',
				"orderable": false,
			}],
			"sPaginationType": "simple_numbers",
			"iDisplayLength": 50,
			"aLengthMenu": [
				[10, 20, 50, 100, 150],
				[10, 20, 50, 100, 150]
			],
			"ajax": {
				url: base_url + 'index.php/' + active_controller + '/data_side',
				type: "post",
				data: function(d) {},
				cache: false,
				error: function() {
					$(".my-grid-error").html("");
					$("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="4">No data found in the server</th></tr></tbody>');
					$("#my-grid_processing").css("display", "none");
				}
			}
		});
	}
</script>