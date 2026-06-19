<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Bukti Mutasi <?= $data->kd_mutasi_aktual ?></title>
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

            .bold {
                font-weight: bold;
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
            min-width: 180px;
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

        .sign-table {
            margin-top: 30px;
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

        .stamp-box {
            border: 1px dashed #999;
            height: 60px;
            width: 100px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #aaa;
            font-size: 9px;
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

    <!-- Tombol Print (tidak ikut tercetak) -->
    <div class="no-print" style="margin-bottom:12px;">
        <button class="btn-print" onclick="window.print()">&#128438; Cetak</button>
        <!-- <button class="btn-print" style="background:#555;" onclick="history.back()">&#8592; Kembali</button> -->
    </div>

    <!-- ===== HEADER ===== -->
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
            <h2>Bukti Mutasi Bank</h2>
            <div class="doc-no">No: <strong><?= $data->kd_mutasi_aktual ?></strong></div>
        </div>
    </div>
    <hr>

    <!-- ===== INFO DOKUMEN ===== -->
    <table class="no-border info-table" style="margin-bottom:12px;">
        <tr>
            <td>No. Mutasi Aktual</td>
            <td>:</td>
            <td><strong><?= $data->kd_mutasi_aktual ?></strong></td>
            <td style="width:30px;"></td>
            <td style="width:160px; font-weight:bold;">No. Request</td>
            <td style="width:10px;">:</td>
            <td><?= $data->kd_mutasi_request ?></td>
        </tr>
        <tr>
            <td>Tanggal Mutasi</td>
            <td>:</td>
            <td><?= date('d F Y', strtotime($data->tgl_mutasi)) ?></td>
            <td></td>
            <td style="font-weight:bold;">Tanggal Request</td>
            <td>:</td>
            <td><?= date('d F Y', strtotime($data->tgl_request)) ?></td>
        </tr>
        <tr>
            <td>Mata Uang</td>
            <td>:</td>
            <td><?= $data->mata_uang ?></td>
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

    <!-- ===== DETAIL MUTASI ===== -->
    <table class="detail-table">
        <thead>
            <tr>
                <th class="text-center" width="5%">No</th>
                <th width="30%">Keterangan</th>
                <th width="25%">Bank Asal</th>
                <th width="25%">Bank Tujuan</th>
                <th class="text-right" width="15%">Nilai Request</th>
                <th class="text-right" width="15%">Nilai Aktual</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td><?= $data->keterangan ?></td>
                <td><?= $data->nama_bank_asal ?><br><small style="color:#555;"><?= $data->bank_asal ?></small></td>
                <td><?= $data->nama_bank_tujuan ?><br><small style="color:#555;"><?= $data->bank_tujuan ?></small></td>
                <td class="text-right"><?= number_format($data->nilai_request, 2) ?></td>
                <td class="text-right"><strong><?= number_format($data->nilai_aktual, 2) ?></strong></td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-right bold">Total Mutasi</td>
                <td class="text-right bold"><?= number_format($data->nilai_aktual, 2) ?></td>
            </tr>
        </tfoot>
    </table>

    <!-- ===== TERBILANG ===== -->
    <div class="terbilang-box">
        Terbilang: <strong><?= isset($data->terbilang) && $data->terbilang ? ucfirst($data->terbilang) : ynz_terbilang($data->nilai_aktual) . ' ' . strtoupper($data->mata_uang) ?></strong>
    </div>

    <!-- ===== JURNAL REFERENSI ===== -->
    <?php if (!empty($data->jurnal)): ?>
        <table class="no-border info-table" style="margin-top:8px;">
            <tr>
                <td>No. Jurnal</td>
                <td>:</td>
                <td><strong><?= $data->jurnal ?></strong></td>
            </tr>
        </table>
    <?php endif; ?>

    <!-- ===== TANDA TANGAN ===== -->
    <table class="sign-table" style="margin-top:35px; width:100%;">
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

    <script>
        // Auto print saat halaman dibuka (opsional — uncomment jika diinginkan)
        // window.onload = function() { window.print(); };
    </script>
</body>

</html>