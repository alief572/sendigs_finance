<?php
$this->load->view('include/side_menu');
?>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<div class="box">
	<div class="box-header">
		<h3 class="box-title"><?php echo $title;?></h3>
	</div>
	<div class="box-body">
		<table class="table table-bordered table-striped" id="mytabledata" width='100%'>
			<thead>
				<tr class='bg-blue'>
					<th class="text-center">No Request</th>
					<th class="text-center">Tgl Jurnal</th>
					<th class="text-center">Status</th>
					<th class="text-center" width='110px'>Option</th>
				</tr>
			</thead>			
		<tbody>
		<?php if(empty($results)){
		}else{
			$numb=0; foreach($results AS $record){ 
			$numb++;
			?>
		<tr>
			<td><?= $record->no_reff ?></td>
			<td><?= $record->tanggal ?></td>
			<td><?= $data_status[$record->stspos]?></td>
			<td><?php if($akses_menu['read']) : ?>
				<a href='<?=base_url().'pembayaran_material/view_jurnal/'.$record->nomor?>' class='btn btn-sm btn-default view' title='View Jurnal'><i class='fa fa-search'></i></a>
				<?php endif;?>
				<?php if($akses_menu['update']) : 
					if($record->stspos==0) { ?>
						<a href='<?=base_url().'pembayaran_material/edit_jurnal/'.$record->nomor?>' class='btn btn-sm btn-warning edit' title='Jurnal'><i class='fa fa-check'></i></a>
						<?php
					}
				endif;?>
			</td>
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
<?php $this->load->view('include/footer'); ?>
<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>
<!-- page script -->
<script type="text/javascript">
$(".divide").divide();

  	$(function() {
    	$("#mytabledata").DataTable({"order": [[ 2, "desc" ]] });
    	$("#form-data").hide();
  	});
</script>