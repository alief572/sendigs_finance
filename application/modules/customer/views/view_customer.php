<?php
    $ENABLE_ADD     = has_permission('Customer.Add');
    $ENABLE_MANAGE  = has_permission('Customer.Manage');
    $ENABLE_VIEW    = has_permission('Customer.View');
    $ENABLE_DELETE  = has_permission('Customer.Delete');

?>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">

<div class="box box-solid">	
	<!-- /.box-header -->
	<div class="box-body">
		<div class="box-body">	
			<div class="row">
			  <div class="col-md-12">
				<legend><h3 style="margin:0px !Important"><label><?= $result['nm_customer']?></label></h3></legend>
				<table class="table-responsive table-condensed" width="100%" style="line-height:10px">
					<tbody>
						<tr>
							<td width='15%'><label>Customer ID</label></td>
							<td width='35%'>: <?= $result['id_customer']?></td>
							<td width='15%'><label>Status</label></td>
							<td width='35%'>: <label class="label label-success"><?= strtoupper($result['aktif'])?></label></td>
						</tr>
						<tr>
							<td><label>Telephone</label></td>
							<td>: <?= $result['telepon']?></td>
							<td><label>City</label></td>
							<?php $city = $this->db->get_where('kota',array('id_kota' => $result['kota'] ))->row_array() ?>
							<td>: <?= $city['nama_kota']?>, <?= $result['kode_pos']?></td>
						</tr>
						<tr>
							<td><label>Fax.</label></td>
							<td>: <?= $result['fax']?></td>
							<td><label>Provinsi</label></td>
							<?php $prov = $this->db->get_where('provinsi',array('id_prov' => $result['provinsi'] ))->row_array() ?>
							<td>: <?= $prov['nama']?></td>
							
						</tr>
						<tr>
							<td><label>E-mail</label></td>
							<td>: <a href="mailto:<?= $result['email']?>"><?= $result['email']?><sup><i class="fa fa-envelope-o"></i></sup></a></td>
							<td><label>Product</label></td>
							<td>: <?= $result['produk']?></td>
						</tr>
						<tr>
							<td><label>Website</label></td>
							<td>: <a href="http://<?= $result['website']?>"> <?= $result['website']?> <sup><i class="fa fa-external-link"></i></sup></a></td>
							<td><label>Marketing</label></td>
							<td>: <?= $result['marketing']?></td>
						</tr>
					</tbody>
				</table>
				<br>
				<table class="" width="100%">
					<tr>
						<td width='15%'><label>PIC Name</label></td>
						<td width='%'>: <?= $result['pic_name']?></td>
						<td width='15%'><label>Divisi</label></td>
						<td width='%'>: <?= $result['pic_divisi']?></td>
					</tr>
					<tr>
						<td width='15%'><label>E-mail</label></td>
						<td>: <a href="mailto:<?= $result['pic_email']?>"><?= $result['pic_email']?><sup><i class="fa fa-envelope-o"></i></sup></a></td>
						<td><label>Handphone</label></td>
						<td>: <?= $result['pic_hp']?></td>
					</tr>
					<tr>
						<td><label>Position</label></td>
						<td>: <?= $result['pic_jabatan']?></td>
						<td></td>
						<td></td>
					</tr>
				</table>
				<br>
				<table class="table">
					<tr>
						<td width="40%"><label>Office Address </label></td>
						<td width="20%"><label>NPWP Number</label></td>
						<td ><label>NPWP Address</label></td>
					</tr>
					<tr>
						<td><?= $result['alamat']?></td>
						<td><?= $result['npwp'] == '' ? '-':$result['npwp'];?></td>
						<td><?= $result['npwp_address'] == '' ? '-':$result['alamat_npwp'];?></td>
					</tr>
				</table>
				<br>
				<legend><label>Note</label></legend>
				<table>
					<tr>
						<td><?= $result['note'] == '' ? '-':$result['note'];?></td>
					</tr>
				</table>
			  <div>
			</div>
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
