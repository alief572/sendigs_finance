<?php $new_sisa_pp=0; ?>
<div class="nav-tabs-area">
    <!-- /.tab-content -->
    <div class="tab-content">
        <div class="tab-pane active" id="area">
        <!-- Biodata Mitra -->
            <div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
            <!-- form start-->
            <div class="box box-primary">
            <?= form_open(base_url().'po_aset/save_data',array('id'=>'frm_data','name'=>'frm_data','role'=>'form','class'=>'form-horizontal')) ?>
				<?php  if(isset($data->id)){$type='edit';}?>
				<input type="hidden" id="id" name="id" value="<?php echo set_value('id', isset($data->id) ? $data->id : ''); ?>">
                <div class="box-body">
					<div class="form-group ">
						<label for="no_pr" class="col-sm-2 control-label">No PR</label>
						<div class="col-sm-3">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-list"></i></span>
								<input type="text" class="form-control" id="no_pr" name="no_pr" value="<?php echo set_value('no_pr', isset($data->no_pr) ? $data->no_pr: ""); ?>" placeholder="Automatic" readonly tabindex="-1">
							</div>
						</div>
						<label for="tgl_pr" class="col-sm-2 control-label">Tgl PR</label>
						<div class="col-sm-3">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
								<input type="text" class="form-control tgl" id="tgl_pr" name="tgl_pr" value="<?php echo set_value('tgl_pr', isset($data->tgl_pr) ? $data->tgl_pr: date("Y-m-d")); ?>" placeholder="Automatic" readonly tabindex="-1">
							</div>
						</div>
					</div>
					<div class="form-group ">
						<label for="divisi" class="col-sm-2 control-label">Departemen<font size="4" color="red"><B>*</B></font></label>
						<div class="col-sm-3">
							<div class="input-group">
								<?php
								$datdivisi[0]	= 'Select An Option';
								echo form_dropdown('divisi',$datdivisi, set_value('divisi', isset($data->divisi) ? $data->divisi: '0'), array('id'=>'divisi','class'=>'form-control','required'=>'required', 'readonly'=>'readonly'));
								?>
							</div>
						</div>
						<label for="id_aset" class="col-sm-2 control-label">Aset<font size="4" color="red"><B>*</B></font></label>
						<div class="col-sm-3">
								<?php
								$dataaset[0]	= 'Select An Option';
								echo form_dropdown('id_aset',$dataaset, set_value('id_aset', isset($data->id_aset) ? $data->id_aset: '0'), array('id'=>'id_aset','class'=>'form-control','required'=>'required', 'readonly'=>'readonly'));
								?>
						</div>
					</div>
					<div class="form-group ">
						
						<label for="description" class="col-sm-2 control-label">Deskripsi</label>
						<div class="col-sm-3">
							<textarea class="form-control" id="description" name="description" readonly><?php echo isset($data->description) ? $data->description: ''; ?></textarea>
						</div>
						
						<label class="col-sm-2 control-label">Qty</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="qty" name="qty" value="<?php echo set_value('qty', isset($data->qty) ? $data->qty: 0); ?>" placeholder="0" required readonly >
							</div>
						</div>
					</div>
					<div class="form-group ">
						<label class="col-sm-2 control-label">Nilai Budget</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="budget" name="budget" value="<?php echo set_value('budget', isset($data->budget) ? $data->budget: 0); ?>" placeholder="0" required readonly tabindex="-1">
							</div>
						</div>
						<label class="col-sm-2 control-label">Sisa Budget PR</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="budgetpr" name="budgetpr" value="<?php echo set_value('budgetpr', isset($data->budgetpr) ? $data->budgetpr: 0); ?>" placeholder="0" required readonly tabindex="-1">
							</div>
						</div>
					</div>
					<div class="form-group ">
						<label class="col-sm-2 control-label">Sisa Budget PO</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="budgetpo" name="budgetpo" value="<?php echo set_value('budgetpo', isset($data->budgetpo) ? $data->budgetpo: 0); ?>" placeholder="0" required readonly tabindex="-1">
							</div>
						</div>
						<label for="nilai_pr" class="col-sm-2 control-label">Nilai PR<font size="4" color="red"><B>*</B></font></label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="nilai_pr" name="nilai_pr" value="<?php echo isset($data->nilai_pr) ? $data->nilai_pr: 0; ?>" onBlur="cekppn()" >
							</div>
						</div>
					</div>
					
						<?php
						if(isset($data->tipe_pr)){
							if ($data->tipe_pr=='PP' and $data->status=='0'){
								if($data->nilai_pr>$data->terbayar){
									$new_sisa_pp=($data->nilai_pr-$data->terbayar);
									?>
									<label for="terbayar" class="col-sm-2 control-label">Terbayar</label>
									<div class="col-sm-5"><p class="form-control-static">
									<input type="text" id="terbayar" name="terbayar" class="divide" value="<?= $data->terbayar ?>" readonly>
									<a href="#newpp" data-toggle="modal" data-target="#newpp" class="btn btn-primary btn-xs">Buat PP Baru</a>
									</p>
									</div>
									<?php
								}
							}
						}
						?>
					</div>
					<?php if(isset($data->nilai_pr)){
						if($data->status=='1') {
							if($data->tipe_pr=='KASBON') { ?>
						<div class="form-group ">
							<label for="vendor_kasbon" class="col-sm-2 control-label">Supplier</label>
							<div class="col-sm-3">
								<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-user"></i></span>
								<input type="text" class="form-control" id="vendor_kasbon" name="vendor_kasbon" value="<?=$data->supplier_text?>" readonly>
								</div>
							</div>
							<label for="pic" class="col-sm-2 control-label">PIC</label>
							<div class="col-sm-3">
								<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-list"></i></span>
								<input type="text" class="form-control" id="pic" name="pic" value="<?php echo isset($data->pic) ? $data->pic: '' ; ?>" readonly>
								</div>
							</div>
						</div>

					<?php }
					if($data->tipe_pr=='PP') { ?>

						<div class="form-group ">
							<label for="vendor_pp" class="col-sm-2 control-label">Supplier</label>
							<div class="col-sm-3">
								<div class="input-group">
									<?php
									$datvendor[0]	= 'Select An Option';
									echo form_dropdown('vendor_pp',$datvendor, $data->supplier_text, array('id'=>'vendor_pp','class'=>'form-control select2 readonly', 'readonly'=>'readonly', 'style'=>'width:300px'));
									?>
								</div>
							</div>
							<label for="nilai_pp" class="col-sm-2 control-label">Nilai Pengajuan</label>
							<div class="col-sm-3">
								<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-list"></i></span>
								<input type="text" class="form-control divide" id="nilai_pp" name="nilai_pp" value="<?=$data->nilai_pengajuan?>" readonly>
								</div>
							</div>
						</div>
						<div class="form-group ">
							<label for="note_pp" class="col-sm-2 control-label">Note</label>
							<div class="col-sm-3">
								<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-list"></i></span>
								<input type="text" class="form-control" id="note_pp" name="note_pp" value="<?=$data->notes?>" readonly>
								</div>
							</div>
							<label for="divisi" class="col-sm-2 control-label">Tipe Pembayaran</label>
							<div class="col-sm-3">
								<div class="input-group">
									<?php
									echo form_dropdown('tipe_bayar',$tipe_bayar, 0, array('id'=>'tipe_bayar','class'=>'form-control readonly','readonly'=>'readonly'));
									?>
								</div>
							</div>
						</div>
						<div class="form-group ">
							<label for="quality_inspect" class="col-sm-2 control-label">Kualitas Produk</label>
							<div class="col-sm-3">
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-list"></i></span>
									<input type="text" class="form-control" id="quality_inspect" readonly name="quality_inspect" value="<?php echo set_value('quality_inspect', isset($data->quality_inspect) ? $data->quality_inspect: ''); ?>">
								</div>
							</div>
							<label for="qty_inspect" class="col-sm-2 control-label">Kesesuaian Qty</label>
							<div class="col-sm-3">
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-list"></i></span>
									<input type="text" class="form-control" id="qty_inspect" readonly name="qty_inspect" value="<?php echo set_value('qty_inspect', isset($data->qty_inspect) ? $data->qty_inspect: ''); ?>">
								</div>
							</div>
						</div>
						<div class="form-group ">
							<label for="note_release" class="col-sm-2 control-label">Note Release</label>
							<div class="col-sm-4">
									<textarea class="form-control" id="note_release" name="note_release" readonly><?php echo set_value('note_release', isset($data->note_release) ? $data->note_release: ''); ?></textarea>
							</div>

						</div>

					<?php }
					if($data->tipe_pr=='PO') { ?>

						<div class="form-group ">
							<label for="vendor_po1" class="col-sm-2 control-label">Alternatif Supplier I</label>
							<div class="col-sm-3">
								<div class="input-group">
									<?php
									$datvendor[0]	= 'Select An Option';
									echo form_dropdown('vendor_po1',$datvendor, $data->alt_supplier_1, array('id'=>'vendor_po1','class'=>'form-control select2 readonly', 'readonly'=>'readonly', 'style'=>'width:300px'));
									?>
								</div>
							</div>
							<label for="vendor_po2" class="col-sm-2 control-label">Alternatif Supplier II</label>
							<div class="col-sm-3">
								<div class="input-group">
									<?php
									echo form_dropdown('vendor_po2',$datvendor, $data->alt_supplier_2, array('id'=>'vendor_po2','class'=>'form-control select2 readonly', 'readonly'=>'readonly', 'style'=>'width:300px'));
									?>
								</div>
							</div>
						</div>
						<div class="form-group ">
							<label for="vendor_po3" class="col-sm-2 control-label">Alternatif Supplier III</label>
							<div class="col-sm-3">
								<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-user"></i></span>
								<input type="text" class="form-control" id="vendor_po3" name="vendor_po3" value="<?=$data->supplier_text?>" readonly >
								</div>
							</div>
						</div>

					<?php  }
						}
					} ?>

					<div class="box-footer">
						<div class="form-group">
							<div class="col-sm-offset-2 col-sm-10">
								<input type="hidden" id="type" name="type" value="approve">
								<a class="btn btn-danger" data-toggle="modal" onclick="cancel()"><i class="fa fa-minus-circle">&nbsp;</i>Cancel</a>
								<button type="submit" name="save" class="btn btn-success" id="approve" value="approve"><i class="fa fa-save">&nbsp;</i>Approve</button>
								<button type="button" name="save" class="btn btn-warning" id="reject" value="reject" data-toggle="modal" data-target="#frmreject"><i class="fa fa-times">&nbsp;</i>Reject</button>
								<!-- <label><input type="checkbox" name="edit_t" id="edit_t" value="1" onclick="opencek()" /> Edit</label> -->
							</div>
						</div>
					</div>

				</div>
            <?= form_close() ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="frmreject" tabindex="-1" role="dialog" aria-labelledby="frmreject">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
	<form id="newfrmpp" class="form-horizontal">
		<div class="modal-body">
			<label for="new_note_pp">Alasan</label>
			<textarea class="form-control" id="reject_reason" name="reject_reason"></textarea>
		</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary"onclick="savereject()">Save</button>
      </div>
	</form>
    </div>
  </div>
