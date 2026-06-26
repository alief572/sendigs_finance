<?php
$hide_table_jurnal_petty_cash = 'd-none';
// if (!empty($results['jurnal_refill_petty_cash'])) {
// 	$hide_table_jurnal_petty_cash = '';
// }

$kode_supplier = [];
$nm_supplier = [];



foreach ($results['result_payment'] as $item) {



	$get_rec_invoice = $this->db->get_where('tr_invoice_po', ['id' => $item->no_doc])->row();

	if (!empty($get_rec_invoice)) {
		if (strpos($get_rec_invoice->no_po, 'TRS1') !== false) {
			$arr_no_incoming = str_replace(', ', ',', $get_rec_invoice->no_po);
			$get_no_po = $this->db
				->select('a.no_ipp')
				->from('tr_incoming_check a')
				->where_in('a.kode_trans', explode(',', $arr_no_incoming))
				->get()
				->result();

			$arr_no_po = [];
			foreach ($get_no_po as $item_no_po) {
				$arr_no_po[] = $item_no_po->no_ipp;
			}

			$arr_no_po = implode(',', $arr_no_po);
			$arr_no_po = str_replace(', ', ',', $arr_no_po);

			$get_no_surat = $this->db->query("SELECT a.no_surat FROM tr_purchase_order a WHERE a.no_po IN ('" . str_replace(",", "','", $arr_no_po) . "')")->result();
			foreach ($get_no_surat as $item_no_surat) {
				$no_po[] = $item_no_surat->no_surat;
			}
		} else {
			$no_po[] = $get_rec_invoice->no_po;
		}
	}

	if (!empty($no_po)) {
		$get_nm_supplier = $this->db
			->select('b.kode_supplier, b.nama')
			->from('tr_purchase_order a')
			->join('new_supplier b', 'b.kode_supplier = a.id_suplier', 'left')
			->where_in('a.no_surat', $no_po)
			->group_by('b.kode_supplier')
			->get()
			->result();
		foreach ($get_nm_supplier as $item_supplier) {
			$kode_supplier[$item_supplier->kode_supplier] = $item_supplier->kode_supplier;
			$nm_supplier[] = $item_supplier->nama;
		}
	}
}
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css" integrity="sha512-yVvxUQV0QESBt1SyZbNJMAwyKvFTLMyXSyBHDO4BG5t7k/Lw34tyqlSDlKIrIENIzCl+RVUNjmCPG+V/GMesRw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
	td {
		padding: 5px 5px 5px 5px;
	}

	.d-none {
		display: none;
	}
