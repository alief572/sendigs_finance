<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="https://cdn.rawgit.com/harvesthq/chosen/gh-pages/chosen.jquery.min.js"></script>
<link href="https://cdn.rawgit.com/harvesthq/chosen/gh-pages/chosen.min.css" rel="stylesheet"/>
<script>
$(".chosen-select").chosen({
  no_results_text: "Oops, nothing found!"
})
</script>

<div class="box box-primary">
    <div class="box-body">
      <input type="hidden" id="emp" value="0">
      <form id="data-form" method="post" autocomplete="off">
        <?php
        echo form_button(array('type'=>'button','class'=>'btn btn-sm btn-success','style'=>'float:right; margin-bottom:5px;','value'=>'back','content'=>'Add Costcenter','id'=>'add-payment'));
        ?>
        <table class='table table-bordered table-striped'>
          <thead>
            <tr class='bg-blue'>
              <td align='center'><b>Costcenter Name</b></td>
              <td align='center'><b>Shift 1</b></td>
              <td align='center'><b>Shift 2</b></td>
              <td align='center'><b>Shift 3</b></td>
              <td align='center'><b>#</b></td>
            </tr>
          </thead>
          <tbody id='list_payment'></tbody>
          <tbody id='list_empty'>
          <tr>
            <td colspan='5'>Empty List</td>
          </tr>
        </tbody>
        </table>
        <button type="button" class="btn btn-danger" style='float:right; margin-left:5px;' name="back" id="back"><i class="fa fa-reply"></i> Back</button>
        <button type="submit" class="btn btn-primary btn-md" style='float:right;' name="save" id="simpan-com"><i class="fa fa-save"></i> Save</button>
    </form>
	</div>
</div>


<script type="text/javascript">
	var base_url			    = '<?php echo base_url(); ?>';
	var active_controller	= '<?php echo($this->uri->segment(1)); ?>';

  //add part
  $(document).on('click', '#back', function(){
      window.location.href = base_url + active_controller;
  });

	$(document).ready(function(){
    $('#add-payment').click(function(){
      var jumlah	=$('#list_payment').find('tr').length;
      if(jumlah==0 || jumlah==null){
        var ada		= 0;
        var loop	= 1;
      }
      else{
        var nilai		= $('#list_payment tr:last').attr('id');
        var jum1		= nilai.split('_');
        var loop		= parseInt(jum1[1])+1;
      }

      var emp = $("#emp").val();
      $("#emp").val(parseInt(emp)+1);
      $("#list_empty").hide();

      Template	='<tr id="tr_'+loop+'">';
      Template	+='<td align="left">';
      Template	+='<input type="text" class="form-control input-md" name="detail['+loop+'][costcenter]" id="data1_'+loop+'_costcenter" label="FALSE" div="FALSE" placeholder="Costcenter Name">';
      Template	+='</td>';
      Template	+='<td align="center">';
      Template	+='<input type="text" class="form-control input-md text-center" name="detail['+loop+'][mp_1]" id="data1_'+loop+'_manpower" label="FALSE" div="FALSE" placeholder="Qty Man Power Shif 1" onkeyup="this.value = this.value.match(/^-?\\\d*[.]?\\\d*$/);">';
      Template	+='</td>';
      Template	+='<td align="center">';
      Template	+='<input type="text" class="form-control input-md text-center" name="detail['+loop+'][mp_2]" id="data1_'+loop+'_manpower" label="FALSE" div="FALSE" placeholder="Qty Man Power Shif 2" onkeyup="this.value = this.value.match(/^-?\\\d*[.]?\\\d*$/);">';
      Template	+='</td>';
      Template	+='<td align="center">';
      Template	+='<input type="text" class="form-control input-md text-center" name="detail['+loop+'][mp_3]" id="data1_'+loop+'_manpower" label="FALSE" div="FALSE" placeholder="Qty Man Power Shif 3" onkeyup="this.value = this.value.match(/^-?\\\d*[.]?\\\d*$/);">';
      Template	+='</td>';
      Template	+='<td align="center"><button type="button" class="btn btn-sm btn-danger" title="Hapus Data" data-role="qtip" onClick="return DelItem('+loop+');"><i class="fa fa-trash-o"></i></button></td>';
      Template	+='</tr>';

      $('#list_payment').append(Template);
      $('.chosen-select').chosen();
    });



	$('#simpan-com').click(function(e){
			e.preventDefault();
		  var emp = $("#emp").val();
			var data, xhr;

      if(emp=='0'){
				swal({
				  title	: "Error Message!",
				  text	: 'List Empty, please add first ...',
				  type	: "warning"
				});
				$('#simpan-com').prop('disabled',false);
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
						var baseurl=siteurl+'costcenter/saveNewCostcenter';
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

	function DelItem(id){
		$('#list_payment #tr_'+id).remove();
    var emp = $("#emp").val();
    $("#emp").val(parseInt(emp)-1);
    if(parseInt(emp)-1 == 0){
      $("#list_empty").show();
    }
	}
</script>
