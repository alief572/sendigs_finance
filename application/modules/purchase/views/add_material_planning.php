
<div class="box box-primary">
   <div class="box-body"><br>
   <form id="form_material" method="post" autocomplete="='off'">
     <input type="hidden" name="no_req" value="<?=$no_req;?>">
     <input type="hidden" name="bulan_awal" value="<?=$tgl_awal;?>">
     <input type="hidden" name="bulan_akhir" value="<?=$tgl_akhir;?>">
     <div class="tableFixHead" style="height:600px;">
     <table id="mytable" class='table table-striped table-bordered table-hover table-condensed' style="border-collapse:collapse;text-align:center;width:100%;">
       <thead>
         <tr class='bg-blue thead'>
            <th class='text-center fixedTop' rowspan="2" style='width: 4%;vertical-align:middle;'>#</th>
            <th class='text-center fixedTop' rowspan="2" style='vertical-align:middle;'>Material Name</th>
            <th class='text-center fixedTop' rowspan="2" style='width: 7%;vertical-align:middle;'>Stock SubGudang</th>
            <th class='text-center fixedTop' rowspan="2" style='width: 7%;vertical-align:middle;'>Stock Pusat</th>
            <th class='text-center fixedTop' colspan="2" style='width: 12%;'><?=$today3;?></th>
            <th class='text-center fixedTop' colspan="2" style='width: 12%;'><?=$nextM3;?></th>
            <th class='text-center fixedTop' colspan="2" style='width: 12%;'><?=$nextN3;?></th>
            <th class='text-center fixedTop' colspan="2" style='width: 12%;'><?=$nextO3;?></th>
            <th class='text-center fixedTop' rowspan="2" style='width: 7%;vertical-align:middle;'>Kebutuhan<br>4 Bulan</th>
            <th class='text-center fixedTop' rowspan="2" style='width: 7%;vertical-align:middle;'>Qty Order</th>
         </tr>
         <tr class='bg-blue thead'>
            <th class='text-center' style='width: 6%;'>Booking</th>
            <th class='text-center' style='width: 6%;'>Free</th>
            <th class='text-center' style='width: 6%;'>Booking</th>
            <th class='text-center' style='width: 6%;'>Free</th>
            <th class='text-center' style='width: 6%;'>Booking</th>
            <th class='text-center' style='width: 6%;'>Free</th>
            <td class='text-center fixedTop2' style='width: 6%;'>Booking</td>
            <td class='text-center fixedTop2' style='width: 6%;'>Free</td>
         </tr>
       </thead>
       <tbody>
         <?php
         $no = 0;
         $readonly = '';
         if($tanda == 'view'){
           $readonly = 'disabled';
         }
         foreach($detail AS $val => $valx){
           if($valx['material'] <> '0'){
             $no++;
             $stock = get_name('warehouse_stock','qty_stock','id_material',$valx['material']);
             $book  = get_name('warehouse_stock','qty_booking','id_material',$valx['material']);

             $stock_pusat  = get_stock_material($valx['material'], '1');


             $req  = get_edit_plan_mat($no_req,$valx['material']);

             $get1  = get_plan_mat($valx['material'],$today1,$today2);
             $get2  = get_plan_mat($valx['material'],$nextM1,$nextM2);
             $get3  = get_plan_mat($valx['material'],$nextN1,$nextN2);
             $get4  = get_plan_mat($valx['material'],$nextO1,$nextO2);

             $stocking =  get_stock_material($valx['material'], '2');

             $stocking2x =  $stocking + $stock_pusat;
             $stocking1 =  $stocking2x - $get1;
             $stocking2 =  $stocking1 - $get2;
             $stocking3 =  $stocking2 - $get3;
             $stocking4 =  $stocking3 - $get4;

             $sum_booking = $get1 + $get2 + $get3 + $get4;
             echo "<tr>";
              echo "<td class='mid' align='center'>".$no."<input type='hidden' name='detail[".$no."][code_material]' value='".$valx['material']."'></td>";
              echo "<td class='mid' align='left'>".strtoupper(get_name('ms_material','nm_material','code_material',$valx['material']))."</td>";
              echo "<td class='mid' align='right'>".number_format($stocking, 2)."</td>";
              echo "<td class='mid' align='right'>".number_format($stock_pusat, 2)."</td>";

              echo "<td class='mid' align='right'>".number_format($get1,2)."</td>";
              echo "<td class='mid' align='right'>".number_format($stocking1, 2)."</td>";
              echo "<td class='mid' align='right'>".number_format($get2,2)."</td>";
              echo "<td class='mid' align='right'>".number_format($stocking2, 2)."</td>";
              echo "<td class='mid' align='right'>".number_format($get3,2)."</td>";
              echo "<td class='mid' align='right'>".number_format($stocking3, 2)."</td>";
              echo "<td class='mid' align='right'>".number_format($get4,2)."</td>";
              echo "<td class='mid' align='right'>".number_format($stocking4, 2)."</td>";

              echo "<td class='mid' align='right'>".number_format($sum_booking, 2)."</td>";
              echo "<td align='right'><input type='text' name='detail[".$no."][qty_order]' value='".number_format($req)."' ".$readonly." class='form-control input-md text-right maskM' placeholder='Qty Order'></td>";
             echo "</tr>";
           }
         }
         // data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''
          ?>
       </tbody>
     </table>
   </div>
   <br>
     <button type="button" class="btn btn-danger" style='float:right; margin-left:5px;' name="back" id="back"><i class="fa fa-reply"></i> Back</button>
     <?php if (empty($tanda)): ?>
       <button type="button" class="btn btn-primary" style='float:right;' name="save" id="save"><i class="fa fa-save"></i> Save</button>
     <?php endif; ?>
     <?php if ($tanda == 'edit'): ?>
       <button type="button" class="btn btn-success" style='float:right;' name="edit" id="save"><i class="fa fa-save"></i> Update</button>
     <?php endif; ?>
     </form>
 </div>
