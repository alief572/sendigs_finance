<?=form_open($this->uri->uri_string(),array('id'=>'frm_data','name'=>'frm_data','role'=>'form','class'=>'form-horizontal'));?>
<div class="tab-content">
	<div class="tab-pane active">
		<div class="box box-primary">
			<div class="box-body">
				<div class="form-group ">
					<label for="divisi" class="col-sm-2 control-label">Departement<font size="4" color="red"><B>*</B></font></label>
					<div class="col-sm-10">
						<div class="input-group">
							<?php
							echo form_dropdown('departement',$datdept, set_value('departement', isset($departement) ? $departement: '0'), array('id'=>'departement','class'=>'form-control','required'=>'required'));
							?>
						</div>
					</div>
				</div>
			</div>
			<div class="table-responsive">
			<table class="table table-bordered table-striped">
				<thead>
					<tr>
					<th width="5">#</th>
					<th>Post Pengeluaran</th>
					<th>Nama Barang /Jasa</th>
					<th>Bulanan/ Tahunan</th>
					<th>Jadual Pembayaran</th>
					<th>Perkiraan Biaya</th>
					<th>Baseline</th>
					<th width=100><div class="pull-right"><a class="btn btn-success btn-xs" href="javascript:void(0)" title="Tambah" onclick="add_detail(0)" id="add-material"><i class="fa fa-plus"></i> Tambah</a></div></th>
					</tr>
				</thead>
				<tbody id="detail_body">
				<?php $total=0; $idd=1;$idalokasi=1;
				$all_coa="";
				foreach($callcoa as $key => $val){
					$all_coa.="<option value='".$key."'>".$val."</option>";
				}

				$combowaktu="<option value=''>Select option</option>";
				$combocoa='';//"<option value=''>Select option</option>";
				$combotgl="<option value=''>Select</option>";
				foreach($datcoa as $key => $val){
					$combocoa.="<option value='".$key."'>".$val."</option>";
				}
				foreach($waktu as $datawaktu){
					$combowaktu.="<option value='".$datawaktu."'>".$datawaktu."</option>";
				}
				for($tgl=1;$tgl<=31;$tgl++){
					$combotgl.="<option value='".$tgl."'>".$tgl."</option>";
				}
				if(!empty($new_data_detail)){
					foreach($new_data_detail AS $record){ ?>
					<tr id='tr1_<?=$idd?>' class='delAll'>
						<td><input type="hidden" name="detail_id[]" id="raw_id_<?=$idd?>" value=""><?=$idd;?></td>
						<td><select class="form-control select2" name="coa[]" id="coa_<?=$idd;?>" style="width:300px" required>
						<?php 
						if($record->coa!='') {
							echo str_ireplace($record->coa."'",$record->coa."' selected ", $combocoa);
						}else{
							echo $combocoa;
						}?>
						</select></td>
						<td><input type="text" class="form-control" name="nama[]" id="nama_<?=$idd;?>" value=""></td>
						<td><select class="form-control" name="tipe[]" id="tipe_<?=$idd;?>" onchange="cektipe(<?=$idd;?>)">
						<?php  echo $combowaktu; ?>
						</select></td>
						<td>
							<input type="text" class="form-control tanggal hidden" name="thn[]" id="thn_<?=$idd;?>" value="" readonly style="background:#fff;cursor: pointer;">
							<select class="form-control hidden" name="bln[]" id="bln_<?=$idd;?>">
							<?php echo $combotgl;?>
							</select>
						</td>
						<td><input type="text" class="form-control divide" name="nilai[]" id="nilai_<?=$idd;?>" value=""></td>
						<td><input type="text" class="form-control" name="keterangan[]" id="keterangan_<?=$idd;?>" value=""></td>
						<td align='center'><button type='button' class='btn btn-danger btn-xs' data-toggle='tooltip' onClick='delDetail(<?=$idd?>)' title='Hapus data'><i class='fa fa-close'></i></button></td>
					</tr>
					<?php
						$idd++;
					}
				}
				
				if(!empty($data_detail)){
					foreach($data_detail AS $record){ ?>
					<tr id='tr1_<?=$idd?>' class='delAll'>
						<td><input type="hidden" name="detail_id[]" id="raw_id_<?=$idd?>" value="<?=$record->id;?>"><?=$idd;?>
						<input type='hidden' name='kode_id[]' id='kode_id_<?=$idd?>' value='<?=$record->kode_id;?>'></td>
						<td><select class="form-control select2" style="width:100%" name="coa[]" id="coa_<?=$idd;?>" style="width:300px" required>
						<?php
						if($record->coa!='') {
							echo str_ireplace($record->coa."'",$record->coa."' selected ", $combocoa);
						}else{
							echo $combocoa;
						}?>
						</select></td>
						<td><input type="text" class="form-control" name="nama[]" id="nama_<?=$idd;?>" value="<?=$record->nama;?>"></td>
						<td><select class="form-control" name="tipe[]" id="tipe_<?=$idd;?>" onchange="cektipe(<?=$idd;?>)">
						<?php 
						if($record->coa!='') {
							echo str_ireplace($record->tipe."'",$record->tipe."' selected ", $combowaktu);
						}else{
							echo $combowaktu;
						}
						?>
						</select></td>
						<td>
							<input type="text" class="form-control tanggal <?php echo ($record->tipe=='bulan'?' hidden':''); ?>" name="thn[]" id="thn_<?=$idd;?>" value="<?=$record->tanggal;?>" readonly style="background:#fff;cursor: pointer;">
							<select class="form-control <?php echo ($record->tipe=='bulan'?' ':' hidden'); ?>" name="bln[]" id="bln_<?=$idd;?>">
							<?php 
							echo str_ireplace($record->tanggal."'",$record->tanggal."' selected ", $combotgl);?>
							</select>
						</td>
						<td><input type="text" class="form-control divide" name="nilai[]" id="nilai_<?=$idd;?>" value="<?=($record->nilai);?>"></td>
						<td><input type="text" class="form-control" name="keterangan[]" id="keterangan_<?=$idd;?>" value="<?=$record->keterangan;?>"></td>
						<td align='center'>
						<button type='button' class='btn btn-success btn-xs' data-toggle='tooltip' onClick='add_detail(<?=$idd?>)' title='Tambah data'><i class='fa fa-plus'></i></button>
						<button type='button' class='btn btn-info btn-xs' data-toggle='tooltip' onClick='add_alokasi(<?=$idd?>)' title='Tambah alokasi'><i class='fa fa-cubes'></i></button> 
						<button type='button' class='btn btn-danger btn-xs' data-toggle='tooltip' onClick='delDetail(<?=$idd?>)' title='Hapus data'><i class='fa fa-close'></i></button></td>
					</tr>
					<?php
						if($record->kode_id != ''){
							$dtalok=$this->db->query("select * from ms_budget_rutin_alokasi where kode='".$record->kode_id."'")->result();
							if(!empty($dtalok)){
								foreach($dtalok AS $fields){ ?>

								<tr id='tr1_dtl<?=$idd."_".$idalokasi?>' class='delAll rows<?=$record->kode_id?>'>
									<td colspan=2><input type='hidden' name='kode_detail[]' id='kode_detail_<?=$idalokasi?>' value='<?=$record->kode_id?>'>
									<input type='hidden' name='detail_alokasi[]' id='detail_alokasi_<?=$idalokasi?>' value='<?=$fields->id?>'></td>
									<td colspan=3>COA :<br /><select class='form-control select2' style="width:100%" name='coa_alokasi[]' id='coa_alokasi_<?=$idalokasi?>'>
									<?php
									if($fields->coa!='') {
										echo str_ireplace($fields->coa."'",$fields->coa."' selected ", $all_coa);
									}else{
										echo $all_coa;
									}?>
									
									
									<?php echo $all_coa;?></select>
									</td>
									<td>Persentase :<br /><input type='text' class='form-control divide' name='nilai_alokasi[]' value='<?=$fields->nilai?>' id='nilai_alokasi_<?=$idalokasi?>' /></td>
									<td align='center' colspan=2>
									<button type='button' class='btn btn-warning btn-xs' data-toggle='tooltip' onClick='delDetailalokasi("<?=$idd."_".$idalokasi?>")' title='Hapus alokasi'><i class='fa fa-minus'></i></button></td>
								</tr>

								<?php $idalokasi++;
								}
							}
						}
						$idd++;
					}
				}?>
				</tbody>
			</table>
			</div>
			<div class="box-footer">
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10">
						<?php
						if(isset($data)){
							if($data->status==0){
//								echo '<button type="button" name="Approve" class="btn btn-primary btn-sm" id="approve" onclick="data_approve()"><i class="fa fa-save">&nbsp;</i>Approve</button>';
							}
						}
						?>
						<button type="submit" name="save" class="btn btn-success btn-sm" id="submit"><i class="fa fa-save">&nbsp;</i>Simpan</button>
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
	var url_save = siteurl+'budget_periodik/save_data/';
	var url_approve = siteurl+'budget_periodik/approve/';
	var nomor=<?=$idd?>;
	var nomor_detail=<?=$idalokasi?>;
	$('.divide').divide();
    $('#frm_data').on('submit', function(e){
        e.preventDefault();
		var errors="";
		if(errors==""){
			data_save();
		}else{
			swal(errors);
			return false;
		}
    });

	function uniqId() {
	  return Math.round(new Date().getTime() + (Math.random() * 100));
	}
	$(function () {
		$(".select2").select2();
		$(".tanggal").datepicker({
			todayHighlight: true,
			format : "mm-dd",
			showInputs: true,
			autoclose:true
		});
	});
	function add_alokasi(id){
		nomor_detail++;
		var kode_id=$("#kode_id_"+id).val();
		if(kode_id==''){
			kode_id=uniqId();
			$("#kode_id_"+id).val(kode_id);
		}
		var Rows	 = 	"<tr id='tr1_dtl"+nomor+"_"+nomor_detail+"' class='delAll rows"+kode_id+"'>";
			Rows	+= 		"<td colspan=2><input type='hidden' name='kode_detail[]' id='kode_detail_"+nomor_detail+"' value='"+kode_id+"'>";
			Rows	+=			"<input type='hidden' name='detail_alokasi[]' id='detail_alokasi_"+nomor_detail+"' value=''>";
			Rows	+= 		"</td>";
			Rows	+= 		"<td colspan=3>";
			Rows	+=			"COA :<br /><select class='form-control select2' style='width:100%' name='coa_alokasi[]' id='coa_alokasi_"+nomor_detail+"'><?php echo $all_coa;?></select>";
			Rows	+= 		"</td>";
			Rows	+= 		"<td>";
			Rows	+=			"Persentase :<br /><input type='text' class='form-control divide' name='nilai_alokasi[]' value='0' id='nilai_alokasi_"+nomor+"' />";
			Rows	+= 		"</td>";
			Rows	+= 		"<td align='center' colspan=2>";
			Rows 	+=			" <button type='button' class='btn btn-warning btn-xs' data-toggle='tooltip' onClick='delDetailalokasi(\""+nomor+"_"+nomor_detail+"\")' title='Hapus alokasi'><i class='fa fa-minus'></i></button>";
			Rows	+= 		"</td>";
			Rows	+= 	"</tr>";
			$('#tr1_'+id).after(Rows);
			var combodata=$("#coa_"+id).val();
		$(".divide").divide();
		$(".select2").select2();

	}

	function add_detail(id){
		var Rows	 = 	"<tr id='tr1_"+nomor+"' class='delAll'>";
			Rows	+= 		"<td>";
			Rows	+=			"<input type='hidden' name='detail_id[]' id='raw_id_"+nomor+"' value=''><input type='hidden' name='kode_id[]' id='kode_id_"+nomor+"' value=''>";
			Rows	+= 		"</td>";
			Rows	+= 		"<td>";
			Rows	+=			"<select class='form-control select2' style='width:100%' name='coa[]' id='coa_"+nomor+"'><?php echo $combocoa;?></select>";
			Rows	+= 		"</td>";
			Rows	+= 		"<td>";
			Rows	+=			"<input type='text' class='form-control' name='nama[]' id='nama_"+nomor+"' />";
			Rows	+= 		"</td>";
			Rows	+= 		"<td>";
			Rows	+=			"<select class='form-control' name='tipe[]' id='tipe_"+nomor+"' onchange='cektipe("+nomor+")'><?php echo $combowaktu;?></select>";
			Rows	+= 		"</td>";
			Rows	+= 		"<td>";
			Rows	+=			"<input type='text' class='form-control tanggal hidden' name='thn[]' value='' id='thn_"+nomor+"' readonly style='background:#fff;cursor: pointer;' />";
			Rows	+=			"<select class='form-control hidden' name='bln[]' id='bln_"+nomor+"'><?php echo $combotgl;?></select>";
			Rows	+= 		"</td>";
			Rows	+= 		"<td>";
			Rows	+=			"<input type='text' class='form-control divide' name='nilai[]' value='0' id='nilai_"+nomor+"' />";
			Rows	+= 		"</td>";
			Rows	+= 		"<td>";
			Rows	+=			"<input type='text' class='form-control' name='keterangan[]' id='keterangan_"+nomor+"' />";
			Rows	+= 		"</td>";
			Rows	+= 		"<td align='center'>";
			Rows	+= 		" <button type='button' class='btn btn-success btn-xs' data-toggle='tooltip' onClick='add_detail("+nomor+")' title='Tambah data'><i class='fa fa-plus'></i></button> ";
			Rows	+= 		" <button type='button' class='btn btn-info btn-xs' data-toggle='tooltip' onClick='add_alokasi("+nomor+")' title='Tambah alokasi'><i class='fa fa-cubes'></i></button> ";
			Rows 	+=			" <button type='button' class='btn btn-danger btn-xs' data-toggle='tooltip' onClick='delDetail("+nomor+")' title='Hapus data'><i class='fa fa-close'></i></button>";
			Rows	+= 		"</td>";
			Rows	+= 	"</tr>";
			if(id==0){
				$('#detail_body').append(Rows);
			}else{
				$('#tr1_'+id).after(Rows);
				var combodata=$("#coa_"+id).val();
				$("#coa_"+nomor).val(combodata);
			}
			$("#coa_"+nomor).focus();
			nomor++;
		$(".tanggal").datepicker({
			todayHighlight: true,
			format : "mm-dd",
			showInputs: true,
			autoclose:true
		});
		$(".divide").divide();
		$(".select2").select2();

	}

	function cektipe(nomor){
		var keys=$("#tipe_"+nomor).val();
		if(keys=="bulan"){
			$("#thn_"+nomor).addClass("hidden");
			$("#bln_"+nomor).removeClass("hidden");
		}else{
			$("#bln_"+nomor).addClass("hidden");
			$("#thn_"+nomor).removeClass("hidden");
		}
	}

	function delDetailalokasi(kode){
		$("#tr1_dtl"+kode).remove();
	}
	function delDetail(row){
		var kode_id=$("#kode_id_"+row).val();
		if(kode_id!=''){
			$('.rows'+kode_id).remove();
		}		
		$('#tr1_'+row).remove();
	}

	function data_approve(){
		swal({
		  title: "Anda Yakin?",
		  text: "Data Akan Disetujui!",
		  type: "info",
		  showCancelButton: true,
		  confirmButtonText: "Ya, setuju!",
		  cancelButtonText: "Tidak!",
		  closeOnConfirm: false,
		  closeOnCancel: true
		},
		function(isConfirm){
		  if (isConfirm) {
			id=$("#id").val();
			$.ajax({
				url: url_approve+id,
				dataType : "json",
				type: 'POST',
				success: function(msg){
					if(msg['save']=='1'){
						swal({
							title: "Sukses!",
							text: "Data Berhasil Di Setujui",
							type: "success",
							timer: 1500,
							showConfirmButton: false
						});
						window.location.reload();
					} else {
						swal({
							title: "Gagal!",
							text: "Data Gagal Di Setujui",
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
	}
</script>
