<style>
    .table_invoice {
        border: 1px solid black;
        /* Outer border */
        border-collapse: collapse;
    }

    .table_invoice td,
    th {
        border: none;
        /* No inner borders */
        padding: 8px;
        vertical-align: top;
    }

    .table_list_barang {
        width: 100%;
        border: 1px solid black;
        /* Outer border */
        border-collapse: collapse;
    }

    .table_list_barang thead {
        background-color: #ccc !important;
        border: 1px solid black;
        text-align: center;
    }

    @media print {
        .table_list_barang thead {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            background-color: #ccc;
        }
    }
</style>
<table width="100%" border="0">
    <?php
    if ($id_company == '3') {
    ?>

        <tr>
            <td style="text-align: center; vertical-align: top;">
                <img src="<?= base_url('assets/images/sentral_sustain.png') ?>" alt="" width="100%">
            </td>
        </tr>

    <?php
    } else if ($id_company == '4') {
    ?>

        <tr>
            <td style="text-align: center; vertical-align: top;">
                <img src="<?= base_url('assets/images/logo_vuca.png') ?>" alt="" width="100%">
            </td>
        </tr>

    <?php
    } else {
    ?>

        <tr>
            <td rowspan="2" style="text-align: center; vertical-align: top;">
                <img src="<?= base_url('assets/images/logo_sentral.png') ?>" alt="" width="300px">
            </td>
            <td style="text-align: center;">
                <h2 style="font-weight: bold;">PT. Sentral Tehnologi Managemen</h2>
            </td>
        </tr>
        <tr>
            <td style="text-align: center;">
                <p style="font-size: 14px;">Jl. MT. Haryono Kav. 10, Jakarta Timur 13330</p>
                <p style="font-size: 14px;">Telp. (021) 29067201, 29067202 Fax. (021) 29067204</p>
                <p style="font-size: 14px;">Email : sspm@sentralsistem.com Website : www.sentralsistem.com</p>
            </td>
        </tr>

    <?php
    }
    ?>
</table>

<table width="100%" class="table_invoice">
    <tr>
        <th style="text-align: center;" colspan="6">
            <h2>INVOICE</h2>
        </th>
    </tr>
    <tr>
        <td>Kepada</td>
        <td style="text-align: center;">:</td>
        <td style="font-weight: bold;"><?= $data_invoice->nm_customer ?></td>
        <td>Tanggal Invoice</td>
        <td style="text-align: center;">:</td>
        <td><?= date('d F Y', strtotime($data_invoice->tanggal_invoice)) ?></td>
    </tr>
    <tr>
        <td>Alamat</td>
        <td style="text-align: center;">:</td>
        <td><?= $data_invoice->address ?></td>
        <td>Nomor Invoice</td>
        <td style="text-align: center;">:</td>
        <td><?= $data_invoice->no_invoice ?></td>
    </tr>
    <tr>
        <td>Up</td>
        <td style="text-align: center;">:</td>
        <td>Finance Dept.</td>
        <td>No. PO</td>
        <td style="text-align: center;">:</td>
        <td><?= $data_invoice->no_po ?></td>
    </tr>
