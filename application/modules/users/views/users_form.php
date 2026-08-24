<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
	.form-section-header {
		font-size: 14px;
		font-weight: 700;
		color: #3c8dbc;
		border-bottom: 2px solid #e5e5e5;
		padding-bottom: 6px;
		margin-top: 10px;
		margin-bottom: 20px;
		text-transform: uppercase;
		letter-spacing: 0.5px;
	}
	.form-section-header i {
		margin-right: 6px;
	}
	.form-group label {
		font-weight: 600;
		color: #444;
	}
	.select2-container .select2-selection--single {
		height: 34px !important;
		border-color: #d2d6de !important;
		border-radius: 0px !important;
	}
	.select2-container--default .select2-selection--single .select2-selection__rendered {
		line-height: 32px !important;
	}
	.select2-container--default .select2-selection--single .select2-selection__arrow {
		height: 32px !important;
	}
	.req {
		color: #dd4b39;
		font-weight: bold;
	}
	.box-footer-actions {
		background: #f9f9f9;
		border-top: 1px solid #eee;
		padding: 15px 20px;
		margin: 20px -15px -10px -15px;
	}
</style>

<div class="box box-primary">
	<div class="box-header with-border">
		<h3 class="box-title">
			<i class="fa <?= isset($data->id_user) ? 'fa-user-edit' : 'fa-user-plus' ?> text-primary"></i> 
			<?= isset($data->id_user) ? 'Edit Master User: <strong class="text-primary">' . htmlspecialchars($data->username) . '</strong>' : 'Tambah Master User Baru' ?>
		</h3>
		<div class="box-tools pull-right">
			<a href="<?= site_url('users/setting') ?>" class="btn btn-sm btn-default btn-flat">
				<i class="fa fa-arrow-left"></i> Kembali ke Daftar
			</a>
		</div>
	</div>

	<!-- form start -->
	<?= form_open($this->uri->uri_string(), array('id' => 'frm_users', 'name' => 'frm_users', 'role' => 'form', 'class' => 'form-horizontal')) ?>
	<div class="box-body" style="padding: 20px 25px;">
		
		<!-- SECTION 1: AUTENTIKASI & AKUN -->
		<div class="form-section-header">
			<i class="fa fa-shield"></i> 1. Informasi Akun & Keamanan Login
		</div>

		<div class="row">
			<div class="col-md-6">
				<div class="form-group <?= form_error('username') ? 'has-error' : ''; ?>">
					<label for="username" class="col-sm-4 control-label">Username <span class="req">*</span></label>
					<div class="col-sm-8">
						<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-user"></i></span>
							<input type="text" class="form-control" id="username" name="username" maxlength="45" placeholder="Contoh: andri.hambali" value="<?= set_value('username', isset($data->username) ? $data->username : ''); ?>" required autofocus />
						</div>
						<?= form_error('username', '<span class="help-block">', '</span>') ?>
					</div>
				</div>

				<div class="form-group <?= form_error('password') ? 'has-error' : ''; ?>">
					<label for="password" class="col-sm-4 control-label"><?= lang('users_password') ?> <?= isset($data->id_user) ? '' : '<span class="req">*</span>' ?></label>
					<div class="col-sm-8">
						<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-lock"></i></span>
							<input type="password" class="form-control" id="password" name="password" maxlength="100" placeholder="<?= isset($data->id_user) ? 'Biarkan kosong jika tidak diubah' : 'Masukkan password' ?>" <?= isset($data->id_user) ? '' : 'required' ?> />
						</div>
						<?= form_error('password', '<span class="help-block">', '</span>') ?>
					</div>
				</div>

				<div class="form-group <?= form_error('re-password') ? 'has-error' : ''; ?>">
					<label for="re-password" class="col-sm-4 control-label"><?= lang('users_repassword') ?> <?= isset($data->id_user) ? '' : '<span class="req">*</span>' ?></label>
					<div class="col-sm-8">
						<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-lock"></i></span>
							<input type="password" class="form-control" id="re-password" name="re-password" maxlength="100" placeholder="<?= isset($data->id_user) ? 'Konfirmasi password baru' : 'Ulangi password' ?>" <?= isset($data->id_user) ? '' : 'required' ?> />
						</div>
						<?= form_error('re-password', '<span class="help-block">', '</span>') ?>
					</div>
				</div>
			</div>

			<div class="col-md-6">
				<div class="form-group <?= form_error('st_aktif') ? 'has-error' : ''; ?>">
					<label for="st_aktif" class="col-sm-4 control-label">Status User <span class="req">*</span></label>
					<div class="col-sm-8">
						<select name="st_aktif" id="st_aktif" class="form-control">
							<option value="1" <?= set_select('st_aktif', 1, isset($data->st_aktif) && $data->st_aktif == 1) ?>><?= lang('users_aktif') ?></option>
							<option value="0" <?= set_select('st_aktif', 0, isset($data->st_aktif) && $data->st_aktif == 0) ?>><?= lang('users_td_aktif') ?></option>
						</select>
					</div>
				</div>

				<input type="hidden" name="kdcab" id="kdcab" value="<?= isset($data->kdcab) ? $data->kdcab : '100'; ?>">
			</div>
		</div>

		<!-- SECTION 2: RELASI KARYAWAN & PROFIL -->
		<div class="form-section-header" style="margin-top: 25px;">
			<i class="fa fa-id-card"></i> 2. Profil & Link Karyawan HRIS
		</div>

		<div class="row">
			<div class="col-md-6">
				<div class="form-group">
					<label for="employee_id" class="col-sm-4 control-label">Employee (HRIS)</label>
					<div class="col-sm-8">
						<select name="employee_id" id="employee_id" class="form-control select2" style="width: 100%;">
							<option value="">-- Pilih Karyawan (Optional) --</option>
							<?php
							$curr_emp_id = (!empty($data->employee_id)) ? $data->employee_id : '';
							if (isset($employees) && !empty($employees)) {
								foreach ($employees as $emp) {
									$selected = ($emp['id'] == $curr_emp_id) ? 'selected' : '';
									$info = $emp['nm_karyawan'] . (!empty($emp['nm_dept']) ? ' - ' . $emp['nm_dept'] : '') . (!empty($emp['nm_pos']) ? ' (' . $emp['nm_pos'] . ')' : '');
									echo '<option value="' . $emp['id'] . '" ' . $selected . ' data-name="' . htmlspecialchars($emp['nm_karyawan']) . '">' . strtoupper($info) . '</option>';
								}
							}
							?>
						</select>
						<p class="help-block" style="font-size: 11px; margin-bottom: 0;"><i class="fa fa-info-circle text-info"></i> Menghubungkan user ini dengan data karyawan di database HRIS untuk fitur approval otomatis.</p>
					</div>
				</div>

				<div class="form-group <?= form_error('nm_lengkap') ? 'has-error' : ''; ?>">
					<label for="nm_lengkap" class="col-sm-4 control-label"><?= lang('users_nm_lengkap') ?> <span class="req">*</span></label>
					<div class="col-sm-8">
						<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-user-circle"></i></span>
							<input type="text" class="form-control" id="nm_lengkap" name="nm_lengkap" maxlength="100" placeholder="Nama lengkap user" value="<?= set_value('nm_lengkap', isset($data->nm_lengkap) ? $data->nm_lengkap : ''); ?>" required />
						</div>
						<?= form_error('nm_lengkap', '<span class="help-block">', '</span>') ?>
					</div>
				</div>
			</div>

			<div class="col-md-6">
				<div class="form-group <?= form_error('email') ? 'has-error' : ''; ?>">
					<label for="email" class="col-sm-4 control-label"><?= lang('users_email') ?></label>
					<div class="col-sm-8">
						<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-envelope"></i></span>
							<input type="email" class="form-control" id="email" name="email" maxlength="100" placeholder="user@company.com" value="<?= set_value('email', isset($data->email) ? $data->email : ''); ?>" />
						</div>
						<?= form_error('email', '<span class="help-block">', '</span>') ?>
					</div>
				</div>

				<div class="form-group <?= form_error('hp') ? 'has-error' : ''; ?>">
					<label for="hp" class="col-sm-4 control-label"><?= lang('users_hp') ?></label>
					<div class="col-sm-8">
						<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-phone"></i></span>
							<input type="text" class="form-control" id="hp" name="hp" maxlength="20" placeholder="08xxxxxxxxxx" value="<?= set_value('hp', isset($data->hp) ? $data->hp : ''); ?>" />
						</div>
						<?= form_error('hp', '<span class="help-block">', '</span>') ?>
					</div>
				</div>
			</div>
		</div>

		<!-- SECTION 3: ORGANISASI & JABATAN -->
		<div class="form-section-header" style="margin-top: 25px;">
			<i class="fa fa-sitemap"></i> 3. Departemen & Jabatan
		</div>

		<div class="row">
			<div class="col-md-6">
				<div class="form-group">
					<label for="department_id" class="col-sm-4 control-label">Departemen <span class="req">*</span></label>
					<div class="col-sm-8">
						<?php
						$deptid = (!empty($data->department_id)) ? $data->department_id : 0;
						$departmentx[0] = '-- Pilih Departemen --';
						foreach ($department as $key => $value) {
							$departmentx[$value['id']] = strtoupper($value['nm_dept'] . ' - ' . strtoupper($value['nm_comp']));
						}
						echo form_dropdown('department_id', $departmentx, $deptid, array('id' => 'department_id', 'class' => 'form-control select2', 'style' => 'width:100%;', 'required' => 'required'));
						?>
					</div>
				</div>
			</div>

			<div class="col-md-6">
				<div class="form-group">
					<label for="title" class="col-sm-4 control-label">Jabatan (Title)</label>
					<div class="col-sm-8">
						<select class="form-control select2 list_title" id="title" name="title" style="width: 100%;">
							<option value="">-- Pilih Jabatan / Title --</option>
							<?php
							if (isset($list_titles) && !empty($list_titles)) {
								echo $list_titles;
							}
							?>
						</select>
					</div>
				</div>
			</div>
		</div>

		<!-- SECTION 4: ALAMAT & DOMISILI -->
		<div class="form-section-header" style="margin-top: 25px;">
			<i class="fa fa-map-marker"></i> 4. Alamat & Domisili
		</div>

		<div class="row">
			<div class="col-md-6">
				<div class="form-group <?= form_error('alamat') ? 'has-error' : ''; ?>">
					<label for="alamat" class="col-sm-4 control-label"><?= lang('users_alamat') ?></label>
					<div class="col-sm-8">
						<textarea class="form-control" id="alamat" name="alamat" rows="3" placeholder="Alamat lengkap user" maxlength="255"><?= set_value('alamat', isset($data->alamat) ? $data->alamat : ''); ?></textarea>
					</div>
				</div>
			</div>

			<div class="col-md-6">
				<div class="form-group <?= form_error('kota') ? 'has-error' : ''; ?>">
					<label for="kota" class="col-sm-4 control-label"><?= lang('users_kota') ?></label>
					<div class="col-sm-8">
						<input type="text" class="form-control" id="kota" name="kota" maxlength="20" placeholder="Nama kota domisili" value="<?= set_value('kota', isset($data->kota) ? $data->kota : ''); ?>" />
					</div>
				</div>
			</div>
		</div>

		<!-- FOOTER BUTTONS -->
		<div class="box-footer-actions text-right">
			<a href="<?= site_url('users/setting') ?>" class="btn btn-default btn-flat" style="margin-right: 8px;">
				<i class="fa fa-times"></i> <?= lang('users_btn_cancel') ?>
			</a>
			<button type="submit" name="save" class="btn btn-primary btn-flat">
				<i class="fa fa-save"></i> <?= lang('users_btn_save') ?>
			</button>
		</div>

	</div>
	<?= form_close() ?>
</div><!-- /.box -->

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
	$(document).ready(function() {
		$('#department_id').select2({
			width: '100%'
		});
		$('#employee_id').select2({
			width: '100%',
			placeholder: '-- Pilih Karyawan (Optional) --',
			allowClear: true
		});
		$('#title').select2({
			width: '100%',
			placeholder: '-- Pilih Jabatan / Title --',
			allowClear: true
		});

		// Auto fill nama lengkap if empty when employee selected
		$('#employee_id').on('change', function() {
			var selectedName = $(this).find(':selected').data('name');
			if (selectedName && $('#nm_lengkap').val() == '') {
				$('#nm_lengkap').val(selectedName);
			}
		});
	});

	$(document).on('change', '#department_id', function() {
		var department_id = $(this).val();
		$.ajax({
			type: 'post',
			url: siteurl + active_controller + '/setting/' + 'get_titles',
			data: {
				'department_id': department_id
			},
			cache: false,
			dataType: 'json',
			success: function(result) {
				$('.list_title').html(result.hasil);
				$('#title').select2({
					width: '100%',
					placeholder: '-- Pilih Jabatan / Title --'
				});
			}
		});
	});
</script>