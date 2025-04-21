<form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data"> 
<div class="box box-primary" style='margin-right: 17px;'>

	<div class="box-body">
		<?php
		if(empty($results)){
		}else{
		$no = 0;
		foreach($results AS $val => $valx){ $no++;

			$sql_supplier	= "SELECT a.* FROM master_supplier a where id_supplier='".$valx['id_supplier']."' LIMIT 1";
			$query = $this->db->query($sql_supplier)->result();
			$data_supplier 	= (empty($query))?false:$query[0];
			$sel_local 	= ($data_supplier->supplier_location == 'local')?'selected':'';
			$sel_import = ($data_supplier->supplier_location != 'local')?'selected':'';
			
			if(!empty($valx['lokasi'])){
				$sel_local 	= ($valx['lokasi'] == 'local')?'selected':'';
				$sel_import = ($valx['lokasi'] == 'import')?'selected':'';
			}
			$alamatSUP = (!empty($valx['alamat_supplier']))?$valx['alamat_supplier']:$data_supplier->address_office;

			$nopo = $valx['no_po'];

			$query 	= "	SELECT 
							a.*
							FROM 
							tran_material_po_detail a 
						WHERE 
							a.no_po='".$valx['no_po']."'
						";
			$res 	= $this->db->query($query)->result_array();

			$query_top 	= "	SELECT 
							a.*
							FROM 
							billing_top a 
						WHERE 
							a.no_ipp='$nopo'
						";
			$res_top 	= $this->db->query($query_top)->result_array();

			?>
			<div class='form-group row'>
				<label for="tgl_po" class="col-sm-2 control-label">NO PO</label>
				<div class='col-sm-4'>
				<input type='text' name='no_po' class='form-control input-sm' value='<?=$nopo;?>' readonly>	
				<input type='hidden' name='kategori' value='<?=$valx['kategori'];?>'>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
				  <div class="box box-default">
					<div class="box-header">
					  <h3 class="box-title">Supplier</h3>
					  <div class="box-tools pull-right">
						<button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
					  </div>
					</div>
					<!-- /.box-header -->
					<div class="box-body">
						<div class='form-group row'>	
							<label class="col-sm-2 control-label">Nama</label>			
							<div class='col-sm-3'>
							<input type='text' name='supplier' class='form-control input-sm' value='<?=$valx['nm_supplier'];?>' readonly>	
							</div>
							<div class='col-sm-2'>
								<select id='lokasi_<?=$no;?>' name='Header[<?=$no;?>][lokasi]' class='form-control input-sm chosen-select' readonly>
									<option value='local' <?=$sel_local;?>>LOCAL</option>
									<option value='import' <?=$sel_import;?>>IMPORT</option>
								</select>
							</div>
							<div class='col-sm-5'>
								<textarea id='alamat_<?=$no;?>' class='form-control input-md' name='Header[<?=$no;?>][alamat]' rows='3' placeholder='Supplier Address' readonly><?=strtoupper($alamatSUP);?></textarea>
								<input type='hidden' name='Header[<?=$no;?>][id]' class='form-control input-sm' value='<?=$valx['id'];?>'>
							</div>
						</div>
					</div>
				  </div>

				  <div class="box box-default">
					<div class="box-header">
					  <h3 class="box-title">Info PO</h3>
					  <div class="box-tools pull-right">
						<button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
					  </div>
					</div>
					<div class="box-body">
						<div class='form-group row'>	
							<label class="col-sm-2 control-label">Incoterms</label>
							<div class='col-sm-3'>
							<input type='text' name='Header[<?=$no;?>][incoterms]' class='form-control input-sm' value='<?=$valx['incoterms']?>'>	
							</div>
							<label class="col-sm-2 control-label">Remarks</label>
							<div class='col-sm-5'>
								<textarea id='remarks<?=$no;?>' class='form-control input-md' name='Header[<?=$no;?>][remarks]' rows='3' placeholder='remarks'><?=$valx['remarks']?></textarea>
							</div>
						</div>
						<div class='form-group row'>	
							<label class="col-sm-2 control-label">Tax</label>
							<div class='col-sm-3'>
								<input type='text' name='Header[<?=$no;?>][tax]' class='form-control input-sm' value='<?=$valx['tax']?>'>	
							</div>
							<label class="col-sm-2 control-label">Request Date</label>
							<div class='col-sm-5'>
								<input type='text' name='Header[<?=$no;?>][request_date]' value='<?=$valx['request_date']?>' class='form-control input-sm'>	
							</div>
						</div>
					</div>
				  </div>
				</div>
			</div>
			<table id="my-grid" class="table table-striped table-bordered table-hover table-condensed" width="100%">
				<thead id='head_table'>
					<tr class='bg-blue'>
						<th class="text-center" width='5%'>Term</th>
						<th class="text-center" width='10%'>Progress (%)</th>
						<th class="text-center" width='25%'>Value</th>
						<th class="text-center" width='50%'>Keterangan</th>
						<th class="text-center" width='5%'>#</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$id = 0;
					if(!empty($res_top)){
						foreach($res_top AS $val => $valx){ $id++;
echo "<tr>";
	echo "<td align='left'><input type='text' id='term_".$id."' name='detail_top_term[]' class='form-control text-center input-md' value='".$valx['term']."'></td>";
	echo "<td align='left'><input type='text' id='progress_".$id."' name='detail_top_progress[]' value='".$valx['progress']."' class='form-control input-md text-center maskM progress_term'></td>";
	echo "<td align='left'><input type='text' id='value_".$id."' name='detail_top_value[]' value='".number_format($valx['value'])."' class='form-control input-md text-right maskM sum_tot_idr' readonly></td>";
	echo "<td align='left'><input type='text' id='syarat_".$id."' name='detail_top_syarat[]' value='".($valx['syarat'])."' class='form-control input-md'></td>";
	echo "<td align='center'>";
	echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
	echo "</td>";
echo "</tr>";
						}
					}
					?>
				</tbody>
				<tfoot>
				<tr id='add_<?=$id;?>'>
					<td align='left'><button type='button' class='btn btn-sm btn-warning addPart' title='Add TOP'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add TOP</button></td>
					<td align='center' colspan='4'></td>
				</tr>
				</tfoot>
			</table>
			<div class='form-group row'>		 	 
				<div class='col-sm-12'>
					<table class="table table-bordered table-striped" width='100%'>
						<thead>
							<tr class='bg-blue'>
								<th class="text-center" width='50%'>Nama Aset</th>
								<th class="text-center" width='20%'>Price From Supplier</th>
								<th class="text-center" width='10%'>Qty Purchase </th>
								<th class="text-center" width='20%'>Total </th>
							</tr>
						</thead>
					<tbody>
						<?php
						$no2 = 0;
						foreach($res AS $val2 => $valx2){ $no2++;
						
						    $subtotal = $valx2['qty_purchase'] * $valx2['price_ref_sup'];
							echo "<tr>";
								echo "<td><input type='hidden' name='Detail[".$no."][detail][".$no2."][id]' class='form-control text-right input-sm maskM' value='".$valx2['id']."'>
								".$valx2['nm_material']."</td>";
								echo "<td align='right'><input type='text' name='Detail[".$no."][detail][".$no2."][price_ref_sup]' class='form-control text-right input-sm maskM' value='".$valx2['price_ref_sup']."' readonly>
								<input type='hidden' id='harga_".$no2."' name='Detail[".$no."][detail][".$no2."][price_ref_sup2]' class='form-control text-right input-sm' value='".$valx2['price_ref_sup']."' readonly></td>";
                                echo "<td align='right'><input type='text' id='qty_".$no2."' name='Detail[".$no."][detail][".$no2."][qty_purchase]' class='form-control text-right input-sm' value='".$valx2['qty_purchase']."' onkeyup='qty(".$no2.")'></td>";
								echo "<td align='right'><input type='text' id='hargatotal_".$no2."' name='Detail[".$no."][detail][".$no2."][total]' class='form-control text-right input-sm maskM' value='".$subtotal."' readonly></td>";
							echo "</tr>";
						}
						?>
					</tbody>
				</table>
				</div>
			</div>
		<?php } 
		}?>
		<?php
			echo form_button(array('type'=>'button','class'=>'btn btn-sm btn-danger','style'=>'min-width:100px; float:right; margin: 5px 0px 5px 0px;','content'=>'Back','id'=>'back')).' ';
			echo form_button(array('type'=>'button','class'=>'btn btn-sm btn-success','style'=>'min-width:100px; float:right; margin: 5px 5px 5px 0px;','value'=>'Create','content'=>'Save','id'=>'save')).' ';
		?>
	</div>
 </div>
  <!-- /.box -->

