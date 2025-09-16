
<div class="box-body">
	<?php if($tanda != 'request'){?>
	<table width="100%" border="0">
		<thead>
			<tr>
				<td class="text-left" style='vertical-align:middle;' width='15%'>No PO</td>
				<td class="text-left" style='vertical-align:middle;' width='2%'>:</td>
				<td class="text-left" style='vertical-align:middle;'><?=$no_po;?></td>
			</tr>
			<tr>
				<td class="text-left" style='vertical-align:middle;'>No Transaksi</td>
				<td class="text-left" style='vertical-align:middle;'>:</td>
				<td class="text-left" style='vertical-align:middle;'><?=$kode_trans;?></td>
			</tr>
			<tr>
				<td class="text-left" style='vertical-align:middle;'>Tanggal Penerimaan</td>
				<td class="text-left" style='vertical-align:middle;'>:</td>
				<td class="text-left" style='vertical-align:middle;'><?=$resv;?></td>
			</tr>
		</thead>
	</table><br>
	<table id="my-grid" class="table table-striped table-bordered table-hover table-condensed" width="100%">
		<thead id='head_table'>
			<tr class='bg-blue'>
				<th class="text-center" style='vertical-align:middle;' width='5%'>No</th>
				<th class="text-center" style='vertical-align:middle;'>Name Barang</th>
                <th class="text-center" style='vertical-align:middle;' width='10%'>Qty</th>
				<th class="text-center" style='vertical-align:middle;' width='15%'>Keterangan</th>
				<th class="text-center" style='vertical-align:middle;' width='15%'>Pemeriksa</th> 
			</tr>
		</thead>
		<tbody>
			<?php
            $No=0;
			foreach($result AS $val => $valx){
                $No++;
				
				$qty_oke 		= number_format($valx['qty_oke'],4);
				$qty_rusak 		= number_format($valx['qty_rusak'],4);
				$keterangan 	= (!empty($valx['keterangan']))?ucfirst($valx['keterangan']):'-';
				$pemeriksa 	= (!empty($valx['ket_req_pro']))?ucfirst($valx['ket_req_pro']):'-';
				$qty_kurang 	= number_format($valx['qty_order'] - $valx['qty_oke'],4);
				if($tanda == 'check' AND $checked == 'Y'){
					$qty_oke 		= number_format($valx['check_qty_oke'],4);
					$qty_rusak 		= number_format($valx['check_qty_rusak'],4);
					$keterangan 	= (!empty($valx['check_keterangan']))?ucfirst($valx['check_keterangan']):'-';
					$pemeriksa 	= (!empty($valx['ket_req_pro']))?ucfirst($valx['ket_req_pro']):'-';
					$qty_kurang 	= number_format($valx['qty_order'] - $valx['check_qty_oke'],4);
				}
				
				echo "<tr>";
					echo "<td align='center'>".$No."</td>";
					echo "<td>".strtoupper($valx['nm_material'])."</td>";
					echo "<td align='right'>".$qty_oke."</td>";
					echo "<td>".$keterangan."</td>";
					echo "<td>".$pemeriksa."</td>";
				echo "</tr>";
			}
			?>
		</tbody>
	</table>
	<?php } ?>
	
	<?php if($tanda == 'request'){?>
	<table id="my-grid" class="table" width="100%">
		<thead>
			<tr>
				<td class="text-left" style='vertical-align:middle;' width='15%'>No Transaksi</td>
				<td class="text-left" style='vertical-align:middle;' width='2%'>:</td>
				<td class="text-left" style='vertical-align:middle;'><?=$kode_trans;?></td>
			</tr>
			<tr>
				<td class="text-left" style='vertical-align:middle;'>Tanggal Request</td>
				<td class="text-left" style='vertical-align:middle;'>:</td>
				<td class="text-left" style='vertical-align:middle;'><?=$resv;?></td>
			</tr>
		</thead>
	</table><br>
	<table id="my-grid" class="table table-striped table-bordered table-hover table-condensed" width="100%">
		<thead id='head_table'>
			<tr class='bg-blue'>
				<th class="text-center" style='vertical-align:middle;' width='4%'>Nox</th>
				<th class="text-center" style='vertical-align:middle;'>Material Name</th>
				<th class="text-center" style='vertical-align:middle;'>Category</th>
                <th class="text-center" style='vertical-align:middle;' width='9%'>Qty Request</th>
				<th class="text-center" style='vertical-align:middle;' width='12%'>Keterangan</th> 
			</tr>
		</thead>
		<tbody>
			<?php
            $No=0;
			foreach($result AS $val => $valx){
                $No++;
				
				$qty_oke 		= number_format($valx['qty_oke'],4);
				$keterangan 	= ucfirst($valx['keterangan']);
				if($checked == 'Y'){
					$qty_oke 		= number_format($valx['check_qty_oke'],4);
					$keterangan 	= ucfirst($valx['check_keterangan']);
				}
				
				echo "<tr>";
					echo "<td align='center'>".$No."</td>";
					echo "<td>".$valx['nm_material']."</td>";
					echo "<td>".$valx['nm_category']."</td>";
					echo "<td align='right'>".$qty_oke."</td>";
					echo "<td>".$keterangan."</td>";
				echo "</tr>";
			}
			?>
		</tbody>
	</table>
	<?php } ?>
</div>
<script>
	swal.close();
</script>