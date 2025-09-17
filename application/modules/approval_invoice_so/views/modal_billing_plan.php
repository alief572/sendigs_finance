<input type="hidden" name="no_so" value="<?= $results->no_so ?>">
<input type="hidden" name="id_customer" value="<?= $results->id_customer ?>">
<input type="hidden" name="nm_customer" value="<?= $nm_customer ?>">
<input type="hidden" name="ttl_harga" value="<?= $ttl_harga ?>">
<input type="hidden" name="tipe_so" value="<?= $results->tipe_so ?>">

<table width="100%">
    <tr>
        <th>No. SO</th>
        <th class="text-center">:</th>
        <th class="text-left"><?= $results->no_so; ?></th>
        <th>No. DO</th>
        <th class="text-center">:</th>
        <th class="text-left"><?= $no_do; ?></th>
    </tr>
    <tr>
        <th>Tanggal SO</th>
        <th class="text-center">:</th>
        <th class="text-left"><?= date('d-m-Y', strtotime($results->tgl_so)); ?></th>
        <th>Tanggal DO</th>
        <th class="text-center">:</th>
        <th class="text-left"><?= $tanggal_do; ?></th>
    </tr>
</table>

<br>

<table class="table table-bordered">
    <thead>
        <tr>
            <th class="text-center">No</th>
            <th class="text-center">Code</th>
            <th class="text-center">Product Name</th>
            <th class="text-center">Qty</th>
            <th class="text-center">UOM</th>
            <th class="text-center">Price/Unit</th>
            <th class="text-center">Subtotal</th>
            <th class="text-center">Discount</th>
            <th class="text-center">Total</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 1;

        $total_all = 0;
        foreach ($results_detail as $item) {
            echo '<tr>';
            echo '<td class="text-center">' . $no . '</td>';
            echo '<td class="text-center">' . $item->kode_matbom . '</td>';
            echo '<td class="text-left">' . $item->nama_produk . '</td>';
            echo '<td class="text-center">' . number_format($item->qty_so) . '</td>';
            echo '<td class="text-center">' . ucfirst($item->uom) . '</td>';
            echo '<td class="text-right">(' . $currency . ') ' . number_format($item->harga_satuan, 2) . '</td>';
            echo '<td class="text-right">(' . $currency . ') ' . number_format($item->harga_satuan * $item->qty_so, 2) . '</td>';
            echo '<td class="text-right">(' . $currency . ') ' . number_format($item->diskon_nilai, 2) . '</td>';
            echo '<td class="text-right">(' . $currency . ') ' . number_format($item->total_harga, 2) . '</td>';
            echo '</tr>';

            $total_all += $item->total_harga;
            $no++;
        }

        foreach ($detail_other_cost as $item_other_cost) {
            echo '<tr>';
            echo '<td class="text-center">' . $no . '</td>';
            echo '<td class="text-center">-</td>';
            echo '<td class="text-left">' . $item_other_cost->keterangan . '</td>';
            echo '<td class="text-center">1</td>';
            echo '<td class="text-center">-</td>';
            echo '<td class="text-right">(' . $currency . ') ' . number_format($item_other_cost->total_nilai) . '</td>';
            echo '<td class="text-right">(' . $currency . ') ' . number_format($item_other_cost->total_nilai) . '</td>';
            echo '<td class="text-right">(' . $currency . ') ' . number_format(0) . '</td>';
            echo '<td class="text-right">(' . $currency . ') ' . number_format($item_other_cost->total_nilai) . '</td>';
            echo '</tr>';

            $total_all += $item_other_cost->total_nilai;
            $no++;
        }

        foreach ($detail_other_item as $item_other_item) {

            $uom = '';
            $get_uom = $this->db->query('
                    SELECT
                        b.code as uom
                    FROM
                        new_inventory_4 a
                        LEFT JOIN ms_satuan b ON b.id = a.id_unit
                    WHERE
                        a.code_lv4 = "' . $item_other_item->id_other . '"

                    UNION ALL

                    SELECT
                        b.code as uom
                    FROM
                        accessories a
                        LEFT JOIN ms_satuan b ON b.id = a.id_unit
                    WHERE
                        a.id = "' . $item_other_item->id_other . '"
                ')->row();
            if (!empty($get_uom)) {
                $uom = $get_uom->uom;
            }

            echo '<tr>';
            echo '<td class="text-center">' . $no . '</td>';
            echo '<td class="text-center">-</td>';
            echo '<td class="text-left">' . $item_other_item->nm_other . '</td>';
            echo '<td class="text-center">1</td>';
            echo '<td class="text-center">' . ucfirst($uom) . '</td>';
            echo '<td class="text-right">(' . $currency . ') ' . number_format($item_other_item->total) . '</td>';
            echo '<td class="text-right">(' . $currency . ') ' . number_format($item_other_item->total) . '</td>';
            echo '<td class="text-right">(' . $currency . ') ' . number_format(0) . '</td>';
            echo '<td class="text-right">(' . $currency . ') ' . number_format($item_other_item->total) . '</td>';
            echo '</tr>';

            $total_all += $item_other_item->total;
            $no++;
        }
        ?>
    </tbody>
    <tbody>
        <?php
        $ppn = $results->nilai_ppn;
        ?>
        <tr>
            <td colspan="8" class="text-right">DPP</td>
            <td class="text-right"><?= '(' . $currency . ') ' . number_format($total_all, 2) ?></td>
        </tr>
        <tr>
            <td colspan="8" class="text-right">PPN</td>
            <td class="text-right"><?= '(' . $currency . ') ' . number_format($ppn, 2) ?></td>
        </tr>
        <tr>
            <td colspan="8" class="text-right">Grand Total</td>
            <td class="text-right"><?= '(' . $currency . ') ' . number_format($total_all + $ppn, 2) ?></td>
        </tr>
    </tbody>
</table>

<br>

<h4>Detail Invoice
    <!-- &nbsp;&nbsp;<button type="button" class="btn btn-sm btn-success add_top_invoice"><i class="fa fa-plus"></i> Add</button> -->
</h4>
<table class="table table-bordered">
    <thead>
        <tr>
            <th class="text-center"><b>Billing Type</b></th>
            <th class="text-center">Percentage (%)</th>
            <th class="text-center">Value</th>
            <th class="text-center">Billing Plan Date</th>
        </tr>
    </thead>
    
    <tbody>
        <tr>
            <td>DP</td>
            <td>
                <input type="text" name="dp_persen" id="" class="form-control form-control-sm auto_num text-right dp_persen hitung_tipe_so" data-tipe_input="persen" data-tipe_so="dp">
            </td>
            <td>
                <input type="text" name="value_dp" id="" class="form-control form-control-sm auto_num text-right value_dp hitung_tipe_so" data-tipe_input="value" data-tipe_so="dp">
            </td>
            <td>
                <input type="date" name="dp_billing_plan_date" id="" class="form-control form-control-sm dp_billing_plan_date">
            </td>
        </tr>
        <tr>
            <td>Retensi</td>
            <td>
                <input type="text" name="retensi_persen" id="" class="form-control form-control-sm auto_num text-right retensi_persen hitung_tipe_so" data-tipe_input="persen" data-tipe_so="retensi">
            </td>
            <td>
                <input type="text" name="value_retensi" id="" class="form-control form-control-sm auto_num text-right value_retensi hitung_tipe_so" data-tipe_input="value" data-tipe_so="retensi">
            </td>
            <td>
                <input type="date" name="retensi_billing_plan_date" id="" class="form-control form-control-sm retensi_billing_plan_date">
            </td>
        </tr>
        <tr>
            <td>Jaminan</td>
            <td>
                <input type="text" name="jaminan_persen" id="" class="form-control form-control-sm auto_num text-right jaminan_persen hitung_tipe_so" data-tipe_input="persen" data-tipe_so="jaminan">
            </td>
            <td>
                <input type="text" name="value_jaminan" id="" class="form-control form-control-sm auto_num text-right value_jaminan hitung_tipe_so" data-tipe_input="value" data-tipe_so="jaminan">
            </td>
            <td>
                <input type="date" name="jaminan_billing_plan_date" id="" class="form-control form-control-sm jaminan_billing_plan_date">
            </td>
        </tr>
    </tbody>
</table>


<input type="hidden" class="total_all" value="<?= ($total_all + $ppn) ?>">

<script type="text/javascript">
    function loadmod() {
        $('#example1').dataTable();

        $(".chosen-select").select2({
            width: '100%'
        });

        $('.auto_num').autoNumeric('init');
    }

    // $(document).on('click', '.add_top_invoice', function() {

    // });

    $(document).on('change', '.hitung_tipe_so', function() {
        var tipe_input = $(this).data('tipe_input');
        var tipe_so = $(this).data('tipe_so');
        var total_all = $('.total_all').val();
        var nilai = $(this).val();
        if (nilai !== '') {
            nilai = nilai.split(',').join('');
            nilai = parseFloat(nilai);
        } else {
            nilai = 0;
        }

        if (tipe_input == 'persen') {
            var nilai_billing = (total_all * nilai / 100);
            $('.value_' + tipe_so).val(nilai_billing.toLocaleString('en-US', {
                maximumFractionDigits: 2
            }));
        } else {
            var nilai_persen = (nilai / total_all * 100);
            $('.' + tipe_so + '_persen').val(nilai_persen.toLocaleString('en-US', {
                maximumFractionDigits: 2
            }));
        }

        if (tipe_so == 'retensi') {
            $('.jaminan_persen').val('');
            $('.jaminan_persen').attr('required', false);
            // $('.jaminan_persen').attr('readonly', true);

            $('.value_jaminan').val('');
            $('.value_jaminan').attr('required', false);
            // $('.value_jaminan').attr('readonly', true);

            $('.jaminan_billing_plan_date').val('');
            $('.jaminan_billing_plan_date').attr('required', false);
            // $('.jaminan_billing_plan_date').attr('readonly', true);

            $(tipe_so + '_persen').attr('required', true);
            $('value_' + tipe_so).attr('required', true);
            $(tipe_so + '_billing_plan_date').attr('required', true);
        }

        if (tipe_so == 'jaminan') {
            $('.retensi_persen').val('');
            $('.retensi_persen').attr('required', false);
            // $('.retensi_persen').attr('readonly', true);

            $('.value_retensi').val('');
            $('.value_retensi').attr('required', false);
            // $('.value_retensi').attr('readonly', true);

            $('.retensi_billing_plan_date').val('');
            $('.retensi_billing_plan_date').attr('required', false);
            // $('.retensi_billing_plan_date').attr('readonly', true);

            $(tipe_so + '_persen').attr('required', true);
            $('value_' + tipe_so).attr('required', true);
            $(tipe_so + '_billing_plan_date').attr('required', true);
        }

        loadmod();
    });
</script>