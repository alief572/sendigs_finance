<?= form_open($this->uri->uri_string(), array('id' => 'frm_data', 'name' => 'frm_data', 'role' => 'form', 'class' => 'form-horizontal', 'enctype' => 'multipart/form-data')); ?>
<?php
$dept = '';
$bank_id = '';
$accnumber = '';
$accname = '';
if (!isset($data->departement)) {
	$datauser = $this->db->get_where('users', ['username' => $this->auth->user_name()])->row();
	$datadept = $this->db->get_where('employee', ['id' => $datauser->employee_id])->row();
	if (!empty($datadept)) {
		$dept = $datadept->department_id;
		$bank_id = $datadept->bank_id;
		$accnumber = $datadept->accnumber;
		$accname = $datadept->accname;
	}
}

$datauser = $this->db->get_where('users', ['username' => $this->auth->user_name()])->row();
$dept = $datauser->department_id;
?>
<input type="hidden" id="id" name="id" value="<?php echo set_value('id', isset($data->id) ? $data->id : ''); ?>">
<input type="hidden" id="departement" name="departement" value="<?php echo (isset($data->departement) ? $data->departement : $dept); ?>">
<input type="hidden" id="nama" name="nama" value="<?php echo (isset($data->nama) ? $data->nama : $this->auth->user_name()); ?>">
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
						<input type="date" class="form-control" id="tgl_doc" name="tgl_doc" value="<?php echo (isset($data->tgl_doc) ? $data->tgl_doc : date("Y-m-d")); ?>" placeholder="Tanggal Dokumen" required>
					</div>
				</div>
				<div class="form-group ">
					<label class="col-sm-2 control-label">Periode 1 <b class="text-red">*</b></label>
					<div class="col-sm-4">
						<input type="date" class="form-control " id="date1" name="date1" value="<?php echo (isset($data->date1) ? $data->date1 : date("Y-m-d")); ?>" placeholder="Tanggal Awal" required>
					</div>
					<label class="col-sm-2 control-label">Periode 2 <b class="text-red">*</b></label>
					<div class="col-sm-4">
						<input type="date" class="form-control" id="date2" name="date2" value="<?php echo (isset($data->date2) ? $data->date2 : date("Y-m-d")); ?>" placeholder="Tanggal Akhir" required>
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
				<div class="table-responsive">
					<table class="table table-bordered table-striped">
						<caption>
							<div class="pull-right">
								<a class="btn btn-info btn-xs stsview" href="javascript:void(0)" title="Transport" onclick="add_detail()" id="add-kasbon"><i class="fa fa-user"></i> Generate</a>
							</div>
						</caption>
						<thead>
							<tr>
								<th width="5">#</th>
								<th>Tanggal</th>
								<th width="100">Keperluan</th>
								<th width="100">Rute</th>
								<th>Bensin</th>
								<th>T o l</th>
								<th>Parkir</th>
								<th>Lain Lain</th>
								<th>KM Awal</th>
								<th>KM Akhir</th>
								<th>Total KM</th>
								<th width="50">Bukti</th>
							</tr>
						</thead>
						<tbody id="detail_body">
							<?php $total_bensin = 0;
							$total_tol = 0;
							$total_parkir = 0;
							$total_kasbon = 0;
							$idd = 1;
							$total_km = 0;
							$grand_total = 0;
							$total_lainnya = 0;
							$gambar = '';
							if (!empty($data_detail)) {
								foreach ($data_detail as $record) {
							?>
									<tr id='tr1_<?= $idd ?>' class='delAll'>
										<td>
											<input type="hidden" name="id_transport[]" id="id_transport_<?= $idd ?>" value="<?= $record->id; ?>"><?= $record->no_doc; ?>
											<input type='hidden' class='fben' name='bensin[]' value='<?= $record->bensin; ?>' id='bensin_<?= $idd ?>' />
											<input type='hidden' class='ftol' name='tol[]' value='<?= $record->tol; ?>' id='tol_<?= $idd ?>' />
											<input type='hidden' class='fpark' name='parkir[]' value='<?= $record->parkir; ?>' id='parkir_<?= $idd ?>' />
											<input type='hidden' class='flainnya' name='lainnya[]' value='<?= $record->lainnya; ?>' id='lainnya_<?= $idd ?>' />
										</td>
										<td><?= $record->tgl_doc; ?></td>
										<td><?= $record->keperluan; ?></td>
										<td><?= $record->rute; ?></td>
										<td class="divide"><?= $record->bensin; ?></td>
										<td class="divide"><?= $record->tol; ?></td>
										<td class="divide"><?= $record->parkir; ?></td>
										<td class="divide"><?= $record->lainnya; ?></td>
										<td class="divide"><?= $record->km_awal; ?></td>
										<td class="divide"><?= $record->km_akhir; ?></td>
										<td class="divide"><?= ($record->km_akhir - $record->km_awal); ?></td>
										<td><span class="pull-right"><?= ($record->doc_file != '' ? '<a href="' . base_url('assets/expense/' . $record->doc_file) . '" target="_blank"><i class="fa fa-download"></i></a>' : '') ?></span>
										</td>
									</tr>
							<?php
									if ($record->doc_file != '') {
										if (strpos($record->doc_file, 'pdf', 0) > 1) {
											$gambar .= '<div class="col-md-12">
								<iframe src="' . base_url('assets/expense/' . $record->doc_file) . '#toolbar=0&navpanes=0" title="PDF" style="width:600px; height:500px;" frameborder="0">
										 Presss me: <a href="' . base_url('assets/expense/' . $record->doc_file) . '">Download PDF</a>
								</iframe>
								<br />' . $record->no_doc . '</div>';
										} else {
											$gambar .= '<div class="col-md-3"><a href="' . base_url('assets/expense/' . $record->doc_file) . '" target="_blank"><img src="' . base_url('assets/expense/' . $record->doc_file) . '" class="img-responsive"></a><br />' . $record->no_doc . '</div>';
										}
									}

									$total_bensin = ($total_bensin + ($record->bensin));
									$total_tol = ($total_tol + ($record->tol));
									$total_parkir = ($total_parkir + ($record->parkir));
									$total_km = ($total_km + ($record->km_akhir - $record->km_awal));
									$total_lainnya = ($total_lainnya + $record->lainnya);
									$idd++;
								}
							}
							$grand_total = ($total_bensin + $total_tol + $total_parkir + $total_lainnya);
							?>
						</tbody>
						<tfoot>
							<tr class="info">
								<td colspan="4" align=right>SUB TOTAL</td>
								<td><input type="text" class="form-control divide input-sm" id="total_bensin" name="total_bensin" value="<?= $total_bensin ?>" placeholder="Total Bensin" tabindex="-1" readonly></td>
								<td><input type="text" class="form-control divide input-sm" id="total_tol" name="total_tol" value="<?= $total_tol ?>" placeholder="Total Tol" tabindex="-1" readonly></td>
								<td><input type="text" class="form-control divide input-sm" id="total_parkir" name="total_parkir" value="<?= $total_parkir ?>" placeholder="Total Parkir" tabindex="-1" readonly></td>
								<td><input type="text" class="form-control divide input-sm" id="total_lainnya" name="total_lainnya" value="<?= $total_lainnya ?>" placeholder="Total Lainnya" tabindex="-1" readonly></td>
								<td colspan=2></td>
								<td><input type="text" class="form-control divide input-sm" id="total_km" name="total_km" value="<?= $total_km ?>" placeholder="Total KM" tabindex="-1" readonly></td>
								<td></td>
							</tr>
							<tr class="warning">
								<td colspan="4" align=right>TOTAL</td>
								<td colspan="4"><input type="text" class="form-control divide input-sm" id="jumlah_expense" name="jumlah_expense" value="<?= $grand_total ?>" placeholder="Total" tabindex="-1" readonly></td>
								<td colspan=4></td>
							</tr>
						</tfoot>
					</table>
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
									}
								}
							}
							?>
							<button type="submit" name="save" class="btn btn-success btn-sm stsview" id="submit"><i class="fa fa-save">&nbsp;</i>Simpan</button>
							<a class="btn btn-default btn-sm" onclick="window.location=siteurl+'expense/transport_req<?= $mod ?>';return false;"><i class="fa fa-reply"></i> Batal</a>
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
	<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>
	<script type="text/javascript">
		var url_save = siteurl + 'expense/transport_req_save/';
		var url_approve = siteurl + 'expense/transport_req_approve/';
		var nomor = <?= $idd ?>;
		$('.divide').divide();
		$('#frm_data').on('submit', function(e) {
			e.preventDefault();
			var errors = "";
			if ($("#jumlah_expense").val() == "0") errors = "Total tidak boleh kosong";
			if ($("#coa").val() == "0") errors = "COA tidak boleh kosong";
			if ($("#tgl_doc").val() == "") errors = "Tanggal Transaksi tidak boleh kosong";
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
										window.location = siteurl + 'expense/transport_req';
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
		} ?>
		$(function() {
			$(".tanggal").datepicker({
				todayHighlight: true,
				format: "yyyy-mm-dd",
				showInputs: true,
				endDate: "0",
				autoclose: true
			});
		});

		function cektotal(id) {
			var sum = 0;
			$('.fben').each(function() {
				sum += Number($(this).val());
			});
			$("#total_bensin").val(sum);
			var sum1 = 0;
			$('.ftol').each(function() {
				sum1 += Number($(this).val());
			});
			$("#total_tol").val(sum1);
			var sum2 = 0;
			$('.fpark').each(function() {
				sum2 += Number($(this).val());
			});
			$("#total_parkir").val(sum2);
			var sum3 = 0;
			$('.fkm').each(function() {
				sum3 += Number($(this).val());
			});
			$("#total_km").val(sum3);
			var sum4 = 0;
			$('.flainnya').each(function() {
				sum4 += Number($(this).val());
			});
			$("#total_lainnya").val(sum4);
			$("#jumlah_expense").val(sum + sum1 + sum2 + sum4);
		}

		function add_detail() {
			$('.kasbonrow').remove();
			var nama = $("#nama").val();
			var departement = $("#departement").val();
			var date1 = $("#date1").val();
			var date2 = $("#date2").val();
			$.ajax({
				url: siteurl + 'expense/get_list_req_transport/' + nama + '/' + departement + '/' + date1 + '/' + date2,
				cache: false,
				type: "POST",
				dataType: "json",
				success: function(data) {
					var i;
					for (i = 0; i < data.length; i++) {
						var Rows = "<tr id='tr1_" + nomor + "' class='delAll kasbonrow'>";
						Rows += "<td><input type='hidden' name='id_transport[]' id='id_transport_" + nomor + "' value='" + data[i].id + "'>";
						Rows += data[i].no_doc + "</td>";
						Rows += "<td>" + data[i].tgl_doc + "</td>";
						Rows += "<td>" + data[i].keperluan + "</td>";
						Rows += "<td>" + data[i].rute + "</td>";
						Rows += "<td>";
						Rows += "<input type='text' class='form-control divide fben input-sm' name='bensin[]' value='" + data[i].bensin + "' id='bensin_" + nomor + "' tabindex='-1' readonly />";
						Rows += "</td>";
						Rows += "<td>";
						Rows += "<input type='text' class='form-control divide ftol input-sm' name='tol[]' value='" + data[i].tol + "' id='tol_" + nomor + "' tabindex='-1' readonly />";
						Rows += "</td>";
						Rows += "<td>";
						Rows += "<input type='text' class='form-control divide fpark input-sm' name='parkir[]' value='" + data[i].parkir + "' id='parkir_" + nomor + "' tabindex='-1' readonly />";
						Rows += "</td>";
						Rows += "<td>";
						Rows += "<input type='text' class='form-control divide flainnya input-sm' name='lainnya[]' value='" + data[i].lainnya + "' id='lainnya_" + nomor + "' tabindex='-1' readonly />";
						Rows += "</td>";
						Rows += "<td>";
						Rows += "<input type='text' class='form-control divide input-sm' name='km_awal[]' value='" + data[i].km_awal + "' id='km_awal_" + nomor + "' tabindex='-1' readonly />";
						Rows += "</td>";
						Rows += "<td>";
						Rows += "<input type='text' class='form-control divide input-sm' name='km_akhir[]' value='" + data[i].km_akhir + "' id='km_akhir_" + nomor + "' tabindex='-1' readonly />";
						Rows += "</td>";
						Rows += "<td>";
						Rows += "<input type='text' class='form-control divide fkm input-sm' name='total_km[]' value='" + (data[i].km_akhir - data[i].km_awal) + "' id='total_km_" + nomor + "' tabindex='-1' readonly />";
						Rows += "</td>";
						Rows += "<td>";
						Rows += "<span class='pull-right'>";
						if (data[i].doc_file != '') {
							Rows += "<a href='<?= base_url('assets/expense/') ?>" + data[i].doc_file + "' target='_blank'><i class='fa fa-download'></i></a></span>";
						}
						Rows += "</td>";
						Rows += "</tr>";
						nomor++;
						$('#detail_body').append(Rows);
					}
					$(".divide").divide();
					cektotal();
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

		function data_approve(id, status) {
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
						$.ajax({
							url: url_approve + id + '/' + status,
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
									window.location = siteurl + 'expense/transport_req<?= $mod ?>';
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
										'table': 'tr_transport_req'
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
											window.location = siteurl + 'expense/transport_req<?= $mod ?>';
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