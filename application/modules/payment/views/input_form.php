<?=form_open($this->uri->uri_string(),array('id'=>'frm_data','name'=>'frm_data','role'=>'form','class'=>'form-horizontal'));?>
<input type="hidden" name="id" id="id" value="<?php echo (isset($data->id) ? $data->id: ''); ?>">
<div class="tab-content">
	<div class="tab-pane active">
		<div class="box box-primary">
			<div class="box-body">
				<div class="form-group ">
					<label for="divisi" class="col-sm-2 control-label">No Dokumen</label>
					<div class="col-sm-2">
						<div class="input-group">
							<input type="text" class="form-control" id="no_doc" name="no_doc" value="<?php echo (isset($data->no_doc) ? $data->no_doc: ''); ?>" placeholder="Auto" required readonly>
						</div>
					</div>

					<label for="divisi" class="col-sm-2 control-label">Tanggal<font size="4" color="red"><B>*</B></font></label>
					<div class="col-sm-2">
						<div class="input-group">
							<input type="text" class="form-control tanggal" id="tanggal_doc" name="tanggal_doc" value="<?php echo (isset($data->tanggal_doc) ? $data->tanggal_doc: date("Y-m-d")); ?>" placeholder="Tanggal" required>
						</div>
					</div>

					<label for="divisi" class="col-sm-2 control-label">Dana Tersedia</label>
					<div class="col-sm-2">
						<div class="input-group">
							<input type="text" class="form-control divide" id="dana_tersedia" name="dana_tersedia" value="<?php echo (isset($data->dana_tersedia) ? $data->dana_tersedia: '0'); ?>" required>
						</div>
					</div>
				</div>
				<div class="table-responsive">
				<table class="table table-bordered table-striped">
					<thead>
						<tr>
						<th width="5">#</th>
						<th>NO PO</th>
						<th>Nama Supplier</th>
						<th>Nilai Invoice</th>
						<th>Sisa Pembayaran</th>
						<th>Jatuh Tempo</th>
						<th>Progress</th>
						<th>No Invoice Supplier</th>
						<th>Value Yang Diajukan</th>
						<th>Keterangan</th>
						<th><div class="pull-right"><a class="btn btn-success btn-xs btnnav" href="javascript:void(0)" title="Tambah" onclick="add_detail()" id="add-material"><i class="fa fa-plus"></i> Tambah</a></div></th>
						</tr>
					</thead>
					<tbody id="detail_body">
					<?php $total_invoice=0; $idd=1; $total_payment=0;
					if(!empty($data_detail)){
						foreach($data_detail AS $record){ ?>
						<tr id='tr1_<?=$idd?>' class='delAll'>
							<td><input type="hidden" name="detail_id[]" id="raw_id_<?=$idd?>" value="<?=$record->id;?>"><?=$idd;?>
							<input type="hidden" name="id_vendor[]" id="id_vendor<?=$idd;?>" value="<?=$record->id_vendor?>">
							<input type="hidden" name="category[]" id="category<?=$idd;?>" value="<?=$record->category?>">
							<input type="hidden" name="id_bill[]" id="id_bill<?=$idd;?>" value="<?=$record->id_bill?>" class="idbill"></td>
							<td><input type="text" readonly class="form-control" name="no_po[]" id="no_po<?=$idd;?>" value="<?=$record->no_po;?>"></td>
							<td><input type="text" readonly class="form-control" name="nama[]" id="nama<?=$idd;?>" value="<?=$record->nama;?>"></td>
							<td><input type="text" readonly class="form-control divide nilaiinvoice" name="nilai_invoice[]" id="nilai_invoice<?=$idd;?>" value="<?=($record->nilai_invoice);?>"></td>
							<td><input type="text" readonly class="form-control divide" name="sisa_bayar[]" id="sisa_bayar<?=$idd;?>" value="<?=($record->sisa_bayar);?>"></td>
							<td><input type="text" readonly class="form-control" name="tanggal_top[]" id="tanggal_top<?=$idd;?>" value="<?=$record->tanggal_top;?>"></td>
							<td><input type="text" readonly class="form-control" name="progress[]" id="progress<?=$idd;?>" value="<?=$record->progress;?>"></td>
							<td><input type="text" class="form-control" name="no_invoice[]" id="no_invoice<?=$idd;?>" value="<?=$record->no_invoice;?>"></td>
							<td><input type="text" class="form-control divide nilaibayar" name="nilai_bayar[]" id="nilai_bayar<?=$idd;?>" value="<?=($record->nilai_bayar);?>" onblur="cektotal()"></td>
							<td><textarea class="form-control" name="keterangan[]" id="keterangan<?=$idd;?>"><?=$record->keterangan;?></textarea></td>
							<td align='center'><button type='button' class='btn btn-danger btn-xs stsview' data-toggle='tooltip' onClick='delDetail(<?=$idd?>)' title='Hapus data'><i class='fa fa-close'></i> Hapus</button></td>
						</tr>
						<?php
							$idd++;
							$total_invoice=($total_invoice+$record->nilai_invoice);
							$total_payment=($total_payment+$record->nilai_bayar);
						}
					}
					echo '</tbody>';
					echo '<tfoot>
							<tr><td colspan="3">Total</td><td><input type="text" class="form-control divide" name="total_invoice" id="total_invoice" value="'.$total_invoice.'" readonly tab-index="-1"></td><td colspan="4"></td><td><input type="text" class="form-control divide" name="total_payment" id="total_payment" value="'.$total_payment.'" readonly tab-index="-1"></td></tr>
						</tfoot>
					';
					?>
				</table>
				</div>
			</div>
			<div class="box-footer">
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10">
						<?php
						if(isset($data)){
							if($data->status==0){
								echo '<button type="button" name="Approve" class="btn btn-primary btn-sm stsview" id="approve" onclick="data_approve()"><i class="fa fa-save">&nbsp;</i>Approve</button>';
							}
						}
						?>
						<button type="submit" name="save" class="btn btn-success btn-sm btnnav" id="submit"><i class="fa fa-save">&nbsp;</i>Simpan</button>
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
	var url_save = siteurl+'payment/save_data/';
	var url_approve = siteurl+'payment/approve/';
	var nomor=<?=$idd?>;
	<?php
	if(isset($type)){
		if($type=='view'){
			echo "$('#frm_data :input').prop('disabled', true);";
			echo "$('.btnnav').addClass('hidden');";
		}
	}?>
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

	function cektotal(){
		var sum=0;
		$('.nilaiinvoice').each(function() {
			sum += Number($(this).val());
		});
		$("#total_invoice").val(sum);
		var sum=0;
		$('.nilaibayar').each(function() {
			sum += Number($(this).val());
		});
		$("#total_payment").val(sum);
	}

	$(function () {
		$(".select2").select2();
		$(".tanggal").datepicker({
			todayHighlight: true,
			format : "yyyy-mm-dd",
			showInputs: true,
			autoclose:true
		});
	});

	function add_detail(){
		var idbill = [];
		var tanggal_doc = $("#tanggal_doc").val();
		$('.idbill').each(function() {
			idbill.push($(this).val());
		});
        $.ajax({
            url: siteurl+"payment/get_data",
            dataType : "json",
            type: 'POST',
            data: { allbill: idbill, tanggal: tanggal_doc },
            success: function(msg){
                if(msg['save']=='1'){
					$.each(msg['data'], function(index, element) {
						var Rows	 = 	"<tr id='tr1_"+nomor+"' class='delAll'>";
							Rows	+= 		"<td>";
							Rows	+=			"<input type='hidden' name='detail_id[]' id=raw_id_"+nomor+"' value=''>";
							Rows	+=			"<input type='hidden' name='category[]' id='category"+nomor+"' value='"+element.category+"'>";
							Rows	+=			"<input type='hidden' name='id_vendor[]' id='id_vendor"+nomor+"' value='"+element.id_vendor+"'>";
							Rows	+=			"<input type='hidden' name='id_bill[]' id='id_bill"+nomor+"' value='"+element.id_bill+"' class='idbill'></td>";
							Rows	+= 		"</td>";
							Rows	+= 		"<td><input type='text' readonly class='form-control' name='no_po[]' id='no_po"+nomor+"' value='"+element.no_po+"' /></td>";
							Rows	+= 		"<td><input type='text' readonly class='form-control' name='nama[]' id='nama"+nomor+"' value='"+element.nama+"' /></td>";
							Rows	+= 		"<td><input type='text' readonly class='form-control divide nilaiinvoice' name='nilai_invoice[]' id='nilai_invoice"+nomor+"' value='"+element.nilai_invoice+"' /></td>";
							Rows	+= 		"<td><input type='text' readonly class='form-control divide' name='sisa_bayar[]' id='sisa_bayar"+nomor+"' value='"+element.sisa_bayar+"' /></td>";
							Rows	+= 		"<td><input type='text' readonly class='form-control' name='tanggal_top[]' id='tanggal_top"+nomor+"' value='"+element.tanggal_top+"' /></td>";
							Rows	+= 		"<td><input type='text' readonly class='form-control' name='progress[]' id='progress"+nomor+"' value='"+element.progress+"' /></td>";
							Rows	+= 		"<td><input type='text' class='form-control' name='no_invoice[]' id='no_invoice"+nomor+"' value='' /></td>";
							Rows	+= 		"<td><input type='text' class='form-control divide nilaibayar' name='nilai_bayar[]' id='nilai_bayar"+nomor+"' value='0' onblur='cektotal()' /></td>";
							Rows	+= 		"<td><textarea class='form-control' name='keterangan[]' id='keterangan"+nomor+"'/></textarea></td>";
							Rows	+= 		"<td align='center'>";
							Rows 	+=			"<button type='button' class='btn btn-danger btn-xs' data-toggle='tooltip' onClick='delDetail("+nomor+")' title='Hapus data'><i class='fa fa-close'></i> Hapus</button>";
							Rows	+= 		"</td>";
							Rows	+= 	"</tr>";
							nomor++;
							$('#detail_body').append(Rows);
					});
					$(".divide").divide();

				} else {
                    swal({
                        title: "Gagal!",
                        text: "Data gagal diambil",
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

	function delDetail(row){
		$('#tr1_'+row).remove();
		cektotal();
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
