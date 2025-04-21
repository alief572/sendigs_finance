<?php
$dept = '';
$bank_id = '';
$accnumber = '';
$accname = '';
if (!isset($data->departement)) {
	$datauser = $this->db->get_where('users', ['id_user' => $this->auth->user_id()])->row();
	$datadept = $this->db->get_where('employee', ['id' => $datauser->employee_id])->row();
	if (!empty($datadept)) {
		$dept = $datauser->department_id;
		$bank_id = $datadept->bank_id;
		$accnumber = $datadept->accnumber;
		$accname = $datadept->accname;
	}
}

$data_user = $this->db->get_where('users', ['id_user' => $this->auth->user_id()])->row();

$metode_pembayaran = (isset($data)) ? $data->metode_pembayaran : '';

?>

<form action="" id="frm_data" class="form-horizontal" enctype="multipart/form-data">
	<input type="hidden" id="id" name="id" value="<?php echo set_value('id', isset($data->id) ? $data->id : ''); ?>">
	<input type="hidden" id="departement" name="departement" value="<?php echo ($data_user->department_id) ?>">
	<input type="hidden" id="nama" name="nama" value="<?php echo (isset($data->nama) ? $data->nama : $this->auth->user_name()); ?>">
	<input type="hidden" name="" class="stsview" value="<?= (isset($stsview)) ? $stsview : null ?>">

	<div class="tab-content">
		<div class="tab-pane active">
			<div class="box box-primary">
				<div class="box-body">
					<div class="form-group ">
						<label class="col-sm-2 control-label">No Dokumen</label>
						<div class="col-sm-4">
							<input type="text" class="form-control" id="no_doc" name="no_doc" value="<?php echo (isset($data->no_doc) ? $data->no_doc : ""); ?>" placeholder="Automatic" readonly>
						</div>
						<label class="col-sm-2 control-label">Tanggal <b class="text-red">*</b></label>
						<div class="col-sm-4">
							<input type="text" class="form-control tanggal" id="tgl_doc" name="tgl_doc" value="<?php echo (isset($data->tgl_doc) ? $data->tgl_doc : date("Y-m-d")); ?>" placeholder="Tanggal Dokumen" required>
						</div>
					</div>
					<div class="form-group ">
						<label class="col-sm-2 control-label">Keperluan</label>
						<div class="col-sm-4">
							<input type="text" class="form-control" id="keperluan" name="keperluan" value="<?php echo (isset($data->keperluan) ? $data->keperluan : ''); ?>" placeholder="Keperluan" required>
						</div>
						<label class="col-sm-2 control-label">Nominal Kasbon</label>
						<div class="col-sm-4">
							<input type="text" class="form-control divide" id="jumlah_kasbon" name="jumlah_kasbon" value="<?php echo (isset($data->jumlah_kasbon) ? $data->jumlah_kasbon : '0'); ?>" placeholder="jumlah_kasbon">
						</div>
					</div>
					<div class="form-group ">
						<label class="col-sm-2 control-label">Dokumen 1</label>
						<div class="col-sm-4">
							<input type="hidden" name="filename" id="filename" value="<?= (isset($data->doc_file) ? $data->doc_file : ''); ?>">
							<input type="file" name="doc_file" id="doc_file">
							<span class="pull-right">
								<?php
								if (isset($data->doc_file)) {
									echo ($data->doc_file != '' ? '<a href="' . base_url('assets/expense/' . $data->doc_file) . '" download target="_blank"><i class="fa fa-download"></i></a>' : '');
								}
								?>
							</span>
						</div>
						<label class="col-sm-2 control-label">Dokumen 2</label>
						<div class="col-sm-4">
							<input type="hidden" name="filename2" id="filename2" value="<?= (isset($data->doc_file_2) ? $data->doc_file_2 : ''); ?>">
							<input type="file" name="doc_file_2" id="doc_file_2">
							<span class="pull-right">
								<?php
								if (isset($data->doc_file_2)) {
									echo ($data->doc_file_2 != '' ? '<a href="' . base_url('assets/expense/' . $data->doc_file_2) . '" download target="_blank"><i class="fa fa-download"></i></a>' : '');
								}
								?>
							</span>
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-2 control-label">Metode Pembayaran</label>
						<div class="col-sm-4">
							<select name="metode_pembayaran" id="" class="form-control metode_pembayaran" required>
								<option value="">- Metode Pembayaran -</option>
								<option value="1" <?= ($metode_pembayaran == 1) ? 'selected' : null ?>>Request Payment</option>
								<option value="2" <?= ($metode_pembayaran == 2) ? 'selected' : null ?>>Pettycash Finance</option>
							</select>
						</div>
						<label class="col-sm-2 control-label">Keterangan</label>
						<div class="col-sm-4">
							<textarea class="form-control" id="keterangan" name="keterangan" placeholder="Keterangan" required><?php echo (isset($data->keterangan) ? $data->keterangan : ''); ?></textarea>

							<?php
							if (isset($data->st_reject)) {
								if ($data->st_reject != '') {
									echo '
							  <div class="alert alert-danger alert-dismissible">
								<h4><i class="icon fa fa-ban"></i> Alasan Penolakan!</h4>
								' . $data->st_reject . '
							  </div>';
								}
							}
							?>
						</div>
					</div>

					<div class="transfer_ke_cont" style="display: none;">
						<h4>Transfer ke</h4>
						<div class="form-group ">
							<label class="col-md-1 control-label">Bank</label>
							<div class="col-md-2">
								<input type="text" class="form-control" id="bank_id" name="bank_id" value="<?php echo (isset($data->bank_id) ? $data->bank_id : $bank_id); ?>" placeholder="Bank">
							</div>
							<label class="col-md-2 control-label">Nomor Rekening</label>
							<div class="col-md-2">
								<input type="text" class="form-control" id="accnumber" name="accnumber" value="<?php echo (isset($data->accnumber) ? $data->accnumber : $accnumber); ?>" placeholder="Nomor Rekening">
							</div>
							<label class="col-md-2 control-label">Nama Rekening</label>
							<div class="col-md-3">
								<input type="text" class="form-control" id="accname" name="accname" value="<?php echo (isset($data->accname) ? $data->accname : $accname); ?>" placeholder="Nama Pemilik Rekening">
							</div>
						</div>
					</div>

					<h4>Kasbon PR Non PO</h4>
					<div class="form-group">
						<div class="col-md-1 control-label">
							No. PR
						</div>
						<div class="col-md-4">
							<?php
							if (isset($data->id_pr)) {
							?>
								<input type="text" name="no_pr" id="search_pr_non_po" class="form-control" placeholder="- No PR -" value="<?= (isset($data->id_pr)) ? $data->id_pr : null ?>">
							<?php
							} else {
							?>

								<select name="no_pr" id="search_pr_non_po" class="form-control chosen_select" id="">
									<option value="">- No PR -</option>
									<?php
									foreach ($list_pr_non_po as $item_pr_non_po) {
										echo '<option value="' . $item_pr_non_po . '">' . $item_pr_non_po . '</option>';
									}
									?>
								</select>

							<?php
							}
							?>

							<input type="hidden" name="tipe_pr" id="tipe_pr" value="<?= (isset($list_detail_pr_kasbon[0]['tipe_pr'])) ? $list_detail_pr_kasbon[0]['tipe_pr'] : null ?>">
							<label for="">*Note: Klik enter jika sudah</label>
						</div>
					</div>
					<div class="col-md-12">
						<table class="table table-striped">
							<thead>
								<tr>
									<th class="text-center">No.</th>
									<th class="text-center">Material Name</th>
									<th class="text-center">Qty</th>
									<th class="text-center">Unit</th>
									<th class="text-center">Harga Satuan</th>
									<th class="text-center">Grand Total</th>
									<th class="text-center">Action</th>
								</tr>
							</thead>
							<tbody class="list_barang_pr">
								<?php
								if (isset($list_detail_pr_kasbon)) {
									$no = 1;
									foreach ($list_detail_pr_kasbon as $detail_pr) :
										$readonly = '';
										if (($mod == '_fin' || $mod == '_mgt')) {
											$readonly = 'readonly';
										}
										echo '<tr class="detail_pr_' . $detail_pr['id'] . '">';
										echo '<td class="text-center">' . $no . '</td>';
										echo '<td class="text-center">' . $detail_pr['nm_material'] . '</td>';
										echo '<td class="text-center">' . number_format($detail_pr['qty']) . ' <input type="hidden" class="qty_' . $detail_pr['id'] . '" value="' . $detail_pr['qty'] . '"></td>';
										echo '<td class="text-center">' . $detail_pr['satuan'] . '</td>';
										echo '<td class="text-center"><input type="text" name="price_input_' . $detail_pr['id'] . '" class="form-control form-control-sm text-right price_input price_input_' . $detail_pr['id'] . ' autonum" data-no="' . $detail_pr['id'] . '" value="' . $detail_pr['harga'] . '" ' . $readonly . '></td>';
										echo '<td class="text-center"><input type="text" name="grand_total_' . $detail_pr['id'] . '" class="form-control form-control-sm text-right grand_total_' . $detail_pr['id'] . ' autonum" value="' . $detail_pr['total_harga'] . '" ' . $readonly . '></td>';
										echo '<td class="text-center">';
										if (($mod == '_fin' || $mod == '_mgt')) {
										} else {
											if (!isset($stsview) || $stsview == '') {
												echo '<button type="button" class="btn btn-sm btn-danger del_detail" data-no="' . $detail_pr['id'] . '"><i class="fa fa-trash"></i></button>';
											}
										}
										echo '</td>';
										echo '</tr>';
										$no++;
									endforeach;
								}
								?>
							</tbody>
						</table>
					</div>
				</div>
				<div class="box-footer">
					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-10">
							<?php
							if (isset($data)) {
								if (($data->status == 0 || $data->status == 1) && $stsview == '') {
									if (($mod == '_fin' || $mod == '_mgt')) {
										echo '<a class="btn btn-primary btn-sm" href="#" id="approve" onclick="data_approve(' . $data->id . ',' . ($data->status + 1) . ')"><i class="fa fa-check-square-o"></i> Approve</a>';
										echo ' <a class="btn btn-danger btn-sm" onclick="data_reject()"><i class="fa fa-ban">&nbsp;</i> Reject</a>';
										$stsview = 'view';
									} else {
										echo '<button type="submit" name="save" class="btn btn-success btn-sm stsview" id="submit"><i class="fa fa-save">&nbsp;</i>Simpan</button>';
									}
								} else {
									echo '<button type="submit" name="save" class="btn btn-success btn-sm stsview" id="submit"><i class="fa fa-save">&nbsp;</i>Simpan</button>';
								}
							} else {
								echo '<button type="submit" name="save" class="btn btn-success btn-sm stsview" id="submit"><i class="fa fa-save">&nbsp;</i>Simpan</button>';
							}
							?>
							<a class="btn btn-default btn-sm" onclick="window.location=siteurl+'expense/kasbon<?= $mod ?>';return false;"><i class="fa fa-reply"></i> Batal</a>
						</div>
					</div>
					<?php
					if (isset($data)) {
						if ($data->doc_file != '') {
							if (strpos($data->doc_file, 'pdf', 0) > 1) {
								echo '<div class="col-md-12">
						<iframe src="' . base_url('assets/expense/' . $data->doc_file) . '#toolbar=0&navpanes=0" title="PDF" style="width:600px; height:500px;" frameborder="0">
								 <a href="' . base_url('assets/expense/' . $data->doc_file) . '">Download PDF</a>
						</iframe>
						<br />' . $data->no_doc . '</div>';
							} else {
								echo '<div class="col-md-12"><a href="' . base_url('assets/expense/' . $data->doc_file) . '" target="_blank"><img src="' . base_url('assets/expense/' . $data->doc_file) . '" class="img-responsive"></a><br />' . $data->no_doc . '</div>';
							}
						}
						if ($data->doc_file_2 != '') {
							if (strpos($data->doc_file_2, 'pdf', 0) > 1) {
								echo '<div class="col-md-12">
						<iframe src="' . base_url('assets/expense/' . $data->doc_file_2) . '#toolbar=0&navpanes=0" title="PDF" style="width:600px; height:500px;" frameborder="0">
								 <a href="' . base_url('assets/expense/' . $data->doc_file_2) . '">Download PDF</a>
						</iframe>
						<br />' . $data->no_doc . '</div>';
							} else {
								echo '<div class="col-md-12"><a href="' . base_url('assets/expense/' . $data->doc_file_2) . '" target="_blank"><img src="' . base_url('assets/expense/' . $data->doc_file_2) . '" class="img-responsive"></a><br />' . $data->no_doc . '</div>';
							}
						}
					}
					?>
				</div>
			</div>
		</div>
	</div>
