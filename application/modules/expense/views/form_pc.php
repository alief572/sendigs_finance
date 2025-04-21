<link rel="stylesheet" href="<?= base_url() ?>assets/plugins/select2/select2.css">
<script src="<?= base_url() ?>assets/plugins/select2/select2.full.min.js"></script>
<?php
$gambar = '';
$datacombocoa = "";
$dept = '';
$app = '';
$bank_id = '';
$accnumber = '';
$accname = '';
if (!isset($data->departement)) {
	$data_user = $this->db->get_where('users', ['username' => $this->auth->user_name()])->row();
	$data_employee = $this->db->get_where('employee', ['id' => $data_user->employee_id])->row();
	if (!empty($data_employee)) {
		$dept = $data_employee->department_id;
		$bank_id = $data_employee->bank_id;
		$accnumber = $data_employee->accnumber;
		$accname = $data_employee->accname;
		//$data_head = $this->db->get_where('divisions_head', ['id' => $data_employee->division_head])->row();
		//$app=$data_head->employee_id;
	}
}
$budgets = 0;
?>
<?= form_open($this->uri->uri_string(), array('id' => 'frm_data', 'name' => 'frm_data', 'role' => 'form', 'class' => 'form-horizontal', 'enctype' => 'multipart/form-data')); ?>
<input type="hidden" id="id" name="id" value="<?php echo set_value('id', isset($data->id) ? $data->id : ''); ?>">
<input type="hidden" id="departement" name="departement" value="<?php echo (isset($data->departement) ? $data->departement : $dept); ?>">
<input type="hidden" id="nama" name="nama" value="<?php echo (isset($data->nama) ? $data->nama : $this->auth->user_name()); ?>">
<input type="hidden" id="approval" name="approval" value="<?php echo (isset($data->approval) ? $data->approval : $app); ?>">
<style>
	@media screen and (max-width: 520px) {
		table {
			width: 100%;
		}

		thead th.column-primary {
			width: 100%;
		}

		thead th:not(.column-primary) {
			display: none;
		}

		th[scope="row"] {
			vertical-align: top;
		}

		td {
			display: block;
			width: auto;
			text-align: right;
		}

		thead th::before {
			text-transform: uppercase;
			font-weight: bold;
			content: attr(data-header);
		}

		thead th:first-child span {
			display: none;
		}

		td::before {
			float: left;
			text-transform: uppercase;
			font-weight: bold;
			content: attr(data-header);
		}
	}
