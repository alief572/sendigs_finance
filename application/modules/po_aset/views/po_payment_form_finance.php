<?php
$readonly='readonly';
$combocoa=' ';
if($datreq->status==1) {
	$readonly=' ';
	$combocoa=' select2';
}
if($datreq->status=='10') {
	$readonlypph=' ';
}else{
	if($datreq->status=='1') {
		$readonlypph=' readonly ';
	}else{
		$readonlypph=' readonly ';
	}
}
?>
<script src="<?= base_url('assets/js/cleave.min.js')?>"></script>
<div class="nav-tabs-area">
    <div class="tab-content">
        <div class="tab-pane active" id="area">
            <div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
            <div class="box box-primary">
            <?= form_open(base_url().'po_nonstock/save_payment_po',array('id'=>'frm_data','name'=>'frm_data','role'=>'form','class'=>'form-horizontal')) ?>
				<?php  if(isset($data->id)){$type='edit';}?>
				<input type="hidden" id="type" name="type" value="<?= isset($type) ? $type : 'add' ?>">
                <div class="box-body">

			    <div class="form-group ">
				   <label for="terbayar" class="col-sm-2 control-label">Nilai Terbayar</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-list"></i></span>
                        <input type="text" class="form-control divide" id="terbayar" name="terbayar" value="<?php echo $datarpo->terbayar?>" readonly tabindex="-1">
                        </div>
                    </div>
				   <label for="tgl_terima_invoice" class="col-sm-2 control-label">Tgl Terima Invoice</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-list"></i></span>
                        <input type="text" class="form-control <?php echo ($readonly==' '?'tanggal':'');?>" id="tgl_terima_invoice" name="tgl_terima_invoice" value="<?=$datreq->tgl_terima_invoice;?>" <?=$readonly?> >
                        </div>
                    </div>
                </div>
			    <div class="form-group ">
				   <label for="dpp" class="col-sm-2 control-label">DPP</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-list"></i></span>
                        <input type="text" class="form-control divide" id="dpp" name="dpp" value="<?php echo $datreq->dpp; ?>" onblur="cektotal()" <?=$readonly?>>
                        </div>
                    </div>
                </div>
					<div class="form-group ">
					    <label for="ppn" class="col-sm-2 control-label">PPN</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<?php
							echo form_dropdown('ppn',$datppn, $datreq->ppn, array('id'=>'ppn','class'=>'form-control '.$readonly,$readonly=>$readonly,'required'=>'required','onchange'=>'cektotal()'));
							?>
							</div>
						</div>
					    <label for="nilai_ppn" class="col-sm-2 control-label">Nilai PPN</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="nilai_ppn" name="nilai_ppn" value="<?=$datreq->nilai_ppn?>" readonly tabindex="-1">
							</div>
						</div>
					</div>
					<div class="form-group ">
						<label for="t_biaya_lain" class="col-sm-2 control-label">Biaya Lain</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="t_biaya_lain" name="t_biaya_lain" value="<?=$datreq->t_biaya_lain?>" onblur="cektotal()" <?=$readonly?>>
							</div>
							<div class="input-group">
								<?php
								$datcoa[0]	= 'Select An Option';
								echo form_dropdown('coa_lain',$datcoa, ($datreq->coa_lain==''?0:$datreq->coa_lain), array('id'=>'coa_lain','class'=>'form-control '.($readonly==''?' select2':'').' '.$readonly,$readonly=>$readonly));
								?>
							</div>
						</div>
					   <label for="biaya_lain_note" class="col-sm-2 control-label">Keterangan Biaya Lain</label>
						<div class="col-sm-4">
							<textarea class="form-control" id="biaya_lain_note" name="biaya_lain_note" <?=$readonly?>><?=$datreq->biaya_lain_note?></textarea>
						</div>
					</div>
					<div class="form-group ">
						<label for="potongan" class="col-sm-2 control-label">Nilai Potongan</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="potongan" name="potongan" value="<?=$datreq->potongan?>" onblur="cektotal()" <?=$readonly?>>
							</div>
							<div class="input-group">
								<?php
								echo form_dropdown('coa_potongan',$datcoa, ($datreq->coa_potongan==''?0:$datreq->coa_potongan), array('id'=>'coa_potongan','class'=>'form-control '.($readonly==''?' select2':'').' '.$readonly,$readonly=>$readonly));
								?>
							</div>
						</div>
						<label for="potongan_note" class="col-sm-2 control-label">Note Potongan</label>
						<div class="col-sm-4">
							<textarea class="form-control" id="potongan_note" name="potongan_note" <?=$readonly?>><?=$datreq->potongan_note?></textarea>
						</div>
					</div>
					<div class="form-group ">
					   <label for="jenis_pph" class="col-sm-2 control-label">Jenis PPH</label>
						<div class="col-sm-3">
							<div class="input-group">
								<?php
								$pphpembelian[0]	= 'Select An Option';
								echo form_dropdown('jenis_pph',$pphpembelian, ($datreq->jenis_pph==''?0:$datreq->jenis_pph), array('id'=>'jenis_pph','class'=>'form-control '.$readonlypph,$readonlypph=>$readonlypph));
								?>
							</div>
						</div>
						<label for="pph" class="col-sm-2 control-label">PPH</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="pph" name="pph" value="<?=$datreq->pph?>" onblur="cektotal()"  <?=$readonlypph?>>
							</div>
						</div>
					</div>

					<div class="form-group ">
					   <label for="nilai_bayar" class="col-sm-2 control-label">Nilai Pembayaran</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="nilai_bayar" name="nilai_bayar" value="<?=$datreq->nilai_bayar?>" readonly tabindex="-1">
							</div>
						</div>
					   <label for="top" class="col-sm-2 control-label">TOP (hari)</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control" id="top" name="top" value="<?=$datreq->top?>" <?=$readonly?>>
							</div>
						</div>
					</div>
					<div class="form-group ">
						<label for="bank" class="col-sm-2 control-label">Bank</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<?php
							echo form_dropdown('bank',$datbank, $datreq->bank, array('id'=>'bank','class'=>'form-control '.$readonly,$readonly=>$readonly));
							?>
							</div>
						</div>
					</div>
			    <div class="form-group ">
					<label for="jenis_pembayaran" class="col-sm-2 control-label">Jenis Pembayaran</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-user"></i></span>
                            <?php
							$datbayar[0]	= 'Select An Option';
							echo form_dropdown('jenis_pembayaran',$datbayar, $datreq->jenis_pembayaran, array('id'=>'jenis_pembayaran','class'=>'form-control '.$readonly,$readonly=>$readonly,'required'=>'required', 'onchange'=>'cekjenis()'));
							?>
                        </div>
                    </div>
				   <label for="notes" class="col-sm-2 control-label">Note</label>
                    <div class="col-sm-4">
                        <textarea class="form-control" id="notes" name="notes" <?=$readonly?>><?=$datreq->notes?></textarea>
                    </div>
                </div>
