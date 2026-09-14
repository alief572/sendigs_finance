<?php
$ENABLE_VIEW = has_permission('Approval_Request_Payment.View');
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.11/css/dataTables.bootstrap.min.css">

<style>
	#table_appr thead th {
		background-color: #3c8dbc;
		color: #fff;
		font-size: 12px;
		text-align: center;
		vertical-align: middle;
		white-space: nowrap;
	}

	#table_appr tbody td {
		font-size: 12.5px;
		vertical-align: middle;
	}

	.appr-status-pending {
		background: #FDECD8;
		color: #E28B34;
		font-size: 11px;
		font-weight: 700;
		padding: 4px 10px;
		border-radius: 10px;
	}

	.appr-toolbar {
		display: flex;
		justify-content: flex-end;
		margin-bottom: 12px;
	}

	.appr-toolbar input {
		padding: 6px 12px;
		font-size: 13px;
		border: 1px solid #ccc;
		border-radius: 3px;
		width: 260px;
	}
</style>

<div class="box">
	<div class="box-header with-border">
		<ol class="breadcrumb" style="background:none;padding:0;margin-bottom:5px;font-size:12px;">
			<li><a href="javascript:void(0);">Finance</a></li>
			<li class="active">Approval Request Payment</li>
		</ol>
		<h3 class="box-title" style="font-size: 22px; font-weight: 600;">
			Menunggu Approval
			<span class="badge" style="background:#E28B34;" id="appr_count_pending">0</span>
		</h3>
	</div>
	<div class="box-body">

		<div class="appr-toolbar">
			<input type="text" id="appr_search" placeholder="Cari no. pengajuan / company...">
		</div>

		<div style="overflow-x:auto;border:1px solid #ddd;">
			<table id="table_appr" class="table table-bordered table-striped" style="width:100%;">
				<thead>
					<tr>
						<th>NO. PENGAJUAN</th>
						<th>COMPANY</th>
						<th>TANGGAL PENGAJUAN</th>
						<th style="width:110px;">JUMLAH DOKUMEN</th>
						<th style="width:150px;">TOTAL DIBAYARKAN</th>
						<th style="width:130px;">STATUS</th>
						<th style="width:100px;">ACTION</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>

	</div>
</div>

<script src="https://cdn.datatables.net/1.13.11/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.11/js/dataTables.bootstrap.min.js"></script>

<script>
	$(document).ready(function() {
		var apprTable = $('#table_appr').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: '<?= site_url("approval_request_payment/get_data_pending"); ?>',
				type: 'POST',
				data: function(d) {
					d.search = {
						value: $('#appr_search').val()
					};
				},
				error: function() {
					alert('Gagal memuat data.');
				}
			},
			columns: [{
					data: 'no_pengajuan'
				},
				{
					data: 'company_nama'
				},
				{
					data: 'submitted_at'
				},
				{
					data: 'jumlah_dokumen',
					className: 'text-center'
				},
				{
					data: 'total_dibayarkan',
					className: 'text-right',
					render: function(d) {
						return 'Rp ' + d;
					}
				},
				{
					data: null,
					orderable: false,
					className: 'text-center',
					render: function() {
						return '<span class="appr-status-pending">Menunggu Approval</span>';
					}
				},
				{
					data: 'aksi',
					orderable: false,
					className: 'text-center',
					render: function(id) {
						return '<a href="<?= site_url("approval_request_payment/detail"); ?>/' + id + '" class="btn btn-primary btn-sm">Approve</a>';
					}
				}
			],
			pageLength: 25,
			lengthChange: false,
			searching: false,
			dom: 'rtip',
			order: [],
			language: {
				processing: '<i class="fa fa-spinner fa-spin"></i> Memproses...',
				emptyTable: 'Belum ada pengajuan yang menunggu approval.',
				zeroRecords: 'Tidak ada pengajuan yang cocok.',
				info: 'Menampilkan _START_ - _END_ dari _TOTAL_ entri',
				infoEmpty: 'Menampilkan 0 - 0 dari 0 entri',
				paginate: {
					first: 'Pertama',
					last: 'Terakhir',
					next: 'Selanjutnya',
					previous: 'Sebelumnya'
				}
			},
			drawCallback: function() {
				var json = this.api().ajax.json();
				if (json) $('#appr_count_pending').text(json.recordsTotal || 0);
			}
		});

		$('#appr_search').on('keyup', function() {
			apprTable.draw();
		});
	});
</script>