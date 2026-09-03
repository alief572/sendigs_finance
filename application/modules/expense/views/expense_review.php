<?php
$gambar = '';
$dept = '';
$app = '';
$bank_id = '';
$accnumber = '';
$accname = '';
$data_session	= $this->session->userdata;
// print_r($data_session);
$dateTime = date('Y-m-d H:i:s');
$UserName = $data_session['app_session']['id_user'];
$dept = $data_session['app_session']['department_id'];
$readonly = 'readonly';
?>
<link rel="stylesheet" href="<?= base_url() ?>assets/plugins/select2/select2.css">
<script src="<?= base_url() ?>assets/plugins/select2/select2.full.min.js"></script>
<?= form_open($this->uri->uri_string(), array('id' => 'frm_data', 'name' => 'frm_data', 'role' => 'form', 'class' => 'form-horizontal', 'enctype' => 'multipart/form-data')); ?>
<input type="hidden" id="id" name="id" value="<?php echo set_value('id', isset($data->id) ? $data->id : ''); ?>">
<input type="hidden" id="nama" name="nama" value="<?php echo (isset($data->nama) ? $data->nama : $UserName); ?>">
<input type="hidden" id="approval" name="approval" value="<?php echo (isset($data->approval) ? $data->approval : $app); ?>">
<input type="hidden" id="bank_pengembalian" name="bank_pengembalian" value="<?php echo (isset($data->id_bank_pengembalian) ? $data->id_bank_pengembalian : ''); ?>">
<style>
	.table-expense-report thead th {
		color: #fff;
		text-align: center;
		vertical-align: middle !important;
		font-size: 12px;
	}
	.table-expense-report tbody td {
		vertical-align: middle !important;
	}
