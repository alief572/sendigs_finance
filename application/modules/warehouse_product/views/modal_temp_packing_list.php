<form id="form_temp_print" method="post">
  <div class="box-body">
  	<br>
  	<input type='hidden' name='id' value='<?=$data[0]->id;?>'>
  	<div class='form-group row'>
  		<label class='label-control col-sm-3'><b>Prepared by,</b></label>
  		<div class='col-sm-9'>
  			<?php
  			 echo form_input(array('id'=>'prepared_by','name'=>'prepared_by','class'=>'form-control input-md','placeholder'=>'Incoterms'), strtoupper($data[0]->prepared_by));
  			?>
  		</div>
  	</div>
  	<div class='form-group row'>
  		<label class='label-control col-sm-3'><b>Checked by,</b></label>
  		<div class='col-sm-9'>
  			<?php
  			 echo form_input(array('id'=>'checked_by','name'=>'checked_by','class'=>'form-control input-md','placeholder'=>'Term Of Payment'), strtoupper($data[0]->checked_by));
  			?>
  		</div>
  	</div>
    <div class='form-group row'>
  		<label class='label-control col-sm-3'><b>Acknowledged by</b></label>
  		<div class='col-sm-9'>
  			<?php
  			 echo form_input(array('id'=>'acknowleged_by','name'=>'acknowleged_by','class'=>'form-control input-md','placeholder'=>'Incoterms'), strtoupper($data[0]->acknowleged_by));
  			?>
  		</div>
  	</div>
    <div class='form-group row'>
  		<label class='label-control col-sm-3'><b>Kota</b></label>
  		<div class='col-sm-9'>
  			<?php
  			 echo form_input(array('id'=>'city','name'=>'city','class'=>'form-control input-md','placeholder'=>'Incoterms'), strtoupper($data[0]->city));
  			?>
  		</div>
  	</div>
  	<div class='form-group row'>
  		<label class='label-control col-sm-3'><b>Keterangan 1</b></label>
  		<div class='col-sm-9'>
  			<?php
  			 echo form_textarea(array('id'=>'ket1','name'=>'ket1','class'=>'form-control input-md','rows'=>'2','cols'=>'75','placeholder'=>'Keterangan 1'), $data[0]->ket1);
  			?>
  		</div>
  	</div>
    <div class='form-group row'>
  		<label class='label-control col-sm-3'><b>Keterangan 2</b></label>
  		<div class='col-sm-9'>
  			<?php
  			 echo form_textarea(array('id'=>'ket2','name'=>'ket2','class'=>'form-control input-md','rows'=>'2','cols'=>'75','placeholder'=>'Keterangan 2'), $data[0]->ket2);
  			?>
  		</div>
  	</div>
  	<div class='form-group row'>
  		<label class='label-control col-sm-3'></label>
  		<div class='col-sm-9'>
  			<?php
  				echo form_button(array('type'=>'button','class'=>'btn btn-md btn-primary','value'=>'save','content'=>'Save','id'=>'edit_temp_print'));
  			?>
  		</div>
  	</div>
  </div>
</form>
