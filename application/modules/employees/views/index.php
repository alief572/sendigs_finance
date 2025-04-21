<?php
$ENABLE_ADD     = has_permission('Master_Employee.Add');
$ENABLE_MANAGE  = has_permission('Master_Employee.Manage');
$ENABLE_VIEW    = has_permission('Master_Employee.View');
$ENABLE_DELETE  = has_permission('Master_Employee.Delete');
?>
<link rel="stylesheet" href="https://cdn.datatables.net/2.2.1/css/dataTables.dataTables.min.css">
<div class="box box-primary">
	<div class="box-header">
		<h3 class="box-title"><?= $title; ?></h3>
		<div class="box-tool pull-right">
		</div>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped">
			<thead>
				<tr class='bg-blue'>
					<!--<th class="text-center">Foto</th> -->
					<th class="text-center">No</th>
					<th class="text-center">Id</th>
					<th class="text-center">NIK</th>
					<th class="text-center">Name</th>
					<th class="text-center">Hometown</th>
					<th class="text-center">Birthday</th>
					<th class="text-center">Gender</th>
					<th class="text-center">Religion</th>
					<th class="text-center">Nationality</th>
					<th class="text-center">Employee Status</th>
					<th class="text-center">Status Aktif</th>
					<th class="text-center">Option</th>
				</tr>
			</thead>
			<tbody>

			</tbody>
		</table>
	</div>
	<!-- /.box-body -->
</div>
<!-- /.box -->
<script src="https://cdn.datatables.net/2.2.1/js/dataTables.min.js"></script>
<script>
	$(document).ready(function() {
		DataTables();
	});

	function deleteData(id) {
		swal({
				title: "Are you sure?",
				text: "You will not be able to process again this data!",
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
					loading_spinner();
					window.location.href = base_url + 'index.php/' + active_controller + '/delete/' + id;

				} else {
					swal("Cancelled", "Data can be process again :)", "error");
					return false;
				}
			});

	}

	function DataTables() {
		var DataTables = $('#example1').dataTable({
			ajax: {
				url: siteurl + active_controller + 'get_data_employees',
				type: "POST",
				dataType: "JSON",
				data: function(d) {

				}
			},
			columns: [{
					data: 'no',
				},
				{
					data: 'id'
				},
				{
					data: 'nik'
				},
				{
					data: 'name'
				},
				{
					data: 'hometown'
				},
				{
					data: 'birthday'
				},
				{
					data: 'gender'
				},
				{
					data: 'religion'
				},
				{
					data: 'nationality'
				},
				{
					data: 'employee_status'
				},
				{
					data: 'status_aktif'
				},
				{
					data: 'option'
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