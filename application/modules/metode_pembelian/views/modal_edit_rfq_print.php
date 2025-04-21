
<div class="box-body"> 
	<br>
	<input type='hidden' name='no_rfq' value='<?=$data[0]->no_rfq;?>'>
	<div class='form-group row'>
		<label class='label-control col-sm-3'><b>Incoterms</b></label>
		<div class='col-sm-9'>
			<?php
			 echo form_input(array('id'=>'incoterms','name'=>'incoterms','class'=>'form-control input-md','placeholder'=>'Incoterms'), strtoupper($data[0]->incoterms));
			?>
		</div>
	</div>
	<div class='form-group row'>
		<label class='label-control col-sm-3'><b>Term Of Payment</b></label>
		<div class='col-sm-9'>
			<?php
			 echo form_input(array('id'=>'top','name'=>'top','class'=>'form-control input-md','placeholder'=>'Term Of Payment'), strtoupper($data[0]->top));
			?>
		</div>
	</div>
	<div class='form-group row'>
		<label class='label-control col-sm-3'><b>Remarks</b></label>
		<div class='col-sm-9'>
			<?php
			 echo form_textarea(array('id'=>'remarks','name'=>'remarks','class'=>'form-control input-md','rows'=>'2','cols'=>'75','placeholder'=>'Remarks'), strtoupper($data[0]->remarks));
			?>
		</div>
	</div>
	<div class='form-group row'>
		<label class='label-control col-sm-3'></label>
		<div class='col-sm-9'>
			<?php
				echo form_button(array('type'=>'button','class'=>'btn btn-md btn-primary','value'=>'save','content'=>'Save','id'=>'edit_po'));
			?>
		</div>
	</div>
</div>
<style>
	.datepicker{
		cursor: pointer;
	}
</style>
<script>
	$(document).ready(function(){
		swal.close();
		$('.datepicker').datepicker();
		$('.maskM').maskMoney();
	});
</script>