<?php
    $ENABLE_ADD     = has_permission('Mold_Rate.Add');
    $ENABLE_MANAGE  = has_permission('Mold_Rate.Manage');
    $ENABLE_VIEW    = has_permission('Mold_Rate.View');
    $ENABLE_DELETE  = has_permission('Mold_Rate.Delete');
?>
<style type="text/css">
thead input {
	width: 100%;
}
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">

<div class="box">
	<div class="box-header">
			<?php if($ENABLE_VIEW) : ?>
				<!-- <a class="btn btn-success btn-sm add" href="javascript:void(0)" title="Add"><i class="fa fa-plus">&nbsp;</i>Add</a> -->
			<?php endif; ?>
			<button type='button' id='update_cost' class="btn btn-sm btn-primary btn-custom">Update Mold</button>
		<span class="pull-right">
		</span>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped">
		<thead>
		<tr>
			<th>#</th>
			<!-- <th>Code</th> -->
			<th>Mold Name</th>
			<th>Capacity</th>
			<th>Unit</th>
			<th>Price IDR</th>
			<th>Price USD</th>
			<th>Est Used /Year</th>
			<th>Depresiasi /Month</th>
			<th>Utilisasi /Pcs</th>
			<th>Price Mold /Hour</th>
			<th>Action</th>
		</tr>
		</thead>

		<tbody>
		<?php if(empty($result)){
		}else{
			$numb=0; foreach($result AS $record){ $numb++; 
			$nm_mesin = (!empty($list_asset[$record->kd_mesin]['nm_asset']))?$list_asset[$record->kd_mesin]['nm_asset']:'';
			$nm_satuan = (!empty($list_satuan[$record->id_unit]['code']))?$list_satuan[$record->id_unit]['code']:'';
			?>
		<tr>
		    <td><?= $numb; ?></td>
			<!-- <td><?= strtoupper($record->kd_mesin) ?></td> -->
			<td><?= strtoupper($nm_mesin) ?></td>
			<td><?= strtoupper($record->kapasitas) ?></td>
			<td><?= strtoupper($nm_satuan) ?></td>
			<td class='text-right'><?= number_format($record->harga_mesin,2) ?></td>
			<td class='text-right'><?= number_format($record->harga_mesin_usd,2) ?></td>
			<td class='text-right'><?= number_format($record->est_manfaat,2) ?></td>
			<td class='text-right'><?= number_format($record->depresiasi_bulan,2) ?></td>
			<td class='text-right'><?= number_format($record->used_hour_month,2) ?></td>
			<td class='text-right'><?= number_format($record->biaya_mesin,2) ?></td>

			<td>

			<?php if($ENABLE_MANAGE) : ?>
				<a class="btn btn-primary btn-sm edit" href="javascript:void(0)" title="Edit" data-id="<?=$record->id?>"><i class="fa fa-edit"></i>
				</a>
			<?php endif; ?>

			<?php if($ENABLE_DELETE) : ?>
				<a class="btn btn-danger btn-sm delete" href="javascript:void(0)" title="Delete" data-id="<?=$record->id?>"><i class="fa fa-trash"></i>
				</a>
			<?php endif; ?>
			</td>

		</tr>
		<?php } }  ?>
		</tbody>
		</table>
	</div>
	<!-- /.box-body -->
</div>

<!-- awal untuk modal dialog -->

<div class="modal modal-default fade" id="dialog-popup" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="head_title">Default</h4>
      </div>
      <div class="modal-body" id="ModalView">
		...
      </div>
  </div>
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>

