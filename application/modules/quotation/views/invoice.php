 <div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post">
		<div class="form-group row" >
			 <table class="table table-bordered" width="100%" id="list_item_stok">
              <thead>
                  <tr>
				      <th width="30%">Code</th>
                      <th width="30%">No Invoice</th>
                      <th width="30%">Nama Customer</th>
                      <th width="30%">Total Invoice</th>
					   <th width="30%">Sisa Invoice</th>
                      <th width="2%" class="text-center">Aksi</th>  
                  </tr>
              </thead>
              <tbody>
                  <?php	
				 
				  $cust = $results['detail'];
				  
                  $invoice = $this->db->query("SELECT a.*, b.name_customer as nm_customer FROM tr_invoice a
				                      INNER JOIN master_customers b ON a.id_customer=b.id_customer WHERE a.id_customer ='$cust' AND (a.sisa_invoice_idr >'0')")->result();
				  if($invoice){
					foreach($invoice as $ks=>$vs){
                  ?>
						  <tr>
							  <td><?php echo $vs->no_invoice ?></td>
							  <td><?php echo $vs->no_surat ?></td>
							  <td><center><?php echo $vs->nm_customer ?></center></td>
							  <td><center><?php echo number_format($vs->nilai_invoice) ?></center></td>
							  <td><center><?php echo number_format($vs->sisa_invoice_idr) ?></center></td>
							  <td>
							  <?php 
							  if($vs->printed_on == null){
								  echo 'Belum cetak invoice';
							  }else{
							  ?>
								<center>
									<button id="btn-<?php echo $vs->no_invoice?>" class="btn btn-warning btn-sm" type="button" onclick="startmutasi('<?php echo $vs->no_invoice?>', '<?php echo $vs->no_surat?>','<?php echo addslashes($vs->nm_customer) ?>','<?php echo $vs->nilai_invoice?>','<?php echo $vs->sisa_invoice_idr?>')">
										Pilih
									</button>
								</center>
							  <?php 
							  }
							  ?>
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