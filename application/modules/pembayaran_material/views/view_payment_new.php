<?php
$header = $results['result_header'];
$payments = $results['result_payment'];
$list_jurnal = isset($results['list_jurnal']) ? $results['list_jurnal'] : [];
$bank_charge = isset($results['bank_charge']) ? $results['bank_charge'] : 0;
$no_payment_paid = isset($results['no_payment_paid']) ? $results['no_payment_paid'] : ($header->id_payment ?? '-');

// Helper function to format document type (remove underscore/hyphen, title case)
function format_doc_type($type_str) {
	if (empty($type_str)) return '-';
	$low = strtolower(trim($type_str));
	if ($low === 'nonpo' || $low === 'non_po') return 'Non PO';
	if ($low === 'pr' || $low === 'po') return strtoupper($low);
	$clean = str_replace(['_', '-'], ' ', $type_str);
	return ucwords(strtolower($clean));
}

// Find Supplier / Requestor name
$supplier_names = [];
foreach ($payments as $p) {
	if (!empty($p->nm_supplier)) {
		$supplier_names[] = $p->nm_supplier;
	} elseif (!empty($p->created_by)) {
		$supplier_names[] = $p->created_by;
	} elseif (!empty($p->nama)) {
		$supplier_names[] = $p->nama;
	}
}
$supplier_names = array_unique($supplier_names);
$display_supplier = !empty($supplier_names) ? implode(', ', $supplier_names) : ($header->nm_supplier ?? ($header->created_by ?? '-'));

// Match Bank Name using ms_bank source
$bank_name = '-';
if (!empty($results['list_bank'])) {
	foreach ($results['list_bank'] as $b) {
		$coa_val = $b->coa_bank ?? ($b->no_perkiraan ?? '');
		$b_id = $b->id ?? '';
		if ((!empty($coa_val) && $coa_val == $header->coa_bank) || (!empty($b_id) && $b_id == $header->coa_bank)) {
			$nama_bank_str = !empty($b->nama_bank) ? $b->nama_bank : 'Bank';
			$rekening_str = !empty($b->rekening) ? ' - ' . $b->rekening : '';
			$nama_str = !empty($b->nama) ? ' (' . $b->nama . ')' : '';
			$coa_str = !empty($b->nm_coa) ? ' [' . $coa_val . ' - ' . $b->nm_coa . ']' : (!empty($coa_val) ? ' [' . $coa_val . ']' : '');
			$bank_name = $nama_bank_str . $rekening_str . $nama_str . $coa_str;
			break;
		}
	}
}

if ($bank_name === '-') {
	if (!empty($header->nm_coa_bank)) {
		$bank_name = (!empty($header->coa_bank) ? $header->coa_bank . ' - ' : '') . $header->nm_coa_bank;
	} elseif (!empty($header->coa_bank)) {
		$bank_name = $header->coa_bank;
	}
}

$mata_uang = !empty($header->mata_uang) ? $header->mata_uang : 'IDR';
$kurs = !empty($header->kurs_payment) ? $header->kurs_payment : 1;
$tgl_bayar_formatted = !empty($header->tgl_bayar) ? date('d F Y', strtotime($header->tgl_bayar)) : '-';
?>

