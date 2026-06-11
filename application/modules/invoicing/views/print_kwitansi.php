<style>
    body {
        font-family: 'Times New Roman', serif;
        font-size: 12px;
    }

    .header-table {
        width: 100%;
        border-bottom: 2px solid #000;
        margin-bottom: 10px;
    }

    .header-table td {
        vertical-align: top;
        padding: 5px;
    }

    .company-name {
        font-size: 25px;
        font-weight: bold;
        margin: 0;
    }

    .company-info {
        font-size: 14px;
        margin: 2px 0;
    }

    .title-section {
        width: 100%;
        margin-bottom: 10px;
        margin-top: 10px;
    }

    .title-section td {
        vertical-align: top;
        padding: 3px;
    }

    .title-kwitansi {
        font-size: 18px;
        font-weight: bold;
    }

    .body-table {
        width: 100%;
        border: 1px solid #000;
        border-collapse: collapse;
        margin-bottom: 15px;
    }

    .body-table td {
        padding: 8px 10px;
        font-size: 12px;
        border: 1px solid #000;
        vertical-align: top;
    }

    .body-table .label {
        width: 140px;
        font-weight: bold;
    }

    .price-section {
        width: 100%;
        margin-bottom: 15px;
    }

    .price-section td {
        padding: 2px 5px;
        font-size: 12px;
        vertical-align: middle;
    }

    .rp-box {
        border: 2px solid #000;
        padding: 8px 15px;
        font-size: 14px;
        font-weight: bold;
        text-align: right;
    }

    .footer-section {
        width: 100%;
        margin-top: 15px;
    }

    .footer-section td {
        vertical-align: top;
        padding: 3px 5px;
        font-size: 12px;
    }

    .signature-area {
        text-align: center;
    }

    .digital-sign {
        color: #999;
        font-size: 9px;
        margin: 10px 0;
    }

    @media print {
        body { margin: 0; }
    }
</style>

<!-- Header Section -->
<table class="header-table">
    <?php if ($id_company == '3') { ?>
    <tr>
        <td style="width: 45%;">
            <img src="<?= base_url('assets/images/sentral_sustain.png') ?>" alt="" width="300px">
        </td>
        <td align="center">
            <p class="company-name">PT. Sentral Sustainability Consulting</p>
            <p class="company-info">MT Haryono Square Lt. 3A No. 2, Jl. MT. Haryono Kav. 10, Jakarta Timur 13330</p>
            <p class="company-info">Telp. (021) 29067201, 29067202 Fax. (021) 29067204</p>
            <p class="company-info">Email : sspm@sentralsistem.com Website : www.sentralsistem.com</p>
        </td>
    </tr>
    <?php } elseif ($id_company == '4' || $id_company == '5') { ?>
    <tr>
        <td style="width: 45%;">
            <img src="<?= base_url('assets/images/logo_vuca.png') ?>" alt="" width="300px">
        </td>
        <td align="center">
            <p class="company-name">PT. Vuca Strategi Bisnis</p>
        </td>
    </tr>
    <?php } else { ?>
    <tr>
        <td style="width: 45%;">
            <img src="<?= base_url('assets/images/logo_sentral.png') ?>" alt="" width="300px">
        </td>
        <td align="center">
            <p class="company-name">PT. Sentral Tehnologi Managemen</p>
            <p class="company-info">MT Haryono Square Lt. 3A No. 2, Jl. MT. Haryono Kav. 10, Jakarta Timur 13330</p>
            <p class="company-info">Telp. (021) 29067201, 29067202 Fax. (021) 29067204</p>
            <p class="company-info">Email : sspm@sentralsistem.com Website : www.sentralsistem.com</p>
        </td>
    </tr>
    <?php } ?>
</table>

<!-- Title + Invoice Info Section -->
<table class="title-section">
    <tr>
        <td width="50%" align="right">
            <h1 style="padding-top: 10px;">KWITANSI</h1>
        </td>
        <td width="50%" style="text-align: right;">
            <table border="0" style="float: right;">
                <tr>
                    <td>Nomor Invoice</td>
                    <td style="width: 10px; text-align: center;">:</td>
                    <td><?= $data_invoice->no_invoice ?></td>
                </tr>
                <tr>
                    <td>Nomor PO</td>
                    <td style="width: 10px; text-align: center;">:</td>
                    <td><?= (!empty($data_invoice->no_po)) ? $data_invoice->no_po : '' ?></td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- Body Section -->
