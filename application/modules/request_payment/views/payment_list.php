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

<style>
    /* Table Styling */
    #mytabledata {
        width: 100% !important;
        border-collapse: separate;
        border-spacing: 0;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }
    #mytabledata thead th {
        background-color: #3c8dbc;
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 12px;
        padding: 12px 10px;
        vertical-align: middle;
        border-bottom: 2px solid #367fa9;
    }
    #mytabledata tbody td {
        padding: 10px 12px;
        vertical-align: middle;
        border-bottom: 1px solid #f0f0f0;
        font-size: 13px;
        color: #333;
        transition: background-color 0.2s ease;
    }
    #mytabledata tbody tr:hover td {
        background-color: #f4f6f9;
    }
    #mytabledata tbody tr:last-child td {
        border-bottom: none;
    }
    /* Button Styling */
    .excel_data {
        background: #00a65a;
        border: none;
        border-radius: 4px;
        padding: 8px 16px;
        font-weight: bold;
        transition: all 0.3s;
        box-shadow: 0 2px 4px rgba(0,166,90,0.3);
    }
    .excel_data:hover {
        background: #008d4c;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,166,90,0.4);
    }
    .table_container {
        margin-top: 15px;
    }
</style>

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
		<div class="col-md-12 table_container table-responsive">
			<table id="mytabledata" class="table table-striped table-hover">
				<thead>
					<tr>
						<th>#</th>
						<th>No Dokumen</th>
						<th>No Transaksi Payment</th>
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
	
    $(document).ready(function() {
        var table = $('#mytabledata').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": siteurl + active_controller + "server_side_payment_list",
                "type": "POST"
            },
            "columns": [
                { "data": 0 },
                { "data": 1 },
                { "data": 2 },
                { "data": 3 },
                { "data": 4 },
                { "data": 5 },
                { "data": 6 },
                { "data": 7 },
                { "data": 8 },
                { "data": 9 },
                { "data": 10 },
                { "data": 11 },
                { "data": 12 }
            ],
            "order": [[4, 'desc']], // Default order by Tanggal
            "columnDefs": [
                { "orderable": false, "targets": [0, 8, 9, 10, 11, 12] }
            ]
        });
    });

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
		// var tgl_from = $('.tgl_from').val();
		// var tgl_to = $('.tgl_to').val();
		// var bank = $('.bank').val();

		window.open(siteurl + active_controller + 'excel_payment_list', '_blank');
	});
</script>