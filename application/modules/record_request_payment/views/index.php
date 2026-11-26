<?php
if (!empty($rp_transfer_available)) {
	$rp_tutorial_page = 'record';
	include dirname(__DIR__, 2) . '/request_payment/views/transfer_tutorial.php';
}
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

	.rec-items-export {
		display: flex;
		align-items: center;
		flex-wrap: wrap;
		gap: 8px;
		margin: 0;
		max-width: 100%;
	}

	.rec-items-export input { max-width: 100%; }

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

	#table_record_items td.rec-keperluan { min-width: 200px; }

	#table_record_items .rec-trf-tag {
		display: inline-block;
		background: #605ca8;
		color: #fff;
		font-size: 10.5px;
		font-weight: 700;
		padding: 2px 8px;
		border-radius: 10px;
		white-space: nowrap;
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
						<div style="display:flex;gap:8px;flex-wrap:wrap">
							<input type="text" id="rec_search" placeholder="Cari company atau no. pengajuan...">
							<?php if (!empty($rp_transfer_available)): ?>
								<button type="button" class="btn btn-success btn-sm" id="rp-export-open" data-toggle="modal" data-target="#rp-export-modal"><i class="fa fa-file-excel-o"></i> Export Excel Beberapa PGJ</button>
							<?php endif; ?>
						</div>
					</div>

					<div style="overflow-x:auto;border:1px solid #ddd;">
						<table id="table_record" class="table table-bordered table-striped" style="width:100%;">
							<thead>
								<tr>
									<th>NO. PENGAJUAN</th>
									<th>COMPANY</th>
									<th>TANGGAL PENGAJUAN</th>
									<th>TANGGAL DIPUTUSKAN</th>
									<?php if (!empty($rp_transfer_available)): ?><th>JUMLAH TRANSFER</th><?php endif; ?>
									<th style="width:150px;">TOTAL DIBAYARKAN</th>
									<th style="width:140px;">STATUS</th>
									<?php if (!empty($rp_transfer_available)): ?><th>STATUS PEMBAYARAN</th><?php endif; ?>
									<th style="width:120px;">ACTION</th>
								</tr>
							</thead>
							<tbody></tbody>
						</table>
					</div>
				</div>

				<?php if (!empty($rp_transfer_available)): ?>
				<div class="modal fade" id="rp-export-modal" tabindex="-1" role="dialog" aria-labelledby="rp-export-title">
					<div class="modal-dialog modal-lg" role="document"><form class="modal-content" id="rp-export-form" method="post">
						<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title" id="rp-export-title"><i class="fa fa-file-excel-o"></i> Export Excel Beberapa PGJ</h4></div>
						<div class="modal-body">
							<p class="text-muted" style="font-size:12px;margin-top:0">Hanya PGJ yang sudah diputuskan. Semua company ikut, berurutan STM, Sustain, lalu Vuca (tidak tercampur). Format mengikuti Plan Cash Flow (sama dengan Print per PGJ): STM, Sustain, Vuca dipisah baris kosong, kolom NO. PENGAJUAN di kanan, ringkasan per company (Pengajuan, Saldo, Sisa Saldo) di bawah. Status pembayaran dihitung per transfer: transfer yang tidak sesuai filter tidak ikut.</p>
							<div class="row"><div class="col-md-6 form-group"><label for="rp-export-status">Status Pembayaran</label>
								<select id="rp-export-status" name="status" class="form-control input-sm"><option value="unpaid">Belum dibayar</option><option value="paid">Sudah dibayar</option><option value="">Semua</option></select></div></div>
							<div class="row">
								<div class="col-md-4 form-group"><label for="rp-export-from">Diputuskan dari *</label><input type="date" id="rp-export-from" name="from" class="form-control input-sm" required value="<?= date('Y-m-01') ?>"></div>
								<div class="col-md-4 form-group"><label for="rp-export-to">Sampai *</label><input type="date" id="rp-export-to" name="to" class="form-control input-sm" required value="<?= date('Y-m-d') ?>"></div>
								<div class="col-md-4 form-group"><label for="rp-export-category">Kategori</label><select id="rp-export-category" name="category" class="form-control input-sm">
									<option value="">Semua</option>
									<?php foreach (Rp_transfer_record::CATEGORIES as $category): ?><option value="<?= html_escape($category) ?>"><?= html_escape($category) ?></option><?php endforeach; ?>
								</select></div>
							</div>
							<div id="rp-export-list" role="status" aria-live="polite"></div>
							<div class="text-danger" id="rp-export-error" role="alert"></div>
						</div>
						<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
							<button type="submit" class="btn btn-success"><i class="fa fa-file-excel-o"></i> Export Excel</button></div>
					</form></div>
				</div>
				<script>
				$(function () {
					var endpoint = <?= json_encode(site_url('record_request_payment/export_transfer_batches')); ?>;
					var form = $('#rp-export-form'), list = $('#rp-export-list'), off = {}, rows = [], sequence = 0;
					function esc(value) { return $('<div>').text(value == null ? '' : String(value)).html(); }
					function rupiah(cents) { return (cents / 100).toLocaleString('id-ID', {maximumFractionDigits: 2}); }
					function filter() { return {status: $('#rp-export-status').val(), from: $('#rp-export-from').val(), to: $('#rp-export-to').val(), category: $('#rp-export-category').val()}; }
					function status(row) {
						if (!row.paid) return '<span class="appr-status-pending">Belum dibayar</span>';
						if (row.paid === row.all) return '<span class="rec-status-done">Sudah dibayar</span>';
						return '<span class="appr-status-pending">Sebagian</span><br><small class="text-muted">' + row.paid + ' dari ' + row.all + ' transfer dibayar</small>';
					}
					function draw() {
						var on = rows.filter(function (row) { return !off[row.id]; });
						list.html(rows.length ? '<div style="max-height:260px;overflow:auto;border:1px solid #ddd"><table class="table table-bordered" style="margin:0;font-size:12.5px"><thead><tr>' +
							'<th style="width:30px"></th><th>Company</th><th>No. Pengajuan</th><th>Diputuskan</th><th>Transfer ikut</th><th>Status PGJ</th><th class="text-right">Dibayarkan</th></tr></thead><tbody>' +
							rows.map(function (row) {
								return '<tr><td class="text-center"><input type="checkbox" data-batch="' + row.id + '" aria-label="Pilih ' + esc(row.no) + '"' + (off[row.id] ? '' : ' checked') + '></td><td>' +
									esc(row.company) + '</td><td><b>' + esc(row.no) + '</b></td><td>' + esc(row.decided_at) + '</td><td class="text-center">' + row.count + ' dari ' + row.all +
									'</td><td class="text-center">' + status(row) + '</td><td class="text-right">' + rupiah(row.net) + '</td></tr>';
							}).join('') + '</tbody></table></div><div style="margin-top:8px;font-size:12.5px"><b>' + on.length + ' PGJ</b> &middot; ' +
							on.reduce(function (sum, row) { return sum + row.count; }, 0) + ' transfer &middot; Total dibayarkan <b>Rp ' +
							rupiah(on.reduce(function (sum, row) { return sum + row.net; }, 0)) + '</b></div>'
							: '<p class="text-muted" style="font-size:12px;margin:0">Tidak ada PGJ yang sesuai filter.</p>');
						form.find('[type=submit]').prop('disabled', !on.length);
					}
					function load() {
						var f = filter(), token = ++sequence;
						off = {}; rows = [];
						$('#rp-export-error').text('');
						if (!f.from || !f.to || f.from > f.to) {
							list.html('<p class="text-muted" style="font-size:12px;margin:0">Isi periode tanggal diputuskan untuk melihat PGJ yang akan diekspor.</p>');
							form.find('[type=submit]').prop('disabled', true);
							return;
						}
						$.getJSON(endpoint, f).done(function (res) {
							if (token !== sequence) return;
							rows = res.batches || [];
							draw();
						}).fail(function (xhr) {
							if (token !== sequence) return;
							list.empty();
							$('#rp-export-error').text((xhr.responseJSON && xhr.responseJSON.msg) || 'Gagal memuat daftar PGJ.');
							form.find('[type=submit]').prop('disabled', true);
						});
					}
					$('#rp-export-modal').on('show.bs.modal', load);
					form.on('change', 'select, input[type=date]', load);
					list.on('change', '[data-batch]', function () { off[this.dataset.batch] = !this.checked; draw(); });
					form.on('submit', function () {
						form.attr('action', endpoint + '?' + $.param(filter()));
						form.find('input[name="batches[]"]').remove();
						rows.filter(function (row) { return !off[row.id]; }).forEach(function (row) {
							form.append($('<input type="hidden" name="batches[]">').val(String(row.id)));
						});
					});
				});
				</script>
				<?php endif; ?>

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
				<?php if (!empty($rp_transfer_available)): ?>
				{
					data: 'transfer_count',
					orderable: false,
					className: 'text-center',
					render: function(count) { return Number(count) || '-'; }
				},
				<?php endif; ?>
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
					<?php if (!empty($rp_transfer_available)): ?>
					data: null,
					orderable: false,
					render: function(row) {
						if (!row.transfer_count) return '-';
						if (!row.transfer_paid) return 'Belum dibayar';
						var outside = Number(row.docs_outside) || 0;
						if (!outside && row.transfer_paid === row.transfer_count) return 'Sudah dibayar';
						return 'Sebagian<br><small>' + row.transfer_paid + ' dari ' + row.transfer_count + ' transfer dibayar'
							+ (outside ? ', ' + outside + ' dokumen di luar transfer' : '') + '</small>';
					}
				},
				{
					<?php endif; ?>
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
