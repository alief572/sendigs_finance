
<div class="box box-primary">
	<div class="box-body">
		<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
			<thead>
				<tr class='bg-blue'>
          <th class='text-center th' width='5%' style='vertical-align:middle;'>No</th>
          <th class='text-center th' width='20%' style='vertical-align:middle;'>Product Name</th>
					<th class='text-center th' width='10%' style='vertical-align:middle;'>Qty Order</th>
          <th class='text-center th' width='10%' style='vertical-align:middle;'>Qty Propose</th>
          <th class='text-center th' width='10%' style='vertical-align:middle;'>Qty Delivery</th>
          <th class='text-center th' width='10%' style='vertical-align:middle;'>Qty Balance </th>
					<th class='text-center th' width='10%' style='vertical-align:middle;'>Remarks </th>
					<th class='text-center th' style='vertical-align:middle;'>Daycode </th>
        </tr>
			</thead>
			<tbody>
        <?php
          foreach ($detail as $key => $value) { $key++;
							$list_daycode = $this->db->query("SELECT * FROM cek_daycode_delivery WHERE delivery_code='".$value['no_delivery']."' AND id_product='".$value['product']."'")->result_array();
							$dtListArray = array();
							foreach($list_daycode AS $val => $valx){
								$dtListArray[$val] = $valx['daycode'];
							}
							$dtImplode	= implode(", ", $dtListArray);

							$bal = $value['qty_propose'] - $value['qty_delivery'];
							$balance = ($bal < 0)?0:$bal;

							$remaks = (!empty($value['remarks']))?$value['remarks']:'-';
							echo "<tr>";
              echo "<td class='text-center'>".$key."</td>";
              echo "<td>".strtoupper(get_name('ms_inventory_category2','nama','id_category2',$value['product']))."</td>";
							echo "<td class='text-center'>".number_format($value['qty_propose'])."</td>";
              echo "<td class='text-center'>".number_format($value['qty_order'])."</td>";
              echo "<td class='text-center'>".number_format($value['qty_delivery'])."</td>";
              echo "<td class='text-center'>".$balance."</td>";
							echo "<td class='text-center'>".ucfirst($remaks)."</td>";
							echo "<td class='text-left'>".$dtImplode."</td>";
              echo "</tr>";
          }
         ?>
			</tbody>
		</table>
	</div>
</div>
