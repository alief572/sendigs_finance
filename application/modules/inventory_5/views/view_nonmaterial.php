<?php
foreach ($results['inven'] as $inven){
$foto = $inven->spec16;
// print_r ($inven);
// exit();
}	
?>
<div class="box box-primary">
	<!-- /.box-header -->
	<div class="box-body">
		<form id="data_form">
		<div class="row">
				<form id="data_form">
		<input type="hidden" name="id_material" id="id_material" value='<?= $inven->id_material ?>'>
		<div class="row">
		<div id="input1">
			<div class="col-md-12">			    
                <div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="inventory_1"> Type</label>
					</div>
					 <div class="col-md-8">
					    <input type="text" class="form-control input-sm" id="spec6"  name="spec6" placeholder="Spesifikasi 6" value="<?= $inven->nama_type ?>" required>
					
					 </div>
				</div>
				</div>
				<!--
				<div class="col-md-5"> 
			    <div class="form-group row">
					<div class="col-md-4">
					  <label for="">Nama Category I</label>
					</div>
					<div class="col-md-8">
					    <input type="text" class="form-control input-sm" id="spec6"  name="spec6" placeholder="Spesifikasi 6" value="<?= $inven->nama_category1 ?>" required>
					
					  </div>
				</div>
				</div>				
				<div class="col-md-5">
			    <div class="form-group row">
					<div class="col-md-4">
					  <label for="">Nama Category II</label>
					</div>
					<div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec6"  name="spec6" placeholder="Spesifikasi 6" value="<?= $inven->nama_category2 ?>" required>
					
					  </div>
				</div>
				</div>	
				<div class="col-md-5">
			    <div class="form-group row">
					<div class="col-md-4">
					  <label for="">Nama Category III</label>
					</div>
					<div class="col-md-8">
					   <input type="text" class="form-control input-sm" id="spec6"  name="spec6" placeholder="Spesifikasi 6" value="<?= $inven->nama_category3 ?>" required>
					
					  </div>
				</div>
				</div>-->
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Nama Material</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="nama"  name="nama" placeholder="Nama Material" value="<?= $inven->nama ?>" required>
					</div>
				</div>
				</div>
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Nama Umum</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec1"  name="spec1" placeholder="Spesifikasi 1" value="<?= $inven->spec1 ?>" required>
					</div>
				</div>
				</div>				
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Brand</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec2"  name="spec2" placeholder="Spesifikasi 2" value="<?= $inven->spec2 ?>" required>
					</div>
				</div>
				</div>
                <!--				
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Material Sejenis</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec3"  name="spec3" placeholder="Spesifikasi 3" value="<?= $inven->spec3 ?>" required>
					</div>
				</div>
				</div>
				
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Hardness</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec4"  name="spec4" placeholder="Spesifikasi 4" value="<?= $inven->spec4 ?>" required>
					</div>
				</div>
				</div>
			    <div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Thickness</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec5"  name="spec5" placeholder="Spesifikasi 5" value="<?= $inven->spec5 ?>" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Length</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec6"  name="spec6" placeholder="Spesifikasi 6" value="<?= $inven->spec6 ?>" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Width</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec7"  name="spec7" placeholder="Spesifikasi 7" value="<?= $inven->spec7 ?>" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Density</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec8"  name="spec8" placeholder="Spesifikasi 8" value="<?= $inven->spec8 ?>" required>
					</div>
				</div>
				</div> 
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Satuan</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec9"  name="spec9" placeholder="Spesifikasi 9" value="<?= $inven->spec9 ?>" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Alt Supplier</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec10"  name="spec10" placeholder="Spesifikasi 10" value="<?= $inven->spec10 ?>" required>
					</div>
				</div>
				</div>  -->
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Safety Stock</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec11"  name="spec11" placeholder="Spesifikasi 11" value="<?= $inven->spec11 ?>" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Order Point</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec12"  name="spec12" placeholder="Spesifikasi 12" value="<?= $inven->spec12 ?>" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Maximum Stock</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec13"  name="spec13" placeholder="Spesifikasi 13" value="<?= $inven->spec13 ?>" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Leadtime</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec14"  name="spec14" placeholder="Spesifikasi 14" value="<?= $inven->spec14 ?>" required>
					</div>
				</div>
				</div>  
				<div class="col-md-5">
				<div class="form-group row">
					<div class="col-md-4">
					  <label for="">Keterangan</label>
					</div>
					 <div class="col-md-8">
					  <input type="text" class="form-control input-sm" id="spec15"  name="spec15" placeholder="Spesifikasi 15" value="<?= $inven->spec15 ?>" required>
					</div>
				</div>
				</div>  
				<div class="col-md-8">
				<h5 class="box-title"><b>Satuan Dan Konversi Satuan</b></h5>
				<table id="example1" border='0' width='100%' class="table table-striped table-bordered table-hover table-condensed">
					<thead id='head_table'>
						<tr class='bg-blue'>
							<th style='text-align:center; vertical-align: middle;' width='5%'>No</th>
							<th style='text-align:center; vertical-align: middle;' width='15%'>Satuan</th>
							<th style='text-align:center; vertical-align: middle;' width='15%'>Nilai Konversi</th>
							<th style='text-align:center; vertical-align: middle;' width='20%'>Satuan Terkecil </th>
						</tr>
					</thead>
					<tbody>
					<?php
						$nopay=0;
						foreach($results['konversi'] AS $pay){
						$nopay++;
							?>
							<tr>
								<td ><?= $nopay;?></td>
								<td ><?= $pay->nama_satuan;?></td>
								<td ><?= $pay->konversi;?></td>
								<td ><?= $pay->satuan_konversi;?></td>
							</tr>
							
							<?php
						}
						
						?>	
					</tbody>
				</table>
				</div>		
				
				<div class="col-md-8">
				<h5 class="box-title"><b>Material Sejenis</b></h5>
				<table id="example1" border='0' width='100%' class="table table-striped table-bordered table-hover table-condensed">
					<thead id='head_table'>
						<tr class='bg-blue'>
							<th style='text-align:center; vertical-align: middle;' width='5%'>No</th>
							<th style='text-align:center; vertical-align: middle;' width='15%'>Material Sejenis</th>
						</tr>
					</thead>
					<tbody>
					<?php
						$nomat=0;
						foreach($results['material'] AS $mat){
						$nomat++;
							?>
							<tr>
								<td ><?= $nomat;?></td>
								<td ><?= $mat->nama_material_sejenis;?></td>
								
							</tr>
							
							<?php
						}
						
						?>	
					</tbody>
				</table>
				</div>		
				
				<div class="col-md-8">
				<h5 class="box-title"><b>Alternative Supplier</b></h5>
				<table id="example1" border='0' width='100%' class="table table-striped table-bordered table-hover table-condensed">
					<thead id='head_table'>
						<tr class='bg-blue'>
							<th style='text-align:center; vertical-align: middle;' width='5%'>No</th>
							<th style='text-align:center; vertical-align: middle;' width='15%'>Nama Supplier</th>
						</tr>
					</thead>
					<tbody>
					<?php
						$nosup=0;
						foreach($results['supplier'] AS $sup){
						$nosup++;
							?>
							<tr>
								<td ><?= $nosup;?></td>
								<td ><?= $sup->nama_alt_supplier;?></td>								
							</tr>
							
							<?php
						}
						
						?>	
					</tbody>
				</table>
				</div>		
				
				<div class="col-md-8">
				<h5 class="box-title"><b>Foto</b></h5>
				<br>
				<img src='<?php echo site_url();?>assets/files/<?php echo $foto;?>' />
				</div>		
				
			</div>		
		</div>
		</div>
	</div>
	</div>
	
</div>



