<?php
$ENABLE_ADD     = has_permission('Kasbon.Add');
$ENABLE_MANAGE  = has_permission('Kasbon.Manage');
$ENABLE_VIEW    = has_permission('Kasbon.View');
$ENABLE_DELETE  = has_permission('Kasbon.Delete');
?>
<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css" integrity="sha512-yVvxUQV0QESBt1SyZbNJMAwyKvFTLMyXSyBHDO4BG5t7k/Lw34tyqlSDlKIrIENIzCl+RVUNjmCPG+V/GMesRw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
<!-- DataTables -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js" integrity="sha512-rMGGF4wg1R73ehtnxXBt5mbUfN9JUJwbk21KMlnLZDJh7BkPmeovBuddZCENJddHYYMkCh9hPFnPmS9sspki8g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<!-- page script -->
<script type="text/javascript">
	var url_add = siteurl + 'expense/kasbon_create/';
	var url_edit = siteurl + 'expense/kasbon_edit/';
	var url_delete = siteurl + 'expense/kasbon_delete/';
	var url_view = siteurl + 'expense/kasbon_view/';

	$(document).ready(function() {
		datatables();

		$('.chosen_select').chosen({
			width: '100%'
		});
	})

	function datatables() {
		$('#mytabledata').dataTable({
			serverSide: true,
			processing: true,
			destroy: true,
			paging: true,
			stateSave: false,
			ajax: {
				type: 'POST',
				url: siteurl + active_controller + 'get_dat_list_kasbon',
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
<script src="<?= base_url('assets/js/basic.js') ?>"></script>