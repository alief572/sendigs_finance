<div class="box-body"> 
	<form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data"> 
	<input type='hidden' id='no_rfq' name='no_rfq' value='<?=$no_rfq;?>'>
	<?php 
	$sqlheader	= "SELECT * FROM tran_material_rfq_header a WHERE a.no_rfq='".$no_rfq."' LIMIT 1";
	$dataheader	= $this->db->query($sqlheader)->result();
	echo "<input type='hidden' name='kategori' value='".$dataheader[0]->category."'>";
	?>
	<table id="my-grid" class="table table-striped table-bordered table-hover table-condensed" width="100%">
		<thead id='head_table'>
			<tr class='bg-blue'>
				<th class="text-center mid" rowspan='2' width='17%'>MATERIAL NAME</th>
				<?php
				$wid = 70 / COUNT($supplier);
				$wind2 = $wid/3;
				foreach($supplier AS $val => $valx){
					echo "<th class='mid' width='".$wid."%' colspan='2'>".$valx['nm_supplier']."</th>";
				}
				?>
				<th class="text-center mid" width='10%'  rowspan='2'>KOMITE</th>
				<th class="text-center mid" width='10%'  rowspan='2'>SELECTION</th>
			</tr>
			<tr class='bg-blue'>
				<?php
				foreach($supplier AS $val => $valx){
					echo "<th class='mid' width='".$wind2."%'>Price</th>";
					echo "<th class='mid' width='".$wind2."%'>Qty</th>";
					
				}
				?>
			</tr>
		</thead>
		<tbody>
			<?php
			$no=0;
            foreach($result AS $val => $valx){ $no++;
				$sql2 		= "SELECT id_supplier, nm_supplier FROM tran_material_rfq_detail a WHERE a.no_rfq='".$no_rfq."' AND a.deleted='N' AND a.id_material='".$valx['id_material']."' AND a.status='SETUJU' LIMIT 1";
				$data2		= $this->db->query($sql2)->result();
                echo "<tr>";
					echo "<td class='mid' >".$valx['nm_material']."<input type='hidden' name='detail[".$no."][id_material]' value='".$valx['id_material']."'></td>";
					foreach($supplier AS $val2 => $valx2){
						$sql_d 		= "SELECT a.* FROM tran_material_rfq_detail a WHERE a.no_rfq='".$no_rfq."' AND a.deleted='N' AND a.id_material='".$valx['id_material']."' AND a.id_supplier='".$valx2['id_supplier']."'";
						$dataT		= $this->db->query($sql_d)->result();
						
						echo "<td class='text-right mid' width='".$wind2."%'>".number_format($dataT[0]->price_ref_sup,2)."</td>";
						echo "<td class='text-right mid' width='".$wind2."%'>".number_format($dataT[0]->qty,2)."</td>";
					}
					echo "<td align='left' >".$data2[0]->nm_supplier."</td>";
					echo "<td align='left' >";
						echo "<select name='detail[".$no."][id_supplier]' class='form-control input-sm chosen-select id_supplier'>";
							foreach($supplier AS $val3 => $valx3){
								$selx = ($valx3['id_supplier'] == $data2[0]->id_supplier)?'selected':'';
								echo "<option value='".$valx3['id_supplier']."' ".$selx.">".$valx3['nm_supplier']."</option>";
							}
						echo "</select>";
					echo "</td>";
				echo "</tr>";
			}
			?>
		</tbody>
	</table>
	<?php
		echo form_button(array('type'=>'button','class'=>'btn btn-sm btn-success','style'=>'min-width:100px; float:right; margin: 15px 0px 0px 0px;','value'=>'approve','content'=>'Approve','id'=>'approve_rfq')).' ';
	?>
</div>
</form>

<style>
	.mid{
		vertical-align: middle !important;
	}
	.chosen-container{
		width: 100% !important;
		text-align : left !important;
	}
</style>
<script>
	$(document).ready(function(){
		swal.close();
//		$('.chosen-select').chosen();
	});
	
	//SAVE NEW ADA DEFAULTNYA
	$(document).on('click', '#approve_rfq', function(){

		swal({
		  title: "Are you sure?",
		  text: "You will not be able to process again this data!",
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
						url			: siteurl +'pr_selection/save_approval',
					type		: "POST",
					data		: formData,
					cache		: false,
					dataType	: 'json',
					processData	: false, 
					contentType	: false,				
					success		: function(data){
							console.log(data);
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
							window.location.href = siteurl +'pr_selection/index_approval';
	
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
					},
					error: function(data) {
						swal({
						  title				: "Error Message !", 
						  text				: 'An Error Occured During Process. Please try again..',						
						  type				: "warning",								  
						  timer				: 7000,
						  showCancelButton	: false,
						  showConfirmButton	: false,
						  allowOutsideClick	: false
						});
							console.log(data);
					}
				});
			} else {
			swal("Cancelled", "Data can be process again ", "error");
			return false;
			}
		});
	});

	
	
</script>