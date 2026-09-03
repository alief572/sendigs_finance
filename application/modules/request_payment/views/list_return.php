<link rel="stylesheet" href="https://cdn.datatables.net/2.0.2/css/dataTables.dataTables.min.css">

<style>
	#mytabledata thead th {
		background: #f4f6f9;
		color: #2d3748;
		font-size: 12px;
		text-transform: uppercase;
		letter-spacing: .3px;
		vertical-align: middle;
		white-space: nowrap;
	}

	#mytabledata td {
		vertical-align: middle;
		font-size: 13px;
	}

	.pe-nodoc {
		font-weight: 600;
		color: #2b6cb0;
	}

	.pe-num {
		font-variant-numeric: tabular-nums;
	}

	.pe-badge {
		display: inline-block;
		padding: 3px 10px;
		border-radius: 12px;
		font-size: 11px;
		font-weight: 600;
		line-height: 1.6;
	}

	.pe-badge-out {
		background: #fdecea;
		color: #c0392b;
	}

	.pe-badge-partial {
		background: #fff4e0;
		color: #b9770e;
	}

	.pe-badge-paid {
		background: #e6f6ec;
		color: #1e874b;
	}

	.pe-summary-card {
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		padding: 12px 16px;
		background: #fff;
		box-shadow: 0 1px 2px rgba(0, 0, 0, .04);
	}

	.pe-summary-card .lbl {
		font-size: 11px;
		text-transform: uppercase;
		color: #718096;
		letter-spacing: .4px;
	}

	.pe-summary-card .val {
		font-size: 22px;
		font-weight: 700;
		color: #2d3748;
	}

	.pe-action-btn {
		border-radius: 6px;
	}

	#Mymodal .modal-header {
		background: #f8fafc;
		border-bottom: 1px solid #e2e8f0;
	}

	#Mymodal .modal-title {
		font-weight: 700;
		color: #2d3748;
	}

	#ModalViewReturn .modal-header {
		background: #f8fafc;
		border-bottom: 1px solid #e2e8f0;
	}

	#ModalViewReturn .modal-title {
		font-weight: 700;
		color: #2d3748;
	}
</style>

<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>

<?php
// Hitung ringkasan untuk kartu di atas tabel
$sum_total    = 0;
$sum_terbayar = 0;
$sum_sisa     = 0;
$cnt_out      = 0;
$cnt_partial  = 0;
$cnt_paid     = 0;
$rows_render  = [];

if (!empty($row)) {
	$numb = 0;
	foreach ($row as $record) {
		// Nilai pengembalian = sisa kasbon setelah dipakai untuk expense.
		// (total_kasbon - jumlah), bukan nilai expense yang diinput user.
		$nilai_pengembalian = ($record->total_kasbon - $record->jumlah);

		// Jika tidak ada sisa kasbon yang harus dikembalikan (<= 0), tidak perlu ditampilkan.
		if ($nilai_pengembalian <= 0) {
			continue;
		}

		$numb++;
		$sisa_expense = $nilai_pengembalian;

		$get_pengembalian_expense = $this->db->select('IF(SUM(transfer_jumlah) IS NULL, 0, SUM(transfer_jumlah)) as ttl_kembali_expense')
			->get_where('tr_pengembalian_expense', ['no_doc' => $record->no_doc, 'status' => 1])->row();
		$ttl_kembali = $get_pengembalian_expense->ttl_kembali_expense;

		$sisa_kembali = ($sisa_expense - $ttl_kembali);
		if ($sisa_expense < 0) {
			$sisa_kembali = ($sisa_expense + $ttl_kembali);
		}

		$status_key = 'out';
		if ($ttl_kembali > 0) $status_key = 'partial';
		if ($sisa_kembali == 0) $status_key = 'paid';

		if ($status_key == 'out')     $cnt_out++;
		if ($status_key == 'partial') $cnt_partial++;
		if ($status_key == 'paid')    $cnt_paid++;

		$sum_total    += abs($sisa_expense);
		$sum_terbayar += $ttl_kembali;
		$sum_sisa     += abs($sisa_kembali);

		// Ambil rincian pengembalian (untuk modal View bila status sudah Paid/sisa 0).
		$detail_pengembalian = $this->db->select('id, transfer_tanggal, transfer_jumlah, bukti_transfer, status')
			->order_by('id', 'asc')
			->get_where('tr_pengembalian_expense', ['no_doc' => $record->no_doc])->result();

		$rows_render[] = [
			'numb'         => $numb,
			'record'       => $record,
			'nilai'        => $nilai_pengembalian,
			'ttl_kembali'  => $ttl_kembali,
			'sisa'         => $sisa_kembali,
			'status_key'   => $status_key,
			'detail'       => $detail_pengembalian,
		];
	}
}
?>

