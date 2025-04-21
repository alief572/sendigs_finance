<form action="#" method="POST" id="form_adjustment" enctype="multipart/form-data" autocomplete='off'>
    <div class="box-body">
        <input type="hidden" name='no_po' id='no_po' value='<?= implode(',', $no_po); ?>'>
        <input type="hidden" name='tanggal' id='tanggal' value='<?= $tanggal_trans; ?>'>
        <input type="hidden" name='pic' id='pic' value='<?= $pic; ?>'>
        <input type="hidden" name='note' id='note' value='<?= $note; ?>'>
        <input type="hidden" name='adjustment' id='adjustment' value='IN'>
        <div class='form-group row'>
            <label class='label-control col-sm-2'><b>Upload Document</b></label>
            <div class='col-sm-4'>
                <input type='file' name='upload_doc' class='form-control input-sm text-left'>
            </div>
        </div>
        <table id="my-grid" class="table table-striped table-bordered table-hover table-condensed" width="100%">
            <thead id='head_table'>
                <tr class='bg-blue'>
                    <th class="text-center" style='vertical-align:middle;' width='3%'>#</th>
                    <th class="text-center" style='vertical-align:middle;' width='10%'>Departemen</th>
                    <th class="text-center" style='vertical-align:middle;'>Nama Barang/Jasa</th>
                    <th class="text-center" style='vertical-align:middle;'>Spec/ Requirement</th>
                    <th class="text-center" style='vertical-align:middle;' width='9%'>Qty PO</th>
                    <th class="text-center" style='vertical-align:middle;' width='9%'>Qty Belum Dikirim</th>
                    <th class="text-center" style='vertical-align:middle;' width='9%'>Qty Diterima</th>
                    <th class="text-center" style='vertical-align:middle;' width='12%'>Catatan</th>
                    <!-- <th class="text-center" style='vertical-align:middle;' width='15%'>Upload Doc</th>  -->
                </tr>
            </thead>
            <tbody>
                <?php
                if (!empty($result)) {
                    $Total1 = 0;
                    $Total2 = 0;
                    $No = 0;
                    foreach ($result as $val => $valx) {
                        $No++;

                        // $cek_type = substr($no_po, 0, 3);


                        // if ($cek_type == 'NPO') {
                        //     $qty = $valx['qty_rev'];
                        //     $satuan = get_name('raw_pieces', 'kode_satuan', 'id_satuan', $valx['satuan']);
                        //     $dept = get_name('department', 'nm_dept', 'id', $valx['id_dept']);
                        //     $no_pox = $valx['no_non_po'];
                        // }
                        // if ($cek_type == 'POX') {
                        //     $qty = $valx['qty_po'];
                        //     $satuan = 'PCS';
                        //     $dept = '';
                        //     $no_pox = $valx['no_po'];
                        // }

                        $incoming_qty = 0;
                        $this->db->select('IF(SUM(a.qty_oke) IS NULL, 0, SUM(a.qty_oke)) as qty_incoming');
                        $this->db->from('warehouse_adjustment_detail a');
                        $this->db->join('warehouse_adjustment b', 'b.kode_trans = a.kode_trans');
                        $this->db->where('a.id_material', $valx['id']);
                        $this->db->where('b.no_ipp', $valx['no_po']);
                        $get_incoming_qty = $this->db->get()->row_array();
                        if (!empty($get_incoming_qty)) {
                            $incoming_qty = $get_incoming_qty['qty_incoming'];
                        }

                        $Qty_kurang = ($valx['qty'] - $incoming_qty);
                        echo "<tr>";
                        echo "<td align='center'>" . $No . "
                            <input type='hidden' name='addInMat[$No][no_po]' value='" . $valx['no_po'] . "'>
                            <input type='hidden' name='addInMat[$No][nm_barang]' value='" . $valx['namamaterial'] . "'>
                            <input type='hidden' name='addInMat[$No][spec]' value='" . $valx['spec'] . "'>
                            <input type='hidden' name='addInMat[$No][id]' value='" . $valx['id'] . "'>
                            <input type='hidden' name='addInMat[$No][qty_rev]' value='" . $valx['qty'] . "'>
                            <input type='hidden' name='addInMat[$No][tipe_po]' value='" . $valx['tipe_po'] . "'>
                            <input type='hidden' name='' class='max_qty_" . $valx['id'] . "' value='" . $Qty_kurang . "'>
                        </td>";
                        echo "<td>" . strtoupper($valx['nm_department']) . "</td>";
                        echo "<td>" . strtoupper($valx['namamaterial']) . "</td>";
                        echo "<td>" . strtoupper($valx['spec']) . "</td>";
                        echo "<td align='center'>" . number_format($valx['qty'], 2) . "</td>";
                        echo "<td align='center' class='belumDiterima'>" . number_format($Qty_kurang, 2) . "</td>";
                        // echo "<td align='center'>".strtoupper($satuan)."</td>";
                        // echo "	<td>
                        //             <select name='addInMat[$No][status]' class='form-control input-md chosen_select'>
                        //                 <option value='1'>YES</option>
                        //                 <option value='2'>NO</option>
                        //             </select>
                        //         </td>";
                        $readonly = '';
                        if ($Qty_kurang <= 0) {
                            $readonly = 'readonly';
                        }
                        echo "<td align='center'><input type='text' name='addInMat[$No][qty_in]' data-no='$No' class='form-control input-sm text-center maskM qtyDiterima' data-id='" . $valx['id'] . "' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero='' " . $readonly . "></td>";
                        echo "<td align='center'><input type='text' name='addInMat[$No][keterangan]' data-no='$No' class='form-control input-sm text-left' " . $readonly . "></td>";
                        // echo "<td align='center'><input type='file' name='upload_".$No."' data-no='$No' class='form-control input-sm text-left'></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr>";
                    echo "<td colspan='3'>Data aktual belum di update, silahkan update data terlebih dahulu.</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
        <?php
        echo form_button(array('type' => 'button', 'class' => 'btn btn-md btn-success', 'style' => 'min-width:100px; float:right; margin: 5px 0px 5px 0px;', 'value' => 'Save', 'content' => 'Save', 'id' => 'saveINMaterial'));
        ?>
    </div>
</form>
<style>
    .tanggal {
        cursor: pointer;
    }
</style>
<script>
    $(document).ready(function() {
        swal.close();
        $('.maskM').autoNumeric('init', {
            mDec: '2',
            aPad: false
        });
    });

    $(document).on('keyup', '.qtyDiterima', function() {
        let belumDiterima = getNum($(this).parent().parent().find('.belumDiterima').text().split(',').join(''))
        let qtyDiterima = getNum($(this).val().split(',').join(''))

        if (qtyDiterima > belumDiterima) {
            $(this).val(belumDiterima)
        }
    })
</script>