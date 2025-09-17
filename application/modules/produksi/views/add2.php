<?php
// echo get_before_costcenter_warehouse('I2000001', 'CC2000009');
 ?>
<form id="data-form" method="post">
 <div class='box box-info'>
   <br>
   <div class='box-body'>
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
           <th class='text-center' style='width: 40%;'>Daycode</th>
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
         				echo "<td align='center'>";
         				echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
         				echo "</td>";
         			echo "</tr>";

           		//add nya
           		echo "<tr id='add_".$id."_0' class='header_".$id."'>";
           			echo "<td align='center'></td>";
           			echo "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' data-id='".$id."' class='btn btn-sm btn-primary addSubPart' title='Add Product'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add</button></td>";
           			echo "<td align='center'></td>";
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
       url: base_url+'index.php/'+active_controller+'/get_add_sub/'+id+'/'+id2+'/'+process,
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

   //add part
   $(document).on('change', '.costcenter', function(){
     // loading_spinner();
     var id_cost = $(this).data('id');
     var process		= $(this).val();

     $.ajax({
       url: base_url+'index.php/'+active_controller+'/get_list_product/'+process,
       cache: false,
       type: "POST",
       dataType: "json",
       success: function(data){
         $(".cost_"+id_cost).html(data.option).trigger("chosen:updated");
         $(".pro_"+id_cost).html("<option value='0'>List Empty</option>").trigger("chosen:updated");
         $(".code_"+id_cost).html("<option value='0'>List Empty</option>").trigger("chosen:updated");
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
       url: base_url+active_controller+'/get_list_process/'+process+'/'+product,
       cache: false,
       type: "POST",
       dataType: "json",
       success: function(data){
         $("#process_"+id+"_"+id2).html(data.option).trigger("chosen:updated");
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





   $('#save').click(function(e){
     e.preventDefault();
     var tanggal_produksi	= $('#tanggal_produksi').val();
     var costcenter	= $('.costcenter').val();
     var id_product		= $('.id_product').val();
     // var id_process		= $('.id_process').val();
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
     // if(id_process == '0' ){
     //   swal({
     //     title	: "Error Message!",
     //     text	: 'Process name empty, select first ...',
     //     type	: "warning"
     //   });
     //
     //   $('#save').prop('disabled',false);
     //   return false;
     // }
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