</style>
<?php
// Nilai header untuk ditampilkan (read-only), meniru tampilan Expense Report.
$deptid       = (isset($data->departement) ? $data->departement : $dept);
$dept_name    = '';
foreach ($data_departement as $item) {
	if ($item->id == $deptid) {
		$dept_name = strtoupper($item->nama);
		break;
	}
}
$no_doc_val   = (isset($data->no_doc) ? $data->no_doc : '');
$tgl_doc_val  = (isset($data->tgl_doc) ? $data->tgl_doc : date('Y-m-d'));
$informasi_val = (isset($data->informasi) ? $data->informasi : '');
$no_doc_kasbon = (isset($data->id_kasbon) ? $data->id_kasbon : '');
$bank_id_val  = (isset($data->bank_id) ? $data->bank_id : $bank_id);
$accnumber_val = (isset($data->accnumber) ? $data->accnumber : $accnumber);
$accname_val  = (isset($data->accname) ? $data->accname : $accname);
$bank_display = trim($bank_id_val . ' - ' . $accnumber_val . ' - ' . $accname_val, ' -');
// Mode view read-only untuk melihat detail pengembalian yang sudah diinput user.
$is_view_return = (isset($stsview) && $stsview == 'view_return');
$bukti_files = (isset($bukti_transfer) && $bukti_transfer !== '') ? array_values(array_filter(array_map('trim', explode(';', $bukti_transfer)))) : [];
?>
<div class="tab-content">
	<div class="tab-pane active">
		<div class="box box-primary">
			<div class="box-body">
				<!-- ===================== HEADER (tampilan sama seperti Expense Report) ===================== -->
				<div class="box box-custom" style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,0.04);margin-bottom:20px;">
					<div style="padding:12px 20px;border-bottom:1px solid #edf2f7;">
						<h4 style="font-size:16px;font-weight:700;color:#2d3748;margin:0;">
							<i class="fa fa-ticket text-primary"></i> Informasi Expense Report (Pertanggungjawaban Kasbon)
						</h4>
					</div>
					<div class="box-body" style="padding:20px;">
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label class="col-sm-4 control-label">No. Dokumen</label>
									<div class="col-sm-8">
										<input type="text" class="form-control input-sm" id="no_doc" name="no_doc" value="<?= $no_doc_val ?>" readonly style="font-weight:bold;background:#f8fafc;color:#2d3748;">
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-4 control-label">No. Kasbon</label>
									<div class="col-sm-8" style="padding-top:6px;">
										<span class="badge bg-blue" style="font-size:13px;padding:6px 10px;"><i class="fa fa-ticket"></i> <?= $no_doc_kasbon ?></span>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-4 control-label">Tanggal</label>
									<div class="col-sm-8">
										<input type="text" class="form-control input-sm" id="tgl_doc" name="tgl_doc" value="<?= $tgl_doc_val ?>" readonly>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-4 control-label">Keterangan</label>
									<div class="col-sm-8">
										<textarea class="form-control input-sm" id="informasi" name="informasi" rows="2" readonly><?= $informasi_val ?></textarea>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="col-sm-4 control-label">Department</label>
									<div class="col-sm-8">
										<input type="text" class="form-control input-sm" value="<?= $dept_name ?>" readonly>
										<input type="hidden" name="department" value="<?= $deptid ?>">
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-4 control-label">Bank Penerima</label>
									<div class="col-sm-8">
										<input type="text" class="form-control input-sm" id="bank_id" name="bank_id" value="<?= $bank_id_val ?>" readonly>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-4 control-label">No. Rekening</label>
									<div class="col-sm-8">
										<input type="text" class="form-control input-sm" id="accnumber" name="accnumber" value="<?= $accnumber_val ?>" readonly>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-4 control-label">Atas Nama</label>
									<div class="col-sm-8">
										<input type="text" class="form-control input-sm" id="accname" name="accname" value="<?= $accname_val ?>" readonly>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- ===================== TABEL RINCIAN KASBON VS REALISASI (sama seperti Expense Report) ===================== -->
				<div style="font-size:15px;font-weight:700;color:#333;margin-bottom:10px;">
					<i class="fa fa-balance-scale text-primary"></i> Rincian Kasbon vs Realisasi Pengeluaran
				</div>
				<div class="table-responsive">
					<table class="table table-bordered table-expense-report" width="100%" style="font-size:12px;">
						<thead>
							<tr>
								<th rowspan="2" width="30" style="background:#3c8dbc;border:1px solid #367fa9;">#</th>
								<th rowspan="2" width="160" style="background:#3c8dbc;border:1px solid #367fa9;">Akun COA</th>
								<th rowspan="2" width="180" style="background:#3c8dbc;border:1px solid #367fa9;">Barang / Jasa</th>
								<th rowspan="2" width="150" style="background:#3c8dbc;border:1px solid #367fa9;">Spesifikasi</th>
								<th rowspan="2" width="90" style="background:#3c8dbc;border:1px solid #367fa9;">Tanggal</th>
								<th colspan="3" style="background:#2e59d9;border:1px solid #224abe;">Kasbon Awal</th>
								<th colspan="3" style="background:#00a65a;border:1px solid #008d4c;">Realisasi Expense Report</th>
								<th rowspan="2" width="90" style="background:#3c8dbc;border:1px solid #367fa9;">Bon / Bukti</th>
							</tr>
							<tr>
								<th width="45" style="background:#4e73df;border:1px solid #3b64db;">Qty</th>
								<th width="85" style="background:#4e73df;border:1px solid #3b64db;">Harga</th>
								<th width="95" style="background:#4e73df;border:1px solid #3b64db;">Total</th>
								<th width="45" style="background:#28a745;border:1px solid #218838;">Qty</th>
								<th width="85" style="background:#28a745;border:1px solid #218838;">Harga</th>
								<th width="95" style="background:#28a745;border:1px solid #218838;">Total</th>
							</tr>
						</thead>
						<tbody id="detail_body">
							<?php
							$idd = 1;
							$total_expense = 0;
							$total_kasbon = 0;
							$error_coa = '';
							if (!empty($data_detail)) {
								foreach ($data_detail as $record) {
									$row_qty_kasbon   = isset($record->qty_kasbon) ? floatval($record->qty_kasbon) : 0;
									$row_harga_kasbon = isset($record->nominal_kasbon) ? floatval($record->nominal_kasbon) : 0;
									$row_total_kasbon = floatval($record->kasbon);
									$row_qty_exp      = (isset($record->qty_expense) && floatval($record->qty_expense) > 0) ? floatval($record->qty_expense) : floatval($record->qty);
									$row_harga_exp    = (isset($record->nominal_expense) && floatval($record->nominal_expense) > 0) ? floatval($record->nominal_expense) : floatval($record->harga);
									$row_total_exp    = floatval($record->expense);
									$total_kasbon    += $row_total_kasbon;
									$total_expense   += $row_total_exp;
									if ($record->coa == '' || $record->coa == '0') $error_coa = 'ERROR';
									$coa_display = isset($data_budget[$record->coa]) ? $data_budget[$record->coa] : $record->coa;
									$coa_code = $record->coa;
									$coa_name = $coa_display;
									if (strpos($coa_display, ' - ') !== false) {
										$parts = explode(' - ', $coa_display, 2);
										$coa_code = $parts[0];
										$coa_name = $parts[1];
									}
							?>
									<tr id='tr1_<?= $idd ?>' class='delAll <?= ($record->id_kasbon != '' ? 'kasbonrow' : '') ?>'>
										<td class="text-center">
											<input type='hidden' name='id_kasbon[]' id='id_kasbon_<?= $idd ?>' value='<?= $record->id_kasbon; ?>'>
											<input type="hidden" name="filename[]" id="filename_<?= $idd ?>" value="<?= $record->doc_file; ?>">
											<input type="hidden" name="detail_id[]" id="raw_id_<?= $idd ?>" value="<?= $idd; ?>" class="dtlloop">
											<input type="hidden" name="coa[]" id="coa<?= $idd ?>" value="<?= $record->coa ?>">
											<input type="hidden" name="tanggal[]" id="tanggal<?= $idd; ?>" value="<?= $record->tanggal; ?>">
											<input type="hidden" name="deskripsi[]" id="deskripsi_<?= $idd; ?>" value="<?= htmlspecialchars($record->deskripsi, ENT_QUOTES); ?>">
											<input type="hidden" name="keterangan[]" id="keterangan_<?= $idd; ?>" value="<?= htmlspecialchars($record->keterangan, ENT_QUOTES); ?>">
											<input type="hidden" name="qty[]" id="qty_<?= $idd; ?>" value="<?= $record->qty; ?>">
											<input type="hidden" name="harga[]" id="harga_<?= $idd; ?>" value="<?= $record->harga; ?>">
											<input type="hidden" name="expense[]" id="expense_<?= $idd; ?>" value="<?= $row_total_exp; ?>">
											<input type="hidden" name="kasbon[]" id="kasbon_<?= $idd; ?>" value="<?= $row_total_kasbon; ?>">
											<?= $idd; ?>
										</td>
										<td>
											<div style="display:flex;flex-direction:column;gap:2px;">
												<span class="badge bg-blue" style="font-size:11px;text-align:left;white-space:normal;"><i class="fa fa-tag"></i> <?= $coa_code ?></span>
												<small class="text-muted" style="font-weight:600;font-size:11px;"><?= $coa_name ?></small>
											</div>
										</td>
										<td style="font-size:12px;"><?= nl2br(htmlspecialchars($record->deskripsi)) ?></td>
										<td style="font-size:12px;"><?= nl2br(htmlspecialchars($record->keterangan)) ?></td>
										<td class="text-center" style="font-size:12px;white-space:nowrap;"><?= $record->tanggal ?></td>
										<td class="text-right"><?= number_format($row_qty_kasbon) ?></td>
										<td class="text-right"><?= number_format($row_harga_kasbon) ?></td>
										<td class="text-right" style="font-weight:bold;color:#2e59d9;background:#eaf2fd;"><?= number_format($row_total_kasbon) ?></td>
										<td class="text-right"><?= number_format($row_qty_exp) ?></td>
										<td class="text-right"><?= number_format($row_harga_exp) ?></td>
										<td class="text-right" style="font-weight:bold;color:#00a65a;background:#e8fadf;"><?= number_format($row_total_exp) ?></td>
										<td class="text-center">
											<?php if ($record->doc_file != ''): $is_pdf = (stripos($record->doc_file, '.pdf') !== false); ?>
												<a href="<?= base_url('assets/expense/' . $record->doc_file) ?>" target="_blank" class="badge" style="background:#3c8dbc;color:#fff;font-size:10px;font-weight:normal;"><i class="fa <?= $is_pdf ? 'fa-file-pdf-o' : 'fa-file-image-o' ?>"></i> Lihat</a>
											<?php else: ?>
												<span class="text-muted" style="font-size:11px;">-</span>
											<?php endif; ?>
										</td>
									</tr>
							<?php
									if ($record->doc_file != '') {
										if (strpos($record->doc_file, 'pdf', 0) > 1) {
											$gambar .= '<div class="col-md-12"><iframe src="' . base_url('assets/expense/' . $record->doc_file) . '#toolbar=0&navpanes=0" title="PDF" style="width:600px; height:500px;" frameborder="0"><a href="' . base_url('assets/expense/' . $record->doc_file) . '">Download PDF</a></iframe><br />' . $record->no_doc . '</div>';
										} else {
											$gambar .= '<div class="col-md-4"><a href="' . base_url('assets/expense/' . $record->doc_file) . '" target="_blank"><img src="' . base_url('assets/expense/' . $record->doc_file) . '" class="img-responsive"></a><br />' . $record->no_doc . '</div>';
										}
									}
									$idd++;
								}
							}
							$grand_total = ($total_expense - $total_kasbon);
							$hidetransfer = 'hidden';
							if ($grand_total < 0) $hidetransfer = '';
							?>
						</tbody>
						<tfoot>
							<tr style="background:#eaf2fd;font-weight:bold;">
								<td colspan="7" align="right" style="color:#2e59d9;"><i class="fa fa-ticket"></i> TOTAL KASBON</td>
								<td colspan="4" class="text-right" style="color:#2e59d9;">
									<input type="hidden" id="total_kasbon" name="total_kasbon" value="<?= $total_kasbon ?>">
									Rp <?= number_format($total_kasbon) ?>
								</td>
							</tr>
							<tr style="background:#e8fadf;font-weight:bold;">
								<td colspan="7" align="right" style="color:#00a65a;"><i class="fa fa-money"></i> TOTAL REALISASI EXPENSE</td>
								<td colspan="4" class="text-right" style="color:#00a65a;">
									<input type="hidden" id="total_expense" name="total_expense" value="<?= $total_expense ?>">
									Rp <?= number_format($total_expense) ?>
								</td>
							</tr>
							<tr style="background:#f8fafc;font-weight:bold;">
								<td colspan="7" align="right">SALDO (EXPENSE - KASBON)</td>
								<td colspan="4" class="text-right">
									<input type="hidden" id="grand_total" name="grand_total" value="<?= $grand_total ?>">
									Rp <?= number_format($grand_total) ?>
								</td>
							</tr>
						</tfoot>
					</table>
				</div>

				<!-- ===================== DATA PENGEMBALIAN / TRANSFER ===================== -->
				<div id="transfer-area" class="<?= $is_view_return ? '' : $hidetransfer ?>" style="margin-top:15px;">
					<div class="panel panel-default" style="border:1px solid #d2d6de;border-radius:6px;margin-bottom:0;">
						<div class="panel-heading" style="background:#f8fafc;font-weight:700;color:#2d3748;font-size:13px;">
							<i class="fa fa-exchange text-primary"></i> Data Pengembalian / Transfer
						</div>
						<div class="panel-body">
							<div class="row">
								<div class="col-sm-6">
									<div class="form-group">
										<label style="font-size:12px;margin-bottom:3px;">Bank Tujuan</label>
										<input type="hidden" name="transfer_coa_bank" id="transfer_coa_bank" value="<?= $bank_id_val ?>">
										<input type="text" class="form-control input-sm" value="<?= $bank_display ?>" readonly style="background:#f8fafc;">
										<small class="text-muted" style="font-size:11px;">Sesuai bank pada Expense Report.</small>
									</div>
								</div>
								<div class="col-sm-3">
									<div class="form-group">
										<label style="font-size:12px;margin-bottom:3px;">Tanggal Transfer</label>
										<input type="text" class="form-control tanggal input-sm" name="transfer_tanggal" id="transfer_tanggal" value="<?= (isset($data->transfer_tanggal) ? $data->transfer_tanggal : ''); ?>" placeholder="YYYY-MM-DD" autocomplete="off" <?= $is_view_return ? 'readonly style="background:#f8fafc;"' : '' ?>>
									</div>
								</div>
								<div class="col-sm-3">
									<div class="form-group">
										<label style="font-size:12px;margin-bottom:3px;">Nilai Transfer</label>
										<input type="text" class="form-control divide input-sm text-right" name="transfer_jumlah" id="transfer_jumlah" value="<?= (isset($data->transfer_jumlah) ? $data->transfer_jumlah : ''); ?>" placeholder="0" <?= $is_view_return ? 'readonly style="background:#f8fafc;"' : '' ?>>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-sm-12">
									<div class="form-group" style="margin-bottom:0;">
										<label style="font-size:12px;margin-bottom:3px;">Bukti Transfer <?php if (!$is_view_return): ?><small class="text-muted">(boleh lebih dari 1 file &mdash; drag &amp; drop atau klik)</small><?php endif; ?></label>
										<?php if ($is_view_return): ?>
											<?php if (!empty($bukti_files)): ?>
												<ul class="list-unstyled" style="margin-bottom:0;">
													<?php foreach ($bukti_files as $bf): $is_pdf = (stripos($bf, '.pdf') !== false); ?>
														<li style="display:flex;align-items:center;justify-content:space-between;padding:8px 12px;border:1px solid #e2e8f0;border-radius:6px;margin-bottom:6px;background:#fff;font-size:12px;">
															<span><i class="fa <?= $is_pdf ? 'fa-file-pdf-o' : 'fa-file-image-o' ?>" style="color:#3c8dbc;"></i> <?= htmlspecialchars($bf) ?></span>
															<a href="<?= base_url('assets/expense/' . $bf) ?>" target="_blank" class="btn btn-primary btn-xs" style="border-radius:6px;"><i class="fa fa-external-link"></i> Buka</a>
														</li>
													<?php endforeach; ?>
												</ul>
											<?php else: ?>
												<div class="text-muted" style="font-size:12px;">Tidak ada bukti transfer.</div>
											<?php endif; ?>
										<?php else: ?>
											<div id="dropzone_bukti" style="border:2px dashed #c7d0dc;border-radius:8px;padding:16px;text-align:center;background:#fafbfc;cursor:pointer;transition:.15s;">
												<i class="fa fa-cloud-upload" style="color:#9aa8bd;font-size:22px;"></i>
												<div style="margin-top:5px;color:#718096;font-size:12px;">Tarik &amp; letakkan file, atau <span style="color:#2b6cb0;font-weight:600;">klik untuk memilih</span></div>
												<input type="file" name="transfer_file[]" id="transfer_file" multiple style="display:none;">
											</div>
											<ul id="preview_bukti" class="list-unstyled" style="margin-top:8px;margin-bottom:0;"></ul>
										<?php endif; ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- ===================== JURNAL EXPENSE & PERTANGGUNGJAWABAN KASBON ===================== -->
				<div id="section_jurnal" style="margin-top: 20px;">
					<div class="panel panel-default" style="border-radius: 6px; border: 1px solid #d2d6de;">
						<div class="panel-heading" style="background:#f8fafc; font-weight: 700; color: #2d3748; font-size: 14px;">
							<i class="fa fa-book text-primary"></i> <b>Jurnal Expense &amp; Pertanggungjawaban Kasbon</b>
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
										<tr>
											<td colspan="7" class="text-center text-muted" style="padding:15px;">
												<i class="fa fa-spinner fa-spin"></i> Memuat jurnal...
											</td>
										</tr>
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

				<div class="box-footer">
					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-10">
							<?php
							$urlback = 'list_expense_approval';
							if ($error_coa == '' && !$is_view_return) {
								echo '<button type="submit" name="save" class="btn btn-success btn-sm" id="submit"><i class="fa fa-save">&nbsp;</i>Simpan</button>';
							}
							?>
							<?php if ($is_view_return): ?>
								<a class="btn btn-default btn-sm" data-dismiss="modal"><i class="fa fa-reply">&nbsp;</i>Tutup</a>
							<?php else: ?>
								<a class="btn btn-default btn-sm" onclick="window.location.reload();return false;"><i class="fa fa-reply">&nbsp;</i>Batal</a>
							<?php endif; ?>
						</div>
					</div>
					<!-- <div class="row">
						<?= $gambar ?>
					</div> -->
				</div>
			</div>
		</div>
	</div>
	<?= form_close() ?>
	<?php
	$datacombocoa = "";
	foreach ($data_budget as $keys => $val) {
		$datacombocoa .= "<option value='" . $keys . "'>" . $val . "</option>";
	}
	?>
	<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>
	<script type="text/javascript">
		var url_approve = base_url + 'expense/return_confirm/';
		var url_reject = base_url + 'expense/reject/';
		var url_set_jurnal_review = base_url + 'expense/set_jurnal_expense';
		$('.divide').divide();

		// Generate tabel jurnal sama seperti yang dilihat user saat membuat Expense Report.
		function set_jurnal_review() {
			var formdata = new FormData($('#frm_data')[0]);
			var bankPeng = $('#bank_pengembalian').val();
			if (bankPeng) {
				formdata.set('bank_pengembalian', bankPeng);
			}
			$.ajax({
				url: url_set_jurnal_review,
				dataType: "json",
				type: 'POST',
				data: formdata,
				processData: false,
				contentType: false,
				success: function(res) {
					if (res && res.status === 1) {
						if (res.hasil && res.hasil.trim() !== '') {
							$('.tbody_jurnal').html(res.hasil);
						} else {
							$('.tbody_jurnal').html('<tr><td colspan="7" class="text-center text-muted" style="padding:15px;">Tidak ada jurnal untuk ditampilkan.</td></tr>');
						}
						$('.ttl_debit').text(res.ttl_debit);
						$('.ttl_kredit').text(res.ttl_kredit);
					}
				},
				error: function(xhr, status, error) {
					$('.tbody_jurnal').html('<tr><td colspan="7" class="text-center text-danger" style="padding:15px;">Gagal memuat jurnal.</td></tr>');
					console.error("Gagal generate jurnal: " + error);
				}
			});
		}
		set_jurnal_review();
		$('.select2').select2({
			width: '100%'
		});
		<?php if (isset($stsview)) {
			if ($stsview == 'review') {
		?>
				$(".stsview").addClass("hidden");
		<?php
			}
		} ?>
		$(function() {
			$(".tanggal").datepicker({
				dateFormat: 'yy-mm-dd'
			});
		});

		// ===================== Bukti Transfer: multi-file + drag & drop =====================
		(function() {
			var dropzone = document.getElementById('dropzone_bukti');
			var fileInput = document.getElementById('transfer_file');
			var preview = document.getElementById('preview_bukti');
			if (!dropzone || !fileInput || !preview) return;

			// Simpan daftar file terpilih secara akumulatif.
			var store = new DataTransfer();

			function humanSize(bytes) {
				if (bytes < 1024) return bytes + ' B';
				if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
				return (bytes / 1048576).toFixed(1) + ' MB';
			}

			function render() {
				preview.innerHTML = '';
				Array.prototype.forEach.call(store.files, function(f, idx) {
					var li = document.createElement('li');
					li.style.cssText = 'display:flex;align-items:center;justify-content:space-between;padding:6px 10px;border:1px solid #e2e8f0;border-radius:6px;margin-bottom:6px;background:#fff;font-size:12px;';
					var left = document.createElement('span');
					left.innerHTML = '<i class="fa fa-file-o" style="color:#3c8dbc;"></i> ' + f.name + ' <span style="color:#a0aec0;">(' + humanSize(f.size) + ')</span>';
					var rm = document.createElement('button');
					rm.type = 'button';
					rm.className = 'btn btn-xs btn-danger';
					rm.innerHTML = '<i class="fa fa-times"></i>';
					rm.onclick = function() {
						removeAt(idx);
					};
					li.appendChild(left);
					li.appendChild(rm);
					preview.appendChild(li);
				});
				fileInput.files = store.files;
			}

			function addFiles(fileList) {
				Array.prototype.forEach.call(fileList, function(f) {
					store.items.add(f);
				});
				render();
			}

			function removeAt(index) {
				var dt = new DataTransfer();
				Array.prototype.forEach.call(store.files, function(f, i) {
					if (i !== index) dt.items.add(f);
				});
				store = dt;
				render();
			}

			dropzone.addEventListener('click', function() {
				fileInput.click();
			});
			fileInput.addEventListener('change', function(e) {
				var picked = [];
				Array.prototype.forEach.call(e.target.files, function(f) {
					picked.push(f);
				});
				addFiles(picked);
			});
			['dragenter', 'dragover'].forEach(function(ev) {
				dropzone.addEventListener(ev, function(e) {
					e.preventDefault();
					e.stopPropagation();
					dropzone.style.borderColor = '#2b6cb0';
					dropzone.style.background = '#eef5fd';
				});
			});
			['dragleave', 'drop'].forEach(function(ev) {
				dropzone.addEventListener(ev, function(e) {
					e.preventDefault();
					e.stopPropagation();
					dropzone.style.borderColor = '#c7d0dc';
					dropzone.style.background = '#fafbfc';
				});
			});
			dropzone.addEventListener('drop', function(e) {
				if (e.dataTransfer && e.dataTransfer.files.length) {
					addFiles(e.dataTransfer.files);
				}
			});
		})();


		$('#frm_data').on('submit', function(e) {
			e.preventDefault();
			var errors = "";
			if (errors == "") {
				swal({
						title: "Anda Yakin?",
						text: "Data Akan Disetujui!",
						type: "info",
						showCancelButton: true,
						confirmButtonText: "Ya, simpan!",
						cancelButtonText: "Tidak!",
						closeOnConfirm: false,
						closeOnCancel: true
					},
					function(isConfirm) {
						if (isConfirm) {
							var formdata = new FormData($('#frm_data')[0]);
							id = $("#id").val();
							$.ajax({
								url: url_approve + id,
								dataType: "json",
								type: 'POST',
								data: formdata,
								processData: false,
								contentType: false,
								success: function(msg) {
									if (msg['save'] == '1') {
										swal({
											title: "Sukses!",
											text: "Data Berhasil Di Setujui",
											type: "success",
											timer: 1500,
											showConfirmButton: false
										});
										window.location.reload();
									} else {
										if (msg['valid'] == 2) {
											swal({
												title: "Gagal!",
												text: "Sisa pengembalian melebihi nilai expense !",
												type: "error",
												timer: 1500,
												showConfirmButton: false
											});
										} else {
											swal({
												title: "Gagal!",
												text: "Data Gagal Di Setujui",
												type: "error",
												timer: 1500,
												showConfirmButton: false
											});
										}
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
			} else {
				swal(errors);
				return false;
			}
		});

		function data_reject() {
			swal({
					title: "Perhatian",
					text: "Berikan alasan penolakan",
					type: "input",
					showCancelButton: true,
					closeOnConfirm: false,
					closeOnCancel: true
				},
				function(inputValue) {
					if (inputValue === false) return false;
					if (inputValue === "") {
						swal.showInputError("Tuliskan alasan anda");
						return false
					}

					swal({
							title: "Anda Yakin?",
							text: "Data Akan Tolak!",
							type: "warning",
							showCancelButton: true,
							confirmButtonText: "Ya, tolak!",
							cancelButtonText: "Tidak!",
							closeOnConfirm: false,
							closeOnCancel: true
						},
						function(isConfirm) {
							if (isConfirm) {
								id = $("#id").val();
								$.ajax({
									url: url_reject,
									data: {
										'id': id,
										'reason': inputValue
									},
									dataType: "json",
									type: 'POST',
									success: function(msg) {
										if (msg['save'] == '1') {
											swal({
												title: "Sukses!",
												text: "Data Berhasil Di Tolak",
												type: "success",
												timer: 1500,
												showConfirmButton: false
											});
											window.location.reload();
										} else {
											swal({
												title: "Gagal!",
												text: "Data Gagal Di Tolak",
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

				});
		}
	</script>
