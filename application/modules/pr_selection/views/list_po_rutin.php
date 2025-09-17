<?php
    $ENABLE_ADD     = has_permission('List_RFQ.Add');
    $ENABLE_MANAGE  = has_permission('List_RFQ.Manage');
    $ENABLE_VIEW    = has_permission('List_RFQ.View');
    $ENABLE_DELETE  = has_permission('List_RFQ.Delete');
?>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
<div class="box">
	<div class="box-header">
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<table id="mytabledata" class="table table-bordered table-striped">
		<thead>
				<tr class='bg-blue'>
					<th class="text-center">No</th>
					<th class="text-center">No RFQ</th> 
					<th class="text-center">Supplier</th>
					<th class="text-center">Material Name</th>
					<th class="text-center">Total Request</th>
					<th class="text-center">Create By</th>
					<th class="text-center">Created Date</th>
					<th class="text-center">Action</th>
				</tr>
		</thead>
		<tbody>
		<?php if(empty($results)){
		}else{
			$numb=0; foreach($results AS $record){ $numb++; 
			
			$list_supplier		= $this->db->query("SELECT nm_supplier FROM tran_material_rfq_header WHERE no_rfq='".$record->no_rfq."' AND deleted='N'")->result_array();
			$arr_sup = array();
			foreach($list_supplier AS $val => $valx){
				$arr_sup[$val] = $valx['nm_supplier'];
			}
			$dt_sup	= implode("<br>", $arr_sup);
			
			
			$list_material		= $this->db->query("SELECT nm_material, qty FROM tran_material_rfq_detail WHERE no_rfq='".$record->no_rfq."' AND deleted='N' GROUP BY id_material")->result_array();
			$arr_mat = array();
			foreach($list_material AS $val => $valx){
				$arr_mat[$val] = $valx['nm_material'];
			}
			$dt_mat	= implode("<br>", $arr_mat);
						
			?>
		<tr>
			<td style="padding-left:20px">
			<?= $numb ?>
			</td>
			<td><?= $record->no_rfq ?></td>
			<td><?= $dt_sup ?></td>
			<td><?= $dt_mat ?></td>
			<td><?= number_format($record->total_request)?></td>
			<td><?= $record->created_by?></td>
			<td><?= $record->created_date?></td>
			<td><?php if($ENABLE_MANAGE) : ?>
			    <a href='<?=base_url().'pr_selection/add_perbandingan/'.$record->no_rfq?>' class='btn btn-sm btn-success editMat' title='Add Perbandingan'><i class='fa fa-plus'></i></a>
				<a href='<?=base_url().'pr_selection/pengajuan/'.$record->no_rfq?>' class='btn btn-sm btn-warning editMat' title='Pengajuan'><i class='fa fa-edit'></i></a>
				<?php endif;?></td>
		</tr>
		<?php } 
		}  ?>
		</tbody>
		<tfoot>
				<tr class='bg-blue'>
					<th class="text-center">No</th>
					<th class="text-center">No RFQ</th> 
					<th class="text-center">Supplier</th>
					<th class="text-center">Material Name</th>
					<th class="text-center">Total Request</th>
					<th class="text-center">Create By</th>
					<th class="text-center">Created Date</th>
				    <th class="text-center">Action</th>
				</tr>
		</tfoot>
		</table>
	</div>
	<!-- /.box-body -->
</div>
<div id="form-data">
</div>
<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>
<!-- page script -->
<script type="text/javascript">

  	$(function() {
    	$("#mytabledata").DataTable();
    	$("#form-data").hide();
  	});
</script>
