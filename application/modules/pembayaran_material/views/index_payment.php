<?php
$this->load->view('include/side_menu');
?>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<div class="box">
	<div class="box-header">
		<h3 class="box-title"><?php echo $title; ?></h3>
	</div>
	<div class="box-body">
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation" class="active"><a href="#material" aria-controls="material" role="tab" data-toggle="tab">Material</a></li>
			<li role="presentation"><a href="#nonmaterial" aria-controls="nonmaterial" role="tab" data-toggle="tab">Non Material</a></li>
		</ul>
		<div class="tab-content">
			<div role="tabpanel" class="tab-pane active" id="material">
				<div class="box-body">
					<table class="table table-bordered table-striped" id="mytabledata" width='100%'>
						<thead>
							<tr class='bg-blue'>
								<th class="text-center">No Request</th>
								<th class="text-center">Tgl Bayar</th>
								<th class="text-center">No PO</th>
								<th class="text-center">Supplier</th>
								<th class="text-center">Request Payment</th>
								<th class="text-center">Approve By</th>
								<th class="text-center">Tgl Approve</th>
								<th class="text-center">Status</th>
								<th class="text-center" width='110px'>Option</th>
							</tr>
						</thead>

						<tbody>
							<?php if (empty($results)) {
							} else {
								$numb = 0;
								foreach ($results as $record) {
									$numb++;
							?>
									<tr>
										<td><?= $record->no_request ?></td>
										<td><?= $record->payment_date ?></td>
										<td><?= $record->no_po ?></td>
										<td><?= $record->nm_supplier ?></td>
										<td class="divide"><?= $record->request_payment ?></td>
										<td><?= $record->approved_by ?></td>
										<td><?= ($record->approved_on != "" ? date("d-m-Y", strtotime($record->approved_on)) : "") ?></td>
										<td><?= $data_status[$record->status] ?></td>
										<td><?php if ($akses_menu['read']) : ?>
												<a href='<?= base_url() . 'pembayaran_material/view_payment/' . $record->id ?>/1' class='btn btn-sm btn-default view' title='View Request Payment'><i class='fa fa-search'></i></a>
											<?php endif; ?>
											<?php if ($akses_menu['update']) :
												if ($record->status == 1) { ?>
													<a href='<?= base_url() . 'pembayaran_material/new_payment/' . $record->id ?>/1' class='btn btn-sm btn-warning edit' title='Payment'><i class='fa fa-check'></i></a>
											<?php
												}
											endif; ?>
										</td>
									</tr>
							<?php }
							}  ?>
						</tbody>
					</table>
				</div>
			</div>
			<div role="tabpanel" class="tab-pane" id="nonmaterial">
				<div class="box-body">
					<table class="table table-bordered table-striped" id="mytabledatanm" width='100%'>
						<thead>
							<tr class='bg-blue'>
								<th class="text-center">No Request</th>
								<th class="text-center">Tgl Bayar</th>
								<th class="text-center">No PO</th>
								<th class="text-center">Supplier</th>
								<th class="text-center">Request Payment</th>
								<th class="text-center">Approve By</th>
								<th class="text-center">Tgl Approve</th>
								<th class="text-center">Status</th>
								<th class="text-center" width='110px'>Option</th>
							</tr>
						</thead>

						<tbody>
							<?php if (empty($resultsnm)) {
							} else {
								$numb = 0;
								foreach ($resultsnm as $record) {
									$numb++;
							?>
									<tr>
										<td><?= $record->no_request ?></td>
										<td><?= $record->payment_date ?></td>
										<td><?= $record->no_po ?></td>
										<td><?= $record->nm_supplier ?></td>
										<td class="divide"><?= $record->request_payment ?></td>
										<td><?= $record->approved_by ?></td>
										<td><?= ($record->approved_on != "" ? date("d-m-Y", strtotime($record->approved_on)) : "") ?></td>
										<td><?= $data_status[$record->status] ?><!-- <?= $record->status ?> --></td>
										<td><?php if ($akses_menu['read']) : ?>
												<a href='<?= base_url() . 'pembayaran_material/view_payment/' . $record->id ?>/2' class='btn btn-sm btn-default view' title='View Request Payment'><i class='fa fa-search'></i></a>
											<?php endif; ?>
											<?php if ($akses_menu['update']) :
												if ($record->status == 1) { ?>
													<a href='<?= base_url() . 'pembayaran_material/new_payment/' . $record->id ?>/2' class='btn btn-sm btn-warning edit' title='Payment'><i class='fa fa-check'></i></a>
											<?php
												}
											endif; ?>
										</td>
									</tr>
							<?php }
							}  ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	<div id="form-data">
	</div>
	<?php $this->load->view('include/footer'); ?>
	<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>
	<!-- page script -->
	<script type="text/javascript">
		$(".divide").divide();
		$(function() {
			$("#mytabledata").DataTable({
				"order": [
					[0, "desc"]
				]
			});
			$("#mytabledatanm").DataTable({
				"order": [
					[0, "desc"]
				]
			});
			$("#form-data").hide();
		});
	</script>