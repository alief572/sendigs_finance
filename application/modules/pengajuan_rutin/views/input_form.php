<?= form_open($this->uri->uri_string(), array('id' => 'frm_data', 'name' => 'frm_data', 'role' => 'form', 'class' => 'form-horizontal', 'enctype' => 'multipart/form-data')); ?>
<input type="hidden" name="id" id="id" value="<?php echo (isset($data->id) ? $data->id : ''); ?>">
<div class="tab-content">
	<div class="tab-pane active">
		<div class="box box-primary">
			<div class="box-body">
				<div class="form-group ">
					<label for="divisi" class="col-sm-2 control-label">Departement<font size="4" color="red"><B>*</B></font></label>
					<div class="col-sm-2">
						<div class="input-group">
							<?php
							echo form_dropdown('departement', $datdept, set_value('departement', isset($departement) ? $departement : '0'), array('id' => 'departement', 'class' => 'form-control', 'required' => 'required'));
							?>
						</div>
					</div>

					<label for="divisi" class="col-sm-2 control-label">No Dokumen</label>
					<div class="col-sm-2">
						<div class="input-group">
							<input type="text" class="form-control" id="no_doc" name="no_doc" value="<?php echo (isset($data->no_doc) ? $data->no_doc : ''); ?>" placeholder="Auto" required readonly>
						</div>
					</div>

					<label for="divisi" class="col-sm-2 control-label">Tanggal<font size="4" color="red"><B>*</B></font></label>
					<div class="col-sm-2">
						<div class="input-group">
							<input type="text" class="form-control tanggal" id="tanggal_doc" name="tanggal_doc" value="<?php echo (isset($data->tanggal_doc) ? $data->tanggal_doc : date("Y-m-d")); ?>" placeholder="Tanggal" required>
						</div>
					</div>

				</div>
				<div class="table-responsive">
					<table class="table table-bordered table-striped">
						<thead>
							<tr>
								<th width="5">#</th>
								<th>Deskripsi</th>
								<th>Jadwal Pembayaran</th>
								<th>Budget</th>
								<th>Perkiraan Biaya</th>
								<th>Keterangan</th>
								<th>Metode Pembayaran</th>
								<th>Dokumen</th>
								<th>Bank / No Rek / Nama</th>
								<th class="hidden">
									<div class="pull-right"><button class="btn btn-success btn-xs" onclick="add_detail()" id="add-material" type="button"><i class="fa fa-plus"></i> Tambah</button></div>
								</th>
							</tr>
						</thead>
						<tbody id="detail_body">
							<?php $total = 0;
							$idd = 1;
							$gambar = '';
							if (!empty($data_detail)) {
								foreach ($data_detail as $record) { ?>
									<tr id='tr1_<?= $idd ?>' class='delAll'>
										<td><input type='hidden' name='details[]' id='details_id_<?= $idd ?>' value='<?= $idd ?>'>
											<input type="hidden" name="detail_id[]" id="raw_id_<?= $idd ?>" value="<?= $record->id; ?>"><?= $idd; ?>
											<input type="hidden" name="id_budget[]" id="id_budget_<?= $idd; ?>" value="<?= $record->id_budget ?>" class='budget'>
											<input type="hidden" name="coa[]" id="coa_<?= $idd; ?>" value="<?= $record->coa ?>">
										</td>
										<td><input type="text" class="form-control" name="nama[]" id="nama_<?= $idd; ?>" value="<?= $record->nama; ?>"></td>
										<td>
											<input type="text" class="form-control tanggal" name="tanggal[]" id="tanggal_<?= $idd; ?>" value="<?= $record->tanggal; ?>" readonly style="background:#fff;cursor: pointer;">
										</td>
										<td><input type="text" class="form-control divide" name="budget[]" id="budget<?= $idd; ?>" value="<?= ($record->budget); ?>" readonly tabindex="-1"></td>
										<td><input type="text" class="form-control nilai divide" name="nilai[]" id="nilai_<?= $idd; ?>" value="<?= ($record->nilai); ?>"></td>
										<td><input type="text" class="form-control" name="keterangan[]" id="keterangan_<?= $idd; ?>" value="<?= $record->keterangan; ?>"></td>
										<td>
											<select name="metode_pembelian[]" id="metode_pembelian_<?= $idd ?>" class="form-control form-control-sm">
												<option value="">- Select Transfer/Kasbon -</option>
												<option value="1" <?= ($record->metode_pembelian == '1') ? 'selected' : null ?>>Transfer</option>
												<option value="2" <?= ($record->metode_pembelian == '2') ? 'selected' : null ?>>Kasbon</option>
											</select>
										</td>
										<td><input type="file" name="doc_file_<?= $idd ?>" id="doc_file<?= $idd ?>">
											<?= ($record->doc_file != '' ? '<a href="' . base_url('assets/bayar_rutin/' . $record->doc_file) . '" download target="_blank"><i class="fa fa-download"></i></a>' : '') ?></td>
										<td>
											<input type="text" class="form-control" name="bank_id[]" id="bank_id_<?= $idd; ?>" value="<?= $record->bank_id; ?>">
											<input type="text" class="form-control" name="accnumber[]" id="accnumber_<?= $idd; ?>" value="<?= $record->accnumber; ?>">
											<input type="text" class="form-control" name="accname[]" id="accname_<?= $idd; ?>" value="<?= $record->accname; ?>">
										</td>
										<td align='center' class="hidden"><button type='button' class='btn btn-danger btn-xs' data-toggle='tooltip' onClick='delDetail(<?= $idd ?>)' title='Hapus data'><i class='fa fa-close'></i> Hapus</button></td>
									</tr>
							<?php
									$total = ($total + $record->nilai);
									if ($record->doc_file != '') {
										if (strpos($record->doc_file, 'pdf', 0) > 1) {
											$gambar .= '<div class="col-md-12">
									<iframe src="' . base_url('assets/bayar_rutin/' . $record->doc_file) . '#toolbar=0&navpanes=0" title="PDF" style="width:600px; height:500px;" frameborder="0">
											 <a href="' . base_url('assets/bayar_rutin/' . $record->doc_file) . '">Download PDF</a>
									</iframe>
									<br />' . $record->no_doc . '</div>';
										} else {
											$gambar .= '<div class="col-md-4"><a href="' . base_url('assets/bayar_rutin/' . $record->doc_file) . '" target="_blank"><img src="' . base_url('assets/bayar_rutin/' . $record->doc_file) . '" class="img-responsive"></a><br />' . $record->no_doc . '</div>';
										}
									}
									$idd++;
								}
							} ?>
							<tr>
								<td colspan=4>TOTAL</td>
								<td><?= number_format($total) ?></td>
							</tr>
						</tbody>
					</table>
				</div>
				<div class="row">
					<?= $gambar ?>
					<?php
					if ($app == 'app') {
						echo '
								<div class="col-md-4">
									<div class="form-group">
										<label for="">Alasan Reject</label>
										<textarea name="" id="" cols="30" rows="10" class="form-control form-control-sm reject_reason"></textarea>
									</div>
								</div>
							';
					}
					?>
				</div>
			</div>
			<div class="box-footer">
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10">
						<?php
						$app_url = '';
						if (isset($data)) {
							if ($data->status == 0) {
								if ($app == 'app') {
									echo '<a href="#" class="btn btn-primary btn-sm stsview" id="approve" onclick="data_approve(' . (isset($data->id) ? $data->id : '') . ')"><i class="fa fa-check-square-o"></i> Approve</a>';
									echo '<a href="#" class="btn btn-danger btn-sm" style="margin-left: 0.2vh;" id="reject" onclick="data_reject(' . (isset($data->id) ? $data->id : '') . ')"><i class="fa fa-close"></i> Reject</a>';
									$app_url = '/app_list';
								}
							}
						}
						?>
						<button type="submit" name="save" class="btn btn-success btn-sm" id="submit"><i class="fa fa-save">&nbsp;</i>Simpan</button>
						<a class="btn btn-warning btn-sm" onclick="location.reload();return false;"><i class="fa fa-reply">&nbsp;</i>Batal</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="bankModal" tabindex="-1" role="dialog" aria-labelledby="bankModal">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Daftar</h4>
			</div>
			<div class="modal-body" id="bankModalBody"></div>
		</div>
	</div>
