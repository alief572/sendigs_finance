<?php
$ENABLE_ADD     = has_permission('Expense.Add');
$ENABLE_MANAGE  = has_permission('Expense.Manage');
$ENABLE_VIEW    = has_permission('Expense.View');
$ENABLE_DELETE  = has_permission('Expense.Delete');
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
		<h4 class="box-custom-title"><i class="fa fa-money text-primary"></i> Daftar Laporan & Pertanggungjawaban Expense</h4>
		<?php if ($ENABLE_ADD) : ?>
			<div style="display: flex; gap: 8px;">
				<button class="btn btn-success btn-sm btn-rounded" type="button" onclick="data_add()" title="Buat Pengeluaran Langsung / Manual">
					<i class="fa fa-plus">&nbsp;</i> Tambah Expense
				</button>
				<button class="btn btn-primary btn-sm btn-rounded" type="button" onclick="data_add_report()" title="Pertanggungjawaban Kasbon Sendigs">
					<i class="fa fa-ticket">&nbsp;</i> Tambah Expense Report
				</button>
			</div>
		<?php endif; ?>
	</div>
	<!-- /.box-header -->
	<div class="box-body" style="padding: 20px;">
		<div class="table-responsive">
			<table id="mytabledata" class="table table-bordered table-striped table-index" width="100%">
				<thead>
					<tr>
						<th width="30" class="text-center">#</th>
						<th width="130">No Dokumen</th>
						<th width="90">Tanggal</th>
						<th width="130">Nama Pemohon</th>
						<th width="120" class="text-right">Total Realisasi</th>
						<th width="110">Approval</th>
						<th width="120">Approval Date</th>
						<th>Keterangan</th>
						<th width="160" class="text-center">Status</th>
						<th width="100" class="text-center">Aksi</th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
	</div>
</div>

<div id="form-data"></div>

<!-- MODAL PILIH KASBON UNTUK EXPENSE REPORT -->
<div class="modal fade" id="modalKasbon" tabindex="-1" role="dialog" aria-labelledby="modalKasbonLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" style="width: 85%;">
		<div class="modal-content" style="border-radius: 8px;">
			<div class="modal-header bg-primary" style="border-radius: 8px 8px 0 0;">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title" id="modalKasbonLabel"><i class="fa fa-ticket"></i> Pilih Kasbon Sendigs untuk Dibuatkan Expense Report</h4>
			</div>
			<div class="modal-body" style="padding: 15px;">
				<div class="table-responsive">
					<table class="table table-bordered table-striped" id="tableKasbon" width="100%">
						<thead>
							<tr class="bg-gray">
								<th width="30" class="text-center">#</th>
								<th width="140">No. Kasbon</th>
								<th width="100">Tanggal</th>
								<th>Keperluan</th>
								<th>Keterangan</th>
								<th width="130" class="text-right">Jumlah (Rp)</th>
								<th width="110" class="text-center">Aksi</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default btn-rounded" data-dismiss="modal">Tutup</button>
			</div>
		</div>
	</div>
</div>

