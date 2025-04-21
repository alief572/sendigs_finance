<?php
$ENABLE_ADD     = has_permission('Approval_Pengajuan_Pembayaran_Rutin.Add');
$ENABLE_MANAGE  = has_permission('Approval_Pengajuan_Pembayaran_Rutin.Manage');
$ENABLE_VIEW    = has_permission('Approval_Pengajuan_Pembayaran_Rutin.View');
$ENABLE_DELETE  = has_permission('Approval_Pengajuan_Pembayaran_Rutin.Delete');
?>
<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<div class="box">
	<div class="box-header">
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<div class="table-responsive col-md-12">
			<table id="mytabledata" class="table table-bordered table-striped">
				<thead>
					<tr>
						<th width="5">#</th>
						<th>Departement</th>
						<th>Nomor</th>
						<th>Tanggal</th>
						<th width="150">
							Action
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
								<td><?= strtoupper($record->nm_dept) ?></td>
								<td><?= $record->no_doc ?></td>
								<td><?= $record->tanggal_doc ?></td>
								<td>
									<?php if ($ENABLE_VIEW) : ?>
										<a class="btn btn-warning btn-sm view" href="javascript:void(0)" title="View" onclick="data_view('<?= $record->id ?>')"><i class="fa fa-eye"></i></a>
										<?php endif;
									if ($record->status == 0) {
										if ($ENABLE_MANAGE) : ?>
											<a class="btn btn-success btn-sm app" href="javascript:void(0)" title="Approve" onclick="data_approve('<?= $record->id ?>')"><i class="fa fa-check"></i></a>
									<?php endif;
									} ?>
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
	var url_add = "";
	var url_view = siteurl + 'pengajuan_rutin/view/';

	function data_approve(id) {
		if (id != "") {
			$(".box").hide();
			$("#form-data").show();
			$("#form-data").load(url_view + id + '/app');
		}
	}
</script>
<script src="<?= base_url('assets/js/basic.js') ?>"></script>