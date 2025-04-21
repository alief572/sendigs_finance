<div class="box box-primary">
	<!-- form start -->
	<?= form_open($this->uri->uri_string(), array('id' => 'frm_users', 'name' => 'frm_users', 'role' => 'form', 'class' => 'form-horizontal')) ?>
	<div class="box-header">
		<input type="submit" name="save" value="<?= lang('users_btn_save') ?>" class="btn btn-primary" />
		<?php
		echo anchor('users/setting', lang('users_btn_cancel'), array('class' => 'btn btn-danger'));
		?>
		<div class="form-group <?= form_error('username') ? ' has-error' : ''; ?>">
			<label for="username" class="col-md-2 control-label"><?= lang('users_username') ?></label>
			<div class="col-sm-3">
				<input type="text" class="form-control" id="username" name="username" maxlength="45" value="<?= set_value('username', isset($data->username) ? $data->username : ''); ?>" readonly />
			</div>
		</div>
		<div class="form-group <?= form_error('nm_lengkap') ? ' has-error' : ''; ?>">
			<label for="nm_lengkap" class="col-md-2 control-label"><?= lang('users_nm_lengkap') ?></label>
			<div class="col-md-3">
				<input type="text" class="form-control" id="nm_lengkap" name="nm_lengkap" maxlength="100" value="<?= set_value('nm_lengkap', isset($data->nm_lengkap) ? $data->nm_lengkap : ''); ?>" readonly />
			</div>
		</div>
	</div>
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped">
			<thead>
				<tr>
					<th>Menu</th>
					<th width='15%' class='text-center'>Check All<br><input type="checkbox" name="chk_all" id="chk_all"></th>
					<th width='15%' class='text-center'>View<br><input type="checkbox" name="chk_view" id="chk_view"></th>
					<th width='15%' class='text-center'>Add<br><input type="checkbox" name="chk_add" id="chk_add"></th>
					<th width='15%' class='text-center'>Manage<br><input type="checkbox" name="chk_manage" id="chk_manage"></th>
					<th width='15%' class='text-center'>Delete<br><input type="checkbox" name="chk_delete" id="chk_delete"></th>
				</tr>
			</thead>
			<tbody id="listDetail">
				<?php
				$no = 0;
				foreach ($permissions as $key => $pr) :
					$no++;
				?>
					<tr>
						<td class='text-primary'><b><?php echo $pr['nama_menu'] ?></b></td>
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
							echo "<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $value['nama_menu'] . "</td>";
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
									echo "<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $value2['nama_menu'] . "</td>";
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
				<?php

				endforeach
				?>
			</tbody>

		</table>
	</div>
	<div class="box-footer">
		<input type="submit" name="save" value="<?= lang('users_btn_save') ?>" class="btn btn-primary" />
		<?php
		echo anchor('users/setting', lang('users_btn_cancel'), array('class' => 'btn btn-danger'));
		?>
	</div>
</div>

<?= form_close() ?>
</div><!-- /.box -->

<script>
	$(document).ready(function() {
		$("td").click(function(e) {
			var chk = $(this).find("input:checkbox").get(0);
			if (e.target != chk) {
				chk.checked = !chk.checked;
			}
		});

		// $('#chk_all').click(function(){
		// 	if($('#chk_all').is(':checked')){
		// 		$('#listDetail').find('input[type="checkbox"]').each(function(){
		// 			$(this).prop('checked',true);
		// 		});
		// 	}else{
		// 		$('#listDetail').find('input[type="checkbox"]').each(function(){
		// 			$(this).prop('checked',false);
		// 		});
		// 	}
		// });

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