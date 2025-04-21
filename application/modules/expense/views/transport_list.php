<?php
$ENABLE_ADD     = has_permission('Transportasi.Add');
$ENABLE_MANAGE  = has_permission('Transportasi.Manage');
$ENABLE_VIEW    = has_permission('Transportasi.View');
$ENABLE_DELETE  = has_permission('Transportasi.Delete');
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
		<div class="table-responsive">
			<table id="mytabledata" class="table table-bordered table-striped">
				<thead>
					<tr>
						<th width="5">#</th>
						<th>No</th>
						<th>Tanggal</th>
						<th>Nama</th>
						<th>Keperluan</th>
						<th>No. Polisi</th>
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
								<td><?= $record->nama ?></td>
								<td><?= $record->keperluan ?></td>
								<td><?= $record->nopol ?></td>
								<td><?= $status[$record->status] ?></td>
								<td>
									<?php if ($ENABLE_VIEW) : ?>
										<a class="btn btn-warning btn-sm view" href="javascript:void(0)" title="View" onclick="data_view('<?= $record->id ?>')"><i class="fa fa-eye"></i></a>
										<?php endif;
									if ($ENABLE_MANAGE) :
										if ($record->status == 0) { ?>
											<a class="btn btn-success btn-sm edit" href="javascript:void(0)" title="Edit" onclick="data_edit('<?= $record->id ?>')"><i class="fa fa-edit"></i></a>
										<?php }
									endif;
									if ($ENABLE_DELETE) :
										if ($record->status == 0) { ?>
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
	</div>
	<!-- /.box-body -->
</div>
<div id="form-data"></div>
<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<!-- page script -->
<script type="text/javascript">
	var url_add = siteurl + 'expense/transport_create/';
	var url_edit = siteurl + 'expense/transport_edit/';
	var url_delete = siteurl + 'expense/transport_delete/';
	var url_view = siteurl + 'expense/transport_view/';
</script>
<script src="<?= base_url('assets/js/basic.js') ?>"></script>