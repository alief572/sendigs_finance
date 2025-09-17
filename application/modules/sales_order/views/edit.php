<?php
$q_header_test = $this->db->query("SELECT * FROM cycletime_detail_header WHERE id_time='".$header[0]->id_time."'")->result_array();
 ?>
<div class="box box-primary">
   <div class="box-body"><br>
   <form id="data-form" method="post">
     <input type="hidden" name="id_time" value="<?=$header[0]->id_time;?>">
     <input type="hidden" name="produk" value="<?=$header[0]->id_product;?>">
     <div class="form-group row">
       <div class="col-md-2">
         <label for="customer">Produk Name</label>
       </div>
       <div class="col-md-4">

         <select id="produk" name="produk" class="form-control input-md chosen-select" disabled>
           <?php foreach ($results['material'] as $material){
             $selected = ($material->id_category2 == $header[0]->id_product)?'selected':'';
           ?>
           <option value="<?= $material->id_category2;?>" <?=$selected;?>><?= strtoupper(strtolower($material->nama))?></option>
           <?php } ?>
         </select>
       </div>
     </div>

     <br>
     <div class='box box-info'>
       <div class='box-header'>
         <h3 class='box-title'>Detail Product</h3>
         <div class='box-tool pull-right'>
           <!--<button type='button' data-id='frp_".$a."' class='btn btn-md btn-info panelSH'>SHOW</button>-->
         </div>
       </div>
       <div class='box-body hide_header'>
         <table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
           <thead>
             <tr class='bg-blue'>
               <th class='text-center' style='width: 4%;'>#</th>
               <th class='text-center' style='width: 30%;'>Cost Center</th>
               <th class='text-center'>Machine</th>
               <th class='text-center'>Mould/Tools</th>
               <th class='text-center'>Information</th>
               <th class='text-center' style='width: 4%;'>#</th>
             </tr>
           </thead>
           <tbody>
             <?php

 						  $id = 0;
 						  foreach($q_header_test AS $val2 => $val2x){
                $id++;
                echo "<tr class='header_".$id."'>";
                    echo "<td align='center'>".$id."</td>";
                    echo "<td align='left'>";
                    echo "<select name='Detail[".$id."][costcenter]' class='chosen-select form-control input-sm inline-blockd costcenter'>";
                    foreach($costcenter AS $val => $valx){
                      $sel = ($valx['id_costcenter'] == $val2x['costcenter'])?'selected':'';
                    echo "<option value='".$valx['id_costcenter']."' ".$sel.">".strtoupper($valx['nama_costcenter'])."</option>";
                    }
                    echo 		"</select>";
                    echo "</td>";
                    echo "<td align='left'>";
                    echo "<select name='Detail[".$id."][machine]' class='chosen-select form-control input-sm inline-blockd'>";
                    echo "<option value='0'>NONE MACHINE</option>";
                    foreach($mesin AS $val => $valx){
                      $sel = ($valx['kd_asset'] == $val2x['machine'])?'selected':'';
                    echo "<option value='".$valx['kd_asset']."' ".$sel.">".strtoupper($valx['nm_asset'])."</option>";
                    }

                    echo 		"</select>";

                    echo "</td>";
                    echo "<td align='left'>";
                    echo "<select name='Detail[".$id."][mould]' class='chosen-select form-control input-sm inline-blockd'>";
                    echo "<option value='0'>NONE MOULD/TOOLS</option>";
                    foreach($mould AS $val => $valx){
                      $sel = ($valx['kd_asset'] == $val2x['mould'])?'selected':'';
                    echo "<option value='".$valx['kd_asset']."' ".$sel.">".strtoupper($valx['nm_asset'])."</option>";
                    }

                    echo 		"</select>";
                    echo "</td>";
                    echo "<td align='left'></td>";
                    echo "<td align='center'>";
                    echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
                    echo "</td>";
                echo "</tr>";
   							$q_dheader_test = $this->db->query("SELECT * FROM cycletime_detail_detail WHERE id_costcenter='".$val2x['id_costcenter']."'")->result_array();
                $process	= $this->db->query("SELECT * FROM ms_process ORDER BY nm_process ASC ")->result_array();
                $no = 0;
                foreach($q_dheader_test AS $val2D => $val2Dx){ $no++;
                  echo "<tr class='header_".$id."'>";
            				echo "<td align='center'></td>";
            				echo "<td align='left' style='vertical-align:middle; padding-left: 30px;'>";
                    echo "<select name='Detail[".$id."][detail][".$no."][process]' class='chosen-select form-control input-sm inline-blockd process'>";
                    foreach($process AS $val => $valx){
                      $sel = ($valx['id'] == $val2Dx['id_process'])?'selected':'';
                      echo "<option value='".$valx['id']."' ".$sel.">".strtoupper($valx['nm_process'])."</option>";
                    }
                    echo 		"</select>";
            				echo "</td>";
                    echo "<td align='left'>";
                    echo "<input type='text' name='Detail[".$id."][detail][".$no."][cycletime]' value='".$val2Dx['cycletime']."' class='form-control input-md maskM' placeholder='Cycletime (Minutes)'  data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>";
            				echo "</td>";
                    echo "<td align='left'>";
                    echo "<input type='text' name='Detail[".$id."][detail][".$no."][qty_mp]' value='".$val2Dx['qty_mp']."' class='form-control input-md maskM' placeholder='Qty Man Power'  data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>";
            				echo "</td>";
                    echo "<td align='left'>";
                    echo "<input type='text' name='Detail[".$id."][detail][".$no."][note]' value='".$val2Dx['note']."' class='form-control input-md' placeholder='Information'  data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>";
                    echo "</td>";
            				echo "<td align='center'>";
            				echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delSubPart' title='Delete Part'><i class='fa fa-close'></i></button>";
            				echo "</td>";
            			echo "</tr>";
   							}
                echo "<tr id='add_".$id."_".$no."' class='header_".$id."'>";
                  echo "<td align='center'></td>";
                  echo "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-primary addSubPart' title='Add Process'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Process</button></td>";
                  echo "<td align='center'></td>";
                  echo "<td align='center'></td>";
                  echo "<td align='center'></td>";
                  echo "<td align='center'></td>";
                echo "</tr>";
 						  }
 						  ?>
             <tr id='add_<?=$id;?>'>
               <td align='center'></td>
               <td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-warning addPart' title='Add Costcenter'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Costcenter</button></td>
               <td align='center'></td>
               <td align='center'></td>
               <td align='center'></td>
               <td align='center'></td>
             </tr>
           </tbody>
         </table>
         <br>
         <button type="button" class="btn btn-danger" style='float:right; margin-left:5px;' name="back" id="back"><i class="fa fa-reply"></i> Back</button>
         <button type="button" class="btn btn-primary" style='float:right;' name="save" id="save"><i class="fa fa-save"></i> Save</button>
       </div>
     </div>
   </form>
 </div>
