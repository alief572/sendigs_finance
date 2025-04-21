<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<input type="hidden" name="tipe_req" value="dp">
<input type="hidden" name="id_top" value="<?= $id_top ?>">
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Nomor PO</label>
            <input type="text" name="nomor_po" id="" class="form-control form-control-sm nomor_po" value="<?= $data_po['no_surat'] ?>" readonly>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Nama Supplier</label>
            <input type="text" name="nm_supplier" id="" class="form-control form-control-sm" value="<?= $get_supplier['nama'] ?>" readonly>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Receive Invoice Date</label>
            <input type="date" name="invoice_date" id="" class="form-control form-control-sm" required>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Persentase DP (%)</label>
            <input type="number" name="persen_dp" id="" class="form-control form-control-sm persen_dp" step="0.01" value="<?= number_format($get_top->progress, 2) ?>" readonly>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Invoice Date</label>
            <input type="date" name="invoice_date_real" id="" class="form-control form-control-sm invoice_date_real">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">DPP</label>
            <input type="text" name="total_pembelian" id="" class="form-control form-control-sm text-right total_pembelian" value="<?= number_format((($get_total_po['ttl_po'] - $nilai_disc) * $get_top->progress / 100), 2) ?>" readonly>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Nomor Invoice</label>
            <input type="text" name="nomor_invoice" id="" class="form-control form-control-sm nomor_invoice" required>
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
            <label for="">Value DP</label>
            <input type="text" name="value_dp" id="" class="form-control form-control-sm text-right value_dp" value="<?= number_format($get_top->nilai) ?>" readonly>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Currency</label>
            <input type="text" name="currency" id="" class="form-control form-control-sm" value="<?= $data_po['matauang'] ?>" readonly>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Upload Invoice</label>
            <input type="file" name="upload_invoice" id="" class="form-control form-control-sm upload_invoice">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Kurs</label>
            <input type="text" name="kurs" id="" class="form-control form-control-sm text-right auto_num">
        </div>
    </div>
    <div class="col-md-6">
        <b>Informasi Bank :</b>
        <div class="form-group">
            <label for="">Bank</label>
            <input type="text" name="bank" id="" class="form-control form-control-sm" placeholder="- Bank -">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Nomor Faktur Pajak</label>
            <input type="text" name="nomor_faktur_pajak" id="" class="form-control form-control-sm nomor_faktur_pajak" >
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">No. Bank</label>
            <input type="text" name="no_bank" id="" class="form-control form-control-sm" placeholder="- No. Bank -">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Tanggal Faktur Pajak</label>
            <input type="date" name="tanggal_faktur_pajak" id="" class="form-control form-control-sm tanggal_faktur_pajak">
        </div>
    </div>
    <div class="col-md-6"></div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="">Nama</label>
            <input type="text" name="nm_acc_bank" id="" class="form-control form-control-sm" placeholder="- Nama Acc Bank -">
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>

<script>
    $(document).ready(function() {
        $('.auto_num').autoNumeric('init');

        $('.select2_modal').select2({
            width: '100%',
            dropdownParent: $('#dialog-popup')
        });
    });

    $(document).on('change', '.persen_dp', function() {
        var total_pembelian = $('.total_pembelian').val();
        if (total_pembelian == '' || total_pembelian == null) {
            total_pembelian = 0;
        } else {
            total_pembelian = total_pembelian.split(',').join('');
            total_pembelian = parseFloat(total_pembelian);
        }

        var persen_dp = parseFloat($(this).val());

        var value_dp = (total_pembelian * persen_dp / 100);

        $('.value_dp').val(value_dp.toLocaleString());
    });

    $(document).on('change', '.value_dp', function() {
        var total_pembelian = $('.total_pembelian').val();
        if (total_pembelian == '' || total_pembelian == null) {
            total_pembelian = 0;
        } else {
            total_pembelian = total_pembelian.split(',').join('');
            total_pembelian = parseFloat(total_pembelian);
        }

        var value_dp = $(this).val();
        if (value_dp == '' || value_dp == null) {
            value_dp = 0;
        } else {
            value_dp = value_dp.split(',').join('');
            value_dp = parseFloat(value_dp);
        }

        var persen_dp = parseFloat((value_dp / total_pembelian) * 100);
        $('.value_dp').val(value_dp.toLocaleString());
        $('.persen_dp').val(persen_dp.toFixed(2));
    });
</script>