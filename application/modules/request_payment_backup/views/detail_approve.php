<?php
$ENABLE_ADD     = has_permission('Request_Payment_Approval.Add');
$ENABLE_MANAGE  = has_permission('Request_Payment_Approval.Manage');
$ENABLE_VIEW    = has_permission('Request_Payment_Approval.View');
$ENABLE_DELETE  = has_permission('Request_Payment_Approval.Delete');

if ($type == 'expense') {
	$keterangan = $header->informasi;
	$no_doc = $header->no_doc;
	$tgl_doc = $header->tgl_doc;

	$bank_id = $header->bank_id;
	$accnumber = $header->accnumber;
	$accname = $header->accname;
} elseif ($type == 'kasbon') {
	$keterangan = $header->keperluan;
	$no_doc = $header->no_doc;
	$tgl_doc = $header->tgl_doc;

	$bank_id = $header->bank_id;
	$accnumber = $header->accnumber;
	$accname = $header->accname;
} elseif ($type == 'transportasi') {
	$keterangan = 'Transportasi';
	$no_doc = $header->no_doc;
	$tgl_doc = $header->tgl_doc;

	$bank_id = $header->bank_id;
	$accnumber = $header->accnumber;
	$accname = $header->accname;
} elseif ($type == 'nonpo') {
	$keterangan = $header->info;
	$no_doc = $header->no_doc;
	$tgl_doc = $header->tanggal_doc;

	$bank_id = $header->bank_id;
	$accnumber = $header->accnumber;
	$accname = $header->accname;
} elseif ($type == 'periodik') {
	$keterangan = $header->keterangan;
	$no_doc = $header->no_doc;
	$tgl_doc = $header->tanggal;

	$bank_id = $header->bank_id;
	$accnumber = $header->accnumber;
	$accname = $header->accname;
} elseif ($type == 'direct_payment') {
	$keterangan = $header->deskripsi;
	$no_doc = $header->no_doc;
	$tgl_doc = $header->tgl_doc;

	$bank_id = $header->bank;
	$accnumber = $header->bank_number;
	$accname = $header->bank_account;
}

