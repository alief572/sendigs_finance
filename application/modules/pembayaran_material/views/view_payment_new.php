<?php
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
						<input type="date" name="tgl_bayar" id="" class="form-control form-control-sm tgl_bayar" value="<?= $results['result_header']->tgl_bayar ?>" readonly>
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
						<textarea name="keterangan_pembayaran" id="" class="form-control form-control-sm keterangan_pembayaran" readonly><?= $results['result_header']->keterangan_pembayaran ?></textarea>
					</td>
					<td width="15%" style="">Pilih Bank</td>
					<td width="5%" class="text-center">:</td>
					<td width="25%">
						<select name="bank" id="" class="form-control form-control-sm " disabled>
							<option value="">- Bank -</option>
							<?php
							foreach ($results['list_bank'] as $item_bank) {
                                $selected = ($item_bank->no_perkiraan == $results['result_header']->coa_bank) ? 'selected' : '';
								echo '<option value="' . $item_bank->no_perkiraan . '" '.$selected.'>' . $item_bank->nama . '</option>';
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td width="15%" style="">Mata Uang</td>
					<td width="5%" class="text-center">:</td>
					<td width="25%">
						<select name="mata_uang" id="" class="form-control form-control-sm " disabled>
							<option value="">- Mata Uang -</option>
							<?php
							foreach ($results['list_mata_uang'] as $item_mata_uang) {
                                $selected = ($item_mata_uang->kode == $results['result_header']->mata_uang) ? 'selected' : '';
								echo '<option value="' . $item_mata_uang->kode . '" '.$selected.'>' . $item_mata_uang->kode . '</option>';
							}
							?>
						</select>
					</td>
					<td width="15%" style="">Request Payment</td>
					<td width="5%" class="text-center">:</td>
					<td width="25%">
						<input type="text" name="payment_bank" id="" class="form-control form-control-sm text-right input_payment_bank auto_num" value="<?= number_format($results['result_header']->payment_bank, 2) ?>" readonly>
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
						<th class="text-center">Payment Bank</th>
						<th class="text-center" colspan="2">PPH</th>
						<th class="text-center">PPN</th>
						<th class="text-center">Payment</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$total_payment = 0;
					$total_ppn = 0;
					$total_pph = 0;
					$total_payment_bank = 0;
					$no = 1;
					foreach ($results['result_payment'] as $item) {

						$nm_supplier = [];

						$get_rec_invoice = $this->db->get_where('tr_invoice_po', ['id' => $item->no_doc])->row();
						// print_r($get_rec_invoice);
						// exit;

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
									->select('a.qty_order, b.hargasatuan')
									->from('tr_incoming_check_detail a')
									->join('dt_trans_po b', 'b.id = a.id_po_detail', 'left')
									->where_in('a.kode_trans', $arr_no_incoming)
									->get()
									->result();

								foreach ($get_incoming_check_detail as $item_detail) {
									$nilai_utuh += ($item_detail->hargasatuan * $item_detail->qty_order);
								}
							} else {
								$no_po[] = $get_rec_invoice->no_po;

								$get_nilai_utuh = $this->db
									->select('a.hargatotal')
									->from('tr_purchase_order a')
									->where('a.no_surat', $get_rec_invoice->no_po)
									->get()
									->result();

								foreach ($get_nilai_utuh as $item_nilai_utuh) {
									$nilai_utuh += $item_nilai_utuh->hargatotal;
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

						$nilai_ppn = (($nilai_utuh * $persen_progress / 100) * 11 / 100);
						// if($nilai_ppn <= 0) {
						// 	$nilai_ppn = ($item->jumlah * 11 / 100);
						// }

                        $nilai_pph = $item->total_pph;

                        $selected_pph_23 = ($item->tipe_pph == 'PPH 23') ? 'selected' : '';
                        $selected_pph_22 = ($item->tipe_pph == 'PPH 22') ? 'selected' : '';

						echo '<tr>';
						echo '<td class="text-center">' . $nm_supplier . '</td>';
						echo '<td class="text-center"><input type="hidden" name="dt['.$no.'][id_payment]" value="'.$item->id.'">' . $item->no_doc . '</td>';
						echo '<td class="text-right">
					<input type="hidden" class="jumlah_col_' . $item->id . '">
					<input type="hidden" class="payment_bank_' . $item->id . '" value="' . $item->jumlah . '">
					' . number_format($item->jumlah, 2) . '
					</td>';
						echo '<td>';
						echo '<select name="dt['.$no.'][tipe_pph]" class="form-control form-control-sm" disabled>';
						echo '<option value="1"'.$selected_pph_23.'>PPH 23</option>';
						echo '<option value="2" '.$selected_pph_22.'>PPH 22</option>';
						echo '</select>';
						echo '</td>';
						echo '<td>';
						echo '<input type="hidden" class="nilai_utuh_' . $item->id . '" value="' . $nilai_utuh . '">';
						echo '<input type="hidden" class="persen_progress_' . $item->id . '" value="' . $persen_progress . '">';
						echo '<input type="text" class="form-control form-control-sm" name="dt['.$no.'][nilai_pph]" data-id="' . $item->id . '" value="'.number_format($item->total_pph, 2).'" readonly>';
						echo '</td>';
						echo '<td class="text-right"><input type="hidden" name="dt['.$no.'][nilai_ppn]" class="nilai_ppn_' . $item->id . '" value="' . $nilai_ppn . '">' . number_format($nilai_ppn, 2) . '</td>';
						echo '<td class="text-right payment_col_' . $item->id . '">' . number_format($item->jumlah - $nilai_ppn + $nilai_pph, 2) . '</td>';
						echo '</tr>';

						$total_payment += ($item->jumlah - $nilai_ppn);
						$total_ppn += ($nilai_ppn);
						$total_payment_bank += ($item->jumlah);
                        $total_pph += ($item->total_pph);

						$no++;
					}
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
						<td class="text-right selisih_col"><?= number_format($total_payment - $results['result_header']->payment_bank, 2) ?></td>
					</tr>
					<tr>
						<td colspan="5"></td>
						<td>Bank Charge</td>
						<td>
							<input type="text" name="bank_charge" id="" class="form-control form-control-sm text-right auto_num bank_charge" value="<?= number_format($results['bank_charge'], 2) ?>" readonly>
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
						<td class="text-right"><?= number_format($total_ppn, 2) ?></td>
					</tr>
					<tr>
						<td colspan="5"></td>
						<td>Kontrol</td>
						<td class="text-right kontrol_col"><?= number_format($results['result_header']->payment_bank - $total_payment - $results['bank_charge'] - $total_ppn + $total_pph, 2) ?></td>
					</tr>
				</tbody>
			</table>
			<input type="hidden" name="total_pph" class="total_pph" value="<?= $total_pph ?>">
			<input type="hidden" name="total_payment" class="total_payment" value="<?= $total_payment ?>">
			<input type="hidden" name="total_ppn" class="total_ppn" value="<?= $total_ppn ?>">
			<input type="hidden" name="total_payment_bank" class="total_payment_bank" value="<?= $total_payment_bank ?>">
			<input type="hidden" name="kontrol" class="kontrol" value="<?= ($results['result_header']->payment_bank - $total_payment - $results['bank_charge'] - $total_ppn + $total_pph) ?>">

			<div class="col-md-4">
				<?php 
                    if(file_exists('assets/expense/'.$results['result_header']->link_doc) && $results['result_header']->link_doc !== '') {
                        echo '<a href="'.base_url('assets/expense/'.$results['result_header']->link_doc).'" class="btn btn-sm btn-primary" target="_blank"><i class="fa fa-download"></i> Download</a>';
                    }
                ?>
			</div>
		</div>
		<!-- <div class="box-footer">
		<input type="hidden" name="total" id="total" value="<?= round($total); ?>" />
		<input type="hidden" name="id_supplier" id="id_supplier" value="<?= (isset($data) ? $data->id_supplier : $id_supplier); ?>" />
		<input type="hidden" name="curs_header" id="curs_header" value="<?= (isset($data) ? $data->curs_header : $curs_header); ?>" />
		<input type="hidden" name="modul" id="modul" value="<?= (isset($data) ? $data->modul : 'PO'); ?>" />
		<div class="row">
			<div class="col-md-6">
				<label class="control-label">Bank</label>
				<?php
				echo form_dropdown('bank_coa', $datacoa, (isset($data) ? $data->bank_coa : ''), array('id' => 'bank_coa', 'class' => 'form-control select2', 'required' => 'required'));
				?>
				<label class="control-label">Nilai Bank</label>
				<input type="text" class="form-control divide" id="nilai_bayar_bank" name="nilai_bayar_bank" value="<?php echo (isset($data) ? $data->nilai_bayar_bank : $total); ?>" placeholder=0 onblur="cek_kurs()" required>
				<label class="control-label">Kurs Bank</label>
				<input type="text" class="form-control divide" id="curs" name="curs" value="<?php echo (isset($data) ? $data->curs : 1); ?>" onblur="cek_kurs()">
				<label class="control-label">Nilai Bank Rupiah</label>
				<input type="text" class="form-control divide" id="bank_nilai" name="bank_nilai" value="<?php echo (isset($data) ? $data->bank_nilai : 0); ?>" placeholder=0 required tabindex="-1">
			</div>
			<div class="col-md-6">
				<label class="control-label">Bank Admin</label>
				<?php
				echo form_dropdown('bank_coa_admin', $datacoa, (isset($data) ? $data->bank_coa_admin : ''), array('id' => 'bank_coa_admin', 'class' => 'form-control select2', 'required' => 'required'));
				?>
				<label class="control-label">Biaya Admin Bank</label>
				<input type="text" class="form-control divide" id="biaya_admin_forex" name="biaya_admin_forex" value="<?php echo (isset($data) ? $data->biaya_admin_forex : 0); ?>" placeholder=0 onblur="cek_kurs_admin('')">
				<label class="control-label">Kurs Admin 1</label>
				<input type="text" class="form-control divide" id="curs_admin" name="curs_admin" value="<?php echo (isset($data) ? $data->curs_admin : 1); ?>" onblur="cek_kurs_admin('')">
				<label class="control-label">Biaya Admin 1 Rupiah</label>
				<input type="text" class="form-control divide" id="biaya_admin" name="biaya_admin" value="<?php echo (isset($data) ? $data->biaya_admin : 0); ?>" placeholder=0 required readonly tabindex="-1">

				<label class="control-label">Biaya Admin Bank 2</label>
				<input type="text" class="form-control divide" id="biaya_admin_forex2" name="biaya_admin_forex2" value="<?php echo (isset($data) ? $data->biaya_admin_forex2 : 0); ?>" placeholder=0 onblur="cek_kurs_admin('2')">
				<label class="control-label">Kurs Admin 2</label>
				<input type="text" class="form-control divide" id="curs_admin2" name="curs_admin2" value="<?php echo (isset($data) ? $data->curs_admin2 : 1); ?>" onblur="cek_kurs_admin('2')">
				<label class="control-label">Biaya Admin 2 Rupiah</label>
				<input type="text" class="form-control divide" id="biaya_admin2" name="biaya_admin2" value="<?php echo (isset($data) ? $data->biaya_admin2 : 0); ?>" placeholder=0 required tabindex="-1">
			</div>
		</div>
	</div> -->
		<div class="box-footer">
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10">
					<a href="<?= base_url() ?>pembayaran_material/payment_list" class="btn btn-warning btn-sm"><i class="fa fa-reply">&nbsp;</i>Kembali</a>
				</div>
			</div>
		</div>

	</div>
</form>

<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js" integrity="sha512-rMGGF4wg1R73ehtnxXBt5mbUfN9JUJwbk21KMlnLZDJh7BkPmeovBuddZCENJddHYYMkCh9hPFnPmS9sspki8g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>

<script>
	$(document).ready(function() {
		// $('.supplier').chosen();
		$('.bank').chosen();
		$('.mata_uang').chosen();
		$('.pph').chosen();

		$('.auto_num').autoNumeric();

		$.ajax({
			type: "POST",
			url: siteurl + active_controller + 'used_choosed_payment',
			cache: false,
			success: function(result) {

			}
		});
	});

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

		var kontrol = parseFloat(total_payment_bank - total_payment - bank_charge - total_ppn - total_pph);

		$('.kontrol_col').html(number_format(kontrol, 2));
		$('.kontrol').val(kontrol);
	}

	$(document).on('change', '.change_nilai_pph', function() {
		var id = $(this).data('id');
		var payment_bank = $('.payment_bank_' + id).val();
		var nilai_ppn = $('.nilai_ppn_' + id).val();

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

		var nilai_payment = (payment_bank - nilai_ppn + nilai_pph);

		$('.payment_col_' + id).html(number_format(nilai_payment, 2));

		hitung_kontrol();
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
	});

	$(document).on('change', '.bank_charge', function() {
		hitung_kontrol();
	});

	$(document).on('submit', '#frm-data', function(e) {
		e.preventDefault();
		var kontrol = $('.kontrol').val();
		if(kontrol !== '' ) {

		}
		if(kontrol > 0) {
			swal({
				title: 'Warning !',
				text: 'Maaf, Pastikan Kontrol harus 0 sebelum data dibayarkan!',
				type: 'warning'
			});
		}else{
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
		}
	});
</script>