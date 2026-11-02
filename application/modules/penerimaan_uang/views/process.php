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
            <th class="text-center">Nomor Invoice</th>
            <th class="text-center">TAX Code</th>
            <th class="text-center">Penjualan</th>
            <th class="text-center">DPP</th>
            <th class="text-center">PPN</th>
            <th class="text-center">PPH 23<?= $pph23_dipotong === 'Y' ? '<br><small class="text-muted">default 2% DPP, bisa diedit</small>' : '' ?></th>
            <th class="text-center">Total Tagihan<br><small class="text-muted">(Penjualan + PPN)</small></th>
            <th class="text-center">Total Tagihan<br><small class="text-muted">- PPH</small></th>
            <th class="text-center">Piutang Dagang<br><small class="text-muted">(Penjualan + PPN)</small></th>
            <th class="text-center">Alokasi Penerimaan</th>
            <th class="text-center">Biaya Admin</th>
            <th class="text-center">Lebih Bayar</th>
            <th class="text-center">Sisa Piutang</th>
        </tr>
    </thead>
    <tbody>
        <?= $hasil ?>
    </tbody>
    <tbody>
        <tr>
            <td colspan="5" class="text-right text-muted">Total</td>
            <td>
                <input type="text" name="total_pph_row" id="total_pph_row" class="form-control form-control-sm autonum text-right" readonly>
                <input type="hidden" name="total_piutang" id="total_piutang" value="<?= $total_piutang ?>">
            </td>
            <td colspan="2"></td>
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
                <input type="text" name="total_lebih" id="total_lebih" class="form-control form-control-sm autonum text-right" readonly>
            </td>
            <td>
                <input type="text" name="total_sisa_piutang" id="total_sisa_piutang" class="form-control form-control-sm autonum text-right" readonly>
            </td>
        </tr>
        <tr>
            <td colspan="8" class="text-right text-muted">Total Piutang (Penjualan + PPN)</td>
            <td>
                <input type="text" id="footer_piutang" class="form-control form-control-sm autonum text-right" value="<?= $total_piutang_dagang ?>" readonly>
                <input type="text" name="grand_total" id="grand_total" class="autonum" style="display:none" readonly>
            </td>
            <td colspan="4"></td>
        </tr>
        <tr>
            <td colspan="8" class="text-right text-muted">Uang Masuk</td>
            <td>
                <input type="text" name="uang_masuk" class="form-control form-control-sm autonum text-right" id="uang_masuk" value="<?= $uang_masuk ?>" readonly>
            </td>
            <td colspan="4"></td>
        </tr>
        <tr>
            <td colspan="8" class="text-right text-muted">Lebih Bayar</td>
            <td>
                <input type="text" id="footer_lebih" class="form-control form-control-sm autonum text-right" readonly>
            </td>
            <td colspan="4" class="text-muted small" id="lebih_note" style="display:none">Masuk ke saldo deposit customer</td>
        </tr>
        <tr>
            <td colspan="8" class="text-right text-muted">Kontrol</td>
            <td>
                <input type="text" name="kontrol" class="form-control form-control-sm autonum text-right" id="kontrol" readonly>
            </td>
            <td colspan="4" class="text-muted small">((Piutang &minus; PPH 23 &minus; Biaya Admin &minus; Sisa Piutang) &minus; Uang Masuk) + Lebih Bayar</td>
        </tr>
    </tbody>
</table>

<div id="kontrol_alert" class="alert alert-danger" role="alert" style="display:none; font-size: 13px;"><b id="kontrol_title"></b><ul id="kontrol_hint" style="margin: 6px 0 0 18px; padding: 0;"></ul></div>

<div id="jurnal_box">
<div class="section-t" style="font-weight:700; margin: 14px 0 8px;">Jurnal Penerimaan Piutang</div>
<table class="table table-striped">
    <thead>
        <tr>
            <th class="text-center">Tanggal Jurnal</th>
            <th class="text-center">COA</th>
            <th class="text-center">Nama Company</th>
            <th class="text-center">Nama Account</th>
            <th class="text-center">Deskripsi</th>
            <th class="text-center">Debit</th>
            <th class="text-center">Credit</th>
        </tr>
    </thead>
    <tbody>
        <?= $hasil_jurnal ?>
    </tbody>
    <tbody>
        <tr>
            <th colspan="5" class="text-center">Balancing</th>
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
</div>

