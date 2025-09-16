
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

<div class="box">
	<div class="box-body">
        <form id="data-form" method="post">
            <input type="hidden" id='id' name='id' value="<?=$product_price[0]['id'];?>">
            <table id="example1" class="table table-sm table-bordered" hidden>
                <thead>
                    <tr>
                        <th class='text-center' width="5%">#</th>
                        <!-- <th class='text-center' width="5%">Code</th> -->
                        <th class='text-center' width="25%">Element Costing</th>
                        <th class='text-center' width="12%">Rate</th>
                        <th class='text-right' width="12%">Price</th>
                        <th class='text-center'>Keterangan</th>
                        <th class='text-center' width="10%">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    
                    foreach ($dataList as $key => $value) {
                        if($value['judul'] == 'Material'){
                            echo "<tr>";
                                echo "<td class='text-center'>1</td>";
                                echo "<td>".$value['element_costing']."</td>";
                                echo "<td></td>";
                                echo "<td class='text-right'>".number_format($product_price[0]['price_material'],2)."</td>";
                                echo "<td>".$value['keterangan']."</td>";
                                echo "<td class='text-center'><span class='text-primary btncursor' id='btnShowMaterial' data-bom='".$no_bom."' >Detail</span></td>";
                            echo "</tr>";
                        }
                    }
                    echo "<tr>";
                        echo "<td class='text-center' rowspan='3'>2</td>";
                        // echo "<td class='text-center'></td>";
                        echo "<td class='text-left text-bold' colspan='4'>Manpower</td>";
                    echo "</tr>";
                    foreach ($dataList as $key => $value) {
                        if($value['judul'] == 'Manpower'){
                            if($value['code'] == '2'){
                                $rate 	    = number_format($product_price[0]['rate_cycletime'],2).' x '.number_format($product_price[0]['rate_man_power_usd'],2);
                                $man_power	= $product_price[0]['cost_direct_labout'];
                                $detRate = "<span class='text-primary btncursor detailRate' id='btnShowMold' data-tanda='manpower' data-cost='".$product_price[0]['rate_man_power_usd']."' data-id_product='".$header[0]->id_product."' >Detail</span>";
                            }
                            if($value['code'] == '3'){
                                $rate 	    = number_format($product_price[0]['cost_persen_indirect'],2)." %";
                                $man_power 	= $product_price[0]['cost_indirect'];
                                $detRate = "";
                            }
			               
                            echo "<tr>";
                                echo "<td>".$value['element_costing']."</td>";
                                echo "<td class='text-center'>".$rate."</td>";
                                echo "<td class='text-right'>".number_format($man_power,2)."</td>";
                                echo "<td>".$value['keterangan']."</td>";
                                echo "<td class='text-center'>".$detRate."</td>";
                            echo "</tr>";
                        }
                    }
                    echo "<tr>";
                        echo "<td class='text-center' rowspan='4'>3</td>";
                        // echo "<td class='text-center'></td>";
                        echo "<td class='text-left text-bold' colspan='4'>Mesin, cetakan, consumable</td>";
                    echo "</tr>";
                    foreach ($dataList as $key => $value) {
                        if($value['judul'] == 'Mesin, cetakan, consumable'){
                            echo "<tr>";
                                if($value['code'] == '4'){
                                    $rate 	    = number_format($product_price[0]['rate_cycletime_machine'],2).' x '.number_format($product_price[0]['rate_depresiasi'],2);
                                    $cost_machine	= $product_price[0]['cost_machine'];
                                    $detRate = "<span class='text-primary btncursor detailRate' id='btnShowMachine' data-tanda='machine' data-cost='".$product_price[0]['rate_depresiasi']."' data-id_product='".$header[0]->id_product."' >Detail</span>";
                                }
                                if($value['code'] == '5'){
                                    $rate 	    = number_format($product_price[0]['rate_cycletime_machine'],2).' x '.number_format($product_price[0]['rate_mould'],2);
                                    $cost_machine 	= $product_price[0]['cost_mould'];
                                    $detRate = "<span class='text-primary btncursor detailRate' id='btnShowMold' data-tanda='mold' data-cost='".$product_price[0]['rate_mould']."' data-id_product='".$header[0]->id_product."' >Detail</span>";
                                }
                                if($value['code'] == '6'){
                                    $rate 	    = number_format($product_price[0]['cost_persen_consumable'],2)." %";
                                    $cost_machine 	= $product_price[0]['cost_consumable'];
                                    $detRate = "";
                                }
                                echo "<td>".$value['element_costing']."</td>";
                                echo "<td class='text-center'>".$rate."</td>";
                                echo "<td class='text-right'>".number_format($cost_machine,2)."</td>";
                                echo "<td>".$value['keterangan']."</td>";
                                echo "<td class='text-center'>".$detRate."</td>";
                            echo "</tr>";
                        }
                    }
                    echo "<tr>";
                        echo "<td class='text-center' rowspan='3'>4</td>";
                        // echo "<td class='text-center'></td>";
                        echo "<td class='text-left text-bold' colspan='4'>Logistik</td>";
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
                    ?>
                </tbody>
            </table>
            <h4>Detail Product</h4>
            <table class="table table-sm">
                <tr>
                    <td class='text-bold'>Product Master</td>
                    <td colspan='6'><?=$product_price[0]['product_master'];?></td>
                </tr>
                <tr>
                    <td class='text-bold'>Product Costing</td>
                    <td class='text-bold text-right' colspan='2'><?=number_format($product_price[0]['cost_price_final'],2);?></td>
                    <td class='text-bold' colspan='4'></td>
                </tr>
                <tr>
                    <td width='15%' class='text-bold'>Price List Current USD</td>
                    <td width='10%' class='text-bold text-primary text-right' colspan='2'><?=number_format($product_price[0]['price_list'],2);?></td>
                    <td width='15%' class='text-bold'>Price List Current IDR</td>
                    <td width='10%' class='text-bold text-primary text-right' colspan='2'><?=number_format($product_price[0]['price_list_idr'],2);?></td>
                    <td></td>
                </tr>
                <tr>
                    <td class='text-bold'>Price List Pengajuan USD</td>
                    <td class='text-bold text-success text-right' colspan='2'><?=number_format($product_price[0]['pengajuan_price_list'],2);?></td>
                    <td class='text-bold'>Price List Pengajuan IDR</td>
                    <td class='text-bold text-success text-right' colspan='2'><?=number_format($product_price[0]['price_idr'],2);?></td>
                    <td></td>
                </tr>
                <tr>
                    <td class='text-bold'>Approval Price List USD</td>
                    <td class='text-bold text-success text-right' colspan='2'><input type="text" name='price_list' id='price_list' class='form-control text-right input-md autoNumeric2 text-bold' autocomplate='off' value='<?=$product_price[0]['pengajuan_price_list'];?>'></td>
                    <td class='text-bold'>Approval Price List IDR</td>
                    <td class='text-bold text-success text-right' colspan='2'><input type="text" name='price_list_idr' id='price_list_idr' class='form-control text-right input-md autoNumeric2 text-bold' autocomplate='off' value='<?=$product_price[0]['price_idr'];?>'></td>
                    <td></td>
                </tr>
                <tr>
                    <td class='text-bold'>Kurs</td>
                    <td class='text-bold text-right' colspan='2'><input type="text" name='kurs' id='kurs' class='form-control text-right input-md autoNumeric2 text-bold' autocomplate='off' value='<?=$product_price[0]['kurs'];?>'></td>
                    <td class='text-bold'>Harga Price Orindo</td>
                    <?php
                    $persen_orindo = (!empty($product_price[0]['price_persen_orindo']))?$product_price[0]['price_persen_orindo']:50;
                    $price_list_idr_orindo = (!empty($product_price[0]['price_list_idr_orindo']))?$product_price[0]['price_list_idr_orindo']:50/100*$product_price[0]['price_idr'];
                    ?>
                    <td class='text-bold text-success text-right'>
                        <div class='input-group'>
                            <input type="text" name='price_persen_orindo' id='price_persen_orindo' class='form-control text-center input-md autoNumeric2 text-bold' autocomplate='off' value='<?=$persen_orindo;?>'>
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default btn-flat">%</button>
                            </span>
                        </div>
                    </td>
                    <td class='text-bold text-success text-right' colspan='2'><input type="text" name='price_list_idr_orindo' id='price_list_idr_orindo' style='width:200px;' class='form-control text-right input-md autoNumeric2 text-bold' autocomplate='off' value='<?=$price_list_idr_orindo;?>' readonly></td>
                </tr>

                <?php if(!empty($detail_ipp_ukuranjadi)){ ?>
                <tr>
                    <td class='text-bold'>Ukuran jadi</td>
                    <td colspan='6'>
                        <table class='table table-bordered'>
                            <tr class='bg-blue'>
                                <th class='text-center' width='11%'>Length</th>
                                <th class='text-center' width='11%'>Width</th>
                                <th class='text-center' width='11%'>Qty</th>
                                <th class='text-center' width='14%'>Price Unit (IDR)</th>
                                <th class='text-center' width='14%'>Total Price (IDR)</th>
                                <th class='text-center' width='14%'>Approve Price</th>
                                <th class='text-center' width='11%'>(%) Orindo</th>
                                <th class='text-center' width='14%'>Price Orindo</th>
                            </tr>
                            <?php
                            $SUM_APP = 0;
                            $SUM_ORINDO = 0;
                            foreach ($detail_ipp_ukuranjadi as $key => $value) {
                                echo "<tr>";
                                    echo "<td class='text-center'>".number_format($value['width'])."</td>";
                                    echo "<td class='text-center'>".number_format($value['length'])."</td>";
                                    echo "<td class='text-center'>".number_format($value['qty'])."</td>";
                                    echo "<td class='text-right'>".number_format($value['price_unit'])."</td>";
                                    echo "<td class='text-right'>".number_format($value['total_price'])."</td>";
                                    echo "<td hidden>
                                            <input type='hidden' name='ukuran_jadi_price[$key][id]' class='form-control input-sm' value='".$value['id']."'>
                                            <input type='text' name='ukuran_jadi_price[$key][price_unit]' id='price_unit_".$key."' data-id='".$key."' class='form-control input-sm autoNumeric2 text-right' value='".$value['price_unit']."'>
                                            </td>";
                                    echo "<td hidden>
                                            <input type='text' name='ukuran_jadi_price[$key][total_price]' id='total_price_".$key."' class='form-control input-sm text-right autoNumeric2' readonly value='".$value['total_price']."'>
                                            </td>";
                                     echo "<td>
                                            <input type='text' name='ukuran_jadi_price[$key][app_price]' id='app_price_".$key."' data-id='".$key."' class='form-control input-sm autoNumeric2 text-right changePrice' value='".$value['total_price']."'>
                                            </td>";
                                    echo "<td>
                                            <input type='text' name='ukuran_jadi_price[$key][persen_orindo]' id='persen_orindo_".$key."' data-id='".$key."' class='form-control input-sm autoNumeric2 text-center' readonly value='".$persen_orindo."'>
                                            </td>";
                                    $harga_idr_orindo = ($persen_orindo/100 * $value['total_price']) + $value['total_price'];
                                    echo "<td>
                                            <input type='text' name='ukuran_jadi_price[$key][harga_idr_orindo]' id='harga_idr_orindo_".$key."' data-id='".$key."' class='form-control input-sm autoNumeric2 text-right' readonly value='".$harga_idr_orindo."'>
                                            </td>";
                                echo "</tr>";

                                $SUM_APP += $value['total_price'];
                                $SUM_ORINDO += $harga_idr_orindo;
                            }
                            echo "<tr>";
                                echo "<td colspan='3'><button type='button' class='btn btn-default text-bold' id='calt'>Calculation</button></td>";
                                echo "<td class='text-bold text-right' style='vertical-align:middle;'>Total Price</td>";
                                echo "<td class='text-right'>".number_format($product_price[0]['total_price_uj'])."</td>";
                                echo "<td class='text-right' id='app_price'>".number_format($SUM_APP)."</td>";
                                echo "<td class='text-right'></td>";
                                echo "<td class='text-right' id='app_price_orindo'>".number_format($SUM_ORINDO)."</td>";
                                // echo "<td><input type='text' name='total_price_uj' id='total_price' class='form-control input-sm text-right  style='vertical-align:middle;'autoNumeric2' readonly></td>";
                            echo "</tr>";
                            echo "<tr>";
                                echo "<td colspan='3'></td>";
                                echo "<td class='text-bold text-right' style='vertical-align:middle;'>Total Costing</td>";
                                echo "<td class='text-right'>".number_format($product_price[0]['total_idr_uj'])."</td>";
                                echo "<td class='text-right' id='idr_price'>".number_format($product_price[0]['price_idr'])."</td>";
                                echo "<td class='text-right'></td>";
                                echo "<td class='text-right' id='idr_price_orindo'>".number_format($price_list_idr_orindo)."</td>";
                                // echo "<td><input type='text' name='total_idr_uj' id='total_idr' class='form-control input-sm text-right autoNumeric2' readonly></td>";
                            echo "</tr>";
                            echo "<tr>";
                                echo "<td colspan='3'></td>";
                                echo "<td class='text-bold text-right'>Selisih</td>";
                                echo "<td class='text-right'>".number_format($product_price[0]['selisih_uj'])."</td>";
                                echo "<td class='text-right' id='selisih_price'>".number_format($SUM_APP - $product_price[0]['price_idr'])."</td>";
                                echo "<td class='text-right'></td>";
                                echo "<td class='text-right' id='selisih_price_orindo'>".number_format($SUM_ORINDO - $price_list_idr_orindo)."</td>";
                                // echo "<td><input type='text' name='selisih_uj' id='selisih' class='form-control input-sm text-right autoNumeric2' readonly></td>";
                            echo "</tr>";
                            ?>
                        </table>
                    </td>
                </tr>
                <?php }
                ?>
                <tr>
                    <td class='text-bold'>Status</td>
                    <td colspan='6'>
                        <select name="status" id="status" class='form-control'>
                            <option value="A">Approval</option>
                            <option value="R">Reject</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='text-bold'>Reason</td>
                    <td colspan='6'>
                        <textarea name="reason" id="reason" rows="3" class='form-control'></textarea>
                    </td>
                </tr>
                <tr>
                    <td class='text-bold'></td>
                    <td colspan='6'>
                        <button type="submit" class="btn btn-primary" name="save" id="btnAjukan">Process</button>
                        <button type="button" class="btn btn-danger" name="back" id="back">Back</button>
                    </td>
                </tr>
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

