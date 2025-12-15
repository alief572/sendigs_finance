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
        echo '<tr>';
        echo '<th style="font-size: 15px;" width="30%">Nama Barang / Pesanan</th>';
        echo '<th style="font-size: 15px;" width="10%">Jumlah</th>';
        echo '<th style="font-size: 15px;" width="10%">Harga @</th>';
        echo '<th style="font-size: 15px;" width="15%">Disc</th>';
        echo '<th style="font-size: 15px;" width="20%">Sub Total</th>';
        echo '<th style="font-size: 15px;" width="20%">Pajak</th>';
        echo '</tr>';
        ?>

    </thead>
    <tbody>
        <tr>
            <td style="text-align: center; height: 200px; vertical-align: top; border-right: 1px solid black;"><?= $data_invoice->print_keterangan ?></td>
            <td style="text-align: center; height: 200px; vertical-align: top; border-right: 1px solid black;">1</td>
            <td style="text-align: center; height: 200px; vertical-align: top; border-right: 1px solid black;"><?= number_format($data_actual_plan_tagih->nominal_payment) ?></td>
            <td style="text-align: center; height: 200px; vertical-align: top; border-right: 1px solid black;">0%</td>
            <td style="text-align: right; height: 200px; vertical-align: top; border-right: 1px solid black;"><?= number_format($data_actual_plan_tagih->nominal_payment) ?></td>
            <td style="text-align: center; height: 200px; vertical-align: top;">0%</td>
        </tr>
        <tr>
            <td style="border-top: 1px solid black;" colspan="4"></td>
            <td style="border-top: 1px solid black;">Discount Final</td>
            <td style="text-align: right; border-top: 1px solid black;">
                <table border="0" style="width: 100%">
                    <tr>
                        <td style="text-align: center;">:</td>
                        <td style="text-align: right;" width="200">
                            -
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="" colspan="4"></td>
            <td style="">Pajak</td>
            <td style="text-align: right; ">
                <table border="0" style="width: 100%">
                    <tr>
                        <td style="text-align: center;">:</td>
                        <td style="text-align: right;" width="200">
                            -
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="" colspan="4"></td>
            <td style="">Biaya Pengiriman</td>
            <td style="text-align: right;">
                <table border="0" style="width: 100%">
                    <tr>
                        <td style="text-align: center;">:</td>
                        <td style="text-align: right;" width="200">
                            -
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="" colspan="4"></td>
            <td style=""><b>TOTAL</b></td>
            <td style="text-align: right; ">
                <table border="0" style="width: 100%">
                    <tr>
                        <td style="text-align: center;">:</td>
                        <td style="text-align: right;" width="200">
                            <?= number_format($data_actual_plan_tagih->nominal_payment) ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="5" style="height: 50px; vertical-align: middle">
                <?= terbilang(($data_actual_plan_tagih->nominal_payment)) . ' Rupiah' ?>
            </td>
        </tr>
        <tr>
            <td colspan="2" rowspan="4" style="border-top: 1px solid black;">
                <p>Keterangan</p>
                <p>Pembayaran harus dilakukan paling lambat 14 hari <br> setelah Invoice diterima</p>
                <br>
                <p>Pembayaran di Transfer ke :</p>
                <b>PT. VUCA STRATEGI BISNIS</b><br>
                <b>OCBC NISP Acc. No 7788.0000.0417</b>
                <p>Bukti pembayaran mohon di email ke : Finance@sentralsistem.com</p>
            </td>
            <td colspan="4" style="border-top: 1px solid black; text-align: center;width: 400px !important;">
                <?php
                echo '<b style="font-size: 15px;">PT. VUCA STRATEGI BISNIS</b>';
                ?>
            </td>
        </tr>
        <tr>
            <td colspan="4" style="text-align: center;">
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
            </td>
        </tr>
        <tr>
            <td colspan="3" style="text-align: center; vertical-align: bottom;">
                <?php
                echo '<span style="font-weight: bold;">Imanuel Iman</span>';
                ?>
                <hr style="width: 300px;">
                <span>Direktur</span>
            </td>
        </tr>
        <tr>
            <td style="text-align: center;"></td>
        </tr>
    </tbody>
</table>


<script>
    window.print();
</script>