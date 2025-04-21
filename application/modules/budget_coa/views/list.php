<?php
    $ENABLE_ADD     = has_permission('Budget.Add');
    $ENABLE_MANAGE  = has_permission('Budget.Manage');
    $ENABLE_VIEW    = has_permission('Budget.View');
    $ENABLE_DELETE  = has_permission('Budget.Delete');
?>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
<div class="box">
	<div class="box-header">
		<form method="get" action="">
			<div class="row">
				<div class="col-md-4">
					<div class="form-group">
						<label class="col-sm-4 control-label">Tahun</label>
						<div class="col-sm-4">
							<div class="input-group">
								<select name="tahun" id="tahun" class="form-control" required="required">
								<?php
								foreach($listtahun as $val){
									echo '<option value="'.$val.'" '.($val==$tahun?' selected ':'').'>'.$val.'</option>';
								}
								?>
								</select>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-2">
					<input type="submit" class="btn btn-info" value="Lihat" />
				</div>
<!--
				<div class="col-md-1">
					<?php if ($ENABLE_MANAGE) : ?>
						<a class="btn bg-purple hidden" href="javascript:void(0)" title="Approve" id="btn_approve" onclick="approve_data()"> Approve</a>
					<?php endif; ?>
				</div>
-->
				<div class="col-md-1">
					<a class="btn btn-default hidden" href="javascript:void(0)" title="Detail" id="btn_detail" onclick="detail_data()"> Detail</a>
				</div>
				<div class="col-md-1">
					<?php if ($ENABLE_MANAGE) : ?>
						<a class="btn btn-warning hidden btn-block" href="javascript:void(0)" title="Edit" id="btn_edit" onclick="edit_data()"> Edit</a>
					<?php endif; ?>
				</div>
				<div class="col-md-1">
					<?php if ($ENABLE_DELETE) : ?>
						<a class="btn btn-danger hidden" href="javascript:void(0)" title="Edit" id="btn_delete" onclick="delete_data()"> Delete</a>
					<?php endif; ?>
				</div>
				<div class="col-md-1">
					<?php if ($ENABLE_ADD) : ?>
						<a class="btn btn-success" href="javascript:void(0)" title="Add" onclick="add_data()">New</a>
					<?php endif; ?>
				</div>
			</div>

		</form>
	</div>
	<!-- /.box-header -->
	<div class="box-body table-responsive">
		<table id="mytabledata" class="table table-bordered table-striped">
		<thead>
		<tr>
			<th>ID</th>
			<th>Tahun</th>
			<th>COA</th>
			<th>Definisi</th>
			<th>Penganggung Jawab</th>
			<th>Kategori</th>
			<th>Formulasi Budget</th>
<!--
			<th>Budget/Bulan</th>
			<th>Budget/Tahun</th>
-->
		</tr>
		</thead>
		<tbody>
		<?php 
		$edit='';
		if(empty($results)){
			$edit='';
		}else{
			$numb=0;
			foreach($results AS $record) {
				$numb++; ?>
		<tr>
		    <td><?= $numb ?></td>
			<td><?= $record->tahun ?></td>
			<td><?= $record->no_perkiraan?> | <?= $record->nama_perkiraan ?></td>
			<td><?= $record->definisi ?></td>
			<td><?= $record->nm_dept ?></td>
			<td><?= $record->kategori ?></td>
			<td><?= $record->info ?></td>
<!--
			<td><?= number_format($record->finance_bulan) ?></td>
			<td><?= number_format($record->finance_tahun) ?></td>
-->
		</tr>
		<?php
				if($record->status==0) {
					$edit=$record->tahun;
				}
			}
		}  ?>
		</tbody>
		</table>
	</div>
	<div class="box-footer"><h1>Laporan Budget</h1>
		<form method="post" action="<?=base_url('budget_coa/detail_bulan');?>">
			<div class="row">
				<div class="col-md-6 col-xs-12">
					<div class="form-group">
						<label class="col-sm-2 control-label">Bulan</label>
						<div class="col-sm-3">
							<div class="input-group">
								<select name="bulan" id="bulan" class="form-control" required="required">
								<?php
								for($i=1; $i<=12; $i++) {
									echo '<option value="'.$i.'">'.date('M', strtotime('2020-'.$i.'-01')).'</option>';  
								}
								?>
								</select>
							</div>
						</div>
						<label class="col-sm-2 control-label">Tahun</label>
						<div class="col-sm-3">
							<div class="input-group">
								<select name="tahun" id="tahun" class="form-control" required="required">
								<?php
								foreach($listtahun as $val){
									echo '<option value="'.$val.'" '.($val==$tahun?' selected ':'').'>'.$val.'</option>';
								}
								?>
								</select>
							</div>
						</div>
						<div class="col-sm-2">
							<button type="submit" class="btn btn-info">Lihat</button>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
