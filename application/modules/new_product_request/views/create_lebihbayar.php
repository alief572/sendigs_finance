<?php
$inv          =  $this->db->query("SELECT * FROM tr_invoice_header WHERE no_invoice = '$no_inv' ")->row();
$alamat_cust =  $this->db->query("SELECT * FROM master_customer WHERE id_customer = '$inv->id_customer'")->row();
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
 <form id="form-header-mutasi2" method="post">
<div class="nav-tabs-salesorder">
     <div class="tab-content">
        <div class="tab-pane active" id="salesorder">			
            <div class="box box-primary">
               	<?php //print_r($kode_customer)?>
                <div class="box-body">
                    <div class="col-sm-6 form-horizontal">
					    
						<div class="row">
                          <div class="form-group">
                              <label for="jenis_invoice" class="col-sm-4 control-label">Pilih Bank </font></label>
                              <div class="col-sm-6">
                                 <select class="form-control input-sm bank" name="bank1" id="bank1">
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
                              <label for="jenis_invoice" class="col-sm-4 control-label">Tanggal </font></label>
                              <div class="col-sm-6">
								<input type="text" class="form-control input-sm tanggal" name="tanggal" id="tanggal">
								
                              </div>
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
		
	</div>
    <div class="box-body">
        <form id="form-detail-mutasi2" method="post">
            <table class="table table-bordered" width="100%" id="tabel-detail-mutasi2">
				<thead>
					<tr class="bg-blue">
						<th class="text-center">Keterangan</th>
						<th class="text-center">Total Penerimaan</th>
					</tr>
				</thead>
				<tbody>	
				 <tr>
                 <td style="padding:3px;"><textarea type="text" class="form-control input-sm" name="keterangan" id="keterangan" ></textarea></td>
				<td style="padding:3px;"><input type="text" class="form-control input-sm totalpenerimaan" name="totalpenerimaan" id="totalpenerimaan" style="text-align:right;"></td>
                 </tr>				
				</tbody>
            </table>
        </form>
    </div>
</div>

<div class="text-right">
  <div class="box active"> 
    <div class="box-body">
        <button class="btn btn-primary" type="button" onclick="savemutasi()"> 
            <i class="fa fa-save"></i><b> Simpan Data Lebih Bayar</b>
        </button>
    </div>
  </div>
</div>
 <script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
 <script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>
 <script>
	$(document).ready(function(){
		swal.close();
	});	
	 function savemutasi(){
	    if ($('#bank1').val() == "") {
          swal({
            title	: "BANK TIDAK BOLEH KOSONG!",
            text	: "PILIH BANK!",
            type	: "warning",
            timer	: 3000,
            showCancelButton	: false,
            showConfirmButton	: false,
            allowOutsideClick	: false
          });
        	
        } else {		
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
				var formdata = $("#form-header-mutasi2,#form-detail-mutasi2").serialize();
				$.ajax({
					url: siteurl+"penerimaan/save_lebihbayar",
					dataType : "json",
					type: 'POST',
					data: formdata,
					success: function(data){
						if(data.status == 1){
						swal({
						  title	: "Save Success!",
						  text	: data.pesan,
						  type	: "success",
						  timer	: 1000,
						  showCancelButton	: false,
						  showConfirmButton	: false,
						  allowOutsideClick	: false
						});
						window.location.href = base_url + active_controller+'modal_detail_invoice/';
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
					error: function(){
						swal({
							title: "Gagal!",
							text: "Batal Proses, Data bisa diproses nanti",
							type: "error",
							timer: 1500,
							showConfirmButton: false
						});
					}
				});
			}
        });		
		}    
    }
	
	function kembali_inv(){
        window.location.href = base_url + active_controller;
    }
	
	$(document).on('blur', '#total_bank', function(){

		var dataTotal	  = $(this).val().split(",").join("");		
		var adm			  = $('#biaya_adm').val().split(",").join("");							
        var pph			  =	$('#biaya_pph').val().split(",").join("");
		var totalBank     = parseFloat(dataTotal).toFixed(0);
		var Total         = parseFloat(dataTotal-adm-pph).toFixed(0);
		$('#total_bank').val(num2(totalBank));
		$('#total_terima').val(num2(Total));
	});
	
	$(document).on('blur', '#biaya_adm', function(){
		
		var dataTotal	  = $(this).val().split(",").join("");
		var totalBank     = parseFloat(dataTotal).toFixed(0);
		var bank1		  = $('#total_bank').val().split(",").join("");							
        var pph1		  =	$('#biaya_pph').val().split(",").join("");

		var pph           = pph1.replace(",", "");
		var bank          = bank1.replace(",", "");
		var totalBank     = parseFloat(dataTotal).toFixed(0);
		var Total         = parseFloat(bank-dataTotal-pph).toFixed(0);

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

		var totalBank     = parseFloat(dataTotal).toFixed(0);
		var Total         = parseFloat(bank-dataTotal-adm).toFixed(0);
		$('#biaya_pph').val(num2(totalBank));
		$('#total_terima').val(num2(Total));

	});

	$(document).on('blur', '#total_terima', function(){
		
		var dataTotal	  = $(this).val().split(",").join("");
		var totalBank     = parseFloat(dataTotal).toFixed(0);		
		$('#total_terima').val(num2(totalBank));
	});
	
	
	$('.tanggal').datepicker({
			format: 'yyyy-m-d',
			startDate: '0',
			todayHighlight: true
			});
	
	function deleterow(tr,id){
        $('#tr_'+tr).remove();
        $("#btn-"+id).removeClass('btn-danger');
        $("#btn-"+id).addClass('btn-warning');
        $("#btn-"+id).attr('disabled',false);
        $("#btn-"+id).text('Pilih');
    }
	
	//ARWANT
	$(document).on('keyup','.sum_change_bayar', function(){
		var jumlah_bayar = 0;
		$(".sum_change_bayar" ).each(function() {
			jumlah_bayar += getNum($(this).val());
		});
		$('#total_invoice').val(num(jumlah_bayar));		
	});

	function sumchangebayar(){
		var jumlah_bayar = 0;
		$(".sum_change_bayar" ).each(function() {
			jumlah_bayar += getNum($(this).val());
		});
		$('#total_invoice').val(num(jumlah_bayar)); 
	}
	function getNum(val) { 
        if (isNaN(val) || val == '') { 
            return 0;
        }
        return parseFloat(val);
    }
	
	
	function num(n) {
      return (n).toFixed(0).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }
	
	function num2(n) {
      return (n).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }
	
	function num3(n) {
      return (n).toFixed(0);
    }
	function numx(n) {
      return (n).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }
	

</script>
