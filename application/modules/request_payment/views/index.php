<?php
$ENABLE_VIEW = has_permission('Request_Payment.View');

// Indonesian months mapping
$bulan_indonesia = [
	1 => 'Januari',
	2 => 'Februari',
	3 => 'Maret',
	4 => 'April',
	5 => 'Mei',
	6 => 'Juni',
	7 => 'Juli',
	8 => 'Agustus',
	9 => 'September',
	10 => 'Oktober',
	11 => 'November',
	12 => 'Desember'
];

$current_month = (int) date('n');
$current_year  = (int) date('Y');
$max_year      = $current_year + 1;
$default_bulan_from = 1; // Januari
?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">

<style>
	.filter-card {
		background: #f9f9f9;
		border: 1px solid #e0e0e0;
		border-radius: 4px;
		padding: 15px;
		margin-bottom: 15px;
	}

	.filter-card .form-group {
		margin-bottom: 10px;
	}

	.filter-card label {
		font-weight: 600;
		font-size: 12px;
		text-transform: uppercase;
		color: #666;
		margin-bottom: 5px;
	}

	.active-filter-badge {
		display: inline-block;
		background: #e8f5e9;
		color: #2e7d32;
		border: 1px solid #a5d6a7;
		border-radius: 3px;
		padding: 5px 12px;
		font-size: 12px;
		margin-top: 10px;
	}

	.breadcrumb-custom {
		background: none;
		padding: 0;
		margin-bottom: 5px;
		font-size: 12px;
	}

	.breadcrumb-custom li a {
		color: #3c8dbc;
	}

	.breadcrumb-custom li.active {
		color: #777;
	}
</style>

