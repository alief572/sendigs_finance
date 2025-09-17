
<?php
$BERAT_MINUS = 0;
if(!empty($detail_additive)){
	foreach($detail_additive AS $val => $valx){ $val++;
		$detail_custom    = $this->db->get_where('bom_detail_custom',array('no_bom_detail'=>$valx['no_bom_detail'],'category'=>'additive'))->result();
		$PENGURANGAN_BERAT = 0;
		foreach($detail_custom AS $valx2){
			$PENGURANGAN_BERAT += $valx2->weight * $valx2->persen /100;
		}
		$BERAT_MINUS += $PENGURANGAN_BERAT;
	}
}

$TOTAL_PRICE_ALL = 0;

//default
foreach($detail AS $val => $valx){ $val++;
    $code_lv2		= (!empty($GET_LEVEL4[$valx['code_material']]['code_lv2']))?$GET_LEVEL4[$valx['code_material']]['code_lv2']:'-';
    $price_ref      = (!empty($GET_PRICE_REF[$valx['code_material']]['price_ref']))?$GET_PRICE_REF[$valx['code_material']]['price_ref']:0;
    $nm_category = strtolower(get_name('new_inventory_2','nama','code_lv2',$code_lv2));
    $berat_pengurang_additive = ($nm_category == 'resin')?$BERAT_MINUS:0;

    $berat_bersih = $valx['weight'] - $berat_pengurang_additive;
    $total_price = $berat_bersih * $price_ref;
    $TOTAL_PRICE_ALL += $total_price;
}

//additive
foreach($detail_additive AS $val => $valx){ $val++;
    $detail_custom    = $this->db->get_where('bom_detail_custom',array('no_bom_detail'=>$valx['no_bom_detail'],'category'=>'additive'))->result();
    foreach($detail_custom AS $valx2){
        $price_ref      = (!empty($GET_PRICE_REF[$valx2->code_material]['price_ref']))?$GET_PRICE_REF[$valx2->code_material]['price_ref']:0;
        $total_price    = $valx2->weight * $price_ref;
        $TOTAL_PRICE_ALL += $total_price;
    }
}

//topping
foreach($detail_topping AS $val => $valx){
    $detail_custom    = $this->db->get_where('bom_detail_custom',array('no_bom_detail'=>$valx['no_bom_detail'],'category'=>'topping'))->result();
    foreach($detail_custom AS $valx2){
        $price_ref      = (!empty($GET_PRICE_REF[$valx2->code_material]['price_ref']))?$GET_PRICE_REF[$valx2->code_material]['price_ref']:0;
        $total_price    = $valx2->weight * $price_ref;
        $TOTAL_PRICE_ALL += $total_price;
    }
}

?>

