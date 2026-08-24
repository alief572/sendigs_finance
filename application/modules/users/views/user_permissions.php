<style>
	.table-perm thead th {
		background-color: #3c8dbc;
		color: #ffffff;
		vertical-align: middle !important;
		font-weight: 600;
	}
	.table-perm tbody tr:hover {
		background-color: #f4f8fa !important;
	}
	.user-card-summary {
		background: #f8fafc;
		border: 1px solid #e2e8f0;
		border-radius: 4px;
		padding: 12px 18px;
		margin-bottom: 15px;
	}
	.user-card-summary .label-title {
		font-size: 11px;
		color: #718096;
		text-transform: uppercase;
		font-weight: 600;
	}
	.user-card-summary .user-val {
		font-size: 15px;
		color: #2d3748;
		font-weight: 700;
	}
	.box-footer-actions {
		background: #f9f9f9;
		border-top: 1px solid #eee;
		padding: 15px 20px;
	}
</style>

<div class="box box-primary">
	<div class="box-header with-border">
		<h3 class="box-title">
			<i class="fa fa-shield text-primary"></i> Setting Hak Akses (Permissions): <strong class="text-primary"><?= htmlspecialchars($data->username) ?></strong>
		</h3>
		<div class="box-tools pull-right">
			<a href="<?= site_url('users/setting') ?>" class="btn btn-sm btn-default btn-flat">
				<i class="fa fa-arrow-left"></i> Kembali ke Daftar
			</a>
		</div>
	</div>

	<!-- form start -->
	<?= form_open($this->uri->uri_string(), array('id' => 'frm_users', 'name' => 'frm_users', 'role' => 'form', 'class' => 'form-horizontal')) ?>
	
	<div class="box-body" style="padding: 15px 20px;">
		<!-- USER PROFILE CARD -->
		<div class="user-card-summary">
			<div class="row">
				<div class="col-sm-4">
					<div class="label-title"><i class="fa fa-user"></i> Username</div>
					<div class="user-val"><?= htmlspecialchars($data->username) ?></div>
				</div>
				<div class="col-sm-4">
					<div class="label-title"><i class="fa fa-user-circle"></i> Nama Lengkap</div>
					<div class="user-val"><?= htmlspecialchars($data->nm_lengkap) ?></div>
				</div>
				<div class="col-sm-4">
					<div class="label-title"><i class="fa fa-envelope"></i> Email</div>
					<div class="user-val" style="font-size: 13px; font-weight: normal;"><?= !empty($data->email) ? htmlspecialchars($data->email) : '-' ?></div>
				</div>
			</div>
		</div>

		<div class="table-responsive">
			<table id="example1" class="table table-bordered table-striped table-hover table-perm" width="100%">
				<thead>
					<tr>
						<th>Menu Navigasi</th>
						<th width='12%' class='text-center'>Check All<br><input type="checkbox" name="chk_all" id="chk_all"></th>
						<th width='12%' class='text-center'>View<br><input type="checkbox" name="chk_view" id="chk_view"></th>
						<th width='12%' class='text-center'>Add<br><input type="checkbox" name="chk_add" id="chk_add"></th>
						<th width='12%' class='text-center'>Manage<br><input type="checkbox" name="chk_manage" id="chk_manage"></th>
						<th width='12%' class='text-center'>Delete<br><input type="checkbox" name="chk_delete" id="chk_delete"></th>
					</tr>
				</thead>
				<tbody id="listDetail">
					<?php
					$no = 0;
					foreach ($permissions as $key => $pr) :
						$no++;
					?>
						<tr style="background: #eef5f9;">
							<td class='text-primary' style="font-size: 13px;">
								<i class="fa fa-folder-open text-primary"></i> <b><?= htmlspecialchars($pr['nama_menu']) ?></b>
							</td>
							<td class='text-center'><input type="checkbox" class="minimal all_baris" data-id="<?= $pr['id']; ?>"></td>
							<?php
							if (!empty($ArrActionPers[$pr['id']])) {
								foreach ($ArrActionPers[$pr['id']] as $key6 => $value6) {
									$id_permission		= $value6['id_permission'];
									$x 					= explode(".", $value6['nm_permission']);
									$id_roll_permission = (isset($auth_permissions[$id_permission]->is_role_permission) && $auth_permissions[$id_permission]->is_role_permission == 1) ? 1 : '';
									$action_value 		= isset($auth_permissions[$id_permission]) ? 1 : 0;
							?>
									<td class='text-center'>
										<input type="checkbox" name="id_permissions[]" class='det<?= $x[1]; ?> menuID<?= $pr['id']; ?>' value="<?= $id_permission ?>" title="<?= $x[0] ?>" <?= ($action_value == 1) ? "checked='checked'" : '' ?> <?= ($id_roll_permission == 1) ? "disabled='disabled'" : '' ?> />
									</td>
							<?php
								}
							}
							?>
						</tr>
						<?php
						if (!empty($ArrPermissionDetail[$pr['id']])) {
							foreach ($ArrPermissionDetail[$pr['id']] as $key => $value) {
								echo "<tr>";
								echo "<td style='padding-left: 25px;'><i class='fa fa-angle-right text-muted'></i> " . htmlspecialchars($value['nama_menu']) . "</td>";
								echo "<td class='text-center'><input type='checkbox' class='minimal all_baris' data-id='" . $value['id'] . "'></td>";
								if (!empty($ArrActionPers[$value['id']])) {
									foreach ($ArrActionPers[$value['id']] as $key3 => $value3) {
										$x 					= explode(".", $value3['nm_permission']);
										$id_roll_permission = (isset($auth_permissions[$value3['id_permission']]->is_role_permission) && $auth_permissions[$value3['id_permission']]->is_role_permission == 1) ? 1 : '';
										$action_value 		= isset($auth_permissions[$value3['id_permission']]) ? 1 : 0;
						?>
										<td class='text-center'>
											<input type="checkbox" name="id_permissions[]" class='det<?= $x[1]; ?> menuID<?= $value['id']; ?>' value="<?= $value3['id_permission'] ?>" title="<?= $x[0] ?>" <?= ($action_value == 1) ? "checked='checked'" : '' ?> <?= ($id_roll_permission == 1) ? "disabled='disabled'" : '' ?> />
										</td>
										<?php
									}
								}
								echo "</tr>";
								if (!empty($ArrPermissionDetail[$value['id']])) {
									foreach ($ArrPermissionDetail[$value['id']] as $key2 => $value2) {
										echo "<tr>";
										echo "<td style='padding-left: 45px;'><i class='fa fa-angle-double-right text-muted'></i> " . htmlspecialchars($value2['nama_menu']) . "</td>";
										echo "<td class='text-center'><input type='checkbox' class='minimal all_baris' data-id='" . $value2['id'] . "'></td>";
										if (!empty($ArrActionPers[$value2['id']])) {
											foreach ($ArrActionPers[$value2['id']] as $key4 => $value4) {
												$x 					= explode(".", $value4['nm_permission']);
												$id_roll_permission = (isset($auth_permissions[$value4['id_permission']]->is_role_permission) && $auth_permissions[$value4['id_permission']]->is_role_permission == 1) ? 1 : '';
												$action_value 		= isset($auth_permissions[$value4['id_permission']]) ? 1 : 0;
										?>
												<td class='text-center'>
													<input type="checkbox" name="id_permissions[]" class='det<?= $x[1]; ?> menuID<?= $value2['id']; ?>' value="<?= $value4['id_permission'] ?>" title="<?= $x[0] ?>" <?= ($action_value == 1) ? "checked='checked'" : '' ?> <?= ($id_roll_permission == 1) ? "disabled='disabled'" : '' ?> />
												</td>
						<?php
											}
										}
										echo "</tr>";
									}
								}
							}
						}
						?>
					<?php endforeach ?>
				</tbody>
			</table>
		</div>
	</div>

	<!-- FOOTER -->
	<div class="box-footer-actions text-right">
		<a href="<?= site_url('users/setting') ?>" class="btn btn-default btn-flat" style="margin-right: 8px;">
			<i class="fa fa-times"></i> <?= lang('users_btn_cancel') ?>
		</a>
		<button type="submit" name="save" class="btn btn-primary btn-flat">
			<i class="fa fa-save"></i> <?= lang('users_btn_save') ?>
		</button>
	</div>

	<?= form_close() ?>
</div><!-- /.box -->

<script>
	$(document).ready(function() {
		$("#chk_view").click(function() {
			$('.detView').not(this).prop('checked', this.checked);
		});
		$("#chk_add").click(function() {
			$('.detAdd').not(this).prop('checked', this.checked);
		});
		$("#chk_delete").click(function() {
			$('.detDelete').not(this).prop('checked', this.checked);
		});
		$("#chk_manage").click(function() {
			$('.detManage').not(this).prop('checked', this.checked);
		});

		$(".all_baris").click(function() {
			var id = $(this).data('id');
			$('.menuID' + id).not(this).prop('checked', this.checked);
		});

		$("#chk_all").click(function() {
			$('input:checkbox').not(this).prop('checked', this.checked);
		});
	});
</script>