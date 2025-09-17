<div class="list_late_pr_approval">
    <h4>Late PR Approval</h4>
    <table class="table table-bordered w-100 late_pr_approval_datatable">
        <thead class="bg-green text-white">
            <tr>
                <th class="text-center">No.</th>
                <th class="text-center">No. PR</th>
                <th class="text-center">Department</th>
                <th class="text-center">Late</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no_late_pr = 1;
            foreach ($list_late_pr_approval as $late_pr) :
                echo '<tr>';
                echo '<td class="text-center">' . $no_late_pr . '</td>';
                echo '<td class="text-center">' . $late_pr['no_pr'] . '</td>';
                echo '<td class="text-center">' . strtoupper($late_pr['department']) . '</td>';
                echo '<td class="text-center">' . $late_pr['late_day'] . ' Hari</td>';
                echo '</tr>';

                $no_late_pr++;
            endforeach;
            ?>
        </tbody>
    </table>

    <br><br>

    <table class="table table-bordered w-100 late_detail_pr_approval_datatable">
        <thead class="bg-green text-white">
            <tr>
                <th class="text-center">No.</th>
                <th class="text-center">No. PR</th>
                <th class="text-center">PR Date</th>
                <th class="text-center">Approval PR (H+2)</th>
                <th class="text-center">Today</th>
                <th class="text-center">Aktual</th>
                <th class="text-center">Late</th>
                <th class="text-center">KPI</th>
            </tr>
        </thead>
        <tbody>
            <?php
            
            $no = 1;
            foreach ($list_all_pr_approval as $detail_item) {

                $kpi = $detail_item['late_day'] . ' Hari';
                if ($detail_item['late_day'] < 1) {
                    $kpi = '0 Hari';
                }

                $late = $detail_item['late_day'] . ' Hari';
                if ($detail_item['aktual'] !== '') {
                    $late = 'Close';
                }

                $aktual = '';
                if ($detail_item['aktual'] !== '') {
                    $aktual = date('d F Y', strtotime($detail_item['aktual']));
                }

                echo '<tr>';
                echo '<td class="text-center">' . $no . '</td>';
                echo '<td class="text-center">' . $detail_item['no_pr'] . '</td>';
                echo '<td class="text-center">' . date('d F Y', strtotime($detail_item['pr_date'])) . '</td>';
                echo '<td class="text-center">' . date('d F Y', strtotime($detail_item['max_approval_date'])) . '</td>';
                echo '<td class="text-center">' . date('d F Y') . '</td>';
                echo '<td class="text-center">' . $aktual . '</td>';
                echo '<td class="text-center">' . $late . '</td>';
                echo '<td class="text-center">' . $kpi . '</td>';
                echo '</tr>';

                $no++;
            }
            ?>
        </tbody>
    </table>

    <br><br>

    <h2>Ontime Approval</h2>
    <table class="table table-bordered w-100">
        <thead class="bg-green text-white">
            <tr>
                <th class="text-center">Months</th>
                <?php
                for ($i = 1; $i <= 12; $i++) {
                    echo '<th class="text-center">' . date('F', strtotime('01-' . $i . '-' . date('Y'))) . '</th>';
                }
                ?>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">Percentage</td>
                <?php
                for ($i = 1; $i <= 12; $i++) {
                    echo '<td class="text-center">' . number_format(ceil($summary_pr_approval[$i])) . '%</td>';
                }
                ?>
            </tr>
        </tbody>
    </table>
</div>
