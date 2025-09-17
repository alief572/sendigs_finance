<?php
	$no_mutasi = $this->uri->segment(3);
	$cabang = $this->uri->segment(4);
	$Header	= $this->db->query("SELECT * FROM trans_internalpo_header WHERE no_mutasi='".$no_mutasi."' LIMIT 1")->result_array();
	$Detail	= $this->db->query("SELECT a.*, b.qty_avl, b.landed_cost, b.harga FROM trans_internalpo_detail a INNER JOIN barang_stock b ON a.id_barang=b.id_barang WHERE a.no_mutasi='".$no_mutasi."' AND b.kdcab='".$cabang."'")->result_array();
	?>
	<div class="box">
		<div class="box-body" style="">
			<table id="my-grid" class="table table-striped table-bordered table-hover table-condensed" width="100%">
				<tbody>
					<tr>
						<td class="text-left" width='15%'><b>No Mutasi</b></td>
						<td class="text-left" width='35%'><?= $Header[0]['no_mutasi']; ?></td>
						<td class="text-left" width='15%'><b>Tanggal Mutasi</b></td>
						<td class="text-left" width='35%'><?= date('d F Y', strtotime($Header[0]['tgl_mutasi'])); ?></td>
					</tr>
					<tr>
						<td class="text-left"><b>Cabang Asal</b></td>
						<td class="text-left"><?= $Header[0]['cabang_asal']; ?></td>
						<td class="text-left"><b>Cabang Tujuan</b></td>
						<td class="text-left"><?= $Header[0]['cabang_tujuan']; ?></td>
						
					</tr>
					
				</tbody>
			</table>
		</div>
	</div>
	<div class="box box-primary">
		<div class="box-body" style="">
			<table id="my-grid" class="table table-striped table-bordered table-hover table-condensed" width="100%">
				<thead>
					<tr class='bg-blue'>
						<th class="text-center">No</th>
						<th class="text-left">Kode Produk</th>
						<th class="text-left">Produk Set</th>
						<th class="text-center">Avl</th>
						<th class="text-right">Landed Cost</th>
						<th class="text-right">Harga</th>
						<th class="text-center">Qty PO</th>
					</tr>
				</thead>
				<tbody>
				<?php
					$no =0;
					foreach($Detail AS $val => $valx){
						$no++;
						echo "<tr>";
							echo "<td align='center'>".$no."</td>";
							echo "<td>".$valx['id_barang']."</td>";
							echo "<td>".$valx['nm_barang']."</td>";
							echo "<td align='center'>".$valx['qty_avl']."</td>";
							echo "<td align='right'>".number_format($valx['landed_cost'], 2)."</td>";
							echo "<td align='right'>".number_format($valx['harga'])."</td>";
							echo "<td align='center'>".$valx['qty_mutasi']."</td>";
						echo "</tr>";
					}	
				?>
				</tbody>
			</table>
		</div>
	</div>
