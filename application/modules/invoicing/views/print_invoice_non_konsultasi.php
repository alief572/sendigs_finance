<style>
    body {
        font-family: times-new-roman;
    }

    .table_invoice {
        border: 1px solid black;
        /* Outer border */
        border-collapse: collapse;
        margin-top: 1rem;
    }

    .table_invoice td,
    th {
        border: 1px sodid black;
        /* No inner borders */
        padding-top: 8px;
        padding-left: 5px;
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
                <p style="font-size: 14px;">Jl. MT. Haryono Kav. 10, Jakarta Timur 13330 <br> Telp. (021) 29067201, 29067202 Fax. (021) 29067204 <br> Email : sspm@sentralsistem.com Website : www.sentralsistem.com</p>
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
        <td style="text-align: center; width: 5px;">:</td>
        <td style="font-weight: bold;" width="230"><?= $data_invoice->nm_customer ?></td>
        <td width="110" style="padding-left: 28px;">Tanggal Invoice</td>
        <td style="text-align: center; width: 5px;">:</td>
        <td width="200"><?= date('d F Y', strtotime($data_invoice->tanggal_invoice)) ?></td>
    </tr>
    <tr>
        <td width="80">Alamat</td>
        <td style="text-align: center; width: 5px;">:</td>
        <td width="230"><?= $data_invoice->address ?></td>
        <td width="110" style="padding-left: 28px;">Nomor Invoice <br><br> No. PO</td>
        <td style="text-align: center;">: <br><br> :</td>
        <td width="200"><?= $data_invoice->no_invoice ?> <br><br> <?= $data_invoice->no_po ?></td>
    </tr>
    <tr>
        <td width="80">Up</td>
        <td style="text-align: center; width: 5px;">:</td>
        <td width="230">Finance Dept.</td>
        <td width="110"></td>
        <td style="text-align: center;"></td>
        <td width="200"></td>
    </tr>
</table>
<table class="table_list_barang">
    <thead>
        <?php
        if ($id_company == '3' || $id_company == '4' || $id_company == '5') {
            echo '<tr>';
            echo '<th style="font-size: 15px;" width="30%">Nama Barang / Pesanan</th>';
            echo '<th style="font-size: 15px;" width="10%">Jumlah</th>';
            echo '<th style="font-size: 15px;" width="10%">Harga @</th>';
            echo '<th style="font-size: 15px;" width="15%">Disc</th>';
            echo '<th style="font-size: 15px;" width="20%">Sub Total</th>';
            echo '</tr>';
        } else {
            echo '<tr>';
            echo '<th style="font-size: 15px;border: 1px solid black; text-align: center;" width="400px">Nama Barang / Pesanan</th>';
            echo '<th style="font-size: 15px;border: 1px solid black; text-align: center;" width="100px">Jumlah</th>';
            echo '<th style="font-size: 15px;border: 1px solid black; text-align: center;" width="100px">Harga @</th>';
            echo '<th style="font-size: 15px;border: 1px solid black; text-align: center;" width="100px">Disc</th>';
            echo '<th style="font-size: 15px;border: 1px solid black; text-align: center;" width="100px">Sub Total</th>';
            echo '</tr>';
        }
        ?>

    </thead>
    <tbody>
        <?php
        if ($id_company == '3' || $id_company == '4' || $id_company == '5') {
            $no_item = 0;
            foreach ($data_invoice_detail as $item) {
                $no_item++;

                echo '
                    <tr>
                        <td style="text-align: center; vertical-align: top; border-right: 1px solid black;">' . $item->nm_item . '</td>
                        <td style="text-align: center; vertical-align: top; border-right: 1px solid black;">' . number_format($item->qty) . '</td>
                        <td style="text-align: center; vertical-align: top; border-right: 1px solid black;">' . number_format($item->harga) . '</td>
                        <td style="text-align: center; vertical-align: top; border-right: 1px solid black;"></td>
                        <td style="text-align: right; vertical-align: top; border-right: 1px solid black;">' . number_format($item->total) . '</td>
                        
                    </tr>
                ';
            }
        ?>
            <tr>
                <td style="min-height: 165px; border-right: 1px solid black;"></td>
                <td style="min-height: 165px; border-right: 1px solid black;"></td>
                <td style="min-height: 165px; border-right: 1px solid black;"></td>
                <td style="min-height: 165px; border-right: 1px solid black;"></td>
                <td style="min-height: 165px; border-right: 1px solid black;"></td>
            </tr>
            <!-- <tr>
                <td style="text-align: center; height: 200px; vertical-align: top; border-right: 1px solid black;"><?= $data_invoice->print_keterangan ?></td>
                <td style="text-align: center; height: 200px; vertical-align: top; border-right: 1px solid black;">1</td>
                <td style="text-align: center; height: 200px; vertical-align: top; border-right: 1px solid black;"><?= number_format($data_invoice->total_nominal) ?></td>
                <td style="text-align: center; height: 200px; vertical-align: top; border-right: 1px solid black;"></td>
                <td style="text-align: right; height: 200px; vertical-align: top;"><?= number_format($data_invoice->total_nominal) ?></td>
            </tr> -->
            <tr>
                <td style="border-top: 1px solid black;" colspan="3"></td>
                <td style="border-top: 1px solid black;">DPP</td>
                <td style="text-align: right; border-top: 1px solid black;">
                    <table border="0" style="width: 100%">
                        <tr>
                            <td style="text-align: center;" width="100">:</td>
                            <td style="text-align: right;" width="100">
                                <?= number_format($data_invoice->total_nominal) ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="" colspan="3"></td>
                <td style="">DPP Lain-lain</td>
                <td style="text-align: right; ">
                    <table border="0" style="width: 100%">
                        <tr>
                            <td style="text-align: center;" width="100">:</td>
                            <td style="text-align: right;" width="100">
                                <?= number_format($data_invoice->dpp_nilai_lain) ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="" colspan="3"></td>
                <td style="">Pajak <?= isset($data_invoicing->ppn_persen) ? $data_invoicing->ppn_persen : 12 ?>%</td>
                <td style="text-align: right;">
                    <table border="0" style="width: 100%">
                        <tr>
                            <td style="text-align: center;" width="100">:</td>
                            <td style="text-align: right;" width="100">
                                <?= number_format($data_invoice->pajak) ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="" colspan="3"></td>
                <td style=""><b>TOTAL</b></td>
                <td style="text-align: right; ">
                    <table border="0" style="width: 100%">
                        <tr>
                            <td style="text-align: center;" width="100">:</td>
                            <td style="text-align: right;" width="100">
                                <?= number_format($data_invoice->total_nominal + $data_invoice->pajak) ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="5" style="height: 50px; vertical-align: middle">
                    <?= terbilang(($data_invoice->total_nominal + $data_invoice->pajak)) . ' Rupiah' ?>
                </td>
            </tr>
            <tr>
                <td colspan="2" rowspan="4" style="border-top: 1px solid black;">
                    <p>Keterangan</p>
                    <p>Pembayaran harus dilakukan paling lambat 14 hari <br> setelah Invoice diterima</p>
                    <br>
                    <p>Pembayaran di Transfer ke :</p>
                    <?php
                    if ($id_company == '3') {
                    ?>
                        <b style="font-size: 13px !important;">PT. SENTRAL SUSTAINABILITY CONSULTING</b><br>
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
                <td colspan="4" style="border-top: 1px solid black; text-align: center;width: 400px !important;">
                    <?php
                    if ($id_company == '3') {
                        echo '<b style="font-size: 15px;">PT. SENTRAL SUSTAINABILITY CONSULTING</b>';
                    } else if ($id_company == '4' || $id_company == '5') {
                        echo '<b style="font-size: 15px;">PT. VUCA STRATEGI BISNIS</b>';
                    } else {
                        echo '<b style="font-size: 15px;">PT. SENTRAL TEHNOLOGI MANAGEMEN</b>';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td colspan="4" style="text-align: center;">
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
                <td colspan="3" style="text-align: center; vertical-align: bottom;">
                    <?php
                    if ($id_company == '3') {
                        echo '<span style="font-weight: bold;">Cahyadi</span>';
                    } else {
                        echo '<span style="font-weight: bold;">Imanuel Iman</span>';
                    }
                    ?>
                    <hr style="width: 300px;">
                    <span>Direktur</span>
                </td>
            </tr>
            <tr>
                <td style="text-align: center;"></td>
            </tr>
        <?php
        } else {
            $no_item = 0;
            foreach ($data_invoice_detail as $item) {
                $no_item++;

                echo '
                    <tr>
                        <td style="text-align: center; vertical-align: top; border-right: 1px solid black;">' . $item->nm_item . '</td>
                        <td style="text-align: center; vertical-align: top; border-right: 1px solid black;">' . number_format($item->qty) . '</td>
                        <td style="text-align: center; vertical-align: top; border-right: 1px solid black;">' . number_format($item->harga) . '</td>
                        <td style="text-align: center; vertical-align: top; border-right: 1px solid black;"></td>
                        <td style="text-align: right; vertical-align: top; border-right: 1px solid black;">' . number_format($item->total) . '</td>
                    </tr>
                ';
            }
        ?>
            <tr>
                <td style="height: 165px; border-right: 1px solid black;"></td>
                <td style="height: 165px; border-right: 1px solid black;"></td>
                <td style="height: 165px; border-right: 1px solid black;"></td>
                <td style="height: 165px; border-right: 1px solid black;"></td>
                <td style="height: 165px; border-right: 1px solid black;"></td>
            </tr>
            <!-- <tr>
                <td style="text-align: center; height: 200px; vertical-align: top; border-right: 1px solid black;"><?= $data_invoice->print_keterangan ?></td>
                <td style="text-align: center; height: 200px; vertical-align: top; border-right: 1px solid black;">1</td>
                <td style="text-align: center; height: 200px; vertical-align: top; border-right: 1px solid black;"><?= number_format($data_invoice->total_nominal) ?></td>
                <td style="text-align: center; height: 200px; vertical-align: top; border-right: 1px solid black;"></td>
                <td style="text-align: right; height: 200px; vertical-align: top;"><?= number_format($data_invoice->total_nominal) ?></td>
            </tr> -->
            <tr>
                <td style="border-top: 1px solid black;" colspan="3"></td>
                <td style="border-top: 1px solid black;">DPP</td>
                <td style="text-align: right; border-top: 1px solid black;">
                    <table border="0" style="width: 100%">
                        <tr>
                            <td style="text-align: center;" width="100">:</td>
                            <td style="text-align: right;">
                                <?= number_format($data_invoice->total_nominal) ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="" colspan="3"></td>
                <td style="">DPP Lain-lain</td>
                <td style="text-align: right; ">
                    <table border="0" style="width: 100%">
                        <tr>
                            <td style="text-align: center;" width="100">:</td>
                            <td style="text-align: right;">
                                <?= number_format($data_invoice->dpp_nilai_lain) ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="" colspan="3"></td>
                <td style="">Pajak <?= isset($data_invoicing->ppn_persen) ? $data_invoicing->ppn_persen : 12 ?>%</td>
                <td style="text-align: right; ">
                    <table border="0" style="width: 100%">
                        <tr>
                            <td style="text-align: center;" width="100">:</td>
                            <td style="text-align: right;">
                                <?= number_format($data_invoice->pajak) ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="" colspan="3"></td>
                <td style="font-weight: bold;">TOTAL</td>
                <td style="text-align: right; ">
                    <table border="0" style="width: 100%">
                        <tr>
                            <td style="text-align: center;" width="100">:</td>
                            <td style="text-align: right;">
                                <?= number_format($data_invoice->total_nominal + $data_invoice->pajak) ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="3" style="height: 50px; vertical-align: middle; padding:8px;">
                    <?= terbilang(($data_invoice->total_nominal + $data_invoice->pajak)) . ' Rupiah' ?>
                </td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td colspan="3" rowspan="4" style="border-top: 1px solid black; padding: 8px;">
                    <p>Keterangan</p>
                    <p>Pembayaran harus dilakukan paling lambat 14 hari <br> setelah Invoice diterima</p>
                    <br>
                    <p>Pembayaran di Transfer ke :</p>
                    <?php
                    if ($id_company == '3') {
                    ?>
                        <b style="font-size: 15px;">PT. SENTRAL SUSTAINABILITY CONSULTING</b><br>
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
                <td colspan="2" style="border-top: 1px solid black; text-align: center; padding: 8px;" width="400px">
                    <b style="font-size: 15px;">PT. SENTRAL TEHNOLOGI MANAGEMEN</b>
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
                        echo '<span style="font-weight: bold;">Cahyadi</span>';
                    } else {
                        echo '<span style="font-weight: bold;">Imanuel Iman</span>';
                    }
                    ?>
                    <hr style="width: 300px;">
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