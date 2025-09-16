
<div class="box box-primary">
	<div class="box-body">
		<table class='' width='100%'>
			<thead>
				<tr>
					<th class='text-center' width='3%'>#</th>
					<th class='text-left'>Material Name</th>
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
						$TOTAL_PRICE_ALL += $valx['berat_bersih'] * $valx['price_ref'];

                        $code_material = $valx['code_material'];
                        $nm_product = (!empty($GET_MATERIAL[$code_material]['nama']))?$GET_MATERIAL[$code_material]['nama']:'';

						echo "<tr>";
							echo "<td align='center'>".$val."</td>";
							echo "<td>".strtoupper($nm_product)."</td>";
							echo "<td align='right'>".number_format($valx['berat_bersih'],4)."</td>";
							echo "<td align='right'>".number_format($valx['price_ref'],2)."</td>";
							echo "<td align='right'>".number_format($valx['berat_bersih']*$valx['price_ref'],2)."</td>";
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
