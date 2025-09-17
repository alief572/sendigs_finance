
<div class="box box-primary">
	<div class="box-body">
		<div class="form-group row">
			<div class="col-md-12">
				<table width='100%' class="table table-sm table-bordered">
					<thead  class="thead">
						<tr>
							<td colspan='7'><b>PRODUCT NAME :</b> <?= strtoupper(get_name('new_inventory_4', 'nama', 'code_lv4', $id_product)); ?></td>
						</tr>
						<tr class='bg-blue'>
							<th class='text-center th' width='3%'>#</th>
							<th class='text-center th' width='25%'>Cost Center</th>
                            <?php
                            if($tanda == 'manpower'){
                            ?>
							<th class='text-center th' width='12%'>Total (Minutes)</th>
							<th class='text-center th' width='12%'>Man Power</th>
							<th class='text-center th' width='12%'>CT Man Power</th>
                            <?php }else{ ?>
                            <th class='text-center th'><?=$title;?></th>
							<th class='text-center th' width='12%'>Cycletime /Hour</th>
							<th class='text-center th' width='12%'><?=$title;?> Rate</th>
							<th class='text-center th' width='12%'>Cost</th>
                            <?php } ?>
						</tr>
					</thead>
					<tbody>
						<?php
                            if(!empty($header[0]->id_time)){
							$q_header_test = $this->db->query("SELECT * FROM cycletime_detail_header WHERE id_time='".$header[0]->id_time."'")->result_array();
						    $nox = 0;
							foreach($q_header_test AS $val2 => $val2x){ $nox++;
								
                                echo "<tr>";
                                    echo "<td align='center'>".$nox."</td>";
                                    echo "<td align='left'><b>".strtoupper(get_name('ms_costcenter', 'nama_costcenter', 'id_costcenter', $val2x['costcenter']))."</b></td>";
                                    
                                    if($tanda == 'manpower'){
                                        echo "<td align='right'></td>";
                                        echo "<td align='right'></td>";
                                        echo "<td align='right'></td>";
                                    }
                                    else{
                                        echo "<td align='right'></td>";
                                        echo "<td align='right'></td>";
                                        echo "<td align='right'></td>";
                                        echo "<td align='right'></td>";
                                    }
                                echo "</tr>";

                                $q_dheader_test = $this->db->query("SELECT * FROM cycletime_detail_detail WHERE id_costcenter='".$val2x['id_costcenter']."'")->result_array();
                                $SUM_CT = 0;
                                $SUM_TOTALTIME = 0;
                                $SUM_CT_TOTAL = 0;
                                foreach($q_dheader_test AS $val2D => $val2Dx){ $val2D++;
                                    $CT2 = (get_name('asset', 'nm_asset', 'kd_asset', $val2Dx['machine']) != '0')?get_name('asset', 'nm_asset', 'kd_asset', $val2Dx['machine']):'';
                                    $MP2 = (get_name('asset', 'nm_asset', 'kd_asset', $val2Dx['mould']) != '0')?get_name('asset', 'nm_asset', 'kd_asset', $val2Dx['mould']):'';

                                    $CT_TIME        = ($val2Dx['cycletime'] > 0)?$val2Dx['cycletime']:0;
                                    $CT_MP          = ($val2Dx['qty_mp'] > 0)?$val2Dx['qty_mp']:0;
                                    $CT_TOTAL       = $CT_TIME * $CT_MP;
                                    $SUM_CT_TOTAL   += $CT_TOTAL;

                                    $CT             = ($val2Dx['cycletime'] > 0)?$val2Dx['cycletime']/60:0;
                                    $TOTAL_TIME     = $CT * $cost;
                                    $SUM_CT         += $CT;
                                    $SUM_TOTALTIME  += $TOTAL_TIME;
                                    

                                    echo "<tr>";
                                        echo "<td align='center'></td>";
                                        echo "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".strtoupper($val2Dx['nm_process'])."</td>";
                                        if($tanda == 'manpower'){
                                            echo "<td align='center'>".number_format($CT_TIME)."</td>";
                                            echo "<td align='center'>".number_format($CT_MP)."</td>";
                                            echo "<td align='right'>".number_format($CT_TOTAL,2)."</td>";
                                        }
                                        else{
                                            echo "<td align='left'>";
                                                if($CT2 != '' AND $tanda == 'machine'){
                                                    echo "<span class='text-primary'>".$CT2."</span>";
                                                }
                                                if($MP2 != '' AND $tanda == 'mold'){
                                                    echo "<span class='text-primary'>".$MP2."</span>";
                                                }
                                            echo "</td>";
                                            echo "<td align='center'>".number_format($CT,2)."</td>";
                                            echo "<td align='center'>".number_format($cost,2)."</td>";
                                            echo "<td align='right'>".number_format($TOTAL_TIME,2)."</td>";
                                        }
                                    echo "</tr>";
                                }
                            }
							?>
						<tr>
							<td class='text-center'></td>
							<td class='text-center'></td>
                            <?php
                            if($tanda == 'manpower'){
                            ?>
                                <td class='text-right text-bold'></td>
                                <td class='text-right text-bold'>TOTAL TIME</td>
                                <td class='text-right text-bold'><?=number_format($SUM_CT_TOTAL,2);?></td>
                            <?php }else{ ?>
                                <td class='text-right text-bold'></td>
                                <td class='text-center text-bold'><?=number_format($SUM_CT,2);?></td>
                                <td class='text-right text-bold'></td>
                                <td class='text-right text-bold'><?=number_format($SUM_TOTALTIME,2);?></td>
                            <?php } ?>
                        </tr>
                        <?php
                        if($tanda == 'manpower'){
                        ?>
                            <tr>
                                <td class='text-right text-bold'></td>
                                <td class='text-right text-bold'></td>
                                <td class='text-right text-bold'></td>
                                <td class='text-right text-bold'>TOTAL TIME/60</td>
                                <td class='text-right text-bold'><?=number_format($SUM_CT_TOTAL/60,2);?></td>
                            </tr>
                            <tr>
                                <td class='text-right text-bold'></td>
                                <td class='text-right text-bold'></td>
                                <td class='text-right text-bold'></td>
                                <td class='text-right text-bold'>RATE MAN POWER</td>
                                <td class='text-right text-bold'><?=number_format($cost,2);?></td>
                            </tr>
                            <tr>
                                <td class='text-right text-bold'></td>
                                <td class='text-right text-bold'></td>
                                <td class='text-right text-bold'></td>
                                <td class='text-right text-bold'>PRICE</td>
                                <td class='text-right text-bold'><?=number_format($SUM_CT_TOTAL/60*$cost,2);?></td>
                            </tr>
                        <?php }
                        } ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
