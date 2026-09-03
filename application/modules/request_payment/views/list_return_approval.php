<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">

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

	.pe-badge-wait {
		background: #e6f0fb;
		color: #2b6cb0;
	}

	.pe-action-btn {
		border-radius: 6px;
	}

	#Mymodal .modal-header,
	#ModalBukti .modal-header {
		background: #f8fafc;
		border-bottom: 1px solid #e2e8f0;
	}

	#Mymodal .modal-title,
	#ModalBukti .modal-title {
		font-weight: 700;
		color: #2d3748;
	}

	.pe-bukti-item {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 10px 14px;
		border: 1px solid #e2e8f0;
		border-radius: 6px;
		margin-bottom: 8px;
		background: #fff;
	}
</style>

<div class="box box-primary">
	<div class="box-header with-border">
		<h3 class="box-title"><i class="fa fa-check-square-o"></i> Approval Pengembalian Expense</h3>
	</div>
	<div class="box-body">
		<p class="text-muted" style="margin-bottom:12px;font-size:12px;">
			<i class="fa fa-info-circle"></i> Data pengembalian expense yang <b>menunggu approval</b>. Klik <b>Approve</b> untuk menyetujui atau <b>Reject</b> untuk menolak.
		</p>
		<div class="table-responsive">
			<table id="mytabledata" class="table table-bordered table-hover" width="100%">
				<thead>
					<tr>
						<th width="40" class="text-center">#</th>
						<th>No Dokumen</th>
						<th>Tanggal Transfer</th>
						<th class="text-right">Nilai Pengembalian</th>
						<th class="text-center">Bukti Transfer</th>
						<th class="text-center">Status</th>
						<th class="text-center">Action</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$no = 1;
					if (!empty($data_pengembalian)):
						foreach ($data_pengembalian as $item):
							$bukti = !empty($item->bukti_transfer) ? array_values(array_filter(array_map('trim', explode(';', $item->bukti_transfer)))) : [];
					?>
							<tr>
								<td class="text-center"><?= $no ?></td>
								<td><span class="pe-nodoc"><?= $item->no_doc ?></span></td>
								<td><?= !empty($item->transfer_tanggal) ? date('d M Y', strtotime($item->transfer_tanggal)) : '-' ?></td>
								<td class="text-right pe-num"><b><?= number_format($item->transfer_jumlah) ?></b></td>
								<td class="text-center">
									<?php if (!empty($bukti)): ?>
										<button type="button" class="btn btn-default btn-xs pe-action-btn btn-bukti"
											data-nodoc="<?= htmlspecialchars($item->no_doc, ENT_QUOTES) ?>"
											data-files="<?= htmlspecialchars(implode(';', $bukti), ENT_QUOTES) ?>">
											<i class="fa fa-paperclip"></i> Lihat Bukti (<?= count($bukti) ?>)
										</button>
									<?php else: ?>
										<span class="text-muted">-</span>
									<?php endif; ?>
								</td>
								<td class="text-center"><span class="pe-badge pe-badge-wait">Waiting Approval</span></td>
								<td class="text-center">
									<button type="button" class="btn btn-success btn-xs pe-action-btn approval" data-id="<?= $item->id ?>" title="Approve">
										<i class="fa fa-check"></i> Approve
									</button>
									<button type="button" class="btn btn-danger btn-xs pe-action-btn reject" data-id="<?= $item->id ?>" title="Reject">
										<i class="fa fa-close"></i> Reject
									</button>
								</td>
							</tr>
					<?php
							$no++;
						endforeach;
					endif;
					?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<!-- Modal daftar bukti transfer -->
<div class="modal fade" id="ModalBukti">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title"><i class="fa fa-paperclip"></i> Bukti Transfer <small class="text-muted bukti-nodoc"></small></h4>
			</div>
			<div class="modal-body" id="bukti_list"></div>
		</div>
	</div>
</div>

<div class="modal fade" id="Mymodal">
	<div class="modal-dialog" style="width:92%;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title"><i class="fa fa-reply"></i> View Expense</h4>
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

