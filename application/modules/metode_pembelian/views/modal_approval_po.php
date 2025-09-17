
<div class="box-body">
    <div class="form-group row">
		<div class='col-sm-4 '>
		   <label class='label-control'>Approve Action</label>
		   <select name='status' id='status' class='form-control input-md'>
				<option value='0'>Select Action</option>
				<option value='Y'>APPROVE</option>
				<option value='N'>REJECT</option>
			</select>
            <input type="hidden" name='id'  id='id' value='<?=$id;?>'>
            <input type="hidden" name='no_po' id='no_po' value='<?=$no_po;?>'>
            <input type="hidden" name='nilai_po' id='nilai_po' value='<?=$nilai_po;?>'>
		</div>
		<div class='col-sm-8 '>
			<div id='HideReject'>
				<label class='label-control'>Reject Reason</label>          
				<?php
					echo form_textarea(array('id'=>'approve_reason','name'=>'approve_reason','class'=>'form-control input-md', 'cols'=>'75','rows'=>'3','autocomplete'=>'off','placeholder'=>'Reason Reject'));
				?>		
			</div>
		</div>
	</div>
	<div class="form-group row">
		<div class='col-sm-12 '>
		<?php
			echo form_button(array('type'=>'button','class'=>'btn btn-md btn-success','style'=>'float:right; margin-left:5px;','content'=>'Confirm','id'=>'process'));
		?>
		</div>
	</div>
    <hr>
    <div class="form-group row">
		<div class='col-sm-1 text-bold'><label>Nomor PO</label></div>
        <div class='col-sm-5'>: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=$no_po;?></div>
        <div class='col-sm-2 text-bold'><label>Tgl Dibutuhkan</label></div>
        <div class='col-sm-4'>: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=date('d F Y', strtotime($result[0]['tgl_butuh']));?></div>
	</div>
    <div class="form-group row">
		<div class='col-sm-1 text-bold'><label>Customer</label></div>
        <div class='col-sm-5'>: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=$result[0]['nm_supplier'];?></div>
	</div>
	<table id="my-grid" class="table table-striped table-bordered table-hover table-condensed" width="100%">
        <thead id='head_table'>
            <tr class='bg-blue'>
                <th class="text-center no-sort" width='5%'>#</th>
                <th class="text-center">Material Name</th>
                <th class="text-center" width='8%'>MOQ</th>
                <th class="text-center" width='8%'>Lead Time</th>
                <th class="text-center" width='15%'>Net Price (IDR)</th>
                <th class="text-center" width='10%'>Qty PO</th>
                <th class="text-center" width='15%'>Total Price (IDR)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $jumlah = count($result);
            $no  = 0;
            $SUM = 0;
            foreach($result AS $val => $valx){ $no++;
                
                $nm_material = $valx['nm_barang'];
                
                echo "<tr>";
                    echo "<td class='vmiddle' align='center'>".$no++."</td>";
                    echo "<td class='vmiddle' align='left'>".strtoupper($nm_material)."</td>";
                    echo "<td class='vmiddle' align='center'>".number_format($valx['moq'],2)."</td>";
                    echo "<td class='vmiddle' align='center'>".number_format($valx['lead_time'],2)."</td>";
                    echo "<td class='vmiddle' align='right'>".number_format($valx['net_price'])."</td>";
                    echo "<td class='vmiddle' align='right'>".number_format($valx['qty_purchase'])."</td>";
                    echo "<td class='vmiddle' align='right'>".number_format($valx['total_price'])."</td>";
               echo "</tr>";
            }
            ?>
            <tr>
                <td class='text-right mid' colspan='6'><b>TOTAL&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>
                <td class='text-right mid text-bold'><?=number_format($result[0]['total_po']);?></td>
            </tr>
            <tr>
                <td class='text-right mid' colspan='6'><b>DISCOUNT (%)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>
                <td class='text-right mid' text-bold><?=number_format($result[0]['discount'],2);?></td>
            </tr>
            <tr>
                <td class='text-right mid' colspan='6'><b>NET PRICE&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>
                <td class='text-right mid text-bold'><?=number_format($result[0]['net_price2']);?></td>
            </tr>
            <tr>
                <td class='text-right mid' colspan='6'><b>TAX (%)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>
                <td class='text-right mid' text-bold><?=number_format($result[0]['tax'],2);?></td>
            </tr>
            <tr>
                <td class='text-right mid' colspan='6'><b>NET PRICE + TAX&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>
                <td class='text-right mid text-bold'><?=number_format($result[0]['net_plus_tax']);?></td>
            </tr>
            <tr>
                <td class='text-right mid' colspan='6'><b>DELIVERY COST&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>
                <td class='text-right mid text-bold'><?=number_format($result[0]['delivery_cost']);?></td>
            </tr>
            <tr>
                <td class='text-right mid' colspan='6'><b>GRAND TOTAL&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>
                <td class='text-right mid text-bold'><?=number_format($result[0]['total_price2']);?></td>
            </tr>
        </tbody>
    </table>
</div>

<script>
	$(document).ready(function(){
		swal.close();
        $('#HideReject').hide();
        $(document).on('change', '#status', function(){
            if($(this).val() == 'N'){
                $('#HideReject').show();
            }
            else{
                $('#HideReject').hide();
            }
        });

        $(document).on('click', '#process', function(e){
            e.preventDefault();
            var id				= $('#id').val();
            var no_po			= $('#no_po').val();
            var status 			= $('#status').val();
            var approve_reason 	= $('#approve_reason').val();
            var nilai_po 	= $('#nilai_po').val();
            
            if(status == '0'){
                swal({
                title	: "Error Message!",
                text	: 'Action approve belum dipilih ...',
                type	: "warning"
                });
                $('#process').prop('disabled',false);
                return false;
            }
            
            if(status == 'N' && approve_reason == ''){
                swal({
                title	: "Error Message!",
                text	: 'Alasan reject masih kosong ...',
                type	: "warning"
                });
                $('#process').prop('disabled',false);
                return false;
            }
            
            swal({
            title: "Are you sure?",
            text: "Approve PO ?",
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
                    loading_spinner();
                    $.ajax({
                        url			: base_url + active_controller+'/modal_approval_po',
                        type		: "POST",
                        data		: {
                            'id'            : id,
                            'no_po'         : no_po,
                            'status'        : status,
                            'approve_reason': approve_reason,
                            'nilai_po'      : nilai_po
                        },
                        cache		: false,
                        dataType	: 'json',				
                        success		: function(data){								
                            if(data.status == 1){											
                                swal({
                                    title	: "Save Success!",
                                    text	: data.pesan,
                                    type	: "success",
                                    timer	: 3000
                                    });
                                window.location.href = base_url + active_controller +'/approval_po/'+data.id;
                            }
                            else if(data.status == 0){
                                swal({
                                title	: "Save Failed!",
                                text	: data.pesan,
                                type	: "warning",
                                timer	: 3000
                                });
                            }
                        },
                        error: function() {
                            swal({
                            title				: "Error Message !",
                            text				: 'An Error Occured During Process. Please try again..',						
                            type				: "warning",								  
                            timer				: 3000
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