<!-- /.box-body -->
<div id="form-data"></div>
<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>
<script src="https://cdn.datatables.net/buttons/1.6.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.html5.min.js"></script>
<!-- page script -->
<script type="text/javascript">
    $(document).ready(function() {
		<?php if($edit!='') {
			if ($ENABLE_MANAGE) {?>
			$("#btn_edit").removeClass("hidden");
			$("#btn_approve").removeClass("hidden");
			$("#btn_detail").removeClass("hidden");
		<?php }
			if ($ENABLE_DELETE) {?>
			$("#btn_delete").removeClass("hidden");
		<?php }
		} ?>
    });

  	$(function() {
    	$("#mytabledata").DataTable({
			"paging":   true,
			dom: 'lBfrtip',
			buttons: [{
                extend: 'excel',
                exportOptions: {
                    columns: [ 1,2,3,4,5 ]
                }
            }]
		});
    	$("#form-data").hide();
  	});
  	function detail_data(){
		var url = 'budget_coa/detail/<?=$edit?>';
		$(".box").hide();
		$("#form-data").show();
		$("#form-data").load(siteurl+url);
	}


  	function add_data(){
		var url = 'budget_coa/create';
		$(".box").hide();
		$("#form-data").show();
		$("#form-data").load(siteurl+url);
	}

  	function edit_data(){
		var url = 'budget_coa/edit/<?=$edit?>';
		$(".box").hide();
		$("#form-data").show();
		$("#form-data").load(siteurl+url);
		$("#title").focus();
	}

	//Delete
	function delete_data(){
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
		            url: siteurl+'budget_coa/hapus_data/<?=$edit?>',
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
						console.log(msg)
		            },
		            error: function(msg){
		                swal({
	                      title: "Gagal!",
	                      text: "Gagal Eksekusi Ajax",
	                      type: "error",
	                      timer: 1500,
	                      showConfirmButton: false
	                    });
						console.log(msg)
		            }
		        });
		  } else {
		    //cancel();
		  }
		});
	}
	//Approve
	function approve_data(){
		swal({
		  title: "Anda Yakin?",
		  text: "Data Akan Di Approve!",
		  type: "warning",
		  showCancelButton: true,
		  confirmButtonColor: "#DD6B55",
		  confirmButtonText: "Ya, Approve!",
		  cancelButtonText: "Tidak!",
		  closeOnConfirm: false,
		  closeOnCancel: true
		},
		function(isConfirm){
		  if (isConfirm) {
		  	$.ajax({
		            url: siteurl+'budget_coa/approve_data/<?=$edit?>',
		            dataType : "json",
		            type: 'POST',
		            success: function(msg){
		                if(msg['delete']=='1'){
		                    swal({
		                      title: "Diapprove!",
		                      text: "Data berhasil Approve",
		                      type: "success",
		                      timer: 1500,
		                      showConfirmButton: false
		                    });
		                    window.location.reload();
		                } else {
		                    swal({
		                      title: "Gagal!",
		                      text: "Data gagal di approve",
		                      type: "error",
		                      timer: 1500,
		                      showConfirmButton: false
		                    });
		                };
						console.log(msg)
		            },
		            error: function(msg){
		                swal({
	                      title: "Gagal!",
	                      text: "Gagal Eksekusi Ajax",
	                      type: "error",
	                      timer: 1500,
	                      showConfirmButton: false
	                    });
						console.log(msg)
		            }
		        });
		  } else {
		    //cancel();
		  }
		});
	}

</script>
