<?php
$ENABLE_ADD     = has_permission('Payment.Add');
$ENABLE_MANAGE  = has_permission('Payment.Manage');
$ENABLE_VIEW    = has_permission('Payment.View');
$ENABLE_DELETE  = has_permission('Payment.Delete');
?>
<script src="//cdn.rawgit.com/rainabba/jquery-table2excel/1.1.0/dist/jquery.table2excel.min.js"></script>
<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<?= form_open($this->uri->uri_string(), array('id' => 'frm_data', 'name' => 'frm_data', 'role' => 'form', 'class' => 'form-horizontal')); ?>
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.5/css/dataTables.dataTables.min.css">
<div class="box">
	<div class="box-body">
		<div class="row">
			<div class="col-md-2">Bank : </div>
			<div class="col-md-6">
				<?php
				echo form_dropdown('bank_coa', $data_coa, '', array('id' => 'bank_coa', 'required' => 'required', 'class' => 'form-control'));
				?>
			</div>
		</div><br />
		<div class="table-responsive">
			<table id="mytabledata" class="table table-bordered">
				<thead>
					<tr>
						<td></td>
						<th width="5">No</th>
						<th class="exclass">No Dokumen</th>
						<th>Request By</th>
						<th class="exclass">Tanggal</th>
						<th>Keperluan</th>
						<th class="exclass">Tipe</th>
						<th>Info Transfer</th>
						<th>Currency</th>
						<th>Bank</th>
						<th>Nilai Pengajuan</th>
						<th class="exclass">Tanggal Pembayaran</th>
						<th class="exclass">Keterangan</th>
						<th class="exclass">Payment</th>
						<th class="exclass">Administrasi</th>
						<th class="exclass">Status</th>
						<th class="exclass">Dokumen</th>
					</tr>
				</thead>
				<tbody>
					<?php
					if (!empty($results)) {
						$numb = 0;
						foreach ($results as $record) {
							$numb++; 
							
							$readonly = ($record->status > 1) ? 'readonly' : null;
							$disabled = ($record->status > 1) ? 'disabled' : null;

							$tipe = $record->tipe;
							if($tipe == 'expense') {
								$get_expense = $this->db->select('exp_inv_po')->get_where('tr_expense', ['no_doc' => $record->no_doc])->row();
								if($get_expense->exp_inv_po == '1') {
									$tipe = 'Pembayaran PO';
								}
							}
							?>
							<tr>
								<td><input type="checkbox" name="status[]" class="status_check" id="status<?= $numb ?>" value="<?= $record->id ?>"></td>
								<td><?= $numb; ?></td>
								<td class="exclass"><?= $record->no_doc ?></td>
								<td><?= $record->nama ?></td>
								<td class="exclass"><?= $record->tgl_doc ?></td>
								<td><?= $record->keperluan ?></td>
								<td class="exclass"><?= ucfirst($tipe) ?></td>
								<td>Bank : <?= $record->bank_id ?><br />
									Nomor : <?= $record->accnumber ?><br />
									Nama : <?= $record->accname ?><br /></td>
								<td><?= $record->currency ?></td>
								<td><?= $record->bank_name ?></td>
								<td><?= number_format($record->jumlah) ?></td>
								<td class="exclass"><?= $record->tanggal ?></td>
								<?php if ($ENABLE_MANAGE) : ?>
									<td class="exclass">
										<!-- <input type="hidden" name="status[]" id="status<?= $numb ?>" value="<?= $record->id ?>"> -->
										<input type="hidden" name="no_doc[]" id="no_doc<?= $numb ?>" value="<?= $record->no_doc ?>">
										<input type="hidden" name="ids[]" id="ids<?= $numb ?>" value="<?= $record->ids ?>">
										<input type="hidden" name="keperluan[]" id="keperluan<?= $numb ?>" value="<?= $record->keperluan ?>">
										<input type="hidden" name="tipe[]" id="tipe<?= $numb ?>" value="<?= $record->tipe ?>">
										<input type="hidden" name="nama[]" id="nama<?= $numb ?>" value="<?= $record->nama ?>">
										<input type="text" name="keterangan[]" class="form-control" id="keterangan<?= $numb ?>" value="<?= $record->keterangan ?>" <?= $readonly ?>>
									</td>
									<td class="exclass"><input type="text" name="bank_nilai[]" class="form-control bank_nilai divide" id="bank_nilai<?= $numb ?>" value="<?= $record->jumlah ?>" data-no="<?= $numb ?>" <?= $readonly ?>></td>
									<td class="exclass"><input type="text" name="bank_admin[]" class="form-control divide" id="bank_admin<?= $numb ?>" value="<?= $record->admin_bank ?>" <?= $readonly ?>></td>
									<td class="exclass">
										<?php 
											if($record->status > 1){
												echo '<div class="badge bg-green">Paid</div>';
											}else{
												echo '<div class="badge bg-red">Unpaid</div>';
											}
										?>
									</td>
									<td class="exclass">
										<?php 
											if($record->status > 1){
												if(file_exists('assets/expense/'.$record->doc_file) && $record->doc_file !== ''){
													echo '<a href="'.base_url('assets/expense/' . $record->doc_file).'" class="btn btn-sm btn-info"><i class="fa fa-download"></i></a>';
												}
											}else{
												echo '<input type="file" name="doc_file_'.$record->id.'" id="doc_file'.$numb.'" '.$disabled.'/>';
											}
										?>
									</td>
								<?php endif; ?>
							</tr>
					<?php
						}
					}  ?>
				</tbody>
			</table>
			<div class="pull-right"><button type="submit" name="save" class="btn btn-success btn-sm" id="submit"><i class="fa fa-save">&nbsp;</i>Update</button></div>
		</div>
	</div>
	<!-- <div> &nbsp;<button type="button" id="btnxls" class="btn btn-default">Export Excel</button><br /><br /></div> -->
	<!-- /.box-body -->
