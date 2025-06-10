<div class="box box-primary">
	<!-- form start -->
	<?= form_open($this->uri->uri_string(), array('id' => 'frm_users', 'name' => 'frm_users', 'role' => 'form', 'class' => 'form-horizontal')) ?>
	<div class="box-body">
		<div class="form-group <?= form_error('username') ? ' has-error' : ''; ?>">
			<label for="username" class="col-md-2 control-label"><?= lang('users_username') ?></label>
			<div class="col-sm-3">
				<input type="text" class="form-control" id="username" name="username" maxlength="45" value="<?= set_value('username', isset($data->username) ? $data->username : ''); ?>" autofocus />
			</div>
		</div>
		<div class="form-group <?= form_error('password') ? ' has-error' : ''; ?>">
			<label for="password" class="col-md-2 control-label"><?= lang('users_password') ?></label>
			<div class="col-md-3">
				<input type="password" class="form-control" id="password" name="password" maxlength="100" value="<?= set_value('password') ?>" />
			</div>
		</div>
		<div class="form-group <?= form_error('re-password') ? ' has-error' : ''; ?>">
			<label for="re-password" class="col-md-2 control-label"><?= lang('users_repassword') ?></label>
			<div class="col-md-3">
				<input type="password" class="form-control" id="re-password" name="re-password" maxlength="100" value="<?= set_value('re-password') ?>" />
			</div>
		</div>
		<div class="form-group <?= form_error('email') ? ' has-error' : ''; ?>">
			<label for="email" class="col-md-2 control-label"><?= lang('users_email') ?></label>
			<div class="col-md-3">
				<input type="email" class="form-control" id="email" name="email" maxlength="100" value="<?= set_value('email', isset($data->email) ? $data->email : ''); ?>" />
			</div>
		</div>
		<div class="form-group <?= form_error('nm_lengkap') ? ' has-error' : ''; ?>">
			<label for="nm_lengkap" class="col-md-2 control-label"><?= lang('users_nm_lengkap') ?></label>
			<div class="col-md-3">
				<input type="text" class="form-control" id="nm_lengkap" name="nm_lengkap" maxlength="100" value="<?= set_value('nm_lengkap', isset($data->nm_lengkap) ? $data->nm_lengkap : ''); ?>" />
			</div>
		</div>
		<div class="form-group <?= form_error('alamat') ? ' has-error' : ''; ?>">
			<label for="alamat" class="col-md-2 control-label"><?= lang('users_alamat') ?></label>
			<div class="col-md-4">
				<textarea class="form-control" id="alamat" name="alamat" maxlength="255"><?= set_value('alamat', isset($data->alamat) ? $data->alamat : ''); ?></textarea>
			</div>
		</div>
		<div class="form-group <?= form_error('kota') ? ' has-error' : ''; ?>">
			<label for="kota" class="col-md-2 control-label"><?= lang('users_kota') ?></label>
			<div class="col-md-2">
				<input type="text" class="form-control" id="kota" name="kota" maxlength="20" value="<?= set_value('kota', isset($data->kota) ? $data->kota : ''); ?>" />
			</div>
		</div>
		<div class="form-group <?= form_error('hp') ? ' has-error' : ''; ?>">
			<label for="hp" class="col-md-2 control-label"><?= lang('users_hp') ?></label>
			<div class="col-md-2">
				<input type="text" class="form-control" id="hp" name="hp" maxlength="20" value="<?= set_value('hp', isset($data->hp) ? $data->hp : ''); ?>" />
			</div>
		</div>
		<div class="form-group <?= form_error('st_aktif') ? ' has-error' : ''; ?>">
			<label for="st_aktif" class="col-md-2 control-label"><?= lang('users_st_aktif') ?></label>
			<div class="col-md-3">
				<select name="st_aktif" id="st_aktif" class="form-control">
					<option value="1" <?= set_select('st_aktif', 1, isset($data->st_aktif) && $data->st_aktif == 1) ?>><?= lang('users_aktif') ?></option>
					<option value="0" <?= set_select('st_aktif', 0, isset($data->st_aktif) && $data->st_aktif == 0) ?>><?= lang('users_td_aktif') ?></option>
				</select>
			</div>
		</div>
		<div class="form-group <?= form_error('kdcab') ? ' has-error' : ''; ?>" hidden>
			<label for="kdcab" class="col-md-2 control-label">Cabang</label>
			<div class="col-md-2">
				<select name="kdcab" id="kdcab" class="form-control">
					<option value="">Pilih Cabang</option>
					<?php
					foreach ($cabang as $k => $v) {
						$selected = '';
						if ($v->kdcab == $data->kdcab) {
							$selected = 'selected="selected"';
						}
					?>
						<option value="<?php echo $v->kdcab ?>" <?php echo $selected ?>><?php echo $v->namacabang ?></option>
					<?php } ?>
				</select>
			</div>
		</div>
		<div class="form-group">
			<label class="col-md-2 control-label"><b>Department</b></label>
			<div class='col-sm-3'>
				<?php
				$deptid = (!empty($data->department_id)) ? $data->department_id : 0;
				$departmentx[0] = 'Select An Department';
				foreach ($department as $key => $value) {
					$departmentx[$value['id']] = strtoupper($value['nama']);
				}
				echo form_dropdown('department_id', $departmentx, $deptid, array('id' => 'department_id', 'class' => 'form-control input-md', 'required' => 'required'));
				?>
			</div>
		</div>
		<div class="box-footer">
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10">
					<button type="submit" name="save" class="btn btn-primary"><?= lang('users_btn_save') ?></button>
					<?php
					echo anchor('users/setting', lang('users_btn_cancel'), array('class' => 'btn btn-danger'));
					?>
				</div>
			</div>
		</div>
	</div>
	<?= form_close() ?>
</div><!-- /.box -->