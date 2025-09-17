<?php
$this->load->view('include/side_menu');
//echo"<pre>";print_r($data_menu);
?>
<form action="#" method="POST" id="form_proses_bro">
	<div class="box box-primary">
		<div class="box-header">
			<h3 class="box-title"><?= $title; ?></h3>
		</div>
		<!-- /.box-header -->
		<div class="box-body">
			<div class='form-group row'>
				<label class='label-control col-sm-2'><b>Employee ID <span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-user"></i></span>
						<?php
						echo form_input(array('readonly' => 'readonly', 'id' => 'id', 'name' => 'id', 'class' => 'form-control input-sm', 'autocomplete' => 'off', 'placeholder' => 'Automatic'));
						?>
					</div>

				</div>
				<label class='label-control col-sm-2'><b>Employee Name <span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-user"></i></span>
						<?php
						echo form_input(array('id' => 'name', 'name' => 'name', 'class' => 'form-control input-sm', 'autocomplete' => 'off', 'placeholder' => 'Employee Name'));
						?>
					</div>

				</div>
			</div>
			<div class='form-group row'>
				<label class='label-control col-sm-2'><b>NIK<span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-user"></i></span>
						<?php
						echo form_input(array('id' => 'nik', 'name' => 'nik', 'class' => 'form-control input-sm', 'autocomplete' => 'off', 'placeholder' => 'Automatic'));
						?>
					</div>

				</div>
				<label class='label-control col-sm-2'><b>Company Name<span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-file"></i></span>
						<?php
						$data_companies[0]	= 'Select An Option';
						echo form_dropdown('company_id', $data_companies, '0', array('id' => 'company_id', 'class' => 'form-control input-sm'));
						?>
					</div>
				</div>
			</div>
			<div class='form-group row'>
				<label class='label-control col-sm-2'><b>Division Name<span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-file"></i></span>
						<?php
						$data_div[0]	= 'Select An Option';
						echo form_dropdown('division_id', $data_div, '0', array('id' => 'division_id', 'class' => 'form-control input-sm'));
						?>
					</div>
				</div>
				<label class='label-control col-sm-2'><b>Departement Name<span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-file"></i></span>
						<?php
						$data_depart[0]	= 'Select An Option';
						echo form_dropdown('department_id', $data_depart, '0', array('id' => 'department_id', 'class' => 'form-control input-sm'));
						?>
					</div>
				</div>
			</div>
			<div class='form-group row'>
				<label class='label-control col-sm-2'><b>Title Name<span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-file"></i></span>
						<?php
						$data_tit[0]	= 'Select An Option';
						echo form_dropdown('title_id', $data_tit, '0', array('id' => 'title_id', 'class' => 'form-control input-sm'));
						?>
					</div>
				</div>
				<label class='label-control col-sm-2'><b>Position Name<span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-file"></i></span>
						<?php
						$data_position[0]	= 'Select An Option';
						echo form_dropdown('position_id', $data_position, '0', array('id' => 'position_id', 'class' => 'form-control input-sm'));
						?>
					</div>
				</div>
			</div>

			<!-- add by Hikmat / 18-07-2021 -->
			<div class="form-group row">
				<label class='label-control col-sm-2'><b>Place Of Birth <span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-file"></i></span>
						<?php
						echo form_input(array('id' => 'hometown', 'name' => 'hometown', 'class' => 'form-control input-sm', 'autocomplete' => 'off', 'placeholder' => 'Hometown'));
						?>
					</div>

				</div>
				<label class='label-control col-sm-2'><b>Division Head<span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-file"></i></span>
						<select name="division_head" id="divisions_head_id" class="form-control input-sm">
							<option value=""></option>
							<?php foreach ($data_divisions_head as $div => $val) : ?>
								<option value="<?= $div; ?>"><?= $val; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
			</div>

			<div class='form-group row'>
				<label class='label-control col-sm-2'><b>Date Of Birth <span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
						<?php
						echo form_input(array('id' => 'birthday', 'name' => 'birthday', 'class' => 'form-control input-sm', 'autocomplete' => 'off', 'placeholder' => 'Birthday'));
						?>
					</div>
				</div>
				<label class='label-control col-sm-2'><b>Email <span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-envelope"></i></span>
						<?php
						echo form_input(array('id' => 'email', 'name' => 'email', 'class' => 'form-control input-sm', 'autocomplete' => 'off', 'placeholder' => 'Email'));
						?>
					</div>
				</div>
			</div>
			<!-- end add -->

			<div class='form-group row'>
				<label class='label-control col-sm-2'><b>Place Of Birth <span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-file"></i></span>
						<?php
						echo form_input(array('id' => 'hometown', 'name' => 'hometown', 'class' => 'form-control input-sm', 'autocomplete' => 'off', 'placeholder' => 'Place Of Birth'));
						?>
					</div>
				</div>
				<label class='label-control col-sm-2'><b>Date of Birth <span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
						<?php
						echo form_input(array('id' => 'birthday', 'name' => 'birthday', 'class' => 'form-control input-sm', 'autocomplete' => 'off', 'placeholder' => 'Date of Birth'));

						?>
					</div>

				</div>
			</div>
			<div class='form-group row'>
				<label class='label-control col-sm-2'><b>Religion <span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<div class='input-group'>
						<span class='input-group-addon'><i class='fa fa-user'></i></span>
						<?php
						$relid[0]	= 'Select An Option';
						$relid[1]	= 'Islam';
						$relid[2]	= 'Katolik';
						$relid[3]	= 'Kristen';
						$relid[4]	= 'Hindu';
						$relid[5]	= 'Budha';
						$relid[6]	= 'Kong Hu Chu';
						echo form_dropdown('relid', $relid, '0', array('id' => 'relid', 'class' => 'form-control input-sm'));
						?>
					</div>
				</div>
				<label class='label-control col-sm-2'><b>Gender<span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<?php

					$data = array(
						'name'          => 'genderid',
						'id'            => 'genderid',
						'checked'       => 'checked',
						'value'         => 'L',
						'class'         => 'input-sm'
					);

					echo form_radio($data) . '&nbsp;&nbsp;Male';

					?>
					<?php
					$data = array(
						'name'          => 'genderid',
						'id'            => 'genderid',
						'value'         => 'P',
						'class'         => 'input-sm'
					);

					echo form_radio($data) . '&nbsp;&nbsp;Female';

					?>
				</div>
			</div>
			<div class='form-group row'>
				<label class='label-control col-sm-2'><b>Stay Address<span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-book"></i></span>
						<?php
						echo form_textarea(array('cols' => '40', 'rows' => '3', 'id' => 'address', 'name' => 'address', 'class' => 'form-control input-sm', 'autocomplete' => 'off', 'placeholder' => 'Address'));
						?>
					</div>

				</div>
				<label class='label-control col-sm-2'><b>City <span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-file"></i></span>
						<?php
						echo form_input(array('id' => 'city', 'name' => 'city', 'class' => 'form-control input-sm', 'autocomplete' => 'off', 'placeholder' => 'City'));
						?>
					</div>

				</div>
			</div>

			<div class='form-group row'>
				<label class='label-control col-sm-2'><b>Province<span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-file"></i></span>
						<?php
						echo form_input(array('id' => 'province', 'name' => 'province', 'class' => 'form-control input-sm', 'autocomplete' => 'off', 'placeholder' => 'Province'));
						?>
					</div>

				</div>
				<label class='label-control col-sm-2'><b>Post Code <span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-file"></i></span>
						<?php
						echo form_input(array('id' => 'postcode', 'name' => 'postcode', 'class' => 'form-control input-sm', 'autocomplete' => 'off', 'placeholder' => 'Post code'));
						?>
					</div>
				</div>
			</div>
			<div class='form-group row'>
				<label class='label-control col-sm-2'><b>Nationality<span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<div class="input-group">
						<?php

						$data = array(
							'name'          => 'nationality',
							'id'            => 'nationality',
							'checked'       => 'checked',
							'value'         => 'WNI',
							'class'         => 'input-sm'
						);

						echo form_radio($data) . '&nbsp;&nbsp;WNI';

						?>
						<?php
						$data = array(
							'name'          => 'nationality',
							'id'            => 'nationality',
							'value'         => 'WNA',
							'class'         => 'input-sm'
						);

						echo form_radio($data) . '&nbsp;&nbsp;WNA';

						?>
					</div>

				</div>

				<label class='label-control col-sm-2'><b>KTP <span class='text-red'></span></b></label>
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-file"></i></span>
						<?php
						echo form_input(array('id' => 'licensid', 'name' => 'licensid', 'class' => 'form-control input-sm', 'autocomplete' => 'off', 'placeholder' => 'KTP'));
						?>
					</div>
				</div>
			</div>
			<div class='form-group row'>
				<label class='label-control col-sm-2'><b>IdCard Address<span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-book"></i></span>
						<?php
						echo form_textarea(array('cols' => '40', 'rows' => '3', 'id' => 'idcard_address', 'name' => 'idcard_address', 'class' => 'form-control input-sm', 'autocomplete' => 'off', 'placeholder' => 'IdCard Address'));
						?>
					</div>

				</div>
				<label class='label-control col-sm-2'><b>NPWP <span class='text-red'></span></b></label>
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-file"></i></span>
						<?php
						echo form_input(array('id' => 'taxid', 'name' => 'taxid', 'class' => 'form-control input-sm', 'autocomplete' => 'off', 'placeholder' => 'NPWP'));
						?>
					</div>

				</div>
			</div>
			<div class='form-group row'>
				<label class='label-control col-sm-2'><b>Handphone Number <span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-phone"></i></span>
						<?php
						echo form_input(array('id' => 'hp', 'name' => 'hp', 'class' => 'form-control input-sm', 'autocomplete' => 'off', 'placeholder' => 'Handphone Number'));
						?>
					</div>
				</div>
				<label class='label-control col-sm-2'><b>Phone Number<span class='text-red'></span></b></label>
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-phone"></i></span>
						<?php
						echo form_input(array('id' => 'phone', 'name' => 'phone', 'class' => 'form-control input-sm', 'autocomplete' => 'off', 'placeholder' => 'Phone Number'));
						?>
					</div>
				</div>

			</div>
			<div class='form-group row'>
				<label class='label-control col-sm-2'><b>Hire Date<span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
						<?php
						echo form_input(array('id' => 'hiredate', 'name' => 'hiredate', 'class' => 'form-control input-sm', 'autocomplete' => 'off', 'placeholder' => 'Hire Date'));
						?>
					</div>
				</div>
				<label class='label-control col-sm-2'><b>Marital Status<span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-file"></i></span>
						<?php
						$data_marital[0]	= 'Select An Option';
						echo form_dropdown('marital_status', $data_marital, '0', array('id' => 'marital_status', 'class' => 'form-control input-sm'));
						?>
					</div>
				</div>
			</div>

			<div class='form-group row'>

				<label class='label-control col-sm-2'><b>Bank Id<span class='text-red'></span></b></label>
				<div class='col-sm-4'>
					<div class="input-group">
						<?php

						$data = array(
							'name'          => 'bank_id',
							'id'            => 'bank_id',
							'checked'       => 'checked',
							'value'         => 'BCA',
							'class'         => 'input-sm'
						);

						echo form_radio($data) . '&nbsp;&nbsp;BCA';

						?>
						<?php
						$data = array(
							'name'          => 'bank_id',
							'id'            => 'bank_id',
							'value'         => 'BJB',
							'class'         => 'input-sm'
						);

						echo form_radio($data) . '&nbsp;&nbsp;BJB';

						?>

					</div>

				</div>
				<label class='label-control col-sm-2'><b>Account Number<span class='text-red'></span></b></label>
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
						<?php
						echo form_input(array('id' => 'accnumber', 'name' => 'accnumber', 'class' => 'form-control input-sm', 'autocomplete' => 'off', 'placeholder' => 'Account Number'));
						?>
					</div>
				</div>
			</div>
			<div class='form-group row'>
				<label class='label-control col-sm-2'><b>Account Name<span class='text-red'></span></b></label>
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
						<?php
						echo form_input(array('id' => 'accname', 'name' => 'accname', 'class' => 'form-control input-sm', 'autocomplete' => 'off', 'placeholder' => 'Account name'));
						?>
					</div>

				</div>
				<label class='label-control col-sm-2'><b>Health Number Of BPJS <span class='text-red'></span></b></label>
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
						<?php
						echo form_input(array('id' => 'bpjs_kes', 'name' => 'bpjs_kes', 'class' => 'form-control input-sm', 'autocomplete' => 'off', 'placeholder' => 'Health Number Of BPJS'));
						?>
					</div>
				</div>

			</div>
			<div class='form-group row'>

				<label class='label-control col-sm-2'><b>Employee Number Of BPJS <span class='text-red'></span></b></label>
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
						<?php
						echo form_input(array('id' => 'bpjs_ket', 'name' => 'bpjs_ket', 'class' => 'form-control input-sm', 'autocomplete' => 'off', 'placeholder' => 'Employee Number Of BPJS'));
						?>
					</div>

				</div>
				<label class='label-control col-sm-2'><b>Tax Marital Status<span class='text-red'></span></b></label>
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-file"></i></span>
						<?php
						$data_marital[0]	= 'Select An Option';
						echo form_dropdown('tax_marital_status', $data_marital, '0', array('id' => 'tax_marital_status', 'class' => 'form-control input-sm'));
						?>
					</div>
				</div>
			</div>

			<div class='form-group row'>
				<label class='label-control col-sm-2'><b>Blood Group <span class='text-red'></span></b></label>
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-tint"></i></span>
						<?php
						$blood['']	= 'Select An Option';
						$blood['O']	= 'O';
						$blood['A']	= 'A';
						$blood['B']	= 'B';
						$blood['AB']	= 'AB';
						echo form_dropdown('blood_group', $blood, '0', array('id' => 'blood_group', 'class' => 'form-control input-sm'));
						?>
					</div>

				</div>
				<label class='label-control col-sm-2'><b>Finger Name<span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-file"></i></span>
						<?php
						$data_idfinger[0]	= 'Select An Option';
						echo form_dropdown('finger_id', $data_idfinger, '0', array('id' => 'finger_id', 'class' => 'form-control input-sm'));
						?>
					</div>
				</div>
			</div>
			<div class='form-group row'>
				<label class='label-control col-sm-2'><b>Salary<span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-dollar"></i></span>
						<?php
						echo form_input(array('id' => 'salary', 'name' => 'salary', 'class' => 'form-control input-sm', 'autocomplete' => 'off', 'placeholder' => 'Salary'));
						?>
					</div>
				</div>
				<label class='label-control col-sm-2'><b>Active<span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<?php
					$data = array(
						'name'          => 'flag_active',
						'id'            => 'flag_active',
						'checked'            => 'checked',
						'value'         => 'Y',
						'class'         => 'input-sm'
					);

					echo form_checkbox($data) . '&nbsp;&nbsp;Yes';

					?>

				</div>
			</div>
			<!--<div class='form-group row'>
			<label class='label-control col-sm-2'><b>Jabatan Allowance<span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-dollar"></i></span>              
						<?php
						//echo form_input(array('id'=>'jabatan','name'=>'jabatan','class'=>'form-control input-sm','autocomplete'=>'off','placeholder'=>'Jabatan Allowance'));											
						?>
					</div>
				</div>				
				<label class='label-control col-sm-2'><b>Pulsa Allowance<span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-dollar"></i></span>              
						<?php
						//echo form_input(array('id'=>'pulsa','name'=>'pulsa','class'=>'form-control input-sm','autocomplete'=>'off','placeholder'=>'Pulsa Allowance'));											
						?>
					</div>
				</div>	
			</div>-->
			<!--<div class='form-group row'>			
				<label class='label-control col-sm-2'><b>Image <span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-image"></i></span>              
						<?php
						echo form_input(array('type' => 'file', 'id' => 'image', 'name' => 'image', 'class' => 'form-control input-sm', 'autocomplete' => 'off', 'placeholder' => 'Image'));
						?>
					</div>
							
				</div>
				
				
			</div>-->

			<div class='form-group row'>
				<label class='label-control col-sm-2'><b>Tanda&nbsp (<span class='text-red'>*</span>)&nbsp Wajib Diisi</b></label>
			</div>
		</div>
		<div class="box-body">
			<div class="box box-danger">
				<div class="box-header">
					<h3 class="box-title">
						<i class="fa fa-star"></i> <?php echo ('<span class="important">Family Data</span>'); ?>
					</h3>
					<div class='box-tool pull-right'>
						<?php
						echo form_button(array('type' => 'button', 'class' => 'btn btn-md btn-success', 'value' => 'back', 'content' => 'Add Family', 'id' => 'add-family'));
						?>
					</div>
				</div>
				<div class="clearfix box-body">
					<table class='table table-bordered table-striped'>
						<thead>
							<tr class='bg-blue'>
								<td align='center'><b>Name</b></td>
								<td align='center'><b>Place Of Birth</b></td>
								<td align='center'><b>Date Of Birth</b></td>
								<td align='center'><b>Relationship</b></td>
								<td align='center'><b>Action</b></td>
							</tr>

						</thead>
						<tbody id='list_family'>

						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="box-body">
			<div class="box box-danger">
				<div class="box-header">
					<h3 class="box-title">
						<i class="fa fa-star"></i> <?php echo ('<span class="important">Education History</span>'); ?>
					</h3>
					<div class='box-tool pull-right'>
						<?php
						echo form_button(array('type' => 'button', 'class' => 'btn btn-md btn-success', 'value' => 'back', 'content' => 'Add Education', 'id' => 'add-education'));
						?>
					</div>
				</div>
				<div class="clearfix box-body">
					<table class='table table-bordered table-striped'>
						<thead>
							<tr class='bg-blue'>
								<td align='center'><b>Level</b></td>
								<td align='center'><b>Institution Name</b></td>
								<td align='center'><b>Graduate Year</b></td>
								<td align='center'><b>Action</b></td>
							</tr>

						</thead>
						<tbody id='list_education'>

						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class='box-footer'>
			<?php
			echo form_button(array('type' => 'button', 'class' => 'btn btn-md btn-primary', 'value' => 'save', 'content' => 'Save', 'id' => 'simpan-com')) . ' ';
			echo form_button(array('type' => 'button', 'class' => 'btn btn-md btn-danger', 'value' => 'back', 'content' => 'Back', 'onClick' => 'javascript:back()'));
			?>
		</div>
		<!-- /.box-body -->
	</div>
	<!-- /.box -->
