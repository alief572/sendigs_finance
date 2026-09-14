<?php
$ENABLE_ADD     = has_permission('Expense_Approval.Add');
$ENABLE_MANAGE  = has_permission('Expense_Approval.Manage');
$ENABLE_VIEW    = has_permission('Expense_Approval.View');
$ENABLE_DELETE  = has_permission('Expense_Approval.Delete');
?>
<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<style>
	.box-custom {
		background: #fff;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		box-shadow: 0 2px 10px rgba(0,0,0,0.04);
		margin-bottom: 20px;
	}
	.box-custom-header {
		padding: 15px 20px;
		border-bottom: 1px solid #edf2f7;
		display: flex;
		justify-content: space-between;
		align-items: center;
	}
	.box-custom-title {
		font-size: 16px;
		font-weight: 700;
		color: #2d3748;
		margin: 0;
	}
	.table-index thead th {
		background-color: #3c8dbc;
		color: #fff;
		font-weight: 600;
		font-size: 13px;
		border: 1px solid #367fa9 !important;
		vertical-align: middle !important;
	}
	.table-index tbody td {
		font-size: 13px;
		vertical-align: middle !important;
	}
	.btn-rounded {
		border-radius: 4px;
	}
</style>

<div class="box box-custom">
	<div class="box-custom-header">
		<h4 class="box-custom-title"><i class="fa fa-check-square-o text-primary"></i> Daftar Approval Expense (Finance)</h4>
	</div>
	<!-- /.box-header -->
	<div class="box-body" style="padding: 20px;">
		<div class="table-responsive">
			<table id="mytabledata" class="table table-bordered table-striped table-index" width="100%">
				<thead>
					<tr>
						<th width="30" class="text-center">#</th>
						<th width="140">No Dokumen</th>
						<th width="100">Tanggal</th>
						<th width="150">Nama Pemohon</th>
						<th>Keterangan</th>
						<th width="130" class="text-right">Nominal</th>
						<th width="110" class="text-center">Status</th>
						<th width="120" class="text-center">Aksi</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
	</div>
</div>

<div id="form-data"></div>

<!-- DataTables & SweetAlert -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script type="text/javascript">
	var url_approval = siteurl + 'expense/approval/';
	var url_view     = siteurl + 'expense/view/';

	$(document).ready(function() {
		DataTables();
	});

	function data_approve(id) {
		if (id != "") {
			$(".box").hide();
			$("#form-data").show();
			$("#form-data").load(url_approval + id);
		}
	}

	function data_view(id) {
		if (id != "") {
			$(".box").hide();
			$("#form-data").show();
			$("#form-data").load(url_view + id);
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
				url: siteurl + 'expense/get_expense_app_finance',
				cache: false,
				dataType: 'json',
				error: function(xhr, status, error) {
					Swal.fire({
						icon: 'error',
						title: 'Error!',
						text: error,
						timer: 3000,
						showConfirmButton: false
					});
				}
			},
			columns: [{
					data: 'no',
					orderable: false,
					className: 'text-center'
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
					data: 'status',
					className: 'text-center'
				},
				{
					data: 'action',
					orderable: false,
					className: 'text-center'
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