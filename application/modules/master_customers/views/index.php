<?php
$ENABLE_ADD     = has_permission('Master_customers.Add');
$ENABLE_MANAGE  = has_permission('Master_customers.Manage');
$ENABLE_VIEW    = has_permission('Master_customers.View');
$ENABLE_DELETE  = has_permission('Master_customers.Delete');

?>
<style type="text/css">
	thead input {
		width: 100%;
	}
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">


<div class="nav-tabs-supplier">
	<ul class="nav nav-tabs">
		<li class="active"><a href="#customer" data-toggle="tab" aria-expanded="true">Local Customer</a></li>
		<li class=""><a href="#Customer_category" data-toggle="tab" aria-expanded="false">Master Customer Category</a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="customer">
			<div class="box box-primary">
				<div class="box-header">
					<?php if ($ENABLE_VIEW) : ?>
						<a class="btn btn-success btn-sm add_customer" href="javascript:void(0)" title="Add"><i class="fa fa-plus">&nbsp;</i>Tambah Data</a>
					<?php endif; ?>
					<span class="pull-right"></span>
				</div>
				<div class="box-body">
					<table id="example1" class="table table-bordered table-striped">
						<thead>
							<tr>
								<th width="5">#</th>
								<th width="13%" hidden>Id Customer</th>
								<th>Nama Customer</th>
								<th>Sales</th>
								<th hidden>Kategori Customer</th>

								<th>Status</th>
								<?php if ($ENABLE_MANAGE) : ?>
									<th width="13%">Action</th>
								<?php endif; ?>
							</tr>
						</thead>

						<tbody>
							<?php if (empty($results['customer'])) {
							} else {

								$numb = 0;
								foreach ($results['customer'] as $customer) {
									$numb++;


							?>
									<tr>
										<td><?= $numb; ?></td>
										<td hidden><?= $customer->id_customer ?></td>
										<td><?= $customer->name_customer ?></td>
										<td><?= $customer->nama_karyawan ?></td>
										<td hidden><?php
													$id = $customer->id_customer;
													$cate  = $this->db->get_where('child_category_customer', array('id_customer' => $id))->result();
													foreach ($cate as $vp) {  ?>
												<?= $vp->name_category_customer ?>
											<?php }; ?>
										</td>


										<td>
											<?php if ($customer->activation == 'aktif') { ?>
												<label class="label label-success">Aktif</label>
											<?php } else { ?>
												<label class="label label-danger">Non Aktif</label>
											<?php } ?>
										</td>
										<td style="padding-left:20px">
											<?php if ($ENABLE_VIEW) : ?>
												<a class="btn btn-primary btn-sm view_local" href="javascript:void(0)" title="View" data-id_customer="<?= $customer->id_customer ?>"><i class="fa fa-eye"></i>
												</a>
											<?php endif; ?>

											<?php if ($ENABLE_MANAGE) : ?>
												<a class="btn btn-success btn-sm edit_local" href="javascript:void(0)" title="Edit" data-id_customer="<?= $customer->id_customer ?>"><i class="fa fa-edit"></i>
												</a>
											<?php endif; ?>

											<?php if ($ENABLE_DELETE) : ?>
												<a class="btn btn-danger btn-sm delete_local" href="javascript:void(0)" title="Delete" data-id_customer="<?= $customer->id_customer ?>"><i class="fa fa-trash"></i>
												</a>
											<?php endif; ?>
										</td>
									</tr>
							<?php }
							}  ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>

		<div class="tab-pane" id="Customer_category">
			<div class="box box-primary">
				<div class="box-header">
					<?php if ($ENABLE_VIEW) : ?>
						<a class="btn btn-success btn-sm add_category" href="javascript:void(0)" title="Add"><i class="fa fa-plus">&nbsp;</i>Tambah Data</a>
					<?php endif; ?>
					<span class="pull-right"></span>
				</div>
				<div class="box-body">
					<table id="example1" class="table table-bordered table-striped">
						<thead>
							<tr>
								<th width="5">#</th>
								<th width="13%" hidden>Id Category</th>
								<th hidden>Kode Kategori</th>
								<th>Nama Kategori</th>
								<th>Status</th>
								<?php if ($ENABLE_MANAGE) : ?>
									<th width="13%">Aksi</th>
								<?php endif; ?>
							</tr>
						</thead>

						<tbody>
							<?php if (empty($results['category'])) {
							} else {

								$numb3 = 0;
								foreach ($results['category'] as $category) {
									$numb3++; ?>
									<tr>
										<td><?= $numb3; ?></td>
										<td hidden><?= $category->id_category_customer ?></td>
										<td hidden><?= $category->customer_code ?></td>
										<td><?= $category->name_category_customer ?></td>

										<td>
											<?php if ($category->activation == 'active') { ?>
												<label class="label label-success">Aktif</label>
											<?php } else { ?>
												<label class="label label-danger">Non Aktif</label>
											<?php } ?>
										</td>
										<td style="padding-left:20px">
											<?php if ($ENABLE_VIEW) : ?>
												<a class="btn btn-primary btn-sm view_category" href="javascript:void(0)" title="View" data-id_category_customer="<?= $category->id_category_customer ?>"><i class="fa fa-eye"></i>
												</a>
											<?php endif; ?>

											<?php if ($ENABLE_MANAGE) : ?>
												<a class="btn btn-success btn-sm edit_category" href="javascript:void(0)" title="Edit" data-id_category_customer="<?= $category->id_category_customer ?>"><i class="fa fa-edit"></i>
												</a>
											<?php endif; ?>

											<?php if ($ENABLE_DELETE) : ?>
												<a class="btn btn-danger btn-sm delete_category" href="javascript:void(0)" title="Delete" data-id_category_customer="<?= $category->id_category_customer ?>"><i class="fa fa-trash"></i>
												</a>
											<?php endif; ?>
										</td>
									</tr>

							<?php }
							}  ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- awal untuk modal dialog -->