<div id="transfers" class="hidden">
			    <div class="form-group ">
				   <label for="bank_id" class="col-sm-2 control-label">Bank</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-list"></i></span>
                        <input type="text" class="form-control" id="bank_id" name="bank_id" value="<?=$datreq->bank_id?>" <?=$readonly?>>
                        </div>
                    </div>
					<label for="nama_rekening" class="col-sm-2 control-label">Nama Pemilik Rekening</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-list"></i></span>
                        <input type="text" class="form-control" id="nama_rekening" name="nama_rekening" value="<?=$datreq->nama_rekening?>"  <?=$readonly?>>
                        </div>
                    </div>
                </div>
			    <div class="form-group ">
				   <label for="nomor_rekening" class="col-sm-2 control-label">No Rekening</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-list"></i></span>
                        <input type="text" class="form-control" id="nomor_rekening" name="nomor_rekening" value="<?=$datreq->nomor_rekening?>" <?=$readonly?>>
                        </div>
                    </div>
                </div>
</div>
			    <div class="form-group hidden" id="cashgiro">
					<label for="giro_atas_nama" class="col-sm-2 control-label">Atas Nama</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-list"></i></span>
                        <input type="text" class="form-control" id="giro_atas_nama" name="giro_atas_nama" value="<?=$datreq->giro_atas_nama?>" <?=$readonly?> >
                        </div>
                    </div>
                </div>
			    <div class="form-group ">
				   <label for="no_voucher" class="col-sm-2 control-label">No Voucher</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-list"></i></span>
                        <input type="text" class="form-control" id="no_voucher" name="no_voucher" value="<?=$datreq->no_voucher?>" readonly>
                        </div>
                    </div>
					<label for="tgl_voucher" class="col-sm-2 control-label">Tgl Voucher</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-list"></i></span>
                        <input type="text" class="form-control <?php echo ($readonly==' '?'tanggal':'');?>" id="tgl_voucher" name="tgl_voucher" value="<?=$datreq->tgl_voucher?>" required <?=$readonly?>>
                        </div>
                    </div>
                </div>

