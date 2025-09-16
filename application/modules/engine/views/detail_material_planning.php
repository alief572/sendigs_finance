<?php
// print_r($header);
?>
<div class="box box-primary">
	<div class="box-body">
		<div class='tableFixHead' style='height:500px;'>
			<table class='table table-striped table-bordered table-hover table-condensed'>
				<thead>
					<tr class='bg-blue'>
	          <th class='text-center headcol long_th' style='vertical-align:middle; min-width:300px;'>Product</th>
	          <th class='text-center long long_th' style='vertical-align:middle;'>Total Order</th>
	          <?php
	          $siz = 74/$data_num;
	          foreach ($data as $key => $value) { $key++;
	              echo "<th class='text-left long long_th' style='font-size: 12px; vertical-align:top; z-index: 99999; min-width:100px;'>".strtoupper(get_name('ms_material','nm_material','code_material',$value['material']))."</th>";
	          }
	          ?>
	        </tr>
				</thead>
				<tbody>
	        <?php
	          foreach ($product as $key => $value) { $key++;
	              echo "<tr>";
	              echo "<td class='long_td'>".strtoupper(get_name('ms_inventory_category2','nama','id_category2',$value['product']))."</td>";
	              echo "<td class='text-center'>".$value['qty_order']."</td>";
	              foreach ($data as $key2 => $value2) { $key2++;
	                  $q_weight = "SELECT weight FROM material_planning_data WHERE material='".$value2['material']."' AND product='".$value['product']."' AND no_plan='".$value['no_plan']."' LIMIT 1 ";
	                  $weight = $this->db->query($q_weight)->result();
	                  $nil = (!empty($weight))?$weight[0]->weight:0;
	                  echo "<td class='text-right'>".number_format($nil,2)."</td>";
	              }
	              echo "</tr>";
	          }
	          echo "<tr>";
	            echo "<td class='long_td'><b>TOTAL KEBUTUHAN</b></td>";
							echo "<td></td>";
	            foreach ($data as $key2 => $value2) { $key2++;
	              $q_weight = "SELECT weight FROM material_planning_footer WHERE material='".$value2['material']."' AND category='sum' AND no_plan='".$value2['no_plan']."' LIMIT 1 ";
	              $weight = $this->db->query($q_weight)->result();
	              echo "<td class='text-right'><b>".number_format($weight[0]->weight,2)."</b></td>";
	            }
	          echo "</tr>";
	         ?>
				</tbody>
			</table>
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

	.tableFixHead thead th {
	  position: sticky;
	  top: 0;
		z-index: 99999;
		background-color:#0073b7 ;
	}

  .long_td:first-child{
    position:sticky;
    left:0;
    z-index: 9999;
    background-color:#e7dbdb;
		font-weight: bold;
  }

	.long_th:first-child {
    position:sticky;
    left:0;
    z-index: 999999;
		background-color:#0073b7 ;
  }

  .long{
    vertical-align: middle;
  }

  .headcol{
    font-weight: bold;
    vertical-align: middle;
  }
</style>
