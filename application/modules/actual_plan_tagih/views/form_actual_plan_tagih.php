<table class="table table-striped">
    <thead>
        <tr>
            <th class="text-center">TOP</th>
            <th class="text-center">Keterangan</th>
            <th class="text-center">Nominal</th>
            <th class="text-center">Tagih/Mundur</th>
            <th class="text-center">Select Tanggal</th>
            <th class="text-center">Alasan Mundur</th>
            <th class="text-center">Upload Surat Mundur</th>
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
                <input type="hidden" name="tgl_plan_tagih" id="tgl_plan_tagih" value="<?= $data_plan_tagih_detail->tgl_plan_tagih ?>">
                <input type="hidden" name="status_terakhir" id="status_terakhir" value="<?= $data_plan_tagih_detail->status_terakhir ?>">
                <input type="hidden" name="urutan" value="<?= $data_plan_tagih_detail->urutan ?>">
                <input type="hidden" name="macet" value="<?= $macet ?>">
                <input type="hidden" name="tgl_actual_tagih_last" value="<?= $tgl_actual_tagih_last ?>">
            </td>
            <td class="text-left" width="20%"><?= $data_plan_tagih_detail->desc_payment ?></td>
            <td class="text-right"><?= number_format($data_plan_tagih_detail->nominal_payment) ?></td>
            <td>
                <select name="tagih_mundur" id="tagih_mundur" class="form-control form-control-sm">
                    <option value="1">Tagih</option>
                    <option value="2">Mundur</option>
                    <option value="3">Tagihan Macet</option>
                </select>
            </td>
            <td>
                <input type="date" name="tanggal_actual" id="tanggal_actual" class="form-control form-control-sm text-center">
            </td>
            <td>
                <textarea name="alasan_mundur" id="" class="form-control form-control-sm" readonly></textarea>
            </td>
            <td>
                <input type="file" name="upload_surat_mundur" id="" class="form-control form-control-sm" disabled>
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

<script>
    $(document).ready(function() {
        var status_terakhir = $('input[name="status_terakhir"]').val();
        var tgl_plan_tagih = $('input[name="tgl_actual_tagih_last"]').val();

        if (status_terakhir == '2') {
            $('input[name="tanggal_actual"]').prop('readonly', false);
            $('textarea[name="alasan_mundur"]').attr('readonly', false);
            $('input[name="upload_surat_mundur"]').prop('disabled', false);
            $('select[name="tagih_mundur"]').val('2');
        } else {
            $('input[name="tanggal_actual"]').val(tgl_plan_tagih);
        }
    });
</script>