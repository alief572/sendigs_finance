<?php
$this->load->view('include/side_menu');	
?>

<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
 <form id="form-header-mutasi" method="post">
<div class="nav-tabs-salesorder">
     <div class="tab-content">
        <div class="tab-pane active" id="salesorder">
            <div class="box box-primary">
               	<?php //print_r($kode_customer)?>
                <div class="box-body">
                    <div class="col-sm-6 form-horizontal">
					    <div class="row">
                          <div class="form-group ">
                            <?php
                            $tglinv=date('Y-m-d');
                            ?>
                            <label for="tgl_bayar" class="col-sm-4 control-label">Tgl Bayar :</label>
                            <div class="col-sm-6">
                                <input type="hidden" name="no_invoice" id="no_invoice"  class="form-control input-sm" readonly>
                                <input type="hidden" name="tgl_invoice" id="tgl_invoice"  class="form-control input-sm" readonly>
                                <input type="date" name="tgl_bayar" id="tgl_bayar" class="form-control input-sm" value="<?= $header->tgl_pembayaran ?>" >
                            </div>
                          </div>
                        </div>
                        <div class="row">
                          <div hidden class="form-group">
                            <label for="alamat" class="col-sm-4 control-label">Alamat Customer </font></label>
                            <div hidden class="col-sm-6">
                              <textarea name="alamatcustomer" class="form-control input-sm" id="alamat" height=100 readonly></textarea>
                            </div>
                          </div>
                        </div>
						<div class="row">
						  <div class="form-group">
                            <label for="ket_bayar" class="col-sm-4 control-label">Keterangan Pembayaran </font></label>
                            <div class="col-sm-6">
                              <textarea name="ket_bayar" class="form-control input-sm" id="ket_bayar"><?= $header->keterangan ?></textarea>
                            </div>
                          </div>
						</div>
						<!--
						<div class="row">
                          <div class="form-group">
                              <label for="jenis_invoice" class="col-sm-4 control-label">Jenis Invoice </font></label>
                              <div class="col-sm-6">
                                    <select id="jenis_invoice" name="jenis_invoice" class="form-control input-sm" style="width: 100%;" tabindex="-1" required readonly>
                                    <?php
								   // $jenis = $inv->jenis_invoice;
									?>
									<option value="TR-01" <?php //echo ($jenis == 'TR-01') ? "selected": "" ?>>Uang Muka</option>
									<option value="TR-02"  <?php //echo ($jenis == 'TR-02') ? "selected": "" ?>>Progress</option>
									<option value="TR-03"   <?php // echo ($jenis == 'TR-03') ? "selected": "" ?>>Retensi</option>
									</select>
                              </div>
                          </div>
                        </div>-->
						<div class="row" hidden>
                          <div class="form-group">
                              <label for="jenis_invoice" class="col-sm-4 control-label">Jenis PPH</font></label>
                              <div class="col-sm-6">
                              <select class="form-control input-sm" name="jenis_pph" id="jenis_pph">
								 	<option value="">Pilih PPH</option>
									<option value="1106-01-04">PREPAID TAX - PPH 22</option>
									<option value="1106-01-03">PREPAID TAX - PPH 23</option>
									<option value="1106-01-06">PREPAID TAX - PPH PSL 4 (2)</option>
							  </select>   
                              </div>
                          </div>
                        </div>
						<div class="row">
                          <div class="form-group">
                              <label for="matauang" class="col-sm-4 control-label">Mata Uang</font></label>
                              <div class="col-sm-6">
                              <select class="form-control input-sm" name="matauang" id="matauang">
							        <?php
									$kurs = $header->kurs_bayar;
									?>
								 	<option value="idr" <?php echo ($kurs == 0) ? "selected": "" ?>>IDR</option>
									<option value="usd" <?php echo ($kurs > 0) ? "selected": "" ?> >USD</option>
							  </select>   
                              </div>
                          </div>
                        </div>
				   </div>
                   <div class="col-sm-6 form-horizontal">
                        <div class="row">
                          <div class="form-group">
                              <label class="col-sm-4 control-label">Nama Customer </font></label>
                              <div class="col-sm-6">
                                   <select class="form-control input-sm select2" name="customer" id="customer">
									<option value="">Pilih Customer</option>
									
									<?php
								    $cus = $header->id_customer;
									?>
									<?php
									foreach($customer as $cs){
										
										if($cus == $cs->id_customer ){
										$selected ='selected';
										} else {
										$selected ='';	
										}
									?>
									<option value="<?php echo $cs->id_customer?>" <?= $selected ?>><?php echo $cs->nm_customer?></option>
									 <?php } ?>
								  </select>
                                  <input type="hidden" name="id_customer" id="id_customer" class="form-control input-sm" value="<?= $header->id_customer?>" readonly>
                                  <input type="hidden" name="nm_customer" id="nm_customer" class="form-control input-sm" value="<?= $header->nm_customer?>" readonly>
                              </div>
                          </div>
                        </div>
                          <div class="form-group">
                              <label for="jenis_invoice" class="col-sm-4 control-label">Pilih Bank </font></label>
                              <div class="col-sm-6">
							    <select class="form-control input-sm" name="bank" id="bank">
									<option value="">Pilih Bank</option>
									
									<?php
								    $bank = $header->kd_bank;
									?>
									<?php
									foreach($coa as $bnk){
										
										if($bank == $bnk->no_perkiraan ){
										$selected ='selected';
										} else {
										$selected ='';	
										}
									?>
									<option value="<?php echo $bnk->no_perkiraan?>" <?= $selected ?>><?php echo $bnk->nama?></option>
									 <?php } ?>
								  </select>
                              </div>
                          </div>
						<div class="form-group ">
                            <label class="col-sm-4 control-label">Penerimaan Bank</label>
                            <div class="col-sm-6">
                              <input type="text" name="total_bank" class="form-control input-sm divide " id="total_bank" value="<?= $header->jumlah_bank_idr?>" onchange="cekall()">
							  <input type="hidden" name="id_unlocated" class="form-control input-sm" id="id_unlocated">
							  <input type="hidden" name="id_lebihbayar" class="form-control input-sm" id="id_lebihbayar">
                            </div>
                        </div>
						<div class="form-group ">
                            <label class="col-sm-4 control-label">Kurs</label> 
                            <div class="col-sm-6">
                              <input type="text" name="kurs" class="form-control input-sm divide " id="kurs" value="0">
                            </div>
                        </div>
					</div>
				</div>
			</div>
		</div>
   </div>
