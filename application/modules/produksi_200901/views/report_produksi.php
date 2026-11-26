
 <div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post"><br>
      <div class="form-group row">
				<div class="col-md-2">
					<label for="customer">Costcenter</label>
				</div>
				<div class="col-md-4">
          <select id="sales_order" name="sales_order" class="form-control input-md chosen-select">
						<option value="0">Select An Option</option>
						<?php
              foreach (get_sales_order() AS $val => $valx){
  						        echo "<option value='".$valx['no_so']."'>".date('d-m-Y', strtotime($valx['delivery_date']))." - ".strtoupper($valx['name_customer'])."</option>"
  						}
            ?>
					</select>
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
  }

  .tableFixHead .thead .th {
    position: sticky;
    top: 0;
  	background: #0073b7;
  }
  .datepicker{
    cursor: pointer;
  }
</style>
<script type="text/javascript">
	//$('#input-kendaraan').hide();
	var base_url			= '<?php echo base_url(); ?>';
	var active_controller	= '<?php echo($this->uri->segment(1)); ?>';

	$(document).ready(function(){
		$('.chosen-select').select2();

  });

</script>
