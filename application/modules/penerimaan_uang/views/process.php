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

    td {
        font-size: 13px;
    }

    input {
        font-size: 13px !important;
    }
</style>

<input type="hidden" name="no_inv" value="<?= $no_inv ?>">
<input type="hidden" name="id_alokasi" value="<?= $id_alokasi ?>">
<input type="hidden" name="nm_customer" value="<?= $nm_customer ?>">
<input type="hidden" name="ppn_dipotong" value="<?= $ppn_dipotong ?>">
<input type="hidden" name="pph23_dipotong" value="<?= $pph23_dipotong ?>">
<input type="hidden" name="nominal_penerimaan_bank" value="<?= $nominal_penerimaan_bank ?>">

<table class="table table-striped">
    <thead>
        <tr>
            <th class="text-center">Tgl invoice</th>
            <th class="text-center">Nomor Invoice</th>
            <th class="text-center">DPP</th>
            <th class="text-center">DPP Lain</th>
            <th class="text-center">PPN</th>
            <th class="text-center">PPH 23</th>
            <th class="text-center">Tagihan + Ppn</th>
            <th class="text-center">Tagihan + Ppn - Pph</th>
            <th class="text-center">Piutang Dagang</th>
            <th class="text-center">Penerimaan</th>
            <th class="text-center">Biaya Admin</th>
            <th class="text-center">Sisa Piutang</th>
        </tr>
    </thead>
    <tbody>
        <?= $hasil ?>
    </tbody>
    <tbody>
        <tr>
            <td colspan="6" class="text-right">Total</td>
            <td colspan="2">
                <input type="text" name="total_piutang" id="total_piutang" class="form-control form-control-sm autonum text-right" value="<?= $total_piutang ?>" readonly>
            </td>
            <td>
                <input type="text" name="total_piutang_dagang" id="total_piutang_dagang" class="form-control form-control-sm autonum text-right" value="<?= $total_piutang_dagang ?>" readonly>
            </td>
            <td>
                <input type="text" name="total_penerimaan" id="total_penerimaan" class="form-control form-control-sm autonum text-right" readonly>
            </td>
            <td>
                <input type="text" name="total_biaya_admin" id="total_biaya_admin" class="form-control form-control-sm autonum text-right" readonly>
            </td>
            <td>
                <input type="text" name="total_sisa_piutang" id="total_sisa_piutang" class="form-control form-control-sm autonum text-right" readonly>
            </td>
        </tr>
        <tr>
            <td colspan="8" class="text-right">Total Penerimaan - Biaya Admin</td>
            <td>
                <input type="text" name="grand_total" class="form-control form-control-sm autonum text-right" id="grand_total" readonly>
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td colspan="8" class="text-right">Uang Masuk</td>
            <td>
                <input type="text" name="uang_masuk" class="form-control form-control-sm autonum text-right" id="uang_masuk" value="<?= $uang_masuk ?>" readonly>
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td colspan="8" class="text-right">Kontrol</td>
            <td>
                <input type="text" name="kontrol" class="form-control form-control-sm autonum text-right" id="kontrol" readonly>
            </td>
            <td></td>
            <td></td>
            <td></td>
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
    <tbody>
        <tr>
            <th colspan="4" class="text-center">Grand Total</th>
            <td class="text-center td_total_debit_jurnal">
                <?= number_format($total_debit) ?>
                <input type="hidden" name="total_debit" value="<?= $total_debit ?>">
            </td>
            <td class="text-center td_total_kredit_jurnal">
                <?= number_format($total_kredit) ?>
                <input type="hidden" name="total_kredit" value="<?= $total_kredit ?>">
            </td>
        </tr>
    </tbody>
</table>