</div>
<?= form_close() ?>
<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>
<script type="text/javascript">
	var url_save = siteurl + 'pengajuan_rutin/save_data/';
	var url_approve = siteurl + 'pengajuan_rutin/approve/';
	var url_reject = siteurl + 'pengajuan_rutin/reject/';
	var nomor = <?= $idd ?>;
	<?php
	if (isset($type)) {
		if ($type == 'view') {
			echo "$('#frm_data :input').prop('disabled', true);";
			echo "$('.reject_reason').prop('disabled', false);";
		}
	} ?>
	$('.divide').divide();

	function list_bank(id) {
		$.ajax({
			url: siteurl + 'all/list_bank/' + id,
			type: 'POST',
			success: function(msg) {
				$("#bankModalBody").html(msg);
				$("#mylistbank").DataTable();
				$('#bankModal').modal('show');
			}
		});
	}

	function pilihini(bank_id, accnumber, accname, id) {
		$("#bank_id_" + id).val(bank_id);
		$("#accnumber_" + id).val(accnumber);
		$("#accname_" + id).val(accname);
		$('#bankModal').modal('hide');
	}

	$(document).on("keyup", ".nilai", function(){
		var no = $(this).data('no');
		var nilai_val = $(this).val();
		if(nilai_val == "" || nilai_val == null || nilai_val == undefined){
			nilai_val = 0;
		}else{
			nilai_val = nilai_val.split(",").join("");
			nilai_val = parseFloat(nilai_val);
		}

		if(nilai_val > 0){
			$("#metode_pembelian_" + no).prop("required", true);
		}else{
			$("#metode_pembelian_" + no).prop("required", false);
		}
	});

	$('#frm_data').on('submit', function(e) {
		e.preventDefault();

		var inputed_nilai = 0;
		$(".nilai").each(function() {
			var nilai_val = $(this).val();
			if (nilai_val == "" || nilai_val == null || nilai_val == undefined) {
				var nilai_val = 0;
			} else {
				var nilai_val = nilai_val.split(",").join("");
				nilai_val = parseFloat(nilai_val);
			}

			if (nilai_val > 0) {
				inputed_nilai += nilai_val
			}
		})
		if (inputed_nilai < 1) {
			swal({
				title: "Error !",
				text: "Maaf, setidaknya isi 1 nilai Perkiraan Biaya !",
				type: "error"
			})
		} else {
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
									cancel();
									window.location.reload();
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
		}
		// var errors = "";
		// if (errors == "") {
		// 	swal({
		// 			title: "Anda Yakin?",
		// 			text: "Data Akan Disimpan!",
		// 			type: "info",
		// 			showCancelButton: true,
		// 			confirmButtonText: "Ya, simpan!",
		// 			cancelButtonText: "Tidak!",
		// 			closeOnConfirm: false,
		// 			closeOnCancel: true
		// 		},
		// 		function(isConfirm) {
		// 			if (isConfirm) {
		// 				var formdata = new FormData($('#frm_data')[0]);
		// 				$.ajax({
		// 					url: url_save,
		// 					dataType: "json",
		// 					type: 'POST',
		// 					data: formdata,
		// 					processData: false,
		// 					contentType: false,
		// 					success: function(msg) {
		// 						if (msg['save'] == '1') {
		// 							swal({
		// 								title: "Sukses!",
		// 								text: "Data Berhasil Di Simpan",
		// 								type: "success",
		// 								timer: 1500,
		// 								showConfirmButton: false
		// 							});
		// 							cancel();
		// 							window.location.reload();
		// 						} else {
		// 							swal({
		// 								title: "Gagal!",
		// 								text: "Data Gagal Di Simpan",
		// 								type: "error",
		// 								timer: 1500,
		// 								showConfirmButton: false
		// 							});
		// 						};
		// 						console.log(msg);
		// 					},
		// 					error: function(msg) {
		// 						swal({
		// 							title: "Gagal!",
		// 							text: "Ajax Data Gagal Di Proses",
		// 							type: "error",
		// 							timer: 1500,
		// 							showConfirmButton: false
		// 						});
		// 						console.log(msg);
		// 					}
		// 				});
		// 			}
		// 		});
		// 	//			data_save();
		// } else {
		// 	swal(errors);
		// 	return false;
		// }
	});

	$(function() {
		$(".select2").select2();
		$(".tanggal").datepicker({
			todayHighlight: true,
			format: "yyyy-mm-dd",
			showInputs: true,
			autoclose: true
		});
	});

	function add_detail() {
		var idbudget = [];
		var departement = $("#departement").val();
		var tanggal_doc = $("#tanggal_doc").val();
		$('.budget').each(function() {
			idbudget.push($(this).val());
		});
		$.ajax({
			url: siteurl + "pengajuan_rutin/get_data",
			dataType: "json",
			type: 'POST',
			data: {
				allbudget: idbudget,
				dept: departement,
				tanggal: tanggal_doc
			},
			success: function(msg) {
				if (msg['save'] == '1') {
					$.each(msg['data'], function(index, element) {

						var selected1 = '';
						var selected2 = '';
						if (element.metode_pembelian == '1') {
							selected1 = 'selected';
						}
						if (element.metode_pembelian == '2') {
							selected2 = 'selected';
						}

						var Rows = "<tr id='tr1_" + nomor + "' class='delAll'>";
						Rows += "<td>";
						Rows += "<input type='hidden' name='detail_id[]' id='details_id_" + nomor + "' value=''>";
						Rows += "<input type='hidden' name='details[]' id='raw_id_" + nomor + "' value='" + nomor + "'>";
						Rows += "<input type='hidden' name='id_budget[]' id='id_budget_" + nomor + "' value='" + element.id + "' class='budget'>";
						Rows += "<input type='hidden' name='coa[]' id='coa_" + nomor + "' value='" + element.coa + "'></td>";
						Rows += "</td>";
						Rows += "<td>";
						Rows += "<input type='text' class='form-control' name='nama[]' id='nama_" + nomor + "' value='" + element.nama + "' />";
						Rows += "</td>";
						var jadwal = '';
						if (element.tipe == 'tahun') jadwal = msg['tahun'] + '-' + element.tanggal;
						if (element.tipe == 'bulan') jadwal = msg['tahun'] + '-' + msg['bulan'] + '-' + element.tanggal;
						Rows += "<td>";
						Rows += "<input type='text' class='form-control' name='tanggal[]' id='tanggal_" + nomor + "' value='" + jadwal + "' />";
						Rows += "</td>";
						Rows += "<td>";
						Rows += "<input type='text' class='form-control divide' name='budget[]' value='" + element.nilai + "' id='budget_" + nomor + "' readonly tabindex='-1'/>";
						Rows += "</td>";
						Rows += "<td>";
						Rows += "<input type='text' class='form-control nilai divide' name='nilai[]' value='0' data-no='"+nomor+"' id='nilai_" + nomor + "' />";
						Rows += "</td>";
						Rows += "<td>";
						Rows += "<input type='text' class='form-control' name='keterangan[]' id='keterangan_" + nomor + "' value='' />";
						Rows += "</td>";
						Rows += "<td>";
						Rows += '<select name="metode_pembelian[]" id="metode_pembelian_' + nomor + '" class="form-control form-control-sm">';
						Rows += '<option value="">- Select Transfer/Kasbon -</option>';
						Rows += '<option value="1" ' + selected1 + '>Transfer</option>';
						Rows += '<option value="2" ' + selected2 + '>Kasbon</option>';
						Rows += '</select>';
						Rows += "</td>";
						Rows += "<td>";
						Rows += "<input type='file' name='doc_file_" + nomor + "' id='doc_file" + nomor + "'>";
						Rows += "</td>";
						Rows += "<td>";

						Rows += "<input type='text' class='form-control' name='bank_id[]' id='bank_id_" + nomor + "' value='' placeholder='- Nama Bank -' />";
						Rows += "<input type='text' class='form-control' name='accnumber[]' id='accnumber_" + nomor + "' value='' placeholder='- No Rekening -' />";
						Rows += "<input type='text' class='form-control' name='accname[]' id='accname_" + nomor + "' value='' placeholder='- Nama -' />";
						Rows += "</td>";
						Rows += "<td align='center' class='hidden'>";
						Rows += "<button type='button' class='btn btn-danger btn-xs' data-toggle='tooltip' onClick='delDetail(" + nomor + ")' title='Hapus data'><i class='fa fa-close'></i> Hapus</button>";
						Rows += "</td>";
						Rows += "</tr>";
						nomor++;
						$('#detail_body').append(Rows);
					});
					$(".divide").divide();


				} else {
					swal({
						title: "Gagal!",
						text: "Data gagal diambil",
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

	function delDetail(row) {
		$('#tr1_' + row).remove();
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
								window.location.reload();
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
				title: "Anda Yakin?",
				text: "Data Akan di Reject!",
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
						url: url_reject + id,
						dataType: "json",
						type: 'POST',
						data: {
							'reject_reason': $(".reject_reason").val()
						},
						success: function(msg) {
							if (msg['save'] == '1') {
								swal({
									title: "Sukses!",
									text: "Data Berhasil Di Reject",
									type: "success",
									timer: 1500,
									showConfirmButton: false
								});
								window.location.reload();
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
	}
	<?php
	if ($type == 'add') echo "add_detail();";
	?>
</script>