</div>


<script src="<?= base_url('assets/js/jquery.maskMoney.js')?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>
<style>
  .mid{
    vertical-align: middle !important;
  }

  /* JUST COMMON TABLE STYLES... */
  .table { border-collapse: collapse; width: 100%; }
  .td { background: #fff; padding: 8px 16px; }


  .tableFixHead {
    overflow: auto;
    height: 100px;
  }

  .fixedTop{
    position: sticky;
    top: 0;
    z-index: 999;
    background-color:#0073b7 ;
  }

  .fixedTop2{
    position: sticky;
    position: -webkit-sticky;
    top: 0;
    z-index: 9999;
    background-color: red;
  }
</style>
<script type="text/javascript">
 //$('#input-kendaraan').hide();
 var base_url			= '<?php echo base_url(); ?>';
 var active_controller	= '<?php echo($this->uri->segment(1)); ?>';

 $(document).ready(function(){

   $('.maskM').maskMoney();
   $(document).on('click', '#back', function(){
       window.location.href = base_url + active_controller +'/material_planning';
   });

   $(document).on('click', '#save', function(){

     swal({
       title: "Are you sure?",
       text: "You will booking material planning be able to process again this data!",
       type: "warning",
       showCancelButton: true,
       confirmButtonClass: "btn-danger",
       confirmButtonText: "Yes, Process it!",
       cancelButtonText: "No, cancel process!",
       closeOnConfirm: false,
       closeOnCancel: false
     },
     function(isConfirm) {
       if (isConfirm) {
         // loading_spinner();
         var formData  	= new FormData($('#form_material')[0]);
         $.ajax({
           url			: base_url+'purchase/add_material_planning',
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
               window.location.href = base_url + active_controller+'/material_planning';
             }
             else if(data.status == 0){
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

</script>
