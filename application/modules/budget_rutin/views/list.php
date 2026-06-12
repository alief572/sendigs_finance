<?php
$ENABLE_ADD     = has_permission('Budget_Rutin.Add');
$ENABLE_MANAGE  = has_permission('Budget_Rutin.Manage');
$ENABLE_VIEW    = has_permission('Budget_Rutin.View');
$ENABLE_DELETE  = has_permission('Budget_Rutin.Delete');
?>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<div class="box">
	<div class="box-header">
		<span class="pull-right">
			<?php if ($ENABLE_VIEW) : ?>
				<a class="btn btn-sm btn-primary" href="<?= base_url() . 'budget_rutin/kompilasi' ?>" title="Kompilasi"><i class="fa fa-clone">&nbsp;</i>Kompilasi Budget</a>
			<?php endif; ?>
			<?php if ($ENABLE_ADD) : ?>
				<a class="btn btn-sm btn-success" href="javascript:void(0)" title="Add" onclick="add_data()"><i class="fa fa-plus">&nbsp;</i>Add Budget</a>
			<?php endif; ?>
		</span>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped">
			<thead>
				<tr>
					<th class='text-center'>#</th>
					<th class='text-left'>Tanggal Dibuat</th>
					<th>Warehouse</th>
					<th class='text-center'>Rev</th>
					<th class='text-center'>
						<?php if ($ENABLE_MANAGE) : ?>
							Action
						<?php endif; ?>
					</th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>
	<!-- /.box-body -->
</div>

<div id="form-data">
</div>
<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<!-- page script -->
<script type="text/javascript">
	$(function() {
		DataTables();
		$("#form-data").hide();
	});

	function DataTables() {
		$('#example1').DataTable({
			ajax: {
				url: siteurl + 'budget_rutin/get_data',
				type: "POST",
				dataType: "JSON"
			},
			columns: [
				{ data: 'no', className: 'text-center', orderable: false, searchable: false },
				{ data: 'tanggal', className: 'text-left' },
				{ data: 'warehouse' },
				{ data: 'rev', className: 'text-center' },
				{ data: 'action', className: 'text-center', orderable: false, searchable: false }
			],
			processing: true,
			serverSide: true,
			destroy: true,
			paging: true,
			stateSave: true
		});
	}

	function add_data() {
		var url = 'budget_rutin/create/';
		$(".box").hide();
		$("#form-data").show();
		$("#form-data").load(siteurl + url);
		$("#title").focus();
	}

	function edit_data(id) {
		if (id != "") {
			var url = 'budget_rutin/edit/' + id;
			$(".box").hide();
			$("#form-data").show();
			$("#form-data").load(siteurl + url);
			$("#title").focus();
		}
	}

	//Delete
	function delete_data(id) {
		swal({
				title: "Anda Yakin?",
				text: "Data Akan Terhapus secara Permanen!",
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: "#DD6B55",
				confirmButtonText: "Ya, delete!",
				cancelButtonText: "Tidak!",
				closeOnConfirm: false,
				closeOnCancel: true
			},
			function(isConfirm) {
				if (isConfirm) {
					$.ajax({
						url: siteurl + 'budget_rutin/hapus_data/' + id,
						dataType: "json",
						type: 'POST',
						success: function(msg) {
							if (msg['delete'] == '1') {
								swal({
									title: "Terhapus!",
									text: "Data berhasil dihapus",
									type: "success",
									timer: 1500,
									showConfirmButton: false
								});
								$('#example1').DataTable().ajax.reload(null, false);
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
				}
			});
	}
</script>
