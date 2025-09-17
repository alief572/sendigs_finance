<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">

<div class="nav-tabs-custom">
    <ul class="nav nav-tabs">
        <li class="active"><a href="#inquiry" data-toggle="tab" aria-expanded="true" id="data"></a></li>
        <li class=""><a href="#formsheet" data-toggle="tab" aria-expanded="false" id="data_form"></a></li>
    </ul>
    <!-- /.tab-content -->
    <div class="tab-content">
        <div class="tab-pane active" id="inquiry">
            <div class="box box-primary">
                <form id="form-header-inquiry" method="post">
                <div class="form-horizontal">
                <div class="box-body">
                
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Tgl Inquiry</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="tgl_inquiry"  name="tgl_inquiry" placeholder="Tgl Inquiry"  required>
					</div>
				</div>
				</div>
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="customer">Nama Customer</label>
					</div>
					 <div class="col-md-8">
					  <select id="customer" name="customer"  class="form-control input-sm" onchange="get_pic()" required>
						<option value="">-- Pilih Customer --</option>
						<?php foreach ($results['customer'] as $customer){ 
						?>
						<option value="<?= $customer->id_customer?>"><?= ucfirst(strtolower($customer->name_customer))?></option>
						<?php } ?>
					  </select>
					 </div>
				</div>
				</div>
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="customer">PIC Customer</label>
					</div>
					 <div class="col-md-8">
					  <select id="pic_cust" name="pic_cust"  class="form-control input-sm" required>
					  </select>
					 </div>
				</div>
				</div>
				<div class="col-md-5">
			    <div class="form-group row">
					<div class="col-md-4">
					  <label for="">Project</label>
					</div>
					<div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="project"  name="project" placeholder="Project"  required>
					</div>
				</div>
				</div>	
				<div class="col-md-5">
			    <div class="form-group row">
					<div class="col-md-4">
					  <label for="">Sales</label>
					</div>
					<div class="col-md-8">
					  <select id="sales" name="sales" class="form-control input-sm" required>
						<option value="">-- Pilih Sales --</option>
						<?php foreach ($results['sales'] as $sales){ 
						?>
						<option value="<?= $sales->id_karyawan?>"><?= ucfirst(strtolower($sales->nama_karyawan))?></option>
						<?php } ?>
					  </select>
					  </div>
				</div>
				</div>	
				<div class="col-md-5">
			    <div class="form-group row">
					<div class="col-md-4">
					  <label for="">Keterangan</label>
					</div>
					<div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="ket_project"  name="ket_project" placeholder="Keterangan" required>
					</div>
				</div>
				</div>		

                </div>
                </div>
                </form>
            </div>
       
			<div class="box box-default ">
				<div class="box-header">
					<h4 class="box-title">Detail Inquiry</h4>
					<div class="box-tools pull-right">
						<button class="btn btn-sm btn-success" id="tambah" type="button" style="width: 100%;">
							<i class="fa fa-plus"></i> Add Item
						</button>
					</div>
				</div>
				<div class="box-body">
					<form id="form-detail-inquiry" method="post">
						<table class="table table-bordered" width="100%" id="tabel-detail-mutasi">
							<thead>
								<tr class="bg-blue">
									<th class="text-center">Kode Material</th>
									<th class="text-center">Nama Material</th>
									<th class="text-center">Thickness</th>
									<th class="text-center">Length</th>
									<th class="text-center">Width</th>
									<th class="text-center">Density</th>
									<th class="text-center">Action</th>
								</tr>
							</thead>
							<tbody id="list_item_mutasi">
							
							</tbody>
						</table>
					</form>
				</div>
			</div>
		<div class="text-right">
		  <div class="box active"> 
			<div class="box-body">
					<button class="btn btn-primary" type="button" onclick="saveinquiry()">
					<i class="fa fa-save"></i><b> Lanjut Isi Detail</b>
				</button>
			</div>
		  </div>
		</div>

		</div>
		
		<div class="tab-pane" id="formsheet">
          <!-- Data Koli -->
            <div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;">
            </div>
            <div class="box box-primary">
                <form id="form-header-coilsheet" method="post">
                <div class="form-horizontal">
                <div class="box-body">
                
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">No Inquiry</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="noinquiry"  name="noinquiry" placeholder="No Inquiry" value="" required readonly>
					</div>
				</div>
				</div>
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Tgl Inquiry</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="tgl"  name="tgl" placeholder="Tgl Inquiry" value="" required readonly>
					</div>
				</div>
				</div>
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="customer">Nama Customer</label>
					</div>
					 <div class="col-md-8">
					  <select id="cust" name="cust"  class="form-control input-sm" readonly required>
						<option value="">-- Pilih Customer --</option>
						<?php foreach ($results['customer'] as $customer){ 
						?>
						<option value="<?= $customer->id_customer?>"><?= ucfirst(strtolower($customer->name_customer))?></option>
						<?php } ?>
					  </select>
					 </div>
				</div>
				</div>
				<div class="col-md-5">
			    <div class="form-group row">
					<div class="col-md-4">
					  <label for="">PIC Customer</label>
					</div>
					<div class="col-md-8">
					  <select id="pic" name="pic" class="form-control input-sm" readonly required>
						<option value="">-- Pilih Sales --</option>
						<?php foreach ($results['pic'] as $pic){ 
						?>
						<option value="<?= $pic->id_pic?>"><?= ucfirst(strtolower($pic->name_pic))?></option>
						<?php } ?>
					  </select>
					  </div>
				</div>
				</div>	
                <div class="col-md-5">
			    <div class="form-group row">
					<div class="col-md-4">
					  <label for="">Sales</label>
					</div>
					<div class="col-md-8">
					  <select id="sales2" name="sales2" class="form-control input-sm" readonly required>
						<option value="">-- Pilih Sales --</option>
						<?php foreach ($results['sales'] as $sales){ 
						?>
						<option value="<?= $sales->id_karyawan?>"><?= ucfirst(strtolower($sales->nama_karyawan))?></option>
						<?php } ?>
					  </select>
					  </div>
				</div>
				</div>				
				<div class="col-md-5">
			    <div class="form-group row">
					<div class="col-md-4">
					  <label for="">Project</label>
					</div>
					<div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="proj"  name="proj" placeholder="Project" value="" $('#proj').val(project); required readonly>
					</div>
				</div>
				</div>	
					
					

                </div>
                </div>
                </form>
            </div>
       
			<div class="box box-primary ">
				<div class="box-header">
					<h4 class="box-title">Detail Inquiry</h4>
    			</div>
				<div id="list_detail">
					
				</div>
				<div class="box-header">
					<h4 class="box-title">Lain-Lain</h4>
    			</div>
			    <div class="box box-primary">
				<form id="form-term" method="post">
                <div class="form-horizontal">
                <div class="box-body">
                
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="sample">Master Sample</label>
					</div>
					 <div class="col-md-8">
					  <select class="form-control input-sm select2" id="sample" name="sample">
						<option value='1'>Ya</option>
						<option value='0'>Tidak</option>
					</select>
					 </div>
				</div>
				</div>
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="instrument">Payment Instrument</label>
					</div>
					 <div class="col-md-8">
					 <select class="form-control input-sm select2" id="pay_instrument" name="pay_instrument">
						<option value='1'>Cash</option>
						<option value='2'>Giro</option>
						<option value='3'>Transfer Bank</option>
					 </select>
					</div>
				</div>
				</div>
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="payment_term">Payment Term</label>
					</div>
					 <div class="col-md-8">
					   <input type="text" class="form-control input-sm" id="pay_term"  name="pay_term" placeholder="Payment Term">
					 </div>
				</div>
				</div>
				</div>
                </div>
                </form>
             </div>
			 <div class="box box-primary">
				 <form id="form-payment" method="post">
				  <div class="form-horizontal">
                  <div class="box-body">
					  <div class="col-sm-8">
						  <b>Progress of payment</b>
						  <div class="input_fields_wrap">
							<div class="row">
							  <div class="col-xs-1">
								No.
							  </div>
							  <div class="col-sm-4">
								<div class="form-group ">
								  <div class="radio-inline">
									<label>
									  <input type="radio" class="radio_logo" name="opsi_top" value="persen" onclick="get_logo(this.value)">Persen
									</label>
								  </div>
								  <div class="radio-inline">
									<label>
									  <input type="radio" class="radio_logo" name="opsi_top" value="nominal" onclick="get_logo(this.value)" checked >Nominal
									</label>
								  </div>
								</div>
							  </div>
							  <div class="col-xs-4">
								Tgl Bayar
							  </div>
							  <div class="col-xs-2">
								<button class="add_field_button btn btn-primary"><i class="fa fa-plus"></i></button>
							  </div>
							</div>
							<div class="row">
							  <div class="col-xs-1">
								1
							  </div>
							  <div class="col-xs-3">
								<div class="input-group">
								  <span class="input-group-addon logo_currency">Rp.</span>
								  <input type="text" name="pembayaran[]"  class="form-control input-sm" value="100" required="" onkeyup="this.value = this.value.match(/^-?\d*[.]?\d*$/);">
								</div>
							  </div>
							  <div class="col-xs-4">
								<input type="text" name="perkiraan_bayar[]" class="form-control pull-right datepickerxx"  value="<?= date('d-m-Y'); ?>">
							  </div>
							  <div class="col-xs-2">
								&nbsp;
							  </div>
							</div>
						  </div>
						</div>
						<div class="col-sm-3">
						</div>
					   </form>
								  
					  <form id="form-est-kirim" method="post">
					  <div class="col-sm-8">
						  <b>Estimasi Pengiriman</b>
						  <div class="input_fields_wrap2">
							<div class="row">
							  <div class="col-xs-1">
								No.
							  </div>
							  <div class="col-xs-4">
							  </div>
							  <div class="col-xs-4">
								Tgl kirim
							  </div>
							  <div class="col-xs-2">
								<button class="add_field_button2 btn btn-primary"><i class="fa fa-plus"></i></button>
							  </div>
							</div>
							<div class="row">
							  <div class="col-xs-1">
								1
							  </div>
							  <div class="col-xs-3">
								<div class="input-group">
								<input type="hidden" name="qty_kirim[]"  class="form-control input-sm" value="" onkeyup="this.value = this.value.match(/^-?\d*[.]?\d*$/);">
								</div>
							  </div>
							  <div class="col-xs-4">
								<input type="text" name="perkiraan_kirim[]" class="form-control pull-right datepickerxx"  value="<?= date('d-m-Y'); ?>">
							  </div>
							  <div class="col-xs-2">
								&nbsp;
							  </div>
							</div>
						  </div>
						</div>
						<div class="col-sm-3">
						</div>
					  </div>
				  </div>
				  </form>
				</div>  
			</div>
			
			
		<div class="text-right">
		  <div class="box box-primary"> 
			<div class="box-body">
					<button class="btn btn-primary" type="button" onclick="saveform()">
					<i class="fa fa-save"></i><b> Simpan Data</b>
				</button>
			</div>
		  </div>
		</div>
          <!-- Data Koli --> 
    </div>
	
	</div>		
