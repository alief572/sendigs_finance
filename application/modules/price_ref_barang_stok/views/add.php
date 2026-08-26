<?php
	$nm_cat = !empty($header->nm_category) ? strtoupper($header->nm_category) : 'Kategori';
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/select2/select2.min.css')?>">
<style>
	.table-custom th, .table-custom td {
		vertical-align: middle !important;
		padding: 6px 8px !important;
	}
	.highlight-new {
		background-color: #eafaf1 !important;
	}
</style>

<div class="box box-success">
	<div class="box-header with-border">
		<h3 class="box-title"><i class="fa fa-check-square-o"></i> Review & Approval Pengajuan Price Reference &mdash; <b><?= $nm_cat ?></b></h3>
		<div class="box-tools pull-right">
			<a href="<?= base_url('price_ref_barang_stok') ?>" class="btn btn-sm btn-default"><i class="fa fa-arrow-left"></i> Kembali</a>
		</div>
	</div>
	
	<form id="form_approval" method="post" autocomplete="off">
		<input type="hidden" name="no_doc" id="no_doc" value="<?= $no_doc ?>">

		<div class="box-body">
			<!-- Header Card Info -->
			<div class="panel panel-default">
				<div class="panel-heading" style="background:#eafaf1; font-weight:bold;"><i class="fa fa-info-circle"></i> Informasi Pengajuan Dokumen</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-md-3">
							<div class="form-group">
								<label>No. Dokumen Pengajuan</label>
								<input type="text" class="form-control" value="<?= $no_doc ?>" readonly style="font-weight:bold; background:#f9f9f9;">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Kategori Barang Stok</label>
								<input type="text" class="form-control" value="<?= $nm_cat ?>" readonly style="font-weight:bold; background:#f9f9f9; color:#00a65a;">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Tanggal Pengajuan</label>
								<input type="text" class="form-control" value="<?= date('d-M-Y', strtotime($header->tanggal_doc)) ?>" readonly>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label>Diajukan Oleh</label>
								<input type="text" class="form-control" value="<?= $header->pembuat ? $header->pembuat : '-' ?>" readonly>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label>Catatan Pengaju</label>
								<textarea class="form-control" rows="2" readonly><?= htmlspecialchars($header->note ?? '-') ?></textarea>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label>File Evidence Terlampir</label>
								<div>
									<?php if(!empty($files)): ?>
										<button type="button" class="btn btn-sm btn-primary btn-view-evidence" data-no_doc="<?= $header->no_doc ?? '' ?>" title="Lihat Evidence Files">
											<i class="fa fa-paperclip"></i> <b><?= count($files) ?> File Terlampir</b> (Lihat File)
										</button>
									<?php else: ?>
										<span class="text-muted">- Tidak ada file bukti terlampir -</span>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Rincian Barang Pengajuan -->
			<div class="panel panel-success">
				<div class="panel-heading" style="font-weight:bold;">
					<i class="fa fa-tags"></i> DAFTAR BARANG YANG DIAJUKAN &mdash; <?= $nm_cat ?> (<?= count($details) ?> item)
				</div>
				<div class="panel-body" style="padding: 0;">
					<div class="table-responsive">
						<table class="table table-bordered table-striped table-hover table-custom" width="100%" style="margin:0;">
							<thead>
								<tr class="bg-green">
									<th rowspan="2" class="text-center" width="3%">#</th>
									<th rowspan="2" class="text-center" width="12%">Kode Stok</th>
									<th rowspan="2" class="text-center" width="27%">Nama Barang & Spesifikasi</th>
									<th rowspan="2" class="text-center" width="8%">Satuan</th>
									<th colspan="2" class="text-center bg-gray-active" width="22%">Harga Lama (Before)</th>
									<th colspan="2" class="text-center" style="background:#1e824c; color:#fff;" width="22%">Pengajuan Harga Baru (After)</th>
									<th rowspan="2" class="text-center" width="10%">Expired</th>
								</tr>
								<tr class="bg-green">
									<!-- Before -->
									<th class="text-center bg-gray">Lower (IDR)</th>
									<th class="text-center bg-gray">Higher (IDR)</th>
									<!-- New -->
									<th class="text-center" style="background:#27ae60; color:#fff;">Lower (IDR)</th>
									<th class="text-center" style="background:#1e824c; color:#fff;">Higher (IDR)</th>
								</tr>
							</thead>
							<tbody>
								<?php 
								$no = 0;
								foreach($details as $d): 
									$no++;
									$id_item = $d->id_barang;
								?>
									<tr class="highlight-new">
										<td class="text-center">
											<?= $no ?>
											<input type="hidden" name="items[<?= $id_item ?>][id_barang]" value="<?= $id_item ?>">
										</td>
										<td><b><?= strtoupper($d->id_stock ?? '-') ?></b></td>
										<td>
											<b><?= strtoupper($d->stock_name) ?></b>
											<?php if(!empty($d->spec)): ?>
												<br><small class="text-muted"><?= $d->spec ?></small>
											<?php endif; ?>
										</td>
										<td class="text-center"><?= $d->nm_satuan ?? '-' ?></td>
										
										<!-- Before IDR -->
										<td class="text-right bg-gray"><?= number_format($d->price_ref_before, 0) ?></td>
										<td class="text-right bg-gray"><?= number_format($d->price_ref_high_before, 0) ?></td>

										<!-- New Lower IDR -->
										<td class="text-right text-bold text-success">Rp <?= number_format($d->price_ref_new, 0) ?></td>

										<!-- New Higher IDR -->
										<td class="text-right text-bold text-success">Rp <?= number_format($d->price_ref_high_new, 0) ?></td>

										<!-- Expired -->
										<td class="text-center">
											<?php
												$exp_text = $d->expired . ' Bulan';
												if ($d->expired == 6) $exp_text = 'Semester';
												if ($d->expired == 12) $exp_text = 'Tahunan';
												echo $exp_text;
											?>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>

			<!-- Decision Box -->
			<div class="panel panel-default" style="margin-top: 15px; border-top: 3px solid #00a65a;">
				<div class="panel-heading" style="font-weight:bold;"><i class="fa fa-gavel"></i> Keputusan Approval</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label>Alasan Penolakan / Catatan Approval <span class="text-danger" id="lbl-reason-req" style="display:none;">* Wajib diisi jika Reject</span></label>
								<textarea class="form-control" name="reason" id="reason" rows="3" placeholder="Masukkan alasan penolakan jika menolak, atau catatan approval jika diperlukan..."></textarea>
							</div>
						</div>
					</div>
				</div>
				<div class="panel-footer text-right">
					<a href="<?= base_url('price_ref_barang_stok') ?>" class="btn btn-default btn-lg pull-left"><i class="fa fa-arrow-left"></i> Batal</a>
					<button type="button" class="btn btn-danger btn-lg" id="btn-do-reject" style="margin-right:10px;"><i class="fa fa-times"></i> Reject Pengajuan</button>
					<button type="button" class="btn btn-success btn-lg" id="btn-do-approve"><i class="fa fa-check"></i> Approve Pengajuan</button>
				</div>
			</div>
		</div>
	</form>
