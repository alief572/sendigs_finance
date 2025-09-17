
<div class="box-body"> 
	<form action="#" method="POST" id="form_edit_po" enctype="multipart/form-data" autocomplete='off'> 
		<input type='hidden' name='no_po' id='no_po' value='<?=$result[0]['no_po']?>'>
		<div class='form-group row'>
            <label class='label-control col-sm-2'><b>Tanggal Dibutuhkan <span class='text-red'>*</span></b></label>
            <div class='col-sm-4'>              
                <input type="text" id='tanggal_dibutuhkan' name='tanggal_dibutuhkan' class='form-control input-md datepicker' readonly value='<?=date('d-F-Y',strtotime($header[0]['tgl_dibutuhkan']))?>'>
            </div>
        </div>
		<table id="my-grid" class="table table-striped table-bordered table-hover table-condensed" width="100%">
			<thead id='head_table'>
				<tr class='bg-blue'>
				<th class="text-center no-sort" width='5%'>#</th>
					<th class="text-center">Material Name</th>
					<th class="text-center" width='8%'>MOQ</th>
					<th class="text-center" width='8%'>Lead Time</th>
					<th class="text-right" width='15%'>Net Price</th>
					<th class="text-center" width='10%'>Qty PO</th>
					<th class="text-center" width='15%'>Total Price</th>
					<th class="text-center" width='5%'>Option</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$jumlah = count($result);
				$no  = 0;
				$SUM_PO = 0;
				foreach($result AS $val => $valx){ $no++;
					$nm_material = $valx['nm_barang'];

					$SUM_PO += $valx['total_price'];
					echo "<tr>";
						echo "<td class='vmiddle' align='center'>".$no++;
							echo "<input type='hidden' name='detail[".$no."][id]' value='".$valx['id']."'>";
							echo "<input type='hidden' class='total_harga_val' name='detail[".$no."][total_price]' value='".$valx['total_price']."'>";
						echo "</td>";
						echo "<td class='vmiddle' align='left'><input name='detail[".$no."][nm_barang]' id='nm_barang_".$no."' class='form-control input-md' value='".strtoupper($valx['nm_barang'])."' ></td>";
						echo "<td class='vmiddle' align='center'>".number_format($valx['moq'],2)."</td>";
						echo "<td class='vmiddle' align='center'>".number_format($valx['lead_time'],2)."</td>";
						echo "<td class='vmiddle' align='right'><span class='harga_idr'>".number_format($valx['net_price'],2)."</span> <span class='text-primary text-bold'>".strtoupper($valx['currency'])."</span></td>";
						echo "<td class='vmiddle' align='right'><input type='text' class='form-control input-md text-center autoNumeric qty_po' name='detail[".$no."][qty_purchase]' value='".$valx['qty_purchase']."'></td>";
						echo "<td class='vmiddle total_harga' align='right'>".number_format($valx['total_price'],2)."</td>";
						echo "<td class='vmiddle' align='center'><button type='button' class='btn btn-sm btn-danger delete_sebagian_po' data-id='".$valx['id']."' title='Hapus Sebagian PO'><i class='fa fa-trash'></i></button></td>";
					echo "</tr>";
				}
				$AFTER_DISCOUNT = $SUM_PO - ($SUM_PO * $header[0]['discount'] / 100);
				$AFTER_TAX		= $AFTER_DISCOUNT + ($AFTER_DISCOUNT * $header[0]['tax'] / 100);
				$AFTER_DELIVERY = $AFTER_TAX + $header[0]['delivery_cost'];
				?>
				<tr>
					<td class='text-right mid' colspan='6'><b>TOTAL&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>
					<td class='mid'><input type="text" id='total_po'  name='total_po' class='form-control input-sm text-right text-bold autoNumeric' placeholder='Total' readonly value='<?=$SUM_PO;?>'></td>
					<td></td>
				</tr>
				<tr>
					<td class='text-right mid' colspan='6'><b>DISCOUNT (%)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>
					<td class='mid'><input type="text" id='discount' name='discount' class='form-control input-sm text-right text-bold autoNumeric' placeholder='Discount (%)' value='<?=$header[0]['discount'];?>'></td>
					<td></td>
				</tr>
				<tr>
					<td class='text-right mid' colspan='6'><b>NET PRICE&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>
					<td class='mid'><input type="text" id='net_price' name='net_price' class='form-control input-sm text-right text-bold autoNumeric' readonly placeholder='Net Price' value='<?=$AFTER_DISCOUNT;?>'></td>
					<td></td>
				</tr>
				<tr>
					<td class='text-right mid' colspan='6'><b>TAX (%)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>
					<td class='mid'><input type="text" id='tax' name='tax' class='form-control input-sm text-right text-bold autoNumeric' placeholder='Tax (%)' value='<?=$header[0]['tax'];?>'></td>
					<td></td>
				</tr>
				<tr>
					<td class='text-right mid' colspan='6'><b>NET PRICE + TAX&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>
					<td class='mid'><input type="text" id='net_plus_tax' name='net_plus_tax' class='form-control input-sm text-right text-bold autoNumeric' readonly placeholder='Net Price + Tax' value='<?=$AFTER_TAX;?>'></td>
					<td></td>
				</tr>
				<tr>
					<td class='text-right mid' colspan='6'><b>DELIVERY COST&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>
					<td class='mid'><input type="text" id='delivery_cost' name='delivery_cost' class='form-control input-sm text-right text-bold autoNumeric' placeholder='Delivery Cost' value='<?=$header[0]['delivery_cost'];?>'></td>
					<td></td>
				</tr>
				<tr>
					<td class='text-right mid' colspan='6'><b>GRAND TOTAL&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>
					<td class='mid'><input type="text" id='grand_total' name='grand_total' class='form-control input-sm text-right text-bold autoNumeric' readonly placeholder='Grand Total' value='<?=$AFTER_DELIVERY;?>'></td>
					<td></td>
				</tr>
			</tbody>
		</table>
	</form>
	<br>
	<div style='float:right;'>
	<?php
		echo form_button(array('type'=>'button','class'=>'btn btn-md btn-primary','value'=>'Update','content'=>'Update','id'=>'update_qty'));
	?>
	</div>