<script>
    function get_num(nilai = null) {
        if (nilai !== '' && nilai !== null) {
            nilai = nilai.split('.').join('');
            nilai = nilai.split('.').join(',');
            nilai = parseFloat(nilai);
        } else {
            nilai = 0;
        }

        return nilai;
    }

    function get_num2(nilai = null) {
        if (nilai !== '' && nilai !== null) {
            nilai = nilai.split(',').join('');
            nilai = parseFloat(nilai);
        } else {
            nilai = 0;
        }

        return nilai;
    }

    function number_format(number, decimals, dec_point, thousands_sep) {
        // Strip all characters but numerical ones.
        number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
        var n = !isFinite(+number) ? 0 : +number,
            prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
            sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
            dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
            s = '',
            toFixedFix = function(n, prec) {
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

    function hitungAll() {
        var no = '<?= $no_inv ?>';

        var uang_masuk = get_num($('#uang_masuk').val());
        var pph23_dipotong = $('input[name="pph23_dipotong"]').val();

        var ttl_debit_jurnal = 0;
        var ttl_kredit_jurnal = 0;

        // Debit bank akan ditambahkan di akhir setelah ttl_penerimaan dihitung
        ttl_kredit_jurnal += get_num2($('input[name="kredit_bank_debit"]').val());

        var ttl_piutang_dagang = 0;
        var ttl_penerimaan = 0;
        var ttl_biaya_admin = 0;
        var ttl_sisa_piutang = 0;
        var ttl_pph23 = 0;
        for (i = 1; i <= no; i++) {
            var piutang_dagang = get_num($('input[name="piutang_dagang_' + i + '"]').val());
            var penerimaan = get_num($('input[name="penerimaan_' + i + '"]').val());
            var biaya_admin = get_num($('input[name="biaya_admin_' + i + '"]').val());
            var pph23_inv = get_num2($('input[name="kredit_1106-01-02_' + i + '"]').val()) + get_num2($('input[name="kredit_1106-01-05_' + i + '"]').val());

            var sisa_piutang;
            if (pph23_dipotong == 'Y') {
                // Potong PPh (client bayar net): Piutang Dagang - Penerimaan - Biaya Admin
                sisa_piutang = Math.round(piutang_dagang - penerimaan - biaya_admin);
            } else {
                // Tidak Potong PPh (client bayar full): Piutang Dagang - (Penerimaan - PPh 23) - Biaya Admin
                sisa_piutang = Math.round(piutang_dagang - (penerimaan - pph23_inv) - biaya_admin);
            }

            $('input[name="sisa_piutang_' + i + '"]').autoNumeric('set', sisa_piutang);

            var coa_jurnal = ['1102-01-01', '7201-01-04', '1106-01-02', '1106-01-05'];
            $.each(coa_jurnal, function(index, value) {
                index = index + 1;
                if (value == '1102-01-01') {
                    var kredit_piutang_dagang;
                    if (pph23_dipotong == 'Y') {
                        // Dipotong PPh: kredit piutang dagang = penerimaan + biaya admin (total yang mengurangi piutang)
                        kredit_piutang_dagang = penerimaan + biaya_admin;
                    } else {
                        // Tidak dipotong PPh: kredit piutang dagang = penerimaan + biaya admin - pph23
                        kredit_piutang_dagang = penerimaan + biaya_admin - pph23_inv;
                    }
                    var resp_piutang = number_format(kredit_piutang_dagang);
                    resp_piutang += '<input type="hidden" name="kredit_' + value + '_' + i + '" value="' + kredit_piutang_dagang + '">';

                    $('.td_kredit_' + value + '_' + i).html(resp_piutang);
                }
                if (value == '7201-01-04') {
                    var resp_admin = number_format(biaya_admin);
                    resp_admin += '<input type="hidden" name="debit_' + value + '_' + i + '" value="' + biaya_admin + '">';

                    $('.td_debit_' + value + '_' + i).html(resp_admin);
                }
                if (value == '1106-01-02') {
                    var pph23 = $('input[name="kredit_' + value + '_' + i + '"]').val();
                }

                ttl_debit_jurnal += get_num2($('input[name="debit_' + value + '_' + i + '"]').val());
                ttl_kredit_jurnal += get_num2($('input[name="kredit_' + value + '_' + i + '"]').val());
            });


            ttl_piutang_dagang += piutang_dagang;
            ttl_penerimaan += penerimaan;
            ttl_biaya_admin += biaya_admin;
            ttl_sisa_piutang += sisa_piutang;
            ttl_pph23 += get_num2($('input[name="kredit_1106-01-02_' + i + '"]').val()) + get_num2($('input[name="kredit_1106-01-05_' + i + '"]').val());

        }

        var grand_total = Math.round(ttl_penerimaan - ttl_biaya_admin);
        var kontrol = Math.round(grand_total - uang_masuk);

        $('input[name="total_penerimaan"]').autoNumeric('init', {
            vMin: '-9999999999999.99', // Ini kuncinya! Kasih range negatif yang luas
            vMax: '9999999999999.99',
            aSep: '.',
            aDec: ','
        });
        $('input[name="total_piutang_dagang"]').autoNumeric('init', {
            vMin: '-9999999999999.99', // Ini kuncinya! Kasih range negatif yang luas
            vMax: '9999999999999.99',
            aSep: '.',
            aDec: ','
        });
        $('input[name="total_biaya_admin"]').autoNumeric('init', {
            vMin: '-9999999999999.99', // Ini kuncinya! Kasih range negatif yang luas
            vMax: '9999999999999.99',
            aSep: '.',
            aDec: ','
        });

        $('input[name="total_sisa_piutang"]').autoNumeric('init', {
            vMin: '-9999999999999.99', // Ini kuncinya! Kasih range negatif yang luas
            vMax: '9999999999999.99',
            aSep: '.',
            aDec: ','
        });
        $('input[name="grand_total"]').autoNumeric('init', {
            vMin: '-9999999999999.99', // Ini kuncinya! Kasih range negatif yang luas
            vMax: '9999999999999.99',
            aSep: '.',
            aDec: ','
        });
        // $('input[name="kontrol"]').val(kontrol);

        $('input[name="total_penerimaan"]').autoNumeric('set', ttl_penerimaan);
        $('input[name="total_piutang_dagang"]').autoNumeric('set', ttl_piutang_dagang);
        $('input[name="total_biaya_admin"]').autoNumeric('set', ttl_biaya_admin);
        $('input[name="total_sisa_piutang"]').autoNumeric('set', ttl_sisa_piutang);
        $('input[name="grand_total"]').autoNumeric('set', grand_total);
        $('input[name="kontrol"]').autoNumeric('set', kontrol);

        // Update nilai Bank Debit dengan total penerimaan yang diketik user
        $('input[name="debit_bank_debit"]').val(ttl_penerimaan);
        $('.td_debit_bank_debit').html(number_format(ttl_penerimaan));
        ttl_debit_jurnal += ttl_penerimaan;

        var resp_ttl_debit_jurnal = number_format(ttl_debit_jurnal);
        resp_ttl_debit_jurnal += '<input type="hidden" name="total_debit_jurnal" value="' + ttl_debit_jurnal + '">';

        var resp_ttl_kredit_jurnal = number_format(ttl_kredit_jurnal);
        resp_ttl_kredit_jurnal += '<input type="hidden" name="total_kredit_jurnal" value="' + ttl_kredit_jurnal + '">';

        $('.td_total_debit_jurnal').html(resp_ttl_debit_jurnal);
        $('.td_total_kredit_jurnal').html(resp_ttl_kredit_jurnal);

    }
</script>