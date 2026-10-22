<?php
$ENABLE_ADD     = has_permission('Expense.Add');
$ENABLE_MANAGE  = has_permission('Expense.Manage');
$ENABLE_VIEW    = has_permission('Expense.View');
$ENABLE_DELETE  = has_permission('Expense.Delete');

$cnt_pengembalian = isset($count_pengembalian) ? (int)$count_pengembalian : 0;
$cnt_lebih = isset($count_lebih_expense) ? (int)$count_lebih_expense : 0;
$bank_options = isset($bank_options) && is_array($bank_options) ? $bank_options : [];
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
		text-align: center;
	}
	.table-index tbody td {
		font-size: 13px;
		vertical-align: middle !important;
	}
	.table-purple thead th {
		background-color: #6f42c1;
		color: #fff;
		font-weight: 600;
		font-size: 13px;
		border: 1px solid #5a32a3 !important;
		vertical-align: middle !important;
		text-align: center;
	}
	.table-green thead th {
		background-color: #00a65a;
		color: #fff;
		font-weight: 600;
		font-size: 13px;
		border: 1px solid #008d4c !important;
		vertical-align: middle !important;
		text-align: center;
	}
	.btn-rounded {
		border-radius: 4px;
	}
	.nav-tabs-custom > .nav-tabs > li.active {
		border-top-color: #3c8dbc;
	}
	.nav-tabs-custom > .nav-tabs > li > a {
		font-size: 14px;
		font-weight: 600;
		color: #4a5568;
	}
	.nav-tabs-custom > .nav-tabs > li.active > a {
		color: #2b6cb0;
	}
</style>

<div class="nav-tabs-custom box-custom" style="padding: 10px 15px 20px 15px;">
	<ul class="nav nav-tabs" style="border-bottom: 2px solid #e2e8f0; margin-bottom: 15px;">
		<li class="active">
			<a href="#tab_laporan" data-toggle="tab">
				<i class="fa fa-list text-primary"></i> Laporan Expense
			</a>
		</li>
		<li>
			<a href="#tab_pengembalian" data-toggle="tab">
				<i class="fa fa-reply" style="color:#6f42c1;"></i> Pengembalian Kasbon
				<?php if ($cnt_pengembalian > 0): ?>
					<span class="badge" style="background:#6f42c1; margin-left:5px;"><?= $cnt_pengembalian ?></span>
				<?php endif; ?>
			</a>
		</li>
		<li>
			<a href="#tab_reimburse" data-toggle="tab">
				<i class="fa fa-money text-success"></i> Lebih Expense (Reimburse ke Karyawan)
				<?php if ($cnt_lebih > 0): ?>
					<span class="badge bg-green" style="margin-left:5px;"><?= $cnt_lebih ?></span>
				<?php endif; ?>
			</a>
		</li>
	</ul>

	<div class="tab-content" style="padding: 0;">
		<!-- ============================================================== -->
		<!-- TAB 1: LAPORAN EXPENSE (EKSISTING)                             -->
		<!-- ============================================================== -->
		<div class="tab-pane active" id="tab_laporan">
			<div class="box-custom-header" style="padding: 10px 0 15px 0;">
				<h4 class="box-custom-title"><i class="fa fa-file-text-o text-primary"></i> Daftar Laporan &amp; Pertanggungjawaban Expense</h4>
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

			<div class="table-responsive">
				<table id="mytabledata" class="table table-bordered table-striped table-index" width="100%">
					<thead>
						<tr>
							<th width="30">#</th>
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

		<!-- ============================================================== -->
		<!-- TAB 2: PENGEMBALIAN KASBON (USER SIDE - LEBIH KASBON)          -->
		<!-- ============================================================== -->
		<div class="tab-pane" id="tab_pengembalian">
			<div class="box-custom-header" style="padding: 10px 0 15px 0;">
				<h4 class="box-custom-title" style="color:#6f42c1;">
					<i class="fa fa-reply"></i> Antrian Pengembalian Kelebihan Kasbon ke Rekening Perusahaan
				</h4>
				<button class="btn btn-default btn-sm btn-rounded" type="button" onclick="load_table_pengembalian();">
					<i class="fa fa-refresh"></i> Refresh
				</button>
			</div>

			<div class="alert alert-info" style="border-radius:6px; font-size:13px; background:#fbf5ff; border:1px solid #d6bcfa; color:#553c9a;">
				<i class="fa fa-info-circle"></i> <b>Petunjuk Pengembalian Kasbon:</b>
				Laporan expense di bawah telah <b>disetujui penuh oleh Direktur</b> dengan realisasi pengeluaran lebih kecil dari kasbon yang diterima (<b>Lebih Kasbon</b>). Silakan transfer sisa uang ke rekening bank perusahaan dan klik tombol <b>Upload Bukti</b> agar Finance dapat memverifikasi pengembalian Anda.
			</div>

			<div class="table-responsive">
				<table id="tablePengembalian" class="table table-bordered table-striped table-purple" width="100%">
					<thead>
						<tr>
							<th width="30">#</th>
							<th width="130">No. Dokumen</th>
							<th width="120">No. Kasbon</th>
							<th width="90">Tanggal</th>
							<th>Keterangan / Keperluan</th>
							<th width="110" class="text-right">Total Kasbon</th>
							<th width="110" class="text-right">Realisasi</th>
							<th width="130" class="text-right">Lebih Kasbon (Rp)</th>
							<th width="160" class="text-center">Status</th>
							<th width="110" class="text-center">Aksi</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
		</div>

		<!-- ============================================================== -->
		<!-- TAB 3: LEBIH EXPENSE (REIMBURSE KANTOR KE KARYAWAN)           -->
		<!-- ============================================================== -->
		<div class="tab-pane" id="tab_reimburse">
			<div class="box-custom-header" style="padding: 10px 0 15px 0;">
				<h4 class="box-custom-title" style="color:#00a65a;">
					<i class="fa fa-money"></i> Status Reimbursement Lebih Expense (Kurang Bayar Kantor ke Karyawan)
				</h4>
				<button class="btn btn-default btn-sm btn-rounded" type="button" onclick="load_table_lebih_expense();">
					<i class="fa fa-refresh"></i> Refresh
				</button>
			</div>

			<div class="alert alert-success" style="border-radius:6px; font-size:13px; background:#f0fff4; border:1px solid #c6f6d5; color:#22543d;">
				<i class="fa fa-info-circle"></i> <b>Petunjuk Reimbursement:</b>
				Laporan expense di bawah memiliki realisasi pengeluaran yang lebih besar dari kasbon (<b>Lebih Expense</b>). Nilai selisih secara otomatis masuk ke antrian <b>Request Payment</b> untuk proses persetujuan dan pencairan transfer penggantian uang dari kantor ke rekening Anda.
			</div>

			<div class="table-responsive">
				<table id="tableLebihExpense" class="table table-bordered table-striped table-green" width="100%">
					<thead>
						<tr>
							<th width="30">#</th>
							<th width="130">No. Dokumen Expense</th>
							<th width="120">No. Kasbon</th>
							<th width="90">Tanggal</th>
							<th>Keterangan Laporan</th>
							<th width="140" class="text-right">Nominal Reimburse (Rp)</th>
							<th width="180" class="text-center">Status Request Payment</th>
							<th width="90" class="text-center">Aksi</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
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