</div>
<!-- /.tab-content -->

<!-- Modal -->
<div class="modal modal-primary" id="dialog-item-do" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel"><span class="fa fa-file-pdf-o"></span>&nbsp;Data SO untuk Delivery Order (DO)</h4>
      </div>
      <div class="modal-body" id="MyModalBody"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">
        <span class="glyphicon glyphicon-remove"></span>  Tutup</button>
        </div>
    </div>
  </div>
</div>
<div class="modal modal-primary" id="dialog-data-stok" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel"><span class="fa fa-file-pdf-o"></span>&nbsp;Data Material</h4>
      </div>
      <div class="modal-body" id="MyModalBodyStok" style="background: #FFF !important;color:#000 !important;">
		<table class="table table-bordered" width="100%" id="list_item_stok">
              <thead>
                  <tr>
                      <th width="75%">Nama Material</th>
                      <th width="10%">Thickness</th>
                      <th width="10%">Length</th>
					  <th width="10%">Width</th>
					  <th width="10%">Density</th>
                      <th width="2%" class="text-center">Aksi</th> 
                  </tr>
              </thead>
              <tbody>
                  <?php				  
				  if($results['material']){
					foreach($results['material'] as $ks=>$vs){
                  ?>
						  <tr>
							  <td><?php echo $vs->id_material.', '.$vs->nama?></td>
							  <td><center><?php echo $vs->spec5?></center></td>
							  <td><center><?php echo $vs->spec6?></center></td>
							  <td><center><?php echo $vs->spec7?></center></td>
							  <td><center><?php echo $vs->spec8?></center></td>
							  <td>
								<center>
									<button id="btn-<?php echo $vs->id_material?>" class="btn btn-warning btn-sm" 
									type="button" onclick="startmutasi('<?php echo $vs->id_material?>','<?php echo $vs->nama?>','<?php echo $vs->spec5?>','<?php echo $vs->spec6?>','<?php echo $vs->spec7?>','<?php echo $vs->spec8?>')">
										Pilih
									</button>
								</center>
							  </td>
						  </tr>
                  <?php 
						}
					  }
				  
				  ?>
              </tbody>
          </table>
	  </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">
        <span class="glyphicon glyphicon-remove"></span>  Tutup</button>
        </div>
    </div>
  </div>
