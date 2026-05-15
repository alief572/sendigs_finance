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
        $no = 0;

        $ttl_debit = 0;
        $ttl_kredit = 0;
        foreach ($jurnal_header as $row) {
            $no++;

            echo '<tr>';
            echo '<td class="text-center">' . date('d F Y', strtotime($row->tgl_jurnal)) . '</td>';
            echo '<td class="text-center">' . $row->jenis_transaksi . '</td>';
            echo '<td class="text-center">' . $row->coa . '</td>';
            echo '<td class="text-left">' . $row->nm_coa . '</td>';
            echo '<td class="text-left">';
            echo '<textarea name="dt_jurnal[' . $no . '][keterangan]" class="form-control form-control-sm" readonly>' . $row->keterangan . '</textarea>';
            echo '</td>';
            echo '<td class="text-center">' . $row->no_transaksi . '</td>';
            echo '<td>';
            echo '<input type="text" name="dt_jurnal[' . $no . '][debit]" class="form-control form-control-sm text-right debit" value="' . number_format($row->debit) . '" onchange="hitungDebit();" readonly>';
            echo '</td>';
            echo '<td>';
            echo '<input type="text" name="dt_jurnal[' . $no . '][kredit]" class="form-control form-control-sm text-right kredit" value="' . number_format($row->kredit) . '" onchange="hitungKredit();" readonly>';
            echo '</td>';
            echo '</tr>';

            $ttl_debit += $row->debit;
            $ttl_kredit += $row->kredit;
        }
        ?>

    </tbody>
    <tfoot>
        <tr>
            <th colspan="6" class="text-right">Grand Total</th>
            <th class="text-right total_debit">
                <?= number_format($ttl_debit) ?>
                <input type="hidden" name="ttl_debit" value="<?= $ttl_debit ?>">
            </th>
            <th class="text-right total_kredit">
                <?= number_format($ttl_kredit) ?>
                <input type="hidden" name="ttl_kredit" value="<?= $ttl_kredit ?>">
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