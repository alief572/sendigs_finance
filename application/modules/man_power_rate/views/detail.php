<?php
 $ENABLE_ADD     = has_permission('Man_Power_Rate.Add');
 $ENABLE_MANAGE  = has_permission('Man_Power_Rate.Manage');
 $ENABLE_VIEW    = has_permission('Man_Power_Rate.View');
 $ENABLE_DELETE  = has_permission('Man_Power_Rate.Delete');
// print_r($header);
$total_direct   	= (!empty($header))?$header[0]->total_direct:'';
$total_bpjs   		= (!empty($header))?$header[0]->total_bpjs:'';
$total_biaya_lain   = (!empty($header))?$header[0]->total_biaya_lain:'';

$rate_dollar   = (!empty($header))?$header[0]->rate_dollar:'';
$upah_per_bulan_dollar   = (!empty($header))?$header[0]->upah_per_bulan_dollar:'';
$upah_per_jam_dollar   = (!empty($header))?$header[0]->upah_per_jam_dollar:'';
$upah_per_bulan   = (!empty($header))?$header[0]->upah_per_bulan:'';
$upah_per_jam   = (!empty($header))?$header[0]->upah_per_jam:'';

// print_r($header);
?>

 <div class="box box-primary">
    <div class="box-body">
		<div class="box-header">
		<span class="pull-right">
			<?php if($ENABLE_MANAGE) : ?>
				<a class="btn btn-success btn-md" href="<?= base_url('man_power_rate/add') ?>" title="Edit">Edit</a>
			<?php endif; ?>
		</span>
		</div>
		<div class='box box-info'>
			<div class='box-body'>
				<table class='' width='60%'>
					<thead>
						<tr>
							<th class='text-center' style='width: 5%;'>#</th>
							<th class='text-left' style='width: 30%;'>Salary Direct Man Power</th>
							<th class='text-left' style='width: 2%;'></th>
							<th class='text-right' style='width: 15%;'></th>
							<th class='text-left' style='width: 15%;'></th>
							<th class='text-left'></th>
							<th class='text-right' style='width: 20%;'></th>
						</tr>
					</thead>
					<tbody>
						<?php
							if(!empty($detail_direct)){
								$SUM_DIRECT = 0;
								foreach($detail_direct AS $val => $valx){ $val++;
									$SUM_DIRECT += $valx['nilai'];
									echo "<tr>";
										echo "<td align='center'>".$val."</td>";
										echo "<td align='left'>".$valx['nama']."</td>";
										echo "<td align='left'>Rp</td>";
										echo "<td align='right'>".number_format($valx['nilai'],2)."</td>";
										echo "<td align='left'></td>";
										echo "<td align='left'>".$valx['keterangan']."</td>";
										echo "<td align='left'></td>";
									echo "</tr>";
								}
								echo "<tr>";
									echo "<td align='center'></td>";
									echo "<td align='left'></td>";
									echo "<td align='left' class='text-bold'>Rp</td>";
									echo "<td align='right' class='text-bold'>".number_format($SUM_DIRECT,2)."</td>";
									echo "<td align='left'></td>";
									echo "<td align='left'></td>";
									echo "<td align='left'></td>";
								echo "</tr>";
							}
							if(!empty($detail_bpjs)){
								echo "<tr>";
									echo "<th class='text-center'>#</th>";
									echo "<th class='text-left'>BPJS</th>";
									echo "<th class='text-right'></th>";
									echo "<th class='text-right'></th>";
									echo "<th class='text-left'></th>";
									echo "<th class='text-left'>Tarif</th>";
									echo "<th class='text-right'></th>";
								echo "</tr>";
								$SUM_BPJS = 0;
								foreach($detail_bpjs AS $val => $valx){ $val++;
									$SUM_BPJS += $valx['nilai'];
									echo "<tr>";
										echo "<td align='center'>".$val."</td>";
										echo "<td align='left'>".$valx['nama']."</td>";
										echo "<td align='left'>Rp</td>";
										echo "<td align='right'>".number_format($valx['nilai'],2)."</td>";
										echo "<td align='left'></td>";
										echo "<td align='left'>".$valx['keterangan']."</td>";
										echo "<td align='left'></td>";
									echo "</tr>";
								}
								echo "<tr>";
									echo "<td align='center'></td>";
									echo "<td align='left'></td>";
									echo "<td align='left' class='text-bold'>Rp</td>";
									echo "<td align='right' class='text-bold'>".number_format($SUM_BPJS,2)."</td>";
									echo "<td align='left'></td>";
									echo "<td align='left'></td>";
									echo "<td align='left'></td>";
								echo "</tr>";
							}
							if(!empty($detail_lain)){
								echo "<tr>";
									echo "<th class='text-center'>#</th>";
									echo "<th class='text-left'>Biaya Lain-Lain</th>";
									echo "<th class='text-right'></th>";
									echo "<th class='text-right'></th>";
									echo "<th class='text-left'></th>";
									echo "<th class='text-left'></th>";
									echo "<th class='text-right'>Harga Per Pcs</th>";
								echo "</tr>";
								$SUM_LAINNYA = 0;
								foreach($detail_lain AS $val => $valx){ $val++;
									$SUM_LAINNYA += $valx['nilai'];
									echo "<tr>";
										echo "<td align='center'>".$val."</td>";
										echo "<td align='left'>".$valx['nama']."</td>";
										echo "<td align='left'>Rp</td>";
										echo "<td align='right'>".number_format($valx['nilai'],2)."</td>";
										echo "<td align='left'></td>";
										echo "<td align='left'>".$valx['keterangan']."</td>";
										echo "<td align='right'>".number_format($valx['harga_per_pcs'],2)."</td>";
									echo "</tr>";
								}
								echo "<tr>";
									echo "<td align='center'></td>";
									echo "<td align='left'></td>";
									echo "<td align='left' class='text-bold'>Rp</td>";
									echo "<td align='right' class='text-bold'>".number_format($SUM_LAINNYA,2)."</td>";
									echo "<td align='left'></td>";
									echo "<td align='left'></td>";
									echo "<td align='left'></td>";
								echo "</tr>";
							}
							echo "<tr>";
								echo "<th class='text-center'>&nbsp;</th>";
								echo "<th class='text-left'></th>";
								echo "<th class='text-left'></th>";
								echo "<th class='text-right'></th>";
								echo "<th class='text-left'></th>";
								echo "<th class='text-left'></th>";
								echo "<th class='text-right'></th>";
							echo "</tr>";
							$SUM_UPAH = $SUM_DIRECT + $SUM_BPJS + $SUM_LAINNYA;
							echo "<tr>";
								echo "<th class='text-center'></th>";
								echo "<th class='text-left'>Total Biaya MP /Bulan</th>";
								echo "<th align='left'>Rp</th>";
								echo "<th class='text-right'>".number_format($SUM_UPAH,2)."</th>";
								echo "<th class='text-left'></th>";
								echo "<th class='text-left'></th>";
								echo "<th class='text-right'></th>";
							echo "</tr>";
							echo "<tr>";
								echo "<th class='text-center'>&nbsp;</th>";
								echo "<th class='text-left'></th>";
								echo "<th class='text-right'></th>";
								echo "<th class='text-right'></th>";
								echo "<th class='text-left'></th>";
								echo "<th class='text-left'></th>";
								echo "<th class='text-right'></th>";
							echo "</tr>";
							$UPAH_PER_BULAN_USD = 0;
							$UPAH_PER_JAM_USD = 0;
							if($rate_dollar > 0){
								$UPAH_PER_BULAN_USD = $SUM_UPAH/$rate_dollar;
								$UPAH_PER_JAM_USD = $UPAH_PER_BULAN_USD/173;
							}
							echo "<tr>";
								echo "<th class='text-center'></th>";
								echo "<th class='text-left'>Upah /Bulan</th>";
								echo "<th align='left'>$</th>";
								echo "<th class='text-right'>".number_format($UPAH_PER_BULAN_USD,2)."</th>";
								echo "<th class='text-left'></th>";
								echo "<th class='text-left'>Kurs</th>";
								echo "<th class='text-right'>".number_format($rate_dollar,2)."</th>";
							echo "</tr>";
							echo "<tr>";
								echo "<th class='text-center'></th>";
								echo "<th class='text-left'>Rate MP</th>";
								echo "<th align='left'>$</th>";
								echo "<th class='text-right'>".number_format($UPAH_PER_JAM_USD,2)."</th>";
								echo "<th class='text-left'></th>";
								echo "<th class='text-left'>Kurs date</th>";
								echo "<th class='text-right text-red'>".date('d-M-Y',strtotime($header[0]->kurs_tanggal))."</th>";
							echo "</tr>";
							echo "<tr>";
								echo "<th class='text-center'></th>";
								echo "<th class='text-left'>Rate MP</th>";
								echo "<th align='left'>Rp</th>";
								echo "<th class='text-right'>".number_format($upah_per_jam,2)."</th>";
								echo "<th class='text-left'></th>";
								echo "<th class='text-left'>Last update kurs in rate man power</th>";
								echo "<th class='text-right text-primary'>".date('d-M-Y H:i:s',strtotime($header[0]->kurs_date))."</th>";
							echo "</tr>";
							echo "<tr>";
								echo "<th class='text-center'>&nbsp;</th>";
								echo "<th class='text-left'></th>";
								echo "<th class='text-left'></th>";
								echo "<th class='text-right'></th>";
								echo "<th class='text-left'></th>";
								echo "<th class='text-left'></th>";
								echo "<th class='text-right'></th>";
							echo "</tr>";
							echo "<tr>";
								echo "<th class='text-center'></th>";
								echo "<th class='text-left'>Dibulatkan</th>";
								echo "<th align='left'>$</th>";
								echo "<th class='text-right'>".number_format($UPAH_PER_JAM_USD,2)."</th>";
								echo "<th class='text-left'></th>";
								echo "<th class='text-left'>Rate per man hour</th>";
								echo "<th class='text-right'></th>";
							echo "</tr>";
						?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
