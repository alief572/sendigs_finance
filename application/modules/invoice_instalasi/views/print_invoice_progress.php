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
            border: 1px solid black;
            border-collapse: collapse;
        }

        table.gridtable th {
            padding: 6px;
            background-color: #f7f7f7;
            color: black;
            border-color: black;
            border-style: solid;
            border-width: 1px;
        }

        table.gridtable th.head {
            padding: 6px;
            background-color: #f7f7f7;
            color: black;
            border-color: black;
            border-style: solid;
            border-width: 1px;
        }

        table.gridtable td {
            border-width: 1px;
            padding: 6px;
            border-style: solid;
            border-color: black;
        }

        table.gridtable td.cols {
            border-width: 1px;
            padding: 6px;
            border-style: solid;
            border-color: black;
        }


        table.gridtable2 {
            font-family: arial, sans-serif;
            /* font-size: 12px; */
            color: #333333;
            border-width: 1px;
            border-color: black;
            border-collapse: collapse;
        }

        table.gridtable2 td,
        th {
            border-width: 1px solid black;
            padding: 3px;
            border-style: none;
            border-color: black;
            background-color: #ffffff;
        }

        table.gridtable2 td.cols {
            border-width: 1px;
            padding: 1px;
            border-style: none;
            border-color: black;
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
            <td width="280" style="vertical-align: top;">
                <b style="font-size:20px;">PT. ORIGA MULIA FRP</b>
                <p style="font-size: 12px;">
                    Jl. Pembangunan 2 No.34 <br>
                    Kecamatan Batu Ceper, Kelurahan Batusari <br>
                    Kota Tangerang Banten 15122 <br>
                    Indonesia
                </p>
            </td>
            <td align="right" width="350" valign="top">
                <b style="text-decoration: underline; font-size: 20px;">PROGRESS</b>
            </td>
        </tr>
    </table>

    <table style="width:100%; margin-top: 1rem;" border="0">
        <tr>
            <td style="border-top: 1px solid black; border-bottom: 1px solid back;">
                To :
            </td>
        </tr>
        <tr>
            <td>
                <?= $data_customer->nm_customer; ?>
            </td>
        </tr>
    </table>

    <div style="min-width: 50%; max-width: 100%">
        <table class="w-100" border="0" style="margin-top: 1rem;">
            <tr>
                <td style="padding-top: 5px;" rowspan="7" valign="top" width="50">Invoicing Address</td>
                <td style="padding-top: 5px;" rowspan="7" valign="top" align="center" width="20">:</td>
                <td style="padding-top: 5px;" rowspan="7" valign="top" width="250">
                    <?= $data_customer->alamat; ?>
                </td>
                <td style="padding-top: 5px;" valign="top" width="120">Invoice No</td>
                <td style="padding-top: 5px;" valign="top" align="center" width="20">:</td>
                <td style="padding-top: 5px;" valign="top" width="220">
                    <?= $id_invoice ?>
                </td>
            </tr>
            <tr>
                <td style="padding-top: 5px;" valign="top" width="120">Date</td>
                <td style="padding-top: 5px;" valign="top" align="center" width="20">:</td>
                <td style="padding-top: 5px;" valign="top" width="220">
                    <?= date('d F Y', strtotime($data_invoice->created_on)) ?>
                </td>
            </tr>
            <tr>
                <td style="padding-top: 5px;" valign="top" width="120">Due Date</td>
                <td style="padding-top: 5px;" valign="top" align="center" width="20">:</td>
                <td style="padding-top: 5px;" valign="top" width="220">
                    <?= date('d F Y', strtotime('+3 days', strtotime($data_invoice->created_on))) ?>
                </td>
            </tr>
            <tr>
                <td style="padding-top: 5px;" valign="top" width="120">Tax Invoice No.</td>
                <td style="padding-top: 5px;" valign="top" align="center" width="20">:</td>
                <td style="padding-top: 5px;" valign="top" width="220">
                    <?= $data_invoice->tax_invoice_no ?>
                </td>
            </tr>
            <tr>
                <td style="padding-top: 5px;" valign="top" width="120">PO NO.</td>
                <td style="padding-top: 5px;" valign="top" align="center" width="20">:</td>
                <td style="padding-top: 5px;" valign="top" width="220">
                    <?= $data_so->po_no ?>
                </td>
            </tr>
            <tr>
                <td style="padding-top: 5px;" valign="top" width="120">Delivery Term</td>
                <td style="padding-top: 5px;" valign="top" align="center" width="20">:</td>
                <td style="padding-top: 5px;" valign="top" width="220">
                    <?= $data_penawaran->delivery_term ?>
                </td>
            </tr>
            <tr>
                <td style="padding-top: 5px;" valign="top" width="120">Payment Term</td>
                <td style="padding-top: 5px;" valign="top" align="center" width="20">:</td>
                <td style="padding-top: 5px;" valign="top" width="220">
                    <?= $data_payment_term->name ?>
                </td>
            </tr>
            <!-- <tr>
                <td rowspan="5" valign="top" width="50"></td>
                <td rowspan="5" valign="top" align="center" width="20"></td>
                <td rowspan="5" valign="top" width="250">

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

                </td>
            </tr>
            <tr>
                <td valign="top" width="120">Surat Jalan</td>
                <td valign="top" align="center" width="20">:</td>
                <td valign="top" width="250">

                </td>
            </tr>
            <tr>
                <td valign="top" width="120">Delivery Date</td>
                <td valign="top" align="center" width="20">:</td>
                <td valign="top" width="250">

                </td>
            </tr>
            <tr>
                <td valign="top" width="120">Term of Payment</td>
                <td valign="top" align="center" width="20">:</td>
                <td valign="top" width="250">

                </td>
            </tr> -->
        </table>
    </div>

    <div style="min-width: 100%; max-width: 100%">
        <table class="gridtable2" width="100%" border="1" style="margin-top: 10px;">
            <thead>
                <tr>
                    <th align="center" width="100">Kode Barang</th>
                    <th align="center" width="140">Nama Barang</th>
                    <th align="center" width="60">Qty</th>
                    <th align="center" width="80">@Harga</th>
                    <th align="center" width="80">Diskon</th>
                    <th align="center" width="80">Total Harga</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;

                $total = 0;
                $total_disc = 0;
                $subtotal = 0;
                foreach ($data_invoice_detail as $item) {

                    $product_code = '';
                    $get_product = $this->db->get_where('new_inventory_4', ['code_lv4' => $item->id_produt])->row();
                    if (!empty($get_product)) {
                        $product_code = $get_product->code;
                    }

                    echo '<tr>';
                    echo '<td align="left" width="120" style="font-size: 11px;">' . $product_code . '</td>';
                    echo '<td align="left" width="170" style="font-size: 11px;">' . $item->nm_produk . '</td>';
                    echo '<td align="center" style="font-size: 11px;" width="60">' . number_format($item->qty) . '</td>';
                    echo '<td align="right" style="font-size: 11px;" width="80">' . number_format($item->harga, 2) . '</td>';
                    echo '<td align="right" style="font-size: 11px;" width="80">' . number_format($item->disc, 2) . '</td>';
                    echo '<td align="right" style="font-size: 11px;" width="120">' . number_format($item->subtotal, 2) . '</td>';
                    echo '</tr>';

                    $total += ($item->harga * $item->qty);
                    $total_disc += ($item->disc * $item->qty);
                    $subtotal += $item->subtotal;

                    $no++;
                }

                $vat = $data_invoice->nilai_ppn;
                ?>
            </tbody>
            <tbody>

            </tbody>
        </table>
    

    <!-- <div style='display:block; border-color:none; background-color:#c2c2c2; margin-top: 1rem;' align='center'>
        <h3>PURCHASE ORDER</h3>
    </div> -->
    <br><br>

    <table border="0">
        <tr>
            <td align="center" style="padding: 5px;" width="120">Terbilang :</td>
            <td align="center" style="border: 1px solid black; padding: 5px;" width="550"><?= ucfirst(rupiah_to_words($data_invoice->nilai_invoice)) ?></td>
        </tr>
    </table>
    </div>

    <br><br>

    <!-- <tr>
        <td style="font-size: 11px;" align="right" colspan="5"><span style="font-weight: bold;">Total</span></td>
        <td style="font-size: 11px;" align="right" colspan="1"><?= number_format($total, 2) ?></td>
    </tr> -->
    <!-- <tr>
        <td style="font-size: 11px;" align="right" colspan="5"><span style="font-weight: bold;">Total Disc</span></td>
        <td style="font-size: 11px;" align="right" colspan="1"><?= number_format($total_disc, 2) ?></td>
    </tr>
    <tr>
        <td style="font-size: 11px;" align="right" colspan="5"><span style="font-weight: bold;">Sub Total</span></td>
        <td style="font-size: 11px;" align="right" colspan="1"><?= number_format($subtotal, 2) ?></td>
    </tr>
    <tr>
        <td style="font-size: 11px;" align="right" colspan="5"><span style="font-weight: bold;">VAT</span></td>
        <td style="font-size: 11px;" align="right" colspan="1"><?= number_format($vat, 2) ?></td>
    </tr>
    <tr>
        <td style="font-size: 11px;" align="right" colspan="5"><span style="font-weight: bold;">Total DownPayment</span></td>
        <td style="font-size: 11px;" align="right" colspan="1"><?= number_format($data_invoice->nilai_invoice, 2) ?></td>
    </tr> -->

    <table width="100%" border="0">
        <tr>
            <th align="left" width="350">
                <span style="text-decoration: underline;">NOTE : </span>
            </th>
            <td style="border: 1px solid black;" align="left" width="150"><span style="font-weight: bold;">Total</span></td>
            <td style="border: 1px solid black;" align="right" width="200"><?= number_format($total, 2) ?></td>
        </tr>
        <tr>
            <td width="220" rowspan="100">
                <b style="line-height: 1.2">Silahkan Transfer Pembayaran FULL AMOUNT Ke : <br>
                    PT. Origa Mulia FRP <br>
                    BCA ARJUNA (Rp) A/N 601.009.3242 <br>
                    PANIN(USD) AC 140.603.1638 <br>
                    *Penalti 0.1% /hari, max 5% dihitung sejak tanggal jatuh tempo pembayaran <br>
                    *Untuk tagihan USD yang harus dibayar dalam rupiah harap konfirmasi nilai tukar ke Keuangan kami <br>
                    *Email Keuangan : fin01@origa.co.id; fin02@origa.co.id <br>
                </b>
            </td>
            <td align="left" width="150" style="border: 1px solid black;"><span style="font-weight: bold;">Total Disc</span></td>
            <td align="right" width="200" style="border: 1px solid black;"><?= number_format($total_disc, 2) ?></td>
        </tr>
        <tr>
            <td align="left" width="150" style="border: 1px solid black;"><span style="font-weight: bold;">Subtotal</span></td>
            <td align="right" width="200" style="border: 1px solid black;"><?= number_format($subtotal, 2) ?></td>
        </tr>
        <tr>
            <td align="left" width="150" style="border: 1px solid black;"><span style="font-weight: bold;">VAT</span></td>
            <td align="right" width="200" style="border: 1px solid black;"><?= number_format($vat, 2) ?></td>
        </tr>
        <tr>
            <td align="left" width="150" style="border: 1px solid black;"><span style="font-weight: bold;">Total Progress</span></td>
            <td align="right" width="200" style="border: 1px solid black;"><?= number_format($data_invoice->nilai_invoice, 2) ?></td>
        </tr>
        <?php
        for ($i = 1; $i <= 30; $i++) {
            if ($i == '12') {
                echo '
                    <tr>
                        <td  align="right" colspan="2">Tangerang, ' . date('d F Y', strtotime($data_invoice->created_on)) . '</td>
                    </tr>   
                ';
            } else if ($i == '20') {
                echo '
                    <tr>
                        <td align="right">
                           
                        </td>
                        <td align="center">
                            <b>' . $this->auth->user_name() . '</b>
                        </td>
                    </tr>
                ';
            } else {
                echo '
                    <tr>
                        <td style="font-size: 11px;" align="left" width="150"></td>
                        <td style="font-size: 11px;" align="right" width="200"></td>
                    </tr>
                ';
            }
        }
        ?>

    </table>

    <br><br><br><br>

    <div align="right" width="735">
        <b>
            Doc. No. : FR-FIN-09-00
        </b>
    </div>


</body>

</html>