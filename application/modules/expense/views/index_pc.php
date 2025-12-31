<?php
$ENABLE_ADD     = has_permission('Expense_Petty_Cash.Add');
$ENABLE_MANAGE  = has_permission('Expense_Petty_Cash.Manage');
$ENABLE_VIEW    = has_permission('Expense_Petty_Cash.View');
$ENABLE_DELETE  = has_permission('Expense_Petty_Cash.Delete');
?>
<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
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
		<div class="table-responsive col-md-12">
			<table id="mytabledata" class="table table-bordered table-striped">
				<thead>
					<tr>
						<th width="5">#</th>
						<th>No Dokumen</th>
						<th>Tanggal</th>
						<th>Nama</th>
						<th>Approval</th>
						<th>Keterangan</th>
						<th>Nominal</th>
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
<!-- page script -->
<script type="text/javascript">
	var url_add = siteurl + 'expense/create_pc/';
	var url_edit = siteurl + 'expense/edit_pc/';
	var url_delete = siteurl + 'expense/delete/';
	var url_view = siteurl + 'expense/view_pc/';



	$(document).ready(function() {
		datatables();
	})

	function datatables() {
		var datatables = $('#mytabledata').dataTable({
			serverSide: true,
			processing: true,
			destroy: true,
			paging: true,
			stateSave: true,
			ajax: {
				type: 'post',
				url: siteurl + active_controller + 'get_dat_expense_pc',
				cache: false,
				dataType: 'json',
				error: function(xhr, status, error) {
					console.log('Error: ' + status + ' - ' + error);
				}
			},
			columns: [{
					data: 'no'
				},
				{
					data: 'no_doc'
				},
				{
					data: 'tgl_doc'
				},
				{
					data: 'nama'
				},
				{
					data: 'approval'
				},
				{
					data: 'keterangan'
				},
				{
					data: 'nominal'
				},
				{
					data: 'status'
				},
				{
					data: 'action'
				},
			]
		});
	}
</script>
<script src="<?= base_url('assets/js/basic.js') ?>"></script>