</div>
<div class="box box-default ">
	<div class="box-header">
		<div class="nav-tabs-custom">
			<div class="box active ">
            <ul class="nav nav-tabs">
			<li class="add"><a href="#" data-toggle="tab" id="tambah2">Add Invoice</a></li>
			<!--<li class="createunlocated"><a href="#" data-toggle="tab" id="createunlocated">Create Deposit</a></li>
			<li class="lebihbayar"><a href="#" data-toggle="tab" id="lebihbayar">Add Lebih Bayar</a></li>-->
			</ul>
			</div>
			<div id="scroll">
				<div class="box box-primary" id="data">
				</div>
            </div>
		</div>
		<!--<div class="box-tools">
			<button class="btn btn-sm btn-success add" id="tambah2" type="button" style="width: 100%;">
				<i class="fa fa-plus"></i> Add Invoice
			</button>
			<button class="btn btn-sm btn-success createunlocated " id="createunlocated" type="button" style="width: 100%;">
				<i class="fa fa-plus"></i> Create Unlocated
			</button>
			<button class="btn btn-sm btn-success lebih " id="lebih" type="button" style="width: 100%;">
				<i class="fa fa-plus"></i> Lebih Bayar
			</button>
		</div>-->
	</div>
    <div class="box-body">
            <table class="table table-bordered" width="100%" id="tabel-detail-mutasi">
				<thead>
					<tr class="bg-blue">
					    <th class="text-center">Code</th>
						<th class="text-center">No Invoice</th>
						<th class="text-center">Nama Customer</th>
						<th class="text-center">Kurs Jual</th>
						<th class="text-center">Total Invoice</th>
						<th class="text-center">Sisa Invoice</th>
						<th class="text-center">Total Bayar</th>
						<th class="text-center">Tipe Bayar</th>
						<th class="text-center">Jenis PPH</th>
						<th class="text-center">PPH</th>
						<th class="text-center">Action</th>
					</tr>
				</thead>
				<tbody id="list_item_mutasi">
				<?php 
				
				$Urut =0;
				foreach ($detail as $dt){	
                $Urut++;				
				?>
				
				<tr id="tr_<?=$Urut?>">
				<td style="padding:3px;"><input type="text" class="form-control input-sm kode-produk" name="kode_produk[]" id="kode_produk_<?=$Urut?>" readonly value="<?=$dt->no_invoice ?>"></td>
				<td style="padding:3px;"><input type="text" class="form-control input-sm" name="no_surat[]" id="no_surat<?=$Urut?>" readonly value="<?=$dt->no_ipp ?>"></td>
				<td style="padding:3px;"><input type="text" class="form-control input-sm" name="nm_customer2[]" id="nm_customer2<?=$Urut?>" readonly value="<?=$dt->nm_customer ?>"></td>
				<td style="padding:3px;"><input type="text" class="form-control input-sm" name="kurs_jual[]" id="kurs_jual<?=$Urut?>" style="text-align:center;" readonly value="<?=$dt->kurs_jual ?>"></td>
				<td style="padding:3px;"><input type="text" class="form-control input-sm" name="jml_invoice[]" id="jml_invoice<?=$Urut?>" style="text-align:center;" readonly value="<?=$dt->total_invoice_idr ?>"></td>
				<td style="padding:3px;"><input type="text" class="form-control input-sm" name="sisa_invoice[]" id="sisa_invoice<?=$Urut?>" style="text-align:center;" readonly value="<?=$dt->total_invoice_idr + $dt->total_bayar_idr ?>"></td>
				<td style="padding:3px;"><input type="text" class="form-control input-sm sum_change_bayar divide" name="jml_bayar[]" id="jml_bayar<?=$Urut?>" style="text-align:right;" value="<?=$dt->total_bayar_idr ?>" onchange="cekall()" ></td>
				
				<td style="padding:3px;">
				<select class="form-control input-sm" name="tipe_bayar[]" id="tipe_bayar<?=$Urut?>">
				<?php
				$tipe = $dt->tipe_bayar;
				?>
							
				<option value="">Pilih Tipe</option>
				
				<option value="PROGRESS" <?php echo ($tipe == 'PROGRESS') ? "selected": "" ?>>PROGRESS</option>
				<option value="RETENSI" <?php echo ($tipe == 'RETENSI') ? "selected": "" ?> >RETENSI</option>
				</select>
				</td>
				
				
				
				
				<td style="padding:3px;">
				<select class="form-control input-sm" name="jenis_pph2[]" id="jenis_pph2<?=$Urut?>">
				<?php
				$pph = $dt->jenis_pph;
				?>
							
				<option value="">Pilih PPH</option>
				
				<option value="1106-01-04" <?php echo ($pph == '1106-01-04') ? "selected": "" ?>>PREPAID TAX - PPH 22</option>
				<option value="1106-01-03" <?php echo ($pph == '1106-01-03') ? "selected": "" ?> >PREPAID TAX - PPH 23</option>
				<option value="1106-01-06" <?php echo ($pph == '1106-01-06') ? "selected": "" ?> >PREPAID TAX - PPH PSL 4 (2)</option>
			    </select>
				</td>
				<td style="padding:3px;"><input type="text" class="form-control input-sm sum_change_pph divide" name="pph[]" id="pph<?=$Urut?>" style="text-align:right;" value="<?=$dt->total_pph_idr ?>" onchange="cekall()"></td>
				<td style="padding:3px;"><center><div class="btn-group" style="margin:0px;">
				<button type="button" onclick="hapus(<?=$Urut?>)" id="delete-row" class="btn btn-sm btn-danger delete_bayar"><i class="fa fa-trash"></i> Hapus</button>
				</div></center></td>'
				</tr>
				
				<?php 
				}				
				?>
				</tbody>
            </table>
    </div>
