<?php
// print_r($header);
$date_now = date('Y-m-d', strtotime($header[0]->date_awal));
?>
<div class="box box-primary">
	<div class="box-body">
		<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
			<thead>
				<tr class='bg-blue'>
          <th class='text-center th' style='vertical-align:middle; width:300px !important;' rowspan='3'>Product</th>
          <th class='text-center th' style='vertical-align:middle;' rowspan='3' width='100px'>Qty Order</th>
          <th class='text-center th' style='vertical-align:middle;' rowspan='3' width='100px'>Stock</th>
          <th class='text-center th' style='vertical-align:middle;' rowspan='3' width='100px'>Shortages to Fulfill Orders</th>
          <th class='text-center th' style='vertical-align:middle;' rowspan='3' width='100px'>Queue</th>
          <th class='text-center th' style='vertical-align:middle;' colspan='<?=$data_num;?>'>Production Planning Date</th>
        </tr>
        <tr class='bg-blue'>
          <?php
          foreach ($data as $key => $value) {
              $loop_date = date("l", strtotime("+".$key." day", strtotime($date_now)));
              $key++;
              echo "<th class='text-center' style='font-size: 12px; vertical-align:middle;' width='200px'>".$loop_date."</th>";
          }
          ?>
        </tr>
        <tr class='bg-blue'>
          <?php
          foreach ($data as $key => $value) { $key++;
              echo "<th class='text-center' style='font-size: 12px; vertical-align:middle;' width='200px'>".$value['date']."</th>";
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
                  $q_weight = "SELECT qty FROM produksi_planning_data WHERE `date`='".$value2['date']."' AND product='".$value['product']."' AND no_plan='".$value['no_plan']."' LIMIT 1 ";
                  $weight = $this->db->query($q_weight)->result();
                  $nil = (!empty($weight))?$weight[0]->qty:0;
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
              echo "<td class='text-center'><b>".number_format($weight[0]->value)."</b></td>";
            }
          echo "</tr>";
          echo "<tr>";
            echo "<td></td>";
            echo "<td colspan='4'><b>AVAILABILITY MAN MINUTES</b></td>";
            foreach ($data as $key2 => $value2) { $key2++;
              $q_weight = "SELECT value FROM produksi_planning_footer WHERE `date`='".$value2['date']."' AND category='availability' AND no_plan='".$value2['no_plan']."' LIMIT 1 ";
              $weight = $this->db->query($q_weight)->result();
              echo "<td class='text-center'><b>".number_format($weight[0]->value,2)."</b></td>";
            }
          echo "</tr>";
         ?>
			</tbody>
		</table>
	</div>
</div>
