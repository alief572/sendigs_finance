
<form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data">
<div class="box box-primary">
	<div class="box-header">
		<h3 class="box-title"><?php echo $title;?></h3>
		<div class="box-tool pull-right">
		<input type='hidden' id='uri_tanda' value='<?=$uri_tanda;?>'>
		</div>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<?php
		if(empty($uri_tanda)){
			?>
			<div class='form-group row'><br>
				<label class='label-control col-sm-2'><b>Dari Gudang</b></label>
				<div class='col-sm-4'>
					<select id='gudang_before' name='gudang_before' class='form-control input-sm chosen_select' style='min-width:200px;'>
						<option value='0'>Select Warehouse</option>
						<?php
							foreach($pusat AS $val => $valx){
								echo "<option value='".$valx['id']."'>".strtoupper($valx['nm_gudang'])."</option>";
							}
						?>
					</select>
				</div>
				<div class='in_id'>
					<label class='label-control col-sm-2'><b>Ke Gudang</b></label>
					<div class='col-sm-4'>
						<select id='gudang_after' name='gudang_after' class='form-control input-sm chosen_select' style='min-width:200px;'>
							<option value='0'>List Empty</option>
						</select>
					</div>
				</div>
			</div>
			<?php
			echo form_button(array('type'=>'button','class'=>'btn btn-md btn-success','style'=>'min-width:100px; float:right; margin: 5px 0px 5px 0px;','value'=>'Process','content'=>'Process','id'=>'request'));
			?>
			<br><br><br>
			<?php
		}
		?>
		<table class="table table-bordered table-striped" id="example1" width='100%'>
			<thead>
				<tr>
					<th class="text-center">No</th>
					<th class="text-center">No Trans</th>
					<th class="text-center">Gudang Dari</th>
					<th class="text-center">Gudang Ke</th>
					<th class="text-center">Sum Material</th>
					<th class="text-center">Receiver</th>
					<th class="text-center">Incoming Date</th>
					<th class="text-center no-sort">Option</th>
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
		<div class="modal-dialog"  style='width:85%; '>
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
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>
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
<script src="<?= base_url('assets/js/jquery.maskMoney.js')?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>
<script>
	$(document).ready(function(){
		$('.maskM').maskMoney();
		$('.chosen_select').select2();

		var uri_tanda 	= $('#uri_tanda').val();
		DataTables(uri_tanda);
	});

	$(document).on('click', '.detailAjust', function(e){
		e.preventDefault();

		$("#head_title2").html("<b>DETAIL REQUEST SUBGUDANG</b>");
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
				  timer				: 5000,
				  showCancelButton	: false,
				  showConfirmButton	: false,
				  allowOutsideClick	: false
				});
			}
		});
	});

	$(document).on('change','#gudang_before', function(e){
		e.preventDefault();
			$.ajax({
				url: base_url + active_controller+'/get_gudang_tujuan',
				cache: false,
				type: "POST",
				data: "id="+this.value,
				dataType: "json",
				success: function(data){
					$("#gudang_after").html(data.option).trigger("chosen:updated");
				}
			});
	});



  $(document).on('click', '#request', function(e){
		e.preventDefault();
		var gudang_before 	= $('#gudang_before').val();
		var gudang_after 	= $('#gudang_after').val();

		if( gudang_before == '0'){
			swal({
			  title	: "Error Message!",
			  text	: 'Gudang Dari belum dipilih, please input first ...',
			  type	: "warning"
			});
			$('#request').prop('disabled',false);
			return false;
		}

		if( gudang_after == '0'){
			swal({
			  title	: "Error Message!",
			  text	: 'Gudang Ke belum dipilih, please input first ...',
			  type	: "warning"
			});
			$('#request').prop('disabled',false);
			return false;
		}


		$("#head_title2").html("<b>TOTAL MATERIAL REQUEST</b>");
		$.ajax({
			type:'POST',
			url: base_url + active_controller+'/modal_mutasi/'+gudang_before+'/'+gudang_after,
			success:function(data){
				$("#ModalView2").modal();
				$("#view2").html(data);

			},
			error: function() {
				swal({
				  title				: "Error Message !",
				  text				: 'Connection Timed Out ...',
				  type				: "warning",
				  timer				: 5000,
				  showCancelButton	: false,
				  showConfirmButton	: false,
				  allowOutsideClick	: false
				});
			}
		});
	});




	$(document).on('click', '#process_mutasi', function(){

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

				var formData  	= new FormData($('#form_adjustment')[0]);
				$.ajax({
					url			: base_url + active_controller+'/process_mutasi',
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

	//MODAL mutasi
	$(document).on('keyup','.pack', function(){
		var no 				= $(this).data('no');
		var konversi 	= getNum($('#konversi_'+no).val());
		var value 		= getNum($(this).val().split(",").join(""));
		var hasil			= value * konversi;
		$('#req_stock_'+no).val(number_format(hasil,2));
		get_total_ct();
	});

	$(document).on('keyup','.stock', function(){
		var no 				= $(this).data('no');
		var konversi 	= getNum($('#konversi_'+no).val());
		var value 		= getNum($(this).val().split(",").join(""));
		var hasil			= value / konversi;
		$('#req_pack_'+no).val(number_format(hasil,2));
		get_total_ct();
	});

	$(document).on('change','.pack, .stock, .ket', function(){
		var no 						= $(this).data('no');
		var id 						= $('#id_'+no).val();
		var req_pack 			= $('#req_pack_'+no).val();
		var req_stock 		= $('#req_stock_'+no).val();
		var ket_request 	= $('#ket_request_'+no).val();

		$.ajax({
				url			: base_url + active_controller+'/save_temp_mutasi',
				type		: "POST",
				data		: {
					"id" 					: id,
					"req_pack" 		: req_pack,
					"req_stock" 	: req_stock,
					"ket_request" : ket_request
				},
				cache		: false
		});

	});

	function get_total_ct(){
	  var kolom = 150;
	  var a;
	  for(a=1; a<kolom; a++){
			// console.log(a);
	    var man =  getNum($("#req_stock_"+a).val().split(",").join(""));
	    var max2 =  getNum($("#stock_"+a).val().split(",").join(""));
			var max = max2;
			if(max2 < 0){
				var max = 0;
			}
			// console.log(man);
			// console.log(max);
	    if(man <= max){
	      $("#process_mutasi").show();
	      $("#notive").html("");
	      // return false;
	    }
	    if(man > max){
	      $("#process_mutasi").hide();
	      $("#notive").html("Tidak boleh melebihi stock yang ada");
	      // swal({title	: "Error Message!",text	: 'Man minutes melebihi availibility, check again ...',type	: "warning"});
	      return false;
	    }

	  }

	}



	function DataTables(uri_tanda=null){
		var dataTable = $('#example1').DataTable({
			"serverSide": true,
			"stateSave" : true,
			"bAutoWidth": true,
			"destroy": true,
			"processing": true,
			"responsive": true,
			"fixedHeader": {
				"header": true,
				"footer": true
			},
			"oLanguage": {
				"sSearch": "<b>Live Search : </b>",
				"sLengthMenu": "_MENU_ &nbsp;&nbsp;<b>Records Per Page</b>&nbsp;&nbsp;",
				"sInfo": "Showing _START_ to _END_ of _TOTAL_ entries",
				"sInfoFiltered": "(filtered from _MAX_ total entries)",
				"sZeroRecords": "No matching records found",
				"sEmptyTable": "No data available in table",
				"sLoadingRecords": "Please wait - loading...",
				"oPaginate": {
					"sPrevious": "Prev",
					"sNext": "Next"
				}
			},
			"aaSorting": [[ 1, "desc" ]],
			"columnDefs": [ {
				"targets": 'no-sort',
				"orderable": false,
			}],
			"sPaginationType": "simple_numbers",
			"iDisplayLength": 10,
			"aLengthMenu": [[10, 20, 50, 100, 150], [10, 20, 50, 100, 150]],
			"ajax":{
				url : base_url + active_controller+'server_side_mutasi_material',
				type: "post",
				data: function(d){
					d.tanda = 'mutasi material',
					d.uri_tanda = uri_tanda
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


	function DataTables2(gudang_before=null){
		var dataTable = $('#example2').DataTable({
			"scrollY": "500",
	    "scrollCollapse" : true,
			"serverSide": true,
			"stateSave" : true,
			"bAutoWidth": true,
			"destroy": true,
			"processing": true,
			"responsive": true,
			"fixedHeader": {
				"header": true,
				"footer": true
			},
			"oLanguage": {
				"sSearch": "<b>Live Search : </b>",
				"sLengthMenu": "_MENU_ &nbsp;&nbsp;<b>Records Per Page</b>&nbsp;&nbsp;",
				"sInfo": "Showing _START_ to _END_ of _TOTAL_ entries",
				"sInfoFiltered": "(filtered from _MAX_ total entries)",
				"sZeroRecords": "No matching records found",
				"sEmptyTable": "No data available in table",
				"sLoadingRecords": "Please wait - loading...",
				"oPaginate": {
					"sPrevious": "Prev",
					"sNext": "Next"
				}
			},
			"aaSorting": [[ 0, "asc" ]],
			"columnDefs": [ {
				"targets": 'no-sort',
				"orderable": false,
			}],
			"sPaginationType": "simple_numbers",
			"iDisplayLength": 10,
			"aLengthMenu": [[10, 20, 50, 100, 150], [10, 20, 50, 100, 150]],
			"ajax":{
				url : base_url + active_controller+'/server_side_modal_mutasi',
				type: "post",
				data: function(d){
					d.gudang_before = gudang_before
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

	function getNum(val) {
	   if (isNaN(val) || val == '') {
	     return 0;
	   }
	   return parseFloat(val);
	}

	function number_format (number, decimals, dec_point, thousands_sep) {
		// Strip all characters but numerical ones.
		number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
		var n = !isFinite(+number) ? 0 : +number,
			prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
			sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
			dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
			s = '',
			toFixedFix = function (n, prec) {
				var k = Math.pow(10, prec);
				return '' + Math.round(n * k) / k;
			};
		// Fix for IE parseFloat(0.55).toFixed(0) = 0;
		s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
		if (s[0].length > 3) {
			s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
		}
		if ((s[1] || '').length < prec) {
			s[1] = s[1] || '';
			s[1] += new Array(prec - s[1].length + 1).join('0');
		}
		return s.join(dec);
	}

</script>
