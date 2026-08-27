<?php
$is_admin       = isset($is_admin) ? $is_admin : $this->auth->is_admin();
$user_dept_id   = isset($user_dept_id) ? $user_dept_id : '';
$id_dept        = (!empty($header)) ? $header[0]->id_dept : ((!$is_admin) ? $user_dept_id : '');
$id_costcenter  = (!empty($header)) ? $header[0]->id_costcenter : '';
$budget         = (!empty($header)) ? number_format($header[0]->budget) : '0';
$sisa_budget    = (!empty($header)) ? number_format($header[0]->sisa_budget) : '0';
$coa            = (!empty($header)) ? $header[0]->coa : '';
$upload_spk     = (!empty($header)) ? $header[0]->document : '';
$no_so          = (!empty($header)) ? $header[0]->no_so : '';
$project_name   = (!empty($header)) ? $header[0]->project_name : '';
$pr_coa         = (!empty($header)) ? $header[0]->coa : '';
$tingkat_pr     = (!empty($header)) ? $header[0]->tingkat_pr : '';
$bank_name      = (!empty($header)) ? $header[0]->bank_name : '';
$bank_account_no   = (!empty($header)) ? $header[0]->bank_account_no : '';
$bank_account_name = (!empty($header)) ? $header[0]->bank_account_name : '';
$nm_pembuat     = (!empty($header)) ? $header[0]->nm_pembuat : '';
$tgl_dibuat     = (!empty($header) && !empty($header[0]->created_date)) ? date('d-M-Y', strtotime($header[0]->created_date)) : '-';

$docs_list = [];
if (!empty($upload_spk)) {
	$decoded = json_decode($upload_spk, true);
	if (is_array($decoded)) {
		$docs_list = $decoded;
	} else {
		$docs_list = [$upload_spk];
	}
}

// Detail Approval
$alasan_reject1 = (!empty($header)) ? $header[0]->reject_reason1 : '';
$alasan_reject2 = (!empty($header)) ? $header[0]->reject_reason2 : '';
$alasan_reject3 = (!empty($header)) ? $header[0]->reject_reason3 : '';

$keterangan_1   = (!empty($header)) ? $header[0]->keterangan_1 : '';
$keterangan_2   = (!empty($header)) ? $header[0]->keterangan_2 : '';
$keterangan_3   = (!empty($header)) ? $header[0]->keterangan_3 : '';

$status1        = '';
$tgl_appre_1    = '';
$status2        = '';
$tgl_appre_2    = '';
$status3        = '';
$tgl_appre_3    = '';

if (!empty($header)) {
	if ($header[0]->app_3 == '1') {
		$status3     = '<span class="badge" style="background:#00a65a; font-size:11px;">Approved</span>';
		$tgl_appre_3 = date('d F Y', strtotime($header[0]->app_3_date));
	} else {
		if ($header[0]->sts_reject3 == '1') {
			$status3     = '<span class="badge" style="background:#dd4b39; font-size:11px;">Rejected</span>';
			$tgl_appre_3 = date('d F Y', strtotime($header[0]->sts_reject3_date));
		} else {
			$status3 = '<span class="badge" style="background:#f39c12; font-size:11px;">Waiting</span>';
		}
	}
}
// End Detail Status