</form>

<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script type="text/javascript">
	var url_save = siteurl + 'expense/kasbon_save/';
	var url_approve = siteurl + 'expense/kasbon_approve/';
	$('.divide').divide();

	$('.autonum').autoNumeric('init');
	$('.chosen_select').chosen({
		width: '100%'
	});

	var stsview = $('.stsview').val();
	if (stsview == 'view') {
		$(".stsview").addClass("hidden");
		$("#frm_data :input").prop("disabled", true);
	}

	$(document).on('change', '.metode_pembayaran', function() {
		var metode_pembayaran = $(this).val();

		if (metode_pembayaran == 1) {
			$('.transfer_ke_cont').show();

			$('#bank_id').prop('required', true);
			$('#accnumber').prop('required', true);
			$('#accname').prop('required', true);
		} else {
			$('.transfer_ke_cont').hide();

			$('#bank_id').prop('required', false);
			$('#accnumber').prop('required', false);
			$('#accname').prop('required', false);
		}
	});


	$('#frm_data').on('submit', function(e) {
		e.preventDefault();
		var errors = "";
		if ($("#filename").val() == "") {
			if ($('#doc_file').get(0).files.length === 0) {
				errors = "Dokumen 1 harus diupload";
			}
		}

		var metode_pembayaran = $('.metode_pembayaran').val();

		if ($("#jumlah_kasbon").val() == "0") errors = "Jumlah Kasbon tidak boleh kosong";
		if ($("#keperluan").val() == "") errors = "keperluan tidak boleh kosong";
		if ($("#tgl_doc").val() == "") errors = "Tanggal Transaksi tidak boleh kosong";
		if (metode_pembayaran == "") errors = "Pilih metode pembayaran";
		if (metode_pembayaran == 1) {
			var bank_id = $('#bank_id').val();
			var accnumber = $('#accnumber').val();
			var accname = $('#accname').val();

			if (bank_id == '' || accnumber == '' || accname == '') {
				errors = "Pastikan data transfer terisi";
			}
		}

		var price_no_input = 0;
		$('.price_input').each(function() {
			var value = parseFloat($(this).val());
			if (isNaN(value)) {
				price_no_input += 1;
			}
		});

		if (price_no_input > 0) {
			errors = "Please make sure all material price is filled !";
		}
		if (errors == "") {
			swal({
					title: "Anda Yakin?",
					text: "Data Akan Disimpan!",
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
										text: "Data Berhasil Di Simpan",
										type: "success",
										timer: 1500,
										showConfirmButton: false
									});
									window.location = siteurl + 'expense/kasbon';
								} else {
									swal({
										title: "Gagal!",
										text: "Data Gagal Di Simpan",
										type: "error",
										timer: 1500,
										showConfirmButton: false
									});
								};

							},
							error: function(msg) {
								swal({
									title: "Gagal!",
									text: "Ajax Data Gagal Di Proses",
									type: "error",
									timer: 1500,
									showConfirmButton: false
								});

							}
						});
					}
				});

			//			data_save();
		} else {
			swal({
				title: 'Error !',
				text: errors,
				type: 'error'
			});
		}
	});

	$(document).on('change', '#search_pr_non_po', function(e) {
		// e.preventDefault();
		const no_pr = $(this).val();

		// if (e.keyCode == '13') {

		// } else {
		// 	$('#search_pr_non_po').val(no_pr);
		// }

		$.ajax({
			type: "POST",
			url: siteurl + active_controller + 'get_pr_non_po',
			data: {
				'no_pr': no_pr
			},
			cache: false,
			dataType: 'json',
			success: function(result) {
				if (result.sts == '1') {
					$('.list_barang_pr').html(result.hasil);
					$('#tipe_pr').val(result.tipe_pr);
					$('.autonum').autoNumeric();
				} else {
					swal({
						title: 'Error !',
						text: result.pesan,
						type: 'error'
					});
				}
			},
			error: function(result) {
				swal({
					title: 'Error !',
					text: 'Error occured, please try again later !',
					type: 'error'
				});
			}
		});
	});

	$(document).on('click', '.del_detail', function() {
		var no = $(this).data('no');

		$('.detail_pr_' + no).remove();
	});

	$(document).on('change', '.price_input', function() {
		var no = $(this).data('no');
		var nilai = $(this).val();
		if (nilai == null || nilai == '') {
			var nilai = 0;
		} else {
			var nilai = nilai.split(',').join('');
			nilai = parseFloat(nilai);
		}

		var qty = $('.qty_' + no).val();

		// alert(nilai);
		// alert(qty);

		var total = parseFloat(nilai * qty);

		$('.grand_total_' + no).autoNumeric('set', total);
	})

	$(function() {
		$(".tanggal").datepicker({
			todayHighlight: true,
			format: "yyyy-mm-dd",
			showInputs: true,
			autoclose: true
		});
	});

	function data_approve() {
		swal({
				title: "Anda Yakin?",
				text: "Data Akan Diupdate!",
				type: "info",
				showCancelButton: true,
				confirmButtonText: "Ya, setuju!",
				cancelButtonText: "Tidak!",
				closeOnConfirm: false,
				closeOnCancel: true
			},
			function(isConfirm) {
				if (isConfirm) {
					id = $("#id").val();
					$.ajax({
						url: url_approve + id,
						dataType: "json",
						type: 'POST',
						success: function(msg) {
							if (msg['save'] == '1') {
								swal({
									title: "Sukses!",
									text: "Data Berhasil Di Update",
									type: "success",
									timer: 1500,
									showConfirmButton: false
								});
								window.location = siteurl + 'expense/kasbon<?= $mod ?>';
							} else {
								swal({
									title: "Gagal!",
									text: "Data Gagal Di Update",
									type: "error",
									timer: 1500,
									showConfirmButton: false
								});
							};

						},
						error: function(msg) {
							swal({
								title: "Gagal!",
								text: "Ajax Data Gagal Di Proses",
								type: "error",
								timer: 1500,
								showConfirmButton: false
							});

						}
					});
				}
			});
	}

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
								url: base_url + 'expense/reject/',
								data: {
									'id': id,
									'reason': inputValue,
									'table': 'tr_kasbon'
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
										window.location = siteurl + 'expense/kasbon<?= $mod ?>'
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