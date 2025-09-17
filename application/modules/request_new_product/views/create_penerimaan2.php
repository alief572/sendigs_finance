<?php
$inv          =  $this->db->query("SELECT * FROM tr_invoice_header WHERE no_invoice = '$no_inv' ")->row();
$alamat_cust =  $this->db->query("SELECT * FROM master_customer WHERE id_customer = '$inv->id_customer'")->row();
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
<form method="POST" id="form_proses">
<div class="nav-tabs-salesorder">
     <div class="tab-content">
        <div class="tab-pane active" id="salesorder">
			
            <div class="box box-primary">
               	<?php //print_r($kode_customer)?>
                <div class="box-body">
                    <div class="col-sm-6 form-horizontal">
					    <div class="row">
                          <div class="form-group">
                              <label for="no_ipp" class="col-sm-4 control-label">No Invoice </font></label>
                              <div class="col-sm-6">
                                  <input type="text" name="no_invoice" id="no_invoice" value="<?php echo $inv->no_invoice ?>" class="form-control input-sm" readonly>
                              </div>

                          </div>
                        </div>
						<div class="row">
                          <div class="form-group">
                              <label for="no_ipp" class="col-sm-4 control-label">Tgl Invoice </font></label>
                              <div class="col-sm-6">
                               
                                 <input type="text" name="tgl_invoice" id="tgl_invoice" value="<?php echo $inv->tgl_invoice ?>" class="form-control input-sm" readonly>
                                
                              </div>

                          </div>
                        </div>
						<div class="row">
                          <div class="form-group ">
                            <?php
                            $tglinv=date('Y-m-d');
                            ?>
                            <label for="tgl_bayar" class="col-sm-4 control-label">Tgl Bayar :</label>
                            <div class="col-sm-6">
                             
                                <input type="text" name="tgl_bayar" id="tgl_bayar" class="form-control input-sm" value="<?php echo date('Y-m-d') ?>" readonly>
                             
                            </div>
                          </div>

                        </div>
                        <div class="row">
                          <div class="form-group">
                              <label class="col-sm-4 control-label">Nama Customer </font></label>
                              <div class="col-sm-6">
                               
                                  <input type="hidden" name="id_customer" id="id_customer" class="form-control input-sm" value="<?php  echo $inv->id_customer?>" readonly>
                             
                                  <input type="text" name="nm_customer" id="nm_customer" class="form-control input-sm" value="<?php  echo $inv->nm_customer?>" readonly>
                               
                              </div>

                          </div>
                        </div>
                        <div class="row">
                          <div class="form-group">
                            <label for="alamat" class="col-sm-4 control-label">Alamat Customer </font></label>
                            <div class="col-sm-6" >                             
                             
                              <textarea name="alamatcustomer" class="form-control input-sm" id="alamat" height=100 readonly><?php echo $alamat_cust->alamat ?></textarea>
                              
                            </div>
                          </div>

                        </div>
						
						<div class="row">
                          <div class="form-group">
                              <label for="jenis_invoice" class="col-sm-4 control-label">Jenis Invoice </font></label>
                              <div class="col-sm-6">
                               
                                    <select id="jenis_invoice" name="jenis_invoice" class="form-control input-sm" style="width: 100%;" tabindex="-1" required readonly>
                                    <?php
								    $jenis = $inv->jenis_invoice;
									?>
									<option value="TR-01" <?php echo ($jenis == 'TR-01') ? "selected": "" ?>>Uang Muka</option>
									<option value="TR-02"  <?php echo ($jenis == 'TR-02') ? "selected": "" ?>>Progress</option>
									<option value="TR-03"   <?php echo ($jenis == 'TR-03') ? "selected": "" ?>>Retensi</option>
									
									</select>
                                
                              </div>

                          </div>
                        </div>
						
						<div class="row">
                          <div class="form-group">
                              <label for="jenis_invoice" class="col-sm-4 control-label">Pilih Bank </font></label>
                              <div class="col-sm-6">
                                 <select class="form-control input-sm" name="bank" id="bank">
									<option value="">Pilih Bank</option>
									<?php
									foreach($datbank as $kb=>$vb){
										$selected='';
										if(isset($data)){
											if($kb.'|'.$vb==$data->kd_bank.'|'.$data->nm_bank) $selected=' selected';
										}
									?>
									<option value="<?php echo $kb.'|'.$vb?>" <?=$selected;?> ><?php echo $vb?></option>
									<?php } ?>
								  </select>
                                
                              </div>

                          </div>
                        </div>
						
						<div class="row">
                          <div class="form-group">
                              <label for="jenis_invoice" class="col-sm-4 control-label">Jenis PPH</font></label>
                              <div class="col-sm-6">
                                  <?php
									$pphpenjualan[0]	= 'Select An Option';
									echo form_dropdown('jenis_pph',$pphpenjualan, (isset($data)?$data->jenis_pph:'0'), array('id'=>'jenis_pph','class'=>'form-control'));
								  ?>
                                
                              </div>

                          </div>
                        </div>
						<div class="form-group ">
                            <label for="tgldo" class="col-sm-4 control-label">Kurs </label>
                            <div class="col-sm-6" style="padding-top: 8px;">
                             

                              <input type="text" name="kurs" class="form-control input-sm" id="kurs" value="1" > 
                              

                            </div>
                        </div>
						
				   </div>
                   <div class="col-sm-6 form-horizontal">
				        
				        
                          <div class="form-group">
                            <label for="ket_bayar" class="col-sm-4 control-label">Keterangan Pembayaran </font></label>
                            <div class="col-sm-6" >                             
                             
                              <textarea name="ket_bayar" class="form-control input-sm" id="ket_bayar" height=100></textarea>
                              
                            </div>
                          </div>

                       
					    <div class="form-group ">
                            <label for="idsalesman" class="col-sm-4 control-label">Total Invoice </label>
                            <div class="col-sm-6" style="padding-top: 8px;">
                             
                              <input type="text" name="total_invoice" class="form-control input-sm" id="total_invoice" value="<?php echo number_format($inv->total_invoice,2)?>" readonly>
                             

                            </div>
                        </div>
                        <div class="form-group ">
                            <label for="idsalesman" class="col-sm-4 control-label">Total Piutang </label>
                            <div class="col-sm-6" style="padding-top: 8px;">
                              <?php 
							  $totpiutang = $inv->total_invoice - $inv->total_bayar;
							  ?>
                              <input type="text" name="total_piutang" class="form-control input-sm" id="total_piutang" value="<?php echo  number_format($totpiutang,2)?>" readonly>
                             

                            </div>
                        </div>
						<div class="form-group ">
                            <label for="idsalesman" class="col-sm-4 control-label">Jumlah Penerimaan </label>
                            <div class="col-sm-6" style="padding-top: 8px;">                              
                              <input type="text" name="total_bank" class="form-control input-sm" id="total_bank" value="0">             

                            </div>
                        </div>
                       <div class="form-group ">
                            <label for="idsalesman" class="col-sm-4 control-label">Biaya Administrasi </label>
                            <div class="col-sm-6" style="padding-top: 8px;">                              
                              <input type="text" name="biaya_adm" class="form-control input-sm" id="biaya_adm" value="0">             

                            </div>
                        </div>
						<div class="form-group ">
                            <label for="idsalesman" class="col-sm-4 control-label">PPH </label>
                            <div class="col-sm-6" style="padding-top: 8px;">                              
                            <input type="text" name="biaya_pph" class="form-control input-sm" id="biaya_pph" value="0">             

                            </div>
                        </div>
						<div class="form-group ">
                            <label for="idsalesman" class="col-sm-4 control-label">Total Penerimaan </label>
                            <div class="col-sm-6" style="padding-top: 8px;">                              
                            <input type="text" name="total_terima" class="form-control input-sm" id="total_terima" value="0">             

                            </div>
                        </div>
						<div class="form-group">
                              <label for="template" class="col-sm-4 control-label">Pilih Template Jurnal</font></label>
                              <div class="col-sm-6">
                                  <?php
									$template[0]	= 'Select An Option';
									echo form_dropdown('template',$template, (isset($data)?$data->template:'0'), array('id'=>'jenis_pph','class'=>'form-control'));
								  ?>
                                
                              </div>

                          </div>                   
						
                        
                </div> 

            </div>
		</div>
	</div>
   </div>
