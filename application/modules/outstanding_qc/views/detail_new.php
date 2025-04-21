<div class="box box-primary">
	<div class="box-body">
		<form id="data_form" method="post"  autocomplete="off" enctype='multiple/form-data'>
			<div class="form-group row">
				<div class="col-md-12">
					<table>
						<tr>
							<td width='20%'>Product Name</td>
							<td width='2%'>:</td>
							<td><?= strtoupper($NamaProduct);?></td>
						</tr>
						<tr>
							<td>No. SPK</td>
							<td>:</td>
							<td><?= strtoupper($listData[0]['no_spk']);?></td>
						</tr>
						<tr>
							<td>Qty</td>
							<td>:</td>
							<td><?= strtoupper($listData[0]['qty']);?></td>
						</tr>
					</table>
				</div>
			</div>
			<div class="form-group row">
				<div class='col-md-12 text-right'>
                    <input type="hidden" name='tanda' value='<?=$tanda;?>'>
					<input type="hidden" name='id' value='<?=$listData[0]['id'];?>'>
					<input type="hidden" name='kode' value='<?=$listData[0]['kode'];?>'>
					<input type="hidden" name='kode_det' value='<?=$listData[0]['kode_det'];?>'>
					<input type="hidden" name='no_spk' value='<?=$listData[0]['no_spk'];?>'>
					<input type="hidden" name='qty' value='<?=$listData[0]['qty'];?>'>
					<input type="hidden" name='code_lv4' value='<?=$listData[0]['code_lv4'];?>'>
					<input type="hidden" name='no_bom' value='<?=$listData[0]['no_bom'];?>'>
					<button type='button' id='sendCheckReleaseNew' class='btn btn-sm btn-success'><b>Release To Finish Good</b></button>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-12">
					<table class='table'>
						<thead>
							<tr>
								<th><input type='checkbox' name='chk_all' id='chk_all'></th>
								<!-- <th>Product Name</th> -->
								<th>Product Ke</th>
								<th>Status</th>
								<th>Daycode</th>
								<th><input type='text' name='datepicker_qc_all' id='datepicker_qc_all' class='form-control input-md text-center' placeholder='QC Pass Date' readonly></th>
								<th>Note</th>
								<th>Inspector Name</th>
								<th>Checksheet Inspeksi</th>
							</tr>
						</thead>
						<tbody>
							<?php
                            foreach ($listData as $key => $value) {
                                $i = $value['product_ke'];
								$UNIQ = $value['id'].'-'.$i;
								$id_key = (!empty($GET_QC[$UNIQ]['id']))?$GET_QC[$UNIQ]['id']:null;
								$status = (!empty($GET_QC[$UNIQ]['status']))?$GET_QC[$UNIQ]['status']:'';
								$daycode = (!empty($GET_QC[$UNIQ]['daycode']))?$GET_QC[$UNIQ]['daycode']:'';
								$qc_pass = (!empty($GET_QC[$UNIQ]['qc_pass']))?$GET_QC[$UNIQ]['qc_pass']:'';
								$note = (!empty($GET_QC[$UNIQ]['note']))?$GET_QC[$UNIQ]['note']:'';
								$inspektor = (!empty($GET_QC[$UNIQ]['inspektor']))?$GET_QC[$UNIQ]['inspektor']:'';
								$download = (!empty($GET_QC[$UNIQ]['doc_inspeksi']))?$GET_QC[$UNIQ]['doc_inspeksi']:'';

								$status_label = ($status == 'NG')?'Downgrade':$status;

								$check = "";
								if($status == 'N'){
									$check = "<input type='checkbox' name='check[$i]' data-nomor='$i' class='chk_personal' value='" . $i . "' >";
								}
								echo "<tr>";
									echo "<td>".$check."</td>";
									// echo "<td>".$value['nama_product']."</td>";
									echo "<td>".$i."</td>";
									if($status == 'N'){
									echo "	<td>
												<select name='detail[$i][status]' id='status_$i' class='form-control input-md chosen_select'>
													<option value='0'>Pilih Status</option>
													<option value='OKE'>OKE</option>
													<option value='NG'>Downgrade</option>
												</select>
											</td>";
									echo "<td><input type='text' name='detail[$i][daycode]' id='daycode_$i' class='form-control input-md' placeholder='Daycode' value='" . $daycode . "'></td>";
									echo "<td><input type='text' name='detail[$i][qc_pass]' id='qc_pass_date_$i' class='form-control input-md datepicker text-center' readonly value='" . $qc_pass . "'></td>";
									echo "<td><input type='text' name='detail[$i][note]' class='form-control input-md' placeholder='Keterangan' value='" . $note . "'></td>";
									echo "<td><input type='text' name='detail[$i][inspektor]' class='form-control input-md' placeholder='Inspektor Name' value='" . $inspektor . "'></td>";
									echo "<td class='text-right'>
											<input type='hidden' name='detail[$i][id]' class='form-control input-md' value='" . $value['id_qc'] . "'>
											<input type='file' name='inspeksi_$i' class='form-control input-md'>
											" . $download . "
											</td>";
									}
									else{
										echo "<td>".$status_label."</td>";
										echo "<td>".$daycode."</td>";
										echo "<td>".date('d-M-Y',strtotime($qc_pass))."</td>";
										echo "<td>".$note."</td>";
										echo "<td>".$inspektor."</td>";
										echo "<td>".$download."</td>";
									}
									
								echo "</tr>";
							}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</form>
	</div>
</div>
<style>
	.datepicker, #datepicker_qc_all {
		cursor: pointer;
		width: 120px;
	}
</style>
<script>
	$(document).ready(function(){
		$('.datepicker, #datepicker_qc_all').datepicker({
			dateFormat: 'yy-mm-dd',
			changeMonth: true,
			changeYear: true
		});

		$('.chosen_select').select2({
			width: '100%'
		});

		$("#chk_all").click(function() {
			$('.chk_personal').not(this).prop('checked', this.checked);
		});

		$("#datepicker_qc_all").change(function() {
			let date_qc_all = $(this).val()

			$(".datepicker").each(function() {
				if ($(this).val() == '') {
					$(this).val(date_qc_all)
				}
			});

		});
	});
</script>