<?php if($datreq->status=='10') { ?>

					<div class="row">
						<div class="form-group ">
							<label class="control-label col-sm-2">Alasan Penolakan</label>
							<div class="col-sm-3">
								<textarea class="form-control" readonly id="rejectreason" name="rejectreason"><?php echo isset($datreq->reject_reason) ? $datreq->reject_reason: ''; ?></textarea>
							</div>
						</div>
					</div>
<?php } ?>
					<div class="row">
						<div class="col-sm-3">
							<label>Invoice/Faktur/Nota
							<?php echo form_dropdown('c_inv',$datcombodata,  $datarpo->c_inv, array('id'=>'c_inv','class'=>'form-control readonly','readonly'=>'readonly')); ?>
							</label>
							<br />
							No Invoice  <input type="text" name="t_inv" class="form-control" value="<?=$datarpo->t_inv?>" readonly /><br />
							Tgl Invoice  <input type="text" name="t_inv_tgl" class="form-control" value="<?=$datarpo->t_inv_tgl?>" readonly />
						</div>
						<div class="col-sm-3">
							<label>Faktur Pajak
							<?php echo form_dropdown('c_faktur',$datcombodata, $datarpo->c_faktur, array('id'=>'c_faktur','class'=>'form-control readonly ','readonly'=>'readonly')); ?>
							</label><br />
							No Faktur<input type="text" id="t_faktur" name="t_faktur" class="form-control t_faktur" placeholder="xxx.xxx-xx-xxxxxxxx" value="<?=$datarpo->t_faktur?>" readonly /><br />
							Tgl Faktur<input type="text" name="t_faktur_tgl" class="form-control" value="<?=$datarpo->t_faktur_tgl?>" readonly />
						</div>
						<div class="col-sm-3">
							<label>Surat Jalan
							<?php echo form_dropdown('c_surat_jalan',$datcombodata, $datarpo->c_surat_jalan, array('id'=>'c_surat_jalan','class'=>'form-control readonly ','readonly'=>'readonly')); ?>
							</label><br />
							No Surat Jalan  <input type="text" name="t_surat_jalan" class="form-control" value="<?=$datarpo->t_surat_jalan?>" readonly />
						</div>
						<div class="col-sm-3">
							<label>Kontrak Kerjasama
							<?php echo form_dropdown('c_kontrak',$datcombodata, $datarpo->c_kontrak, array('id'=>'c_kontrak','class'=>'form-control readonly','readonly'=>'readonly')); ?>
							</label><br />
							No Kontrak  <input type="text" name="t_kontrak" class="form-control" value="<?=$datarpo->t_kontrak?>" readonly />
						</div>
					</div>


					<hr >
					<h4>Info Penerimaan Barang</h4>
					<div class="form-group ">
						<label for="no_po" class="col-sm-2 control-label">No PO</label>
						<div class="col-sm-3">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-list"></i></span>
								<input type="text" class="form-control" id="no_po" name="no_po" value="<?php echo $data->no_po; ?>" readonly tabindex="-1">
							</div>
						</div>
						<label for="tgl_periksa" class="col-sm-2 control-label">Tgl Pemeriksaan</label>
						<div class="col-sm-3">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
								<input type="text" class="form-control" id="tgl_periksa" name="tgl_periksa" value="<?php echo set_value('tgl_periksa', isset($datarpo->tgl_periksa) ? $datarpo->tgl_periksa: date("Y-m-d")); ?>"readonly tabindex="-1">
							</div>
						</div>
					</div>
					<div class="form-group ">
						<label for="request_payment" class="col-sm-2 control-label">Nilai Pembayaran</label>
						<div class="col-sm-3">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-list"></i></span>
								<input type="text" class="form-control divide" id="request_payment" name="request_payment" value="<?php echo set_value('request_payment', isset($datarpo->request_payment) ? $datarpo->request_payment: 0); ?>" readonly tabindex="-1">
							</div>
						</div>
						<label for="request_note" class="col-sm-2 control-label">Note Pembayaran</label>
						<div class="col-sm-3">
								<textarea class="form-control" id="request_note" name="request_note" readonly tabindex="-1"><?php echo set_value('request_note', isset($datarpo->request_note) ? $datarpo->request_note: ''); ?></textarea>
						</div>
					</div>
					<div class="form-group ">
						<label for="quality_inspect" class="col-sm-2 control-label">Kualitas Produk</label>
						<div class="col-sm-3">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-list"></i></span>
								<input type="text" class="form-control" id="quality_inspect" name="quality_inspect" value="<?php echo set_value('quality_inspect', isset($datarpo->quality_inspect) ? $datarpo->quality_inspect: ''); ?>" readonly tabindex="-1">
							</div>
						</div>
						<label for="qty_inspect" class="col-sm-2 control-label">Kesesuaian Qty</label>
						<div class="col-sm-3">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-list"></i></span>
								<input type="text" class="form-control" id="qty_inspect" name="qty_inspect" value="<?php echo set_value('qty_inspect', isset($datarpo->qty_inspect) ? $datarpo->qty_inspect: ''); ?>" readonly tabindex="-1">
							</div>
						</div>
					</div>
					<div class="form-group ">
						<label for="note_release" class="col-sm-2 control-label">Note Release</label>
						<div class="col-sm-3">
								<textarea class="form-control" id="note_release" name="note_release" readonly tabindex="-1"><?php echo set_value('note_release', isset($datarpo->note_release) ? $datarpo->note_release: ''); ?></textarea>
						</div>
						<label  class="col-sm-2 control-label">Dibuat oleh</label>
						<div class="col-sm-3"><p class="form-control-static"><?php echo (isset($datarpo->username) ? $datarpo->username : ''); ?></p></div>
					</div>
					<hr>
					<h4>Info PO</h4>
					<div class="form-group ">
						<label for="vendor_id" class="col-sm-2 control-label">Supplier</label>
						<div class="col-sm-3">
							<?php
							echo form_dropdown('vendor_id',$datvendor, ($data->vendor_id!=''?$data->vendor_id:0), array('id'=>'vendor_id','class'=>'form-control','readonly'=>'readonly'));
							?>
							<div id="info_vendor"></div>
						</div>
						<label for="vendor_reason" class="col-sm-2 control-label">Alasan Pilih Supplier</label>
						<div class="col-sm-3">
							<textarea class="form-control" id="vendor_reason" name="vendor_reason" readonly><?php echo isset($data->vendor_reason) ? $data->vendor_reason: ''; ?></textarea>
						</div>
					</div>
					<div class="form-group ">
						<label for="info_desc" class="col-sm-2 control-label">Deskripsi</label>
						<div class="col-sm-3">
							<textarea class="form-control" id="info_desc" name="info_desc" readonly tabindex="-1"><?php echo isset($data->info_desc) ? $data->info_desc: ''; ?></textarea>
						</div>
					    <label for="notes" class="col-sm-2 control-label">Note</label>
						<div class="col-sm-3">
							<textarea class="form-control" id="notes" name="notes" readonly tabindex="-1"><?=$data->notes?></textarea>
						</div>
					</div>
					<div class="form-group ">
						<label for="qty" class="col-sm-2 control-label">Qty</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="qty" name="qty" value="<?php echo isset($data->qty) ? $data->qty: 0; ?>"  readonly tabindex="-1">
							</div>
						</div>
						<label for="harga_satuan" class="col-sm-2 control-label">Harga Satuan</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="harga_satuan" name="harga_satuan" value="<?php echo isset($data->harga_satuan) ? $data->harga_satuan: 0; ?>"  readonly tabindex="-1" >
							</div>
						</div>
					</div>
					<!--<div class="form-group ">
					    <label for="ppn" class="col-sm-2 control-label">PPN</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<?php
							//echo form_dropdown('ppn',$datppn, $data->ppn, array('id'=>'ppn','class'=>'form-control','required'=>'required','readonly'=>'readonly'));
							?>
							</div>
						</div>
					    <label for="nilai_ppn" class="col-sm-2 control-label">Nilai PPN</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="nilai_ppn" name="nilai_ppn" value="<?=$data->nilai_ppn?>" readonly tabindex="-1">
							</div>
						</div>
					</div>-->
					<div class="form-group ">
						<input type="hidden" id="harga_total" name="harga_total" value="<?= $data->harga_total; ?>">
					   <label for="total_nilai_po" class="col-sm-2 control-label">Total PO</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="total_nilai_po" name="total_nilai_po" value="<?=$data->total_nilai_po?>" readonly tabindex="-1">
							</div>
						</div>
						<label  class="col-sm-2 control-label">Dibuat oleh</label>
						<div class="col-sm-3"><p class="form-control-static"><?php echo (isset($data->username) ? $data->username : ''); ?></p></div>
					</div>
					<hr>
					<h4>Info PR</h4>
					<div class="form-group ">
						<label for="no_pr" class="col-sm-2 control-label">No PR</label>
						<div class="col-sm-3">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-list"></i></span>
								<input type="text" class="form-control" id="no_pr" name="no_pr" value="<?php echo set_value('no_pr', isset($datapr->no_pr) ? $datapr->no_pr: ""); ?>" placeholder="Automatic" readonly tabindex="-1">
							</div>
						</div>
						<label for="tgl_pr" class="col-sm-2 control-label">Tgl PR</label>
						<div class="col-sm-3">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
								<input type="text" class="form-control" id="tgl_pr" name="tgl_pr" value="<?php echo set_value('tgl_pr', isset($datapr->tgl_pr) ? $datapr->tgl_pr: date("Y-m-d")); ?>" readonly>
							</div>
						</div>
					</div>
					<div class="form-group ">
						<label for="id_aset" class="col-sm-2 control-label">Aset</label>
						<div class="col-sm-3">
							<?php
							echo form_dropdown('id_aset',$dataaset, $datapr->id_aset, array('id'=>'id_aset','class'=>'form-control','readonly'=>'readonly'));
							?>
						</div>
						<label for="description" class="col-sm-2 control-label">Deskripsi</label>
						<div class="col-sm-3">
							<textarea class="form-control" id="description" name="description" readonly><?php echo isset($datapr->description) ? $datapr->description: ''; ?></textarea>
						</div>
					</div>
					<div class="form-group ">
						<label class="col-sm-2 control-label">Nilai Budget</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="budget" name="budget" value="<?php echo set_value('budget', isset($datapr->budget) ? $datapr->budget: 0); ?>" placeholder="0" readonly tabindex="-1">
							</div>
						</div>
						<label class="col-sm-2 control-label">Sisa Budget</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="budget_sisa" name="budget_sisa" value="<?php echo set_value('budget_sisa', isset($datapr->budget_sisa) ? $datapr->budget_sisa: 0); ?>" placeholder="0" readonly tabindex="-1">
							</div>
						</div>
					</div>
					<div class="form-group ">
						<label for="nilai_pr" class="col-sm-2 control-label">Nilai PR</label>
						<div class="col-sm-3">
							<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-list"></i></span>
							<input type="text" class="form-control divide" id="nilai_pr" name="nilai_pr" value="<?php echo isset($datapr->nilai_pr) ? $datapr->nilai_pr: 0; ?>" readonly>
							</div>
						</div>
						<label for="no_coa" class="col-sm-2 control-label">Tipe PR</label>
						<div class="col-sm-3">
							<div class="input-group">
							<?php if(isset($datapr->nilai_pr)){?>
								<input type="text" class="form-control" id="tipe_pr" name="tipe_pr" value="<?php echo $datapr->tipe_pr ?>" readonly>
							<?php } ?>
							</div>
						</div>
					</div>
					<div class="form-group ">
						<label  class="col-sm-2 control-label">Dibuat oleh</label>
						<div class="col-sm-3"><p class="form-control-static"><?php echo (isset($datapr->username) ? $datapr->username : ''); ?></p></div>
					</div>

					<div class="box-footer">
						<div class="form-group">
							<div class="col-sm-offset-2 col-sm-10">
								<input type="hidden" id="status" name="status" value="<?=$datreq->status?>">
								<input type="hidden" id="id" name="id" value="<?=$datreq->id; ?>">
								<input type="hidden" id="no_payment" name="no_payment" value="<?=$datreq->no_payment; ?>">
								<input type="hidden" id="no_request" name="no_request" value="<?=$datreq->no_request; ?>">
