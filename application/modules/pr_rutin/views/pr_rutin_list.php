<?php
$ENABLE_ADD     = has_permission('PR_Rutin.Add');
$ENABLE_MANAGE  = has_permission('PR_Rutin.Manage');
$ENABLE_VIEW    = has_permission('PR_Rutin.View');
$ENABLE_DELETE  = has_permission('PR_Rutin.Delete');
?>
<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<div class="box">
	<div class="box-header">
		<?php if ($ENABLE_ADD) : ?>
			<a class="btn btn-success btn-sm" href="javascript:void(0)" title="Tambah" onclick="data_add()"><i class="fa fa-plus">&nbsp;</i>Buat PR</a>
		<?php endif; ?>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<div class="table-responsive">
			<table id="mytabledata" class="table table-bordered table-striped">
				<thead>
					<tr>
						<th width="5">No</th>
						<th>No PR</th>
						<th>Tgl PR</th>
						<th>Last Requestor</th>
						<th>Status</th>
						<th width="100">
							<?php if ($ENABLE_MANAGE) : ?>
								Action
							<?php endif; ?>
						</th>
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
								<td><?= $record->so_number ?></td>
								<td><?= date('d F Y', strtotime($record->tgl_so)) ?></td>
								<td><?= $record->nm_lengkap ?></td>
								<td><?= $status[$record->status] ?></td>
								<td nowrap>
									<?php if ($ENABLE_VIEW) : ?>
										<a class="btn btn-warning btn-sm view" href="javascript:void(0)" title="View" onclick="data_view('<?= $record->id ?>')"><i class="fa fa-eye"></i></a>
										<a class="btn btn-info btn-sm print" title="Print" href="<?= base_url() . 'pr_rutin/printout/' . $record->id ?>" target="_blank"><i class="fa fa-print"></i></a>
										<?php endif;
									if ($ENABLE_MANAGE) :
										if ($record->status == 0 || $record->status == 10) { ?>
											<a class="btn btn-success btn-sm edit" href="javascript:void(0)" title="Edit" onclick="data_edit('<?= $record->id ?>')"><i class="fa fa-edit"></i></a>
										<?php }
									endif;
									if ($ENABLE_DELETE) :
										if ($record->status == 0) { ?>
											<a class="btn btn-danger btn-sm delete" href="javascript:void(0)" title="Delete" onclick="data_delete('<?= $record->id ?>')"><i class="fa fa-trash"></i></a>
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
	var url_add = siteurl + 'pr_rutin/create/';
	var url_edit = siteurl + 'pr_rutin/edit/';
	var url_delete = siteurl + 'pr_rutin/delete/';
	var url_view = siteurl + 'pr_rutin/view/';
</script>
<script src="<?= base_url('assets/js/basic.js') ?>"></script>