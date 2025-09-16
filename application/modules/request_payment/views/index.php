<?php
$ENABLE_ADD     = has_permission('Request_Payment.Add');
$ENABLE_MANAGE  = has_permission('Request_Payment.Manage');
$ENABLE_DELETE  = has_permission('Request_Payment.Delete');
$ENABLE_VIEW    = has_permission('Request_Payment.View');
?>

<style>
	.table-container {
		max-height: 500px;
		/* Example height for the table container (adjust as needed) */
		overflow-y: auto;
		/* Enable vertical scrolling */
	}

	/* Style for the table */
	.table-container table {
		width: 100%;
		border-collapse: collapse !important;
	}

	/* Style for the table header */

	/* Style for table cells */
	.table-container th,
	.table-container td {
		padding: 8px;
		border: 1px solid #ddd;
		text-align: left;
	}

	.sticky-header th {
		position: sticky !important;
		top: 0 !important;
		/* Stick to the top of the container */
		z-index: 1;
		/* Ensure it appears above tbody content */
		background-color: #3c8dbc;
		/* Header background color */
		color: white;
		font-weight: bold;
	}
</style>
<!-- <script src="//cdn.rawgit.com/rainabba/jquery-table2excel/1.1.0/dist/jquery.table2excel.min.js"></script> -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css" integrity="sha512-yVvxUQV0QESBt1SyZbNJMAwyKvFTLMyXSyBHDO4BG5t7k/Lw34tyqlSDlKIrIENIzCl+RVUNjmCPG+V/GMesRw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.min.css">
<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>


<form action="<?= $this->uri->uri_string() ?>" id="frm_data" name="frm_data" class="form-horizontal" enctype="multipart/form-data">
	<div class="box">
		<div class="box-header text-right">
			<a href="<?= base_url('request_payment/download_excel_request_payment') ?>" class="btn btn-sm btn-success"><i class="fa fa-download"></i> Excel</a>
			<button type="button" class="btn btn-sm btn-danger" onclick="reset_data();"><i class="fa fa-refresh"></i> Reset</button>
		</div>
		<div class="box-body">

			<input type="hidden" name="" class="actived_tab" value="transport">
			<!-- <ul class="nav nav-tabs" role="tablist">
				<li role="presentation" class="transport_tab tab_pin active"><a href="javascript:void();" onclick="change_tab('transport')">Transportasi</a></li>
				<li role="presentation" class="kasbon_tab tab_pin"><a href="javascript:void();" onclick="change_tab('kasbon')">Kasbon</a></li>
				<li role="presentation" class="expense_tab tab_pin"><a href="javascript:void();" onclick="change_tab('expense')">Expense</a></li>
				<li role="presentation" class="periodik_tab tab_pin"><a href="javascript:void();" onclick="change_tab('periodik')">Periodik</a></li>
				<li role="presentation" class="pembayaran_po_tab tab_pin"><a href="javascript:void();" onclick="change_tab('pembayaran_po')">Pembayaran PO</a></li>
				<li role="presentation" class="pembayaran_direct_payment tab_pin"><a href="javascript:void();" onclick="change_tab('direct_payment')">Direct Payment</a></li>
			</ul> -->
			<div class=" col-md-12" style="margin-top: 10px;">
				<table id="table_req_payment" class="table table-bordered">
					<thead class="sticky-header">
						<tr>
							<th class="text-center">No.</th>
							<th class="text-center">No. Dokumen</th>
							<th class="text-center">Request By</th>
							<th class="text-center">Tanggal</th>
							<th class="text-center">Keperluan</th>
							<th class="text-center">Kategori</th>
							<th class="text-center">Nilai Pengajuan</th>
							<th class="text-center">Tanggal Pembayaran</th>
							<th class="text-center">Action</th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
				<!-- <div class="pull-left"> -->
				<div class="col-md-6">
					<div class="form-group">
						<label for="">Reject Reason</label>
						<textarea name="reject_reason" id="reject_reason" class="form-control form-control-sm"></textarea>
					</div>
				</div>
				<!-- </div> -->
				<div class="pull-right">
					<!-- <button type="button" id="btnxls" class="btn btn-default">Export Excel</button>  -->
					<button type="button" class="btn btn-sm btn-danger" onclick="reject_req_payment()"><i class="fa fa-close"></i> Reject</button>
					<button type="submit" name="save" class="btn btn-success btn-sm" id="submit"><i class="fa fa-save">&nbsp;</i>Update</button>
				</div>
			</div>
		</div>
		<!-- /.box-body -->
	</div>
