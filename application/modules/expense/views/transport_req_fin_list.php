<?php
$ENABLE_ADD     = has_permission('Pengajuan_Transportasi_Approval.Add');
$ENABLE_MANAGE  = has_permission('Pengajuan_Transportasi_Approval.Manage');
$ENABLE_VIEW    = has_permission('Pengajuan_Transportasi_Approval.View');
$ENABLE_DELETE  = has_permission('Pengajuan_Transportasi_Approval.Delete');
?>
<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
<div class="box">
	<div class="box-body"><div class="table-responsive">
		<table id="mytabledata" class="table table-bordered table-striped">
		<thead>
		<tr>
			<th width="5">#</th>
			<th>No</th>
			<th>Tanggal</th>
			<th>Nama</th>
			<th>Status</th>
			<th width="120">Action</th>
		</tr>
		</thead>
		<tbody>
		<?php
		if(!empty($results)){
			$numb=0; foreach($results AS $record){ $numb++; ?>
		<tr>
		    <td><?= $numb; ?></td>
			<td><?= $record->no_doc ?></td>
			<td><?= $record->tgl_doc ?></td>
			<td><?= $record->nmuser ?></td>
			<td><?= $status[$record->status] ?></td>
			<td>
			<?php if($ENABLE_VIEW) : ?>
				<a class="btn btn-default btn-sm print" href="<?=base_url('expense/transport_req_print/'.$record->id)?>" target="transport_req_print" title="Print"><i class="fa fa-print"></i> </a>
				<a class="btn btn-warning btn-sm view" href="<?=base_url('expense/transport_req_view/'.$record->id.'/_fin')?>" title="View"><i class="fa fa-eye"></i></a>
			<?php endif;
			if($ENABLE_MANAGE) :
				if ($record->status==0) {?>
				<a class="btn btn-success btn-sm approve" href="<?=base_url('expense/transport_req_edit/'.$record->id.'/_fin')?>" title="Approve"><i class="fa fa-check-square-o"></i></a>
				<?php }
				endif;?>
			</td>
		</tr>
		<?php
			}
		}  ?>
		</tbody>
		</table>
		</div>
	</div>
	<!-- /.box-body -->
</div>
<div id="form-data"></div>
<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>
<!-- page script -->
<script type="text/javascript">
	var url_view = siteurl+'expense/transport_req_view/';
	var url_approve = siteurl+'expense/transport_req_approve/';
	function data_approve(id){
		swal({
		  title: "Anda Yakin?",
		  text: "Data Akan Disetujui!",
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
				url: url_approve+id+'/1',
				dataType : "json",
				type: 'POST',
				success: function(msg){
					if(msg['save']=='1'){
						swal({
							title: "Sukses!",
							text: "Data Berhasil Di Setujui",
							type: "success",
							timer: 1500,
							showConfirmButton: false
						});
						window.location.reload();
					} else {
						swal({
							title: "Gagal!",
							text: "Data Gagal Di Setujui",
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
<script src="<?= base_url('assets/js/basic.js')?>"></script>