$tanda    = (!empty($code)) ? 'Update' : 'Insert';
$disabled = (!empty($approve)) ? 'disabled' : '';
$disabled2 = ($approve == 'view') ? 'disabled' : '';
$disabled3 = ($approve == 'view') ? 'readonly' : '';
?>
<style>
	.section-title {
		font-size: 13px;
		font-weight: 700;
		color: #3c8dbc;
		text-transform: uppercase;
		letter-spacing: 0.5px;
		border-bottom: 2px solid #3c8dbc;
		padding-bottom: 6px;
		margin-bottom: 15px;
	}

	.breadcrumb {
		background: none;
		padding: 0;
		font-size: 12px;
		margin-bottom: 5px;
	}

	.approval-progress {
		display: flex;
		gap: 0;
		border: 1px solid #ddd;
		border-radius: 4px;
		overflow: hidden;
		margin-bottom: 20px;
	}

	.approval-step {
		flex: 1;
		padding: 12px 15px;
		border-right: 1px solid #ddd;
		background: #f9f9f9;
	}

	.approval-step:last-child {
		border-right: none;
	}

	.approval-step .step-label {
		font-size: 10px;
		font-weight: 700;
		color: #777;
		text-transform: uppercase;
		letter-spacing: 0.5px;
		margin-bottom: 6px;
	}

	.approval-step .step-status {
		margin-bottom: 4px;
	}

	.approval-step .step-date {
		font-size: 11px;
		color: #888;
		margin-bottom: 4px;
	}

	.approval-step .step-reason {
		font-size: 11px;
		color: #c0392b;
		font-style: italic;
	}

	.detail-table thead tr th {
		background-color: #3c8dbc;
		color: #fff;
		font-size: 12px;
		border-color: #357ca5;
	}

	.action-footer {
		display: flex;
		gap: 8px;
		justify-content: flex-end;
		margin-top: 20px;
		padding-top: 15px;
		border-top: 1px solid #eee;
	}

	.chosen-container-active .chosen-single {
		border: none;
		box-shadow: none;
	}

	.chosen-container-single .chosen-single {
		height: 34px;
		border: 1px solid #d2d6de;
		border-radius: 0px;
		background: none;
		box-shadow: none;
		color: #444;
		line-height: 32px;
	}

	.chosen-container-single .chosen-single div {
		top: 5px;
	}

	.chosen-container .chosen-results {
		max-height: 300px;
		overflow-y: auto;
	}

	.datepicker {
		cursor: pointer;
	}
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.css" integrity="sha512-0nkKORjFgcyxv3HbE4rzFUlENUMNqic/EzDIeYCgsKa/nwqr2B91Vu/tNAu4Q0cBuG4Xe/D1f/freEci/7GDRA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<form action="#" method="POST" id="form_ct" enctype="multipart/form-data" autocomplete="off">
	<input type="hidden" name="id" value="<?= $id; ?>">
	<input type="hidden" name="tanda" value="<?= $tanda; ?>">
	<input type="hidden" id="approve" name="approve" value="<?= $approve; ?>">
	<input type="hidden" name="tingkat_approval" id="tingkat_approval" value="<?= $tingkat_approval ?>">

	<div class="box box-primary">
		<div class="box-header with-border">
			<div>
				<ol class="breadcrumb">
					<li><i class="fa fa-shopping-cart"></i> Procurement</li>
					<li><a href="<?= site_url('non_rutin') ?>">PR Non-Rutin</a></li>
					<li class="active">Form PR</li>
				</ol>
				<h3 class="box-title" style="font-size:16px; font-weight:700;"><?php echo $title; ?></h3>
			</div>
			<div class="box-tools pull-right">
				<button type="button" class="btn btn-sm btn-default" id="back">
					<i class="fa fa-arrow-left"></i>&nbsp; Kembali
				</button>
			</div>
		</div>
		<!-- /.box-header -->

		<div class="box-body">

			<!-- Section: Informasi PR -->
			<div class="section-title"><i class="fa fa-info-circle"></i>&nbsp; Informasi PR</div>

			<div class="form-group row">
				<label class="label-control col-sm-2"><b>Department <span class="text-red">*</span></b></label>
				<div class="col-sm-4">
					<select name="id_dept" id="id_dept" class="form-control input-md chosen_select" <?= $disabled; ?>>
						<?php if ($is_admin) { ?>
							<option value="0">Select An Department</option>
						<?php } ?>
						<?php
						foreach ($list_departement as $departement) {
							$selected = '';
							if ($departement->id == $id_dept || (!$is_admin && count($list_departement) == 1)) {
								$selected = 'selected';
							}
							echo "<option value='" . $departement->id . "' " . $selected . ">" . strtoupper($departement->name . ' - ' . $departement->nm_company) . "</option>";
						}
						?>
					</select>
				</div>
				<label class="label-control col-sm-2"><b>Project Name</b></label>
				<div class="col-sm-4">
					<?php
					echo form_input(array('id' => 'project_name', 'name' => 'project_name', 'class' => 'form-control input-md', 'placeholder' => 'Project Name'), $project_name);
					?>
				</div>
			</div>

			<div class="form-group row">
				<label class="label-control col-sm-2"><b>Upload Document</b></label>
				<div class="col-sm-10">
					<?php if (empty($approve)) { ?>
						<div style="display:flex; align-items:center; gap:10px;">
							<label class="btn btn-sm btn-primary btn-flat" style="margin-bottom:0; cursor:pointer; font-weight:600;">
								<i class="fa fa-folder-open"></i> Pilih File Lampiran
								<input type="file" id="temp_file_picker" style="display:none;" accept=".jpg,.jpeg,.png,.pdf,.xls,.xlsx" multiple>
							</label>
							<span id="selected_files_count" class="text-muted" style="font-size:12px;">Belum ada file baru dipilih</span>
						</div>
					<?php } ?>

					<!-- Hidden actual input holding accumulated files -->
					<input type="file" id="upload_spk" name="upload_spk[]" multiple style="display:none;">

					<!-- List of newly selected files -->
					<div id="new_files_list" style="display:flex; flex-wrap:wrap; gap:6px; margin-top:8px;"></div>
					<small class="text-muted" style="font-size:11px; display:block; margin-top:4px;">Format diterima: JPG, PNG, PDF, XLS/XLSX - Maks. 5 MB per file. Anda bisa klik tombol <b>Pilih File Lampiran</b> berkali-kali untuk memilih file dari folder mana saja satu per satu.</small>

					<?php if (!empty($docs_list)) { ?>
						<div class="existing-files-container" style="margin-top:10px;">
							<label style="font-size:12px; font-weight:600; color:#555;"><i class="fa fa-paperclip"></i> Dokumen Terlampir Sebelumnya:</label>
							<div class="file-list" style="display:flex; flex-wrap:wrap; gap:8px; margin-top:4px;">
								<?php foreach ($docs_list as $doc_file) { ?>
									<span class="badge file-item-badge" style="background:#3c8dbc; padding:6px 10px; font-size:11px; font-weight:normal; border-radius:3px; display:inline-flex; align-items:center; gap:6px;">
										<a href="<?= base_url('assets/pr/' . $doc_file); ?>" target="_blank" style="color:#fff; text-decoration:none;" title="Buka / Download">
											<i class="fa fa-file"></i> <?= $doc_file; ?>
										</a>
										<input type="hidden" name="existing_docs[]" value="<?= htmlspecialchars($doc_file); ?>">
										<?php if (empty($approve)) { ?>
											<button type="button" class="btn btn-xs btn-danger remove-existing-file" style="padding:1px 5px; line-height:1; font-size:10px; border-radius:2px;" title="Hapus file ini">
												<i class="fa fa-times"></i>
											</button>
										<?php } ?>
									</span>
								<?php } ?>
							</div>
						</div>
					<?php } ?>
				</div>
			</div>

			<div class="form-group row">
				<label class="label-control col-sm-2"><b>Tingkat PR</b></label>
				<div class="col-sm-4">
					<select name="tingkat_pr" id="" class="form-control input-md" <?= $disabled; ?>>
						<option value="1" <?= ($tingkat_pr == '1') ? 'selected' : null ?>>Normal</option>
						<option value="2" <?= ($tingkat_pr == '2') ? 'selected' : null ?>>Urgent</option>
					</select>
				</div>
			</div>

			<div class="form-group row">
				<label class="label-control col-sm-2"><b>Nama Pembuat PR</b></label>
				<div class="col-sm-4">
					<input type="text" class="form-control" value="<?= ucwords(strtolower($nm_pembuat)) ?>" readonly>
				</div>
				<label class="label-control col-sm-2"><b>Tgl Dibuat PR</b></label>
				<div class="col-sm-4">
					<input type="text" class="form-control" value="<?= $tgl_dibuat ?>" readonly>
				</div>
			</div>

			<!-- Section: Status Approval -->
			<div class="section-title" style="margin-top:20px;"><i class="fa fa-check-circle"></i>&nbsp; Status Approval</div>

			<div class="approval-progress">
				<div class="approval-step">
					<div class="step-label"><i class="fa fa-building"></i>&nbsp; Management</div>
					<div class="step-status"><?= $status3 ?: '<span class="badge" style="background:#aaa; font-size:11px;">Belum Diproses</span>' ?></div>
					<?php if (!empty($tgl_appre_3)) { ?>
						<div class="step-date"><i class="fa fa-calendar"></i>&nbsp; <?= $tgl_appre_3 ?></div>
					<?php } ?>
					<?php if (!empty($alasan_reject3)) { ?>
						<div class="step-reason"><i class="fa fa-times"></i>&nbsp; <?= $alasan_reject3 ?></div>
					<?php } ?>
					<div style="margin-top:8px;">
						<input type="hidden" name="reject_reason3" value="<?= $alasan_reject3 ?>">
						<input type="text" name="keterangan_3" class="form-control input-sm" placeholder="Keterangan..." value="<?= $keterangan_3 ?>" style="font-size:11px;">
					</div>
				</div>
			</div>

			<?php if ($approve == 'approve') { ?>
				<div class="form-group row">
					<label class="label-control col-sm-2"><b>Approve <span class="text-red">*</span></b></label>
					<div class="col-sm-2">
						<select name="sts_app" id="sts_app" class="form-control input-md">
							<option value="0">Select Approve</option>
							<option value="Y">Approve</option>
							<option value="D">Reject</option>
						</select>
					</div>
					<div class="col-sm-2"></div>
					<label class="label-control col-sm-2 tnd_reason"><b>Reason <span class="text-red">*</span></b></label>
					<div class="col-sm-4 tnd_reason">
						<?php
						echo form_textarea(array('id' => 'reason', 'name' => 'reason', 'class' => 'form-control input-md', 'rows' => '2', 'cols' => '75', 'placeholder' => 'Reason'));
						?>
					</div>
				</div>
			<?php } ?>

			<!-- Section: Detail Barang/Jasa -->
			<div class="section-title" style="margin-top:20px;"><i class="fa fa-list"></i>&nbsp; Detail Barang / Jasa</div>

			<table class="table table-striped table-bordered table-hover table-condensed detail-table" width="100%">
				<thead>
					<tr>
						<th class="text-center" style="width:3%;">#</th>
						<th class="text-center">Nama Barang/Jasa</th>
						<th class="text-center" style="width:13%;">Spec/ Requirement</th>
						<th class="text-center" style="width:6%;">Qty</th>
						<th class="text-center" style="width:7%;">Satuan</th>
						<th class="text-center" style="width:14%;">COA</th>
						<th class="text-center" style="width:8%;">Est Harga</th>
						<th class="text-center" style="width:9%;">Est Total Harga</th>
						<th class="text-center" style="width:9%;">Tanggal Dibutuhkan</th>
						<th class="text-center" style="width:13%;">Keterangan</th>
						<?php if (empty($approve)) { ?>
							<th class="text-center" style="width:6%;">#</th>
						<?php } ?>
					</tr>
				</thead>
				<tbody>
					<?php
					$nomor = 0;
					if (!empty($detail)) {
						foreach ($detail as $val => $valx) {
							$nomor++;
							$item_coa = isset($valx['coa']) ? $valx['coa'] : '';
							echo "<tr class='header_" . $nomor . "'>";
							echo "<td align='center'>" . $nomor . "<input type='hidden' name='detail[" . $nomor . "][id]' value='" . ($valx['id'] ?? '') . "'></td>";
							echo "<td align='left'>
                                <textarea class='form-control input-md nm_barang_" . $nomor . "' name='detail[" . $nomor . "][nm_barang]' " . $disabled3 . ">" . strtoupper($valx['nm_barang']) . "</textarea>
                            </td>";
							echo "<td align='left'>
                                <textarea class='form-control input-md spec_" . $nomor . "' name='detail[" . $nomor . "][spec]' " . $disabled3 . ">" . strtoupper($valx['spec']) . "</textarea>
                            </td>";
							echo "<td align='left'><input type='text' " . $disabled2 . " id='qty_" . $nomor . "' name='detail[" . $nomor . "][qty]' class='form-control input-md text-right autoNumeric2 sum_tot qty_" . $nomor . "' value='" . $valx['qty'] . "'></td>";
							echo "<td align='left'>
                                <select name='detail[" . $nomor . "][satuan]' class='form-control wajib satuan_" . $nomor . "' " . $disabled2 . " required>";
							echo "<option value=''>Pilih</option>";
							foreach ($satuan as $key => $value) {
								$selected = ($value['id'] == $valx['satuan']) ? 'selected' : '';
								echo "<option value='" . $value['id'] . "' " . $selected . ">" . $value['code'] . "</option>";
							}
							echo "</select></td>";

							// COA Dropdown per item
							echo "<td align='left'>";
							if ($approve == 'view') {
								$nm_coa_display = $item_coa;
								foreach ($list_coa as $c) {
									if ($c['no_perkiraan'] == $item_coa) {
										$nm_coa_display = $c['no_perkiraan'] . ' - ' . $c['nama'];
										break;
									}
								}
								echo "<input type='text' class='form-control input-md' value='" . htmlspecialchars($nm_coa_display) . "' readonly>";
								echo "<input type='hidden' name='detail[" . $nomor . "][coa]' value='" . htmlspecialchars($item_coa) . "'>";
							} else {
								echo "<select name='detail[" . $nomor . "][coa]' class='form-control chosen_select wajib_coa coa_" . $nomor . "' " . $disabled2 . " required>";
								echo "<option value=''>- Pilih COA -</option>";
								foreach ($list_coa as $c) {
									$selected = ($c['no_perkiraan'] == $item_coa) ? 'selected' : '';
									echo "<option value='" . $c['no_perkiraan'] . "' " . $selected . ">" . $c['no_perkiraan'] . ' - ' . $c['nama'] . "</option>";
								}
								echo "</select>";
							}
							echo "</td>";

							echo "<td align='left'><input type='text' " . $disabled2 . " id='harga_" . $nomor . "' name='detail[" . $nomor . "][harga]' class='form-control input-md text-right maskM sum_tot harga_" . $nomor . "' value='" . $valx['harga'] . "' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''></td>";
							echo "<td align='left'><input type='text' " . $disabled2 . " id='total_harga_" . $nomor . "' name='detail[" . $nomor . "][total_harga]' class='form-control input-md text-right maskM jumlah_all total_harga_" . $nomor . "' value='" . ($valx['qty'] * $valx['harga']) . "' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero='' readonly></td>";
							echo "<td align='left'><input type='text' " . $disabled3 . " name='detail[" . $nomor . "][tanggal]' class='form-control input-md text-center datepicker tgl_dibutuhkan tanggal_" . $nomor . "' readonly value='" . strtoupper($valx['tanggal']) . "'></td>";
							echo "<td align='left'>
                                <textarea class='form-control input-md keterangan_" . $nomor . "' name='detail[" . $nomor . "][keterangan]' " . $disabled3 . ">" . strtoupper($valx['keterangan']) . "</textarea>
                            </td>";
							if (empty($approve)) {
								echo "<td align='center'><button type='button' class='btn btn-sm btn-warning edit_detail edit_detail_" . $nomor . "' data-id='" . $valx['id'] . "' data-nomor='" . $nomor . "' style='margin-right:0.5em;'><i class='fa fa-pencil'></i></button><button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button></td>";
							}
							echo "</tr>";
						}
					}
					if (empty($approve)) {
					?>
						<tr id="add_<?= $nomor; ?>">
							<td align="center"></td>
							<td align="left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type="button" class="btn btn-sm btn-warning addPart" title="Add Barang"><i class="fa fa-plus"></i>&nbsp;&nbsp;Add Barang</button></td>
							<td align="center" colspan="9"></td>
						</tr>
					<?php } ?>
				</tbody>
			</table>

			<div class="row" style="margin-top:10px;">
				<div class="col-sm-12 text-right">
					<h4 style="font-weight:700; margin:0; color:#333;">Total PR Keseluruhan: <span style="color:#3c8dbc;" id="total_pr_display">Rp 0</span></h4>
				</div>
			</div>

			<!-- Section: INFORMASI BANK -->
			<div class="section-title" style="margin-top:25px;"><i class="fa fa-university"></i>&nbsp; INFORMASI BANK</div>

			<div class="form-group row">
				<label class="label-control col-sm-2"><b>Bank <span class="text-red">*</span></b></label>
				<div class="col-sm-5">
					<input type="text" name="bank_name" id="bank_name" class="form-control input-md" placeholder="Nama Bank (e.g. BCA, Mandiri)" value="<?= htmlspecialchars($bank_name); ?>" <?= $disabled; ?>>
				</div>
			</div>

			<div class="form-group row">
				<label class="label-control col-sm-2"><b>No Rekening <span class="text-red">*</span></b></label>
				<div class="col-sm-5">
					<input type="text" name="bank_account_no" id="bank_account_no" class="form-control input-md" placeholder="No. Rekening" value="<?= htmlspecialchars($bank_account_no); ?>" <?= $disabled; ?>>
				</div>
			</div>

			<div class="form-group row">
				<label class="label-control col-sm-2"><b>Nama Rekening <span class="text-red">*</span></b></label>
				<div class="col-sm-5">
					<input type="text" name="bank_account_name" id="bank_account_name" class="form-control input-md" placeholder="Nama Pemilik Rekening" value="<?= htmlspecialchars($bank_account_name); ?>" <?= $disabled; ?>>
				</div>
			</div>

			<div class="alert" style="background-color:#fffdf0; border:1px solid #f9e29d; color:#8a6d3b; margin-top:15px; font-size:12px; border-radius:4px; padding:10px 15px;">
				<strong>Catatan desain:</strong> COA kini dipilih per baris item, bukan satu COA untuk seluruh PR. Setiap baris wajib memilih COA sebelum PR bisa disimpan — baris dengan COA kosong akan ditandai merah. Ini mengantisipasi kasus satu PR berisi barang dan jasa dengan akun biaya yang berbeda, supaya jurnal expense di belakang tidak salah alokasi akun.
			</div>

			<!-- Action Footer -->
			<div class="action-footer">
				<?php if ($approve <> 'view') { ?>
					<button type="button" class="btn btn-md btn-primary" id="save">
						<i class="fa fa-save"></i>&nbsp; Save
					</button>
				<?php } ?>
				<button type="button" class="btn btn-md btn-default" id="back">
					<i class="fa fa-arrow-left"></i>&nbsp; Back
				</button>
			</div>

			<?php if (!empty($docs_list)) : ?>
				<div class="col-md-12" style="margin-top:25px; padding:0;">
					<div class="section-title"><i class="fa fa-paperclip"></i>&nbsp; Lampiran Dokumen PR (<?= count($docs_list) ?> file)</div>
					<div class="row">
						<?php foreach ($docs_list as $doc_item) :
							$ext = strtolower(pathinfo($doc_item, PATHINFO_EXTENSION));
							$file_path = 'assets/pr/' . $doc_item;
							$file_url = base_url($file_path);
							if (file_exists($file_path)) :
						?>
							<div class="col-md-6" style="margin-bottom:15px;">
								<div class="panel panel-default">
									<div class="panel-heading" style="font-size:12px; font-weight:bold; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
										<i class="fa fa-file"></i> <?= htmlspecialchars($doc_item) ?>
										<a href="<?= $file_url ?>" target="_blank" class="btn btn-xs btn-primary pull-right"><i class="fa fa-download"></i> Buka / Download</a>
									</div>
									<div class="panel-body text-center" style="max-height:420px; overflow:auto; padding:10px;">
										<?php if ($ext == 'pdf') : ?>
											<iframe src="<?= $file_url ?>#toolbar=0&navpanes=0" style="width:100%; height:380px;" frameborder="0"></iframe>
										<?php elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) : ?>
											<a href="<?= $file_url ?>" target="_blank">
												<img src="<?= $file_url ?>" class="img-responsive img-thumbnail" style="max-height:380px; margin:0 auto;">
											</a>
										<?php else : ?>
											<div style="padding:40px 0;">
												<i class="fa fa-file-text-o fa-4x text-muted"></i>
												<p style="margin-top:10px; font-weight:600; color:#555;"><?= htmlspecialchars($doc_item) ?></p>
												<a href="<?= $file_url ?>" target="_blank" class="btn btn-sm btn-info"><i class="fa fa-download"></i> Download File (<?= strtoupper($ext) ?>)</a>
											</div>
										<?php endif; ?>
									</div>
								</div>
							</div>
						<?php 
							endif;
						endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

		</div>
		<!-- /.box-body -->
	</div>
	<!-- /.box -->
