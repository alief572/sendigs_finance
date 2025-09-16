<?php
$this->load->view('include/side_menu');
?>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<div class="box">
	<div class="box-header">
		<h3 class="box-title"><?php echo $title;?></h3>
	</div>
	<div class="box-body">
	 <ul class="nav nav-tabs" role="tablist">
		<li role="presentation" class="active"><a href="#material" aria-controls="material" role="tab" data-toggle="tab">Material</a></li>
		<li role="presentation"><a href="#nonmaterial" aria-controls="nonmaterial" role="tab" data-toggle="tab">Non Material</a></li>
	  </ul>
	<div class="tab-content">
		<div role="tabpanel" class="tab-pane active" id="material">

	<div class="box-body table-responsive">
		<table class="table table-bordered table-striped" id="mytabledata" width='100%'>
			<thead>
				<tr class='bg-blue'>
					<th class="text-center">No Request</th>
					<th class="text-center">Tgl Request</th>
					<th class="text-center">Supplier</th>
					<th class="text-center">No PO</th>
					<th class="text-center">No Invoice</th>
					<th class="text-center">Nilai</th>
					<th class="text-center">Keterangan</th>
					<th class="text-center">Request payment</th>
					<th class="text-center">Request By</th>
					<th class="text-center">Approve By</th>
					<th class="text-center">Status</th>
					<th class="text-center" width='150px'>Option</th>
				</tr>
			</thead>
			
		<tbody>
		<?php if(empty($results)){
		}else{
			$numb=0; foreach($results AS $record){ 
			$numb++;
			?>
		<tr>
			<td><?= $record->no_request ?></td>
			<td><?= $record->request_date ?></td>
			<td><?= $record->nm_supplier ?></td>
			<td><?= $record->no_po ?></td>
			<td><?= $record->no_invoice ?></td>
			<td class="divide"><?= $record->nilai_invoice ?></td>
			<td><?= $record->keterangan?></td>
			<td class="divide"><?= $record->request_payment?></td>
			<td><?= $record->created_by?></td>
			<td><?= $record->approved_by?></td>
			<td><?= $data_status[$record->status]?></td>
			<td><?php if($akses_menu['read']) : ?>
				<a href='<?=base_url().'pembayaran_material/view_request/'.$record->id.'/1'?>' class='btn btn-sm btn-default view' title='View Request Payment'><i class='fa fa-search'></i></a>
				<?php endif;?>
				<?php  
					if($record->status==0){
						if($akses_menu['approve']) {
							echo "<a href='" . site_url('pembayaran_material/approve_request/' . $record->id.'/1') . "' class='btn btn-success btn-sm' title='Approve Request Payment' data-role='qtip'><i class='fa fa-check'></i></a> ";
						}
						if($akses_menu['update']) {?>
							<a href='<?=base_url().'pembayaran_material/edit_request/'.$record->id.'/1'?>' class='btn btn-sm btn-warning edit' title='Edit Request Payment'><i class='fa fa-edit'></i></a>
							<?php 
							echo "<a href='javascript:data_delete(". $record->id . ",1)' class='btn btn-danger btn-sm' title='Delete Request Payment' data-role='qtip'><i class='fa fa-remove'></i></a> ";
						}
					};?>
			</td>
		</tr>
		<?php } 
		}  ?>
		</tbody>
		</table>
		  </div>
		</div>
		<div role="tabpanel" class="tab-pane" id="nonmaterial">
	<div class="box-body table-responsive">
		<table class="table table-bordered table-striped" id="mytabledatanm" width='100%'>
			<thead>
				<tr class='bg-blue'>
					<th class="text-center">No Request</th>
					<th class="text-center">Tgl Request</th>
					<th class="text-center">Supplier</th>
					<th class="text-center">No PO</th>
					<th class="text-center">No Invoice</th>
					<th class="text-center">Nilai</th>
					<th class="text-center">Keterangan</th>
					<th class="text-center">Request payment</th>
					<th class="text-center">Request By</th>
					<th class="text-center">Approve By</th>
					<th class="text-center">Status</th>
					<th class="text-center" width='150px'>Option</th>
				</tr>
			</thead>
			
		<tbody>
		<?php if(empty($resultsnm)){
		}else{
			$numb=0; foreach($resultsnm AS $record){ 
			$numb++;
			?>
		<tr>
			<td><?= $record->no_request ?></td>
			<td><?= $record->request_date ?></td>
			<td><?= $record->nm_supplier ?></td>
			<td><?= $record->no_po ?></td>
			<td><?= $record->no_invoice ?></td>
			<td class="divide"><?= $record->nilai_invoice ?></td>
			<td><?= $record->keterangan?></td>
			<td class="divide"><?= $record->request_payment?></td>
			<td><?= $record->created_by?></td>
			<td><?= $record->approved_by?></td>
			<td><?= $data_status[$record->status]?></td>
			<td><?php if($akses_menu['read']) : ?>
				<a href='<?=base_url().'pembayaran_material/view_request/'.$record->id.'/2'?>' class='btn btn-sm btn-default view' title='View Request Payment'><i class='fa fa-search'></i></a>
				<?php endif;?>
				<?php  
					if($record->status==0){
						if($akses_menu['approve']) {
							echo "<a href='" . site_url('pembayaran_material/approve_request/' . $record->id.'/2') . "' class='btn btn-success btn-sm' title='Approve Request Payment' data-role='qtip'><i class='fa fa-check'></i></a> ";
						}
						if($akses_menu['update']) {?>
							<a href='<?=base_url().'pembayaran_material/edit_request/'.$record->id.'/2'?>' class='btn btn-sm btn-warning edit' title='Edit Request Payment'><i class='fa fa-edit'></i></a>
							<?php 
							echo "<a href='javascript:data_delete(". $record->id .",2)' class='btn btn-danger btn-sm' title='Delete Request Payment' data-role='qtip'><i class='fa fa-remove'></i></a> ";
						}
					};?>
			</td>
		</tr>
		<?php } 
		}  ?>
		</tbody>
		</table>
		  </div>
		</div>
		</div>
	</div>
	<!-- /.box-body -->