<!-- MODAL UPLOAD BUKTI PENGEMBALIAN KASBON (TAB 2) -->
<div class="modal fade" id="modalUploadPengembalian" tabindex="-1" role="dialog" aria-labelledby="modalUploadLabel" aria-hidden="true">
	<div class="modal-dialog" style="width: 600px;">
		<div class="modal-content" style="border-radius: 8px;">
			<div class="modal-header" style="background:#6f42c1; color:#fff; border-radius: 8px 8px 0 0;">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff; opacity:0.8;">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title" id="modalUploadLabel">
					<i class="fa fa-upload"></i> Upload Bukti Pengembalian Kelebihan Kasbon
				</h4>
			</div>
			<form id="formUploadPengembalian" enctype="multipart/form-data">
				<input type="hidden" name="id_expense" id="upload_id_expense" value="">
				<div class="modal-body" style="padding: 20px;">
					<div class="well well-sm" style="background:#f8fafc; border-radius:6px; margin-bottom:15px; font-size:13px;">
						<div style="display:flex; justify-content:space-between; margin-bottom:4px;">
							<span>No. Dokumen:</span>
							<b id="upload_no_doc">-</b>
						</div>
						<div style="display:flex; justify-content:space-between; margin-bottom:4px;">
							<span>No. Kasbon:</span>
							<b id="upload_no_kasbon">-</b>
						</div>
						<div style="display:flex; justify-content:space-between; font-size:14px; color:#6f42c1; border-top:1px dashed #cbd5e0; padding-top:4px;">
							<b>Sisa Lebih Kasbon (Wajib Dikembalikan):</b>
							<b id="upload_nominal_lebih">Rp 0</b>
						</div>
					</div>

					<div class="form-group">
						<label style="font-size:13px; font-weight:600; color:#2d3748;">Bank Tujuan Transfer Perusahaan <b class="text-danger">*</b></label>
						<select name="transfer_coa_bank" id="transfer_coa_bank" class="form-control input-sm select2" style="width:100%;" required>
							<option value="">-- Pilih Rekening Bank Perusahaan --</option>
							<?php foreach ($bank_options as $bk): ?>
								<option value="<?= htmlspecialchars($bk->no_perkiraan) ?>">
									<?= htmlspecialchars($bk->no_perkiraan . ' - ' . $bk->nama) ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="row">
						<div class="col-sm-6">
							<div class="form-group">
								<label style="font-size:13px; font-weight:600; color:#2d3748;">Tanggal Transfer <b class="text-danger">*</b></label>
								<input type="text" name="transfer_tanggal" id="transfer_tanggal" class="form-control input-sm tanggal" value="<?= date('Y-m-d') ?>" required autocomplete="off">
							</div>
						</div>
						<div class="col-sm-6">
							<div class="form-group">
								<label style="font-size:13px; font-weight:600; color:#2d3748;">Nominal Transfer (Rp) <b class="text-danger">*</b></label>
								<input type="text" name="transfer_jumlah" id="transfer_jumlah" class="form-control input-sm divide text-right" required>
							</div>
						</div>
					</div>

					<div class="form-group" style="margin-bottom:0;">
						<label style="font-size:13px; font-weight:600; color:#2d3748;">File Bukti Transfer <b class="text-danger">*</b> <small class="text-muted">(JPG, PNG, PDF &mdash; boleh lebih dari 1 file)</small></label>
						<input type="file" name="bukti_transfer[]" id="bukti_transfer" class="form-control input-sm" multiple required accept=".jpg,.jpeg,.png,.pdf">
						<small class="text-muted" style="display:block; margin-top:4px;">Pastikan bukti transfer mencantumkan nomor referensi, tanggal, dan nominal yang sesuai.</small>
					</div>
				</div>
				<div class="modal-footer" style="background:#f8fafc; border-radius:0 0 8px 8px;">
					<button type="button" class="btn btn-default btn-sm btn-rounded" data-dismiss="modal">Batal</button>
					<button type="submit" class="btn btn-primary btn-sm btn-rounded" style="background:#6f42c1; border-color:#6f42c1;" id="btn_submit_pengembalian">
						<i class="fa fa-send"></i> Kirim Bukti Pengembalian
					</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- DataTables & SweetAlert -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datepicker/bootstrap-datepicker.js') ?>"></script>