<table class="body-table"">
    <tr>
        <td class="label">Sudah Terima Dari</td>
        <td><?= $data_invoice->nm_customer ?></td>
    </tr>
    <tr>
        <td class="label">Banyak Uang</td>
        <td><?= ucfirst(terbilang($data_invoice->total_nominal + $data_invoice->pajak)) ?> Rupiah</td>
    </tr>
    <tr>
        <td class="label">Untuk Pembayaran</td>
        <td><?= $data_invoice->print_keterangan ?></td>
    </tr>
</table>

<!-- Price Breakdown + Rp Box -->
<table class="price-section">
    <tr>
        <td width="50%">
            <table border="0" width="100%">
                <tr>
                    <td style="font-weight: bold; text-align: right; width: 120px;">Harga</td>
                    <td style="width: 10px; text-align: center;">:</td>
                    <td style="text-align: right;"><?= number_format($data_invoice->total_nominal) ?></td>
                </tr>
                <tr>
                    <td style="font-weight: bold; text-align: right;">DPP Nilai Lain</td>
                    <td style="text-align: center;">:</td>
                    <td style="text-align: right;"><?= number_format($data_invoice->dpp_nilai_lain) ?></td>
                </tr>
                <tr>
                    <td style="font-weight: bold; text-align: right;">PPN 12%</td>
                    <td style="text-align: center;">:</td>
                    <td style="text-align: right;"><?= number_format($data_invoice->pajak) ?></td>
                </tr>
                <tr>
                    <td style="font-weight: bold; text-align: right; border-top: 1px solid #000; padding-top: 5px;">Total Harga</td>
                    <td style="text-align: center; border-top: 1px solid #000; padding-top: 5px;">:</td>
                    <td style="text-align: right; border-top: 1px solid #000; padding-top: 5px;"><?= number_format($data_invoice->total_nominal + $data_invoice->pajak) ?></td>
                </tr>
            </table>
        </td>
        <td width="50%" style="vertical-align: middle; text-align: right;">
            <div class="rp-box" style="display: inline-block;">
                Rp &nbsp;&nbsp;&nbsp;&nbsp; <?= number_format($data_invoice->total_nominal + $data_invoice->pajak) ?>
            </div>
        </td>
    </tr>
</table>

<hr style="border: 1px solid black;">

<!-- Footer Section -->
<table class="footer-section">
    <tr>
        <td width="55%">
            <p>Pembayaran dengan Tunai atau di Transfer ke:</p>
            <?php if ($id_company == '3') { ?>
                <p><strong>PT. SENTRAL SUSTAINABILITY CONSULTING</strong></p>
                <p><strong>BCA Tebet Barat Acc. No 436.400.0300</strong></p>
            <?php } elseif ($id_company == '4' || $id_company == '5') { ?>
                <p><strong>PT. VUCA STRATEGI BISNIS</strong></p>
                <p><strong>OCBC NISP Acc. No 7788.0000.0417</strong></p>
            <?php } else { ?>
                <p><strong>PT. SENTRAL TEHNOLOGI MANAGEMEN</strong></p>
                <p><strong>BCA Tebet Barat Acc. No 436.300.5287</strong></p>
            <?php } ?>
            <p>Bukti pembayaran mohon di email ke : Finance@sentralsistem.com</p>
        </td>
        <td width="45%" class="signature-area">
            <p>Jakarta, <?= date('d F Y') ?></p>
            <?php if ($id_company == '3') { ?>
                <p><strong>PT. Sentral Sustainability Consulting</strong></p>
                <p class="digital-sign">Digitally Signned By:<br>Cahyadi<br>PT. Sentral Sustainability Consulting</p>
                <br><br>
                <p><strong>Cahyadi</strong></p>
                <hr style="width: 180px; margin: 0 auto;">
                <p>Direktur</p>
            <?php } elseif ($id_company == '4' || $id_company == '5') { ?>
                <p><strong>PT. Vuca Strategi Bisnis</strong></p>
                <p class="digital-sign">Digitally Signned By:<br>Imanuel Iman<br>PT. Vuca Strategi Bisnis</p>
                <br><br>
                <p><strong>Imanuel Iman</strong></p>
                <hr style="width: 180px; margin: 0 auto;">
                <p>Direktur</p>
            <?php } else { ?>
                <p><strong>PT. Sentral Tehnologi Managemen</strong></p>
                <p class="digital-sign">Digitally Signned By:<br>Imanuel Iman<br>PT. Sentral Tehnologi Managemen</p>
                <br><br>
                <p><strong>Imanuel Iman</strong></p>
                <hr style="width: 180px; margin: 0 auto;">
                <p>Direktur</p>
            <?php } ?>
        </td>
    </tr>
</table>

<script>
    window.print();
</script>
