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
			<div class="col-md-3">
				<div class="form-group">
					<input type="date" name="tgl_from" id="" class="form-control form-control-sm tgl_from">
				</div>
			</div>
			<div class="col-md-1 text-center">
				<p>S/D</p>
			</div>
			<div class="col-md-3">
				<div class="form-group">
					<input type="date" name="tgl_to" id="" class="form-control form-control-sm tgl_to">
				</div>
			</div>
			<div class="col-md-3">
				<div class="form-group">
					<select name="bank" id="" class="form-control form-control-sm bank select2">
						<option value="">- Pilih Bank -</option>
						<?php
						foreach ($data_bank as $item) {
							echo '<option value="' . $item->no_perkiraan . ' - ' . $item->nama . '">' . $item->no_perkiraan . ' - ' . $item->nama . '</option>';
						}
						?>
					</select>
				</div>
			</div>
			<div class="col-md-2">
				<button type="button" class="btn btn-sm btn-primary search_data"><i class="fa fa-search"></i> Search</button>
				<button type="button" class="btn btn-sm btn-danger clearing"><i class="fa fa-cogs"></i> Reset</button>
			</div>
		</div>
		<!-- </div> -->
		<!-- </div> -->
		<div class="table-responsive col-md-12 table_container">
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
				<tbody></tbody>
			</table>
		</div>
	</div>
	<!-- /.box-body -->
</div>

<script src="https://cdn.datatables.net/2.0.7/js/dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script type="text/javascript">
	
	
	$(document).ready(function() {
		$(".divide").autoNumeric('init');
	
		$('.select2').select2({
			width: '100%'
		});

		DataTablee();
	})

	function DataTablee() {
		var tgl_from = $('.tgl_from').val()
		var tgl_to = $('.tgl_to').val()
		var bank = $('.bank').val()

		var DataTablee = $('#mytabledata').DataTable().destroy();

		var DataTablee = $('#mytabledata').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				type: 'post',
				url: siteurl + active_controller + 'get_payment_list',
				data: function(d) {
					d.tgl_from = tgl_from
					d.tgl_to = tgl_to
					d.bank = bank
				}
			},
			columns: [
				{
					data: 'no'
				},
				{
					data: 'no_dokumen'
				},
				{
					data: 'request_by'
				},
				{
					data: 'tanggal'
				},
				{
					data: 'keperluan'
				},
				{
					data: 'tipe'
				},
				{
					data: 'nilai_pengajuan'
				},
				{
					data: 'diajukan_oleh'
				},
				{
					data: 'tanggal_pengajuan'
				},
				{
					data: 'dibayar_oleh'
				},
				{
					data: 'tanggal_pembayaran'
				},
				{
					data: 'status'
				}
			]
		});
	}

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
		DataTablee();
	});
	$(document).on('click', '.clearing', function() {
		var tgl_from = $('.tgl_from').val('')
		var tgl_to = $('.tgl_to').val('')
		var bank = $('.bank').val('')

		DataTablee();
	});

	$(document).on('click', '.excel_data', function() {
		var tgl_from = $('.tgl_from').val();
		var tgl_to = $('.tgl_to').val();
		var bank = $('.bank').val();

		window.open(siteurl + active_controller + 'excel_payment_list/' + tgl_from + '/' + tgl_to + '/' + bank, '_blank');
	});
</script>