</style>
<form action="" id="frm-data" enctype="multipart/form-data">
	<input type="hidden" name="id_payment" class="id_payment" value="<?= $results['id_payment'] ?>">
	<div class="box box-primary">
		<div class="box-header">
			<table class="" style="width: 100%;" border="0">
				<tr>
					<td width="15%" style="">Tgl Bayar</td>
					<td width="5%" class="text-center">:</td>
					<td width="25%">
						<input type="date" name="tgl_bayar" id="" class="form-control form-control-sm tgl_bayar" value="<?= date('Y-m-d') ?>">
					</td>
					<td width="15%" style="">Supplier</td>
					<td width="5%" class="text-center">:</td>
					<td width="25%">
						<input type="hidden" name="supplier_input" class="supplier_input" value="<?= implode(',', $kode_supplier) ?>">
						<input type="hidden" name="nm_supplier_input" class="nm_supplier_input" value="<?= implode(',', $nm_supplier) ?>">
						<select name="supplier" id="" class="form-control form-control-sm supplier" disabled>
							<option value="">- Supplier Name -</option>
							<?php
							foreach ($results['list_supplier'] as $item_supplier) {
								$selected = (isset($kode_supplier[$item_supplier->kode_supplier])) ? 'selected' : '';
								echo '<option value="' . $item_supplier->kode_supplier . '" ' . $selected . '>' . $item_supplier->nama . '</option>';
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td width="15%" style="">Keterangan Pembayaran</td>
					<td width="5%" class="text-center">:</td>
					<td width="25%">
						<textarea name="keterangan_pembayaran" id="" class="form-control form-control-sm keterangan_pembayaran"></textarea>
					</td>
					<td width="15%" style="">Pilih Bank</td>
					<td width="5%" class="text-center">:</td>
					<td width="25%">
						<select name="bank" id="" class="form-control form-control-sm bank" onchange="set_jurnal_refill('<?= $results['id_payment'] ?>')">
							<option value="">- Bank -</option>
							<?php
							foreach ($results['list_bank'] as $item_bank) {
								echo '<option value="' . $item_bank->id . '">(' . $item_bank->rekening . ' a/n ' . $item_bank->nama . ') - ' . $item_bank->nama_bank . '</option>';
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td width="15%" style="">Mata Uang</td>
					<td width="5%" class="text-center">:</td>
					<td width="25%">
						<select name="mata_uang" id="" class="form-control form-control-sm mata_uang">
							<option value="">- Mata Uang -</option>
							<?php
							foreach ($results['list_mata_uang'] as $item_mata_uang) {
								echo '<option value="' . $item_mata_uang->kode . '">' . $item_mata_uang->kode . '</option>';
							}
							?>
						</select>
					</td>
					<td width="15%" style="">Payment Bank</td>
					<td width="5%" class="text-center">:</td>
					<td width="25%">
						<input type="text" name="payment_bank" id="" class="form-control form-control-sm text-right input_payment_bank auto_num" value="0">
					</td>
				</tr>
				<tr>
					<td width="15%" style="">Kurs</td>
					<td width="5%" class="text-center">:</td>
					<td width="25%">
						<input type="text" name="kurs_payment" id="" class="form-control form-control-sm text-right auto_num">
					</td>
				</tr>
				<!-- <tr>
				<td colspan="3"></td>
				<td width="15%" style="">Kurs</td>
				<td width="5%" class="text-center">:</td>
				<td width="25%">
					<input type="text" name="kurs" id="" class="form-control form-control-sm text-right auto_num" value="0">
				</td>
			</tr> -->
			</table>
		</div>
		<div class="box-body">
			<table class="table table-bordered table-striped" id="mytabledata" width='100%'>
				<thead>
					<tr class='bg-blue'>
						<th class="text-center">Supplier</th>
						<th class="text-center">Nomor Dokumen</th>
						<th class="text-center">Request Payment</th>
						<th class="text-center" colspan="2">PPH</th>
						<th class="text-center">PPN</th>
						<th class="text-center">DPP</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$total_payment = 0;
					$total_ppn = 0;
					$total_pph = 0;
					$total_payment_bank = 0;
					$ttl_bank_charge = 0;
					$no = 1;
					foreach ($results['result_payment'] as $item) {

						$nm_supplier = [];

						$get_rec_invoice = $this->db->get_where('tr_invoice_po', ['id' => $item->no_doc])->row();
						if ($get_rec_invoice && isset($get_rec_invoice->kurs)) {
							$kurs_invoice = $get_rec_invoice->kurs;
							$ppn = $get_rec_invoice->nilai_ppn;
						} else {
							$kurs_invoice = 1;
							$ppn = 0;
						}


						$nilai_utuh = 0;
						$persen_progress = 1;
						if (!empty($get_rec_invoice) && $get_rec_invoice->id_top !== '') {
							$get_top = $this->db->get_where('tr_top_po', ['id' => $get_rec_invoice->id_top])->row();
							if (!empty($get_top)) {
								$persen_progress = $get_top->progress;
							}
						}
						if (!empty($get_rec_invoice)) {
							if (strpos($get_rec_invoice->no_po, 'TRS1') !== false) {
								$arr_no_incoming = str_replace(', ', ',', $get_rec_invoice->no_po);
								$get_no_po = $this->db
									->select('a.no_ipp')
									->from('tr_incoming_check a')
									->where_in('a.kode_trans', explode(',', $arr_no_incoming))
									->get()
									->result();

								$arr_no_po = [];
								foreach ($get_no_po as $item_no_po) {
									$arr_no_po[] = $item_no_po->no_ipp;
								}

								$arr_no_po = implode(',', $arr_no_po);
								$arr_no_po = str_replace(', ', ',', $arr_no_po);

								$get_no_surat = $this->db->query("SELECT a.no_surat FROM tr_purchase_order a WHERE a.no_po IN ('" . str_replace(",", "','", $arr_no_po) . "')")->result();
								foreach ($get_no_surat as $item_no_surat) {
									$no_po[] = $item_no_surat->no_surat;
								}

								$get_incoming_check_detail = $this->db
									->select('a.qty_order, b.hargasatuan, b.persen_disc as item_disc, c.persen_disc as po_disc')
									->from('tr_incoming_check_detail a')
									->join('dt_trans_po b', 'b.id = a.id_po_detail', 'left')
									->join('tr_purchase_order c', 'c.no_po = b.no_po', 'left')
									->where_in('a.kode_trans', $arr_no_incoming)
									->get()
									->result();

								foreach ($get_incoming_check_detail as $item_detail) {
									$persen_disc = $item_detail->item_disc;
									if ($item_detail->item_disc <= 0) {
										$persen_disc = $item_detail->po_disc;
									}
									$nilai_after_disc = $item_detail->hargasatuan;
									if ($persen_disc > 0) {
										$nilai_after_disc = ($item_detail->hargasatuan - ($item_detail->hargasatuan * $item_detail->persen_disc / 100));
									}
									$nilai_utuh += ($nilai_after_disc * $item_detail->qty_order);
								}
							} else {
								$no_po[] = $get_rec_invoice->no_po;

								$get_nilai_utuh = $this->db
									->select('a.hargatotal, a.nilai_disc')
									->from('tr_purchase_order a')
									->where('a.no_surat', $get_rec_invoice->no_po)
									->get()
									->result();

								foreach ($get_nilai_utuh as $item_nilai_utuh) {
									$nilai_utuh += ($item_nilai_utuh->hargatotal - $item_nilai_utuh->nilai_disc);
								}
							}
						}

						if (!empty($no_po)) {
							$get_nm_supplier = $this->db
								->select('b.nama as nm_supplier')
								->from('tr_purchase_order a')
								->join('new_supplier b', 'b.kode_supplier = a.id_suplier', 'left')
								->where_in('a.no_surat', $no_po)
								->group_by('b.nama')
								->get()
								->result();
							foreach ($get_nm_supplier as $item_supplier) {
								$nm_supplier[] = $item_supplier->nm_supplier;
							}
						}

						$nm_supplier = implode(', ', $nm_supplier);

						if ($ppn != 0) {
							$nilai_ppn = $ppn;
						} else {
							$nilai_ppn = 0;
						}

						// if($nilai_ppn <= 0) {
						// 	$nilai_ppn = ($item->jumlah * 11 / 100);
						// }

						echo '<tr>';
						echo '<td class="text-center">' . $nm_supplier . '</td>';
						echo '<td class="text-center">
						<input type="hidden" name="dt[' . $no . '][id_payment]" value="' . $item->id . '">
						<input type="hidden" name="dt[' . $no . '][kurs_invoice]" value="' . $kurs_invoice . '">
						
						' . $item->no_doc . '</td>';
						echo '<td class="text-right">
					<input type="hidden" class="jumlah_col_' . $item->id . '">
					<input type="hidden" class="payment_bank_' . $item->id . '" value="' . $item->jumlah . '">
					' . number_format($item->jumlah, 2) . '
					</td>';
						echo '<td>';
						echo '<select name="dt[' . $no . '][tipe_pph]" class="form-control form-control-sm chosen">';
						echo '<option value="1">PPH 21</option>';
						echo '</select>';
						echo '</td>';
						echo '<td>';
						echo '<input type="hidden" class="nilai_utuh_' . $item->id . '" value="' . $nilai_utuh . '">';
						echo '<input type="hidden" class="persen_progress_' . $item->id . '" value="' . $persen_progress . '">';
						echo '<input type="text" class="form-control form-control-sm text-right auto_num nilai_pph nilai_pph_' . $item->id . ' change_nilai_pph" name="dt[' . $no . '][nilai_pph]" data-id="' . $item->id . '">';
						echo '</td>';
						echo '<td class="text-right">';
						echo '<input type="text" name="dt[' . $no . '][nilai_ppn]" class="form-control form-control-sm text-right auto_num change_nilai_ppn nilai_ppn nilai_ppn_' . $item->id . '" data-id="' . $item->id . '" value="' . $nilai_ppn . '">';
						echo '</td>';
						echo '<td class="text-right payment_col_' . $item->id . '">' . number_format($item->jumlah - $nilai_ppn, 2) . '</td>';
						echo '</tr>';

						$total_payment += ($item->jumlah - $nilai_ppn);
						$total_ppn += ($nilai_ppn);
						$total_payment_bank += ($item->jumlah);
						$ttl_bank_charge += ($item->admin_bank);

						$no++;
					}

					$kontrol = (0 - $total_payment - $total_ppn + 0 - $ttl_bank_charge);
					?>
				</tbody>
				<tbody>
					<tr>
						<td colspan="5"></td>
						<td>Total Payment</td>
						<td class="text-right">
							<?= number_format($total_payment, 2) ?>
						</td>
					</tr>
					<tr>
						<td colspan="5"></td>
						<td>Selisih</td>
						<td class="text-right selisih_col"><?= number_format($total_payment - 0, 2) ?></td>
					</tr>
					<tr>
						<td colspan="5"></td>
						<td>Bank Charge</td>
						<td>
							<input type="text" name="bank_charge" id="" class="form-control form-control-sm text-right auto_num bank_charge" value="<?= $ttl_bank_charge ?>">
						</td>
					</tr>
					<tr>
						<td colspan="5"></td>
						<td>PPh</td>
						<td class="text-right total_pph_col">
							<?= number_format($total_pph, 2) ?>
						</td>
					</tr>
					<tr>
						<td colspan="5"></td>
						<td>PPn</td>
						<td class="text-right total_ppn_col"><?= number_format($total_ppn, 2) ?></td>
					</tr>
					<tr>
						<td colspan="5"></td>
						<td>Kontrol</td>
						<td class="text-right kontrol_col"><?= number_format($kontrol, 2) ?></td>
					</tr>
				</tbody>
			</table>
			<input type="hidden" name="total_pph" class="total_pph" value="<?= $total_pph ?>">
			<input type="hidden" name="total_payment" class="total_payment" value="<?= $total_payment ?>">
			<input type="hidden" name="total_ppn" class="total_ppn" value="<?= $total_ppn ?>">
			<input type="hidden" name="total_payment_bank" class="total_payment_bank" value="<?= $total_payment_bank ?>">
			<input type="hidden" name="kontrol" class="kontrol" value="0">

			<div class="col-md-4">
				<div class="form-group">
					<input type="file" class="form-control form-control-sm" name="upload_doc" id="" style="margin-top: 15px;">
				</div>
			</div>

			<br><br>

			<div class="col-md-12">
				<h3>Jurnal</h3>
				<table class="table table-striped">
					<thead class="bg-primary">
						<tr>
							<th class="text-center">Tanggal Jurnal</th>
							<th class="text-center">Nama Company</th>
							<th class="text-center">Department</th>
							<th class="text-center">COA</th>
							<th class="text-center">Nama Account</th>
							<th class="text-center">Keterangan</th>
							<th class="text-center">Debit</th>
							<th class="text-center">Kredit</th>
						</tr>
					</thead>
					<tbody class="tbody_jurnal"></tbody>
					<tfoot class="bg-primary">
						<tr>
							<th colspan="6" class="text-center">Balancing</th>
							<th class="text-right th_ttl_debit_jurnal">0</th>
							<th class="text-right th_ttl_kredit_jurnal">0</th>
						</tr>
					</tfoot>
				</table>
			</div>

			<br><br>

			<div class="col-md-12 <?= $hide_table_jurnal_petty_cash ?>">
				<h3>Jurnal Refill Pettycash</h3>
				<table class="table table-striped">
					<thead class="bg-primary">
						<tr>
							<th class="text-center">Tanggal Jurnal</th>
							<th class="text-center">Nama Company</th>
							<th class="text-center">Divisi</th>
							<th class="text-center">COA</th>
							<th class="text-center">Nama Account</th>
							<th class="text-center">Keterangan</th>
							<th class="text-center">Debit</th>
							<th class="text-center">Kredit</th>
						</tr>
					</thead>
					<tbody class="tbody_jurnal_refill_pettycash">
						<?php
						$ttl_debit_jurnal_refill = 0;
						$ttl_kredit_jurnal_refill = 0;
						if (!empty($results['jurnal_refill_petty_cash'])) {
							$no_jurnal_refill_pettycash = 0;
							foreach ($results['jurnal_refill_petty_cash'] as $item) {
								$no_jurnal_refill_pettycash++;

								$get_coa = $this->db->get_where(DBACC . '.coa_master', ['no_perkiraan' => '1010-10-2'])->row();

								$id_coa = (!empty($get_coa)) ? $get_coa->no_perkiraan : '';
								$nm_coa = (!empty($get_coa)) ? $get_coa->nama : '';

								echo '<tr>';

								echo '<td class="text-center">';
								echo date('d F Y');
								echo '<input type="hidden" name="jurnal_refill_pettycash[' . $no_jurnal_refill_pettycash . '][tanggal_jurnal]" value="' . date('Y-m-d') . '">';
								echo '</td>';

								echo '<td class="text-center">';
								echo 'Vuca';
								echo '<input type="hidden" name="jurnal_refill_pettycash[' . $no_jurnal_refill_pettycash . '][id_company]" value="4">';
								echo '<input type="hidden" name="jurnal_refill_pettycash[' . $no_jurnal_refill_pettycash . ']nm_company]" value="Vuca">';
								echo '</td>';

								echo '<td class="text-center">';
								echo 'Driver';
								echo '</td>';

								echo '<td class="text-center">';
								echo $id_coa;
								echo '<input type="hidden" name="jurnal_refill_pettycash[' . $no_jurnal_refill_pettycash . '][coa]" value="' . $id_coa . '">';
								echo '</td>';

								echo '<td class="text-center">';
								echo $nm_coa;
								echo '<input type="hidden" name="jurnal_refill_pettycash[' . $no_jurnal_refill_pettycash . '][nm_account]" value="' . $nm_coa . '">';
								echo '</td>';

								echo '<td class="text-center">';
								echo 'Refill Pettycash - ' . $item->no_doc . '';
								echo '<input type="hidden" name="jurnal_refill_pettycash[' . $no_jurnal_refill_pettycash . '][deskripsi]" value="Refill Pettycash - ' . $item->no_doc . '">';
								echo '</td>';

								echo '<td class="text-right">';
								echo number_format($item->jumlah);
								echo '<input type="hidden" name="jurnal_refill_pettycash[' . $no_jurnal_refill_pettycash . '][debit]" value="' . $item->jumlah . '">';
								echo '</td>';

								echo '<td class="text-right">';
								echo 0;
								echo '<input type="hidden" name="jurnal_refill_pettycash[' . $no_jurnal_refill_pettycash . '][kredit]" value="0">';
								echo '</td>';

								echo '</tr>';

								$ttl_debit_jurnal_refill += $item->jumlah;
							}
						}
						?>
					</tbody>
					<tfoot class="bg-primary">
						<tr>
							<th colspan="6" class="text-center">Balancing</th>
							<th class="text-right ttl_debit_refill"><?= number_format($ttl_debit_jurnal_refill) ?></th>
							<th class="text-right ttl_kredit_refill"><?= number_format($ttl_kredit_jurnal_refill) ?></th>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>

		<div class="box-footer">
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10">
					<button type="submit" name="simpan-com" class="btn btn-success btn-sm stsview" id="simpan-com"><i class="fa fa-save">&nbsp;</i>Submit</button>
					<a href="<?= base_url() ?>pembayaran_material/payment_list" class="btn btn-warning btn-sm"><i class="fa fa-reply">&nbsp;</i>Kembali</a>
				</div>
			</div>
		</div>

	</div>
</form>

<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js" integrity="sha512-rMGGF4wg1R73ehtnxXBt5mbUfN9JUJwbk21KMlnLZDJh7BkPmeovBuddZCENJddHYYMkCh9hPFnPmS9sspki8g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>

<script>
	set_jurnal();
	set_jurnal_refill();

	$(document).ready(function() {
		// $('.supplier').chosen();
		$('.bank').chosen();
		$('.mata_uang').chosen();
		$('.pph').chosen();

		$('.auto_num').autoNumeric();

		// $.ajax({
		// 	type: "POST",
		// 	url: siteurl + active_controller + 'used_choosed_payment',
		// 	cache: false,
		// 	success: function(result) {

		// 	}
		// });
	});

	function getNum(val) {
		if (isNaN(val) || val == '') {
			return 0;
		}
		return parseFloat(val);
	}

	function number_format(number, decimals, dec_point, thousands_sep) {
		// Strip all characters but numerical ones.
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
		// Fix for IE parseFloat(0.55).toFixed(0) = 0;
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

	function hitung_kontrol() {
		var total_payment = parseFloat($('.total_payment').val());
		var total_pph = parseFloat($('.total_pph').val());
		var total_ppn = parseFloat($('.total_ppn').val());
		var total_payment_bank = $('.input_payment_bank').val();
		if (total_payment_bank !== '') {
			total_payment_bank = total_payment_bank.split(',').join('');
			total_payment_bank = parseFloat(total_payment_bank);
		} else {
			total_payment_bank = 0;
		}
		var bank_charge = $('.bank_charge').val();
		if (bank_charge !== '') {
			bank_charge = bank_charge.split(',').join('');
			bank_charge = parseFloat(bank_charge);
		}

		var kontrol = parseFloat(total_payment_bank - total_payment - total_ppn + total_pph - bank_charge);

		$('.kontrol_col').html(number_format(kontrol, 2));
		$('.kontrol').val(kontrol);
	}

	function set_jurnal() {
		var id_payment = $('.id_payment').val();
		var payment_bank = $('.input_payment_bank').val()
		var bank_charge = $('.bank_charge').val();
		var bank = $('.bank').val();
		var nilai_pph = $('.total_pph').val();
		var nilai_ppn = $('.total_ppn').val();

		$.ajax({
			type: 'post',
			url: siteurl + active_controller + 'set_jurnal',
			data: {
				'id_payment': id_payment,
				'payment_bank': payment_bank,
				'bank_charge': bank_charge,
				'bank': bank,
				'nilai_pph': nilai_pph,
				'nilai_ppn': nilai_ppn
			},
			cache: false,
			dataType: 'json',
			success: function(result) {
				$('.tbody_jurnal').html(result.hasil_jurnal);
				$('.th_ttl_debit_jurnal').html(number_format(result.ttl_debit));
				$('.th_ttl_kredit_jurnal').html(number_format(result.ttl_kredit));
			}
		})
	}

	function set_jurnal_refill() {
		// var id_payment = $('.id_payment').val();
		// var bank = $('.bank').val();

		// $.ajax({
		// 	type: 'post',
		// 	url: siteurl + active_controller + 'set_jurnal_refill',
		// 	data: {
		// 		'id_payment': id_payment,
		// 		'bank': bank
		// 	},
		// 	cache: false,
		// 	dataType: 'json',
		// 	success: function(result) {
		// 		$('.tbody_jurnal_refill_pettycash').html(result.hasil);
		// 		$('.ttl_debit_refill').html(number_format(result.ttl_debit));
		// 		$('.ttl_kredit_refill').html(number_format(result.ttl_kredit));
		// 	}
		// });
	}

	$(document).on('change', '.change_nilai_pph', function() {
		var id = $(this).data('id');
		var payment_bank = $('.payment_bank_' + id).val();
		var nilai_ppn = $('.nilai_ppn_' + id).val();
		if (nilai_ppn !== '') {
			nilai_ppn = nilai_ppn.split(',').join('');
			nilai_ppn = parseFloat(nilai_ppn);
		} else {
			nilai_ppn = 0;
		}

		var nilai_pph = $(this).val();
		if (nilai_pph !== '') {
			nilai_pph = nilai_pph.split(',').join('');
			nilai_pph = parseFloat(nilai_pph);
		} else {
			nilai_pph = 0;
		}

		var ttl_pph = 0;
		$('.nilai_pph').each(function() {
			var pph = $(this).val();
			if (pph !== '') {
				pph = pph.split(',').join('');
				pph = parseFloat(pph);
			} else {
				pph = 0;
			}

			ttl_pph += pph;
		});
		$('.total_pph').val(ttl_pph);
		$('.total_pph_col').html(number_format(ttl_pph, 2));

		var nilai_payment = (payment_bank - nilai_pph + nilai_ppn);

		$('.payment_col_' + id).html(number_format(nilai_payment, 2));

		hitung_kontrol();
		set_jurnal();
	});

	$(document).on('change', '.change_nilai_ppn', function() {
		var id = $(this).data('id');
		var payment_bank = $('.payment_bank_' + id).val();
		var nilai_pph = $('.nilai_pph_' + id).val();
		if (nilai_pph !== '') {
			nilai_pph = nilai_pph.split(',').join('');
			nilai_pph = parseFloat(nilai_pph);
		} else {
			nilai_pph = 0;
		}

		var nilai_ppn = $(this).val();
		if (nilai_ppn !== '') {
			nilai_ppn = nilai_ppn.split(',').join('');
			nilai_ppn = parseFloat(nilai_ppn);
		} else {
			nilai_ppn = 0;
		}

		var ttl_ppn = 0;
		$('.nilai_ppn').each(function() {
			var ppn = $(this).val();
			if (ppn !== '') {
				ppn = ppn.split(',').join('');
				ppn = parseFloat(ppn);
			} else {
				ppn = 0;
			}

			ttl_ppn += ppn;
		});
		$('.total_ppn').val(ttl_ppn);
		$('.total_ppn_col').html(number_format(ttl_ppn, 2));

		var nilai_payment = (payment_bank - nilai_pph + nilai_ppn);

		$('.payment_col_' + id).html(number_format(nilai_payment, 2));

		hitung_kontrol();
		set_jurnal();
	});

	$(document).on('change', '.input_payment_bank', function() {
		var nilai_payment_bank = $(this).val();
		if (nilai_payment_bank !== '') {
			nilai_payment_bank = nilai_payment_bank.split(',').join('');
			nilai_payment_bank = parseFloat(nilai_payment_bank);
		} else {
			nilai_payment_bank = 0;
		}

		var total_payment = $('.total_payment').val();

		var selisih = parseFloat(total_payment - nilai_payment_bank);

		$('.selisih_col').html(number_format(selisih, 2));

		hitung_kontrol();
		set_jurnal();
	});

	$(document).on('change', '.bank_charge', function() {
		hitung_kontrol();
		set_jurnal();
	});
	$(document).on('change', '.bank', function() {
		set_jurnal();
	})

	$(document).on('submit', '#frm-data', function(e) {
		e.preventDefault();
		var kontrol = $('.kontrol').val();
		if (kontrol == '') {
			kontrol = 0;
		} else {
			kontrol = kontrol.split(',').join('');
			kontrol = parseFloat(kontrol);
		}

		var mata_uang = $('select[name="mata_uang"]').val();
		var bank = $('select[name="bank"]').val();
		var kurs_payment = $('input[name="kurs_payment"]').val();

		var payment_bank = $('.input_payment_bank').val();
		if (payment_bank !== '') {
			payment_bank = payment_bank.split(',').join('');
			payment_bank = parseFloat(payment_bank);
		} else {
			payment_bank = 0;
		}

		if (kontrol > 0 || kontrol < 0) {
			swal({
				title: 'Warning !',
				text: 'Maaf, Pastikan Kontrol harus 0 sebelum data dibayarkan!',
				type: 'warning'
			});

			return false;
		}
		if (payment_bank <= 0) {
			swal({
				title: 'Warning !',
				text: 'Maaf, Payment bank harus diisi dan tidak boleh 0!',
				type: 'warning'
			});

			return false;
		}
		if (bank == '') {
			swal({
				title: 'Warning !',
				text: 'Maaf, Bank wajib diisi!',
				type: 'warning'
			});

			return false;
		}

		if (mata_uang == '') {
			swal({
				title: 'Warning !',
				text: 'Maaf, Mata Uang tidak boleh kosong!',
				type: 'warning'
			});

			return false;
		}

		if (kurs_payment == '') {
			swal({
				title: 'Warning !',
				text: 'Maaf, Kurs payment tidak bbisa kosong!',
				type: 'warning'
			});

			return false;
		}

		swal({
				title: "Are you sure?",
				text: "You will not be able to process again this data!",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-danger",
				confirmButtonText: "Yes, Process it!",
				cancelButtonText: "No, cancel process!",
				closeOnConfirm: true,
				closeOnCancel: false
			},
			function(isConfirm) {
				if (isConfirm) {

					var formData = new FormData($('#frm-data')[0]);
					var baseurl = siteurl + active_controller + 'save_payment';
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
								swal({
									title: "Save Success!",
									text: data.pesan,
									type: "success",
									timer: 5000,
									showCancelButton: false,
									showConfirmButton: false,
									allowOutsideClick: false
								});
								window.location.href = base_url + active_controller + 'payment_list';
							} else {

								if (data.status == 2) {
									swal({
										title: "Save Failed!",
										text: data.pesan,
										type: "warning",
										timer: 5000,
										showCancelButton: false,
										showConfirmButton: false,
										allowOutsideClick: false
									});
								} else {
									swal({
										title: "Save Failed!",
										text: data.pesan,
										type: "warning",
										timer: 5000,
										showCancelButton: false,
										showConfirmButton: false,
										allowOutsideClick: false
									});
								}

							}
						},
						error: function() {

							swal({
								title: "Error Message !",
								text: 'An Error Occured During Process. Please try again..',
								type: "warning",
								timer: 5000,
								showCancelButton: false,
								showConfirmButton: false,
								allowOutsideClick: false
							});
						}
					});
				} else {
					swal("Cancelled", "Data can be process again :)", "error");
					return false;
				}
			});
	});
</script>