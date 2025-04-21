<?=form_open($this->uri->uri_string(),array('id'=>'frm_data','name'=>'frm_data','role'=>'form','class'=>'form-horizontal','enctype'=>'multipart/form-data'));?>
<input type="hidden" name="id" id="id" value="<?php echo (isset($data->id) ? $data->id: ''); ?>">
<input type="hidden" name="modul" id="modul" value="bayarrutin">
<div class="tab-content">
	<div class="tab-pane active">
		<div class="box box-primary">
			<div class="box-body">
				<div class="form-group ">
					<label for="divisi" class="col-sm-2 control-label">Departement<font size="4" color="red"><B>*</B></font></label>
					<div class="col-sm-2">
						<div class="input-group">
							<?php
							echo form_dropdown('departement',$datdept, set_value('departement', isset($departement) ? $departement: '0'), array('id'=>'departement','class'=>'form-control','required'=>'required'));
							?>
						</div>
					</div>

					<label for="divisi" class="col-sm-2 control-label">No Dokumen</label>
					<div class="col-sm-2">
						<div class="input-group">
							<input type="text" class="form-control" id="no_doc" name="no_doc" value="<?php echo (isset($data->no_doc) ? $data->no_doc: ''); ?>" placeholder="Auto" required readonly tabindex="-1">
						</div>
					</div>

					<label for="divisi" class="col-sm-2 control-label">Tanggal<font size="4" color="red"><B>*</B></font></label>
					<div class="col-sm-2">
						<div class="input-group">
							<input type="text" class="form-control" id="tanggal_doc" name="tanggal_doc" value="<?php echo (isset($data->tanggal_doc) ? $data->tanggal_doc: date("Y-m-d")); ?>" placeholder="Tanggal" required tabindex="-1" readonly>
						</div>
					</div>

				</div>
				<div class="table-responsive">
				<table class="table table-bordered table-striped">
					<thead>
						<tr>
						<th width="5">#</th>
						<th>Nama Barang /Jasa</th>
						<th>Jadual Pembayaran</th>
						<th>Perkiraan Biaya</th>
						<th colspan=2>Keterangan</th>
						<th>Bank / No Rek / Nama</th>
						<th>PPN</th>
						<th>Aktual Bayar</th>
						</tr>
					</thead>
					<tbody id="detail_body">
					<?php $total=0; $idd=0;
					if(!empty($data_detail)){
						foreach($data_detail AS $record){ ?>
						<tr id='trd<?=$idd?>' class='delAll'>
							<td><input type="hidden" name="detail_id[]" id="raw_id_<?=$idd?>" value="<?=$record->id;?>"><?=($idd+1);?>
							<input type="hidden" name="id_budget[]" id="id_budget_<?=$idd;?>" value="<?=$record->id_budget?>" class='budget'>
							<input type="hidden" name="coa[]" id="coa_<?=$idd;?>" value="<?=$record->coa?>"></td>
							<td><input type="text" class="form-control" name="nama[]" id="nama_<?=$idd;?>" value="<?=$record->nama;?>" readonly tabindex="-1"></td>
							<td><input type="text" class="form-control" name="tanggal[]" id="tanggal_<?=$idd;?>" value="<?=$record->tanggal;?>" readonly tabindex="-1"></td>
							<td><input type="text" class="form-control divide" name="nilai[]" id="nilai_<?=$idd;?>" value="<?=($record->nilai);?>" readonly tabindex="-1"></td>
							<td><input type="text" class="form-control" name="keterangan[]" id="keterangan_<?=$idd;?>" value="<?=$record->keterangan;?>" readonly tabindex="-1">
							</td>
							<td width=5><?=($record->doc_file!=''?'<a href="'.base_url('assets/bayar_rutin/'.$record->doc_file).'" download target="_blank"><i class="fa fa-download"></i></a>':'')?></td>
							<td>
							<input type="text" class="form-control" name="bank_id[]" id="bank_id_<?=$idd;?>" value="<?=$record->bank_id;?>" readonly>
							<input type="text" class="form-control" name="accnumber[]" id="accnumber_<?=$idd;?>" value="<?=$record->accnumber;?>" readonly>
							<input type="text" class="form-control" name="accname[]" id="accname_<?=$idd;?>" value="<?=$record->accname;?>" readonly>
							</td>							
							<td><input type="checkbox" name="ppn_check[]" id="ppn_check_<?=$idd;?>" <?=($record->ppn!=''?' checked':'')?> value="1" onclick="cektotal(<?=$idd;?>)" />
							<input type="hidden" name="ppn[]" id="ppn_<?=$idd;?>" value="<?=$record->ppn?>">
							</td>
							<td><input type="text" class="form-control divide subtotal" name="nilai_bayar[]" id="nilai_bayar_<?=$idd;?>" value="<?=($record->nilai_bayar);?>" onblur="cektotal(<?=$idd;?>)"></td>
						</tr>
						<?php
							$idd++;
						}
					}
					$combocoa="<option value=''>Select option</option>";
					foreach($datcoa as $key){
						$combocoa.="<option value='".$key->no_perkiraan."'>".$key->nama_perkiraan."</option>";
					}
					?>
					</tbody>
					<tfoot>
						<tr>
							<th colspan="7">Total </th>
							<th><input type="text" class="form-control divide" name="nilai_total" id="nilai_total" value="<?=($data->nilai_total);?>" readonly tabindex="-1"></th>
						</tr>
					</tfoot>
				</table>

				<div class="form-group ">
					<label class="col-sm-2 control-label">Cash/Bank <b class="text-red">*</b></label>
					<div class="col-sm-4">
						<select class="form-control select2" name="coa_bank" id="coa_bank" style="width:300px" required>
							<?php 
							if($data->coa_bank!=''){
								echo str_ireplace($data->coa_bank."'",$data->coa_bank."' selected ", $combocoa);
							}else{
								echo $combocoa;
							}
							?>
						</select>
					</div>
					<label class="col-sm-2 hidden">
						<select class="form-control" name="coa_ppn" id="coa_ppn" onblur="checkppn()">
							<?='<option value="0"'.($data->nilai_ppn==0?' selected':'').'>Non PPN</option>';?>
							<?='<option value="10"'.($data->nilai_ppn>0?' selected':'').'>PPN</option>';?>
						</select>
					</label>
					<div class="col-sm-4 hidden">
						<input type="text" class="form-control divide" id="nilai_ppn" name="nilai_ppn" value="<?php echo (isset($data->nilai_ppn) ? $data->nilai_ppn:0 ); ?>" readonly>
					</div>
				</div>
				
				<table class="table table-bordered table-striped">
					<thead>
						<tr>
						<th width="5">#</th>
						<th>No COA</th>
						<th>Keterangan</th>
						<th>Debit</th>
						<th>Kredit</th>
						<th><div class="pull-right btnnav"><a class="btn btn-success btn-xs" href="javascript:void(0)" title="Tambah" onclick="add_detail()" id="add-data-detail"><i class="fa fa-plus"></i> Tambah</a></div></th>
						</tr>
					</thead>
					<tbody id="detail_coa">
					<?php $totalcoa=0; $idcoa=1;
					if(!empty($datacoapayment)){
						foreach($datacoapayment AS $record){ ?>
						<tr id='tr1_<?=$idcoa?>' class='delAll'>
							<td><input type="hidden" name="detail_id_coa[]" id="detail_id_coa<?=$idcoa?>" value="<?=$record->id;?>"><?=$idcoa;?></td>
							<td><select class="form-control select2" name="detail_coa[]" id="detail_coa<?=$idd;?>" style="width:300px" required>
							<?php echo str_ireplace($record->coa."'",$record->coa."' selected ", $combocoa);?>
							</select></td>
							<td><textarea class="form-control" name="keterangancoa[]" id="keterangancoa<?=$idcoa;?>"><?=$record->keterangan;?></textarea>
							</td>
							<td><input type="text" class="form-control divide" name="debit[]" id="debit<?=$idcoa;?>" value="<?=($record->debit);?>"></td>
							<td><input type="text" class="form-control divide" name="kredit[]" id="kredit<?=$idcoa;?>" value="<?=($record->kredit);?>"></td>
							<td align='center'><button type='button' class='btn btn-danger btn-xs btnnav' data-toggle='tooltip' onClick='delDetail(<?=$idcoa?>)' title='Hapus data'><i class='fa fa-close'></i> Hapus</button></td>
						</tr>
						<?php
							$idcoa++;
						}
					}?>
					</tbody>
				</table>
				</div>
			</div>
			<div class="box-footer">
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10">
						<?php
						if(isset($data)){
							if($data->status==1){
								echo '<button type="submit" name="save" class="btn btn-success btn-sm" id="submit"><i class="fa fa-save">&nbsp;</i>Simpan</button>';
							}
						}
						?>
						<a class="btn btn-warning btn-sm" onclick="location.reload();return false;"><i class="fa fa-reply">&nbsp;</i>Batal</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?= form_close() ?>
