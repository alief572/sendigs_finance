<?php
    $ENABLE_ADD     = has_permission('Price_Supplier_Barang_Stok.Add');
    $ENABLE_MANAGE  = has_permission('Price_Supplier_Barang_Stok.Manage');
    $ENABLE_VIEW    = has_permission('Price_Supplier_Barang_Stok.View');
    $ENABLE_DELETE  = has_permission('Price_Supplier_Barang_Stok.Delete');
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
<link rel="stylesheet" href="<?= base_url('assets/plugins/select2/select2.min.css')?>">

<div class="box box-primary">
	<div class="box-header with-border">
		<h3 class="box-title"><i class="fa fa-list"></i> Daftar Pengajuan Price Supplier (Barang Stok)</h3>
		<span class="pull-right">
			<button type="button" class="btn btn-sm btn-info" id="btn-history-price"><i class="fa fa-history"></i> History Harga Barang</button>
			<?php if($ENABLE_ADD) : ?>
				<a class="btn btn-sm btn-success" href="<?= base_url('price_sup_barang_stok/add') ?>"><i class="fa fa-plus"></i> Tambah Pengajuan Baru</a>
			<?php endif; ?>
		</span>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<table id="table-pengajuan" class="table table-bordered table-striped table-hover" width="100%">
			<thead>
				<tr class="bg-blue">
					<th class="text-center" width="4%">#</th>
					<th class="text-center" width="15%">No. Dokumen</th>
					<th class="text-center" width="10%">Tanggal</th>
					<th class="text-center" width="10%">Kurs (IDR/USD)</th>
					<th class="text-center" width="10%">Total Item</th>
					<th class="text-center" width="12%">Dibuat Oleh</th>
					<th class="text-center" width="12%">Status</th>
					<th class="text-center" width="15%">Evidence Files</th>
					<th class="text-center" width="12%">Aksi</th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>
	<!-- /.box-body -->
</div>

<!-- Modal Dialog Popup View Detail -->
<div class="modal fade" id="modal-view-doc" role="dialog" aria-labelledby="modalViewLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" style="width: 90%;">
    <div class="modal-content">
      <div class="modal-header bg-blue">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title"><i class="fa fa-file-text-o"></i> Detail Pengajuan Price Supplier</h4>
      </div>
      <div class="modal-body" id="modal-view-body">
		<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><br>Memuat data...</div>
      </div>
	  <div class="modal-footer">
		<button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-close"></i> Tutup</button>
	  </div>
    </div>
  </div>
</div>

<!-- Modal History Harga Barang -->
<div class="modal fade" id="modal-history" role="dialog" aria-labelledby="modalHistoryLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" style="width: 92%;">
    <div class="modal-content">
      <div class="modal-header bg-purple">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title"><i class="fa fa-history"></i> Tracking History Perubahan Harga Barang Stok</h4>
      </div>
      <div class="modal-body" id="modal-history-body">
		<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><br>Memuat data...</div>
      </div>
	  <div class="modal-footer">
		<button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-close"></i> Tutup</button>
	  </div>
    </div>
  </div>
</div>

<!-- DataTables & Select2 Scripts -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/select2/select2.full.min.js')?>"></script>

<script type="text/javascript">
	$(document).ready(function() {
		load_table();
	});

	function load_table() {
		$('#table-pengajuan').DataTable({
			ajax: {
				url: siteurl + 'price_sup_barang_stok/get_data',
				type: "POST",
				dataType: "JSON"
			},
			columns: [
				{ data: 'no', className: 'text-center' },
				{ data: 'no_doc', className: 'text-center' },
				{ data: 'tanggal_doc', className: 'text-center' },
				{ data: 'kurs', className: 'text-right' },
				{ data: 'total_item', className: 'text-center' },
				{ data: 'pembuat', className: 'text-center' },
				{ data: 'status', className: 'text-center' },
				{ data: 'files', className: 'text-left' },
				{ data: 'action', className: 'text-center', orderable: false, searchable: false }
			],
			processing: true,
			serverSide: true,
			destroy: true,
			order: [[1, 'desc']],
			paging: true
		});
	}

	$(document).on('click', '.view_doc', function() {
		var no_doc = $(this).data('no_doc');
		$('#modal-view-body').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><br>Memuat data...</div>');
		$('#modal-view-doc').modal('show');
		$.ajax({
			url: siteurl + 'price_sup_barang_stok/view/' + encodeURIComponent(no_doc),
			type: 'GET',
			success: function(html) {
				$('#modal-view-body').html(html);
			},
			error: function() {
				$('#modal-view-body').html('<div class="alert alert-danger">Gagal memuat detail dokumen.</div>');
			}
		});
	});

	$(document).on('click', '#btn-history-price', function() {
		$('#modal-history-body').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><br>Memuat data...</div>');
		$('#modal-history').modal('show');
		$.ajax({
			url: siteurl + 'price_sup_barang_stok/history',
			type: 'GET',
			success: function(html) {
				$('#modal-history-body').html(html);
			},
			error: function() {
				$('#modal-history-body').html('<div class="alert alert-danger">Gagal memuat histori harga.</div>');
			}
		});
	});

	$(document).on('click', '.delete_doc', function() {
		var no_doc = $(this).data('no_doc');
		swal({
			title: "Hapus Dokumen Pengajuan?",
			text: "Dokumen pengajuan " + no_doc + " akan dihapus secara permanen!",
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: "#DD6B55",
			confirmButtonText: "Ya, Hapus!",
			cancelButtonText: "Batal",
			closeOnConfirm: false
		}, function(isConfirm) {
			if (isConfirm) {
				$.ajax({
					url: siteurl + 'price_sup_barang_stok/delete_data',
					type: 'POST',
					dataType: 'json',
					data: { no_doc: no_doc },
					success: function(res) {
						if (res.status == 1) {
							swal({
								title: "Berhasil!",
								text: res.pesan,
								type: "success",
								timer: 1500,
								showConfirmButton: false
							});
							$('#table-pengajuan').DataTable().ajax.reload(null, false);
						} else {
							swal("Gagal!", res.pesan, "error");
						}
					},
					error: function() {
						swal("Error!", "Terjadi kesalahan pada server saat menghapus data.", "error");
					}
				});
			}
		});
	});
</script>
