<link rel="stylesheet" href="<?= base_url('assets/plugins/chosen/chosen.min.css')?>">
<div class="box box-primary" style='margin-right: 17px;'>
	
    <form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data"> 
    <div class="box-body"> 
	<br>
	<input type='hidden' id='no_rfq' name='no_rfq' value='<?=$no_rfq;?>'>
	<table id="my-grid" class="table table-striped table-bordered table-hover table-condensed" width="100%">
		<thead id='head_table'>
			<tr class='bg-blue'>
				<th class="text-center mid" rowspan='2' >MATERIAL NAME</th>
			
				<?php
				$wid = 70 / COUNT($supplier);
				$wind2 = $wid/3;
				foreach($supplier AS $val => $valx){
					echo "<th class='mid' >".$valx['nm_supplier']."</th>";
				}
				?>
				<th class="text-center mid"   rowspan='2'>SELECTION</th>
			</tr>
			<tr class='bg-blue'>
				<?php
				foreach($supplier AS $val => $valx){
					echo "<th class='mid' >Price</th>";
					
				}
				?>
			</tr>
		</thead>
		<tbody>
			<?php
			$no=0;
            foreach($result AS $val => $valx){ $no++;
                echo "<tr>";
					echo "<td class='mid' >".$valx['nm_material']."<input type='hidden' name='detail[".$no."][id_material]' value='".$valx['id_material']."'></td>";
					
					foreach($supplier AS $val2 => $valx2){
						$sql_d 		= "SELECT a.* FROM tran_material_rfq_detail a WHERE a.no_rfq='".$no_rfq."' AND a.deleted='N' AND a.id_material='".$valx['id_material']."' AND a.id_supplier='".$valx2['id_supplier']."'";
						$dataT		= $this->db->query($sql_d)->result();
						
						echo "<td class='text-right mid' >".number_format($dataT[0]->price_ref_sup,2)."</td>";
					}
					echo "<td align='left' >";
						echo "<select name='detail[".$no."][id_supplier]' class='form-control input-sm chosen-select id_supplier'>";
							echo "<option value='0'>Select Supplier</option>";
							foreach($supplier AS $val3 => $valx3){
								echo "<option value='".$valx3['id_supplier']."'>".$valx3['nm_supplier']."</option>";
							}
						echo "</select>";
					echo "</td>";
				echo "</tr>";
			}
			?>
		</tbody>
	</table>
	<?php
		echo form_button(array('type'=>'button','class'=>'btn btn-sm btn-success','style'=>'min-width:100px; float:right; margin: 5px 0px 5px 0px;','value'=>'Create Pengajuan','content'=>'Save','id'=>'saveAju')).' ';
	?>
	</form>
    </div>
</div>
<script src="<?= base_url('assets/plugins/chosen/chosen.jquery.min.js')?>"></script>
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
		$('.chosen-select').chosen();
	});
	
	$(document).on('click', '#saveAju', function(){
		var id_supplier = $('.id_supplier').val();
		if( id_supplier == '0'){
			swal({
			  title	: "Error Message!",
			  text	: 'Supplier Not Select, please input first ...',
			  type	: "warning"
			});
			$('#saveAju').prop('disabled',false);
			return false;
		}

		swal({ 
			title: "Are you sure?",
			text: "You will be able to process again this data!",
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
					url			: siteurl +'pr_selection/save_pengajuan',
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
							window.location.href = siteurl +'pr_selection';
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

</script>
