<div class="box-body">
    <input type="hidden" name='kode_trans' id='kode_trans' value='<?= $getData[0]['kode_trans']; ?>'>
    <input type="hidden" name='id_gudang_dari' id='id_gudang_dari' value='<?= $getData[0]['id_gudang_dari']; ?>'>
    <input type="hidden" name='id_gudang_ke' id='id_gudang_ke' value='<?= $getData[0]['id_gudang_ke']; ?>'>
    <table width="100%" border='0'>
        <thead>
            <tr>
                <td class="text-left" style='vertical-align:middle;' width='15%'>No Transaksi</td>
                <td class="text-left" style='vertical-align:middle;' width='2%'>:</td>
                <td class="text-left" style='vertical-align:middle;'><?= $getData[0]['kode_trans']; ?></td>
            </tr>
            <tr>
                <td class="text-left" style='vertical-align:middle;'>Tanggal Request</td>
                <td class="text-left" style='vertical-align:middle;'>:</td>
                <td class="text-left" style='vertical-align:middle;'><?= tgl_indo($getData[0]['tanggal']); ?></td>
            </tr>
        </thead>
    </table><br>

    <h4>Material</h4>
    <table class="table table-striped table-bordered table-hover table-condensed" width="100%">
        <thead>
            <tr>
                <th class='text-center' width='3%'>#</th>
                <th class='text-center' width='15%'>Code</th>
                <th class='text-center'>Material Name</th>
                <th class='text-center' width='9%'>Qty</th>
                <th class='text-center' width='5%'>Unit</th>
                <?php
                if ($tanda == 'edit') {
                ?>
                    <th class='text-center' width='10%'>Keterangan Req</th>
                    <th class='text-center' width='9%'>Stok</th>
                    <th class='text-center' width='9%'>Pengeluaran</th>
                <?php
                }
                ?>
                <th class='text-center' width='14%'>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (!empty($getDataDetailMix)) {
                $No = 100;
                foreach ($getDataDetailMix as $key => $value) {
                    $key++;
                    $No++;
                    $id_material     = $value['id_material'];
                    $nm_material    = (!empty($GET_MATERIAL[$id_material]['nama'])) ? $GET_MATERIAL[$id_material]['nama'] : 0;
                    $code_material  = (!empty($GET_MATERIAL[$id_material]['code'])) ? $GET_MATERIAL[$id_material]['code'] : 0;
                    $id_packing     = (!empty($GET_MATERIAL[$id_material]['id_packing'])) ? $GET_MATERIAL[$id_material]['id_packing'] : 0;
                    $id_unit         = (!empty($GET_MATERIAL[$id_material]['id_unit'])) ? $GET_MATERIAL[$id_material]['id_unit'] : 0;
                    $konversi       = (!empty($GET_MATERIAL[$id_material]['konversi'])) ? $GET_MATERIAL[$id_material]['konversi'] : 0;
                    $packing        = (!empty($GET_SATUAN[$id_packing]['code'])) ? $GET_SATUAN[$id_packing]['code'] : 0;
                    $unit            = (!empty($GET_SATUAN[$id_unit]['code'])) ? $GET_SATUAN[$id_unit]['code'] : 0;

                    $berat_req        = $value['qty_order'];

                    $stok = (!empty($GET_STOK[$id_material]['stok'])) ? $GET_STOK[$id_material]['stok'] : 0;
                    echo "<tr>";
                    echo "<td align='center'>" . $key . "</td>";
                    echo "<td>" . strtoupper($code_material) . "</td>";
                    echo "<td>" . strtoupper($nm_material) . "</td>";
                    if ($tanda == 'edit') {
                        echo "<td align='center'>" . number_format($berat_req, 4) . "</td>";
                        echo "<td align='center'>" . strtoupper($unit) . "</td>";
                        echo "<td align='left'>" . $value['keterangan'] . "</td>";
                        echo "<td align='center'>" . number_format($stok, 2) . "</td>";
                        echo "<td align='center'>
                                    <input type='hidden' name='detail[" . $No . "][no_ipp]' value='" . $value['no_ipp'] . "'>
                                    <input type='hidden' name='detail[" . $No . "][id]' value='" . $value['id'] . "'>
                                    <input type='hidden' name='detail[" . $No . "][id_material]' value='" . $id_material . "'>
                                    <input type='hidden' name='detail[" . $No . "][request]' value='" . $berat_req . "'>
                                    <input type='text' name='detail[" . $No . "][edit_qty]' data-no='$No' class='form-control input-md text-center autoNumeric4' value='" . $berat_req . "'>
                                </td>";
                        echo "<td align='center'><input type='text' name='detail[" . $No . "][keterangan]' data-no='$No' class='form-control input-md text-left'></td>";
                    } else {
                        echo "<td align='center'>" . number_format($berat_req, 4) . "</td>";
                        echo "<td align='center'>" . strtoupper($unit) . "</td>";
                        echo "<td align='left'>" . $value['keterangan'] . "</td>";
                    }
                    echo "</tr>";
                }
            ?>
            <?php
            } else {
                echo "<tr>";
                echo "<td colspan='9'><b>Tidak ada data yang ditampilkan !</b></td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
    <?php
    if ($tanda == 'edit') {
        echo form_button(array('type' => 'button', 'class' => 'btn btn-sm btn-primary', 'style' => 'min-width:100px; float:right; margin: 5px 0px 5px 0px;', 'value' => 'Save', 'content' => 'Confirm', 'id' => 'edit_materialacc'));
    }
    ?>
</div>
<style>
    .tanggal {
        cursor: pointer;
    }
</style>
<script>
    $(document).ready(function() {
        swal.close();
        $('.autoNumeric4').autoNumeric('init', {
            mDec: '4',
            aPad: false
        });
        $('.tanggal').datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true,
            minDate: 0
        });
    });
</script>