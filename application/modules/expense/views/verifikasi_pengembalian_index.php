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
	.table-verif thead th {
		background-color: #2b6cb0;
		color: #fff;
		font-weight: 600;
		font-size: 13px;
		border: 1px solid #2b6cb0 !important;
		vertical-align: middle !important;
		text-align: center;
	}
	.table-verif tbody td {
		font-size: 13px;
		vertical-align: middle !important;
	}
	.btn-rounded {
		border-radius: 4px;
	}
</style>

<div class="box box-custom">
	<div class="box-custom-header">
		<h4 class="box-custom-title">
			<i class="fa fa-check-square-o text-primary"></i> Antrian Verifikasi Pengembalian Kasbon
		</h4>
		<div>
			<button class="btn btn-default btn-sm btn-rounded" onclick="window.location.reload();">
				<i class="fa fa-refresh"></i> Refresh
			</button>
		</div>
	</div>

	<div class="box-body" style="padding: 20px;">
		<div class="alert alert-info" style="border-radius:6px; margin-bottom:15px; font-size:13px; background:#ebf8fa; border:1px solid #bce8f1; color:#236174;">
			<i class="fa fa-info-circle"></i> <b>Petunjuk Verifikasi:</b>
			Halaman ini menampilkan laporan expense dengan kondisi <b>Lebih Kasbon</b> yang buktinya telah diunggah oleh pelapor. Finance wajib memverifikasi keabsahan bukti transfer, pencocokan mutasi rekening bank perusahaan, dan kesesuaian nominal sebelum melakukan posting Jurnal 2.
		</div>

		<div class="table-responsive">
			<table id="tableVerifikasi" class="table table-bordered table-striped table-verif" width="100%">
				<thead>
					<tr>
						<th width="30">#</th>
						<th width="130">No. Dokumen</th>
						<th width="120">No. Kasbon</th>
						<th width="90">Tanggal</th>
						<th width="130">Nama Pelapor</th>
						<th>Keperluan / Keterangan</th>
						<th width="130" class="text-right">Lebih Kasbon (Rp)</th>
						<th width="140">Bank Tujuan Transfer</th>
						<th width="100">Tgl Transfer</th>
						<th width="120" class="text-center">Status</th>
						<th width="90" class="text-center">Aksi</th>
					</tr>
				</thead>
				<tbody>
				</tbody>
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
	$(document).ready(function() {
		load_table_verifikasi();
	});

	function load_table_verifikasi() {
		$('#tableVerifikasi').DataTable({
			serverSide: true,
			processing: true,
			destroy: true,
			paging: true,
			ajax: {
				type: 'POST',
				url: siteurl + 'expense/get_dat_verifikasi_pengembalian',
				cache: false,
				dataType: 'json'
			},
			columns: [
				{ data: 'no', orderable: false, className: 'text-center' },
				{ data: 'no_doc' },
				{ data: 'no_kasbon' },
				{ data: 'tgl_doc', className: 'text-center' },
				{ data: 'nmuser' },
				{ data: 'informasi' },
				{ data: 'lebih_bayar', className: 'text-right' },
				{ data: 'bank_name' },
				{ data: 'transfer_tanggal', className: 'text-center' },
				{ data: 'status', className: 'text-center' },
				{ data: 'action', orderable: false, className: 'text-center' }
			],
			order: [[1, 'desc']]
		});
	}

	function data_verif_detail(id) {
		if (id) {
			$(".box").hide();
			$("#form-data").show();
			$("#form-data").load(siteurl + 'expense/verifikasi_pengembalian_detail/' + id);
		}
	}
</script>