</style>
<div class="tab-content">
	<div class="tab-pane active">
		<div class="box box-primary">
			<div class="box-body">
				<div class="form-group ">
					<label class="col-sm-2 col-md-2 control-label">No Dokumen</label>
					<div class="col-sm-4 col-md-4">
						<input type="text" class="form-control" id="no_doc" name="no_doc" value="<?php echo (isset($data->no_doc) ? $data->no_doc : ""); ?>" placeholder="Automatic" readonly>
					</div>
					<label class="col-sm-2 col-md-2 control-label">Tanggal <b class="text-red">*</b></label>
					<div class="col-sm-4 col-md-4">
						<input type="text" class="form-control tanggal" id="tgl_doc" name="tgl_doc" value="<?php echo (isset($data->tgl_doc) ? $data->tgl_doc : date("Y-m-d")); ?>" placeholder="Tanggal Dokumen" required>
					</div>
				</div>
				<div class="form-group ">
					<label class="col-sm-2 control-label">Petty Cash<font size="4" color="red"><B>*</B></font></label>
					<div class="col-sm-4">
						<div class="input-group">
							<select name="pettycash" id="pettycash" class="form-control" placeholder="Petty Cash" required>
								<?php
								echo '<option value="">Select an option</option>';
								foreach ($data_pc as $record) {
									$selected = '';
									if (isset($data->pettycash)) {
										if ($record->nama == $data->pettycash) {
											$selected = ' selected';
											$budgets = $record->budget;
											$datacombocoa = $record->coa;
										}
									}
									echo '<option value="' . $record->nama . '" ' . $selected . ' data-budget="' . $record->budget . '" data-approval="' . $record->approval . '" data-coa="' . $record->coa . '">' . $record->nama . '</option>';
								}
								?>
							</select>
						</div>
					</div>
					<label class="col-sm-2 col-md-2 control-label">Keterangan <b class="text-red">*</b></label>
					<div class="col-sm-4 col-md-4">
						<input type="text" class="form-control" id="informasi" name="informasi" value="<?php echo (isset($data->informasi) ? $data->informasi : ""); ?>" placeholder="Keterangan" required>
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



				<?php
				if (!isset($stsview) || (isset($stsview) && $stsview == '')) {
				?>

					<h4>List Penggantian/Pengembalian Kasbon</h4>
					<table class="table table-bordered">
						<thead>
							<tr>
								<th class="text-center">No</th>
								<th class="text-center">No. Expense</th>
								<th class="text-center">Keterangan</th>
								<th class="text-center">Jumlah</th>
								<th class="text-center">Action</th>
							</tr>
						</thead>
						<tbody class="list_expense_kembalian">
							<?php
							$no = 1;
							foreach ($data_penggantian_kasbon as $item) {
								echo '<tr>';
								echo '<td class="text-center">' . $no . '</td>';
								echo '<td class="text-left">' . $item->no_doc . '</td>';
								echo '<td class="text-left">' . $item->informasi . '</td>';
								echo '<td class="text-center">' . number_format($item->jumlah) . '</td>';
								echo '<td class="text-center">
									<button type="button" class="btn btn-sm btn-success	add_ganti_expense" data-no_doc="' . $item->no_doc . '">Bayar</button>
								</td>';
								echo '</tr>';

								$no++;
							}
							?>
						</tbody>
					</table>

					<h4>List Penggunaan Pettycash</h4>
					<table class="table table-bordered ">
						<thead>
							<tr>
								<th class="text-center">PIC</th>
								<th class="text-center">No. Dokumen</th>
								<th class="text-center">Keterangan</th>
								<th class="text-center">Jumlah</th>
								<th class="text-center">Action</th>
							</tr>
						</thead>
						<tbody class="list_kasbon_pr_non_po">
							<?php
							foreach ($data_kasbon_pr_pet as $item) {
								echo '<tr>';
								echo '<td class="text-center">' . $item->created_by . '</td>';
								echo '<td class="text-left">' . $item->no_doc . '</td>';
								echo '<td class="text-left">' . $item->keperluan . '</td>';
								echo '<td class="text-center">' . number_format($item->jumlah_kasbon) . '</td>';
								echo '<td class="text-center">
									<button type="button" class="btn btn-sm btn-success add_kasbon_pr" data-no_doc="' . $item->no_doc . '">Bayar</button>
								</td>';
								echo '</tr>';
							}
							?>
						</tbody>
					</table>

				<?php
				}
				?>

				<div>
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
				<div class="table-responsive">
					<table class="table table-bordered table-striped" width="100%">
						<thead>
							<tr>
								<th width="5" scope="col" class="column-primary">#</th>
								<th scope="col" width="250">Jenis dan<br /> Tanggal</th>
								<th scope="col" width="250">Barang/Jasa &<br />Keterangan</th>
								<th scope="col" width=150 nowrap>Jumlah</th>
								<th scope="col" width=200 nowrap>Harga Satuan</th>
								<th scope="col" width="200">Expense</th>
								<th scope="col" width="200">Kasbon</th>
								<th scope="col" width="50">Bon Bukti</th>
								<th scope="col" class="column-primary">
									<div class="pull-right">
										<a class="btn btn-info btn-xs stsview" href="javascript:void(0)" title="Kasbon" onclick="add_kasbon()" id="add-kasbon"><i class="fa fa-user"></i> Kasbon</a><br />
										<a class="btn btn-success btn-xs stsview" href="javascript:void(0)" title="Tambah" onclick="add_detail()" id="add-material"><i class="fa fa-plus"></i> Tambah</a>
									</div>
								</th>
							</tr>
						</thead>
						<tbody id="detail_body">
							<?php $total = 0;
							$idd = 1;
							$grand_total = 0;
							$total_expense = 0;
							$total_kasbon = 0;
							if (!empty($data_detail)) {
								foreach ($data_detail as $record) {
									$tekskasbon = "";
									if ($record->id_kasbon != '') $tekskasbon = ' readonly'; ?>
									<tr id='tr1_<?= $idd ?>' class='delAll <?= ($record->id_kasbon != '' ? 'kasbonrow' : '') ?>'>
										<td data-header="#">
											<input type='hidden' name='id_kasbon[]' id=id_kasbon_<?= $idd ?>' value='<?= $record->id_kasbon; ?>'>
											<input type="hidden" name="filename[]" id="filename_<?= $idd ?>" value="<?= $record->doc_file; ?>">
											<input type="hidden" name="detail_id[]" id="raw_id_<?= $idd ?>" value="<?= $idd; ?>" class="dtlloop">
											<input type="hidden" name="id_detail[]" id="id_detail_<?= $idd ?>" value="<?= $record->id; ?>" class="dtlloop"><?= $idd; ?>
											<input type="hidden" name="no_docc[]" id="id_detail_<?= $idd ?>" value="<?= $record->no_doc ?>">
											<?php
											if ($record->kasbon_pr_non_po_pett !== '' && $record->kasbon_pr_non_po_pett !== null) {
											?>
												<input type='hidden' name='kasbon_pr_non_po_<?= $idd ?>' id='raw_id_<?= $idd ?>' value='<?= $record->kasbon_pr_non_po_pett ?>' class='dtlloop'>
											<?php
											}
											?>
										</td>
										<td data-header="Jenis & Tanggal">
											<?php
											if ($tekskasbon == '') {
												echo form_dropdown('coa[]', $data_budget, (isset($record->coa) ? $record->coa : ''), array('id' => 'coa' . $idd, 'required' => 'required', 'class' => 'form-control select2', 'style' => 'width:300px'));
											} else {
												echo '<input type="hidden" name="coa[]" id="coa' . $idd . '" value="' . $record->coa . '">';
											}
											?>
											<input type="text" class="form-control tanggal input-sm" name="tanggal[]" id="tanggal<?= $idd; ?>" value="<?= $record->tanggal; ?>" <?= $tekskasbon ?>>
										</td>
										<td data-header="Barang / Jasa & Keterangan"><input type="text" class="form-control input-sm" name="deskripsi[]" id="deskripsi_<?= $idd; ?>" value="<?= $record->deskripsi; ?>" <?= $tekskasbon ?> style='width:100px'>
											<input type="text" class="form-control input-sm" name="keterangan[]" id="keterangan_<?= $idd; ?>" value="<?= $record->keterangan; ?>" style='width:100px'>
										</td>
										<td data-header="Qty"><input type="text" class="form-control divide input-sm" name="qty[]" id="qty_<?= $idd; ?>" value="<?= $record->qty; ?>" onblur="cektotal(<?= $idd; ?>)" <?= $tekskasbon ?> size="15"></td>
										<td data-header="Harga Satuan"><input type="text" class="form-control divide input-sm" name="harga[]" id="harga_<?= $idd; ?>" value="<?= $record->harga; ?>" onblur="cektotal(<?= $idd; ?>)" <?= $tekskasbon ?>></td>
										<td data-header="Expense"><input type="text" class="form-control divide subtotal input-sm" name="expense[]" id="expense_<?= $idd; ?>" value="<?= ($record->expense); ?>" tabindex="-1" readonly></td>
										<td data-header="Kasbon"><input type="text" class="form-control divide subkasbon input-sm" name="kasbon[]" id="kasbon_<?= $idd; ?>" value="<?= ($record->kasbon); ?>" tabindex="-1" readonly></td>
										<td data-header="Bon Bukti" width="50">
											<div class="upload-btn-wrapper">
												<!--<label for="doc_file<?= $idd ?>" <?= ($tekskasbon != '' ? 'class="hidden"' : '') ?> >Upload file</label>-->
												<input type="file" name="doc_file_<?= $idd ?>" id="doc_file_<?= $idd ?>" />
											</div>
											<span class="pull-right"><?= ($record->doc_file != '' ? '<a href="' . base_url('assets/expense/' . $record->doc_file) . '" download target="_blank"><i class="fa fa-download"></i></a>' : '') ?></span>
										</td>
										<?php
										if ($record->kasbon_pr_non_po_pett !== null) {
										?>
											<th scope="row" align='center'><button type='button' class='btn btn-danger btn-xs stsview' data-toggle='tooltip' onClick='delDetailPRPett(<?= $idd ?>, "<?= $record->kasbon_pr_non_po_pett ?>")' title='Hapus data'><i class='fa fa-close'></i> Hapus</button></th>
										<?php
										} else if ($record->id_expense_bayar_sisa !== null) {
										?>
											<th scope="row" align='center'><button type='button' class='btn btn-danger btn-xs stsview' data-toggle='tooltip' onClick='delDetailKembalian(<?= $idd ?>, "<?= $record->id_expense_bayar_sisa ?>")' title='Hapus data'><i class='fa fa-close'></i> Hapus</button></th>
										<?php
										} else {
										?>
											<th scope="row" align='center'><button type='button' class='btn btn-danger btn-xs stsview' data-toggle='tooltip' onClick='delDetail(<?= $idd ?>)' title='Hapus data'><i class='fa fa-close'></i> Hapus</button></th>
										<?php
										}
										?>
									</tr>
							<?php
									if ($record->doc_file != '') {
										if (strpos($record->doc_file, 'pdf', 0) > 1) {
											$gambar .= '<div class="col-md-12">
								<iframe src="' . base_url('assets/expense/' . $record->doc_file) . '#toolbar=0&navpanes=0" title="PDF" style="width:600px; height:500px;" frameborder="0">
										 <a href="' . base_url('assets/expense/' . $record->doc_file) . '">Download PDF</a>
								</iframe>
								<br />' . $record->no_doc . '</div>';
										} else {
											$gambar .= '<div class="col-md-4"><a href="' . base_url('assets/expense/' . $record->doc_file) . '" target="_blank"><img src="' . base_url('assets/expense/' . $record->doc_file) . '" class="img-responsive"></a><br />' . $record->no_doc . '</div>';
										}
									}
									$total_expense = ($total_expense + ($record->expense));
									$total_kasbon = ($total_kasbon + ($record->kasbon));
									$idd++;
								}
								$grand_total = ($grand_total + ($total_expense - $total_kasbon));
							} ?>
						</tbody>
						<tfoot>
							<tr>
								<td colspan="5" align=right>TOTAL</td>
								<td><input type="text" class="form-control divide input-sm" id="total_expense" name="total_expense" value="<?= $total_expense ?>" placeholder="Total Expense" tabindex="-1" readonly style='width:90px'></td>
								<td><input type="text" class="form-control divide input-sm" id="total_kasbon" name="total_kasbon" value="<?= $total_kasbon ?>" placeholder="Total Kasbon" tabindex="-1" readonly style='width:90px'></td>
								<td align=right colspan=2>
									<div class="row">
										<div class="col-md-2">Saldo</div>
										<div class="col-md-10"><input type="text" class="form-control divide input-sm" id="grand_total" name="grand_total" value="<?= $grand_total ?>" placeholder="Grand Total" tabindex="-1" readonly style='width:90px'></div>
									</div>
									<div class="row">
										<div class="col-md-2">Budget</div>
										<div class="col-md-10"><input type="text" class="form-control divide input-sm" id="budgets" name="budgets" value="<?= $budgets ?>" tabindex="-1" disabled style='width:90px'></div>
									</div>
								</td>
							</tr>
						</tfoot>
					</table>
				</div>
				<div class="box-footer">
					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-10">
							<?php
							$urlback = 'petty_cash/';
							if (isset($data)) {
								if ($data->status == 0) {
									if ($stsview == 'approval') {
										$urlback = 'list_expense_approval';
										echo '<a class="btn btn-warning btn-sm" onclick="data_approve()"><i class="fa fa-check-square-o">&nbsp;</i>Approve</a>';
										echo ' <a class="btn btn-danger btn-sm" onclick="data_reject()"><i class="fa fa-ban">&nbsp;</i> Reject</a>';
									}
								}
							}
							?>
							<button type="submit" name="save" class="btn btn-success btn-sm stsview" id="submit"><i class="fa fa-save">&nbsp;</i>Simpan</button>
							<a class="btn btn-default btn-sm" onclick="window.location.reload();return false;"><i class="fa fa-reply">&nbsp;</i>Batal</a>
						</div>
					</div>
					<div class="row">
						<?= $gambar ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?= form_close() ?>
	<?php
	/*
foreach($data_budget as $keys=>$val){
	$datacombocoa.="<option value='".$keys."'>".$val."</option>";
}
*/
	?>
	<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>
	<script type="text/javascript">
		var combocoa = "<?= $datacombocoa ?>";

		$(document).ready(function() {
			$('.dataTable-lpp').dataTable();
		});



		function getcoabudget(datacoa) {
			formdata = {
				coa: datacoa
			};
			$.ajax({
				url: siteurl + 'expense/getcoabudget/',
				type: 'POST',
				data: formdata,
				success: function(msg) {
					combocoa = msg;
					//				console.log(msg);
				},
				error: function(msg) {
					console.log(msg);
				}
			});
		}
		<?php if ($datacombocoa != '') {
			echo "getcoabudget('" . $datacombocoa . "');
	";
		} ?>
		$('#pettycash').blur(function() {
			tipe = $(this).val();
			budgets = $(this).find(':selected').data('budget');
			approval = $(this).find(':selected').data('approval');
			$("#budgets").val(budgets);
			$("#approval").val(approval);
			coa = $(this).find(':selected').data('coa');
			getcoabudget(coa);
		});
		var url_save = siteurl + 'expense/save/';
		var url_approve = siteurl + 'expense/approve/';
		var nomor = <?= $idd ?>;
		$('.divide').divide();
		$('.select2').select2();
		$('#frm_data').on('submit', function(e) {
			e.preventDefault();
			var errors = "";
			var lops = 0;
			$('.dtlloop').each(function() {
				lops++;
				var iddtl = $(this).val();
				// if($("#filename_"+iddtl).val()=="") {
				// 	if ($('#doc_file_'+iddtl).get(0).files.length === 0) {
				// 		errors="Bon Bukti harus diupload";
				// 	}
				// }
			});
			if (lops == 0) errors = "Detail harus diisi";
			if ($("#informasi").val() == "") errors = "Keterangan tidak boleh kosong";
			if ($("#coa").val() == "0") errors = "Jenis Expense tidak boleh kosong";
			if ($("#tgl_doc").val() == "") errors = "Tanggal Transaksi tidak boleh kosong";
			if (parseFloat($("#grand_total").val()) > parseFloat($("#budgets").val())) errors = "Saldo lebih dari budget";
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
										window.location = siteurl + 'expense/<?= $urlback ?>';
									} else {
										swal({
											title: "Gagal!",
											text: "Data Gagal Di Simpan",
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

				//			data_save();
			} else {
				swal(errors);
				return false;
			}
		});
		<?php if (isset($stsview)) {
			if ($stsview == 'view') {
		?>
				$(".stsview").addClass("hidden");
				$("#frm_data :input").prop("disabled", true);
			<?php
			}
			if ($stsview == 'approval') {
			?>
				$(".stsview").addClass("hidden");
				$("#frm_data :input").prop("disabled", true);
		<?php
			}
		} ?>
		$(function() {
			$(".tanggal").datepicker({
				todayHighlight: true,
				format: "yyyy-mm-dd",
				showInputs: true,
				autoclose: true
			});
		});

		function cektotal(id) {
			var sqty = $("#qty_" + id).val();
			var pref = $("#harga_" + id).val();
			var subtotal = (parseFloat(sqty) * parseFloat(pref));
			$("#expense_" + id).val(subtotal);
			var sum = 0;
			$('.subtotal').each(function() {
				sum += Number($(this).val());
			});
			$("#total_expense").val(sum);
			var sumkasbon = 0;
			$('.subkasbon').each(function() {
				sumkasbon += Number($(this).val());
			});
			$("#total_kasbon").val(sumkasbon);
			$("#grand_total").val(Number(sum) - Number(sumkasbon));
		}

		function add_kasbon() {
			$('.kasbonrow').remove();
			var nama = $("#nama").val();
			var departement = $("#departement").val();
			$.ajax({
				url: siteurl + 'expense/get_kasbon/' + nama + '/' + departement + '/<?= (isset($data->no_doc) ? $data->no_doc : ""); ?>',
				cache: false,
				type: "POST",
				dataType: "json",
				success: function(data) {
					var i;
					for (i = 0; i < data.length; i++) {
						var Rows = "<tr id='tr1_" + nomor + "' class='delAll kasbonrow'>";
						Rows += "<td data-header='#'><input type='hidden' name='id_kasbon[]' id='id_kasbon_" + nomor + "' value='" + data[i].no_doc + "'>";
						Rows += "<input type='hidden' name='detail_id[]' id='raw_id_" + nomor + "' value='" + nomor + "'>";
						Rows += "<input type='hidden' name='id_detail[]' id='id_detail_" + nomor + "' value='" + data[i].id + "'>";
						Rows += "<input type='hidden' name='filename[]' id='filename_" + nomor + "' value='" + data[i].doc_file + "'></td>";
						Rows += "<td data-header='Tanggal'>";
						Rows += "<input type='text' class='form-control tanggal input-sm' name='tanggal[]' id='tanggal_" + nomor + "' tabindex='-1' readonly value='" + data[i].tgl_doc + "' />";
						Rows += "<input type='hidden' name='coa[]' id='coa_" + nomor + "' value='" + data[i].coa + "' />";
						Rows += "</td>";
						Rows += "<td data-header='Barang / Jasa & Keteranga'>";
						Rows += "<input type='text' class='form-control input-sm' name='deskripsi[]' id='deskripsi_" + nomor + "' value='" + data[i].keperluan + "' tabindex='-1' readonly />";
						Rows += "<input type='text' class='form-control input-sm' name='keterangan[]' id='keterangan_" + nomor + "' />";
						Rows += "</td>";
						Rows += "<td data-header='Qty'>";
						Rows += "<input type='text' class='form-control divide input-sm' name='qty[]' value='1' id='qty_" + nomor + "' tabindex='-1' readonly />";
						Rows += "</td>";
						Rows += "<td data-header='Harga Satuan'>";
						Rows += "<input type='text' class='form-control divide input-sm' name='harga[]' value='0' id='harga_" + nomor + "' tabindex='-1' readonly />";
						Rows += "</td>";
						Rows += "<td data-header='Expense'>";
						Rows += "<input type='text' class='form-control divide input-sm subtotal hidden' name='expense[]' value='0' id='expense_" + nomor + "' tabindex='-1' readonly />";
						Rows += "</td>";
						Rows += "<td data-header='Kasbon'>";
						Rows += "<input type='text' class='form-control divide input-sm subkasbon' name='kasbon[]' value='" + data[i].jumlah_kasbon + "' id='kasbon_" + nomor + "' tabindex='-1' readonly />";
						Rows += "</td>";
						Rows += "<td data-header='Bon Bukti'>";
						Rows += "<input type='file'  name='doc_file_" + nomor + "' id='doc_file_" + nomor + "' class='hidden' />";
						Rows += "<span class='pull-right'>";
						if (data[i].doc_file != '') {
							Rows += "<a href='<?= base_url('assets/expense/') ?>" + data[i].doc_file + "' download target='_blank'><i class='fa fa-download'></i></a></span>";
						}
						Rows += "</td>";
						Rows += "< th scope='row' align='center'>";
						Rows += "<button type='button' class='btn btn-danger btn-xs' data-toggle='tooltip' onClick='delDetail(" + nomor + ")' title='Hapus data'><i class='fa fa-close'></i> Hapus</button>";
						Rows += "</th>";
						Rows += "</tr>";
						nomor++;
						$('#detail_body').append(Rows);
						cektotal(nomor - 1);
					}
					$(".divide").divide();
				},
				error: function() {
					swal({
						title: "Error Message !",
						text: 'Connection Time Out. Please try again..',
						type: "warning",
						timer: 3000,
						showCancelButton: false,
						showConfirmButton: false,
						allowOutsideClick: false
					});
				}
			});
		}

		function add_detail() {
			var Rows = "<tr id='tr1_" + nomor + "' class='delAll'>";
			Rows += "<td data-header='#'><input type='hidden' name='id_kasbon[]' id='id_kasbon_" + nomor + "' value=''>";
			Rows += "<input type='hidden' name='detail_id[]' id='raw_id_" + nomor + "' value='" + nomor + "' class='dtlloop'>";
			Rows += "<input type='hidden' name='id_detail[]' id='id_detail_" + nomor + "' value='' class='dtlloop'>";
			Rows += "<input type='hidden' name='filename[]' id='filename_" + nomor + "' value=''></td>";
			Rows += "<td data-header='Jenis & Tanggal'>";
			Rows += "<select name='coa[]' id='coa_" + nomor + "' required='required' class='form-control select2' style='width:300px'>" + combocoa + "</select>";
			Rows += "<input type='text' class='form-control tanggal input-sm' placeholder='Tanggal' name='tanggal[]' id='tanggal_" + nomor + "' />";
			Rows += "</td>";
			Rows += "<td data-header='Barang / Jasa & Keterangan'>";
			Rows += "<input type='text' class='form-control input-sm' placeholder='Barang/Jasa' name='deskripsi[]' id='deskripsi_" + nomor + "' style='width:100px' />";
			Rows += "<input type='text' class='form-control input-sm' placeholder='Keterangan' name='keterangan[]' id='keterangan_" + nomor + "' style='width:100px' />";
			Rows += "</td>";
			Rows += "<td data-header='Qty'>";
			Rows += "<input type='text' class='form-control divide input-sm' name='qty[]' value='0' id='qty_" + nomor + "' onblur='cektotal(" + nomor + ")' style='width:60px' />";
			Rows += "</td>";
			Rows += "<td data-header='Harga Satuan'>";
			Rows += "<input type='text' class='form-control divide input-sm' name='harga[]' value='0' id='harga_" + nomor + "' onblur='cektotal(" + nomor + ")' style='width:90px' />";
			Rows += "</td>";
			Rows += "<td data-header='Expense'>";
			Rows += "<input type='text' class='form-control divide input-sm subtotal' name='expense[]' value='0' id='expense_" + nomor + "' tabindex='-1' readonly style='width:90px' />";
			Rows += "</td>";
			Rows += "<td data-header='Kasbon'>";
			Rows += "<input type='text' class='form-control divide input-sm subkasbon hidden' name='kasbon[]' value='0' id='kasbon_" + nomor + "' tabindex='-1' readonly />";
			Rows += "</td>";
			Rows += "<td data-header='Bon Bukti'>";
			Rows += "<input type='file'  name='doc_file_" + nomor + "' id='doc_file_" + nomor + "'  />";
			Rows += "</td>";
			Rows += "<th align='center' th scope='row'>";
			Rows += "<button type='button' class='btn btn-danger btn-xs' data-toggle='tooltip' onClick='delDetail(" + nomor + ")' title='Hapus data'><i class='fa fa-close'></i> Hapus</button>";
			Rows += "</th>";
			Rows += "</tr>";
			$("#tanggal_" + nomor).focus();
			nomor++;
			$('#detail_body').append(Rows);
			$(".tanggal").datepicker({
				todayHighlight: true,
				format: "yyyy-mm-dd",
				showInputs: true,
				autoclose: true
			});
			$('.select2').select2();
			$(".divide").divide();
		}

		function delDetail(row) {

			var id_detail = $('#id_detail_' + row).val();
			$.ajax({
				type: "POST",
				url: siteurl + active_controller + '/del_detail',
				data: {
					'id_detail': id_detail
				},
				cache: false,
				success: function(result) {
					refresh_list_kasbon_non_pr();
					refresh_list_expense_kembalian();
				}
			});

			$('#tr1_' + row).remove();
			cektotal(row);
		}

		function delDetailPRPett(row, id_kasbon_non_po) {

			var id_detail = $('#id_detail_' + row).val();
			$.ajax({
				type: "POST",
				url: siteurl + active_controller + '/del_detail_kasbon_non_po',
				data: {
					'id_detail': id_detail,
					'id_kasbon_non_po': id_kasbon_non_po
				},
				cache: false,
				success: function(result) {
					refresh_list_kasbon_non_pr();
				}
			});

			$('#tr1_' + row).remove();
			cektotal(row);
		}

		function delDetailKembalian(row, id_expense_kembalian) {
			var id_detail = $('#id_detail_' + row).val();
			$.ajax({
				type: "POST",
				url: siteurl + active_controller + '/del_detail_kembalian_expense',
				data: {
					'id_detail': id_detail,
					'id_expense_kembalian': id_expense_kembalian
				},
				cache: false,
				success: function(result) {
					refresh_list_expense_kembalian();
				}
			});

			$('#tr1_' + row).remove();
			cektotal(row);
		}

		function data_approve() {
			swal({
					title: "Anda Yakin?",
					text: "Data Akan Disetujui!",
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
										text: "Data Berhasil Di Setujui",
										type: "success",
										timer: 1500,
										showConfirmButton: false
									});
									window.location = siteurl + 'expense/<?= $urlback ?>';
								} else {
									swal({
										title: "Gagal!",
										text: "Data Gagal Di Setujui",
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
										'table': 'tr_expense'
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

		function refresh_list_kasbon_non_pr(no_doc = null) {
			$.ajax({
				type: 'post',
				url: siteurl + active_controller + 'refresh_list_kasbon_non_pr',
				data: {
					'no_doc': no_doc
				},
				cache: false,
				success: function(result) {
					$('.list_kasbon_pr_non_po').html(result);
				},
				error: function(result) {
					swal({
						title: 'Error !',
						text: 'Please try again later !',
						type: 'error'
					});
				}
			});
		}

		function refresh_list_expense_kembalian(no_doc = null) {
			$.ajax({
				type: 'post',
				url: siteurl + active_controller + 'refresh_list_expense_kembalian',
				data: {
					'no_doc': no_doc
				},
				cache: false,
				success: function(result) {
					$('.list_expense_kembalian').html(result);
				},
				error: function(result) {
					swal({
						title: 'Error !',
						text: 'Please try again later !',
						type: 'error'
					});
				}
			});
		}

		$(document).on('click', '.add_kasbon_pr', function() {
			var no_doc = $(this).data('no_doc');
			var nama = $("#nama").val();
			var departement = $("#departement").val();

			$.ajax({
				type: 'post',
				url: siteurl + active_controller + 'add_kasbon_pr',
				data: {
					'no_doc': no_doc,
					'nama': nama,
					'department': departement
				},
				dataType: 'json',
				cache: false,
				success: function(result) {
					refresh_list_kasbon_non_pr(no_doc);

					var Rows = "<tr id='tr1_" + nomor + "' class='delAll'>";
					Rows += "<td data-header='#'><input type='hidden' name='id_kasbon[]' id='id_kasbon_" + nomor + "' value=''>";
					Rows += "<input type='hidden' name='kasbon_pr_non_po_" + nomor + "' id='raw_id_" + nomor + "' value='" + result.no_doc + "' class='dtlloop'>";
					Rows += "<input type='hidden' name='detail_id[]' id='raw_id_" + nomor + "' value='" + nomor + "' class='dtlloop'>";
					Rows += "<input type='hidden' name='id_detail[]' id='id_detail_" + nomor + "' value='' class='dtlloop'>";
					Rows += "<input type='hidden' name='filename[]' id='filename_" + nomor + "' value=''></td>";
					Rows += "<td data-header='Jenis & Tanggal'>";
					Rows += "<select name='coa[]' id='coa_" + nomor + "' required='required' class='form-control select2' style='width:300px'>" + combocoa + "</select>";
					Rows += "<input type='text' class='form-control tanggal input-sm' placeholder='Tanggal' name='tanggal[]' id='tanggal_" + nomor + "' />";
					Rows += "</td>";
					Rows += "<td data-header='Barang / Jasa & Keterangan'>";
					Rows += "<input type='text' class='form-control input-sm' placeholder='Barang/Jasa' name='deskripsi[]' id='deskripsi_" + nomor + "' value='" + result.keperluan + "' style='width:100px' />";
					Rows += "<input type='text' class='form-control input-sm' placeholder='Keterangan' name='keterangan[]' id='keterangan_" + nomor + "' style='width:100px' />";
					Rows += "</td>";
					Rows += "<td data-header='Qty'>";
					Rows += "<input type='text' class='form-control divide input-sm' name='qty[]' value='1' id='qty_" + nomor + "' onblur='cektotal(" + nomor + ")' style='width:60px' />";
					Rows += "</td>";
					Rows += "<td data-header='Harga Satuan'>";
					Rows += "<input type='text' class='form-control divide input-sm' name='harga[]' value='" + result.jumlah_kasbon + "' id='harga_" + nomor + "' style='width:90px' readonly/>";
					Rows += "</td>";
					Rows += "<td data-header='Expense'>";
					Rows += "<input type='text' class='form-control divide input-sm subtotal' name='expense[]' value='" + result.jumlah_kasbon + "' id='expense_" + nomor + "' tabindex='-1' readonly style='width:90px' />";
					Rows += "</td>";
					Rows += "<td data-header='Kasbon'>";
					Rows += "<input type='text' class='form-control divide input-sm subkasbon hidden' name='kasbon[]' value='0' id='kasbon_" + nomor + "' tabindex='-1' readonly />";
					Rows += "</td>";
					Rows += "<td data-header='Bon Bukti'>";
					Rows += "<input type='file'  name='doc_file_" + nomor + "' id='doc_file_" + nomor + "'  />";
					Rows += "</td>";
					Rows += "<th align='center' th scope='row'>";
					Rows += "<button type='button' class='btn btn-danger btn-xs' data-toggle='tooltip' onClick='delDetail(" + nomor + ")' title='Hapus data'><i class='fa fa-close'></i> Hapus</button>";
					Rows += "</th>";
					Rows += "</tr>";
					$("#tanggal_" + nomor).focus();
					nomor++;
					$('#detail_body').append(Rows);
					$(".tanggal").datepicker({
						todayHighlight: true,
						format: "yyyy-mm-dd",
						showInputs: true,
						autoclose: true
					});
					$('.select2').select2();
					$(".divide").divide();
					cektotal(nomor - 1);
				},
				error: function(result) {
					swal({
						title: 'Error !',
						text: 'Please try again later !',
						type: 'error'
					});
				}
			});
		});

		$(document).on('click', '.add_ganti_expense', function() {
			var no_doc = $(this).data('no_doc');
			var tgl = $('#tgl_doc').val()

			$.ajax({
				type: "POST",
				url: siteurl + active_controller + 'add_ganti_expense',
				data: {
					'no_doc': no_doc,
					'tgl': tgl
				},
				dataType: "JSON",
				cache: false,
				success: function(result) {
					refresh_list_expense_kembalian(no_doc);

					var Rows = "<tr id='tr1_" + nomor + "' class='delAll'>";
					Rows += "<td data-header='#'><input type='hidden' name='id_kasbon[]' id='id_kasbon_" + nomor + "' value=''>";
					Rows += "<input type='hidden' name='pengembalian_expense_" + nomor + "' id='raw_id_" + nomor + "' value='" + result.no_doc + "' class='dtlloop'>";
					Rows += "<input type='hidden' name='detail_id[]' id='raw_id_" + nomor + "' value='" + nomor + "' class='dtlloop'>";
					Rows += "<input type='hidden' name='id_detail[]' id='id_detail_" + nomor + "' value='" + result.id_detail + "' class='dtlloop'>";
					Rows += "<input type='hidden' name='no_docc[]' id='no_doc_" + nomor + "' value='" + result.no_doc2 + "' class='dtlloop'>";
					Rows += "<input type='hidden' name='filename[]' id='filename_" + nomor + "' value=''></td>";
					Rows += "<td data-header='Jenis & Tanggal'>";
					Rows += "<select name='coa[]' id='coa_" + nomor + "' required='required' class='form-control select2' style='width:300px'>" + combocoa + "</select>";
					Rows += "<input type='text' class='form-control tanggal input-sm' placeholder='Tanggal' name='tanggal[]' id='tanggal_" + nomor + "' />";
					Rows += "</td>";
					Rows += "<td data-header='Barang / Jasa & Keterangan'>";
					Rows += "<input type='text' class='form-control input-sm' placeholder='Barang/Jasa' name='deskripsi[]' id='deskripsi_" + nomor + "' value='" + result.informasi + "' style='width:100px' />";
					Rows += "<input type='text' class='form-control input-sm' placeholder='Keterangan' name='keterangan[]' id='keterangan_" + nomor + "' style='width:100px' />";
					Rows += "</td>";
					Rows += "<td data-header='Qty'>";
					Rows += "<input type='text' class='form-control divide input-sm' name='qty[]' value='1' id='qty_" + nomor + "' onblur='cektotal(" + nomor + ")' style='width:60px' />";
					Rows += "</td>";
					Rows += "<td data-header='Harga Satuan'>";
					Rows += "<input type='text' class='form-control divide input-sm' name='harga[]' value='" + result.jumlah + "' id='harga_" + nomor + "' style='width:90px' readonly/>";
					Rows += "</td>";
					Rows += "<td data-header='Expense'>";
					Rows += "<input type='text' class='form-control divide input-sm subtotal' name='expense[]' value='" + result.jumlah + "' id='expense_" + nomor + "' tabindex='-1' readonly style='width:90px' />";
					Rows += "</td>";
					Rows += "<td data-header='Kasbon'>";
					Rows += "<input type='text' class='form-control divide input-sm subkasbon hidden' name='kasbon[]' value='0' id='kasbon_" + nomor + "' tabindex='-1' readonly />";
					Rows += "</td>";
					Rows += "<td data-header='Bon Bukti'>";
					Rows += "<input type='file'  name='doc_file_" + nomor + "' id='doc_file_" + nomor + "'  />";
					Rows += "</td>";
					Rows += "<th align='center' th scope='row'>";
					Rows += "<button type='button' class='btn btn-danger btn-xs' data-toggle='tooltip' onClick='delDetailKembalian(" + nomor + ", " + '"' + result.no_doc + '"' + ")' title='Hapus data'><i class='fa fa-close'></i> Hapus</button>";
					Rows += "</th>";
					Rows += "</tr>";
					$("#tanggal_" + nomor).focus();
					nomor++;
					$('#detail_body').append(Rows);
					$(".tanggal").datepicker({
						todayHighlight: true,
						format: "yyyy-mm-dd",
						showInputs: true,
						autoclose: true
					});
					$('.select2').select2();
					$(".divide").divide();
					cektotal(nomor - 1);
				},
				error: function(result) {
					swal({
						title: 'Error !',
						text: 'Please try again later !',
						type: 'error'
					});
				}
			})
		});
	</script>