<?php
	$tanggal = date('Y-m-d');
	$id 	= $this->uri->segment(3);
	$list	= $this->db->query("SELECT * FROM dt_spkmarketing WHERE id_dt_spkmarketing = '$id' ")->result();
?>

 <div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post">
			<div class="col-sm-12">
				<div class="input_fields_wrap2">
			<div class="row">
		<center><label for="customer" ><h3>Purchase Request</h3></label></center>
		<div class="col-sm-12">
		<div class="col-sm-6">
		<div class="form-group row">
			<div class="col-md-4">
				<label for="customer">NO.PR</label>
			</div>
			<div class="col-md-8" hidden>
				<input type="text" class="form-control" id="no_pr"  required name="no_pr" readonly placeholder="ID PR">
			</div>
			<div class="col-md-8">
				<input type="text" class="form-control" id="no_surat"  required name="no_surat" readonly placeholder="No.PR">
			</div>
		</div>
		</div>
		</div>
		<div class="col-sm-12">
		<div class="col-sm-6">
		<div class="form-group row">
			<div class="col-md-4">
				<label for="customer">Tanggal PR</label>
			</div>
			<div class="col-md-8">
				<input type="text" class="form-control datepicker" id="tanggal" value="<?= $tanggal ?>" onkeyup required name="tanggal" readonly >
			</div>
		</div>
		</div>
		</div>
		<div class="col-sm-12">
		<div class="col-sm-6">
			<div class="form-group row">
				<div class="col-md-4">
					<label for="id_customer">Requestor</label>
				</div>
				<div class="col-md-8">
					<input type="text" class="form-control" id="requestor" required name="requestor"  >
				</div>
			</div>
		</div>
		</div>
		<div class="col-sm-12">
				<div class="form-group row">
		<button type='button' class='btn btn-sm btn-success' title='Ambil' id='tbh_ata' data-role='qtip' onClick='addmaterial();'><i class='fa fa-plus'></i>Add</button>

		</div>
		<div class="form-group row" >
			<table class='table table-bordered table-striped'>
			<thead>
			<tr class='bg-blue'>
				<th>Material</th>
				<th width='8%'>Bentuk</th>
				<th width='8%'>ID</th>
				<th width='8%'>OD</th>
				<th hidden>Qty (Unit)</th>
				<th hidden>Weight (Unit)</th>
				<th width='8%'>Total Weight</th>
				<th width='8%'>Width</th>
				<th hidden>Width</th>
				<th hidden>Length</th>
				<th width='15%'>Supplier</th>
				<th width='10%'>Tanggal Dibutuhkan</th>
				<th width='10%'>Keterangan</th>
				<th width='5%'>Aksi</th>
			</tr>
			</thead>
			<tbody id="data_request">
			<?php $loop = 0;
			foreach($list as $list){
			 $loop++;
			$material = $this->db->query("SELECT a.*, b.nama as alloy, c.nm_bentuk as bentuk FROM ms_inventory_category3 as a INNER JOIN ms_inventory_category2 as b ON a.id_category2 = b.id_category2 INNER JOIN ms_bentuk as c ON a.id_bentuk = c.id_bentuk WHERE a.deleted = '0'  ")->result();
			$mat = $this->db->query("SELECT a.*, b.nm_bentuk as nama_bentuk FROM  ms_inventory_category3 as a inner join ms_bentuk as b on a.id_bentuk = b.id_bentuk WHERE id_category3 = '$list->id_material' ")->result();
			$bentuk= $mat[0]->nama_bentuk;
			$id_bentuk= $mat[0]->id_bentuk;;
		echo "
		<tr id='tr_$loop'>
		<td><select class='form-control select2' id='dt_idmaterial_$loop' name='dt[$loop][idmaterial]'	onchange ='CariProperties($loop)'>
		<option value=''>--Pilih--</option>";
		foreach ($material as $material){
		$select = $list->id_material == $material->id_category3 ? 'selected' : '';
		if($material->id_bentuk == 'B2000001' ){
		$dimensi = $this->db->query("SELECT * FROM child_inven_dimensi WHERE id_category3 = '$material->id_category3' AND id_dimensi = '22' ")->result();
		$thickness = $dimensi[0]->nilai_dimensi;
		echo"<option $select value='$material->id_category3'>$material->bentuk $material->alloy $material->nama $material->hardness $thickness</option>";
		}elseif($material->id_bentuk == 'B2000002' ){
		$dimensi = $this->db->query("SELECT * FROM child_inven_dimensi WHERE id_category3 = '$material->id_category3' AND id_dimensi = '25' ")->result();
		$thickness = $dimensi[0]->nilai_dimensi;
		echo"<option $select value='$material->id_category3'>$material->bentuk $material->alloy $material->nama $material->hardness $thickness</option>";
		}elseif($material->id_bentuk == 'B2000003' ){
		$dimensi = $this->db->query("SELECT * FROM child_inven_dimensi WHERE id_category3 = '$material->id_category3' AND id_dimensi = '30' ")->result();
		$thickness = $dimensi[0]->nilai_dimensi;
		echo"<option $select value='$material->id_category3'>$material->bentuk $material->alloy $material->nama $material->hardness $thickness</option>";
		}elseif($material->id_bentuk == 'B2000004' ){
		$dimensi = $this->db->query("SELECT * FROM child_inven_dimensi WHERE id_category3 = '$material->id_category3' AND id_dimensi = '8' ")->result();
		$thickness = $dimensi[0]->nilai_dimensi;
		$dimensi2 = $this->db->query("SELECT * FROM child_inven_dimensi WHERE id_category3 = '$material->id_category3' AND id_dimensi = '9' ")->result();
		$thickness2 = $dimensi[0]->nilai_dimensi;
		echo"<option $select value='$material->id_category3'>$material->bentuk $material->alloy $material->nama $material->hardness $thickness x $thickness</option>";
		}elseif($material->id_bentuk == 'B2000005' ){
		$dimensi = $this->db->query("SELECT * FROM child_inven_dimensi WHERE id_category3 = '$material->id_category3' AND id_dimensi = '11' ")->result();
		$thickness = $dimensi[0]->nilai_dimensi;
		echo"<option $select value='$material->id_category3'>$material->bentuk $material->alloy $material->nama $material->hardness $thickness</option>";
		}elseif($material->id_bentuk == 'B2000006' ){
		$dimensi = $this->db->query("SELECT * FROM child_inven_dimensi WHERE id_category3 = '$material->id_category3' AND id_dimensi = '13' ")->result();
		$thickness = $dimensi[0]->nilai_dimensi;
		echo"<option $select value='$material->id_category3'>$material->bentuk $material->alloy $material->nama $material->hardness $thickness</option>";
		}elseif($material->id_bentuk == 'B2000007' ){
		$dimensi = $this->db->query("SELECT * FROM child_inven_dimensi WHERE id_category3 = '$material->id_category3' AND id_dimensi = '15' ")->result();
		$thickness = $dimensi[0]->nilai_dimensi;
		$dimensi2 = $this->db->query("SELECT * FROM child_inven_dimensi WHERE id_category3 = '$material->id_category3' AND id_dimensi = '16' ")->result();
		$thickness2 = $dimensi[0]->nilai_dimensi;
		echo"<option $select value='$material->id_category3'>$material->bentuk $material->alloy $material->nama $material->hardness $thickness $thickness2</option>";
		}elseif($material->id_bentuk == 'B2000009' ){
		$dimensi = $this->db->query("SELECT * FROM child_inven_dimensi WHERE id_category3 = '$material->id_category3' AND id_dimensi = '19' ")->result();
		$thickness = $dimensi[0]->nilai_dimensi;
		echo"<option $select value='$material->id_category3'>$material->bentuk $material->alloy $material->nama $material->hardness $thickness</option>";
		};
		};
		echo"</select></td>
		<td id='bentuk_".$loop."'><input readonly type='text' value='".$bentuk."' class='form-control' id='dt_bentuk_$loop' required name='dt[$loop][bentuk]' ></td>
		<td id='idbentuk_".$loop."' hidden><input readonly type='text' value='".$id_bentuk."' class='form-control' id='dt_idbentuk_$loop' required name='dt[$loop][idbentuk]' ></td>
		<td>
		<select class='form-control select2' id='dt_idameter_$loop' name='dt[$loop][idameter]'>
		<option value='200'>200</option>
		<option value='250'>250</option>
		<option value='300'>300</option>
		<option value='400'>400</option>
		<option value='500'>500</option>
		</select>
		</td>
		<td><input type='text' class='form-control autoNumeric text-right' id='dt_odameter_$loop' 			required name='dt[$loop][odameter]' 	></td>
		<td hidden><input type='number' class='form-control' id='dt_qty_$loop' 			required name='dt[$loop][qty]' 		onkeyup='HitungTweight(".$loop.")'></td>
		<td hidden><input type='number' class='form-control' id='dt_weight_$loop' 			required name='dt[$loop][weight]' 	onkeyup='HitungTweight(".$loop.")'></td>
		<td id='HasilTwight_".$loop."'><input type='text' class='form-control autoNumeric text-right' id='dt_totalweight_$loop' 	required name='dt[$loop][totalweight]' ></td>
		<td ><input type='text' class='form-control autoNumeric text-right' id='dt_width_$loop' 	required name='dt[$loop][width]' ></td>
		<td hidden><input type='number' class='form-control' id='dt_length_$loop' 	required name='dt[$loop][length]' ></td>
				<td id='supplier_".$loop."'><select class='form-control select2' id='dt_suplier_$loop' name='dt[$loop][suplier]'>
		<option value=''>Pilih</option>";
		$supplier	= $this->db->query("SELECT a.*, b.name_suplier as supname FROM child_inven_suplier as a INNER JOIN master_supplier as b on a.id_suplier = b.id_suplier WHERE a.id_category3 = '".$list->id_material."' ")->result();
			foreach ($supplier as $supplier){};
					echo"<option value='".$supplier->id_suplier ."'>".$supplier->supname ."</option>";
		echo"</select></td>
		<td><input type='text' class='form-control datepicker' readonly id='dt_tanggal_$loop' 	required name='dt[$loop][tanggal]' 	></td>
		<td><input type='text' class='form-control' id='dt_keterangan_$loop' 	required name='dt[$loop][keterangan]' 	></td>
		<td><button type='button' class='btn btn-sm btn-danger' title='Hapus Data' data-role='qtip' onClick='return HapusItem($loop);'><i class='fa fa-close'></i></button></td>
		</tr>
		";
			}?>
			</tbody>
			</table>
		</div>
			</div>
			<center>
		<button type="submit" class="btn btn-success btn-sm" name="save" id="simpan-com"><i class="fa fa-save"></i>Simpan</button>
			</center>
				 </div>
			</div>
		</form>		  
	</div>
