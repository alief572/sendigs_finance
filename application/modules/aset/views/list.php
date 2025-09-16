<?php
    $ENABLE_ADD     = has_permission('Assets.Add');
    $ENABLE_MANAGE  = has_permission('Assets.Manage');
    $ENABLE_VIEW    = has_permission('Assets.View');
    $ENABLE_DELETE  = has_permission('Assets.Delete');
?>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
<div class="box">
	<div class="box-header">
		<?php if ($ENABLE_ADD) : ?>
			<a class="btn btn-success" href="javascript:void(0)" title="Add" onclick="add_data()"><i class="fa fa-plus">&nbsp;</i>New</a>
		<?php endif; ?>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped">
		<thead>
		<tr>
			<th>Kategori Asset</th>
			<th>Departemen</th>
			<th>Costcenter</th>
			<th>Nama Aset</th>
			<th>Qty</th>
			<th>Budget</th>
			<th>Sisa Budget PR</th>
			<th>Sisa Budget PO</th>
			<th>Planing</th>
			<th>Status Approval</th>
			<?php if($ENABLE_MANAGE) : ?>
			<th width="50">Action</th>
			<?php endif; ?>
		</tr>
		</thead>

		<tbody>
		<?php if(empty($results)){
		}else{
			$numb=0; foreach($results AS $record){ $numb++; 
			if($record->status_appr == 0){
			$approval ='Belum Approval';
			}
			else if($record->status_appr == 1){
			$approval ='Sudah Approval';	
				
			}
			else if($record->status_appr == 2){
			$approval ='Ditolak';	
				
			}
			?>
		<tr>
			<td><?= $record->coa?> | <?= $record->nama ?></td>
			<td><?= $record->nm_dept ?></td>
			<td><?= strtoupper($record->nm_costcenter) ?></td>
			<td><?= $record->nama_aset ?></td>
			<td><?= $record->qty ?></td>
			<td><?= number_format($record->budget) ?></td>
			<td><?= number_format($record->budgetpr) ?></td>
			<td><?= number_format($record->budgetpo) ?></td>
			<td><?= $record->tahun ?> - <?= $record->bulan ?></td>
		    <td><?= $approval ?> - <?= $record->alasan ?></td>
			<td style="padding-left:20px">
			<?php if($ENABLE_MANAGE) : ?>
				<a class="text-green" href="javascript:void(0)" title="Edit" onclick="edit_data('<?=$record->id?>')"> <i class="fa fa-pencil"></i>
				</a>
			<?php endif; ?>

			<?php if($ENABLE_DELETE) : ?>
				<a class="text-red" href="javascript:void(0)" title="Delete" onclick="delete_data('<?=$record->id?>')"> <i class="fa fa-trash"></i>
				</a>
			<?php endif; ?>
			</td>
		</tr>
		<?php } }  ?>
		</tbody>

		<tfoot>
		<tr>
			<th>Kategori Asset</th>
			<th>Departemen</th>
			<th>Costcenter</th>
			<th>Nama Aset</th>
			<th>Qty</th>
			<th>Budget</th>
			<th>Sisa Budget PR</th>
			<th>Sisa Budget PO</th>
			<th>Planing</th>
			<th>Status Approval</th>
			<th><?php if($ENABLE_MANAGE) : ?>
			Action
			<?php endif; ?>
			</th>
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

  	function add_data(){
		var url = 'aset/create/';
		$(".box").hide();
		$("#form-data").show();
		$("#form-data").load(siteurl+url);
		$("#title").focus();
	}

  	function edit_data(id){
		if(id!=""){
			var url = 'aset/edit/'+id;
			$(".box").hide();
			$("#form-data").show();
			$("#form-data").load(siteurl+url);
		    $("#title").focus();
		}
	}

	//Delete
	function delete_data(id){
		//alert(id);
		swal({
		  title: "Anda Yakin?",
		  text: "Data Akan Terhapus secara Permanen!",
		  type: "warning",
		  showCancelButton: true,
		  confirmButtonColor: "#DD6B55",
		  confirmButtonText: "Ya, delete!",
		  cancelButtonText: "Tidak!",
		  closeOnConfirm: false,
		  closeOnCancel: true
		},
		function(isConfirm){
		  if (isConfirm) {
		  	$.ajax({
		            url: siteurl+'aset/hapus_data/'+id,
		            dataType : "json",
		            type: 'POST',
		            success: function(msg){
		                if(msg['delete']=='1'){
		                    swal({
		                      title: "Terhapus!",
		                      text: "Data berhasil dihapus",
		                      type: "success",
		                      timer: 1500,
		                      showConfirmButton: false
		                    });
		                    window.location.reload();
		                } else {
		                    swal({
		                      title: "Gagal!",
		                      text: "Data gagal dihapus",
		                      type: "error",
		                      timer: 1500,
		                      showConfirmButton: false
		                    });
		                };
		            },
		            error: function(){
		                swal({
	                      title: "Gagal!",
	                      text: "Gagal Eksekusi Ajax",
	                      type: "error",
	                      timer: 1500,
	                      showConfirmButton: false
	                    });
		            }
		        });
		  } else {
		    //cancel();
		  }
		});
	}
</script>
