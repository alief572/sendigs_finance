<?php
$nmuser = (!empty($data_pr->nm_pic)) ? $data_pr->nm_pic : '';
?>
<html>

<head>
    <title> PEMBELIAN CASH </title>
</head>

<body>
    <style>
        body {
            font-family: sans-serif;
        }

        table.garis {
            border-collapse: collapse;
            font-size: 0.9em;
            font-family: sans-serif;
        }

        @media print {
            .pagebreak {
                page-break-before: always;
            }
        }
    </style>
    <table cellpadding=2 cellspacing=0 border=0 width=650>
        <tr>
            <th colspan=6>PEMBELIAN CASH<br /><br /><br /></th>
        </tr>
        <tr>
            <td nowrap colspan=2>No Dokumen : <?= $data_pr->no_non_po ?></td>
            <td nowrap colspan=2>Total : <?= number_format($data_pr->total_pr) ?></td>
            <td nowrap colspan=2>Tanggal : <?= date('d F Y', strtotime($v_req_payment->tanggal)) ?></td>
        </tr>
        <tr>
            <th colspan=6><br /></th>
        </tr>
        <tr>
            <td valign=top width=100>Keperluan</td>
            <td valign=top colspan=5>: <?= $v_req_payment->keperluan ?></td>
        </tr>
        <tr>
            <td height=60 colspan=6></td>
        </tr>
        <tr>
            <td colspan=2 align=center>Mengajukan</td>
            <td colspan=2 rowspan=3></td>
            <td colspan=2 align=center>Mengetahui</td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <?php
        $mengajukan = $this->db->query("SELECT a.nm_lengkap as name FROM users a WHERE a.username='" . $data_pr->created_by . "'")->row();
        $mengetahui = new stdClass();
        $mengetahui->name = "FINANCE";
        ?>
        <tr height=120>
            <td colspan=2 align=center nowrap valign="bottom">
                <u>&nbsp; &nbsp; <?= (($nmuser) ? $nmuser : ' &nbsp; &nbsp;  &nbsp; &nbsp;  &nbsp; &nbsp; ') ?> &nbsp; &nbsp; </u><br><?= date('d F Y'); ?>
            </td>
            <td colspan=2 align=center nowrap valign="bottom">
                <u>&nbsp; &nbsp; <?= (($mengetahui) ? $mengetahui->name : ' &nbsp; &nbsp;  &nbsp; &nbsp;  &nbsp; &nbsp; ') ?> &nbsp; &nbsp; </u><br><?= date('d F Y'); ?>
            </td>
        </tr>
    </table><br /><br />
    <?php
    if (isset($doc_pr)) {
        if ($doc_pr->doc_file != '') {
            if (strpos($doc_pr->doc_file, 'pdf', 0) > 1) {
                echo '<div class="pagebreak"></div>
                <embed src="' . base_url($doc_pr->doc_file) . '" type="application/pdf" width="100%" style="height: 100vh; border: none;">';
            } else {
                echo '<div class="pagebreak"></div>
                <div class="col-md-12"><img src="' . base_url($doc_pr->doc_file) . '" style="max-width:100%; height:auto;"><br />' . $doc_pr->no_doc . '</div>';
            }
        }
    }
    ?>
    <script>
        window.print();
    </script>
</body>

</html>