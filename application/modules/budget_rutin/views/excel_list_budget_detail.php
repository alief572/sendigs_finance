<?php
$wh_name = !empty($warehouse) ? $warehouse->nm_gudang : 'SENTRAL SISTEM';
$safe_wh = preg_replace('/[^A-Za-z0-9_\-]/', '_', $wh_name);
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Budget_Stock_" . $safe_wh . "_" . date('Ymd') . ".xls");
?>
<table width="100%" border="1">
    <thead>
        <tr>
            <th colspan="7" style="font-size:16px; font-weight:bold; text-align:center; height:35px; background-color:#f2f2f2;">
                DAFTAR BUDGET STOCK &mdash; WAREHOUSE: <?= strtoupper($wh_name) ?>
            </th>
        </tr>
        <tr style="background-color: #2e6da4; color: #ffffff; font-weight:bold;">
            <th width="5%">#</th>
            <th width="20%">Kategori Stock</th>
            <th width="30%">Nama Barang</th>
            <th width="20%">Spesifikasi</th>
            <th width="8%">Kebutuhan 1 Bulan</th>
            <th width="8%">Satuan</th>
            <th width="15%">Price Reference (IDR)</th>
            <th width="15%">Total Price (IDR)</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 0;
        $grand_total = 0;
        $curr_cat = '';
        foreach ($data_detail as $item) {
            $no++;
            $grand_total += $item->total_price;
            $cat_display = !empty($item->nm_category) ? strtoupper($item->nm_category) : '-';
            echo '
                <tr>
                    <td align="center">' . $no . '</td>
                    <td><b>' . $cat_display . '</b></td>
                    <td>' . $item->stock_name . '</td>
                    <td>' . ($item->spec ?? '-') . '</td>
                    <td align="center">' . $item->kebutuhan_month . '</td>
                    <td align="center">' . strtoupper($item->code ?? 'PCS') . '</td>
                    <td align="right">' . number_format($item->price_reference, 0, ',', '.') . '</td>
                    <td align="right">' . number_format($item->total_price, 0, ',', '.') . '</td>
                </tr>
            ';
        }
        ?>
    </tbody>
    <tfoot>
        <tr style="font-weight:bold; background-color: #e8f4f8; font-size:14px;">
            <td colspan="7" align="right">TOTAL KESELURUHAN BUDGET STOCK:</td>
            <td align="right"><?= number_format($grand_total, 0, ',', '.') ?></td>
        </tr>
    </tfoot>
</table>
