<?php
$del = (!empty($product))?'disabled':'';
$tanda = (!empty($no_bom))?'Update':'Insert';
$list = (!empty($no_bom))?get_product():get_product_bom();
 ?>
<div class="box box-primary">
   <div class="box-body"><br>
   <form id="data-form" method="post">
     <input type="hidden" name="no_bom_hth" value="<?=$no_bom;?>">
     <input type="hidden" name="produk" value="<?=$product;?>">
     <input type="hidden" name="tanda" value="<?=$tanda;?>">
     <div class="form-group row">
       <div class="col-md-2">
         <label for="customer">Product Name</label>
       </div>
       <div class="col-md-4">
         <select id="produk" name="produk" class="form-control input-md chosen-select" <?=$del;?>>
           <option value="0">Select An Option</option>
           <?php foreach ($list as $val => $valx){
             $sel = ($product == $valx['id_category2'])?'selected':'';
           ?>
           <option value="<?= $valx['id_category2'];?>" <?=$sel;?>><?= strtoupper(strtolower($valx['nama']))?></option>
           <?php } ?>
         </select>
       </div>
     </div>

     <br>
     <div class='box box-info'>
       <div class='form-group row'>
         <div class='col-sm-6'>
           <div class='box-header'>
             <h3 class='box-title'>F-Tackle</h3>
             <div class='box-tool pull-right'>
             </div>
           </div>
           <div class='box-body hide_header'>
             <table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
               <thead>
                 <tr class='bg-blue'>
                   <th class='text-center' style='width: 4%;'>#</th>
                   <th class='text-center' style='width: 44%;'>Group Process</th>
                   <th class='text-center' style='width: 18%;'>Qty</th>
                   <th class='text-center' style='width: 12%;'>Price</th>
                   <th class='text-center' style='width: 18%;'>Total</th>
                   <th class='text-center' style='width: 4%;'>#</th>
                 </tr>
               </thead>
               <tbody>
                 <?php
                 $id = 0;
                 foreach($detail_header AS $val => $valx){ $id++;
                   echo "<tr class='header_".$id."'>";
             				echo "<td align='center' style='vertical-align:middle;'>".$id."</td>";
             				echo "<td align='left' style='vertical-align:middle;'>";
                     echo "<select name='Detail[".$id."][group_material]' data-no='".$id."' id='group_".$id."' class='chosen_select form-control input-sm inline-blockd group_material'>";
                     echo "<option value='0'>Select Group Material</option>";
                     foreach($group_material AS $val2 => $valx2){
                       $selc = ($valx['id_group_material'] == $valx2['id_group_material'])?'selected':'';
                       echo "<option value='".$valx2['id_group_material']."' ".$selc.">".strtoupper($valx2['group_material'])."</option>";
                     }
                     echo "</select>";
             				echo "</td>";
                     echo "<td align='left'>";
             				echo "</td>";
                     echo "<td align='left'>";
             				echo "</td>";
             				echo "<td align='left'></td>";
             				echo "<td align='center' style='vertical-align:middle;'>";
             				echo "<button type='button' class='btn btn-sm btn-danger delPart' data-no='".$id."' title='Delete Part'><i class='fa fa-close'></i></button>";
             				echo "</td>";
             			echo "</tr>";

                  $detail_detail = $this->db->get_where('bom_hth_detail_detail',array('kode_bom_hth' => $no_bom, 'kode_bom_hth_detail' => $valx['kode_bom_hth_detail'], 'company' => 'f-tackle'))->result_array();
                  $no = 0;
                  foreach($detail_detail AS $val3 => $valx3){ $no++;
                    $material	= $this->db->query("SELECT
                                                    b.code_material,
                                                    b.weight,
                                                    c.nm_material
                                                  FROM
                                                    bom_header a
                                                    LEFT JOIN bom_detail b ON a.no_bom=b.no_bom
                                                    LEFT JOIN ms_material c ON b.code_material=c.code_material
                                                  WHERE
                                                    a.id_product='".$product."'
                                                  ")->result_array();
                		  echo "<tr class='header_".$id."'>";
                				echo "<td align='center'></td>";
                				echo "<td align='left' style='vertical-align:middle; padding-left: 30px;'>";
                        // echo "<select name='Detail[".$id."][detail][".$no."][material]' class='chosen_select form-control input-sm inline-blockd material'>";
                        // echo "<option value='0'>Select Material Name</option>";
                        // foreach($material AS $val4 => $valx4){
                        //   $selc2 = ($valx3['id_material'] == $valx4['code_material'])?'selected':'';
                        //   echo "<option value='".$valx4['code_material']."' ".$selc2.">".strtoupper($valx4['nm_material'])."</option>";
                        // }
                        // echo 		"</select>";
                        echo "<input type='text' name='Detail[".$id."][detail][".$no."][material]' id='material_".$id."_".$no."' value='".$valx3['id_material']."' class='form-control text-left input-md' placeholder='Material Name'>";
                				echo "</td>";
                        echo "<td align='left' style='vertical-align:middle;'>";
                        echo "<input type='text' name='Detail[".$id."][detail][".$no."][qty]' id='qty_".$id."_".$no."' value='".$valx3['qty']."' class='form-control text-right input-md maskM qty_".$id." getTotal' placeholder='Qty'>";
                				echo "</td>";
                        echo "<td align='left' style='vertical-align:middle;'>";
                        echo "<input type='text' name='Detail[".$id."][detail][".$no."][price]' id='price_".$id."_".$no."' value='".$valx3['price']."' class='form-control text-right  input-md maskM2 getTotal' placeholder='Price'>";
                				echo "</td>";
                        echo "<td align='left' style='vertical-align:middle;'>";
                        echo "<input type='text' name='Detail[".$id."][detail][".$no."][total]' id='total_".$id."_".$no."' value='".$valx3['total']."' class='form-control text-right  input-md total_".$id."' placeholder='Total' readonly>";
                        echo "</td>";
                				echo "<td align='center' style='vertical-align:middle;'>";
                				echo "<button type='button' class='btn btn-sm btn-danger delSubPart' data-no='".$id."' title='Delete Part'><i class='fa fa-close'></i></button>";
                				echo "</td>";
                			echo "</tr>";
                  }
                  echo "<tr id='add_".$id."_".$no."' class='header_".$id."'>";
              			echo "<td align='center'></td>";
              			echo "<td align='left' style='vertical-align:middle;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-primary addSubPart' data-no='".$id."' title='Add Material'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Material</button></td>";
                    echo "<td align='left' style='vertical-align:middle;'>";
                    echo "<input type='text' name='Detail[".$id."][footer][".$no."][total_qty]' class='form-control text-right  input-md total_qty_".$id." total_qty' placeholder='Total Qty' readonly>";
                    echo "</td>";
              			echo "<td align='center'></td>";
                    echo "<td align='left' style='vertical-align:middle;'>";
                    echo "<input type='text' name='Detail[".$id."][footer][".$no."][total_total]' class='form-control text-right  input-md total_total_".$id." total_total' placeholder='Sub Total' readonly>";
                    echo "</td>";
              			echo "<td align='center'></td>";
              		echo "</tr>";

                 }
                 ?>
                 <tr id='add_<?=$id;?>'>
                   <td align='center'></td>
                   <td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-warning addPart' title='Add Group'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Group Process</button></td>
                   <?php if($id > 0){
                     echo "<td align='left' style='vertical-align:middle;'>";
                     echo "<input type='text' name='total_qty' id='sub_qty' class='form-control text-right  input-md' placeholder='Total Qty' readonly>";
                     echo "</td>";
               			echo "<td align='center'></td>";
                     echo "<td align='left' style='vertical-align:middle;'>";
                     echo "<input type='text' name='total_total' id='sub_total' class='form-control text-right  input-md' placeholder='Sub Total' readonly>";
                     echo "</td>";
                   } ?>
                   <?php if($id < 1){?>
                   <td align='center'></td>
                   <td align='center'></td>
                   <td align='center'></td>
                 <?php } ?>
                   <td align='center'></td>
                 </tr>
               </tbody>
             </table>
           </div>
         </div>

         <div class='col-sm-6'>
           <div class='box-header'>
             <h3 class='box-title'>ORIGA</h3>
             <div class='box-tool pull-right'>
             </div>
           </div>
           <div class='box-body hide_header'>
             <table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
               <thead>
                 <tr class='bg-blue'>
                   <th class='text-center' style='width: 4%;'>#</th>
                   <th class='text-center' style='width: 44%;'>Group Process</th>
                   <th class='text-center' style='width: 18%;'>Qty</th>
                   <th class='text-center' style='width: 12%;'>Price</th>
                   <th class='text-center' style='width: 18%;'>Total</th>
                   <th class='text-center' style='width: 4%;'>#</th>
                 </tr>
               </thead>
               <tbody>
                 <?php
                 $id = 0;
                 foreach($detail_header2 AS $val => $valx){ $id++;
                   echo "<tr class='header2_".$id."'>";
             				echo "<td align='center' style='vertical-align:middle;'>".$id."</td>";
             				echo "<td align='left' style='vertical-align:middle;'>";
                     echo "<select name='Detail2[".$id."][group_material]' data-no='".$id."' id='group2_".$id."' class='chosen_select form-control input-sm inline-blockd group_material'>";
                     echo "<option value='0'>Select Group Material</option>";
                     foreach($group_material AS $val2 => $valx2){
                       $selc = ($valx['id_group_material'] == $valx2['id_group_material'])?'selected':'';
                       echo "<option value='".$valx2['id_group_material']."' ".$selc.">".strtoupper($valx2['group_material'])."</option>";
                     }
                     echo "</select>";
             				echo "</td>";
                     echo "<td align='left'>";
             				echo "</td>";
                     echo "<td align='left'>";
             				echo "</td>";
             				echo "<td align='left'></td>";
             				echo "<td align='center' style='vertical-align:middle;'>";
             				echo "<button type='button' class='btn btn-sm btn-danger delPart' data-no='".$id."' title='Delete Part'><i class='fa fa-close'></i></button>";
             				echo "</td>";
             			echo "</tr>";

                  $detail_detail = $this->db->get_where('bom_hth_detail_detail',array('kode_bom_hth' => $no_bom, 'kode_bom_hth_detail' => $valx['kode_bom_hth_detail'], 'company' => 'origa'))->result_array();
                  $no = 0;
                  foreach($detail_detail AS $val3 => $valx3){ $no++;
                    $weight	= $this->db->query("SELECT
                                                    b.weight
                                                  FROM
                                                    bom_header a
                                                    LEFT JOIN bom_detail b ON a.no_bom=b.no_bom
                                                  WHERE
                                                    a.id_product='".$product."'
                                                    AND b.code_material='".$valx3['id_material']."'
                                                  LIMIT 1
                                                  ")->result();
                    $get_price	= $this->db->query("SELECT
                                                b.rate
                                              FROM
                                                price_ref b
                                              WHERE
                                                b.code='".$valx3['id_material']."'
                                                AND category = 'material'
                                              LIMIT 1
                                              ")->result();
                    $qty = (!empty($weight[0]->weight))?$weight[0]->weight:0;
                    $price = (!empty($get_price[0]->rate))?$get_price[0]->rate:0;

                    echo "<tr class='header2_".$id."'>";
              				echo "<td align='center'></td>";
              				echo "<td align='left' style='vertical-align:middle; padding-left: 30px;'>";
                      echo "<select name='Detail2[".$id."][detail][".$no."][material]' data-no1='".$id."' data-no2='".$no."' class='chosen_select form-control input-sm inline-blockd material process2'>";
                      echo "<option value='0'>Select Material Name</option>";
                      foreach($material AS $val4 => $valx4){
                        $selc2 = ($valx3['id_material'] == $valx4['code_material'])?'selected':'';
                        echo "<option value='".$valx4['code_material']."' ".$selc2.">".strtoupper($valx4['nm_material'])."</option>";
                      }
                      echo 		"</select>";
              				echo "</td>";
                      echo "<td align='left' style='vertical-align:middle;'>";
                      echo "<input type='text' name='Detail2[".$id."][detail][".$no."][qty]' id='qty2_".$id."_".$no."' value='".number_format($qty,5)."' class='form-control text-right input-md qty2_".$id."' placeholder='Qty' readonly>";
              				echo "</td>";
                      echo "<td align='left' style='vertical-align:middle;'>";
                      echo "<input type='text' name='Detail2[".$id."][detail][".$no."][price]' id='price2_".$id."_".$no."' value='".number_format($price,2)."' class='form-control text-right  input-md' placeholder='Price' readonly>";
              				echo "</td>";
                      echo "<td align='left' style='vertical-align:middle;'>";
                      echo "<input type='text' name='Detail2[".$id."][detail][".$no."][total]' id='total2_".$id."_".$no."' value='".number_format($qty * $price,5)."' class='form-control text-right  input-md total2_".$id."' placeholder='Total' readonly>";
                      echo "</td>";
              				echo "<td align='center' style='vertical-align:middle;'>";
              				echo "<button type='button' class='btn btn-sm btn-danger delSubPart' data-no='".$id."' title='Delete Part'><i class='fa fa-close'></i></button>";
              				echo "</td>";
              			echo "</tr>";
                  }
                  echo "<tr id='add2_".$id."_".$no."' class='header2_".$id."'>";
              			echo "<td align='center'></td>";
              			echo "<td align='left' style='vertical-align:middle;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-primary addSubPart2' data-no='".$id."' title='Add Material'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Material</button></td>";
                    echo "<td align='left' style='vertical-align:middle;'>";
                    echo "<input type='text' name='Detail2[".$id."][footer][".$no."][total_qty]' class='form-control text-right  input-md total_qty2_".$id." total_qty2' placeholder='Total Qty' readonly>";
                    echo "</td>";
              			echo "<td align='center'></td>";
                    echo "<td align='left' style='vertical-align:middle;'>";
                    echo "<input type='text' name='Detail2[".$id."][footer][".$no."][total_total]' class='form-control text-right  input-md total_total2_".$id." total_total2' placeholder='Sub Total' readonly>";
                    echo "</td>";
              			echo "<td align='center'></td>";
              		echo "</tr>";
                 }
                 ?>
                 <tr id='add2_<?=$id;?>'>
                   <td align='center'></td>
                   <td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-warning addPart2' title='Add Group'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Group Process</button></td>
                   <?php if($id > 0){
                     echo "<td align='left' style='vertical-align:middle;'>";
                     echo "<input type='text' name='total_qty2' id='sub_qty2' class='form-control text-right  input-md' placeholder='Total Qty' readonly>";
                     echo "</td>";
               			echo "<td align='center'></td>";
                     echo "<td align='left' style='vertical-align:middle;'>";
                     echo "<input type='text' name='total_total2' id='sub_total2' class='form-control text-right  input-md' placeholder='Sub Total' readonly>";
                     echo "</td>";
                   } ?>
                   <?php if($id < 1){?>
                   <td align='center'></td>
                   <td align='center'></td>
                   <td align='center'></td>
                 <?php } ?>
                   <td align='center'></td>
                 </tr>
               </tbody>
             </table>
           </div>
         </div>
       </div>
     </div>
     <button type="button" class="btn btn-danger" style='float:right; margin-left:5px;' name="back" id="back"><i class="fa fa-reply"></i> Back</button>
     <button type="submit" class="btn btn-primary" style='float:right;' name="save" id="save"><i class="fa fa-save"></i> Save</button>
   </form>
 </div>
</div>


<script src="<?= base_url('assets/js/jquery.maskMoney.js')?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>

<script type="text/javascript">
 //$('#input-kendaraan').hide();
 var base_url			= '<?php echo base_url(); ?>';
 var active_controller	= '<?php echo($this->uri->segment(1)); ?>';

 $(document).ready(function(){
   $('.chosen-select').select2();
   $('.chosen_select').select2();
   $('.maskM').maskMoney({precision: 5});
   $('.maskM2').maskMoney({precision: 2});
   var a;
   for(a=0; a <= 50; a++){
     get_summary(a);
     get_summary2(a);
   }


   $(document).on('change', '.group_material', function(){
     var nomor 		= $(this).data('no');
     var value 		= $(this).val();
     // alert(nomor+value);

     $.ajax({
       url: base_url+active_controller+'/get_add_bom_selected/'+nomor+'/'+value,
       cache: false,
       type: "POST",
       dataType: "json",
       success: function(data){
        $("#group_"+nomor).html(data.option).trigger("chosen:updated");
        $("#group2_"+nomor).html(data.option2).trigger("chosen:updated");
         swal.close();
       },
       error: function() {
         swal({
           title				: "Error Message !",
           text				: 'Connection Time Out. Please try again..',
           type				: "warning",
           timer				: 3000,
           showCancelButton	: false,
           showConfirmButton	: false,
           allowOutsideClick	: false
         });
       }
     });
   });

   //add part
   $(document).on('click', '.addPart', function(){
     // loading_spinner();
     var get_id 		= $(this).parent().parent().attr('id');
     // console.log(get_id);
     var split_id	= get_id.split('_');
     var id 		= parseInt(split_id[1])+1;
     var id_bef 	= split_id[1];

     $.ajax({
       url: base_url+active_controller+'/get_add_bom/'+id,
       cache: false,
       type: "POST",
       dataType: "json",
       success: function(data){
         $("#add_"+id_bef).before(data.header);
         $("#add_"+id_bef).remove();
         $('.chosen_select').select2({width: '100%'});
         $('.maskM').maskMoney({precision: 5});
         $('.maskM2').maskMoney({precision: 2});
         swal.close();
       },
       error: function() {
         swal({
           title				: "Error Message !",
           text				: 'Connection Time Out. Please try again..',
           type				: "warning",
           timer				: 3000,
           showCancelButton	: false,
           showConfirmButton	: false,
           allowOutsideClick	: false
         });
       }
     });


     //part2
     $.ajax({
       url: base_url+active_controller+'/get_add_bom2/'+id,
       cache: false,
       type: "POST",
       dataType: "json",
       success: function(data){
         $("#add2_"+id_bef).before(data.header);
         $("#add2_"+id_bef).remove();
         $('.chosen_select').select2({width: '100%'});
         $('.maskM').maskMoney({precision: 5});
         $('.maskM2').maskMoney({precision: 2});
         swal.close();
       },
       error: function() {
         swal({
           title				: "Error Message !",
           text				: 'Connection Time Out. Please try again..',
           type				: "warning",
           timer				: 3000,
           showCancelButton	: false,
           showConfirmButton	: false,
           allowOutsideClick	: false
         });
       }
     });

   });
   //add part
   $(document).on('click', '.addSubPart', function(){
     // loading_spinner();
     var product = $('#produk').val();
     if(product == '0'){
 			swal({
 				title	: "Error Message!",
 				text	: 'Product Name belum dipilih ...',
 				type	: "warning"
 			});
 			return false;
 		}

     var get_id 		= $(this).parent().parent().attr('id');
     // console.log(get_id);
     var split_id	= get_id.split('_');
     var id 			= split_id[1];
     var id2 		= parseInt(split_id[2])+1;
     var id_bef 		= split_id[2];

     $.ajax({
       url: base_url+active_controller+'/get_add_sub_bom/'+id+'/'+id2+'/'+product,
       cache: false,
       type: "POST",
       dataType: "json",
       success: function(data){
         $("#add_"+id+"_"+id_bef).before(data.header);
         $("#add_"+id+"_"+id_bef).remove();
         $('.chosen_select').select2({width: '100%'});
         $('.maskM').maskMoney({precision: 5});
         $('.maskM2').maskMoney({precision: 2});
         swal.close();
       },
       error: function() {
         swal({
           title				: "Error Message !",
           text				: 'Connection Time Out. Please try again..',
           type				: "warning",
           timer				: 3000,
           showCancelButton	: false,
           showConfirmButton	: false,
           allowOutsideClick	: false
         });
       }
     });
   });

   $(document).on('click', '.addPart2', function(){
     // loading_spinner();
     var get_id 		= $(this).parent().parent().attr('id');
     // console.log(get_id);
     var split_id	= get_id.split('_');
     var id 		= parseInt(split_id[1])+1;
     var id_bef 	= split_id[1];

     $.ajax({
       url: base_url+active_controller+'/get_add_bom2/'+id,
       cache: false,
       type: "POST",
       dataType: "json",
       success: function(data){
         $("#add2_"+id_bef).before(data.header);
         $("#add2_"+id_bef).remove();
         $('.chosen_select').select2({width: '100%'});
         $('.maskM').maskMoney({precision: 5});
         $('.maskM2').maskMoney({precision: 2});
         swal.close();
       },
       error: function() {
         swal({
           title				: "Error Message !",
           text				: 'Connection Time Out. Please try again..',
           type				: "warning",
           timer				: 3000,
           showCancelButton	: false,
           showConfirmButton	: false,
           allowOutsideClick	: false
         });
       }
     });

     $.ajax({
       url: base_url+active_controller+'/get_add_bom/'+id,
       cache: false,
       type: "POST",
       dataType: "json",
       success: function(data){
         $("#add_"+id_bef).before(data.header);
         $("#add_"+id_bef).remove();
         $('.chosen_select').select2({width: '100%'});
         $('.maskM').maskMoney({precision: 5});
         $('.maskM2').maskMoney({precision: 2});
         swal.close();
       },
       error: function() {
         swal({
           title				: "Error Message !",
           text				: 'Connection Time Out. Please try again..',
           type				: "warning",
           timer				: 3000,
           showCancelButton	: false,
           showConfirmButton	: false,
           allowOutsideClick	: false
         });
       }
     });

   });
   //add part
   $(document).on('click', '.addSubPart2', function(){
     // loading_spinner();
     var product = $('#produk').val();
     if(product == '0'){
 			swal({
 				title	: "Error Message!",
 				text	: 'Product Name belum dipilih ...',
 				type	: "warning"
 			});
 			return false;
 		}

     var get_id 		= $(this).parent().parent().attr('id');
     // console.log(get_id);
     var split_id	= get_id.split('_');
     var id 			= split_id[1];
     var id2 		= parseInt(split_id[2])+1;
     var id_bef 		= split_id[2];

     $.ajax({
       url: base_url+active_controller+'/get_add_sub_bom2/'+id+'/'+id2+'/'+product,
       cache: false,
       type: "POST",
       dataType: "json",
       success: function(data){
         $("#add2_"+id+"_"+id_bef).before(data.header);
         $("#add2_"+id+"_"+id_bef).remove();
         $('.chosen_select').select2({width: '100%'});
         $('.maskM').maskMoney({precision: 5});
         $('.maskM2').maskMoney({precision: 2});
         swal.close();
       },
       error: function() {
         swal({
           title				: "Error Message !",
           text				: 'Connection Time Out. Please try again..',
           type				: "warning",
           timer				: 3000,
           showCancelButton	: false,
           showConfirmButton	: false,
           allowOutsideClick	: false
         });
       }
     });
   });

   $(document).on('change', '.process2', function(){
     // loading_spinner();
     var product = $("#produk").val();
     var material = $(this).val();
     var nomor1 = $(this).data('no1');
     var nomor2 = $(this).data('no2');

     $.ajax({
       url: base_url+active_controller+'/get_data_berat_price/'+product+'/'+material,
       cache: false,
       type: "POST",
       dataType: "json",
       success: function(data){
         $("#qty2_"+nomor1+"_"+nomor2).val(data.qty);
         $("#price2_"+nomor1+"_"+nomor2).val(data.price);
         $("#total2_"+nomor1+"_"+nomor2).val(data.total);

         get_summary2(nomor1);
       },
       error: function() {
         swal({
           title				: "Error Message !",
           text				: 'Connection Time Out. Please try again..',
           type				: "warning",
           timer				: 3000,
           showCancelButton	: false,
           showConfirmButton	: false,
           allowOutsideClick	: false
         });
       }
     });
   });

   $(document).on('keyup', '.getTotal', function(){
     // console.log("HHA");
     var get_id 		= $(this).attr('id');
     var split_id	  = get_id.split('_');
     var id 		    = split_id[1];
     var id2 	      = split_id[2];

     var qty   = getNum($("#qty_"+id+"_"+id2).val().split(",").join(""));
     var price = getNum($("#price_"+id+"_"+id2).val().split(",").join(""));
     var total = qty * price;

     $("#total_"+id+"_"+id2).val(total.toFixed(5));

     get_summary(id);
   });
   //delete part
   $(document).on('click', '.delPart', function(){
     var no = $(this).data('no');
     // var get_id 		= $(this).parent().parent().attr('class');
     $(".header_"+no).remove();
     $(".header2_"+no).remove();

     var nomor = $(this).data('no');
     get_summary(nomor);
     get_summary2(nomor);
   });

   $(document).on('click', '.delSubPart', function(){
     var get_id 		= $(this).parent().parent('tr').html();
     $(this).parent().parent('tr').remove();

     var nomor = $(this).data('no');
     get_summary(nomor);
     get_summary2(nomor);
   });

   //add part
   $(document).on('click', '#back', function(){
       window.location.href = base_url + active_controller +'/bom_head_to_head';
   });

  $(document).on('click', '#save', function(e){
     e.preventDefault();
     var produk		         = $('#produk').val();
     var group_material		 = $('.group_material').val();
     var material		       = $('.material').val();
     var sub_qty	= getNum($('#sub_qty').val());
     var sub_qty2	= getNum($('#sub_qty2').val());

     if(produk == '0' ){
       swal({
         title	: "Error Message!",
         text	  : 'Product name empty, select first ...',
         type	  : "warning"
       });
       $('#save').prop('disabled',false);
       return false;
     }

     if(group_material == '0' ){
       swal({
         title	: "Error Message!",
         text	  : 'Group Material empty, select first ...',
         type	  : "warning"
       });
       $('#save').prop('disabled',false);
       return false;
     }

     // if(material == '0' ){
     //   swal({
     //     title	: "Error Message!",
     //     text	  : 'Material name empty, select first ...',
     //     type	  : "warning"
     //   });
     //   $('#save').prop('disabled',false);
     //   return false;
     // }

     // if(sub_qty <= 0 ){
     //   swal({
     //     title	: "Error Message!",
     //     text	  : 'Qty F-Tackle empty, select first ...',
     //     type	  : "warning"
     //   });
     //   $('#save').prop('disabled',false);
     //   return false;
     // }
     //
     // if(sub_qty2 <= 0 ){
     //   swal({
     //     title	: "Error Message!",
     //     text	  : 'Qty ORIGA empty, select first ...',
     //     type	  : "warning"
     //   });
     //   $('#save').prop('disabled',false);
     //   return false;
     // }

     swal({
         title: "Are you sure?",
         text: "You will not be able to process again this data!",
         type: "warning",
         showCancelButton: true,
         confirmButtonClass: "btn-danger",
         confirmButtonText: "Yes, Process it!",
         cancelButtonText: "No, cancel process!",
         closeOnConfirm: true,
         closeOnCancel: false
       },
       function(isConfirm) {
         if (isConfirm) {
           var formData 	=new FormData($('#data-form')[0]);
           var baseurl=siteurl+active_controller+'/save_bom_hth';
           $.ajax({
             url			: baseurl,
             type		: "POST",
             data		: formData,
             cache		: false,
             dataType	: 'json',
             processData	: false,
             contentType	: false,
             success		: function(data){
               if(data.status == 1){
                 swal({
                     title	: "Save Success!",
                     text	: data.pesan,
                     type	: "success",
                     timer	: 7000,
                     showCancelButton	: false,
                     showConfirmButton	: false,
                     allowOutsideClick	: false
                   });
                 window.location.href = base_url + active_controller +'/bom_head_to_head';
               }
               else{
                   swal({
                     title	: "Save Failed!",
                     text	: data.pesan,
                     type	: "warning",
                     timer	: 7000,
                     showCancelButton	: false,
                     showConfirmButton	: false,
                     allowOutsideClick	: false
                   });
               }
             },
             error: function() {

               swal({
                 title				: "Error Message !",
                 text				: 'An Error Occured During Process. Please try again..',
                 type				: "warning",
                 timer				: 7000,
                 showCancelButton	: false,
                 showConfirmButton	: false,
                 allowOutsideClick	: false
               });
             }
           });
         } else {
         swal("Cancelled", "Data can be process again :)", "error");
         return false;
         }
     });
   });

});

function get_summary(a){
  var QTY = 0;
  var PRICE = 0;

  $(".qty_"+a).each(function() {
    QTY += Number($(this).val().split(",").join(""));
  });
  $(".total_"+a).each(function() {
    PRICE += Number($(this).val().split(",").join(""));
  });

  $(".total_qty_"+a).val(QTY.toFixed(5));
  $(".total_total_"+a).val(PRICE.toFixed(5));

  var SUM = 0;
  var SUM2 = 0;
  $(".total_qty").each(function() {
    SUM += Number($(this).val().split(",").join(""));
  });
  $(".total_total").each(function() {
    SUM2 += Number($(this).val().split(",").join(""));
  });

  $("#sub_qty").val(SUM.toFixed(5));
  $("#sub_total").val(SUM2.toFixed(5));
}

function get_summary2(a){
  var QTY = 0;
  var PRICE = 0;

  $(".qty2_"+a).each(function() {
    QTY += Number($(this).val().split(",").join(""));
  });
  $(".total2_"+a).each(function() {
    PRICE += Number($(this).val().split(",").join(""));
  });

  $(".total_qty2_"+a).val(QTY.toFixed(5));
  $(".total_total2_"+a).val(PRICE.toFixed(5));

  var SUM = 0;
  var SUM2 = 0;
  $(".total_qty2").each(function() {
    SUM += Number($(this).val().split(",").join(""));
  });
  $(".total_total2").each(function() {
    SUM2 += Number($(this).val().split(",").join(""));
  });

  $("#sub_qty2").val(SUM.toFixed(5));
  $("#sub_total2").val(SUM2.toFixed(5));
}

</script>