</div>

<script type="text/javascript">
	$("#frm_data :input").attr("readonly", "readonly");
	function savereject(){
		var nopr=$("#no_pr").val();
		var nid=$("#id").val();
		var nreject_reason=$("#reject_reason").val();
					$.ajax({
						url: siteurl+"po_aset/reject_approval_pr",
						dataType : "json",
						type: 'POST',
						data: {no_pr: nopr,reject_reason:nreject_reason,id:nid},
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

	function cekppn(){
		var ppn=$("#ppn").val();
		var nilai_pr=$("#nilai_pr").val();
		nilai_ppn=Math.ceil(Number(nilai_pr)*Number(ppn)/100);
		$("#nilai_ppn").val(nilai_ppn);
	}

    $(document).ready(function() {
        $(".select2").select2();
        //$("#id_aset").select2({});
		$(".divide").divide();
    });
	function get_aset(){
		var tgl = $("#tgl_pr").val();
		var Template	='<option value="0" selected>Empty List</option>';
		$('#id_aset').html(Template).trigger('chosen:updated');
		$.ajax({
			url         :siteurl+"aset/get_aset/"+tgl,
			type		: "POST",
			cache		: false,
			success: function(data){
				console.log(data);
				var datas	= $.parseJSON(data);
				if($.isEmptyObject(datas)==true){
				}else{
					var Template	='<option value="0" selected>Select An Option</option>';
					$.each(datas, function (index,values){
						Template	+='<option value="'+values.id+'">'+values.coa+' | '+values.nama_aset+' | '+values.nm_divisi+'</option>';
					});
				}
				$('#id_aset').html(Template).trigger('chosen:updated');
				get_budget();
			}
		});
	}
	function get_budget(){
		var id = $("#id_aset").val();
		var tgl = $("#tgl_pr").val();
		$.ajax({
			url         :siteurl+"aset/search/"+tgl+"/"+id,
			type		: "POST",
			dataType	: "json",
			cache		: false,
			success: function(data){
				if(data!=''){
					$("#budget").val(data.budget);
					$("#budget_sisa").val(data.sisa);
				}
				console.log(data);
			}
		});
	}
	function show_dtl(){
		var tipe= $('#tipe_pr').val();
		$("#tab-PO").addClass("hidden");
		$("#tab-PP").addClass("hidden");
		$("#tab-KASBON").addClass("hidden");
		if(tipe!='') {
			$("#tab-"+tipe).removeClass("hidden");
		}
	}
	var lastJQueryTS = 0 ;
	$(function () {
		// Daterange Picker
		$(".tgl").datepicker({
			todayHighlight: true,
			format : "yyyy-mm-dd",
			showInputs: true,
			autoclose:true,
			onSelect: function() {
				$(this).change();
				get_aset();
			}
			}).on("change", function() {
			var send = true;
			if (typeof(event) == 'object'){
				if (event.timeStamp - lastJQueryTS < 300){
					send = false;
				}
				lastJQueryTS = event.timeStamp;
			}
			if (send){
				get_aset();
			}

		});

	});

    $('#frm_data').on('submit', function(e){
        e.preventDefault();
		var d_error='';
		if(Number($("#budget_sisa").val())<Number($("#nilai_pr").val())) d_error='Sisa budget tidak mencukupi';
		if(Number($("#budget_sisa").val())<=0) d_error='Sisa budget sudah habis';
		if($("#divisi").val()=="0") d_error='Divisi harus di isi';
		if($("#id_aset").val()=="0") d_error='Aset harus di isi';
		if(d_error!=''){
			alert(d_error);
		}else{
			swal({
				  title: "Simpan data ini?", text: "Data tidak bisa di ubah kembali !", type: "warning", showCancelButton: true, confirmButtonClass: "btn-danger", confirmButtonText: "Ya", cancelButtonText: "Tidak", closeOnConfirm: true, closeOnCancel: true
				},
				function(isConfirm) {
				  if (isConfirm) {
					$(".select2").prop('disabled', false);
					var formdata = $("#frm_data").serialize();
					$.ajax({
						url: siteurl+"po_aset/save_approval_pr",
						dataType : "json",
						type: 'POST',
						data: formdata,
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
								$(".select2").prop('disabled', true);
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
							$(".select2").prop('disabled', true);
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
				}
				);
		}
    });

    function cancel(){
        $(".box").show();
        $("#form-data").hide();
    }

</script>
