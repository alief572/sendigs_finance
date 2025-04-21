<?php
    $ENABLE_ADD     = has_permission('Inventory_5.Add');
    $ENABLE_MANAGE  = has_permission('Inventory_5.Manage');
    $ENABLE_VIEW    = has_permission('Inventory_5.View');
    $ENABLE_DELETE  = has_permission('Inventory_5.Delete');
foreach ($results as $record){
}
	
?>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">

<div class="box box-solid">	
	<!-- /.box-header -->
	<div class="box-body">
		<div class="box-body">	
	<table align = "center">
	<tr>
		<td><label>Id Inventory</label></td>
		<td><label>:</label></td>
		<td><label><?= $record->id_material ?></label></td>
		<td width="75px"></td>
		<td><label>Nama Inventory</label></td>
		<td><label>:</label></td>
		<td><label><?= $record->nama ?></label></td>
	</tr>
	<tr>
		<td><label>Type Inventory</label></td>
		<td><label>:</label></td>
		<td><label><?= $record->nama_type ?></label></td>
		<td width="75px"></td>
		<td><label>Category I</label></td>
		<td><label>:</label></td>
		<td><label><?= $record->nama_category1 ?></label></td>
	</tr>

	<tr>
		<td><label>Category II</label></td>
		<td><label>:</label></td>
		<td><label><?= $record->nama_category2 ?></label></td>
		<td width="75px"></td>
		<td><label>Category III</label></td>
		<td><label>:</label></td>
		<td><label><?= $record->nama_category3 ?></label></td>
	</tr>
	<tr>
	<td colspan = "7">
	<td>
	</tr>
	<tr>
	<td colspan="7">
	<table align = "center">
	<tr>
		<td><label>Nama Umum</label></td>
		<td><label>:</label></td>
		<td><label><?= $record->spec1 ?></label></td>
		<td width="75px"></td>
		<td><label>Brand</label></td>
		<td><label>:</label></td>
		<td><label><?= $record->spec2 ?></label></td>
		<td width="75px"></td>
		<td><label>Material Sejenis</label></td>
		<td><label>:</label></td>
		<td><label><?= $record->spec3 ?></label></td>
	</tr>
	<tr>
		<td><label>Hardnes</label></td>
		<td><label>:</label></td>
		<td><label><?= $record->spec4 ?></label></td>
		<td width="75px"></td>
		<td><label>Thicknes</label></td>
		<td><label>:</label></td>
		<td><label><?= $record->spec5 ?></label></td>
		<td width="75px"></td>
		<td><label>Length</label></td>
		<td><label>:</label></td>
		<td><label><?= $record->spec6 ?></label></td>
	</tr>

	<tr>
		<td><label>WIdth</label></td>
		<td><label>:</label></td>
		<td><label><?= $record->spec7 ?></label></td>
		<td width="75px"></td>
		<td><label>Dencity</label></td>
		<td><label>:</label></td>
		<td><label><?= $record->spec8 ?></label></td>
		<td width="75px"></td>
		<td><label>Satuan</label></td>
		<td><label>:</label></td>
		<td><label><?= $record->spec9 ?></label></td>
	</tr>
	
	<tr>
		<td><label>Alt Suplier</label></td>
		<td><label>:</label></td>
		<td><label><?= $record->spec10 ?></label></td>
		<td width="75px"></td>
		<td><label>Safety Stock</label></td>
		<td><label>:</label></td>
		<td><label><?= $record->spec11 ?></label></td>
		<td width="75px"></td>
		<td><label>Order Point</label></td>
		<td><label>:</label></td>
		<td><label><?= $record->spec12 ?></label></td>
	</tr>
	
	<tr>
		<td><label>Maximum Stock</label></td>
		<td><label>:</label></td>
		<td><label><?= $record->spec13 ?></label></td>
		<td width="75px"></td>
		<td><label>Lead Time</label></td>
		<td><label>:</label></td>
		<td><label><?= $record->spec14 ?></label></td>
		<td width="75px"></td>
		<td><label>Last Spesification</label></td>
		<td><label>:</label></td>
		<td><label><?= $record->spec15 ?></label></td>
	</tr>

	</table>
	</td>
	</tr>
	</table>

	
		</div>
	</div>
	<!-- /.box-body -->
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>

<!-- page script -->
<script type="text/javascript">

  	$(function() {
    	$("#example1").DataTable();
    	$("#form-area").hide();
  	});

  	function add_data(){
		var url = 'customer/create/';
		$(".box").hide();
		$("#form-area").show();
		$("#form-area").load(siteurl+url);
		$("#title").focus();
	}

  	function edit_data(id_customer){
		if(id_customer!=""){
			var url = 'customer/edit/'+id_customer;
			$(".box").hide();
			$("#form-area").show();
			$("#form-area").load(siteurl+url);
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
		            url: siteurl+'customer/hapus_customer/'+id,
		            dataType : "json",
		            type: 'POST',
		            success: function(msg){
		                if(msg['delete']=='1'){
		                    $("#dataku"+id).hide(2000);
		                    //swal("Terhapus!", "Data berhasil dihapus.", "success");
		                    swal({
		                      title: "Terhapus!",
		                      text: "Data berhasil dihapus",
		                      type: "success",
		                      timer: 1500,
		                      showConfirmButton: false
		                    });
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

	function PreviewPdf(id)
	{
		param=id;
		tujuan = 'customer/print_request/'+param;

	   	$(".modal-body").html('<iframe src="'+tujuan+'" frameborder="no" width="570" height="400"></iframe>');
	}

</script>
