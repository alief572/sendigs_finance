<?php
// print_r($id_bq);
// exit;
$data_header        = $this->db->query("SELECT * FROM tr_invoice_payment WHERE kd_pembayaran ='$kodebayar'")->row();
$data_detail        = $this->db->query("SELECT sum(total_bayar) as total_bayar FROM tr_invoice_payment_detail WHERE kd_pembayaran ='$kodebayar'")->row();
$alamat_cust        =  $this->db->query("SELECT * FROM customer WHERE id_customer = '$data_header->id_customer'")->row();
//$pay_term           =  $this->db->query("SELECT * FROM quotation_payment_term WHERE id_quotation = '$data_header->no_ipp'")->row(); 
//$jenis				=  $data_header->jenis_invoice;
//$ppn				=  $data_header->total_ppn;
// $dt_fabric          = $this->db->query("SELECT * FROM tr_invoice_detail WHERE no_invoice ='$no_invoice' AND kategori_detail ='FABRIC'")->result();
// $rail               = $this->db->query("SELECT * FROM tr_invoice_detail WHERE no_invoice ='$no_invoice' AND kategori_detail ='BQ'")->result();
// $sew                = $this->db->query("SELECT * FROM tr_invoice_detail WHERE no_invoice ='$no_invoice' AND kategori_detail ='SEWING'")->result();
// $airfreight         = $this->db->query("SELECT * FROM tr_invoice_detail WHERE no_invoice ='$no_invoice' AND kategori_detail ='AIRFREIGHT'")->result();
// $akomodasi          = $this->db->query("SELECT * FROM tr_invoice_detail WHERE no_invoice ='$no_invoice' AND kategori_detail ='AKOMODASI'")->result();
// $acc                = $this->db->query("SELECT * FROM tr_invoice_detail WHERE no_invoice ='$no_invoice' AND kategori_detail ='ACCESSORIES'")->result();
?>