<!-- page script -->
<script type="text/javascript">

	$(document).on('click', '.edit', function(e){
		var id = $(this).data('id');
		$("#head_title").html("<b>Mold Rate</b>");
		$.ajax({
			type:'POST',
			url:siteurl+active_controller+'/add/'+id,
			success:function(data){
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			}
		})
	});

	$(document).on('click', '.add', function(){
		$("#head_title").html("<b>Mold Rate</b>");
		$.ajax({
			type:'POST',
			url:siteurl+active_controller+'/add/',
			success:function(data){
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			}
		})
	});

	$(document).on('keyup','.getDep',function(){
		get_depresiasi();
	});
	

	$(document).on('submit', '#data_form', function(e){
		e.preventDefault()
		
		var kd_mesin = $('#kd_mesin').val();

		if(kd_mesin == '0' ){
			swal({title	: "Error Message!",text	: 'Mold not selected...',type	: "warning"
			});
			return false;
		}
		// alert(data);

		swal({
		  title: "Anda Yakin?",
		  text: "Data akan diproses!",
		  type: "warning",
		  showCancelButton: true,
		  confirmButtonClass: "btn-info",
		  confirmButtonText: "Yes",
		  cancelButtonText: "No",
		  closeOnConfirm: false
		},
		function(){
			// var form_data = $('#data_form').serialize();
			var form_data = new FormData($('#data_form')[0]);
		  $.ajax({
			  type:'POST',
			  url:siteurl + active_controller+'add',
			  dataType : "json",
			  data:form_data,
			  processData: false,
        		contentType: false,
			  success:function(data){
				  if(data.status == '1'){
					 swal({
						  title: "Sukses",
						  text : data.pesan,
						  type : "success"
						},
						function (){
							window.location.reload(true);
						})
				  } else {
					swal({
					  title : "Error",
					  text  : data.pesan,
					  type  : "error"
					})

				  }
			  },
			  error : function(){
				swal({
					  title : "Error",
					  text  : "Error proccess !",
					  type  : "error"
					})
			  }
		  })
		});

	})


	// DELETE DATA
	$(document).on('click', '.delete', function(e){
		e.preventDefault()
		var id = $(this).data('id');
		// alert(id);
		swal({
		  title: "Anda Yakin?",
		  text: "Data akan di hapus!",
		  type: "warning",
		  showCancelButton: true,
		  confirmButtonClass: "btn-info",
		  confirmButtonText: "Yes",
		  cancelButtonText: "No",
		  closeOnConfirm: false
		},
		function(){
		  $.ajax({
			  type:'POST',
			  url:siteurl+active_controller+'/delete',
			  dataType : "json",
			  data:{'id':id},
			  success:function(data){
				  if(data.status == '1'){
					 swal({
						  title: "Sukses",
						  text : data.pesan,
						  type : "success"
						},
						function (){
							window.location.reload(true);
						})
				  } else {
					swal({
					  title : "Error",
					  text  : data.pesan,
					  type  : "error"
					})

				  }
			  },
			  error : function(){
				swal({
					  title : "Error",
					  text  : "Error proccess !",
					  type  : "error"
					})
			  }
		  })
		});

	})

	$(document).on('click', '#update_cost', function(){
		swal({
		  title: "Update Data Rate ?",
		  text: "Update Data ",
		  type: "warning",
		  showCancelButton: true,
		  confirmButtonClass: "btn-danger",
		  confirmButtonText: "Ya, Update!",
		  cancelButtonText: "Tidak, Batalkan!",
		  closeOnConfirm: true,
		  closeOnCancel: false
		},
		function(isConfirm) {
			if (isConfirm) {
				// loading_spinner();
				$('#spinnerx').show();
				$.ajax({
					url			: siteurl+active_controller+'/insert_select_data',
					type		: "POST",
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
							  timer	: 7000
							});
							$('#spinnerx').hide();
							window.location.href = siteurl + active_controller;
						}
						else if(data.status == 0){
							swal({
							  title	: "Save Failed!",
							  text	: data.pesan,
							  type	: "warning",
							  timer	: 7000
							});
							$('#spinnerx').hide();
						}
					},
					error: function() {
						swal({
						  title				: "Error Message !",
						  text				: 'An Error Occured During Process. Please try again..',
						  type				: "warning",
						  timer				: 7000
						});
						$('#spinnerx').hide();
					}
				});
			} else {
			swal("Cancelled", "Data can be process again :)", "error");
			return false;
			}
		});
	});

	$(document).on('click', '#update-kurs', function(e){
		e.preventDefault();
		var id = $('#id').val()
		var harga_mesin = getNum($('#harga_mesin').val().split(",").join(""));
		swal({
				title: "Are you sure?",
				text: "Update KURS!",
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
					var baseurl = base_url+active_controller+'/update_kurs'
					$.ajax({
						url			: baseurl,
						type		: "POST",
						data 		: {
							'id' : id
						},
						cache		: false,
						dataType	: 'json',
						success		: function(data){
							if(data.status == 1){
								swal({
										title	: "Save Success!",
										text	: data.pesan,
										type	: "success",
										timer	: 3000
								});

								$('#harga_mesin_usd').val(number_format(harga_mesin/data.kurs,2))
								$('#label_kurs').text(data.label_kurs)
								$('#label_kurs_date').text(data.label_kurs_date)
								$('#label_kurs_last').text(data.label_kurs_last)

								setInterval(function() {
									get_depresiasi();
								}, 1000);
								

								// $.ajax({
								// 	type:'POST',
								// 	url:siteurl+active_controller+'/add/'+id,
								// 	success:function(data){
								// 		$("#dialog-popup").modal();
								// 		$("#ModalView").html(data);

								// 	}
								// })
								// window.location.href = base_url + active_controller + '/add';
							}else{

								if(data.status == 2){
									swal({
										title	: "Save Failed!",
										text	: data.pesan,
										type	: "warning",
										timer	: 3000,
									});
								}else{
									swal({
										title	: "Save Failed!",
										text	: data.pesan,
										type	: "warning",
										timer	: 3000,
									});
								}

							}
						},
						error: function() {

							swal({
								title				: "Error Message !",
								text				: 'An Error Occured During Process. Please try again..',
								type				: "warning",
								timer				: 3000,
							});
						}
					});
				} else {
				swal("Cancelled", "Data can be process again :)", "error");
				return false;
				}
		});
	});

  	$(function() {
	    var table = $('#example1').DataTable( {
	        orderCellsTop: true,
	        fixedHeader: true
	    } );
    	$("#form-area").hide();
  	});

	function get_depresiasi(){
		var harga_mesin   		= getNum($('#harga_mesin_usd').val().split(",").join(""));
		var est_manfaat   		= getNum($('#est_manfaat').val().split(",").join(""));
		var used_hour_month   	= getNum($('#used_hour_month').val().split(",").join(""));
		var depresiasi 			= harga_mesin / (est_manfaat * 12);
		var biaya_mesin 		= depresiasi / used_hour_month;

		$('#depresiasi_bulan').val(depresiasi.toFixed(2));
		$('#biaya_mesin').val(biaya_mesin.toFixed(2));
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
