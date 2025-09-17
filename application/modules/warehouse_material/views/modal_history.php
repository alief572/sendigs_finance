<div class="box box-primary">
	<div class="box-header">
		<h3 class="box-title"><b>HISTORY <?=get_name('warehouse','nm_gudang','id', $gudang);?></b></h3><br>
    <h3 class="box-title" style="color:#c85b0e;"><b><?=strtoupper(get_name('ms_material','nm_material','code_material', $material));?></b></h3>
	</div>
  <div class="box-body tableFixHead" style="height:500px;">
  	<table class="table table-striped table-bordered table-hover table-condensed" width="100%">
  		<thead class='bg-white thead'>
  			<tr>
  				<th class="text-left th" width='3%'>#</th>
  				<th class="text-right th" width='9%'>Hist Date</th>
          <th class="text-left th" width='7%'>Hist By</th>
  				<th class="text-left th" width='10%'>Dari Gudang</th>
          <th class="text-left th" width='10%'>Ke Gudang</th>
          <th class="text-right th" width='6%'>Qty</th>
          <th class="text-right th" width='6%'>Stock Awal</th>
          <th class="text-right th" width='6%'>Stock Akhir</th>
          <th class="text-left th" width='20%'>No Trans</th>
          <th class="text-left th">Note</th>
  			</tr>
  		</thead>
  		<tbody>
        <?php
          $no = 0;
          if(!empty($data)){
            foreach($data AS $val => $valx){ $no++;
              $dari = $valx['kd_gudang_dari'];
              $ke   = $valx['kd_gudang_ke'];
              if(!empty($valx['id_gudang_dari'])){
                $dari = get_name('warehouse','nm_gudang','id', $valx['id_gudang_dari']);
              }
              if(!empty($valx['id_gudang_ke'])){
                $ke   = get_name('warehouse','nm_gudang','id', $valx['id_gudang_ke']);
              }
              echo "<tr>";
                echo "<td>".$no."</td>";
                echo "<td align='right'>".date('d-M-Y H:i:s', strtotime($valx['update_date']))."</td>";
                echo "<td>".strtolower($valx['update_by'])."</td>";
                echo "<td>".$dari."</td>";
                echo "<td>".$ke."</td>";
                echo "<td align='right'>".number_format($valx['jumlah_mat'],2)."</td>";
                echo "<td align='right'>".number_format($valx['qty_stock_awal'],2)."</td>";
                echo "<td align='right'>".number_format($valx['qty_stock_akhir'],2)."</td>";
                echo "<td>".$valx['no_ipp']."</td>";
                echo "<td>".strtolower($valx['ket'])."</td>";
              echo "</tr>";
            }
          }
          else{
            echo "<tr>";
              echo "<td colspan='10'>Tidak ada data history</td>";
            echo "</tr>";
          }
        ?>
  		</tbody>
  	</table>
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
  	background: #a0a0a0;
  }
</style>
<script>
  swal.close();
</script>
