<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/plugins/datepicker/datepicker3.css') ?>">

<style>
	.btn-flat-custom { border-radius: 4px; }
	.box-custom {
		background: #fff;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		box-shadow: 0 2px 10px rgba(0,0,0,0.04);
		margin-bottom: 20px;
	}
	.box-custom-header {
		padding: 15px 20px;
		border-bottom: 1px solid #edf2f7;
		display: flex;
		justify-content: space-between;
		align-items: center;
	}
	.box-custom-title {
		font-size: 16px;
		font-weight: 700;
		color: #2d3748;
		margin: 0;
	}
	.table-expense thead th {
		background-color: #3c8dbc;
		color: #fff;
		text-align: center;
		vertical-align: middle !important;
		font-size: 13px;
		border: 1px solid #367fa9 !important;
	}
	.table-expense tbody td {
		vertical-align: middle !important;
	}
	.badge-expense {
		background-color: #00a65a;
		color: #fff;
		font-size: 11px;
		padding: 4px 8px;
		border-radius: 4px;
		font-weight: 600;
		display: inline-block;
	}
	.summary-field {
		font-weight: bold;
		font-size: 14px;
		text-align: right;
	}
</style>

<?php
$datauser = $this->db->get_where('users', ['id_user' => $this->auth->user_id()])->row();
$dept = !empty($datauser) ? $datauser->department_id : '';
$bank_id = '';
$accnumber = '';
$accname = '';
if (!empty($datauser) && !empty($datauser->employee_id)) {
	$datadept = $this->db->get_where('employee', ['id' => $datauser->employee_id])->row();
	if (!empty($datadept)) {
		$bank_id = $datadept->bank_id;
		$accnumber = $datadept->accnumber;
		$accname = $datadept->accname;
	}
}

$id = (isset($data->id)) ? $data->id : '';
$pre_id = (isset($pre_id)) ? $pre_id : '';
$no_doc_val = (isset($data->no_doc) && !empty($data->no_doc)) ? $data->no_doc : $pre_id;
$tgl_doc = (isset($data->tgl_doc)) ? $data->tgl_doc : date('Y-m-d');
$keterangan = (isset($data->informasi)) ? $data->informasi : '';
$bank_id = (isset($data->bank_id) && $data->bank_id !== '') ? $data->bank_id : $bank_id;
$accnumber = (isset($data->accnumber) && $data->accnumber !== '') ? $data->accnumber : $accnumber;
$accname = (isset($data->accname) && $data->accname !== '') ? $data->accname : $accname;
$pettycash = (isset($data->pettycash)) ? $data->pettycash : '';
$stsview = (isset($stsview)) ? $stsview : '';
$option_coa = (isset($option_coa) && is_array($option_coa)) ? $option_coa : [];
$datacoa = '';
foreach ($option_coa as $keys => $val) {
	$datacoa .= '<option value="' . $keys . '">' . $val . '</option>';
}
?>

<?= form_open_multipart($this->uri->segment(1) . '/save', array('id' => 'frm_data', 'name' => 'frm_data', 'role' => 'form', 'class' => 'form-horizontal')) ?>
<input type="hidden" id="id" name="id" value="<?php echo $id; ?>">
<input type="hidden" id="pettycash" name="pettycash" value="<?php echo $pettycash; ?>">
<input type="hidden" id="nama" name="nama" value="<?php echo (isset($data->nama) ? $data->nama : $this->auth->user_name()); ?>">
<input type="hidden" id="departement" name="departement" value="<?php echo (isset($data->departement) ? $data->departement : $dept); ?>">

