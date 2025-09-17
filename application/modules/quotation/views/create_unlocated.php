<?php
// $inv          =  $this->db->query("SELECT * FROM tr_invoice_header WHERE no_invoice = '$no_inv' ")->row();
// $alamat_cust =  $this->db->query("SELECT * FROM master_customer WHERE id_customer = '$inv->id_customer'")->row();
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
 <form id="form-header-mutasi3" method="post">
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
                                 <select class="form-control input-sm bank" name="bank" id="bank2">
									<option value="">Pilih Bank</option>
									<option value="1102-01-01">CIMB NIAGA IDR  8000.9570.4600</option>
									<option value="1102-01-02">BCA IDR 870.003.599.0</option>
									<option value="1102-01-04">CIMB SYARIAH</option>
									<option value="1102-01-05">PERMATA BANK</option>
									<option value="1102-01-06">CIMB NIAGA USD 8000.9633.1040</option>
								  </select>                                
                              </div>
                          </div>
                        </div>			
                        
                        <div class="row">
                          <div class="form-group">
                              <label for="jenis_invoice" class="col-sm-4 control-label">Tanggal </font></label>
                              <div class="col-sm-6">
								<input type="date" class="form-control input-sm tanggal" name="tanggal" id="tanggal">
								
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
		<div class="box-tools pull-right">
			<button class="btn btn-sm btn-success" id="tambahrow" type="button" style="width: 100%;" onclick="startmutasi2('1')">
				<i class="fa fa-plus"></i> Add Rows
			</button>
		</div>
	</div>
    <div class="box-body">
        <form id="form-detail-mutasi3" method="post">
            <table class="table table-bordered" width="100%" id="tabel-detail-mutasi2">
				<thead>
					<tr class="bg-blue">
						<th class="text-center">Keterangan</th>
						<th class="text-center">Total Penerimaan</th>
						<th class="text-center">Action</th>
					</tr>
				</thead>
				<tbody id="list_item_mutasi2">				
				</tbody>
            </table>
        </form>
    </div>
</div>

<div class="text-right">
  <div class="box active"> 
    <div class="box-body">
        <!--<button class="btn btn-danger" onclick="kembali_inv()">
           <i class="fa fa-refresh"></i><b> Kembali</b>
        </button>-->
        <button class="btn btn-primary" type="button" onclick="savemutasi2()"> 
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
	});	
	 function savemutasi2(){
		 
		 var tanggal =  $('#tanggal').val();		 
		 		 
	    if (tanggal == "") {
          swal({
            title	: "TANGGAL TIDAK BOLEH KOSONG!",
            text	: "ISI TANGGAL TANGGAL BAYAR!",
            type	: "warning",
            timer	: 3000,
            showCancelButton	: false,
            showConfirmButton	: false,
            allowOutsideClick	: false
          });
        }
		
	   else if ($('#bank2').val() == "") {
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
				var formdata = $("#form-header-mutasi3,#form-detail-mutasi3").serialize();
				$.ajax({
					url: siteurl+"penerimaan/save_unlocated",
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

	$("#tambah").click(function(){
		$('#dialog-data-stok').modal('show');
//        $("#list_item_stok").DataTable({lengthMenu:[10,15,25,30]}).draw();		
	});
	
	function startmutasi2(id){
		
	   var Cek_OK		= 1;
	   var Urut			= 1;
	   var total_row	= $('#list_item_mutasi2').find('tr').length;
	   
	   if(Cek_OK==1){	   
			var idnya = "'"+id+"'";
			html='<tr id="tr_'+Urut+'">'
				+ '<td style="padding:3px;"><textarea type="text" class="form-control input-sm" name="keterangan[]" id="keterangan'+Urut+'" ></textarea></td>'
				+ '<td style="padding:3px;"><input type="text" class="form-control input-sm totalpenerimaan" name="totalpenerimaan[]" id="totalpenerimaan'+Urut+'" style="text-align:right;"></td>'
				+ '<td style="padding:3px;"><center><div class="btn-group" style="margin:0px;">'
				+ '<button type="button" onclick="deleterow('+Urut+','+idnya+')" id="delete-row" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i> Hapus</button>'
				+ '</div></center></td>'
				
				+ '</tr>';
			$("#tabel-detail-mutasi2").append(html);
			$("#btn-"+id).removeClass('btn-warning');
			$("#btn-"+id).addClass('btn-danger');
			$("#btn-"+id).attr('disabled',true);
			$("#btn-"+id).text('Sudah');
			sumchangebayar();
	   }
	   
			

    }
	
	// $('.tanggal').datepicker({
			// format: 'yyyy-m-d',
			// startDate: '0',
			// todayHighlight: true
			// });
	
	
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
