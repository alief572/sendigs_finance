<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Nomor PO</label>
            <input type="text" name="nomor_po" id="" class="form-control form-control-sm nomor_po" value="<?= $data_invoice['id'] ?>" readonly>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Nama Supplier</label>
            <input type="text" name="nm_supplier" id="" class="form-control form-control-sm" value="<?= $data_invoice['nm_supplier'] ?>" readonly>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Receive Invoice Date</label>
            <input type="date" name="invoice_date" id="" class="form-control form-control-sm" value="<?= $data_invoice['invoice_date'] ?>" readonly>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Persentase Retensi (%)</label>
            <input type="number" name="persen_dp" id="" class="form-control form-control-sm persen_dp" step="0.01" value="<?= number_format($data_invoice['persen_dp'], 2) ?>" readonly>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Invoice Date</label>
            <input type="date" name="invoice_date_real" id="" class="form-control form-control-sm invoice_date_real" value="<?= $data_invoice['invoice_date_real'] ?>" reaodnly>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">DPP</label>
            <input type="text" name="total_pembelian" id="" class="form-control form-control-sm text-right total_pembelian" value="<?= number_format($data_invoice['total_pembelian'], 2) ?>" readonly>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Nomor Invoice</label>
            <input type="text" name="nomor_invoice" id="" class="form-control form-control-sm nomor_invoice" value="<?= $data_invoice['invoice_no'] ?>" readonly>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Nilai PPn</label>
            <input type="text" name="nilai_ppn" id="" class="form-control form-control-sm text-right auto_num nilai_ppn" value="<?= number_format($nilai_ppn, 2) ?>" readonly>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Nilai Disc</label>
            <input type="text" name="nilai_disc" id="" class="form-control form-control-sm text-right auto_num nilai_disc" value="<?= number_format($nilai_disc, 2) ?>" readonly>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Value Retensi</label>
            <input type="text" name="value_dp" id="" class="form-control form-control-sm text-right value_dp" value="<?= number_format($get_top->nilai) ?>" readonly>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Currency</label>
            <input type="text" name="currency" id="" class="form-control form-control-sm" value="<?= $data_invoice['curr'] ?>" readonly>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Upload Invoice</label>
            <input type="file" name="upload_invoice" id="" class="form-control form-control-sm upload_invoice">
            <?php 
                if(file_exists($data_invoice['link_doc']) && $data_invoice['link_doc'] !== '' && $data_invoice['link_doc'] !== null){
                    echo '<a href="'.base_url($data_invoice['link_doc']).'" class="btn btn-sm btn-primary" target="_blank"><i class="fa fa-download"></i> Download</a>';
                }
            ?>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Kurs</label>
            <input type="text" name="kurs" id="" class="form-control form-control-sm text-right auto_num" value="<?= number_format($data_invoice['kurs'], 2) ?>" readonly>
        </div>
    </div>
    <div class="col-md-6">
        <b>Informasi Bank :</b>
        <div class="form-group">
            <label for="">Bank</label>
            <input type="text" name="bank" id="" class="form-control form-control-sm" placeholder="- Bank -" value="<?= $data_invoice['bank'] ?>" readonly>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Nomor Faktur Pajak</label>
            <input type="text" name="nomor_faktur_pajak" id="" class="form-control form-control-sm nomor_faktur_pajak" value="<?= $data_invoice['no_faktur_pajak'] ?>" readonly>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">No. Bank</label>
            <input type="text" name="no_bank" id="" class="form-control form-control-sm" placeholder="- No. Bank -" value="<?= $data_invoice['no_bank'] ?>" readonly>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Tanggal Faktur Pajak</label>
            <input type="date" name="tanggal_faktur_pajak" id="" class="form-control form-control-sm tanggal_faktur_pajak" value="<?= $data_invoice['tanggal_faktur_pajak'] ?>" readonly>
        </div>
    </div>
    <div class="col-md-6"></div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Nama</label>
            <input type="text" name="nm_acc_bank" id="" class="form-control form-control-sm" placeholder="- Nama Acc Bank -" value="<?= $data_invoice['nm_acc_bank'] ?>" readonly>
        </div>
    </div>
</div>
