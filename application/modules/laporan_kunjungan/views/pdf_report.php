<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Kunjungan Konsultan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            color: #333333;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        .header h2 {
            margin: 0;
            padding: 0;
            font-size: 14pt;
            font-weight: bold;
        }
        .header-info {
            margin-top: 10px;
            margin-bottom: 15px;
        }
        .header-info table {
            width: 100%;
            border: none;
        }
        .header-info td {
            padding: 3px 5px;
            font-size: 10pt;
            vertical-align: top;
        }
        .header-info td.label {
            width: 120px;
            font-weight: bold;
        }
        .header-info td.separator {
            width: 10px;
            text-align: center;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .report-table th {
            background-color: #4472C4;
            color: #FFFFFF;
            font-weight: bold;
            font-size: 9pt;
            padding: 6px 4px;
            border: 1px solid #2F5496;
            text-align: center;
        }
        .report-table td {
            padding: 5px 4px;
            border: 1px solid #999999;
            font-size: 9pt;
            vertical-align: top;
        }
        .report-table tr:nth-child(even) td {
            background-color: #F2F2F2;
        }
        .status-done {
            color: #2E7D32;
            font-weight: bold;
        }
        .status-progress {
            color: #E65100;
            font-weight: bold;
        }
        .footer {
            margin-top: 15px;
            font-size: 8pt;
            color: #666666;
            text-align: right;
        }
    </style>
</head>
<body>

    <!-- Report Header -->
    <div class="header">
        <h2>LAPORAN KUNJUNGAN KONSULTAN</h2>
    </div>

    <!-- Project Info -->
    <div class="header-info">
        <table>
            <tr>
                <td class="label">Perusahaan</td>
                <td class="separator">:</td>
                <td><?php echo htmlspecialchars(isset($spk_detail->nm_customer) ? $spk_detail->nm_customer : '-'); ?></td>
            </tr>
            <tr>
                <td class="label">Project</td>
                <td class="separator">:</td>
                <td><?php echo htmlspecialchars(isset($spk_detail->nm_project) ? $spk_detail->nm_project : '-'); ?></td>
            </tr>
            <tr>
                <td class="label">Tanggal Cetak</td>
                <td class="separator">:</td>
                <td><?php echo date('d-m-Y'); ?></td>
            </tr>
        </table>
    </div>

    <!-- Report Data Table -->
    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 8%;">No</th>
                <th style="width: 10%;">Date</th>
                <th style="width: 12%;">Konsultan</th>
                <th style="width: 18%;">Kegiatan</th>
                <th style="width: 22%;">Action Plan</th>
                <th style="width: 10%;">PIC</th>
                <th style="width: 10%;">Due Date</th>
                <th style="width: 10%;">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 0;
            if (!empty($visits)) :
                foreach ($visits as $visit) :
                    $visit_date_formatted = !empty($visit->visit_date) ? date('d-m-Y', strtotime($visit->visit_date)) : '-';
                    $konsultan_name = isset($visit->konsultan_name) ? $visit->konsultan_name : '-';

                    if (!empty($visit->kegiatan)) :
                        foreach ($visit->kegiatan as $kegiatan) :
                            $nama_kegiatan = isset($kegiatan->nama_kegiatan) ? $kegiatan->nama_kegiatan : '-';

                            if (!empty($kegiatan->action_plans)) :
                                foreach ($kegiatan->action_plans as $plan) :
                                    $no++;
                                    $due_date_formatted = !empty($plan->due_date) ? date('d-m-Y', strtotime($plan->due_date)) : '-';
                                    $status = isset($plan->status) ? $plan->status : '-';
                                    $status_class = (strtolower($status) === 'done') ? 'status-done' : 'status-progress';
            ?>
            <tr>
                <td style="text-align: center;"><?php echo $no; ?></td>
                <td style="text-align: center;"><?php echo $visit_date_formatted; ?></td>
                <td><?php echo htmlspecialchars($konsultan_name); ?></td>
                <td><?php echo htmlspecialchars($nama_kegiatan); ?></td>
                <td><?php echo htmlspecialchars(isset($plan->description) ? $plan->description : '-'); ?></td>
                <td><?php echo htmlspecialchars(isset($plan->pic) ? $plan->pic : '-'); ?></td>
                <td style="text-align: center;"><?php echo $due_date_formatted; ?></td>
                <td style="text-align: center;" class="<?php echo $status_class; ?>"><?php echo htmlspecialchars($status); ?></td>
            </tr>
            <?php
                                endforeach;
                            else :
                                // Kegiatan without action plans
                                $no++;
            ?>
            <tr>
                <td style="text-align: center;"><?php echo $no; ?></td>
                <td style="text-align: center;"><?php echo $visit_date_formatted; ?></td>
                <td><?php echo htmlspecialchars($konsultan_name); ?></td>
                <td><?php echo htmlspecialchars($nama_kegiatan); ?></td>
                <td>-</td>
                <td>-</td>
                <td style="text-align: center;">-</td>
                <td style="text-align: center;">-</td>
            </tr>
            <?php
                            endif;
                        endforeach;
                    else :
                        // Visit without kegiatan
                        $no++;
            ?>
            <tr>
                <td style="text-align: center;"><?php echo $no; ?></td>
                <td style="text-align: center;"><?php echo $visit_date_formatted; ?></td>
                <td><?php echo htmlspecialchars($konsultan_name); ?></td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td style="text-align: center;">-</td>
                <td style="text-align: center;">-</td>
            </tr>
            <?php
                    endif;
                endforeach;
            else :
            ?>
            <tr>
                <td colspan="8" style="text-align: center; padding: 20px;">Belum ada data laporan kunjungan.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        Dicetak pada: <?php echo date('d-m-Y H:i'); ?>
    </div>

</body>
</html>
