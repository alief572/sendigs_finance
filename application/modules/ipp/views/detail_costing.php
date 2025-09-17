
<div class="box">
	<div class="box-body">
        
        <form id="data-form" method="post">
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
                                // echo "<td class='text-center'><span class='text-primary btncursor' id='btnShowMaterial' data-bom='".$no_bom."' >Detail</span></td>";
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
                            }
                            if($value['code'] == '3'){
                                $rate 	    = number_format($product_price[0]['cost_persen_indirect'],2)." %";
                                $man_power 	= $product_price[0]['cost_indirect'];
                            }
			               
                            echo "<tr>";
                                echo "<td>".$value['element_costing']."</td>";
                                echo "<td class='text-center'>".$rate."</td>";
                                echo "<td class='text-right'>".number_format($man_power,2)."</td>";
                                echo "<td>".$value['keterangan']."</td>";
                                echo "<td></td>";
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
                                    $rate 	    = number_format($product_price[0]['rate_cycletime'],2).' x '.number_format($product_price[0]['rate_depresiasi'],2);
                                    $cost_machine	= $product_price[0]['cost_machine'];
                                }
                                if($value['code'] == '5'){
                                    $rate 	    = number_format($product_price[0]['rate_cycletime'],2).' x '.number_format($product_price[0]['rate_mould'],2);
                                    $cost_machine 	= $product_price[0]['cost_mould'];
                                }
                                if($value['code'] == '6'){
                                    $rate 	    = number_format($product_price[0]['cost_persen_consumable'],2)." %";
                                    $cost_machine 	= $product_price[0]['cost_consumable'];
                                }
                                echo "<td>".$value['element_costing']."</td>";
                                echo "<td class='text-center'>".$rate."</td>";
                                echo "<td class='text-right'>".number_format($cost_machine,2)."</td>";
                                echo "<td>".$value['keterangan']."</td>";
                                echo "<td></td>";
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
        </form>

        <button type="button" class="btn btn-danger" style='float:right; margin-left:5px;' name="back" id="back"><i class="fa fa-reply"></i> Back</button>
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
        $('.autoNumeric').autoNumeric()
        $('#rate_1,#rate_2,#rate_4,#rate_5,#rate_8,#rate_15,#rate_16,#rate_19,#coa_14,#coa_15,#coa_16,#coa_17,#coa_18,#coa_19').prop('readonly', true);

        $(document).on('click', '#btnShowMaterial', function(){
            var no_bom = $(this).data('bom');
            // alert(id);
            $("#head_title").html("<i class='fa fa-list-alt'></i><b>Detail Bill Of Material</b>");
            $.ajax({
                type:'POST',
                url: base_url+active_controller+'detail_material/'+no_bom,
                data:{'no_bom':no_bom},
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

</script>