</div>
<?= form_close() ?>
<script src="https://cdn.datatables.net/2.0.5/js/dataTables.min.js"></script>
<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script type="text/javascript">
	var url_save = siteurl + 'request_payment/save_payment/';
	$('.divide').autoNumeric();
	//Save
	$('#frm_data').on('submit', function(e) {
		e.preventDefault();

		var checked_checkbox = $('.status_check:checked').length;
		// alert(checked_checkbox);

		var errors = "";
		if ($("#bank_coa").val() == "0") errors = "Bank tidak boleh kosong";
		if (checked_checkbox < 1) errors = "Maaf, pilih terlebih dahulu payment yang akan di proses";
		if (errors == "") {
			swal({
					title: "Anda Yakin?",
					text: "Data Akan Di Update!",
					type: "info",
					showCancelButton: true,
					confirmButtonText: "Ya, Update!",
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
								if (msg.hasil == '1') {
									swal({
										title: "Sukses!",
										text: "Data Berhasil Di Update",
										type: "success",
										timer: 1500,
										showConfirmButton: false
									});
									window.location.href = window.location.href;
								} else {
									swal({
										title: "Gagal!",
										text: "Data Gagal Di Update",
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
			swal(errors);
			return false;
		}
	});

	$(document).on("keyup", ".bank_nilai", function(){
		var no = $(this).data("no");
		var bank_nilai = $(this).val();
		if(bank_nilai == "" || bank_nilai == undefined){
			var bank_nilai = 0;
		}else{
			bank_nilai = bank_nilai.split(",").join("");
			bank_nilai = parseFloat(bank_nilai);
		}

		if(bank_nilai > 0){
			$("#doc_file" + no).prop("required", true);
		}else{
			$("#doc_file" + no).prop("required", false);
		}
	});
	$("#mytabledata").dataTable();
	// $("#btnxls").click(function() {
	// 	$("#mytabledata").table2excel({
	// 		exclude: ".exclass",
	// 		name: "Weekly Budget",
	// 		filename: "WeeklyBudget.xls", // do include extension
	// 		preserveColors: false // set to true if you want background colors and font colors preserved
	// 	});
	// });
</script>