<?php
$disabled        = 'disabled';
$disabled2        = 'disabled';
$disabled3        = 'readonly';
?>

<!-- /.box-header -->
<div class="box-body">
    <div class='form-group row'>
        <label class='label-control col-sm-2'><b>Department <span class='text-red'>*</span></b></label>
        <div class='col-sm-4'>
            <select name='id_dept' id='id_dept' class='form-control input-md chosen_select' <?= $disabled; ?>>
                <option value='0'>Select An Department</option>
                <?php
                // foreach (get_list_dept() as $val => $valx) {
                // 	$dept = ($valx['id'] == $id_dept) ? 'selected' : '';
                // 	echo "<option value='" . $valx['id'] . "' " . $dept . ">" . $valx['nm_dept'] . "</option>";
                // }


                foreach ($list_departement as $departement) {
                    $selected = '';
                    if ($departement->id == $header[0]->id_dept) {
                        $selected = 'selected';
                    }
                    echo "<option value='" . $departement->id . "' " . $selected . ">" . strtoupper($departement->nama) . "</option>";
                }
                ?>
            </select>
        </div>
        <label class='label-control col-sm-2'><b>Project Name</b></label>
        <div class='col-sm-4'>
            <input type="text" name="" id="" value="<?= $header[0]->project_name ?>" class="form-control" readonly>
        </div>
    </div>

    <div class='form-group row'>

        <label class='label-control col-sm-2'><b>Upload Document</b></label>
        <div class='col-sm-4  text-right'>
            <input type='file' id='upload_spk' name='upload_spk' class='form-control input-md' placeholder='Upload Document' disabled>
            <?php if (!empty($header[0]->document)) { ?>
                <a href='<?= base_url('assets/file/produksi/' . $header[0]->document); ?>' target='_blank' title='Download' data-role='qtip'>Download</a>
            <?php } ?>
        </div>
        <label class='label-control col-sm-2'><b>Requestor</b></label>
        <div class='col-sm-4 text-right'>
            <input type="text" name="" id="" class="form-control" value="<?= $header[0]->nm_user ?>" readonly>
        </div>
    </div>

    <?php
    if ($approve == 'approve') {
    ?>
        <div class='form-group row'>
            <label class='label-control col-sm-2'><b>Approve <span class='text-red'>*</span></b></label>
            <div class='col-sm-2'>
                <select name='sts_app' id='sts_app' class='form-control input-md'>
                    <option value='0'>Select Approve</option>
                    <option value='Y'>Approve</option>
                    <option value='D'>Reject</option>
                </select>
            </div>
            <div class='col-sm-2'>

            </div>
            <label class='label-control col-sm-2 tnd_reason'><b>Reason <span class='text-red'>*</span></b></label>
            <div class='col-sm-4 tnd_reason'>
                <?php
                echo form_textarea(array('id' => 'reason', 'name' => 'reason', 'class' => 'form-control input-md', 'rows' => '2', 'cols' => '75', 'placeholder' => 'Reason'));
                ?>
            </div>
        </div>
    <?php
    }
    ?>
    <table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
        <thead>
            <tr class='bg-blue'>
                <th class='text-center' style='width: 3%;'>#</th>
                <th class='text-center'>Nama Barang/Jasa</th>
                <th class='text-center' style='width: 13%;'>Spec/ Requirement</th>
                <th class='text-center' style='width: 7%;'>Qty</th>
                <th class='text-center' style='width: 8%;'>Satuan</th>
                <th class='text-center' style='width: 9%;'>Est Harga</th>
                <th class='text-center' style='width: 9%;'>Est Total Harga</th>
                <th class='text-center' style='width: 9%;'>Tanggal Dibutuhkan</th>
                <th class='text-center' style='width: 15%;'>Keterangan</th>
                <th class='text-center' style='width: 15%;'>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $nomor = 0;
            if (!empty($detail)) {
                foreach ($detail as $val => $valx) {
                    $nomor++;

                    $status = '<div class="badge bg-red">PO Not Created</div>';
                    $check_po_created = $this->db->get_where('dt_trans_po', ['idpr' => $valx['id'], 'tipe' => 'pr depart'])->result();
                    if (count($check_po_created) > 0) {
                        $status = '<div class="badge bg-green">PO Created</div>';
                    }

                    echo "<tr class='header_" . $nomor . "'>";
                    echo "<td align='center'>" . $nomor . "<input type='hidden' name='detail[" . $nomor . "][id]' value='" . $valx['id'] . "'></td>";
                    echo "<td align='left'><input type='text' " . $disabled3 . " name='detail[" . $nomor . "][nm_barang]' class='form-control input-md' value='" . strtoupper($valx['nm_barang']) . "'></td>";
                    echo "<td align='left'><input type='text' " . $disabled3 . " name='detail[" . $nomor . "][spec]' class='form-control input-md' value='" . strtoupper($valx['spec']) . "'></td>";
                    echo "<td align='left'><input type='text' " . $disabled2 . " id='qty_" . $nomor . "' name='detail[" . $nomor . "][qty]' class='form-control input-md text-right autoNumeric2 sum_tot' value='" . $valx['qty'] . "'></td>";
                    echo "<td align='left'>
									<select name='detail[" . $nomor . "][satuan]' class='form-control wajib' " . $disabled2 . " required>";
                    echo "<option value=''>Pilih</option>";
                    foreach ($satuan as $key => $value) {
                        $selected = ($value['id'] == $valx['satuan']) ? 'selected' : '';
                        echo "<option value='" . $value['id'] . "' " . $selected . ">" . $value['code'] . "</option>";
                    }
                    echo "	</select>
									</td>";
                    echo "<td align='left'><input type='text' " . $disabled2 . " id='harga_" . $nomor . "' name='detail[" . $nomor . "][harga]' class='form-control input-md text-right maskM sum_tot' value='" . number_format($valx['harga']) . "' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''></td>";
                    echo "<td align='left'><input type='text' " . $disabled2 . " id='total_harga_" . $nomor . "' name='detail[" . $nomor . "][total_harga]' class='form-control input-md text-right maskM jumlah_all' value='" . number_format($valx['qty'] * $valx['harga']) . "' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero='' readonly></td>";
                    echo "<td align='left'><input type='text' " . $disabled3 . " name='detail[" . $nomor . "][tanggal]' class='form-control input-md text-center datepicker tgl_dibutuhkan' readonly value='" . strtoupper($valx['tanggal']) . "'></td>";
                    echo "<td align='left'><input type='text' " . $disabled3 . " name='detail[" . $nomor . "][keterangan]' class='form-control input-md' value='" . strtoupper($valx['keterangan']) . "'></td>";
                    echo "<td align='center'>" . $status . "</td>";
                    echo "</tr>";
                }
            }
            ?>
        </tbody>
    </table>
    <div class='box-footer'>

    </div>
</div>