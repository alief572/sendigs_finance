<?php
// print_r($header);
?>
<div class="box box-primary">
	<div class="box-body">
		<div class="form-group row">
			<div class="col-md-2">
				<label for="customer">Customer Name</label>
			</div>
			<div class="col-md-6">
				<select id="code_cust" name="code_cust" class="form-control input-md chosen-select" disabled>
					<?php foreach ($results['customer'] as $customer){
						$sel = ($customer->id_customer == $header[0]->code_cust)?'selected':'';
					?>
					<option value="<?= $customer->id_customer;?>" <?=$sel;?>><?= strtoupper(strtolower($customer->name_customer))?></option>
					<?php } ?>
				</select>
			</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2">
					<label for="customer">Delivery Date</label>
				</div>
				<div class="col-md-6">
					<input type="text" id="delivery_date" name="delivery_date" class="form-control input-md" placeholder="Delivery Date" readonly value="<?=date('d F Y',strtotime($header[0]->delivery_date));?>">
				</div>
			</div>
			<div class="form-group row">
					<div class="col-md-2">
						<label for="customer">Delivery Methode</label>
					</div>
					<div class="col-md-6">
						<select id="shipping" name="shipping" class="form-control input-md chosen-select" disabled>
							<?php foreach ($results['shipping'] as $shipping){
								$sel = ($shipping->value == $header[0]->shipping)?'selected':'';
							?>
							<option value="<?= $shipping->value;?>" <?=$sel;?>><?= strtoupper(strtolower($shipping->view))?></option>
							<?php } ?>
						</select>
					</div>
		</div>
		<div class="form-group row">
				<div class="tableFixHead" style="height:500px;">
					<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
						<thead class="thead">
							<tr class='bg-blue'>
								<th class='text-center th' style='width: 10%;'>#</th>
								<th class='text-center th' style='width: 50%;'>Product Name</th>
								<th class='text-center th'>Qty Propose</th>
								<th class='text-center th'>Qty Order</th>
							</tr>
						</thead>
						<tbody>
							<?php
								foreach($detail AS $val => $valx){ $val++;
									echo "<tr>";
										echo "<td align='center'>".$val."</td>";
										echo "<td>".strtoupper(get_name('ms_inventory_category2', 'nama', 'id_category2', $valx['product']))."</td>";
										echo "<td align='center'>".$valx['qty_order']."</td>";
										echo "<td align='center'>".$valx['qty_propose']."</td>";
									echo "</tr>";
								}
							 ?>
						</tbody>
					</table>
				</div>
		</div>
	</div>
</div>
<style media="screen">
  /* JUST COMMON TABLE STYLES... */
  .table { border-collapse: collapse; width: 100%; }
  .td { background: #fff; padding: 8px 16px; }

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
