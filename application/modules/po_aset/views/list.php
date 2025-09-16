<?php
    $ENABLE_ADD     = has_permission('PR_Asset.Add');
    $ENABLE_MANAGE  = has_permission('PR_Asset.Manage');
    $ENABLE_VIEW    = has_permission('PR_Asset.View');
    $ENABLE_DELETE  = has_permission('PR_Asset.Delete');
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
		<table id="mytabledata" class="table table-bordered table-striped">
		<thead>
		<tr>
			<th width="35">
			<?php if($ENABLE_MANAGE) : ?>
			Action
			<?php endif; ?>
			</th>
			<th>No PR</th>
			<th>Tanggal PR</th>
			<th>Description</th>
			<th>Nilai PR</th>
			<th>Status</th>
		</tr>
		</thead>

		<tbody>
		<?php if(empty($results)){
		}else{
			$numb=0; foreach($results AS $record){ $numb++; ?>
		<tr>
			<td style="padding-left:20px">
			<?php if($ENABLE_MANAGE) : ?>
				<a class="text-green" href="javascript:void(0)" title="View" onclick="edit_data('<?=$record->id?>')"><i class="fa fa-search"></i></a>
			<?php endif;?>
			</td>
			<td><?= $record->no_pr ?></td>
			<td><?= $record->tgl_pr?></td>
			<td><?= $record->description?></td>
			<td><?= number_format($record->nilai_pr)?></td>
			<td><?php
			if($record->status=='10') {
				echo 'Ditolak';
			}else{
				if($record->status=='1') {
					echo 'Menuggu Persetujuan';
				}else{
					if($record->tipe_pr!='PP'){
						echo 'Selesai '.$record->tipe_pr;
					}else{
						if($record->nilai_pr<=$record->terbayar) {
							echo 'Selesai '.$record->tipe_pr;
						}else{
							echo 'Belum selesai';
						}
					}
				}
			}
			?></td>
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

  	function add_data(){
		var url = 'po_aset/create/';
		$(".box").hide();
		$("#form-data").show();
		$("#form-data").load(siteurl+url);
		$("#title").focus();
	}

  	function edit_data(id){
		if(id!=""){
			var url = 'po_aset/edit/'+id;
			$(".box").hide();
			$("#form-data").show();
			$("#form-data").load(siteurl+url);
		    $("#title").focus();
		}
	}

</script>