</div>


<script src="<?= base_url('assets/js/jquery.maskMoney.js')?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>

<script type="text/javascript">
 //$('#input-kendaraan').hide();
 var base_url			= '<?php echo base_url(); ?>';
 var active_controller	= '<?php echo($this->uri->segment(1)); ?>';

 $(document).on('click', '#back', function(){
     window.location.href = base_url + active_controller;
 });

 $(document).ready(function(){
   $('.chosen-select').select2();
   $('.maskM').maskMoney();
   //add part
   $(document).on('click', '.addPart', function(){
     // loading_spinner();
     var get_id 		= $(this).parent().parent().attr('id');
     // console.log(get_id);
     var split_id	= get_id.split('_');
     var id 		= parseInt(split_id[1])+1;
     var id_bef 	= split_id[1];

     $.ajax({
       url: base_url+'index.php/'+active_controller+'/get_add/'+id,
       cache: false,
       type: "POST",
       dataType: "json",
       success: function(data){
         $("#add_"+id_bef).before(data.header);
         $("#add_"+id_bef).remove();
         $('.chosen-select').select2({width: '100%'});
         $('.maskM').maskMoney();
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
     var get_id 		= $(this).parent().parent().attr('id');
     // console.log(get_id);
     var split_id	= get_id.split('_');
     var id 			= split_id[1];
     var id2 		= parseInt(split_id[2])+1;
     var id_bef 		= split_id[2];

     $.ajax({
       url: base_url+'index.php/'+active_controller+'/get_add_sub/'+id+'/'+id2,
       cache: false,
       type: "POST",
       dataType: "json",
       success: function(data){
         $("#add_"+id+"_"+id_bef).before(data.header);
         $("#add_"+id+"_"+id_bef).remove();
         $('.chosen-select').select2({width: '100%'});
         $('.maskM').maskMoney();
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

   //delete part
   $(document).on('click', '.delPart', function(){
     var get_id 		= $(this).parent().parent().attr('class');
     $("."+get_id).remove();
   });

   $(document).on('click', '.delSubPart', function(){
     var get_id 		= $(this).parent().parent('tr').html();
     $(this).parent().parent('tr').remove();
   });





   // $('#save').click(function(e){
  $(document).on('click', '#save', function(e){
     e.preventDefault();
     // alert('masuk'); return false;
     var produk		= $('#produk').val();
     var costcenter	= $('.costcenter').val();
     var process		= $('.process').val();

     if(produk == '0' ){
       swal({
         title	: "Error Message!",
         text	: 'Product name empty, select first ...',
         type	: "warning"
       });

       $('#save').prop('disabled',false);
       return false;
     }
     if(costcenter == '0' ){
       swal({
         title	: "Error Message!",
         text	: 'Costcenter empty, select first ...',
         type	: "warning"
       });

       $('#save').prop('disabled',false);
       return false;
     }
     if(process == '0' ){
       swal({
         title	: "Error Message!",
         text	: 'Process name empty, select first ...',
         type	: "warning"
       });

       $('#save').prop('disabled',false);
       return false;
     }
     // return false;
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
           var baseurl=siteurl+'cycletime/edit_cycletime';
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
                 window.location.href = base_url + active_controller;
               }else{

                 if(data.status == 2){
                   swal({
                     title	: "Save Failed!",
                     text	: data.pesan,
                     type	: "warning",
                     timer	: 7000,
                     showCancelButton	: false,
                     showConfirmButton	: false,
                     allowOutsideClick	: false
                   });
                 }else{
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

</script>
