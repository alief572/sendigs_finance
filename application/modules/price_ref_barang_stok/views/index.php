<?php
    $ENABLE_ADD     = has_permission('Price_Ref_Barang_Stok.Add');
    $ENABLE_MANAGE  = has_permission('Price_Ref_Barang_Stok.Manage');
    $ENABLE_VIEW    = has_permission('Price_Ref_Barang_Stok.View');
    $ENABLE_DELETE  = has_permission('Price_Ref_Barang_Stok.Delete');
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
<link rel="stylesheet" href="<?= base_url('assets/plugins/select2/select2.min.css')?>">

<div class="box box-success">
	<div class="box-header with-border">
		<h3 class="box-title"><i class="fa fa-check-square-o"></i> Approval Price Reference (Barang Stok)</h3>
		<span class="pull-right">
			<button type="button" class="btn btn-sm btn-info" id="btn-history-price"><i class="fa fa-history"></i> History Perubahan Harga</button>
		</span>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<table id="table-approval" class="table table-bordered table-striped table-hover" width="100%">
			<thead>
				<tr class="bg-green">
					<th class="text-center" width="4%">#</th>
					<th class="text-center" width="15%">No. Dokumen</th>
					<th class="text-center" width="10%">Tanggal</th>
					<th class="text-center" width="10%">Kurs (IDR/USD)</th>
					<th class="text-center" width="10%">Total Item</th>
					<th class="text-center" width="12%">Diajukan Oleh</th>
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
      <div class="modal-header bg-green">
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
		$('#table-approval').DataTable({
			ajax: {
				url: siteurl + 'price_ref_barang_stok/get_data',
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
			order: [],
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
</script>
