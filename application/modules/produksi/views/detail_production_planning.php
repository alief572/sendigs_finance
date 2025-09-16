<?php
// print_r($header);
$date_now = date('Y-m-d', strtotime($header[0]->date_awal));
?>
<div class="box box-primary">
	<div class="box-body">
		<div class='tableFixHead' style='height:600px;'>
			<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
				<thead class='thead'>
					<tr class='bg-blue'>
	          <th class='text-center th' style='vertical-align:middle; min-width:200px !important;' rowspan='2'>Product</th>
	          <th class='text-center th' style='vertical-align:middle; min-width:100px;' rowspan='2' width='100px'>Total Propose</th>
	          <th class='text-center th' style='vertical-align:middle; min-width:100px;' rowspan='2' width='100px'>Stock</th>
	          <th class='text-center th' style='vertical-align:middle; min-width:100px;' rowspan='2' width='100px'>Shortages to Fulfill Orders</th>
	          <th class='text-center th' style='vertical-align:middle; min-width:100px;' rowspan='2' width='100px'>Queue</th>
	          <th class='text-center th' style='vertical-align:middle; min-width:100px;' colspan='<?=$data_num;?>'>Production Planning Date</th>
	        </tr>
	        <tr class='bg-blue'>
	          <?php
	          foreach ($data as $key => $value) {
	              $loop_date = date("l", strtotime("+".$key." day", strtotime($date_now)));
								$loop_date3 = date("d-m-y", strtotime("+".$key." day", strtotime($date_now)));
	              $key++;
	              echo "<th class='text-center' style='font-size: 12px; vertical-align:middle; min-width:100px;'>".$loop_date."<br>".$loop_date3."</th>";
	          }
	          ?>
	        </tr>
				</thead>
				<tbody>
	        <?php
	          foreach ($product as $key => $value) { $key++;
	              echo "<tr>";
	              echo "<td>".strtoupper(get_name('ms_inventory_category2','nama','id_category2',$value['product']))."</td>";
	              echo "<td class='text-center'>".$value['qty_order']."</td>";
	              echo "<td class='text-center'>".$value['stock']."</td>";
	              echo "<td class='text-center'>".$value['shortages']."</td>";
	              echo "<td class='text-center'>".$value['queue']."</td>";
	              foreach ($data as $key2 => $value2) { $key2++;
	                  // $q_weight = "SELECT qty FROM produksi_planning_data WHERE `date`='".$value2['date']."' AND product='".$value['product']."' AND no_plan='".$value['no_plan']."' LIMIT 1 ";
	                  // $weight = $this->db->query($q_weight)->result();
	                  // $nil = (!empty($weight))?$weight[0]->qty:0;
										$nil = 0;
	                  echo "<td class='text-center'>".number_format($nil)."</td>";
	              }
	              echo "</tr>";
	          }
	          echo "<tr>";
	            echo "<td></td>";
	            echo "<td colspan='4'><b>TOTAL MAN MINUTES</b></td>";
	            foreach ($data as $key2 => $value2) { $key2++;
	              $q_weight = "SELECT value FROM produksi_planning_footer WHERE `date`='".$value2['date']."' AND category='man minutes' AND no_plan='".$value2['no_plan']."' LIMIT 1 ";
	              $weight = $this->db->query($q_weight)->result();

								$weighT = $weight[0]->value;
	              echo "<td class='text-right'><b>".number_format($weighT)."</b></td>";
	            }
	          echo "</tr>";
	          echo "<tr>";
	            echo "<td></td>";
	            echo "<td colspan='4'><b>AVAILABILITY MAN MINUTES</b></td>";
	            foreach ($data as $key2 => $value2) { $key2++;
	              $q_weight = "SELECT value FROM produksi_planning_footer WHERE `date`='".$value2['date']."' AND category='availability' AND no_plan='".$value2['no_plan']."' LIMIT 1 ";
	              $weight = $this->db->query($q_weight)->result();
	              echo "<td class='text-right'><b>".number_format($weight[0]->value)."</b></td>";
	            }
	          echo "</tr>";
	         ?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<style>
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