</form>

<?php $this->load->view('include/footer'); ?>
<style>
	.chosen-container {
		width: 100% !important;
	}
</style>
<script>
	var data_delv = <?php echo json_encode($family_type); ?>;
	var data_edu = <?php echo json_encode($education_type); ?>;
	$(document).ready(function() {
		$('#add-family').click(function() {
			var total = $('#list_family').find('tr').length;
			if (total == 0 || total == null) {
				var ada = 0;
				var loop = 1;
			} else {
				var nil = $('#list_family tr:last').attr('id');
				var jum = nil.split('_');
				var loop = parseInt(jum[1]) + 1;
			}

			Template = '<tr id="tr_' + loop + '">';
			Template += '<td align="left">';
			Template += '<input type="text" class="form-control input-sm" name="det_Family[' + loop + '][name]" id="det_Family_' + loop + '_name" label="FALSE" div="FALSE">';
			Template += '</td>';
			Template += '<td align="left">';
			Template += '<input type="text" class="form-control input-sm" name="det_Family[' + loop + '][birth_place]" id="det_Family_' + loop + '_place" label="FALSE" div="FALSE">';
			Template += '</td>';
			Template += '<td align="left">';
			Template += '<input type="text" class="form-control input-sm" name="det_Family[' + loop + '][birth_date]" id="det_Family_' + loop + '_birthday" label="FALSE" div="FALSE" readOnly="true" data-role="tanggal">';
			Template += '</td>';
			Template += '<td align="center">';
			Template += '<select name="det_Family[' + loop + '][category]" id="det_Family_' + loop + '_category" class="form-control input-sm chosen-select">';
			Template += '<option value="">Select An Option</option>';
			if (!$.isEmptyObject(data_delv)) {
				$.each(data_delv, function(key, value) {
					Template += '<option value="' + key + '">' + value + '</option>';
				});
			}

			Template += '</select>';
			Template += '</td>';

			Template += '<td align="center"><button type="button" class="btn btn-sm btn-danger" title="Hapus Data" data-role="qtip" onClick="return DelItem(' + loop + ');"><i class="fa fa-trash-o"></i></button></td>';
			Template += '</tr>';
			$('#list_family').append(Template);
			$('input[data-role="tanggal"]').datepicker({
				dateFormat: 'yy-mm-dd',
				changeMonth: true,
				changeYear: true,
				yearRange: 'c-80:c+100'
			});
			$('#det_Family_' + loop + '_category').chosen();
		});



		$('#add-education').click(function() {
			var jumlah = $('#list_education').find('tr').length;
			if (jumlah == 0 || jumlah == null) {
				var ada1 = 0;
				var loop1 = 1;
			} else {
				var nilai = $('#list_education tr:last').attr('id');
				var jum1 = nil.split('_');
				var loop1 = parseInt(jum1[1]) + 1;
			}

			Template = '<tr id="tr_' + loop1 + '">';
			Template += '<td align="center">';
			Template += '<select name="det_Education[' + loop1 + '][level]" id="det_Education_' + loop1 + '_category" class="form-control input-sm chosen-select">';
			Template += '<option value="">Select An Option</option>';
			if (!$.isEmptyObject(data_edu)) {
				$.each(data_edu, function(key, value) {
					Template += '<option value="' + key + '">' + value + '</option>';
				});
			}

			Template += '</select>';
			Template += '</td>';
			Template += '<td align="left">';
			Template += '<input type="text" class="form-control input-sm" name="det_Education[' + loop1 + '][institution]" id="det_Education_' + loop1 + '_name" label="FALSE" div="FALSE">';
			Template += '</td>';
			Template += '<td align="left">';
			Template += '<input type="text" class="form-control input-sm" name="det_Education[' + loop1 + '][graduated]" id="det_Education_' + loop1 + '_year" label="FALSE" div="FALSE" data-role="tgllulus">';
			Template += '</td>';


			Template += '<td align="center"><button type="button" class="btn btn-sm btn-danger" title="Hapus Data" data-role="qtip" onClick="return DelItem2(' + loop1 + ');"><i class="fa fa-trash-o"></i></button></td>';
			Template += '</tr>';
			$('#list_education').append(Template);
			$('input[data-role="tgllulus"]').datepicker({
				dateFormat: 'yy',
				changeYear: true,
				yearRange: 'c-80:c+100'
			});
			$('#det_Education_' + loop1 + '_category').chosen();
		});

		$("#taxid").mask("99.999.999.9-999-999");

		$('#name').keyup(function() {

			var nama = $('#name').val();

			$("#accname").val(nama);;

		});

		$('#simpan-com').click(function(e) {
			e.preventDefault();
			var nama = $('#name').val();
			var company = $('#company_id').val();
			var divisi = $('#division_id').val();
			var department = $('#department_id').val();
			var title = $('#title_id').val();
			var position = $('#position_id').val();
			var birthday = $('#birthday').val();
			var hometown = $('#hometown').val();
			var relid = $('#relid').val();
			var genderid = $('#genderid').val();
			var address = $('#address').val();
			var city = $('#city').val();
			var province = $('#province').val();
			var postcode = $('#postcode').val();
			var nationality = $('#nationality').val();
			var licensid = $('#licensid').val();
			var idcardaddress = $('#idcard_address').val();
			var taxid = $('#taxid').val();
			var phone = $('#phone').val();
			var handphone = $('#hp').val();
			var citizenid = $('#citizenid').val();
			var marital = $('#marital_status').val();
			var familyno = $('#familyno').val();
			var childs = $('#childs').val();
			var wifehusband = $('#wifehusband_name').val();
			var firstchild = $('#firstchild_name').val();
			var secondchild = $('#secondchild_name').val();
			var thirdchild = $('#thirdchild_name').val();
			var hiredate = $('#hiredate').val();
			var height = $('#height').val();
			var bank = $('#bank_id').val();
			var accnumber = $('#accnumber').val();
			var accname = $('#accname').val();
			var bpjskes = $('#bpjs_kes').val();
			var bpjsket = $('#bpjs_ket').val();
			var flag_active = $('#flag_active').val();




			if (nama == '' || nama == null || nama == '0') {
				swal({
					title: "Error Message!",
					text: 'Empty Employee Name, please input Employee Name first.....',
					type: "warning"
				});

				return false;
			}
			if (company == '' || company == null || company == '0') {
				swal({
					title: "Error Message!",
					text: 'Empty Company Name, please input Company Name first.....',
					type: "warning"
				});

				return false;
			}
			if (divisi == '' || divisi == null || divisi == '0') {
				swal({
					title: "Error Message!",
					text: 'Empty divisi Name, please input divisi Name first.....',
					type: "warning"
				});

				return false;
			}
			if (department == '' || department == null || department == '0') {
				swal({
					title: "Error Message!",
					text: 'Empty department Name, please input department Name first.....',
					type: "warning"
				});

				return false;
			}
			if (title == '' || title == null || title == '0') {
				swal({
					title: "Error Message!",
					text: 'Empty title Name, please input title Name first.....',
					type: "warning"
				});

				return false;
			}
			if (position == '' || position == null || position == '0') {
				swal({
					title: "Error Message!",
					text: 'Empty position Name, please input position Name first.....',
					type: "warning"
				});

				return false;
			}
			if (birthday == '' || birthday == null || birthday == '0') {
				swal({
					title: "Error Message!",
					text: 'Empty birthday, please input birthday first.....',
					type: "warning"
				});

				return false;
			}
			if (hometown == '' || hometown == null || hometown == '0') {
				swal({
					title: "Error Message!",
					text: 'Empty hometown , please input hometown  first.....',
					type: "warning"
				});

				return false;
			}
			if (relid == '' || relid == null || relid == '0') {
				swal({
					title: "Error Message!",
					text: 'Empty relid, please input relid first.....',
					type: "warning"
				});

				return false;
			}
			if (genderid == '' || genderid == null || genderid == '0') {
				swal({
					title: "Error Message!",
					text: 'Empty genderid, please input genderid first.....',
					type: "warning"
				});

				return false;
			}
			if (address == '' || address == null || address == '0') {
				swal({
					title: "Error Message!",
					text: 'Empty address, please input address first.....',
					type: "warning"
				});

				return false;
			}
			if (city == '' || city == null || city == '0') {
				swal({
					title: "Error Message!",
					text: 'Empty city, please input city first.....',
					type: "warning"
				});

				return false;
			}
			if (province == '' || province == null || province == '0') {
				swal({
					title: "Error Message!",
					text: 'Empty province, please input province first.....',
					type: "warning"
				});

				return false;
			}
			if (postcode == '' || postcode == null || postcode == '0') {
				swal({
					title: "Error Message!",
					text: 'Empty postcode, please input postcode first.....',
					type: "warning"
				});

				return false;
			}
			if (nationality == '' || nationality == null || nationality == '0') {
				swal({
					title: "Error Message!",
					text: 'Empty nationality, please input nationality first.....',
					type: "warning"
				});

				return false;
			}
			if (licensid == '' || licensid == null || licensid == '0') {
				swal({
					title: "Error Message!",
					text: 'Empty licensid, please input licensid first.....',
					type: "warning"
				});

				return false;
			}
			if (idcardaddress == '' || idcardaddress == null || idcardaddress == '0') {
				swal({
					title: "Error Message!",
					text: 'Empty idcard address, please input idcard address first.....',
					type: "warning"
				});

				return false;
			}


			if (phone == '' || phone == null || phone == '0') {
				swal({
					title: "Error Message!",
					text: 'Empty phone, please input phone first.....',
					type: "warning"
				});

				return false;
			}

			if (handphone == '' || handphone == null || handphone == '0') {
				swal({
					title: "Error Message!",
					text: 'Empty handphone, please input handphone first.....',
					type: "warning"
				});

				return false;
			}

			if (marital == '' || marital == null || marital == '0') {
				swal({
					title: "Error Message!",
					text: 'Empty marital Status, please input marital status first.....',
					type: "warning"
				});

				return false;
			}

			if (hiredate == '' || hiredate == null || hiredate == '0') {
				swal({
					title: "Error Message!",
					text: 'Empty hiredate, please input hiredate first.....',
					type: "warning"
				});

				return false;
			}
			var total = $('#list_family').find('tr').length;
			if (parseInt(total) > 0) {
				var intL = 0;
				var intN = 0;
				var intD = 0;
				var intC = 0;
				$('#list_family').find('tr').each(function() {
					var nil = $(this).attr('id');
					var jum = nil.split('_');
					var nama_family = $('#det_Family_' + jum[1] + '_name').val();
					var lahir = $('#det_Family_' + jum[1] + '_place').val();
					var tanggal = $('#det_Family_' + jum[1] + '_birthday').val();
					var category = $('#det_Family_' + jum[1] + '_category').val();
					if (nama_family == '' || nama_family == null || nama_family == '-' || nama_family == '0') {
						intN++;
					}

					if (lahir == '' || lahir == null || lahir == '-' || lahir == '0') {
						intL++;
					}

					if (tanggal == '' || tanggal == null) {
						intD++;
					}

					if (category == '' || category == null) {
						intC++;
					}
				});
				if (intN > 0) {
					swal({
						title: "Error Message!",
						text: 'Empty Family Name. Please Input Family Name First.....',
						type: "warning"
					});

					return false;
				}

				/*if(intL > 0){
					swal({
					  title	: "Error Message!",
					  text	: 'Empty Birth Of Place. Please Input Birth Of Place First.....',
					  type	: "warning"
					});
					
					return false;
				}
				
				if(intD > 0){
					swal({
					  title	: "Error Message!",
					  text	: 'Empty Birth Of Date. Please Input Birth Of Date First.....',
					  type	: "warning"
					});
					
					return false;
				}*/

				if (intC > 0) {
					swal({
						title: "Error Message!",
						text: 'Empty Family Relationship. Please Choose Family Relationship First.....',
						type: "warning"
					});

					return false;
				}
			}

			var jumlah = $('#list_education').find('tr').length;
			if (parseInt(jumlah) > 0) {
				var int1 = 0;
				var int2 = 0;
				var int3 = 0;
				$('#list_education').find('tr').each(function() {
					var value = $(this).attr('id');
					var jum = value.split('_');
					var nama_edu = $('#det_Education_' + jum[1] + '_name').val();
					var lulus = $('#det_Education_' + jum[1] + '_year').val();
					var category2 = $('#det_Education_' + jum[1] + '_category').val();

					if (nama_edu == '' || nama_edu == null || nama_edu == '-' || nama_edu == '0') {
						int2++;
					}

					if (lulus == '' || lulus == null || lulus == '-' || lulus == '0') {
						int3++;
					}

					if (category2 == '' || category2 == null) {
						int1++;
					}
				});

				if (int2 > 0) {
					swal({
						title: "Error Message!",
						text: 'Empty Institution Name. Please Input Institution Name First.....',
						type: "warning"
					});

					return false;
				}
				if (int3 > 0) {
					swal({
						title: "Error Message!",
						text: 'Empty Year Graduated. Please Input Year Graduated First.....',
						type: "warning"
					});

					return false;
				}
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
						loading_spinner();
						var formData = new FormData($('#form_proses_bro')[0]);
						var baseurl = base_url + active_controller + '/add';
						$.ajax({
							url: baseurl,
							type: "POST",
							data: formData,
							cache: false,
							dataType: 'json',
							processData: false,
							contentType: false,
							success: function(data) {
								if (data.status == 1) {
									swal({
										title: "Save Success!",
										text: data.pesan,
										type: "success",
										timer: 7000,
										showCancelButton: false,
										showConfirmButton: false,
										allowOutsideClick: false
									});
									window.location.href = base_url + active_controller;
								} else {

									if (data.status == 2) {
										swal({
											title: "Save Failed!",
											text: data.pesan,
											type: "warning",
											timer: 7000,
											showCancelButton: false,
											showConfirmButton: false,
											allowOutsideClick: false
										});
									} else {
										swal({
											title: "Save Failed!",
											text: data.pesan,
											type: "warning",
											timer: 7000,
											showCancelButton: false,
											showConfirmButton: false,
											allowOutsideClick: false
										});
									}

								}
							},
							error: function() {

								swal({
									title: "Error Message !",
									text: 'An Error Occured During Process. Please try again..',
									type: "warning",
									timer: 7000,
									showCancelButton: false,
									showConfirmButton: false,
									allowOutsideClick: false
								});
							}
						});
					} else {
						swal("Cancelled", "Data can be process again :)", "error");
						return false;
					}
				});
		});
		$('#company_id').change(function() {
			var comp = $('#company_id').val();
			if (comp == '' || comp == '0' || comp == null) {
				var Template = '<option value="">Empty List</option>';
				$('#division_id').html(Template).trigger('chosen:updated');
			} else {
				var baseurl = base_url + active_controller + '/getDetail/' + comp;
				$.ajax({
					url: baseurl,
					type: "GET",
					success: function(data) {
						var datas = $.parseJSON(data);
						if ($.isEmptyObject(datas) == true) {
							var Template = '<option value="">Empty List</option>';
						} else {
							var Template = '<option value="">Select An Option</option>';
							$.each(datas, function(kode, nilai) {
								Template += '<option value="' + kode + '">' + nilai + '</option>';
							});
						}
						$('#division_id').html(Template).trigger('chosen:updated');
					},
					error: function() {

						swal({
							title: "Error Message !",
							text: 'An Error Occured During Process. Please try again..',
							type: "warning",
							timer: 7000,
							showCancelButton: false,
							showConfirmButton: false,
							allowOutsideClick: false
						});
					}
				});
			}

		});
		$('#division_id').change(function() {
			var comp = $('#division_id').val();
			if (comp == '' || comp == '0' || comp == null) {
				var Template = '<option value="">Empty List</option>';
				$('#division_id').html(Template).trigger('chosen:updated');
			} else {
				var baseurl = base_url + active_controller + '/getDept/' + comp;
				$.ajax({
					url: baseurl,
					type: "GET",
					success: function(data) {
						var datas = $.parseJSON(data);
						if ($.isEmptyObject(datas) == true) {
							var Template = '<option value="">Empty List</option>';
						} else {
							var Template = '<option value="">Select An Option</option>';
							$.each(datas, function(kode, nilai) {
								Template += '<option value="' + kode + '">' + nilai + '</option>';
							});
						}
						$('#department_id').html(Template).trigger('chosen:updated');
					},
					error: function() {

						swal({
							title: "Error Message !",
							text: 'An Error Occured During Process. Please try again..',
							type: "warning",
							timer: 7000,
							showCancelButton: false,
							showConfirmButton: false,
							allowOutsideClick: false
						});
					}
				});
			}

		});
		$('#department_id').change(function() {
			var comp = $('#department_id').val();
			if (comp == '' || comp == '0' || comp == null) {
				var Template = '<option value="">Empty List</option>';
				$('#department_id').html(Template).trigger('chosen:updated');
			} else {
				var baseurl = base_url + active_controller + '/getTitle/' + comp;
				$.ajax({
					url: baseurl,
					type: "GET",
					success: function(data) {
						var datas = $.parseJSON(data);
						if ($.isEmptyObject(datas) == true) {
							var Template = '<option value="">Empty List</option>';
						} else {
							var Template = '<option value="">Select An Option</option>';
							$.each(datas, function(kode, nilai) {
								Template += '<option value="' + kode + '">' + nilai + '</option>';
							});
						}
						$('#title_id').html(Template).trigger('chosen:updated');
					},
					error: function() {

						swal({
							title: "Error Message !",
							text: 'An Error Occured During Process. Please try again..',
							type: "warning",
							timer: 7000,
							showCancelButton: false,
							showConfirmButton: false,
							allowOutsideClick: false
						});
					}
				});
			}

		});
		$(function() {
			// Daterange Picker
			$('#hiredate').datepicker({
				dateFormat: 'yy-mm-dd',
				changeMonth: true,
				changeYear: true,
				yearRange: 'c-80:c+100',

			});
		});

	});
</script>
<script>
	$(function() {
		// Daterange Picker
		$('#birthday').datepicker({
			dateFormat: 'yy-mm-dd',
			changeMonth: true,
			changeYear: true,
			yearRange: 'c-80:c+100',

		});
	});

	function DelItem(id) {
		$('#list_family #tr_' + id).remove();

	}

	function DelItem2(id) {
		$('#list_education #tr_' + id).remove();

	}
</script>