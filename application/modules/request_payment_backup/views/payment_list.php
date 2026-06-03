<?php
$ENABLE_ADD     = has_permission('Payment_List.Add');
$ENABLE_MANAGE  = has_permission('Payment_List.Manage');
$ENABLE_DELETE  = has_permission('Payment_List.Delete');
$ENABLE_VIEW    = has_permission('Payment_List.View');
?>
<!-- <script src="//cdn.rawgit.com/rainabba/jquery-table2excel/1.1.0/dist/jquery.table2excel.min.js"></script> -->

<link rel="stylesheet" href="https://cdn.datatables.net/2.0.7/css/dataTables.dataTables.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>

<div class="box">
	<div class="box-body">
		<!-- <div class="col-md-6"> -->
		<!-- <div class="form-inline"> -->
		<div class="row">

			<div class="col-md-2">
				<!-- <button type="button" class="btn btn-sm btn-primary search_data"><i class="fa fa-search"></i> Search</button> -->
				<button type="button" class="btn btn-sm btn-success excel_data"><i class="fa fa-download"></i> Excel</button>
			</div>
		</div>
		<!-- </div> -->
		<!-- </div> -->
		<div class="col-md-12 table_container">
			<table id="mytabledata" class="table table-bordered">
				<thead>
					<tr>
						<th>#</th>
						<th>No Dokumen</th>
						<th>Request By</th>
						<th>Tanggal</th>
						<th>Keperluan</th>
						<th>Tipe</th>
						<th>Nilai Pengajuan</th>
						<th>Diajukan Oleh</th>
						<th>Tanggal Pengajuan</th>
						<th>Dibayar Oleh</th>
						<th>Tanggal Pembayaran</th>
						<th>Status</th>
					</tr>
				</thead>
				<tbody>
					<?php
					if (!empty($data)) {
						$numb = 0;
						foreach ($data as $record) {

							$nmuser = $record->nama;
							$no_doc = $record->no_doc;
							if ($record->tipe == 'kasbon') {
								$get_kasbon = $this->db->get_where('tr_kasbon', array('no_doc' => $record->no_doc))->row();

								if ($get_kasbon->no_kasbon_consultant !== null) {
									$no_doc = $get_kasbon->no_kasbon_consultant;
								}

								$check_detail = $this->db->get_where('tr_pr_detail_kasbon', ['id_kasbon' => $record->no_doc])->result();
								if (count($check_detail)) {
									if ($get_kasbon->tipe_pr == 'pr departemen') {
										$this->db->select('b.nm_lengkap');
										$this->db->from('rutin_non_planning_header a');
										$this->db->join('users b', 'b.id_user = a.created_by');
										$this->db->where('a.no_pr', $get_kasbon->id_pr);
										$get_single_detail = $this->db->get()->row();

										$nmuser = $get_single_detail->nm_lengkap;
									}

									if ($get_kasbon->tipe_pr == 'pr stok') {
										$this->db->select('b.nm_lengkap');
										$this->db->from('material_planning_base_on_produksi a');
										$this->db->join('users b', 'b.id_user = a.created_by');
										$this->db->where('a.no_pr', $get_kasbon->id_pr);
										$get_single_detail = $this->db->get()->row();

										$nmuser = $get_single_detail->nm_lengkap;
									}

									if ($get_kasbon->tipe_pr == 'pr asset') {
										$this->db->select('b.nm_lengkap');
										$this->db->from('tran_pr_header a');
										$this->db->join('users b', 'b.id_user = a.created_by');
										$this->db->where('a.no_pr', $get_kasbon->id_pr);
										$get_single_detail = $this->db->get()->row();

										$nmuser = $get_single_detail->nm_lengkap;
									}
								}
							}

							$tgl_pengajuan = (isset($list_tgl_pengajuan_pembayaran[$record->no_doc])) ? $list_tgl_pengajuan_pembayaran[$record->no_doc]['tgl_pengajuan'] : '';

							$diajukan_oleh = (isset($list_tgl_pengajuan_pembayaran[$record->no_doc])) ? $list_tgl_pengajuan_pembayaran[$record->no_doc]['diajukan_oleh'] : '';

							$this->db->select('c.nm_lengkap, a.created_on');
							$this->db->from('tr_payment_paid a');
							$this->db->join('payment_approve b', 'b.id_payment = a.id', 'left');
							$this->db->join('users c', 'c.id_user = a.created_by', 'left');
							$this->db->where('b.no_doc', $record->no_doc);
							$get_payment_details = $this->db->get()->row();

							$dibayar_oleh = (!empty($get_payment_details)) ? $get_payment_details->nm_lengkap : '';
							$tgl_pembayaran = (!empty($get_payment_details)) ? $get_payment_details->created_on : '';

							$numb++; ?>
							<tr>
								<td><?= $numb; ?></td>
								<td><?= $no_doc ?></td>
								<td><?= $nmuser ?></td>
								<td><?= $record->tgl_doc ?></td>
								<td><?= $record->keperluan ?></td>
								<td><?= $record->tipe ?></td>
								<td><?= (($record->tipe == 'expense' and $record->id_kasbon != null and $record->kurang_bayar > 0) ? number_format($record->kurang_bayar) : number_format($record->jumlah)) ?></td>
								<td class="text-center"><?= $diajukan_oleh ?></td>
								<td class="text-center"><?= $tgl_pengajuan ?></td>
								<td class="text-center"><?= $dibayar_oleh ?></td>
								<td class="text-center"><?= $tgl_pembayaran ?></td>
								<td>
									<?php
									$get_payment = $this->db->get_where('payment_approve', ['no_doc' => $record->no_doc, 'tgl_bayar <>' => null])->result();

									if (!empty($get_payment)) {
										echo '<div class="badge bg-green text-light">Paid</div>';
									} else {
										echo '<div class="badge bg-blue">Open</div>';
									}
									?>
								</td>
							</tr>
					<?php
						}
					}  ?>

				</tbody>
			</table>
		</div>
	</div>
	<!-- /.box-body -->