<script>
    function get_num(nilai = null) {
        if (nilai !== '' && nilai !== null) {
            // Format bisa '140,610,000.00' (autoNumeric di add.php) atau '2.574.990,00' (Indonesia):
            // pemisah terakhir yang diikuti 1-2 digit adalah desimal, pemisah lain adalah ribuan.
            nilai = String(nilai).trim();
            var dec = /[.,](\d{1,2})$/.exec(nilai);
            nilai = dec ? parseFloat(nilai.slice(0, dec.index).replace(/[.,]/g, '') + '.' + dec[1]) : parseFloat(nilai.replace(/[.,]/g, ''));
            if (isNaN(nilai)) nilai = 0;
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

    // PPh yang dipotong pada baris i (mockup): Y = kolom PPH 23 (default sisa anggaran, 0..anggaran, bisa diedit); N = 0.
    function pphValue(i, budget, errors) {
        if ($('input[name="pph23_dipotong"]').val() == 'N') { return 0; }
        var el = $('input[name="pph_actual_' + i + '"]');
        if (!el.length) { return budget; }
        var actual = get_num(el.val());
        var link = $('.reset-pph-link-' + i);
        if (link.length) { link.toggle(Math.abs(actual - budget) > 0.004); }
        if (actual < 0 || actual > budget) { errors.push('PPh 23 baris ' + i + ' harus antara 0 dan ' + number_format(budget) + '.'); return 0; }
        return actual;
    }

    function pphEdited(i) { hitungAll(); }

    function resetPph(i) {
        var el = $('input[name="pph_actual_' + i + '"]');
        el.autoNumeric('set', el.attr('data-default'));
        hitungAll();
    }

    // Seperti mockup: Alokasi (tampilan) = Piutang - Sisa selama Alokasi belum diedit manual; Sisa tidak dihitung otomatis dari Alokasi/Admin.
    var penManual = {};
    function penEdited(i) { penManual[i] = true; hitungAll(); }
    function sisaEdited(i) {
        if (!penManual[i]) {
            var piutang = get_num($('input[name="piutang_dagang_' + i + '"]').val());
            var sisa = get_num($('input[name="sisa_piutang_' + i + '"]').val());
            $('input[name="penerimaan_' + i + '"]').autoNumeric('set', Math.max(0, Math.round((piutang - sisa) * 100) / 100));
        }
        hitungAll();
    }

    function hitungAll() {
        var no = '<?= $no_inv ?>';

        var uang_masuk = get_num($('#uang_masuk').val());
        var pph23_dipotong = $('input[name="pph23_dipotong"]').val();

        var ttl_debit_jurnal = 0;
        var ttl_kredit_jurnal = 0;

        // Debit bank akan ditambahkan di akhir setelah ttl_penerimaan dihitung
        ttl_kredit_jurnal += get_num2($('input[name="kredit_bank_debit"]').val());

        var lebihEligible = <?= json_encode(!empty($lebih_eligible)) ?>;
        var ttl_lebih = 0;
        var ttl_piutang_dagang = 0;
        var ttl_penerimaan = 0;
        var ttl_biaya_admin = 0;
        var ttl_sisa_piutang = 0;
        var ttl_pph23 = 0;
        var ttl_kas = 0;
        var inputErrors = [];
        for (i = 1; i <= no; i++) {
            var piutang_dagang = get_num($('input[name="piutang_dagang_' + i + '"]').val());
            var penerimaan = get_num($('input[name="penerimaan_' + i + '"]').val());
            var biaya_admin = get_num($('input[name="biaya_admin_' + i + '"]').val());
            var sisa_piutang = get_num($('input[name="sisa_piutang_' + i + '"]').val());
            var lebih_inv = lebihEligible ? Math.max(0, get_num($('input[name="lebih_bayar_' + i + '"]').val())) : 0;
            ttl_lebih += lebih_inv;
            // Anggaran PPh dari riwayat (Y); N = 0.
            var budget_pph = get_num2($('input[name="remaining_pph_' + i + '"]').val());
            var pph23_inv = pphValue(i, budget_pph, inputErrors);

            // Total Tagihan - PPH dihitung ulang langsung saat PPh diubah (mockup 832).
            var tagihanCell = $('#tagihan_net_' + i);
            if (tagihanCell.length) { tagihanCell.text(number_format(parseFloat(tagihanCell.attr('data-tagihan')) - pph23_inv)); }
            if (penerimaan < 0 || biaya_admin < 0 || sisa_piutang < 0) { inputErrors.push('Nilai minus tidak diperbolehkan di baris ' + i + '.'); }
            if (sisa_piutang > piutang_dagang) { inputErrors.push('Sisa piutang melebihi piutang dagang pada baris ' + i + '.'); }
            if (Math.abs(penerimaan + sisa_piutang - piutang_dagang) > 0.004) { inputErrors.push('Alokasi penerimaan + sisa piutang harus sama dengan piutang dagang pada baris ' + i + '.'); }
            // Kas bank invoice ini (mockup): Piutang - PPh 23 - Biaya Admin - Sisa.
            var kas = Math.round((piutang_dagang - pph23_inv - biaya_admin - sisa_piutang) * 100) / 100;
            if (kas < 0) { inputErrors.push('Penerimaan bank tidak boleh minus pada baris ' + i + '.'); }
            ttl_kas += kas;

            // Jurnal (mockup): Debit PPh 23 Disetor, Debit Admin, Kredit Piutang bruto, Debit Piutang = sisa.
            $.each(['1106-01-02', '1106-01-05'], function(index, coa) {
                var cellK = $('.td_kredit_' + coa + '_' + i);
                if (cellK.length) { cellK.html(number_format(0) + '<input type="hidden" name="kredit_' + coa + '_' + i + '" value="0">'); }
                var cellD = $('.td_debit_' + coa + '_' + i);
                if (cellD.length) { cellD.html(number_format(pph23_inv) + '<input type="hidden" name="debit_' + coa + '_' + i + '" value="' + pph23_inv + '">'); }
            });
            $('.td_kredit_1102-01-01_' + i).html(number_format(piutang_dagang) + '<input type="hidden" name="kredit_1102-01-01_' + i + '" value="' + piutang_dagang + '">');
            $('.td_debit_1102-01-01_' + i).html(number_format(sisa_piutang) + '<input type="hidden" name="debit_1102-01-01_' + i + '" value="' + sisa_piutang + '">');
            $('.td_debit_7201-01-04_' + i).html(number_format(biaya_admin) + '<input type="hidden" name="debit_7201-01-04_' + i + '" value="' + biaya_admin + '">');

            $.each(['1102-01-01', '7201-01-04', '1106-01-02', '1106-01-05'], function(index, value) {
                ttl_debit_jurnal += get_num2($('input[name="debit_' + value + '_' + i + '"]').val());
                ttl_kredit_jurnal += get_num2($('input[name="kredit_' + value + '_' + i + '"]').val());
            });

            ttl_piutang_dagang += piutang_dagang;
            ttl_penerimaan += penerimaan;
            ttl_biaya_admin += biaya_admin;
            ttl_sisa_piutang += sisa_piutang;
            ttl_pph23 += pph23_inv;
        }

        var grand_total = Math.round(ttl_penerimaan * 100) / 100;
        // Lebih Bayar ikut sisi bank: uang masuk = penerimaan + lebih bayar.
        if (ttl_biaya_admin > 10000) { inputErrors.push('Total biaya admin melebihi ' + number_format(10000) + '.'); }
        // Kontrol (mockup): ((Piutang - PPh 23 - Biaya Admin - Sisa) - Uang Masuk) + Lebih Bayar.
        var kontrol = Math.round((ttl_kas + ttl_lebih - uang_masuk) * 100) / 100;
        var kontrolTitle, kontrolMsg;
        if (inputErrors.length) {
            kontrolTitle = kontrol !== 0 ? 'Catatan: kontrol belum 0, belum bisa disimpan.' : 'Catatan: data belum valid.';
            kontrolMsg = inputErrors.join(' ');
        } else if (kontrol > 0) {
            kontrolTitle = 'Catatan: kontrol belum 0, belum bisa disimpan.';
            kontrolMsg = 'Masih ada selisih kurang ' + number_format(kontrol) + '. Alokasikan ke PPh 23, Biaya Admin, atau Sisa Piutang pada invoice yang sesuai.';
        } else if (kontrol < 0) {
            kontrolTitle = 'Catatan: kontrol belum 0, belum bisa disimpan.';
            kontrolMsg = lebihEligible
                ? 'Ada kelebihan ' + number_format(-kontrol) + '. Alokasikan ke Lebih Bayar (masuk ke saldo deposit customer).'
                : 'Ada kelebihan ' + number_format(-kontrol) + '. Lebih Bayar tidak tersedia untuk invoice ini (company belum terpetakan atau campuran): sesuaikan PPh 23, Biaya Admin, atau Sisa Piutang.';
        } else {
            kontrolTitle = 'Kontrol 0.';
            kontrolMsg = 'Periksa jurnal di bawah, lalu klik Simpan Penerimaan & Bentuk Jurnal.';
        }
        $('#kontrol_title').text(kontrolTitle);
        // Catatan sebagai daftar berpoin (mockup errlist).
        var hintItems = inputErrors.length ? inputErrors : [kontrolMsg];
        $('#kontrol_hint').empty();
        $.each(hintItems, function(idx, text) { $('#kontrol_hint').append($('<li>').text(text)); });
        $('#kontrol').css('background', kontrol === 0 ? '#dff0d8' : '#f2dede');
        $('#lebih_note').toggle(ttl_lebih > 0);
        $('#total_pph_row').autoNumeric('init', {vMin: '-9999999999999.99', vMax: '9999999999999.99', aSep: '.', aDec: ','}).autoNumeric('set', ttl_pph23);
        $('#kontrol_alert').removeClass('alert-danger alert-info').addClass(kontrol === 0 && !inputErrors.length ? 'alert-info' : 'alert-danger').show();
        $('.tr_jurnal_lebih').toggle(ttl_lebih > 0);
        $('.td_kredit_lebih').html(number_format(ttl_lebih) + '<input type="hidden" name="kredit_lebih" value="' + ttl_lebih + '">');
        ttl_kredit_jurnal += ttl_lebih;

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
        $('input[name="total_lebih"]').autoNumeric('init', {
            vMin: '-9999999999999.99',
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
        $('input[name="total_lebih"]').autoNumeric('set', ttl_lebih);
        $('input[name="total_sisa_piutang"]').autoNumeric('set', ttl_sisa_piutang);
        $('input[name="grand_total"]').autoNumeric('set', grand_total);
        $('input[name="kontrol"]').autoNumeric('set', kontrol);
        $('#footer_piutang').autoNumeric('set', ttl_piutang_dagang);
        $('#footer_lebih').autoNumeric('set', ttl_lebih);

        // Update nilai Bank Debit dengan total penerimaan yang diketik user
        var bank_masuk = ttl_kas + ttl_lebih;
        var resp_debit_bank_debit = number_format(bank_masuk);
        resp_debit_bank_debit += '<input type="hidden" name="debit_bank_debit" value="' + bank_masuk + '">';
        $('.td_debit_bank_debit').html(resp_debit_bank_debit);
        ttl_debit_jurnal += bank_masuk;

        var resp_ttl_debit_jurnal = number_format(ttl_debit_jurnal);
        resp_ttl_debit_jurnal += '<input type="hidden" name="total_debit_jurnal" value="' + ttl_debit_jurnal + '">';

        var resp_ttl_kredit_jurnal = number_format(ttl_kredit_jurnal);
        resp_ttl_kredit_jurnal += '<input type="hidden" name="total_kredit_jurnal" value="' + ttl_kredit_jurnal + '">';

        $('.td_total_debit_jurnal').html(resp_ttl_debit_jurnal);
        $('.td_total_kredit_jurnal').html(resp_ttl_kredit_jurnal);

        // Seperti mockup: jurnal hanya tampil bila Kontrol 0 dan isian valid.
        $('#jurnal_box').toggle(kontrol === 0 && inputErrors.length === 0);

        // Tombol simpan di footer modal (add.php) aktif hanya bila Kontrol 0, ada uang bank yang dialokasikan, dan jurnal seimbang.
        $('#dialog-popup button[type="submit"]').prop('disabled', !(kontrol === 0 && inputErrors.length === 0 && bank_masuk > 0 && Math.abs(ttl_debit_jurnal - ttl_kredit_jurnal) < 0.01));

        // Seperti mockup: baris jurnal bernilai nol disembunyikan (inputnya tetap ada di form).
        $('.tr_jurnal_row').each(function () {
            var row = $(this);
            row.toggle(get_num2(row.find('input[name^="debit_"]').val() || '') + get_num2(row.find('input[name^="kredit_"]').val() || '') > 0);
        });

    }
</script>
