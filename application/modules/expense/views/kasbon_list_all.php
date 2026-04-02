<?php
$ENABLE_ADD     = has_permission('Kasbon_List.Add');
$ENABLE_MANAGE  = has_permission('Kasbon_List.Manage');
$ENABLE_VIEW    = has_permission('Kasbon_List.View');
$ENABLE_DELETE  = has_permission('Kasbon_List.Delete');
?>
<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<div class="box">
	<!-- /.box-header -->
	<div class="box-body">
		<div class="table-responsive">
			<table id="mytabledata2" class="table table-bordered table-striped">
				<thead>
					<tr>
						<th width="5">#</th>
						<th>No Kasbon</th>
						<th>Tanggal</th>
						<th>Nama</th>
						<th>Keperluan</th>
						<th>Approval Date</th>
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
<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<!-- <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script> -->
<!-- page script -->
<script type="text/javascript">
	var url_view = siteurl + 'expense/kasbon_view/';
	// $("#mytabledata2").DataTable({
	// 	dom: "<'row'<'col-sm-2'B><'col-sm-4'l><'col-sm-6'f>>rtip",
	// 	buttons: [
	// 		'excel'
	// 	]
	// });

	$(document).ready(function() {
		datatables();
	})

	function datatables() {
		var datatables = $('#mytabledata2').DataTable({
			serverSide: true,
			processing: true,
			destroy: true,
			paging: true,
			ajax: {
				type: 'post',
				url: siteurl + active_controller + 'get_dat_kasbon_list',
				cache: false,
				dataType: 'json',
				error: function(xhr, status, error) {
					console.error("DataTable AJAX error: " + status + ": " + error);
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
					data: 'approval_date'
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
<script src="<?= base_url('assets/js/basic.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>