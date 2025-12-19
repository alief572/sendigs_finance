<?php
$ENABLE_ADD     = has_permission('Expense.Add');
$ENABLE_MANAGE  = has_permission('Expense.Manage');
$ENABLE_VIEW    = has_permission('Expense.View');
$ENABLE_DELETE  = has_permission('Expense.Delete');
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
						<th>Approval Date</th>
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

<div class="modal fade" id="modalKasbon" tabindex="-1" role="dialog" aria-labelledby="modalKasbonLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="modalKasbonLabel">Pilih Data Kasbon</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<table class="table table-bordered" id="tableKasbon">
					<thead>
						<tr>
							<th>#</th>
							<th>No Dokumen</th>
							<th>Tanggal</th>
							<th>Keperluan</th>
							<th>Keterangan</th>
							<th>Jumlah</th>
							<th>Aksi</th>
						</tr>
					</thead>
					<tbody>
						<!-- Data kasbon akan dimuat secara dinamis -->
					</tbody>
				</table>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
			</div>
		</div>
	</div>
</div>

<!-- DataTables -->

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="<?= base_url('assets/js/basic.js') ?>"></script>

<!-- page script -->
<script type="text/javascript">
	var url_add = siteurl + 'expense/create/';
	var url_edit = siteurl + 'expense/edit/';
	var url_delete = siteurl + 'expense/delete/';
	var url_view = siteurl + 'expense/view/';

	var all = "<?= (isset($all)) ? $all : '' ?>";

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
				url: siteurl + active_controller + 'get_dat_expense_data',
				cache: false,
				dataType: 'json',
				data: function(d) {
					d.all = all;
				},
				error: function(xhr, status, error) {
					console.error("DataTable AJAX error: " + status + ": " + error);
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
					data: 'approval_date'
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