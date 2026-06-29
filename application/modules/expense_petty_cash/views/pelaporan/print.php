<?php

/**
 * PDF Template - Pelaporan Petty Cash
 *
 * Template HTML/CSS untuk mPDF (A4 Portrait).
 * Menampilkan header pelaporan, tabel pencatatan, dan area tanda tangan.
 *
 * @var object $pelaporan       Object berisi ->header dan ->pencatatan_list
 * @var string $creator_name    Nama pembuat pelaporan
 * @var string $approver_name   Nama approver
 */

// Indonesian month names
$bulan_indo = [
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember'
];

/**
 * Format tanggal ke "DD Month YYYY" (Indonesia)
 */
function format_tanggal_indo($date_str, $bulan_indo)
{
    if (empty($date_str)) return '-';
    $dt = new DateTime($date_str);
    $day = $dt->format('d');
    $month = (int) $dt->format('m');
    $year = $dt->format('Y');
    return $day . ' ' . $bulan_indo[$month] . ' ' . $year;
}

/**
 * Format tanggal ke "DD/MM/YYYY"
 */
function format_tanggal_tabel($date_str)
{
    if (empty($date_str)) return '-';
    $dt = new DateTime($date_str);
    return $dt->format('d/m/Y');
}

// Prepare periode display
$periode_start = format_tanggal_indo($pelaporan->header->periode_start, $bulan_indo);
$periode_end   = format_tanggal_indo($pelaporan->header->periode_end, $bulan_indo);

// Calculate grand total (handle empty pencatatan)
$grand_total = (int) $pelaporan->header->grand_total;
?>
<html>

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .header-section {
            text-align: center;
            margin-bottom: 20px;
        }

        .header-section h2 {
            font-size: 16px;
            margin: 0 0 5px 0;
            padding: 0;
            font-weight: bold;
            text-transform: uppercase;
        }

        .info-table {
            width: 100%;
            margin-bottom: 20px;
            border: none;
        }

        .info-table td {
            padding: 3px 5px;
            font-size: 11px;
            vertical-align: top;
        }

        .info-label {
            width: 120px;
            font-weight: bold;
        }

        .info-separator {
            width: 10px;
            text-align: center;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .data-table th {
            background-color: #f0f0f0;
            border: 1px solid #333;
            padding: 6px 8px;
            text-align: center;
            font-weight: bold;
            font-size: 10px;
        }

        .data-table td {
            border: 1px solid #333;
            padding: 5px 8px;
            font-size: 10px;
        }

        .data-table .text-center {
            text-align: center;
        }

        .data-table .text-right {
            text-align: right;
        }

        .data-table .text-left {
            text-align: left;
        }

        .total-row td {
            font-weight: bold;
            background-color: #f9f9f9;
        }

        .signature-section {
            width: 100%;
            margin-top: 40px;
        }

        .signature-section td {
            width: 50%;
            text-align: center;
            padding: 5px;
            vertical-align: top;
        }

        .signature-label {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 60px;
        }

        .signature-line {
            border-bottom: 1px solid #333;
            width: 180px;
            margin: 60px auto 5px auto;
        }

        .signature-name {
            font-size: 11px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <!-- Header Section -->
    <div class="header-section">
        <h2>LAPORAN PENGELUARAN KAS KECIL</h2>
    </div>

    <!-- Info Section -->
    <table class="info-table">
        <tr>
            <td class="info-label">No Pelaporan</td>
            <td class="info-separator">:</td>
            <td><?= htmlspecialchars($pelaporan->header->no_pelaporan) ?></td>
        </tr>
        <tr>
            <td class="info-label">Periode</td>
            <td class="info-separator">:</td>
            <td><?= $periode_start ?> - <?= $periode_end ?></td>
        </tr>
        <tr>
            <td class="info-label">Company</td>
            <td class="info-separator">:</td>
            <td><?= htmlspecialchars($pelaporan->header->company) ?></td>
        </tr>
    </table>

    <!-- Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 110px;">No Pencatatan</th>
                <th style="width: 80px;">Tanggal</th>
                <th>Request By</th>
                <th>Keterangan</th>
                <th style="width: 100px;">Nominal</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($pelaporan->pencatatan_list)): ?>
                <?php $no = 1; ?>
                <?php foreach ($pelaporan->pencatatan_list as $item): ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td class="text-center"><?= htmlspecialchars($item->no_pencatatan) ?></td>
                        <td class="text-center"><?= format_tanggal_tabel($item->tanggal) ?></td>
                        <td class="text-left"><?= htmlspecialchars($item->request_by) ?></td>
                        <td class="text-left"><?= htmlspecialchars($item->keterangan ?: '-') ?></td>
                        <td class="text-right"><?= number_format((int) $item->grand_total, 0, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center" style="padding: 15px; font-style: italic;">Tidak ada data pencatatan</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" class="text-right" style="padding-right: 10px;">Grand Total</td>
                <td class="text-right"><?= number_format($grand_total, 0, ',', '.') ?></td>
            </tr>
        </tfoot>
    </table>

    <!-- Signature Section -->
    <table class="signature-section">
        <tr>
            <td>
                <div class="signature-label">Dibuat Oleh</div>
                <div class="signature-line"></div>
                <div class="signature-name"><?= htmlspecialchars($creator_name ?: '_______________') ?></div>
            </td>
            <td>
                <div class="signature-label">Disetujui Oleh</div>
                <div class="signature-line"></div>
                <div class="signature-name"><?= htmlspecialchars($approver_name ?: '_______________') ?></div>
            </td>
        </tr>
    </table>
</body>

</html>