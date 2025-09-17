
<div class="box-body"> 
	<br>
	<table id="my-grid" class="table table-striped table-bordered table-hover table-condensed" width="100%">
		<thead id='head_table'>
			<tr class='bg-blue'>
			<th class="text-center" width='7%'>No RFQ</th> 
				<th class="text-center" width='13%'>Supplier Name</th>
				<th class="text-center">Pengiriman</th>
				<th class="text-center" width='15%'>Material Name</th>
				<th class="text-center" width='6%'>Price Ref ($)</th>
				<th class="text-center" width='8%'>Price From Supplier</th>
				<th class="text-center" width='8%'>Harga (IDR)</th>
				<th class="text-center" width='7%'>Qty PR</th>
				<th class="text-center" width='5%'>MOQ (Kg)</th>
				<th class="text-center" width='5%'>Lead Time (Days)</th>
				<th class="text-center" width='7%'>Tgl Dibutuhkan</th>
				<th class="text-center" width='8%'>Total Harga</th>
			</tr>
		</thead>
		<tbody>
			<?php
			$jumlah = count($result);
			$rows2 = $jumlah / $num_rows;
			$no  = 0;
			$no2 = 1;
			$dataArr = array();
			foreach($result AS $val => $valx){
				$dataArr[] = $no2 += $rows2;
			}
			
            foreach($result AS $val => $valx){ $no++;
                echo "<tr>";
					if($no == '1'){
						echo "<td align='center' rowspan='".$jumlah."'>".$valx['no_rfq']."</td>";
					}
					if(in_array($no, $dataArr) || $no == '1'){
						echo "<td align='left' rowspan='".$rows2."'>".$valx['nm_supplier']."</td>";
					}
					if(in_array($no, $dataArr) || $no == '1'){
						echo "<td align='left' rowspan='".$rows2."'><b>".strtoupper($valx['lokasi'])."</b><br>".strtoupper($valx['alamat_supplier'])."<br><b>CURRENCY : ".strtoupper($valx['currency'])."</b></td>";
					}
					echo "<td align='left'>".strtoupper($valx['nm_barang'])."</td>";
					echo "<td align='right'>".number_format($valx['price_ref'],2)."</td>";
					echo "<td align='right'>".number_format($valx['price_ref_sup'],2)."</td>";
					echo "<td align='right'>".number_format($valx['harga_idr'])."</td>";
					echo "<td align='right'>".number_format($valx['qty'])."</td>";
					echo "<td align='center'>".number_format($valx['moq'])."</td>";
					echo "<td align='center'>".number_format($valx['lead_time'])."</td>";
					echo "<td align='center'>".date('d-m-Y', strtotime($valx['tgl_dibutuhkan']))."</td>";
					echo "<td align='right'>".number_format($valx['total_harga'])."</td>";
				echo "</tr>";
			}
			?>
		</tbody>
	</table>
</div>

<script>
	$(document).ready(function(){
		swal.close();
	});
</script>