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
</table>

<br><br>

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
        <tr>
            <th>Nama Barang / Pesanan</th>
            <th>Jumlah</th>
            <th>Harga @</th>
            <th>Disc</th>
            <th>Sub Total</th>
        </tr>
    </thead>
    <tbody>
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
                <?= terbilang($data_invoice->total_akhir) ?>
            </td>
        </tr>
        <tr>
            <td colspan="3" rowspan="3" style="border-top: 1px solid black;">
                <p>Keterangan</p>
                <p>Pembayaran harus dilakukan paling lambat 30 hari <br> setelah Invoice diterima</p>
                <br>
                <p>Pembayaran di Transfer ke :</p>
                <b>PT. SENTRAL TEHNOLOGI MANAGEMEN</b><br>
                <b>BCA Tebet Barat Acc. No 436.300.5287</b>
                <p>Bukti pembayaran mohon di email ke : Finance@sentralsistem.com</p>
            </td>
            <td colspan="2" style="border-top: 1px solid black; text-align: center;">
                <b style="font-size: 14px;">PT. SENTRAL TEHNOLOGI MANAGEMEN</b> <br>
                <span style="color: #ccc; text-align: left !important;">
                    Digitally Signned By : <br>
                    Imanuel Iman <br>
                    PT. Sentral Tehnologi Managemen <br>
                </span>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: center; ">
                <span style="text-decoration: underline; font-weight: bold;">Imanuel Iman</span>
                <br>
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