<?php if($datreq->status>='2') { ?>
	<?php if($datreq->status=='10') { ?>
								<button type="submit" name="save" class="btn btn-success" id="submit"><i class="fa fa-save">&nbsp;</i>Save</button>
	<?php }else{ ?>

								<button type="submit" name="save" class="btn btn-success" id="submit"><i class="fa fa-save">&nbsp;</i>Approve</button>
								<button type="button" name="save" class="btn btn-warning" id="reject" value="reject" data-toggle="modal" data-target="#frmreject"><i class="fa fa-times">&nbsp;</i>Reject</button>
	<?php } ?>
<?php }else{ ?>
								<button type="submit" name="save" class="btn btn-success" id="submit"><i class="fa fa-save">&nbsp;</i>Save</button>
<?php } ?>
								<a class="btn btn-danger" data-toggle="modal" onclick="cancel()"><i class="fa fa-minus-circle">&nbsp;</i>Cancel</a>
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
        <button type="button" class="btn btn-primary" onclick="savereject()">Save</button>
      </div>
	</form>
    </div>
  </div>
</div>

<script type="text/javascript">
	function savereject(){
		var nopr=$("#no_request").val();
		var nid=$("#id").val();
		var nreject_reason=$("#reject_reason").val();
					$.ajax({
						url: siteurl+"po_aset/reject_approval_po_payment_finance",
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

	function cekjenis(){
		var jenis=$("#jenis_pembayaran").val();
		switch (jenis){
			case 'CASH':
			case 'GIRO':
				$("#transfers").addClass('hidden');
				$("#cashgiro").removeClass('hidden');
			break;
			case 'TRANSFER':
				$("#cashgiro").addClass('hidden');
				$("#transfers").removeClass('hidden');
			break;
			default:
				$("#cashgiro").addClass('hidden');
				$("#transfers").addClass('hidden');
		}
	}

	function cektotal(){
		var maxpay=<?php echo $datreq->dpp; ?>;
		var dpp=$("#dpp").val();
		if(Number(dpp)>Number(maxpay)){
			dpp=maxpay;
			$("#dpp").val(maxpay);
			alert("DPP maksimal = "+maxpay);
		}
		var pph=$("#pph").val();
		var ppn=$("#ppn").val();
		var t_biaya_lain=$("#t_biaya_lain").val();
		nilai_ppn=Math.ceil(Number(dpp)*Number(ppn)/100);
		$("#nilai_ppn").val(nilai_ppn);
		var potongan = $("#potongan").val();
		nilai_bayar=(Number(dpp)-Number(pph)+Number(nilai_ppn)+Number(t_biaya_lain)-Number(potongan));
		$("#nilai_bayar").val(nilai_bayar);
	}

	$(function () {
		// Daterange Picker
		$(".tanggal").datepicker({
			todayHighlight: true,
			format : "yyyy-mm-dd",
			showInputs: true,
			autoclose:true
		});
	});

    $(document).ready(function() {
		<?php if ($datreq->status>=2) { ?>
			$('.readonly').css('pointer-events','none');
		<?php } ?>
		$(".divide").divide();
		$(".select2").select2();
    });
	$(function () {
		// Daterange Picker
		$(".tgl").datepicker({
			todayHighlight: true,
			format : "yyyy-mm-dd",
			showInputs: true,
			autoclose:true
		});
	});
    $('#frm_data').on('submit', function(e){
        e.preventDefault();
		var tgl_voucher=$("#tgl_voucher").val();
		if(tgl_voucher==''){
			alert("Tanggal voucher harus diisi");
			return false
		}
        var formdata = $("#frm_data").serialize();
        $.ajax({
            url: siteurl+"po_aset/save_payment_po_finance",
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
    });

    function cancel(){
        $(".box").show();
        $("#form-data").hide();
    }

var cleave = new Cleave('.t_faktur', {
    delimiters: ['.', '-', '-','.'],
    blocks: [3, 3, 2, 8],
    numericOnly: true
});
    $(document).ready(function() {
		var id_vendor=$("#vendor_id").val();
		if(id_vendor!=''){
			$.ajax({
				type	: "POST",
				url		: siteurl+"purchase_order/vendor_info/"+id_vendor,
				success	: function(ret){
					$("#info_vendor").html(ret);
				}
			});
		}
	});

</script>
