<?php
$ENABLE_ADD     = has_permission('Expense.Add');
$ENABLE_MANAGE  = has_permission('Expense.Manage');
$ENABLE_VIEW    = has_permission('Expense.View');
$ENABLE_DELETE  = has_permission('Expense.Delete');
?>
<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<div class="box">
	<div class="box-header">
		<?php if ($ENABLE_ADD) : ?>
			<div class="dropdown">
				<button class="btn btn-success btn-sm" type="button" onclick="data_add()">
					<i class="fa fa-plus">&nbsp;</i> Tambah
				</button>
			</div>
		<?php endif; ?>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<div class="table-responsive col-md-12">
			<table id="mytabledata" class="table table-bordered table-striped">
				<thead>
					<tr>
						<th width="5">#</th>
						<th>No Dokumen</th>
						<th>Tanggal</th>
						<th>Nama</th>
						<th>Approval</th>
						<th>Approval Date</th>
						<th>Keterangan</th>
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
								<td><?= $record->nmapproval ?></td>
								<td><?= $record->approved_on ?></td>
								<td><?= $record->informasi ?></td>
								<td><?= $status[$record->status] ?></td>
								<td>
									<?php if ($ENABLE_VIEW) : ?>
										<a class="btn btn-default btn-sm print" href="<?= base_url('expense/expense_print/' . $record->id) ?>" target="expense_print" title="Print"><i class="fa fa-print"></i> </a>
										<a class="btn btn-warning btn-sm view" href="javascript:void(0)" title="View" onclick="data_view('<?= $record->id ?>')"><i class="fa fa-eye"></i></a>
										<?php endif;
									if ($ENABLE_MANAGE) :
										if ($record->status == 0 || $record->status == 9) { ?>
											<a class="btn btn-success btn-sm edit" href="javascript:void(0)" title="Edit" onclick="data_edit('<?= $record->id ?>')"><i class="fa fa-edit"></i></a>
										<?php }
									endif;
									if ($ENABLE_DELETE) :
										if ($record->status == 0 || $record->status == 9) { ?>
											<a class="btn btn-danger btn-sm delete" href="javascript:void(0)" title="Hapus" onclick="data_delete('<?= $record->id ?>')"><i class="fa fa-trash"></i></a>
									<?php }
									endif; ?>
								</td>
							</tr>
					<?php
						}
					}  ?>
				</tbody>
			</table>
		</div>

		<h3>Detail Expense</h3>
		<div class="table-responsive col-md-12">
			<table id="mytabledata2" class="table table-bordered table-striped">
				<thead>
					<tr>
						<th width="5">#</th>
						<th>No Dokumen</th>
						<th>Tanggal</th>
						<th>Pemohon</th>
						<th>Barang/Jasa&Keterangan</th>
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
								<td><?= $record->deskripsi ?></td>
								<td><?= $record->tanggal ?></td>
								<td><?= $status[$record->status] ?></td>
								<td><?= $record->approved_on ?></td>
								<td>
									<?php if ($ENABLE_VIEW) : ?>
										<a class="btn btn-warning btn-sm view" href="javascript:void(0)" title="View" onclick="data_view('<?= $record->id ?>')"><i class="fa fa-eye"></i></a>
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

<div class="modal fade" id="modalKasbon" tabindex="-1" role="dialog" aria-labelledby="modalKasbonLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="modalKasbonLabel">Pilih Data Kasbon</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<table class="table table-bordered" id="tableKasbon">
					<thead>
						<tr>
							<th>#</th>
							<th>No Dokumen</th>
							<th>Tanggal</th>
							<th>Keperluan</th>
							<th>Keterangan</th>
							<th>Jumlah</th>
							<th>Aksi</th>
						</tr>
					</thead>
					<tbody>
						<!-- Data kasbon akan dimuat secara dinamis -->
					</tbody>
				</table>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
			</div>
		</div>
	</div>
</div>

<!-- DataTables -->

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="<?= base_url('assets/js/basic.js') ?>"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<!-- page script -->
<script type="text/javascript">
	var url_add = siteurl + 'expense/create/';
	var url_edit = siteurl + 'expense/edit/';
	var url_delete = siteurl + 'expense/delete/';
	var url_view = siteurl + 'expense/view/';
	$("#mytabledata2").DataTable({
		dom: "<'row'<'col-sm-2'B><'col-sm-4'l><'col-sm-6'f>>rtip",
		buttons: [
			'excel'
		]
	});
</script>