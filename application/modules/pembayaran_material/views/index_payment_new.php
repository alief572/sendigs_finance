<link rel="stylesheet" href="https://cdn.datatables.net/2.1.7/css/dataTables.dataTables.min.css">
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<div class="box">
	<div class="box-header">
		<h3 class="box-title"><?php echo $title; ?></h3>
		<button type="button" class="btn btn-sm btn-success choose_payment" style="float: right;">Payment</button>
	</div>
	<div class="box-body">

		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation" class="active"><a href="#material" aria-controls="material" role="tab" data-toggle="tab">PR</a></li>
			<li role="presentation"><a href="#non_material" aria-controls="non_material" role="tab" data-toggle="tab">Non PR</a></li>
		</ul>
		<div class="tab-content">
			<div role="tabpanel" class="tab-pane active" id="material">
				<div class="box-body table-responsive">
					<table class="table table-bordered table-striped" id="mytabledata" width='100%'>
						<thead>
							<tr class='bg-blue'>
								<th class="text-center">No Payment</th>
								<th class="text-center">No Dokumen</th>
								<th class="text-center">Tgl Bayar</th>
								<th class="text-center">Requestor / Supplier</th>
								<th class="text-center">Nilai Bayar</th>
								<th class="text-center">Keterangan</th>
								<th class="text-center" width='110px'>Option</th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
				</div>
			</div>
			<div role="tabpanel" class="tab-pane" id="non_material">
				<div class="box-body table-responsive">
					<table class="table table-bordered table-striped" id="mytabledatanonmaterial" width='100%'>
						<thead>
							<tr class='bg-blue'>
								<th class="text-center">No Payment</th>
								<th class="text-center">No Dokumen</th>
								<th class="text-center">Tgl Bayar</th>
								<th class="text-center">Requestor / Supplier</th>
								<th class="text-center">Nilai Bayar</th>
								<th class="text-center">Keterangan</th>
								<th class="text-center" width='110px'>Option</th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	<div class="modal" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
					<h4 class="modal-title" id="myModalLabel"><span class="fa fa-money"></span>&nbsp;Pilih Jenis Payment</h4>
				</div>
				<div class="modal-body" id="MyModalBody">
					<div class="form-group">
						<label for="">Jenis Payment</label>
						<select name="jenis_payment" id="" class="form-control form-control-sm jenis_payment">
							<option value="">- Jenis Payment -</option>
							<option value="1">Pembayaran PR</option>
							<option value="2">Pembayaran Non PR</option>
						</select>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-success confirm_jenis_payment"><i class="fa fa-check"></i> Proses</button>
					<button type="button" class="btn btn-danger" data-dismiss="modal">
						<span class="glyphicon glyphicon-remove"></span> Batal</button>
				</div>
			</div>
		</div>
	</div>
	<div id="form-data">
	</div>

	<script src="https://cdn.datatables.net/2.1.7/js/dataTables.min.js"></script>

	<!-- page script -->
	<script>
		var tablePR;
		var tableNonPR;

		$(document).ready(function() {
			tablePR = $("#mytabledata").DataTable({
				ajax: {
					url: siteurl + active_controller + 'get_data_payment_list_pr',
					type: "POST",
					dataType: "JSON"
				},
				columns: [
					{ data: 'no_payment', className: 'text-center' },
					{ data: 'no_doc', className: 'text-center' },
					{ data: 'tgl_bayar', className: 'text-center' },
					{ data: 'supplier', className: 'text-center' },
					{ data: 'nilai_bayar', className: 'text-right' },
					{ data: 'keterangan', className: 'text-left' },
					{ data: 'option', className: 'text-center' }
				],
				columnDefs: [
					{ targets: [6], orderable: false, searchable: false }
				],
				responsive: true,
				processing: true,
				serverSide: true,
				stateSave: true,
				destroy: true,
				pageLength: 10,
				order: [[2, "desc"]]
			});

			tableNonPR = $("#mytabledatanonmaterial").DataTable({
				ajax: {
					url: siteurl + active_controller + 'get_data_payment_list_non_pr',
					type: "POST",
					dataType: "JSON"
				},
				columns: [
					{ data: 'no_payment', className: 'text-center' },
					{ data: 'no_doc', className: 'text-center' },
					{ data: 'tgl_bayar', className: 'text-center' },
					{ data: 'requestor', className: 'text-center' },
					{ data: 'nilai_bayar', className: 'text-right' },
					{ data: 'keterangan', className: 'text-left' },
					{ data: 'option', className: 'text-center' }
				],
				columnDefs: [
					{ targets: [6], orderable: false, searchable: false }
				],
				responsive: true,
				processing: true,
				serverSide: true,
				stateSave: false,
				destroy: true,
				pageLength: 10,
				order: [[2, "desc"]]
			});

			$("#form-data").hide();

			$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
				$.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
			});
		});

		$(document).on('click', '.choose_payment', function() {
			$('#dialog-popup').modal('show');
		});

		$(document).on('click', '.confirm_jenis_payment', function() {
			var jenis_payment = $('.jenis_payment').val();

			if (jenis_payment == '' || jenis_payment == null) {
				swal({
					title: 'Warning !',
					text: 'Mohon pilih salah satu Jenis Payment !',
					type: 'warning'
				});
			} else {
				if (jenis_payment == 1 || jenis_payment == 2) {
					window.location.href = siteurl + active_controller + 'list_request_payment/' + jenis_payment
				} else {
					swal({
						title: 'Error !',
						text: 'Please try again later !',
						type: 'error'
					});
				}
			}
		});
	</script>