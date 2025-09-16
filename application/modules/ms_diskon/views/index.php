<?php
$ENABLE_ADD     = has_permission('Master_Discount.Add');
$ENABLE_MANAGE  = has_permission('Master_Discount.Manage');
$ENABLE_VIEW    = has_permission('Master_Discount.View');
$ENABLE_DELETE  = has_permission('Master_Discount.Delete');
?>
<style type="text/css">
	thead input {
		width: 100%;
	}
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<div class="box">
	<div class="box-header">
		<?php if ($ENABLE_ADD) : ?>
			<a class="btn btn-success btn-sm" href="<?= base_url('/ms_diskon/AddDiskon/') ?>" title="Add New"><i class="fa fa-plus">&nbsp;</i>Add</i></a>
		<?php endif; ?>

		<span class="pull-right">
		</span>
	</div>
	<!-- /.box-header -->
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped">
			<thead>
				<tr class='bg-blue'>
					<th width='3%'>No</th>
					<th>Tingkatan</th>
					<th>Keterangan</th>
					<th>Discount Awal</th>
					<th>Discount Akhir</th>
					<th>Approve By</th>
					<th>Aksi</th>
				</tr>
			</thead>
			<tbody>
				<?php if (empty($results)) {
				} else {
					$numb = 0;
					foreach ($results as $record) {
						$numb++; ?>
						<tr>
							<td><?= $numb; ?></td>
							<td><?= $record->tingkatan ?></td>
							<td><?= $record->keterangan ?></td>
							<td><?= $record->diskon_awal ?></td>
							<td><?= $record->diskon_akhir ?></td>
							<td><?= $record->nm_lengkap ?></td>
							<td style="padding-left:20px">

								<?php if ($ENABLE_MANAGE) : ?>
									<a class="btn btn-success btn-sm" href="<?= base_url('ms_diskon/AddDiskon/') . $record->id; ?>" title="Edit" data-id="<?= $record->id ?>"><i class="fa fa-edit"></i>
									</a>
								<?php endif; ?>

								<?php if ($ENABLE_DELETE) : ?>
									<a class="btn btn-danger btn-sm delete" href="javascript:void(0)" title="Delete" data-id="<?= $record->id ?>"><i class="fa fa-trash"></i>
									</a>
								<?php endif; ?>
							</td>

						</tr>
				<?php }
				}  ?>
			</tbody>
		</table>
	</div>
	<!-- /.box-body -->
</div>

<!-- awal untuk modal dialog -->
<!-- Modal -->
<div class="modal modal-primary" id="dialog-rekap" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel"><span class="fa fa-file-pdf-o"></span>&nbsp;Rekap Data Customer</h4>
			</div>
			<div class="modal-body" id="MyModalBody">
				...
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">
					<span class="glyphicon glyphicon-remove"></span> Close</button>
			</div>
		</div>
	</div>
</div>

<div class="modal modal-default fade" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel"><span class="fa fa-users"></span>&nbsp;Data Inventory</h4>
			</div>
			<div class="modal-body" id="ModalView">
				...
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" data-dismiss="modal">
					<span class="glyphicon glyphicon-remove"></span> Close</button>
			</div>
		</div>
	</div>
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<!-- page script -->
<script type="text/javascript">
	// alert(siteurl + active_controller + 'editDiskon');
	$(document).on('click', '.edit', function(e) {
		var id = $(this).data('id');
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Edit Diskon</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + active_controller + 'editDiskon/' + id,
			success: function(data) {
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			}
		})
	});



	// DELETE DATA
	$(document).on('click', '.delete', function(e) {
		e.preventDefault()
		var id = $(this).data('id');
		// alert(id);
		swal({
				title: "Anda Yakin?",
				text: "Data Ini akan di hapus.",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-info",
				confirmButtonText: "Ya, Hapus!",
				cancelButtonText: "Batal",
				closeOnConfirm: false
			},
			function() {
				$.ajax({
					type: 'POST',
					url: siteurl + 'ms_diskon/deleteDiskon',
					dataType: "json",
					data: {
						'id': id
					},
					success: function(result) {
						if (result.status == '1') {
							swal({
									title: "Sukses",
									text: "Data Inventory berhasil dihapus.",
									type: "success"
								},
								function() {
									window.location.reload(true);
								})
						} else {
							swal({
								title: "Error",
								text: "Data error. Gagal hapus data",
								type: "error"
							})

						}
					},
					error: function() {
						swal({
							title: "Error",
							text: "Data error. Gagal request Ajax",
							type: "error"
						})
					}
				})
			});

	})

	$(function() {
		// var table = $('#example1').DataTable( {
		// orderCellsTop: true,
		// fixedHeader: true
		// } );
		$("#form-area").hide();
	});


	//Delete

	function PreviewPdf(id) {
		param = id;
		tujuan = 'customer/print_request/' + param;

		$(".modal-body").html('<iframe src="' + tujuan + '" frameborder="no" width="570" height="400"></iframe>');
	}

	function PreviewRekap() {
		tujuan = 'customer/rekap_pdf';
		$(".modal-body").html('<iframe src="' + tujuan + '" frameborder="no" width="100%" height="400"></iframe>');
	}
</script>