<div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post">
		<div class="form-group row" >
			<table class="table table-bordered" width="100%" id="list_item_lebihbayar">
              <thead>
                  <tr>
                     <th class="text-center">Tanggal</th>
					 <th class="text-center">Customer</th>
					 <th class="text-center">Keterangan</th>
					 <th class="text-center">Total Penerimaan</th>
					 <th class="text-center">Bank</th>
                     <th width="2%" class="text-center">Aksi</th>  
                  </tr>
              </thead>
              <tbody>
                  <?php	
				  // $cust = $inv->nm_customer;
				  $cust = $results['detail'];
				  
                  $invoice = $this->db->query("SELECT * FROM tr_lebihbayar_bank WHERE id_customer ='$cust' AND saldo !=0 ")->result();                 				  
				  if($invoice){
					foreach($invoice as $ks=>$vs){
                  ?>
						  <tr>
							  <td><?php echo $vs->tgl ?></td>
							  <td><center><?php echo $vs->nm_customer ?></center></td>
							  <td><center><?php echo $vs->keterangan ?></center></td>
							  <td><center><?php echo number_format($vs->totalpenerimaan) ?></center></td>
							  <td><center><?php echo $vs->bank ?></center></td>
							  <td>
								<center>
									<button id="btn-<?php echo $vs->id?>" class="btn btn-warning btn-sm" type="button" onclick="startlebihbayar('<?php echo $vs->id?>','<?php echo $vs->totalpenerimaan?>')">
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
			</div>
				 </div>
			</div>
		</form>		  
	</div>
</div>	
	
				  
				  

	
	
	
</script>