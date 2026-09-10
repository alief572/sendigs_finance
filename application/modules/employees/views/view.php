<?php
// Mapping data pendukung
$emp = !empty($row[0]) ? $row[0] : null;

if (!$emp) {
	echo "<div class='alert alert-danger'>Data karyawan tidak ditemukan.</div>";
	return;
}

// Perusahaan, Divisi, Departemen, Title, Posisi
$company_name     = isset($data_companies[$emp->company_id]) ? $data_companies[$emp->company_id] : '-';
$division_name    = isset($data_divisions[$emp->division_id]) ? $data_divisions[$emp->division_id] : '-';
$division_head    = isset($data_divisions_head[$emp->division_head]) ? $data_divisions_head[$emp->division_head] : '-';
$department_name  = isset($data_department[$emp->department_id]) ? $data_department[$emp->department_id] : '-';
$title_name       = isset($data_title[$emp->title_id]) ? $data_title[$emp->title_id] : '-';
$position_name    = isset($data_position[$emp->position_id]) ? $data_position[$emp->position_id] : '-';
$marital_name     = isset($data_marital[$emp->marital_status]) ? $data_marital[$emp->marital_status] : '-';
$tax_marital_name = isset($data_marital[$emp->tax_marital_status]) ? $data_marital[$emp->tax_marital_status] : '-';
$finger_name      = isset($data_idfinger[$emp->finger_id]) ? $data_idfinger[$emp->finger_id] : '-';

// Hitung Masa Kerja
$tenure_str = '-';
if (!empty($emp->hiredate) && $emp->hiredate != '0000-00-00') {
	$tgl_masuk  = date_create($emp->hiredate);
	$tgl_kini   = date_create();
	$diff_masa  = date_diff($tgl_masuk, $tgl_kini);
	$tenure_str = $diff_masa->y . ' Th ' . $diff_masa->m . ' Bln ' . $diff_masa->d . ' Hari';
}

// Hitung Usia
$age_str = '-';
$bday_fmt = '-';
if (!empty($emp->birthday) && $emp->birthday != '0000-00-00') {
	$tgl_lahir = date_create($emp->birthday);
	$tgl_kini  = date_create();
	$diff_usia = date_diff($tgl_lahir, $tgl_kini);
	$age_str   = $diff_usia->y . ' Tahun';
	$bday_fmt  = date('d-m-Y', strtotime($emp->birthday));
}

// Format Agama
$religi_map = [
	'1' => 'Islam',
	'2' => 'Katolik',
	'3' => 'Kristen',
	'4' => 'Hindu',
	'5' => 'Budha',
	'6' => 'Kong Hu Chu'
];
$religi_name = isset($religi_map[$emp->relid]) ? $religi_map[$emp->relid] : ($emp->relid ? $emp->relid : '-');

// Format Status Kontrak
$contract_name = 'Belum Kontrak';
if ($emp->permanent_id == 'CTR004') {
	$contract_name = 'Tetap (Permanent)';
} elseif ($emp->thirdcontract_id == 'CTR003') {
	$contract_name = 'Kontrak Ketiga';
} elseif ($emp->secondcontract_id == 'CTR002') {
	$contract_name = 'Kontrak Kedua';
} elseif ($emp->firstcontract_id == 'CTR001') {
	$contract_name = 'Kontrak Pertama';
}

// Format Gender
$gender_label = '-';
if ($emp->genderid === 'L') {
	$gender_label = '<i class="fa fa-mars text-blue"></i> Laki-laki';
} elseif ($emp->genderid === 'P') {
	$gender_label = '<i class="fa fa-venus text-maroon"></i> Perempuan';
}
?>