</div>
<style>
	.vmiddle{
		vertical-align:middle !important;
	}
	.mid{
        vertical-align: middle !important;
    }
	.datepicker{
        cursor:pointer;
    }
</style>
<script>
	$(document).ready(function(){
		swal.close();
		$('.datepicker').datepicker({
            dateFormat: 'dd-MM-yy',
            changeMonth:true,
            changeYear:true
        });
        $(".autoNumeric").autoNumeric('init', {mDec: '2', aPad: false});
	});

	$(document).on('keyup', '.qty_po', function(){
        let qty_po      = getNum($(this).val().split(",").join(""))
        let harga_idr   = getNum($(this).parent().parent().find('.harga_idr').html().split(",").join(""))
        let total_harga = qty_po * harga_idr

        $(this).parent().parent().find('.total_harga').html(number_format(total_harga,2))
        $(this).parent().parent().find('.total_harga_val').val(total_harga)
		sumTotal();
	});

    $(document).on('keyup', '#discount, #tax, #delivery_cost', function(){
        sumTotal();
	});

	let sumTotal = () => {
        let discount        =  getNum($('#discount').val().split(",").join(""))
        let tax             =  getNum($('#tax').val().split(",").join(""))
        let delivery_cost   =  getNum($('#delivery_cost').val().split(",").join(""))

		let sum_total = 0
        let total
        $(".qty_po" ).each(function() {
			total = getNum($(this).parent().parent().find('.total_harga').html().split(",").join(""))
			sum_total += Number(total);
        });
        $('#total_po').val(number_format(sum_total,2))
        let net_price = sum_total - (sum_total * discount / 100)
        $('#net_price').val(number_format(net_price,2))
        let net_plus_tax = net_price + (net_price * tax / 100)
        $('#net_plus_tax').val(number_format(net_plus_tax,2))
        let grand_total = net_plus_tax + delivery_cost
        $('#grand_total').val(number_format(grand_total,2))
	}
</script>