?>
<!-- <script src="//cdn.rawgit.com/rainabba/jquery-table2excel/1.1.0/dist/jquery.table2excel.min.js"></script> -->
<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<?= form_open($this->uri->uri_string(), array('id' => 'frm_data', 'name' => 'frm_data', 'role' => 'form', 'class' => 'form-horizontal')); ?>
<div class="box">
	<div class="box-body">

		<div class="row">
			<input type="hidden" name="id" value="<?= $header->id; ?>">
			<input type="hidden" name="tipe" value="<?= $type; ?>">
			<input type="hidden" name="tingkat_approval" value="2">
			<div class="col-md-6">
				<div class="row">
					<div class="col-md-4 text-right" style="margin-bottom: 1rem;"><label for="" class="control-label">Nomor Dokumen </label></div>
					<div class="col-md-6" style="margin-bottom: 1rem;">
						<input type="text" name="no_doc" class="form-control" readonly value="<?= $no_doc; ?>">
					</div>

					<div class="col-md-4 text-right" style="margin-bottom: 1rem;"><label for="" class="control-label">Keterangan</label></div>
					<div class="col-md-6" style="margin-bottom: 1rem;">
						<input type="text" name="informasi" class="form-control" readonly value="<?= ($keterangan) ?: ''; ?>">
					</div>

					<div class="col-md-4 text-right" style="margin-bottom: 1rem;"><label for="" class="control-label">Biaya Admin</label></div>
					<div class="col-md-6" style="margin-bottom: 1rem;">
						<input type="text" name="admin_bank" class="form-control" readonly value="<?= number_format(($data_req_payment['admin_bank']), 2) ?>">
					</div>

					<div class="col-md-4 text-right" style="margin-bottom: 1rem;"><label for="" class="control-label">Dokumen Req Payment</label></div>
					<div class="col-md-6" style="margin-bottom: 1rem;">
						<?php
						if ($data_req_payment['link_doc'] !== '' && $data_req_payment['link_doc'] !== null) {
							if (file_exists('./assets/expense/' . $data_req_payment['link_doc'])) {
								echo '<a href="' . base_url('assets/expense/' . $data_req_payment['link_doc']) . '" class="btn btn-sm btn-primary" target="_blank">
										<i class="fa fa-download"></i> Download
									</a>';
							}
						}
						?>
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="row">
					<div class="col-md-4 text-right"><label for="" class="control-label">Bank</label></div>
					<div class="col-md-6">
						<input type="text" name="date" class="form-control" style="margin-bottom: 1rem;" readonly value="<?= $data_req_payment['bank_name']; ?>">
					</div>
					<div class="col-md-4 text-right"><label for="" class="control-label">Tgl.</label></div>
					<div class="col-md-6">
						<input type="text" name="date" class="form-control" style="margin-bottom: 1rem;" readonly value="<?= $tgl_doc; ?>">
					</div>
					<div class="col-md-4 text-right" style="margin-bottom: 1rem;"><label for="" class="control-label">Reject Reason</label></div>
					<div class="col-md-6" style="margin-bottom: 1rem;">
						<input type="text" name="reject_reason" class="form-control reject_reason" value="">
					</div>
				</div>

			</div>

		</div>

		<div class="table-responsive">
			<table id="mytabledata" class="table table-bordered">
				<thead>
					<tr>
						<th width="5">#</th>
						<th class="exclass">COA</th>
						<th class="exclass">Barang/Jasa</th>
						<th>Tanggal Transaksi</th>
						<th class="exclass">Jumlah</th>
						<th class="exclass">Currency</th>
						<th class="exclass"></th>
						<th class="exclass">Bon Bukti</th>
						<th class="exclass">
							<div class="checkbox">
								<label><input class="master_check" type="checkbox" checked>Semua</label>
							</div>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php
					if (!empty($details)) {
						$n = $gTotal = 0;
						foreach ($details as $dtl) : $n++;
							$coa = (isset($dtl->coa)) ? $dtl->coa : '';
							$nm_coa = (isset($list_coa[$coa]) && $coa !== '') ? $list_coa[$coa] : '';
							if ($type == 'expense') :
								$harga  = $dtl->harga;
								if (isset($dtl->id_kasbon) && $dtl->id_kasbon !== '') {
									$harga = $dtl->kasbon * -1;
								}

								$gTotal += ($data_req_payment['jumlah'] + $data_req_payment['admin_bank'] - $data_req_payment['total_pph']); ?>
								<tr>
									<td><?= $n; ?></td>
									<td><?= $dtl->coa . ' - ' . $nm_coa; ?></td>
									<td><?= $dtl->deskripsi; ?> <?= (isset($dtl->id_kasbon) && $dtl->id_kasbon !== '') ? "<b>(Kasbon)</b>" : null ?></td>
									<td><?= $dtl->tanggal; ?></td>
									<td><?= $dtl->qty; ?></td>
									<td><?= $data_req_payment['currency']; ?></td>
									<td class="text-left">
										<table class="w-100">
											<tr>
												<td>Nilai Pengajuan</td>
												<td class="text-center" style="min-width: 50px;">:</td>
												<td class="text-right">
													<input type="text" name="" id="" class="form-control form-control-sm text-right" value="<?= number_format($data_req_payment['jumlah'], 2) ?>" readonly>
												</td>
											</tr>
											<tr>
												<td>Nilai PPh</td>
												<td class="text-center" style="min-width: 50px;">:</td>
												<td class="text-right">
													<input type="text" name="" id="" class="form-control form-control-sm text-right" value="<?= number_format($data_req_payment['total_pph'], 2) ?>" readonly>
												</td>
											</tr>
											<tr>
												<td>Bank Charge</td>
												<td class="text-center" style="min-width: 50px;">:</td>
												<td class="text-right">
													<input type="text" name="" id="" class="form-control form-control-sm text-right" value="<?= number_format($data_req_payment['admin_bank'], 2) ?>" readonly>
												</td>
											</tr>
											<tr>
												<td>Net Payment</td>
												<td class="text-center" style="min-width: 50px;">:</td>
												<td class="text-right">
													<input type="text" name="" id="" class="form-control form-control-sm text-right" value="<?= number_format(($data_req_payment['jumlah'] + $data_req_payment['admin_bank'] - $data_req_payment['total_pph']), 2) ?>" readonly>
												</td>
											</tr>
										</table>
									</td>
									<td class="text-center">
										<?php

										$get_ros = $this->db->get_where('tr_ros', ['id' => $dtl->no_doc])->row_array();
										$get_invoice = $this->db->get_where('tr_invoice_po', ['id' => $dtl->no_doc])->row_array();
										if (!empty($get_ros)) {
											if (file_exists($get_ros['link_doc'])) {
												echo '<a href="' . base_url('./' . $get_ros['link_doc']) . '" target="_blank"><i class="fa fa-download"></i></a>';
											}
										} else if (!empty($get_invoice)) {
											if (file_exists($get_invoice['link_doc'])) {
												echo '<a href="' . base_url('./' . $get_invoice['link_doc']) . '" target="_blank"><i class="fa fa-download"></i></a>';
											}
										} else {
											if (file_exists('./assets/expense/' . $dtl->doc_file) && $dtl->doc_file !== '') {
										?>
												<a href="<?= base_url('./assets/expense/') . $dtl->doc_file; ?>" target="_blank"><i class="fa fa-download"></i></a>
										<?php
											}
										}
										?>
									</td>
									<td>

										<input type="checkbox" checked value="<?= $dtl->id; ?>" name="item[<?= $n; ?>][id]" class="check_item" id="check_<?= $dtl->id; ?>">

									</td>
								</tr>
								<?php elseif ($type == 'kasbon') :

								if ($kasbon_pr == '1') {
								?>

									<tr>
										<td><?= $n; ?></td>
										<td><?= $dtl->coa . ' - ' . $nm_coa; ?></td>
										<td><?= $dtl->keperluan; ?></td>
										<td><?= $dtl->tgl_doc; ?></td>
										<td>-</td>
										<td><?= $data_req_payment['currency']; ?></td>
										<td class="text-right">-</td>
										<td class="text-right">-</td>
										<td class="text-center"><a href="<?= base_url('assets/expense/') . $dtl->doc_file; ?>" target="_blank"><i class="fa fa-download"></i></a></td>
										<td>
											<?php if ($dtl->status == '2') : ?>
												<input type="checkbox" checked value="<?= $dtl->id; ?>" name="item[<?= $n; ?>][id]" class="check_item" id="check_<?= $dtl->id; ?>">
											<?php elseif ($dtl->status == '3') : ?>
												<label for="" class="label bg-maroon">Process</label>
											<?php elseif ($dtl->status == '4') : ?>
												<label for="" class="label bg-green">PAID</label>
											<?php else : ?>
												<label for="" class="label bg-gray"><span class="text-muted">Undefined</span></label>
											<?php endif; ?>
										</td>
									</tr>
									<?php

									foreach ($data_detail_pr_kasbon as $detail_kasbon_pr) :
										echo '<tr>';
										echo '<td></td>';
										echo '<td></td>';
										echo '<td>' . $detail_kasbon_pr->nm_material . '</td>';
										echo '<td></td>';
										echo '<td>' . number_format($detail_kasbon_pr->qty) . '</td>';
										echo '<td>' . $data_req_payment['currency'] . '</td>';
										echo '<td class="text-right">' . number_format($detail_kasbon_pr->total_harga) . '</td>';
										echo '<td class="text-right">' . number_format($data_req_payment['admin_bank']) . '</td>';
										echo '<td></td>';
										echo '<td></td>';
										echo '</tr>';

										$gTotal += $detail_kasbon_pr->total_harga;
									endforeach;
								} else {
									$gTotal += ($dtl->jumlah_kasbon + $data_req_payment['admin_bank'] - $data_req_payment['total_pph']);
									?>

									<tr>
										<td><?= $n; ?></td>
										<td><?= $dtl->coa . ' - ' . $nm_coa; ?></td>
										<td><?= $dtl->keperluan; ?></td>
										<td><?= $dtl->tgl_doc; ?></td>
										<td>1</td>
										<td><?= $data_req_payment['currency']; ?></td>
										<td class="text-left">
											<table class="w-100">
												<tr>
													<td>Nilai Pengajuan</td>
													<td class="text-center" style="min-width: 50px;">:</td>
													<td class="text-right">
														<input type="text" name="" id="" class="form-control form-control-sm text-right" value="<?= number_format($data_req_payment['jumlah'], 2) ?>">
													</td>
												</tr>
												<tr>
													<td>Nilai PPh</td>
													<td class="text-center" style="min-width: 50px;">:</td>
													<td class="text-right">
														<input type="text" name="" id="" class="form-control form-control-sm text-right" value="<?= number_format($data_req_payment['total_pph'], 2) ?>">
													</td>
												</tr>
												<tr>
													<td>Bank Charge</td>
													<td class="text-center" style="min-width: 50px;">:</td>
													<td class="text-right">
														<input type="text" name="" id="" class="form-control form-control-sm text-right" value="<?= number_format($data_req_payment['admin_bank'], 2) ?>">
													</td>
												</tr>
												<tr>
													<td>Net Payment</td>
													<td class="text-center" style="min-width: 50px;">:</td>
													<td class="text-right">
														<input type="text" name="" id="" class="form-control form-control-sm text-right" value="<?= number_format(($data_req_payment['jumlah'] + $data_req_payment['admin_bank'] - $data_req_payment['total_pph']), 2) ?>">
													</td>
												</tr>
											</table>
										</td>
										<td class="text-center"><a href="<?= base_url('assets/expense/') . $dtl->doc_file; ?>" target="_blank"><i class="fa fa-download"></i></a></td>
										<td>
											<?php if ($dtl->status == '2') : ?>
												<input type="checkbox" checked value="<?= $dtl->id; ?>" name="item[<?= $n; ?>][id]" class="check_item" id="check_<?= $dtl->id; ?>">
											<?php elseif ($dtl->status == '3') : ?>
												<label for="" class="label bg-maroon">Process</label>
											<?php elseif ($dtl->status == '4') : ?>
												<label for="" class="label bg-green">PAID</label>
											<?php else : ?>
												<label for="" class="label bg-gray"><span class="text-muted">Undefined</span></label>
											<?php endif; ?>
										</td>
									</tr>

								<?php
								}
								?>
							<?php elseif ($type == 'transportasi') :
								$gTotal += ($dtl->jumlah_kasbon + $data_req_payment['admin_bank'] - $data_req_payment['total_pph']); ?>
								<tr>
									<td><?= $n; ?></td>
									<td></td>
									<td><?= $dtl->keperluan; ?></td>
									<td><?= $dtl->tgl_doc; ?></td>
									<td>1</td>
									<td><?= $data_req_payment['currency']; ?></td>
									<td class="text-left">
										<table class="w-100">
											<tr>
												<td>Nilai Pengajuan</td>
												<td class="text-center" style="min-width: 50px;">:</td>
												<td class="text-right">
													<input type="text" name="" id="" class="form-control form-control-sm text-right" value="<?= number_format($dtl->jumlah_kasbon, 2) ?>" readonly>
												</td>
											</tr>
											<tr>
												<td>Nilai PPh</td>
												<td class="text-center" style="min-width: 50px;">:</td>
												<td class="text-right">
													<input type="text" name="" id="" class="form-control form-control-sm text-right" value="<?= number_format($data_req_payment['total_pph'], 2) ?>" readonly>
												</td>
											</tr>
											<tr>
												<td>Bank Charge</td>
												<td class="text-center" style="min-width: 50px;">:</td>
												<td class="text-right">
													<input type="text" name="" id="" class="form-control form-control-sm text-right" value="<?= number_format($data_req_payment['admin_bank'], 2) ?>" readonly>
												</td>
											</tr>
											<tr>
												<td>Net Payment</td>
												<td class="text-center" style="min-width: 50px;">:</td>
												<td class="text-right">
													<input type="text" name="" id="" class="form-control form-control-sm text-right" value="<?= number_format(($dtl->jumlah_kasbon + $data_req_payment['admin_bank'] - $data_req_payment['total_pph']), 2) ?>" readonly>
												</td>
											</tr>
										</table>
									</td>
									<td class="text-center">
										<?php
										if (file_exists('./assets/expense/' . $dtl->doc_file) && $dtl->doc_file !== '') {
											echo '<a href="' . base_url('./assets/expense/') . $dtl->doc_file . '" target="_blank"><i class="fa fa-download"></i></a>';
										}
										?>
									</td>
									<td>
										<?php if ($dtl->status == '1') : ?>
											<input type="checkbox" checked value="<?= $dtl->id; ?>" name="item[<?= $n; ?>][id]" class="check_item" id="check_<?= $dtl->id; ?>">
										<?php elseif ($dtl->status == '2') : ?>
											<label for="" class="label bg-maroon">Process</label>
										<?php elseif ($dtl->status == '3') : ?>
											<label for="" class="label bg-green">PAID</label>
										<?php else : ?>
											<label for="" class="label bg-gray"><span class="text-muted">Undefined</span></label>
										<?php endif; ?>
									</td>
								</tr>

							<?php elseif ($type == 'nonpo') :
								$gTotal += ($dtl->total_request + $data_req_payment['admin_bank'] - $data_req_payment['total_pph']); ?>
								<tr>
									<td><?= $n; ?></td>
									<td><?= $dtl->coa . ' - ' . $nm_coa; ?></td>
									<td><?= $dtl->deskripsi; ?></td>
									<td><?= $dtl->tgl_pr; ?></td>
									<td>1</td>
									<td><?= $data_req_payment['currency']; ?></td>
									<td class="text-left">
										<table class="w-100">
											<tr>
												<td>Nilai Pengajuan</td>
												<td class="text-center" style="min-width: 50px;">:</td>
												<td class="text-right">
													<input type="text" name="" id="" class="form-control form-control-sm text-right" value="<?= number_format($dtl->total_request, 2) ?>">
												</td>
											</tr>
											<tr>
												<td>Nilai PPh</td>
												<td class="text-center" style="min-width: 50px;">:</td>
												<td class="text-right">
													<input type="text" name="" id="" class="form-control form-control-sm text-right" value="<?= number_format($data_req_payment['total_pph'], 2) ?>">
												</td>
											</tr>
											<tr>
												<td>Bank Charge</td>
												<td class="text-center" style="min-width: 50px;">:</td>
												<td class="text-right">
													<input type="text" name="" id="" class="form-control form-control-sm text-right" value="<?= number_format($data_req_payment['admin_bank'], 2) ?>">
												</td>
											</tr>
											<tr>
												<td>Net Payment</td>
												<td class="text-center" style="min-width: 50px;">:</td>
												<td class="text-right">
													<input type="text" name="" id="" class="form-control form-control-sm text-right" value="<?= number_format(($dtl->total_request + $data_req_payment['admin_bank'] - $data_req_payment['total_pph']), 2) ?>">
												</td>
											</tr>
										</table>
									</td>
									<td class="text-center">
										<?php
										if (file_exists('./assets/expense/' . $dtl->doc_file) && $dtl->doc_file !== '') {
											echo '<a href="' . base_url('./assets/expense/') . $dtl->doc_file . '" target="_blank"><i class="fa fa-download"></i></a>';
										}
										?>
									</td>
									<td>

										<input type="checkbox" checked value="<?= $dtl->id; ?>" name="item[<?= $n; ?>][id]" class="check_item" id="check_<?= $dtl->id; ?>">

									</td>
								</tr>

							<?php elseif ($type == 'periodik') :
								$gTotal += ($data_req_payment['jumlah'] + $data_req_payment['admin_bank'] - $data_req_payment['total_pph']); ?>
								<tr>
									<td><?= $n; ?></td>
									<td><?= $dtl->coa . ' - ' . $nm_coa; ?></td>
									<td><?= $dtl->keterangan; ?></td>
									<td><?= $dtl->tanggal; ?></td>
									<td>1</td>
									<td><?= $data_req_payment['currency']; ?></td>
									<td class="text-left">
										<table class="w-100">
											<tr>
												<td>Nilai Pengajuan</td>
												<td class="text-center" style="min-width: 50px;">:</td>
												<td class="text-right">
													<input type="text" name="" id="" class="form-control form-control-sm text-right" value="<?= number_format($data_req_payment['jumlah'], 2) ?>" readonly>
												</td>
											</tr>
											<tr>
												<td>Nilai PPh</td>
												<td class="text-center" style="min-width: 50px;">:</td>
												<td class="text-right">
													<input type="text" name="" id="" class="form-control form-control-sm text-right" value="<?= number_format($data_req_payment['total_pph'], 2) ?>" readonly>
												</td>
											</tr>
											<tr>
												<td>Bank Charge</td>
												<td class="text-center" style="min-width: 50px;">:</td>
												<td class="text-right">
													<input type="text" name="" id="" class="form-control form-control-sm text-right" value="<?= number_format($data_req_payment['admin_bank'], 2) ?>" readonly>
												</td>
											</tr>
											<tr>
												<td>Net Payment</td>
												<td class="text-center" style="min-width: 50px;">:</td>
												<td class="text-right">
													<input type="text" name="" id="" class="form-control form-control-sm text-right" value="<?= number_format(($data_req_payment['jumlah'] + $data_req_payment['admin_bank'] - $data_req_payment['total_pph']), 2) ?>" readonly>
												</td>
											</tr>
										</table>
									</td>
									<td class="text-center">
										<?php
										if (file_exists('./assets/bayar_rutin/' . $dtl->doc_file) && $dtl->doc_file !== '') {
											echo '<a href="' . base_url('./assets/bayar_rutin/') . $dtl->doc_file . '" target="_blank"><i class="fa fa-download"></i></a>';
										}
										?>
									</td>
									<td>

										<input type="checkbox" checked value="<?= $dtl->id; ?>" name="item[<?= $n; ?>][id]" class="check_item" id="check_<?= $dtl->id; ?>">

									</td>
								</tr>
							<?php endif;
							if ($type == 'direct_payment') {
							?>

								<tr>
									<td><?= $n; ?></td>
									<td><?= $coa . ' - ' . $nm_coa; ?></td>
									<td><?= $dtl->deskripsi; ?></td>
									<td><?= $dtl->tgl_doc; ?></td>
									<td><?= number_format($dtl->grand_total, 2) ?></td>
									<td><?= $data_req_payment['currency']; ?></td>
									<td class="text-right"><?= number_format($dtl->grand_total, 2) ?></td>

									<td class="text-center"><a href="<?= base_url('assets/expense/') . $data_req_payment['link_doc']; ?>" target="_blank"><i class="fa fa-download"></i></a></td>
									<td>
										<?php if ($dtl->sts == '2') : ?>
											<input type="checkbox" checked value="<?= $dtl->id; ?>" name="item[<?= $n; ?>][id]" class="check_item" id="check_<?= $dtl->id; ?>">
										<?php elseif ($dtl->sts == '3') : ?>
											<label for="" class="label bg-maroon">Process</label>
										<?php elseif ($dtl->sts == '4') : ?>
											<label for="" class="label bg-green">PAID</label>
										<?php else : ?>
											<label for="" class="label bg-gray"><span class="text-muted">Undefined</span></label>
										<?php endif; ?>
									</td>
								</tr>

					<?php
								$gTotal += $dtl->grand_total;
							}
						endforeach;
					}  ?>
				</tbody>
				<tfoot>
					<tr class="bg-blue">
						<th colspan="6" class="text-right">Total</th>
						<th class="text-right"><?= number_format($gTotal); ?></th>
						<th colspan="3" class="text-center"></th>
					</tr>
				</tfoot>
			</table>
			<div class="col-md-4">
				<table class="table table-bordered">
					<thead>
						<tr>
							<th class="text-center" colspan="3">Info Transfer</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>Bank</td>
							<td class="text-center">:</td>
							<td><?= $bank_id ?></td>
						</tr>
						<tr>
							<td>Account Number</td>
							<td class="text-center">:</td>
							<td><?= $accnumber ?></td>
						</tr>
						<tr>
							<td>Account Name</td>
							<td class="text-center">:</td>
							<td><?= $accname ?></td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="col-md-8"></div>
			<div class="col-md-12"></div>
			<div class="">
				<button type="button" class="btn btn-success btn-sm text-right pull-right" id="process"><i class="fa fa-save">&nbsp;</i>Process</button>
				<button type="button" class="btn btn-danger btn-sm text-right pull-right" style="margin-right: 0.5em;" id="reject"><i class="fa fa-close">&nbsp;</i>Reject</button>
				<a href="<?= base_url($this->uri->segment(1) . '/list_approve_management'); ?>" class="btn btn-default btn-sm pull-right" style="margin-right: 0.5em;"><i class="fa fa-reply">&nbsp;</i>Back</a>
			</div>
		</div>
	</div>
	<!-- <div> &nbsp;<button type="button" id="btnxls" class="btn btn-default">Export Excel</button><br /><br /></div> -->
	<!-- /.box-body -->
