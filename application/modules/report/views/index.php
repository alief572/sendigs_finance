
 <div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post"><br>
      <div class="form-group row">
				<div class="col-md-2">
					<label for="customer">Sales Order</label>
				</div>
				<div class="col-md-4">
          <select id="sales_order" name="sales_order" class="form-control input-md chosen-select">
						<option value="0">Select An Option</option>
						<?php
              foreach (get_sales_order() AS $val => $valx){
  						   echo "<option value='".$valx['no_so']."'>".$valx['no_so']."  [".date('d-m-Y', strtotime($valx['delivery_date']))."]  [".strtoupper($valx['name_customer'])."]</option>";
  						}
            ?>
					</select>
				</div>
        <div class="col-md-2">
          <button type='button' class="btn btn-sm btn-success" id='excel_report'>
    				<i class="fa fa-print"></i> Download Excel
    			</button>
				</div>
      </div>

      <br>
      <div id="tmp_plan">

      </div>
  </form>
	</div>
</div>


<script src="<?= base_url('assets/js/jquery.maskMoney.js')?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>
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

  td:first-child, th:first-child, td:nth-child(2), th:nth-child(2) {
    position:sticky;
    left:0;
    z-index: 9999;
    background-color:grey;
  }

  .thead .th {
    position: sticky;
    top: 0;
    z-index: 9999;
  	background: #0073b7;
  }

  .tfoot .td {
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
    font-weight: bold;
    vertical-align: middle;
  }
</style>
<script type="text/javascript">
	//$('#input-kendaraan').hide();
	var base_url			= '<?php echo base_url(); ?>';
	var active_controller	= '<?php echo($this->uri->segment(1)); ?>';

	$(document).ready(function(){
		$('.chosen-select').select2();

    $(document).on('click', '#excel_report', function(e){
			// loading_spinner();
      e.preventDefault();
      var sales_order		        = $('#sales_order').val();
      if(sales_order == '0' ){
        swal({
          title	: "Error Message!",
          text	: 'Sales order empty, select first ...',
          type	: "warning"
        });
        return false;
      }

      var Link	= base_url + active_controller +'/excel_report/'+sales_order;
		  window.open(Link);

		});

    $(document).on('change', '#sales_order', function(e){
			// loading_spinner();
      e.preventDefault();
      var sales_order		        = $('#sales_order').val();

      if(sales_order == '0' ){
        swal({
          title	: "Error Message!",
          text	: 'Sales order empty, select first ...',
          type	: "warning"
        });
        return false;
      }

			$.ajax({
				url: base_url + active_controller+'/get_planning/'+sales_order,
				cache: false,
				type: "POST",
				dataType: "json",
				success: function(data){
					$("#tmp_plan").html(data.header);
					$('.chosen_select').select2({width: '100%'});
					$('.maskM').maskMoney();
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

  });

</script>