</div>

<div id="form-data">
</div>
<?php $this->load->view('include/footer'); ?>
<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>
<script type="text/javascript">
$(".divide").divide();
	var url_delete = base_url + active_controller+'/delete_request/';

  	$(function() {
    	$("#mytabledata").DataTable( {"order": [[ 0, "desc" ]] } );
    	$("#mytabledatanm").DataTable( {"order": [[ 0, "desc" ]] } );
    	$("#form-data").hide();
  	});
	function data_delete(id,tipe){
		swal({
		  title: "Anda Yakin?",
		  text: "Data Akan Dihapus!",
		  type: "info",
		  showCancelButton: true,
		  confirmButtonText: "Ya, setuju!",
		  cancelButtonText: "Tidak!",
		  closeOnConfirm: false,
		  closeOnCancel: true
		},
		function(isConfirm){
		  if (isConfirm) {
			$.ajax({
				url: url_delete+id+"/"+tipe,
				dataType : "json",
				type: 'GET',
				success: function(msg){
					if(msg['save']=='1'){
						swal({
							title: "Sukses!",
							text: "Data Berhasil Di hapus",
							type: "success",
							timer: 1500,
							showConfirmButton: false
						});
						window.location.reload();
					} else {
						swal({
							title: "Gagal!",
							text: "Data Gagal Di hapus",
							type: "error",
							timer: 1500,
							showConfirmButton: false
						});
					};
					console.log(msg);
				},
				error: function(msg){
					swal({
						title: "Gagal!",
						text: "Ajax Data Gagal Di Proses",
						type: "error",
						timer: 1500,
						showConfirmButton: false
					});
					console.log(msg);
				}
			});
		  }
		});
	}
</script>