<!-- DataTables & SweetAlert -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- page script -->
<script type="text/javascript">
	var url_add = siteurl + 'expense/create/';
	var url_add_report = siteurl + 'expense/create_report/';
	var url_edit = siteurl + 'expense/edit/';
	var url_delete = siteurl + 'expense/delete/';
	var url_view = siteurl + 'expense/view/';

	var all = "<?= (isset($all)) ? $all : '' ?>";

	$(document).ready(function() {
		datatables();
	});

	function data_add() {
		var companyName = (document.title.indexOf('|') !== -1) ? document.title.split('|')[0].trim() : 'SENDIGS SS';
		document.title = companyName + ' | Expense';
		$('.content-header h1').html('<i class="fa fa-cubes"></i> Expense');
		$(".box").hide();
		$("#form-data").show();
		$("#form-data").load(url_add);
	}

	function data_add_report() {
		$.ajax({
			url: siteurl + 'expense/get_kasbon',
			type: "POST",
			dataType: "json",
			success: function(data) {
				if ($.fn.DataTable.isDataTable('#tableKasbon')) {
					$('#tableKasbon').DataTable().destroy();
				}

				var tbody = '';
				if (data && data.length > 0) {
					for (var i = 0; i < data.length; i++) {
						tbody += '<tr>';
						tbody += '<td class="text-center">' + (i + 1) + '</td>';
						tbody += '<td><b>' + data[i].no_doc + '</b></td>';
						tbody += '<td>' + data[i].tgl_doc + '</td>';
						tbody += '<td>' + (data[i].keperluan || '-') + '</td>';
						tbody += '<td>' + (data[i].keterangan || '-') + '</td>';
						tbody += '<td class="text-right" style="font-weight:bold; color:#2e59d9;">' + Number(data[i].jumlah_kasbon).toLocaleString('en-US') + '</td>';
						tbody += '<td class="text-center"><button type="button" class="btn btn-primary btn-xs btn-rounded btn-pilih-kasbon-report" data-doc="' + data[i].no_doc + '"><i class="fa fa-arrow-right"></i> Proses Report</button></td>';
						tbody += '</tr>';
					}
				}
				$('#tableKasbon tbody').html(tbody);
				$('#tableKasbon').DataTable({
					paging: true,
					pageLength: 10,
					lengthMenu: [10, 25, 50, 100],
					ordering: true,
					searching: true
				});
				$('#modalKasbon').modal('show');
			},
			error: function() {
				Swal.fire({
					title: "Gagal!",
					text: 'Gagal mengambil data kasbon dari server.',
					icon: "warning"
				});
			}
		});
	}

	$(document).on('click', '.btn-pilih-kasbon-report', function() {
		var no_doc_kasbon = $(this).data('doc');
		select_kasbon_report(no_doc_kasbon);
	});

	function select_kasbon_report(no_doc_kasbon) {
		$('#modalKasbon').modal('hide');
		var companyName = (document.title.indexOf('|') !== -1) ? document.title.split('|')[0].trim() : 'SENDIGS SS';
		document.title = companyName + ' | Expense Report';
		$('.content-header h1').html('<i class="fa fa-ticket"></i> Expense Report');
		$(".box").hide();
		$("#form-data").show();
		$("#form-data").load(url_add_report + encodeURIComponent(no_doc_kasbon));
	}

	function data_edit(id) {
		if (id != "") {
			$(".box").hide();
			$("#form-data").show();
			$("#form-data").load(url_edit + id);
		}
	}

	function data_view(id) {
		if (id != "") {
			$(".box").hide();
			$("#form-data").show();
			$("#form-data").load(url_view + id);
		}
	}

	function data_delete(id) {
		Swal.fire({
			title: 'Hapus Dokumen Expense?',
			text: 'Data yang dihapus tidak dapat dikembalikan!',
			icon: 'warning',
			showCancelButton: true,
			confirmButtonText: 'Ya, Hapus!',
			cancelButtonText: 'Batal',
			confirmButtonColor: '#d33'
		}).then((result) => {
			if (result.isConfirmed) {
				$.post(url_delete + id, function(res) {
					if (res.delete === true) {
						Swal.fire('Terhapus!', 'Dokumen expense telah berhasil dihapus.', 'success');
						datatables();
					} else {
						Swal.fire('Gagal!', 'Gagal menghapus dokumen expense.', 'error');
					}
				}, 'json');
			}
		});
	}

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
			columns: [
				{ data: 'no', className: 'text-center' },
				{ data: 'no_doc' },
				{ data: 'tgl_doc' },
				{ data: 'nama' },
				{ data: 'total_realisasi', className: 'text-right' },
				{ data: 'approval' },
				{ data: 'approval_date' },
				{ data: 'keterangan' },
				{ data: 'status', className: 'text-center' },
				{ data: 'action', className: 'text-center' }
			]
		});
	}
</script>
