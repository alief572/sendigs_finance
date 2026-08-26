<?php
$ENABLE_ADD     = has_permission('Budget_Rutin.Add');
$ENABLE_MANAGE  = has_permission('Budget_Rutin.Manage');
$ENABLE_VIEW    = has_permission('Budget_Rutin.View');
$ENABLE_DELETE  = has_permission('Budget_Rutin.Delete');
?>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box box-primary">
	<div class="box-header with-border">
		<h3 class="box-title"><i class="fa fa-list"></i> Daftar Budget Stock per Warehouse</h3>
		<span class="pull-right">
			<?php if ($ENABLE_VIEW) : ?>
				<a class="btn btn-sm btn-primary" href="<?= base_url('budget_rutin/kompilasi') ?>" title="Kompilasi"><i class="fa fa-clone"></i> Kompilasi Budget</a>
			<?php endif; ?>
		</span>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped table-hover" width="100%">
			<thead>
				<tr class="bg-blue">
					<th class='text-center' width="5%">No</th>
					<th class='text-left' width="45%">Warehouse</th>
					<th class='text-center' width="30%">Last Update Date</th>
					<th class='text-center' width="20%">Action</th>
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
				{ data: 'warehouse', className: 'text-left' },
				{ data: 'last_update', className: 'text-center' },
				{ data: 'action', className: 'text-center', orderable: false, searchable: false }
			],
			processing: true,
			serverSide: true,
			destroy: true,
			paging: true,
			stateSave: true
		});
	}

	function edit_warehouse_budget(code_budget) {
		var url = 'budget_rutin/edit/' + encodeURIComponent(code_budget);
		$(".box").hide();
		$("#form-data").show();
		$("#form-data").load(siteurl + url);
	}
</script>


