<style>
    .form-inline {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    body {
        background-color: #f5f5f5;
        padding-top: 20px;
    }

    .main-container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 30px;
        margin-bottom: 30px;
    }

    .form-inline .form-group {
        margin-right: 15px;
        margin-bottom: 10px;
    }

    .form-inline .form-group label {
        margin-right: 8px;
        font-weight: 600;
        color: #555;
    }

    .form-inline .form-control {
        border-radius: 4px;
        border: 1px solid #ddd;
        padding: 8px 12px;
        font-size: 14px;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-inline .form-control:focus {
        border-color: #66afe9;
        outline: 0;
        box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075), 0 0 8px rgba(102, 175, 233, .6);
    }

    .btn {
        border-radius: 6px;
        font-weight: 500;
        margin-right: 8px;
        transition: all 0.2s ease-in-out;
    }

    .btn-primary {
        background-color: #337ab7;
        border-color: #2e6da4;
    }

    .btn-primary:hover {
        background-color: #286090;
        border-color: #204d74;
    }

    .btn-success {
        background-color: #5cb85c;
        border-color: #4cae4c;
    }

    .btn-success:hover {
        background-color: #449d44;
        border-color: #398439;
    }

    .form-control-sm {
        height: 34px;
        padding: 6px 12px;
        font-size: 14px;
    }

    .search-section {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .search-section h4 {
        color: #495057;
        margin-bottom: 15px;
        font-weight: 600;
    }

    .date-group {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .date-input-wrapper {
        display: flex;
        align-items: center;
        margin-right: 15px;
    }

    .date-input-wrapper label {
        margin-right: 8px;
        white-space: nowrap;
    }

    @media (max-width: 768px) {
        .form-inline .form-group {
            display: block;
            margin-bottom: 15px;
        }

        .date-group {
            flex-direction: column;
            align-items: stretch;
        }

        .date-input-wrapper {
            margin-bottom: 10px;
        }
    }
</style>

<table class="table table-striped">
    <thead>
        <tr>
            <th class="text-center">Tgl Inv</th>
            <th class="text-center">Nomor Inv</th>
            <th class="text-center">Total Invoice</th>
            <th class="text-center">DPP</th>
            <th class="text-center">PPN</th>
            <th class="text-center">PPH 23</th>
            <th class="text-center">Piutang</th>
            <th class="text-center">Penerimaan</th>
            <th class="text-center">Biaya Admin</th>
        </tr>
    </thead>
    <tbody>
        <?= $hasil ?>
    </tbody>
    <tbody>
        <tr>
            <td colspan="6" class="text-right">Total</td>
            <td>
                <input type="text" name="total_piutang" id="total_piutang" class="form-control form-control-sm autonum text-right" value="<?= $total_piutang ?>" readonly>
            </td>
            <td>
                <input type="text" name="total_penerimaan" id="total_penerimaan" class="form-control form-control-sm autonum text-right" readonly>
            </td>
            <td>
                <input type="text" name="total_biaya_admin" id="total_biaya_admin" class="form-control form-control-sm autonum text-right" readonly>
            </td>
        </tr>
        <tr>
            <td colspan="7" class="text-right">Grand Total</td>
            <td>
                <input type="text" name="grand_total" class="form-control form-control-sm autonum text-right" id="grand_total" readonly>
            </td>
            <td></td>
        </tr>
        <tr>
            <td colspan="7" class="text-right">Uang Masuk</td>
            <td>
                <input type="text" name="uang_masuk" class="form-control form-control-sm autonum text-right" id="uang_masuk" value="<?= $uang_masuk ?>" readonly>
            </td>
            <td></td>
        </tr>
        <tr>
            <td colspan="7" class="text-right">Kontrol</td>
            <td>
                <input type="text" name="kontrol" class="form-control form-control-sm autonum text-right" id="kontrol" readonly>
            </td>
            <td></td>
        </tr>
    </tbody>
</table>

<br><br>

<table class="table table-striped">
    <thead>
        <tr>
            <th class="text-center">Tanggal Jurnal</th>
            <th class="text-center">COA</th>
            <th class="text-center">Nama Company</th>
            <th class="text-center">Nama Account</th>
            <th class="text-center">Debit</th>
            <th class="text-center">Credit</th>
        </tr>
    </thead>
    <tbody>
        <?= $hasil_jurnal ?>
    </tbody>
</table>

<script>
    function get_num(nilai = null) {
        if (nilai !== '' && nilai !== null) {
            nilai = nilai.split(',').join('');
            nilai = parseFloat(nilai);
        } else {
            nilai = 0;
        }

        return nilai;
    }

    function hitungAll() {
        var no = '<?= $no_inv ?>';

        var uang_masuk = get_num($('#uang_masuk').val());

        var ttl_penerimaan = 0;
        var ttl_biaya_admin = 0;
        for (i = 1; i <= no; i++) {
            var penerimaan = get_num($('input[name="penerimaan_' + i + '"]').val());
            var biaya_admin = get_num($('input[name="biaya_admin_' + i + '"]').val());

            ttl_penerimaan += penerimaan;
            ttl_biaya_admin += biaya_admin;
        }

        var grand_total = (ttl_penerimaan - ttl_biaya_admin);
        var kontrol = (grand_total - uang_masuk);

        $('input[name="total_penerimaan"]').autoNumeric('set', ttl_penerimaan);
        $('input[name="total_biaya_admin"]').autoNumeric('set', ttl_biaya_admin);
        $('input[name="grand_total"]').autoNumeric('set', grand_total);
        $('input[name="kontrol"]').val(kontrol);


    }
</script>