<div class="box box-custom">
	<div class="box-custom-header">
		<h4 class="box-custom-title">
			<i class="fa fa-pencil-square-o text-success"></i> Form Pengajuan Expense Langsung (Direct Expense)
		</h4>
		<div>
			<a class="btn btn-default btn-sm btn-flat-custom" onclick="window.location.reload();return false;">
				<i class="fa fa-reply"></i> Kembali
			</a>
		</div>
	</div>

	<div class="box-body" style="padding: 20px;">
		<div class="form-horizontal">
			<!-- INFORMASI EXPENSE -->
			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label class="col-sm-3 control-label">No. Dokumen</label>
						<div class="col-sm-9">
							<input type="text" class="form-control input-sm" id="no_doc" name="no_doc" value="<?php echo $no_doc_val; ?>" placeholder="Otomatis (System)" readonly>
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-3 control-label">Tanggal <b class="text-red">*</b></label>
						<div class="col-sm-9">
							<input type="text" class="form-control tanggal input-sm" id="tgl_doc" name="tgl_doc" value="<?php echo $tgl_doc; ?>" required placeholder="YYYY-MM-DD">
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-3 control-label">Keterangan <b class="text-red">*</b></label>
						<div class="col-sm-9">
							<textarea class="form-control input-sm" id="informasi" name="informasi" rows="3" required placeholder="Tuliskan keterangan pengeluaran..."><?php echo $keterangan; ?></textarea>
							<?php
							if (isset($data->st_reject) && !empty($data->st_reject)) {
								echo '<div class="alert alert-danger" style="margin-top:5px; padding:6px 10px; font-size:12px;">
									<b><i class="fa fa-ban"></i> Catatan Reject:</b><br>' . $data->st_reject . '
								</div>';
							}
							?>
						</div>
					</div>
				</div>

				<div class="col-md-6">
					<div class="form-group">
						<label class="col-sm-3 control-label">Bank Penerima</label>
						<div class="col-sm-9">
							<input type="text" class="form-control input-sm" id="bank_id" name="bank_id" value="<?php echo $bank_id; ?>" placeholder="Nama Bank">
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-3 control-label">No. Rekening</label>
						<div class="col-sm-9">
							<input type="text" class="form-control input-sm" id="accnumber" name="accnumber" value="<?php echo $accnumber; ?>" placeholder="Nomor Rekening">
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-3 control-label">Atas Nama</label>
						<div class="col-sm-9">
							<input type="text" class="form-control input-sm" id="accname" name="accname" value="<?php echo $accname; ?>" placeholder="Nama Pemilik Rekening">
						</div>
					</div>
				</div>
			</div>

			<!-- TABEL RINCIAN ITEM -->
			<div style="margin-top: 25px;">
				<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
					<span style="font-size: 15px; font-weight: 700; color: #333;">
						<i class="fa fa-list-alt text-success"></i> Rincian Pengeluaran Barang & Jasa
					</span>
					<div class="stsview">
						<button type="button" class="btn btn-success btn-sm btn-flat-custom" onclick="add_detail()" id="add-material" title="Tambah Baris Pengeluaran Baru">
							<i class="fa fa-plus"></i> Tambah Baris Pengeluaran
						</button>
					</div>
				</div>

				<div class="table-responsive">
					<table class="table table-bordered table-expense" width="100%">
						<thead>
							<tr>
								<th width="35">#</th>
								<th width="80">Tipe</th>
								<th width="200">Akun COA / Jenis</th>
								<th width="110">Tanggal</th>
								<th width="220">Barang / Jasa</th>
								<th width="180">Spesifikasi</th>
								<th width="70">Qty</th>
								<th width="120">Harga Satuan</th>
								<th width="140">Total Nominal</th>
								<th width="170">Bon / Bukti</th>
								<th width="50">Aksi</th>
							</tr>
						</thead>
						<tbody id="detail_body">
							<?php
							$idd = 1;
							$total_expense = 0;
							if (!empty($data_detail)) {
								foreach ($data_detail as $record) {
									$row_expense_val = floatval($record->expense > 0 ? $record->expense : $record->total_harga);
									$total_expense += $row_expense_val;
									$row_files = isset($detail_files[$record->id]) ? $detail_files[$record->id] : [];
							?>
									<tr id='tr1_<?= $idd ?>' class='delAll'>
										<td class="text-center">
											<input type='hidden' name='id_kasbon[]' id='id_kasbon_<?= $idd ?>' value=''>
											<input type="hidden" name="filename[]" id="filename_<?= $idd ?>" value="<?= $record->doc_file; ?>">
											<input type="hidden" name="detail_id[]" id="raw_id_<?= $idd ?>" value="<?= $idd; ?>" class="dtlloop">
											<input type="hidden" name="id_detail[]" id="id_detail_<?= $idd ?>" value="<?= $record->id; ?>" class="dtlloop">
											<?= $idd ?>
										</td>
										<td class="text-center">
											<span class="badge-expense"><i class="fa fa-money"></i> Realisasi</span>
										</td>
										<td>
											<?= form_dropdown('coa[]', $option_coa, (isset($record->coa) ? $record->coa : ''), array('id' => 'coa' . $idd, 'required' => 'required', 'class' => 'form-control select2 input-sm')); ?>
										</td>
										<td>
											<input type="text" class="form-control tanggal input-sm" name="tanggal[]" id="tanggal<?= $idd; ?>" value="<?= $record->tanggal; ?>">
										</td>
										<td>
											<textarea class="form-control input-sm" name="deskripsi[]" id="deskripsi_<?= $idd; ?>" rows="2" style="font-size:13px;"><?= $record->deskripsi; ?></textarea>
										</td>
										<td>
											<textarea class="form-control input-sm" name="keterangan[]" id="keterangan_<?= $idd; ?>" rows="2" style="font-size:13px;"><?= $record->keterangan; ?></textarea>
										</td>
										<td><input type="text" class="form-control divide input-sm text-right" name="qty[]" id="qty_<?= $idd; ?>" value="<?= $record->qty; ?>" onblur="cektotal(<?= $idd; ?>)"></td>
										<td><input type="text" class="form-control divide input-sm text-right" name="harga[]" id="harga_<?= $idd; ?>" value="<?= $record->harga; ?>" onblur="cektotal(<?= $idd; ?>)"></td>
										<td>
											<input type="text" class="form-control divide subtotal input-sm text-right" name="expense[]" id="expense_<?= $idd; ?>" value="<?= $row_expense_val ?>" tabindex="-1" readonly style="font-weight:bold; color:#00a65a; background:#e8fadf;">
											<input type="hidden" class="subkasbon" name="kasbon[]" id="kasbon_<?= $idd; ?>" value="0">
										</td>
										<td class="text-center">
											<div class="file-upload-cell-<?= $idd ?>">
												<label class="btn btn-xs btn-primary btn-flat-custom stsview" style="cursor:pointer; margin-bottom:2px;" title="Pilih File Bon/Bukti">
													<i class="fa fa-folder-open"></i> Pilih File
													<input type="file" id="temp_file_<?= $idd ?>" class="temp-detail-file-picker" data-row="<?= $idd ?>" style="display:none;" accept=".jpg,.jpeg,.png,.pdf" multiple>
												</label>
												<input type="file" name="doc_files_<?= $idd ?>[]" id="doc_files_<?= $idd ?>" multiple style="display:none;">
												<div id="new_files_list_<?= $idd ?>" style="display:flex; flex-direction:column; gap:3px; margin-top:3px;"></div>

												<?php if (!empty($row_files)): ?>
													<div class="existing-files-row-<?= $idd ?>" style="margin-top:4px; display:flex; flex-direction:column; gap:3px;">
														<input type="hidden" name="has_existing_files_<?= $idd ?>" value="1">
														<?php foreach ($row_files as $rf): 
															$furl = base_url('assets/expense/' . $rf->doc_file);
															$is_fpdf = (stripos($rf->doc_file, '.pdf') !== false);
														?>
															<span class="badge file-badge-item" style="background:#3c8dbc; font-size:10px; font-weight:normal; text-align:left; padding:3px 5px; display:inline-flex; align-items:center; justify-content:space-between;">
																<a href="<?= $furl ?>" target="_blank" style="color:#fff; max-width:110px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; display:inline-block;" title="<?= htmlspecialchars($rf->doc_file) ?>">
																	<i class="fa <?= $is_fpdf ? 'fa-file-pdf-o' : 'fa-file-image-o' ?>"></i> <?= htmlspecialchars($rf->doc_file) ?>
																</a>
																<input type="hidden" name="existing_files_<?= $idd ?>[]" value="<?= htmlspecialchars($rf->id) ?>">
																<?php if ($stsview != 'view' && $stsview != 'approval'): ?>
																	<button type="button" class="btn btn-xs btn-danger remove-existing-detail-file" style="padding:0 3px; font-size:9px; line-height:1; margin-left:4px;" title="Hapus file ini">
																		<i class="fa fa-times"></i>
																	</button>
																<?php endif; ?>
															</span>
														<?php endforeach; ?>
													</div>
												<?php endif; ?>
											</div>
										</td>
										<td class="text-center">
											<button type='button' class='btn btn-danger btn-xs stsview' data-toggle='tooltip' onClick='delDetail(<?= $idd ?>)' title='Hapus'><i class='fa fa-trash'></i></button>
										</td>
									</tr>
							<?php
									$idd++;
								}
							}
							?>
						</tbody>
						<tfoot>
							<tr style="background:#f8fafc; font-weight:bold;">
								<td colspan="8" align="right">TOTAL PENGELUARAN EXPENSE</td>
								<td colspan="3">
									<input type="text" class="form-control divide input-sm summary-field" id="total_expense" name="total_expense" value="<?= $total_expense ?>" placeholder="0" tabindex="-1" readonly style="background:#ffffff; color:#333;">
									<input type="hidden" id="total_kasbon" name="total_kasbon" value="0">
									<input type="hidden" id="grand_total" name="grand_total" value="<?= -$total_expense ?>">
								</td>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>

			<!-- FOOTER CONTROLS -->
			<div style="border-top: 2px solid #f4f4f4; padding-top: 15px; margin-top: 20px;">
				<div class="text-right">
					<?php
					if (isset($data)) {
						if ($data->status == 0) {
							if ($stsview == 'approval') {
								echo '<a class="btn btn-warning btn-sm btn-flat-custom" onclick="data_approve()" style="margin-right:5px;"><i class="fa fa-check-square-o">&nbsp;</i>Setujui (Approve)</a>';
								echo '<a class="btn btn-danger btn-sm btn-flat-custom" onclick="data_reject()" style="margin-right:5px;"><i class="fa fa-ban">&nbsp;</i>Tolak (Reject)</a>';
							}
						}
					}
					?>
					<button type="submit" name="save" class="btn btn-success btn-sm btn-flat-custom stsview" id="submit" style="margin-right:5px;">
						<i class="fa fa-save">&nbsp;</i> Simpan Expense
					</button>
					<a class="btn btn-default btn-sm btn-flat-custom" onclick="window.location.reload();return false;">
						<i class="fa fa-reply">&nbsp;</i> Kembali / Batal
					</a>
				</div>
			</div>
		</div>
	</div>