<div class="box box-primary">
	<div class="box-body">
        <form id="data-form" method="post">
            <input type="hidden" id='id' name='id' value="<?=$product_price[0]['id'];?>">
            <input type="hidden" id='no_bom' name='no_bom' value="<?=$product_price[0]['no_bom'];?>">
            <input type="hidden" id='kode' name='kode' value="<?=$product_price[0]['kode'];?>">
            <table id="example1" class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th class='text-center' width="3%">#</th>
                        <th class='text-center' width="25%">Element Costing</th>
                        <th class='text-center' width="17%">Rate</th>
                        <th class='text-right' width="12%">Price</th>
                        <th class='text-center'>Keterangan</th>
                        <th class='text-center' width="10%">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $SUM_SINGLE_PRODUCT = 0;
                    $T_CUTP_ct_setting = 0;
                    $T_CUTP_ct_produksi = 0;
                    $T_CUTP_biaya_setting_mp = 0;
                    $T_CUTP_qty_man_power = 0;
                    $T_CUTP_rate_man_power_usd = 0;

                    $CuttingProcess_RateCT = 0;
                    $CuttingProcess_RateMCH = 0;
                    $CuttingProcess_RateDEPT = 0;
                    $CuttingProcess_RateMOULD = 0;
                    foreach ($list_assembly_product as $key => $value) {
                        $SUM_SINGLE_PRODUCT += $value['product_price'] * $value['product_qty'];

                        $T_CUTP_ct_setting += $value['ct_setting'];
                        $T_CUTP_ct_produksi += $value['ct_produksi'];
                        $T_CUTP_biaya_setting_mp += $value['biaya_setting_mp'];
                        $T_CUTP_qty_man_power += $value['qty_man_power'];

                        if($value['rate_man_power_usd'] > 0){
                            $T_CUTP_rate_man_power_usd = $value['rate_man_power_usd'];
                        }
                        if($value['rate_cycletime'] > 0){
                            $CuttingProcess_RateCT += $value['rate_cycletime'];
                        }
                        if($value['rate_cycletime_machine'] > 0){
                            $CuttingProcess_RateMCH = $value['rate_cycletime_machine'];
                        }
                        if($value['rate_depresiasi'] > 0){
                            $CuttingProcess_RateDEPT = $value['rate_depresiasi'];
                        }
                        if($value['rate_mould'] > 0){
                            $CuttingProcess_RateMOULD = $value['rate_mould'];
                        }
                    }
                    echo "<tr>";
                        echo "<td class='text-center'>1</td>";
                        echo "<td>Single Product</td>";
                        echo "<td></td>";
                        echo "<td class='text-right'>".number_format($SUM_SINGLE_PRODUCT,2)."</td>";
                        echo "<td></td>";
                        echo "<td class='text-center'><span class='text-primary btncursor' id='btnShowProduct' data-bom='".$kode."' >Detail</span></td>";
                    echo "</tr>";
                    $SUM_CUTTING_PROCESS = 0;
                    foreach ($list_cutting_process as $key => $value) {
                        $SUM_CUTTING_PROCESS += $value['total_price'];
                    }
                    echo "<tr>";
                        echo "<td style='background-color:#cbcbcb;' class='text-center'>#</td>";
                        echo "<td style='background-color:#cbcbcb;' class='text-left text-bold' colspan='2'>Cutting Process</td>";
                        echo "<td style='background-color:#cbcbcb;' class='text-center'></td>";
                        echo "<td style='background-color:#cbcbcb;' colspan='2'><span id='CuttingProcess'>Show/Hide</span></td>";
                    echo "</tr>";
                    echo "<tr class='CuttingProcess'>";
                        echo "<td class='text-center'>1</td>";
                        echo "<td>Material Cutting Process</td>";
                        echo "<td></td>";
                        echo "<td class='text-right'>".number_format($SUM_CUTTING_PROCESS,2)."</td>";
                        echo "<td></td>";
                        echo "<td class='text-center'><span class='text-primary btncursor' id='btnShowMatCutProcess' data-bom='".$kode."' >Detail</span></td>";
                    echo "</tr>";
                    // echo "<tr>";
                    //     echo "<td class='text-center'>2</td>";
                    //     echo "<td class='text-left text-bold' colspan='5'>Biaya Setting</td>";
                    // echo "</tr>";
                    // echo "<tr>";
                    //     echo "<td></td>";
                    //     echo "<td>#.1. Biaya Setting MP</td>";
                    //     echo "<td class='text-center'>((".number_format($T_CUTP_ct_setting,2)." + ".number_format($T_CUTP_ct_produksi,2).") * ".number_format($T_CUTP_qty_man_power,2)." / 60) * ".number_format($T_CUTP_rate_man_power_usd,2)."</td>";
                    //     echo "<td class='text-right'>".number_format($T_CUTP_biaya_setting_mp,2)."</td>";
                    //     echo "<td>((Setup Time + Production Time) * MP / 60) * Rating MP</td>";
                    //     echo "<td class='text-center'></td>";
                    // echo "</tr>";
                    // echo "<tr>";
                    //     echo "<td>#.2. Biaya Setting Mesin</td>";
                    //     echo "<td class='text-center'>(".number_format($product_price[0]['ct_setting'],2)." + ".number_format($product_price[0]['ct_produksi'],2).") / 60 * ".number_format($product_price[0]['rate_depresiasi'],2)."</td>";
                    //     echo "<td class='text-right'>".number_format($product_price[0]['biaya_setting_mesin'],2)."</td>";
                    //     echo "<td>(Setup Time + Production Time) / 60 * Rating Mesin</td>";
                    //     echo "<td class='text-center'></td>";
                    // echo "</tr>";
                    // echo "<tr>";
                    //     echo "<td>#.3. Waste Setting Material</td>";
                    //     echo "<td class='text-center'>(".number_format($product_price[0]['waste_set_resin'],2)." + ".number_format($product_price[0]['waste_set_glass'],2).") * ".number_format($product_price[0]['berat_per_kg'],2)."</td>";
                    //     echo "<td class='text-right'>".number_format($product_price[0]['biaya_waste_set_mat'],2)."</td>";
                    //     echo "<td>Total berat Waste Setting * Harga per kilo</td>";
                    //     echo "<td class='text-center'></td>";
                    // echo "</tr>";
                    // echo "<tr>";
                    //     echo "<td>#.4. Total biaya setting</td>";
                    //     echo "<td class='text-center'></td>";
                    //     echo "<td class='text-right'>".number_format($product_price[0]['biaya_total_setting'],2)."</td>";
                    //     echo "<td>(#.1 + #.2 + #.3)</td>";
                    //     echo "<td class='text-center'></td>";
                    // echo "</tr>";
                    // echo "<tr>";
                    //     echo "<td>#.5. Charge Setting MOQ BOM</td>";
                    //     echo "<td class='text-center'>".number_format($product_price[0]['biaya_total_setting'],2)." / ".number_format($product_price[0]['bom_moq'],2)."</td>";
                    //     echo "<td class='text-right'>".number_format($product_price[0]['charge_setting_bom'],2)."</td>";
                    //     echo "<td>Total biaya setting / MOQ</td>";
                    //     echo "<td class='text-center'></td>";
                    // echo "</tr>";
                    // echo "<tr>";
                    //     echo "<td>#.6. Charge Setting MOQ CT</td>";
                    //     echo "<td class='text-center'>".number_format($product_price[0]['biaya_total_setting'],2)." / ".number_format($product_price[0]['ct_moq'],2)."</td>";
                    //     echo "<td class='text-right'>".number_format($product_price[0]['charge_setting_ct'],2)."</td>";
                    //     echo "<td>Total biaya setting / MOQ</td>";
                    //     echo "<td class='text-center'></td>";
                    // echo "</tr>";
                    echo "<tr class='CuttingProcess'>";
                        echo "<td class='text-center' rowspan='3'>2</td>";
                        echo "<td class='text-left text-bold' colspan='4'>Manpower</td>";
                    echo "</tr>";
                    foreach ($dataList as $key => $value) {
                        if($value['judul'] == 'Manpower'){
                            if($value['code'] == '2'){
                                $rate 	    = number_format($CuttingProcess_RateCT,2).' x '.number_format($T_CUTP_rate_man_power_usd,2);
                                $man_power	= $CuttingProcess_RateCT * $T_CUTP_rate_man_power_usd;
                                $detRate = "<span class='text-primary btncursor detailRate' id='btnShowMold' data-tanda='manpower' data-cost='".$T_CUTP_rate_man_power_usd."' data-id_product='".$header[0]->id_product."' data-no_bom='".$header[0]->no_bom."' data-category='addBO' >Detail</span>";
                            }
                            if($value['code'] == '3'){
                                $rate 	    = number_format($product_price[0]['cost_persen_indirect'],2)." %";
                                $rateMP	    = $CuttingProcess_RateCT * $T_CUTP_rate_man_power_usd;
                                $man_power 	= $rateMP * $product_price[0]['cost_persen_indirect'] / 100;
                                $detRate = "";
                            }
                            // $detRate = "";
                            echo "<tr class='CuttingProcess'>";
                                echo "<td>".$value['element_costing']."</td>";
                                echo "<td class='text-center'>".$rate."</td>";
                                echo "<td class='text-right'>".number_format($man_power,2)."</td>";
                                echo "<td>".$value['keterangan']."</td>";
                                echo "<td class='text-center'>".$detRate."</td>";
                            echo "</tr>";
                        }
                    }
                    echo "<tr class='CuttingProcess'>";
                        echo "<td class='text-center' rowspan='4'>3</td>";
                        echo "<td class='text-left text-bold' colspan='4'>Mesin, cetakan, consumable</td>";
                    echo "</tr>";
                    foreach ($dataList as $key => $value) {
                        if($value['judul'] == 'Mesin, cetakan, consumable'){
                            echo "<tr class='CuttingProcess'>";
                                if($value['code'] == '4'){
                                    $rate 	    = number_format($CuttingProcess_RateMCH,2).' x '.number_format($CuttingProcess_RateDEPT,2);
                                    $cost_machine	= $CuttingProcess_RateMCH * $CuttingProcess_RateDEPT;
                                    $detRate = "<span class='text-primary btncursor detailRate' id='btnShowMachine' data-tanda='machine' data-cost='".$CuttingProcess_RateDEPT."' data-id_product='".$header[0]->id_product."' data-no_bom='".$header[0]->no_bom."' data-category='addBO' >Detail</span>";
                                }
                                if($value['code'] == '5'){
                                    $rate 	    = number_format($CuttingProcess_RateMCH,2).' x '.number_format($CuttingProcess_RateMOULD,2);
                                    $cost_machine 	= $CuttingProcess_RateMCH * $CuttingProcess_RateMOULD;
                                    $detRate = "<span class='text-primary btncursor detailRate' id='btnShowMold' data-tanda='mold' data-cost='".$CuttingProcess_RateMOULD."' data-id_product='".$header[0]->id_product."' data-no_bom='".$header[0]->no_bom."' data-category='addBO' >Detail</span>";
                                }
                                if($value['code'] == '6'){
                                    $rate 	    = number_format($product_price[0]['cost_persen_consumable'],2)." %";
                                    $cost_machine 	= $SUM_CUTTING_PROCESS * ($product_price[0]['cost_persen_consumable'] / 100);
                                    $detRate = "";
                                }
                                // $detRate = "";
                                echo "<td>".$value['element_costing']."</td>";
                                echo "<td class='text-center'>".$rate."</td>";
                                echo "<td class='text-right'>".number_format($cost_machine,2)."</td>";
                                echo "<td>".$value['keterangan']."</td>";
                                echo "<td class='text-center'>".$detRate."</td>";
                            echo "</tr>";
                        }
                    }
                    foreach ($list_assembly as $key2 => $value2) {
                        // if($value2['berat_per_kg'] > 0){
                            $nmCategory = '';
                            $nmFilter = '';
                            if($value2['category'] == 'addJoint'){
                                $nmCategory = 'Material Joint & Finishing';
                                $nmFilter = 'mat joint';
                            }
                            if($value2['category'] == 'addFlatSheet'){
                                $nmCategory = 'Flat Sheet';
                                $nmFilter = 'material flat sheet';
                            }
                            if($value2['category'] == 'addEndPlate'){
                                $nmCategory = 'End Plate / Kick Plate';
                                $nmFilter = 'material end plate';
                            }
                            if($value2['category'] == 'addChequeredPlate'){
                                $nmCategory = 'Chequered Plate';
                                $nmFilter = 'material ukuran jadi';
                            }
                            if($value2['category'] == 'addOthers'){
                                $nmCategory = 'Others';
                                $nmFilter = 'material others';
                            }
                            echo "<tr>";
                                echo "<td style='background-color:#cbcbcb;' class='text-center'>#</td>";
                                echo "<td style='background-color:#cbcbcb;' class='text-left text-bold' colspan='2'>".$nmCategory."</td>";
                                echo "<td style='background-color:#cbcbcb;' class='text-center'></td>";
                                echo "<td style='background-color:#cbcbcb;' colspan='2'><span id='".$value2['category']."'>Show/Hide</span></td>";
                            echo "</tr>";
                            foreach ($dataList as $key => $value) {
                                if($value['judul'] == 'Material'){
                                    echo "<tr class='".$value2['category']."'>";
                                        echo "<td class='text-center'>1</td>";
                                        echo "<td class='text-bold'>".$value['element_costing']."</td>";
                                        echo "<td></td>";
                                        echo "<td class='text-right'>".number_format($value2['price_material'],2)."</td>";
                                        echo "<td>".$value['keterangan']."</td>";
                                        echo "<td class='text-center'>";
                                        echo "<span class='text-primary btncursor' id='btnShowMaterialAssembly' data-nm_class='".$nmFilter."' data-bom='".$value2['kode']."' >Detail</span>";
                                        echo "</td>";
                                    echo "</tr>";
                                }
                            }
                            //===============NEW=====================
                            // echo "<tr>";
                            //     echo "<td class='text-center' rowspan='7'>2</td>";
                            //     echo "<td class='text-left text-bold' colspan='4'>Biaya Setting</td>";
                            // echo "</tr>";
                            // echo "<tr>";
                            //     echo "<td>#.1. Biaya Setting MP</td>";
                            //     echo "<td class='text-center'>((".number_format($value2['ct_setting'],2)." + ".number_format($value2['ct_produksi'],2).") * ".number_format($value2['qty_man_power'],2)." / 60) * ".number_format($value2['rate_man_power_usd'],2)."</td>";
                            //     echo "<td class='text-right'>".number_format($value2['biaya_setting_mp'],2)."</td>";
                            //     echo "<td>((Setup Time + Production Time) * MP / 60) * Rating MP</td>";
                            //     echo "<td class='text-center'></td>";
                            // echo "</tr>";
                            // echo "<tr>";
                            //     echo "<td>#.2. Biaya Setting Mesin</td>";
                            //     echo "<td class='text-center'>(".number_format($value2['ct_setting'],2)." + ".number_format($value2['ct_produksi'],2).") / 60 * ".number_format($value2['rate_depresiasi'],2)."</td>";
                            //     echo "<td class='text-right'>".number_format($value2['biaya_setting_mesin'],2)."</td>";
                            //     echo "<td>(Setup Time + Production Time) / 60 * Rating Mesin</td>";
                            //     echo "<td class='text-center'></td>";
                            // echo "</tr>";
                            // echo "<tr>";
                            //     echo "<td>#.3. Waste Setting Material</td>";
                            //     echo "<td class='text-center'>(".number_format($value2['waste_set_resin'],2)." + ".number_format($value2['waste_set_glass'],2).") * ".number_format($value2['berat_per_kg'],2)."</td>";
                            //     echo "<td class='text-right'>".number_format($value2['biaya_waste_set_mat'],2)."</td>";
                            //     echo "<td>Total berat Waste Setting * Harga per kilo</td>";
                            //     echo "<td class='text-center'></td>";
                            // echo "</tr>";
                            // echo "<tr>";
                            //     echo "<td>#.4. Total biaya setting</td>";
                            //     echo "<td class='text-center'></td>";
                            //     echo "<td class='text-right'>".number_format($value2['biaya_total_setting'],2)."</td>";
                            //     echo "<td>(#.1 + #.2 + #.3)</td>";
                            //     echo "<td class='text-center'></td>";
                            // echo "</tr>";
                            // echo "<tr>";
                            //     echo "<td>#.5. Charge Setting MOQ BOM</td>";
                            //     echo "<td class='text-center'>".number_format($value2['biaya_total_setting'],2)." / ".number_format($value2['bom_moq'],2)."</td>";
                            //     echo "<td class='text-right'>".number_format($value2['charge_setting_bom'],2)."</td>";
                            //     echo "<td>Total biaya setting / MOQ</td>";
                            //     echo "<td class='text-center'></td>";
                            // echo "</tr>";
                            // echo "<tr>";
                            //     echo "<td>#.6. Charge Setting MOQ CT</td>";
                            //     echo "<td class='text-center'>".number_format($value2['biaya_total_setting'],2)." / ".number_format($value2['ct_moq'],2)."</td>";
                            //     echo "<td class='text-right'>".number_format($value2['charge_setting_ct'],2)."</td>";
                            //     echo "<td>Total biaya setting / MOQ</td>";
                            //     echo "<td class='text-center'></td>";
                            // echo "</tr>";
                            echo "<tr class='".$value2['category']."'>";
                                echo "<td class='text-center' rowspan='3'>2</td>";
                                echo "<td class='text-left text-bold' colspan='4'>Manpower</td>";
                            echo "</tr>";
                            foreach ($dataList as $key => $value) {
                                if($value['judul'] == 'Manpower'){
                                    if($value['code'] == '2'){
                                        $rate 	    = number_format($value2['rate_cycletime'],2).' x '.number_format($value2['rate_man_power_usd'],2);
                                        $man_power	= $value2['rate_cycletime'] * $value2['rate_man_power_usd'];
                                        $detRate = "<span class='text-primary btncursor detailRate' id='btnShowMold' data-tanda='manpower' data-cost='".$value2['rate_man_power_usd']."' data-id_product='".$header[0]->id_product."' data-no_bom='".$header[0]->no_bom."' data-category='".$value2['category']."'>Detail</span>";
                                    }
                                    if($value['code'] == '3'){
                                        $rate 	    = number_format($product_price[0]['cost_persen_indirect'],2)." %";
                                        $rateMP	    = $value2['rate_cycletime'] * $value2['rate_man_power_usd'];
                                        $man_power 	= $rateMP * $product_price[0]['cost_persen_indirect'] / 100;
                                        $detRate = "";
                                    }
                                    // $detRate = "";
                                    echo "<tr class='".$value2['category']."'>";
                                        echo "<td>".$value['element_costing']."</td>";
                                        echo "<td class='text-center'>".$rate."</td>";
                                        echo "<td class='text-right'>".number_format($man_power,2)."</td>";
                                        echo "<td>".$value['keterangan']."</td>";
                                        echo "<td class='text-center'>".$detRate."</td>";
                                    echo "</tr>";
                                }
                            }
                            echo "<tr class='".$value2['category']."'>";
                                echo "<td class='text-center' rowspan='4'>3</td>";
                                echo "<td class='text-left text-bold' colspan='4'>Mesin, cetakan, consumable</td>";
                            echo "</tr>";
                            foreach ($dataList as $key => $value) {
                                if($value['judul'] == 'Mesin, cetakan, consumable'){
                                    echo "<tr class='".$value2['category']."'>";
                                        if($value['code'] == '4'){
                                            $rate 	    = number_format($value2['rate_cycletime_machine'],2).' x '.number_format($value2['rate_depresiasi'],2);
                                            $cost_machine	= $product_price[0]['cost_machine'];
                                            $detRate = "<span class='text-primary btncursor detailRate' id='btnShowMachine' data-tanda='machine' data-cost='".$value2['rate_depresiasi']."' data-id_product='".$header[0]->id_product."' data-no_bom='".$header[0]->no_bom."' data-category='".$value2['category']."' >Detail</span>";
                                        }
                                        if($value['code'] == '5'){
                                            $rate 	    = number_format($value2['rate_cycletime_machine'],2).' x '.number_format($value2['rate_mould'],2);
                                            $cost_machine 	= $product_price[0]['cost_mould'];
                                            $detRate = "<span class='text-primary btncursor detailRate' id='btnShowMold' data-tanda='mold' data-cost='".$value2['rate_mould']."' data-id_product='".$header[0]->id_product."' data-no_bom='".$header[0]->no_bom."' data-category='".$value2['category']."' >Detail</span>";
                                        }
                                        if($value['code'] == '6'){
                                            $rate 	    = number_format($product_price[0]['cost_persen_consumable'],2)." %";
                                            $cost_machine 	= $product_price[0]['cost_consumable'];
                                            $detRate = "";
                                        }
                                        // $detRate = "";
                                        echo "<td>".$value['element_costing']."</td>";
                                        echo "<td class='text-center'>".$rate."</td>";
                                        echo "<td class='text-right'>".number_format($cost_machine,2)."</td>";
                                        echo "<td>".$value['keterangan']."</td>";
                                        echo "<td class='text-center'>".$detRate."</td>";
                                    echo "</tr>";
                                }
                            }
                        // }
                    }
                    echo "<tr>";
                        echo "<td class='text-center' rowspan='3'>4</td>";
                        echo "<td class='text-left text-bold' colspan='2'>Logistik</td>";
                        echo "<td class='text-right text-bold'>";
                        // echo "<button type='button' id='updateLogistik' class='btn btn-sm btn-success'>Update Logistik</button>";
                        echo "</td>";
                    echo "</tr>";
                    foreach ($dataList as $key => $value) {
                        if($value['judul'] == 'Logistik'){
                            echo "<tr>";
                                if($value['code'] == '7'){
                                    $rate 	    = number_format($product_price[0]['cost_persen_packing'],2)." %";
                                    $cost_packing	= $product_price[0]['cost_packing'];
                                }
                                if($value['code'] == '8'){
                                    $rate 	    = '';
                                    $cost_packing 	= $product_price[0]['cost_transport'];
                                }
                                echo "<td>".$value['element_costing']."</td>";
                                echo "<td class='text-center'>".$rate."</td>";
                                echo "<td class='text-right'>".number_format($cost_packing,2)."</td>";
                                echo "<td>".$value['keterangan']."</td>";
                                echo "<td></td>";
                            echo "</tr>";
                        }
                    }
                    $nomor = 4;
                    foreach ($dataList as $key => $value) { 
                        if($value['judul'] == 'Lainnya'){
                            $nomor++;

                            if($value['code'] == '9'){
                                $rate 	    = number_format($product_price[0]['cost_persen_enginnering'],2)." %";
                                $cost   	= $product_price[0]['cost_enginnering'];
                            }
                            if($value['code'] == '10'){
                                $rate 	    = number_format($product_price[0]['cost_persen_foh'],2)." %";
                                $cost    	= $product_price[0]['cost_foh'];
                            }
                            if($value['code'] == '11'){
                                $rate 	    = number_format($product_price[0]['cost_persen_fin_adm'],2)." %";
                                $cost   	= $product_price[0]['cost_fin_adm'];
                            }
                            if($value['code'] == '12'){
                                $rate 	    = number_format($product_price[0]['cost_persen_mkt_sales'],2)." %";
                                $cost    	= $product_price[0]['cost_mkt_sales'];
                            }
                            if($value['code'] == '13'){
                                $rate 	    = number_format($product_price[0]['cost_persen_interest'],2)." %";
                                $cost   	= $product_price[0]['cost_interest'];
                            }
                            if($value['code'] == '14'){
                                $rate 	    = number_format($product_price[0]['cost_persen_profit'],2)." %";
                                $cost    	= $product_price[0]['cost_profit'];
                            }
                            if($value['code'] == '15'){
                                $rate 	    = '';
                                $cost   	= $product_price[0]['cost_bottom_price'];
                            }
                            if($value['code'] == '16'){
                                $rate 	    = number_format($product_price[0]['cost_factor_kompetitif'],2);
                                $cost    	= 0;
                            }
                            if($value['code'] == '17'){
                                $rate 	    = '';
                                $cost   	= $product_price[0]['cost_bottom_selling'];
                            }
                            if($value['code'] == '18'){
                                $rate 	    = number_format($product_price[0]['cost_nego_allowance'],2)." %";
                                $cost    	= $product_price[0]['cost_allowance'];
                            }
                            if($value['code'] == '19'){
                                $rate 	    = '';
                                $cost    	= $product_price[0]['cost_price_final'];
                            }
                            echo "<tr>";
                                echo "<td class='text-center'>".$nomor."</td>";
                                echo "<td>".$value['element_costing']."</td>";
                                echo "<td class='text-center'>".$rate."</td>";
                                if($value['code'] == '16'){
                                    echo "<td class='text-right'></td>";
                                }
                                else{
                                    echo "<td class='text-right'>".number_format($cost,2)."</td>";
                                }
                                echo "<td>".$value['keterangan']."</td>";
                                echo "<td></td>";
                            echo "</tr>";
                        }
                    }

                    // echo "<tr>";
                    //     echo "<td class='text-center'></td>";
                    //     echo "<td>Product Without Setting</td>";
                    //     echo "<td class='text-center'></td>";
                    //     echo "<td class='text-right'>".number_format($cost,2)."</td>";
                    //     echo "<td></td>";
                    //     echo "<td></td>";
                    // echo "</tr>";
                    // $total_setting_bom = $cost + $product_price[0]['charge_setting_bom'];
                    // echo "<tr>";
                    //     echo "<td class='text-center'></td>";
                    //     echo "<td>Price product BOM MOQ</td>";
                    //     echo "<td class='text-center'>".number_format($cost,2)." + ".number_format($product_price[0]['charge_setting_bom'],2)."</td>";
                    //     echo "<td class='text-right'>".number_format($total_setting_bom,2)."</td>";
                    //     echo "<td>Product Without Setting + Charge Setting MOQ BOM + Total biaya setting</td>";
                    //     echo "<td></td>";
                    // echo "</tr>";
                    // $total_setting_ct = $cost + $product_price[0]['charge_setting_ct'];
                    // echo "<tr>";
                    //     echo "<td class='text-center'></td>";
                    //     echo "<td>Price product with setting MOQ</td>";
                    //     echo "<td class='text-center'>".number_format($cost,2)." + ".number_format($product_price[0]['charge_setting_ct'],2)."</td>";
                    //     echo "<td class='text-right'>".number_format($total_setting_ct,2)."</td>";
                    //     echo "<td>Product Without Setting + Charge Setting MOQ CT + Total biaya setting</td>";
                    //     echo "<td></td>";
                    // echo "</tr>";

                    // $cost = ($total_setting_bom < $total_setting_ct)?$total_setting_bom:$total_setting_ct;

                    // echo "<tr>";
                    //     echo "<td class='text-center'></td>";
                    //     echo "<td>Selling Price</td>";
                    //     echo "<td class='text-center'></td>";
                    //     echo "<td class='text-right'>".number_format($cost,2)."</td>";
                    //     echo "<td></td>";
                    //     echo "<td></td>";
                    // echo "</tr>";

                    $cost_pengajuan = ($product_price[0]['pengajuan_price_list'] > 0)?$product_price[0]['pengajuan_price_list']:$cost;
                    $kurs = ($product_price[0]['kurs'] > 0)?$product_price[0]['kurs']:'';
                    $price_idr = ($product_price[0]['price_idr'] > 0)?$product_price[0]['price_idr']:'';
                    ?>
                <tr>
                    <td></td>
                    <td colspan='2' class='text-bold'>Pengajuan Price List Costing</td>
                    <td class='text-right'><?=number_format($cost_pengajuan,2);?></td>
                    <td colspan='2'>
			
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan='2' class='text-bold'>Kurs</td>
                    <td class='text-right'><?=number_format($kurs,2);?></td>
                    <td colspan='2'>
					  
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan='2' class='text-bold'>Price IDR</td>
                    <td class='text-right'><?=number_format($price_idr,2);?></td>
                    <td colspan='2'>
					    <!-- <button type="submit" class="btn btn-primary" name="save" id="btnAjukan">Ajukan</button> -->
                        <button type="button" class="btn btn-danger" name="back" id="back">Back</button>
                    </td>
                </tr>
                <?php if(!empty($detail_ipp_ukuranjadi)){ ?>
                <tr>
                    <td></td>
                    <td class='text-bold'>Ukuran jadi</td>
                    <td colspan='4'>
                        <table class='table table-bordered'>
                            <tr class='bg-blue'>
                                <th class='text-center' width='20%'>Length</th>
                                <th class='text-center' width='20%'>Width</th>
                                <th class='text-center' width='20%'>Qty</th>
                                <th class='text-center' width='20%'>Price Unit (IDR)</th>
                                <th class='text-center' width='20%'>Total Price (IDR)</th>
                            </tr>
                            <?php
                            $SUM = 0;
                            foreach ($detail_ipp_ukuranjadi as $key => $value) {
                                echo "<tr>";
                                    echo "<td class='text-center'>".number_format($value['width'])."</td>";
                                    echo "<td class='text-center'>".number_format($value['length'])."</td>";
                                    echo "<td class='text-center'>".number_format($value['qty'])."</td>";
                                    echo "<td class='text-right'>".number_format($value['price_unit'])."</td>";
                                    echo "<td class='text-right'>".number_format($value['total_price'])."</td>";
                                echo "</tr>";

                                $SUM += $value['total_price'];
                            }
                            $selsih = $SUM - $price_idr;
                            echo "<tr>";
                                echo "<td colspan='3'></td>";
                                echo "<td class='text-bold text-right' style='vertical-align:middle;'>Total Price</td>";
                                echo "<td class='text-bold text-right'>".number_format($SUM,2)."</td>";
                            echo "</tr>";
                            echo "<tr>";
                                echo "<td colspan='3'></td>";
                                echo "<td class='text-bold text-right' style='vertical-align:middle;'>Total Costing</td>";
                                echo "<td class='text-bold text-right'>".number_format($price_idr,2)."</td>";
                            echo "</tr>";
                            echo "<tr>";
                                echo "<td colspan='3'></td>";
                                echo "<td class='text-bold text-right'>Selisih</td>";
                                echo "<td class='text-bold text-right'>".number_format($selsih,2)."</td>";
                            echo "</tr>";
                            ?>
                        </table>
                    </td>
                </tr>
                <?php }
                else{
                    echo "<input type='hidden' name='total_price_uj' id='total_price' class='form-control input-sm text-right autoNumeric2'  style='vertical-align:middle;' readonly>";
                    echo "<input type='hidden' name='total_idr_uj' id='total_idr' class='form-control input-sm text-right autoNumeric2' readonly>";
                    echo "<input type='hidden' name='selisih_uj' id='selisih' class='form-control input-sm text-right autoNumeric2' readonly>";
                } ?>
                </tbody>
            </table>
            
        </form>
	</div>