</div>


<script src="<?= base_url('assets/plugins/select2/select2.full.min.js')?>"></script>

<script type="text/javascript">
	$(document).on('click', '#btn-do-approve', function() {
		var no_doc = $('#no_doc').val();
		var reason = $('#reason').val();

		swal({
			title: "Konfirmasi Approve",
			text: "Apakah Anda yakin ingin menyetujui pengajuan harga pada dokumen " + no_doc + "? Harga master barang akan langsung diperbarui.",
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: "#00a65a",
			confirmButtonText: "Ya, Approve!",
			cancelButtonText: "Batal",
			closeOnConfirm: false
		}, function(isConfirm) {
			if (isConfirm) {
				$('#btn-do-approve').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Memproses...');
				
				$.ajax({
					url: siteurl + 'price_ref_barang_stok/process_approval',
					type: 'POST',
					dataType: 'json',
					data: {
						no_doc: no_doc,
						action: 'approve',
						reason: reason
					},
					success: function(res) {
						$('#btn-do-approve').prop('disabled', false).html('<i class="fa fa-check"></i> Approve Pengajuan');
						if (res.status == 1) {
							swal({
								title: "Disetujui!",
								text: res.pesan,
								type: "success"
							}, function() {
								window.location.href = siteurl + 'price_ref_barang_stok';
							});
						} else {
							swal("Gagal!", res.pesan, "error");
						}
					},
					error: function() {
						$('#btn-do-approve').prop('disabled', false).html('<i class="fa fa-check"></i> Approve Pengajuan');
						swal("Error!", "Terjadi kesalahan pada server saat memproses approval.", "error");
					}
				});
			}
		});
	});

	$(document).on('click', '#btn-do-reject', function() {
		var no_doc = $('#no_doc').val();
		var reason = $('#reason').val();

		if (!reason || reason.trim() === '') {
			$('#lbl-reason-req').show();
			$('#reason').focus();
			swal("Peringatan!", "Harap masukkan alasan penolakan pada kolom catatan/alasan!", "warning");
			return;
		}

		swal({
			title: "Konfirmasi Reject",
			text: "Apakah Anda yakin ingin menolak pengajuan harga pada dokumen " + no_doc + "?",
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: "#dd4b39",
			confirmButtonText: "Ya, Reject!",
			cancelButtonText: "Batal",
			closeOnConfirm: false
		}, function(isConfirm) {
			if (isConfirm) {
				$('#btn-do-reject').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Memproses...');
				
				$.ajax({
					url: siteurl + 'price_ref_barang_stok/process_approval',
					type: 'POST',
					dataType: 'json',
					data: {
						no_doc: no_doc,
						action: 'reject',
						reason: reason
					},
					success: function(res) {
						$('#btn-do-reject').prop('disabled', false).html('<i class="fa fa-times"></i> Reject Pengajuan');
						if (res.status == 1) {
							swal({
								title: "Ditolak!",
								text: res.pesan,
								type: "success"
							}, function() {
								window.location.href = siteurl + 'price_ref_barang_stok';
							});
						} else {
							swal("Gagal!", res.pesan, "error");
						}
					},
					error: function() {
						$('#btn-do-reject').prop('disabled', false).html('<i class="fa fa-times"></i> Reject Pengajuan');
						swal("Error!", "Terjadi kesalahan pada server saat memproses penolakan.", "error");
					}
				});
			}
		});
	});
</script>

<!-- Modal Evidence Files -->
<div class="modal fade" id="modal-evidence" role="dialog" aria-labelledby="modalEvidenceLabel" aria-hidden="true" style="z-index: 1060;">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header bg-green">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title"><i class="fa fa-paperclip"></i> Daftar File Evidence Terlampir</h4>
      </div>
      <div class="modal-body" id="modal-evidence-body">
		<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><br>Memuat file...</div>
      </div>
	  <div class="modal-footer">
		<button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-close"></i> Tutup</button>
	  </div>
    </div>
  </div>
</div>

<script type="text/javascript">
	$(document).on('click', '.btn-view-evidence', function() {
		var no_doc = $(this).data('no_doc');
		$('#modal-evidence-body').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><br>Memuat data file...</div>');
		$('#modal-evidence').modal('show');
		$.ajax({
			url: siteurl + 'price_sup_barang_stok/get_evidence_modal/' + encodeURIComponent(no_doc),
			type: 'GET',
			success: function(html) {
				$('#modal-evidence-body').html(html);
			},
			error: function() {
				$('#modal-evidence-body').html('<div class="alert alert-danger">Gagal memuat daftar file.</div>');
			}
		});
	});
</script>

