<?php
    $ENABLE_ADD     = has_permission('List_PO.Add');
    $ENABLE_MANAGE  = has_permission('List_PO.Manage');
    $ENABLE_VIEW    = has_permission('List_PO.View');
    $ENABLE_DELETE  = has_permission('List_PO.Delete');
?>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
<div class="box">
	<div class="box-header">
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<table class="table table-bordered table-striped" id="mytabledata" width='100%'>
			<thead>
				<tr class='bg-blue'>
					<th class="text-center">No</th>
					<th class="text-center">No PO</th> 
					<th class="text-center">Supplier</th>
					<th class="text-center">Material Name</th>
					<th class="text-center">Qty</th>
					<th class="text-center">Price Supplier</th>
					<th class="text-center">Create By</th>
					<th class="text-center">Created Date</th>
					<th class="text-center" width='170px'>Option</th>
				</tr>
			</thead>
			
		<tbody>
		<?php if(empty($results)){
		}else{
			$numb=0; foreach($results AS $record){ $numb++; 
			
			$list_supplier		= $this->db->query("SELECT nm_supplier FROM tran_material_po_header WHERE no_po='".$record->no_po."' AND deleted='N'")->result_array();
			$arr_sup = array();
			foreach($list_supplier AS $val => $valx){
				$arr_sup[$val] = $valx['nm_supplier'];
			}
			$dt_sup	= implode("<br>", $arr_sup);
			
			
			$list_material		= $this->db->query("SELECT nm_material, qty_purchase, price_ref, price_ref_sup FROM tran_material_po_detail WHERE no_po='".$record->no_po."' GROUP BY id_material")->result_array();
			$arr_mat = array();
			foreach($list_material AS $val => $valx){
				$arr_mat[$val] = $valx['nm_material'];
			}
			$dt_mat	= implode("<br>", $arr_mat);
			
			$arr_qty = array();
			foreach($list_material AS $val => $valx){
				$arr_qty[$val] = number_format($valx['qty_purchase']);
			}
			$dt_qty	= implode("<br>", $arr_qty);
			
			$arr_pur = array();
			foreach($list_material AS $val => $valx){
				$arr_pur[$val] = number_format($valx['price_ref_sup'],2);
			}
			$dt_pur	= implode("<br>", $arr_pur);
						
			?>
		<tr>
			<td style="padding-left:20px">
			<?= $numb ?>
			</td>
			<td><?= $record->no_po ?></td>
			<td><?= $dt_sup ?></td>
			<td><?= $dt_mat ?></td>
			<td><?= $dt_qty?></td>
			<td><?= $dt_pur?></td>
			<td><?= $record->created_by?></td>
			<td><?= $record->created_date?></td>
			<td><?php if($ENABLE_MANAGE) : ?>
			    <a href='<?=base_url().'pr_selection/view_po/'.$record->no_po?>' class='btn btn-sm btn-success editMat' title='View PO'><i class='fa fa-eye'></i></a>
				<a href='<?=base_url().'pr_selection/edit_po/'.$record->no_po?>' class='btn btn-sm btn-warning editMat' title='Edit PO'><i class='fa fa-edit'></i></a>
				<a href='<?=base_url().'pr_selection/print_po/'.$record->no_po?>' class='btn btn-sm btn-primary editMat' title='Print' target='_blank'><i class='fa fa-print'></i></a>
				<?php endif;?></td>
		</tr>
		<?php } 
		}  ?>
		</tbody>
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
