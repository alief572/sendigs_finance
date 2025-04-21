<?php
$ENABLE_ADD     = has_permission('Pembayaran_Periodik.Add');
$ENABLE_MANAGE  = has_permission('Pembayaran_Periodik.Manage');
$ENABLE_VIEW    = has_permission('Pembayaran_Periodik.View');
$ENABLE_DELETE  = has_permission('Pembayaran_Periodik.Delete');
?>
<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<div class="box">
	<div class="box-header">
		<?php if ($ENABLE_ADD) : ?>
			<div class="col-md-3">
				<div class="dropdown">
					<button class="btn btn-success dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
						<i class="fa fa-plus">&nbsp;</i> New
					</button>
					<ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
						<?php
						echo '<li> <b> DEPARTEMEN</b></li>';
						foreach ($datdept as $key => $val) {
							echo '<li><a href="javascript:void(0)" title="Add" onclick="new_data(\'' . $key . '\')"><i class="fa fa-university">&nbsp; </i> ' . $val . '</a></li>';
						}
						?>
					</ul>
				</div>
			</div>
		<?php endif; ?>
		<div class="col-md-2">
			<?php if ($ENABLE_MANAGE) : ?>
				<a class="btn btn-info" href="javascript:void(0)" title="Proses" onclick="data_proses()">Proses</a>
			<?php endif; ?>
		</div>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<div class="table-responsive">
			<table id="mytabledata" class="table table-bordered table-striped">
				<thead>
					<tr>
						<th width="5">#</th>
						<th>Penanggung Jawab</th>
						<th width="100">
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
								<td>
									<?php if ($ENABLE_VIEW) : ?>
										<a class="btn btn-warning btn-sm view" href="javascript:void(0)" title="View" onclick="new_data('<?= $record->departement ?>')"><i class="fa fa-eye"></i></a>
									<?php endif;
									if ($ENABLE_MANAGE) : ?>
										<a class="btn btn-success btn-sm edit" href="javascript:void(0)" title="Edit" onclick="data_edit('<?= $record->departement ?>')"><i class="fa fa-edit"></i></a>
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
<!-- page script -->
<script type="text/javascript">
	var url_add = "";
	var url_add_def = siteurl + 'budget_periodik/create/';
	var url_edit = siteurl + 'budget_periodik/edit/';
	var url_delete = siteurl + 'budget_periodik/hapus_data/';
	var url_view = siteurl + 'budget_periodik/view/';

	function new_data(key) {
		url_add = url_add_def + key;
		data_add();
	}

	function data_proses() {
		swal({
				title: "Anda Yakin?",
				text: "Data Akan Proses!",
				type: "info",
				showCancelButton: true,
				confirmButtonText: "Ya!",
				cancelButtonText: "Tidak!",
				closeOnConfirm: false,
				closeOnCancel: true
			},
			function(isConfirm) {
				if (isConfirm) {
					$.ajax({
						dataType: "json",
						url: siteurl + 'budget_periodik/proses_budget_periodik',
						type: 'POST',
						success: function(msg) {
							if (msg['save'] == '1') {
								swal({
									title: "Sukses!",
									text: "Data Berhasil Di Proses",
									type: "success",
									timer: 1500,
									showConfirmButton: false
								});
								window.location.reload();
							} else {
								swal({
									title: "Gagal!",
									text: "Data Gagal Di Proses",
									type: "error",
									timer: 1500,
									showConfirmButton: false
								});
							};
							console.log(msg);
						},
						error: function(msg) {
							swal({
								title: "Gagal!",
								text: "Ajax Data Gagal Di Proses",
								type: "error",
								timer: 1500,
								showConfirmButton: false
							});
							console.log(msg);
						}
					});
				}
			});
	}
</script>
<script src="<?= base_url('assets/js/basic.js') ?>"></script>