</div>

<div class="text-right">
  <div class="box active">
    <div class="box-body">
		<div class="row">
			<div class="col-lg-6">
			</div>
			<div class="col-lg-6">
					    <div class="form-group ">
                            <label class="col-sm-4 control-label">Total Bayar Invoice</label>
                            <div class="col-sm-6">
                              <input type="text" name="total_invoice" class="form-control input-sm divide" id="total_invoice"  value="<?= $header->jumlah_piutang_idr?>" readonly tabindex="-1" onchange="cekall()">
                            </div>
                        </div>
                        <!--<div class="form-group ">
                            <label class="col-sm-4 control-label">Total Piutang </label>
                            <div class="col-sm-6">
                              <?php
							  //$totpiutang = $inv->total_invoice - $inv->total_bayar;
							  ?>
                              <input type="text" name="total_piutang" class="form-control input-sm" id="total_piutang" value="<?php //echo  number_format($totpiutang,2)?>" readonly>
                            </div>
                        </div>-->
					    <div class="form-group ">
                            <label class="col-sm-4 control-label">Selisih</label>
                            <div class="col-sm-6">
                              <input type="text" name="selisih" class="form-control input-sm divide" id="selisih" value="<?= $header->selisih_idr?>" readonly tabindex="-1">
                            </div>
                        </div>
                       <div class="form-group ">
                            <label class="col-sm-4 control-label">Biaya Administrasi </label>
                            <div class="col-sm-6">
                              <input type="text" name="biaya_adm" class="form-control input-sm divide" id="biaya_adm" value="<?= $header->biaya_admin_idr ?>" readonly onchange="cekall()">
                            </div>
                        </div>
						<div class="form-group ">
                            <label class="col-sm-4 control-label">PPH </label>
                            <div class="col-sm-6">
                            <input type="text" name="biaya_pph" class="form-control input-sm divide" id="biaya_pph" value="<?= $header->biaya_pph_idr ?>" readonly onchange="cekall()">
                            </div>
                        </div>
						 <div class="form-group" id="pakailebihbayar">
                            <label for="pakai_lebih_bayar" class="col-sm-4 control-label">Pakai Lebih Bayar</label>
                            <div class="col-sm-6">
                              <input type="text" name="pakai_lebih_bayar" class="form-control input-sm " id="pakai_lebih_bayar" value="0">
                            </div>
                        </div>						
						<div class="form-group ">
                            <label for="tambah_lebih_bayar" class="col-sm-4 control-label">Lebih Bayar</label>
                            <div class="col-sm-6">
                              <input type="text" name="tambah_lebih_bayar" class="form-control input-sm divide" id="tambah_lebih_bayar" value="0" onchange="cekall()">
                            </div>
                        </div>
						<div class="form-group hidden">
                            <label class="col-sm-4 control-label">Total Penerimaan </label>
                            <div class="col-sm-6">
                            <input type="text" name="total_terima" class="form-control input-sm divide" id="total_terima" value="0"  tabindex="-1">
                            </div>
                        </div>
						<div class="form-group ">
                            <label class="col-sm-4 control-label">Kontrol</label>
                            <div class="col-sm-6">
                            <input type="text" name="control" class="form-control input-sm divide" id="control" value="0"  tabindex="-1">
                            </div>
                        </div>
						<!--<div class="form-group ">
                            <label class="col-sm-4 control-label">Selisih Kurs</label>
                            <div class="col-sm-6">
                            <input type="text" name="selisih_kurs" class="form-control input-sm divide" id="selisih_kurs" value="0" readonly tabindex="-1">
                            </div>
                        </div>
						
						<div class="form-group">
                              <label for="template" class="col-sm-4 control-label">Pilih Template Jurnal</font></label>
                              <div class="col-sm-6">
                                  <?php
									$template[0]	= 'Select An Option';
									echo form_dropdown('template',$template, (isset($data)?$data->template:'0'), array('id'=>'jenis_pph','class'=>'form-control'));
								  ?>
                              </div>
                          </div> -->
			</div>
		</div><hr>
		<div class="row">
			<div class="col-lg-12">
			
				<button class="btn btn-danger" > 
				<a href="<?= base_url() ?>penerimaan">
				   <i class="fa fa-refresh"></i><b> Kembali</b>
			    </a> 
				</button>
			
				<button id="simpanpenerimaan" class="btn btn-primary" type="button" onclick="savemutasi()">
					<i class="fa fa-save"></i><b> Simpan Data Penerimaan</b>
				</button>
				
				
			</div>
		</div>
    </div>
  </div>
