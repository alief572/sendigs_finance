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
                           <?php if($hd->order_status==stk){ ?>
						    <option value="stk" selected>Stock</option>
                            <option value="ind" >Indent</option>
                            <?php } else if($hd->order_status==ind){?>
                            <option value="stk" >Stock</option>
                            <option value="ind" selected>Indent</option>
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
								<button type='button' class='btn btn-sm btn-success' title='Ambil' id='tbh_ata' data-role='qtip' onClick='GetProduk();'><i class='fa fa-plus'></i>Add</button>
							</div>
						    <?php } ?>
							<div class="form-group row" >
							<table class='table table-bordered table-striped'>
									<thead>
										<tr class='bg-blue'>
											<th width='3%'>No</th>
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
                                            <td>$loop</td>
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
                                            <td id='qty_$loop'><input type='text' value='$dt_spk->qty' class='form-control input-sm' id='used_qty_$loop' required name='dt[$loop][qty]' onblur='HitungTotal($loop)'></td>
                                            <td id='harga_satuan_$loop'><input type='text' value='$dt_spk->harga_satuan' class='form-control input-sm' id='used_harga_satuan_$loop' required name='dt[$loop][harga_satuan]'></td>
                                            <td id='stok_tersedia_$loop'><input type='text' value='$dt_spk->stok_tersedia' class='form-control input-sm' id='used_stok_tersedia_$loop' required name='dt[$loop][stok_tersedia]' onblur='HitungLoss($loop)'></td>
                                            <td id='potensial_loss_$loop'><input type='text' value='$dt_spk->potensial_loss' class='form-control input-sm' id='used_potensial_loss_$loop' required name='dt[$loop][potensial_loss]'></td>
                                            <td id='diskon_$loop'><input type='text' value='$dt_spk->diskon' class='form-control'  id='used_diskon_$loop' required name='dt[$loop][diskon]' onblur='HitungTotal($loop)'></td>
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
											<th width='3%'></th>
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
											<th width='3%'></th>
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
											<th width='3%'></th>
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
                    <a class="btn btn-primary btn-sm ajukan" id='ajukan' title="Ajukan">Ubah Status Loss</a>
                    </center>
		</form>		  
	</div>
</div>	
	

<!-- awal untuk modal dialog -->
<!-- Modal -->
<div class="modal modal-default fade" id="dialog-popup" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel"><span class="fa fa-users"></span>&nbsp;Data Penawaran</h4>
      </div>
      <div class="modal-body" id="ModalView">
		...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">
        <span class="glyphicon glyphicon-remove"></span>  Close</button>
        </div>
    </div>
  </div>
</div>
	
				  
				  
				  
<script type="text/javascript">

$(document).on('click', '.ajukan', function(){
		var id = $('#no_penawaran').val();
		// alert(id);
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Data Penawaran</b>");
		$.ajax({
			type:'POST',
			url:siteurl+'wt_penawaran/FormLoss/'+id,
			data:{'id':id},
			success:function(data){
				$("#dialog-popup").modal();
				$("#ModalView").html(data);
				
			}
		})
	});

</script>