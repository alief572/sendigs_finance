<?php
$ENABLE_ADD     = has_permission('Expense.Add');
$ENABLE_MANAGE  = has_permission('Expense.Manage');
$ENABLE_VIEW    = has_permission('Expense.View');
$ENABLE_DELETE  = has_permission('Expense.Delete');

$cnt_pengembalian = isset($count_pengembalian) ? (int)$count_pengembalian : 0;
$cnt_pk_upload    = isset($count_pk_upload) ? (int)$count_pk_upload : $cnt_pengembalian;
$cnt_pk_verif     = isset($count_pk_verif) ? (int)$count_pk_verif : 0;
$cnt_pk_done      = isset($count_pk_done) ? (int)$count_pk_done : 0;

$cnt_lebih        = isset($count_lebih_expense) ? (int)$count_lebih_expense : 0;
$cnt_rb_belum     = isset($count_rb_belum) ? (int)$count_rb_belum : 0;
$cnt_rb_menunggu  = isset($count_rb_menunggu) ? (int)$count_rb_menunggu : 0;
$cnt_rb_selesai   = isset($count_rb_selesai) ? (int)$count_rb_selesai : 0;

$bank_options     = isset($bank_options) && is_array($bank_options) ? $bank_options : [];
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

	/* Sub-tab Pills (Anti-slop clean design system) */
	.subtab-pills {
		display: flex;
		gap: 8px;
		margin-bottom: 14px;
		margin-top: 10px;
		flex-wrap: wrap;
		align-items: center;
	}
	.subtab-pill-btn {
		display: inline-flex;
		align-items: center;
		gap: 7px;
		border: 1px solid #cbd5e1;
		background: #ffffff;
		color: #475569;
		padding: 6px 14px;
		border-radius: 20px;
		font-size: 12.5px;
		font-weight: 600;
		cursor: pointer;
		transition: all 0.15s ease;
		outline: none;
	}
	.subtab-pill-btn:hover {
		background: #f8fafc;
		border-color: #94a3b8;
		color: #1e293b;
	}
	.subtab-pill-btn .badge-pill-count {
		background: #f1f5f9;
		border: 1px solid #e2e8f0;
		color: #64748b;
		border-radius: 10px;
		font-size: 11px;
		font-weight: 700;
		padding: 1px 7px;
	}
	/* Active state for Pengembalian Kasbon */
	.subtab-pill-btn.active-purple {
		background: #6f42c1;
		border-color: #5a32a3;
		color: #ffffff;
	}
	.subtab-pill-btn.active-purple .badge-pill-count {
		background: #ffffff;
		border-color: #ffffff;
		color: #6f42c1;
	}
	/* Active state for Lebih Expense */
	.subtab-pill-btn.active-green {
		background: #00a65a;
		border-color: #008d4c;
		color: #ffffff;
	}
	.subtab-pill-btn.active-green .badge-pill-count {
		background: #ffffff;
		border-color: #ffffff;
		color: #00a65a;
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
				<span class="badge" id="badge_tab_pengembalian" style="background:#6f42c1; margin-left:5px; <?= ($cnt_pengembalian > 0 ? '' : 'display:none;') ?>"><?= $cnt_pengembalian ?></span>
			</a>
		</li>
		<li>
			<a href="#tab_reimburse" data-toggle="tab">
				<i class="fa fa-money text-success"></i> Lebih Expense (Reimburse ke Karyawan)
				<span class="badge bg-green" id="badge_tab_reimburse" style="margin-left:5px; <?= ($cnt_lebih > 0 ? '' : 'display:none;') ?>"><?= $cnt_lebih ?></span>
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
					<i class="fa fa-reply"></i> Daftar Piutang Kelebihan Kasbon
				</h4>
				<button class="btn btn-default btn-sm btn-rounded" type="button" onclick="load_table_pengembalian();">
					<i class="fa fa-refresh"></i> Refresh
				</button>
			</div>

			<!-- Sub-tab Pills Pengembalian Kasbon -->
			<div class="subtab-pills" role="tablist">
				<button type="button" class="subtab-pill-btn active-purple" data-subtab-pk="upload" onclick="switch_subtab_pk('upload', this)">
					Perlu Upload <span class="badge-pill-count" id="badge_pk_upload"><?= $cnt_pk_upload ?></span>
				</button>
				<button type="button" class="subtab-pill-btn" data-subtab-pk="verif" onclick="switch_subtab_pk('verif', this)">
					Menunggu Verifikasi <span class="badge-pill-count" id="badge_pk_verif"><?= $cnt_pk_verif ?></span>
				</button>
				<button type="button" class="subtab-pill-btn" data-subtab-pk="done" onclick="switch_subtab_pk('done', this)">
					Selesai <span class="badge-pill-count" id="badge_pk_done"><?= $cnt_pk_done ?></span>
				</button>
			</div>

			<div class="table-responsive">
				<table id="tablePengembalian" class="table table-bordered table-striped table-purple" width="100%">
					<thead>
						<tr>
							<th width="30">#</th>
							<th width="130">No. Dokumen</th>
							<th width="120">No. Kasbon</th>
							<th>Keperluan / Keterangan</th>
							<th width="110">Tanggal Approval</th>
							<th width="130" class="text-right">Kelebihan Kasbon (Rp)</th>
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
					<i class="fa fa-money"></i> Monitoring Lebih Expense (Reimburse ke Karyawan)
				</h4>
				<button class="btn btn-default btn-sm btn-rounded" type="button" onclick="load_table_lebih_expense();">
					<i class="fa fa-refresh"></i> Refresh
				</button>
			</div>

			<!-- Sub-tab Pills Lebih Expense -->
			<div class="subtab-pills" role="tablist">
				<button type="button" class="subtab-pill-btn active-green" data-subtab-rb="belum" onclick="switch_subtab_rb('belum', this)">
					Menunggu Diajukan <span class="badge-pill-count" id="badge_rb_belum"><?= $cnt_rb_belum ?></span>
				</button>
				<button type="button" class="subtab-pill-btn" data-subtab-rb="menunggu" onclick="switch_subtab_rb('menunggu', this)">
					Menunggu Approval <span class="badge-pill-count" id="badge_rb_menunggu"><?= $cnt_rb_menunggu ?></span>
				</button>
				<button type="button" class="subtab-pill-btn" data-subtab-rb="selesai" onclick="switch_subtab_rb('selesai', this)">
					Disetujui <span class="badge-pill-count" id="badge_rb_selesai"><?= $cnt_rb_selesai ?></span>
				</button>
			</div>

			<div class="table-responsive">
				<table id="tableLebihExpense" class="table table-bordered table-striped table-green" width="100%">
					<thead>
						<tr>
							<th width="30">#</th>
							<th width="130">No. Dokumen Expense</th>
							<th width="120">No. Kasbon</th>
							<th>Keperluan</th>
							<th width="90">Company</th>
							<th width="110">Tanggal Approval</th>
							<th width="140" class="text-right">Hutang Expense (Rp)</th>
							<th width="180" class="text-center">Status</th>
							<th width="120" class="text-center">No. Pengajuan</th>
							<th>Keterangan</th>
							<th width="80" class="text-center">Aksi</th>
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

<!-- Sub-view form pengembalian dimuat di #form-data -->

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
	// SUB-TAB CONTROLLERS FOR PENGEMBALIAN KASBON & LEBIH EXPENSE
	// ==============================================================
	var current_subtab_pk = 'upload';
	var current_subtab_rb = 'belum';

	function switch_subtab_pk(subtab, el) {
		current_subtab_pk = subtab;
		$('.subtab-pill-btn[data-subtab-pk]').removeClass('active-purple');
		$(el).addClass('active-purple');
		load_table_pengembalian();
	}

	function switch_subtab_rb(subtab, el) {
		current_subtab_rb = subtab;
		$('.subtab-pill-btn[data-subtab-rb]').removeClass('active-green');
		$(el).addClass('active-green');
		load_table_lebih_expense();
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
				dataType: 'json',
				data: function(d) {
					d.subtab = current_subtab_pk;
				},
				dataSrc: function(json) {
					if (json && json.counts) {
						$('#badge_pk_upload').text(json.counts.upload);
						$('#badge_pk_verif').text(json.counts.verif);
						$('#badge_pk_done').text(json.counts.done);
						if (json.counts.upload > 0) {
							$('#badge_tab_pengembalian').text(json.counts.upload).show();
						} else {
							$('#badge_tab_pengembalian').hide();
						}
					}
					return json.data || [];
				}
			},
			columns: [
				{ data: 'no', orderable: false, className: 'text-center' },
				{ data: 'no_doc' },
				{ data: 'no_kasbon' },
				{ data: 'informasi' },
				{ data: 'tgl_approval', className: 'text-center' },
				{ data: 'lebih_bayar', className: 'text-right' },
				{ data: 'status', className: 'text-center' },
				{ data: 'action', orderable: false, className: 'text-center' }
			],
			order: [[1, 'desc']]
		});
	}

	$(document).on('click', '.btn-upload-pengembalian', function() {
		var id = $(this).data('id');
		var is_ro = $(this).data('readonly') ? '1' : '0';
		if (id) {
			$(".box, .nav-tabs-custom").hide();
			$("#form-data").show();
			$("#form-data").load(siteurl + 'expense/form_pengembalian/' + id + '?readonly=' + is_ro);
		}
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
				dataType: 'json',
				data: function(d) {
					d.subtab = current_subtab_rb;
				},
				dataSrc: function(json) {
					if (json && json.counts) {
						$('#badge_rb_belum').text(json.counts.belum);
						$('#badge_rb_menunggu').text(json.counts.menunggu);
						$('#badge_rb_selesai').text(json.counts.selesai);
						var total_active = json.counts.total;
						if (total_active > 0) {
							$('#badge_tab_reimburse').text(total_active).show();
						} else {
							$('#badge_tab_reimburse').hide();
						}
					}
					return json.data || [];
				}
			},
			columns: [
				{ data: 'no', orderable: false, className: 'text-center' },
				{ data: 'no_doc' },
				{ data: 'no_kasbon' },
				{ data: 'informasi' },
				{ data: 'company', className: 'text-center' },
				{ data: 'tgl_approval', className: 'text-center' },
				{ data: 'kurang_bayar', className: 'text-right' },
				{ data: 'status_reimburse', className: 'text-center' },
				{ data: 'no_pengajuan', className: 'text-center' },
				{ data: 'keterangan' },
				{ data: 'action', orderable: false, className: 'text-center' }
			],
			order: [[1, 'desc']]
		});
	}
</script>

<!-- Sync: 2026-10-22 Updated Expense Report Flow -->
