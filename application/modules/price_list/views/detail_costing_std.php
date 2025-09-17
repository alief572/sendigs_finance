
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
            <table id="example1" class="table table-sm table-bordered">
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
                    //===============NEW=====================
                    echo "<tr>";
                        echo "<td class='text-center' rowspan='7'>#</td>";
                        echo "<td class='text-left text-bold' colspan='4'>Biaya Setting</td>";
                    echo "</tr>";
                    echo "<tr>";
                        echo "<td>#.1. Biaya Setting MP</td>";
                        echo "<td class='text-center'>((".number_format($product_price[0]['ct_setting'],2)." + ".number_format($product_price[0]['ct_produksi'],2).") * ".number_format($product_price[0]['rate_cycletime'],2).") * ".number_format($product_price[0]['rate_man_power_usd'],2)."</td>";
                        echo "<td class='text-right'>".number_format($product_price[0]['biaya_setting_mp'],2)."</td>";
                        echo "<td>((Setup Time + Production Time) * MP / 60) * Rating MP</td>";
                        echo "<td class='text-center'></td>";
                    echo "</tr>";
                    echo "<tr>";
                        echo "<td>#.2. Biaya Setting Mesin</td>";
                        echo "<td class='text-center'>(".number_format($product_price[0]['ct_setting'],2)." + ".number_format($product_price[0]['ct_produksi'],2).") / 60 * ".number_format($product_price[0]['rate_depresiasi'],2)."</td>";
                        echo "<td class='text-right'>".number_format($product_price[0]['biaya_setting_mesin'],2)."</td>";
                        echo "<td>(Setup Time + Production Time) / 60 * Rating Mesin</td>";
                        echo "<td class='text-center'></td>";
                    echo "</tr>";
                    echo "<tr>";
                        echo "<td>#.3. Waste Setting Material</td>";
                        echo "<td class='text-center'>(".number_format($product_price[0]['waste_set_resin'],2)." + ".number_format($product_price[0]['waste_set_glass'],2).") * ".number_format($product_price[0]['berat_per_kg'],2)."</td>";
                        echo "<td class='text-right'>".number_format($product_price[0]['biaya_waste_set_mat'],2)."</td>";
                        echo "<td>Total berat Waste Setting * Harga per kilo</td>";
                        echo "<td class='text-center'></td>";
                    echo "</tr>";
                    echo "<tr>";
                        echo "<td>#.4. Total biaya setting</td>";
                        echo "<td class='text-center'></td>";
                        echo "<td class='text-right'>".number_format($product_price[0]['biaya_total_setting'],2)."</td>";
                        echo "<td>(#.1 + #.2 + #.3)</td>";
                        echo "<td class='text-center'></td>";
                    echo "</tr>";
                    echo "<tr>";
                        echo "<td>#.5. Charge Setting MOQ 71 (MOQ BOM)</td>";
                        echo "<td class='text-center'>".number_format($product_price[0]['biaya_total_setting'],2)." / ".number_format($product_price[0]['bom_moq'],2)."</td>";
                        echo "<td class='text-right'>".number_format($product_price[0]['charge_setting_bom'],2)."</td>";
                        echo "<td>Total biaya setting / MOQ 71 (Masih Revisi)</td>";
                        echo "<td class='text-center'></td>";
                    echo "</tr>";
                    echo "<tr>";
                        echo "<td>#.6. Charge Setting MOQ 176 (MOQ CT)</td>";
                        echo "<td class='text-center'>".number_format($product_price[0]['biaya_total_setting'],2)." / ".number_format($product_price[0]['ct_moq'],2)."</td>";
                        echo "<td class='text-right'>".number_format($product_price[0]['charge_setting_ct'],2)."</td>";
                        echo "<td>Total biaya setting / MOQ 176</td>";
                        echo "<td class='text-center'></td>";
                    echo "</tr>";

                    //===============END NEW=================
                    echo "<tr>";
                        echo "<td class='text-center' rowspan='3'>2</td>";
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

                    echo "<tr>";
                        echo "<td class='text-center'></td>";
                        echo "<td>Product Without Setting</td>";
                        echo "<td class='text-center'></td>";
                        echo "<td class='text-right'>".number_format($cost,2)."</td>";
                        echo "<td></td>";
                        echo "<td></td>";
                    echo "</tr>";
                    $total_setting_bom = $cost + $product_price[0]['charge_setting_bom'] + $product_price[0]['biaya_total_setting'];
                    echo "<tr>";
                        echo "<td class='text-center'></td>";
                        echo "<td>Price product BOM MOQ 71</td>";
                        echo "<td class='text-center'>".number_format($cost,2)." + ".number_format($product_price[0]['charge_setting_bom'],2)." + ".number_format($product_price[0]['biaya_total_setting'],2)."</td>";
                        echo "<td class='text-right'>".number_format($total_setting_bom,2)."</td>";
                        echo "<td>Product Without Setting + Charge Setting MOQ 71 BOM + Total biaya setting</td>";
                        echo "<td></td>";
                    echo "</tr>";
                    $total_setting_ct = $cost + $product_price[0]['charge_setting_ct'] + $product_price[0]['biaya_total_setting'];
                    echo "<tr>";
                        echo "<td class='text-center'></td>";
                        echo "<td>Price product with setting MOQ 176</td>";
                        echo "<td class='text-center'>".number_format($cost,2)." + ".number_format($product_price[0]['charge_setting_ct'],2)." + ".number_format($product_price[0]['biaya_total_setting'],2)."</td>";
                        echo "<td class='text-right'>".number_format($total_setting_ct,2)."</td>";
                        echo "<td>Product Without Setting + Charge Setting MOQ 176 CT + Total biaya setting</td>";
                        echo "<td></td>";
                    echo "</tr>";

                    $cost = ($total_setting_bom < $total_setting_ct)?$total_setting_bom:$total_setting_ct;

                    echo "<tr>";
                        echo "<td class='text-center'></td>";
                        echo "<td>Selling Price</td>";
                        echo "<td class='text-center'></td>";
                        echo "<td class='text-right'>".number_format($cost,2)."</td>";
                        echo "<td></td>";
                        echo "<td></td>";
                    echo "</tr>";

                    $cost_pengajuan = ($product_price[0]['pengajuan_price_list'] > 0)?$product_price[0]['pengajuan_price_list']:$cost;
                    $kurs = ($product_price[0]['kurs'] > 0)?$product_price[0]['kurs']:0;
                    $price_idr = ($product_price[0]['price_idr'] > 0)?$product_price[0]['price_idr']:0;
                    ?>
                <tr>
                    <td></td>
                    <td colspan='2' class='text-bold'>Pengajuan Price List Costing</td>
                    <td class='text-right text-bold'>$ <?=number_format($cost_pengajuan,2);?></td>
                    <td colspan='2'></td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan='2' class='text-bold'>Kurs</td>
                    <td class='text-right text-bold'>Rp. <?=number_format($kurs,2);?></td>
                    <td colspan='2'></td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan='2' class='text-bold'>Price IDR</td>
                    <td class='text-right text-bold'>Rp. <?=number_format($price_idr,2);?></td>
                    <td colspan='2'></td>
                </tr>
                </tbody>
            </table>
            <button type="button" class="btn btn-danger" name="back" id="back">Back</button>
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

        $(document).on('keyup', '#pengajuan_price_list, #kurs', function(){
            var pengajuan_price_list    = getNum($('#pengajuan_price_list').val().split(",").join(""))
            var kurs                    = getNum($('#kurs').val().split(",").join(""))
            var price_idr               = pengajuan_price_list * kurs;
            // console.log('masuk')
            $('#price_idr').val(number_format(price_idr,2))
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
        let pengajuan_price_list = $('#pengajuan_price_list').val()
        let kurs = $('#kurs').val()
        let price_idr = $('#price_idr').val()
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
		  $.ajax({
			  type:'POST',
			  url:base_url+active_controller+'/ajukan_product_price',
			  dataType : "json",
			  data:{
                'id':id,
                'pengajuan_price_list':pengajuan_price_list,
                'kurs':kurs,
                'price_idr':price_idr,
            },
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
