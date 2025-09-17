<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
<div class="nav-tabs-custom">
    <ul class="nav nav-tabs">
        <li class="active"><a href="#inquiry" data-toggle="tab" aria-expanded="true" id="data"></a></li>
        <li class=""><a href="#formsheet" data-toggle="tab" aria-expanded="false" id="data_form"></a></li>
    </ul>
	 <?php if(empty($results['inquiry'])){
		}else{
	    foreach($results['inquiry'] AS $record){
		// print_r($record);
		// exit();
		
		?>
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
					  <input type="text" class="form-control input-sm" id="tgl_inquiry"  name="tgl_inquiry" placeholder="Tgl Inquiry"  value="<?= tgl_indo($record->tgl_inquiry) ?>" required>
					  <input type="hidden" class="form-control input-sm" id="no_inquiry"  name="no_inquiry" placeholder="no_inquiry"  value="<?= $results['noinquiry'] ?>" readonly required>
					  <input type="hidden" class="form-control input-sm" id="sample1"  name="sample1" placeholder="sample1"  value="<?= $record->sample ?>" readonly required>
					  <input type="hidden" class="form-control input-sm" id="instrument"  name="instrument" placeholder="instrument"  value="<?= $record->pay_instrument ?>" readonly required>
					  <input type="hidden" class="form-control input-sm" id="term"  name="term" placeholder="term"  value="<?= $record->pay_term ?>" readonly required>
					
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
						<option value="<?= $customer->id_customer ?>" <?= set_select('customer','0', isset($customer->id_customer) && $customer->id_customer ==  $record->id_customer); ?>>
						<?= ucfirst(strtolower($customer->name_customer))?>
						</option>
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
					  <?php foreach ($results['pic'] as $st){ 
						?>
					  <option value='<?= $st->id_pic ?>' <?= set_select('pic_cust', $st->id_pic, isset($st->name_pic) && $st->name_pic == $record->id_pic) ?> >
                           <?= ucfirst(strtolower($st->name_pic))?>
                       </option>
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
					  <input type="text" class="form-control input-sm" id="project"  name="project" placeholder="Project" value="<?= $record->project;?>" required>
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
						<option value="<?= $sales->id_karyawan ?>" <?= set_select('customer','0', isset($sales->id_karyawan) && $sales->id_karyawan ==  $record->id_sales); ?>>
						<?= ucfirst(strtolower($sales->nama_karyawan))?>
						</option>
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
					  <input type="text" class="form-control input-sm" id="ket_project"  name="ket_project" placeholder="Keterangan" value="<?= $record->ket_project;?>" required>
					</div>
				</div>
				</div>		

                </div>
                </div>
                </form>
            </div>
			
			<?php } }  ?>
       
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
                              <?php if(empty($results['form'])){
								}else{
								$numb = 0;
								foreach($results['form'] AS $form){ 
								$numb++;
								
							   ?>
							 
							<tr id='tr_<?= $numb; ?>' >
							<td style="padding:3px;"><input type="text" class="form-control input-sm kode-produk" name="kode_produk[]" id="kode_produk_<?php echo $numb ?>" readonly value="<?php echo set_value('kode_produk', isset($form->id_material) ? $form->id_material : ''); ?>" readonly></td>
							<td style="padding:3px;"><input type="text" class="form-control input-sm nama-produk" name="nama_produk[]" id="nama_produk_<?php echo $numb ?>" readonly value="<?php echo set_value('nama_produk', isset($form->nama_material) ? $form->nama_material : ''); ?>" readonly ></td>
							<td style="padding:3px;"><input type="text" class="form-control input-sm" name="thickness[]" id="thickness__<?php echo $numb ?>"style="text-align:center;" value="<?php echo set_value('thickness', isset($form->thickness) ? $form->thickness : ''); ?>" readonly ></td>
							<td style="padding:3px;"><input type="text" class="form-control input-sm" name="length[]" id="length_<?php echo $numb ?>"style="text-align:center;" readonly value="<?php echo set_value('length', isset($form->length) ? $form->length : ''); ?>" readonly ></td>
							<td style="padding:3px;"><input type="text" class="form-control input-sm" name="width[]" id="width_<?php echo $numb ?>" style="text-align:center;" value="<?php echo set_value('width', isset($form->width) ? $form->width : ''); ?>" readonly ></td>
							<td style="padding:3px;"><input type="text" class="form-control input-sm" name="density[]" id="density__<?php echo $numb ?>"style="text-align:center;" value="<?php echo set_value('density', isset($form->density) ? $form->density : ''); ?>" readonly ></td>
							<td style="padding:3px;"><center><div class="btn-group" style="margin:0px;">
							<button type="button" onclick="deleterow(<?= $numb; ?> )" id="delete-row" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i> Hapus</button>
							</div></center></td>
							</tr>
								<?php }
							}
							?>
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
						<option value='1' >Ya</option>		
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
					<input type="text" class="form-control input-sm" id="pay_term"  name="pay_term" placeholder="pay_term"  value=""  required>
					
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
					<div class='box-tool pull-right'>
					<?php
						echo form_button(array('type'=>'button','class'=>'btn btn-md btn-success','value'=>'back','content'=>'Add','id'=>'add-payment'));
					?>
					</div>
					<table class='table table-bordered table-striped'>
						<thead>
							<tr class='bg-blue'>
								<td align='center'><b>Tipe Payment</b></td>	
								<td align='center'><b>Nominal </b></td>							
								<td align='center'><b>Perkiraan Bayar</b></td>
								<td align='center'><b>Action</b></td>
							</tr>
							
						</thead>
						<tbody id='list_payment'>
							<?php
								if(empty($results['payment'])){
								}else{
								$loop = 0;
								foreach($results['payment'] AS $pay){ 
								$loop++;
								
								
								
								echo"<tr id='tr_".$loop."'>";
									        echo"<td class='text-center'>";					
												echo form_dropdown('data1['.$loop.'][tipe_payment]',$results['payment_type'],'$pay->tipe_payment', array('id'=>'data1'.$loop.'_tipe_payment','class'=>'form-control input-sm'));
											echo"</td>";
											echo"<td>";
												echo form_input(array('name'=>'data1['.$loop.'][pembayaran]','id'=>'data1'.$loop.'_pembayaran','class'=>'form-control input-sm','autocomplete'=>'off'),$pay->nominal);
											echo"</td>";
											echo"<td>";
												echo form_input(array('name'=>'data1['.$loop.'][perkiraan_bayar]','id'=>'data1'.$loop.'_perkiraan_bayar','class'=>'form-control input-sm','autocomplete'=>'off'),tgl_indo($pay->perkiraan_bayar) );
											echo"</td>";
											echo"<td class='text-center'>";
												echo"<button type='button' class='btn btn-sm btn-danger' title='Hapus Data' data-role='qtip' onClick='return DelItem(".$loop.");'><i class='fa fa-trash-o'></i></button>";
											echo"</td>";
									echo"</tr>";
								}
							}
							
							?>
						</tbody>
					</table>
					</div>
				   </div>
                  </div>				
				  </form>
				  <form id="form-est-kirim" method="post">
				  <div class="form-horizontal">
                  <div class="box-body">				 
				  <div class="col-sm-8">
				   <b>Estimasi Pengiriman</b>
				   <div class='box-tool pull-right'>
					<?php
						echo form_button(array('type'=>'button','class'=>'btn btn-md btn-success','value'=>'back','content'=>'Add','id'=>'add-kirim'));
					?>
					</div>
					<table class='table table-bordered table-striped'>
						<thead>
							<tr class='bg-blue'>
								<td align='center'><b>Qty Kirim </b></td>							
								<td align='center'><b>Perkiraan Kirim</b></td>
								<td align='center'><b>Action</b></td>
							</tr>
							
						</thead>
						<tbody id='list_kirim'>
							<?php
								if(empty($results['delivery'])){
								}else{
								$loop = 0;
								foreach($results['delivery'] AS $dev){ 
								$loop++;
								
							   
									echo"<tr id='tr_".$loop."'>";
									       	echo"<td>";
												echo form_input(array('name'=>'data2['.$loop.'][qty_kirim]','id'=>'data2_'.$loop.'_qty_kirim','class'=>'form-control input-sm','autocomplete'=>'off'),$dev->qty_kirim);
											echo"</td>";
											echo"<td>";
												echo form_input(array('name'=>'data2['.$loop.'][perkiraan_kirim]','id'=>'data2_'.$loop.'_perkiraan_kirim','class'=>'form-control input-sm','autocomplete'=>'off'),tgl_indo($dev->perkiraan_kirim));
											echo"</td>";
											echo"<td class='text-center'>";
												echo"<button type='button' class='btn btn-sm btn-danger' title='Hapus Data' data-role='qtip' onClick='return DelItem2(".$loop.");'><i class='fa fa-trash-o'></i></button>";
											echo"</td>";
									echo"</tr>";
								}
							}
							
							?>
						</tbody>
					</table>						  
				  </form>
				</div> 
                </div> 
			    </div> 
			</div>			
		<div class="text-right">
		  <div class="box box-primary"> 
			<div class="box-body">
					<button class="btn btn-primary" type="button" onclick="saveform()">
					<i class="fa fa-save"></i><b> Ubah Data</b>
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

<script type="text/javascript">
	//$('#input-kendaraan').hide();
	var base_url			= '<?php echo base_url(); ?>';
	var active_controller	= '<?php echo($this->uri->segment(1)); ?>';
	var cabang_user			= '<?php echo $cabs_user;?>';
	var arr_cabang			= <?php echo json_encode($Arr_Cabang);?>;
    var data_pay	        = <?php echo json_encode($results['payment_type']);?>;
				
	$(document).ready(function(){
		
		
		$("#tgl_inquiry").datepicker({
			format : "yyyy-mm-dd",
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
					$('#sambungan_'+dataNomor).attr('readonly', 'readonly');	
					$('#max_sambungan_'+dataNomor).attr('readonly', 'readonly');	
					
					}
                    else if(dataIni == "1"){
                    $('#width_'+dataNomor).attr('readonly', 'readonly');
                    $('#inner_'+dataNomor).removeAttr('readonly');	
                    $('#kg_sheet_'+dataNomor).attr('readonly', 'readonly');	
                    $('#kg_roll_'+dataNomor).removeAttr('readonly');
					$('#qty_pcs_'+dataNomor).attr('readonly', 'readonly');	
                    $('#qty_roll_'+dataNomor).removeAttr('readonly');
                    $('#sambungan_'+dataNomor).removeAttr('readonly');
					$('#max_sambungan_'+dataNomor).removeAttr('readonly');					
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
		
		});
		
		
		
		$(document).on('keypress keyup blur', '.QtyPcs', function(){
		var dataNomor = $(this).data('nomor');
		var dataIni	  = $(this).val();
		var dataKg    = $('#kg_sheet_'+dataNomor).val();	
		
		var hitung		= parseFloat(dataIni)*parseFloat(dataKg)
		
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
				  format: 'yyyy-mm-dd',
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
				'<input type="text" name="qty_kirim[]"  class="form-control input-sm" value="" required="" onkeyup="this.value = this.value.match(/^-?\\\d*[.]?\\\d*$/);">'+
				'</div>'+
				'</div>'+
				'<div class="col-xs-4"><input type="text" name="perkiraan_kirim[]" class="form-control pull-right "  id="datepickerxxr'+x2+'"  value="<?= date('Y-m-d'); ?>"></div>'+
				'<a href="#" class="remove_field2">Remove</a>'+
				'</div>'); //add input box
				$('#datepickerxxr'+x2).datepicker({
				  format: 'yyyy-mm-dd',
				  autoclose: true
				});
			  }
			});

			$(wrapper2).on("click",".remove_field2", function(e){ //user click on remove text
			  e.preventDefault(); $(this).parent('div').remove(); x2--;
			})
			
			
			$('#add-payment').click(function(){
			var jumlah	=$('#list_payment').find('tr').length;
			if(jumlah==0 || jumlah==null){
				var ada		= 0;
				var loop	= 1;
			}else{
				var nilai		= $('#list_payment tr:last').attr('id');
				var jum1		= nilai.split('_');
				var loop		= parseInt(jum1[1])+1;
			}
			
			Template	='<tr id="tr_'+loop+'">';
				Template	+='<td align="center">';
				Template	+='<select name="data1['+loop+'][tipe_payment]" id="data1_'+loop+'_tipe_payment" class="form-control input-sm chosen-select">';
					Template	+='<option value="">Select An Option</option>';
					if(!$.isEmptyObject(data_pay)){
						$.each(data_pay,function(key,value){
							Template	+='<option value="'+key+'">'+value+'</option>';
						});
					}
					
				Template	+='</select>';
			Template	+='</td>';	
				Template	+='<td align="left">';
					Template	+='<input type="text" class="form-control input-sm" name="data1['+loop+'][pembayaran]" id="data1_'+loop+'_pembayaran" label="FALSE" div="FALSE">';
				Template	+='</td>';
				Template	+='<td align="left">';
					Template	+='<input type="text" class="form-control input-sm" name="data1['+loop+'][perkiraan_bayar]" id="data1_'+loop+'_perkiraan_bayar" label="FALSE" div="FALSE" data-role="tglbayar">';
				Template	+='</td>';
				
			
			Template	+='<td align="center"><button type="button" class="btn btn-sm btn-danger" title="Hapus Data" data-role="qtip" onClick="return DelItem('+loop+');"><i class="fa fa-trash-o"></i></button></td>';
			Template	+='</tr>';
			$('#list_payment').append(Template);
			$('input[data-role="tglbayar"]').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true			
			});
			});
		
			
			$('#add-kirim').click(function(){
			var jumlah	=$('#list_kirim').find('tr').length;
			if(jumlah==0 || jumlah==null){
				var ada		= 0;
				var loop	= 1;
			}else{
				var nilai		= $('#list_kirim tr:last').attr('id');
				var jum1		= nilai.split('_');
				var loop		= parseInt(jum1[1])+1;
			}
			
			Template	='<tr id="tr_'+loop+'">';
				Template	+='<td align="left">';
					Template	+='<input type="text" class="form-control input-sm" name="data2['+loop+'][qty_kirim]" id="data2_'+loop+'_qty_kirim" label="FALSE" div="FALSE">';
				Template	+='</td>';
				Template	+='<td align="left">';
					Template	+='<input type="text" class="form-control input-sm" name="data2['+loop+'][perkiraan_kirim]" id="data2_'+loop+'_perkiraan_kirim" label="FALSE" div="FALSE" data-role="tglkirim">';
				Template	+='</td>';
				
			
			Template	+='<td align="center"><button type="button" class="btn btn-sm btn-danger" title="Hapus Data" data2-role="qtip" onClick="return DelItem2('+loop+');"><i class="fa fa-trash-o"></i></button></td>';
			Template	+='</tr>';
			$('#list_kirim').append(Template);
			$('input[data-role="tglkirim"]').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true				
			});
		
		});
		
			
			
		
	});
	
	  $('.datepickerxx').datepicker({
      format: 'yyyy-mm-dd',
      autoclose: true
    })
    
	 function changelogo(val){
      if ($("input:radio.radio_logo:checked").val() == 'nominal') {
        if(value=="dollar"){
          //$(".logo_currency").text("$");
          var logo = 'Rp.';
        }else if(value=="rmb"){
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
	
	function DelItem(id){
		$('#list_payment #tr_'+id).remove();
		
	}
	function DelItem2(id){
		$('#list_kirim #tr_'+id).remove();
		
	}
	
	
	//SIMPAN DATA INQUIRY
	
    function saveinquiry(){
       
       var formdata = $("#form-header-inquiry,#form-detail-inquiry").serialize();
		
		  $.ajax({
          url: siteurl+"inquiry/saveEditInquiry",
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
					var sample =msg['sample'];
					var instrument =msg['instrument'];
					var term =msg['term'];
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
					    $('#sample').val(sample);
					    $('#pay_instrument').val(instrument);
						$('#pay_term').val(term);
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
          url: siteurl+"inquiry/saveEditForm",
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
          url:siteurl+"inquiry/load_detail_edit",
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
</script>