</div>

<div class="modal modal-primary" id="dialog-data-stok" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel"><span class="fa fa-file-pdf-o"></span>&nbsp;Data Invoice</h4>
      </div>
      <div class="modal-body" id="MyModalBodyStok" style="background: #FFF !important;color:#000 !important;">
          <table class="table table-bordered" width="100%" id="list_item_stok">
              <thead>
                  <tr>
                      <th width="30%">No Invoice</th>
                      <th width="30%">Nama Customer</th>
					  <th width="30%">Kurs Jual</th>
                      <th width="30%">Total Invoice</th>
					   <th width="30%">Sisa Invoice</th>
                      <th width="2%" class="text-center">Aksi</th>
                  </tr>
              </thead>
              <tbody>
                  
              </tbody>
          </table>
       </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">
        <span class="glyphicon glyphicon-remove"></span> Tutup</button>
        </div>
    </div>
  </div>
</div>

<div class="modal modal-primary" id="dialog-data-incomplete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel"><span class="fa fa-file-pdf-o"></span>&nbsp;Data Deposit</h4>
      </div>
      <div class="modal-body" id="MyModalBodyUnlocated" style="background: #FFF !important;color:#000 !important;">
          <table class="table table-bordered" width="100%" id="list_item_unlocated">
              <thead>
                  <tr>
                     <th class="text-center">Tanggal</th>
					 <th class="text-center">Keterangan</th>
					 <th class="text-center">Total Penerimaan</th>
					 <th class="text-center">Bank</th>
                     <th width="2%" class="text-center">Aksi</th>
                  </tr>
              </thead>
              <tbody>
                  <?php
				  
                  $invoice = $this->db->query("SELECT * FROM tr_unlocated_bank WHERE saldo !=0 ")->result();
				  if($invoice){
					foreach($invoice as $ks=>$vs){
                  ?>
						  <tr>
							  <td><?php echo $vs->tgl ?></td>
							  <td><center><?php echo $vs->keterangan ?></center></td>
							  <td><center><?php echo number_format($vs->totalpenerimaan) ?></center></td>
							  <td><center><?php echo $vs->bank ?></center></td>
							  <td>
								<center>
									<button id="btn-<?php echo $vs->id?>" class="btn btn-warning btn-sm" type="button" onclick="startunlocated('<?php echo $vs->id?>','<?php echo $vs->totalpenerimaan?>')">
										Pilih
									</button>
								</center>
							  </td>
						  </tr>
                  <?php
						}
					  }
				  ?>
              </tbody>
          </table>
       </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">
        <span class="glyphicon glyphicon-remove"></span> Tutup</button>
        </div>
    </div>
  </div>
</div>

<div class="modal modal-primary" id="dialog-data-lebihbayar" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel"><span class="fa fa-file-pdf-o"></span>&nbsp;Data Lebih Bayar</h4>
      </div>
      <div class="modal-body" id="MyModalBodyLebihbayar" style="background: #FFF !important;color:#000 !important;">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">
        <span class="glyphicon glyphicon-remove"></span> Tutup</button>
        </div>
    </div>
  </div>
</div>

<div class="modal modal-default fade" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" style="width: 100%;">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Tutup</span></button>
        <h4 class="modal-title" id="myModalLabel"><span class="fa fa-users"></span>&nbsp;List Invoice</h4>
      </div>
      <div class="modal-body" id="ModalView">
		...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">
        <span class="glyphicon glyphicon-remove"></span>  Tutup</button>
        </div>
    </div>
  </div>
</div>

<?php $this->load->view('include/footer'); ?>