</form>

<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js" integrity="sha512-rMGGF4wg1R73ehtnxXBt5mbUfN9JUJwbk21KMlnLZDJh7BkPmeovBuddZCENJddHYYMkCh9hPFnPmS9sspki8g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
	$(document).ready(function() {
		$('.maskM').autoNumeric();
		$('.autoNumeric2').autoNumeric('init', {
			mDec: '2',
			aPad: false
		});
		$('.chosen_select').chosen({
			width: '100%'
		});
		$('.datepicker').datepicker({
			dateFormat: 'yy-mm-dd',
			//minDate: 0
		});
		$('.tnd_reason').hide();
		calculate_all_total();
	});

	// Single Button Multi-Directory File Upload Accumulator
	var dtUpload = new DataTransfer();

	$(document).on('change', '#temp_file_picker', function() {
		var newFiles = this.files;
		if (newFiles.length > 0) {
			for (var i = 0; i < newFiles.length; i++) {
				var file = newFiles[i];
				var exists = false;
				for (var j = 0; j < dtUpload.items.length; j++) {
					var existingFile = dtUpload.items[j].getAsFile();
					if (existingFile && existingFile.name === file.name && existingFile.size === file.size) {
						exists = true;
						break;
					}
				}
				if (!exists) {
					dtUpload.items.add(file);
				}
			}
			var hiddenInput = document.getElementById('upload_spk');
			if (hiddenInput) {
				hiddenInput.files = dtUpload.files;
			}
			$(this).val('');
			render_new_files_list();
		}
	});

	function render_new_files_list() {
		var container = $('#new_files_list');
		container.empty();
		var count = dtUpload.files.length;
		if (count > 0) {
			$('#selected_files_count').text(count + ' file baru dipilih:');
			for (var i = 0; i < count; i++) {
				var file = dtUpload.files[i];
				var sizeKb = Math.round(file.size / 1024);
				var badgeHtml = '<span class="badge" style="background:#00a65a; padding:6px 10px; font-size:11px; font-weight:normal; border-radius:3px; display:inline-flex; align-items:center; gap:6px;">' +
					'<i class="fa fa-file-o"></i> ' + file.name + ' (' + sizeKb + ' KB) ' +
					'<button type="button" class="btn btn-xs btn-danger remove-new-file" data-index="' + i + '" style="padding:1px 5px; line-height:1; font-size:10px; border-radius:2px; margin-left:4px;" title="Hapus file ini">' +
					'<i class="fa fa-times"></i>' +
					'</button>' +
					'</span>';
				container.append(badgeHtml);
			}
		} else {
			$('#selected_files_count').text('Belum ada file baru dipilih');
		}
	}

	$(document).on('click', '.remove-new-file', function(e) {
		e.preventDefault();
		var idx = parseInt($(this).data('index'));
		var newDt = new DataTransfer();
		for (var i = 0; i < dtUpload.files.length; i++) {
			if (i !== idx) {
				newDt.items.add(dtUpload.files[i]);
			}
		}
		dtUpload = newDt;
		var hiddenInput = document.getElementById('upload_spk');
		if (hiddenInput) {
			hiddenInput.files = dtUpload.files;
		}
		render_new_files_list();
	});

	// Hapus file existing
	$(document).on('click', '.remove-existing-file', function(e) {
		e.preventDefault();
		$(this).closest('.file-item-badge').fadeOut(200, function() {
			$(this).remove();
		});
	});

	// Reset error border on COA change
	$(document).on('change', '.wajib_coa', function() {
		if ($(this).val() != '') {
			$(this).css('border', '');
			$(this).next('.chosen-container').find('.chosen-single').css('border', '');
		}
	});

	$('#no_so').on('change', function(evt, params) {
		var data = $("select#no_so").find(":selected").data("project");
		$("#project_name").val(data);
	});

	$(document).on('change', '#sts_app', function(e) {
		var sts = $(this).val();
		if (sts == 'D') {
			$('.tnd_reason').show();
		} else {
			$('.tnd_reason').hide();
		}
	});

	$(document).on('click', '#back', function(e) {
		var app = $("#approve").val();
		var tingkat_approval = $('#tingkat_approval').val();
		var tanda = "";
		if (app == 'approve') {
			if (tingkat_approval == '1') {
				var tanda = 'approval_head';
			}
			if (tingkat_approval == '2') {
				var tanda = 'approval_cost_control';
			}
			if (tingkat_approval == '3') {
				var tanda = 'approval_management';
			}
		}
		window.location.href = base_url + active_controller + tanda;
	});

	$(document).on('click', '.addPart', function() {
		var get_id = $(this).parent().parent().attr('id');
		var split_id = get_id.split('_');
		var id = parseInt(split_id[1]) + 1;
		var id_bef = split_id[1];

		$.ajax({
			url: base_url + active_controller + '/get_add/' + id,
			cache: false,
			type: "POST",
			dataType: "json",
			success: function(data) {
				$("#add_" + id_bef).before(data.header);
				$("#add_" + id_bef).remove();
				$('.chosen_select').chosen({
					width: '100%'
				});
				$('.maskM').autoNumeric();
				$('.datepicker').datepicker({
					dateFormat: 'yy-mm-dd',
					//minDate: 0
				});
				Swal.close();
			},
			error: function() {
				Swal.fire({
					title: "Error Message !",
					text: 'Connection Time Out. Please try again..',
					icon: "warning",
					timer: 3000,
					showCancelButton: false,
					showConfirmButton: false,
					allowOutsideClick: false
				});
			}
		});
	});

	// delete part
	$(document).on('click', '.delPart', function() {
		var get_id = $(this).parent().parent().attr('class');
		$("." + get_id).remove();
		calculate_all_total();
	});

	$(document).on('keyup', '.sum_tot', function() {
		var id = $(this).attr('id');
		var det_id = id.split('_');
		var a = det_id[1];
		sum_total(a);
	});

	// SAVE
	$(document).on('click', '#save', function(e) {
		e.preventDefault();
		$('#save').prop('disabled', true);

		var tingkat_approval = $('#tingkat_approval').val();
		var id_dept = $('#id_dept').val();
		var sts_app = $('#sts_app').val();
		var app = $("#approve").val();

		if (id_dept == '0') {
			Swal.fire({
				title: "Error Message!",
				text: 'Department name empty, select first ...',
				icon: "warning"
			});
			$('#save').prop('disabled', false);
			return false;
		}

		// Validasi item barang - hanya untuk mode input (bukan approve/view)
		if (app == '' || app == null) {
			var totalItems = $("tr[class^='header_']").length;
			if (totalItems == 0) {
				Swal.fire({
					title: "Peringatan!",
					text: 'Item barang harus diisi minimal 1 item sebelum menyimpan.',
					icon: "warning"
				});
				$('#save').prop('disabled', false);
				return false;
			}

			var itemValid = true;
			var pesanError = '';

			// Reset styling error border
			$('.wajib_coa').each(function() {
				$(this).css('border', '');
				$(this).next('.chosen-container').find('.chosen-single').css('border', '');
			});

			$("tr[class^='header_']").each(function(index) {
				var nomorItem = index + 1;
				var nmBarang = $(this).find("textarea[name*='[nm_barang]']").val();
				var coaSelect = $(this).find("select[name*='[coa]']");
				var coaVal = coaSelect.val();
				var qtyVal = $(this).find("input[name*='[qty]']").val();
				var hargaVal = $(this).find("input[name*='[harga]']").val();

				var qty = 0;
				var harga = 0;
				if (qtyVal != null && qtyVal != '') {
					qty = parseFloat(qtyVal.toString().split(",").join("").split(" ").join(""));
				}
				if (hargaVal != null && hargaVal != '') {
					harga = parseFloat(hargaVal.toString().split(",").join("").split(" ").join(""));
				}

				if (nmBarang == null || nmBarang.trim() == '') {
					itemValid = false;
					pesanError = 'Nama Barang/Jasa pada item ke-' + nomorItem + ' harus diisi.';
					return false;
				}
				if (coaVal == null || coaVal == '' || coaVal == '0') {
					itemValid = false;
					pesanError = 'COA pada item ke-' + nomorItem + ' wajib dipilih!';
					coaSelect.css('border', '1px solid red');
					coaSelect.next('.chosen-container').find('.chosen-single').css('border', '1px solid red');
					return false;
				}
				if (isNaN(qty) || qty <= 0) {
					itemValid = false;
					pesanError = 'Qty pada item ke-' + nomorItem + ' harus diisi dan lebih dari 0.';
					return false;
				}
				if (isNaN(harga) || harga <= 0) {
					itemValid = false;
					pesanError = 'Est Harga pada item ke-' + nomorItem + ' harus diisi dan lebih dari 0.';
					return false;
				}
			});

			if (!itemValid) {
				Swal.fire({
					title: "Peringatan!",
					text: pesanError,
					icon: "warning"
				});
				$('#save').prop('disabled', false);
				return false;
			}
		}

		var tanda = "";
		if (app == 'approve') {
			if (sts_app == '0') {
				Swal.fire({
					title: "Error Message!",
					text: 'Status Approve empty, select first ...',
					icon: "warning",
					showCancelButton: false,
					showConfirmButton: false,
					allowOutsideClick: false,
					timer: 2000
				});
				$('#save').prop('disabled', false);
				return false;
			}
		}

		let wajib;
		let FALIDASIwajib = true;
		$(".wajib").each(function() {
			satuan = $(this).val();
			if (satuan == '' || satuan == '0') {
				FALIDASIwajib = false;
				return false;
			}
		});
		if (FALIDASIwajib === false) {
			Swal.fire({
				title: "Error Message!",
				text: 'Satuan wajib diisi !',
				icon: "warning",
				showCancelButton: false,
				showConfirmButton: false,
				allowOutsideClick: false,
				timer: 2000
			});
			$('#save').prop('disabled', false);
			return false;
		}

		let tgl_butuh;
		let FALIDASI = true;
		$(".tgl_dibutuhkan").each(function() {
			tgl_butuh = $(this).val();
			if (tgl_butuh == '' || tgl_butuh == '0000-00-00') {
				FALIDASI = false;
				return false;
			}
		});
		if (FALIDASI === false) {
			Swal.fire({
				title: "Error Message!",
				text: 'Tgl dibutuhkan wajib diisi !',
				icon: "warning",
				showCancelButton: false,
				showConfirmButton: false,
				allowOutsideClick: false,
				timer: 2000
			});
			$('#save').prop('disabled', false);
			return false;
		}

		$('#save').prop('disabled', true);

		Swal.fire({
			title: "Are you sure?",
			text: "Save this data ?",
			icon: "warning",
			showCancelButton: true,
			confirmButtonClass: "btn-danger",
			confirmButtonText: "Yes, Process it!",
			cancelButtonText: "No, cancel process!",
			closeOnConfirm: true,
			closeOnCancel: false
		}).then((isConfirm) => {
			if (isConfirm.isConfirmed) {
				var formData = new FormData($('#form_ct')[0]);
				var baseurl = base_url + active_controller + '/add';
				$.ajax({
					url: baseurl,
					type: "POST",
					data: formData,
					cache: false,
					dataType: 'json',
					processData: false,
					contentType: false,
					success: function(data) {
						if (data.status == 1) {
							Swal.fire({
								title: "Save Success!",
								text: data.pesan,
								icon: "success",
								timer: 3000,
								showCancelButton: false,
								showConfirmButton: false,
								allowOutsideClick: false
							}).then((next) => {
								var return_link = '';
								if (tingkat_approval == '1') {
									return_link = 'approval_head';
								}
								if (tingkat_approval == '2') {
									return_link = 'approval_cost_control';
								}
								if (tingkat_approval == '3') {
									return_link = 'approval_management';
								}
								window.location.href = base_url + active_controller + return_link;
							});
						} else if (data.status == 0) {
							Swal.fire({
								title: "Save Failed!",
								text: data.pesan,
								icon: "warning",
								timer: 3000,
								showCancelButton: false,
								showConfirmButton: false,
								allowOutsideClick: false
							}).then((next) => {
								$('#save').prop('disabled', false);
							});
						}
					},
					error: function() {
						Swal.fire({
							title: "Error Message !",
							text: 'An Error Occured During Process. Please try again..',
							icon: "warning",
							timer: 3000,
							showCancelButton: false,
							showConfirmButton: false,
							allowOutsideClick: false
						}).then(next => {
							$('#save').prop('disabled', false);
						});
					}
				});
			} else {
				Swal.fire("Cancelled", "Data can be process again :)", "error");
				$('#save').prop('disabled', false);
				return false;
			}
		});
	});

	$(document).on('click', '.edit_detail', function() {
		var id = $(this).data('id');
		var nomor = $(this).data('nomor');

		var nm_barang = $('.nm_barang_' + nomor).val();
		var spec = $('.spec_' + nomor).val();
		var qty = $('.qty_' + nomor).val();
		var satuan = $('.satuan_' + nomor).val();
		var coa = $('.coa_' + nomor).val();
		var harga = $('.harga_' + nomor).val();
		var total_harga = $('.total_harga_' + nomor).val();
		var tanggal = $('.tanggal_' + nomor).val();
		var keterangan = $('.keterangan_' + nomor).val();

		if (coa == '' || coa == null) {
			Swal.fire({
				title: 'Peringatan!',
				text: 'COA wajib dipilih sebelum update item!',
				icon: 'warning'
			});
			return false;
		}

		if (qty == '' || qty == null) {
			qty = 0;
		} else {
			qty = qty.split(',').join('');
			qty = parseFloat(qty);
		}
		if (harga == '' || harga == null) {
			harga = 0;
		} else {
			harga = harga.split(',').join('');
			harga = parseFloat(harga);
		}
		if (total_harga == '' || total_harga == null) {
			total_harga = 0;
		} else {
			total_harga = total_harga.split(',').join('');
			total_harga = parseFloat(total_harga);
		}

		$.ajax({
			type: 'POST',
			url: siteurl + active_controller + '/edit_detail',
			data: {
				'id': id,
				'nm_barang': nm_barang,
				'spec': spec,
				'qty': qty,
				'satuan': satuan,
				'coa': coa,
				'harga': harga,
				'total_harga': total_harga,
				'tanggal': tanggal,
				'keterangan': keterangan
			},
			cache: false,
			dataType: 'json',
			beforeSend: function(result) {
				$('.edit_detail_' + nomor).html('<i class="fa fa-spin fa-spinner"></i>');
				$('.edit_detail_' + nomor).prop('disabled', true);
			},
			success: function(result) {
				if (result.status == 1) {
					Swal.fire({
						title: 'Success !',
						text: 'Success, item data has been updated !',
						icon: 'success',
						timer: 2000,
						showCancelButton: false,
						showConfirmButton: false,
						allowOutsideClick: false
					}).then((next) => {
						location.reload();
					});
				} else {
					Swal.fire({
						title: 'Failed !',
						text: 'Failed, item data has not been updated !',
						icon: 'error',
						timer: 2000,
						showCancelButton: false,
						showConfirmButton: false,
						allowOutsideClick: false
					}).then((next) => {
						location.reload();
					});
				}
			},
			error: function(result) {
				Swal.fire({
					title: 'Failed !',
					icon: 'error',
					timer: 2000,
					showCancelButton: false,
					allowOutsideClick: false
				}).then((next) => {
					location.reload();
				});
			}
		});
	});

	function sum_total(a) {
		var qty = getNum($('#qty_' + a).val().split(",").join(""));
		var harga = getNum($('#harga_' + a).val().split(",").join(""));
		var total = qty * harga;
		$('#total_harga_' + a).val(number_format(total));
		calculate_all_total();
	}

	function calculate_all_total() {
		var SUM = 0;
		$(".jumlah_all").each(function() {
			SUM += Number(getNum($(this).val().split(",").join("")));
		});
		$('#budget').val(number_format(SUM));
		$('#total_pr_display').text('Rp ' + number_format(SUM));
	}

	function number_format(number, decimals, dec_point, thousands_sep) {
		number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
		var n = !isFinite(+number) ? 0 : +number,
			prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
			sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
			dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
			s = '',
			toFixedFix = function(n, prec) {
				var k = Math.pow(10, prec);
				return '' + Math.round(n * k) / k;
			};
		s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
		if (s[0].length > 3) {
			s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
		}
		if ((s[1] || '').length < prec) {
			s[1] = s[1] || '';
			s[1] += new Array(prec - s[1].length + 1).join('0');
		}
		return s.join(dec);
	}
</script>