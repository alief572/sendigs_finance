<html>

<head>
    <style type="text/css">
        @media print {

            table,
            div {
                break-inside: avoid;
            }
        }

        thead {
            display: table-header-group;
        }

        tfoot {
            display: table-row-group;
        }

        tr {
            page-break-after: always !important;
            page-break-before: always !important;
            page-break-inside: auto !important;

        }

        .header_style_company {
            padding: 15px;
            color: black;
            font-size: 20px;
            vertical-align: bottom;
        }

        .header_style_company2 {
            padding: 15px;
            color: black;
            font-size: 15px;
            vertical-align: top;
        }

        .header_style_alamat {
            padding: 10px;
            color: black;
            font-size: 10px;
        }

        table.default {
            font-family: arial, sans-serif;
            font-size: 9px;
            padding: 0px;
        }

        p {
            font-family: arial, sans-serif;
            font-size: 14px;
        }

        .font {
            font-family: arial, sans-serif;
            font-size: 14px;
        }

        table.gridtable {
            font-family: arial, sans-serif;
            font-size: 11px;
            color: #333333;
            border: 1px solid #808080;
            border-collapse: collapse;
        }

        table.gridtable th {
            padding: 6px;
            background-color: #f7f7f7;
            color: black;
            border-color: #808080;
            border-style: solid;
            border-width: 1px;
        }

        table.gridtable th.head {
            padding: 6px;
            background-color: #f7f7f7;
            color: black;
            border-color: #808080;
            border-style: solid;
            border-width: 1px;
        }

        table.gridtable td {
            border-width: 1px;
            padding: 6px;
            border-style: solid;
            border-color: #808080;
        }

        table.gridtable td.cols {
            border-width: 1px;
            padding: 6px;
            border-style: solid;
            border-color: #808080;
        }


        table.gridtable2 {
            font-family: arial, sans-serif;
            font-size: 12px;
            color: #333333;
            border-width: 1px;
            border-color: #666666;
            border-collapse: collapse;
        }

        table.gridtable2 td,
        th {
            border-width: 1px;
            padding: 3px;
            border-style: none;
            border-color: #666666;
            background-color: #ffffff;
        }

        table.gridtable2 td.cols {
            border-width: 1px;
            padding: 1px;
            border-style: none;
            border-color: #666666;
            background-color: #ffffff;
        }

        table.gridtableX {
            font-family: arial, sans-serif;
            font-size: 12px;
            color: #333333;
            border: none;
            border-collapse: collapse;
        }

        table.gridtableX td {
            border-width: 1px;
            padding: 6px;
        }

        table.gridtableX td.cols {
            border-width: 1px;
            padding: 6px;
        }

        table.gridtableX2 {
            font-family: arial, sans-serif;
            font-size: 12px;
            color: #333333;
            border: none;
            border-collapse: collapse;
        }

        table.gridtableX2 td {
            border-width: 1px;
            padding: 2px;
        }

        table.gridtableX2 td.cols {
            border-width: 1px;
            padding: 2px;
        }

        #testtable {
            width: 100%;
        }
    </style>
</head>