<div class="row" style="margin-bottom: 15px;">
	<div class="col-md-3 col-sm-6">
		<div class="pe-summary-card">
			<div class="lbl"><i class="fa fa-file-text-o"></i> Total Dokumen</div>
			<div class="val"><?= count($rows_render) ?></div>
		</div>
	</div>
	<div class="col-md-3 col-sm-6">
		<div class="pe-summary-card">
			<div class="lbl"><i class="fa fa-money"></i> Total Nilai Pengembalian</div>
			<div class="val pe-num">Rp <?= number_format($sum_total) ?></div>
		</div>
	</div>
	<div class="col-md-3 col-sm-6">
		<div class="pe-summary-card">
			<div class="lbl"><i class="fa fa-check-circle-o"></i> Terbayar</div>
			<div class="val pe-num" style="color:#1e874b;">Rp <?= number_format($sum_terbayar) ?></div>
		</div>
	</div>
	<div class="col-md-3 col-sm-6">
		<div class="pe-summary-card">
			<div class="lbl"><i class="fa fa-hourglass-half"></i> Sisa Outstanding</div>
			<div class="val pe-num" style="color:#c0392b;">Rp <?= number_format($sum_sisa) ?></div>
		</div>
	</div>
</div>

<div class="box box-primary">
	<div class="box-header with-border">
		<h3 class="box-title"><i class="fa fa-reply"></i> Daftar Expense Report Disetujui</h3>
		<div class="pull-right">
			<span class="pe-badge pe-badge-out"><?= $cnt_out ?> Outstanding</span>
			<span class="pe-badge pe-badge-partial"><?= $cnt_partial ?> Partial</span>
			<span class="pe-badge pe-badge-paid"><?= $cnt_paid ?> Paid</span>
		</div>
	</div>
	<div class="box-body">
		<p class="text-muted" style="margin-bottom:12px;font-size:12px;">
			<i class="fa fa-info-circle"></i> Data yang tampil hanya <b>Expense Report (Pertanggungjawaban Kasbon)</b> dengan status <b>Disetujui</b>. Klik tombol <b>Proses</b> untuk melihat rincian, jurnal, dan mencatat pengembalian.
		</p>
		<div class="table-responsive">
			<table id="mytabledata" class="table table-bordered table-hover" width="100%">
				<thead>
					<tr>
						<th width="40" class="text-center">#</th>
						<th>No Dokumen</th>
						<th>Request By</th>
						<th>Tanggal</th>
						<th>Keperluan</th>
						<th class="text-center">Tipe</th>
						<th class="text-right">Nilai Pengembalian</th>
						<th class="text-right">Terbayar</th>
						<th class="text-right">Sisa</th>
						<th class="text-center">Status</th>
						<th class="text-center">Action</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($rows_render as $r):
						$record = $r['record'];
						$badge_map = [
							'out'     => '<span class="pe-badge pe-badge-out">Outstanding</span>',
							'partial' => '<span class="pe-badge pe-badge-partial">Partial</span>',
							'paid'    => '<span class="pe-badge pe-badge-paid">Paid</span>',
						];
					?>
						<tr>
							<td class="text-center"><?= $r['numb']; ?></td>
							<td><span class="pe-nodoc"><?= $record->no_doc ?></span></td>
							<td><?= $record->nama ? $record->nama : $record->created_by ?></td>
							<td><?= !empty($record->tgl_doc) ? date('d M Y', strtotime($record->tgl_doc)) : '-' ?></td>
							<td><?= $record->keperluan ?></td>
							<td class="text-center"><span class="label label-default" style="text-transform:capitalize;"><?= $record->tipe ?></span></td>
							<td class="text-right pe-num"><?= number_format($r['nilai']) ?></td>
							<td class="text-right pe-num"><?= number_format($r['ttl_kembali']) ?></td>
							<td class="text-right pe-num"><b><?= number_format($r['sisa']) ?></b></td>
							<td class="text-center"><?= $badge_map[$r['status_key']] ?></td>
							<td class="text-center">
								<?php if ($r['status_key'] == 'paid'): ?>
									<?php
									$view_payload = [];
									foreach ($r['detail'] as $dp) {
										$view_payload[] = [
											'id'     => $dp->id,
											'tgl'    => !empty($dp->transfer_tanggal) ? date('d M Y', strtotime($dp->transfer_tanggal)) : '-',
											'jumlah' => number_format($dp->transfer_jumlah),
											'status' => ($dp->status == 1 ? 'Disetujui' : ($dp->status == 2 ? 'Ditolak' : 'Menunggu Approval')),
											'files'  => !empty($dp->bukti_transfer) ? array_values(array_filter(array_map('trim', explode(';', $dp->bukti_transfer)))) : [],
										];
									}
									?>
									<button type="button" class="btn btn-info btn-xs pe-action-btn btn-view-return"
										data-nodoc="<?= htmlspecialchars($record->no_doc, ENT_QUOTES) ?>"
										data-detail="<?= htmlspecialchars(json_encode($view_payload), ENT_QUOTES) ?>"
										title="Lihat Detail Pengembalian">
										<i class="fa fa-eye"></i> View
									</button>
								<?php else: ?>
									<button type="button" class="btn btn-primary btn-xs pe-action-btn" onclick="edit(<?= $record->ids ?>)" title="Proses Pengembalian">
										<i class="fa fa-check-square-o"></i> Proses
									</button>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<div class="modal fade" id="Mymodal">
	<div class="modal-dialog" style="width:92%;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title"><i class="fa fa-reply"></i> Proses Pengembalian Expense Report</h4>
			</div>
			<div class="modal-body" id="listexpense">
				<div class="text-center text-muted" style="padding:40px;">
					<i class="fa fa-spinner fa-spin fa-2x"></i>
					<div style="margin-top:10px;">Memuat data...</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Modal detail pengembalian (View) -->
