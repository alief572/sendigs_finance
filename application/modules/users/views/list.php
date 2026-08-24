<?php
$ENABLE_ADD     = has_permission('Users.Add');
$ENABLE_MANAGE  = has_permission('Users.Manage');
$ENABLE_DELETE  = has_permission('Users.Delete');
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<style>
	.table-users thead th {
		background-color: #3c8dbc;
		color: #ffffff;
		vertical-align: middle !important;
		font-weight: 600;
	}
	.table-users tbody td {
		vertical-align: middle !important;
	}
	.badge-dept {
		background-color: #00c0ef;
		color: #fff;
		font-size: 11px;
		font-weight: 500;
		padding: 3px 7px;
		border-radius: 3px;
		display: inline-block;
	}
	.badge-pos {
		background-color: #f39c12;
		color: #fff;
		font-size: 11px;
		font-weight: 500;
		padding: 3px 7px;
		border-radius: 3px;
		display: inline-block;
		margin-top: 3px;
	}
	.badge-emp {
		color: #337ab7;
		font-weight: 600;
	}
	.btn-action-group .btn {
		margin: 1px;
	}
</style>

<div class="box box-primary">
	<div class="box-header with-border">
		<h3 class="box-title"><i class="fa fa-users text-primary"></i> Master User Management</h3>
		<div class="box-tools pull-right">
			<?php if ($ENABLE_ADD) : ?>
				<a href="<?= site_url('users/setting/create') ?>" class="btn btn-sm btn-success btn-flat" title="<?= lang('users_btn_new') ?>">
					<i class="fa fa-plus-circle"></i> <?= lang('users_btn_new') ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<div class="table-responsive">
			<table id="table_users" class="table table-bordered table-striped table-hover table-users" width="100%">
				<thead>
					<tr>
						<th width="35" class="text-center">#</th>
						<th>Username</th>
						<th>Nama Lengkap</th>
						<th>Employee (HRIS)</th>
						<th>Departemen & Jabatan</th>
						<th>Kontak</th>
						<th width="70" class="text-center">Status</th>
						<?php if ($ENABLE_MANAGE) : ?>
							<th width="100" class="text-center">Aksi</th>
						<?php endif; ?>
					</tr>
				</thead>

				<tbody>
					<?php 
					$no = 1;
					foreach ($results as $record) : 
						$emp_name = !empty($record->nm_karyawan) ? $record->nm_karyawan : (!empty($record->employee_id) ? $record->employee_id : '-');
						$dept_name = !empty($record->nm_dept) ? $record->nm_dept : '-';
						$pos_name = !empty($record->nm_pos) ? $record->nm_pos : (!empty($record->nm_title) ? $record->nm_title : '');
					?>
						<tr>
							<td class="text-center"><?= $no++; ?></td>
							<td>
								<strong><?= htmlspecialchars($record->username) ?></strong>
								<?php if ($record->id_user == 7 || $record->id_user == 1): ?>
									<span class="label label-danger" style="font-size: 10px;">Superadmin</span>
								<?php endif; ?>
							</td>
							<td><?= htmlspecialchars($record->nm_lengkap) ?></td>
							<td>
								<?php if ($emp_name !== '-'): ?>
									<span class="badge-emp"><i class="fa fa-id-badge"></i> <?= htmlspecialchars($emp_name) ?></span>
								<?php else: ?>
									<span class="text-muted"><i class="fa fa-unlink"></i> <em>Belum di-link</em></span>
								<?php endif; ?>
							</td>
							<td>
								<?php if ($dept_name !== '-'): ?>
									<span class="badge-dept"><i class="fa fa-building-o"></i> <?= htmlspecialchars($dept_name) ?></span>
								<?php endif; ?>
								<?php if (!empty($pos_name)): ?>
									<br><span class="badge-pos"><i class="fa fa-briefcase"></i> <?= htmlspecialchars($pos_name) ?></span>
								<?php endif; ?>
								<?php if ($dept_name === '-' && empty($pos_name)): ?>
									<span class="text-muted">-</span>
								<?php endif; ?>
							</td>
							<td>
								<?php if (!empty($record->email)): ?>
									<div><i class="fa fa-envelope-o text-muted"></i> <small><?= htmlspecialchars($record->email) ?></small></div>
								<?php endif; ?>
								<?php if (!empty($record->hp)): ?>
									<div><i class="fa fa-phone text-muted"></i> <small><?= htmlspecialchars($record->hp) ?></small></div>
								<?php endif; ?>
								<?php if (empty($record->email) && empty($record->hp)): ?>
									<span class="text-muted">-</span>
								<?php endif; ?>
							</td>
							<td class="text-center">
								<?= ($record->st_aktif == 0) 
									? "<span class='label label-danger'><i class='fa fa-times-circle'></i> " . lang('users_td_aktif') . "</span>" 
									: "<span class='label label-success'><i class='fa fa-check-circle'></i> " . lang('users_aktif') . "</span>" 
								?>
							</td>
							<?php if ($ENABLE_MANAGE) : ?>
								<td class="text-center btn-action-group">
									<a class="btn btn-xs btn-warning btn-flat" href="<?= site_url('users/setting/edit/' . $record->id_user); ?>" data-toggle="tooltip" data-placement="top" title="Edit User">
										<i class="fa fa-pencil"></i> Edit
									</a>
									<?php if ($record->id_user != 1) : ?>
										<a class="btn btn-xs btn-primary btn-flat" href="<?= site_url('users/setting/permission/' . $record->id_user); ?>" data-toggle="tooltip" data-placement="top" title="Hak Akses">
											<i class="fa fa-shield"></i> Akses
										</a>
									<?php endif; ?>
								</td>
							<?php endif; ?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
	<!-- /.box-body -->
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<!-- page script -->
<script>
	$(document).ready(function() {
		$("#table_users").DataTable({
			"responsive": true,
			"pageLength": 25,
			"order": [[1, "asc"]],
			"columnDefs": [
				{ "orderable": false, "targets": [0, -1] }
			]
		});
		$('[data-toggle="tooltip"]').tooltip();
	});
</script>