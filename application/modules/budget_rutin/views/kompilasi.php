<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.6.1/css/buttons.dataTables.min.css">
<div class="box box-primary" style='margin-right: 17px;'>
	<div class="box-body">
		<div class="table-responsive" >
			<table class="table table-bordered table-striped" id="example1" width='100%'>
				<thead>
					<tr>
						<th class="text-center th">#</th>
						<th class="text-left th">KATEGORI</th> 
						<th class="text-left th">NAMA BARANG</th>
						<th class="text-left th">SPESIFIKASI</th>
						<th class="text-left th">BRAND</th>
						<th class="text-center th">TOTAL</th>
						<th class="text-center th">UNIT</th>
						<?php
						foreach($group_header AS $val => $valx){
							$cc = '';
							if($valx['cost_center'] <> '0'){
								$cc = $valx['cost_center'];
							}
							echo "<th class='text-center th'><u>".strtoupper($valx['nm_dept'])."</u><i><br>".$cc."</i></th>";
						}
						?>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach($group_barang AS $val => $valx){ $val++;
						echo "<tr>";
							echo "<td class='text-center'>".$val."</td>";
							echo "<td class='text-left'>".strtoupper($valx['jenisbarang'])."</td>";
							echo "<td class='text-left'>".strtoupper($valx['nama'])."</td>";
							echo "<td class='text-left'>".strtoupper($valx['spec1'])."</td>";
							echo "<td class='text-left'>".strtoupper($valx['spec2'])."</td>";
							$total_kebutuhan = 0;
							foreach($group_header AS $val2 => $valx2){
								$get_qty = $this->db->query("SELECT a.kebutuhan_month FROM budget_rutin_detail a LEFT JOIN budget_rutin_header b ON a.code_budget=b.code_budget WHERE a.id_barang='".$valx['id_barang']."' AND b.department='".$valx2['department']."' AND b.costcenter='".$valx2['costcenter']."' ")->result();
								$total_kebutuhan += (!empty($get_qty))?$get_qty[0]->kebutuhan_month:0;
							}
							echo "<td class='text-center'>".number_format($total_kebutuhan)."</td>";
							echo "<td class='text-center'>".strtoupper($valx['satuan'])."</td>";
							foreach($group_header AS $val2 => $valx2){
								$get_qty = $this->db->query("SELECT a.kebutuhan_month FROM budget_rutin_detail a LEFT JOIN budget_rutin_header b ON a.code_budget=b.code_budget WHERE a.id_barang='".$valx['id_barang']."' AND b.department='".$valx2['department']."' AND b.costcenter='".$valx2['costcenter']."' ")->result();
								$qty = (!empty($get_qty))?number_format($get_qty[0]->kebutuhan_month):'-';
								echo "<td class='text-center'>".$qty."</td>";
							}
						echo "</tr>";
					}
					?>
				</tbody>
			</table>
		</div>
		<?php
			echo form_button(array('type'=>'button','class'=>'btn btn-md btn-danger','style'=>'float:right; margin: 5px 0px 5px 0px;','content'=>'Back','id'=>'back')).' ';
		?>
	</div>
 </div>
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>
<script src="https://cdn.datatables.net/buttons/1.6.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.html5.min.js"></script>
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
	vertical-align: top;
  }
</style>
<script>
	$(document).on('click', '#back', function(e){
		window.location.href = base_url +'budget_rutin/';
	});
  	$(function() {
    	$("#example1").DataTable({
			dom: 'Blfrtip',
			buttons: [
				{
                extend: 'excel',
            }],			
		});
  	});
</script>
