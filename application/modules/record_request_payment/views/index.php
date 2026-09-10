<?php
$ENABLE_VIEW = has_permission('Request_Payment.View');
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.11/css/dataTables.bootstrap.min.css">

<style>
	#table_record thead th {
		background-color: #3c8dbc;
		color: #fff;
		font-size: 12px;
		text-align: center;
		vertical-align: middle;
		white-space: nowrap;
	}

	#table_record tbody td {
		font-size: 12.5px;
		vertical-align: middle;
	}

	.rec-status-done {
		background: #E1F3EA;
		color: #3FA772;
		font-size: 11px;
		font-weight: 700;
		padding: 4px 10px;
		border-radius: 10px;
	}

	.rec-toolbar {
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin-bottom: 12px;
		flex-wrap: wrap;
		gap: 10px;
	}

	.rec-toolbar input {
		padding: 6px 12px;
		font-size: 13px;
		border: 1px solid #ccc;
		border-radius: 3px;
		width: 280px;
	}

	.mini-btn {
		border: 1px solid #ccc;
		background: #fff;
		padding: 4px 10px;
		border-radius: 4px;
		font-size: 11.5px;
		font-weight: 600;
		cursor: pointer;
		text-decoration: none;
	}

	.mini-btn.view {
		color: #3D6690;
		border-color: #BFD6EA;
	}

	.mini-btn.print {
		color: #3A4656;
		border-color: #C9D3DC;
	}
</style>

<div class="box">
	<div class="box-header with-border">
		<ol class="breadcrumb" style="background:none;padding:0;margin-bottom:5px;font-size:12px;">
			<li><a href="javascript:void(0);">Finance</a></li>
			<li class="active">Record Request Payment</li>
		</ol>
		<h3 class="box-title" style="font-size: 22px; font-weight: 600;">Record Request Payment</h3>
	</div>
	<div class="box-body">

		<div class="rec-toolbar">
			<div class="text-muted">Riwayat seluruh pengajuan yang sudah diputuskan (final).</div>
			<input type="text" id="rec_search" placeholder="Cari company atau no. pengajuan...">
		</div>

		<div style="overflow-x:auto;border:1px solid #ddd;">
			<table id="table_record" class="table table-bordered table-striped" style="width:100%;">
				<thead>
					<tr>
						<th>NO. PENGAJUAN</th>
						<th>COMPANY</th>
						<th>TANGGAL PENGAJUAN</th>
						<th>TANGGAL DIPUTUSKAN</th>
						<th style="width:150px;">TOTAL DIBAYARKAN</th>
						<th style="width:140px;">STATUS</th>
						<th style="width:120px;">ACTION</th>
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
		var recTable = $('#table_record').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: '<?= site_url("record_request_payment/get_data_record"); ?>',
				type: 'POST',
				data: function(d) {
					d.search = {
						value: $('#rec_search').val()
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
					data: 'decided_at'
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
					render: function(row) {
						return '<span class="rec-status-done">Selesai</span><br><small class="text-muted">' + row.jumlah_approved + ' dokumen disetujui</small>';
					}
				},
				{
					data: 'aksi',
					orderable: false,
					className: 'text-center',
					render: function(id) {
						return '<a href="<?= site_url("record_request_payment/detail"); ?>/' + id + '" class="mini-btn view">View</a> ' +
							'<a href="<?= site_url("record_request_payment/export_excel"); ?>/' + id + '" class="mini-btn print"><i class="fa fa-print"></i> Print</a>';
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
				emptyTable: 'Belum ada pengajuan yang tercatat.',
				zeroRecords: 'Tidak ada record yang cocok.',
				info: 'Menampilkan _START_ - _END_ dari _TOTAL_ entri',
				infoEmpty: 'Menampilkan 0 - 0 dari 0 entri',
				paginate: {
					first: 'Pertama',
					last: 'Terakhir',
					next: 'Selanjutnya',
					previous: 'Sebelumnya'
				}
			}
		});

		$('#rec_search').on('keyup', function() {
			recTable.draw();
		});
	});
</script>
