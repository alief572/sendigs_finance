<?php
    $ENABLE_ADD     = has_permission('Penawaran.Add');
    $ENABLE_MANAGE  = has_permission('Penawaran.Manage');
    $ENABLE_VIEW    = has_permission('Penawaran.View');
    $ENABLE_DELETE  = has_permission('Penawaran.Delete');
	$tanggal = date('Y-m-d');
    foreach ($results['header'] as $hd){
?>

<div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post">
			<div class="col-sm-12">
				<div class="input_fields_wrap2">
			<div class="row">
		<center><label for="customer" ><h3>Penawaran</h3></label></center>
		<div class="col-sm-12">
		    <div class="col-sm-6">
		        <div class="form-group row">
			        <div class="col-md-4">
				        <label for="customer">NO.Penawaran</label>
			        </div>
			        <div class="col-md-8" hidden>
				        <input type="text" class="form-control" id="no_penawaran" value="<?= $hd->no_penawaran?>" required name="no_penawaran" readonly placeholder="No.CRCL">
			        </div>
			        <div class="col-md-8">
				        <input type="text" class="form-control" id="no_surat"  value="<?= $hd->no_surat?>" required name="no_surat" readonly placeholder="No.Penawaran">
			        </div>
		        </div>
		    </div>
		    <div class="col-sm-6">
		        <div class="form-group row">
			        <div class="col-md-4">
				        <label for="customer">Tanggal</label>
			        </div>
			        <div class="col-md-8">
				        <input type="date" class="form-control" id="tanggal" value="<?= $hd->tgl_penawaran?>"  onkeyup required name="tanggal" readonly >
			        </div>
		        </div>
		    </div>
		</div>
		<div class="col-sm-12">
            <div class="col-sm-6">
                <div class="form-group row">
                    <div class="col-md-4">
                        <label for="id_customer">Customer</label>
                    </div>
                    <div class="col-md-8">
                        <select id="id_customer" name="id_customer" class="form-control select" onchange="get_customer()" required>
                            <option value="">--Pilih--</option>
                             <?php foreach ($results['customers'] as $customers){
                             $select1 = $hd->id_customer == $customers->id_customer ? 'selected' : '';	?>
                            <option value="<?= $customers->id_customer?>"<?= $select1 ?>><?= strtoupper(strtolower($customers->name_customer))?></option>
                                <?php } ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group row">
                    <div class="col-md-4">
                        <label for="id_category_supplier">Sales/Marketing</label>
                    </div>
                    <div id="sales_slot">
                    <div class='col-md-8'>
                        <input type='text' class='form-control' id='nama_sales' value="<?= $hd->nama_sales?>"  required name='nama_sales' readonly placeholder='Sales Marketing'>
                    </div>
                    <div class='col-md-8' hidden>
                        <input type='text' class='form-control' id='id_sales'  value="<?= $hd->id_sales?>" required name='id_sales' readonly placeholder='Sales Marketing'>
                    </div>
                    </div>
                </div>
            </div>		
		</div>
		
		<div class="col-md-12">
		    <div class='col-sm-6'>
		        <div class='form-group row'>
			        <div class='col-md-4'>
				        <label for='email_customer'>Email</label>
			        </div>
			        <div class='col-md-8' id="email_slot">
				        <input type='email' class='form-control'  value="<?= $hd->email_customer?>" id='email_customer' required name='email_customer' >
			        </div>
		        </div>
		    </div>
		    <div class='col-sm-6'>
			    <div class='form-group row'>
				    <div class='col-md-4'>
					    <label for='id_category_supplier'>PIC Customer</label>
				    </div>
				    <div class='col-md-8' id="pic_slot" >
					    <select id='pic_customer' name='pic_customer' class='form-control select' required>
						    <option value="<?= $hd->pic_customer?>" selected><?= strtoupper(strtolower($hd->pic_customer))?></option>
					    </select>
				    </div>
			    </div>
		    </div>
		</div>		
       
		<div class="col-md-12">
		    <div class='col-sm-6'>
		        <div class='form-group row'>
			        <div class='col-md-4'>
				        <label for='email_customer'>Term Of Payment</label>
			        </div>
                    <div class='col-md-8'>
                        <select id="top" name="top" class="form-control select" required>
                            <option value="">--Pilih--</option>
                                <?php foreach ($results['top'] as $top){
                                $select2 = $top->id_top == $hd->top ? 'selected' : '';	?>
                            <option value="<?= $top->id_top?>" <?= $select2 ?>><?= strtoupper(strtolower($top->nama_top))?></option>
                                <?php } ?>
                        </select>
                    </div>
			    </div>
		    </div>
		    <div class='col-sm-6'>
		        <div class='form-group row'>
			        <div class='col-md-4'>
				        <label for='email_customer'>Order Status</label>
			        </div>
			        <div class='col-md-8' id="">
					    <select id="order_sts" name="order_sts" class="form-control select" required>
							<option value="">--Pilih--</option>
                           <?php if($hd->order_status=='stk'){ ?>
						    <option value="stk" selected>Stock</option>
                            <option value="ind" >Indent</option>
                            <?php } elseif($hd->order_status=='ind'){?>
                            <option value="stk" >Stock</option>
                            <option value="ind" selected>Indent</option>
                            <?php } else { ?>
                            <option value="stk" >Stock</option>
                            <option value="ind" >Indent</option>
                            <?php } ?>
					    </select>
			        </div>
		        </div>
            </div>
		</div>

        <?php } ?>
		                
                    <div class="col-sm-12">
					    <div class="col-sm-12">
						    <?php if(empty($results['view'])){ ?>
						    <div class="form-group row">
								<button type='button' class='btn btn-sm btn-success' title='Ambil' id='tbh_ata' data-role='qtip' data-klik='0'  ><i class='fa fa-plus'></i>Add</button>
							</div>
						    <?php } ?>
							<div class="form-group row" >
								<table class='table table-bordered table-striped'>
									<thead>
										<tr class='bg-blue'>
											
											<th width='30%'>Produk</th>
											<th width='7%'>Qty <br> Penawaran</th>
											<th width='7%'>Harga <br> Produk</th>
											<th width='7%'>Stok <br> Tersedia</th>
											<th width='7%'>Potensial <br> Loss</th>											
											<th width='7%'>Diskon %</th>
											<th width='7%'>Freight Cost</th>											
											<th width='7%'>Total Harga</th>																						
											<th width='5%'>Aksi</th>
										</tr>
									</thead>
									<tbody id="list_spk">
                                    <?php $loop=0;
									foreach ($results['detail'] as $dt_spk){$loop++; 

                                        $customers = $this->Wt_penawaran_model->get_data('master_customers','deleted',$deleted);
		                                $material = $this->db->query("SELECT a.* FROM ms_inventory_category3 as a ")->result();
                                        echo "
                                        <tr id='tr_$loop'>
                                           
                                            <td>
                                                <select id='used_no_surat_$loop' name='dt[$loop][no_surat]' data-no='$loop' onchange='CariDetail($loop)' class='form-control select' required>
                                                    <option value=''>-Pilih-</option>";	
                                                    foreach($material as $produk){
                                                        $select = $dt_spk->id_category3 == $produk->id_category3 ? 'selected' : '';				
                                                        echo"<option value='$produk->id_category3' $select>$produk->nama|$produk->kode_barang</option>"; 
                                                    }
                                        echo	"</select>
                                            </td>
                                            <td id='nama_produk_$loop' hidden><input type='text' value='$dt_spk->nama_produk' class='form-control input-sm' readonly id='used_nama_produk_$loop' required name='dt[$loop][nama_produk]'></td>
                                            <td id='qty_$loop'><input type='text' value='$dt_spk->qty' class='form-control input-sm' id='used_qty_$loop' required name='dt[$loop][qty]' onkeyup='HitungTotal($loop)'></td>
                                            <td id='harga_satuan_$loop'><input type='text' value='$dt_spk->harga_satuan' class='form-control input-sm' id='used_harga_satuan_$loop' required name='dt[$loop][harga_satuan]' readonly></td>
                                            <td id='stok_tersedia_$loop'><input type='text' value='$dt_spk->stok_tersedia' class='form-control input-sm' id='used_stok_tersedia_$loop' required name='dt[$loop][stok_tersedia]' onblur='HitungLoss($loop)' readonly></td>
                                            <td id='potensial_loss_$loop'><input type='text' value='$dt_spk->potensial_loss' class='form-control input-sm' id='used_potensial_loss_$loop' required name='dt[$loop][potensial_loss]' readonly></td>
											<td id='compare_diskon_$loop' hidden><input type='text' value='$dt_spk->diskon_compare' class='form-control'  id='used_compare_diskon_$loop' required name='dt[$loop][compare_diskon_]'></td>
                                            <td id='diskon_$loop'><input type='text' value='$dt_spk->diskon' class='form-control'  id='used_diskon_$loop' required name='dt[$loop][diskon]' onkeyup='HitungTotal($loop)'></td>
                                            <td id='nilai_diskon_$loop' hidden><input type='text' value='$dt_spk->nilai_diskon' class='form-control'  id='used_nilai_diskon_$loop' required name='dt[$loop][nilai_diskon]'></td>
                                            <td id='freight_cost_$loop'><input type='text' value='$dt_spk->freight_cost' class='form-control input-sm' id='used_freight_cost_$loop' required name='dt[$loop][freight_cost]' onblur='Freight($loop)'></td>
                                            <td id='total_harga_$loop'><input type='text' value='$dt_spk->total_harga' class='form-control input-sm total' id='used_total_harga_$loop' required name='dt[$loop][total_harga]' readonly></td>
                                            <td align='center'>
                                                <button type='button' class='btn btn-sm btn-danger' title='Hapus Data' data-role='qtip' onClick='return HapusItem($loop);'><i class='fa fa-close'></i></button>
                                            </td>
                                            
                                        </tr>";
                                     }
                                    ?>
 
									</tbody>
									<tfoot>
									    <tr>
											
											<th width='10%'></th>
											<th width='7%'></th>
											<th width='7%'></th>
											<th width='7%'></th>
											<th width='7%'></th>
											<th width='7%'><b>Total</b></th>											
											<th width='7%'></th>                                            
                                            <th width='7%'><input type='text' class='form-control totalproduk' id='totalproduk'  name='totalproduk' readonly value="<?= $hd->nilai_penawaran?>" ></th>										
                                            	
										</tr>
										<tr>
											
											<th width='10%'></th>
											<th width='7%'></th>
											<th width='7%'></th>
											<th width='7%'></th>
											<th width='7%'></th>
											<th width='7%'><b>PPN</b></th>											
											<th width='7%'><input type='text' class='form-control ppn' id='ppn'  name='ppn' onblur='hitungPpn()' value="<?= $hd->ppn?>" ></th>                                            
                                            <th width='7%'><input type='text' class='form-control totalppn' id='totalppn'  name='totalppn' value="<?= $hd->nilai_ppn?>" readonly ></th>										
                                            	
										</tr>
										<tr>
											
											<th width='10%'></th>
											<th width='7%'></th>
											<th width='7%'></th>
											<th width='7%'></th>
											<th width='7%'></th>
											<th width='7%'><b>Grand Total</b></th>											
											<th width='7%'></th>                                            
                                            <th width='7%'><input type='text' class='form-control grandtotal' id='grandtotal'  name='grandtotal' value="<?= $hd->grand_total?>" readonly ></th>										
                                            	
										</tr>
									   										
									</tfoot>
								</table>
						    </div>
					    </div>
				    </div>
					
                    <center>
                    <button type="submit" class="btn btn-success btn-sm" name="save" id="simpan-com"><i class="fa fa-save"></i>Simpan</button>
                    <a class="btn btn-danger btn-sm" href="<?= base_url('/wt_penawaran/') ?>"  title="Edit">Kembali</a>
                    </center>
		</form>		  
	</div>
