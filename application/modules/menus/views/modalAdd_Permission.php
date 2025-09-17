<?php

$barang_tambahan = $this->db->query("SELECT * FROM supplier_barang s INNER JOIN barang_master m ON s.id_barang = m.id_barang  WHERE s.id_supplier = '$sup' AND s.id_barang NOT IN (SELECT b.id_barang FROM trans_po_detail b WHERE b.no_po = '$no')")->result_array();

$kurs_rmb = $this->db->get_where('mata_uang',array("kode"=>"RMB"))->row();
$kurs_usd = $this->db->get_where('mata_uang',array("kode"=>"USD"))->row();
$usd_to_rmb = round($kurs_usd->kurs/$kurs_rmb->kurs,2);

?>
<div class="box box-success">
	<div class="box-body" style="">
		<table id="my-grid" class="table table-striped table-bordered table-hover table-condensed" width="100%">
			<tbody>
				<tr style='background-color: #175477; color: white; font-size: 15px;'>
					<th class="text-center" >Pilih Spesifikasi</th>
				</tr>
			</tbody>
		</table>
		<br>

		<?php
			echo form_button(array('type'=>'button','class'=>'btn btn-md btn-success','style'=>'min-width:100px; float:right;','value'=>'save','content'=>'Add','id'=>'addItem')).' ';
		?>
	</div>
</div>

<style>
	.inSp{
		text-align: center;
		display: inline-block;
		width: 100px;
	}
	.inSp2{
		text-align: center;
		display: inline-block;
		width: 80px;
	}
	.inSpL{
		text-align: left;
	}
	.vMid{
		vertical-align: middle !important;
	}

</style>

<script>
	$(".numberOnly").on("keypress keyup blur",function (event) {
		if ((event.which < 48 || event.which > 57 ) && (event.which < 46 || event.which > 46 )) {
			event.preventDefault();
		}
	});

	function getNum(val) {
	   if (isNaN(val) || val == '') {
		 return 0;
	   }
	   return parseFloat(val);
	}
  function getItem(nm) {
    $("#nama_barang").val(nm.options[nm.selectedIndex].text);
  }
  function calc(){
    var qty_po = parseFloat($('#qty_acc').val());
    var qty_i = parseFloat($('#qty_i').val());
    var harga_satuan = parseFloat($('#harga_satuan').val());
    var kurs = $('#kurs').val();
    var kurs_usd  = parseFloat($('#kurs_dollar').val());
    var kurs_rmb  = parseFloat($('#kurs_rmb').val());
    if (isNaN($('#qty_acc').val())) {
      qty_po = 0;
    }
    if (isNaN($('#qty_i').val())) {
      qty_i = 0;
    }
    if (isNaN(harga_satuan) || harga_satuan == null || harga_satuan == '' || harga_satuan == 0) {
      harga_satuan = 0;
    }

    var konversi = 0;
    var subtotal_kurs = 0;
    var subtotal_rp = 0;
		var text_sub = 0;
    if (kurs == 'dollar') {
      var subtotal_kurs =  qty_i*harga_satuan;
      var subtotal_rp =  qty_i*(harga_satuan*kurs_usd);
      konversi  = (harga_satuan*kurs_usd);
      var text_sub = "$. "+subtotal_kurs.format(2,3,',');
    }
    else if (kurs == 'rmb') {
      var subtotal_kurs =  qty_i*harga_satuan;
      var subtotal_rp =  qty_i*(harga_satuan/kurs_rmb*kurs_usd);
      konversi  = (harga_satuan/kurs_rmb*kurs_usd);
      var text_sub = "¥. "+subtotal_kurs.format(2,3,',');
    }

    $('#konversi').text("Rp "+konversi.format(2,3,','));
    $('#subtotal').text(text_sub+" / "+"Rp "+subtotal_rp.format(2,3,','));
		return {
		konversi : konversi,
		subtotal_rp : subtotal_rp,
		subtotal_kurs : subtotal_kurs,
		}
  }
	$(document).ready(function() {
    $(".select2").select2({
      placeholder: "Pilih",
      allowClear: true
    });

    $('.calc').on( 'click keyup keypress blur', function () {
			console.log('a');
      //$(this).parents('tr').remove();
      calc();
    } );
    $('#kurs').on( 'change', 'select.barang_t', function () {
      calc();
    } );

  } );

</script>
