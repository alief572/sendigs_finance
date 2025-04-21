
<div class="box box-primary">
    <div class="box-header">
        <h3 class="box-title">Data Detail</h3>
		<div class='box-tool pull-right'>
			<button type='button' class='btn btn-md btn-danger' id='btn_export'><i class="fa fa-cloud-download"> Download Excel</i></button>
			<input type='hidden' id='category_type' value='<?php echo $kategori;?>'>;
		</div>
    </div>
    <div class="box-body" style='overflow-x:scroll'>
        <table class="table table-bordered table-striped" id='table_other'>
            <thead>
                <tr class='bg-blue'>
					 <th class="text-center">No</th>
					 <th class="text-center">Invoice No</th>
					 <th class="text-center">Invoice Date</th>
					 <th class="text-center">Cabang</th>
					 <th class="text-center">Customer</th>
					 <th class="text-center">Salesman</th>
					 <th class="text-center">Total Inv</th>
					 <th class="text-center">Payment</th>
					 <th class="text-center">AR</th>
					 <th class="text-center">Aging (Days)</th>
					
                </tr>
            </thead>
            <tbody>
            <?php
			   $Total_Inv	= $Total_Pay	= $Total_AR	=0;
               if($rows_ar){
				   $int=0;
					foreach($rows_ar as $key=>$val){
						$int++;
						$inv_nil	= round($val->hargajualtotal);
						$pay_nil	= round($val->jum_bayar);
						$ar_nil		= $inv_nil -  $pay_nil;
						$inv_date	= date('d M Y',strtotime($val->tanggal_invoice));
						$aging		= number_format($val->umur);
						$Total_AR	+= $ar_nil;
						$Total_Inv	+= $inv_nil;
						$Total_Pay	+= $pay_nil;
						echo'<tr>';
                    		echo'<td class="text-center">'.$int.'</td>';
							echo'<td class="text-center">'.$val->no_invoice.'</td>';
							echo'<td class="text-center">'.$inv_date.'</td>';
							echo'<td class="text-center">'.$val->kdcab.'</td>';
							echo'<td class="text-left">'.$val->nm_customer.'</td>';
							echo'<td class="text-center">'.$val->nm_salesman.'</td>';
							echo'<td class="text-right">'.number_format($inv_nil).'</td>';
							echo'<td class="text-right">'.number_format($pay_nil).'</td>';
							echo'<td class="text-right">'.number_format($ar_nil).'</td>';
							echo'<td class="text-center">'.$aging.'</td>';
						
                   		echo'</tr>';
					}
			   }
            ?>
            </tbody>
			<tfoot>
				<tr class='bg-gray'>
					<th class="text-center" colspan="6"><b>Grand Total</b></th>
					<th class="text-right text-red"><b><?php echo number_format($Total_Inv);?></b></th>
					<th class="text-right text-red"><b><?php echo number_format($Total_Pay);?></b></th>
					<th class="text-right text-red"><b><?php echo number_format($Total_AR);?></b></th>
					<th class="text-center"></th>
				</tr>
			</tfoot>
        </table>
     
    </div><!-- /.box-body -->
</div><!-- /.box -->
<script>
	var base_url			= siteurl;
	var active_controller	= 'dashboard';
	$(document).ready(function(){
		//$('#table_other').dataTable();
		$('#btn_export').click(function(){
			var Kategori	= $('#category_type').val();
			var Links		= base_url+active_controller+'/excel_piutang_dashboard/'+Kategori;
			//alert(Links);
			window.open(Links,'_blank');
		});
		
	})
</script>