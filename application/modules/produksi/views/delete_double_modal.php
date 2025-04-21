<?php
// print_r($header); style="height:500px;"
?>
<div class="box box-primary">
	<div class="box-body">
		<div class="form-group row">
				<div class="tableFixHead" >
					<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
						<thead class="thead">
							<tr class='bg-blue'>
								<th class='text-center th'>#</th>
								<th class='text-center th'>Tanggal Produksi</th>
                <th class='text-center th'>Costcenter</th>
								<th class='text-center th'>Product</th>
								<th class='text-center th'>Daycode</th>
                <th class='text-center th'>Delete Satuan</th>
                <th class='text-center th'><input type='checkbox' name='chk_all' id='chk_all'></th>
							</tr>
						</thead>
						<tbody>
							<?php
								foreach($detail AS $val => $valx){ $val++;
                  $sisa	  = $this->db->select('jumlah_double')->limit(1)->get_where('table_count_daycode', array('code'=>$valx['code'],'id_product'=>$valx['id_product'],'id_costcenter'=>$valx['id_costcenter']))->result();

                  $jumlah_double  = (!empty($sisa[0]->jumlah_double))?$sisa[0]->jumlah_double:0;

                  $hapus_d = "";
                  $check = "";
                  if($jumlah_double > 1){
                    $hapus_d = "<button type='button' class='btn btn-sm btn-warning delete_qc' title='Delete Double' data-id='".$valx['id']."'><i class='fa fa-trash'></i></button>";
                    $check = "<input type='checkbox' name='check[".$valx['id']."]' class='check_part' value='".$valx['id']."'>";
                  }
									echo "<tr>";
										echo "<td align='center'>".$val."</td>";
                    echo "<td align='center'>".$valx['tanggal_produksi']."</td>";
                    	echo "<td>".strtoupper(get_name('ms_costcenter', 'nama_costcenter', 'id_costcenter', $valx['id_costcenter']))."</td>";
										echo "<td>".strtoupper(get_name('ms_inventory_category2', 'nama', 'id_category2', $valx['id_product']))."</td>";
										echo "<td align='center'>".$valx['code']."</td>";
                    echo "<td align='center'>".$hapus_d."</td>";
                    echo "<td align='center'>".$check."</td>";
									echo "</tr>";
								}
                if(empty($detail)){
                  echo "<tr>";
                    echo "<td>Data not found ...</td>";
                  echo "</tr>";
                }
							 ?>
						</tbody>
					</table>
				</div>
		</div>
    <?php
        echo form_button(array('type'=>'button','class'=>'btn btn-md btn-danger','value'=>'save','content'=>'HAPUS DAYCODE','id'=>'repeat_custom')).' ';
      ?>
	</div>
</div>
<style media="screen">
  /* JUST COMMON TABLE STYLES... */
  /* .table { border-collapse: collapse; width: 100%; }
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
  } */
</style>
<script>
  $("#chk_all").click(function(){
      $('.check_part').not(this).prop('checked', this.checked);
  });
</script>
