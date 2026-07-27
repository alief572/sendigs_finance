<table class="table table-striped">
    <thead>
        <tr>
            <th class="text-center">TOP</th>
            <th class="text-center">Keterangan</th>
            <th class="text-center">Nominal</th>
            <th class="text-center">Tagih/Mundur</th>
            <th class="text-center">Tanggal Tagih</th>
            <th class="text-center">Keterangan Terakhir Konsultan</th>
            <th class="text-center">Upload Surat Mundur</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="text-center">
                <?= $data_plan_tagih_detail['urutan'] ?>
            </td>
            <td class="text-left" width="20%"><?= $data_plan_tagih_detail['desc_payment'] ?></td>
            <td class="text-right"><?= number_format($data_plan_tagih_detail['nominal_payment']) ?></td>
            <td>
                Tagih
            </td>
            <td>
                <?= date('d F Y', strtotime($data_actual_plan_tagih['tanggal_actual_plan_tagih'])) ?>
            </td>
            <td>
                <?= $alasan_mundur ?>
            </td>
            <td>
                <?php 
                    if(file_exists(base_url($data_actual_plan_tagih['file_surat_mundur']))) {
                        echo '<a href="'.base_url($data_actual_plan_tagih['file_surat_mundur']).'" class="btn btn-info btn-sm"><i class="fa fa-file"></i> Download</a>';
                    }
                ?>
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