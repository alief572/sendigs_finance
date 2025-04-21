<?php
$this->load->view('include/side_menu');
?>
<form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data" autocomplete='off'> 
	<div class="box box-primary">
		<div class="box-header">
			<h3 class="box-title"><?php echo $title;?></h3>
			<div class="box-tool pull-right"><br>
				<select id='no_po' name='no_po' class='form-control input-sm' style='min-width:200px;'>
					<option value='0'>All PO Number</option>
					<?php
						foreach($list_po AS $val => $valx){
							echo "<option value='".$valx['no_po']."'>".strtoupper($valx['no_po'])."</option>";
						}
					?>
				</select>
				<select id='gudang' name='gudang' class='form-control input-sm' style='min-width:200px;'>
					<option value='0'>All Warehouse</option>
					<?php
						foreach($data_gudang AS $val => $valx){
							echo "<option value='".$valx['kd_gudang']."'>".strtoupper($valx['nm_gudang'])."</option>";
						}
					?>
				</select>
			</div>
		</div>
		<div class="box-body">
			<table id="example1" width='100%' class="table table-bordered table-striped" width='100%'>
				<thead>
					<tr class='bg-blue'>
						<th class="text-center">No</th> 
						<th class="text-center">No Trans</th>
						<th class="text-center">Warehouse</th>
						<th class="text-center">Material</th>
						<th class="text-center">Sum Material</th>
						<th class="text-center">Receiver</th>
						<th class="text-center">Incoming Date</th>
						<th class="text-center">Status</th> 
						<th class="text-center">Option</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
		<!-- /.box-body -->
	 </div>
  <!-- /.box -->
	<!-- modal -->
	<div class="modal fade" id="ModalView2" style='overflow-y: auto;'>
		<div class="modal-dialog"  style='width:95%; '>
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="head_title2"></h4>
					</div>
					<div class="modal-body" id="view2">
					</div>
					<div class="modal-footer">
					<!--<button type="button" class="btn btn-primary">Save</button>-->
					<button type="button" class="btn btn-default " data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
	<!-- modal -->
</form>

<?php $this->load->view('include/footer'); ?>
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
	$(document).ready(function(){
		var no_po 	= $('#no_po').val();
		var gudang 	= $('#gudang').val();
		DataTables(no_po,gudang);
		
		$(document).on('change','#gudang, #no_po', function(e){
			e.preventDefault();
			var no_po 	= $('#no_po').val();
			var gudang 	= $('#gudang').val();
			DataTables(no_po,gudang);
		});
		
	});

	$(document).on('click', '.detailAjust', function(e){
		e.preventDefault();
		loading_spinner();
		$("#head_title2").html("<b>DETAIL INCOMING</b>");
		$.ajax({
			type:'POST',
			url: base_url + active_controller+'/modal_detail_adjustment/'+$(this).data('kode_trans')+'/'+$(this).data('tanda'),
			success:function(data){
				$("#ModalView2").modal();
				$("#view2").html(data);

			},
			error: function() {
				swal({
				  title				: "Error Message !",
				  text				: 'Connection Timed Out ...',
				  type				: "warning",
				  timer				: 5000
				});
			}
		});
	});
	
	$(document).on('click', '.check', function(e){
		e.preventDefault();
		loading_spinner();
		$("#head_title2").html("<b>CHECK INCOMING MATERIAL</b>");
		$.ajax({
			type:'POST',
			url: base_url + active_controller+'/modal_incoming_check/'+$(this).data('kode_trans'),
			success:function(data){
				$("#ModalView2").modal();
				$("#view2").html(data);

			},
			error: function() {
				swal({
				  title				: "Error Message !",
				  text				: 'Connection Timed Out ...',
				  type				: "warning",
				  timer				: 5000
				});
			}
		});
	});
	
	$(document).on('click','.plus', function(){
		var no 		= $(this).data('id');
		var kolom	= parseFloat($(this).parent().parent().find("td:nth-child(1)").attr('rowspan')) + 1;
		
		$(this).parent().parent().find("td:nth-child(1), td:nth-child(2), td:nth-child(3), td:nth-child(4), td:nth-child(5), td:nth-child(6)").attr('rowspan', kolom);
		
		var Rows	= "<tr>";
			Rows	+= "<td align='center'><input type='text' name='detail["+no+"][detail]["+kolom+"][qty_oke]' data-no='"+no+"' class='form-control input-sm text-right maskM'></td>";
			Rows	+= "<td align='center'><input type='text' name='detail["+no+"][detail]["+kolom+"][qty_rusak]' data-no='"+no+"' class='form-control input-sm text-right maskM'></td>";
			Rows	+= "<td align='center'><input type='text' name='detail["+no+"][detail]["+kolom+"][expired]' data-no='"+no+"' class='form-control text-center input-sm text-left tanggal' readonly placeholder='Expired Date'></td>";
			Rows	+= "<td align='center'><input type='text' name='detail["+no+"][detail]["+kolom+"][konversi]' data-no='"+no+"' value='1' class='form-control input-sm text-center maskM'></td>";
			Rows	+= "<td align='center'><input type='text' name='detail["+no+"][detail]["+kolom+"][keterangan]' data-no='"+no+"' class='form-control input-sm text-left'></td>";
			Rows	+= "<td align='center'>";
			Rows	+= "<button type='button' class='btn btn-sm btn-danger delete' title='Delete' data-id='"+no+"'><i class='fa fa-trash'></i></button>";
			Rows	+= "</td>";
			Rows	+= "</tr>";
		// alert(Rows);
		$(this).parent().parent().after(Rows);
		
		$('.maskM').autoNumeric('init', {mDec: '2', aPad: false});
		$('.tanggal').datepicker({
			dateFormat : 'yy-mm-dd',
			changeMonth: true,
			changeYear: true
		});
	});
	
	$(document).on('click','.delete', function(){
		var no 		= $(this).data('id');
		var kolom	= parseFloat($(".baris_"+no).find("td:nth-child(1)").attr('rowspan')) - 1;
		$(".baris_"+no).find("td:nth-child(1), td:nth-child(2), td:nth-child(3), td:nth-child(4), td:nth-child(5), td:nth-child(6)").attr('rowspan', kolom);
		
		$(this).parent().parent().remove();
	});

	$(document).on('click', '#checkMaterial', function(){
		
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
				loading_spinner();
				var formData  	= new FormData($('#form_proses_bro')[0]);
				$.ajax({
					url			: base_url + active_controller+'/process_check_material',
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
							window.location.href = base_url + active_controller+'/incoming_check';
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


	function DataTables(no_po=null,gudang=null){
		var dataTable = $('#example1').DataTable({
			"processing": true,
			"serverSide": true,
			"stateSave" : true,
			"bAutoWidth": true,
			"destroy": true,
			"responsive": true,
			"aaSorting": [[ 1, "asc" ]],
			"columnDefs": [ {
				"targets": 'no-sort',
				"orderable": false,
			}], 
			"sPaginationType": "simple_numbers",
			"iDisplayLength": 10,
			"aLengthMenu": [[10, 20, 50, 100, 150], [10, 20, 50, 100, 150]],
			"ajax":{
				url : base_url + active_controller+'/server_side_check_material',
				type: "post",
				data: function(d){
					d.no_po = no_po,
					d.gudang = gudang
				},
				cache: false,
				error: function(){
					$(".my-grid-error").html("");
					$("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
					$("#my-grid_processing").css("display","none");
				}
			}
		});
	}

</script>
