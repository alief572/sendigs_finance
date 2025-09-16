<?php

if (!empty($id)) {
  $getS   = $this->db->get_where('master_supplier',array('id_supplier'=>$id))->row();
  $arrB   = explode(";",$getS->id_brand);
}
$getB		= $this->db->get('master_product_brand')->result();
echo "$id";
?>
<?php selbrand: ?>

<form class="" action="" method="post" id="form-selBrand">
  <div class="box box-success">
    <div class="box-body" style="">
      <table id="my-grid" class="table table-striped table-bordered table-hover table-condensed" width="100%">
        <thead>
          <tr>
            <th>Code</th>
            <th>Brand Name</th>
          </tr>
        </thead>
        <tbody id="tableselBrand_tbody">

        </tbody>
      </table>
      <br>
      <a class="btn btn-sm btn-success" id="addBrand">Add Brand</a>
      <br>
      <br>
      <?php
      echo form_button(array('type'=>'button','class'=>'btn btn-md btn-success','style'=>'min-width:100px; float:right;','value'=>'save','content'=>'Save','id'=>'saveSelBrand')).' ';
      ?>
    </div>
  </div>
</form>


<style>
	.inSp{
		text-align: center;
		display: inline-block;
		width: 100px;
	}
	.inSp2{
		text-align: center;
		display: inline-block;
		width: 45%;
	}
	.inSpL{
		text-align: left;
	}
	.vMid{
		vertical-align: middle !important;
	}

</style>

<script type="text/javascript">

	$(document).ready(function(){
    //console.log('<?=$id?>');
    //DataTables();

    var id_brand = $('#id_brand').val();
    console.log(id_brand);
    $.ajax({
      url: siteurl+active_controller+"getRefreshBrand",
      dataType : "json",
      type: 'POST',
      data: {id: '<?=$id?>',idb:id_brand},
      success: function(result){
        $('#tableselBrand_tbody').empty();
        $('#tableselBrand_tbody').append(result.html);
      },
      error: function (request, error) {
        console.log(arguments);
        alert(" Can't do because: " + error);
      }
    });

    $('#addListBrand').click(function(e){
      var x = parseInt(document.getElementById("addbrand_tbody").rows.length)+1;
      console.log(x);
      var row = '<tr class="addjs">'+
            '<td style="background-color:#E0E0E0">'+
            '<a class="text-red hapus_item_js" href="javascript:void(0)" title="Delete List"><i class="fa fa-times"></i></a> || <span class="numbering">'+
            x+'</span> '+
            '<input type="text" name="[]"  class="form-control input-sm " required="" placeholder="Input PIC Name" style="width:75%;text-align:center;display:inline-block">'+
						/*'<select name="step[]" id="step[]" class="chosen_select form-control inline-block select_step">'+
							'<option value="0">Select Step</option>'+
						'<?php foreach($getStep AS $val => $valx){ ?>'+
								'<option value="<?=$valx["step_name"]?>"><?=strtoupper($valx["step_name"])?></option>'+
						'<?php } ?>'+
						'</select>'+*/
            '</td>'+
            '<td style="background-color:#E0E0E0;text-align:center">'+
            '<input type="text" name="pic_phone[]"  class="form-control input-sm inSp2 " required="" placeholder="Input PIC Number">'+
            '<input type="text" name="pic_email[]"  class="form-control input-sm inSp2 " required="" placeholder="Input PIC Email">'+
            '</td>'+
        '</tr>'

      $('#tfoot-pic').append(row);

    });

    //REMOVE LIST BUTTON
    $('#tfoot-pic').on( 'click', 'a.hapus_item_js', function () {
			console.log('a');
      $(this).parents('tr').remove();
      if (parseInt(document.getElementById("tfoot-pic").rows.length) == 0) {
        var x=1;
      }else {
        var x = parseInt(document.getElementById("tfoot-pic").rows.length)+1;
      }
      for (var i = 0; i < x; i++) {
        $('.numbering').eq(i-1).text(i);
      }
    } );

    $(".datepicker").datepicker({
        format : "yyyy-mm-dd",
        showInputs: true,
        autoclose:true
    });

    $(".select2").select2({
      placeholder: "Pilih",
      allowClear: true
    });

    function num(n) {
      return (n).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }

    $('#ModalView3').on('hide.bs.modal', function (e) {
      $.ajax({
        url: siteurl+active_controller+"getRefreshBrand",
        dataType : "json",
        type: 'POST',
        data: {id: '<?=$id?>',idb:id_brand},
        success: function(result){
          $('#tableselBrand_tbody').empty();
          $('#tableselBrand_tbody').append(result.html);
        },
        error: function (request, error) {
          console.log(arguments);
          alert(" Can't do because: " + error);
        }
      });
    })

	});



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

  function numfor(nmbr, n, x, s, c) {
    var re = '\\d(?=(\\d{' + (x || 3) + '})+' + (n > 0 ? '\\D' : '$') + ')',
      num = nmbr.toFixed(Math.max(0, ~~n));

    return (c ? num.replace('.', c) : num).replace(new RegExp(re, 'g'), '$&' + (s || ','));
  };

</script>
