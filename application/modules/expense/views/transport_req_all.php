<?php
$ENABLE_ADD     = has_permission('List_Transportasi.Add');
$ENABLE_MANAGE  = has_permission('List_Transportasi.Manage');
$ENABLE_VIEW    = has_permission('List_Transportasi.View');
$ENABLE_DELETE  = has_permission('List_Transportasi.Delete');
?>
<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<div class="box">
	<div class="box-body">
		<div class="table-responsive">
			<table id="mytabledata" class="table table-bordered table-striped">
				<thead>
					<tr>
						<th width="5">#</th>
						<th>No</th>
						<th>Tanggal</th>
						<th>Nama</th>
						<th>Approval Date</th>
						<th>Status</th>
						<th width="120">Action</th>
					</tr>
				</thead>
				<tbody>
					<?php
					if (!empty($results)) {
						$numb = 0;
						foreach ($results as $record) {
							$numb++; ?>
							<tr>
								<td><?= $numb; ?></td>
								<td><?= $record->no_doc ?></td>
								<td><?= $record->tgl_doc ?></td>
								<td><?= $record->nmuser ?></td>
								<td><?= $record->approved_on ?></td>
								<td><?= $status[$record->status] ?></td>
								<td>
									<?php if ($ENABLE_VIEW) : ?>
										<a class="btn btn-default btn-sm print" href="<?= base_url('expense/transport_req_print/' . $record->id) ?>" target="transport_req_print" title="Print"><i class="fa fa-print"></i> </a>
										<a class="btn btn-warning btn-sm view" href="<?= base_url('expense/transport_req_view/' . $record->id . '/_all') ?>" title="View"><i class="fa fa-eye"></i></a>
									<?php endif; ?>
								</td>
							</tr>
					<?php
						}
					}  ?>
				</tbody>
			</table>
		</div>

		<h3>Detail Transport</h3>
		<div class="table-responsive">
			<table id="mytabledata2" class="table table-bordered table-striped">
				<thead>
					<tr>
						<th width="5">#</th>
						<th>No Dokumen</th>
						<th>Tanggal</th>
						<th>Pemohon</th>
						<th>Keperluan</th>
						<th>Tanggal Transaksi</th>
						<th>Status</th>
						<th>Tanggal ACC</th>
						<th width="120">Action</th>
					</tr>
				</thead>
				<tbody>
					<?php
					if (!empty($data_detail)) {
						$numb = 0;
						foreach ($data_detail as $record) {
							$numb++; ?>
							<tr>
								<td><?= $numb; ?></td>
								<td><?= $record->no_doc ?></td>
								<td><?= $record->tgl_doc ?></td>
								<td><?= $record->nmuser ?></td>
								<td><?= $record->keperluan ?></td>
								<td><?= $record->tgl_trans ?></td>
								<td><?= $status[$record->status] ?></td>
								<td><?= $record->approved_on ?></td>
								<td>
									<?php if ($ENABLE_VIEW) : ?>
										<a class="btn btn-warning btn-sm view" href="<?= base_url('expense/transport_req_view/' . $record->id . '/_all') ?>" title="View"><i class="fa fa-eye"></i></a>
									<?php endif; ?>
								</td>
							</tr>
					<?php
						}
					}  ?>
				</tbody>
			</table>
		</div>

	</div>
	<!-- /.box-body -->
</div>
<div id="form-data"></div>
<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<!-- page script -->
<script type="text/javascript">
	var url_add = siteurl + 'expense/transport_req_create/';
	var url_edit = siteurl + 'expense/transport_req_edit/';
	var url_delete = siteurl + 'expense/transport_req_delete/';
	var url_view = siteurl + 'expense/transport_req_view/';
	$("#mytabledata2").DataTable({
		dom: "<'row'<'col-sm-2'B><'col-sm-4'l><'col-sm-6'f>>rtip",
		buttons: [
			'excel'
		]
	});
</script>
<script src="<?= base_url('assets/js/basic.js') ?>"></script>