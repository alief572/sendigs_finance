<?php
$ENABLE_ADD     = has_permission('Costcenter.Add');
$ENABLE_MANAGE  = has_permission('Costcenter.Manage');
$ENABLE_VIEW    = has_permission('Costcenter.View');
$ENABLE_DELETE  = has_permission('Costcenter.Delete');
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
			<a class="btn btn-success btn-sm" href="<?= base_url('costcenter/add') ?>" title="Add"> <i class="fa fa-plus">&nbsp;</i>Add</a>
		<?php endif; ?>

		<span class="pull-right">
		</span>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped">
			<thead>
				<tr>
					<th>#</th>
					<th class='text-center'>Costcenter Name</th>
					<th class='text-center'>Qty MP Shift1</th>
					<th class='text-center'>Qty MP Shift2</th>
					<th class='text-center'>Qty MP Shift3</th>
					<th class='text-center'>Shift1</th>
					<th class='text-center'>Shift2</th>
					<th class='text-center'>Shift3</th>
					<th class='text-center'>Action</th>
				</tr>
			</thead>

			<tbody>
				<?php if (empty($results)) {
				} else {
					$numb = 0;
					foreach ($results as $record) {
						$numb++;
						$s1 = ($record->shift1 == 'Y') ? 'blue' : 'red';
						$s2 = ($record->shift2 == 'Y') ? 'blue' : 'red';
						$s3 = ($record->shift3 == 'Y') ? 'blue' : 'red';

						$sx1 = ($record->shift1 == 'Y') ? 'Yes' : 'No';
						$sx2 = ($record->shift2 == 'Y') ? 'Yes' : 'No';
						$sx3 = ($record->shift3 == 'Y') ? 'Yes' : 'No';

				?>
						<tr>
							<td><?= $numb; ?></td>
							<td><?= strtoupper($record->nama_costcenter) ?></td>
							<td align='center'><span class="badge bg-<?= $s1; ?>"><?= $record->mp_1; ?></span></td>
							<td align='center'><span class="badge bg-<?= $s2; ?>"><?= $record->mp_2; ?></span></td>
							<td align='center'><span class="badge bg-<?= $s3; ?>"><?= $record->mp_3; ?></span></td>
							<td align='center'><span class="badge bg-<?= $s1; ?>"><?= $sx1; ?></span></td>
							<td align='center'><span class="badge bg-<?= $s2; ?>"><?= $sx2; ?></span></td>
							<td align='center'><span class="badge bg-<?= $s3; ?>"><?= $sx3; ?></span></td>
							<td>
								<!-- <?php if ($ENABLE_VIEW) : ?>
				<a class="btn btn-primary btn-sm view" href="javascript:void(0)" title="View" data-id_inventory1="<?= $record->id_type ?>"><i class="fa fa-eye"></i>
				</a>
			<?php endif; ?> -->

								<?php if ($ENABLE_MANAGE) : ?>
									<a class="btn btn-success btn-sm edit" href="javascript:void(0)" title="Edit" data-id_inventory1="<?= $record->id ?>"><i class="fa fa-edit"></i>
									</a>
								<?php endif; ?>

								<?php if ($ENABLE_DELETE and empty($record->kd_gudang)) : ?>
									<a class="btn btn-danger btn-sm delete" href="javascript:void(0)" title="Delete" data-id_inventory1="<?= $record->id ?>"><i class="fa fa-trash"></i>
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
		</div>
	</div>

	<!-- DataTables -->
	<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
	<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
	<script src="<?= base_url('assets/js/jquery.maskMoney.js') ?>"></script>
	<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>

	<!-- page script -->
	<script type="text/javascript">
		$(document).on('click', '.edit', function(e) {
			var id = $(this).data('id_inventory1');
			$("#head_title").html("<i class='fa fa-list-alt'></i><b>Edit Costcenter</b>");
			$.ajax({
				type: 'POST',
				url: siteurl + 'costcenter/edit/' + id,
				success: function(data) {
					$("#dialog-popup").modal();
					$("#ModalView").html(data);

				}
			})
		});


		// DELETE DATA
		$(document).on('click', '.delete', function(e) {
			e.preventDefault()
			var id = $(this).data('id_inventory1');
			// alert(id);
			swal({
					title: "Anda Yakin?",
					text: "Data Inventory akan di hapus.",
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
						url: siteurl + 'costcenter/hapus_data/' + id,
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
			// $('#example1 thead tr').clone(true).appendTo( '#example1 thead' );
			// $('#example1 thead tr:eq(1) th').each( function (i) {
			// var title = $(this).text();
			//alert(title);
			// if (title == "#" || title =="Action" ) {
			// $(this).html( '' );
			// }else{
			// $(this).html( '<input type="text" />' );
			// }

			// $( 'input', this ).on( 'keyup change', function () {
			// if ( table.column(i).search() !== this.value ) {
			// table
			// .column(i)
			// .search( this.value )
			// .draw();
			// }else{
			// table
			// .column(i)
			// .search( this.value )
			// .draw();
			// }
			// } );
			// } );

			var table = $('#example1').DataTable({
				orderCellsTop: true,
				fixedHeader: true
			});
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