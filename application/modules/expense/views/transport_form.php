<?= form_open($this->uri->uri_string(), array('id' => 'frm_data', 'name' => 'frm_data', 'role' => 'form', 'class' => 'form-horizontal', 'enctype' => 'multipart/form-data')); ?>
<input type="hidden" id="id" name="id" value="<?php echo set_value('id', isset($data->id) ? $data->id : ''); ?>">
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
				<div class="form-group hidden">
					<?php
					$dept = '';

					$datauser = $this->db->get_where('users', ['id_user' => $this->auth->user_id()])->row();
					?>
					<input type="hidden" id="departement" name="departement" value="<?php echo set_value('departement', isset($data->departement) ? $data->departement : $datauser->department_id); ?>">
					<input type="hidden" id="nama" name="nama" value="<?php echo set_value('nama', isset($data->nama) ? $data->nama : $this->auth->user_name()); ?>">
				</div>
				<div style="border: 1px solid #008d4c;border-radius: 4px;padding: 9px 14px;">
					<strong>BERANGKAT :</strong>
					<div class="form-group ">
						<label class="col-sm-2 control-label">No. Polisi</label>
						<div class="col-sm-4">
							<input type="text" class="form-control" id="nopol" name="nopol" value="<?php echo (isset($data->nopol) ? $data->nopol : ''); ?>" placeholder="Nomor Polisi" maxlength="15">
						</div>
						<label class="col-sm-2 control-label">KM. Awal</label>
						<div class="col-sm-4">
							<input type="text" class="form-control divide" id="km_awal" name="km_awal" value="<?php echo (isset($data->km_awal) ? $data->km_awal : '0'); ?>" placeholder="KM Awal">
						</div>
					</div>
				</div>
				<br />
				<div style="border: 1px solid #f39c12;border-radius: 4px;padding: 9px 14px;"> <strong>PULANG :</strong>
					<div class="form-group ">
						<label class="col-sm-2 control-label">Keperluan</label>
						<div class="col-sm-4">
							<input type="text" class="form-control" id="keperluan" name="keperluan" value="<?php echo (isset($data->keperluan) ? $data->keperluan : ''); ?>" placeholder="keperluan">
						</div>
						<label class="col-sm-2 control-label">Rute</label>
						<div class="col-sm-4">
							<input type="text" class="form-control" id="rute" name="rute" value="<?php echo (isset($data->rute) ? $data->rute : ''); ?>" placeholder="Rute">
						</div>
					</div>
					<div class="form-group ">
						<label class="col-sm-2 control-label">Bensin</label>
						<div class="col-sm-4">
							<input type="text" class="form-control divide" id="bensin" name="bensin" value="<?php echo (isset($data->bensin) ? $data->bensin : '0'); ?>" placeholder="Bensin">
						</div>
						<label class="col-sm-2 control-label">Tol</label>
						<div class="col-sm-4">
							<input type="text" class="form-control divide" id="tol" name="tol" value="<?php echo (isset($data->tol) ? $data->tol : '0'); ?>" placeholder="Tol">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">Parkir</label>
						<div class="col-sm-4">
							<input type="text" class="form-control divide" id="parkir" name="parkir" value="<?php echo (isset($data->parkir) ? $data->parkir : '0'); ?>" placeholder="Parkir">
						</div>
						<label class="col-sm-2 control-label">Transport / Lain-Lain</label>
						<div class="col-sm-4">
							<input type="text" class="form-control divide" id="lainnya" name="lainnya" value="<?php echo (isset($data->lainnya) ? $data->lainnya : '0'); ?>" placeholder="Lain-Lain">
						</div>
					</div>
					<div class="form-group ">
						<label class="col-sm-2 control-label">KM. Akhir</label>
						<div class="col-sm-4">
							<input type="text" class="form-control divide" id="km_akhir" name="km_akhir" value="<?php echo (isset($data->km_akhir) ? $data->km_akhir : '0'); ?>" placeholder="KM Akhir">
						</div>
						<label class="col-sm-2 control-label">Dokumen</label>
						<div class="col-sm-4">
							<input type="hidden" name="filename" id="filename" value="<?= (isset($data->doc_file) ? $data->doc_file : ''); ?>">
							<input type="file" name="doc_file" id="doc_file">
							<span class="pull-right"><?php
														$gambar = '';
														if (isset($data->doc_file)) {
															echo ($data->doc_file != '' ? '<a href="' . base_url('assets/expense/' . $data->doc_file) . '" download target="_blank"><i class="fa fa-download"></i></a>' : '');
															if (strpos($data->doc_file, 'pdf', 0) > 1) {
																$gambar .= '<div class="col-md-12">
								<iframe src="' . base_url('assets/expense/' . $data->doc_file) . '#toolbar=0&navpanes=0" title="PDF" style="width:600px; height:500px;" frameborder="0">
										 <a href="' . base_url('assets/expense/' . $data->doc_file) . '">Download PDF</a>
								</iframe>
								<br />' . $data->no_doc . '</div>';
															} else {
																$gambar .= '<div class="col-md-12"><a href="' . base_url('assets/expense/' . $data->doc_file) . '" target="_blank"><img src="' . base_url('assets/expense/' . $data->doc_file) . '" class="img-responsive"></a><br />' . $data->no_doc . '</div>';
															}
														}
														?>
							</span>
						</div>
					</div>
					<div class="form-group ">
						<label class="col-sm-2 control-label">Keterangan</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" id="keterangan" name="keterangan" value="<?php echo (isset($data->keterangan) ? $data->keterangan : ''); ?>" placeholder="Keterangan">
						</div>
					</div>
				</div>
				<div class="box-footer">
					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-10">
							<?php
							if (isset($data)) {
								if ($data->status == 0) {
									//echo '<button type="button" name="Approve" class="btn btn-primary btn-sm stsview" id="approve" onclick="data_approve()"><i class="fa fa-save">&nbsp;</i>Update</button>';
								}
							}
							?>
							<button type="submit" name="save" class="btn btn-success btn-sm stsview" id="submit"><i class="fa fa-save">&nbsp;</i>Simpan</button>
							<a class="btn btn-warning btn-sm" onclick="window.location=siteurl+'expense/transport';return false;"><i class="fa fa-reply">&nbsp;</i>Batal</a>
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
		var url_save = siteurl + 'expense/transport_save/';
		var url_approve = siteurl + 'expense/transport_approve/';
		$('.divide').divide();
		$('#frm_data').on('submit', function(e) {
			e.preventDefault();
			var errors = "";
			if ($("#filename").val() == "") {
				if ($('#doc_file').get(0).files.length === 0) {
					errors = "Dokumen harus diupload";
				}
			}
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
										window.location = siteurl + 'expense/transport';
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
									window.location.reload();
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
		}
	</script>