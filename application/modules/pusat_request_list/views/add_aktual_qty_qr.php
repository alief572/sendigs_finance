<div class="box-body">
	<form id="data-form" method="post" autocomplete="off"><br>
		<input type="hidden" name="id_material" value="<?= $id_material ?>">
		<div class="box box-primary">
			<div class="box-header with-border">
				<h3 class="box-title">SCAN QRCODE</h3>
			</div>
			<div class="box-body">
				<div class="form-group row">
					<div class="col-md-4">
						<div class="input-group">
							<span class="input-group-addon" style="padding: 4px 10px 0px 10px;">
								<i class="fa fa-qrcode fa-3x"></i>
							</span>
							<input type="text" name="qr_code" id="qr_code" class="form-control input-lg" placeholder="QR Code">
						</div>
					</div>
					<div class="col-md-8">
						<span id="help-text" class="text-success text-bold text-lg"></span>
						<div class="notif">
						</div>
					</div>
				</div>
			</div>
		</div>
		<h4>List Product</h4>
		<div class="form-group row">
			<div class="col-md-12">
				<table class="table table-bordered table-striped" width='100%'>
					<thead>
						<tr>
							<th width='5%' class='text-center'>#</th>
							<th>PRODUCT</th>
							<th width='12%' class='text-center'>QTY ORDER</th>
							<th width='12%' class='text-center'>QTY SPK</th>
							<th width='12%' class='text-center'>QTY DELIVERY</th>
							<th width='5%' class='text-center'>#</th>
						</tr>
					</thead>
					<tbody id='load-data'>
						<?php
						if (!empty($getDetail)) {
							foreach ($getDetail as $key => $value) {
								$key++;
								$nama_product = (!empty($GET_DET_Lv4[$value['code_lv4']]['nama'])) ? $GET_DET_Lv4[$value['code_lv4']]['nama'] : '';

								echo "<tr class='tr_" . $key . "'>";
								echo "<td class='text-center'>" . $key . "</td>";
								echo "<td>" . $nama_product . "</td>";
								echo "<td class='text-center'>" . number_format($value['qty_order'], 2) . "</td>";
								echo "<td class='text-center qtyBelumKirim'>" . number_format($value['qty_spk'], 2) . "</td>";
								echo "<td class='text-center'>
											<input type='hidden' name='detail[" . $key . "][id_spk]' value='" . $value['id_spk'] . "'>
											<input type='hidden' name='detail[" . $key . "][code_lv4]' value='" . $value['code_lv4'] . "'>
											<input type='hidden' name='detail[" . $key . "][qty_order]' value='" . $value['qty_order'] . "'>
											<input type='hidden' name='detail[" . $key . "][qty_spk]' value='" . $value['qty_spk'] . "'>
											<input type='text' name='detail[" . $key . "][qty_delivery]' data-id_spk='" . $value['id_spk'] . "' class='form-control input-sm text-center autoNumeric0 changeDelivery' value='" . $value['qty_delivery'] . "'>
											</td>";
								echo "<td class='text-center'><button type='button' class='btn btn-sm btn-danger delPart' data-id='" . $value['id_spk'] . "' title='Delete' data-role='qtip'><i class='fa fa-trash'></i></button></td>";
								echo "</tr>";
							}
						} else {
							echo "<tr>";
							echo "<td colspan='5'>Tidak ada data yang ditampilkan.</td>";
							echo "</tr>";
						}
						?>
					</tbody>
				</table>
			</div>
		</div>
		<div class="form-group row">
			<div class="col-md-6">
				<button type="button" class="btn btn-primary" name="save" id="save">Save</button>
				<button type="button" class="btn btn-danger" style='margin-left:5px;' name="back" id="back">Back</button>
			</div>
		</div>


	</form>
</div>

