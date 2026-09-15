<?php
$ENABLE_ADD     = has_permission('Expense_Approval.Add');
$ENABLE_MANAGE  = has_permission('Expense_Approval.Manage');
$ENABLE_VIEW    = has_permission('Expense_Approval.View');
$ENABLE_DELETE  = has_permission('Expense_Approval.Delete');
?>
<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<div class="box">
	<div class="box-body">
		<div class="table-responsive col-md-12">
			<table id="mytabledata" class="table table-bordered table-striped">
				<thead>
					<tr>
						<th width="5">#</th>
						<th>No Dokumen</th>
						<th>Tanggal</th>
						<th>Nama</th>
						<th>Keterangan</th>
						<th>Nominal</th>
						<th>Status</th>
						<th width="120">Action</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
	</div>
</div>
<div id="form-data"></div>
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="text/javascript">
	var url_approval = siteurl + 'expense/approval/';

	DataTables();

	function data_approve(id) {
		if (id != "") {
			$(".box").hide();
			$("#form-data").show();
			$("#form-data").load(url_approval + id);
		}
	}

	function DataTables() {
		$('#mytabledata').dataTable({
			serverSide: true,
			processing: true,
			stateSave: true,
			destroy: true,
			paging: true,
			ajax: {
				type: 'GET',
				url: siteurl + active_controller + 'get_expense_app_finance',
				cache: false,
				dataType: 'json'
			},
			columns: [{
					data: 'no',
					orderable: false
				},
				{
					data: 'no_doc'
				},
				{
					data: 'tgl_doc'
				},
				{
					data: 'nmuser'
				},
				{
					data: 'informasi'
				},
				{
					data: 'nominal',
					className: 'text-right'
				},
				{
					data: 'status'
				},
				{
					data: 'action',
					orderable: false
				}
			],
			language: {
				processing: "Memuat data...",
				search: "Cari:",
				lengthMenu: "Tampilkan _MENU_ data",
				info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
				infoEmpty: "Tidak ada data",
				zeroRecords: "Data tidak ditemukan",
				paginate: {
					first: "Pertama",
					last: "Terakhir",
					next: "Berikutnya",
					previous: "Sebelumnya"
				}
			}
		});
	}
</script>
<script src="<?= base_url('assets/js/basic.js') ?>"></script>