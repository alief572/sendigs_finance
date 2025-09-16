
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<div class="box">
	<div class="box-header">
		<span class="pull-right">
		</span>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
        <div class='form-group row'>
				<label class='label-control col-sm-2'><b>Warehouse</b></label>
				<div class='col-sm-4'>
					<select name='warehouse' id='warehouse' class='form-control input-md chosen-select'>
						<option value='0'>ALL WAREHOUSE</option>
						<?php
						foreach($warehouse AS $val => $valx){
							echo "<option value='".$valx['id']."'>".strtoupper(strtolower($valx['nm_gudang']))."</option>";
						}
					 	?>
					</select>
				</div>
			</div>
            <div class='form-group row'>
				<label class='label-control col-sm-2'><b>Range Date</b></label>
				<div class='col-sm-4'>
					<div class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text">
								<i class="far fa-calendar-alt"></i>
							</span>
						</div>
						<input type="text" class="form-control float-right" id="range_picker" placeholder='Select range date' readonly value=''>
					</div>
				</div>	
			</div>
            <div class='form-group row'>
				<label class='label-control col-sm-2'></label>
				<div class='col-sm-4'>
                    <?php
                    echo form_button(array('type'=>'button','class'=>'btn btn-md btn-primary','value'=>'Show','content'=>'Show History','id'=>'showHistory'));
                    echo form_button(array('type'=>'button','class'=>'btn btn-md btn-info','value'=>'save','content'=>'Download Excel','id'=>'download_excel'));
                    ?>
				</div>	
			</div>
			<div id='show_history_view'></div>
	</div>
	<!-- /.box-body -->
</div>

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
				<button type="button" class="btn btn-default " data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<!-- modal --> 
<style type="text/css">
	#range_picker{
		cursor:pointer;
	}
</style>
<!-- page script -->
<script type="text/javascript">
     $(document).ready(function(){
        $('#range_picker').daterangepicker({
            locale: {
                format: 'DD-MM-YYYY'
            }
        });

        $(document).on('click', '#download_excel', function(e){
            let range       = $('#range_picker').val();
            let warehouse   = $('#warehouse').val();
            var tgl_awal 	= '0';
            var tgl_akhir 	= '0';
            if(range != ''){
            var sPLT 		= range.split(' - ');
            var tgl_awal 	= sPLT[0];
            var tgl_akhir 	= sPLT[1];
            }
			if(warehouse == 0){
				swal({
				  title	: "Error Message!",
				  text	: 'Warehouse wajib dipilih ...',
				  type	: "warning"
				});
				return false;	
			}
            var Link	= base_url + active_controller +'/download_excel_summary_gudang/'+warehouse+'/'+tgl_awal+'/'+tgl_akhir;
            window.open(Link);
        });

		$(document).on('click', '.detail_material', function(){
			var type 		= $(this).data('type');
			var id_material = $(this).data('id_material');
			let range       = $('#range_picker').val();
            let warehouse   = $('#warehouse').val();
            var tgl_awal 	= '0';
            var tgl_akhir 	= '0';
            if(range != ''){
            var sPLT 		= range.split(' - ');
            var tgl_awal 	= sPLT[0];
            var tgl_akhir 	= sPLT[1];
            }
			$("#head_title2").html("<b>DETAIL TRANSKASI</b>");

			$.ajax({
				url			: base_url + active_controller+'/show_history_summary_gudang_detail',
				type		: "POST",
				data		: {
					'tanda' 	: type,
					'warehouse' : warehouse,
					'material' 	: id_material,	
					'tgl_awal' 	: tgl_awal,	
					'tgl_akhir' : tgl_akhir,	
				},
				cache		: false,
				dataType	: 'json',
				success:function(data){
					$("#ModalView2").modal();
					$("#view2").html(data.data_html);
				},
				error: function() {
					swal({
					title	: "Error Message !",
					text	: 'Connection Timed Out ...',
					type	: "warning",
					timer	: 5000,
					});
				}
			})
		});

		$(document).on('click', '#showHistory', function(e){
            let range       = $('#range_picker').val();
            let warehouse   = $('#warehouse').val();
            var tgl_awal 	= '0';
            var tgl_akhir 	= '0';
            if(range != ''){
            var sPLT 		= range.split(' - ');
            var tgl_awal 	= sPLT[0];
            var tgl_akhir 	= sPLT[1];
            }

			if(warehouse == 0){
				swal({
				  title	: "Error Message!",
				  text	: 'Warehouse wajib dipilih ...',
				  type	: "warning"
				});
				return false;	
			}

			var baseurl=base_url + active_controller +'/show_history_summary_gudang';
			$.ajax({
				url			: baseurl,
				type		: "POST",
				data		: {
					'warehouse' : warehouse,
					'tgl_awal' : tgl_awal,
					'tgl_akhir' : tgl_akhir,	
				},
				cache		: false,
				dataType	: 'json',
				// beforeSend : function(){
				// 	loading_spinner()
				// },
				success		: function(data){
					if(data.status == 1){
					$('#show_history_view').html(data.data_html);
					swal.close();
					}
					else{
						swal({
							title	: "Save Failed!",
							text	: data.pesan,
							type	: "warning",
							timer	: 3000
						});
					}
				},
				error: function() {
					swal({
						title		: "Error Message !",
						text		: 'An Error Occured During Process. Please try again..',
						type		: "warning",
						timer		: 3000
					});
				}
			});
        });
    });
</script>