<?php
$inv          =  $this->db->query("SELECT * FROM tr_invoice_header WHERE no_invoice = '$data_header->no_invoice' ")->row();
$alamat_cust =  $this->db->query("SELECT * FROM customer WHERE id_customer = '$data_header->id_customer'")->row();
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<form id="form-header-mutasi" method="post">
  <div class="nav-tabs-salesorder">
    <div class="tab-content">
      <div class="tab-pane active" id="salesorder">

        <div class="box box-primary">
          <?php //print_r($kode_customer)
          ?>
          <div class="box-body">
            <div class="col-sm-6 form-horizontal">
              <div class="row">
                <div class="form-group">
                  <label class="col-sm-4 control-label">Kode Bayar </font></label>
                  <div class="col-sm-6">

                    <input type="text" name="no_invoice" id="no_invoice" value="<?php echo $data_header->kd_pembayaran ?>" class="form-control input-sm" readonly>

                  </div>

                </div>
              </div>
              <div class="row">
                <div class="form-group ">
                  <?php
                  $tglinv = date('Y-m-d');
                  ?>
                  <label for="tgl_bayar" class="col-sm-4 control-label">Tgl Bayar :</label>
                  <div class="col-sm-6">
                    <input type="text" name="tgl_bayar" id="tgl_bayar" class="form-control input-sm" value="<?php echo tgl_indo($data_header->tgl_pembayaran) ?>" readonly>

                  </div>
                </div>

              </div>
              <div class="row">
                <div class="form-group">
                  <label class="col-sm-4 control-label">Nama Customer </font></label>
                  <div class="col-sm-6">

                    <input type="hidden" name="id_customer" id="id_customer" class="form-control input-sm" value="<?php echo $data_header->id_customer ?>" readonly>

                    <input type="text" name="nm_customer" id="nm_customer" class="form-control input-sm" value="<?php echo $data_header->nm_customer ?>" readonly>

                  </div>

                </div>
              </div>
              <div class="row">
                <div class="form-group">
                  <label for="alamat" class="col-sm-4 control-label">Alamat Customer </font></label>
                  <div class="col-sm-6">

                    <textarea name="alamatcustomer" class="form-control input-sm" id="alamat" height=100 readonly><?php echo $alamat_cust->alamat ?></textarea>

                  </div>
                </div>

              </div>
              <div class="row">
                <div class="form-group">
                  <label for="ket_bayar" class="col-sm-4 control-label">Kurs </font></label>
                  <div class="col-sm-6">

                    <textarea name="ket_bayar" class="form-control input-sm" id="ket_bayar" height=100 readonly><?= ($data_header->kurs_bayar > 0) ? 'USD' : 'IDR' ?></textarea>

                  </div>
                </div>
              </div>
              <div class="row">
                <div class="form-group">
                  <label for="ket_bayar" class="col-sm-4 control-label">Keterangan Pembayaran </font></label>
                  <div class="col-sm-6">

                    <textarea name="ket_bayar" class="form-control input-sm" id="ket_bayar" height=100 readonly><?php echo $data_header->keterangan ?></textarea>

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
                                    $jenis = $inv->jenis_invoice;
                                    ?>
									<option value="TR-01" <?php echo ($jenis == 'TR-01') ? "selected" : "" ?>>Uang Muka</option>
									<option value="TR-02"  <?php echo ($jenis == 'TR-02') ? "selected" : "" ?>>Progress</option>
									<option value="TR-03"   <?php echo ($jenis == 'TR-03') ? "selected" : "" ?>>Retensi</option>
									
									</select>
                                
                              </div>

                          </div>
                        </div>
						
						<div class="row">
                          <div class="form-group">
                              <label for="jenis_invoice" class="col-sm-4 control-label">Pilih Bank </font></label>
                              <div class="col-sm-6">
                                 <select class="form-control input-sm" name="bank" id="bank">
									<option value="">Pilih Bank</option>
									<?php
                  foreach ($datbank as $kb => $vb) {
                    $selected = '';
                    if (isset($data)) {
                      if ($kb . '|' . $vb == $data->kd_bank . '|' . $data->nm_bank) $selected = ' selected';
                    }
                  ?>
									<option value="<?php echo $kb . '|' . $vb ?>" <?= $selected; ?> ><?php echo $vb ?></option>
									<?php } ?>
								  </select>
                                
                              </div>

                          </div>
                        </div>
						
						<div class="row">
                          <div class="form-group">
                              <label for="jenis_invoice" class="col-sm-4 control-label">Jenis PPH</font></label>
                              <div class="col-sm-6">
                                  <?php
                                  $pphpenjualan[0]  = 'Select An Option';
                                  echo form_dropdown('jenis_pph', $pphpenjualan, (isset($data) ? $data->jenis_pph : '0'), array('id' => 'jenis_pph', 'class' => 'form-control'));
                                  ?>
                                
                              </div>

                          </div>
                        </div>
						<div class="form-group ">
                            <label for="tgldo" class="col-sm-4 control-label"> </label>
                            <div class="col-sm-6" style="padding-top: 8px;">
                             

                              <input type="hidden" name="kurs" class="form-control input-sm" id="kurs" value="1" > 
                              

                            </div>
                        </div>-->

            </div>
            <div class="col-sm-6 form-horizontal">
              <div class="form-group ">
                <label for="idsalesman" class="col-sm-4 control-label">Total Invoice Bayar</label>
                <div class="col-sm-6" style="padding-top: 8px;">

                  <input type="text" name="total_invoice" class="form-control input-sm" id="total_invoice" value="<?php echo number_format($data_detail->total_bayar, 2) ?>" readonly>


                </div>
              </div>
              <!--<div class="form-group ">
                            <label for="idsalesman" class="col-sm-4 control-label">Total Piutang </label>
                            <div class="col-sm-6" style="padding-top: 8px;">
                              <?php
                              $totpiutang = $inv->total_invoice - $inv->total_bayar;
                              ?>
                              <input type="text" name="total_piutang" class="form-control input-sm" id="total_piutang" value="<?php echo  number_format($data_header->jumlah_pembayaran_idr, 2) ?>" readonly>
                             

                            </div>
                        </div>-->
              <div class="form-group ">
                <label for="idsalesman" class="col-sm-4 control-label">Jumlah Penerimaan bank</label>
                <div class="col-sm-6" style="padding-top: 8px;">
                  <input type="text" name="total_bank" class="form-control input-sm" id="total_bank" value="<?php echo  number_format($data_header->jumlah_bank, 2) ?>" readonly>

                </div>
              </div>
              <div class="form-group ">
                <label for="idsalesman" class="col-sm-4 control-label">Biaya Administrasi </label>
                <div class="col-sm-6" style="padding-top: 8px;">
                  <input type="text" name="biaya_adm" class="form-control input-sm" id="biaya_adm" value="<?php echo  number_format($data_header->biaya_admin, 2) ?>" readonly>

                </div>
              </div>
              <div class="form-group ">
                <label for="idsalesman" class="col-sm-4 control-label">PPH </label>
                <div class="col-sm-6" style="padding-top: 8px;">
                  <input type="text" name="biaya_pph" class="form-control input-sm" id="biaya_pph" value="<?php echo  number_format($data_header->biaya_pph_idr, 2) ?>" readonly>

                </div>
              </div>
              <div class="form-group ">
                <label for="idsalesman" class="col-sm-4 control-label">Total Penerimaan </label>
                <div class="col-sm-6" style="padding-top: 8px;">
                  <input type="text" name="total_terima" class="form-control input-sm" id="total_terima" value="<?php echo  number_format($data_header->jumlah_pembayaran, 2) ?>" readonly>

                </div>
              </div>
              <!--
						<div class="form-group">
                              <label for="template" class="col-sm-4 control-label">Pilih Template Jurnal</font></label>
                              <div class="col-sm-6">
                                  <?php
                                  $template[0]  = 'Select An Option';
                                  echo form_dropdown('template', $template, (isset($data) ? $data->template : '0'), array('id' => 'jenis_pph', 'class' => 'form-control'));
                                  ?>
                                
                              </div>

                          </div> -->


            </div>


            <div class="box-body">
              <form id="form-detail-mutasi" method="post">
                <table class="table table-bordered" width="100%" id="tabel-detail-mutasi">
                  <thead>
                    <tr class="bg-blue">
                      <th class="text-center">No Invoice</th>
                      <th class="text-center">Nama Customer</th>
                      <th class="text-center">Total Invoice</th>
                      <th class="text-center">Kurs</th>
                      <th class="text-center">Total Bayar</th>

                    </tr>
                  </thead>


                  <?php

                  $detail        = $this->db->query("SELECT * FROM tr_invoice_payment_detail WHERE kd_pembayaran ='$kodebayar'")->result();




                  foreach ($detail as $val => $det) {

                    $kodeinv = $det->no_invoice;

                    $inv_det  = $this->db->query("SELECT * FROM tr_invoice_sales WHERE id_invoice ='$kodeinv'")->row();



                  ?>

                    <tbody>
                      <tr>
                        <td class="text-center"><?php echo $det->no_invoice ?></td>
                        <td class="text-center"><?php echo $data_header->nm_customer ?></td>
                        <td class="text-center"><?php echo  number_format($inv_det->nilai_invoice, 2) ?></td>
                        <td class="text-center"><?php echo  number_format($data_header->kurs_bayar, 2) ?></td>
                        <td class="text-center"><?php echo  number_format($det->total_bayar_idr, 2) ?></td>

                      </tr>
                    </tbody>

                  <?php

                  }

                  ?>

                </table>
              </form>
            </div>

          </div>

        </div>
      </div>
    </div>
  </div>
  </div>
</form>