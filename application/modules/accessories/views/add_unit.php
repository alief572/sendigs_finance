<?php
    $id    = (!empty($header)) ? $header[0]->id : '';
    $code  = (!empty($header)) ? $header[0]->code : '';
?>

<div style="padding: 10px 5px;">
	<form id="data_form" autocomplete="off">
		<input type="hidden" id="id" name="id" value='<?= $id; ?>'>
		
		<div class="form-group" style="margin-bottom: 20px;">
			<label for="code" style="font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 8px;">
				Nama / Simbol Unit <span class="text-danger">*</span>
			</label>
			<input type="text" class="form-control" id="code" required name="code" placeholder="Contoh: PCS, ROLL, KG, DUS" value='<?= htmlspecialchars($code); ?>' style="border-radius: 6px; height: 38px; border: 1px solid #cbd5e1;">
			<span class="help-block" style="font-size: 11px; color: #94a3b8; margin-top: 5px;">Masukkan kode atau nama satuan yang singkat dan jelas.</span>
		</div>

		<div style="text-align: right; margin-top: 25px; padding-top: 15px; border-top: 1px solid #f1f5f9;">
			<button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius: 6px; padding: 7px 16px; margin-right: 6px;">
				<i class="fa fa-times"></i> Batal
			</button>
			<button type="button" class="btn btn-primary" name="save" id="save_unit" style="border-radius: 6px; padding: 7px 18px;">
				<i class="fa fa-save"></i> Simpan Unit
			</button>
		</div>
	</form>
</div>