</div>

<div class="modal modal-default fade" id="dialog-popup" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" style='width:80%;'>
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel"><span class="fa fa-users"></span>&nbsp;Detail Data</h4>
      </div>
      <div class="modal-body" id="ModalView">
		...
      </div>
  </div>
</div>
</div>

<div class="modal modal-default fade" id="dialog-popup2" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" style='width:30%;'>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel2">Update Cost Logistik</h4>
            </div>
            <div class="modal-body" id="ModalView2">
            
                <div class="form-group row">
                    <div class="col-md-4">
                        <label for="customer">Cost Packing</label>
                    </div>
                    <div class="col-md-8">
                        <input type="text" id="new_packing" class='form-control input-md autoNumeric2' value="<?=$product_price[0]['cost_packing'];?>">
                        <input type="hidden" id="no_bom" class='form-control input-md' value="<?=$product_price[0]['no_bom'];?>">
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-4">
                        <label for="customer">Cost Transport</label>
                    </div>
                    <div class="col-md-8">
                        <input type="text" id="new_shipping" class='form-control input-md autoNumeric2' value="<?=$product_price[0]['cost_transport'];?>">
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-4">
                        <label for="customer"></label>
                    </div>
                    <div class="col-md-8">
                        <button type='button' class='btn btn-primary' id='btnLogistik'>Update</button>
                    </div>
                </div>
                 
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/js/jquery.maskMoney.js')?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>