</div>

<script src="https://cdn.datatables.net/2.0.7/js/dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script type="text/javascript">
	$(".divide").autoNumeric('init');
	$("#mytabledata").DataTable();

	$('.select2').select2({
		width: '100%'
	});

	function cektotal() {
		var total_req = 0;
		$('.dtlloop').each(function() {
			if (this.checked) {
				var ids = $(this).val();
				total_req += Number($("#jumlah_" + ids).val());
			}
		});
		$("#total_req").autoNumeric('set', total_req);
	}
	var url_save = siteurl + 'request_payment/save_request/';
	$(function() {
		$(".tanggal").datepicker({
			todayHighlight: true,
			format: "yyyy-mm-dd",
			showInputs: true,
			autoclose: true
		});
	});
	//Save
	$('#frm_data').on('submit', function(e) {
		e.preventDefault();
		var errors = "";
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

	$(document).on('click', '.search_data', function() {
		var tgl_from = $('.tgl_from').val();
		var tgl_to = $('.tgl_to').val();
		var bank = $('.bank').val();

		$.ajax({
			type: "POST",
			url: siteurl + active_controller + 'search_payment_list',
			data: {
				'tgl_from': tgl_from,
				'tgl_to': tgl_to,
				'bank': bank
			},
			cache: false,
			beforeSend: function(result) {
				$('.search_data').html('<i class="fa fa-spin fa-spinner"></i>');
			},
			success: function(result) {
				$('.table_container').html(result);
				$('.search_data').html('<i class="fa fa-search"></i> Search');
			},
			error: function(result) {
				swal({
					title: 'Error !',
					text: 'Please try again later !',
					type: 'error'
				});
				$('.search_data').html('<i class="fa fa-search"></i> Search');
			}
		});
	});

	$(document).on('click', '.excel_data', function() {
		var tgl_from = $('.tgl_from').val();
		var tgl_to = $('.tgl_to').val();
		var bank = $('.bank').val();

		window.open(siteurl + active_controller + 'excel_payment_list/' + tgl_from + '/' + tgl_to + '/' + bank, '_blank');
	});
</script>