
<form id="data-form" method="post">
 <div class='box box-info'>
   <div class='box-body'>
     <div class="callout callout-success">
     		<p>
     			<h4><i class="icon fa fa-info"></i> Info !!!</h4>
     			<span><b>(NEW) Button "GET DAYCODE" untuk mendapatkan list daycode.<span style='color:red;'></span></b></span><br>
     	</p>
     </div>
     <br>
     <div class="form-group row">
       <div class="col-md-2">
         <label for="customer">Production Date</label>
       </div>
       <div class="col-md-4">
         <input type="text" name="tanggal_produksi" id="tanggal_produksi"  class="form-control input-md datepicker" placeholder="Select Date" readonly>
      </div>
     </div>

     <table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
       <thead>
         <tr class='bg-blue'>
           <th class='text-center' style='width: 5%;'>#</th>
           <th class='text-center' style='width: 30%;'>Cost Center</th>
           <th class='text-center' style='width: 7%;'>Get</th>
           <th class='text-center' style='width: 33%;'>Daycode</th>
           <th class='text-center' style='width: 20%;'>Remarks</th>
           <th class='text-center' style='width: 5%;'>#</th>
         </tr>
       </thead>
       <tbody>
         <?php
            $id = 0;
            foreach(get_costcenter_input_produksi() AS $val => $valx){ $id++;
               echo "<tr class='header_".$id."'>";
         				echo "<td align='center'>".$id."</td>";
         				echo "<td align='left'>";
                   echo "<select name='Detail[".$id."][id_costcenter]' id='cost_".$id."'data-id='".$id."' class='chosen_select form-control input-sm inline-blockd costcenter'>";
                    echo "<option value='".$valx['id_costcenter']."'>".strtoupper($valx['nama_costcenter'])."</option>";
                   echo 		"</select>";
         				echo "</td>";
                echo "<td align='left'></td>";
                echo "<td align='left'></td>";
         				echo "<td align='left'></td>";
         				echo "<td align='center'>";
         				echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
         				echo "</td>";
         			echo "</tr>";

              //list New
              $no = 0;
              foreach($product_check AS $val2 => $valx2 ){
                $check_p = $this->db->query(" SELECT
                                              	a.costcenter AS id,
                                              	b.id_product AS id_product
                                              FROM
                                              	cycletime_detail_header a
                                              	LEFT JOIN cycletime_header b ON a.id_time = b.id_time
                                              WHERE
                                              	a.costcenter = '".$valx['id_costcenter']."'
                                              	AND b.id_product = '".$valx2['id_category2']."'
                                              GROUP BY
                                              	a.costcenter,
                                              	b.id_product")->result_array();
                if(!empty($check_p)){
                  $no++;
                  echo "<tr class='header_".$id."'>";
            				echo "<td align='center'></td>";
            				echo "<td align='left' style='vertical-align:middle; padding-left: 30px;'>";
                      echo "<select name='Detail[".$id."][detail][".$no."][id_product]' id='product_".$id."_".$no."' class='chosen_select form-control input-sm inline-blockd id_product cost_".$id."'>";
                      $get_product	= $this->db->get_where('ms_inventory_category2', array('id_category2'=>$valx2['id_category2']))->result_array();
                      foreach($get_product AS $val3 => $valx3){
                        echo "<option value='".$valx3['id_category2']."' ".$selx.">".strtoupper($valx3['nama'])."</option>";
                      }
                      echo 		"</select>";
            				echo "</td>";
                    echo "<td align='center'><button type='button' class='btn btn-sm btn-info get_daycode' data-no1='".$id."' data-no2='".$no."' data-product='".$valx2['id_category2']."' data-costcenter='".$valx['id_costcenter']."'>Get Daycode</button></td>";
                    echo "<td align='left'>";
                      echo "<select name='Detail[".$id."][detail][".$no."][code][]' id='code_".$id."_".$no."' data-product='".$valx2['id_category2']."' data-costcenter='".$valx['id_costcenter']."' class='chosen_select form-control input-sm inline-blockd code code_".$id."' multiple>";
                      echo 	"</select>";
            				echo "</td>";
                    echo "<td align='left'>";
                      echo 	"<input type='text' name='Detail[".$id."][detail][".$no."][remarks]' class='form-control input-md' placeholder='Remarks'>";
            				echo "</td>";
            				echo "<td align='center'>";
            				    echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delSubPart' title='Delete Part'><i class='fa fa-close'></i></button>";
            				echo "</td>";
            			echo "</tr>";
                }
              }

           		//add nya
           		echo "<tr id='add_".$id."_".$no."' class='header_".$id."'>";
           			echo "<td align='center'></td>";
           			echo "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' data-id='".$id."' class='btn btn-sm btn-primary addSubPart' title='Add Product'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add</button></td>";
           			echo "<td align='center'></td>";
                echo "<td align='left'></td>";
           			echo "<td align='center'></td>";
                echo "<td align='center'></td>";
           		echo "</tr>";
            }
          ?>
       </tbody>
     </table>
     <br>
     <button type="button" class="btn btn-danger" style='float:right; margin-left:5px;' name="back" id="back"><i class="fa fa-reply"></i> Back</button>
     <button type="submit" class="btn btn-primary" style='float:right;' name="save" id="save"><i class="fa fa-save"></i> Save</button>
   </div>
 </div>
</form>


<script src="<?= base_url('assets/js/jquery.maskMoney.js')?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>

<script type="text/javascript">
 var base_url			= '<?php echo base_url(); ?>';
 var active_controller	= '<?php echo($this->uri->segment(1)); ?>';

 $(document).on('click', '#back', function(){
     window.location.href = base_url + active_controller;
 });

 $(document).ready(function(){
   $('.chosen-select').select2();
   $('.chosen_select').select2();
   $(".datepicker").datepicker({
     dateFormat: "DD, d MM yy",
     changeMonth: true,
     changeYear: true,
     maxDate: 0
   });

   //add part
   $(document).on('click', '.addSubPart', function(){
     // loading_spinner();
     var get_id 		= $(this).parent().parent().attr('id');
     var id_cost = $(this).data('id');
     var split_id	= get_id.split('_');
     var id 			= split_id[1];
     var id2 		= parseInt(split_id[2])+1;
     var id_bef 		= split_id[2];

     var process		= $('#cost_'+id_cost).val();

     if(process == '0' ){
       swal({
         title	: "Error Message!",
         text	: 'Coscenter name empty, select first ...',
         type	: "warning"
       });
       return false;
     }

     $.ajax({
       url: base_url+'index.php/'+active_controller+'/get_add_sub_new/'+id+'/'+id2+'/'+process,
       cache: false,
       type: "POST",
       dataType: "json",
       success: function(data){
         $("#add_"+id+"_"+id_bef).before(data.header);
         $("#add_"+id+"_"+id_bef).remove();
         $('.chosen_select').select2({width: '100%'});
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

   $(document).on('click', '.get_daycode', function(){
     var id 			= $(this).data('no1');
     var id2 			= $(this).data('no2');

     var id_product		= $(this).data('product');
     var costcenter		= $(this).data('costcenter');

     $.ajax({
       url: base_url+active_controller+'/get_list_process2/'+id_product+'/'+costcenter,
       cache: false,
       type: "POST",
       dataType: "json",
       success: function(data){
         $("#code_"+id+"_"+id2).html(data.daycode).trigger("chosen:updated");
         $('.chosen_select').select2({width: '100%'});
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

   $(document).on('change', '.id_product', function(){
     // loading_spinner();
     var get_id 		= $(this).attr('id');
     // console.log(get_id); return false;
     var split_id	= get_id.split('_');
     var id 			= split_id[1];
     var id2 			= split_id[2];

     var process		= $(this).val();
     var product		= $("#cost_"+id).val();

     $.ajax({
       url: base_url+active_controller+'/get_list_process2/'+process+'/'+product,
       cache: false,
       type: "POST",
       dataType: "json",
       success: function(data){
         // $("#process_"+id+"_"+id2).html(data.option).trigger("chosen:updated");
         $("#code_"+id+"_"+id2).html(data.daycode).trigger("chosen:updated");
         $('.chosen_select').select2({width: '100%'});
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

   $(document).on('click', '#save', function(e){
     e.preventDefault();
     var tanggal_produksi	= $('#tanggal_produksi').val();
     var costcenter	= $('.costcenter').val();
     var id_product		= $('.id_product').val();
     var code		= $('.code').val();
     var ket		= $('.ket').val();
     // $('#save').prop('disabled',true);
     if(tanggal_produksi == '' ){
       swal({
         title	: "Error Message!",
         text	: 'Production date is empty, select first ...',
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
     if(id_product == '0' ){
       swal({
         title	: "Error Message!",
         text	: 'Product name empty, select first ...',
         type	: "warning"
       });

       $('#save').prop('disabled',false);
       return false;
     }
     if(code == '0' ){
       swal({
         title	: "Error Message!",
         text	: 'Daycode empty, select first ...',
         type	: "warning"
       });

       $('#save').prop('disabled',false);
       return false;
     }
     if(ket == '0' ){
       swal({
         title	: "Error Message!",
         text	: 'Status empty, select first ...',
         type	: "warning"
       });

       $('#save').prop('disabled',false);
       return false;
     }


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
           var baseurl=siteurl+'produksi/save_process';
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
                   swal({
                     title	: "Save Failed!",
                     text	: data.pesan,
                     type	: "warning",
                     timer	: 7000,
                     showCancelButton	: false,
                     showConfirmButton	: false,
                     allowOutsideClick	: false
                   });
                   $('#save').prop('disabled',false);
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
         $('#save').prop('disabled',false);
         return false;
         }
     });
   });

});

</script>
