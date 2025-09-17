<?php
// print_r($header);
?>
<div class="box box-primary">
	<div class="box-body">
		<table class="table w-100">
			<tr>
				<th>Customer Name</th>
				<th>:</th>
				<td><?= $sales_order->nm_customer ?></td>
				<th>Quote Number</th>
				<th>:</th>
				<td><?= $sales_order->no_penawaran ?></td>
			</tr>
			<tr>
				<th>Customer Address</th>
				<th>:</th>
				<td><?= $sales_order->alamat ?></td>
				<th>Quote Date</th>
				<th>:</th>
				<td><?= date('d F Y', strtotime($sales_order->tgl_so)) ?></td>
			</tr>
			<tr>
				<th>Contact Person</th>
				<th>:</th>
				<td><?= $sales_order->nm_pic ?></td>
				<th>Invoice Address</th>
				<th>:</th>
				<td><?= $sales_order->invoice_address ?></td>
			</tr>
			<tr>
				<th>TOP</th>
				<th>:</th>
				<td><?= $top_name . ' ' . $sales_order->top_custom ?></td>
				<th>Sales</th>
				<th>:</th>
				<td>
					<?= $sales_order->nm_lengkap ?>
				</td>
			</tr>
			<tr>
				<th>Notes</th>
				<th>:</th>
				<td><?= $sales_order->notes ?></td>
				<th>PO Date</th>
				<th>:</th>
				<td><?= ($sales_order->po_date !== '' && $sales_order->po_date !== '0000-00-00') ? date('d F Y', strtotime($sales_order->po_date)) : null ?></td>
			</tr>
			<tr>
				<th colspan="3"></th>
				<th>PO Number</th>
				<th>:</th>
				<td><?= $sales_order->po_no ?></td>
			</tr>
			<tr>
				<th>Delivery Address</th>
				<th>:</th>
				<td><?= $sales_order->delivery_address ?></td>
				<th>Delivery Date</th>
				<th>:</th>
				<td><?= date('d F Y', strtotime($sales_order->delivery_date)) ?></td>
			</tr>
			<tr>
				<th>Upload Dokumen</th>
				<th>:</th>
				<td style="vertical-align:top;">
					<?php
					$exp_uppo = explode('|', $sales_order->upload_po);
					foreach ($exp_uppo as $uppo) {
						if (base_url($uppo) && $uppo !== '') {
							echo '<a href="' . base_url($uppo) . '" target="_blank">' . str_replace('uploads/po/', '', $uppo) . '</a> <br>';
						}
					}
					?>
				</td>
				<th>Pengiriman</th>
				<th>:</th>
				<td>
					<?= ($sales_order->pengiriman !== '') ? ucfirst($sales_order->pengiriman) : null ?>
				</td>
			</tr>
			<tr>
				<th>Tipe SO</th>
				<td>:</td>
				<td>
					<?php 
						$tipe_so = '';
						if($sales_order->tipe_so == '1') {
							$tipe_so = 'Produk';
						}
						if($sales_order->tipe_so == '2') {
							$tipe_so = 'Instalasi';
						}

						echo $tipe_so;
					?>
				</td>
				<td colspan="3"></td>
			</tr>
		</table>
		<div class="form-group row">
			<div class="tableFixHead">
				<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
					<thead>
						<tr class='bg-blue'>
							<th class='text-center'>No.</th>
							<th class='text-center'>Code</th>
							<th class='text-center' style="width: 250px;">Product Name</th>
							<th class='text-center'>Color</th>
							<th class='text-center'>Surface</th>
							<th class='text-center'>Variant</th>
							<th class='text-center'>Qty</th>
							<th class='text-center'>Price</th>
							<th class='text-center'>Total Price</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$subtotal = 0;
						$discount = 0;
						$ppn = 0;

						$x = 1;
						foreach ($data_sales_order_detail as $sales_order_detail) :
							$request_production = 0;
							if (($sales_order_detail->actual_stock - $sales_order_detail->booking_stock) < $sales_order_detail->qty) {
								$request_production = ($sales_order_detail->qty - ($sales_order_detail->actual_stock - $sales_order_detail->booking_stock));
							}
							echo '
									<tr>
										<td class="text-center" style="vertical-align: middle;">' . $x . '</td>
										<td class="text-center" style="vertical-align: middle;">' . $sales_order_detail->product_code . '</td>
										<td class="text-center" style="vertical-align: middle;min-width: 250px; max-width: 250px;">' . $sales_order_detail->nama_produk . '</td>
										<td class="text-center" style="vertical-align: middle;">' . $sales_order_detail->color . '</td>
										<td class="text-center" style="vertical-align: middle;">' . $sales_order_detail->surface . '</td>
										<td class="text-center" style="vertical-align: middle;">' . $sales_order_detail->variant_product . '</td>
										<td class="text-center" style="vertical-align: middle;">' . number_format($sales_order_detail->qty, 2) . '</td>
										
										<td class="text-left" style="vertical-align: middle;">(' . $data_penawaran->currency . ') ' . number_format(($sales_order_detail->harga_satuan - ($sales_order_detail->harga_satuan * $sales_order_detail->diskon_persen / 100))) . '</td>
                        				<td class="text-left" style="vertical-align: middle;">(' . $data_penawaran->currency . ') ' . number_format((($sales_order_detail->harga_satuan - ($sales_order_detail->harga_satuan * $sales_order_detail->diskon_persen / 100)) * $sales_order_detail->qty), 2) . '</td>
									</tr>
								';


							$subtotal += ((($sales_order_detail->harga_satuan) * $sales_order_detail->qty));
							$discount += (($sales_order_detail->harga_satuan * $sales_order_detail->qty) * $sales_order_detail->diskon_persen / 100);
							$ppn += ((($sales_order_detail->harga_satuan * $sales_order_detail->qty) -  (($sales_order_detail->harga_satuan * $sales_order_detail->qty) * $sales_order_detail->diskon_persen / 100)) * $data_penawaran->ppn / 100);
							$x++;
						endforeach;
						?>
					</tbody>
					<tbody>
						<tr>
							<td colspan="8" class="text-right">Subtotal</td>
							<td>(<?= $data_penawaran->currency ?>) <?= number_format($subtotal, 2) ?></td>
						</tr>
						<tr>
							<td colspan="8" class="text-right">Discount</td>
							<td>(<?= $data_penawaran->currency ?>) <?= number_format($discount, 2) ?></td>
						</tr>
						<tr>
							<td colspan="8" class="text-right">PPn</td>
							<td>(<?= $data_penawaran->currency ?>) <?= number_format($ppn, 2) ?></td>
						</tr>
						<tr>
							<td colspan="8" class="text-right">Grand Total</td>
							<td>(<?= $data_penawaran->currency ?>) <?= number_format($subtotal - ($discount) + $ppn, 2) ?></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<style media="screen">
	/* JUST COMMON TABLE STYLES... */
	.table {
		border-collapse: collapse;
		width: 100%;
	}

	.td {
		background: #fff;
		padding: 8px 16px;
	}

	.tableFixHead {
		overflow: auto;
		height: 300px;
		position: sticky;
		top: 0;
	}

	.thead .th {
		position: sticky;
		top: 0;
		z-index: 9999;
		background: #0073b7;
	}
</style>