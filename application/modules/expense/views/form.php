<link rel="stylesheet" href="<?= base_url() ?>assets/plugins/select2/select2.css">
<script src="<?= base_url() ?>assets/plugins/select2/select2.full.min.js"></script>
<?php
$gambar = '';
$dept = '';
$app = '';
$bank_id = '';
$accnumber = '';
$accname = '';
if (!isset($data->departement)) {
	$data_user = $this->db->get_where('users', ['id_user' => $this->auth->user_id()])->row();
	$data_employee = $this->db->get_where('employee', ['id' => $data_user->employee_id])->row();
	$dept = $data_user->department_id;
	if (!empty($data_employee)) {
		$bank_id = $data_employee->bank_id;
		$accnumber = $data_employee->accnumber;
		$accname = $data_employee->accname;
		$data_head = $this->db->get_where('divisions_head', ['id' => $data_employee->division_head])->row();
		$app = $data_head->employee_id;
	}
}

$list_bon_bukti = array();
if (isset($data->bon_bukti) && !empty($data->bon_bukti)) {
	$files_header = explode(';', $data->bon_bukti);
	foreach ($files_header as $fh) {
		$fh_clean = trim($fh);
		if (!empty($fh_clean)) {
			$list_bon_bukti[] = array(
				'source' => 'Header Dokumen',
				'path'   => $fh_clean,
				'name'   => basename($fh_clean)
			);
		}
	}
}
if (!empty($data_detail)) {
	foreach ($data_detail as $dd) {
		if (!empty($dd->doc_file)) {
			$files_detail = explode(';', $dd->doc_file);
			foreach ($files_detail as $fd) {
				$fd_clean = trim($fd);
				if (!empty($fd_clean)) {
					$is_dup = false;
					foreach ($list_bon_bukti as $lbb) {
						if ($lbb['path'] == $fd_clean || basename($lbb['path']) == basename($fd_clean)) {
							$is_dup = true;
							break;
						}
					}
					if (!$is_dup) {
						$path_final = (strpos($fd_clean, 'assets/') === 0) ? $fd_clean : 'assets/expense/' . $fd_clean;
						$list_bon_bukti[] = array(
							'source' => !empty($dd->deskripsi) ? $dd->deskripsi : 'Baris Pengeluaran',
							'path'   => $path_final,
							'name'   => basename($fd_clean)
						);
					}
				}
			}
		}
	}
}
?>
<?= form_open($this->uri->uri_string(), array('id' => 'frm_data', 'name' => 'frm_data', 'role' => 'form', 'class' => 'form-horizontal', 'enctype' => 'multipart/form-data')); ?>
<input type="hidden" id="id" name="id" value="<?php echo set_value('id', isset($data->id) ? $data->id : ''); ?>">
<input type="hidden" id="departement" name="departement" value="<?php echo $dept; ?>">
<input type="hidden" id="nama" name="nama" value="<?php echo (isset($data->nama) ? $data->nama : $this->auth->user_name()); ?>">
<input type="hidden" id="approval" name="approval" value="<?php echo (isset($data->approval) ? $data->approval : $app); ?>">
<input type="hidden" name="" class="stsview" value="<?= (isset($stsview)) ? $stsview : null ?>">

<style>
	.form-card {
		background: #ffffff;
		border: 1px solid #d2d6de;
		border-radius: 6px;
		box-shadow: 0 1px 3px rgba(0,0,0,0.08);
		padding: 20px;
		margin-bottom: 20px;
	}
	.form-card-title {
		font-size: 16px;
		font-weight: 700;
		color: #333333;
		border-bottom: 2px solid #f4f4f4;
		padding-bottom: 10px;
		margin-bottom: 20px;
	}
	.rekening-box {
		background: #fbfbfb;
		border: 1px solid #e1e4e8;
		border-radius: 6px;
		padding: 15px;
		margin-top: 5px;
	}
	.table-expense, .table-jurnal-custom {
		border: 1px solid #d2d6de;
		background: #fff;
	}
	.table-expense thead th, .table-jurnal-custom thead th {
		background-color: #3c8dbc;
		color: #ffffff;
		font-weight: 600;
		text-align: center;
		vertical-align: middle !important;
		border: 1px solid #357ca5 !important;
		font-size: 13px;
		padding: 10px 6px;
	}
	.table-expense tbody td, .table-jurnal-custom tbody td {
		vertical-align: middle !important;
		font-size: 13px;
		border: 1px solid #e9ecef;
		padding: 6px;
	}
	.table-expense tfoot td, .table-jurnal-custom tfoot th {
		vertical-align: middle !important;
		font-size: 13px;
		border: 1px solid #d2d6de;
		padding: 8px 10px;
	}
	.table-responsive {
		overflow-x: auto;
		-webkit-overflow-scrolling: touch;
	}
	.summary-field {
		font-weight: bold;
		text-align: right;
		font-size: 14px;
		border-radius: 4px;
	}
	.badge-kasbon {
		background-color: #3c8dbc;
		color: #fff;
		padding: 4px 8px;
		border-radius: 3px;
		font-size: 11px;
		font-weight: 600;
	}
	.badge-expense {
		background-color: #00a65a;
		color: #fff;
		padding: 4px 8px;
		border-radius: 3px;
		font-size: 11px;
		font-weight: 600;
	}
	.btn-flat-custom {
		border-radius: 4px;
		font-weight: 600;
	}
</style>