</div>
<?= form_close() ?>

<!-- Plugins JS -->
<script src="<?= base_url('assets/plugins/datepicker/bootstrap-datepicker.js') ?>"></script>
<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/select2/select2.full.min.js') ?>"></script>

<script type="text/javascript">
	var stsview = "<?= $stsview ?>";

	$(document).ready(function() {
		$('.select2').select2({ width: '100%' });
		$(".divide").divide();
		$(".tanggal").datepicker({
			todayHighlight: true,
			format: "yyyy-mm-dd",
			showInputs: true,
			autoclose: true
		});

		if (stsview == 'view' || stsview == 'approval') {
			$(".stsview").hide();
			$("#frm_data input:not([type=hidden]), #frm_data textarea, #frm_data select").prop("disabled", true);
		}

		if ($("#detail_body tr").length === 0) {
			add_detail();
		}
	});

	function add_detail() {
		var nomor = $("#detail_body tr").length + 1;
		var datacoa = <?= json_encode($datacoa) ?>;
		var Rows = "<tr id='tr1_" + nomor + "' class='delAll'>";
		Rows += "<td class='text-center'><input type='hidden' name='id_kasbon[]' id='id_kasbon_" + nomor + "' value=''>";
		Rows += "<input type='hidden' name='detail_id[]' id='raw_id_" + nomor + "' value='" + nomor + "' class='dtlloop'>";
		Rows += "<input type='hidden' name='id_detail[]' id='id_detail_" + nomor + "' value='" + nomor + "' class='dtlloop'>";
		Rows += nomor + "</td>";
		Rows += "<td class='text-center'><span class='badge-expense'><i class='fa fa-money'></i> Realisasi</span></td>";
		Rows += "<td>";
		Rows += "<select name='coa[]' id='coa_" + nomor + "' required='required' class='form-control select2 input-sm'>" + datacoa + "</select>";
		Rows += "</td>";
		Rows += "<td>";
		Rows += "<input type='text' class='form-control tanggal input-sm' placeholder='YYYY-MM-DD' name='tanggal[]' id='tanggal_" + nomor + "' value='<?= date("Y-m-d") ?>' />";
		Rows += "</td>";
		Rows += "<td>";
		Rows += "<textarea class='form-control input-sm' placeholder='Nama barang/jasa...' name='deskripsi[]' id='deskripsi_" + nomor + "' rows='2' style='font-size:13px;'></textarea>";
		Rows += "<input type='hidden' class='form-control input-sm' name='id_expense_detail[]' id='id_expense_detail_" + nomor + "' value='' />";
		Rows += "</td>";
		Rows += "<td>";
		Rows += "<textarea class='form-control input-sm' placeholder='Rincian / spesifikasi...' name='keterangan[]' id='keterangan_" + nomor + "' rows='2' style='font-size:13px;'></textarea>";
		Rows += "</td>";
		Rows += "<td>";
		Rows += "<input type='text' class='form-control divide input-sm text-right' name='qty[]' id='qty_" + nomor + "' value='1' onblur='cektotal(" + nomor + ")'/>";
		Rows += "</td>";
		Rows += "<td>";
		Rows += "<input type='text' class='form-control divide input-sm text-right' name='harga[]' id='harga_" + nomor + "' value='0' onblur='cektotal(" + nomor + ")' />";
		Rows += "</td>";
		Rows += "<td>";
		Rows += "<input type='text' class='form-control divide subtotal input-sm text-right' name='expense[]' id='expense_" + nomor + "' value='0' tabindex='-1' readonly style='font-weight:bold; color:#00a65a; background:#e8fadf;' />";
		Rows += "<input type='hidden' class='subkasbon' name='kasbon[]' id='kasbon_" + nomor + "' value='0' />";
		Rows += "</td>";
		Rows += "<td class='text-center'>";
		Rows += "<div class='file-upload-cell-" + nomor + "'>";
		Rows += "<label class='btn btn-xs btn-primary btn-flat-custom' style='cursor:pointer; margin-bottom:2px;' title='Pilih File Bon/Bukti'>";
		Rows += "<i class='fa fa-folder-open'></i> Pilih File";
		Rows += "<input type='file' id='temp_file_" + nomor + "' class='temp-detail-file-picker' data-row='" + nomor + "' style='display:none;' accept='.jpg,.jpeg,.png,.pdf' multiple>";
		Rows += "</label>";
		Rows += "<input type='file' name='doc_files_" + nomor + "[]' id='doc_files_" + nomor + "' multiple style='display:none;'>";
		Rows += "<div id='new_files_list_" + nomor + "' style='display:flex; flex-direction:column; gap:3px; margin-top:3px;'></div>";
		Rows += "</div>";
		Rows += "</td>";
		Rows += "<td class='text-center'>";
		Rows += "<button type='button' class='btn btn-danger btn-xs' data-toggle='tooltip' onClick='delDetail(" + nomor + ")' title='Hapus'><i class='fa fa-trash'></i></button>";
		Rows += "</td>";
		Rows += "</tr>";

		$('#detail_body').append(Rows);
		$("#tanggal_" + nomor).focus();
		$(".tanggal").datepicker({
			todayHighlight: true,
			format: "yyyy-mm-dd",
			showInputs: true,
			autoclose: true
		});
		$('.select2').select2({ width: '100%' });
		$(".divide").divide();
		cektotal();
	}

	function delDetail(row) {
		$('#tr1_' + row).remove();
		$('#detail_body tr').each(function(index) {
			$(this).find('td:first').contents().filter(function() {
				return this.nodeType === 3;
			}).remove();
			$(this).find('td:first').append((index + 1));
		});
		cektotal();
	}

	function cektotal(id) {
		if (id !== undefined) {
			var qty = parseFloat($("#qty_" + id).val().replace(/,/g, '')) || 0;
			var harga = parseFloat($("#harga_" + id).val().replace(/,/g, '')) || 0;
			var total = qty * harga;
			$("#expense_" + id).val(total);
		}

		var total_expense = 0;
		$(".subtotal").each(function() {
			total_expense += parseFloat($(this).val().replace(/,/g, '')) || 0;
		});
		$("#total_expense").val(total_expense);
		$(".divide").divide();
	}

	// ==========================================
	// DETAIL MULTI-FILE ACCUMULATOR (DataTransfer)
	// ==========================================
	var dtDetailMap = {};

	$(document).on('change', '.temp-detail-file-picker', function() {
		var row = $(this).data('row');
		if (!dtDetailMap[row]) {
			dtDetailMap[row] = new DataTransfer();
		}
		var dt = dtDetailMap[row];
		var newFiles = this.files;
		if (newFiles.length > 0) {
			for (var i = 0; i < newFiles.length; i++) {
				var file = newFiles[i];
				var exists = false;
				for (var j = 0; j < dt.items.length; j++) {
					var existing = dt.items[j].getAsFile();
					if (existing && existing.name === file.name && existing.size === file.size) {
						exists = true;
						break;
					}
				}
				if (!exists) {
					dt.items.add(file);
				}
			}
			var hiddenInput = document.getElementById('doc_files_' + row);
			if (hiddenInput) {
				hiddenInput.files = dt.files;
			}
			$(this).val('');
			render_detail_files(row);
		}
	});

	function render_detail_files(row) {
		var container = $('#new_files_list_' + row);
		container.empty();
		if (!dtDetailMap[row]) return;
		var dt = dtDetailMap[row];
		for (var i = 0; i < dt.files.length; i++) {
			var file = dt.files[i];
			var isPdf = file.name.toLowerCase().endsWith('.pdf');
			var sizeKb = Math.round(file.size / 1024);
			var badge = '<span class="badge" style="background:#00a65a; font-size:10px; font-weight:normal; text-align:left; padding:3px 5px; display:inline-flex; align-items:center; justify-content:space-between;">' +
				'<span style="max-width:110px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="' + file.name + ' (' + sizeKb + ' KB)">' +
				'<i class="fa ' + (isPdf ? 'fa-file-pdf-o' : 'fa-file-image-o') + '"></i> ' + file.name +
				'</span>' +
				'<button type="button" class="btn btn-xs btn-danger remove-new-detail-file" data-row="' + row + '" data-index="' + i + '" style="padding:0 3px; font-size:9px; line-height:1; margin-left:4px;" title="Hapus file ini">' +
				'<i class="fa fa-times"></i>' +
				'</button>' +
				'</span>';
			container.append(badge);
		}
	}

	$(document).on('click', '.remove-new-detail-file', function(e) {
		e.preventDefault();
		var row = $(this).data('row');
		var idx = parseInt($(this).data('index'));
		if (dtDetailMap[row]) {
			var dt = dtDetailMap[row];
			var newDt = new DataTransfer();
			for (var i = 0; i < dt.files.length; i++) {
				if (i !== idx) {
					newDt.items.add(dt.files[i]);
				}
			}
			dtDetailMap[row] = newDt;
			var hiddenInput = document.getElementById('doc_files_' + row);
			if (hiddenInput) {
				hiddenInput.files = newDt.files;
			}
			render_detail_files(row);
		}
	});

	$(document).on('click', '.remove-existing-detail-file', function(e) {
		e.preventDefault();
		$(this).closest('.file-badge-item').remove();
	});

	// SUBMIT FORM
	$('#frm_data').on('submit', function(e) {
		e.preventDefault();
		var errors = "";
		if ($("#informasi").val() == "") errors = "Keterangan tidak boleh kosong";
		if ($("#tgl_doc").val() == "") errors = "Tanggal Dokumen tidak boleh kosong";
		if ($("#detail_body tr").length == 0) errors = "Rincian barang/jasa pengeluaran belum diisi";

		if (errors != "") {
			Swal.fire({ title: "Perhatian!", text: errors, icon: "warning" });
			return false;
		}

		Swal.fire({
			title: "Simpan Expense?",
			text: "Pastikan seluruh data dan bukti pengeluaran sudah benar!",
			icon: "question",
			showCancelButton: true,
			confirmButtonText: "Ya, Simpan!",
			cancelButtonText: "Batal"
		}).then((result) => {
			if (result.isConfirmed) {
				var formdata = new FormData($('#frm_data')[0]);
				$.ajax({
					url: siteurl + 'expense/save',
					dataType: "json",
					type: 'POST',
					data: formdata,
					processData: false,
					contentType: false,
					success: function(msg) {
						if (msg.save == '1') {
							Swal.fire({
								title: "Berhasil!",
								text: "Dokumen Expense berhasil disimpan.",
								icon: "success"
							}).then(() => {
								window.location.reload();
							});
						} else {
							Swal.fire({
								title: "Gagal!",
								text: msg.message || "Gagal menyimpan dokumen.",
								icon: "error"
							});
						}
					},
					error: function() {
						Swal.fire({
							title: "Gagal!",
							text: "Terjadi kesalahan pada server saat menyimpan.",
							icon: "error"
						});
					}
				});
			}
		});
	});

	function data_approve() {
		Swal.fire({
			title: "Setujui Expense?",
			text: "Dokumen akan diproses ke tahap persetujuan!",
			icon: "question",
			showCancelButton: true,
			confirmButtonText: "Ya, Setujui!",
			cancelButtonText: "Batal",
			confirmButtonColor: "#28a745"
		}).then((res) => {
			if (res.isConfirmed) {
				var id = $("#id").val();
				$.post(siteurl + 'expense/approve/' + id, function(result) {
					if (result.save) {
						Swal.fire("Sukses!", "Expense berhasil disetujui.", "success").then(() => {
							window.location.reload();
						});
					} else {
						Swal.fire("Gagal!", "Gagal memproses approval.", "error");
					}
				}, "json");
			}
		});
	}

	function data_reject() {
		Swal.fire({
			title: "Tolak Dokumen Expense",
			input: "textarea",
			inputLabel: "Alasan Penolakan (Reject Reason)",
			inputPlaceholder: "Tuliskan alasan penolakan di sini...",
			showCancelButton: true,
			confirmButtonText: "Tolak Dokumen",
			cancelButtonText: "Batal",
			confirmButtonColor: "#d33",
			inputValidator: (value) => {
				if (!value) {
					return "Alasan penolakan wajib diisi!";
				}
			}
		}).then((res) => {
			if (res.isConfirmed) {
				var id = $("#id").val();
				$.post(siteurl + 'expense/reject', { id: id, reason: res.value }, function(result) {
					Swal.fire("Ditolak!", "Dokumen telah ditolak.", "info").then(() => {
						window.location.reload();
					});
				}, "json");
			}
		});
	}
</script>
