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
            <th>Status</th><th>Perlakuan PPN</th><th>Penjualan</th><th>DPP</th><th>PPN</th><th>Total Tagihan</th><th>Piutang Awal</th><th>Saldo Piutang</th>
        </tr>
    </thead>
    <tbody>
        <?php $no = 0;
        foreach ($list_data as $item) : $no++;
            $status = "Uninvoiced";
            if ($item->sts_invoice == '1') {
                $status = "Invoiced";
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
                <td><?= htmlspecialchars((string)($item->tax_treatment??''),ENT_QUOTES,'UTF-8') ?></td>
                <?php foreach(array('total_nominal','dpp_nilai_lain','ppn_jurnal','tagihan_ppn_jurnal','total_akhir_jurnal','saldo_piutang') as $moneyField): ?><td><?= (int)($item->consulting_version??0)===3?number_format((float)$item->$moneyField,2,'.',''):'' ?></td><?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>