</div>
</form>


<div class="box box-default ">
	<div class="box-header">
		<h4 class="box-title">Detail</h4>
		<div class="box-tools pull-right">
			<button class="btn btn-sm btn-success" id="tambah" type="button" style="width: 100%;">
				<i class="fa fa-plus"></i> Add Invoice
			</button>
		</div>
	</div>
    <div class="box-body">
        <form id="form-detail-mutasi" method="post">
            <table class="table table-bordered" width="100%" id="tabel-detail-mutasi">
				<thead>
					<tr class="bg-blue">
						<th class="text-center">Kode Produk</th>
						<th class="text-center">Nama Produk</th>
						<th class="text-center">Stok Avl</th>
						<th class="text-center">Stok Real</th>
						<th class="text-center">Qty</th>
						<th class="text-center">Action</th>
					</tr>
				</thead>
				<tbody id="list_item_mutasi">
				
				</tbody>
            </table>
        </form>
    </div>
</div>

<div class="modal modal-primary" id="dialog-data-stok" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel"><span class="fa fa-file-pdf-o"></span>&nbsp;Data Invoice</h4>
      </div>
      <div class="modal-body" id="MyModalBodyStok" style="background: #FFF !important;color:#000 !important;">
		
          <table class="table table-bordered" width="100%" id="list_item_stok">
              <thead>
                  <tr>
                      <th width="75%">No Invoice</th>
                      <th width="10%">Nama Customer</th>
                      <th width="10%">Total Invoice</th>
                      <th width="2%" class="text-center">Aksi</th> 
                  </tr>
              </thead>
              <tbody>
                  <?php	
                  $invoice = $this->db->query("SELECT * FROM tr_invoice_header")->result();
                  				  
				  if($invoice){
					foreach($invoice as $ks=>$vs){
                  ?>
						  <tr>
							  <td><?php echo $vs->no_invoice ?></td>
							  <td><center><?php echo $vs->nm_customer ?></center></td>
							  <td><center><?php echo $vs->total_invoice_idr ?></center></td>
							  <td>
								<center>
									<button id="btn-<?php echo $vs->no_invoice?>" class="btn btn-warning btn-sm" type="button" onclick="startmutasi('<?php echo $vs->no_invoice?>','<?php echo $vs->nm_customer?>','<?php echo $vs->total_invoice_idr?>','<?php echo $vs->total_invoice_idr?>')">
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

<div class="text-right">
  <div class="box active"> 
    <div class="box-body">
        <button class="btn btn-danger" onclick="kembali_mutasi()">
           <i class="fa fa-refresh"></i><b> Kembali</b>
        </button>
        <button class="btn btn-primary" type="button" onclick="savemutasi()">
            <i class="fa fa-save"></i><b> Simpan Data Penerimaan</b>
        </button>
    </div>
  </div>
</div>

<div class="text-right">
  <div class="box active">
    <div class="box-body">
        <button class="btn btn-danger" onclick="kembali_inv()" type="button">
           <i class="fa fa-refresh"></i><b> Kembali</b>
        </button>
        <button class="btn btn-primary" type="button" id="proses_inv">
            <i class="fa fa-save"></i><b> Simpan Data Penerimaan</b>
        </button>
    </div>
  </div>
</div>
 
 <script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
 <script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>
 <script>
	$(document).ready(function(){
		swal.close();
		
		$('#proses_inv').click(function(e){
			  e.preventDefault();
        if ($('#tgl_bayar').val() == "") {
          swal({
            title	: "TANGGAL BAYAR TIDAK BOLEH KOSONG!",
            text	: "ISI TANGGAL INVOICE!",
            type	: "warning",
            timer	: 3000,
            showCancelButton	: false,
            showConfirmButton	: false,
            allowOutsideClick	: false
          });
        }
		
		else {

          swal({
            title: "Anda Yakin?",
            text: "You will not be able to process again this data!",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-danger",
            confirmButtonText: "Ya Lanjutkan",
            cancelButtonText: "Batal",
            closeOnConfirm: false,
            closeOnCancel: false,
            showLoaderOnConfirm: true
          },
          function(isConfirm) {
            if (isConfirm) {

              //var formData 	= $('#form_proses').serialize();
              var formData 	=new FormData($('#form_proses')[0]);
              //console.log(formData);return false;
              var baseurl=base_url + 'penerimaan/save_penerimaan';
              //console.log(baseurl);return false;
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
                      timer	: 15000,
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
                        timer	: 10000,
                        showCancelButton	: false,
                        showConfirmButton	: false,
                        allowOutsideClick	: false
                      });
                    }else{
                      swal({
                        title	: "Save Failed!",
                        text	: data.pesan,
                        type	: "warning",
                        timer	: 10000,
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
                    type				: "error",
                    timer				: 7000,
                    showCancelButton	: false,
                    showConfirmButton	: false,
                    allowOutsideClick	: false
                  });
                }
              });
            } else {
              swal("Batal Proses", "Data bisa diproses nanti", "error");
              return false;
            }
          });
        }
		});
		
	});
	
	function kembali_inv(){
        window.location.href = base_url + active_controller;
    }
	
	$(document).on('blur', '#total_bank', function(){
		
		var dataTotal	  = $(this).val().split(",").join("");		
		var adm			  = $('#biaya_adm').val().split(",").join("");							
        var pph			  =	$('#biaya_pph').val().split(",").join("");
		var totalBank     = parseFloat(dataTotal).toFixed(2);
		var Total         = parseFloat(dataTotal-adm-pph).toFixed(2);
		
		
		$('#total_bank').val(num2(totalBank));
		$('#total_terima').val(num2(Total));
			

	});
	
	$(document).on('blur', '#biaya_adm', function(){
		
		var dataTotal	  = $(this).val().split(",").join("");
		var totalBank     = parseFloat(dataTotal).toFixed(2);
		var bank1		  = $('#total_bank').val().split(",").join("");							
        var pph1			  =	$('#biaya_pph').val().split(",").join("");
		
		
		var pph           = pph1.replace(",", "");
		var bank          = bank1.replace(",", "");
		var totalBank     = parseFloat(dataTotal).toFixed(2);
		var Total         = parseFloat(bank-dataTotal-pph).toFixed(2);
		
		console.log(bank);
		console.log(pph);
		console.log(dataTotal);
		console.log(Total);
		
		$('#biaya_adm').val(num2(totalBank));
		$('#total_terima').val(num2(Total));
		 
			

	});
	
	$(document).on('blur', '#biaya_pph', function(){
		
		var dataTotal	  = $(this).val().split(",").join("");
		var bank		    = $('#total_bank').val().split(",").join("");							
    var adm			    =	$('#biaya_adm').val().split(",").join("");
		
		var totalBank     = parseFloat(dataTotal).toFixed(2);
		var Total         = parseFloat(bank-dataTotal-adm).toFixed(2);
		$('#biaya_pph').val(num2(totalBank));
		$('#total_terima').val(num2(Total));
			

	});
	
	$(document).on('blur', '#total_terima', function(){
		
		var dataTotal	  = $(this).val().split(",").join("");
		var totalBank     = parseFloat(dataTotal).toFixed(2);
		
		$('#total_terima').val(num2(totalBank));
		 
			

	});
	
	function num(n) {
      return (n).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }
	
	function num2(n) {
      return (n).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }
	
	function num3(n) {
      return (n).toFixed(2);
    }
	
	$("#tambah").click(function(){
		$('#dialog-data-stok').modal('show');
        $("#list_item_stok").DataTable({lengthMenu:[10,15,25,30]}).draw();		
	});
	
	function startmutasi(id,nm,avl,real){
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
				+ '<td style="padding:3px;"><input type="text" class="form-control input-sm" name="stok_avl[]" id="stok_avl_'+Urut+'" style="text-align:center;" readonly value="'+avl+'"></td>'
				+ '<td style="padding:3px;"><input type="text" class="form-control input-sm" name="stok_real[]" id="stok_real'+Urut+'" style="text-align:center;" value="'+real+'" readonly></td>'
				+ '<td style="padding:3px;"><input type="text" class="form-control input-sm" name="qty_mutasi[]" id="qty_mutasi_'+Urut+'" style="text-align:center;" ></td>'
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

</script>