</div>


<!-- Modal -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>
<script src="<?= base_url('assets/js/jquery.maskMoney.js')?>"></script>

<script type="text/javascript">
	//$('#input-kendaraan').hide();
	var base_url			= '<?php echo base_url(); ?>';
	var active_controller	= '<?php echo($this->uri->segment(1)); ?>';
	var cabang_user			= '<?php echo $cabs_user;?>';
	var arr_cabang			= <?php echo json_encode($Arr_Cabang);?>;
	
	$(document).ready(function(){
		
		// $(".TotalKg").maskMoney({
			// thousands:',', decimal:'.', allowZero: true, suffix: ''
			// });
		// $(".Budget").maskMoney({
			// thousands:',', decimal:'.', allowZero: true, suffix: ''
			// });
		
		
		$("#tgl_inquiry").datepicker({
			format : "dd-mm-yyyy",
			showInputs: true,
			autoclose:true,
			todayHighlight: true,
			startDate: "dateToday -30"
    	});
		
		
		
		$('#tipekirim').change(get_driver_kbm);
		$('#cabang_asal').change(function(){
			var cabang_asal = $('#cabang_asal').val();
			$('#MyModalBodyStok, #list_item_mutasi').empty();
			if(cabang_asal !='' && cabang_asal!=null){
				var cabang_tujuan	= $('#cabang_tujuan').val();
				if(cabang_asal==cabang_tujuan){
					$('#cabang_tujuan').val('');
				}
				
			}
		});
		$("#tambah").click(function(){
			$('#dialog-data-stok').modal('show');
		});
		 $("#tambah2").click(function(){
			if(cabang_user !='100'){
				$('#dialog-data-stok').modal('show');
			}else{
				var cabang_asal	= $('#cabang_asal').val();
				var cabang_tujuan = $('#cabang_tujuan').val();
				if(cabang_tujuan=='' || cabang_tujuan==null){
					swal({
						title: "Peringatan!",
						text: "Cabang Asal belum dipilih. Mohon pilih cabang asal terlebih dahulu...",
						type: "warning"
					});
				}else{
					var kode_pecah	= cabang_asal.split('|');
					var kode_tujuan	= cabang_tujuan.split('|');
					var baseurl=base_url + active_controller +'/get_stock_item';				
					$.ajax({
						'url'		: baseurl,
						'type'		: 'post', 
						'data'		: {'cabang':kode_pecah[0],'tujuan':kode_tujuan[0]},
						'success'	: function(data){
							$('#MyModalBodyStok').html(data);
							$('#dialog-data-stok').modal('show');
							$("#list_item_stok").DataTable({lengthMenu:[5,10,15,20]}).draw();
						}, 
						'error'		: function(data){
							alert('An error occured, please try again.');						
						}
					});
				}
			}
			
		});
		$("#list_item_stok").DataTable({lengthMenu:[5,10,15,20]}).draw();
		
		
		$(document).on('change', '.PilihForm', function(){
		var dataNomor = $(this).data('nomor');
		var dataIni	= $(this).val();
		
		
		if(dataIni != ""){
            
					if(dataIni == "0"){
                    $('#inner_'+dataNomor).attr('readonly', 'readonly');
					$('#width_'+dataNomor).removeAttr('readonly');
                    $('#kg_roll_'+dataNomor).attr('readonly', 'readonly');	
                    $('#kg_sheet_'+dataNomor).removeAttr('readonly');	
                    $('#qty_roll_'+dataNomor).attr('readonly', 'readonly');					
					$('#qty_pcs_'+dataNomor).removeAttr('readonly');
					
					}
                    else if(dataIni == "1"){
                    $('#width_'+dataNomor).attr('readonly', 'readonly');
                    $('#inner_'+dataNomor).removeAttr('readonly');	
                    $('#kg_sheet_'+dataNomor).attr('readonly', 'readonly');	
                    $('#kg_roll_'+dataNomor).removeAttr('readonly');
					$('#qty_pcs_'+dataNomor).attr('readonly', 'readonly');	
                    $('#qty_roll_'+dataNomor).removeAttr('readonly');					
                    // $('#pricelistcetak_'+dataNomor).val(data.pricelist_repostcetak);
					// $('#pricelistbongsang_'+dataNomor).val('0');
					// $('#ongkoskirim_'+dataNomor).val(data.biaya_kirim);
					// $('#markupbongsang_'+dataNomor).attr('readonly', 'readonly');
					// $('#markupcetak_'+dataNomor).removeAttr('readonly');
					// $('#finalhargajual_'+dataNomor).val(data.pricelist_repostcetak);
					}
					
              
        }
		else
		{
		// $('#no_titik').val('');	
		// $('#lokasi').val('');	
		}
		
		});
		
		
		
		
		$(document).on('keypress keyup blur', '.IsiLength', function(){
		var dataNomor = $(this).data('nomor');
		var dataIni	  = $(this).val();
	    var dataWidth = $('#width2_'+dataNomor).val();
		var dataForm  = $('#pilihform_'+dataNomor).val();
		var dataDensity = $('#density_'+dataNomor).val();
		var hitung		= parseFloat(dataIni)*parseFloat(dataWidth)*parseFloat(dataDensity);
			
		
		if (dataForm =='0') {
		$('#kg_sheet_'+dataNomor).val(hitung);
        $('#kg_roll_'+dataNomor).val('0');		
		}
		else if (dataForm =='1') {
		$('#kg_sheet_'+dataNomor).val('0');	
		$('#kg_roll_'+dataNomor).val(hitung);	
		}
		// $('#kg_sheet_'+dataNomor).val('tes');
		
		});
		
		
		
		$(document).on('keypress keyup blur', '.IsiWidth', function(){
		var dataNomor = $(this).data('nomor');
		var dataIni	  = $(this).val();
	    var dataLength = $('#length2_'+dataNomor).val();
		var dataForm  = $('#pilihform_'+dataNomor).val();
		var dataDensity = $('#density_'+dataNomor).val();
		var hitung		= parseFloat(dataIni)*parseFloat(dataLength)*parseFloat(dataDensity);
			
		
		if (dataForm =='0') {
		$('#kg_sheet_'+dataNomor).val(hitung);
        $('#kg_roll_'+dataNomor).val('0');		
		}
		else if (dataForm =='1') {
		$('#kg_sheet_'+dataNomor).val('0');	
		$('#kg_roll_'+dataNomor).val(hitung);	
		}
		// $('#kg_sheet_'+dataNomor).val('tes');
		
		});
		
		
		
		$(document).on('keypress keyup blur', '.QtyPcs', function(){
		var dataNomor = $(this).data('nomor');
		var dataIni	  = $(this).val();
		var dataKg    = $('#kg_sheet_'+dataNomor).val();	
		
		var hitung		= parseFloat(dataIni)*parseFloat(dataKg);
		
		$('#total_kg_'+dataNomor).val(hitung);
        $('#qty_roll_'+dataNomor).val('0');			
		
		});
		
		
		
		$(document).on('keypress keyup blur', '.QtyRoll', function(){
		var dataNomor = $(this).data('nomor');
		var dataIni	  = $(this).val();
		var dataKg    = $('#kg_roll_'+dataNomor).val();	
		
		var hitung		= parseFloat(dataIni)*parseFloat(dataKg);
		
		$('#total_kg_'+dataNomor).val(hitung);
        $('#qty_sheet_'+dataNomor).val('0');			
		
		});
		
		
		
		
		
			var max_fields      = 10; //maximum input boxes allowed
			var wrapper         = $(".input_fields_wrap"); //Fields wrapper
			var add_button      = $(".add_field_button"); //Add button ID

			//console.log(persen);

			var x = 1; //initlal text box count
			$(add_button).click(function(e){ //on add input button click
			  e.preventDefault();
			  if(x < max_fields){ //max input box allowed
				x++; //text box increment
				var persen          = $("input:radio.radio_logo:checked").val();
				///console.log("a"+persen);
				if (persen == "persen") {
				  var logo = "%";
				}else {
				  var val = $("input:radio.radio_paycon:checked").val();
				   var logo = "Rp.";
				}


				$(wrapper).append('<div class="row">'+
				'<div class="col-xs-1">'+x+'</div>'+
				'<div class="col-xs-3">'+
				'<div class="input-group">'+
				'<span class="input-group-addon logo_currency">'+logo+'</span>'+
				'<input type="text" name="pembayaran[]"  class="form-control input-sm" value="100" required="" onkeyup="this.value = this.value.match(/^-?\\\d*[.]?\\\d*$/);get_payterm()">'+
				'</div>'+
				'</div>'+
				'<div class="col-xs-4"><input type="text" name="perkiraan_bayar[]" class="form-control pull-right "  id="datepickerxxr'+x+'"  value="<?= date('Y-m-d'); ?>"></div>'+
				'<a href="#" class="remove_field">Remove</a>'+
				'</div>'); //add input box
				$('#datepickerxxr'+x).datepicker({
				  format: 'dd-mm-yyyy',
				  autoclose: true
				});
			  }
			});

			$(wrapper).on("click",".remove_field", function(e){ //user click on remove text
			  e.preventDefault(); $(this).parent('div').remove(); x--;
			})
			
			
			///INPUT PERKIRAAN KIRIM
			
			
			var max_fields2      = 10; //maximum input boxes allowed
			var wrapper2         = $(".input_fields_wrap2"); //Fields wrapper
			var add_button2      = $(".add_field_button2"); //Add button ID

			//console.log(persen);

			var x2 = 1; //initlal text box count
			$(add_button2).click(function(e){ //on add input button click
			  e.preventDefault();
			  if(x2 < max_fields2){ //max input box allowed
				x2++; //text box increment
				
				$(wrapper2).append('<div class="row">'+
				'<div class="col-xs-1">'+x2+'</div>'+
				'<div class="col-xs-3">'+
				'<div class="input-group">'+
				'<input type="hidden" name="qty_kirim[]"  class="form-control input-sm" value="" onkeyup="this.value = this.value.match(/^-?\\\d*[.]?\\\d*$/);">'+
				'</div>'+
				'</div>'+
				'<div class="col-xs-4"><input type="text" name="perkiraan_kirim[]" class="form-control pull-right "  id="datepickerxxr'+x2+'"  value="<?= date('Y-m-d'); ?>"></div>'+
				'<a href="#" class="remove_field2">Remove</a>'+
				'</div>'); //add input box
				$('#datepickerxxr'+x2).datepicker({
				  format: 'dd-mm-yyyy',
				  autoclose: true
				});
			  }
			});

			$(wrapper2).on("click",".remove_field2", function(e){ //user click on remove text
			  e.preventDefault(); $(this).parent('div').remove(); x2--;
			})
		
	});
	
	  $('.datepickerxx').datepicker({
      format: 'dd-mm-yyyy',
      autoclose: true
    })
    
	 function changelogo(val){
      if ($("input:radio.radio_logo:checked").val() == 'nominal') {
        if(val=="dollar"){
          //$(".logo_currency").text("$");
          var logo = 'Rp.';
        }else if(val=="rmb"){
          //$(".logo_currency").text("$");
          var logo = 'Rp.';
        }else {
          //$(".logo_currency").text("Rp.");
          var logo = 'Rp.';
        }
      }
      return logo;
    }
	
    function startmutasi(id,nm,thickness,length,width,density){
       //  Cek Ada Data Gagal
	   var Cek_OK		= 1;
	   var Urut			= 1;
	   var total_row	= $('#list_item_mutasi').find('tr').length;
	   if(total_row > 0){
		  var kode_tr_akhir= $('#list_item_mutasi tr:last').attr('id');
		  var row_akhir		= kode_tr_akhir.split('_');
		  var Urut			= parseInt(row_akhir[1]) + 1;
		  $('#list_item_mutasi').find('tr').each(function(){
			  var kode_row	= $(this).attr('id');
			  var id_row	= kode_row.split('_');
			  var kode_produknya	= $('#kode_produk_'+id_row[1]).val();
			  if(id==kode_produknya){
				  Cek_OK	= 0;
			  }
		  });
	   }
	   if(Cek_OK==1){	   
			var idnya = "'"+id+"'";
			html='<tr id="tr_'+Urut+'">'
				+ '<td style="padding:3px;">'
				+ '<input type="text" class="form-control input-sm kode-produk" name="kode_produk[]" id="kode_produk_'+Urut+'" readonly value="'+id+'">'
				+ '</td>'
				+ '<td style="padding:3px;"><input type="text" class="form-control input-sm" name="nama_produk[]" id="nama_produk_'+Urut+'" readonly value="'+nm+'"></td>'
				+ '<td style="padding:3px;"><input type="text" class="form-control input-sm" name="thickness[]" id="thickness_'+Urut+'" style="text-align:center;" value="'+thickness+'" readonly></td>'
				+ '<td style="padding:3px;"><input type="text" class="form-control input-sm" name="length[]" id="length_'+Urut+'" style="text-align:center;" readonly value="'+length+'"></td>'
				+ '<td style="padding:3px;"><input type="text" class="form-control input-sm" name="width[]" id="width_'+Urut+'" style="text-align:center;" value="'+width+'" readonly></td>'
				+ '<td style="padding:3px;"><input type="text" class="form-control input-sm" name="density[]" id="density_'+Urut+'" style="text-align:center;" value="'+density+'" readonly></td>'
				+ '<td style="padding:3px;"><center><div class="btn-group" style="margin:0px;">'
				+ '<button type="button" onclick="deleterow('+Urut+','+idnya+')" id="delete-row" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i> Hapus</button>'
				+ '</div></center></td>'
				+ '</tr>';
			$("#tabel-detail-mutasi").append(html);
			$("#btn-"+id).removeClass('btn-warning');
			$("#btn-"+id).addClass('btn-danger');
			$("#btn-"+id).attr('disabled',true);
			$("#btn-"+id).text('Sudah');
	   }
        
    }
   
    function deleterow(tr,id){
        $('#tr_'+tr).remove();
        $("#btn-"+id).removeClass('btn-danger');
        $("#btn-"+id).addClass('btn-warning');
        $("#btn-"+id).attr('disabled',false);
        $("#btn-"+id).text('Pilih');
    }
	
	
	//SIMPAN DATA INQUIRY
	
    function saveinquiry(){
       
       var formdata = $("#form-header-inquiry,#form-detail-inquiry").serialize();
		
		  $.ajax({
          url: siteurl+"inquiry/saveNewInquiry",
          dataType : "json",
          type: 'POST',
          data: formdata,
            //alert(msg);
            success: function(msg){
                if(msg['save']=='1'){
                    var inquiry =msg['inquiry'];
					var tgl =msg['tgl'];
					var project =msg['project'];
					var customer =msg['customer'];
					var sales =msg['sales'];
					var pic =msg['pic'];
                    swal({
                      title: "Sukses!",
                      text: "Lanjutkan pengisian data detail inquiry",
                      type: "success",
                      showCancelButton: true,
                      confirmButtonColor: "Blue",
                      confirmButtonText: "Ya",
                      cancelButtonText: "Tidak",
                      closeOnConfirm: true,
                      closeOnCancel: true
                    },
                    function(isConfirm){
                      if (isConfirm) {
                        $('[href="#formsheet"]').tab('show');
                        $('#noinquiry').val(inquiry);
						$('#tgl').val(tgl);
						$('#cust').val(customer);
						$('#proj').val(project);
						$('#sales2').val(sales);
						$('#pic').val(pic);
                        load_detail(inquiry);
                       // ShowOtherButton();
                      } else {
                        window.location.reload();
                        cancel();
                      }
                    });
                } else {
                    swal({
                        title: "Gagal!",
                        text: "Data Gagal Di Simpan",
                        type: "error",
                        timer: 1500,
                        showConfirmButton: false
                    });
                };//alert(msg);
            },
            error: function(){
                swal({
                    title: "Gagal!",
                    text: "Ajax Data Gagal Di Proses",
                    type: "error",
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        });
    }
	
	
	//END SIMPAN INQUIRY
	
	
	
	 function saveform(){
       
       var formdata = $("#form-header-coilsheet,#form-detail-coilsheet,#form-term,#form-payment,#form-est-kirim").serialize();
		
		  $.ajax({
          url: siteurl+"inquiry/saveNewForm",
          dataType : "json",
          type: 'POST',
          data: formdata,
            //alert(msg);
            success: function(msg){
               if(msg['save']=='1'){
                    swal({
                        title: "Sukses!",
                        text: "Data Berhasil Di Simpan",
                        type: "success",
                        timer: 1500,
                        showConfirmButton: false
                    });
					
                   window.location.href = siteurl + "inquiry";
				   
                } else {
                    swal({
                        title: "Gagal!",
                        text: "Data Gagal Di Simpan",
                        type: "error",
                        timer: 1500,
                        showConfirmButton: false
                    });
                };//alert(msg);
            },
            error: function(){
                swal({
                    title: "Gagal!",
                    text: "Ajax Data Gagal Di Proses",
                    type: "error",
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        });
    }
	
	
	//END SIMPAN INQUIRY
	
	
	
	
    function savemutasi(){
		var asal = $('#cabang_asal').val();
        var tujuan = $('#cabang_tujuan').val();
       
        if(tujuan == "" || asal==''){
            swal({
                title: "Peringatan!",
                text: "Data harus lengkap",
                type: "warning",
                timer: 1500,
                showConfirmButton: false
            });
			return false
        }
		var total_row	= $('#list_item_mutasi').find('tr').length;
		if(total_row < 1){
			swal({
                title: "Peringatan!",
                text: "Data Item Mutasi belum dipilih. Mohon pilih data item terlebih dahulu..",
                type: "warning",
                timer: 1500,
                showConfirmButton: false
            });
			return false
		}
		
        swal({
          title: "Peringatan !",
          text: "Pastikan data sudah lengkap dan benar",
          type: "warning",
          showCancelButton: true,
          confirmButtonColor: "#DD6B55",
          confirmButtonText: "Ya, simpan!",
          cancelButtonText: "Batal!",
          closeOnConfirm: false,
          closeOnCancel: true
        },
        function(isConfirm){
			if(isConfirm) {
				var formdata = $("#form-header-mutasi,#form-detail-mutasi").serialize();
				$.ajax({
					url: siteurl+"internal/save_po",
					dataType : "json",
					type: 'POST',
					data: formdata,
					success: function(result){
						if(result.save=='1'){
							swal({
								title: "Sukses!",
								text: result['msg'],
								type: "success",
								timer: 1500,
								showConfirmButton: false
							});
							setTimeout(function(){
								window.location.href=siteurl+'internal/po';
							},1600);
						} else {
							swal({
								title: "Gagal!",
								text: "Data Gagal Di Simpan",
								type: "error",
								timer: 1500,
								showConfirmButton: false
							});
						};
					},
					error: function(){
						swal({
							title: "Gagal!",
							text: "Ajax Data Gagal Di Proses",
							type: "error",
							timer: 1500,
							showConfirmButton: false
						});
					}
				});
			}
        });
    
    }
	
	function get_pic(){
        var cust=$("#customer").val();
        $.ajax({
            type:"GET",
            url:siteurl+"inquiry/get_pic",
            data:"customer="+cust,
            success:function(html){
               $("#pic_cust").html(html);
            }
        });
    }
	
	 //Data Koli
    function load_detail(inquiry){
      $.ajax({
          type:"GET",
          url:siteurl+"inquiry/load_detail",
          data:"noinquiry="+inquiry,
          success:function(html){
              $("#list_detail").html(html);
          }
      })
    }
	
    function kembali_mutasi(){
        window.location.href = siteurl+"internal/po";
    }
    function filterAngka(a){
        if(!a.match(/^[0-9]+$/)){
            return 0;
        }else{
            return 1;
        }
    }
    function cekqtymutasi(no){
        var mutasi = parseInt($('#qty_mutasi_'+no).val());
        var avl = parseInt($('#stok_avl_'+no).val());
        if(filterAngka($('#qty_mutasi_'+no).val()) == 1){
            if(mutasi > avl){
                swal({
                    title: "Peringatan!",
                    text: "Qty Mutasi tidak boleh melebihi Stok Avl",
                    type: "warning",
                    timer: 2000,
                    showConfirmButton: false
                });
                $('#qty_mutasi_'+no).val(0);
            }
        }else{
            var ang = $('#qty_mutasi_'+no).val();
            $('#qty_mutasi_'+no).val(ang.replace(/[^0-9]/g,'')); 
        }
    }
	
	function get_logo(a){
      if (a == 'persen') {
        b = '%';
      }else {
        b = 'Rp.';
      }
      var x = document.getElementsByClassName("logo_currency");
      console.log(x);
      var i;
      for (i = 0; i < x.length; i++) {
          x[i].innerHTML= b;
      }
    }
	
	function get_driver_kbm(){
		var cabang_asal		= $('#cabang_asal').val();
		var kirim			= $('#tipekirim').val();
		if(kirim=='' || kirim==null){
			$('#list_kendaraan, #list_supir').empty();
		}else{
			 var Template   ='<span class="input-group-addon"><i class="fa fa-user"></i></span>';
			 var Kendaraan	='<span class="input-group-addon"><i class="fa fa-car"></i></span>';
			if(kirim=='SENDIRI' && cabang_asal !='' && cabang_asal !=null){
				var pecah_cabang	= cabang_asal.split('|');
				// AMBIL DATA DRIVER 
				var baseurl=base_url + active_controller +'/get_Driver/'+pecah_cabang[0];				
				$.ajax({
					'url'		: baseurl,
					'type'		: 'get', 
					'success'	: function(data){
						var datas	= $.parseJSON(data);
						Template	+='<select name="supir_do" id="supir_do" class="form-control input sm">';
						if(!$.isEmptyObject(datas)){
							  $.each(datas,function(key,value){
								  Template    +='<option value="'+key+'^_^'+value+'">'+value+'</option>';
							  });
						  }
					   Template   +='</select>';
					   $('#list_supir').html(Template);
					   $("#supir_do").select2({
						  placeholder: "Pilih",
						  allowClear: true
					   });
					}, 
					'error'		: function(data){
						alert('An error occured, please try again.');						
					}
				});
				
				// AMBIL DATA KBM				
				var baseurl=base_url + active_controller +'/get_Kendaraan/'+pecah_cabang[0];				
				$.ajax({
					'url'		: baseurl,
					'type'		: 'get', 
					'success'	: function(data){
						var datas	= $.parseJSON(data);
						Kendaraan	+='<select name="kendaraan_do" id="kendaraan_do" class="form-control input sm">';
						if(!$.isEmptyObject(datas)){
							  $.each(datas,function(key,value){
								  Kendaraan    +='<option value="'+key+'^_^'+value+'">'+value+'</option>';
							  });
						  }
					   Kendaraan   +='</select>';
					   $('#list_kendaraan').html(Kendaraan);
					   $("#kendaraan_do").select2({
						  placeholder: "Pilih",
						  allowClear: true
					   });
					}, 
					'error'		: function(data){
						alert('An error occured, please try again.');						
					}
				});
			}else{
				Template	+='<input type="text" name="supir_do" id="supir_do" class="form-control input-sm">';
				$('#list_supir').html(Template);

			   Kendaraan   +='<input type="text" name="kendaraan_do" id="kendaraan_do" class="form-control input-sm">';
			   $('#list_kendaraan').html(Kendaraan);
			}
		}
	}
	
	jQuery(document).on('keyup', '.Budget', function() {
    var val = this.value;
    val = val.replace(/[^0-9\.]/g, '');

    if (val != "") {
      valArr = val.split('.');
      valArr[0] = (parseInt(valArr[0], 10)).toLocaleString();
      val = valArr.join('.');
    }

    this.value = val;
	});

</script>