<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>
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
		$('.divide').divide();
		$('.tanggal').datepicker({
			format: 'yyyy-mm-dd',
			autoclose: true,
			todayHighlight: true
		});

		datatables();

		// Event saat tab berpindah: load datatables masing-masing tab
		$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
			var target = $(e.target).attr("href");
			if (target === '#tab_pengembalian') {
				load_table_pengembalian();
			} else if (target === '#tab_reimburse') {
				load_table_lebih_expense();
			}
		});
	});

	function data_add() {
		var companyName = (document.title.indexOf('|') !== -1) ? document.title.split('|')[0].trim() : 'SENDIGS SS';
		document.title = companyName + ' | Expense';
		$('.content-header h1').html('<i class="fa fa-cubes"></i> Expense');
		$(".box, .nav-tabs-custom").hide();
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
		$(".box, .nav-tabs-custom").hide();
		$("#form-data").show();
		$("#form-data").load(url_add_report + encodeURIComponent(no_doc_kasbon));
	}

	function data_edit(id) {
		if (id != "") {
			$(".box, .nav-tabs-custom").hide();
			$("#form-data").show();
			$("#form-data").load(url_edit + id);
		}
	}

	function data_view(id) {
		if (id != "") {
			$(".box, .nav-tabs-custom").hide();
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
		$('#mytabledata').dataTable({
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

	// ==============================================================
	// TAB 2: PENGEMBALIAN KASBON (DATATABLES & MODAL UPLOAD)
	// ==============================================================
	function load_table_pengembalian() {
		if ($.fn.DataTable.isDataTable('#tablePengembalian')) {
			$('#tablePengembalian').DataTable().destroy();
		}
		$('#tablePengembalian').DataTable({
			serverSide: true,
			processing: true,
			destroy: true,
			paging: true,
			ajax: {
				type: 'POST',
				url: siteurl + 'expense/get_dat_pengembalian_kasbon',
				cache: false,
				dataType: 'json'
			},
			columns: [
				{ data: 'no', orderable: false, className: 'text-center' },
				{ data: 'no_doc' },
				{ data: 'no_kasbon' },
				{ data: 'tgl_doc', className: 'text-center' },
				{ data: 'informasi' },
				{ data: 'total_kasbon', className: 'text-right' },
				{ data: 'realisasi', className: 'text-right' },
				{ data: 'lebih_bayar', className: 'text-right' },
				{ data: 'status', className: 'text-center' },
				{ data: 'action', orderable: false, className: 'text-center' }
			],
			order: [[1, 'desc']]
		});
	}

	$(document).on('click', '.btn-upload-pengembalian', function() {
		var id = $(this).data('id');
		var doc = $(this).data('doc');
		var kasbon = $(this).data('kasbon');
		var nominal = parseFloat($(this).data('nominal')) || 0;

		$('#upload_id_expense').val(id);
		$('#upload_no_doc').text(doc);
		$('#upload_no_kasbon').text(kasbon || '-');
		$('#upload_nominal_lebih').text('Rp ' + Number(nominal).toLocaleString('id-ID'));
		$('#transfer_jumlah').val(Number(nominal).toLocaleString('en-US'));
		$('#formUploadPengembalian')[0].reset();
		$('#upload_id_expense').val(id);
		$('#transfer_jumlah').val(Number(nominal).toLocaleString('en-US'));

		$('#modalUploadPengembalian').modal('show');
	});

	$('#formUploadPengembalian').on('submit', function(e) {
		e.preventDefault();
		var bank = $('#transfer_coa_bank').val();
		if (!bank) {
			Swal.fire("Perhatian!", "Bank Tujuan Perusahaan wajib dipilih!", "warning");
			return false;
		}

		var fileInput = document.getElementById('bukti_transfer');
		if (!fileInput.files || fileInput.files.length === 0) {
			Swal.fire("Perhatian!", "Bukti transfer pengembalian dana wajib dilampirkan!", "warning");
			return false;
		}

		var formdata = new FormData(this);
		$('#btn_submit_pengembalian').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Mengirim...');

		$.ajax({
			url: siteurl + 'expense/save_pengembalian_dana',
			type: 'POST',
			data: formdata,
			processData: false,
			contentType: false,
			dataType: 'json',
			success: function(res) {
				$('#btn_submit_pengembalian').prop('disabled', false).html('<i class="fa fa-send"></i> Kirim Bukti Pengembalian');
				if (res && res.save) {
					$('#modalUploadPengembalian').modal('hide');
					Swal.fire({
						title: "Berhasil!",
						text: res.message,
						icon: "success"
					}).then(() => {
						load_table_pengembalian();
						window.location.reload();
					});
				} else {
					Swal.fire({
						title: "Gagal!",
						text: (res && res.message) ? res.message : "Gagal mengunggah bukti pengembalian.",
						icon: "error"
					});
				}
			},
			error: function() {
				$('#btn_submit_pengembalian').prop('disabled', false).html('<i class="fa fa-send"></i> Kirim Bukti Pengembalian');
				Swal.fire("Gagal!", "Terjadi kesalahan pada server saat mengirim data.", "error");
			}
		});
	});

	// ==============================================================
	// TAB 3: LEBIH EXPENSE (DATATABLES MONITORING REQUEST PAYMENT)
	// ==============================================================
	function load_table_lebih_expense() {
		if ($.fn.DataTable.isDataTable('#tableLebihExpense')) {
			$('#tableLebihExpense').DataTable().destroy();
		}
		$('#tableLebihExpense').DataTable({
			serverSide: true,
			processing: true,
			destroy: true,
			paging: true,
			ajax: {
				type: 'POST',
				url: siteurl + 'expense/get_dat_lebih_expense',
				cache: false,
				dataType: 'json'
			},
			columns: [
				{ data: 'no', orderable: false, className: 'text-center' },
				{ data: 'no_doc' },
				{ data: 'no_kasbon' },
				{ data: 'tgl_doc', className: 'text-center' },
				{ data: 'informasi' },
				{ data: 'kurang_bayar', className: 'text-right' },
				{ data: 'status_reimburse', className: 'text-center' },
				{ data: 'action', orderable: false, className: 'text-center' }
			],
			order: [[1, 'desc']]
		});
	}
</script>

<!-- Sync: 2026-10-22 Updated Expense Report Flow -->