<script>
	$(document).ready(function(){
		//$('.select2').select2();
		swal.close();
		$('#incomplete').hide();
		$('#pakailebihbayar').hide();
		$("#list_item_unlocated").DataTable({lengthMenu:[10,15,25,30]}).draw();
		$(".divide").divide();
	});
	 function savemutasi(){
		
	    if ($('#tgl_bayar').val() == "") {
          swal({
            title	: "Tanggal Tidak Boleh Kosong!",
            text	: "Isi Tanggal Terlebih Dahulu!",
            type	: "warning",
            timer	: 3000,
            showCancelButton	: false,
            showConfirmButton	: false,
            allowOutsideClick	: false
          });
        }
		  if ($('#matauang').val() == "") {
          swal({
            title	: "Mata uang tidak boleh kosong!",
            text	: "Silahkan Pilih Mata Uang Dahulu!",
            type	: "warning",
            timer	: 3000,
            showCancelButton	: false,
            showConfirmButton	: false,
            allowOutsideClick	: false
          });
        }
		
		else if ($('#matauang').val() == "usd" && $('#kurs').val() == "0" ) {
          swal({
            title	: "Perhatian",
            text	: "Kurs Harus di isi terlebih dahulu !",
            type	: "warning",
            timer	: 3000,
            showCancelButton	: false,
            showConfirmButton	: false,
            allowOutsideClick	: false
          });
        }
		else if ($('#control').val() != "0") {
          swal({
            title	: "Perhatian",
            text	: "Kontrol harus 0!",
            type	: "warning",
            timer	: 3000,
            showCancelButton	: false,
            showConfirmButton	: false,
            allowOutsideClick	: false
          });
        }
		else if ($('#bank').val() == "") {
          swal({
            title	: "BANK TIDAK BOLEH KOSONG!",
            text	: "ISI TANGGAL INVOICE!",
            type	: "warning",
            timer	: 3000,
            showCancelButton	: false,
            showConfirmButton	: false,
            allowOutsideClick	: false
          });
        }
		else if ($('#total_bank').val() != $('#total_terima').val()) {
          swal({
            title	: "JUMLAH BAYAR DAN PENERIMAAN BANK TIDAK SAMA!",
            text	: "SILAHKAN PERBAIKI DATA ANDA!",
            type	: "warning",
            timer	: 3000,
            showCancelButton	: false,
            showConfirmButton	: false,
            allowOutsideClick	: false
          });
        } else {
        swal({
          title: "Peringatan !",
          text: "Pastikan data sudah lengkap dan benar",
          type: "warning",
          showCancelButton: true,
          confirmButtonColor: "#DD6B55",
          confirmButtonText: "Ya, simpan!",
          cancelButtonText: "Batal!",
          closeOnConfirm: false,
          closeOnCancel: true
        },
        function(isConfirm){
			if(isConfirm) {
				//$('#simpanpenerimaan').hide();
				var formdata = $("#form-header-mutasi").serialize();
				$.ajax({
					url: base_url+"penerimaan/save_penerimaan",
					dataType : "json",
					type: 'POST',
					data: formdata,
					success: function(data){
						if(data.status == 1){
						swal({
						  title	: "Save Success!",
						  text	: data.pesan,
						  type	: "success",
						  timer	: 15000,
						  showCancelButton	: false,
						  showConfirmButton	: false,
						  allowOutsideClick	: false
						});
						window.location.href = base_url + active_controller+'/printout_pn/'+data.nomor;
					  }else{

						if(data.status == 2){
						  swal({
							title	: "Save Failed!",
							text	: data.pesan,
							type	: "warning",
							timer	: 10000,
							showCancelButton	: false,
							showConfirmButton	: false,
							allowOutsideClick	: false
						  });
						}else{
						  swal({
							title	: "Save Failed!",
							text	: data.pesan,
							type	: "warning",
							timer	: 10000,
							showCancelButton	: false,
							showConfirmButton	: false,
							allowOutsideClick	: false
						  });
						}

					  }
					},
					error: function(){
						swal({
							title: "Gagal!",
							text: "Batal Proses, Data bisa diproses nanti",
							type: "error",
							timer: 1500,
							showConfirmButton: false
						});
					}
				});
			}
        });
		}
    }
	
	function savedraf(){
		
	    if ($('#tgl_bayar').val() == "") {
          swal({
            title	: "Tanggal Tidak Boleh Kosong!",
            text	: "Isi Tanggal Terlebih Dahulu!",
            type	: "warning",
            timer	: 3000,
            showCancelButton	: false,
            showConfirmButton	: false,
            allowOutsideClick	: false
          });
        }
		  if ($('#matauang').val() == "") {
          swal({
            title	: "Mata uang tidak boleh kosong!",
            text	: "Silahkan Pilih Mata Uang Dahulu!",
            type	: "warning",
            timer	: 3000,
            showCancelButton	: false,
            showConfirmButton	: false,
            allowOutsideClick	: false
          });
        }
		
		else if ($('#matauang').val() == "usd" && $('#kurs').val() == "0" ) {
          swal({
            title	: "Perhatian",
            text	: "Kurs Harus di isi terlebih dahulu !",
            type	: "warning",
            timer	: 3000,
            showCancelButton	: false,
            showConfirmButton	: false,
            allowOutsideClick	: false
          });
        }
		else if ($('#control').val() != "0") {
          swal({
            title	: "Perhatian",
            text	: "Kontrol harus 0!",
            type	: "warning",
            timer	: 3000,
            showCancelButton	: false,
            showConfirmButton	: false,
            allowOutsideClick	: false
          });
        }
		else if ($('#bank').val() == "") {
          swal({
            title	: "BANK TIDAK BOLEH KOSONG!",
            text	: "ISI TANGGAL INVOICE!",
            type	: "warning",
            timer	: 3000,
            showCancelButton	: false,
            showConfirmButton	: false,
            allowOutsideClick	: false
          });
        }
		else if ($('#total_bank').val() != $('#total_terima').val()) {
          swal({
            title	: "JUMLAH BAYAR DAN PENERIMAAN BANK TIDAK SAMA!",
            text	: "SILAHKAN PERBAIKI DATA ANDA!",
            type	: "warning",
            timer	: 3000,
            showCancelButton	: false,
            showConfirmButton	: false,
            allowOutsideClick	: false
          });
        } else {
        swal({
          title: "Peringatan !",
          text: "Pastikan data sudah lengkap dan benar untuk di preview",
          type: "warning",
          showCancelButton: true,
          confirmButtonColor: "#DD6B55",
          confirmButtonText: "Ya!",
          cancelButtonText: "Batal!",
          closeOnConfirm: false,
          closeOnCancel: true
        },
        function(isConfirm){
			if(isConfirm) {
				//$('#simpanpenerimaan').hide();
				var formdata = $("#form-header-mutasi").serialize();
				$.ajax({
					url: base_url+"penerimaan/save_penerimaan_proforma",
					dataType : "json",
					type: 'POST',
					data: formdata,
					success: function(data){
						if(data.status == 1){
						swal({
						  title	: "Save Success!",
						  text	: data.pesan,
						  type	: "success",
						  timer	: 15000,
						  showCancelButton	: false,
						  showConfirmButton	: false,
						  allowOutsideClick	: false
						});
						window.location.href = base_url + active_controller+'/printout_draft/'+data.nomor;
					  }else{

						if(data.status == 2){
						  swal({
							title	: "Save Failed!",
							text	: data.pesan,
							type	: "warning",
							timer	: 10000,
							showCancelButton	: false,
							showConfirmButton	: false,
							allowOutsideClick	: false
						  });
						}else{
						  swal({
							title	: "Save Failed!",
							text	: data.pesan,
							type	: "warning",
							timer	: 10000,
							showCancelButton	: false,
							showConfirmButton	: false,
							allowOutsideClick	: false
						  });
						}

					  }
					},
					error: function(){
						swal({
							title: "Gagal!",
							text: "Batal Proses, Data bisa diproses nanti",
							type: "error",
							timer: 1500,
							showConfirmButton: false
						});
					}
				});
			}
        });
		}
    }

	function kembali_inv(){
		
		   window.location.href = base_url + active_controller;
    }
	function cekall(){
		var total_bank=$("#total_bank").val();
		var total_invoice=$("#total_invoice").val();
		var selisih=(parseFloat(total_bank)-parseFloat(total_invoice));
		$("#selisih").val(number_format(selisih,2));
		var biaya_adm=$("#biaya_adm").val();
		var biaya_pph=$("#biaya_pph").val();
		var tambah_lebih_bayar=$("#tambah_lebih_bayar").val();
		var control1=(parseFloat(selisih)+parseFloat(biaya_adm)+parseFloat(biaya_pph)-parseFloat(tambah_lebih_bayar));
		var control = number_format(control1,0);
		//console.log(control);
		$("#control").val(control);
		var total_terima=(parseFloat(total_invoice)-parseFloat(biaya_adm)-parseFloat(biaya_pph)+parseFloat(tambah_lebih_bayar));
		$("#total_terima").val(total_terima);
	}
	
	
	

	$("#tambah").click(function(){
		$('#dialog-data-stok').modal('show');

	});

	function startmutasi(id,surat,ipp,nm,avl,real,kurs){
		var avl2 =numx(avl);
		var real2= numx(real);
		
		//console.log(id);
		

		
       //  Cek Ada Data Gagal
	   var Cek_OK		= 1;
	   var Urut			= 1;
	   var total_row	= $('#list_item_mutasi').find('tr').length;
	   if(total_row > 0){
		  var kode_tr_akhir= $('#list_item_mutasi tr:last').attr('id');
		  var row_akhir		= kode_tr_akhir.split('_');
		  var Urut			= parseInt(row_akhir[1]) + 1;
		  $('#list_item_mutasi').find('tr').each(function(){
			  var kode_row	= $(this).attr('id');
			  var id_row	= kode_row.split('_');
			  var kode_produknya	= $('#kode_produk_'+id_row[1]).val();
			  if(id==kode_produknya){
				  Cek_OK	= 0;
			  }
		  });
	   }
	   if(Cek_OK==1){
			var idnya = "'"+id+"'";
			html='<tr id="tr_'+Urut+'">'
				+ '<td style="padding:3px;">'
				+ '<input type="text" class="form-control input-sm kode-produk" name="kode_produk[]" id="kode_produk_'+Urut+'" readonly value="'+surat+'">'
				+ '</td>'
				+ '<td style="padding:3px;"><input type="text" class="form-control input-sm" name="no_surat[]" id="no_surat'+Urut+'" readonly value="'+ipp+'"></td>'
				+ '<td style="padding:3px;"><input type="text" class="form-control input-sm" name="nm_customer2[]" id="nm_customer2'+Urut+'" readonly value="'+nm+'"></td>'
				+ '<td style="padding:3px;"><input type="text" class="form-control input-sm" name="kurs_jual[]" id="kurs_jual'+Urut+'" style="text-align:center;" readonly value="'+kurs+'"></td>'
				+ '<td style="padding:3px;"><input type="text" class="form-control input-sm" name="jml_invoice[]" id="jml_invoice'+Urut+'" style="text-align:center;" readonly value="'+avl2+'"></td>'
				+ '<td style="padding:3px;"><input type="text" class="form-control input-sm" name="sisa_invoice[]" id="sisa_invoice'+Urut+'" style="text-align:center;" readonly value="'+real2+'"></td>'
				+ '<td style="padding:3px;"><input type="text" class="form-control input-sm sum_change_bayar divide" name="jml_bayar[]" id="jml_bayar'+Urut+'" style="text-align:right;" value="'+number_format(real)+'" onchange="cekall()" ></td>'
				+ '<td style="padding:3px;"><select class="form-control input-sm" name="jenis_pph2[]" id="jenis_pph2'+Urut+'"><option value="">Pilih PPH</option><option value="1106-01-04">PREPAID TAX - PPH 22</option><option value="1106-01-03">PREPAID TAX - PPH 23</option><option value="1106-01-06">PREPAID TAX - PPH PSL 4 (2)</option></select></td>'
				+ '<td style="padding:3px;"><input type="text" class="form-control input-sm sum_change_pph divide" name="pph[]" id="pph'+Urut+'" style="text-align:right;" onchange="cekall()"></td>'
				+ '<td style="padding:3px;"><center><div class="btn-group" style="margin:0px;">'
				+ '<button type="button" onclick="deleterow('+Urut+','+idnya+')" id="delete-row" class="btn btn-sm btn-danger delete_bayar"><i class="fa fa-trash"></i> Hapus</button>'
				+ '</div></center></td>'
				+ '</tr>';
			$("#tabel-detail-mutasi").append(html);
			$("#btn-"+id).removeClass('btn-warning');
			$("#btn-"+id).addClass('btn-danger');
			$("#btn-"+id).attr('disabled',true);
			$("#btn-"+id).text('Sudah');
			sumchangebayar();
	   }
    }

	function deleterow(tr,id){
        $('#tr_'+tr).remove();
        $("#btn-"+id).removeClass('btn-danger');
        $("#btn-"+id).addClass('btn-warning');
        $("#btn-"+id).attr('disabled',false);
        $("#btn-"+id).text('Pilih');
		sumchangebayar();
    }
	
	function hapus(tr){
		
		console.log(tr);
		
        $('#tr_'+tr).remove();
	}

	//ARWANT
	$(document).on('keyup','.sum_change_bayar', function(){
		var jumlah_bayar = 0;
		$(".sum_change_bayar" ).each(function() {
			jumlah_bayar += getNum($(this).val().split(",").join(""));
		});
		$('#total_invoice').val(number_format(jumlah_bayar,2));
	});

	//SYAM
	$(document).on('keyup','.sum_change_pph', function(){
		var jumlah_bayar = 0;
		$(".sum_change_pph" ).each(function() {
			jumlah_bayar += getNum($(this).val().split(",").join(""));
		});
		$('#biaya_pph').val(number_format(jumlah_bayar,2));
		//totalterima();
	});

	function sumchangebayar(){
		var jumlah_bayar = 0;
		$(".sum_change_bayar" ).each(function() {
			jumlah_bayar += getNum($(this).val().split(",").join(""));
		});
		$('#total_invoice').val(number_format(jumlah_bayar,2));
	}

	function getNum(val) {
        if (isNaN(val) || val == '') {
            return 0;
        }
        return parseFloat(val);
    }

	function num(n) {
      return (n).toFixed(0).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }

	function num2(n) {
      return (n).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }

	function num3(n) {
      return (n).toFixed(0);
    }
	function numx(n) {
      return (n).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }

	function number_format (number, decimals, dec_point, thousands_sep) {
		// Strip all characters but numerical ones.
		number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
		var n = !isFinite(+number) ? 0 : +number,
			prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
			sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
			dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
			s = '',
			toFixedFix = function (n, prec) {
				var k = Math.pow(10, prec);
				return '' + Math.round(n * k) / k;
			};
		// Fix for IE parseFloat(0.55).toFixed(0) = 0;
		s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
		if (s[0].length > 3) {
			s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
		}
		if ((s[1] || '').length < prec) {
			s[1] = s[1] || '';
			s[1] += new Array(prec - s[1].length + 1).join('0');
		}
		return s.join(dec);
	}

	$(document).on('change', '#bank', function(){
		var dataCoa	  = $(this).val();
		if(dataCoa=='2101-07-01'){
			$('#incomplete').show();
		} else{
			$('#incomplete').hide();
		}
	});

	$("#incomplete").click(function(){
		$('#dialog-data-incomplete').modal('show');
//        $("#list_item_stok").DataTable({lengthMenu:[10,15,25,30]}).draw();
	});

	$("#lebihbayar-1").click(function(){
		$('#dialog-data-lebihbayar').modal('show');
		$('#pakailebihbayar').show();
//        $("#list_item_stok").DataTable({lengthMenu:[10,15,25,30]}).draw();
	});

	function startunlocated(id,value){

		$("#total_bank").val(value);
		$("#id_unlocated").val(id);
		$("#btn-"+id).removeClass('btn-warning');
		$("#btn-"+id).addClass('btn-danger');
		$("#btn-"+id).attr('disabled',true);
		$("#btn-"+id).text('Sudah');
		var totalBank     = parseFloat(value).toFixed(0);
		$('#total_bank').val(number_format(totalBank));
//		totalterima();
		cekall();
	   }

	function startlebihbayar(id,value){

		$("#pakai_lebih_bayar").val(value);
		$("#id_lebihbayar").val(id);
		$("#btn-"+id).removeClass('btn-warning');
		$("#btn-"+id).addClass('btn-danger');
		$("#btn-"+id).attr('disabled',true);
		$("#btn-"+id).text('Sudah');
		var totalBank     = parseFloat(value).toFixed(0);
		$('#pakai_lebih_bayar').val(number_format(totalBank));
//		totalterima();
		cekall();
	}

    $(document).on('click', '.add', function(){
		var id_customer=$("#customer").val();

		if (id_customer == "") {
          swal({
            title	: "Customer Tidak Boleh Kosong!",
            text	: "Pilih Customer Terlebih Dahulu!",
            type	: "warning",
            timer	: 3000,
            showCancelButton	: false,
            showConfirmButton	: false,
            allowOutsideClick	: false
          });
        }
		else {

		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Tambah Invoice</b>");
		$.ajax({
			type:'POST',
			url:base_url + active_controller +'/TambahInvoice/'+id_customer,
			data:{'id_customer':id_customer},
			success:function(data){
				$("#dialog-popup").modal();
				$("#ModalView").html(data);
			}
		})
		}
	});

	$(document).on('click', '#lebihbayar', function(){
		// $('#dialog-data-lebihbayar').modal('show');
		$('#pakailebihbayar').show();
		var id_customer=$("#customer").val();
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Tambah Lebih Bayar</b>");
		$.ajax({
			type:'POST',
			 url:siteurl+'penerimaan/TambahLebihBayar/'+id_customer,
			data:{'id_customer':id_customer},
			success:function(data){
				$("#dialog-data-lebihbayar").modal();
				$("#MyModalBodyLebihbayar").html(data);
			}
		})
	});

	$(document).on('click', '.lebih', function(){
		var id_customer=$("#customer").val();
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Tambah Lebih Bayar</b>");
		$.ajax({
			type:'POST',
			 url:siteurl+'penerimaan/lebihbayar',
			data:{'id_customer':id_customer},
			success:function(data){
				$("#dialog-popup").modal();
				$("#ModalView").html(data);
			}
		})
	});

	 $(document).on('change', '#customer', function(){
		var id_customer=$("#customer").val();
		$("#id_customer").val(id_customer);
	});
	
	$(document).on('change', '#matauang', function(){
		var mataUang	  = $(this).val();
		if(mataUang=='usd'){
			$('#kurs').show();
		} else{
			$('#kurs').hide();
		}
	});

	function totalterima(){
		cekall();
		/*
		var pakai_lebih_bayar   = parseFloat($('#pakai_lebih_bayar').val().split(",").join(""))
		var tambah_lebih_bayar   = parseFloat($('#tambah_lebih_bayar').val().split(",").join(""))
	    var biaya_adm   = parseFloat($('#biaya_adm').val().split(",").join(""))
		var total_bank	= parseFloat($('#total_bank').val().split(",").join(""));
        var biaya_pph	= parseFloat($('#biaya_pph').val().split(",").join(""));
		var Total       = parseInt(biaya_adm)+parseInt(total_bank)+parseInt(biaya_pph)+parseInt(pakai_lebih_bayar)-parseInt(tambah_lebih_bayar);
		$('#total_terima').val(number_format(Total));
		*/
	}

	$(document).on('click', '.createunlocated', function(){
		var id_customer=$("#customer").val();
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Tambah Unlocated</b>");
		$.ajax({
			type:'POST',
			 url:siteurl+'penerimaan/createunlocated',
			data:{'id_customer':id_customer},
			success:function(data){
				$("#dialog-popup").modal();
				$("#ModalView").html(data);
			}
		})
	});

// $('#tgl_bayar').datepicker({
	// format: 'yyyy-mm-dd',
	// todayHighlight: true
// });
</script>
