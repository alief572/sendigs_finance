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
							<td><?= strtoupper($listData[0]->no_spk);?></td>
						</tr>
						<tr>
							<td>Qty</td>
							<td>:</td>
							<td><?= strtoupper($listData[0]->qty);?></td>
						</tr>
					</table>
				</div>
			</div>
			<div class="form-group row">
				<div class='col-md-12 text-left'>
					<div class="form-horizontal">
						<label class="">Size Barcode:</label>
						<div class="form-group row">
							<div class="col-sm-1">
								<div class="radio">
									<label>
										<input type="radio" name="size" id="size_sm" value="sm" checked="checked">
										Small
									</label>
								</div>
							</div>
							
							<div class="col-sm-1">
								<div class="radio">
									<label>
										<input type="radio" name="size" id="size_md" value="md">
										Medium
									</label>
								</div>
							</div>
							<div class="col-sm-1">
								<div class="radio">
									<label>
										<input type="radio" name="size" id="size_lg" value="lg">
										Large
									</label>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class='col-md-12 text-left'>
					<input type="hidden" name='id' value='<?=$listData[0]->id;?>'>
					<input type="hidden" name='kode' value='<?=$listData[0]->kode;?>'>
					<input type="hidden" name='kode_det' value='<?=$listData[0]->kode_det;?>'>
					<input type="hidden" name='no_spk' value='<?=$listData[0]->no_spk;?>'>
					<input type="hidden" name='qty' value='<?=$listData[0]->qty;?>'>
					<input type="hidden" name='code_lv4' value='<?=$listData[0]->code_lv4;?>'>
					<input type="hidden" name='no_bom' value='<?=$listData[0]->no_bom;?>'>
					<button type='button' id='cetakQR' class='btn btn-sm btn-default'><b>Cetak Barcode</b></button>
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
								<th>QC Pass Date</th>
								<th>Inspektor Name</th>
								<th>Note</th>
								<th>Status Cetak</th>
								<!-- <th>Checksheet Inspeksi</th> -->
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ($listData as $value) {
								$i = $value->product_ke;
								$UNIQ = $value->id.'-'.$tanda.'-'.$i;
								$id_key = (!empty($GET_QC[$UNIQ]['id']))?$GET_QC[$UNIQ]['id']:null;
								$status = (!empty($GET_QC[$UNIQ]['status']))?$GET_QC[$UNIQ]['status']:'';
								$daycode = (!empty($GET_QC[$UNIQ]['daycode']))?$GET_QC[$UNIQ]['daycode']:'';
								$qc_pass = (!empty($GET_QC[$UNIQ]['qc_pass']))?$GET_QC[$UNIQ]['qc_pass']:'';
								$note = (!empty($GET_QC[$UNIQ]['note']))?$GET_QC[$UNIQ]['note']:'';
								$download = (!empty($GET_QC[$UNIQ]['doc_inspeksi']))?$GET_QC[$UNIQ]['doc_inspeksi']:'';
								$sts_print_qr = (!empty($GET_QC[$UNIQ]['sts_print_qr']))?$GET_QC[$UNIQ]['sts_print_qr']:'N';
								$inspektor = (!empty($GET_QC[$UNIQ]['inspektor']))?$GET_QC[$UNIQ]['inspektor']:'';

								$status_label = ($status == 'NG')?'Downgrade':'Passed';

								$status = 'Not Yet';
								$warna = 'blue';
								if($sts_print_qr == 'Y'){
									$status = 'Done';
									$warna = 'green';
								}

								$check = "<input type='checkbox' name='check[$i]' data-nomor='$i' class='chk_personal' value='".$id_key."' >";
								echo "<tr>";
									echo "<td align='center'>".$check."</td>";
									// echo "<td>".$value->nama_product."</td>";
									echo "<td>".$i."</td>";
									echo "<td>".$status_label."</td>";
									echo "<td>".$daycode."</td>";
									echo "<td>".date('d-M-Y',strtotime($qc_pass))."</td>";
									echo "<td>".$inspektor."</td>";
									echo "<td>".$note."</td>";
									echo "<td><span class='badge bg-".$warna."'>".$status."</span></td>";
									// echo "<td>".$download."</td>";
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