<!-- Modal -->
<div class="modal modal-primary" id="dialog-rekap" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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

<div class="modal modal-default fade" id="dialog-popup" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel"><span class="fa fa-users"></span>&nbsp;Data Customer</h4>
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
</div>
<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<!-- End Modal Bidus-->
<style>
	.box-primary {

		border: 1px solid #ddd;
	}
</style>
<!-- page script -->
<script type="text/javascript">
	$(document).on('click', '.edit_category', function(e) {
		var id = $(this).data('id_category_customer');
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Edit Data</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'master_customers/EditCategory/' + id,
			success: function(data) {
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			}
		})
	});

	$(document).on('click', '.view_category', function() {
		var id = $(this).data('id_category_supplier');
		// alert(id);
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Detail Data</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'master_suplier/viewCategory/' + id,
			data: {
				'id': id
			},
			success: function(data) {
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			}
		})
	});
	$(document).on('click', '.edit_international', function(e) {
		var id = $(this).data('id_suplier');
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Edit Data</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'master_suplier/EditInternasional/' + id,
			success: function(data) {
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			}
		})
	});

	$(document).on('click', '.view_international', function() {
		var id = $(this).data('id_suplier');
		// alert(id);
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Detail Data</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'master_suplier/viewInternasional/' + id,
			data: {
				'id': id
			},
			success: function(data) {
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			}
		})
	});
	$(document).on('click', '.edit_local', function(e) {
		var id = $(this).data('id_customer');
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Edit Data</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'master_customers/editCustomer/' + id,
			success: function(data) {
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			}
		})
	});

	$(document).on('click', '.view_local', function() {
		var id = $(this).data('id_customer');
		// alert(id);
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Detail Data</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'master_customers/viewCustomer/' + id,
			data: {
				'id': id
			},
			success: function(data) {
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			}
		})
	});
	$(document).on('click', '.add_customer', function() {
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Tambah Data</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'master_customers/addCustomer',
			success: function(data) {
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			}
		})
	});
	$(document).on('click', '.add_category', function() {
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Tambah Data</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'master_customers/addCategory',
			success: function(data) {
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			}
		})
	});


	// DELETE DATA
	$(document).on('click', '.delete_category', function(e) {
		e.preventDefault()
		var id = $(this).data('id_category_customer');
		// alert(id);
		swal({
				title: "Anda Yakin?",
				text: "Data akan di hapus.",
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
					url: siteurl + 'master_customers/deleteCategory',
					dataType: "json",
					data: {
						'id': id
					},
					success: function(result) {
						if (result.status == '1') {
							swal({
									title: "Sukses",
									text: "Data berhasil dihapus.",
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

	});
	// DELETE DATA
	$(document).on('click', '.delete_local', function(e) {
		e.preventDefault()
		var id = $(this).data('id_customer');
		// alert(id);
		swal({
				title: "Anda Yakin?",
				text: "Data akan di hapus.",
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
					url: siteurl + 'master_customers/deletelokal',
					dataType: "json",
					data: {
						'id': id
					},
					success: function(result) {
						if (result.status == '1') {
							swal({
									title: "Sukses",
									text: "Data berhasil dihapus.",
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

	});
	// DELETE DATA
	$(document).on('click', '.delete_international', function(e) {
		e.preventDefault()
		var id = $(this).data('id_suplier');
		// alert(id);
		swal({
				title: "Anda Yakin?",
				text: "Data akan di hapus.",
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
					url: siteurl + 'master_suplier/deleteinternational',
					dataType: "json",
					data: {
						'id': id
					},
					success: function(result) {
						if (result.status == '1') {
							swal({
									title: "Sukses",
									text: "Data berhasil dihapus.",
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

	});

	$(function() {

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