<div class="tab-content">
	<div class="tab-pane active">
		<div class="form-card">
			<div class="form-card-title">
				<i class="fa fa-file-text-o text-primary"></i> <b>Form Pengajuan & Pertanggungjawaban Expense</b>
			</div>

			<!-- HEADER INFORMATION SECTION -->
			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label class="col-sm-3 control-label">No. Dokumen</label>
						<div class="col-sm-9">
							<input type="text" class="form-control input-sm" id="no_doc" name="no_doc" value="<?php echo (isset($data->no_doc) ? $data->no_doc : ""); ?>" placeholder="Otomatis (System)" readonly>
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-3 control-label">Tanggal <b class="text-red">*</b></label>
						<div class="col-sm-9">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
								<input type="text" class="form-control input-sm tanggal" id="tgl_doc" name="tgl_doc" value="<?php echo (isset($data->tgl_doc) ? $data->tgl_doc : date("Y-m-d")); ?>" placeholder="YYYY-MM-DD" required>
							</div>
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-3 control-label">Keterangan <b class="text-red">*</b></label>
						<div class="col-sm-9">
							<textarea class="form-control input-sm" id="informasi" name="informasi" rows="3" placeholder="Tuliskan keterangan pengeluaran..." required><?php echo (isset($data->informasi) ? $data->informasi : ""); ?></textarea>
							<?php
							if (isset($data->st_reject) && $data->st_reject != '') {
								echo '
								<div class="alert alert-danger alert-dismissible" style="margin-top:10px;">
									<h4><i class="icon fa fa-ban"></i> Alasan Penolakan!</h4>
									' . $data->st_reject . '
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
							<input type="text" class="form-control input-sm" id="bank_id" name="bank_id" value="<?php echo (isset($data->bank_id) ? $data->bank_id : $bank_id); ?>" placeholder="Nama Bank">
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-3 control-label">No. Rekening</label>
						<div class="col-sm-9">
							<input type="text" class="form-control input-sm" id="accnumber" name="accnumber" value="<?php echo (isset($data->accnumber) ? $data->accnumber : $accnumber); ?>" placeholder="Nomor Rekening">
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-3 control-label">Atas Nama</label>
						<div class="col-sm-9">
							<input type="text" class="form-control input-sm" id="accname" name="accname" value="<?php echo (isset($data->accname) ? $data->accname : $accname); ?>" placeholder="Nama Pemilik Rekening">
						</div>
					</div>
					<input type="hidden" id="no_doc_kasbon" name="no_doc_kasbon">
					<input type="hidden" id="idKasbon" name="idKasbon">
				</div>
			</div>

			<!-- TABEL RINCIAN ITEM -->
			<div style="margin-top: 25px;">
				<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
					<span style="font-size: 15px; font-weight: 700; color: #333;">
						<i class="fa fa-list-alt text-primary"></i> Rincian Pengeluaran Barang & Jasa
					</span>
					<div class="stsview">
						<button type="button" class="btn btn-primary btn-sm btn-flat-custom" onclick="add_kasbon()" id="add-kasbon" title="Ambil Data dari Kasbon Sendigs">
							<i class="fa fa-ticket"></i> Ambil Kasbon Sendigs
						</button>
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
								<th width="190">Akun COA / Jenis</th>
								<th width="105">Tanggal</th>
								<th width="200">Barang / Jasa</th>
								<th width="160">Spesifikasi</th>
								<th width="65">Qty</th>
								<th width="115">Harga Satuan</th>
								<th width="130">Total Nominal</th>
								<th width="160">Bon / Bukti</th>
								<th width="45">Aksi</th>
							</tr>
						</thead>
						<tbody id="detail_body">
							<?php
							$idd = 1;
							$total_expense = 0;
							$total_kasbon = 0;
							if (!empty($data_detail)) {
								foreach ($data_detail as $record) {
									$is_kasbon_row = (!empty($record->id_kasbon));
									$row_kasbon_val = $is_kasbon_row ? floatval($record->kasbon) : 0;
									$row_expense_val = !$is_kasbon_row ? floatval($record->expense) : 0;

									$total_kasbon += $row_kasbon_val;
									$total_expense += $row_expense_val;
							?>
									<tr id='tr1_<?= $idd ?>' class='delAll <?= ($is_kasbon_row ? 'kasbonrow' : '') ?>'>
										<td class="text-center">
											<input type='hidden' name='id_kasbon[]' id='id_kasbon_<?= $idd ?>' value='<?= $record->id_kasbon; ?>'>
											<input type="hidden" name="filename[]" id="filename_<?= $idd ?>" value="<?= $record->doc_file; ?>">
											<input type="hidden" name="detail_id[]" id="raw_id_<?= $idd ?>" value="<?= $idd; ?>" class="dtlloop">
											<input type="hidden" name="id_detail[]" id="id_detail_<?= $idd ?>" value="<?= $record->id; ?>" class="dtlloop">
											<?= $idd ?>
										</td>
										<td class="text-center">
											<?php if ($is_kasbon_row): ?>
												<span class="badge-kasbon"><i class="fa fa-ticket"></i> Kasbon</span>
											<?php else: ?>
												<span class="badge-expense"><i class="fa fa-money"></i> Realisasi</span>
											<?php endif; ?>
										</td>
										<td>
											<?php
											if (!$is_kasbon_row) {
												echo form_dropdown('coa[]', $option_coa, (isset($record->coa) ? $record->coa : ''), array('id' => 'coa' . $idd, 'required' => 'required', 'class' => 'form-control select2 input-sm', 'onchange' => 'set_jurnal()'));
											} else {
												echo '<input type="hidden" name="coa[]" id="coa' . $idd . '" value="' . $record->coa . '"><span class="badge bg-blue">' . $record->coa . '</span>';
											}
											?>
										</td>
										<td>
											<input type="text" class="form-control tanggal input-sm" name="tanggal[]" id="tanggal<?= $idd; ?>" value="<?= $record->tanggal; ?>" <?= $is_kasbon_row ? 'readonly' : '' ?>>
										</td>
										<td>
											<textarea class="form-control input-sm" name="deskripsi[]" id="deskripsi_<?= $idd; ?>" rows="2" style="font-size:13px;" <?= $is_kasbon_row ? 'readonly' : '' ?> onblur="set_jurnal()"><?= $record->deskripsi; ?></textarea>
										</td>
										<td>
											<textarea class="form-control input-sm" name="keterangan[]" id="keterangan_<?= $idd; ?>" rows="2" style="font-size:13px;" <?= $is_kasbon_row ? 'readonly' : '' ?>><?= $record->keterangan; ?></textarea>
										</td>
										<td><input type="text" class="form-control divide input-sm text-right" name="qty[]" id="qty_<?= $idd; ?>" value="<?= $record->qty; ?>" onblur="cektotal(<?= $idd; ?>)" <?= $is_kasbon_row ? 'readonly' : '' ?>></td>
										<td><input type="text" class="form-control divide input-sm text-right" name="harga[]" id="harga_<?= $idd; ?>" value="<?= ($is_kasbon_row ? $record->kasbon : $record->harga) ?>" onblur="cektotal(<?= $idd; ?>)" <?= $is_kasbon_row ? 'readonly' : '' ?>></td>
										<td>
											<?php if ($is_kasbon_row): ?>
												<input type="text" class="form-control divide input-sm text-right" value="<?= $row_kasbon_val ?>" tabindex="-1" readonly style="font-weight:bold; color:#3c8dbc; background:#eaf2fd;">
												<input type="hidden" class="subtotal" name="expense[]" id="expense_<?= $idd; ?>" value="0">
												<input type="hidden" class="subkasbon" name="kasbon[]" id="kasbon_<?= $idd; ?>" value="<?= $row_kasbon_val ?>">
											<?php else: ?>
												<input type="text" class="form-control divide subtotal input-sm text-right" name="expense[]" id="expense_<?= $idd; ?>" value="<?= $row_expense_val ?>" tabindex="-1" readonly style="font-weight:bold; color:#00a65a; background:#e8fadf;">
												<input type="hidden" class="subkasbon" name="kasbon[]" id="kasbon_<?= $idd; ?>" value="0">
											<?php endif; ?>
										</td>
										<td class="text-center">
											<?php if (!$is_kasbon_row): 
												$row_files = isset($detail_files[$record->id]) ? $detail_files[$record->id] : [];
											?>
												<div class="file-upload-cell-<?= $idd ?>">
													<label class="btn btn-xs btn-primary btn-flat-custom stsview" style="cursor:pointer; margin-bottom:2px;" title="Pilih File Bon/Bukti">
														<i class="fa fa-folder-open"></i> Pilih File
														<input type="file" id="temp_file_<?= $idd ?>" class="temp-detail-file-picker" data-row="<?= $idd ?>" style="display:none;" accept=".jpg,.jpeg,.png,.pdf" multiple>
													</label>
													
													<!-- Hidden real file input -->
													<input type="file" name="doc_files_<?= $idd ?>[]" id="doc_files_<?= $idd ?>" multiple style="display:none;">

													<!-- Container of new files -->
													<div id="new_files_list_<?= $idd ?>" style="display:flex; flex-direction:column; gap:3px; margin-top:3px;"></div>

													<!-- Container of existing files -->
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
											<?php else: ?>
												<span class="text-muted"><i class="fa fa-minus"></i></span>
											<?php endif; ?>
										</td>
										<td class="text-center">
											<button type='button' class='btn btn-danger btn-xs stsview' data-toggle='tooltip' onClick='delDetail(<?= $idd ?>)' title='Hapus'><i class='fa fa-trash'></i></button>
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
							<tr style="background:#f8fafc; font-weight:bold;">
								<td colspan="9" align="right">TOTAL REALISASI EXPENSE</td>
								<td colspan="2">
									<input type="text" class="form-control divide input-sm summary-field" id="total_expense" name="total_expense" value="<?= $total_expense ?>" placeholder="0" tabindex="-1" readonly style="background:#ffffff; color:#333;">
								</td>
							</tr>
							<tr id="total_kasbon_row" <?= ($total_kasbon > 0) ? "" : "hidden" ?> style="background:#eaf2fd; font-weight:bold;">
								<td colspan="9" align="right" style="color:#2e59d9;"><i class="fa fa-ticket"></i> TOTAL PENGAJUAN KASBON</td>
								<td colspan="2">
									<input type="text" class="form-control divide input-sm summary-field" id="total_kasbon" name="total_kasbon" value="<?= $total_kasbon ?>" placeholder="0" tabindex="-1" readonly style="color:#2e59d9; background:#ffffff;">
								</td>
							</tr>
							<tr id="kontrol_row" <?= (isset($data->lebih_bayar) && $data->lebih_bayar > 0) ? "" : "hidden" ?> style="background:#e8fadf; font-weight:bold;">
								<td colspan="9" align="right" style="color:#00a65a;"><i class="fa fa-reply"></i> LEBIH BAYAR (PENGEMBALIAN KE KANTOR)</td>
								<td colspan="2">
									<input type="text" class="form-control divide input-sm summary-field" onblur="updateGrandTotal()" id="kontrol" placeholder="0" tabindex="-1" value="<?= (isset($data->lebih_bayar)) ? $data->lebih_bayar : "" ?>" style="color:#00a65a; background:#ffffff;">
								</td>
							</tr>
							<tr id="kurang_bayar_row" <?= (isset($data->kurang_bayar) && $data->kurang_bayar > 0) ? "" : "hidden" ?> style="background:#fde8e8; font-weight:bold;">
								<td colspan="9" align="right" style="color:#dd4b39;"><i class="fa fa-exclamation-circle"></i> KURANG BAYAR (REIMBURSE KE KARYAWAN)</td>
								<td colspan="2">
									<input type="text" class="form-control divide input-sm summary-field" id="kurang_bayar" name="kurang_bayar" value="<?= (isset($data->kurang_bayar)) ? $data->kurang_bayar : "" ?>" placeholder="0" tabindex="-1" readonly style="color:#dd4b39; background:#ffffff;">
								</td>
							</tr>
							<tr id="selisih_row" <?= ($total_kasbon > 0) ? "" : "hidden" ?> style="background:#f1f5f9; font-weight:bold;">
								<td colspan="9" align="right">SELISIH KONTROL (KASBON - EXPENSE)</td>
								<td colspan="2">
									<input type="text" class="form-control divide input-sm summary-field" id="grand_total" name="grand_total" value="<?= $grand_total ?>" placeholder="0" tabindex="-1" readonly style="background:#ffffff;">
									<input type="hidden" id="initial_grand_total">
								</td>
							</tr>
						</tfoot>
					</table>
				</div>

				<!-- SECTION PENGEMBALIAN (LEBIH BAYAR) & KETERANGAN (KURANG BAYAR) -->
				<div class="row" style="margin-top: 15px;">
					<div class="col-md-6" id="pengembalian" <?= (isset($data->lebih_bayar) && $data->lebih_bayar > 0) ? "" : "hidden" ?>>
						<input type="hidden" name="pengembalian" id="pengembalian_val" value="2">
						<div class="panel panel-info" style="border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
							<div class="panel-heading" style="font-weight: 700;">
								<i class="fa fa-reply"></i> Pengembalian Kelebihan Kasbon (Transfer ke Rekening Perusahaan)
							</div>
							<div class="panel-body">
								<div class="form-group" style="margin-left:0; margin-right:0; margin-bottom: 0;">
									<label class="control-label">Upload Bukti Transfer Balik <b class="text-red">*</b></label>
									<input type="file" name="bukti_pengembalian[]" class="form-control input-sm" multiple accept=".jpg,.jpeg,.png,.pdf">
									<small class="text-muted"><i class="fa fa-info-circle"></i> Format: JPG, PNG, PDF (Bisa upload beberapa file)</small>
									<?php
									$file = '';
									if (isset($data->bukti_pengembalian) && !empty($data->bukti_pengembalian)) {
										$arr_files = explode(';', $data->bukti_pengembalian);
										foreach ($arr_files as $f_item) {
											if (empty($f_item)) continue;
											if (strpos($f_item, 'pdf') !== false) {
												$file .= '<div style="margin-top:5px;"><a href="' . base_url($f_item) . '" class="btn btn-xs btn-default" target="_blank"><i class="fa fa-file-pdf-o text-red"></i> ' . basename($f_item) . '</a></div>';
											} else {
												$file .= '<div style="margin-top:5px;"><a href="' . base_url($f_item) . '" target="_blank"><img src="' . base_url($f_item) . '" style="max-height:80px; border:1px solid #ddd; padding:2px; border-radius:4px;"></a></div>';
											}
										}
									}
									?>
									<?= $file ?>
								</div>
							</div>
						</div>
					</div>

					<div class="col-md-6" id="section_kurang_bayar" <?= (isset($data->kurang_bayar) && $data->kurang_bayar > 0) ? "" : "hidden" ?>>
						<div class="panel panel-danger" style="border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
							<div class="panel-heading" style="font-weight: 700;">
								<i class="fa fa-exclamation-triangle"></i> Keterangan Kurang Bayar (Reimburse Kantor)
							</div>
							<div class="panel-body">
								<textarea class="form-control input-sm" name="keterangan_kurang_bayar" id="keterangan_kurang_bayar" rows="3" placeholder="Alasan mengapa pengeluaran melebihi kasbon awal (opsional)..."><?= (isset($data->keterangan_kurang_bayar) ? $data->keterangan_kurang_bayar : "") ?></textarea>
							</div>
						</div>
					</div>
				</div>

				<!-- SECTION TABLE JURNAL (HANYA MUNCUL JIKA TERKAIT KASBON) -->
				<div id="section_jurnal" <?= ($total_kasbon > 0) ? "" : "hidden" ?> style="margin-top: 25px;">
					<div class="panel panel-default" style="border-radius: 6px; border: 1px solid #d2d6de; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
						<div class="panel-heading" style="background:#f8fafc; font-weight: 700; color: #2d3748; font-size: 14px;">
							<i class="fa fa-book text-primary"></i> <b>Daftar Jurnal Expense & Pertanggungjawaban Kasbon</b>
						</div>
						<div class="panel-body" style="padding: 15px;">
							<div class="table-responsive">
								<table class="table table-bordered table-striped table-jurnal-custom" width="100%">
									<thead>
										<tr>
											<th width="120" class="text-center">Tanggal Jurnal</th>
											<th width="130" class="text-center">COA</th>
											<th width="230">Nama Account</th>
											<th>Deskripsi / Keterangan</th>
											<th width="140" class="text-right">Debit (Rp)</th>
											<th width="140" class="text-right">Kredit (Rp)</th>
										</tr>
									</thead>
									<tbody class="tbody_jurnal">
									</tbody>
									<tfoot>
										<tr style="background:#f1f5f9; font-weight:bold;">
											<th colspan="4" class="text-center">TOTAL BALANCING</th>
											<th class="text-right ttl_debit">0</th>
											<th class="text-right ttl_kredit">0</th>
										</tr>
									</tfoot>
								</table>
							</div>
						</div>
					</div>
				</div>

				<?php
				if (isset($data_exp_kasbon) && !empty($data_exp_kasbon)) {
					foreach ($data_exp_kasbon as $exp_kasbon) :
						$no_kasbon_detail = 1;
						$this->db->select('a.*, IF(b.code IS NULL, "Pcs", b.code) AS satuan');
						$this->db->from('tr_pr_detail_kasbon a');
						$this->db->join('ms_satuan b', 'b.id = a.unit', 'left');
						$this->db->where('a.id_kasbon', $exp_kasbon['id_kasbon']);
						$get_pr_kasbon_detail = $this->db->get()->result_array();

						if (!empty($get_pr_kasbon_detail)) {
							echo '<div class="rekening-box" style="margin-top:15px;">';
							echo '<h5 style="font-weight:bold; color:#2b6cb0; margin-top:0;"><i class="fa fa-info-circle"></i> Referensi Item PR Kasbon (No PR: ' . $get_pr_kasbon_detail[0]['no_pr'] . ')</h5>';
							echo '<table class="table table-bordered table-condensed table-striped" style="background:#fff;">';
							echo '<thead><tr class="bg-gray"><th class="text-center" width="30">#</th><th>Material Name</th><th class="text-center" width="70">Qty</th><th class="text-center" width="70">Unit</th><th class="text-right" width="130">Price</th><th class="text-right" width="140">Total Price</th></tr></thead>';
							echo '<tbody>';
							foreach ($get_pr_kasbon_detail as $kasbon_detail) :
								echo '<tr>';
								echo '<td class="text-center">' . $no_kasbon_detail . '</td>';
								echo '<td>' . $kasbon_detail['nm_material'] . '</td>';
								echo '<td class="text-center">' . number_format($kasbon_detail['qty']) . '</td>';
								echo '<td class="text-center">' . $kasbon_detail['satuan'] . '</td>';
								echo '<td class="text-right">' . number_format($kasbon_detail['harga']) . '</td>';
								echo '<td class="text-right">' . number_format($kasbon_detail['total_harga']) . '</td>';
								echo '</tr>';
								$no_kasbon_detail++;
							endforeach;
							echo '</tbody></table></div>';
						}
					endforeach;
				}
				?>
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

<!-- MODAL LIHAT BON BUKTI -->
<div class="modal fade" id="modalBonBukti" tabindex="-1" role="dialog" aria-labelledby="modalBonBuktiLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" style="width: 75%;">
		<div class="modal-content" style="border-radius: 8px;">
			<div class="modal-header bg-primary" style="border-radius: 8px 8px 0 0;">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title" id="modalBonBuktiLabel"><i class="fa fa-file-image-o"></i> Daftar Lampiran Bon / Bukti Pengeluaran</h4>
			</div>
			<div class="modal-body" style="padding: 20px;">
				<?php if (!empty($list_bon_bukti)): ?>
					<div class="table-responsive">
						<table class="table table-bordered table-striped" width="100%">
							<thead>
								<tr class="bg-gray">
									<th width="35" class="text-center">#</th>
									<th width="200">Keterangan / Sumber</th>
									<th>Nama File Lampiran</th>
									<th width="120" class="text-center">Preview</th>
									<th width="120" class="text-center">Aksi</th>
								</tr>
							</thead>
							<tbody>
								<?php $bno = 1; foreach ($list_bon_bukti as $bb): 
									$file_url = base_url($bb['path']);
									$is_pdf = (stripos($bb['name'], '.pdf') !== false);
								?>
								<tr>
									<td class="text-center"><?= $bno++ ?></td>
									<td><span class="badge bg-gray text-dark" style="color:#333;"><?= htmlspecialchars($bb['source']) ?></span></td>
									<td><b><?= htmlspecialchars($bb['name']) ?></b></td>
									<td class="text-center">
										<?php if ($is_pdf): ?>
											<a href="<?= $file_url ?>" target="_blank" class="text-red">
												<i class="fa fa-file-pdf-o fa-2x"></i><br><small>Dokumen PDF</small>
											</a>
										<?php else: ?>
											<a href="<?= $file_url ?>" target="_blank">
												<img src="<?= $file_url ?>" style="max-height: 60px; max-width: 90px; border: 1px solid #ddd; padding: 2px; border-radius: 4px;">
											</a>
										<?php endif; ?>
									</td>
									<td class="text-center">
										<a href="<?= $file_url ?>" target="_blank" class="btn btn-xs btn-primary btn-flat-custom" title="Buka File">
											<i class="fa fa-external-link"></i> Buka
										</a>
										<a href="<?= $file_url ?>" download class="btn btn-xs btn-default btn-flat-custom" title="Unduh File">
											<i class="fa fa-download"></i>
										</a>
									</td>
								</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php else: ?>
					<div class="text-center text-muted" style="padding: 30px;">
						<i class="fa fa-file-o fa-3x"></i>
						<p style="margin-top: 10px;">Belum ada file bon / bukti yang dilampirkan pada dokumen ini.</p>
					</div>
				<?php endif; ?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default btn-flat-custom" data-dismiss="modal">Tutup</button>
			</div>
		</div>
	</div>
</div>

<?php
$datacombocoa = "";
foreach ($data_budget as $keys => $val) {
	$datacombocoa .= "<option value='" . $keys . "'>" . $val . "</option>";
}

$datacoa = "";
foreach ($option_coa as $keys => $val) {
	$datacoa .= "<option value='" . $keys . "'>" . $val . "</option>";
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>
<script type="text/javascript">
	var url_save = siteurl + 'expense/save/';
	var url_approve = siteurl + 'expense/approve/';
	var url_set_jurnal = siteurl + 'expense/set_jurnal_expense';
	var nomor = parseInt("<?= $idd ?>");
	$('.divide').divide();
	$('.select2').select2({ width: '100%' });

	var stsview = $('.stsview').val();
	if (stsview == 'view' || stsview == 'approval') {
		$(".stsview").addClass("hidden");
		$("#frm_data :input").prop("disabled", true);
	}

	$(function() {
		$(".tanggal").datepicker({
			todayHighlight: true,
			format: "yyyy-mm-dd",
			showInputs: true,
			autoclose: true
		});
		set_jurnal();
	});

	// Initial calculation check
	var totalKasbon = parseFloat($("#total_kasbon").val()) || 0;
	var grandTotal = parseFloat($("#grand_total").val()) || 0;
	if (totalKasbon > 0 || grandTotal !== 0) {
		$("#total_kasbon_row").show();
		$("#selisih_row").show();
		$("#section_jurnal").show();
	}

	// Save Expense
	$('#frm_data').on('submit', function(e) {
		e.preventDefault();
		var errors = "";

		if ($("#informasi").val() == "") errors = "Keterangan tidak boleh kosong";
		if ($("#tgl_doc").val() == "") errors = "Tanggal Transaksi tidak boleh kosong";
		if ($("#detail_body tr").length === 0) errors = "Rincian expense tidak boleh kosong";

		if (errors !== "") {
			Swal.fire({
				title: "Peringatan",
				text: errors,
				icon: "warning"
			});
			return false;
		}

		Swal.fire({
			title: "Konfirmasi Simpan",
			text: "Apakah data yang Anda masukkan sudah benar?",
			icon: "question",
			showCancelButton: true,
			confirmButtonText: "Ya, Simpan!",
			cancelButtonText: "Batal",
			confirmButtonColor: "#3085d6"
		}).then((next) => {
			if (next.isConfirmed) {
				var formdata = new FormData($('#frm_data')[0]);
				$.ajax({
					url: url_save,
					dataType: "json",
					type: 'POST',
					data: formdata,
					processData: false,
					contentType: false,
					success: function(msg) {
						if (msg['save'] == '1' || msg['save'] === true) {
							Swal.fire({
								title: "Sukses!",
								text: "Data Expense Berhasil Disimpan",
								icon: "success",
								timer: 1500,
								showConfirmButton: false
							});
							window.location.reload();
						} else {
							Swal.fire({
								title: "Gagal!",
								text: msg['message'] || "Data Gagal Disimpan",
								icon: "error"
							});
						}
					},
					error: function(msg) {
						Swal.fire({
							title: "Gagal!",
							text: "Terjadi kesalahan saat memproses data ke server",
							icon: "error"
						});
					}
				});
			}
		});
	});

	function cektotal(id) {
		if (id !== undefined && id !== null) {
			var isKasbonRow = $("#tr1_" + id).hasClass('kasbonrow');
			var sqty = $("#qty_" + id).val() || 0;
			var pref = $("#harga_" + id).val() || 0;
			var subtotal = (parseFloat(sqty.toString().replace(/,/g, '')) * parseFloat(pref.toString().replace(/,/g, '')));
			if (isKasbonRow) {
				$("#kasbon_" + id).val(subtotal);
				$("#expense_" + id).val(0);
			} else {
				$("#expense_" + id).val(subtotal);
				$("#kasbon_" + id).val(0);
			}
		}

		var sumExpense = 0;
		$('.subtotal').each(function() {
			var v = $(this).val();
			if (v) sumExpense += Number(v.toString().replace(/,/g, ''));
		});
		$("#total_expense").val(sumExpense);

		var sumKasbon = 0;
		$('.subkasbon').each(function() {
			var v = $(this).val();
			if (v) sumKasbon += Number(v.toString().replace(/,/g, ''));
		});
		$("#total_kasbon").val(sumKasbon);

		if (sumKasbon > 0) {
			$("#total_kasbon_row").show();
			$("#selisih_row").show();
			$("#section_jurnal").show();
			$("#total_kasbon").prop("disabled", false);
			$("#grand_total").prop("disabled", false);
		} else {
			$("#total_kasbon_row").hide();
			$("#selisih_row").hide();
			$("#section_jurnal").hide();
			$("#total_kasbon").prop("disabled", true);
			$("#grand_total").prop("disabled", true);
		}

		var selisih = sumKasbon - sumExpense;
		$("#grand_total").val(selisih);

		if (sumKasbon > 0) {
			if (selisih > 0) {
				// LEBIH BAYAR (Kasbon > Expense) -> Pengembalian kasbon ke kantor (Transfer)
				$("#initial_grand_total").val(selisih);
				$("#kontrol").val(selisih);
				$("#kontrol_row").show();
				$("#pengembalian").show();
				$("#kurang_bayar_row").hide();
				$("#kurang_bayar").val(0);
				$("#section_kurang_bayar").hide();

				$("input[name='pengembalian']").val("2").prop("disabled", false);
				$("input[name='bukti_pengembalian[]']").prop("disabled", false);
				$("input[name='kontrol']").prop("required", true).prop("disabled", false);
			} else if (selisih < 0) {
				// KURANG BAYAR (Expense > Kasbon) -> Reimburse kantor ke karyawan
				var kurangBayarVal = Math.abs(selisih);
				$("#kurang_bayar").val(kurangBayarVal);
				$("#kurang_bayar_row").show();
				$("#section_kurang_bayar").show();

				$("#pengembalian").hide();
				$("#kontrol_row").hide();
				$("#kontrol").val(0);
				$("input[name='pengembalian']").prop("disabled", true);
				$("input[name='bukti_pengembalian[]']").prop("disabled", true).val('');
				$("input[name='kontrol']").prop("required", false).prop("disabled", true);
			} else {
				// Pas / Balance (Expense == Kasbon)
				$("#pengembalian").hide();
				$("#kontrol_row").hide();
				$("#kurang_bayar_row").hide();
				$("#section_kurang_bayar").hide();
				$("input[name='pengembalian']").prop("disabled", true);
				$("input[name='bukti_pengembalian[]']").prop("disabled", true).val('');
				$("input[name='kontrol']").prop("required", false).prop("disabled", true);
			}
		} else {
			// TANPA KASBON (Direct Expense / Reimbursement Murni)
			$("#pengembalian").hide();
			$("#kontrol_row").hide();
			$("#kurang_bayar_row").hide();
			$("#section_kurang_bayar").hide();
			$("input[name='pengembalian']").prop("disabled", true);
			$("input[name='bukti_pengembalian[]']").prop("disabled", true).val('');
			$("input[name='kontrol']").prop("required", false).prop("disabled", true);
		}

		$(".divide").divide();
		set_jurnal();
	}

	function set_jurnal() {
		var sumKasbon = 0;
		$('.subkasbon').each(function() {
			var v = $(this).val();
			if (v) sumKasbon += Number(v.toString().replace(/,/g, ''));
		});

		// Jika tidak ada kasbon, sembunyikan section jurnal dan return
		if (sumKasbon <= 0) {
			$("#section_jurnal").hide();
			return;
		} else {
			$("#section_jurnal").show();
		}

		var formdata = new FormData($('#frm_data')[0]);
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

	function updateGrandTotal() {
		var initialGrandTotal = parseFloat($("#initial_grand_total").val()) || 0;
		var kontrolVal = parseFloat($("#kontrol").val()) || 0;
		var newGrandTotal = initialGrandTotal - kontrolVal;
		$("#grand_total").val(newGrandTotal);
		set_jurnal();
	}

	function add_kasbon() {
		var nama = $("#nama").val();
		var departement = $("#departement").val();

		$.ajax({
			url: siteurl + 'expense/get_kasbon/' + nama + '/' + departement + '/<?= (isset($data->no_doc) ? $data->no_doc : ""); ?>',
			type: "POST",
			dataType: "json",
			success: function(data) {
				var tbody = '';
				if (data && data.length > 0) {
					for (var i = 0; i < data.length; i++) {
						tbody += '<tr>';
						tbody += '<td class="text-center">' + (i + 1) + '</td>';
						tbody += '<td><b>' + data[i].no_doc + '</b></td>';
						tbody += '<td>' + data[i].tgl_doc + '</td>';
						tbody += '<td>' + (data[i].keperluan || '-') + '</td>';
						tbody += '<td>' + (data[i].keterangan || '-') + '</td>';
						tbody += '<td class="text-right" style="font-weight:bold; color:#2e59d9;">' + Number(data[i].jumlah_kasbon).toLocaleString('en-US') + '</td>';
						tbody += '<td style="display:none">' + (data[i].bank_id || '') + '</td>';
						tbody += '<td style="display:none">' + (data[i].accnumber || '') + '</td>';
						tbody += '<td style="display:none">' + (data[i].accname || '') + '</td>';
						tbody += '<td style="display:none">' + (data[i].id || '') + '</td>';
						tbody += '<td class="text-center"><button type="button" class="btn btn-primary btn-xs btn-flat-custom" onclick="selectKasbon(' + i + ')"><i class="fa fa-check"></i> Pilih</button></td>';
						tbody += '</tr>';
					}
				} else {
					tbody = '<tr><td colspan="7" class="text-center text-muted"><i>Tidak ada data kasbon Sendigs yang siap diproses.</i></td></tr>';
				}
				$('#tableKasbon tbody').html(tbody);
				$('#modalKasbon').modal('show');
			},
			error: function() {
				Swal.fire({
					title: "Gagal!",
					text: 'Gagal mengambil data kasbon dari server.',
					icon: "warning"
				});
			}
		});
	}

	function selectKasbon(index) {
		var row = $('#tableKasbon tbody tr').eq(index);
		var no_doc = row.find('td').eq(1).text();
		var tgl_doc = row.find('td').eq(2).text();
		var keperluan = row.find('td').eq(3).text();
		var keterangan = row.find('td').eq(4).text();
		var jumlah = row.find('td').eq(5).text().replace(/,/g, '');
		var bank_id = row.find('td').eq(6).text();
		var accnumber = row.find('td').eq(7).text();
		var accname = row.find('td').eq(8).text();
		var id = row.find('td').eq(9).text();

		var nomor = $("#detail_body tr").length + 1;
		var datacoa = "<?= $datacoa ?>";

		var Rows = "<tr id='tr1_" + nomor + "' class='delAll kasbonrow'>";
		Rows += "<td class='text-center'><input type='hidden' name='id_kasbon[]' id='id_kasbon_" + nomor + "' value='" + no_doc + "'>";
		Rows += "<input type='hidden' name='detail_id[]' id='raw_id_" + nomor + "' value='" + nomor + "'>";
		Rows += "<input type='hidden' name='id_detail[]' id='id_detail_" + nomor + "' value='" + nomor + "'>";
		Rows += nomor + " </td>";
		Rows += "<td class='text-center'><span class='badge-kasbon'><i class='fa fa-ticket'></i> Kasbon</span></td>";
		Rows += "<td>";
		Rows += "<select name='coa[]' id='coa_" + nomor + "' class='form-control select2 input-sm' onchange='set_jurnal()'><?= $datacoa ?></select>";
		Rows += "</td>";
		Rows += "<td>";
		Rows += "<input type='text' class='form-control tanggal input-sm' name='tanggal[]' id='tanggal_" + nomor + "' tabindex='-1' value='" + tgl_doc + "' readonly />";
		Rows += "</td>";
		Rows += "<td>";
		Rows += "<textarea class='form-control input-sm' name='deskripsi[]' id='deskripsi_" + nomor + "' rows='2' style='font-size:13px;' readonly>" + keperluan + "</textarea>";
		Rows += "<input type='hidden' class='form-control input-sm' name='id_expense_detail[]' id='id_expense_detail_" + nomor + "' value='' />";
		Rows += "</td>";
		Rows += "<td>";
		Rows += "<textarea class='form-control input-sm' name='keterangan[]' id='keterangan_" + nomor + "' rows='2' style='font-size:13px;' readonly>" + keterangan + "</textarea>";
		Rows += "</td>";
		Rows += "<td>";
		Rows += "<input type='text' class='form-control divide input-sm text-right' name='qty[]' value='1' id='qty_" + nomor + "' readonly />";
		Rows += "</td>";
		Rows += "<td>";
		Rows += "<input type='text' class='form-control divide input-sm text-right' name='harga[]' value='" + jumlah + "' id='harga_" + nomor + "' readonly />";
		Rows += "</td>";
		Rows += "<td>";
		Rows += "<input type='text' class='form-control divide input-sm text-right' value='" + jumlah + "' tabindex='-1' readonly style='font-weight:bold; color:#3c8dbc; background:#eaf2fd;' />";
		Rows += "<input type='hidden' class='subtotal' name='expense[]' id='expense_" + nomor + "' value='0' />";
		Rows += "<input type='hidden' class='subkasbon' name='kasbon[]' value='" + jumlah + "' id='kasbon_" + nomor + "' />";
		Rows += "</td>";
		Rows += "<td class='text-center'><span class='text-muted'><i class='fa fa-minus'></i></span></td>";
		Rows += "<td class='text-center'>";
		Rows += "<button type='button' class='btn btn-danger btn-xs' data-toggle='tooltip' onClick='delDetail(" + nomor + ")' title='Hapus'><i class='fa fa-trash'></i></button>";
		Rows += "</td>";
		Rows += "</tr>";

		//isi data rekening
		if (bank_id) $('#bank_id').val(bank_id);
		if (accnumber) $('#accnumber').val(accnumber);
		if (accname) $('#accname').val(accname);
		$('#no_doc_kasbon').val(no_doc);
		$('#idKasbon').val(id);

		$('#detail_body').append(Rows);
		$('#modalKasbon').modal('hide');
		$('.select2').select2({ width: '100%' });
		$(".divide").divide();
		cektotal();
	}

	function add_detail() {
		var nomor = $("#detail_body tr").length + 1;
		var datacombocoa = "<?= $datacombocoa ?>";
		var datacoa = "<?= $datacoa ?>";
		var Rows = "<tr id='tr1_" + nomor + "' class='delAll'>";
		Rows += "<td class='text-center'><input type='hidden' name='id_kasbon[]' id='id_kasbon_" + nomor + "' value=''>";
		Rows += "<input type='hidden' name='detail_id[]' id='raw_id_" + nomor + "' value='" + nomor + "' class='dtlloop'>";
		Rows += "<input type='hidden' name='id_detail[]' id='id_detail_" + nomor + "' value='" + nomor + "' class='dtlloop'>";
		Rows += nomor + "</td>";
		Rows += "<td class='text-center'><span class='badge-expense'><i class='fa fa-money'></i> Realisasi</span></td>";
		Rows += "<td>";
		Rows += "<select name='coa[]' id='coa_" + nomor + "' required='required' class='form-control select2 input-sm' onchange='set_jurnal()'><?= $datacoa ?></select>";
		Rows += "</td>";
		Rows += "<td>";
		Rows += "<input type='text' class='form-control tanggal input-sm' placeholder='YYYY-MM-DD' name='tanggal[]' id='tanggal_" + nomor + "' value='<?= date("Y-m-d") ?>' />";
		Rows += "</td>";
		Rows += "<td>";
		Rows += "<textarea class='form-control input-sm' placeholder='Nama barang/jasa...' name='deskripsi[]' id='deskripsi_" + nomor + "' rows='2' style='font-size:13px;' onblur='set_jurnal()'></textarea>";
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

	function delDetail(row) {
		$('#tr1_' + row).remove();
		$('#detail_body tr').each(function(index) {
			var newRowNum = index + 1;
			$(this).attr('id', 'tr1_' + newRowNum);
			$(this).find('td:first').contents().filter(function() {
				return this.nodeType == 3;
			}).first().replaceWith(newRowNum.toString());
		});
		cektotal();
	}

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
				$.post(url_approve + id, function(result) {
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
