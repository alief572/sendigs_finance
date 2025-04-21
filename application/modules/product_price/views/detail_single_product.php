
<div class="box box-primary">
	<div class="box-body">
		<table class='' width='100%'>
			<thead>
				<tr>
					<th class='text-center' width='3%'>#</th>
					<th class='text-left'>Product Name</th>
					<th class='text-center' width='5%'>Qty</th>
					<th class='text-right' width='10%'>Price Unit</th>
					<th class='text-right' width='10%'>Total Price</th>
				</tr>
			</thead>
			<tbody>
				<?php
                    $TOTAL_PRICE_ALL = 0;
					foreach($detail_single AS $val => $valx){
                        $val++;
						$TOTAL_PRICE_ALL += $valx['product_price'] * $valx['product_qty'];

                        $no_bom = str_replace('add','',$valx['category']);
                        $GetProduct = get_name_product_by_bom($no_bom);

                        $nm_product = (!empty($GetProduct[$no_bom]))?$GetProduct[$no_bom]:'';

						echo "<tr>";
							echo "<td align='center'>".$val."</td>";
							echo "<td>".strtoupper($nm_product)."</td>";
							echo "<td align='center'>".number_format($valx['product_qty'])."</td>";
							echo "<td align='right'>".number_format($valx['product_price'],2)."</td>";
							echo "<td align='right'>".number_format($valx['product_price']*$valx['product_qty'],2)."</td>";
						echo "</tr>";
					}
                    echo "<tr>";
                        echo "<td colspan='4'></td>";
                        echo "<th class='text-right'>".number_format($TOTAL_PRICE_ALL,2)."</th>";
                    echo "</tr>";
					?>
			</tbody>
		</table>
	</div>
</div>
