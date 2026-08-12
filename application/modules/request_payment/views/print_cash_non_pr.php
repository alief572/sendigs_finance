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
                echo '<div id="pdf-pages"></div>';
                echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>';
                echo '<script>
                    pdfjsLib.GlobalWorkerOptions.workerSrc = "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js";

                    var pdfUrl = "' . base_url($doc_pr->doc_file) . '";
                    var container = document.getElementById("pdf-pages");

                    pdfjsLib.getDocument(pdfUrl).promise.then(function(pdf) {
                        var totalPages = pdf.numPages;
                        var pagesRendered = 0;

                        for (var i = 1; i <= totalPages; i++) {
                            (function(pageNum) {
                                pdf.getPage(pageNum).then(function(page) {
                                    var scale = 1.5;
                                    var viewport = page.getViewport({ scale: scale });

                                    var wrapper = document.createElement("div");
                                    wrapper.className = "pagebreak";
                                    wrapper.style.marginBottom = "0";

                                    var canvas = document.createElement("canvas");
                                    canvas.width = viewport.width;
                                    canvas.height = viewport.height;
                                    canvas.style.width = "100%";
                                    canvas.style.height = "auto";
                                    canvas.style.display = "block";

                                    wrapper.appendChild(canvas);
                                    container.appendChild(wrapper);

                                    var context = canvas.getContext("2d");
                                    page.render({ canvasContext: context, viewport: viewport }).promise.then(function() {
                                        pagesRendered++;
                                        if (pagesRendered === totalPages) {
                                            setTimeout(function() { window.print(); }, 500);
                                        }
                                    });
                                });
                            })(i);
                        }
                    });
                </script>';
            } else {
                echo '<div class="pagebreak"></div>
                <div class="col-md-12"><img src="' . base_url($doc_pr->doc_file) . '" style="max-width:100%; height:auto;"><br />' . $doc_pr->no_doc . '</div>';
                echo '<script>window.print();</script>';
            }
        } else {
            echo '<script>window.print();</script>';
        }
    } else {
        echo '<script>window.print();</script>';
    }
    ?>
</body>

</html>