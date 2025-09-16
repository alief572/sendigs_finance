<?php
$code_lv4 = $getData[0]['code_lv4'];
$nama_product = $getData[0]['nama'];
$qty_order = $getHeader[0]['propose'];
$id_customer = $getHeader[0]['id_customer'];
$project = $getHeader[0]['project'];
$so_customer = $getHeader[0]['so_customer'];
$id_uniq = $getHeader[0]['id'];
$no_bom = $getHeader[0]['no_bom'];
$propose = $getHeader[0]['propose'];

?>
<div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post" autocomplete="off"><br>
        <input type="hidden" name='id_uniq' id='id_uniq' class='form-control' value='<?=$id_uniq;?>' readonly>
		<table width='100%' >
			<tr>
				<td class='text-bold' width='15%'>Product Name</td>
				<td width='1%'>:</td>
				<td><?=$nama_product;?></td>
			</tr>
			<tr>
				<td class='text-bold'>Propose</td>
				<td>:</td>
				<td><?=$propose;?></td>
			</tr>
		</table>
		<hr>
        <h4 class='box-title'>A. BOM Single Product</h4>
		<table class='table table-bordered table-sm' width='100%'>
			<thead>
				<tr>
					<th class='text-center' width='5%'>#</th>
					<th>Nama Product</th>
					<th class='text-center' width='10%'>Kebutuhan</th>
				</tr>
			</thead>
			<tbody>
			<?php
				$nomor = 0;
				$ArrBOM = [];
				$listStock = [];
				if(!empty($detail_hi_grid)){
					foreach($detail_hi_grid AS $val => $valx){ $nomor++;
						$namaBOM = (!empty($getNameBOMProduct[$valx['code_material']]))?$getNameBOMProduct[$valx['code_material']]:'';
						$Qty = $valx['qty'] * $propose;
						$ArrBOM[] = $valx['code_material'];
						echo "<tr>";
							echo "<td class='text-center'>".$nomor."</td>";
							echo "<td>".$namaBOM."</td>";
							echo "<td class='text-center'>".$Qty."</td>";
						echo "</tr>";
					}
					$listStock = $this->db->select('a.*,
													(SELECT actual_stock FROM stock_product WHERE id = MAX(a.id)) AS stock_akhir,
              										(SELECT booking_stock FROM stock_product WHERE id = MAX(a.id)) AS booking_akhir,
												')->where_in('no_bom',$ArrBOM)->get_where('stock_product a',array('a.deleted_date'=>NULL))->result_array();

				}

				
			?>
			<tbody>
		</table>
		<table class='table table-bordered table-sm' width='100%'>
			<thead>
				<tr>
					<th></th>
					<td colspan='3' class='text-bold'>LIST STOCK SINGLE PRODUCT</td>
				</tr>
				<tr>
					<th class='text-center' width='5%'>#</th>
					<th>Nama Product</th>
					<th class='text-center' width='7%'>Stock</th>
					<th class='text-center' width='10%'>Qty</th>
				</tr>
			</thead>
			<tbody>
			<?php
				$nomor = 0;
				$keyNumber = 0;
				$ArrBOM = [];
				if(!empty($listStock)){
					foreach($listStock AS $val => $valx){ $nomor++; $keyNumber++;
						$namaBOM = (!empty($getNameBOMProduct[$valx['no_bom']]))?$getNameBOMProduct[$valx['no_bom']]:'';
						$Qty = $valx['stock_akhir'] - $valx['booking_akhir'];
						echo "<tr>";
							echo "<td class='text-center'>".$nomor."
								<input type='hidden' name='detail[".$keyNumber."][id_master]' value='".$valx['id']."'>
								<input type='hidden' name='detail[".$keyNumber."][key_hub]' value='".$keyNumber."'>
								<input type='hidden' name='detail[".$keyNumber."][category]' value='single product'>
								<input type='hidden' name='detail[".$keyNumber."][kode_barang]' value='".$valx['no_bom']."'>
								<input type='hidden' name='detail[".$keyNumber."][nama_barang]' value='".$namaBOM."'>
								<input type='hidden' name='detail[".$keyNumber."][est]' value='".$Qty."'>
								</td>";
							echo "<td>".$namaBOM."</td>";
							echo "<td class='text-center'>".$Qty."</td>";
							echo "<td class='text-center'><input type='input' name='detail[".$keyNumber."][qty]' class='form-control input-sm text-center'></td>";
						echo "</tr>";
					}
				}
			?>
			<tbody>
		</table>

		<h4 class='box-title'>B. Cutting Plan</h4>
		<table class='table table-bordered table-sm' width='100%'>
			<thead>
				<tr>
					<th class='text-center' width='5%'>#</th>
					<th>Nama Product</th>
					<th class='text-center' width='7%'>Length</th>
					<th class='text-center' width='7%'>Width</th>
					<th class='text-center' width='10%'>Kebutuhan</th>
				</tr>
			</thead>
			<tbody>
			<?php
				$nomor = 0;
				$ArrBOM = [];
				$listStock = [];
				if(!empty($detail_hi_grid_cut)){
					foreach($detail_hi_grid_cut AS $val => $valx){ $nomor++;
						$namaBOM = (!empty($getNameBOMProduct[$valx['code_material']]))?$getNameBOMProduct[$valx['code_material']]:'';
						$Qty = $valx['qty'];
						$ArrBOM[] = $valx['code_material'];
						echo "<tr>";
							echo "<td class='text-center'>".$nomor."</td>";
							echo "<td>".$namaBOM."</td>";
							echo "<td class='text-center'>".number_format($valx['length'])."</td>";
							echo "<td class='text-center'>".number_format($valx['width'])."</td>";
							echo "<td class='text-center'>".number_format($Qty)."</td>";
						echo "</tr>";
					}
				}
			?>
			<tbody>
		</table>
		<table class='table table-bordered table-sm' width='100%'>
			<thead>
				<tr>
					<th></th>
					<th colspan='4' class='text-bold'>LIST STOCK CUTTING</th>
				</tr>
				<tr>
					<th class='text-center' width='5%'>#</th>
					<th>Nama Product</th>
					<th class='text-center' width='7%'>No SPK</th>
					<th class='text-center' width='7%'>Length</th>
					<th class='text-center' width='7%'>Width</th>
					<th class='text-center' width='7%'>Stock</th>
					<th class='text-center' width='10%'>Qty</th>
				</tr>
			</thead>
			<tbody>
			<?php
				$nomor = 0;
				$ArrBOM = [];
				$listStock = [];
				if(!empty($detail_hi_grid_cut)){
					foreach($detail_hi_grid_cut AS $val => $valx){ 
						$no_bom = $valx['code_material'];
						$Qty 	= $valx['qty'];
						$getStockCutting = $this->db->select('a.*, COUNT(a.id) AS qty_cut')->group_by('a.id_key_spk')->get_where('so_spk_cutting_product a',array('a.no_bom'=>$no_bom,'a.length'=>$valx['length'],'a.width'=>$valx['width'],'a.status <>'=>'N'))->result_array();
						$namaBOM = (!empty($getNameBOMProduct[$valx['code_material']]))?$getNameBOMProduct[$valx['code_material']]:'';
						foreach ($getStockCutting as $key => $value) {$nomor++; $keyNumber++;
							echo "<tr>";
								echo "<td class='text-center'>".$nomor."
									<input type='hidden' name='detail[".$keyNumber."][id_master]' value='".$value['id']."'>
									<input type='hidden' name='detail[".$keyNumber."][key_hub]' value='".$keyNumber."'>
									<input type='hidden' name='detail[".$keyNumber."][category]' value='cutting product'>
									<input type='hidden' name='detail[".$keyNumber."][kode_barang]' value='".$valx['code_material']."'>
									<input type='hidden' name='detail[".$keyNumber."][nama_barang]' value='".$namaBOM."'>
									<input type='hidden' name='detail[".$keyNumber."][est]' value='".$value['qty_cut']."'>
									<input type='hidden' name='detail[".$keyNumber."][length]' value='".$value['length']."'>
									<input type='hidden' name='detail[".$keyNumber."][width]' value='".$value['width']."'>
									</td>";
								echo "<td>".$namaBOM."</td>";
								echo "<td class='text-center'>".$value['no_spk']."</td>";
								echo "<td class='text-center'>".number_format($value['length'])."</td>";
								echo "<td class='text-center'>".number_format($value['width'])."</td>";
								echo "<td class='text-center'>".number_format($value['qty_cut'])."</td>";
								echo "<td class='text-center'><input type='input' name='detail[".$keyNumber."][qty]' class='form-control input-sm text-center'></td>";
							echo "</tr>";
						}

						$resultCuttingMaterial = $this->db->get_where('bom_detail_custom',array('category'=>'material cutting','no_bom_detail'=>$valx['no_bom_detail']))->result_array();
						// echo $this->db->last_query().'<br>';
						$materialList    = get_list_inventory_lv4('material');
						if(!empty($resultCuttingMaterial)){
							echo "<tr>";
								echo "<th class='text-center'></th>";
								echo "<th>Nama Material</th>";
								echo "<th class='text-center'></th>";
								echo "<th class='text-center'></th>";
								echo "<th class='text-center'></th>";
								echo "<th class='text-center'>Weight</th>";
								echo "<th class='text-center'></th>";
							echo "</tr>";
							foreach ($resultCuttingMaterial as $key => $value) { $keyNumber++;
								$namaMaterial = (!empty($materialList[$value['code_material']]['nama']))?$materialList[$value['code_material']]['nama']:'';
								echo "<tr>";
									echo "<td class='text-center'>
									<input type='hidden' name='detail[".$keyNumber."][id_master]' value='".$value['id']."'>
									<input type='hidden' name='detail[".$keyNumber."][key_hub]' value='".$keyNumber."'>
									<input type='hidden' name='detail[".$keyNumber."][category]' value='cutting material'>
									<input type='hidden' name='detail[".$keyNumber."][kode_barang]' value='".$value['code_material']."'>
									<input type='hidden' name='detail[".$keyNumber."][nama_barang]' value='".$namaBOM."'>
									<input type='hidden' name='detail[".$keyNumber."][est]' value='".$value['weight']."'>
									</td>";
									echo "<td>".$namaMaterial."</td>";
									echo "<td class='text-center'></td>";
									echo "<td class='text-center'></td>";
									echo "<td class='text-center'></td>";
									echo "<td class='text-center'>".number_format($value['weight'],4)."</td>";
									echo "<td class='text-center'><input type='input' name='detail[".$keyNumber."][qty]' class='form-control input-sm text-center'></td>";
								echo "</tr>";
							}
						}
					}
				}
			?>
			<tbody>
		</table>
        
		<h4 class='box-title'>C. Accessories</h4>
		<table class='table table-bordered table-sm' width='100%'>
			<thead>
				<tr>
					<th class='text-center' width='5%'>#</th>
					<th>Nama Accessories</th>
					<th class='text-center' width='21%' colspan='3'>Keterangan</th>
					<th class='text-center' width='7%'>Kebutuhan</th>
					<th class='text-center' width='10%'>Qty</th>
				</tr>
			</thead>
			<tbody>
			<?php
				$nomor = 0;
				$GET_ACC = get_accessories();
				if(!empty($detail_accessories)){
					foreach($detail_accessories AS $val => $valx){ $nomor++; $keyNumber++;
						$namaBOM = (!empty($GET_ACC[$valx['code_material']]['nama_full']))?$GET_ACC[$valx['code_material']]['nama_full']:'';
						echo "<tr>";
							echo "<td class='text-center'>".$nomor."
								<input type='hidden' name='detail[".$keyNumber."][id_master]' value='".$valx['id']."'>
								<input type='hidden' name='detail[".$keyNumber."][key_hub]' value='".$keyNumber."'>
								<input type='hidden' name='detail[".$keyNumber."][category]' value='accessories'>
								<input type='hidden' name='detail[".$keyNumber."][kode_barang]' value='".$valx['code_material']."'>
								<input type='hidden' name='detail[".$keyNumber."][nama_barang]' value='".$namaBOM."'>
								<input type='hidden' name='detail[".$keyNumber."][est]' value='".$valx['weight']."'>
								<input type='hidden' name='detail[".$keyNumber."][ket]' value='".$valx['ket']."'>
								</td>";
							echo "<td>".$namaBOM."</td>";
							echo "<td class='text-left' colspan='3'>".$valx['ket']."</td>";
							echo "<td class='text-center'>".number_format($valx['weight'])."</td>";
							echo "<td class='text-center'><input type='input' name='detail[".$keyNumber."][qty]' class='form-control input-sm text-center'></td>";
						echo "</tr>";
					}
				}
			?>
			<tbody>
		</table>
		<h4 class='box-title'>D. Material Joint & Finishing</h4>
		<table class='table table-bordered table-sm' width='100%'>
			<thead>
				<tr>
					<th class='text-center' width='5%'>#</th>
					<th>Material Name</th>
					<th class='text-center' width='7%'>Layer</th>
					<th class='text-center' width='21%' colspan='2'>Keterangan</th>
					<th class='text-center' width='7%'>Weight</th>
					<th class='text-center' width='10%'>Qty</th>
				</tr>
			</thead>
			<tbody>
			<?php
				$nomor = 0;
				$materialList    = get_list_inventory_lv4('material');
				if(!empty($detail_mat_joint)){
					foreach($detail_mat_joint AS $val => $valx){ $nomor++; $keyNumber++;
						$namaMaterial = (!empty($materialList[$valx['code_material']]['nama']))?$materialList[$valx['code_material']]['nama']:'';
						echo "<tr>";
							echo "<td class='text-center'>".$nomor."
								<input type='hidden' name='detail[".$keyNumber."][id_master]' value='".$valx['id']."'>
								<input type='hidden' name='detail[".$keyNumber."][key_hub]' value='".$keyNumber."'>
								<input type='hidden' name='detail[".$keyNumber."][category]' value='material joint'>
								<input type='hidden' name='detail[".$keyNumber."][kode_barang]' value='".$valx['code_material']."'>
								<input type='hidden' name='detail[".$keyNumber."][nama_barang]' value='".$namaMaterial."'>
								<input type='hidden' name='detail[".$keyNumber."][est]' value='".$valx['weight']."'>
								<input type='hidden' name='detail[".$keyNumber."][ket]' value='".$valx['ket']."'>
								<input type='hidden' name='detail[".$keyNumber."][layer]' value='".$valx['layer']."'>
								</td>";
							echo "<td>".$namaMaterial."</td>";
							echo "<td class='text-left'>".$valx['layer']."</td>";
							echo "<td class='text-left' colspan='2'>".$valx['ket']."</td>";
							echo "<td class='text-center'>".number_format($valx['weight'],4)."</td>";
							echo "<td class='text-center'><input type='input' name='detail[".$keyNumber."][qty]' class='form-control input-sm text-center'></td>";
						echo "</tr>";
					}
				}
			?>
			<tbody>
		</table>
		<h4 class='box-title'>E. Flat Sheet</h4>
		<table class='table table-bordered table-sm' width='100%'>
			<thead>
				<tr>
					<th class='text-center' width='5%'>#</th>
					<th>Nama Product</th>
					<th class='text-center' width='7%'></th>
					<th class='text-center' width='7%'>Length</th>
					<th class='text-center' width='7%'>Width</th>
					<th class='text-center' width='7%'>Est</th>
					<th class='text-center' width='10%'>Qty</th>
				</tr>
			</thead>
			<tbody>
			<?php
				$nomor = 0;
				$ArrBOM = [];
				$listStock = [];
				if(!empty($detail_flat_sheet)){
					foreach($detail_flat_sheet AS $val => $valx){ $nomor++; $keyNumber++;
						echo "<tr>";
							echo "<td class='text-center'>".$nomor."
									<input type='hidden' name='detail[".$keyNumber."][id_master]' value='".$valx['id']."'>
									<input type='hidden' name='detail[".$keyNumber."][key_hub]' value='".$keyNumber."'>
									<input type='hidden' name='detail[".$keyNumber."][category]' value='flat sheet product'>
									<input type='hidden' name='detail[".$keyNumber."][kode_barang]' value=''>
									<input type='hidden' name='detail[".$keyNumber."][nama_barang]' value=''>
									<input type='hidden' name='detail[".$keyNumber."][est]' value='".$valx['qty']."'>
									<input type='hidden' name='detail[".$keyNumber."][length]' value='".$valx['length']."'>
									<input type='hidden' name='detail[".$keyNumber."][width]' value='".$valx['width']."'>
									</td>";
							echo "<td>Flat Sheet</td>";
							echo "<td class='text-center'></td>";
							echo "<td class='text-center'>".number_format($valx['length'])."</td>";
							echo "<td class='text-center'>".number_format($valx['width'])."</td>";
							echo "<td class='text-center'>".number_format($valx['qty'])."</td>";
							echo "<td class='text-center'><input type='input' name='detail[".$keyNumber."][qty]' class='form-control input-sm text-center'></td>";
						echo "</tr>";

						$resultCuttingMaterial = $this->db->get_where('bom_detail_custom',array('category'=>'material flat sheet','no_bom_detail'=>$valx['no_bom_detail']))->result_array();
						$materialList    = get_list_inventory_lv4('material');
						if(!empty($resultCuttingMaterial)){
							echo "<tr>";
								echo "<th class='text-center'></th>";
								echo "<th>Nama Material</th>";
								echo "<th class='text-center'></th>";
								echo "<th class='text-center'></th>";
								echo "<th class='text-center'></th>";
								echo "<th class='text-center'>Weight</th>";
								echo "<th class='text-center'></th>";
							echo "</tr>";
							foreach ($resultCuttingMaterial as $key => $value) { $keyNumber++;
								$namaMaterial = (!empty($materialList[$value['code_material']]['nama']))?$materialList[$value['code_material']]['nama']:'';
								echo "<tr>";
									echo "<td class='text-center'>";
									echo "<input type='hidden' name='detail[".$keyNumber."][id_master]' value='".$value['id']."'>";
									echo "<input type='hidden' name='detail[".$keyNumber."][key_hub]' value='".$keyNumber."'>";
									echo "<input type='hidden' name='detail[".$keyNumber."][category]' value='flat sheet material'>";
									echo "<input type='hidden' name='detail[".$keyNumber."][kode_barang]' value='".$value['code_material']."'>";
									echo "<input type='hidden' name='detail[".$keyNumber."][nama_barang]' value='".$namaMaterial."'>";
									echo "<input type='hidden' name='detail[".$keyNumber."][est]' value='".$value['weight']."'>";
									echo "</td>";
									echo "<td>".$namaMaterial."</td>";
									echo "<td class='text-center'></td>";
									echo "<td class='text-center'></td>";
									echo "<td class='text-center'></td>";
									echo "<td class='text-center'>".number_format($value['weight'],4)."</td>";
									echo "<td class='text-center'><input type='input' name='detail[".$keyNumber."][qty]' class='form-control input-sm text-center'></td>";
								echo "</tr>";
							}
						}
					}
				}
			?>
			<tbody>
		</table>
		<h4 class='box-title'>F. End Plate / Kick Plate</h4>
		<table class='table table-bordered table-sm' width='100%'>
			<thead>
				<tr>
					<th class='text-center' width='5%'>#</th>
					<th>Nama Product</th>
					<th class='text-center' width='7%'></th>
					<th class='text-center' width='7%'>Length</th>
					<th class='text-center' width='7%'>Width</th>
					<th class='text-center' width='7%'>Est</th>
					<th class='text-center' width='10%'>Qty</th>
				</tr>
			</thead>
			<tbody>
			<?php
				$nomor = 0;
				$ArrBOM = [];
				$listStock = [];
				if(!empty($detail_end_plate)){
					foreach($detail_end_plate AS $val => $valx){ $nomor++; $keyNumber++;
						echo "<tr>";
							echo "<td class='text-center'>".$nomor."
								<input type='hidden' name='detail[".$keyNumber."][id_master]' value='".$valx['id']."'>
								<input type='hidden' name='detail[".$keyNumber."][key_hub]' value='".$keyNumber."'>
								<input type='hidden' name='detail[".$keyNumber."][category]' value='end plate product'>
								<input type='hidden' name='detail[".$keyNumber."][kode_barang]' value=''>
								<input type='hidden' name='detail[".$keyNumber."][nama_barang]' value=''>
								<input type='hidden' name='detail[".$keyNumber."][est]' value='".$valx['qty']."'>
								<input type='hidden' name='detail[".$keyNumber."][length]' value='".$valx['length']."'>
								<input type='hidden' name='detail[".$keyNumber."][width]' value='".$valx['width']."'>
							</td>";
							echo "<td>End Plate / Kick Plate</td>";
							echo "<td class='text-center'></td>";
							echo "<td class='text-center'>".number_format($valx['length'])."</td>";
							echo "<td class='text-center'>".number_format($valx['width'])."</td>";
							echo "<td class='text-center'>".number_format($valx['qty'])."</td>";
							echo "<td class='text-center'><input type='input' name='detail[".$keyNumber."][qty]' class='form-control input-sm text-center'></td>";
						echo "</tr>";

						$resultCuttingMaterial = $this->db->get_where('bom_detail_custom',array('category'=>'material end plate','no_bom_detail'=>$valx['no_bom_detail']))->result_array();
						$materialList    = get_list_inventory_lv4('material');
						if(!empty($resultCuttingMaterial)){
							echo "<tr>";
								echo "<th class='text-center'></th>";
								echo "<th>Nama Material</th>";
								echo "<th class='text-center'></th>";
								echo "<th class='text-center'></th>";
								echo "<th class='text-center'></th>";
								echo "<th class='text-center'>Weight</th>";
								echo "<th class='text-center'></th>";
							echo "</tr>";
							foreach ($resultCuttingMaterial as $key => $value) { $keyNumber++;
								$namaMaterial = (!empty($materialList[$value['code_material']]['nama']))?$materialList[$value['code_material']]['nama']:'';
								echo "<tr>";
									echo "<td class='text-center'>";
									echo "<input type='hidden' name='detail[".$keyNumber."][id_master]' value='".$value['id']."'>";
									echo "<input type='hidden' name='detail[".$keyNumber."][key_hub]' value='".$keyNumber."'>";
									echo "<input type='hidden' name='detail[".$keyNumber."][category]' value='end plate material'>";
									echo "<input type='hidden' name='detail[".$keyNumber."][kode_barang]' value='".$value['code_material']."'>";
									echo "<input type='hidden' name='detail[".$keyNumber."][nama_barang]' value='".$namaMaterial."'>";
									echo "<input type='hidden' name='detail[".$keyNumber."][est]' value='".$value['weight']."'>";
									echo "</td>";
									echo "<td>".$namaMaterial."</td>";
									echo "<td class='text-center'></td>";
									echo "<td class='text-center'></td>";
									echo "<td class='text-center'></td>";
									echo "<td class='text-center'>".number_format($value['weight'],4)."</td>";
									echo "<td class='text-center'><input type='input' name='detail[".$keyNumber."][qty]' class='form-control input-sm text-center'></td>";
								echo "</tr>";
							}
						}
					}
				}
			?>
			<tbody>
		</table>
		<h4 class='box-title'>G. Chequered Plate</h4>
		<table class='table table-bordered table-sm' width='100%'>
			<thead>
				<tr>
					<th class='text-center' width='5%'>#</th>
					<th>Nama Product</th>
					<th class='text-center' width='7%'></th>
					<th class='text-center' width='7%'>Length</th>
					<th class='text-center' width='7%'>Width</th>
					<th class='text-center' width='7%'>Est</th>
					<th class='text-center' width='10%'>Qty</th>
				</tr>
			</thead>
			<tbody>
			<?php
				$nomor = 0;
				$ArrBOM = [];
				$listStock = [];
				if(!empty($detail_ukuran_jadi)){
					foreach($detail_ukuran_jadi AS $val => $valx){ $nomor++; $keyNumber++;
						echo "<tr>";
							echo "<td class='text-center'>".$nomor."
									<input type='hidden' name='detail[".$keyNumber."][id_master]' value='".$valx['id']."'>
									<input type='hidden' name='detail[".$keyNumber."][key_hub]' value='".$keyNumber."'>
									<input type='hidden' name='detail[".$keyNumber."][category]' value='chequered plate product'>
									<input type='hidden' name='detail[".$keyNumber."][kode_barang]' value=''>
									<input type='hidden' name='detail[".$keyNumber."][nama_barang]' value=''>
									<input type='hidden' name='detail[".$keyNumber."][est]' value='".$valx['qty']."'>
									<input type='hidden' name='detail[".$keyNumber."][length]' value='".$valx['length']."'>
									<input type='hidden' name='detail[".$keyNumber."][width]' value='".$valx['width']."'>
									</td>";
							echo "<td>Chequered Plate</td>";
							echo "<td class='text-center'></td>";
							echo "<td class='text-center'>".number_format($valx['length'])."</td>";
							echo "<td class='text-center'>".number_format($valx['width'])."</td>";
							echo "<td class='text-center'>".number_format($valx['qty'])."</td>";
							echo "<td class='text-center'><input type='input' name='detail[".$keyNumber."][qty]' class='form-control input-sm text-center'></td>";
						echo "</tr>";

						$resultCuttingMaterial = $this->db->get_where('bom_detail_custom',array('category'=>'material ukuran jadi','no_bom_detail'=>$valx['no_bom_detail']))->result_array();
						$materialList    = get_list_inventory_lv4('material');
						if(!empty($resultCuttingMaterial)){
							echo "<tr>";
								echo "<th class='text-center'></th>";
								echo "<th>Nama Material</th>";
								echo "<th class='text-center'></th>";
								echo "<th class='text-center'></th>";
								echo "<th class='text-center'></th>";
								echo "<th class='text-center'>Weight</th>";
								echo "<th class='text-center'></th>";
							echo "</tr>";
							foreach ($resultCuttingMaterial as $key => $value) { $keyNumber++;
								$namaMaterial = (!empty($materialList[$value['code_material']]['nama']))?$materialList[$value['code_material']]['nama']:'';
								echo "<tr>";
									echo "<td class='text-center'>";
									echo "<input type='hidden' name='detail[".$keyNumber."][id_master]' value='".$value['id']."'>";
									echo "<input type='hidden' name='detail[".$keyNumber."][key_hub]' value='".$keyNumber."'>";
									echo "<input type='hidden' name='detail[".$keyNumber."][category]' value='chequered plate material'>";
									echo "<input type='hidden' name='detail[".$keyNumber."][kode_barang]' value='".$value['code_material']."'>";
									echo "<input type='hidden' name='detail[".$keyNumber."][nama_barang]' value='".$namaMaterial."'>";
									echo "<input type='hidden' name='detail[".$keyNumber."][est]' value='".$value['weight']."'>";
									echo "</td>";
									echo "<td>".$namaMaterial."</td>";
									echo "<td class='text-center'></td>";
									echo "<td class='text-center'></td>";
									echo "<td class='text-center'></td>";
									echo "<td class='text-center'>".number_format($value['weight'],4)."</td>";
									echo "<td class='text-center'><input type='input' name='detail[".$keyNumber."][qty]' class='form-control input-sm text-center'></td>";
								echo "</tr>";
							}
						}
					}
				}
			?>
			<tbody>
		</table>
		<h4 class='box-title'>H. Others</h4>
		<table class='table table-bordered table-sm' width='100%'>
			<thead>
				<tr>
					<th class='text-center' width='5%'>#</th>
					<th>Nama Product</th>
					<th class='text-center' width='7%'></th>
					<th class='text-center' width='7%'>Length</th>
					<th class='text-center' width='7%'>Width</th>
					<th class='text-center' width='7%'>Est</th>
					<th class='text-center' width='10%'>Qty</th>
				</tr>
			</thead>
			<tbody>
			<?php
				$nomor = 0;
				$ArrBOM = [];
				$listStock = [];
				if(!empty($detail_others)){
					foreach($detail_others AS $val => $valx){ $nomor++; $keyNumber++;
						echo "<tr>";
							echo "<td class='text-center'>".$nomor."
								<input type='hidden' name='detail[".$keyNumber."][id_master]' value='".$valx['id']."'>
								<input type='hidden' name='detail[".$keyNumber."][key_hub]' value='".$keyNumber."'>
								<input type='hidden' name='detail[".$keyNumber."][category]' value='others product'>
								<input type='hidden' name='detail[".$keyNumber."][kode_barang]' value=''>
								<input type='hidden' name='detail[".$keyNumber."][nama_barang]' value=''>
								<input type='hidden' name='detail[".$keyNumber."][est]' value='".$valx['qty']."'>
								<input type='hidden' name='detail[".$keyNumber."][length]' value='".$valx['length']."'>
								<input type='hidden' name='detail[".$keyNumber."][width]' value='".$valx['width']."'>
								</td>";
							echo "<td>Others</td>";
							echo "<td class='text-center'></td>";
							echo "<td class='text-center'>".number_format($valx['length'])."</td>";
							echo "<td class='text-center'>".number_format($valx['width'])."</td>";
							echo "<td class='text-center'>".number_format($valx['qty'])."</td>";
							echo "<td class='text-center'><input type='input' name='detail[".$keyNumber."][qty]' class='form-control input-sm text-center'></td>";
						echo "</tr>";

						$resultCuttingMaterial = $this->db->get_where('bom_detail_custom',array('category'=>'material others','no_bom_detail'=>$valx['no_bom_detail']))->result_array();
						$materialList    = get_list_inventory_lv4('material');
						if(!empty($resultCuttingMaterial)){
							echo "<tr>";
								echo "<th class='text-center'></th>";
								echo "<th>Nama Material</th>";
								echo "<th class='text-center'></th>";
								echo "<th class='text-center'></th>";
								echo "<th class='text-center'></th>";
								echo "<th class='text-center'>Weight</th>";
								echo "<th class='text-center'></th>";
							echo "</tr>";
							foreach ($resultCuttingMaterial as $key => $value) { $keyNumber++;
								$namaMaterial = (!empty($materialList[$value['code_material']]['nama']))?$materialList[$value['code_material']]['nama']:'';
								echo "<tr>";
									echo "<td class='text-center'>";
									echo "<input type='hidden' name='detail[".$keyNumber."][id_master]' value='".$value['id']."'>";
									echo "<input type='hidden' name='detail[".$keyNumber."][key_hub]' value='".$keyNumber."'>";
									echo "<input type='hidden' name='detail[".$keyNumber."][category]' value='others material'>";
									echo "<input type='hidden' name='detail[".$keyNumber."][kode_barang]' value='".$value['code_material']."'>";
									echo "<input type='hidden' name='detail[".$keyNumber."][nama_barang]' value='".$namaMaterial."'>";
									echo "<input type='hidden' name='detail[".$keyNumber."][est]' value='".$value['weight']."'>";
									echo "</td>";
									echo "<td>".$namaMaterial."</td>";
									echo "<td class='text-center'></td>";
									echo "<td class='text-center'></td>";
									echo "<td class='text-center'></td>";
									echo "<td class='text-center'>".number_format($value['weight'],4)."</td>";
									echo "<td class='text-center'><input type='input' name='detail[".$keyNumber."][qty]' class='form-control input-sm text-center'></td>";
								echo "</tr>";
							}
						}
					}
				}
			?>
			<tbody>
		</table>
		<div class="form-group row">
        	<div class="col-md-1">
				<label for="customer"></label>
			</div>
			<div class="col-md-11">
				<button type="button" class="btn btn-primary" name="save" id="save">Buat Assembly</button>
				<button type="button" class="btn btn-danger" style='margin-left:5px;' name="back" id="back">Back</button>
			</div>
        </div>

      	
		</form>
	</div>
</div>

<div class="modal modal-default fade" id="dialog-popup" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" style='width:70%;'>
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel"><span class="fa fa-users"></span>&nbsp;Detail Data</h4>
      </div>
      <div class="modal-body" id="ModalView">
		...
      </div>
  </div>
</div>


<script src="<?= base_url('assets/js/jquery.maskMoney.js')?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>
<style>
    .datepicker{
        cursor: pointer;
    }
	/* th {
		background-color: beige;
	} */
</style>

<script type="text/javascript">
	//$('#input-kendaraan').hide();
	var base_url			= '<?php echo base_url(); ?>';
	var active_controller	= '<?php echo($this->uri->segment(1)); ?>';

	$(document).ready(function(){
		$('.datepicker').datepicker({ dateFormat: 'dd-M-yy' });
    	$('.autoNumeric0').autoNumeric('init', {mDec: '0', aPad: false})
    	$('.autoNumeric4').autoNumeric('init', {mDec: '4', aPad: false})
    	$('.chosen-select').select2()

    	//back
		$(document).on('click', '#back', function(){
		    window.location.href = base_url + active_controller
		});

		$(document).on('click', '.detail', function(){
			var no_bom = $(this).data('id');
			var category = $(this).data('category');
			let controller_category = '';
			if(category == 'standard'){
				controller_category = 'bom';
			}
			if(category == 'topping'){
				controller_category = 'bom_topping';
			}
			if(category == 'grid standard'){
				controller_category = 'bom_hi_grid_standard';
			}
			if(category == 'grid custom'){
				controller_category = 'bom_hi_grid_custom';
			}
			if(category == 'ftackel'){
				controller_category = 'bom_ftackel';
			}
			// alert(id);
			$("#head_title").html("<b>Detail Bill Of Material</b>");
			$.ajax({
				type:'POST',
				url: base_url + controller_category + '/detail/',
				data:{
					'no_bom':no_bom,
				},
				success:function(data){
					$("#dialog-popup").modal();
					$("#ModalView").html(data);

				}
			})
		});

		$('#save').click(function(e){
			e.preventDefault();

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
						var baseurl=siteurl+active_controller+'/add';
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
										  timer	: 7000
										});
									window.location.href = base_url + active_controller
								}else{

									if(data.status == 2){
										swal({
										  title	: "Save Failed!",
										  text	: data.pesan,
										  type	: "warning",
										  timer	: 7000
										});
									}else{
										swal({
										  title	: "Save Failed!",
										  text	: data.pesan,
										  type	: "warning",
										  timer	: 7000
										});
									}

								}
							},
							error: function() {

								swal({
								  title				: "Error Message !",
								  text				: 'An Error Occured During Process. Please try again..',
								  type				: "warning",
								  timer				: 7000
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



</script>
