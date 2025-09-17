
<div class="box box-primary">
    <div class="box-body">
        <br>
        <div class='form-group row'>		 	 
            <label class='label-control col-sm-2'><b>Supplier Name <span class='text-red'>*</span></b></label>
            <div class='col-sm-4'>              
                <select id='id_supplier' name='id_supplier' class='form-control input-sm chosen-select'>
                    <option value="0">Select An Supplier</option>
                    <?php
                        foreach($supList AS $val => $valx){
                            echo "<option value='".$valx['id_supplier']."'>".strtoupper($valx['nm_supplier'])."</option>";
                        }
                    ?>
                </select>
            </div>
            <label class='label-control col-sm-2'><b>Repeat-PO Valid</b></label>
            <div class='col-sm-4'>              
                <input type="text" id='valid_date' name='valid_date' class='form-control input-md datepicker' placeholder='Jangan dipilih jika tidak repeat PO' readonly>
            </div>
        </div>
        <div class='form-group row'>		 	 
            <label class='label-control col-sm-2'><b>Category <span class='text-red'>*</span></b></label>
            <div class='col-sm-4'>              
                <select id='category2' name='category2' class='form-control input-sm chosen-select'>
                    <option value='0'>Select Category</option>
                    <option value='asset'>ASSET</option>
                    <option value='rutin'>STOK</option>
				    <option value='non rutin'>DEPARTEMEN</option>
                </select>
            </div>
            <label class='label-control col-sm-2'><b>Tanggal Dibutuhkan <span class='text-red'>*</span></b></label>
            <div class='col-sm-4'>              
                <input type="text" id='tanggal_dibutuhkan' name='tanggal_dibutuhkan' class='form-control input-md datepicker' readonly>
            </div>
        </div>
        <?php
			echo form_button(array('type'=>'button','class'=>'btn btn-sm btn-success','style'=>'min-width:100px; float:right; margin: 5px 0px 5px 0px;','value'=>'Create PO','content'=>'Create','id'=>'savePO')).' ';
		?>
        <br><br>
        <div class="box box-success"> 
            <br>
            <table class="table table-bordered table-striped" id="my-grid3" width='100%'>
                <thead>
                    <tr class='bg-blue'>
                        <th class="text-center no-sort" width='3%'>#</th>
                        <th class="text-center" width='8%'>No RFQ</th> 
                        <th class="text-center">Material Name</th>
                        <th class="text-right" width='8%'>Qty PR</th>
                        <th class="text-right" width='8%'>Qty PO</th>
						<th class="text-center" width='8%'>Dibutuhkan</th>
						<th class="text-center" width='7%'>Created</th>
						<th class="text-center" width='7%'>Dated</th>
						<th class="text-right" width='10%'>Net Price</th>
						<th class="text-right" width='4%'>#</th>
                        <th class="text-center" width='10%'>Qty PO</th>
                        <th class="text-center" width='10%'>Total Price</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
            <br>
            <table class='table2' width='100%' cellpadding='2'>
                <tbody>
                    <tr>
                        <td width='60%'></td>
                        <td class='text-right mid' width='25%'><b>TOTAL&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>
                        <td class='mid'><input type="text" id='total_po'  name='total_po' class='form-control input-sm text-right text-bold' placeholder='Total' readonly></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td class='text-right mid'><b>DISCOUNT (%)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>
                        <td class='mid'><input type="text" id='discount' name='discount' class='form-control input-sm text-right text-bold autoNumeric' placeholder='Discount (%)'></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td class='text-right mid'><b>NET PRICE&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>
                        <td class='mid'><input type="text" id='net_price' name='net_price' class='form-control input-sm text-right text-bold' readonly placeholder='Net Price'></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td class='text-right mid'><b>TAX (%)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>
                        <td class='mid'><input type="text" id='tax' name='tax' class='form-control input-sm text-right text-bold autoNumeric' placeholder='Tax (%)'></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td class='text-right mid'><b>NET PRICE + TAX&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>
                        <td class='mid'><input type="text" id='net_plus_tax' name='net_plus_tax' class='form-control input-sm text-right text-bold' readonly placeholder='Net Price + Tax'></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td class='text-right mid'><b>DELIVERY COST&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>
                        <td class='mid'><input type="text" id='delivery_cost' name='delivery_cost' class='form-control input-sm text-right text-bold autoNumeric' placeholder='Delivery Cost'></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td class='text-right mid'><b>GRAND TOTAL&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>
                        <td class='mid'><input type="text" id='grand_total' name='grand_total' class='form-control input-sm text-right text-bold' readonly placeholder='Grand Total'></td>
                    </tr>
                </tbody>
            </table>
        </div>
	</div>
</div>
<style>
	.chosen-container{
		width: 100% !important;
		text-align : left !important;
	}
    .mid{
        vertical-align: middle !important;
        padding: 2px;
    }
    .datepicker{
        cursor:pointer;
    }
</style>
<script>
    swal.close();
    $(document).ready(function(){
        var id_supplier = $('#id_supplier').val();
        var category = $('#category2').val();
        DataTables3(id_supplier, category);
		$('.chosen-select').chosen();
        $('.datepicker').datepicker({
            dateFormat: 'dd-MM-yy',
            changeMonth:true,
            changeYear:true
        });
        $(".autoNumeric").autoNumeric('init', {mDec: '2', aPad: false});
	});

    $(document).on('change','#id_supplier, #category2', function(){
		var id_supplier = $('#id_supplier').val();
        var category = $('#category2').val();
        DataTables3(id_supplier, category);
	});
	
	$(document).on('click', '.check_pr', function(){
		sumTotal();
	});

    $(document).on('keyup', '.qty_po', function(){
        let qty_po      = getNum($(this).val().split(",").join(""))
        let harga_idr   = getNum($(this).parent().parent().parent().find('.harga_idr').html().split(",").join(""))
        let total_harga = qty_po * harga_idr
        $(this).parent().parent().parent().find('.total_harga').html(number_format(total_harga,2))
        $(this).parent().parent().parent().find('.total_harga_val').val(total_harga)
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
        $(".check_pr" ).each(function() {
            if ($(this).is(':checked')) {
                total = getNum($(this).parent().parent().parent().find('.total_harga').html().split(",").join(""))
                sum_total += Number(total);
            }
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