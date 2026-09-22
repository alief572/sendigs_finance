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
	.table-expense-report thead th {
		color: #fff;
		text-align: center;
		vertical-align: middle !important;
		font-size: 12px;
	}
	.table-expense-report tbody td {
		vertical-align: middle !important;
	}
	.badge-kasbon {
		background-color: #3c8dbc;
		color: #fff;
		font-size: 11px;
		padding: 4px 8px;
		border-radius: 4px;
		font-weight: 600;
		display: inline-block;
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
$bank_id = (isset($data->bank_id) && $data->bank_id !== '') ? $data->bank_id : ((isset($data_kasbon->bank_id) && $data_kasbon->bank_id !== '') ? $data_kasbon->bank_id : $bank_id);
$accnumber = (isset($data->accnumber) && $data->accnumber !== '') ? $data->accnumber : ((isset($data_kasbon->accnumber) && $data_kasbon->accnumber !== '') ? $data_kasbon->accnumber : $accnumber);
$accname = (isset($data->accname) && $data->accname !== '') ? $data->accname : ((isset($data_kasbon->accname) && $data_kasbon->accname !== '') ? $data_kasbon->accname : $accname);
$pettycash = (isset($data->pettycash)) ? $data->pettycash : '';
$no_doc_kasbon = (isset($data->id_kasbon) && !empty($data->id_kasbon)) ? $data->id_kasbon : (isset($data_kasbon->no_doc) ? $data_kasbon->no_doc : '');
$stsview = (isset($stsview)) ? $stsview : '';
$option_coa = (isset($option_coa) && is_array($option_coa)) ? $option_coa : [];
$list_bank = (isset($list_bank)) ? $list_bank : [];
$datacoa = '';
foreach ($option_coa as $keys => $val) {
	$datacoa .= '<option value="' . $keys . '">' . $val . '</option>';
}
?>

<?= form_open_multipart($this->uri->segment(1) . '/save', array('id' => 'frm_data', 'name' => 'frm_data', 'role' => 'form', 'class' => 'form-horizontal')) ?>
<input type="hidden" id="id" name="id" value="<?php echo $id; ?>">
<input type="hidden" id="pettycash" name="pettycash" value="<?php echo $pettycash; ?>">
<input type="hidden" id="nama" name="nama" value="<?php echo (isset($data->nama) ? $data->nama : (isset($data_kasbon->nama) ? $data_kasbon->nama : $this->auth->user_name())); ?>">
<input type="hidden" id="departement" name="departement" value="<?php echo (isset($data->departement) ? $data->departement : (isset($data_kasbon->departement) ? $data_kasbon->departement : $dept)); ?>">
<input type="hidden" id="no_doc_kasbon" name="no_doc_kasbon" value="<?= $no_doc_kasbon ?>">

<?php include __DIR__ . '/reject_card.php'; ?>

<div class="box box-custom">
	<div class="box-custom-header">
		<h4 class="box-custom-title">
			<i class="fa fa-ticket text-primary"></i> 
			<?= ($stsview == 'approval') ? 'Approval Pertanggungjawaban Kasbon (Expense Report)' : (($stsview == 'view') ? 'Detail Pertanggungjawaban Kasbon (Expense Report)' : 'Form Pertanggungjawaban Kasbon (Expense Report)') ?>
		</h4>
		<div>
			<a class="btn btn-default btn-sm btn-flat-custom" onclick="window.location.reload();return false;">
				<i class="fa fa-reply"></i> Kembali
			</a>
		</div>
	</div>

	<div class="box-body" style="padding: 20px;">
		<div class="form-horizontal">
			<!-- INFORMASI EXPENSE REPORT -->
			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label class="col-sm-3 control-label">No. Dokumen</label>
						<div class="col-sm-9">
							<input type="text" class="form-control input-sm" id="no_doc" name="no_doc" value="<?php echo $no_doc_val; ?>" placeholder="No. Dokumen Otomatis" readonly style="font-weight:bold; background:#f8fafc; color:#2d3748;">
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-3 control-label">No. Kasbon <b class="text-red">*</b></label>
						<div class="col-sm-9">
							<div style="display:flex; align-items:center; gap:8px;">
								<span class="badge bg-blue" style="font-size:13px; padding:6px 10px;"><i class="fa fa-ticket"></i> <?= $no_doc_kasbon ?></span>
								<?php if (isset($data_kasbon->keperluan)): ?>
									<small class="text-muted"><b>Keperluan:</b> <?= htmlspecialchars($data_kasbon->keperluan) ?></small>
								<?php endif; ?>
							</div>
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
							<textarea class="form-control input-sm" id="informasi" name="informasi" rows="2" required placeholder="Tuliskan keterangan laporan expense..."><?php echo $keterangan; ?></textarea>
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

			<!-- TABEL RINCIAN PERBANDINGAN KASBON VS REALISASI -->
			<div style="margin-top: 25px;">
				<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
					<span style="font-size: 15px; font-weight: 700; color: #333;">
						<i class="fa fa-balance-scale text-primary"></i> Rincian Kasbon vs Realisasi Pengeluaran
					</span>
				</div>

				<div class="table-responsive">
					<table class="table table-bordered table-expense-report" width="100%">
						<thead>
							<tr>
								<th class="text-center" rowspan="2" width="30" style="background:#3c8dbc; border:1px solid #367fa9;">#</th>
								<th class="text-center" rowspan="2" width="180" style="background:#3c8dbc; border:1px solid #367fa9;">Akun COA</th>
								<th class="text-center" rowspan="2" width="200" style="background:#3c8dbc; border:1px solid #367fa9;">Barang / Jasa</th>
								<th class="text-center" rowspan="2" width="160" style="background:#3c8dbc; border:1px solid #367fa9;">Spesifikasi</th>
								<th class="text-center" rowspan="2" width="95" style="background:#3c8dbc; border:1px solid #367fa9;">Tanggal</th>
								<th class="text-center" colspan="3" style="background:#2e59d9; border:1px solid #224abe;">Kasbon Awal</th>
								<th class="text-center" colspan="3" style="background:#00a65a; border:1px solid #008d4c;">Realisasi Expense Report</th>
								<th class="text-center" rowspan="2" width="160" style="background:#3c8dbc; border:1px solid #367fa9;">Bon / Bukti</th>
							</tr>
							<tr>
								<th class="text-center" width="50" style="background:#4e73df; border:1px solid #3b64db;">Qty</th>
								<th class="text-center" width="95" style="background:#4e73df; border:1px solid #3b64db;">Harga</th>
								<th class="text-center" width="110" style="background:#4e73df; border:1px solid #3b64db;">Total</th>
								<th class="text-center" width="50" style="background:#28a745; border:1px solid #218838;">Qty</th>
								<th class="text-center" width="95" style="background:#28a745; border:1px solid #218838;">Harga</th>
								<th class="text-center" width="110" style="background:#28a745; border:1px solid #218838;">Total</th>
							</tr>
						</thead>
						<tbody id="detail_report_body">
							<?php
							$idd = 1;
							$total_kasbon = 0;
							$total_expense = 0;

							if (!empty($detail_items)) {
								foreach ($detail_items as $item) {
									$row_qty_kasbon = isset($item['qty_kasbon']) ? floatval($item['qty_kasbon']) : 1;
									$row_harga_kasbon = isset($item['harga_kasbon']) ? floatval($item['harga_kasbon']) : 0;
									$row_total_kasbon = isset($item['total_kasbon']) ? floatval($item['total_kasbon']) : ($row_qty_kasbon * $row_harga_kasbon);

									$row_qty_exp = isset($item['qty']) ? floatval($item['qty']) : $row_qty_kasbon;
									$row_harga_exp = isset($item['harga']) ? floatval($item['harga']) : $row_harga_kasbon;
									$row_total_exp = isset($item['expense']) ? floatval($item['expense']) : ($row_qty_exp * $row_harga_exp);

									$total_kasbon += $row_total_kasbon;
									$total_expense += $row_total_exp;

									$row_coa = !empty($item['coa']) ? $item['coa'] : '1304-01-01';
									$row_coa_name = !empty($item['coa_name']) ? $item['coa_name'] : 'Peralatan Kantor';
									$row_files = isset($detail_files[$item['id']]) ? $detail_files[$item['id']] : [];
							?>
									<tr id='tr1_<?= $idd ?>' class='delAll'>
										<td class="text-center">
											<input type='hidden' name='id_kasbon[]' id='id_kasbon_<?= $idd ?>' value='<?= $no_doc_kasbon ?>'>
											<input type="hidden" name="filename[]" id="filename_<?= $idd ?>" value="<?= isset($item['doc_file']) ? $item['doc_file'] : '' ?>">
											<input type="hidden" name="detail_id[]" id="raw_id_<?= $idd ?>" value="<?= $idd; ?>" class="dtlloop">
											<input type="hidden" name="id_detail[]" id="id_detail_<?= $idd ?>" value="<?= isset($item['id']) ? $item['id'] : $idd; ?>" class="dtlloop">
											<?= $idd ?>
										</td>
										<td>
											<input type="hidden" name="coa[]" id="coa_<?= $idd ?>" value="<?= $row_coa ?>">
											<div style="display:flex; flex-direction:column; gap:2px;">
												<span class="badge bg-blue" style="font-size:11px; text-align:left; white-space:normal;"><i class="fa fa-tag"></i> <?= $row_coa ?></span>
												<small class="text-muted" style="font-weight:600; font-size:11px;"><?= $row_coa_name ?></small>
											</div>
										</td>
										<td>
											<textarea class="form-control input-sm" name="deskripsi[]" id="deskripsi_<?= $idd; ?>" rows="2" style="font-size:12px;" onblur="set_jurnal()"><?= isset($item['deskripsi']) ? $item['deskripsi'] : '' ?></textarea>
											<input type="hidden" name="id_expense_detail[]" id="id_expense_detail_<?= $idd ?>" value="<?= isset($item['id_expense_detail']) ? $item['id_expense_detail'] : '' ?>">
										</td>
										<td>
											<textarea class="form-control input-sm" name="keterangan[]" id="keterangan_<?= $idd; ?>" rows="2" style="font-size:12px;"><?= isset($item['keterangan']) ? $item['keterangan'] : '' ?></textarea>
										</td>
										<td>
											<input type="text" class="form-control tanggal input-sm" name="tanggal[]" id="tanggal_<?= $idd; ?>" value="<?= isset($item['tanggal']) ? $item['tanggal'] : $tgl_doc ?>" onchange="set_jurnal()">
										</td>

										<!-- KASBON AWAL (READONLY) -->
										<td><input type="text" class="form-control divide input-sm text-right" name="qty_kasbon[]" value="<?= $row_qty_kasbon ?>" readonly tabindex="-1" style="background:#eaf2fd;"></td>
										<td><input type="text" class="form-control divide input-sm text-right" name="harga_kasbon[]" value="<?= $row_harga_kasbon ?>" readonly tabindex="-1" style="background:#eaf2fd;"></td>
										<td><input type="text" class="form-control divide subkasbon input-sm text-right" name="kasbon[]" id="kasbon_<?= $idd ?>" value="<?= $row_total_kasbon ?>" readonly tabindex="-1" style="font-weight:bold; color:#2e59d9; background:#eaf2fd;"></td>

										<!-- REALISASI EXPENSE REPORT (EDITABLE) -->
										<td><input type="text" class="form-control divide input-sm text-right" name="qty[]" id="qty_<?= $idd ?>" value="<?= $row_qty_exp ?>" onblur="hitung_report_row(<?= $idd ?>)"></td>
										<td><input type="text" class="form-control divide input-sm text-right" name="harga[]" id="harga_<?= $idd ?>" value="<?= $row_harga_exp ?>" onblur="hitung_report_row(<?= $idd ?>)"></td>
										<td><input type="text" class="form-control divide subtotal input-sm text-right" name="expense[]" id="expense_<?= $idd ?>" value="<?= $row_total_exp ?>" readonly tabindex="-1" style="font-weight:bold; color:#00a65a; background:#e8fadf;"></td>

										<!-- BON / BUKTI -->
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
																<a href="<?= $furl ?>" target="_blank" style="color:#fff; max-width:105px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; display:inline-block;" title="<?= htmlspecialchars($rf->doc_file) ?>">
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
									</tr>
							<?php
									$idd++;
								}
							}
							$grand_total = $total_kasbon - $total_expense;
							?>
						</tbody>
						<tfoot>
							<tr style="background:#eaf2fd; font-weight:bold;">
								<td colspan="7" align="right" style="color:#2e59d9;"><i class="fa fa-ticket"></i> TOTAL PENGAJUAN KASBON</td>
								<td colspan="5">
									<input type="text" class="form-control divide input-sm summary-field" id="total_kasbon" name="total_kasbon" value="<?= $total_kasbon ?>" placeholder="0" tabindex="-1" readonly style="color:#2e59d9; background:#ffffff;">
								</td>
							</tr>
							<tr style="background:#e8fadf; font-weight:bold;">
								<td colspan="7" align="right" style="color:#00a65a;"><i class="fa fa-money"></i> TOTAL REALISASI EXPENSE REPORT</td>
								<td colspan="5">
									<input type="text" class="form-control divide input-sm summary-field" id="total_expense" name="total_expense" value="<?= $total_expense ?>" placeholder="0" tabindex="-1" readonly style="color:#00a65a; background:#ffffff;">
								</td>
							</tr>
							<tr id="kontrol_row" <?= ($grand_total > 0) ? "" : "hidden" ?> style="background:#e8fadf; font-weight:bold;">
								<td colspan="7" align="right" style="color:#00a65a;"><i class="fa fa-reply"></i> LEBIH KASBON (PENGEMBALIAN KE KANTOR)</td>
								<td colspan="5">
									<input type="text" class="form-control divide input-sm summary-field" id="kontrol" placeholder="0" tabindex="-1" readonly value="<?= ($grand_total > 0) ? $grand_total : 0 ?>" style="color:#00a65a; background:#ffffff;">
								</td>
							</tr>
							<tr id="kurang_bayar_row" <?= ($grand_total < 0) ? "" : "hidden" ?> style="background:#fde8e8; font-weight:bold;">
								<td colspan="7" align="right" style="color:#dd4b39;"><i class="fa fa-exclamation-circle"></i> LEBIH EXPENSE (REIMBURSE KE KARYAWAN)</td>
								<td colspan="5">
									<input type="text" class="form-control divide input-sm summary-field" id="kurang_bayar" name="kurang_bayar" value="<?= ($grand_total < 0) ? abs($grand_total) : 0 ?>" placeholder="0" tabindex="-1" readonly style="color:#dd4b39; background:#ffffff;">
								</td>
							</tr>
							<tr id="selisih_row" style="background:#f8fafc; font-weight:bold;">
								<td colspan="7" align="right">SELISIH KONTROL (KASBON - EXPENSE)</td>
								<td colspan="5">
									<input type="text" class="form-control divide input-sm summary-field" id="grand_total" name="grand_total" value="<?= abs($grand_total) ?>" placeholder="0" tabindex="-1" readonly style="background:#ffffff;">
								</td>
							</tr>
						</tfoot>
					</table>
				</div>

				<!-- SECTION PENGEMBALIAN (LEBIH KASBON) & KETERANGAN (LEBIH EXPENSE) -->
				<div class="row" style="margin-top: 15px;">
					<div class="col-md-6" id="pengembalian" <?= ($grand_total > 0) ? "" : "hidden" ?>>
						<input type="hidden" name="pengembalian" id="pengembalian_val" value="2">
						<div class="panel panel-info" style="border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
							<div class="panel-heading" style="font-weight: 700;">
								<i class="fa fa-reply"></i> Pengembalian Kelebihan Kasbon (Transfer ke Rekening Perusahaan)
							</div>
							<div class="panel-body">
								<?php
								// Bank pengembalian: bank yang tersimpan (edit/view) > fallback default lama (dokumen lama pra-fitur, BCA 1101-02-01) > kosong (dokumen baru)
								$selected_bank_pengembalian = (isset($data->id_bank_pengembalian) && $data->id_bank_pengembalian !== '' && $data->id_bank_pengembalian !== null)
									? $data->id_bank_pengembalian
									: '';
								if ($selected_bank_pengembalian === '' && isset($data) && $grand_total > 0) {
									// Dokumen lama (sudah tersimpan sebelum fitur ini ada) tanpa id_bank_pengembalian -> fallback BCA
									foreach ($list_bank as $lb_item) {
										if ($lb_item->coa_bank == '1101-02-01') {
											$selected_bank_pengembalian = $lb_item->id;
											break;
										}
									}
								}
								?>
								<div class="form-group" style="margin-left:0; margin-right:0;">
									<label class="control-label">Bank Pengembalian (Tujuan Transfer) <b class="text-red">*</b></label>
									<select class="form-control input-sm" name="bank_pengembalian" id="bank_pengembalian" onchange="set_jurnal()">
										<option value="">- Pilih Bank -</option>
										<?php
										if (!empty($list_bank)) {
											foreach ($list_bank as $lb_item) {
												$lb_selected = ((string)$lb_item->id === (string)$selected_bank_pengembalian) ? 'selected' : '';
												echo '<option value="' . $lb_item->id . '" ' . $lb_selected . '>(' . $lb_item->rekening . ' a/n ' . $lb_item->nama . ') - ' . $lb_item->nama_bank . '</option>';
											}
										}
										?>
									</select>
								</div>
								<div class="form-group" style="margin-left:0; margin-right:0; margin-bottom: 0;">
									<label class="control-label">Upload Bukti Transfer Balik <b class="text-red">*</b></label>

									<div style="display:flex; align-items:center; gap:8px; margin-bottom:5px;">
										<label class="btn btn-sm btn-primary btn-flat-custom stsview" style="cursor:pointer; margin-bottom:0;" title="Pilih File Bukti Transfer">
											<i class="fa fa-folder-open"></i> Pilih Bukti Transfer
											<input type="file" id="temp_bukti_pengembalian_picker" style="display:none;" accept=".jpg,.jpeg,.png,.pdf" multiple>
										</label>
										<span id="selected_bukti_count" class="text-muted" style="font-size:12px;"></span>
									</div>

									<input type="file" name="bukti_pengembalian[]" id="id_bukti_pengembalian" multiple style="display:none;" accept=".jpg,.jpeg,.png,.pdf">
									<div id="new_bukti_pengembalian_list" style="display:flex; flex-wrap:wrap; gap:5px; margin-top:5px;"></div>
									<small class="text-muted" style="font-size:11px; display:block; margin-top:4px;"><i class="fa fa-info-circle"></i> Format: JPG, PNG, PDF. Bisa klik <b>Pilih Bukti Transfer</b> berkali-kali untuk memilih file dari berbagai folder satu per satu.</small>

									<?php if (isset($data->bukti_pengembalian) && !empty($data->bukti_pengembalian)): 
										$arr_files = explode(';', $data->bukti_pengembalian);
									?>
										<div class="existing-bukti-container" style="margin-top:10px;">
											<label style="font-size:12px; font-weight:600; color:#555;"><i class="fa fa-paperclip"></i> Bukti Transfer Terlampir Sebelumnya:</label>
											<div style="display:flex; flex-wrap:wrap; gap:6px; margin-top:4px;">
												<?php foreach ($arr_files as $f_item): 
													if (empty($f_item)) continue;
													$furl = base_url($f_item);
													$is_fpdf = (stripos($f_item, '.pdf') !== false);
													$fname = basename($f_item);
												?>
													<span class="badge file-badge-item" style="background:#3c8dbc; font-size:11px; font-weight:normal; padding:5px 8px; display:inline-flex; align-items:center; gap:6px;">
														<a href="<?= $furl ?>" target="_blank" style="color:#fff; text-decoration:none;" title="<?= htmlspecialchars($fname) ?>">
															<i class="fa <?= $is_fpdf ? 'fa-file-pdf-o' : 'fa-file-image-o' ?>"></i> <?= htmlspecialchars($fname) ?>
														</a>
														<input type="hidden" name="existing_bukti_pengembalian[]" value="<?= htmlspecialchars($f_item) ?>">
														<?php if ($stsview != 'view' && $stsview != 'approval'): ?>
															<button type="button" class="btn btn-xs btn-danger remove-existing-bukti" style="padding:0 4px; font-size:10px; line-height:1; border-radius:2px;" title="Hapus file ini">
																<i class="fa fa-times"></i>
															</button>
														<?php endif; ?>
													</span>
												<?php endforeach; ?>
											</div>
										</div>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div>

					<div class="col-md-6" id="section_kurang_bayar" <?= ($grand_total < 0) ? "" : "hidden" ?>>
						<div class="panel panel-danger" style="border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
							<div class="panel-heading" style="font-weight: 700;">
								<i class="fa fa-exclamation-triangle"></i> Keterangan Lebih Expense (Reimburse Kantor)
							</div>
							<div class="panel-body">
								<textarea class="form-control input-sm" name="keterangan_kurang_bayar" id="keterangan_kurang_bayar" rows="3" placeholder="Alasan mengapa pengeluaran melebihi kasbon awal (opsional)..."><?= (isset($data->keterangan_kurang_bayar) ? $data->keterangan_kurang_bayar : "") ?></textarea>
							</div>
						</div>
					</div>
				</div>

				<!-- SECTION TABLE JURNAL EXPENSE & KASBON -->
				<div id="section_jurnal" style="margin-top: 25px;">
					<div class="panel panel-default" style="border-radius: 6px; border: 1px solid #d2d6de; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
						<div class="panel-heading" style="background:#f8fafc; font-weight: 700; color: #2d3748; font-size: 14px;">
							<i class="fa fa-book text-primary"></i> <b>Daftar Jurnal Expense & Pertanggungjawaban Kasbon</b>
						</div>
						<div class="panel-body" style="padding: 10px;">
							<div class="table-responsive">
								<table class="table table-bordered table-striped" width="100%" style="font-size:12px; margin-bottom:0;">
									<thead>
										<tr style="background:#e2e8f0; color:#333;">
											<th width="120" class="text-center">Tanggal Jurnal</th>
											<th width="110" class="text-center">COA</th>
											<th width="160" class="text-center">Nama Company</th>
											<th width="180">Nama Account</th>
											<th>Deskripsi / Keterangan</th>
											<th width="130" class="text-right">Debit (Rp)</th>
											<th width="130" class="text-right">Kredit (Rp)</th>
										</tr>
									</thead>
									<tbody class="tbody_jurnal">
									</tbody>
									<tfoot>
										<tr style="background:#edf2f7; font-weight:bold;">
											<td colspan="5" class="text-center"><b>TOTAL BALANCING</b></td>
											<td class="text-right ttl_debit" style="color:#00a65a; font-size:13px;">0</td>
											<td class="text-right ttl_kredit" style="color:#2e59d9; font-size:13px;">0</td>
										</tr>
									</tfoot>
								</table>
							</div>
						</div>
					</div>
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
					<button type="submit" name="save" class="btn btn-primary btn-sm btn-flat-custom stsview" id="submit" style="margin-right:5px;">
						<i class="fa fa-save">&nbsp;</i> Simpan Expense Report
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
	var url_set_jurnal = siteurl + 'expense/set_jurnal_expense';

	$(document).ready(function() {
		var companyName = (document.title.indexOf('|') !== -1) ? document.title.split('|')[0].trim() : 'SENDIGS SS';
		document.title = companyName + ' | Expense Report';
		$('.content-header h1').html('<i class="fa fa-ticket"></i> Expense Report');

		$('.select2').select2({ width: '100%' });
		$(".divide").divide();
		$(".tanggal").datepicker({
			todayHighlight: true,
			format: "yyyy-mm-dd",
			showInputs: true,
			autoclose: true
		}).on('changeDate change', function() {
			set_jurnal();
		});

		$(document).on('change', '#tgl_doc, input[name="tanggal[]"]', function() {
			set_jurnal();
		});

		if (stsview == 'view' || stsview == 'approval') {
			$(".stsview").hide();
			$("#frm_data input:not([type=hidden]), #frm_data textarea, #frm_data select").prop("disabled", true);
		}

		hitung_report_totals();
	});

	function hitung_report_row(id) {
		var qty = parseFloat($("#qty_" + id).val().replace(/,/g, '')) || 0;
		var harga = parseFloat($("#harga_" + id).val().replace(/,/g, '')) || 0;
		var total = qty * harga;
		$("#expense_" + id).val(total);
		hitung_report_totals();
	}

	function hitung_report_totals() {
		var total_kasbon = 0;
		$(".subkasbon").each(function() {
			total_kasbon += parseFloat($(this).val().replace(/,/g, '')) || 0;
		});

		var total_expense = 0;
		$(".subtotal").each(function() {
			total_expense += parseFloat($(this).val().replace(/,/g, '')) || 0;
		});

		$("#total_kasbon").val(total_kasbon);
		$("#total_expense").val(total_expense);

		var selisih = total_kasbon - total_expense;
		$("#grand_total").val(Math.abs(selisih));

		if (selisih > 0) {
			$("#kontrol_row").show();
			$("#kontrol").val(selisih);
			$("#kurang_bayar_row").hide();
			$("#kurang_bayar").val(0);
			$("#pengembalian").show();
			$("#section_kurang_bayar").hide();
		} else if (selisih < 0) {
			$("#kontrol_row").hide();
			$("#kontrol").val(0);
			$("#kurang_bayar_row").show();
			$("#kurang_bayar").val(Math.abs(selisih));
			$("#pengembalian").hide();
			$("#section_kurang_bayar").show();
		} else {
			$("#kontrol_row").hide();
			$("#kontrol").val(0);
			$("#kurang_bayar_row").hide();
			$("#kurang_bayar").val(0);
			$("#pengembalian").hide();
			$("#section_kurang_bayar").hide();
		}

		$(".divide").divide();
		set_jurnal();
	}

	function set_jurnal() {
		var formdata = new FormData($('#frm_data')[0]);

		// Pada mode view/approval semua field di-disable, sehingga tidak ikut
		// terkirim di FormData. Pastikan bank pengembalian yang dipilih tetap
		// dikirim agar baris jurnal Bank menampilkan rekening yang benar
		// (bukan fallback default BCA).
		var bankPengembalianVal = $('#bank_pengembalian').val();
		if (bankPengembalianVal) {
			formdata.set('bank_pengembalian', bankPengembalianVal);
		}

		$.ajax({
			url: url_set_jurnal,
			dataType: "json",
			type: 'POST',
			data: formdata,
			processData: false,
			contentType: false,
			success: function(res) {
				if (res && res.status === 1) {
					$('.tbody_jurnal').html(res.hasil);
					$('.ttl_debit').text(res.ttl_debit);
					$('.ttl_kredit').text(res.ttl_kredit);
				}
			},
			error: function(xhr, status, error) {
				console.error("Gagal generate jurnal: " + error);
			}
		});
	}

	// ==========================================
	// BUKTI PENGEMBALIAN MULTI-FILE ACCUMULATOR
	// ==========================================
	var dtBuktiPengembalian = new DataTransfer();

	$(document).on('change', '#temp_bukti_pengembalian_picker', function() {
		var newFiles = this.files;
		if (newFiles.length > 0) {
			for (var i = 0; i < newFiles.length; i++) {
				var file = newFiles[i];
				var exists = false;
				for (var j = 0; j < dtBuktiPengembalian.items.length; j++) {
					var existingFile = dtBuktiPengembalian.items[j].getAsFile();
					if (existingFile && existingFile.name === file.name && existingFile.size === file.size) {
						exists = true;
						break;
					}
				}
				if (!exists) {
					dtBuktiPengembalian.items.add(file);
				}
			}
			var hiddenInput = document.getElementById('id_bukti_pengembalian');
			if (hiddenInput) {
				hiddenInput.files = dtBuktiPengembalian.files;
			}
			$(this).val('');
			render_bukti_pengembalian_list();
		}
	});

	function render_bukti_pengembalian_list() {
		var container = $('#new_bukti_pengembalian_list');
		container.empty();
		var count = dtBuktiPengembalian.files.length;
		if (count > 0) {
			$('#selected_bukti_count').text(count + ' file baru dipilih:');
			for (var i = 0; i < count; i++) {
				var file = dtBuktiPengembalian.files[i];
				var isPdf = file.name.toLowerCase().endsWith('.pdf');
				var sizeKb = Math.round(file.size / 1024);
				var badge = '<span class="badge" style="background:#00a65a; font-size:11px; font-weight:normal; text-align:left; padding:4px 7px; display:inline-flex; align-items:center; gap:5px;">' +
					'<i class="fa ' + (isPdf ? 'fa-file-pdf-o' : 'fa-file-image-o') + '"></i> ' + file.name + ' (' + sizeKb + ' KB) ' +
					'<button type="button" class="btn btn-xs btn-danger remove-new-bukti" data-index="' + i + '" style="padding:0 4px; font-size:10px; line-height:1; border-radius:2px;" title="Hapus file ini">' +
					'<i class="fa fa-times"></i>' +
					'</button>' +
					'</span>';
				container.append(badge);
			}
		} else {
			$('#selected_bukti_count').text('');
		}
	}

	$(document).on('click', '.remove-new-bukti', function(e) {
		e.preventDefault();
		var idx = parseInt($(this).data('index'));
		var newDt = new DataTransfer();
		for (var i = 0; i < dtBuktiPengembalian.files.length; i++) {
			if (i !== idx) {
				newDt.items.add(dtBuktiPengembalian.files[i]);
			}
		}
		dtBuktiPengembalian = newDt;
		var hiddenInput = document.getElementById('id_bukti_pengembalian');
		if (hiddenInput) {
			hiddenInput.files = dtBuktiPengembalian.files;
		}
		render_bukti_pengembalian_list();
	});

	$(document).on('click', '.remove-existing-bukti', function(e) {
		e.preventDefault();
		$(this).closest('.file-badge-item').remove();
	});

	// ==========================================
	// DETAIL MULTI-FILE ACCUMULATOR
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
				'<span style="max-width:105px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="' + file.name + ' (' + sizeKb + ' KB)">' +
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

	// SUBMIT EXPENSE REPORT
	$('#frm_data').on('submit', function(e) {
		e.preventDefault();
		var errors = "";
		if ($("#informasi").val() == "") errors = "Keterangan tidak boleh kosong";
		if ($("#tgl_doc").val() == "") errors = "Tanggal Dokumen tidak boleh kosong";
		if ($("#detail_report_body tr").length == 0) errors = "Rincian pengeluaran belum ada";

		var total_kasbon = parseFloat($("#total_kasbon").val().replace(/,/g, '')) || 0;
		var total_expense = parseFloat($("#total_expense").val().replace(/,/g, '')) || 0;
		var selisih = total_kasbon - total_expense;

		if (selisih > 0) {
			var bankPengembalian = $('#bank_pengembalian').val();
			if (!bankPengembalian) {
				errors = "Terdapat Lebih Kasbon sebesar Rp " + Number(selisih).toLocaleString('en-US') + ". Bank Pengembalian (Tujuan Transfer) WAJIB dipilih!";
			}

			var hasNewBukti = (typeof dtBuktiPengembalian !== 'undefined' && dtBuktiPengembalian.files.length > 0);
			var hasExistingBukti = ($('input[name="existing_bukti_pengembalian[]"]').length > 0);
			if (errors == "" && !hasNewBukti && !hasExistingBukti) {
				errors = "Terdapat Lebih Kasbon sebesar Rp " + Number(selisih).toLocaleString('en-US') + ". Bukti Transfer Balik (Pengembalian ke Kantor) WAJIB diupload!";
			}
		}

		if (errors != "") {
			Swal.fire({ title: "Perhatian!", text: errors, icon: "warning" });
			return false;
		}

		Swal.fire({
			title: "Simpan Expense Report?",
			text: "Pastikan seluruh rincian realisasi kasbon dan bukti pengeluaran sudah benar!",
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
								text: "Dokumen Expense Report berhasil disimpan.",
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
				$.post(siteurl + 'expense/reject', { id: id, reason: res.value, table: 'tr_expense' }, function(result) {
					Swal.fire("Ditolak!", "Dokumen telah ditolak.", "info").then(() => {
						window.location.reload();
					});
				}, "json");
			}
		});
	}
</script>
