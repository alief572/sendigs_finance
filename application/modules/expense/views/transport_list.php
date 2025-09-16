<?php
$ENABLE_ADD     = has_permission('Transportasi.Add');
$ENABLE_MANAGE  = has_permission('Transportasi.Manage');
$ENABLE_VIEW    = has_permission('Transportasi.View');
$ENABLE_DELETE  = has_permission('Transportasi.Delete');
?>
<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css">
<div class="box">
	<div class="box-header">
		<?php if ($ENABLE_ADD) : ?>
			<div class="dropdown">
				<button class="btn btn-success btn-sm" type="button" onclick="data_add()">
					<i class="fa fa-plus">&nbsp;</i> Tambah
				</button>
			</div>
		<?php endif; ?>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<div class="table-responsive">
			<table id="mytabledata" class="table table-bordered table-striped">
				<thead>
					<tr>
						<th width="5">#</th>
						<th>No</th>
						<th>Tanggal</th>
						<th>Nama</th>
						<th>Keperluan</th>
						<th>No. Polisi</th>
						<th>Total Transport</th>
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
<script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>
<!-- page script -->
<script type="text/javascript">
	var url_add = siteurl + 'expense/transport_create/';
	var url_edit = siteurl + 'expense/transport_edit/';
	var url_delete = siteurl + 'expense/transport_delete/';
	var url_view = siteurl + 'expense/transport_view/';

	DataTables();

	function DataTables() {
		var datatables = $('#mytabledata').dataTable({
			serverSide: true,
			process: true,
			stateSave: true,
			paging: true,
			ajax: {
				type: 'post',
				url: siteurl + active_controller + 'get_data_transport_input',
				cache: false,
				dataType: 'json'
			},
			columns: [{
					data: 'no'
				},
				{
					data: 'id_pengajuan'
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
					data: 'no_polisi'
				},
				{
					data: 'total'
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