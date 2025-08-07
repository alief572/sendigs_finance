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

    td {
        font-size: 13px;
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
    } else if ($id_company == '4' || $id_company == '5') {
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
        <td width="80">Kepada</td>
        <td style="text-align: center;">:</td>
        <td style="font-weight: bold;" width="230"><?= $data_invoice->nm_customer ?></td>
        <td width="110">Tanggal Invoice</td>
        <td style="text-align: center;">:</td>
        <td width="200"><?= date('d F Y', strtotime($data_invoice->tanggal_invoice)) ?></td>
    </tr>
    <tr>
        <td width="80">Alamat</td>
        <td style="text-align: center;">:</td>
        <td width="230"><?= $data_invoice->address ?></td>
        <td width="110">Nomor Invoice</td>
        <td style="text-align: center;">:</td>
        <td width="200"><?= $data_invoice->no_invoice ?></td>
    </tr>
    <tr>
        <td width="80">Up</td>
        <td style="text-align: center;">:</td>
        <td width="230">Finance Dept.</td>
        <td width="110">No. PO</td>
        <td style="text-align: center;">:</td>
        <td width="200"><?= $data_invoice->no_po ?></td>
    </tr>
</table>
<table class="table_list_barang">
    <thead>
        <?php
        if ($id_company == '3' || $id_company == '4' || $id_company == '5') {
            echo '<tr>';
            echo '<th style="font-size: 15px">Nama Barang / Pesanan</th>';
            echo '<th style="font-size: 15px">Jumlah</th>';
            echo '<th style="font-size: 15px">Harga @</th>';
            echo '<th style="font-size: 15px">Disc</th>';
            echo '<th style="font-size: 15px">Sub Total</th>';
            echo '</tr>';
        } else {
            echo '<tr>';
            echo '<th style="font-size: 15px">Nama Barang / Pesanan</th>';
            echo '<th style="font-size: 15px">Jumlah</th>';
            echo '<th style="font-size: 15px">Harga @</th>';
            echo '<th style="font-size: 15px">Disc</th>';
            echo '<th style="font-size: 15px">Sub Total</th>';
            echo '</tr>';
        }
        ?>

    </thead>
    <tbody>
        <?php
        if ($id_company == '3' || $id_company == '4' || $id_company == '5') {
        ?>
            <tr>
                <td style="text-align: center; height: 200px; vertical-align: top;"><?= $data_invoice->print_keterangan ?></td>
                <td style="text-align: center; height: 200px; vertical-align: top;">1</td>
                <td style="text-align: center; height: 200px; vertical-align: top;"><?= number_format($data_actual_plan_tagih->nominal_payment) ?></td>
                <td style="text-align: center; height: 200px; vertical-align: top;"></td>
                <td style="text-align: right; height: 200px; vertical-align: top;"><?= number_format($data_actual_plan_tagih->nominal_payment) ?></td>
            </tr>
            <tr>
                <td style="border-top: 1px solid black;" colspan="2"></td>
                <td style="border-top: 1px solid black;" width="150">DPP</td>
                <td style="text-align: center; border-top: 1px solid black;" width="150">:</td>
                <td style="text-align: right; border-top: 1px solid black;" width="150"><?= number_format($data_actual_plan_tagih->nominal_payment) ?></td>
            </tr>
            <tr>
                <td colspan="2"></td>
                <td width="150">DPP Lain-lain</td>
                <td style="text-align: center;" width="150">:</td>
                <td style="text-align: right;" width="150"><?= number_format($data_invoice->dpp_nilai_lain) ?></td>
            </tr>
            <tr>
                <td colspan="2"></td>
                <td width="150">PPN 12% dari DPP lain</td>
                <td style="text-align: center;" width="150">:</td>
                <td style="text-align: right;" width="150"><?= number_format($data_invoice->pajak) ?></td>
            </tr>
            <tr>
                <td colspan="2"></td>
                <td style="font-weight: bold;" width="150">Total Tagihan + PPN</td>
                <td style="text-align: center;" width="150">:</td>
                <td style="text-align: right;" width="150"><?= number_format($data_actual_plan_tagih->nominal_payment + $data_invoice->pajak) ?></td>
            </tr>
            <tr>
                <td colspan="5" style="height: 50px; vertical-align: middle">
                    <?= terbilang(($data_actual_plan_tagih->nominal_payment + $data_invoice->pajak)) . ' Rupiah' ?>
                </td>
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
                    } else if ($id_company == '4' || $id_company == '5') {
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
                    } else if ($id_company == '4' || $id_company == '5') {
                        echo '<b style="font-size: 14px;">PT. VUCA STRATEGI BISNIS</b>';
                    } else {
                        echo '<b style="font-size: 14px;">PT. SENTRAL TEHNOLOGI MANAGEMEN</b>';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td colspan="3" style="text-align: center;">
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
                <td style="text-align: center; height: 200px; vertical-align: top;"><?= $data_invoice->print_keterangan ?></td>
                <td style="text-align: center; height: 200px; vertical-align: top;">1</td>
                <td style="text-align: center; height: 200px; vertical-align: top;"><?= number_format($data_actual_plan_tagih->nominal_payment) ?></td>
                <td style="text-align: center; height: 200px; vertical-align: top;"></td>
                <td style="text-align: right; height: 200px; vertical-align: top;"><?= number_format($data_actual_plan_tagih->nominal_payment) ?></td>
            </tr>
            <tr>
                <td style="border-top: 1px solid black;" colspan="2"></td>
                <td style="border-top: 1px solid black;" width="150">DPP</td>
                <td style="text-align: center; border-top: 1px solid black;" width="150">:</td>
                <td style="text-align: right; border-top: 1px solid black;" width="150"><?= number_format($data_actual_plan_tagih->nominal_payment) ?></td>
            </tr>
            <tr>
                <td colspan="2"></td>
                <td width="150">DPP Lain-lain</td>
                <td style="text-align: center;" width="150">:</td>
                <td style="text-align: right;" width="150"><?= number_format($data_invoice->dpp_nilai_lain) ?></td>
            </tr>
            <tr>
                <td colspan="2"></td>
                <td width="150">PPN 12% dari DPP lain</td>
                <td style="text-align: center;" width="150">:</td>
                <td style="text-align: right;" width="150"><?= number_format($data_invoice->pajak) ?></td>
            </tr>
            <tr>
                <td colspan="2"></td>
                <td style="font-weight: bold;" width="150">Total Tagihan + PPN</td>
                <td style="text-align: center;" width="150">:</td>
                <td style="text-align: right;" width="150"><?= number_format($data_actual_plan_tagih->nominal_payment + $data_invoice->pajak) ?></td>
            </tr>
            <tr>
                <td colspan="5" style="height: 50px; vertical-align: middle">
                    <?= terbilang(($data_actual_plan_tagih->nominal_payment + $data_invoice->pajak)) . ' Rupiah' ?>
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
                    } else if ($id_company == '4' || $id_company == '5') {
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
                <td colspan="3" style="text-align: center;">
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