</div>
<?= form_close() ?>
<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>
<script type="text/javascript">
	var url_save = siteurl + 'request_payment/save_approval';
	var url_reject = siteurl + 'request_payment/reject_approval';
	$('.divide').divide();

	$(document).on('click', '.master_check', function() {
		const checked = $(this).is(':checked');
		$('.check_item').prop('checked', false)
		if (checked) {
			$('.check_item').prop('checked', true)
		}
	})

	//Save
	$(document).on('click', '#process', function(e) {
		var errors = "";
		if ($("#bank_coa").val() == "0") errors = "Bank tidak boleh kosong";
		const check = $('.check_item').is(':checked')

		if (check) {
			swal({
					title: "Anda Yakin?",
					text: "Item Akan Di Approve!",
					type: "info",
					showCancelButton: true,
					confirmButtonText: "Ya, Approve!",
					cancelButtonText: "Tidak!",
					closeOnConfirm: false,
					closeOnCancel: true
				},
				function(isConfirm) {
					if (isConfirm) {
						var formdata = new FormData($('#frm_data')[0]);
						$.ajax({
							url: url_save,
							dataType: "json",
							type: 'POST',
							data: formdata,
							processData: false,
							contentType: false,
							success: function(msg) {
								if (msg['save'] == '1') {
									swal({
										title: "Sukses!",
										text: "Data Berhasil Di Approve",
										type: "success",
										timer: 1500,
										showConfirmButton: false
									});
									location.href = siteurl + active_controller + 'list_approve_management';
								} else {
									swal({
										title: "Gagal!",
										text: "Data Gagal Di Approve",
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
		} else {
			swal("Warning!", "Pilih item yang akan di Approve!", "warning", 3000);
			return false;
		}
	});

	$(document).on('click', '#reject', function(e) {
		var errors = "";
		if ($("#bank_coa").val() == "0") errors = "Bank tidak boleh kosong";
		const check = $('.check_item').is(':checked')

		var reject_reason = $('.reject_reason').val();

		if (check && reject_reason !== '') {
			swal({
					title: "Anda Yakin?",
					text: "Item Akan Di Reject!",
					type: "info",
					showCancelButton: true,
					confirmButtonText: "Ya, Reject!",
					cancelButtonText: "Tidak!",
					closeOnConfirm: false,
					closeOnCancel: true
				},
				function(isConfirm) {
					if (isConfirm) {
						var formdata = new FormData($('#frm_data')[0]);
						$.ajax({
							url: url_reject,
							dataType: "json",
							type: 'POST',
							data: formdata,
							processData: false,
							contentType: false,
							success: function(msg) {
								if (msg['save'] == '1') {
									swal({
										title: "Sukses!",
										text: "Data Berhasil Di Reject",
										type: "success",
										timer: 1500,
										showConfirmButton: false
									});
									location.href = siteurl + active_controller + 'list_approve_management';
								} else {
									swal({
										title: "Gagal!",
										text: "Data Gagal Di Reject",
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
		} else {
			swal("Warning!", "Pilih item yang akan di Reject dan pastikan Reject Reason terisi!", "warning", 3000);
			return false;
		}
	});
	$("#btnxls").click(function() {
		$("#mytabledata").table2excel({
			exclude: ".exclass",
			name: "Weekly Budget",
			filename: "WeeklyBudget.xls", // do include extension
			preserveColors: false // set to true if you want background colors and font colors preserved
		});
	});
</script>