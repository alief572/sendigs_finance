<?php
$ENABLE_VIEW = has_permission('Record_Request_Payment.View');
$record_tab = $this->input->get_post('tab');
if (!is_string($record_tab) || !in_array($record_tab, ['batch', 'items', 'legacy'], true)) {
	$record_tab = 'batch';
}
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.11/css/dataTables.bootstrap.min.css">

<style>
	#table_record thead th,
	#table_record_items thead th,
	#table_record_legacy thead th {
		background-color: #3c8dbc;
		color: #fff;
		font-size: 12px;
		text-align: center;
		vertical-align: middle;
		white-space: nowrap;
	}

	#table_record tbody td,
	#table_record_items tbody td,
	#table_record_legacy tbody td {
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
		display: inline-block;
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
		width: 320px;
	}

<<<<<<< HEAD
=======
	.rec-items-export {
		display: flex;
		align-items: center;
		flex-wrap: wrap;
		gap: 8px;
		margin: 0;
		max-width: 100%;
	}

	.rec-items-export input { max-width: 100%; }

>>>>>>> b127c8e19d1f7bed84ac03533b27c503f3837a47
	.mini-btn {
		border: 1px solid #ccc;
		background: #fff;
		padding: 4px 10px;
		border-radius: 4px;
		font-size: 11.5px;
		font-weight: 600;
		cursor: pointer;
		text-decoration: none;
		display: inline-block;
		margin: 2px;
	}

	.mini-btn.view {
		color: #3D6690;
		border-color: #BFD6EA;
	}

	.mini-btn.print {
		color: #3A4656;
		border-color: #C9D3DC;
	}

	#table_record_items .rec-item-actions {
		display: inline-flex;
		align-items: center;
		white-space: nowrap;
	}

	#table_record_items .rec-docnum { font-weight: 700; color: #2B3440; }

	#table_record_items .rec-kategori-badge {
		display: inline-block;
		background: #3A4656;
		color: #fff;
		font-size: 10.5px;
		font-weight: 600;
		padding: 3px 9px;
		border-radius: 10px;
		white-space: nowrap;
		margin-bottom: 4px;
	}

	.nav-tabs-custom {
		box-shadow: none;
		margin-bottom: 0;
	}

	.nav-tabs-custom > .nav-tabs > li.active > a {
		border-top-color: #3c8dbc;
		font-weight: 600;
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

		<div class="nav-tabs-custom">
			<ul class="nav nav-tabs" id="record_tabs">
				<li<?= $record_tab === 'batch' ? ' class="active"' : ''; ?>>
					<a href="#tab_baru" data-toggle="tab">
						<i class="fa fa-folder-open-o"></i> Pengajuan Baru (Batch)
					</a>
				</li>
				<li<?= $record_tab === 'items' ? ' class="active"' : ''; ?>>
					<a href="#tab_items" data-toggle="tab">Per Transaksi (Approved)</a>
				</li>
				<li<?= $record_tab === 'legacy' ? ' class="active"' : ''; ?>>
					<a href="#tab_lama" data-toggle="tab">
						<i class="fa fa-history"></i> Histori Lama (Pre-Cutoff)
					</a>
				</li>
			</ul>

			<div class="tab-content" style="padding: 15px 0 0 0;">
				<!-- TAB 1: PENGAJUAN BARU -->
				<div class="tab-pane<?= $record_tab === 'batch' ? ' active' : ''; ?>" id="tab_baru">
					<div class="rec-toolbar">
						<div class="text-muted">Riwayat seluruh batch pengajuan yang sudah diputuskan (final).</div>
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

				<div class="tab-pane<?= $record_tab === 'items' ? ' active' : ''; ?>" id="tab_items">
					<?php $this->load->view('items'); ?>
				</div>

				<!-- TAB 2: HISTORI LAMA -->
				<div class="tab-pane<?= $record_tab === 'legacy' ? ' active' : ''; ?>" id="tab_lama">
					<div class="rec-toolbar">
						<div class="text-muted">Data historis request payment yang telah diproses sebelum sistem batch baru aktif.</div>
						<input type="text" id="rec_legacy_search" placeholder="Cari no. dokumen, pemohon, keperluan...">
					</div>

					<div style="overflow-x:auto;border:1px solid #ddd;">
						<table id="table_record_legacy" class="table table-bordered table-striped" style="width:100%;">
							<thead>
								<tr>
									<th>NO. DOKUMEN</th>
									<th>KATEGORI</th>
									<th>PEMOHON</th>
									<th>KEPERLUAN</th>
									<th style="width:110px;">TGL. DOKUMEN</th>
									<th style="width:110px;">TGL. BAYAR</th>
									<th style="width:130px;">JUMLAH (RP)</th>
									<th style="width:150px;">STATUS</th>
									<th style="width:110px;">ACTION</th>
								</tr>
							</thead>
							<tbody></tbody>
						</table>
					</div>
				</div>
			</div>
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

		// Lazy initialize Tab Histori Lama
		var legacyInitialized = false;
		var legacyTable = null;

		function initLegacyTable() {
			if (legacyInitialized) return;
			legacyInitialized = true;

			legacyTable = $('#table_record_legacy').DataTable({
				processing: true,
				serverSide: true,
				ajax: {
					url: '<?= site_url("record_request_payment/get_data_record_legacy"); ?>',
					type: 'POST',
					data: function(d) {
						d.search = {
							value: $('#rec_legacy_search').val()
						};
					},
					error: function() {
						alert('Gagal memuat data histori lama.');
					}
				},
				columns: [
					{ data: 'no_doc' },
					{ data: 'kategori' },
					{ data: 'nama' },
					{ data: 'keperluan' },
					{ data: 'tgl_doc', className: 'text-center' },
					{ data: 'tgl_bayar', className: 'text-center' },
					{ data: 'jumlah', className: 'text-right' },
					{ data: 'status', className: 'text-center', orderable: false },
					{ data: 'aksi', className: 'text-center', orderable: false }
				],
				pageLength: 25,
				lengthChange: false,
				searching: false,
				dom: 'rtip',
				order: [],
				language: {
					processing: '<i class="fa fa-spinner fa-spin"></i> Memproses...',
					emptyTable: 'Tidak ada data histori lama.',
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

			$('#rec_legacy_search').on('keyup', function() {
				legacyTable.draw();
			});
		}

		// Adjust columns on tab switch
		$('#record_tabs a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
			var target = $(e.target).attr('href');
			var tabNames = {'#tab_baru': 'batch', '#tab_items': 'items', '#tab_lama': 'legacy'};
			var url = new URL(window.location.href);
			url.searchParams.set('tab', tabNames[target] || 'batch');
			history.replaceState(null, '', url.toString());
			if (target === '#tab_lama') {
				if (!legacyInitialized) {
					initLegacyTable();
				} else if (legacyTable) {
					legacyTable.columns.adjust().draw(false);
				}
			} else if (target === '#tab_baru') {
				if (recTable) {
					recTable.columns.adjust().draw(false);
				}
			}
		});
		if (<?= json_encode($record_tab); ?> === 'legacy') initLegacyTable();
	});
</script>