<style>
    .btncursor{
        cursor:pointer;
    }
    #addJoint, #addFlatSheet, #addEndPlate, #addChequeredPlate, #addOthers, #CuttingProcess{
        cursor: pointer;
        color: blue;
        font-weight: bold;
    }
</style>
<!-- page script -->
<script type="text/javascript">
    $(document).ready(function(){
        $('.autoNumeric2').autoNumeric('init', {mDec: '2', aPad: false})
        $('#rate_1,#rate_2,#rate_4,#rate_5,#rate_8,#rate_15,#rate_16,#rate_19,#coa_14,#coa_15,#coa_16,#coa_17,#coa_18,#coa_19').prop('readonly', true);
        $('.addJoint, .addFlatSheet, .addEndPlate, .addChequeredPlate, .addOthers, .CuttingProcess').hide();

        $(document).on('click', '#addJoint', function(){
            $('.addJoint').toggle();
        });
        $(document).on('click', '#addFlatSheet', function(){
            $('.addFlatSheet').toggle();
        });
        $(document).on('click', '#addEndPlate', function(){
            $('.addEndPlate').toggle();
        });
        $(document).on('click', '#addChequeredPlate', function(){
            $('.addChequeredPlate').toggle();
        });
        $(document).on('click', '#addOthers', function(){
            $('.addOthers').toggle();
        });
        $(document).on('click', '#CuttingProcess', function(){
            $('.CuttingProcess').toggle();
        });

        $(document).on('click', '#btnShowMaterial', function(){
            var no_bom = $(this).data('bom');
            // alert(id);
            $("#myModalLabel").html("<b>Detail Price</b>");
            $.ajax({
                type:'POST',
                url: base_url+active_controller+'detail_material',
                data:{'no_bom':no_bom},
                success:function(data){
                    $("#dialog-popup").modal();
                    $("#ModalView").html(data);

                }
            })
        });

        $(document).on('click', '#btnShowMaterialAssembly', function(){
            var no_bom = $(this).data('bom');
            var nm_class = $(this).data('nm_class');
            // alert(id);
            $("#myModalLabel").html("<b>Detail Price</b>");
            $.ajax({
                type:'POST',
                url: base_url+active_controller+'detail_mat_assembly',
                data:{'no_bom':no_bom,'nm_class':nm_class},
                success:function(data){
                    $("#dialog-popup").modal();
                    $("#ModalView").html(data);

                }
            })
        });

        $(document).on('click', '#btnShowProduct', function(){
            var no_bom = $(this).data('bom');
            // alert(id);
            $("#myModalLabel").html("<b>Detail Single Product</b>");
            $.ajax({
                type:'POST',
                url: base_url+active_controller+'detail_single_product',
                data:{'no_bom':no_bom},
                success:function(data){
                    $("#dialog-popup").modal();
                    $("#ModalView").html(data);

                }
            })
        });

        $(document).on('click', '#btnShowMatCutProcess', function(){
            var no_bom = $(this).data('bom');
            // alert(id);
            $("#myModalLabel").html("<b>Detail Material Cutting Process</b>");
            $.ajax({
                type:'POST',
                url: base_url+active_controller+'detail_mat_cutting_process',
                data:{'no_bom':no_bom},
                success:function(data){
                    $("#dialog-popup").modal();
                    $("#ModalView").html(data);

                }
            })
        });

        $(document).on('keyup', '#pengajuan_price_list, #kurs', function(){
            var pengajuan_price_list    = getNum($('#pengajuan_price_list').val().split(",").join(""))
            var kurs                    = getNum($('#kurs').val().split(",").join(""))
            var total_price             = getNum($('#total_price').val().split(",").join(""))
            var price_idr               = pengajuan_price_list * kurs;
            // console.log('masuk')
            $('#price_idr').val(number_format(price_idr,2))
            $('#total_idr').val(number_format(price_idr,2))
            $('#selisih').val(number_format(total_price-price_idr,2))
        });

        $(document).on('keyup', '.changePrice', function(){
            var total_idr = getNum($('#price_idr').val().split(",").join(""))
            let SUM = 0
            let valueNilai
            let id
            let qty
            let totalPrice
            $('.changePrice').each(function(){
                id          = $(this).data('id')
                qty         = getNum($('#qty_'+id).val().split(",").join(""))
                valueNilai  = getNum($(this).val().split(",").join(""))

                totalPrice = valueNilai * qty

                $('#total_price_'+id).val(number_format(totalPrice,2))

                SUM += totalPrice
            })

            // console.log('masuk')
            $('#total_idr').val(number_format(total_idr,2))
            $('#total_price').val(number_format(SUM,2))
            $('#selisih').val(number_format(SUM-total_idr,2))
        });

        $(document).on('click', '.detailRate', function(){
            var id_product  = $(this).data('id_product');
            var cost       = $(this).data('cost');
            var tanda       = $(this).data('tanda');
            var no_bom       = $(this).data('no_bom');
            var category       = $(this).data('category');
            // alert(id);
            $("#myModalLabel").html("<b>Detail Price</b>");
            $.ajax({
                type:'POST',
                url: base_url+active_controller+'detail_machine_mold_ass',
                data:{'id_product':id_product,'tanda':tanda,'cost':cost,'no_bom':no_bom,'category':category},
                success:function(data){
                    $("#dialog-popup").modal();
                    $("#ModalView").html(data);

                }
            })
        });

        $(document).on('click', '#updateLogistik', function(){
            $("#dialog-popup2").modal();
        });

        $(document).on('click', '#back', function(){
		    window.location.href = base_url + active_controller;
		});
    })

    $(document).on('click', '#btnAjukan', function(e){
		e.preventDefault()
        // let id = $('#id').val()
        // let pengajuan_price_list = $('#pengajuan_price_list').val()
        // let kurs = $('#kurs').val()
        // let price_idr = $('#price_idr').val()
        // let total_price_uj = $('#total_price').val()
        // let total_idr_uj = $('#total_idr').val()
        // let selisih_uj = $('#selisih').val()
        // let ukuran_jadi_price = $("input[name=ukuran_jadi_price]").val();

        // var values = $("input[name='ukuran_jadi_price[]']")
        //       .map(function(){return $(this).val();}).get();

        // console.log('Hay'+values)
		swal({
		  title: "Anda Yakin?",
		  text: "Mengajukan Price Costing !",
		  type: "warning",
		  showCancelButton: true,
		  confirmButtonClass: "btn-info",
		  confirmButtonText: "Ya, Update!",
		  cancelButtonText: "Batal",
		  closeOnConfirm: false
		},
		function(){
        var formData 	=new FormData($('#data-form')[0]);
		  $.ajax({
			  type:'POST',
			  url:base_url+active_controller+'ajukan_product_price',
			  dataType : "json",
              data		: formData,
			//   data:{
            //     'id':id,
            //     'pengajuan_price_list':pengajuan_price_list,
            //     'kurs':kurs,
            //     'price_idr':price_idr,
            //     'ukuran_jadi_price':ukuran_jadi_price,
            //     'total_price_uj':total_price_uj,
            //     'total_idr_uj':total_idr_uj,
            //     'selisih_uj':selisih_uj
            // },
            processData	: false,
			contentType	: false,
            success:function(result){
                if(result.status == '1'){
                    swal({
                        title: "Sukses",
                        text : "Data berhasil diajuakan",
                        type : "success"
                    },
                    function (){
                        window.location.href = base_url + active_controller;
                    })
                }
                else {
                    swal({
                        title : "Error",
                        text  : "Data error. Gagal diajuakan",
                        type  : "error"
                    })
                }
            },
            error : function(){
				swal({
					  title : "Error",
					  text  : "Data error. Gagal request Ajax",
					  type  : "error"
					})
			    }
		    })
		});

	});

    $(document).on('click', '#btnLogistik', function(e){
		e.preventDefault()
        let id = $('#id').val()
        let new_packing = $('#new_packing').val()
        let no_bom = $('#no_bom').val()
        let new_shipping = $('#new_shipping').val()
		swal({
		  title: "Anda Yakin?",
		  text: "Update Cost Logistik !",
		  type: "warning",
		  showCancelButton: true,
		  confirmButtonClass: "btn-info",
		  confirmButtonText: "Ya, Update!",
		  cancelButtonText: "Batal",
		  closeOnConfirm: false
		},
		function(){
		  $.ajax({
			  type:'POST',
			  url:base_url+active_controller+'/update_product_price_satuan',
			  dataType : "json",
			  data:{
                'id':id,
                'new_packing':new_packing,
                'no_bom':no_bom,
                'new_shipping':new_shipping,
            },
            success:function(result){
                if(result.status == '1'){
                    swal({
                        title: "Sukses",
                        text : "Data berhasil diupdate",
                        type : "success"
                    },
                    function (){
                        window.location.href = base_url + active_controller + 'pengajuan_costing_ass/' + result.no_bom;
                    })
                }
                else {
                    swal({
                        title : "Error",
                        text  : "Data error. Gagal diupdate",
                        type  : "error"
                    })
                }
            },
            error : function(){
				swal({
					  title : "Error",
					  text  : "Data error. Gagal request Ajax",
					  type  : "error"
					})
			    }
		    })
		});

	});

    function number_format (number, decimals, dec_point, thousands_sep) {
        // Strip all characters but numerical ones.
        number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
        var n = !isFinite(+number) ? 0 : +number,
            prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
            sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
            dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
            s = '',
            toFixedFix = function (n, prec) {
                var k = Math.pow(10, prec);
                return '' + Math.round(n * k) / k;
            };
        // Fix for IE parseFloat(0.55).toFixed(0) = 0;
        s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
        if (s[0].length > 3) {
            s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
        }
        if ((s[1] || '').length < prec) {
            s[1] = s[1] || '';
            s[1] += new Array(prec - s[1].length + 1).join('0');
        }
        return s.join(dec);
    }

</script>
