<?php
    $ENABLE_ADD     = has_permission('Packing_List.Add');
    $ENABLE_MANAGE  = has_permission('Packing_List.Manage');
    $ENABLE_VIEW    = has_permission('Packing_List.View');
    $ENABLE_DELETE  = has_permission('Packing_List.Delete');
?>

<form id="form_prosee" method="post">
  <div class="box-body">
    <div class="box box-primary">
      <div class="box-body"><br>
        <div class="form-group row">
          <div class="col-md-1">
            <b>Sales Order</b>
          </div>
          <div class="col-md-3">
            <select name='no_so[]' id='no_so' class='form-control input-sm chosen-select' multiple data-select="false">
    					<?php
    					foreach($sales_order AS $val => $valx){
    						echo "<option value='".$valx['no_so']."'>".strtoupper($valx['no_so']." - ".date('d-M-Y', strtotime($valx['delivery_date'])))."</option>";
    					}
    					?>
    				</select>
          </div>
          <div class="col-md-8">
            <button type="button" class="btn btn-primary" style='min-width:100px; float:right;' name="edit_temp" id="edit_temp">Edit Temp</button>
          </div>
        </div>

        <div class="form-group row">
          <div class="col-md-1"></div>
          <div class="col-md-11">
            <button type="button" class="btn btn-success" style='min-width:100px;' name="tampilkan" id="tampilkan">Tampilkan</button>
          </div>
        </div>

        <div class="box box-success">
          <div class="box-header">
        		<h3 class="box-title">Efektifitas Container</h3>
        	</div>
          <div class="box-body">
            <div class="form-group row">
              <div class="col-md-1">
                <b>Total karton</b>
              </div>
              <div class="col-md-3">
                <input type='text' name='total_karton' id='total_karton' class='form-control input-sm' placeholder="0" readonly>
              </div>
            </div>
            <div class="form-group row">
              <div class="col-md-1">
                <b>Total m3</b>
              </div>
              <div class="col-md-3">
                <input type='text' name='totalm3' id='totalm3' class='form-control input-sm' placeholder="0" readonly>
              </div>
            </div>
            <br>
            <div class="form-group row">
              <div class="col-md-8">
                <table class="table table-bordered table-condensed" width="100%">
                  <tbody>
                    <?php foreach(get_container() AS $val => $valx){ $val++;
                      $cub = $valx['length'] * $valx['wide'] * $valx['high'];
                      echo "<tr>";
                        echo "<th colspan='7'><b>".strtoupper($valx['nama'])."</b></th>";
                      echo "</tr>";
                      echo "<tr>";
                        echo "<th class='text-center headcol' width='15%'>L</th>";
                        echo "<th class='text-center headcol' width='15%'>W</th>";
                        echo "<th class='text-center headcol' width='15%'>H</th>";
                        echo "<th class='text-center headcol' width='15%'>CUB</th>";
                        echo "<th class='text-center headcol' width='15%'>".number_format($valx['persenan'])." %</th>";
                        echo "<th class='text-center headcol' width='15%'>Efektifitas (%)</th>";
                        echo "<th class='text-center headcol' width='10%'>#</th>";
                      echo "</tr>";
                      echo "<tr>";
                        echo "<td class='text-center'>".number_format($valx['length'],2)."</td>";
                        echo "<td class='text-center'>".number_format($valx['wide'],2)."</td>";
                        echo "<td class='text-center'>".number_format($valx['high'],2)."</td>";
                        echo "<td class='text-center'>".number_format($cub,4)."</td>";
                        echo "<td class='text-center'><div id='persen_".$val."' class='persenan'>".number_format($cub-($cub*($valx['persenan']/100)),2)."</div></td>";
                        echo "<td class='text-center'><div id='efek_".$val."'>0</div></td>";
                        echo "<td class='text-center'><input type='radio' name='check' value='".$valx['id']."'></td>";
                      echo "</tr>";
                    } ?>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="form-group row">
              <div class="col-md-8">
                <button type="button" class="btn btn-success" style='min-width:100px; float:right;' name="download" id="download">Download</button>
              </div>
            </div>
          </div>
        </div>

        <div class="box box-info">
          <div class="box-header">
        		<h3 class="box-title">Shipping Schedule</h3>
        	</div>
          <div class="box-body">
            <div id='tmp_cal'></div>
          </div>
        </div>

        <div class="box box-warning">
          <div class="box-header">
        		<h3 class="box-title">Container Stuffing</h3>
        	</div>
          <div class="box-body">
            <div id='tmp_cal2'></div>
          </div>
        </div>

    </div>
  </div>
</div>



</form>

