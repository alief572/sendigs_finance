<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Transaksi Bank <?= $data->kd_mutasi ?></title>
    <style>
        @page {
            size: A4;
            margin: 10mm 15mm;
        }

        @media print {

            html,
            body {
                width: 210mm;
                margin: 0;
                padding: 0;
                font-family: Arial, sans-serif;
                font-size: 10px;
            }

            .no-print {
                display: none !important;
            }

            table {
                border-collapse: collapse;
                width: 100%;
            }

            th,
            td {
                border: 1px solid #000;
                padding: 5px 7px;
            }

            .no-border td,
            .no-border th {
                border: none !important;
            }

            .text-right {
                text-align: right;
            }

            .text-center {
                text-align: center;
            }

            tr,
            td,
            th {
                page-break-inside: avoid;
            }
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            padding: 10mm 15mm;
            margin: 0;
            color: #000;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px 7px;
        }

        .no-border td,
        .no-border th {
            border: none !important;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .header-wrap {
            display: flex;
            align-items: flex-start;
            margin-bottom: 6px;
        }

        .header-logo {
            width: 120px;
        }

        .header-company {
            flex: 1;
            padding-left: 12px;
        }

        .header-title {
            text-align: right;
            min-width: 200px;
        }

        .header-title h2 {
            margin: 0;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .header-title .doc-no {
            font-size: 11px;
            color: #333;
            margin-top: 4px;
        }

        hr {
            border: none;
            border-top: 2px solid #000;
            margin: 6px 0 10px;
        }

        .info-table td {
            padding: 3px 6px;
            font-size: 11px;
        }

        .info-table td:first-child {
            width: 160px;
            font-weight: bold;
        }

        .info-table td:nth-child(2) {
            width: 10px;
        }

        .detail-table thead th {
            background-color: #1a5276;
            color: #fff;
            text-align: center;
            font-size: 11px;
        }

        .detail-table tbody td {
            font-size: 11px;
        }

        .terbilang-box {
            border: 1px solid #000;
            padding: 6px 10px;
            margin-top: 10px;
            font-size: 11px;
            font-style: italic;
        }

        .jenis-badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            color: #fff;
        }

        .jenis-keluar {
            background: #e74c3c;
        }

        .jenis-terima {
            background: #27ae60;
        }

        .sign-table {
            margin-top: 35px;
            width: 100%;
        }

        .sign-table td {
            border: none;
            text-align: center;
            width: 33%;
            padding: 0 10px;
            font-size: 11px;
        }

        .sign-line {
            height: 55px;
        }

        .btn-print {
            display: inline-block;
            margin-bottom: 15px;
            padding: 6px 18px;
            background: #1a5276;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
        }

        .btn-print:hover {
            background: #154360;
        }
    </style>
</head>

<body>

    <div class="no-print" style="margin-bottom:12px;">
        <button class="btn-print" onclick="window.print()">&#128438; Cetak</button>
        <!-- <button class="btn-print" style="background:#555;" onclick="history.back()">&#8592; Kembali</button> -->
    </div>

    <!-- HEADER -->
    <div class="header-wrap">
        <div class="header-logo">
            <img src="<?= base_url('assets/images/logo_sbf.png') ?>" alt="Logo" width="100" height="60">
        </div>
        <div class="header-company">
            <strong style="font-size:13px;">PT Surya Bangun Fajar</strong><br>
            Jl. Kalijaga No.35 Kel. Pegambiran Kec. Lemahwungkuk<br>
            Kota Cirebon, Jawa Barat 45113 &mdash; Indonesia
        </div>
        <div class="header-title">
            <?php
            $jenis = $data->jenis_transaksi ?? 'terima';
            $judulJenis = ($jenis == 'keluar') ? 'Bukti Pengeluaran Bank' : 'Bukti Penerimaan Bank';
            $badgeClass = ($jenis == 'keluar') ? 'jenis-keluar' : 'jenis-terima';
            $badgeLabel = ($jenis == 'keluar') ? 'PENGELUARAN' : 'PENERIMAAN';
            ?>
            <h2><?= $judulJenis ?></h2>
            <div class="doc-no">No: <strong><?= $data->kd_mutasi ?></strong></div>
            <div style="margin-top:4px;">
                <span class="jenis-badge <?= $badgeClass ?>"><?= $badgeLabel ?></span>
            </div>
        </div>
    </div>
    <hr>

    <!-- INFO DOKUMEN -->
    <table class="no-border info-table" style="margin-bottom:12px;">
        <tr>
            <td>No. Transaksi</td>
            <td>:</td>
            <td><strong><?= $data->kd_mutasi ?></strong></td>
            <td style="width:30px;"></td>
            <td style="width:120px; font-weight:bold;">Mata Uang</td>
            <td style="width:10px;">:</td>
            <td><?= $data->mata_uang ?></td>
        </tr>
        <tr>
            <td>Tanggal Transaksi</td>
            <td>:</td>
            <td><?= date('d F Y', strtotime($data->tgl_request)) ?></td>
            <td></td>
            <td style="font-weight:bold;">Kurs</td>
            <td>:</td>
            <td><?= number_format($data->kurs, 2) ?></td>
        </tr>
        <tr>
            <td>Keterangan</td>
            <td>:</td>
            <td colspan="5"><?= $data->keterangan ?></td>
        </tr>
    </table>

    <!-- DETAIL TRANSAKSI -->
    <table class="detail-table">
        <thead>
            <tr>
                <th class="text-center" width="5%">No</th>
                <th width="30%">Keterangan</th>
                <th width="25%">COA Asal / Tujuan</th>
                <th width="25%">COA Bank</th>
                <th class="text-right" width="15%">Nilai (Valas)</th>
                <th class="text-right" width="15%">Nilai (IDR)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td><?= $data->keterangan ?></td>
                <td>
                    <?= $data->nama_bank_asal ?>
                    <br><small style="color:#555;"><?= $data->bank_asal ?></small>
                </td>
                <td>
                    <?= $data->nama_bank_tujuan ?>
                    <br><small style="color:#555;"><?= $data->bank_tujuan ?></small>
                </td>
                <td class="text-right"><?= number_format($data->nilai, 2) ?></td>
                <td class="text-right"><strong><?= number_format($data->transaksi, 2) ?></strong></td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-right bold">Total</td>
                <td class="text-right bold"><?= number_format($data->transaksi, 2) ?></td>
            </tr>
        </tfoot>
    </table>

    <!-- TERBILANG -->
    <div class="terbilang-box">
        Terbilang: <strong><?= !empty($data->terbilang) ? ucfirst($data->terbilang) : ynz_terbilang($data->transaksi) . ' ' . strtoupper($data->mata_uang) ?></strong>
    </div>

    <!-- JURNAL REFERENSI -->
    <?php
    $jurnal_refs = array_filter([
        isset($data->jurnal1) && $data->jurnal1 ? 'Jurnal 1: ' . $data->jurnal1 : null,
        isset($data->jurnal2) && $data->jurnal2 ? 'Jurnal 2: ' . $data->jurnal2 : null,
    ]);
    ?>
    <?php if (!empty($jurnal_refs)): ?>
        <table class="no-border info-table" style="margin-top:8px;">
            <?php foreach ($jurnal_refs as $jr): ?>
                <tr>
                    <td colspan="3"><?= $jr ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <!-- TANDA TANGAN -->
    <table class="sign-table">
        <tr>
            <td class="text-center">Dibuat Oleh</td>
            <td class="text-center">Diperiksa Oleh</td>
            <td class="text-center">Disetujui Oleh</td>
        </tr>
        <tr>
            <td class="sign-line"></td>
            <td class="sign-line"></td>
            <td class="sign-line"></td>
        </tr>
        <tr>
            <td class="text-center">
                ( <strong><?= $data->created_by ?></strong> )<br>
                <small><?= date('d/m/Y', strtotime($data->created_on)) ?></small>
            </td>
            <td class="text-center">( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</td>
            <td class="text-center">( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</td>
        </tr>
    </table>

</body>

</html>