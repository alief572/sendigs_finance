<?php

$no_bom          = (!empty($header)) ? $header[0]->no_bom : '';
$id_product      = (!empty($header)) ? $header[0]->id_product : '';
$waste_product   = (!empty($header)) ? $header[0]->waste_product : '';
$waste_setting   = (!empty($header)) ? $header[0]->waste_setting : '';
$variant_product   = (!empty($header)) ? $header[0]->variant_product : '';

$fire_retardant = (!empty($header[0]->fire_retardant)) ? $header[0]->fire_retardant : '0';
$anti_uv 		= (!empty($header[0]->anti_uv)) ? $header[0]->anti_uv : '0';
$tixotropic 	= (!empty($header[0]->tixotropic)) ? $header[0]->tixotropic : '0';
$food_grade 	= (!empty($header[0]->food_grade)) ? $header[0]->food_grade : '0';
$wax 			= (!empty($header[0]->wax)) ? $header[0]->wax : '0';
$corrosion 		= (!empty($header[0]->corrosion)) ? $header[0]->corrosion : '0';

$file_upload 	= (!empty($header[0]->file_upload)) ? $header[0]->file_upload : '';

// print_r($header);
?>
<form id="data-form" method="post">
	<div class="box box-primary">
		<div class="box-body">
			<br>
			<div class="form-group row">
				<div class="col-md-2">
					<label for="customer">Product Master <span class='text-red'>*</span></label>
				</div>
				<div class="col-md-4">
					<input type="hidden" name="no_bom" value="<?= $no_bom; ?>">
					<select id="id_product" name="id_product" class="form-control input-md chosen-select" required>
						<option value="0">Select An Option</option>
						<?php foreach ($results['product'] as $product) {
							$sel = ($product->code_lv4 == $id_product) ? 'selected' : '';
						?>
							<option value="<?= $product->code_lv4; ?>" <?= $sel; ?>><?= strtoupper(strtolower($product->nama)) ?></option>
						<?php } ?>
					</select>
					<span id='addProductMaster' class='text-primary text-bold' style='cursor:pointer;'>Add Product Master</span>
				</div>
				<div class="col-md-2">
					<label for="customer">Varian Product</label>
				</div>
				<div class="col-md-4">
					<input type="text" name="variant_product" class='form-control input-md' placeholder='Variant Product' value="<?= $variant_product; ?>">
				</div>
			</div>
			<br>
			<div class='box box-primary' hidden>
				<div class='box-header'>
					<h3 class='box-title'>A. Mixing & Proses</h3>
					<div class='box-tool pull-right'>
						<!--<button type='button' data-id='frp_".$a."' class='btn btn-md btn-info panelSH'>SHOW</button>-->
					</div>
				</div>
				<div class='box-body hide_header'>
					<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
						<thead>
							<tr class='bg-blue'>
								<th class='text-center' style='width: 4%;'>#</th>
								<th class='text-center' style='width: 40%;'>Material Name</th>
								<th class='text-center'>Weight /kg</th>
								<th class='text-center' style='width: 4%;'>#</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$val = 0;
							if (!empty($detail)) {
								foreach ($detail as $val => $valx) {
									$val++;
									echo "<tr class='header_" . $val . "'>";
									echo "<td align='center'>" . $val . "</td>";
									echo "<td align='left'>";
									echo "<select name='Detail[" . $val . "][code_material]' class='chosen-select form-control input-sm inline-blockd material'>";
									echo "<option value='0'>Select Material Name</option>";
									foreach ($material as $valx4) {
										$sel2 = ($valx4->code_lv4 == $valx['code_material']) ? 'selected' : '';
										echo "<option value='" . $valx4->code_lv4 . "' " . $sel2 . ">" . strtoupper($valx4->nama) . "</option>";
									}
									echo 		"</select>";
									echo "</td>";
									echo "<td align='left'>";
									echo "<input type='text' name='Detail[" . $val . "][weight]' class='form-control input-md autoNumeric4 qty' placeholder='Weight /kg' value='" . $valx['weight'] . "'>";
									echo "</td>";
									echo "<td align='left'>";
									echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
									echo "</td>";
									echo "</tr>";
								}
							}
							?>
							<tr id='add_<?= $val ?>'>
								<td align='center'></td>
								<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-primary addPart' title='Add Material'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Material</button></td>
								<td align='center'></td>
								<td align='center'></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class='box box-primary'>
				<div class='box-header'>
					<h3 class='box-title'>A. BOM Single Product</h3>
				</div>
				<div class='box-body hide_header'>
					<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
						<thead>
							<tr class='bg-blue'>
								<th class='text-center' style='width: 4%;'>#</th>
								<th class='text-center'>BOM Standard & HI GRID Standard</th>
								<th class='text-center' hidden>Nama Material & Berat /kg</th>
								<th class='text-center' style='width: 8%;'>Qty</th>
								<th class='text-center' style='width: 8%;'>Unit</th>
								<th class='text-center'>Keterangan</th>
								<th class='text-center' style='width: 4%;'>#</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$val = 0;
							if (!empty($detail_hi_grid)) {
								foreach ($detail_hi_grid as $val => $valx) {
									$val++;
									echo "<tr class='headerhigrid_" . $val . "'>";
									echo "<td align='center'>" . $val . "</td>";
									echo "<td align='left'>";
									echo "<select name='DetailHiGrid[" . $val . "][code_material]'  data-id='" . $val . "' class='chosen-select form-control input-sm inline-blockd changeFungsiHiGrid'>";
									echo "<option value='0'>Select BOM Standard</option>";
									foreach ($bom_higridstd as $valx4) {
										$sel2 = ($valx4->no_bom == $valx['code_material']) ? 'selected' : '';
										echo "<option value='" . $valx4->no_bom . "' " . $sel2 . ">" . strtoupper($valx4->nm_jenis . ' | ' . $valx4->variant_product) . "</option>";
									}
									echo "</select>";
									echo "</td>";
									echo "<td align='left' hidden>";
									echo "<table class='table table-bordered higridMat" . $val . "'>";

									$detail_custom    = $this->db->get_where('bom_detail_custom', array('no_bom_detail' => $valx['no_bom_detail'], 'category' => 'hi grid std'))->result();
									$nomor = 0;
									foreach ($detail_custom as $valx2) {
										$nomor++;
										$nm_material = (!empty($GET_LEVEL4[$valx2->code_material]['nama'])) ? $GET_LEVEL4[$valx2->code_material]['nama'] : '';
										$datetime 	= $val . '-' . $nomor;

										echo "<tr>";
										echo "<td width='70%'>";
										echo "<input type='hidden' name='DetailHiGrid[" . $val . "][detail][" . $datetime . "][code_material]' class='form-control input-md' value='" . $valx2->code_material . "'>";
										echo "<input type='text' name='DetailHiGrid[" . $val . "][detail][" . $datetime . "][nm_material]' class='form-control input-md' value='" . $nm_material . "' readonly>";
										echo "</td>";
										echo "<td>";
										echo "<input type='text' name='DetailHiGrid[" . $val . "][detail][" . $datetime . "][berat]' class='form-control input-md autoNumeric4 qty' placeholder='Weight'  readonly value='" . $valx2->weight . "'>";
										echo "</td>";
										echo "</tr>";
									}
									echo "</table>";
									echo "</td>";
									echo "<td align='left'>";
									echo "<input type='text' name='DetailHiGrid[" . $val . "][qty]' class='form-control input-md autoNumeric0 text-center' placeholder='Qty' value='" . $valx['qty'] . "'>";
									echo "</td>";
									echo "<td align='left'>";
									echo "<select name='DetailHiGrid[" . $val . "][unit]'  data-id='" . $val . "' class='chosen-select form-control input-sm inline-blockd'>";
									echo "<option value='0'>Select Unit</option>";
									foreach ($satuan as $valx4) {
										$sel2 = ($valx4->id == $valx['unit']) ? 'selected' : '';
										echo "<option value='" . $valx4->id . "' " . $sel2 . ">" . strtoupper($valx4->code) . "</option>";
									}
									echo "</select>";
									echo "</td>";
									echo "<td align='left'>";
									echo "<input type='text' name='DetailHiGrid[" . $val . "][ket]' class='form-control input-md' placeholder='Keterangan' value='" . $valx['ket'] . "'>";
									echo "</td>";
									echo "<td align='left'>";
									echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
									echo "</td>";
									echo "</tr>";

									$resultUkuranJadi = $this->db->get_where('bom_detail_custom', array('category' => 'ukuran jadi', 'no_bom_detail' => $valx['no_bom_detail']))->result_array();
									$no = 0;
									foreach ($resultUkuranJadi as $val2D => $val2Dx) {
										$no++;
										echo "<tr class='headerhigrid_" . $val . "'>";
										echo "<td align='center'></td>";
										echo "<td align='left'>";
										echo "<div class='input-group'>";
										echo "<span class='input-group-addon' style='background: bisque;'>Length :</span>";
										echo "<input type='text' name='DetailHiGrid[" . $val . "][ukuran_jadi][" . $no . "][length]' class='form-control input-md autoNumeric' value='" . $val2Dx['length'] . "'>";
										echo "<span class='input-group-addon' style='background: bisque;'>Width :</span>";
										echo "<input type='text' name='DetailHiGrid[" . $val . "][ukuran_jadi][" . $no . "][width]' class='form-control input-md autoNumeric' value='" . $val2Dx['width'] . "'>";
										echo "<span class='input-group-addon' style='background: bisque;'>Qty :</span>";
										echo "<input type='text' name='DetailHiGrid[" . $val . "][ukuran_jadi][" . $no . "][qty]' class='form-control input-md autoNumeric' value='" . $val2Dx['qty'] . "'>";
										echo "<span class='input-group-addon' style='background: bisque;'>Meter Lari :</span>";
										echo "<input type='text' name='DetailHiGrid[" . $val . "][ukuran_jadi][" . $no . "][lari]' class='form-control input-md autoNumeric' value='" . $val2Dx['lari'] . "'>";
										echo "</div>";
										echo "</td>";
										echo "<td align='left' hidden></td>";
										echo "<td align='left'></td>";
										echo "<td align='left'></td>";
										echo "<td align='left'></td>";
										echo "<td align='left'>";
										echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delSubPart' title='Delete Part'><i class='fa fa-close'></i></button>";
										echo "</td>";
										echo "</tr>";
									}
									echo "<tr id='addhigrid_" . $val . "_" . $no . "' class='headerhigrid_" . $val . "'>";
									echo "<td align='center'></td>";
									echo "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-default addSubPart' title='Add'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Ukuran Jadi</button></td>";
									echo "<td align='center' hidden></td>";
									echo "<td align='center'></td>";
									echo "<td align='center'></td>";
									echo "<td align='center'></td>";
									echo "<td align='center'></td>";
									echo "</tr>";

									//cutting material
									$resultCuttingMaterial = $this->db->get_where('bom_detail_custom', array('category' => 'material cutting', 'no_bom_detail' => $valx['no_bom_detail']))->result_array();
									$materialList    = get_list_inventory_lv4('material');
									$no = 0;
									foreach ($resultCuttingMaterial as $val2D => $val2Dx) {
										$no++;
										echo "<tr class='headerhigrid_" . $val . "'>";
										echo "<td align='center'></td>";
										echo "<td align='left'>";
										echo "<select name='DetailHiGrid[" . $val . "][cutting][" . $no . "][id_material]' data-id='" . $val . "' class='chosen-select form-control input-sm inline-blockd'>";
										echo "<option value='0'>Select Material</option>";
										foreach ($materialList as $valx => $value) {
											$selected = ($value['code_lv4'] == $val2Dx['code_material']) ? 'selected' : '';
											echo "<option value='" . $value['code_lv4'] . "' " . $selected . ">" . strtoupper($value['nama']) . "</option>";
										}
										echo "</select>";
										echo "</td>";
										echo "<td align='left' hidden></td>";
										echo "<td align='left'>";
										echo "<input type='text' name='DetailHiGrid[" . $val . "][cutting][" . $no . "][weight]' class='form-control input-md text-center autoNumeric4 qty' placeholder='Qty' value='" . $val2Dx['weight'] . "'>";
										echo "</td>";
										echo "<td align='left'></td>";
										echo "<td align='left'></td>";
										echo "<td align='left'>";
										echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delSubPart' title='Delete Part'><i class='fa fa-close'></i></button>";
										echo "</td>";
										echo "</tr>";
									}
									echo "<tr id='addhigridcutting_" . $val . "_" . $no . "' class='headerhigrid_" . $val . "'>";
									echo "<td align='center'></td>";
									echo "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-default addSubPartMatCut' title='Add'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Material Cutting</button></td>";
									echo "<td align='center' hidden></td>";
									echo "<td align='center'></td>";
									echo "<td align='center'></td>";
									echo "<td align='center'></td>";
									echo "<td align='center'></td>";
									echo "</tr>";
								}
							}
							?>
							<tr id='addhigrid_<?= $val ?>'>
								<td align='center'></td>
								<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-primary addPartHiGrid' title='Add'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Standard & HI GRID Standard</button></td>
								<td align='center' hidden></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class='box box-info' hidden>
				<div class='box-header'>
					<h3 class='box-title'>B. Additive</h3>
				</div>
				<div class='box-body hide_header'>
					<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
						<thead>
							<tr class='bg-blue'>
								<th class='text-center' style='width: 4%;'>#</th>
								<th class='text-center' style='width: 30%;'>Fungsi Additive</th>
								<th class='text-center'>Nama Material & Berat /kg</th>
								<th class='text-center' style='width: 4%;'>#</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$val = 0;
							if (!empty($detail_additive)) {
								foreach ($detail_additive as $val => $valx) {
									$val++;
									echo "<tr class='headeradditive_" . $val . "'>";
									echo "<td align='center'>" . $val . "</td>";
									echo "<td align='left'>";
									echo "<select name='DetailAdt[" . $val . "][code_material]'  data-id='" . $val . "' class='chosen-select form-control input-sm inline-blockd changeFungsiAdditive'>";
									echo "<option value='0'>Select Additive</option>";
									foreach ($bom_additive as $valx4) {
										$sel2 = ($valx4->no_bom == $valx['code_material']) ? 'selected' : '';
										echo "<option value='" . $valx4->no_bom . "' " . $sel2 . ">" . strtoupper($valx4->additive_name) . "</option>";
									}
									echo "</select>";
									echo "</td>";
									echo "<td align='left'>";
									echo "<table class='table table-bordered additiveMat" . $val . "'>";

									$detail_custom    = $this->db->get_where('bom_detail_custom', array('no_bom_detail' => $valx['no_bom_detail'], 'category' => 'additive'))->result();
									$nomor = 0;
									foreach ($detail_custom as $valx2) {
										$nomor++;
										$nm_material = (!empty($GET_LEVEL4[$valx2->code_material]['nama'])) ? $GET_LEVEL4[$valx2->code_material]['nama'] : '';
										$datetime 	= $val . '-' . $nomor;

										echo "<tr>";
										echo "<td width='70%'>";
										echo "<input type='hidden' name='DetailAdt[" . $val . "][detail][" . $datetime . "][code_material]' class='form-control input-md' value='" . $valx2->code_material . "'>";
										echo "<input type='text' name='DetailAdt[" . $val . "][detail][" . $datetime . "][nm_material]' class='form-control input-md' value='" . $nm_material . "' readonly>";
										echo "</td>";
										echo "<td>";
										echo "<input type='text' name='DetailAdt[" . $val . "][detail][" . $datetime . "][berat]' class='form-control input-md autoNumeric4 qty' placeholder='Weight' value='" . $valx2->weight . "'>";
										echo "</td>";
										echo "</tr>";
									}
									echo "</table>";
									echo "</td>";
									echo "<td align='left'>";
									echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
									echo "</td>";
									echo "</tr>";
								}
							}
							?>
							<tr id='addadditive_<?= $val ?>'>
								<td align='center'></td>
								<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-info addPartAdditive' title='Add Additive'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Additive</button></td>
								<td align='center'></td>
								<td align='center'></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class='box box-success' hidden>
				<div class='box-header'>
					<h3 class='box-title'>B. Topping</h3>
				</div>
				<div class='box-body hide_header'>
					<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
						<thead>
							<tr class='bg-blue'>
								<th class='text-center' style='width: 4%;'>#</th>
								<th class='text-center' style='width: 30%;'>Topping</th>
								<th class='text-center'>Nama Material & Berat /kg</th>
								<th class='text-center' style='width: 8%;'>Qty</th>
								<th class='text-center' style='width: 8%;'>Unit</th>
								<th class='text-center'>Keterangan</th>
								<th class='text-center' style='width: 4%;'>#</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$val = 0;
							if (!empty($detail_topping)) {
								foreach ($detail_topping as $val => $valx) {
									$val++;
									echo "<tr class='headertopping_" . $val . "'>";
									echo "<td align='center'>" . $val . "</td>";
									echo "<td align='left'>";
									echo "<select name='DetailTop[" . $val . "][code_material]'  data-id='" . $val . "' class='chosen-select form-control input-sm inline-blockd changeFungsiTopping'>";
									echo "<option value='0'>Select Topping</option>";
									foreach ($bom_topping as $valx4) {
										$sel2 = ($valx4->no_bom == $valx['code_material']) ? 'selected' : '';
										echo "<option value='" . $valx4->no_bom . "' " . $sel2 . ">" . strtoupper($valx4->nm_jenis . ' | ' . $valx4->variant_product) . "</option>";
									}
									echo "</select>";
									echo "</td>";
									echo "<td align='left'>";
									echo "<table class='table table-bordered toppingMat" . $val . "'>";

									$detail_custom    = $this->db->get_where('bom_detail_custom', array('no_bom_detail' => $valx['no_bom_detail'], 'category' => 'topping'))->result();
									$nomor = 0;
									foreach ($detail_custom as $valx2) {
										$nomor++;
										$nm_material = (!empty($GET_LEVEL4[$valx2->code_material]['nama'])) ? $GET_LEVEL4[$valx2->code_material]['nama'] : '';
										$datetime 	= $val . '-' . $nomor;

										echo "<tr>";
										echo "<td width='70%'>";
										echo "<input type='hidden' name='DetailTop[" . $val . "][detail][" . $datetime . "][code_material]' class='form-control input-md' value='" . $valx2->code_material . "'>";
										echo "<input type='text' name='DetailTop[" . $val . "][detail][" . $datetime . "][nm_material]' class='form-control input-md' value='" . $nm_material . "' readonly>";
										echo "</td>";
										echo "<td>";
										echo "<input type='text' name='DetailTop[" . $val . "][detail][" . $datetime . "][berat]' class='form-control input-md autoNumeric4 qty' placeholder='Weight'  readonly value='" . $valx2->weight . "'>";
										echo "</td>";
										echo "</tr>";
									}
									echo "</table>";
									echo "</td>";
									echo "<td align='left'>";
									echo "<input type='text' name='DetailTop[" . $val . "][qty]' class='form-control input-md autoNumeric0 text-center' placeholder='Qty' value='" . $valx['qty'] . "'>";
									echo "</td>";
									echo "<td align='left'>";
									echo "<select name='DetailTop[" . $val . "][unit]'  data-id='" . $val . "' class='chosen-select form-control input-sm inline-blockd'>";
									echo "<option value='0'>Select Unit</option>";
									foreach ($satuan as $valx4) {
										$sel2 = ($valx4->id == $valx['unit']) ? 'selected' : '';
										echo "<option value='" . $valx4->id . "' " . $sel2 . ">" . strtoupper($valx4->code) . "</option>";
									}
									echo "</select>";
									echo "</td>";
									echo "<td align='left'>";
									echo "<input type='text' name='DetailTop[" . $val . "][ket]' class='form-control input-md' placeholder='Keterangan' value='" . $valx['ket'] . "'>";
									echo "</td>";
									echo "<td align='left'>";
									echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
									echo "</td>";
									echo "</tr>";
								}
							}
							?>
							<tr id='addtopping_<?= $val ?>'>
								<td align='center'></td>
								<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-success addPartTopping' title='Add Topping'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Topping</button></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class='box box-warning'>
				<div class='box-header'>
					<h3 class='box-title'>B. Accessories</h3>
					<div class='box-tool pull-right'>
						<!--<button type='button' data-id='frp_".$a."' class='btn btn-md btn-info panelSH'>SHOW</button>-->
					</div>
				</div>
				<div class='box-body hide_header'>
					<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
						<thead>
							<tr class='bg-blue'>
								<th class='text-center' style='width: 4%;'>#</th>
								<th class='text-center' style='width: 40%;'>Accessories Name</th>
								<th class='text-center'>Qty</th>
								<th class='text-center'>Keterangan</th>
								<th class='text-center' style='width: 4%;'>#</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$val = 0;
							
							if (!empty($detail_accessories)) {
								foreach ($detail_accessories as $val => $valx) {
									$val++;
									echo "<tr class='headeraccessories_" . $val . "'>";
									echo "<td align='center'>" . $val . "</td>";
									echo "<td align='left'>";
									echo "<select name='DetailAcc[" . $val . "][code_material]' class='chosen-select form-control input-sm inline-blockd'>";
									echo "<option value='0'>Select Accessories</option>";
									foreach ($accessories as $valx4) {
										$sel2 = ($valx4->id == $valx['code_material']) ? 'selected' : '';
										echo "<option value='" . $valx4->id . "' " . $sel2 . ">" . strtoupper($valx4->stock_name . ' ' . $valx4->brand . ' ' . $valx4->spec) . "</option>";
									}
									echo 		"</select>";
									echo "</td>";
									echo "<td align='left'>";
									echo "<input type='text' name='DetailAcc[" . $val . "][weight]' class='form-control input-md autoNumeric4 qty' placeholder='Qty' value='" . $valx['weight'] . "'>";
									echo "</td>";
									echo "<td align='left'>";
									echo "<input type='text' name='DetailAcc[" . $val . "][ket]' class='form-control input-md' placeholder='Keterangan' value='" . $valx['ket'] . "'>";
									echo "</td>";
									echo "<td align='left'>";
									echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
									echo "</td>";
									echo "</tr>";
								}
							}
							?>
							<tr id='addaccessories_<?= $val ?>'>
								<td align='center'></td>
								<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-warning addPartAcc' title='Add Accessories'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Accessories</button></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class='box box-primary'>
				<div class='box-header'>
					<h3 class='box-title'>C. Material Joint & Finishing</h3>
					<div class='box-tool pull-right'>
						<!--<button type='button' data-id='frp_".$a."' class='btn btn-md btn-info panelSH'>SHOW</button>-->
					</div>
				</div>
				<div class='box-body hide_header'>
					<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
						<thead>
							<tr class='bg-blue'>
								<th class='text-center' style='width: 4%;'>#</th>
								<th class='text-center' style='width: 12%;'>Layer</th>
								<th class='text-center' style='width: 40%;'>Material Name</th>
								<th class='text-center'>Qty</th>
								<th class='text-center'>Keterangan</th>
								<th class='text-center' style='width: 4%;'>#</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$val = 0;
							if (!empty($detail_mat_joint)) {
								foreach ($detail_mat_joint as $val => $valx) {
									$val++;
									echo "<tr class='headermatjoint_" . $val . "'>";
									echo "<td align='center'>" . $val . "</td>";
									echo "<td align='left'>";
									echo "<input type='text' name='DetailMatJoint[" . $val . "][layer]' class='form-control input-md' placeholder='Layer' value='" . $valx['layer'] . "'>";
									echo "</td>";
									echo "<td align='left'>";
									echo "<select name='DetailMatJoint[" . $val . "][code_material]' class='chosen-select form-control input-sm inline-blockd'>";
									echo "<option value='0'>Select Material</option>";
									foreach ($material as $valx4) {
										$sel2 = ($valx4->code_lv4 == $valx['code_material']) ? 'selected' : '';
										echo "<option value='" . $valx4->code_lv4 . "' " . $sel2 . ">" . strtoupper($valx4->nama) . "</option>";
									}
									echo 		"</select>";
									echo "</td>";
									echo "<td align='left'>";
									echo "<input type='text' name='DetailMatJoint[" . $val . "][weight]' class='form-control input-md autoNumeric4 qty' placeholder='Qty' value='" . $valx['weight'] . "'>";
									echo "</td>";
									echo "<td align='left'>";
									echo "<input type='text' name='DetailMatJoint[" . $val . "][ket]' class='form-control input-md' placeholder='Keterangan' value='" . $valx['ket'] . "'>";
									echo "</td>";
									echo "<td align='left'>";
									echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
									echo "</td>";
									echo "</tr>";
								}
							}
							?>
							<tr id='addmatjoint_<?= $val ?>'>
								<td align='center'></td>
								<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-warning addPartMatJoint' title='Add'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Material Joint</button></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class='box box-danger'>
				<div class='box-header'>
					<h3 class='box-title'>D. Flat Sheet</h3>
					<div class='box-tool pull-right'>
						<!--<button type='button' data-id='frp_".$a."' class='btn btn-md btn-info panelSH'>SHOW</button>-->
					</div>
				</div>
				<div class='box-body hide_header'>
					<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
						<thead>
							<tr class='bg-blue'>
								<th class='text-center' style='width: 4%;'>#</th>
								<th class='text-center' style='width: 23%;'>Length</th>
								<th class='text-center' style='width: 23%;'>Width</th>
								<th class='text-center' style='width: 23%;'>Qty</th>
								<th class='text-center' style='width: 23%;'>M2</th>
								<th class='text-center' style='width: 4%;'>#</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$val = 0;
							if (!empty($detail_flat_sheet)) {
								foreach ($detail_flat_sheet as $val => $valx) {
									$val++;
									echo "<tr class='headerflatsheet_" . $val . "'>";
									echo "<td align='center'>" . $val . "</td>";
									echo "<td align='left'>";
									echo "<input type='text' name='DetailFlat[" . $val . "][length]' class='form-control input-md text-center autoNumeric4 length changeFlat' placeholder='Length' value='" . $valx['length'] . "'>";
									echo "</td>";
									echo "<td align='left'>";
									echo "<input type='text' name='DetailFlat[" . $val . "][width]' class='form-control input-md text-center autoNumeric4 width changeFlat' placeholder='Width' value='" . $valx['width'] . "'>";
									echo "</td>";
									echo "<td align='left'>";
									echo "<input type='text' name='DetailFlat[" . $val . "][qty]' class='form-control input-md text-center autoNumeric4' placeholder='Qty' value='" . $valx['qty'] . "'>";
									echo "</td>";
									echo "<td align='left'>";
									echo "<input type='text' name='DetailFlat[" . $val . "][m2]' class='form-control input-md text-center autoNumeric4 resultM2' placeholder='M2' readonly value='" . $valx['m2'] . "'>";
									echo "</td>";
									echo "<td align='left'>";
									echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
									echo "</td>";
									echo "</tr>";

									//material flat sheet
									$resultCuttingMaterial = $this->db->get_where('bom_detail_custom', array('category' => 'material flat sheet', 'no_bom_detail' => $valx['no_bom_detail']))->result_array();
									$materialList    = get_list_inventory_lv4('material');
									$no = 0;
									foreach ($resultCuttingMaterial as $val2D => $val2Dx) {
										$no++;
										echo "<tr class='headerflatsheet_" . $val . "'>";
										echo "<td align='center'></td>";
										echo "<td align='left' colspan='2'>";
										echo "<select name='DetailFlat[" . $val . "][material][" . $no . "][id_material]' data-id='" . $val . "' class='chosen-select form-control input-sm inline-blockd'>";
										echo "<option value='0'>Select Material</option>";
										foreach ($materialList as $valx => $value) {
											$selected = ($value['code_lv4'] == $val2Dx['code_material']) ? 'selected' : '';
											echo "<option value='" . $value['code_lv4'] . "' " . $selected . ">" . strtoupper($value['nama']) . "</option>";
										}
										echo "</select>";
										echo "</td>";
										echo "<td align='left'>";
										echo "<input type='text' name='DetailFlat[" . $val . "][material][" . $no . "][weight]' class='form-control input-md text-center autoNumeric4 qty' placeholder='Qty' value='" . $val2Dx['weight'] . "'>";
										echo "</td>";
										echo "<td align='left'></td>";
										echo "<td align='left'>";
										echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delSubPart' title='Delete Part'><i class='fa fa-close'></i></button>";
										echo "</td>";
										echo "</tr>";
									}
									echo "<tr id='headerflatsheet_" . $val . "_" . $no . "' class='headerflatsheet_" . $val . "'>";
									echo "<td align='center'></td>";
									echo "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-default addSubPartMat' data-label_class='headerflatsheet' data-label_name='DetailFlat' title='Add'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Material</button></td>";
									echo "<td align='center'></td>";
									echo "<td align='center'></td>";
									echo "<td align='center'></td>";
									echo "<td align='center'></td>";
									echo "</tr>";
								}
							}
							?>
							<tr id='addflatsheet_<?= $val ?>'>
								<td align='center'></td>
								<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-danger addPartFlat' title='Add Flat Sheet'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Flat Sheet</button></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class='box box-primary'>
				<div class='box-header'>
					<h3 class='box-title'>E. End Plate / Kick Plate</h3>
					<div class='box-tool pull-right'>
						<!--<button type='button' data-id='frp_".$a."' class='btn btn-md btn-info panelSH'>SHOW</button>-->
					</div>
				</div>
				<div class='box-body hide_header'>
					<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
						<thead>
							<tr class='bg-blue'>
								<th class='text-center' style='width: 4%;'>#</th>
								<th class='text-center' style='width: 23%;'>Length</th>
								<th class='text-center' style='width: 23%;'>Height</th>
								<th class='text-center' style='width: 23%;'>Qty</th>
								<th class='text-center' style='width: 23%;'>M2</th>
								<th class='text-center' style='width: 4%;'>#</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$val = 0;
							if (!empty($detail_end_plate)) {
								foreach ($detail_end_plate as $val => $valx) {
									$val++;
									echo "<tr class='headerendplate_" . $val . "'>";
									echo "<td align='center'>" . $val . "</td>";
									echo "<td align='left'>";
									echo "<input type='text' name='DetailEnd[" . $val . "][length]' class='form-control input-md text-center autoNumeric4 length changeEnd' placeholder='Length' value='" . $valx['length'] . "'>";
									echo "</td>";
									echo "<td align='left'>";
									echo "<input type='text' name='DetailEnd[" . $val . "][width]' class='form-control input-md text-center autoNumeric4 width changeEnd' placeholder='Height' value='" . $valx['width'] . "'>";
									echo "</td>";
									echo "<td align='left'>";
									echo "<input type='text' name='DetailEnd[" . $val . "][qty]' class='form-control input-md text-center autoNumeric4' placeholder='Qty' value='" . $valx['qty'] . "'>";
									echo "</td>";
									echo "<td align='left'>";
									echo "<input type='text' name='DetailEnd[" . $val . "][m2]' class='form-control input-md text-center autoNumeric4 resultM2' placeholder='M2' readonly value='" . $valx['m2'] . "'>";
									echo "</td>";
									echo "<td align='left'>";
									echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
									echo "</td>";
									echo "</tr>";

									//material end plate
									$resultCuttingMaterial = $this->db->get_where('bom_detail_custom', array('category' => 'material end plate', 'no_bom_detail' => $valx['no_bom_detail']))->result_array();
									$materialList    = get_list_inventory_lv4('material');
									$no = 0;
									foreach ($resultCuttingMaterial as $val2D => $val2Dx) {
										$no++;
										echo "<tr class='headerendplate_" . $val . "'>";
										echo "<td align='center'></td>";
										echo "<td align='left' colspan='2'>";
										echo "<select name='DetailEnd[" . $val . "][material][" . $no . "][id_material]' data-id='" . $val . "' class='chosen-select form-control input-sm inline-blockd'>";
										echo "<option value='0'>Select Material</option>";
										foreach ($materialList as $valx => $value) {
											$selected = ($value['code_lv4'] == $val2Dx['code_material']) ? 'selected' : '';
											echo "<option value='" . $value['code_lv4'] . "' " . $selected . ">" . strtoupper($value['nama']) . "</option>";
										}
										echo "</select>";
										echo "</td>";
										echo "<td align='left'>";
										echo "<input type='text' name='DetailEnd[" . $val . "][material][" . $no . "][weight]' class='form-control input-md text-center autoNumeric4 qty' placeholder='Qty' value='" . $val2Dx['weight'] . "'>";
										echo "</td>";
										echo "<td align='left'></td>";
										echo "<td align='left'>";
										echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delSubPart' title='Delete Part'><i class='fa fa-close'></i></button>";
										echo "</td>";
										echo "</tr>";
									}
									echo "<tr id='headerendplate_" . $val . "_" . $no . "' class='headerendplate_" . $val . "'>";
									echo "<td align='center'></td>";
									echo "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-default addSubPartMat' data-label_class='headerendplate' data-label_name='DetailEnd' title='Add'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Material</button></td>";
									echo "<td align='center'></td>";
									echo "<td align='center'></td>";
									echo "<td align='center'></td>";
									echo "<td align='center'></td>";
									echo "</tr>";
								}
							}
							?>
							<tr id='addendplate_<?= $val ?>'>
								<td align='center'></td>
								<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-primary addPartEnd' title='Add End Plate'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add End Plate</button></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class='box box-success'>
				<div class='box-header'>
					<h3 class='box-title'>F. Chequered Plate</h3>
					<div class='box-tool pull-right'>
					</div>
				</div>
				<div class='box-body hide_header'>
					<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
						<thead>
							<tr class='bg-blue'>
								<th class='text-center' style='width: 4%;'>#</th>
								<th class='text-center' style='width: 23%;'>Length</th>
								<th class='text-center' style='width: 23%;'>Width</th>
								<th class='text-center' style='width: 23%;'>Qty</th>
								<th class='text-center' style='width: 23%;'>M2</th>
								<th class='text-center' style='width: 4%;'>#</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$val = 0;
							if (!empty($detail_ukuran_jadi)) {
								foreach ($detail_ukuran_jadi as $val => $valx) {
									$val++;
									echo "<tr class='headerukuranjadi_" . $val . "'>";
									echo "<td align='center'>" . $val . "</td>";
									echo "<td align='left'>";
									echo "<input type='text' name='DetailJadi[" . $val . "][length]' class='form-control input-md text-center autoNumeric4 length changeEnd' placeholder='Length' value='" . $valx['length'] . "'>";
									echo "</td>";
									echo "<td align='left'>";
									echo "<input type='text' name='DetailJadi[" . $val . "][width]' class='form-control input-md text-center autoNumeric4 width changeEnd' placeholder='Width' value='" . $valx['width'] . "'>";
									echo "</td>";
									echo "<td align='left'>";
									echo "<input type='text' name='DetailJadi[" . $val . "][qty]' class='form-control input-md text-center autoNumeric4' placeholder='Qty' value='" . $valx['qty'] . "'>";
									echo "</td>";
									echo "<td align='left'>";
									echo "<input type='text' name='DetailJadi[" . $val . "][m2]' class='form-control input-md text-center autoNumeric4 resultM2' placeholder='M2' readonly value='" . $valx['m2'] . "'>";
									echo "</td>";
									echo "<td align='left'>";
									echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
									echo "</td>";
									echo "</tr>";

									//material end plate
									$resultCuttingMaterial = $this->db->get_where('bom_detail_custom', array('category' => 'material ukuran jadi', 'no_bom_detail' => $valx['no_bom_detail']))->result_array();
									$materialList    = get_list_inventory_lv4('material');
									$no = 0;
									foreach ($resultCuttingMaterial as $val2D => $val2Dx) {
										$no++;
										echo "<tr class='headerukuranjadi_" . $val . "'>";
										echo "<td align='center'></td>";
										echo "<td align='left' colspan='2'>";
										echo "<select name='DetailJadi[" . $val . "][material][" . $no . "][id_material]' data-id='" . $val . "' class='chosen-select form-control input-sm inline-blockd'>";
										echo "<option value='0'>Select Material</option>";
										foreach ($materialList as $valx => $value) {
											$selected = ($value['code_lv4'] == $val2Dx['code_material']) ? 'selected' : '';
											echo "<option value='" . $value['code_lv4'] . "' " . $selected . ">" . strtoupper($value['nama']) . "</option>";
										}
										echo "</select>";
										echo "</td>";
										echo "<td align='left'>";
										echo "<input type='text' name='DetailJadi[" . $val . "][material][" . $no . "][weight]' class='form-control input-md text-center autoNumeric4 qty' placeholder='Qty' value='" . $val2Dx['weight'] . "'>";
										echo "</td>";
										echo "<td align='left'></td>";
										echo "<td align='left'>";
										echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delSubPart' title='Delete Part'><i class='fa fa-close'></i></button>";
										echo "</td>";
										echo "</tr>";
									}
									echo "<tr id='headerukuranjadi_" . $val . "_" . $no . "' class='headerukuranjadi_" . $val . "'>";
									echo "<td align='center'></td>";
									echo "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-default addSubPartMat' data-label_class='headerukuranjadi' data-label_name='DetailJadi' title='Add'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Material</button></td>";
									echo "<td align='center'></td>";
									echo "<td align='center'></td>";
									echo "<td align='center'></td>";
									echo "<td align='center'></td>";
									echo "</tr>";
								}
							}
							?>
							<tr id='addukuranjadi_<?= $val ?>'>
								<td align='center'></td>
								<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-success addPartJadi' title='Add'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Chequered Plate</button></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>

			<div class='box box-success'>
				<div class='box-header'>
					<h3 class='box-title'>G. Others</h3>
					<div class='box-tool pull-right'>
					</div>
				</div>
				<div class='box-body hide_header'>
					<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
						<thead>
							<tr class='bg-blue'>
								<th class='text-center' style='width: 4%;'>#</th>
								<th class='text-center' style='width: 23%;'>Length</th>
								<th class='text-center' style='width: 23%;'>Width</th>
								<th class='text-center' style='width: 23%;'>Qty</th>
								<th class='text-center' style='width: 23%;'>M2</th>
								<th class='text-center' style='width: 4%;'>#</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$val = 0;
							if (!empty($detail_others)) {
								foreach ($detail_others as $val => $valx) {
									$val++;
									echo "<tr class='headerothers_" . $val . "'>";
									echo "<td align='center'>" . $val . "</td>";
									echo "<td align='left'>";
									echo "<input type='text' name='DetailOthers[" . $val . "][length]' class='form-control input-md text-center autoNumeric4 length changeEnd' placeholder='Length' value='" . $valx['length'] . "'>";
									echo "</td>";
									echo "<td align='left'>";
									echo "<input type='text' name='DetailOthers[" . $val . "][width]' class='form-control input-md text-center autoNumeric4 width changeEnd' placeholder='Width' value='" . $valx['width'] . "'>";
									echo "</td>";
									echo "<td align='left'>";
									echo "<input type='text' name='DetailOthers[" . $val . "][qty]' class='form-control input-md text-center autoNumeric4' placeholder='Qty' value='" . $valx['qty'] . "'>";
									echo "</td>";
									echo "<td align='left'>";
									echo "<input type='text' name='DetailOthers[" . $val . "][m2]' class='form-control input-md text-center autoNumeric4 resultM2' placeholder='M2' readonly value='" . $valx['m2'] . "'>";
									echo "</td>";
									echo "<td align='left'>";
									echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
									echo "</td>";
									echo "</tr>";

									//material end plate
									$resultCuttingMaterial = $this->db->get_where('bom_detail_custom', array('category' => 'material others', 'no_bom_detail' => $valx['no_bom_detail']))->result_array();
									$materialList    = get_list_inventory_lv4('material');
									$no = 0;
									foreach ($resultCuttingMaterial as $val2D => $val2Dx) {
										$no++;
										echo "<tr class='headerothers_" . $val . "'>";
										echo "<td align='center'></td>";
										echo "<td align='left' colspan='2'>";
										echo "<select name='DetailOthers[" . $val . "][material][" . $no . "][id_material]' data-id='" . $val . "' class='chosen-select form-control input-sm inline-blockd'>";
										echo "<option value='0'>Select Material</option>";
										foreach ($materialList as $valx => $value) {
											$selected = ($value['code_lv4'] == $val2Dx['code_material']) ? 'selected' : '';
											echo "<option value='" . $value['code_lv4'] . "' " . $selected . ">" . strtoupper($value['nama']) . "</option>";
										}
										echo "</select>";
										echo "</td>";
										echo "<td align='left'>";
										echo "<input type='text' name='DetailOthers[" . $val . "][material][" . $no . "][weight]' class='form-control input-md text-center autoNumeric4 qty' placeholder='Qty' value='" . $val2Dx['weight'] . "'>";
										echo "</td>";
										echo "<td align='left'></td>";
										echo "<td align='left'>";
										echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delSubPart' title='Delete Part'><i class='fa fa-close'></i></button>";
										echo "</td>";
										echo "</tr>";
									}
									echo "<tr id='headerothers_" . $val . "_" . $no . "' class='headerothers_" . $val . "'>";
									echo "<td align='center'></td>";
									echo "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-default addSubPartMat' data-label_class='headerothers' data-label_name='DetailOthers' title='Add'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Material</button></td>";
									echo "<td align='center'></td>";
									echo "<td align='center'></td>";
									echo "<td align='center'></td>";
									echo "<td align='center'></td>";
									echo "</tr>";
								}
							}
							?>
							<tr id='addothers_<?= $val ?>'>
								<td align='center'></td>
								<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-success addPartOthers' title='Add'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Others</button></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
							</tr>
						</tbody>
					</table>
					<hr>
					<div class="form-group row">
						<div class="col-md-2">
							<label>Sesuai Gambar</label>
						</div>
						<div class="col-md-10">
							<div class="form-group">
								<input type="file" name='photo' id="photo">
							</div>
							<?php if (!empty($file_upload)) { ?>
								<a href='<?= base_url() . $file_upload; ?>' target='_blank' class="text-primary" title='Download'>Download File</a>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
			<button type="button" class="btn btn-danger" style='float:right; margin-left:5px;' name="back" id="back"><i class="fa fa-reply"></i> Back</button>
			<button type="submit" class="btn btn-primary" style='float:right;' name="save" id="save_bom"><i class="fa fa-save"></i> Save</button>
</form>
</div>
</div>
</form>
<div class="modal modal-default fade" id="dialog-popup" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="head_title">Default</h4>
			</div>
			<div class="modal-body" id="ModalView">
				...
			</div>
		</div>
	</div>

	<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
	<style media="screen">
		.datepicker {
			cursor: pointer;
			padding-left: 12px;
		}
	</style>
	<script type="text/javascript">
		//$('#input-kendaraan').hide();
		var base_url = '<?php echo base_url(); ?>';
		var active_controller = '<?php echo ($this->uri->segment(1)); ?>';

		$(document).ready(function() {
			$('.chosen-select').select2();
			$(".datepicker").datepicker();
			$(".autoNumeric4").autoNumeric('init', {
				mDec: '4',
				aPad: false
			});
			$(".autoNumeric0").autoNumeric('init', {
				mDec: '0',
				aPad: false
			});
			$(".autoNumeric").autoNumeric('init', {
				mDec: '2',
				aPad: false
			});

			//add part material dan proses
			$(document).on('click', '.addPart', function() {
				// loading_spinner();
				var get_id = $(this).parent().parent().attr('id');
				// console.log(get_id);
				var split_id = get_id.split('_');
				var id = parseInt(split_id[1]) + 1;
				var id_bef = split_id[1];

				$.ajax({
					url: base_url + active_controller + '/get_add/' + id,
					cache: false,
					type: "POST",
					dataType: "json",
					success: function(data) {
						$("#add_" + id_bef).before(data.header);
						$("#add_" + id_bef).remove();
						$('.chosen_select').select2();
						$('.autoNumeric4').autoNumeric('init', {
							mDec: '4',
							aPad: false
						});
						swal.close();
					},
					error: function() {
						swal({
							title: "Error Message !",
							text: 'Connection Time Out. Please try again..',
							type: "warning",
							timer: 3000,
							showCancelButton: false,
							showConfirmButton: false,
							allowOutsideClick: false
						});
					}
				});
			});

			//add part additive
			$(document).on('click', '.addPartAdditive', function() {
				// loading_spinner();
				var get_id = $(this).parent().parent().attr('id');
				// console.log(get_id);
				var split_id = get_id.split('_');
				var id = parseInt(split_id[1]) + 1;
				var id_bef = split_id[1];

				$.ajax({
					url: base_url + active_controller + '/get_add_additive/' + id,
					cache: false,
					type: "POST",
					dataType: "json",
					success: function(data) {
						$("#addadditive_" + id_bef).before(data.header);
						$("#addadditive_" + id_bef).remove();
						$('.chosen_select').select2();
						$('.autoNumeric4').autoNumeric('init', {
							mDec: '4',
							aPad: false
						});
						swal.close();
					},
					error: function() {
						swal({
							title: "Error Message !",
							text: 'Connection Time Out. Please try again..',
							type: "warning",
							timer: 3000,
							showCancelButton: false,
							showConfirmButton: false,
							allowOutsideClick: false
						});
					}
				});
			});

			$(document).on('change', '.changeFungsiAdditive', function() {
				// loading_spinner();
				var id = $(this).val();
				var id_row = $(this).data('id');

				$.ajax({
					url: base_url + active_controller + '/get_add_additive_breakdown/' + id + '/' + id_row,
					cache: false,
					type: "POST",
					dataType: "json",
					success: function(data) {
						$(".additiveMat" + id_row).html(data.material);
						$('.chosen_select').select2();
						$('.autoNumeric4').autoNumeric('init', {
							mDec: '4',
							aPad: false
						});
						swal.close();
					},
					error: function() {
						swal({
							title: "Error Message !",
							text: 'Connection Time Out. Please try again..',
							type: "warning",
							timer: 3000,
							showCancelButton: false,
							showConfirmButton: false,
							allowOutsideClick: false
						});
					}
				});
			});

			//add part topping
			$(document).on('click', '.addPartTopping', function() {
				// loading_spinner();
				var get_id = $(this).parent().parent().attr('id');
				// console.log(get_id);
				var split_id = get_id.split('_');
				var id = parseInt(split_id[1]) + 1;
				var id_bef = split_id[1];

				$.ajax({
					url: base_url + active_controller + '/get_add_topping/' + id,
					cache: false,
					type: "POST",
					dataType: "json",
					success: function(data) {
						$("#addtopping_" + id_bef).before(data.header);
						$("#addtopping_" + id_bef).remove();
						$('.chosen_select').select2();
						$('.autoNumeric4').autoNumeric('init', {
							mDec: '4',
							aPad: false
						});
						$(".autoNumeric0").autoNumeric('init', {
							mDec: '0',
							aPad: false
						});
						swal.close();
					},
					error: function() {
						swal({
							title: "Error Message !",
							text: 'Connection Time Out. Please try again..',
							type: "warning",
							timer: 3000,
							showCancelButton: false,
							showConfirmButton: false,
							allowOutsideClick: false
						});
					}
				});
			});

			$(document).on('change', '.changeFungsiTopping', function() {
				// loading_spinner();
				var id = $(this).val();
				var id_row = $(this).data('id');

				$.ajax({
					url: base_url + active_controller + '/get_add_topping_breakdown/' + id + '/' + id_row,
					cache: false,
					type: "POST",
					dataType: "json",
					success: function(data) {
						$(".toppingMat" + id_row).html(data.material);
						$('.chosen_select').select2();
						$('.autoNumeric4').autoNumeric('init', {
							mDec: '4',
							aPad: false
						});
						swal.close();
					},
					error: function() {
						swal({
							title: "Error Message !",
							text: 'Connection Time Out. Please try again..',
							type: "warning",
							timer: 3000,
							showCancelButton: false,
							showConfirmButton: false,
							allowOutsideClick: false
						});
					}
				});
			});

			//add part accessories
			$(document).on('click', '.addPartAcc', function() {
				// loading_spinner();
				var get_id = $(this).parent().parent().attr('id');
				// console.log(get_id);
				var split_id = get_id.split('_');
				var id = parseInt(split_id[1]) + 1;
				var id_bef = split_id[1];

				$.ajax({
					url: base_url + active_controller + '/get_add_accessories/' + id,
					cache: false,
					type: "POST",
					dataType: "json",
					success: function(data) {
						$("#addaccessories_" + id_bef).before(data.header);
						$("#addaccessories_" + id_bef).remove();
						$('.chosen_select').select2();
						$('.autoNumeric4').autoNumeric('init', {
							mDec: '4',
							aPad: false
						});
						swal.close();
					},
					error: function() {
						swal({
							title: "Error Message !",
							text: 'Connection Time Out. Please try again..',
							type: "warning",
							timer: 3000,
							showCancelButton: false,
							showConfirmButton: false,
							allowOutsideClick: false
						});
					}
				});
			});

			//add material joint
			$(document).on('click', '.addPartMatJoint', function() {
				// loading_spinner();
				var get_id = $(this).parent().parent().attr('id');
				// console.log(get_id);
				var split_id = get_id.split('_');
				var id = parseInt(split_id[1]) + 1;
				var id_bef = split_id[1];

				$.ajax({
					url: base_url + active_controller + '/get_add_mat_joint/' + id,
					cache: false,
					type: "POST",
					dataType: "json",
					success: function(data) {
						$("#addmatjoint_" + id_bef).before(data.header);
						$("#addmatjoint_" + id_bef).remove();
						$('.chosen_select').select2();
						$('.autoNumeric4').autoNumeric('init', {
							mDec: '4',
							aPad: false
						});
						swal.close();
					},
					error: function() {
						swal({
							title: "Error Message !",
							text: 'Connection Time Out. Please try again..',
							type: "warning",
							timer: 3000,
							showCancelButton: false,
							showConfirmButton: false,
							allowOutsideClick: false
						});
					}
				});
			});

			//add part flat sheet
			$(document).on('click', '.addPartFlat', function() {
				// loading_spinner();
				var get_id = $(this).parent().parent().attr('id');
				// console.log(get_id);
				var split_id = get_id.split('_');
				var id = parseInt(split_id[1]) + 1;
				var id_bef = split_id[1];

				$.ajax({
					url: base_url + active_controller + '/get_add_flat_sheet/' + id,
					cache: false,
					type: "POST",
					dataType: "json",
					success: function(data) {
						$("#addflatsheet_" + id_bef).before(data.header);
						$("#addflatsheet_" + id_bef).remove();
						$('.autoNumeric4').autoNumeric('init', {
							mDec: '4',
							aPad: false
						});
						swal.close();
					},
					error: function() {
						swal({
							title: "Error Message !",
							text: 'Connection Time Out. Please try again..',
							type: "warning",
							timer: 3000,
							showCancelButton: false,
							showConfirmButton: false,
							allowOutsideClick: false
						});
					}
				});
			});

			//add part end plate
			$(document).on('click', '.addPartEnd', function() {
				// loading_spinner();
				var get_id = $(this).parent().parent().attr('id');
				// console.log(get_id);
				var split_id = get_id.split('_');
				var id = parseInt(split_id[1]) + 1;
				var id_bef = split_id[1];

				$.ajax({
					url: base_url + active_controller + '/get_add_end_plate/' + id,
					cache: false,
					type: "POST",
					dataType: "json",
					success: function(data) {
						$("#addendplate_" + id_bef).before(data.header);
						$("#addendplate_" + id_bef).remove();
						$('.autoNumeric4').autoNumeric('init', {
							mDec: '4',
							aPad: false
						});
						swal.close();
					},
					error: function() {
						swal({
							title: "Error Message !",
							text: 'Connection Time Out. Please try again..',
							type: "warning",
							timer: 3000,
							showCancelButton: false,
							showConfirmButton: false,
							allowOutsideClick: false
						});
					}
				});
			});

			//add part ukuran jadi
			$(document).on('click', '.addPartJadi', function() {
				// loading_spinner();
				var get_id = $(this).parent().parent().attr('id');
				// console.log(get_id);
				var split_id = get_id.split('_');
				var id = parseInt(split_id[1]) + 1;
				var id_bef = split_id[1];

				$.ajax({
					url: base_url + active_controller + '/get_add_ukuran_jadi/' + id,
					cache: false,
					type: "POST",
					dataType: "json",
					success: function(data) {
						$("#addukuranjadi_" + id_bef).before(data.header);
						$("#addukuranjadi_" + id_bef).remove();
						$('.autoNumeric4').autoNumeric('init', {
							mDec: '4',
							aPad: false
						});
						swal.close();
					},
					error: function() {
						swal({
							title: "Error Message !",
							text: 'Connection Time Out. Please try again..',
							type: "warning",
							timer: 3000,
							showCancelButton: false,
							showConfirmButton: false,
							allowOutsideClick: false
						});
					}
				});
			});

			//add others
			$(document).on('click', '.addPartOthers', function() {
				// loading_spinner();
				var get_id = $(this).parent().parent().attr('id');
				// console.log(get_id);
				var split_id = get_id.split('_');
				var id = parseInt(split_id[1]) + 1;
				var id_bef = split_id[1];

				$.ajax({
					url: base_url + active_controller + '/get_add_others/' + id,
					cache: false,
					type: "POST",
					dataType: "json",
					success: function(data) {
						$("#addothers_" + id_bef).before(data.header);
						$("#addothers_" + id_bef).remove();
						$('.autoNumeric4').autoNumeric('init', {
							mDec: '4',
							aPad: false
						});
						swal.close();
					},
					error: function() {
						swal({
							title: "Error Message !",
							text: 'Connection Time Out. Please try again..',
							type: "warning",
							timer: 3000,
							showCancelButton: false,
							showConfirmButton: false,
							allowOutsideClick: false
						});
					}
				});
			});

			//delete part
			$(document).on('click', '.delPart', function() {
				var get_id = $(this).parent().parent().attr('class');
				$("." + get_id).remove();
			});

			//changeFlat
			$(document).on('keyup', '.changeFlat', function() {
				var length = getNum($(this).parent().parent().find('.length').val().split(',').join(''));
				var width = getNum($(this).parent().parent().find('.width').val().split(',').join(''));
				var m2 = length * width;
				$(this).parent().parent().find('.resultM2').val(m2)
			});

			//changeEnd
			$(document).on('keyup', '.changeEnd', function() {
				var length = getNum($(this).parent().parent().find('.length').val().split(',').join(''));
				var width = getNum($(this).parent().parent().find('.width').val().split(',').join(''));
				var m2 = length * width;
				$(this).parent().parent().find('.resultM2').val(m2)
			});

			//add part
			$(document).on('click', '#back', function() {
				window.location.href = base_url + active_controller;
			});

			$('#save_bom').click(function(e) {
				e.preventDefault();
				var id_product = $('#id_product').val();
				var material = $('.material').val();
				var qty = $('.qty').val();

				if (id_product == '0') {
					swal({
						title: "Error Message!",
						text: 'Product name empty, select first ...',
						type: "warning"
					});

					$('#save_bom').prop('disabled', false);
					return false;
				}
				if (material == '0') {
					swal({
						title: "Error Message!",
						text: 'Material name empty, select first ...',
						type: "warning"
					});

					$('#save_bom').prop('disabled', false);
					return false;
				}
				if (qty == '') {
					swal({
						title: "Error Message!",
						text: 'Weight empty, select first ...',
						type: "warning"
					});

					$('#save_bom').prop('disabled', false);
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
							var formData = new FormData($('#data-form')[0]);
							var baseurl = base_url + active_controller + '/add'
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
											timer: 3000,
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
												timer: 3000,
												showCancelButton: false,
												showConfirmButton: false,
												allowOutsideClick: false
											});
										} else {
											swal({
												title: "Save Failed!",
												text: data.pesan,
												type: "warning",
												timer: 3000,
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

			//add hi grid std
			$(document).on('click', '.addPartHiGrid', function() {
				// loading_spinner();
				var get_id = $(this).parent().parent().attr('id');
				// console.log(get_id);
				var split_id = get_id.split('_');
				var id = parseInt(split_id[1]) + 1;
				var id_bef = split_id[1];

				$.ajax({
					url: base_url + active_controller + '/get_add_hi_grid_std/' + id,
					cache: false,
					type: "POST",
					dataType: "json",
					success: function(data) {
						$("#addhigrid_" + id_bef).before(data.header);
						$("#addhigrid_" + id_bef).remove();
						$('.chosen_select').select2();
						$('.autoNumeric4').autoNumeric('init', {
							mDec: '4',
							aPad: false
						});
						$(".autoNumeric0").autoNumeric('init', {
							mDec: '0',
							aPad: false
						});
						swal.close();
					},
					error: function() {
						swal({
							title: "Error Message !",
							text: 'Connection Time Out. Please try again..',
							type: "warning",
							timer: 3000,
							showCancelButton: false,
							showConfirmButton: false,
							allowOutsideClick: false
						});
					}
				});
			});

			$(document).on('click', '.addSubPart', function() {
				// loading_spinner();
				var get_id = $(this).parent().parent().attr('id');
				// console.log(get_id);
				var split_id = get_id.split('_');
				var id = split_id[1];
				var id2 = parseInt(split_id[2]) + 1;
				var id_bef = split_id[2];

				$.ajax({
					url: base_url + active_controller + '/get_add_sub_ukuran_jadi/' + id + '/' + id2,
					cache: false,
					type: "POST",
					dataType: "json",
					success: function(data) {
						$("#addhigrid_" + id + "_" + id_bef).before(data.header);
						$("#addhigrid_" + id + "_" + id_bef).remove();
						$('.chosen-select').select2();
						$('.autoNumeric').autoNumeric();
						swal.close();
					},
					error: function() {
						swal({
							title: "Error Message !",
							text: 'Connection Time Out. Please try again..',
							type: "warning",
							timer: 3000,
						});
					}
				});
			});

			$(document).on('click', '.addSubPartMatCut', function() {
				// loading_spinner();
				var get_id = $(this).parent().parent().attr('id');
				// console.log(get_id);
				var split_id = get_id.split('_');
				var id = split_id[1];
				var id2 = parseInt(split_id[2]) + 1;
				var id_bef = split_id[2];

				$.ajax({
					url: base_url + active_controller + '/get_add_sub_cutting_material/' + id + '/' + id2,
					cache: false,
					type: "POST",
					dataType: "json",
					success: function(data) {
						$("#addhigridcutting_" + id + "_" + id_bef).before(data.header);
						$("#addhigridcutting_" + id + "_" + id_bef).remove();
						$('.chosen-select').select2();
						$('.autoNumeric4').autoNumeric('init', {
							mDec: '4',
							aPad: false
						});
						swal.close();
					},
					error: function() {
						swal({
							title: "Error Message !",
							text: 'Connection Time Out. Please try again..',
							type: "warning",
							timer: 3000,
						});
					}
				});
			});

			$(document).on('change', '.changeFungsiHiGrid', function() {
				// loading_spinner();
				var id = $(this).val();
				var id_row = $(this).data('id');

				$.ajax({
					url: base_url + active_controller + '/get_add_hi_grid_std_breakdown/' + id + '/' + id_row,
					cache: false,
					type: "POST",
					dataType: "json",
					success: function(data) {
						$(".higridMat" + id_row).html(data.material);
						$('.chosen_select').select2();
						$('.autoNumeric4').autoNumeric('init', {
							mDec: '4',
							aPad: false
						});
						swal.close();
					},
					error: function() {
						swal({
							title: "Error Message !",
							text: 'Connection Time Out. Please try again..',
							type: "warning",
							timer: 3000,
							showCancelButton: false,
							showConfirmButton: false,
							allowOutsideClick: false
						});
					}
				});
			});

			$(document).on('click', '.delSubPart', function() {
				var get_id = $(this).parent().parent('tr').html();
				$(this).parent().parent('tr').remove();
			});

			$(document).on('click', '.addSubPartMat', function() {
				// loading_spinner();
				var get_id = $(this).parent().parent().attr('id');
				// console.log(get_id);
				var split_id = get_id.split('_');
				var id = split_id[1];
				var id2 = parseInt(split_id[2]) + 1;
				var id_bef = split_id[2];

				var label_name = $(this).data('label_name')
				var label_class = $(this).data('label_class')

				$.ajax({
					url: base_url + active_controller + '/get_add_sub_material',
					cache: false,
					type: "POST",
					data: {
						'id': id,
						'no': id2,
						'label_name': label_name,
						'label_class': label_class
					},
					dataType: "json",
					success: function(data) {
						$("#" + label_class + "_" + id + "_" + id_bef).before(data.header);
						$("#" + label_class + "_" + id + "_" + id_bef).remove();
						$('.chosen-select').select2();
						$('.autoNumeric4').autoNumeric('init', {
							mDec: '4',
							aPad: false
						});
						swal.close();
					},
					error: function() {
						swal({
							title: "Error Message !",
							text: 'Connection Time Out. Please try again..',
							type: "warning",
							timer: 3000,
						});
					}
				});
			});

			//new
			$(document).on('click', '#addProductMaster', function() {
				$("#head_title").html("<b>Product Master</b>");
				$.ajax({
					type: 'POST',
					url: siteurl + 'product_master/add/',
					success: function(data) {
						$("#dialog-popup").modal();
						$("#ModalView").html(data);

					}
				})
			});

			$(document).on('change', '#code_lv1', function() {
				var code_lv1 = $("#code_lv1").val();

				$.ajax({
					url: siteurl + 'product_master/get_list_level1',
					method: "POST",
					data: {
						code_lv1: code_lv1
					},
					dataType: 'json',
					success: function(data) {
						$('#code_lv2').html(data.option);
						$('#code_lv3').html("<option value='0'>List Empty</option>");
					}
				});
			});

			$(document).on('change', '#code_lv2', function() {
				var code_lv1 = $("#code_lv1").val();
				var code_lv2 = $("#code_lv2").val();

				$.ajax({
					url: siteurl + 'product_master/get_list_level3',
					method: "POST",
					data: {
						code_lv1: code_lv1,
						code_lv2: code_lv2
					},
					dataType: 'json',
					success: function(data) {
						$('#code_lv3').html(data.option);
					}
				});
			});

			$(document).on('change', '#code_lv3', function() {
				var code_lv1 = $("#code_lv1").val();
				var code_lv2 = $("#code_lv2").val();
				var code_lv3 = $("#code_lv3").val();

				$.ajax({
					url: siteurl + 'product_master/get_list_level4_name',
					method: "POST",
					data: {
						code_lv1: code_lv1,
						code_lv2: code_lv2,
						code_lv3: code_lv3
					},
					dataType: 'json',
					success: function(data) {
						$('#nama').val(data.nama);
						$('#code').val(data.code);
					}
				});
			});

			$(document).on('click', '#updateManualCode', function() {
				var code_lv1 = $("#code_lv1").val();
				var code_lv2 = $("#code_lv2").val();
				var code_lv3 = $("#code_lv3").val();

				$.ajax({
					url: siteurl + 'product_master/get_list_level4_name',
					method: "POST",
					data: {
						code_lv1: code_lv1,
						code_lv2: code_lv2,
						code_lv3: code_lv3
					},
					dataType: 'json',
					success: function(data) {
						$('#code').val(data.code);
					}
				});
			});

			$(document).on('keyup', '.getCub', function() {
				get_cub();
			});


			$(document).on('click', '#save', function(e) {
				e.preventDefault()

				var code_lv1 = $('#code_lv1').val();
				var code_lv2 = $('#code_lv2').val();
				var code_lv3 = $('#code_lv3').val();

				if (code_lv1 == '0') {
					swal({
						title: "Error Message!",
						text: 'Product type not selected...',
						type: "warning"
					});
					return false;
				}
				if (code_lv2 == '0') {
					swal({
						title: "Error Message!",
						text: 'Product category not selected...',
						type: "warning"
					});
					return false;
				}
				if (code_lv3 == '0') {
					swal({
						title: "Error Message!",
						text: 'Product jenis not selected...',
						type: "warning"
					});
					return false;
				}
				// alert(data);

				swal({
						title: "Anda Yakin?",
						text: "Data akan diproses!",
						type: "warning",
						showCancelButton: true,
						confirmButtonClass: "btn-info",
						confirmButtonText: "Yes",
						cancelButtonText: "No",
						closeOnConfirm: false
					},
					function() {
						// var form_data = $('#data_form_master_product').serialize();
						var form_data = new FormData($('#data_form_master_product')[0]);
						$.ajax({
							type: 'POST',
							url: siteurl + 'product_master/add',
							dataType: "json",
							data: form_data,
							processData: false,
							contentType: false,
							success: function(data) {
								if (data.status == '1') {
									swal({
											title: "Sukses",
											text: data.pesan,
											type: "success"
										},
										function() {
											window.location.reload(true);
										})
								} else {
									swal({
										title: "Error",
										text: data.pesan,
										type: "error"
									})

								}
							},
							error: function() {
								swal({
									title: "Error",
									text: "Error proccess !",
									type: "error"
								})
							}
						})
					});

			})

		});

		function get_cub() {
			var l = getNum($('#length').val().split(",").join(""));
			var w = getNum($('#wide').val().split(",").join(""));
			var h = getNum($('#high').val().split(",").join(""));
			var cub = (l * w * h) / 1000000000;

			$('#cub').val(cub.toFixed(7));
		}
	</script>