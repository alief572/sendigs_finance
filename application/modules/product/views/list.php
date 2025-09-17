<?php
$ENABLE_ADD = has_permission('Product.Add');
$ENABLE_MANAGE = has_permission('Product.Manage');
$ENABLE_VIEW = has_permission('Product.View');
$ENABLE_DELETE = has_permission('Product.Delete');
?>

<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css'); ?>">

<div class="box">
	<div class="box-header">
		<?php if ($ENABLE_ADD) : ?>
			<a class="btn btn-success" href="javascript:void(0)" title="Add" onclick="add_data()"><i class="fa fa-plus">&nbsp;</i>New</a>

			<!--<span class="pull-right">
				<?php //anchor(site_url('barang/downloadExcel'), ' <i class="fa fa-download"></i> Excel ', 'class="btn btn-primary btn-sm"'); 
				?>
				<a class="btn btn-primary btn-sm" data-toggle="modal" href="#dialog-rekap" title="Pdf" onclick="PreviewRekap()"><i class="fa fa-print">&nbsp;</i>PDF</a>
			</span> -->

		<?php endif; ?>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped">
			<thead>
				<tr>
					<th width="5">#</th>
					<th>Product Id</th>
					<th>Product Name</th>
					<th>Hardnes</th>
					<th>Sureface</th>
					<th>Thicknes</th>
					<th>Width</th>
					<th>Form</th>
					<th>Unit Coil</th>
					<th>Weight</th>
					<th>$</th>
					<th>Price</th>
					<?php if ($ENABLE_MANAGE) : ?>
						<th width="12%">Action</th>
					<?php endif; ?>
				</tr>
			</thead>

			<tbody id="data-product">
				<tr>
					<?php $no = 1;
					foreach ($result as $prod) { ?>
						<td><?= $no++ ?></td>
						<td><?= $prod->id_product ?></td>
						<td><?= $prod->product_name ?></td>
						<td><?= $prod->hardnes ?></td>
						<td><?= $prod->surface ?></td>
						<td class="text-right"><?= $prod->thicknes ?></td>
						<td class="text-right"><?= number_format($prod->width, 2, '.', ',') ?></td>
						<td><?= $prod->form ?></td>
						<td class="text-right"><?= number_format($prod->unit_coil, 2, '.', '') ?></td>
						<td class="text-right"><?= number_format($prod->weight, 2, '.', '') ?></td>
						<td><?= $prod->id_currency ?></td>
						<td class="text-right"><?= number_format($prod->price) ?></td>
						<td class="text-center">
							<?php if ($ENABLE_MANAGE) : ?>
								<button class="btn btn-primary btn-sm" id="view" data-id="<?= $prod->id_product ?>"><i class="fa fa-eye"></i></button>
								<button class="btn btn-success btn-sm" id="edit" data-id="<?= $prod->id_product ?>"><i class="fa fa-edit"></i></button>
								<button class="btn btn-danger btn-sm" id="delete" data-id="<?= $prod->id_product ?>"><i class="fa fa-trash-o"></i></button>
							<?php endif; ?>
						</td>
				</tr>
			<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<th width="5">#</th>
					<th>Product Id</th>
					<th>Product Name</th>
					<th>Hardnes</th>
					<th>Sureface</th>
					<th>Thicknes</th>
					<th>Width</th>
					<th>Form</th>
					<th>Unit Coil</th>
					<th>Weight</th>
					<th>$</th>
					<th>Price</th>
					<?php if ($ENABLE_MANAGE) : ?>
						<th width="50">Action</th>
					<?php endif; ?>
				</tr>
			</tfoot>
		</table>
	</div>
	<!-- /.box-body -->
</div>

<div class="modal fade" id="modal-detail" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel"><span class="fa fa-list-alt"></span>&nbsp;Data Produk</h4>
			</div>
			<div class="modal-body" id="MyModalBody">
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
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js'); ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js'); ?>"></script>

<!-- page script -->
<script type="text/javascript">
	// ADD PRODUCT 
	function add_data() {
		window.location.href = siteurl + "product/addProduct";
	}

	// EDIT PRODUCT
	$(document).on('click', '#edit', function() {
		var id = $(this).data('id');
		location.href = siteurl + 'product/editProduct/' + id;
		return false;
	})

	// VIEW PRODUCT
	$(document).on('click', '#view', function() {
		var id = $(this).data('id');
		$.ajax({
			type: 'POST',
			url: siteurl + 'product/viewProduct',
			data: {
				'id': id
			},
			success: function(data) {
				$('#modal-detail').modal();
				$('#MyModalBody').html(data);
			}
		})
	})

	// DELETE PRODUCT
	$(document).on('click', '#delete', function() {
		var id = $(this).data('id');
		swal({
				title: "Anda Yakin?",
				text: "Data Product ini akan dihapus!",
				type: "warning",
				showCancelButton: true,
				confirmButtonText: "Ya, delete!",
				cancelButtonText: "Tidak!",
				closeOnConfirm: false,
				closeOnCancel: true
			},
			function(isConfirm) {
				if (isConfirm) {
					$.ajax({
						url: siteurl + 'product/deleteProduct/' + id,
						dataType: "json",
						type: 'POST',
						success: function(result) {
							if (result.status == '1') {
								//swal("Terhapus!", "Data berhasil dihapus.", "success");
								swal({
										title: "Terhapus!",
										text: "Data berhasil dihapus",
										type: "success",
										timer: 1500,
										showConfirmButton: false
									},
									function() {
										window.location.reload(true);
									});
							} else {
								swal({
									title: "Gagal!",
									text: "Data gagal dihapus",
									type: "error",
									timer: 1500,
									showConfirmButton: false
								});
							};
						},
						error: function() {
							swal({
								title: "Gagal!",
								text: "Gagal Eksekusi Ajax",
								type: "error",
								timer: 1500,
								showConfirmButton: false
							});
						}
					});
				} else {
					//cancel();
				}
			});
	})



	function PreviewPdf(id) {
		param = id;
		tujuan = 'barang/print_request/' + param;

		$(".modal-body").html('<iframe src="' + tujuan + '" frameborder="no" width="570" height="400"></iframe>');
	}

	function PreviewRekap() {
		tujuan = 'barang/print_rekap';
		$(".modal-body").html('<iframe src="' + tujuan + '" frameborder="no" width="100%" height="400"></iframe>');
	}

	$(document).ready(function() {
		$('#example1').dataTable();
		// DataTables('data');
	})
</script>