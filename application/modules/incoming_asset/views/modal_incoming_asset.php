<form action="#" method="POST" id="form_adjustment" enctype="multipart/form-data" autocomplete='off'>
    <div class="box-body">
        <br>
        <input type="hidden" name='no_po' id='no_po' value='<?= $no_po; ?>'>
        <input type="hidden" name='id_dept' id='id_dept' value='<?= $id_dept; ?>'>
        <input type="hidden" name='id_costcenter' id='id_costcenter' value='<?= $id_costcenter; ?>'>
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
                    <th class="text-center" style='vertical-align:middle;' width='5%'>Qty</th>
                    <th class="text-center" style='vertical-align:middle;' width='5%'>Satuan</th>
                    <th class="text-center" style='vertical-align:middle;' width='7%'>Status</th>
                    <th class="text-center" style='vertical-align:middle;' width='12%'>Catatan</th>
                    <th class="text-center" style='vertical-align:middle;' width='12%'>Pemeriksa</th>
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

                        $cek_type = substr($no_po, 0, 3);

                        // $dept = get_name('ms_department', 'nama', 'id', $valx['id_dept']);

                        $dept = [];

                        $this->db->select('a.nama');
                        $this->db->from('ms_department a');
                        $this->db->where_in('a.id', explode(',', $valx['id_dept']));
                        $get_dept = $this->db->get()->result();

                        foreach ($get_dept as $item_dept) {
                            $dept[] = $item_dept->nama;
                        }

                        $dept = implode(', ', $dept);

                        $satuan = 'UNIT';


                        echo "<tr>";
                        echo "<td align='center'>" . $No . "
                            <input type='hidden' name='addInMat[$No][no_po]' value='" . $valx['no_po'] . "'>
                            <input type='hidden' name='addInMat[$No][nm_barang]' value='" . $valx['namamaterial'] . "'>
                            <input type='hidden' name='addInMat[$No][spec]' value='" . $valx['idmaterial'] . "'>
                            <input type='hidden' name='addInMat[$No][id]' value='" . $valx['id'] . "'>
                            <input type='hidden' name='addInMat[$No][qty_rev]' value='" . $valx['qty'] . "'>
                        </td>";
                        echo "<td>" . strtoupper($dept) . "</td>";
                        echo "<td>" . strtoupper($valx['namamaterial']) . "</td>";
                        echo "<td align='center'>" . number_format($valx['qty']) . "</td>";
                        echo "<td align='center'>" . strtoupper($satuan) . "</td>";
                        echo "	<td>
                                <select name='addInMat[$No][status]' class='form-control input-md chosen_select'>
                                    <option value='1'>YES</option>
                                    <option value='2'>NO</option>
                                </select>
                            </td>";
                        echo "<td align='center'><input type='text' name='addInMat[$No][keterangan]' data-no='$No' class='form-control input-sm text-left'></td>";
                        echo "<td align='center'><input type='text' name='addInMat[$No][pemeriksa]' data-no='$No' class='form-control input-sm text-left'></td>";
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
    });
</script>