<div class="modal fade" id="ModalViewReturn">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title"><i class="fa fa-eye"></i> Detail Pengembalian <small class="text-muted view-return-nodoc"></small></h4>
			</div>
			<div class="modal-body" id="view_return_body"></div>
		</div>
	</div>

<!-- Modal detail lengkap pengembalian (sama seperti modal Proses, read-only) -->
<div class="modal fade" id="ModalDetailReturn">
	<div class="modal-dialog" style="width:92%;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title"><i class="fa fa-file-text-o"></i> Detail Pengembalian Expense Report</h4>
			</div>
			<div class="modal-body" id="detail_return_body">
				<div class="text-center text-muted" style="padding:40px;">
					<i class="fa fa-spinner fa-spin fa-2x"></i>
					<div style="margin-top:10px;">Memuat data...</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script src="https://cdn.datatables.net/2.0.2/js/dataTables.min.js"></script>
<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>
<script type="text/javascript">
	$("#mytabledata").DataTable({
		"order": [],
		"pageLength": 10,
		"language": {
			"emptyTable": "Tidak ada Expense Report yang perlu diproses",
			"zeroRecords": "Data tidak ditemukan",
			"search": "Cari:",
			"lengthMenu": "Tampilkan _MENU_ data",
			"info": "Menampilkan _START_ - _END_ dari _TOTAL_ data",
			"infoEmpty": "Menampilkan 0 data",
			"paginate": {
				"first": "Awal",
				"last": "Akhir",
				"next": "›",
				"previous": "‹"
			}
		}
	});
	$(".divide").divide();

	function edit(id) {
		$("#listexpense").html('<div class="text-center text-muted" style="padding:40px;"><i class="fa fa-spinner fa-spin fa-2x"></i><div style="margin-top:10px;">Memuat data...</div></div>');
		$("#Mymodal").modal();
		$("#listexpense").load(base_url + 'expense/review/' + id);
	};

	// Tampilkan detail pengembalian (untuk dokumen yang sudah Paid / sisa 0)
	$(document).on('click', '.btn-view-return', function() {
		var noDoc = $(this).data('nodoc');
		var detail = $(this).data('detail') || [];
		$('.view-return-nodoc').text(noDoc ? '(' + noDoc + ')' : '');

		var html = '';
		if (!detail.length) {
			html = '<div class="text-center text-muted" style="padding:20px;">Belum ada data pengembalian.</div>';
		} else {
			html += '<div class="table-responsive"><table class="table table-bordered" style="font-size:13px;">';
			html += '<thead><tr style="background:#f4f6f9;">' +
				'<th class="text-center" width="40">#</th>' +
				'<th>Tanggal Transfer</th>' +
				'<th class="text-right">Nilai Transfer</th>' +
				'<th class="text-center">Status</th>' +
				'<th class="text-center">Bukti Transfer</th>' +
				'<th class="text-center">Detail</th>' +
				'</tr></thead><tbody>';
			detail.forEach(function(d, i) {
				var buktiHtml = '';
				if (d.files && d.files.length) {
					d.files.forEach(function(f) {
						var isPdf = /\.pdf$/i.test(f);
						buktiHtml += '<a href="' + base_url + 'assets/expense/' + f + '" target="_blank" class="pe-badge pe-badge-paid" style="margin:1px;display:inline-block;" title="' + f + '"><i class="fa ' + (isPdf ? 'fa-file-pdf-o' : 'fa-file-image-o') + '"></i> Lihat</a>';
					});
				} else {
					buktiHtml = '<span class="text-muted">-</span>';
				}
				html += '<tr>' +
					'<td class="text-center">' + (i + 1) + '</td>' +
					'<td>' + d.tgl + '</td>' +
					'<td class="text-right pe-num">' + d.jumlah + '</td>' +
					'<td class="text-center">' + d.status + '</td>' +
					'<td class="text-center">' + buktiHtml + '</td>' +
					'<td class="text-center"><button type="button" class="btn btn-info btn-xs pe-action-btn btn-view-detail" data-id="' + d.id + '"><i class="fa fa-search-plus"></i> View</button></td>' +
					'</tr>';
			});
			html += '</tbody></table></div>';
		}
		$('#view_return_body').html(html);
		$('#ModalViewReturn').modal();
	});

	// Buka detail lengkap pengembalian (modal sama seperti Proses, read-only)
	$(document).on('click', '.btn-view-detail', function() {
		var idPengembalian = $(this).data('id');
		$('#detail_return_body').html('<div class="text-center text-muted" style="padding:40px;"><i class="fa fa-spinner fa-spin fa-2x"></i><div style="margin-top:10px;">Memuat data...</div></div>');
		$('#ModalDetailReturn').modal();
		$('#detail_return_body').load(base_url + 'expense/review_return/' + idPengembalian);
	});
</script>