<input type="hidden" name="id" value="<?= $id ?>">
<table class="table table-bordered w-100">
    <thead>
        <tr>
            <th class="text-center">Tanggal</th>
            <th class="text-center">Tipe</th>
            <th class="text-center">No. COA</th>
            <th class="text-center">Nama COA</th>
            <th class="text-center">Keterangan</th>
            <th class="text-center">No. Reff</th>
            <th class="text-center">Debit</th>
            <th class="text-center">Kredit</th>
        </tr>
    </thead>
    <tbody>
        <?php

        $ttl_debit = 0;
        $ttl_kredit = 0;

        foreach ($jurnal_header as $item) {
        ?>
            <tr>
                <td class="text-center"><?= date('d F Y', strtotime($item->tgl_jurnal)) ?></td>
                <td class="text-center"><?= $item->jenis_transaksi ?></td>
                <td class="text-center"><?= $item->coa ?></td>
                <td class="text-left"><?= $item->nm_coa ?></td>
                <td class="text-left">
                    <textarea name="keterangan" class="form-control form-control-sm" readonly><?= $item->keterangan ?></textarea>
                </td>
                <td class="text-center"><?= $item->no_transaksi ?></td>
                <td>
                    <input type="text" name="debit" class="form-control form-control-sm text-right debit" value="<?= $item->debit ?>" onchange="hitungDebit();" readonly>
                </td>
                <td>
                    <input type="text" name="kredit" class="form-control form-control-sm text-right kredit" value="<?= $item->kredit ?>" onchange="hitungKredit();" readonly>
                </td>
            </tr>
        <?php

            $ttl_debit += $item->debit;
            $ttl_kredit += $item->kredit;
        }

        ?>

    </tbody>
    <tfoot>
        <tr>
            <th colspan="6" class="text-right">Grand Total</th>
            <th class="text-right total_debit">
                <?= number_format($ttl_debit) ?>
            </th>
            <th class="text-right total_kredit">
                <?= number_format($ttl_kredit) ?>
            </th>
        </tr>
    </tfoot>
</table>

<!-- <div class="col-md-6"> -->
<div class="form-group" style="width: 50%;">
    <label for="">Alasan Revisi</label>
    <textarea name="alasan_revisi" id="" class="form-control form-control-sm"></textarea>
</div>
<!-- </div> -->

<script>
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

    function hitungDebit() {
        var debit = $('.debit').val();

        if (debit !== '') {
            debit = debit.split(',').join('');
            debit = parseFloat(debit);
        } else {
            debit = 0;
        }

        $('.total_debit').html(number_format(debit));
    }

    function hitungKredit() {
        var kredit = $('.kredit').val();

        if (kredit !== '') {
            kredit = kredit.split(',').join('');
            kredit = parseFloat(kredit);
        } else {
            kredit = 0;
        }

        $('.total_kredit').html(number_format(kredit));
    }
</script>