<body>

    <table class="gridtable2" border="0">
        <tr>
            <td style="text-align:left;" valign="top">
                <img src="assets/images/ori_logo2.png" alt="" width="75" height="95">
            </td>
            <td width="280" style="vertical-align: middle;">
                <b style="font-size:20px;">PT. ORIGA MULIA FRP</b>
                <p style="font-size: 12px;">

                </p>
            </td>
            <td align="right" width="350" valign="middle">
                <b style="text-decoration: underline; font-size: 20px;">INVOICE</b>
            </td>
        </tr>
    </table>

    <p style="margin-top: 1rem;">
        Jl. Pembangunan 2 No.34 <br>
        Kecamatan Batu Ceper, Kelurahan Batusari <br>
        Kota Tangerang Banten 15122 <br>
        Indonesia
    </p>

    <table style="width:100%;" border="0">
        <tr>
            <td>NPWP</td>
            <td class="text-center">:</td>
            <td><?= $data_customer->npwp ?></td>
        </tr>
        <tr>
            <td>Phone No</td>
            <td class="text-center">:</td>
            <td><?= $data_customer->telpon ?></td>
        </tr>
        <tr>
            <td>Fax No</td>
            <td class="text-center">:</td>
            <td><?= $data_customer->fax ?></td>
        </tr>
    </table>

    <div style="min-width: 50%; max-width: 100%">
        <table class="w-100" border="0" style="margin-top: 1rem;">
            <tr>
                <td rowspan="2" valign="top" width="50">To</td>
                <td rowspan="2" valign="top" align="center" width="20">:</td>
                <td rowspan="2" valign="top" width="250">
                    <?= $data_customer->nm_customer ?>
                </td>
                <td valign="top" width="120">Invoice No</td>
                <td valign="top" align="center" width="20">:</td>
                <td valign="top" width="250">
                    <?= $id_invoice ?>
                </td>
            </tr>
            <tr>
                <td valign="top" width="120">Tax Invoice No</td>
                <td valign="top" align="center" width="20">:</td>
                <td valign="top" width="250">
                    <?= $data_invoice->tax_invoice_no ?>
                </td>
            </tr>
            <tr>
                <td rowspan="5" valign="top" width="50">Address</td>
                <td rowspan="5" valign="top" align="center" width="20">:</td>
                <td rowspan="5" valign="top" width="250">
                    <?= $data_customer->alamat ?>
                </td>
                <td valign="top" width="120">PO No</td>
                <td valign="top" align="center" width="20">:</td>
                <td valign="top" width="250">
                    <?= $data_so->po_no ?>
                </td>
            </tr>
            <tr>
                <td valign="top" width="120">Due Date</td>
                <td valign="top" align="center" width="20">:</td>
                <td valign="top" width="250">
                    <?= date('d F Y', strtotime('+' . $data_penawaran->jumlah_top . ' days', strtotime($data_delivery->delivery_date))); ?>
                </td>
            </tr>
            <tr>
                <td valign="top" width="120">Surat Jalan</td>
                <td valign="top" align="center" width="20">:</td>
                <td valign="top" width="250">
                    <?= $data_delivery->no_surat_jalan ?>
                </td>
            </tr>
            <tr>
                <td valign="top" width="120">Delivery Date</td>
                <td valign="top" align="center" width="20">:</td>
                <td valign="top" width="250">
                    <?= date('d F Y', strtotime($data_delivery->delivery_date)) ?>
                </td>
            </tr>
            <tr>
                <td valign="top" width="120">Term of Payment</td>
                <td valign="top" align="center" width="20">:</td>
                <td valign="top" width="250">
                    <?= $data_penawaran->top_name; ?>
                </td>
            </tr>
        </table>
    </div>

    <br><br>

    <table class="gridtable2" width="100%" border="1">
        <thead>
            <tr>
                <th align="center">No.</th>
                <th align="center" width="100">Product Code</th>
                <th align="center" width="140">Product Description</th>
                <th align="center" width="60">Qty</th>
                <th align="center" width="60">UOM</th>
                <th align="center" width="80">Unit Price</th>
                <th align="center" width="80">Discount</th>
                <th align="center" width="80">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;

            $total = 0;
            foreach ($data_invoice_detail as $item) {

                $product_code = '';
                $get_product = $this->db->get_where('new_inventory_4', ['code_lv4' => $item->id_produt])->row();
                if (!empty($get_product)) {
                    $product_code = $get_product->code;
                }

                echo '<tr>';
                echo '<td align="center" style="font-size: 11px;">' . $no . '</td>';
                echo '<td align="left" width="100" style="font-size: 11px;">' . $product_code . '</td>';
                echo '<td align="left" width="140" style="font-size: 11px;">' . $item->nm_produk . '</td>';
                echo '<td align="center" style="font-size: 11px;" width="60">' . number_format($item->qty) . '</td>';
                echo '<td align="center" style="font-size: 11px;" width="60">' . strtoupper($item->uom) . '</td>';
                echo '<td align="right" style="font-size: 11px;" width="80">' . number_format($item->harga, 2) . '</td>';
                echo '<td align="right" style="font-size: 11px;" width="80">' . number_format($item->disc, 2) . '</td>';
                echo '<td align="right" style="font-size: 11px;" width="80">' . number_format($item->subtotal, 2) . '</td>';
                echo '</tr>';

                $no++;

                $total += $item->subtotal;
            }

            $vat = $data_invoice->nilai_ppn;

            $dpp = ($total - $nilai_dp - $nilai_retensi);
            $total_all = (($dpp) + $vat);
            $total_tagihan = ($total_all + $nilai_jaminan);
            ?>
        </tbody>
        <tbody>
            <tr>
                <td style="font-size: 11px;" align="right" colspan="6"><span style="font-weight: bold;">Total</span></td>
                <td style="font-size: 11px;" align="right" colspan="2"><?= number_format($total, 2) ?></td>
            </tr>
            <tr>
                <td style="font-size: 11px;" align="right" colspan="6"><span style="font-weight: bold;">DP Proporsional (<?= number_format($persen_dp, 2) ?>%)</span></td>
                <td style="font-size: 11px;" align="right" colspan="2"><?= number_format($nilai_dp, 2) ?></td>
            </tr>
            <tr>
                <td style="font-size: 11px;" align="right" colspan="6"><span style="font-weight: bold;">Retensi Proporsional (<?= number_format($persen_retensi, 2) ?>%)</span></td>
                <td style="font-size: 11px;" align="right" colspan="2"><?= number_format($nilai_retensi, 2) ?></td>
            </tr>
            <tr>
                <td style="font-size: 11px;" align="right" colspan="6"><span style="font-weight: bold;">DPP</span></td>
                <td style="font-size: 11px;" align="right" colspan="2"><?= number_format($dpp, 2) ?></td>
            </tr>
            <!-- <tr>
                <td style="font-size: 11px;" align="right" colspan="6"><span style="font-weight: bold;">Other Cost</span></td>
                <td style="font-size: 11px;" align="right" colspan="2">0</td>
            </tr> -->
            <tr>
                <td style="font-size: 11px;" align="right" colspan="6"><span style="font-weight: bold;">PPn (<?= $data_invoice->ppn ?>%)</span></td>
                <td style="font-size: 11px;" align="right" colspan="2"><?= number_format($data_invoice->nilai_ppn, 2) ?></td>
            </tr>
            <tr>
                <td style="font-size: 11px;" align="right" colspan="6"><span style="font-weight: bold;">Subtotal</span></td>
                <td style="font-size: 11px;" align="right" colspan="2"><?= number_format($total_all, 2) ?></td>
            </tr>
            <tr>
                <td style="font-size: 11px;" align="right" colspan="6"><span style="font-weight: bold;">Jaminan</span></td>
                <td style="font-size: 11px;" align="right" colspan="2"><?= number_format($nilai_jaminan, 2) ?></td>
            </tr>
            <tr>
                <td style="font-size: 11px;" align="right" colspan="6"><span style="font-weight: bold;">Total Tagihan</span></td>
                <td style="font-size: 11px;" align="right" colspan="2"><?= number_format($total_tagihan, 2) ?></td>
            </tr>
        </tbody>
    </table>

    <br><br>

    <table border="">
        <tr>
            <td align="center" style="padding: 5px;" width="120">Say :</td>
            <td align="center" style="border: 1px solid black; padding: 5px;" width="550"><?= ucfirst(rupiah_to_words($total_tagihan)) ?></td>
        </tr>
    </table>

    <br><br>

    <table width="100%" border="0">
        <tr>
            <th align="left" width="350">
                <span style="text-decoration: underline;">NOTE</span>
            </th>
            <th align="right" width="350">
                Jakarta, <?= date('d F Y', strtotime($data_delivery->delivery_date)) ?>
            </th>
        </tr>
        <tr>
            <td colspan="2">
                Pembayaran dengan cek / giro dianggap sah, setelah Cek / Giro dapat <br> diuangkan (Clearing)
            </td>
        </tr>
        <tr>
            <td colspan="2">
                *Denda 0,1% / Hari, maks 5% dihitung sejak jatuh tempo pembayaran
            </td>
        </tr>
        <tr>
            <td colspan="2">
                *Untuk tagihan USD yang dilakukan dalam rupiah, harap konfirmasi dengan <br> keuangan kami
            </td>
        </tr>
    </table>

    <br><br>

    Silahkan Transfer Pembayaran FULL AMOUNT ke :
    <table border="0">
        <tr>
            <td>
                PT. ORIGA MULIA FRP
            </td>
            <td align="right" width="240">

            </td>
        </tr>
        <tr>
            <td>
                <span style="line-height: 1.5;">
                    BCA. IDR AC 6010093242 / PANIN USD AC 140.603.1638 <br>
                    *Penalti 0,1% / Hari, maks 5% dihitung sejak tanggal jatuh tempo pembayaran <br>
                    *Untuk tagihan USD yang harus dibayar dalam rupiah, harap konfirmasi <br> nilai tukar dengan keuangan kami <br>
                    *Email Keuangan : fin02@origa.co.id
                </span>
            </td>
            <td align="right" width="240">
                <b style="padding-top: 50px;"><?= $this->auth->user_name(); ?></b>
            </td>
        </tr>
    </table>


    <!-- <div style='display:block; border-color:none; background-color:#c2c2c2; margin-top: 1rem;' align='center'>
        <h3>PURCHASE ORDER</h3>
    </div> -->
    <br>

    <div align="right" width="735">
        <b>
            Doc. No. : FR-FIN-08-00
        </b>
    </div>


</body>

</html>