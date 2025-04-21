<?php
$this->load->view('include/side_menu');
?>
<?=form_open('pembayaran_material/form_payment_new',array('id'=>'frm_data','name'=>'frm_data'));?>
<div class="box">
	<div class="box-header">
		<h3 class="box-title"><?php echo $title;?></h3>
	</div>
	<div class="box-body">
	<ul class="nav nav-tabs" role="tablist">
		<li role="presentation" class="active"><a href="#material" aria-controls="material" role="tab" data-toggle="tab">Material</a></li>
	</ul>
	<div class="tab-content">
		<div role="tabpanel" class="tab-pane active" id="material">
		  <div class="box-body">
			<table class="table table-bordered table-striped" id="mytabledata" width='100%'>
				<thead>
					<tr class='bg-blue'>
						<th class="text-center">No Request</th>
						<th class="text-center">Tgl Bayar</th>
						<th class="text-center">No PO</th>
						<th class="text-center">Supplier</th>
						<th class="text-center">Request Payment</th>
						<th class="text-center">Approve By</th>
						<th class="text-center">Tgl Approve</th>
						<th class="text-center">Status</th>
						<th class="text-center">Option</th>
					</tr>
				</thead>			
			<tbody>
			<?php 
			$data=0;
			if(empty($results)){
			}else{
				$numb=0; foreach($results AS $record){ 
				$numb++;
				?>
			<tr>
				<td><?= $record->no_request ?></td>
				<td><?= $record->payment_date ?></td>
				<td><?= $record->no_po ?></td>
				<td><?= $record->nm_supplier?></td>
				<td align=right><?= number_format($record->request_payment,2)?></td>
				<td><?= $record->approved_by?></td>
				<td><?= ($record->approved_on!=""?date("d-m-Y",strtotime($record->approved_on)):"")?></td>
				<td><?= $data_status[$record->status]?></td>
				<td>
					<?php if($akses_menu['update']) : 
						?>
							<input type="checkbox" class="chekcbox" id="request_id_<?=$record->id?>" name="request_id[]" data-supplier="<?=$record->id_supplier?>" value="<?=$record->id?>" onclick="ceksupplier('<?=$record->id?>','<?=$record->id_supplier?>')">
							<?php
							$data++;
					endif;?>
				</td>
			</tr>
			<?php } 
			}  ?>
			</tbody>
			<tfoot>
				<tr>
					<td colspan=8></td>
					<td><?php 
					if($data>0){
						echo '<button class="btn btn-info" type="button" onclick="cekdata()">Submit</button>';
					}
					?>
					</td>
				</tr>
			</tfoot>
			</table>
		  </div>
		</div>
	</div>
</div>
<?=form_close()?>
<?php $this->load->view('include/footer'); ?>
<!-- page script -->
<script type="text/javascript">
function cekdata(){
	var sList = 0;
	$('input[type=checkbox]').each(function () {
		var sThisVal = (this.checked ? 1 : 0);
		sList += sThisVal;
	});
	if(sList==0){
		alert("Pilih salah satu No Request !");
	}else{
		$('form#frm_data').submit();
	}
}
$('#mytabledata').DataTable({
	"paging": false,
	"columnDefs": [
		{ "orderable": false, "targets": [7,8] }
	],
	"order": [[ 3, "asc" ], [ 0, "asc" ]],
});
function ceksupplier(id,supplier){
	if($('#request_id_'+id).is(':checked')) {
		$('input[type=checkbox]').each(function () {
			if($(this).data('supplier')!=supplier) {
				$(this).attr("disabled", true);
			}else{
				$(this).attr("disabled", false);
			}
		});
	}else{
		$('input[type=checkbox]').each(function () {
			$(this).attr("disabled", false);
		});
	}
}
</script>