</div>	
	
				  
				  
				  
<script type="text/javascript">
	//$('#input-kendaraan').hide();
	var base_url			= '<?php echo base_url(); ?>';
	var active_controller	= '<?php echo($this->uri->segment(1)); ?>';
	$(document).ready(function(){	
			var max_fields2      = 10; //maximum input boxes allowed
			var wrapper2         = $(".input_fields_wrap2"); //Fields wrapper
			var add_button2      = $(".add_field_button2"); //Add button ID		

			$('.select').select2({
				width: '100%'
			});
	$('#simpan-com').click(function(e){
		e.preventDefault();
			var deskripsi	= $('#deskripsi').val();
			var image	= $('#image').val();
			var idtype	= $('#inventory_1').val();
			var ppn	    = $('#ppn').val();	
			
			var data, xhr;

			if(ppn =='' || ppn=='0'){
					swal("Warning", "PPN Tidak Boleh Kosong :)", "error");
					return false;
			}else{
			swal({
				  title: "Are you sure?",
				  text: "You will not be able to process again this data!",
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
						var formData 	=new FormData($('#data-form')[0]);
						var baseurl=siteurl+'wt_penawaran/SaveEditPenawaran';
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
										  timer	: 7000,
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
										  timer	: 7000,
										  showCancelButton	: false,
										  showConfirmButton	: false,
										  allowOutsideClick	: false
										});
									}else{
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

		  }
		});
		
});
function get_customer(){
        var id_customer=$("#id_customer").val();
		 $.ajax({
            type:"GET",
            url:siteurl+'wt_penawaran/getemail',
            data:"id_customer="+id_customer,
            success:function(html){
               $("#email_slot").html(html);
            }
        });
		$.ajax({
            type:"GET",
            url:siteurl+'wt_penawaran/getpic',
            data:"id_customer="+id_customer,
            success:function(html){
               $("#pic_slot").html(html);
            }
        });
		$.ajax({
            type:"GET",
            url:siteurl+'wt_penawaran/getsales',
            data:"id_customer="+id_customer,
            success:function(html){
               $("#sales_slot").html(html);
            }
        });
    }