<div class="box box-primary">
	<div class="box-header with-border">
		<h3 class="box-title"><i class="fa fa-info-circle text-primary"></i> Detail Payment: <strong><?= $no_payment_paid ?></strong></h3>
		<a href="<?= base_url('pembayaran_material/payment_list') ?>" class="btn btn-sm btn-default pull-right"><i class="fa fa-arrow-left"></i> Kembali ke Payment List</a>
	</div>
	<div class="box-body">
		<!-- Header Info Grid -->
		<div class="well well-sm" style="background-color: #fcfcfc; border: 1px solid #e3e6f0; border-radius: 6px; padding: 15px; margin-bottom: 20px;">
			<div class="row">
				<div class="col-md-6">
					<table class="table table-sm table-borderless" style="margin-bottom: 0;">
						<tr>
							<th width="35%" style="border: none; padding: 6px 4px; color: #495057;">No Payment</th>
							<td width="5%" style="border: none; padding: 6px 0;">:</td>
							<td style="border: none; padding: 6px 4px;">
								<span class="badge bg-navy" style="font-size: 13px; padding: 6px 12px;"><?= $no_payment_paid ?></span>
							</td>
						</tr>
						<tr>
							<th style="border: none; padding: 6px 4px; color: #495057;">Tanggal Bayar</th>
							<td style="border: none; padding: 6px 0;">:</td>
							<td style="border: none; padding: 6px 4px;">
								<strong><?= $tgl_bayar_formatted ?></strong>
							</td>
						</tr>
						<tr>
							<th style="border: none; padding: 6px 4px; color: #495057;">Supplier / Penerima</th>
							<td style="border: none; padding: 6px 0;">:</td>
							<td style="border: none; padding: 6px 4px;">
								<?= htmlspecialchars($display_supplier) ?>
							</td>
						</tr>
						<tr>
							<th style="border: none; padding: 6px 4px; color: #495057;">Keterangan Pembayaran</th>
							<td style="border: none; padding: 6px 0;">:</td>
							<td style="border: none; padding: 6px 4px;">
								<div class="text-muted" style="white-space: pre-wrap;"><?= !empty($header->keterangan_pembayaran) ? htmlspecialchars($header->keterangan_pembayaran) : '-' ?></div>
							</td>
						</tr>
						<tr>
							<th style="border: none; padding: 6px 4px; color: #495057;">Dokumen Bukti Bayar</th>
							<td style="border: none; padding: 6px 0;">:</td>
							<td style="border: none; padding: 6px 4px;">
								<?php if (!empty($header->link_doc) && file_exists('assets/expense/' . $header->link_doc)): ?>
									<a href="<?= base_url('assets/expense/' . $header->link_doc) ?>" target="_blank" class="btn btn-xs btn-primary"><i class="fa fa-download"></i> Unduh Dokumen (<?= $header->link_doc ?>)</a>
								<?php else: ?>
									<span class="text-muted"><i class="fa fa-minus"></i> Tidak ada file lampiran</span>
								<?php endif; ?>
							</td>
						</tr>
					</table>
				</div>
				<div class="col-md-6">
					<table class="table table-sm table-borderless" style="margin-bottom: 0;">
						<tr>
							<th width="35%" style="border: none; padding: 6px 4px; color: #495057;">Bank Pembayar</th>
							<td width="5%" style="border: none; padding: 6px 0;">:</td>
							<td style="border: none; padding: 6px 4px;">
								<strong class="text-primary"><?= $bank_name ?></strong>
							</td>
						</tr>
						<tr>
							<th style="border: none; padding: 6px 4px; color: #495057;">Mata Uang</th>
							<td style="border: none; padding: 6px 0;">:</td>
							<td style="border: none; padding: 6px 4px;">
								<span class="badge bg-blue"><?= $mata_uang ?></span> (Kurs: <?= number_format($kurs, 2) ?>)
							</td>
						</tr>
						<tr>
							<th style="border: none; padding: 6px 4px; color: #495057;">Total Pengajuan</th>
							<td style="border: none; padding: 6px 0;">:</td>
							<td style="border: none; padding: 6px 4px;">
								<strong>Rp <?= number_format($header->total_payment ?? $header->jumlah, 2) ?></strong>
							</td>
						</tr>
						<tr>
							<th style="border: none; padding: 6px 4px; color: #495057;">Total Nilai Bayar (Bank)</th>
							<td style="border: none; padding: 6px 0;">:</td>
							<td style="border: none; padding: 6px 4px;">
								<strong class="text-green" style="font-size: 15px;">Rp <?= number_format($header->payment_bank, 2) ?></strong>
							</td>
						</tr>
						<tr>
							<th style="border: none; padding: 6px 4px; color: #495057;">Selisih & Admin Charge</th>
							<td style="border: none; padding: 6px 0;">:</td>
							<td style="border: none; padding: 6px 4px;">
								Selisih: <strong>Rp <?= number_format($header->selisih ?? 0, 2) ?></strong> &nbsp;|&nbsp;
								Biaya Bank: <strong>Rp <?= number_format($bank_charge, 2) ?></strong>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>

		<!-- Detail Tagihan Table -->
		<div class="box box-solid box-default" style="border: 1px solid #d2d6de; margin-bottom: 25px;">
			<div class="box-header with-border" style="background-color: #f7f7f7;">
				<h4 class="box-title" style="font-size: 15px; font-weight: 600;"><i class="fa fa-list text-blue"></i> Rincian Dokumen / Tagihan yang Dibayar</h4>
			</div>
			<div class="box-body table-responsive no-padding">
				<table class="table table-bordered table-striped table-hover" style="margin-bottom: 0;">
					<thead>
						<tr style="background-color: #3c8dbc; color: #fff;">
							<th class="text-center" width="40px">#</th>
							<th class="text-center">No Dokumen</th>
							<th class="text-center">Request By / Supplier</th>
							<th class="text-center">Tgl Dokumen</th>
							<th class="text-center">Keperluan</th>
							<th class="text-center">Tipe</th>
							<th class="text-right">Nilai Pengajuan</th>
							<th class="text-right">PPN</th>
							<th class="text-right">PPH</th>
							<th class="text-right">Nilai Bayar</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$no = 1;
						$tot_pengajuan = 0;
						$tot_ppn = 0;
						$tot_pph = 0;
						$tot_bayar = 0;

						foreach ($payments as $item) {
							// Metadata fallback per tipe dokumen jika di payment_approve belum terisi
							$tgl_doc = !empty($item->tgl_doc) ? date('d F Y', strtotime($item->tgl_doc)) : (!empty($item->created_on) ? date('d F Y', strtotime($item->created_on)) : '-');
							$req_by = !empty($item->nm_supplier) ? $item->nm_supplier : (!empty($item->nama) ? $item->nama : (!empty($item->created_by) ? $item->created_by : '-'));
							$keperluan = !empty($item->keperluan) ? $item->keperluan : (!empty($item->keterangan_pembayaran) ? $item->keterangan_pembayaran : '-');
							$tipe_formatted = format_doc_type($item->tipe);

							$nilai_pengajuan = ($item->jumlah > 0) ? $item->jumlah : $item->payment_bank;
							$ppn = (float) $item->total_ppn;
							$pph = (float) $item->total_pph;
							$nilai_bayar = ($item->payment_bank > 0) ? $item->payment_bank : $nilai_pengajuan;

							$tot_pengajuan += $nilai_pengajuan;
							$tot_ppn += $ppn;
							$tot_pph += $pph;
							$tot_bayar += $nilai_bayar;

							echo '<tr>';
							echo '<td class="text-center">' . $no . '</td>';
							echo '<td class="text-center"><strong>' . htmlspecialchars($item->no_doc) . '</strong></td>';
							echo '<td class="text-left">' . htmlspecialchars($req_by) . '</td>';
							echo '<td class="text-center">' . $tgl_doc . '</td>';
							echo '<td class="text-left">' . htmlspecialchars($keperluan) . '</td>';
							echo '<td class="text-center"><span class="badge bg-gray" style="font-size: 11px; padding: 4px 8px;">' . htmlspecialchars($tipe_formatted) . '</span></td>';
							echo '<td class="text-right">' . number_format($nilai_pengajuan, 2) . '</td>';
							echo '<td class="text-right">' . ($ppn > 0 ? number_format($ppn, 2) : '-') . '</td>';
							echo '<td class="text-right">' . ($pph > 0 ? number_format($pph, 2) . ' <small class="text-muted">(' . $item->tipe_pph . ')</small>' : '-') . '</td>';
							echo '<td class="text-right"><strong>' . number_format($nilai_bayar, 2) . '</strong></td>';
							echo '</tr>';

							$no++;
						}
						?>
					</tbody>
					<tfoot>
						<tr style="background-color: #f4f4f4; font-weight: bold;">
							<td colspan="6" class="text-right">TOTAL :</td>
							<td class="text-right">Rp <?= number_format($tot_pengajuan, 2) ?></td>
							<td class="text-right">Rp <?= number_format($tot_ppn, 2) ?></td>
							<td class="text-right">Rp <?= number_format($tot_pph, 2) ?></td>
							<td class="text-right text-green" style="font-size: 14px;">Rp <?= number_format($tot_bayar, 2) ?></td>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>

		<!-- Jurnal Transaksi Table -->
		<div class="box box-solid box-default" style="border: 1px solid #d2d6de;">
			<div class="box-header with-border" style="background-color: #f7f7f7;">
				<h4 class="box-title" style="font-size: 15px; font-weight: 600;"><i class="fa fa-book text-green"></i> Jurnal Transaksi Pembayaran</h4>
			</div>
			<div class="box-body table-responsive no-padding">
				<table class="table table-bordered table-striped table-hover" style="margin-bottom: 0;">
					<thead>
						<tr style="background-color: #00a65a; color: #fff;">
							<th class="text-center" width="40px">#</th>
							<th class="text-center">No Jurnal</th>
							<th class="text-center">Tanggal Jurnal</th>
							<th class="text-center">Jenis Transaksi / Company</th>
							<th class="text-center">No Perkiraan (COA)</th>
							<th class="text-left">Nama Akun</th>
							<th class="text-left">Keterangan</th>
							<th class="text-right" width="140px">Debit (Rp)</th>
							<th class="text-right" width="140px">Kredit (Rp)</th>
						</tr>
					</thead>
					<tbody>
						<?php
						if (!empty($list_jurnal)) {
							$j_no = 1;
							$total_debit = 0;
							$total_kredit = 0;

							foreach ($list_jurnal as $jr) {
								$debit = (float) $jr->debit;
								$kredit = (float) $jr->kredit;
								$total_debit += $debit;
								$total_kredit += $kredit;

								$tgl_jurnal_fmt = !empty($jr->tgl_jurnal) ? date('d F Y', strtotime($jr->tgl_jurnal)) : '-';
								$jenis_or_comp = !empty($jr->jenis_transaksi) ? $jr->jenis_transaksi : (!empty($jr->nm_company) ? $jr->nm_company : '-');

								echo '<tr>';
								echo '<td class="text-center">' . $j_no . '</td>';
								echo '<td class="text-center"><span class="badge bg-gray">' . htmlspecialchars($jr->no_jurnal ?? '-') . '</span></td>';
								echo '<td class="text-center">' . $tgl_jurnal_fmt . '</td>';
								echo '<td class="text-center">' . htmlspecialchars($jenis_or_comp) . '</td>';
								echo '<td class="text-center"><strong>' . htmlspecialchars($jr->coa ?? '-') . '</strong></td>';
								echo '<td class="text-left">' . htmlspecialchars($jr->nm_coa ?? '-') . '</td>';
								echo '<td class="text-left">' . htmlspecialchars($jr->keterangan ?? '-') . '</td>';
								echo '<td class="text-right">' . ($debit > 0 ? number_format($debit, 2) : '-') . '</td>';
								echo '<td class="text-right">' . ($kredit > 0 ? number_format($kredit, 2) : '-') . '</td>';
								echo '</tr>';

								$j_no++;
							}
						} else {
							echo '<tr><td colspan="9" class="text-center text-muted" style="padding: 20px;"><em>Tidak ada data jurnal yang tersimpan untuk transaksi payment ini.</em></td></tr>';
						}
						?>
					</tbody>
					<?php if (!empty($list_jurnal)): ?>
					<tfoot>
						<tr style="background-color: #f4f4f4; font-weight: bold;">
							<td colspan="7" class="text-right">TOTAL JURNAL :</td>
							<td class="text-right text-navy">Rp <?= number_format($total_debit, 2) ?></td>
							<td class="text-right text-navy">Rp <?= number_format($total_kredit, 2) ?></td>
						</tr>
						<?php if (round($total_debit, 2) === round($total_kredit, 2)): ?>
						<tr style="background-color: #e8f5e9;">
							<td colspan="9" class="text-center text-green" style="font-weight: 600;">
								<i class="fa fa-check-circle"></i> Status Jurnal: <strong>BALANCE (Seimbang)</strong>
							</td>
						</tr>
						<?php else: ?>
						<tr style="background-color: #ffebee;">
							<td colspan="9" class="text-center text-red" style="font-weight: 600;">
								<i class="fa fa-exclamation-triangle"></i> Status Jurnal: <strong>TIDAK BALANCE (Selisih: Rp <?= number_format(abs($total_debit - $total_kredit), 2) ?>)</strong>
							</td>
						</tr>
						<?php endif; ?>
					</tfoot>
					<?php endif; ?>
				</table>
			</div>
		</div>

		<!-- Action Back Button -->
		<div class="row" style="margin-top: 20px;">
			<div class="col-md-12 text-center">
				<a href="<?= base_url('pembayaran_material/payment_list') ?>" class="btn btn-default btn-flat" style="min-width: 140px;"><i class="fa fa-arrow-left"></i> Kembali</a>
			</div>
		</div>
	</div>
</div>