<script src="<?= base_url('assets/js/jquery.maskMoney.js')?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>

<style>
    .btncursor{
        cursor:pointer;
    }
</style>
<!-- page script -->
<script type="text/javascript">
    $(document).ready(function(){
        $('.autoNumeric2').autoNumeric('init', {mDec: '2', aPad: false})
        $('#rate_1,#rate_2,#rate_4,#rate_5,#rate_8,#rate_15,#rate_16,#rate_19,#coa_14,#coa_15,#coa_16,#coa_17,#coa_18,#coa_19').prop('readonly', true);

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

        $(document).on('keyup', '#price_list, #kurs, #price_persen_orindo', function(){
            var price_list              = getNum($('#price_list').val().split(",").join(""))
            var kurs                    = getNum($('#kurs').val().split(",").join(""))
            var price_persen_orindo     = getNum($('#price_persen_orindo').val().split(",").join(""))
            var price_idr               = price_list * kurs;
            var price_orindo            = price_idr + (price_persen_orindo/100 * price_idr);
            // console.log('masuk')
            $('#price_list_idr').val(number_format(price_idr))
            $('#price_list_idr_orindo').val(number_format(price_orindo))
        });

        $(document).on('keyup', '#price_list_idr, #kurs, #price_persen_orindo', function(){
            var price_list              = getNum($('#price_list_idr').val().split(",").join(""))
            var kurs                    = getNum($('#kurs').val().split(",").join(""))
            var price_persen_orindo     = getNum($('#price_persen_orindo').val().split(",").join(""))
            var price_idr               = price_list / kurs;
            var price_orindo            = price_list + (price_persen_orindo/100 * price_list);
            // console.log('masuk')
            $('#price_list').val(number_format(price_idr,2))
            $('#price_list_idr_orindo').val(number_format(price_orindo))
        });

        $(document).on('keyup', '.changePrice', function(){
            caltUkuranJadi()
        });

        $(document).on('click', '#calt', function(){
            caltUkuranJadi()
        });

        $(document).on('click', '.detailRate', function(){
            var id_product  = $(this).data('id_product');
            var cost       = $(this).data('cost');
            var tanda       = $(this).data('tanda');
            // alert(id);
            $("#myModalLabel").html("<b>Detail Price</b>");
            $.ajax({
                type:'POST',
                url: base_url+active_controller+'detail_machine_mold',
                data:{'id_product':id_product,'tanda':tanda,'cost':cost},
                success:function(data){
                    $("#dialog-popup").modal();
                    $("#ModalView").html(data);

                }
            })
        });

        $(document).on('click', '#back', function(){
		    window.location.href = base_url + active_controller;
		});
    })

    $(document).on('click', '#btnAjukan', function(e){
		e.preventDefault()
        let id = $('#id').val()
        let status = $('#status').val()
        let reason = $('#reason').val()
        let price_list = $('#price_list').val()
        let price_list_idr = $('#price_list_idr').val()
        let price_persen_orindo = $('#price_persen_orindo').val()
        let price_list_idr_orindo = $('#price_list_idr_orindo').val()
        let kurs = $('#kurs').val()
        let labelReason = (status == 'A')?'Approve':'Reject'
		swal({
		  title: labelReason+"?",
		  text: "Confirm Price List !",
		  type: "warning",
		  showCancelButton: true,
		  confirmButtonClass: "btn-info",
		  confirmButtonText: "Ya!",
		  cancelButtonText: "Batal",
		  closeOnConfirm: false
		},
		function(){
            var formData 	=new FormData($('#data-form')[0]);
		  $.ajax({
			  type:'POST',
			  url:base_url+active_controller+'/confirm_product_price',
			  dataType : "json",
              data		: formData,
              processData	: false,
			contentType	: false,
			//   data:{
            //     'id':id,
            //     'status':status,
            //     'price_list':price_list,
            //     'price_list_idr':price_list_idr,
            //     'price_persen_orindo':price_persen_orindo,
            //     'price_list_idr_orindo':price_list_idr_orindo,
            //     'kurs':kurs,
            //     'reason':reason
            // },
            success:function(result){
                if(result.status == '1'){
                    swal({
                        title: "Success",
                        text : result.pesan,
                        type : "success"
                    },
                    function (){
                        window.location.href = base_url + active_controller;
                    })
                }
                else {
                    swal({
                        title : "Error",
                        text  : result.pesan,
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

    function caltUkuranJadi(){
        var price_list_idr          = getNum($('#price_list_idr').val().split(",").join(""))
        var price_list_idr_orindo   = getNum($('#price_list_idr_orindo').val().split(",").join(""))
        let SUM = 0
        let SUM_ORINDO = 0
        let valueNilai
        let id
        let qty
        let persen
        let totalPrice
        $('.changePrice').each(function(){
            id          = $(this).data('id')
            persen       = getNum($('#persen_orindo_'+id).val().split(",").join(""))
            valueNilai  = getNum($(this).val().split(",").join(""))

            totalPrice = valueNilai + (valueNilai * (persen/100))

            $('#harga_idr_orindo_'+id).val(number_format(totalPrice,2))

            SUM += valueNilai
            SUM_ORINDO += totalPrice
        })

        // console.log('masuk')
        $('#app_price').text(number_format(SUM))
        $('#app_price_orindo').text(number_format(SUM_ORINDO))

        $('#idr_price').text(number_format(price_list_idr))
        $('#idr_price_orindo').text(number_format(price_list_idr_orindo))

        $('#selisih_price').text(number_format(SUM - price_list_idr))
        $('#selisih_price_orindo').text(number_format(SUM_ORINDO - price_list_idr_orindo))
    }

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
