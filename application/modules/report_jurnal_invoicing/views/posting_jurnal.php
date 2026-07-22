<style>
    .table-jurnal th {
        background-color: #0073b7 !important;
        color: #fff !important;
        vertical-align: middle !important;
        padding: 12px 8px !important;
        border-bottom: 2px solid #0056b3 !important;
    }
    .table-jurnal td {
        vertical-align: middle !important;
        padding: 8px !important;
    }
    .input-readonly {
        background-color: #f4f6f9 !important;
        border: 1px solid #ddd !important;
        color: #444 !important;
        font-weight: 600;
    }
    .textarea-keterangan {
        resize: vertical;
        min-height: 50px;
        font-size: 13px;
        line-height: 1.4;
    }
    .tfoot-jurnal th {
        background-color: #f1f4f9;
        font-size: 15px;
        font-weight: bold;
        color: #333;
        padding: 12px 8px !important;
        border-top: 2px solid #0073b7 !important;
    }
</style>
<div class="table-responsive" style="border-radius: 6px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px;">
    <table class="table table-bordered table-striped table-hover w-100 table-jurnal" style="margin-bottom: 0;">
        <thead>
            <tr>
                <th class="text-center" width="10%">Tanggal</th>
                <th class="text-center" width="10%">Tipe</th>
                <th class="text-center" width="15%">No. COA</th>
                <th class="text-center" width="25%">Keterangan</th>
                <th class="text-center" width="15%">No. Reff</th>
                <th class="text-center" width="12%">Debit</th>
                <th class="text-center" width="13%">Kredit</th>
            </tr>
        </thead>
    <tbody>
        <?php
        $no = 0;

        $ttl_debit = 0;
        $ttl_kredit = 0;
        foreach ($data_jurnal as $row) {
            $no++;

            $get_invoice = $this->db->get_where('tr_invoicing', array('id' => $row->no_transaksi))->row();
            $no_invoice = $get_invoice->no_invoice ?? $row->no_transaksi;

            $ket = explode(' - ', $row->keterangan);
            $keterangan = $ket[0].' - '.$no_invoice ?? $row->keterangan;

            echo '<tr>';
            echo '<td class="text-center">' . date('d M Y', strtotime($row->tgl_jurnal)) . '</td>';
            echo '<td class="text-center">' . $row->jenis_transaksi . '</td>';
            echo '<td class="text-center"><strong>' . $row->coa . '</strong></td>';
            echo '<td class="text-left">';
            echo '<textarea name="dt_jurnal[' . $no . '][keterangan]" class="form-control form-control-sm textarea-keterangan" readonly style="background-color: #fafafa; border: 1px dashed #ccc;">' . $keterangan . '</textarea>';
            echo '</td>';
            echo '<td class="text-center"><span class="label label-info" style="font-size:12px;">' . $no_invoice . '</span></td>';
            echo '<td>';
            echo '<input type="text" name="dt_jurnal[' . $no . '][debit]" class="form-control form-control-sm text-right debit autonum input-readonly" value="' . number_format($row->debit) . '" onchange="hitungDebit();" readonly>';
            echo '</td>';
            echo '<td>';
            echo '<input type="text" name="dt_jurnal[' . $no . '][kredit]" class="form-control form-control-sm text-right kredit autonum input-readonly" value="' . number_format($row->kredit) . '" onchange="hitungKredit();" readonly>';
            echo '</td>';
            echo '</tr>';

            $ttl_debit += $row->debit;
            $ttl_kredit += $row->kredit;
        }
        ?>

    </tbody>
    <tfoot class="tfoot-jurnal">
        <tr>
            <th colspan="5" class="text-right" style="vertical-align: middle;">GRAND TOTAL</th>
            <th class="text-right total_debit" style="color: #00a65a; font-size: 16px;">
                <?= number_format($ttl_debit) ?>
            </th>
            <th class="text-right total_kredit" style="color: #00a65a; font-size: 16px;">
                <?= number_format($ttl_kredit) ?>
            </th>
        </tr>
    </tfoot>
    </table>
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