<style>
	.profile-card {
		background: #fff;
		border-radius: 4px;
		border-top: 3px solid #3c8dbc;
		box-shadow: 0 1px 3px rgba(0,0,0,0.1);
		margin-bottom: 20px;
	}
	.profile-user-img {
		width: 100px;
		height: 100px;
		border-radius: 50%;
		object-fit: cover;
		border: 3px solid #d2d6de;
		margin: 0 auto;
		display: flex;
		align-items: center;
		justify-content: center;
		background: #f4f6f9;
		color: #3c8dbc;
		font-size: 48px;
	}
	.profile-username {
		font-size: 19px;
		font-weight: 700;
		margin-top: 12px;
		color: #222;
	}
	.profile-info-table th {
		width: 32%;
		color: #555;
		font-weight: 600;
		border-top: 1px solid #f0f0f0 !important;
		padding: 9px 12px !important;
		background-color: #fafbfc;
	}
	.profile-info-table td {
		color: #333;
		border-top: 1px solid #f0f0f0 !important;
		padding: 9px 12px !important;
	}
	.nav-tabs-custom > .nav-tabs > li.active {
		border-top-color: #3c8dbc;
	}
	.nav-tabs-custom > .nav-tabs > li > a {
		font-weight: 600;
		color: #555;
	}
	.nav-tabs-custom > .nav-tabs > li.active > a {
		color: #3c8dbc;
	}
	.badge-status-lg {
		font-size: 12px;
		padding: 4px 10px;
		border-radius: 12px;
	}
	@media print {
		.no-print {
			display: none !important;
		}
		.box {
			border: none !important;
			box-shadow: none !important;
		}
		.tab-content > .tab-pane {
			display: block !important;
			opacity: 1 !important;
			visibility: visible !important;
			margin-bottom: 20px;
		}
	}
</style>

<!-- Action Header Toolbar -->
<div class="row no-print" style="margin-bottom: 15px;">
	<div class="col-xs-12">
		<a href="<?= site_url('employees'); ?>" class="btn btn-default btn-flat" style="border-radius: 3px;">
			<i class="fa fa-arrow-left"></i> Kembali ke Daftar Karyawan
		</a>
		<button type="button" class="btn btn-primary btn-flat pull-right" onclick="window.print();" style="border-radius: 3px;">
			<i class="fa fa-print"></i> Cetak Profil
		</button>
	</div>
</div>