</div>	
	
<style>
	.select2 {
		width:100%!important;
	}
	.datepicker{
		cursor: pointer;
	}
</style>			  
				  
				  
<script type="text/javascript">
	//$('#input-kendaraan').hide();
	var base_url			= '<?php echo base_url(); ?>';
	var active_controller	= '<?php echo($this->uri->segment(1)); ?>';
	$(document).ready(function(){	
			var max_fields2      = 10; //maximum input boxes allowed
			var wrapper2         = $(".input_fields_wrap2"); //Fields wrapper
			var add_button2      = $(".add_field_button2"); //Add button ID		

			$('.select2').select2();
				$('.autoNumeric').autoNumeric();
				$('.datepicker').datepicker({
					dateFormat: 'yy-mm-dd',
					changeMonth:true,
					changeYear:true,
				});	
	$('#simpan-com').click(function(e){
			e.preventDefault();
			var deskripsi	= $('#deskripsi').val();
			var image	= $('#image').val();
			var idtype	= $('#inventory_1').val();
			
			var data, xhr;
			swal({
				  title: "Are you sure?",
				  text: "You will not be able to process again this data!",
				  type: "warning",
				  showCancelButton: true,
				  confirmButtonClass: "btn-danger",
				  confirmButtonText: "Yes, Process it!",
				  cancelButtonText: "No, cancel process!",
				  closeOnConfirm: true,
				  closeOnCancel: false
				},
				function(isConfirm) {
				  if (isConfirm) {
						var formData 	=new FormData($('#data-form')[0]);
						var baseurl=siteurl+'purchase_request/SaveNew';
						$.ajax({
							url			: baseurl,
							type		: "POST",
							data		: formData,
							cache		: false,
							dataType	: 'json',
							processData	: false, 
							contentType	: false,				
							success		: function(data){								
								if(data.status == 1){											
									swal({
										  title	: "Save Success!",
										  text	: data.pesan,
										  type	: "success",
										  timer	: 7000,
										  showCancelButton	: false,
										  showConfirmButton	: false,
										  allowOutsideClick	: false
										});
									window.location.href = base_url + active_controller;
								}else{
									
									if(data.status == 2){
										swal({
										  title	: "Save Failed!",
										  text	: data.pesan,
										  type	: "warning",
										  timer	: 7000,
										  showCancelButton	: false,
										  showConfirmButton	: false,
										  allowOutsideClick	: false
										});
									}else{
										swal({
										  title	: "Save Failed!",
										  text	: data.pesan,
										  type	: "warning",
										  timer	: 7000,
										  showCancelButton	: false,
										  showConfirmButton	: false,
										  allowOutsideClick	: false
										});
									}
									
								}
							},
							error: function() {
								
								swal({
								  title				: "Error Message !",
								  text				: 'An Error Occured During Process. Please try again..',						
								  type				: "warning",								  
								  timer				: 7000,
								  showCancelButton	: false,
								  showConfirmButton	: false,
								  allowOutsideClick	: false
								});
							}
						});
				  } else {
					swal("Cancelled", "Data can be process again :)", "error");
					return false;
				  }
			});
		});
		
});
	function addmaterial(){ 
		var jumlah	=$('#data_request').find('tr').length;
		$.ajax({
            type:"GET",
            url:siteurl+'purchase_request/AddMaterial',
            data:"jumlah="+jumlah,
            success:function(html){
               $("#data_request").append(html);
			   $('.select2').select2();
				$('.autoNumeric').autoNumeric();
				$('.datepicker').datepicker({
					dateFormat: 'yy-mm-dd',
					changeMonth:true,
					changeYear:true,
				});
            }
        });
    }
		function HitungTweight(id){
        var dt_qty=$("#dt_qty_"+id).val();
		 var dt_weight=$("#dt_weight_"+id).val();
		 $.ajax({
            type:"GET",
            url:siteurl+'purchase_request/HitungTwight',
            data:"dt_weight="+dt_weight+"&dt_qty="+dt_qty+"&id="+id,
            success:function(html){
               $("#HasilTwight_"+id).html(html);
            }
        });
    }
	function CariProperties(id){
        var idmaterial=$("#dt_idmaterial_"+id).val();
		 $.ajax({
            type:"GET",
            url:siteurl+'purchase_request/CariBentuk',
            data:"idmaterial="+idmaterial+"&id="+id,
            success:function(html){
               $("#bentuk_"+id).html(html);
            }
        });
		$.ajax({
            type:"GET",
            url:siteurl+'purchase_request/CariIdBentuk',
            data:"idmaterial="+idmaterial+"&id="+id,
            success:function(html){
               $("#idbentuk_"+id).html(html);
            }
        });
		$.ajax({
            type:"GET",
            url:siteurl+'purchase_request/CariSupplier',
            data:"idmaterial="+idmaterial+"&id="+id,
            success:function(html){
               $("#supplier_"+id).html(html);
			   $('.select2').select2();
            }
        });
    }
function HapusItem(id){
		$('#data_request #tr_'+id).remove();
		
	}
	
	
	
</script>