<div class="box">
	<div class="box-header with-border">
		<ol class="breadcrumb breadcrumb-custom">
			<li><a href="javascript:void(0);">Finance</a></li>
			<li class="active">Request Payment</li>
		</ol>
		<h3 class="box-title" style="font-size: 22px; font-weight: 600;">Request Payment</h3>
		<div class="box-tools pull-right">
			<button type="button" class="btn btn-sm btn-success" onclick="exportExcel();">
				<i class="fa fa-file-excel-o"></i> Export Excel
			</button>
			<button type="button" class="btn btn-sm btn-default" onclick="resetFilter();">
				<i class="fa fa-refresh"></i> Reset Filter
			</button>
		</div>
	</div>
	<div class="box-body">

		<!-- Filter Bar Card -->
		<div class="filter-card">
			<div class="row">
				<!-- COMPANY Dropdown -->
				<div class="col-md-3">
					<div class="form-group">
						<label>Company</label>
						<select id="filter_company" class="form-control select2-filter" style="width: 100%;">
							<option value="">Semua Entitas</option>
							<?php if (!empty($companies)): ?>
								<?php foreach ($companies as $company): ?>
									<option value="<?= $company->id; ?>"><?= $company->nama; ?></option>
								<?php endforeach; ?>
							<?php endif; ?>
						</select>
					</div>
				</div>

				<!-- PERIODE Dropdowns -->
				<div class="col-md-5">
					<div class="form-group">
						<label>Periode</label>
						<div style="display: flex; align-items: center; gap: 5px;">
							<div style="flex: 3;">
								<select id="filter_bulan_from" class="form-control select2-filter" style="width: 100%;">
									<?php foreach ($bulan_indonesia as $num => $nama): ?>
										<option value="<?= $num; ?>" <?= ($num == $default_bulan_from) ? 'selected' : ''; ?>><?= $nama; ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div style="flex: 2;">
								<select id="filter_tahun_from" class="form-control select2-filter" style="width: 100%;">
									<?php for ($y = 2020; $y <= $max_year; $y++): ?>
										<option value="<?= $y; ?>" <?= ($y == $current_year) ? 'selected' : ''; ?>><?= $y; ?></option>
									<?php endfor; ?>
								</select>
							</div>
							<div style="flex: 0; padding: 0 5px; font-weight: 600;">
								&mdash;
							</div>
							<div style="flex: 3;">
								<select id="filter_bulan_to" class="form-control select2-filter" style="width: 100%;">
									<?php foreach ($bulan_indonesia as $num => $nama): ?>
										<option value="<?= $num; ?>" <?= ($num == $current_month) ? 'selected' : ''; ?>><?= $nama; ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div style="flex: 2;">
								<select id="filter_tahun_to" class="form-control select2-filter" style="width: 100%;">
									<?php for ($y = 2020; $y <= $max_year; $y++): ?>
										<option value="<?= $y; ?>" <?= ($y == $current_year) ? 'selected' : ''; ?>><?= $y; ?></option>
									<?php endfor; ?>
								</select>
							</div>
						</div>
					</div>
				</div>

				<!-- KATEGORI Dropdown -->
				<div class="col-md-3">
					<div class="form-group">
						<label>Kategori</label>
						<select id="filter_kategori" class="form-control select2-filter" style="width: 100%;">
							<option value="">Semua</option>
							<option value="Cash">Cash</option>
							<option value="Transport">Transport</option>
							<option value="Kasbon">Kasbon</option>
							<option value="Periodik">Periodik</option>
							<option value="Expense">Expense</option>
							<option value="Non-PO">Non-PO</option>
							<option value="Purchase Invoice">Purchase Invoice</option>
							<option value="Direct Payment">Direct Payment</option>
						</select>
					</div>
				</div>

				<!-- Apply Filter Button -->
				<div class="col-md-1">
					<div class="form-group">
						<label>&nbsp;</label>
						<button type="button" class="btn btn-sm btn-primary btn-block" onclick="applyFilter();" style="margin-top: 1px;">
							<i class="fa fa-filter"></i> Filter
						</button>
					</div>
				</div>
			</div>

			<!-- Active Filter Badge -->
			<div class="row">
				<div class="col-md-12">
					<span class="active-filter-badge" id="active_filter_badge">
						<i class="fa fa-info-circle"></i>
						Export: <?= $bulan_indonesia[$default_bulan_from]; ?> - <?= $bulan_indonesia[$current_month]; ?> <?= $current_year; ?> - Semua Entitas
					</span>
				</div>
			</div>
		</div>
		<!-- End Filter Bar Card -->

		<!-- Summary Cards Section -->
		<div class="row" style="margin-bottom: 20px;">
			<!-- Card 1: Total Pengajuan -->
			<div class="col-md-3">
				<div class="card-summary" style="border: 1px solid #e0e0e0; border-radius: 4px; padding: 15px; background-color: #fff;">
					<div style="font-size: 11px; font-weight: bold; color: #888; text-transform: uppercase; margin-bottom: 5px;">TOTAL PENGAJUAN</div>
					<div style="font-size: 22px; font-weight: bold; color: #333;" id="card_total_pengajuan">0</div>
					<div style="font-size: 12px; color: #999;">item</div>
				</div>
			</div>
			<!-- Card 2: Total Nilai -->
			<div class="col-md-3">
				<div class="card-summary" style="border: 1px solid #e0e0e0; border-radius: 4px; padding: 15px; background-color: #fff;">
					<div style="font-size: 11px; font-weight: bold; color: #888; text-transform: uppercase; margin-bottom: 5px;">TOTAL NILAI</div>
					<div style="font-size: 22px; font-weight: bold; color: #2196F3;">
						<span style="font-size: 14px;">Rp</span> <span id="card_total_nilai">0</span>
					</div>
				</div>
			</div>
			<!-- Card 3: Cash -->
			<div class="col-md-3">
				<div class="card-summary" style="border: 1px solid #e0e0e0; border-radius: 4px; padding: 15px; background-color: #fff;">
					<div style="font-size: 11px; font-weight: bold; color: #888; text-transform: uppercase; margin-bottom: 5px;">CASH</div>
					<div style="font-size: 22px; font-weight: bold; color: #2196F3;">
						<span style="font-size: 14px;">Rp</span> <span id="card_total_cash">0</span>
					</div>
				</div>
			</div>
			<!-- Card 4: Kasbon -->
			<div class="col-md-3">
				<div class="card-summary" style="border: 1px solid #e0e0e0; border-radius: 4px; padding: 15px; background-color: #fff;">
					<div style="font-size: 11px; font-weight: bold; color: #888; text-transform: uppercase; margin-bottom: 5px;">KASBON</div>
					<div style="font-size: 22px; font-weight: bold; color: #FF9800;">
						<span style="font-size: 14px;">Rp</span> <span id="card_total_kasbon">0</span>
					</div>
				</div>
			</div>
		</div>
		<!-- End Summary Cards Section -->

		<!-- Tab Navigation Section -->
		<style>
			.nav-tabs-custom {
				margin-bottom: 0;
			}

			.nav-tabs-custom>.nav-tabs>li.active>a {
				border-top-color: #3c8dbc;
				font-weight: 600;
			}

			.nav-tabs-custom>.nav-tabs>li>a .badge {
				margin-left: 5px;
				font-size: 11px;
			}

			.tab-badge-belum {
				background-color: #f39c12;
				color: #fff;
			}

			.tab-badge-sudah {
				background-color: #00a65a;
				color: #fff;
			}

			.datatable-controls {
				display: flex;
				justify-content: space-between;
				align-items: center;
				margin-bottom: 15px;
				flex-wrap: wrap;
				gap: 10px;
			}

			.datatable-controls .entries-control {
				font-size: 13px;
				color: #555;
			}

			.datatable-controls .entries-control select {
				display: inline-block;
				width: auto;
				padding: 4px 8px;
				font-size: 13px;
				border: 1px solid #ccc;
				border-radius: 3px;
			}

			.datatable-controls .search-control {
				position: relative;
			}

			.datatable-controls .search-control input {
				padding: 6px 12px;
				font-size: 13px;
				border: 1px solid #ccc;
				border-radius: 3px;
				width: 250px;
			}

			#table_req_payment {
				width: 100% !important;
			}

			#table_req_payment thead th {
				position: sticky;
				top: 0;
				background-color: #3c8dbc;
				color: #fff;
				z-index: 10;
				font-size: 12px;
				text-align: center;
				vertical-align: middle;
				white-space: nowrap;
			}

			#table_req_payment tbody td {
				font-size: 13px;
				vertical-align: middle;
			}

			#table_req_payment .col-nilai {
				text-align: right;
			}

			#table_req_payment .col-checkbox {
				text-align: center;
				width: 30px;
			}

			#table_req_payment .col-no {
				text-align: center;
				width: 40px;
			}

			.table-wrapper {
				max-height: 500px;
				overflow-y: auto;
				border: 1px solid #ddd;
			}
		</style>

		<div class="nav-tabs-custom">
			<ul class="nav nav-tabs" id="payment_tabs">
				<li class="active">
					<a href="#tab_belum_dibayar" data-toggle="tab" data-tab="belum_dibayar">
						Belum Dibayar <span class="badge tab-badge-belum" id="badge_belum_dibayar">0</span>
					</a>
				</li>
				<li>
					<a href="#tab_sudah_dibayar" data-toggle="tab" data-tab="sudah_dibayar">
						Sudah Dibayar <span class="badge tab-badge-sudah" id="badge_sudah_dibayar">0</span>
					</a>
				</li>
			</ul>
			<div class="tab-content">
				<div class="tab-pane active" id="tab_belum_dibayar">
				</div>
				<div class="tab-pane" id="tab_sudah_dibayar">
				</div>
			</div>
		</div>
		<!-- End Tab Navigation -->

		<!-- DataTables Controls -->
		<div class="datatable-controls">
			<div class="entries-control">
				Tampilkan
				<select id="dt_entries_per_page">
					<option value="10">10</option>
					<option value="25">25</option>
					<option value="50">50</option>
					<option value="100">100</option>
				</select>
				entri per halaman
			</div>
			<div class="search-control">
				<input type="text" id="dt_search" class="form-control" placeholder="Cari dokumen, pemohon...">
			</div>
		</div>
		<!-- End DataTables Controls -->

		<!-- DataTable -->
		<div class="table-wrapper">
			<table id="table_req_payment" class="table table-bordered table-striped" style="width:100%;">
				<thead>
					<tr>
						<th class="col-checkbox"><input type="checkbox" id="check_all"></th>
						<th class="col-no">NO</th>
						<th>NO. DOKUMEN</th>
						<th>DIMINTA OLEH</th>
						<th>COMPANY</th>
						<th>TANGGAL PENGAJUAN</th>
						<th>TANGGAL APPROVAL</th>
						<th>KEPERLUAN</th>
						<th>KATEGORI</th>
						<th class="col-nilai">NILAI (RP)</th>
						<th>TGL PEMBAYARAN</th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
		<!-- End DataTable -->

		<!-- Process Buttons (only for Belum Dibayar tab) -->
		<div id="process_section" class="row" style="margin-top: 15px;">
			<div class="col-md-6">
				<div class="form-group">
					<label for="reject_reason">Reject Reason</label>
					<textarea name="reject_reason" id="reject_reason" class="form-control form-control-sm" rows="2" placeholder="Masukkan alasan reject..."></textarea>
				</div>
			</div>
			<div class="col-md-6 text-right" style="padding-top: 25px;">
				<button type="button" class="btn btn-sm btn-default" onclick="resetChecked();"><i class="fa fa-refresh"></i> Reset Pilihan</button>
				<button type="button" class="btn btn-sm btn-danger" onclick="rejectReqPayment();"><i class="fa fa-close"></i> Reject</button>
				<button type="button" class="btn btn-sm btn-success" onclick="processReqPayment();"><i class="fa fa-save"></i> Proses Pembayaran</button>
			</div>
		</div>
		<!-- End Process Buttons -->

	</div><!-- /.box-body -->
