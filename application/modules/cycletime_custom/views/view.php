
<div class="box box-primary">
	<div class="box-body">
		<div class="form-group row">
			<div class="col-md-12">
				<table id="example1" border='0' width='100%' class="table table-striped table-bordered table-hover table-condensed">
					<thead  class="thead">
						<tr>
							<td colspan='7'><b>PRODUCT NAME :</b> <?= strtoupper(get_name('new_inventory_4', 'nama', 'code_lv4', $header[0]->id_product)); ?></td>
						</tr>
						<tr class='bg-blue'>
							<th class='text-center th' width='3%'>#</th>
							<th class='text-center th'>Cost Center</th>
							<th class='text-center th'>Machine & Mold</th>
							<th class='text-center th' width='9%'>Time (minutes)</th>
							<th class='text-center th' width='9%'>Man Power</th>
							<th class='text-center th' width='9%'>Time x Man Power</th>
							<th class='text-center th'>Information</th>
							<th class='text-center th'>VA</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$q_header_test = $this->db->query("SELECT * FROM cycletime_custom_detail_header WHERE id_time='".$header[0]->id_time."'")->result_array();
						$nox = 0;
						$SUM_CT = 0;
						$SUM_MP = 0;
						$SUM_TOTALTIME = 0;
						foreach($q_header_test AS $val2 => $val2x){ $nox++;
								
							echo "<tr>";
							echo "<td align='center'>".$nox."</td>";
							echo "<td align='left'><b>".strtoupper(get_name('ms_costcenter', 'nama_costcenter', 'id_costcenter', $val2x['costcenter']))."</b></td>";
							echo "<td align='right'></td>";
							echo "<td align='right'></td>";
							echo "<td align='right'></td>";
							echo "<td align='right'></td>";
							echo "<td align='left'></td>";
							echo "<td align='left'></td>";
							echo "</tr>";
							$q_dheader_test = $this->db->query("SELECT * FROM cycletime_custom_detail_detail WHERE id_costcenter='".$val2x['id_costcenter']."'")->result_array();
							
							foreach($q_dheader_test AS $val2D => $val2Dx){ $val2D++;
									$nomor = ($val2D==1)?$val2D:'';

								$CT2 = (get_name('asset', 'nm_asset', 'kd_asset', $val2Dx['machine']) != '0')?get_name('asset', 'nm_asset', 'kd_asset', $val2Dx['machine']):'';
								$MP2 = (get_name('asset', 'nm_asset', 'kd_asset', $val2Dx['mould']) != '0')?get_name('asset', 'nm_asset', 'kd_asset', $val2Dx['mould']):'';

								$CT = ($val2Dx['cycletime'] != 0)?$val2Dx['cycletime']:0;
								$MP = ($val2Dx['qty_mp'] != 0)?$val2Dx['qty_mp']:0;
								$TOTAL_TIME = $CT * $MP;

								$SUM_CT += $CT;
								$SUM_MP += $MP;
								$SUM_TOTALTIME += $TOTAL_TIME;

								echo "<tr>";
									echo "<td align='center'></td>";
									echo "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Tipe: ".ucfirst($val2Dx['nm_process'])."</b><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".strtoupper($val2Dx['nm_process'])."</td>";
									echo "<td align='left'>";
										if($CT2 != ''){
											echo "<span class='text-primary'>".$CT2."</span>";
										}
										if($MP2 != ''){
											echo "<br><span class='text-primary'>".$MP2."</span>";
										}
									echo "</td>";
									echo "<td align='right'>".number_format($CT)."</td>";
									echo "<td align='right'>".$MP."</td>";
									echo "<td align='right'>".$TOTAL_TIME."</td>";
									echo "<td align='left'>".$val2Dx['note']."</td>";

									$vaNote = '';
									if($val2Dx['va'] == 'Y' OR $val2Dx['va'] == 'N'){
										$vaNote = ($val2Dx['va'] == 'Y')?'Value Added':'Non-Value Added';
									}
									echo "<td align='left'>".$vaNote."</td>";
								echo "</tr>";
							}
						}

						$totalTime = 0;
						$totalTimeMachine = 0;
						if($SUM_CT > 0){
							$totalTime = $SUM_CT / 60;
						}
						if($SUM_TOTALTIME > 0){
							$totalTimeMachine = $SUM_TOTALTIME / 60;
						}
						?>
					
						<tr>
							<td class='text-center'></td>
							<td class='text-center'></td>
							<td class='text-right text-bold'>TOTAL TIME</td>
							<td class='text-right text-bold'><?=$SUM_CT;?></td>
							<td class='text-right text-bold'><?=$SUM_MP;?></td>
							<td class='text-right text-bold'><?=$SUM_TOTALTIME;?></td>
							<td class='text-center'></td>
							<td class='text-center'></td>
						</tr>
						<tr>
							<td class='text-center'></td>
							<td class='text-center'></td>
							<td class='text-right text-bold'>TOTAL HOUR</td>
							<td class='text-right text-bold'><?=number_format($totalTime,2);?></td>
							<td class='text-center'></td>
							<td class='text-right text-bold'><?=number_format($totalTimeMachine,2);?></td>
							<td class='text-center'></td>
							<td class='text-center'></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
