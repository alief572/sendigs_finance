<?php
$ENABLE_ADD     = has_permission('PR_Departemen.Add');
$ENABLE_MANAGE  = has_permission('PR_Departemen.Manage');
$ENABLE_VIEW    = has_permission('PR_Departemen.View');
$ENABLE_DELETE  = has_permission('PR_Departemen.Delete');
?>
<style>
	.section-title {
		font-size: 13px;
		font-weight: 700;
		color: #3c8dbc;
		text-transform: uppercase;
		letter-spacing: 0.5px;
		border-bottom: 2px solid #3c8dbc;
		padding-bottom: 6px;
		margin-bottom: 15px;
	}

	#my-grid thead tr th {
		background-color: #3c8dbc;
		color: #fff;
		font-size: 12px;
		border-color: #357ca5;
	}

	.breadcrumb {
		background: none;
		padding: 0;
		font-size: 12px;
		margin-bottom: 5px;
	}
</style>

<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data" autocomplete="off">
	<div class="box box-primary">
		<div class="box-header with-border">
			<div>
				<ol class="breadcrumb">
					<li><i class="fa fa-shopping-cart"></i> Procurement</li>
					<li class="active">Approval Management PR Dept</li>
				</ol>
				<h3 class="box-title" style="font-size:16px; font-weight:700;"><?php echo $title; ?></h3>
			</div>
		</div>
		<!-- /.box-header -->
		<div class="box-body table-responsive">
			<input type="hidden" id="tanda" value="<?= $tanda; ?>">
			<table class="table table-bordered table-striped" id="my-grid" width="100%">
				<thead>
					<tr>
						<th class="text-center">#</th>
						<th class="text-center">No PR</th>
						<th class="text-center">Departemen</th>
						<th class="text-center no-sort">Keterangan</th>
						<th class="text-center no-sort">PIC</th>
						<th class="text-center no-sort">Created Date</th>
						<th class="text-center no-sort">Nama Pembuat PR</th>
						<th class="text-center no-sort">Tgl Dibuat PR</th>
						<th class="text-center no-sort">Tingkat PR</th>
						<th class="text-center no-sort">Status</th>
						<th class="text-center no-sort" width="13%">Option</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
		<!-- /.box-body -->
	</div>
	<!-- /.box -->

	<!-- Modal View PR -->
	<div class="modal fade" id="ModalView2" style="overflow-y: auto;">
		<div class="modal-dialog" style="width:80%;">
			<div class="modal-content">
				<div class="modal-header" style="background:#3c8dbc; color:#fff; border-radius:3px 3px 0 0;">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff; opacity:1;">
						<span aria-hidden="true">&times;</span>
					</button>
					<h4 class="modal-title" id="head_title2"></h4>
				</div>
				<div class="modal-body" id="view2"></div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
	<!-- /modal -->
</form>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script>
	$(document).ready(function() {
		$('.maskM').autoNumeric();

		var tanda = $('#tanda').val();
		DataTables(tanda);
	});

	function DataTables(tanda = null) {
		var dataTable = $('#my-grid').DataTable({
			"processing": true,
			"serverSide": true,
			"stateSave": true,
			"bAutoWidth": true,
			"destroy": true,
			"responsive": true,
			"aaSorting": [
				[1, "asc"]
			],
			"columnDefs": [{
				"targets": 'no-sort',
				"orderable": false,
			}],
			"sPaginationType": "simple_numbers",
			"iDisplayLength": 10,
			"aLengthMenu": [
				[10, 20, 50, 100, 150],
				[10, 20, 50, 100, 150]
			],
			"ajax": {
				url: siteurl + active_controller + 'server_side_non_rutin_approval_management',
				type: "post",
				data: function(d) {
					d.tanda = tanda
				},
				cache: false,
				error: function(xhr, error, code) {
					console.error("DataTables Ajax Error: ", xhr.responseText);
					$(".my-grid-error").html("");
					$("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="11" class="text-center text-danger">No data found in the server</th></tr></tbody>');
					$("#my-grid_processing").css("display", "none");
				}
			}
		});
	}
</script>