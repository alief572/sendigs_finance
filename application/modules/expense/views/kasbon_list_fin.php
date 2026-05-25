<?php
$ENABLE_ADD     = has_permission('Kasbon_Approval.Add');
$ENABLE_MANAGE  = has_permission('Kasbon_Approval.Manage');
$ENABLE_VIEW    = has_permission('Kasbon_Approval.View');
$ENABLE_DELETE  = has_permission('Kasbon_Approval.Delete');
?>
<link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.dataTables.min.css">
<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<div class="box">
	<div class="box-header">
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<div class="table-responsive">
			<table id="mytabledata" class="table table-bordered table-striped">
				<thead>
					<tr>
						<th width="5">#</th>
						<th>No Kasbon</th>
						<th>Tanggal</th>
						<th>Nama</th>
						<th>Keperluan</th>
						<th>Keterangan</th>
						<th>Status</th>
						<th width="120">Action</th>
					</tr>
				</thead>
				<tbody>

				</tbody>
			</table>
		</div>
	</div>
	<!-- /.box-body -->
</div>
<div id="form-data"></div>

<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
<script>
	$(document).ready(function() {
		datatables();
	})

	function datatables() {
		var datatables = $('#mytabledata').dataTable({
			serverSide: true,
			processing: true,
			destroy: true,
			paging: true,
			ajax: {
				type: 'post',
				url: siteurl + active_controller + 'get_dat_app_kasbon',
				cache: false,
				dataType: 'json',
				error: function(xhr, status, error) {
					console.error("Error: " + error);
				}
			},
			columns: [{
					data: 'no'
				},
				{
					data: 'no_kasbon'
				},
				{
					data: 'tanggal'
				},
				{
					data: 'nama'
				},
				{
					data: 'keperluan'
				},
				{
					data: 'keterangan'
				},
				{
					data: 'status'
				},
				{
					data: 'action'
				}
			]
		});
	}
</script>