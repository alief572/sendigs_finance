<div class="box-body">
    <?php if ($tanda != 'request') { ?>
        <table id="my-grid" class="table" width="100%">
            <thead>
                <tr>
                    <td class="text-left" style='vertical-align:middle;' width='15%'>No PO</td>
                    <td class="text-left" style='vertical-align:middle;' width='2%'>:</td>
                    <td class="text-left" style='vertical-align:middle;'><?= $no_surat; ?></td>
                </tr>
                <tr>
                    <td class="text-left" style='vertical-align:middle;'>Tanggal Penerimaan</td>
                    <td class="text-left" style='vertical-align:middle;'>:</td>
                    <td class="text-left" style='vertical-align:middle;'><?= $tanggal; ?></td>
                </tr>
            </thead>
        </table><br>
        <table id="my-grid" class="table table-striped table-bordered table-hover table-condensed" width="100%">
            <thead id='head_table'>
                <tr class='bg-blue'>
                    <th class="text-center" style='vertical-align:middle;' width='5%'>No</th>
                    <th class="text-center" style='vertical-align:middle;'>Code</th>
                    <th class="text-center" style='vertical-align:middle;'>Product Name</th>
                    <th class="text-center" style='vertical-align:middle;'>Unit Measurement</th>
                    <th class="text-center" style='vertical-align:middle;'>Qty</th>
                    <th class="text-center" style='vertical-align:middle;'>Qty Diterima</th>
                    <th class="text-center" style='vertical-align:middle;'>Qty Pack</th>
                    <th class="text-center" style='vertical-align:middle;'>Unit Packing</th>
                    <th class="text-center" style='vertical-align:middle;'>Keterangan</th>
                    <!-- <th class="text-center" style='vertical-align:middle;' width='5'>QRCode</th> -->
                </tr>
            </thead>
            <tbody>
                <?php
                $No = 0;
                foreach ($result as $val => $valx) {
                    $No++;

                    $qty_oke         = number_format($valx['qty_oke'], 4);
                    $qty_rusak         = number_format($valx['qty_rusak'], 4);
                    $keterangan     = (!empty($valx['keterangan'])) ? ucfirst($valx['keterangan']) : '-';
                    $qty_kurang     = number_format($valx['qty_order'] - $valx['qty_oke'], 4);
                    if ($tanda == 'check' and $checked == 'Y') {
                        $qty_oke         = number_format($valx['check_qty_oke'], 4);
                        $qty_rusak         = number_format($valx['check_qty_rusak'], 4);
                        $keterangan     = (!empty($valx['check_keterangan'])) ? ucfirst($valx['check_keterangan']) : '-';
                        $qty_kurang     = number_format($valx['qty_order'] - $valx['check_qty_oke'], 4);
                    }

                    echo "<tr>";
                    echo "<td align='center'>" . $No . "</td>";
                    echo "<td align='center'>" . $valx['code_product'] . "</td>";
                    echo "<td align='center'>" . $valx['namamaterial'] . "</td>";
                    echo "<td align='center'>" . ucfirst($valx['unit_measure']) . "</td>";
                    echo "<td align='center'>" . number_format($valx['qty'], 2) . "</td>";
                    echo "<td align='center'>" . number_format($valx['qty_order'], 2) . "</td>";
                    echo "<td align='center'>" . number_format($valx['qty_order'] / (($valx['konversi'] <= 0) ? 1 : $valx['konversi']), 2) . "</td>";
                    echo "<td align='center'>" . ucfirst($valx['unit_packing']) . "</td>";
                    echo "<td align='left'>" . $valx['keterangan_check'] . "</td>";
                    // echo "<td><a href='" . base_url('warehouse/print_qrcode/' . $valx['id']) . "' target='_blank' class='btn btn-xs btn-default'>QR</a></td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>

        <?php
        if ($file_incoming_material !== '' && $file_incoming_material !== null) {
            echo '<span>Incoming Material File</span> <br>';
            $exp_file_incoming_check = explode('|', $file_incoming_material);
            foreach ($exp_file_incoming_check as $exp_incoming) {
                if (file_exists($exp_incoming)) {
                    echo '<a href="' . base_url($exp_incoming) . '" class="" style="margin-top: 2vh;margin-left: 1vh;"><i class="fa fa-download"></i> ' . $exp_incoming . '</a> <br>';
                }
            }
        }
        ?>


    <?php } ?>


</div>
<script>
    swal.close();
</script>