function DelItem(id){
		$('#data_barang #tr_'+id).remove();
		
	}
	
	
     $('#tbh_ata').on( 'click', function () {
		
	 var jumlah	=$('#list_spk').find('tr').length;
	 
	 var nomor   = jumlah;
	 
	 var klik = parseInt(nomor)+parseInt(10);
	 
	 
	 $.ajax({
		 type:"GET",
		 url:siteurl+'wt_penawaran/GetProduk',
		 data:"jumlah="+klik,
		 success:function(html){
			$("#list_spk").append(html);
			$('.select').select2({
				width:'100%'
			});
			
			
		   
		   
			
		 }
	 });
	 
	  $(this).data('klik',klik);
	  
	  // alert(klik);
	  });

    function HapusItem(id){		
		
		$('#list_spk #tr_'+id).remove();	

        totalBalanced();		
	}

	function CariDetail(id){
		
        var id_material=$('#used_no_surat_'+id).val();
		var id_top  =$('#top').val();

		$.ajax({
            type:"GET",
            url:siteurl+'wt_penawaran/CariNamaProduk',
            data:"id_category3="+id_material+"&id="+id,
            success:function(html){
               $('#nama_produk_'+id).html(html);
			   $('#used_qty_'+id).val('');
			   
            }
        });

		$.ajax({
            type:"GET",
            url:siteurl+'wt_penawaran/CariHarga',
            data:"id_category3="+id_material+"&id="+id,
            success:function(html){
               $('#harga_satuan_'+id).html(html);
			   $('#used_qty_'+id).val('');
			  
            }
        });
		$.ajax({
            type:"GET",
            url:siteurl+'wt_penawaran/CariDiskon',
            data:"id_category3="+id_material+"&id="+id+"&top="+id_top,
            success:function(html){
               $('#diskon_'+id).html(html);
			   $('#used_qty_'+id).val('');
			  
            }
        });
		
		$.ajax({
            type:"GET",
            url:siteurl+'wt_penawaran/CariDiskonCompare',
            data:"id_category3="+id_material+"&id="+id+"&top="+id_top,
            success:function(html){
               $('#compare_diskon_'+id).html(html);

			   $('#used_qty_'+id).val('');

            }
        });

		$.ajax({
            type:"GET",
            url:siteurl+'wt_penawaran/CariStokFree',
            data:"id_category3="+id_material+"&id="+id+"&top="+id_top,
            success:function(html){
               $('#stok_tersedia_'+id).html(html);

			   $('#used_qty_'+id).val('');

			  
            }
        });

      



	}


	function Freight(id)
		{
			var freight=$('#used_freight_cost_'+id).val();
			$('#used_freight_cost_'+id).val(number_format(freight,2));	

			HitungTotal(id);

		}

		function HitungTotal(id){
			
		//alert(id);
		
	    var qty=$('#used_qty_'+id).val();
		var harga=$('#used_harga_satuan_'+id).val().split(",").join("");
		var diskon=$('#used_diskon_'+id).val();
		var diskonCompare=$('#used_compare_diskon_'+id).val();
		var freight=$('#used_freight_cost_'+id).val().split(",").join("");
		
		
		if (diskon > diskonCompare) {
				swal({
					title: "Warning!",
					text: "Diskon Melebihi Ketentuan!",
					type: "warning",
					timer: 3000
				});
				$('#used_diskon_'+id).val(diskonCompare);
				return false;
		}else 
		{
		
		var totalBerat = getNum(qty) * getNum(harga);
		var nilai_diskon = (getNum(diskon) * getNum(totalBerat))/100;
		var total_harga =  getNum(totalBerat) - getNum(nilai_diskon)+getNum(freight);

		
		$('#used_total_harga_'+id).val(number_format(total_harga,2));	
		$('#used_nilai_diskon_'+id).val(number_format(nilai_diskon,2));	
			


		HitungLoss(id);

		totalBalanced();
		
		
		}

		
			
		}


		
		

		function totalBalanced(){
		
		var SUMx = 0;
		$(".total" ).each(function() {
			SUMx += Number($(this).val().split(",").join(""));
		});
		
		
		$('.totalproduk').val(number_format(SUMx,2));

		$('#grandtotal').val(number_format(SUMx,2));	

		hitungPpn();

		
		}


		function hitungPpn(){
		var total =$('.totalproduk').val().split(",").join("");
		var ppn   =$('#ppn').val();	
	
		var   nilai_ppn  = (getNum(total) * getNum(ppn))/100;
		var   grand_total =  getNum(total) + getNum(nilai_ppn);

		
		$('#totalppn').val(number_format(nilai_ppn,2));		
		$('#grandtotal').val(number_format(grand_total,2));		

		
		}


		function HitungLoss(id){
	    var qty=$('#used_qty_'+id).val();
		var stok=$('#used_stok_tersedia_'+id).val();
		
		
		var totalstok      = getNum(qty) + getNum(stok);
		var totalselisih   = getNum(stok) - getNum(qty);
		var total_loss     =  getNum(totalstok) + getNum(totalselisih);
		var loss_nilai     = getNum(totalselisih) * -1;

		if (totalselisih >= 0)
		{
			var loss = 0;

		}
		else
		{
			var loss = loss_nilai;
		}

		
		if(order_sts=='ind')
		{
			$('#used_potensial_loss_'+id).val('0');		
		}else{
			$('#used_potensial_loss_'+id).val(number_format(loss,2));		
		}	
					
		}

		
			
		function getNum(val) 
		{
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