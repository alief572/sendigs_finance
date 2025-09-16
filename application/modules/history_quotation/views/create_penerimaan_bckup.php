<?php
$inv          =  $this->db->query("SELECT * FROM tr_invoice_header WHERE no_invoice = '$no_inv' ")->row();
$alamat_cust =  $this->db->query("SELECT * FROM master_customer WHERE id_customer = '$inv->id_customer'")->row();
?>
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
 </form>
 
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
		
		// else if ($('#kurs').val()=="1") {
          // swal({
            // title	: "KURS HARUS DI UPDATE!",
            // text	: "SILAHKAN UPDATE KURS TERLEBIH DAHULU!",
            // type	: "warning",
            // timer	: 3000,
            // showCancelButton	: false,
            // showConfirmButton	: false,
            // allowOutsideClick	: false
			
          // });
		  
		   // $('#kurs').focus();
        // }
		
		// else if ($('#jenis_invoice').val()=="uang muka" && $('#um_persen').val()=="") {
          // swal({
            // title	: "PERSENTASE UM HARUS DIISI!",
            // text	: "SILAHKAN ISI PERSENTASE UM TERLEBIH DAHULU!",
            // type	: "warning",
            // timer	: 3000,
            // showCancelButton	: false,
            // showConfirmButton	: false,
            // allowOutsideClick	: false
			
          // });
		  
		   // $('#um_persen').focus();
        // }
		
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

</script>