<!-- modal -->
	<div class="modal fade" id="ModalView2" style='overflow-y: auto;'>
		<div class="modal-dialog"  style='width:80%; '>
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="head_title2"></h4>
					</div>
					<div class="modal-body" id="view2">
					</div>
					<div class="modal-footer">
					<!--<button type="button" class="btn btn-primary">Save</button>-->
					<button type="button" class="btn btn-default " data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
	<!-- modal -->

<style media="screen">
  /* JUST COMMON TABLE STYLES... */
  .table { border-collapse: collapse; width: 100%; }
  .td { background: #fff; padding: 8px 16px; }

  .tableFixHead {
    overflow: auto;
    height: 300px;
    position: sticky;
    top: 0;
  }

  /* td:first-child, th:first-child {
    position:sticky;
    left:0;
    z-index: 9999;
    background-color:grey;
  } */

  .thead .th {
    position: sticky;
    top: 0;
    z-index: 9999;
  	background: #0073b7;
  }

  .datepicker{
    cursor: pointer;
  }

  .long{
    vertical-align: middle;
  }

  .headcol{
    /* font-weight: bold; */
    vertical-align: middle;
    background-color: #aeaeae;
  }


</style>
<!-- page script -->
<script type="text/javascript">

  $(document).ready(function(){
    $('.datepicker').daterangepicker({
			showDropdowns: true,
			autoUpdateInput: false,
			locale: {
				cancelLabel: 'Clear'
			}
		});

    $(document).on('click', '#edit_temp', function(e){
  		e.preventDefault();
  		$("#head_title2").html("<b>EDIT TEMP PRINT PACKING LIST</b>");
  		$.ajax({
  			type:'POST',
  			url: base_url + active_controller+'modal_temp_packing_list',
  			success:function(data){
  				$("#ModalView2").modal();
  				$("#view2").html(data);

  			},
  			error: function() {
  				swal({
  				  title				: "Error Message !",
  				  text				: 'Connection Timed Out ...',
  				  type				: "warning",
  				  timer				: 5000,
  				  showCancelButton	: false,
  				  showConfirmButton	: false,
  				  allowOutsideClick	: false
  				});
  			}
  		});
  	});

    $(document).on('click', '#tampilkan', function(e){
      e.preventDefault();
      var no_so2	= $('#no_so').val();

      if(no_so2 == null ){
        swal({
          title	: "Error Message!",
          text	: 'Sales Order is empty, select first ...',
          type	: "warning"
        });
        return false;
      }
      var no_so = no_so2.toString().split(",").join("-");
      $.ajax({
				url: base_url + active_controller+'/get_packing',
        type: "POST",
				cache: false,
        data: {
  				"no_so" : no_so
  			},
				dataType: "json",
				success: function(data){
					$("#tmp_cal").html(data.header);
          $("#tmp_cal2").html(data.header2);
          $("#totalm3").val(data.total_cub);
          $("#total_karton").val(data.total_box);
          get_kapasitas(data.total_cub);
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

    $(document).on('click', '#download', function(e){
      e.preventDefault();
      var no_so2	= $('#no_so').val();

      var radio_val	= $("input[type='radio']:checked").val();

      if(no_so2 == null ){
        swal({
          title	: "Error Message!",
          text	: 'SO Number is empty, select first ...',
          type	: "warning"
        });
        return false;
      }
      var no_so = no_so2.toString().split(",").join("-");
      if(radio_val == null ){
        swal({
          title	: "Error Message!",
          text	: 'Option container is empty, select first ...',
          type	: "warning"
        });
        return false;
      }

      // alert(radio_val);
      // return false;

      var Link	= base_url + active_controller +'print_packing_list/'+radio_val+'/'+no_so;
		  window.open(Link);

		});

    $(document).on('click', '#edit_temp_print', function(e){
  		e.preventDefault();

      swal({
  			title: "Are you sure?",
  			text: "You will not be able to process again this data!",
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

  				var formData  	= new FormData($('#form_temp_print')[0]);
  				$.ajax({
  					url			: base_url + active_controller+'/save_temp_packing_list',
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
  							window.location.href = base_url + active_controller + 'packing';
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


  function get_kapasitas(nilai){
    $(".persenan").each(function() {
      var get_id 		= $(this).attr('id');
      var split_id	= get_id.split('_');
      var id 		    = split_id[1];
      var cub       = Number(nilai.split(",").join("")) / Number($(this).html().split(",").join("")) * 100;

      // console.log(Number($(this).html().split(",").join("")));
      // console.log(Number(nilai.split(",").join("")));
      // console.log(cub);

      $('#efek_'+id).html(cub.toFixed() +' %');
    });
  }
</script>
