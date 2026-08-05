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
						if (isset($data->reject_reason_finance)) {
							if ($data->reject_reason_finance != '') {
								echo '
								  <div class="alert alert-danger alert-dismissible">
									<h4><i class="icon fa fa-ban"></i> Alasan Penolakan!</h4>
									' . $data->reject_reason_finance . '
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
								<a class="btn btn-info btn-xs stsview" href="javascript:void(0)" title="Transport" onclick="add_detail()" id="add-kasbon"><i class="fa fa-plus"></i> Generate</a>
							</div>
						</caption>
						<thead>
							<tr>
								<th width="5">#</th>
								<th style="min-width: 100px;">Tanggal</th>
								<th style="min-width: 120px;">No. Polisi</th>
								<th style="min-width: 250px;">COA</th>
								<th style="min-width: 150px;">Keperluan</th>
								<th style="min-width: 150px;">Rute</th>
								<th style="min-width: 120px;">Bensin</th>
								<th style="min-width: 120px;">T o l</th>
								<th style="min-width: 120px;">Parkir</th>
								<th style="min-width: 120px;">Lain Lain</th>
								<th style="min-width: 100px;">KM Awal</th>
								<th style="min-width: 100px;">KM Akhir</th>
								<th style="min-width: 100px;">Total KM</th>
								<th style="min-width: 60px;">Bukti</th>
								<th style="min-width: 50px;" class="stsview">Aksi</th>
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
										</td>
										<td><?= $record->tgl_doc; ?></td>
										<td><input type='text' class='form-control input-sm detail-edit' name='nopol[]' value='<?= $record->nopol; ?>' id='nopol_<?= $idd ?>' /></td>
										<td>
											<select class="form-control select2 input-sm" name="coa[]" id="coa_<?= $idd ?>" onchange="set_nm_coa(<?= $idd ?>)" style="width:100%; min-width:200px;">
												<option value="" data-name="">- Pilih COA -</option>
												<?php
												if (!empty($list_coa)) {
													foreach ($list_coa as $item_coa) {
														$selected = ($record->no_coa == $item_coa->no_coa) ? 'selected' : '';
														echo '<option value="' . $item_coa->no_coa . '" data-name="' . $item_coa->nm_coa . '" ' . $selected . '>' . $item_coa->no_coa . ' - ' . $item_coa->nm_coa . '</option>';
													}
												}
												?>
											</select>
											<input type="hidden" name="nm_coa[]" id="nm_coa_<?= $idd ?>" value="<?= $record->nm_coa ?>">
										</td>
										<td><input type='text' class='form-control input-sm detail-edit' name='keperluan[]' value='<?= $record->keperluan; ?>' id='keperluan_<?= $idd ?>' /></td>
										<td><input type='text' class='form-control input-sm detail-edit' name='rute[]' value='<?= $record->rute; ?>' id='rute_<?= $idd ?>' /></td>
										<td><input type='text' class='form-control fben input-sm detail-edit' name='bensin[]' value='<?= $record->bensin; ?>' id='bensin_<?= $idd ?>' onblur='cektotal()' /></td>
										<td><input type='text' class='form-control ftol input-sm detail-edit' name='tol[]' value='<?= $record->tol; ?>' id='tol_<?= $idd ?>' onblur='cektotal()' /></td>
										<td><input type='text' class='form-control fpark input-sm detail-edit' name='parkir[]' value='<?= $record->parkir; ?>' id='parkir_<?= $idd ?>' onblur='cektotal()' /></td>
										<td><input type='text' class='form-control flainnya input-sm detail-edit' name='lainnya[]' value='<?= $record->lainnya; ?>' id='lainnya_<?= $idd ?>' onblur='cektotal()' /></td>
										<td><input type='text' class='form-control divide input-sm detail-edit fkm_awal' name='km_awal[]' value='<?= $record->km_awal; ?>' id='km_awal_<?= $idd ?>' onblur='cek_km(<?= $idd ?>)' /></td>
										<td><input type='text' class='form-control divide input-sm detail-edit fkm_akhir' name='km_akhir[]' value='<?= $record->km_akhir; ?>' id='km_akhir_<?= $idd ?>' onblur='cek_km(<?= $idd ?>)' /></td>
										<td><input type='text' class='form-control divide fkm input-sm' name='total_km[]' value='<?= ($record->km_akhir - $record->km_awal); ?>' id='total_km_<?= $idd ?>' tabindex='-1' readonly /></td>
										<td><span class="pull-right"><?= ($record->doc_file != '' ? '<a href="' . base_url('assets/expense/' . $record->doc_file) . '" target="_blank"><i class="fa fa-download"></i></a>' : '') ?></span>
										</td>
										<td class="stsview"><a href="javascript:void(0)" class="btn btn-danger btn-xs" onclick="remove_detail(<?= $idd ?>, <?= $record->id ?>)" title="Hapus detail"><i class="fa fa-trash"></i></a>
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
								<td colspan="6" align=right>SUB TOTAL</td>
								<td><input type="text" class="form-control divide input-sm" id="total_bensin" name="total_bensin" value="<?= $total_bensin ?>" placeholder="Total Bensin" tabindex="-1" readonly></td>
								<td><input type="text" class="form-control divide input-sm" id="total_tol" name="total_tol" value="<?= $total_tol ?>" placeholder="Total Tol" tabindex="-1" readonly></td>
								<td><input type="text" class="form-control divide input-sm" id="total_parkir" name="total_parkir" value="<?= $total_parkir ?>" placeholder="Total Parkir" tabindex="-1" readonly></td>
								<td><input type="text" class="form-control divide input-sm" id="total_lainnya" name="total_lainnya" value="<?= $total_lainnya ?>" placeholder="Total Lainnya" tabindex="-1" readonly></td>
								<td colspan=2></td>
								<td><input type="text" class="form-control divide input-sm" id="total_km" name="total_km" value="<?= $total_km ?>" placeholder="Total KM" tabindex="-1" readonly></td>
								<td colspan="2"></td>
							</tr>
							<tr class="warning">
								<td colspan="6" align=right>TOTAL</td>
								<td colspan="4"><input type="text" class="form-control divide input-sm" id="jumlah_expense" name="jumlah_expense" value="<?= $grand_total ?>" placeholder="Total" tabindex="-1" readonly></td>
								<td colspan=5></td>
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
		var removed_ids = []; // Track removed detail IDs
		$('.divide').divide();
		$('#frm_data').on('submit', function(e) {
			e.preventDefault();
			var errors = "";
			if ($("#jumlah_expense").val() == "0") errors = "Total tidak boleh kosong";
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
							// Append removed IDs to form data
							for (var i = 0; i < removed_ids.length; i++) {
								formdata.append('removed_transport[]', removed_ids[i]);
							}
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
			$(".select2").select2({width: '100%'});
		});

		function cektotal() {
			var sum = 0;
			$('.fben').each(function() {
				sum += Number(String($(this).val()).replace(/\./g, '').replace(/,/g, ''));
			});
			$("#total_bensin").val(sum);
			var sum1 = 0;
			$('.ftol').each(function() {
				sum1 += Number(String($(this).val()).replace(/\./g, '').replace(/,/g, ''));
			});
			$("#total_tol").val(sum1);
			var sum2 = 0;
			$('.fpark').each(function() {
				sum2 += Number(String($(this).val()).replace(/\./g, '').replace(/,/g, ''));
			});
			$("#total_parkir").val(sum2);
			var sum3 = 0;
			$('.fkm').each(function() {
				sum3 += Number(String($(this).val()).replace(/\./g, '').replace(/,/g, ''));
			});
			$("#total_km").val(sum3);
			var sum4 = 0;
			$('.flainnya').each(function() {
				sum4 += Number(String($(this).val()).replace(/\./g, '').replace(/,/g, ''));
			});
			$("#total_lainnya").val(sum4);
			$("#jumlah_expense").val(sum + sum1 + sum2 + sum4);
		}

		function cek_km(id) {
			var km_awal = Number(String($("#km_awal_"+id).val()).replace(/\./g, '').replace(/,/g, ''));
			var km_akhir = Number(String($("#km_akhir_"+id).val()).replace(/\./g, '').replace(/,/g, ''));
			var total_km = km_akhir - km_awal;
			if(total_km < 0) total_km = 0;
			
			$("#total_km_"+id).val(total_km);
			cektotal();
		}

		function set_nm_coa(id) {
			var nm_coa = $("#coa_"+id).find(':selected').data('name');
			if(nm_coa !== undefined) {
				$("#nm_coa_"+id).val(nm_coa);
			}
		}

		function add_detail() {
			var nama = $("#nama").val();
			var departement = $("#departement").val();
			var date1 = $("#date1").val();
			var date2 = $("#date2").val();
			var no_doc = $("#no_doc").val();
			var existing_ids = [];
			// Collect IDs of transport details already in the table
			$("input[name='id_transport[]']").each(function() {
				existing_ids.push($(this).val());
			});
			$.ajax({
				url: siteurl + 'expense/get_list_req_transport/' + nama + '/' + departement + '/' + date1 + '/' + date2,
				cache: false,
				type: "POST",
				dataType: "json",
				data: {
					existing_ids: existing_ids,
					no_doc: no_doc
				},
				success: function(data) {
					if (data.length == 0) {
						swal({
							title: "Info",
							text: "Tidak ada data transport baru yang tersedia untuk periode ini.",
							type: "info",
							timer: 2000,
							showConfirmButton: false
						});
						return;
					}
					var i;
					for (i = 0; i < data.length; i++) {
						var Rows = "<tr id='tr1_" + nomor + "' class='delAll kasbonrow'>";
						Rows += "<td><input type='hidden' name='id_transport[]' id='id_transport_" + nomor + "' value='" + data[i].id + "'>";
						Rows += data[i].no_doc + "</td>";
						Rows += "<td>" + data[i].tgl_doc + "</td>";
						Rows += "<td><input type='text' class='form-control input-sm detail-edit' name='nopol[]' value='" + (data[i].nopol ? data[i].nopol : '') + "' id='nopol_" + nomor + "' /></td>";
						var coa_options = '<option value="" data-name="">- Pilih COA -</option>';
						<?php
						if (!empty($list_coa)) {
							foreach ($list_coa as $item_coa) {
								echo "coa_options += '<option value=\"".$item_coa->no_coa."\" data-name=\"".$item_coa->nm_coa."\">".$item_coa->no_coa." - ".$item_coa->nm_coa."</option>';\n";
							}
						}
						?>
						Rows += "<td><select class='form-control select2 input-sm' name='coa[]' id='coa_" + nomor + "' onchange='set_nm_coa(" + nomor + ")' style='width:100%; min-width:200px;'>";
						Rows += coa_options;
						Rows += "</select><input type='hidden' name='nm_coa[]' id='nm_coa_" + nomor + "' value='" + data[i].nm_coa + "'></td>";
						Rows += "<td><input type='text' class='form-control input-sm detail-edit' name='keperluan[]' value='" + data[i].keperluan + "' id='keperluan_" + nomor + "' /></td>";
						Rows += "<td><input type='text' class='form-control input-sm detail-edit' name='rute[]' value='" + data[i].rute + "' id='rute_" + nomor + "' /></td>";
						Rows += "<td>";
						Rows += "<input type='text' class='form-control fben input-sm detail-edit' name='bensin[]' value='" + data[i].bensin + "' id='bensin_" + nomor + "' onblur='cektotal()' />";
						Rows += "</td>";
						Rows += "<td>";
						Rows += "<input type='text' class='form-control ftol input-sm detail-edit' name='tol[]' value='" + data[i].tol + "' id='tol_" + nomor + "' onblur='cektotal()' />";
						Rows += "</td>";
						Rows += "<td>";
						Rows += "<input type='text' class='form-control fpark input-sm detail-edit' name='parkir[]' value='" + data[i].parkir + "' id='parkir_" + nomor + "' onblur='cektotal()' />";
						Rows += "</td>";
						Rows += "<td>";
						Rows += "<input type='text' class='form-control flainnya input-sm detail-edit' name='lainnya[]' value='" + data[i].lainnya + "' id='lainnya_" + nomor + "' onblur='cektotal()' />";
						Rows += "</td>";
						Rows += "<td>";
						Rows += "<input type='text' class='form-control divide input-sm detail-edit fkm_awal' name='km_awal[]' value='" + data[i].km_awal + "' id='km_awal_" + nomor + "' onblur='cek_km(" + nomor + ")' />";
						Rows += "</td>";
						Rows += "<td>";
						Rows += "<input type='text' class='form-control divide input-sm detail-edit fkm_akhir' name='km_akhir[]' value='" + data[i].km_akhir + "' id='km_akhir_" + nomor + "' onblur='cek_km(" + nomor + ")' />";
						Rows += "</td>";
						Rows += "<td>";
						Rows += "<input type='text' class='form-control divide fkm input-sm' name='total_km[]' value='" + (data[i].km_akhir - data[i].km_awal) + "' id='total_km_" + nomor + "' tabindex='-1' readonly />";
						Rows += "</td>";
						Rows += "<td>";
						Rows += "<span class='pull-right'>";
						if (data[i].doc_file != '' && data[i].doc_file != null) {
							Rows += "<a href='<?= base_url('assets/expense/') ?>" + data[i].doc_file + "' target='_blank'><i class='fa fa-download'></i></a>";
						}
						Rows += "</span></td>";
						Rows += "<td class='stsview'><a href='javascript:void(0)' class='btn btn-danger btn-xs' onclick='remove_detail(" + nomor + ", " + data[i].id + ")' title='Hapus detail'><i class='fa fa-trash'></i></a></td>";
						Rows += "</tr>";
						$('#detail_body').append(Rows);
						$("#coa_" + nomor).val(data[i].no_coa);
						setTimeout(function(){
							$("#coa_" + (nomor - 1)).select2();
						}, 100);
						nomor++;
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

		function remove_detail(row_id, transport_id) {
			swal({
					title: "Anda Yakin?",
					text: "Detail ini akan dihapus dari pengajuan.",
					type: "warning",
					showCancelButton: true,
					confirmButtonText: "Ya, hapus!",
					cancelButtonText: "Batal",
					closeOnConfirm: true,
					closeOnCancel: true
				},
				function(isConfirm) {
					if (isConfirm) {
						$('#tr1_' + row_id).remove();
						// Track removed ID so backend can unlink it
						removed_ids.push(transport_id);
						cektotal();
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
										swal({
											title: "Sukses!",
											text: "Data Berhasil Di Tolak",
											type: "success",
											timer: 1500,
											showConfirmButton: false
										});
										window.location = siteurl + 'expense/transport_req<?= $mod ?>';
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