</table>
<table class="table_list_barang">
    <thead>
        <?php
        if ($id_company == '3' || $id_company == '4') {
            echo '<tr>';

            echo '<th>Nama Barang / Pesanan</th>';
            echo '<th>Jumlah</th>';
            echo '<th>Harga @</th>';
            echo '<th>Disc.</th>';
            echo '<th>Subtotal</th>';
            echo '<th>Pajak</th>';

            echo '</tr>';
        } else {
            echo '<tr>';
            echo '<th>Nama Barang / Pesanan</th>';
            echo '<th>Jumlah</th>';
            echo '<th>Harga @</th>';
            echo '<th>Disc</th>';
            echo '<th>Sub Total</th>';
            echo '</tr>';
        }
        ?>

    </thead>
    <tbody>
        <?php
        if ($id_company == '3' || $id_company == '4') {
        ?>
            <tr>
                <td style="text-align: center; height: 200px; vertical-align: top;"><?= ucfirst($data_actual_plan_tagih->desc_payment) ?></td>
                <td style="text-align: center; height: 200px; vertical-align: top;">1</td>
                <td style="text-align: center; height: 200px; vertical-align: top;"><?= number_format($data_actual_plan_tagih->nominal_payment) ?></td>
                <td style="text-align: center; height: 200px; vertical-align: top;"></td>
                <td style="text-align: center; height: 200px; vertical-align: top;"><?= number_format($data_actual_plan_tagih->nominal_payment) ?></td>
                <td style="text-align: center; height: 200px; vertical-align: top;">0%</td>
            </tr>
            <tr>
                <td style="border-top: 1px solid black;" colspan="2"></td>
                <td style="border-top: 1px solid black;">Discount Final</td>
                <td style="text-align: center; border-top: 1px solid black;">:</td>
                <td style="text-align: right; border-top: 1px solid black;">-</td>
                <td style="text-align: center; border-top: 1px solid black;"></td>
            </tr>
            <tr>
                <td style="" colspan="2"></td>
                <td style="">Pajak</td>
                <td style="text-align: center; ">:</td>
                <td style="text-align: right; ">-</td>
                <td style="text-align: center; "></td>
            </tr>
            <tr>
                <td style="" colspan="2"></td>
                <td style="">Biaya Pengiriman</td>
                <td style="text-align: center; ">:</td>
                <td style="text-align: right; ">-</td>
                <td style="text-align: center; "></td>
            </tr>
            <tr>
                <td style="" colspan="2"></td>
                <td style="">
                    <span style="font-weight: bold;">Total</span>
                </td>
                <td style="text-align: center; ">:</td>
                <td style="text-align: right; ">
                    <span style="font-weight: bold;">
                        <?= number_format($data_actual_plan_tagih->nominal_payment) ?>
                    </span>
                </td>
                <td style="text-align: center; "></td>
            </tr>
            <tr>
                <td colspan="4" rowspan="4" style="border-top: 1px solid black;">
                    <p>Keterangan</p>
                    <p>Pembayaran harus dilakukan paling lambat 14 hari <br> setelah Invoice diterima</p>
                    <br>
                    <p>Pembayaran di Transfer ke :</p>
                    <?php
                    if ($id_company == '3') {
                    ?>
                        <b>PT. SENTRAL SUSTAINABILITY CONSULTING</b><br>
                        <b>BCA Tebet Barat Acc. No 436.400.0300</b>
                        <p>Bukti pembayaran mohon di email ke : Finance@sentralsistem.com</p>
                    <?php
                    } else if ($id_company == '4') {
                    ?>
                        <b>PT. VUCA STRATEGI BISNIS</b><br>
                        <b>OCBC NISP Acc. No 7788.0000.0417</b>
                        <p>Bukti pembayaran mohon di email ke : Finance@sentralsistem.com</p>
                    <?php
                    } else {
                    ?>
                        <b>PT. SENTRAL TEHNOLOGI MANAGEMEN</b><br>
                        <b>BCA Tebet Barat Acc. No 436.300.5287</b>
                        <p>Bukti pembayaran mohon di email ke : Finance@sentralsistem.com</p>
                    <?php
                    }
                    ?>
                </td>
                <td colspan="2" style="border-top: 1px solid black; text-align: center;">
                    <?php
                    if ($id_company == '3') {
                        echo '<b style="font-size: 14px;">PT. SENTRAL SUSTAINABILITY CONSULTING</b>';
                    } else if ($id_company == '4') {
                        echo '<b style="font-size: 14px;">PT. VUCA STRATEGI BISNIS</b>';
                    } else {
                        echo '<b style="font-size: 14px;">PT. SENTRAL TEHNOLOGI MANAGEMEN</b>';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td></td>
                <td colspan="2" style="text-align: left;">
                    <?php
                    if ($id_company !== '3') {
                    ?>
                        <span style="color: #ccc; text-align: center !important; font-size: 10px;">
                            Digitally Signned By : <br>
                            Imanuel Iman <br>
                            <?php
                            if ($id_company == '4') {
                                echo 'PT. Vuca Strategi Bisnis <br>';
                            } else {
                                echo 'PT. Sentral Tehnologi Managemen <br>';
                            }
                            ?>
                        </span>
                    <?php
                    }
                    ?>

                </td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: center; ">
                    <?php
                    if ($id_company == '3') {
                        echo '<span style="text-decoration: underline; font-weight: bold;">Cahyadi</span>';
                    } else {
                        echo '<span style="text-decoration: underline; font-weight: bold;">Imanuel Iman</span>';
                    }
                    ?>
                    <br>
                    <span>Direktur</span>
                </td>
            </tr>
            <tr>
                <td style="text-align: center;"></td>
            </tr>
        <?php
        } else {
        ?>
            <tr>
                <td style="text-align: center; height: 200px; vertical-align: top;"><?= $data_actual_plan_tagih->desc_payment ?></td>
                <td style="text-align: center; height: 200px; vertical-align: top;">1</td>
                <td style="text-align: center; height: 200px; vertical-align: top;"><?= number_format($data_actual_plan_tagih->nominal_payment) ?></td>
                <td style="text-align: center; height: 200px; vertical-align: top;"></td>
                <td style="text-align: center; height: 200px; vertical-align: top;"><?= number_format($data_actual_plan_tagih->nominal_payment) ?></td>
            </tr>
            <tr>
                <td style="border-top: 1px solid black;" colspan="2"></td>
                <td style="border-top: 1px solid black;">Total</td>
                <td style="text-align: center; border-top: 1px solid black;">:</td>
                <td style="text-align: right; border-top: 1px solid black;"><?= number_format($data_actual_plan_tagih->nominal_payment) ?></td>
            </tr>
            <tr>
                <td colspan="2"></td>
                <td>DPP Nilai Lain</td>
                <td style="text-align: center;">:</td>
                <td style="text-align: right;"><?= number_format($data_invoice->dpp_nilai_lain) ?></td>
            </tr>
            <tr>
                <td colspan="2"></td>
                <td>Pajak 12%</td>
                <td style="text-align: center;">:</td>
                <td style="text-align: right;"><?= number_format($data_invoice->pajak) ?></td>
            </tr>
            <tr>
                <td colspan="2"></td>
                <td style="font-weight: bold;">TOTAL</td>
                <td style="text-align: center;">:</td>
                <td style="text-align: right;"><?= number_format($data_invoice->total_akhir) ?></td>
            </tr>
            <tr>
                <td colspan="5">
                    <?= terbilang($data_invoice->total_akhir) . ' Rupiah' ?>
                </td>
            </tr>
            <tr>
                <td colspan="3" rowspan="4" style="border-top: 1px solid black;">
                    <p>Keterangan</p>
                    <p>Pembayaran harus dilakukan paling lambat 14 hari <br> setelah Invoice diterima</p>
                    <br>
                    <p>Pembayaran di Transfer ke :</p>
                    <?php
                    if ($id_company == '3') {
                    ?>
                        <b>PT. SENTRAL SUSTAINABILITY CONSULTING</b><br>
                        <b>OCBC NISP Acc. No 7788.0000.0417</b>
                        <p>Bukti pembayaran mohon di email ke : Finance@sentralsistem.com</p>
                    <?php
                    } else if ($id_company == '4') {
                    ?>
                        <b>PT. VUCA STRATEGI BISNIS</b><br>
                        <b>OCBC NISP Acc. No 7788.0000.0417</b>
                        <p>Bukti pembayaran mohon di email ke : Finance@sentralsistem.com</p>
                    <?php
                    } else {
                    ?>
                        <b>PT. SENTRAL TEHNOLOGI MANAGEMEN</b><br>
                        <b>BCA Tebet Barat Acc. No 436.300.5287</b>
                        <p>Bukti pembayaran mohon di email ke : Finance@sentralsistem.com</p>
                    <?php
                    }
                    ?>
                </td>
                <td colspan="2" style="border-top: 1px solid black; text-align: center;">
                    <b style="font-size: 14px;">PT. SENTRAL TEHNOLOGI MANAGEMEN</b>
                </td>
            </tr>
            <tr>
                <td></td>
                <td colspan="2" style="text-align: left;">
                    <span style="color: #ccc; text-align: left !important; font-size: 10px;">
                        Digitally Signned By : <br>
                        Imanuel Iman <br>
                        <?php
                        if ($id_company == '4') {
                            echo 'PT. Vuca Strategi Bisnis <br>';
                        } else {
                            echo 'PT. Sentral Tehnologi Managemen <br>';
                        }
                        ?>
                    </span>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: center; ">
                    <?php
                    if ($id_company == '3') {
                        echo '<span style="text-decoration: underline; font-weight: bold;">Cahyadi</span>';
                    } else {
                        echo '<span style="text-decoration: underline; font-weight: bold;">Imanuel Iman</span>';
                    }
                    ?>
                    <br>
                    <span>Direktur</span>
                </td>
            </tr>
            <tr>
                <td style="text-align: center;"></td>
            </tr>
        <?php
        }
        ?>
    </tbody>
</table>


<script>
    window.print();
</script>