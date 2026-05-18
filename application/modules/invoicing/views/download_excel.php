<?php
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Data Invoicing.xls");
?>
<table width="100%" border="1">
    <thead>
        <tr>
            <th>No.</th>
            <th>No. Invoice</th>
            <th>Company</th>
            <th>No. SPK</th>
            <th>Customer</th>
            <th>Project</th>
            <th>Project Leader</th>
            <th>Sales</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php $no = 0;
        foreach ($list_data as $item) : $no++;
            $status = "Draft";
            if ($item->sts_invoice == '1') {
                $status = "Invoice Created";
            }
        ?>
            <tr>
                <td style="text-align: center;"><?= $no ?></td>
                <td><?= $item->no_invoice ?></td>
                <td><?= $item->nm_company ?></td>
                <td><?= $item->id_spk_penawaran ?></td>
                <td><?= $item->nm_customer ?></td>
                <td><?= $item->nm_project ?></td>
                <td><?= $item->nm_project_leader ?></td>
                <td><?= $item->nm_sales ?></td>
                <td><?= $status ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>