</form>

<style>
	.chosen-container-active .chosen-single {
	     border: none;
	     box-shadow: none;
	}
	.chosen-container-single .chosen-single {
		height: 34px;
	    border: 1px solid #d2d6de;
	    border-radius: 0px;
	     background: none;
	    box-shadow: none;
	    color: #444;
	    line-height: 32px;
	}
	.chosen-container-single .chosen-single div{
		top: 5px;
	}
</style>
<script>
	var idt=<?=$id?>;
	$(document).on('click', '.delPart', function(){
	   $(this).closest("tr").remove();
	});

	$(document).on('click', '.addPart', function(){
		$('#my-grid tr:last').after("<tr><td align='left'><input type='text' id='term_"+idt+"' name='detail_top_term[]' class='form-control text-center input-md' value=''></td><td align='left'><input type='text' id='progress_term_"+idt+"' name='detail_top_progress[]' value='0' class='form-control input-md text-center maskM progress_term'></td><td align='left'><input type='text' id='value_"+idt+"' name='detail_top_value[]' value='0' class='form-control input-md text-right maskM sum_tot_idr' readonly></td><td align='left'><input type='text' id='syarat_"+idt+"' name='detail_top_syarat[]' value='' class='form-control input-md'></td><td align='center'>&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button></td></tr>");
	})
    $(document).ready(function() {
//      $(".maskM").divide();
    });

	$(document).on('click', '#back', function(e){
		window.location.href = siteurl+"pr_selection/purchase_order";
	});
	$(document).on('click', '#save', function(e){
		e.preventDefault();

		swal({ 
			title: "Are you sure?",
			text: "You will save be able to process again this data!",
			type: "warning",
			showCancelButton: true,
			confirmButtonClass: "btn-danger",
			confirmButtonText: "Yes, Process it!",
			cancelButtonText: "No, cancel process!",
			closeOnConfirm: false,
			closeOnCancel: false
		},
		function(isConfirm) {
			if (isConfirm) {
				var formData  	= new FormData($('#form_proses_bro')[0]);
				$.ajax({
					url: siteurl+"pr_selection/save_edit_po",
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
							window.location.href = siteurl+"pr_selection/purchase_order";
						}
						else if(data.status == 0){
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
						console.log(data);
					},
					error: function(data) {
						console.log(data);
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
			swal("Cancelled", "Data can be process again", "error");
			return false;
			}
		});
	});
	$(function () {
		// Daterange Picker
		$(".date").datepicker({
			todayHighlight: true,
			format : "yyyy-mm-dd",
			showInputs: true,
			autoclose:true
		});
	});

	function qty(no){		
		var harga = parseInt($('#harga_'+no).val());
        var qty   = parseInt($('#qty_'+no).val());
        var total = Math.round(harga * qty);
		console.log(harga)
		$('#hargatotal_'+no).val(total);
	}

</script>