<div class="row">
	<!-- Profil Kiri (Summary Widget) -->
	<div class="col-md-4 col-sm-12">
		<div class="box box-primary">
			<div class="box-body box-profile text-center" style="padding-top: 25px;">
				<div class="profile-user-img">
					<i class="fa fa-user"></i>
				</div>
				<h3 class="profile-username"><?= !empty($emp->name) ? $emp->name : '-'; ?></h3>
				<p class="text-muted" style="margin-bottom: 6px; font-size: 13px;">
					<strong>NIK:</strong> <?= !empty($emp->nik) ? $emp->nik : '-'; ?> &nbsp;|&nbsp; 
					<strong>ID:</strong> <?= !empty($emp->id) ? $emp->id : '-'; ?>
				</p>
				<p style="font-size: 14px; font-weight: 600; color: #3c8dbc; margin-bottom: 12px;">
					<?= $title_name != '-' ? $title_name : ($position_name != '-' ? $position_name : 'Karyawan'); ?>
				</p>

				<div style="margin-bottom: 15px;">
					<?php if ($emp->flag_active == 'Y') : ?>
						<span class="label label-success badge-status-lg"><i class="fa fa-check-circle"></i> Karyawan Aktif</span>
					<?php else : ?>
						<span class="label label-danger badge-status-lg"><i class="fa fa-times-circle"></i> Non-Aktif</span>
					<?php endif; ?>

					<span class="label label-info badge-status-lg" style="margin-left: 5px;">
						<i class="fa fa-briefcase"></i> <?= $contract_name; ?>
					</span>
				</div>

				<ul class="list-group list-group-unbordered text-left" style="margin-top: 20px; font-size: 13px;">
					<li class="list-group-item">
						<b><i class="fa fa-building text-muted" style="width: 20px;"></i> Perusahaan</b>
						<span class="pull-right text-bold"><?= $company_name; ?></span>
					</li>
					<li class="list-group-item">
						<b><i class="fa fa-sitemap text-muted" style="width: 20px;"></i> Departemen</b>
						<span class="pull-right text-bold"><?= $department_name; ?></span>
					</li>
					<li class="list-group-item">
						<b><i class="fa fa-tags text-muted" style="width: 20px;"></i> Divisi</b>
						<span class="pull-right text-bold"><?= $division_name; ?></span>
					</li>
					<li class="list-group-item">
						<b><i class="fa fa-calendar-check-o text-muted" style="width: 20px;"></i> Tgl Masuk</b>
						<span class="pull-right">
							<?= (!empty($emp->hiredate) && $emp->hiredate != '0000-00-00') ? date('d-m-Y', strtotime($emp->hiredate)) : '-'; ?>
						</span>
					</li>
					<li class="list-group-item">
						<b><i class="fa fa-clock-o text-muted" style="width: 20px;"></i> Masa Kerja</b>
						<span class="pull-right text-primary text-bold"><?= $tenure_str; ?></span>
					</li>
					<li class="list-group-item">
						<b><i class="fa fa-hand-o-up text-muted" style="width: 20px;"></i> ID Fingerprint</b>
						<span class="pull-right"><?= $finger_name; ?></span>
					</li>
				</ul>
			</div>
		</div>

		<!-- Card Kontak Cepat -->
		<div class="box box-info">
			<div class="box-header with-border">
				<h3 class="box-title" style="font-size: 15px;"><i class="fa fa-phone" style="margin-right: 5px;"></i> Kontak Karyawan</h3>
			</div>
			<div class="box-body" style="font-size: 13px;">
				<p style="margin-bottom: 8px;">
					<strong><i class="fa fa-mobile text-muted" style="width: 20px; font-size: 16px;"></i> No. Handphone:</strong><br>
					<span style="margin-left: 24px;"><?= !empty($emp->hp) ? $emp->hp : '-'; ?></span>
				</p>
				<p style="margin-bottom: 8px;">
					<strong><i class="fa fa-phone text-muted" style="width: 20px;"></i> No. Telepon:</strong><br>
					<span style="margin-left: 24px;"><?= !empty($emp->phone) ? $emp->phone : '-'; ?></span>
				</p>
				<p style="margin-bottom: 0;">
					<strong><i class="fa fa-envelope text-muted" style="width: 20px;"></i> Alamat Email:</strong><br>
					<span style="margin-left: 24px;"><?= !empty($emp->email) ? $emp->email : '-'; ?></span>
				</p>
			</div>
		</div>
	</div>

	<!-- Kolom Kanan (Nav-Tabs Informasi Lengkap) -->
	<div class="col-md-8 col-sm-12">
		<div class="nav-tabs-custom" style="border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
			<ul class="nav nav-tabs">
				<li class="active">
					<a href="#tab_personal" data-toggle="tab"><i class="fa fa-user" style="margin-right: 5px;"></i> Data Pribadi</a>
				</li>
				<li>
					<a href="#tab_employment" data-toggle="tab"><i class="fa fa-briefcase" style="margin-right: 5px;"></i> Kepegawaian</a>
				</li>
				<li>
					<a href="#tab_address" data-toggle="tab"><i class="fa fa-map-marker" style="margin-right: 5px;"></i> Alamat & Domisili</a>
				</li>
				<li>
					<a href="#tab_identity" data-toggle="tab"><i class="fa fa-credit-card" style="margin-right: 5px;"></i> Legal & BPJS</a>
				</li>
				<li>
					<a href="#tab_family" data-toggle="tab"><i class="fa fa-heart" style="margin-right: 5px;"></i> Keluarga (<?= count($rows_family); ?>)</a>
				</li>
				<li>
					<a href="#tab_education" data-toggle="tab"><i class="fa fa-graduation-cap" style="margin-right: 5px;"></i> Pendidikan (<?= count($rows_education); ?>)</a>
				</li>
			</ul>

			<div class="tab-content" style="padding: 18px;">
				<!-- TAB 1: DATA PRIBADI -->
				<div class="tab-pane active" id="tab_personal">
					<h4 style="margin-top: 0; margin-bottom: 15px; font-weight: 600; color: #3c8dbc; border-bottom: 2px solid #f4f4f4; padding-bottom: 8px;">
						<i class="fa fa-id-badge"></i> Informasi Data Pribadi
					</h4>
					<div class="table-responsive">
						<table class="table table-striped table-bordered profile-info-table">
							<tbody>
								<tr>
									<th>Nama Lengkap</th>
									<td><?= !empty($emp->name) ? $emp->name : '-'; ?></td>
								</tr>
								<tr>
									<th>Nomor Induk Kependudukan (KTP)</th>
									<td><?= !empty($emp->licensid) ? $emp->licensid : '-'; ?></td>
								</tr>
								<tr>
									<th>Nomor Induk Karyawan (NIK)</th>
									<td><?= !empty($emp->nik) ? $emp->nik : '-'; ?></td>
								</tr>
								<tr>
									<th>Tempat / Tanggal Lahir</th>
									<td>
										<?= !empty($emp->hometown) ? $emp->hometown : '-'; ?>, <?= $bday_fmt; ?>
										<?php if ($age_str != '-') : ?>
											<span class="badge bg-aqua" style="margin-left: 8px;"><?= $age_str; ?></span>
										<?php endif; ?>
									</td>
								</tr>
								<tr>
									<th>Jenis Kelamin</th>
									<td><?= $gender_label; ?></td>
								</tr>
								<tr>
									<th>Agama</th>
									<td><?= $religi_name; ?></td>
								</tr>
								<tr>
									<th>Golongan Darah</th>
									<td>
										<?= !empty($emp->blood_group) ? '<span class="badge bg-red">' . $emp->blood_group . '</span>' : '-'; ?>
									</td>
								</tr>
								<tr>
									<th>Kewarganegaraan</th>
									<td><?= !empty($emp->nationality) ? $emp->nationality : 'WNI'; ?></td>
								</tr>
								<tr>
									<th>Status Pernikahan</th>
									<td><?= $marital_name; ?></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>

				<!-- TAB 2: KEPEGAWAIAN -->
				<div class="tab-pane" id="tab_employment">
					<h4 style="margin-top: 0; margin-bottom: 15px; font-weight: 600; color: #3c8dbc; border-bottom: 2px solid #f4f4f4; padding-bottom: 8px;">
						<i class="fa fa-briefcase"></i> Informasi Posisi & Kepegawaian
					</h4>
					<div class="table-responsive">
						<table class="table table-striped table-bordered profile-info-table">
							<tbody>
								<tr>
									<th>Perusahaan (Company)</th>
									<td class="text-bold"><?= $company_name; ?></td>
								</tr>
								<tr>
									<th>Divisi</th>
									<td><?= $division_name; ?></td>
								</tr>
								<tr>
									<th>Kepala Divisi (Division Head)</th>
									<td><?= $division_head; ?></td>
								</tr>
								<tr>
									<th>Departemen</th>
									<td><?= $department_name; ?></td>
								</tr>
								<tr>
									<th>Jabatan (Title)</th>
									<td><?= $title_name; ?></td>
								</tr>
								<tr>
									<th>Posisi</th>
									<td><?= $position_name; ?></td>
								</tr>
								<tr>
									<th>ID Mesin Fingerprint</th>
									<td><?= $finger_name; ?></td>
								</tr>
								<tr>
									<th>Tanggal Mulai Bekerja (Hire Date)</th>
									<td>
										<?= (!empty($emp->hiredate) && $emp->hiredate != '0000-00-00') ? date('d-m-Y', strtotime($emp->hiredate)) : '-'; ?>
									</td>
								</tr>
								<tr>
									<th>Total Masa Kerja</th>
									<td><strong class="text-primary"><?= $tenure_str; ?></strong></td>
								</tr>
								<tr>
									<th>Status Ikatan Kerja</th>
									<td>
										<span class="label label-info" style="font-size: 12px;"><?= $contract_name; ?></span>
									</td>
								</tr>
								<tr>
									<th>Status Keaktifan</th>
									<td>
										<?php if ($emp->flag_active == 'Y') : ?>
											<span class="label label-success"><i class="fa fa-check"></i> Aktif Bekerja</span>
										<?php else : ?>
											<span class="label label-danger"><i class="fa fa-times"></i> Non-Aktif</span>
										<?php endif; ?>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>

				<!-- TAB 3: ALAMAT & DOMISILI -->
				<div class="tab-pane" id="tab_address">
					<h4 style="margin-top: 0; margin-bottom: 15px; font-weight: 600; color: #3c8dbc; border-bottom: 2px solid #f4f4f4; padding-bottom: 8px;">
						<i class="fa fa-map-marker"></i> Alamat Tempat Tinggal & KTP
					</h4>
					<div class="table-responsive">
						<table class="table table-striped table-bordered profile-info-table">
							<tbody>
								<tr>
									<th>Alamat Domisili (Tempat Tinggal)</th>
									<td><?= !empty($emp->address) ? nl2br($emp->address) : '-'; ?></td>
								</tr>
								<tr>
									<th>Kota Domisili</th>
									<td><?= !empty($emp->city) ? $emp->city : '-'; ?></td>
								</tr>
								<tr>
									<th>Provinsi Domisili</th>
									<td><?= !empty($emp->province) ? $emp->province : '-'; ?></td>
								</tr>
								<tr>
									<th>Kode Pos</th>
									<td><?= !empty($emp->postcode) ? $emp->postcode : '-'; ?></td>
								</tr>
								<tr>
									<th>Alamat Sesuai KTP</th>
									<td><?= !empty($emp->idcard_address) ? nl2br($emp->idcard_address) : '-'; ?></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>

				<!-- TAB 4: LEGAL & FINANCIAL / BPJS -->
				<div class="tab-pane" id="tab_identity">
					<h4 style="margin-top: 0; margin-bottom: 15px; font-weight: 600; color: #3c8dbc; border-bottom: 2px solid #f4f4f4; padding-bottom: 8px;">
						<i class="fa fa-credit-card"></i> Identitas Legal, Bank & BPJS
					</h4>
					<div class="table-responsive">
						<table class="table table-striped table-bordered profile-info-table">
							<tbody>
								<tr>
									<th>No. KTP</th>
									<td><?= !empty($emp->licensid) ? $emp->licensid : '-'; ?></td>
								</tr>
								<tr>
									<th>Nomor NPWP</th>
									<td><?= !empty($emp->taxid) ? $emp->taxid : '-'; ?></td>
								</tr>
								<tr>
									<th>Status Pajak Pernikahan</th>
									<td><?= $tax_marital_name; ?></td>
								</tr>
								<tr>
									<th>Nama Bank</th>
									<td>
										<?php if (!empty($emp->bank_id)) : ?>
											<span class="label label-primary" style="font-size: 11px;"><?= $emp->bank_id; ?></span>
										<?php else : ?>
											-
										<?php endif; ?>
									</td>
								</tr>
								<tr>
									<th>Nomor Rekening Bank</th>
									<td><strong style="letter-spacing: 0.5px;"><?= !empty($emp->accnumber) ? $emp->accnumber : '-'; ?></strong></td>
								</tr>
								<tr>
									<th>Nama Pemilik Rekening</th>
									<td><?= !empty($emp->accname) ? $emp->accname : '-'; ?></td>
								</tr>
								<tr>
									<th>No. BPJS Kesehatan</th>
									<td><?= !empty($emp->bpjs_kes) ? $emp->bpjs_kes : '-'; ?></td>
								</tr>
								<tr>
									<th>No. BPJS Ketenagakerjaan</th>
									<td><?= !empty($emp->bpjs_ket) ? $emp->bpjs_ket : '-'; ?></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>

				<!-- TAB 5: DATA KELUARGA -->
				<div class="tab-pane" id="tab_family">
					<h4 style="margin-top: 0; margin-bottom: 15px; font-weight: 600; color: #3c8dbc; border-bottom: 2px solid #f4f4f4; padding-bottom: 8px;">
						<i class="fa fa-heart"></i> Susunan Anggota Keluarga
					</h4>
					<?php if (!empty($rows_family)) : ?>
						<div class="table-responsive">
							<table class="table table-bordered table-striped table-hover">
								<thead>
									<tr style="background-color: #f4f6f9; color: #333;">
										<th class="text-center" style="width: 45px;">No</th>
										<th>Hubungan / Kategori</th>
										<th>Nama Lengkap</th>
										<th>Tempat Lahir</th>
										<th class="text-center" style="width: 120px;">Tanggal Lahir</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$no_f = 0;
									foreach ($rows_family as $fam) :
										$no_f++;
										$fam_cat = isset($family_type[$fam['category']]) ? $family_type[$fam['category']] : $fam['category'];
										$fam_bday = (!empty($fam['birth_date']) && $fam['birth_date'] != '0000-00-00') ? date('d-m-Y', strtotime($fam['birth_date'])) : '-';
									?>
										<tr>
											<td class="text-center"><?= $no_f; ?></td>
											<td><span class="label label-default" style="font-size: 11px;"><?= $fam_cat; ?></span></td>
											<td><strong><?= !empty($fam['name']) ? $fam['name'] : '-'; ?></strong></td>
											<td><?= !empty($fam['birth_place']) ? $fam['birth_place'] : '-'; ?></td>
											<td class="text-center"><?= $fam_bday; ?></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					<?php else : ?>
						<div class="alert alert-info" style="border-radius: 4px;">
							<i class="fa fa-info-circle"></i> Belum ada data keluarga yang tercatat untuk karyawan ini.
						</div>
					<?php endif; ?>
				</div>

				<!-- TAB 6: RIWAYAT PENDIDIKAN -->
				<div class="tab-pane" id="tab_education">
					<h4 style="margin-top: 0; margin-bottom: 15px; font-weight: 600; color: #3c8dbc; border-bottom: 2px solid #f4f4f4; padding-bottom: 8px;">
						<i class="fa fa-graduation-cap"></i> Riwayat Pendidikan Formal
					</h4>
					<?php if (!empty($rows_education)) : ?>
						<div class="table-responsive">
							<table class="table table-bordered table-striped table-hover">
								<thead>
									<tr style="background-color: #f4f6f9; color: #333;">
										<th class="text-center" style="width: 45px;">No</th>
										<th style="width: 150px;">Jenjang Pendidikan</th>
										<th>Nama Institusi / Lembaga / Sekolah</th>
										<th class="text-center" style="width: 130px;">Tahun Lulus</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$no_e = 0;
									foreach ($rows_education as $edu) :
										$no_e++;
										$edu_lvl = isset($education_type[$edu['level']]) ? $education_type[$edu['level']] : $edu['level'];
									?>
										<tr>
											<td class="text-center"><?= $no_e; ?></td>
											<td><span class="label label-primary" style="font-size: 11px;"><?= $edu_lvl; ?></span></td>
											<td><strong><?= !empty($edu['institution']) ? $edu['institution'] : '-'; ?></strong></td>
											<td class="text-center"><?= !empty($edu['graduated']) ? $edu['graduated'] : '-'; ?></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					<?php else : ?>
						<div class="alert alert-info" style="border-radius: 4px;">
							<i class="fa fa-info-circle"></i> Belum ada data riwayat pendidikan yang tercatat untuk karyawan ini.
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>
