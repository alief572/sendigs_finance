<?php
$tanggal = date('Y-m-d');
foreach ($results['header'] as $header) {
}
?>

<div class="box box-primary">
	<div class="box-body">
		<form id="data-form" method="post">
			<div class="col-sm-12">
				<div class="input_fields_wrap2">
					<div class="row">
						<center><label for="customer">
								<h3>Purchase Request</h3>
							</label></center>
						<div class="col-sm-12">
							<div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-4">
										<label for="customer">No. Request</label>
									</div>
									<div class="col-md-8">
										<?= $header->no_pr ?>
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-12">
							<div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-4">
										<label for="id_customer">Customer</label>
									</div>
									<div class="col-md-8">
										<?= $header->nm_customer ?>
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-12">
							<div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-4">
										<label for="customer">Tanggal Dibutuhkan</label>
									</div>
									<div class="col-md-8">
										<?= date('d F Y', strtotime($header->tgl_dibutuhkan)) ?>
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-12">
							<div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-4">
										<label for="customer">Requestor</label>
									</div>
									<div class="col-md-8">
										<?= $header->nama_user ?>
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-12">
							<div class="form-group row">

							</div>
							<div class="form-group row">
								<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
									<thead class='thead'>
										<tr class='bg-blue'>
											<th class='text-center th'>#</th>
											<th class='text-center th'>Material Name</th>
											<th class='text-center th'>Material Code</th>
											<th class='text-center th'>Min Stock</th>
											<th class='text-center th'>Max Stock</th>
											<th class='text-center th'>Min Order</th>
											<th class='text-center th'>Qty PR (Pack)</th>
											<th class="text-center th">Unit Pack</th>
											<th class='text-center th'>Qty</th>
											<th class="text-center th">Unit Measurement</th>
											<th class='text-center th'>Status</th>
											<th class='text-center th'>#</th>
										</tr>
									</thead>
									<tbody>
										<?php
										foreach ($results['detail'] as $key => $value) {
											$key++;
											$nm_material 	= (!empty($GET_LEVEL4[$value['id_material']]['nama'])) ? $GET_LEVEL4[$value['id_material']]['nama'] : '';
											$stock_free 	= $value['stock_free'];
											$use_stock 		= $value['use_stock'];
											$sisa_free 		= $stock_free - $use_stock;
											$propose 		= $value['propose_purchase'];

											$check_po_created = $this->db->select('IF(SUM(qty) IS NULL, 0, SUM(qty)) AS ttl_qty_po')->get_where('dt_trans_po', ['idpr' => $value['id'], 'tipe' => ''])->row();

											$status = "<div class='badge bg-red'>PO Not Created</div>";
											if ($check_po_created->ttl_qty_po > 0) {
												if($check_po_created->ttl_qty_po >= $value['propose_purchase']) {
													$status = '<div class="badge bg-green">PO Created</div>';
												}else{
													$status = '<div class="badge bg-yellow">Partial</div>';
												}
											}

											if ($value['category'] == 'pr stok') {
												$get_barang = $this->db->get_where('accessories', ['id' => $value['id_material']])->row();

												$konversi = ($get_barang->konversi > 0) ? $get_barang->konversi : 1;

												$qty_pack = $value['propose_purchase'];
												$unit_pack = strtoupper($value['satuan_packing']);
												$qty = ($value['propose_purchase'] * $konversi);
												$unit_meas = strtoupper($value['unit_measure']);
											} else {
												$get_barang = $this->db->get_where('new_inventory_4', ['code_lv4' => $value['id_material']])->row();

												$konversi = ($get_barang->konversi > 0) ? $get_barang->konversi : 1;

												$qty_pack = ($value['propose_purchase'] / $konversi);
												$unit_pack = strtoupper($value['satuan_packing']);
												$qty = $value['propose_purchase'];
												$unit_meas = strtoupper($value['unit_measure']);
											}

											echo "<tr>";
											echo "<td class='text-center'>" . $key . "</td>";
											echo "	<td class='text-left'>" . $value['nm_product'] . ";
											<input type='hidden' name='detail[" . $key . "][id]' value='" . $value['id'] . "'>
											</td>";
											echo "	<td class='text-left'>" . $value['code_material'] . "</td>";
											echo "<td class='text-right min_stok'>" . number_format($value['min_stok'], 2) . "</td>";
											echo "<td class='text-right max_stok'>" . number_format($value['max_stok'], 2) . "</td>";
											echo "<td class='text-right min_order'>" . number_format(0, 2) . "</td>";
											echo "<td class='text-right'>" . number_format($qty_pack, 2) . "</td>";
											echo "<td class='text-center'>" . $unit_pack . "</td>";
											echo "<td class='text-right'>" . number_format($qty, 2) . "</td>";
											echo "<td class='text-center'>" . $unit_meas . "</td>";
											echo "<td class='text-center'>" . $status . "</td>";
											echo "<td class='text-center'></td>";

											echo "</tr>";
										}
										?>
									</tbody>
								</table>
							</div>
						</div>

					</div>
				</div>
		</form>
	</div>
</div>