</form>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js" integrity="sha512-rMGGF4wg1R73ehtnxXBt5mbUfN9JUJwbk21KMlnLZDJh7BkPmeovBuddZCENJddHYYMkCh9hPFnPmS9sspki8g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script type="text/javascript">
	DataTables();
	load_all_party();

	// change_tab('transport');

	function load_all_party() {
		$(".divide").autoNumeric('init');
		$(".select2").select2({
			width: '100%'
		});
		$('.vendor').chosen();
		$('.tipe').chosen();

		$(".tanggal").datepicker({
			todayHighlight: true,
			format: "yyyy-mm-dd",
			showInputs: true,
			autoclose: true
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


	function change_tab(tab) {
		$('.tab_pin').removeClass('active');

		$('.' + tab + '_tab').addClass('active');
		$('.actived_tab').val(tab);

		$.ajax({
			type: 'POST',
			url: siteurl + active_controller + 'change_tab',
			data: {
				'tab': tab
			},
			cache: false,
			success: function(result) {
				$('.list_req_payment').html(result);
				load_all_party();
			},
			error: function() {
				swal({
					title: 'Error !',
					text: 'Please try again later !',
					type: 'error'
				});
			}
		});
	}

	function hitung_net_payment(no) {
		var nilai_pengajuan = $('.nilai_pengajuan_' + no).val();
		if (nilai_pengajuan !== '') {
			nilai_pengajuan = nilai_pengajuan.split(',').join('');
			nilai_pengajuan = parseFloat(nilai_pengajuan);
		}

		var admin_charge = $('.admin_charge_' + no).val();
		if (admin_charge !== '') {
			admin_charge = admin_charge.split(',').join('');
			admin_charge = parseFloat(admin_charge);
		}

		var nilai_pph = $('.nilai_pph_' + no).val();
		if (nilai_pph !== '') {
			nilai_pph = nilai_pph.split(',').join('');
			nilai_pph = parseFloat(nilai_pph);
		}

		var net_payment = (nilai_pengajuan + admin_charge - nilai_pph);

		$('.net_payment_' + no).val(net_payment.toLocaleString());
	}

	function reset_data() {
		$.ajax({
			type: 'post',
			url: siteurl + active_controller + 'reset_choosed_req_payment',
			cache: false,
			success: function(result) {
				DataTables();
			}
		});
	}

	function reject_req_payment() {
		var reject_reason = $('#reject_reason').val();

		if (reject_reason == '') {
			swal({
				type: 'warning',
				title: 'Warning !',
				text: 'Reject Reason masih kosong !',
				showCancelButton: false,
				timer: 3000
			});

			return false;
		}

		swal({
			type: 'warning',
			title: 'Are you sure ?',
			text: 'Selected data will be rejected !',
			showCancelButton: true
		}, function(next) {
			if (next) {
				$.ajax({
					type: 'post',
					url: siteurl + active_controller + 'reject_req_payment',
					cache: false,
					data: {
						'reject_reason': reject_reason
					},
					dataType: 'json',
					success: function(result) {
						if (result.status == '1') {
							swal({
								type: 'success',
								title: 'Success !',
								text: result.msg,
								timer: 3000,
								showConfirmButton: false
							}, function(lanjut) {
								swal.close();
								DataTables();
							});
						} else {
							swal({
								type: 'warning',
								title: 'Failed !',
								text: result.msg,
								timer: 3000,
								showConfirmButton: false
							});
						}
					},
					error: function(result) {
						swal({
							type: 'error',
							title: 'Error !',
							text: 'Please try again later !',
							timer: 3000,
							showConfirmButton: false
						});
					}
				});
			} else {
				swal({
					type: 'success',
					title: 'Success !',
					text: 'Selected data did not reject !',
					timer: 3000,
					showConfirmButton: false
				}, function(next) {
					swal.close();
					DataTables();
				});
			}
		});
	}

	$(document).on('click', '.pilih_data', function() {
		var val_pilih = $(this).val();
		var kategori = $(this).data('kategori');

		var isChecked = $('input[value="' + val_pilih + '"]').is(':checked');

		var wdo = 1;
		if (!isChecked) {
			wdo = 0;
		}

		$.ajax({
			type: 'post',
			url: siteurl + active_controller + 'added_pilih_data',
			data: {
				'id': val_pilih,
				'kategori': kategori,
				'wdo': wdo
			},
			cache: false,
			success: function(result) {

			},
			error: function(result) {
				swal({
					type: 'error',
					title: 'Error !',
					text: 'Please try again later !'
				});
			}
		})
	})

	//Save
	$('#frm_data').on('submit', function(e) {
		e.preventDefault();

		swal({
			type: 'warning',
			title: 'Are you sure ?',
			text: 'The data you choose will be processed !',
			showCancelButton: true
		}, function(next) {
			if (next) {
				var formdata = $('#frm_data').serialize();
				$.ajax({
					type: 'post',
					url: siteurl + active_controller + 'save_request_payment',
					data: formdata,
					dataType: 'json',
					cache: false,
					success: function(result) {
						if (result.status == '1') {
							swal({
								type: 'success',
								title: 'Success !',
								text: result.msg
							}, function(lanjut) {
								if (lanjut) {
									DataTables();
								}
							});
						} else {
							swal({
								type: 'warning',
								title: 'Failed !',
								text: result.msg
							}, function(lanjut) {
								if (lanjut) {
									DataTables();
								}
							});
						}
					},
					error: function(result) {
						swal({
							type: 'error',
							title: 'Error !',
							text: 'Please try again later !'
						});
					}
				})
			}
		});

		// var errors = "";

		// var checked_item = $('input[name="pilih"]:checked').length;
		// if (errors == "" && checked_item > 0) {
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
		// 								text: "Data Berhasil Di Update",
		// 								type: "success",
		// 								timer: 1500,
		// 								showConfirmButton: false
		// 							});
		// 							window.location.href = window.location.href;
		// 						} else {
		// 							swal({
		// 								title: "Gagal!",
		// 								text: "Data Gagal Di Update",
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
		// } else {
		// 	if (checked_item < 1) {
		// 		errors = 'Please check at least 1 data before you update it !';
		// 	}
		// 	swal({
		// 		title: 'Error !',
		// 		text: errors,
		// 		type: 'error'
		// 	});
		// 	return false;
		// }
	});

	function DataTables() {
		var DataTables = $('#table_req_payment').dataTable({
			serverSide: true,
			process: true,
			stateSave: true,
			paging: true,
			destroy: true,
			ajax: {
				type: 'post',
				url: siteurl + active_controller + 'get_data_req_payment',
				dataType: 'json'
			},
			columns: [{
					data: 'no'
				},
				{
					data: 'no_dokumen',
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
					data: 'kategori'
				},
				{
					data: 'nilai_pengajuan'
				},
				{
					data: 'tanggal_pembayaran'
				},
				{
					data: 'action'
				}
			]
		});
	}
</script>