<script src="<?= base_url('assets/js/number-divider.min.js')?>"></script>
<script type="text/javascript">
	var url_save = siteurl+'pembayaran_rutin/save_data/';
	var url_approve = siteurl+'pembayaran_rutin/approve/';
	var nomor=<?=$idcoa?>;
	<?php
	if($type=='view'){
		echo "$('#frm_data :input').prop('disabled', true);";
		echo "$('.btnnav').addClass('hidden');";		
	}?>
	$('.divide').divide();
    $('#frm_data').on('submit', function(e){
        e.preventDefault();
		var errors="";
		if(errors==""){
		swal({
		  title: "Anda Yakin?",
		  text: "Data Akan Disimpan!",
		  type: "info",
		  showCancelButton: true,
		  confirmButtonText: "Ya, simpan!",
		  cancelButtonText: "Tidak!",
		  closeOnConfirm: false,
		  closeOnCancel: true
		},
		function(isConfirm){
		  if (isConfirm) {
			var formdata = new FormData($('#frm_data')[0]);
			$.ajax({
				url: url_save,
				dataType : "json",
				type: 'POST',
				data: formdata,
				processData	: false,
				contentType	: false,
				success: function(msg){
					if(msg['save']=='1'){
						swal({
							title: "Sukses!",
							text: "Data Berhasil Di Simpan",
							type: "success",
							timer: 1500,
							showConfirmButton: false
						});
						cancel();
						window.location.reload();
					} else {
						swal({
							title: "Gagal!",
							text: "Data Gagal Di Simpan",
							type: "error",
							timer: 1500,
							showConfirmButton: false
						});
					};
					console.log(msg);
				},
				error: function(msg){
					swal({
						title: "Gagal!",
						text: "Ajax Data Gagal Di Proses",
						type: "error",
						timer: 1500,
						showConfirmButton: false
					});
					console.log(msg);
				}
			});
		  }
		});

//			data_save();
		}else{
			swal(errors);
			return false;
		}
    });

	$(function () {
		$(".select2").select2();
		$(".tanggal").datepicker({
			todayHighlight: true,
			format : "yyyy-mm-dd",
			showInputs: true,
			autoclose:true
		});
	});

	function checkppn(){
		var sppn = $("#coa_ppn").val();
		var stotal = $("#nilai_total").val();
		var nilaippn = (parseFloat(sppn)*parseFloat(stotal)/100);		
		$("#nilai_ppn").val(nilaippn);
	}
	function cektotal(id){
		if($("#ppn_check_"+id).is(':checked')){
			var nilai=$("#nilai_bayar_"+id).val();
			if(nilai==0 || nilai==''){
				$("#ppn_"+id).val(0);
			}else{
				var ppn=parseInt((parseInt(nilai)-(parseInt(nilai)/1.1)));
				$("#ppn_"+id).val(ppn);
			}
		}else{
			$("#ppn_"+id).val(0);
		}
		var sum = 0;
		$('.subtotal').each(function() {
			sum += Number($(this).val());
		});
		$("#nilai_total").val(sum);
		checkppn();
	}

	function add_detail(){
		var Rows	 = 	"<tr id='tr1_"+nomor+"' class='delAll'>";
			Rows	+= 		"<td>";
			Rows	+=			"<input type='hidden' name='detail_id_coa[]' id=detail_id_coa"+nomor+"' value=''>";
			Rows	+= 		"</td>";
			Rows	+= 		"<td>";
			Rows	+=			"<select class='form-control select2' name='detail_coa[]' id='detail_coa"+nomor+"' style='width:300px' required><?=$combocoa?></select>";
			Rows	+= 		"</td>";
			Rows	+= 		"<td><textarea class='form-control' name='keterangancoa[]' id='keterangancoa"+nomor+"'/></textarea></td>";
			Rows	+= 		"<td><input type='text' class='form-control divide' name='debit[]' id='debit"+nomor+"' value='0' /></td>";
			Rows	+= 		"<td><input type='text' class='form-control divide' name='kredit[]' id='kredit"+nomor+"' value='0' /></td>";
			Rows	+= 		"<td align='center'>";
			Rows 	+=			"<button type='button' class='btn btn-danger btn-xs' data-toggle='tooltip' onClick='delDetail("+nomor+")' title='Hapus data'><i class='fa fa-close'></i> Hapus</button>";
			Rows	+= 		"</td>";
			Rows	+= 	"</tr>";
			nomor++;
			$('#detail_coa').append(Rows);
			$(".divide").divide();
			$(".select2").select2();
	}
	function delDetail(row){
		$('#tr1_'+row).remove();
	}
</script>
