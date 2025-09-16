
<form id="data-form" method="post">
 <div class='box box-info'>
   <br>
   <div class='box-body'>
     <table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
       <thead>
         <tr class='bg-blue'>
           <th class='text-center'>Cost Center</th>
           <th class='text-center'>Process Name</th>
           <th class='text-center' width='15%'>Daycode</th>
           <th class='text-center' width='10%'>#</th>
         </tr>
       </thead>
       <tbody>
         <?php
          foreach($header AS $id => $valx){
            echo "<tr class='header_".$id."'>";
      				echo "<td align='left'>";
              echo "<input type='text' name='id_costcenter' class='form-control input-md' readonly='readonly'  value='".get_name('ms_costcenter', 'nama_costcenter', 'id_costcenter', $valx['id_costcenter'])."'>";
      				echo "</td>";
              echo "<td align='left'></td>";
      				echo "<td align='left'></td>";
              echo "<td align='left'></td>";
      			echo "</tr>";
          $no = 0;
            foreach($detail AS $id2 => $valx2){ $no++;
            echo "<tr class='header_".$id."'>";
      				echo "<td align='left' style='vertical-align:middle; padding-left: 30px;'>";
              echo "<input type='text' name='id_product' class='form-control input-md' readonly='readonly'  value='".get_name('ms_inventory_category2', 'nama', 'id_category2', $valx2['id_product'])."'>";
              echo "</td>";
              echo "<td align='left'>";
              echo "<input type='text' name='[id_process' class='form-control input-md' readonly='readonly'  value='".get_name('ms_process', 'nm_process', 'id', $valx2['id_process'])."'>";
              echo "<input type='hidden' name='Detail[".$no."][id]' class='form-control input-md' readonly='readonly' value='".$valx2['id']."'>";
              echo "</td>";
              echo "<td align='left'>";
              echo "<select name='Detail[".$no."][code]' class='chosen_select form-control input-sm inline-blockd code'>";
              foreach($daycode AS $val => $valx){
                $sel = ($valx2['code'] == $valx['code'])?'selected':'';
                echo "<option value='".$valx['code']."' ".$sel.">".strtoupper($valx['code'])."</option>";
              }
              echo 		"</select>";
      				echo "</td>";
              echo "<td align='left'>";
              echo "<select name='Detail[".$no."][ket]' class='chosen_select form-control input-sm inline-blockd ket'>";
              $selG = ($valx2['ket'] == 'good')?'selected':'';
              $selN = ($valx2['ket'] == 'bad')?'selected':'';
              echo "<option value='good' ".$selG.">GOOD</option>";
              echo "<option value='bad' ".$selN.">NOT GOOD</option>";
              echo 	"</select>";
      				echo "</td>";
      			echo "</tr>";
          }
        }
        ?>
       </tbody>
     </table>
     <br>
     <button type="submit" class="btn btn-primary" style='float:right;' name="save" id="save"><i class="fa fa-save"></i> Save</button>
   </div>
 </div>
</form>


<script src="<?= base_url('assets/js/jquery.maskMoney.js')?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>

<script type="text/javascript">
 //$('#input-kendaraan').hide();
 var base_url			= '<?php echo base_url(); ?>';
 var active_controller	= '<?php echo($this->uri->segment(1)); ?>';

 $(document).ready(function(){
   $('.chosen_select').select2();

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

     $.ajax({
       url: base_url+'index.php/'+active_controller+'/get_list_process/'+process,
       cache: false,
       type: "POST",
       dataType: "json",
       success: function(data){
         $(".pro_"+id_cost).html(data.option).trigger("chosen:updated");
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
     var costcenter	= $('.costcenter').val();
     var id_product		= $('.id_product').val();
     var id_process		= $('.id_process').val();
     var code		= $('.code').val();
     var ket		= $('.ket').val();


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
     if(id_process == '0' ){
       swal({
         title	: "Error Message!",
         text	: 'Process name empty, select first ...',
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
           var baseurl=siteurl+'produksi/edit_process';
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