</div><!-- /.box -->

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

<script>
	$(document).ready(function() {
		// Current defaults from PHP
		var defaultBulanFrom = <?= $default_bulan_from; ?>;
		var defaultTahunFrom = <?= $current_year; ?>;
		var defaultBulanTo = <?= $current_month; ?>;
		var defaultTahunTo = <?= $current_year; ?>;

		// Indonesian month names for badge display
		var bulanIndonesia = {
			1: 'Januari',
			2: 'Februari',
			3: 'Maret',
			4: 'April',
			5: 'Mei',
			6: 'Juni',
			7: 'Juli',
			8: 'Agustus',
			9: 'September',
			10: 'Oktober',
			11: 'November',
			12: 'Desember'
		};

		// Active tab variable
		var currentTab = 'belum_dibayar';

		// DataTables instance
		var dataTable = null;

		// ========================================
		// INIT FILTERS
		// ========================================
		function initFilters() {
			$('.select2-filter').select2({
				minimumResultsForSearch: -1,
				width: '100%'
			});

			// Set defaults
			$('#filter_bulan_from').val(defaultBulanFrom).trigger('change.select2');
			$('#filter_tahun_from').val(defaultTahunFrom).trigger('change.select2');
			$('#filter_bulan_to').val(defaultBulanTo).trigger('change.select2');
			$('#filter_tahun_to').val(defaultTahunTo).trigger('change.select2');
			$('#filter_company').val('').trigger('change.select2');
			$('#filter_kategori').val('').trigger('change.select2');
		}

		// ========================================
		// GET CURRENT FILTERS
		// ========================================
		function getFilters() {
			return {
				company_id: $('#filter_company').val(),
				bulan_from: $('#filter_bulan_from').val(),
				tahun_from: $('#filter_tahun_from').val(),
				bulan_to: $('#filter_bulan_to').val(),
				tahun_to: $('#filter_tahun_to').val(),
				kategori: $('#filter_kategori').val()
			};
		}

		// ========================================
		// VALIDATE FILTERS
		// ========================================
		function validateFilters(filters) {
			var tahunFrom = parseInt(filters.tahun_from);
			var tahunTo = parseInt(filters.tahun_to);
			var bulanFrom = parseInt(filters.bulan_from);
			var bulanTo = parseInt(filters.bulan_to);

			if (tahunFrom > tahunTo) {
				alert('Tahun awal tidak boleh lebih besar dari tahun akhir.');
				return false;
			}
			if (tahunFrom === tahunTo && bulanFrom > bulanTo) {
				alert('Bulan awal tidak boleh lebih besar dari bulan akhir pada tahun yang sama.');
				return false;
			}
			return true;
		}

		// ========================================
		// APPLY FILTER
		// ========================================
		window.applyFilter = function() {
			var filters = getFilters();

			if (!validateFilters(filters)) {
				return;
			}

			// Update active filter badge text
			var companyText = $('#filter_company option:selected').text() || 'Semua Entitas';
			if (!filters.company_id) companyText = 'Semua Entitas';
			var bulanToLabel = bulanIndonesia[parseInt(filters.bulan_to)] || '';
			var badgeText = 'Export: ' + bulanToLabel + ' ' + filters.tahun_to + ' - ' + companyText;
			$('#active_filter_badge').html('<i class="fa fa-info-circle"></i> ' + badgeText);

			// Reload DataTables
			if (dataTable) {
				dataTable.ajax.reload(null, true);
			}

			// Reload summary cards
			loadSummaryCards(filters);
		};

		// ========================================
		// RESET FILTER
		// ========================================
		window.resetFilter = function() {
			$('#filter_company').val('').trigger('change.select2');
			$('#filter_bulan_from').val(defaultBulanFrom).trigger('change.select2');
			$('#filter_tahun_from').val(defaultTahunFrom).trigger('change.select2');
			$('#filter_bulan_to').val(defaultBulanTo).trigger('change.select2');
			$('#filter_tahun_to').val(defaultTahunTo).trigger('change.select2');
			$('#filter_kategori').val('').trigger('change.select2');

			applyFilter();
		};

		// ========================================
		// LOAD SUMMARY CARDS
		// ========================================
		function loadSummaryCards(filters) {
			$.ajax({
				url: '<?= site_url("request_payment/get_summary_cards"); ?>',
				type: 'POST',
				data: filters,
				dataType: 'json',
				success: function(response) {
					$('#card_total_pengajuan').text(formatNumber(response.total_pengajuan || 0));
					$('#card_total_nilai').text(formatNumber(response.total_nilai || 0));
					$('#card_total_cash').text(formatNumber(response.total_cash || 0));
					$('#card_total_kasbon').text(formatNumber(response.total_kasbon || 0));
				},
				error: function() {
					$('#card_total_pengajuan').text('-');
					$('#card_total_nilai').text('-');
					$('#card_total_cash').text('-');
					$('#card_total_kasbon').text('-');
				}
			});
		}

		// ========================================
		// FORMAT NUMBER (dot as thousand separator)
		// ========================================
		function formatNumber(num) {
			if (num === null || num === undefined || num === '') return '0';
			num = parseInt(num);
			if (isNaN(num)) return '0';
			return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
		}

		// ========================================
		// LOAD OTHER TAB COUNT
		// ========================================
		function loadOtherTabCount() {
			var filters = getFilters();
			var otherTab = (currentTab === 'belum_dibayar') ? 'sudah_dibayar' : 'belum_dibayar';
			var postData = $.extend({}, filters, {
				tab: otherTab,
				draw: 1,
				start: 0,
				length: 1,
				search: {
					value: ''
				}
			});

			$.ajax({
				url: '<?= site_url("request_payment/get_data_req_payment"); ?>',
				type: 'POST',
				data: postData,
				dataType: 'json',
				success: function(response) {
					if (otherTab === 'belum_dibayar') {
						$('#badge_belum_dibayar').text(response.recordsTotal || 0);
					} else {
						$('#badge_sudah_dibayar').text(response.recordsTotal || 0);
					}
				}
			});
		}

		// ========================================
		// INIT DATATABLES
		// ========================================
		function initDataTable() {
			// Destroy existing if any
			if (dataTable) {
				dataTable.destroy();
				dataTable = null;
			}

			dataTable = $('#table_req_payment').DataTable({
				processing: true,
				serverSide: true,
				ajax: {
					url: '<?= site_url("request_payment/get_data_req_payment"); ?>',
					type: 'POST',
					data: function(d) {
						var filters = getFilters();
						d.company_id = filters.company_id;
						d.bulan_from = filters.bulan_from;
						d.tahun_from = filters.tahun_from;
						d.bulan_to = filters.bulan_to;
						d.tahun_to = filters.tahun_to;
						d.kategori = filters.kategori;
						d.tab = currentTab;
					},
					error: function(xhr, error, thrown) {
						alert('Gagal memuat data. Silakan coba lagi.');
						console.error('DataTables AJAX error:', error, thrown);
					}
				},
				columns: [{
						data: 'checkbox',
						orderable: false,
						searchable: false
					},
					{
						data: 'no',
						orderable: false,
						searchable: false
					},
					{
						data: 'no_dokumen'
					},
					{
						data: 'diminta_oleh'
					},
					{
						data: 'company'
					},
					{
						data: 'tanggal'
					},
					{
						data: 'tanggal_approval'
					},
					{
						data: 'keperluan'
					},
					{
						data: 'kategori'
					},
					{
						data: 'nilai',
						className: 'col-nilai'
					},
					{
						data: 'tgl_pembayaran',
						orderable: false,
						searchable: false
					}
				],
				order: [
					[5, 'desc']
				],
				pageLength: parseInt($('#dt_entries_per_page').val()) || 10,
				searching: true,
				lengthChange: false,
				dom: 'rtip',
				language: {
					processing: '<i class="fa fa-spinner fa-spin"></i> Memproses...',
					emptyTable: 'Tidak ada data yang tersedia',
					zeroRecords: 'Tidak ada data yang cocok dengan pencarian',
					info: 'Menampilkan _START_ - _END_ dari _TOTAL_ entri',
					infoEmpty: 'Menampilkan 0 - 0 dari 0 entri',
					infoFiltered: '(disaring dari _MAX_ total entri)',
					paginate: {
						first: 'Pertama',
						last: 'Terakhir',
						next: 'Selanjutnya',
						previous: 'Sebelumnya'
					}
				},
				drawCallback: function(settings) {
					// Update tab badge counts from server response
					var json = this.api().ajax.json();
					if (json) {
						if (currentTab === 'belum_dibayar') {
							$('#badge_belum_dibayar').text(json.recordsTotal || 0);
						} else {
							$('#badge_sudah_dibayar').text(json.recordsTotal || 0);
						}
					}
					// Load the opposite tab count
					loadOtherTabCount();

					// Initialize datepicker on rendered date inputs
					$('#table_req_payment .datepicker').datepicker({
						format: 'dd/mm/yyyy',
						autoclose: true,
						todayHighlight: true,
						orientation: 'bottom auto'
					});
				}
			});
		}

		// ========================================
		// TAB SWITCHING
		// ========================================
		$('#payment_tabs a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
			var tab = $(e.target).data('tab');
			currentTab = tab;

			// Reinitialize DataTables with new tab
			initDataTable();
			toggleProcessSection();
		});

		// ========================================
		// ENTRIES PER PAGE CHANGE
		// ========================================
		$('#dt_entries_per_page').on('change', function() {
			if (dataTable) {
				dataTable.page.len(parseInt($(this).val())).draw();
			}
		});

		// ========================================
		// CUSTOM SEARCH
		// ========================================
		$('#dt_search').on('keyup', function() {
			if (dataTable) {
				dataTable.search($(this).val()).draw();
			}
		});

		// ========================================
		// CHECK ALL CHECKBOX
		// ========================================
		$('#check_all').on('change', function() {
			var isChecked = $(this).is(':checked');
			$('#table_req_payment tbody input[type="checkbox"]').prop('checked', isChecked);
		});

		// ========================================
		// EXPORT EXCEL
		// ========================================
		window.exportExcel = function() {
			var filters = getFilters();

			if (!validateFilters(filters)) {
				return;
			}

			var params = [];
			if (filters.company_id) params.push('company_id=' + encodeURIComponent(filters.company_id));
			if (filters.bulan_from) params.push('bulan_from=' + encodeURIComponent(filters.bulan_from));
			if (filters.tahun_from) params.push('tahun_from=' + encodeURIComponent(filters.tahun_from));
			if (filters.bulan_to) params.push('bulan_to=' + encodeURIComponent(filters.bulan_to));
			if (filters.tahun_to) params.push('tahun_to=' + encodeURIComponent(filters.tahun_to));
			if (filters.kategori) params.push('kategori=' + encodeURIComponent(filters.kategori));

			var url = '<?= site_url("request_payment/download_excel_request_payment"); ?>';
			if (params.length > 0) {
				url += '?' + params.join('&');
			}

			window.location = url;
		};

		// ========================================
		// PROCESS / REJECT FUNCTIONS
		// ========================================

		// Toggle process section visibility based on active tab
		function toggleProcessSection() {
			if (currentTab === 'belum_dibayar') {
				$('#process_section').show();
			} else {
				$('#process_section').hide();
			}
		}

		// Track checkbox selection via AJAX (same as backup)
		$(document).on('click', '.pilih_data', function() {
			var val_pilih = $(this).val();
			var kategori = $(this).data('kategori');
			var isChecked = $(this).is(':checked');
			var wdo = isChecked ? 1 : 0;

			$.ajax({
				type: 'post',
				url: '<?= site_url("request_payment/added_pilih_data"); ?>',
				data: {
					id: val_pilih,
					kategori: kategori,
					wdo: wdo
				},
				cache: false
			});
		});

		// Reset checked items (clear tr_added_req_payment)
		window.resetChecked = function() {
			$.ajax({
				type: 'post',
				url: '<?= site_url("request_payment/reset_choosed_req_payment"); ?>',
				cache: false,
				success: function() {
					initDataTable();
				}
			});
		};

		// Process payment (submit selected data)
		window.processReqPayment = function() {
			var checked = $('#table_req_payment tbody input.pilih_data:checked');
			if (checked.length === 0) {
				alert('Pilih minimal 1 data sebelum memproses!');
				return;
			}

			if (!confirm('Data yang dipilih akan diproses. Lanjutkan?')) return;

			var formData = $('#table_req_payment tbody input').serialize();

			$.ajax({
				type: 'post',
				url: '<?= site_url("request_payment/save_request_payment"); ?>',
				data: formData,
				dataType: 'json',
				cache: false,
				success: function(result) {
					if (result.status == '1') {
						alert(result.msg || 'Data berhasil diproses!');
						initDataTable();
						loadSummaryCards(getFilters());
					} else {
						alert(result.msg || 'Gagal memproses data!');
					}
				},
				error: function() {
					alert('Terjadi error. Silakan coba lagi.');
				}
			});
		};

		// Reject selected data
		window.rejectReqPayment = function() {
			var reject_reason = $('#reject_reason').val();
			if (reject_reason === '') {
				alert('Reject Reason masih kosong!');
				return;
			}

			var checked = $('#table_req_payment tbody input.pilih_data:checked');
			if (checked.length === 0) {
				alert('Pilih minimal 1 data sebelum reject!');
				return;
			}

			if (!confirm('Data yang dipilih akan di-reject. Lanjutkan?')) return;

			$.ajax({
				type: 'post',
				url: '<?= site_url("request_payment/reject_req_payment"); ?>',
				data: {
					reject_reason: reject_reason
				},
				dataType: 'json',
				cache: false,
				success: function(result) {
					if (result.status == '1') {
						alert(result.msg || 'Data berhasil di-reject!');
						$('#reject_reason').val('');
						initDataTable();
						loadSummaryCards(getFilters());
					} else {
						alert(result.msg || 'Gagal reject data!');
					}
				},
				error: function() {
					alert('Terjadi error. Silakan coba lagi.');
				}
			});
		};

		// ========================================
		// INITIALIZATION
		// ========================================
		initFilters();
		initDataTable();
		loadSummaryCards(getFilters());
		toggleProcessSection();
	});
</script>