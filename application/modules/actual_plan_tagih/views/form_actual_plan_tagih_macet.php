<table class="table table-striped">
    <thead>
        <tr>
            <th class="text-center">TOP</th>
            <th class="text-center">Keterangan</th>
            <th class="text-center">Tagih/Mundur</th>
            <th class="text-center">Select Tanggal</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="text-center">
                <?= $data_plan_tagih_detail->urutan ?>
                <input type="hidden" name="id_detail_plan_tagih" value="<?= $data_plan_tagih_detail->id ?>">
                <input type="hidden" name="id_top" value="<?= $data_plan_tagih_detail->id_top ?>">
                <input type="hidden" name="id_spk_penawaran" value="<?= $data_plan_tagih_detail->id_spk_penawaran ?>">
                <input type="hidden" name="id_penawaran" value="<?= $data_plan_tagih_detail->id_penawaran ?>">
                <input type="hidden" name="term_payment" value="<?= $data_plan_tagih_detail->term_payment ?>">
                <input type="hidden" name="persen_payment" value="<?= $data_plan_tagih_detail->persen_payment ?>">
                <input type="hidden" name="nominal_payment" value="<?= $data_plan_tagih_detail->nominal_payment ?>">
                <input type="hidden" name="desc_payment" value="<?= $data_plan_tagih_detail->desc_payment ?>">
                <input type="hidden" name="tgl_plan_tagih" value="<?= $data_plan_tagih_detail->tgl_plan_tagih ?>">
                <input type="hidden" name="urutan" value="<?= $data_plan_tagih_detail->urutan ?>">
                <input type="hidden" name="macet" value="<?= $macet ?>">
            </td>
            <td class="text-left"><?= $data_plan_tagih_detail->desc_payment ?></td>
            <td>
                <select name="tagih_mundur" id="" class="form-control form-control-sm">
                    <option value="2">Mundur</option>
                </select>
            </td>
            <td>
                <input type="date" name="tanggal_actual" id="" class="form-control form-control-sm" class="text-center">
            </td>
        </tr>
    </tbody>
</table>

<br>

<div class="col-6">
    <div class="form-group">
        <label for="upload_laporan_progress">Upload Laporan Progress</label>
        <input type="file" name="upload_laporan_progress" id="upload_laporan_progress" class="form-control form-control-sm">
    </div>
</div>