<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
		$("#mytabledata").DataTable({
			"order": [],
			"pageLength": 10,
			"language": {
				"emptyTable": "Tidak ada data pengembalian yang perlu diproses",
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
	});

	// Tampilkan daftar bukti transfer di dalam modal
	$(document).on('click', '.btn-bukti', function() {
		var noDoc = $(this).data('nodoc');
		var files = String($(this).data('files')).split(';').filter(function(f) {
			return f && f.trim() !== '';
		});
		$('.bukti-nodoc').text(noDoc ? '(' + noDoc + ')' : '');

		var html = '';
		if (files.length === 0) {
			html = '<div class="text-center text-muted" style="padding:20px;">Tidak ada bukti transfer.</div>';
		} else {
			files.forEach(function(f, i) {
				var isPdf = /\.pdf$/i.test(f);
				var url = base_url + 'assets/expense/' + f;
				html += '<div class="pe-bukti-item">' +
					'<span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:70%;">' +
					'<i class="fa ' + (isPdf ? 'fa-file-pdf-o' : 'fa-file-image-o') + '" style="color:#3c8dbc;"></i> ' + (i + 1) + '. ' + f + '</span>' +
					'<a href="' + url + '" target="_blank" class="btn btn-primary btn-xs pe-action-btn"><i class="fa fa-external-link"></i> Buka</a>' +
					'</div>';
			});
		}
		$('#bukti_list').html(html);
		$('#ModalBukti').modal();
	});

	$(document).on('click', '.approval', function() {
		var id = $(this).data('id');

		swal({
				title: "Anda Yakin?",
				text: "Pengembalian expense akan di approve !",
				type: "info",
				showCancelButton: true,
				confirmButtonText: "Ya, Approve!",
				cancelButtonText: "Tidak!",
				closeOnConfirm: false,
				closeOnCancel: true
			},
			function(isConfirm) {
				if (isConfirm) {
					$.ajax({
						url: siteurl + active_controller + 'approve_pengembalian_expense',
						dataType: "json",
						type: 'POST',
						data: {
							'id': id
						},
						cache: false,
						success: function(msg) {
							if (msg.status == '1') {
								swal({
									title: "Sukses!",
									text: "Data Pengembalian berhasil di approve",
									type: "success",
									timer: 1500,
									showConfirmButton: false
								});
								window.location.reload();
							} else {
								swal({
									title: "Gagal!",
									text: "Data Pengembalian gagal Di Approve !",
									type: "error",
									timer: 1500,
									showConfirmButton: false
								});
							};
						},
						error: function(msg) {
							swal({
								title: "Gagal!",
								text: "Ajax Data Gagal Di Proses",
								type: "error",
								timer: 1500,
								showConfirmButton: false
							});
						}
					});
				}
			});
	});

	$(document).on('click', '.reject', function() {
		var id = $(this).data('id');

		swal({
				title: "Anda Yakin?",
				text: "Pengembalian expense akan di reject !",
				type: "info",
				showCancelButton: true,
				confirmButtonText: "Ya, Reject!",
				cancelButtonText: "Tidak!",
				closeOnConfirm: false,
				closeOnCancel: true
			},
			function(isConfirm) {
				if (isConfirm) {
					$.ajax({
						url: siteurl + active_controller + 'reject_pengembalian_expense',
						dataType: "json",
						type: 'POST',
						data: {
							'id': id
						},
						cache: false,
						success: function(msg) {
							if (msg.status == '1') {
								swal({
									title: "Sukses!",
									text: "Data Pengembalian berhasil di reject",
									type: "success",
									timer: 1500,
									showConfirmButton: false
								});
								window.location.reload();
							} else {
								swal({
									title: "Gagal!",
									text: "Data Pengembalian gagal Di Reject !",
									type: "error",
									timer: 1500,
									showConfirmButton: false
								});
							};
						},
						error: function(msg) {
							swal({
								title: "Gagal!",
								text: "Ajax Data Gagal Di Proses",
								type: "error",
								timer: 1500,
								showConfirmButton: false
							});
						}
					});
				}
			});
	});
</script>
