<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">
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
			<li role="presentation"></li>
		</ul>
		<div class="tab-content">
			<div role="tabpanel" class="tab-pane active" id="material">
				<div class="box-body">
					<table class="table table-bordered table-striped" id="mytabledata" width='100%'>
						<thead>
							<tr class='bg-blue'>
								<th class="text-center">No Payment</th>
								<th class="text-center">No Dokumen</th>
								<th class="text-center">Tgl Bayar</th>
								<th class="text-center">Requesto / Supplier</th>
								<th class="text-center">Nilai Bayar</th>
								<th class="text-center">Keterangan</th>
								<th class="text-center" width='110px'>Option</th>
							</tr>
						</thead>

						<tbody>
							<?php 

							if (!empty($results)) {
								$no = 1;
								foreach ($results as $item) {

									$nm_supplier = $item->nm_supplier;

									echo '<tr>';
									echo '<td class="text-center">' . $item->id_payment . '</td>';
									echo '<td class="text-center">' . $item->no_doc . '</td>';
									echo '<td class="text-center">' . date('d F Y', strtotime($item->tgl_bayar)) . '</td>';
									echo '<td class="text-center">' . $nm_supplier . '</td>';
									echo '<td class="text-right">' . number_format($item->payment_bank, 2) . '</td>';
									echo '<td class="text-left">'.$item->keterangan_pembayaran.'</td>';
									echo '<td>';
									echo '<a href="' . base_url('pembayaran_material/view_payment_new/' . $item->id_payment) . '" target="_blank" class="btn btn-sm btn-info view" title="View Request Payment"><i class="fa fa-eye"></i></a>';
									if(file_exists('assets/expense/'.$item->link_doc) && $item->link_doc !== '') {
										echo '<a href="'.base_url('assets/expense/'.$item->link_doc).'" class="btn btn-sm btn-primary" style="margin-left: 5px;"><i class="fa fa-download"></i></a>';
									}
									echo '</td>';
									echo '</tr>';

									$no++;
								}
							}

							?>
						</tbody>
					</table>
				</div>
			</div>
			<div role="tabpanel" class="tab-pane" id="non_material">
				<div class="box-body">
					<table class="table table-bordered table-striped" id="mytabledatanonmaterial" width='100%'>
						<thead>
							<tr class='bg-blue'>
								<th class="text-center">No Payment</th>
								<th class="text-center">No Dokumen</th>
								<th class="text-center">Tgl Bayar</th>
								<th class="text-center">Requesto / Supplier</th>
								<th class="text-center">Nilai Bayar</th>
								<th class="text-center">Keterangan</th>
								<th class="text-center" width='110px'>Option</th>
							</tr>
						</thead>

						<tbody>
							<?php 

							if (!empty($results2)) {
								$no = 1;
								foreach ($results2 as $item) {
									echo '<tr>';
									echo '<td class="text-center">' . $item->id_payment . '</td>';
									echo '<td class="text-center">' . $item->no_doc . '</td>';
									echo '<td class="text-center">' . date('d F Y', strtotime($item->tgl_bayar)) . '</td>';
									echo '<td class="text-center">' . $item->created_by . '</td>';
									echo '<td class="text-right">' . number_format($item->payment_bank, 2) . '</td>';
									echo '<td class="text-left">'.$item->keterangan_pembayaran.'</td>';
									echo '<td>';
									echo '<a href="' . base_url('pembayaran_material/view_payment_new/' . $item->id_payment) . '" target="_blank" class="btn btn-sm btn-info view" title="View Request Payment"><i class="fa fa-eye"></i></a>';
									if(file_exists('assets/expense/'.$item->link_doc) && $item->link_doc !== '') {
										echo '<a href="'.base_url('assets/expense/'.$item->link_doc).'" class="btn btn-sm btn-primary" style="margin-left: 5px;"><i class="fa fa-download"></i></a>';
									}
									echo '</tr>';

									$no++;
								}
							}

							?>
						</tbody>
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

	<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>

	<!-- page script -->
	<script>
		$(document).ready(function() {
			$("#mytabledata").DataTable({
				"order": [
					[0, "asc"]
				]
			});
			$("#form-data").hide();

			$("#mytabledatanonmaterial").DataTable({
				"order": [
					[0, "asc"]
				]
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